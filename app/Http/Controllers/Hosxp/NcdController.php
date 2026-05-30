<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NcdController extends Controller
{
    public function index(Request $request)
    {
        $title = 'งานคลินิกโรคเรื้อรัง (NCD)';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        return view('hosxp.ncd.index', compact('title', 'budget_year_select', 'budget_year', 'start_date', 'end_date'));
    }

    /**
     * ทะเบียนผู้ป่วยคลินิกเบาหวาน (clinic = '001')
     */
    public function dm_register(Request $request)
    {
        $title = 'ทะเบียนผู้ป่วยคลินิกเบาหวาน';
        $dates = $this->resolveDateRange($request);
        $start_date = $dates['start_date'];
        $end_date   = $dates['end_date'];
        $budget_year = $dates['budget_year'];
        $budget_year_select = $dates['budget_year_select'];

        // 1. สรุปจำนวนผู้ป่วยแยกตามสถานะ (clinic_member_status_name)
        $status_summary = DB::connection('hosxp')->select("
            SELECT clinic_member_status_name, COUNT(hn) AS total
            FROM (
                SELECT c.hn, cm.clinic_member_status_name, c.clinic_member_status_id
                FROM clinicmember c
                LEFT OUTER JOIN clinic_member_status cm ON cm.clinic_member_status_id = c.clinic_member_status_id
                WHERE c.clinic = '001'
                GROUP BY c.hn
            ) AS a
            GROUP BY clinic_member_status_id
            ORDER BY total DESC
        ");

        // 2. จำนวนผู้ป่วยรายใหม่แยกรายเดือน (regdate ในช่วงที่เลือก)
        $new_by_month = DB::connection('hosxp')->select("
            SELECT
                CASE
                    WHEN MONTH(regdate)=10 THEN CONCAT('ต.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=11 THEN CONCAT('พ.ย. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=12 THEN CONCAT('ธ.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=1  THEN CONCAT('ม.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=2  THEN CONCAT('ก.พ. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=3  THEN CONCAT('มี.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=4  THEN CONCAT('เม.ย. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=5  THEN CONCAT('พ.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=6  THEN CONCAT('มิ.ย. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=7  THEN CONCAT('ก.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=8  THEN CONCAT('ส.ค. ', RIGHT(YEAR(regdate)+543,2))
                    WHEN MONTH(regdate)=9  THEN CONCAT('ก.ย. ', RIGHT(YEAR(regdate)+543,2))
                END AS month_name,
                COUNT(hn) AS total,
                YEAR(regdate) AS y,
                MONTH(regdate) AS m
            FROM clinicmember
            WHERE clinic = '001'
              AND regdate BETWEEN ? AND ?
            GROUP BY y, m, month_name
            ORDER BY y, m
        ", [$start_date, $end_date]);

        // 3. รายชื่อผู้ป่วยทั้งหมด
        $patients = DB::connection('hosxp')->select("
            SELECT
                n.NAME AS clinic_name,
                p.cid,
                c.hn,
                CONCAT(p.pname, p.fname, SPACE(1), p.lname) AS patient_name,
                c.regdate,
                c.lastvisit,
                c.last_hba1c_date,
                c.last_hba1c_value,
                c.last_ua_date,
                c.last_ua_value,
                c.last_bp_date,
                CONCAT(c.last_bp_bps_value, '/', c.last_bp_bpd_value) AS last_bp_value,
                c.last_fbs_date,
                c.last_fbs_value,
                y.NAME AS pttype_name,
                s.NAME AS sex_name,
                d.NAME AS doctor_name,
                cm.clinic_member_status_name,
                c.clinic_member_status_id,
                CONCAT(ph.hosptype, SPACE(1), ph.NAME) AS send_pcu_hospital_name
            FROM clinicmember c
            LEFT OUTER JOIN patient p ON p.hn = c.hn
            LEFT OUTER JOIN clinic n ON n.clinic = c.clinic
            LEFT OUTER JOIN pttype y ON y.pttype = p.pttype
            LEFT OUTER JOIN sex s ON s.CODE = p.sex
            LEFT OUTER JOIN doctor d ON d.CODE = c.doctor
            LEFT OUTER JOIN clinic_member_status cm ON cm.clinic_member_status_id = c.clinic_member_status_id
            LEFT OUTER JOIN hospcode ph ON ph.hospcode = c.send_to_pcu_hcode
            WHERE c.clinic = '001'
            GROUP BY c.hn
            ORDER BY c.pt_number, c.regdate
        ");

        return view('hosxp.ncd.dm_register', compact(
            'title',
            'budget_year_select',
            'budget_year',
            'start_date',
            'end_date',
            'status_summary',
            'new_by_month',
            'patients'
        ));
    }

    private function resolveDateRange(Request $request)
    {
        $budget_year_select = DB::table('budget_year')->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')->orderByDesc('LEAVE_YEAR_ID')->limit(7)->get();
        $budget_year_now = DB::table('budget_year')->whereDate('DATE_END', '>=', date('Y-m-d'))->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))->value('LEAVE_YEAR_ID');
        $budget_year = $request->budget_year ?: $budget_year_now;

        if ($request->start_date && $request->end_date && $request->budget_year_changed != '1') {
            $start_date = $request->start_date;
            $end_date   = $request->end_date;
            $matched_year = DB::table('budget_year')
                ->where('DATE_BEGIN', '<=', $start_date)
                ->where('DATE_END', '>=', $start_date)
                ->value('LEAVE_YEAR_ID');
            if ($matched_year) {
                $budget_year = $matched_year;
            }
        } else {
            $year_data = DB::table('budget_year')->where('LEAVE_YEAR_ID', $budget_year)->first();
            if ($year_data) {
                $start_date = $year_data->DATE_BEGIN;
                $end_date   = $year_data->DATE_END;
            } else {
                $start_date = ($budget_year - 543) . '-10-01';
                $end_date   = ($budget_year - 542) . '-09-30';
            }
        }

        return ['start_date' => $start_date, 'end_date' => $end_date, 'budget_year' => $budget_year, 'budget_year_select' => $budget_year_select];
    }
}
