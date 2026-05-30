<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$start_date = '2025-10-01';
$end_date = '2026-05-30';

$t1 = microtime(true);
$results = DB::connection('hosxp')->select("
    SELECT 
        CASE 
            WHEN MONTH(i.dchdate) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 1 THEN CONCAT('ม.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 2 THEN CONCAT('ก.พ. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 3 THEN CONCAT('มี.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 4 THEN CONCAT('เม.ย. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 5 THEN CONCAT('พ.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 6 THEN CONCAT('มิ.ย. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 7 THEN CONCAT('ก.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 8 THEN CONCAT('ส.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
            WHEN MONTH(i.dchdate) = 9 THEN CONCAT('ก.ย. ', RIGHT(YEAR(i.dchdate) + 543, 2))
        END AS month,
        SUM(CASE WHEN i.ipt_severe_type_id = 1 THEN 1 ELSE 0 END) AS admit_1,
        SUM(CASE WHEN i.ipt_severe_type_id = 2 THEN 1 ELSE 0 END) AS admit_2,
        SUM(CASE WHEN i.ipt_severe_type_id = 3 THEN 1 ELSE 0 END) AS admit_3,
        SUM(CASE WHEN i.ipt_severe_type_id = 4 THEN 1 ELSE 0 END) AS admit_4,
        SUM(CASE WHEN i.ipt_severe_type_id IS NULL THEN 1 ELSE 0 END) AS admit_null,
        SUM(CASE WHEN i.dch_severe_type_id = 1 THEN 1 ELSE 0 END) AS dch_1,
        SUM(CASE WHEN i.dch_severe_type_id = 2 THEN 1 ELSE 0 END) AS dch_2,
        SUM(CASE WHEN i.dch_severe_type_id = 3 THEN 1 ELSE 0 END) AS dch_3,
        SUM(CASE WHEN i.dch_severe_type_id = 4 THEN 1 ELSE 0 END) AS dch_4,
        SUM(CASE WHEN i.dch_severe_type_id IS NULL THEN 1 ELSE 0 END) AS dch_null,
        COUNT(i.an) AS total_patients
    FROM ipt i
    WHERE i.dchdate BETWEEN ? AND ?
      AND i.dchdate IS NOT NULL
    GROUP BY YEAR(i.dchdate), MONTH(i.dchdate)
    ORDER BY YEAR(i.dchdate), MONTH(i.dchdate)
", [$start_date, $end_date]);
$t2 = microtime(true);

echo "Execution time: " . round($t2 - $t1, 4) . " seconds\n";
print_r($results);
