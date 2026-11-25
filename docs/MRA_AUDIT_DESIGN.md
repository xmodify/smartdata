# เอกสารออกแบบระบบ Internal Audit เวชระเบียน (MRA - OPD & IPD)

เอกสารนี้สรุปโครงสร้างการออกแบบระบบ **Audit เวชระเบียน (Medical Record Audit)** สำหรับผู้ป่วยนอก (OPD) และผู้ป่วยใน (IPD) บนระบบ SmartData เพื่อเชื่อมต่อข้อมูลจาก HOSxP (Read-Only) และบันทึกผลการตรวจประเมินตามเกณฑ์มาตรฐานของ **สปสช. / สนย. / สรพ. / สภาการพยาบาล**

---

## 1. Workflow การทำงาน (Closed-Loop Audit: HOSxP ⇄ SmartData)

1. **Pre-Audit (ดึงจาก HOSxP → ตรวจและบันทึกที่ SmartData)**
   - SmartData ดึงข้อมูลเคสจากฐาน `hosxp` (Read-Only) เช่น `HN`, `VN/AN`, ประวัติการรักษา, ผลตรวจ, คำสั่งยา, สรุปชาร์ต, ICD-10 / ICD-9 และช่วยตรวจเช็กเบื้องต้นอัตโนมัติ (Pre-Check)
   - ผู้ตรวจ (Auditor) ประเมินให้คะแนนตามเกณฑ์ พร้อมระบุวันที่ตรวจ (`audit_date`) และผู้ตรวจสอบ/รับรองผล (`verifier_id`) บันทึกเป็น `audit_stage = 'pre'`
2. **ส่งคืนหน้างาน & แก้ไขต้นทางที่ HOSxP**
   - เคสที่ไม่ผ่านเกณฑ์หรือมีข้อบกพร่องจะถูกติดสถานะ `correction_status = 'returned'`
   - ระบบรองรับการแสดงหน้ารายการ **รอแก้ไข (ส่งคืนหน้างาน)**, Export CSV/Excel แยกรายวอร์ด/รายแพทย์/รายแผนก และคัดลอกข้อความแจ้งแก้ไข เพื่อให้แพทย์/พยาบาล/ทีมรหัสโรคกลับไปแก้ไขใน **HOSxP** โดยตรง
3. **Post-Audit (ดึงข้อมูลล่าสุดจาก HOSxP → ตรวจซ้ำและบันทึกที่ SmartData)**
   - เมื่อหน้างานแก้ไขใน HOSxP เสร็จแล้ว ผู้ตรวจเปิดเคสเดิมใน SmartData (ระบบดึงข้อมูลล่าสุดจาก HOSxP มาแสดง)
   - สามารถกด **ดึงผลจาก Pre-Audit มาแก้ต่อ** ปรับเฉพาะข้อที่แก้ไขแล้ว และบันทึกเป็นแถวใหม่ `audit_stage = 'post'` ที่ผูก `parent_audit_id` กลับไปยัง Pre-Audit เดิม
   - ระบบคำนวณเปรียบเทียบ **Pre vs Post Score** อัตโนมัติ (รองรับทั้งกรณีทำรอบเดียวจบ หรือทำครบ Pre + Post)

---

## 2. เกณฑ์มาตรฐานและการคิดคะแนน

### 2.1 เวชระเบียนผู้ป่วยนอก / ฉุกเฉิน (OPD / ER) — 9 หมวดหลัก
1. **Patient’s profile** (ข้อมูลผู้ป่วย) — 7 ข้อ
2. **Chief complaint** (อาการสำคัญ) — 3 ข้อ
3. **History of present illness** (ประวัติเจ็บป่วยปัจจุบัน) — 7 ข้อ
4. **Past / Family / Personal history & Risk factor** — 9 ข้อ
5. **Physical examination** (การตรวจร่างกาย) — 7 ข้อ
6. **Diagnosis / Investigation / Treatment** (การวินิจฉัย ตรวจ และรักษา) — 6 ข้อ
7. **Informed consent** (ใบแสดงความยินยอม) — 7 ข้อ
8. **Operative note** (บันทึกผ่าตัด/หัตถการ) — 6 ข้อ
9. **Progress note & Physician’s order** (บันทึกความก้าวหน้าและคำสั่งการรักษา) — 9 ข้อ

### 2.2 เวชระเบียนผู้ป่วยใน (IPD) — 12 เรื่อง × เกณฑ์ 9 ข้อ
- **กลุ่ม 1: บังคับประเมินทุกราย (7 เรื่อง)**
  1. Discharge summary: Diagnosis, Operation
  2. Discharge summary: Others
  3. Informed consent
  4. History record
  5. Physical exam record
  6. Progress note & Doctor’s order
  7. Nurses’ note
- **กลุ่ม 2: ประเมินเมื่อมีการให้บริการ (5 เรื่อง — หากไม่มีให้เลือก `NA`)**
  8. Consultation record
  9. Anesthetic record
  10. Operative note
  11. Labour record
  12. Rehabilitation record

**การให้คะแนนและคิดร้อยละ:**
- ผลต่อข้อ: `pass = 1`, `fail = 0`, `missing = 0`, `na = ไม่นำมาคิดคะแนน`
- **Full Score (IPD):** `9 × จำนวนเรื่องที่ไม่เป็น NA` (ขั้นต่ำ 63 คะแนน, สูงสุด 108 คะแนน)
- **Percent Score:** `(sum_score / full_score) * 100`

