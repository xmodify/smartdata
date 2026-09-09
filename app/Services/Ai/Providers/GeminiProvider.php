<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\LlmProviderInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class GeminiProvider implements LlmProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $embedModel;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct(string $apiKey, string $model = 'gemini-3.6-flash', string $embedModel = 'gemini-embedding-001')
    {
        $this->apiKey = trim($apiKey);
        $this->model = trim($model) ?: 'gemini-3.6-flash';
        $this->embedModel = trim($embedModel) ?: 'gemini-embedding-001';
    }

    public function getProviderName(): string
    {
        return 'Google Gemini (' . $this->model . ')';
    }

    public function chat(array $messages, array $options = []): string
    {
        if (empty($this->apiKey)) {
            throw new Exception('กรุณาระบุ Google Gemini API Key ในเมนูตั้งค่า AI ก่อนใช้งาน');
        }

        $systemInstruction = null;
        $contents = [];

        foreach ($messages as $msg) {
            $role = $msg['role'] ?? 'user';
            $text = $msg['content'] ?? '';

            if ($role === 'system') {
                $systemInstruction = ['parts' => [['text' => $text]]];
            } else {
                $geminiRole = ($role === 'assistant') ? 'model' : 'user';
                $contents[] = [
                    'role' => $geminiRole,
                    'parts' => [['text' => $text]]
                ];
            }
        }

        if (empty($contents)) {
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => 'สวัสดี']]
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.3,
                'maxOutputTokens' => $options['maxTokens'] ?? 2048,
            ]
        ];

        if ($systemInstruction) {
            $payload['systemInstruction'] = $systemInstruction;
        }

        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Attempt request with retry for high-demand spikes (503/429)
        $maxAttempts = 2;
        $attempt = 0;
        $lastError = '';

        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                $response = Http::withoutVerifying()
                    ->timeout(60)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $this->apiKey
                    ])
                    ->post($url, $payload);

                if ($response->successful()) {
                    $result = $response->json();
                    $candidates = $result['candidates'] ?? [];
                    if (!empty($candidates[0]['content']['parts'][0]['text'])) {
                        return trim($candidates[0]['content']['parts'][0]['text']);
                    }
                    return 'ขออภัย ไม่สามารถสร้างคำตอบได้ในขณะนี้';
                }

                $status = $response->status();
                $lastError = $response->json('error.message') ?? $response->body();

                // If transient high-demand or rate-limited, wait briefly and try fallback model if available
                if (in_array($status, [429, 503]) && $attempt < $maxAttempts) {
                    usleep(1200000); // 1.2s
                    // Fallback to fast & available flash-lite if 3.7 spikes or hits rate limits
                    if (str_contains($this->model, '3.7') || str_contains($this->model, '3.6')) {
                        $url = "{$this->baseUrl}/models/gemini-3.1-flash-lite:generateContent?key={$this->apiKey}";
                    }
                    continue;
                }

                throw new Exception("Gemini API Error: " . $lastError);
            } catch (Exception $e) {
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
                usleep(1000000);
            }
        }

        return 'ขออภัย ไม่สามารถสร้างคำตอบได้ในขณะนี้: ' . $lastError;
    }

    public function embed(string $text): array
    {
        if (empty($this->apiKey)) {
            throw new Exception('กรุณาระบุ Google Gemini API Key ก่อนสร้าง Embedding');
        }

        $url = "{$this->baseUrl}/models/{$this->embedModel}:embedContent?key={$this->apiKey}";

        $response = Http::withoutVerifying()
            ->timeout(30)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $this->apiKey
            ])
            ->post($url, [
                'model' => "models/{$this->embedModel}",
                'content' => [
                    'parts' => [['text' => $text]]
                ]
            ]);

        if (!$response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new Exception("Gemini Embedding Error: " . $errorMsg);
        }

        $values = $response->json('embedding.values');
        if (!is_array($values)) {
            throw new Exception("ไม่พบข้อมูล Vector จาก Gemini Embedding");
        }

        return $values;
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        try {
            if (empty($this->apiKey)) {
                return ['success' => false, 'message' => 'ยังไม่ได้ระบุ Google Gemini API Key'];
            }

            $reply = $this->chat([
                ['role' => 'user', 'content' => 'ตอบคำว่า "การเชื่อมต่อระบบ AI สำเร็จ" เพียงข้อความเดียวสั้นๆ']
            ]);

            $latency = round((microtime(true) - $start) * 1000);
            return [
                'success' => true,
                'message' => "เชื่อมต่อ Gemini สำเร็จ! ({$this->model})",
                'model' => $this->model,
                'latency_ms' => $latency,
                'response' => trim($reply)
            ];
        } catch (Exception $e) {
            $latency = round((microtime(true) - $start) * 1000);
            return [
                'success' => false,
                'message' => "เชื่อมต่อล้มเหลว: " . $e->getMessage(),
                'latency_ms' => $latency
            ];
        }
    }

    /**
     * Fetch all available models for this Gemini API Key.
     */
    public function listModels(): array
    {
        if (empty($this->apiKey)) {
            throw new Exception('กรุณาระบุ Google Gemini API Key ก่อนดึงรายชื่อโมเดล');
        }

        $url = "{$this->baseUrl}/models?key={$this->apiKey}";
        $response = Http::withoutVerifying()
            ->timeout(25)
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->get($url);

        if (!$response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new Exception("ดึงรายชื่อ Model ไม่สำเร็จ: " . $errorMsg);
        }

        $allModels = $response->json('models') ?? [];
        $chatModels = [];
        $embedModels = [];

        foreach ($allModels as $m) {
            $rawName = $m['name'] ?? '';
            $cleanName = preg_replace('/^models\//', '', $rawName);
            $methods = $m['supportedGenerationMethods'] ?? [];
            $displayName = $m['displayName'] ?? $cleanName;
            $desc = $m['description'] ?? '';

            if (in_array('generateContent', $methods)) {
                $chatModels[] = [
                    'id' => $cleanName,
                    'name' => $displayName,
                    'description' => $desc
                ];
            }

            if (in_array('embedContent', $methods)) {
                $embedModels[] = [
                    'id' => $cleanName,
                    'name' => $displayName,
                    'description' => $desc
                ];
            }
        }

        return [
            'success' => true,
            'chat_models' => $chatModels,
            'embed_models' => $embedModels,
            'total' => count($chatModels) + count($embedModels)
        ];
    }
}
