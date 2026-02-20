<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReferController extends Controller
{
    public function index(Request $request)
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

        return view('hosxp.refer.index', compact(
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

        return view('hosxp.refer.' . $view, compact(
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

        $diag_top_opd = DB::connection('hosxp')->select('
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

        $diag_top_er = DB::connection('hosxp')->select('
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

        $diag_top_ipd = DB::connection('hosxp')->select('
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

        return view('hosxp.refer.refer_out_top20', compact(
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

    private function fetch_refer_out_opd($start_date, $end_date)
    {
        return DB::connection('hosxp')->select('
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
        return DB::connection('hosxp')->select('
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
        return DB::connection('hosxp')->select("
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

        return DB::connection('hosxp')->select("
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
        return DB::connection('hosxp')->select('
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
        return DB::connection('hosxp')->select("
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

        return DB::connection('hosxp')->select("
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
}
