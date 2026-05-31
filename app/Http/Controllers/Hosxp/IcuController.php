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

        // 1. Detailed Patient List
        $patients = DB::connection('hosxp')->select("
            SELECT
                w.name AS 'ward_name',
                i.an,
                i.hn,
                CONCAT(p.pname, p.fname, ' ', p.lname) AS 'ptname',
                -- วันที่/เวลาที่ย้ายเข้าเตียง ICU (จาก iptbedmove)
                icu.movedate AS 'icu_movedate',
                icu.movetime AS 'icu_movetime',
                -- เวรที่รับเข้า ICU
                CASE
                    WHEN TIME(icu.movetime) BETWEEN '08:00:00' AND '15:59:59' THEN 'เวรเช้า'
                    WHEN TIME(icu.movetime) BETWEEN '16:00:00' AND '23:59:59' THEN 'เวรบ่าย'
                    WHEN TIME(icu.movetime) BETWEEN '00:00:00' AND '07:59:59' THEN 'เวรดึก'
                    ELSE '-'
                END AS 'admit_shift',
                i.dchdate,
                i.dchtime,
                -- วันนอนรวมที่ ICU: จากวันที่เข้าเตียง ICU ถึงวันจำหน่าย
                DATEDIFF(i.dchdate, icu.movedate) AS 'icu_los_days',
                -- คำนวณชั่วโมงอย่างละเอียด
                ROUND(TIMESTAMPDIFF(HOUR,
                    CONCAT(icu.movedate, ' ', icu.movetime),
                    CONCAT(i.dchdate, ' ', i.dchtime)
                ) / 24, 1) AS 'icu_los_exact',
                -- วันนอนรวมทั้งหมด (admit โรงพยาบาล ถึงจำหน่าย)
                DATEDIFF(i.dchdate, i.regdate) AS 'total_los_days',
                ds.name AS 'dch_status',
                dt.name AS 'dch_type',
                d.name AS 'dch_doctor',
                a.pdx,
                a.diag_text_list,
                i.adjrw,
                pt.name AS pttype_name
            FROM ipt i
            -- JOIN กับ iptbedmove เพื่อดึงวันที่/เวลาที่ย้ายเข้าเตียง ICU (เร็วที่สุด)
            INNER JOIN (
                SELECT an, MIN(movedate) AS movedate,
                       SUBSTRING_INDEX(GROUP_CONCAT(movetime ORDER BY movedate ASC, movetime ASC), ',', 1) AS movetime
                FROM iptbedmove
                WHERE nbedno LIKE 'ICU%'
                GROUP BY an
            ) icu ON icu.an = i.an
            LEFT JOIN an_stat a ON a.an = i.an
            LEFT JOIN patient p ON p.hn = i.hn
            LEFT JOIN ward w ON w.ward = i.ward
            LEFT JOIN dchstts ds ON ds.dchstts = i.dchstts
            LEFT JOIN dchtype dt ON dt.dchtype = i.dchtype
            LEFT JOIN doctor d ON d.code = i.dch_doctor
            LEFT JOIN pttype pt ON pt.pttype = i.pttype
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.dchdate IS NOT NULL
            ORDER BY i.dchdate ASC, i.ward ASC
        ", [$start_date, $end_date]);

        $bed_capacity = 4;

        // 2. Monthly Stats with Detailed Metrics (Admissions, Occupancy, AdjRW, CMI, Shifts)
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(a.dchdate)='10' THEN CONCAT('ต.ค. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='11' THEN CONCAT('พ.ย. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='12' THEN CONCAT('ธ.ค. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='1' THEN CONCAT('ม.ค. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='2' THEN CONCAT('ก.พ. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='3' THEN CONCAT('มี.ค. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='4' THEN CONCAT('เม.ย. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='5' THEN CONCAT('พ.ค. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='6' THEN CONCAT('มิ.ย. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='7' THEN CONCAT('ก.ค. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='8' THEN CONCAT('ส.ค. ',RIGHT(YEAR(a.dchdate)+543,2))
                    WHEN MONTH(a.dchdate)='9' THEN CONCAT('ก.ย. ',RIGHT(YEAR(a.dchdate)+543,2))
                END AS 'month',
                -- อัตราครองเตียง (Bed Occupancy Rate)
                -- สูตร: (วันนอน ICU รวม × 100) / (จำนวนเตียง × จำนวนวันในเดือน)
                COUNT(DISTINCT a.an) AS 'total_admission',
                SUM(a.icu_los_days) AS 'total_bed_days',
                SUM(a.total_los_days) AS 'total_hospital_bed_days',
                ROUND((SUM(a.icu_los_days) * 100) / ({$bed_capacity} * CASE 
                    WHEN YEAR(a.dchdate) = YEAR(CURDATE()) AND MONTH(a.dchdate) = MONTH(CURDATE()) 
                    THEN DAY(CURDATE()) 
                    ELSE DAY(LAST_DAY(a.dchdate)) 
                END), 2) AS 'bed_occupancy_rate',
                -- จำนวนเตียงที่ใช้งานเฉลี่ยต่อวัน (Active Bed)
                ROUND((SUM(a.icu_los_days) / CASE 
                    WHEN YEAR(a.dchdate) = YEAR(CURDATE()) AND MONTH(a.dchdate) = MONTH(CURDATE()) 
                    THEN DAY(CURDATE()) 
                    ELSE DAY(LAST_DAY(a.dchdate)) 
                END), 2) AS 'active_bed',
                -- วันนอนเฉลี่ย ICU และ รพ.
                ROUND(SUM(a.icu_los_days) / COUNT(DISTINCT a.an), 2) AS 'avg_icu_los_days',
                ROUND(SUM(a.total_los_days) / COUNT(DISTINCT a.an), 2) AS 'avg_total_los_days',
                ROUND(SUM(a.adjrw), 4) AS 'total_adjrw',
                -- ค่าดัชนีกลุ่มวินิจฉัยโรคร่วมเฉลี่ย (CMI)
                ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2) AS 'cmi',
                -- ค่ายา (inc12) และ ค่า LAB (inc03)
                SUM(a.inc12) AS 'total_inc12',
                SUM(a.inc03) AS 'total_inc03',
                -- สถิติการรับใหม่แยกตามเวร (ใช้เวลาที่ย้ายเข้าเตียง ICU จาก iptbedmove)
                SUM(CASE WHEN TIME(a.icu_movetime) BETWEEN '08:00:00' AND '15:59:59' THEN 1 ELSE 0 END) AS 'admit_morning_shift',
                SUM(CASE WHEN TIME(a.icu_movetime) BETWEEN '16:00:00' AND '23:59:59' THEN 1 ELSE 0 END) AS 'admit_evening_shift',
                SUM(CASE WHEN TIME(a.icu_movetime) BETWEEN '00:00:00' AND '07:59:59' THEN 1 ELSE 0 END) AS 'admit_night_shift',
                SUM(a.is_refer_in) AS 'total_refer_in',
                SUM(a.is_refer_out) AS 'total_refer_out',
                SUM(a.vent_less_96) AS 'vent_less_96',
                SUM(a.vent_more_96) AS 'vent_more_96'
            FROM (
                SELECT 
                    i.an, 
                    i.dchdate,
                    i.adjrw,
                    -- วันนอน ICU จริง: ตั้งแต่เข้าเตียง ICU (iptbedmove) ถึงจำหน่าย
                    DATEDIFF(i.dchdate, icu.movedate) AS icu_los_days,
                    -- วันนอนโรงพยาบาลทั้งหมด
                    DATEDIFF(i.dchdate, i.regdate) AS total_los_days,
                    -- ดึงเวลาเข้าเตียง ICU ครั้งแรกสุดจาก iptbedmove
                    icu.movetime AS icu_movetime,
                    a.inc12,
                    a.inc03,
                    IF(ri.vn IS NOT NULL, 1, 0) AS is_refer_in,
                    IF(ro.vn IS NOT NULL, 1, 0) AS is_refer_out,
                    IFNULL((SELECT COUNT(*) FROM iptoprt WHERE an = i.an AND icd9 = '9671'), 0) AS vent_less_96,
                    IFNULL((SELECT COUNT(*) FROM iptoprt WHERE an = i.an AND icd9 = '9672'), 0) AS vent_more_96
                FROM ipt i
                INNER JOIN an_stat a ON a.an = i.an
                INNER JOIN (
                    SELECT an,
                           MIN(movedate) AS movedate,
                           SUBSTRING_INDEX(GROUP_CONCAT(movetime ORDER BY movedate ASC, movetime ASC), ',', 1) AS movetime
                    FROM iptbedmove
                    WHERE nbedno LIKE 'ICU%'
                    GROUP BY an
                ) icu ON icu.an = i.an
                LEFT JOIN referin ri ON ri.vn = i.vn
                LEFT JOIN referout ro ON ro.vn = i.an
                WHERE i.dchdate BETWEEN ? AND ?
                  AND i.dchdate IS NOT NULL
                GROUP BY i.an
            ) AS a
            GROUP BY YEAR(a.dchdate), MONTH(a.dchdate)
            ORDER BY YEAR(a.dchdate), MONTH(a.dchdate)
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

        // 6. รอสรุป Chart: นับผู้ป่วย ICU ที่จำหน่ายแล้วแต่ adjrw = 0 หรือ NULL (ยังไม่สรุป Chart)
        $pending_chart_count = DB::connection('hosxp')->select("
            SELECT COUNT(DISTINCT i.an) AS cnt
            FROM ipt i
            INNER JOIN (
                SELECT DISTINCT an FROM iptbedmove WHERE nbedno LIKE 'ICU%'
            ) icu ON icu.an = i.an
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.dchdate IS NOT NULL
              AND (i.adjrw IS NULL OR i.adjrw = 0)
        ", [$start_date, $end_date]);
        $pending_chart_count = $pending_chart_count[0]->cnt ?? 0;

        // 6. Summary Stats
        $summary_stats = DB::connection('hosxp')->select("
            SELECT 
                'รวมทั้งหมด' AS 'month_year',
                COUNT(DISTINCT a.an) AS 'total_admission',
                SUM(a.icu_los_days) AS 'total_bed_days',
                SUM(a.total_los_days) AS 'total_hospital_bed_days',
                ROUND((SUM(a.icu_los_days) * 100) / ({$bed_capacity} * (DATEDIFF(LEAST(?, CURDATE()), ?) + 1)), 2) AS 'bed_occupancy_rate',
                ROUND(SUM(a.icu_los_days) / (DATEDIFF(LEAST(?, CURDATE()), ?) + 1), 2) AS 'active_bed',
                ROUND(SUM(a.icu_los_days) / COUNT(DISTINCT a.an), 2) AS 'avg_icu_los_days',
                ROUND(SUM(a.total_los_days) / COUNT(DISTINCT a.an), 2) AS 'avg_total_los_days',
                SUM(a.adjrw) AS 'total_adjrw',
                ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2) AS 'cmi',
                SUM(a.inc12) AS 'total_inc12',
                SUM(a.inc03) AS 'total_inc03',
                SUM(CASE WHEN TIME(a.icu_movetime) BETWEEN '08:00:00' AND '15:59:59' THEN 1 ELSE 0 END) AS 'admit_morning_shift',
                SUM(CASE WHEN TIME(a.icu_movetime) BETWEEN '16:00:00' AND '23:59:59' THEN 1 ELSE 0 END) AS 'admit_evening_shift',
                SUM(CASE WHEN TIME(a.icu_movetime) BETWEEN '00:00:00' AND '07:59:59' THEN 1 ELSE 0 END) AS 'admit_night_shift',
                SUM(a.is_refer_in) AS 'total_refer_in',
                SUM(a.is_refer_out) AS 'total_refer_out',
                SUM(a.vent_less_96) AS 'vent_less_96',
                SUM(a.vent_more_96) AS 'vent_more_96'
            FROM (
                SELECT 
                    i.an, i.regtime, i.adjrw, 
                    DATEDIFF(i.dchdate, icu.movedate) AS icu_los_days,
                    DATEDIFF(i.dchdate, i.regdate) AS total_los_days,
                    icu.movetime AS icu_movetime,
                    a.inc12, a.inc03,
                    IF(ri.vn IS NOT NULL, 1, 0) AS is_refer_in,
                    IF(ro.vn IS NOT NULL, 1, 0) AS is_refer_out,
                    IFNULL((SELECT COUNT(*) FROM iptoprt WHERE an = i.an AND icd9 = '9671'), 0) AS vent_less_96,
                    IFNULL((SELECT COUNT(*) FROM iptoprt WHERE an = i.an AND icd9 = '9672'), 0) AS vent_more_96
                FROM ipt i
                INNER JOIN an_stat a ON a.an = i.an
                INNER JOIN (
                    SELECT an, MIN(movedate) AS movedate,
                           SUBSTRING_INDEX(GROUP_CONCAT(movetime ORDER BY movedate ASC, movetime ASC), ',', 1) AS movetime
                    FROM iptbedmove
                    WHERE nbedno LIKE 'ICU%'
                    GROUP BY an
                ) icu ON icu.an = i.an
                LEFT JOIN referin ri ON ri.vn = i.vn
                LEFT JOIN referout ro ON ro.vn = i.an
                WHERE i.dchdate BETWEEN ? AND ?
                GROUP BY i.an
            ) AS a
        ", [$end_date, $start_date, $end_date, $start_date, $start_date, $end_date])[0];

        // 4.5 Patients with Important Procedures (9671, 9672)
        $vent_patients = DB::connection('hosxp')->select("
            SELECT
                i.an,
                i.hn,
                CONCAT(p.pname, p.fname, ' ', p.lname) AS 'ptname',
                icu.movedate AS 'icu_movedate',
                icu.movetime AS 'icu_movetime',
                i.dchdate,
                i.dchtime,
                DATEDIFF(i.dchdate, icu.movedate) AS 'icu_los_days',
                op.icd9,
                io.name AS 'proc_name',
                op.opdate,
                op.optime,
                a.pdx,
                a.diag_text_list
            FROM ipt i
            INNER JOIN (
                SELECT an, MIN(movedate) AS movedate,
                       SUBSTRING_INDEX(GROUP_CONCAT(movetime ORDER BY movedate ASC, movetime ASC), ',', 1) AS movetime
                FROM iptbedmove
                WHERE nbedno LIKE 'ICU%'
                GROUP BY an
            ) icu ON icu.an = i.an
            INNER JOIN iptoprt op ON op.an = i.an
            LEFT JOIN icd9cm1 io ON io.code = op.icd9
            LEFT JOIN an_stat a ON a.an = i.an
            LEFT JOIN patient p ON p.hn = i.hn
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.dchdate IS NOT NULL
              AND op.icd9 IN ('9671', '9672')
            ORDER BY op.opdate ASC, op.optime ASC
        ", [$start_date, $end_date]);

        return view('hosxp.icu.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'patients', 'monthly_stats', 'dch_types', 'top_pdx', 'admit_count',
            'summary_stats', 'bed_capacity', 'pending_chart_count', 'vent_patients'
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
