<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabController extends Controller
{
    public function thyroid(Request $request)
    {
        $title = 'รายงานการส่งตรวจแล็บไทรอยด์';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Define Target Icodes
        $target_icodes = ['3000533', '3000534', '3000535'];
        $icodes_str = "'" . implode("','", $target_icodes) . "'";

        // 1. Fetch Monthly Stats for the 3 main icodes
        $monthly_stats = DB::connection('hosxp')->select('
            SELECT 
                CASE 
                    WHEN MONTH(o.vstdate)="10" THEN CONCAT("ต.ค. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="11" THEN CONCAT("พ.ย. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="12" THEN CONCAT("ธ.ค. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="1" THEN CONCAT("ม.ค. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="2" THEN CONCAT("ก.พ. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="3" THEN CONCAT("มี.ค. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="4" THEN CONCAT("เม.ย. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="5" THEN CONCAT("พ.ค. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="6" THEN CONCAT("มิ.ย. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="7" THEN CONCAT("ก.ค. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="8" THEN CONCAT("ส.ค. ",RIGHT(YEAR(o.vstdate)+543,2))
                    WHEN MONTH(o.vstdate)="9" THEN CONCAT("ก.ย. ",RIGHT(YEAR(o.vstdate)+543,2))
                END AS month,
                YEAR(o.vstdate) AS y,
                MONTH(o.vstdate) AS m,
                COUNT(o.vn) AS total_visit,
                COUNT(DISTINCT o.hn) AS total_hn,
                SUM(o.sum_price) AS total_income,
                SUM(CASE WHEN o.icode = "3000533" THEN o.qty ELSE 0 END) AS ft3_qty,
                SUM(CASE WHEN o.icode = "3000533" THEN o.sum_price ELSE 0 END) AS ft3_income,
                SUM(CASE WHEN o.icode = "3000534" THEN o.qty ELSE 0 END) AS ft4_qty,
                SUM(CASE WHEN o.icode = "3000534" THEN o.sum_price ELSE 0 END) AS ft4_income,
                SUM(CASE WHEN o.icode = "3000535" THEN o.qty ELSE 0 END) AS tsh_qty,
                SUM(CASE WHEN o.icode = "3000535" THEN o.sum_price ELSE 0 END) AS tsh_income
            FROM opitemrece o
            WHERE o.vstdate BETWEEN ? AND ?
              AND o.icode IN (' . $icodes_str . ')
            GROUP BY YEAR(o.vstdate), MONTH(o.vstdate)
            ORDER BY YEAR(o.vstdate), MONTH(o.vstdate)
        ', [$start_date, $end_date]);

        // 2. Fetch Monthly New Cases (first-ever thyroid test for these icodes)
        $new_cases_stats = DB::connection('hosxp')->select('
            SELECT 
                YEAR(first_date) AS y,
                MONTH(first_date) AS m,
                COUNT(hn) AS new_cases
            FROM (
                SELECT hn, MIN(vstdate) AS first_date
                FROM opitemrece
                WHERE icode IN (' . $icodes_str . ')
                GROUP BY hn
            ) AS first_tests
            WHERE first_date BETWEEN ? AND ?
            GROUP BY YEAR(first_date), MONTH(first_date)
        ', [$start_date, $end_date]);

        // Fetch Monthly New Cases by individual icode (first time ever receiving this specific test)
        $new_cases_by_icode = DB::connection('hosxp')->select('
            SELECT 
                YEAR(first_date) AS y,
                MONTH(first_date) AS m,
                SUM(CASE WHEN icode = "3000533" THEN 1 ELSE 0 END) AS ft3_new,
                SUM(CASE WHEN icode = "3000534" THEN 1 ELSE 0 END) AS ft4_new,
                SUM(CASE WHEN icode = "3000535" THEN 1 ELSE 0 END) AS tsh_new
            FROM (
                SELECT hn, icode, MIN(vstdate) AS first_date
                FROM opitemrece
                WHERE icode IN (' . $icodes_str . ')
                GROUP BY hn, icode
            ) AS first_tests
            WHERE first_date BETWEEN ? AND ?
            GROUP BY YEAR(first_date), MONTH(first_date)
        ', [$start_date, $end_date]);

        // Map new cases to monthly stats
        $new_cases_map = [];
        foreach ($new_cases_stats as $row) {
            $new_cases_map["{$row->y}-{$row->m}"] = (int) $row->new_cases;
        }

        $new_cases_by_icode_map = [];
        foreach ($new_cases_by_icode as $row) {
            $new_cases_by_icode_map["{$row->y}-{$row->m}"] = [
                'ft3_new' => (int) $row->ft3_new,
                'ft4_new' => (int) $row->ft4_new,
                'tsh_new' => (int) $row->tsh_new,
            ];
        }

        foreach ($monthly_stats as $row) {
            $key = "{$row->y}-{$row->m}";
            $row->new_cases = $new_cases_map[$key] ?? 0;
            $row->ft3_new = $new_cases_by_icode_map[$key]['ft3_new'] ?? 0;
            $row->ft4_new = $new_cases_by_icode_map[$key]['ft4_new'] ?? 0;
            $row->tsh_new = $new_cases_by_icode_map[$key]['tsh_new'] ?? 0;
        }

        // Totals for Summary Cards
        $total_visit = array_sum(array_column($monthly_stats, 'total_visit'));
        $total_hn = DB::connection('hosxp')
            ->table('opitemrece')
            ->whereBetween('vstdate', [$start_date, $end_date])
            ->whereIn('icode', $target_icodes)
            ->distinct()
            ->count('hn');
            
        $total_new_cases = array_sum(array_column($monthly_stats, 'new_cases'));
        $total_income = array_sum(array_column($monthly_stats, 'total_income'));

        // Arrays for Charts
        $months = array_column($monthly_stats, 'month');
        $ft3_qtys = array_map('intval', array_column($monthly_stats, 'ft3_qty'));
        $ft4_qtys = array_map('intval', array_column($monthly_stats, 'ft4_qty'));
        $tsh_qtys = array_map('intval', array_column($monthly_stats, 'tsh_qty'));
        $new_cases_series = array_map('intval', array_column($monthly_stats, 'new_cases'));
        $ft3_new_series = array_map('intval', array_column($monthly_stats, 'ft3_new'));
        $ft4_new_series = array_map('intval', array_column($monthly_stats, 'ft4_new'));
        $tsh_new_series = array_map('intval', array_column($monthly_stats, 'tsh_new'));

        // 3. Fetch Top 10 Ordering Doctors with separated lab types
        $top_doctors = DB::connection('hosxp')->select("
            SELECT 
                COALESCE(d.name, d_op.name, 'ไม่ระบุแพทย์/ผู้สั่ง') AS doctor_name,
                SUM(CASE WHEN o.icode = '3000533' THEN o.qty ELSE 0 END) AS ft3_qty,
                SUM(CASE WHEN o.icode = '3000534' THEN o.qty ELSE 0 END) AS ft4_qty,
                SUM(CASE WHEN o.icode = '3000535' THEN o.qty ELSE 0 END) AS tsh_qty,
                COUNT(o.vn) AS total_orders
            FROM opitemrece o
            LEFT JOIN lab_head lh ON lh.vn = o.vn
            LEFT JOIN doctor d ON d.code = lh.doctor_code
            LEFT JOIN doctor d_op ON d_op.code = o.doctor
            WHERE o.vstdate BETWEEN ? AND ?
              AND o.icode IN ('3000533', '3000534', '3000535')
            GROUP BY COALESCE(d.name, d_op.name, 'ไม่ระบุแพทย์/ผู้สั่ง')
            ORDER BY total_orders DESC
            LIMIT 10
        ", [$start_date, $end_date]);

        $top_doctors_names = array_column($top_doctors, 'doctor_name');
        $top_doctors_ft3 = array_map('intval', array_column($top_doctors, 'ft3_qty'));
        $top_doctors_ft4 = array_map('intval', array_column($top_doctors, 'ft4_qty'));
        $top_doctors_tsh = array_map('intval', array_column($top_doctors, 'tsh_qty'));

        // 4. Fetch Top 10 Diagnoses for New Cases
        $top_diags = DB::connection('hosxp')->select("
            SELECT 
                diag.diag_code,
                CONCAT(diag.diag_code, ': ', COALESCE(i.name, '')) AS diag_name,
                diag.total_cases
            FROM (
                SELECT 
                    IF(o.an IS NOT NULL AND o.an <> '', 
                        COALESCE((SELECT icd10 FROM iptdiag WHERE an = o.an AND diagtype = 1 LIMIT 1), a.pdx),
                        COALESCE((SELECT icd10 FROM ovstdiag WHERE vn = o.vn AND diagtype = 1 LIMIT 1), v.pdx)
                    ) AS diag_code,
                    COUNT(o.vn) AS total_cases
                FROM opitemrece o
                LEFT JOIN vn_stat v ON v.vn = o.vn
                LEFT JOIN an_stat a ON a.an = o.an
                WHERE o.vstdate BETWEEN ? AND ?
                  AND o.icode IN ('3000533', '3000534', '3000535')
                  AND o.vstdate = (SELECT MIN(vstdate) FROM opitemrece WHERE hn = o.hn AND icode IN ('3000533', '3000534', '3000535'))
                GROUP BY diag_code
            ) diag
            LEFT JOIN icd101 i ON i.code = diag.diag_code
            WHERE diag.diag_code IS NOT NULL AND diag.diag_code <> ''
            ORDER BY diag.total_cases DESC
            LIMIT 10
        ", [$start_date, $end_date]);

        $top_diags_names = array_column($top_diags, 'diag_name');
        $top_diags_counts = array_map('intval', array_column($top_diags, 'total_cases'));

        return view('hosxp.lab.thyroid', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'monthly_stats',
            'months',
            'ft3_qtys',
            'ft4_qtys',
            'tsh_qtys',
            'new_cases_series',
            'ft3_new_series',
            'ft4_new_series',
            'tsh_new_series',
            'total_visit',
            'total_hn',
            'total_new_cases',
            'total_income',
            'top_doctors_names',
            'top_doctors_ft3',
            'top_doctors_ft4',
            'top_doctors_tsh',
            'top_diags_names',
            'top_diags_counts'
        ));
    }

    public function thyroid_patients(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $month_key = $request->input('month_key');
        $icode = $request->input('icode');
        $is_new = (int) $request->input('is_new', 0);

        if (empty($start_date) || empty($end_date)) {
            return response()->json(['error' => 'Missing date parameters'], 400);
        }

        $q_start = $start_date;
        $q_end = $end_date;

        if (!empty($month_key) && $month_key !== 'all') {
            $q_start = $month_key . '-01';
            $q_end = date('Y-m-t', strtotime($q_start));
        }

        $icode_condition = "o.icode IN ('3000533', '3000534', '3000535')";
        $bindings = [$q_start, $q_end];

        if (!empty($icode) && $icode !== 'all') {
            $icode_condition = "o.icode = ?";
            $bindings = [$icode, $q_start, $q_end];
        }

        $new_condition = "";
        if ($is_new === 1) {
            if (!empty($icode) && $icode !== 'all') {
                $new_condition = "AND o.vstdate = (SELECT MIN(vstdate) FROM opitemrece WHERE hn = o.hn AND icode = o.icode)";
            } else {
                $new_condition = "AND o.vstdate = (SELECT MIN(vstdate) FROM opitemrece WHERE hn = o.hn AND icode IN ('3000533', '3000534', '3000535'))";
            }
        }

        $query = "
            SELECT DISTINCT
                DATE_FORMAT(o.vstdate, '%Y-%m-%d') AS test_date,
                p.hn,
                CONCAT(p.pname, p.fname, ' ', p.lname) AS patient_name,
                IF(o.an IS NOT NULL AND o.an <> '', 
                    COALESCE((SELECT icd10 FROM iptdiag WHERE an = o.an AND diagtype = 1 LIMIT 1), a.pdx),
                    COALESCE((SELECT icd10 FROM ovstdiag WHERE vn = o.vn AND diagtype = 1 LIMIT 1), v.pdx)
                ) AS pdx,
                IF(o.an IS NOT NULL AND o.an <> '', 
                    (SELECT GROUP_CONCAT(DISTINCT icd10) FROM iptdiag WHERE an = o.an AND diagtype NOT IN (1) AND icd10 REGEXP '^[A-Za-z]'),
                    (SELECT GROUP_CONCAT(DISTINCT icd10) FROM ovstdiag WHERE vn = o.vn AND diagtype NOT IN (1) AND icd10 REGEXP '^[A-Za-z]')
                ) AS sdx,
                IF(o.an IS NOT NULL AND o.an <> '', 
                    (SELECT GROUP_CONCAT(DISTINCT icd9) FROM iptoprt WHERE an = o.an),
                    (SELECT GROUP_CONCAT(DISTINCT icd9) FROM doctor_operation WHERE vn = o.vn)
                ) AS icd9,
                COALESCE(d.name, d_op.name) AS doctor_name
            FROM opitemrece o
            INNER JOIN patient p ON p.hn = o.hn
            LEFT JOIN vn_stat v ON v.vn = o.vn
            LEFT JOIN an_stat a ON a.an = o.an
            LEFT JOIN lab_head lh ON lh.vn = o.vn
            LEFT JOIN doctor d ON d.code = lh.doctor_code
            LEFT JOIN doctor d_op ON d_op.code = o.doctor
            WHERE o.vstdate BETWEEN ? AND ?
              AND {$icode_condition}
              {$new_condition}
            ORDER BY o.vstdate DESC
        ";

        if (!empty($icode) && $icode !== 'all') {
            $bindings = [$q_start, $q_end, $icode];
        } else {
            $bindings = [$q_start, $q_end];
        }

        $patients = DB::connection('hosxp')->select($query, $bindings);

        foreach ($patients as $p) {
            if ($p->test_date) {
                $thaiFull = DateThai($p->test_date); // e.g. "29 พ.ย. 2568"
                $parts = explode(' ', $thaiFull);
                if (count($parts) === 3) {
                    $parts[2] = substr($parts[2], 2); // "2568" -> "68"
                    $p->test_date = implode(' ', $parts);
                } else {
                    $p->test_date = $thaiFull;
                }
            } else {
                $p->test_date = '-';
            }
        }

        return response()->json([
            'patients' => $patients
        ]);
    }

    public function anywhere(Request $request)
    {
        $title = 'รายงานการให้บริการ Lab Anywhere';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // 1. Overall Totals
        $overall = DB::connection('hosxp')->select('
            SELECT 
                COUNT(DISTINCT lh.lab_order_number) as total_orders,
                COUNT(DISTINCT lh.hn) as total_hn,
                COUNT(lo.lab_items_code) as total_tests,
                SUM(CASE WHEN lh.confirm_report = "Y" THEN 1 ELSE 0 END) as confirmed_orders
            FROM lab_head lh
            LEFT JOIN lab_order lo ON lo.lab_order_number = lh.lab_order_number
            WHERE lh.form_name = "28.ใบสั่งตรวจ Lab Anywhere"
              AND lh.order_date BETWEEN ? AND ?
        ', [$start_date, $end_date]);

        $total_orders = $overall[0]->total_orders ?? 0;
        $total_hn = $overall[0]->total_hn ?? 0;
        $total_tests = $overall[0]->total_tests ?? 0;
        $confirmed_orders = $overall[0]->confirmed_orders ?? 0;
        $confirm_pct = $total_orders > 0 ? round(($confirmed_orders / $total_orders) * 100, 1) : 0;

        // 2. Monthly Stats
        $monthly_stats = DB::connection('hosxp')->select('
            SELECT 
                CASE 
                    WHEN MONTH(lh.order_date)="10" THEN CONCAT("ต.ค. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="11" THEN CONCAT("พ.ย. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="12" THEN CONCAT("ธ.ค. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="1" THEN CONCAT("ม.ค. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="2" THEN CONCAT("ก.พ. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="3" THEN CONCAT("มี.ค. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="4" THEN CONCAT("เม.ย. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="5" THEN CONCAT("พ.ค. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="6" THEN CONCAT("มิ.ย. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="7" THEN CONCAT("ก.ค. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="8" THEN CONCAT("ส.ค. ",RIGHT(YEAR(lh.order_date)+543,2))
                    WHEN MONTH(lh.order_date)="9" THEN CONCAT("ก.ย. ",RIGHT(YEAR(lh.order_date)+543,2))
                END AS month_name,
                YEAR(lh.order_date) AS y,
                MONTH(lh.order_date) AS m,
                CONCAT(YEAR(lh.order_date), "-", LPAD(MONTH(lh.order_date), 2, "0")) AS month_key,
                COUNT(DISTINCT lh.lab_order_number) AS total_orders,
                COUNT(DISTINCT lh.hn) AS total_hn,
                COUNT(lo.lab_items_code) AS total_tests,
                SUM(CASE WHEN lo.lab_items_code = 935 THEN 1 ELSE 0 END) AS fbs_count,
                SUM(CASE WHEN lo.lab_items_code = 937 THEN 1 ELSE 0 END) AS cr_count,
                SUM(CASE WHEN lo.lab_items_code = 936 THEN 1 ELSE 0 END) AS bun_count,
                SUM(CASE WHEN lo.lab_items_code IN (939,940,941,957,938) THEN 1 ELSE 0 END) AS lipid_count,
                SUM(CASE WHEN lo.lab_items_code = 954 THEN 1 ELSE 0 END) AS hba1c_count,
                SUM(CASE WHEN lo.lab_items_code = 959 THEN 1 ELSE 0 END) AS hct_count,
                SUM(CASE WHEN lo.lab_items_code IN (933,554,650) THEN 1 ELSE 0 END) AS alb_count,
                SUM(CASE WHEN lo.lab_items_code IN (995,996,997,998,33,34,35,36) THEN 1 ELSE 0 END) AS electrolyte_count,
                SUM(CASE WHEN lo.lab_items_code IN (944,945,947,948,949,950,955) THEN 1 ELSE 0 END) AS lft_count,
                SUM(CASE WHEN lo.lab_items_code IN (934,953,956,952) THEN 1 ELSE 0 END) AS other_count
            FROM lab_head lh
            LEFT JOIN lab_order lo ON lo.lab_order_number = lh.lab_order_number
            WHERE lh.form_name = "28.ใบสั่งตรวจ Lab Anywhere"
              AND lh.order_date BETWEEN ? AND ?
            GROUP BY YEAR(lh.order_date), MONTH(lh.order_date)
            ORDER BY YEAR(lh.order_date), MONTH(lh.order_date)
        ', [$start_date, $end_date]);

        // Chart data
        $months = array_column($monthly_stats, 'month_name');
        $orders_series = array_map('intval', array_column($monthly_stats, 'total_orders'));
        $hns_series = array_map('intval', array_column($monthly_stats, 'total_hn'));
        $tests_series = array_map('intval', array_column($monthly_stats, 'total_tests'));

        // 3. Top 10 Lab Items
        $top_items = DB::connection('hosxp')->select("
            SELECT 
                REPLACE(REPLACE(REPLACE(REPLACE(li.lab_items_name, '_Lab anywhere', ''), '_Lab Anywhere', ''), ' LabAnywhere', ''), '_LabAnywhere', '') AS item_name,
                COUNT(*) AS total_count
            FROM lab_head lh
            JOIN lab_order lo ON lo.lab_order_number = lh.lab_order_number
            JOIN lab_items li ON li.lab_items_code = lo.lab_items_code
            WHERE lh.form_name = '28.ใบสั่งตรวจ Lab Anywhere'
              AND lh.order_date BETWEEN ? AND ?
            GROUP BY item_name
            ORDER BY total_count DESC
            LIMIT 10
        ", [$start_date, $end_date]);
        $top_items_names = array_column($top_items, 'item_name');
        $top_items_counts = array_map('intval', array_column($top_items, 'total_count'));

        // 4. Top 10 Diagnoses (ICD10)
        $top_diags = DB::connection('hosxp')->select("
            SELECT 
                COALESCE(v.pdx, 'ไม่ระบุ') AS pdx,
                CONCAT(COALESCE(v.pdx, '-'), ': ', COALESCE(i.name, 'ไม่ระบุชื่อโรค')) AS diag_name,
                COUNT(DISTINCT lh.lab_order_number) AS total_orders
            FROM lab_head lh
            LEFT JOIN vn_stat v ON v.vn = lh.vn
            LEFT JOIN icd101 i ON i.code = v.pdx
            WHERE lh.form_name = '28.ใบสั่งตรวจ Lab Anywhere'
              AND lh.order_date BETWEEN ? AND ?
              AND v.pdx IS NOT NULL AND v.pdx <> ''
            GROUP BY v.pdx, i.name
            ORDER BY total_orders DESC
            LIMIT 10
        ", [$start_date, $end_date]);
        $top_diags_names = array_column($top_diags, 'diag_name');
        $top_diags_counts = array_map('intval', array_column($top_diags, 'total_orders'));

        // 5. Top 10 Ordering Doctors
        $top_doctors = DB::connection('hosxp')->select("
            SELECT 
                COALESCE(d.name, 'ไม่ระบุแพทย์/ผู้สั่ง') AS doctor_name,
                COUNT(DISTINCT lh.lab_order_number) AS total_orders
            FROM lab_head lh
            LEFT JOIN doctor d ON d.code = lh.doctor_code
            WHERE lh.form_name = '28.ใบสั่งตรวจ Lab Anywhere'
              AND lh.order_date BETWEEN ? AND ?
            GROUP BY COALESCE(d.name, 'ไม่ระบุแพทย์/ผู้สั่ง')
            ORDER BY total_orders DESC
            LIMIT 10
        ", [$start_date, $end_date]);
        $top_doctors_names = array_column($top_doctors, 'doctor_name');
        $top_doctors_counts = array_map('intval', array_column($top_doctors, 'total_orders'));

        return view('hosxp.lab.anywhere', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'total_orders',
            'total_hn',
            'total_tests',
            'confirmed_orders',
            'confirm_pct',
            'monthly_stats',
            'months',
            'orders_series',
            'hns_series',
            'tests_series',
            'top_items_names',
            'top_items_counts',
            'top_diags_names',
            'top_diags_counts',
            'top_doctors_names',
            'top_doctors_counts'
        ));
    }

    public function anywhere_patients(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $month_key = $request->input('month_key');
        $lab_item = $request->input('lab_item');

        if (empty($start_date) || empty($end_date)) {
            return response()->json(['error' => 'Missing date parameters'], 400);
        }

        $q_start = $start_date;
        $q_end = $end_date;

        if (!empty($month_key) && $month_key !== 'all') {
            $q_start = $month_key . '-01';
            $q_end = date('Y-m-t', strtotime($q_start));
        }

        $item_condition = "";
        $bindings = [$q_start, $q_end];

        if (!empty($lab_item) && $lab_item !== 'all') {
            if ($lab_item === 'fbs') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code = 935)";
            } elseif ($lab_item === 'cr') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code = 937)";
            } elseif ($lab_item === 'bun') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code = 936)";
            } elseif ($lab_item === 'lipid') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code IN (939,940,941,957,938))";
            } elseif ($lab_item === 'hba1c') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code = 954)";
            } elseif ($lab_item === 'hct') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code = 959)";
            } elseif ($lab_item === 'alb') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code IN (933,554,650))";
            } elseif ($lab_item === 'electrolyte') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code IN (995,996,997,998,33,34,35,36))";
            } elseif ($lab_item === 'lft') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code IN (944,945,947,948,949,950,955))";
            } elseif ($lab_item === 'other') {
                $item_condition = "AND EXISTS (SELECT 1 FROM lab_order lo WHERE lo.lab_order_number = lh.lab_order_number AND lo.lab_items_code IN (934,953,956,952))";
            }
        }

        $query = "
            SELECT 
                lh.lab_order_number,
                DATE_FORMAT(lh.order_date, '%Y-%m-%d') AS order_date,
                TIME_FORMAT(lh.order_time, '%H:%i') AS order_time,
                lh.hn,
                CONCAT(p.pname, p.fname, ' ', p.lname) AS patient_name,
                v.age_y,
                IF(p.sex = '1', 'ชาย', IF(p.sex = '2', 'หญิง', 'ไม่ระบุ')) AS sex_name,
                COALESCE(pt.name, 'ไม่ระบุสิทธิ') AS pttype_name,
                COALESCE(v.pdx, '-') AS pdx,
                COALESCE(i.name, '') AS pdx_name,
                COALESCE(d.name, 'ไม่ระบุแพทย์') AS doctor_name,
                lh.confirm_report
            FROM lab_head lh
            INNER JOIN patient p ON p.hn = lh.hn
            LEFT JOIN vn_stat v ON v.vn = lh.vn
            LEFT JOIN pttype pt ON pt.pttype = v.pttype
            LEFT JOIN icd101 i ON i.code = v.pdx
            LEFT JOIN doctor d ON d.code = lh.doctor_code
            WHERE lh.form_name = '28.ใบสั่งตรวจ Lab Anywhere'
              AND lh.order_date BETWEEN ? AND ?
              {$item_condition}
            ORDER BY lh.order_date DESC, lh.order_time DESC
            LIMIT 1000
        ";

        $patients = DB::connection('hosxp')->select($query, $bindings);

        if (!empty($patients)) {
            $orderNumbers = array_column($patients, 'lab_order_number');
            $inStr = implode(',', $orderNumbers);
            $results = DB::connection('hosxp')->select("
                SELECT 
                    lo.lab_order_number,
                    li.lab_items_name,
                    lo.lab_order_result,
                    li.lab_items_unit
                FROM lab_order lo
                JOIN lab_items li ON li.lab_items_code = lo.lab_items_code
                WHERE lo.lab_order_number IN ($inStr)
            ");
            $grouped = [];
            foreach ($results as $r) {
                $cleanName = str_ireplace(['_Lab anywhere', '_Lab Anywhere', ' LabAnywhere', '_LabAnywhere'], '', $r->lab_items_name);
                $val = trim($r->lab_order_result);
                if ($val !== '') {
                    $grouped[$r->lab_order_number][] = $cleanName . ': ' . $val . ($r->lab_items_unit ? ' ' . $r->lab_items_unit : '');
                }
            }
            foreach ($patients as $p) {
                $p->lab_results_text = isset($grouped[$p->lab_order_number]) ? implode(', ', $grouped[$p->lab_order_number]) : '-';
                if ($p->order_date) {
                    $thaiFull = DateThai($p->order_date);
                    $parts = explode(' ', $thaiFull);
                    if (count($parts) === 3) {
                        $parts[2] = substr($parts[2], 2);
                        $p->order_date_thai = implode(' ', $parts);
                    } else {
                        $p->order_date_thai = $thaiFull;
                    }
                } else {
                    $p->order_date_thai = '-';
                }
            }
        }

        return response()->json([
            'patients' => $patients
        ]);
    }

    private function resolveDateRange(Request $request)
    {
        $budget_year_select = DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(7)
            ->get();

        $budget_year_now = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $budget_year = $request->budget_year ?: $budget_year_now;

        if ($request->start_date && $request->end_date && !$request->has('budget_year_changed')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $matched_year = DB::table('budget_year')
                ->where('DATE_BEGIN', '<=', $start_date)
                ->where('DATE_END', '>=', $start_date)
                ->value('LEAVE_YEAR_ID');

            if ($matched_year) {
                $budget_year = $matched_year;
            }
        }
        else {
            $year_data = DB::table('budget_year')
                ->where('LEAVE_YEAR_ID', $budget_year)
                ->first();

            if ($year_data) {
                $start_date = $year_data->DATE_BEGIN;
                $end_date = $year_data->DATE_END;
            }
            else {
                $start_date = ($budget_year - 543) . '-10-01';
                $end_date = ($budget_year - 542) . '-09-30';
            }
        }

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'budget_year' => $budget_year,
            'budget_year_select' => $budget_year_select
        ];
    }
}
