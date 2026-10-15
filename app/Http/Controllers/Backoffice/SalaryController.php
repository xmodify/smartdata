<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalaryController extends Controller
{
    private array $thaiMonths = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม',
    ];

    private array $thaiMonthsShort = [
        1 => 'ม.ค.',
        2 => 'ก.พ.',
        3 => 'มี.ค.',
        4 => 'เม.ย.',
        5 => 'พ.ค.',
        6 => 'มิ.ย.',
        7 => 'ก.ค.',
        8 => 'ส.ค.',
        9 => 'ก.ย.',
        10 => 'ต.ค.',
        11 => 'พ.ย.',
        12 => 'ธ.ค.',
    ];

    private array $fiscalMonthOrder = [10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    public function index(Request $request)
    {
        if (!auth()->user()->hasAccessPersonnel()) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงรายงานเงินเดือนและค่าตอบแทน');
        }

        // Active tab: 'overview', 'salary', or 'compen' (Default: 'overview' as recommended for executives)
        $tab = in_array($request->tab, ['overview', 'salary', 'compen']) ? $request->tab : 'overview';

        // Compensation sub-type filter (applicable when tab === 'compen')
        $compen_type = $request->compen_type ?: 'all';
        $availableCompenTypes = [
            'all' => 'ทั้งหมด',
            'OT' => 'OT (ปฏิบัติงานนอกเวลา)',
            'ฉ.11' => 'ฉ.11 (เบี้ยเลี้ยงเหมาจ่าย)',
            'ค่าไม่เปิดเวช' => 'ค่าไม่เปิดเวช',
            'พตส.' => 'พตส.',
            'ค่าตรวจชันสูตร' => 'ค่าตรวจชันสูตร',
        ];

        // Available fiscal years
        $availableYears = DB::connection('backoffice')
            ->table('salary_all')
            ->distinct()
            ->orderBy('SALARYALL_YEAR_ID', 'desc')
            ->pluck('SALARYALL_YEAR_ID')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = ['2569'];
        }

        $year_id = $request->year_id && in_array($request->year_id, $availableYears) 
            ? $request->year_id 
            : $availableYears[0];

        // Available months for this year and type
        $rawMonthsQuery = DB::connection('backoffice')
            ->table('salary_all')
            ->where('SALARYALL_YEAR_ID', $year_id);

        if ($tab !== 'overview') {
            $rawMonthsQuery->where('SALARYALL_TYPE', $tab);
        }

        $rawMonths = $rawMonthsQuery->distinct()
            ->pluck('SALARYALL_MONTH_ID')
            ->map(fn($m) => (int)$m)
            ->toArray();

        $availableMonths = array_values(array_intersect($this->fiscalMonthOrder, $rawMonths));

        // Default month: 'all' as requested by user ("tab แรกรวมทั้งปี")
        $month_id = $request->has('month_id') ? (string)$request->month_id : 'all';

        // Build complete 12 fiscal months list for the sub-tabs
        $fiscalMonthsList = [];
        foreach ($this->fiscalMonthOrder as $m) {
            $calYear = in_array($m, [10, 11, 12]) ? ((int)$year_id - 1) : (int)$year_id;
            $hasData = in_array($m, $rawMonths);
            $fiscalMonthsList[] = [
                'month_id' => (string)$m,
                'name_short' => $this->thaiMonthsShort[$m],
                'name_full' => $this->thaiMonths[$m],
                'cal_year' => $calYear,
                'year_short' => substr((string)$calYear, -2),
                'label' => $this->thaiMonthsShort[$m] . ' ' . substr((string)$calYear, -2),
                'has_data' => $hasData,
            ];
        }

        // Departments filter list
        $depts = DB::connection('backoffice')->select('
            SELECT DISTINCT hrds.HR_DEPARTMENT_SUB_SUB_ID, hrds.HR_DEPARTMENT_SUB_SUB_NAME, hds.HR_DEPARTMENT_ID
            FROM hrd_person hr
            INNER JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID = hr.HR_DEPARTMENT_SUB_SUB_ID
            LEFT JOIN hrd_department_sub hds ON hds.HR_DEPARTMENT_SUB_ID = hrds.HR_DEPARTMENT_SUB_ID
            WHERE hr.HR_STATUS_ID = 1
            ORDER BY hds.HR_DEPARTMENT_ID, hrds.HR_DEPARTMENT_SUB_SUB_NAME
        ');

        $dept_ids = $request->dept_ids ?: [];

        // Build human-friendly label for selected departments
        $selectedDeptNames = [];
        if (!empty($dept_ids)) {
            foreach ($depts as $d) {
                if (in_array($d->HR_DEPARTMENT_SUB_SUB_ID, $dept_ids)) {
                    $selectedDeptNames[] = $d->HR_DEPARTMENT_SUB_SUB_NAME;
                }
            }
        }

        if (empty($dept_ids) || count($selectedDeptNames) === 0 || count($selectedDeptNames) >= count($depts)) {
            $selectedDeptText = 'ทั้งหมด';
        } elseif (count($selectedDeptNames) === 1) {
            $selectedDeptText = $selectedDeptNames[0];
        } elseif (count($selectedDeptNames) === 2) {
            $selectedDeptText = implode(', ', $selectedDeptNames);
        } else {
            $selectedDeptText = $selectedDeptNames[0] . ' และอีก ' . (count($selectedDeptNames) - 1) . ' หน่วยงาน';
        }

        // Department WHERE clause
        $where_dept = "";
        $dept_params = [];
        if (!empty($dept_ids)) {
            $placeholders = implode(',', array_fill(0, count($dept_ids), '?'));
            $where_dept = " AND hrds.HR_DEPARTMENT_SUB_SUB_ID IN ($placeholders) ";
            $dept_params = $dept_ids;
        }

        // Monthly Trend Query across the entire Fiscal Year (for Line/Area Chart)
        if (!empty($dept_ids)) {
            $trendParams = array_merge([$year_id], $dept_params);
            $trendsRaw = DB::connection('backoffice')->select("
                SELECT 
                    s.SALARYALL_TYPE,
                    CAST(s.SALARYALL_MONTH_ID AS UNSIGNED) as month_num,
                    SUM(CAST(s.SALARYALL_TOTAL AS DECIMAL(12,2))) as total_net
                FROM salary_all s
                LEFT JOIN hrd_person p ON p.ID = CAST(s.SALARYALL_PERSON_ID AS UNSIGNED)
                LEFT JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID = p.HR_DEPARTMENT_SUB_SUB_ID
                WHERE s.SALARYALL_YEAR_ID = ? {$where_dept}
                GROUP BY s.SALARYALL_TYPE, month_num
            ", $trendParams);
        } else {
            $trendsRaw = DB::connection('backoffice')->select("
                SELECT 
                    s.SALARYALL_TYPE,
                    CAST(s.SALARYALL_MONTH_ID AS UNSIGNED) as month_num,
                    SUM(CAST(s.SALARYALL_TOTAL AS DECIMAL(12,2))) as total_net
                FROM salary_all s
                WHERE s.SALARYALL_YEAR_ID = ?
                GROUP BY s.SALARYALL_TYPE, month_num
            ", [$year_id]);
        }

        $trendSalary = array_fill_keys($this->fiscalMonthOrder, 0);
        $trendCompen = array_fill_keys($this->fiscalMonthOrder, 0);
        $trendGrand = array_fill_keys($this->fiscalMonthOrder, 0);

        foreach ($trendsRaw as $t) {
            $mNum = (int)$t->month_num;
            if (isset($trendSalary[$mNum])) {
                if ($t->SALARYALL_TYPE === 'salary') {
                    $trendSalary[$mNum] = round((float)$t->total_net, 2);
                } elseif ($t->SALARYALL_TYPE === 'compen') {
                    $trendCompen[$mNum] = round((float)$t->total_net, 2);
                }
            }
        }

        foreach ($this->fiscalMonthOrder as $mNum) {
            $trendGrand[$mNum] = round($trendSalary[$mNum] + $trendCompen[$mNum], 2);
        }

        $trendMonthLabels = [];
        foreach ($this->fiscalMonthOrder as $mNum) {
            $calYear = in_array($mNum, [10, 11, 12]) ? ((int)$year_id - 1) : (int)$year_id;
            $trendMonthLabels[] = $this->thaiMonthsShort[$mNum] . ' ' . substr((string)$calYear, -2);
        }

        // Sub-trend query for compensation categories when in 'compen' tab
        $trendCompenSeries = [];
        if ($tab === 'compen') {
            $monthlyCompenItems = DB::connection('backoffice')->table('salary_all_receive as r')
                ->join('salary_all as s', 's.SALARYALL_ID', '=', 'r.SALARYALL_ID')
                ->where('s.SALARYALL_YEAR_ID', $year_id)
                ->where('s.SALARYALL_TYPE', 'compen')
                ->select(
                    's.SALARYALL_MONTH_ID',
                    'r.SALARYALL_RECEIVE_LISTNAME',
                    DB::raw('SUM(CAST(r.SALARYALL_RECEIVE_AMOUNT AS DECIMAL(12,2))) as total')
                )
                ->groupBy('s.SALARYALL_MONTH_ID', 'r.SALARYALL_RECEIVE_LISTNAME')
                ->get();

            $seriesOT = array_fill_keys($this->fiscalMonthOrder, 0);
            $seriesCh11 = array_fill_keys($this->fiscalMonthOrder, 0);
            $seriesNoClinic = array_fill_keys($this->fiscalMonthOrder, 0);
            $seriesOther = array_fill_keys($this->fiscalMonthOrder, 0);
            $seriesSpecific = array_fill_keys($this->fiscalMonthOrder, 0);

            foreach ($monthlyCompenItems as $item) {
                $m = (int)$item->SALARYALL_MONTH_ID;
                $val = round((float)$item->total, 2);
                if (!isset($seriesOT[$m])) continue;

                if ($compen_type !== 'all' && $item->SALARYALL_RECEIVE_LISTNAME === $compen_type) {
                    $seriesSpecific[$m] += $val;
                }

                if ($item->SALARYALL_RECEIVE_LISTNAME === 'OT') {
                    $seriesOT[$m] += $val;
                } elseif ($item->SALARYALL_RECEIVE_LISTNAME === 'ฉ.11') {
                    $seriesCh11[$m] += $val;
                } elseif ($item->SALARYALL_RECEIVE_LISTNAME === 'ค่าไม่เปิดเวช') {
                    $seriesNoClinic[$m] += $val;
                } else {
                    $seriesOther[$m] += $val;
                }
            }

            if ($compen_type === 'all') {
                $trendCompenSeries = [
                    ['name' => 'OT (ปฏิบัติงานนอกเวลา)', 'data' => array_values($seriesOT), 'color' => '#f59e0b'],
                    ['name' => 'ฉ.11 (เบี้ยเลี้ยงเหมาจ่าย)', 'data' => array_values($seriesCh11), 'color' => '#3b82f6'],
                    ['name' => 'ค่าไม่เปิดเวช', 'data' => array_values($seriesNoClinic), 'color' => '#10b981'],
                    ['name' => 'พตส. และอื่นๆ', 'data' => array_values($seriesOther), 'color' => '#ec4899'],
                    ['name' => 'รวมค่าตอบแทนสุทธิ', 'data' => array_values($trendCompen), 'color' => '#0ea5e9'],
                ];
            } else {
                $trendCompenSeries = [
                    ['name' => $availableCompenTypes[$compen_type] ?? $compen_type, 'data' => array_values($seriesSpecific), 'color' => '#0ea5e9'],
                ];
            }
        }

        $fiscalTrendData = [
            'labels' => $trendMonthLabels,
            'salary' => array_values($trendSalary),
            'compen' => array_values($trendCompen),
            'grand' => array_values($trendGrand),
            'compen_series' => $trendCompenSeries,
            'total_salary_year' => array_sum($trendSalary),
            'total_compen_year' => array_sum($trendCompen),
            'total_grand_year' => array_sum($trendGrand),
        ];

        // -------------------------------------------------------------
        // Base rows and aggregation
        // -------------------------------------------------------------
        $monthWhere = ($month_id !== 'all') ? " AND s.SALARYALL_MONTH_ID = ? " : "";
        $typeWhere = ($tab !== 'overview') ? " AND s.SALARYALL_TYPE = ? " : "";

        $mainQueryParams = array_merge(
            [$year_id],
            ($month_id !== 'all' ? [$month_id] : []),
            ($tab !== 'overview' ? [$tab] : []),
            $dept_params
        );

        $baseRows = DB::connection('backoffice')->select("
            SELECT 
                s.SALARYALL_ID, s.SALARYALL_YEAR_ID, s.SALARYALL_MONTH_ID, s.SALARYALL_TYPE,
                s.SALARYALL_PERSON_ID, s.SALARYALL_PERSON_NAME, s.SALARYALL_SUB_NAME,
                s.SALARYALL_BOOK_NUM,
                CAST(s.SALARYALL_TOTAL AS DECIMAL(12,2)) as net_total,
                p.ID as person_id, p.HR_CID, p.POSITION_IN_WORK,
                hrp.HR_PREFIX_NAME, p.HR_FNAME, p.HR_LNAME,
                hrt.HR_PERSON_TYPE_NAME,
                COALESCE(hrds.HR_DEPARTMENT_SUB_SUB_ID, 0) as dept_id,
                COALESCE(hrds.HR_DEPARTMENT_SUB_SUB_NAME, s.SALARYALL_SUB_NAME, 'ไม่ระบุ') as department_name
            FROM salary_all s
            LEFT JOIN hrd_person p ON p.ID = CAST(s.SALARYALL_PERSON_ID AS UNSIGNED)
            LEFT JOIN hrd_prefix hrp ON hrp.HR_PREFIX_ID = p.HR_PREFIX_ID
            LEFT JOIN hrd_person_type hrt ON hrt.HR_PERSON_TYPE_ID = p.HR_PERSON_TYPE_ID
            LEFT JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID = p.HR_DEPARTMENT_SUB_SUB_ID
            WHERE s.SALARYALL_YEAR_ID = ? {$monthWhere} {$typeWhere} {$where_dept}
        ", $mainQueryParams);

        // Fetch receive and pay amounts using indexed SALARYALL_ID lookup
        $salaryIds = array_map(fn($r) => (string)$r->SALARYALL_ID, $baseRows);
        $recvMap = [];
        $payMap = [];
        $compenSpecificMap = [];

        if (!empty($salaryIds)) {
            $chunks = array_chunk($salaryIds, 1000);
            foreach ($chunks as $chunk) {
                // If filtering by specific compen_type (e.g., OT)
                if ($tab === 'compen' && $compen_type !== 'all') {
                    $itemRows = DB::connection('backoffice')->table('salary_all_receive')
                        ->whereIn('SALARYALL_ID', $chunk)
                        ->where('SALARYALL_RECEIVE_LISTNAME', $compen_type)
                        ->select('SALARYALL_ID', DB::raw('SUM(CAST(SALARYALL_RECEIVE_AMOUNT AS DECIMAL(12,2))) as item_amount'))
                        ->groupBy('SALARYALL_ID')
                        ->get();
                    foreach ($itemRows as $r) {
                        $compenSpecificMap[$r->SALARYALL_ID] = (float)$r->item_amount;
                    }
                }

                $recRows = DB::connection('backoffice')->table('salary_all_receive')
                    ->whereIn('SALARYALL_ID', $chunk)
                    ->select('SALARYALL_ID', DB::raw('SUM(CAST(SALARYALL_RECEIVE_AMOUNT AS DECIMAL(12,2))) as total_receive'))
                    ->groupBy('SALARYALL_ID')
                    ->get();
                foreach ($recRows as $r) {
                    $recvMap[$r->SALARYALL_ID] = (float)$r->total_receive;
                }

                $payRows = DB::connection('backoffice')->table('salary_all_pay')
                    ->whereIn('SALARYALL_ID', $chunk)
                    ->select('SALARYALL_ID', DB::raw('SUM(CAST(SALARYALL_PAY_AMOUNT AS DECIMAL(12,2))) as total_pay'))
                    ->groupBy('SALARYALL_ID')
                    ->get();
                foreach ($payRows as $r) {
                    $payMap[$r->SALARYALL_ID] = (float)$r->total_pay;
                }
            }
        }

        // Aggregate into Department Stats and Personnel List
        $deptStatsMap = [];
        $personMap = [];

        foreach ($baseRows as $row) {
            $salId = (string)$row->SALARYALL_ID;

            // If specific compen_type is selected, only include rows that received this item
            if ($tab === 'compen' && $compen_type !== 'all') {
                if (!isset($compenSpecificMap[$salId])) {
                    continue; // Skip persons who did not receive this specific compensation item
                }
                $recVal = $compenSpecificMap[$salId];
                $payVal = 0.0;
                $netVal = $compenSpecificMap[$salId];
            } else {
                $recVal = $recvMap[$salId] ?? 0.0;
                $payVal = $payMap[$salId] ?? 0.0;
                $netVal = (float)$row->net_total;
            }

            // Department Stats aggregation
            $dId = $row->dept_id;
            if (!isset($deptStatsMap[$dId])) {
                $deptStatsMap[$dId] = (object)[
                    'dept_id' => $dId,
                    'dept_name' => $row->department_name,
                    'person_ids' => [],
                    'person_count' => 0,
                    'total_receive' => 0.0,
                    'total_pay' => 0.0,
                    'total_net' => 0.0,
                    'salary_net' => 0.0,
                    'salary_rec' => 0.0,
                    'salary_pay' => 0.0,
                    'compen_net' => 0.0,
                    'compen_rec' => 0.0,
                    'compen_pay' => 0.0,
                ];
            }
            $deptStatsMap[$dId]->person_ids[$row->SALARYALL_PERSON_ID] = true;
            $deptStatsMap[$dId]->total_receive += $recVal;
            $deptStatsMap[$dId]->total_pay += $payVal;
            $deptStatsMap[$dId]->total_net += $netVal;
            if ($row->SALARYALL_TYPE === 'salary') {
                $deptStatsMap[$dId]->salary_net += $netVal;
                $deptStatsMap[$dId]->salary_rec += $recVal;
                $deptStatsMap[$dId]->salary_pay += $payVal;
            } elseif ($row->SALARYALL_TYPE === 'compen') {
                $deptStatsMap[$dId]->compen_net += $netVal;
                $deptStatsMap[$dId]->compen_rec += $recVal;
                $deptStatsMap[$dId]->compen_pay += $payVal;
            }

            // Person aggregation
            $pId = $row->SALARYALL_PERSON_ID;
            $fullName = ($row->HR_PREFIX_NAME || $row->HR_FNAME) 
                ? trim(($row->HR_PREFIX_NAME ?? '') . ($row->HR_FNAME ?? '') . ' ' . ($row->HR_LNAME ?? ''))
                : ($row->SALARYALL_PERSON_NAME ?: 'ไม่ระบุ');

            if ($month_id === 'all') {
                if (!isset($personMap[$pId])) {
                    $personMap[$pId] = (object)[
                        'SALARYALL_ID' => $row->SALARYALL_ID,
                        'SALARYALL_YEAR_ID' => $row->SALARYALL_YEAR_ID,
                        'SALARYALL_MONTH_ID' => 'all',
                        'SALARYALL_TYPE' => $row->SALARYALL_TYPE,
                        'SALARYALL_PERSON_ID' => $pId,
                        'SALARYALL_PERSON_NAME' => $fullName,
                        'SALARYALL_SUB_NAME' => $row->SALARYALL_SUB_NAME,
                        'SALARYALL_BOOK_NUM' => $row->SALARYALL_BOOK_NUM,
                        'net_total' => 0.0,
                        'salary_net' => 0.0,
                        'compen_net' => 0.0,
                        'person_id' => $row->person_id,
                        'HR_CID' => $row->HR_CID,
                        'POSITION_IN_WORK' => $row->POSITION_IN_WORK,
                        'HR_PREFIX_NAME' => $row->HR_PREFIX_NAME,
                        'HR_FNAME' => $row->HR_FNAME,
                        'HR_LNAME' => $row->HR_LNAME,
                        'HR_PERSON_TYPE_NAME' => $row->HR_PERSON_TYPE_NAME,
                        'HR_DEPARTMENT_SUB_SUB_ID' => $row->dept_id,
                        'department_name' => $row->department_name,
                        'total_receive' => 0.0,
                        'total_pay' => 0.0,
                        'months' => [],
                        'month_count' => 0,
                    ];
                }
                $personMap[$pId]->net_total += $netVal;
                if ($row->SALARYALL_TYPE === 'salary') {
                    $personMap[$pId]->salary_net += $netVal;
                } elseif ($row->SALARYALL_TYPE === 'compen') {
                    $personMap[$pId]->compen_net += $netVal;
                }
                $personMap[$pId]->total_receive += $recVal;
                $personMap[$pId]->total_pay += $payVal;
                $personMap[$pId]->months[$row->SALARYALL_MONTH_ID] = true;
                if ($row->SALARYALL_BOOK_NUM) {
                    $personMap[$pId]->SALARYALL_BOOK_NUM = $row->SALARYALL_BOOK_NUM;
                }
            } else {
                // Single month
                if ($tab === 'overview') {
                    if (!isset($personMap[$pId])) {
                        $personMap[$pId] = (object)[
                            'SALARYALL_ID' => $row->SALARYALL_ID,
                            'SALARYALL_YEAR_ID' => $row->SALARYALL_YEAR_ID,
                            'SALARYALL_MONTH_ID' => $row->SALARYALL_MONTH_ID,
                            'SALARYALL_TYPE' => 'overview',
                            'SALARYALL_PERSON_ID' => $pId,
                            'SALARYALL_PERSON_NAME' => $fullName,
                            'SALARYALL_SUB_NAME' => $row->SALARYALL_SUB_NAME,
                            'SALARYALL_BOOK_NUM' => $row->SALARYALL_BOOK_NUM,
                            'net_total' => 0.0,
                            'salary_net' => 0.0,
                            'compen_net' => 0.0,
                            'person_id' => $row->person_id,
                            'HR_CID' => $row->HR_CID,
                            'POSITION_IN_WORK' => $row->POSITION_IN_WORK,
                            'HR_PREFIX_NAME' => $row->HR_PREFIX_NAME,
                            'HR_FNAME' => $row->HR_FNAME,
                            'HR_LNAME' => $row->HR_LNAME,
                            'HR_PERSON_TYPE_NAME' => $row->HR_PERSON_TYPE_NAME,
                            'HR_DEPARTMENT_SUB_SUB_ID' => $row->dept_id,
                            'department_name' => $row->department_name,
                            'total_receive' => 0.0,
                            'total_pay' => 0.0,
                            'month_count' => 1,
                        ];
                    }
                    $personMap[$pId]->net_total += $netVal;
                    if ($row->SALARYALL_TYPE === 'salary') {
                        $personMap[$pId]->salary_net += $netVal;
                    } elseif ($row->SALARYALL_TYPE === 'compen') {
                        $personMap[$pId]->compen_net += $netVal;
                    }
                    $personMap[$pId]->total_receive += $recVal;
                    $personMap[$pId]->total_pay += $payVal;
                } else {
                    $personMap[] = (object)[
                        'SALARYALL_ID' => $row->SALARYALL_ID,
                        'SALARYALL_YEAR_ID' => $row->SALARYALL_YEAR_ID,
                        'SALARYALL_MONTH_ID' => $row->SALARYALL_MONTH_ID,
                        'SALARYALL_TYPE' => $row->SALARYALL_TYPE,
                        'SALARYALL_PERSON_ID' => $pId,
                        'SALARYALL_PERSON_NAME' => $fullName,
                        'SALARYALL_SUB_NAME' => $row->SALARYALL_SUB_NAME,
                        'SALARYALL_BOOK_NUM' => $row->SALARYALL_BOOK_NUM,
                        'net_total' => $netVal,
                        'salary_net' => $row->SALARYALL_TYPE === 'salary' ? $netVal : 0.0,
                        'compen_net' => $row->SALARYALL_TYPE === 'compen' ? $netVal : 0.0,
                        'person_id' => $row->person_id,
                        'HR_CID' => $row->HR_CID,
                        'POSITION_IN_WORK' => $row->POSITION_IN_WORK,
                        'HR_PREFIX_NAME' => $row->HR_PREFIX_NAME,
                        'HR_FNAME' => $row->HR_FNAME,
                        'HR_LNAME' => $row->HR_LNAME,
                        'HR_PERSON_TYPE_NAME' => $row->HR_PERSON_TYPE_NAME,
                        'HR_DEPARTMENT_SUB_SUB_ID' => $row->dept_id,
                        'department_name' => $row->department_name,
                        'total_receive' => $recVal,
                        'total_pay' => $payVal,
                        'month_count' => 1,
                    ];
                }
            }
        }

        // Finalize dept stats
        $deptStats = array_values($deptStatsMap);
        foreach ($deptStats as $d) {
            $d->person_count = count($d->person_ids);
            unset($d->person_ids);
        }
        usort($deptStats, fn($a, $b) => $b->total_net <=> $a->total_net);

        // Finalize persons list
        if ($month_id === 'all' || $tab === 'overview') {
            $persons = array_values($personMap);
            if ($month_id === 'all') {
                foreach ($persons as $p) {
                    $p->month_count = count($p->months);
                    unset($p->months);
                }
            }
            usort($persons, fn($a, $b) => $b->net_total <=> $a->net_total);
        } else {
            $persons = $personMap;
            usort($persons, function($a, $b) {
                $cmp = strcmp($a->HR_PREFIX_NAME ?? '', $b->HR_PREFIX_NAME ?? '');
                if ($cmp !== 0) return $cmp;
                $cmp = strcmp($a->HR_FNAME ?? '', $b->HR_FNAME ?? '');
                if ($cmp !== 0) return $cmp;
                return strcmp($a->HR_LNAME ?? '', $b->HR_LNAME ?? '');
            });
        }

        // KPI Totals
        $total_net = (float)array_sum(array_column($deptStats, 'total_net'));
        $total_receive = (float)array_sum(array_column($deptStats, 'total_receive'));
        $total_pay = (float)array_sum(array_column($deptStats, 'total_pay'));
        $total_persons = count($persons);
        $total_depts_count = count($deptStats);
        $avg_per_person = $total_persons > 0 ? $total_net / $total_persons : 0;

        $total_salary_net_sum = (float)array_sum(array_column($deptStats, 'salary_net'));
        $total_salary_rec_sum = (float)array_sum(array_column($deptStats, 'salary_rec'));
        $total_salary_pay_sum = (float)array_sum(array_column($deptStats, 'salary_pay'));

        $total_compen_net_sum = (float)array_sum(array_column($deptStats, 'compen_net'));
        $total_compen_rec_sum = (float)array_sum(array_column($deptStats, 'compen_rec'));
        $total_compen_pay_sum = (float)array_sum(array_column($deptStats, 'compen_pay'));

        // Chart Data (Top 8 Departments)
        $chartDepts = array_slice($deptStats, 0, 8);
        $chartData = [
            'labels' => array_column($chartDepts, 'dept_name'),
            'net_totals' => array_map(fn($v) => round((float)$v, 2), array_column($chartDepts, 'total_net')),
            'receives' => array_map(fn($v) => round((float)$v, 2), array_column($chartDepts, 'total_receive')),
            'pays' => array_map(fn($v) => round((float)$v, 2), array_column($chartDepts, 'total_pay')),
        ];

        $thaiMonths = $this->thaiMonths;
        $thaiMonthsShort = $this->thaiMonthsShort;

        return view('backoffice.salary.index', compact(
            'tab', 'compen_type', 'availableCompenTypes', 'year_id', 'month_id',
            'availableYears', 'availableMonths', 'fiscalMonthsList',
            'depts', 'dept_ids', 'selectedDeptText', 'selectedDeptNames', 'deptStats', 'persons', 'chartData', 'fiscalTrendData',
            'total_net', 'total_receive', 'total_pay', 'total_persons',
            'total_depts_count', 'avg_per_person', 
            'total_salary_net_sum', 'total_salary_rec_sum', 'total_salary_pay_sum',
            'total_compen_net_sum', 'total_compen_rec_sum', 'total_compen_pay_sum',
            'thaiMonths', 'thaiMonthsShort'
        ));
    }

    /**
     * Get Person Monthly History across Fiscal Year (For Modal View)
     */
    public function personDetail(Request $request, $id)
    {
        if (!auth()->user()->hasAccessPersonnel()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $person = DB::connection('backoffice')->selectOne("
            SELECT p.ID, p.HR_CID, p.POSITION_IN_WORK, p.HR_PHONE, p.HR_EMAIL,
                   hrp.HR_PREFIX_NAME, p.HR_FNAME, p.HR_LNAME,
                   hrt.HR_PERSON_TYPE_NAME,
                   hrds.HR_DEPARTMENT_SUB_SUB_NAME,
                   hrd.HR_DEPARTMENT_NAME
            FROM hrd_person p
            LEFT JOIN hrd_prefix hrp ON hrp.HR_PREFIX_ID = p.HR_PREFIX_ID
            LEFT JOIN hrd_person_type hrt ON hrt.HR_PERSON_TYPE_ID = p.HR_PERSON_TYPE_ID
            LEFT JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID = p.HR_DEPARTMENT_SUB_SUB_ID
            LEFT JOIN hrd_department hrd ON hrd.HR_DEPARTMENT_ID = p.HR_DEPARTMENT_ID
            WHERE p.ID = ?
        ", [$id]);

        if (!$person) {
            return response()->json(['error' => 'ไม่พบข้อมูลบุคลากร'], 404);
        }

        // Available fiscal years for this person
        $availableYears = DB::connection('backoffice')
            ->table('salary_all')
            ->where('SALARYALL_PERSON_ID', (string)$id)
            ->distinct()
            ->orderBy('SALARYALL_YEAR_ID', 'desc')
            ->pluck('SALARYALL_YEAR_ID')
            ->toArray();

        if (empty($availableYears)) {
            $availableYears = ['2569'];
        }

        $year_id = $request->year_id && in_array($request->year_id, $availableYears)
            ? $request->year_id
            : $availableYears[0];

        // Fetch all salary and compen records for this person in selected year
        $records = DB::connection('backoffice')->select("
            SELECT s.SALARYALL_ID, s.SALARYALL_YEAR_ID, s.SALARYALL_MONTH_ID, s.SALARYALL_DAY_ID, s.SALARYALL_TYPE,
                   s.SALARYALL_BOOK_NUM, s.SALARYALL_SUB_NAME,
                   CAST(s.SALARYALL_TOTAL AS DECIMAL(12,2)) as net_total
            FROM salary_all s
            WHERE s.SALARYALL_PERSON_ID = ? AND s.SALARYALL_YEAR_ID = ?
            ORDER BY CAST(s.SALARYALL_MONTH_ID AS UNSIGNED)
        ", [(string)$id, $year_id]);

        $salIds = array_map(fn($r) => (string)$r->SALARYALL_ID, $records);
        $recvMap = [];
        $payMap = [];
        if (!empty($salIds)) {
            $recRows = DB::connection('backoffice')->table('salary_all_receive')
                ->whereIn('SALARYALL_ID', $salIds)
                ->select('SALARYALL_ID', DB::raw('SUM(CAST(SALARYALL_RECEIVE_AMOUNT AS DECIMAL(12,2))) as total_receive'))
                ->groupBy('SALARYALL_ID')
                ->get();
            foreach ($recRows as $r) $recvMap[$r->SALARYALL_ID] = (float)$r->total_receive;

            $payRows = DB::connection('backoffice')->table('salary_all_pay')
                ->whereIn('SALARYALL_ID', $salIds)
                ->select('SALARYALL_ID', DB::raw('SUM(CAST(SALARYALL_PAY_AMOUNT AS DECIMAL(12,2))) as total_pay'))
                ->groupBy('SALARYALL_ID')
                ->get();
            foreach ($payRows as $r) $payMap[$r->SALARYALL_ID] = (float)$r->total_pay;
        }

        foreach ($records as $r) {
            $salId = (string)$r->SALARYALL_ID;
            $r->total_receive = $recvMap[$salId] ?? 0.0;
            $r->total_pay = $payMap[$salId] ?? 0.0;
        }

        // Map by month and type
        $mapped = [];
        $book_num = null;
        foreach ($records as $r) {
            $m = (int)$r->SALARYALL_MONTH_ID;
            $mapped[$m][$r->SALARYALL_TYPE] = $r;
            if ($r->SALARYALL_BOOK_NUM) {
                $book_num = $r->SALARYALL_BOOK_NUM;
            }
        }

        // Build 12 months sequence according to Thai fiscal year: 10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8, 9
        $fiscalMonths = [];
        $sum_salary_net = 0;
        $sum_salary_rec = 0;
        $sum_salary_pay = 0;

        $sum_compen_net = 0;
        $sum_compen_rec = 0;
        $sum_compen_pay = 0;

        foreach ($this->fiscalMonthOrder as $m) {
            $calYear = in_array($m, [10, 11, 12]) ? ((int)$year_id - 1) : (int)$year_id;
            $salRow = $mapped[$m]['salary'] ?? null;
            $compRow = $mapped[$m]['compen'] ?? null;

            $salNet = $salRow ? (float)$salRow->net_total : 0;
            $salRec = $salRow ? (float)$salRow->total_receive : 0;
            $salPay = $salRow ? (float)$salRow->total_pay : 0;

            $compNet = $compRow ? (float)$compRow->net_total : 0;
            $compRec = $compRow ? (float)$compRow->total_receive : 0;
            $compPay = $compRow ? (float)$compRow->total_pay : 0;

            $combinedNet = $salNet + $compNet;

            $sum_salary_net += $salNet;
            $sum_salary_rec += $salRec;
            $sum_salary_pay += $salPay;

            $sum_compen_net += $compNet;
            $sum_compen_rec += $compRec;
            $sum_compen_pay += $compPay;

            $fiscalMonths[] = [
                'month_id' => $m,
                'cal_year' => $calYear,
                'month_name' => $this->thaiMonths[$m],
                'month_name_short' => $this->thaiMonthsShort[$m],
                'month_label' => $this->thaiMonths[$m] . ' ' . $calYear,
                'has_data' => ($salRow !== null || $compRow !== null),
                'salary' => $salRow ? [
                    'id' => $salRow->SALARYALL_ID,
                    'receive' => $salRec,
                    'pay' => $salPay,
                    'net' => $salNet,
                ] : null,
                'compen' => $compRow ? [
                    'id' => $compRow->SALARYALL_ID,
                    'receive' => $compRec,
                    'pay' => $compPay,
                    'net' => $compNet,
                ] : null,
                'combined_net' => $combinedNet,
            ];
        }

        $yearlySummary = [
            'salary_net' => $sum_salary_net,
            'salary_rec' => $sum_salary_rec,
            'salary_pay' => $sum_salary_pay,
            'compen_net' => $sum_compen_net,
            'compen_rec' => $sum_compen_rec,
            'compen_pay' => $sum_compen_pay,
            'grand_net' => $sum_salary_net + $sum_compen_net,
            'grand_rec' => $sum_salary_rec + $sum_compen_rec,
            'grand_pay' => $sum_salary_pay + $sum_compen_pay,
        ];

        return view('backoffice.salary.person_detail_modal', compact(
            'person', 'availableYears', 'year_id', 'fiscalMonths', 'yearlySummary', 'book_num'
        ));
    }

    /**
     * Get Slip itemized details for specific SALARYALL_ID (Income & Deductions)
     */
    public function slipDetail(Request $request, $salaryAllId)
    {
        if (!auth()->user()->hasAccessPersonnel()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $slip = DB::connection('backoffice')->selectOne("
            SELECT s.SALARYALL_ID, s.SALARYALL_YEAR_ID, s.SALARYALL_MONTH_ID, s.SALARYALL_DAY_ID,
                   s.SALARYALL_TYPE, s.SALARYALL_PERSON_ID, s.SALARYALL_PERSON_NAME,
                   s.SALARYALL_SUB_NAME, s.SALARYALL_BOOK_NUM, s.SALARYALL_TOTAL,
                   p.HR_CID, p.POSITION_IN_WORK, hrp.HR_PREFIX_NAME, hrt.HR_PERSON_TYPE_NAME
            FROM salary_all s
            LEFT JOIN hrd_person p ON p.ID = CAST(s.SALARYALL_PERSON_ID AS UNSIGNED)
            LEFT JOIN hrd_prefix hrp ON hrp.HR_PREFIX_ID = p.HR_PREFIX_ID
            LEFT JOIN hrd_person_type hrt ON hrt.HR_PERSON_TYPE_ID = p.HR_PERSON_TYPE_ID
            WHERE s.SALARYALL_ID = ?
        ", [$salaryAllId]);

        if (!$slip) {
            return response()->json(['error' => 'ไม่พบข้อมูลสลิปรายการ'], 404);
        }

        // Income items
        $receives = DB::connection('backoffice')->select("
            SELECT SALARYALL_RECEIVE_LISTNAME as item_name,
                   CAST(SALARYALL_RECEIVE_AMOUNT AS DECIMAL(12,2)) as amount,
                   is_tax
            FROM salary_all_receive
            WHERE SALARYALL_ID = ?
            ORDER BY amount DESC
        ", [$salaryAllId]);

        // Deduction items
        $pays = DB::connection('backoffice')->select("
            SELECT SALARYALL_PAY_LISTNAME as item_name,
                   CAST(SALARYALL_PAY_AMOUNT AS DECIMAL(12,2)) as amount
            FROM salary_all_pay
            WHERE SALARYALL_ID = ?
            ORDER BY amount DESC
        ", [$salaryAllId]);

        $totalReceive = array_sum(array_column($receives, 'amount'));
        $totalPay = array_sum(array_column($pays, 'amount'));
        $netSalary = (float)$slip->SALARYALL_TOTAL;

        $m = (int)$slip->SALARYALL_MONTH_ID;
        $monthName = $this->thaiMonths[$m] ?? "เดือน $m";

        return view('backoffice.salary.slip_modal', compact(
            'slip', 'receives', 'pays', 'totalReceive', 'totalPay', 'netSalary', 'monthName'
        ));
    }

    /**
     * Get Unified Monthly Slip for a person (both Salary & Compensation)
     */
    public function monthlySlip(Request $request, $personId, $yearId, $monthId)
    {
        if (!auth()->user()->hasAccessPersonnel()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $person = DB::connection('backoffice')->selectOne("
            SELECT p.ID, p.HR_CID, p.POSITION_IN_WORK, p.HR_PHONE, p.HR_EMAIL,
                   hrp.HR_PREFIX_NAME, p.HR_FNAME, p.HR_LNAME,
                   hrt.HR_PERSON_TYPE_NAME,
                   hrds.HR_DEPARTMENT_SUB_SUB_NAME,
                   hrd.HR_DEPARTMENT_NAME
            FROM hrd_person p
            LEFT JOIN hrd_prefix hrp ON hrp.HR_PREFIX_ID = p.HR_PREFIX_ID
            LEFT JOIN hrd_person_type hrt ON hrt.HR_PERSON_TYPE_ID = p.HR_PERSON_TYPE_ID
            LEFT JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID = p.HR_DEPARTMENT_SUB_SUB_ID
            LEFT JOIN hrd_department hrd ON hrd.HR_DEPARTMENT_ID = p.HR_DEPARTMENT_ID
            WHERE p.ID = ?
        ", [$personId]);

        if (!$person) {
            return response()->json(['error' => 'ไม่พบข้อมูลบุคลากร'], 404);
        }

        // Fetch salary record
        $salary = DB::connection('backoffice')->table('salary_all')
            ->where('SALARYALL_PERSON_ID', (string)$personId)
            ->where('SALARYALL_YEAR_ID', (string)$yearId)
            ->where('SALARYALL_MONTH_ID', (string)$monthId)
            ->where('SALARYALL_TYPE', 'salary')
            ->first();

        $salReceives = [];
        $salPays = [];
        $salNet = 0;
        $salBookNum = null;
        if ($salary) {
            $salReceives = DB::connection('backoffice')->select("
                SELECT SALARYALL_RECEIVE_LISTNAME as item_name,
                       CAST(SALARYALL_RECEIVE_AMOUNT AS DECIMAL(12,2)) as amount
                FROM salary_all_receive
                WHERE SALARYALL_ID = ?
                ORDER BY amount DESC
            ", [$salary->SALARYALL_ID]);

            $salPays = DB::connection('backoffice')->select("
                SELECT SALARYALL_PAY_LISTNAME as item_name,
                       CAST(SALARYALL_PAY_AMOUNT AS DECIMAL(12,2)) as amount
                FROM salary_all_pay
                WHERE SALARYALL_ID = ?
                ORDER BY amount DESC
            ", [$salary->SALARYALL_ID]);

            $salNet = (float)$salary->SALARYALL_TOTAL;
            $salBookNum = $salary->SALARYALL_BOOK_NUM;
        }

        // Fetch compen record
        $compen = DB::connection('backoffice')->table('salary_all')
            ->where('SALARYALL_PERSON_ID', (string)$personId)
            ->where('SALARYALL_YEAR_ID', (string)$yearId)
            ->where('SALARYALL_MONTH_ID', (string)$monthId)
            ->where('SALARYALL_TYPE', 'compen')
            ->first();

        $compReceives = [];
        $compPays = [];
        $compNet = 0;
        $compBookNum = null;
        if ($compen) {
            $compReceives = DB::connection('backoffice')->select("
                SELECT SALARYALL_RECEIVE_LISTNAME as item_name,
                       CAST(SALARYALL_RECEIVE_AMOUNT AS DECIMAL(12,2)) as amount
                FROM salary_all_receive
                WHERE SALARYALL_ID = ?
                ORDER BY amount DESC
            ", [$compen->SALARYALL_ID]);

            $compPays = DB::connection('backoffice')->select("
                SELECT SALARYALL_PAY_LISTNAME as item_name,
                       CAST(SALARYALL_PAY_AMOUNT AS DECIMAL(12,2)) as amount
                FROM salary_all_pay
                WHERE SALARYALL_ID = ?
                ORDER BY amount DESC
            ", [$compen->SALARYALL_ID]);

            $compNet = (float)$compen->SALARYALL_TOTAL;
            $compBookNum = $compen->SALARYALL_BOOK_NUM;
        }

        $bookNum = $salBookNum ?: $compBookNum;
        $m = (int)$monthId;
        $monthName = $this->thaiMonths[$m] ?? "เดือน $m";
        $calYear = in_array($m, [10, 11, 12]) ? ((int)$yearId - 1) : (int)$yearId;

        $salTotalRec = array_sum(array_column($salReceives, 'amount'));
        $salTotalPay = array_sum(array_column($salPays, 'amount'));

        $compTotalRec = array_sum(array_column($compReceives, 'amount'));
        $compTotalPay = array_sum(array_column($compPays, 'amount'));

        $grandNet = $salNet + $compNet;
        $grandRec = $salTotalRec + $compTotalRec;
        $grandPay = $salTotalPay + $compTotalPay;
        $grandNetThai = $this->thaiBaht($grandNet);

        return view('backoffice.salary.slip_modal', compact(
            'person', 'yearId', 'monthId', 'monthName', 'calYear', 'bookNum',
            'salary', 'salReceives', 'salPays', 'salTotalRec', 'salTotalPay', 'salNet',
            'compen', 'compReceives', 'compPays', 'compTotalRec', 'compTotalPay', 'compNet',
            'grandNet', 'grandRec', 'grandPay', 'grandNetThai'
        ));
    }

    /**
     * Convert numeric amount to formal Thai Baht text (เช่น หนึ่งแสนหนึ่งหมื่นสี่พันสองร้อยห้าสิบบาทถ้วน)
     */
    private function thaiBaht($number): string
    {
        if (!is_numeric($number)) return 'ศูนย์บาทถ้วน';
        $number = number_format((float)$number, 2, '.', '');
        [$integer, $fraction] = explode('.', $number);

        $thaiNum = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
        $unit = ['', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน'];

        $convertGroup = function($numStr) use ($thaiNum, $unit) {
            $ret = '';
            $len = strlen($numStr);
            for ($i = 0; $i < $len; $i++) {
                $digit = (int)$numStr[$i];
                $pos = $len - $i - 1;
                if ($digit !== 0) {
                    if ($pos === 1 && $digit === 1) {
                        $ret .= 'สิบ';
                    } elseif ($pos === 1 && $digit === 2) {
                        $ret .= 'ยี่สิบ';
                    } elseif ($pos === 0 && $digit === 1 && $len > 1 && (int)$numStr[$len - 2] !== 0) {
                        $ret .= 'เอ็ด';
                    } else {
                        $ret .= $thaiNum[$digit] . $unit[$pos];
                    }
                }
            }
            return $ret;
        };

        $intPart = '';
        $len = strlen($integer);
        if ($len > 6) {
            $intPart = $convertGroup(substr($integer, 0, $len - 6)) . 'ล้าน';
            $intPart .= $convertGroup(substr($integer, $len - 6));
        } else {
            $intPart = $convertGroup($integer);
        }
        if ($intPart === '') $intPart = 'ศูนย์';

        $ret = $intPart . 'บาท';

        if ((int)$fraction === 0) {
            $ret .= 'ถ้วน';
        } else {
            $fracPart = '';
            $f0 = (int)$fraction[0];
            $f1 = (int)$fraction[1];
            if ($f0 === 1) $fracPart .= 'สิบ';
            elseif ($f0 === 2) $fracPart .= 'ยี่สิบ';
            elseif ($f0 > 2) $fracPart .= $thaiNum[$f0] . 'สิบ';

            if ($f1 === 1 && $f0 > 0) $fracPart .= 'เอ็ด';
            elseif ($f1 > 0) $fracPart .= $thaiNum[$f1];

            $ret .= $fracPart . 'สตางค์';
        }
        return $ret;
    }
}
