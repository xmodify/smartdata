<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HosxpSettingController extends Controller
{
    private array $categories = [
        'doctor' => [
            'key' => 'doctor',
            'title' => 'แพทย์และบุคลากร',
            'subtitle' => 'เลข ว., สภาวิชาชีพ, เลข 13 หลัก',
            'icon' => 'fa-user-doctor',
            'color' => '#2563eb',
        ],
        'nondrug' => [
            'key' => 'nondrug',
            'title' => 'ค่ารักษาพยาบาล',
            'subtitle' => 'หมวดรายได้, รหัสมาตรฐาน ADP',
            'icon' => 'fa-file-invoice-dollar',
            'color' => '#0891b2',
        ],
        'drug' => [
            'key' => 'drug',
            'title' => 'ข้อมูลยา',
            'subtitle' => 'รายการยา, รหัส 24 หลัก, TMT, ราคา',
            'icon' => 'fa-pills',
            'color' => '#7c3aed',
        ],
        'lab' => [
            'key' => 'lab',
            'title' => 'ข้อมูล Lab',
            'subtitle' => 'รายการตรวจ, TMLT, LOINC, ราคา',
            'icon' => 'fa-flask-vial',
            'color' => '#059669',
        ],
        'pttype' => [
            'key' => 'pttype',
            'title' => 'สิทธิการรักษา',
            'subtitle' => 'ตาราง pttype, ตรวจสอบ PROVIS / สปสช.',
            'icon' => 'fa-id-card-clip',
            'color' => '#4f46e5',
        ],
        'icd10' => [
            'key' => 'icd10',
            'title' => 'รหัสโรค ICD-10',
            'subtitle' => 'รหัส icd101, เกณฑ์ สกส./สปสช.',
            'icon' => 'fa-virus',
            'color' => '#dc2626',
        ],
        'icd9' => [
            'key' => 'icd9',
            'title' => 'รหัสหัตถการ ICD-9',
            'subtitle' => 'รหัส icd9cm1, ศคส. / มาตรฐาน',
            'icon' => 'fa-scissors',
            'color' => '#ea580c',
        ],
    ];

    public function index(Request $request)
    {
        $activeTab = $request->input('tab', 'doctor');
        if (!isset($this->categories[$activeTab])) {
            $activeTab = 'doctor';
        }

        $db = DB::connection('hosxp');

        $counts = [
            'doctor' => (int) $db->table('doctor')->where('active', 'Y')->count(),
            'nondrug' => (int) $db->table('nondrugitems')->where('istatus', 'Y')->count(),
            'drug' => (int) $db->table('drugitems')->where('istatus', 'Y')->count(),
            'lab' => (int) $db->table('lab_items')->where(function ($q) {
                $q->where('active_status', 'Y')->orWhereNull('active_status');
            })->count(),
            'pttype' => (int) $db->table('pttype')->count(),
            'icd10' => (int) $db->table('icd101')->count(),
            'icd9' => (int) $db->table('icd9cm1')->count(),
        ];

        return view('hosxp.setting.index', [
            'categories' => $this->categories,
            'activeTab' => $activeTab,
            'counts' => $counts,
        ]);
    }

    public function data(Request $request, string $tab)
    {
        if (!isset($this->categories[$tab])) {
            return response()->json(['error' => 'Invalid tab'], 400);
        }

        $filter = $request->input('filter', 'card1');
        $search = trim((string) $request->input('search', ''));
        $perPage = min(100, max(10, (int) $request->input('per_page', 15)));
        $page = max(1, (int) $request->input('page', 1));

        $payload = $this->buildTabData($tab, $filter, $search, $perPage, $page);

        return response()->json($payload);
    }

    public function export(Request $request, string $tab)
    {
        if (!isset($this->categories[$tab])) {
            abort(404);
        }

        $filter = $request->input('filter', 'card1');
        $search = trim((string) $request->input('search', ''));
        $payload = $this->buildTabData($tab, $filter, $search, 5000, 1);

        $filename = "hosxp_{$tab}_{$filter}_" . date('Ymd_His') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function () use ($payload) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $payload['export_headers'] ?? $payload['columns']);
            foreach ($payload['rows'] as $row) {
                $line = [];
                foreach ($payload['export_keys'] ?? array_keys($payload['rows'][0] ?? []) as $k) {
                    $line[] = $row[$k] ?? '';
                }
                fputcsv($out, $line);
            }
            fclose($out);
        }, $filename, $headers);
    }

    private function buildTabData(string $tab, string $filter, string $search, int $perPage, int $page): array
    {
        $db = DB::connection('hosxp');

        return match ($tab) {
            'doctor' => $this->queryDoctor($db, $filter, $search, $perPage, $page),
            'nondrug' => $this->queryNondrug($db, $filter, $search, $perPage, $page),
            'drug' => $this->queryDrug($db, $filter, $search, $perPage, $page),
            'lab' => $this->queryLab($db, $filter, $search, $perPage, $page),
            'pttype' => $this->queryPttype($db, $filter, $search, $perPage, $page),
            'icd10' => $this->queryIcd10($db, $filter, $search, $perPage, $page),
            'icd9' => $this->queryIcd9($db, $filter, $search, $perPage, $page),
        };
    }

    private function paginateQuery($query, int $perPage, int $page): array
    {
        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $items = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        return [$items, $total, $page, $lastPage];
    }

    private function queryDoctor($db, string $filter, string $search, int $perPage, int $page): array
    {
        $activeCount = (int) $db->table('doctor')->where('active', 'Y')->count();
        $inactiveCount = (int) $db->table('doctor')->where(fn($q) => $q->where('active', '!=', 'Y')->orWhereNull('active'))->count();
        $badLicenseCount = (int) $db->table('doctor')->where('active', 'Y')
            ->where(fn($q) => $q->whereNull('licenseno')->orWhere('licenseno', '')->orWhere('licenseno', 'like', '-%'))->count();
        $badCidCount = (int) $db->table('doctor')->where('active', 'Y')
            ->where(fn($q) => $q->whereNull('cid')->orWhereRaw("LENGTH(TRIM(cid)) != 13")
                ->orWhereRaw("TRIM(cid) NOT REGEXP '^[0-9]{13}$'"))->count();

        $q = $db->table('doctor')
            ->select('code', 'name', 'jobposition', 'licenseno', 'cid', 'council_code', 'sex', 'birth_date', 'active');

        match ($filter) {
            'card2' => $q->where(fn($w) => $w->where('active', '!=', 'Y')->orWhereNull('active')),
            'card3' => $q->where('active', 'Y')->where(fn($w) => $w->whereNull('licenseno')->orWhere('licenseno', '')->orWhere('licenseno', 'like', '-%')),
            'card4' => $q->where('active', 'Y')->where(fn($w) => $w->whereNull('cid')->orWhereRaw("LENGTH(TRIM(cid)) != 13")->orWhereRaw("TRIM(cid) NOT REGEXP '^[0-9]{13}$'")),
            default => $q->where('active', 'Y'),
        };

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('licenseno', 'like', "%{$search}%")
                  ->orWhere('cid', 'like', "%{$search}%")
                  ->orWhere('jobposition', 'like', "%{$search}%");
            });
        }

        $q->orderBy('code');
        [$items, $total, $page, $lastPage] = $this->paginateQuery($q, $perPage, $page);

        $councilMap = [
            '01' => '01 - แพทยสภา',
            '02' => '02 - สภาการพยาบาล',
            '03' => '03 - สภาเภสัชกรรม',
            '04' => '04 - ทันตแพทยสภา',
            '05' => '05 - สภากายภาพบำบัด',
            '06' => '06 - สภาเทคนิคการแพทย์',
        ];

        $rows = $items->map(function ($r) use ($councilMap) {
            $cid = trim((string) $r->cid);
            $cidValid = preg_match('/^[0-9]{13}$/', $cid) === 1;
            $lic = trim((string) $r->licenseno);
            $licValid = $lic !== '' && !str_starts_with($lic, '-');
            $sexLabel = match ((string) $r->sex) {
                '1' => 'ชาย',
                '2' => 'หญิง',
                default => '-',
            };
            $birthThai = $r->birth_date ? $this->formatThaiDate($r->birth_date) : '-';
            $auditOk = $cidValid && $licValid;

            return [
                'code' => $r->code,
                'name' => $r->name,
                'jobposition' => $r->jobposition ?: '-',
                'licenseno' => $lic !== '' ? $lic : '-',
                'lic_valid' => $licValid,
                'cid' => $cid !== '' ? $cid : '-',
                'cid_valid' => $cidValid,
                'council' => $councilMap[$r->council_code] ?? ($r->council_code ?: '-'),
                'sex' => $sexLabel,
                'birth_date' => $birthThai,
                'active' => $r->active === 'Y' ? 'Active' : 'Inactive',
                'audit_ok' => $auditOk,
                'audit_note' => $auditOk ? 'สมบูรณ์' : (!$cidValid ? 'CID ไม่ครบ 13 หลัก' : 'ไม่มีเลข ว.'),
            ];
        })->values()->all();

        $titles = [
            'card1' => 'รายชื่อแพทย์และบุคลากรที่เปิดใช้งาน',
            'card2' => 'รายชื่อแพทย์และบุคลากรที่ปิดใช้งาน',
            'card3' => 'รายการที่เลขใบประกอบวิชาชีพไม่ถูกต้อง/ว่าง',
            'card4' => 'รายการที่เลขบัตรประชาชน 13 หลักไม่ถูกต้อง',
        ];

        return [
            'cards' => [
                ['key' => 'card1', 'label' => 'แพทย์ที่เปิดใช้งาน', 'count' => $activeCount, 'unit' => 'ราย', 'icon' => 'fa-user-check', 'tone' => 'success'],
                ['key' => 'card2', 'label' => 'แพทย์ที่ปิดใช้งาน', 'count' => $inactiveCount, 'unit' => 'ราย', 'icon' => 'fa-user-xmark', 'tone' => 'secondary'],
                ['key' => 'card3', 'label' => 'เลขใบประกอบฯ ไม่ถูกต้อง', 'count' => $badLicenseCount, 'unit' => 'ราย', 'icon' => 'fa-id-badge', 'tone' => 'danger'],
                ['key' => 'card4', 'label' => 'เลขบัตรประชาชนไม่ถูกต้อง', 'count' => $badCidCount, 'unit' => 'ราย', 'icon' => 'fa-address-card', 'tone' => 'warning'],
            ],
            'table_title' => $titles[$filter] ?? $titles['card1'],
            'columns' => ['รหัส', 'ชื่อ - สกุล', 'ตำแหน่ง', 'เลขใบประกอบวิชาชีพ', 'เลขบัตรประชาชน (13 หลัก)', 'สภาวิชาชีพ', 'เพศ', 'วันเกิด', 'สถานะ', 'ผลการตรวจสอบ'],
            'export_keys' => ['code', 'name', 'jobposition', 'licenseno', 'cid', 'council', 'sex', 'birth_date', 'active', 'audit_note'],
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
        ];
    }

    private function queryNondrug($db, string $filter, string $search, int $perPage, int $page): array
    {
        $activeCount = (int) $db->table('nondrugitems')->where('istatus', 'Y')->count();
        $inactiveCount = (int) $db->table('nondrugitems')->where(fn($q) => $q->where('istatus', '!=', 'Y')->orWhereNull('istatus'))->count();
        $noAdpCount = (int) $db->table('nondrugitems')->where('istatus', 'Y')->where(fn($q) => $q->whereNull('nhso_adp_code')->orWhere('nhso_adp_code', ''))->count();
        $noIncomeCount = (int) $db->table('nondrugitems')->where('istatus', 'Y')->where(fn($q) => $q->whereNull('income')->orWhere('income', ''))->count();

        $q = $db->table('nondrugitems as n')
            ->leftJoin('income as inc', 'n.income', '=', 'inc.income')
            ->select('n.icode', 'n.name', 'n.price', 'n.ipd_price', 'n.income', 'inc.name as income_name', 'n.nhso_adp_code', 'n.billcode', 'n.istatus');

        match ($filter) {
            'card2' => $q->where(fn($w) => $w->where('n.istatus', '!=', 'Y')->orWhereNull('n.istatus')),
            'card3' => $q->where('n.istatus', 'Y')->where(fn($w) => $w->whereNull('n.nhso_adp_code')->orWhere('n.nhso_adp_code', '')),
            'card4' => $q->where('n.istatus', 'Y')->where(fn($w) => $w->whereNull('n.income')->orWhere('n.income', '')),
            default => $q->where('n.istatus', 'Y'),
        };

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('n.icode', 'like', "%{$search}%")
                  ->orWhere('n.name', 'like', "%{$search}%")
                  ->orWhere('n.nhso_adp_code', 'like', "%{$search}%")
                  ->orWhere('n.billcode', 'like', "%{$search}%");
            });
        }

        $q->orderBy('n.icode');
        [$items, $total, $page, $lastPage] = $this->paginateQuery($q, $perPage, $page);

        $rows = $items->map(function ($r) {
            $adpOk = !empty(trim((string) $r->nhso_adp_code));
            $incOk = !empty(trim((string) $r->income));
            $auditOk = $adpOk && $incOk;

            return [
                'code' => $r->icode,
                'name' => $r->name,
                'income' => $r->income ? ($r->income . ' - ' . ($r->income_name ?: '-')) : '-',
                'adp' => $r->nhso_adp_code ?: '-',
                'billcode' => $r->billcode ?: '-',
                'price' => number_format((float) $r->price, 2),
                'ipd_price' => number_format((float) $r->ipd_price, 2),
                'active' => $r->istatus === 'Y' ? 'Active' : 'Inactive',
                'audit_ok' => $auditOk,
                'audit_note' => $auditOk ? 'สมบูรณ์' : (!$incOk ? 'ไม่มีหมวดรายได้' : 'ไม่มีรหัส ADP'),
            ];
        })->values()->all();

        return [
            'cards' => [
                ['key' => 'card1', 'label' => 'รายการเปิดใช้งาน', 'count' => $activeCount, 'unit' => 'รายการ', 'icon' => 'fa-check-circle', 'tone' => 'success'],
                ['key' => 'card2', 'label' => 'รายการปิดใช้งาน', 'count' => $inactiveCount, 'unit' => 'รายการ', 'icon' => 'fa-ban', 'tone' => 'secondary'],
                ['key' => 'card3', 'label' => 'ไม่มีรหัส ADP สปสช.', 'count' => $noAdpCount, 'unit' => 'รายการ', 'icon' => 'fa-triangle-exclamation', 'tone' => 'danger'],
                ['key' => 'card4', 'label' => 'ไม่มีหมวดรายได้', 'count' => $noIncomeCount, 'unit' => 'รายการ', 'icon' => 'fa-folder-open', 'tone' => 'warning'],
            ],
            'table_title' => 'รายการค่ารักษาพยาบาลและหัตถการ',
            'columns' => ['รหัส (icode)', 'ชื่อรายการ', 'หมวดรายได้', 'รหัส ADP', 'Billcode', 'ราคา OPD', 'ราคา IPD', 'สถานะ', 'ผลการตรวจสอบ'],
            'export_keys' => ['code', 'name', 'income', 'adp', 'billcode', 'price', 'ipd_price', 'active', 'audit_note'],
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
        ];
    }

    private function queryDrug($db, string $filter, string $search, int $perPage, int $page): array
    {
        $activeCount = (int) $db->table('drugitems')->where('istatus', 'Y')->count();
        $inactiveCount = (int) $db->table('drugitems')->where(fn($q) => $q->where('istatus', '!=', 'Y')->orWhereNull('istatus'))->count();
        $noDidCount = (int) $db->table('drugitems')->where('istatus', 'Y')->where(fn($q) => $q->whereNull('did')->orWhereRaw("LENGTH(TRIM(did)) != 24"))->count();
        $noTmtCount = (int) $db->table('drugitems')->where('istatus', 'Y')->where(fn($q) => $q->whereNull('tmt_tp_code')->orWhere('tmt_tp_code', ''))->count();

        $q = $db->table('drugitems')
            ->select('icode', 'name', 'strength', 'units', 'did', 'tmt_tp_code', 'drugaccount', 'unitprice', 'unitcost', 'istatus');

        match ($filter) {
            'card2' => $q->where(fn($w) => $w->where('istatus', '!=', 'Y')->orWhereNull('istatus')),
            'card3' => $q->where('istatus', 'Y')->where(fn($w) => $w->whereNull('did')->orWhereRaw("LENGTH(TRIM(did)) != 24")),
            'card4' => $q->where('istatus', 'Y')->where(fn($w) => $w->whereNull('tmt_tp_code')->orWhere('tmt_tp_code', '')),
            default => $q->where('istatus', 'Y'),
        };

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('icode', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('did', 'like', "%{$search}%")
                  ->orWhere('tmt_tp_code', 'like', "%{$search}%");
            });
        }

        $q->orderBy('icode');
        [$items, $total, $page, $lastPage] = $this->paginateQuery($q, $perPage, $page);

        $rows = $items->map(function ($r) {
            $did = trim((string) $r->did);
            $didOk = strlen($did) === 24;
            $tmtOk = !empty(trim((string) $r->tmt_tp_code));
            $auditOk = $didOk && $tmtOk;

            return [
                'code' => $r->icode,
                'name' => trim($r->name . ' ' . ($r->strength ?? '')),
                'units' => $r->units ?: '-',
                'did' => $did !== '' ? $did : '-',
                'tmt' => $r->tmt_tp_code ?: '-',
                'account' => $r->drugaccount ?: '-',
                'unitcost' => number_format((float) $r->unitcost, 2),
                'unitprice' => number_format((float) $r->unitprice, 2),
                'active' => $r->istatus === 'Y' ? 'Active' : 'Inactive',
                'audit_ok' => $auditOk,
                'audit_note' => $auditOk ? 'สมบูรณ์' : (!$didOk ? 'รหัส 24 หลักไม่ครบ' : 'ไม่มีรหัส TMT'),
            ];
        })->values()->all();

        return [
            'cards' => [
                ['key' => 'card1', 'label' => 'ยาที่เปิดใช้งาน', 'count' => $activeCount, 'unit' => 'รายการ', 'icon' => 'fa-pills', 'tone' => 'success'],
                ['key' => 'card2', 'label' => 'ยาที่ปิดใช้งาน', 'count' => $inactiveCount, 'unit' => 'รายการ', 'icon' => 'fa-ban', 'tone' => 'secondary'],
                ['key' => 'card3', 'label' => 'รหัส 24 หลักไม่สมบูรณ์', 'count' => $noDidCount, 'unit' => 'รายการ', 'icon' => 'fa-barcode', 'tone' => 'danger'],
                ['key' => 'card4', 'label' => 'ไม่มีรหัส TMT (TP)', 'count' => $noTmtCount, 'unit' => 'รายการ', 'icon' => 'fa-triangle-exclamation', 'tone' => 'warning'],
            ],
            'table_title' => 'รายการข้อมูลยาในคลัง HOSxP',
            'columns' => ['รหัส (icode)', 'ชื่อยา / ความแรง', 'หน่วย', 'รหัสยา 24 หลัก (DID)', 'รหัส TMT', 'บัญชี', 'ราคาทุน', 'ราคาขาย', 'สถานะ', 'ผลการตรวจสอบ'],
            'export_keys' => ['code', 'name', 'units', 'did', 'tmt', 'account', 'unitcost', 'unitprice', 'active', 'audit_note'],
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
        ];
    }

    private function queryLab($db, string $filter, string $search, int $perPage, int $page): array
    {
        $activeCount = (int) $db->table('lab_items')->where(fn($q) => $q->where('active_status', 'Y')->orWhereNull('active_status'))->count();
        $inactiveCount = (int) $db->table('lab_items')->where('active_status', '!=', 'Y')->count();
        $noTmltCount = (int) $db->table('lab_items')->where(fn($q) => $q->where('active_status', 'Y')->orWhereNull('active_status'))->where(fn($q) => $q->whereNull('tmlt_code')->orWhere('tmlt_code', ''))->count();
        $noLoincCount = (int) $db->table('lab_items')->where(fn($q) => $q->where('active_status', 'Y')->orWhereNull('active_status'))->where(fn($q) => $q->whereNull('loinc_code')->orWhere('loinc_code', ''))->count();

        $q = $db->table('lab_items')
            ->select('lab_items_code', 'lab_items_name', 'lab_items_unit', 'icode', 'tmlt_code', 'loinc_code', 'service_price', 'active_status');

        match ($filter) {
            'card2' => $q->where('active_status', '!=', 'Y'),
            'card3' => $q->where(fn($w) => $w->where('active_status', 'Y')->orWhereNull('active_status'))->where(fn($w) => $w->whereNull('tmlt_code')->orWhere('tmlt_code', '')),
            'card4' => $q->where(fn($w) => $w->where('active_status', 'Y')->orWhereNull('active_status'))->where(fn($w) => $w->whereNull('loinc_code')->orWhere('loinc_code', '')),
            default => $q->where(fn($w) => $w->where('active_status', 'Y')->orWhereNull('active_status')),
        };

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('lab_items_code', 'like', "%{$search}%")
                  ->orWhere('lab_items_name', 'like', "%{$search}%")
                  ->orWhere('icode', 'like', "%{$search}%")
                  ->orWhere('tmlt_code', 'like', "%{$search}%")
                  ->orWhere('loinc_code', 'like', "%{$search}%");
            });
        }

        $q->orderBy('lab_items_code');
        [$items, $total, $page, $lastPage] = $this->paginateQuery($q, $perPage, $page);

        $rows = $items->map(function ($r) {
            $tmltOk = !empty(trim((string) $r->tmlt_code));
            $loincOk = !empty(trim((string) $r->loinc_code));
            $auditOk = $tmltOk && $loincOk;

            return [
                'code' => $r->lab_items_code,
                'name' => $r->lab_items_name,
                'unit' => $r->lab_items_unit ?: '-',
                'icode' => $r->icode ?: '-',
                'tmlt' => $r->tmlt_code ?: '-',
                'loinc' => $r->loinc_code ?: '-',
                'price' => number_format((float) $r->service_price, 2),
                'active' => ($r->active_status === 'Y' || $r->active_status === null) ? 'Active' : 'Inactive',
                'audit_ok' => $auditOk,
                'audit_note' => $auditOk ? 'สมบูรณ์' : (!$tmltOk ? 'ไม่มี TMLT' : 'ไม่มี LOINC'),
            ];
        })->values()->all();

        return [
            'cards' => [
                ['key' => 'card1', 'label' => 'รายการ Lab เปิดใช้งาน', 'count' => $activeCount, 'unit' => 'รายการ', 'icon' => 'fa-flask', 'tone' => 'success'],
                ['key' => 'card2', 'label' => 'รายการ Lab ปิดใช้งาน', 'count' => $inactiveCount, 'unit' => 'รายการ', 'icon' => 'fa-ban', 'tone' => 'secondary'],
                ['key' => 'card3', 'label' => 'ไม่มีรหัส TMLT', 'count' => $noTmltCount, 'unit' => 'รายการ', 'icon' => 'fa-triangle-exclamation', 'tone' => 'danger'],
                ['key' => 'card4', 'label' => 'ไม่มีรหัส LOINC', 'count' => $noLoincCount, 'unit' => 'รายการ', 'icon' => 'fa-circle-exclamation', 'tone' => 'warning'],
            ],
            'table_title' => 'รายการข้อมูล Lab (lab_items)',
            'columns' => ['รหัส Lab', 'ชื่อรายการตรวจ', 'หน่วย', 'icode', 'รหัส TMLT', 'รหัส LOINC', 'ราคา', 'สถานะ', 'ผลการตรวจสอบ'],
            'export_keys' => ['code', 'name', 'unit', 'icode', 'tmlt', 'loinc', 'price', 'active', 'audit_note'],
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
        ];
    }

    private function queryPttype($db, string $filter, string $search, int $perPage, int $page): array
    {
        $totalCount = (int) $db->table('pttype')->count();
        $activeCount = (int) $db->table('pttype')->where('isuse', 'Y')->count();
        $noNhsoCount = (int) $db->table('pttype')->where('isuse', 'Y')->where(fn($q) => $q->whereNull('nhso_code')->orWhere('nhso_code', ''))->count();
        $noProvisCount = (int) $db->table('pttype as p')
            ->leftJoin('provis_instype as pr', 'p.nhso_code', '=', 'pr.code')
            ->where('p.isuse', 'Y')
            ->whereNull('pr.code')->count();

        $q = $db->table('pttype as p')
            ->leftJoin('provis_instype as pr', 'p.nhso_code', '=', 'pr.code')
            ->leftJoin('pttype_price_group as pg', 'p.pttype_price_group_id', '=', 'pg.pttype_price_group_id')
            ->select('p.pttype', 'p.name', 'p.pcode', 'p.nhso_code', 'p.hipdata_code', 'pg.pttype_price_group_name', 'pr.code as provis_code', 'p.isuse');

        match ($filter) {
            'card2' => $q->where('p.isuse', 'Y'),
            'card3' => $q->where('p.isuse', 'Y')->where(fn($w) => $w->whereNull('p.nhso_code')->orWhere('p.nhso_code', '')),
            'card4' => $q->where('p.isuse', 'Y')->whereNull('pr.code'),
            default => null,
        };

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('p.pttype', 'like', "%{$search}%")
                  ->orWhere('p.name', 'like', "%{$search}%")
                  ->orWhere('p.nhso_code', 'like', "%{$search}%")
                  ->orWhere('p.pcode', 'like', "%{$search}%");
            });
        }

        $q->orderBy('p.pttype');
        [$items, $total, $page, $lastPage] = $this->paginateQuery($q, $perPage, $page);

        $rows = $items->map(function ($r) {
            $nhsoOk = !empty(trim((string) $r->nhso_code));
            $provisOk = !empty($r->provis_code);
            $auditOk = $nhsoOk && $provisOk;

            return [
                'code' => $r->pttype,
                'name' => $r->name,
                'pcode' => $r->pcode ?: '-',
                'nhso_code' => $r->nhso_code ?: '-',
                'hipdata_code' => $r->hipdata_code ?: '-',
                'price_group' => $r->pttype_price_group_name ?: '-',
                'provis' => $provisOk ? 'ตรงกับ PROVIS' : 'ไม่พบใน PROVIS',
                'active' => $r->isuse === 'Y' ? 'Active' : 'Inactive',
                'audit_ok' => $auditOk,
                'audit_note' => $auditOk ? 'สมบูรณ์' : (!$nhsoOk ? 'ไม่มีรหัส สปสช.' : 'ไม่มีใน PROVIS'),
            ];
        })->values()->all();

        return [
            'cards' => [
                ['key' => 'card1', 'label' => 'สิทธิทั้งหมด', 'count' => $totalCount, 'unit' => 'สิทธิ', 'icon' => 'fa-list', 'tone' => 'primary'],
                ['key' => 'card2', 'label' => 'สิทธิที่เปิดใช้งาน', 'count' => $activeCount, 'unit' => 'สิทธิ', 'icon' => 'fa-check-circle', 'tone' => 'success'],
                ['key' => 'card3', 'label' => 'ไม่มีรหัส NHSO', 'count' => $noNhsoCount, 'unit' => 'สิทธิ', 'icon' => 'fa-triangle-exclamation', 'tone' => 'danger'],
                ['key' => 'card4', 'label' => 'ไม่พบใน PROVIS', 'count' => $noProvisCount, 'unit' => 'สิทธิ', 'icon' => 'fa-circle-exclamation', 'tone' => 'warning'],
            ],
            'table_title' => 'สิทธิการรักษาใน HOSxP (pttype)',
            'columns' => ['รหัสสิทธิ', 'ชื่อสิทธิการรักษา', 'Pcode', 'NHSO Code', 'Hipdata', 'กลุ่มราคา', 'PROVIS', 'สถานะ', 'ผลการตรวจสอบ'],
            'export_keys' => ['code', 'name', 'pcode', 'nhso_code', 'hipdata_code', 'price_group', 'provis', 'active', 'audit_note'],
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
        ];
    }

    private function queryIcd10($db, string $filter, string $search, int $perPage, int $page): array
    {
        $totalCount = (int) $db->table('icd101')->count();
        $activeCount = (int) $db->table('icd101')->where(fn($q) => $q->where('active_status', 'Y')->orWhereNull('active_status'))->count();
        $ipdValidCount = (int) $db->table('icd101')->where('ipd_valid', 'Y')->count();
        $noCompatCount = (int) $db->table('icd101')->where(fn($q) => $q->where('icd10compat', '!=', 'Y')->orWhereNull('icd10compat'))->count();

        $q = $db->table('icd101')->select('code', 'name', 'tname', 'ipd_valid', 'icd10compat', 'active_status');

        match ($filter) {
            'card2' => $q->where(fn($w) => $w->where('active_status', 'Y')->orWhereNull('active_status')),
            'card3' => $q->where('ipd_valid', 'Y'),
            'card4' => $q->where(fn($w) => $w->where('icd10compat', '!=', 'Y')->orWhereNull('icd10compat')),
            default => null,
        };

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('tname', 'like', "%{$search}%");
            });
        }

        $q->orderBy('code');
        [$items, $total, $page, $lastPage] = $this->paginateQuery($q, $perPage, $page);

        $rows = $items->map(function ($r) {
            $compatOk = $r->icd10compat === 'Y';
            return [
                'code' => $r->code,
                'name' => $r->name,
                'tname' => $r->tname ?: '-',
                'ipd_valid' => $r->ipd_valid === 'Y' ? 'Y' : '-',
                'compat' => $compatOk ? 'Y' : 'N',
                'active' => ($r->active_status === 'Y' || $r->active_status === null) ? 'Active' : 'Inactive',
                'audit_ok' => $compatOk,
                'audit_note' => $compatOk ? 'ผ่านเกณฑ์' : 'ไม่ผ่าน Compat',
            ];
        })->values()->all();

        return [
            'cards' => [
                ['key' => 'card1', 'label' => 'รหัสโรคทั้งหมด', 'count' => $totalCount, 'unit' => 'รหัส', 'icon' => 'fa-virus', 'tone' => 'primary'],
                ['key' => 'card2', 'label' => 'เปิดใช้งาน', 'count' => $activeCount, 'unit' => 'รหัส', 'icon' => 'fa-check-circle', 'tone' => 'success'],
                ['key' => 'card3', 'label' => 'ใช้เป็น Principal Dx IPD', 'count' => $ipdValidCount, 'unit' => 'รหัส', 'icon' => 'fa-bed-pulse', 'tone' => 'info'],
                ['key' => 'card4', 'label' => 'ไม่ผ่านเกณฑ์ Compat', 'count' => $noCompatCount, 'unit' => 'รหัส', 'icon' => 'fa-triangle-exclamation', 'tone' => 'warning'],
            ],
            'table_title' => 'รหัสโรคมาตรฐาน ICD-10 (icd101)',
            'columns' => ['รหัสโรค', 'ชื่อโรค (EN)', 'ชื่อโรค (TH)', 'IPD Valid', 'Compat', 'สถานะ', 'ผลการตรวจสอบ'],
            'export_keys' => ['code', 'name', 'tname', 'ipd_valid', 'compat', 'active', 'audit_note'],
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
        ];
    }

    private function queryIcd9($db, string $filter, string $search, int $perPage, int $page): array
    {
        $totalCount = (int) $db->table('icd9cm1')->count();
        $activeCount = (int) $db->table('icd9cm1')->where(fn($q) => $q->where('active_status', 'Y')->orWhereNull('active_status'))->count();
        $exportCount = (int) $db->table('icd9cm1')->where('export_proced', 'Y')->count();
        $noExportCount = (int) $db->table('icd9cm1')->where(fn($q) => $q->where('export_proced', '!=', 'Y')->orWhereNull('export_proced'))->count();

        $q = $db->table('icd9cm1')->select('code', 'name', 'export_proced', 'active_status');

        match ($filter) {
            'card2' => $q->where(fn($w) => $w->where('active_status', 'Y')->orWhereNull('active_status')),
            'card3' => $q->where('export_proced', 'Y'),
            'card4' => $q->where(fn($w) => $w->where('export_proced', '!=', 'Y')->orWhereNull('export_proced')),
            default => null,
        };

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $q->orderBy('code');
        [$items, $total, $page, $lastPage] = $this->paginateQuery($q, $perPage, $page);

        $rows = $items->map(function ($r) {
            $expOk = $r->export_proced === 'Y';
            return [
                'code' => $r->code,
                'name' => $r->name,
                'export_proced' => $expOk ? 'Y' : 'N',
                'active' => ($r->active_status === 'Y' || $r->active_status === null) ? 'Active' : 'Inactive',
                'audit_ok' => $expOk,
                'audit_note' => $expOk ? 'ส่งออกหัตถการ' : 'ไม่ส่งออก',
            ];
        })->values()->all();

        return [
            'cards' => [
                ['key' => 'card1', 'label' => 'รหัสหัตถการทั้งหมด', 'count' => $totalCount, 'unit' => 'รหัส', 'icon' => 'fa-scissors', 'tone' => 'primary'],
                ['key' => 'card2', 'label' => 'เปิดใช้งาน', 'count' => $activeCount, 'unit' => 'รหัส', 'icon' => 'fa-check-circle', 'tone' => 'success'],
                ['key' => 'card3', 'label' => 'ส่งออก 43 แฟ้ม/เคลม', 'count' => $exportCount, 'unit' => 'รหัส', 'icon' => 'fa-file-export', 'tone' => 'info'],
                ['key' => 'card4', 'label' => 'ไม่ตั้งค่าส่งออก', 'count' => $noExportCount, 'unit' => 'รหัส', 'icon' => 'fa-triangle-exclamation', 'tone' => 'warning'],
            ],
            'table_title' => 'รหัสหัตถการมาตรฐาน ICD-9-CM (icd9cm1)',
            'columns' => ['รหัสหัตถการ', 'ชื่อหัตถการ', 'Export Proced', 'สถานะ', 'ผลการตรวจสอบ'],
            'export_keys' => ['code', 'name', 'export_proced', 'active', 'audit_note'],
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
        ];
    }

    private function formatThaiDate(string $dateStr): string
    {
        try {
            $dt = \Carbon\Carbon::parse($dateStr);
            $months = [1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'];
            return $dt->day . ' ' . ($months[$dt->month] ?? $dt->month) . ' ' . ($dt->year + 543);
        } catch (\Throwable $e) {
            return $dateStr;
        }
    }
}
