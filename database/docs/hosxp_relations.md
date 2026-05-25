# แผนผังความสัมพันธ์ตาราง HOSxP (Extracted from Project Queries)

เอกสารฉบับนี้ทำการถอดความสัมพันธ์ของตารางฐานข้อมูล HOSxP (Database Relations) จาก SQL Queries ที่เขียนใช้อยู่ใน Controller ต่างๆ ของระบบ ณ ปัจจุบัน เพื่อใช้เป็นแผนที่นำทางสำหรับ AI และนักพัฒนาในการสร้างคิวรี่และรายงานใหม่ได้อย่างแม่นยำ

---

## 1. กลุ่มระบบผู้ป่วยนอก (OPD & Diagnosis & Death)
ดึงและวิเคราะห์จาก: `OpdController`, `DiagnosisController`, `DeathController`

### ความสัมพันธ์หลัก:
* **`ovst` (ทะเบียนผู้ป่วยนอก)**
  * `ovst.hn` -> `patient.hn` (ข้อมูลทะเบียนประวัติผู้ป่วย)
  * `ovst.vn` -> `vn_stat.vn` (ข้อมูลสรุปภาพรวมราย Visit - OPD)
  * `ovst.vn` -> `opdscreen.vn` (ข้อมูลสัญญาณชีพ/ซักประวัติคัดกรอง)
  * `ovst.pttype` -> `pttype.pttype` (สิทธิการรักษาราย Visit)
  * `ovst.vn` -> `visit_pttype.vn` (สิทธิการรักษาย่อยเพิ่มเติม)
  * `ovst.vn` -> `referout.vn` (ส่งต่อผู้ป่วยนอก)
  * `ovst.ovstist` -> `ovstist.ovstist` (สถานะการมาตรวจ)

* **`vn_stat` (ตารางสรุปบริการผู้ป่วยนอก)**
  * `vn_stat.dx_doctor` -> `doctor.code` (แพทย์ผู้วินิจฉัยโรคหลัก)
  * `vn_stat.spclty` -> `spclty.spclty` (แผนกเฉพาะทางหลัก)
  * `vn_stat.vn` -> `opitemrece.vn` (รายการค่าใช้จ่าย/ยาผู้ป่วยนอก)

* **`death` (ทะเบียนผู้เสียชีวิต)**
  * `death.hn` -> `patient.hn` (ข้อมูลประวัติผู้ป่วย)
  * `death.death_cause` -> `rpt_504_name.id` (กลุ่มสาเหตุการเสียชีวิต)
  * `death.death_diag_1` -> `icd101.code` (รหัสโรคสาเหตุการตายหลัก ICD-10)

---

## 2. กลุ่มระบบผู้ป่วยใน & ICU (IPD & ICU)
ดึงและวิเคราะห์จาก: `IpdController`, `IcuController`

### ความสัมพันธ์หลัก:
* **`ipt` (ทะเบียนรับผู้ป่วยใน / Admit)**
  * `ipt.an` -> `an_stat.an` (ตารางสรุปภาพรวมผู้ป่วยใน)
  * `ipt.hn` -> `patient.hn` (ข้อมูลทะเบียนประวัติผู้ป่วย)
  * `ipt.ward` -> `ward.ward` (รหัสหอผู้ป่วยปัจจุบัน/จำหน่าย)
  * `ipt.dchstts` -> `dchstts.dchstts` (สถานะจำหน่ายจำหน่าย เช่น ดีขึ้น, ไม่ดีขึ้น)
  * `ipt.dchtype` -> `dchtype.dchtype` (ประเภทจำหน่าย เช่น แพทย์อนุญาต, ส่งต่อ)
  * `ipt.dch_doctor` -> `doctor.code` (แพทย์ผู้สั่งจำหน่ายจำหน่าย)
  * `ipt.pttype` -> `pttype.pttype` (สิทธิการรักษาราย Admit)
  * `ipt.an` -> `iptbedmove.an` (ประวัติการย้ายเตียง/วอร์ด)

* **`an_stat` (ตารางสรุปบริการผู้ป่วยใน)**
  * `an_stat.dx_doctor` -> `doctor.code` (แพทย์ผู้รักษาสรุปโรคหลัก)
  * `an_stat.an` -> `iptdiag.an` (รหัสโรคผู้ป่วยในราย AN)
  * `an_stat.an` -> `opitemrece.an` (รายการค่าใช้จ่าย/ยาผู้ป่วยใน)

