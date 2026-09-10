<?php

namespace App\Services\Ai;

use App\Models\AiSetting;
use App\Services\Ai\Contracts\LlmProviderInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;

class TextToSqlService
{
    protected LlmProviderInterface $provider;

    public function __construct(?LlmProviderInterface $provider = null)
    {
        $this->provider = $provider ?? AiManager::getActiveProvider();
    }

    /**
     * Determine target database connection based on choice or auto detection.
     */
    public function resolveTargetDatabase(string $targetDb, string $question, ?string $previousDb = null): string
    {
        $targetDb = strtolower(trim($targetDb));

        if (in_array($targetDb, ['hosxp', 'backoffice', 'mysql'])) {
            return $targetDb;
        }

        // Auto-detect from keywords
        $q = mb_strtolower($question);
        
        $boKeywords = [
            'พัสดุ', 'บุคลากร', 'พนักงาน', 'เจ้าหน้าที่', 'วันลา', 'เงินเดือน', 'ครุภัณฑ์', 'เบิก', 'สัญญา', 'จัดซื้อ', 'จัดจ้าง', 'hr', 'สารบรรณ', 'ลาป่วย', 'ลากิจ', 'hrd_', 'ฝ่าย', 'กลุ่มงาน',
            'คลัง', 'คลังพัสดุ', 'คลังยา', 'สต็อก', 'คงคลัง', 'เบิกพัสดุ', 'เบิกยา', 'รับเข้าคลัง', 'จ่ายออกจากคลัง', 'คลังย่อย',
            'ซ่อม', 'แจ้งซ่อม', 'ช่าง', 'คอมพิวเตอร์', 'ไอที', 'งานซ่อม', 'เครื่องมือแพทย์',
            'รถยนต์', 'ขอใช้รถ', 'รถตู้', 'รถส่งต่อ', 'ยานพาหนะ', 'จองห้องประชุม', 'ห้องประชุม',
            'อุบัติการณ์', 'ความเสี่ยง', 'จ่ายกลาง', 'ค่าเสื่อม', 'ทะเบียนครุภัณฑ์', 'แทงจำหน่าย'
        ];
        foreach ($boKeywords as $kw) {
            if (mb_strpos($q, $kw) !== false) {
                return 'backoffice';
            }
        }

        $sdKeywords = ['smartdata', 'สิทธิ์การใช้งาน', 'คลังความรู้', 'ผู้ใช้งานระบบ', 'ระบบยืม', 'ร้องเรียน', 'users'];
        foreach ($sdKeywords as $kw) {
            if (mb_strpos($q, $kw) !== false) {
                return 'mysql';
            }
        }

        // HOSxP explicit keywords (All clinical departments, master data, 43 files & hospital workflows)
        $hosxpKeywords = [
            'คนไข้', 'ผู้ป่วย', 'opd', 'ipd', 'er', 'vn', 'hn', 'an', 'โรค', 'icd', 'pttype', 'สิทธิ', 'ค่ารักษา',
            'refer', 'admit', 'เตียง', 'คลินิก', 'วอร์ด', 'ยา', 'หมอ', 'แพทย์', 'ทันตกรรม', 'ทำฟัน', 'ฟัน',
            'กายภาพ', 'กายภาพบำบัด', 'แพทย์แผนไทย', 'แผนไทย', 'คลอด', 'ห้องคลอด', 'ทารก', 'ฉุกเฉิน', 'อุบัติเหตุ',
            'ems', 'แล็บ', 'lab', 'xray', 'เอ็กซเรย์', 'ct scan', 'เสียชีวิต', 'ตาย', 'นัด', 'ใบนัด', 'นัดหมาย',
            'แพ้ยา', 'เบาหวาน', 'ความดัน', 'ไต', 'stroke', 'สโตรก', 'sepsis', 'หัวใจ', 'ปอด', 'รีเฟอร์', 'ส่งต่อ',
            'ครองเตียง', 'ชาร์ต', 'chart', 'revisit', 'readmit', 'cmi', 'adjrw', 'คิว', 'วัณโรค', 'หอบหืด',
            '43 แฟ้ม', '43แฟ้ม', 'provis', 'provis_', 'tmt', 'billcode', 'adp', 'eclaim', 'e-claim', 'csop', 'fdh',
            'ราคายา', 'ค่ายา', 'ค่าบริการ', 'บัญชียา', 'ed', 'ned', 'หัตถการ', 'icd9', 'lab_items', 'opduser',
            'type area', 'typearea', 'ประชากร', 'วัคซีน', 'epi', 'chronic', 'labfu', 'disability', 'ความพิการ',
            'เลข ว', 'ตั้งค่าสิทธิ', 'หมวดรายได้', 'ต้นทุนยา', 'ราคาขายยา',
            'pttype_price_group', 'price_group', 'price_type', 'กลุ่มราคา', 'ราคาตามสิทธิ', 'unitprice2', 'unitprice3', 'price2', 'price3', 'ราคานอกเวลา', 'ราคาต่างชาติ',
            'pttype_items_price', 'ราคาเฉพาะสิทธิ', 'ตั้งราคาเฉพาะรายการ'
        ];
        foreach ($hosxpKeywords as $kw) {
            if (mb_strpos($q, $kw) !== false) {
                return 'hosxp';
            }
        }

        // If no explicit DB keyword matched and we have a previous DB from an active multi-turn session, inherit it!
        if (!empty($previousDb) && in_array(strtolower($previousDb), ['hosxp', 'backoffice', 'mysql'])) {
            return strtolower($previousDb);
        }

        return 'hosxp';
    }

