<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;

class DatabaseRestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:restore {filename? : The filename of the backup to restore}';

    /**
     * The console command description.
     */
    protected $description = 'Restore database from a compressed backup file (.sql.gz)';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $filename = $this->argument('filename');

        if (empty($filename)) {
            $files = $backupService->getBackupFiles();
            if (empty($files)) {
                $this->error("No backup files found in storage/app/backups/");
                return Command::FAILURE;
            }

            $choices = array_map(function ($f) {
                return "{$f['filename']} ({$f['size_formatted']} - {$f['created_at']})";
            }, $files);

            $selected = $this->choice("Select a backup file to restore:", $choices, 0);
            $index = array_search($selected, $choices);
            $filename = $files[$index]['filename'];
        }

        if (!$this->confirm("WARNING: This will overwrite current database with data from {$filename}. Continue?", false)) {
            $this->warn("Restore aborted by user.");
            return Command::SUCCESS;
        }

        $this->info("Starting database restore from {$filename}...");

        try {
            $res = $backupService->restoreBackup($filename);

            $this->info("Database restore completed successfully!");
            $this->line("Method: {$res['method']} | Duration: {$res['duration_seconds']}s");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Database restore failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
