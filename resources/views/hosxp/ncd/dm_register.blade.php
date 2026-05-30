@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.ncd.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }
        .report-title-box h5 { font-size: 1.1rem; letter-spacing: -0.01em; }
        .header-form-controls { display: flex; align-items: center; gap: 0.5rem; }
        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-budget { width: 100% !important; }
        }

        .card-custom { border-radius: 16px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }

        .table thead th {
            background-color: #f8fafc;
            color: #1e3a8a;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .table td { font-size: 0.82rem; vertical-align: middle; }

        /* DataTable layout styling to match user mockup */
        .dataTables_length {
            font-size: 0.85rem;
            color: #475569;
        }
        .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.25rem 1.5rem 0.25rem 0.5rem;
            margin: 0 0.25rem;
            outline: none;
            font-size: 0.85rem;
            background-color: #fff;
        }
        .dataTables_filter {
            font-size: 0.85rem;
            color: #475569;
        }
        .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.25rem 0.75rem;
            margin-left: 0.5rem;
            outline: none;
            font-size: 0.85rem;
            width: 180px;
            transition: border-color 0.2s;
        }
        .dataTables_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15);
        }
        .dt-buttons .btn-success {
            background-color: #1b5e20 !important;
            border-color: #1b5e20 !important;
            color: #fff !important;
            font-size: 0.82rem;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .dt-buttons .btn-success:hover {
            background-color: #154c18 !important;
            border-color: #154c18 !important;
        }

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6; padding: 8px; text-align: center; cursor: pointer;
            color: #e53935; font-weight: bold; font-size: 0.9rem; transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }
        .flatpickr-today-button:hover { background: #fff5f5; color: #b71c1c; }

        .badge-status {
            font-size: 0.72rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .text-dm-red { color: #e53935; }
    </style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-3">
    {{-- Header --}}
    <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center report-title-box">
            <div class="ps-3 py-1">
                <h5 class="text-dark mb-0 fw-bold">
                    <i class="fas fa-syringe text-dm-red me-2"></i> {{ $title }}
                </h5>
                <div class="text-muted small mt-1">ปีงบประมาณ {{ $budget_year }}</div>
                <div class="text-dm-red small fw-bold mt-1">
                    <i class="fas fa-calendar-alt me-1"></i>
                    ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="m-0 header-form-controls">
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius:8px;overflow:hidden;">
                    <span class="input-group-text bg-white border-end-0 text-dm-red"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                        value="{{ $start_date }}" style="font-size:0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius:8px;overflow:hidden;">
                    <span class="input-group-text bg-white border-end-0 text-dm-red"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0"
                        value="{{ $end_date }}" style="font-size:0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius:8px;overflow:hidden;">
                    <select class="form-select border-end-0" name="budget_year" style="font-size:0.8rem;">
                        @foreach ($budget_year_select as $row)
                            <option value="{{ $row->LEAVE_YEAR_ID }}"
                                {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                {{ $row->LEAVE_YEAR_NAME }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="budget_year_changed" id="budget_year_changed" value="0">
                    <button type="submit" class="btn btn-danger text-white px-3" style="font-size:0.8rem;">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Summary Cards --}}
    @php
        $totalAll = collect($status_summary)->sum('total');
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:14px; border-left: 4px solid #e53935 !important;">
                <div class="text-muted small mb-1">ผู้ป่วยทั้งหมด</div>
                <div class="fw-bold fs-4 text-dm-red">{{ number_format($totalAll) }}</div>
                <div class="text-muted" style="font-size:0.7rem;">ราย</div>
            </div>
        </div>
        @foreach($status_summary as $s)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:14px;">
                <div class="text-muted small mb-1" style="font-size:0.75rem;">{{ $s->clinic_member_status_name ?? 'ไม่ระบุสถานะ' }}</div>
                <div class="fw-bold fs-5">{{ number_format($s->total) }}</div>
                <div class="text-muted" style="font-size:0.7rem;">ราย</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts Row --}}
    <div class="row g-4 mb-4">
        {{-- Left: Donut Chart by Status --}}
        <div class="col-md-5">
            <div class="card card-custom h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0 text-dm-red">
                        <i class="fas fa-chart-pie me-2"></i>สัดส่วนผู้ป่วยแยกตามสถานะ
                    </h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="statusDonutChart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>

        {{-- Right: Bar Chart new by month --}}
        <div class="col-md-7">
            <div class="card card-custom h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0 text-danger">
                        <i class="fas fa-chart-bar me-2"></i>จำนวนผู้ป่วยรายใหม่ แยกรายเดือน
                    </h6>
                    <div class="text-muted small">วันที่ลงทะเบียน (regdate) ในช่วงที่เลือก</div>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="newByMonthChart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Patient Table --}}
    <div class="card card-custom mb-4">
        <div class="card-header bg-transparent border-0 pt-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold mb-0 text-dm-red">
                    <i class="fas fa-table me-2"></i>รายชื่อผู้ป่วยคลินิกเบาหวาน
                    <span class="badge bg-danger ms-2" id="countBadge">{{ number_format(count($patients)) }} ราย</span>
                </h6>
            </div>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="patientTable" style="width:100%;">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>HN</th>
                            <th>CID</th>
                            <th>ชื่อ-สกุล</th>
                            <th>เพศ</th>
                            <th>สิทธิ์</th>
                            <th>วันที่ลงทะเบียน</th>
                            <th>เยี่ยมล่าสุด</th>
                            <th>FBS (วัน/ค่า)</th>
                            <th>HbA1c (วัน/ค่า)</th>
                            <th>UA (วัน/ค่า)</th>
                            <th>BP ล่าสุด</th>
                            <th>แพทย์</th>
                            <th>สถานะสมาชิก</th>
                            <th>รพ.สต.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $i => $row)
                        <tr>
                            <td class="text-center text-muted small">{{ $i + 1 }}</td>
                            <td class="fw-bold small">{{ $row->hn }}</td>
                            <td class="small text-muted">{{ $row->cid }}</td>
                            <td class="small">{{ $row->patient_name }}</td>
                            <td class="small text-center">{{ $row->sex_name }}</td>
                            <td class="small">{{ $row->pttype_name }}</td>
                            <td class="small text-center">{{ $row->regdate }}</td>
                            <td class="small text-center">{{ $row->lastvisit }}</td>
                            <td class="small text-center">
                                @if($row->last_fbs_date)
                                    <span class="d-block text-muted" style="font-size:0.7rem;">{{ $row->last_fbs_date }}</span>
                                    <span class="fw-bold {{ $row->last_fbs_value > 126 ? 'text-danger' : 'text-success' }}">
                                        {{ $row->last_fbs_value }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small text-center">
                                @if($row->last_hba1c_date)
                                    <span class="d-block text-muted" style="font-size:0.7rem;">{{ $row->last_hba1c_date }}</span>
                                    <span class="fw-bold {{ $row->last_hba1c_value > 7 ? 'text-danger' : 'text-success' }}">
                                        {{ $row->last_hba1c_value }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small text-center">
                                @if($row->last_ua_date)
                                    <span class="d-block text-muted" style="font-size:0.7rem;">{{ $row->last_ua_date }}</span>
                                    <span>{{ $row->last_ua_value }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small text-center">{{ $row->last_bp_value ?? '-' }}</td>
                            <td class="small">{{ $row->doctor_name }}</td>
                            <td class="text-center">
                                @php
                                    $statusId = $row->clinic_member_status_id ?? '';
                                    $statusName = $row->clinic_member_status_name ?? 'ไม่ระบุ';
                                    $badgeColor = match((string)$statusId) {
                                        '1' => 'success',
                                        '2' => 'warning',
                                        '3' => 'secondary',
                                        default => 'light'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeColor }} badge-status">{{ $statusName }}</span>
                            </td>
                            <td class="small">{{ $row->send_pcu_hospital_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

    <script>
    // ข้อมูลจาก PHP (render ก่อน script เริ่มทำงาน)
    var statusLabels = @json(array_map(fn($v) => $v->clinic_member_status_name ?? 'ไม่ระบุ', $status_summary));
    var statusValues = @json(array_map(fn($v) => (int)$v->total, $status_summary));
    var monthLabels  = @json(array_column($new_by_month, 'month_name'));
    var monthValues  = @json(array_map(fn($v) => (int)$v->total, $new_by_month));

    function initCharts() {
        // ===== Donut Chart: Status =====
        if (document.querySelector('#statusDonutChart') && typeof ApexCharts !== 'undefined') {
            var donutChart = new ApexCharts(document.querySelector('#statusDonutChart'), {
                chart: { type: 'donut', height: 320, toolbar: { show: false } },
                series: statusValues,
                labels: statusLabels,
                colors: ['#e53935','#fb8c00','#43a047','#1e88e5','#8e24aa','#00897b'],
                legend: { position: 'bottom', fontSize: '13px' },
                dataLabels: { enabled: true, style: { fontSize: '12px' } },
                plotOptions: {
                    pie: { donut: { size: '60%', labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'ทั้งหมด',
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce(function(a,b){return a+b;}, 0).toLocaleString();
                            }
                        }
                    }}}
                },
                tooltip: { y: { formatter: function(val) { return val.toLocaleString() + ' ราย'; } } }
            });
            donutChart.render();
        }

        // ===== Bar Chart: New by Month =====
        if (document.querySelector('#newByMonthChart') && typeof ApexCharts !== 'undefined') {
            var noData = monthValues.length === 0
                ? { text: 'ไม่มีผู้ป่วยลงทะเบียนใหม่ในช่วงนี้', align: 'center', style: { fontSize: '14px', color: '#999' } }
                : {};
            var barChart = new ApexCharts(document.querySelector('#newByMonthChart'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'ผู้ป่วยรายใหม่', data: monthValues.length ? monthValues : [0] }],
                xaxis: { categories: monthLabels.length ? monthLabels : ['ไม่มีข้อมูล'] },
                colors: ['#e53935'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
                dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#333'] } },
                yaxis: { labels: { formatter: function(val) { return Math.floor(val).toLocaleString(); } } },
                tooltip: { y: { formatter: function(val) { return val.toLocaleString() + ' ราย'; } } },
                noData: noData
            });
            barChart.render();
        }
    }

    // รอให้ ApexCharts โหลดเสร็จก่อน
    if (typeof ApexCharts !== 'undefined') {
        initCharts();
    } else {
        document.querySelector('script[src*="apexcharts"]').addEventListener('load', initCharts);
    }

    // ===== DataTable =====
    var dtInstance;
    var STATUS_COL = 13; // column index ของ "สถานะสมาชิก"

    (function initDataTable() {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
            setTimeout(initDataTable, 100);
            return;
        }

        dtInstance = $('#patientTable').DataTable({
            pageLength: 10,
            dom: '<"d-flex justify-content-between align-items-center mb-3"l<"d-flex align-items-center gap-2"fB>>rtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success btn-sm shadow-sm px-3',
                    title: 'ทะเบียนผู้ป่วยคลินิกเบาหวาน',
                    exportOptions: { search: 'applied', order: 'applied' }
                }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json',
                search: "ค้นหา:",
                lengthMenu: "แสดง _MENU_ รายการ",
                searchPlaceholder: "กรอกข้อมูลเพื่อค้นหา..."
            },
            order: [[6, 'asc']],
            scrollX: true,
            initComplete: function() {
                updateCountBadge(this.api());
            }
        });

        // อัปเดต badge จำนวนที่แสดง
        function updateCountBadge(api) {
            var count = api.rows({ search: 'applied' }).count();
            $('#countBadge').text(count.toLocaleString() + ' ราย');
        }

        // อัปเดต badge เมื่อค้นหา
        dtInstance.on('search.dt draw.dt', function() {
            updateCountBadge(dtInstance);
        });
    })();

    // ===== Flatpickr (ภาษาไทย พุทธศักราช) =====
    var yearOffset = 543;
    var startPicker, endPicker;

    function setThaiAltValue(instance, date) {
        if (instance.altInput && date) {
            var thaiMonth = instance.l10n.months.shorthand[date.getMonth()];
            var buddhistYear = date.getFullYear() + yearOffset;
            instance.altInput.value = date.getDate() + ' ' + thaiMonth + ' ' + buddhistYear;
        }
    }

    var fpConfig = {
        locale: 'th',
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'j F Y',
        onReady: function(selectedDates, dateStr, instance) {
            // ปุ่มวันนี้
            var container = instance.calendarContainer;
            if (container && !container.querySelector('.flatpickr-today-button')) {
                var btn = document.createElement('div');
                btn.className = 'flatpickr-today-button';
                btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                btn.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    instance.setDate(new Date(), true);
                    instance.close();
                });
                container.appendChild(btn);
            }
            // แสดงปีพุทธศักราชทันทีที่ init
            if (selectedDates.length > 0) {
                setThaiAltValue(instance, selectedDates[0]);
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                setTimeout(function() {
                    setThaiAltValue(instance, selectedDates[0]);
                }, 10);
            }
        }
    };

    startPicker = flatpickr('#start_date', fpConfig);
    endPicker   = flatpickr('#end_date',   fpConfig);

    // แสดงปีพุทธศักราชสำหรับค่าเริ่มต้น (onReady อาจ fire ก่อน l10n โหลด)
    setTimeout(function() {
        if (startPicker.selectedDates.length) setThaiAltValue(startPicker, startPicker.selectedDates[0]);
        if (endPicker.selectedDates.length)   setThaiAltValue(endPicker,   endPicker.selectedDates[0]);
    }, 50);

    // ===== เลือกปีงบ → อัปเดตปฏิทิน =====
    var budgetSelect = document.querySelector('select[name="budget_year"]');
    if (budgetSelect) {
        budgetSelect.addEventListener('change', function() {
            var v = parseInt(this.value);
            if (!isNaN(v)) {
                var sDate = (v - 544) + '-10-01';  // เช่น ปีงบ 2569 → 2025-10-01
                var eDate = (v - 543) + '-09-30';  // เช่น ปีงบ 2569 → 2026-09-30
                startPicker.setDate(sDate, true);
                endPicker.setDate(eDate, true);
                document.getElementById('budget_year_changed').value = '1';
                // อัปเดต display ภาษาไทย
                setTimeout(function() {
                    if (startPicker.selectedDates.length) setThaiAltValue(startPicker, startPicker.selectedDates[0]);
                    if (endPicker.selectedDates.length)   setThaiAltValue(endPicker,   endPicker.selectedDates[0]);
                }, 20);
            }
        });
    }
    </script>
@endpush
@endsection

