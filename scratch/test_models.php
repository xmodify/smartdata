<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = App\Models\AiSetting::get('gemini_api_key');
$res = Illuminate\Support\Facades\Http::withoutVerifying()
    ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

$models = $res->json('models') ?? [];
echo "Available models:\n";
foreach ($models as $m) {
    if (str_contains($m['name'], 'flash') || str_contains($m['name'], 'gemini')) {
        echo "- " . $m['name'] . "\n";
    }
}
