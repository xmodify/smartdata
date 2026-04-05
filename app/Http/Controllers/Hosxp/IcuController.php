<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IcuController extends Controller
{
    public function index(Request $request)
    {
        $title = 'งานผู้ป่วยหนัก ICU';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // 1. Detailed Patient List (User's Query)
        $patients = DB::connection('hosxp')->select("
            SELECT
                w.name AS 'ward_name',
                i.an ,
                i.hn,
                CONCAT(p.pname, p.fname, ' ', p.lname) AS 'ptname',
                i.regdate ,
                i.regtime ,
                i.dchdate ,
                i.dchtime ,
                DATEDIFF(i.dchdate, i.regdate) AS 'admdate',     
                ds.name AS 'dch_status',
                dt.name AS 'dch_type',
                d.name AS 'dch_doctor',
                a.pdx,
                a.diag_text_list
            FROM ipt i
            LEFT JOIN an_stat a ON a.an=i.an
            LEFT JOIN patient p ON p.hn = i.hn
            LEFT JOIN ward w ON w.ward = i.ward
            LEFT JOIN dchstts ds ON ds.dchstts = i.dchstts
            LEFT JOIN dchtype dt ON dt.dchtype = i.dchtype
            LEFT JOIN doctor d ON d.code = i.dch_doctor
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.dchdate IS NOT NULL 
            AND i.an IN (SELECT DISTINCT an FROM iptbedmove WHERE nbedno LIKE 'ICU%')
            ORDER BY i.dchdate ASC, i.ward ASC
        ", [$start_date, $end_date]);

        // 2. Monthly Stats (Top Chart)
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(i.dchdate)='10' THEN CONCAT('ต.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='11' THEN CONCAT('พ.ย. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='12' THEN CONCAT('ธ.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='1' THEN CONCAT('ม.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='2' THEN CONCAT('ก.พ. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='3' THEN CONCAT('มี.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='4' THEN CONCAT('เม.ย. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='5' THEN CONCAT('พ.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='6' THEN CONCAT('มิ.ย. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='7' THEN CONCAT('ก.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='8' THEN CONCAT('ส.ค. ',RIGHT(YEAR(i.dchdate)+543,2))
                    WHEN MONTH(i.dchdate)='9' THEN CONCAT('ก.ย. ',RIGHT(YEAR(i.dchdate)+543,2))
                END AS 'month',
                COUNT(DISTINCT i.an) AS 'count'
            FROM ipt i
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.dchdate IS NOT NULL
            AND i.an IN (SELECT DISTINCT an FROM iptbedmove WHERE nbedno LIKE 'ICU%')
            GROUP BY YEAR(i.dchdate), MONTH(i.dchdate)
            ORDER BY YEAR(i.dchdate), MONTH(i.dchdate)
        ", [$start_date, $end_date]);

        // 3. Discharge Type Distribution (Bottom Left Chart)
        $dch_types = DB::connection('hosxp')->select("
            SELECT 
                dt.name AS dch_type_name,
                COUNT(*) AS count
            FROM ipt i
            LEFT JOIN dchtype dt ON dt.dchtype = i.dchtype
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.dchdate IS NOT NULL
            AND i.an IN (SELECT DISTINCT an FROM iptbedmove WHERE nbedno LIKE 'ICU%')
            GROUP BY i.dchtype, dt.name
            ORDER BY count DESC
        ", [$start_date, $end_date]);

        // 4. Top 10 Diagnoses (Bottom Right Chart)
        $top_pdx = DB::connection('hosxp')->select("
            SELECT 
                IFNULL(a.pdx, 'ยังไม่สรุป Chart') AS pdx,
                IFNULL(i.name, 'ยังไม่สรุป Chart') AS diag_name,
                COUNT(DISTINCT a.an) AS count
            FROM an_stat a
            LEFT JOIN icd101 i ON i.code = a.pdx
            WHERE a.dchdate BETWEEN ? AND ?
              AND a.dchdate IS NOT NULL
            AND a.an IN (SELECT DISTINCT an FROM iptbedmove WHERE nbedno LIKE 'ICU%')
            GROUP BY a.pdx, i.name
            ORDER BY count DESC
            LIMIT 10
        ", [$start_date, $end_date]);

        // 5. Currently Admitted (Ward 10)
        $admit_count = DB::connection('hosxp')
            ->table('ipt')
            ->whereNull('dchdate')
            ->where('ward', 10)
            ->count();

        return view('hosxp.icu.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'patients', 'monthly_stats', 'dch_types', 'top_pdx', 'admit_count'
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

            // Sync budget_year to the start_date provided
            $matched_year = DB::table('budget_year')
                ->where('DATE_BEGIN', '<=', $start_date)
                ->where('DATE_END', '>=', $start_date)
                ->value('LEAVE_YEAR_ID');

            if ($matched_year) {
                $budget_year = $matched_year;
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
}
