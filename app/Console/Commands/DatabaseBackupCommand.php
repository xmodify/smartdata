<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DatabaseBackupService;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     */
    protected $description = 'Create a compressed database backup (.sql.gz) with automatic 7-day retention policy';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info("Starting SmartData database backup...");

        try {
            $res = $backupService->createBackup();

            $this->info("Backup completed successfully!");
            $this->table(
                ['Filename', 'Size', 'Method', 'Duration', 'Cleaned Old Files'],
                [[
                    $res['filename'],
                    $res['file_size_formatted'],
                    $res['method'],
                    $res['duration_seconds'] . 's',
                    $res['cleaned_old_files'] . ' files'
                ]]
            );

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Database backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
