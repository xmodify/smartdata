# MOPH F5 Drug Warehouse API Receiver Specification
### คู่มือมาตรฐานการพัฒนา API ตัวรับข้อมูล 5 แฟ้มยา (INVS Drug Warehouse) จาก INVS Agent
**เวอร์ชัน:** 1.0.0  
**รูปแบบการเชื่อมต่อ:** Single POST Endpoint (ใช้ URL เดียวจบ รองรับทั้ง Ping ตรวจสอบสถานะ และรับข้อมูล 5 แฟ้ม)  
**ไฟล์ JSON ตัวอย่าง:** [sample_moph_f5_month_payload.json](file:///d:/Project%20Laravel/smartdata/docs/sample_moph_f5_month_payload.json)

---

## 1. ภาพรวมการทำงาน (Architecture Overview)

โปรแกรม **INVS Agent** (โปรแกรมส่งข้อมูล 5 แฟ้มยา สป.สธ.) จะทำการดึงข้อมูลยาจากระบบคลังยาโรงพยาบาล (INVS MS SQL Server) และยิงข้อมูลส่งออกมายัง **Server ปลายทางผ่าน HTTP POST ด้วย JSON Payload ไปยัง Endpoint เดียว**

```
+--------------------------+                         +-----------------------------------+
|     INVS Agent (Go)      |                         |      API Receiver (ตัวรับ)        |
|                          |   POST (Single URL)     |  (PHP / Node.js / Python / etc.)  |
| 1. ปุ่ม [ทดสอบการเชื่อมต่อ]  | --------------------->  |                                   |
|    - ส่ง Ping Body       |                         |  - ตรวจสอบ API Token              |
|                          |                         |  - ถ้าเป็น Ping -> ตอบกลับ 200 OK |
| 2. ปุ่ม [ส่งข้อมูล] / ตั้งเวลา | --------------------->  |  - ถ้ามี dataset 5 แฟ้ม            |
|    - ส่ง JSON 5 แฟ้ม     |                         |    -> ทำ Upsert ลงฐานข้อมูล       |
|                          |   <------------------   |    -> ตอบกลับ 200 ผลการบันทึก     |
+--------------------------+    JSON Response        +-----------------------------------+
```

---

## 2. ข้อมูลทางเทคนิคของ Endpoint (Endpoint Specification)

* **HTTP Method:** `POST`
* **URL:** ใช้ URL เดียวกันทั้งหมด เช่น `https://your-domain.com/api/moph-f5/receiver`
* **HTTP Headers ที่ Agent ส่งมา:**
  ```http
  Content-Type: application/json
  Accept: application/json
  User-Agent: SmartData-INVS-Agent/1.0.0 (Windows; Go)
  Authorization: Bearer YOUR_API_TOKEN
  X-DMSIC-Token: YOUR_API_TOKEN
  ```

> [!NOTE]
> ระบบตัวรับสามารถตรวจสอบ Token ได้จาก Header `Authorization` (ตัดคำว่า `Bearer ` ออก) หรือจาก Header `X-DMSIC-Token`

---

## 3. การประมวลผล 2 เงื่อนไขบน URL เดียว (Dual Payload Logic)

เมื่อมี Request แบบ `POST` เข้ามาที่ URL ปลายทาง ให้แบ่ง Logic การทำงานเป็น 3 ลำดับ ดังนี้:

### ลำดับที่ 1: ตรวจสอบความถูกต้องของ Token (Authentication)
* ตรวจสอบว่า Token ที่ส่งมาตรงกับค่า Secret Key ที่ตกลงกันไว้หรือไม่
* หากไม่ตรง หรือไม่มี Token แนบมา:
  * **HTTP Status:** `401 Unauthorized`
  * **Response:**
    ```json
    {
      "success": false,
      "message": "Unauthorized: Invalid or missing API Token",
      "errors": ["AUTH_FAILED"]
    }
    ```

---

### ลำดับที่ 2: กรณี "ทดสอบการเชื่อมต่อ" (Ping / Connection Test)
เมื่อผู้ใช้กดปุ่ม **[ทดสอบการเชื่อมต่อ API]** บนหน้าจอ Agent ตัว Agent จะยิง POST มาด้วย Body:
```json
{
  "test_connection": true,
  "ping": true
}
```

* **สิ่งที่ตัวรับต้องตอบกลับ:**
  * **HTTP Status:** `200 OK`
  * **Response:**
    ```json
    {
      "success": true,
      "message": "Connection test successful",
      "status": "online"
    }
    ```

---

### ลำดับที่ 3: กรณี "ส่งข้อมูล 5 แฟ้มยา" (Data Synchronization)
เมื่อมีการส่งข้อมูลจริง Agent จะยิง POST มาพร้อมกับ Body ที่มีโครงสร้างดังนี้:

```json
{
  "hosp_code": "10989",
  "period": "202610",
  "period_rpt": "202610",
  "dataset": {
    "druglist": [ ... ],
    "purchaseplan": [ ... ],
    "receipt": [ ... ],
    "distribution": [ ... ],
    "inventory": [ ... ]
  }
}
```

* **สิ่งที่ตัวรับต้องทำ:**
  1. ดึงค่า `hosp_code` และ `period_rpt` (รูปแบบปี ค.ศ. + เดือน เช่น `202610`)
  2. วนลูปอ่านข้อมูลในคีย์ `dataset['druglist']`, `dataset['purchaseplan']`, `dataset['receipt']`, `dataset['distribution']`, `dataset['inventory']`
  3. ทำการบันทึกลงฐานข้อมูลแบบ **Upsert (Update if exists, Insert if new)** โดยใช้ Unique Key ของแต่ละแฟ้ม
* **สิ่งที่ตัวรับต้องตอบกลับ:**
  * **HTTP Status:** `200 OK`
  * **Response:**
    ```json
    {
      "success": true,
      "message": "บันทึกข้อมูล 5 แฟ้มยาสำเร็จ",
      "counts": {
        "druglist": 5,
        "purchaseplan": 2,
        "receipt": 3,
        "distribution": 4,
        "inventory": 5
      },
      "errors": []
    }
    ```

---

## 4. โครงสร้างคอลัมน์ของ 5 แฟ้มยา (Data Dictionary & Schema)

คีย์ใน JSON ของแต่ละรายการจะเป็น **ตัวพิมพ์ใหญ่ (UPPERCASE)** ตามมาตรฐานของกระทรวงสาธารณสุข (สป.สธ.):

### 4.1 แฟ้ม `druglist` (รายการบัญชียาโรงพยาบาล - 11 คอลัมน์)
* **Unique Key (ห้ามซ้ำ):** `HOSP_CODE` + `WORKING_CODE`

| คอลัมน์ | Data Type | คำอธิบาย | ตัวอย่าง |
| :--- | :--- | :--- | :--- |
| `HOSP_CODE` | String(20) | รหัสสถานพยาบาล 5 หรือ 9 หลัก | `"10989"` |
| `WORKING_CODE` | String(50) | รหัสยาของโรงพยาบาล (Primary Code) | `"1000001"` |
| `GENERIC_NAME` | String(255) | ชื่อสามัญทางยา + ความแรง + รูปแบบ | `"Paracetamol 500 mg tablet"` |
| `GPUID` | String(50) | รหัส GPU ตามมาตรฐาน สปสช. | `"1000101"` |
| `NLEM` | String(10) | บัญชียาหลักแห่งชาติ (E = ในบัญชี, N = นอกบัญชี) | `"E"` |
| `PRODUCT_CAT` | String(10) | หมวดรายการยา (1=ยา, 2=เวชภัณฑ์, ฯลฯ) | `"1"` |
| `BASE_UNIT` | String(50) | หน่วยนับพื้นฐาน (เช่น TAB, CAP, VIAL, BTL) | `"TAB"` |
| `STATUS` | String(10) | สถานะของยา (1 = ใช้งาน, 0 = ยกเลิก/ซ่อน) | `"1"` |
| `DATE_STATUS` | String(20) | วันที่ปรับปรุงสถานะ (YYYYMMDD) | `"20261001"` |
| `PERIOD_RPT` | String(20) | รอบเดือนที่รายงาน (YYYYMM) | `"202610"` |
| `DATE_SEND` | String(30) | วันที่เวลาที่ส่งข้อมูล (YYYYMMDDHHmmss) | `"20261017112500"` |

---

### 4.2 แฟ้ม `purchaseplan` (แผนปฏิบัติการจัดซื้อยา - 20 คอลัมน์)
* **Unique Key (ห้ามซ้ำ):** `HOSP_CODE` + `YEARBUDGET` + `WORKING_CODE` + `PERIOD_RPT`

| คอลัมน์ | Data Type | คำอธิบาย | ตัวอย่าง |
| :--- | :--- | :--- | :--- |
| `HOSP_CODE` | String(20) | รหัสสถานพยาบาล | `"10989"` |
| `YEARBUDGET` | String(10) | ปีงบประมาณ (ค.ศ. เช่น 2026) | `"2026"` |
| `WORKING_CODE` | String(50) | รหัสยาของโรงพยาบาล | `"1000001"` |
| `GENERIC_NAME` | String(255) | ชื่อสามัญทางยา | `"Paracetamol 500 mg tablet"` |
| `GPUID` | String(50) | รหัส GPU | `"1000101"` |
| `NLEM` | String(10) | ยาใน/นอกบัญชียาหลัก | `"E"` |
| `QTY_USE_YEAR3` | Number(Float) | ปริมาณการใช้ย้อนหลัง 3 ปี | `450000.0` |
| `QTY_USE_YEAR2` | Number(Float) | ปริมาณการใช้ย้อนหลัง 2 ปี | `480000.0` |
| `QTY_USE_YEAR1` | Number(Float) | ปริมาณการใช้ย้อนหลัง 1 ปี | `500000.0` |
| `QTY_THIS_YEAR` | Number(Float) | ประมาณการใช้ในปีนี้ | `520000.0` |
| `PACK_SIZE` | Number(Int) | ขนาดบรรจุต่อแพ็ก | `1000` |
| `BASE_UNIT` | String(50) | หน่วยนับ | `"TAB"` |
| `PACK_COST` | Number(Float) | ราคาซื้อต่อแพ็ก | `250.0` |
| `VALUE_THIS_YEAR` | Number(Float) | มูลค่าจัดซื้อรวมทั้งปี | `130000.0` |
| `QTY_PLAN_TRIMES1` | Number(Float) | แผนจัดซื้อไตรมาส 1 (จำนวนแพ็ก) | `130.0` |
| `QTY_PLAN_TRIMES2` | Number(Float) | แผนจัดซื้อไตรมาส 2 (จำนวนแพ็ก) | `130.0` |
| `QTY_PLAN_TRIMES3` | Number(Float) | แผนจัดซื้อไตรมาส 3 (จำนวนแพ็ก) | `130.0` |
| `QTY_PLAN_TRIMES4` | Number(Float) | แผนจัดซื้อไตรมาส 4 (จำนวนแพ็ก) | `130.0` |
| `PERIOD_RPT` | String(20) | รอบเดือนที่รายงาน (YYYYMM) | `"202610"` |
| `DATE_SEND` | String(30) | วันที่เวลาที่ส่งข้อมูล | `"20261017112500"` |

---

### 4.3 แฟ้ม `receipt` (การตรวจรับยาเข้าคลัง - 22 คอลัมน์)
* **Unique Key (ห้ามซ้ำ):** `HOSP_CODE` + `RCV_NO` + `WORKING_CODE` + `LOT_NO` + `EXPIRE_DATE` + `RCV_FLAG`

| คอลัมน์ | Data Type | คำอธิบาย | ตัวอย่าง |
| :--- | :--- | :--- | :--- |
| `HOSP_CODE` | String(20) | รหัสสถานพยาบาล | `"10989"` |
| `WORKING_CODE` | String(50) | รหัสยาของโรงพยาบาล | `"1000001"` |
| `TRADE_NAME` | String(255) | ชื่อการค้า | `"Sara 500 mg tablet"` |
| `TPUID` | String(50) | รหัส TPU | `"2000101"` |
| `VENDOR_NAME` | String(255) | ชื่อบริษัทผู้จำหน่าย | `"บริษัท ไทยนครพัฒนา จำกัด"` |
| `VENDOR_TAX_ID` | String(50) | เลขประจำตัวผู้เสียภาษี 13 หลัก | `"0105521001234"` |
| `QTY_RCV` | Number(Float) | จำนวนแพ็กที่รับเข้า | `100.0` |
| `PACK_SIZE` | Number(Int) | ขนาดบรรจุต่อแพ็ก | `1000` |
| `BASE_UNIT` | String(50) | หน่วยนับ | `"TAB"` |
| `PACK_COST` | Number(Float) | ราคาซื้อต่อแพ็ก | `250.0` |
| `TOTAL_VALUE` | Number(Float) | มูลค่ารวม | `25000.0` |
| `LOT_NO` | String(100) | หมายเลขรุ่นที่ผลิต (Lot/Batch) | `"LOT-PC2610A"` |
| `EXPIRE_DATE` | String(20) | วันหมดอายุ (YYYYMMDD) | `"20281031"` |
| `RCV_NO` | String(100) | เลขที่ใบรับสินค้า/ใบส่งของ | `"RCV-69-10-0012"` |
| `PO_NO` | String(100) | เลขที่ใบสั่งซื้อ (PO) | `"PO-69-10-0005"` |
| `CNT_NO` | String(100) | เลขที่สัญญาจัดซื้อ (ถ้ามี) | `"CNT-69-001"` |
| `DATE_RCV` | String(20) | วันที่ตรวจรับเข้าคลัง (YYYYMMDD) | `"20261005"` |
| `BUY_METHOD_ID` | String(10) | วิธีการจัดซื้อ (1=เฉพาะเจาะจง, 2=คัดเลือก, ฯลฯ) | `"1"` |
| `CO_PURCHASE_ID`| String(10) | การจัดซื้อร่วม (1=ซื้อร่วม, 0=ซื้อเดี่ยว) | `"1"` |
| `RCV_FLAG` | String(10) | สถานะการรับ (1=รับปกติ, 2=รับคืน, ฯลฯ) | `"1"` |
| `PERIOD_RPT` | String(20) | รอบเดือนที่รายงาน | `"202610"` |
| `DATE_SEND` | String(30) | วันที่เวลาที่ส่งข้อมูล | `"20261017112500"` |

---

### 4.4 แฟ้ม `distribution` (การเบิกจ่ายยาออกจากคลัง - 11 คอลัมน์)
* **Unique Key (ห้ามซ้ำ):** `HOSP_CODE` + `WORKING_CODE` + `TRADE_NAME` + `PACK_SIZE` + `DIS_DEPT_GROUP` + `PERIOD_RPT`

| คอลัมน์ | Data Type | คำอธิบาย | ตัวอย่าง |
| :--- | :--- | :--- | :--- |
| `HOSP_CODE` | String(20) | รหัสสถานพยาบาล | `"10989"` |
| `WORKING_CODE` | String(50) | รหัสยาของโรงพยาบาล | `"1000001"` |
| `TRADE_NAME` | String(255) | ชื่อการค้า | `"Sara 500 mg tablet"` |
| `TPUID` | String(50) | รหัส TPU | `"2000101"` |
| `QTY_DIS` | Number(Float) | จำนวนแพ็กที่จ่ายออก | `25.0` |
| `PACK_SIZE` | Number(Int) | ขนาดบรรจุ | `1000` |
| `BASE_UNIT` | String(50) | หน่วยนับ | `"TAB"` |
| `VALUE` | Number(Float) | มูลค่าการจ่าย | `6250.0` |
| `DIS_DEPT_GROUP` | String(10) | กลุ่มหน่วยงานที่เบิก (OPD, IPD, ER, WARD) | `"OPD"` |
| `PERIOD_RPT` | String(20) | รอบเดือนที่รายงาน | `"202610"` |
| `DATE_SEND` | String(30) | วันที่เวลาที่ส่งข้อมูล | `"20261017112500"` |

---

### 4.5 แฟ้ม `inventory` (รายงานยอดคงคลังยา - 15 คอลัมน์)
* **Unique Key (ห้ามซ้ำ):** `HOSP_CODE` + `WORKING_CODE` + `LOT_NO` + `EXPIRE_DATE` + `DATE_ONHAND`

| คอลัมน์ | Data Type | คำอธิบาย | ตัวอย่าง |
| :--- | :--- | :--- | :--- |
| `HOSP_CODE` | String(20) | รหัสสถานพยาบาล | `"10989"` |
| `WORKING_CODE` | String(50) | รหัสยาของโรงพยาบาล | `"1000001"` |
| `TRADE_NAME` | String(255) | ชื่อการค้า | `"Sara 500 mg tablet"` |
| `TPUID` | String(50) | รหัส TPU | `"2000101"` |
| `VENDOR_NAME` | String(255) | ชื่อบริษัทผู้จำหน่าย | `"บริษัท ไทยนครพัฒนา จำกัด"` |
| `VENDOR_TAX_ID` | String(50) | เลขประจำตัวผู้เสียภาษี | `"0105521001234"` |
| `QTY_ONHAND` | Number(Float) | จำนวนแพ็กคงเหลือ ณ วันที่สำรวจ | `120.0` |
| `PACK_SIZE` | Number(Int) | ขนาดบรรจุ | `1000` |
| `BASE_UNIT` | String(50) | หน่วยนับ | `"TAB"` |
| `PACK_COST` | Number(Float) | ราคาซื้อต่อแพ็ก | `250.0` |
| `VALUE_ONHAND` | Number(Float) | มูลค่าคงเหลือรวม | `30000.0` |
| `LOT_NO` | String(100) | หมายเลขรุ่นที่ผลิต | `"LOT-PC2610A"` |
| `EXPIRE_DATE` | String(20) | วันหมดอายุ (YYYYMMDD) | `"20281031"` |
| `DATE_ONHAND` | String(20) | วันที่ยอดคงเหลือ (YYYYMMDD) | `"20261016"` |
| `DATE_SEND` | String(30) | วันที่เวลาที่ส่งข้อมูล | `"20261017112500"` |

---

## 5. ตัวอย่าง Source Code สำหรับตัวรับ (Single POST Endpoint)

ผู้พัฒนาสามารถนำตัวอย่างโค้ดไปปรับใช้ตามภาษาที่ใช้งานได้ทันที:

### ตัวอย่างที่ 1: PHP (Native / CodeIgniter / Any Framework)
```php
<?php
header('Content-Type: application/json; charset=utf-8');

$EXPECTED_TOKEN = "YOUR_SECRET_API_TOKEN"; // กำหนด Secret Token ให้ตรงกับ Agent

// 1. ตรวจสอบ Token จาก Header
$authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
if (empty($authHeader) && isset($_SERVER['HTTP_X_DMSIC_TOKEN'])) {
    $authHeader = $_SERVER['HTTP_X_DMSIC_TOKEN'];
}
$token = trim(str_replace(['Bearer ', 'bearer '], '', $authHeader));

if ($token !== $EXPECTED_TOKEN) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Invalid or missing API Token',
        'errors'  => ['AUTH_FAILED']
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. อ่าน JSON Body
$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);

if (empty($payload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Empty payload or invalid JSON'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 3. กรณีทดสอบการเชื่อมต่อ (Ping Test จากปุ่มใน Agent)
if (!empty($payload['test_connection']) || !empty($payload['ping'])) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Connection test successful',
        'status'  => 'online'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. กรณีรับข้อมูล 5 แฟ้มจริง
$hospCode  = isset($payload['hosp_code']) ? $payload['hosp_code'] : '';
$periodRpt = isset($payload['period_rpt']) ? $payload['period_rpt'] : '';
$dataset   = isset($payload['dataset']) ? $payload['dataset'] : [];

$counts = [
    'druglist'     => 0,
    'purchaseplan' => 0,
    'receipt'      => 0,
    'distribution' => 0,
    'inventory'    => 0,
];

// ประมวลผลบันทึกข้อมูล (Upsert)
foreach (['druglist', 'purchaseplan', 'receipt', 'distribution', 'inventory'] as $fileType) {
    if (!empty($dataset[$fileType]) && is_array($dataset[$fileType])) {
        foreach ($dataset[$fileType] as $row) {
            // ดำเนินการ Upsert ลงตารางตาม $fileType
            // เช่น $db->upsert($row, $uniqueKeys);
        }
        $counts[$fileType] = count($dataset[$fileType]);
    }
}

// 5. ตอบกลับผลการประมวลผลสำเร็จ
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'บันทึกข้อมูล 5 แฟ้มยาสำเร็จ',
    'hosp_code' => $hospCode,
    'period_rpt' => $periodRpt,
    'counts'  => $counts,
    'errors'  => []
], JSON_UNESCAPED_UNICODE);
```

---

### ตัวอย่างที่ 2: Node.js (Express.js)
```javascript
const express = require('express');
const app = express();
app.use(express.json({ limit: '100mb' }));

const EXPECTED_TOKEN = 'YOUR_SECRET_API_TOKEN';

app.post('/api/moph-f5/receiver', (req, res) => {
    // 1. Auth Check
    const authHeader = req.headers['authorization'] || req.headers['x-dmsic-token'] || '';
    const token = authHeader.replace(/^Bearer\s+/i, '').trim();

    if (token !== EXPECTED_TOKEN) {
        return res.status(401).json({
            success: false,
            message: 'Unauthorized: Invalid API Token',
            errors: ['AUTH_FAILED']
        });
    }

    // 2. Ping / Connection Test
    if (req.body.test_connection || req.body.ping) {
        return res.status(200).json({
            success: true,
            message: 'Connection test successful',
            status: 'online'
        });
    }

    // 3. Process 5 Datasets
    const { hosp_code, period_rpt, dataset = {} } = req.body;
    const counts = {};

    for (const fileType of ['druglist', 'purchaseplan', 'receipt', 'distribution', 'inventory']) {
        const rows = dataset[fileType] || [];
        // ดำเนินการ Upsert ลง Database ...
        counts[fileType] = rows.length;
    }

    return res.status(200).json({
        success: true,
        message: 'บันทึกข้อมูล 5 แฟ้มยาสำเร็จ',
        hosp_code,
        period_rpt,
        counts,
        errors: []
    });
});

app.listen(3000, () => console.log('Receiver API running on port 3000'));
```

---

## 6. คำสั่ง cURL สำหรับทดสอบ Endpoint

### 6.1 ทดสอบคำสั่ง Ping (จำลองการกดปุ่ม [ทดสอบการเชื่อมต่อ API] ใน Agent)
```bash
curl -X POST https://your-domain.com/api/moph-f5/receiver \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_SECRET_API_TOKEN" \
  -d '{"test_connection":true,"ping":true}'
```
**ผลลัพธ์ที่ถูกต้อง:**
```json
{"success":true,"message":"Connection test successful","status":"online"}
```

---

### 6.2 ทดสอบส่งข้อมูล 5 แฟ้มจริง โดยใช้ไฟล์ `sample_moph_f5_month_payload.json`
```bash
curl -X POST https://your-domain.com/api/moph-f5/receiver \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_SECRET_API_TOKEN" \
  --data-binary "@sample_moph_f5_month_payload.json"
```
**ผลลัพธ์ที่ถูกต้อง:**
```json
{
  "success": true,
  "message": "บันทึกข้อมูล 5 แฟ้มยาสำเร็จ",
  "counts": {
    "druglist": 5,
    "purchaseplan": 2,
    "receipt": 3,
    "distribution": 4,
    "inventory": 5
  },
  "errors": []
}
```

---

## 7. สรุปไฟล์ที่ส่งมอบให้ผู้พัฒนา

1. **เอกสารสเปกนี้:** [MOPH_F5_API_RECEIVER_SPEC.md](file:///d:/Project%20Laravel/smartdata/docs/MOPH_F5_API_RECEIVER_SPEC.md)
2. **ไฟล์ข้อมูลตัวอย่าง 1 เดือน (5 แฟ้ม):** [sample_moph_f5_month_payload.json](file:///d:/Project%20Laravel/smartdata/docs/sample_moph_f5_month_payload.json)