---

## 3. กลุ่มระบบอุบัติเหตุ-ฉุกเฉิน (ER)
ดึงและวิเคราะห์จาก: `ErController`

### ความสัมพันธ์หลัก:
* **`er_regist` (ทะเบียนบริการฉุกเฉิน)**
  * `er_regist.vn` -> `ovst.vn` (เชื่อมโยงกับการเข้าตรวจ OPD หลัก)
  * `er_regist.er_emergency_type` -> `er_emergency_type.er_emergency_type` (ประเภทความรุนแรง 5 ระดับ)

---

## 4. กลุ่มระบบส่งต่อผู้ป่วย (Refer Out / In)
ดึงและวิเคราะห์จาก: `ReferController`

### ความสัมพันธ์หลัก:
* **`referout` (ส่งต่อผู้ป่วยไปโรงพยาบาลอื่น)**
  * `referout.hn` -> `patient.hn` (ข้อมูลทะเบียนประวัติผู้ป่วย)
  * `referout.refer_hospcode` -> `hospcode.hospcode` (รหัสสถานพยาบาลปลายทาง)
  * `referout.refer_cause` -> `refer_cause.refer_cause` (สาเหตุการส่งต่อ)
  * `referout.vn` -> `vn_stat.vn` (ข้อมูลสรุปบริการผู้ป่วยนอก)
  * `referout.spclty` -> `spclty.spclty` (แผนกเฉพาะทางที่ส่ง)
  * `referout.refer_back_status_id` -> `rpt_refer_back_status.id` (สถานะการส่งกลับข้อมูล)

---

## 5. กลุ่มระบบยาและเวชภัณฑ์ (Pharmacy & Items)
ดึงและวิเคราะห์จาก: `PharController`, `PhysicController`, `DentController`

### ความสัมพันธ์หลัก:
* **`opitemrece` (รายการเวชภัณฑ์/ยา/ค่าบริการที่จ่าย)**
  * `opitemrece.vn` หรือ `opitemrece.an` -> เชื่อมกับ Visit / Admit
  * `opitemrece.icode` -> `drugitems.icode` (ทะเบียนยารักษา - ในกรณีเป็นยา)
  * `opitemrece.icode` -> `nondrugitems.icode` (ทะเบียนเวชภัณฑ์มิใช่ยา/ค่าบริการ/หัตถการ - ในกรณีไม่ใช่ยา)
  * `opitemrece.income` -> `income.income` (หมวดหมู่รายได้หลักทางบัญชีโรงพยาบาล)
  * `opitemrece.pttype` -> `pttype.pttype` (สิทธิการรักษาของรายการนั้น ๆ)
  * `opitemrece.use_code` -> `drugusage.drugusage` (เชื่อมโยงเพื่อดึงวิธีใช้ยา/คำเตือนการใช้ยา)

* **`drugusage` (ตาราง lookup วิธีการใช้ยา - Drug Usage Instructions)**
  * `drugusage` (รหัสวิธีใช้ยา เช่น `0001`)
  * `name1`, `name2`, `name3` (รายละเอียดวิธีใช้ยาภาษาไทยบรรทัดที่ 1, 2, 3 เช่น "รับประทานครั้งละ 1 เม็ด", "วันละ 3 ครั้ง หลังอาหาร เช้า กลางวัน เย็น")
  * `ename1`, `ename2` (รายละเอียดวิธีใช้ยาภาษาอังกฤษ)

---

## 6. กลุ่มงานโรคเรื้อรัง (NCD / Chronic Diseases)
ดึงและวิเคราะห์จากตารางในฐานข้อมูล HOSxP:
* **`clinicmember` (สมาชิกคลินิกโรคเรื้อรัง)**
  * `clinicmember.hn` -> `patient.hn` (ประวัติทะเบียนผู้ป่วย)
  * `clinicmember.clinic` -> `clinic.clinic` (ตาราง lookup ชื่อคลินิกเรื้อรัง เช่น DM เบาหวาน, HT ความดัน)

---

