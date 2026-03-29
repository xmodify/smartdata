<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\SysVar;
use App\Models\MophNotify;
use App\Models\TelegramNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\BufferedOutput;

class StructureController extends Controller
{
    /**
     * Display system settings and variables.
     */
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        
        $sysVars = SysVar::all();
        
        // Handle cases where tables might be missing (e.g. after manual deletion)
        try {
            $mophNotifies = MophNotify::all();
        } catch (\Exception $e) {
            $mophNotifies = collect();
        }

        try {
            $telegramNotifies = TelegramNotify::all();
        } catch (\Exception $e) {
            $telegramNotifies = collect();
        }

        return view('admin.system.index', compact('sysVars', 'mophNotifies', 'telegramNotifies'));
    }

    /**
         * Execute git pull to update source code.
         */
    public function gitPull(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $details = $request->input('details');
        if ($details) {
            Log::info('Git Pull triggered by ' . auth()->user()->name . ' with details: ' . $details);
        }

        $output = shell_exec('git pull 2>&1');
        return back()->with('git_output', $output)->with('active_tab', 'system');
    }

    /**
     * Upgrade database structure by running migrations.
     */
    public function upgrade()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        try {
            // If tables were manually deleted, clear migration records to force recreate
            if (!Schema::hasTable('moph_notify')) {
                DB::table('migrations')->where('migration', 'like', '%create_moph_notifies_table%')->delete();
            }
            if (!Schema::hasTable('telegram_notify')) {
                DB::table('migrations')->where('migration', 'like', '%create_telegram_notifies_table%')->delete();
            }

            $output = new BufferedOutput();
            Artisan::call('migrate', [], $output);

            // Ensure active column exists if the table was created before this update
            if (Schema::hasTable('moph_notify') && !Schema::hasColumn('moph_notify', 'active')) {
                Schema::table('moph_notify', function ($table) {
                    $table->char('active', 1)->default('Y')->after('secret');
                });
            }

            // Sync Moph Notify Records (From User Screenshot)
            if (Schema::hasTable('moph_notify')) {
                $mophRecords = [
                    ['name' => 'AuN HC10989'],
                    ['name' => 'IT รพ.หัวตะพาน HC10989'],
                    ['name' => 'หัวหน้าฝ่าย/งาน HC10989'],
                ];
                foreach ($mophRecords as $record) {
                    MophNotify::firstOrCreate(['name' => $record['name']], [
                        'client_id' => 'xxx',
                        'secret' => 'xxx'
                    ]);
                }
            }

            // Sync Telegram Notify Records
            if (Schema::hasTable('telegram_notify')) {
                $telegramRecords = [
                    ['name' => 'telegram_bot_token', 'name_th' => 'Telegram Bot Token'],
                    ['name' => 'telegram_chat_id_register', 'name_th' => 'Telegram Chat ID Register'],
                ];
                foreach ($telegramRecords as $record) {
                    TelegramNotify::firstOrCreate(['name' => $record['name']], [
                        'name_th' => $record['name_th'],
                        'value' => 'xxx'
                    ]);
                }
            }

            $result = $output->fetch();
            $result .= "\nDatabase upgrade and notify records synced.";

            return back()->with('success', 'อัปเกรดโครงสร้างฐานข้อมูลเสร็จสิ้น')->with('migrate_output', $result)->with('active_tab', 'system');
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการอัปเกรด: ' . $e->getMessage())->with('active_tab', 'system');
        }
    }

    /**
     * Update a system variable value.
     */
    public function update_sysvar(Request $request, SysVar $sysVar)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'sys_value' => 'nullable|string',
        ]);

        $sysVar->update($validated);

        return back()->with('success', 'อัปเดตค่า ' . $sysVar->sys_name_th . ' เรียบร้อยแล้ว')->with('active_tab', 'system');
    }

    /**
     * Update Moph Notify settings.
     */
    public function update_moph_notify(Request $request, MophNotify $mophNotify)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => 'nullable|string',
            'secret' => 'nullable|string',
        ]);

        // Handle checkbox (send 'Y' or 'N' instead of boolean)
        $validated['active'] = $request->has('active') ? 'Y' : 'N';

        $mophNotify->update($validated);

        return back()->with('success', 'อัปเดตการตั้งค่า Moph Notify (' . $mophNotify->name . ') เรียบร้อยแล้ว')->with('active_tab', 'system');
    }

    /**
     * Update Telegram Notify settings.
     */
    public function update_telegram_notify(Request $request, TelegramNotify $telegramNotify)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name_th' => 'nullable|string',
            'value' => 'nullable|string',
        ]);

        $telegramNotify->update($validated);

        return back()->with('success', 'อัปเดตการตั้งค่า Telegram Notify (' . $telegramNotify->name_th . ') เรียบร้อยแล้ว')->with('active_tab', 'system');
    }

}
