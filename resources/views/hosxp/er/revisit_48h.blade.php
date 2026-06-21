@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #dc3545; transition: all 0.3s;">
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

        .header-form-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-group-date {
            width: 160px !important;
        }

        .input-group-budget {
            width: 250px !important;
        }

        @media (max-width: 768px) {
            .page-header-container {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .header-form-controls {
                width: 100%;
                flex-wrap: wrap;
            }

            .input-group-date,
            .input-group-budget {
                width: 100% !important;
            }
        }

        .card-revisit {
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card-revisit:hover {
            transform: translateY(-5px);
        }

        /* Override DataTables UI to match the premium look */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            outline: none !important;
            font-size: 0.8rem !important;
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

        .table-revisit tbody td {
            font-size: 0.82rem;
            vertical-align: middle;
        }

        .text-revisit-red { color: #e11d48; }
        .bg-pastel-red { background-color: #fff1f2; }
        
        .flatpickr-today-button {
            padding: 10px;
            text-align: center;
            border-top: 1px solid #e6e6e6;
            cursor: pointer;
            font-weight: bold;
            color: #e11d48;
            background: #f8f9fa;
        }
        .flatpickr-today-button:hover {
            background: #fff1f2;
        }

        /* Diagnostic List Style */
        .diag-item {
            padding: 10px 15px;
            border-bottom: 1px solid #f1f5f9;
            position: relative;
        }
        .diag-item:last-child {
            border-bottom: none;
        }
        .diag-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(239, 110, 110, 0.15);
            transition: width 0.6s ease;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Box -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-history text-revisit-red me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-revisit-red small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง
                        {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" id="filter-form" class="m-0 header-form-controls">
                    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-revisit-red"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-revisit-red"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0"
                            value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget"
                        style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" id="budget_year" style="font-size: 0.8rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-danger px-3" style="font-size: 0.8rem; background-color: #e11d48; border-color: #e11d48;">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @php
            $total_count = count($revisit_list);
        @endphp

        <!-- Summary Cards -->
        <div class="row mb-4 g-3 justify-content-center">
            <div class="col-md-4">
                <div class="card card-revisit shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #dc3545 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-history fa-2x text-danger opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($total_count) }}</h3>
                        <div class="small fw-bold text-danger mt-1">จำนวนการ Re-visit ใน 48 ชม. ด้วยโรคเดิม</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Chart Row (EMS style, spans 12) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-revisit" style="border-radius: 15px;">
                    <div class="card-header bg-light py-3 border-0" style="border-radius: 16px 16px 0 0;">
                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-chart-line me-2"></i>จำนวนผู้ป่วย Re-visit แยกรายเดือน</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-revisit-monthly"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- side-by-side row: Left is Chart, Right is DataTable (รูปแบบเหมือนรูป) with custom tabs -->
        <div class="nav-tabs-custom mt-4">
            <ul class="nav nav-tabs border-0" id="revisitTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="diagtop-tab" data-bs-toggle="tab" data-bs-target="#diagtop-pane"
                        type="button" role="tab" aria-controls="diagtop-pane" aria-selected="true">
                        <i class="fas fa-clipboard-list me-1 text-primary"></i> 20 อันดับโรค (Primary Diagnosis)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="grp504-tab" data-bs-toggle="tab" data-bs-target="#grp504-pane"
                        type="button" role="tab" aria-controls="grp504-pane" aria-selected="false">
                        <i class="fas fa-layer-group me-1 text-warning"></i> 21 กลุ่มสาเหตุ (รพ.504)
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="revisitTabsContent">
            <!-- Tab 1: 20 อันดับโรค -->
            <div class="tab-pane fade show active" id="diagtop-pane" role="tabpanel" aria-labelledby="diagtop-tab">
                <div class="row mb-4 g-3">
                    <!-- Left Column: Top 20 Chart -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm card-revisit" style="border-radius: 15px; height: 100%;">
                            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 450px;">
                                <div id="chart-revisit-top20" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Top 20 DataTable -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm card-revisit" style="border-radius: 15px; height: 100%;">
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="table-revisit-top20" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 10%">อันดับ</th>
                                                <th>ICD10</th>
                                                <th>ชื่อโรค (อังกฤษ)</th>
                                                <th>ชื่อโรค (ไทย)</th>
                                                <th class="text-center">จำนวน</th>
                                                <th class="text-center">ชาย</th>
                                                <th class="text-center">หญิง</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($revisit_diagtop as $index => $row)
                                                <tr>
                                                    <td class="text-center fw-bold text-muted">
                                                        @if ($index < 3 && $row->total > 0)
                                                            <span class="badge rounded-pill bg-warning text-dark px-2">
                                                                <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                            </span>
                                                        @else
                                                            {{ $index + 1 }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2">{{ $row->code }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 180px;" title="{{ $row->pdx_name }}">{{ $row->pdx_name }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate text-muted small" style="max-width: 150px;" title="{{ $row->pdx_tname }}">{{ $row->pdx_tname }}</div>
                                                    </td>
                                                    <td class="text-center fw-bold text-primary">{{ number_format($row->total) }}</td>
                                                    <td class="text-center text-primary">{{ number_format($row->male) }}</td>
                                                    <td class="text-center text-danger">{{ number_format($row->female) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: 21 กลุ่มสาเหตุ (รพ.504) -->
            <div class="tab-pane fade" id="grp504-pane" role="tabpanel" aria-labelledby="grp504-tab">
                <div class="row mb-4 g-3">
                    <!-- Left Column: 504 Chart -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm card-revisit" style="border-radius: 15px; height: 100%;">
                            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 450px;">
                                <div id="chart-revisit-504" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: 504 DataTable -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm card-revisit" style="border-radius: 15px; height: 100%;">
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0" id="table-revisit-504" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 10%">อันดับ</th>
                                                <th>ชื่อกลุ่มสาเหตุ 504</th>
                                                <th class="text-center">จำนวน</th>
                                                <th class="text-center">ชาย</th>
                                                <th class="text-center">หญิง</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($revisit_504 as $index => $row)
                                                <tr>
                                                    <td class="text-center fw-bold text-muted">
                                                        @if ($index < 3 && $row->total > 0)
                                                            <span class="badge rounded-pill bg-warning text-dark px-2">
                                                                <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                            </span>
                                                        @else
                                                            {{ $index + 1 }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="fw-medium text-dark">{{ $row->name }}</div>
                                                    </td>
                                                    <td class="text-center fw-bold text-warning">{{ number_format($row->total) }}</td>
                                                    <td class="text-center text-primary">{{ number_format($row->male) }}</td>
                                                    <td class="text-center text-danger">{{ number_format($row->female) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Patient List Table -->
        <div class="row pb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-revisit" style="border-radius: 15px;">
                    <div class="card-header bg-light py-3 border-0" style="border-radius: 16px 16px 0 0;">
                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-table me-2"></i>รายชื่อผู้ป่วย Re-visit ใน 48 ชม. ด้วยโรคเดิม</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="table-revisit-list" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">ลำดับ</th>
                                        <th>วันที่รับบริการ</th>
                                        <th>เว้นระยะจากครั้งก่อน</th>
                                        <th style="width: 5%">คิว</th>
                                        <th>HN</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th style="width: 5%">อายุ</th>
                                        <th>สิทธิการรักษา</th>
                                        <th>อาการสำคัญ (CC)</th>
                                        <th>วินิจฉัยหลัก (PDX)</th>
                                        <th>แผนกที่เข้ารับบริการ</th>
                                        <th>สถานะหลังตรวจ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($revisit_list as $index => $row)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="text-center">{{ DateThai($row->vstdate) }}</td>
                                            <td class="text-center fw-bold text-danger">{{ $row->p_vstdate }}</td>
                                            <td class="text-center fw-bold text-primary">{{ $row->q }}</td>
                                            <td class="text-center">{{ $row->hn }}</td>
                                            <td>{{ $row->ptname }}</td>
                                            <td class="text-center">{{ $row->age_y }} ปี</td>
                                            <td class="small">{{ $row->pttype }}</td>
                                            <td class="small" title="{{ $row->cc }}">{{ Str::limit($row->cc, 40) }}</td>
                                            <td class="text-center fw-bold">{{ $row->pdx }}</td>
                                            <td class="text-center">
                                                @if($row->depart === 'ER')
                                                    <span class="badge bg-danger">ER</span>
                                                @else
                                                    <span class="badge bg-primary">OPD</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($row->admit)
                                                    <span class="badge bg-purple"><i class="fas fa-bed me-1"></i> {{ $row->admit }}</span>
                                                @endif
                                                @if($row->refer)
                                                    <span class="badge bg-orange"><i class="fas fa-share me-1"></i> {{ $row->refer }}</span>
                                                @endif
                                                @if(!$row->admit && !$row->refer)
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
        <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
        
        <script>
            $(document).ready(function() {
                // Initialize Flatpickr
                let startPicker, endPicker;
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
                        allowInput: false,
                        onReady: function(selectedDates, dateStr, instance) {
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

                            if (instance.altInput) {
                                const date = instance.selectedDates[0] || new Date(instance.input.value);
                                if (date && !isNaN(date.getTime())) {
                                    const day = date.getDate();
                                    const month = instance.l10n.months.shorthand[date.getMonth()];
                                    const year = date.getFullYear() + yearOffset;
                                    instance.altInput.value = `${day} ${month} ${year}`;
                                }
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
                    startPicker = flatpickr("#start_date", commonConfig);
                    endPicker = flatpickr("#end_date", commonConfig);
                }

                // Update start_date and end_date on budget_year change
                $('#budget_year').on('change', function() {
                    var selectedYear = parseInt($(this).val());
                    if(!isNaN(selectedYear)) {
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

                // Initialize DataTable for Patient List
                $('#table-revisit-list').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success',
                        title: '{{ $title }}',
                        messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
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

                // Initialize DataTable for Top 20 Summary
                $('#table-revisit-top20').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success',
                        title: '20 อันดับโรคสูงสุด (Re-visit)',
                        messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
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

                // Initialize DataTable for Group 504 Summary
                $('#table-revisit-504').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success',
                        title: 'กลุ่มสาเหตุ รพ.504 (Re-visit)',
                        messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
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

                // Render Re-visit Monthly Trend Line Chart (ER) - EMS style
                const monthlyCategories = {!! json_encode(array_column($revisit_monthly, 'month_year')) !!};
                const erData = {!! json_encode(array_map('intval', array_column($revisit_monthly, 'er'))) !!};

                const monthlyChartOptions = {
                    series: [
                        { name: 'ER (ครั้ง)', data: erData }
                    ],
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: { show: true }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    colors: ['#dc3545'],
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 5
                    },
                    xaxis: {
                        categories: monthlyCategories,
                        labels: { style: { fontSize: '11px', fontWeight: 600 } }
                    },
                    yaxis: {
                        title: { text: 'จำนวน Re-visit (ครั้ง)', style: { fontWeight: 600 } },
                        labels: { formatter: (val) => val.toLocaleString() }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'center'
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                };

                const monthlyChart = new ApexCharts(document.querySelector("#chart-revisit-monthly"), monthlyChartOptions);
                monthlyChart.render();

                // Render Top 20 Horizontal Bar Chart (ICD-10)
                const topDiagNames = {!! json_encode(array_map(function($row) {
                    $fullName = '[' . $row->code . '] ' . ($row->pdx_tname ?: $row->pdx_name);
                    return mb_strlen($fullName, 'UTF-8') > 30 ? mb_substr($fullName, 0, 30, 'UTF-8') . '...' : $fullName;
                }, $revisit_diagtop)) !!};
                const topDiagTotals = {!! json_encode(array_map('intval', array_column($revisit_diagtop, 'total'))) !!};

                const top20ChartOptions = {
                    series: [{
                        name: 'จำนวน Re-visit (ครั้ง)',
                        data: topDiagTotals
                    }],
                    chart: {
                        type: 'bar',
                        height: 480,
                        toolbar: { show: true }
                    },
                    colors: ['#2563eb'],
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            barHeight: '70%',
                            borderRadius: 4,
                            dataLabels: {
                                position: 'end'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        textAnchor: 'start',
                        style: {
                            colors: ['#000'],
                            fontWeight: 'bold',
                            fontSize: '11px'
                        },
                        formatter: function(val) {
                            return val.toLocaleString();
                        },
                        offsetX: 8
                    },
                    xaxis: {
                        categories: topDiagNames,
                        labels: { 
                            show: true,
                            style: { fontSize: '11px', fontWeight: 600 } 
                        }
                    },
                    yaxis: {
                        labels: {
                            maxWidth: 220,
                            style: { fontSize: '11px', fontWeight: 600 }
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                };

                const top20Chart = new ApexCharts(document.querySelector("#chart-revisit-top20"), top20ChartOptions);
                top20Chart.render();

                // Render Group 504 Horizontal Bar Chart
                const group504Names = {!! json_encode(array_map(function($row) {
                    return mb_strlen($row->name, 'UTF-8') > 30 ? mb_substr($row->name, 0, 30, 'UTF-8') . '...' : $row->name;
                }, $revisit_504)) !!};
                const group504Totals = {!! json_encode(array_map('intval', array_column($revisit_504, 'total'))) !!};

                const group504ChartOptions = {
                    series: [{
                        name: 'จำนวน Re-visit (ครั้ง)',
                        data: group504Totals
                    }],
                    chart: {
                        type: 'bar',
                        height: 480,
                        toolbar: { show: true }
                    },
                    colors: ['#f59e0b'],
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            barHeight: '70%',
                            borderRadius: 4,
                            dataLabels: {
                                position: 'end'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        textAnchor: 'start',
                        style: {
                            colors: ['#000'],
                            fontWeight: 'bold',
                            fontSize: '11px'
                        },
                        formatter: function(val) {
                            return val.toLocaleString();
                        },
                        offsetX: 8
                    },
                    xaxis: {
                        categories: group504Names,
                        labels: { 
                            show: true,
                            style: { fontSize: '11px', fontWeight: 600 } 
                        }
                    },
                    yaxis: {
                        labels: {
                            maxWidth: 220,
                            style: { fontSize: '11px', fontWeight: 600 }
                        }
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                };

                const group504Chart = new ApexCharts(document.querySelector("#chart-revisit-504"), group504ChartOptions);
                group504Chart.render();

                // Trigger window resize when switching tabs to fix ApexCharts rendering issues in hidden panes
                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                    window.dispatchEvent(new Event('resize'));
                });
            });
        </script>
    @endpush
@endsection
