<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sessions = App\Models\AiChatSession::withCount('messages')->get();
echo "Total sessions: " . $sessions->count() . "\n";
foreach ($sessions as $s) {
    echo "ID: {$s->id} | User: {$s->user_id} | UUID: {$s->session_uuid} | Title: {$s->title} | Msgs: {$s->messages_count}\n";
}
