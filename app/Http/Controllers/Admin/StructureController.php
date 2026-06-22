<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
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

        return view('admin.system.index', compact('mophNotifies', 'telegramNotifies'));
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

        $output = shell_exec('git reset --hard && git pull origin main 2>&1');
        
        try {
            $artisanOutput = new BufferedOutput();
            Artisan::call('optimize:clear', [], $artisanOutput);
            $output .= "\n\n--- Artisan Optimize Clear ---\n" . $artisanOutput->fetch();
        } catch (\Exception $e) {
            $output .= "\n\nError running artisan optimize:clear: " . $e->getMessage();
        }

        return back()->with('git_output', $output)->with('active_tab', 'system');
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
                    Artisan::call('migrate', [], $output);
                    return response()->json(['success' => true, 'message' => 'Migrations completed. Output: ' . trim($output->fetch())]);

                case 'sync_seed_data':
                    $seedFile = database_path('default_seeds.json');
                    if (!file_exists($seedFile)) {
                        return response()->json(['success' => true, 'message' => 'ไม่พบไฟล์ข้อมูลตั้งต้น (default_seeds.json)']);
                    }

                    $seeds = json_decode(file_get_contents($seedFile), true);
                    if (!is_array($seeds)) {
                        return response()->json(['success' => false, 'message' => 'รูปแบบไฟล์ default_seeds.json ไม่ถูกต้อง']);
                    }

                    // Load extra seeds from icd10_seeds.json if exists
                    $icd10SeedFile = database_path('icd10_seeds.json');
                    if (file_exists($icd10SeedFile)) {
                        $icd10Seeds = json_decode(file_get_contents($icd10SeedFile), true);
                        if (is_array($icd10Seeds)) {
                            $seeds = array_merge($seeds, $icd10Seeds);
                        }
                    }

                    $log = [];
                    foreach ($seeds as $table => $records) {
                        if (!Schema::hasTable($table)) {
                            continue;
                        }

                        $seededCount = 0;
                        foreach ($records as $record) {
                            $matchBy = $record['match_by'] ?? [];
                            $data = $record['data'] ?? [];

                            // Build query to check if record exists
                            $query = DB::table($table);
                            foreach ($matchBy as $key) {
                                if (isset($data[$key])) {
                                    $query->where($key, $data[$key]);
                                }
                            }

                            if (!$query->exists()) {
                                // Add timestamps if columns exist in the table
                                if (Schema::hasColumn($table, 'created_at')) {
                                    $data['created_at'] = now();
                                }
                                if (Schema::hasColumn($table, 'updated_at')) {
                                    $data['updated_at'] = now();
                                }

                                DB::table($table)->insert($data);
                                $seededCount++;
                            }
                        }

                        if ($seededCount > 0) {
                            $log[] = "ตาราง {$table} (เพิ่ม {$seededCount} รายการ)";
                        }
                    }

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
            } elseif ($type === 'timestamp') {
                $colObj = $tableBlueprint->timestamp($column);
            } elseif ($type === 'date') {
                $colObj = $tableBlueprint->date($column);
            } elseif ($type === 'time') {
                $colObj = $tableBlueprint->time($column);
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
                    if (strval($current['default']) !== strval($info['default'])) {
                        if ($current['extra'] !== 'auto_increment') {
                            $changed = true;
                        }
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

}
