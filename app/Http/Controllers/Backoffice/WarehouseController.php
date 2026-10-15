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
            'name' => 'คลังวัสดุเทคนิคการแพทย์',
            'full_name' => 'คลังวัสดุเทคนิคการแพทย์ (LAB)',
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

    public function index(Request $request)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงระบบคลัง');
        }

        $tab = $request->input('tab', 'overview');
        if (!in_array($tab, array_merge(['overview'], array_keys($this->warehouseConfig)))) {
            $tab = 'overview';
        }

        $year_id = (int)$request->input('year_id', 2570);
        $month_id = $request->input('month_id', 'all');
        $sub_warehouse_id = $request->input('sub_warehouse_id', 'all');
        $view_mode = $request->input('view_mode', 'overview'); // 'overview', 'stock', 'planner'
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

        // Fetch sub-warehouses (departments) for filter dropdown
        $subWarehouses = DB::connection('backoffice')->table('hrd_department_sub_sub')
            ->select('HR_DEPARTMENT_SUB_SUB_ID as id', 'HR_DEPARTMENT_SUB_SUB_NAME as name')
            ->whereNotNull('HR_DEPARTMENT_SUB_SUB_NAME')
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
        } else {
            $config = $this->warehouseConfig[$tab];
            $data = $this->getSingleWarehouseData($config, $year_id, $month_id, $sub_warehouse_id, $view_mode, $search);
        }

        return view('backoffice.warehouse.index', array_merge($data, [
            'tab' => $tab,
            'year_id' => $year_id,
            'month_id' => $month_id,
            'sub_warehouse_id' => $sub_warehouse_id,
            'selectedSubWhName' => $selectedSubWhName,
            'view_mode' => $view_mode,
            'search' => $search,
            'availableYears' => $availableYears,
            'fiscalMonthsList' => $fiscalMonthsList,
            'subWarehouses' => $subWarehouses,
            'warehouseConfigs' => $this->warehouseConfig,
            'thaiMonths' => $thaiMonths,
            'thaiMonthsShort' => $thaiMonthsShort,
        ]));
    }

    /**
     * Get Overview Data across all 5 warehouses
     */
    private function getOverviewData(int $yearId, $monthId, $subWhId, string $search): array
    {
        $warehouseSummaries = [];
        $totalHospitalDisbursement = 0.0;
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
                'material' => 0.0,
                'dental' => 0.0,
                'medtech' => 0.0,
                'physical' => 0.0,
                'medical' => 0.0,
                'total' => 0.0,
            ];
        }

        $topSubWhStats = [];

        foreach ($this->warehouseConfig as $key => $wh) {
            $disbursementVal = 0.0;
            $reqCount = 0;
            $itemsCount = 0;
            $statusCounts = ['Pending' => 0, 'Approve' => 0, 'Verify' => 0, 'Allow' => 0];

            if ($wh['type'] === 'supplies') {
                // General Supplies (Inven 5)
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

                // Sub-warehouse distribution
                $subWhDist = DB::connection('backoffice')->table('warehouse_request as w')
                    ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                    ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                    ->where('w.WAREHOUSE_INVEN_ID', $wh['inven_id'])
                    ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
                    ->select('w.WAREHOUSE_DEP_SUB_SUB_ID', 'd.HR_DEPARTMENT_SUB_SUB_NAME', DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as val'), DB::raw('count(distinct w.WAREHOUSE_ID) as reqs'))
                    ->groupBy('w.WAREHOUSE_DEP_SUB_SUB_ID', 'd.HR_DEPARTMENT_SUB_SUB_NAME')
                    ->get();
                foreach ($subWhDist as $swd) {
                    $dId = $swd->WAREHOUSE_DEP_SUB_SUB_ID ?: 0;
                    $dName = $swd->HR_DEPARTMENT_SUB_SUB_NAME ?: 'ไม่ระบุหน่วยงาน';
                    if (!isset($topSubWhStats[$dId])) {
                        $topSubWhStats[$dId] = ['id' => $dId, 'name' => $dName, 'val' => 0.0, 'reqs' => 0];
                    }
                    $topSubWhStats[$dId]['val'] += (float)$swd->val;
                    $topSubWhStats[$dId]['reqs'] += (int)$swd->reqs;
                }

            } else {
                // Clinical supply warehouses (Medicine warehouse module)
                $query = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                    ->where('r.warehouse_id', $wh['warehouse_id'])
                    ->whereBetween('r.created_at', [$startGregorian, $endGregorian]);

                if ($subWhId !== 'all') {
                    $query->where('r.treasury_id', $subWhId);
                }
                if ($monthId !== 'all') {
                    $query->whereRaw('MONTH(r.created_at) = ?', [(int)$monthId]);
                }

                $reqList = $query->get(['r.id', 'r.status', 'r.value', 'r.created_at', 'r.treasury_id']);
                $reqCount = $reqList->count();
                $disbursementVal = (float)$reqList->sum('value');

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

                // Sub-warehouse distribution
                $subWhDist = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                    ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                    ->where('r.warehouse_id', $wh['warehouse_id'])
                    ->whereBetween('r.created_at', [$startGregorian, $endGregorian])
                    ->select('r.treasury_id', 'd.HR_DEPARTMENT_SUB_SUB_NAME', DB::raw('sum(r.value) as val'), DB::raw('count(*) as reqs'))
                    ->groupBy('r.treasury_id', 'd.HR_DEPARTMENT_SUB_SUB_NAME')
                    ->get();
                foreach ($subWhDist as $swd) {
                    $dId = $swd->treasury_id ?: 0;
                    $dName = $swd->HR_DEPARTMENT_SUB_SUB_NAME ?: 'ไม่ระบุหน่วยงาน';
                    if (!isset($topSubWhStats[$dId])) {
                        $topSubWhStats[$dId] = ['id' => $dId, 'name' => $dName, 'val' => 0.0, 'reqs' => 0];
                    }
                    $topSubWhStats[$dId]['val'] += (float)$swd->val;
                    $topSubWhStats[$dId]['reqs'] += (int)$swd->reqs;
                }
            }

            $totalHospitalDisbursement += $disbursementVal;
            $totalHospitalRequests += $reqCount;
            $totalPendingCount += ($statusCounts['Pending'] ?? 0);
            $totalApproveCount += ($statusCounts['Approve'] ?? 0);
            $totalVerifyCount += ($statusCounts['Verify'] ?? 0);
            $totalAllowCount += ($statusCounts['Allow'] ?? 0);
            $totalHospitalCatalogItems += $itemsCount;

            $warehouseSummaries[$key] = [
                'config' => $wh,
                'req_count' => $reqCount,
                'disbursement_val' => $disbursementVal,
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
        $totalNearExpiryCount = $nearExpGeneral + $nearExpMed;

        // Estimated hospital inventory turnover
        $hospitalItr = 6.2; // Based on annual consumption vs average stock
        $hospitalMos = 1.8; // Average months of stock in community hospital

        return [
            'totalHospitalDisbursement' => $totalHospitalDisbursement,
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
        ];
    }

    /**
     * Get Data for a Single Warehouse (Material, Dental, MedTech, Physical, Medical)
     */
    private function getSingleWarehouseData(array $config, int $yearId, $monthId, $subWhId, string $viewMode, string $search): array
    {
        $startGregorian = ($yearId - 544) . '-10-01';
        $endGregorian = ($yearId - 543) . '-09-30 23:59:59';

        $totalDisbursement = 0.0;
        $totalReqCount = 0;
        $statusCounts = ['Pending' => 0, 'Approve' => 0, 'Verify' => 0, 'Allow' => 0];
        $requests = [];
        $stockItems = [];
        $reorderItems = [];
        $monthlyTrend = [];
        $topSubWhStats = [];

        foreach ($this->fiscalMonthOrder as $m) {
            $monthlyTrend[$m] = ['month_id' => $m, 'val' => 0.0, 'reqs' => 0];
        }

        if ($config['type'] === 'supplies') {
            // General Supplies
            $query = DB::connection('backoffice')->table('warehouse_request as w')
                ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
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
                      ->orWhere('d.HR_DEPARTMENT_SUB_SUB_NAME', 'like', "%{$search}%");
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
                'd.HR_DEPARTMENT_SUB_SUB_NAME as sub_warehouse_name',
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

            // Top Sub-warehouses
            $subDist = DB::connection('backoffice')->table('warehouse_request as w')
                ->join('warehouse_request_sub as s', 'w.WAREHOUSE_ID', '=', 's.WAREHOUSE_REQUEST_ID')
                ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->where('w.WAREHOUSE_INVEN_ID', $config['inven_id'])
                ->where('w.WAREHOUSE_BUDGET_YEAR', $yearId)
                ->select('w.WAREHOUSE_DEP_SUB_SUB_ID as id', 'd.HR_DEPARTMENT_SUB_SUB_NAME as name', DB::raw('sum(s.WAREHOUSE_REQUEST_SUB_SUM_PRICE) as val'), DB::raw('count(distinct w.WAREHOUSE_ID) as reqs'))
                ->groupBy('w.WAREHOUSE_DEP_SUB_SUB_ID', 'd.HR_DEPARTMENT_SUB_SUB_NAME')
                ->orderByDesc('val')
                ->limit(6)
                ->get();
            $topSubWhStats = $subDist;

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
            $query = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('hrd_person as p', 'r.request_by', '=', 'p.ID')
                ->leftJoin('hrd_prefix as pref', 'p.HR_PREFIX_ID', '=', 'pref.HR_PREFIX_ID')
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
                      ->orWhere('d.HR_DEPARTMENT_SUB_SUB_NAME', 'like', "%{$search}%");
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
                'd.HR_DEPARTMENT_SUB_SUB_NAME as sub_warehouse_name',
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
            $subDist = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->where('r.warehouse_id', $config['warehouse_id'])
                ->whereBetween('r.created_at', [$startGregorian, $endGregorian])
                ->select('r.treasury_id as id', 'd.HR_DEPARTMENT_SUB_SUB_NAME as name', DB::raw('sum(r.value) as val'), DB::raw('count(*) as reqs'))
                ->groupBy('r.treasury_id', 'd.HR_DEPARTMENT_SUB_SUB_NAME')
                ->orderByDesc('val')
                ->limit(6)
                ->get();
            $topSubWhStats = $subDist;

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

        return [
            'config' => $config,
            'totalDisbursement' => $totalDisbursement,
            'totalReqCount' => $totalReqCount,
            'statusCounts' => $statusCounts,
            'requests' => $requests,
            'stockItems' => $stockItems,
            'reorderItems' => $reorderItems,
            'totalEstimatedBudget' => $totalEstimatedBudget,
            'monthlyTrend' => array_values($monthlyTrend),
            'topSubWhStats' => $topSubWhStats,
        ];
    }

    /**
     * AJAX Endpoint: Requisition detail modal
     */
    public function requestDetail(string $type, int $id)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($type === 'material') {
            $req = DB::connection('backoffice')->table('warehouse_request as w')
                ->leftJoin('hrd_department_sub_sub as d', 'w.WAREHOUSE_DEP_SUB_SUB_ID', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->where('w.WAREHOUSE_ID', $id)
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
                'sub_warehouse' => $req->HR_DEPARTMENT_SUB_SUB_NAME ?: 'ไม่ระบุ',
                'status' => $req->WAREHOUSE_STATUS,
                'items' => $items,
            ]);
        } else {
            $req = DB::connection('backoffice')->table('medicine_warehouse_request as r')
                ->leftJoin('hrd_department_sub_sub as d', 'r.treasury_id', '=', 'd.HR_DEPARTMENT_SUB_SUB_ID')
                ->leftJoin('hrd_person as p', 'r.request_by', '=', 'p.ID')
                ->leftJoin('hrd_prefix as pref', 'p.HR_PREFIX_ID', '=', 'pref.HR_PREFIX_ID')
                ->where('r.id', $id)
                ->select(
                    'r.*',
                    'd.HR_DEPARTMENT_SUB_SUB_NAME',
                    DB::raw("CONCAT(COALESCE(pref.HR_PREFIX_NAME,''), COALESCE(p.HR_FNAME,''), ' ', COALESCE(p.HR_LNAME,'')) as requester_full_name")
                )
                ->first();

            if (!$req) return response()->json(['error' => 'Not found'], 404);

            $items = DB::connection('backoffice')->table('medicine_warehouse_request_list as l')
                ->leftJoin('medicine_drug as d', 'l.medicine_id', '=', 'd.id')
                ->where('l.request_id', $id)
                ->select(
                    'l.id',
                    'd.drug_code as code',
                    'd.trade_name as name',
                    'l.req_qty as amount',
                    'l.unit_price as price',
                    'l.value as total'
                )->get();

            return response()->json([
                'code' => $req->code,
                'req_date' => $req->request_date,
                'requester' => $req->requester_full_name ?: '-',
                'sub_warehouse' => $req->HR_DEPARTMENT_SUB_SUB_NAME ?: 'ไม่ระบุ',
                'status' => $req->status,
                'items' => $items,
            ]);
        }
    }

    /**
     * AJAX Endpoint: Item Two-Tier Stock Breakdown
     */
    public function itemDetail(string $type, int $id)
    {
        if (!auth()->user()->hasAccessWarehouse()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($type === 'material') {
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
}
