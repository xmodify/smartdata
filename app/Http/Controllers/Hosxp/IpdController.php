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
                ROUND(SUM(a.adjrw), 4) AS 'total_adjrw',
                -- รายได้เรียกเก็บสุทธิต่อหน่วยน้ำหนักสัมพัทธ์
                ROUND(SUM(a.income - a.rcpt_money) / NULLIF(SUM(a.adjrw), 0), 2) AS 'net_income_per_rw',
                -- ค่าดัชนีกลุ่มวินิจฉัยโรคร่วมเฉลี่ย
                ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2) AS 'cmi',
                -- สถิติการรับใหม่แยกตามเวร (ใช้เวลาย้ายเข้าวอร์ดนั้นๆ)
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '08:00:00' AND '15:59:59' THEN 1 ELSE 0 END) AS 'admit_morning_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '16:00:00' AND '23:59:59' THEN 1 ELSE 0 END) AS 'admit_evening_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '00:00:00' AND '07:59:59' THEN 1 ELSE 0 END) AS 'admit_night_shift'
            FROM (
                SELECT 
                    i.an, 
                    i.dchdate, 
                    {$time_field} AS regtime, 
                    i.adjrw, 
                    {$los_field} AS admdate, 
                    a.income, 
                    a.rcpt_money
                FROM ipt i
                INNER JOIN an_stat a ON a.an = i.an
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
                SUM(a.adjrw) AS 'total_adjrw',
                ROUND(SUM(a.income - a.rcpt_money) / NULLIF(SUM(a.adjrw), 0), 2) AS 'net_income_per_rw',
                ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2) AS 'cmi',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '08:00:00' AND '15:59:59' THEN 1 ELSE 0 END) AS 'admit_morning_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '16:00:00' AND '23:59:59' THEN 1 ELSE 0 END) AS 'admit_evening_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '00:00:00' AND '07:59:59' THEN 1 ELSE 0 END) AS 'admit_night_shift'
            FROM (
                SELECT 
                    i.an,
                    {$time_field} AS regtime,
                    i.adjrw,
                    {$los_field} AS admdate,
                    a.income,
                    a.rcpt_money
                FROM ipt i
                INNER JOIN an_stat a ON a.an = i.an
                WHERE i.dchdate BETWEEN ? AND ?
                  AND i.dchdate IS NOT NULL
                  AND a.pdx NOT IN ('Z290', 'Z208')
                {$ward_filter}
                GROUP BY i.an
            ) AS a
        ", [$end_date, $start_date, $end_date, $start_date, $start_date, $end_date])[0];

        return view('hosxp.ipd.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'monthly_stats', 'current_admit', 'summary_stats', 'tab', 'tab_name'
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
