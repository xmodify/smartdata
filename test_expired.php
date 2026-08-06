<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\License::all() as $l) {
    echo $l->id . ': key=' . $l->license_key . ' | status=' . $l->status . ' | expired_at=' . var_export($l->expired_at, true) . ' | isExpired=' . ($l->isExpired() ? 'true' : 'false') . "\n";
}
