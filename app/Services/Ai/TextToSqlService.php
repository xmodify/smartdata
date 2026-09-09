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
        
        $boKeywords = ['พัสดุ', 'บุคลากร', 'พนักงาน', 'เจ้าหน้าที่', 'วันลา', 'เงินเดือน', 'ครุภัณฑ์', 'เบิก', 'สัญญา', 'จัดซื้อ', 'จัดจ้าง', 'hr', 'สารบรรณ', 'ลาป่วย', 'ลากิจ', 'hrd_', 'ฝ่าย', 'กลุ่มงาน'];
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

        // HOSxP explicit keywords
        $hosxpKeywords = ['คนไข้', 'ผู้ป่วย', 'opd', 'ipd', 'er', 'vn', 'hn', 'an', 'โรค', 'icd', 'pttype', 'สิทธิ', 'ค่ารักษา', 'refer', 'admit', 'เตียง', 'คลินิก', 'วอร์ด', 'ยา', 'หมอ', 'แพทย์'];
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
            return [
                'success' => false,
                'target_db' => $connection,
                'error' => 'ไม่สามารถสร้างคำสั่ง SQL จากคำถามนี้ได้ กรุณาระบุรายละเอียดเพิ่มเติม',
                'raw_reply' => $rawResponse
            ];
        }

        // Validate SQL safety
        $safetyCheck = $this->validateSqlSafety($sql);
        if (!$safetyCheck['safe']) {
            return [
                'success' => false,
                'target_db' => $connection,
                'sql' => $sql,
                'error' => 'คำสั่ง SQL ถูกระงับเนื่องจากเหตุผลด้านความปลอดภัย: ' . $safetyCheck['reason']
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
            return [
                'success' => false,
                'target_db' => $connection,
                'sql' => $sql,
                'error' => 'เกิดข้อผิดพลาดในการรัน SQL บนฐานข้อมูล ' . $connection . ': ' . $e->getMessage()
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
        if ($count === 0) {
            $hint = "";
            if (mb_strpos($question, 'วันนี้') !== false || mb_strpos($sql, 'CURDATE()') !== false) {
                $hint = " (หมายเหตุ: เนื่องจากเงื่อนไขสืบค้นเป็น 'วันนี้' หากอยู่นอกเวลาทำการหรือยังไม่มีการบันทึกข้อมูล สามารถลองระบุเป็น 'เดือนนี้' หรือ 'ย้อนหลัง 30 วัน' ได้ครับ)";
            }
            return "สืบค้นข้อมูลจากฐานข้อมูล {$connection} สำเร็จ ไม่พบข้อมูลตามเงื่อนไขที่ระบุ{$hint}";
        }

        // If single row with 1-3 columns, format directly
        if ($count === 1 && count($rows[0]) <= 3) {
            $parts = [];
            foreach ($rows[0] as $k => $v) {
                $formattedVal = is_numeric($v) ? number_format($v) : $v;
                $parts[] = "{$k}: **{$formattedVal}**";
            }
            return "ผลลัพธ์จากฐานข้อมูล {$connection}: " . implode(' | ', $parts);
        }

        // Multiple rows (2-10): Provide conversational breakdown with numbers
        if ($count > 1 && $count <= 10) {
            $lines = [];
            foreach ($rows as $row) {
                $vals = array_values($row);
                if (count($vals) >= 2) {
                    $name = $vals[0];
                    $num = is_numeric($vals[1]) ? number_format($vals[1]) : $vals[1];
                    $lines[] = "- {$name}: **{$num}**";
                } elseif (count($vals) === 1) {
                    $lines[] = "- " . $vals[0];
                }
            }
            if (!empty($lines)) {
                return "ผลลัพธ์จากฐานข้อมูล {$connection} (พบทั้งหมด **{$count}** รายการ):\n" . implode("\n", $lines);
            }
        }

        // More than 10 rows: Provide top 5 and indicate remaining in table
        if ($count > 10) {
            $sampleLines = [];
            $topRows = array_slice($rows, 0, 5);
            foreach ($topRows as $row) {
                $vals = array_values($row);
                if (count($vals) >= 2) {
                    $name = $vals[0];
                    $num = is_numeric($vals[1]) ? number_format($vals[1]) : $vals[1];
                    $sampleLines[] = "- {$name}: **{$num}**";
                }
            }
            $sampleText = !empty($sampleLines) ? ":\n" . implode("\n", $sampleLines) . "\n- *(และรายการอื่น ๆ รวมทั้งหมด {$count} รายการ ดังตาราง)*" : "";
            return "ผลลัพธ์จากฐานข้อมูล {$connection} พบทั้งหมด **{$count}** รายการ{$sampleText}";
        }

        // General summary
        return "สืบค้นข้อมูลจากฐานข้อมูล {$connection} สำเร็จ พบผลลัพธ์ทั้งหมด **{$count}** รายการ ดังแสดงในตารางด้านล่าง";
    }

    /**
     * Schema Dictionary for target DB
     */
    protected function getSchemaContext(string $connection): string
    {
        if ($connection === 'backoffice') {
            return "
-- ฐานข้อมูล Backoffice (งานบริหารบุคคล, สารบรรณ, พัสดุ, ครุภัณฑ์)
- hrd_person: ข้อมูลบุคลากร/เจ้าหน้าที่ (ID, HR_CID as เลขบัตรประชาชน, HR_PREFIX_ID as คำนำหน้า, HR_FNAME as ชื่อ, HR_LNAME as นามสกุล, HR_DEPARTMENT_ID as รหัสกลุ่มงาน/ฝ่าย, HR_DEPARTMENT_SUB_ID as รหัสงานย่อย, HR_PERSON_TYPE_ID as รหัสประเภทบุคลากร, HR_POSITION_ID as รหัสตำแหน่งสายงาน, HR_STATUS_ID as สถานะการทำงาน [1=ปฏิบัติงานปกติ], SEX, BIRTHDAY, START_WORK_DATE)
- hrd_person_type: ประเภทบุคลากร (HR_PERSON_TYPE_ID, HR_PERSON_TYPE_NAME เช่น ข้าราชการ, ลูกจ้างประจำ, พนักงานราชการ, พนักงานกระทรวงสาธารณสุข, ลูกจ้างรายเดือน, ลูกจ้างรายวัน, ผู้พิเศษ)
- hrd_position: ตำแหน่งสายงาน/วิชาชีพ (HR_POSITION_ID, HR_POSITION_NAME เช่น พยาบาลวิชาชีพ, นายแพทย์, เจ้าพนักงานสาธารณสุข, เภสัชกร, นักวิชาการสาธารณสุข)
- hrd_department: กลุ่มงาน/ฝ่าย (HR_DEPARTMENT_ID, HR_DEPARTMENT_NAME เช่น กลุ่มงานการพยาบาล, กลุ่มงานบริหารทั่วไป, กลุ่มงานบริการทางการแพทย์)
- hrd_department_sub: ฝ่ายย่อย/งาน (HR_DEPARTMENT_SUB_ID, HR_DEPARTMENT_SUB_NAME, HR_DEPARTMENT_ID)
- hrd_prefix: คำนำหน้าชื่อ (HR_PREFIX_ID, HR_PREFIX_NAME เช่น นาย, นาง, นางสาว, นพ., พญ.)
- hrd_status: สถานะเจ้าหน้าที่ (HR_STATUS_ID, HR_STATUS_NAME เช่น 1=ปฏิบัติงานปกติ, 2=ลาศึกษาต่อ, 3=ลาออก, 4=เกษียณ)
- hrd_leave_over: ประวัติการลา (ID, PERSON_ID, LEAVE_TYPE_ID, LEAVE_DATE_BEGIN, LEAVE_DATE_END, LEAVE_DAYS)
- gleave_type: ประเภทวันลา (LEAVE_TYPE_ID, LEAVE_TYPE_NAME เช่น ลาป่วย, ลากิจ, ลาพักผ่อน, ลาคลอด)
- supplies: ข้อมูลพัสดุ/ครุภัณฑ์ (ID, NUM as รหัสครุภัณฑ์, NAME as ชื่อพัสดุ, BUY_DATE as วันที่ซื้อ, PRICE as ราคา, STATUS_ID as สถานะ)
- supplies_types: ประเภทพัสดุครุภัณฑ์ (SUP_TYPE_ID, SUP_TYPE_NAME)
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
-- ฐานข้อมูล HOSxP (ระบบบริการผู้ป่วย, เวชระเบียน, การเงินโรงพยาบาล)
- patient: ข้อมูลประชากร/ผู้ป่วย (hn, fname as ชื่อ, lname as นามสกุล, sex as เพศ [1=ชาย, 2=หญิง], birthday as วันเกิด, cid, addrpart, mojupart, amphur, changwat)
- ovst: ข้อมูลการมาตรวจผู้ป่วยนอก (vn, hn, vstdate as วันที่ตรวจ [YYYY-MM-DD], vsttime as เวลาตรวจ, cur_dep as แผนกที่ตรวจ, pttype as สิทธิการรักษา, main_dep)
- vn_stat: สถิติผู้ป่วยนอกและค่าใช้จ่าย (vn, hn, vstdate as วันที่ตรวจ, pdx as รหัสโรคหลัก ICD10, dx0, dx1, dx2, dx3, dx4, dx5, sex, age_y as อายุเป็นปี, pttype, income as ค่าใช้จ่ายรวม, uc_money as เบิกได้, paid_money as ชำระเอง)
- ipt: ผู้ป่วยในรับ admit (an, hn, vn, regdate as วันที่รับไว้, regtime, dchdate as วันที่จำหน่าย [ถ้ายังนอน รพ. dchdate IS NULL], dchtime, dchstts as สถานะจำหน่าย, dchtype as ประเภทจำหน่าย, ward as รหัสหอผู้ป่วย, pttype, spclty)
- iptadm: เตียงและห้องพักผู้ป่วยใน (an, bedno as เลขที่เตียง, roomno as ห้องพัก)
- an_stat: สถิติผู้ป่วยใน (an, hn, regdate, dchdate, pdx as รหัสโรคหลัก, income as ยอดเงินรวม, ward, age_y)
- ovstdiag: การวินิจฉัยโรค OPD (vn, hn, icd10 as รหัสโรค, diagtype as ประเภทการวินิจฉัย [1=Principle Dx, 2=Co-morbidity, 3=Complication], vstdate)
- iptdiag: การวินิจฉัยโรค IPD (an, hn, icd10, diagtype)
- er_regist: ผู้ป่วยฉุกเฉิน ER (vn, hn, vstdate, vsttime, er_emergency_type [1=กู้ชีพ Resuscitation, 2=ฉุกเฉินเร่งด่วน Emergency, 3=ฉุกเฉิน Urgent, 4=กึ่งฉุกเฉิน Semi-urgent, 5=ไม่ฉุกเฉิน Non-urgent])
- referout: การส่งต่อผู้ป่วยไป รพ. อื่น (vn, hn, refer_date as วันที่ส่งต่อ, refer_hospcode as รหัสสถานพยาบาลปลายทาง, refer_point, with_ambulance)
- pttype: ตารางสิทธิการรักษา (pttype as รหัสสิทธิ, name as ชื่อสิทธิการรักษา, pcode)
- clinic: แผนก/คลินิก (clinic as รหัสคลินิก, name as ชื่อคลินิก)
- ward: ตึกผู้ป่วยใน/หอผู้ป่วย (ward as รหัสวอร์ด, name as ชื่อหอผู้ป่วย, bedcount as จำนวนเตียงทั้งหมด)
- icd101: พจนานุกรมรหัสโรค ICD-10 (code as รหัสโรค, name as ชื่อโรคอังกฤษ, tname as ชื่อโรคภาษาไทย)
- drugitems: คลังรายการยา (icode as รหัสยา, name as ชื่อยา, generic_name, units)
- opitemrece: รายการจ่ายยาและค่าบริการ (vn, an, hn, icode, qty, unitprice, sum_price, rxdate)
- doctor: แพทย์และบุคลากรทางการแพทย์ (code as รหัสแพทย์, name as ชื่อแพทย์)
* กฎสำคัญ HOSxP: ตาราง ipt ไม่มีฟิลด์ bedno เด็ดขาด หากต้องการนับผู้ป่วยครองเตียง/Admit ให้ใช้ COUNT(DISTINCT i.an) WHERE i.dchdate IS NULL และอัตราครองเตียงให้คำนวณร่วมกับ ward.bedcount
";
    }
}
