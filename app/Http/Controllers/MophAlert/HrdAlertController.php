<?php

namespace App\Http\Controllers\MophAlert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MophAlert;
use App\Models\MophAlertDetail;
use App\Services\MophAlertService;

class HrdAlertController extends Controller
{
    public function index(Request $request)
    {
        // Permission check
        if (auth()->user()->role !== 'admin' && auth()->user()->allow_moph_alert !== 'Y') {
            abort(403);
        }

        // Fetch Alert settings to check if Moph Alert is active
        $mophAlert = MophAlert::find(1); // ID 1 is "ระบบประชาสัมพันธ์ รพ.หัวตะพาน"

        // Handle Filters (Department)
        $dept_ids = $request->dept_ids ?: [];

        // Fetch Only Departments for Filter
        try {
            $depts = DB::connection('backoffice')->select('
                SELECT DISTINCT hrds.HR_DEPARTMENT_SUB_SUB_ID, hrds.HR_DEPARTMENT_SUB_SUB_NAME, hds.HR_DEPARTMENT_ID
                FROM hrd_person hr
                INNER JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID = hr.HR_DEPARTMENT_SUB_SUB_ID
                LEFT JOIN hrd_department_sub hds ON hds.HR_DEPARTMENT_SUB_ID = hrds.HR_DEPARTMENT_SUB_ID
                WHERE hr.HR_STATUS_ID = 1
                ORDER BY hds.HR_DEPARTMENT_ID, hrds.HR_DEPARTMENT_SUB_SUB_NAME
            ');
        } catch (\Exception $e) {
            $depts = [];
        }

        // Build Where Clause for Departments
        $where_dept = "";
        $dept_params = [];
        if (!empty($dept_ids)) {
            $placeholders = implode(',', array_fill(0, count($dept_ids), '?'));
            $where_dept = " AND hrds.HR_DEPARTMENT_SUB_SUB_ID IN ($placeholders) ";
            $dept_params = $dept_ids;
        }

        // Fetch active hospital staff from Backoffice
        try {
            $staffList = DB::connection('backoffice')->select('
                SELECT hr.HR_CID as cid, hrp.HR_PREFIX_NAME as prefix, hr.HR_FNAME as fname, hr.HR_LNAME as lname,
                hr.POSITION_IN_WORK as position, hrds.HR_DEPARTMENT_SUB_SUB_NAME as department
                FROM hrd_person hr
                LEFT JOIN hrd_prefix hrp ON hrp.HR_PREFIX_ID=hr.HR_PREFIX_ID
                LEFT JOIN hrd_department_sub_sub hrds ON hrds.HR_DEPARTMENT_SUB_SUB_ID=hr.HR_DEPARTMENT_SUB_SUB_ID
                WHERE hr.HR_STATUS_ID = 1 ' . $where_dept . '
                ORDER BY hr.HR_FNAME, hr.HR_LNAME
            ', $dept_params);
        } catch (\Exception $e) {
            $staffList = [];
        }

        // Get selected department names for display
        $selected_dept_names = [];
        if (!empty($dept_ids)) {
            foreach ($depts as $dept) {
                if (in_array($dept->HR_DEPARTMENT_SUB_SUB_ID, $dept_ids)) {
                    $selected_dept_names[] = $dept->HR_DEPARTMENT_SUB_SUB_NAME;
                }
            }
        }

        // Fetch send history logs
        $history = MophAlertDetail::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('mophalert.hrd', compact('mophAlert', 'staffList', 'depts', 'dept_ids', 'selected_dept_names', 'history'));
    }

    public function send(Request $request)
    {
        if (auth()->user()->role !== 'admin' && auth()->user()->allow_moph_alert !== 'Y') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'cids'   => 'required|array',
            'cids.*' => 'required|string|max:20',
            'title'  => 'required|string|max:255',
            'html'   => 'required|string',
        ]);

        $cids = $request->cids;
        $title = $request->title;
        $html = $request->html;

        // Extract plain text from HTML to include in the Line chat bubble notification
        $plainDetail = trim(strip_tags($html));
        // Remove potential duplicate title/footer components from Javascript auto-formatting
        $plainDetail = preg_replace('/📣\s*.*?\n/u', '', $plainDetail);
        $plainDetail = preg_replace('/📢\s*.*?\n/u', '', $plainDetail);
        $plainDetail = str_replace('โรงพยาบาลหัวตะพาน', '', $plainDetail);
        // Replace newlines and multiple spaces with a single space to prevent manual line wrapping
        $plainDetail = str_replace(["\r", "\n"], " ", $plainDetail);
        $plainDetail = preg_replace('/\s+/', ' ', $plainDetail);
        $plainDetail = trim($plainDetail);

        // Combine into the requested format using non-breaking space (\u{00A0}) after colons
        // to prevent Line from wrapping text right after the colons.
        $nbsp = "\u{00A0}";
        $bubbleText = "📢" . $nbsp . "ประชาสัมพันธ์จาก รพ.หัวตะพาน\n\n📌" . $nbsp . "เรื่อง:" . $nbsp . trim($title);
        if (!empty($plainDetail)) {
            $bubbleText .= "\n📝" . $nbsp . "รายละเอียด:" . $nbsp . $plainDetail;
        }

        // ID 1 is "ระบบประชาสัมพันธ์ รพ.หัวตะพาน"
        $result = MophAlertService::sendFreeForm($cids, $title, $bubbleText, $html, 1);

        // Save log to moph_alert_detail
        MophAlertDetail::create([
            'moph_alert_id' => 1,
            'user_id' => auth()->id(),
            'title' => $title,
            'message_text' => $bubbleText,
            'message_html' => $html,
            'recipient_count' => count($cids),
            'recipients' => $cids,
            'status' => $result ? 'success' : 'failed',
            'response_message' => $result ? 'Sent successfully' : 'Failed to call MOPH Alert API',
        ]);

        if ($result) {
            return response()->json([
                'success' => true, 
                'message' => 'ส่งแจ้งเตือนสำเร็จไปยังเจ้าหน้าที่จำนวน ' . count($cids) . ' คน!',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => 'ส่งแจ้งเตือนล้มเหลว กรุณาตรวจสอบการตั้งค่า Moph Alert ในระบบหลังบ้าน'
        ]);
    }
}
