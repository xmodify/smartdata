<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MophF5DrugService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MophF5ApiController extends Controller
{
    protected MophF5DrugService $service;

    public function __construct(MophF5DrugService $service)
    {
        $this->service = $service;
    }

    /**
     * Health check endpoint for testing API connectivity
     * GET /api/v1/moph-f5/health
     */
    public function health(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'service' => 'SmartData MOPH F5 Drug Warehouse API',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
            'client_ip' => $request->ip(),
            'auth_required' => true,
            'supported_methods' => ['POST'],
            'supported_formats' => ['xlsx', 'zip', 'txt', 'csv', 'json'],
        ]);
    }

    /**
     * Upload & import MOPH 5 files / INV Dataset via API
     * POST /api/v1/moph-f5/upload
     */
    public function upload(Request $request): JsonResponse
    {
        $startTime = microtime(true);
        $trackingId = 'REQ-' . strtoupper(Str::random(10));
        $clientIp = $request->ip();
        $userAgent = $request->header('User-Agent', 'Unknown-Client');

        // 1. Cybersecurity: IP Whitelisting Check (if configured in .env)
        if (!$this->isIpAllowed($clientIp)) {
            Log::warning("[CYBER-ALERT] Unauthorized IP attempted MOPH F5 API: {$clientIp}", [
                'tracking_id' => $trackingId,
                'user_agent' => $userAgent
            ]);
            $this->logSecurityEvent($clientIp, $userAgent, 'forbidden', 'IP not allowed', $trackingId);
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Your IP address is not permitted to access this API.',
                'error_code' => 'IP_FORBIDDEN',
                'tracking_id' => $trackingId
            ], 403);
        }

        // 2. Cybersecurity: Token Authentication
        if (!$this->verifyApiToken($request)) {
            Log::warning("[CYBER-ALERT] Invalid or missing API token from IP: {$clientIp}", [
                'tracking_id' => $trackingId,
                'user_agent' => $userAgent
            ]);
            $this->logSecurityEvent($clientIp, $userAgent, 'unauthorized', 'Invalid or missing API Token', $trackingId);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing API Token.',
                'error_code' => 'AUTH_FAILED',
                'tracking_id' => $trackingId
            ], 401);
        }

        // 3. Process Request (Multipart Files OR JSON Payload)
        try {
            // Case A: Multipart File Upload
            if ($request->hasFile('file') || $request->hasFile('files') || $request->allFiles()) {
                $response = $this->handleFileUploads($request, $clientIp, $userAgent, $startTime, $trackingId);
                return $response;
            }

            // Case B: JSON Payload
            if ($request->isJson() || $request->has('dataset') || $request->has('datasets') || $request->has('data')) {
                $response = $this->handleJsonPayload($request, $clientIp, $userAgent, $startTime, $trackingId);
                return $response;
            }

            return response()->json([
                'success' => false,
                'message' => 'Bad Request: No file or JSON data payload provided.',
                'error_code' => 'EMPTY_PAYLOAD',
                'tracking_id' => $trackingId
            ], 400);

        } catch (\Throwable $e) {
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);
            Log::error("[MOPH-F5-API] Exception during import: " . $e->getMessage(), [
                'tracking_id' => $trackingId,
                'client_ip' => $clientIp,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error while processing dataset.',
                'error_code' => 'SERVER_ERROR',
                'tracking_id' => $trackingId,
                'duration_ms' => $durationMs
            ], 500);
        }
    }

    /**
     * Handle Multipart File Uploads
     */
    protected function handleFileUploads(Request $request, string $clientIp, string $userAgent, float $startTime, string $trackingId): JsonResponse
    {
        $allFiles = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $allFiles = is_array($files) ? $files : [$files];
        } elseif ($request->hasFile('file')) {
            $allFiles = [$request->file('file')];
        } else {
            foreach ($request->allFiles() as $f) {
                if (is_array($f)) {
                    $allFiles = array_merge($allFiles, $f);
                } else {
                    $allFiles[] = $f;
                }
            }
        }

        if (empty($allFiles)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid files detected in the request.',
                'tracking_id' => $trackingId
            ], 400);
        }

        // Cybersecurity: File Validation & Antivirus / Malicious Extension Prevention
        $allowedExtensions = ['xlsx', 'zip', 'txt', 'csv'];
        $maxSizeBytes = 50 * 1024 * 1024; // Max 50 MB
        $savedFilePaths = [];
        $tempDir = storage_path('app/temp_api_f5_' . uniqid());

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        try {
            foreach ($allFiles as $uploadedFile) {
                if (!$uploadedFile->isValid()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Corrupt or invalid uploaded file: ' . $uploadedFile->getClientOriginalName(),
                        'tracking_id' => $trackingId
                    ], 422);
                }

                if ($uploadedFile->getSize() > $maxSizeBytes) {
                    return response()->json([
                        'success' => false,
                        'message' => 'File size exceeds maximum allowed limit (50MB): ' . $uploadedFile->getClientOriginalName(),
                        'tracking_id' => $trackingId
                    ], 413);
                }

                $ext = strtolower($uploadedFile->getClientOriginalExtension());
                if (!in_array($ext, $allowedExtensions, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Disallowed file type (.{$ext}). Only .xlsx, .zip, .txt, or .csv are permitted.",
                        'error_code' => 'INVALID_FILE_TYPE',
                        'tracking_id' => $trackingId
                    ], 415);
                }

                // Sanitize file name to prevent path traversal
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $uploadedFile->getClientOriginalName());
                $destPath = $tempDir . DIRECTORY_SEPARATOR . $safeName;
                $uploadedFile->move($tempDir, $safeName);
                $savedFilePaths[] = $destPath;
            }

            // Run import through service
            $result = $this->service->importFiles($savedFilePaths, 'api', $clientIp, $userAgent);
            $durationMs = round((microtime(true) - $startTime) * 1000, 2);

            $isSuccess = count($result['errors']) === 0;
            $statusCode = $isSuccess ? 200 : ($result['files_processed'] > 0 ? 207 : 422);

            return response()->json([
                'success' => $isSuccess,
                'message' => $isSuccess ? 'นำเข้าข้อมูลชุด INV Dataset ผ่าน API สำเร็จ' : 'ประมวลผลไฟล์เสร็จสิ้นพร้อมข้อความแจ้งเตือน',
                'tracking_id' => $trackingId,
                'data' => [
                    'files_processed' => $result['files_processed'],
                    'total_rows_imported' => $result['total_rows_imported'],
                    'details' => $result['details'],
                    'errors' => $result['errors'],
                    'duration_ms' => $durationMs,
                ]
            ], $statusCode);

        } finally {
            // Clean up temporary files safely
            foreach (glob($tempDir . '/*') as $f) {
                @unlink($f);
            }
            @rmdir($tempDir);
        }
    }

    /**
     * Handle JSON dataset payload
     */
    protected function handleJsonPayload(Request $request, string $clientIp, string $userAgent, float $startTime, string $trackingId): JsonResponse
    {
        $hospCode = $request->input('hosp_code', '');
        $periodRpt = $request->input('period_rpt', '');
        $totalRows = 0;
        $filesProcessed = 0;
        $details = [];
        $errors = [];

        // Check if payload has 'dataset' or 'datasets' (keyed by file type: inventory, receipt, etc.)
        $dataset = $request->input('dataset') ?: $request->input('datasets', []);
        if (empty($dataset) && $request->has('file_type') && $request->has('data')) {
            $dataset[$request->input('file_type')] = $request->input('data');
        }

        if (empty($dataset)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid data array found in JSON payload.',
                'tracking_id' => $trackingId
            ], 422);
        }

        foreach ($dataset as $fileType => $rows) {
            $normalizedType = $this->service->detectTableType($fileType);
            if (!$normalizedType) {
                $errors[] = "Unknown dataset type: {$fileType}";
                continue;
            }

            if (!is_array($rows)) continue;

            $res = $this->service->importJsonRecords($normalizedType, $rows, 'api');
            $details[] = $res;
            if ($res['success']) {
                $filesProcessed++;
                $totalRows += $res['rows_imported'];
                if (!empty($res['hosp_code'])) $hospCode = $res['hosp_code'];
                if (!empty($res['period_rpt'])) $periodRpt = $res['period_rpt'];
            } else {
                $errors[] = "{$fileType}: " . ($res['error'] ?? 'Import failed');
            }
        }

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // Write upload log
        try {
            DB::table('moph_f5_upload_logs')->insert([
                'hosp_code' => $hospCode,
                'period_rpt' => $periodRpt,
                'source' => 'api_json',
                'ip_address' => $clientIp,
                'user_agent' => substr($userAgent, 0, 255),
                'files_count' => $filesProcessed,
                'rows_imported' => $totalRows,
                'status' => count($errors) === 0 ? 'success' : ($filesProcessed > 0 ? 'partial' : 'failed'),
                'details' => json_encode(['details' => $details, 'errors' => $errors, 'duration_ms' => $durationMs, 'tracking_id' => $trackingId], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to write moph_f5_upload_logs for JSON: " . $e->getMessage());
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => count($errors) === 0 ? 'บันทึกข้อมูล JSON ผ่าน API สำเร็จ' : 'บันทึกข้อมูลเสร็จสิ้นพร้อมข้อความแจ้งเตือน',
            'tracking_id' => $trackingId,
            'data' => [
                'hosp_code' => $hospCode,
                'period_rpt' => $periodRpt,
                'files_processed' => $filesProcessed,
                'total_rows_imported' => $totalRows,
                'details' => $details,
                'errors' => $errors,
                'duration_ms' => $durationMs,
            ]
        ], count($errors) === 0 ? 200 : 207);
    }

    /**
     * Cybersecurity: Verify API Token / DMSIC Token
     */
    protected function verifyApiToken(Request $request): bool
    {
        $providedToken = $request->bearerToken();
        if (!$providedToken) {
            $providedToken = $request->header('X-API-Key')
                ?: $request->header('Token')
                ?: $request->input('dmsic_token')
                ?: $request->input('token')
                ?: $request->input('api_key');
        }

        if (empty($providedToken)) {
            return false;
        }

        // 1. Primary Token Check (from .env or config)
        $configuredToken = env('MOPH_F5_API_TOKEN');
        if (!empty($configuredToken) && hash_equals($configuredToken, (string)$providedToken)) {
            return true;
        }

        // 2. Allow in local environment if configured
        if (app()->environment('local') && !empty($providedToken)) {
            return true;
        }

        // 3. DMSIC Specific Token Check (from .env)
        $dmsicConfigToken = env('MOPH_F5_DMSIC_TOKEN');
        if (!empty($dmsicConfigToken) && hash_equals($dmsicConfigToken, (string)$providedToken)) {
            return true;
        }

        // 3. DMSIC JWT Token Format Verification
        // If DMSIC token format (starts with eyJ... having 3 segments), verify JWT payload structure
        if (env('MOPH_F5_ACCEPT_DMSIC_TOKEN', true)) {
            $parts = explode('.', (string)$providedToken);
            if (count($parts) === 3) {
                $payloadJson = @base64_decode(strtr($parts[1], '-_', '+/'));
                if ($payloadJson) {
                    $payload = @json_decode($payloadJson, true);
                    if (is_array($payload)) {
                        // Valid DMSIC JWT Token payload structure
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Cybersecurity: IP Whitelisting Check
     */
    protected function isIpAllowed(string $ip): bool
    {
        $allowedIps = env('MOPH_F5_ALLOWED_IPS');
        if (empty($allowedIps) || $allowedIps === '*') {
            return true;
        }

        $list = array_map('trim', explode(',', $allowedIps));
        foreach ($list as $allowed) {
            if ($allowed === $ip) return true;
            if (str_contains($allowed, '*')) {
                $pattern = '/^' . str_replace(['.', '*'], ['\.', '.*'], $allowed) . '$/';
                if (preg_match($pattern, $ip)) return true;
            }
        }

        return false;
    }

    /**
     * Log security events
     */
    protected function logSecurityEvent(string $ip, string $userAgent, string $status, string $reason, string $trackingId): void
    {
        try {
            DB::table('moph_f5_upload_logs')->insert([
                'hosp_code' => null,
                'period_rpt' => null,
                'source' => 'api_security',
                'ip_address' => $ip,
                'user_agent' => substr($userAgent, 0, 255),
                'files_count' => 0,
                'rows_imported' => 0,
                'status' => $status,
                'details' => json_encode(['reason' => $reason, 'tracking_id' => $trackingId], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silent fail for log write
        }
    }
}
