# 04. Marketplace & Cloud User Storage (KV Store)

> **Marketplace Base URL:** `https://hosxp-marketplace.bmscloud.in.th`  
> บริการสำหรับจัดการ Addon, ออก Token สิทธิ์การทำงาน, บันทึกการใช้งาน (Billing) และ Cloud Storage ส่วนตัวของผู้ใช้

---

## 1. HOSxP Marketplace API

เมื่อนักพัฒนาลงทะเบียน Addon บน HOSxP Marketplace จะได้รับ **Addon API Key (`hxp_...`)**

### 1.1 การขอ Access Token ชั่วคราว (`mkt_...`)
Token นี้มีอายุประมาณ **15 นาที (900 วินาที)** ใช้สำหรับยืนยันสิทธิ์ในการเขียนข้อมูล HOSxP ผ่าน `/api/rest`

```http
POST https://hosxp-marketplace.bmscloud.in.th/api/v1/addon/token
X-Addon-Key: hxp_1a2b3c4d5e6f...
Content-Type: application/json

{
  "bms-session-id": "550e8400-e29b-41d4-a716-446655440000"
}
```

#### ผลลัพธ์ Response:
```json
{
  "token": "mkt_9z8y7x6w5v4u...",
  "expires_in": 900,
  "scope": ["READ", "READWRITE"],
  "hospital_code": "10670"
}
```

---

### 1.2 การรายงานการใช้งาน & ตัดเครดิต (`/api/v1/addon/usage`)
สำหรับ Addon ที่คิดค่าบริการตามปริมาณการใช้งาน (Usage-Based Billing)

```http
POST https://hosxp-marketplace.bmscloud.in.th/api/v1/addon/usage
X-Addon-Key: hxp_1a2b3c4d5e6f...
Content-Type: application/json

{
  "bms-session-id": "550e8400-...",
  "event_type": "ai_summary_generated",
  "units": 1,
  "timestamp": "2026-10-04T12:00:00Z"
}
```

---

## 2. Cloud Key-Value User Data Storage

บริการ Cloud KV Store ฟรีสำหรับ Addon ใช้เก็บการตั้งค่า (Preferences), ประวัติการใช้งาน (Chat History), หรือร่างเอกสาร (Drafts)

### 2.1 Isolation Model (การแยกข้อมูล)
ข้อมูลจะถูกแยกอิสระ **3 มิติ (3-Layer Namespace):**
1. แยกตาม **Addon** (ระบุด้วย `hxp_` token)
2. แยกตาม **โรงพยาบาล** (ระบุด้วย Hospital Code จาก Session)
3. แยกตาม **ผู้ใช้งาน** (ระบุด้วย User ID จาก `X-BMS-Session-Id`)

---

### 2.2 กฎการส่ง Header & Token ใน KV Storage (จุดที่ผิดบ่อย)

| Method | การทำงาน | ตำแหน่งส่ง Token | ตำแหน่งส่ง Session ID |
| :--- | :--- | :--- | :--- |
| **PUT** | บันทึกข้อมูล | ส่งใน **Request Body** (`marketplace_token`) | Header `X-BMS-Session-Id` |
| **GET** | อ่านข้อมูล | Header `X-Marketplace-Token` | Header `X-BMS-Session-Id` |
| **DELETE** | ลบข้อมูล | Header `X-Marketplace-Token` | Header `X-BMS-Session-Id` |

---

### 2.3 ตัวอย่างการบันทึกค่า (PUT)
```http
PUT https://hosxp-marketplace.bmscloud.in.th/api/v1/addon/storage/user_settings
X-BMS-Session-Id: 550e8400-e29b-41d4-a716-446655440000
Content-Type: application/json

{
  "marketplace_token": "hxp_1a2b3c4d5e...",
  "value": {
    "theme": "dark",
    "default_ward": "01",
    "auto_refresh_seconds": 30
  },
  "expectedVersion": 1
}
```

### 2.4 ตัวอย่างการอ่านค่า (GET)
```http
GET https://hosxp-marketplace.bmscloud.in.th/api/v1/addon/storage/user_settings
X-BMS-Session-Id: 550e8400-e29b-41d4-a716-446655440000
X-Marketplace-Token: hxp_1a2b3c4d5e...
```

#### ผลลัพธ์ Response:
```json
{
  "key": "user_settings",
  "value": {
    "theme": "dark",
    "default_ward": "01",
    "auto_refresh_seconds": 30
  },
  "version": 2,
  "updated_at": "2026-10-04T12:30:00Z"
}
```

> [!TIP]
> **Optimistic Locking (`expectedVersion`):**  
> สามารถส่ง `expectedVersion` ไปตอนบันทึก เพื่อป้องกันการบันทึกข้อมูลทับกัน (Race Condition) กรณีผู้ใช้เปิด Addon หลายแท็บพร้อมกัน หาก Version ไม่ตรง เซิร์ฟเวอร์จะคืน `409 Conflict`