## 7. กลุ่มงานนัดหมายผู้ป่วย (Appointments)
ดึงและวิเคราะห์จากตารางในฐานข้อมูล HOSxP:
* **`oapp` (ตารางการนัดตรวจผู้ป่วยนอก)**
  * `oapp.hn` -> `patient.hn` (ประวัติทะเบียนผู้ป่วย)
  * `oapp.vn` -> `ovst.vn` (รหัส Visit เดิมที่สั่งนัดตรวจครั้งถัดไป)
  * `oapp.spclty` -> `spclty.spclty` (แผนกเฉพาะทางที่สั่งนัดตรวจ)
  * `oapp.depcode` -> `kskdepartment.depcode` (ห้องตรวจ/จุดบริการที่นัดหมายตรวจ)
  * `oapp.doctor` -> `doctor.code` (รหัสแพทย์ผู้นัดตรวจ)

---

## 8. กลุ่มงานประวัติแพ้ยา (Drug Allergies)
ดึงและวิเคราะห์จากตารางในฐานข้อมูล HOSxP:
* **`opd_allergy` (ประวัติแพ้ยาผู้ป่วย)**
  * `opd_allergy.hn` -> `patient.hn` (ประวัติทะเบียนผู้ป่วย)
  * `opd_allergy.agent` -> มักใช้เชื่อมเพื่อดูชื่อสารเคมีหรือตัวยาที่แพ้กับ `drugitems` หรือระบุเป็นข้อความโดยตรง

---

## 9. กลุ่มงานรังสีและชันสูตร (Lab & X-Ray)
ดึงและวิเคราะห์จากตารางในฐานข้อมูล HOSxP:
* **`lab_head` (ตารางหัวใบสั่งแล็บ)**
  * `lab_head.vn` -> `ovst.vn` (ในกรณีตรวจ OPD)
  * `lab_head.an` -> `ipt.an` (ในกรณีตรวจ IPD)
  * `lab_head.hn` -> `patient.hn`
  * `lab_head.doctor` -> `doctor.code` (แพทย์สั่งตรวจแล็บ)
  * `lab_head.lab_order_number` -> `lab_order.lab_order_number` (เชื่อมโยงไปผลตรวจแล็บรายตัว)

* **`lab_order` (ตารางผลแล็บรายรายการ)**
  * `lab_order.lab_items_code` -> `lab_items.lab_items_code` (ตาราง Lookup รหัสรายการตรวจวิเคราะห์แล็บ)

* **`xray_report` (ตารางผลอ่านเอกซเรย์)**
  * `xray_report.vn` -> `ovst.vn` หรือ `xray_report.an` -> `ipt.an`
  * `xray_report.hn` -> `patient.hn`
  * `xray_report.xray_items_code` -> `xray_items.xray_items_code` (ตาราง Lookup รหัสรายการรังสีรักษา)

---

## 10. กลุ่มงานเวชระเบียน (Medical Record Audit & Coding)
ดึงและวิเคราะห์ตารางที่เกี่ยวข้องกับการบันทึกและตรวจสอบการรหัสโรค/รหัสหัตถการทางการแพทย์:

### ความสัมพันธ์หลัก:
* **`ovstdiag` (ตารางการให้รหัสวินิจฉัยโรคสำหรับผู้ป่วยนอก - OPD Diagnosis)**
  * `ovstdiag.vn` -> `ovst.vn` (เชื่อมโยงกับ Visit การมารับบริการผู้ป่วยนอก)
  * `ovstdiag.hn` -> `patient.hn` (เชื่อมทะเบียนประวัติผู้ป่วย)
  * `ovstdiag.icd10` -> `icd101.code` (เชื่อมตารางรหัสและชื่อโรค ICD-10)
  * `ovstdiag.diagtype` -> `diagtype.diagtype` (ประเภทการวินิจฉัย เช่น Principal Diagnosis, Comorbidity, External Cause)
  * `ovstdiag.doctor` -> `doctor.code` (รหัสแพทย์ผู้วินิจฉัย)

* **`iptdiag` (ตารางการให้รหัสวินิจฉัยโรคสำหรับผู้ป่วยใน - IPD Diagnosis)**
  * `iptdiag.an` -> `ipt.an` (เชื่อมโยงกับการครองเตียง/Admit ผู้ป่วยใน)
  * `iptdiag.hn` -> `patient.hn` (เชื่อมทะเบียนประวัติผู้ป่วย)
  * `iptdiag.icd10` -> `icd101.code` (เชื่อมตารางรหัสและชื่อโรค ICD-10)
  * `iptdiag.diagtype` -> `diagtype.diagtype` (ประเภทการวินิจฉัย เช่น Principal Diagnosis, Comorbidity)
  * `iptdiag.doctor` -> `doctor.code` (รหัสแพทย์ผู้วินิจฉัย)

