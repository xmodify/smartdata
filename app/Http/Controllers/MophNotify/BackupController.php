<?php

namespace App\Http\Controllers\MophNotify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BackupController extends Controller
{
    public function check(Request $request)
    {
        try {
            $latest = DB::connection('hosxp')
                ->table('system_backup_log')
                ->orderBy('backup_finish_datetime', 'desc')
                ->first(['backup_datetime', 'backup_finish_datetime', 'backup_computer', 'backup_filename']);

            if (!$latest) {
                return response()->json(['status' => 'no_records']);
            }

            $cacheKey = 'last_moph_notified_backup_finish';
            $lastNotified = Cache::get($cacheKey);

            if (!$lastNotified) {
                // First run: initialize cache with latest backup finish time
                Cache::put($cacheKey, $latest->backup_finish_datetime, now()->addDays(30));
                return response()->json([
                    'status' => 'initialized',
                    'latest_backup_finish' => $latest->backup_finish_datetime
                ]);
            }

            if ($latest->backup_finish_datetime > $lastNotified || $request->has('force')) {
                // New backup detected or forced notification
                $message = "แจ้งเตือน BackupHOSxP\n"
                    . "✅เสร็จเรียบร้อยแล้ว\n"
                    . "เริ่ม: " . DateThai($latest->backup_datetime) . "\n"
                    . "เสร็จ: " . DateThai($latest->backup_finish_datetime) . "\n" 
                    . "-----------------------\n" 
                    . route('mophnotify.backup_hosxp') . "\n"
                    . "-----------------------\n"
                    . "เวลาเช็ค: " . DateThai(now()->toDateTimeString());

                // Send to MOPH Notify ID 1
                $client = DB::table('moph_notify')
                    ->where('id', 1)
                    ->where('active', 'Y')
                    ->first(['id', 'name', 'client_id', 'secret']);

                $notifyResult = 'No active client configuration found';
                if ($client) {
                    $endpoint = "https://morpromt2f.moph.go.th/api/notify/send";
                    $payload = [
                        "messages" => [
                            [
                                "type" => "text",
                                "text" => "🏥 {$message}"
                            ]
                        ]
                    ];

                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'client-key'   => $client->client_id,
                        'secret-key'   => $client->secret
                    ])->post($endpoint, $payload);

                    if ($response->successful()) {
                        $notifyResult = '✅ ส่งการแจ้งเตือน MOPH Notify สำเร็จไปยัง ' . $client->name;
                    } else {
                        $notifyResult = '❌ ส่งการแจ้งเตือนล้มเหลว HTTP Code ' . $response->status();
                    }
                }

                // Update cache
                Cache::put($cacheKey, $latest->backup_finish_datetime, now()->addDays(30));

                return response()->json([
                    'status' => 'new_backup_notified',
                    'latest_backup' => $latest,
                    'notify_status' => $notifyResult
                ]);
            }

            return response()->json([
                'status' => 'no_new_backup',
                'latest_backup_finish' => $latest->backup_finish_datetime,
                'last_notified' => $lastNotified
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $logs = DB::connection('hosxp')
                ->table('system_backup_log')
                ->orderBy('backup_finish_datetime', 'desc')
                ->limit(10)
                ->get(['backup_datetime', 'backup_finish_datetime', 'backup_computer', 'backup_filename', 'backup_size', 'backup_type']);

            return view('mophnotify.backup', compact('logs'));

        } catch (\Exception $e) {
            return response()->view('errors.500', ['exception' => $e], 500);
        }
    }
}
