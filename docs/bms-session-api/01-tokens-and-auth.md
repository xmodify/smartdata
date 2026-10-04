# 01. สถาปัตยกรรมและการยืนยันตัวตน (Tokens & Authentication Model)

> **หัวใจสำคัญ:** BMS Session Ecosystem มีความเฉพาะตัวตรงที่ใช้ Token ทั้งหมด 4 ชนิดสำหรับแต่ละหน้าที่และแต่ละ Endpoint อย่างชัดเจน

---

## 🔑 Token ทั้ง 4 ชนิดในระบบ

```mermaid
classDiagram
    class BMS_Session_ID {
        +String UUID/Code
        +รับจาก URL: ?bms-session-id=...
        +ใช้ Resolve หา bms_url
        +ใช้เป็น Bearer สำหรับ BMS AI Gateway
        +อายุตาม expired_second
    }
    class BMS_Session_Code {
        +Opaque String
        +ได้จากผล Resolve PasteJSON
        +ใช้เป็น Bearer สำหรับ {bms_url}/api/*
        +ห้ามแก้หรือ parse โครงสร้าง
    }
    class HXP_API_Key {
        +String hxp_...
        +ได้จากการลงทะเบียน Marketplace
        +ใช้ขอ mkt_ token ชั่วคราว
        +ใช้เป็น X-Addon-Key
    }
    class MKT_Access_Token {
        +String mkt_...
        +อายุสั้น ~15 นาที (900s)
        +ปลดล็อคสิทธิ์เขียน REST (READWRITE)
        +ปลดล็อค Data Masking
    }
```

| Token / Credential | Prefix / รูปแบบ | ได้รับจากที่ไหน | นำไปใช้ที่ไหน | สิทธิ์ / วัตถุประสงค์ |
| :--- | :--- | :--- | :--- | :--- |
| **1. `bms-session-id`** | String ตัวอักษร | HOSxP ส่งมาทาง URL Parameter เมื่อผู้ใช้เปิด Addon | • `hosxp.net/phps/bms_session_paste.php`<br>• `ai-api.kube.bmscloud.in.th` | • ใช้ Resolve หา Tunnel URL ของ รพ.<br>• ใช้เรียก AI Gateway โดยตรง |
| **2. `bms_session_code`** | Opaque String | ได้จาก JSON ตอน Resolve `bms-session-id` | • `{bms_url}/api/sql`<br>• `{bms_url}/api/rest`<br>• `{bms_url}/api/function` | • ยืนยันตัวตนกับ Tunnel Gateway ของ รพ.<br>• ส่งใน Header `Authorization: Bearer ...` |
| **3. `hxp_...`** | `hxp_` ตามด้วยตัวอักษร | HOSxP Marketplace Portal (ประจำตัว Addon แต่ละตัว) | • `hosxp-marketplace.bmscloud.in.th` | • ยืนยันสิทธิ์ Addon Developer<br>• ใช้ขอ `mkt_` token และรายงาน Billing |
| **4. `mkt_...`** | `mkt_` ตามด้วยตัวอักษร | ขอจาก Marketplace API (`POST /api/v1/addon/token`) | • `{bms_url}/api/rest` (Header `X-Marketplace-Token`) | • **ปลดล็อคสิทธิ์เขียนข้อมูล (Write)**<br>• ปลด Mask ข้อมูลอ่อนไหว (Sensitive Data) |

---

## 🔄 ขั้นตอนการ Resolve Session (PasteJSON)

เมื่อผู้ใช้เปิด Addon ขึ้นมาผ่าน Browser หรือ WebView ใน HOSxP จะได้ URL เช่น:
```http
https://smartdata.hospital.in.th/?bms-session-id=550e8400-e29b-41d4-a716-446655440000&hn=0001234&vn=670101123456
```

### 1. เรียก Resolve Session จาก `hosxp.net`
```http
GET https://hosxp.net/phps/bms_session_paste.php?bms-session-id=550e8400-e29b-41d4-a716-446655440000
```
*(หรือส่งเป็น POST Body `{ "bms-session-id": "550e8400-..." }`)*

### 2. โครงสร้าง Response ที่ได้กลับมา
```json
{
  "result": {
    "bms_url": "https://DEV99-admin-manoi-m7730.tunnel.hosxp.net",
    "bms_session_code": "eyAidHlwIjogIkpXVCIsICJhbGciOiAiSFMyNTYiIH0.ey...",
    "expired_second": 36000,
    "user_id": "doctor_somchai",
    "user_name": "นพ. สมชาย ใจดี",
    "hospital_code": "10670",
    "hospital_name": "โรงพยาบาลตัวอย่าง",
    "department_id": "01",
    "department_name": "ห้องตรวจอายุรกรรม"
  },
  "MessageCode": 200,
  "Message": "OK"
}
```

> [!IMPORTANT]
> **ข้อควรระวังเรื่องอายุ Session (`expired_second`):**  
> ห้าม Hardcode อายุของ Session ในโปรแกรม ให้ดึงค่าจากฟิลด์ `expired_second` ในผลลัพธ์ PasteJSON ของ Session ปัจจุบันเสมอ (ในโหมด Dev มักจะอยู่ที่ 36,000 วินาที ส่วน Production มักอยู่ที่ 2,592,000 วินาที)

---

## 🛡️ โมเดลความปลอดภัย & การปกปิดข้อมูล (Data Masking)

### 1. Development Mode (ไม่มี Marketplace Token)
- API อนุญาตเฉพาะการ **อ่านข้อมูล (Read-Only)** ผ่าน `/api/sql` และ `GET /api/rest`
- ระบบจะทำการ **Data Masking (ปกปิดข้อมูลส่วนบุคคล)** อัตโนมัติ เช่น:
  - ชื่อผู้ป่วย: `สมชาย -> ส***ย`
  - เลขบัตรประชาชน: `1234567890123 -> 123456******3`
  - เบอร์โทรศัพท์: `0812345678 -> 081***5678`

### 2. Production Mode (มี Marketplace Token `mkt_...`)
- สามารถทำการ **เขียนข้อมูล (POST, PUT, DELETE)** ผ่าน `/api/rest` ได้ตามสิทธิ์ที่ Addon ได้รับการอนุมัติ
- ข้อมูลผู้ป่วยจะถูก Unmask (แสดงข้อมูลจริง) สำหรับบุคลากรทางการแพทย์ที่ได้รับอนุญาต

---

## 🧪 เครื่องมือทดสอบสำหรับนักพัฒนา (BMS Dev Portal)

หากยังไม่มีเครื่องเซิร์ฟเวอร์ HOSxP ของโรงพยาบาลจริง สามารถดาวน์โหลดโปรแกรมจำลอง:
- **Download:** [BMSDevPortalHOSxPTest-Setup.zip](https://cloud3.hosxp.net/bms_app/BMSDevPortalHOSxPTest-Setup.zip)
- รันระบบ HOSxP mORMot API พร้อมฐานข้อมูล MariaDB ฝังตัว (Embedded libmysqld) บนเครื่อง localhost ทันที
- ให้ Tunnel URL และ `bms-session-id` สำหรับการพัฒนาบนเครื่อง Local ได้เสมือนต่อกับโรงพยาบาลจริง
