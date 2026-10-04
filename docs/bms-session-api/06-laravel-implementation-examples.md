# 06. ตัวอย่างการพัฒนาด้วย Laravel ใน SmartData

> โค้ดตัวอย่างการเขียน Service และ Controller ใน Laravel (PHP 8.2+) สำหรับเชื่อมต่อ BMS Session Data API และ BMS AI Gateway

---

## 1. Service จัดการ BMS Session & Data API (`BmsSessionService.php`)

ไฟล์นี้ใช้สำหรับ Resolve `bms-session-id`, แคชข้อมูล Tunnel และส่ง Query ไปยัง HOSxP

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class BmsSessionService
{
    /**
     * Resolve bms-session-id จาก hosxp.net PasteJSON
     */
    public function resolveSession(string $bmsSessionId): array
    {
        return Cache::remember("bms_session_{$bmsSessionId}", 3600, function () use ($bmsSessionId) {
            $response = Http::timeout(10)->get('https://hosxp.net/phps/bms_session_paste.php', [
                'bms-session-id' => $bmsSessionId,
            ]);

            if (!$response->successful()) {
                throw new Exception("ไม่สามารถเชื่อมต่อ PasteJSON ได้: HTTP " . $response->status());
            }

            $data = $response->json();
            if (($data['MessageCode'] ?? null) !== 200 || empty($data['result']['bms_url'])) {
                throw new Exception("Resolve Session ไม่สำเร็จ: " . ($data['Message'] ?? 'Unknown Error'));
            }

            return $data['result'];
        });
    }

    /**
     * รันคำสั่ง SQL ผ่าน /api/sql
     */
    public function executeSql(string $bmsSessionId, string $sql, array $params = [], int $limit = 100): array
    {
        $session = $this->resolveSession($bmsSessionId);
        $bmsUrl = rtrim($session['bms_url'], '/');
        $sessionCode = $session['bms_session_code'];

        $response = Http::withToken($sessionCode)
            ->timeout(60)
            ->post("{$bmsUrl}/api/sql", [
                'sql' => $sql,
                'params' => $params,
                'limit' => $limit,
            ]);

        if (!$response->successful()) {
            Log::error("BMS SQL Execution Error", ['body' => $response->body()]);
            throw new Exception("การรันคำสั่ง SQL ล้มเหลว: HTTP " . $response->status());
        }

        $res = $response->json();
        return $res['result'] ?? [];
    }

    /**
     * ขอ Primary Key ใหม่ผ่าน /api/function?name=get_serialnumber
     */
    public function getSerialNumber(string $bmsSessionId, string $tableName, string $fieldName): int
    {
        $session = $this->resolveSession($bmsSessionId);
        $bmsUrl = rtrim($session['bms_url'], '/');
        $sessionCode = $session['bms_session_code'];

        $response = Http::withToken($sessionCode)
            ->timeout(15)
            ->post("{$bmsUrl}/api/function?name=get_serialnumber", [
                'serial_name' => $tableName,
                'table_name' => $tableName,
                'field_name' => $fieldName,
            ]);

        $res = $response->json();
        if (($res['MessageCode'] ?? null) !== 200 || !isset($res['Value'])) {
            throw new Exception("ไม่สามารถขอ Serial Number ได้: " . ($res['Message'] ?? 'Unknown'));
        }

        return (int) $res['Value'];
    }
}
```

---

## 2. Service จัดการ BMS Cloud AI Gateway (`BmsAiService.php`)

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class BmsAiService
{
    protected string $aiBaseUrl = 'https://ai-api.kube.bmscloud.in.th';

    /**
     * ดึงรายชื่อโมเดล AI ที่พร้อมใช้งาน
     */
    public function getModels(): array
    {
        $response = Http::timeout(10)->get("{$this->aiBaseUrl}/v1/models");
        return $response->json()['data'] ?? [];
    }

    /**
     * ส่งคำขอ Chat Completions (LLM)
     */
    public function chat(string $bmsSessionId, array $messages, string $model = 'deepseek-chat', float $temperature = 0.3): string
    {
        $response = Http::withToken($bmsSessionId) // ใช้ bms-session-id ดิบโดยตรง
            ->timeout(290)
            ->post("{$this->aiBaseUrl}/v1/chat/completions", [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
            ]);

        if (!$response->successful()) {
            throw new Exception("AI Chat Request Failed: " . $response->body());
        }

        $res = $response->json();
        return $res['choices'][0]['message']['content'] ?? '';
    }

    /**
     * ค้นหารหัสโรค ICD-11 Semantic Lookup
     */
    public function lookupIcd11(string $bmsSessionId, string $clinicalNote, int $topK = 5): array
    {
        $response = Http::withToken($bmsSessionId)
            ->timeout(30)
            ->post("{$this->aiBaseUrl}/v1/icd11/lookup", [
                'text' => $clinicalNote,
                'top_k' => $topK,
            ]);

        if (!$response->successful()) {
            throw new Exception("ICD-11 Lookup Failed: " . $response->body());
        }

        return $response->json()['results'] ?? [];
    }

    /**
     * สังเคราะห์เสียงภาษาไทย (Thai TTS)
     */
    public function textToSpeech(string $bmsSessionId, string $text, string $voice = 'female'): string
    {
        $response = Http::withToken($bmsSessionId)
            ->timeout(60)
            ->post("{$this->aiBaseUrl}/v1/audio/speech", [
                'model' => 'voxcpm-thai',
                'input' => $text,
                'voice' => $voice,
                'response_format' => 'mp3'
            ]);

        if (!$response->successful()) {
            throw new Exception("TTS Synthesis Failed: " . $response->body());
        }

        return $response->body(); // Raw Binary MP3
    }
}
```

---

## 3. ตัวอย่างการนำไปใช้ใน Controller (`BmsApiController.php`)

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BmsSessionService;
use App\Services\BmsAiService;

class BmsApiController extends Controller
{
    public function index(Request $request, BmsSessionService $bms)
    {
        $sessionId = $request->query('bms-session-id');
        $hn = $request->query('hn');

        if (!$sessionId) {
            return view('bms.error', ['message' => 'ไม่พบ bms-session-id กรุณาเปิดผ่าน HOSxP']);
        }

        // ดึงข้อมูลผู้ป่วย
        $patient = null;
        if ($hn) {
            $patient = $bms->executeSql($sessionId, "SELECT * FROM patient WHERE hn = :hn LIMIT 1", ['hn' => $hn]);
        }

        return view('bms.dashboard', [
            'sessionId' => $sessionId,
            'patient' => $patient[0] ?? null,
            'hn' => $hn,
        ]);
    }

    public function askAi(Request $request, BmsAiService $ai)
    {
        $request->validate([
            'session_id' => 'required|string',
            'prompt' => 'required|string',
        ]);

        $answer = $ai->chat($request->session_id, [
            ['role' => 'user', 'content' => $request->prompt]
        ]);

        return response()->json(['reply' => $answer]);
    }
}
```