---

## 3. โครงสร้างฐานข้อมูล (SmartData `mysql`)

แยกตารางบันทึกผล **OPD** และ **IPD** ออกจากกันชัดเจน และใช้ตารางกลางสำหรับ **เกณฑ์ประเมิน** และ **รอบการตรวจ**

### 3.1 ตารางกลาง
- **`mra_criteria`**
  - `id`, `audit_type` (`opd` / `ipd`), `section_code`, `section_name`, `section_order`, `is_required_all` (boolean), `item_no`, `item_label`, `max_score`, `active`
- **`mra_rounds`**
  - `id`, `audit_type` (`opd` / `ipd`), `round_name`, `frequency` (`monthly` / `quarterly` / `custom`), `fiscal_year`, `period_month` (nullable), `start_date`, `end_date`, `target_count`, `status`, `created_by`, `timestamps`

### 3.2 ตารางฝั่งผู้ป่วยนอก (OPD)
- **`mra_opd_audits`**
  - อ้างอิง HOSxP: `vn` (index), `hn` (index), `patient_name`, `vstdate` (index), `dep_code`, `dep_name`, `doctor_code`, `doctor_name`, `pttype`, `pdx`
  - ข้อมูลรอบและผลประเมิน: `round_id` (nullable), `audit_stage` (`pre` / `post`), `parent_audit_id` (nullable, FK -> `mra_opd_audits.id`)
  - วันที่และผู้รับผิดชอบ: `audit_date` (datetime), `auditor_id`, `auditor_name`, `verifier_id`, `verifier_name`, `verified_at`
  - คะแนน: `full_score`, `sum_score`, `percent_score`
  - การส่งคืนแก้ไข: `correction_status` (`none` / `returned` / `corrected` / `completed`), `returned_at`, `returned_to`, `returned_note`, `corrected_at`
  - `timestamps`
- **`mra_opd_audit_details`**
  - `id`, `audit_id` (FK -> `mra_opd_audits.id`), `criteria_id`, `section_code`, `result` (`pass` / `fail` / `missing` / `na`), `score`, `remark`, `timestamps`

### 3.3 ตารางฝั่งผู้ป่วยใน (IPD)
- **`mra_ipd_audits`**
  - อ้างอิง HOSxP: `an` (index), `hn` (index), `patient_name`, `regdate`, `dchdate` (index), `ward`, `ward_name`, `los`, `adjrw`, `doctor_code`, `doctor_name`, `pttype`, `pdx`
  - ข้อมูลรอบและผลประเมิน: `round_id` (nullable), `audit_stage` (`pre` / `post`), `parent_audit_id` (nullable, FK -> `mra_ipd_audits.id`)
  - วันที่และผู้รับผิดชอบ: `audit_date` (datetime), `auditor_id`, `auditor_name`, `verifier_id`, `verifier_name`, `verified_at`
  - คะแนน: `full_score`, `sum_score`, `percent_score`
  - การส่งคืนแก้ไข: `correction_status` (`none` / `returned` / `corrected` / `completed`), `returned_at`, `returned_to`, `returned_note`, `corrected_at`
  - `timestamps`
- **`mra_ipd_audit_details`**
  - `id`, `audit_id` (FK -> `mra_ipd_audits.id`), `criteria_id`, `section_code`, `criterion_no` (1..9), `result` (`pass` / `fail` / `missing` / `na`), `score`, `remark`, `timestamps`

---

## 4. สิทธิ์การใช้งาน, Routes และหน้าจอที่ต้องพัฒนา

- **Permission:** ใช้ `auth()->user()->hasAccessMra()` ที่มีอยู่แล้วใน `app/Models/User.php`
- **Sidebar Menu:** เชื่อมเมนูใน `resources/views/layouts/app.blade.php`
  - `ผู้ป่วยนอก (OPD)` → `route('mra.opd.index')`
  - `ผู้ป่วยใน (IPD)` → `route('mra.ipd.index')`
- **ฟีเจอร์หลักของทั้ง OPD และ IPD:**
  1. **Dashboard & KPI รายเดือน/รายรอบ:** คะแนนเฉลี่ย Pre vs Post, ความคืบหน้าการสุ่มตรวจตามรอบ, กราฟรายเดือน, จุดที่ตกบ่อยรายหมวด/รายวอร์ด/รายแผนก
  2. **ค้นหา / สุ่มเคสจาก HOSxP (Random Sampling):** กรองตามวันที่, แผนก/วอร์ด, สิทธิ, แพทย์ หรือค้นหา `HN / VN / AN`
  3. **หน้าแบบประเมิน (Audit Form):** ดึงข้อมูล HOSxP Read-Only + Auto Pre-Check เบื้องต้น + ปุ่ม "ผ่านทั้งหมวด" + ปุ่ม "ดึงผล Pre-Audit มาทำ Post-Audit" + บันทึก `auditor` และ `verifier`
  4. **แท็บเคสส่งคืนหน้างาน & Export:** กรองเคสที่ยังไม่ผ่านเกณฑ์ (`returned`), Export CSV/Excel แยกรายวอร์ด/แพทย์ เพื่อส่งกลับไปแก้ไขใน HOSxP
