<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $budget_year_select = DB::connection('backoffice')->select('select LEAVE_YEAR_ID,LEAVE_YEAR_NAME FROM budget_year ORDER BY LEAVE_YEAR_ID DESC LIMIT 7');
        $budget_year_last = DB::connection('backoffice')->table('budget_year')->where('DATE_END','>=',date('Y-m-d'))->where('DATE_BEGIN','<=',date('Y-m-d'))->value('LEAVE_YEAR_ID');
        
        $budget_year = $request->budget_year;
        if($budget_year == '' || $budget_year == null) {
            $budget_year = $budget_year_last;
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if ($start_date == '' || $end_date == '') {
            $start_date = DB::connection('backoffice')->table('budget_year')->where('LEAVE_YEAR_ID',$budget_year)->value('DATE_BEGIN');
            $end_date = DB::connection('backoffice')->table('budget_year')->where('LEAVE_YEAR_ID',$budget_year)->value('DATE_END');
        }

        // Fallback dates in case DB has no values
        if (!$start_date) {
            $start_date = (intval($budget_year) - 544) . "-10-01";
        }
        if (!$end_date) {
            $end_date = (intval($budget_year) - 543) . "-09-30";
        }
        
        $risk_clinic = DB::connection('backoffice')->select('select 
                MONTH(a.RISKREP_DATESAVE) AS month_num,
                YEAR(a.RISKREP_DATESAVE) AS year_num,
                CASE WHEN MONTH(a.RISKREP_DATESAVE)="10" THEN CONCAT("ต.ค. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="11" THEN CONCAT("พ.ย. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="12" THEN CONCAT("ธ.ค. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="1" THEN CONCAT("ม.ค. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="2" THEN CONCAT("ก.พ. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="3" THEN CONCAT("มี.ค. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="4" THEN CONCAT("เม.ย. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="5" THEN CONCAT("พ.ค. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="6" THEN CONCAT("มิ.ย. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="7" THEN CONCAT("ก.ค. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="8" THEN CONCAT("ส.ค. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                WHEN MONTH(a.RISKREP_DATESAVE)="9" THEN CONCAT("ก.ย. ",RIGHT(YEAR(a.RISKREP_DATESAVE)+543,2))
                END AS "month",
                SUM(CASE WHEN a.RISK_REPPROGRAMSUB_DETAIL = "Clinical" THEN 1 ELSE 0 END) AS "clinical",
                SUM(CASE WHEN a.RISK_REPPROGRAMSUB_DETAIL = "General" THEN 1 ELSE 0 END) AS "general",            
                COUNT(DISTINCT a.RISKREP_ID) AS total,
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "A" THEN 1 ELSE 0 END) AS "a",
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "B" THEN 1 ELSE 0 END) AS "b", 	
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "C" THEN 1 ELSE 0 END) AS "c", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "D" THEN 1 ELSE 0 END) AS "d", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "E" THEN 1 ELSE 0 END) AS "e", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "F" THEN 1 ELSE 0 END) AS "f", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "G" THEN 1 ELSE 0 END) AS "g", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "H" THEN 1 ELSE 0 END) AS "h", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "I" THEN 1 ELSE 0 END) AS "i",
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "1" THEN 1 ELSE 0 END) AS "g1", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "2" THEN 1 ELSE 0 END) AS "g2", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "3" THEN 1 ELSE 0 END) AS "g3", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "4" THEN 1 ELSE 0 END) AS "g4", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "5" THEN 1 ELSE 0 END) AS "g5",
                SUM(CASE WHEN (a.RISK_REP_LEVEL_NAME = "" OR a.RISK_REP_LEVEL_NAME IS NULL) THEN 1 ELSE 0 END) AS "null",
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME IN ("A","B","1") THEN 1 ELSE 0 END) AS "near_miss",
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME IN ("C","D","2") THEN 1 ELSE 0 END) AS "low_risk",
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME IN ("E","F","3") THEN 1 ELSE 0 END) AS "moderate_risk",
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME IN ("G","H","4","5") THEN 1 ELSE 0 END) AS "high_risk"
                FROM (SELECT r.RISKREP_ID,r.RISKREP_DETAILRISK,r.RISKREP_LEVEL,
                r.RISKREP_DATESAVE,l.RISK_REP_LEVEL_NAME,
                r.RISK_REPPROGRAM_ID,r.RISK_REPPROGRAMSUB_ID,
                ps.RISK_REPPROGRAMSUB_NAME,ps.RISK_REPPROGRAMSUB_DETAIL
                FROM risk_rep r
                LEFT JOIN risk_rep_program_sub ps ON ps.RISK_REPPROGRAMSUB_ID=r.RISK_REPPROGRAMSUB_ID                
                LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID=r.RISKREP_LEVEL 
                WHERE r.RISKREP_DATESAVE BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND r.RISKREP_STATUS <> "CANCEL"
                GROUP BY r.RISKREP_ID) AS a
                GROUP BY MONTH(a.RISKREP_DATESAVE)
                ORDER BY YEAR(a.RISKREP_DATESAVE) , MONTH(a.RISKREP_DATESAVE)'); 
                
        $risk_clinic_m = array_column($risk_clinic,'month');              
        $risk_clinical = array_column($risk_clinic,'clinical');
        $risk_general = array_column($risk_clinic,'general');
        $risk_lavel_near_miss = array_column($risk_clinic,'near_miss'); 
        $risk_lavel_low_risk = array_column($risk_clinic,'low_risk');
        $risk_lavel_moderate_risk = array_column($risk_clinic,'moderate_risk'); 
        $risk_lavel_high_risk = array_column($risk_clinic,'high_risk');   

        $risk_clinic_year = DB::connection('backoffice')->select('select 
                SUM(CASE WHEN a.RISK_REPPROGRAMSUB_DETAIL = "Clinical" THEN 1 ELSE 0 END) AS "clinical",
                SUM(CASE WHEN a.RISK_REPPROGRAMSUB_DETAIL = "General" THEN 1 ELSE 0 END) AS "general",
                SUM(CASE WHEN a.RISK_REPPROGRAMSUB_DETAIL = "" OR a.RISK_REPPROGRAMSUB_DETAIL IS NULL THEN 1 ELSE 0 END) AS "null"
                FROM (SELECT r.RISKREP_ID,r.RISKREP_DETAILRISK,r.RISKREP_LEVEL,
                r.RISKREP_DATESAVE,r.RISK_REPPROGRAM_ID,r.RISK_REPPROGRAMSUB_ID,
                ps.RISK_REPPROGRAMSUB_NAME,ps.RISK_REPPROGRAMSUB_DETAIL
                FROM risk_rep r                
                LEFT JOIN risk_rep_program_sub ps ON ps.RISK_REPPROGRAMSUB_ID=r.RISK_REPPROGRAMSUB_ID 
                WHERE r.RISKREP_DATESAVE BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND r.RISKREP_STATUS <> "CANCEL"
                GROUP BY r.RISKREP_ID) AS a');
                
        $risk_clinical_y = 0;
        $risk_general_y = 0;
        $risk_null_y = 0;
        foreach ($risk_clinic_year as $row){
            $risk_clinical_y = $row->clinical; 
            $risk_general_y = $row->general;  
            $risk_null_y = $row->null; 
        }

        $risk_program = DB::connection('backoffice')->select('select 
                a.RISK_REPPROGRAM_ID AS id,a.RISK_REPPROGRAM_NAME,                 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "A" THEN 1 ELSE 0 END) AS "a",
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "B" THEN 1 ELSE 0 END) AS "b", 	
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "C" THEN 1 ELSE 0 END) AS "c", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "D" THEN 1 ELSE 0 END) AS "d", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "E" THEN 1 ELSE 0 END) AS "e", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "F" THEN 1 ELSE 0 END) AS "f", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "G" THEN 1 ELSE 0 END) AS "g", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "H" THEN 1 ELSE 0 END) AS "h", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "I" THEN 1 ELSE 0 END) AS "i",
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "1" THEN 1 ELSE 0 END) AS "g1", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "2" THEN 1 ELSE 0 END) AS "g2", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "3" THEN 1 ELSE 0 END) AS "g3", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "4" THEN 1 ELSE 0 END) AS "g4", 
                SUM(CASE WHEN a.RISK_REP_LEVEL_NAME = "5" THEN 1 ELSE 0 END) AS "g5",
                SUM(CASE WHEN (a.RISK_REP_LEVEL_NAME = "" OR a.RISK_REP_LEVEL_NAME IS NULL) THEN 1 ELSE 0 END) AS "null",
                COUNT(DISTINCT a.RISKREP_ID) AS total
                FROM (SELECT r.RISKREP_ID,r.RISKREP_DETAILRISK,r.RISKREP_LEVEL,r.RISKREP_DATESAVE,l.RISK_REP_LEVEL_NAME,
                IF(p.RISK_REPPROGRAM_ID IS NULL,"0",p.RISK_REPPROGRAM_ID) AS RISK_REPPROGRAM_ID,
                IF((p.RISK_REPPROGRAM_NAME="" OR p.RISK_REPPROGRAM_NAME IS NULL),"Non-Program",
                p.RISK_REPPROGRAM_NAME) AS RISK_REPPROGRAM_NAME         
                FROM risk_rep r
                LEFT JOIN risk_rep_program p ON p.RISK_REPPROGRAM_ID=r.RISK_REPPROGRAM_ID                
                LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID=r.RISKREP_LEVEL 
                WHERE r.RISKREP_DATESAVE BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND r.RISKREP_STATUS <> "CANCEL"
                GROUP BY r.RISKREP_ID  ) AS a
                GROUP BY a.RISK_REPPROGRAM_NAME
                ORDER BY a.RISK_REPPROGRAM_NAME');

        $risk_matrix = DB::connection('backoffice')->select('select 
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c1_1",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c1_2",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c1_3",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c1_4",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c1_5",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c2_1",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c2_2",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c2_3",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c2_4",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c2_5",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c3_1",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c3_2",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c3_3",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c3_4",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c3_5",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c4_1",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c4_2",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c4_3",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c4_4",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c4_5",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c5_1",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c5_2",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c5_3",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c5_4",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="clinical" THEN 1 ELSE 0 END) AS "c5_5",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g1_1",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g1_2",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g1_3",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g1_4",
                SUM(CASE WHEN a.consequence="1" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g1_5",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g2_1",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g2_2",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g2_3",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g2_4",
                SUM(CASE WHEN a.consequence="2" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g2_5",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g3_1",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g3_2",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g3_3",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g3_4",
                SUM(CASE WHEN a.consequence="3" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g3_5",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g4_1",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g4_2",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g4_3",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g4_4",
                SUM(CASE WHEN a.consequence="4" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g4_5",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="1" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g5_1",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="2" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g5_2",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="3" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g5_3",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="4" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g5_4",
                SUM(CASE WHEN a.consequence="5" AND a.likelihood="5" AND LOWER(a.RISK_REPPROGRAMSUB_DETAIL)="general" THEN 1 ELSE 0 END) AS "g5_5"
                FROM (SELECT r.RISKREP_ID,r.RISKREP_DATESAVE,DATE(NOW()) AS date_now,DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) AS date_count,
                CASE WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE)  BETWEEN 0 AND 30 THEN "5"
                WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) BETWEEN 31 AND 183 THEN "4"
                WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) BETWEEN 184 AND 730 THEN "3"
                WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) BETWEEN 731 AND 1825 THEN "2" 
                WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) > 1825 THEN "1" END AS "likelihood",
                l.RISK_REP_LEVEL_NAME,CASE WHEN l.RISK_REP_LEVEL_NAME IN ("I","5") THEN "5" 
                WHEN l.RISK_REP_LEVEL_NAME IN ("G","H","4") THEN "4" 
                WHEN l.RISK_REP_LEVEL_NAME IN ("E","F","3") THEN "3" 
                WHEN l.RISK_REP_LEVEL_NAME IN ("B","C","D","2") THEN "2" 
                WHEN l.RISK_REP_LEVEL_NAME IN ("A","1")THEN "1" END AS "consequence",            
                ps.RISK_REPPROGRAMSUB_NAME,ps.RISK_REPPROGRAMSUB_DETAIL
                FROM risk_rep r            
                LEFT JOIN risk_rep_program_sub ps ON ps.RISK_REPPROGRAMSUB_ID=r.RISK_REPPROGRAMSUB_ID            
                LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID=r.RISKREP_LEVEL
                WHERE r.RISKREP_STATUS <> "CANCEL" AND r.RISKREP_DATESAVE BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                GROUP BY r.RISKREP_ID) AS a');
                
        $matrix = [];
        if (!empty($risk_matrix)) {
            $matrix = (array) $risk_matrix[0];
        }

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);
        Session::put('budget_year', $budget_year);
        Session::save();

        return view('backoffice.incident.index', compact(
            'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'risk_clinic_m', 'risk_clinical', 'risk_general', 
            'risk_clinical_y', 'risk_general_y', 'risk_null_y', 'risk_clinic', 
            'risk_lavel_near_miss', 'risk_lavel_low_risk', 'risk_lavel_moderate_risk', 'risk_lavel_high_risk',
            'risk_program', 'matrix'
        ));          
    }

    public function med_error(Request $request)
    {
        $budget_year_select = DB::connection('backoffice')->select('select LEAVE_YEAR_ID,LEAVE_YEAR_NAME FROM budget_year ORDER BY LEAVE_YEAR_ID DESC LIMIT 7');
        $budget_year_last = DB::connection('backoffice')->table('budget_year')->where('DATE_END','>=',date('Y-m-d'))->where('DATE_BEGIN','<=',date('Y-m-d'))->value('LEAVE_YEAR_ID');
        
        $budget_year = $request->budget_year;
        if($budget_year == '' || $budget_year == null) {
            $budget_year = $budget_year_last;
        }

        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if ($start_date == '' || $end_date == '') {
            $start_date = DB::connection('backoffice')->table('budget_year')->where('LEAVE_YEAR_ID',$budget_year)->value('DATE_BEGIN');
            $end_date = DB::connection('backoffice')->table('budget_year')->where('LEAVE_YEAR_ID',$budget_year)->value('DATE_END');
        }

        if (!$start_date) {
            $start_date = (intval($budget_year) - 544) . "-10-01";
        }
        if (!$end_date) {
            $end_date = (intval($budget_year) - 543) . "-09-30";
        }

        $med_error = DB::connection('hosxp')->select('select 
                CASE WHEN MONTH(a.vstdate)="10" THEN CONCAT("ต.ค. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="11" THEN CONCAT("พ.ย. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="12" THEN CONCAT("ธ.ค. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="1" THEN CONCAT("ม.ค. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="2" THEN CONCAT("ก.พ. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="3" THEN CONCAT("มี.ค. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="4" THEN CONCAT("เม.ย. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="5" THEN CONCAT("พ.ค. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="6" THEN CONCAT("มิ.ย. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="7" THEN CONCAT("ก.ค. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="8" THEN CONCAT("ส.ค. ",RIGHT(YEAR(a.vstdate)+543,2))
                WHEN MONTH(a.vstdate)="9" THEN CONCAT("ก.ย. ",RIGHT(YEAR(a.vstdate)+543,2))
                END AS "month",
                COUNT(a.med_error_id) AS "total",
                SUM(CASE WHEN a.med_error_process_type_id ="1" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "po_1",
                SUM(CASE WHEN a.med_error_process_type_id ="2" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "po_2",
                SUM(CASE WHEN a.med_error_process_type_id ="3" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "po_3",
                SUM(CASE WHEN a.med_error_process_type_id ="4" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "po_4",
                SUM(CASE WHEN a.med_error_process_type_id ="5" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "po_5",
                SUM(CASE WHEN a.med_error_process_type_id ="1" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "pi_1",
                SUM(CASE WHEN a.med_error_process_type_id ="2" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "pi_2",
                SUM(CASE WHEN a.med_error_process_type_id ="3" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "pi_3",
                SUM(CASE WHEN a.med_error_process_type_id ="4" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "pi_4",
                SUM(CASE WHEN a.med_error_process_type_id ="5" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "pi_5",
                SUM(CASE WHEN a.med_error_risk_type_id ="1" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_a",
                SUM(CASE WHEN a.med_error_risk_type_id ="2" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_b",
                SUM(CASE WHEN a.med_error_risk_type_id ="3" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_c",
                SUM(CASE WHEN a.med_error_risk_type_id ="4" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_d",
                SUM(CASE WHEN a.med_error_risk_type_id ="5" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_e",
                SUM(CASE WHEN a.med_error_risk_type_id ="6" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_f",
                SUM(CASE WHEN a.med_error_risk_type_id ="7" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_g",
                SUM(CASE WHEN a.med_error_risk_type_id ="8" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_h",
                SUM(CASE WHEN a.med_error_risk_type_id ="9" AND a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "o_i",                
                SUM(CASE WHEN a.dep_type ="OPD" THEN 1 ELSE 0 END) AS "opd",
                SUM(CASE WHEN a.med_error_risk_type_id ="1" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_a",
                SUM(CASE WHEN a.med_error_risk_type_id ="2" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_b",
                SUM(CASE WHEN a.med_error_risk_type_id ="3" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_c",
                SUM(CASE WHEN a.med_error_risk_type_id ="4" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_d",
                SUM(CASE WHEN a.med_error_risk_type_id ="5" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_e",
                SUM(CASE WHEN a.med_error_risk_type_id ="6" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_f",
                SUM(CASE WHEN a.med_error_risk_type_id ="7" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_g",
                SUM(CASE WHEN a.med_error_risk_type_id ="8" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_h",
                SUM(CASE WHEN a.med_error_risk_type_id ="9" AND a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "i_i",
                SUM(CASE WHEN a.dep_type ="IPD" THEN 1 ELSE 0 END) AS "ipd"
                FROM
                (SELECT m.med_error_id,m.dep_type,m.med_error_process_type_id,m.med_error_risk_type_id,
                DATE(m.update_datetime) AS vstdate,m1.med_error_process_type_name,m2.med_error_risk_type_name
                FROM med_error m	
                LEFT OUTER JOIN med_error_process_type m1 ON m1.med_error_process_type_id = m.med_error_process_type_id
                LEFT OUTER JOIN med_error_risk_type m2 ON m2.med_error_risk_type_id = m.med_error_risk_type_id 
                WHERE DATE(m.update_datetime) BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                ORDER BY m.update_datetime) AS a
                GROUP BY MONTH(a.vstdate)
                ORDER BY YEAR(a.vstdate),MONTH(a.vstdate)'); 
                
        $med_error_m = array_column($med_error,'month');              
        $med_error_opd = array_column($med_error,'opd');
        $med_error_ipd = array_column($med_error,'ipd'); 
        
        $med_error_top=DB::connection('hosxp')->select('select 
                CONCAT(d.`name`,SPACE(1),d.strength) AS drug,COUNT(DISTINCT m.med_error_id) AS total              
                FROM med_error m	
                LEFT JOIN drugitems d ON d.icode=m.icode
                WHERE DATE(m.update_datetime) BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND d.icode <>"" AND d.icode IS NOT NULL AND m.dep_type ="OPD"
                GROUP BY m.icode ORDER BY COUNT(DISTINCT m.med_error_id) DESC limit 20');
        $med_error_drug = array_column($med_error_top,'drug');              
        $med_error_total = array_column($med_error_top,'total');

        $med_error_top_ipd=DB::connection('hosxp')->select('select 
                CONCAT(d.`name`,SPACE(1),d.strength) AS drug,COUNT(DISTINCT m.med_error_id) AS total              
                FROM med_error m	
                LEFT JOIN drugitems d ON d.icode=m.icode
                WHERE DATE(m.update_datetime) BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND d.icode <>"" AND d.icode IS NOT NULL AND m.dep_type ="IPD"
                GROUP BY m.icode ORDER BY COUNT(DISTINCT m.med_error_id) DESC limit 20');
        $med_error_drug_ipd = array_column($med_error_top_ipd,'drug');              
        $med_error_total_ipd = array_column($med_error_top_ipd,'total');

        return view('backoffice.incident.med_error', compact(
            'budget_year', 'med_error_m', 'med_error', 'med_error_opd', 'med_error_ipd',
            'budget_year_select', 'med_error_drug', 'med_error_total', 'med_error_drug_ipd', 'med_error_total_ipd',
            'start_date', 'end_date'
        ));
    }

    public function nrls_dataset(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if($start_date == '' || $end_date == null) {
            $start_date = date('Y-m-d', strtotime("first day of previous month"));
        }
        if($end_date == '' || $end_date == null) {
            $end_date = date('Y-m-d', strtotime("last day of previous month"));
        }
      
        $rr001 = DB::connection('hosxp')->table('an_stat')->selectRaw('lpad(SUM(admdate),6,0) AS "rr001"')
                ->whereBetween('dchdate', [$start_date,$end_date])->get();
        $rr003 = DB::connection('hosxp')->table('ovst')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr003"')
                ->whereBetween('vstdate', [$start_date,$end_date])->wherein('visit_type',['I'])->get();  
        $rr004 = DB::connection('hosxp')->table('ovst')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr004"')
                ->whereBetween('vstdate', [$start_date,$end_date])->wherein('visit_type',['S','O'])->get(); 
        $rr005 = DB::connection('hosxp')->table('ovst')->selectRaw('lpad(COUNT(DISTINCT hn),6,0) AS "rr005"')
                ->whereBetween('vstdate', [$start_date,$end_date])->wherein('visit_type',['I'])->get(); 
        $rr006 = DB::connection('hosxp')->table('ovst')->selectRaw('lpad(COUNT(DISTINCT hn),6,0) AS "rr006"')
                ->whereBetween('vstdate', [$start_date,$end_date])->wherein('visit_type',['S','O'])->get();                                
        $rr007 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr007"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','1')->get(); 
        $rr008 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr008"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','3')->get(); 
        $rr009 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr009"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','4')->get(); 
        $rr010 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr010"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','5')->get(); 
        $rr011 = DB::connection('hosxp')->table('referout')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr011"')
                ->whereBetween('refer_date', [$start_date,$end_date])->get(); 
        $rr015 = DB::connection('hosxp')->table('ipt')->selectRaw('lpad(COUNT(DISTINCT an),6,0) AS "rr015"')
                ->whereBetween('regdate', [$start_date,$end_date])->where('ipt_type','3')->get();  
        $rr016 = DB::connection('hosxp')->table('ipt')->selectRaw('lpad(COUNT(DISTINCT an),6,0) AS "rr016"')
                ->whereBetween('regdate', [$start_date,$end_date])->where('ipt_type','4')->get();     
        $rr022 = DB::connection('hosxp')->table('opitemrece')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr022"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('icode','like','1%')->whereNotNull('vn')->get(); 
        $rr024 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr024"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','2')->get(); 
        
        Session::put('start_date',$start_date);
        Session::put('end_date',$end_date);
        Session::put('rr001',$rr001);
        Session::put('rr003',$rr003);
        Session::put('rr004',$rr004);
        Session::put('rr005',$rr005);
        Session::put('rr006',$rr006);
        Session::put('rr007',$rr007);
        Session::put('rr008',$rr008);
        Session::put('rr009',$rr009);
        Session::put('rr010',$rr010);
        Session::put('rr011',$rr011);
        Session::put('rr015',$rr015);
        Session::put('rr016',$rr016);
        Session::put('rr022',$rr022);
        Session::put('rr024',$rr024);
        Session::save();
        
        return view('backoffice.incident.nrls_dataset',compact('start_date','end_date','rr001','rr003','rr004','rr005','rr006',
                'rr007','rr008','rr009','rr010','rr011','rr015','rr016','rr022','rr024'));
    }       

    public function nrls_dataset_export(Request $request)
    {
        $start_date = Session::get('start_date');   
        $date = substr($start_date, 0, 4).substr($start_date, 5, 2);  
        $rr001 = Session::get('rr001');
        $rr003 = Session::get('rr003');
        $rr004 = Session::get('rr004');
        $rr005 = Session::get('rr005');
        $rr006 = Session::get('rr006');
        $rr007 = Session::get('rr007');
        $rr008 = Session::get('rr008');
        $rr009 = Session::get('rr009');
        $rr010 = Session::get('rr010');
        $rr011 = Session::get('rr011');
        $rr015 = Session::get('rr015');
        $rr016 = Session::get('rr016');
        $rr022 = Session::get('rr022');
        $rr024 = Session::get('rr024');

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="10989_DataSet'.$date.'.csv"',
        ];
        
        $content = "\xEF\xBB\xBF" . 
                   "rr001|".$rr001[0]->rr001."\r\n".
                   "rr003|".$rr003[0]->rr003."\r\n".
                   "rr004|".$rr004[0]->rr004."\r\n".
                   "rr005|".$rr005[0]->rr005."\r\n".
                   "rr006|".$rr006[0]->rr006."\r\n".
                   "rr007|".$rr007[0]->rr007."\r\n".
                   "rr008|".$rr008[0]->rr008."\r\n".
                   "rr009|".$rr009[0]->rr009."\r\n".
                   "rr010|".$rr010[0]->rr010."\r\n".
                   "rr011|".$rr011[0]->rr011."\r\n".
                   "rr015|".$rr015[0]->rr015."\r\n".
                   "rr016|".$rr016[0]->rr016."\r\n".
                   "rr022|".$rr022[0]->rr022."\r\n".
                   "rr024|".$rr024[0]->rr024."\r\n";

        return Response::make($content, 200, $headers);
    }

    public function nrls(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if($start_date == '' || $start_date == null) {
            $start_date = date('Y-m-d', strtotime("first day of previous month"));
        }
        if($end_date == '' || $end_date == null) {
            $end_date = date('Y-m-d', strtotime("last day of previous month"));
        }
        
        $active_tab = $request->input('tab', 'occurrence');

        // 1. Incident occurrence data
        $nrls = DB::connection('backoffice')->select('
                SELECT "10989" AS hospital,LPAD(r.RISKREP_ID,10,0) AS risk_id,ri.RISK_REPITEMS_CODE AS datadic1,
                ri.RISK_REPITEMS_NAME AS datadic1_name,ru.INCEDENCE_USEREFFECT_CODE AS effect_code,ru.INCEDENCE_USEREFFECT_NAME AS effect_name,
                IF((r.RISKREP_SEX = "" OR r.RISKREP_SEX IS NULL) ,"O",r.RISKREP_SEX) AS pt_sex,
                IF((r.RISKREP_AGE = "" OR r.RISKREP_AGE IS NULL),"000",LPAD(r.RISKREP_AGE,3,0)) AS person_age,
                rl.RISK_LOCATION_CODE AS datadic4,rl.RISK_LOCATION_NAME AS datadic4_name,
                DATE_FORMAT(r.RISKREP_STARTDATE, "%Y%m%d") AS risk_date,LPAD(r.RISKREP_FATE,5,0) AS datadic5,
                rv.RISK_REP_LEVEL_CODE AS datadic6,rv.RISK_REP_LEVEL_NAME AS datadic6_name,r.RISKREP_DETAILRISK AS risk_detail,
                CASE WHEN ri.RISK_REPITEMS_CODE LIKE "C%" AND rv.RISK_REP_LEVEL_ID <="9" THEN "OK"
                WHEN ri.RISK_REPITEMS_CODE LIKE "C%" AND rv.RISK_REP_LEVEL_ID >"9" THEN "ความรุนแรงไม่ตรงกับรหัสอุบัติการณ์"
                WHEN ri.RISK_REPITEMS_CODE LIKE "G%" AND rv.RISK_REP_LEVEL_ID >"9" THEN "OK"
                WHEN ri.RISK_REPITEMS_CODE LIKE "G%" AND rv.RISK_REP_LEVEL_ID <="9" THEN "ความรุนแรงไม่ตรงกับรหัสอุบัติการณ์" END AS "status_lavel"
                FROM risk_rep r
                LEFT JOIN risk_rep_items ri ON ri.RISK_REPITEMS_ID=r.RISK_REPITEMS_ID
                LEFT JOIN risk_setupincidence_usereffect ru ON ru.INCEDENCE_USEREFFECT_ID=r.RISK_REP_EFFECT
                LEFT JOIN risk_rep_location rl ON rl.RISK_LOCATION_ID=r.RISKREP_LOCAL
                LEFT JOIN risk_rep_level rv ON rv.RISK_REP_LEVEL_ID=r.RISKREP_LEVEL
                WHERE r.RISKREP_STARTDATE BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND (ri.RISK_REPITEMS_ID IS NOT NULL OR ri.RISK_REPITEMS_ID <>"") GROUP BY r.RISKREP_ID');

        // 2. Incident correction data
        $nrls_edit = DB::connection('backoffice')->select('
                SELECT "10989" AS hospital,LPAD(r.RISKREP_ID,10,0) AS risk_id,ri.RISK_REPITEMS_CODE AS datadic1,
                ri.RISK_REPITEMS_NAME AS datadic1_name,ru.INCEDENCE_USEREFFECT_CODE AS effect_code,ru.INCEDENCE_USEREFFECT_NAME AS effect_name,
                IF((r.RISKREP_SEX = "" OR r.RISKREP_SEX IS NULL) ,"O",r.RISKREP_SEX) AS pt_sex,
                IF((r.RISKREP_AGE = "" OR r.RISKREP_AGE IS NULL),"000",LPAD(r.RISKREP_AGE,3,0)) AS person_age,
                rl.RISK_LOCATION_CODE AS datadic4,rl.RISK_LOCATION_NAME AS datadic4_name,
                DATE_FORMAT(r.RISKREP_STARTDATE, "%Y%m%d") AS risk_date,DATE_FORMAT(r.RISKREP_INFER_DAYENDPROBLEM, "%Y%m%d") AS risk_date_edit,
                LPAD(r.RISKREP_FATE,5,0) AS datadic5,rv.RISK_REP_LEVEL_CODE AS datadic6,rv.RISK_REP_LEVEL_NAME AS datadic6_name,
                REPLACE(r.RISKREP_DETAILRISK,","," ") AS risk_detail,IF(r.RISKREP_INFER_EDIT IS NULL,"-",r.RISKREP_INFER_EDIT) AS risk_detail_edit,
                r.RISKREP_INFER_IMPROVE,r.RISKREP_INFER_GROUPPROBLEM AS risk_detail_group
                FROM risk_rep r
                LEFT JOIN risk_rep_items ri ON ri.RISK_REPITEMS_ID=r.RISK_REPITEMS_ID
                LEFT JOIN risk_setupincidence_usereffect ru ON ru.INCEDENCE_USEREFFECT_ID=r.RISK_REP_EFFECT
                LEFT JOIN risk_rep_location rl ON rl.RISK_LOCATION_ID=r.RISKREP_LOCAL
                LEFT JOIN risk_rep_level rv ON rv.RISK_REP_LEVEL_ID=r.RISKREP_LEVEL
                WHERE r.RISKREP_STARTDATE BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND (ri.RISK_REPITEMS_ID IS NOT NULL OR ri.RISK_REPITEMS_ID <>"") GROUP BY r.RISKREP_ID');

        // 3. Monthly Dataset
        $rr001 = DB::connection('hosxp')->table('an_stat')->selectRaw('lpad(SUM(admdate),6,0) AS "rr001"')
                ->whereBetween('dchdate', [$start_date,$end_date])->get();
        $rr003 = DB::connection('hosxp')->table('ovst')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr003"')
                ->whereBetween('vstdate', [$start_date,$end_date])->wherein('visit_type',['I'])->get();  
        $rr004 = DB::connection('hosxp')->table('ovst')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr004"')
                ->whereBetween('vstdate', [$start_date,$end_date])->wherein('visit_type',['S','O'])->get(); 
        $rr005 = DB::connection('hosxp')->table('ovst')->selectRaw('lpad(COUNT(DISTINCT hn),6,0) AS "rr005"')
                ->whereBetween('vstdate', [$start_date,$end_date])->wherein('visit_type',['I'])->get(); 
        $rr006 = DB::connection('hosxp')->table('ovst')->selectRaw('lpad(COUNT(DISTINCT hn),6,0) AS "rr006"')
                ->whereBetween('vstdate', [$start_date,$end_date])->wherein('visit_type',['S','O'])->get();                                
        $rr007 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr007"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','1')->get(); 
        $rr008 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr008"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','3')->get(); 
        $rr009 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr009"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','4')->get(); 
        $rr010 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr010"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','5')->get(); 
        $rr011 = DB::connection('hosxp')->table('referout')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr011"')
                ->whereBetween('refer_date', [$start_date,$end_date])->get(); 
        $rr015 = DB::connection('hosxp')->table('ipt')->selectRaw('lpad(COUNT(DISTINCT an),6,0) AS "rr015"')
                ->whereBetween('regdate', [$start_date,$end_date])->where('ipt_type','3')->get();  
        $rr016 = DB::connection('hosxp')->table('ipt')->selectRaw('lpad(COUNT(DISTINCT an),6,0) AS "rr016"')
                ->whereBetween('regdate', [$start_date,$end_date])->where('ipt_type','4')->get();     
        $rr022 = DB::connection('hosxp')->table('opitemrece')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr022"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('icode','like','1%')->whereNotNull('vn')->get(); 
        $rr024 = DB::connection('hosxp')->table('er_regist')->selectRaw('lpad(COUNT(DISTINCT vn),6,0) AS "rr024"')
                ->whereBetween('vstdate', [$start_date,$end_date])->where('er_emergency_type','2')->get(); 

        Session::put('start_date',$start_date);
        Session::put('end_date',$end_date);
        Session::put('nrls',$nrls);
        Session::put('nrls_edit',$nrls_edit);
        
        Session::put('rr001',$rr001);
        Session::put('rr003',$rr003);
        Session::put('rr004',$rr004);
        Session::put('rr005',$rr005);
        Session::put('rr006',$rr006);
        Session::put('rr007',$rr007);
        Session::put('rr008',$rr008);
        Session::put('rr009',$rr009);
        Session::put('rr010',$rr010);
        Session::put('rr011',$rr011);
        Session::put('rr015',$rr015);
        Session::put('rr016',$rr016);
        Session::put('rr022',$rr022);
        Session::put('rr024',$rr024);
        Session::save();
        
        return view('backoffice.incident.nrls',compact(
            'start_date','end_date','nrls','nrls_edit','active_tab','rr001','rr003','rr004','rr005','rr006',
            'rr007','rr008','rr009','rr010','rr011','rr015','rr016','rr022','rr024'
        ));
    }

    public function nrls_export(Request $request)
    {
        $start_date = Session::get('start_date');  
        $end_date = Session::get('end_date');    
        $date = substr($start_date, 0, 4).substr($start_date, 5, 2);  
        $nrls = DB::connection('backoffice')->select('
                SELECT CONCAT("10989","|",LPAD(r.RISKREP_ID,10,0),"|",ri.RISK_REPITEMS_CODE,"|",
                ru.INCEDENCE_USEREFFECT_CODE,"|",IF((r.RISKREP_SEX = "" OR r.RISKREP_SEX IS NULL) ,"O",r.RISKREP_SEX),"|",
                IF((r.RISKREP_AGE = "" OR r.RISKREP_AGE IS NULL),"000",LPAD(r.RISKREP_AGE,3,0)),"|",rl.RISK_LOCATION_CODE,"|",
                DATE_FORMAT(r.RISKREP_STARTDATE, "%Y%m%d"),"|",LPAD(r.RISKREP_FATE,5,0),"|",rv.RISK_REP_LEVEL_CODE,"|",
                REPLACE(r.RISKREP_DETAILRISK,","," "),"|") AS nrls
                FROM risk_rep r
                LEFT JOIN risk_rep_items ri ON ri.RISK_REPITEMS_ID=r.RISK_REPITEMS_ID
                LEFT JOIN risk_setupincidence_usereffect ru ON ru.INCEDENCE_USEREFFECT_ID=r.RISK_REP_EFFECT
                LEFT JOIN risk_rep_location rl ON rl.RISK_LOCATION_ID=r.RISKREP_LOCAL
                LEFT JOIN risk_rep_level rv ON rv.RISK_REP_LEVEL_ID=r.RISKREP_LEVEL
                WHERE r.RISKREP_STARTDATE BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND (ri.RISK_REPITEMS_ID IS NOT NULL OR ri.RISK_REPITEMS_ID <>"") GROUP BY r.RISKREP_ID');

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="10989_Export'.$date.'.csv"',
        ];

        $content = "\xEF\xBB\xBF";
        foreach($nrls as $row) {
            $line = str_replace(["\r\n", "\r", "\n"], " ", $row->nrls);
            $content .= $line . "\r\n";
        }

        return Response::make($content, 200, $headers);
    }

    public function nrls_edit(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        if($start_date == '' || $end_date == null) {
            $start_date = date('Y-m-d', strtotime("first day of previous month"));
        }
        if($end_date == '' || $end_date == null) {
            $end_date = date('Y-m-d', strtotime("last day of previous month"));
        }

        $nrls = DB::connection('backoffice')->select('
                SELECT "10989" AS hospital,LPAD(r.RISKREP_ID,10,0) AS risk_id,ri.RISK_REPITEMS_CODE AS datadic1,
                ri.RISK_REPITEMS_NAME AS datadic1_name,ru.INCEDENCE_USEREFFECT_CODE AS effect_code,ru.INCEDENCE_USEREFFECT_NAME AS effect_name,
                IF((r.RISKREP_SEX = "" OR r.RISKREP_SEX IS NULL) ,"O",r.RISKREP_SEX) AS pt_sex,
                IF((r.RISKREP_AGE = "" OR r.RISKREP_AGE IS NULL),"000",LPAD(r.RISKREP_AGE,3,0)) AS person_age,
                rl.RISK_LOCATION_CODE AS datadic4,rl.RISK_LOCATION_NAME AS datadic4_name,
                DATE_FORMAT(r.RISKREP_STARTDATE, "%Y%m%d") AS risk_date,DATE_FORMAT(r.RISKREP_INFER_DAYENDPROBLEM, "%Y%m%d") AS risk_date_edit,
                LPAD(r.RISKREP_FATE,5,0) AS datadic5,rv.RISK_REP_LEVEL_CODE AS datadic6,rv.RISK_REP_LEVEL_NAME AS datadic6_name,
                REPLACE(r.RISKREP_DETAILRISK,","," ") AS risk_detail,IF(r.RISKREP_INFER_EDIT IS NULL,"-",r.RISKREP_INFER_EDIT) AS risk_detail_edit,
                r.RISKREP_INFER_IMPROVE,r.RISKREP_INFER_GROUPPROBLEM AS risk_detail_group
                FROM risk_rep r
                LEFT JOIN risk_rep_items ri ON ri.RISK_REPITEMS_ID=r.RISK_REPITEMS_ID
                LEFT JOIN risk_setupincidence_usereffect ru ON ru.INCEDENCE_USEREFFECT_ID=r.RISK_REP_EFFECT
                LEFT JOIN risk_rep_location rl ON rl.RISK_LOCATION_ID=r.RISKREP_LOCAL
                LEFT JOIN risk_rep_level rv ON rv.RISK_REP_LEVEL_ID=r.RISKREP_LEVEL
                WHERE r.RISKREP_STARTDATE BETWEEN "'.$start_date.'" AND "'.$end_date.'"
                AND (ri.RISK_REPITEMS_ID IS NOT NULL OR ri.RISK_REPITEMS_ID <>"") GROUP BY r.RISKREP_ID');

        Session::put('start_date',$start_date);
        Session::put('nrls',$nrls);
        Session::save();
        
        return view('backoffice.incident.nrls_edit',compact('start_date','end_date','nrls'));
    }

    public function nrls_editexport(Request $request)
    {
        $start_date = Session::get('start_date');   
        $date = substr($start_date, 0, 4).substr($start_date, 5, 2);  
        $nrls = Session::get('nrls_edit') ?: [];

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="10989_EditExport'.$date.'.csv"',
        ];

        $content = "\xEF\xBB\xBF";
        foreach($nrls as $row) {
            $risk_detail = str_replace(["\r\n", "\r", "\n"], " ", $row->risk_detail);
            $risk_detail_edit = str_replace(["\r\n", "\r", "\n"], " ", $row->risk_detail_edit);
            $improve = str_replace(["\r\n", "\r", "\n"], " ", $row->RISKREP_INFER_IMPROVE);
            $group = str_replace(["\r\n", "\r", "\n"], " ", $row->risk_detail_group);

            $content .= $row->hospital."|".$row->risk_id."|".$row->datadic1."|".$row->effect_code."|".
                        $row->pt_sex."|".$row->person_age."|".$row->datadic4."|".$row->risk_date."|".
                        $row->datadic5."|".$row->datadic6."|".$risk_detail."|".$risk_detail_edit."|".
                        $improve."|".$group."|\r\n";
        }

        return Response::make($content, 200, $headers);
    }

    public function program_detail(Request $request, $id)
    {
        $start_date = Session::get('start_date');
        $end_date = Session::get('end_date'); 
        $budget_year = Session::get('budget_year'); 

        $program_detail = DB::connection('backoffice')->select('
                SELECT * FROM(SELECT CONCAT("R",RIGHT(r.budget_year,2),"-",IF(LENGTH(r.RISKREP_ID)=1,CONCAT("000",r.RISKREP_ID),if(LENGTH(r.RISKREP_ID)=2,concat("00",r.RISKREP_ID),if(LENGTH(r.RISKREP_ID)="3",concat("0",r.RISKREP_ID),r.RISKREP_ID)))) AS id,r.RISKREP_DATESAVE,r.RISKREP_STARTDATE,DATE(NOW()) AS date_now,DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) AS date_count,     CASE WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE)  BETWEEN 1 AND 30 THEN "5"
                WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) BETWEEN 31 AND 183 THEN "4"
                WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) BETWEEN 184 AND 730 THEN "3"
                WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) BETWEEN 731 AND 1825 THEN "2" 
                WHEN DATEDIFF(DATE(NOW()),r.RISKREP_DATESAVE) > 1825 THEN "1" END AS "likelihood",
                l.RISK_REP_LEVEL_NAME,CASE WHEN l.RISK_REP_LEVEL_NAME IN ("I","5") THEN "5" 
                WHEN l.RISK_REP_LEVEL_NAME IN ("G","H","4") THEN "4" 
                WHEN l.RISK_REP_LEVEL_NAME IN ("E","F","3") THEN "3" 
                WHEN l.RISK_REP_LEVEL_NAME IN ("B","C","D","2") THEN "2" 
                WHEN l.RISK_REP_LEVEL_NAME IN ("A","1")THEN "1" END AS "consequence", 
                IF(p.RISK_REPPROGRAM_ID IS NULL,"0",p.RISK_REPPROGRAM_ID) AS RISK_REPPROGRAM_ID,          
                IF(p.RISK_REPPROGRAM_NAME IS NULL,"ไม่ระบุ",p.RISK_REPPROGRAM_NAME) AS RISK_REPPROGRAM_NAME,
                IF(ps.RISK_REPPROGRAMSUB_ID IS NULL,"00",ps.RISK_REPPROGRAMSUB_ID) AS RISK_REPPROGRAMSUB_ID,
                IF(ps.RISK_REPPROGRAMSUB_NAME IS NULL,"ไม่ระบุ",ps.RISK_REPPROGRAMSUB_NAME) AS RISK_REPPROGRAMSUB_NAME,
                IF(pss.RISK_REPPROGRAMSUBSUB_ID IS NULL,"000",pss.RISK_REPPROGRAMSUBSUB_ID) AS RISK_REPPROGRAMSUBSUB_ID, 
                IF(pss.RISK_REPPROGRAMSUBSUB_NAME IS NULL,"ไม่ระบุ",pss.RISK_REPPROGRAMSUBSUB_NAME) AS RISK_REPPROGRAMSUBSUB_NAME,
                ps.RISK_REPPROGRAMSUB_DETAIL AS clinic,r.RISKREP_DETAILRISK,GROUP_CONCAT(rc.RISK_RECHECK_DATE) AS "recheck"
                FROM risk_rep r LEFT JOIN risk_rep_program p ON p.RISK_REPPROGRAM_ID=r.RISK_REPPROGRAM_ID  								
                LEFT JOIN risk_rep_program_sub ps ON ps.RISK_REPPROGRAMSUB_ID=r.RISK_REPPROGRAMSUB_ID  
                LEFT JOIN risk_rep_program_subsub pss ON pss.RISK_REPPROGRAMSUBSUB_ID=r.RISK_REPPROGRAMSUBSUB_ID
                LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID=r.RISKREP_LEVEL
                LEFT JOIN risk_recheck rc ON rc.RISK_RECHECK_RISKID=r.RISKREP_ID
                WHERE r.RISKREP_STATUS <> "CANCEL" GROUP BY r.RISKREP_ID) AS a
                WHERE RISKREP_DATESAVE BETWEEN "'.$start_date.'" AND "'.$end_date.'" AND RISK_REPPROGRAM_ID = "'.$id.'"
                ORDER BY RISK_REP_LEVEL_NAME DESC');

        $RISK_REPPROGRAM_NAME = "ไม่ระบุ";
        foreach ($program_detail as $row){
            $RISK_REPPROGRAM_NAME = $row->RISK_REPPROGRAM_NAME;
        }

        return view('backoffice.incident.program_detail', compact('program_detail','budget_year','RISK_REPPROGRAM_NAME'));
    }

    public function matrix_detail(Request $request, $type, $consequence, $likelihood)
    {
        $start_date = Session::get('start_date') ?: date('Y-m-d', strtotime("-1 year"));
        $end_date = Session::get('end_date') ?: date('Y-m-d');

        $incidents = DB::connection('backoffice')->select('
            SELECT * FROM (
                SELECT 
                    CONCAT("R", RIGHT(r.budget_year, 2), "-", IF(LENGTH(r.RISKREP_ID) = 1, CONCAT("000", r.RISKREP_ID), IF(LENGTH(r.RISKREP_ID) = 2, CONCAT("00", r.RISKREP_ID), IF(LENGTH(r.RISKREP_ID) = 3, CONCAT("0", r.RISKREP_ID), r.RISKREP_ID)))) AS id,
                    r.RISKREP_DATESAVE,
                    r.RISKREP_STARTDATE,
                    CASE 
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) BETWEEN 0 AND 30 THEN "5"
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) BETWEEN 31 AND 183 THEN "4"
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) BETWEEN 184 AND 730 THEN "3"
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) BETWEEN 731 AND 1825 THEN "2" 
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) > 1825 THEN "1" 
                    END AS "likelihood",
                    l.RISK_REP_LEVEL_NAME,
                    CASE 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("I", "5") THEN "5" 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("G", "H", "4") THEN "4" 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("E", "F", "3") THEN "3" 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("B", "C", "D", "2") THEN "2" 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("A", "1") THEN "1" 
                    END AS "consequence", 
                    IF(p.RISK_REPPROGRAM_ID IS NULL, "0", p.RISK_REPPROGRAM_ID) AS RISK_REPPROGRAM_ID,          
                    IF(p.RISK_REPPROGRAM_NAME IS NULL, "ไม่ระบุ", p.RISK_REPPROGRAM_NAME) AS RISK_REPPROGRAM_NAME,
                    IF(ps.RISK_REPPROGRAMSUB_ID IS NULL, "00", ps.RISK_REPPROGRAMSUB_ID) AS RISK_REPPROGRAMSUB_ID,
                    IF(ps.RISK_REPPROGRAMSUB_NAME IS NULL, "ไม่ระบุ", ps.RISK_REPPROGRAMSUB_NAME) AS RISK_REPPROGRAMSUB_NAME,
                    IF(pss.RISK_REPPROGRAMSUBSUB_ID IS NULL, "000", pss.RISK_REPPROGRAMSUBSUB_ID) AS RISK_REPPROGRAMSUBSUB_ID, 
                    IF(pss.RISK_REPPROGRAMSUBSUB_NAME IS NULL, "ไม่ระบุ", pss.RISK_REPPROGRAMSUBSUB_NAME) AS RISK_REPPROGRAMSUBSUB_NAME,
                    ps.RISK_REPPROGRAMSUB_DETAIL AS clinic,
                    r.RISKREP_DETAILRISK,
                    GROUP_CONCAT(rc.RISK_RECHECK_DATE) AS "recheck"
                FROM risk_rep r 
                LEFT JOIN risk_rep_program p ON p.RISK_REPPROGRAM_ID = r.RISK_REPPROGRAM_ID  								
                LEFT JOIN risk_rep_program_sub ps ON ps.RISK_REPPROGRAMSUB_ID = r.RISK_REPPROGRAMSUB_ID  
                LEFT JOIN risk_rep_program_subsub pss ON pss.RISK_REPPROGRAMSUBSUB_ID = r.RISK_REPPROGRAMSUBSUB_ID
                LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID = r.RISKREP_LEVEL
                LEFT JOIN risk_recheck rc ON rc.RISK_RECHECK_RISKID = r.RISKREP_ID
                WHERE r.RISKREP_STATUS <> "CANCEL" AND r.RISKREP_DATESAVE BETWEEN ? AND ?
                GROUP BY r.RISKREP_ID
            ) AS a
            WHERE consequence = ? AND likelihood = ? AND LOWER(clinic) = LOWER(?)
            ORDER BY RISKREP_STARTDATE DESC
        ', [$start_date, $end_date, $consequence, $likelihood, $type]);

        return view('backoffice.incident.matrix_detail', compact('incidents', 'type', 'consequence', 'likelihood', 'start_date', 'end_date'));
    }

    public function table_detail(Request $request)
    {
        $start_date = Session::get('start_date') ?: date('Y-m-d', strtotime("-1 year"));
        $end_date = Session::get('end_date') ?: date('Y-m-d');

        $level = $request->input('level', 'all');
        $month = $request->input('month', 'all');
        $year = $request->input('year', 'all');
        $program_id = $request->input('program_id', 'all');
        $sub_id = $request->input('sub_id', 'all');
        $subsub_id = $request->input('subsub_id', 'all');
        $drilldown = (int) $request->input('drilldown', 0);

        if ($drilldown === 1) {
            // Drilldown Level 3: Show flat incidents for selected subsub_id
            if ($subsub_id !== 'all') {
                $incidents = $this->get_flat_incidents($start_date, $end_date, $level, $month, $year, $program_id, $sub_id, $subsub_id);
                $breadcrumbs = $this->get_breadcrumbs($program_id, $sub_id, $subsub_id);
                return view('backoffice.incident.table_detail', compact('incidents', 'level', 'month', 'year', 'program_id', 'sub_id', 'subsub_id', 'breadcrumbs', 'start_date', 'end_date', 'drilldown'));
            }

            // Drilldown Level 2: Show list of Sub 2 (subsub) under chosen Sub 1 (sub)
            if ($sub_id !== 'all') {
                $subsubs = DB::connection('backoffice')->select('
                    SELECT 
                        pss.RISK_REPPROGRAMSUBSUB_ID AS id,
                        pss.RISK_REPPROGRAMSUBSUB_NAME AS name,
                        COUNT(r.RISKREP_ID) AS total,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "A" THEN 1 ELSE 0 END) AS a,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "B" THEN 1 ELSE 0 END) AS b,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "C" THEN 1 ELSE 0 END) AS c,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "D" THEN 1 ELSE 0 END) AS d,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "E" THEN 1 ELSE 0 END) AS e,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "F" THEN 1 ELSE 0 END) AS f,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "G" THEN 1 ELSE 0 END) AS g,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "H" THEN 1 ELSE 0 END) AS h,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "I" THEN 1 ELSE 0 END) AS i,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "1" THEN 1 ELSE 0 END) AS g1,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "2" THEN 1 ELSE 0 END) AS g2,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "3" THEN 1 ELSE 0 END) AS g3,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "4" THEN 1 ELSE 0 END) AS g4,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "5" THEN 1 ELSE 0 END) AS g5,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME IS NULL OR l.RISK_REP_LEVEL_NAME = "" THEN 1 ELSE 0 END) AS `null`
                    FROM risk_rep_program_subsub pss
                    LEFT JOIN risk_rep r ON r.RISK_REPPROGRAMSUBSUB_ID = pss.RISK_REPPROGRAMSUBSUB_ID
                        AND r.RISKREP_STATUS <> "CANCEL"
                        AND r.RISKREP_DATESAVE BETWEEN ? AND ?
                    LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID = r.RISKREP_LEVEL
                    WHERE pss.RISK_REPPROGRAMSUB_ID = ?
                    GROUP BY pss.RISK_REPPROGRAMSUBSUB_ID, pss.RISK_REPPROGRAMSUBSUB_NAME
                    ORDER BY total DESC, pss.RISK_REPPROGRAMSUBSUB_NAME ASC
                ', [$start_date, $end_date, $sub_id]);

                // Direct incidents with no Sub 2
                $no_subsub_row = DB::connection('backoffice')->select('
                    SELECT 
                        "0" AS id,
                        "อื่นๆ (ไม่ได้ระบุโปรแกรมย่อยระดับ 2)" AS name,
                        COUNT(r.RISKREP_ID) AS total,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "A" THEN 1 ELSE 0 END) AS a,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "B" THEN 1 ELSE 0 END) AS b,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "C" THEN 1 ELSE 0 END) AS c,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "D" THEN 1 ELSE 0 END) AS d,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "E" THEN 1 ELSE 0 END) AS e,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "F" THEN 1 ELSE 0 END) AS f,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "G" THEN 1 ELSE 0 END) AS g,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "H" THEN 1 ELSE 0 END) AS h,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "I" THEN 1 ELSE 0 END) AS i,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "1" THEN 1 ELSE 0 END) AS g1,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "2" THEN 1 ELSE 0 END) AS g2,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "3" THEN 1 ELSE 0 END) AS g3,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "4" THEN 1 ELSE 0 END) AS g4,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "5" THEN 1 ELSE 0 END) AS g5,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME IS NULL OR l.RISK_REP_LEVEL_NAME = "" THEN 1 ELSE 0 END) AS `null`
                    FROM risk_rep r
                    LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID = r.RISKREP_LEVEL
                    WHERE r.RISKREP_STATUS <> "CANCEL"
                        AND r.RISKREP_DATESAVE BETWEEN ? AND ?
                        AND r.RISK_REPPROGRAMSUB_ID = ?
                        AND (r.RISK_REPPROGRAMSUBSUB_ID IS NULL OR r.RISK_REPPROGRAMSUBSUB_ID = "0" OR r.RISK_REPPROGRAMSUBSUB_ID = "" OR r.RISK_REPPROGRAMSUBSUB_ID = "000")
                ', [$start_date, $end_date, $sub_id]);

                $no_subsub = (!empty($no_subsub_row) && $no_subsub_row[0]->total > 0) ? $no_subsub_row[0] : null;

                $breadcrumbs = $this->get_breadcrumbs($program_id, $sub_id);

                if (empty($subsubs) && (!$no_subsub)) {
                    $incidents = $this->get_flat_incidents($start_date, $end_date, $level, $month, $year, $program_id, $sub_id, 'all');
                    return view('backoffice.incident.table_detail', compact('incidents', 'level', 'month', 'year', 'program_id', 'sub_id', 'subsub_id', 'breadcrumbs', 'start_date', 'end_date', 'drilldown'));
                }

                return view('backoffice.incident.drilldown_detail', compact('subsubs', 'no_subsub', 'program_id', 'sub_id', 'breadcrumbs', 'start_date', 'end_date', 'drilldown', 'level', 'month', 'year'));
            }

            // Drilldown Level 1: Show list of Sub 1 (subs) under chosen Program
            if ($program_id !== 'all') {
                $subs = DB::connection('backoffice')->select('
                    SELECT 
                        ps.RISK_REPPROGRAMSUB_ID AS id,
                        ps.RISK_REPPROGRAMSUB_NAME AS name,
                        COUNT(r.RISKREP_ID) AS total,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "A" THEN 1 ELSE 0 END) AS a,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "B" THEN 1 ELSE 0 END) AS b,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "C" THEN 1 ELSE 0 END) AS c,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "D" THEN 1 ELSE 0 END) AS d,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "E" THEN 1 ELSE 0 END) AS e,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "F" THEN 1 ELSE 0 END) AS f,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "G" THEN 1 ELSE 0 END) AS g,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "H" THEN 1 ELSE 0 END) AS h,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "I" THEN 1 ELSE 0 END) AS i,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "1" THEN 1 ELSE 0 END) AS g1,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "2" THEN 1 ELSE 0 END) AS g2,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "3" THEN 1 ELSE 0 END) AS g3,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "4" THEN 1 ELSE 0 END) AS g4,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "5" THEN 1 ELSE 0 END) AS g5,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME IS NULL OR l.RISK_REP_LEVEL_NAME = "" THEN 1 ELSE 0 END) AS `null`
                    FROM risk_rep_program_sub ps
                    LEFT JOIN risk_rep r ON r.RISK_REPPROGRAMSUB_ID = ps.RISK_REPPROGRAMSUB_ID
                        AND r.RISKREP_STATUS <> "CANCEL"
                        AND r.RISKREP_DATESAVE BETWEEN ? AND ?
                    LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID = r.RISKREP_LEVEL
                    WHERE ps.RISK_REPPROGRAM_ID = ?
                    GROUP BY ps.RISK_REPPROGRAMSUB_ID, ps.RISK_REPPROGRAMSUB_NAME
                    ORDER BY total DESC, ps.RISK_REPPROGRAMSUB_NAME ASC
                ', [$start_date, $end_date, $program_id]);

                // Direct program incidents with no Sub 1
                $no_sub_row = DB::connection('backoffice')->select('
                    SELECT 
                        "0" AS id,
                        "อื่นๆ (ไม่ได้ระบุโปรแกรมย่อย)" AS name,
                        COUNT(r.RISKREP_ID) AS total,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "A" THEN 1 ELSE 0 END) AS a,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "B" THEN 1 ELSE 0 END) AS b,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "C" THEN 1 ELSE 0 END) AS c,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "D" THEN 1 ELSE 0 END) AS d,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "E" THEN 1 ELSE 0 END) AS e,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "F" THEN 1 ELSE 0 END) AS f,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "G" THEN 1 ELSE 0 END) AS g,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "H" THEN 1 ELSE 0 END) AS h,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "I" THEN 1 ELSE 0 END) AS i,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "1" THEN 1 ELSE 0 END) AS g1,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "2" THEN 1 ELSE 0 END) AS g2,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "3" THEN 1 ELSE 0 END) AS g3,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "4" THEN 1 ELSE 0 END) AS g4,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME = "5" THEN 1 ELSE 0 END) AS g5,
                        SUM(CASE WHEN l.RISK_REP_LEVEL_NAME IS NULL OR l.RISK_REP_LEVEL_NAME = "" THEN 1 ELSE 0 END) AS `null`
                    FROM risk_rep r
                    LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID = r.RISKREP_LEVEL
                    WHERE r.RISKREP_STATUS <> "CANCEL"
                        AND r.RISKREP_DATESAVE BETWEEN ? AND ?
                        AND r.RISK_REPPROGRAM_ID = ?
                        AND (r.RISK_REPPROGRAMSUB_ID IS NULL OR r.RISK_REPPROGRAMSUB_ID = "0" OR r.RISK_REPPROGRAMSUB_ID = "" OR r.RISK_REPPROGRAMSUB_ID = "00")
                ', [$start_date, $end_date, $program_id]);

                $no_sub = (!empty($no_sub_row) && $no_sub_row[0]->total > 0) ? $no_sub_row[0] : null;

                $breadcrumbs = $this->get_breadcrumbs($program_id);

                if (empty($subs) && (!$no_sub)) {
                    $incidents = $this->get_flat_incidents($start_date, $end_date, $level, $month, $year, $program_id, 'all', 'all');
                    return view('backoffice.incident.table_detail', compact('incidents', 'level', 'month', 'year', 'program_id', 'sub_id', 'subsub_id', 'breadcrumbs', 'start_date', 'end_date', 'drilldown'));
                }

                return view('backoffice.incident.drilldown_detail', compact('subs', 'no_sub', 'program_id', 'breadcrumbs', 'start_date', 'end_date', 'drilldown', 'level', 'month', 'year'));
            }
        }

        $incidents = $this->get_flat_incidents($start_date, $end_date, $level, $month, $year, $program_id, $sub_id, $subsub_id);
        $breadcrumbs = $this->get_breadcrumbs($program_id, $sub_id, $subsub_id);
        return view('backoffice.incident.table_detail', compact('incidents', 'level', 'month', 'year', 'program_id', 'sub_id', 'subsub_id', 'breadcrumbs', 'start_date', 'end_date', 'drilldown'));
    }

    private function get_flat_incidents($start_date, $end_date, $level, $month, $year, $program_id, $sub_id, $subsub_id)
    {
        $query = '
            SELECT * FROM (
                SELECT 
                    CONCAT("R", RIGHT(r.budget_year, 2), "-", IF(LENGTH(r.RISKREP_ID) = 1, CONCAT("000", r.RISKREP_ID), IF(LENGTH(r.RISKREP_ID) = 2, CONCAT("00", r.RISKREP_ID), IF(LENGTH(r.RISKREP_ID) = 3, CONCAT("0", r.RISKREP_ID), r.RISKREP_ID)))) AS id,
                    r.RISKREP_DATESAVE,
                    r.RISKREP_STARTDATE,
                    CASE 
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) BETWEEN 0 AND 30 THEN "5"
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) BETWEEN 31 AND 183 THEN "4"
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) BETWEEN 184 AND 730 THEN "3"
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) BETWEEN 731 AND 1825 THEN "2" 
                        WHEN DATEDIFF(DATE(NOW()), r.RISKREP_DATESAVE) > 1825 THEN "1" 
                    END AS "likelihood",
                    l.RISK_REP_LEVEL_NAME,
                    CASE 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("I", "5") THEN "5" 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("G", "H", "4") THEN "4" 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("E", "F", "3") THEN "3" 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("B", "C", "D", "2") THEN "2" 
                        WHEN l.RISK_REP_LEVEL_NAME IN ("A", "1") THEN "1" 
                    END AS "consequence", 
                    IF(p.RISK_REPPROGRAM_ID IS NULL, "0", p.RISK_REPPROGRAM_ID) AS RISK_REPPROGRAM_ID,          
                    IF(p.RISK_REPPROGRAM_NAME IS NULL, "Non-Program", p.RISK_REPPROGRAM_NAME) AS RISK_REPPROGRAM_NAME,
                    IF(ps.RISK_REPPROGRAMSUB_ID IS NULL, "00", ps.RISK_REPPROGRAMSUB_ID) AS RISK_REPPROGRAMSUB_ID,
                    IF(ps.RISK_REPPROGRAMSUB_NAME IS NULL, "ไม่ระบุ", ps.RISK_REPPROGRAMSUB_NAME) AS RISK_REPPROGRAMSUB_NAME,
                    IF(pss.RISK_REPPROGRAMSUBSUB_ID IS NULL, "000", pss.RISK_REPPROGRAMSUBSUB_ID) AS RISK_REPPROGRAMSUBSUB_ID, 
                    IF(pss.RISK_REPPROGRAMSUBSUB_NAME IS NULL, "ไม่ระบุ", pss.RISK_REPPROGRAMSUBSUB_NAME) AS RISK_REPPROGRAMSUBSUB_NAME,
                    ps.RISK_REPPROGRAMSUB_DETAIL AS clinic,
                    r.RISKREP_DETAILRISK,
                    GROUP_CONCAT(rc.RISK_RECHECK_DATE) AS "recheck"
                FROM risk_rep r 
                LEFT JOIN risk_rep_program p ON p.RISK_REPPROGRAM_ID = r.RISK_REPPROGRAM_ID  								
                LEFT JOIN risk_rep_program_sub ps ON ps.RISK_REPPROGRAMSUB_ID = r.RISK_REPPROGRAMSUB_ID  
                LEFT JOIN risk_rep_program_subsub pss ON pss.RISK_REPPROGRAMSUBSUB_ID = r.RISK_REPPROGRAMSUBSUB_ID
                LEFT JOIN risk_rep_level l ON l.RISK_REP_LEVEL_ID = r.RISKREP_LEVEL
                LEFT JOIN risk_recheck rc ON rc.RISK_RECHECK_RISKID = r.RISKREP_ID
                WHERE r.RISKREP_STATUS <> "CANCEL" AND r.RISKREP_DATESAVE BETWEEN ? AND ?
                GROUP BY r.RISKREP_ID
            ) AS a WHERE 1=1
        ';

        $params = [$start_date, $end_date];

        if ($month !== 'all') {
            $query .= ' AND MONTH(RISKREP_DATESAVE) = ?';
            $params[] = (int) $month;
        }

        if ($year !== 'all') {
            $query .= ' AND YEAR(RISKREP_DATESAVE) = ?';
            $params[] = (int) $year;
        }

        if ($level !== 'all') {
            if (strtolower($level) === 'null') {
                $query .= ' AND (RISK_REP_LEVEL_NAME IS NULL OR RISK_REP_LEVEL_NAME = "")';
            } else {
                $query .= ' AND RISK_REP_LEVEL_NAME = ?';
                $params[] = $level;
            }
        }

        if ($program_id !== 'all') {
            $query .= ' AND RISK_REPPROGRAM_ID = ?';
            $params[] = $program_id;
        }

        if ($sub_id !== 'all') {
            if ($sub_id === '0' || $sub_id === '00' || $sub_id === '') {
                $query .= ' AND (RISK_REPPROGRAMSUB_ID IS NULL OR RISK_REPPROGRAMSUB_ID = "0" OR RISK_REPPROGRAMSUB_ID = "00" OR RISK_REPPROGRAMSUB_ID = "")';
            } else {
                $query .= ' AND RISK_REPPROGRAMSUB_ID = ?';
                $params[] = $sub_id;
            }
        }

        if ($subsub_id !== 'all') {
            if ($subsub_id === '0' || $subsub_id === '000' || $subsub_id === '') {
                $query .= ' AND (RISK_REPPROGRAMSUBSUB_ID IS NULL OR RISK_REPPROGRAMSUBSUB_ID = "0" OR RISK_REPPROGRAMSUBSUB_ID = "000" OR RISK_REPPROGRAMSUBSUB_ID = "")';
            } else {
                $query .= ' AND RISK_REPPROGRAMSUBSUB_ID = ?';
                $params[] = $subsub_id;
            }
        }

        $query .= ' ORDER BY RISKREP_STARTDATE DESC';

        return DB::connection('backoffice')->select($query, $params);
    }

    private function get_breadcrumbs($program_id = 'all', $sub_id = 'all', $subsub_id = 'all')
    {
        $breadcrumbs = [];

        if ($program_id !== 'all' && $program_id !== '') {
            $p_name = DB::connection('backoffice')->table('risk_rep_program')->where('RISK_REPPROGRAM_ID', $program_id)->value('RISK_REPPROGRAM_NAME') ?: 'โปรแกรมหลัก';
            $breadcrumbs[] = [
                'name' => $p_name,
                'program_id' => $program_id,
                'sub_id' => 'all',
                'subsub_id' => 'all'
            ];
        }

        if ($sub_id !== 'all' && $sub_id !== '') {
            $s_name = DB::connection('backoffice')->table('risk_rep_program_sub')->where('RISK_REPPROGRAMSUB_ID', $sub_id)->value('RISK_REPPROGRAMSUB_NAME') ?: 'โปรแกรมย่อย 1';
            $breadcrumbs[] = [
                'name' => $s_name,
                'program_id' => $program_id,
                'sub_id' => $sub_id,
                'subsub_id' => 'all'
            ];
        }

        if ($subsub_id !== 'all' && $subsub_id !== '') {
            $ss_name = DB::connection('backoffice')->table('risk_rep_program_subsub')->where('RISK_REPPROGRAMSUBSUB_ID', $subsub_id)->value('RISK_REPPROGRAMSUBSUB_NAME') ?: 'โปรแกรมย่อย 2';
            $breadcrumbs[] = [
                'name' => $ss_name,
                'program_id' => $program_id,
                'sub_id' => $sub_id,
                'subsub_id' => $subsub_id
            ];
        }

        return $breadcrumbs;
    }
}
