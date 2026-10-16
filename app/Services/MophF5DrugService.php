<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class MophF5DrugService
{
    /**
     * Import multiple MOPH 5 files (.xlsx or .zip)
     *
     * @param array $filePaths Array of absolute file paths
     * @param string $source Source identifier ('web_upload', 'api', etc.)
     * @param string|null $ipAddress Client IP address for audit log
     * @param string|null $userAgent Client User Agent for audit log
     * @return array Summary of import results
     */
    public function importFiles(array $filePaths, string $source = 'web_upload', ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $results = [
            'total_files' => count($filePaths),
            'files_processed' => 0,
            'total_rows_imported' => 0,
            'details' => [],
            'errors' => [],
        ];

        $latestHospCode = '';
        $latestPeriodRpt = '';

        foreach ($filePaths as $filePath) {
            if (!file_exists($filePath)) {
                $results['errors'][] = "ไม่พบไฟล์: " . basename($filePath);
                continue;
            }

            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($ext === 'zip') {
                $zipRes = $this->importZipFile($filePath, $source, $ipAddress, $userAgent);
                $results['files_processed'] += $zipRes['files_processed'];
                $results['total_rows_imported'] += $zipRes['total_rows_imported'];
                $results['details'] = array_merge($results['details'], $zipRes['details']);
                $results['errors'] = array_merge($results['errors'], $zipRes['errors']);
                if ($zipRes['hosp_code']) $latestHospCode = $zipRes['hosp_code'];
                if ($zipRes['period_rpt']) $latestPeriodRpt = $zipRes['period_rpt'];
            } elseif ($ext === 'xlsx') {
                $res = $this->importXlsxFile($filePath);
                $results['details'][] = $res;
                if ($res['success']) {
                    $results['files_processed']++;
                    $results['total_rows_imported'] += $res['rows_imported'];
                    if (!empty($res['hosp_code'])) $latestHospCode = $res['hosp_code'];
                    if (!empty($res['period_rpt'])) $latestPeriodRpt = $res['period_rpt'];
                } else {
                    $results['errors'][] = "{$res['filename']}: {$res['error']}";
                }
            } elseif ($ext === 'txt' || $ext === 'csv') {
                $res = $this->importTextFile($filePath);
                $results['details'][] = $res;
                if ($res['success']) {
                    $results['files_processed']++;
                    $results['total_rows_imported'] += $res['rows_imported'];
                    if (!empty($res['hosp_code'])) $latestHospCode = $res['hosp_code'];
                    if (!empty($res['period_rpt'])) $latestPeriodRpt = $res['period_rpt'];
                } else {
                    $results['errors'][] = "{$res['filename']}: {$res['error']}";
                }
            } else {
                $results['errors'][] = "รูปแบบไฟล์ไม่รองรับ (" . basename($filePath) . ") ต้องเป็น .xlsx, .zip, .txt หรือ .csv";
            }
        }

        // Record log with cyber audit info
        try {
            DB::table('moph_f5_upload_logs')->insert([
                'hosp_code' => $latestHospCode,
                'period_rpt' => $latestPeriodRpt,
                'source' => $source,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
                'files_count' => $results['files_processed'],
                'rows_imported' => $results['total_rows_imported'],
                'status' => count($results['errors']) === 0 ? 'success' : (count($results['details']) > 0 ? 'partial' : 'failed'),
                'details' => json_encode($results['details'], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to write moph_f5_upload_logs: " . $e->getMessage());
        }

        return $results;
    }

    /**
     * Import a .zip archive containing MOPH 5 files
     */
    public function importZipFile(string $zipPath, string $source = 'web_upload', ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return [
                'files_processed' => 0,
                'total_rows_imported' => 0,
                'details' => [],
                'errors' => ["ไม่สามารถเปิดไฟล์ Zip: " . basename($zipPath)],
                'hosp_code' => '',
                'period_rpt' => ''
            ];
        }

        $tempDir = storage_path('app/temp_moph_f5_' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        try {
            $zip->extractTo($tempDir);
            $zip->close();

            $extractedFiles = array_merge(
                glob($tempDir . '/*.xlsx') ?: [],
                glob($tempDir . '/*.txt') ?: [],
                glob($tempDir . '/*.csv') ?: []
            );

            $subResults = $this->importFiles($extractedFiles, $source, $ipAddress, $userAgent);

            return [
                'files_processed' => $subResults['files_processed'],
                'total_rows_imported' => $subResults['total_rows_imported'],
                'details' => $subResults['details'],
                'errors' => $subResults['errors'],
                'hosp_code' => $subResults['details'][0]['hosp_code'] ?? '',
                'period_rpt' => $subResults['details'][0]['period_rpt'] ?? ''
            ];
        } finally {
            // Clean up temp directory
            foreach (glob($tempDir . '/*') as $f) {
                @unlink($f);
            }
            @rmdir($tempDir);
        }
    }

    /**
     * Parse and import a single .xlsx file using native ZipArchive + SimpleXML
     */
    public function importXlsxFile(string $filePath): array
    {
        $filename = basename($filePath);
        $fileType = $this->detectTableType($filename);

        if (!$fileType) {
            return [
                'filename' => $filename,
                'file_type' => 'unknown',
                'success' => false,
                'rows_imported' => 0,
                'error' => 'ไม่สามารถระบุประเภทแฟ้มยาจากชื่อไฟล์ได้ (ต้องมีคำว่า druglist, purchaseplan, receipt, distribution หรือ inventory)'
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'success' => false,
                'rows_imported' => 0,
                'error' => 'ไม่สามารถเปิดอ่านไฟล์ XLSX ได้ (ไฟล์อาจเสียหาย)'
            ];
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $sharedStringsEntry = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsEntry !== false) {
            $ssXml = @simplexml_load_string($sharedStringsEntry);
            if ($ssXml && isset($ssXml->si)) {
                foreach ($ssXml->si as $si) {
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string)$si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $r) {
                            $text .= (string)$r->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        // 2. Find worksheet
        $sheetXmlContent = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (str_starts_with($name, 'xl/worksheets/') && str_ends_with($name, '.xml')) {
                $sheetXmlContent = $zip->getFromIndex($i);
                break;
            }
        }

        $zip->close();

        if ($sheetXmlContent === false) {
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'success' => false,
                'rows_imported' => 0,
                'error' => 'ไม่พบข้อมูล Worksheet ภายในไฟล์ XLSX'
            ];
        }

        $sheetXml = @simplexml_load_string($sheetXmlContent);
        if (!$sheetXml || !isset($sheetXml->sheetData->row)) {
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'success' => false,
                'rows_imported' => 0,
                'error' => 'Worksheet ว่างเปล่าหรือรูปแบบ XML ไม่ถูกต้อง'
            ];
        }

        // 3. Parse Header and Rows
        $headers = [];
        $records = [];
        $hospCode = '';
        $periodRpt = '';
        $rowIndex = 0;

        foreach ($sheetXml->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $cellRef = (string)$cell['r'];
                preg_match('/^([A-Z]+)/', $cellRef, $colMatches);
                $colIndex = $this->columnLetterToIndex($colMatches[1] ?? 'A');

                $val = (string)$cell->v;
                if ((string)$cell['t'] === 's' && isset($sharedStrings[(int)$val])) {
                    $val = $sharedStrings[(int)$val];
                }

                $rowData[$colIndex] = trim($val);
            }

            if ($rowIndex === 0) {
                // Header row
                foreach ($rowData as $idx => $headerName) {
                    $headers[$idx] = strtoupper(trim($headerName));
                }
            } else {
                // Data row
                $rowAssoc = [];
                foreach ($headers as $idx => $headerName) {
                    $rowAssoc[$headerName] = $rowData[$idx] ?? '';
                }

                if (!empty($rowAssoc['HOSP_CODE'])) {
                    $hospCode = $rowAssoc['HOSP_CODE'];
                }
                if (!empty($rowAssoc['PERIOD_RPT'])) {
                    $periodRpt = $rowAssoc['PERIOD_RPT'];
                }

                $mappedRecord = $this->mapRecordToTable($fileType, $rowAssoc);
                if ($mappedRecord) {
                    $records[] = $mappedRecord;
                }
            }
            $rowIndex++;
        }

        if (empty($records)) {
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'hosp_code' => $hospCode,
                'period_rpt' => $periodRpt,
                'success' => true,
                'rows_imported' => 0,
                'message' => 'ไม่มีข้อมูลในไฟล์ (ไฟล์ว่าง)'
            ];
        }

        // 4. Batch Upsert (Update or Insert) into MySQL using Official Primary Keys from PDF
        $tableName = 'moph_f5_' . $fileType;
        $uniqueKeys = $this->getUniqueKeysForTable($fileType);

        try {
            // Process in chunks of 250 for high performance bulk updateOrInsert
            $chunkSize = 250;
            $chunks = array_chunk($records, $chunkSize);
            foreach ($chunks as $chunk) {
                $first = $chunk[0];
                $updateColumns = array_values(array_diff(array_keys($first), array_merge($uniqueKeys, ['created_at'])));
                DB::table($tableName)->upsert($chunk, $uniqueKeys, $updateColumns);
            }

            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'hosp_code' => $hospCode,
                'period_rpt' => $periodRpt,
                'success' => true,
                'rows_imported' => count($records),
                'table' => $tableName
            ];
        } catch (\Exception $e) {
            Log::error("Error importing {$tableName}: " . $e->getMessage());
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'success' => false,
                'rows_imported' => 0,
                'error' => "เกิดข้อผิดพลาดในการบันทึกลงตาราง {$tableName}: " . $e->getMessage()
            ];
        }
    }

    /**
     * Import a text/CSV file (pipe |, tab, or comma separated)
     */
    public function importTextFile(string $filePath): array
    {
        $filename = basename($filePath);
        $fileType = $this->detectTableType($filename);

        if (!$fileType) {
            return [
                'filename' => $filename,
                'file_type' => 'unknown',
                'success' => false,
                'rows_imported' => 0,
                'error' => 'ไม่สามารถระบุประเภทแฟ้มยาจากชื่อไฟล์ได้'
            ];
        }

        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'success' => false,
                'rows_imported' => 0,
                'error' => 'ไม่สามารถเปิดอ่านไฟล์ Text ได้'
            ];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'success' => true,
                'rows_imported' => 0,
                'message' => 'ไฟล์ว่างเปล่า'
            ];
        }

        // Remove UTF-8 BOM
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', trim($firstLine));

        // Detect delimiter: pipe, tab, or comma
        $delimiter = '|';
        if (str_contains($firstLine, '|')) {
            $delimiter = '|';
        } elseif (str_contains($firstLine, "\t")) {
            $delimiter = "\t";
        } elseif (str_contains($firstLine, ',')) {
            $delimiter = ',';
        }

        $headers = array_map(function ($h) {
            return strtoupper(trim(trim($h, '"\'')));
        }, explode($delimiter, $firstLine));

        $records = [];
        $hospCode = '';
        $periodRpt = '';

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = explode($delimiter, $line);
            $rowAssoc = [];
            foreach ($headers as $idx => $hName) {
                $val = isset($parts[$idx]) ? trim(trim($parts[$idx], '"\'')) : '';
                $rowAssoc[$hName] = $val;
            }

            if (!empty($rowAssoc['HOSP_CODE']) && empty($hospCode)) {
                $hospCode = $rowAssoc['HOSP_CODE'];
            }
            if (!empty($rowAssoc['PERIOD_RPT']) && empty($periodRpt)) {
                $periodRpt = $rowAssoc['PERIOD_RPT'];
            }

            $mapped = $this->mapRecordToTable($fileType, $rowAssoc);
            if ($mapped) {
                $records[] = $mapped;
            }
        }
        fclose($handle);

        if (empty($records)) {
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'hosp_code' => $hospCode,
                'period_rpt' => $periodRpt,
                'success' => true,
                'rows_imported' => 0,
                'message' => 'ไม่มีข้อมูลในไฟล์'
            ];
        }

        $tableName = 'moph_f5_' . $fileType;
        $uniqueKeys = $this->getUniqueKeysForTable($fileType);

        try {
            $chunks = array_chunk($records, 250);
            foreach ($chunks as $chunk) {
                $first = $chunk[0];
                $updateColumns = array_values(array_diff(array_keys($first), array_merge($uniqueKeys, ['created_at'])));
                DB::table($tableName)->upsert($chunk, $uniqueKeys, $updateColumns);
            }

            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'hosp_code' => $hospCode,
                'period_rpt' => $periodRpt,
                'success' => true,
                'rows_imported' => count($records),
                'table' => $tableName
            ];
        } catch (\Exception $e) {
            Log::error("Error importing {$tableName} from text: " . $e->getMessage());
            return [
                'filename' => $filename,
                'file_type' => $fileType,
                'success' => false,
                'rows_imported' => 0,
                'error' => "เกิดข้อผิดพลาดในการบันทึกลงตาราง {$tableName}: " . $e->getMessage()
            ];
        }
    }

    /**
     * Import JSON records directly for API
     */
    public function importJsonRecords(string $fileType, array $rawRows, string $source = 'api'): array
    {
        $uniqueKeys = $this->getUniqueKeysForTable($fileType);
        $tableName = 'moph_f5_' . $fileType;
        $records = [];
        $hospCode = '';
        $periodRpt = '';

        foreach ($rawRows as $row) {
            if (!is_array($row)) continue;
            $upperRow = [];
            foreach ($row as $k => $v) {
                $upperRow[strtoupper((string)$k)] = $v;
            }
            if (!empty($upperRow['HOSP_CODE']) && empty($hospCode)) {
                $hospCode = $upperRow['HOSP_CODE'];
            }
            if (!empty($upperRow['PERIOD_RPT']) && empty($periodRpt)) {
                $periodRpt = $upperRow['PERIOD_RPT'];
            }
            $mapped = $this->mapRecordToTable($fileType, $upperRow);
            if ($mapped) {
                $records[] = $mapped;
            }
        }

        if (empty($records)) {
            return [
                'file_type' => $fileType,
                'success' => true,
                'rows_imported' => 0,
                'message' => 'ไม่มีข้อมูลที่นำเข้าได้'
            ];
        }

        try {
            $chunks = array_chunk($records, 250);
            foreach ($chunks as $chunk) {
                $first = $chunk[0];
                $updateColumns = array_values(array_diff(array_keys($first), array_merge($uniqueKeys, ['created_at'])));
                DB::table($tableName)->upsert($chunk, $uniqueKeys, $updateColumns);
            }

            return [
                'file_type' => $fileType,
                'hosp_code' => $hospCode,
                'period_rpt' => $periodRpt,
                'success' => true,
                'rows_imported' => count($records),
                'table' => $tableName
            ];
        } catch (\Exception $e) {
            Log::error("Error importing {$tableName} from JSON: " . $e->getMessage());
            return [
                'file_type' => $fileType,
                'success' => false,
                'rows_imported' => 0,
                'error' => "เกิดข้อผิดพลาดในการบันทึกลงตาราง {$tableName}: " . $e->getMessage()
            ];
        }
    }

    /**
     * Map Excel row columns to table schema
     */
    protected function mapRecordToTable(string $fileType, array $row): ?array
    {
        $now = now();

        switch ($fileType) {
            case 'druglist':
                if (empty($row['WORKING_CODE'])) return null;
                return [
                    'hosp_code'    => $row['HOSP_CODE'] ?? '',
                    'working_code' => $row['WORKING_CODE'],
                    'generic_name' => $row['GENERIC_NAME'] ?? '',
                    'gpuid'        => $row['GPUID'] ?? '',
                    'nlem'         => $row['NLEM'] ?? '',
                    'product_cat'  => $row['PRODUCT_CAT'] ?? '',
                    'base_unit'    => $row['BASE_UNIT'] ?? '',
                    'status'       => $row['STATUS'] ?? '',
                    'date_status'  => $row['DATE_STATUS'] ?? '',
                    'period_rpt'   => $row['PERIOD_RPT'] ?? '',
                    'date_send'    => $row['DATE_SEND'] ?? '',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];

            case 'purchaseplan':
                if (empty($row['WORKING_CODE'])) return null;
                return [
                    'hosp_code'          => $row['HOSP_CODE'] ?? '',
                    'yearbudget'         => $row['YEARBUDGET'] ?? '',
                    'working_code'       => $row['WORKING_CODE'],
                    'generic_name'       => $row['GENERIC_NAME'] ?? '',
                    'gpuid'              => $row['GPUID'] ?? '',
                    'nlem'               => $row['NLEM'] ?? '',
                    'qty_use_year3'      => (float)($row['QTY_USE_YEAR3'] ?? 0),
                    'qty_use_year2'      => (float)($row['QTY_USE_YEAR2'] ?? 0),
                    'qty_use_year1'      => (float)($row['QTY_USE_YEAR1'] ?? 0),
                    'qty_this_year'      => (float)($row['QTY_THIS_YEAR'] ?? 0),
                    'pack_size'          => (int)($row['PACK_SIZE'] ?? 1) ?: 1,
                    'base_unit'          => $row['BASE_UNIT'] ?? '',
                    'pack_cost'          => (float)($row['PACK_COST'] ?? 0),
                    'value_this_year'    => (float)($row['VALUE_THIS_YEAR'] ?? 0),
                    'qty_plan_trimes1'   => (float)($row['QTY_PLAN_TRIMES1'] ?? 0),
                    'qty_plan_trimes2'   => (float)($row['QTY_PLAN_TRIMES2'] ?? 0),
                    'qty_plan_trimes3'   => (float)($row['QTY_PLAN_TRIMES3'] ?? 0),
                    'qty_plan_trimes4'   => (float)($row['QTY_PLAN_TRIMES4'] ?? 0),
                    'period_rpt'         => $row['PERIOD_RPT'] ?? '',
                    'date_send'          => $row['DATE_SEND'] ?? '',
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];

            case 'receipt':
                if (empty($row['WORKING_CODE']) && empty($row['RCV_NO'])) return null;
                return [
                    'hosp_code'       => $row['HOSP_CODE'] ?? '',
                    'working_code'    => $row['WORKING_CODE'] ?? '',
                    'trade_name'      => $row['TRADE_NAME'] ?? '',
                    'tpuid'           => $row['TPUID'] ?? '',
                    'vendor_name'     => $row['VENDOR_NAME'] ?? '',
                    'vendor_tax_id'   => $row['VENDOR_TAX_ID'] ?? '',
                    'qty_rcv'         => (float)($row['QTY_RCV'] ?? 0),
                    'pack_size'       => (int)($row['PACK_SIZE'] ?? 1) ?: 1,
                    'base_unit'       => $row['BASE_UNIT'] ?? '',
                    'pack_cost'       => (float)($row['PACK_COST'] ?? 0),
                    'total_value'     => (float)($row['TOTAL_VALUE'] ?? 0),
                    'lot_no'          => $row['LOT_NO'] ?? '',
                    'expire_date'     => $row['EXPIRE_DATE'] ?? '',
                    'rcv_no'          => $row['RCV_NO'] ?? '',
                    'po_no'           => $row['PO_NO'] ?? '',
                    'cnt_no'          => $row['CNT_NO'] ?? '',
                    'date_rcv'        => $row['DATE_RCV'] ?? '',
                    'buy_method_id'   => $row['BUY_METHOD_ID'] ?? '',
                    'co_purchase_id'  => $row['CO_PURCHASE_ID'] ?? '',
                    'rcv_flag'        => $row['RCV_FLAG'] ?? '',
                    'period_rpt'      => $row['PERIOD_RPT'] ?? '',
                    'date_send'       => $row['DATE_SEND'] ?? '',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

            case 'distribution':
                if (empty($row['WORKING_CODE'])) return null;
                return [
                    'hosp_code'      => $row['HOSP_CODE'] ?? '',
                    'working_code'   => $row['WORKING_CODE'],
                    'trade_name'     => $row['TRADE_NAME'] ?? '',
                    'tpuid'          => $row['TPUID'] ?? '',
                    'qty_dis'        => (float)($row['QTY_DIS'] ?? 0),
                    'pack_size'      => (int)($row['PACK_SIZE'] ?? 1) ?: 1,
                    'base_unit'      => $row['BASE_UNIT'] ?? '',
                    'value'          => (float)($row['VALUE'] ?? 0),
                    'dis_dept_group' => $row['DIS_DEPT_GROUP'] ?? '',
                    'period_rpt'     => $row['PERIOD_RPT'] ?? '',
                    'date_send'      => $row['DATE_SEND'] ?? '',
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];

            case 'inventory':
                if (empty($row['WORKING_CODE'])) return null;
                return [
                    'hosp_code'     => $row['HOSP_CODE'] ?? '',
                    'working_code'  => $row['WORKING_CODE'],
                    'trade_name'    => $row['TRADE_NAME'] ?? '',
                    'tpuid'         => $row['TPUID'] ?? '',
                    'vendor_name'   => $row['VENDOR_NAME'] ?? '',
                    'vendor_tax_id' => $row['VENDOR_TAX_ID'] ?? '',
                    'qty_onhand'    => (float)($row['QTY_ONHAND'] ?? 0),
                    'pack_size'     => (int)($row['PACK_SIZE'] ?? 1) ?: 1,
                    'base_unit'     => $row['BASE_UNIT'] ?? '',
                    'pack_cost'     => (float)($row['PACK_COST'] ?? 0),
                    'value_onhand'  => (float)($row['VALUE_ONHAND'] ?? 0),
                    'lot_no'        => $row['LOT_NO'] ?? '',
                    'expire_date'   => $row['EXPIRE_DATE'] ?? '',
                    'date_onhand'   => $row['DATE_ONHAND'] ?? '',
                    'date_send'     => $row['DATE_SEND'] ?? '',
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

            default:
                return null;
        }
    }

    /**
     * Official Primary / Unique keys from ประกาศมาตรฐาน 5 แฟ้ม.pdf
     */
    protected function getUniqueKeysForTable(string $fileType): array
    {
        switch ($fileType) {
            case 'druglist':
                // PDF page 3: HOSP_CODE, WORKING_CODE
                return ['hosp_code', 'working_code'];
            case 'purchaseplan':
                // PDF page 6, 9: HOSP_CODE, YEARBUDGET, WORKING_CODE, PERIOD_RPT
                return ['hosp_code', 'yearbudget', 'working_code', 'period_rpt'];
            case 'receipt':
                // PDF page 10, 12, 13: HOSP_CODE, RCV_NO, WORKING_CODE, LOT_NO, EXPIRE_DATE, RCV_FLAG
                return ['hosp_code', 'rcv_no', 'working_code', 'lot_no', 'expire_date', 'rcv_flag'];
            case 'distribution':
                // PDF page 14, 15: HOSP_CODE, WORKING_CODE, TRADE_NAME, PACK_SIZE, DIS_DEPT_GROUP, PERIOD_RPT
                return ['hosp_code', 'working_code', 'trade_name', 'pack_size', 'dis_dept_group', 'period_rpt'];
            case 'inventory':
                // PDF page 16, 18: HOSP_CODE, WORKING_CODE, LOT_NO, EXPIRE_DATE, DATE_ONHAND
                return ['hosp_code', 'working_code', 'lot_no', 'expire_date', 'date_onhand'];
            default:
                return ['id'];
        }
    }

    /**
     * Detect file type from filename
     */
    public function detectTableType(string $filename): ?string
    {
        $fn = strtolower($filename);
        if (str_contains($fn, 'druglist') || str_contains($fn, 'druglis')) return 'druglist';
        if (str_contains($fn, 'purchaseplan') || str_contains($fn, 'purchase')) return 'purchaseplan';
        if (str_contains($fn, 'receipt') || str_contains($fn, 'receip')) return 'receipt';
        if (str_contains($fn, 'distribution') || str_contains($fn, 'distrib')) return 'distribution';
        if (str_contains($fn, 'inventory') || str_contains($fn, 'invento')) return 'inventory';
        return null;
    }

    /**
     * Convert Excel column letter (e.g. 'A', 'Z', 'AA') to 0-based integer
     */
    protected function columnLetterToIndex(string $col): int
    {
        $index = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - 64);
        }
        return $index - 1;
    }
}
