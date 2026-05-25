<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErController extends Controller
{
    public function index(Request $request)
    {
        $title = 'รายงานอุบัติเหตุ-ฉุกเฉิน (ER)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // 1. Monthly ER Statistics by Severity
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(e.vstdate) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 1 THEN CONCAT('ม.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 2 THEN CONCAT('ก.พ. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 3 THEN CONCAT('มี.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 4 THEN CONCAT('เม.ย. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 5 THEN CONCAT('พ.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 6 THEN CONCAT('มิ.ย. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 7 THEN CONCAT('ก.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 8 THEN CONCAT('ส.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 9 THEN CONCAT('ก.ย. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                END AS 'month_year',
                COUNT(DISTINCT e.vn) AS 'total_visit',
                SUM(CASE WHEN e.er_emergency_type = 1 THEN 1 ELSE 0 END) AS 'level_1',
                SUM(CASE WHEN e.er_emergency_type = 2 THEN 1 ELSE 0 END) AS 'level_2',
                SUM(CASE WHEN e.er_emergency_type = 3 THEN 1 ELSE 0 END) AS 'level_3',
                SUM(CASE WHEN e.er_emergency_type = 4 THEN 1 ELSE 0 END) AS 'level_4',
                SUM(CASE WHEN e.er_emergency_type = 5 THEN 1 ELSE 0 END) AS 'level_5',
                SUM(CASE WHEN e.er_emergency_type IS NULL OR e.er_emergency_type NOT IN (1,2,3,4,5) THEN 1 ELSE 0 END) AS 'level_null'
            FROM er_regist e
            WHERE e.vstdate BETWEEN ? AND ?
            GROUP BY YEAR(e.vstdate), MONTH(e.vstdate)
            ORDER BY YEAR(e.vstdate), MONTH(e.vstdate)
        ", [$start_date, $end_date]);

        // 2. Summary Statistics for Cards
        $summary_stats = DB::connection('hosxp')->selectOne("
            SELECT 
                COUNT(DISTINCT e.vn) AS 'total_visit',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 1 THEN 1 ELSE 0 END), 0) AS 'level_1',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 2 THEN 1 ELSE 0 END), 0) AS 'level_2',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 3 THEN 1 ELSE 0 END), 0) AS 'level_3',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 4 THEN 1 ELSE 0 END), 0) AS 'level_4',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 5 THEN 1 ELSE 0 END), 0) AS 'level_5',
                IFNULL(SUM(CASE WHEN e.er_emergency_type IS NULL OR e.er_emergency_type NOT IN (1,2,3,4,5) THEN 1 ELSE 0 END), 0) AS 'level_null'
            FROM er_regist e
            WHERE e.vstdate BETWEEN ? AND ?
        ", [$start_date, $end_date]);

        // 3. Dynamic severity level names
        $severity_types = DB::connection('hosxp')->table('er_emergency_type')
            ->select('er_emergency_type', 'name')
            ->orderBy('er_emergency_type')
            ->get()
            ->keyBy('er_emergency_type');

        return view('hosxp.er.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'monthly_stats', 'summary_stats', 'severity_types'
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
        } else {
            $year_data = DB::table('budget_year')
                ->where('LEAVE_YEAR_ID', $budget_year)
                ->first();

            if ($year_data) {
                $start_date = $year_data->DATE_BEGIN;
                $end_date = $year_data->DATE_END;
            } else {
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
