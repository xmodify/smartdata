<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Ai\AiManager;

$provider = AiManager::getActiveProvider();

$history = [
    ['role' => 'user', 'content' => 'บุคลากรกี่คน', 'sql' => "SELECT COUNT(ID) AS 'จำนวนบุคลากร' FROM hrd_person WHERE HR_STATUS_ID = 1"],
    ['role' => 'assistant', 'content' => 'ผลลัพธ์จากฐานข้อมูล backoffice: จำนวนบุคลากร: 234'],
    ['role' => 'user', 'content' => 'กี่ประเภท', 'sql' => "SELECT COALESCE(t.HR_PERSON_TYPE_NAME, 'ไม่ระบุ') AS 'ประเภทบุคลากร', COUNT(p.ID) AS 'จำนวนคน' FROM hrd_person p LEFT JOIN hrd_person_type t ON p.HR_PERSON_TYPE_ID = t.HR_PERSON_TYPE_ID WHERE p.HR_STATUS_ID = 1 GROUP BY p.HR_PERSON_TYPE_ID, t.HR_PERSON_TYPE_NAME ORDER BY `จำนวนคน` DESC"],
    ['role' => 'assistant', 'content' => 'พบทั้งหมด 7 ประเภท ข้าราชการ 98 คน...']
];

$backofficeSchema = "
-- ฐานข้อมูล Backoffice (งานบริหารบุคคล, สารบรรณ, พัสดุ, ครุภัณฑ์)
- hrd_person: ข้อมูลบุคลากร/เจ้าหน้าที่ (ID, HR_CID as เลขบัตรประชาชน, HR_PREFIX_ID as คำนำหน้า, HR_FNAME as ชื่อ, HR_LNAME as นามสกุล, HR_DEPARTMENT_ID as รหัสกลุ่มงาน/ฝ่าย, HR_DEPARTMENT_SUB_ID as รหัสงานย่อย, HR_PERSON_TYPE_ID as รหัสประเภทบุคลากร, HR_POSITION_ID as รหัสตำแหน่งสายงาน, HR_STATUS_ID as สถานะการทำงาน [1=ปฏิบัติงานปกติ], SEX, BIRTHDAY, START_WORK_DATE)
- hrd_person_type: ประเภทบุคลากร (HR_PERSON_TYPE_ID, HR_PERSON_TYPE_NAME เช่น ข้าราชการ, ลูกจ้างประจำ, พนักงานราชการ, พนักงานกระทรวงสาธารณสุข, ลูกจ้างรายเดือน, ลูกจ้างรายวัน, ผู้พิเศษ)
- hrd_position: ตำแหน่งสายงาน (HR_POSITION_ID, HR_POSITION_NAME เช่น พยาบาลวิชาชีพ, นายแพทย์, เจ้าพนักงานสาธารณสุข, เภสัชกร, นักวิชาการสาธารณสุข)
- hrd_department: กลุ่มงาน/ฝ่าย (HR_DEPARTMENT_ID, HR_DEPARTMENT_NAME เช่น กลุ่มงานการพยาบาล, กลุ่มงานบริหารทั่วไป, กลุ่มงานบริการทางการแพทย์)
- hrd_department_sub: งานย่อย (HR_DEPARTMENT_SUB_ID, HR_DEPARTMENT_SUB_NAME, HR_DEPARTMENT_ID)
- hrd_prefix: คำนำหน้าชื่อ (HR_PREFIX_ID, HR_PREFIX_NAME เช่น นาย, นาง, นางสาว, นพ., พญ.)
- hrd_status: สถานะเจ้าหน้าที่ (HR_STATUS_ID, HR_STATUS_NAME เช่น 1=ปฏิบัติงานปกติ, 2=ลาศึกษาต่อ, 3=ลาออก, 4=เกษียณ)
- hrd_leave_over: ประวัติการลา (ID, PERSON_ID, LEAVE_TYPE_ID, LEAVE_DATE_BEGIN, LEAVE_DATE_END, LEAVE_DAYS)
- gleave_type: ประเภทวันลา (LEAVE_TYPE_ID, LEAVE_TYPE_NAME เช่น ลาป่วย, ลากิจ, ลาพักผ่อน, ลาคลอด)
- supplies: ข้อมูลพัสดุ/ครุภัณฑ์ (ID, NUM as รหัสครุภัณฑ์, NAME as ชื่อพัสดุ, BUY_DATE as วันที่ซื้อ, PRICE as ราคา, STATUS_ID as สถานะ)
- supplies_types: ประเภทพัสดุครุภัณฑ์ (SUP_TYPE_ID, SUP_TYPE_NAME)
";