* **`iptoprt` (ตารางการให้รหัสหัตถการ/ผ่าตัดสำหรับผู้ป่วยใน - IPD Procedures)**
  * `iptoprt.an` -> `ipt.an` (เชื่อมโยงกับการครองเตียง/Admit ผู้ป่วยใน)
  * `iptoprt.icd9` -> `icd9cm1.code` (เชื่อมตารางรหัสหัตถการผ่าตัด ICD-9-CM)
  * `iptoprt.doctor` -> `doctor.code` (แพทย์ผู้ทำหัตถการ)

* **`doctor_operation` (ตารางการผ่าตัด/หัตถการทั่วไปหรือผู้ป่วยนอก - General/OPD Operation)**
  * `doctor_operation.vn` -> `ovst.vn` (เชื่อมโยงกับ Visit การมารับบริการผู้ป่วยนอก)
  * `doctor_operation.icd9` -> `icd9cm1.code` (เชื่อมตารางรหัสหัตถการผ่าตัด ICD-9-CM)
  * `doctor_operation.doctor` -> `doctor.code` (แพทย์ผู้ผ่าตัด/ทำหัตถการ)

* **`er_regist_oper` (ตารางหัตถการห้องฉุกเฉิน - ER Procedures)**
  * `er_regist_oper.vn` -> `ovst.vn` หรือ `er_regist.vn` (เชื่อมโยงกับ Visit ห้องฉุกเฉิน)
  * `er_regist_oper.doctor` -> `doctor.code` (แพทย์ผู้ทำหัตถการ)

### ตาราง Lookup สำคัญด้านการให้รหัสโรคและรหัสหัตถการ (Coding Lookups):
* **`icd101` (ตารางรหัสโรคมาตรฐานสากล ICD-10)**
  * `code` (รหัสโรค เช่น `E11.9`, `I10`)
  * `name` (ชื่อโรคภาษาอังกฤษ)
  * `tname` (ชื่อโรคภาษาไทย)
  * `ipd_valid` (ใช้ตรวจสอบว่าเหมาะสำหรับการให้รหัสผู้ป่วยในหรือไม่)
  * `active_status` (สถานะการใช้งาน)

* **`icd9cm1` (ตารางรหัสหัตถการและการผ่าตัดมาตรฐานสากล ICD-9-CM)**
  * `code` (รหัสหัตถการ เช่น `38.93`, `47.09`)
  * `name` (ชื่อหัตถการภาษาอังกฤษ)
  * `active_status` (สถานะการใช้งาน)

* **`diagtype` (ตารางประเภทการวินิจฉัยโรค - Diagnosis Type)**
  * `diagtype` (รหัสประเภทการวินิจฉัย)
    * `01` = Principal Diagnosis (การวินิจฉัยโรคหลัก)
    * `02` = Secondary Diagnosis (การวินิจฉัยโรคร่วม / Comorbidity)
    * `03` = Complication (โรคแทรกซ้อน)
    * `04` = Other Diagnosis (โรคอื่น ๆ)
    * `05` = External Cause of Injury (สาเหตุภายนอกของการบาดเจ็บ เช่น อุบัติเหตุทางถนน)
  * `name` (ชื่อประเภทการวินิจฉัย)
  * `nhso_code` (รหัสสำหรับส่งออก สปสช.)

---

## 11. กลุ่มงานข้อมูลพื้นฐานและข้อมูลประชากรศาสตร์ (Demographics & Administrative Lookups)
ตารางหลักที่ใช้จัดเก็บประวัติข้อมูลประชากรศาสตร์ของผู้ป่วย และสิทธิ์การใช้งานของบุคลากรภายในโรงพยาบาล (ที่มักถูกเรียกใช้งานบ่อยในการออกรายงานเพื่อกรองหรือแสดงข้อมูลผู้ใช้):

