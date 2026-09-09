<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\AiChatSession;
use Illuminate\Http\Request;
use App\Http\Controllers\Ai\ChatController;

$user = User::first();
auth()->login($user);

$sessionUuid = (string) Illuminate\Support\Str::uuid();
$controller = app(ChatController::class);

echo "=== SIMULATION 1: User asks 'บุคลากรกี่คน' ===\n";
$req1 = new Request([
    'message' => 'บุคลากรกี่คน',
    'session_uuid' => $sessionUuid,
    'mode' => 'smart',
    'target_db' => 'auto'
]);
$res1 = $controller->sendMessage($req1);
$data1 = json_decode($res1->getContent(), true);
echo "Mode: " . ($data1['mode'] ?? 'error') . "\n";
echo "Target DB: " . ($data1['target_db'] ?? 'none') . "\n";
echo "Content:\n" . ($data1['content'] ?? '') . "\n";
if (!empty($data1['sql'])) {
    echo "SQL: " . $data1['sql'] . "\n";
}

echo "\n=== SIMULATION 2: User asks follow-up 'กี่ประเภท' ===\n";
$req2 = new Request([
    'message' => 'กี่ประเภท',
    'session_uuid' => $sessionUuid,
    'mode' => 'smart',
    'target_db' => 'auto'
]);
$res2 = $controller->sendMessage($req2);
$data2 = json_decode($res2->getContent(), true);
echo "Mode: " . ($data2['mode'] ?? 'error') . "\n";
echo "Target DB: " . ($data2['target_db'] ?? 'none') . "\n";
echo "Content:\n" . ($data2['content'] ?? '') . "\n";
if (!empty($data2['sql'])) {
    echo "SQL: " . $data2['sql'] . "\n";
}
if (!empty($data2['rows'])) {
    echo "Rows count: " . count($data2['rows']) . "\n";
    foreach ($data2['rows'] as $r) {
        echo " - " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
    }
}

echo "\n=== SIMULATION 3: User asks follow-up 'แยกตามกลุ่มงาน' ===\n";
$req3 = new Request([
    'message' => 'แยกตามกลุ่มงาน',
    'session_uuid' => $sessionUuid,
    'mode' => 'smart',
    'target_db' => 'auto'
]);
$res3 = $controller->sendMessage($req3);
$data3 = json_decode($res3->getContent(), true);
echo "Mode: " . ($data3['mode'] ?? 'error') . "\n";
echo "Target DB: " . ($data3['target_db'] ?? 'none') . "\n";
echo "Content:\n" . ($data3['content'] ?? '') . "\n";
if (!empty($data3['sql'])) {
    echo "SQL: " . $data3['sql'] . "\n";
}
