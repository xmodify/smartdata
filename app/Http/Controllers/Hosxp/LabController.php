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
            'total_income'
        ));
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