### ความสัมพันธ์หลัก:
* **`iptadm` (ตารางบันทึกการครองเตียงและค่าเตียงรายวันของผู้ป่วยใน - IPD Admission Details)**
  * `iptadm.an` -> `ipt.an` (เชื่อมโยงกับการสั่ง Admit)
  * `iptadm.admday` (จำนวนวันนอนโรงพยาบาล)
  * `iptadm.rate` (อัตราค่าห้อง/ค่าเตียง)

* **`ptcardno` (ตารางจัดเก็บเลขที่บัตรต่าง ๆ ของผู้ป่วย - Patient Card Numbers)**
  * `ptcardno.hn` -> `patient.hn` (เชื่อมข้อมูลประวัติผู้ป่วย)
  * `ptcardno.cardtype` (ประเภทบัตร เช่น บัตรประชาชน, บัตรโรงพยาบาล)
  * `ptcardno.cardno` (หมายเลขบัตร)

### ตาราง Lookup ข้อมูลพื้นฐานผู้ป่วย (Patient Lookup Tables):
* **`sex` (ตารางรหัสเพศ - Gender)**
  * `code` (รหัสเพศ: `1` = ชาย, `2` = หญิง)
  * `name` (ชื่อเพศภาษาไทย)

* **`thaiaddress` (ตารางที่อยู่และรหัสไปรษณีย์ในประเทศไทย - Thai Address Directory)**
  * `addressid` (รหัสจังหวัด+อำเภอ+ตำบล เช่น `300101`)
  * `name` (ชื่อตำบล/อำเภอ/จังหวัด)
  * `chwpart` (รหัสจังหวัด), `amppart` (รหัสอำเภอ), `tmbpart` (รหัสตำบล)
  * `full_name` (ที่อยู่แบบเต็ม เช่น "อ.เมือง จ.นครราชสีมา")

* **`nationality` (ตารางสัญชาติ - Nationality)**
  * `nationality` (รหัสสัญชาติ)
  * `name` (ชื่อสัญชาติ เช่น ไทย, พม่า, ลาว)

* **`marrystatus` (ตารางสถานะการสมรส - Marital Status)**
  * `code` (รหัสสถานะ เช่น `1` = โสด, `2` = คู่)
  * `name` (ชื่อสถานะ เช่น โสด, สมรส, หย่าร้าง)

### ตารางเกี่ยวกับบุคลากรและสิทธิ์การใช้งาน (Hospital Staff & User Accounts):
* **`opduser` (ตารางบัญชีผู้ใช้งานระบบ HOSxP - System Users)**
  * `loginname` (ชื่อที่ใช้เข้าสู่ระบบ)
  * `name` (ชื่อ-นามสกุลจริงของผู้ใช้งาน)
  * `doctorcode` -> `doctor.code` (เชื่อมกับรหัสแพทย์ หากผู้ใช้นั้นเป็นแพทย์)
  * `department` (จุดบริการ/ห้องตรวจที่สังกัดหลัก)

* **`officer` (ตารางรายชื่อเจ้าหน้าที่และบุคลากรโรงพยาบาล - Hospital Personnel)**
  * `officer_login_name` -> `opduser.loginname` (เชื่อมข้อมูลการเข้าระบบ)
  * `officer_doctor_code` -> `doctor.code` (เชื่อมรหัสแพทย์)
  * `officer_name` (ชื่อเจ้าหน้าที่)

---

## 12. กลุ่มงานการเงินและบัญชีโรงพยาบาล (Hospital Finance & Billing)
ตารางสำคัญที่ใช้ในการจัดการค่าใช้จ่าย ออกใบเสร็จรับเงิน และบริหารจัดการหนี้สิน/ลูกหนี้ค่ารักษาพยาบาล:

### ความสัมพันธ์หลัก:
* **`rcpt_print` (ตารางบันทึกการออกใบเสร็จรับเงินค่ารักษาพยาบาล - Receipt Payment Logs)**
  * `rcpt_print.hn` -> `patient.hn` (เชื่อมข้อมูลผู้ป่วย)
  * `rcpt_print.vn` -> `ovst.vn` (กรณีผู้ป่วยนอก OPD)
  * `rcpt_print.finance_number` (เลขที่ใบเสร็จทางการเงิน)
  * `rcpt_print.bill_amount` (ยอดเงินตามใบแจ้งหนี้)
  * `rcpt_print.total_amount` (ยอดเงินรวมที่รับชำระจริง)
  * `rcpt_print.discount` (ส่วนลดค่ารักษา)
  * `rcpt_print.pttype` -> `pttype.pttype` (เชื่อมโยงสิทธิที่ใช้ชำระเงิน)
  * `rcpt_print.bill_staff` -> `opduser.name` หรือ `officer.officer_name` (รหัสเจ้าหน้าที่การเงินผู้รับเงิน/ออกใบเสร็จ)

