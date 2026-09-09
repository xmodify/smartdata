<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = App\Models\AiSetting::get('gemini_api_key');
$models = [
    'gemini-3.7-flash',
    'gemini-3.5-flash',
    'gemini-3.1-flash-lite',
    'gemini-2.5-flash-lite',
    'gemini-3.8-flash'
];

foreach ($models as $m) {
    echo "Testing {$m}... ";
    $start = microtime(true);
    try {
        $res = Illuminate\Support\Facades\Http::withoutVerifying()
            ->timeout(10)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$m}:generateContent?key={$apiKey}", [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => 'ตอบสั้นๆ: สวัสดี']]]
                ]
            ]);

        $ms = round((microtime(true) - $start) * 1000);
        if ($res->successful()) {
            $text = $res->json('candidates.0.content.parts.0.text');
            echo "SUCCESS ({$ms}ms): " . trim($text) . "\n";
        } else {
            echo "FAILED ({$ms}ms): HTTP " . $res->status() . " - " . ($res->json('error.message') ?? $res->body()) . "\n";
        }
    } catch (Exception $e) {
        $ms = round((microtime(true) - $start) * 1000);
        echo "ERROR ({$ms}ms): " . $e->getMessage() . "\n";
    }
}
