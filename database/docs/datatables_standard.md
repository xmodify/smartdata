# มาตรฐานการใช้งาน DataTables (Premium Style Guide)

เอกสารฉบับนี้ใช้สำหรับอ้างอิงรูปแบบและโครงสร้างในการสร้างตารางรายงานด้วย **DataTables** ให้มีหน้าตาและสไตล์ที่ตรงกันทั่งทั้งโครงการ (Premium UI/UX)

---

## 1. CSS Overrides (สไตล์สำหรับปรับแต่ง)

ใส่ CSS ต่อไปนี้ใน `@push('styles')` เพื่อควบคุมปุ่มนำออก Excel, ตัวกรองค้นหา, ตัวเลขหน้าตาราง (Pagination) และหัวตารางให้เป็นสีน้ำเงิน-น้ำเงินและไม่มีขอบตั้ง

```css
/* ปรับแต่ง Dropdown เลือกจำนวนแถว และช่องค้นหา */
.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #dee2e6 !important;
    border-radius: 0.5rem !important;
    padding: 0.2rem 0.6rem !important;
    outline: none !important;
    font-size: 0.8rem !important;
}

/* ปุ่ม Excel (สีเขียวพรีเมียม) */
.dt-buttons .btn-success {
    background-color: #198754 !important;
    border-color: #198754 !important;
    color: #ffffff !important;
    border-radius: 0.4rem !important;
    font-weight: 500 !important;
    padding: 0.25rem 0.6rem !important;
    font-size: 0.75rem !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.4rem !important;
    box-shadow: 0 2px 4px rgba(25, 135, 84, 0.2) !important;
}

/* Pagination ปุ่มเลขหน้าปัจจุบัน */
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #4e73df !important;
    color: white !important;
    border: 1px solid #4e73df !important;
    border-radius: 0.5rem !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f8f9fc !important;
    color: #4e73df !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 0.5rem !important;
}

/* หัวตาราง (thead) สีน้ำเงินและมีเส้นใต้หนาขึ้น ไม่มีขอบข้าง */
table.dataTable thead th {
    background-color: #f8f9fc !important;
    color: #4e73df !important;
    font-weight: 700 !important;
    border-bottom: 2px solid #e3e6f0 !important;
    font-size: 0.85rem !important;
}

.dataTables_wrapper .dataTables_filter {
    margin-bottom: 0rem;
}

.dataTables_wrapper .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 0;
    font-size: 0.85rem;
}

.dt-buttons {
    margin-bottom: 0 !important;
    display: flex !important;
    align-items: center !important;
}
```

---

## 2. โครงสร้าง HTML (ตารางและการ์ดครอบ)

ใช้คลาส `table table-hover align-middle` โดย**ไม่ต้องใส่ `table-bordered`** เพื่อป้องกันไม่ให้มีขอบแนวตั้ง และใส่ `style="width: 100%"` เพื่อรองรับการแสดงผลแบบ Responsive

```html
<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header bg-light py-3 border-0" style="border-radius: 16px 16px 0 0;">
        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-table me-2"></i>หัวข้อตารางรายงาน</h6>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="table-standard-id" style="width: 100%">
                <thead>
                    <tr>
                        <th style="width: 5%">อันดับ</th>
                        <th>รหัสโรค</th>
                        <th>ชื่อรายงาน</th>
                        <th class="text-end">จำนวน</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $row)
                        <tr>
                            <!-- การแสดง Badge มงกุฎสำหรับอันดับ 1, 2, 3 -->
                            <td class="text-center fw-bold text-muted">
                                @if ($index < 3 && $row->sum > 0)
                                    <span class="badge rounded-pill bg-warning text-dark px-2">
                                        <i class="fas fa-crown"></i> {{ $index + 1 }}
                                    </span>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td><span class="badge bg-primary bg-opacity-10 text-primary px-2">{{ $row->code }}</span></td>
                            <td>{{ $row->name }}</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($row->sum) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
```

---

## 3. JavaScript การตั้งค่า (Initialization)

กำหนดโครงสร้างการจัดวางปุ่ม Excel และเมนูกรองด้วย `dom` รวมถึงใช้ภาษาไทยสำหรับคำอธิบายตาราง

```javascript
$(document).ready(function() {
    $('#table-standard-id').DataTable({
        // จัดการ Layout: แสดงหน้า (ซ้าย), ค้นหา & ปุ่ม Excel (ขวาคู่กัน)
        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        buttons: [{
            extend: 'excelHtml5',
            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
            className: 'btn btn-success',
            title: 'ชื่อไฟล์เมื่อดาวน์โหลด',
            messageTop: 'ช่วงวันที่: จากระบบหรือตัวกรอง'
        }],
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
            paginate: {
                previous: "ก่อนหน้า",
                next: "ถัดไป"
            }
        },
        pageLength: 10,
        responsive: true
    });
});
```
