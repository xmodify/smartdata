<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    public function refer_out(Request $request)
    {
        $title = 'รายงานผู้ป่วยส่งต่อ Refer Out';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $refer_list_opd = $this->fetch_refer_out_opd($start_date, $end_date);
        $refer_list_ipd = $this->fetch_refer_out_ipd($start_date, $end_date);

        // Fetch Trend Data for Charts
        $monthly_trend_raw = $this->fetch_refer_trend_monthly($start_date, $end_date);
        $monthly_trend = collect($monthly_trend_raw)->map(function ($item) {
            $months_th = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
            $item->label = $months_th[(int) $item->month_num] . " " . ($item->year_be);
            return $item;
        });

        $yearly_trend = $this->fetch_refer_trend_yearly($budget_year);

        return view('hosxp.stats.refer_out', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'refer_list_opd',
            'refer_list_ipd',
            'monthly_trend',
            'yearly_trend',
            'start_date',
            'end_date'
        ));
    }

    public function refer_out_4h(Request $request)
    {
        return $this->refer_out_after_admit($request, 4, 'รายงานผู้ป่วยส่งต่อ Refer Out ภายใน 4 ชม.หลัง Admit');
    }

    public function refer_out_24h(Request $request)
    {
        return $this->refer_out_after_admit($request, 24, 'รายงานผู้ป่วยส่งต่อ Refer Out ภายใน 24 ชม.หลัง Admit');
    }

    private function refer_out_after_admit(Request $request, $hours, $title)
    {
        $view_map = [4 => 'refer_out_4h', 24 => 'refer_out_24h'];
        $view = $view_map[$hours];

        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $report_data = $this->fetch_refer_out_after_admit($start_date, $end_date, $hours);

        // Fetch Trend Data for Charts
        $monthly_trend_raw = $this->fetch_refer_after_admit_trend_monthly($start_date, $end_date, $hours);
        $monthly_trend = collect($monthly_trend_raw)->map(function ($item) {
            $months_th = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
            $item->label = $months_th[(int) $item->month_num] . " " . ($item->year_be);
            return $item;
        });

        $yearly_trend = $this->fetch_refer_after_admit_trend_yearly($budget_year, $hours);

        return view('hosxp.stats.' . $view, compact(
            'title',
            'budget_year_select',
            'budget_year',
            'hours',
            'report_data',
            'monthly_trend',
            'yearly_trend',
            'start_date',
            'end_date'
        ));
    }

    public function refer_out_top20(Request $request)
    {
        $title = 'รายงานผู้ป่วยส่งต่อ Refer Out 30 อันดับโรค (Primary Diagnosis)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $diag_top_opd = \DB::connection('hosxp')->select('
            select 
                CONCAT("[",pdx,"] " ,name) AS name, count(*) AS sum
            FROM (
                SELECT r.hn, r.department, r.refer_point, r.pdx, i.name
                FROM referout r 
                LEFT JOIN icd101 i ON i.code=r.pdx										
                WHERE r.refer_date BETWEEN ? AND ?
                AND (r.pdx <>"" AND r.pdx IS NOT NULL)
            ) AS a
            WHERE a.department = "OPD" AND a.refer_point = "OPD"
            GROUP BY pdx  
            ORDER BY sum desc limit 30', [$start_date, $end_date]);

        $diag_top_er = \DB::connection('hosxp')->select('
            select 
                CONCAT("[",pdx,"] " ,name) AS name, count(*) AS sum
            FROM (
                SELECT r.hn, r.department, r.refer_point, r.pdx, i.name
                FROM referout r 
                LEFT JOIN icd101 i ON i.code=r.pdx										
                WHERE r.refer_date BETWEEN ? AND ?
                AND (r.pdx <>"" AND r.pdx IS NOT NULL)
            ) AS a
            WHERE a.department = "OPD" AND a.refer_point = "ER"
            GROUP BY pdx  
            ORDER BY sum desc limit 30', [$start_date, $end_date]);

        $diag_top_ipd = \DB::connection('hosxp')->select('
            select 
                CONCAT("[",pdx,"] " ,name) AS name, count(*) AS sum
            FROM (
                SELECT r.hn, r.department, r.refer_point, r.pdx, i.name
                FROM referout r 
                LEFT JOIN icd101 i ON i.code=r.pdx										
                WHERE r.refer_date BETWEEN ? AND ?
                AND (r.pdx <>"" AND r.pdx IS NOT NULL)
            ) AS a
            WHERE a.department = "IPD"
            GROUP BY pdx  
            ORDER BY sum desc limit 30', [$start_date, $end_date]);

        return view('hosxp.stats.refer_out_top20', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'diag_top_opd',
            'diag_top_er',
            'diag_top_ipd'
        ));
    }

    public function death(Request $request)
    {
        $title = 'รายงานการเสียชีวิต';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Raw Death Query
        $death_list = \DB::connection('hosxp')->select('select 
            pt.hn,d.an,CONCAT(pt.pname,pt.fname,SPACE(1),pt.lname ) AS ptname,
            pt.birthday,d.death_date,d.death_time,c1.name1 AS name504,CONCAT("[",i1.`code`,"] ",i1.`name`) AS icdname
            FROM death d
            LEFT OUTER JOIN patient pt ON pt.hn = d.hn
            LEFT OUTER JOIN rpt_504_name c1 ON c1.id = d.death_cause
            LEFT OUTER JOIN icd101 i1 ON i1.CODE = d.death_diag_1 
            WHERE d.death_date BETWEEN ? AND ?
            AND d.death_place = "1"
            ORDER BY d.death_date', [$start_date, $end_date]);

        // Monthly Trend Data
        $monthly_trend_raw = $this->fetch_death_trend_monthly($start_date, $end_date);
        $monthly_trend = collect($monthly_trend_raw)->map(function ($item) {
            $months_th = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
            $item->label = $months_th[(int) $item->month_num] . " " . ($item->year_be);
            return $item;
        });

        // Yearly Trend Data
        $yearly_trend = $this->fetch_death_trend_yearly($budget_year);

        return view('hosxp.stats.death', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
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

        $diag_icd10 = \DB::connection('hosxp')->select('select 
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

        $diag_504 = \DB::connection('hosxp')->select('select 
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

        return view('hosxp.stats.death_top20', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'diag_icd10',
            'diag_504'
        ));
    }

    /**
     * Helper to render report with budget year selection.
     */
    private function render_report(Request $request, $type, $title)
    {
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        return view('hosxp.stats.' . $type, compact('type', 'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date'));
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

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'budget_year' => $budget_year,
            'budget_year_select' => $budget_year_select
        ];
    }
    private function fetch_top20_opd($start_date, $end_date)
    {
        return \DB::connection('hosxp')->select("
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
            /* and v.pdx not in (SELECT icd10 FROM hrims.lookup_icd10 WHERE pp = 'Y') */
            group by v.pdx,i.name  
            order by sum desc limit 20
        ", [$start_date, $end_date]);
    }

    private function fetch_group_504($start_date, $end_date)
    {
        return \DB::connection('hosxp')->select("
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
        return \DB::connection('hosxp')->select("
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
        return \DB::connection('hosxp')->select("
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
        return \DB::connection('hosxp')->select("
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

    private function fetch_refer_out_opd($start_date, $end_date)
    {
        return \DB::connection('hosxp')->select('
            select
                o.hn, CONCAT(p.pname,p.fname,SPACE(1),p.lname) AS ptname, pmh.cc_persist_disease AS pmh,
                GROUP_CONCAT(DISTINCT c1.`name`) AS "clinic", r.refer_point, o.vstdate, o.vsttime, v.pdx, r.refer_date,
                r.refer_time, r.pre_diagnosis, r.pdx AS pdx_refer, h.`name` AS refer_hos, r.with_ambulance
            FROM referout r 
            LEFT JOIN ovst o ON o.vn=r.vn
            LEFT JOIN vn_stat v ON v.vn=r.vn 
            LEFT JOIN clinicmember c ON c.hn=r.hn
            LEFT JOIN clinic c1 ON c1.clinic=c.clinic
            LEFT JOIN opd_ill_history pmh ON pmh.hn=r.hn
            LEFT JOIN patient p ON p.hn=r.hn 
            LEFT JOIN hospcode h ON h.hospcode=r.refer_hospcode
            WHERE r.refer_date BETWEEN ? AND ?
            AND r.department = "OPD"
            GROUP BY r.vn
            ORDER BY r.refer_point, r.refer_date
        ', [$start_date, $end_date]);
    }

    private function fetch_refer_out_ipd($start_date, $end_date)
    {
        return \DB::connection('hosxp')->select('
            select
                i.hn, CONCAT(p.pname,p.fname,SPACE(1),p.lname) AS ptname, pmh.cc_persist_disease AS pmh,
                GROUP_CONCAT(DISTINCT c1.`name`) AS "clinic", r.refer_point, i.regdate, i.regtime, a.pdx, r.refer_date,
                r.refer_time, r.pre_diagnosis, r.pdx AS pdx_refer, h.`name` AS refer_hos, r.with_ambulance
            FROM referout r 
            LEFT JOIN ipt i ON i.an=r.vn
            LEFT JOIN an_stat a ON a.an=r.vn
            LEFT JOIN clinicmember c ON c.hn=r.hn
            LEFT JOIN clinic c1 ON c1.clinic=c.clinic
            LEFT JOIN opd_ill_history pmh ON pmh.hn=r.hn 
            LEFT JOIN patient p ON p.hn=r.hn
            LEFT JOIN hospcode h ON h.hospcode=r.refer_hospcode
            WHERE r.refer_date BETWEEN ? AND ?
            AND r.department = "IPD"
            GROUP BY r.vn
            ORDER BY r.refer_point, r.refer_date
        ', [$start_date, $end_date]);
    }

    private function fetch_refer_trend_monthly($start_date, $end_date)
    {
        return \DB::connection('hosxp')->select("
            select 
                DATE_FORMAT(refer_date, '%m') as month_num,
                (YEAR(refer_date) + 543) as year_be,
                sum(case when department = 'OPD' then 1 else 0 end) as opd_count,
                sum(case when department = 'IPD' then 1 else 0 end) as ipd_count
            from referout
            where refer_date between ? and ?
            group by year_be, month_num
            order by year_be, month_num
        ", [$start_date, $end_date]);
    }

    private function fetch_refer_trend_yearly($budget_year)
    {
        $years = [];
        for ($i = 4; $i >= 0; $i--) {
            $years[] = $budget_year - $i;
        }

        $placeholders = implode(',', array_fill(0, count($years), '?'));

        return \DB::connection('hosxp')->select("
            select 
                (YEAR(refer_date) + 543) as year_be,
                sum(case when department = 'OPD' then 1 else 0 end) as opd_count,
                sum(case when department = 'IPD' then 1 else 0 end) as ipd_count
            from referout
            where (YEAR(refer_date) + 543) in ($placeholders)
            group by year_be
            order by year_be
        ", $years);
    }

    private function fetch_refer_out_after_admit($start_date, $end_date, $hours)
    {
        return \DB::connection('hosxp')->select('
            select 
                i.an, i.hn, CONCAT(p.pname,p.fname,SPACE(1),p.lname) AS ptname, i.regdate, i.regtime, 
                i.dchdate, i.dchtime, r.refer_date, r.refer_time,
                a.pdx AS admit_pdx, r.pdx AS refer_pdx, h.`name` AS refer_hos, a.admit_hour    
            FROM ipt i    
            LEFT JOIN patient p ON p.hn=i.hn   
            LEFT JOIN an_stat a ON i.an=a.an
            LEFT JOIN referout r ON i.an=r.vn 
            LEFT JOIN hospcode h ON h.hospcode=r.refer_hospcode
            WHERE i.dchtype=04 AND a.admit_hour <= ?
            AND i.dchdate BETWEEN ? AND ?   
            GROUP BY i.an 
        ', [$hours, $start_date, $end_date]);
    }

    private function fetch_refer_after_admit_trend_monthly($start_date, $end_date, $hours)
    {
        return \DB::connection('hosxp')->select("
            select 
                DATE_FORMAT(i.dchdate, '%m') as month_num,
                (YEAR(i.dchdate) + 543) as year_be,
                count(i.an) as total_count
            from ipt i
            join an_stat a on i.an = a.an
            where i.dchtype=04 and a.admit_hour <= ?
            and i.dchdate between ? and ?
            group by year_be, month_num
            order by year_be, month_num
        ", [$hours, $start_date, $end_date]);
    }

    private function fetch_refer_after_admit_trend_yearly($budget_year, $hours)
    {
        $years = [];
        for ($i = 4; $i >= 0; $i--) {
            $years[] = $budget_year - $i;
        }
        $placeholders = implode(',', array_fill(0, count($years), '?'));

        return \DB::connection('hosxp')->select("
            select 
                (YEAR(i.dchdate) + 543) as year_be,
                count(i.an) as total_count
            from ipt i
            join an_stat a on i.an = a.an
            where i.dchtype=04 and a.admit_hour <= ?
            and (YEAR(i.dchdate) + 543) in ($placeholders)
            group by year_be
            order by year_be
        ", array_merge([$hours], $years));
    }
    private function fetch_death_trend_monthly($start_date, $end_date)
    {
        return \DB::connection('hosxp')->select("
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

        return \DB::connection('hosxp')->select("
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
