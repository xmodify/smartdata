<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function showCols($table) {
    echo "\n--- Table: $table ---\n";
    $cols = DB::connection('backoffice')->select("DESC $table");
    foreach($cols as $c) {
        echo $c->Field . " (" . $c->Type . ")\n";
    }
}

showCols('hrd_department_sub_sub');
showCols('hrd_department_sub');
showCols('hrd_department');
