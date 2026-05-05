<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PharController extends Controller
{
    public function index(Request $request)
    {
        $title = 'งานเภสัชกรรม';
        return view('hosxp.phar.index', compact('title'));
    }

    public function top20_value(Request $request)
    {
        $title = '20 อันดับมูลค่าการใช้ยา';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Summary Data from HOSxP
        $summary_opd = DB::connection('hosxp')->selectOne('
            SELECT ROUND(SUM(sum_price),2) as value, COUNT(DISTINCT vn) as count
            FROM opitemrece
            WHERE rxdate BETWEEN ? AND ?
            AND icode LIKE "1%" AND vn IS NOT NULL
        ', [$start_date, $end_date]);

        $summary_ipd = DB::connection('hosxp')->selectOne('
            SELECT ROUND(SUM(o.sum_price),2) as value, COUNT(DISTINCT o.an) as count
            FROM opitemrece o
            LEFT JOIN an_stat a ON a.an = o.an
            WHERE o.rxdate BETWEEN ? AND ?
            AND o.icode LIKE "1%" AND o.an IS NOT NULL
        ', [$start_date, $end_date]);

        $summary = [
            'total_value' => ($summary_opd->value ?? 0) + ($summary_ipd->value ?? 0),
            'opd_value' => $summary_opd->value ?? 0,
            'ipd_value' => $summary_ipd->value ?? 0,
            'presc_count' => ($summary_opd->count ?? 0) + ($summary_ipd->count ?? 0),
        ];

        // Top 20 OPD Drugs
        $top20_opd = DB::connection('hosxp')->select('
            SELECT d.name, ROUND(SUM(o.sum_price),2) as value, SUM(o.qty) as qty
            FROM opitemrece o
            JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
            AND o.icode LIKE "1%" AND o.vn IS NOT NULL
            GROUP BY d.name
            ORDER BY value DESC
            LIMIT 20
        ', [$start_date, $end_date]);

        // Top 20 IPD Drugs
        $top20_ipd = DB::connection('hosxp')->select('
            SELECT d.name, ROUND(SUM(o.sum_price),2) as value, SUM(o.qty) as qty
            FROM opitemrece o
            JOIN drugitems d ON d.icode = o.icode
            LEFT JOIN an_stat a ON a.an = o.an
            WHERE o.rxdate BETWEEN ? AND ?
            AND o.icode LIKE "1%" AND o.an IS NOT NULL
            GROUP BY d.name
            ORDER BY value DESC
            LIMIT 20
        ', [$start_date, $end_date]);

        return view('hosxp.phar.top20_value', compact(
            'title', 
            'budget_year_select', 
            'budget_year', 
            'start_date', 
            'end_date',
            'summary',
            'top20_opd',
            'top20_ipd'
        ));
    }

    private function resolveDateRange(Request $request)
    {
        $budget_year_select = DB::table('budget_year')->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')->orderByDesc('LEAVE_YEAR_ID')->limit(7)->get();
        $budget_year_now = DB::table('budget_year')->whereDate('DATE_END', '>=', date('Y-m-d'))->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))->value('LEAVE_YEAR_ID');
        $budget_year = $request->budget_year ?: $budget_year_now;

        if ($request->start_date && $request->end_date && !$request->has('budget_year_changed')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            // Sync budget_year to the start_date provided
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
                $end_date = $year_data->DATE_END;
            } else {
                $start_date = ($budget_year - 543) . '-10-01';
                $end_date = ($budget_year - 542) . '-09-30';
            }
        }

        return ['start_date' => $start_date, 'end_date' => $end_date, 'budget_year' => $budget_year, 'budget_year_select' => $budget_year_select];
    }
}
