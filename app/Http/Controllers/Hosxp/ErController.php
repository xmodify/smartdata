<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErController extends Controller
{
    public function index(Request $request)
    {
        $title = 'รายงานอุบัติเหตุ-ฉุกเฉิน (ER)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // 1. Monthly ER Statistics by Severity
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(e.vstdate) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 1 THEN CONCAT('ม.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 2 THEN CONCAT('ก.พ. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 3 THEN CONCAT('มี.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 4 THEN CONCAT('เม.ย. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 5 THEN CONCAT('พ.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 6 THEN CONCAT('มิ.ย. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 7 THEN CONCAT('ก.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 8 THEN CONCAT('ส.ค. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                    WHEN MONTH(e.vstdate) = 9 THEN CONCAT('ก.ย. ', RIGHT(YEAR(e.vstdate) + 543, 2))
                END AS 'month_year',
                COUNT(DISTINCT e.vn) AS 'total_visit',
                SUM(CASE WHEN e.er_emergency_type = 1 THEN 1 ELSE 0 END) AS 'level_1',
                SUM(CASE WHEN e.er_emergency_type = 2 THEN 1 ELSE 0 END) AS 'level_2',
                SUM(CASE WHEN e.er_emergency_type = 3 THEN 1 ELSE 0 END) AS 'level_3',
                SUM(CASE WHEN e.er_emergency_type = 4 THEN 1 ELSE 0 END) AS 'level_4',
                SUM(CASE WHEN e.er_emergency_type = 5 THEN 1 ELSE 0 END) AS 'level_5',
                SUM(CASE WHEN e.er_emergency_type IS NULL OR e.er_emergency_type NOT IN (1,2,3,4,5) THEN 1 ELSE 0 END) AS 'level_null'
            FROM er_regist e
            WHERE e.vstdate BETWEEN ? AND ?
            GROUP BY YEAR(e.vstdate), MONTH(e.vstdate)
            ORDER BY YEAR(e.vstdate), MONTH(e.vstdate)
        ", [$start_date, $end_date]);

        // 2. Summary Statistics for Cards
        $summary_stats = DB::connection('hosxp')->selectOne("
            SELECT 
                COUNT(DISTINCT e.vn) AS 'total_visit',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 1 THEN 1 ELSE 0 END), 0) AS 'level_1',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 2 THEN 1 ELSE 0 END), 0) AS 'level_2',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 3 THEN 1 ELSE 0 END), 0) AS 'level_3',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 4 THEN 1 ELSE 0 END), 0) AS 'level_4',
                IFNULL(SUM(CASE WHEN e.er_emergency_type = 5 THEN 1 ELSE 0 END), 0) AS 'level_5',
                IFNULL(SUM(CASE WHEN e.er_emergency_type IS NULL OR e.er_emergency_type NOT IN (1,2,3,4,5) THEN 1 ELSE 0 END), 0) AS 'level_null'
            FROM er_regist e
            WHERE e.vstdate BETWEEN ? AND ?
        ", [$start_date, $end_date]);

        // 3. Dynamic severity level names
        $severity_types = DB::connection('hosxp')->table('er_emergency_type')
            ->select('er_emergency_type', 'name')
            ->orderBy('er_emergency_type')
            ->get()
            ->keyBy('er_emergency_type');

        return view('hosxp.er.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'monthly_stats', 'summary_stats', 'severity_types'
        ));
    }

    public function ems(Request $request)
    {
        $title = 'รายงานผู้ป่วยให้บริการ EMS';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $ems_diag_als = DB::connection('hosxp')->select('
            SELECT CONCAT("[",pdx,"] " ,name) AS name, count(*) AS sum
            FROM (
                SELECT v.vn, v.hn, v.vstdate, v.pdx, i.name 
                FROM vn_stat v
                LEFT JOIN icd101 i ON i.code=v.pdx
                LEFT JOIN ovst o ON o.vn=v.vn
                WHERE v.vstdate BETWEEN ? AND ?
                AND (v.pdx <> "" OR v.pdx IS NOT NULL)
                AND o.ovstist IN ("08")
                AND v.pdx NOT LIKE "z%" AND v.pdx NOT IN ("u119")
            ) AS a
            GROUP BY pdx
            ORDER BY sum DESC LIMIT 20
        ', [$start_date, $end_date]);

        $ems_diag_als_name = array_column($ems_diag_als, 'name');
        $ems_diag_als_sum = array_column($ems_diag_als, 'sum');

        $ems_diag_ils = DB::connection('hosxp')->select('
            SELECT CONCAT("[",pdx,"] " ,name) AS name, count(*) AS sum
            FROM (
                SELECT v.vn, v.hn, v.vstdate, v.pdx, i.name 
                FROM vn_stat v
                LEFT JOIN icd101 i ON i.code=v.pdx
                LEFT JOIN ovst o ON o.vn=v.vn
                WHERE v.vstdate BETWEEN ? AND ?
                AND (v.pdx <> "" OR v.pdx IS NOT NULL)
                AND o.ovstist IN ("09")
                AND v.pdx NOT LIKE "z%" AND v.pdx NOT IN ("u119")
            ) AS a
            GROUP BY pdx
            ORDER BY sum DESC LIMIT 20
        ', [$start_date, $end_date]);

        $ems_diag_ils_name = array_column($ems_diag_ils, 'name');
        $ems_diag_ils_sum = array_column($ems_diag_ils, 'sum');

        $ems_diag_fr = DB::connection('hosxp')->select('
            SELECT CONCAT("[",pdx,"] " ,name) AS name, count(*) AS sum
            FROM (
                SELECT v.vn, v.hn, v.vstdate, v.pdx, i.name 
                FROM vn_stat v
                LEFT JOIN icd101 i ON i.code=v.pdx
                LEFT JOIN ovst o ON o.vn=v.vn
                WHERE v.vstdate BETWEEN ? AND ?
                AND (v.pdx <> "" OR v.pdx IS NOT NULL)
                AND o.ovstist IN ("10")
                AND v.pdx NOT LIKE "z%" AND v.pdx NOT IN ("u119")
            ) AS a
            GROUP BY pdx
            ORDER BY sum DESC LIMIT 20
        ', [$start_date, $end_date]);

        $ems_diag_fr_name = array_column($ems_diag_fr, 'name');
        $ems_diag_fr_sum = array_column($ems_diag_fr, 'sum');

        $ems_list = DB::connection('hosxp')->select('
            SELECT o.vn, o.oqueue, o.vstdate, o.vsttime, o.hn, CONCAT(p.pname, p.fname, SPACE(1), p.lname) AS ptname,
            v.age_y, CONCAT(o.pttype, " [", p1.hipdata_code, "]") AS pttype, o1.cc, v.pdx, d.`name` AS dx_doctor,
            CASE WHEN o.ovstist = "08" THEN "ALS" WHEN o.ovstist = "09" THEN "FR" WHEN o.ovstist = "10" THEN "ILS" END AS ems,
            IF(o.an <> "", "Admit", NULL) AS admit, CONCAT(r.refer_hospcode, " [", r.pdx, "]") AS refer,
            CASE WHEN e.er_emergency_type = "1" THEN "Resuscitate" WHEN e.er_emergency_type = "2" THEN "Emergency" 
            WHEN e.er_emergency_type = "3" THEN "Urgency" WHEN e.er_emergency_type = "4" THEN "Semi_Urgency"  
            WHEN (e.er_emergency_type = "5" OR e.er_emergency_type IS NULL) THEN "Non_Urgency" END AS er_emergency_type
            FROM ovst o
            LEFT JOIN patient p ON p.hn=o.hn
            LEFT JOIN pttype p1 ON p1.pttype=o.pttype
            LEFT JOIN opdscreen o1 ON o1.vn=o.vn
            LEFT JOIN vn_stat v ON v.vn=o.vn
            LEFT JOIN referout r ON r.vn=o.vn
            LEFT JOIN doctor d ON d.`code`=v.dx_doctor
            LEFT JOIN er_regist e ON e.vn=o.vn
            WHERE o.vstdate BETWEEN ? AND ?
            AND o.ovstist IN ("08", "09", "10")
            GROUP BY o.vn
        ', [$start_date, $end_date]);

        $ems_monthly = DB::connection('hosxp')->select('
            SELECT 
                CASE 
                    WHEN MONTH(o.vstdate) = 10 THEN CONCAT("ต.ค. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 11 THEN CONCAT("พ.ย. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 12 THEN CONCAT("ธ.ค. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 1 THEN CONCAT("ม.ค. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 2 THEN CONCAT("ก.พ. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 3 THEN CONCAT("มี.ค. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 4 THEN CONCAT("เม.ย. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 5 THEN CONCAT("พ.ค. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 6 THEN CONCAT("มิ.ย. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 7 THEN CONCAT("ก.ค. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 8 THEN CONCAT("ส.ค. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                    WHEN MONTH(o.vstdate) = 9 THEN CONCAT("ก.ย. ", RIGHT(YEAR(o.vstdate) + 543, 2))
                END AS month_year,
                SUM(CASE WHEN o.ovstist = "08" THEN 1 ELSE 0 END) AS als,
                SUM(CASE WHEN o.ovstist = "09" THEN 1 ELSE 0 END) AS fr,
                SUM(CASE WHEN o.ovstist = "10" THEN 1 ELSE 0 END) AS ils
            FROM ovst o
            WHERE o.vstdate BETWEEN ? AND ?
            AND o.ovstist IN ("08", "09", "10")
            GROUP BY YEAR(o.vstdate), MONTH(o.vstdate)
            ORDER BY YEAR(o.vstdate), MONTH(o.vstdate)
        ', [$start_date, $end_date]);

        return view('hosxp.er.ems', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'ems_diag_als', 'ems_diag_als_name', 'ems_diag_als_sum',
            'ems_diag_ils', 'ems_diag_ils_name', 'ems_diag_ils_sum',
            'ems_diag_fr', 'ems_diag_fr_name', 'ems_diag_fr_sum',
            'ems_list', 'ems_monthly'
        ));
    }

    public function wait_admit_2h(Request $request)
    {
        $title = 'รายงานผู้ป่วยรอ Admit ที่ ER เกิน 2 ชั่วโมง';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $waitingtime_admit = DB::connection('hosxp')->select('
            SELECT * FROM (
                SELECT o.vstdate, o.oqueue, o.hn, CONCAT(pt.pname, pt.fname, SPACE(1), pt.lname) AS ptname, o.an,
                TIME(e.enter_er_time) AS er_time, et.`name` AS emergency_type, i.regtime AS admit_time, d.`name` AS er_doctor,
                LEFT(SEC_TO_TIME(AVG((time_to_sec(TIME(i.regtime)) - time_to_sec(TIME(e.enter_er_time))) )), 8) AS time_wait_admit
                FROM ovst o
                INNER JOIN er_regist e ON e.vn=o.vn
                LEFT JOIN er_emergency_type et ON et.er_emergency_type=e.er_emergency_type
                LEFT JOIN ipt i ON i.an=o.an
                LEFT JOIN patient pt ON pt.hn=o.hn
                LEFT JOIN doctor d ON d.`code`=e.er_doctor
                WHERE o.vstdate BETWEEN ? AND ?
                AND (o.an IS NOT NULL AND o.vn <> "") 
                GROUP BY o.vn
            ) AS a
            WHERE time_wait_admit >= "02:00:00"
            ORDER BY time_wait_admit DESC
        ', [$start_date, $end_date]);

        return view('hosxp.er.wait_admit_2h', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'waitingtime_admit'
        ));
    }

    public function revisit_48h(Request $request)
    {
        $title = 'รายงาน Re-visit ใน 48 ชม. ด้วยโรคเดิม ER';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        $revisit_list = DB::connection('hosxp')->select('
            SELECT o.vstdate, CONCAT(v.lastvisit_hour, " ช.ม.") AS p_vstdate, o.main_dep_queue AS q, o.hn, c.cc, CONCAT(p.pname, p.fname, SPACE(1), p.lname) AS ptname
            , v.age_y, v.pttype, v.pdx, IF(e.vn <> "", "ER", "OPD") AS depart, IF(o.an <> "", "Admit", NULL) AS admit, IF(r.vn <> "", "Refer", NULL) AS refer
            FROM ovst o
            LEFT JOIN vn_stat v ON v.vn=o.vn
            LEFT JOIN er_regist e ON e.vn=o.vn
            LEFT JOIN opdscreen c ON c.vn=o.vn
            LEFT JOIN ovstdiag o1 ON o1.vn=o.vn
            LEFT JOIN referout r ON r.vn=o.vn
            LEFT JOIN patient p ON p.hn=o.hn
            WHERE v.lastvisit_hour <= 48
            AND o.vstdate BETWEEN ? AND ?
            AND v.pdx NOT LIKE "Z%" AND v.old_diagnosis = "Y"
            AND (e.vn <> "" OR o.main_dep = "002")
            AND o1.icd10 NOT IN ("U071", "U072", "Z290", "Z208")
            AND c.cc NOT LIKE "%นัด%" AND c.cc NOT LIKE "%ต่อเนื่อง%" AND c.cc NOT LIKE "%ออกซิเจน%" AND c.cc NOT LIKE "%ออกชิเจน%"
            AND c.cc NOT LIKE "%ยาเดิม%"  AND c.cc NOT LIKE "%ใบความเห็นแพทย์%"  AND c.cc NOT LIKE "%covid%" AND c.cc NOT LIKE "%ยาแทน%"
            AND c.cc NOT LIKE "%ใบส่งตัว%" AND c.cc NOT LIKE "%ใบรับรองแพทย์%"
            GROUP BY o.vn, v.pdx
            ORDER BY v.pdx, o.hn, o.vstdate
        ', [$start_date, $end_date]);

        $revisit_diagtop = DB::connection('hosxp')->select('
            SELECT a.pdx AS code, i.`name` AS pdx_name, i.`tname` AS pdx_tname,
            SUM(CASE WHEN a.sex=1 THEN 1 ELSE 0 END) AS male,
            SUM(CASE WHEN a.sex=2 THEN 1 ELSE 0 END) AS female,
            COUNT(a.pdx) AS total
            FROM (
                SELECT o.vstdate, o.hn, p.sex, v.pdx
                FROM ovst o
                LEFT JOIN vn_stat v ON v.vn=o.vn
                LEFT JOIN er_regist e ON e.vn=o.vn
                LEFT JOIN opdscreen c ON c.vn=o.vn
                LEFT JOIN ovstdiag o1 ON o1.vn=o.vn
                LEFT JOIN patient p ON p.hn=o.hn
                WHERE v.lastvisit_hour <= 48
                AND o.vstdate BETWEEN ? AND ?
                AND v.pdx NOT LIKE "Z%" AND v.old_diagnosis = "Y"
                AND (e.vn <> "" OR o.main_dep = "002")
                AND o1.icd10 NOT IN ("U071", "U072", "Z290", "Z208")
                AND c.cc NOT LIKE "%นัด%" AND c.cc NOT LIKE "%ต่อเนื่อง%" AND c.cc NOT LIKE "%ออกซิเจน%" AND c.cc NOT LIKE "%ออกชิเจน%"
                AND c.cc NOT LIKE "%ยาเดิม%"  AND c.cc NOT LIKE "%ใบความเห็นแพทย์%"  AND c.cc NOT LIKE "%covid%" AND c.cc NOT LIKE "%ยาแทน%"
                AND c.cc NOT LIKE "%ใบส่งตัว%" AND c.cc NOT LIKE "%ใบรับรองแพทย์%"
                GROUP BY o.vn, v.pdx
            ) AS a
            LEFT JOIN icd101 i ON i.`code`=a.pdx
            GROUP BY a.pdx 
            ORDER BY total DESC 
            LIMIT 20
        ', [$start_date, $end_date]);

        $revisit_504 = DB::connection('hosxp')->select('
            SELECT 
                CONCAT(n.name1, " [", n.id, "]") AS name,
                IFNULL(d.male, 0) AS male,
                IFNULL(d.female, 0) AS female,
                IFNULL(d.total, 0) AS total
            FROM rpt_504_name n
            LEFT JOIN (
                SELECT c.id,
                       SUM(CASE WHEN a.sex = 1 THEN 1 ELSE 0 END) AS male,
                       SUM(CASE WHEN a.sex = 2 THEN 1 ELSE 0 END) AS female,
                       COUNT(c.id) AS total
                FROM rpt_504_code c
                INNER JOIN (
                    SELECT o.vstdate, p.sex, v.pdx
                    FROM ovst o
                    LEFT JOIN vn_stat v ON v.vn=o.vn
                    LEFT JOIN er_regist e ON e.vn=o.vn
                    LEFT JOIN opdscreen c ON c.vn=o.vn
                    LEFT JOIN ovstdiag o1 ON o1.vn=o.vn
                    LEFT JOIN patient p ON p.hn=o.hn
                    WHERE v.lastvisit_hour <= 48
                    AND o.vstdate BETWEEN ? AND ?
                    AND v.pdx NOT LIKE "Z%" AND v.old_diagnosis = "Y"
                    AND (e.vn <> "" OR o.main_dep = "002")
                    AND o1.icd10 NOT IN ("U071", "U072", "Z290", "Z208")
                    AND c.cc NOT LIKE "%นัด%" AND c.cc NOT LIKE "%ต่อเนื่อง%" AND c.cc NOT LIKE "%ออกซิเจน%" AND c.cc NOT LIKE "%ออกชิเจน%"
                    AND c.cc NOT LIKE "%ยาเดิม%"  AND c.cc NOT LIKE "%ใบความเห็นแพทย์%"  AND c.cc NOT LIKE "%covid%" AND c.cc NOT LIKE "%ยาแทน%"
                    AND c.cc NOT LIKE "%ใบส่งตัว%" AND c.cc NOT LIKE "%ใบรับรองแพทย์%"
                    GROUP BY o.vn, v.pdx
                ) a ON a.pdx BETWEEN c.code1 AND c.code2
                GROUP BY c.id
            ) d ON d.id = n.id
            WHERE IFNULL(d.total, 0) > 0
            ORDER BY total DESC
        ', [$start_date, $end_date]);

        $revisit_monthly = DB::connection('hosxp')->select('
            SELECT 
                CASE 
                    WHEN MONTH(a.vstdate) = 10 THEN CONCAT("ต.ค. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 11 THEN CONCAT("พ.ย. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 12 THEN CONCAT("ธ.ค. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 1 THEN CONCAT("ม.ค. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 2 THEN CONCAT("ก.พ. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 3 THEN CONCAT("มี.ค. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 4 THEN CONCAT("เม.ย. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 5 THEN CONCAT("พ.ค. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 6 THEN CONCAT("มิ.ย. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 7 THEN CONCAT("ก.ค. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 8 THEN CONCAT("ส.ค. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                    WHEN MONTH(a.vstdate) = 9 THEN CONCAT("ก.ย. ", RIGHT(YEAR(a.vstdate) + 543, 2))
                END AS month_year,
                SUM(CASE WHEN a.depart = "ER" THEN 1 ELSE 0 END) AS er,
                SUM(CASE WHEN a.depart = "OPD" THEN 1 ELSE 0 END) AS opd,
                COUNT(*) AS total
            FROM (
                SELECT o.vstdate, IF(e.vn <> "", "ER", "OPD") AS depart
                FROM ovst o
                LEFT JOIN vn_stat v ON v.vn=o.vn
                LEFT JOIN er_regist e ON e.vn=o.vn
                LEFT JOIN opdscreen c ON c.vn=o.vn
                LEFT JOIN ovstdiag o1 ON o1.vn=o.vn
                WHERE v.lastvisit_hour <= 48
                AND o.vstdate BETWEEN ? AND ?
                AND v.pdx NOT LIKE "Z%" AND v.old_diagnosis = "Y"
                AND (e.vn <> "" OR o.main_dep = "002")
                AND o1.icd10 NOT IN ("U071", "U072", "Z290", "Z208")
                AND c.cc NOT LIKE "%นัด%" AND c.cc NOT LIKE "%ต่อเนื่อง%" AND c.cc NOT LIKE "%ออกซิเจน%" AND c.cc NOT LIKE "%ออกชิเจน%"
                AND c.cc NOT LIKE "%ยาเดิม%"  AND c.cc NOT LIKE "%ใบความเห็นแพทย์%"  AND c.cc NOT LIKE "%covid%" AND c.cc NOT LIKE "%ยาแทน%"
                AND c.cc NOT LIKE "%ใบส่งตัว%" AND c.cc NOT LIKE "%ใบรับรองแพทย์%"
                GROUP BY o.vn, v.pdx
            ) AS a
            GROUP BY YEAR(a.vstdate), MONTH(a.vstdate)
            ORDER BY YEAR(a.vstdate), MONTH(a.vstdate)
        ', [$start_date, $end_date]);

        return view('hosxp.er.revisit_48h', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'revisit_list', 'revisit_diagtop', 'revisit_504', 'revisit_monthly'
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

        if ($request->start_date && $request->end_date && !$request->has('budget_year_changed')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;

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
