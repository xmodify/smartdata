# มาตรฐานตัวเลือกวันที่และปีงบประมาณ (Date & Budget Year Picker Standard)

เอกสารนี้ระบุมาตรฐานโครงสร้างการทำงานและดีไซน์ของตัวเลือกช่วงวันที่และปีงบประมาณในระบบ SmartData ซึ่งรองรับ:
1. การเลือกปีงบประมาณไทยแล้วเปลี่ยนช่วงวันที่ในปฏิทินอัตโนมัติ (1 ต.ค. ปีก่อนหน้า ถึง 30 ก.ย. ปีปีงบประมาณ)
2. การเลือกวันที่แยกกันอย่างอิสระ (Custom Date Range)
3. การแสดงผลปี พ.ศ. (Thai Buddhist Era - BE) ในปฏิทินอย่างถูกต้อง

---

## 1. โครงสร้าง HTML (Blade Template View)

วางตัวเลือกวันที่ในแถบ Header หรือมุมขวาบนของหน้ารายงาน โดยใช้ CSS/Bootstrap ร่วมกับไอคอน FontAwesome

```html
<form action="" method="GET" class="m-0 header-form-controls">
    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
    
    <!-- วันที่เริ่มต้น -->
    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden; width: 160px;">
        <span class="input-group-text bg-white border-end-0 text-primary">
            <i class="fas fa-calendar-alt"></i>
        </span>
        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
    </div>
    
    <!-- วันที่สิ้นสุด -->
    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden; width: 160px;">
        <span class="input-group-text bg-white border-end-0 text-primary">
            <i class="fas fa-calendar-alt"></i>
        </span>
        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0"
            value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
    </div>
    
    <!-- เลือกปีงบประมาณ -->
    <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius: 8px; overflow: hidden; width: 250px;">
        <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
            @foreach ($budget_year_select as $row)
                <option value="{{ $row->LEAVE_YEAR_ID }}"
                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                    {{ $row->LEAVE_YEAR_NAME }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary px-3" style="font-size: 0.8rem;">
            <i class="fas fa-search"></i> ค้นหา
        </button>
    </div>
</form>
```

---

## 2. โครงสร้าง JavaScript (Flatpickr กับปี พ.ศ. ไทย)

การตั้งค่า Flatpickr ให้แสดงผลเป็น ปี พ.ศ. (บวกปี ค.ศ. เพิ่ม 543) และซิงก์ความสัมพันธ์เมื่อผู้ใช้เปลี่ยนปีงบประมาณ

