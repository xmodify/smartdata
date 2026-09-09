<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\AiChatSession::where('session_uuid', 'test-live-send')->first();
if ($s) {
    $s->messages()->delete();
    $s->delete();
    echo "Cleaned up test-live-send\n";
}
