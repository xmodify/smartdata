<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeathController extends Controller
{
    public function index(Request $request)
    {
        $title = 'รายงานการเสียชีวิต';
        $dates = $this->resolveDateRange($request);
        $year_start = $dates['year_start'];
        $year_end = $dates['year_end'];
        $table_start_date = $dates['table_start_date'];
        $table_end_date = $dates['table_end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Raw Death Query for Patient Table filtered by independent table dates
        $death_list = $this->fetch_death_patients($table_start_date, $table_end_date);

        // Check if Ajax request
        if ($request->ajax()) {
            $html = view('hosxp.death.partials._table_death', compact('death_list'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'start_date_thai' => DateThai($table_start_date),
                'end_date_thai' => DateThai($table_end_date),
                'table_start_date' => $table_start_date,
                'table_end_date' => $table_end_date,
                'total' => count($death_list)
            ]);
        }

        // Monthly Trend Data (12-month budget year)
        $monthly_trend_raw = $this->fetch_death_trend_monthly($year_start, $year_end);
        $monthly_trend = collect($monthly_trend_raw)->map(function ($item) {
            $months_th = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
            $item->label = $months_th[(int) $item->month_num] . " " . ($item->year_be);
            return $item;
        });

        // Yearly Trend Data
        $yearly_trend = $this->fetch_death_trend_yearly($budget_year);

        // Backwards compatibility variables
        $start_date = $year_start;
        $end_date = $year_end;

        return view('hosxp.death.index', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'year_start',
            'year_end',
            'table_start_date',
            'table_end_date',
            'death_list',
            'monthly_trend',
            'yearly_trend'
        ));
    }

    public function death_top20(Request $request)
    {
        $title = '20 อันดับโรค (Primary Diagnosis) สถิติการเสียชีวิต';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $diag_icd10 = DB::connection('hosxp')->select('select 
            IF(CONCAT("[",d.death_diag_1,"] ",i1.NAME) ="" OR CONCAT("[",d.death_diag_1,"] ",i1.NAME) IS Null,
            "ไม่บันทึกรหัสโรค",CONCAT("[",d.death_diag_1,"] ",i1.NAME)) AS name,
            sum(case when pt.sex=1 THEN 1 ELSE 0 END) as male,   
            sum(case when pt.sex=2 THEN 1 ELSE 0 END) as female,   
            COUNT(DISTINCT d.hn) AS "sum"
            FROM death d
            LEFT OUTER JOIN patient pt ON pt.hn = d.hn
            LEFT OUTER JOIN rpt_504_name c1 ON c1.id = d.death_cause
            LEFT OUTER JOIN icd101 i1 ON i1.CODE = d.death_diag_1 
            WHERE d.death_date BETWEEN ? AND ?
            GROUP BY d.death_diag_1 
            ORDER BY COUNT(DISTINCT d.hn) DESC
            LIMIT 20', [$start_date, $end_date]);

        $diag_504 = DB::connection('hosxp')->select('select 
            IF(c1.name1="" OR c1.name1 IS NULL,"ไม่มีรหัสโรค",c1.name1) AS name,
            sum(case when pt.sex=1 THEN 1 ELSE 0 END) as male,   
            sum(case when pt.sex=2 THEN 1 ELSE 0 END) as female,   
            COUNT(DISTINCT d.hn) AS "sum"
            FROM death d
            LEFT OUTER JOIN patient pt ON pt.hn = d.hn
            LEFT OUTER JOIN rpt_504_name c1 ON c1.id = d.death_cause
            LEFT OUTER JOIN icd101 i1 ON i1.CODE = d.death_diag_1 
            WHERE d.death_date BETWEEN ? AND ?
            GROUP BY d.death_cause
            ORDER BY COUNT(DISTINCT d.hn) DESC
            LIMIT 20', [$start_date, $end_date]);

        return view('hosxp.death.death_top20', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'diag_icd10',
            'diag_504'
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

        // Support explicit start_date & end_date if requested
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        } else {
            $start_date = $year_start;
            $end_date = $year_end;
        }

        // Independent table date range (defaults to current month, clamped to selected budget year)
        if ($request->filled('table_start_date') && $request->filled('table_end_date')) {
            $table_start_date = $request->table_start_date;
            $table_end_date = $request->table_end_date;
        } else {
            $currentDate = date('Y-m-d');
            if ($currentDate >= $year_start && $currentDate <= $year_end) {
                $table_start_date = date('Y-m-01');
                $table_end_date = date('Y-m-t');
            } else {
                $table_start_date = date('Y-m-01', strtotime($year_end));
                $table_end_date = $year_end;
            }
        }

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'year_start' => $year_start,
            'year_end' => $year_end,
            'table_start_date' => $table_start_date,
            'table_end_date' => $table_end_date,
            'budget_year' => $budget_year,
            'budget_year_select' => $budget_year_select
        ];
    }

    private function fetch_death_patients($start_date, $end_date)
    {
        return DB::connection('hosxp')->select('select 
            pt.hn,d.an,CONCAT(pt.pname,pt.fname,SPACE(1),pt.lname ) AS ptname,
            pt.birthday,d.death_date,d.death_time,c1.name1 AS name504,CONCAT("[",i1.`code`,"] ",i1.`name`) AS icdname
            FROM death d
            LEFT OUTER JOIN patient pt ON pt.hn = d.hn
            LEFT OUTER JOIN rpt_504_name c1 ON c1.id = d.death_cause
            LEFT OUTER JOIN icd101 i1 ON i1.CODE = d.death_diag_1 
            WHERE d.death_date BETWEEN ? AND ?
            AND d.death_place = "1"
            ORDER BY d.death_date', [$start_date, $end_date]);
    }

    private function fetch_death_trend_monthly($start_date, $end_date)
    {
        return DB::connection('hosxp')->select("
            select 
                DATE_FORMAT(d.death_date, '%m') as month_num,
                (YEAR(d.death_date) + 543) as year_be,
                count(d.hn) as total_count
            from death d
            where d.death_place = '1'
            and d.death_date between ? and ?
            group by year_be, month_num
            order by year_be, month_num
        ", [$start_date, $end_date]);
    }

    private function fetch_death_trend_yearly($budget_year)
    {
        $years = [];
        for ($i = 4; $i >= 0; $i--) {
            $years[] = $budget_year - $i;
        }
        $placeholders = implode(',', array_fill(0, count($years), '?'));

        return DB::connection('hosxp')->select("
            select 
                (YEAR(d.death_date) + 543) as year_be,
                count(d.hn) as total_count
            from death d
            where d.death_place = '1'
            and (YEAR(d.death_date) + 543) in ($placeholders)
            group by year_be
            order by year_be
        ", $years);
    }
}
