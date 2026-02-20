<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdController extends Controller
{
    public function index(Request $request)
    {
        $title = 'งานบริการผู้ป่วยนอก (OPD)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Monthly Stats SQL (User's Query)
        $visit_month = DB::connection('hosxp')->select('
            SELECT CASE WHEN MONTH(vstdate)="10" THEN CONCAT("ต.ค. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="11" THEN CONCAT("พ.ย. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="12" THEN CONCAT("ธ.ค. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="1" THEN CONCAT("ม.ค. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="2" THEN CONCAT("ก.พ. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="3" THEN CONCAT("มี.ค. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="4" THEN CONCAT("เม.ย. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="5" THEN CONCAT("พ.ค. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="6" THEN CONCAT("มิ.ย. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="7" THEN CONCAT("ก.ค. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="8" THEN CONCAT("ส.ค. ",RIGHT(YEAR(vstdate)+543,2))
            WHEN MONTH(vstdate)="9" THEN CONCAT("ก.ย. ",RIGHT(YEAR(vstdate)+543,2))
            END AS "month",COUNT(vn) AS "visit",COUNT(DISTINCT hn) AS "hn",
            SUM(CASE WHEN diagtype ="OP" THEN 1 ELSE 0 END) AS "visit_op",
            SUM(CASE WHEN diagtype ="PP" THEN 1 ELSE 0 END) AS "visit_pp",SUM(income) AS "income",
            SUM(inc12) AS "inc_drug",SUM(inc03) AS "inc_lab",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND incup = "Y" THEN 1 ELSE 0 END) AS "ucs_incup",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND incup = "Y" THEN income ELSE 0 END) AS "ucs_incup_income",  
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND incup = "N" THEN 1 ELSE 0 END) AS "ucs_outcup",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND incup = "N" THEN income ELSE 0 END) AS "ucs_outcup_income",            
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") THEN inc12 ELSE 0 END) AS "ucs_inc_drug",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") THEN inc03 ELSE 0 END) AS "ucs_inc_lab",
            SUM(CASE WHEN hipdata_code IN ("OFC","BKK","BMT") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS "ofc",
            SUM(CASE WHEN hipdata_code IN ("OFC","BKK","BMT") AND paidst NOT IN ("01","03") THEN income ELSE 0 END) AS "ofc_income",
            SUM(CASE WHEN hipdata_code IN ("OFC","BKK","BMT") AND paidst NOT IN ("01","03") THEN inc12 ELSE 0 END) AS "ofc_inc_drug",
            SUM(CASE WHEN hipdata_code IN ("OFC","BKK","BMT") AND paidst NOT IN ("01","03") THEN inc03 ELSE 0 END) AS "ofc_inc_lab",            
            SUM(CASE WHEN hipdata_code IN ("SSS","SSI") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS "sss",
            SUM(CASE WHEN hipdata_code IN ("SSS","SSI") AND paidst NOT IN ("01","03") THEN income ELSE 0 END) AS "sss_income",
            SUM(CASE WHEN hipdata_code IN ("SSS","SSI") AND paidst NOT IN ("01","03") THEN inc12 ELSE 0 END) AS "sss_inc_drug",
            SUM(CASE WHEN hipdata_code IN ("SSS","SSI") AND paidst NOT IN ("01","03") THEN inc03 ELSE 0 END) AS "sss_inc_lab",            
            SUM(CASE WHEN hipdata_code IN ("LGO") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS "lgo",
            SUM(CASE WHEN hipdata_code IN ("LGO") AND paidst NOT IN ("01","03") THEN income ELSE 0 END) AS "lgo_income",
            SUM(CASE WHEN hipdata_code IN ("LGO") AND paidst NOT IN ("01","03") THEN inc12 ELSE 0 END) AS "lgo_inc_drug",
            SUM(CASE WHEN hipdata_code IN ("LGO") AND paidst NOT IN ("01","03") THEN inc03 ELSE 0 END) AS "lgo_inc_lab",            
            SUM(CASE WHEN hipdata_code IN ("NRD","NRH") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS "fss",
            SUM(CASE WHEN hipdata_code IN ("NRD","NRH") AND paidst NOT IN ("01","03") THEN income ELSE 0 END) AS "fss_income",
            SUM(CASE WHEN hipdata_code IN ("NRD","NRH") AND paidst NOT IN ("01","03") THEN inc12 ELSE 0 END) AS "fss_inc_drug",
            SUM(CASE WHEN hipdata_code IN ("NRD","NRH") AND paidst NOT IN ("01","03") THEN inc03 ELSE 0 END) AS "fss_inc_lab",            
            SUM(CASE WHEN hipdata_code IN ("STP") AND paidst NOT IN ("01","03") THEN 1 ELSE 0 END) AS "stp",   
            SUM(CASE WHEN hipdata_code IN ("STP") AND paidst NOT IN ("01","03") THEN income ELSE 0 END) AS "stp_income",
            SUM(CASE WHEN hipdata_code IN ("STP") AND paidst NOT IN ("01","03") THEN inc12 ELSE 0 END) AS "stp_inc_drug", 
            SUM(CASE WHEN hipdata_code IN ("STP") AND paidst NOT IN ("01","03") THEN inc03 ELSE 0 END) AS "stp_inc_lab",
            SUM(CASE WHEN (paidst IN ("01","03") OR hipdata_code IN ("A1","A9")) THEN 1 ELSE 0 END) AS "pay",
            SUM(CASE WHEN (paidst IN ("01","03") OR hipdata_code IN ("A1","A9")) THEN income ELSE 0 END) AS "pay_income",
            SUM(CASE WHEN (paidst IN ("01","03") OR hipdata_code IN ("A1","A9")) THEN inc12 ELSE 0 END) AS "pay_inc_drug",
            SUM(CASE WHEN (paidst IN ("01","03") OR hipdata_code IN ("A1","A9")) THEN inc03 ELSE 0 END) AS "pay_inc_lab"            
            FROM (SELECT v.vstdate,v.vn,v.hn,v.pttype,p.hipdata_code,p.paidst,v.income,v.inc03,v.inc12 ,v.pdx,
            IF(i.icd10 IS NULL,"OP","PP") AS diagtype,IF(vp.hospmain IS NOT NULL,"Y","N") AS incup
            FROM vn_stat v
            LEFT JOIN pttype p ON p.pttype=v.pttype
            LEFT JOIN visit_pttype vp ON vp.vn =v.vn 
              AND vp.hospmain IN (SELECT hospcode FROM hrims.lookup_hospcode WHERE hmain_ucs = "Y")
            LEFT JOIN hrims.lookup_icd10 i ON i.icd10=v.pdx AND i.pp="Y"	
            WHERE v.vstdate BETWEEN ? AND ? GROUP BY v.vn) AS a									
            GROUP BY YEAR(vstdate) , MONTH(vstdate)
            ORDER BY YEAR(vstdate) , MONTH(vstdate)', [$start_date, $end_date]);

        $months = array_column($visit_month, 'month');
        $visits = array_map('intval', array_column($visit_month, 'visit'));
        $hns = array_map('intval', array_column($visit_month, 'hn'));
        $visit_ops = array_map('intval', array_column($visit_month, 'visit_op'));
        $visit_pps = array_map('intval', array_column($visit_month, 'visit_pp'));
        $incomes = array_map('floatval', array_column($visit_month, 'income'));

        return view('hosxp.opd.index', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'visit_month',
            'months',
            'visits',
            'hns',
            'visit_ops',
            'visit_pps',
            'incomes'
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

        // If start_date and end_date are provided, use them.
        // BUT if the user explicitly provided a budget_year that is different from 
        // the one implied by the custom dates, we should probably check that.
        // However, a simpler fix for the user: if they clicked Search with a specific year,
        // and they DID NOT change the dates manually, they expect the year bounds.

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
            // Use budget_year to get the range
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
