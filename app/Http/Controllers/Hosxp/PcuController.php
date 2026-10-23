<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PcuController extends Controller
{
    /**
     * เมนูหลักงานเชิงรุก PCU
     */
    public function index(Request $request)
    {
        $title = 'งานเชิงรุก PCU';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        return view('hosxp.pcu.index', compact('title', 'budget_year_select', 'budget_year', 'start_date', 'end_date'));
    }

    /**
     * รายงานอันดับสาเหตุการเจ็บป่วย/การมารับบริการสูงสุด คนไข้ในเขตรับผิดชอบ
     * Template เดียวกับ 20 อันดับโรค OPD โดยเพิ่มตัวกรองหมู่บ้าน
     */
    public function top10_illness(Request $request)
    {
        $title = 'อันดับสาเหตุการเจ็บป่วย/การมารับบริการสูงสุด คนไข้ในเขตรับผิดชอบ';

        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // ตัวกรองหมู่บ้าน
        $village_id = $request->get('village_id', 'all');

        $villages = DB::connection('hosxp')->table('village')
            ->where('village_moo', '<>', '0')
            ->where('village_moo', '<>', '')
            ->where('village_id', '<>', 1)
            ->orderByRaw('cast(village_moo as unsigned)')
            ->get();

        $selected_village = ($village_id !== 'all') ? $villages->firstWhere('village_id', $village_id) : null;
        if ($selected_village) {
            $title .= " (บ้าน{$selected_village->village_name} หมู่ {$selected_village->village_moo})";
        }

        $diag_icd10 = $this->fetch_top20_opd_pcu($start_date, $end_date, $village_id);
        $diag_504 = $this->fetch_group_504_pcu($start_date, $end_date, $village_id);

        return view('hosxp.pcu.top10_illness', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'villages',
            'village_id',
            'selected_village',
            'diag_icd10',
            'diag_504'
        ));
    }

    /**
     * ดึงข้อมูล 20 อันดับโรค OPD คนไข้ในเขต (ไม่ใช่หมู่ 0)
     */
    private function fetch_top20_opd_pcu($start_date, $end_date, $village_id = 'all')
    {
        $whereVil = "";
        $params = [$start_date, $end_date];

        if ($village_id !== 'all') {
            $whereVil = " AND vi.village_id = ? ";
            $params[] = $village_id;
        }

        return DB::connection('hosxp')->select("
            SELECT 
                i.`code`,
                i.`name`,
                i.tname,
                count(v.pdx) as sum, 
                sum(case when v.sex = 1 THEN 1 ELSE 0 END) as male,   
                sum(case when v.sex = 2 THEN 1 ELSE 0 END) as female,
                sum(v.inc03) as inc_lab,
                sum(v.inc12) as inc_drug   
            FROM vn_stat v
            JOIN person p ON v.hn = p.patient_hn
            JOIN village vi ON p.village_id = vi.village_id
            LEFT OUTER JOIN icd101 i ON i.code = v.pdx 
            WHERE vi.village_moo <> '0' AND vi.village_moo <> '' AND vi.village_id <> 1
            AND v.vstdate BETWEEN ? AND ?
            AND v.pdx <> '' AND v.pdx IS NOT NULL
            {$whereVil}
            GROUP BY v.pdx, i.name, i.tname
            ORDER BY sum DESC 
            LIMIT 20
        ", $params);
    }

    /**
     * ดึงข้อมูล 21 กลุ่มสาเหตุ (รง.504) คนไข้ในเขต (ไม่ใช่หมู่ 0)
     */
    private function fetch_group_504_pcu($start_date, $end_date, $village_id = 'all')
    {
        $whereVil = "";
        $params = [$start_date, $end_date];

        if ($village_id !== 'all') {
            $whereVil = " AND vi.village_id = ? ";
            $params[] = $village_id;
        }

        return DB::connection('hosxp')->select("
            SELECT 
                concat(a.name1, ' [', a.id, ']') as name,
                ifnull(d.male, 0) as male,
                ifnull(d.female, 0) as female,
                ifnull(d.amount, 0) as sum,
                ifnull(d.inc_lab, 0) as inc_lab,
                ifnull(d.inc_drug, 0) as inc_drug
            FROM rpt_504_name a 
            LEFT JOIN (
                SELECT b.id,
                    sum(case when v.sex = 1 THEN 1 ELSE 0 END) as male,   
                    sum(case when v.sex = 2 THEN 1 ELSE 0 END) as female,
                    count(b.id) as amount,
                    sum(v.inc03) as inc_lab,
                    sum(v.inc12) as inc_drug
                FROM rpt_504_code b
                JOIN vn_stat v ON v.pdx BETWEEN b.code1 AND b.code2
                JOIN person p ON v.hn = p.patient_hn
                JOIN village vi ON p.village_id = vi.village_id
                WHERE vi.village_moo <> '0' AND vi.village_moo <> '' AND vi.village_id <> 1
                AND v.vstdate BETWEEN ? AND ?
                {$whereVil}
                GROUP BY b.id
            ) d ON d.id = a.id 
            ORDER BY sum DESC
        ", $params);
    }

    /**
     * Standard Date Range Resolver for all reports
     */
    private function resolveDateRange(Request $request)
    {
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

        if ($request->start_date && $request->end_date) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

            $matched_year = DB::table('budget_year')
                ->where('DATE_BEGIN', '<=', $start_date)
                ->where('DATE_END', '>=', $start_date)
                ->first();

            if ($matched_year) {
                // If user selected budget_year from dropdown and also sent start_date/end_date:
                // If budget_year does NOT match the year of start_date, the user intentionally chose a new budget_year!
                if ($request->filled('budget_year') && (int)$request->budget_year !== (int)$matched_year->LEAVE_YEAR_ID) {
                    $chosen_year = $budget_year_select->firstWhere('LEAVE_YEAR_ID', $request->budget_year);
                    if ($chosen_year) {
                        $start_date = $chosen_year->DATE_BEGIN;
                        $end_date = $chosen_year->DATE_END;
                        $budget_year = $chosen_year->LEAVE_YEAR_ID;
                    }
                } else {
                    $budget_year = $matched_year->LEAVE_YEAR_ID;
                }
            }
        } else {
            $year_data = DB::table('budget_year')
                ->where('LEAVE_YEAR_ID', $budget_year)
                ->first();

            if ($year_data) {
                $start_date = $year_data->DATE_BEGIN;
                $end_date = $year_data->DATE_END;
            } else {
                $start_date = ($budget_year - 544) . '-10-01';
                $end_date = ($budget_year - 543) . '-09-30';
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