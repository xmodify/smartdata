<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class HrdController extends Controller
{
    public function index(Request $request)
    {
        // Handle Filters
        $start_date = $request->start_date ?: date('Y-m-01');
        $end_date = $request->end_date ?: date('Y-m-t');
        $dept_ids = $request->dept_ids ?: [];

        // Store in Session
        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        // Fetch Only Departments for Filter
        $depts = DB::connection('backoffice')->select('
            SELECT DISTINCT hrds.HR_DEPARTMENT_SUB_SUB_ID, hrds.HR_DEPARTMENT_SUB_SUB_NAME, hds.HR_DEPARTMENT_ID
            FROM hrd_person hr
            INNER JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID = hr.HR_DEPARTMENT_SUB_SUB_ID
            LEFT JOIN hrd_department_sub hds ON hds.HR_DEPARTMENT_SUB_ID = hrds.HR_DEPARTMENT_SUB_ID
            WHERE hr.HR_STATUS_ID = 1
            ORDER BY hds.HR_DEPARTMENT_ID, hrds.HR_DEPARTMENT_SUB_SUB_NAME
        ');

        // Build Where Clause for Departments
        $where_dept = "";
        $dept_params = [];
        if (!empty($dept_ids)) {
            $placeholders = implode(',', array_fill(0, count($dept_ids), '?'));
            $where_dept = " AND hrds.HR_DEPARTMENT_SUB_SUB_ID IN ($placeholders) ";
            $dept_params = $dept_ids;
        }

        // Build Personnel Query (Filtered by Depts)
        $params = array_merge([$start_date, $end_date], $dept_params);
        $persons = DB::connection('backoffice')->select('
            SELECT hr.id,hr.FINGLE_ID,hr.HR_CID,hrp.HR_PREFIX_NAME,hr.HR_FNAME,hr.HR_LNAME,hr.HR_EN_NAME,hr.SEX,hr.HR_BIRTHDAY,hr.HR_PHONE,hr.HR_EMAIL,hrt.HR_PERSON_TYPE_ID,hrt.HR_PERSON_TYPE_NAME,hr.POSITION_IN_WORK,
            hr.HR_STATUS_ID,hrs.HR_STATUS_NAME,hr.HR_DEPARTMENT_ID,hrd.HR_DEPARTMENT_NAME,hrds.HR_DEPARTMENT_SUB_SUB_ID,
            hrds.HR_DEPARTMENT_SUB_SUB_NAME,
            COALESCE(work_stats.day_count, 0) as day_count,
            COALESCE(work_stats.shift_count, 0) as shift_count
            FROM hrd_person hr
            LEFT JOIN hrd_prefix hrp ON hrp.HR_PREFIX_ID=hr.HR_PREFIX_ID
            LEFT JOIN hrd_status hrs ON hrs.HR_STATUS_ID=hr.HR_STATUS_ID
            LEFT JOIN hrd_person_type hrt ON hrt.HR_PERSON_TYPE_ID=hr.HR_PERSON_TYPE_ID
            LEFT JOIN hrd_department hrd ON hrd.HR_DEPARTMENT_ID=hr.HR_DEPARTMENT_ID
            LEFT JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID=hr.HR_DEPARTMENT_SUB_SUB_ID
            LEFT JOIN (
                SELECT USER_ID, 
                       COUNT(DISTINCT SHIFT_DATE) as day_count,
                       SUM(CASE WHEN SHIFT_ID IS NOT NULL AND SHIFT_ID != "" THEN 1 ELSE 0 END) as shift_count
                FROM checkin_shift_summary
                WHERE SHIFT_DATE BETWEEN ? AND ?
                GROUP BY USER_ID
            ) work_stats ON work_stats.USER_ID = hr.id
            WHERE hr.HR_STATUS_ID = 1 ' . $where_dept . '
            ORDER BY hrt.HR_PERSON_TYPE_ID, hr.HR_FNAME, hr.HR_LNAME
        ', $params);

        // Stats for Charts (Always Total, No Dept Filter)
        $statsType = DB::connection('backoffice')->select('
            SELECT hrt.HR_PERSON_TYPE_NAME, COUNT(hr.id) as total
            FROM hrd_person hr
            LEFT JOIN hrd_person_type hrt ON hrt.HR_PERSON_TYPE_ID=hr.HR_PERSON_TYPE_ID
            WHERE hr.HR_STATUS_ID = 1
            GROUP BY hrt.HR_PERSON_TYPE_NAME
        ');

        $statsPosition = DB::connection('backoffice')->select('
            SELECT hr.POSITION_IN_WORK, COUNT(hr.id) as total
            FROM hrd_person hr
            WHERE hr.HR_STATUS_ID = 1
            GROUP BY hr.POSITION_IN_WORK
            ORDER BY total DESC
            LIMIT 10
        ');

        $chartData = [
            'type_labels' => array_column($statsType, 'HR_PERSON_TYPE_NAME'),
            'type_values' => array_column($statsType, 'total'),
            'pos_labels' => array_column($statsPosition, 'POSITION_IN_WORK'),
            'pos_values' => array_column($statsPosition, 'total'),
        ];

        // Summary Counts
        $total_all = array_sum(array_column($statsType, 'total'));
        $total_perm = 0;
        foreach($statsType as $row) {
            if($row->HR_PERSON_TYPE_NAME == 'ข้าราชการ') {
                $total_perm = $row->total;
                break;
            }
        }
        $total_other = $total_all - $total_perm;

        return view('backoffice.hrd.index', compact(
            'persons', 'chartData', 'start_date', 'end_date', 'dept_ids', 'depts',
            'total_all', 'total_perm', 'total_other'
        ));
    }

    public function checkin_indiv_pdf(Request $request, $id)
    {
        $start_date = Session::get('start_date');
        $end_date = Session::get('end_date');
        
        // 1. Fetch Person Details first to ensure header info is always available
        $person = DB::connection('backoffice')->selectOne('
            SELECT p.ID, CONCAT(pn.HR_PREFIX_NAME, p.HR_FNAME, SPACE(1), p.HR_LNAME) AS ptname,
            p.HR_PHONE, pp.HR_POSITION_NAME, hd.HR_DEPARTMENT_SUB_SUB_NAME, pt.HR_PERSON_TYPE_NAME
            FROM hrd_person p
            LEFT JOIN hrd_prefix pn ON pn.HR_PREFIX_ID=p.HR_PREFIX_ID
            LEFT JOIN hrd_position pp ON pp.HR_POSITION_ID=p.HR_POSITION_ID
            LEFT JOIN hrd_person_type pt ON pt.HR_PERSON_TYPE_ID=p.HR_PERSON_TYPE_ID 
            LEFT JOIN hrd_department_sub_sub hd ON hd.HR_DEPARTMENT_SUB_SUB_ID=p.HR_DEPARTMENT_SUB_SUB_ID
            WHERE p.ID = ?', [$id]);

        if (!$person) {
            return "ไม่พบข้อมูลบุคลากร";
        }

        $type_name = $person->HR_PERSON_TYPE_NAME;
        $ptname = $person->ptname;
        $phone = $person->HR_PHONE;
        $position_name = $person->HR_POSITION_NAME;
        $depart = $person->HR_DEPARTMENT_SUB_SUB_NAME;

        // 2. Fetch Check-in Logs
        $checkin_indiv = DB::connection('backoffice')->select('
            SELECT cs.SHIFT_DATE,
            IF(o.OPERATE_JOB_NAME IS NULL,"ไม่มีเวร",o.OPERATE_JOB_NAME) AS shift,
            TIME(cs.SCAN_START_DATETIME) AS scan_start, TIME(cs.SCAN_END_DATETIME) AS scan_end,
            p.ID, CONCAT(pn.HR_PREFIX_NAME, p.HR_FNAME, SPACE(1), p.HR_LNAME) AS ptname
            FROM hrd_person p
            LEFT JOIN hrd_prefix pn ON pn.HR_PREFIX_ID=p.HR_PREFIX_ID
            INNER JOIN checkin_shift_summary cs ON cs.USER_ID=p.ID
            LEFT JOIN operate_job o ON o.OPERATE_JOB_ID=cs.SHIFT_ID
            WHERE p.ID = ? AND cs.SHIFT_DATE BETWEEN ? AND ?
            GROUP BY cs.SCAN_START_DATETIME, cs.SCAN_END_DATETIME 
            ORDER BY cs.SHIFT_DATE, scan_start', [$id, $start_date, $end_date]);

        $pdf = Pdf::loadView('backoffice.hrd.checkin_indiv_pdf', compact('type_name', 'ptname', 'phone', 'position_name',
            'depart', 'checkin_indiv', 'start_date', 'end_date'))
            ->setPaper('A4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->stream();
    }

    public function checkin_indiv_detail_pdf(Request $request, $id)
    {
        $start_date = Session::get('start_date');
        $end_date = Session::get('end_date');
        
        // 1. Fetch Person Details first
        $person = DB::connection('backoffice')->selectOne('
            SELECT p.ID, CONCAT(pn.HR_PREFIX_NAME, p.HR_FNAME, SPACE(1), p.HR_LNAME) AS ptname,
            pp.HR_POSITION_NAME, hd.HR_DEPARTMENT_SUB_SUB_NAME
            FROM hrd_person p
            LEFT JOIN hrd_prefix pn ON pn.HR_PREFIX_ID=p.HR_PREFIX_ID
            LEFT JOIN hrd_position pp ON pp.HR_POSITION_ID=p.HR_POSITION_ID
            LEFT JOIN hrd_department_sub_sub hd ON hd.HR_DEPARTMENT_SUB_SUB_ID=p.HR_DEPARTMENT_SUB_SUB_ID
            WHERE p.ID = ?', [$id]);

        if (!$person) {
            return "ไม่พบข้อมูลบุคลากร";
        }

        $ptname = $person->ptname;
        $position_name = $person->HR_POSITION_NAME;
        $depart = $person->HR_DEPARTMENT_SUB_SUB_NAME;

        // 2. Fetch Detailed Check-in Logs
        $checkin_indiv = DB::connection('backoffice')->select('
            SELECT cd.`name` AS device,
            DATE(c.time_attendance) AS c_date, TIME(c.time_attendance) AS c_time 
            FROM checkin_device_time_attendance c
            LEFT JOIN checkin_device_setting cd ON cd.id=c.device_id
            LEFT JOIN map_user_hr_scan m ON m.HR_SCAN_ID=c.user_id 
            WHERE m.PERSON_ID = ? AND DATE(c.time_attendance) BETWEEN ? AND ?
            ORDER BY c.time_attendance', [$id, $start_date, $end_date]);

        $pdf = Pdf::loadView('backoffice.hrd.checkin_indiv_detail_pdf', compact('ptname', 'position_name',
            'depart', 'checkin_indiv', 'start_date', 'end_date'))
            ->setPaper('A4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isFontSubsettingEnabled', true);

        return $pdf->stream();
    }

    private function formatThaiDate($date, $format = 'full')
    {
        if (!$date) return '-';
        $carbon = Carbon::parse($date);
        $thaiMonths = [
            '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม', '04' => 'เมษายน',
            '05' => 'พฤษภาคม', '06' => 'มิถุนายน', '07' => 'กรกฎาคม', '08' => 'สิงหาคม',
            '09' => 'กันยายน', '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
        ];
        $thaiMonthsShort = [
            '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
            '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
            '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
        ];

        $year = $carbon->year + 543;
        $month = $carbon->format('m');
        $day = $carbon->day;

        if ($format == 'short') {
            return $day . ' ' . $thaiMonthsShort[$month] . ' ' . $year;
        }

        return $day . ' ' . $thaiMonths[$month] . ' พ.ศ. ' . $year;
    }
}
