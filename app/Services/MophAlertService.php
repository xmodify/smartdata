<?php
 
namespace App\Services;
 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
 
class MophAlertService
 {
     /**
     * ส่งแจ้งเตือนแบบข้อความทั่วไป (Free Form Alert) ไปยังแอปหมอพร้อมและ Line OA รายบุคคล
     *
     * @param array|string $cids เลขบัตรประชาชนผู้รับ (สามารถส่งเป็น String ตัวเดียว หรือ Array ของ CIDs ได้)
     * @param string $title หัวข้อการแจ้งเตือน (ส่งเข้า Application หมอพร้อม)
     * @param string $messageText ข้อความที่จะแสดงในรายการกล่องข้อความของแอปหมอพร้อม (ควรใช้ UTF-8 ธรรมดา ไม่ใช้ 4-byte bold)
     * @param string $messageHtml ข้อความรูปแบบ HTML แสดงภายในกล่องข้อความของแอปหมอพร้อม
     * @param int|null $alertId ID ของการตั้งค่าระบบแจ้งเตือนที่จะใช้ (หากไม่ระบุจะดึงรายการที่ active อยู่)
     * @param string|null $lineMessage ข้อความสำหรับส่งเข้า Line OA (หากไม่ระบุจะใช้ $messageText สามารถใช้ Unicode Bold ได้)
     * @return array|bool ผลลัพธ์จากการยิง API หรือ false หากมีข้อผิดพลาด
     */
    public static function sendFreeForm($cids, string $title, string $messageText, string $messageHtml, ?int $alertId = null, ?string $lineMessage = null)
    {
        try {
            $query = DB::table('moph_alert')->where('active', 'Y');
            if ($alertId !== null) {
                $config = (clone $query)->where('id', $alertId)->first(['id', 'client_id', 'secret']);
                if (!$config) {
                    $config = $query->first(['id', 'client_id', 'secret']);
                }
            } else {
                $config = $query->first(['id', 'client_id', 'secret']);
            }

            if (!$config || empty($config->client_id) || empty($config->secret)) {
                Log::warning("MOPH Alert: Active configuration not found or credentials empty (Requested ID: " . ($alertId ?? 'any') . ")");
                return false;
            }

            $rawCids = is_array($cids) ? $cids : [$cids];
            $cidsArray = array_values(array_filter(array_map(function ($cid) {
                return trim((string)$cid);
            }, $rawCids)));

            if (empty($cidsArray)) {
                Log::warning("MOPH Alert: No valid CID provided to send alert.");
                return false;
            }

            $lineText = !empty($lineMessage) ? $lineMessage : $messageText;

            $payload = [
                'cid' => $cidsArray,
                'messages' => [
                    [
                        'text' => $lineText,
                        'type' => 'text'
                    ]
                ],
                'message_title' => $title,
                'message_html'  => $messageHtml,
                'message_text'  => $messageText,
                'message_type'  => 'HPT'
            ];

            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'client-key'   => $config->client_id,
                    'secret-key'   => $config->secret,
                ])
                ->post('https://morpromt2c.moph.go.th/alert/v3.1/messages', $payload);

            $resJson = $response->json();
            $isSuccess = $response->successful() && is_array($resJson) && isset($resJson['message_code']) && (int)$resJson['message_code'] === 200;

            if ($isSuccess) {
                Log::info("MOPH Alert sent successfully to CID: " . implode(',', $cidsArray));
                return $resJson;
            }

            $errorMsg = is_array($resJson) && isset($resJson['message']) ? $resJson['message'] : $response->body();
            Log::error("MOPH Alert SendFreeForm failed: HTTP {$response->status()} - {$errorMsg}");
            return false;

        } catch (\Exception $e) {
            Log::error("MOPH Alert SendFreeForm Exception: " . $e->getMessage());
            return false;
        }
    }
 
     /**
      * ส่งแจ้งเตือนแบบใช้เทมเพลต (Template Alert) ไปยังแอปหมอพร้อมรายบุคคล
      *
      * @param string $cid เลขบัตรประชาชน
      * @param string $name ชื่อ-สกุล ผู้รับ
      * @param string $template ชื่อเทมเพลต (เช่น 'ยินดีต้อนรับ', 'แจ้งเตือนคิว', 'ใกล้ถึงคิวของคุณแล้ว')
      * @param string $header หัวข้อความ
      * @param string $text รายละเอียดข้อความ
      * @param string $title หัวข้อแจ้งเตือนตอนส่ง
      * @param string $html ข้อความ HTML แสดงข้างใน
      * @param string $msgText ชื่อในกล่องข้อความ
      * @param array $extra ข้อมูลเพิ่มเติมสำหรับเทมเพลตเฉพาะ (เช่น queue_no, hn_no, service, url, queue_waiting)
      * @param int|null $alertId ID ของการตั้งค่าระบบแจ้งเตือนที่จะใช้ (default: null -> first active)
      * @return array|bool
      */
     public static function sendTemplate(
         string $cid,
         string $name,
         string $template,
         string $header,
         string $text,
         string $title,
         string $html,
         string $msgText,
         array $extra = [],
         ?int $alertId = null
     ) {
         try {
             $query = DB::table('moph_alert')->where('active', 'Y');
             if ($alertId !== null) {
                 $config = (clone $query)->where('id', $alertId)->first(['id', 'client_id', 'secret']);
                 if (!$config) {
                     $config = $query->first(['id', 'client_id', 'secret']);
                 }
             } else {
                 $config = $query->first(['id', 'client_id', 'secret']);
             }
 
             if (!$config || empty($config->client_id) || empty($config->secret)) {
                 Log::warning("MOPH Alert: Active configuration not found or credentials empty (Requested ID: " . ($alertId ?? 'any') . ")");
                 return false;
             }
 
             $payload = array_merge([
                 'cid'           => trim($cid),
                 'name'          => $name,
                 'template'      => $template,
                 'header'        => $header,
                 'text'          => $text,
                 'message_title' => $title,
                 'message_html'  => $html,
                 'message_text'  => $msgText,
                 'message_type'  => 'HPT'
             ], $extra);
 
             $response = Http::timeout(10)
                 ->withoutVerifying()
                 ->withHeaders([
                     'Content-Type' => 'application/json',
                     'client-key'   => $config->client_id,
                     'secret-key'   => $config->secret,
                 ])
                 ->post('https://morpromt2c.moph.go.th/alert/v3.1/template', $payload);
 
             $resJson = $response->json();
             $isSuccess = $response->successful() && is_array($resJson) && isset($resJson['message_code']) && (int)$resJson['message_code'] === 200;
 
             if ($isSuccess) {
                 Log::info("MOPH Alert Template sent successfully to CID: {$cid}");
                 return $resJson;
             }
 
             $errorMsg = is_array($resJson) && isset($resJson['message']) ? $resJson['message'] : $response->body();
             Log::error("MOPH Alert SendTemplate failed: HTTP {$response->status()} - {$errorMsg}");
             return false;
 
         } catch (\Exception $e) {
             Log::error("MOPH Alert SendTemplate Exception: " . $e->getMessage());
             return false;
         }
     }
 }
