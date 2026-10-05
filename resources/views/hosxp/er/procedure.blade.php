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
        }

        .card-proc {
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card-proc:hover {
            transform: translateY(-4px);
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

        .filter-pill-btn {
            border-radius: 50px;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
        }

        .filter-pill-btn.active, .filter-pill-btn:hover {
            background: #0284c7;
            color: #fff;
            border-color: #0284c7;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
        }

        .filter-pill-btn.btn-cpr.active, .filter-pill-btn.btn-cpr:hover {
            background: #dc3545;
            border-color: #dc3545;
            box-shadow: 0 2px 6px rgba(220, 53, 69, 0.25);
        }

        .filter-pill-btn.btn-both.active, .filter-pill-btn.btn-both:hover {
            background: #7c3aed;
            border-color: #7c3aed;
            box-shadow: 0 2px 6px rgba(124, 58, 237, 0.25);
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
            background: rgba(59, 130, 246, 0.15);
            transition: width 0.6s ease;
        }
        .diag-progress.bg-intubation { background: rgba(2, 132, 199, 0.25); }
        .diag-progress.bg-cpr { background: rgba(220, 53, 69, 0.25); }
        
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
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Box -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-heartbeat text-danger me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }} (หัตถการใส่ท่อช่วยหายใจ ICD-9: 96.04 และ ฟื้นคืนชีพ CPR ICD-9: 99.60)</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0">
                    <div class="input-group input-group-sm shadow-sm" style="width: 230px; border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-light text-danger border-end-0 fw-bold" style="font-size: 0.8rem;">เลือก</span>
                        <select class="form-select border-start-0" name="budget_year" onchange="this.form.submit()" style="font-size: 0.85rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards (5 columns) -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 mb-4 g-3">
            <div class="col">
                <div class="card card-proc shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #475569 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-users-medical fa-2x text-secondary opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($summary_stats->total_patients ?? 0) }}</h3>
                        <div class="small fw-bold text-muted mt-1">ผู้ป่วยทำหัตถการสำคัญทั้งหมด</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-proc shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #0284c7 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-lungs fa-2x opacity-75" style="color: #0284c7;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #0284c7;">{{ number_format($summary_stats->intubation_patients ?? 0) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #0284c7;">ใส่ท่อช่วยหายใจ (96.04)</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-proc shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #dc3545 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-heartbeat fa-2x text-danger opacity-75"></i></div>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($summary_stats->cpr_patients ?? 0) }}</h3>
                        <div class="small fw-bold text-danger mt-1">ฟื้นคืนชีพ CPR (99.60)</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-proc shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #7c3aed !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-procedures fa-2x opacity-75" style="color: #7c3aed;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #7c3aed;">{{ number_format($summary_stats->both_patients ?? 0) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #7c3aed;">ทำทั้ง 2 อย่าง (ท่อ + CPR)</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-proc shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #198754 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-hospital-user fa-2x text-success opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($summary_stats->admit_patients ?? 0) }} / {{ number_format($summary_stats->refer_patients ?? 0) }}</h3>
                        <div class="small fw-bold text-success mt-1">Admit / Refer ส่งต่อ</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-proc" style="border-radius: 15px;">
                    <div class="card-header bg-light py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 16px 16px 0 0;">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="fas fa-chart-line me-2"></i>จำนวนผู้ป่วยทำหัตถการสำคัญ ER แยกรายเดือน (ใส่ท่อช่วยหายใจ, CPR, Admit, Refer)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-procedure-monthly"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 20 Diagnoses Row (Intubation vs CPR) -->
        <div class="row mb-4 g-3">
            <!-- Intubation Top Diags -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm card-proc h-100">
                    <div class="card-header text-white py-3 border-0" style="border-radius: 16px 16px 0 0; background-color: #0284c7;">
                        <h6 class="fw-bold mb-0"><i class="fas fa-lungs me-2"></i>20 อันดับโรคสูงสุดที่ได้รับการใส่ท่อช่วยหายใจ (Intubation)</h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                        @if(empty($diag_intubation))
                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                        @else
                            @php $max_intub = reset($diag_intubation)->sum ?? 1; @endphp
                            @foreach($diag_intubation as $index => $row)
                                <div class="diag-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            @if($index < 3 && $row->sum > 0)
                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                </span>
                                            @else
                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                            @endif
                                            {{ $row->name }}
                                        </span>
                                        <span class="badge rounded-pill text-white" style="background-color: #0284c7;">{{ number_format($row->sum) }}</span>
                                    </div>
                                    <div class="diag-progress bg-intubation" style="width: {{ ($row->sum / $max_intub) * 100 }}%"></div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- CPR Top Diags -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm card-proc h-100">
                    <div class="card-header bg-danger text-white py-3 border-0" style="border-radius: 16px 16px 0 0;">
                        <h6 class="fw-bold mb-0"><i class="fas fa-heartbeat me-2"></i>20 อันดับโรคสูงสุดที่ได้รับการฟื้นคืนชีพ (CPR)</h6>
                    </div>
                    <div class="card-body p-0" style="max-height: 420px; overflow-y: auto;">
                        @if(empty($diag_cpr))
                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                        @else
                            @php $max_cpr = reset($diag_cpr)->sum ?? 1; @endphp
                            @foreach($diag_cpr as $index => $row)
                                <div class="diag-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            @if($index < 3 && $row->sum > 0)
                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                </span>
                                            @else
                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                            @endif
                                            {{ $row->name }}
                                        </span>
                                        <span class="badge bg-danger rounded-pill">{{ number_format($row->sum) }}</span>
                                    </div>
                                    <div class="diag-progress bg-cpr" style="width: {{ ($row->sum / $max_cpr) * 100 }}%"></div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Patient List Table -->
        <div class="row pb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-proc" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <h6 class="m-0 fw-bold text-primary">
                                    <i class="fas fa-table me-2"></i> รายชื่อผู้ป่วยที่ได้รับการทำหัตถการสำคัญ ER
                                </h6>
                                <small class="text-muted">
                                    ช่วงวันที่: <span id="displayDateRange" class="fw-bold text-dark">{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}</span>
                                </small>
                            </div>

                            <!-- Filter Pills for Procedure Type -->
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <button type="button" class="filter-pill-btn {{ $proc_filter === 'all' ? 'active' : '' }}" data-filter="all">
                                    ทั้งหมด
                                </button>
                                <button type="button" class="filter-pill-btn {{ $proc_filter === 'intubation' ? 'active' : '' }}" data-filter="intubation">
                                    <i class="fas fa-lungs me-1"></i> ใส่ท่อช่วยหายใจ
                                </button>
                                <button type="button" class="filter-pill-btn btn-cpr {{ $proc_filter === 'cpr' ? 'active' : '' }}" data-filter="cpr">
                                    <i class="fas fa-heartbeat me-1"></i> ฟื้นคืนชีพ CPR
                                </button>
                                <button type="button" class="filter-pill-btn btn-both {{ $proc_filter === 'both' ? 'active' : '' }}" data-filter="both">
                                    <i class="fas fa-plus-circle me-1"></i> ทำทั้ง 2 อย่าง
                                </button>
                            </div>

                            <!-- Independent Table Filter -->
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <div class="input-group input-group-sm" style="width: 140px;">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" id="table_start_date" class="form-control border-start-0 ps-0 text-center"
                                        value="{{ $table_start_date }}" placeholder="เริ่ม">
                                </div>
                                <span class="text-muted small">ถึง</span>
                                <div class="input-group input-group-sm" style="width: 140px;">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" id="table_end_date" class="form-control border-start-0 ps-0 text-center"
                                        value="{{ $table_end_date }}" placeholder="สิ้นสุด">
                                </div>
                                <button type="button" id="btnFilterTable" class="btn btn-sm btn-danger px-3 shadow-sm" style="border-radius: 6px; background-color: #e11d48; border-color: #e11d48;">
                                    <i class="fas fa-search me-1"></i> ค้นหา
                                </button>
                                <div class="btn-group btn-group-sm ms-1 shadow-sm">
                                    <button type="button" id="btnCurrentMonth" class="btn btn-outline-secondary" title="เลือกเดือนปัจจุบัน">
                                        เดือนนี้
                                    </button>
                                    <button type="button" id="btnPrevMonth" class="btn btn-outline-secondary" title="เลือกเดือนก่อนหน้า">
                                        เดือนก่อน
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4" id="tableContainer">
                        @include('hosxp.er.partials._table_procedure')
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
                let currentProcFilter = '{{ $proc_filter }}';

                function initPatientTable() {
                    if ($.fn.DataTable.isDataTable('#table-procedure-list')) {
                        $('#table-procedure-list').DataTable().destroy();
                    }
                    $('#table-procedure-list').DataTable({
                        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                        buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success',
                            title: '{{ $title }}',
                            messageTop: function() {
                                return 'ช่วงวันที่: ' + ($('#displayDateRange').text() || '{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}');
                            }
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
                }

                let tableStartPicker, tableEndPicker;
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
                        allowInput: false,
                        onReady: function(selectedDates, dateStr, instance) {
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
                    tableStartPicker = flatpickr("#table_start_date", commonConfig);
                    tableEndPicker = flatpickr("#table_end_date", commonConfig);
                }

                function loadTableData(startDate, endDate, filter) {
                    const $btn = $('#btnFilterTable');
                    const originalText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> ค้นหา...');
                    $('#tableContainer').css('opacity', '0.5');

                    $.ajax({
                        url: "{{ route('hosxp.er.procedure') }}",
                        method: 'GET',
                        data: {
                            budget_year: "{{ $budget_year }}",
                            table_start_date: startDate,
                            table_end_date: endDate,
                            proc_filter: filter || currentProcFilter
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success && res.html) {
                                if ($.fn.DataTable.isDataTable('#table-procedure-list')) {
                                    $('#table-procedure-list').DataTable().destroy();
                                }
                                $('#tableContainer').html(res.html);
                                initPatientTable();
                                $('#displayDateRange').text(`${res.start_date_thai} ถึง ${res.end_date_thai}`);
                            }
                        },
                        error: function(err) {
                            console.error("Failed to load table data", err);
                            alert("เกิดข้อผิดพลาดในการโหลดข้อมูลตาราง กรุณาลองใหม่อีกครั้ง");
                        },
                        complete: function() {
                            $('#tableContainer').css('opacity', '1');
                            $btn.prop('disabled', false).html(originalText);
                        }
                    });
                }

                $('.filter-pill-btn').on('click', function() {
                    $('.filter-pill-btn').removeClass('active');
                    $(this).addClass('active');
                    currentProcFilter = $(this).data('filter');

                    const s = $('#table_start_date').val();
                    const e = $('#table_end_date').val();
                    loadTableData(s, e, currentProcFilter);
                });

                $('#btnFilterTable').on('click', function() {
                    const s = $('#table_start_date').val();
                    const e = $('#table_end_date').val();
                    loadTableData(s, e, currentProcFilter);
                });

                $('#btnCurrentMonth').on('click', function() {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
                    const s = `${y}-${m}-01`;
                    const e = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

                    if (tableStartPicker) tableStartPicker.setDate(s, true);
                    if (tableEndPicker) tableEndPicker.setDate(e, true);
                    loadTableData(s, e, currentProcFilter);
                });

                $('#btnPrevMonth').on('click', function() {
                    const now = new Date();
                    const prev = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    const y = prev.getFullYear();
                    const m = String(prev.getMonth() + 1).padStart(2, '0');
                    const lastDay = new Date(y, prev.getMonth() + 1, 0).getDate();
                    const s = `${y}-${m}-01`;
                    const e = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

                    if (tableStartPicker) tableStartPicker.setDate(s, true);
                    if (tableEndPicker) tableEndPicker.setDate(e, true);
                    loadTableData(s, e, currentProcFilter);
                });

                initPatientTable();

                // Render Procedure Monthly Trend Chart
                const monthlyCategories = {!! json_encode(array_column($procedure_monthly, 'month_year')) !!};
                const totalData = {!! json_encode(array_map('intval', array_column($procedure_monthly, 'total_cases'))) !!};
                const intubationData = {!! json_encode(array_map('intval', array_column($procedure_monthly, 'intubation_cases'))) !!};
                const cprData = {!! json_encode(array_map('intval', array_column($procedure_monthly, 'cpr_cases'))) !!};
                const admitData = {!! json_encode(array_map('intval', array_column($procedure_monthly, 'admit_cases'))) !!};

                const chartOptions = {
                    series: [
                        { name: 'ใส่ท่อช่วยหายใจ (Intubation)', data: intubationData },
                        { name: 'ฟื้นคืนชีพ CPR', data: cprData },
                        { name: 'Admit ผู้ป่วยใน', data: admitData },
                        { name: 'ทำหัตถการทั้งหมด', data: totalData }
                    ],
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: { show: true }
                    },
                    dataLabels: {
                        enabled: true
                    },
                    colors: ['#0284c7', '#dc3545', '#198754', '#64748b'],
                    stroke: {
                        curve: 'smooth',
                        width: [3, 3, 2, 2],
                        dashArray: [0, 0, 4, 2]
                    },
                    markers: {
                        size: 5
                    },
                    xaxis: {
                        categories: monthlyCategories,
                        labels: { style: { fontSize: '11px', fontWeight: 600 } }
                    },
                    yaxis: {
                        title: { text: 'จำนวนผู้ป่วย (ราย)', style: { fontWeight: 600 } },
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

                const chart = new ApexCharts(document.querySelector("#chart-procedure-monthly"), chartOptions);
                chart.render();
            });
        </script>
    @endpush
@endsection
