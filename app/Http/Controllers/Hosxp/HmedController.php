<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HmedController extends Controller
{
    public function index(Request $request)
    {
        $title = 'งานแพทย์แผนไทย';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        return view('hosxp.hmed.index', compact('title', 'budget_year_select', 'budget_year', 'start_date', 'end_date'));
    }

    public function service_stats(Request $request)
    {
        $title = 'สถิติผู้รับบริการแพทย์แผนไทย';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // OPD Query
        $stats_opd = DB::connection('hosxp')->select('
            SELECT 
                CASE 
                    WHEN MONTH(vstdate)="10" THEN CONCAT("ต.ค. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="11" THEN CONCAT("พ.ย. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="12" THEN CONCAT("ธ.ค. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="1"  THEN CONCAT("ม.ค. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="2"  THEN CONCAT("ก.พ. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="3"  THEN CONCAT("มี.ค. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="4"  THEN CONCAT("เม.ย. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="5"  THEN CONCAT("พ.ค. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="6"  THEN CONCAT("มิ.ย. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="7"  THEN CONCAT("ก.ค. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="8"  THEN CONCAT("ส.ค. ", RIGHT(YEAR(vstdate)+543, 2))
                    WHEN MONTH(vstdate)="9"  THEN CONCAT("ก.ย. ", RIGHT(YEAR(vstdate)+543, 2))
                END AS month_name,
                COUNT(DISTINCT hn) as total_hn, 
                COUNT(DISTINCT vn) as total_visit,
                SUM(sum_price_service + sum_price_other) AS total_sum_price,
                SUM(sum_price_service) as total_sum_service,
                SUM(sum_price_other) as total_sum_other,
                
                -- UCS
                COUNT(DISTINCT CASE WHEN hipdata_code IN ("UCS") AND paidst NOT IN ("01","03") THEN hn END) AS hn_ucs,
                SUM(CASE WHEN hipdata_code IN ("UCS") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS visit_ucs,
                SUM(CASE WHEN hipdata_code IN ("UCS") AND paidst NOT IN ("01","03") THEN sum_price_service ELSE 0 END) AS sum_price_service_ucs,
                SUM(CASE WHEN hipdata_code IN ("UCS") AND paidst NOT IN ("01","03") THEN sum_price_other ELSE 0 END) AS sum_price_other_ucs,

                -- OFC
                COUNT(DISTINCT CASE WHEN hipdata_code IN ("OFC") AND paidst NOT IN ("01","03") THEN hn END) AS hn_ofc,
                SUM(CASE WHEN hipdata_code IN ("OFC") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS visit_ofc,
                SUM(CASE WHEN hipdata_code IN ("OFC") AND paidst NOT IN ("01","03") THEN sum_price_service ELSE 0 END) AS sum_price_service_ofc,
                SUM(CASE WHEN hipdata_code IN ("OFC") AND paidst NOT IN ("01","03") THEN sum_price_other ELSE 0 END) AS sum_price_other_ofc,

                -- SSS
                COUNT(DISTINCT CASE WHEN hipdata_code IN ("SSS","SSI") AND paidst NOT IN ("01","03") THEN hn END) AS hn_sss,
                SUM(CASE WHEN hipdata_code IN ("SSS","SSI") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS visit_sss,
                SUM(CASE WHEN hipdata_code IN ("SSS","SSI") AND paidst NOT IN ("01","03") THEN sum_price_service ELSE 0 END) AS sum_price_service_sss,
                SUM(CASE WHEN hipdata_code IN ("SSS","SSI") AND paidst NOT IN ("01","03") THEN sum_price_other ELSE 0 END) AS sum_price_other_sss,

                -- LGO
                COUNT(DISTINCT CASE WHEN hipdata_code IN ("LGO") AND paidst NOT IN ("01","03") THEN hn END) AS hn_lgo,
                SUM(CASE WHEN hipdata_code IN ("LGO") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS visit_lgo,
                SUM(CASE WHEN hipdata_code IN ("LGO") AND paidst NOT IN ("01","03") THEN sum_price_service ELSE 0 END) AS sum_price_service_lgo,
                SUM(CASE WHEN hipdata_code IN ("LGO") AND paidst NOT IN ("01","03") THEN sum_price_other ELSE 0 END) AS sum_price_other_lgo,

                -- Pay
                COUNT(DISTINCT CASE WHEN (paidst IN ("01","03") OR hipdata_code IN ("A1","A9")) THEN hn END) AS hn_pay,
                SUM(CASE WHEN (paidst IN ("01","03") OR hipdata_code IN ("A1","A9")) THEN 1 ELSE 0 END) AS visit_pay,
                SUM(CASE WHEN (paidst IN ("01","03") OR hipdata_code IN ("A1","A9")) THEN sum_price_service ELSE 0 END) AS sum_price_service_pay,
                SUM(CASE WHEN (paidst IN ("01","03") OR hipdata_code IN ("A1","A9")) THEN sum_price_other ELSE 0 END) AS sum_price_other_pay

            FROM (
                SELECT 
                    o.vn, o.hn, o.vstdate, 
                    p.hipdata_code, p.paidst,
                    COALESCE(o1.sum_price_service, 0) AS sum_price_service,
                    COALESCE(o1.sum_price_other, 0) AS sum_price_other
                FROM ovst o 
                LEFT JOIN (
                    SELECT vn, pttype FROM visit_pttype GROUP BY vn, pttype
                ) vp ON vp.vn = o.vn
                LEFT JOIN pttype p ON p.pttype = COALESCE(vp.pttype, o.pttype)
                LEFT JOIN (
                    SELECT 
                        ot.vn, ot.pttype, 
                        SUM(ot.sum_price) AS sum_price_service,
                        0 AS sum_price_other
                    FROM opitemrece ot
                    INNER JOIN nondrugitems n ON n.icode = ot.icode
                    WHERE ot.vstdate BETWEEN ? AND ? 
                      AND n.income = "15"
                      AND EXISTS (SELECT 1 FROM health_med_service hms2 WHERE hms2.vn = ot.vn)
                    GROUP BY ot.vn, ot.pttype 
                ) o1 ON o1.vn = o.vn AND o1.pttype = COALESCE(vp.pttype, o.pttype)
                WHERE o.vstdate BETWEEN ? AND ?
                  AND EXISTS (SELECT 1 FROM health_med_service hms WHERE hms.vn = o.vn)
            ) AS a                        
            GROUP BY YEAR(vstdate), MONTH(vstdate)
            ORDER BY YEAR(vstdate), MONTH(vstdate)
        ', [$start_date, $end_date, $start_date, $end_date]);

        return view('hosxp.hmed.service_stats', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'stats_opd'
        ));
    }

    public function top20_diag(Request $request)
    {
        $title = '20 อันดับโรค แพทย์แผนไทย';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $diag_top20 = DB::connection('hosxp')->select('
            SELECT 
                v.pdx AS code,
                i.name AS name,
                i.tname AS tname,
                CONCAT("[",v.pdx,"] " ,i.name) AS full_name,
                COUNT(v.pdx) AS sum, 
                SUM(CASE WHEN v.sex=1 THEN 1 ELSE 0 END) AS male,   
                SUM(CASE WHEN v.sex=2 THEN 1 ELSE 0 END) AS female   
            FROM vn_stat v   
            LEFT OUTER JOIN icd101 i ON i.code=v.pdx 
            WHERE v.vstdate BETWEEN ? AND ?
            AND EXISTS (SELECT 1 FROM health_med_service hms WHERE hms.vn = v.vn)            
            GROUP BY v.pdx, i.name, i.tname  
            ORDER BY sum DESC 
            LIMIT 20
        ', [$start_date, $end_date]);

        return view('hosxp.hmed.top20_diag', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'diag_top20'
        ));
    }

    public function service_value(Request $request)
    {
        $title = 'มูลค่าการให้บริการแพทย์แผนไทย';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // 1. แพทย์แผนไทย (Income 15)
        $data_hmed = DB::connection('hosxp')->select('
            SELECT 
                n.`name`,
                ot.icode,
                SUM(ot.qty) AS qty,
                ot.unitprice,
                SUM(ot.sum_price) AS sum_price,
                SUM(CASE WHEN p.sex = "1" THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN p.sex = "2" THEN 1 ELSE 0 END) as female
            FROM ovst o
            INNER JOIN opitemrece ot ON ot.vn = o.vn
            INNER JOIN nondrugitems n ON n.icode = ot.icode
            INNER JOIN patient p ON p.hn = o.hn
            WHERE o.vstdate BETWEEN ? AND ?
              AND ot.rxdate BETWEEN ? AND ?
              AND EXISTS (SELECT 1 FROM health_med_service hms WHERE hms.vn = o.vn)
              AND n.income IN ("15")
            GROUP BY ot.icode
            ORDER BY sum_price DESC
        ', [$start_date, $end_date, $start_date, $end_date]);

        // 2. รายการอื่น ๆ (Income <> 15 ใน HMS)
        $data_other = DB::connection('hosxp')->select('
            SELECT 
                n.`name`,
                ot.icode,
                SUM(ot.qty) AS qty,
                ot.unitprice,
                SUM(ot.sum_price) AS sum_price,
                SUM(CASE WHEN p.sex = "1" THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN p.sex = "2" THEN 1 ELSE 0 END) as female
            FROM ovst o
            INNER JOIN opitemrece ot ON ot.vn = o.vn
            INNER JOIN nondrugitems n ON n.icode = ot.icode
            INNER JOIN patient p ON p.hn = o.hn
            WHERE o.vstdate BETWEEN ? AND ?
              AND ot.rxdate BETWEEN ? AND ?
              AND EXISTS (SELECT 1 FROM health_med_service hms WHERE hms.vn = o.vn)
              AND n.income NOT IN ("15")
            GROUP BY ot.icode
            ORDER BY sum_price DESC
            LIMIT 100
        ', [$start_date, $end_date, $start_date, $end_date]);

        return view('hosxp.hmed.service_value', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'data_hmed', 'data_other'
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
                $start_date = ($budget_year - 544) . '-10-01';
                $end_date = ($budget_year - 543) . '-09-30';
            }
        }

        return ['start_date' => $start_date, 'end_date' => $end_date, 'budget_year' => $budget_year, 'budget_year_select' => $budget_year_select];
    }
}
