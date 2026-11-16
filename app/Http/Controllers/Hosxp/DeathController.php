<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use App\Services\AopodService;
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
        $year_be = $dates['year_be'];
        $year_select = $dates['year_select'];

        // รายชื่อผู้เสียชีวิตตามช่วงวันที่ของตาราง
        [$death_list, $death_in, $death_out, $death_stats] = $this->classifyDeaths($table_start_date, $table_end_date);

        // Check if Ajax request
        if ($request->ajax()) {
            $render = fn($list, $tab) => view('hosxp.death.partials._table_death', ['death_list' => $list, 'tab' => $tab])->render();
            return response()->json([
                'success' => true,
                'html_all' => $render($death_list, 'all'),
                'html_in' => $render($death_in, 'in'),
                'html_out' => $render($death_out, 'out'),
                'count_all' => count($death_list),
                'count_in' => count($death_in),
                'count_out' => count($death_out),
                'stats' => $death_stats,
                'start_date_thai' => DateThai($table_start_date),
                'end_date_thai' => DateThai($table_end_date),
                'table_start_date' => $table_start_date,
                'table_end_date' => $table_end_date,
            ]);
        }

        // แนวโน้มรายเดือน (ม.ค. - ธ.ค. ของปีที่เลือก) แยกในสถานพยาบาล / นอกสถานพยาบาล
        $months_th = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
        $hosp_monthly = [];
        $hosp_out_monthly = [];
        foreach ($this->fetch_death_trend_monthly($year_start, $year_end) as $r) {
            $hosp_monthly[(int) $r->month_num] = (int) $r->in_count;
            $hosp_out_monthly[(int) $r->month_num] = (int) $r->out_count;
        }

        // ผู้เสียชีวิตนอกสถานพยาบาล 5 ปีย้อนหลังถึงสิ้นปีที่เลือก (ใช้ทั้งกราฟรายเดือนและรายปี)
        $five_start = ($year_be - 543 - 4) . '-01-01';
        $out_five = AopodService::outOfHospital($five_start, $year_end);
        $out_monthly = array_fill(1, 12, 0);
        $out_yearly = [];
        foreach ($out_five as $r) {
            if (!$r->death_date) {
                continue;
            }
            $y = (int) substr($r->death_date, 0, 4);
            $out_yearly[$y + 543] = ($out_yearly[$y + 543] ?? 0) + 1;
            if ($r->death_date >= $year_start && $r->death_date <= $year_end) {
                $out_monthly[(int) substr($r->death_date, 5, 2)]++;
            }
        }

        $monthly_trend = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthly_trend[] = [
                'label' => $months_th[$m] . ' ' . $year_be,
                'in_count' => $hosp_monthly[$m] ?? 0,
                'out_count' => $out_monthly[$m] + ($hosp_out_monthly[$m] ?? 0),
            ];
        }

        // แนวโน้ม 5 ปีย้อนหลัง (ปี พ.ศ.)
        $hosp_yearly = [];
        $hosp_out_yearly = [];
        foreach ($this->fetch_death_trend_yearly($year_be) as $r) {
            $hosp_yearly[(int) $r->year_be] = (int) $r->in_count;
            $hosp_out_yearly[(int) $r->year_be] = (int) $r->out_count;
        }
        $yearly_trend = [];
        for ($i = 4; $i >= 0; $i--) {
            $y = $year_be - $i;
            $yearly_trend[] = [
                'year_be' => $y,
                'in_count' => $hosp_yearly[$y] ?? 0,
                'out_count' => ($out_yearly[$y] ?? 0) + ($hosp_out_yearly[$y] ?? 0),
            ];
        }

        return view('hosxp.death.index', compact(
            'title',
            'year_select',
            'year_be',
            'year_start',
            'year_end',
            'table_start_date',
            'table_end_date',
            'death_list',
            'death_in',
            'death_out',
            'death_stats',
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
        $year_be = $dates['year_be'];
        $year_select = $dates['year_select'];

        // แยกในสถานพยาบาล / นอกสถานพยาบาล แล้วจัดอันดับสาเหตุจากรายชื่อชุดเดียวกับหน้ารายงาน
        [, $in, $out, $stats] = $this->classifyDeaths($start_date, $end_date);

        $icdName = fn($r) => !empty($r->icdname) ? $r->icdname : 'ไม่บันทึกรหัสโรค';
        $grpName = fn($r) => !empty($r->name504) ? $r->name504 : 'ไม่มีรหัสโรค';

        $diag_icd10 = $this->rankCauses($in, $icdName);
        $diag_504 = $this->rankCauses($in, $grpName);
        $out_icd10 = $this->rankCauses($out, $icdName);
        $out_504 = $this->rankCauses($out, $grpName);
        $in_total = count($in);
        $out_total = count($out);

        return view('hosxp.death.death_top20', compact(
            'title',
            'year_select',
            'year_be',
            'start_date',
            'end_date',
            'diag_icd10',
            'diag_504',
            'out_icd10',
            'out_504',
            'in_total',
            'out_total'
        ));
    }

    /**
     * แยกผู้เสียชีวิตเป็น "ในสถานพยาบาล" / "นอกสถานพยาบาล"
     *  - HOSxP: มี AN = ในสถานพยาบาล(ผู้ป่วยใน) เสมอ, death_place = 2 (ไม่มี AN) = นอกสถานพยาบาล,
     *           นอกนั้น (1 / ว่าง / ค่าเก่า) = ในสถานพยาบาล (ไม่มี AN)
     *  - AOPOD ที่ไม่พบเลขบัตรใน HOSxP = นอกสถานพยาบาล (เฉพาะ AOPOD)
     * คืนค่า [ทั้งหมด, ในสถานพยาบาล, นอกสถานพยาบาล, สถิติย่อย]
     */
    private function classifyDeaths(string $start, string $end): array
    {
        $hosp = $this->fetch_death_patients($start, $end);
        $aopod = AopodService::outOfHospital($start, $end);

        $in = array_values(array_filter($hosp, fn($r) => in_array($r->dept, ['IPD', 'OPD'])));
        $outHosxp = array_values(array_filter($hosp, fn($r) => $r->dept === 'OUTH'));
        $out = array_merge($outHosxp, $aopod);

        $all = array_merge($in, $out);
        usort($all, fn($a, $b) => strcmp((string) $a->death_date, (string) $b->death_date));
        usort($out, fn($a, $b) => strcmp((string) $a->death_date, (string) $b->death_date));

        $stats = [
            'ipd' => count(array_filter($in, fn($r) => $r->dept === 'IPD')),
            'opd' => count(array_filter($in, fn($r) => $r->dept === 'OPD')),
            'out_hosxp' => count($outHosxp),
            'out_aopod' => count($aopod),
        ];

        return [$all, $in, $out, $stats];
    }

    /**
     * จัดอันดับสาเหตุ (ชาย/หญิง/รวม) จากรายชื่อผู้เสียชีวิต
     */
    private function rankCauses(array $rows, callable $nameFn): array
    {
        $acc = [];
        foreach ($rows as $r) {
            $name = $nameFn($r);
            $acc[$name] ??= (object) ['name' => $name, 'male' => 0, 'female' => 0, 'sum' => 0];
            if ((string) $r->sex === '1') {
                $acc[$name]->male++;
            } elseif ((string) $r->sex === '2') {
                $acc[$name]->female++;
            }
            $acc[$name]->sum++;
        }
        usort($acc, fn($a, $b) => $b->sum <=> $a->sum);
        return array_slice(array_values($acc), 0, 20);
    }

    private function resolveDateRange(Request $request)
    {
        // ปี พ.ศ. (ปีปฏิทิน ม.ค. - ธ.ค.) 8 ปีล่าสุด
        $currentBE = (int) date('Y') + 543;
        $year_select = range($currentBE, $currentBE - 7);

        $year_be = (int) ($request->year ?: $currentBE);
        if (!in_array($year_be, $year_select)) {
            $year_be = $currentBE;
        }

        $year_start = ($year_be - 543) . '-01-01';
        $year_end = ($year_be - 543) . '-12-31';

        // Support explicit start_date & end_date if requested
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        } else {
            $start_date = $year_start;
            $end_date = $year_end;
        }

        // Independent table date range (defaults to current month, clamped to selected year)
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
            'year_be' => $year_be,
            'year_select' => $year_select,
        ];
    }

    private function fetch_death_patients($start_date, $end_date)
    {
        return DB::connection('hosxp')->select('select
            pt.hn,d.an,COALESCE(NULLIF(d.cid,""),pt.cid) AS cid,CONCAT(pt.pname,pt.fname,SPACE(1),pt.lname ) AS ptname,
            pt.sex,pt.birthday,d.death_date,d.death_time,c1.name1 AS name504,CONCAT("[",i1.`code`,"] ",i1.`name`) AS icdname,
            CASE WHEN d.an IS NOT NULL AND d.an <> "" THEN "IPD"
                 WHEN d.death_place = "2" THEN "OUTH"
                 ELSE "OPD" END AS dept
            FROM death d
            LEFT OUTER JOIN patient pt ON pt.hn = d.hn
            LEFT OUTER JOIN rpt_504_name c1 ON c1.id = d.death_cause
            LEFT OUTER JOIN icd101 i1 ON i1.CODE = d.death_diag_1
            WHERE d.death_date BETWEEN ? AND ?
            ORDER BY d.death_date', [$start_date, $end_date]);
    }

    private function fetch_death_trend_monthly($start_date, $end_date)
    {
        return DB::connection('hosxp')->select("
            select
                DATE_FORMAT(d.death_date, '%m') as month_num,
                sum(case when d.death_place = '2' and (d.an is null or d.an = '') then 0 else 1 end) as in_count,
                sum(case when d.death_place = '2' and (d.an is null or d.an = '') then 1 else 0 end) as out_count
            from death d
            where d.death_date between ? and ?
            group by month_num
            order by month_num
        ", [$start_date, $end_date]);
    }

    private function fetch_death_trend_yearly($year_be)
    {
        $years = [];
        for ($i = 4; $i >= 0; $i--) {
            $years[] = $year_be - $i;
        }
        $placeholders = implode(',', array_fill(0, count($years), '?'));

        return DB::connection('hosxp')->select("
            select
                (YEAR(d.death_date) + 543) as year_be,
                sum(case when d.death_place = '2' and (d.an is null or d.an = '') then 0 else 1 end) as in_count,
                sum(case when d.death_place = '2' and (d.an is null or d.an = '') then 1 else 0 end) as out_count
            from death d
            where (YEAR(d.death_date) + 543) in ($placeholders)
            group by year_be
            order by year_be
        ", $years);
    }
}
