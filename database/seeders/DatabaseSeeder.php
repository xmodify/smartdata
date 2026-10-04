<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed default users if not exist
        if (!User::where('username', '1341800003078')->exists()) {
            User::factory()->create([
                'name' => 'นายศิริฤกษ์ คณาดี',
                'username' => '1341800003078',
                'password' => bcrypt('12345678'),
                'role' => 'admin',
                'active' => 'Y',
                'email' => 'admin@example.com',
            ]);
        }

        if (!User::where('username', 'user01')->exists()) {
            User::factory()->create([
                'name' => 'User Test',
                'username' => 'user01',
                'password' => bcrypt('12345678'),
                'role' => 'user',
                'active' => 'Y',
                'email' => 'user@example.com',
            ]);
        }

        // 2. Auto-seed all JSON files matching database/seeders/*_seeds.json
        $this->syncJsonSeeds();
    }

    /**
     * Scan and sync all *_seeds.json files into database
     */
    public function syncJsonSeeds(): void
    {
        $seedFiles = array_unique(array_merge(
            glob(database_path('seeders/*_seeds.json')) ?: [],
            glob(database_path('*_seeds.json')) ?: []
        ));

        $today = date('Y-m-d');

        foreach ($seedFiles as $file) {
            $content = json_decode(file_get_contents($file), true);
            if (!is_array($content)) {
                continue;
            }

            foreach ($content as $table => $records) {
                if (!Schema::hasTable($table) || !is_array($records)) {
                    continue;
                }

                foreach ($records as $record) {
                    $matchBy = $record['match_by'] ?? [];
                    $data = $record['data'] ?? [];

                    if ($table === 'budget_year' && isset($data['DATE_BEGIN'], $data['DATE_END'])) {
                        $data['ACTIVE'] = ($today >= $data['DATE_BEGIN'] && $today <= $data['DATE_END']) ? 'True' : 'False';
                    }

                    if (empty($matchBy)) {
                        continue;
                    }

                    $matchCondition = [];
                    foreach ($matchBy as $key) {
                        if (isset($data[$key])) {
                            $matchCondition[$key] = $data[$key];
                        }
                    }

                    if (empty($matchCondition)) {
                        continue;
                    }

                    // SAFE-BY-DEFAULT POLICY:
                    // Only explicit master/dictionary lookup tables are allowed to update reference data.
                    // All other tables (configurations, user data, transactions, and ANY future tables) are strictly INSERT-ONLY if not exist.
                    $masterReferenceTables = ['budget_year', 'lookup_hospcode', 'icd10'];
                    $isMasterTable = in_array($table, $masterReferenceTables);

                    $query = DB::table($table)->where($matchCondition);
                    if ($query->exists()) {
                        // If it's a master reference table, sync updated definitions/active status
                        if ($isMasterTable) {
                            $updateData = $data;
                            if (Schema::hasColumn($table, 'updated_at')) {
                                $updateData['updated_at'] = now();
                            }
                            $query->update($updateData);
                        }
                        // For all other tables (present and future): PRESERVE EXISTING DATA, NEVER OVERWRITE!
                    } else {
                        // Record does not exist -> Safe initial insertion
                        $insertData = $data;
                        if (Schema::hasColumn($table, 'created_at')) {
                            $insertData['created_at'] = now();
                        }
                        if (Schema::hasColumn($table, 'updated_at')) {
                            $insertData['updated_at'] = now();
                        }
                        DB::table($table)->insert($insertData);
                    }
                }
            }
        }
    }
}
