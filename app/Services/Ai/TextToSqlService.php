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
    public function resolveTargetDatabase(string $targetDb, string $question): string
    {
        $targetDb = strtolower(trim($targetDb));

        if (in_array($targetDb, ['hosxp', 'backoffice', 'mysql'])) {
            return $targetDb;
        }

        // Auto-detect from keywords
        $q = mb_strtolower($question);
        
        $boKeywords = ['พัสดุ', 'บุคลากร', 'พนักงาน', 'เจ้าหน้าที่', 'วันลา', 'เงินเดือน', 'ครุภัณฑ์', 'เบิก', 'สัญญา', 'จัดซื้อ', 'จัดจ้าง', 'hr', 'สารบรรณ', 'ลาป่วย', 'ลากิจ', 'hrd_'];
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

        return 'hosxp';
    }

    /**
     * Generate SQL from natural language prompt and execute it safely.
     */
    public function query(string $question, string $targetDb = 'auto'): array
    {
        $connection = $this->resolveTargetDatabase($targetDb, $question);
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

Schema ข้อมูลที่สามารถใช้ได้:
{$schemaContext}
";

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "แปลงคำถามนี้เป็น SQL: \"{$question}\""]
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
            return "สืบค้นข้อมูลจากฐานข้อมูล {$connection} สำเร็จ ไม่พบข้อมูลตามเงื่อนไขที่ระบุ";
        }

        // If single row with 1-2 columns, format directly
        if ($count === 1 && count($rows[0]) <= 3) {
            $parts = [];
            foreach ($rows[0] as $k => $v) {
                $parts[] = "{$k}: **" . (is_numeric($v) ? number_format($v) : $v) . "**";
            }
            return "ผลลัพธ์จากฐานข้อมูล {$connection}: " . implode(' | ', $parts);
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
-- ฐานข้อมูล Backoffice (งานบริหาร, บุคลากร, โครงสร้าง)
- hrd_person: ข้อมูลเจ้าหน้าที่ (ID, HR_CID as เลขบัตร, HR_PREFIX_ID, HR_FNAME as ชื่อ, HR_LNAME as นามสกุล, HR_DEPARTMENT_ID, HR_POSITION_NUM, HR_STATUS_ID (1=ปกติ), SEX, BIRTHDAY, START_WORK_DATE)
- hrd_prefix: คำนำหน้าชื่อ (HR_PREFIX_ID, HR_PREFIX_NAME)
- hrd_department: กลุ่มงาน/ฝ่าย (HR_DEPARTMENT_ID, HR_DEPARTMENT_NAME)
- hrd_department_sub: งาน/กลุ่มย่อย (HR_DEPARTMENT_SUB_ID, HR_DEPARTMENT_SUB_NAME, HR_DEPARTMENT_ID)
- hrd_leave_over: ข้อมูลการลา (ID, PERSON_ID, LEAVE_TYPE_ID, LEAVE_DATE_BEGIN, LEAVE_DATE_END, LEAVE_DAYS)
- supplies: พัสดุ/ครุภัณฑ์ (ID, NUM, NAME, BUY_DATE, PRICE, STATUS_ID)
";
        }

        if ($connection === 'mysql') {
            return "
-- ฐานข้อมูล SmartData (ระบบจัดการภายใน)
- users: ผู้ใช้งานระบบ (id, name, username, email, role, active, created_at)
- ai_knowledge_docs: เอกสารคลังความรู้ (id, title, category, file_type, file_size, status, chunks_count, created_at)
- lend_items: รายการอุปกรณ์ให้ยืม (id, item_code, item_name, category, status, total_qty, available_qty)
- lend_transactions: รายการยืม-คืน (id, item_id, borrower_name, borrow_date, return_date, status)
- customer_complains: ข้อร้องเรียน (id, topic, detail, status, created_at)
";
        }

        // Default: HOSxP
        return "
-- ฐานข้อมูล HOSxP (เวชระเบียน, บริการทางการแพทย์, สถิติผู้ป่วย)
- patient: ข้อมูลผู้ป่วย (hn, fname, lname, sex, birthday, cid, occupation, addrpart, mojupart, amphur, changwat, marrystatus)
- ovst: ข้อมูลการมาตรวจผู้ป่วยนอก (vn, hn, vstdate, vsttime, cur_dep, cur_dep_time, pttype, main_dep, o個性)
- vn_stat: สถิติการตรวจผู้ป่วยนอกและค่าใช้จ่าย (vn, hn, vstdate, pdx, dx0, dx1, dx2, dx3, dx4, dx5, sex, age_y, pttype, income, uc_money, count_in_year, pttypeno)
- ipt: ข้อมูลผู้ป่วยใน (an, hn, vn, regdate, regtime, dchdate, dchtime, dchstts, dchtype, ward, pttype, bedno)
- an_stat: สถิติผู้ป่วยใน (an, hn, regdate, dchdate, pdx, dx0, dx1, income, uc_money, ward, pttype, age_y, admdate)
- ovstdiag: การวินิจฉัยโรคผู้ป่วยนอก (vn, hn, icd10, diagtype (1=Principle Dx, 2=Co-morbidity, 3=Complication), vstdate)
- iptdiag: การวินิจฉัยโรคผู้ป่วยใน (an, hn, icd10, diagtype, modify_datetime)
- er_regist: ผู้ป่วยห้องฉุกเฉิน ER (vn, hn, vstdate, vsttime, er_emergency_type (1=Resuscitation, 2=Emergency, 3=Urgent, 4=Semi-urgent, 5=Non-urgent), finish_time, finish_er_status)
- referout: การส่งต่อผู้ป่วยออก (vn, hn, refer_date, refer_hospcode, refer_point, with_ambulance, cause_refering)
- pttype: สิทธิการรักษา (pttype, name, pcode)
- clinic: คลินิกบริการ (clinic, name)
- ward: ตึกผู้ป่วยใน (ward, name)
- icd101: พจนานุกรมรหัสโรค ICD-10 (code, name, tname)
- drugitems: รายการยา (icode, name, generic_name, units)
- opitemrece: รายการสั่งใช้ยาและค่าบริการ (vn, an, hn, icode, qty, unitprice, sum_price, rxdate)
";
    }
}
