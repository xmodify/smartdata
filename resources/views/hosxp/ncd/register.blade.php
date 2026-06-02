@extends('layouts.app')

@php
    $themeColor = match($config['theme'] ?? 'red') {
        'orange' => '#f57c00',
        'teal' => '#00796b',
        'indigo' => '#3949ab',
        'cyan' => '#0097a7',
        'green' => '#388e3c',
        default => '#e53935' // red
    };
    
    $btnClass = match($config['theme'] ?? 'red') {
        'orange' => 'btn-warning text-white',
        'teal' => 'btn-success',
        'indigo' => 'btn-primary',
        'cyan' => 'btn-info text-white',
        'green' => 'btn-success',
        default => 'btn-danger' // red
    };
    
    $textClass = match($config['theme'] ?? 'red') {
        'orange' => 'text-orange',
        'teal' => 'text-teal',
        'indigo' => 'text-indigo',
        'cyan' => 'text-cyan',
        'green' => 'text-green2',
        default => 'text-dm-red'
    };
@endphp

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.ncd.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: {{ $themeColor }}; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
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

        /* Premium DataTables Overrides matching other dashboards but with Dynamic accent */
        table.dataTable thead th {
            background-color: #f8fafc !important;
            color: {{ $themeColor }} !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #e2e8f0 !important;
            font-size: 0.82rem !important;
            padding: 12px 10px !important;
            white-space: nowrap;
        }
        
        table.dataTable tbody td {
            font-size: 0.82rem;
            vertical-align: middle;
            padding: 8px 10px !important;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            outline: none !important;
            font-size: 0.8rem !important;
        }

        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
            font-size: 0.85rem;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: {{ $themeColor }} !important;
            color: white !important;
            border: 1px solid {{ $themeColor }} !important;
            border-radius: 0.5rem !important;
            padding: 0.3em 0.8em !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #fff5f5 !important;
            color: {{ $themeColor }} !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
        }

        .dt-buttons {
            margin-bottom: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
        }

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

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6; padding: 8px; text-align: center; cursor: pointer;
            color: {{ $themeColor }}; font-weight: bold; font-size: 0.9rem; transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }
        .flatpickr-today-button:hover { background: #fff5f5; color: {{ $themeColor }}; }

        .badge-status {
            font-size: 0.72rem;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        .text-dm-red { color: {{ $themeColor }}; }

        /* ซ่อนแถบกำลังโหลด (Processing) ของ DataTable ทั้งหมดแบบถาวร */
        div.dataTables_processing {
            display: none !important;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-3">
    {{-- Header --}}
    <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center report-title-box">
            <div class="ps-3 py-1">
                <h5 class="text-dark mb-0 fw-bold">
                    <i class="{{ $config['icon'] ?? 'fas fa-syringe' }} {{ $textClass }} me-2"></i> {{ $title }}
                </h5>
                <div class="text-muted small mt-1">ปีงบประมาณ {{ $budget_year }}</div>
                <div class="{{ $textClass }} small fw-bold mt-1">
                    <i class="fas fa-calendar-alt me-1"></i>
                    ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="m-0 header-form-controls">
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius:8px;overflow:hidden;">
                    <span class="input-group-text bg-white border-end-0 {{ $textClass }}"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                        value="{{ $start_date }}" style="font-size:0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius:8px;overflow:hidden;">
                    <span class="input-group-text bg-white border-end-0 {{ $textClass }}"><i class="fas fa-calendar-alt"></i></span>
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
                    <button type="submit" class="btn {{ $btnClass }} px-3" style="font-size:0.8rem;">
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
            <div class="card border-0 shadow-sm text-center py-3" style="border-radius:14px; border-left: 4px solid {{ $themeColor }} !important;">
                <div class="text-muted small mb-1">ผู้ป่วยทั้งหมด</div>
                <div class="fw-bold fs-4 {{ $textClass }}">{{ number_format($totalAll) }}</div>
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
                    <h6 class="fw-bold mb-0 {{ $textClass }}">
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
                    <h6 class="fw-bold mb-0 {{ $textClass }}">
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
                <h6 class="fw-bold mb-0 {{ $textClass }}">
                    <i class="fas fa-table me-2"></i>รายชื่อผู้ป่วย
                    <span class="badge bg-danger ms-2" id="countBadge" style="display: none;"></span>
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
                        {{-- Populated via serverSide AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
window.themeColor = "{{ $themeColor }}";
// ข้อมูลจาก PHP สำหรับชาร์ต (ผ่าน window global variable โหลดก่อน push scripts block)
window.statusLabels = @json(array_values(array_map(function($v) { return $v->clinic_member_status_name ?? 'ไม่ระบุ'; }, $status_summary)));
window.statusValues = @json(array_values(array_map(function($v) { return (int)$v->total; }, $status_summary)));
window.monthLabels  = @json(array_column($new_by_month, 'month_name'));
window.monthValues  = @json(array_column($new_by_month, 'total'));
</script>

@push('scripts')
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
    <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>

    <script>
    var statusLabels = window.statusLabels || [];
    var statusValues = window.statusValues || [];
    var monthLabels  = window.monthLabels  || [];
    var monthValues  = window.monthValues  || [];
    var themeColor   = window.themeColor   || '#e53935';

    function initCharts() {
        // Donut Chart: Status
        var statusChartEl = document.querySelector('#statusDonutChart');
        if (statusChartEl) {
            statusChartEl.innerHTML = '';
            var donutChart = new ApexCharts(statusChartEl, {
                chart: { type: 'donut', height: 320, toolbar: { show: false } },
                series: statusValues,
                labels: statusLabels,
                colors: [themeColor, '#fb8c00','#43a047','#1e88e5','#8e24aa','#00897b'],
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

        // Bar Chart: New by Month
        var monthChartEl = document.querySelector('#newByMonthChart');
        if (monthChartEl) {
            monthChartEl.innerHTML = '';
            var noData = monthValues.length === 0
                ? { text: 'ไม่มีผู้ป่วยลงทะเบียนใหม่ในช่วงนี้', align: 'center', style: { fontSize: '14px', color: '#999' } }
                : {};
            var barChart = new ApexCharts(monthChartEl, {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'ผู้ป่วยรายใหม่', data: monthValues.length ? monthValues : [0] }],
                xaxis: { categories: monthLabels.length ? monthLabels : ['ไม่มีข้อมูล'] },
                colors: [themeColor],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
                dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#333'] } },
                yaxis: { labels: { formatter: function(val) { return Math.floor(val).toLocaleString(); } } },
                tooltip: { y: { formatter: function(val) { return val.toLocaleString() + ' ราย'; } } },
                noData: noData
            });
            barChart.render();
        }
    }

    function waitForCharts() {
        if (typeof ApexCharts !== 'undefined') {
            initCharts();
        } else {
            setTimeout(waitForCharts, 100);
        }
    }
    waitForCharts();

    // ===== DataTable Server-Side =====
    var dtInstance;

    (function initDataTable() {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
            setTimeout(initDataTable, 100);
            return;
        }

        dtInstance = $('#patientTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: window.location.href,
                type: 'GET'
            },
            columns: [
                { data: 'index', name: 'index', orderable: false, searchable: false, className: 'text-center text-muted small' },
                { data: 'hn', name: 'c.hn', className: 'fw-bold small' },
                { data: 'cid', name: 'p.cid', className: 'small text-muted' },
                { data: 'patient_name', name: 'patient_name', className: 'small' },
                { data: 'sex_name', name: 's.name', className: 'small text-center' },
                { data: 'pttype_name', name: 'y.name', className: 'small' },
                { data: 'regdate', name: 'c.regdate', className: 'small text-center' },
                { data: 'lastvisit', name: 'c.lastvisit', className: 'small text-center' },
                { data: 'last_fbs', name: 'last_fbs', className: 'small text-center', searchable: false },
                { data: 'last_hba1c', name: 'last_hba1c', className: 'small text-center', searchable: false },
                { data: 'last_ua', name: 'last_ua', className: 'small text-center', searchable: false },
                { data: 'last_bp_value', name: 'last_bp_value', className: 'small text-center', searchable: false },
                { data: 'doctor_name', name: 'd.name', className: 'small' },
                { data: 'status_badge', name: 'status_badge', className: 'text-center', searchable: false },
                { data: 'send_pcu_hospital_name', name: 'send_pcu_hospital_name', className: 'small' }
            ],
            pageLength: 10,
            dom: '<"d-flex justify-content-between align-items-center mb-3 py-2 px-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center p-3"ip>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success',
                    title: 'ทะเบียนผู้ป่วย'
                }
            ],
            language: {
                search: "ค้นหา:",
                lengthMenu: "แสดง _MENU_ รายการ",
                searchPlaceholder: "กรอกข้อมูลเพื่อค้นหา...",
                info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                paginate: {
                    previous: "ก่อนหน้า",
                    next: "ถัดไป"
                }
            },
            order: [[6, 'desc']],
            scrollX: true,
            drawCallback: function(settings) {
                updateCountBadge(this.api());
            }
        });

        function updateCountBadge(api) {
            if (!api || typeof api.page !== 'function') return;
            var count = api.page.info().recordsFiltered;
            $('#countBadge').text(count.toLocaleString() + ' ราย').show();
        }
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
                var sDate = (v - 544) + '-10-01';
                var eDate = (v - 543) + '-09-30';
                startPicker.setDate(sDate, true);
                endPicker.setDate(eDate, true);
                document.getElementById('budget_year_changed').value = '1';
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
