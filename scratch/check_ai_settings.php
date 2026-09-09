<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$settings = App\Models\AiSetting::getAllSettings();
foreach ($settings as $k => $v) {
    if (str_contains($k, 'key')) {
        $v = substr($v, 0, 8) . '...' . substr($v, -4);
    }
    echo "{$k} => {$v}\n";
}
