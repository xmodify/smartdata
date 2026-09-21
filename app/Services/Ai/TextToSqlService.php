<?php

namespace App\Services\Ai;

use App\Models\AiSetting;
use App\Services\Ai\Contracts\LlmProviderInterface;
use App\Services\Ai\Context\HosxpContextService;
use App\Services\Ai\Context\BackofficeContextService;
use App\Services\Ai\Context\SmartdataContextService;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;

class TextToSqlService
{
    protected LlmProviderInterface $provider;
    protected HosxpContextService $hosxpContext;
    protected BackofficeContextService $backofficeContext;
    protected SmartdataContextService $smartdataContext;

    public function __construct(
        ?LlmProviderInterface $provider = null,
        ?HosxpContextService $hosxpContext = null,
        ?BackofficeContextService $backofficeContext = null,
        ?SmartdataContextService $smartdataContext = null
    ) {
        $this->provider = $provider ?? AiManager::getActiveProvider();
        $this->hosxpContext = $hosxpContext ?? app(HosxpContextService::class);
        $this->backofficeContext = $backofficeContext ?? app(BackofficeContextService::class);
        $this->smartdataContext = $smartdataContext ?? app(SmartdataContextService::class);
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
        $schemaContext = $this->getSchemaContext($connection, $question);

        // Retrieve live dynamic domain lookup context (ICD-10, Doctor, Ward, Risk levels, etc.)
        $liveContextText = '';
        try {
            $liveCtx = $this->getLiveDomainContext($connection, $question);
            if ($liveCtx && !empty($liveCtx['text'])) {
                $liveContextText = "\n\nข้อมูลบริบทจริงและ Lookup จากฐานข้อมูล [{$connection}]:\n" . $liveCtx['text'];
            }
        } catch (\Throwable $e) {
            Log::warning("TextToSql live context error: " . $e->getMessage());
        }

        // Calculate current date and Thai fiscal year dynamically
        $now = now();
        $todayDate = $now->format('Y-m-d');
        $thaiYear = $now->year + 543;
        $currentFiscalYear = ($now->month >= 10) ? ($thaiYear + 1) : $thaiYear;
        $fiscalStartCe = ($now->month >= 10) ? $now->year : ($now->year - 1);
        $fiscalEndCe = $fiscalStartCe + 1;
        $fiscalDateRange = "{$fiscalStartCe}-10-01 ถึง {$fiscalEndCe}-09-30";

        // Ask LLM to generate SQL
        $systemPrompt = "คุณคือ Expert Database Architect & SQL Generator สำหรับระบบโรงพยาบาล
ฐานข้อมูลเป้าหมายคือ MySQL: [Connection: {$connection}]
ข้อมูลเวลาปัจจุบันของระบบ:
- วันที่ปัจจุบัน: {$todayDate} (พ.ศ. {$thaiYear})
- ปีงบประมาณปัจจุบัน: ปีงบประมาณ {$currentFiscalYear} (ช่วงวันที่ {$fiscalDateRange})

กฎสำคัญสูงสุด:
1. ตอบกลับเป็นคำสั่ง SQL `SELECT` หรือ `WITH` (CTE) ที่สมบูรณ์เพียงคำสั่งเดียวเท่านั้น โดยต้องอยู่ใน Markdown Block: ```sql ... ```
2. ห้ามใช้คำสั่ง DDL/DML เด็ดขาด (ห้าม INSERT, UPDATE, DELETE, DROP, ALTER, TRUNCATE)
3. สำหรับวันที่และการกรองช่วงเวลา (Date Filtering & Performance Optimization):
   - สำคัญสูงสุด: ห้ามนำคอลัมน์วันที่ (เช่น vstdate, regdate, dchdate, incident_date, rxdate) ไปครอบด้วยฟังก์ชัน YEAR(), MONTH(), DATE() ใน WHERE เด็ดขาด เพราะจะทำให้ข้าม Index และเกิด Full Table Scan หลายล้านแถวจนระบบ Timeout
   - ให้ใช้การเปรียบเทียบช่วงวันที่แบบ SARGable (Index-friendly) เสมอ เช่น:
     • 'เดือนนี้': `vstdate BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())`
     • 'เดือนที่แล้ว': `vstdate BETWEEN DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH) AND LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))`
     • 'วันนี้': `vstdate = CURDATE()`
     • 'เมื่อวาน': `vstdate = DATE_SUB(CURDATE(), INTERVAL 1 DAY)`
     • 'ปีนี้': `vstdate BETWEEN DATE_FORMAT(CURDATE(), '%Y-01-01') AND DATE_FORMAT(CURDATE(), '%Y-12-31')`
     • 'ย้อนหลัง 30 วัน': `vstdate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)`
     • 'ปีงบประมาณนี้' หรือ 'ปีงบประมาณปัจจุบัน' (ปี {$currentFiscalYear}): `vstdate BETWEEN '{$fiscalStartCe}-10-01' AND '{$fiscalEndCe}-09-30'`
     • 'ปีงบประมาณนี้ ถึงเดือน...': เช่น 'ปีงบประมาณนี้ ถึงเดือน สค 2569' ให้ใช้ `vstdate BETWEEN '{$fiscalStartCe}-10-01' AND '2026-08-31'`
     • 'ปีงบประมาณไทยระบุปี เช่น 2568': `vstdate BETWEEN '2024-10-01' AND '2025-09-30'` (1 ต.ค. ปีก่อนหน้า ถึง 30 ก.ย. ปีนั้น)
4. กำหนด LIMIT สูงสุดไม่เกิน 100 แถวเสมอ (เช่น LIMIT 100)
5. ใช้ชื่อฟิลด์ภาษาอังกฤษตาม Schema ที่ให้มาด้านล่าง
6. เขียนคำสั่ง SQL ที่มีประสิทธิภาพ พร้อมตั้งชื่อ Alias คอลัมน์เป็นภาษาไทยที่อ่านง่าย เช่น `count(*) as 'จำนวนคน'`
7. หากคำถามถามเรื่องประเภท หรือถามต่อเนื่องว่า 'กี่ประเภท' หรือ 'แยกตาม...' ให้เขียนคำสั่งที่แจกแจงตามประเภทนั้นๆ พร้อมนับจำนวน (GROUP BY และ COUNT) เพื่อให้ผู้ใช้เห็นรายละเอียดและจำนวนครบถ้วน
8. ห้ามใส่เครื่องหมายจุลภาคหรือฟังก์ชัน FORMAT(...) กับรหัสประจำตัว (Identifiers) เด็ดขาด เช่น HN, AN, VN, CID, รหัสยา icode, รหัสแล็บ, เลขเตียง, ปี พ.ศ. เพราะไม่ใช่จำนวนนับหรือราคาเงิน ให้คงค่าเดิมไว้เสมอ
9. ห้ามแสดงรหัส ID ดิบๆ ที่มีตาราง Lookup กำกับเด็ดขาด (เช่น ระดับความรุนแรงความเสี่ยง, โปรแกรมความเสี่ยง, หน่วยงาน/แผนก, สถานที่, สถานะ, ชื่อพัสดุ) ให้ทำการ JOIN ตาราง Lookup ที่เกี่ยวข้องเสมอ เพื่อดึงชื่อและคำอธิบายภาษาไทยมาแสดงผลให้อ่านเข้าใจง่าย
10. หากคำถามของผู้ใช้มีการกล่าวถึง 'กราฟ', 'แผนภูมิ', 'chart', 'พล็อต', 'plot', 'สัดส่วน', 'เปรียบเทียบ', หรือ 'อันดับ' ให้เขียน SQL ในลักษณะจัดกลุ่มสรุป (GROUP BY) และเรียงลำดับจากมากไปหาน้อย (ORDER BY ... DESC) โดยเลือกคอลัมน์ที่เป็นชื่อกลุ่ม/ประเภท/หน่วยงาน/สิทธิ/โรค (Label) 1 คอลัมน์ และคอลัมน์ที่เป็นตัวเลข/จำนวนนับ/ยอดรวม (Metric) 1 หรือหลายคอลัมน์ เพื่อให้ระบบสามารถนำข้อมูลไปพล็อตกราฟ (Interactive Chart) ได้ทันทีอย่างถูกต้องและสวยงาม

Schema ข้อมูลที่สามารถใช้ได้:
{$schemaContext}{$liveContextText}
";

        // Build user prompt with multi-turn history if present
        $userPrompt = "แปลงคำถามนี้เป็น SQL: \"{$question}\"";
        if (!empty($history)) {
            $contextStr = "บริบทการสนทนาก่อนหน้านี้ในเซสชันนี้:\n";
            foreach ($history as $h) {
                $roleName = ($h['role'] === 'user') ? 'ผู้ใช้' : 'ดองกี้';
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

        // Optimize non-sargable date expressions to prevent full table scans
        $sql = $this->optimizeSqlDates($sql);

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
            // Protect hospital database with query statement timeout (Max 15 seconds)
            try {
                DB::connection($connection)->statement('SET SESSION max_statement_time = 15;');
            } catch (\Throwable $te) {
                // Ignore if connection does not support max_statement_time
            }

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

            // Detect query timeout (MariaDB max_statement_time 1969 or MySQL 3024)
            if (stripos($rawError, 'max_statement_time') !== false || stripos($rawError, 'max_execution_time') !== false || stripos($rawError, 'interrupted') !== false) {
                $politeMsg = "ขออภัยครับ คำสั่งสืบค้นนี้ใช้เวลาประมวลผลนานเกินกำหนด (มากกว่า 15 วินาที) ระบบได้ตัดการทำงานอัตโนมัติเพื่อความปลอดภัยของฐานข้อมูล {$dbName} ครับ 🙏\n\n💡 *คำแนะนำ: กรุณาระบุเงื่อนไขวันที่ หรือระบุช่วงเวลาให้แคบลง เพื่อให้การสืบค้นรวดเร็วยิ่งขึ้นครับ*";
            } else {
                $politeMsg = "ขออภัยครับ ระบบไม่พบข้อมูลหรือเกิดข้อขัดข้องในการสืบค้นจากฐานข้อมูล {$dbName} ในขณะนี้ครับ 🙏\n\n💡 *คำแนะนำ: โครงสร้างคำถามอาจยังไม่สอดคล้องกับตารางข้อมูล ท่านสามารถลองปรับเปลี่ยนคำค้นหา หรือสอบถามเจ้าหน้าที่ผู้ดูแลระบบ (Admin) เพื่อตรวจสอบคำสั่งสืบค้นได้ครับ*";
            }

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
     * Rewrite non-sargable date expressions to indexed range queries.
     * Prevents multi-million row full table scans (e.g. vn_stat).
     */
    protected function optimizeSqlDates(string $sql): string
    {
        // 1. Current month: YEAR(col) = YEAR(CURDATE()) AND MONTH(col) = MONTH(CURDATE())
        $patternCurrentMonth = '/(?:YEAR\(\s*([a-zA-Z0-9_\.]+)\s*\)\s*=\s*YEAR\s*\(\s*(?:CURDATE|NOW)\(\)\s*\)\s+AND\s+MONTH\(\s*\1\s*\)\s*=\s*MONTH\s*\(\s*(?:CURDATE|NOW)\(\)\s*\)|MONTH\(\s*([a-zA-Z0-9_\.]+)\s*\)\s*=\s*MONTH\s*\(\s*(?:CURDATE|NOW)\(\)\s*\)\s+AND\s+YEAR\(\s*\2\s*\)\s*=\s*YEAR\s*\(\s*(?:CURDATE|NOW)\(\)\s*\))/i';
        $sql = preg_replace_callback($patternCurrentMonth, function ($m) {
            $col = !empty($m[1]) ? $m[1] : $m[2];
            return "{$col} BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())";
        }, $sql);

        // 2. Specific year & month: YEAR(col) = 2024 AND MONTH(col) = 9
        $patternSpecificMonth = '/(?:YEAR\(\s*([a-zA-Z0-9_\.]+)\s*\)\s*=\s*[\'"]?(\d{4})[\'"]?\s+AND\s+MONTH\(\s*\1\s*\)\s*=\s*[\'"]?(\d{1,2})[\'"]?|MONTH\(\s*([a-zA-Z0-9_\.]+)\s*\)\s*=\s*[\'"]?(\d{1,2})[\'"]?\s+AND\s+YEAR\(\s*\4\s*\)\s*=\s*[\'"]?(\d{4})[\'"]?)/i';
        $sql = preg_replace_callback($patternSpecificMonth, function ($m) {
            $col = !empty($m[1]) ? $m[1] : $m[4];
            $year = !empty($m[2]) ? $m[2] : $m[6];
            $month = str_pad(!empty($m[3]) ? $m[3] : $m[5], 2, '0', STR_PAD_LEFT);
            return "{$col} BETWEEN '{$year}-{$month}-01' AND LAST_DAY('{$year}-{$month}-01')";
        }, $sql);

        // 3. Current year: YEAR(col) = YEAR(CURDATE())
        $patternCurrentYear = '/YEAR\(\s*([a-zA-Z0-9_\.]+)\s*\)\s*=\s*YEAR\s*\(\s*(?:CURDATE|NOW)\(\)\s*\)/i';
        $sql = preg_replace_callback($patternCurrentYear, function ($m) {
            return "{$m[1]} BETWEEN DATE_FORMAT(CURDATE(), '%Y-01-01') AND DATE_FORMAT(CURDATE(), '%Y-12-31')";
        }, $sql);

        // 4. Specific year: YEAR(col) = 2024
        $patternSpecificYear = '/YEAR\(\s*([a-zA-Z0-9_\.]+)\s*\)\s*=\s*[\'"]?(\d{4})[\'"]?/i';
        $sql = preg_replace_callback($patternSpecificYear, function ($m) {
            return "{$m[1]} BETWEEN '{$m[2]}-01-01' AND '{$m[2]}-12-31'";
        }, $sql);

        // 5. DATE(col) = CURDATE() or DATE(col) = 'YYYY-MM-DD'
        $patternDateOnly = '/DATE\(\s*([a-zA-Z0-9_\.]+)\s*\)\s*=\s*(CURDATE\(\)|\'[^\']+\'|"[^"]+")/i';
        $sql = preg_replace($patternDateOnly, '$1 = $2', $sql);

        return $sql;
    }

    /**
     * Clean and extract SQL string from markdown block or raw LLM text.
     */
    protected function extractSql(string $text): string
    {
        // 1. Check for markdown code blocks (```sql ... ``` or ``` ... ```)
        if (preg_match('/```(?:sql)?\s*([\s\S]*?)\s*```/i', $text, $matches)) {
            $extracted = trim($matches[1]);
            // If there was conversational text accidentally included inside the block, extract starting from WITH or SELECT
            if (preg_match('/\b((?:WITH|SELECT)\s+[\s\S]+)/i', $extracted, $m)) {
                return trim($m[1]);
            }
            return $extracted;
        }

        // 2. Fallback: Catch any SELECT or WITH statement ending with a semicolon
        if (preg_match('/\b((?:WITH|SELECT)\s+[\s\S]+?);/i', $text, $matches)) {
            return trim($matches[1]);
        }

        // 3. Fallback: Check if string begins with SELECT or WITH
        if (preg_match('/^\s*((?:WITH|SELECT)\b[\s\S]+)/i', trim($text), $matches)) {
            return trim($matches[1]);
        }

        // 4. Fallback: Extract SELECT or WITH statement until Thai explanation or end of string
        if (preg_match('/\b((?:WITH|SELECT)\s+[A-Za-z0-9_\s\.,\(\)\*\=\<\>\'\`\-\+\/\%\:\&]+)/i', $text, $matches)) {
            return trim($matches[1]);
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
        $dateScope = $this->detectDateRange($sql, $question);
        $dateScopePrefix = $dateScope ? " [{$dateScope}]" : "";

        if ($count === 0) {
            $hint = "";
            if (mb_strpos($question, 'วันนี้') !== false || mb_strpos($sql, 'CURDATE()') !== false) {
                $hint = "\n\n💡 *คำแนะนำ: เนื่องจากเงื่อนไขสืบค้นระบุเป็น 'วันนี้' หากอยู่นอกเวลาทำการหรือยังไม่มีการบันทึกข้อมูล สามารถลองสอบถามโดยระบุเป็น 'เดือนนี้' หรือ 'ย้อนหลัง 30 วัน' ได้ครับ*";
            }
            return "ขออภัยครับ จากการสืบค้นฐานข้อมูล {$dbName}{$dateScopePrefix} ในระบบ ไม่พบข้อมูลตามเงื่อนไขหรือช่วงเวลาที่ระบุครับ 🙏{$hint}";
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

        // Generate smart follow-up suggestions based on query and tables
        $followUpHints = [];
        $sqlLower = mb_strtolower($sql);
        $qLower = mb_strtolower($question);

        if (str_contains($sqlLower, 'asset_article') || str_contains($qLower, 'คอม') || str_contains($qLower, 'ครุภัณฑ์')) {
            $followUpHints = [
                'ขอดูรายการแยกตามแผนก/ตึกที่ตั้งใช้งาน',
                'ขอดูรายละเอียดเลขครุภัณฑ์และวันที่ตรวจรับ',
                'มีประวัติการแจ้งซ่อมคอมพิวเตอร์ในปีนี้กี่ครั้ง'
            ];
        } elseif (str_contains($sqlLower, 'supplies') || str_contains($sqlLower, 'warehouse') || str_contains($qLower, 'วัสดุ') || str_contains($qLower, 'เบิก')) {
            $followUpHints = [
                'ขอยอดการเบิกจ่ายพัสดุย้อนหลัง 30 วัน แยกตามหน่วยงาน',
                '10 อันดับวัสดุที่มีการเบิกใช้สูงสุดในเดือนนี้',
                'ขอดูรายการวัสดุสิ้นเปลืองแยกตามหมวดหมู่'
            ];
        } elseif (str_contains($sqlLower, 'risk_rep') || str_contains($qLower, 'เสี่ยง')) {
            $followUpHints = [
                'ขอดูอุบัติการณ์ความเสี่ยงระดับรุนแรง (ระดับ E ถึง I)',
                'สรุปอุบัติการณ์แยกตามโปรแกรมความเสี่ยงหลัก 5 ด้าน',
                'สรุปความเสี่ยงแยกตามสถานที่เกิดเหตุ'
            ];
        } elseif (str_contains($sqlLower, 'ovst') || str_contains($sqlLower, 'vn_stat') || str_contains($qLower, 'opd') || str_contains($qLower, 'คนไข้')) {
            $followUpHints = [
                'ขอยอดผู้ป่วยนอกแยกตามสิทธิการรักษา',
                '5 อันดับโรคหลักที่มีผู้มารับบริการมากที่สุด',
                'ขอยอดผู้ป่วยแยกตามช่วงเวลาเข้าตรวจ'
            ];
        } elseif (str_contains($sqlLower, 'ipt') || str_contains($sqlLower, 'an_stat') || str_contains($qLower, 'ipd')) {
            $followUpHints = [
                'จำนวนผู้ป่วยในกำลังครองเตียงแยกตามหอผู้ป่วย',
                'ยอดผู้ป่วยใน Refer ส่งต่อไป รพ. อื่น',
                'ค่าเฉลี่ยวันนอน (LOS) และค่ารักษาเฉลี่ย'
            ];
        }

        $chartNotice = '';
        if (preg_match('/(กราฟ|chart|แผนภูมิ|พล็อต|plot|สัดส่วน)/iu', $question)) {
            $chartNotice = "\n\n📊 *ดองกี้ได้จัดเตรียมกราฟสรุป AI และตารางข้อมูลไว้ให้เรียบร้อยแล้วครับ ท่านสามารถคลิกดูแท็บ 'กราฟสรุป AI' เพื่อดูแผนภูมิแท่งแนวนอน แนวตั้ง โดนัท เส้น พร้อมเซฟรูปภาพได้ทันทีครับ*";
        }

        $hintText = $chartNotice;
        if (!empty($followUpHints)) {
            $hintItems = array_map(fn($h) => "  • *\"{$h}\"*", $followUpHints);
            $hintText .= "\n\n💡 **คำถามแนะนำเพื่อเจาะลึกข้อมูลต่อ:**\n" . implode("\n", $hintItems);
        }

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
            return "ผลลัพธ์จากฐานข้อมูล {$dbName}{$dateScopePrefix}: " . implode(' | ', $parts) . $hintText;
        }

        // Calculate total sum across measure column if available
        $totalSum = 0;
        $hasNumericSum = false;
        if ($isSecondColMeasure) {
            $hasNumericSum = true;
            foreach ($rows as $r) {
                $val = array_values($r)[1] ?? null;
                if (is_numeric($val)) {
                    $totalSum += ($val + 0);
                } else {
                    $hasNumericSum = false;
                    break;
                }
            }
        }

        // Check if there are status columns (e.g. ใช้งานปกติ, จำหน่ายแล้ว)
        $statusSummary = '';
        $activeCol = null;
        $disposedCol = null;
        foreach ($keys as $k) {
            if (mb_strpos($k, 'ปกติ') !== false || mb_strpos($k, 'ใช้งาน') !== false) $activeCol = $k;
            if (mb_strpos($k, 'จำหน่าย') !== false) $disposedCol = $k;
        }
        if ($activeCol && $disposedCol) {
            $activeTotal = array_sum(array_column($rows, $activeCol));
            $disposedTotal = array_sum(array_column($rows, $disposedCol));
            $allTotal = $activeTotal + $disposedTotal;
            if ($allTotal > 0) {
                $actPct = round(($activeTotal / $allTotal) * 100, 1);
                $disPct = round(($disposedTotal / $allTotal) * 100, 1);
                $statusSummary = "\n- สภาพความพร้อมใช้งาน: ใช้งานปกติ **" . number_format($activeTotal) . "** ({$actPct}%) | จำหน่ายแล้ว **" . number_format($disposedTotal) . "** ({$disPct}%)";
            }
        }

        $totalSummaryLine = "";
        if ($hasNumericSum && $totalSum > 0) {
            $formattedTotal = is_float($totalSum) ? number_format($totalSum, 2) : number_format($totalSum);
            $totalSummaryLine = "\n- **ยอดรวมทั้งสิ้น:** **{$formattedTotal}** (จัดกลุ่มได้ **{$count}** รายการ){$statusSummary}";
        }

        // Multiple rows (2-10): Provide breakdown
        if ($count > 1 && $count <= 10 && $isSecondColMeasure) {
            $lines = [];
            foreach ($rows as $row) {
                $vals = array_values($row);
                $name = $vals[0];
                $val1 = $vals[1] ?? '';
                $num = (is_numeric($val1) && !$isCodeCol($secondCol)) ? (is_float($val1 + 0) ? number_format($val1, 2) : number_format($val1)) : $val1;
                $lines[] = "- {$name}: **{$num}**";
            }
            return "ผลลัพธ์จากฐานข้อมูล {$dbName}{$dateScopePrefix} (พบ **{$count}** รายการ):{$totalSummaryLine}\n" . implode("\n", $lines) . $hintText;
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
            $sampleText = !empty($sampleLines) ? ":{$totalSummaryLine}\n" . implode("\n", $sampleLines) . "\n- *(และรายการอื่น ๆ รวมทั้งหมด {$count} รายการ ดังตารางด้านล่าง)*" : "";
            return "ผลลัพธ์จากฐานข้อมูล {$dbName}{$dateScopePrefix} พบทั้งหมด **{$count}** รายการ{$sampleText}{$hintText}";
        }

        // General summary for entity/patient record listings (AN, HN, visits, appointments, details)
        return "ผลลัพธ์จากฐานข้อมูล {$dbName}{$dateScopePrefix} (พบทั้งหมด **{$count}** รายการ) ดังแสดงในตารางด้านล่าง{$hintText}";
    }

    /**
     * Extract human-readable Thai date range / period from SQL query or user question.
     */
    protected function detectDateRange(string $sql, string $question): ?string
    {
        $formatThaiDate = function ($ymd) {
            if (!$ymd || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
                return $ymd;
            }
            $y = (int)$m[1] + 543;
            $months = [
                '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
                '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
                '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
            ];
            $d = (int)$m[3];
            $monthName = $months[$m[2]] ?? $m[2];
            return "{$d} {$monthName} {$y}";
        };

        // 1. Explicit BETWEEN 'YYYY-MM-DD' AND 'YYYY-MM-DD'
        if (preg_match("/BETWEEN\s+'(\d{4}-\d{2}-\d{2})'\s+AND\s+'(\d{4}-\d{2}-\d{2})'/i", $sql, $m)) {
            return "ช่วงวันที่ " . $formatThaiDate($m[1]) . " ถึง " . $formatThaiDate($m[2]);
        }

        // 2. >= 'YYYY-MM-DD' AND <= 'YYYY-MM-DD'
        if (preg_match("/>=\s*'(\d{4}-\d{2}-\d{2})'\s+AND\s+<=\s*'(\d{4}-\d{2}-\d{2})'/i", $sql, $m)) {
            return "ช่วงวันที่ " . $formatThaiDate($m[1]) . " ถึง " . $formatThaiDate($m[2]);
        }

        // 3. >= 'YYYY-MM-DD' AND < 'YYYY-MM-DD'
        if (preg_match("/>=\s*'(\d{4}-\d{2}-\d{2})'\s+AND\s+<\s*'(\d{4}-\d{2}-\d{2})'/i", $sql, $m)) {
            try {
                $end = new \DateTime($m[2]);
                $end->modify('-1 day');
                return "ช่วงวันที่ " . $formatThaiDate($m[1]) . " ถึง " . $formatThaiDate($end->format('Y-m-d'));
            } catch (\Exception $e) {
                return "ช่วงวันที่ " . $formatThaiDate($m[1]);
            }
        }

        // 4. Dynamic expressions: INTERVAL 1 MONTH / เดือนที่แล้ว
        if (stripos($sql, 'INTERVAL 1 MONTH') !== false || mb_strpos($question, 'เดือนที่แล้ว') !== false) {
            $lastMonth = new \DateTime('first day of last month');
            $lastMonthEnd = new \DateTime('last day of last month');
            return "ช่วงวันที่ " . $formatThaiDate($lastMonth->format('Y-m-d')) . " ถึง " . $formatThaiDate($lastMonthEnd->format('Y-m-d')) . " (เดือนที่แล้ว)";
        }

        // 5. Dynamic expressions: เดือนนี้
        if (mb_strpos($question, 'เดือนนี้') !== false || (stripos($sql, 'MONTH(') !== false && stripos($sql, 'CURDATE()') !== false)) {
            $thisMonth = new \DateTime('first day of this month');
            $today = new \DateTime();
            return "ช่วงวันที่ " . $formatThaiDate($thisMonth->format('Y-m-d')) . " ถึง " . $formatThaiDate($today->format('Y-m-d')) . " (เดือนนี้)";
        }

        // 6. เมื่อวาน / INTERVAL 1 DAY
        if (mb_strpos($question, 'เมื่อวาน') !== false || stripos($sql, 'CURDATE() - INTERVAL 1 DAY') !== false || stripos($sql, 'INTERVAL 1 DAY') !== false) {
            $yesterday = new \DateTime('-1 day');
            return "ประจำวันที่ " . $formatThaiDate($yesterday->format('Y-m-d')) . " (เมื่อวานนี้)";
        }

        // 7. Single explicit date = 'YYYY-MM-DD'
        if (preg_match("/=\s*'(\d{4}-\d{2}-\d{2})'/i", $sql, $m)) {
            return "ประจำวันที่ " . $formatThaiDate($m[1]);
        }

        // 8. วันนี้ / CURDATE()
        if (mb_strpos($question, 'วันนี้') !== false || stripos($sql, 'CURDATE()') !== false || stripos($sql, 'CURRENT_DATE') !== false) {
            $today = new \DateTime();
            return "ประจำวันที่ " . $formatThaiDate($today->format('Y-m-d')) . " (วันนี้)";
        }

        // 9. ปีงบประมาณ
        if (preg_match('/(256\d)/', $question, $qm)) {
            return "ประจำปีงบประมาณ {$qm[1]}";
        }

        return null;
    }

        /**
     * Schema Dictionary for target DB (Delegated to dedicated Context Services)
     */
    protected function getSchemaContext(string $connection, string $question = ''): string
    {
        switch ($connection) {
            case 'backoffice':
                return $this->backofficeContext->getSchemaContext($question);
            case 'mysql':
                return $this->smartdataContext->getSchemaContext($question);
            case 'hosxp':
            default:
                return $this->hosxpContext->getSchemaContext($question);
        }
    }

    /**
     * Get live domain lookups for target connection
     */
    protected function getLiveDomainContext(string $connection, string $question): ?array
    {
        switch ($connection) {
            case 'backoffice':
                return $this->backofficeContext->getContext($question);
            case 'mysql':
                return $this->smartdataContext->getContext($question);
            case 'hosxp':
            default:
                return $this->hosxpContext->getContext($question);
        }
    }
}