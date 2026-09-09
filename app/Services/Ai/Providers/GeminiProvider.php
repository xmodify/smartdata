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

    public function __construct(string $apiKey, string $model = 'gemini-2.0-flash', string $embedModel = 'text-embedding-004')
    {
        $this->apiKey = trim($apiKey);
        $this->model = trim($model) ?: 'gemini-2.0-flash';
        $this->embedModel = trim($embedModel) ?: 'text-embedding-004';
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

        $response = Http::timeout(60)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $payload);

        if (!$response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new Exception("Gemini API Error: " . $errorMsg);
        }

        $result = $response->json();
        $candidates = $result['candidates'] ?? [];
        if (!empty($candidates[0]['content']['parts'][0]['text'])) {
            return trim($candidates[0]['content']['parts'][0]['text']);
        }

        return 'ขออภัย ไม่สามารถสร้างคำตอบได้ในขณะนี้';
    }

    public function embed(string $text): array
    {
        if (empty($this->apiKey)) {
            throw new Exception('กรุณาระบุ Google Gemini API Key ก่อนสร้าง Embedding');
        }

        $url = "{$this->baseUrl}/models/{$this->embedModel}:embedContent?key={$this->apiKey}";

        $response = Http::timeout(30)
            ->withHeaders(['Content-Type' => 'application/json'])
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
                ['role' => 'user', 'content' => 'ตอบคำว่า "CONNECTED" เพียงคำเดียวสั้นๆ']
            ]);

            $latency = round((microtime(true) - $start) * 1000);
            return [
                'success' => true,
                'message' => "เชื่อมต่อ Gemini สำเร็จ! ({$this->model})",
                'latency_ms' => $latency,
                'response' => $reply
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
}
