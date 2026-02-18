<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\SysVar;
use Symfony\Component\Console\Output\BufferedOutput;

class StructureController extends Controller
{
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
        return back()->with('git_output', $output);
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
            $output = new BufferedOutput();
            Artisan::call('migrate', [], $output);
            $result = $output->fetch();

            // ปรับปรุงข้อมูลใน sys_var
            SysVar::upsert([
                ['sys_name' => 'telegram_token', 'sys_name_th' => 'Telegram Bot Token', 'sys_value' => 'xxx'],
                ['sys_name' => 'telegram_chat_id_register', 'sys_name_th' => 'Telegram Chat ID Register', 'sys_value' => 'xxx']
            ], ['sys_name'], ['sys_name_th']);

            $result .= "\nRecords in sys_var have been synchronized using upsert.";

            return back()->with('success', 'อัปเกรดโครงสร้างฐานข้อมูลเสร็จสิ้น')->with('migrate_output', $result);
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการอัปเกรด: ' . $e->getMessage());
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

        return back()->with('success', 'อัปเดตค่า ' . $sysVar->sys_name_th . ' เรียบร้อยแล้ว');
    }
}
