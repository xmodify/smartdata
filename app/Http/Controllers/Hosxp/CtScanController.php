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
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Execute the user's SQL query on 'hosxp' connection
        $patients = DB::connection('hosxp')->select("
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

        // Aggregate by main health rights: UCS, OFC, LGO, SSS, A9, Others
        $summary = [
            'UCS' => 0,
            'OFC' => 0,
            'LGO' => 0,
            'SSS' => 0,
            'A9' => 0,
            'Others' => 0,
            'Total' => count($patients)
        ];

        foreach ($patients as $row) {
            $code = strtoupper(trim($row->hipdata_code ?? ''));
            if (in_array($code, ['UCS', 'OFC', 'LGO', 'SSS', 'A9'])) {
                $summary[$code]++;
            } else {
                $summary['Others']++;
            }
        }

        // Aggregate counts by month
        $monthly_data = [];
        $thai_months = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
            7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        foreach ($patients as $row) {
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

        return view('hosxp.ct_scan.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'patients', 'summary', 'monthly_stats'
        ));
    }

    public function print(Request $request)
    {
        $title = 'ข้อมูลงานบริการ CT Scan';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];

        $patients = DB::connection('hosxp')->select("
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

        return view('hosxp.ct_scan.print', compact(
            'title', 'budget_year', 'start_date', 'end_date', 'patients'
        ));
    }

    private function resolveDateRange(Request $request)
    {
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

        if ($request->start_date && $request->end_date) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        } else {
            $year_data = DB::table('budget_year')
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

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'budget_year' => $budget_year,
            'budget_year_select' => $budget_year_select
        ];
    }
}
