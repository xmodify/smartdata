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

        $pp_icd10s = DB::table('lookup_icd10')->where('pp', 'Y')->pluck('icd10')->toArray();
        if (empty($pp_icd10s)) {
            $pp_icd10s = ['Z00', 'Z000'];
        }
        $pp_icd10s_str = "'" . implode("','", $pp_icd10s) . "'";

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
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND hospmain IN (' . $ucs_incup_str . ') THEN 1 ELSE 0 END) AS "ucs_incup",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND hospmain IN (' . $ucs_incup_str . ') THEN income ELSE 0 END) AS "ucs_incup_income",  
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND hospmain IN (' . $ucs_incup_str . ') THEN inc12 ELSE 0 END) AS "ucs_incup_inc_drug",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND hospmain IN (' . $ucs_incup_str . ') THEN inc03 ELSE 0 END) AS "ucs_incup_inc_lab",
            
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND hospmain IN (' . $ucs_inprov_str . ') THEN 1 ELSE 0 END) AS "ucs_inprov",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND hospmain IN (' . $ucs_inprov_str . ') THEN income ELSE 0 END) AS "ucs_inprov_income",            
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND hospmain IN (' . $ucs_inprov_str . ') THEN inc12 ELSE 0 END) AS "ucs_inprov_inc_drug",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND hospmain IN (' . $ucs_inprov_str . ') THEN inc03 ELSE 0 END) AS "ucs_inprov_inc_lab",

            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND (hospmain IS NULL OR (hospmain NOT IN (' . $ucs_incup_str . ') AND hospmain NOT IN (' . $ucs_inprov_str . '))) THEN 1 ELSE 0 END) AS "ucs_outprov",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND (hospmain IS NULL OR (hospmain NOT IN (' . $ucs_incup_str . ') AND hospmain NOT IN (' . $ucs_inprov_str . '))) THEN income ELSE 0 END) AS "ucs_outprov_income",            
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND (hospmain IS NULL OR (hospmain NOT IN (' . $ucs_incup_str . ') AND hospmain NOT IN (' . $ucs_inprov_str . '))) THEN inc12 ELSE 0 END) AS "ucs_outprov_inc_drug",
            SUM(CASE WHEN hipdata_code IN ("UCS","DIS") AND paidst NOT IN ("01","03") AND (hospmain IS NULL OR (hospmain NOT IN (' . $ucs_incup_str . ') AND hospmain NOT IN (' . $ucs_inprov_str . '))) THEN inc03 ELSE 0 END) AS "ucs_outprov_inc_lab",

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
            IF(v.pdx IN (' . $pp_icd10s_str . '),"PP","OP") AS diagtype,vp.hospmain
            FROM vn_stat v
            LEFT JOIN pttype p ON p.pttype=v.pttype
            LEFT JOIN visit_pttype vp ON vp.vn =v.vn 
            WHERE v.vstdate BETWEEN ? AND ? GROUP BY v.vn) AS a									
            GROUP BY YEAR(vstdate) , MONTH(vstdate)
            ORDER BY YEAR(vstdate) , MONTH(vstdate)', [$start_date, $end_date]);
            
        $months = array_column($visit_month, 'month');
        $visits = array_map('intval', array_column($visit_month, 'visit'));
        $hns = array_map('intval', array_column($visit_month, 'hn'));
        $repeat_visits = array_map(function ($v, $h) {
            return $v - $h;
        }, $visits, $hns);
        $visit_ops = array_map('intval', array_column($visit_month, 'visit_op'));
        $visit_pps = array_map('intval', array_column($visit_month, 'visit_pp'));
        $incomes = array_map('floatval', array_column($visit_month, 'income'));
        $inc_drugs = array_map('floatval', array_column($visit_month, 'inc_drug'));
        $inc_labs = array_map('floatval', array_column($visit_month, 'inc_lab'));

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
            'repeat_visits',
            'visit_ops',
            'visit_pps',
            'incomes',
            'inc_drugs',
            'inc_labs'
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
        }
        else {
            // Use budget_year to get the range
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

    public function waitTime(Request $request)
    {
        $title = 'รายงานระยะเวลารอคอยผู้ป่วยนอก';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Fetch monthly wait times using Optimized Query
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(o.vstdate) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 1 THEN CONCAT('ม.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 2 THEN CONCAT('ก.พ. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 3 THEN CONCAT('มี.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 4 THEN CONCAT('เม.ย. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 5 THEN CONCAT('พ.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 6 THEN CONCAT('มิ.ย. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 7 THEN CONCAT('ก.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 8 THEN CONCAT('ส.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 9 THEN CONCAT('ก.ย. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                END AS month,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, CONCAT(o.vstdate, ' ', o.vsttime), s.begin_time_screen)))), 8) AS screen_wait,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.begin_time_screen, s.end_time_screen)))), 8) AS screen_success,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.end_time_screen, s.begin_time_doctor)))), 8) AS doctor_wait,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.begin_time_doctor, s.end_time_doctor)))), 8) AS doctor_success,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.end_time_doctor, IFNULL(r.end_time_rx, s.end_time_doctor))))), 8) AS rx_success,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, CONCAT(o.vstdate, ' ', o.vsttime), IFNULL(r.end_time_rx, s.end_time_doctor))))), 8) AS success_all,
                -- Minutes calculation for charts
                ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, CONCAT(o.vstdate, ' ', o.vsttime), s.begin_time_screen))) / 60, 1) AS screen_wait_min,
                ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.begin_time_screen, s.end_time_screen))) / 60, 1) AS screen_success_min,
                ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.end_time_screen, s.begin_time_doctor))) / 60, 1) AS doctor_wait_min,
                ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.begin_time_doctor, s.end_time_doctor))) / 60, 1) AS doctor_success_min,
                ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.end_time_doctor, IFNULL(r.end_time_rx, s.end_time_doctor)))) / 60, 1) AS rx_success_min,
                ROUND(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, CONCAT(o.vstdate, ' ', o.vsttime), IFNULL(r.end_time_rx, s.end_time_doctor)))) / 60, 1) AS success_all_min
            FROM ovst o
            INNER JOIN (
                SELECT 
                    vn,
                    MIN(CASE WHEN ovst_service_time_type_code = 'OPD-SCREEN' THEN service_begin_datetime END) AS begin_time_screen,
                    MAX(CASE WHEN ovst_service_time_type_code = 'OPD-SCREEN' THEN service_end_datetime END) AS end_time_screen,
                    MIN(CASE WHEN ovst_service_time_type_code = 'OPD-DOCTOR' THEN service_begin_datetime END) AS begin_time_doctor,
                    MAX(CASE WHEN ovst_service_time_type_code = 'OPD-DOCTOR' THEN service_end_datetime END) AS end_time_doctor
                FROM ovst_service_time
                WHERE ovst_service_time_type_code IN ('OPD-SCREEN', 'OPD-DOCTOR')
                GROUP BY vn
            ) s ON s.vn = o.vn
            LEFT JOIN (
                SELECT vn, MAX(review_finish_datetime) AS end_time_rx
                FROM rx_stat
                WHERE review_finish_datetime IS NOT NULL
                GROUP BY vn
            ) r ON r.vn = o.vn
            WHERE o.vstdate BETWEEN ? AND ?
              AND o.main_dep = '002'
              AND o.vn NOT IN (SELECT vn FROM er_regist)
              AND s.begin_time_screen IS NOT NULL
              AND s.begin_time_doctor IS NOT NULL
              AND DATE(s.begin_time_screen) = o.vstdate
              AND DATE(s.end_time_screen) = o.vstdate
              AND DATE(s.begin_time_doctor) = o.vstdate
              AND DATE(s.end_time_doctor) = o.vstdate
              AND (r.end_time_rx IS NULL OR DATE(r.end_time_rx) = o.vstdate)
            GROUP BY YEAR(o.vstdate), MONTH(o.vstdate)
            ORDER BY YEAR(o.vstdate), MONTH(o.vstdate)
        ", [$start_date, $end_date]);

        // Fetch overall summary wait times
        $summary_stats = DB::connection('hosxp')->select("
            SELECT 
                'รวมทั้งหมด' AS month,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, CONCAT(o.vstdate, ' ', o.vsttime), s.begin_time_screen)))), 8) AS screen_wait,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.begin_time_screen, s.end_time_screen)))), 8) AS screen_success,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.end_time_screen, s.begin_time_doctor)))), 8) AS doctor_wait,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.begin_time_doctor, s.end_time_doctor)))), 8) AS doctor_success,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, s.end_time_doctor, IFNULL(r.end_time_rx, s.end_time_doctor))))), 8) AS rx_success,
                LEFT(SEC_TO_TIME(AVG(GREATEST(0, TIMESTAMPDIFF(SECOND, CONCAT(o.vstdate, ' ', o.vsttime), IFNULL(r.end_time_rx, s.end_time_doctor))))), 8) AS success_all
            FROM ovst o
            INNER JOIN (
                SELECT 
                    vn,
                    MIN(CASE WHEN ovst_service_time_type_code = 'OPD-SCREEN' THEN service_begin_datetime END) AS begin_time_screen,
                    MAX(CASE WHEN ovst_service_time_type_code = 'OPD-SCREEN' THEN service_end_datetime END) AS end_time_screen,
                    MIN(CASE WHEN ovst_service_time_type_code = 'OPD-DOCTOR' THEN service_begin_datetime END) AS begin_time_doctor,
                    MAX(CASE WHEN ovst_service_time_type_code = 'OPD-DOCTOR' THEN service_end_datetime END) AS end_time_doctor
                FROM ovst_service_time
                WHERE ovst_service_time_type_code IN ('OPD-SCREEN', 'OPD-DOCTOR')
                GROUP BY vn
            ) s ON s.vn = o.vn
            LEFT JOIN (
                SELECT vn, MAX(review_finish_datetime) AS end_time_rx
                FROM rx_stat
                WHERE review_finish_datetime IS NOT NULL
                GROUP BY vn
            ) r ON r.vn = o.vn
            WHERE o.vstdate BETWEEN ? AND ?
              AND o.main_dep = '002'
              AND o.vn NOT IN (SELECT vn FROM er_regist)
              AND s.begin_time_screen IS NOT NULL
              AND s.begin_time_doctor IS NOT NULL
              AND DATE(s.begin_time_screen) = o.vstdate
              AND DATE(s.end_time_screen) = o.vstdate
              AND DATE(s.begin_time_doctor) = o.vstdate
              AND DATE(s.end_time_doctor) = o.vstdate
              AND (r.end_time_rx IS NULL OR DATE(r.end_time_rx) = o.vstdate)
        ", [$start_date, $end_date])[0];

        return view('hosxp.opd.wait_time', compact('title', 'budget_year_select', 'budget_year', 'start_date', 'end_date', 'monthly_stats', 'summary_stats'));
    }

    public function telehealth(Request $request)
    {
        $title = 'รายงานการให้บริการแพทย์ทางไกล Telehealth';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Get telehealth non-drug items to find target icodes
        $telmed_icodes = DB::connection('hosxp')
            ->table('nondrugitems')
            ->where('nhso_adp_code', 'TELMED')
            ->pluck('icode')
            ->toArray();

        $icodes_str = count($telmed_icodes) > 0 ? "'" . implode("','", $telmed_icodes) . "'" : "''";

        // Query telehealth visits using the optimized query
        $patients = DB::connection('hosxp')->select("
            SELECT 
                o.vstdate, o.vn, o.oqueue, o.hn, 
                CONCAT(p.pname, p.fname, ' ', p.lname) AS ptname,
                v.age_y, 
                CONCAT(vp.pttype, ' [', p1.hipdata_code, ']') AS pttype, 
                v.pdx, 
                k.department,
                IF(o1.vn IS NOT NULL, 'ตามนัด', '') AS oapp, 
                k1.department AS oapp_dep, 
                c.name AS oapp_clinic,
                d.name AS oapp_doctor, 
                d1.name AS dx_doctor, 
                vp.auth_code,
                o.ovstist,
                IF(EXISTS(SELECT 1 FROM opitemrece op WHERE op.vn = o.vn AND op.icode IN ($icodes_str)), 1, 0) AS has_telmed_charge
            FROM ovst o
            LEFT JOIN (
                SELECT vn, clinic, depcode, doctor, MIN(oapp_id) as oapp_id 
                FROM oapp 
                GROUP BY vn
            ) oapp_grp ON oapp_grp.vn = o.vn
            LEFT JOIN oapp o1 ON o1.oapp_id = oapp_grp.oapp_id
            LEFT JOIN clinic c ON c.clinic = o1.clinic
            LEFT JOIN kskdepartment k ON k.depcode = o.main_dep
            LEFT JOIN kskdepartment k1 ON k1.depcode = o1.depcode
            LEFT JOIN visit_pttype vp ON vp.vn = o.vn
            LEFT JOIN pttype p1 ON p1.pttype = vp.pttype 
            LEFT JOIN vn_stat v ON v.vn = o.vn
            LEFT JOIN patient p ON p.hn = o.hn
            LEFT JOIN doctor d ON d.code = o1.doctor
            LEFT JOIN doctor d1 ON d1.code = v.dx_doctor
            WHERE o.vstdate BETWEEN ? AND ?
              AND (
                  o.ovstist = '12'
                  OR o.vn IN (
                      SELECT vn FROM opitemrece WHERE icode IN ($icodes_str)
                  )
              )
            ORDER BY o.hn, o.vstdate
        ", [$start_date, $end_date]);

        // Query telehealth monthly stats for chart
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(o.vstdate) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 1 THEN CONCAT('ม.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 2 THEN CONCAT('ก.พ. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 3 THEN CONCAT('มี.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 4 THEN CONCAT('เม.ย. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 5 THEN CONCAT('พ.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 6 THEN CONCAT('มิ.ย. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 7 THEN CONCAT('ก.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 8 THEN CONCAT('ส.ค. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 9 THEN CONCAT('ก.ย. ', RIGHT(YEAR(o.vstdate) + 543, 2))
                END AS month,
                COUNT(DISTINCT o.vn) AS total_visits,
                SUM(IF(o.ovstist = '12' AND EXISTS(SELECT 1 FROM opitemrece op WHERE op.vn = o.vn AND op.icode IN ($icodes_str)), 1, 0)) AS complete_count,
                SUM(IF(o.ovstist = '12' AND NOT EXISTS(SELECT 1 FROM opitemrece op WHERE op.vn = o.vn AND op.icode IN ($icodes_str)), 1, 0)) AS type_only_count,
                SUM(IF(o.ovstist != '12' AND EXISTS(SELECT 1 FROM opitemrece op WHERE op.vn = o.vn AND op.icode IN ($icodes_str)), 1, 0)) AS charge_only_count
            FROM ovst o
            WHERE o.vstdate BETWEEN ? AND ?
              AND (
                  o.ovstist = '12'
                  OR o.vn IN (
                      SELECT vn FROM opitemrece WHERE icode IN ($icodes_str)
                  )
              )
            GROUP BY YEAR(o.vstdate), MONTH(o.vstdate)
            ORDER BY YEAR(o.vstdate), MONTH(o.vstdate)
        ", [$start_date, $end_date]);

        return view('hosxp.opd.telehealth', compact('title', 'budget_year_select', 'budget_year', 'start_date', 'end_date', 'patients', 'monthly_stats'));
    }
}
