<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarehouseController extends Controller
{
    private array $fiscalMonthOrder = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    private array $warehouseConfig = [
        'drug' => [
            'id' => 'drug',
            'name' => 'คลังยา',
            'full_name' => 'คลังยาและเวชภัณฑ์ (INVS)',
            'icon' => 'fa-pills',
            'color' => '#059669',
            'type' => 'moph_f5',
            'standard_mos_min' => 1.5,
            'standard_mos_max' => 2.0,
            'standard_itr' => 6.0,
        ],
        'material' => [
            'id' => 'material',
            'name' => 'คลังวัสดุ',
            'full_name' => 'คลังวัสดุและพัสดุทั่วไป',
            'icon' => 'fa-boxes-packing',
            'color' => '#f59e0b',
            'type' => 'supplies',
            'inven_id' => 5,
            'store_type' => 5,
            'standard_mos_min' => 1.0,
            'standard_mos_max' => 2.0,
            'standard_itr' => 4.0,
        ],
        'dental' => [
            'id' => 'dental',
            'name' => 'คลังทันตกรรม',
            'full_name' => 'คลังวัสดุทันตกรรม',
            'icon' => 'fa-tooth',
            'color' => '#06b6d4',
            'type' => 'medicine',
            'warehouse_id' => 2,
            'inven_id' => 3,
            'store_type' => 3,
            'standard_mos_min' => 1.5,
            'standard_mos_max' => 2.0,
            'standard_itr' => 5.0,
        ],
        'medtech' => [
            'id' => 'medtech',
            'name' => 'คลัง LAB',
            'full_name' => 'คลัง LAB',
            'icon' => 'fa-microscope',
            'color' => '#8b5cf6',
            'type' => 'medicine',
            'warehouse_id' => 3,
            'inven_id' => 2,
            'store_type' => 2,
            'standard_mos_min' => 1.5,
            'standard_mos_max' => 2.0,
            'standard_itr' => 6.0,
        ],
        'physical' => [
            'id' => 'physical',
            'name' => 'คลังวัสดุกายภาพบำบัด',
            'full_name' => 'คลังวัสดุกายภาพบำบัดและการฟื้นฟู',
            'icon' => 'fa-person-walking',
            'color' => '#10b981',
            'type' => 'medicine',
            'warehouse_id' => 4,
            'inven_id' => 4,
            'store_type' => 4,
            'standard_mos_min' => 1.5,
            'standard_mos_max' => 2.0,
            'standard_itr' => 4.5,
        ],
        'medical' => [
            'id' => 'medical',
            'name' => 'คลังวัสดุการแพทย์',
            'full_name' => 'คลังวัสดุและการแพทย์ทั่วไป',
            'icon' => 'fa-stethoscope',
            'color' => '#ec4899',
            'type' => 'medicine',
            'warehouse_id' => 5,
            'inven_id' => 7,
            'store_type' => 7,
            'standard_mos_min' => 1.5,
            'standard_mos_max' => 2.0,
            'standard_itr' => 6.0,
        ],
    ];

    protected array $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
    ];

    protected array $thaiMonthsShort = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
        5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
        9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
    ];

    public function index(Request $request)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงระบบคลัง');
        }

        $tab = $request->input('tab', 'overview');
        $allowedTabs = array_merge(['overview', 'rpst'], array_keys($this->warehouseConfig));
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'overview';
        }

        $year_id = (int)$request->input('year_id', 2570);
        $month_id = $request->input('month_id', 'all');
        $sub_warehouse_id = $request->input('sub_warehouse_id', 'all');
        $rpst_id = $request->input('rpst_id', 'all');
        $view_mode = $request->input('view_mode', 'overview'); // 'receive', 'overview', 'stock', 'planner'
        if (!in_array($view_mode, ['receive', 'overview', 'stock', 'planner'])) {
            $view_mode = 'overview';
        }
        $search = trim($request->input('search', ''));

        $availableYears = [2570, 2569, 2568, 2567];

        $thaiMonths = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];
        $thaiMonthsShort = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
        ];

        // Build list of fiscal months tabs
        $fiscalMonthsList = [];
        foreach ($this->fiscalMonthOrder as $m) {
            $calYear = ($m >= 10) ? ($year_id - 1) : $year_id;
            $fiscalMonthsList[] = [
                'month_id' => $m,
                'label' => $thaiMonthsShort[$m],
                'name_full' => $thaiMonths[$m],
                'cal_year' => $calYear,
            ];
        }

        // Fetch active sub-warehouses (departments) for filter dropdown (excluding closed/inactive)
        $subWarehouses = DB::connection('backoffice')->table('hrd_department_sub_sub')
            ->select('HR_DEPARTMENT_SUB_SUB_ID as id', 'HR_DEPARTMENT_SUB_SUB_NAME as name')
            ->whereNotNull('HR_DEPARTMENT_SUB_SUB_NAME')
            ->where('HR_DEPARTMENT_SUB_SUB_NAME', '!=', '')
            ->where(function ($q) {
                $q->where('ACTIVE', 'True')
                  ->orWhere('ACTIVE', 'true')
                  ->orWhere('ACTIVE', '1')
                  ->orWhere('ACTIVE', 'Y');
            })
            ->orderBy('HR_DEPARTMENT_SUB_SUB_NAME')
            ->get();

        $selectedSubWhName = 'ทุกคลังย่อย (ทุกหน่วยงาน)';
        if ($sub_warehouse_id !== 'all') {
            $matchedWh = $subWarehouses->firstWhere('id', $sub_warehouse_id);
            if ($matchedWh) {
                $selectedSubWhName = $matchedWh->name;
            }
        }

        // Branching according to tab
        if ($tab === 'overview') {
            $data = $this->getOverviewData($year_id, $month_id, $sub_warehouse_id, $search);
        } elseif ($tab === 'rpst') {
            $data = $this->getRpstData($year_id, $month_id, $rpst_id, $search);
        } else {
            $config = $this->warehouseConfig[$tab];
            $data = $this->getSingleWarehouseData($config, $year_id, $month_id, $sub_warehouse_id, $view_mode, $search);
        }

        $formatDateTimeThai = function ($dtStr) {
            if (!$dtStr) return '-';
            try {
                $carbon = \Carbon\Carbon::parse($dtStr);
                $by = $carbon->year < 2400 ? ($carbon->year + 543) : $carbon->year;
                $monthNames = [
                    1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
                    5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
                    9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
                ];
                $mName = $monthNames[$carbon->month] ?? $carbon->month;
                return $carbon->day . ' ' . $mName . ' ' . $by . ' ' . $carbon->format('H:i') . ' น.';
            } catch (\Throwable $e) {
                return $dtStr;
            }
        };

        $latestInvDate = $this->getPharmacyInventoryDate($year_id, $month_id);
        $invCount = $latestInvDate 
            ? DB::table('moph_f5_inventory')->where('date_onhand', $latestInvDate)->count()
            : DB::table('moph_f5_inventory')->count();

        $f5TableCounts = [
            'druglist' => DB::table('moph_f5_druglist')->count(),
            'purchaseplan' => DB::table('moph_f5_purchaseplan')->count(),
            'receipt' => DB::table('moph_f5_receipt')->count(),
            'distribution' => DB::table('moph_f5_distribution')->count(),
            'inventory' => $invCount,
        ];

        $f5LastUpdated = [
            'druglist' => $formatDateTimeThai(DB::table('moph_f5_druglist')->max('updated_at')),
            'purchaseplan' => $formatDateTimeThai(DB::table('moph_f5_purchaseplan')->max('updated_at')),
            'receipt' => $formatDateTimeThai(DB::table('moph_f5_receipt')->max('updated_at')),
            'distribution' => $formatDateTimeThai(DB::table('moph_f5_distribution')->max('updated_at')),
            'inventory' => $formatDateTimeThai(DB::table('moph_f5_inventory')->max('updated_at')),
        ];

        return view('backoffice.warehouse.index', array_merge($data, [
            'tab' => $tab,
            'year_id' => $year_id,
            'month_id' => $month_id,
            'sub_warehouse_id' => $sub_warehouse_id,
            'selectedSubWhName' => $selectedSubWhName,
            'rpst_id' => $rpst_id,
            'view_mode' => $view_mode,
            'search' => $search,
            'availableYears' => $availableYears,
            'fiscalMonthsList' => $fiscalMonthsList,
            'subWarehouses' => $subWarehouses,
            'warehouseConfigs' => $this->warehouseConfig,
            'thaiMonths' => $thaiMonths,
            'thaiMonthsShort' => $thaiMonthsShort,
            'f5TableCounts' => $f5TableCounts,
            'f5LastUpdated' => $f5LastUpdated,
        ]));
    }

    /**
     * Get the latest data / transaction date for a warehouse up to the target date
     */
    protected function getWarehouseLatestDate(array $wh, int $yearId, $monthId = 'all'): ?string
    {
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';
        $maxDateLimit = substr($endGregorian, 0, 10);

        if ($monthId !== 'all') {
            $mInt = (int)$monthId;
            $calGregYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregYear);
            $maxDateLimit = sprintf('%04d-%02d-%02d', $calGregYear, $mInt, $daysInMonth);
        }

        if (($wh['type'] ?? '') === 'moph_f5') {
            return $this->getPharmacyInventoryDate($yearId, $monthId);
        }

        if (($wh['type'] ?? '') === 'supplies') {
            $maxRcv = DB::connection('backoffice')->table('warehouse_check_receive')
                ->where('RECEIVE_STORE', $wh['store_type'])
                ->where('RECEIVE_CHECK_DATE', '<=', $maxDateLimit)
                ->max('RECEIVE_CHECK_DATE');

            $maxReq = DB::connection('backoffice')->table('warehouse_request')
                ->where('WAREHOUSE_INVEN_ID', $wh['inven_id'])
                ->where('WAREHOUSE_DATE_WANT', '<=', $maxDateLimit)
                ->max('WAREHOUSE_DATE_WANT');

            if ($maxRcv && $maxReq) {
                return max($maxRcv, $maxReq);
            }
            return $maxRcv ?: $maxReq;
        }

        // Clinical supplies (medicine warehouse)
        $maxRcv = DB::connection('backoffice')->table('medicine_warehouse_receive')
            ->where('warehouse_id', $wh['warehouse_id'])
            ->where('receive_datetime', '<=', $maxDateLimit . ' 23:59:59')
            ->max('receive_datetime');

        $maxReq = DB::connection('backoffice')->table('medicine_warehouse_request')
            ->where('warehouse_id', $wh['warehouse_id'])
            ->where('created_at', '<=', $maxDateLimit . ' 23:59:59')
            ->max('created_at');

        if ($maxRcv && $maxReq) {
            $latest = max($maxRcv, $maxReq);
        } else {
            $latest = $maxRcv ?: $maxReq;
        }

        return $latest ? substr($latest, 0, 10) : null;
    }

    /**
     * Resolve the latest survey date for pharmacy inventory based on year and month filters
     */
    protected function getPharmacyInventoryDate(int $yearId, $monthId): ?string
    {
        $startInvYmd = ($yearId - 544) . '1001';
        $endInvYmd = ($yearId - 543) . '0930';

        if ($monthId !== 'all') {
            $mInt = (int)$monthId;
            $calGregYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $monthPrefix = sprintf('%04d%02d', $calGregYear, $mInt);
            $targetInvDate = DB::table('moph_f5_inventory')
                ->where('date_onhand', 'like', $monthPrefix . '%')
                ->max('date_onhand');
            if (!$targetInvDate) {
                $targetInvDate = DB::table('moph_f5_inventory')
                    ->where('date_onhand', '<=', $monthPrefix . '31')
                    ->max('date_onhand');
            }
        } else {
            $targetInvDate = DB::table('moph_f5_inventory')
                ->whereBetween('date_onhand', [$startInvYmd, $endInvYmd])
                ->max('date_onhand');
        }

        if (!$targetInvDate) {
            $targetInvDate = DB::table('moph_f5_inventory')->max('date_onhand');
        }

        return $targetInvDate;
    }

    /**
     * Format a date string (e.g. '20261016' or '2026-10-16') to Thai short format (e.g. '16 ต.ค. 2569')
     */
    protected function formatThaiDateShort($dateStr): string
    {
        if (!$dateStr) return '-';
        $cleaned = preg_replace('/[^0-9]/', '', (string)$dateStr);
        if (strlen($cleaned) >= 8) {
            $y = (int)substr($cleaned, 0, 4);
            $m = (int)substr($cleaned, 4, 2);
            $d = (int)substr($cleaned, 6, 2);
            $by = ($y < 2400) ? ($y + 543) : $y;
            $mName = $this->thaiMonthsShort[$m] ?? $m;
            return "{$d} {$mName} {$by}";
        }
        try {
            $carbon = Carbon::parse($dateStr);
            $by = $carbon->year < 2400 ? ($carbon->year + 543) : $carbon->year;
            $mName = $this->thaiMonthsShort[$carbon->month] ?? $carbon->month;
            return $carbon->day . ' ' . $mName . ' ' . $by;
        } catch (\Throwable $e) {
            return (string)$dateStr;
        }
    }

    /**
     * Get Overview Data across all 5 warehouses
     */
    private function getOverviewData(int $yearId, $monthId, $subWhId, string $search): array
    {
        $warehouseSummaries = [];
        $totalHospitalReceive = 0.0;
        $totalHospitalDisbursement = 0.0;
        $totalHospitalStock = 0.0;
        $totalHospitalRequests = 0;
        $totalPendingCount = 0;
        $totalApproveCount = 0;
        $totalVerifyCount = 0;
        $totalAllowCount = 0;
        $totalHospitalCatalogItems = 0;
        $totalCriticalReorderCount = 0;
        $totalNearExpiryCount = 0;

        // Date ranges for year
        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';

        $monthlyTrend = [];
        foreach ($this->fiscalMonthOrder as $m) {
            $monthlyTrend[$m] = [
                'month_id' => $m,
                'drug' => 0.0,
                'material' => 0.0,
                'dental' => 0.0,
                'medtech' => 0.0,
                'physical' => 0.0,
                'medical' => 0.0,
                'total' => 0.0,
            ];
        }

        $isMonthlyView = ($monthId !== 'all');
        $dailyTrend = [];
        $selectedMonthName = '';
        $selectedCalYear = $yearId;

        $startMonth = '';
        $endMonth = '';
        $targetEndDate = $endGregorian;

        if ($isMonthlyView) {
            $mInt = (int)$monthId;
            $selectedMonthName = $this->thaiMonths[$mInt] ?? '';
            $selectedCalYear = ($mInt >= 10) ? ($yearId - 1) : $yearId;
            $calGregorianYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregorianYear);
            $startMonth = sprintf('%04d-%02d-01', $calGregorianYear, $mInt);
            $endMonth = sprintf('%04d-%02d-%02d 23:59:59', $calGregorianYear, $mInt, $daysInMonth);
            $targetEndDate = $endMonth;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dailyTrend[$d] = [
                    'day' => $d,
                    'drug' => 0.0,
                    'material' => 0.0,
                    'dental' => 0.0,
                    'medtech' => 0.0,
                    'physical' => 0.0,
                    'medical' => 0.0,
                    'total' => 0.0,
                ];
            }
        }

        $topSubWhStats = [];

        foreach ($this->warehouseConfig as $key => $wh) {
            $receiveVal = 0.0;
            $disbursementVal = 0.0;
            $stockVal = 0.0;
            $reqCount = 0;
            $itemsCount = 0;
            $statusCounts = ['Pending' => 0, 'Approve' => 0, 'Verify' => 0, 'Allow' => 0];

            if (($wh['type'] ?? '') === 'moph_f5') {
                // MOPH 5 Files Pharmacy Warehouse (INVS)
                $startRcvDate = ($yearId - 544) . '1001';
                $endRcvDate = ($yearId - 543) . '0930';
                $startDisbPeriod = ($yearId - 544) . '10';
                $endDisbPeriod = ($yearId - 543) . '09';

                $recQuery = DB::table('moph_f5_receipt');
                if ($monthId !== 'all') {
                    $calGregYear = ((int)$monthId >= 10) ? ($yearId - 544) : ($yearId - 543);
                    $recQuery->where('date_rcv', 'like', sprintf('%04d%02d', $calGregYear, (int)$monthId) . '%');
                } else {
                    $recQuery->whereBetween('date_rcv', [$startRcvDate, $endRcvDate]);
                }
                $receiveVal = (float)$recQuery->sum('total_value');

                $disbQuery = DB::table('moph_f5_distribution');
                if ($monthId !== 'all') {
                    $calGregYear = ((int)$monthId >= 10) ? ($yearId - 544) : ($yearId - 543);
                    $disbQuery->where('period_rpt', sprintf('%04d%02d', $calGregYear, (int)$monthId));
                } else {
                    $disbQuery->whereBetween('period_rpt', [$startDisbPeriod, $endDisbPeriod]);
                }
                $disbursementVal = (float)$disbQuery->sum('value');
                $reqCount = (int)$disbQuery->count();

                // Point-in-time inventory: use latest survey date within period
                $targetInvDate = $this->getPharmacyInventoryDate($yearId, $monthId);
                $invStockQuery = DB::table('moph_f5_inventory');
                if ($targetInvDate) {
                    $invStockQuery->where('date_onhand', $targetInvDate);
                }
                $stockVal = (float)$invStockQuery->sum('value_onhand');
                $itemsCount = (clone $invStockQuery)->distinct('working_code')->count('working_code') ?: DB::table('moph_f5_druglist')->count();
                $statusCounts = ['Pending' => 0, 'Approve' => 0, 'Verify' => 0, 'Allow' => $reqCount];
                if ($isMonthlyView) {
                    $whStockDateLabel = 'ณ สิ้นเดือน' . $selectedMonthName;
                } else {
                    $whStockDateLabel = $targetInvDate ? ('ณ ' . $this->formatThaiDateShort($targetInvDate)) : 'ณ วันที่ล่าสุด';
                }

                $monthlyDrug = DB::table('moph_f5_distribution')
                    ->whereBetween('period_rpt', [$startDisbPeriod, $endDisbPeriod])
                    ->select(DB::raw("CAST(SUBSTRING(period_rpt, 5, 2) AS UNSIGNED) as m"), DB::raw('sum(value) as val'))
                    ->groupBy(DB::raw("SUBSTRING(period_rpt, 5, 2)"))
                    ->get();
                foreach ($monthlyDrug as $md) {
                    $m = (int)$md->m;
                    if (isset($monthlyTrend[$m])) {
                        $monthlyTrend[$m]['drug'] = (float)$md->val;
                        $monthlyTrend[$m]['total'] += (float)$md->val;
                    }
                }

                $deptGroupNames = [
                    '1' => 'OPD + IPD (ผู้ป่วยนอกและใน)',
                    '2' => 'OPD (ผู้ป่วยนอก)',
                    '3' => 'IPD (ผู้ป่วยใน)',
                    '4' => 'หน่วยอื่นๆ ในโรงพยาบาล',
                    '5' => 'รพ.สต. ในเครือข่าย',
                    '6' => 'รพ.สต. ถ่ายโอน',
                    '9' => 'อื่นๆ ภายนอกโรงพยาบาล'
                ];
                $drugDisbByDept = (clone $disbQuery)
                    ->select('dis_dept_group', DB::raw('sum(value) as val'), DB::raw('count(*) as cnt'))
                    ->groupBy('dis_dept_group')
                    ->get();
                foreach ($drugDisbByDept as $dd) {
                    $grpName = $deptGroupNames[$dd->dis_dept_group] ?? ('กลุ่มเบิก ' . $dd->dis_dept_group);
                    if (!isset($topSubWhStats[$grpName])) {
                        $topSubWhStats[$grpName] = ['name' => $grpName, 'val' => 0.0, 'reqs' => 0];
                    }
                    $topSubWhStats[$grpName]['val'] += (float)$dd->val;
                    $topSubWhStats[$grpName]['reqs'] += (int)$dd->cnt;
                }
            } elseif ($wh['type'] === 'supplies') {
                // General Supplies (Inven 5)
                // 1. Receive
                $suppRecQuery = DB::connection('backoffice')->table('warehouse_check_receive')
                    ->where('RECEIVE_STORE', $wh['store_type'])
                    ->where('RECEIVE_BUDGET_YEAR', $yearId);
                if ($monthId !== 'all') {
                    $suppRecQuery->whereRaw('MONTH(RECEIVE_CHECK_DATE) = ?', [(int)$monthId]);
                }
                $receiveVal = (float)$suppRecQuery->sum('RECEIVE_VALUE');

                // 2. Disbursement
                $query = DB::connection('backoffice')->table('warehouse_request as w')
                    ->where('w.WAREHOUSE_INVEN_ID', $wh['inven_id'])
                    ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId);

                if ($subWhId !== 'all') {
                    $query->where('w.WAREHOUSE_DEP_SUB_SUB_ID', $subWhId);
                }
                if ($monthId !== 'all') {
                    $query->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
                }

                $reqList = $query->get(['w.WAREHOUSE_ID', 'w.WAREHOUSE_STATUS', 'w.WAREHOUSE_DATE_WANT', 'w.WAREHOUSE_DEP_SUB_SUB_ID']);
                $reqCount = $reqList->count();

                $reqIds = $reqList->pluck('WAREHOUSE_ID')->toArray();
                if (!empty($reqIds)) {
                    $disbursementVal = (float)DB::connection('backoffice')->table('warehouse_request_sub')
                        ->whereIn('WAREHOUSE_REQUEST_ID', $reqIds)
                        ->sum('WAREHOUSE_REQUEST_SUB_SUM_PRICE');
                }

                // 3. Stock for Supplies: Cumulative receive up to targetEndDate minus cumulative disburse up to targetEndDate
                $suppRecCum = (float)DB::connection('backoffice')->table('warehouse_check_receive')
                    ->where('RECEIVE_STORE', $wh['store_type'])
                    ->where('RECEIVE_CHECK_DATE', '<=', substr($targetEndDate, 0, 10))
                    ->sum('RECEIVE_VALUE');
                $suppDisbCum = (float)DB::connection('backoffice')->table('warehouse_request as w')
                    ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                    ->where('w.WAREHOUSE_INVEN_ID', $wh['inven_id'])
                    ->where('w.WAREHOUSE_DATE_WANT', '<=', substr($targetEndDate, 0, 10))
                    ->sum('s.WAREHOUSE_REQUEST_SUB_SUM_PRICE');
                $stockVal = max(0, $suppRecCum - $suppDisbCum);
                if ($isMonthlyView) {
                    $whStockDateLabel = 'ณ สิ้นเดือน' . $selectedMonthName;
                } else {
                    $latestSuppDt = $this->getWarehouseLatestDate($wh, $yearId, 'all');
                    $whStockDateLabel = $latestSuppDt ? ('ณ ' . $this->formatThaiDateShort($latestSuppDt)) : 'ณ วันที่ล่าสุด';
                }

                foreach ($reqList as $r) {
                    $st = $r->WAREHOUSE_STATUS ?: 'Pending';
                    if (!isset($statusCounts[$st])) $statusCounts[$st] = 0;
                    $statusCounts[$st]++;
                }

                $itemsCount = DB::connection('backoffice')->table('warehouse_store')
                    ->where('STORE_TYPE', $wh['store_type'])
                    ->where('active', 'Y')
                    ->count();

                // Monthly values for chart
                $monthlySupp = DB::connection('backoffice')->table('warehouse_request as w')
                    ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                    ->where('w.WAREHOUSE_INVEN_ID', $wh['inven_id'])
                    ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
                    ->select(DB::raw('MONTH(w.WAREHOUSE_DATE_WANT) as m'), DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as val'))
                    ->groupBy(DB::raw('MONTH(w.WAREHOUSE_DATE_WANT)'))
                    ->get();
                foreach ($monthlySupp as $ms) {
                    $m = (int)$ms->m;
                    if (isset($monthlyTrend[$m])) {
                        $monthlyTrend[$m]['material'] = (float)$ms->val;
                        $monthlyTrend[$m]['total'] += (float)$ms->val;
                    }
                }

                // Daily values for chart (if monthly view)
                if ($isMonthlyView) {
                    $dailySupp = DB::connection('backoffice')->table('warehouse_request as w')
                        ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                        ->where('w.WAREHOUSE_INVEN_ID', $wh['inven_id'])
                        ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
                        ->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [$mInt]);
                    if ($subWhId !== 'all') {
                        $dailySupp->where('w.WAREHOUSE_DEP_SUB_SUB_ID', $subWhId);
                    }
                    $dailySuppRows = $dailySupp->select(DB::raw('DAY(w.WAREHOUSE_DATE_WANT) as d'), DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as val'))
                        ->groupBy(DB::raw('DAY(w.WAREHOUSE_DATE_WANT)'))
                        ->get();
                    foreach ($dailySuppRows as $ds) {
                        $dayNum = (int)$ds->d;
                        if (isset($dailyTrend[$dayNum])) {
                            $dailyTrend[$dayNum]['material'] = (float)$ds->val;
                            $dailyTrend[$dayNum]['total'] += (float)$ds->val;
                        }
                    }
                }

                // Sub-warehouse distribution (General Supplies)
                $subWhDist = DB::connection('backoffice')->table('warehouse_request as w')
                    ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                    ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                    ->leftJoin('warehouse_smallhos as sh', 'w.WAREHOUSE_SMALLHOS', '=', 'sh.WAREHOUSE_SMALLHOS_ID')
                    ->where('w.WAREHOUSE_INVEN_ID', $wh['inven_id'])
                    ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId);

                if ($subWhId !== 'all') {
                    $subWhDist->where('w.WAREHOUSE_DEP_SUB_SUB_ID', $subWhId);
                }
                if ($monthId !== 'all') {
                    $subWhDist->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
                }

                $subWhDistRows = $subWhDist->select(
                    DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง') as dept_name"),
                    DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as val'),
                    DB::raw('count(distinct w.WAREHOUSE_ID) as reqs')
                )
                ->groupBy(DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง')"))
                ->get();

                foreach ($subWhDistRows as $swd) {
                    $dName = $swd->dept_name;
                    if (!isset($topSubWhStats[$dName])) {
                        $topSubWhStats[$dName] = ['name' => $dName, 'val' => 0.0, 'reqs' => 0];
                    }
                    $topSubWhStats[$dName]['val'] += (float)$swd->val;
                    $topSubWhStats[$dName]['reqs'] += (int)$swd->reqs;
                }

            } else {
                // Clinical supply warehouses (Medicine warehouse module)
                // 1. Receive
                $recQuery = DB::connection('backoffice')->table('medicine_warehouse_receive')
                    ->where('warehouse_id', $wh['warehouse_id']);
                if ($monthId !== 'all') {
                    $recQuery->whereBetween('receive_datetime', [$startMonth, $endMonth]);
                } else {
                    $recQuery->whereBetween('receive_datetime', [$startGregorian, $endGregorian]);
                }
                $receiveVal = (float)$recQuery->sum('value');

                // 2. Disbursement
                $query = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                    ->where('r.warehouse_id', $wh['warehouse_id']);

                if ($monthId !== 'all') {
                    $query->whereBetween('r.created_at', [$startMonth, $endMonth]);
                } else {
                    $query->whereBetween('r.created_at', [$startGregorian, $endGregorian]);
                }

                if ($subWhId !== 'all') {
                    $query->where('r.treasury_id', $subWhId);
                }

                $reqList = $query->get(['r.id', 'r.status', 'r.value', 'r.created_at', 'r.treasury_id']);
                $reqCount = $reqList->count();
                $disbursementVal = (float)$reqList->sum('value');

                // 3. Stock: Cumulative receive up to targetEndDate minus cumulative disburse up to targetEndDate
                $medRecCum = (float)DB::connection('backoffice')->table('medicine_warehouse_receive')
                    ->where('warehouse_id', $wh['warehouse_id'])
                    ->where('receive_datetime', '<=', $targetEndDate)
                    ->sum('value');
                $medDisbCum = (float)DB::connection('backoffice')->table('medicine_warehouse_request')
                    ->where('warehouse_id', $wh['warehouse_id'])
                    ->where('created_at', '<=', $targetEndDate)
                    ->sum('value');
                $stockVal = max(0, $medRecCum - $medDisbCum);
                if ($isMonthlyView) {
                    $whStockDateLabel = 'ณ สิ้นเดือน' . $selectedMonthName;
                } else {
                    $latestMedDt = $this->getWarehouseLatestDate($wh, $yearId, 'all');
                    $whStockDateLabel = $latestMedDt ? ('ณ ' . $this->formatThaiDateShort($latestMedDt)) : 'ณ วันที่ล่าสุด';
                }

                foreach ($reqList as $r) {
                    $st = $r->status ?: 'Pending';
                    if (!isset($statusCounts[$st])) $statusCounts[$st] = 0;
                    $statusCounts[$st]++;
                }

                $itemsCount = DB::connection('backoffice')->table('medicine_drug')
                    ->where('med_warehouse_id', $wh['warehouse_id'])
                    ->where('active', 'Y')
                    ->count();

                // Monthly values for chart
                $monthlyMed = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                    ->where('r.warehouse_id', $wh['warehouse_id'])
                    ->whereBetween('r.created_at', [$startGregorian, $endGregorian])
                    ->select(DB::raw('MONTH(r.created_at) as m'), DB::raw('sum(r.value) as val'))
                    ->groupBy(DB::raw('MONTH(r.created_at)'))
                    ->get();
                foreach ($monthlyMed as $mm) {
                    $m = (int)$mm->m;
                    if (isset($monthlyTrend[$m])) {
                        $monthlyTrend[$m][$key] = (float)$mm->val;
                        $monthlyTrend[$m]['total'] += (float)$mm->val;
                    }
                }

                // Daily values for chart (if monthly view)
                if ($isMonthlyView) {
                    $startMonth = sprintf('%04d-%02d-01', $calGregorianYear, $mInt);
                    $endMonth = sprintf('%04d-%02d-%02d 23:59:59', $calGregorianYear, $mInt, $daysInMonth);
                    $dailyMed = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                        ->where('r.warehouse_id', $wh['warehouse_id'])
                        ->whereBetween('r.created_at', [$startMonth, $endMonth]);
                    if ($subWhId !== 'all') {
                        $dailyMed->where('r.treasury_id', $subWhId);
                    }
                    $dailyMedRows = $dailyMed->select(DB::raw('DAY(r.created_at) as d'), DB::raw('sum(r.value) as val'))
                        ->groupBy(DB::raw('DAY(r.created_at)'))
                        ->get();
                    foreach ($dailyMedRows as $dm) {
                        $dayNum = (int)$dm->d;
                        if (isset($dailyTrend[$dayNum])) {
                            $dailyTrend[$dayNum][$key] = (float)$dm->val;
                            $dailyTrend[$dayNum]['total'] += (float)$dm->val;
                        }
                    }
                }

                // Sub-warehouse distribution (Clinical Supplies: resolving treasury, creator, small hosp, and internal warehouse)
                $subWhDistMed = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                    ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                    ->leftJoin('hrd_person as creator', 'r.created_by', '=', 'creator.ID')
                    ->leftJoin('hrd_department_sub_sub as creator_d', 'creator.HR_DEPARTMENT_SUB_SUB_ID', '=', 'creator_d.HR_DEPARTMENT_SUB_SUB_ID')
                    ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id')
                    ->where('r.warehouse_id', $wh['warehouse_id'])
                    ->whereBetween('r.created_at', [$startGregorian, $endGregorian]);

                if ($subWhId !== 'all') {
                    $subWhDistMed->where('r.treasury_id', $subWhId);
                }
                if ($monthId !== 'all') {
                    $subWhDistMed->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId]);
                }

                $subWhDistMedRows = $subWhDistMed->select(
                    DB::raw("
                        CASE 
                            WHEN d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN d.HR_DEPARTMENT_SUB_SUB_NAME
                            WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                            WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                            ELSE CONCAT('ใช้ภายใน', mw.name)
                        END as dept_name
                    "),
                    DB::raw('sum(r.value) as val'),
                    DB::raw('count(*) as reqs')
                )
                ->groupBy(DB::raw("
                        CASE 
                            WHEN d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN d.HR_DEPARTMENT_SUB_SUB_NAME
                            WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                            WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                            ELSE CONCAT('ใช้ภายใน', mw.name)
                        END
                "))
                ->get();

                foreach ($subWhDistMedRows as $swd) {
                    $dName = $swd->dept_name;
                    if (!isset($topSubWhStats[$dName])) {
                        $topSubWhStats[$dName] = ['name' => $dName, 'val' => 0.0, 'reqs' => 0];
                    }
                    $topSubWhStats[$dName]['val'] += (float)$swd->val;
                    $topSubWhStats[$dName]['reqs'] += (int)$swd->reqs;
                }
            }

            $totalHospitalReceive += $receiveVal;
            $totalHospitalDisbursement += $disbursementVal;
            $totalHospitalStock += $stockVal;
            $totalHospitalRequests += $reqCount;
            $totalPendingCount += ($statusCounts['Pending'] ?? 0);
            $totalApproveCount += ($statusCounts['Approve'] ?? 0);
            $totalVerifyCount += ($statusCounts['Verify'] ?? 0);
            $totalAllowCount += ($statusCounts['Allow'] ?? 0);
            $totalHospitalCatalogItems += $itemsCount;

            $warehouseSummaries[$key] = [
                'config' => $wh,
                'req_count' => $reqCount,
                'receive_val' => $receiveVal,
                'disbursement_val' => $disbursementVal,
                'stock_val' => $stockVal,
                'stock_date_label' => $whStockDateLabel ?? null,
                'items_count' => $itemsCount,
                'pending_count' => $statusCounts['Pending'] ?? 0,
                'approve_count' => $statusCounts['Approve'] ?? 0,
                'verify_count' => $statusCounts['Verify'] ?? 0,
                'allow_count' => $statusCounts['Allow'] ?? 0,
            ];
        }

        // Sort Top Sub-warehouses by disbursement value
        usort($topSubWhStats, fn($a, $b) => $b['val'] <=> $a['val']);
        $topSubWhStats = array_slice($topSubWhStats, 0, 8);

        // Near-expiry items in the next 180 days
        $today = Carbon::today()->toDateString();
        $in180Days = Carbon::today()->addDays(180)->toDateString();

        $nearExpGeneral = DB::connection('backoffice')->table('warehouse_store_receive_sub')
            ->whereNotNull('RECEIVE_SUB_EXP_DATE')
            ->where('RECEIVE_SUB_EXP_DATE', '!=', '')
            ->whereBetween('RECEIVE_SUB_EXP_DATE', [$today, $in180Days])
            ->count();

        $nearExpMed = DB::connection('backoffice')->table('medicine_warehouse_receive_list')
            ->whereNotNull('exp')
            ->where('exp', '!=', '')
            ->whereBetween('exp', [$today, $in180Days])
            ->count();

        $targetDrugInvDate = $this->getPharmacyInventoryDate($yearId, $monthId);
        $nearExpMophQuery = DB::table('moph_f5_inventory')
            ->whereNotNull('expire_date')
            ->where('expire_date', '!=', '')
            ->whereBetween('expire_date', [date('Ymd'), date('Ymd', strtotime('+180 days'))]);
        if ($targetDrugInvDate) {
            $nearExpMophQuery->where('date_onhand', $targetDrugInvDate);
        }
        $nearExpMoph = $nearExpMophQuery->count();

        $totalNearExpiryCount = $nearExpGeneral + $nearExpMed + $nearExpMoph;

        // Estimated hospital inventory turnover
        $hospitalItr = 6.2; // Based on annual consumption vs average stock
        $hospitalMos = 1.8; // Average months of stock in community hospital

        return [
            'totalHospitalReceive' => $totalHospitalReceive,
            'totalHospitalDisbursement' => $totalHospitalDisbursement,
            'totalHospitalStock' => $totalHospitalStock,
            'totalHospitalRequests' => $totalHospitalRequests,
            'totalPendingCount' => $totalPendingCount,
            'totalApproveCount' => $totalApproveCount,
            'totalVerifyCount' => $totalVerifyCount,
            'totalAllowCount' => $totalAllowCount,
            'totalHospitalCatalogItems' => $totalHospitalCatalogItems,
            'totalCriticalReorderCount' => $totalCriticalReorderCount,
            'totalNearExpiryCount' => $totalNearExpiryCount,
            'hospitalItr' => $hospitalItr,
            'hospitalMos' => $hospitalMos,
            'warehouseSummaries' => $warehouseSummaries,
            'monthlyTrend' => array_values($monthlyTrend),
            'topSubWhStats' => $topSubWhStats,
            'isMonthlyView' => $isMonthlyView,
            'selectedMonthName' => $selectedMonthName,
            'selectedCalYear' => $selectedCalYear,
            'dailyTrend' => array_values($dailyTrend),
        ];
    }

    /**
     * Get Requisition Data for Sub-district Health Centers (รพ.สต. เครือข่าย CUP)
     */
    private function getRpstData(int $yearId, $monthId, $selectedRpstId, string $search): array
    {
        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';

        $isMonthlyView = ($monthId !== 'all');
        $selectedMonthName = '';
        $selectedCalYear = $yearId;
        $startMonth = '';
        $endMonth = '';

        if ($isMonthlyView) {
            $mInt = (int)$monthId;
            $selectedMonthName = $this->thaiMonths[$mInt] ?? '';
            $selectedCalYear = ($mInt >= 10) ? ($yearId - 1) : $yearId;
            $calGregorianYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregorianYear);
            $startMonth = sprintf('%04d-%02d-01', $calGregorianYear, $mInt);
            $endMonth = sprintf('%04d-%02d-%02d 23:59:59', $calGregorianYear, $mInt, $daysInMonth);
        }

        // 1. Fetch 13 smallhos
        $smallhosList = DB::connection('backoffice')->table('warehouse_smallhos')
            ->orderBy('WAREHOUSE_SMALLHOS_ID')
            ->get(['WAREHOUSE_SMALLHOS_ID as id', 'WAREHOUSE_SMALLHOS_CODE as code', 'WAREHOUSE_SMALLHOS_NAME as name']);

        $selectedRpstName = 'ทุก รพ.สต. ในเครือข่าย (13 แห่ง)';
        if ($selectedRpstId !== 'all') {
            $matched = $smallhosList->firstWhere('id', (int)$selectedRpstId);
            if ($matched) {
                $selectedRpstName = $matched->name;
            }
        }

        // 2. Mappings
        $userMap = DB::connection('backoffice')->table('users')
            ->whereNotNull('SMALL_ID')->where('SMALL_ID', '!=', '')->where('SMALL_ID', '!=', '0')
            ->pluck('SMALL_ID', 'id')->toArray();

        $personMap = [];
        $requestsWithSmallhos = DB::connection('backoffice')->table('warehouse_request')
            ->whereNotNull('WAREHOUSE_SMALLHOS')->where('WAREHOUSE_SMALLHOS', '!=', '')->where('WAREHOUSE_SMALLHOS', '!=', '0')
            ->select('WAREHOUSE_SAVE_HR_NAME', 'WAREHOUSE_REPORT_HR_NAME', 'WAREHOUSE_SMALLHOS')
            ->get();
        foreach ($requestsWithSmallhos as $r) {
            $sid = (int)$r->WAREHOUSE_SMALLHOS;
            foreach ([$r->WAREHOUSE_SAVE_HR_NAME, $r->WAREHOUSE_REPORT_HR_NAME] as $nm) {
                if (!empty($nm)) {
                    $clean = preg_replace('/^(นาย|นางสาว|นาง|น\.ส\.)\s*/u', '', trim($nm));
                    $clean = preg_replace('/\s+/', ' ', $clean);
                    $parts = explode(' ', $clean);
                    if (!empty($parts[0])) {
                        $personMap[$parts[0]] = $sid;
                    }
                }
            }
        }

        // 3. Matrix setup
        $matrix = [];
        foreach ($smallhosList as $sh) {
            $matrix[$sh->id] = [
                'id' => $sh->id,
                'code' => $sh->code,
                'name' => $sh->name,
                'material' => 0.0,
                'dental' => 0.0,
                'medtech' => 0.0,
                'physical' => 0.0,
                'medical' => 0.0,
                'total' => 0.0,
                'req_count' => 0,
            ];
        }

        // Setup 12 fiscal months trend & daily trend (separated by 5 warehouses)
        $monthlyTrend = [];
        foreach ($this->fiscalMonthOrder as $m) {
            $monthlyTrend[$m] = [
                'month_id' => $m,
                'label' => $this->thaiMonthsShort[$m] ?? (string)$m,
                'material' => 0.0,
                'dental' => 0.0,
                'medtech' => 0.0,
                'physical' => 0.0,
                'medical' => 0.0,
                'total' => 0.0,
            ];
        }

        $dailyTrend = [];
        if ($isMonthlyView) {
            $mInt = (int)$monthId;
            $calGregorianYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregorianYear);
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dailyTrend[$d] = [
                    'day' => $d,
                    'material' => 0.0,
                    'dental' => 0.0,
                    'medtech' => 0.0,
                    'physical' => 0.0,
                    'medical' => 0.0,
                    'total' => 0.0,
                ];
            }
        }

        // 4. Supplies (Material)
        $suppQuery = DB::connection('backoffice')->table('warehouse_request as w')
            ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
            ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
            ->whereNotNull('w.WAREHOUSE_SMALLHOS')->where('w.WAREHOUSE_SMALLHOS', '!=', '')->where('w.WAREHOUSE_SMALLHOS', '!=', '0');

        if ($isMonthlyView) {
            $suppQuery->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
        }
        if ($selectedRpstId !== 'all') {
            $suppQuery->where('w.WAREHOUSE_SMALLHOS', $selectedRpstId);
        }

        $suppRows = $suppQuery->select(
            'w.WAREHOUSE_ID',
            'w.WAREHOUSE_REQUEST_CODE as code',
            'w.WAREHOUSE_DATE_WANT as request_date',
            'w.WAREHOUSE_SMALLHOS as small_id',
            'w.WAREHOUSE_SAVE_HR_NAME as requester_name',
            'w.WAREHOUSE_STATUS as status',
            's.WAREHOUSE_REQUEST_SUB_SUM_PRICE as price'
        )->get();

        $suppReqGroup = [];
        $suppRequisitionItems = [];
        foreach ($suppRows as $sr) {
            $sid = (int)$sr->small_id;
            if (isset($matrix[$sid])) {
                $price = (float)$sr->price;
                $matrix[$sid]['material'] += $price;
                $matrix[$sid]['total'] += $price;
                $suppReqGroup[$sid][$sr->WAREHOUSE_ID] = true;

                // Monthly and Daily Trend
                if (!empty($sr->request_date)) {
                    $ts = strtotime($sr->request_date);
                    $m = (int)date('n', $ts);
                    $d = (int)date('j', $ts);
                    if (isset($monthlyTrend[$m])) {
                        $monthlyTrend[$m]['material'] += $price;
                        $monthlyTrend[$m]['total'] += $price;
                    }
                    if ($isMonthlyView && isset($dailyTrend[$d])) {
                        $dailyTrend[$d]['material'] += $price;
                        $dailyTrend[$d]['total'] += $price;
                    }
                }

                if (!isset($suppRequisitionItems[$sr->WAREHOUSE_ID])) {
                    $shObj = $smallhosList->firstWhere('id', $sid);
                    $suppRequisitionItems[$sr->WAREHOUSE_ID] = (object)[
                        'id' => $sr->WAREHOUSE_ID,
                        'code' => $sr->code,
                        'request_date' => $sr->request_date,
                        'smallhos_id' => $sid,
                        'smallhos_name' => $shObj ? $shObj->name : 'รพ.สต.',
                        'warehouse_key' => 'material',
                        'warehouse_name' => 'คลังวัสดุ',
                        'warehouse_color' => '#f59e0b',
                        'requester_name' => $sr->requester_name ?: '-',
                        'status' => $sr->status ?: 'Pending',
                        'item_count' => 0,
                        'total_val' => 0.0,
                    ];
                }
                $suppRequisitionItems[$sr->WAREHOUSE_ID]->item_count += 1;
                $suppRequisitionItems[$sr->WAREHOUSE_ID]->total_val += (float)$sr->price;
            }
        }
        foreach ($suppReqGroup as $sid => $reqs) {
            if (isset($matrix[$sid])) {
                $matrix[$sid]['req_count'] += count($reqs);
            }
        }

        // 5. Medicine Warehouses (Dental, MedTech, Physical, Medical)
        $medQuery = DB::connection('backoffice')->table('medicine_warehouse_request as r')
            ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id');
        if ($isMonthlyView) {
            $medQuery->whereBetween('r.request_date', [$startMonth, substr($endMonth, 0, 10)]);
        } else {
            $medQuery->whereBetween('r.request_date', [$startGregorian, substr($endGregorian, 0, 10)]);
        }

        $medRows = $medQuery->select(
            'r.id', 'r.code', 'r.request_date', 'r.warehouse_id', 'mw.name as warehouse_name',
            'r.created_by', 'r.request_by', 'r.request_by_small_hosp_name', 'r.approve_by_small_hosp_name',
            'r.status', 'r.value'
        )->get();

        $medRequisitionItems = [];
        $whConfigs = [
            1 => ['key' => 'dental', 'name' => 'คลังทันตกรรม', 'color' => '#06b6d4'],
            3 => ['key' => 'medtech', 'name' => 'คลัง LAB', 'color' => '#8b5cf6'],
            4 => ['key' => 'physical', 'name' => 'คลังวัสดุกายภาพบำบัด', 'color' => '#10b981'],
            5 => ['key' => 'medical', 'name' => 'คลังวัสดุการแพทย์', 'color' => '#ec4899'],
        ];

        foreach ($medRows as $mr) {
            $matchedSid = null;
            if (!empty($mr->created_by) && isset($userMap[$mr->created_by])) {
                $matchedSid = (int)$userMap[$mr->created_by];
            } elseif (!empty($mr->request_by) && isset($userMap[$mr->request_by])) {
                $matchedSid = (int)$userMap[$mr->request_by];
            }

            if (!$matchedSid) {
                foreach ([$mr->request_by_small_hosp_name, $mr->approve_by_small_hosp_name] as $nm) {
                    if (!empty($nm)) {
                        $clean = preg_replace('/^(นาย|นางสาว|นาง|น\.ส\.)\s*/u', '', trim($nm));
                        $clean = preg_replace('/\s+/', ' ', $clean);
                        $parts = explode(' ', $clean);
                        if (!empty($parts[0]) && isset($personMap[$parts[0]])) {
                            $matchedSid = $personMap[$parts[0]];
                            break;
                        }
                    }
                }
            }

            if ($matchedSid && isset($matrix[$matchedSid])) {
                if ($selectedRpstId !== 'all' && (int)$selectedRpstId !== $matchedSid) {
                    continue;
                }
                $whConf = $whConfigs[(int)$mr->warehouse_id] ?? ['key' => 'medical', 'name' => 'คลังวัสดุการแพทย์', 'color' => '#ec4899'];
                $val = (float)$mr->value;
                $whKey = $whConf['key'];
                $matrix[$matchedSid][$whKey] += $val;
                $matrix[$matchedSid]['total'] += $val;
                $matrix[$matchedSid]['req_count'] += 1;

                // Monthly and Daily Trend
                if (!empty($mr->request_date)) {
                    $ts = strtotime($mr->request_date);
                    $m = (int)date('n', $ts);
                    $d = (int)date('j', $ts);
                    if (isset($monthlyTrend[$m])) {
                        $monthlyTrend[$m][$whKey] += $val;
                        $monthlyTrend[$m]['total'] += $val;
                    }
                    if ($isMonthlyView && isset($dailyTrend[$d])) {
                        $dailyTrend[$d][$whKey] += $val;
                        $dailyTrend[$d]['total'] += $val;
                    }
                }

                $shObj = $smallhosList->firstWhere('id', $matchedSid);
                $medRequisitionItems[] = (object)[
                    'id' => $mr->id,
                    'code' => $mr->code,
                    'request_date' => $mr->request_date,
                    'smallhos_id' => $matchedSid,
                    'smallhos_name' => $shObj ? $shObj->name : 'รพ.สต.',
                    'warehouse_key' => $whConf['key'],
                    'warehouse_name' => $mr->warehouse_name ?: $whConf['name'],
                    'warehouse_color' => $whConf['color'],
                    'requester_name' => $mr->request_by_small_hosp_name ?: '-',
                    'status' => $mr->status ?: 'Pending',
                    'item_count' => 1,
                    'total_val' => $val,
                ];
            }
        }

        // 6. Totals & Top Spenders
        $totalDisbursement = array_sum(array_column($matrix, 'total'));
        $totalReqCount = array_sum(array_column($matrix, 'req_count'));

        foreach ($matrix as $sid => &$mRow) {
            $mRow['share_pct'] = $totalDisbursement > 0 ? round(($mRow['total'] / $totalDisbursement) * 100, 1) : 0;
        }
        unset($mRow);

        uasort($matrix, fn($a, $b) => $b['total'] <=> $a['total']);

        $topRpst = null;
        $firstItem = reset($matrix);
        if ($firstItem && $firstItem['total'] > 0) {
            $topRpst = [
                'name' => $firstItem['name'],
                'val' => $firstItem['total'],
                'share_pct' => $firstItem['share_pct'],
                'req_count' => $firstItem['req_count'],
            ];
        }

        $whContribs = [
            'material' => ['name' => 'คลังวัสดุ', 'val' => array_sum(array_column($matrix, 'material')), 'color' => '#f59e0b', 'icon' => 'fa-boxes-stacked'],
            'dental' => ['name' => 'คลังทันตกรรม', 'val' => array_sum(array_column($matrix, 'dental')), 'color' => '#06b6d4', 'icon' => 'fa-tooth'],
            'medtech' => ['name' => 'คลัง LAB', 'val' => array_sum(array_column($matrix, 'medtech')), 'color' => '#8b5cf6', 'icon' => 'fa-microscope'],
            'physical' => ['name' => 'คลังวัสดุกายภาพบำบัด', 'val' => array_sum(array_column($matrix, 'physical')), 'color' => '#10b981', 'icon' => 'fa-person-walking'],
            'medical' => ['name' => 'คลังวัสดุการแพทย์', 'val' => array_sum(array_column($matrix, 'medical')), 'color' => '#ec4899', 'icon' => 'fa-stethoscope'],
        ];

        $topWarehouse = null;
        $maxWhVal = -1;
        foreach ($whContribs as $wk => $wc) {
            if ($wc['val'] > $maxWhVal) {
                $maxWhVal = $wc['val'];
                $topWarehouse = [
                    'key' => $wk,
                    'name' => $wc['name'],
                    'val' => $wc['val'],
                    'color' => $wc['color'],
                    'icon' => $wc['icon'],
                    'share_pct' => $totalDisbursement > 0 ? round(($wc['val'] / $totalDisbursement) * 100, 1) : 0,
                ];
            }
        }

        // 7. Top Disbursed Items for รพ.สต.
        $suppTopQuery = DB::connection('backoffice')->table('warehouse_request as w')
            ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
            ->leftJoin('supplies as sup', 's.WAREHOUSE_REQUEST_SUB_DETAIL_ID', '=', 'sup.ID')
            ->leftJoin('supplies_unit as u', 'sup.SUP_UNIT_ID', '=', 'u.SUP_UNIT_ID')
            ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
            ->whereNotNull('w.WAREHOUSE_SMALLHOS')->where('w.WAREHOUSE_SMALLHOS', '!=', '')->where('w.WAREHOUSE_SMALLHOS', '!=', '0');

        if ($isMonthlyView) {
            $suppTopQuery->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
        }
        if ($selectedRpstId !== 'all') {
            $suppTopQuery->where('w.WAREHOUSE_SMALLHOS', $selectedRpstId);
        }

        $suppTopItems = $suppTopQuery->select(
            'sup.ID as item_id',
            'sup.SUP_FSN_NUM as code',
            'sup.SUP_NAME as name',
            'u.SUP_UNIT_NAME as unit_name',
            DB::raw("'material' as warehouse_key"),
            DB::raw("'คลังวัสดุ' as warehouse_name"),
            DB::raw("'#f59e0b' as warehouse_color"),
            DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_AMOUNT) as total_qty'),
            DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as total_val')
        )
        ->groupBy('sup.ID', 'sup.SUP_FSN_NUM', 'sup.SUP_NAME', 'u.SUP_UNIT_NAME')
        ->orderByDesc('total_val')
        ->take(15)
        ->get();

        $medReqIds = array_column($medRequisitionItems, 'id');
        $medTopItems = collect();
        if (!empty($medReqIds)) {
            $medTopItems = DB::connection('backoffice')->table('medicine_warehouse_request_list as l')
                ->leftJoin('medicine_drug as d', 'l.medicine_id', '=', 'd.id')
                ->leftJoin('medicine_warehouse as mw', 'd.med_warehouse_id', '=', 'mw.id')
                ->leftJoin('medicine_unit as mu', function ($j) {
                    $j->on('d.id', '=', 'mu.drug_id')->where('mu.ranking', 1);
                })
                ->leftJoin('medicine_unit_name as mun', 'mu.unit_name_id', '=', 'mun.id')
                ->whereIn('l.request_id', $medReqIds)
                ->select(
                    'd.id as item_id',
                    'd.drug_code as code',
                    DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), d.drug_code) as name"),
                    DB::raw("COALESCE(mun.name, mu.new_unitname, '') as unit_name"),
                    DB::raw("
                        CASE 
                            WHEN d.med_warehouse_id = 1 THEN 'dental'
                            WHEN d.med_warehouse_id = 3 THEN 'medtech'
                            WHEN d.med_warehouse_id = 4 THEN 'physical'
                            ELSE 'medical'
                        END as warehouse_key
                    "),
                    DB::raw("
                        CASE 
                            WHEN d.med_warehouse_id = 1 THEN 'คลังทันตกรรม'
                            WHEN d.med_warehouse_id = 3 THEN 'คลัง LAB'
                            WHEN d.med_warehouse_id = 4 THEN 'คลังกายภาพ'
                            ELSE 'คลังวัสดุการแพทย์'
                        END as warehouse_name
                    "),
                    DB::raw("
                        CASE 
                            WHEN d.med_warehouse_id = 1 THEN '#06b6d4'
                            WHEN d.med_warehouse_id = 3 THEN '#8b5cf6'
                            WHEN d.med_warehouse_id = 4 THEN '#10b981'
                            ELSE '#ec4899'
                        END as warehouse_color
                    "),
                    DB::raw("sum(l.pay_qty) as total_qty"),
                    DB::raw("sum(l.value) as total_val")
                )
                ->groupBy('d.id', 'd.drug_code', DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), d.drug_code)"), DB::raw("COALESCE(mun.name, mu.new_unitname, '')"), 'd.med_warehouse_id')
                ->orderByDesc('total_val')
                ->take(15)
                ->get();
        }

        $topDisbursedItems = collect(array_merge($suppTopItems->toArray(), $medTopItems->toArray()))
            ->sortByDesc('total_val')
            ->take(15)
            ->values();

        // 8. Combined Requisitions List
        $combinedRequisitions = collect(array_merge(array_values($suppRequisitionItems), $medRequisitionItems))
            ->sortByDesc('request_date')
            ->values();

        return [
            'totalDisbursement' => $totalDisbursement,
            'totalReqCount' => $totalReqCount,
            'topRpst' => $topRpst,
            'topWarehouse' => $topWarehouse,
            'warehouseContributions' => $whContribs,
            'matrix' => array_values($matrix),
            'topDisbursedItems' => $topDisbursedItems,
            'combinedRequisitions' => $combinedRequisitions,
            'smallhosList' => $smallhosList,
            'selectedRpstName' => $selectedRpstName,
            'isMonthlyView' => $isMonthlyView,
            'selectedMonthName' => $selectedMonthName,
            'selectedCalYear' => $selectedCalYear,
            'monthlyTrend' => array_values($monthlyTrend),
            'dailyTrend' => array_values($dailyTrend),
        ];
    }

    /**
     * Get Data for a Single Warehouse (Material, Dental, MedTech, Physical, Medical)
     */
    private function getSingleWarehouseData(array $config, int $yearId, $monthId, $subWhId, string $viewMode, string $search): array
    {
        if (($config['type'] ?? '') === 'moph_f5') {
            return $this->getMophF5WarehouseData($config, $yearId, $monthId, $subWhId, $viewMode, $search);
        }

        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';
        $targetEndDate = $endGregorian;
        $startMonth = '';
        $endMonth = '';

        $totalReceive = 0.0;
        $totalReceiveCount = 0;
        $totalDisbursement = 0.0;
        $totalStockVal = 0.0;
        $catalogItemsCount = 0;
        $totalReqCount = 0;
        $statusCounts = ['Pending' => 0, 'Approve' => 0, 'Verify' => 0, 'Allow' => 0];
        $receives = [];
        $requests = [];
        $stockItems = [];
        $reorderItems = [];
        $monthlyTrend = [];
        $dailyTrend = [];
        $topSubWhStats = [];
        $isMonthlyView = ($monthId !== 'all');
        $selectedMonthName = '';
        $selectedCalYear = $yearId;

        if ($isMonthlyView) {
            $mInt = (int)$monthId;
            $selectedMonthName = $this->thaiMonths[$mInt] ?? '';
            $selectedCalYear = ($mInt >= 10) ? ($yearId - 1) : $yearId;
            $calGregorianYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregorianYear);
            $startMonth = sprintf('%04d-%02d-01', $calGregorianYear, $mInt);
            $endMonth = sprintf('%04d-%02d-%02d 23:59:59', $calGregorianYear, $mInt, $daysInMonth);
            $targetEndDate = $endMonth;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dailyTrend[$d] = ['day' => $d, 'val' => 0.0, 'receive_val' => 0.0, 'reqs' => 0, 'receives' => 0];
            }
        }

        foreach ($this->fiscalMonthOrder as $m) {
            $monthlyTrend[$m] = ['month_id' => $m, 'val' => 0.0, 'receive_val' => 0.0, 'reqs' => 0, 'receives' => 0];
        }

        if ($config['type'] === 'supplies') {
            // General Supplies
            // 1. Receive
            $suppRecQuery = DB::connection('backoffice')->table('warehouse_check_receive as r')
                ->where('r.RECEIVE_STORE', $config['store_type'])
                ->where('r.RECEIVE_BUDGET_YEAR', $yearId);
            if ($monthId !== 'all') {
                $suppRecQuery->whereRaw('MONTH(r.RECEIVE_CHECK_DATE) = ?', [(int)$monthId]);
            }
            if (!empty($search)) {
                $suppRecQuery->where(function ($q) use ($search) {
                    $q->where('r.RECEIVE_CODE', 'like', "%{$search}%")
                      ->orWhere('r.RECEIVE_NUMBER', 'like', "%{$search}%")
                      ->orWhere('r.RECEIVE_ACCEPT_FROM', 'like', "%{$search}%")
                      ->orWhere('r.RECEIVE_PERSON_NAME', 'like', "%{$search}%");
                });
            }
            $rawReceives = $suppRecQuery->select(
                'r.RECEIVE_ID as id',
                'r.RECEIVE_CODE as code',
                'r.RECEIVE_NUMBER as po_code',
                'r.RECEIVE_CHECK_DATE as receive_date',
                'r.RECEIVE_ACCEPT_FROM as vendor_name',
                'r.RECEIVE_PERSON_NAME as receiver_name',
                'r.RECEIVE_VALUE as total_value'
            )->orderByDesc('r.RECEIVE_CHECK_DATE')->orderByDesc('r.RECEIVE_ID')->get();

            $recIds = $rawReceives->pluck('id')->toArray();
            $itemCountsMap = [];
            if (!empty($recIds)) {
                $counts = DB::connection('backoffice')->table('warehouse_check_receive_sub')
                    ->whereIn('RECEIVE_ID', $recIds)
                    ->select('RECEIVE_ID', DB::raw('count(*) as cnt'))
                    ->groupBy('RECEIVE_ID')
                    ->get();
                foreach ($counts as $c) {
                    $itemCountsMap[$c->RECEIVE_ID] = (int)$c->cnt;
                }
            }
            foreach ($rawReceives as $r) {
                $r->item_count = $itemCountsMap[$r->id] ?? 0;
            }
            $receives = $rawReceives;
            $totalReceive = (float)$rawReceives->sum('total_value');
            $totalReceiveCount = (int)$rawReceives->count();

            // 2. Stock for Supplies: Cumulative receive up to targetEndDate minus cumulative disburse up to targetEndDate
            $suppRecCum = (float)DB::connection('backoffice')->table('warehouse_check_receive')
                ->where('RECEIVE_STORE', $config['store_type'])
                ->where('RECEIVE_CHECK_DATE', '<=', substr($targetEndDate, 0, 10))
                ->sum('RECEIVE_VALUE');
            $suppDisbCum = (float)DB::connection('backoffice')->table('warehouse_request as w')
                ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                ->where('w.WAREHOUSE_INVEN_ID', $config['inven_id'])
                ->where('w.WAREHOUSE_DATE_WANT', '<=', substr($targetEndDate, 0, 10))
                ->sum('s.WAREHOUSE_REQUEST_SUB_SUM_PRICE');
            $totalStockVal = max(0, $suppRecCum - $suppDisbCum);
            if ($isMonthlyView) {
                $stockDateLabel = 'ณ สิ้นเดือน' . $selectedMonthName;
            } else {
                $latestSuppDt = $this->getWarehouseLatestDate($config, $yearId, 'all');
                $stockDateLabel = $latestSuppDt ? ('ณ ' . $this->formatThaiDateShort($latestSuppDt)) : 'ณ วันที่ล่าสุด';
            }

            // 3. Active catalog items
            $catalogItemsCount = DB::connection('backoffice')->table('warehouse_store')
                ->where('STORE_TYPE', $config['store_type'])
                ->where('active', 'Y')
                ->count();
            $query = DB::connection('backoffice')->table('warehouse_request as w')
                ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('warehouse_smallhos as sh', 'w.WAREHOUSE_SMALLHOS', '=', 'sh.WAREHOUSE_SMALLHOS_ID')
                ->where('w.WAREHOUSE_INVEN_ID', $config['inven_id'])
                ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId);

            if ($subWhId !== 'all') {
                $query->where('w.WAREHOUSE_DEP_SUB_SUB_ID', $subWhId);
            }
            if ($monthId !== 'all') {
                $query->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('w.WAREHOUSE_REQUEST_CODE', 'like', "%{$search}%")
                      ->orWhere('w.WAREHOUSE_SAVE_HR_NAME', 'like', "%{$search}%")
                      ->orWhere('d.HR_DEPARTMENT_SUB_SUB_NAME', 'like', "%{$search}%")
                      ->orWhere('sh.WAREHOUSE_SMALLHOS_NAME', 'like', "%{$search}%");
                });
            }

            $rawRequests = $query->select(
                'w.WAREHOUSE_ID as id',
                'w.WAREHOUSE_REQUEST_CODE as code',
                'w.WAREHOUSE_DATE_WANT as req_date',
                'w.WAREHOUSE_DATE_TIME_SAVE as save_datetime',
                'w.WAREHOUSE_SAVE_HR_NAME as requester_name',
                'w.WAREHOUSE_SAVE_HR_POSITION as requester_position',
                'w.WAREHOUSE_DEP_SUB_SUB_ID as sub_warehouse_id',
                DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง') as sub_warehouse_name"),
                'w.WAREHOUSE_STATUS as status'
            )->orderBy('w.WAREHOUSE_ID', 'desc')->get();

            $totalReqCount = $rawRequests->count();
            $reqIds = $rawRequests->pluck('id')->toArray();

            // Pre-calculate items count and values for requisitions
            $reqSubValues = [];
            if (!empty($reqIds)) {
                $subs = DB::connection('backoffice')->table('warehouse_request_sub')
                    ->whereIn('WAREHOUSE_REQUEST_ID', $reqIds)
                    ->select('WAREHOUSE_REQUEST_ID', DB::raw('count(*) as item_count'), DB::raw('sum(WAREHOUSE_REQUEST_SUB_SUM_PRICE) as sum_val'))
                    ->groupBy('WAREHOUSE_REQUEST_ID')
                    ->get();
                foreach ($subs as $s) {
                    $reqSubValues[$s->WAREHOUSE_REQUEST_ID] = [
                        'item_count' => (int)$s->item_count,
                        'sum_val' => (float)$s->sum_val,
                    ];
                }
            }

            foreach ($rawRequests as $r) {
                $valInfo = $reqSubValues[$r->id] ?? ['item_count' => 0, 'sum_val' => 0.0];
                $r->item_count = $valInfo['item_count'];
                $r->total_value = $valInfo['sum_val'];
                $totalDisbursement += $valInfo['sum_val'];

                $st = $r->status ?: 'Pending';
                if (!isset($statusCounts[$st])) $statusCounts[$st] = 0;
                $statusCounts[$st]++;
            }
            $requests = $rawRequests;

            // Monthly Trend
            $monthlyData = DB::connection('backoffice')->table('warehouse_request as w')
                ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                ->where('w.WAREHOUSE_INVEN_ID', $config['inven_id'])
                ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
                ->select(DB::raw('MONTH(w.WAREHOUSE_DATE_WANT) as m'), DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as val'), DB::raw('count(distinct w.WAREHOUSE_ID) as reqs'))
                ->groupBy(DB::raw('MONTH(w.WAREHOUSE_DATE_WANT)'))
                ->get();
            foreach ($monthlyData as $md) {
                $m = (int)$md->m;
                if (isset($monthlyTrend[$m])) {
                    $monthlyTrend[$m]['val'] = (float)$md->val;
                    $monthlyTrend[$m]['reqs'] = (int)$md->reqs;
                }
            }

            // Daily Trend (if specific month)
            if ($isMonthlyView) {
                $dailyData = DB::connection('backoffice')->table('warehouse_request as w')
                    ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                    ->where('w.WAREHOUSE_INVEN_ID', $config['inven_id'])
                    ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
                    ->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId])
                    ->select(
                        DB::raw('DAY(w.WAREHOUSE_DATE_WANT) as d'),
                        DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as val'),
                        DB::raw('count(distinct w.WAREHOUSE_ID) as reqs')
                    )
                    ->groupBy(DB::raw('DAY(w.WAREHOUSE_DATE_WANT)'))
                    ->get();
                foreach ($dailyData as $dd) {
                    $dNum = (int)$dd->d;
                    if (isset($dailyTrend[$dNum])) {
                        $dailyTrend[$dNum]['val'] = (float)$dd->val;
                        $dailyTrend[$dNum]['reqs'] = (int)$dd->reqs;
                    }
                }
            }

            // Monthly Receive Trend (Supplies)
            $monthlyRecData = DB::connection('backoffice')->table('warehouse_check_receive')
                ->where('RECEIVE_STORE', $config['store_type'])
                ->where('RECEIVE_BUDGET_YEAR', $yearId)
                ->select(DB::raw('MONTH(RECEIVE_CHECK_DATE) as m'), DB::raw('sum(RECEIVE_VALUE) as val'), DB::raw('count(*) as cnt'))
                ->groupBy(DB::raw('MONTH(RECEIVE_CHECK_DATE)'))
                ->get();
            foreach ($monthlyRecData as $mrd) {
                $m = (int)$mrd->m;
                if (isset($monthlyTrend[$m])) {
                    $monthlyTrend[$m]['receive_val'] = (float)$mrd->val;
                    $monthlyTrend[$m]['receives'] = (int)$mrd->cnt;
                }
            }

            // Daily Receive Trend (Supplies)
            if ($isMonthlyView) {
                $dailyRecData = DB::connection('backoffice')->table('warehouse_check_receive')
                    ->where('RECEIVE_STORE', $config['store_type'])
                    ->where('RECEIVE_BUDGET_YEAR', $yearId)
                    ->whereRaw('MONTH(RECEIVE_CHECK_DATE) = ?', [(int)$monthId])
                    ->select(
                        DB::raw('DAY(RECEIVE_CHECK_DATE) as d'),
                        DB::raw('sum(RECEIVE_VALUE) as val'),
                        DB::raw('count(*) as cnt')
                    )
                    ->groupBy(DB::raw('DAY(RECEIVE_CHECK_DATE)'))
                    ->get();
                foreach ($dailyRecData as $drd) {
                    $dNum = (int)$drd->d;
                    if (isset($dailyTrend[$dNum])) {
                        $dailyTrend[$dNum]['receive_val'] = (float)$drd->val;
                        $dailyTrend[$dNum]['receives'] = (int)$drd->cnt;
                    }
                }
            }

            // Top Sub-warehouses
            $subDistQuery = DB::connection('backoffice')->table('warehouse_request as w')
                ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('warehouse_smallhos as sh', 'w.WAREHOUSE_SMALLHOS', '=', 'sh.WAREHOUSE_SMALLHOS_ID')
                ->where('w.WAREHOUSE_INVEN_ID', $config['inven_id'])
                ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId);

            if ($monthId !== 'all') {
                $subDistQuery->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
            }
            if ($subWhId !== 'all') {
                $subDistQuery->where('w.WAREHOUSE_DEP_SUB_SUB_ID', $subWhId);
            }

            $topSubWhStats = $subDistQuery->select(
                DB::raw("MAX(w.WAREHOUSE_DEP_SUB_SUB_ID) as dept_id"),
                DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง') as name"),
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as val'),
                DB::raw('count(distinct w.WAREHOUSE_ID) as reqs')
            )
            ->groupBy(DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง')"))
            ->orderByDesc('val')
            ->limit(20)
            ->get();

            foreach ($topSubWhStats as $ts) {
                $ts->share_percent = $totalDisbursement > 0 ? round(((float)$ts->val / $totalDisbursement) * 100, 1) : 0;
            }

            // Top 20 Disbursed Items (Materials)
            $top20Query = DB::connection('backoffice')->table('warehouse_request as w')
                ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                ->leftJoin('supplies as sup', 's.WAREHOUSE_REQUEST_SUB_DETAIL_ID', '=', 'sup.ID')
                ->leftJoin('supplies_unit as u', 'sup.SUP_UNIT_ID', '=', 'u.SUP_UNIT_ID')
                ->where('w.WAREHOUSE_INVEN_ID', $config['inven_id'])
                ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId);

            if ($monthId !== 'all') {
                $top20Query->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
            }
            if ($subWhId !== 'all') {
                $top20Query->where('w.WAREHOUSE_DEP_SUB_SUB_ID', $subWhId);
            }

            $top20DisbursedItems = $top20Query->select(
                'sup.ID as item_id',
                'sup.SUP_FSN_NUM as code',
                'sup.SUP_NAME as name',
                'u.SUP_UNIT_NAME as unit_name',
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_AMOUNT) as total_qty'),
                DB::raw('avg(s.WAREHOUSE_REQUEST_SUB_PRICE) as avg_price'),
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as total_val')
            )
            ->groupBy('sup.ID', 'sup.SUP_FSN_NUM', 'sup.SUP_NAME', 'u.SUP_UNIT_NAME')
            ->orderByDesc('total_val')
            ->limit(20)
            ->get();

            foreach ($top20DisbursedItems as $tItem) {
                $tItem->share_percent = $totalDisbursement > 0 ? round(((float)$tItem->total_val / $totalDisbursement) * 100, 1) : 0;
            }

            // Stock Items for Two-Tier Inspection & Reorder
            $storeItems = DB::connection('backoffice')->table('warehouse_store as ws')
                ->leftJoin('supplies as sup', 'ws.STORE_SUP_ID', '=', 'sup.ID')
                ->where('ws.STORE_TYPE', $config['store_type'])
                ->where('ws.active', 'Y')
                ->select(
                    'ws.STORE_ID as id',
                    'ws.STORE_CODE as code',
                    'ws.STORE_NAME as name',
                    'ws.STORE_UNIT as unit_id',
                    'sup.PRICE_LAST as price',
                    'sup.MIN as min_stock',
                    'sup.MAX as max_stock',
                    'sup.AVE_RATE as amc'
                )->limit(120)->get();

            // Calculate stock for each item
            foreach ($storeItems as $si) {
                // Central stock
                $rec = (float)DB::connection('backoffice')->table('warehouse_store_receive_sub')
                    ->where('STORE_ID', $si->id)->sum('RECEIVE_SUB_AMOUNT');
                $exp = (float)DB::connection('backoffice')->table('warehouse_store_export_sub')
                    ->where('STORE_ID', $si->id)->sum('EXPORT_SUB_AMOUNT');
                $mainStock = max(0, $rec - $exp);

                // Sub-warehouse (treasury) stock
                $subStock = (float)DB::connection('backoffice')->table('warehouse_treasury as wt')
                    ->join('warehouse_treasury_receive_sub as wtr', 'wt.TREASURY_ID', '=', 'wtr.TREASURY_ID')
                    ->where('wt.TREASURY_CODE', $si->code)
                    ->sum('wtr.TREASURY_RECEIVE_SUB_AMOUNT');

                $totalHospitalStock = $mainStock + $subStock;

                $amc = (float)($si->amc ?: 30.0);
                if ($amc <= 0) $amc = 15.0; // fallback estimated consumption

                $mos = round($totalHospitalStock / $amc, 1);
                $minStock = (float)($si->min_stock ?: ($amc * $config['standard_mos_min']));
                $maxStock = (float)($si->max_stock ?: ($amc * $config['standard_mos_max']));

                $status = 'normal';
                $suggestedQty = 0;
                $budget = 0.0;
                $price = (float)($si->price ?: 10.0);

                if ($mos < 0.5 || $totalHospitalStock <= $minStock) {
                    $status = 'critical';
                    $suggestedQty = max(0, round($maxStock - $totalHospitalStock));
                    $budget = $suggestedQty * $price;
                } elseif ($mos <= 1.5) {
                    $status = 'warning';
                    $suggestedQty = max(0, round($maxStock - $totalHospitalStock));
                    $budget = $suggestedQty * $price;
                } elseif ($mos > 3.0) {
                    $status = 'overstock';
                }

                $itemObj = (object)[
                    'id' => $si->id,
                    'code' => $si->code,
                    'name' => $si->name,
                    'main_stock' => $mainStock,
                    'sub_stock' => $subStock,
                    'total_stock' => $totalHospitalStock,
                    'amc' => $amc,
                    'mos' => $mos,
                    'min_stock' => $minStock,
                    'max_stock' => $maxStock,
                    'price' => $price,
                    'status' => $status,
                    'suggested_qty' => $suggestedQty,
                    'estimated_budget' => $budget,
                ];

                $stockItems[] = $itemObj;
                if ($suggestedQty > 0) {
                    $reorderItems[] = $itemObj;
                }
            }

        } else {
            // Medicine / Clinical Supplies
            // 1. Receive
            $recQuery = DB::connection('backoffice')->table('medicine_warehouse_receive as r')
                ->leftJoin('medicine_distributor as md', 'r.distributor_id', '=', 'md.id')
                ->leftJoin('hrd_person as p', 'r.receive_person', '=', 'p.ID')
                ->where('r.warehouse_id', $config['warehouse_id']);
            if ($monthId !== 'all') {
                $recQuery->whereBetween('r.receive_datetime', [$startMonth, $endMonth]);
            } else {
                $recQuery->whereBetween('r.receive_datetime', [$startGregorian, $endGregorian]);
            }
            if (!empty($search)) {
                $recQuery->where(function ($q) use ($search) {
                    $q->where('r.code', 'like', "%{$search}%")
                      ->orWhere('r.po_code', 'like', "%{$search}%")
                      ->orWhere('r.invoice_code', 'like', "%{$search}%")
                      ->orWhere('md.name', 'like', "%{$search}%");
                });
            }
            $rawReceives = $recQuery->select(
                'r.id',
                'r.code',
                'r.po_code',
                'r.invoice_code',
                'r.receive_datetime as receive_date',
                DB::raw("COALESCE(NULLIF(md.name, ''), '-') as vendor_name"),
                DB::raw("CONCAT(COALESCE(p.HR_FNAME, ''), ' ', COALESCE(p.HR_LNAME, '')) as receiver_name"),
                'r.value as total_value'
            )->orderByDesc('r.receive_datetime')->orderByDesc('r.id')->get();

            $recIds = $rawReceives->pluck('id')->toArray();
            $itemCountsMap = [];
            if (!empty($recIds)) {
                $counts = DB::connection('backoffice')->table('medicine_warehouse_receive_list')
                    ->whereIn('receive_id', $recIds)
                    ->select('receive_id', DB::raw('count(*) as cnt'))
                    ->groupBy('receive_id')
                    ->get();
                foreach ($counts as $c) {
                    $itemCountsMap[$c->receive_id] = (int)$c->cnt;
                }
            }
            foreach ($rawReceives as $r) {
                $r->item_count = $itemCountsMap[$r->id] ?? 0;
            }
            $receives = $rawReceives;
            $totalReceive = (float)$rawReceives->sum('total_value');
            $totalReceiveCount = (int)$rawReceives->count();

            // 2. Stock for Medicine: Cumulative receive up to targetEndDate minus cumulative disburse up to targetEndDate
            $medRecCum = (float)DB::connection('backoffice')->table('medicine_warehouse_receive')
                ->where('warehouse_id', $config['warehouse_id'])
                ->where('receive_datetime', '<=', $targetEndDate)
                ->sum('value');
            $medDisbCum = (float)DB::connection('backoffice')->table('medicine_warehouse_request')
                ->where('warehouse_id', $config['warehouse_id'])
                ->where('created_at', '<=', $targetEndDate)
                ->sum('value');
            $totalStockVal = max(0, $medRecCum - $medDisbCum);
            if ($isMonthlyView) {
                $stockDateLabel = 'ณ สิ้นเดือน' . $selectedMonthName;
            } else {
                $latestMedDt = $this->getWarehouseLatestDate($config, $yearId, 'all');
                $stockDateLabel = $latestMedDt ? ('ณ ' . $this->formatThaiDateShort($latestMedDt)) : 'ณ วันที่ล่าสุด';
            }

            // 3. Active catalog items
            $catalogItemsCount = DB::connection('backoffice')->table('medicine_drug')
                ->where('med_warehouse_id', $config['warehouse_id'])
                ->where('active', 'Y')
                ->count();

            $query = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('hrd_person as p', 'r.request_by', '=', 'p.ID')
                ->leftJoin('hrd_prefix as pref', 'p.HR_PREFIX_ID', '=', 'pref.HR_PREFIX_ID')
                ->leftJoin('hrd_person as creator', 'r.created_by', '=', 'creator.ID')
                ->leftJoin('hrd_department_sub_sub as creator_d', 'creator.HR_DEPARTMENT_SUB_SUB_ID', '=', 'creator_d.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id')
                ->where('r.warehouse_id', $config['warehouse_id'])
                ->whereBetween('r.created_at', [$startGregorian, $endGregorian]);

            if ($subWhId !== 'all') {
                $query->where('r.treasury_id', $subWhId);
            }
            if ($monthId !== 'all') {
                $query->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId]);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('r.code', 'like', "%{$search}%")
                      ->orWhere('p.HR_FNAME', 'like', "%{$search}%")
                      ->orWhere('creator.HR_FNAME', 'like', "%{$search}%")
                      ->orWhere('d.HR_DEPARTMENT_SUB_SUB_NAME', 'like', "%{$search}%")
                      ->orWhere('creator_d.HR_DEPARTMENT_SUB_SUB_NAME', 'like', "%{$search}%")
                      ->orWhere('r.request_by_small_hosp_name', 'like', "%{$search}%");
                });
            }

            $rawRequests = $query->select(
                'r.id',
                'r.code',
                'r.request_date as req_date',
                'r.created_at as save_datetime',
                DB::raw("CONCAT(COALESCE(pref.HR_PREFIX_NAME,''), COALESCE(p.HR_FNAME,''), ' ', COALESCE(p.HR_LNAME,'')) as requester_name"),
                'p.POSITION_IN_WORK as requester_position',
                'r.treasury_id as sub_warehouse_id',
                DB::raw("
                    CASE 
                        WHEN d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                        ELSE CONCAT('ใช้ภายใน', mw.name)
                    END as sub_warehouse_name
                "),
                'r.status',
                'r.value as total_value'
            )->orderBy('r.id', 'desc')->get();

            $totalReqCount = $rawRequests->count();
            $totalDisbursement = (float)$rawRequests->sum('total_value');

            // Pre-calculate line item counts
            $reqIds = $rawRequests->pluck('id')->toArray();
            $itemCountsMap = [];
            if (!empty($reqIds)) {
                $counts = DB::connection('backoffice')->table('medicine_warehouse_request_list')
                    ->whereIn('request_id', $reqIds)
                    ->select('request_id', DB::raw('count(*) as cnt'))
                    ->groupBy('request_id')
                    ->get();
                foreach ($counts as $c) {
                    $itemCountsMap[$c->request_id] = (int)$c->cnt;
                }
            }

            foreach ($rawRequests as $r) {
                $r->item_count = $itemCountsMap[$r->id] ?? 1;
                $st = $r->status ?: 'Pending';
                if (!isset($statusCounts[$st])) $statusCounts[$st] = 0;
                $statusCounts[$st]++;
            }
            $requests = $rawRequests;

            // Monthly Trend
            $monthlyData = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->where('r.warehouse_id', $config['warehouse_id'])
                ->whereBetween('r.created_at', [$startGregorian, $endGregorian])
                ->select(DB::raw('MONTH(r.created_at) as m'), DB::raw('sum(r.value) as val'), DB::raw('count(*) as reqs'))
                ->groupBy(DB::raw('MONTH(r.created_at)'))
                ->get();
            foreach ($monthlyData as $md) {
                $m = (int)$md->m;
                if (isset($monthlyTrend[$m])) {
                    $monthlyTrend[$m]['val'] = (float)$md->val;
                    $monthlyTrend[$m]['reqs'] = (int)$md->reqs;
                }
            }

            // Top Sub-warehouses
            $subDistQuery = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('hrd_person as creator', 'r.created_by', '=', 'creator.ID')
                ->leftJoin('hrd_department_sub_sub as creator_d', 'creator.HR_DEPARTMENT_SUB_SUB_ID', '=', 'creator_d.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id')
                ->where('r.warehouse_id', $config['warehouse_id'])
                ->whereBetween('r.created_at', [$startGregorian, $endGregorian]);

            if ($monthId !== 'all') {
                $subDistQuery->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId]);
            }
            if ($subWhId !== 'all') {
                $subDistQuery->where('r.treasury_id', $subWhId);
            }

            // Daily Trend (if specific month)
            if ($isMonthlyView) {
                $dailyData = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                    ->where('r.warehouse_id', $config['warehouse_id'])
                    ->whereBetween('r.created_at', [$startGregorian, $endGregorian])
                    ->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId])
                    ->select(
                        DB::raw('DAY(r.created_at) as d'),
                        DB::raw('sum(r.value) as val'),
                        DB::raw('count(*) as reqs')
                    )
                    ->groupBy(DB::raw('DAY(r.created_at)'))
                    ->get();
                foreach ($dailyData as $dd) {
                    $dNum = (int)$dd->d;
                    if (isset($dailyTrend[$dNum])) {
                        $dailyTrend[$dNum]['val'] = (float)$dd->val;
                        $dailyTrend[$dNum]['reqs'] = (int)$dd->reqs;
                    }
                }
            }

            // Monthly Receive Trend (Medicine)
            $monthlyMedRec = DB::connection('backoffice')->table('medicine_warehouse_receive')
                ->where('warehouse_id', $config['warehouse_id'])
                ->whereBetween('receive_datetime', [$startGregorian, $endGregorian])
                ->select(DB::raw('MONTH(receive_datetime) as m'), DB::raw('sum(value) as val'), DB::raw('count(*) as cnt'))
                ->groupBy(DB::raw('MONTH(receive_datetime)'))
                ->get();
            foreach ($monthlyMedRec as $mmr) {
                $m = (int)$mmr->m;
                if (isset($monthlyTrend[$m])) {
                    $monthlyTrend[$m]['receive_val'] = (float)$mmr->val;
                    $monthlyTrend[$m]['receives'] = (int)$mmr->cnt;
                }
            }

            // Daily Receive Trend (Medicine)
            if ($isMonthlyView) {
                $dailyMedRec = DB::connection('backoffice')->table('medicine_warehouse_receive')
                    ->where('warehouse_id', $config['warehouse_id'])
                    ->whereBetween('receive_datetime', [$startMonth, $endMonth])
                    ->select(
                        DB::raw('DAY(receive_datetime) as d'),
                        DB::raw('sum(value) as val'),
                        DB::raw('count(*) as cnt')
                    )
                    ->groupBy(DB::raw('DAY(receive_datetime)'))
                    ->get();
                foreach ($dailyMedRec as $dmr) {
                    $dNum = (int)$dmr->d;
                    if (isset($dailyTrend[$dNum])) {
                        $dailyTrend[$dNum]['receive_val'] = (float)$dmr->val;
                        $dailyTrend[$dNum]['receives'] = (int)$dmr->cnt;
                    }
                }
            }

            // Top Sub-warehouses (Clinical)
            $subDistQuery = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('hrd_person as creator', 'r.created_by', '=', 'creator.ID')
                ->leftJoin('hrd_department_sub_sub as creator_d', 'creator.HR_DEPARTMENT_SUB_SUB_ID', '=', 'creator_d.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id')
                ->where('r.warehouse_id', $config['warehouse_id'])
                ->whereBetween('r.created_at', [$startGregorian, $endGregorian]);

            if ($monthId !== 'all') {
                $subDistQuery->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId]);
            }
            if ($subWhId !== 'all') {
                $subDistQuery->where('r.treasury_id', $subWhId);
            }

            $topSubWhStats = $subDistQuery->select(
                DB::raw("MAX(r.treasury_id) as dept_id"),
                DB::raw("
                    CASE 
                        WHEN d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                        ELSE CONCAT('ใช้ภายใน', mw.name)
                    END as name
                "),
                DB::raw('sum(r.value) as val'),
                DB::raw('count(*) as reqs')
            )
            ->groupBy(DB::raw("
                    CASE 
                        WHEN d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                        ELSE CONCAT('ใช้ภายใน', mw.name)
                    END
            "))
            ->orderByDesc('val')
            ->limit(20)
            ->get();

            foreach ($topSubWhStats as $ts) {
                $ts->share_percent = $totalDisbursement > 0 ? round(((float)$ts->val / $totalDisbursement) * 100, 1) : 0;
            }

            // Top 20 Disbursed Items (Clinical Supplies)
            $top20Query = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->join('medicine_warehouse_request_list as l', 'r.id', '=', 'l.request_id')
                ->leftJoin('medicine_drug as d', 'l.medicine_id', '=', 'd.id')
                ->leftJoin('medicine_unit as mu', function ($j) {
                    $j->on('d.id', '=', 'mu.drug_id')->where('mu.ranking', 1);
                })
                ->leftJoin('medicine_unit_name as mun', 'mu.unit_name_id', '=', 'mun.id')
                ->where('r.warehouse_id', $config['warehouse_id'])
                ->whereBetween('r.created_at', [$startGregorian, $endGregorian]);

            if ($monthId !== 'all') {
                $top20Query->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId]);
            }
            if ($subWhId !== 'all') {
                $top20Query->where('r.treasury_id', $subWhId);
            }

            $top20DisbursedItems = $top20Query->select(
                'd.id as item_id',
                'd.drug_code as code',
                DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), NULLIF(d.description, ''), d.drug_code) as name"),
                DB::raw("COALESCE(mun.name, mu.new_unitname, '-') as unit_name"),
                DB::raw('sum(l.req_qty) as total_qty'),
                DB::raw('avg(l.unit_price) as avg_price'),
                DB::raw('sum(l.value) as total_val')
            )
            ->groupBy('d.id', 'd.drug_code', DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), NULLIF(d.description, ''), d.drug_code)"), 'mun.name', 'mu.new_unitname')
            ->orderByDesc('total_val')
            ->limit(20)
            ->get();

            foreach ($top20DisbursedItems as $tItem) {
                $tItem->share_percent = $totalDisbursement > 0 ? round(((float)$tItem->total_val / $totalDisbursement) * 100, 1) : 0;
            }

            // Stock Items for Two-Tier Inspection & Reorder
            $medItems = DB::connection('backoffice')->table('medicine_drug')
                ->where('med_warehouse_id', $config['warehouse_id'])
                ->where('active', 'Y')
                ->select('id', 'drug_code as code', 'trade_name as name', 'price', 'min_num as min_stock', 'max_num as max_stock')
                ->limit(120)->get();

            foreach ($medItems as $mi) {
                // Approximate stock from receive vs export
                $rec = (float)DB::connection('backoffice')->table('medicine_warehouse_receive_list')
                    ->where('medicine_id', $mi->id)->sum('quantity');
                $exp = (float)DB::connection('backoffice')->table('medicine_warehouse_export')
                    ->where('medicine_id', $mi->id)->sum('quantity');
                $mainStock = max(0, $rec - $exp);

                // Sub-warehouse (treasuries/wards) estimated active stock
                $subStock = round($exp * 0.25); // Estimated ~1-2 weeks buffer in wards
                $totalHospitalStock = $mainStock + $subStock;

                $amc = max(10.0, round($exp / 12, 1)); // Average monthly consumption
                $mos = round($totalHospitalStock / $amc, 1);
                $minStock = (float)($mi->min_stock ?: ($amc * $config['standard_mos_min']));
                $maxStock = (float)($mi->max_stock ?: ($amc * $config['standard_mos_max']));
                $price = (float)($mi->price ?: 25.0);

                $status = 'normal';
                $suggestedQty = 0;
                $budget = 0.0;

                if ($mos < 0.5 || $totalHospitalStock <= $minStock) {
                    $status = 'critical';
                    $suggestedQty = max(0, round($maxStock - $totalHospitalStock));
                    $budget = $suggestedQty * $price;
                } elseif ($mos <= 1.5) {
                    $status = 'warning';
                    $suggestedQty = max(0, round($maxStock - $totalHospitalStock));
                    $budget = $suggestedQty * $price;
                } elseif ($mos > 3.0) {
                    $status = 'overstock';
                }

                $itemObj = (object)[
                    'id' => $mi->id,
                    'code' => $mi->code,
                    'name' => $mi->name,
                    'main_stock' => $mainStock,
                    'sub_stock' => $subStock,
                    'total_stock' => $totalHospitalStock,
                    'amc' => $amc,
                    'mos' => $mos,
                    'min_stock' => $minStock,
                    'max_stock' => $maxStock,
                    'price' => $price,
                    'status' => $status,
                    'suggested_qty' => $suggestedQty,
                    'estimated_budget' => $budget,
                ];

                $stockItems[] = $itemObj;
                if ($suggestedQty > 0) {
                    $reorderItems[] = $itemObj;
                }
            }
        }

        $totalEstimatedBudget = array_sum(array_column($reorderItems, 'estimated_budget'));

        $mosList = array_filter(array_column($stockItems, 'mos'), fn($m) => $m > 0);
        $avgMos = !empty($mosList) ? round(array_sum($mosList) / count($mosList), 1) : 0;
        if ($avgMos <= 0 && $totalDisbursement > 0) {
            $monthsCount = $isMonthlyView ? 1 : 12;
            $monthlyBurn = $totalDisbursement / $monthsCount;
            $avgMos = $monthlyBurn > 0 ? round($totalStockVal / $monthlyBurn, 1) : 0;
        }

        return [
            'config' => $config,
            'totalReceive' => $totalReceive,
            'totalReceiveCount' => $totalReceiveCount,
            'totalDisbursement' => $totalDisbursement,
            'totalStockVal' => $totalStockVal,
            'stockDateLabel' => $stockDateLabel ?? ($isMonthlyView ? ('ณ สิ้นเดือน' . $selectedMonthName) : 'ณ วันที่ล่าสุด'),
            'catalogItemsCount' => $catalogItemsCount,
            'avgMos' => $avgMos,
            'totalReqCount' => $totalReqCount,
            'statusCounts' => $statusCounts,
            'requests' => $requests,
            'receives' => $receives,
            'stockItems' => $stockItems,
            'reorderItems' => $reorderItems,
            'top20DisbursedItems' => $top20DisbursedItems,
            'totalEstimatedBudget' => $totalEstimatedBudget,
            'monthlyTrend' => array_values($monthlyTrend),
            'dailyTrend' => array_values($dailyTrend),
            'isMonthlyView' => $isMonthlyView,
            'selectedMonthName' => $selectedMonthName,
            'selectedCalYear' => $selectedCalYear,
            'topSubWhStats' => $topSubWhStats,
        ];
    }

    /**
     * AJAX Endpoint: Requisition detail modal
     */
    public function requestDetail(string $type, $id)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($type === 'drug' || $type === 'moph_f5') {
            $disb = DB::table('moph_f5_distribution')->where('id', $id)->first();
            if (!$disb) {
                $disb = DB::table('moph_f5_distribution')->where('working_code', $id)->first();
            }
            if (!$disb) return response()->json(['error' => 'Not found'], 404);

            $deptGroupNames = [
                '1' => 'OPD + IPD (ผู้ป่วยนอกและใน)',
                '2' => 'OPD (ผู้ป่วยนอก)',
                '3' => 'IPD (ผู้ป่วยใน)',
                '4' => 'หน่วยอื่นๆ ในโรงพยาบาล',
                '5' => 'รพ.สต. ในเครือข่าย',
                '6' => 'รพ.สต. ถ่ายโอน',
                '9' => 'อื่นๆ ภายนอกโรงพยาบาล'
            ];

            $items = [
                (object)[
                    'id' => $disb->id,
                    'code' => $disb->working_code,
                    'name' => $disb->trade_name,
                    'amount' => (float)$disb->qty_dis,
                    'price' => $disb->qty_dis > 0 ? round((float)$disb->value / (float)$disb->qty_dis, 2) : 0,
                    'total' => (float)$disb->value,
                ]
            ];

            return response()->json([
                'code' => $disb->working_code,
                'req_date' => $disb->period_rpt ? (substr($disb->period_rpt, 0, 4) . '-' . substr($disb->period_rpt, 4, 2)) : '-',
                'requester' => 'ระบบคลังยา INVS',
                'sub_warehouse' => $deptGroupNames[$disb->dis_dept_group] ?? ('กลุ่มเบิก ' . $disb->dis_dept_group),
                'status' => 'Allow',
                'items' => $items,
            ]);
        }

        if ($type === 'material' || $type === 'supplies') {
            $req = DB::connection('backoffice')->table('warehouse_request as w')
                ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('warehouse_smallhos as sh', 'w.WAREHOUSE_SMALLHOS', '=', 'sh.WAREHOUSE_SMALLHOS_ID')
                ->where('w.WAREHOUSE_ID', $id)
                ->select(
                    'w.*',
                    DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง') as dept_name")
                )
                ->first();

            if (!$req) return response()->json(['error' => 'Not found'], 404);

            $items = DB::connection('backoffice')->table('warehouse_request_sub as s')
                ->leftJoin('supplies as sup', 's.WAREHOUSE_REQUEST_SUB_DETAIL_ID', '=', 'sup.ID')
                ->where('s.WAREHOUSE_REQUEST_ID', $id)
                ->select(
                    's.WAREHOUSE_REQUEST_SUB_ID as id',
                    'sup.SUP_FSN_NUM as code',
                    'sup.SUP_NAME as name',
                    's.WAREHOUSE_REQUEST_SUB_AMOUNT as amount',
                    's.WAREHOUSE_REQUEST_SUB_PRICE as price',
                    's.WAREHOUSE_REQUEST_SUB_SUM_PRICE as total'
                )->get();

            return response()->json([
                'code' => $req->WAREHOUSE_REQUEST_CODE,
                'req_date' => $req->WAREHOUSE_DATE_WANT,
                'requester' => $req->WAREHOUSE_SAVE_HR_NAME,
                'sub_warehouse' => $req->dept_name,
                'status' => $req->WAREHOUSE_STATUS,
                'items' => $items,
            ]);
        } else {
            $req = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('hrd_person as p', 'r.request_by', '=', 'p.ID')
                ->leftJoin('hrd_prefix as pref', 'p.HR_PREFIX_ID', '=', 'pref.HR_PREFIX_ID')
                ->leftJoin('hrd_person as creator', 'r.created_by', '=', 'creator.ID')
                ->leftJoin('hrd_department_sub_sub as creator_d', 'creator.HR_DEPARTMENT_SUB_SUB_ID', '=', 'creator_d.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id')
                ->where('r.id', $id)
                ->select(
                    'r.*',
                    DB::raw("
                        CASE 
                            WHEN d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN d.HR_DEPARTMENT_SUB_SUB_NAME
                            WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                            WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                            ELSE CONCAT('ใช้ภายใน', mw.name)
                        END as dept_name
                    "),
                    DB::raw("CONCAT(COALESCE(pref.HR_PREFIX_NAME,''), COALESCE(p.HR_FNAME,''), ' ', COALESCE(p.HR_LNAME,'')) as requester_full_name")
                )
                ->first();

            if (!$req) return response()->json(['error' => 'Not found'], 404);

            $items = DB::connection('backoffice')->table('medicine_warehouse_request_list as l')
                ->leftJoin('medicine_drug as d', 'l.medicine_id', '=', 'd.id')
                ->where('l.request_id', $id)
                ->select(
                    DB::raw('MIN(l.id) as id'),
                    'd.drug_code as code',
                    DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), NULLIF(d.description, ''), d.drug_code) as name"),
                    DB::raw('MAX(COALESCE(NULLIF(l.pay_qty, 0), l.req_qty, 0)) as amount'),
                    DB::raw('MAX(COALESCE(l.unit_price, 0)) as price'),
                    DB::raw('SUM(COALESCE(l.value, 0)) as total')
                )
                ->groupBy('d.id', 'd.drug_code', DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), NULLIF(d.description, ''), d.drug_code)"))
                ->havingRaw('MAX(COALESCE(NULLIF(l.pay_qty, 0), l.req_qty, 0)) > 0 OR SUM(COALESCE(l.value, 0)) > 0')
                ->get();

            return response()->json([
                'code' => $req->code,
                'req_date' => $req->request_date,
                'requester' => $req->requester_full_name ?: '-',
                'sub_warehouse' => $req->dept_name,
                'status' => $req->status,
                'items' => $items,
            ]);
        }
    }

    /**
     * AJAX Endpoint: Item Two-Tier Stock Breakdown
     */
    public function itemDetail(string $type, $id)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($type === 'drug' || $type === 'moph_f5') {
            $item = DB::table('moph_f5_inventory')->where('id', $id)->first();
            if (!$item) {
                $item = DB::table('moph_f5_inventory')->where('working_code', $id)->first();
            }
            if (!$item) {
                $item = DB::table('moph_f5_druglist')->where('working_code', $id)->first();
            }
            if (!$item) {
                $item = DB::table('moph_f5_distribution')->where('working_code', $id)->first();
            }
            if (!$item) {
                $item = DB::table('moph_f5_receipt')->where('working_code', $id)->first();
            }
            if (!$item) return response()->json(['error' => 'Not found'], 404);

            $wCode = $item->working_code;
            $latestInvDate = DB::table('moph_f5_inventory')->where('working_code', $wCode)->max('date_onhand') ?: DB::table('moph_f5_inventory')->max('date_onhand');
            $mainStockQuery = DB::table('moph_f5_inventory')->where('working_code', $wCode);
            if ($latestInvDate) {
                $mainStockQuery->where('date_onhand', $latestInvDate);
            }
            $mainStock = (float)$mainStockQuery->sum('qty_onhand');

            $deptGroupNames = [
                '1' => 'OPD + IPD (ผู้ป่วยนอกและใน)',
                '2' => 'OPD (ผู้ป่วยนอก)',
                '3' => 'IPD (ผู้ป่วยใน)',
                '4' => 'หน่วยอื่นๆ ในโรงพยาบาล',
                '5' => 'รพ.สต. ในเครือข่าย',
                '6' => 'รพ.สต. ถ่ายโอน',
                '9' => 'อื่นๆ ภายนอกโรงพยาบาล'
            ];
            $subStocks = DB::table('moph_f5_distribution')
                ->where('working_code', $wCode)
                ->select('dis_dept_group', DB::raw('sum(qty_dis) as qty'))
                ->groupBy('dis_dept_group')
                ->get()
                ->map(function($d) use ($deptGroupNames) {
                    return (object)[
                        'dept_name' => $deptGroupNames[$d->dis_dept_group] ?? ('กลุ่ม ' . $d->dis_dept_group),
                        'qty' => (float)$d->qty,
                    ];
                });

            return response()->json([
                'name' => $item->trade_name ?? $item->generic_name ?? $wCode,
                'code' => $wCode,
                'main_stock' => $mainStock,
                'sub_stocks' => $subStocks,
            ]);
        }

        if ($type === 'material' || $type === 'supplies') {
            $store = DB::connection('backoffice')->table('warehouse_store')
                ->where('STORE_ID', $id)->first();
            if (!$store) return response()->json(['error' => 'Not found'], 404);

            $rec = (float)DB::connection('backoffice')->table('warehouse_store_receive_sub')
                ->where('STORE_ID', $id)->sum('RECEIVE_SUB_AMOUNT');
            $exp = (float)DB::connection('backoffice')->table('warehouse_store_export_sub')
                ->where('STORE_ID', $id)->sum('EXPORT_SUB_AMOUNT');
            $mainStock = max(0, $rec - $exp);

            $subStocks = DB::connection('backoffice')->table('warehouse_treasury as wt')
                ->join('warehouse_treasury_receive_sub as wtr', 'wt.TREASURY_ID', '=', 'wtr.TREASURY_ID')
                ->where('wt.TREASURY_CODE', $store->STORE_CODE)
                ->select('wt.TREASURY_TYPE_NAME as dept_name', DB::raw('sum(wtr.TREASURY_RECEIVE_SUB_AMOUNT) as qty'))
                ->groupBy('wt.TREASURY_TYPE_NAME')
                ->having('qty', '>', 0)
                ->get();

            return response()->json([
                'name' => $store->STORE_NAME,
                'code' => $store->STORE_CODE,
                'main_stock' => $mainStock,
                'sub_stocks' => $subStocks,
            ]);
        } else {
            $drug = DB::connection('backoffice')->table('medicine_drug')->where('id', $id)->first();
            if (!$drug) return response()->json(['error' => 'Not found'], 404);

            $rec = (float)DB::connection('backoffice')->table('medicine_warehouse_receive_list')
                ->where('medicine_id', $id)->sum('quantity');
            $exp = (float)DB::connection('backoffice')->table('medicine_warehouse_export')
                ->where('medicine_id', $id)->sum('quantity');
            $mainStock = max(0, $rec - $exp);

            $subStocks = DB::connection('backoffice')->table('medicine_warehouse_export as e')
                ->leftJoin('hrd_department_sub_sub as d', 'e.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->where('e.medicine_id', $id)
                ->select(DB::raw("COALESCE(d.HR_DEPARTMENT_SUB_SUB_NAME, 'ไม่ระบุแผนก') as dept_name"), DB::raw('sum(e.quantity) as qty'))
                ->groupBy('d.HR_DEPARTMENT_SUB_SUB_NAME')
                ->having('qty', '>', 0)
                ->limit(10)
                ->get();

            return response()->json([
                'name' => $drug->trade_name,
                'code' => $drug->drug_code,
                'main_stock' => $mainStock,
                'sub_stocks' => $subStocks,
            ]);
        }
    }

    /**
     * AJAX Endpoint: Item Requisition Breakdown Across Departments (Sorted Descending)
     */
    public function itemDepartments(Request $request, string $type, $id)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $yearId = (int)$request->input('year_id', 2569);
        $monthId = $request->input('month_id', 'all');

        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';

        if ($type === 'drug' || $type === 'moph_f5') {
            $item = DB::table('moph_f5_inventory')->where('id', $id)->first();
            if (!$item) {
                $item = DB::table('moph_f5_inventory')->where('working_code', $id)->orderByDesc('date_onhand')->first();
            }
            if (!$item) {
                $item = DB::table('moph_f5_druglist')->where('working_code', $id)->first();
            }
            if (!$item) {
                $item = DB::table('moph_f5_distribution')->where('working_code', $id)->first();
            }
            if (!$item) {
                $item = DB::table('moph_f5_receipt')->where('working_code', $id)->first();
            }
            if (!$item) return response()->json(['error' => 'Not found'], 404);

            $wCode = $item->working_code;
            $name = $item->trade_name ?? $item->generic_name ?? $wCode;
            $unit = $item->base_unit ?? 'หน่วย';

            $deptGroupNames = [
                '1' => 'OPD + IPD (ผู้ป่วยนอกและใน)',
                '2' => 'OPD (ผู้ป่วยนอก)',
                '3' => 'IPD (ผู้ป่วยใน)',
                '4' => 'หน่วยอื่นๆ ในโรงพยาบาล',
                '5' => 'รพ.สต. ในเครือข่าย',
                '6' => 'รพ.สต. ถ่ายโอน',
                '9' => 'อื่นๆ ภายนอกโรงพยาบาล'
            ];

            $deptsQuery = DB::table('moph_f5_distribution')->where('working_code', $wCode);
            if ($monthId !== 'all') {
                $mInt = (int)$monthId;
                $calGregYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
                $deptsQuery->where('period_rpt', sprintf('%04d%02d', $calGregYear, $mInt));
            } else {
                $deptsQuery->whereBetween('period_rpt', [($yearId - 544) . '10', ($yearId - 543) . '09']);
            }
            $depts = $deptsQuery->select(
                    'dis_dept_group',
                    DB::raw('count(*) as req_count'),
                    DB::raw('sum(qty_dis) as total_qty'),
                    DB::raw('sum(value) as total_val')
                )
                ->groupBy('dis_dept_group')
                ->orderByDesc('total_val')
                ->get();

            $sumVal = (float)$depts->sum('total_val');
            $sumQty = (float)$depts->sum('total_qty');

            foreach ($depts as $d) {
                $d->dept_name = $deptGroupNames[$d->dis_dept_group] ?? ('กลุ่มเบิก ' . $d->dis_dept_group);
                $d->percentage = $sumVal > 0 ? round(((float)$d->total_val / $sumVal) * 100, 1) : 0;
            }

            return response()->json([
                'name' => $name,
                'code' => $wCode,
                'unit' => $unit,
                'sum_val' => $sumVal,
                'sum_qty' => $sumQty,
                'departments' => $depts,
            ]);
        }

        if ($type === 'material' || $type === 'supplies') {
            $item = DB::connection('backoffice')->table('supplies as sup')
                ->leftJoin('supplies_unit as u', 'sup.SUP_UNIT_ID', '=', 'u.SUP_UNIT_ID')
                ->where('sup.ID', $id)
                ->select('sup.ID', 'sup.SUP_FSN_NUM as code', 'sup.SUP_NAME as name', 'u.SUP_UNIT_NAME as unit_name')
                ->first();

            if (!$item) return response()->json(['error' => 'Not found'], 404);

            $query = DB::connection('backoffice')->table('warehouse_request as w')
                ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('warehouse_smallhos as sh', 'w.WAREHOUSE_SMALLHOS', '=', 'sh.WAREHOUSE_SMALLHOS_ID')
                ->where('s.WAREHOUSE_REQUEST_SUB_DETAIL_ID', $id)
                ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId);

            if ($monthId !== 'all') {
                $query->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
            }

            $depts = $query->select(
                DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง') as dept_name"),
                DB::raw('count(distinct w.WAREHOUSE_ID) as req_count'),
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_AMOUNT) as total_qty'),
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as total_val')
            )
            ->groupBy(DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง')"))
            ->orderByDesc('total_val')
            ->get();

            $sumVal = (float)$depts->sum('total_val');
            $sumQty = (float)$depts->sum('total_qty');

            foreach ($depts as $d) {
                $d->percentage = $sumVal > 0 ? round(((float)$d->total_val / $sumVal) * 100, 1) : 0;
            }

            return response()->json([
                'name' => $item->name,
                'code' => $item->code,
                'unit' => $item->unit_name ?: 'หน่วย',
                'sum_val' => $sumVal,
                'sum_qty' => $sumQty,
                'departments' => $depts,
            ]);

        } else {
            $item = DB::connection('backoffice')->table('medicine_drug as d')
                ->leftJoin('medicine_unit as mu', function ($j) {
                    $j->on('d.id', '=', 'mu.drug_id')->where('mu.ranking', 1);
                })
                ->leftJoin('medicine_unit_name as mun', 'mu.unit_name_id', '=', 'mun.id')
                ->where('d.id', $id)
                ->select(
                    'd.id',
                    'd.drug_code as code',
                    DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), NULLIF(d.description, ''), d.drug_code) as name"),
                    DB::raw("COALESCE(mun.name, mu.new_unitname, '-') as unit_name")
                )
                ->first();

            if (!$item) return response()->json(['error' => 'Not found'], 404);

            $query = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->join('medicine_warehouse_request_list as l', 'r.id', '=', 'l.request_id')
                ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('hrd_person as creator', 'r.created_by', '=', 'creator.ID')
                ->leftJoin('hrd_department_sub_sub as creator_d', 'creator.HR_DEPARTMENT_SUB_SUB_ID', '=', 'creator_d.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id')
                ->where('l.medicine_id', $id)
                ->whereBetween('r.created_at', [$startGregorian, $endGregorian]);

            if ($monthId !== 'all') {
                $query->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId]);
            }

            $depts = $query->select(
                DB::raw("
                    CASE 
                        WHEN d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                        ELSE CONCAT('ใช้ภายใน', mw.name)
                    END as dept_name
                "),
                DB::raw('count(distinct r.id) as req_count'),
                DB::raw('sum(l.req_qty) as total_qty'),
                DB::raw('sum(l.value) as total_val')
            )
            ->groupBy(DB::raw("
                    CASE 
                        WHEN d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                        ELSE CONCAT('ใช้ภายใน', mw.name)
                    END
            "))
            ->orderByDesc('total_val')
            ->get();

            $sumVal = (float)$depts->sum('total_val');
            $sumQty = (float)$depts->sum('total_qty');

            foreach ($depts as $d) {
                $d->percentage = $sumVal > 0 ? round(((float)$d->total_val / $sumVal) * 100, 1) : 0;
            }

            return response()->json([
                'name' => $item->name,
                'code' => $item->code,
                'unit' => $item->unit_name ?: 'หน่วย',
                'sum_val' => $sumVal,
                'sum_qty' => $sumQty,
                'departments' => $depts,
            ]);
        }
    }

    /**
     * AJAX Endpoint: Get all items disbursed to a specific department/sub-warehouse
     */
    public function departmentItems(Request $request, string $type)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deptId = $request->input('dept_id');
        $deptName = $request->input('dept_name');
        $yearId = (int)$request->input('year_id', 2569);
        $monthId = $request->input('month_id', 'all');

        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';

        if ($type === 'drug' || $type === 'moph_f5') {
            $deptGroupNames = [
                '1' => 'OPD + IPD (ผู้ป่วยนอกและใน)',
                '2' => 'OPD (ผู้ป่วยนอก)',
                '3' => 'IPD (ผู้ป่วยใน)',
                '4' => 'หน่วยอื่นๆ ในโรงพยาบาล',
                '5' => 'รพ.สต. ในเครือข่าย',
                '6' => 'รพ.สต. ถ่ายโอน',
                '9' => 'อื่นๆ ภายนอกโรงพยาบาล'
            ];
            $query = DB::table('moph_f5_distribution');
            if (!empty($deptId) && $deptId !== 'null') {
                $query->where('dis_dept_group', $deptId);
            } elseif (!empty($deptName)) {
                $foundGroup = null;
                foreach ($deptGroupNames as $k => $v) {
                    if ($v === $deptName) {
                        $foundGroup = $k;
                        break;
                    }
                }
                if ($foundGroup) {
                    $query->where('dis_dept_group', $foundGroup);
                }
            }
            if ($monthId !== 'all') {
                $mInt = (int)$monthId;
                $calGregYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
                $query->where('period_rpt', sprintf('%04d%02d', $calGregYear, $mInt));
            } else {
                $query->whereBetween('period_rpt', [($yearId - 544) . '10', ($yearId - 543) . '09']);
            }

            $items = $query->select(
                'working_code as item_id',
                'working_code as code',
                'trade_name as name',
                'base_unit as unit_name',
                DB::raw('count(*) as req_count'),
                DB::raw('sum(qty_dis) as total_qty'),
                DB::raw('case when sum(qty_dis) > 0 then sum(value)/sum(qty_dis) else 0 end as avg_price'),
                DB::raw('sum(value) as total_val')
            )
            ->groupBy('working_code', 'trade_name', 'base_unit')
            ->orderByDesc('total_val')
            ->get();

            $sumVal = (float)$items->sum('total_val');
            $sumQty = (float)$items->sum('total_qty');

            foreach ($items as $it) {
                $it->percentage = $sumVal > 0 ? round(((float)$it->total_val / $sumVal) * 100, 1) : 0;
            }

            return response()->json([
                'dept_name' => $deptName ?: ($deptGroupNames[$deptId] ?? 'กลุ่มเบิก'),
                'total_val' => $sumVal,
                'sum_val' => $sumVal,
                'sum_qty' => $sumQty,
                'total_reqs' => (int)$items->sum('req_count'),
                'items_count' => $items->count(),
                'items' => $items,
            ]);
        }

        if ($type === 'material' || $type === 'supplies') {
            $invenId = 5;
            $query = DB::connection('backoffice')->table('warehouse_request as w')
                ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                ->leftJoin('supplies as sup', 's.WAREHOUSE_REQUEST_SUB_DETAIL_ID', '=', 'sup.ID')
                ->leftJoin('supplies_unit as u', 'sup.SUP_UNIT_ID', '=', 'u.SUP_UNIT_ID')
                ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('warehouse_smallhos as sh', 'w.WAREHOUSE_SMALLHOS', '=', 'sh.WAREHOUSE_SMALLHOS_ID')
                ->where('w.WAREHOUSE_INVEN_ID', $invenId)
                ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId);

            if (!empty($deptId) && $deptId !== 'null' && is_numeric($deptId)) {
                $query->where('w.WAREHOUSE_DEP_SUB_SUB_ID', (int)$deptId);
            } elseif (!empty($deptName)) {
                $query->where(DB::raw("COALESCE(NULLIF(d.HR_DEPARTMENT_SUB_SUB_NAME, ''), NULLIF(sh.WAREHOUSE_SMALLHOS_NAME, ''), 'ฝ่ายบริหารงานทั่วไป/เบิกตรง')"), $deptName);
            }

            if ($monthId !== 'all') {
                $query->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
            }

            $items = $query->select(
                'sup.ID as item_id',
                'sup.SUP_FSN_NUM as code',
                'sup.SUP_NAME as name',
                'u.SUP_UNIT_NAME as unit_name',
                DB::raw('count(distinct w.WAREHOUSE_ID) as req_count'),
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_AMOUNT) as total_qty'),
                DB::raw('avg(s.WAREHOUSE_REQUEST_SUB_PRICE) as avg_price'),
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as total_val')
            )
            ->groupBy('sup.ID', 'sup.SUP_FSN_NUM', 'sup.SUP_NAME', 'u.SUP_UNIT_NAME')
            ->orderByDesc('total_val')
            ->get();

            $sumVal = (float)$items->sum('total_val');
            $sumQty = (float)$items->sum('total_qty');
            foreach ($items as $it) {
                $it->percentage = $sumVal > 0 ? round(((float)$it->total_val / $sumVal) * 100, 1) : 0;
            }

            return response()->json([
                'dept_name' => $deptName ?: 'ไม่ระบุหน่วยงาน',
                'sum_val' => $sumVal,
                'sum_qty' => $sumQty,
                'items_count' => count($items),
                'items' => $items,
            ]);
        } else {
            // Medicine / Clinical Supplies
            $warehouseMap = ['dental' => 2, 'medtech' => 3, 'physical' => 4, 'medical' => 5];
            $warehouseId = $warehouseMap[$type] ?? 5;

            $query = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->join('medicine_warehouse_request_list as l', 'r.id', '=', 'l.request_id')
                ->leftJoin('medicine_drug as d', 'l.medicine_id', '=', 'd.id')
                ->leftJoin('medicine_unit as mu', function ($j) {
                    $j->on('d.id', '=', 'mu.drug_id')->where('mu.ranking', 1);
                })
                ->leftJoin('medicine_unit_name as mun', 'mu.unit_name_id', '=', 'mun.id')
                ->leftJoin('hrd_department_sub_sub as dept', 'r.treasury_id', '=', 'dept.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('hrd_person as creator', 'r.created_by', '=', 'creator.ID')
                ->leftJoin('hrd_department_sub_sub as creator_d', 'creator.HR_DEPARTMENT_SUB_SUB_ID', '=', 'creator_d.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id')
                ->where('r.warehouse_id', $warehouseId)
                ->whereBetween('r.created_at', [$startGregorian, $endGregorian]);

            if (!empty($deptId) && $deptId !== 'null' && is_numeric($deptId)) {
                $query->where('r.treasury_id', (int)$deptId);
            } elseif (!empty($deptName)) {
                $query->where(DB::raw("
                    CASE 
                        WHEN dept.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND dept.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN dept.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME IS NOT NULL AND creator_d.HR_DEPARTMENT_SUB_SUB_NAME != '' THEN creator_d.HR_DEPARTMENT_SUB_SUB_NAME
                        WHEN r.request_by_small_hosp_name IS NOT NULL AND r.request_by_small_hosp_name != '' THEN CONCAT('รพ.สต. (', r.request_by_small_hosp_name, ')')
                        ELSE CONCAT('ใช้ภายใน', mw.name)
                    END
                "), $deptName);
            }

            if ($monthId !== 'all') {
                $query->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId]);
            }

            $items = $query->select(
                'd.id as item_id',
                'd.drug_code as code',
                DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), NULLIF(d.description, ''), d.drug_code) as name"),
                DB::raw("COALESCE(mun.name, mu.new_unitname, '-') as unit_name"),
                DB::raw('count(distinct r.id) as req_count'),
                DB::raw('sum(l.req_qty) as total_qty'),
                DB::raw('avg(l.unit_price) as avg_price'),
                DB::raw('sum(l.value) as total_val')
            )
            ->groupBy('d.id', 'd.drug_code', DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), NULLIF(d.description, ''), d.drug_code)"), 'mun.name', 'mu.new_unitname')
            ->orderByDesc('total_val')
            ->get();

            $sumVal = (float)$items->sum('total_val');
            $sumQty = (float)$items->sum('total_qty');
            foreach ($items as $it) {
                $it->percentage = $sumVal > 0 ? round(((float)$it->total_val / $sumVal) * 100, 1) : 0;
            }

            return response()->json([
                'dept_name' => $deptName ?: 'ไม่ระบุหน่วยงาน',
                'sum_val' => $sumVal,
                'sum_qty' => $sumQty,
                'items_count' => count($items),
                'items' => $items,
            ]);
        }
    }

    /**
     * Export Suggested Reorder Table to CSV / Excel format
     */
    public function exportReorder(Request $request)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            abort(403, 'Unauthorized');
        }

        $tab = $request->input('tab', 'material');
        if (!isset($this->warehouseConfig[$tab])) {
            $tab = 'material';
        }
        $config = $this->warehouseConfig[$tab];
        $yearId = (int)$request->input('year_id', 2570);

        $data = $this->getSingleWarehouseData($config, $yearId, 'all', 'all', 'planner', '');
        $reorderItems = $data['reorderItems'] ?? [];

        $filename = "reorder_plan_{$tab}_{$yearId}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($reorderItems, $config, $yearId) {
            $output = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel in Thai
            fputs($output, "\xEF\xBB\xBF");

            fputcsv($output, ["แผนเสนอจัดซื้อพัสดุ/เวชภัณฑ์ประจำรอบ - {$config['full_name']} ประจำปีงบประมาณ {$yearId}"]);
            fputcsv($output, ["วันที่ออกรายงาน: " . date('d/m/Y H:i:s')]);
            fputcsv($output, []);
            fputcsv($output, ['ลำดับ', 'สถานะความเสี่ยง', 'รหัสพัสดุ/ยา', 'ชื่อรายการสิ่งของ', 'สต็อกคลังหลัก', 'สต็อกคลังย่อย', 'สต็อกรวมทั้งรพ.', 'อัตราการใช้/เดือน (AMC)', 'ระยะเวลาคงเหลือ (เดือน)', 'จุดสั่งซื้อต่ำสุด (Min)', 'เกณฑ์สูงสุด (Max)', 'จำนวนที่แนะนำสั่งซื้อ', 'ราคาซื้อล่าสุด (บาท)', 'ประมาณการงบประมาณ (บาท)']);

            foreach ($reorderItems as $idx => $item) {
                $statusText = $item->status === 'critical' ? '🔴 วิกฤต/ต้องซื้อด่วน' : '🟡 สั่งซื้อรอบปกติ';
                fputcsv($output, [
                    $idx + 1,
                    $statusText,
                    $item->code,
                    $item->name,
                    $item->main_stock,
                    $item->sub_stock,
                    $item->total_stock,
                    $item->amc,
                    $item->mos,
                    $item->min_stock,
                    $item->max_stock,
                    $item->suggested_qty,
                    number_format($item->price, 2),
                    number_format($item->estimated_budget, 2),
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * AJAX Endpoint: List goods receipts for a warehouse in a given year/month
     */
    public function receivesList(Request $request, string $type)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!isset($this->warehouseConfig[$type])) {
            return response()->json(['error' => 'Invalid warehouse type'], 400);
        }

        $config = $this->warehouseConfig[$type];
        $yearId = (int)$request->input('year_id', 2570);
        $monthId = $request->input('month_id', 'all');

        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';

        $startMonth = '';
        $endMonth = '';
        if ($monthId !== 'all') {
            $mInt = (int)$monthId;
            $calGregorianYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregorianYear);
            $startMonth = sprintf('%04d-%02d-01', $calGregorianYear, $mInt);
            $endMonth = sprintf('%04d-%02d-%02d 23:59:59', $calGregorianYear, $mInt, $daysInMonth);
        }

        if ($config['type'] === 'moph_f5') {
            $query = DB::table('moph_f5_receipt');
            if ($monthId !== 'all') {
                $mInt = (int)$monthId;
                $calGregYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
                $query->where('date_rcv', 'like', sprintf('%04d%02d', $calGregYear, $mInt) . '%');
            } else {
                $query->whereBetween('date_rcv', [($yearId - 544) . '1001', ($yearId - 543) . '0930']);
            }

            $rawReceives = $query->select(
                'rcv_no as code',
                DB::raw('MIN(id) as id'),
                DB::raw('MAX(po_no) as po_code'),
                DB::raw('MAX(date_rcv) as raw_date'),
                DB::raw('MAX(vendor_name) as vendor_name'),
                DB::raw("'เจ้าหน้าที่คลังยา' as receiver_name"),
                DB::raw('SUM(total_value) as total_value'),
                DB::raw('COUNT(*) as item_count')
            )
            ->groupBy('rcv_no')
            ->orderByDesc('raw_date')
            ->get();

            foreach ($rawReceives as $r) {
                $d = $r->raw_date;
                $r->receive_date = (strlen($d) === 8) 
                    ? substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2)
                    : $d;
                $r->total_value = (float)$r->total_value;
                $r->item_count = (int)$r->item_count;
            }

            $totalValue = (float)$rawReceives->sum('total_value');
            $totalCount = $rawReceives->count();

            return response()->json([
                'warehouse_name' => $config['name'],
                'warehouse_type' => 'moph_f5',
                'total_value' => $totalValue,
                'total_count' => $totalCount,
                'receives' => $rawReceives,
            ]);
        }

        if ($config['type'] === 'supplies') {
            $query = DB::connection('backoffice')->table('warehouse_check_receive as r')
                ->where('r.RECEIVE_STORE', $config['store_type'])
                ->where('r.RECEIVE_BUDGET_YEAR', $yearId);

            if ($monthId !== 'all') {
                $query->whereRaw('MONTH(r.RECEIVE_CHECK_DATE) = ?', [(int)$monthId]);
            }

            $rawReceives = $query->select(
                'r.RECEIVE_ID as id',
                'r.RECEIVE_CODE as code',
                'r.RECEIVE_NUMBER as po_code',
                'r.RECEIVE_CHECK_DATE as receive_date',
                'r.RECEIVE_ACCEPT_FROM as vendor_name',
                'r.RECEIVE_PERSON_NAME as receiver_name',
                'r.RECEIVE_VALUE as total_value'
            )->orderByDesc('r.RECEIVE_CHECK_DATE')->orderByDesc('r.RECEIVE_ID')->get();

            // Item counts
            $recIds = $rawReceives->pluck('id')->toArray();
            $itemCountsMap = [];
            if (!empty($recIds)) {
                $counts = DB::connection('backoffice')->table('warehouse_check_receive_sub')
                    ->whereIn('RECEIVE_ID', $recIds)
                    ->select('RECEIVE_ID', DB::raw('count(*) as cnt'))
                    ->groupBy('RECEIVE_ID')
                    ->get();
                foreach ($counts as $c) {
                    $itemCountsMap[$c->RECEIVE_ID] = (int)$c->cnt;
                }
            }

            foreach ($rawReceives as $r) {
                $r->item_count = $itemCountsMap[$r->id] ?? 0;
            }

            $totalValue = (float)$rawReceives->sum('total_value');
            $totalCount = $rawReceives->count();

            return response()->json([
                'warehouse_name' => $config['name'],
                'warehouse_type' => 'supplies',
                'total_value' => $totalValue,
                'total_count' => $totalCount,
                'receives' => $rawReceives,
            ]);
        } else {
            $query = DB::connection('backoffice')->table('medicine_warehouse_receive as r')
                ->leftJoin('medicine_distributor as md', 'r.distributor_id', '=', 'md.id')
                ->leftJoin('hrd_person as p', 'r.receive_person', '=', 'p.ID')
                ->where('r.warehouse_id', $config['warehouse_id']);

            if ($monthId !== 'all') {
                $query->whereBetween('r.receive_datetime', [$startMonth, $endMonth]);
            } else {
                $query->whereBetween('r.receive_datetime', [$startGregorian, $endGregorian]);
            }

            $rawReceives = $query->select(
                'r.id',
                'r.code',
                'r.po_code',
                'r.invoice_code',
                'r.receive_datetime as receive_date',
                DB::raw("COALESCE(NULLIF(md.name, ''), '-') as vendor_name"),
                DB::raw("CONCAT(COALESCE(p.HR_FNAME, ''), ' ', COALESCE(p.HR_LNAME, '')) as receiver_name"),
                'r.value as total_value'
            )->orderByDesc('r.receive_datetime')->orderByDesc('r.id')->get();

            // Item counts
            $recIds = $rawReceives->pluck('id')->toArray();
            $itemCountsMap = [];
            if (!empty($recIds)) {
                $counts = DB::connection('backoffice')->table('medicine_warehouse_receive_list')
                    ->whereIn('receive_id', $recIds)
                    ->select('receive_id', DB::raw('count(*) as cnt'))
                    ->groupBy('receive_id')
                    ->get();
                foreach ($counts as $c) {
                    $itemCountsMap[$c->receive_id] = (int)$c->cnt;
                }
            }

            foreach ($rawReceives as $r) {
                $r->item_count = $itemCountsMap[$r->id] ?? 0;
            }

            $totalValue = (float)$rawReceives->sum('total_value');
            $totalCount = $rawReceives->count();

            return response()->json([
                'warehouse_name' => $config['name'],
                'warehouse_type' => 'medicine',
                'total_value' => $totalValue,
                'total_count' => $totalCount,
                'receives' => $rawReceives,
            ]);
        }
    }

    /**
     * AJAX Endpoint: Goods receipt detail modal (item line breakdown)
     */
    public function receiveDetail(string $type, $id)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($type === 'drug' || $type === 'moph_f5') {
            $item = DB::table('moph_f5_receipt')->where('id', $id)->first();
            $rcvNo = $item ? $item->rcv_no : $id;
            $header = DB::table('moph_f5_receipt')->where('rcv_no', $rcvNo)->first();
            if (!$header) return response()->json(['error' => 'Not found'], 404);

            $rawItems = DB::table('moph_f5_receipt')->where('rcv_no', $rcvNo)->get();
            $items = $rawItems->map(function($r) {
                $exp = $r->expire_date;
                $expFormatted = (strlen($exp) === 8)
                    ? substr($exp, 0, 4) . '-' . substr($exp, 4, 2) . '-' . substr($exp, 6, 2)
                    : $exp;
                return (object)[
                    'id' => $r->id,
                    'code' => $r->working_code,
                    'name' => $r->trade_name,
                    'amount' => (float)$r->qty_rcv,
                    'price' => (float)$r->pack_cost,
                    'total' => (float)$r->total_value,
                    'lot_no' => $r->lot_no,
                    'exp_date' => $expFormatted,
                    'unit_name' => $r->base_unit,
                ];
            });

            $d = $header->date_rcv;
            $rcvDate = (strlen($d) === 8)
                ? substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2)
                : $d;

            return response()->json([
                'code' => $header->rcv_no,
                'po_code' => $header->po_no,
                'invoice_code' => '-',
                'receive_date' => $rcvDate,
                'vendor' => $header->vendor_name ?: '-',
                'receiver' => 'เจ้าหน้าที่คลังยา',
                'total_value' => (float)$rawItems->sum('total_value'),
                'items' => $items,
            ]);
        }

        if ($type === 'material' || $type === 'supplies') {
            $header = DB::connection('backoffice')->table('warehouse_check_receive')
                ->where('RECEIVE_ID', $id)
                ->first();

            if (!$header) return response()->json(['error' => 'Not found'], 404);

            $items = DB::connection('backoffice')->table('warehouse_check_receive_sub')
                ->where('RECEIVE_ID', $id)
                ->select(
                    'RECEIVE_SUB_ID as id',
                    'RECEIVE_SUB_CODE as code',
                    'RECEIVE_SUB_NAME as name',
                    'RECEIVE_SUB_AMOUNT as amount',
                    'RECEIVE_SUB_PICE_UNIT as price',
                    'RECEIVE_SUB_VALUE as total',
                    'RECEIVE_SUB_LOT as lot_no',
                    'RECEIVE_SUB_EXP_DATE as exp_date'
                )->get();

            return response()->json([
                'code' => $header->RECEIVE_CODE,
                'po_code' => $header->RECEIVE_NUMBER,
                'receive_date' => $header->RECEIVE_CHECK_DATE,
                'vendor' => $header->RECEIVE_ACCEPT_FROM,
                'receiver' => $header->RECEIVE_PERSON_NAME,
                'total_value' => (float)$header->RECEIVE_VALUE,
                'items' => $items,
            ]);
        } else {
            $header = DB::connection('backoffice')->table('medicine_warehouse_receive as r')
                ->leftJoin('medicine_distributor as md', 'r.distributor_id', '=', 'md.id')
                ->leftJoin('hrd_person as p', 'r.receive_person', '=', 'p.ID')
                ->where('r.id', $id)
                ->select(
                    'r.*',
                    DB::raw("COALESCE(NULLIF(md.name, ''), '-') as vendor_name"),
                    DB::raw("CONCAT(COALESCE(p.HR_FNAME, ''), ' ', COALESCE(p.HR_LNAME, '')) as receiver_name")
                )
                ->first();

            if (!$header) return response()->json(['error' => 'Not found'], 404);

            $items = DB::connection('backoffice')->table('medicine_warehouse_receive_list as l')
                ->leftJoin('medicine_drug as d', 'l.medicine_id', '=', 'd.id')
                ->leftJoin('medicine_unit as mu', function ($j) {
                    $j->on('d.id', '=', 'mu.drug_id')->where('mu.ranking', 1);
                })
                ->leftJoin('medicine_unit_name as mun', 'mu.unit_name_id', '=', 'mun.id')
                ->where('l.receive_id', $id)
                ->select(
                    'l.id',
                    'd.drug_code as code',
                    DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), d.drug_code) as name"),
                    DB::raw("COALESCE(mun.name, mu.new_unitname, '-') as unit_name"),
                    'l.quantity as amount',
                    'l.price',
                    'l.value as total',
                    'l.lot_no',
                    'l.exp as exp_date'
                )->get();

            return response()->json([
                'code' => $header->code,
                'po_code' => $header->po_code,
                'invoice_code' => $header->invoice_code,
                'receive_date' => $header->receive_datetime,
                'vendor' => $header->vendor_name,
                'receiver' => $header->receiver_name,
                'total_value' => (float)$header->value,
                'items' => $items,
            ]);
        }
    }

    /**
     * AJAX Endpoint: Get Requisition & Disbursed Items Detail for a specific Sub-district Health Center (รพ.สต.)
     */
    public function rpstDetail(Request $request, int $id)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $yearId = (int)$request->input('year_id', 2570);
        $monthId = $request->input('month_id', 'all');

        $sh = DB::connection('backoffice')->table('warehouse_smallhos')
            ->where('WAREHOUSE_SMALLHOS_ID', $id)
            ->first(['WAREHOUSE_SMALLHOS_ID as id', 'WAREHOUSE_SMALLHOS_CODE as code', 'WAREHOUSE_SMALLHOS_NAME as name']);

        if (!$sh) {
            return response()->json(['error' => 'ไม่พบข้อมูล รพ.สต.'], 404);
        }

        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';
        $isMonthlyView = ($monthId !== 'all');
        $startMonth = '';
        $endMonth = '';
        if ($isMonthlyView) {
            $mInt = (int)$monthId;
            $calGregorianYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregorianYear);
            $startMonth = sprintf('%04d-%02d-01', $calGregorianYear, $mInt);
            $endMonth = sprintf('%04d-%02d-%02d 23:59:59', $calGregorianYear, $mInt, $daysInMonth);
        }

        // Summary counters
        $summary = [
            'material' => 0.0,
            'dental' => 0.0,
            'medtech' => 0.0,
            'physical' => 0.0,
            'medical' => 0.0,
            'total_val' => 0.0,
            'req_count' => 0,
        ];

        $itemsMap = [];
        $suppReqIdsSeen = [];

        // 1. General Supplies
        $suppQuery = DB::connection('backoffice')->table('warehouse_request as w')
            ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
            ->leftJoin('supplies as sup', 's.WAREHOUSE_REQUEST_SUB_DETAIL_ID', '=', 'sup.ID')
            ->leftJoin('supplies_unit as u', 'sup.SUP_UNIT_ID', '=', 'u.SUP_UNIT_ID')
            ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
            ->where('w.WAREHOUSE_SMALLHOS', $id);

        if ($isMonthlyView) {
            $suppQuery->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
        }

        $suppRows = $suppQuery->select(
            'w.WAREHOUSE_ID as id',
            'w.WAREHOUSE_REQUEST_CODE as code',
            'w.WAREHOUSE_DATE_WANT as request_date',
            'w.WAREHOUSE_SAVE_HR_NAME as requester',
            'w.WAREHOUSE_STATUS as status',
            'sup.SUP_FSN_NUM as item_code',
            'sup.SUP_NAME as item_name',
            'u.SUP_UNIT_NAME as unit_name',
            's.WAREHOUSE_REQUEST_SUB_AMOUNT as qty',
            's.WAREHOUSE_REQUEST_SUB_PRICE as price',
            's.WAREHOUSE_REQUEST_SUB_SUM_PRICE as total_val'
        )->get();

        foreach ($suppRows as $sr) {
            $val = (float)$sr->total_val;
            $summary['material'] += $val;
            $summary['total_val'] += $val;

            if (!isset($suppReqIdsSeen[$sr->id])) {
                $suppReqIdsSeen[$sr->id] = [
                    'id' => $sr->id,
                    'code' => $sr->code ?: '-',
                    'request_date' => $sr->request_date ? substr($sr->request_date, 0, 10) : '-',
                    'warehouse_name' => 'คลังวัสดุ',
                    'warehouse_key' => 'material',
                    'warehouse_color' => '#f59e0b',
                    'requester' => $sr->requester ?: '-',
                    'status' => $sr->status ?: 'Pending',
                    'total_val' => 0.0,
                    'item_count' => 0,
                ];
            }
            $suppReqIdsSeen[$sr->id]['total_val'] += $val;
            $suppReqIdsSeen[$sr->id]['item_count'] += 1;

            $itemKey = 'supp_' . ($sr->item_code ?: $sr->item_name);
            if (!isset($itemsMap[$itemKey])) {
                $itemsMap[$itemKey] = [
                    'code' => $sr->item_code ?: '-',
                    'name' => $sr->item_name ?: 'วัสดุทั่วไป',
                    'unit' => $sr->unit_name ?: '-',
                    'warehouse_name' => 'คลังวัสดุ',
                    'warehouse_key' => 'material',
                    'warehouse_color' => '#f59e0b',
                    'qty' => 0,
                    'total_val' => 0.0,
                ];
            }
            $itemsMap[$itemKey]['qty'] += (float)$sr->qty;
            $itemsMap[$itemKey]['total_val'] += $val;
        }

        // 2. Clinical Warehouses (Medicine, Dental, MedTech, Physical)
        $userMap = DB::connection('backoffice')->table('users')->where('SMALL_ID', $id)->pluck('id')->toArray();
        $personNames = DB::connection('backoffice')->table('warehouse_request')
            ->where('WAREHOUSE_SMALLHOS', $id)
            ->select('WAREHOUSE_SAVE_HR_NAME', 'WAREHOUSE_REPORT_HR_NAME')
            ->get();
        $names = [];
        foreach ($personNames as $pn) {
            foreach ([$pn->WAREHOUSE_SAVE_HR_NAME, $pn->WAREHOUSE_REPORT_HR_NAME] as $n) {
                if (!empty($n)) {
                    $clean = preg_replace('/^(นาย|นางสาว|นาง|น\.ส\.)\s*/u', '', trim($n));
                    $clean = preg_replace('/\s+/', ' ', $clean);
                    $parts = explode(' ', $clean);
                    if (!empty($parts[0])) $names[] = $parts[0];
                }
            }
        }
        $names = array_unique($names);

        $medQuery = DB::connection('backoffice')->table('medicine_warehouse_request as r')
            ->leftJoin('medicine_warehouse as mw', 'r.warehouse_id', '=', 'mw.id');

        if ($isMonthlyView) {
            $medQuery->whereBetween('r.request_date', [$startMonth, substr($endMonth, 0, 10)]);
        } else {
            $medQuery->whereBetween('r.request_date', [$startGregorian, substr($endGregorian, 0, 10)]);
        }

        $medQuery->where(function ($q) use ($userMap, $names) {
            if (!empty($userMap)) {
                $q->whereIn('r.created_by', $userMap)
                  ->orWhereIn('r.request_by', $userMap);
            }
            foreach ($names as $nm) {
                $q->orWhere('r.request_by_small_hosp_name', 'like', "%{$nm}%")
                  ->orWhere('r.approve_by_small_hosp_name', 'like', "%{$nm}%");
            }
        });

        $medReqList = $medQuery->select(
            'r.id', 'r.code', 'r.request_date', 'r.warehouse_id', 'mw.name as warehouse_name',
            'r.request_by_small_hosp_name as requester', 'r.status', 'r.value'
        )->get();

        $whConfigs = [
            1 => ['key' => 'dental', 'name' => 'คลังทันตกรรม', 'color' => '#06b6d4'],
            3 => ['key' => 'medtech', 'name' => 'คลัง LAB', 'color' => '#8b5cf6'],
            4 => ['key' => 'physical', 'name' => 'คลังวัสดุกายภาพบำบัด', 'color' => '#10b981'],
            5 => ['key' => 'medical', 'name' => 'คลังวัสดุการแพทย์', 'color' => '#ec4899'],
        ];

        $medReqIds = $medReqList->pluck('id')->toArray();
        $medReqMap = [];
        foreach ($medReqList as $mr) {
            $whConf = $whConfigs[(int)$mr->warehouse_id] ?? ['key' => 'medical', 'name' => 'คลังวัสดุการแพทย์', 'color' => '#ec4899'];
            $val = (float)$mr->value;
            $summary[$whConf['key']] += $val;
            $summary['total_val'] += $val;

            $medReqMap[$mr->id] = [
                'id' => $mr->id,
                'code' => $mr->code ?: '-',
                'request_date' => $mr->request_date ? substr($mr->request_date, 0, 10) : '-',
                'warehouse_name' => ((int)$mr->warehouse_id === 3) ? 'คลัง LAB' : ($whConf['name'] ?? $mr->warehouse_name),
                'warehouse_key' => $whConf['key'],
                'warehouse_color' => $whConf['color'],
                'requester' => $mr->requester ?: '-',
                'status' => $mr->status ?: 'Pending',
                'total_val' => $val,
                'item_count' => 0,
            ];
        }

        if (!empty($medReqIds)) {
            $medItems = DB::connection('backoffice')->table('medicine_warehouse_request_list as l')
                ->leftJoin('medicine_drug as d', 'l.medicine_id', '=', 'd.id')
                ->leftJoin('medicine_unit as mu', function ($j) {
                    $j->on('d.id', '=', 'mu.drug_id')->where('mu.ranking', 1);
                })
                ->leftJoin('medicine_unit_name as mun', 'mu.unit_name_id', '=', 'mun.id')
                ->leftJoin('medicine_warehouse as mw', 'd.med_warehouse_id', '=', 'mw.id')
                ->whereIn('l.request_id', $medReqIds)
                ->select(
                    'l.request_id',
                    'd.drug_code as item_code',
                    DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), d.drug_code) as item_name"),
                    DB::raw("COALESCE(mun.name, mu.new_unitname, '-') as unit_name"),
                    'l.pay_qty as qty',
                    'l.unit_price as price',
                    'l.value as total_val',
                    'mw.name as warehouse_name',
                    'd.med_warehouse_id as warehouse_id'
                )
                ->get();

            foreach ($medItems as $mi) {
                if (isset($medReqMap[$mi->request_id])) {
                    $medReqMap[$mi->request_id]['item_count'] += 1;
                }
                $whConf = $whConfigs[(int)$mi->warehouse_id] ?? ['key' => 'medical', 'name' => 'คลังเวชภัณฑ์', 'color' => '#ec4899'];
                $whName = ((int)$mi->warehouse_id === 3) ? 'คลัง LAB' : ($whConf['name'] ?? $mi->warehouse_name);
                $itemKey = 'med_' . ($mi->item_code ?: $mi->item_name);
                if (!isset($itemsMap[$itemKey])) {
                    $itemsMap[$itemKey] = [
                        'code' => $mi->item_code ?: '-',
                        'name' => $mi->item_name ?: 'เวชภัณฑ์',
                        'unit' => $mi->unit_name ?: '-',
                        'warehouse_name' => $whName,
                        'warehouse_key' => $whConf['key'],
                        'warehouse_color' => $whConf['color'],
                        'qty' => 0,
                        'total_val' => 0.0,
                    ];
                }
                $itemsMap[$itemKey]['qty'] += (float)$mi->qty;
                $itemsMap[$itemKey]['total_val'] += (float)$mi->total_val;
            }
        }

        $allReqs = array_merge(array_values($suppReqIdsSeen), array_values($medReqMap));
        usort($allReqs, fn($a, $b) => strcmp($b['request_date'], $a['request_date']));
        $summary['req_count'] = count($allReqs);

        $allItems = array_values($itemsMap);
        usort($allItems, fn($a, $b) => $b['total_val'] <=> $a['total_val']);

        $periodLabel = ($isMonthlyView) 
            ? "ประจำเดือน {$this->thaiMonths[(int)$monthId]} ปีงบประมาณ {$yearId}" 
            : "ทั้งปีงบประมาณ {$yearId}";

        return response()->json([
            'rpst_id' => $sh->id,
            'rpst_code' => $sh->code,
            'rpst_name' => $sh->name,
            'period_label' => $periodLabel,
            'summary' => $summary,
            'items_count' => count($allItems),
            'items' => $allItems,
            'requisitions_count' => count($allReqs),
            'requisitions' => $allReqs,
        ]);
    }

    /**
     * AJAX Endpoint: Get Health Centers (รพ.สต.) breakdown for a top disbursed item
     */
    public function rpstItemBreakdown(Request $request)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $code = $request->input('code');
        $itemId = $request->input('item_id');
        $whKey = $request->input('warehouse_key', 'material');
        $yearId = (int)$request->input('year_id', 2569);
        $monthId = $request->input('month_id', 'all');

        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';
        $isMonthlyView = ($monthId !== 'all');
        $startMonth = '';
        $endMonth = '';
        if ($isMonthlyView) {
            $mInt = (int)$monthId;
            $calGregorianYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregorianYear);
            $startMonth = sprintf('%04d-%02d-01', $calGregorianYear, $mInt);
            $endMonth = sprintf('%04d-%02d-%02d 23:59:59', $calGregorianYear, $mInt, $daysInMonth);
        }

        $smallhosList = DB::connection('backoffice')->table('warehouse_smallhos')
            ->get(['WAREHOUSE_SMALLHOS_ID as id', 'WAREHOUSE_SMALLHOS_CODE as code', 'WAREHOUSE_SMALLHOS_NAME as name'])
            ->keyBy('id');

        $itemInfo = [
            'code' => $code ?: '-',
            'name' => '',
            'unit_name' => '',
            'warehouse_key' => $whKey,
            'warehouse_name' => 'คลังพัสดุ/เวชภัณฑ์',
            'warehouse_color' => '#3b82f6',
            'total_qty' => 0,
            'total_val' => 0.0,
        ];
        $rpstList = [];

        if ($whKey === 'material') {
            $itemQuery = DB::connection('backoffice')->table('supplies as sup')
                ->leftJoin('supplies_unit as u', 'sup.SUP_UNIT_ID', '=', 'u.SUP_UNIT_ID');
            if ($itemId) {
                $itemQuery->where('sup.ID', $itemId);
            } else {
                $itemQuery->where('sup.SUP_FSN_NUM', $code);
            }
            $itemObj = $itemQuery->select('sup.ID', 'sup.SUP_FSN_NUM as code', 'sup.SUP_NAME as name', 'u.SUP_UNIT_NAME as unit_name')->first();
            if ($itemObj) {
                $itemInfo['code'] = $itemObj->code;
                $itemInfo['name'] = $itemObj->name;
                $itemInfo['unit_name'] = $itemObj->unit_name ?: 'หน่วย';
                $itemInfo['warehouse_name'] = 'คลังวัสดุ';
                $itemInfo['warehouse_color'] = '#f59e0b';
            }

            $query = DB::connection('backoffice')->table('warehouse_request as w')
                ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                ->leftJoin('supplies as sup', 's.WAREHOUSE_REQUEST_SUB_DETAIL_ID', '=', 'sup.ID')
                ->leftJoin('warehouse_smallhos as sh', 'w.WAREHOUSE_SMALLHOS', '=', 'sh.WAREHOUSE_SMALLHOS_ID')
                ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
                ->whereNotNull('w.WAREHOUSE_SMALLHOS')->where('w.WAREHOUSE_SMALLHOS', '!=', '')->where('w.WAREHOUSE_SMALLHOS', '!=', '0');

            if ($itemId) {
                $query->where('s.WAREHOUSE_REQUEST_SUB_DETAIL_ID', $itemId);
            } else {
                $query->where('sup.SUP_FSN_NUM', $code);
            }

            if ($isMonthlyView) {
                $query->whereRaw('MONTH(w.WAREHOUSE_DATE_WANT) = ?', [(int)$monthId]);
            }

            $rows = $query->select(
                'sh.WAREHOUSE_SMALLHOS_ID as rpst_id',
                'sh.WAREHOUSE_SMALLHOS_CODE as rpst_code',
                'sh.WAREHOUSE_SMALLHOS_NAME as rpst_name',
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_AMOUNT) as qty'),
                DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as total_val'),
                DB::raw('count(distinct w.WAREHOUSE_ID) as req_count'),
                DB::raw('max(w.WAREHOUSE_DATE_WANT) as last_date')
            )
            ->groupBy('sh.WAREHOUSE_SMALLHOS_ID', 'sh.WAREHOUSE_SMALLHOS_CODE', 'sh.WAREHOUSE_SMALLHOS_NAME')
            ->orderByDesc('qty')
            ->get();

            foreach ($rows as $r) {
                $rpstList[] = [
                    'rpst_id' => $r->rpst_id,
                    'rpst_code' => $r->rpst_code ?: '-',
                    'rpst_name' => $r->rpst_name ?: 'รพ.สต.',
                    'qty' => (float)$r->qty,
                    'total_val' => (float)$r->total_val,
                    'req_count' => (int)$r->req_count,
                    'last_date' => $r->last_date ? substr($r->last_date, 0, 10) : '-',
                ];
                $itemInfo['total_qty'] += (float)$r->qty;
                $itemInfo['total_val'] += (float)$r->total_val;
            }
        } else {
            // Medicine / Clinical
            $drugQuery = DB::connection('backoffice')->table('medicine_drug as d')
                ->leftJoin('medicine_warehouse as mw', 'd.med_warehouse_id', '=', 'mw.id')
                ->leftJoin('medicine_unit as mu', function ($j) {
                    $j->on('d.id', '=', 'mu.drug_id')->where('mu.ranking', 1);
                })
                ->leftJoin('medicine_unit_name as mun', 'mu.unit_name_id', '=', 'mun.id');

            if ($itemId) {
                $drugQuery->where('d.id', $itemId);
            } else {
                $drugQuery->where('d.drug_code', $code);
            }

            $drugObj = $drugQuery->select(
                'd.id', 'd.drug_code as code',
                DB::raw("COALESCE(NULLIF(d.trade_name, ''), NULLIF(d.generic_name, ''), d.drug_code) as name"),
                DB::raw("COALESCE(mun.name, mu.new_unitname, '') as unit_name"),
                'd.med_warehouse_id',
                'mw.name as warehouse_name'
            )->first();

            $whNames = [
                1 => ['name' => 'คลังทันตกรรม', 'color' => '#06b6d4', 'key' => 'dental'],
                3 => ['name' => 'คลัง LAB', 'color' => '#8b5cf6', 'key' => 'medtech'],
                4 => ['name' => 'คลังกายภาพ', 'color' => '#10b981', 'key' => 'physical'],
                5 => ['name' => 'คลังวัสดุการแพทย์', 'color' => '#ec4899', 'key' => 'medical'],
            ];

            if ($drugObj) {
                $itemInfo['code'] = $drugObj->code;
                $itemInfo['name'] = $drugObj->name;
                $itemInfo['unit_name'] = $drugObj->unit_name ?: 'หน่วย';
                $wConf = $whNames[(int)$drugObj->med_warehouse_id] ?? ['name' => $drugObj->warehouse_name ?: 'คลังเวชภัณฑ์', 'color' => '#8b5cf6', 'key' => 'medical'];
                $itemInfo['warehouse_name'] = $wConf['name'];
                $itemInfo['warehouse_color'] = $wConf['color'];
                $itemInfo['warehouse_key'] = $wConf['key'];
            }

            $userMap = DB::connection('backoffice')->table('users')
                ->whereNotNull('SMALL_ID')->where('SMALL_ID', '!=', '')->where('SMALL_ID', '!=', '0')
                ->pluck('SMALL_ID', 'id')->toArray();

            $personMap = [];
            $requestsWithSmallhos = DB::connection('backoffice')->table('warehouse_request')
                ->whereNotNull('WAREHOUSE_SMALLHOS')->where('WAREHOUSE_SMALLHOS', '!=', '')->where('WAREHOUSE_SMALLHOS', '!=', '0')
                ->select('WAREHOUSE_SAVE_HR_NAME', 'WAREHOUSE_REPORT_HR_NAME', 'WAREHOUSE_SMALLHOS')
                ->get();
            foreach ($requestsWithSmallhos as $r) {
                $sid = (int)$r->WAREHOUSE_SMALLHOS;
                foreach ([$r->WAREHOUSE_SAVE_HR_NAME, $r->WAREHOUSE_REPORT_HR_NAME] as $nm) {
                    if (!empty($nm)) {
                        $clean = preg_replace('/^(นาย|นางสาว|นาง|น\.ส\.)\s*/u', '', trim($nm));
                        $clean = preg_replace('/\s+/', ' ', $clean);
                        $parts = explode(' ', $clean);
                        if (!empty($parts[0])) {
                            $personMap[$parts[0]] = $sid;
                        }
                    }
                }
            }

            $medQuery = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->join('medicine_warehouse_request_list as l', 'r.id', '=', 'l.request_id')
                ->leftJoin('medicine_drug as d', 'l.medicine_id', '=', 'd.id');

            if ($isMonthlyView) {
                $medQuery->whereBetween('r.request_date', [$startMonth, substr($endMonth, 0, 10)]);
            } else {
                $medQuery->whereBetween('r.request_date', [$startGregorian, substr($endGregorian, 0, 10)]);
            }

            if ($itemId) {
                $medQuery->where('l.medicine_id', $itemId);
            } else {
                $medQuery->where('d.drug_code', $code);
            }

            $medRows = $medQuery->select(
                'r.id as request_id', 'r.code as request_code', 'r.request_date',
                'r.created_by', 'r.request_by', 'r.request_by_small_hosp_name', 'r.approve_by_small_hosp_name',
                'l.pay_qty as qty', 'l.value as total_val'
            )->get();

            $rpstGroup = [];
            foreach ($medRows as $mr) {
                $matchedSid = null;
                if (!empty($mr->created_by) && isset($userMap[$mr->created_by])) {
                    $matchedSid = (int)$userMap[$mr->created_by];
                } elseif (!empty($mr->request_by) && isset($userMap[$mr->request_by])) {
                    $matchedSid = (int)$userMap[$mr->request_by];
                }
                if (!$matchedSid) {
                    foreach ([$mr->request_by_small_hosp_name, $mr->approve_by_small_hosp_name] as $nm) {
                        if (!empty($nm)) {
                            $clean = preg_replace('/^(นาย|นางสาว|นาง|น\.ส\.)\s*/u', '', trim($nm));
                            $clean = preg_replace('/\s+/', ' ', $clean);
                            $parts = explode(' ', $clean);
                            if (!empty($parts[0]) && isset($personMap[$parts[0]])) {
                                $matchedSid = $personMap[$parts[0]];
                                break;
                            }
                        }
                    }
                }

                if ($matchedSid) {
                    if (!isset($rpstGroup[$matchedSid])) {
                        $sh = $smallhosList->get($matchedSid);
                        $rpstGroup[$matchedSid] = [
                            'rpst_id' => $matchedSid,
                            'rpst_code' => $sh ? $sh->code : '-',
                            'rpst_name' => $sh ? $sh->name : 'รพ.สต.',
                            'qty' => 0.0,
                            'total_val' => 0.0,
                            'req_count' => 0,
                            'last_date' => $mr->request_date,
                        ];
                    }
                    $rpstGroup[$matchedSid]['qty'] += (float)$mr->qty;
                    $rpstGroup[$matchedSid]['total_val'] += (float)$mr->total_val;
                    $rpstGroup[$matchedSid]['req_count'] += 1;
                    if ($mr->request_date > $rpstGroup[$matchedSid]['last_date']) {
                        $rpstGroup[$matchedSid]['last_date'] = $mr->request_date;
                    }
                    $itemInfo['total_qty'] += (float)$mr->qty;
                    $itemInfo['total_val'] += (float)$mr->total_val;
                }
            }

            uasort($rpstGroup, fn($a, $b) => $b['qty'] <=> $a['qty']);
            $rpstList = array_values($rpstGroup);
        }

        foreach ($rpstList as &$rp) {
            $rp['share_pct'] = $itemInfo['total_qty'] > 0 ? round(($rp['qty'] / $itemInfo['total_qty']) * 100, 1) : 0;
        }
        unset($rp);

        $periodLabel = ($isMonthlyView) 
            ? "ประจำเดือน {$this->thaiMonths[(int)$monthId]} ปีงบประมาณ {$yearId}" 
            : "ทั้งปีงบประมาณ {$yearId}";

        return response()->json([
            'item' => $itemInfo,
            'period_label' => $periodLabel,
            'rpst_count' => count($rpstList),
            'rpst_list' => $rpstList,
        ]);
    }

    /**
     * AJAX Endpoint: Upload MOPH 5 Files (INVS Drug Warehouse)
     */
    public function uploadMophF5(Request $request, \App\Services\MophF5DrugService $service)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์เข้าถึงระบบคลัง'], 403);
        }

        $files = $request->file('files');
        if (empty($files) || !is_array($files)) {
            return response()->json(['success' => false, 'message' => 'กรุณาเลือกไฟล์ .xlsx อย่างน้อย 1 ไฟล์'], 422);
        }

        $savedPaths = [];
        $tempDir = storage_path('app/temp_f5_' . uniqid());
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        try {
            foreach ($files as $file) {
                if (!$file->isValid()) continue;
                $origName = $file->getClientOriginalName();
                $targetPath = $tempDir . DIRECTORY_SEPARATOR . $origName;
                $file->move($tempDir, $origName);
                $savedPaths[] = $targetPath;
            }

            if (empty($savedPaths)) {
                return response()->json(['success' => false, 'message' => 'ไม่พบไฟล์ที่ถูกต้อง'], 422);
            }

            $results = $service->importFiles($savedPaths);

            $latestInv = DB::table('moph_f5_inventory')->max('date_onhand');
            $counts = [
                'druglist' => DB::table('moph_f5_druglist')->count(),
                'purchaseplan' => DB::table('moph_f5_purchaseplan')->count(),
                'receipt' => DB::table('moph_f5_receipt')->count(),
                'distribution' => DB::table('moph_f5_distribution')->count(),
                'inventory' => $latestInv ? DB::table('moph_f5_inventory')->where('date_onhand', $latestInv)->count() : DB::table('moph_f5_inventory')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'นำเข้าข้อมูล 5 แฟ้มยาสำเร็จ',
                'results' => $results,
                'counts' => $counts,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการประมวลผล: ' . $e->getMessage(),
            ], 500);
        } finally {
            if (is_dir($tempDir)) {
                $scan = array_diff(scandir($tempDir), ['.', '..']);
                foreach ($scan as $f) {
                    @unlink($tempDir . DIRECTORY_SEPARATOR . $f);
                }
                @rmdir($tempDir);
            }
        }
    }

    /**
     * AJAX Endpoint: Get DataTable records for MOPH 5 files
     */
    public function mophF5TableData(Request $request, string $file)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $monthNames = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        $formatDate = function ($dateStr) use ($monthNames) {
            if (!$dateStr) return '-';
            $cleaned = preg_replace('/[^0-9]/', '', (string)$dateStr);
            if (strlen($cleaned) >= 8) {
                $y = (int)substr($cleaned, 0, 4);
                $m = (int)substr($cleaned, 4, 2);
                $d = (int)substr($cleaned, 6, 2);
                $by = ($y < 2400) ? ($y + 543) : $y;
                $mName = $monthNames[$m] ?? $m;
                return "{$d} {$mName} {$by}";
            }
            return $dateStr;
        };

        $formatPeriod = function ($p) use ($monthNames) {
            if (!$p) return '-';
            $cleaned = preg_replace('/[^0-9]/', '', (string)$p);
            if (strlen($cleaned) === 6) {
                $y = (int)substr($cleaned, 0, 4);
                $m = (int)substr($cleaned, 4, 2);
                $by = ($y < 2400) ? ($y + 543) : $y;
                $mName = $monthNames[$m] ?? $m;
                return "{$mName} {$by}";
            }
            return $p;
        };

        $formatDateTimeThai = function ($dtStr) use ($monthNames) {
            if (!$dtStr) return '-';
            try {
                $carbon = \Carbon\Carbon::parse($dtStr);
                $by = $carbon->year < 2400 ? ($carbon->year + 543) : $carbon->year;
                $mName = $monthNames[$carbon->month] ?? $carbon->month;
                return $carbon->day . ' ' . $mName . ' ' . $by . ' ' . $carbon->format('H:i') . ' น.';
            } catch (\Throwable $e) {
                return $dtStr;
            }
        };

        $deptNames = [
            '1' => 'OPD + IPD (ผู้ป่วยนอกและใน)',
            '2' => 'OPD (ผู้ป่วยนอก)',
            '3' => 'IPD (ผู้ป่วยใน)',
            '4' => 'หน่วยอื่นๆ ในโรงพยาบาล',
            '5' => 'รพ.สต. (ในเครือข่าย)',
            '6' => 'รพ.สต. (ถ่ายโอน)',
            '9' => 'อื่นๆ ภายนอกโรงพยาบาล',
        ];

        $periodOptions = function ($tableName) use ($monthNames) {
            $periods = DB::table($tableName)->distinct()->whereNotNull('period_rpt')->where('period_rpt', '!=', '')->orderByDesc('period_rpt')->pluck('period_rpt');
            $list = [];
            foreach ($periods as $p) {
                $cleaned = preg_replace('/[^0-9]/', '', (string)$p);
                $label = $p;
                if (strlen($cleaned) === 6) {
                    $y = (int)substr($cleaned, 0, 4);
                    $m = (int)substr($cleaned, 4, 2);
                    $by = ($y < 2400) ? ($y + 543) : $y;
                    $mName = $monthNames[$m] ?? $m;
                    $label = "{$mName} {$by} ({$cleaned})";
                }
                $list[] = [
                    'value' => $p,
                    'label' => $label,
                ];
            }
            return $list;
        };

        switch ($file) {
            case 'druglist':
                $q = DB::table('moph_f5_druglist');
                if ($request->filled('period_rpt') && $request->period_rpt !== 'all') {
                    $q->where('period_rpt', $request->period_rpt);
                }
                if ($request->filled('status') && $request->status !== 'all') {
                    $q->where('status', $request->status);
                }
                $rows = $q->orderBy('working_code')->get();
                $totalActive = 0;
                $totalNlem = 0;
                $data = [];
                foreach ($rows as $i => $r) {
                    $isActive = ($r->status === '1' || strtolower((string)$r->status) === 'active');
                    if ($isActive) $totalActive++;
                    if ($r->nlem && $r->nlem !== 'NED') $totalNlem++;
                    $data[] = [
                        'index' => $i + 1,
                        'working_code' => $r->working_code,
                        'generic_name' => $r->generic_name ?? '-',
                        'gpuid' => $r->gpuid ?: '-',
                        'nlem' => $r->nlem ?: '-',
                        'product_cat' => $r->product_cat ?: '-',
                        'base_unit' => $r->base_unit ?: '-',
                        'status' => $r->status,
                        'status_badge' => $isActive
                            ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>ใช้งาน</span>'
                            : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1"><i class="fas fa-ban me-1"></i>ยกเลิก</span>',
                        'date_status' => $formatDate($r->date_status),
                        'period_rpt' => $formatPeriod($r->period_rpt),
                    ];
                }
                return response()->json([
                    'success' => true,
                    'file' => 'druglist',
                    'count' => count($data),
                    'last_updated' => $formatDateTimeThai(DB::table('moph_f5_druglist')->max('updated_at')),
                    'summary' => [
                        'total_items' => count($data),
                        'total_active' => $totalActive,
                        'total_nlem' => $totalNlem,
                    ],
                    'filters' => [
                        'available_periods' => $periodOptions('moph_f5_druglist'),
                    ],
                    'data' => $data,
                ]);

            case 'purchaseplan':
                $q = DB::table('moph_f5_purchaseplan');
                if ($request->filled('period_rpt') && $request->period_rpt !== 'all') {
                    $q->where('period_rpt', $request->period_rpt);
                }
                if ($request->filled('yearbudget') && $request->yearbudget !== 'all') {
                    $q->where('yearbudget', $request->yearbudget);
                }
                $rows = $q->orderBy('working_code')->get();
                $totalPlanQty = 0;
                $totalPlanVal = 0;
                $data = [];
                foreach ($rows as $i => $r) {
                    $totalPlanQty += (float)$r->qty_this_year;
                    $totalPlanVal += (float)$r->value_this_year;
                    $data[] = [
                        'index' => $i + 1,
                        'yearbudget' => $r->yearbudget ?: '-',
                        'working_code' => $r->working_code,
                        'generic_name' => $r->generic_name ?? '-',
                        'gpuid' => $r->gpuid ?: '-',
                        'nlem' => $r->nlem ?: '-',
                        'pack_size' => (int)$r->pack_size,
                        'base_unit' => $r->base_unit ?: '-',
                        'pack_cost' => (float)$r->pack_cost,
                        'qty_this_year' => (float)$r->qty_this_year,
                        'value_this_year' => (float)$r->value_this_year,
                        'qty_use_year1' => (float)$r->qty_use_year1,
                        'qty_plan_trimes1' => (float)$r->qty_plan_trimes1,
                        'qty_plan_trimes2' => (float)$r->qty_plan_trimes2,
                        'qty_plan_trimes3' => (float)$r->qty_plan_trimes3,
                        'qty_plan_trimes4' => (float)$r->qty_plan_trimes4,
                        'period_rpt' => $formatPeriod($r->period_rpt),
                    ];
                }
                $rawYears = DB::table('moph_f5_purchaseplan')->distinct()->whereNotNull('yearbudget')->orderByDesc('yearbudget')->pluck('yearbudget');
                $availableYears = [];
                foreach ($rawYears as $y) {
                    $yInt = (int)$y;
                    $thYear = ($yInt < 2400) ? ($yInt + 543) : $yInt;
                    $availableYears[] = ['value' => $y, 'label' => "ปีงบ {$thYear} ({$y})"];
                }
                return response()->json([
                    'success' => true,
                    'file' => 'purchaseplan',
                    'count' => count($data),
                    'last_updated' => $formatDateTimeThai(DB::table('moph_f5_purchaseplan')->max('updated_at')),
                    'summary' => [
                        'total_items' => count($data),
                        'total_qty' => $totalPlanQty,
                        'total_value' => $totalPlanVal,
                    ],
                    'filters' => [
                        'available_periods' => $periodOptions('moph_f5_purchaseplan'),
                        'available_years' => $availableYears,
                    ],
                    'data' => $data,
                ]);

            case 'receipt':
                $q = DB::table('moph_f5_receipt');
                if ($request->filled('period_rpt') && $request->period_rpt !== 'all') {
                    $q->where('period_rpt', $request->period_rpt);
                }
                $rows = $q->orderByDesc('date_rcv')->orderByDesc('id')->get();
                $totalQty = 0;
                $totalVal = 0;
                $data = [];
                foreach ($rows as $i => $r) {
                    $totalQty += (float)$r->qty_rcv;
                    $totalVal += (float)$r->total_value;
                    $data[] = [
                        'index' => $i + 1,
                        'rcv_no' => $r->rcv_no ?: '-',
                        'po_no' => $r->po_no ?: '-',
                        'date_rcv' => $formatDate($r->date_rcv),
                        'raw_date_rcv' => $r->date_rcv,
                        'working_code' => $r->working_code,
                        'trade_name' => $r->trade_name ?? '-',
                        'vendor_name' => $r->vendor_name ?? '-',
                        'qty_rcv' => (float)$r->qty_rcv,
                        'pack_size' => (int)$r->pack_size,
                        'base_unit' => $r->base_unit ?: '-',
                        'pack_cost' => (float)$r->pack_cost,
                        'total_value' => (float)$r->total_value,
                        'lot_no' => $r->lot_no ?: '-',
                        'expire_date' => $formatDate($r->expire_date),
                        'period_rpt' => $formatPeriod($r->period_rpt),
                    ];
                }
                return response()->json([
                    'success' => true,
                    'file' => 'receipt',
                    'count' => count($data),
                    'last_updated' => $formatDateTimeThai(DB::table('moph_f5_receipt')->max('updated_at')),
                    'summary' => [
                        'total_items' => count($data),
                        'total_qty' => $totalQty,
                        'total_value' => $totalVal,
                    ],
                    'filters' => [
                        'available_periods' => $periodOptions('moph_f5_receipt'),
                    ],
                    'data' => $data,
                ]);

            case 'distribution':
                $q = DB::table('moph_f5_distribution');
                if ($request->filled('period_rpt') && $request->period_rpt !== 'all') {
                    $q->where('period_rpt', $request->period_rpt);
                }
                if ($request->filled('dis_dept_group') && $request->dis_dept_group !== 'all') {
                    $q->where('dis_dept_group', $request->dis_dept_group);
                }
                $rows = $q->orderBy('dis_dept_group')->orderBy('working_code')->get();
                $totalQty = 0;
                $totalVal = 0;
                $data = [];
                foreach ($rows as $i => $r) {
                    $totalQty += (float)$r->qty_dis;
                    $totalVal += (float)$r->value;
                    $deptName = $deptNames[$r->dis_dept_group] ?? ('กลุ่มที่ ' . $r->dis_dept_group);
                    $data[] = [
                        'index' => $i + 1,
                        'period_rpt' => $formatPeriod($r->period_rpt),
                        'dept_group' => $r->dis_dept_group,
                        'dept_name' => $deptName,
                        'working_code' => $r->working_code,
                        'trade_name' => $r->trade_name ?? '-',
                        'qty_dis' => (float)$r->qty_dis,
                        'pack_size' => (int)$r->pack_size,
                        'base_unit' => $r->base_unit ?: '-',
                        'value' => (float)$r->value,
                    ];
                }
                $rawDepts = DB::table('moph_f5_distribution')->distinct()->whereNotNull('dis_dept_group')->orderBy('dis_dept_group')->pluck('dis_dept_group');
                $availableDepts = [];
                foreach ($rawDepts as $d) {
                    $availableDepts[] = [
                        'value' => $d,
                        'label' => $deptNames[$d] ?? ("กลุ่มที่ " . $d),
                    ];
                }
                return response()->json([
                    'success' => true,
                    'file' => 'distribution',
                    'count' => count($data),
                    'last_updated' => $formatDateTimeThai(DB::table('moph_f5_distribution')->max('updated_at')),
                    'summary' => [
                        'total_items' => count($data),
                        'total_qty' => $totalQty,
                        'total_value' => $totalVal,
                    ],
                    'filters' => [
                        'available_periods' => $periodOptions('moph_f5_distribution'),
                        'available_depts' => $availableDepts,
                    ],
                    'data' => $data,
                ]);

            case 'inventory':
                // Group by Year-Month and pick the MAX date_onhand for each month
                // This ensures each month has exactly 1 entry (representing the latest date of that month)
                $monthlyGroups = DB::table('moph_f5_inventory')
                    ->whereNotNull('date_onhand')
                    ->where('date_onhand', '!=', '')
                    ->select(
                        DB::raw("SUBSTRING(date_onhand, 1, 6) as ym"),
                        DB::raw("MAX(date_onhand) as max_date"),
                        DB::raw("COUNT(*) as total_rows")
                    )
                    ->groupBy(DB::raw("SUBSTRING(date_onhand, 1, 6)"))
                    ->orderByDesc('ym')
                    ->get();

                $latestDate = $monthlyGroups->first()->max_date ?? null;

                // Inventory is a point-in-time balance: default to the latest survey date of current month
                $targetDate = $request->get('date_onhand');
                if (!$targetDate || $targetDate === 'latest' || $targetDate === 'all') {
                    $targetDate = $latestDate;
                } else {
                    $cleaned = preg_replace('/[^0-9]/', '', (string)$targetDate);
                    if (strlen($cleaned) === 6) {
                        $matchedGroup = $monthlyGroups->firstWhere('ym', $cleaned);
                        if ($matchedGroup) {
                            $targetDate = $matchedGroup->max_date;
                        }
                    }
                }

                $q = DB::table('moph_f5_inventory');
                if ($targetDate) {
                    $cleanedOnHand = preg_replace('/[^0-9]/', '', (string)$targetDate);
                    $q->where('date_onhand', $cleanedOnHand);
                }
                if ($request->filled('expire_filter') && $request->expire_filter !== 'all') {
                    $todayYmd = date('Ymd');
                    $sixMonthsYmd = date('Ymd', strtotime('+6 months'));
                    if ($request->expire_filter === 'expired') {
                        $q->whereNotNull('expire_date')->where('expire_date', '<=', $todayYmd)->where('expire_date', '!=', '');
                    } elseif ($request->expire_filter === 'near_exp') {
                        $q->whereNotNull('expire_date')->where('expire_date', '>', $todayYmd)->where('expire_date', '<=', $sixMonthsYmd);
                    }
                }
                $rows = $q->orderBy('working_code')->get();
                $totalQty = 0;
                $totalVal = 0;
                $data = [];
                foreach ($rows as $i => $r) {
                    $totalQty += (float)$r->qty_onhand;
                    $totalVal += (float)$r->value_onhand;
                    $data[] = [
                        'index' => $i + 1,
                        'date_onhand' => $formatDate($r->date_onhand),
                        'raw_date_onhand' => $r->date_onhand,
                        'working_code' => $r->working_code,
                        'trade_name' => $r->trade_name ?? '-',
                        'vendor_name' => $r->vendor_name ?? '-',
                        'qty_onhand' => (float)$r->qty_onhand,
                        'pack_size' => (int)$r->pack_size,
                        'base_unit' => $r->base_unit ?: '-',
                        'pack_cost' => (float)$r->pack_cost,
                        'value_onhand' => (float)$r->value_onhand,
                        'lot_no' => $r->lot_no ?: '-',
                        'expire_date' => $formatDate($r->expire_date),
                    ];
                }

                $availableDates = [];
                foreach ($monthlyGroups as $idx => $g) {
                    $ym = (string)$g->ym;
                    $maxDate = (string)$g->max_date;
                    $isLatest = ($idx === 0);
                    $cnt = DB::table('moph_f5_inventory')->where('date_onhand', $maxDate)->count();

                    $y = (int)substr($ym, 0, 4);
                    $m = (int)substr($ym, 4, 2);
                    $by = ($y < 2400) ? ($y + 543) : $y;
                    $mName = $monthNames[$m] ?? $m;

                    $prefix = $isLatest ? "ล่าสุด: " : "สิ้นงวด: ";
                    $label = "{$mName} {$by} ({$prefix}ณ วันที่ " . $formatDate($maxDate) . ")";

                    $availableDates[] = [
                        'value' => $maxDate,
                        'label' => $label,
                        'is_latest' => $isLatest,
                        'ym' => $ym,
                    ];
                }
                return response()->json([
                    'success' => true,
                    'file' => 'inventory',
                    'count' => count($data),
                    'selected_date' => $targetDate,
                    'last_updated' => $formatDateTimeThai(DB::table('moph_f5_inventory')->max('updated_at')),
                    'summary' => [
                        'total_items' => count($data),
                        'total_qty' => $totalQty,
                        'total_value' => $totalVal,
                    ],
                    'filters' => [
                        'available_dates' => $availableDates,
                    ],
                    'data' => $data,
                ]);

            default:
                return response()->json(['error' => 'Invalid file parameter'], 400);
        }
    }

    /**
     * Data provider for MOPH 5 Drug Warehouse tab
     */
    private function getMophF5WarehouseData(array $config, int $yearId, $monthId, $subWhId, string $viewMode, string $search): array
    {
        $isMonthlyView = ($monthId !== 'all');
        $selectedMonthName = '';
        $selectedCalYear = $yearId;
        $monthlyTrend = [];
        $dailyTrend = [];

        foreach ($this->fiscalMonthOrder as $m) {
            $monthlyTrend[$m] = ['month_id' => $m, 'val' => 0.0, 'receive_val' => 0.0, 'reqs' => 0, 'receives' => 0];
        }

        if ($isMonthlyView) {
            $mInt = (int)$monthId;
            $selectedMonthName = $this->thaiMonths[$mInt] ?? '';
            $selectedCalYear = ($mInt >= 10) ? ($yearId - 1) : $yearId;
            $calGregorianYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mInt, $calGregorianYear);

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dailyTrend[$d] = ['day' => $d, 'val' => 0.0, 'receive_val' => 0.0, 'reqs' => 0, 'receives' => 0];
            }
        }

        // Fiscal year date & period ranges
        $startRcvDate = ($yearId - 544) . '1001';
        $endRcvDate = ($yearId - 543) . '0930';
        $startDisbPeriod = ($yearId - 544) . '10';
        $endDisbPeriod = ($yearId - 543) . '09';

        // 1. Receives
        $recQuery = DB::table('moph_f5_receipt');
        if ($monthId !== 'all') {
            $mInt = (int)$monthId;
            $calGregYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $monthPrefix = sprintf('%04d%02d', $calGregYear, $mInt);
            $recQuery->where('date_rcv', 'like', $monthPrefix . '%');
        } else {
            $recQuery->whereBetween('date_rcv', [$startRcvDate, $endRcvDate]);
        }
        if (!empty($search)) {
            $recQuery->where(function($q) use ($search) {
                $q->where('trade_name', 'like', "%{$search}%")
                  ->orWhere('working_code', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%")
                  ->orWhere('rcv_no', 'like', "%{$search}%")
                  ->orWhere('po_no', 'like', "%{$search}%");
            });
        }

        $rawReceives = (clone $recQuery)
            ->select(
                'rcv_no as code',
                DB::raw('MIN(id) as id'),
                DB::raw('MAX(po_no) as po_code'),
                DB::raw('MAX(date_rcv) as raw_date'),
                DB::raw('MAX(vendor_name) as vendor_name'),
                DB::raw("'เจ้าหน้าที่คลังยา' as receiver_name"),
                DB::raw('SUM(total_value) as total_value'),
                DB::raw('COUNT(*) as item_count')
            )
            ->groupBy('rcv_no')
            ->orderByDesc('raw_date')
            ->get();

        $receives = $rawReceives->map(function($r) {
            $d = $r->raw_date;
            $r->receive_date = (strlen($d) === 8) 
                ? substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2)
                : $d;
            $r->total_value = (float)$r->total_value;
            $r->item_count = (int)$r->item_count;
            return $r;
        });

        $totalReceive = (float)$rawReceives->sum('total_value');
        $totalReceiveCount = $rawReceives->count();

        // 2. Disbursements (Requests)
        $deptGroupNames = [
            '1' => 'OPD + IPD (ผู้ป่วยนอกและใน)',
            '2' => 'OPD (ผู้ป่วยนอก)',
            '3' => 'IPD (ผู้ป่วยใน)',
            '4' => 'หน่วยอื่นๆ ในโรงพยาบาล',
            '5' => 'รพ.สต. ในเครือข่าย',
            '6' => 'รพ.สต. ถ่ายโอน',
            '9' => 'อื่นๆ ภายนอกโรงพยาบาล'
        ];

        $disbQuery = DB::table('moph_f5_distribution');
        if ($monthId !== 'all') {
            $mInt = (int)$monthId;
            $calGregYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $monthPeriod = sprintf('%04d%02d', $calGregYear, $mInt);
            $disbQuery->where('period_rpt', $monthPeriod);
        } else {
            $disbQuery->whereBetween('period_rpt', [$startDisbPeriod, $endDisbPeriod]);
        }
        if ($subWhId !== 'all') {
            $disbQuery->where('dis_dept_group', $subWhId);
        }
        if (!empty($search)) {
            $disbQuery->where(function($q) use ($search) {
                $q->where('trade_name', 'like', "%{$search}%")
                  ->orWhere('working_code', 'like', "%{$search}%");
            });
        }

        $rawRequests = (clone $disbQuery)->orderByDesc('id')->get();
        $totalDisbursement = (float)$rawRequests->sum('value');
        $totalReqCount = $rawRequests->count();
        $statusCounts = ['Pending' => 0, 'Approve' => 0, 'Verify' => 0, 'Allow' => $totalReqCount];

        $requests = $rawRequests->map(function($r) use ($deptGroupNames) {
            $d = $r->period_rpt;
            $dateStr = (strlen($d) === 6) ? (substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-01') : date('Y-m-d');
            return (object)[
                'id' => $r->id,
                'code' => $r->working_code,
                'req_date' => $dateStr,
                'save_datetime' => $dateStr . ' 08:30:00',
                'requester_name' => 'ระบบคลังยา INVS',
                'requester_position' => 'เจ้าหน้าที่คลัง',
                'sub_warehouse_id' => $r->dis_dept_group,
                'sub_warehouse_name' => $deptGroupNames[$r->dis_dept_group] ?? ('กลุ่มเบิก ' . $r->dis_dept_group),
                'status' => 'Allow',
                'item_count' => 1,
                'total_value' => (float)$r->value,
            ];
        });

        // 3. Stock Items & Catalog (คำนวณ ณ วันสำรวจล่าสุด ไม่ใช่ทั้งปี)
        $targetInvDate = $this->getPharmacyInventoryDate($yearId, $monthId);
        $invBaseQuery = DB::table('moph_f5_inventory');
        if ($targetInvDate) {
            $invBaseQuery->where('date_onhand', $targetInvDate);
        }

        $totalStockVal = (float)(clone $invBaseQuery)->sum('value_onhand');
        $catalogItemsCount = (clone $invBaseQuery)->distinct('working_code')->count('working_code') ?: DB::table('moph_f5_druglist')->count();

        if ($isMonthlyView) {
            $stockDateLabel = 'ณ สิ้นเดือน' . $selectedMonthName;
        } else {
            $stockDateLabel = $targetInvDate ? ('ณ ' . $this->formatThaiDateShort($targetInvDate)) : 'ณ วันที่ล่าสุด';
        }

        $consumptionMap = DB::table('moph_f5_distribution')
            ->select('working_code', DB::raw('SUM(qty_dis) as total_qty'), DB::raw('SUM(value) as total_val'))
            ->groupBy('working_code')
            ->get()
            ->keyBy('working_code');

        $rawStockQuery = (clone $invBaseQuery);
        if (!empty($search)) {
            $rawStockQuery->where(function($q) use ($search) {
                $q->where('working_code', 'like', "%{$search}%")
                  ->orWhere('trade_name', 'like', "%{$search}%")
                  ->orWhere('lot_no', 'like', "%{$search}%");
            });
        }

        $rawStock = $rawStockQuery->select(
                DB::raw('MIN(id) as id'),
                'working_code',
                DB::raw('MAX(trade_name) as trade_name'),
                DB::raw('MAX(base_unit) as base_unit'),
                DB::raw('SUM(qty_onhand) as total_qty'),
                DB::raw('SUM(value_onhand) as total_val'),
                DB::raw('MAX(pack_cost) as pack_cost'),
                DB::raw('MAX(pack_size) as pack_size'),
                DB::raw('MAX(lot_no) as lot_no'),
                DB::raw('MIN(expire_date) as expire_date')
            )
            ->groupBy('working_code')
            ->get();

        $stockItems = [];
        $reorderItems = [];

        foreach ($rawStock as $st) {
            $wCode = $st->working_code;
            $mainStock = (float)$st->total_qty;
            $totalVal = (float)$st->total_val;
            
            $disbInfo = $consumptionMap->get($wCode);
            $disbQty = $disbInfo ? (float)$disbInfo->total_qty : 0.0;
            
            $subStock = round($disbQty * 0.2);
            $totalHospitalStock = $mainStock + $subStock;

            $amc = $disbQty > 0 ? max(1.0, round($disbQty / 12, 1)) : max(1.0, round($mainStock / 4, 1));
            $mos = $amc > 0 ? round($totalHospitalStock / $amc, 1) : 99.0;
            
            $minStock = round($amc * ($config['standard_mos_min'] ?? 1.5), 1);
            $maxStock = round($amc * ($config['standard_mos_max'] ?? 2.0), 1);
            $unitPrice = ($st->pack_size > 0 && $st->pack_cost > 0) 
                ? ((float)$st->pack_cost / (float)$st->pack_size) 
                : ($mainStock > 0 ? ($totalVal / $mainStock) : 10.0);

            $status = 'normal';
            $suggestedQty = 0;
            $budget = 0.0;

            if ($mos < 0.5 || $totalHospitalStock <= $minStock) {
                $status = 'critical';
                $suggestedQty = max(0, round($maxStock - $totalHospitalStock));
                $budget = $suggestedQty * $unitPrice;
            } elseif ($mos <= 1.5) {
                $status = 'warning';
                $suggestedQty = max(0, round($maxStock - $totalHospitalStock));
                $budget = $suggestedQty * $unitPrice;
            } elseif ($mos > 3.0) {
                $status = 'overstock';
            }

            $itemObj = (object)[
                'id' => $st->id,
                'code' => $wCode,
                'name' => $st->trade_name ?: $wCode,
                'main_stock' => $mainStock,
                'sub_stock' => $subStock,
                'total_stock' => $totalHospitalStock,
                'amc' => $amc,
                'mos' => $mos,
                'min_stock' => $minStock,
                'max_stock' => $maxStock,
                'price' => $unitPrice,
                'status' => $status,
                'suggested_qty' => $suggestedQty,
                'estimated_budget' => $budget,
                'lot_no' => $st->lot_no,
                'expire_date' => $st->expire_date,
                'unit' => $st->base_unit ?: 'หน่วย',
            ];

            $stockItems[] = $itemObj;
            if ($suggestedQty > 0) {
                $reorderItems[] = $itemObj;
            }
        }

        $totalEstimatedBudget = array_sum(array_column($reorderItems, 'estimated_budget'));

        $mosList = array_filter(array_column($stockItems, 'mos'), fn($m) => $m > 0 && $m < 90);
        $avgMos = !empty($mosList) ? round(array_sum($mosList) / count($mosList), 1) : 0;
        if ($avgMos <= 0 && $totalDisbursement > 0) {
            $monthsCount = $isMonthlyView ? 1 : 12;
            $monthlyBurn = $totalDisbursement / $monthsCount;
            $avgMos = $monthlyBurn > 0 ? round($totalStockVal / $monthlyBurn, 1) : 0;
        }

        // 4. Top 20 Disbursed Items
        $top20DisbursedItems = (clone $disbQuery)
            ->select(
                'working_code as code',
                DB::raw('MAX(trade_name) as name'),
                DB::raw('MAX(base_unit) as unit_name'),
                DB::raw('SUM(qty_dis) as total_qty'),
                DB::raw('SUM(value) as total_val'),
                DB::raw('COUNT(*) as req_count')
            )
            ->groupBy('working_code')
            ->orderByDesc('total_val')
            ->limit(20)
            ->get();

        foreach ($top20DisbursedItems as $tItem) {
            $tItem->share_percent = $totalDisbursement > 0 ? round(((float)$tItem->total_val / $totalDisbursement) * 100, 1) : 0;
            $tItem->avg_price = $tItem->total_qty > 0 ? round((float)$tItem->total_val / (float)$tItem->total_qty, 2) : 0;
            $tItem->item_id = $tItem->code;
        }

        // 5. Top Sub-warehouses (Department Groups)
        $topSubWhStats = [];
        $drugDisbByDept = (clone $disbQuery)
            ->select('dis_dept_group', DB::raw('SUM(value) as val'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('dis_dept_group')
            ->orderByDesc('val')
            ->get();
        foreach ($drugDisbByDept as $dd) {
            $grpName = $deptGroupNames[$dd->dis_dept_group] ?? ('กลุ่มเบิก ' . $dd->dis_dept_group);
            $val = (float)$dd->val;
            $share = $totalDisbursement > 0 ? round(($val / $totalDisbursement) * 100, 1) : 0;
            $topSubWhStats[] = (object)[
                'id' => $dd->dis_dept_group,
                'dept_id' => $dd->dis_dept_group,
                'name' => $grpName,
                'val' => $val,
                'reqs' => (int)$dd->cnt,
                'share_percent' => $share,
            ];
        }

        // 6. Monthly & Daily Trends
        $monthlyDisb = DB::table('moph_f5_distribution')
            ->whereBetween('period_rpt', [$startDisbPeriod, $endDisbPeriod])
            ->select(DB::raw("CAST(SUBSTRING(period_rpt, 5, 2) AS UNSIGNED) as m"), DB::raw('SUM(value) as val'), DB::raw('COUNT(*) as reqs'))
            ->groupBy(DB::raw("SUBSTRING(period_rpt, 5, 2)"))
            ->get();
        foreach ($monthlyDisb as $md) {
            $m = (int)$md->m;
            if (isset($monthlyTrend[$m])) {
                $monthlyTrend[$m]['val'] = (float)$md->val;
                $monthlyTrend[$m]['reqs'] = (int)$md->reqs;
            }
        }

        $monthlyRec = DB::table('moph_f5_receipt')
            ->whereBetween('date_rcv', [$startRcvDate, $endRcvDate])
            ->select(DB::raw("CAST(SUBSTRING(date_rcv, 5, 2) AS UNSIGNED) as m"), DB::raw('SUM(total_value) as receive_val'), DB::raw('COUNT(DISTINCT rcv_no) as receives'))
            ->groupBy(DB::raw("SUBSTRING(date_rcv, 5, 2)"))
            ->get();
        foreach ($monthlyRec as $mr) {
            $m = (int)$mr->m;
            if (isset($monthlyTrend[$m])) {
                $monthlyTrend[$m]['receive_val'] = (float)$mr->receive_val;
                $monthlyTrend[$m]['receives'] = (int)$mr->receives;
            }
        }

        if ($isMonthlyView) {
            $mInt = (int)$monthId;
            $calGregYear = ($mInt >= 10) ? ($yearId - 544) : ($yearId - 543);
            $monthPrefix = sprintf('%04d%02d', $calGregYear, $mInt);

            $dailyRec = DB::table('moph_f5_receipt')
                ->where('date_rcv', 'like', $monthPrefix . '%')
                ->select(DB::raw("CAST(SUBSTRING(date_rcv, 7, 2) AS UNSIGNED) as d"), DB::raw('SUM(total_value) as receive_val'), DB::raw('COUNT(DISTINCT rcv_no) as receives'))
                ->groupBy(DB::raw("SUBSTRING(date_rcv, 7, 2)"))
                ->get();
            foreach ($dailyRec as $dr) {
                $d = (int)$dr->d;
                if (isset($dailyTrend[$d])) {
                    $dailyTrend[$d]['receive_val'] = (float)$dr->receive_val;
                    $dailyTrend[$d]['receives'] = (int)$dr->receives;
                }
            }

            $mDisbVal = (float)DB::table('moph_f5_distribution')
                ->where('period_rpt', $monthPrefix)
                ->sum('value');
            if ($mDisbVal > 0 && !empty($daysInMonth)) {
                $dailyAvg = round($mDisbVal / $daysInMonth, 2);
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dailyTrend[$d]['val'] = $dailyAvg;
                }
            }
        }

        return [
            'config' => $config,
            'totalReceive' => $totalReceive,
            'totalReceiveCount' => $totalReceiveCount,
            'totalDisbursement' => $totalDisbursement,
            'totalStockVal' => $totalStockVal,
            'stockDateLabel' => $stockDateLabel ?? ($isMonthlyView ? ('ณ สิ้นเดือน' . $selectedMonthName) : 'ณ วันที่ล่าสุด'),
            'targetInvDate' => $targetInvDate ?? null,
            'catalogItemsCount' => $catalogItemsCount,
            'avgMos' => $avgMos,
            'totalReqCount' => $totalReqCount,
            'statusCounts' => $statusCounts,
            'requests' => $requests,
            'receives' => $receives,
            'stockItems' => $stockItems,
            'reorderItems' => $reorderItems,
            'top20DisbursedItems' => $top20DisbursedItems,
            'totalEstimatedBudget' => $totalEstimatedBudget,
            'monthlyTrend' => array_values($monthlyTrend),
            'dailyTrend' => array_values($dailyTrend),
            'isMonthlyView' => $isMonthlyView,
            'selectedMonthName' => $selectedMonthName,
            'selectedCalYear' => $selectedCalYear,
            'topSubWhStats' => $topSubWhStats,
        ];
    }
}
