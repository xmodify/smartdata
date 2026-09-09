<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CtScanController extends Controller
{
    public function index(Request $request)
    {
        $title = 'ข้อมูลงานบริการ CT Scan';

        // 1. Budget Year Selection & Date Range
        $budget_year_select = DB::table('budget_year')
            ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME', 'DATE_BEGIN', 'DATE_END')
            ->orderByDesc('LEAVE_YEAR_ID')
            ->limit(7)
            ->get();

        $budget_year_now = DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $budget_year = $request->budget_year ?: $budget_year_now;

        $year_data = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->first();

        if ($year_data) {
            $year_start = $year_data->DATE_BEGIN;
            $year_end = $year_data->DATE_END;
        } else {
            $year_start = ($budget_year - 544) . '-10-01';
            $year_end = ($budget_year - 543) . '-09-30';
        }

        // 2. Table Date Range (Default: current month if in active budget year, else last month of that budget year)
        if ($request->table_start_date && $request->table_end_date) {
            $table_start_date = $request->table_start_date;
            $table_end_date = $request->table_end_date;
        } elseif ($request->start_date && $request->end_date) {
            $table_start_date = $request->start_date;
            $table_end_date = $request->end_date;
        } else {
            $today = date('Y-m-d');
            if ($today >= $year_start && $today <= $year_end) {
                $table_start_date = date('Y-m-01');
                $table_end_date = date('Y-m-t');
            } else {
                $table_start_date = date('Y-m-01', strtotime($year_end));
                $table_end_date = $year_end;
            }
        }

        // 3. Handle AJAX Request for Table Data
        if ($request->ajax()) {
            $patients = $this->queryPatients($table_start_date, $table_end_date);
            $total_qty = 0;
            $total_bill = 0;
            $total_claim = 0;
            $total_ct = 0;

            foreach ($patients as $row) {
                $total_qty += $row->qty;
                $total_bill += $row->price_bill;
                $total_claim += $row->price_claim;
                $total_ct += $row->price_ct;
            }

            return response()->json([
                'success' => true,
                'patients' => $patients,
                'table_start_date' => $table_start_date,
                'table_end_date' => $table_end_date,
                'start_date_thai' => DateThai($table_start_date),
                'end_date_thai' => DateThai($table_end_date),
                'totals' => [
                    'qty' => number_format($total_qty),
                    'bill' => number_format($total_bill, 2),
                    'claim' => number_format($total_claim, 2),
                    'ct' => number_format($total_ct, 2),
                ],
                'print_url' => route('hosxp.ct_scan.print', [
                    'start_date' => $table_start_date,
                    'end_date' => $table_end_date,
                    'budget_year' => $budget_year
                ]),
            ]);
        }

        // 4. Executive Dashboard Stats (Always full 12 months of the budget year)
        $yearly_patients = $this->queryPatients($year_start, $year_end);

        $summary = [
            'UCS' => 0,
            'OFC' => 0,
            'LGO' => 0,
            'SSS' => 0,
            'A9' => 0,
            'Others' => 0,
            'Total' => count($yearly_patients)
        ];

        foreach ($yearly_patients as $row) {
            $code = strtoupper(trim($row->hipdata_code ?? ''));
            if (in_array($code, ['UCS', 'OFC', 'LGO', 'SSS', 'A9'])) {
                $summary[$code]++;
            } else {
                $summary['Others']++;
            }
        }

        $thai_months = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
            7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        $monthly_data = [];
        foreach ($yearly_patients as $row) {
            if ($row->rxdate) {
                $time = strtotime($row->rxdate);
                $ym = date('Y-m', $time);
                if (!isset($monthly_data[$ym])) {
                    $monthly_data[$ym] = [
                        'count' => 0,
                        'price_ct' => 0,
                        'price_claim' => 0,
                        'UCS' => 0,
                        'OFC' => 0,
                        'LGO' => 0,
                        'SSS' => 0,
                        'A9' => 0,
                        'Others' => 0
                    ];
                }
                $monthly_data[$ym]['count']++;
                $monthly_data[$ym]['price_ct'] += floatval($row->price_ct);
                $monthly_data[$ym]['price_claim'] += floatval($row->price_claim);

                $code = strtoupper(trim($row->hipdata_code ?? ''));
                if (in_array($code, ['UCS', 'OFC', 'LGO', 'SSS', 'A9'])) {
                    $monthly_data[$ym][$code] += floatval($row->price_claim);
                } else {
                    $monthly_data[$ym]['Others'] += floatval($row->price_claim);
                }
            }
        }
        ksort($monthly_data);

        $monthly_stats = [];
        foreach ($monthly_data as $ym => $data) {
            $parts = explode('-', $ym);
            $year = intval($parts[0]) + 543;
            $month = intval($parts[1]);
            $month_name = $thai_months[$month] . ' ' . substr($year, -2);
            $monthly_stats[] = [
                'month' => $month_name,
                'count' => $data['count'],
                'price_ct' => $data['price_ct'],
                'price_claim' => $data['price_claim'],
                'UCS' => $data['UCS'],
                'OFC' => $data['OFC'],
                'LGO' => $data['LGO'],
                'SSS' => $data['SSS'],
                'A9' => $data['A9'],
                'Others' => $data['Others']
            ];
        }

        // 5. Patient Table Data (Filtered by table_start_date and table_end_date)
        if ($table_start_date === $year_start && $table_end_date === $year_end) {
            $patients = $yearly_patients;
        } else {
            $patients = $this->queryPatients($table_start_date, $table_end_date);
        }

        return view('hosxp.ct_scan.index', compact(
            'title', 'budget_year_select', 'budget_year', 'year_start', 'year_end',
            'table_start_date', 'table_end_date', 'patients', 'summary', 'monthly_stats'
        ));
    }

    public function print(Request $request)
    {
        $title = 'ข้อมูลงานบริการ CT Scan';

        $budget_year = $request->budget_year ?: DB::table('budget_year')
            ->whereDate('DATE_END', '>=', date('Y-m-d'))
            ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
            ->value('LEAVE_YEAR_ID');

        $start_date = $request->table_start_date ?: ($request->start_date ?: date('Y-m-01'));
        $end_date = $request->table_end_date ?: ($request->end_date ?: date('Y-m-t'));

        $patients = $this->queryPatients($start_date, $end_date);

        return view('hosxp.ct_scan.print', compact(
            'title', 'budget_year', 'start_date', 'end_date', 'patients'
        ));
    }

    private function queryPatients($start_date, $end_date)
    {
        return DB::connection('hosxp')->select("
            SELECT IF((o.an IS NULL OR o.an =''),'OPD','IPD') AS depart,CONCAT(pt.pname,pt.fname,SPACE(1),pt.lname) AS ptname,
            pt.cid,o.hn,o.an,p.hipdata_code,p.`name` AS pttype,IFNULL(vp.hospmain,ip.hospmain) AS hospmain,o.rxdate,o.rxtime,
            TIME(o.last_modified) AS updatetime,GROUP_CONCAT(DISTINCT s.`name`) AS item_name,SUM(o.qty) AS qty,SUM(o.qty)*nd.price AS price_bill,
            SUM(o.sum_price) AS price_claim,SUM(o.qty)*nd.unitcost AS price_ct
            FROM opitemrece o
            LEFT JOIN patient pt ON pt.hn=o.hn
            LEFT JOIN visit_pttype vp ON vp.vn=o.vn AND vp.pttype=o.pttype
            LEFT JOIN ipt_pttype ip ON ip.an=o.an AND ip.pttype=o.pttype
            LEFT JOIN pttype p ON p.pttype=o.pttype		
            LEFT JOIN s_drugitems s ON s.icode = o.icode	
            LEFT JOIN nondrugitems nd ON nd.icode = o.icode
            WHERE o.rxdate BETWEEN ? AND ?
            AND o.icode IN (SELECT icode FROM xray_items WHERE xray_items_group = 3)
            GROUP BY o.hn,o.vn,o.an,o.icode
            ORDER BY o.pttype,o.hn,o.rxdate,o.rxtime
        ", [$start_date, $end_date]);
    }
}