* **`rcpt_arrear` (ตารางบันทึกข้อมูลค้างชำระ/ลูกหนี้ค่ารักษาพยาบาล - Hospital Receivables & Arrears)**
  * `rcpt_arrear.hn` -> `patient.hn` (เชื่อมข้อมูลผู้ป่วย)
  * `rcpt_arrear.vn` -> `ovst.vn` (กรณี OPD)
  * `rcpt_arrear.an` -> `ipt.an` (กรณี IPD)
  * `rcpt_arrear.amount` (จำนวนยอดเงินค้างชำระทั้งหมด)
  * `rcpt_arrear.paid` (สถานะการชำระหนี้: `Y` = ชำระแล้ว, `N` หรือว่าง = ยังค้างชำระ)
  * `rcpt_arrear.rcpno` -> `rcpt_print.rcpno` (เลขที่ใบเสร็จเมื่อมีการมาชำระเงินภายหลัง)
  * `rcpt_arrear.finance_number` -> `rcpt_print.finance_number` (เชื่อมเลขที่ใบเสร็จทางการเงิน)

---

## 13. กลุ่มงานข้อมูลหลักระดับองค์กร (Core Master Tables - Patient, Doctor & Village)
ตารางหลักที่ใช้เป็นฐานข้อมูลกลาง (Master Data) สำหรับคนไข้ แพทย์ และหมู่บ้านในเขตรับผิดชอบ:

### ตารางหลัก:
* **`patient` (ทะเบียนประวัติผู้ป่วยนอก/ข้อมูลหลักคนไข้ - Patient Master Registry)**
  * `hn` (หมายเลขประจำตัวผู้ป่วย/รหัสหลักคนไข้ - Primary Key)
  * `cid` (เลขบัตรประจำตัวประชาชน 13 หลัก)
  * `pname` (คำนำหน้าชื่อ), `fname` (ชื่อ), `lname` (นามสกุล)
  * `birthday` (วันเกิด), `sex` (เพศ: `1`=ชาย, `2`=หญิง)
  * `bloodgrp` (กลุ่มเลือด เช่น A, B, O, AB)
  * `addrpart` (บ้านเลขที่), `moopart` (หมู่ที่), `tmbpart` (รหัสตำบล), `amppart` (รหัสอำเภอ), `chwpart` (รหัสจังหวัด)
  * `addressid` -> `thaiaddress.addressid` (เชื่อมโยงที่อยู่เต็ม)
  * `nationality` -> `nationality.nationality` (สัญชาติ)
  * `marrystatus` -> `marrystatus.code` (สถานะการสมรส)
  * `pttype` -> `pttype.pttype` (สิทธิการรักษาพยาบาลหลักเริ่มต้น)
  * `death` (สถานะการเสียชีวิต: `Y` = เสียชีวิตแล้ว)

* **`doctor` (ทะเบียนข้อมูลและรหัสผู้ตรวจรักษา/แพทย์ - Doctor Registry)**
  * `code` (รหัสประจำตัวแพทย์ - Primary Key)
  * `cid` (เลขบัตรประชาชนแพทย์)
  * `pname` (คำนำหน้า), `fname` (ชื่อ), `lname` (นามสกุล)
  * `licenseno` (เลขที่ใบอนุญาตประกอบวิชาชีพเวชกรรม)
  * `spclty` -> `spclty.spclty` (แผนกเฉพาะทางหลักของแพทย์)
  * `active` (สถานะการทำงาน: `Y` = ยังปฏิบัติงานอยู่, `N` = พ้นสภาพ/ไม่ได้ปฏิบัติงานแล้ว)

