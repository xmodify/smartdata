<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VersionService
{
    const CACHE_KEY = 'smartdata_version_check_result';
    const CACHE_TTL_SECONDS = 300; // 5 minutes cache

    /**
     * Get current system version from Git commit timestamp or config
     */
    public static function getCurrentVersion(): string
    {
        // 1. If explicit override in env is set and not default, prioritize it
        $envVersion = env('APP_VERSION');
        if (!empty($envVersion) && !in_array($envVersion, ['V.26-10-04', 'latest'])) {
            return $envVersion;
        }

        // 2. Auto-generate from local git commit date/time
        try {
            $base_path = base_path();
            $res = self::runCommand('git -c safe.directory=* log -1 --format="%ci"', $base_path);
            $rawDate = trim($res['output'] ?? '');
            if (!empty($rawDate) && strtotime($rawDate)) {
                $dt = new \DateTime($rawDate);
                $dt->setTimezone(new \DateTimeZone('Asia/Bangkok'));
                return 'V.' . $dt->format('y-m-d H:i');
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return config('app.version', 'V.26-10-04');
    }

    /**
     * Get current local commit short hash
     */
    public static function getCurrentCommit(): string
    {
        try {
            $base_path = base_path();
            $res = self::runCommand('git -c safe.directory=* rev-parse --short HEAD', $base_path);
            $hash = trim($res['output'] ?? '');
            return !empty($hash) && strlen($hash) <= 12 ? $hash : 'unknown';
        } catch (\Throwable $e) {
            return 'unknown';
        }
    }

    /**
     * Check if a new version / commit is available remotely
     */
    public static function checkForUpdate(bool $force = false): array
    {
        if (!$force) {
            $cached = Cache::get(self::CACHE_KEY);
            if ($cached && is_array($cached)) {
                return $cached;
            }
        }

        $currentVersion = self::getCurrentVersion();
        $currentCommit = self::getCurrentCommit();
        $base_path = base_path();
        $git_cmd = 'git -c safe.directory=*';

        $hasUpdate = false;
        $remoteCommit = '';
        $remoteVersion = '';
        $message = 'ระบบเป็นเวอร์ชันล่าสุดแล้ว';
        $status = 'latest';

        try {
            // Check origin URL of current repository
            $originRes = self::runCommand("{$git_cmd} config --get remote.origin.url", $base_path);
            $currentOrigin = trim($originRes['output'] ?? '');

            $remote_url = !empty($currentOrigin) ? $currentOrigin : 'https://github.com/xmodify/smartdata.git';
            $repoName = 'xmodify/smartdata';

            // Get remote commit hash for main branch
            $lsRes = self::runCommand("{$git_cmd} ls-remote {$remote_url} refs/heads/main", $base_path);
            $rawLs = trim($lsRes['output'] ?? '');

            if (!empty($rawLs)) {
                $parts = preg_split('/\s+/', $rawLs);
                $fullRemoteHash = $parts[0] ?? '';
                if (!empty($fullRemoteHash) && strlen($fullRemoteHash) >= 7) {
                    $remoteCommit = substr($fullRemoteHash, 0, 7);

                    // Get full local commit hash
                    $localFullRes = self::runCommand("{$git_cmd} rev-parse HEAD", $base_path);
                    $localFullHash = trim($localFullRes['output'] ?? '');

                    if ($localFullHash === $fullRemoteHash) {
                        $hasUpdate = false;
                        $status = 'latest';
                        $message = 'ระบบเป็นเวอร์ชันล่าสุดแล้ว';
                    } else {
                        // Check if remote commit is already in local history
                        $ancestorCheck = self::runCommand("{$git_cmd} merge-base --is-ancestor {$fullRemoteHash} HEAD", $base_path);

                        if (($ancestorCheck['code'] ?? 1) === 0) {
                            $hasUpdate = false;
                            $status = 'latest';
                            $message = 'ระบบเป็นเวอร์ชันล่าสุดแล้ว';
                        } else {
                            $hasUpdate = true;
                            $status = 'update_available';
                            $message = "มีเวอร์ชันใหม่พร้อมอัปเดต";

                            // Attempt to parse version from GitHub API (commit timestamp & message)
                            try {
                                $headers = ['User-Agent' => 'SmartData-App'];
                                $req = Http::withHeaders($headers)->timeout(4);
                                $apiRes = $req->get("https://api.github.com/repos/{$repoName}/commits/main");
                                if ($apiRes->successful()) {
                                    $commitData = $apiRes->json();
                                    
                                    // 1. Auto version from remote commit timestamp
                                    $commitDate = $commitData['commit']['committer']['date'] ?? $commitData['commit']['author']['date'] ?? null;
                                    if ($commitDate) {
                                        $dt = new \DateTime($commitDate);
                                        $dt->setTimezone(new \DateTimeZone('Asia/Bangkok'));
                                        $remoteVersion = 'V.' . $dt->format('y-m-d H:i');
                                    }

                                    // 2. Override if explicit version pattern found in commit message
                                    $commitMsg = $commitData['commit']['message'] ?? '';
                                    if (preg_match('/(?:Release update:|version:?)\s*(V\.[0-9a-zA-Z\-\s:]+)/i', $commitMsg, $mV)) {
                                        $remoteVersion = trim($mV[1]);
                                    } elseif (preg_match('/(V\.[0-9]{2}-[0-9]{2}-[0-9]{2}(?:\s+[0-9]{1,2}:[0-9]{2})?)/i', $commitMsg, $mV)) {
                                        $remoteVersion = trim($mV[1]);
                                    }
                                }
                            } catch (\Throwable $e) {
                                // Fallback to commit hash if API fails
                            }
                        }
                    }
                }
            } else {
                $status = 'dev_mode';
                $message = 'โหมดพัฒนา (Local Repository)';
            }

        } catch (\Throwable $e) {
            Log::warning("SmartData version check error: " . $e->getMessage());
            $message = 'ไม่สามารถตรวจสอบเวอร์ชันออนไลน์ได้';
            $status = 'offline';
        }

        $result = [
            'current_version' => $currentVersion,
            'current_commit' => $currentCommit,
            'has_update' => $hasUpdate,
            'remote_commit' => $remoteCommit,
            'remote_version' => $remoteVersion,
            'status' => $status,
            'message' => $message,
            'checked_at' => date('Y-m-d H:i:s'),
        ];

        Cache::put(self::CACHE_KEY, $result, self::CACHE_TTL_SECONDS);

        return $result;
    }

    /**
     * Run command synchronously
     */
    private static function runCommand(string $cmd, ?string $cwd = null): array
    {
        $cwd = $cwd ?: base_path();
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = @proc_open($cmd, $descriptors, $pipes, $cwd);
        if (!is_resource($process)) {
            $out = [];
            $code = 1;
            @exec("{$cmd} 2>&1", $out, $code);
            return ['code' => $code, 'output' => trim(implode("\n", $out))];
        }
        if (isset($pipes[0])) fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($process);

        return [
            'code' => $code,
            'output' => trim($output . ($err ? "\n" . $err : ''))
        ];
    }
}
