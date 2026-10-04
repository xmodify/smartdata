<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PDO;

class DatabaseBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    /**
     * Create a compressed SQL backup (.sql.gz) of the entire database.
     */
    public function createBackup(): array
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '512M');

        $startTime = microtime(true);
        $dateStr = date('Y-m-d_His');
        $filename = "backup_smartdata_{$dateStr}.sql.gz";
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        if (empty($dbName)) {
            throw new \Exception("Database name is not configured.");
        }

        // 1. Try native mysqldump first if available (Ultra fast)
        $mysqldumpPath = $this->findMysqldumpPath();
        if ($mysqldumpPath) {
            try {
                $success = $this->dumpWithMysqldump($mysqldumpPath, $dbHost, $dbPort, $dbUser, $dbPass, $dbName, $filePath);
                if ($success && File::exists($filePath) && filesize($filePath) > 0) {
                    $duration = round(microtime(true) - $startTime, 2);
                    $fileSize = filesize($filePath);
                    $cleanedCount = $this->cleanOldBackups(7);

                    $result = [
                        'ok' => true,
                        'method' => 'mysqldump',
                        'filename' => $filename,
                        'file_path' => $filePath,
                        'file_size' => $fileSize,
                        'file_size_formatted' => $this->formatBytes($fileSize),
                        'duration_seconds' => $duration,
                        'cleaned_old_files' => $cleanedCount,
                        'created_at' => now()->toDateTimeString(),
                    ];

                    $this->logBackupResult($result);
                    return $result;
                }
            } catch (\Throwable $ex) {
                Log::warning("mysqldump failed, falling back to PDO stream dump: " . $ex->getMessage());
            }
        }

        // 2. High-speed PDO streaming dump (Pure PHP fallback)
        $gz = @gzopen($filePath, 'w6');
        if (!$gz) {
            throw new \Exception("Cannot create backup file at {$filePath}");
        }

        try {
            $pdo = DB::connection()->getPdo();

            gzwrite($gz, "-- --------------------------------------------------------\n");
            gzwrite($gz, "-- SmartData Database Backup (High-Speed PHP Stream)\n");
            gzwrite($gz, "-- Database: {$dbName}\n");
            gzwrite($gz, "-- Date: " . date('Y-m-d H:i:s') . "\n");
            gzwrite($gz, "-- --------------------------------------------------------\n\n");
            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=0;\n");
            gzwrite($gz, "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n");
            gzwrite($gz, "SET AUTOCOMMIT=0;\n");
            gzwrite($gz, "START TRANSACTION;\n\n");

            $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tables = $tablesStmt->fetchAll(PDO::FETCH_NUM);

            $tableCount = 0;
            $rowCountTotal = 0;

            foreach ($tables as $tRow) {
                $tableName = $tRow[0];
                $tableCount++;

                gzwrite($gz, "\n-- --------------------------------------------------------\n");
                gzwrite($gz, "-- Table structure for `{$tableName}`\n");
                gzwrite($gz, "-- --------------------------------------------------------\n");
                gzwrite($gz, "DROP TABLE IF EXISTS `{$tableName}`;\n");

                $createStmt = $pdo->query("SHOW CREATE TABLE `{$tableName}`");
                $createRow = $createStmt->fetch(PDO::FETCH_NUM);
                if (!empty($createRow[1])) {
                    gzwrite($gz, $createRow[1] . ";\n\n");
                }

                // Stream rows with cursor
                $dataStmt = $pdo->query("SELECT * FROM `{$tableName}`");
                $batch = [];
                $batchSize = 250;

                while ($row = $dataStmt->fetch(PDO::FETCH_NUM)) {
                    $rowCountTotal++;
                    $escapedValues = [];
                    foreach ($row as $val) {
                        if (is_null($val)) {
                            $escapedValues[] = 'NULL';
                        } elseif (is_numeric($val) && !is_string($val)) {
                            $escapedValues[] = $val;
                        } else {
                            $escapedValues[] = $pdo->quote((string)$val);
                        }
                    }
                    $batch[] = "(" . implode(", ", $escapedValues) . ")";

                    if (count($batch) >= $batchSize) {
                        gzwrite($gz, "INSERT INTO `{$tableName}` VALUES \n" . implode(",\n", $batch) . ";\n");
                        $batch = [];
                    }
                }

                if (!empty($batch)) {
                    gzwrite($gz, "INSERT INTO `{$tableName}` VALUES \n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }

            gzwrite($gz, "\nCOMMIT;\n");
            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
            gzclose($gz);

            $duration = round(microtime(true) - $startTime, 2);
            $fileSize = filesize($filePath);
            $cleanedCount = $this->cleanOldBackups(7);

            $result = [
                'ok' => true,
                'method' => 'pdo_stream',
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_size_formatted' => $this->formatBytes($fileSize),
                'table_count' => $tableCount,
                'total_rows' => $rowCountTotal,
                'duration_seconds' => $duration,
                'cleaned_old_files' => $cleanedCount,
                'created_at' => now()->toDateTimeString(),
            ];

            $this->logBackupResult($result);
            return $result;
        } catch (\Throwable $e) {
            if (isset($gz) && is_resource($gz)) {
                @gzclose($gz);
            }
            if (File::exists($filePath)) {
                @unlink($filePath);
            }

            Log::error("Database backup failed: " . $e->getMessage());
            $errorResult = ['ok' => false, 'error' => $e->getMessage(), 'created_at' => now()->toDateTimeString()];
            $this->logBackupResult($errorResult);

            throw $e;
        }
    }

    /**
     * Check if mysqldump is available and return path.
     */
    protected function findMysqldumpPath(): ?string
    {
        $pathsToCheck = [
            'mysqldump',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'D:\\HostLite\\mysql\\bin\\mysqldump.exe',
            'D:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];

        foreach ($pathsToCheck as $p) {
            if ($p === 'mysqldump') {
                $test = @shell_exec('mysqldump --version 2>&1');
                if ($test && stripos($test, 'Distrib') !== false) {
                    return 'mysqldump';
                }
            } elseif (file_exists($p)) {
                return $p;
            }
        }

        return null;
    }

    /**
     * Run mysqldump command directly to gzip file.
     */
    protected function dumpWithMysqldump(string $bin, string $host, string $port, string $user, string $pass, string $db, string $targetGz): bool
    {
        $passArg = !empty($pass) ? ("-p" . escapeshellarg($pass)) : "";
        $cmd = escapeshellarg($bin) . " -h " . escapeshellarg($host) . " -P " . escapeshellarg($port) . " -u " . escapeshellarg($user) . " " . $passArg . " --single-transaction --quick " . escapeshellarg($db);

        $descriptors = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"], // stderr
        ];

        $process = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($process)) {
            return false;
        }

        fclose($pipes[0]);

        $gz = @gzopen($targetGz, 'w6');
        if (!$gz) {
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return false;
        }

        while (!feof($pipes[1])) {
            $chunk = fread($pipes[1], 65536);
            if ($chunk !== false && strlen($chunk) > 0) {
                gzwrite($gz, $chunk);
            }
        }

        gzclose($gz);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);
        if ($returnCode !== 0) {
            Log::warning("mysqldump error (code {$returnCode}): " . $stderr);
            return false;
        }

        return true;
    }

    /**
     * Get list of all backup files sorted by timestamp desc.
     */
    public function getBackupFiles(): array
    {
        if (!File::exists($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $list = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (preg_match('/^backup_smartdata_.*\.sql\.gz$/', $filename) || preg_match('/^backup_.*\.sql\.gz$/', $filename)) {
                $mtime = $file->getMTime();
                $size = $file->getSize();
                $carbonDate = Carbon::createFromTimestamp($mtime, config('app.timezone', 'Asia/Bangkok'));

                $list[] = [
                    'filename' => $filename,
                    'file_path' => $file->getPathname(),
                    'size_bytes' => $size,
                    'size_formatted' => $this->formatBytes($size),
                    'timestamp' => $mtime,
                    'created_at' => $carbonDate->toDateTimeString(),
                    'diff_human' => $carbonDate->diffForHumans(),
                    'age_days' => now()->diffInDays($carbonDate),
                ];
            }
        }

        usort($list, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return $list;
    }

    /**
     * Delete a single backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        if (!preg_match('/^backup_[a-zA-Z0-9_\-]+\.sql\.gz$/', $filename)) {
            return false;
        }

        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        if (File::exists($filePath)) {
            return File::delete($filePath);
        }

        return false;
    }

    /**
     * Clean backup files older than $days (7 days retention).
     */
    public function cleanOldBackups(int $days = 7): int
    {
        if (!File::exists($this->backupDir)) {
            return 0;
        }

        $files = File::files($this->backupDir);
        $deleted = 0;
        $cutoff = now()->subDays($days)->timestamp;

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (preg_match('/^backup_.*\.sql\.gz$/', $filename)) {
                if ($file->getMTime() < $cutoff) {
                    File::delete($file->getPathname());
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Format bytes.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Restore a compressed SQL backup (.sql.gz) into the database.
     */
    public function restoreBackup(string $filename): array
    {
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit', '1024M');

        $startTime = microtime(true);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!File::exists($filePath)) {
            throw new \Exception("Backup file {$filename} not found.");
        }

        $dbHost = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = config('database.connections.mysql.port', '3306');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');

        if (empty($dbName)) {
            throw new \Exception("Database name is not configured.");
        }

        // 1. Try native mysql CLI first if available (Ultra fast)
        $mysqlBinPath = $this->findMysqlCliPath();
        if ($mysqlBinPath) {
            try {
                $success = $this->restoreWithMysqlCli($mysqlBinPath, $dbHost, $dbPort, $dbUser, $dbPass, $dbName, $filePath);
                if ($success) {
                    $duration = round(microtime(true) - $startTime, 2);
                    $result = [
                        'ok' => true,
                        'method' => 'mysql_cli',
                        'filename' => $filename,
                        'duration_seconds' => $duration,
                        'restored_at' => now()->toDateTimeString(),
                    ];
                    $this->logRestoreResult($result);
                    return $result;
                }
            } catch (\Throwable $ex) {
                Log::warning("mysql CLI restore failed, falling back to PDO stream restore: " . $ex->getMessage());
            }
        }

        // 2. Pure PHP PDO stream decompression & execution fallback
        $gz = @gzopen($filePath, 'rb');
        if (!$gz) {
            throw new \Exception("Cannot read backup archive {$filename}");
        }

        try {
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

            $sqlBuffer = '';
            $executedQueries = 0;

            while (!gzeof($gz)) {
                $line = gzgets($gz, 65536);
                if ($line === false) continue;

                $trimmed = trim($line);
                if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                $sqlBuffer .= $line;

                if (str_ends_with($trimmed, ';')) {
                    $pdo->exec($sqlBuffer);
                    $executedQueries++;
                    $sqlBuffer = '';
                }
            }

            if (!empty(trim($sqlBuffer))) {
                $pdo->exec($sqlBuffer);
                $executedQueries++;
            }

            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            gzclose($gz);

            $duration = round(microtime(true) - $startTime, 2);
            $result = [
                'ok' => true,
                'method' => 'pdo_stream',
                'filename' => $filename,
                'executed_queries' => $executedQueries,
                'duration_seconds' => $duration,
                'restored_at' => now()->toDateTimeString(),
            ];

            $this->logRestoreResult($result);
            return $result;
        } catch (\Throwable $e) {
            if (isset($gz) && is_resource($gz)) {
                @gzclose($gz);
            }
            Log::error("Database restore failed: " . $e->getMessage());
            $errorResult = ['ok' => false, 'error' => $e->getMessage(), 'filename' => $filename, 'restored_at' => now()->toDateTimeString()];
            $this->logRestoreResult($errorResult);
            throw $e;
        }
    }

    /**
     * Check if mysql CLI binary is available and return path.
     */
    protected function findMysqlCliPath(): ?string
    {
        $pathsToCheck = [
            'mysql',
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'D:\\HostLite\\mysql\\bin\\mysql.exe',
            'D:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysql.exe',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
        ];

        foreach ($pathsToCheck as $p) {
            if ($p === 'mysql') {
                $test = @shell_exec('mysql --version 2>&1');
                if ($test && stripos($test, 'Distrib') !== false) {
                    return 'mysql';
                }
            } elseif (file_exists($p)) {
                return $p;
            }
        }

        return null;
    }

    /**
     * Restore using native mysql CLI with gzip streaming.
     */
    protected function restoreWithMysqlCli(string $bin, string $host, string $port, string $user, string $pass, string $db, string $gzPath): bool
    {
        $passArg = !empty($pass) ? ("-p" . escapeshellarg($pass)) : "";
        $cmd = escapeshellarg($bin) . " -h " . escapeshellarg($host) . " -P " . escapeshellarg($port) . " -u " . escapeshellarg($user) . " " . $passArg . " " . escapeshellarg($db);

        $descriptors = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"], // stderr
        ];

        $process = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($process)) {
            return false;
        }

        $gz = @gzopen($gzPath, 'rb');
        if (!$gz) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return false;
        }

        while (!gzeof($gz)) {
            $chunk = gzread($gz, 65536);
            if ($chunk !== false && strlen($chunk) > 0) {
                fwrite($pipes[0], $chunk);
            }
        }

        gzclose($gz);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);
        if ($returnCode !== 0) {
            Log::warning("mysql CLI restore error (code {$returnCode}): " . $stderr);
            return false;
        }

        return true;
    }

    /**
     * Log backup operations to storage/logs/backup_schedule.log.
     */
    protected function logBackupResult(array $result): void
    {
        $logFile = storage_path('logs/backup_schedule.log');
        $timeStr = $result['created_at'] ?? now()->toDateTimeString();

        if (!empty($result['ok'])) {
            $line = "[{$timeStr}] [BACKUP_SUCCESS] File: {$result['filename']} | Size: {$result['file_size_formatted']} | Method: {$result['method']} | Time: {$result['duration_seconds']}s | Cleaned: {$result['cleaned_old_files']} files" . PHP_EOL;
        } else {
            $line = "[{$timeStr}] [BACKUP_FAILED] Error: " . ($result['error'] ?? 'Unknown error') . PHP_EOL;
        }

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log restore operations.
     */
    protected function logRestoreResult(array $result): void
    {
        $logFile = storage_path('logs/backup_schedule.log');
        $timeStr = $result['restored_at'] ?? now()->toDateTimeString();

        if (!empty($result['ok'])) {
            $line = "[{$timeStr}] [RESTORE_SUCCESS] File: {$result['filename']} | Method: {$result['method']} | Time: {$result['duration_seconds']}s" . PHP_EOL;
        } else {
            $line = "[{$timeStr}] [RESTORE_FAILED] File: {$result['filename']} | Error: " . ($result['error'] ?? 'Unknown error') . PHP_EOL;
        }

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
