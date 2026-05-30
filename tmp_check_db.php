<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$start_date = '2025-10-01';
$t1 = microtime(true);
$results = DB::connection('hosxp')->select("
    SELECT ipd_nurse_eval_range_code, COUNT(*) AS count
    FROM ipt
    WHERE dchdate BETWEEN '2025-10-01' AND '2026-05-30'
    GROUP BY ipd_nurse_eval_range_code
");
$t2 = microtime(true);

echo "Execution time: " . round($t2 - $t1, 4) . " seconds\n";
print_r($results);
