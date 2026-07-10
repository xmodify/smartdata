<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LrController extends Controller
{
    public function index(Request $request)
    {
        $title = 'งานบริการห้องคลอด LR';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // Tab Handling
        $tab = $request->get('tab', 'total');
        $delivery_filter = "";
        $bed_capacity = 10; // Default bed capacity for Ward 02
        $tab_name = "ผู้คลอดทั้งหมด";

        if ($tab == 'normal') {
            // คลอดปกติ (Normal labor / deliver_type 1)
            $delivery_filter = " AND ip.deliver_type = '1' ";
            $tab_name = "คลอดธรรมชาติ (Normal Labor)";
        } elseif ($tab == 'cs') {
            // คลอดผิดปกติ (deliver_type 2)
            $delivery_filter = " AND ip.deliver_type = '2' ";
            $tab_name = "คลอดผิดปกติ (Abnormal)";
        } elseif ($tab == 'assist') {
            // อื่นๆ (แท้ง, ไม่คลอด / deliver_type 3, 4)
            $delivery_filter = " AND ip.deliver_type IN ('3','4') ";
            $tab_name = "อื่น ๆ (แท้ง / ไม่คลอด)";
        }

        // 1. Monthly LR Statistics
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
                ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2) AS 'cmi',
                -- คล้าย IPD แยกกะเข้าโรงพยาบาล
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '08:00:00' AND '15:59:59' THEN 1 ELSE 0 END) AS 'admit_morning_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '16:00:00' AND '23:59:59' THEN 1 ELSE 0 END) AS 'admit_evening_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '00:00:00' AND '07:59:59' THEN 1 ELSE 0 END) AS 'admit_night_shift',
                SUM(a.is_refer_in) AS 'total_refer_in',
                SUM(a.is_refer_out) AS 'total_refer_out'
            FROM (
                SELECT 
                    i.an, 
                    i.dchdate, 
                    i.regtime, 
                    i.adjrw, 
                    a.admdate, 
                    a.admdate AS hospital_admdate,
                    IF(ri.vn IS NOT NULL, 1, 0) AS is_refer_in,
                    IF(ro.vn IS NOT NULL, 1, 0) AS is_refer_out
                FROM ipt i
                INNER JOIN an_stat a ON a.an = i.an
                INNER JOIN ipt_pregnancy ip ON ip.an = i.an
                LEFT JOIN referin ri ON ri.vn = i.vn
                LEFT JOIN referout ro ON ro.vn = i.an
                WHERE i.dchdate BETWEEN ? AND ?
                  AND i.dchdate IS NOT NULL
                  AND i.ward = '02'
                  {$delivery_filter}
                GROUP BY i.an
            ) AS a
            GROUP BY YEAR(a.dchdate), MONTH(a.dchdate)
            ORDER BY YEAR(a.dchdate), MONTH(a.dchdate)
        ", [$start_date, $end_date]);

        // 2. Currently Admitted to Ward 02
        $current_admit = DB::connection('hosxp')->select("
            SELECT COUNT(DISTINCT an) AS total
            FROM ipt
            WHERE confirm_discharge = 'N' AND ward = '02'
        ")[0]->total;

        // 3. Summary LR Statistics (รวมทั้งปีงบประมาณ)
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
                ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2) AS 'cmi',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '08:00:00' AND '15:59:59' THEN 1 ELSE 0 END) AS 'admit_morning_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '16:00:00' AND '23:59:59' THEN 1 ELSE 0 END) AS 'admit_evening_shift',
                SUM(CASE WHEN TIME(a.regtime) BETWEEN '00:00:00' AND '07:59:59' THEN 1 ELSE 0 END) AS 'admit_night_shift',
                SUM(a.is_refer_in) AS 'total_refer_in',
                SUM(a.is_refer_out) AS 'total_refer_out'
            FROM (
                SELECT 
                    i.an,
                    i.regtime,
                    i.adjrw,
                    a.admdate,
                    a.admdate AS hospital_admdate,
                    IF(ri.vn IS NOT NULL, 1, 0) AS is_refer_in,
                    IF(ro.vn IS NOT NULL, 1, 0) AS is_refer_out
                FROM ipt i
                INNER JOIN an_stat a ON a.an = i.an
                INNER JOIN ipt_pregnancy ip ON ip.an = i.an
                LEFT JOIN referin ri ON ri.vn = i.vn
                LEFT JOIN referout ro ON ro.vn = i.an
                WHERE i.dchdate BETWEEN ? AND ?
                  AND i.dchdate IS NOT NULL
                  AND i.ward = '02'
                  {$delivery_filter}
                GROUP BY i.an
            ) AS a
        ", [
            $end_date, $start_date, $end_date, $start_date, // For occupancy rate / active bed date diffs
            $start_date, $end_date
        ])[0];

        // 4. วิธีการคลอด (Delivery Methods breakdown)
        $delivery_types_breakdown = DB::connection('hosxp')->select("
            SELECT 
                IFNULL(dt.name, 'ไม่ระบุ') AS delivery_type,
                COUNT(DISTINCT ip.an) AS total_cases
            FROM ipt_pregnancy ip
            INNER JOIN ipt i ON i.an = ip.an
            LEFT JOIN ipt_pregnancy_deliver_type dt ON dt.id = ip.deliver_type
            WHERE i.dchdate BETWEEN ? AND ?
              AND i.ward = '02'
            GROUP BY dt.name
            ORDER BY total_cases DESC
        ", [$start_date, $end_date]);

        // 5. 10 อันดับโรคแทรก/ภาวะแทรกซ้อน (Top 10 diagnoses in Ward 02)
        $top_pdx = DB::connection('hosxp')->select("
            SELECT 
                IFNULL(a.pdx, 'ยังไม่สรุป Chart') AS pdx,
                IFNULL(i10.name, 'ยังไม่สรุป Chart') AS diag_name,
                COUNT(DISTINCT a.an) AS count
            FROM an_stat a
            INNER JOIN ipt i ON i.an = a.an
            INNER JOIN ipt_pregnancy ip ON ip.an = i.an
            LEFT JOIN icd101 i10 ON i10.code = a.pdx
            WHERE a.dchdate BETWEEN ? AND ?
              AND a.dchdate IS NOT NULL
              AND i.ward = '02'
            GROUP BY a.pdx, i10.name
            ORDER BY count DESC
            LIMIT 10
        ", [$start_date, $end_date]);

        return view('hosxp.lr.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'monthly_stats', 'current_admit', 'summary_stats', 'tab', 'tab_name',
            'delivery_types_breakdown', 'top_pdx'
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
