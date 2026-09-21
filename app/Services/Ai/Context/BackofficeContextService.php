<?php

namespace App\Services\Ai\Context;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackofficeContextService
{
    /**
     * Check if Backoffice connection is accessible
     */
    public function isBackofficeConnected(): bool
    {
        try {
            DB::connection('backoffice')->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get live Backoffice context / lookups based on user query
     *
     * @param string $query User's question
     * @return array|null ['text' => string, 'sources' => array]
     */
    public function getContext(string $query): ?array
    {
        try {
            if (!$this->isBackofficeConnected()) {
                return null;
            }

            $contextBlocks = [];
            $sources = [];

            // 1. Check for Risk management / severity level lookup
            $riskData = $this->getRiskContext($query);
            if ($riskData) {
                $contextBlocks[] = $riskData['text'];
                $sources[] = $riskData['source'];
            }

            // 2. Check for HR / Departments
            $hrData = $this->getDepartmentContext($query);
            if ($hrData) {
                $contextBlocks[] = $hrData['text'];
                $sources[] = $hrData['source'];
            }

            // 3. Check for Asset categories & status
            $assetData = $this->getAssetContext($query);
            if ($assetData) {
                $contextBlocks[] = $assetData['text'];
                $sources[] = $assetData['source'];
            }

            // 4. Check for Supplies types
            $suppliesData = $this->getSuppliesContext($query);
            if ($suppliesData) {
                $contextBlocks[] = $suppliesData['text'];
                $sources[] = $suppliesData['source'];
            }

            if (empty($contextBlocks)) {
                return null;
            }

            return [
                'text' => implode("\n\n", $contextBlocks),
                'sources' => $sources,
            ];
        } catch (\Throwable $e) {
            Log::warning("BackofficeContextService getContext error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lookup Risk Severity Levels & Programs
     */
    protected function getRiskContext(string $query): ?array
    {
        if (!preg_match('/(ความเสี่ยง|อุบัติการณ์|ระดับความรุนแรง|risk|โปรแกรมความเสี่ยง|รุนแรง|risk_rep)/iu', $query)) {
            return null;
        }

        try {
            $lines = ["-- ข้อมูลระดับความรุนแรงของอุบัติการณ์ความเสี่ยง (Lookup จาก risk_rep_level ใน Backoffice):"];
            $levels = DB::connection('backoffice')->table('risk_rep_level')
                ->select(['RISK_REP_LEVEL_ID', 'RISK_REP_LEVEL_NAME', 'RISK_REP_LEVEL_DETAIL'])
                ->orderBy('RISK_REP_LEVEL_NAME', 'asc')
                ->get();

            if ($levels->isNotEmpty()) {
                foreach ($levels as $lvl) {
                    $lines[] = "  * ID `{$lvl->RISK_REP_LEVEL_ID}`: ระดับ {$lvl->RISK_REP_LEVEL_NAME} ({$lvl->RISK_REP_LEVEL_DETAIL})";
                }
            }

            $programs = DB::connection('backoffice')->table('risk_rep_program')
                ->select(['RISK_REPPROGRAM_ID', 'RISK_REPPROGRAM_NAME'])
                ->limit(6)
                ->get();

            if ($programs->isNotEmpty()) {
                $lines[] = "-- โปรแกรมความเสี่ยงหลัก (risk_rep_program):";
                foreach ($programs as $p) {
                    $lines[] = "  * ID `{$p->RISK_REPPROGRAM_ID}`: {$p->RISK_REPPROGRAM_NAME}";
                }
            }

            return [
                'text' => implode("\n", $lines),
                'source' => 'ตาราง risk_rep_level / risk_rep_program (Backoffice)'
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lookup HR Departments
     */
    protected function getDepartmentContext(string $query): ?array
    {
        if (!preg_match('/(กลุ่มงาน|ฝ่าย|แผนก|หน่วยงาน|บุคลากร|เจ้าหน้าที่|hrd)/iu', $query)) {
            return null;
        }

        try {
            $deps = DB::connection('backoffice')->table('hrd_department')
                ->select(['HR_DEPARTMENT_ID', 'HR_DEPARTMENT_NAME'])
                ->limit(10)
                ->get();

            if ($deps->isEmpty()) {
                return null;
            }

            $lines = ["-- รายชื่อกลุ่มงาน/ฝ่ายหลัก (hrd_department ใน Backoffice):"];
            foreach ($deps as $d) {
                $lines[] = "  * ID `{$d->HR_DEPARTMENT_ID}`: {$d->HR_DEPARTMENT_NAME}";
            }

            return [
                'text' => implode("\n", $lines),
                'source' => 'ตาราง hrd_department (Backoffice)'
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lookup Asset categories & status
     */
    protected function getAssetContext(string $query): ?array
    {
        if (!preg_match('/(ครุภัณฑ์|คอมพิวเตอร์|สินทรัพย์|แทงจำหน่าย|asset)/iu', $query)) {
            return null;
        }

        try {
            $statuses = DB::connection('backoffice')->table('asset_status')
                ->select(['STATUS_ID', 'STATUS_NAME'])
                ->get();

            $declines = DB::connection('backoffice')->table('supplies_decline')
                ->select(['DECLINE_ID', 'DECLINE_NAME'])
                ->limit(6)
                ->get();

            $lines = ["-- ข้อมูลสถานะและประเภทครุภัณฑ์ (Backoffice):"];
            if ($statuses->isNotEmpty()) {
                $lines[] = "สถานะครุภัณฑ์ (asset_status): " . $statuses->map(fn($s) => "{$s->STATUS_ID}={$s->STATUS_NAME}")->implode(', ');
            }
            if ($declines->isNotEmpty()) {
                $lines[] = "ประเภทครุภัณฑ์ (supplies_decline): " . $declines->map(fn($d) => "{$d->DECLINE_ID}={$d->DECLINE_NAME}")->implode(', ');
            }

            return [
                'text' => implode("\n", $lines),
                'source' => 'ตาราง asset_status / supplies_decline (Backoffice)'
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lookup Supplies types
     */
    protected function getSuppliesContext(string $query): ?array
    {
        if (!preg_match('/(พัสดุ|วัสดุ|จัดซื้อ|เบิกพัสดุ|supplies)/iu', $query)) {
            return null;
        }

        try {
            $types = DB::connection('backoffice')->table('supplies_type')
                ->select(['SUP_TYPE_ID', 'SUP_TYPE_NAME'])
                ->limit(8)
                ->get();

            if ($types->isEmpty()) {
                return null;
            }

            $lines = ["-- หมวดหมู่วัสดุพัสดุ (supplies_type ใน Backoffice):"];
            foreach ($types as $t) {
                $lines[] = "  * ID `{$t->SUP_TYPE_ID}`: {$t->SUP_TYPE_NAME}";
            }

            return [
                'text' => implode("\n", $lines),
                'source' => 'ตาราง supplies_type (Backoffice)'
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get Complete Backoffice Schema Dictionary & Domain Rules
     */
    public function getSchemaContext(string $query = ''): string
    {
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
- supplies: ทะเบียนวัสดุ/พัสดุโรงพยาบาล (ID, SUP_FSN_NUM [รหัส FSN], SUP_NAME [ชื่อวัสดุพัสดุ], SUP_TYPE_ID [รหัสหมวด เชื่อม supplies_type.SUP_TYPE_ID], PRICE_LAST [ราคาซื้อล่าสุด], PRICE_CENTER [ราคากลาง])
- supplies_type: หมวดหมู่ประเภทพัสดุ/วัสดุ (SUP_TYPE_ID [PK], SUP_TYPE_NAME เช่น 'วัสดุการแพทย์ทั่วไป', 'วัสดุทันตกรรม', 'วัสดุวิทยาศาสตร์หรือการแพทย์', 'วัสดุงานบ้านงานครัว', 'วัสดุบริโภค', 'วัสดุคอมพิวเตอร์', 'วัสดุสำนักงาน', 'ครุภัณฑ์การแพทย์', 'ครุภัณฑ์สำนักงาน', 'ครุภัณฑ์คอมพิวเตอร์')
- supplies_con: สัญญาจัดซื้อจัดจ้าง/โครงการ (ID, CON_NUM [เลขที่สัญญา/ข้อตกลง], CON_YEAR_ID [ปีงบประมาณ พ.ศ. เช่น '2569'], DATE_REGIS [วันที่ลงทะเบียน], DEP_REQUEST_NAME [ฝ่ายที่ขอซื้อ], PERSON_REQUEST_NAME [ผู้ขอซื้อ], CON_PROJECT_NAME [ชื่อโครงการ], EGP_PLAN_NAME)
- supplies_con_list: รายการสิ่งของในสัญญาจัดซื้อ (ID, CON_ID [เชื่อม supplies_con.ID], SUP_NAME [ชื่อรายการสินค้า], SUP_TOTAL [จำนวน], PRICE_PER_UNIT [ราคาต่อหน่วย], PRICE_SUM [ราคารวม])
- supplies_vendor: ทะเบียนบริษัทคู่ค้า/ผู้จัดจำหน่ายพัสดุ (VENDOR_ID, VENDOR_NAME, VENDOR_PHONE)

4. งานทรัพย์สินและครุภัณฑ์ (Assets & Articles):
- asset_article: ทะเบียนครุภัณฑ์โรงพยาบาล (รายชิ้น มีเลขครุภัณฑ์และสถานที่ตั้งชัดเจน) (
    ARTICLE_ID [รหัสครุภัณฑ์ PK],
    ARTICLE_NUM [เลขครุภัณฑ์ เช่น '7440-001-0006/11'],
    ARTICLE_NAME [ชื่อครุภัณฑ์ เช่น 'เครื่องคอมพิวเตอร์ All In One สำหรับงานประมวลผล', 'เครื่องคอมพิวเตอร์ สําหรับงานประมวลผล แบบที่ 1', 'เครื่องคอมพิวเตอร์ สำหรับงานสำนักงาน', 'เครื่องคอมพิวเตอร์โน้ตบุ๊ก สำหรับงานประมวลผล', 'เครื่องคอมพิวเตอร์แม่ข่าย แบบที่ 1'],
    DECLINE_ID [รหัสประเภทครุภัณฑ์ เชื่อม supplies_decline.DECLINE_ID: โดย 18='ครุภัณฑ์คอมพิวเตอร์', 5='ครุภัณฑ์สำนักงาน', 6='ครุภัณฑ์ยานพาหนะและขนส่ง', 17='ครุภัณฑ์วิทยาศาสตร์และการแพทย์'],
    STATUS_ID [สถานะครุภัณฑ์ เชื่อม asset_status.STATUS_ID: โดย 1='ปกติ', 2='จำหน่ายแล้ว' (แทงจำหน่าย), 3='รอจำหน่าย', 4='ถูกยืม'],
    PRICE_PER_UNIT [ราคาต่อหน่วย/มูลค่า],
    RECEIVE_DATE [วันที่ได้มา/ตรวจรับ],
    DEP_SUB_SUB_NAME [ชื่อหน่วยงาน/งานย่อยที่ครอบครองหรือสถานที่ตั้ง เช่น 'กลุ่มงานการพยาบาล', 'ศูนย์คอมพิวเตอร์', 'ห้องฉุกเฉิน', 'งานเทคนิคการแพทย์'],
    SERIAL_NO [หมายเลขซีเรียลเครื่อง]
  )
- supplies_decline: ประเภทหมวดหมู่ครุภัณฑ์ (DECLINE_ID [PK], DECLINE_NAME เช่น 18='ครุภัณฑ์คอมพิวเตอร์', 5='ครุภัณฑ์สำนักงาน', 6='ครุภัณฑ์ยานพาหนะ', 17='ครุภัณฑ์วิทยาศาสตร์และการแพทย์')
- asset_status: สถานะครุภัณฑ์ (STATUS_ID [PK], STATUS_NAME เช่น 1='ปกติ', 2='จำหน่ายแล้ว', 3='รอจำหน่าย', 4='ถูกยืม')
- asset_depreciate: ค่าเสื่อมราคาครุภัณฑ์ (DEP_ID, DEP_ASSET_ID, DEP_YEAR [ปีงบ], DEP_PRICE, DEP_VALUE [มูลค่าคงเหลือ])
- asset_dispose: ทะเบียนครุภัณฑ์ที่แทงจำหน่าย (DISPOSE_ID, ARTICLE_ID, DISPOSE_DATE, DISPOSE_REASON)

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

9. ระบบรายงานอุบัติการณ์และความเสี่ยงโรงพยาบาล (Hospital Risk Management System):
- risk_rep: รายงานอุบัติการณ์ความเสี่ยง (
    RISKREP_ID [รหัสอุบัติการณ์ PK],
    RISKREP_NO [เลขที่รายงาน เช่น 'R69-00270'],
    RISKREP_DATESAVE [วันที่บันทึกรายงาน YYYY-MM-DD เช่น '2026-08-15'],
    RISKREP_STARTDATE [วันที่เกิดอุบัติการณ์],
    RISKREP_TIME [เวลาเกิดเหตุ เช่น '10:00:00'],
    RISKREP_DETAILRISK [รายละเอียดเหตุการณ์ความเสี่ยง],
    RISKREP_BASICMANAGE [การแก้ไขปัญหาเบื้องต้น],
    RISKREP_LEVEL [รหัสระดับความรุนแรง เชื่อม risk_rep_level.RISK_REP_LEVEL_ID],
    RISK_REPPROGRAM_ID [รหัสโปรแกรมความเสี่ยงหลัก เชื่อม risk_rep_program.RISK_REPPROGRAM_ID],
    RISK_REPPROGRAMSUB_ID [รหัสโปรแกรมความเสี่ยงย่อย เชื่อม risk_rep_program_sub.RISK_REPPROGRAMSUB_ID],
    RISK_REPPROGRAMSUBSUB_ID [รหัสโปรแกรมความเสี่ยงย่อยเฉพาะ เชื่อม risk_rep_program_subsub.RISK_REPPROGRAMSUBSUB_ID],
    RISKREP_DEPARTMENT_SUB [รหัสหน่วยงานที่เกิดเหตุ/รายงาน เชื่อม risk_rep_department_sub หรือ hrd_department_sub],
    RISKREP_LOCATION_ID [รหัสสถานที่เกิดเหตุ เชื่อม risk_rep_location.RISK_LOCATION_ID],
    RISK_REPTYPERESON_ID [รหัสสาเหตุความเสี่ยง เชื่อม risk_rep_typereason.RISK_REPTYPERESON_ID],
    RISK_REPTYPERESONSYS_ID [รหัสระบบสาเหตุความเสี่ยง เชื่อม risk_rep_typereason_sys.RISK_REPTYPERESONSYS_ID],
    RISKREP_STATUS [สถานะความเสี่ยง เช่น 'REPORT', 'SUCCESS'],
    LEADER_PERSON_NAME [ชื่อหัวหน้า/ผู้รับผิดชอบ],
    RISKREP_USEREFFECT_FULLNAME [ชื่อผู้ได้รับผลกระทบ],
    BUDGET_YEAR [ปีงบประมาณ พ.ศ. เช่น 2569]
  )
- risk_rep_level: ตารางระดับความรุนแรงของอุบัติการณ์ความเสี่ยง (Lookup ระดับความรุนแรง):
  * RISK_REP_LEVEL_ID [รหัสระดับ PK ใช้เชื่อมกับ risk_rep.RISKREP_LEVEL]
  * RISK_REP_LEVEL_CODE [เช่น '00001', '00002']
  * RISK_REP_LEVEL_NAME [ชื่อระดับตัวอักษรหรือตัวเลข เช่น 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', '1', '2', '3', '4', '5']
  * RISK_REP_LEVEL_DETAIL [คำอธิบายความหมายระดับความรุนแรงภาษาไทย]:
    - A = '(เกิดที่นี่) เกิดเหตุการณ์ขึ้นจากตัวเองและค้นพบได้ด้วยตัวเอง สามารถปรับแก้ไขได้ ไม่ส่งผลกระทบถึงผู้อื่น'
    - B = '(เกิดที่ไกล) เกิดเหตุการณ์ส่งต่อไปที่ผู้อื่น แต่ตรวจพบแก้ไขได้ ยังไม่มีผลกระทบถึงผู้ป่วยหรือบุคลากร'
    - C = '(เกิดกับใคร) เกิดเหตุการณ์มีผลกระทบถึงผู้ป่วยหรือบุคลากร แต่ไม่เกิดอันตรายหรือเสียหาย'
    - D = '(ให้ระวัง) มีผลกระทบถึงผู้ป่วยหรือบุคลากร ต้องให้การดูแลเฝ้าระวังเป็นพิเศษ'
    - E = '(ต้องรักษา) เกิดอันตรายชั่วคราวที่ต้องแก้ไข/รักษาเพิ่มมากขึ้น'
    - F = '(เยียวยานาน) ต้องรักษาหรือนอนโรงพยาบาลนานขึ้น'
    - G = '(ต้องพิการ) ทำให้เกิดความพิการถาวรหรือเสียชื่อเสียง/มีเรื่องร้องเรียน'
    - H = '(ต้องการปั๊ม) มีผลทำให้ต้องทำการช่วยชีวิต CPR'
    - I = '(จำใจลา) เป็นสาเหตุทำให้เสียชีวิต'
    - 1 = 'ผลกระทบมูลค่าความเสียหาย 0-1,000 บาท'
    - 2 = 'ผลกระทบมูลค่าความเสียหาย 1,001-10,000 บาท'
- risk_rep_program: ตารางโปรแกรมความเสี่ยงหลัก (RISK_REPPROGRAM_ID [PK], RISK_REPPROGRAM_NAME เช่น '1.โปรแกรมด้านคลินิก', '2.โปรแกรมความคลาดเคลื่อนทางยา', '3.โปรแกรมการควบคุมและป้องกันการติดเชื้อในโรงพยาบาล', '4.โปรแกรมสิ่งแวดล้อม ความปลอดภัย สาธารณูปโภค', '5.โปรแกรมด้านเครื่องมือ อุปกรณ์การแพทย์')
- risk_rep_program_sub: ตารางโปรแกรมความเสี่ยงย่อย (RISK_REPPROGRAMSUB_ID [PK], RISK_REPPROGRAMSUB_NAME, RISK_REPPROGRAM_ID)
- risk_rep_department_sub: ตารางหน่วยงานความเสี่ยง (RISK_REP_DEPARTMENT_SUBID [PK], RISK_REP_DEPARTMENT_SUBNAME เช่น 'งานการพยาบาลผู้ป่วยใน', 'งานเทคนิคการแพทย์', 'งานป้องกันและควบคุมโรค', 'องค์กรแพทย์')
- risk_rep_location: ตารางสถานที่เกิดเหตุ (RISK_LOCATION_ID [PK], RISK_LOCATION_NAME เช่น 'OPD', 'IPD', 'ห้องคลอด', 'ห้องฉุกเฉิน')
- risk_rep_typereason: ตารางสาเหตุความเสี่ยง (RISK_REPTYPERESON_ID [PK], RISK_REPTYPERESON_NAME เช่น 'ผู้ป่วย', 'บุคลากร', 'เครื่องมือ')
- risk_rep_typereason_sys: ตารางระบบสาเหตุความเสี่ยง (RISK_REPTYPERESONSYS_ID [PK], RISK_REPTYPERESONSYS_NAME)
- risk_status: ตารางสถานะความเสี่ยง (RISK_STATUS_ID, RISK_STATUS_NAME, RISK_STATUS_NAME_TH เช่น 'รายงาน', 'ดำเนินการ', 'เสร็จสิ้น')

* กฎสำคัญ Backoffice & Lookup Tables:
1. การสอบถามระดับความรุนแรงความเสี่ยง (Risk Severity Level):
   - ห้ามแสดงค่า RISKREP_LEVEL เป็นตัวเลข ID ดิบๆ เด็ดขาด!
   - ต้อง LEFT JOIN risk_rep_level lvl ON lvl.RISK_REP_LEVEL_ID = r.RISKREP_LEVEL
   - ให้แสดงผลชื่อระดับและคำอธิบายภาษาไทยเสมอ เช่น:
     COALESCE(CONCAT('ระดับ ', lvl.RISK_REP_LEVEL_NAME, ' : ', lvl.RISK_REP_LEVEL_DETAIL), 'ไม่ระบุระดับความรุนแรง') AS 'ระดับความรุนแรง'
   - ตัวอย่างคำสั่งเมื่อถาม 'บันทึกความเสี่ยงแยกตามระดับความรุนแรง':
     SELECT 
       COALESCE(CONCAT('ระดับ ', lvl.RISK_REP_LEVEL_NAME, ' : ', lvl.RISK_REP_LEVEL_DETAIL), 'ไม่ระบุระดับความรุนแรง') AS 'ระดับความรุนแรง',
       COUNT(*) AS 'จำนวนอุบัติการณ์'
     FROM risk_rep r
     LEFT JOIN risk_rep_level lvl ON lvl.RISK_REP_LEVEL_ID = r.RISKREP_LEVEL
     WHERE r.RISKREP_DATESAVE BETWEEN ? AND ?
     GROUP BY r.RISKREP_LEVEL, lvl.RISK_REP_LEVEL_NAME, lvl.RISK_REP_LEVEL_DETAIL
     ORDER BY COUNT(*) DESC
2. การสอบถามโปรแกรมความเสี่ยง / เรื่องความเสี่ยง:
   - ต้อง LEFT JOIN risk_rep_program prog ON prog.RISK_REPPROGRAM_ID = r.RISK_REPPROGRAM_ID แล้วดึง prog.RISK_REPPROGRAM_NAME
3. การสอบถามหน่วยงานที่เกิดเหตุ/รายงานในระบบความเสี่ยง:
   - ให้ LEFT JOIN risk_rep_department_sub rdep ON rdep.RISK_REP_DEPARTMENT_SUBID = r.RISKREP_DEPARTMENT_SUB
     LEFT JOIN hrd_department_sub hrd ON hrd.HR_DEPARTMENT_SUB_ID = r.RISKREP_DEPARTMENT_SUB
     แล้วใช้ COALESCE(rdep.RISK_REP_DEPARTMENT_SUBNAME, hrd.HR_DEPARTMENT_SUB_NAME, 'ไม่ระบุ') AS 'หน่วยงาน'
4. การสอบถามสถานที่เกิดเหตุความเสี่ยง:
   - ต้อง LEFT JOIN risk_rep_location loc ON loc.RISK_LOCATION_ID = r.RISKREP_LOCATION_ID แล้วดึง loc.RISK_LOCATION_NAME
5. คลังพัสดุทั่วไปใช้ตารางตระกูล warehouse_* ส่วนคลังยาและเวชภัณฑ์ใช้ตารางตระกูล medicine_warehouse_*
   - เบิกพัสดุ: warehouse_request_sub ต้อง JOIN supplies s ON s.ID = sub.WAREHOUSE_REQUEST_SUB_DETAIL_ID เพื่อดึงชื่อพัสดุ s.SUP_NAME (ห้ามแสดงแค่ ID)
6. เจ้าหน้าที่ไอทีหรือสารสนเทศ สังกัดกลุ่มงานชื่อ 'กลุ่มงานสุขภาพดิจิทัล' ใน hrd_department
7. ปีงบประมาณใน Backoffice ส่วนใหญ่ใช้ พ.ศ. เช่น 2568, 2569 ในคอลัมน์ YEAR_ID, CON_YEAR_ID, BUDGET_YEAR
8. การสอบถามเครื่องคอมพิวเตอร์และครุภัณฑ์ (Assets & Equipment) ใน Backoffice:
   - ตารางหลักคือ `asset_article`
   - หากถามเกี่ยวกับเครื่องคอมพิวเตอร์ ให้กรองด้วย `(a.ARTICLE_NAME LIKE '%คอมพิวเตอร์%' OR a.DECLINE_ID = 18)`
   - สรุปภาพรวมแบบฉลาด (Smart Executive View): ให้แสดงทั้ง 'ประเภทครุภัณฑ์', 'จำนวนทั้งหมด', 'ใช้งานปกติ', 'จำหน่ายแล้ว' เสมอ เพื่อให้เห็นภาพรวมสถานะทันที:
     SELECT 
       a.ARTICLE_NAME AS 'ประเภทครุภัณฑ์',
       COUNT(a.ARTICLE_ID) AS 'จำนวนทั้งหมด',
       SUM(CASE WHEN a.STATUS_ID = 1 THEN 1 ELSE 0 END) AS 'ใช้งานปกติ',
       SUM(CASE WHEN a.STATUS_ID = 2 THEN 1 ELSE 0 END) AS 'จำหน่ายแล้ว'
     FROM asset_article a
     WHERE a.ARTICLE_NAME LIKE '%คอมพิวเตอร์%' OR a.DECLINE_ID = 18
     GROUP BY a.ARTICLE_NAME
     ORDER BY COUNT(a.ARTICLE_ID) DESC;
9. การสอบถามพัสดุและวัสดุสิ้นเปลือง (Supplies & Materials) ใน Backoffice:
   - ตารางหลักของวัสดุคือ `supplies` และเชื่อมหมวดหมู่วัสดุด้วย `supplies_type` (`s.SUP_TYPE_ID = t.SUP_TYPE_ID`)
   - หากถาม 'วัสดุมีกี่หมวด' หรือ 'แยกตามหมวดหมู่วัสดุ':
     SELECT 
       COALESCE(t.SUP_TYPE_NAME, 'ไม่ระบุหมวด') AS 'หมวดหมู่วัสดุ',
       COUNT(s.ID) AS 'จำนวนรายการพัสดุ'
     FROM supplies s
     LEFT JOIN supplies_type t ON t.SUP_TYPE_ID = s.SUP_TYPE_ID
     GROUP BY s.SUP_TYPE_ID, t.SUP_TYPE_NAME
     ORDER BY COUNT(s.ID) DESC;
10. การขอเบิกพัสดุ/ตัดจ่ายพัสดุคลัง (Warehouse Requisition & Disbursement):
    - เชื่อมต่อระหว่างใบขอเบิก `warehouse_request` กับรายการพัสดุ `warehouse_request_sub` และชื่อวัสดุ `supplies`:
      SELECT 
        w.WAREHOUSE_REQUEST_CODE AS 'เลขที่ใบเบิก',
        w.WAREHOUSE_DATE_TIME_SAVE AS 'วันที่ขอเบิก',
        w.WAREHOUSE_SAVE_HR_NAME AS 'ผู้ขอเบิก',
        w.WAREHOUSE_DEP_SUB_SUB_NAME AS 'หน่วยงานที่เบิก',
        s.SUP_NAME AS 'รายการวัสดุ',
        sub.WAREHOUSE_REQUEST_SUB_AMOUNT AS 'จำนวนขอเบิก',
        sub.WAREHOUSE_REQUEST_SUB_PRICE AS 'ราคาต่อหน่วย',
        sub.WAREHOUSE_REQUEST_SUB_SUM_PRICE AS 'ราคารวม'
      FROM warehouse_request_sub sub
      JOIN warehouse_request w ON w.WAREHOUSE_ID = sub.WAREHOUSE_REQUEST_ID
      JOIN supplies s ON s.ID = sub.WAREHOUSE_REQUEST_SUB_DETAIL_ID
      ORDER BY w.WAREHOUSE_DATE_TIME_SAVE DESC
      LIMIT 50;
";
    }
}
