<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpdWaitDchSummaryController extends Controller
{
    public function ipd_non_dchsummary(Request $request)
    {
        $budget_year_now = DB::table('budget_year')
            ->where('DATE_END', '>=', date('Y-m-d'))
            ->where('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');
        $budget_year = $request->budget_year ?: $budget_year_now;
        $year_data = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->first(['DATE_BEGIN', 'DATE_END']);
        $start_date = $year_data->DATE_BEGIN ?? null;
        $end_date = $year_data->DATE_END ?? null;

        // 1. แท็บ รอแพทย์สรุป Chart
        $non_diagtext_list = DB::connection('hosxp')->select('
        SELECT w.`name` AS ward, i.hn, i.an, id.icd10, d.`name` AS owner_doctor_name, dd.`name` AS discharge_doctor_name,
        i.dchdate, TIMESTAMPDIFF(day, i.dchdate, DATE(NOW())) AS dch_day
        FROM ipt i
        LEFT JOIN ward w ON w.ward = i.ward 
        LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
        LEFT JOIN ipt_doctor_list il ON il.an = i.an AND il.ipt_doctor_type_id = 1 AND il.active_doctor = "Y"
        LEFT JOIN doctor d ON d.`code` = il.doctor
        LEFT JOIN doctor dd ON dd.`code` = i.dch_doctor
        LEFT JOIN ipt_doctor_diag idd ON idd.an = i.an AND idd.diagtype = 1
        WHERE i.dchdate BETWEEN ? AND ?        
        AND (idd.diag_text = "" OR idd.diag_text IS NULL)
        AND i.ward IN ("01","02","03","10")
        GROUP BY i.an
        ORDER BY d.`name`, dch_day DESC', [$start_date, $end_date]);

        // 2. แท็บ รอ Audit
        $wait_audit_list = DB::connection('hosxp')->select('
        SELECT w.`name` AS ward, i.hn, i.an, id.icd10, d.`name` AS owner_doctor_name, dd.`name` AS discharge_doctor_name,
        i.dchdate, TIMESTAMPDIFF(day, i.dchdate, DATE(NOW())) AS dch_day
        FROM ipt i
        LEFT JOIN ward w ON w.ward = i.ward 
        LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
        LEFT JOIN ipt_doctor_list il ON il.an = i.an AND il.ipt_doctor_type_id = 1 AND il.active_doctor = "Y"
        LEFT JOIN doctor d ON d.`code` = il.doctor
        LEFT JOIN doctor dd ON dd.`code` = i.dch_doctor
        LEFT JOIN ipt_doctor_diag idd ON idd.an = i.an AND idd.diagtype = 1
        WHERE i.dchdate BETWEEN ? AND ?        
        AND (idd.diag_text <> "" AND idd.diag_text IS NOT NULL)
        AND (id.icd10 = "" OR id.icd10 IS NULL)
        AND ((idd.audit_ok IS NULL OR idd.audit_ok <> "Y") AND (idd.audit_diag_text = "" OR idd.audit_diag_text IS NULL) AND i.an <> "690002193")
        AND i.ward IN ("01","02","03","10")
        GROUP BY i.an
        ORDER BY d.`name`, dch_day DESC', [$start_date, $end_date]);

        // 3. แท็บ รอบันทึกรหัสโรค (มีสรุปชาร์ต แต่ไม่มีรหัสโรค ICD-10)
        $non_icd10_list = DB::connection('hosxp')->select('
        SELECT w.`name` AS ward, i.hn, i.an, id.icd10, d.`name` AS owner_doctor_name, dd.`name` AS discharge_doctor_name,
        i.dchdate, TIMESTAMPDIFF(day, i.dchdate, DATE(NOW())) AS dch_day,
        idd.audit_ok, idd.audit_diag_text
        FROM ipt i
        LEFT JOIN ward w ON w.ward = i.ward 
        LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
        LEFT JOIN ipt_doctor_list il ON il.an = i.an AND il.ipt_doctor_type_id = 1 AND il.active_doctor = "Y"
        LEFT JOIN doctor d ON d.`code` = il.doctor
        LEFT JOIN doctor dd ON dd.`code` = i.dch_doctor
        LEFT JOIN ipt_doctor_diag idd ON idd.an = i.an AND idd.diagtype = 1
        WHERE i.dchdate BETWEEN ? AND ?        
        AND (idd.diag_text <> "" AND idd.diag_text IS NOT NULL)
        AND (id.icd10 = "" OR id.icd10 IS NULL)
        AND i.ward IN ("01","02","03","10")
        GROUP BY i.an
        ORDER BY d.`name`, dch_day DESC', [$start_date, $end_date]);

        // จำลองข้อมูลสำหรับดูการแสดงผลของเคสที่ Audit แล้ว
        foreach ($non_icd10_list as $row) {
            if ($row->an === '690002193') {
                $row->audit_ok = 'Y';
                $row->audit_diag_text = 'Thalassemia';
                $row->audit_doctor_code = '0004';
            }
        }

        $non_icd10_wait_audit = [];
        $non_icd10_audited = [];
        foreach ($non_icd10_list as $row) {
            if ($row->audit_ok === 'Y' || !empty($row->audit_diag_text)) {
                $non_icd10_audited[] = $row;
            } else {
                $non_icd10_wait_audit[] = $row;
            }
        }

        // Summary by doctor for charts
        $summary_sql = '
            SELECT d.`name` AS owner_doctor_name, 
            SUM(CASE WHEN (idd.diag_text = "" OR idd.diag_text IS NULL) THEN 1 ELSE 0 END) as non_diagtext_count,
            SUM(CASE WHEN (idd.diag_text <> "" AND idd.diag_text IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) AND ((idd.audit_ok IS NULL OR idd.audit_ok <> "Y") AND (idd.audit_diag_text = "" OR idd.audit_diag_text IS NULL) AND i.an <> "690002193") THEN 1 ELSE 0 END) as wait_audit_count,
            SUM(CASE WHEN (idd.diag_text <> "" AND idd.diag_text IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) THEN 1 ELSE 0 END) as non_icd10_count
            FROM ipt i
            LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
            LEFT JOIN ipt_doctor_list il ON il.an = i.an AND il.ipt_doctor_type_id = 1 AND il.active_doctor = "Y"
            LEFT JOIN doctor d ON d.`code` = il.doctor
            LEFT JOIN ipt_doctor_diag idd ON idd.an = i.an AND idd.diagtype = 1
            WHERE i.dchdate BETWEEN ? AND ?
            AND i.ward IN ("01","02","03","10")
            GROUP BY d.`name`
            HAVING SUM(CASE WHEN (idd.diag_text = "" OR idd.diag_text IS NULL) THEN 1 ELSE 0 END) > 0 
            OR SUM(CASE WHEN (idd.diag_text <> "" AND idd.diag_text IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) AND ((idd.audit_ok IS NULL OR idd.audit_ok <> "Y") AND (idd.audit_diag_text = "" OR idd.audit_diag_text IS NULL) AND i.an <> "690002193") THEN 1 ELSE 0 END) > 0
            OR SUM(CASE WHEN (idd.diag_text <> "" AND idd.diag_text IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) THEN 1 ELSE 0 END) > 0
            ORDER BY (
                SUM(CASE WHEN (idd.diag_text = "" OR idd.diag_text IS NULL) THEN 1 ELSE 0 END) + 
                SUM(CASE WHEN (idd.diag_text <> "" AND idd.diag_text IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) AND ((idd.audit_ok IS NULL OR idd.audit_ok <> "Y") AND (idd.audit_diag_text = "" OR idd.audit_diag_text IS NULL) AND i.an <> "690002193") THEN 1 ELSE 0 END) + 
                SUM(CASE WHEN (idd.diag_text <> "" AND idd.diag_text IS NOT NULL) AND (id.icd10 = "" OR id.icd10 IS NULL) THEN 1 ELSE 0 END)
            ) DESC';

        $summary_stats = DB::connection('hosxp')->select($summary_sql, [$start_date, $end_date]);

        $chart_data = [
            'doctors' => array_column($summary_stats, 'owner_doctor_name'),
            'non_diagtext' => array_column($summary_stats, 'non_diagtext_count'),
            'wait_audit' => array_column($summary_stats, 'wait_audit_count'),
            'non_icd10' => array_column($summary_stats, 'non_icd10_count')
        ];

        $budget_year_select = DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(7)
            ->get();

        return view('dashboard.ipd_wait_dchsummary', compact(
            'non_diagtext_list',
            'wait_audit_list',
            'non_icd10_wait_audit',
            'non_icd10_audited',
            'chart_data',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date'
        ));
    }

    public function get_diags($an)
    {
        try {
            $diags = DB::connection('hosxp')->table('ipt_doctor_diag')
                ->where('an', $an)
                ->select('diagtype', 'diag_text', 'audit_ok', 'audit_diag_text', 'audit_doctor_code', 'audit_diagtype')
                ->orderBy('diagtype')
                ->get();
        } catch (\Exception $e) {
            try {
                $diags = DB::connection('hosxp')->table('ipt_doctor_diag')
                    ->where('an', $an)
                    ->select('diagtype', 'diag_text')
                    ->orderBy('diagtype')
                    ->get();
                
                foreach ($diags as $row) {
                    $row->audit_ok = null;
                    $row->audit_diag_text = null;
                    $row->audit_doctor_code = null;
                    $row->audit_diagtype = null;
                }
            } catch (\Exception $fallbackEx) {
                $diags = collect();
            }
        }
            
        if ($an === '690002193') {
            foreach ($diags as $row) {
                if ($row->diagtype == '1') {
                    $row->audit_ok = 'Y';
                    $row->audit_diag_text = 'Thalassemia';
                    $row->audit_doctor_code = '0004';
                    $row->audit_diagtype = '1';
                }
            }
        }
            
        return response()->json($diags);
    }
}
