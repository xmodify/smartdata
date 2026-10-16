# INVS Drug Warehouse Agent (Go Standalone)

โปรแกรมเชื่อมต่อและส่งข้อมูล 5 แฟ้มยา (INVS Drug Warehouse) สป.สธ. เข้าสู่ **SmartData Platform** และส่งออกไฟล์ Excel มาตรฐาน สป.สธ.

---

## 🌟 คุณสมบัติหลัก (Key Features)

1. **Standalone Windows Executable (`.exe`)**:
   - พัฒนาด้วยภาษา **Go (Golang)** แบบ Pure-Go (`CGO_ENABLED=0`)
   - รันได้บน Windows ทุกรุ่นโดยไม่ต้องติดตั้ง Runtime เพิ่มเติม (ไม่ต้องลง Python / .NET / Node.js)
   - ขนาดกะทัดรัด (~18 MB) ไฟล์เดียวพกพาใส่ Flash Drive ไปวางบนเครื่องไหนก็ใช้งานได้ทันที

2. **หน้าจอ GUI เสมือนโปรแกรม DMSIC ต้นฉบับ**:
   - **แท็บ 1 ส่งข้อมูลใหม่**: เลือกเดือน, ปี, คลังหลัก (`STOCK1`), ประเภทบัญชียา, ติ๊กเลือกเฉพาะแฟ้มที่ต้องการ
   - **แท็บ 2 ตั้งค่า SQL Server**: ระบุ Host, Port 1433, Database Name, Username, Password พร้อมปุ่ม **[ทดสอบการเชื่อมต่อ]**
   - **แท็บ 3 ตั้งค่า API & Schedule**: ระบุ SmartData API Endpoint, API Token, และตั้งเวลาส่งอัตโนมัติประจำวัน (Schedule Timer)
   - **แท็บ 4 ส่งไฟล์เอง**: อัปโหลดไฟล์ `.xlsx` หรือ `.zip` ที่มีอยู่แล้วส่งเข้า API ได้ทันที
   - **Telegram Alert**: แจ้งเตือนผ่าน Telegram Bot เมื่อส่งข้อมูลสำเร็จหรือเกิดข้อผิดพลาด

3. **ส่งออกไฟล์ Excel (.xlsx) ตามโครงสร้าง สป.สธ. เป๊ะ 100%**:
   - แฟ้มที่ 1: `druglist` (11 คอลัมน์)
   - แฟ้มที่ 2: `purchaseplan` (20 คอลัมน์)
   - แฟ้มที่ 3: `receipt` (22 คอลัมน์)
   - แฟ้มที่ 4: `distribution` (11 คอลัมน์)
   - แฟ้มที่ 5: `inventory` (15 คอลัมน์)

4. **โหมดสั่งการผ่าน Command Line / Background Silent Mode**:
   - รันโหมดปกติ (เปิด GUI): `invs_agent.exe`
   - รันโหมดเงียบ (สำหรับ Task Scheduler): `invs_agent.exe -s`
   - ทดสอบการเชื่อมต่อ SQL Server: `invs_agent.exe -test-db`
   - ดึงและส่งข้อมูลทันทีผ่าน CLI: `invs_agent.exe -sync-now`
   - ดึงและส่งออกไฟล์ Excel ลงโฟลเดอร์ exports: `invs_agent.exe -export-excel`

---

## 🚀 วิธีการใช้งาน

### 1. เปิดใช้งานแบบมีหน้าต่าง GUI
ดับเบิ้ลคลิกไฟล์ `invs_agent.exe` หรือ `run_agent.bat`
หน้าต่างจะเปิดขึ้นมาอัตโนมัติในโหมด Desktop Application ที่ `http://127.0.0.1:18899`

### 2. ตั้งค่าการเชื่อมต่อฐานข้อมูล INVS (MS SQL Server)
1. ไปที่แท็บ **"ตั้งค่า SQL Server"**
2. กรอก Server IP / Port (1433), Database, Username (`sa`), Password
3. กดปุ่ม **`[ทดสอบการเชื่อมต่อ SQL Server]`** เมื่อขึ้นสีเขียวว่าเชื่อมต่อสำเร็จ ให้กด **`[บันทึกการตั้งค่า SQL]`**

### 3. ตั้งค่า SmartData API
1. ไปที่แท็บ **"ตั้งค่า API & Schedule"**
2. ใส่ URL: `http://<IP-ของเครื่อง-SmartData>/api/v1/moph-f5/upload`
3. ใส่ Token: (Token ที่ระบุไว้ในไฟล์ `.env` ของ SmartData ตัวแปร `MOPH_F5_API_TOKEN`)
4. กด **`[ทดสอบการเชื่อมต่อ API]`** และ **`[บันทึกการตั้งค่า API & Schedule]`**

### 4. การตั้งค่าส่งอัตโนมัติทุกวัน (Windows Task Scheduler)
สร้าง Basic Task ใน Windows Task Scheduler:
* **Trigger**: Daily (ทุกวัน เวลา 01:00 น.)
* **Action**: Start a Program
* **Program/script**: `D:\path\to\invs_agent.exe`
* **Add arguments**: `-s`
* **Start in**: `D:\path\to\` (โฟลเดอร์ที่เก็บ `invs_agent.exe`)
