<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function warfarin(Request $request)
    {
        $title = 'ข้อมูลการใช้ยา Warfarin';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // OPD Query
        $warfarin_opd = DB::connection('hosxp')->select('
            SELECT o.vn, o.vstdate, o.vsttime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                v.age_y,o.hn,o1.rxdate,o1.rxtime,d2.`code` AS drugusage,lh.report_date,lh.report_time,
                lo.lab_items_name_ref AS pt,lo.lab_order_result AS pt_result,
                lo2.lab_items_name_ref AS inr,lo2.lab_order_result AS inr_result,
                CASE WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370601") THEN "รพ.สต.หัวตะพาน" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("4","5","6","10","11")) THEN "รพ.สต.โนนหนามแท่ง"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("1","2","3","7","8","9","12")) THEN "รพ.สต.คำพระ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370603" ) THEN "รพ.สต.เค็งใหญ่"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370604" ) THEN "รพ.สต.โคกเลาะ"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("2","5","6","7","8","9")) THEN "รพ.สต.ขุมเหล็ก" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("1","3","4","10","11","12")) THEN "รพ.สต.โพนเมืองน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606"AND p.moopart IN ("1","3","7","10","11","12","13")) THEN "รพ.สต.สร้างถ่อน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606" AND p.moopart IN ("2","4","5","6","8","9") ) THEN "รพ.สต.นาคู" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("3","6","7","8","9")) THEN "รพ.สต.หนองยอ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("1","2","4","5","10","11","12")) THEN "รพ.สต.จิกดู่"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370608" ) THEN "PCU รัตนวารี" 
                ELSE "นอกเขตอำเภอหัวตะพาน" END AS "pcu" 
            FROM ovst o
            INNER JOIN opitemrece o1 ON o1.vn=o.vn AND o1.icode IN ("1550002","1500035","1500036")
            LEFT JOIN patient p ON p.hn=o.hn
            LEFT JOIN vn_stat v ON v.vn=o.vn
            LEFT JOIN drugitems d1 ON d1.icode=o1.icode
            LEFT JOIN drugusage d2 ON d2.drugusage=o1.drugusage
            INNER JOIN lab_head lh ON lh.vn=o.vn
            LEFT JOIN lab_head lh2 ON lh2.vn=o.vn
            LEFT JOIN lab_order lo ON lo.lab_order_number=lh.lab_order_number AND lo.lab_items_code ="350" 
            LEFT JOIN lab_order lo2 ON lo2.lab_order_number=lh2.lab_order_number AND lo2.lab_items_code ="353" 
            WHERE o.vstdate BETWEEN ? AND ?
            AND ((lo.lab_items_code ="350" AND lo.lab_order_result <>"") OR (lo2.lab_items_code ="353" AND lo2.lab_order_result <>""))
            GROUP BY o.vn,o1.icode,lo.lab_items_code
            ORDER BY pcu,o.hn,o.vstdate,o1.icode,lo.lab_items_code
        ', [$start_date, $end_date]);

        // IPD Query
        $warfarin_ipd = DB::connection('hosxp')->select('
            SELECT i.an, i.regdate, i.regtime, i.dchdate, i.dchtime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                a.age_y,i.hn,o1.rxdate,o1.rxtime,d2.`code` AS drugusage,lh.report_date,lh.report_time,
                lo.lab_items_name_ref AS pt,lo.lab_order_result AS pt_result,
                lo2.lab_items_name_ref AS inr,lo2.lab_order_result AS inr_result,
                CASE WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370601") THEN "รพ.สต.หัวตะพาน" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("4","5","6","10","11")) THEN "รพ.สต.โนนหนามแท่ง"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("1","2","3","7","8","9","12")) THEN "รพ.สต.คำพระ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370603" ) THEN "รพ.สต.เค็งใหญ่"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370604" ) THEN "รพ.สต.โคกเลาะ"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("2","5","6","7","8","9")) THEN "รพ.สต.ขุมเหล็ก" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("1","3","4","10","11","12")) THEN "รพ.สต.โพนเมืองน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606"AND p.moopart IN ("1","3","7","10","11","12","13")) THEN "รพ.สต.สร้างถ่อน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606" AND p.moopart IN ("2","4","5","6","8","9") ) THEN "รพ.สต.นาคู" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("3","6","7","8","9")) THEN "รพ.สต.หนองยอ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("1","2","4","5","10","11","12")) THEN "รพ.สต.จิกดู่"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370608" ) THEN "PCU รัตนวารี" 
                ELSE "นอกเขตอำเภอหัวตะพาน" END AS "pcu" 
            FROM ipt i
            INNER JOIN opitemrece o1 ON o1.an=i.an AND o1.icode IN ("1550002","1500035","1500036")
            LEFT JOIN patient p ON p.hn=i.hn
            LEFT JOIN an_stat a ON a.an=i.an
            LEFT JOIN drugitems d1 ON d1.icode=o1.icode
            LEFT JOIN drugusage d2 ON d2.drugusage=o1.drugusage
            INNER JOIN lab_head lh ON lh.vn=i.an
            LEFT JOIN lab_head lh2 ON lh2.vn=i.an
            LEFT JOIN lab_order lo ON lo.lab_order_number=lh.lab_order_number AND lo.lab_items_code ="350" 
            LEFT JOIN lab_order lo2 ON lo2.lab_order_number=lh2.lab_order_number AND lo2.lab_items_code ="353" 
            WHERE i.regdate BETWEEN ? AND ?
            AND ((lo.lab_items_code ="350" AND lo.lab_order_result <>"") OR (lo2.lab_items_code ="353" AND lo2.lab_order_result <>""))
            GROUP BY i.an,o1.icode,o1.rxdate,lo.lab_items_code
            ORDER BY pcu,i.an,o1.icode,o1.rxdate,lo.lab_items_code
        ', [$start_date, $end_date]);

        // Process grouping by vn (OPD) and an (IPD)
        $grouped_opd = [];
        foreach ($warfarin_opd as $row) {
            $vn = $row->vn;
            if (!isset($grouped_opd[$vn])) {
                $grouped_opd[$vn] = [
                    'vn' => $vn,
                    'vstdate' => $row->vstdate,
                    'vsttime' => $row->vsttime,
                    'ptname' => $row->ptname,
                    'age_y' => $row->age_y,
                    'hn' => $row->hn,
                    'pcu' => $row->pcu,
                    'drugs' => [],
                    'labs' => []
                ];
            }
            
            // Add drug and usage if not already added
            $drug_exists = false;
            foreach ($grouped_opd[$vn]['drugs'] as $d) {
                if ($d['name'] == $row->drug && $d['usage'] == $row->drugusage) {
                    $drug_exists = true;
                    break;
                }
            }
            if (!$drug_exists) {
                $grouped_opd[$vn]['drugs'][] = [
                    'name' => $row->drug,
                    'usage' => $row->drugusage,
                    'rxdate' => $row->rxdate,
                    'rxtime' => $row->rxtime
                ];
            }

            // Add lab result if not already added
            $lab_key = ($row->report_date ?? '') . '_' . ($row->pt_result ?? '') . '_' . ($row->inr_result ?? '');
            if (!isset($grouped_opd[$vn]['labs'][$lab_key])) {
                $grouped_opd[$vn]['labs'][$lab_key] = [
                    'report_date' => $row->report_date,
                    'report_time' => $row->report_time,
                    'pt' => $row->pt,
                    'pt_result' => $row->pt_result,
                    'inr' => $row->inr,
                    'inr_result' => $row->inr_result
                ];
            }
        }

        $grouped_ipd = [];
        foreach ($warfarin_ipd as $row) {
            $an = $row->an;
            if (!isset($grouped_ipd[$an])) {
                $grouped_ipd[$an] = [
                    'an' => $an,
                    'regdate' => $row->regdate,
                    'regtime' => $row->regtime,
                    'dchdate' => $row->dchdate,
                    'dchtime' => $row->dchtime,
                    'ptname' => $row->ptname,
                    'age_y' => $row->age_y,
                    'hn' => $row->hn,
                    'pcu' => $row->pcu,
                    'drugs' => [],
                    'labs' => []
                ];
            }
            
            // Add drug and usage if not already added
            $drug_exists = false;
            foreach ($grouped_ipd[$an]['drugs'] as $d) {
                if ($d['name'] == $row->drug && $d['usage'] == $row->drugusage) {
                    $drug_exists = true;
                    break;
                }
            }
            if (!$drug_exists) {
                $grouped_ipd[$an]['drugs'][] = [
                    'name' => $row->drug,
                    'usage' => $row->drugusage,
                    'rxdate' => $row->rxdate,
                    'rxtime' => $row->rxtime
                ];
            }

            // Add lab result if not already added
            $lab_key = ($row->report_date ?? '') . '_' . ($row->pt_result ?? '') . '_' . ($row->inr_result ?? '');
            if (!isset($grouped_ipd[$an]['labs'][$lab_key])) {
                $grouped_ipd[$an]['labs'][$lab_key] = [
                    'report_date' => $row->report_date,
                    'report_time' => $row->report_time,
                    'pt' => $row->pt,
                    'pt_result' => $row->pt_result,
                    'inr' => $row->inr,
                    'inr_result' => $row->inr_result
                ];
            }
        }

        // Process monthly counts for charts
        $monthly_opd = $this->aggregateMonthly($warfarin_opd, $start_date, $end_date, 'rxdate');
        $monthly_ipd = $this->aggregateMonthly($warfarin_ipd, $start_date, $end_date, 'rxdate');

        return view('hosxp.phar.warfarin', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'grouped_opd',
            'grouped_ipd',
            'monthly_opd',
            'monthly_ipd'
        ));
    }

    public function metformin(Request $request)
    {
        $title = 'ข้อมูลการใช้ยา Metformin';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // OPD Query
        $metformin_opd = DB::connection('hosxp')->select('
            SELECT o.vn, o.vstdate, o.vsttime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                v.age_y,o.hn,o1.rxdate,o1.rxtime,d2.`code` AS drugusage,lh.report_date,lh.report_time,
                lo.lab_items_name_ref AS pt,lo.lab_order_result AS pt_result,
                CASE WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370601") THEN "รพ.สต.หัวตะพาน" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("4","5","6","10","11")) THEN "รพ.สต.โนนหนามแท่ง"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("1","2","3","7","8","9","12")) THEN "รพ.สต.คำพระ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370603" ) THEN "รพ.สต.เค็งใหญ่"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370604" ) THEN "รพ.สต.โคกเลาะ"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("2","5","6","7","8","9")) THEN "รพ.สต.ขุมเหล็ก" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("1","3","4","10","11","12")) THEN "รพ.สต.โพนเมืองน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606"AND p.moopart IN ("1","3","7","10","11","12","13")) THEN "รพ.สต.สร้างถ่อน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606" AND p.moopart IN ("2","4","5","6","8","9") ) THEN "รพ.สต.นาคู" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("3","6","7","8","9")) THEN "รพ.สต.หนองยอ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("1","2","4","5","10","11","12")) THEN "รพ.สต.จิกดู่"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370608" ) THEN "PCU รัตนวารี" 
                ELSE "นอกเขตอำเภอหัวตะพาน" END AS "pcu" 
            FROM ovst o
            INNER JOIN opitemrece o1 ON o1.vn=o.vn AND o1.icode IN ("1000189","1550032")
            LEFT JOIN patient p ON p.hn=o.hn
            LEFT JOIN vn_stat v ON v.vn=o.vn
            LEFT JOIN drugitems d1 ON d1.icode=o1.icode
            LEFT JOIN drugusage d2 ON d2.drugusage=o1.drugusage
            INNER JOIN lab_head lh ON lh.vn=o.vn
            INNER JOIN lab_order lo ON lo.lab_order_number=lh.lab_order_number AND lo.lab_items_code = "693" 
            WHERE o.vstdate BETWEEN ? AND ?
            AND lo.lab_order_result <> ""
            GROUP BY o.vn,o1.icode
            ORDER BY pcu,o.hn,o.vstdate,o1.icode
        ', [$start_date, $end_date]);

        // IPD Query
        $metformin_ipd = DB::connection('hosxp')->select('
            SELECT i.an, i.regdate, i.regtime, i.dchdate, i.dchtime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                a.age_y,i.hn,o1.rxdate,o1.rxtime,d2.`code` AS drugusage,lh.report_date,lh.report_time,
                lo.lab_items_name_ref AS pt,lo.lab_order_result AS pt_result,
                CASE WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370601") THEN "รพ.สต.หัวตะพาน" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("4","5","6","10","11")) THEN "รพ.สต.โนนหนามแท่ง"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("1","2","3","7","8","9","12")) THEN "รพ.สต.คำพระ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370603" ) THEN "รพ.สต.เค็งใหญ่"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370604" ) THEN "รพ.สต.โคกเลาะ"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("2","5","6","7","8","9")) THEN "รพ.สต.ขุมเหล็ก" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("1","3","4","10","11","12")) THEN "รพ.สต.โพนเมืองน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606"AND p.moopart IN ("1","3","7","10","11","12","13")) THEN "รพ.สต.สร้างถ่อน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606" AND p.moopart IN ("2","4","5","6","8","9") ) THEN "รพ.สต.นาคู" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("3","6","7","8","9")) THEN "รพ.สต.หนองยอ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("1","2","4","5","10","11","12")) THEN "รพ.สต.จิกดู่"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370608" ) THEN "PCU รัตนวารี" 
                ELSE "นอกเขตอำเภอหัวตะพาน" END AS "pcu" 
            FROM ipt i
            INNER JOIN opitemrece o1 ON o1.an=i.an AND o1.icode IN ("1000189","1550032")
            LEFT JOIN patient p ON p.hn=i.hn
            LEFT JOIN an_stat a ON a.an=i.an
            LEFT JOIN drugitems d1 ON d1.icode=o1.icode
            LEFT JOIN drugusage d2 ON d2.drugusage=o1.drugusage
            INNER JOIN lab_head lh ON lh.vn=i.an
            INNER JOIN lab_order lo ON lo.lab_order_number=lh.lab_order_number AND lo.lab_items_code = "693" 
            WHERE i.regdate BETWEEN ? AND ?
            AND lo.lab_order_result <> ""
            GROUP BY i.an,o1.icode,o1.rxdate
            ORDER BY pcu,i.an,o1.icode,o1.rxdate
        ', [$start_date, $end_date]);

        // Process grouping by vn (OPD) and an (IPD)
        $grouped_opd = [];
        foreach ($metformin_opd as $row) {
            $vn = $row->vn;
            if (!isset($grouped_opd[$vn])) {
                $grouped_opd[$vn] = [
                    'vn' => $vn,
                    'vstdate' => $row->vstdate,
                    'vsttime' => $row->vsttime,
                    'ptname' => $row->ptname,
                    'age_y' => $row->age_y,
                    'hn' => $row->hn,
                    'pcu' => $row->pcu,
                    'drugs' => [],
                    'labs' => []
                ];
            }
            
            // Add drug
            $drug_exists = false;
            foreach ($grouped_opd[$vn]['drugs'] as $d) {
                if ($d['name'] == $row->drug && $d['usage'] == $row->drugusage) {
                    $drug_exists = true;
                    break;
                }
            }
            if (!$drug_exists) {
                $grouped_opd[$vn]['drugs'][] = [
                    'name' => $row->drug,
                    'usage' => $row->drugusage,
                    'rxdate' => $row->rxdate,
                    'rxtime' => $row->rxtime
                ];
            }

            // Add lab
            $lab_key = ($row->report_date ?? '') . '_' . ($row->pt_result ?? '');
            if (!isset($grouped_opd[$vn]['labs'][$lab_key])) {
                $grouped_opd[$vn]['labs'][$lab_key] = [
                    'report_date' => $row->report_date,
                    'report_time' => $row->report_time,
                    'pt' => $row->pt,
                    'pt_result' => $row->pt_result
                ];
            }
        }

        $grouped_ipd = [];
        foreach ($metformin_ipd as $row) {
            $an = $row->an;
            if (!isset($grouped_ipd[$an])) {
                $grouped_ipd[$an] = [
                    'an' => $an,
                    'regdate' => $row->regdate,
                    'regtime' => $row->regtime,
                    'dchdate' => $row->dchdate,
                    'dchtime' => $row->dchtime,
                    'ptname' => $row->ptname,
                    'age_y' => $row->age_y,
                    'hn' => $row->hn,
                    'pcu' => $row->pcu,
                    'drugs' => [],
                    'labs' => []
                ];
            }
            
            // Add drug
            $drug_exists = false;
            foreach ($grouped_ipd[$an]['drugs'] as $d) {
                if ($d['name'] == $row->drug && $d['usage'] == $row->drugusage) {
                    $drug_exists = true;
                    break;
                }
            }
            if (!$drug_exists) {
                $grouped_ipd[$an]['drugs'][] = [
                    'name' => $row->drug,
                    'usage' => $row->drugusage,
                    'rxdate' => $row->rxdate,
                    'rxtime' => $row->rxtime
                ];
            }

            // Add lab
            $lab_key = ($row->report_date ?? '') . '_' . ($row->pt_result ?? '');
            if (!isset($grouped_ipd[$an]['labs'][$lab_key])) {
                $grouped_ipd[$an]['labs'][$lab_key] = [
                    'report_date' => $row->report_date,
                    'report_time' => $row->report_time,
                    'pt' => $row->pt,
                    'pt_result' => $row->pt_result
                ];
            }
        }

        // Process monthly counts for charts
        $monthly_opd = $this->aggregateMonthly($metformin_opd, $start_date, $end_date, 'rxdate');
        $monthly_ipd = $this->aggregateMonthly($metformin_ipd, $start_date, $end_date, 'rxdate');

        return view('hosxp.phar.metformin', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'grouped_opd',
            'grouped_ipd',
            'monthly_opd',
            'monthly_ipd'
        ));
    }

    public function due(Request $request)
    {
        $title = 'ข้อมูลการใช้ยา DUE';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // OPD Query
        $due_opd = DB::connection('hosxp')->select('
            SELECT o.vn, o.vstdate, o.vsttime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                v.age_y,o.hn,o1.rxdate,o1.rxtime,d2.`code` AS drugusage,lh.report_date,lh.report_time,
                lo.lab_items_name_ref AS pt,lo.lab_order_result AS pt_result,
                CASE WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370601") THEN "รพ.สต.หัวตะพาน" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("4","5","6","10","11")) THEN "รพ.สต.โนนหนามแท่ง"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("1","2","3","7","8","9","12")) THEN "รพ.สต.คำพระ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370603" ) THEN "รพ.สต.เค็งใหญ่"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370604" ) THEN "รพ.สต.โคกเลาะ"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("2","5","6","7","8","9")) THEN "รพ.สต.ขุมเหล็ก" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("1","3","4","10","11","12")) THEN "รพ.สต.โพนเมืองน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606"AND p.moopart IN ("1","3","7","10","11","12","13")) THEN "รพ.สต.สร้างถ่อน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606" AND p.moopart IN ("2","4","5","6","8","9") ) THEN "รพ.สต.นาคู" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("3","6","7","8","9")) THEN "รพ.สต.หนองยอ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("1","2","4","5","10","11","12")) THEN "รพ.สต.จิกดู่"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370608" ) THEN "PCU รัตนวารี" 
                ELSE "นอกเขตอำเภอหัวตะพาน" END AS "pcu" 
            FROM ovst o
            INNER JOIN opitemrece o1 ON o1.vn=o.vn AND o1.icode IN ("1000048","1520046","1610023","1610015","1631004")
            LEFT JOIN patient p ON p.hn=o.hn
            LEFT JOIN vn_stat v ON v.vn=o.vn
            LEFT JOIN drugitems d1 ON d1.icode=o1.icode
            LEFT JOIN drugusage d2 ON d2.drugusage=o1.drugusage
            INNER JOIN lab_head lh ON lh.vn=o.vn
            INNER JOIN lab_order lo ON lo.lab_order_number=lh.lab_order_number AND lo.lab_items_code = "4" 
            WHERE o.vstdate BETWEEN ? AND ?
            AND lo.lab_order_result <> ""
            GROUP BY o.vn,o1.icode
            ORDER BY pcu,o.hn,o.vstdate,o1.icode
        ', [$start_date, $end_date]);

        // IPD Query
        $due_ipd = DB::connection('hosxp')->select('
            SELECT i.an, i.regdate, i.regtime, i.dchdate, i.dchtime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                a.age_y,i.hn,o1.rxdate,o1.rxtime,d2.`code` AS drugusage,lh.report_date,lh.report_time,
                lo.lab_items_name_ref AS pt,lo.lab_order_result AS pt_result,
                CASE WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370601") THEN "รพ.สต.หัวตะพาน" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("4","5","6","10","11")) THEN "รพ.สต.โนนหนามแท่ง"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370602" AND p.moopart IN ("1","2","3","7","8","9","12")) THEN "รพ.สต.คำพระ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370603" ) THEN "รพ.สต.เค็งใหญ่"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370604" ) THEN "รพ.สต.โคกเลาะ"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("2","5","6","7","8","9")) THEN "รพ.สต.ขุมเหล็ก" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370605" AND p.moopart IN ("1","3","4","10","11","12")) THEN "รพ.สต.โพนเมืองน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606"AND p.moopart IN ("1","3","7","10","11","12","13")) THEN "รพ.สต.สร้างถ่อน้อย" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370606" AND p.moopart IN ("2","4","5","6","8","9") ) THEN "รพ.สต.นาคู" 
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("3","6","7","8","9")) THEN "รพ.สต.หนองยอ"  
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370607" AND p.moopart IN ("1","2","4","5","10","11","12")) THEN "รพ.สต.จิกดู่"   
                WHEN (CONCAT(p.chwpart,p.amppart,p.tmbpart )="370608" ) THEN "PCU รัตนวารี" 
                ELSE "นอกเขตอำเภอหัวตะพาน" END AS "pcu" 
            FROM ipt i
            INNER JOIN opitemrece o1 ON o1.an=i.an AND o1.icode IN ("1000048","1520046","1610023","1610015","1631004")
            LEFT JOIN patient p ON p.hn=i.hn
            LEFT JOIN an_stat a ON a.an=i.an
            LEFT JOIN drugitems d1 ON d1.icode=o1.icode
            LEFT JOIN drugusage d2 ON d2.drugusage=o1.drugusage
            INNER JOIN lab_head lh ON lh.vn=i.an
            INNER JOIN lab_order lo ON lo.lab_order_number=lh.lab_order_number AND lo.lab_items_code = "4" 
            WHERE i.regdate BETWEEN ? AND ?
            AND lo.lab_order_result <> ""
            GROUP BY i.an,o1.icode,o1.rxdate
            ORDER BY pcu,i.an,o1.icode,o1.rxdate
        ', [$start_date, $end_date]);

        // Process grouping by vn (OPD) and an (IPD)
        $grouped_opd = [];
        foreach ($due_opd as $row) {
            $vn = $row->vn;
            if (!isset($grouped_opd[$vn])) {
                $grouped_opd[$vn] = [
                    'vn' => $vn,
                    'vstdate' => $row->vstdate,
                    'vsttime' => $row->vsttime,
                    'ptname' => $row->ptname,
                    'age_y' => $row->age_y,
                    'hn' => $row->hn,
                    'pcu' => $row->pcu,
                    'drugs' => [],
                    'labs' => []
                ];
            }
            
            // Add drug
            $drug_exists = false;
            foreach ($grouped_opd[$vn]['drugs'] as $d) {
                if ($d['name'] == $row->drug && $d['usage'] == $row->drugusage) {
                    $drug_exists = true;
                    break;
                }
            }
            if (!$drug_exists) {
                $grouped_opd[$vn]['drugs'][] = [
                    'name' => $row->drug,
                    'usage' => $row->drugusage,
                    'rxdate' => $row->rxdate,
                    'rxtime' => $row->rxtime
                ];
            }

            // Add lab
            $lab_key = ($row->report_date ?? '') . '_' . ($row->pt_result ?? '');
            if (!isset($grouped_opd[$vn]['labs'][$lab_key])) {
                $grouped_opd[$vn]['labs'][$lab_key] = [
                    'report_date' => $row->report_date,
                    'report_time' => $row->report_time,
                    'pt' => $row->pt,
                    'pt_result' => $row->pt_result
                ];
            }
        }

        $grouped_ipd = [];
        foreach ($due_ipd as $row) {
            $an = $row->an;
            if (!isset($grouped_ipd[$an])) {
                $grouped_ipd[$an] = [
                    'an' => $an,
                    'regdate' => $row->regdate,
                    'regtime' => $row->regtime,
                    'dchdate' => $row->dchdate,
                    'dchtime' => $row->dchtime,
                    'ptname' => $row->ptname,
                    'age_y' => $row->age_y,
                    'hn' => $row->hn,
                    'pcu' => $row->pcu,
                    'drugs' => [],
                    'labs' => []
                ];
            }
            
            // Add drug
            $drug_exists = false;
            foreach ($grouped_ipd[$an]['drugs'] as $d) {
                if ($d['name'] == $row->drug && $d['usage'] == $row->drugusage) {
                    $drug_exists = true;
                    break;
                }
            }
            if (!$drug_exists) {
                $grouped_ipd[$an]['drugs'][] = [
                    'name' => $row->drug,
                    'usage' => $row->drugusage,
                    'rxdate' => $row->rxdate,
                    'rxtime' => $row->rxtime
                ];
            }

            // Add lab
            $lab_key = ($row->report_date ?? '') . '_' . ($row->pt_result ?? '');
            if (!isset($grouped_ipd[$an]['labs'][$lab_key])) {
                $grouped_ipd[$an]['labs'][$lab_key] = [
                    'report_date' => $row->report_date,
                    'report_time' => $row->report_time,
                    'pt' => $row->pt,
                    'pt_result' => $row->pt_result
                ];
            }
        }

        // Process monthly counts for charts
        $monthly_opd = $this->aggregateMonthly($due_opd, $start_date, $end_date, 'rxdate');
        $monthly_ipd = $this->aggregateMonthly($due_ipd, $start_date, $end_date, 'rxdate');

        return view('hosxp.phar.due', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'grouped_opd',
            'grouped_ipd',
            'monthly_opd',
            'monthly_ipd'
        ));
    }

    public function antiviral(Request $request)
    {
        $title = 'ข้อมูลการใช้ยาต้านไวรัส';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // OPD Query
        $antiviral_opd = DB::connection('hosxp')->select('
            SELECT o.vn, o.vstdate, o.vsttime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                v.age_y,o.hn,p.cid,o1.rxdate,o1.rxtime,SUM(o1.qty) AS qty,pt.name AS pttype_name
            FROM ovst o
            INNER JOIN opitemrece o1 ON o1.vn=o.vn AND o1.icode IN ("1630768","1630855","1500051","1306001")
            LEFT JOIN patient p ON p.hn=o.hn
            LEFT JOIN vn_stat v ON v.vn=o.vn
            LEFT JOIN drugitems d1 ON d1.icode=o1.icode
            LEFT JOIN pttype pt ON pt.pttype=o.pttype
            WHERE o.vstdate BETWEEN ? AND ?
            AND pt.hipdata_code LIKE "SS%"
            GROUP BY o.vn,o1.icode
            ORDER BY o.vstdate,o.hn,o1.icode
        ', [$start_date, $end_date]);

        // IPD Query
        $antiviral_ipd = DB::connection('hosxp')->select('
            SELECT i.an, i.regdate, i.regtime, i.dchdate, i.dchtime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                a.age_y,i.hn,p.cid,o1.rxdate,o1.rxtime,SUM(o1.qty) AS qty,pt.name AS pttype_name
            FROM ipt i
            INNER JOIN opitemrece o1 ON o1.an=i.an AND o1.icode IN ("1630768","1630855","1500051","1306001")
            LEFT JOIN patient p ON p.hn=i.hn
            LEFT JOIN an_stat a ON a.an=i.an
            LEFT JOIN drugitems d1 ON d1.icode=o1.icode
            LEFT JOIN pttype pt ON pt.pttype=i.pttype
            WHERE i.regdate BETWEEN ? AND ?
            AND pt.hipdata_code LIKE "SS%"
            GROUP BY i.an,o1.icode
            ORDER BY i.regdate,i.hn,o1.icode
        ', [$start_date, $end_date]);

        return view('hosxp.phar.antiviral', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'antiviral_opd',
            'antiviral_ipd'
        ));
    }

    public function antiviral_pdf(Request $request)
    {
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $type = $request->type ?? 'opd';

        if ($type === 'ipd') {
            $data = DB::connection('hosxp')->select('
                SELECT i.an, i.regdate AS rxdate, i.regtime AS rxtime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                    a.age_y,i.hn,p.cid,SUM(o1.qty) AS qty,pt.name AS pttype_name
                FROM ipt i
                INNER JOIN opitemrece o1 ON o1.an=i.an AND o1.icode IN ("1630768","1630855","1500051","1306001")
                LEFT JOIN patient p ON p.hn=i.hn
                LEFT JOIN an_stat a ON a.an=i.an
                LEFT JOIN drugitems d1 ON d1.icode=o1.icode
                LEFT JOIN pttype pt ON pt.pttype=i.pttype
                WHERE i.regdate BETWEEN ? AND ?
                AND pt.hipdata_code LIKE "SS%"
                GROUP BY i.an,o1.icode
                ORDER BY i.regdate,i.hn,o1.icode
            ', [$start_date, $end_date]);
            $title = 'รายงานรายชื่อผู้ใช้เวชภัณฑ์ยาต้านไวรัส';
            $subtitle = 'สิทธิประกันสังคมผู้ป่วยใน โรงพยาบาลหัวตะพาน';
        } else {
            $data = DB::connection('hosxp')->select('
                SELECT o.vn, o.vstdate AS rxdate, o.vsttime AS rxtime, CONCAT(d1.`name`," ",d1.strength) AS drug ,CONCAT(p.pname,p.fname," ",p.lname) AS ptname,
                    v.age_y,o.hn,p.cid,SUM(o1.qty) AS qty,pt.name AS pttype_name
                FROM ovst o
                INNER JOIN opitemrece o1 ON o1.vn=o.vn AND o1.icode IN ("1630768","1630855","1500051","1306001")
                LEFT JOIN patient p ON p.hn=o.hn
                LEFT JOIN vn_stat v ON v.vn=o.vn
                LEFT JOIN drugitems d1 ON d1.icode=o1.icode
                LEFT JOIN pttype pt ON pt.pttype=o.pttype
                WHERE o.vstdate BETWEEN ? AND ?
                AND pt.hipdata_code LIKE "SS%"
                GROUP BY o.vn,o1.icode
                ORDER BY o.vstdate,o.hn,o1.icode
            ', [$start_date, $end_date]);
            $title = 'รายงานรายชื่อผู้ใช้เวชภัณฑ์ยาต้านไวรัส';
            $subtitle = 'สิทธิประกันสังคมผู้ป่วยนอก โรงพยาบาลหัวตะพาน';
        }

        // Calculate summary of each drug type at the bottom
        $summary = [];
        foreach ($data as $row) {
            $drug = $row->drug;
            if (!isset($summary[$drug])) {
                $summary[$drug] = 0;
            }
            $summary[$drug] += $row->qty;
        }

        $pdf = Pdf::loadView('hosxp.phar.antiviral_pdf', compact(
            'title',
            'subtitle',
            'start_date',
            'end_date',
            'data',
            'summary',
            'type'
        ));

        return $pdf->stream('antiviral_' . $type . '_report.pdf');
    }

    public function hd(Request $request)
    {
        $title = 'ข้อมูลการใช้ยา HD';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // OPD Query
        $hd_opd = DB::connection('hosxp')->select('
            SELECT 
                o.icode,
                d.name AS drug_name,
                d.generic_name AS generic_name,
                COUNT(DISTINCT o.vn) AS total_visit,
                SUM(o.qty) AS total_qty,
                SUM(o.qty * o.cost) AS total_cost,
                SUM(o.sum_price) AS total_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "UCS" THEN o.vn END) AS ucs_visit,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty ELSE 0 END) AS ucs_qty,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty * o.cost ELSE 0 END) AS ucs_cost,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.sum_price ELSE 0 END) AS ucs_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "OFC" THEN o.vn END) AS ofc_visit,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty ELSE 0 END) AS ofc_qty,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty * o.cost ELSE 0 END) AS ofc_cost,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.sum_price ELSE 0 END) AS ofc_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "LGO" THEN o.vn END) AS lgo_visit,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty ELSE 0 END) AS lgo_qty,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty * o.cost ELSE 0 END) AS lgo_cost,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.sum_price ELSE 0 END) AS lgo_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.vn END) AS sss_visit,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty ELSE 0 END) AS sss_qty,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty * o.cost ELSE 0 END) AS sss_cost,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.sum_price ELSE 0 END) AS sss_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.vn END) AS other_visit,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty ELSE 0 END) AS other_qty,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty * o.cost ELSE 0 END) AS other_cost,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.sum_price ELSE 0 END) AS other_price
            FROM opitemrece o
            INNER JOIN drugitems_property_list dpl ON dpl.icode = o.icode AND dpl.drugitems_property_id = 8
            LEFT JOIN pttype p ON p.pttype = o.pttype
            LEFT JOIN drugitems d ON d.icode = o.icode								
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.vn IS NOT NULL AND o.vn <> "")
            GROUP BY o.icode
            ORDER BY total_price DESC
        ', [$start_date, $end_date]);

        // IPD Query
        $hd_ipd = DB::connection('hosxp')->select('
            SELECT 
                o.icode,
                d.name AS drug_name,
                d.generic_name AS generic_name,
                COUNT(DISTINCT o.an) AS total_visit,
                SUM(o.qty) AS total_qty,
                SUM(o.qty * o.cost) AS total_cost,
                SUM(o.sum_price) AS total_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "UCS" THEN o.an END) AS ucs_visit,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty ELSE 0 END) AS ucs_qty,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty * o.cost ELSE 0 END) AS ucs_cost,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.sum_price ELSE 0 END) AS ucs_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "OFC" THEN o.an END) AS ofc_visit,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty ELSE 0 END) AS ofc_qty,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty * o.cost ELSE 0 END) AS ofc_cost,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.sum_price ELSE 0 END) AS ofc_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "LGO" THEN o.an END) AS lgo_visit,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty ELSE 0 END) AS lgo_qty,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty * o.cost ELSE 0 END) AS lgo_cost,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.sum_price ELSE 0 END) AS lgo_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.an END) AS sss_visit,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty ELSE 0 END) AS sss_qty,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty * o.cost ELSE 0 END) AS sss_cost,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.sum_price ELSE 0 END) AS sss_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.an END) AS other_visit,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty ELSE 0 END) AS other_qty,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty * o.cost ELSE 0 END) AS other_cost,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.sum_price ELSE 0 END) AS other_price
            FROM opitemrece o
            INNER JOIN drugitems_property_list dpl ON dpl.icode = o.icode AND dpl.drugitems_property_id = 8
            LEFT JOIN pttype p ON p.pttype = o.pttype
            LEFT JOIN drugitems d ON d.icode = o.icode								
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.an IS NOT NULL AND o.an <> "")
            GROUP BY o.icode
            ORDER BY total_price DESC
        ', [$start_date, $end_date]);

        // Monthly cost for chart
        $monthly_raw_opd = DB::connection('hosxp')->select('
            SELECT 
                d.name AS drug_name,
                YEAR(o.rxdate) as y,
                MONTH(o.rxdate) as m,
                SUM(o.qty * o.cost) AS cost
            FROM opitemrece o
            INNER JOIN drugitems_property_list dpl ON dpl.icode = o.icode AND dpl.drugitems_property_id = 8
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.vn IS NOT NULL AND o.vn <> "")
            GROUP BY d.name, y, m
            ORDER BY d.name, y, m
        ', [$start_date, $end_date]);

        $monthly_raw_ipd = DB::connection('hosxp')->select('
            SELECT 
                d.name AS drug_name,
                YEAR(o.rxdate) as y,
                MONTH(o.rxdate) as m,
                SUM(o.qty * o.cost) AS cost
            FROM opitemrece o
            INNER JOIN drugitems_property_list dpl ON dpl.icode = o.icode AND dpl.drugitems_property_id = 8
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.an IS NOT NULL AND o.an <> "")
            GROUP BY d.name, y, m
            ORDER BY d.name, y, m
        ', [$start_date, $end_date]);

        // Generate all months in range for X-Axis
        $thai_months = [
            '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
            '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
            '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
        ];
        
        $start = new \DateTime($start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($end_date);
        $end->modify('last day of this month');
        
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end);
        
        $month_keys = [];
        $month_categories = [];
        foreach ($period as $dt) {
            $ym = $dt->format('Y-m');
            $month_keys[] = $ym;
            list($y, $m) = explode('-', $ym);
            $thai_year = ($y + 543) % 100;
            $month_categories[] = $thai_months[$m] . ' ' . $thai_year;
        }

        // Format chart helper
        $formatChart = function($raw_data) use ($month_keys) {
            $series = [];
            $drugs_data = [];
            
            // Group raw data by drug name
            foreach ($raw_data as $row) {
                $drug_name = $row->drug_name;
                $ym = sprintf('%04d-%02d', $row->y, $row->m);
                if (!isset($drugs_data[$drug_name])) {
                    $drugs_data[$drug_name] = array_fill_keys($month_keys, 0);
                }
                $drugs_data[$drug_name][$ym] = (float)$row->cost;
            }
            
            foreach ($drugs_data as $drug_name => $values) {
                $series[] = [
                    'name' => $drug_name,
                    'data' => array_values($values)
                ];
            }
            
            return $series;
        };

        $chart_series_opd = $formatChart($monthly_raw_opd);
        $chart_series_ipd = $formatChart($monthly_raw_ipd);

        return view('hosxp.phar.hd', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'hd_opd',
            'hd_ipd',
            'month_categories',
            'chart_series_opd',
            'chart_series_ipd'
        ));
    }

    public function esrd(Request $request)
    {
        $title = 'ข้อมูลการใช้ยา ESRD';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // OPD Query
        $esrd_opd = DB::connection('hosxp')->select('
            SELECT 
                o.icode,
                d.name AS drug_name,
                d.generic_name AS generic_name,
                COUNT(DISTINCT o.vn) AS total_visit,
                SUM(o.qty) AS total_qty,
                SUM(o.qty * o.cost) AS total_cost,
                SUM(o.sum_price) AS total_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "UCS" THEN o.vn END) AS ucs_visit,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty ELSE 0 END) AS ucs_qty,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty * o.cost ELSE 0 END) AS ucs_cost,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.sum_price ELSE 0 END) AS ucs_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "OFC" THEN o.vn END) AS ofc_visit,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty ELSE 0 END) AS ofc_qty,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty * o.cost ELSE 0 END) AS ofc_cost,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.sum_price ELSE 0 END) AS ofc_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "LGO" THEN o.vn END) AS lgo_visit,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty ELSE 0 END) AS lgo_qty,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty * o.cost ELSE 0 END) AS lgo_cost,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.sum_price ELSE 0 END) AS lgo_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.vn END) AS sss_visit,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty ELSE 0 END) AS sss_qty,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty * o.cost ELSE 0 END) AS sss_cost,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.sum_price ELSE 0 END) AS sss_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.vn END) AS other_visit,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty ELSE 0 END) AS other_qty,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty * o.cost ELSE 0 END) AS other_cost,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.sum_price ELSE 0 END) AS other_price
            FROM opitemrece o
            INNER JOIN drugitems_property_list dpl ON dpl.icode = o.icode AND dpl.drugitems_property_id = 7
            LEFT JOIN pttype p ON p.pttype = o.pttype
            LEFT JOIN drugitems d ON d.icode = o.icode								
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.vn IS NOT NULL AND o.vn <> "")
            GROUP BY o.icode
            ORDER BY total_price DESC
        ', [$start_date, $end_date]);

        // IPD Query
        $esrd_ipd = DB::connection('hosxp')->select('
            SELECT 
                o.icode,
                d.name AS drug_name,
                d.generic_name AS generic_name,
                COUNT(DISTINCT o.an) AS total_visit,
                SUM(o.qty) AS total_qty,
                SUM(o.qty * o.cost) AS total_cost,
                SUM(o.sum_price) AS total_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "UCS" THEN o.an END) AS ucs_visit,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty ELSE 0 END) AS ucs_qty,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty * o.cost ELSE 0 END) AS ucs_cost,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.sum_price ELSE 0 END) AS ucs_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "OFC" THEN o.an END) AS ofc_visit,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty ELSE 0 END) AS ofc_qty,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty * o.cost ELSE 0 END) AS ofc_cost,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.sum_price ELSE 0 END) AS ofc_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "LGO" THEN o.an END) AS lgo_visit,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty ELSE 0 END) AS lgo_qty,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty * o.cost ELSE 0 END) AS lgo_cost,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.sum_price ELSE 0 END) AS lgo_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.an END) AS sss_visit,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty ELSE 0 END) AS sss_qty,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty * o.cost ELSE 0 END) AS sss_cost,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.sum_price ELSE 0 END) AS sss_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.an END) AS other_visit,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty ELSE 0 END) AS other_qty,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty * o.cost ELSE 0 END) AS other_cost,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.sum_price ELSE 0 END) AS other_price
            FROM opitemrece o
            INNER JOIN drugitems_property_list dpl ON dpl.icode = o.icode AND dpl.drugitems_property_id = 7
            LEFT JOIN pttype p ON p.pttype = o.pttype
            LEFT JOIN drugitems d ON d.icode = o.icode								
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.an IS NOT NULL AND o.an <> "")
            GROUP BY o.icode
            ORDER BY total_price DESC
        ', [$start_date, $end_date]);

        // Monthly qty for chart
        $monthly_raw_opd = DB::connection('hosxp')->select('
            SELECT 
                d.name AS drug_name,
                YEAR(o.rxdate) as y,
                MONTH(o.rxdate) as m,
                SUM(o.qty) AS qty
            FROM opitemrece o
            INNER JOIN drugitems_property_list dpl ON dpl.icode = o.icode AND dpl.drugitems_property_id = 7
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.vn IS NOT NULL AND o.vn <> "")
            GROUP BY d.name, y, m
            ORDER BY d.name, y, m
        ', [$start_date, $end_date]);

        $monthly_raw_ipd = DB::connection('hosxp')->select('
            SELECT 
                d.name AS drug_name,
                YEAR(o.rxdate) as y,
                MONTH(o.rxdate) as m,
                SUM(o.qty) AS qty
            FROM opitemrece o
            INNER JOIN drugitems_property_list dpl ON dpl.icode = o.icode AND dpl.drugitems_property_id = 7
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.an IS NOT NULL AND o.an <> "")
            GROUP BY d.name, y, m
            ORDER BY d.name, y, m
        ', [$start_date, $end_date]);

        // Generate all months in range for X-Axis
        $thai_months = [
            '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
            '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
            '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
        ];
        
        $start = new \DateTime($start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($end_date);
        $end->modify('last day of this month');
        
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end);
        
        $month_keys = [];
        $month_categories = [];
        foreach ($period as $dt) {
            $ym = $dt->format('Y-m');
            $month_keys[] = $ym;
            list($y, $m) = explode('-', $ym);
            $thai_year = ($y + 543) % 100;
            $month_categories[] = $thai_months[$m] . ' ' . $thai_year;
        }

        // Format chart helper
        $formatChart = function($raw_data) use ($month_keys) {
            $series = [];
            $drugs_data = [];
            
            // Group raw data by drug name
            foreach ($raw_data as $row) {
                $drug_name = $row->drug_name;
                $ym = sprintf('%04d-%02d', $row->y, $row->m);
                if (!isset($drugs_data[$drug_name])) {
                    $drugs_data[$drug_name] = array_fill_keys($month_keys, 0);
                }
                $drugs_data[$drug_name][$ym] = (float)$row->qty;
            }
            
            foreach ($drugs_data as $drug_name => $values) {
                $series[] = [
                    'name' => $drug_name,
                    'data' => array_values($values)
                ];
            }
            
            return $series;
        };

        $chart_series_opd = $formatChart($monthly_raw_opd);
        $chart_series_ipd = $formatChart($monthly_raw_ipd);

        return view('hosxp.phar.esrd', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'esrd_opd',
            'esrd_ipd',
            'month_categories',
            'chart_series_opd',
            'chart_series_ipd'
        ));
    }

    public function herbal(Request $request)
    {
        $title = 'มูลค่าการใช้ยาสมุนไพร';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // OPD Query (Optimized)
        $herbal_opd = DB::connection('hosxp')->select('
            SELECT 
                o.icode,
                CONCAT(d.name, SPACE(1), d.strength) AS drug_name,
                d.generic_name,
                COUNT(DISTINCT o.hn) AS total_hn,
                COUNT(DISTINCT o.vn) AS total_visit,
                SUM(o.qty) AS total_qty,
                SUM(o.qty * o.cost) AS total_cost,
                SUM(o.sum_price) AS total_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "UCS" THEN o.vn END) AS ucs_visit,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty ELSE 0 END) AS ucs_qty,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty * o.cost ELSE 0 END) AS ucs_cost,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.sum_price ELSE 0 END) AS ucs_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "OFC" THEN o.vn END) AS ofc_visit,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty ELSE 0 END) AS ofc_qty,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty * o.cost ELSE 0 END) AS ofc_cost,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.sum_price ELSE 0 END) AS ofc_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "LGO" THEN o.vn END) AS lgo_visit,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty ELSE 0 END) AS lgo_qty,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty * o.cost ELSE 0 END) AS lgo_cost,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.sum_price ELSE 0 END) AS lgo_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.vn END) AS sss_visit,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty ELSE 0 END) AS sss_qty,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty * o.cost ELSE 0 END) AS sss_cost,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.sum_price ELSE 0 END) AS sss_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.vn END) AS other_visit,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty ELSE 0 END) AS other_qty,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty * o.cost ELSE 0 END) AS other_cost,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.sum_price ELSE 0 END) AS other_price
            FROM opitemrece o
            INNER JOIN (
                SELECT icode FROM drugitems_ref_code WHERE drugitems_ref_code_type_id = 1 AND ref_code LIKE "4%"
                UNION
                SELECT icode FROM drugitems_property_list WHERE drugitems_property_id = 1
            ) herbal ON herbal.icode = o.icode
            LEFT JOIN pttype p ON p.pttype = o.pttype
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.vn IS NOT NULL AND o.vn <> "")
            GROUP BY o.icode
            ORDER BY d.name
        ', [$start_date, $end_date]);

        // IPD Query (Optimized)
        $herbal_ipd = DB::connection('hosxp')->select('
            SELECT 
                o.icode,
                CONCAT(d.name, SPACE(1), d.strength) AS drug_name,
                d.generic_name,
                COUNT(DISTINCT o.hn) AS total_hn,
                COUNT(DISTINCT o.an) AS total_visit,
                SUM(o.qty) AS total_qty,
                SUM(o.qty * o.cost) AS total_cost,
                SUM(o.sum_price) AS total_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "UCS" THEN o.an END) AS ucs_visit,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty ELSE 0 END) AS ucs_qty,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty * o.cost ELSE 0 END) AS ucs_cost,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.sum_price ELSE 0 END) AS ucs_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "OFC" THEN o.an END) AS ofc_visit,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty ELSE 0 END) AS ofc_qty,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty * o.cost ELSE 0 END) AS ofc_cost,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.sum_price ELSE 0 END) AS ofc_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "LGO" THEN o.an END) AS lgo_visit,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty ELSE 0 END) AS lgo_qty,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty * o.cost ELSE 0 END) AS lgo_cost,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.sum_price ELSE 0 END) AS lgo_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.an END) AS sss_visit,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty ELSE 0 END) AS sss_qty,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty * o.cost ELSE 0 END) AS sss_cost,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.sum_price ELSE 0 END) AS sss_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.an END) AS other_visit,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty ELSE 0 END) AS other_qty,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty * o.cost ELSE 0 END) AS other_cost,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.sum_price ELSE 0 END) AS other_price
            FROM opitemrece o
            INNER JOIN (
                SELECT icode FROM drugitems_ref_code WHERE drugitems_ref_code_type_id = 1 AND ref_code LIKE "4%"
                UNION
                SELECT icode FROM drugitems_property_list WHERE drugitems_property_id = 1
            ) herbal ON herbal.icode = o.icode
            LEFT JOIN pttype p ON p.pttype = o.pttype
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.an IS NOT NULL AND o.an <> "")
            GROUP BY o.icode
            ORDER BY d.name
        ', [$start_date, $end_date]);

        // Monthly qty for chart
        $monthly_raw_opd = DB::connection('hosxp')->select('
            SELECT 
                d.name AS drug_name,
                YEAR(o.rxdate) as y,
                MONTH(o.rxdate) as m,
                SUM(o.qty) AS qty
            FROM opitemrece o
            INNER JOIN (
                SELECT icode FROM drugitems_ref_code WHERE drugitems_ref_code_type_id = 1 AND ref_code LIKE "4%"
                UNION
                SELECT icode FROM drugitems_property_list WHERE drugitems_property_id = 1
            ) herbal ON herbal.icode = o.icode
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.vn IS NOT NULL AND o.vn <> "")
            GROUP BY d.name, y, m
            ORDER BY d.name, y, m
        ', [$start_date, $end_date]);

        $monthly_raw_ipd = DB::connection('hosxp')->select('
            SELECT 
                d.name AS drug_name,
                YEAR(o.rxdate) as y,
                MONTH(o.rxdate) as m,
                SUM(o.qty) AS qty
            FROM opitemrece o
            INNER JOIN (
                SELECT icode FROM drugitems_ref_code WHERE drugitems_ref_code_type_id = 1 AND ref_code LIKE "4%"
                UNION
                SELECT icode FROM drugitems_property_list WHERE drugitems_property_id = 1
            ) herbal ON herbal.icode = o.icode
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND (o.an IS NOT NULL AND o.an <> "")
            GROUP BY d.name, y, m
            ORDER BY d.name, y, m
        ', [$start_date, $end_date]);

        // Generate all months in range for X-Axis
        $thai_months = [
            '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
            '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
            '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
        ];
        
        $start = new \DateTime($start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($end_date);
        $end->modify('last day of this month');
        
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end);
        
        $month_keys = [];
        $month_categories = [];
        foreach ($period as $dt) {
            $ym = $dt->format('Y-m');
            $month_keys[] = $ym;
            list($y, $m) = explode('-', $ym);
            $thai_year = ($y + 543) % 100;
            $month_categories[] = $thai_months[$m] . ' ' . $thai_year;
        }

        // Format chart helper (limit to top 10 drugs by total qty to keep chart clean)
        $formatChart = function($raw_data) use ($month_keys) {
            $series = [];
            $drugs_data = [];
            $drug_totals = [];
            
            // Group raw data by drug name
            foreach ($raw_data as $row) {
                $drug_name = $row->drug_name;
                $ym = sprintf('%04d-%02d', $row->y, $row->m);
                if (!isset($drugs_data[$drug_name])) {
                    $drugs_data[$drug_name] = array_fill_keys($month_keys, 0);
                    $drug_totals[$drug_name] = 0;
                }
                $drugs_data[$drug_name][$ym] = (float)$row->qty;
                $drug_totals[$drug_name] += (float)$row->qty;
            }
            
            // Sort by total quantity descending and slice top 10
            arsort($drug_totals);
            $top_drugs = array_slice(array_keys($drug_totals), 0, 10);
            
            foreach ($top_drugs as $drug_name) {
                if (isset($drugs_data[$drug_name])) {
                    $series[] = [
                        'name' => $drug_name,
                        'data' => array_values($drugs_data[$drug_name])
                    ];
                }
            }
            
            return $series;
        };

        $chart_series_opd = $formatChart($monthly_raw_opd);
        $chart_series_ipd = $formatChart($monthly_raw_ipd);

        return view('hosxp.phar.herbal', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'herbal_opd',
            'herbal_ipd',
            'month_categories',
            'chart_series_opd',
            'chart_series_ipd'
        ));
    }

    public function dmht(Request $request)
    {
        $title = 'ข้อมูลการใช้ยา DM-HT';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $target_icodes = [
            "1000199","1000200","1000160","1510019","1000258","1510004","1550032","1000189","1610057",
            "1000013","1000120","1000121","1000122","1000123","1510023","1000286","1570010","1000016","1000209",
            "1000312","1000103","1000104","1540019","1000034","1560002","1000195","1000250","1000102","1520023","1500020"
        ];

        // OPD Query
        $dmht_opd = DB::connection('hosxp')->select('
            SELECT 
                o.icode,
                d.name AS drug_name,
                d.generic_name AS generic_name,
                COUNT(DISTINCT o.vn) AS total_visit,
                SUM(o.qty) AS total_qty,
                SUM(o.qty * o.cost) AS total_cost,
                SUM(o.sum_price) AS total_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "UCS" THEN o.vn END) AS ucs_visit,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty ELSE 0 END) AS ucs_qty,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty * o.cost ELSE 0 END) AS ucs_cost,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.sum_price ELSE 0 END) AS ucs_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "OFC" THEN o.vn END) AS ofc_visit,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty ELSE 0 END) AS ofc_qty,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty * o.cost ELSE 0 END) AS ofc_cost,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.sum_price ELSE 0 END) AS ofc_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "LGO" THEN o.vn END) AS lgo_visit,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty ELSE 0 END) AS lgo_qty,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty * o.cost ELSE 0 END) AS lgo_cost,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.sum_price ELSE 0 END) AS lgo_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.vn END) AS sss_visit,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty ELSE 0 END) AS sss_qty,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty * o.cost ELSE 0 END) AS sss_cost,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.sum_price ELSE 0 END) AS sss_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.vn END) AS other_visit,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty ELSE 0 END) AS other_qty,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty * o.cost ELSE 0 END) AS other_cost,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.sum_price ELSE 0 END) AS other_price
            FROM opitemrece o
            LEFT JOIN pttype p ON p.pttype = o.pttype
            LEFT JOIN drugitems d ON d.icode = o.icode								
            WHERE o.rxdate BETWEEN ? AND ?
              AND o.icode IN (' . implode(',', array_map(fn($c) => '"' . $c . '"', $target_icodes)) . ')
              AND (o.vn IS NOT NULL AND o.vn <> "")
            GROUP BY o.icode
            ORDER BY total_price DESC
        ', [$start_date, $end_date]);

        // IPD Query
        $dmht_ipd = DB::connection('hosxp')->select('
            SELECT 
                o.icode,
                d.name AS drug_name,
                d.generic_name AS generic_name,
                COUNT(DISTINCT o.an) AS total_visit,
                SUM(o.qty) AS total_qty,
                SUM(o.qty * o.cost) AS total_cost,
                SUM(o.sum_price) AS total_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "UCS" THEN o.an END) AS ucs_visit,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty ELSE 0 END) AS ucs_qty,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.qty * o.cost ELSE 0 END) AS ucs_cost,
                SUM(CASE WHEN p.hipdata_code = "UCS" THEN o.sum_price ELSE 0 END) AS ucs_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "OFC" THEN o.an END) AS ofc_visit,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty ELSE 0 END) AS ofc_qty,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.qty * o.cost ELSE 0 END) AS ofc_cost,
                SUM(CASE WHEN p.hipdata_code = "OFC" THEN o.sum_price ELSE 0 END) AS ofc_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code = "LGO" THEN o.an END) AS lgo_visit,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty ELSE 0 END) AS lgo_qty,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.qty * o.cost ELSE 0 END) AS lgo_cost,
                SUM(CASE WHEN p.hipdata_code = "LGO" THEN o.sum_price ELSE 0 END) AS lgo_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.an END) AS sss_visit,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty ELSE 0 END) AS sss_qty,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.qty * o.cost ELSE 0 END) AS sss_cost,
                SUM(CASE WHEN p.hipdata_code IN ("SSS", "SSI") THEN o.sum_price ELSE 0 END) AS sss_price,
                
                COUNT(DISTINCT CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.an END) AS other_visit,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty ELSE 0 END) AS other_qty,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.qty * o.cost ELSE 0 END) AS other_cost,
                SUM(CASE WHEN p.hipdata_code NOT IN ("UCS", "OFC", "LGO", "SSS", "SSI") OR p.hipdata_code IS NULL THEN o.sum_price ELSE 0 END) AS other_price
            FROM opitemrece o
            LEFT JOIN pttype p ON p.pttype = o.pttype
            LEFT JOIN drugitems d ON d.icode = o.icode								
            WHERE o.rxdate BETWEEN ? AND ?
              AND o.icode IN (' . implode(',', array_map(fn($c) => '"' . $c . '"', $target_icodes)) . ')
              AND (o.an IS NOT NULL AND o.an <> "")
            GROUP BY o.icode
            ORDER BY total_price DESC
        ', [$start_date, $end_date]);

        // Monthly qty for chart
        $monthly_raw_opd = DB::connection('hosxp')->select('
            SELECT 
                d.name AS drug_name,
                YEAR(o.rxdate) as y,
                MONTH(o.rxdate) as m,
                SUM(o.qty) AS qty
            FROM opitemrece o
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND o.icode IN (' . implode(',', array_map(fn($c) => '"' . $c . '"', $target_icodes)) . ')
              AND (o.vn IS NOT NULL AND o.vn <> "")
            GROUP BY d.name, y, m
            ORDER BY d.name, y, m
        ', [$start_date, $end_date]);

        $monthly_raw_ipd = DB::connection('hosxp')->select('
            SELECT 
                d.name AS drug_name,
                YEAR(o.rxdate) as y,
                MONTH(o.rxdate) as m,
                SUM(o.qty) AS qty
            FROM opitemrece o
            LEFT JOIN drugitems d ON d.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
              AND o.icode IN (' . implode(',', array_map(fn($c) => '"' . $c . '"', $target_icodes)) . ')
              AND (o.an IS NOT NULL AND o.an <> "")
            GROUP BY d.name, y, m
            ORDER BY d.name, y, m
        ', [$start_date, $end_date]);

        // Generate all months in range for X-Axis
        $thai_months = [
            '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
            '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
            '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
        ];
        
        $start = new \DateTime($start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($end_date);
        $end->modify('last day of this month');
        
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end);
        
        $month_keys = [];
        $month_categories = [];
        foreach ($period as $dt) {
            $ym = $dt->format('Y-m');
            $month_keys[] = $ym;
            list($y, $m) = explode('-', $ym);
            $thai_year = ($y + 543) % 100;
            $month_categories[] = $thai_months[$m] . ' ' . $thai_year;
        }

        // Format chart helper (limit to top 10 drugs by total qty to keep chart clean)
        $formatChart = function($raw_data) use ($month_keys) {
            $series = [];
            $drugs_data = [];
            $drug_totals = [];
            
            // Group raw data by drug name
            foreach ($raw_data as $row) {
                $drug_name = $row->drug_name;
                $ym = sprintf('%04d-%02d', $row->y, $row->m);
                if (!isset($drugs_data[$drug_name])) {
                    $drugs_data[$drug_name] = array_fill_keys($month_keys, 0);
                    $drug_totals[$drug_name] = 0;
                }
                $drugs_data[$drug_name][$ym] = (float)$row->qty;
                $drug_totals[$drug_name] += (float)$row->qty;
            }
            
            // Sort by total quantity descending and slice top 10
            arsort($drug_totals);
            $top_drugs = array_slice(array_keys($drug_totals), 0, 10);
            
            foreach ($top_drugs as $drug_name) {
                if (isset($drugs_data[$drug_name])) {
                    $series[] = [
                        'name' => $drug_name,
                        'data' => array_values($drugs_data[$drug_name])
                    ];
                }
            }
            
            return $series;
        };

        $chart_series_opd = $formatChart($monthly_raw_opd);
        $chart_series_ipd = $formatChart($monthly_raw_ipd);

        return view('hosxp.phar.dmht', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'dmht_opd',
            'dmht_ipd',
            'month_categories',
            'chart_series_opd',
            'chart_series_ipd'
        ));
    }

    public function allergyPcu(Request $request)
    {
        $title = 'ข้อมูลการแพ้ยา แยก รพ.สต.';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Allergy query (Optimized explicit JOINs)
        $allergy_data = DB::connection('hosxp')->select('
            SELECT 
                p.cid,
                p.hn,
                CONCAT(p.pname, p.fname, " ", p.lname) AS ptname,
                o.report_date,
                p.drugallergy,
                GROUP_CONCAT(DISTINCT o.symptom) AS symptom,
                GROUP_CONCAT(DISTINCT o1.seiousness_name) AS seiousness_name,
                GROUP_CONCAT(DISTINCT o2.result_name) AS result_name,
                COUNT(o.agent) AS agent_count,
                CASE 
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370601") THEN "รพ.สต.หัวตะพาน" 
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370602" AND p.moopart IN ("4","5","6","10","11")) THEN "รพ.สต.โนนหนามแท่ง"   
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370602" AND p.moopart IN ("1","2","3","7","8","9","12")) THEN "รพ.สต.คำพระ"  
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370603") THEN "รพ.สต.เค็งใหญ่"  
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370604") THEN "รพ.สต.โคกเลาะ"   
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370605" AND p.moopart IN ("2","5","6","7","8","9")) THEN "รพ.สต.ขุมเหล็ก" 
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370605" AND p.moopart IN ("1","3","4","10","11","12")) THEN "รพ.สต.โพนเมืองน้อย" 
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370606" AND p.moopart IN ("1","3","7","10","11","12","13")) THEN "รพ.สต.สร้างถ่อน้อย" 
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370606" AND p.moopart IN ("2","4","5","6","8","9")) THEN "รพ.สต.นาคู" 
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370607" AND p.moopart IN ("3","6","7","8","9")) THEN "รพ.สต.หนองยอ"  
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370607" AND p.moopart IN ("1","2","4","5","10","11","12")) THEN "รพ.สต.จิกดู่"   
                    WHEN (CONCAT(p.chwpart, p.amppart, p.tmbpart) = "370608") THEN "PCU รัตนวารี" 
                    ELSE "นอกเขตอำเภอหัวตะพาน" 
                END AS pcu,
                o.agent
            FROM opd_allergy o
            INNER JOIN patient p ON p.hn = o.hn
            LEFT JOIN allergy_seriousness o1 ON o1.seriousness_id = o.seriousness_id
            LEFT JOIN allergy_result o2 ON o2.allergy_result_id = o.allergy_result_id
            WHERE o.report_date BETWEEN ? AND ?
            GROUP BY p.cid, p.hn, p.pname, p.fname, p.lname, o.report_date, p.drugallergy, o.agent, p.chwpart, p.amppart, p.tmbpart, p.moopart
            ORDER BY pcu, o.report_date DESC
        ', [$start_date, $end_date]);

        return view('hosxp.phar.allergy_pcu', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'allergy_data'
        ));
    }

    private function aggregateMonthly($data, $start_date, $end_date, $dateField = 'rxdate')
    {
        $thai_months = [
            '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
            '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
            '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
        ];

        // Generate all months in range
        $start = new \DateTime($start_date);
        $start->modify('first day of this month');
        $end = new \DateTime($end_date);
        $end->modify('last day of this month');
        
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end);
        
        $months = [];
        foreach ($period as $dt) {
            $months[$dt->format('Y-m')] = 0;
        }

        // Count
        foreach ($data as $row) {
            if (isset($row->$dateField)) {
                $m = date('Y-m', strtotime($row->$dateField));
                if (isset($months[$m])) {
                    $months[$m]++;
                }
            }
        }

        $categories = [];
        $values = [];
        foreach ($months as $ym => $count) {
            list($y, $m) = explode('-', $ym);
            $thai_year = ($y + 543) % 100;
            $categories[] = $thai_months[$m] . ' ' . $thai_year;
            $values[] = $count;
        }

        return [
            'categories' => $categories,
            'values' => $values
        ];
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
