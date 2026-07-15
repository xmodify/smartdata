<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$ans = ['680004907', '680004683', '680005109', '680005088', '680005494', '680005787', '690002021', '690002137', '690002186', '690002524'];

try {
    foreach ($ans as $an) {
        $ipt = DB::connection('hosxp')
            ->table('ipt')
            ->where('an', $an)
            ->first(['an', 'dchdate', 'ipt_summary_status_id']);
            
        $diags = DB::connection('hosxp')
            ->table('ipt_doctor_diag')
            ->where('an', $an)
            ->get();
            
        $iptdiag = DB::connection('hosxp')
            ->table('iptdiag')
            ->where('an', $an)
            ->get();

        echo "AN: {$an} | dchdate: " . ($ipt->dchdate ?? 'N/A') . " | ipt_summary_status_id: " . ($ipt->ipt_summary_status_id ?? 'NULL') . "\n";
        echo "  ipt_doctor_diag rows: " . count($diags) . "\n";
        foreach ($diags as $d) {
            echo "    - doctor: {$d->doctor_code} | diag_text: {$d->diag_text} | diagtype: {$d->diagtype} | final_diag: {$d->final_diag}\n";
        }
        echo "  iptdiag rows: " . count($iptdiag) . "\n";
        foreach ($iptdiag as $id) {
            echo "    - icd10: {$id->icd10} | diagtype: {$id->diagtype}\n";
        }
        echo "--------------------------------------------------\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
