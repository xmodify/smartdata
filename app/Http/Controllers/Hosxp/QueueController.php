<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * หน้าแสดงสถานะคิวสำหรับคนไข้ (สแกน QR Code)
     */
    public function showStatus($vn)
    {
        $patientQueue = $this->getQueueData($vn);

        if (!$patientQueue) {
            return view('hosxp.queue.not_found', compact('vn'));
        }

        return view('hosxp.queue.status', compact('patientQueue'));
    }

    /**
     * API สำหรับดึงข้อมูลอัปเดต Real-time (AJAX/Fetch)
     */
    public function getStatusApi($vn)
    {
        $patientQueue = $this->getQueueData($vn);

        if (!$patientQueue) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลคิวสำหรับ VN นี้'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $patientQueue
        ]);
    }

    /**
     * Helper สำหรับดึงข้อมูลคิวของคนไข้และสถานะคิวในแผนก
     */
    private function getQueueData($vn)
    {
        // 1. ข้อมูลการ Visit และ คิวของคนไข้
        $patient = DB::connection('hosxp')->table('ovst')
            ->leftJoin('patient', 'ovst.hn', '=', 'patient.hn')
            ->leftJoin('kskdepartment as cur_k', 'cur_k.depcode', '=', 'ovst.cur_dep')
            ->leftJoin('kskdepartment as main_k', 'main_k.depcode', '=', 'ovst.main_dep')
            ->leftJoin('opd_qs_slot', 'ovst.vn', '=', 'opd_qs_slot.vn')
            ->select(
                'ovst.vn',
                'ovst.hn',
                'ovst.vstdate',
                'ovst.vsttime',
                'ovst.cur_dep',
                'ovst.ovstost',
                'cur_k.department as cur_dep_name',
                'main_k.department as main_dep_name',
                'ovst.oqueue as ovst_queue',
                'opd_qs_slot.queue_slot_number',
                DB::raw("CONCAT(patient.pname, patient.fname, ' ', IF(LENGTH(patient.lname) > 2, CONCAT(SUBSTRING(patient.lname, 1, 2), '***'), patient.lname)) as patient_name_masked")
            )
            ->where('ovst.vn', $vn)
            ->first();

        if (!$patient) {
            return null;
        }

        // กำหนดเลขคิวที่จะใช้แสดงผล
        $myQueueNumber = $patient->queue_slot_number ?? $patient->ovst_queue;

        // เช็คว่าบริการเสร็จสิ้นแล้วหรือไม่ (ovstost = '99' คือรับบริการเสร็จสิ้นแล้ว)
        $isFinished = ($patient->ovstost == '99');

        // 2. คิวล่าสุดที่กำลังกดเรียกในแผนกนี้
        $currentCalling = DB::connection('hosxp')->table('opd_qs_room_sub_queue')
            ->where('opd_qs_room_s_queue_start_date', $patient->vstdate)
            ->where('cur_dep', $patient->cur_dep)
            ->orderBy('opd_qs_room_s_queue_id', 'desc')
            ->value('queue_slot_number');

        if (!$currentCalling) {
            $currentCalling = DB::connection('hosxp')->table('opd_qs_slot')
                ->join('ovst', 'ovst.vn', '=', 'opd_qs_slot.vn')
                ->where('ovst.cur_dep', $patient->cur_dep)
                ->where('ovst.vstdate', $patient->vstdate)
                ->where('opd_qs_slot.queue_slot_number', '<', $myQueueNumber)
                ->max('opd_qs_slot.queue_slot_number') ?? '-';
        }

        // 3. คำนวณจำนวนคิวก่อนหน้า
        $waitingAheadCount = 0;
        if (!$isFinished) {
            if ($patient->queue_slot_number) {
                $waitingAheadCount = DB::connection('hosxp')->table('opd_qs_slot')
                    ->join('ovst', 'ovst.vn', '=', 'opd_qs_slot.vn')
                    ->where('ovst.cur_dep', $patient->cur_dep)
                    ->where('ovst.vstdate', $patient->vstdate)
                    ->where('opd_qs_slot.queue_slot_number', '<', $patient->queue_slot_number)
                    ->count();
            } else {
                $waitingAheadCount = DB::connection('hosxp')->table('ovst')
                    ->where('cur_dep', $patient->cur_dep)
                    ->where('vstdate', $patient->vstdate)
                    ->where('oqueue', '<', $patient->ovst_queue)
                    ->count();
            }
        }

        // ประเมินเวลารอโดยประมาณ (คิวละประมาณ 5 นาที)
        $estimatedMinutes = $isFinished ? 0 : ($waitingAheadCount * 5);
        $depName = $isFinished ? 'รับบริการเสร็จสิ้นแล้ว' : ($patient->cur_dep_name ?? 'แผนกผู้ป่วยนอก');


        return [
            'vn' => $patient->vn,
            'hn' => $patient->hn,
            'vstdate' => $patient->vstdate,
            'vsttime' => $patient->vsttime,
            'patient_name' => $patient->patient_name_masked,
            'department_name' => $depName,
            'main_department_name' => $patient->main_dep_name ?? ($patient->cur_dep_name ?? 'แผนกผู้ป่วยนอก'),
            'my_queue' => $myQueueNumber,
            'is_finished' => $isFinished,
            'current_calling_queue' => $currentCalling,

            'waiting_ahead' => $waitingAheadCount,
            'estimated_minutes' => $estimatedMinutes,
            'updated_at' => date('H:i:s')
        ];
    }
}
