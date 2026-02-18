<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DiagnosisController extends Controller
{
    /**
     * Display the HOSxP Diagnosis Index.
     */
    public function index()
    {
        return view('hosxp.diagnosis.index');
    }
    private $disease_configs = [
        'pneumonia' => [
            'name' => 'Pneumonia (ปอดบวม)',
            'icon' => 'bi bi-lungs-fill',
            'color' => 'text-primary',
            'codes' => ['J12', 'J13', 'J14', 'J15', 'J16', 'J17', 'J18']
        ],
        'stroke' => [
            'name' => 'Stroke (หลอดเลือดสมอง)',
            'icon' => 'bi bi-brain-fill',
            'color' => 'text-danger',
            'codes' => ['I60', 'I61', 'I62', 'I63', 'I64']
        ],
        'ihd' => [
            'name' => 'IHD (หัวใจขาดเลือด)',
            'icon' => 'bi bi-heart-pulse-fill',
            'color' => 'text-warning',
            'codes' => ['I20', 'I21', 'I22', 'I23', 'I24', 'I25']
        ],
        'mi' => [
            'name' => 'MI (กล้ามเนื้อหัวใจตาย)',
            'icon' => 'bi bi-heart-fill',
            'color' => 'text-danger',
            'codes' => ['I21']
        ],
        'asthma' => [
            'name' => 'Asthma (หอบหืด)',
            'icon' => 'bi bi-wind',
            'color' => 'text-info',
            'codes' => ['J45', 'J46']
        ],
        'copd' => [
            'name' => 'COPD (ปอดอุดกั้นเรื้อรัง)',
            'icon' => 'bi bi-moisture',
            'color' => 'text-secondary',
            'codes' => ['J44']
        ],
        'sepsis' => [
            'name' => 'Sepsis (ติดเชื้อในกระแสเลือด)',
            'icon' => 'bi bi-bug-fill',
            'color' => 'text-primary',
            'codes' => ['A40', 'A41']
        ],
        'alcohol_withdrawal' => [
            'name' => 'Alcohol Withdrawal',
            'icon' => 'bi bi-cup-straw',
            'color' => 'text-warning',
            'codes' => ['F103']
        ],
        'fracture' => [
            'name' => 'กระดูกสะโพกหัก (Hip Fracture)',
            'icon' => 'bi bi-bandaid-fill',
            'color' => 'text-warning',
            'codes' => ['S720', 'S721', 'S722']
        ],
        'head_injury' => [
            'name' => 'Head Injury',
            'icon' => 'bi bi-headset-vr',
            'color' => 'text-warning',
            'codes' => ['S06']
        ],
        'trauma' => [
            'name' => 'Trauma',
            'icon' => 'bi bi-exclamation-triangle-fill',
            'color' => 'text-warning',
            'codes' => ['V01', 'V02', 'V03', 'V04', 'V05', 'V06', 'V07', 'V08', 'V09', 'V10']
        ],
        'palliative_care' => [
            'name' => 'Palliative Care',
            'icon' => 'bi bi-heart-fill',
            'color' => 'text-warning',
            'codes' => ['Z515']
        ]
    ];

    public function report(Request $request, $type)
    {
        if (!isset($this->disease_configs[$type])) {
            abort(404);
        }

        $config = $this->disease_configs[$type];
        return $this->get_diag_report($request, $type, $config);
    }

    private function get_diag_report(Request $request, $type, $config)
    {
        set_time_limit(300);

        $budget_year_select = DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(7)
            ->get();

        $budget_year_now = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $budget_year = $request->budget_year ?: $budget_year_now;

        $year_data = DB::table('budget_year')
            ->whereIn('LEAVE_YEAR_ID', [$budget_year, $budget_year - 4])
            ->pluck('DATE_BEGIN', 'LEAVE_YEAR_ID');

        $start_date = $year_data[$budget_year] ?? null;
        $start_date_y = $year_data[$budget_year - 4] ?? null;
        $end_date = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->value('DATE_END');

        if (!$start_date) {
            // Fallback if specific year ID not found
            $start_date = ($budget_year - 543) . '-10-01';
            $end_date = ($budget_year - 542) . '-09-30';
            $start_date_y = ($budget_year - 547) . '-10-01';
        }

        $codes = $config['codes'];

        // Build robust diagnosis where clause using LIKE for prefix matching (e.g., A41 matching A419)
        $diag_cols = ['pdx', 'dx0', 'dx1', 'dx2', 'dx3', 'dx4', 'dx5'];
        $where_clauses = [];
        $params_set = [];

        foreach ($diag_cols as $col) {
            $col_likes = [];
            foreach ($codes as $code) {
                $col_likes[] = "v.$col LIKE ?";
                $params_set[] = $code . '%';
            }
            $where_clauses[] = "(" . implode(' OR ', $col_likes) . ")";
        }
        $diag_where = "(" . implode(' OR ', $where_clauses) . ")";

        // Monthly Stats
        $diag_month = DB::connection('hosxp')->select("
            SELECT CASE 
                WHEN MONTH(vstdate)=10 THEN CONCAT('ต.ค. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=11 THEN CONCAT('พ.ย. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=12 THEN CONCAT('ธ.ค. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=1 THEN CONCAT('ม.ค. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=2 THEN CONCAT('ก.พ. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=3 THEN CONCAT('มี.ค. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=4 THEN CONCAT('เม.ย. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=5 THEN CONCAT('พ.ค. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=6 THEN CONCAT('มิ.ย. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=7 THEN CONCAT('ก.ค. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=8 THEN CONCAT('ส.ค. ',RIGHT(YEAR(vstdate)+543,2))
                WHEN MONTH(vstdate)=9 THEN CONCAT('ก.ย. ',RIGHT(YEAR(vstdate)+543,2))
            END AS 'month', COUNT(DISTINCT hn) AS 'hn', COUNT(vn) AS 'visit',
            SUM(CASE WHEN admit <> '' THEN 1 ELSE 0 END) AS admit,
            SUM(CASE WHEN refer <> '' THEN 1 ELSE 0 END) AS refer
            FROM (
                SELECT o.vn, o.vstdate, o.hn, o.an AS admit, r.vn AS refer
                FROM ovst o 
                LEFT JOIN vn_stat v ON v.vn=o.vn
                LEFT JOIN referout r ON r.vn=o.vn
                WHERE o.vstdate BETWEEN ? AND ?
                AND $diag_where
                GROUP BY o.vn 
            ) AS a
            GROUP BY MONTH(vstdate)
            ORDER BY YEAR(vstdate), MONTH(vstdate)", array_merge([$start_date, $end_date], $params_set));

        $diag_m = array_column($diag_month, 'month');
        $diag_visit_m = array_column($diag_month, 'visit');
        $diag_hn_m = array_column($diag_month, 'hn');
        $diag_admit_m = array_column($diag_month, 'admit');
        $diag_refer_m = array_column($diag_month, 'refer');

        // Yearly Trend (5 years)
        $diag_year = DB::connection('hosxp')->select("
            SELECT IF(MONTH(vstdate)>9,YEAR(vstdate)+1,YEAR(vstdate)) + 543 AS year_bud,
            COUNT(DISTINCT hn) AS 'hn', COUNT(vn) AS 'visit',
            SUM(CASE WHEN admit <> '' THEN 1 ELSE 0 END) AS admit,
            SUM(CASE WHEN refer <> '' THEN 1 ELSE 0 END) AS refer
            FROM (
                SELECT o.vn, o.vstdate, o.hn, o.an AS admit, r.vn AS refer
                FROM ovst o 
                LEFT JOIN vn_stat v ON v.vn=o.vn
                LEFT JOIN referout r ON r.vn=o.vn
                WHERE o.vstdate BETWEEN ? AND ?
                AND $diag_where
                GROUP BY o.vn 
            ) AS a
            GROUP BY year_bud
            ORDER BY year_bud", array_merge([$start_date_y, $end_date], $params_set));

        $diag_y = array_column($diag_year, 'year_bud');
        $diag_visit_y = array_column($diag_year, 'visit');
        $diag_hn_y = array_column($diag_year, 'hn');
        $diag_admit_y = array_column($diag_year, 'admit');
        $diag_refer_y = array_column($diag_year, 'refer');

        // Detailed Patient List
        $diag_list = DB::connection('hosxp')->select("
            SELECT o.vn, o.vstdate, o.vsttime, o.oqueue, o.hn, CONCAT(pt.pname,pt.fname,SPACE(1),pt.lname) AS ptname,
            v.age_y, CONCAT(p.hipdata_code,'-',p.name) AS pttype, oc.cc, v.pdx, od.dx, od.icd9,
            o.an AS admit, CONCAT(h.`name`,' [',r.pdx,']') AS refer,
            v.inc03 AS inc_lab, v.inc12 AS inc_drug
            FROM ovst o 
            LEFT JOIN opdscreen oc ON oc.vn=o.vn
            LEFT JOIN vn_stat v ON v.vn=o.vn
            LEFT JOIN (
                SELECT vn, 
                GROUP_CONCAT(DISTINCT IF(diagtype NOT IN ('1','2'), icd10, NULL) SEPARATOR ', ') AS dx,
                GROUP_CONCAT(DISTINCT IF(diagtype = '2', icd10, NULL) SEPARATOR ', ') AS icd9
                FROM ovstdiag GROUP BY vn
            ) od ON od.vn=o.vn
            LEFT JOIN referout r ON r.vn=o.vn
            LEFT JOIN hospcode h ON h.hospcode=r.refer_hospcode
            LEFT JOIN patient pt ON pt.hn=o.hn
            LEFT JOIN pttype p ON p.pttype=o.pttype
            WHERE o.vstdate BETWEEN ? AND ?
            AND $diag_where
            GROUP BY o.vn
            ORDER BY o.vstdate DESC, o.vsttime DESC", array_merge([$start_date, $end_date], $params_set));

        return view('hosxp.diagnosis.report', compact(
            'budget_year_select',
            'budget_year',
            'config',
            'diag_m',
            'diag_visit_m',
            'diag_hn_m',
            'diag_admit_m',
            'diag_refer_m',
            'diag_y',
            'diag_visit_y',
            'diag_hn_y',
            'diag_admit_y',
            'diag_refer_y',
            'diag_list'
        ));
    }

}