    /**
     * Generate SQL from natural language prompt and execute it safely.
     * Supports multi-turn conversation history for context-aware follow-ups.
     */
    public function query(string $question, string $targetDb = 'auto', array $history = []): array
    {
        // Extract previous database connection from history if available
        $previousDb = null;
        if (!empty($history)) {
            foreach (array_reverse($history) as $h) {
                if (!empty($h['target_db'])) {
                    $previousDb = $h['target_db'];
                    break;
                }
            }
        }

        $connection = $this->resolveTargetDatabase($targetDb, $question, $previousDb);
        $schemaContext = $this->getSchemaContext($connection);

        // Ask LLM to generate SQL
        $systemPrompt = "คุณคือ Expert Database Architect & SQL Generator สำหรับระบบโรงพยาบาล
ฐานข้อมูลเป้าหมายคือ MySQL: [Connection: {$connection}]
กฎสำคัญสูงสุด:
1. ตอบกลับเป็นคำสั่ง SQL `SELECT` ที่สมบูรณ์เพียงคำสั่งเดียวเท่านั้น โดยต้องอยู่ใน Markdown Block: ```sql ... ```
2. ห้ามใช้คำสั่ง DDL/DML เด็ดขาด (ห้าม INSERT, UPDATE, DELETE, DROP, ALTER, TRUNCATE)
3. สำหรับวันที่ หากระบุ 'วันนี้' ให้ใช้ CURDATE() หากระบุปีงบประมาณไทย เช่น 2568 (วันที่ 2024-10-01 ถึง 2025-09-30) หรือแปลงพุทธศักราชเป็น ค.ศ. (ปี พ.ศ. - 543)
4. กำหนด LIMIT สูงสุดไม่เกิน 100 แถวเสมอ (เช่น LIMIT 100)
5. ใช้ชื่อฟิลด์ภาษาอังกฤษตาม Schema ที่ให้มาด้านล่าง
6. เขียนคำสั่ง SQL ที่มีประสิทธิภาพ พร้อมตั้งชื่อ Alias คอลัมน์เป็นภาษาไทยที่อ่านง่าย เช่น `count(*) as 'จำนวนคน'`
7. หากคำถามถามเรื่องประเภท หรือถามต่อเนื่องว่า 'กี่ประเภท' หรือ 'แยกตาม...' ให้เขียนคำสั่งที่แจกแจงตามประเภทนั้นๆ พร้อมนับจำนวน (GROUP BY และ COUNT) เพื่อให้ผู้ใช้เห็นรายละเอียดและจำนวนครบถ้วน
8. ห้ามใส่เครื่องหมายจุลภาคหรือฟังก์ชัน FORMAT(...) กับรหัสประจำตัว (Identifiers) เด็ดขาด เช่น HN, AN, VN, CID, รหัสยา icode, รหัสแล็บ, เลขเตียง, ปี พ.ศ. เพราะไม่ใช่จำนวนนับหรือราคาเงิน ให้คงค่าเดิมไว้เสมอ

Schema ข้อมูลที่สามารถใช้ได้:
{$schemaContext}
";

        // Build user prompt with multi-turn history if present
        $userPrompt = "แปลงคำถามนี้เป็น SQL: \"{$question}\"";
        if (!empty($history)) {
            $contextStr = "บริบทการสนทนาก่อนหน้านี้ในเซสชันนี้:\n";
            foreach ($history as $h) {
                $roleName = ($h['role'] === 'user') ? 'ผู้ใช้' : 'Copilot';
                $contextStr .= "- {$roleName}: " . ($h['content'] ?? '') . "\n";
                if (!empty($h['sql'])) {
                    $contextStr .= "  [SQL ก่อนหน้า]: {$h['sql']}\n";
                }
            }
            $userPrompt = "{$contextStr}\nคำถามต่อเนื่องล่าสุดของผู้ใช้: \"{$question}\"\n(คำแนะนำ: กรุณาตีความคำถามล่าสุดโดยอิงจากเอนทิตีหรือหัวข้อที่เพิ่งพูดถึงก่อนหน้า เช่น หากถามว่า 'กี่ประเภท', 'แยกตามกลุ่มงาน', 'มีใครบ้าง' หมายถึงหัวข้อที่สนทนาอยู่ก่อนหน้า แล้วสร้าง SQL ที่ตอบคำถามต่อเนื่องนี้ได้อย่างถูกต้อง)";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        $rawResponse = $this->provider->chat($messages, ['temperature' => 0.1]);

        // Extract SQL from response
        $sql = $this->extractSql($rawResponse);

        if (empty($sql)) {
            $politeMsg = "ขออภัยครับ ระบบไม่สามารถแปลงคำถามนี้เป็นคำสั่งสืบค้นได้ครับ 🙏\n\n💡 *คำแนะนำ: กรุณาระบุรายละเอียดเพิ่มเติม เช่น ชื่อแผนก ตึกผู้ป่วย หรือช่วงเวลาที่ต้องการสืบค้นครับ*";
            return [
                'success' => false,
                'target_db' => $connection,
                'polite_message' => $politeMsg,
                'error' => $politeMsg,
                'raw_reply' => $rawResponse
            ];
        }

        // Validate SQL safety
        $safetyCheck = $this->validateSqlSafety($sql);
        if (!$safetyCheck['safe']) {
            $politeMsg = "ขออภัยครับ คำถามนี้สร้างคำสั่งสืบค้นที่ไม่ผ่านเกณฑ์ความปลอดภัยของระบบครับ 🙏\n\n💡 *ระบบอนุญาตเฉพาะคำสั่งสืบค้นข้อมูล (SELECT) ที่ปลอดภัยเท่านั้นครับ*";
            return [
                'success' => false,
                'target_db' => $connection,
                'sql' => $sql,
                'polite_message' => $politeMsg,
                'error' => $politeMsg,
                'raw_error' => $safetyCheck['reason']
            ];
        }

        // Auto append LIMIT 100 if not present
        if (!preg_match('/\bLIMIT\s+\d+/i', $sql)) {
            $sql = rtrim(trim($sql), ';') . ' LIMIT 100;';
        }

        // Execute SQL on connection
        try {
            $startTime = microtime(true);
            $results = DB::connection($connection)->select($sql);
            $executionMs = round((microtime(true) - $startTime) * 1000);

            // Convert to array
            $rows = array_map(function ($item) {
                return (array) $item;
            }, $results);

            $columns = !empty($rows) ? array_keys($rows[0]) : [];

            // Generate Thai explanation / summary
            $explanation = $this->generateSummary($question, $sql, $rows, $connection);

            return [
                'success' => true,
                'target_db' => $connection,
                'sql' => $sql,
                'columns' => $columns,
                'rows' => $rows,
                'count' => count($rows),
                'execution_ms' => $executionMs,
                'explanation' => $explanation,
            ];
        } catch (Exception $e) {
            $rawError = $e->getMessage();
            $dbName = strtoupper($connection);
            $politeMsg = "ขออภัยครับ ระบบไม่พบข้อมูลหรือเกิดข้อขัดข้องในการสืบค้นจากฐานข้อมูล {$dbName} ในขณะนี้ครับ 🙏\n\n💡 *คำแนะนำ: โครงสร้างคำถามอาจยังไม่สอดคล้องกับตารางข้อมูล ท่านสามารถลองปรับเปลี่ยนคำค้นหา หรือสอบถามเจ้าหน้าที่ผู้ดูแลระบบ (Admin) เพื่อตรวจสอบคำสั่งสืบค้นได้ครับ*";

            return [
                'success' => false,
                'target_db' => $connection,
                'sql' => $sql,
                'polite_message' => $politeMsg,
                'error' => $politeMsg,
                'raw_error' => $rawError,
            ];
        }
    }

    /**
     * Clean and extract SQL string from markdown block.
     */
    protected function extractSql(string $text): string
    {
        if (preg_match('/```sql\s*([\s\S]*?)\s*```/i', $text, $matches)) {
            return trim($matches[1]);
        }
        if (preg_match('/```\s*([\s\S]*?)\s*```/i', $text, $matches)) {
            return trim($matches[1]);
        }
        if (preg_match('/^\s*SELECT\b/i', trim($text))) {
            return trim($text);
        }
        return '';
    }

    /**
     * Strict safety verification for SQL.
     */
    protected function validateSqlSafety(string $sql): array
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $sql));

        // Must start with SELECT or WITH
        if (!preg_match('/^(SELECT|WITH)\b/i', $normalized)) {
            return ['safe' => false, 'reason' => 'อนุญาตเฉพาะคำสั่ง SELECT หรือ WITH (CTE) เท่านั้น'];
        }

        // Dangerous keywords
        $forbiddenKeywords = [
            '\bINSERT\b', '\bUPDATE\b', '\bDELETE\b', '\bDROP\b', '\bALTER\b',
            '\bTRUNCATE\b', '\bREPLACE\b', '\bGRANT\b', '\bREVOKE\b', '\bLOCK\b',
            '\bINTO\s+OUTFILE\b', '\bINTO\s+DUMPFILE\b', '\bLOAD_FILE\b',
            '\bINFORMATION_SCHEMA\b', '\bSLEEP\b', '\bBENCHMARK\b', '\bEXEC\b', '\bCALL\b'
        ];

        foreach ($forbiddenKeywords as $pattern) {
            if (preg_match('/' . $pattern . '/i', $normalized)) {
                return ['safe' => false, 'reason' => "พบคำสั่งต้องห้าม ({$pattern}) ในคำสั่ง SQL"];
            }
        }

        return ['safe' => true];
    }

    /**
     * Generate concise Thai summary of query result.
     */
    protected function generateSummary(string $question, string $sql, array $rows, string $connection): string
    {
        $count = count($rows);
        $dbName = strtoupper($connection);
        if ($count === 0) {
            $hint = "";
            if (mb_strpos($question, 'วันนี้') !== false || mb_strpos($sql, 'CURDATE()') !== false) {
                $hint = "\n\n💡 *คำแนะนำ: เนื่องจากเงื่อนไขสืบค้นระบุเป็น 'วันนี้' หากอยู่นอกเวลาทำการหรือยังไม่มีการบันทึกข้อมูล สามารถลองสอบถามโดยระบุเป็น 'เดือนนี้' หรือ 'ย้อนหลัง 30 วัน' ได้ครับ*";
            }
            return "ขออภัยครับ จากการสืบค้นฐานข้อมูล {$dbName} ในระบบ ไม่พบข้อมูลตามเงื่อนไขหรือช่วงเวลาที่ระบุครับ 🙏{$hint}";
        }

        $isCodeCol = function ($name) {
            return (bool) preg_match('/(hn|an|vn|cid|pid|code|icode|tmt|billcode|adp|idcard|phone|tel|year|bed|ward|dept|clinic|เลข|รหัส|ปี|เบอร์|โทร|เตียง|ลำดับ)/i', $name);
        };
        $isMeasureCol = function ($name) {
            return (bool) preg_match('/(จำนวน|ราคา|ยอด|บาท|มูลค่า|ผลรวม|จ่าย|ค้าง|ต้นทุน|count|qty|amount|price|cost|total|sum|adjrw|cmi)/i', $name);
        };

        $keys = array_keys($rows[0]);
        $firstCol = $keys[0] ?? '';
        $secondCol = $keys[1] ?? '';
        // Only consider 2nd col a measure if it's explicitly named like count/sum/qty/price and NOT a code/ID
        $isSecondColMeasure = !empty($secondCol) && $isMeasureCol($secondCol) && !$isCodeCol($secondCol);

        // If single row with 1-3 columns, format directly
        if ($count === 1 && count($rows[0]) <= 3) {
            $parts = [];
            foreach ($rows[0] as $k => $v) {
                if (is_numeric($v) && !$isCodeCol($k) && $isMeasureCol($k)) {
                    $formattedVal = is_float($v + 0) ? number_format($v, 2) : number_format($v);
                } else {
                    $formattedVal = $v;
                }
                $parts[] = "{$k}: **{$formattedVal}**";
            }
            return "ผลลัพธ์จากฐานข้อมูล {$dbName}: " . implode(' | ', $parts);
        }

        // Multiple rows (2-10): Only provide breakdown if it's an aggregation (e.g. group by + count/amount)
        if ($count > 1 && $count <= 10 && $isSecondColMeasure) {
            $lines = [];
            foreach ($rows as $row) {
                $vals = array_values($row);
                $name = $vals[0];
                $val1 = $vals[1] ?? '';
                $num = (is_numeric($val1) && !$isCodeCol($secondCol)) ? (is_float($val1 + 0) ? number_format($val1, 2) : number_format($val1)) : $val1;
                $lines[] = "- {$name}: **{$num}**";
            }
            if (!empty($lines)) {
                return "ผลลัพธ์จากฐานข้อมูล {$dbName} (พบทั้งหมด **{$count}** รายการ):\n" . implode("\n", $lines);
            }
        }

        // More than 10 rows with measure
        if ($count > 10 && $isSecondColMeasure) {
            $sampleLines = [];
            $topRows = array_slice($rows, 0, 5);
            foreach ($topRows as $row) {
                $vals = array_values($row);
                $name = $vals[0];
                $val1 = $vals[1] ?? '';
                $num = (is_numeric($val1) && !$isCodeCol($secondCol)) ? (is_float($val1 + 0) ? number_format($val1, 2) : number_format($val1)) : $val1;
                $sampleLines[] = "- {$name}: **{$num}**";
            }
            $sampleText = !empty($sampleLines) ? ":\n" . implode("\n", $sampleLines) . "\n- *(และรายการอื่น ๆ รวมทั้งหมด {$count} รายการ ดังตารางด้านล่าง)*" : "";
            return "ผลลัพธ์จากฐานข้อมูล {$dbName} พบทั้งหมด **{$count}** รายการ{$sampleText}";
        }

        // General summary for entity/patient record listings (AN, HN, visits, appointments, details)
        return "ผลลัพธ์จากฐานข้อมูล {$dbName} (พบทั้งหมด **{$count}** รายการ) ดังแสดงในตารางด้านล่าง";
    }

    /**
     * Schema Dictionary for target DB
     */
    protected function getSchemaContext(string $connection): string
    {
        if ($connection === 'backoffice') {
            return "
-- ฐานข้อมูล Backoffice (ระบบบริหารงานโรงพยาบาล v5.6.1.1)

1. คลังพัสดุ และการเบิกจ่ายพัสดุ (Warehouse & Inventory):
- warehouse_store: คลังพัสดุหลัก (STORE_ID, STORE_NAME, STORE_TYPE_ID)
- warehouse_treasury: คลังย่อย/คลังประจำหน่วยงาน (TREASURY_ID, TREASURY_NAME, HR_DEPARTMENT_SUB_SUB_ID)
- warehouse_request: ใบขอเบิกพัสดุ (WAREHOUSE_ID, WAREHOUSE_REQUEST_CODE [เลขที่ใบเบิก เช่น 'RE-690441'], WAREHOUSE_DATE_WANT [วันที่ต้องการ YYYY-MM-DD], WAREHOUSE_DATE_TIME_SAVE [วันเวลาบันทึก], WAREHOUSE_SAVE_HR_NAME [ชื่อผู้บันทึก/ขอเบิก], WAREHOUSE_STATUS [สถานะใบเบิก เช่น 'Approve','Pending'], INVEN_ID [คลังย่อย])
- warehouse_request_sub: รายการพัสดุในใบขอเบิก (WAREHOUSE_REQUEST_SUB_ID, WAREHOUSE_REQUEST_ID [เชื่อม warehouse_request.WAREHOUSE_ID], WAREHOUSE_REQUEST_SUB_DETAIL_ID [รหัสพัสดุเชื่อม supplies.ID], WAREHOUSE_REQUEST_SUB_AMOUNT [จำนวนที่ขอเบิก], WAREHOUSE_REQUEST_SUB_PRICE [ราคาต่อหน่วย], WAREHOUSE_REQUEST_SUB_SUM_PRICE [ราคารวม])
- warehouse_treasury_pay: การตัดจ่ายพัสดุจากคลังย่อย (TREASURT_PAY_ID, TREASURT_PAY_NAME [หน่วยงาน], TREASURT_PAY_REQUEST_HR_NAME [ผู้เบิก], objective_text [วัตถุประสงค์], created_at)
- warehouse_check_receive: การตรวจรับพัสดุเข้าคลัง (RECEIVE_CHECK_ID, RECEIVE_CHECK_CODE [เลขที่รับเข้า], RECEIVE_CHECK_DATE [วันที่รับ], SUPPLIER_NAME [ผู้จำหน่าย], TOTAL_PRICE [มูลค่ารวม])

2. คลังยาและเวชภัณฑ์ (Medicine & Pharmacy Stock):
- medicine_drug: ทะเบียนยาและเวชภัณฑ์ (id, generic_name [ชื่อสามัญ], trade_name [ชื่อทางการค้า], thai_name [ชื่อไทย], tpu_number [รหัส TPU], icode [รหัสเชื่อม HOSxP], med_warehouse_id [คลังยาหลัก])
- medicine_warehouse_items: รายการยาคงคลัง (id, warehouse_id [คลัง], distributor_id [ผู้จัดจำหน่าย], created_at)
- medicine_warehouse_request: ใบขอเบิกยาและเวชภัณฑ์ (id, code [เลขที่ใบเบิก เช่น 'RE-6901002'], status ['Approve','Pending'], reason [เหตุผล], request_date [วันที่ขอ], pay_date [วันที่จ่าย], warehouse_id [คลังหลัก], treasury_id [คลังย่อย], value [มูลค่ารวม], created_at)
- medicine_warehouse_request_list: รายการยาที่ขอเบิก (id, request_id [เชื่อม medicine_warehouse_request], medicine_id [รหัสยาเชื่อม medicine_drug.id], req_qty [จำนวนขอเบิก], pay_qty [จำนวนจ่ายจริง], unit_price [ราคาต่อหน่วย], value [มูลค่า])
- medicine_warehouse_receive: การรับยาเข้าคลัง (id, code [เลขที่รับเข้า], warehouse_id, po_code [เลขที่ PO], invoice_code [ใบส่งของ], distributor_id, receive_datetime [วันที่รับ], value [มูลค่า])
- medicine_warehouse_export: การจ่ายยาออกจากคลังไปยังคลังย่อย/ตึก (id, req_id, treasury_id [คลังย่อย], medicine_id [รหัสยา], quantity [จำนวนจ่าย], price [ราคา], created_at)
- medicine_purchase_order: การสั่งซื้อยาและเวชภัณฑ์ (id, po_code, order_date, vendor_id, total_price, delivery_date)

3. พัสดุและการจัดซื้อจัดจ้าง (Supplies & Procurement):
- supplies: ทะเบียนพัสดุ (ID, SUP_FSN_NUM [รหัส FSN], SUP_NAME [ชื่อพัสดุ], SUP_TYPE_ID [หมวดพัสดุ], PRICE_LAST [ราคาซื้อล่าสุด], PRICE_CENTER [ราคากลาง])
- supplies_con: สัญญาจัดซื้อจัดจ้าง/โครงการ (ID, CON_NUM [เลขที่สัญญา/ข้อตกลง], CON_YEAR_ID [ปีงบประมาณ พ.ศ. เช่น '2569'], DATE_REGIS [วันที่ลงทะเบียน], DEP_REQUEST_NAME [ฝ่ายที่ขอซื้อ], PERSON_REQUEST_NAME [ผู้ขอซื้อ], CON_PROJECT_NAME [ชื่อโครงการ], EGP_PLAN_NAME)
- supplies_con_list: รายการสิ่งของในสัญญาจัดซื้อ (ID, CON_ID [เชื่อม supplies_con.ID], SUP_NAME [ชื่อรายการสินค้า], SUP_TOTAL [จำนวน], PRICE_PER_UNIT [ราคาต่อหน่วย], PRICE_SUM [ราคารวม])
- supplies_vendor: ทะเบียนบริษัทคู่ค้า/ผู้จัดจำหน่ายพัสดุ (VENDOR_ID, VENDOR_NAME, VENDOR_PHONE)

4. งานทรัพย์สินและครุภัณฑ์ (Assets & Depreciate):
- asset_article: ทะเบียนครุภัณฑ์โรงพยาบาล (ARTICLE_ID, ARTICLE_NUM [เลขครุภัณฑ์ เช่น '3920-005-1103/10'], ARTICLE_NAME [ชื่อครุภัณฑ์], SUPPLIER_ID [ผู้ขาย], RECEIVE_DATE [วันที่ได้มา], PRICE_PER_UNIT [ราคาต่อหน่วย], LOCATION_ID [สถานที่ตั้ง], STATUS_ID [สถานะ: 1=ปกติ, 2=ชำรุด, 3=ส่งซ่อม, 4=แทงจำหน่าย])
- asset_depreciate: ค่าเสื่อมราคาครุภัณฑ์ (ARTICLE_ID, YEAR_ID [ปีงบประมาณ], DEPRECIATE_PRICE [ค่าเสื่อมปีนี้], VALUE_REMAIN [มูลค่าคงเหลือ])
- asset_dispose: ทะเบียนครุภัณฑ์ที่แทงจำหน่าย (ARTICLE_ID, DISPOSE_DATE, DISPOSE_REASON)

5. งานซ่อมบำรุง / ศูนย์คอมพิวเตอร์ / ศูนย์เครื่องมือแพทย์ (Maintenance & Repairs):
- informrepair_index: การแจ้งซ่อมบำรุงทั่วไปและอาคารสถานที่ (ID, REPAIR_ID [เลขที่แจ้งซ่อม เช่น 'R69-00530'], YEAR_ID [ปีงบประมาณ], REPAIR_NAME [ชื่อเรื่อง/สิ่งที่ชำรุด], SYMPTOM [อาการชำรุด], USRE_REQUEST_NAME [ผู้แจ้งซ่อม], DATE_TIME_REQUEST [วันเวลาแจ้ง], REPAIR_STATUS [สถานะการซ่อม เช่น 'REQUEST','SUCCESS'], STATUS)
- informcom_repair: การแจ้งซ่อมคอมพิวเตอร์และอุปกรณ์ไอที (ID, REPAIR_ID [เลขที่แจ้งซ่อมคอม เช่น 'C69-00009'], YEAR_ID, ARTICLE_ID [รหัสครุภัณฑ์ที่ซ่อม], USRE_REQUEST_NAME [ผู้แจ้ง], REPAIR_NAME [เรื่องที่แจ้ง], SYMPTOM [อาการเสีย], DATE_TIME_REQUEST [วันเวลาแจ้ง], REPAIR_STATUS [สถานะการซ่อม เช่น 'REQUEST','RECEIVE','SUCCESS'], REPAIR_STATUS_SUB)
- informcom_service: งานบริการศูนย์คอมพิวเตอร์ (ID, SERVICE_NAME, USER_REQUEST_NAME, DATE_TIME_REQUEST)

6. งานยานพาหนะ (Vehicle & Ambulance Refer):
- vehicle_car_reserve: การขอใช้รถยนต์ส่วนกลาง/รถตู้ (RESERVE_ID, RESERVE_PERSON_NAME [ผู้ขอใช้รถ], RESERVE_LOCATION [สถานที่ไป], DATE_TIME_REQUEST, DATE_BEGIN, DATE_END, STATUS)
- vehicle_car_refer: การใช้รถพยาบาลส่งต่อผู้ป่วยฉุกเฉิน Refer (REFER_ID, REFER_LOCATION [รพ.ปลายทาง], DRIVER_NAME [พนักงานขับรถ], NURSE_NAME [พยาบาลเวร], USER_CREATED_NAME, DATE_TIME_REQUEST)

7. งานจองห้องประชุม (Meeting Room):
- meetingroom_service: การจองห้องประชุม (ID, ROOM_ID [รหัสห้องประชุม], PERSON_REQUEST_NAME [ผู้ขอใช้], SERVICE_STORY [หัวข้อ/วาระประชุม], DATE_BEGIN [วันที่เริ่ม], DATE_END [วันที่สิ้นสุด], TIME_BEGIN [เวลาเริ่ม], TIME_END [เวลาสิ้นสุด], TOTAL_PEOPLE [จำนวนผู้เข้าประชุม])

8. บุคลากร, เงินเดือน, ลงเวลาสแกนนิ้ว, และการลา (HR, Payroll & Attendance):
- hrd_person: ข้อมูลบุคลากร/เจ้าหน้าที่ (ID, HR_CID as เลขบัตรประชาชน, HR_PREFIX_ID as คำนำหน้า, HR_FNAME as ชื่อ, HR_LNAME as นามสกุล, HR_DEPARTMENT_ID as รหัสกลุ่มงาน, HR_DEPARTMENT_SUB_ID as รหัสงานย่อย, HR_PERSON_TYPE_ID as รหัสประเภทบุคลากร, HR_POSITION_ID as รหัสตำแหน่งสายงาน, HR_STATUS_ID as สถานะการทำงาน [1=ปฏิบัติงานปกติ], SEX, BIRTHDAY, START_WORK_DATE)
- hrd_person_type: ประเภทบุคลากร (HR_PERSON_TYPE_ID, HR_PERSON_TYPE_NAME เช่น ข้าราชการ, ลูกจ้างประจำ, พนักงานราชการ, พนักงานกระทรวงสาธารณสุข, ลูกจ้างรายเดือน, ลูกจ้างรายวัน)
- hrd_position: ตำแหน่งสายงาน/วิชาชีพ (HR_POSITION_ID, HR_POSITION_NAME เช่น พยาบาลวิชาชีพ, นายแพทย์, เจ้าพนักงานสาธารณสุข, เภสัชกร, นักวิชาการสาธารณสุข)
- hrd_department: กลุ่มงาน/ฝ่าย (HR_DEPARTMENT_ID, HR_DEPARTMENT_NAME เช่น กลุ่มงานการพยาบาล, กลุ่มงานบริหารทั่วไป, กลุ่มงานบริการทางการแพทย์, กลุ่มงานสุขภาพดิจิทัล)
- hrd_status: สถานะเจ้าหน้าที่ (HR_STATUS_ID, HR_STATUS_NAME เช่น 1=ปฏิบัติงานปกติ, 2=ลาศึกษาต่อ, 3=ลาออก, 4=เกษียณ)
- checkin_device_time_attendance: ประวัติการสแกนลายนิ้วมือ/ใบหน้า (id, person_id, time_attendance [วันเวลาที่สแกน])
- gleave_register: รายการยื่นใบลา (ID, PERSON_ID [เชื่อม hrd_person.ID], LEAVE_TYPE_ID, LEAVE_DATE_BEGIN, LEAVE_DATE_END, LEAVE_DAYS [จำนวนวันลา], STATUS [สถานะอนุมัติ])
- gleave_over: ประวัติวันลาสะสม (ID, PERSON_ID, LEAVE_TYPE_ID, LEAVE_DAYS)
- gleave_type: ประเภทวันลา (LEAVE_TYPE_ID, LEAVE_TYPE_NAME เช่น ลาป่วย, ลากิจ, ลาพักผ่อน, ลาคลอด)
- salary_all: บัญชีเงินเดือนและค่าตอบแทน (ID, YEAR_ID [ปีงบประมาณ เช่น 2569], MONTH_ID [รหัสเดือน 1-12], PERSON_ID, TOTAL_RECEIVE [ยอดรับรวม], TOTAL_PAY [ยอดหักรวม], NET_SALARY [รับสุทธิ])

9. รายงานความเสี่ยงและอุบัติการณ์ (Risk Incident):
- risk_rep: รายงานอุบัติการณ์ความเสี่ยง (RISKREP_ID, RISKREP_DATESAVE [วันที่รายงาน], RISKREP_LOCAL [สถานที่เกิดเหตุ], RISKREP_TYPE [ประเภทความเสี่ยง], RISKREP_USEREFFECT [ระดับความรุนแรง], RISKREP_STARTDATE [วันที่เกิดเหตุ], RISKREP_TIME [เวลาเกิดเหตุ])

* กฎสำคัญ Backoffice:
- เจ้าหน้าที่ไอทีหรือสารสนเทศ สังกัดกลุ่มงานชื่อ 'กลุ่มงานสุขภาพดิจิทัล'
- คลังพัสดุทั่วไปใช้ตารางตระกูล warehouse_* ส่วนคลังยาและเวชภัณฑ์ใช้ตารางตระกูล medicine_warehouse_*
- ปีงบประมาณใน Backoffice ส่วนใหญ่ใช้ พ.ศ. เช่น 2568, 2569 ในคอลัมน์ YEAR_ID หรือ CON_YEAR_ID
";
        }

        if ($connection === 'mysql') {
            return "
-- ฐานข้อมูล SmartData (ระบบจัดการภายใน)
- users: ผู้ใช้งานระบบ (id, name, username, email, role, allow_copilot, active, created_at)
- ai_knowledge_docs: เอกสารคลังความรู้ (id, title, category, file_type, file_size, status, chunks_count, created_at)
- lend_items: รายการอุปกรณ์ให้ยืม (id, item_code, item_name, category, status, total_qty, available_qty)
- lend_transactions: รายการยืม-คืน (id, item_id, borrower_name, borrow_date, return_date, status)
- customer_complains: ข้อร้องเรียน (id, topic, detail, status, created_at)
";
        }

        // Default: HOSxP
        return "
-- ฐานข้อมูล HOSxP (ระบบบริการผู้ป่วย, เวชระเบียน, การเงิน และตัวชี้วัดโรงพยาบาล)

1. ผู้ป่วยและประชากร:
- patient: ข้อมูลผู้ป่วย (hn, cid, pname, fname, lname, sex [1=ชาย, 2=หญิง], birthday, addrpart, mojupart, amphur, changwat, chwpart, amppart, tmbpart, moopart, hcode, pttype, drugallergy)
- hospcode: สถานพยาบาล/รพ.สต. (hospcode, name, hosptype)

2. ผู้ป่วยนอก (OPD):
- ovst: การมารับบริการ (vn, hn, vstdate [YYYY-MM-DD], vsttime, cur_dep, main_dep, pttype, ovstost ['99'=เสร็จสิ้น], oqueue, main_dep_queue, ovstist ['08'=ALS, '09'=FR, '10'=ILS สำหรับ EMS], an)
- vn_stat: สถิติผู้ป่วยนอกและการเงิน (vn, hn, vstdate, pdx [รหัสโรคหลัก ICD-10], dx0, dx1, dx2, dx3, dx4, dx5, sex, age_y, pttype, income [ค่าบริการรวม], uc_money [เบิกได้], paid_money [ชำระเอง], inc12 [ค่ายา], inc03 [ค่าแล็บ], lastvisit_hour [ชม. ที่มาตรวจครั้งก่อน], old_diagnosis ['Y'=โรคเดิม], dx_doctor)
- opdscreen: คัดกรองและสัญญาณชีพ (vn, hn, cc [อาการสำคัญ Chief Complaint], bps, bpd, bw [น้ำหนัก], height [ส่วนสูง], hr, pulse, temperature)
- ovstdiag: การวินิจฉัยโรค OPD (vn, hn, icd10, diagtype [1=Principle Dx, 2=Co-morbidity, 3=Complication], vstdate)
- kskdepartment: แผนกตรวจ (depcode, department)
  * รหัสแผนกหลัก main_dep: '002'=OPD ทั่วไป, '011'=NCD คลินิกเรื้อรัง, '032'=ARI ทางเดินหายใจ, '033'=ไตเทียม รพ., '024'=ไตเทียมนอก

3. ผู้ป่วยใน (IPD):
- ipt: การรับไว้รักษา (an, hn, vn, regdate [วันที่รับไว้], regtime, dchdate [วันที่จำหน่าย], dchtime, dchstts, dchtype ['04'=Refer], dch_doctor, ward, pttype, spclty, adjrw, confirm_discharge ['N'=ครองเตียงอยู่ หรือ dchdate IS NULL], prediag [อาการแรกรับ], provision_dx [การวินิจฉัยแรกรับก่อน Admit ที่แพทย์สั่ง เช่น 'Sepsis c UTI', 'Pneumonia'], provision_dx_icd [รหัส ICD-10 แรกรับ เช่น 'A419', 'J189'])
- an_stat: สถิติผู้ป่วยใน (an, hn, regdate, dchdate, pdx [รหัสโรคหลักเมื่อจำหน่าย], admdate [วันนอน admit], hospital_admdate, income, rcpt_money, inc12 [ค่ายา], inc03 [ค่าแล็บ], ward, age_y, admit_hour, adjrw)
- iptadm: เตียงและห้องพักผู้ป่วยใน (an, bedno, roomno)
- iptbedmove: ประวัติการย้ายเตียง/วอร์ด IPD (an, movedate, movetime, nbedno, nward)
- iptdiag: การวินิจฉัยโรค IPD (an, hn, icd10, diagtype [1=Principle Dx])
- ipt_doctor_diag: แพทย์บันทึกการวินิจฉัย/สรุปชาร์ต EMR (an, diag_text [ข้อความวินิจฉัยโรคทางคลินิกที่แพทย์บันทึกแรกรับและระหว่างรักษา], audit_diag_text, audit_ok ['Y'=Audit ผ่าน], diagtype [1=โรคหลัก])
- ipt_doctor_list: แพทย์ผู้ดูแล IPD (an, doctor, ipt_doctor_type_id, active_doctor ['Y'])
- iptoprt: หัตถการและผ่าตัด IPD (an, icd9 [รหัสหัตถการ ICD-9-CM])
- doctor_operation: หัตถการ OPD (vn, icd9)
- ward: หอผู้ป่วย (ward, name, bedcount [จำนวนเตียงทั้งหมด])
  * รหัสวอร์ด: '01'=สามัญ, '02'=ห้องคลอด (LR), '03'=VIP, '10'=ICU (หรือดูเตียง nbedno LIKE 'ICU%'), '06'=Homeward
- dchstts: สถานะการจำหน่าย (dchstts, name เช่น หาย, ดีขึ้น, ไม่ทุเลา, เสียชีวิต)
- dchtype: ประเภทการจำหน่าย (dchtype, name เช่น 01=With Approval, 04=Refer ส่งต่อไป รพ. อื่น)

4. งานบริการเฉพาะทาง (Specialty Clinics & Units):
- dtmain: ทันตกรรม Dental (vn, hn) *เงื่อนไขผู้รับบริการ: `vn IN (SELECT vn FROM dtmain)`
- physic_list: กายภาพบำบัด Physical Therapy (vn, hn) *เงื่อนไขผู้รับบริการ: `EXISTS (SELECT 1 FROM physic_list WHERE vn = o.vn)`
- health_med_service: แพทย์แผนไทย (vn, hn) *เงื่อนไขผู้รับบริการ: `EXISTS (SELECT 1 FROM health_med_service WHERE vn = o.vn)`
- ipt_pregnancy: การคลอดในห้องคลอด LR (an, deliver_type [1=คลอดธรรมชาติ Normal, 2=คลอดผิดปกติ/ผ่าคลอด, 3=แท้ง/อื่นๆ])
- person_anc_service: บริการฝากครรภ์ ANC (vn, hn)
- clinic: คลินิกเฉพาะโรค (clinic, name)
  * รหัสคลินิกเรื้อรัง NCD: '001'=เบาหวาน (DM), '002'=ความดันโลหิตสูง (HT), '007'=ไตเรื้อรัง CKD, '009'=วัณโรค/Asthma, '012'=สุขภาพจิต, '013'=ฟอกไต HD, '014'=ฟอกไต CAPD, '020'=บำบัดยาเสพติด, '021'=COPD, '028'=โรคหลอดเลือดสมอง Stroke, '029'=หัวใจล้มเหลว, '032'=CKD 4-5
- clinicmember: ทะเบียนผู้ป่วยคลินิกโรคเรื้อรัง NCD (hn, clinic, regdate, lastvisit, dchdate, clinic_member_status_id, last_fbs_value [ค่าน้ำตาล], last_hba1c_value, last_ua_value, last_bp_bps_value [ความดันตัวบน], doctor, send_to_pcu_hcode)
- clinic_member_status: สถานะผู้ป่วยคลินิกเรื้อรัง (clinic_member_status_id, clinic_member_status_name)

5. งานอุบัติเหตุ-ฉุกเฉิน (ER & EMS):
- er_regist: ผู้ป่วยฉุกเฉิน ER (vn, hn, vstdate, vsttime, enter_er_time, finish_time, er_emergency_type, er_doctor)
- er_emergency_type: ระดับความเร่งด่วน ER (er_emergency_type, name [1=กู้ชีพ Resuscitate, 2=ฉุกเฉินเร่งด่วน Emergency, 3=ฉุกเฉิน Urgency, 4=กึ่งฉุกเฉิน Semi-Urgency, 5=ไม่ฉุกเฉิน Non-Urgency])

6. เภสัชกรรม ยา และการแพ้ยา:
- drugitems: คลังยา (icode, name, generic_name, units, strength) *รหัสยา icode ขึ้นต้นด้วย '1%'
- opitemrece: รายการสั่งยาและค่าบริการ (vn, an, hn, icode, qty, unitprice, sum_price, rxdate, rxtime, doctor)
- opd_allergy: ประวัติแพ้ยา (hn, report_date, agent [ชื่อยาที่แพ้], symptom [อาการแพ้], seriousness_id, allergy_result_id)
- allergy_seriousness: ความรุนแรงการแพ้ยา (seriousness_id, seiousness_name)
- allergy_result: ผลการประเมินการแพ้ยา (allergy_result_id, result_name)

7. ชันสูตร รังสี และค่าบริการ:
- nondrugitems: ค่าบริการและเวชภัณฑ์มิใช่ยา (icode, name, price, unitcost, income)
  * หมวดรายได้ income: '02'=อุปกรณ์/อวัยวะเทียม, '03'=แล็บ (LAB), '12'=ยา, '13'=ทันตกรรม, '14'=กายภาพบำบัด, '15'=แพทย์แผนไทย
- xray_items: เอกซเรย์ (icode, name, xray_items_group [3=CT Scan])
- xray_head: ใบสั่งตรวจเอกซเรย์ (vn, hn, order_date, order_time) **ไม่มีฟิลด์ an เด็ดขาด! ให้เชื่อมด้วย xh.vn**
- lab_head: สั่งตรวจแล็บ (vn, hn, lab_order_number, order_date, order_time, doctor_code, form_name, confirm_report)
  * **สำคัญมาก: ตาราง lab_head ไม่มีคอลัมน์ an เด็ดขาด! (ห้าม WHERE lh.an = ...)**
  * การเชื่อมโยงแล็บของผู้ป่วยใน (IPD ด้วย AN): รหัส AN จะถูกบันทึกไว้ในฟิลด์ vn (lh.vn = an) หรือเชื่อมโยงผ่าน ipt.vn เสมอ ให้ใช้เงื่อนไข: `WHERE (lh.vn = :an OR lh.vn IN (SELECT vn FROM ipt WHERE an = :an))`
- lab_order: รายการและผลการตรวจแล็บ (lab_order_number, lab_items_code, lab_order_result [ค่าผลตรวจแล็บ], lab_order_remark, confirm ['Y'/'N'])
  * เชื่อมโยงกับ lab_head ด้วย: `lo.lab_order_number = lh.lab_order_number`
  * เชื่อมโยงกับ lab_items ด้วย: `li.lab_items_code = lo.lab_items_code` (ห้ามนำ lab_items_code ไปเท่ากับ lab_order_number เด็ดขาด!)
- lab_items: รายการตรวจแล็บ (lab_items_code, lab_items_name, lab_items_unit, lab_items_normal_value [ค่าปกติอ้างอิง], icode, price, provis_lab_code)

8. นัดหมาย และ คิวตรวจ:
- oapp: ใบนัดผู้ป่วย (vn, hn, nextdate [วันที่นัด], nexttime, clinic, doctor, app_cause [เหตุผลที่นัด])
- opd_qs_slot: คิวตรวจ OPD (vn, queue_slot_number)

9. การส่งต่อ และ ระบาดวิทยา:
- referout: ส่งต่อไป รพ. อื่น (vn, hn, refer_date, refer_time, refer_hospcode, refer_point ['OPD','ER','IPD'], department, pdx, with_ambulance)
- referin: รับส่งต่อจาก รพ. อื่น (vn, hn, refer_date, refer_time, refer_hospcode, pre_diagnosis, icd10 [as pdx_refer], refer_point)
- death: การเสียชีวิต (hn, an, death_date, death_time, death_cause, death_diag_1 [รหัสโรค ICD10 ที่เสียชีวิต], death_place ['1'=เสียชีวิตใน รพ.])
- surveil_member: ผู้ป่วยโรคเฝ้าระวังทางระบาดวิทยา 506 (hn, vn, an, code506, report_date, begin_date)
- name506: พจนานุกรมโรคเฝ้าระวัง 506 (code, name)

10. สิทธิการรักษาและการกำหนดกลุ่มราคา (Pttype & Price Group):
- pttype: ตารางสิทธิการรักษา (pttype, name, hipdata_code, pcode, paidst, isuse ['Y'/'N'], expire_date, price_type [ระดับราคา 1-5], pttype_price_group_id [เชื่อม pttype_price_group], pttype_sks_code)
  * hipdata_code: 'UCS'/'DIS'=บัตรทอง, 'OFC'/'BKK'/'BMT'=ข้าราชการ/เบิกตรง, 'SSS'/'SSI'=ประกันสังคม, 'LGO'=อปท., 'NRD'/'NRH'=ไร้สัญชาติ/ต่างด้าวไร้สิทธิ, 'A1'/'A9' หรือ paidst IN ('01','03')=ชำระเงินเอง
- pttype_price_group: ตารางกลุ่มการคิดราคาตามสิทธิ (pttype_price_group_id, name, price_type [1=ราคาปกติ, 2=ราคานอกเวลา/เบิกได้, 3=ราคาต่างด้าว/ต่างชาติ, 4, 5])
- pttype_price_policy: นโยบายการกำหนดราคาตามสิทธิ (pttype, price_type, discount_percent)
- pttype_items_price: ตารางกำหนดราคาค่ายาและค่ารักษาเฉพาะรายสิทธิและรายรายการ icode (pttype, icode, price, price2, price3, paidst, claim_code)
  * ความสำคัญสูงสุด (Override Priority 1): หากรายการ icode ใดมีการกำหนดราคาใน pttype_items_price ระบบ HOSxP จะดึงราคานี้มาใช้ก่อนเสมอ เหนือกว่า pttype_price_group และ drugitems.unitprice / nondrugitems.price
- visit_pttype: สิทธิตอนตรวจ OPD (vn, pttype, hospmain [รหัส รพ. ตามสิทธิ เช่น '10989'=รพ.หัวตะพาน In-CUP])
- ipt_pttype: สิทธิตอน Admit IPD (an, pttype, hospmain)
- icd101: พจนานุกรมรหัสโรค ICD-10 (code, name, tname)

11. ข้อมูลพื้นฐานและการตั้งค่าระบบ (Master Data & Settings สำหรับ Admin):
- drugitems: คลังยาและการกำหนดราคาขายหลายระดับตามกลุ่มสิทธิ (icode, name, generic_name, strength, units, unitcost [ราคาทุน], unitprice [ราคาขายระดับ 1 ปกติ], unitprice2 [ราคาขายระดับ 2 นอกเวลา/เบิกได้], unitprice3 [ราคาขายระดับ 3 ต่างด้าว/ต่างชาติ], unitprice4 [ราคา 4], unitprice5 [ราคา 5], drugaccount [1=ED ในบัญชียาหลัก, 2=NED นอกบัญชี], hospital_drug_code [รหัสยามาตรฐาน 24 หลัก TMT สำหรับส่งออก e-Claim], did [รหัสยามาตรฐาน สธ.], nhso_adp_code [รหัสเบิก สปสช.], warn_text, pregnancy_category)
  * การคิดค่ายาตามสิทธิ: ระบบ HOSxP จะดึงราคา unitprice, unitprice2, unitprice3... ตามฟิลด์ price_type ของ pttype หรือ pttype_price_group ของผู้ป่วย
- drugitems_price_group: ตารางกำหนดราคายาเฉพาะกลุ่มราคา (icode, pttype_price_group_id, unitprice)
- nondrugitems: ค่าบริการ ค่ารักษาพยาบาล และหัตถการหลายระดับราคา (icode, name, unitcost [ราคาทุน], price [ราคาปกติระดับ 1], price2 [ราคานอกเวลาระดับ 2], price3 [ราคาต่างด้าวระดับ 3], price4 [ราคา 4], price5 [ราคา 5], income [หมวดรายได้], billcode [รหัสเบิกจ่ายตรง CSOP], nhso_adp_code [รหัสเบิก สปสช. E-Claim], cgd_code, istype)
  * การคิดค่าบริการตามสิทธิ: ระบบ HOSxP จะดึงราคา price, price2, price3... ตาม price_type ของ pttype หรือ pttype_price_group
- nondrugitems_price_group: ตารางกำหนดค่าบริการเฉพาะกลุ่มราคา (icode, pttype_price_group_id, price)
- income: หมวดรายได้หลักโรงพยาบาล (income, name เช่น 01=ค่าห้อง, 02=อุปกรณ์, 03=แล็บ, 12=ยา, 13=ทันตกรรม, 14=กายภาพ, 15=แพทย์แผนไทย)
- icd9cm1: พจนานุกรมรหัสหัตถการและผ่าตัด ICD-9-CM มาตรฐาน (code, name)
- lab_items: รายการตรวจแล็บ (lab_items_code, lab_items_name, lab_items_unit, lab_items_normal_value [ค่าปกติอ้างอิง Reference Range], icode [เชื่อม nondrugitems.icode], price, provis_lab_code)
- doctor: แพทย์และบุคลากรทางการแพทย์ (code, name, licence_no [เลขที่ใบอนุญาต ว./ท./พ./ภ. สำหรับส่งออกแฟ้ม PROVIDER], spclty, active ['Y'/'N'], cid)
- spclty: สาขาความเชี่ยวชาญแพทย์ (spclty, name เช่น 01=อายุรกรรม, 02=ศัลยกรรม, 03=สูติ-นรีเวช, 04=กุมารเวชกรรม, 05=ออร์โธปิดิกส์)
- opduser: บัญชีผู้ใช้งานระบบ HOSxP (loginname, name, groupname, department, account_disable ['Y'/'N'])

12. โครงสร้างการส่งออก 43 แฟ้ม และตารางมาตรฐาน provis_:
- provis_instype: รหัสสิทธิการรักษามาตรฐาน 43 แฟ้ม สนย. (code, name) เชื่อมโยงกับ pttype (สำหรับแฟ้ม PERSON, CHARGE_OPD, CHARGE_IPD)
- provis_vaccine: รหัสวัคซีนมาตรฐาน 43 แฟ้ม แฟ้ม EPI (code, name เช่น 010=BCG, 041=DTP-HB1, 081=OPV1, 061=MMR, 073=HPV)
- provis_ncd_clinic: รหัสคลินิกโรคเรื้อรัง 43 แฟ้ม แฟ้ม CHRONIC (code, name เช่น 01=เบาหวาน, 02=ความดัน, 03=หัวใจ, 04=Stroke, 07=CKD)
- provis_lab_code: รหัสแล็บตรวจติดตามโรคเรื้อรัง 43 แฟ้ม แฟ้ม LABFU (code, name เช่น FBS, HbA1c, Bun, Cr, eGFR, TC, TG, HDL, LDL)
- provis_fp_type: รหัสวิธีวางแผนครอบครัว แฟ้ม FP (code, name เช่น 1=ยาเม็ด, 2=ยาฉีด, 3=ห่วง, 4=ยาฝัง, 5=ถุงยาง, 6=หมันชาย, 7=หมันหญิง)
- provis_typedis: รหัสประเภทความพิการ แฟ้ม DISABILITY (code, name เช่น 1=มองเห็น, 2=ได้ยิน, 3=กายภาพ/เคลื่อนไหว, 4=จิตใจ, 5=สติปัญญา, 6=ออทิสติก)
- provis_title: รหัสคำนำหน้าชื่อมาตรฐาน 43 แฟ้ม (title_code, title_name)
- provis_marriage: รหัสสถานภาพสมรสมาตรฐาน 43 แฟ้ม (marriage_code, marriage_name)
- provis_occupation: รหัสอาชีพมาตรฐาน 43 แฟ้ม (occupation_code, occupation_name)
- provis_accident_code: รหัสสาเหตุการเกิดอุบัติเหตุ แฟ้ม ACCIDENT (code, name)
- person: ข้อมูลประชากรในเขตรับผิดชอบสำหรับ 43 แฟ้ม แฟ้ม PERSON (person_id, hn, cid, pname, fname, lname, birthdate, sex, house_id, discharge_status, type_area)
  * รหัส type_area: 1=มีชื่อและตัวอยู่จริง, 2=มีชื่อแต่ตัวไม่อยู่, 3=ตัวอยู่จริงไม่มีชื่อในทะเบียนบ้าน, 4=ประชากรแฝง/นอกเขต
- person_chronic: ทะเบียนผู้ป่วยโรคเรื้อรังระดับบุคคล แฟ้ม CHRONIC (person_id, hn, chronic_diag, clinic_code)
- person_disability: ข้อมูลคนพิการระดับบุคคล แฟ้ม DISABILITY (person_id, typedis, disabcode)
- person_vaccine: ข้อมูลประวัติการรับวัคซีนระดับบุคคล แฟ้ม EPI (person_id, vaccine_code, vaccine_date)
- village: ข้อมูลหมู่บ้าน (village_id, village_moo, village_name)
- house: ข้อมูลหลังคาเรือน (house_id, village_id, house_address)

---------------------------------------------------------
* กฎสำคัญ Hospital Domain Rules & Best Practices:
1. ตาราง ipt ไม่มีฟิลด์ pdx และไม่มีฟิลด์ bedno เด็ดขาด!
   - หากต้องการเตียงผู้ป่วยใน ให้ LEFT JOIN iptadm adm ON i.an = adm.an แล้วใช้ adm.bedno
   - ตาราง lab_head และ xray_head ไม่มีฟิลด์ an เด็ดขาด! (ห้าม WHERE lh.an = ... หรือ xh.an = ...)
   - การค้นหาแล็บด้วย AN ของผู้ป่วยใน: HOSxP จะบันทึก AN ไว้ในฟิลด์ vn ของ lab_head ดังนั้นต้องเขียนเงื่อนไขเป็น:
     `WHERE (lh.vn = ? OR lh.vn IN (SELECT vn FROM ipt WHERE an = ?))`
2. คนไข้ที่กำลังนอน รพ. (Admit อยู่ขณะนี้):
   - เงื่อนไขครองเตียง: `WHERE (i.confirm_discharge = 'N' OR i.dchdate IS NULL)`
   - สำคัญมากที่สุดตามโครงสร้าง HOSxP:
     * ตาราง `an_stat` เป็นตารางสรุปตอนจำหน่าย (Discharge) ขณะคนไข้นอนจะยังไม่มีข้อมูลเด็ดขาด!
     * ตาราง `iptdiag` และ `ipt_doctor_diag` ขณะคนไข้นอนส่วนใหญ่ยังไม่ได้บันทึก เพราะต้องรอแพทย์สรุปชาร์ตตอนจำหน่าย
     * ข้อมูลการวินิจฉัยโรคขณะคนไข้กำลังนอน Admit อยู่ จะบันทึกไว้ในตาราง `ipt` โดยตรง คือ:
       (1) `i.provision_dx` = การวินิจฉัยแรกรับทางคลินิกที่แพทย์ลงตอนสั่ง Admit (เช่น 'Sepsis c UTI', 'Pneumonia', 'CHF')
       (2) `i.provision_dx_icd` = รหัส ICD-10 แรกรับตอนสั่ง Admit (เช่น 'A419', 'J189')
       (3) `i.prediag` = อาการแรกรับและประวัติส่งต่อ
       (4) `v.pdx` จาก `LEFT JOIN vn_stat v ON v.vn = i.vn` (รหัสโรคแรกรับจาก OPD/ER ก่อนส่งขึ้นตึก)
   - ตัวอย่างคำสั่งมาตรฐานเมื่อค้นหาคนไข้ที่กำลัง Admit ด้วยโรคเฉพาะเจาะจง (เช่น Sepsis):
     SELECT 
       i.an AS 'เลข AN',
       i.hn AS 'เลข HN',
       CONCAT(p.pname, p.fname, ' ', p.lname) AS 'ชื่อ-นามสกุล',
       w.name AS 'หอผู้ป่วย',
       i.regdate AS 'วันที่รับไว้รักษา',
       COALESCE(
         NULLIF(i.provision_dx, ''),
         (SELECT CONCAT('[', i.provision_dx_icd, '] ', icd.name) FROM icd101 icd WHERE icd.code = i.provision_dx_icd),
         (SELECT CONCAT('[', v.pdx, '] ', icd2.name) FROM icd101 icd2 WHERE icd2.code = v.pdx LIMIT 1),
         i.prediag,
         'อยู่ระหว่างการวินิจฉัย'
       ) AS 'การวินิจฉัยแรกรับ (Provisional Dx)'
     FROM ipt i
     JOIN patient p ON p.hn = i.hn
     LEFT JOIN ward w ON w.ward = i.ward
     LEFT JOIN vn_stat v ON v.vn = i.vn
     WHERE (i.confirm_discharge = 'N' OR i.dchdate IS NULL)
       AND (
           i.provision_dx LIKE '%sepsis%' OR i.provision_dx LIKE '%septic%' OR i.provision_dx LIKE '%ติดเชื้อในกระแส%'
           OR i.provision_dx_icd LIKE 'A41%' OR i.provision_dx_icd = 'R572'
           OR i.prediag LIKE '%sepsis%' OR i.prediag LIKE '%ติดเชื้อในกระแส%'
           OR v.pdx LIKE 'A41%' OR v.pdx = 'R572'
           OR EXISTS (SELECT 1 FROM iptdiag id WHERE id.an = i.an AND (id.icd10 LIKE 'A41%' OR id.icd10 = 'R572'))
       )
3. ผู้ป่วยในที่จำหน่ายแล้ว (Discharged IPD):
   - หาโรคหลักโดย JOIN an_stat a ON i.an = a.an แล้วใช้ a.pdx (หรือ LEFT JOIN icd101 icd ON a.pdx = icd.code)
4. ตัวชี้วัดคุณภาพโรงพยาบาล (Hospital Indicators):
   - Re-admit ภายใน 28 วันด้วยโรคเดิม (IPD):
     FROM ipt ipt_new
     INNER JOIN iptdiag diag_new ON diag_new.an = ipt_new.an AND diag_new.diagtype = '1'
     INNER JOIN ipt ipt_old ON ipt_old.hn = ipt_new.hn AND ipt_old.an <> ipt_new.an
     INNER JOIN iptdiag diag_old ON diag_old.an = ipt_old.an AND diag_old.diagtype = '1' AND diag_old.icd10 = diag_new.icd10
     WHERE ipt_new.regdate BETWEEN ? AND ?
       AND TIMESTAMPDIFF(DAY, ipt_old.dchdate, ipt_new.regdate) BETWEEN 1 AND 28
   - Re-visit ภายใน 48 ชม. ด้วยโรคเดิม (ER/OPD):
     FROM ovst o JOIN vn_stat v ON v.vn = o.vn WHERE v.lastvisit_hour <= 48 AND v.old_diagnosis = 'Y'
   - Refer Out ภายใน 4 ชม. หรือ 24 ชม. หลัง Admit:
     FROM ipt i JOIN an_stat a ON i.an = a.an WHERE i.dchtype = '04' AND a.admit_hour <= 4 (หรือ <= 24)
   - ค่าดัชนีกลุ่มวินิจฉัยโรคร่วมเฉลี่ย (CMI): `ROUND(SUM(a.adjrw) / COUNT(DISTINCT a.an), 2)`
   - ชาร์ตรอแพทย์สรุป: `NOT EXISTS (SELECT 1 FROM ipt_doctor_diag idd WHERE idd.an = i.an AND idd.diag_text <> '')`
   - ชาร์ตรอ Audit: `EXISTS (SELECT 1 FROM ipt_doctor_diag idd WHERE idd.an = i.an AND idd.diag_text <> '' AND (idd.audit_ok IS NULL OR idd.audit_ok <> 'Y'))`
5. การตรวจสอบความพร้อมการส่งออก 43 แฟ้ม และการตั้งค่าระบบ (43 Files Audit & Master Settings):
   - ตรวจสอบรายการที่ตั้งราคาเฉพาะสิทธิใน pttype_items_price: `SELECT p.pttype, t.name as pttype_name, p.icode, COALESCE(d.name, n.name) as item_name, p.price, COALESCE(d.unitprice, n.price) as standard_price, COALESCE(d.unitcost, n.unitcost) as unitcost FROM pttype_items_price p LEFT JOIN pttype t ON t.pttype = p.pttype LEFT JOIN drugitems d ON d.icode = p.icode LEFT JOIN nondrugitems n ON n.icode = p.icode`
   - ตรวจสอบสิทธิที่มีการตั้งราคาพิเศษเฉพาะรายการมากที่สุด: `SELECT p.pttype, t.name, count(p.icode) as custom_item_count FROM pttype_items_price p LEFT JOIN pttype t ON t.pttype = p.pttype GROUP BY p.pttype, t.name`
   - ตรวจสอบกลุ่มราคา pttype_price_group และจำนวนสิทธิที่ผูก: `SELECT g.pttype_price_group_id, g.name, g.price_type, count(p.pttype) as pttype_count FROM pttype_price_group g LEFT JOIN pttype p ON p.pttype_price_group_id = g.pttype_price_group_id GROUP BY g.pttype_price_group_id, g.name, g.price_type`
   - ตรวจสอบสิทธิที่ยังไม่ได้กำหนดระดับราคา (price_type หรือ pttype_price_group_id): `SELECT pttype, name, price_type, pttype_price_group_id FROM pttype WHERE isuse = 'Y' AND (price_type IS NULL OR price_type = '' OR price_type = '0')`
   - ตรวจสอบราคายาในกลุ่มราคา 1, 2, 3 ที่ต่ำกว่าราคาทุน: `SELECT icode, name, unitcost, unitprice, unitprice2, unitprice3 FROM drugitems WHERE (unitprice < unitcost OR (unitprice2 > 0 AND unitprice2 < unitcost) OR (unitprice3 > 0 AND unitprice3 < unitcost)) AND unitcost > 0`
   - ตรวจสอบค่าบริการ/หัตถการที่ราคาตามสิทธิผิดปกติหรือต่ำกว่าทุน: `SELECT icode, name, price, price2, price3, unitcost FROM nondrugitems WHERE (price < unitcost OR (price2 > 0 AND price2 < unitcost)) AND unitcost > 0`
   - ตรวจสอบยาที่ยังไม่ใส่รหัส TMT 24 หลัก: `SELECT icode, name, units FROM drugitems WHERE (hospital_drug_code IS NULL OR hospital_drug_code = '') AND (istype = '01' OR istype IS NULL)`
   - ตรวจสอบค่าบริการ/แล็บ/หัตถการที่ยังไม่ได้ใส่ billcode หรือ nhso_adp_code: `SELECT icode, name, price, income FROM nondrugitems WHERE (billcode IS NULL OR billcode = '') AND (nhso_adp_code IS NULL OR nhso_adp_code = '')`
   - ตรวจสอบสิทธิที่เปิดใช้งานแต่ยังไม่ผูก hipdata_code หรือ provis_instype: `SELECT pttype, name, hipdata_code, pcode FROM pttype WHERE isuse = 'Y' AND (hipdata_code IS NULL OR hipdata_code = '')`
   - ตรวจสอบแพทย์/ผู้ให้บริการที่ Active แต่ยังไม่มีเลข ว. (licence_no) สำหรับแฟ้ม PROVIDER: `SELECT code, name, cid FROM doctor WHERE active = 'Y' AND (licence_no IS NULL OR licence_no = '')`
   - ตรวจสอบประชากร 43 แฟ้ม แยกตาม Type Area: `SELECT type_area, count(*) as count FROM person GROUP BY type_area`
   - ตรวจสอบประชากรที่ CID ว่างหรือไม่ครบ 13 หลัก (แฟ้ม PERSON): `SELECT person_id, fname, lname, cid, type_area FROM person WHERE cid IS NULL OR LENGTH(cid) <> 13`
   - ตรวจสอบประชากรที่ยังไม่ได้ระบุ Type Area: `SELECT person_id, fname, lname, cid FROM person WHERE type_area IS NULL OR type_area = ''`
   - ตรวจสอบรายการแล็บที่ยังไม่ผูกรหัส provis_lab_code (แฟ้ม LABFU): `SELECT lab_items_code, lab_items_name, provis_lab_code FROM lab_items WHERE provis_lab_code IS NULL OR provis_lab_code = ''`
   - ตรวจสอบคลินิก NCD ที่ยังไม่ผูกรหัส provis_ncd_clinic (แฟ้ม CHRONIC): `SELECT clinic, name, provis_ncd_clinic_code FROM clinic WHERE provis_ncd_clinic_code IS NULL OR provis_ncd_clinic_code = ''`
   - ตรวจสอบวัคซีนที่ยังไม่ผูกรหัส provis_vaccine (แฟ้ม EPI): `SELECT vaccine_code, vaccine_name, provis_vaccine_code FROM vaccine WHERE provis_vaccine_code IS NULL OR provis_vaccine_code = ''`
6. ผู้ป่วยหนัก ICU: ค้นหาจาก `iptbedmove.nbedno LIKE 'ICU%'` หรือ `i.ward = '10'` หรือ `w.name LIKE '%ICU%' OR w.name LIKE '%วิกฤต%'`
7. รหัสโรคสำคัญ: Stroke (I64, I619, I639) | Sepsis (A419, A415) | Septic Shock (R572) | Pneumonia (J189, J180) | MI (I219) | CHF (I500, I509) | COPD (J449) | Asthma (J459) | Head Injury (S099, S060)
8. การสืบค้นติดตามผู้ป่วยรายบุคคลด้วย HN หรือ AN (Patient Follow-up & PDPA Privacy):
   - การสื่อสารด้วย HN หรือ AN เป็นมาตรการตามหลัก Pseudonymization (ข้อมูลรหัสเทียมภายนอกไม่สามารถระบุตัวตนบุคคลได้ ปลอดภัยตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล PDPA)
    - ติดตามผลแล็บคนไข้ราย HN หรือ AN (ตาราง lab_head ไม่มีคอลัมน์ an เด็ดขาด! ค้นหาด้วย AN ให้ใช้ WHERE lh.vn = :an OR lh.vn IN (SELECT vn FROM ipt WHERE an = :an)):
     SELECT lh.order_date, lh.order_time, li.lab_items_name, lo.lab_order_result, li.lab_items_unit, li.lab_items_normal_value
     FROM lab_head lh
     JOIN lab_order lo ON lo.lab_order_number = lh.lab_order_number
     JOIN lab_items li ON li.lab_items_code = lo.lab_items_code
     WHERE (lh.vn = ? OR lh.vn IN (SELECT vn FROM ipt WHERE an = ?))
     ORDER BY lh.order_date DESC, lh.order_time DESC LIMIT 50
   - ติดตามรายการยาที่คนไข้ได้รับราย HN/AN:
     SELECT o.rxdate, o.rxtime, d.name AS drug_name, o.qty, d.units, o.unitprice, o.sum_price
     FROM opitemrece o
     JOIN drugitems d ON d.icode = o.icode
     WHERE (o.hn = ? OR o.an = ?) AND o.icode LIKE '1%'
     ORDER BY o.rxdate DESC, o.rxtime DESC LIMIT 30
   - ติดตามค่ารักษาพยาบาลราย HN/AN:
     SELECT i.name AS income_group, SUM(o.sum_price) AS total_amount, SUM(o.qty) AS item_count
     FROM opitemrece o
     JOIN income i ON i.income = o.income
     WHERE o.hn = ? OR o.an = ?
     GROUP BY i.name ORDER BY total_amount DESC
   - มาตรการ PDPA: ห้าม SELECT เลขบัตรประชาชน (cid), เบอร์โทร, หรือที่อยู่ เว้นแต่ผู้ใช้ระบุโดยตรง เพื่อป้องกันการเปิดเผยข้อมูลส่วนบุคคลที่ระบุตัวตนโดยตรง
";
    }
}
