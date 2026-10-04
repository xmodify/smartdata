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
use App\Services\DatabaseBackupService;
use Exception;

class MonitorController extends Controller
{
    /**
     * Show the monitoring dashboard.
     */
    public function index(DatabaseBackupService $backupService)
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

        // 3. Database Backup Files
        $backupFiles = $backupService->getBackupFiles();

        // 4. Read Laravel Log (last 60 lines)
        $logPath = storage_path('logs/laravel.log');
        $logLines = $this->readTailLines($logPath, 60);

        // 5. Read Backup Log (last 60 lines)
        $backupLogPath = storage_path('logs/backup_schedule.log');
        $backupLogLines = $this->readTailLines($backupLogPath, 60);

        // 6. Hospital Code for Security Code generation
        try {
            $hospcode = DB::table('lookup_hospcode')->value('hospcode') ?? '10989';
        } catch (\Throwable $e) {
            $hospcode = '10989';
        }

        // 7. Server Info
        $serverTime = now()->toDateTimeString();
        $osName = PHP_OS;
        $phpVersion = phpversion();

        return view('admin.monitor', compact(
            'schedulerLastRun',
            'schedulerStatus',
            'localDbStatus',
            'localDbError',
            'hosxpDbStatus',
            'hosxpDbError',
            'backupFiles',
            'logLines',
            'backupLogLines',
            'hospcode',
            'serverTime',
            'osName',
            'phpVersion'
        ));
    }

    /**
     * Run manual database backup.
     */
    public function backupRun(DatabaseBackupService $backupService)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $result = $backupService->createBackup();

            return response()->json([
                'success' => true,
                'message' => "สำรองฐานข้อมูลสำเร็จเรียบร้อย! ({$result['file_size_formatted']})",
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'การสำรองฐานข้อมูลล้มเหลว: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore database from selected backup file.
     */
    public function backupRestore(Request $request, string $filename, DatabaseBackupService $backupService)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!preg_match('/^backup_[a-zA-Z0-9_\-]+\.sql\.gz$/', $filename)) {
            return response()->json(['success' => false, 'message' => 'รูปแบบชื่อไฟล์ไม่ถูกต้อง'], 400);
        }

        try {
            $hcode = DB::table('lookup_hospcode')->value('hospcode') ?? '10989';
        } catch (\Throwable $e) {
            $hcode = '10989';
        }

        $securityCode = trim($request->input('security_code', ''));

        // Generate allowed codes with +-2 minutes tolerance
        $validCodes = [];
        for ($i = -2; $i <= 2; $i++) {
            $validCodes[] = $hcode . '-' . now()->addMinutes($i)->format('Hi');
        }

        if (empty($securityCode) || !in_array($securityCode, $validCodes)) {
            return response()->json([
                'success' => false,
                'message' => "รหัสยืนยันความปลอดภัยไม่ถูกต้อง กรุณากรอกรหัสตามรูปแบบ {$hcode}-HHmm (เช่น {$hcode}-" . now()->format('Hi') . ")"
            ], 422);
        }

        try {
            $result = $backupService->restoreBackup($filename);

            return response()->json([
                'success' => true,
                'message' => "กู้คืนฐานข้อมูลจากไฟล์ {$filename} เสร็จสมบูรณ์แล้ว! (ใช้เวลา {$result['duration_seconds']} วินาที)",
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'การกู้คืนฐานข้อมูลล้มเหลว: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file (.sql.gz).
     */
    public function backupDownload(string $filename, DatabaseBackupService $backupService)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        if (!preg_match('/^backup_[a-zA-Z0-9_\-]+\.sql\.gz$/', $filename)) {
            abort(400, 'Invalid filename format');
        }

        $filePath = storage_path('app/backups/' . $filename);
        if (!File::exists($filePath)) {
            abort(404, 'Backup file not found');
        }

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/gzip',
        ]);
    }

    /**
     * Delete backup file.
     */
    public function backupDelete(string $filename, DatabaseBackupService $backupService)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!preg_match('/^backup_[a-zA-Z0-9_\-]+\.sql\.gz$/', $filename)) {
            return response()->json(['success' => false, 'message' => 'รูปแบบชื่อไฟล์ไม่ถูกต้อง'], 400);
        }

        $deleted = $backupService->deleteBackup($filename);
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => "ลบไฟล์สำรอง {$filename} สำเร็จแล้ว"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'ไม่พบไฟล์ที่ต้องการลบหรือลบไม่สำเร็จ'
        ], 404);
    }

    /**
     * Clear application log file.
     */
    public function clearLog(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $type = $request->input('type', 'laravel');
        $logFile = $type === 'backup' 
            ? storage_path('logs/backup_schedule.log') 
            : storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            File::put($logFile, '');
        }

        return response()->json([
            'success' => true,
            'message' => 'ล้างประวัติ Log สำเร็จเรียบร้อย'
        ]);
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
                case 'db_backup':
                    $res = app(DatabaseBackupService::class)->createBackup();
                    $output = "สำรองฐานข้อมูลสำเร็จ: {$res['filename']} ({$res['file_size_formatted']}) ใช้เวลา {$res['duration_seconds']}s";
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

    /**
     * Helper to read last N lines of a file efficiently.
     */
    protected function readTailLines(string $filePath, int $maxLines = 50): array
    {
        $lines = [];
        if (!File::exists($filePath)) {
            return $lines;
        }

        try {
            $file = new \SplFileObject($filePath, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();

            $startLine = max(0, $totalLines - $maxLines);
            $file->seek($startLine);

            while (!$file->eof()) {
                $line = trim($file->current());
                if (!empty($line)) {
                    $lines[] = $line;
                }
                $file->next();
            }

            return array_reverse($lines);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
