<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
auth()->login($user);

$controller = app(App\Http\Controllers\Ai\ChatController::class);

echo "Testing deleteSession for UUID e31bc359-002b-4153-8ba0-68fe1327bf86...\n";
$res = $controller->deleteSession('e31bc359-002b-4153-8ba0-68fe1327bf86');
echo "Result: " . $res->getContent() . "\n";

$check = App\Models\AiChatSession::where('session_uuid', 'e31bc359-002b-4153-8ba0-68fe1327bf86')->first();
echo "Found after delete? " . ($check ? 'YES' : 'NO') . "\n";
