<?php

namespace App\Http\Controllers\MophNotify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ReplicationController extends Controller
{
    public function check(Request $request)
    {
        $errors = [];
        $statusReport = [];
        
        $connections = [
            'hosxp_master' => 'Master',
            'hosxp'        => 'Slave 1',
            'hosxp_slave2' => 'Slave 2'
        ];

        // สำหรับเก็บข้อมูลที่ดึงมาตรวจสอบ
        $opdMaxVn = [];
        $ipdMaxAn = [];
        
        $opdCount2Days = [];
        $ipdCount2Days = [];
        $itemCount2Days = [];
        
        $isConnected = [];
        $lastVisitTime = [];

        // 1. ดึงสถานะและข้อมูลจากทั้ง 3 ฐานข้อมูล
        foreach ($connections as $conn => $label) {
            try {
                // ทดสอบการเชื่อมต่อ
                DB::connection($conn)->getPdo();
                $isConnected[$conn] = true;

                // A. ดึงค่าสูงสุด (Max Value)
                $opdMaxRes = DB::connection($conn)->selectOne("SELECT MAX(vn) as max_vn FROM ovst");
                $opdMaxVn[$conn] = $opdMaxRes ? ($opdMaxRes->max_vn ?? 0) : 0;

                $ipdMaxRes = DB::connection($conn)->selectOne("SELECT MAX(an) as max_an FROM ipt");
                $ipdMaxAn[$conn] = $ipdMaxRes ? ($ipdMaxRes->max_an ?? 0) : 0;

                // B. นับจำนวนเรคคอร์ด 2 วันล่าสุด (ความสมบูรณ์ของแถวข้อมูล)
                // ตาราง ovst (OPD)
                $opdCountRes = DB::connection($conn)->selectOne("
                    SELECT COUNT(*) as total FROM ovst 
                    WHERE vstdate >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                ");
                $opdCount2Days[$conn] = $opdCountRes ? $opdCountRes->total : 0;

                // ตาราง ipt (IPD)
                $ipdCountRes = DB::connection($conn)->selectOne("
                    SELECT COUNT(*) as total FROM ipt 
                    WHERE regdate >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                ");
                $ipdCount2Days[$conn] = $ipdCountRes ? $ipdCountRes->total : 0;

                // ตาราง opitemrece (รายการค่ารักษา/สั่งยา) - อัปเดตบ่อยมาก
                $itemCountRes = DB::connection($conn)->selectOne("
                    SELECT COUNT(*) as total FROM opitemrece 
                    WHERE vstdate >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                ");
                $itemCount2Days[$conn] = $itemCountRes ? $itemCountRes->total : 0;

                // C. ดึงวันเวลาล่าสุดของ Visit (ovst)
                $lastVisitRes = DB::connection($conn)->selectOne("
                    SELECT CONCAT(vstdate, ' ', vsttime) as last_time 
                    FROM ovst 
                    WHERE vn = (SELECT MAX(vn) FROM ovst)
                ");
                $lastVisitTime[$conn] = $lastVisitRes ? $lastVisitRes->last_time : 'N/A';

            } catch (\Exception $e) {
                $isConnected[$conn] = false;
                $opdMaxVn[$conn] = 'Offline';
                $ipdMaxAn[$conn] = 'Offline';
                $opdCount2Days[$conn] = 0;
                $ipdCount2Days[$conn] = 0;
                $itemCount2Days[$conn] = 0;
                $lastVisitTime[$conn] = 'Offline';
                
                $errors[] = "❌ [{$label}] เชื่อมต่อฐานข้อมูลไม่ได้: " . $e->getMessage();
            }
        }

        // 2. เปรียบเทียบข้อมูลระหว่าง Master กับ Slaves
        $slaves = [
            'hosxp'        => 'Slave 1',
            'hosxp_slave2' => 'Slave 2'
        ];

        if (!empty($isConnected['hosxp_master'])) {
            $masterVn = $opdMaxVn['hosxp_master'];
            $masterAn = $ipdMaxAn['hosxp_master'];
            
            $masterOpdCount = $opdCount2Days['hosxp_master'];
            $masterIpdCount = $ipdCount2Days['hosxp_master'];
            $masterItemCount = $itemCount2Days['hosxp_master'];

            foreach ($slaves as $conn => $label) {
                if (!empty($isConnected[$conn])) {
                    $slaveVn = $opdMaxVn[$conn];
                    $slaveAn = $ipdMaxAn[$conn];
                    
                    $slaveOpdCount = $opdCount2Days[$conn];
                    $slaveIpdCount = $ipdCount2Days[$conn];
                    $slaveItemCount = $itemCount2Days[$conn];

                    // A. ตรวจสอบค่าสูงสุด (VN/AN)
                    if ($masterVn !== $slaveVn) {
                        $statusReport[] = "⏳ [{$label}] ข้อมูล OPD ดีเลย์ (Master Max VN: {$masterVn} | {$label} Max VN: {$slaveVn})";
                    }
                    if ($masterAn !== $slaveAn) {
                        $statusReport[] = "⏳ [{$label}] ข้อมูล IPD ดีเลย์ (Master Max AN: {$masterAn} | {$label} Max AN: {$slaveAn})";
                    }

                    // B. ตรวจสอบจำนวนแถวข้อมูล 2 วันล่าสุด (ความสมบูรณ์ข้อมูล)
                    if ($masterOpdCount !== $slaveOpdCount) {
                        $statusReport[] = "⚠️ [{$label}] จำนวนแถวตาราง ovst ไม่เท่ากัน (Master Count: {$masterOpdCount} | {$label} Count: {$slaveOpdCount})";
                    }
                    if ($masterIpdCount !== $slaveIpdCount) {
                        $statusReport[] = "⚠️ [{$label}] จำนวนแถวตาราง ipt ไม่เท่ากัน (Master Count: {$masterIpdCount} | {$label} Count: {$slaveIpdCount})";
                    }
                    if ($masterItemCount !== $slaveItemCount) {
                        $statusReport[] = "⚠️ [{$label}] จำนวนแถวตาราง opitemrece ไม่เท่ากัน (Master Count: {$masterItemCount} | {$label} Count: {$slaveItemCount})";
                    }
                }
            }
        }

        // 3. ส่งการแจ้งเตือน MOPH Notify หากมีข้อผิดพลาด หรือถ้าระบบปกติแต่ครบรอบเวลา 4 ชั่วโมง (หรือทดสอบโดยส่ง ?force=1)
        $isPeriodicReport = ($request->has('force') || (date('i') === '00' && intval(date('H')) % 4 === 0));
        $notifyResult = 'No notification sent';
        
        if (!empty($errors) || !empty($statusReport) || $isPeriodicReport) {
            $message = "เช็ค Replication HOSxP\n";
            if (!empty($errors)) {
                $message .= implode("\n", $errors) . "\n";
            }
            if (!empty($statusReport)) {
                $message .= implode("\n", $statusReport) . "\n";
            }
            
            // หากปกติทั้งหมด
            if (empty($errors) && empty($statusReport)) {
                $message .= "✅ ปกติทุกฐานข้อมูล (100%)\n";
                $message .= "- Master (" . (isset($lastVisitTime['hosxp_master']) && $lastVisitTime['hosxp_master'] !== 'Offline' ? DateThai($lastVisitTime['hosxp_master']) : 'Offline') . ")\n";
                $message .= "- Slave1 (" . (isset($lastVisitTime['hosxp']) && $lastVisitTime['hosxp'] !== 'Offline' ? DateThai($lastVisitTime['hosxp']) : 'Offline') . ")\n";
                $message .= "- Slave2 (" . (isset($lastVisitTime['hosxp_slave2']) && $lastVisitTime['hosxp_slave2'] !== 'Offline' ? DateThai($lastVisitTime['hosxp_slave2']) : 'Offline') . ")\n";
            }
            
            $message .= "-----------------------\n";
            $message .= "เวลาเช็ค: " . DateThai(now()->toDateTimeString());

            try {
                $client = DB::table('moph_notify')
                    ->where('id', 1)
                    ->where('active', 'Y')
                    ->first(['id', 'name', 'client_id', 'secret']);

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

                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'client-key'   => $client->client_id,
                        'secret-key'   => $client->secret
                    ])->post($endpoint, $payload);

                    if ($response->successful()) {
                        $notifyResult = '✅ ส่งการแจ้งเตือน MOPH Notify สำเร็จไปยัง ' . $client->name;
                    } else {
                        $notifyResult = '❌ ส่งการแจ้งเตือนล้มเหลว HTTP Code ' . $response->status();
                    }
                } else {
                    $notifyResult = '⚠️ ไม่พบข้อมูลการตั้งค่า MOPH Notify (ID: 1 หรือ active != "Y")';
                }
            } catch (\Exception $notifyEx) {
                $notifyResult = '⚠️ เกิดข้อผิดพลาดขณะส่งแจ้งเตือน: ' . $notifyEx->getMessage();
            }
        }

        // 4. ส่งสถานะกลับแบบ JSON
        return response()->json([
            'checked_at'  => now()->toDateTimeString(),
            'connections' => [
                'master'  => [
                    'connected' => $isConnected['hosxp_master'] ?? false, 
                    'max_vn'    => $opdMaxVn['hosxp_master'] ?? 0,
                    'max_an'    => $ipdMaxAn['hosxp_master'] ?? 0,
                    'ovst_count_2days' => $opdCount2Days['hosxp_master'] ?? 0,
                    'ipt_count_2days'  => $ipdCount2Days['hosxp_master'] ?? 0,
                    'opitemrece_count_2days' => $itemCount2Days['hosxp_master'] ?? 0,
                ],
                'slave_1' => [
                    'connected' => $isConnected['hosxp'] ?? false, 
                    'max_vn'    => $opdMaxVn['hosxp'] ?? 0,
                    'max_an'    => $ipdMaxAn['hosxp'] ?? 0,
                    'ovst_count_2days' => $opdCount2Days['hosxp'] ?? 0,
                    'ipt_count_2days'  => $ipdCount2Days['hosxp'] ?? 0,
                    'opitemrece_count_2days' => $itemCount2Days['hosxp'] ?? 0,
                ],
                'slave_2' => [
                    'connected' => $isConnected['hosxp_slave2'] ?? false, 
                    'max_vn'    => $opdMaxVn['hosxp_slave2'] ?? 0,
                    'max_an'    => $ipdMaxAn['hosxp_slave2'] ?? 0,
                    'ovst_count_2days' => $opdCount2Days['hosxp_slave2'] ?? 0,
                    'ipt_count_2days'  => $ipdCount2Days['hosxp_slave2'] ?? 0,
                    'opitemrece_count_2days' => $itemCount2Days['hosxp_slave2'] ?? 0,
                ]
            ],
            'errors'        => $errors,
            'reports'       => $statusReport,
            'notify_status' => $notifyResult
        ]);
    }
}
