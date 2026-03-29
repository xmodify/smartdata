<?php

namespace App\Http\Controllers\MophNotify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ServiceController extends Controller
{
    public function service_night()
    {   
    //1. ดึงข้อมูลสรุปบริการ (เวรดึก)
        $service = DB::connection('hosxp')->selectOne("
            SELECT CURDATE() AS vstdate,
            COUNT(DISTINCT o.vn) AS total_visit,
            COUNT(DISTINCT CASE WHEN o.main_dep = '002' THEN o.vn END) AS opd,
            COUNT(DISTINCT CASE WHEN o.main_dep = '032' THEN o.vn END) AS ari,
            COUNT(DISTINCT CASE WHEN o.main_dep = '011' THEN o.vn END) AS ncd,
            COUNT(DISTINCT CASE WHEN o.main_dep = '033' THEN o.vn END) AS kidney_hos,
            COUNT(DISTINCT CASE WHEN o.main_dep = '024' THEN o.vn END) AS kidney_os,
            COUNT(DISTINCT er.vn) AS er,
            COUNT(DISTINCT ps.vn) AS physic,
            COUNT(DISTINCT hm.vn) AS health_med,
            COUNT(DISTINCT dt.vn) AS dental,
            COUNT(DISTINCT anc.vn) AS anc,
            COUNT(DISTINCT ipt1.an) AS admit,
            COUNT(DISTINCT ipt2.an) AS discharge,
            COUNT(DISTINCT ro.vn) AS referout,
            COUNT(DISTINCT ri.vn) AS referin
            FROM ovst o
            LEFT JOIN opdscreen_bp os ON os.vn = o.vn 
            LEFT JOIN er_regist er ON er.vn = o.vn
            LEFT JOIN physic_list ps ON ps.vn = o.vn
            LEFT JOIN health_med_service hm ON hm.vn = o.vn
            LEFT JOIN dtmain dt ON dt.vn = o.vn
            LEFT JOIN person_anc_service anc ON anc.vn = o.vn
            LEFT JOIN referout ro ON ro.vn = o.vn
            LEFT JOIN referin ri ON ri.vn = o.vn
            LEFT JOIN ipt ipt1 ON ipt1.regdate = CURDATE() AND ipt1.regtime BETWEEN '00:00:01' AND '07:59:59'
            LEFT JOIN ipt ipt2 ON ipt2.dchdate = CURDATE() AND ipt2.dchtime BETWEEN '00:00:01' AND '07:59:59'
            WHERE o.vstdate = CURDATE()
            AND os.screen_time BETWEEN '00:00:01' AND '07:59:59' ");

    // 1.1 ดึงตัวเลข Admit ปัจจุบัน (Current IPD Status) - แยกอิสระจาก OPD
        $ipd = DB::connection('hosxp')->selectOne("
            SELECT 
                ipd.ipd_all, ipd.ipd_normal, ipd.ipd_vip, ipd.ipd_icu, ipd.ipd_labor, ipd.homeward,
                bed.bed_qty,
                ROUND(IF(bed.bed_qty > 0, (ipd.ipd_all / bed.bed_qty) * 100, 0), 2) AS occ_ipd_all_rate,
                ROUND(IF(bed.bed_normal > 0, (ipd.ipd_normal / bed.bed_normal) * 100, 0), 2) AS occ_ipd_normal_rate,
                ROUND(IF(bed.bed_vip > 0, (ipd.ipd_vip / bed.bed_vip) * 100, 0), 2) AS occ_ipd_vip_rate,
                ROUND(IF(bed.bed_icu > 0, (ipd.ipd_icu / bed.bed_icu) * 100, 0), 2) AS occ_ipd_icu_rate
            FROM (
                SELECT 
                    COUNT(DISTINCT an) AS ipd_all,
                    SUM(ward = '01') AS ipd_normal,
                    SUM(ward = '02') AS ipd_labor,
                    SUM(ward = '03') AS ipd_vip,
                    SUM(ward = '10') AS ipd_icu,
                    SUM(ward = '06') AS homeward
                FROM ipt
                WHERE confirm_discharge = 'N'
            ) ipd
            JOIN (
                SELECT  
                    COUNT(DISTINCT b.bedno) AS bed_qty,
                    SUM(r.ward = '01') AS bed_normal,
                    SUM(r.ward = '03') AS bed_vip,
                    IFNULL(SUM(r.ward = '10'), 0) AS bed_icu
                FROM bedno b
                JOIN roomno r ON b.roomno = r.roomno
                WHERE b.export_code IS NOT NULL AND r.ward NOT IN ('06')
            ) bed ON 1=1 ");

    // 1.2 ดึงสถิติ Chart (รอแพทย์สรุป / รอลงรหัส) - รายปีงบประมาณปัจจุบัน
        $budget = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->first();
        $start_date = $budget->DATE_BEGIN ?? (date('m') >= 10 ? date('Y-10-01') : (date('Y') - 1) . '-10-01');
        $end_date = $budget->DATE_END ?? (date('m') >= 10 ? (date('Y') + 1) . '-09-30' : date('Y-09-30'));

        $chart = DB::connection('hosxp')->selectOne("
            SELECT
                SUM(CASE WHEN (a.diag_text_list IS NULL OR a.diag_text_list = '') THEN 1 ELSE 0 END) AS non_diagtext,
                SUM(CASE WHEN (a.diag_text_list IS NOT NULL AND a.diag_text_list <> '') AND (id.icd10 IS NULL OR id.icd10 = '') THEN 1 ELSE 0 END) AS non_icd10
            FROM ipt i
            LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
            LEFT JOIN an_stat a ON a.an = i.an
            WHERE i.dchdate BETWEEN ? AND ?
            AND i.ward IN ('01','02','03','10')
            AND (
                (a.diag_text_list IS NULL OR a.diag_text_list = '')
                OR
                ((a.diag_text_list IS NOT NULL AND a.diag_text_list <> '') AND (id.icd10 IS NULL OR id.icd10 = ''))
            )", [$start_date, $end_date]);

    //2. สร้างข้อความสรุป
        $message = "สรุปข้อมูลบริการ " .DateThai(date('Y-m-d')) ."\n"
            ."เวรดึก 🕒 00.00-08.00 น." ."\n\n"
            ."OP " . ($service->total_visit ?? 0) ." Visit" ."\n"
            ." - OPD " . ($service->opd ?? 0) ."\n"
            ." - ARI " . ($service->ari ?? 0) ."\n"
            ." - NCD " . ($service->ncd ?? 0) ."\n"
            ." - ER " . ($service->er ?? 0) ."\n"
            ." - ฟอกไต รพ. " . ($service->kidney_hos ?? 0) ."\n"
            ." - ฟอกไต เอกชน. " . ($service->kidney_os ?? 0) ."\n"            
            ." - กายภาพบำบัด " . ($service->physic ?? 0) ."\n"
            ." - แผนไทย " . ($service->health_med ?? 0) ."\n"
            ." - ทันตกรรม " . ($service->dental ?? 0) ."\n"
            ." - ฝากครรภ์ " . ($service->anc ?? 0) ."\n"
            ." - Admit " . ($service->admit ?? 0) ."\n"
            ." - Discharge " . ($service->discharge ?? 0) ."\n"
            ." - ReferOUT " . ($service->referout ?? 0) ."\n"
            ." - ReferIN " . ($service->referin ?? 0) ."\n\n"      
           
            . "Admit อยู่ " . $ipd->ipd_all ." | occ " . $ipd->occ_ipd_all_rate ." %" ."\n"
            . " - สามัญ " . $ipd->ipd_normal ." | occ " . $ipd->occ_ipd_normal_rate ." %" ."\n"
            . " - VIP " . $ipd->ipd_vip ." | occ " . $ipd->occ_ipd_vip_rate ." %" ."\n"
            . " - ICU " . $ipd->ipd_icu ." | occ " . $ipd->occ_ipd_icu_rate ." %" ."\n"
            . " - LR " . $ipd->ipd_labor ."\n"
            . " - Homeward " . $ipd->homeward ."\n"
            . "\n"
            . "Chart รอแพทย์สรุป: " . ($chart->non_diagtext ?? 0) . " AN" . "\n"
            . "Chart รอลงรหัสโรค: " . ($chart->non_icd10 ?? 0) . " AN" . "\n"
            . "https://huataphanhospital.go.th/rims/ipd_non_dchsummary" . "\n";

    //3. ดึงรายการ client จากตาราง moph_notify
        $clients = DB::table('moph_notify')
            ->whereIn('id', [1]) // 👈 ระบุ ID กลุ่มทึ่ต้องการส่งในเวรนี้
            ->where('active', 'Y')
            ->get(['id', 'name', 'client_id', 'secret']);
        $endpoint = "https://morpromt2f.moph.go.th/api/notify/send";
        $results = [];

    //4. ส่งข้อความให้ทุก client
        foreach ($clients as $client) {
            $payload = [
                "messages" => [
                    [
                        "type" => "text",
                        "text" => "🏥{$message}"
                    ]
                ]
            ];
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'client-key' => $client->client_id,
                    'secret-key' => $client->secret
                ])->post($endpoint, $payload);

                $results[] = [
                    'hospital' => $client->name,
                    'status' => $response->successful() ? '✅ success' : '❌ failed',
                    'http_code' => $response->status(),
                    'response' => $response->json()
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'hospital' => $client->name,
                    'status' => '⚠️ error',
                    'error' => $e->getMessage()
                ];
            }
            sleep(1); // ป้องกัน spam API
        }

    //5. ส่งผลลัพธ์กลับ
        return response()->json([
            'sent_at' => now()->toDateTimeString(),
            'total_clients' => $clients->count(),
            'results' => $results
        ]);
    }

// service_morning ##################################################################################################################
    public function service_morning()
        {   
    //1. ดึงข้อมูลสรุปบริการ (เวรเช้า)
        $service = DB::connection('hosxp')->selectOne("
            SELECT CURDATE() AS vstdate,
            COUNT(DISTINCT o.vn) AS total_visit,
            COUNT(DISTINCT CASE WHEN o.main_dep = '002' THEN o.vn END) AS opd,
            COUNT(DISTINCT CASE WHEN o.main_dep = '032' THEN o.vn END) AS ari,
            COUNT(DISTINCT CASE WHEN o.main_dep = '011' THEN o.vn END) AS ncd,
            COUNT(DISTINCT CASE WHEN o.main_dep = '033' THEN o.vn END) AS kidney_hos,
            COUNT(DISTINCT CASE WHEN o.main_dep = '024' THEN o.vn END) AS kidney_os,
            COUNT(DISTINCT er.vn) AS er,
            COUNT(DISTINCT ps.vn) AS physic,
            COUNT(DISTINCT hm.vn) AS health_med,
            COUNT(DISTINCT dt.vn) AS dental,
            COUNT(DISTINCT anc.vn) AS anc,
            COUNT(DISTINCT ipt1.an) AS admit,
            COUNT(DISTINCT ipt2.an) AS discharge,
            COUNT(DISTINCT ro.vn) AS referout,
            COUNT(DISTINCT ri.vn) AS referin
            FROM ovst o
            LEFT JOIN opdscreen_bp os ON os.vn = o.vn 
            LEFT JOIN er_regist er ON er.vn = o.vn
            LEFT JOIN physic_list ps ON ps.vn = o.vn
            LEFT JOIN health_med_service hm ON hm.vn = o.vn
            LEFT JOIN dtmain dt ON dt.vn = o.vn
            LEFT JOIN person_anc_service anc ON anc.vn = o.vn
            LEFT JOIN referout ro ON ro.vn = o.vn
            LEFT JOIN referin ri ON ri.vn = o.vn
            LEFT JOIN ipt ipt1 ON ipt1.regdate = CURDATE() AND ipt1.regtime BETWEEN '08:00:01' AND '15:59:59'
            LEFT JOIN ipt ipt2 ON ipt2.dchdate = CURDATE() AND ipt2.dchtime BETWEEN '08:00:01' AND '15:59:59'
            WHERE o.vstdate = CURDATE()
            AND os.screen_time BETWEEN '08:00:01' AND '15:59:59' ");

    // 1.1 ดึงตัวเลข Admit ปัจจุบัน (Current IPD Status) - แยกอิสระจาก OPD
        $ipd = DB::connection('hosxp')->selectOne("
            SELECT 
                ipd.ipd_all, ipd.ipd_normal, ipd.ipd_vip, ipd.ipd_icu, ipd.ipd_labor, ipd.homeward,
                bed.bed_qty,
                ROUND(IF(bed.bed_qty > 0, (ipd.ipd_all / bed.bed_qty) * 100, 0), 2) AS occ_ipd_all_rate,
                ROUND(IF(bed.bed_normal > 0, (ipd.ipd_normal / bed.bed_normal) * 100, 0), 2) AS occ_ipd_normal_rate,
                ROUND(IF(bed.bed_vip > 0, (ipd.ipd_vip / bed.bed_vip) * 100, 0), 2) AS occ_ipd_vip_rate,
                ROUND(IF(bed.bed_icu > 0, (ipd.ipd_icu / bed.bed_icu) * 100, 0), 2) AS occ_ipd_icu_rate
            FROM (
                SELECT 
                    COUNT(DISTINCT an) AS ipd_all,
                    SUM(ward = '01') AS ipd_normal,
                    SUM(ward = '02') AS ipd_labor,
                    SUM(ward = '03') AS ipd_vip,
                    SUM(ward = '10') AS ipd_icu,
                    SUM(ward = '06') AS homeward
                FROM ipt
                WHERE confirm_discharge = 'N'
            ) ipd
            JOIN (
                SELECT  
                    COUNT(DISTINCT b.bedno) AS bed_qty,
                    SUM(r.ward = '01') AS bed_normal,
                    SUM(r.ward = '03') AS bed_vip,
                    IFNULL(SUM(r.ward = '10'), 0) AS bed_icu
                FROM bedno b
                JOIN roomno r ON b.roomno = r.roomno
                WHERE b.export_code IS NOT NULL AND r.ward NOT IN ('06')
            ) bed ON 1=1 ");

    // 1.2 ดึงสถิติ Chart (รอแพทย์สรุป / รอลงรหัส) - รายปีงบประมาณปัจจุบัน
        $budget = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->first();
        $start_date = $budget->DATE_BEGIN ?? (date('m') >= 10 ? date('Y-10-01') : (date('Y') - 1) . '-10-01');
        $end_date = $budget->DATE_END ?? (date('m') >= 10 ? (date('Y') + 1) . '-09-30' : date('Y-09-30'));

        $chart = DB::connection('hosxp')->selectOne("
            SELECT
                SUM(CASE WHEN (a.diag_text_list IS NULL OR a.diag_text_list = '') THEN 1 ELSE 0 END) AS non_diagtext,
                SUM(CASE WHEN (a.diag_text_list IS NOT NULL AND a.diag_text_list <> '') AND (id.icd10 IS NULL OR id.icd10 = '') THEN 1 ELSE 0 END) AS non_icd10
            FROM ipt i
            LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
            LEFT JOIN an_stat a ON a.an = i.an
            WHERE i.dchdate BETWEEN ? AND ?
            AND i.ward IN ('01','02','03','10')
            AND (
                (a.diag_text_list IS NULL OR a.diag_text_list = '')
                OR
                ((a.diag_text_list IS NOT NULL AND a.diag_text_list <> '') AND (id.icd10 IS NULL OR id.icd10 = ''))
            )", [$start_date, $end_date]);

    //2. สร้างข้อความสรุป
        $message = "สรุปข้อมูลบริการ " .DateThai(date('Y-m-d')) ."\n"
            ."เวรเช้า 🕒 08.00-16.00 น." ."\n"
             ."OP " . ($service->total_visit ?? 0) ." Visit" ."\n"
            ." - OPD " . ($service->opd ?? 0)  ."\n"
            ." - ARI " . ($service->ari ?? 0) ."\n"
            ." - NCD " . ($service->ncd ?? 0) ."\n"
            ." - ER " . ($service->er ?? 0) ."\n"
            ." - ฟอกไต รพ. " . ($service->kidney_hos ?? 0) ."\n"
            ." - ฟอกไต เอกชน. " . ($service->kidney_os ?? 0) ."\n"            
            ." - กายภาพบำบัด " . ($service->physic ?? 0) ."\n"
            ." - แผนไทย " . ($service->health_med ?? 0) ."\n"
            ." - ทันตกรรม " . ($service->dental ?? 0) ."\n"
            ." - ฝากครรภ์ " . ($service->anc ?? 0) ."\n"
            ." - Admit " . ($service->admit ?? 0) ."\n"
            ." - Discharge " . ($service->discharge ?? 0) ."\n"
            ." - ReferOUT " . ($service->referout ?? 0) ."\n"
            ." - ReferIN " . ($service->referin ?? 0) ."\n\n"      
           
            . "Admit อยู่ " . $ipd->ipd_all ." | occ " . $ipd->occ_ipd_all_rate ." %" ."\n"
            . " - สามัญ " . $ipd->ipd_normal ." | occ " . $ipd->occ_ipd_normal_rate ." %" ."\n"
            . " - VIP " . $ipd->ipd_vip ." | occ " . $ipd->occ_ipd_vip_rate ." %" ."\n"
            . " - ICU " . $ipd->ipd_icu ." | occ " . $ipd->occ_ipd_icu_rate ." %" ."\n"
            . " - LR " . $ipd->ipd_labor ."\n"
            . " - Homeward " . $ipd->homeward ."\n"
            . "\n"
            . "Chart รอแพทย์สรุป: " . ($chart->non_diagtext ?? 0) . " AN" . "\n"
            . "Chart รอลงรหัสโรค: " . ($chart->non_icd10 ?? 0) . " AN" . "\n"
            . "https://huataphanhospital.go.th/rims/ipd_non_dchsummary" . "\n";

    //3. ดึงรายการ client จากตาราง moph_notify
        $clients = DB::table('moph_notify')
            ->whereIn('id', [1]) // 👈 ระบุ ID กลุ่มทึ่ต้องการส่งในเวรนี้
            ->where('active', 'Y')
            ->get(['id', 'name', 'client_id', 'secret']);
        $endpoint = "https://morpromt2f.moph.go.th/api/notify/send";
        $results = [];

    //4. ส่งข้อความให้ทุก client
        foreach ($clients as $client) {
            $payload = [
                "messages" => [
                    [
                        "type" => "text",
                        "text" => "🏥{$message}"
                    ]
                ]
            ];
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'client-key' => $client->client_id,
                    'secret-key' => $client->secret
                ])->post($endpoint, $payload);

                $results[] = [
                    'hospital' => $client->name,
                    'status' => $response->successful() ? '✅ success' : '❌ failed',
                    'http_code' => $response->status(),
                    'response' => $response->json()
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'hospital' => $client->name,
                    'status' => '⚠️ error',
                    'error' => $e->getMessage()
                ];
            }
            sleep(1); // ป้องกัน spam API
        }

    //5. ส่งผลลัพธ์กลับ
        return response()->json([
            'sent_at' => now()->toDateTimeString(),
            'total_clients' => $clients->count(),
            'results' => $results
        ]);
    }
// service_afternoon ##################################################################################################################
    public function service_afternoon()
        {   
    //1. ดึงข้อมูลสรุปบริการ (เวรบ่าย)
        $service = DB::connection('hosxp')->selectOne("
            SELECT CURDATE() - INTERVAL 1 DAY AS vstdate,
            COUNT(DISTINCT o.vn) AS total_visit,
            COUNT(DISTINCT CASE WHEN o.main_dep = '002' THEN o.vn END) AS opd,
            COUNT(DISTINCT CASE WHEN o.main_dep = '032' THEN o.vn END) AS ari,
            COUNT(DISTINCT CASE WHEN o.main_dep = '011' THEN o.vn END) AS ncd,
            COUNT(DISTINCT CASE WHEN o.main_dep = '033' THEN o.vn END) AS kidney_hos,
            COUNT(DISTINCT CASE WHEN o.main_dep = '024' THEN o.vn END) AS kidney_os,
            COUNT(DISTINCT er.vn) AS er,
            COUNT(DISTINCT ps.vn) AS physic,
            COUNT(DISTINCT hm.vn) AS health_med,
            COUNT(DISTINCT dt.vn) AS dental,
            COUNT(DISTINCT anc.vn) AS anc,
            COUNT(DISTINCT ipt1.an) AS admit,
            COUNT(DISTINCT ipt2.an) AS discharge,
            COUNT(DISTINCT ro.vn) AS referout,
            COUNT(DISTINCT ri.vn) AS referin
            FROM ovst o
            LEFT JOIN opdscreen_bp os ON os.vn = o.vn 
            LEFT JOIN er_regist er ON er.vn = o.vn
            LEFT JOIN physic_list ps ON ps.vn = o.vn
            LEFT JOIN health_med_service hm ON hm.vn = o.vn
            LEFT JOIN dtmain dt ON dt.vn = o.vn
            LEFT JOIN person_anc_service anc ON anc.vn = o.vn
            LEFT JOIN referout ro ON ro.vn = o.vn
            LEFT JOIN referin ri ON ri.vn = o.vn
            LEFT JOIN ipt ipt1 ON ipt1.regdate = CURDATE() - INTERVAL 1 DAY AND ipt1.regtime BETWEEN '16:00:01' AND '23:59:59'
            LEFT JOIN ipt ipt2 ON ipt2.dchdate = CURDATE() - INTERVAL 1 DAY AND ipt2.dchtime BETWEEN '16:00:01' AND '23:59:59'
            WHERE o.vstdate = CURDATE() - INTERVAL 1 DAY
            AND os.screen_time BETWEEN '16:00:01' AND '23:59:59' ");    

    // 1.1 ดึงตัวเลข Admit ปัจจุบัน (Current IPD Status) - แยกอิสระจาก OPD
        $ipd = DB::connection('hosxp')->selectOne("
            SELECT 
                ipd.ipd_all, ipd.ipd_normal, ipd.ipd_vip, ipd.ipd_icu, ipd.ipd_labor, ipd.homeward,
                bed.bed_qty,
                ROUND(IF(bed.bed_qty > 0, (ipd.ipd_all / bed.bed_qty) * 100, 0), 2) AS occ_ipd_all_rate,
                ROUND(IF(bed.bed_normal > 0, (ipd.ipd_normal / bed.bed_normal) * 100, 0), 2) AS occ_ipd_normal_rate,
                ROUND(IF(bed.bed_vip > 0, (ipd.ipd_vip / bed.bed_vip) * 100, 0), 2) AS occ_ipd_vip_rate,
                ROUND(IF(bed.bed_icu > 0, (ipd.ipd_icu / bed.bed_icu) * 100, 0), 2) AS occ_ipd_icu_rate
            FROM (
                SELECT 
                    COUNT(DISTINCT an) AS ipd_all,
                    SUM(ward = '01') AS ipd_normal,
                    SUM(ward = '02') AS ipd_labor,
                    SUM(ward = '03') AS ipd_vip,
                    SUM(ward = '10') AS ipd_icu,
                    SUM(ward = '06') AS homeward
                FROM ipt
                WHERE confirm_discharge = 'N'
            ) ipd
            JOIN (
                SELECT  
                    COUNT(DISTINCT b.bedno) AS bed_qty,
                    SUM(r.ward = '01') AS bed_normal,
                    SUM(r.ward = '03') AS bed_vip,
                    IFNULL(SUM(r.ward = '10'), 0) AS bed_icu
                FROM bedno b
                JOIN roomno r ON b.roomno = r.roomno
                WHERE b.export_code IS NOT NULL AND r.ward NOT IN ('06')
            ) bed ON 1=1 ");

    // 1.2 ดึงสถิติ Chart (รอแพทย์สรุป / รอลงรหัส) - รายปีงบประมาณปัจจุบัน
        $budget = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->first();
        $start_date = $budget->DATE_BEGIN ?? (date('m') >= 10 ? date('Y-10-01') : (date('Y') - 1) . '-10-01');
        $end_date = $budget->DATE_END ?? (date('m') >= 10 ? (date('Y') + 1) . '-09-30' : date('Y-09-30'));

        $chart = DB::connection('hosxp')->selectOne("
            SELECT
                SUM(CASE WHEN (a.diag_text_list IS NULL OR a.diag_text_list = '') THEN 1 ELSE 0 END) AS non_diagtext,
                SUM(CASE WHEN (a.diag_text_list IS NOT NULL AND a.diag_text_list <> '') AND (id.icd10 IS NULL OR id.icd10 = '') THEN 1 ELSE 0 END) AS non_icd10
            FROM ipt i
            LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
            LEFT JOIN an_stat a ON a.an = i.an
            WHERE i.dchdate BETWEEN ? AND ?
            AND i.ward IN ('01','02','03','10')
            AND (
                (a.diag_text_list IS NULL OR a.diag_text_list = '')
                OR
                ((a.diag_text_list IS NOT NULL AND a.diag_text_list <> '') AND (id.icd10 IS NULL OR id.icd10 = ''))
            )", [$start_date, $end_date]);

    //2. สร้างข้อความสรุป
        $message = "สรุปข้อมูลบริการ " .DateThai(date("Y-m-d", strtotime("-1 day"))) ."\n"
            ."เวรบ่าย 🕒 16.00-24.00 น." ."\n"
             ."OP " . ($service->total_visit ?? 0) ." Visit" ."\n"
            ." - OPD " . ($service->opd ?? 0)  ."\n"
            ." - ARI " . ($service->ari ?? 0) ."\n"
            ." - NCD " . ($service->ncd ?? 0) ."\n"
            ." - ER " . ($service->er ?? 0) ."\n"
            ." - ฟอกไต รพ. " . ($service->kidney_hos ?? 0) ."\n"
            ." - ฟอกไต เอกชน. " . ($service->kidney_os ?? 0) ."\n"            
            ." - กายภาพบำบัด " . ($service->physic ?? 0) ."\n"
            ." - แผนไทย " . ($service->health_med ?? 0) ."\n"
            ." - ทันตกรรม " . ($service->dental ?? 0) ."\n"
            ." - ฝากครรภ์ " . ($service->anc ?? 0) ."\n"
            ." - Admit " . ($service->admit ?? 0) ."\n"
            ." - Discharge " . ($service->discharge ?? 0) ."\n"
            ." - ReferOUT " . ($service->referout ?? 0) ."\n"
            ." - ReferIN " . ($service->referin ?? 0) ."\n\n"      
           
            . "Admit อยู่ " . $ipd->ipd_all ." | occ " . $ipd->occ_ipd_all_rate ." %" ."\n"
            . " - สามัญ " . $ipd->ipd_normal ." | occ " . $ipd->occ_ipd_normal_rate ." %" ."\n"
            . " - VIP " . $ipd->ipd_vip ." | occ " . $ipd->occ_ipd_vip_rate ." %" ."\n"
            . " - ICU " . $ipd->ipd_icu ." | occ " . $ipd->occ_ipd_icu_rate ." %" ."\n"
            . " - LR " . $ipd->ipd_labor ."\n"
            . " - Homeward " . $ipd->homeward ."\n"
            . "\n"
            . "Chart รอแพทย์สรุป: " . ($chart->non_diagtext ?? 0) . " AN" . "\n"
            . "Chart รอลงรหัสโรค: " . ($chart->non_icd10 ?? 0) . " AN" . "\n"
            . "https://huataphanhospital.go.th/rims/ipd_non_dchsummary" . "\n";

    //3. ดึงรายการ client จากตาราง moph_notify
        $clients = DB::table('moph_notify')
            ->whereIn('id', [1]) // 👈 ระบุ ID กลุ่มทึ่ต้องการส่งในเวรนี้
            ->where('active', 'Y')
            ->get(['id', 'name', 'client_id', 'secret']);
        $endpoint = "https://morpromt2f.moph.go.th/api/notify/send";
        $results = [];

    //4. ส่งข้อความให้ทุก client
        foreach ($clients as $client) {
            $payload = [
                "messages" => [
                    [
                        "type" => "text",
                        "text" => "🏥{$message}"
                    ]
                ]
            ];
            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'client-key' => $client->client_id,
                    'secret-key' => $client->secret
                ])->post($endpoint, $payload);

                $results[] = [
                    'hospital' => $client->name,
                    'status' => $response->successful() ? '✅ success' : '❌ failed',
                    'http_code' => $response->status(),
                    'response' => $response->json()
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'hospital' => $client->name,
                    'status' => '⚠️ error',
                    'error' => $e->getMessage()
                ];
            }
            sleep(1); // ป้องกัน spam API
        }

    //5. ส่งผลลัพธ์กลับ
        return response()->json([
            'sent_at' => now()->toDateTimeString(),
            'total_clients' => $clients->count(),
            'results' => $results
        ]);
    }

}


