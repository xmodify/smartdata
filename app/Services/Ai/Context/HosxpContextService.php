<?php

namespace App\Services\Ai\Context;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HosxpContextService
{
    /**
     * Check if HOSxP connection is accessible
     */
    public function isHosxpConnected(): bool
    {
        try {
            DB::connection('hosxp')->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get live HOSxP context / lookups based on user query (Doctors, ICD-10, Clinics, Wards, Rights, Drugs, Lab)
     *
     * @param string $query User's question
     * @return array|null ['text' => string, 'sources' => array]
     */
    public function getContext(string $query): ?array
    {
        try {
            if (!$this->isHosxpConnected()) {
                return null;
            }

            $contextBlocks = [];
            $sources = [];

            // 1. Check for Doctor / Medical Staff
            $doctorData = $this->getDoctorContext($query);
            if ($doctorData) {
                $contextBlocks[] = $doctorData['text'];
                $sources[] = $doctorData['source'];
            }

            // 2. Check for ICD-10 / Disease diagnoses
            $icdData = $this->getIcdContext($query);
            if ($icdData) {
                $contextBlocks[] = $icdData['text'];
                $sources[] = $icdData['source'];
            }

            // 3. Check for Clinic / Ward
            $wardData = $this->getWardAndClinicContext($query);
            if ($wardData) {
                $contextBlocks[] = $wardData['text'];
                $sources[] = $wardData['source'];
            }

            // 4. Check for Pttype / Treatment rights
            $pttypeData = $this->getPttypeContext($query);
            if ($pttypeData) {
                $contextBlocks[] = $pttypeData['text'];
                $sources[] = $pttypeData['source'];
            }

            // 5. Check for Drug items
            $drugData = $this->getDrugContext($query);
            if ($drugData) {
                $contextBlocks[] = $drugData['text'];
                $sources[] = $drugData['source'];
            }

            // 6. Check for Lab items
            $labData = $this->getLabContext($query);
            if ($labData) {
                $contextBlocks[] = $labData['text'];
                $sources[] = $labData['source'];
            }

            if (empty($contextBlocks)) {
                return null;
            }

            return [
                'text' => implode("\n\n", $contextBlocks),
                'sources' => $sources,
            ];
        } catch (\Throwable $e) {
            Log::warning("HosxpContextService getContext error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lookup Doctor info
     */
    protected function getDoctorContext(string $query): ?array
    {
        if (!preg_match('/(แพทย์|หมอ|นพ\.|พญ\.|licence|ใบประกอบ|รหัสแพทย์|doctor)/iu', $query)) {
            return null;
        }

        try {
            // Check for specific doctor code or name
            $cleanName = preg_replace('/(แพทย์|หมอ|นพ\.|พญ\.|นายแพทย์|แพทย์หญิง|ขอข้อมูล|ของ)/iu', '', $query);
            $cleanName = trim($cleanName);

            $q = DB::connection('hosxp')->table('doctor')
                ->where('active', 'Y')
                ->select(['code', 'name', 'licence_no', 'spclty']);

            if (mb_strlen($cleanName) >= 2) {
                $q->where('name', 'like', "%{$cleanName}%");
            }

            $docs = $q->limit(5)->get();
            if ($docs->isEmpty()) {
                return null;
            }

            $lines = ["-- ข้อมูลแพทย์จริงจากตาราง doctor (HOSxP):"];
            foreach ($docs as $d) {
                $lic = $d->licence_no ? " (เลข ว: {$d->licence_no})" : " (ไม่มีเลข ว.)";
                $lines[] = "  * รหัส `{$d->code}`: {$d->name}{$lic} [สาขา: {$d->spclty}]";
            }

            return [
                'text' => implode("\n", $lines),
                'source' => 'ตาราง doctor (HOSxP)'
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lookup ICD-10 disease codes
     */
    protected function getIcdContext(string $query): ?array
    {
        if (!preg_match('/(icd|icd10|icd-10|รหัสโรค|โรค|diag|pdx|เบาหวาน|ความดัน|ไต|stroke|sepsis|pneumonia|covid|dengue|มะเร็ง|[a-zA-Z]\d{2,3})/iu', $query)) {
            return null;
        }

        try {
            // Extract ICD code if present (e.g. I10, E11, A419)
            if (preg_match('/\b([A-Za-z]\d{2,3}(?:\.\d{1,2})?)\b/', $query, $m)) {
                $code = strtoupper($m[1]);
                $icd = DB::connection('hosxp')->table('icd101')
                    ->where('code', 'like', "{$code}%")
                    ->select(['code', 'name', 'tname'])
                    ->limit(5)
                    ->get();

                if ($icd->isNotEmpty()) {
                    $lines = ["-- ข้อมูลรหัสโรค ICD-10 จากตาราง icd101:"];
                    foreach ($icd as $row) {
                        $lines[] = "  * รหัส `{$row->code}`: {$row->name} ({$row->tname})";
                    }
                    return [
                        'text' => implode("\n", $lines),
                        'source' => 'ตาราง icd101 (HOSxP)'
                    ];
                }
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lookup Wards and Clinics
     */
    protected function getWardAndClinicContext(string $query): ?array
    {
        if (!preg_match('/(วอร์ด|หอผู้ป่วย|ตึก|ward|คลินิก|clinic|แผนก|dep)/iu', $query)) {
            return null;
        }

        try {
            $lines = [];
            if (preg_match('/(วอร์ด|หอผู้ป่วย|ตึก|ward)/iu', $query)) {
                $wards = DB::connection('hosxp')->table('ward')
                    ->select(['ward', 'name', 'bedcount'])
                    ->limit(10)
                    ->get();
                if ($wards->isNotEmpty()) {
                    $lines[] = "-- ข้อมูลหอผู้ป่วย (Ward) ใน HOSxP:";
                    foreach ($wards as $w) {
                        $lines[] = "  * รหัส ward `{$w->ward}`: {$w->name} (จำนวนเตียง: {$w->bedcount})";
                    }
                }
            }

            if (preg_match('/(คลินิก|clinic)/iu', $query)) {
                $clinics = DB::connection('hosxp')->table('clinic')
                    ->select(['clinic', 'name'])
                    ->limit(10)
                    ->get();
                if ($clinics->isNotEmpty()) {
                    $lines[] = "-- ข้อมูลคลินิกเฉพาะโรค (Clinic) ใน HOSxP:";
                    foreach ($clinics as $c) {
                        $lines[] = "  * รหัส clinic `{$c->clinic}`: {$c->name}";
                    }
                }
            }

            if (empty($lines)) {
                return null;
            }

            return [
                'text' => implode("\n", $lines),
                'source' => 'ตาราง ward / clinic (HOSxP)'
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lookup Pttype / Right
     */
    protected function getPttypeContext(string $query): ?array
    {
        if (!preg_match('/(สิทธิ|สิทธิการรักษา|pttype|บัตรทอง|ประกันสังคม|เบิกตรง|ข้าราชการ|ต่างด้าว|จ่ายสด|ชำระเงิน)/iu', $query)) {
            return null;
        }

        try {
            $pttypes = DB::connection('hosxp')->table('pttype')
                ->where('isuse', 'Y')
                ->select(['pttype', 'name', 'hipdata_code', 'paidst', 'price_type'])
                ->limit(10)
                ->get();

            if ($pttypes->isEmpty()) {
                return null;
            }

            $lines = ["-- ข้อมูลสิทธิการรักษาที่เปิดใช้งานในตาราง pttype (HOSxP):"];
            foreach ($pttypes as $p) {
                $lines[] = "  * รหัส `{$p->pttype}`: {$p->name} [กลุ่มสิทธิ: {$p->hipdata_code}, ชำระเงิน: {$p->paidst}, ระดับราคา: {$p->price_type}]";
            }

            return [
                'text' => implode("\n", $lines),
                'source' => 'ตาราง pttype (HOSxP)'
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lookup Drug items
     */
    protected function getDrugContext(string $query): ?array
    {
        if (!preg_match('/(ยา|คลังยา|drug|icode|ขนาดยา)/iu', $query)) {
            return null;
        }

        return null;
    }

    /**
     * Lookup Lab items
     */
    protected function getLabContext(string $query): ?array
    {
        if (!preg_match('/(แล็บ|lab|ผลตรวจ|ตรวจเลือด|ตรวจปัสสาวะ)/iu', $query)) {
            return null;
        }

        return null;
    }

    /**
     * Get Complete HOSxP Schema Dictionary & Domain Rules
     */
    public function getSchemaContext(string $query = ''): string
    {
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
- operation_list: บันทึกการผ่าตัด ห้องผ่าตัด OR (operation_id, hn, vn, an, operation_date, operation_time, room_id, status_id [1=รอผ่าตัด, 2=กำลังผ่าตัด, 3=ผ่าตัดเสร็จแล้ว], emergency_id [1=Emergency, 2=Elective], patient_department ['OPD','IPD'], request_doctor, operation_name, operation_detail_name, blood_loss, operation_list_anes_type_id, operation_anes_physical_status_id)
- operation_team: ทีมผ่าตัดและแพทย์ผู้ทำการผ่าตัด (operation_id, doctor [เชื่อม doctor.code], position_id [1=ผู้ทำการผ่าตัด/ศัลยแพทย์ Surgeon, 2=Instrument Nurse, 3=Circulate Nurse, 5=Scrub Nurse])
- operation_detail: รายการหัตถการผ่าตัด (operation_id, operation_item_id, icdcode [รหัส ICD-9-CM], price)
- operation_item: ทะเบียนรายการผ่าตัด (operation_item_id, name, icode, price, icd9)
- operation_room: ห้องผ่าตัด (room_id, room_name เช่น ห้องผ่าตัด 1, 2, 3)
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
5. ผู้ป่วยหนัก ICU: ค้นหาจาก `iptbedmove.nbedno LIKE 'ICU%'` หรือ `i.ward = '10'` หรือ `w.name LIKE '%ICU%' OR w.name LIKE '%วิกฤต%'`
6. รหัสโรคสำคัญ: Stroke (I64, I619, I639) | Sepsis (A419, A415) | Septic Shock (R572) | Pneumonia (J189, J180) | MI (I219) | CHF (I500, I509) | COPD (J449) | Asthma (J459) | Head Injury (S099, S060)
7. การสืบค้นติดตามผู้ป่วยรายบุคคลด้วย HN หรือ AN (Patient Follow-up & PDPA Privacy):
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
