<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "hrd_person_type columns:\n";
    $cols = DB::connection('backoffice')->select('SHOW COLUMNS FROM hrd_person_type');
    foreach ($cols as $c) echo ' - ' . $c->Field . ' (' . $c->Type . ")\n";

    echo "\nhrd_person_type data:\n";
    $rows = DB::connection('backoffice')->select('SELECT * FROM hrd_person_type');
    foreach ($rows as $r) {
        echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
    }

    echo "\nhrd_position columns:\n";
    $colsPos = DB::connection('backoffice')->select('SHOW COLUMNS FROM hrd_position');
    foreach ($colsPos as $c) echo ' - ' . $c->Field . ' (' . $c->Type . ")\n";

    echo "\nSample count by person type:\n";
    $counts = DB::connection('backoffice')->select('
        SELECT t.HR_PERSON_TYPE_ID, t.HR_PERSON_TYPE_NAME, count(p.ID) as total
        FROM hrd_person_type t
        LEFT JOIN hrd_person p ON p.HR_PERSON_TYPE_ID = t.HR_PERSON_TYPE_ID AND p.HR_STATUS_ID = "1"
        GROUP BY t.HR_PERSON_TYPE_ID, t.HR_PERSON_TYPE_NAME
        ORDER BY total DESC
    ');
    foreach ($counts as $cnt) {
        echo "{$cnt->HR_PERSON_TYPE_NAME}: {$cnt->total} คน\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
