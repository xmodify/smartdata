<?php
 
namespace App\Services;
 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
 
class MophAlertService
 {
     /**
      * ส่งแจ้งเตือนแบบข้อความทั่วไป (Free Form Alert) ไปยังแอปหมอพร้อมรายบุคคล
      *
      * @param array|string $cids เลขบัตรประชาชนผู้รับ (สามารถส่งเป็น String ตัวเดียว หรือ Array ของ CIDs ได้)
      * @param string $title หัวข้อการแจ้งเตือน
      * @param string $messageText ข้อความที่จะแสดงในรายการกล่องข้อความ
      * @param string $messageHtml ข้อความรูปแบบ HTML แสดงภายในกล่องข้อความ
      * @param int $alertId ID ของการตั้งค่าระบบแจ้งเตือนที่จะใช้ (default: 1)
      * @return array|bool ผลลัพธ์จากการยิง API หรือ false หากมีข้อผิดพลาด
      */
     public static function sendFreeForm($cids, string $title, string $messageText, string $messageHtml, int $alertId = 1)
     {
         try {
             $config = DB::table('moph_alert')
                 ->where('id', $alertId)
                 ->where('active', 'Y')
                 ->first(['client_id', 'secret']);
 
             if (!$config || !$config->client_id || !$config->secret) {
                 Log::warning("MOPH Alert: Configuration not found or inactive for ID {$alertId}");
                 return false;
             }
 
             $cidsArray = is_array($cids) ? $cids : [$cids];
 
             $payload = [
                 'cid' => $cidsArray,
                 'messages' => [
                     [
                         'text' => $messageText,
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
 
             if ($response->successful()) {
                 return $response->json();
             }
 
             Log::error("MOPH Alert SendFreeForm failed: Status {$response->status()} - " . $response->body());
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
      * @param int $alertId ID ของการตั้งค่าระบบแจ้งเตือนที่จะใช้ (default: 1)
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
         int $alertId = 1
     ) {
         try {
             $config = DB::table('moph_alert')
                 ->where('id', $alertId)
                 ->where('active', 'Y')
                 ->first(['client_id', 'secret']);
 
             if (!$config || !$config->client_id || !$config->secret) {
                 Log::warning("MOPH Alert: Configuration not found or inactive for ID {$alertId}");
                 return false;
             }
 
             $payload = array_merge([
                 'cid'           => $cid,
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
 
             if ($response->successful()) {
                 return $response->json();
             }
 
             Log::error("MOPH Alert SendTemplate failed: Status {$response->status()} - " . $response->body());
             return false;
 
         } catch (\Exception $e) {
             Log::error("MOPH Alert SendTemplate Exception: " . $e->getMessage());
             return false;
         }
     }
 }
