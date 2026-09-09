<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\LlmProviderInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class OpenAiProvider implements LlmProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $embedModel;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct(string $apiKey, string $model = 'gpt-4o-mini', string $embedModel = 'text-embedding-3-small')
    {
        $this->apiKey = trim($apiKey);
        $this->model = trim($model) ?: 'gpt-4o-mini';
        $this->embedModel = trim($embedModel) ?: 'text-embedding-3-small';
    }

    public function getProviderName(): string
    {
        return 'OpenAI (' . $this->model . ')';
    }

    public function chat(array $messages, array $options = []): string
    {
        if (empty($this->apiKey)) {
            throw new Exception('กรุณาระบุ OpenAI API Key ในเมนูตั้งค่า AI ก่อนใช้งาน');
        }

        $formattedMessages = [];
        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? ''
            ];
        }

        $payload = [
            'model' => $this->model,
            'messages' => $formattedMessages,
            'temperature' => $options['temperature'] ?? 0.3,
            'max_tokens' => $options['maxTokens'] ?? 2048,
        ];

        $response = Http::timeout(60)
            ->withToken($this->apiKey)
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if (!$response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new Exception("OpenAI API Error: " . $errorMsg);
        }

        $result = $response->json();
        return trim($result['choices'][0]['message']['content'] ?? 'ขออภัย ไม่พบคำตอบจาก OpenAI');
    }

    public function embed(string $text): array
    {
        if (empty($this->apiKey)) {
            throw new Exception('กรุณาระบุ OpenAI API Key ก่อนสร้าง Embedding');
        }

        $response = Http::timeout(30)
            ->withToken($this->apiKey)
            ->post("{$this->baseUrl}/embeddings", [
                'model' => $this->embedModel,
                'input' => $text
            ]);

        if (!$response->successful()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            throw new Exception("OpenAI Embedding Error: " . $errorMsg);
        }

        $values = $response->json('data.0.embedding');
        if (!is_array($values)) {
            throw new Exception("ไม่พบข้อมูล Vector จาก OpenAI Embedding");
        }

        return $values;
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        try {
            if (empty($this->apiKey)) {
                return ['success' => false, 'message' => 'ยังไม่ได้ระบุ OpenAI API Key'];
            }

            $reply = $this->chat([
                ['role' => 'user', 'content' => 'ตอบคำว่า "CONNECTED" เพียงคำเดียวสั้นๆ']
            ]);

            $latency = round((microtime(true) - $start) * 1000);
            return [
                'success' => true,
                'message' => "เชื่อมต่อ OpenAI สำเร็จ! ({$this->model})",
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