$systemPrompt = "คุณคือ Expert Database Architect & SQL Generator สำหรับระบบโรงพยาบาล
ฐานข้อมูลเป้าหมายคือ MySQL: [Connection: backoffice]
กฎสำคัญสูงสุด:
1. ตอบกลับเป็นคำสั่ง SQL `SELECT` ที่สมบูรณ์เพียงคำสั่งเดียวเท่านั้น โดยต้องอยู่ใน Markdown Block: ```sql ... ```
2. ห้ามใช้คำสั่ง DDL/DML เด็ดขาด (ห้าม INSERT, UPDATE, DELETE, DROP, ALTER, TRUNCATE)
3. สำหรับวันที่ หากระบุ 'วันนี้' ให้ใช้ CURDATE() หากระบุปีงบประมาณไทย เช่น 2568 (วันที่ 2024-10-01 ถึง 2025-09-30) หรือแปลงพุทธศักราชเป็น ค.ศ. (ปี พ.ศ. - 543)
4. กำหนด LIMIT สูงสุดไม่เกิน 100 แถวเสมอ (เช่น LIMIT 100)
5. ใช้ชื่อฟิลด์ภาษาอังกฤษตาม Schema ที่ให้มาด้านล่าง
6. เขียนคำสั่ง SQL ที่มีประสิทธิภาพ พร้อมตั้งชื่อ Alias คอลัมน์เป็นภาษาไทยที่อ่านง่าย เช่น `count(*) as 'จำนวนคน'`
7. หากคำถามถามเรื่องประเภท หรือถามต่อเนื่องว่า 'กี่ประเภท' หรือ 'แยกตาม...' ให้เขียนคำสั่งที่แจกแจงตามประเภทนั้นๆ พร้อมนับจำนวน (GROUP BY และ COUNT) เพื่อให้ผู้ใช้เห็นรายละเอียดและจำนวนครบถ้วน

Schema ข้อมูลที่สามารถใช้ได้:
{$backofficeSchema}
";

$contextStr = "บริบทการสนทนาก่อนหน้านี้ในเซสชัน:\n";
foreach ($history as $h) {
    $role = ($h['role'] === 'user') ? 'ผู้ใช้' : 'ผู้ช่วย Copilot';
    $contextStr .= "- {$role}: {$h['content']}\n";
    if (!empty($h['sql'])) {
        $contextStr .= "  [SQL ก่อนหน้า]: {$h['sql']}\n";
    }
}

$question = "แยกตามกลุ่มงาน";
$userPrompt = "{$contextStr}\nคำถามต่อเนื่องล่าสุดของผู้ใช้: \"{$question}\"\n(คำแนะนำ: ตีความคำถามต่อเนื่องโดยอิงจากเอนทิตีหรือหัวข้อที่เพิ่งพูดถึงก่อนหน้า เช่น หากถาม 'แยกตามกลุ่มงาน' ให้สร้าง SQL สรุปตามกลุ่มงานของบุคลากร)";

$messages = [
    ['role' => 'system', 'content' => $systemPrompt],
    ['role' => 'user', 'content' => $userPrompt]
];

$res = $provider->chat($messages, ['temperature' => 0.1]);
echo "Response from LLM for 'แยกตามกลุ่มงาน':\n" . $res . "\n";

if (preg_match('/```sql\s*([\s\S]*?)\s*```/i', $res, $matches)) {
    $sql = trim($matches[1]);
    try {
        $rows = DB::connection('backoffice')->select($sql);
        echo "Results count: " . count($rows) . "\n";
        foreach ($rows as $r) {
            echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
        }
    } catch (Exception $e) {
        echo "SQL Execution Error: " . $e->getMessage() . "\n";
    }
}
