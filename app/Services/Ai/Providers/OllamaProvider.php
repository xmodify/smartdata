<?php

namespace App\Services\Ai\Providers;

use App\Services\Ai\Contracts\LlmProviderInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class OllamaProvider implements LlmProviderInterface
{
    protected string $baseUrl;
    protected string $model;
    protected string $embedModel;

    public function __construct(string $baseUrl = 'http://localhost:11434', string $model = 'deepseek-r1:latest', string $embedModel = 'nomic-embed-text')
    {
        $this->baseUrl = rtrim(trim($baseUrl) ?: 'http://localhost:11434', '/');
        $this->model = trim($model) ?: 'deepseek-r1:latest';
        $this->embedModel = trim($embedModel) ?: 'nomic-embed-text';
    }

    public function getProviderName(): string
    {
        return 'Local Ollama (' . $this->model . ')';
    }

    public function chat(array $messages, array $options = []): string
    {
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
            'stream' => false,
            'options' => [
                'temperature' => $options['temperature'] ?? 0.3,
            ]
        ];

        try {
            $response = Http::withoutVerifying()
                ->timeout(120)
                ->post("{$this->baseUrl}/api/chat", $payload);

            if (!$response->successful()) {
                throw new Exception("Ollama Server Error ({$response->status()}): " . $response->body());
            }

            $result = $response->json();
            $content = $result['message']['content'] ?? '';
            
            // Clean DeepSeek <think>...</think> tags if present for concise UI output
            $cleaned = preg_replace('/<think>[\s\S]*?<\/think>/', '', $content);
            return trim($cleaned ?: $content);
        } catch (Exception $e) {
            throw new Exception("ไม่สามารถเชื่อมต่อ Local Ollama ได้ที่ {$this->baseUrl}: " . $e->getMessage());
        }
    }

    public function embed(string $text): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->post("{$this->baseUrl}/api/embeddings", [
                    'model' => $this->embedModel,
                    'prompt' => $text
                ]);

            if (!$response->successful()) {
                throw new Exception("Ollama Embed Error ({$response->status()}): " . $response->body());
            }

            $values = $response->json('embedding');
            if (!is_array($values)) {
                throw new Exception("ไม่พบข้อมูล Vector จาก Ollama Embed");
            }

            return $values;
        } catch (Exception $e) {
            throw new Exception("Ollama Embed Failed: " . $e->getMessage());
        }
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        try {
            // Check Ollama version or tags first
            $check = Http::withoutVerifying()->timeout(5)->get("{$this->baseUrl}/api/version");
            if (!$check->successful()) {
                return [
                    'success' => false,
                    'message' => "ไม่สามารถเชื่อมต่อ Ollama ที่ {$this->baseUrl} ได้ กรุณาเปิดโปรแกรม Ollama หรือตรวจสอบ URL",
                    'latency_ms' => round((microtime(true) - $start) * 1000)
                ];
            }

            $reply = $this->chat([
                ['role' => 'user', 'content' => 'ตอบคำว่า "การเชื่อมต่อระบบ AI สำเร็จ" เพียงข้อความเดียวสั้นๆ']
            ]);

            $latency = round((microtime(true) - $start) * 1000);
            return [
                'success' => true,
                'message' => "เชื่อมต่อ Local Ollama สำเร็จ! ({$this->model})",
                'model' => $this->model,
                'latency_ms' => $latency,
                'response' => trim($reply)
            ];
        } catch (Exception $e) {
            $latency = round((microtime(true) - $start) * 1000);
            return [
                'success' => false,
                'message' => "เชื่อมต่อ Ollama ล้มเหลว: " . $e->getMessage(),
                'latency_ms' => $latency
            ];
        }
    }
}
