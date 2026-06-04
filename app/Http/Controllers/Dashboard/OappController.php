<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OappController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ?: date('Y-m-d', strtotime('+1 day'));

        $results = DB::connection('hosxp')->select('
            SELECT c.`name` AS clinic, d.`name` AS doctor, COUNT(o.vn) AS oapp 
            FROM oapp o
            LEFT JOIN clinic c ON c.clinic = o.clinic
            LEFT JOIN doctor d ON d.`code` = o.doctor
            WHERE o.nextdate = ?
            GROUP BY o.clinic, o.doctor, c.`name`, d.`name`
            ORDER BY oapp DESC
        ', [$date]);

        $clinics = [];
        foreach ($results as $row) {
            $clinicName = $row->clinic ?: 'ไม่ระบุคลินิก';
            $doctorName = $row->doctor ?: 'ไม่ระบุแพทย์';
            if (!isset($clinics[$clinicName])) {
                $clinics[$clinicName] = [
                    'total' => 0,
                    'doctors' => []
                ];
            }
            $clinics[$clinicName]['doctors'][] = [
                'name' => $doctorName,
                'count' => $row->oapp
            ];
            $clinics[$clinicName]['total'] += $row->oapp;
        }

        return view('dashboard.oapp', compact('clinics', 'date'));
    }
}