* **`village` (ทะเบียนหมู่บ้านในเขตรับผิดชอบ - Local Health Jurisdiction Villages)**
  * `village_id` (รหัสไอดีหมู่บ้าน - Primary Key)
  * `village_moo` (หมายเลขหมู่บ้าน/หมู่ที่ เช่น `หมู่ 1`)
  * `village_name` (ชื่อหมู่บ้าน)
  * `address_id` -> `thaiaddress.addressid` (เชื่อมเพื่อระบุ ตำบล/อำเภอ/จังหวัด ของหมู่บ้าน)
  * `doctor_code` -> `doctor.code` (แพทย์หรือเจ้าหน้าที่สาธารณสุขผู้รับผิดชอบหลักของหมู่บ้านนั้น ๆ)
  * `latitude`, `longitude` (พิกัด GPS สำหรับใช้ทำแผนที่สุขภาพ)
  * `family_count` (จำนวนหลังคาเรือน/ครอบครัว), `person_count` (จำนวนประชากรรวมในหมู่บ้าน)

---

## 14. กลุ่มงานบริการปฐมภูมิและงานเชิงรุกชุมชน (PCU & Community Health Services / Proactive Care)
ตารางสำหรับการทำงานเชิงรุกด้านการส่งเสริมสุขภาพ การคัดกรองโรคเรื้อรังในชุมชน และข้อมูลแผนที่ครอบครัวในเขตรับผิดชอบ (Primary Care Unit):

### ความสัมพันธ์หลัก:
* **`person` (ทะเบียนรายชื่อบุคคลในเขตรับผิดชอบเชิงรุก - PCU Person Master)**
  * `person_id` (รหัสประจำตัวบุคคลเชิงรุก - Primary Key)
  * `patient_hn` -> `patient.hn` (เชื่อมโยงประวัติการเข้ารักษาในโรงพยาบาล หากเคยมาตรวจที่รพ.หลัก)
  * `house_id` -> `house.house_id` (เชื่อมโยงบ้านพักอาศัยในชุมชน)
  * `village_id` -> `village.village_id` (เชื่อมโยงหมู่บ้านที่อาศัยอยู่)
  * `cid` (หมายเลขบัตรประชาชน 13 หลัก)
  * `pname` (คำนำหน้า), `fname` (ชื่อ), `lname` (นามสกุล)
  * `birthdate` (วันเกิด), `sex` (เพศ)
  * `blood_group` (กลุ่มเลือด)
  * `marrystatus` -> `marrystatus.code` (สถานะการสมรส)
  * `pttype` -> `pttype.pttype` (สิทธิ์การรักษาพยาบาล)
  * `death` (สถานะการเสียชีวิต: `Y` = เสียชีวิตแล้ว)

* **`house` (ตารางข้อมูลบ้านเรือน/ที่อยู่อาศัยในเขตรับผิดชอบ - Household Registry)**
  * `house_id` (รหัสประจำตัวบ้าน - Primary Key)
  * `village_id` -> `village.village_id` (เชื่อมโยงหมู่บ้านที่ตั้งของบ้าน)
  * `address` (เลขที่บ้าน/ที่อยู่จดทะเบียน)
  * `road` (ถนน/ซอย)
  * `latitude`, `longitude` (พิกัดแผนที่บ้านของผู้ป่วย/ประชากร)
  * `doctor_code` -> `doctor.code` (รหัสเจ้าหน้าที่/อสม./แพทย์ผู้ดูแลรับผิดชอบบ้านหลังนี้)
  * `head_person_id` -> `person.person_id` (เชื่อมโยงระบุตัวหัวหน้าครอบครัว)

* **`person_chronic` (ทะเบียนการขึ้นทะเบียนโรคเรื้อรังในชุมชนเชิงรุก - PCU Community Chronic Patients)**
  * `person_id` -> `person.person_id` (เชื่อมโยงตัวบุคคลที่ป่วย)
  * `clinic` -> `clinic.clinic` (เชื่อมคลินิกโรคเรื้อรัง เช่น DM เบาหวาน, HT ความดัน)
  * `icd10` -> `icd101.code` (รหัสโรคตามมาตรฐาน ICD-10)
  * `regdate` (วันที่ขึ้นทะเบียนโรคเรื้อรัง)
  * `discharge` (สถานะจำหน่ายออกจากทะเบียนโรคเรื้อรังชุมชน: `Y` = จำหน่ายแล้ว เช่น ย้ายที่อยู่หรือเสียชีวิต)






