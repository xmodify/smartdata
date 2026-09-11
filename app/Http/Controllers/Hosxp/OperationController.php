<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationController extends Controller
{
    public function index(Request $request)
    {
        $title = 'รายงานห้องผ่าตัด (OR)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];
        $by_start_date = $dates['by_start_date'];
        $by_end_date = $dates['by_end_date'];

        // Status Filter: 3 = ผ่าตัดจริง (เสร็จแล้ว) [Default], not_done = สั่งแต่ไม่ได้ผ่า/รอผ่าตัด, 1 = รอผ่าตัด, 9 = ยกเลิก, all = ทั้งหมด
        $status = $request->get('status', '3');

        $status_condition = '';
        if ($status === '3') {
            $status_condition = ' AND ol.status_id = 3 ';
        } elseif ($status === '1') {
            $status_condition = ' AND ol.status_id = 1 ';
        } elseif ($status === '9') {
            $status_condition = ' AND ol.status_id = 9 ';
        } elseif ($status === 'not_done') {
            $status_condition = ' AND ol.status_id IN (1, 9) ';
        }

        // 1. Annual Monthly Stats (Top Chart - 12 Months of selected budget year)
        $monthly_stats = DB::connection('hosxp')->select("
            SELECT 
                CASE 
                    WHEN MONTH(ol.operation_date) = 10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 1  THEN CONCAT('ม.ค. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 2  THEN CONCAT('ก.พ. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 3  THEN CONCAT('มี.ค. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 4  THEN CONCAT('เม.ย. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 5  THEN CONCAT('พ.ค. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 6  THEN CONCAT('มิ.ย. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 7  THEN CONCAT('ก.ค. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 8  THEN CONCAT('ส.ค. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                    WHEN MONTH(ol.operation_date) = 9  THEN CONCAT('ก.ย. ', RIGHT(YEAR(ol.operation_date) + 543, 2))
                END AS month_label,
                YEAR(ol.operation_date) AS yr,
                MONTH(ol.operation_date) AS mo,
                COUNT(ol.operation_id) AS total_cases,
                SUM(CASE WHEN ol.emergency_id = 1 THEN 1 ELSE 0 END) AS emergency_cases,
                SUM(CASE WHEN ol.emergency_id = 2 THEN 1 ELSE 0 END) AS elective_cases,
                SUM(CASE WHEN ol.patient_department = 'IPD' THEN 1 ELSE 0 END) AS ipd_cases,
                SUM(CASE WHEN ol.patient_department = 'OPD' THEN 1 ELSE 0 END) AS opd_cases
            FROM operation_list ol
            WHERE ol.operation_date BETWEEN ? AND ?
            {$status_condition}
            GROUP BY YEAR(ol.operation_date), MONTH(ol.operation_date)
            ORDER BY 
                CASE 
                    WHEN MONTH(ol.operation_date) >= 10 THEN MONTH(ol.operation_date) - 10
                    ELSE MONTH(ol.operation_date) + 2
                END ASC
        ", [$by_start_date, $by_end_date]);

        // 2. Summary KPI Cards (For Calendar Range: start_date to end_date)
        $summary = DB::connection('hosxp')->selectOne("
            SELECT 
                COUNT(ol.operation_id) AS total_cases,
                SUM(CASE WHEN ol.status_id = 3 THEN 1 ELSE 0 END) AS completed_cases,
                SUM(CASE WHEN ol.emergency_id = 2 THEN 1 ELSE 0 END) AS elective_cases,
                SUM(CASE WHEN ol.emergency_id = 1 THEN 1 ELSE 0 END) AS emergency_cases,
                SUM(CASE WHEN ol.status_id IN (1, 2) THEN 1 ELSE 0 END) AS pending_cases,
                SUM(CASE WHEN ol.status_id = 9 THEN 1 ELSE 0 END) AS cancelled_cases,
                SUM(CASE WHEN ol.patient_department = 'IPD' THEN 1 ELSE 0 END) AS ipd_cases,
                SUM(CASE WHEN ol.patient_department = 'OPD' THEN 1 ELSE 0 END) AS opd_cases,
                COALESCE(SUM(ol.blood_loss), 0) AS total_blood_loss
            FROM operation_list ol
            WHERE ol.operation_date BETWEEN ? AND ?
        ", [$start_date, $end_date]);

        // 3. Detailed Patient List (Tab 1 - Filtered by Calendar Range and Status)
        $patients = DB::connection('hosxp')->select("
            SELECT 
                ol.operation_id,
                ol.operation_date,
                ol.operation_time,
                ol.hn,
                CONCAT(p.pname, p.fname, ' ', p.lname) AS patient_name,
                ol.patient_department,
                ol.an,
                room.room_name,
                otype.name AS operation_type,
                oem.emergency_name,
                ostat.status_name,
                ol.operation_name,
                ol.operation_detail_name,
                -- แพทย์ผู้ทำการผ่าตัด (Surgeon)
                COALESCE(
                    (SELECT d.name 
                     FROM operation_team ot 
                     JOIN doctor d ON d.code = ot.doctor 
                     WHERE ot.operation_id = ol.operation_id AND ot.position_id = 1 
                     LIMIT 1),
                    (SELECT d.name FROM doctor d WHERE d.code = ol.request_doctor),
                    '-'
                ) AS surgeon_name,
                -- รายการผ่าตัดและรหัส ICD-9-CM
                (
                    SELECT GROUP_CONCAT(
                        CONCAT(COALESCE(oi.name, od.clinical_term, ol.operation_name), 
                               IF(COALESCE(od.icdcode, oi.icd9) IS NOT NULL, CONCAT(' [', COALESCE(od.icdcode, oi.icd9), ']'), ''))
                        SEPARATOR '; '
                    )
                    FROM operation_detail od
                    LEFT JOIN operation_item oi ON oi.operation_item_id = od.operation_item_id
                    WHERE od.operation_id = ol.operation_id
                ) AS procedures,
                -- พยาบาล Scrub
                (SELECT d.name 
                  FROM operation_team ot 
                 JOIN doctor d ON d.code = ot.doctor 
                 WHERE ot.operation_id = ol.operation_id AND ot.position_id = 5 
                 LIMIT 1) AS scrub_nurse,
                -- พยาบาล Circulate
                (SELECT d.name 
                 FROM operation_team ot 
                 JOIN doctor d ON d.code = ot.doctor 
                 WHERE ot.operation_id = ol.operation_id AND ot.position_id = 3 
                 LIMIT 1) AS circulate_nurse,
                -- วิธีระงับความรู้สึก (Anesthesia)
                COALESCE(
                    (SELECT t.name FROM operation_anes oa JOIN operation_anes_technique t ON t.technique_id = oa.technique_id WHERE oa.operation_id = ol.operation_id LIMIT 1),
                    (SELECT at.anes_name FROM operation_anes oa JOIN operation_anes_type at ON at.anes_type_id = oa.anes_type_id WHERE oa.operation_id = ol.operation_id LIMIT 1),
                    anes.operation_list_anes_type_name,
                    '-'
                ) AS anes_type,
                -- ระดับ ASA Class
                COALESCE(
                    ps.operation_anes_physical_status_name,
                    '-'
                ) AS asa_class,
                ol.blood_loss,
                ol.enter_time,
                ol.leave_time
            FROM operation_list ol
            LEFT JOIN patient p ON p.hn = ol.hn
            LEFT JOIN operation_room room ON room.room_id = ol.room_id
            LEFT JOIN operation_type otype ON otype.operation_type_id = ol.operation_type_id
            LEFT JOIN operation_emergency oem ON oem.emergency_id = ol.emergency_id
            LEFT JOIN operation_status ostat ON ostat.status_id = ol.status_id
            LEFT JOIN operation_list_anes_type anes ON anes.operation_list_anes_type_id = ol.operation_list_anes_type_id
            LEFT JOIN operation_anes_physical_status ps ON ps.operation_anes_physical_status_id = ol.operation_anes_physical_status_id
            WHERE ol.operation_date BETWEEN ? AND ?
            {$status_condition}
            ORDER BY ol.operation_date DESC, ol.operation_time DESC, ol.operation_id DESC
        ", [$start_date, $end_date]);

        // 4. Procedures Stats (Tab 2 - หัตถการ Operation เช่น MRM, Hernioplasty, Excision ฯลฯ)
        $procedures = DB::connection('hosxp')->select("
            SELECT 
                COALESCE(od.icdcode, oi.icd9, '-') AS icd9,
                COALESCE(oi.name, icd.name, od.clinical_term, ol.operation_name, 'ไม่ระบุชื่อหัตถการ') AS proc_name,
                COUNT(*) AS total_count,
                SUM(CASE WHEN ol.patient_department = 'IPD' THEN 1 ELSE 0 END) AS ipd_count,
                SUM(CASE WHEN ol.patient_department = 'OPD' THEN 1 ELSE 0 END) AS opd_count,
                SUM(CASE WHEN ol.emergency_id = 1 THEN 1 ELSE 0 END) AS emergency_count,
                SUM(CASE WHEN ol.emergency_id = 2 THEN 1 ELSE 0 END) AS elective_count
            FROM operation_detail od
            JOIN operation_list ol ON ol.operation_id = od.operation_id
            LEFT JOIN operation_item oi ON oi.operation_item_id = od.operation_item_id
            LEFT JOIN icd9cm1 icd ON icd.code = od.icdcode
            WHERE ol.operation_date BETWEEN ? AND ?
            {$status_condition}
            GROUP BY COALESCE(od.icdcode, oi.icd9, '-'), proc_name
            ORDER BY total_count DESC
            LIMIT 30
        ", [$start_date, $end_date]);

        // 5. Surgeons Stats (Tab 3 - แพทย์ผู้ผ่าตัด)
        $surgeons = DB::connection('hosxp')->select("
            SELECT 
                d.code AS doctor_code,
                d.name AS doctor_name,
                COALESCE(s.name, 'แพทย์ทั่วไป/ศัลยกรรม') AS spclty_name,
                COUNT(DISTINCT ol.operation_id) AS total_cases,
                SUM(CASE WHEN ol.emergency_id = 1 THEN 1 ELSE 0 END) AS emergency_cases,
                SUM(CASE WHEN ol.emergency_id = 2 THEN 1 ELSE 0 END) AS elective_cases,
                SUM(CASE WHEN ol.patient_department = 'IPD' THEN 1 ELSE 0 END) AS ipd_cases,
                SUM(CASE WHEN ol.patient_department = 'OPD' THEN 1 ELSE 0 END) AS opd_cases
            FROM operation_team ot
            JOIN operation_list ol ON ol.operation_id = ot.operation_id
            JOIN doctor d ON d.code = ot.doctor
            LEFT JOIN spclty s ON s.spclty = d.spclty
            WHERE ot.position_id = 1
              AND ol.operation_date BETWEEN ? AND ?
              {$status_condition}
            GROUP BY d.code, d.name, s.name
            ORDER BY total_cases DESC
        ", [$start_date, $end_date]);

        // 6. Department / Specialty Stats (Tab 4 - แยกแผนก: ศัลย์, Ortho, สูติ, ตา)
        $departments = DB::connection('hosxp')->select("
            SELECT 
                dept_name,
                COUNT(DISTINCT operation_id) AS total_cases,
                SUM(CASE WHEN patient_department = 'IPD' THEN 1 ELSE 0 END) AS ipd_cases,
                SUM(CASE WHEN patient_department = 'OPD' THEN 1 ELSE 0 END) AS opd_cases,
                SUM(CASE WHEN emergency_id = 1 THEN 1 ELSE 0 END) AS emergency_cases,
                SUM(CASE WHEN emergency_id = 2 THEN 1 ELSE 0 END) AS elective_cases
            FROM (
                SELECT 
                    ol.operation_id,
                    ol.patient_department,
                    ol.emergency_id,
                    CASE 
                        WHEN s.name = 'ศัลยกรรม' THEN 'ศัลยกรรม (ศัลย์)'
                        WHEN s.name = 'สูติกรรม' THEN 'สูติ-นรีเวช'
                        WHEN s.name = 'ศัลยกรรมออร์โธปิดิกส์' THEN 'ออร์โธปิดิกส์ (Ortho)'
                        WHEN s.name LIKE '%จักษุ%' THEN 'จักษุ (ตา)'
                        WHEN CAST(LEFT(COALESCE(od.icdcode, oi.icd9, '0'), 2) AS UNSIGNED) BETWEEN 8 AND 16 THEN 'จักษุ (ตา)'
                        WHEN CAST(LEFT(COALESCE(od.icdcode, oi.icd9, '0'), 2) AS UNSIGNED) BETWEEN 76 AND 84 THEN 'ออร์โธปิดิกส์ (Ortho)'
                        WHEN CAST(LEFT(COALESCE(od.icdcode, oi.icd9, '0'), 2) AS UNSIGNED) BETWEEN 65 AND 75 THEN 'สูติ-นรีเวช'
                        WHEN CAST(LEFT(COALESCE(od.icdcode, oi.icd9, '0'), 2) AS UNSIGNED) BETWEEN 42 AND 54 
                             OR CAST(LEFT(COALESCE(od.icdcode, oi.icd9, '0'), 2) AS UNSIGNED) IN (85, 86) THEN 'ศัลยกรรม (ศัลย์)'
                        WHEN s_doc.name IS NOT NULL THEN s_doc.name
                        ELSE 'ศัลยกรรม (ศัลย์)'
                    END AS dept_name
                FROM operation_list ol
                LEFT JOIN spclty s ON s.spclty = ol.spclty
                LEFT JOIN operation_team ot ON ot.operation_id = ol.operation_id AND ot.position_id = 1
                LEFT JOIN doctor d ON d.code = ot.doctor
                LEFT JOIN spclty s_doc ON s_doc.spclty = d.spclty
                LEFT JOIN operation_detail od ON od.operation_id = ol.operation_id
                LEFT JOIN operation_item oi ON oi.operation_item_id = od.operation_item_id
                WHERE ol.operation_date BETWEEN ? AND ?
                {$status_condition}
            ) AS dept_sub
            GROUP BY dept_name
            ORDER BY total_cases DESC
        ", [$start_date, $end_date]);

        // 7. Anesthesia & ASA Class Stats (Tab 5 - หัตถการวิสัญญี: GA, RA, TIVA, MAC & ASA Class)
        $anes_techniques = DB::connection('hosxp')->select("
            SELECT 
                anes_category,
                COUNT(DISTINCT operation_id) AS total_cases
            FROM (
                SELECT 
                    ol.operation_id,
                    CASE 
                        WHEN t.name LIKE '%GA%' OR at.anes_name LIKE '%General%' OR lat.operation_list_anes_type_name LIKE '%General%' THEN 'GA (General Anesthesia)'
                        WHEN t.name LIKE '%Spinal%' OR at.anes_name LIKE '%Regional%' OR lat.operation_list_anes_type_name LIKE '%Spinal%' OR lat.operation_list_anes_type_name LIKE '%Regional%' THEN 'RA (Regional Anesthesia)'
                        WHEN t.name LIKE '%TIVA%' OR at.anes_name LIKE '%TIVA%' THEN 'TIVA (Total Intravenous Anesthesia)'
                        WHEN t.name LIKE '%MAC%' OR lat.operation_list_anes_type_name LIKE '%Monitored%' THEN 'MAC (Monitored Anesthesia Care)'
                        WHEN at.anes_name LIKE '%Local%' OR lat.operation_list_anes_type_name LIKE '%Local%' THEN 'Local Anesthesia'
                        ELSE 'Local / อื่นๆ'
                    END AS anes_category
                FROM operation_list ol
                LEFT JOIN operation_anes oa ON oa.operation_id = ol.operation_id
                LEFT JOIN operation_anes_technique t ON t.technique_id = oa.technique_id
                LEFT JOIN operation_anes_type at ON at.anes_type_id = oa.anes_type_id
                LEFT JOIN operation_list_anes_type lat ON lat.operation_list_anes_type_id = ol.operation_list_anes_type_id
                WHERE ol.operation_date BETWEEN ? AND ?
                {$status_condition}
            ) AS anes_sub
            GROUP BY anes_category
            ORDER BY total_cases DESC
        ", [$start_date, $end_date]);

        $asa_classes = DB::connection('hosxp')->select("
            SELECT 
                COALESCE(ps.operation_anes_physical_status_name, 'ไม่ระบุ ASA Class') AS asa_class_name,
                COUNT(DISTINCT ol.operation_id) AS total_cases
            FROM operation_list ol
            LEFT JOIN operation_anes_physical_status ps ON ps.operation_anes_physical_status_id = ol.operation_anes_physical_status_id
            WHERE ol.operation_date BETWEEN ? AND ?
            {$status_condition}
            GROUP BY asa_class_name
            ORDER BY total_cases DESC
        ", [$start_date, $end_date]);

        // Anesthesia Billed Procedures from operation_anes_oper_list
        $anes_procedures = DB::connection('hosxp')->select("
            SELECT 
                oao.operation_anes_oper_name AS proc_name,
                COUNT(DISTINCT ol.operation_id) AS total_cases,
                SUM(ol_sub.qty) AS total_qty,
                SUM(ol_sub.total_price) AS total_amount
            FROM operation_anes_oper_list ol_sub
            JOIN operation_anes_oper oao ON oao.operation_anes_oper_id = ol_sub.operation_anes_oper_id
            JOIN operation_list ol ON ol.operation_id = ol_sub.operation_id
            WHERE ol.operation_date BETWEEN ? AND ?
            {$status_condition}
            GROUP BY oao.operation_anes_oper_name
            ORDER BY total_cases DESC
            LIMIT 15
        ", [$start_date, $end_date]);

        return view('hosxp.operation.index', compact(
            'title', 'budget_year_select', 'budget_year', 'start_date', 'end_date',
            'by_start_date', 'by_end_date', 'status', 'monthly_stats', 'summary',
            'patients', 'procedures', 'surgeons', 'departments',
            'anes_techniques', 'asa_classes', 'anes_procedures'
        ));
    }

    /**
     * Resolve Date Range and Budget Year.
     * Keeps annual budget year for the top monthly graph and calendar date range for patient list.
     */
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

        $budget_year = $request->budget_year ?: ($budget_year_now ?: (date('Y') + 543));

        // Date range of the chosen budget year (October 1 to September 30)
        $year_data = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->first();

        if ($year_data) {
            $by_start_date = $year_data->DATE_BEGIN;
            $by_end_date = $year_data->DATE_END;
        } else {
            $by_start_date = ($budget_year - 544) . '-10-01';
            $by_end_date = ($budget_year - 543) . '-09-30';
        }

        // Calendar date range for detailed patient list
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        } else {
            // Default to start and end of selected budget year
            $start_date = $by_start_date;
            $end_date = $by_end_date;
        }

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'budget_year' => $budget_year,
            'budget_year_select' => $budget_year_select,
            'by_start_date' => $by_start_date,
            'by_end_date' => $by_end_date
        ];
    }
}
