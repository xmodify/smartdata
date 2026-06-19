<?php

namespace App\Http\Controllers\MophNotify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AuditEmrController extends Controller
{
    public function check(Request $request)
    {
        try {
            $budget_year_now = DB::table('budget_year')
                ->where('DATE_END', '>=', date('Y-m-d'))
                ->where('DATE_BEGIN', '<=', date('Y-m-d'))
                ->first();

            $start_date = $budget_year_now->DATE_BEGIN ?? (date('m') >= 10 ? date('Y-10-01') : (date('Y') - 1) . '-10-01');
            $end_date = $budget_year_now->DATE_END ?? (date('m') >= 10 ? (date('Y') + 1) . '-09-30' : date('Y-09-30'));
            $year_name = $budget_year_now->LEAVE_YEAR_NAME ?? (date('m') >= 10 ? (date('Y') + 544) : (date('Y') + 543));

            $chart = DB::connection('hosxp')->selectOne("
                SELECT
                    COUNT(DISTINCT CASE WHEN (idd.diag_text = '' OR idd.diag_text IS NULL) THEN i.an END) AS non_diagtext,
                    COUNT(DISTINCT CASE WHEN (idd.diag_text <> '' AND idd.diag_text IS NOT NULL) AND (idd.audit_ok <> 'Y' AND (idd.audit_diag_text = '' OR idd.audit_diag_text IS NULL)) THEN i.an END) AS wait_audit,
                    COUNT(DISTINCT CASE WHEN (idd.diag_text <> '' AND idd.diag_text IS NOT NULL) AND (id.icd10 = '' OR id.icd10 IS NULL) THEN i.an END) AS total_non_icd10,
                    COUNT(DISTINCT CASE WHEN (idd.diag_text <> '' AND idd.diag_text IS NOT NULL) AND (id.icd10 = '' OR id.icd10 IS NULL) AND (idd.audit_ok = 'Y' OR (idd.audit_diag_text <> '' AND idd.audit_diag_text IS NOT NULL)) THEN i.an END) AS non_icd10_audited,
                    COUNT(DISTINCT CASE WHEN (idd.diag_text <> '' AND idd.diag_text IS NOT NULL) AND (id.icd10 = '' OR id.icd10 IS NULL) AND (idd.audit_ok <> 'Y' AND (idd.audit_diag_text = '' OR idd.audit_diag_text IS NULL)) THEN i.an END) AS non_icd10_wait_audit
                FROM ipt i
                LEFT JOIN iptdiag id ON id.an = i.an AND id.diagtype = 1
                LEFT JOIN ipt_doctor_diag idd ON idd.an = i.an AND idd.diagtype = 1
                WHERE i.dchdate BETWEEN ? AND ?
                AND i.ward IN ('01','02','03','10')
            ", [$start_date, $end_date]);

            $message = "รายงาน EMR Audit\n"
                . "✅ ณ " . DateThai(now()->toDateTimeString()) . "\n"
                . "--------------------------------\n"
                . "รอแพทย์สรุป : " . ($chart->non_diagtext ?? 0) . " AN\n"
                . "รอ Audit : " . ($chart->wait_audit ?? 0) . " AN\n"
                . "รอบันทึกรหัสโรค: " . ($chart->total_non_icd10 ?? 0) . " AN\n"
                . "   - Audit แล้ว: " . ($chart->non_icd10_audited ?? 0) . " AN 🟢\n"
                . "   - รอ Audit: " . ($chart->non_icd10_wait_audit ?? 0) . " AN 🔴\n"
                . "--------------------------------\n"
                . route('ipd.wait_dchsummary') . "\n";

            // Send to MOPH Notify ID 1
            $client = DB::table('moph_notify')
                ->where('id', 5)
                ->where('active', 'Y')
                ->first(['id', 'name', 'client_id', 'secret']);

            $notifyResult = 'No active client configuration found';
            if ($client) {
                $endpoint = "https://morpromt2f.moph.go.th/api/notify/send";
                $payload = [
                    "messages" => [
                        [
                            "type" => "text",
                            "text" => "🏥 {$message}"
                        ]
                    ]
                ];

                try {
                    $response = Http::timeout(5)->withHeaders([
                        'Content-Type' => 'application/json',
                        'client-key'   => $client->client_id,
                        'secret-key'   => $client->secret
                    ])->post($endpoint, $payload);

                    if ($response->successful()) {
                        $notifyResult = '✅ ส่งการแจ้งเตือน MOPH Notify สำเร็จไปยัง ' . $client->name;
                    } else {
                        $notifyResult = '❌ ส่งการแจ้งเตือนล้มเหลว HTTP Code ' . $response->status();
                    }
                } catch (\Exception $ex) {
                    $notifyResult = '⚠️ ไม่สามารถเชื่อมต่อกับ MOPH Notify API ได้: ' . $ex->getMessage();
                }
            }

            return response()->json([
                'status' => 'success',
                'stats' => $chart,
                'notify_status' => $notifyResult
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
