<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\MophNotify\ServiceController;
use App\Http\Controllers\MophNotify\ReplicationController;
use App\Http\Controllers\MophNotify\BackupController;
use App\Http\Controllers\MophNotify\AuditEmrController;
use Exception;

class MonitorController extends Controller
{
    /**
     * Show the monitoring dashboard.
     */
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // 1. Scheduler Heartbeat
        $schedulerLastRun = Cache::get('scheduler_last_run');
        $schedulerStatus = 'offline';
        if ($schedulerLastRun) {
            $diffInMinutes = now()->diffInMinutes(\Carbon\Carbon::parse($schedulerLastRun));
            if ($diffInMinutes <= 2) {
                $schedulerStatus = 'online';
            } else {
                $schedulerStatus = 'delayed';
            }
        }

        // 2. Database Connections
        $localDbStatus = 'offline';
        $localDbError = null;
        try {
            DB::connection()->getPdo();
            $localDbStatus = 'online';
        } catch (Exception $e) {
            $localDbError = $e->getMessage();
        }

        $hosxpDbStatus = 'offline';
        $hosxpDbError = null;
        try {
            DB::connection('hosxp')->getPdo();
            $hosxpDbStatus = 'online';
        } catch (Exception $e) {
            $hosxpDbError = $e->getMessage();
        }

        // 3. Read Laravel Log (last 50 lines)
        $logPath = storage_path('logs/laravel.log');
        $logLines = [];
        if (File::exists($logPath)) {
            $file = new \SplFileObject($logPath, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();
            
            // Read last 50 lines
            $startLine = max(0, $totalLines - 50);
            $file->seek($startLine);
            while (!$file->eof()) {
                $line = trim($file->current());
                if (!empty($line)) {
                    $logLines[] = $line;
                }
                $file->next();
            }
            $logLines = array_reverse($logLines);
        }

        // 4. Server Info
        $serverTime = now()->toDateTimeString();
        $osName = PHP_OS;

        return view('admin.monitor', compact(
            'schedulerLastRun',
            'schedulerStatus',
            'localDbStatus',
            'localDbError',
            'hosxpDbStatus',
            'hosxpDbError',
            'logLines',
            'serverTime',
            'osName'
        ));
    }

    /**
     * Run a scheduled task manually.
     */
    public function runTask(Request $request, $task)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $output = '';
            switch ($task) {
                case 'service_night':
                    $result = app(ServiceController::class)->service_night();
                    $output = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE);
                    break;
                case 'service_morning':
                    $result = app(ServiceController::class)->service_morning();
                    $output = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE);
                    break;
                case 'service_afternoon':
                    $result = app(ServiceController::class)->service_afternoon();
                    $output = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE);
                    break;
                case 'replication':
                    $result = app(ReplicationController::class)->check($request);
                    $output = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE);
                    break;
                case 'backup_hosxp':
                    $result = app(BackupController::class)->check($request);
                    $output = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE);
                    break;
                case 'audit_emr':
                    $result = app(AuditEmrController::class)->check($request);
                    $output = is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE);
                    break;
                case 'test_heartbeat':
                    Cache::put('scheduler_last_run', now()->toDateTimeString(), now()->addDays(7));
                    $output = 'Heartbeat updated to: ' . now()->toDateTimeString();
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid task specified'], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'รันงานสำเร็จแล้ว',
                'output' => $output
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการรันงาน: ' . $e->getMessage()
            ], 500);
        }
    }
}
