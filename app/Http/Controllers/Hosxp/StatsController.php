<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Display the HOSxP Stats Index.
     */
    public function index()
    {
        return view('hosxp.stats.index');
    }

    public function top20_opd(Request $request)
    {
        $title = '20 อันดับโรค ผู้ป่วยนอก';

        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $diag_icd10 = $this->fetch_top20_opd($start_date, $end_date);
        $diag_504 = $this->fetch_group_504($start_date, $end_date);

        return view('hosxp.stats.top20_opd', compact('title', 'budget_year_select', 'budget_year', 'diag_icd10', 'diag_504', 'start_date', 'end_date'));
    }

    public function top20_ipd(Request $request)
    {
        $title = '20 อันดับโรค ผู้ป่วยใน';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $diag_icd10 = $this->fetch_top20_ipd($start_date, $end_date);
        $diag_505 = $this->fetch_group_505($start_date, $end_date);

        return view('hosxp.stats.top20_ipd', compact('title', 'budget_year_select', 'budget_year', 'diag_icd10', 'diag_505', 'start_date', 'end_date'));
    }

    public function group_506(Request $request)
    {
        $title = 'กลุ่มโรคที่ต้องเฝ้าระวัง (รง.506)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $report_data = $this->fetch_group_506($start_date, $end_date);

        return view('hosxp.stats.group_506', compact('title', 'budget_year_select', 'budget_year', 'report_data', 'start_date', 'end_date'));
    }

    /**
     * Standard Date Range Resolver for all reports
     */
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

            $matched_year = DB::table('budget_year')
                ->where('DATE_BEGIN', '<=', $start_date)
                ->where('DATE_END', '>=', $start_date)
                ->first();

            if ($matched_year) {
                $budget_year = $matched_year->LEAVE_YEAR_ID;
            }
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

    private function fetch_top20_opd($start_date, $end_date)
    {
        return DB::connection('hosxp')->select("
            select 
                i.`code`,
                i.`name`,
                i.tname,
                count(v.pdx) as sum , 
                sum(case when v.sex=1 THEN 1 ELSE 0 END) as male,   
                sum(case when v.sex=2 THEN 1 ELSE 0 END) as female,
                sum(v.inc03) as inc_lab,
                sum(v.inc12) as inc_drug   
            FROM vn_stat v   
            left outer join icd101 i on i.code=v.pdx 
            where v.vstdate BETWEEN ? AND ?
            and v.pdx<>'' AND v.pdx is not null 
            group by v.pdx,i.name  
            order by sum desc limit 20
        ", [$start_date, $end_date]);
    }

    private function fetch_group_504($start_date, $end_date)
    {
        return DB::connection('hosxp')->select("
            select 
                concat(a.name1,' [',a.id,']') as name,
                ifnull(d.male,0) as male,
                ifnull(d.female,0) as female,
                ifnull(d.amount,0) as sum,
                ifnull(d.inc_lab,0) as inc_lab,
                ifnull(d.inc_drug,0) as inc_drug
            from rpt_504_name a 
            left join (
                select b.id,
                    sum(case when v.sex=1 THEN 1 ELSE 0 END) as male,   
                    sum(case when v.sex=2 THEN 1 ELSE 0 END) as female,
                    count(b.id) as amount,
                    sum(v.inc03) as inc_lab,
                    sum(v.inc12) as inc_drug
                from rpt_504_code b, vn_stat v 
                where v.pdx between b.code1 and b.code2  
                and v.vstdate between ? AND ? 
                group by b.id
            ) d on d.id = a.id 
            order by sum desc
        ", [$start_date, $end_date]);
    }

    private function fetch_top20_ipd($start_date, $end_date)
    {
        return DB::connection('hosxp')->select("
            select 
                concat('[',v.pdx,'] ' ,i.name) as name,
                count(v.pdx) as sum,
                sum(case when v.sex=1 THEN 1 ELSE 0 END) as male,   
                sum(case when v.sex=2 THEN 1 ELSE 0 END) as female,
                sum(v.inc03) as inc_lab,
                sum(v.inc12) as inc_drug   
            FROM an_stat v   
            left outer join icd101 i on i.code=v.pdx 
            where v.dchdate BETWEEN ? AND ?
            and v.pdx<>'' and v.pdx is not null and v.pdx not like 'z%'
            AND v.pdx NOT IN ('Z290','Z208')
            group by v.pdx,i.name  
            order by sum desc limit 30
        ", [$start_date, $end_date]);
    }

    private function fetch_group_505($start_date, $end_date)
    {
        return DB::connection('hosxp')->select("
            select 
                concat(a.name2,' [',a.id,']') as name,
                ifnull(d.male,0) as male,
                ifnull(d.female,0) as female,
                ifnull(d.amount,0) as sum,
                ifnull(d.inc_lab,0) as inc_lab,
                ifnull(d.inc_drug,0) as inc_drug
            from rpt_505_name a 
            left join (
                select b.id,
                    sum(case when v.sex=1 THEN 1 ELSE 0 END) as male,   
                    sum(case when v.sex=2 THEN 1 ELSE 0 END) as female,
                    count(b.id) as amount,
                    sum(v.inc03) as inc_lab,
                    sum(v.inc12) as inc_drug
                from rpt_505_code b, an_stat v 
                where v.pdx between b.code1 and b.code2  
                and v.dchdate between ? AND ? 
                AND v.pdx NOT IN ('Z290','Z208')
                group by b.id
            ) d on d.id = a.id 
            order by sum desc
        ", [$start_date, $end_date]);
    }

    private function fetch_group_506($start_date, $end_date)
    {
        return DB::connection('hosxp')->select("
            select 
                n.name as name, 
                sum(case when p.sex=1 THEN 1 ELSE 0 END) as male,   
                sum(case when p.sex=2 THEN 1 ELSE 0 END) as female,  
                COUNT(DISTINCT s.vn) as sum
            from surveil_member s   
            LEFT JOIN patient p on p.hn=s.hn  
            LEFT JOIN name506 n on n.code=s.code506 
            where s.report_date between ? AND ? 
            GROUP BY s.code506 
            ORDER BY sum DESC
        ", [$start_date, $end_date]);
    }
}
