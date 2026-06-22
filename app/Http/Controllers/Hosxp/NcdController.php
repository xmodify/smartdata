<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NcdController extends Controller
{
    public function index(Request $request)
    {
        $title = 'งานคลินิกโรคเรื้อรัง (NCD)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        return view('hosxp.ncd.index', compact('title', 'budget_year_select', 'budget_year', 'start_date', 'end_date'));
    }

    private $clinics_config = [
        '001' => [
            'name' => 'เบาหวาน',
            'title' => 'ทะเบียนผู้ป่วยคลินิกเบาหวาน',
            'theme' => 'red',
            'icon' => 'fas fa-syringe',
        ],
        '002' => [
            'name' => 'ความดัน',
            'title' => 'ทะเบียนผู้ป่วยคลินิกความดัน',
            'theme' => 'orange',
            'icon' => 'fas fa-heartbeat',
        ],
        '007' => [
            'name' => 'CKD',
            'title' => 'ทะเบียนผู้ป่วยคลินิก CKD',
            'theme' => 'teal',
            'icon' => 'fas fa-circle-nodes',
        ],
        '009' => [
            'name' => 'วัณโรค / Asthma',
            'title' => 'ทะเบียนผู้ป่วยคลินิกวัณโรค / Asthma',
            'theme' => 'green',
            'icon' => 'fas fa-lungs',
        ],
        '012' => [
            'name' => 'สุขภาพจิต',
            'title' => 'ทะเบียนผู้ป่วยคลินิกสุขภาพจิต',
            'theme' => 'cyan',
            'icon' => 'fas fa-brain',
        ],
        '013' => [
            'name' => 'ฟอกไต HD',
            'title' => 'ทะเบียนผู้ป่วยคลินิกฟอกไต HD',
            'theme' => 'teal',
            'icon' => 'fas fa-procedures',
        ],
        '014' => [
            'name' => 'ฟอกไต CAPD',
            'title' => 'ทะเบียนผู้ป่วยคลินิกฟอกไต CAPD',
            'theme' => 'cyan',
            'icon' => 'fas fa-water',
        ],
        '020' => [
            'name' => 'บำบัดยาเสพติด',
            'title' => 'ทะเบียนผู้ป่วยคลินิกบำบัดยาเสพติด',
            'theme' => 'orange',
            'icon' => 'fas fa-capsules',
        ],
        '021' => [
            'name' => 'COPD',
            'title' => 'ทะเบียนผู้ป่วยคลินิก COPD',
            'theme' => 'green',
            'icon' => 'fas fa-lungs',
        ],
        '028' => [
            'name' => 'โรคหลอดเลือดสมอง',
            'title' => 'ทะเบียนผู้ป่วยคลินิกโรคหลอดเลือดสมอง',
            'theme' => 'red',
            'icon' => 'fas fa-head-side-virus',
        ],
        '029' => [
            'name' => 'โรคหัวใจล้มเหลว',
            'title' => 'ทะเบียนผู้ป่วยคลินิกโรคหัวใจล้มเหลว',
            'theme' => 'indigo',
            'icon' => 'fas fa-heart',
        ],
        '032' => [
            'name' => 'โรคไตเรื้อรังระยะ 4-5',
            'title' => 'ทะเบียนผู้ป่วยคลินิกโรคไตเรื้อรังระยะ 4-5',
            'theme' => 'teal',
            'icon' => 'fas fa-network-wired',
        ],
    ];

    public function clinic_register(Request $request, $clinic_code)
    {
        if (!isset($this->clinics_config[$clinic_code])) {
            abort(404);
        }

        $config = $this->clinics_config[$clinic_code];
        $title = $config['title'];
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date   = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // 1. สรุปจำนวนผู้ป่วยแยกตามสถานะ (Optimized)
        $status_summary = DB::connection('hosxp')->select("
            SELECT cm.clinic_member_status_name, COUNT(c.hn) AS total
            FROM clinicmember c
            LEFT OUTER JOIN clinic_member_status cm ON cm.clinic_member_status_id = c.clinic_member_status_id
            WHERE c.clinic = ?
            GROUP BY c.clinic_member_status_id, cm.clinic_member_status_name
            ORDER BY total DESC
        ", [$clinic_code]);

        // 2. จำนวนผู้ป่วยรายใหม่แยกรายเดือน
        $new_by_month = DB::connection('hosxp')->select("
            SELECT
                CASE
                    WHEN MONTH(regdate)=10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=1  THEN CONCAT('ม.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=2  THEN CONCAT('ก.พ. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=3  THEN CONCAT('มี.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=4  THEN CONCAT('เม.ย. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=5  THEN CONCAT('พ.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=6  THEN CONCAT('มิ.ย. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=7  THEN CONCAT('ก.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=8  THEN CONCAT('ส.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=9  THEN CONCAT('ก.ย. ', RIGHT(YEAR(regdate)+543,2))
                END AS month_name,
                COUNT(hn) AS total,
                YEAR(regdate) AS y,
                MONTH(regdate) AS m
            FROM clinicmember
            WHERE clinic = ?
              AND regdate BETWEEN ? AND ?
            GROUP BY y, m, month_name
            ORDER BY y, m
        ", [$clinic_code, $start_date, $end_date]);

        if ($request->ajax()) {
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $searchValue = $request->get('search')['value'] ?? '';

            // Map columns for ordering
            $orderColumnIndex = $request->get('order')[0]['column'] ?? 6;
            $orderDir = $request->get('order')[0]['dir'] ?? 'desc';

            $columnsMap = [
                1 => 'c.hn',
                2 => 'p.cid',
                3 => 'p.fname',
                4 => 's.name',
                5 => 'y.name',
                6 => 'c.regdate',
                7 => 'c.lastvisit',
                8 => 'c.last_fbs_value',
                9 => 'c.last_hba1c_value',
                10 => 'c.last_ua_value',
                11 => 'c.last_bp_bps_value',
                12 => 'd.name',
                13 => 'c.clinic_member_status_id',
                14 => 'ph.name'
            ];

            $orderBy = $columnsMap[$orderColumnIndex] ?? 'c.regdate';

            $baseSql = "
                FROM clinicmember c
                INNER JOIN patient p ON p.hn = c.hn
                LEFT OUTER JOIN clinic n ON n.clinic = c.clinic
                LEFT OUTER JOIN pttype y ON y.pttype = p.pttype
                LEFT OUTER JOIN sex s ON s.CODE = p.sex
                LEFT OUTER JOIN doctor d ON d.CODE = c.doctor
                LEFT OUTER JOIN clinic_member_status cm ON cm.clinic_member_status_id = c.clinic_member_status_id
                LEFT OUTER JOIN hospcode ph ON ph.hospcode = c.send_to_pcu_hcode
                WHERE c.clinic = :clinic
            ";

            // Total records
            $totalCountRow = DB::connection('hosxp')->selectOne("SELECT COUNT(DISTINCT c.hn) AS total " . str_replace(':clinic', '?', $baseSql), [$clinic_code]);
            $recordsTotal = $totalCountRow ? $totalCountRow->total : 0;

            // Search filter
            $bindings = ['clinic' => $clinic_code];
            $searchSql = "";
            if (!empty($searchValue)) {
                $searchSql = "
                    AND (
                        c.hn LIKE :search
                        OR p.cid LIKE :search
                        OR p.fname LIKE :search
                        OR p.lname LIKE :search
                        OR y.NAME LIKE :search
                        OR d.NAME LIKE :search
                        OR ph.NAME LIKE :search
                    )
                ";
                $bindings['search'] = '%' . $searchValue . '%';
            }

            // Filtered records count
            $filteredCountRow = DB::connection('hosxp')->selectOne("
                SELECT COUNT(DISTINCT c.hn) AS total
                $baseSql
                $searchSql
            ", $bindings);
            $recordsFiltered = $filteredCountRow ? $filteredCountRow->total : 0;

            // Limit
            $limitSql = " LIMIT " . (int)$start . ", " . (int)$length;
            if ((int)$length === -1) {
                $limitSql = "";
            }

            // Fetch
            $patients = DB::connection('hosxp')->select("
                SELECT DISTINCT
                    n.NAME AS clinic_name,
                    p.cid,
                    c.hn,
                    CONCAT(p.pname, p.fname, SPACE(1), p.lname) AS patient_name,
                    c.regdate,
                    c.lastvisit,
                    c.last_hba1c_date,
                    c.last_hba1c_value,
                    c.last_ua_date,
                    c.last_ua_value,
                    c.last_bp_date,
                    CONCAT(c.last_bp_bps_value, '/', c.last_bp_bpd_value) AS last_bp_value,
                    c.last_fbs_date,
                    c.last_fbs_value,
                    y.NAME AS pttype_name,
                    s.NAME AS sex_name,
                    d.NAME AS doctor_name,
                    cm.clinic_member_status_name,
                    c.clinic_member_status_id,
                    CONCAT(ph.hosptype, SPACE(1), ph.NAME) AS send_pcu_hospital_name
                $baseSql
                $searchSql
                ORDER BY $orderBy $orderDir
                $limitSql
            ", $bindings);

            // Helpers for formatting
            $cleanDecimal = function($val) {
                if ($val === null || $val === '') return '-';
                $num = (float)$val;
                return ($num == (int)$num) ? (string)(int)$num : (string)round($num, 2);
            };

            $formatBp = function($val) {
                if (!$val) return '-';
                $parts = explode('/', $val);
                if (count($parts) === 2) {
                    $bps = (float)$parts[0];
                    $bpd = (float)$parts[1];
                    return round($bps) . '/' . round($bpd);
                }
                return str_replace('.00', '', $val);
            };

            $data = [];
            foreach ($patients as $idx => $row) {
                // FBS Format
                if ($row->last_fbs_date) {
                    $fbs_color = (float)$row->last_fbs_value > 126 ? 'text-danger' : 'text-success';
                    $last_fbs = '<span class="d-block text-muted" style="font-size:0.7rem;">'.DateThai($row->last_fbs_date).'</span>'
                              . '<span class="fw-bold '.$fbs_color.'">'.$cleanDecimal($row->last_fbs_value).'</span>';
                } else {
                    $last_fbs = '<span class="text-muted">-</span>';
                }

                // HbA1c Format
                if ($row->last_hba1c_date) {
                    $hba1c_color = (float)$row->last_hba1c_value > 7 ? 'text-danger' : 'text-success';
                    $last_hba1c = '<span class="d-block text-muted" style="font-size:0.7rem;">'.DateThai($row->last_hba1c_date).'</span>'
                                . '<span class="fw-bold '.$hba1c_color.'">'.$cleanDecimal($row->last_hba1c_value).'</span>';
                } else {
                    $last_hba1c = '<span class="text-muted">-</span>';
                }

                // UA Format
                if ($row->last_ua_date) {
                    $last_ua = '<span class="d-block text-muted" style="font-size:0.7rem;">'.DateThai($row->last_ua_date).'</span>'
                             . '<span>'.$cleanDecimal($row->last_ua_value).'</span>';
                } else {
                    $last_ua = '<span class="text-muted">-</span>';
                }

                // Status Badge
                $statusId = $row->clinic_member_status_id ?? '';
                $statusName = $row->clinic_member_status_name ?? 'ไม่ระบุ';
                $badgeColor = match((string)$statusId) {
                    '1' => 'success',
                    '2' => 'warning',
                    '3' => 'secondary',
                    default => 'light'
                };
                $status_badge = '<span class="badge bg-'.$badgeColor.' badge-status">'.$statusName.'</span>';

                $data[] = [
                    'index' => $start + $idx + 1,
                    'hn' => $row->hn,
                    'cid' => $row->cid,
                    'patient_name' => $row->patient_name,
                    'sex_name' => $row->sex_name ?? '-',
                    'pttype_name' => $row->pttype_name ?? '-',
                    'regdate' => $row->regdate ? DateThai($row->regdate) : '-',
                    'lastvisit' => $row->lastvisit ? DateThai($row->lastvisit) : '-',
                    'last_fbs' => $last_fbs,
                    'last_hba1c' => $last_hba1c,
                    'last_ua' => $last_ua,
                    'last_bp_value' => $formatBp($row->last_bp_value),
                    'doctor_name' => $row->doctor_name ?? '-',
                    'status_badge' => $status_badge,
                    'send_pcu_hospital_name' => $row->send_pcu_hospital_name ?? '-'
                ];
            }

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
        }

        return view('hosxp.ncd.register', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'status_summary',
            'new_by_month',
            'config'
        ));
    }

    public function hd_report(Request $request)
    {
        $title = 'รายงานรับบริการผู้ป่วยคลินิกฟอกไต HD';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $ucs_incup_codes = DB::table('lookup_hospcode')->where('hmain_ucs', 'Y')->pluck('hospcode')->toArray();
        if (empty($ucs_incup_codes)) {
            $ucs_incup_codes = ['10989'];
        }
        $ucs_incup_str = "'" . implode("','", $ucs_incup_codes) . "'";

        $ucs_inprov_codes = DB::table('lookup_hospcode')->where('in_province', 'Y')->where(function($q) {
            $q->whereNull('hmain_ucs')->orWhere('hmain_ucs', '<>', 'Y');
        })->pluck('hospcode')->toArray();
        if (empty($ucs_inprov_codes)) {
            $ucs_inprov_codes = ['10703', '10985', '10986', '10987', '10988', '10990'];
        }
        $ucs_inprov_str = "'" . implode("','", $ucs_inprov_codes) . "'";

        $visit_month = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(v.vstdate)=10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=1  THEN CONCAT('ม.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=2  THEN CONCAT('ก.พ. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=3  THEN CONCAT('มี.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=4  THEN CONCAT('เม.ย. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=5  THEN CONCAT('พ.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=6  THEN CONCAT('มิ.ย. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=7  THEN CONCAT('ก.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=8  THEN CONCAT('ส.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=9  THEN CONCAT('ก.ย. ', RIGHT(YEAR(v.vstdate)+543,2))
                END AS month,
                COUNT(v.vn) AS visit,
                COUNT(DISTINCT v.hn) AS hn,
                SUM(v.income) AS income,
                SUM(IFNULL(o.hd_price, 0)) AS inc_hd,
                SUM(IFNULL(o.drug_price, 0)) AS inc_drug,
                
                -- UCS IN-CUP
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN v.hn END) AS ucs_incup_hn,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN 1 ELSE 0 END) AS ucs_incup,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN v.income ELSE 0 END) AS ucs_incup_income,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS ucs_incup_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS ucs_incup_inc_drug,

                -- UCS IN-PROVINCE
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN v.hn END) AS ucs_inprov_hn,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN 1 ELSE 0 END) AS ucs_inprov,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN v.income ELSE 0 END) AS ucs_inprov_income,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS ucs_inprov_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS ucs_inprov_inc_drug,

                -- UCS OUT-OF-PROVINCE
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN v.hn END) AS ucs_outprov_hn,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN 1 ELSE 0 END) AS ucs_outprov,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN v.income ELSE 0 END) AS ucs_outprov_income,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS ucs_outprov_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS ucs_outprov_inc_drug,

                -- OFC
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS ofc_hn,
                SUM(CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS ofc,
                SUM(CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS ofc_income,
                SUM(CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS ofc_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS ofc_inc_drug,

                -- SSS
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS sss_hn,
                SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS sss,
                SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS sss_income,
                SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS sss_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS sss_inc_drug,

                -- LGO
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS lgo_hn,
                SUM(CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS lgo,
                SUM(CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS lgo_income,
                SUM(CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS lgo_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS lgo_inc_drug,

                -- FSS
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS fss_hn,
                SUM(CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS fss,
                SUM(CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS fss_income,
                SUM(CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS fss_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS fss_inc_drug,

                -- STP
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS stp_hn,
                SUM(CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS stp,
                SUM(CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS stp_income,
                SUM(CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS stp_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS stp_inc_drug,

                -- PAY
                COUNT(DISTINCT CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN v.hn END) AS pay_hn,
                SUM(CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN 1 ELSE 0 END) AS pay,
                SUM(CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN v.income ELSE 0 END) AS pay_income,
                SUM(CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS pay_inc_hd,
                SUM(CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS pay_inc_drug
            FROM clinicmember c
            INNER JOIN vn_stat v ON v.hn = c.hn
            LEFT JOIN pttype p ON p.pttype = v.pttype
            LEFT JOIN visit_pttype vp ON vp.vn = v.vn 
            LEFT JOIN (
                SELECT 
                    vn,
                    SUM(CASE WHEN icode IN ('3003375', '3004035') THEN sum_price ELSE 0 END) AS hd_price,
                    SUM(CASE WHEN icode IN ('1630895', '1630897', '1630898', '1630899', '1630907', '1630908') THEN sum_price ELSE 0 END) AS drug_price
                FROM opitemrece
                WHERE icode IN ('3003375', '3004035', '1630895', '1630897', '1630898', '1630899', '1630907', '1630908')
                GROUP BY vn
            ) o ON o.vn = v.vn
            WHERE c.clinic = '013'
              AND v.vstdate BETWEEN ? AND ?
            GROUP BY YEAR(v.vstdate), MONTH(v.vstdate)
            ORDER BY YEAR(v.vstdate), MONTH(v.vstdate)
        ", [$start_date, $end_date]);

        $total_register = DB::connection('hosxp')
            ->table('clinicmember')
            ->where('clinic', '013')
            ->count();

        $months = array_column($visit_month, 'month');
        $visits = array_map('intval', array_column($visit_month, 'visit'));
        $hns = array_map('intval', array_column($visit_month, 'hn'));
        $repeat_visits = array_map(function ($v, $h) {
            return $v - $h;
        }, $visits, $hns);
        $incomes = array_map('floatval', array_column($visit_month, 'income'));
        $inc_hds = array_map('floatval', array_column($visit_month, 'inc_hd'));
        $inc_drugs = array_map('floatval', array_column($visit_month, 'inc_drug'));

        return view('hosxp.ncd.hd_report', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'visit_month',
            'months',
            'visits',
            'hns',
            'repeat_visits',
            'incomes',
            'inc_hds',
            'inc_drugs',
            'total_register'
        ));
    }

    public function hd_private_report(Request $request)
    {
        $title = 'รายงานรับบริการผู้ป่วยคลินิกฟอกไต HD เอกชน';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $ucs_incup_codes = DB::table('lookup_hospcode')->where('hmain_ucs', 'Y')->pluck('hospcode')->toArray();
        if (empty($ucs_incup_codes)) {
            $ucs_incup_codes = ['10989'];
        }
        $ucs_incup_str = "'" . implode("','", $ucs_incup_codes) . "'";

        $ucs_inprov_codes = DB::table('lookup_hospcode')->where('in_province', 'Y')->where(function($q) {
            $q->whereNull('hmain_ucs')->orWhere('hmain_ucs', '<>', 'Y');
        })->pluck('hospcode')->toArray();
        if (empty($ucs_inprov_codes)) {
            $ucs_inprov_codes = ['10703', '10985', '10986', '10987', '10988', '10990'];
        }
        $ucs_inprov_str = "'" . implode("','", $ucs_inprov_codes) . "'";

        $visit_month = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(v.vstdate)=10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=1  THEN CONCAT('ม.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=2  THEN CONCAT('ก.พ. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=3  THEN CONCAT('มี.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=4  THEN CONCAT('เม.ย. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=5  THEN CONCAT('พ.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=6  THEN CONCAT('มิ.ย. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=7  THEN CONCAT('ก.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=8  THEN CONCAT('ส.ค. ', RIGHT(YEAR(v.vstdate)+543,2))
                    WHEN MONTH(v.vstdate)=9  THEN CONCAT('ก.ย. ', RIGHT(YEAR(v.vstdate)+543,2))
                END AS month,
                COUNT(v.vn) AS visit,
                COUNT(DISTINCT v.hn) AS hn,
                SUM(v.income) AS income,
                SUM(IFNULL(o.hd_price, 0)) AS inc_hd,
                SUM(IFNULL(o.drug_price, 0)) AS inc_drug,
                
                -- UCS IN-CUP
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN v.hn END) AS ucs_incup_hn,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN 1 ELSE 0 END) AS ucs_incup,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN v.income ELSE 0 END) AS ucs_incup_income,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS ucs_incup_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_incup_str) THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS ucs_incup_inc_drug,

                -- UCS IN-PROVINCE
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN v.hn END) AS ucs_inprov_hn,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN 1 ELSE 0 END) AS ucs_inprov,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN v.income ELSE 0 END) AS ucs_inprov_income,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS ucs_inprov_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND vp.hospmain IN ($ucs_inprov_str) THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS ucs_inprov_inc_drug,

                -- UCS OUT-OF-PROVINCE
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN v.hn END) AS ucs_outprov_hn,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN 1 ELSE 0 END) AS ucs_outprov,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN v.income ELSE 0 END) AS ucs_outprov_income,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS ucs_outprov_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('UCS','DIS') AND p.paidst NOT IN ('01','03') AND (vp.hospmain IS NULL OR (vp.hospmain NOT IN ($ucs_incup_str) AND vp.hospmain NOT IN ($ucs_inprov_str))) THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS ucs_outprov_inc_drug,

                -- OFC
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS ofc_hn,
                SUM(CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS ofc,
                SUM(CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS ofc_income,
                SUM(CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS ofc_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('OFC','BKK','BMT') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS ofc_inc_drug,

                -- SSS
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS sss_hn,
                SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS sss,
                SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS sss_income,
                SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS sss_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('SSS','SSI') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS sss_inc_drug,

                -- LGO
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS lgo_hn,
                SUM(CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS lgo,
                SUM(CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS lgo_income,
                SUM(CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS lgo_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('LGO') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS lgo_inc_drug,

                -- FSS
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS fss_hn,
                SUM(CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS fss,
                SUM(CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS fss_income,
                SUM(CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS fss_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('NRD','NRH') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS fss_inc_drug,

                -- STP
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN v.hn END) AS stp_hn,
                SUM(CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN 1 ELSE 0 END) AS stp,
                SUM(CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN v.income ELSE 0 END) AS stp_income,
                SUM(CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS stp_inc_hd,
                SUM(CASE WHEN p.hipdata_code IN ('STP') AND p.paidst NOT IN ('01','03') THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS stp_inc_drug,

                -- PAY
                COUNT(DISTINCT CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN v.hn END) AS pay_hn,
                SUM(CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN 1 ELSE 0 END) AS pay,
                SUM(CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN v.income ELSE 0 END) AS pay_income,
                SUM(CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN IFNULL(o.hd_price, 0) ELSE 0 END) AS pay_inc_hd,
                SUM(CASE WHEN (p.paidst IN ('01','03') OR p.hipdata_code IN ('A1','A9')) THEN IFNULL(o.drug_price, 0) ELSE 0 END) AS pay_inc_drug
            FROM vn_stat v
            LEFT JOIN clinicmember c ON c.hn = v.hn AND c.clinic = '013'
            LEFT JOIN pttype p ON p.pttype = v.pttype
            LEFT JOIN visit_pttype vp ON vp.vn = v.vn 
            INNER JOIN (
                SELECT 
                    vn,
                    SUM(CASE WHEN icode IN ('3003375', '3004035') THEN sum_price ELSE 0 END) AS hd_price,
                    SUM(CASE WHEN icode IN ('1630895', '1630897', '1630898', '1630899', '1630907', '1630908') THEN sum_price ELSE 0 END) AS drug_price
                FROM opitemrece
                WHERE icode IN ('3003375', '3004035', '1630895', '1630897', '1630898', '1630899', '1630907', '1630908')
                GROUP BY vn
            ) o ON o.vn = v.vn
            WHERE c.hn IS NULL
              AND v.vstdate BETWEEN ? AND ?
            GROUP BY YEAR(v.vstdate), MONTH(v.vstdate)
            ORDER BY YEAR(v.vstdate), MONTH(v.vstdate)
        ", [$start_date, $end_date]);

        $total_register = DB::connection('hosxp')
            ->table('opitemrece as o')
            ->join('vn_stat as v', 'v.vn', '=', 'o.vn')
            ->leftJoin('clinicmember as c', function($join) {
                $join->on('c.hn', '=', 'v.hn')->where('c.clinic', '013');
            })
            ->whereNull('c.hn')
            ->whereIn('o.icode', ['3003375', '3004035', '1630895', '1630897', '1630898', '1630899', '1630907', '1630908'])
            ->whereBetween('v.vstdate', [$start_date, $end_date])
            ->distinct()
            ->count('v.hn');

        $months = array_column($visit_month, 'month');
        $visits = array_map('intval', array_column($visit_month, 'visit'));
        $hns = array_map('intval', array_column($visit_month, 'hn'));
        $repeat_visits = array_map(function ($v, $h) {
            return $v - $h;
        }, $visits, $hns);
        $incomes = array_map('floatval', array_column($visit_month, 'income'));
        $inc_hds = array_map('floatval', array_column($visit_month, 'inc_hd'));
        $inc_drugs = array_map('floatval', array_column($visit_month, 'inc_drug'));

        return view('hosxp.ncd.hd_private_report', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'visit_month',
            'months',
            'visits',
            'hns',
            'repeat_visits',
            'incomes',
            'inc_hds',
            'inc_drugs',
            'total_register'
        ));
    }

    private function resolveDateRange(Request $request)
    {
        $budget_year_select = DB::table('budget_year')->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')->orderByDesc('LEAVE_YEAR_ID')->limit(7)->get();
        $budget_year_now = DB::table('budget_year')->whereDate('DATE_END', '>=', date('Y-m-d'))->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))->value('LEAVE_YEAR_ID');
        $budget_year = $request->budget_year ?: $budget_year_now;

        if ($request->start_date && $request->end_date && $request->budget_year_changed != '1') {
            $start_date = $request->start_date;
            $end_date   = $request->end_date;
            $matched_year = DB::table('budget_year')
                ->where('DATE_BEGIN', '<=', $start_date)
                ->where('DATE_END', '>=', $start_date)
                ->value('LEAVE_YEAR_ID');
            if ($matched_year) {
                $budget_year = $matched_year;
            }
        } else {
            $year_data = DB::table('budget_year')->where('LEAVE_YEAR_ID', $budget_year)->first();
            if ($year_data) {
                $start_date = $year_data->DATE_BEGIN;
                $end_date   = $year_data->DATE_END;
            } else {
                $start_date = ($budget_year - 543) . '-10-01';
                $end_date   = ($budget_year - 542) . '-09-30';
            }
        }

        return ['start_date' => $start_date, 'end_date' => $end_date, 'budget_year' => $budget_year, 'budget_year_select' => $budget_year_select];
    }
}
