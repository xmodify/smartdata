<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = App\Models\AiSetting::get('gemini_api_key');
$models = ['gemini-3.7-flash', 'gemini-3.1-flash-lite', 'gemini-3.8-flash'];

foreach ($models as $m) {
    echo "=== Model: {$m} ===\n";
    $provider = new App\Services\Ai\Providers\GeminiProvider($apiKey, $m);
    try {
        $start = microtime(true);
        $res = $provider->chat([
            ['role' => 'system', 'content' => 'You are SQL helper. Output ONLY SQL query in ```sql ```.'],
            ['role' => 'user', 'content' => 'บุคลากรกี่คน จาก hrd_person']
        ]);
        $ms = round((microtime(true) - $start) * 1000);
        echo "SUCCESS in {$ms}ms:\n{$res}\n\n";
        break;
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n\n";
    }
}
