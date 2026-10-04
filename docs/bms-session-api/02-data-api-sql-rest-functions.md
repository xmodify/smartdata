# 02. API ข้อมูล HOSxP (SQL, REST, Functions)

> Data API ทั้งหมดจะเรียกผ่าน URL อุโมงค์ของโรงพยาบาล `{bms_url}` ที่ได้จากการ Resolve Session  
> **Header พื้นฐาน:** `Authorization: Bearer {bms_session_code}` และ `Content-Type: application/json`

---

## 1. การส่งคำสั่ง SQL ผ่าน `/api/sql`

`/api/sql` ถูกออกแบบให้เป็น **Read-Only API** สำหรับดึงข้อมูลสรุปหรือ Query ซับซ้อน (อนุญาตเฉพาะคำสั่ง `SELECT`, `DESCRIBE`, `EXPLAIN`, `SHOW`, `WITH`)

### ตัวอย่างคำขอ (POST Request พร้อม Parameter Binding)
```http
POST {bms_url}/api/sql
Authorization: Bearer {bms_session_code}
Content-Type: application/json

{
  "sql": "SELECT p.hn, p.pname, p.fname, p.lname, o.vstdate, o.vsttime FROM ovst o LEFT JOIN patient p ON p.hn = o.hn WHERE o.vstdate = :vstdate AND o.main_dep = :dep ORDER BY o.vsttime DESC LIMIT 100",
  "params": {
    "vstdate": "2026-10-04",
    "dep": "01"
  },
  "limit": 100
}
```

### โครงสร้าง Response ที่ส่งกลับ
```json
{
  "result": [
    {
      "hn": "0001234",
      "pname": "นาย",
      "fname": "สมชาย",
      "lname": "ใจดี",
      "vstdate": "2026-10-04",
      "vsttime": "08:30:15"
    }
  ],
  "MessageCode": 200,
  "Message": "OK"
}
```

> [!TIP]
> **ข้อแนะนำ:** ใช้ Parameter Binding (`:param_name`) เสมอ เพื่อป้องกัน SQL Injection และหลีกเลี่ยงการใช้คำสั่งเขียน (`INSERT`, `UPDATE`, `DELETE`) บน `/api/sql` เนื่องจาก Gateway จะปฏิเสธคำขอทันที

---

## 2. การจัดการข้อมูลผ่าน `/api/rest`

`/api/rest` ใช้สำหรับการเข้าถึงและจัดการข้อมูลระดับตาราง (Table CRUD)

### 2.1 การอ่านข้อมูล (GET)
```http
GET {bms_url}/api/rest/patient?filter=hn:eq:0001234&expand=ovst
Authorization: Bearer {bms_session_code}
```

#### ตัวดำเนินการ Filter (Filter Operators) ทั้ง 9 แบบ:
1. `:eq:` เท่ากับ เช่น `hn:eq:0001234`
2. `:ne:` ไม่เท่ากับ เช่น `sex:ne:1`
3. `:gt:` มากกว่า เช่น `age_y:gt:60`
4. `:gte:` มากกว่าหรือเท่ากับ เช่น `vstdate:gte:2026-01-01`
5. `:lt:` น้อยกว่า เช่น `bps:lt:90`
6. `:lte:` น้อยกว่าหรือเท่ากับ เช่น `bpd:lte:60`
7. `:like:` ค้นหาคำ เช่น `fname:like:%สมชาย%`
8. `:in:` อยู่ในกลุ่ม เช่น `clinic:in:001,002,003`
9. `:is:null` ค่าว่าง เช่น `death_date:is:null`

---

### 2.2 การเขียนข้อมูล (POST / PUT / DELETE)
การเขียนข้อมูลต้องแนบ **Marketplace Token (`mkt_...`)** ใน Header เสมอ

```http
POST {bms_url}/api/rest/opdscreen
Authorization: Bearer {bms_session_code}
X-Marketplace-Token: mkt_8f9a2b...
Content-Type: application/json

{
  "opdscreen_id": 1234567,
  "vn": "671004083012",
  "hn": "0001234",
  "vstdate": "2026-10-04",
  "bps": 120,
  "bpd": 80,
  "pulse": 72,
  "temperature": 36.6,
  "cc": "มีไข้ ไอ เจ็บคอ 2 วัน"
}
```

---

## 3. Server Functions ผ่าน `/api/function`

เนื่องจากตารางใน **HOSxP ไม่มี `AUTO_INCREMENT`** การ Insert ข้อมูลแถวใหม่ลงตารางจำเป็นต้องขอ Primary Key ล่วงหน้าผ่าน `/api/function?name=get_serialnumber`

### 3.1 ฟังก์ชัน `get_serialnumber` (สร้าง Primary Key)
```http
POST {bms_url}/api/function?name=get_serialnumber
Authorization: Bearer {bms_session_code}
Content-Type: application/json

{
  "serial_name": "opdscreen_id",
  "table_name": "opdscreen",
  "field_name": "opdscreen_id"
}
```

#### ผลลัพธ์ Response:
```json
{
  "MessageCode": 200,
  "Message": "OK",
  "Value": 1234567
}
```

### 3.2 Standard Pattern การบันทึกข้อมูล HOSxP ที่ถูกต้อง
```
[ขั้นตอนที่ 1] ขอ Primary Key (get_serialnumber)
       │
       ▼
[ขั้นตอนที่ 2] ทำการ POST /api/rest/{table} ทันที โดยใช้ Value ที่ได้เป็น PK
```

> [!WARNING]
> **ข้อควรระวัง:** ห้ามขอ Serial Number เก็บไว้ล่วงหน้านานๆ ก่อน Insert เพราะอาจเกิดปัญหาเลขชนกับเครื่องอื่น (PK Collision) ให้ทำรูปแบบ **"ขอแล้ว Insert ทันที"** เสมอ

---

### 3.3 ฟังก์ชัน `get_hosvariable` (อ่านค่าตั้งค่าของระบบ)
```http
POST {bms_url}/api/function?name=get_hosvariable
Authorization: Bearer {bms_session_code}
Content-Type: application/json

{
  "name": "hospitalname"
}
```

#### ผลลัพธ์ Response:
```json
{
  "MessageCode": 200,
  "Message": "OK",
  "Value": "โรงพยาบาลสมาร์ทดาต้า"
}
```
