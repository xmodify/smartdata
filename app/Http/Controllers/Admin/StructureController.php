<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\MophNotify;
use App\Models\MophAlert;
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
        
        // Handle cases where tables might be missing (e.g. after manual deletion)
        try {
            $mophNotifies = MophNotify::all();
        } catch (\Exception $e) {
            $mophNotifies = collect();
        }

        try {
            $mophAlerts = MophAlert::all();
        } catch (\Exception $e) {
            $mophAlerts = collect();
        }

        try {
            $telegramNotifies = TelegramNotify::all();
        } catch (\Exception $e) {
            $telegramNotifies = collect();
        }

        try {
            $staffList = DB::connection('backoffice')->table('hrd_person')
                ->leftJoin('hrd_prefix', 'hrd_prefix.HR_PREFIX_ID', '=', 'hrd_person.HR_PREFIX_ID')
                ->where('hrd_person.HR_STATUS_ID', 1)
                ->select([
                    'hrd_person.HR_CID as cid',
                    'hrd_prefix.HR_PREFIX_NAME as prefix',
                    'hrd_person.HR_FNAME as fname',
                    'hrd_person.HR_LNAME as lname'
                ])
                ->orderBy('hrd_person.HR_FNAME')
                ->get();
        } catch (\Exception $e) {
            $staffList = collect();
        }

        try {
            $providerId = \App\Models\ProviderId::find(1);
        } catch (\Exception $e) {
            $providerId = null;
        }

        try {
            $aopodToken = \App\Models\SysVar::find('aopod_api_token')?->sys_value;
            $aopodUrl = \App\Models\SysVar::find('aopod_death_api_url')?->sys_value;
        } catch (\Exception $e) {
            $aopodToken = $aopodUrl = null;
        }

        try {
            $aopodYears = \App\Models\AopodDeath::whereNotNull('death_date')
                ->selectRaw('DISTINCT YEAR(death_date) as y')->orderByDesc('y')->pluck('y')->all();
            $aopodYear = request('aopod_year', $aopodYears[0] ?? 'all');
            if ($aopodYear !== 'all' && !in_array((int) $aopodYear, $aopodYears)) {
                $aopodYear = $aopodYears[0] ?? 'all';
            }
            $aopodQuery = \App\Models\AopodDeath::query();
            if ($aopodYear !== 'all') {
                $aopodQuery->whereYear('death_date', (int) $aopodYear);
            }
            $aopodDeaths = $aopodQuery->orderByDesc('death_date')->orderByDesc('id')->get();
            $aopodTotal = \App\Models\AopodDeath::count();
            $aopodLog = \App\Models\AopodFetchLog::orderByDesc('id')->first();
            $aopodLastSuccess = \App\Models\AopodFetchLog::where('status', 'success')->orderByDesc('id')->first();
        } catch (\Exception $e) {
            $aopodDeaths = collect();
            $aopodYears = [];
            $aopodYear = 'all';
            $aopodTotal = 0;
            $aopodLog = $aopodLastSuccess = null;
        }

        return view('admin.system.index', compact('mophNotifies', 'telegramNotifies', 'mophAlerts', 'staffList', 'providerId', 'aopodToken', 'aopodUrl', 'aopodYears', 'aopodYear', 'aopodDeaths', 'aopodTotal', 'aopodLog', 'aopodLastSuccess'));
    }

    /**
     * Fetch death data from AOPOD and store locally (with fetch status log).
     */
    public function fetch_aopod(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        @set_time_limit(180);
        try {
            $log = \App\Services\AopodService::fetch(auth()->user()->name ?? null);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json([
            'success' => $log->status === 'success',
            'message' => $log->message,
            'total' => $log->total,
            'inserted' => $log->inserted,
            'updated' => $log->updated,
        ]);
    }

    /**
     * Save AOPOD API settings (stored in sys_var).
     */
    public function update_aopod(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'aopod_api_token' => 'nullable|string',
            'aopod_death_api_url' => 'nullable|url|max:500',
        ]);

        // โครงสร้างตารางสร้างโดยปุ่ม Update Version (database/extracted_schemas.json)
        if (!Schema::hasTable('sys_var')) {
            return back()->with('error', 'ยังไม่มีตาราง sys_var กรุณากด Update Version (อัปเกรดโครงสร้างฐานข้อมูล) ก่อน')->with('active_tab', 'aopod');
        }

        $labels = [
            'aopod_api_token' => 'AOPOD API Token',
            'aopod_death_api_url' => 'AOPOD Death API URL',
        ];
        foreach ($labels as $key => $label) {
            \App\Models\SysVar::updateOrCreate(
                ['sys_name' => $key],
                ['sys_name_th' => $label, 'sys_value' => trim($validated[$key] ?? '')]
            );
        }

        return back()->with('success', 'บันทึกการตั้งค่า AOPOD เรียบร้อยแล้ว')->with('active_tab', 'aopod');
    }

    /**
     * Test the AOPOD API connection with the given (or saved) token/URL.
     */
    public function test_aopod(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $hasTable = Schema::hasTable('sys_var');
        $url = trim((string) $request->input('url')) ?: ($hasTable ? \App\Models\SysVar::find('aopod_death_api_url')?->sys_value : null);
        $token = trim((string) $request->input('token')) ?: ($hasTable ? \App\Models\SysVar::find('aopod_api_token')?->sys_value : null);

        if (!$url || !$token) {
            return response()->json(['success' => false, 'message' => 'กรุณากรอก Token และ URL ก่อนทดสอบ']);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(20)
                ->acceptJson()
                ->withToken($token)
                ->post($url);

            $json = $response->json();
            $sample = is_array($json) ? mb_substr(json_encode(array_slice($json, 0, 1), JSON_UNESCAPED_UNICODE), 0, 600) : mb_substr($response->body(), 0, 300);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful()
                    ? 'เชื่อมต่อสำเร็จ (HTTP ' . $response->status() . ')'
                    : 'เชื่อมต่อไม่สำเร็จ (HTTP ' . $response->status() . ')',
                'sample' => $sample,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เชื่อมต่อไม่ได้: ' . $e->getMessage()]);
        }
    }

    /**
     * Execute git pull to update source code.
     */
    public function gitPull(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        // Safety check: Prevent git reset & pull on local development environment
        if (app()->isLocal() || config('app.env') === 'local') {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'ระบบทำงานในสภาพแวดล้อม Local (Development Mode) การ Git Reset & Pull ถูกระงับเพื่อป้องกันโค้ดที่คุณกำลังพัฒนาสูญหาย',
                    'is_local' => true,
                    'skipped' => true
                ], 400);
            }
            return redirect()->back()->with('error', 'ระบบทำงานในสภาพแวดล้อม Local การ Git Pull ถูกปิดใช้งานเพื่อความปลอดภัย');
        }

        $details = $request->input('details');
        if ($details) {
            Log::info('Git Pull triggered by ' . auth()->user()->name . ' with details: ' . $details);
        }

        // 1. Fetch latest changes from remote main branch
        // 2. Hard reset local working tree to match origin/main exactly
        // 3. Clean untracked files (keeps .env and storage safe via .gitignore)
        $output = shell_exec('git fetch origin main && git reset --hard origin/main && git clean -fd 2>&1');
        
        // 4. Clear application caches (config, route, views, compiled classes)
        try {
            $artisanOutput = new BufferedOutput();
            Artisan::call('optimize:clear', [], $artisanOutput);
            $output .= "\n\n--- Artisan Optimize Clear ---\n" . $artisanOutput->fetch();
        } catch (\Exception $e) {
            $output .= "\n\nError running artisan optimize:clear: " . $e->getMessage();
        }

        \Illuminate\Support\Facades\Cache::forget(\App\Services\VersionService::CACHE_KEY);

        if ($request->wantsJson()) {
            return response()->json(['output' => trim($output)]);
        }

        return back()->with('git_output', $output)->with('active_tab', 'system');
    }

    /**
     * Check for version update via VersionService (AJAX API)
     */
    public function checkVersionUpdate(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $force = $request->boolean('force', false);
        $res = \App\Services\VersionService::checkForUpdate($force);
        return response()->json($res);
    }

    /**
     * Upgrade database structure by running migrations.
     */
    public function upgrade(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($request->input('action') === 'get_steps') {
            $schemaFile = database_path('extracted_schemas.json');
            $tables = [];
            if (file_exists($schemaFile)) {
                $schemas = json_decode(file_get_contents($schemaFile), true);
                if (is_array($schemas)) {
                    $tables = array_keys($schemas);
                }
            }

            $steps = [
                ['id' => 'run_migrations', 'name' => 'รันคำสั่ง Migration ฐานข้อมูลหลัก...']
            ];

            foreach ($tables as $table) {
                $steps[] = [
                    'id' => 'sync_table_' . $table,
                    'name' => "ตรวจสอบและอัปเดตตาราง {$table}..."
                ];
            }

            $steps[] = ['id' => 'sync_seed_data', 'name' => 'นำเข้าข้อมูลตั้งต้นระบบ...'];
            $steps[] = ['id' => 'finalize', 'name' => 'เคลียร์ระบบแคชและออปติไมซ์...'];

            return response()->json(['success' => true, 'steps' => $steps]);
        }

        $step = $request->input('step');

        try {
            if (strpos($step, 'sync_table_') === 0) {
                $tableName = substr($step, 11);
                $msg = $this->syncTableSchema($tableName);
                return response()->json(['success' => true, 'message' => $msg]);
            }

            switch ($step) {
                case 'run_migrations':
                    $output = new BufferedOutput();
                    Artisan::call('migrate', ['--force' => true], $output);
                    return response()->json(['success' => true, 'message' => 'Migrations completed. Output: ' . trim($output->fetch())]);

                case 'sync_seed_data':
                    $seeder = new \Database\Seeders\DatabaseSeeder();
                    $log = $seeder->syncJsonSeeds();

                    return response()->json([
                        'success' => true, 
                        'message' => count($log) > 0 ? implode(', ', $log) : 'ข้อมูลตั้งต้นระบบเป็นปัจจุบันแล้ว'
                    ]);

                case 'finalize':
                    Artisan::call('optimize:clear');
                    return response()->json(['success' => true, 'message' => 'Cache and optimization cleared. Upgrade successful!']);

                default:
                    return response()->json(['success' => false, 'message' => 'Invalid upgrade step.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to sync table schema using database/extracted_schemas.json
     */
    private function syncTableSchema($tableName)
    {
        $schemaFile = database_path('extracted_schemas.json');
        if (!file_exists($schemaFile)) {
            return "Schema file not found.";
        }

        $schemas = json_decode(file_get_contents($schemaFile), true);
        if (!isset($schemas[$tableName])) {
            return "Schema for table {$tableName} not found in JSON.";
        }

        $tableSchema = $schemas[$tableName];
        $expectedColumns = $tableSchema['columns'] ?? [];
        $expectedIndexes = $tableSchema['indexes'] ?? [];

        // Helper to define a column on Blueprint
        $defineColumn = function($tableBlueprint, $column, $info, $change = false) {
            $type = $info['type'];
            $length = $info['length'] ?? null;
            $nullable = $info['nullable'] ?? true;
            $default = $info['default'] ?? null;
            
            $colObj = null;

            if ($info['extra'] === 'auto_increment' && !$change) {
                if ($type === 'bigint') {
                    return $tableBlueprint->bigIncrements($column);
                } else {
                    return $tableBlueprint->increments($column);
                }
            }

            if ($type === 'bigint') {
                $colObj = $tableBlueprint->bigInteger($column);
            } elseif ($type === 'int') {
                $colObj = $tableBlueprint->integer($column);
            } elseif ($type === 'char') {
                $colObj = $tableBlueprint->char($column, $length ?: 1);
            } elseif ($type === 'text') {
                $colObj = $tableBlueprint->text($column);
            } elseif ($type === 'longtext') {
                $colObj = $tableBlueprint->longText($column);
            } elseif ($type === 'mediumtext') {
                $colObj = $tableBlueprint->mediumText($column);
            } elseif ($type === 'timestamp') {
                $colObj = $tableBlueprint->timestamp($column);
            } elseif ($type === 'date') {
                $colObj = $tableBlueprint->date($column);
            } elseif ($type === 'time') {
                $colObj = $tableBlueprint->time($column);
            } elseif ($type === 'double') {
                if ($length && strpos($length, ',') !== false) {
                    list($precision, $scale) = explode(',', $length);
                    $colObj = $tableBlueprint->double($column, (int)$precision, (int)$scale);
                } else {
                    $colObj = $tableBlueprint->double($column);
                }
            } elseif ($type === 'float') {
                $colObj = $tableBlueprint->float($column);
            } elseif ($type === 'tinyint') {
                $colObj = $tableBlueprint->tinyInteger($column);
            } elseif ($type === 'smallint') {
                $colObj = $tableBlueprint->smallInteger($column);
            } elseif ($type === 'datetime') {
                $colObj = $tableBlueprint->dateTime($column);
            } elseif ($type === 'decimal') {
                $precision = 10;
                $scale = 2;
                if ($length && strpos($length, ',') !== false) {
                    list($precision, $scale) = explode(',', $length);
                }
                $colObj = $tableBlueprint->decimal($column, (int)$precision, (int)$scale);
            } elseif ($type === 'enum') {
                $allowed = [];
                if ($length) {
                    preg_match_all("/'([^']+)'/", $length, $matches);
                    if (!empty($matches[1])) {
                        $allowed = $matches[1];
                    }
                }
                $colObj = $tableBlueprint->enum($column, $allowed);
            } else {
                $colObj = $tableBlueprint->string($column, $length ?: 191);
            }

            if ($nullable) {
                $colObj->nullable();
            } else {
                $colObj->nullable(false);
            }

            if ($info['extra'] === 'auto_increment' && !$change) {
                // Keep auto increment setup intact during creation
            } else {
                if ($default !== null) {
                    $colObj->default($default);
                } else {
                    if ($nullable) {
                        $colObj->default(null);
                    }
                }
            }

            if ($change) {
                $colObj->change();
            }

            return $colObj;
        };

        // If table doesn't exist, create it completely from JSON definition
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function ($tableBlueprint) use ($expectedColumns, $expectedIndexes, $defineColumn) {
                foreach ($expectedColumns as $column => $info) {
                    $defineColumn($tableBlueprint, $column, $info, false);
                }

                foreach ($expectedIndexes as $keyName => $info) {
                    $cols = $info['columns'];
                    if ($keyName === 'PRIMARY') {
                        $isAuto = false;
                        foreach ($cols as $c) {
                            if (($expectedColumns[$c]['extra'] ?? '') === 'auto_increment') {
                                $isAuto = true;
                            }
                        }
                        if (!$isAuto) {
                            $tableBlueprint->primary($cols);
                        }
                        continue;
                    }
                    if ($info['unique']) {
                        $tableBlueprint->unique($cols, $keyName);
                    } else {
                        $tableBlueprint->index($cols, $keyName);
                    }
                }
            });
            return "Created table successfully.";
        }

        // 1. Get current columns from DB
        $currentCols = [];
        $columnsInfo = DB::select("SHOW FULL COLUMNS FROM `{$tableName}`");
        foreach ($columnsInfo as $col) {
            $rawType = $col->Type;
            $type = $rawType;
            $length = null;
            if (preg_match('/^(\w+)\((\d+)\)/', $rawType, $matches)) {
                $type = $matches[1];
                $length = (int)$matches[2];
            } elseif (preg_match('/^(\w+)\((.+)\)/', $rawType, $matches)) {
                $type = $matches[1];
                $length = $matches[2];
            }

            $currentCols[$col->Field] = [
                'type' => $type,
                'length' => $length,
                'nullable' => ($col->Null === 'YES'),
                'default' => $col->Default,
                'extra' => $col->Extra,
            ];
        }
        // 2. Add, modify or drop columns
        $addedColumns = [];
        $updatedColumns = [];
        $droppedCols = [];

        Schema::table($tableName, function ($tableBlueprint) use ($expectedColumns, $currentCols, $defineColumn, &$addedColumns, &$updatedColumns, &$droppedCols) {
            foreach ($expectedColumns as $column => $info) {
                if (!isset($currentCols[$column])) {
                    // Add column
                    $defineColumn($tableBlueprint, $column, $info, false);
                    $addedColumns[] = $column;
                } else {
                    // Check if modification is needed
                    $current = $currentCols[$column];
                    $changed = false;
                    
                    if ($current['type'] !== $info['type']) {
                        $changed = true;
                    }
                    if ($current['nullable'] !== $info['nullable']) {
                        $changed = true;
                    }

                    $currDef = $current['default'];
                    $expDef = $info['default'];
                    $defaultChanged = false;

                    if ($currDef !== null && $expDef !== null && is_numeric($currDef) && is_numeric($expDef)) {
                        // Compare numerically for float/decimal/double/int (e.g. '0.00' == '0')
                        if ((float)$currDef != (float)$expDef) {
                            $defaultChanged = true;
                        }
                    } else {
                        if (strval($currDef) !== strval($expDef)) {
                            $defaultChanged = true;
                        }
                    }

                    if ($defaultChanged && ($current['extra'] ?? '') !== 'auto_increment') {
                        $changed = true;
                    }

                    if ($info['length'] !== null && strval($current['length']) !== strval($info['length'])) {
                        $changed = true;
                    }

                    if ($changed) {
                        try {
                            $defineColumn($tableBlueprint, $column, $info, true);
                            $updatedColumns[] = $column;
                        } catch (\Exception $e) {
                            Log::warning("Failed to modify column {$column} on {$tableName}: " . $e->getMessage());
                        }
                    }
                }
            }

            // Drop columns not present in expected columns list
            foreach ($currentCols as $colName => $colInfo) {
                if (!isset($expectedColumns[$colName])) {
                    $tableBlueprint->dropColumn($colName);
                    $droppedCols[] = $colName;
                }
            }
        });

        // 3. Sync Indexes
        $currentIndexes = [];
        $indexInfo = DB::select("SHOW INDEX FROM `{$tableName}`");
        foreach ($indexInfo as $idx) {
            $keyName = $idx->Key_name;
            $currentIndexes[$keyName] = $keyName;
        }

        $addedIndexes = [];
        $droppedIndexes = [];
        Schema::table($tableName, function ($tableBlueprint) use ($expectedIndexes, $currentIndexes, &$addedIndexes, &$droppedIndexes) {
            foreach ($expectedIndexes as $keyName => $info) {
                if ($keyName === 'PRIMARY') {
                    continue;
                }
                
                if (!isset($currentIndexes[$keyName])) {
                    $cols = $info['columns'];
                    if ($info['unique']) {
                        $tableBlueprint->unique($cols, $keyName);
                    } else {
                        $tableBlueprint->index($cols, $keyName);
                    }
                    $addedIndexes[] = $keyName;
                }
            }

            foreach ($currentIndexes as $keyName) {
                if ($keyName === 'PRIMARY') {
                    continue;
                }
                if (strpos($keyName, '_foreign') !== false) {
                    continue;
                }

                if (!isset($expectedIndexes[$keyName])) {
                    $tableBlueprint->dropIndex($keyName);
                    $droppedIndexes[] = $keyName;
                }
            }
        });

        $logMsg = [];
        if (count($addedColumns) > 0) $logMsg[] = "Added: " . implode(', ', $addedColumns);
        if (count($updatedColumns) > 0) $logMsg[] = "Modified: " . implode(', ', $updatedColumns);
        if (count($droppedCols) > 0) $logMsg[] = "Dropped: " . implode(', ', $droppedCols);
        if (count($addedIndexes) > 0) $logMsg[] = "Added indexes: " . implode(', ', $addedIndexes);
        if (count($droppedIndexes) > 0) $logMsg[] = "Dropped indexes: " . implode(', ', $droppedIndexes);

        return count($logMsg) > 0 ? "Synced: " . implode('; ', $logMsg) : "Schema is up-to-date.";
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

    /**
     * Update Moph Alert settings.
     */
    public function update_moph_alert(Request $request, MophAlert $mophAlert)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'client_id' => 'nullable|string',
            'secret' => 'nullable|string',
        ]);

        // Handle checkboxes (send 'Y' or 'N' instead of boolean)
        $validated['active'] = $request->has('active') ? 'Y' : 'N';
        $validated['enable_2fa'] = $request->has('enable_2fa') ? 'Y' : 'N';

        $mophAlert->update($validated);

        return back()->with('success', 'อัปเดตการตั้งค่า Moph Alert (' . $mophAlert->name . ') เรียบร้อยแล้ว')->with('active_tab', 'system');
    }

    /**
     * Test MOPH Alert by sending a free-form message.
     */
    public function test_moph_alert(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'alert_id' => 'required|integer',
            'cid'      => 'required|string|max:20',
            'title'    => 'required|string|max:255',
            'text'     => 'required|string',
            'html'     => 'required|string',
        ]);

        $result = \App\Services\MophAlertService::sendFreeForm(
            $request->cid,
            $request->title,
            $request->text,
            $request->html,
            $request->alert_id
        );

        if ($result) {
            return response()->json(['success' => true, 'message' => 'ส่งข้อความแจ้งเตือนสำเร็จ!', 'data' => $result]);
        }

        return response()->json(['success' => false, 'message' => 'ส่งข้อความล้มเหลว กรุณาตรวจสอบ Client ID / Secret หรือ Logs ของระบบ']);
    }

    /**
     * Update Provider ID settings.
     */
    public function update_provider_id(Request $request, \App\Models\ProviderId $providerId)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:191',
            'health_id_client_id' => 'nullable|string',
            'health_id_secret' => 'nullable|string',
            'provider_id_client_id' => 'nullable|string',
            'provider_id_secret' => 'nullable|string',
        ]);

        // Handle checkbox
        $validated['active'] = $request->has('active') ? 'Y' : 'N';

        $providerId->update($validated);

        return back()->with('success', 'อัปเดตการตั้งค่า Provider ID เรียบร้อยแล้ว')->with('active_tab', 'system');
    }

}

