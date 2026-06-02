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
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        $configs = $this->disease_configs;

        if ($category !== 'all') {
            $configs = array_filter($configs, function ($config) use ($category) {
                return in_array($category, $config['categories'] ?? []);
            });
        }

        return view('hosxp.diagnosis.index', compact('category', 'configs'));
    }

    private $disease_configs = [
        'stroke' => [
            'name' => 'Stroke (โรคหลอดเลือดสมอง)',
            'icon' => 'fas fa-brain',
            'color' => 'text-danger',
            'codes' => ['I64', 'I619', 'I639', 'I609'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Cardiovascular & Neurology',
            'group_icon' => 'fas fa-heart-pulse',
            'group_color' => '#fee2e2'
        ],
        'sepsis' => [
            'name' => 'Sepsis (ภาวะติดเชื้อในกระแสเลือด)',
            'icon' => 'fas fa-bug',
            'color' => 'text-primary',
            'codes' => ['A419', 'A415', 'A410'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Respiratory & Sepsis',
            'group_icon' => 'fas fa-lungs',
            'group_color' => '#e0f2fe'
        ],
        'septic_shock' => [
            'name' => 'Septic Shock (ช็อกจากการติดเชื้อ)',
            'icon' => 'fas fa-biohazard',
            'color' => 'text-danger',
            'codes' => ['R572'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Respiratory & Sepsis',
            'group_icon' => 'fas fa-lungs',
            'group_color' => '#e0f2fe'
        ],
        'pneumonia' => [
            'name' => 'Pneumonia (ปอดอักเสบ)',
            'icon' => 'fas fa-lungs',
            'color' => 'text-primary',
            'codes' => ['J189', 'J180', 'J159', 'J129'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Respiratory & Sepsis',
            'group_icon' => 'fas fa-lungs',
            'group_color' => '#e0f2fe'
        ],
        'mi' => [
            'name' => 'MI (กล้ามเนื้อหัวใจตายเฉียบพลัน)',
            'icon' => 'fas fa-heart',
            'color' => 'text-danger',
            'codes' => ['I219', 'I210', 'I211'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Cardiovascular & Neurology',
            'group_icon' => 'fas fa-heart-pulse',
            'group_color' => '#fee2e2'
        ],
        'ihd' => [
            'name' => 'IHD (โรคหัวใจขาดเลือดเรื้อรัง)',
            'icon' => 'fas fa-heart-pulse',
            'color' => 'text-warning',
            'codes' => ['I259', 'I209'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Cardiovascular & Neurology',
            'group_icon' => 'fas fa-heart-pulse',
            'group_color' => '#fee2e2'
        ],
        'copd' => [
            'name' => 'COPD (โรคปอดอุดกั้นเรื้อรัง)',
            'icon' => 'fas fa-smog',
            'color' => 'text-secondary',
            'codes' => ['J449', 'J440', 'J441'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Respiratory & Sepsis',
            'group_icon' => 'fas fa-lungs',
            'group_color' => '#e0f2fe'
        ],
        'asthma' => [
            'name' => 'Asthma (โรคหืด)',
            'icon' => 'fas fa-wind',
            'color' => 'text-info',
            'codes' => ['J459', 'J450', 'J451', 'J46'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Respiratory & Sepsis',
            'group_icon' => 'fas fa-lungs',
            'group_color' => '#e0f2fe'
        ],
        'head_injury' => [
            'name' => 'Head Injury (การบาดเจ็บที่ศีรษะ)',
            'icon' => 'fas fa-head-side-virus',
            'color' => 'text-warning',
            'codes' => ['S099', 'S060', 'S062'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Trauma & Injury',
            'group_icon' => 'fas fa-car-crash',
            'group_color' => '#ffedd5'
        ],
        'fracture' => [
            'name' => 'Broken Hip (กระดูกสะโพกหัก)',
            'icon' => 'fas fa-bone',
            'color' => 'text-warning',
            'codes' => ['S7200', 'S7210', 'S7290'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Trauma & Injury',
            'group_icon' => 'fas fa-car-crash',
            'group_color' => '#ffedd5'
        ],
        'trauma' => [
            'name' => 'Trauma (การบาดเจ็บ)',
            'icon' => 'fas fa-car-burst',
            'color' => 'text-warning',
            'codes' => ['T149', 'V892'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Trauma & Injury',
            'group_icon' => 'fas fa-car-crash',
            'group_color' => '#ffedd5'
        ],
        'alcohol_withdrawal' => [
            'name' => 'Alcohol Withdrawal (ภาวะถอนแอลกอฮอล์)',
            'icon' => 'fas fa-wine-glass-empty',
            'color' => 'text-warning',
            'codes' => ['F103', 'F104'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Others',
            'group_icon' => 'fas fa-clipboard-check',
            'group_color' => '#fef3c7'
        ],
        'palliative_care' => [
            'name' => 'Palliative Care (การดูแลแบบประคับประคอง)',
            'icon' => 'fas fa-hand-holding-heart',
            'color' => 'text-warning',
            'codes' => ['Z515'],
            'categories' => ['opd', 'ipd', 'refer'],
            'group' => 'Others',
            'group_icon' => 'fas fa-clipboard-check',
            'group_color' => '#fef3c7'
        ],
        'ic_bacteria_virus' => [
            'name' => 'รหัสเชื้อก่อโรคแบคทีเรียและไวรัส (B95-B97)',
            'icon' => 'fas fa-bug',
            'color' => 'text-danger',
            'codes' => ['B95', 'B96', 'B97'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_drug_resistance' => [
            'name' => 'รหัสการดื้อยาต้านจุลชีพ (Z16 และ U84)',
            'icon' => 'fas fa-capsules',
            'color' => 'text-warning',
            'codes' => ['Z16', 'U84'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_vap_j958' => [
            'name' => 'ปอดอักเสบจากการใช้เครื่องช่วยหายใจ (VAP - J95.8)',
            'icon' => 'fas fa-lungs',
            'color' => 'text-primary',
            'codes' => ['J958'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_vap_j180' => [
            'name' => 'ปอดอักเสบจากการใช้เครื่องช่วยหายใจ (VAP - J18.0)',
            'icon' => 'fas fa-lungs',
            'color' => 'text-primary',
            'codes' => ['J180'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_ssi_t814' => [
            'name' => 'การติดเชื้อที่แผลผ่าตัด (SSI - T81.4)',
            'icon' => 'fas fa-scissors',
            'color' => 'text-danger',
            'codes' => ['T814'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_cauti_t83511' => [
            'name' => 'การติดเชื้อจากสายสวนปัสสาวะค้างในร่างกาย (T83.511)',
            'icon' => 'fas fa-tint',
            'color' => 'text-info',
            'codes' => ['T83511'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_cauti_t83518' => [
            'name' => 'การติดเชื้อจากสายสวนปัสสาวะชนิดอื่น (T83.518)',
            'icon' => 'fas fa-tint',
            'color' => 'text-info',
            'codes' => ['T83518'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_phlebitis' => [
            'name' => 'ภาวะหลอดเลือดดำอักเสบ (Phlebitis - I80.0-I80.9)',
            'icon' => 'fas fa-heart-pulse',
            'color' => 'text-danger',
            'codes' => ['I800', 'I801', 'I802', 'I803', 'I804', 'I805', 'I806', 'I807', 'I808', 'I809'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_vascular_catheter' => [
            'name' => 'สายสวนหลอดเลือด (Vascular Catheter - T80.211, T80.212, T80.218, T82.7)',
            'icon' => 'fas fa-syringe',
            'color' => 'text-warning',
            'codes' => ['T80211', 'T80212', 'T80218', 'T827'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_hap_y95' => [
            'name' => 'การติดเชื้อในโรงพยาบาล (HAP - Y95)',
            'icon' => 'fas fa-hospital',
            'color' => 'text-secondary',
            'codes' => ['Y95'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_capd_t8571_t856' => [
            'name' => 'การติดเชื้อที่สายล้างไตทางช่องท้อง (CAPD - T85.71, T85.6)',
            'icon' => 'fas fa-circle-nodes',
            'color' => 'text-primary',
            'codes' => ['T8571', 'T856'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ],
        'ic_hemodialysis' => [
            'name' => 'ติดเชื้อทางเส้นเลือดสำหรับการฟอกไต (T82.4, T82.7)',
            'icon' => 'fas fa-droplet',
            'color' => 'text-danger',
            'codes' => ['T824', 'T827'],
            'categories' => ['ic'],
            'group' => 'Infection Control',
            'group_icon' => 'fas fa-shield-virus',
            'group_color' => '#e0f2fe'
        ]
    ];

    public function report(Request $request, $type)
    {
        if (!isset($this->disease_configs[$type])) {
            abort(404);
        }

        $config = $this->disease_configs[$type];
        $category = $request->get('category', 'opd');

        return $this->get_diag_report($request, $type, $config, $category);
    }

    private function get_diag_report(Request $request, $type, $config, $category)
    {
        set_time_limit(300);

        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];
        $start_date_y = $dates['start_date_y'];

        $codes = $config['codes'];

        // Build Dynamic Stats Query based on Category
        if ($category === 'ipd') {
            $stats_base = "ipt i JOIN an_stat a ON a.an=i.an";
            $v_prefix = "a";
            $date_col = "i.dchdate";
            $hn_col = "i.hn";
            $vn_col = "i.an";
        } elseif ($category === 'refer' || $category === 'ic') {
            $stats_base = "referout r LEFT JOIN vn_stat v ON v.vn=r.vn LEFT JOIN an_stat a ON a.an=r.vn";
            $v_prefix = "r"; // Referout PDX
            $date_col = "r.refer_date";
            $hn_col = "r.hn";
            $vn_col = "r.vn";
        } else { // OPD
            $stats_base = "ovst o JOIN vn_stat v ON v.vn=o.vn";
            $v_prefix = "v";
            $date_col = "o.vstdate";
            $hn_col = "o.hn";
            $vn_col = "o.vn";
        }

        $params_set = [];
        $where_clauses = [];
        $is_ic_disease = in_array('ic', $config['categories'] ?? []);

        if ($category === 'refer' || $category === 'ic') {
            $diag_cols = $is_ic_disease ? ['pdx', 'dx0', 'dx1', 'dx2', 'dx3', 'dx4', 'dx5'] : ['pdx'];
            foreach ($diag_cols as $col) {
                foreach ($codes as $code) {
                    if ($col === 'pdx') {
                        $where_clauses[] = "r.pdx LIKE ?";
                        $params_set[] = $code . '%';
                    }
                    $where_clauses[] = "v.$col LIKE ?";
                    $params_set[] = $code . '%';
                    $where_clauses[] = "a.$col LIKE ?";
                    $params_set[] = $code . '%';
                }
            }
            $diag_where = "(" . implode(' OR ', $where_clauses) . ")";
        } else {
            // OPD/IPD
            $diag_cols = $is_ic_disease ? ['pdx', 'dx0', 'dx1', 'dx2', 'dx3', 'dx4', 'dx5'] : ['pdx'];
            foreach ($diag_cols as $col) {
                foreach ($codes as $code) {
                    $where_clauses[] = "$v_prefix.$col LIKE ?";
                    $params_set[] = $code . '%';
                }
            }
            $diag_where = "(" . implode(' OR ', $where_clauses) . ")";
        }

        // Monthly Stats
        $diag_month = DB::connection('hosxp')->select("
            SELECT CASE 
                WHEN MONTH($date_col)=10 THEN CONCAT('ต.ค. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=11 THEN CONCAT('พ.ย. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=12 THEN CONCAT('ธ.ค. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=1 THEN CONCAT('ม.ค. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=2 THEN CONCAT('ก.พ. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=3 THEN CONCAT('มี.ค. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=4 THEN CONCAT('เม.ย. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=5 THEN CONCAT('พ.ค. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=6 THEN CONCAT('มิ.ย. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=7 THEN CONCAT('ก.ค. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=8 THEN CONCAT('ส.ค. ',RIGHT(YEAR($date_col)+543,2))
                WHEN MONTH($date_col)=9 THEN CONCAT('ก.ย. ',RIGHT(YEAR($date_col)+543,2))
            END AS 'month', COUNT(DISTINCT $hn_col) AS 'hn', COUNT($vn_col) AS 'visit'
            FROM $stats_base
            WHERE $date_col BETWEEN ? AND ?
            AND $diag_where
            GROUP BY MONTH($date_col)
            ORDER BY YEAR($date_col), MONTH($date_col)", array_merge([$start_date, $end_date], $params_set));

        $diag_m = array_column($diag_month, 'month');
        $diag_visit_m = array_column($diag_month, 'visit');
        $diag_hn_m = array_column($diag_month, 'hn');

        // Yearly Trend
        $diag_year = DB::connection('hosxp')->select("
            SELECT IF(MONTH($date_col)>9,YEAR($date_col)+1,YEAR($date_col)) + 543 AS year_bud,
            COUNT(DISTINCT $hn_col) AS 'hn', COUNT($vn_col) AS 'visit'
            FROM $stats_base
            WHERE $date_col BETWEEN ? AND ?
            AND $diag_where
            GROUP BY year_bud
            ORDER BY year_bud", array_merge([$start_date_y, $end_date], $params_set));

        $diag_y = array_column($diag_year, 'year_bud');
        $diag_visit_y = array_column($diag_year, 'visit');
        $diag_hn_y = array_column($diag_year, 'hn');

        if ($category === 'ipd') {
            $diag_list = $this->fetch_ipd_list($start_date, $end_date, $codes);
        } elseif ($category === 'refer' || $category === 'ic') {
            $diag_list = $this->fetch_refer_list($start_date, $end_date, $codes);
        } else {
            $diag_list = $this->fetch_opd_list($start_date, $end_date, $codes);
        }

        return view('hosxp.diagnosis.report', compact(
            'type',
            'config',
            'category',
            'budget_year',
            'budget_year_select',
            'start_date',
            'end_date',
            'diag_m',
            'diag_visit_m',
            'diag_hn_m',
            'diag_y',
            'diag_visit_y',
            'diag_hn_y',
            'diag_list'
        ));
    }

    /**
     * Standard Date Range Resolver for all reports
     */
    private function resolveDateRange(Request $request)
    {
        $budget_year_select = \DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(7)
            ->get();

        $budget_year_now = \DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $budget_year = $request->budget_year ?: $budget_year_now;

        if ($request->start_date && $request->end_date) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $matched_year = \DB::table('budget_year')
                ->where('DATE_BEGIN', '<=', $start_date)
                ->where('DATE_END', '>=', $start_date)
                ->first();

            if ($matched_year) {
                $budget_year = $matched_year->LEAVE_YEAR_ID;
            }
        } else {
            $year_data = \DB::table('budget_year')
                ->where('LEAVE_YEAR_ID', $budget_year)
                ->first();

            if ($year_data) {
                $start_date = $year_data->DATE_BEGIN;
                $end_date = $year_data->DATE_END;
            } else {
                $start_date = ($budget_year - 543) . '-10-01';
                $end_date = ($budget_year - 542) . '-09-30';
            }
        }

        // Specifically for Diagnosis reports (5-year trend)
        $start_date_y = \DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year - 4)
            ->value('DATE_BEGIN');

        if (!$start_date_y) {
            $start_date_y = ($budget_year - 547) . '-10-01';
        }

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'budget_year' => $budget_year,
            'budget_year_select' => $budget_year_select,
            'start_date_y' => $start_date_y
        ];
    }

    private function fetch_opd_list($start_date, $end_date, $codes)
    {
        $params = [$start_date, $end_date];
        $diag_cols = ['pdx', 'dx0', 'dx1', 'dx2', 'dx3', 'dx4', 'dx5'];
        $where_clauses = [];
        foreach ($diag_cols as $col) {
            $col_likes = [];
            foreach ($codes as $code) {
                $col_likes[] = "v.$col LIKE ?";
                $params[] = $code . '%';
            }
            $where_clauses[] = "(" . implode(' OR ', $col_likes) . ")";
        }
        $diag_where = "(" . implode(' OR ', $where_clauses) . ")";

        return DB::connection('hosxp')->select("
            SELECT o.vn, o3.name AS ovstist, o.oqueue, o.vstdate, o.vsttime, o.hn, CONCAT(p.pname,p.fname,SPACE(1),p.lname) AS ptname,
            v.age_y, CONCAT(o.pttype,' [',p1.hipdata_code,']') AS pttype, o1.cc, v.pdx, od.dx,
            d.`name` AS dx_doctor, CONCAT(h.`name`,' [',r.pdx,']') AS refer
            FROM ovst o             
            LEFT JOIN opdscreen o1 ON o1.vn=o.vn
            LEFT JOIN vn_stat v ON v.vn=o.vn
            LEFT JOIN (
                SELECT vn, GROUP_CONCAT(icd10) AS dx FROM ovstdiag WHERE diagtype <> '1' GROUP BY vn
            ) od ON od.vn=o.vn
            LEFT JOIN ovstist o3 ON o3.ovstist=o.ovstist
            LEFT JOIN referout r ON r.vn=o.vn
            LEFT JOIN hospcode h ON h.hospcode=r.refer_hospcode
            LEFT JOIN patient p ON p.hn=o.hn
            LEFT JOIN pttype p1 ON p1.pttype=o.pttype
            LEFT JOIN doctor d ON d.`code`=v.dx_doctor
            WHERE o.vstdate BETWEEN ? AND ?
            AND $diag_where
            GROUP BY o.vn 
            ORDER BY o.vstdate DESC, o.vsttime DESC", $params);
    }

    private function fetch_ipd_list($start_date, $end_date, $codes)
    {
        $params = [$start_date, $end_date];
        $diag_cols = ['pdx', 'dx0', 'dx1', 'dx2', 'dx3', 'dx4', 'dx5'];
        $where_clauses = [];
        foreach ($diag_cols as $col) {
            $col_likes = [];
            foreach ($codes as $code) {
                $col_likes[] = "a.$col LIKE ?";
                $params[] = $code . '%';
            }
            $where_clauses[] = "(" . implode(' OR ', $col_likes) . ")";
        }
        $diag_where = "(" . implode(' OR ', $where_clauses) . ")";

        return DB::connection('hosxp')->select("
            SELECT i.an, i.hn, i.regdate, i.regtime, CONCAT(p.pname,p.fname,SPACE(1),p.lname) AS ptname,
            a.age_y, CONCAT(i.pttype,' [',p1.hipdata_code,']') AS pttype, i.prediag, a.pdx, id.dx,
            d.`name` AS dx_doctor, CONCAT(h.`name`,' [',r.pdx,']') AS refer, i.dchdate, i.dchtime 
            FROM ipt i
            LEFT JOIN an_stat a ON a.an=i.an
            LEFT JOIN (
                SELECT an, GROUP_CONCAT(icd10) AS dx FROM iptdiag WHERE diagtype <> '1' GROUP BY an
            ) id ON id.an=i.an
            LEFT JOIN referout r ON r.vn=i.an
            LEFT JOIN hospcode h ON h.hospcode=r.refer_hospcode
            LEFT JOIN patient p ON p.hn=i.hn
            LEFT JOIN pttype p1 ON p1.pttype=i.pttype
            LEFT JOIN doctor d ON d.`code`=a.dx_doctor
            WHERE i.dchdate BETWEEN ? AND ?   
            AND $diag_where
            GROUP BY i.an 
            ORDER BY i.dchdate DESC, i.dchtime DESC", $params);
    }

    private function fetch_refer_list($start_date, $end_date, $codes)
    {
        $params = [$start_date, $end_date];

        // List should use PDX + DX0-DX5
        $diag_cols = ['pdx', 'dx0', 'dx1', 'dx2', 'dx3', 'dx4', 'dx5'];
        $where_clauses = [];
        foreach ($diag_cols as $col) {
            foreach ($codes as $code) {
                if ($col === 'pdx') {
                    $where_clauses[] = "r.pdx LIKE ?";
                    $params[] = $code . '%';
                }
                $where_clauses[] = "v.$col LIKE ?";
                $params[] = $code . '%';
                $where_clauses[] = "a.$col LIKE ?";
                $params[] = $code . '%';
            }
        }
        $diag_where = "(" . implode(' OR ', $where_clauses) . ")";

        return DB::connection('hosxp')->select("
            SELECT o.hn, CONCAT(p.pname,p.fname,SPACE(1),p.lname) AS ptname, pmh.cc_persist_disease AS pmh,
            GROUP_CONCAT(DISTINCT c1.`name`) AS clinic, r.department, r.refer_point, o.vstdate, o.vsttime,
            IFNULL(v.pdx, a.pdx) AS pdx, r.refer_date, r.refer_time, r.pre_diagnosis, r.pdx AS pdx_refer, h.`name` AS refer_hos
            FROM referout r 
            LEFT JOIN ovst o ON o.vn=r.vn
            LEFT JOIN vn_stat v ON v.vn=r.vn 
            LEFT JOIN an_stat a ON a.an=r.vn
            LEFT JOIN clinicmember c ON c.hn=r.hn
            LEFT JOIN clinic c1 ON c1.clinic=c.clinic
            LEFT JOIN opd_ill_history pmh ON pmh.hn=r.hn
            LEFT JOIN patient p ON p.hn=r.hn
            LEFT JOIN hospcode h ON h.hospcode=r.refer_hospcode
            WHERE r.refer_date BETWEEN ? AND ?		 
            AND $diag_where
            GROUP BY r.vn							
            ORDER BY r.refer_date DESC, r.refer_time DESC", $params);
    }

}
