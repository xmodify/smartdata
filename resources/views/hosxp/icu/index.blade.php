@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <style>
        .page-header-container {
            background: #f8fbfd; /* Slightly darker background to make white cards pop */
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            border: 1px solid #e3eef5;
        }

        body { background-color: #f4f7fa !important; } /* Page background */

        .header-form-controls {
            display: flex; align-items: center; gap: 0.5rem;
        }

        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        .card-icu { 
            border-radius: 16px; 
            border: 1px solid #e3eef5 !important; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            background: #fff;
            overflow: hidden;
        }
        .chart-container { min-height: 350px; }
        
        .table-icu { font-size: 0.85rem; }
        .table-icu thead th { background-color: #f8f9fa; color: #334155; font-weight: 700; border-bottom: 2px solid #e2e8f0; }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-budget { width: 100% !important; }
        }

        /* Custom DataTables Styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            font-size: 0.8rem !important;
        }
        .dt-buttons .btn-success {
            background-color: #1d6f42 !important; /* Excel Corporate Green */
            border-color: #1d6f42 !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            font-size: 0.8rem !important;
            padding: 0.4rem 1rem !important;
            margin-right: 5px;
            font-weight: 600 !important;
            box-shadow: 0 4px 6px rgba(29, 111, 66, 0.1) !important;
            transition: all 0.2s ease !important;
        }
        .dt-buttons .btn-success:hover {
            background-color: #155130 !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(29, 111, 66, 0.2) !important;
        }
        .dt-buttons .btn-success i {
            color: #ffffff !important;
        }
        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            border-bottom: 2px solid #e3e6f0 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4e73df !important;
            color: white !important;
            border: 1px solid #4e73df !important;
            border-radius: 0.5rem !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-hospital-user text-danger me-2"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ปีงบประมาณ {{ $budget_year }} | ระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-danger"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-danger"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget">
                        <select class="form-select border-end-0" name="budget_year">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-danger px-3">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Charts Top -->
        <div class="row mb-4 g-4">
            <div class="col-md-3">
                <div class="card card-icu shadow-sm border-0 h-100 bg-white" style="min-height: 200px; transition: transform 0.3s ease; border-top: 4px solid #e74a3b !important;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
                        <div class="mb-3">
                            <span class="p-3 rounded-circle bg-light d-inline-block text-danger border border-danger border-opacity-10">
                                <i class="fas fa-user-check fa-2x"></i>
                            </span>
                        </div>
                        <h1 class="display-4 fw-bold mb-1 text-danger animate__animated animate__pulse animate__infinite" style="letter-spacing: -2px;">{{ number_format($admit_count) }}</h1>
                        <div class="text-muted fw-bold small text-uppercase" style="letter-spacing: 1px;">กำลัง Admit</div>
                        <div class="mt-4 w-100">
                            <div class="badge bg-light text-muted w-100 py-2 border" style="border-radius: 10px;">
                                <i class="fas fa-clock me-1 text-primary"></i> ข้อมูล ณ ปัจจุบัน
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card card-icu shadow-sm h-100" style="border-top: 4px solid #4e73df !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-bar me-2 text-primary"></i> สถิติผู้ป่วย ICU รายเดือน</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="monthlyChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Bottom -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="card card-icu shadow-sm h-100" style="border-top: 4px solid #1cc88a !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie me-2 text-success"></i> แยกตามประเภทการจำหน่าย (Discharge Type)</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="dchTypeChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-icu shadow-sm h-100" style="border-top: 4px solid #f6c23e !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ol me-2 text-warning"></i> 10 อันดับรายโรค (PDX)</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="pdxChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="row pb-5">
            <div class="col-12">
                <div class="card card-icu shadow-sm" style="border-top: 4px solid #36b9cc !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-info"></i> รายละเอียดผู้ป่วย ICU</h6>
                        <p class="text-muted small mb-0 mt-1">แสดงข้อมูลผู้ป่วยที่มีการเคลื่อนย้ายลงเตียง ICU (iptbedmove LIKE 'ICU%')</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-icu mb-0" id="icuTable">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Ward</th>
                                        <th>AN / HN</th>
                                        <th style="min-width: 150px;">ชื่อ-นามสกุล</th>
                                        <th>วันที่รับเข้า</th>
                                        <th>วันที่จำหน่าย</th>
                                        <th class="text-center">วันนอน (LOS)</th>
                                        <th>สถานะ/ประเภทจำหน่าย</th>
                                        <th>แพทย์</th>
                                        <th style="min-width: 200px;">การวินิจฉัย (PDX / Diag Text)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($patients as $row)
                                    <tr>
                                        <td class="ps-4"><span class="badge bg-light text-dark border">{{ $row->ward_name }}</span></td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $row->an }}</div>
                                            <div class="small text-muted">HN: {{ $row->hn }}</div>
                                        </td>
                                        <td>{{ $row->ptname }}</td>
                                        <td>
                                            <div class="small">{{ date('d/m/Y', strtotime($row->regdate)) }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $row->regtime }} น.</div>
                                        </td>
                                        <td>
                                            @if($row->dchdate)
                                                <div class="small">{{ date('d/m/Y', strtotime($row->dchdate)) }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $row->dchtime }} น.</div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold">{{ $row->admdate }}</td>
                                        <td>
                                            <div class="small fw-bold">{{ $row->dch_status }}</div>
                                            <div class="small text-muted">{{ $row->dch_type }}</div>
                                        </td>
                                        <td><div class="small">{{ $row->dch_doctor }}</div></td>
                                        <td>
                                            @if($row->pdx)
                                                <span class="badge bg-danger mb-1">{{ $row->pdx }}</span>
                                            @endif
                                            <div class="small" style="font-size: 0.75rem;">{{ $row->diag_text_list }}</div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <i class="fas fa-folder-open fa-3x text-muted opacity-25 mb-3"></i>
                                            <p class="text-muted">ไม่พบข้อมูลผู้ป่วย ICU ในช่วงเวลาที่เลือก</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
        
        <script>
            $(document).ready(function() {
                // Initialize DataTable
                $('#icuTable').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success',
                        title: 'รายงานผู้ป่วย ICU ({{ DateThai($start_date) }} - {{ DateThai($end_date) }})'
                    }],
                    language: {
                        search: "ค้นหา:",
                        lengthMenu: "แสดง _MENU_ รายการ",
                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                        paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                    },
                    pageLength: 10,
                    ordering: true,
                    responsive: true
                });

                const yearOffset = 543;
                const commonConfig = {
                    locale: "th", dateFormat: "Y-m-d", altInput: true, altFormat: "j M Y", allowInput: false,
                    onReady: function(selectedDates, dateStr, instance) {
                        if (instance.altInput) {
                            const date = instance.selectedDates[0] || new Date(instance.input.value);
                            if (date && !isNaN(date.getTime())) {
                                const day = date.getDate();
                                const month = instance.l10n.months.shorthand[date.getMonth()];
                                const year = date.getFullYear() + yearOffset;
                                instance.altInput.value = `${day} ${month} ${year}`;
                            }
                        }

                        // Add Today Button
                        const container = instance.calendarContainer;
                        if (container && !container.querySelector('.flatpickr-today-button')) {
                            const btn = document.createElement("div");
                            btn.className = "flatpickr-today-button";
                            btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                            btn.addEventListener("mousedown", function(e) {
                                e.preventDefault();
                                e.stopPropagation();
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
                const startPicker = flatpickr("#start_date", commonConfig);
                const endPicker = flatpickr("#end_date", commonConfig);

                // Update start_date and end_date based on budget_year change
                $('select[name="budget_year"]').on('change', function() {
                    var selectedYear = parseInt($(this).val());
                    if(!isNaN(selectedYear)) {
                        // Calculate budget year ranges
                        var startYear = selectedYear - 544; 
                        var endYear = selectedYear - 543;   
                        var startDateStr = startYear + "-10-01";
                        var endDateStr = endYear + "-09-30";
                        
                        setTimeout(() => {
                            if (typeof startPicker !== 'undefined' && startPicker) startPicker.setDate(startDateStr, true);
                            if (typeof endPicker !== 'undefined' && endPicker) endPicker.setDate(endDateStr, true);
                        }, 50);
                    }
                });

                // 1. Monthly Chart
                var monthlyOptions = {
                    series: [{ name: 'จำนวนผู้ป่วย (คน)', data: @json(array_column($monthly_stats, 'count')) }],
                    chart: { type: 'bar', height: 350, toolbar: { show: false }, zoom: { enabled: false } },
                    colors: ['#4e73df'],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '50%', dataLabels: { position: 'top' } } },
                    xaxis: { categories: @json(array_column($monthly_stats, 'month')), axisBorder: { show: false } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    dataLabels: { enabled: true, offsetBox: -20, style: { fontSize: '12px', colors: ['#4e73df'] }, offsetY: -20 }
                };
                new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions).render();

                // 2. Discharge Type Chart
                var dchTypeOptions = {
                    series: @json(array_column($dch_types, 'count')),
                    chart: { type: 'donut', height: 350 },
                    labels: @json(array_column($dch_types, 'dch_type_name')),
                    colors: ['#1cc88a', '#4e73df', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#36b9cc'],
                    legend: { position: 'bottom', fontSize: '11px' },
                    stroke: { width: 0 },
                    dataLabels: { enabled: true, dropShadow: { enabled: false } },
                    responsive: [{ breakpoint: 480, options: { chart: { width: 200 } } }]
                };
                new ApexCharts(document.querySelector("#dchTypeChart"), dchTypeOptions).render();

                // 3. Top 10 PDX Chart
                var pdxOptions = {
                    series: [{ name: 'จำนวนคน', data: @json(array_column($top_pdx, 'count')) }],
                    chart: { type: 'bar', height: 350, toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '60%' } },
                    colors: ['#f6c23e'],
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    xaxis: { categories: @json(array_column($top_pdx, 'diag_name')), axisBorder: { show: false } },
                    dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#444'] }, offsetX: 5, dropShadow: { enabled: false } }
                };
                new ApexCharts(document.querySelector("#pdxChart"), pdxOptions).render();
            });
        </script>
    @endpush
@endsection
