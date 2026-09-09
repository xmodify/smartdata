<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
auth()->login($user);

$controller = app(App\Http\Controllers\Ai\ChatController::class);

echo "Sending follow-up message: 'กี่ประเภท'...\n";
$req = new Illuminate\Http\Request([
    'message' => 'กี่ประเภท',
    'session_uuid' => 'test-live-send',
    'mode' => 'smart',
    'target_db' => 'auto'
]);

$res = $controller->sendMessage($req);
$data = json_decode($res->getContent(), true);

echo "Success: " . ($data['success'] ? 'YES' : 'NO') . "\n";
echo "Mode: " . ($data['mode'] ?? 'none') . "\n";
echo "Content: \n" . ($data['content'] ?? 'empty') . "\n";
if (!empty($data['sql'])) {
    echo "SQL: " . $data['sql'] . "\n";
}
if (!empty($data['rows'])) {
    echo "Rows: " . count($data['rows']) . "\n";
    foreach ($data['rows'] as $r) {
        echo " - " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
    }
}
