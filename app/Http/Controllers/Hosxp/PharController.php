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

    public function prescription_count(Request $request)
    {
        $title = 'จำนวนใบสั่งยา';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Prescription Data OPD
        $prescription_opd = DB::connection('hosxp')->select('
            SELECT CASE 
                WHEN MONTH(rxdate)="10" THEN CONCAT("ต.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="11" THEN CONCAT("พ.ย. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="12" THEN CONCAT("ธ.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="1" THEN CONCAT("ม.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="2" THEN CONCAT("ก.พ. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="3" THEN CONCAT("มี.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="4" THEN CONCAT("เม.ย. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="5" THEN CONCAT("พ.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="6" THEN CONCAT("มิ.ย. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="7" THEN CONCAT("ก.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="8" THEN CONCAT("ส.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="9" THEN CONCAT("ก.ย. ",RIGHT(YEAR(rxdate)+543,2))
            END AS month_name, 
            COUNT(DISTINCT vn) AS count, 
            COUNT(icode) AS drug_count,
            ROUND(SUM(qty*cost),2) as sum_cost,
            ROUND(SUM(sum_price),2) as sum_price,
            YEAR(rxdate) as y, MONTH(rxdate) as m
            FROM opitemrece 
            WHERE rxdate BETWEEN ? AND ?
            AND icode LIKE "1%" AND (vn IS NOT NULL AND vn <> "")
            GROUP BY y, m, month_name
            ORDER BY y, m
        ', [$start_date, $end_date]);

        // Prescription Data IPD
        $prescription_ipd = DB::connection('hosxp')->select('
            SELECT CASE 
                WHEN MONTH(rxdate)="10" THEN CONCAT("ต.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="11" THEN CONCAT("พ.ย. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="12" THEN CONCAT("ธ.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="1" THEN CONCAT("ม.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="2" THEN CONCAT("ก.พ. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="3" THEN CONCAT("มี.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="4" THEN CONCAT("เม.ย. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="5" THEN CONCAT("พ.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="6" THEN CONCAT("มิ.ย. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="7" THEN CONCAT("ก.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="8" THEN CONCAT("ส.ค. ",RIGHT(YEAR(rxdate)+543,2))
                WHEN MONTH(rxdate)="9" THEN CONCAT("ก.ย. ",RIGHT(YEAR(rxdate)+543,2))
            END AS month_name, 
            COUNT(DISTINCT order_no) AS count, 
            COUNT(icode) AS drug_count,
            ROUND(SUM(qty*cost),2) as sum_cost,
            ROUND(SUM(sum_price),2) as sum_price,
            YEAR(rxdate) as y, MONTH(rxdate) as m
            FROM opitemrece 
            WHERE rxdate BETWEEN ? AND ?
            AND icode LIKE "1%" AND (an IS NOT NULL AND an <> "")
            GROUP BY y, m, month_name
            ORDER BY y, m
        ', [$start_date, $end_date]);

        return view('hosxp.phar.prescription_count', compact(
            'title', 
            'budget_year_select', 
            'budget_year', 
            'start_date', 
            'end_date',
            'prescription_opd',
            'prescription_ipd'
        ));
    }

    public function top20_diag(Request $request)
    {
        $title = '20 อันดับโรค (Primary Diagnosis)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Top 20 Diag OPD
        $top20_diag_opd = DB::connection('hosxp')->select('
            SELECT CONCAT("[",a.pdx,"] " ,a.name) as name,
            COUNT(DISTINCT a.hn) as hn_count, COUNT(DISTINCT a.vn) as visit_count, 
            SUM(b.sum_cost) AS sum_cost, SUM(b.sum_price) AS sum_price
            FROM
            (SELECT v.vstdate,v.hn,v.vn,v.sex,v.pdx,i.name
            FROM vn_stat v
            LEFT JOIN icd101 i ON i.code=v.pdx
            WHERE v.vstdate BETWEEN ? AND ?
            AND (v.pdx<>"" OR v.pdx IS NOT NULL) AND v.pdx NOT LIKE "z%" AND v.pdx NOT IN ("u119")) AS a
            LEFT JOIN
            (SELECT vn, SUM(qty*cost) as sum_cost, SUM(sum_price) as sum_price
            FROM opitemrece
            WHERE rxdate BETWEEN ? AND ?
            AND icode LIKE "1%" AND vn IS NOT NULL
            GROUP BY vn) AS b ON a.vn=b.vn
            GROUP BY a.pdx, a.name
            ORDER BY visit_count DESC LIMIT 20
        ', [$start_date, $end_date, $start_date, $end_date]);

        // Top 20 Diag IPD
        $top20_diag_ipd = DB::connection('hosxp')->select('
            SELECT CONCAT("[",a.pdx,"] " ,a.name) as name,
            COUNT(DISTINCT a.hn) as hn_count, COUNT(DISTINCT a.an) as visit_count, 
            SUM(b.sum_cost) AS sum_cost, SUM(b.sum_price) AS sum_price
            FROM
            (SELECT a.dchdate,a.hn,a.an,a.sex,a.pdx,i.name
            FROM an_stat a
            LEFT JOIN icd101 i ON i.code=a.pdx
            WHERE a.dchdate BETWEEN ? AND ?
            AND a.ward NOT IN ("06","07")
            AND (a.pdx <> "" AND a.pdx IS NOT NULL) AND a.pdx NOT LIKE "z%" AND a.pdx NOT IN ("u119")) AS a
            LEFT JOIN
            (SELECT an, SUM(qty*cost) as sum_cost, SUM(sum_price) as sum_price
            FROM opitemrece
            WHERE rxdate BETWEEN ? AND ?
            AND icode LIKE "1%" AND an IS NOT NULL AND an <>""
            GROUP BY an) AS b ON a.an=b.an
            GROUP BY a.pdx, a.name
            ORDER BY visit_count DESC LIMIT 20
        ', [$start_date, $end_date, $start_date, $end_date]);

        return view('hosxp.phar.top20_diag', compact(
            'title', 
            'budget_year_select', 
            'budget_year', 
            'start_date', 
            'end_date',
            'top20_diag_opd',
            'top20_diag_ipd'
        ));
    }

    private function resolveDateRange(Request $request)
    {
        $budget_year_select = DB::table('budget_year')->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')->orderByDesc('LEAVE_YEAR_ID')->limit(7)->get();
        $budget_year_now = DB::table('budget_year')->whereDate('DATE_END', '>=', date('Y-m-d'))->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))->value('LEAVE_YEAR_ID');
        $budget_year = $request->budget_year ?: $budget_year_now;

        if ($request->start_date && $request->end_date && $request->budget_year_changed != '1') {
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