```javascript
$(document).ready(function() {
    if (typeof flatpickr !== 'undefined') {
        const yearOffset = 543; // แปลงปี ค.ศ. เป็น พ.ศ.
        const commonConfig = {
            locale: "th",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j M Y",
            allowInput: false,
            onReady: function(selectedDates, dateStr, instance) {
                // แปลงปี ค.ศ. ในช่องอินพุตแสดงผล (altInput) ให้เป็น ปี พ.ศ. เสมอ
                if (instance.altInput) {
                    const date = instance.selectedDates[0] || new Date(instance.input.value);
                    if (date && !isNaN(date.getTime())) {
                        const day = date.getDate();
                        const month = instance.l10n.months.shorthand[date.getMonth()];
                        const year = date.getFullYear() + yearOffset;
                        instance.altInput.value = `${day} ${month} ${year}`;
                    }
                }
                
                // เพิ่มปุ่ม "วันนี้" ด้านล่างปฏิทิน
                const container = instance.calendarContainer;
                if (container && !container.querySelector('.flatpickr-today-button')) {
                    const btn = document.createElement("div");
                    btn.className = "flatpickr-today-button";
                    btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                    btn.addEventListener("mousedown", function(e) {
                        e.preventDefault();
                        instance.setDate(new Date());
                        instance.close();
                    });
                    container.appendChild(btn);
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (instance.altInput && selectedDates.length > 0) {
                    const date = selectedDates[0];
                    setTimeout(() => {
                        const day = date.getDate();
                        const month = instance.l10n.months.shorthand[date.getMonth()];
                        const year = date.getFullYear() + yearOffset;
                        instance.altInput.value = `${day} ${month} ${year}`;
                    }, 10);
                }
            }
        };

        // เริ่มต้นใช้งาน Flatpickr
        const startPicker = flatpickr("#start_date", commonConfig);
        const endPicker = flatpickr("#end_date", commonConfig);

        // เมื่อเปลี่ยนปีงบประมาณ ให้คำนวณและอัปเดตวันที่เริ่มต้น-สิ้นสุดในปฏิทินอัตโนมัติ
        $('select[name="budget_year"]').on('change', function() {
            var selectedYear = parseInt($(this).val());
            if (!isNaN(selectedYear)) {
                var startYear = selectedYear - 544; // ต.ค. ปีก่อนหน้า (ค.ศ.)
                var endYear = selectedYear - 543;   // ก.ย. ปีปัจจุบัน (ค.ศ.)
                var startDateStr = startYear + "-10-01";
                var endDateStr = endYear + "-09-30";
                
                setTimeout(() => {
                    if (startPicker) startPicker.setDate(startDateStr, true);
                    if (endPicker) endPicker.setDate(endDateStr, true);
                }, 50);
            }
        });
    }
});
```

---

## 3. โค้ดฝั่ง Controller (resolveDateRange ใน PHP)

เขียนฟังก์ชันนี้ไว้ใน Controller เพื่อรองรับค่าที่ส่งมาจากฟอร์ม (รองรับทั้งการคลิกค้นหาจากช่วงวันที่อิสระ หรือเปลี่ยนตามปีงบประมาณ)

```php
private function resolveDateRange(Request $request)
{
    // ดึงรายการปีงบประมาณย้อนหลัง 7 ปี
    $budget_year_select = DB::table('budget_year')
        ->select('LEAVE_YEAR_ID', 'LEAVE_YEAR_NAME')
        ->orderByDesc('LEAVE_YEAR_ID')
        ->limit(7)
        ->get();

    // ค้นหาปีงบประมาณปัจจุบัน
    $budget_year_now = DB::table('budget_year')
        ->whereDate('DATE_END', '>=', date('Y-m-d'))
        ->whereDate('DATE_BEGIN', '<=', date('Y-m-d'))
        ->value('LEAVE_YEAR_ID');

    $budget_year = $request->budget_year ?: $budget_year_now;

    // ตรวจสอบการเลือกวันที่อิสระ
    if ($request->start_date && $request->end_date && !$request->has('budget_year_changed')) {
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        // ซิงก์ค่าปีงบประมาณให้สัมพันธ์กับ start_date ที่เลือก
        $matched_year = DB::table('budget_year')
            ->where('DATE_BEGIN', '<=', $start_date)
            ->where('DATE_END', '>=', $start_date)
            ->value('LEAVE_YEAR_ID');

        if ($matched_year) {
            $budget_year = $matched_year;
        }
    } else {
        // อิงตามปีงบประมาณที่เลือกเพื่อหาวันที่เริ่ม-สิ้นสุด
        $year_data = DB::table('budget_year')
            ->where('LEAVE_YEAR_ID', $budget_year)
            ->first();

        if ($year_data) {
            $start_date = $year_data->DATE_BEGIN;
            $end_date = $year_data->DATE_END;
        } else {
            // สำรองหากไม่มีข้อมูลในตาราง budget_year
            $start_date = ($budget_year - 543) . '-10-01';
            $end_date = ($budget_year - 542) . '-09-30';
        }
    }

    return [
        'start_date' => $start_date,
        'end_date' => $end_date,
        'budget_year' => $budget_year,
        'budget_year_select' => $budget_year_select
    ];
}
```
