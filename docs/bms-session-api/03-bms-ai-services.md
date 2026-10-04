# 03. บริการ BMS Cloud AI Services

> **Central AI Gateway URL:** `https://ai-api.kube.bmscloud.in.th`  
> **Authentication Header:** `Authorization: Bearer {bms-session-id}` *(ใช้ raw session id โดยตรง ไม่ใช่ bms_session_code)*  
> **Technical Limits:** Ingress Timeout 300 วินาที (แนะนำตั้ง Client Timeout ที่ 290 วินาที), Request Body สูงสุด 10 MB

---

## 1. LLM Chat Completions (OpenAI Compatible)

รองรับโมเดล AI ภาษาขนาดใหญ่ชั้นนำ เช่น DeepSeek, Qwen, GLM, Kimi โดยมี Interface แบบเดียวกับ OpenAI API

### 1.1 ตรวจสอบรายชื่อโมเดลที่พร้อมใช้งาน (Dynamic Model Catalog)
```http
GET https://ai-api.kube.bmscloud.in.th/v1/models
```
*(ห้าม Hardcode ชื่อโมเดลลงในโค้ด ให้ดึงรายชื่อจาก endpoint นี้แบบ dynamic)*

### 1.2 ส่งคำขอ Chat Completion
```http
POST https://ai-api.kube.bmscloud.in.th/v1/chat/completions
Authorization: Bearer {bms-session-id}
Content-Type: application/json

{
  "model": "deepseek-chat",
  "messages": [
    {
      "role": "system",
      "content": "คุณคือผู้ช่วยแพทย์อัจฉริยะ ให้สรุปข้อมูลประวัติการรักษาและวิเคราะห์อาการสำคัญ"
    },
    {
      "role": "user",
      "content": "ผู้ป่วยชาย 55 ปี มีไข้สูง 38.5 องศา ไอแห้ง ปวดเมื่อยตัวมา 3 วัน หายใจไม่อิ่ม ควรตรวจวินิจฉัยเพิ่มเติมอย่างไร"
    }
  ],
  "temperature": 0.3,
  "max_tokens": 1000,
  "stream": false
}
```

---

## 2. Thai Speech-to-Text / ASR (พิมพ์ด้วยเสียงภาษาไทย)

แปลงไฟล์เสียงพูดภาษาไทยหรือบันทึกสดจากไมโครโฟนเป็นข้อความ เหมาะสำหรับระบบแพทย์บันทึกอาการ (Voice Clinical Notes)

### ตัวอย่างคำขอ (Multipart Form-Data)
```http
POST https://ai-api.kube.bmscloud.in.th/v1/audio/transcriptions
Authorization: Bearer {bms-session-id}
Content-Type: multipart/form-data; boundary=---Boundary123

-----Boundary123
Content-Disposition: form-data; name="file"; filename="voice_note.webm"
Content-Type: audio/webm

<Binary Audio Stream>
-----Boundary123
Content-Disposition: form-data; name="model"

Qwen/Qwen3-ASR-1.7B
-----Boundary123
Content-Disposition: form-data; name="language"

th
-----Boundary123
Content-Disposition: form-data; name="response_format"

json
-----Boundary123--
```

#### ผลลัพธ์ Response:
```json
{
  "text": "ผู้ป่วยมารับการตรวจตามนัด ความดันโลหิตปกติ บ่นมีอาการปวดข้อเข่าขวาเวลาเดิน"
}
```

---

## 3. Thai Text-to-Speech / TTS & Normalize (สังเคราะห์เสียงภาษาไทย)

แปลงข้อความเป็นเสียงสังเคราะห์ภาษาไทยที่ลื่นไหลเป็นธรรมชาติ เหมาะสำหรับระบบเรียกคิว หรืออ่านคำแนะนำการใช้ยา

### 3.1 Normalization ข้อความก่อนอ่าน (`/v1/audio/normalize`)
แปลงเลขอารบิก/ไทย คำย่อทางการแพทย์ และหน่วยเงิน เป็นคำอ่านเต็ม
```http
POST https://ai-api.kube.bmscloud.in.th/v1/audio/normalize
Authorization: Bearer {bms-session-id}
Content-Type: application/json

{
  "text": "คิวที่ A102 เชิญที่ห้องตรวจ ๑ วันนี้รับประทาน Paracetamol 500mg 2 เม็ด ทุก 4-6 ชม."
}
```

### 3.2 สังเคราะห์เสียงพูด (`/v1/audio/speech`)
```http
POST https://ai-api.kube.bmscloud.in.th/v1/audio/speech
Authorization: Bearer {bms-session-id}
Content-Type: application/json

{
  "model": "voxcpm-thai",
  "input": "คิวที่ เอ หนึ่งศูนย์สอง เชิญที่ห้องตรวจหนึ่ง ค่ะ",
  "voice": "female",
  "response_format": "mp3"
}
```
*คืนค่าเป็น **Raw Audio Stream (MP3/WAV)***

---

## 4. ICD-11 Semantic Diagnosis Lookup

ระบบค้นหารหัสโรค ICD-11 อัจฉริยะจากข้อความอาการสำคัญ (Chief Complaint) หรือบันทึกของแพทย์

### ตัวอย่างคำขอ
```http
POST https://ai-api.kube.bmscloud.in.th/v1/icd11/lookup
Authorization: Bearer {bms-session-id}
Content-Type: application/json

{
  "text": "ผู้ป่วยมีอาการไข้ เจ็บคอ ไอ ตรวจพบคอแดง ทอนซิลโต มีหนอง",
  "top_k": 5
}
```

#### ผลลัพธ์ Response:
```json
{
  "results": [
    {
      "code": "CA02.0",
      "title": "Acute streptococcal tonsillitis",
      "score": 0.942,
      "chapter": "01"
    },
    {
      "code": "CA02.Z",
      "title": "Acute tonsillitis, unspecified",
      "score": 0.887,
      "chapter": "01"
    }
  ]
}
```

---

## 5. Medical Document OCR

สกัดข้อความและโครงสร้างข้อมูลจากไฟล์ภาพหรือ PDF เช่น ใบรับรองแพทย์ ผลตรวจทางห้องปฏิบัติการ (Lab Slip)

```http
POST https://ai-api.kube.bmscloud.in.th/v1/ocr/extract
Authorization: Bearer {bms-session-id}
Content-Type: multipart/form-data

file: [LabResult.pdf]
type: "medical_lab"
```
