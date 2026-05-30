<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpdController extends Controller
{
    public function index(Request $request)
    {
        $title = 'งานบริการผู้ป่วยใน (IPD)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Tab Handling
        $tab = $request->get('tab', 'total');
        $ward_filter = "";
        $bed_capacity = 60; // Default for total
        $time_field = "i.regtime"; // เวลา admit โรงพยาบาล (default)
        $los_field = "a.admdate"; // วันนอน (default คือวันนอนของ admission)
        $tab_name = "ผู้ป่วยในรวม";

        if ($tab == 'general') {
            // กรองเฉพาะผู้ที่จำหน่ายจากวอร์ดสามัญ (i.ward = '01')
            $ward_filter = " AND i.ward = '01' ";
            $bed_capacity = 40;
            // ใช้เวลาที่ย้ายเข้าวอร์ดสามัญครั้งแรก (จาก iptbedmove)
            $time_field = "IFNULL((SELECT MIN(movetime) FROM iptbedmove WHERE an = i.an AND nward = '01'), i.regtime)";
            // คำนวณวันนอนจริงเฉพาะวอร์ดสามัญ
            $los_field = "DATEDIFF(i.dchdate, IFNULL((SELECT MIN(movedate) FROM iptbedmove WHERE an = i.an AND nward = '01'), i.regdate))";
            $tab_name = "ผู้ป่วยในสามัญ";
        } elseif ($tab == 'vip') {
            // กรองเฉพาะผู้ที่จำหน่ายจากวอร์ด VIP (i.ward = '03')
            $ward_filter = " AND i.ward = '03' ";
            $bed_capacity = 20;
            // ใช้เวลาที่ย้ายเข้าวอร์ด VIP ครั้งแรก (จาก iptbedmove)
            $time_field = "IFNULL((SELECT MIN(movetime) FROM iptbedmove WHERE an = i.an AND nward = '03'), i.regtime)";
            // คำนวณวันนอนจริงเฉพาะวอร์ด VIP
            $los_field = "DATEDIFF(i.dchdate, IFNULL((SELECT MIN(movedate) FROM iptbedmove WHERE an = i.an AND nward = '03'), i.regdate))";
            $tab_name = "ผู้ป่วยใน VIP";
        }

        // 1. Monthly IPD Statistics
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(a.dchdate) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 1 THEN CONCAT('ม.ค. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 2 THEN CONCAT('ก.พ. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 3 THEN CONCAT('มี.ค. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 4 THEN CONCAT('เม.ย. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 5 THEN CONCAT('พ.ค. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 6 THEN CONCAT('มิ.ย. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 7 THEN CONCAT('ก.ค. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 8 THEN CONCAT('ส.ค. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                    WHEN MONTH(a.dchdate) = 9 THEN CONCAT('ก.ย. ', RIGHT(YEAR(a.dchdate) + 543, 2))
                END AS 'month_year',
                COUNT(DISTINCT a.an) AS 'total_admission',
                SUM(a.admdate) AS 'total_bed_days',
                -- อัตราครองเตียง (Bed Occupancy Rate)
                -- สูตร: (วันนอนรวม × 100) / (จำนวนเตียง × จำนวนวันในเดือน)
                ROUND((SUM(a.admdate) * 100) / ({$bed_capacity} * CASE 
                    WHEN YEAR(a.dchdate) = YEAR(CURDATE()) AND MONTH(a.dchdate) = MONTH(CURDATE()) 
                    THEN DAY(CURDATE()) 
                    ELSE DAY(LAST_DAY(a.dchdate)) 
                END), 2) AS 'bed_occupancy_rate',
                -- จำนวนเตียงที่ใช้งานเฉลี่ยต่อวัน (Active Bed)
                ROUND((SUM(a.admdate) / CASE 
                    WHEN YEAR(a.dchdate) = YEAR(CURDATE()) AND MONTH(a.dchdate) = MONTH(CURDATE()) 
                    THEN DAY(CURDATE()) 
                    ELSE DAY(LAST_DAY(a.dchdate)) 
                END), 2) AS 'active_bed',
                ROUND(SUM(a.admdate) / COUNT(DISTINCT a.an), 2) AS 'avg_los_days',
                ROUND(SUM(a.hospital_admdate) / COUNT(DISTINCT a.an), 2) AS 'avg_hospital_los_days',
                ROUND(SUM(a.adjrw), 4) AS 'total_adjrw',
                -- รายได้เรียกเก็บสุทธิต่อหน่วยน้ำหนักสัมพัทธ์
                ROUND(SUM(a.income - a.rcpt_money) / NULLIF(SUM(a.adjrw), 0), 2) AS 'net_income_per_rw',
                -- ค่าดัชนีกลุ่มวินิจฉัยโรคร่วมเฉลี่ย
                ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2) AS 'cmi',
                -- ค่ายา (inc12) และ ค่า LAB (inc03)
                SUM(a.inc12) AS 'total_inc12',
                SUM(a.inc03) AS 'total_inc03',
                -- สถิติการรับใหม่แยกตามเวร (ใช้เวลาย้ายเข้าวอร์ดนั้นๆ)
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '08:00:00' AND '15:59:59' THEN 1 ELSE 0 END) AS 'admit_morning_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '16:00:00' AND '23:59:59' THEN 1 ELSE 0 END) AS 'admit_evening_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '00:00:00' AND '07:59:59' THEN 1 ELSE 0 END) AS 'admit_night_shift',
                SUM(a.is_refer_in) AS 'total_refer_in',
                SUM(a.is_refer_out) AS 'total_refer_out'
            FROM (
                SELECT 
                    i.an, 
                    i.dchdate, 
                    {$time_field} AS regtime, 
                    i.adjrw, 
                    {$los_field} AS admdate, 
                    a.admdate AS hospital_admdate,
                    a.income, 
                    a.rcpt_money,
                    a.inc12,
                    a.inc03,
                    IF(ri.vn IS NOT NULL, 1, 0) AS is_refer_in,
                    IF(ro.vn IS NOT NULL, 1, 0) AS is_refer_out
                FROM ipt i
                INNER JOIN an_stat a ON a.an = i.an
                LEFT JOIN referin ri ON ri.vn = i.vn
                LEFT JOIN referout ro ON ro.vn = i.an
                WHERE i.dchdate BETWEEN ? AND ?
                  AND i.dchdate IS NOT NULL
                  AND a.pdx NOT IN ('Z290', 'Z208')
                {$ward_filter}
                GROUP BY i.an
            ) AS a
            GROUP BY YEAR(a.dchdate), MONTH(a.dchdate)
            ORDER BY YEAR(a.dchdate), MONTH(a.dchdate)
        ", [$start_date, $end_date]);

        // 2. Currently Admitted by Ward
        $current_admit = DB::connection('hosxp')->select("
            SELECT COUNT(DISTINCT an) AS total,
                IFNULL(SUM(CASE WHEN ward = '01' THEN 1 ELSE 0 END),0) AS 'ipd',
                IFNULL(SUM(CASE WHEN ward = '03' THEN 1 ELSE 0 END),0) AS 'vip',
                IFNULL(SUM(CASE WHEN ward = '02' THEN 1 ELSE 0 END),0) AS 'lr',
                IFNULL(SUM(CASE WHEN ward = '06' THEN 1 ELSE 0 END),0) AS 'homeward'
            FROM (
                SELECT i.an, i.ward 
                FROM ipt i 
                WHERE confirm_discharge = 'N'
            ) AS a
        ")[0];

        // 3. Summary IPD Statistics (รวมทั้งปีงบประมาณ)
        $summary_stats = DB::connection('hosxp')->select("
            SELECT 
                'รวมทั้งหมด' AS 'month_year',
                COUNT(DISTINCT a.an) AS 'total_admission',
                SUM(a.admdate) AS 'total_bed_days',
                ROUND((SUM(a.admdate) * 100) / ({$bed_capacity} * (DATEDIFF(LEAST(?, CURDATE()), ?) + 1)), 2) AS 'bed_occupancy_rate',
                ROUND(SUM(a.admdate) / (DATEDIFF(LEAST(?, CURDATE()), ?) + 1), 2) AS 'active_bed',
                ROUND(SUM(a.admdate) / COUNT(DISTINCT a.an), 2) AS 'avg_los_days',
                ROUND(SUM(a.hospital_admdate) / COUNT(DISTINCT a.an), 2) AS 'avg_hospital_los_days',
                SUM(a.adjrw) AS 'total_adjrw',
                ROUND(SUM(a.income - a.rcpt_money) / NULLIF(SUM(a.adjrw), 0), 2) AS 'net_income_per_rw',
                ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2) AS 'cmi',
                SUM(a.inc12) AS 'total_inc12',
                SUM(a.inc03) AS 'total_inc03',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '08:00:00' AND '15:59:59' THEN 1 ELSE 0 END) AS 'admit_morning_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '16:00:00' AND '23:59:59' THEN 1 ELSE 0 END) AS 'admit_evening_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '00:00:00' AND '07:59:59' THEN 1 ELSE 0 END) AS 'admit_night_shift',
                SUM(a.is_refer_in) AS 'total_refer_in',
                SUM(a.is_refer_out) AS 'total_refer_out'
            FROM (
                SELECT 
                    i.an,
                    {$time_field} AS regtime,
                    i.adjrw,
                    {$los_field} AS admdate,
                    a.admdate AS hospital_admdate,
                    a.income,
                    a.rcpt_money,
                    a.inc12,
                    a.inc03,
                    IF(ri.vn IS NOT NULL, 1, 0) AS is_refer_in,
                    IF(ro.vn IS NOT NULL, 1, 0) AS is_refer_out
                FROM ipt i
                INNER JOIN an_stat a ON a.an = i.an
                LEFT JOIN referin ri ON ri.vn = i.vn
                LEFT JOIN referout ro ON ro.vn = i.an
                WHERE i.dchdate BETWEEN ? AND ?
                  AND i.dchdate IS NOT NULL
                  AND a.pdx NOT IN ('Z290', 'Z208')
                {$ward_filter}
                GROUP BY i.an
            ) AS a
        ", [$end_date, $start_date, $end_date, $start_date, $start_date, $end_date])[0];

        // 4. Discharge Type Distribution (Bottom Left Chart)
        $dch_types = DB::connection('hosxp')->select("
            SELECT 
                dt.name AS dch_type_name,
                COUNT(*) AS count
            FROM ipt i
            LEFT JOIN dchtype dt ON dt.dchtype = i.dchtype
            INNER JOIN an_stat a ON a.an = i.an
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.dchdate IS NOT NULL
              AND a.pdx NOT IN ('Z290', 'Z208')
              {$ward_filter}
            GROUP BY i.dchtype, dt.name
            ORDER BY count DESC
        ", [$start_date, $end_date]);

        // 5. Top 10 Diagnoses (Bottom Right Chart)
        $top_pdx = DB::connection('hosxp')->select("
            SELECT 
                IFNULL(a.pdx, 'ยังไม่สรุป Chart') AS pdx,
                IFNULL(i10.name, 'ยังไม่สรุป Chart') AS diag_name,
                COUNT(DISTINCT a.an) AS count
            FROM an_stat a
            INNER JOIN ipt i ON i.an = a.an
            LEFT JOIN icd101 i10 ON i10.code = a.pdx
            WHERE a.dchdate BETWEEN ? AND ?
              AND a.dchdate IS NOT NULL
              AND a.pdx NOT IN ('Z290', 'Z208')
              {$ward_filter}
            GROUP BY a.pdx, i10.name
            ORDER BY count DESC
            LIMIT 10
        ", [$start_date, $end_date]);

        return view('hosxp.ipd.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'monthly_stats', 'current_admit', 'summary_stats', 'tab', 'tab_name',
            'dch_types', 'top_pdx'
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

    public function severity(Request $request)
    {
        $title = 'รายงานจำนวนผู้ป่วยในแยกระดับความรุนแรง';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $tab = $request->get('tab', 'total');
        $ward_filter = "";
        $tab_title = "ผู้ป่วยในรวม";
        if ($tab == 'general') {
            $ward_filter = " AND i.ward = '01' ";
            $tab_title = "ผู้ป่วยในสามัญ";
        } elseif ($tab == 'vip') {
            $ward_filter = " AND i.ward = '03' ";
            $tab_title = "ผู้ป่วยใน VIP";
        }

        $results = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(i.dchdate) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 1 THEN CONCAT('ม.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 2 THEN CONCAT('ก.พ. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 3 THEN CONCAT('มี.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 4 THEN CONCAT('เม.ย. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 5 THEN CONCAT('พ.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 6 THEN CONCAT('มิ.ย. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 7 THEN CONCAT('ก.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 8 THEN CONCAT('ส.ค. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                    WHEN MONTH(i.dchdate) = 9 THEN CONCAT('ก.ย. ', RIGHT(YEAR(i.dchdate) + 543, 2))
                END AS month_year,
                SUM(CASE WHEN i.ipt_severe_type_id = 1 THEN 1 ELSE 0 END) AS admit_1,
                SUM(CASE WHEN i.ipt_severe_type_id = 2 THEN 1 ELSE 0 END) AS admit_2,
                SUM(CASE WHEN i.ipt_severe_type_id = 3 THEN 1 ELSE 0 END) AS admit_3,
                SUM(CASE WHEN i.ipt_severe_type_id = 4 THEN 1 ELSE 0 END) AS admit_4,
                SUM(CASE WHEN i.ipt_severe_type_id IS NULL THEN 1 ELSE 0 END) AS admit_null,
                SUM(CASE WHEN i.dch_severe_type_id = 1 THEN 1 ELSE 0 END) AS dch_1,
                SUM(CASE WHEN i.dch_severe_type_id = 2 THEN 1 ELSE 0 END) AS dch_2,
                SUM(CASE WHEN i.dch_severe_type_id = 3 THEN 1 ELSE 0 END) AS dch_3,
                SUM(CASE WHEN i.dch_severe_type_id = 4 THEN 1 ELSE 0 END) AS dch_4,
                SUM(CASE WHEN i.dch_severe_type_id IS NULL THEN 1 ELSE 0 END) AS dch_null,
                COUNT(i.an) AS total_patients
            FROM ipt i
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.dchdate IS NOT NULL
              $ward_filter
            GROUP BY YEAR(i.dchdate), MONTH(i.dchdate)
            ORDER BY YEAR(i.dchdate), MONTH(i.dchdate)
        ", [$start_date, $end_date]);

        return view('hosxp.ipd.severity', compact(
            'title', 'tab', 'tab_title', 'budget_year_select', 'budget_year', 'start_date', 'end_date', 'results'
        ));
    }

    public function readmit(Request $request)
    {
        $title = 'รายงาน Re-Admit ภายใน 28 วันด้วยโรคเดิม';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // 1. Patient List Query (Optimized)
        $patients = DB::connection('hosxp')->select("
            SELECT 
                p.hn,
                CONCAT(p.pname, p.fname, ' ', p.lname) AS ptname,
                ipt_new.an AS AN_new,
                ipt_new.regdate AS regdate_AN_New,
                ipt_new.dchdate AS dcdate_AN_New,
                ipt_old.an AS AN_old,
                ipt_old.regdate AS regdate_AN_Old,
                ipt_old.dchdate AS dcdate_AN_Old,
                diag_new.icd10 AS icd10_1,
                c.name AS icd_name,
                TIMESTAMPDIFF(DAY, ipt_old.dchdate, ipt_new.regdate) AS ReAdmitDate
            FROM ipt ipt_new
            INNER JOIN iptdiag diag_new ON diag_new.an = ipt_new.an AND diag_new.diagtype = '1'
            INNER JOIN icd101 c ON c.code = diag_new.icd10
            INNER JOIN ipt ipt_old ON ipt_old.hn = ipt_new.hn AND ipt_old.an <> ipt_new.an
            INNER JOIN iptdiag diag_old ON diag_old.an = ipt_old.an AND diag_old.diagtype = '1' AND diag_old.icd10 = diag_new.icd10
            INNER JOIN patient p ON p.hn = ipt_new.hn
            WHERE ipt_new.regdate BETWEEN ? AND ?
              AND TIMESTAMPDIFF(DAY, ipt_old.dchdate, ipt_new.regdate) > 0
              AND TIMESTAMPDIFF(DAY, ipt_old.dchdate, ipt_new.regdate) <= 28
            ORDER BY ipt_new.an
        ", [$start_date, $end_date]);

        // 2. Monthly Re-admissions count (Optimized)
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(ipt_new.regdate) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 1 THEN CONCAT('ม.ค. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 2 THEN CONCAT('ก.พ. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 3 THEN CONCAT('มี.ค. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 4 THEN CONCAT('เม.ย. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 5 THEN CONCAT('พ.ค. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 6 THEN CONCAT('มิ.ย. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 7 THEN CONCAT('ก.ค. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 8 THEN CONCAT('ส.ค. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                    WHEN MONTH(ipt_new.regdate) = 9 THEN CONCAT('ก.ย. ', RIGHT(YEAR(ipt_new.regdate) + 543, 2))
                END AS month_year,
                COUNT(DISTINCT ipt_new.an) AS total_readmit
            FROM ipt ipt_new
            INNER JOIN iptdiag diag_new ON diag_new.an = ipt_new.an AND diag_new.diagtype = '1'
            INNER JOIN ipt ipt_old ON ipt_old.hn = ipt_new.hn AND ipt_old.an <> ipt_new.an
            INNER JOIN iptdiag diag_old ON diag_old.an = ipt_old.an AND diag_old.diagtype = '1' AND diag_old.icd10 = diag_new.icd10
            WHERE ipt_new.regdate BETWEEN ? AND ?
              AND TIMESTAMPDIFF(DAY, ipt_old.dchdate, ipt_new.regdate) > 0
              AND TIMESTAMPDIFF(DAY, ipt_old.dchdate, ipt_new.regdate) <= 28
            GROUP BY YEAR(ipt_new.regdate), MONTH(ipt_new.regdate)
            ORDER BY YEAR(ipt_new.regdate), MONTH(ipt_new.regdate)
        ", [$start_date, $end_date]);

        // 3. Top 10 Re-admit Diagnoses (Optimized)
        $top_diagnoses = DB::connection('hosxp')->select("
            SELECT 
                diag_new.icd10 AS icd10,
                c.name AS icd_name,
                COUNT(DISTINCT ipt_new.an) AS total_readmit
            FROM ipt ipt_new
            INNER JOIN iptdiag diag_new ON diag_new.an = ipt_new.an AND diag_new.diagtype = '1'
            INNER JOIN icd101 c ON c.code = diag_new.icd10
            INNER JOIN ipt ipt_old ON ipt_old.hn = ipt_new.hn AND ipt_old.an <> ipt_new.an
            INNER JOIN iptdiag diag_old ON diag_old.an = ipt_old.an AND diag_old.diagtype = '1' AND diag_old.icd10 = diag_new.icd10
            WHERE ipt_new.regdate BETWEEN ? AND ?
              AND TIMESTAMPDIFF(DAY, ipt_old.dchdate, ipt_new.regdate) > 0
              AND TIMESTAMPDIFF(DAY, ipt_old.dchdate, ipt_new.regdate) <= 28
            GROUP BY diag_new.icd10, c.name
            ORDER BY total_readmit DESC
            LIMIT 10
        ", [$start_date, $end_date]);

        return view('hosxp.ipd.readmit', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date', 'patients', 'monthly_stats', 'top_diagnoses'
        ));
    }
}
