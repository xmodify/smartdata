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

        .card-stat {
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card-stat:hover {
            transform: translateY(-4px);
        }

        /* Filter Pills */
        .filter-pill-btn {
            border-radius: 50px;
            padding: 0.35rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-pill-btn:hover {
            border-color: #cbd5e1;
            background-color: #f8fafc;
            color: #334155;
        }

        .filter-pill-btn.active {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-color: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
        }

        .flatpickr-today-button {
            border-top: 1px solid #e2e8f0;
            padding: 8px 0;
            text-align: center;
            font-weight: 600;
            font-size: 0.85rem;
            color: #2563eb;
            cursor: pointer;
            background-color: #f8fafc;
            border-radius: 0 0 8px 8px;
            transition: background-color 0.2s;
        }

        .flatpickr-today-button:hover {
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        /* Override DataTables UI */
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
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Box -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="p-2 rounded-3 me-3" style="background-color: #eff6ff;">
                    <i class="fas fa-user-nurse fa-2x text-primary"></i>
                </div>
                <div class="py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">
                        <span class="badge bg-secondary-subtle text-secondary me-1">ช่วงเวลา 19:00 - 08:00 น.</span>
                        ข้อมูลปีงบประมาณ {{ $budget_year }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0">
                    <div class="input-group input-group-sm shadow-sm" style="width: 230px; border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-light text-primary border-end-0 fw-bold" style="font-size: 0.8rem;">ปีงบประมาณ</span>
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

        @php
            $total_cases = $summary_stats->total_cases ?? 0;
            $evening_cases = $summary_stats->evening_cases ?? 0;
            $night_cases = $summary_stats->night_cases ?? 0;
            $admit_cases = $summary_stats->admit_cases ?? 0;
            $refer_cases = $summary_stats->refer_cases ?? 0;

            $evening_pct = $total_cases > 0 ? round(($evening_cases / $total_cases) * 100, 1) : 0;
            $night_pct = $total_cases > 0 ? round(($night_cases / $total_cases) * 100, 1) : 0;
        @endphp

        <!-- Summary KPI Cards -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 mb-4 g-3">
            <!-- 1. Total Cases -->
            <div class="col">
                <div class="card card-stat shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #3b82f6 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-users-line fa-2x text-primary opacity-75"></i></div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($total_cases) }}</h3>
                        <div class="small fw-bold text-muted mt-1">ผู้ป่วยนอกเวลาทั้งหมด</div>
                        <div class="badge bg-primary-subtle text-primary mt-2">ปีงบฯ {{ $budget_year }}</div>
                    </div>
                </div>
            </div>

            <!-- 2. Evening Cases (19:00 - 24:00) -->
            <div class="col">
                <div class="card card-stat shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #f59e0b !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-moon fa-2x text-warning opacity-75"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #d97706;">{{ number_format($evening_cases) }}</h3>
                        <div class="small fw-bold text-muted mt-1">ช่วงเวลา 19:00 - 24:00 น.</div>
                        <div class="small text-muted mt-1 fw-semibold">({{ $evening_pct }}% ของทั้งหมด)</div>
                    </div>
                </div>
            </div>

            <!-- 3. Night Cases (00:00 - 08:00) -->
            <div class="col">
                <div class="card card-stat shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #6366f1 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-cloud-moon fa-2x opacity-75" style="color: #6366f1;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #4f46e5;">{{ number_format($night_cases) }}</h3>
                        <div class="small fw-bold text-muted mt-1">ช่วงเวลา 00:00 - 08:00 น.</div>
                        <div class="small text-muted mt-1 fw-semibold">({{ $night_pct }}% ของทั้งหมด)</div>
                    </div>
                </div>
            </div>

            <!-- 4. Admit Cases -->
            <div class="col">
                <div class="card card-stat shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #8b5cf6 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-bed-pulse fa-2x opacity-75" style="color: #8b5cf6;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #7c3aed;">{{ number_format($admit_cases) }}</h3>
                        <div class="small fw-bold text-muted mt-1">รับไว้รักษาใน รพ. (Admit)</div>
                        <div class="small text-muted mt-1 fw-semibold">({{ $total_cases > 0 ? round(($admit_cases / $total_cases) * 100, 2) : 0 }}%)</div>
                    </div>
                </div>
            </div>

            <!-- 5. Refer Out Cases -->
            <div class="col">
                <div class="card card-stat shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #ea580c !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-ambulance fa-2x opacity-75" style="color: #ea580c;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #ea580c;">{{ number_format($refer_cases) }}</h3>
                        <div class="small fw-bold text-muted mt-1">ส่งต่อไป รพ.อื่น (Refer)</div>
                        <div class="small text-muted mt-1 fw-semibold">({{ $total_cases > 0 ? round(($refer_cases / $total_cases) * 100, 2) : 0 }}%)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Analytics Row -->
        <div class="row g-3 mb-4">
            <!-- Left Chart: Monthly Trend -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100 bg-white" style="border-radius: 14px;">
                    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            แนวโน้มจำนวนผู้ป่วยรายเดือน (แยกตามช่วงเวลา 19:00-24:00 และ 00:00-08:00)
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div id="chart-monthly-trend" style="min-height: 330px;"></div>
                    </div>
                </div>
            </div>

            <!-- Right: Top Diagnoses and Top Providers -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100 bg-white" style="border-radius: 14px;">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-1 fw-semibold small" id="pills-diag-tab" data-bs-toggle="pill" data-bs-target="#pills-diag" type="button" role="tab">
                                    <i class="fas fa-virus me-1"></i> 10 อันดับโรค/การวินิจฉัย
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-1 fw-semibold small" id="pills-staff-tab" data-bs-toggle="pill" data-bs-target="#pills-staff" type="button" role="tab">
                                    <i class="fas fa-user-nurse me-1"></i> 10 อันดับผู้ให้บริการ
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="pills-tabContent">
                            <!-- Diag Tab -->
                            <div class="tab-pane fade show active" id="pills-diag" role="tabpanel">
                                @php
                                    $maxDiag = !empty($top_diags) ? max(array_column($top_diags, 'sum')) : 1;
                                @endphp
                                <div class="list-group list-group-flush" style="max-height: 330px; overflow-y: auto;">
                                    @forelse($top_diags as $index => $item)
                                        <div class="diag-item">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="small fw-semibold text-truncate me-2" style="max-width: 80%;" title="{{ $item->name }} {{ $item->tname ? '('.$item->tname.')' : '' }}">
                                                    <span class="badge bg-light text-primary border me-1">{{ $index + 1 }}</span>
                                                    {{ $item->name }}
                                                </div>
                                                <span class="badge bg-primary-subtle text-primary fw-bold font-monospace">{{ number_format($item->sum) }} ราย</span>
                                            </div>
                                            <div class="diag-progress" style="width: {{ round(($item->sum / $maxDiag) * 100) }}%;"></div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 text-muted small">ไม่พบข้อมูล</div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Staff Tab -->
                            <div class="tab-pane fade" id="pills-staff" role="tabpanel">
                                @php
                                    $maxStaff = !empty($top_staff) ? max(array_column($top_staff, 'total_count')) : 1;
                                @endphp
                                <div class="list-group list-group-flush" style="max-height: 330px; overflow-y: auto;">
                                    @forelse($top_staff as $index => $staff)
                                        <div class="diag-item">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="small fw-semibold text-truncate me-2" style="max-width: 80%;">
                                                    <span class="badge bg-light text-secondary border me-1">{{ $index + 1 }}</span>
                                                    {{ $staff->staff_name }}
                                                    <span class="badge bg-light text-muted smaller ms-1">{{ $staff->position_name }}</span>
                                                </div>
                                                <span class="badge bg-success-subtle text-success fw-bold font-monospace">{{ number_format($staff->total_count) }} ราย</span>
                                            </div>
                                            <div class="diag-progress" style="width: {{ round(($staff->total_count / $maxStaff) * 100) }}%; background: rgba(16, 185, 129, 0.15);"></div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 text-muted small">ไม่พบข้อมูล</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient List Card & Filter Box -->
        <div class="card shadow-sm border-0 mb-4 bg-white" style="border-radius: 14px;">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="fas fa-list-ul text-primary me-2"></i>
                            รายชื่อผู้ป่วยที่มารับบริการ (ช่วงเวลา 19.00-08.00 น. ที่ไม่ใช่แพทย์ตรวจ)
                        </h6>
                        <div class="text-muted small">
                            ช่วงวันที่: <span id="displayDateRange" class="fw-semibold text-primary">{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}</span>
                        </div>
                    </div>

                    <!-- Shift Filter Pills -->
                    <div class="d-flex flex-wrap align-items-center gap-1">
                        <button type="button" class="filter-pill-btn active" data-period="all">
                            <i class="fas fa-clock me-1"></i> ทุกช่วงเวลา (19:00-08:00)
                        </button>
                        <button type="button" class="filter-pill-btn" data-period="evening">
                            <i class="fas fa-moon text-warning me-1"></i> 19:00-24:00
                        </button>
                        <button type="button" class="filter-pill-btn" data-period="night">
                            <i class="fas fa-cloud-moon text-primary me-1"></i> 00:00-08:00
                        </button>
                    </div>
                </div>

                <!-- Secondary Filter Bar -->
                <div class="row g-2 mt-2 align-items-center pt-2 border-top">
                    <!-- Date Pickers -->
                    <div class="col-12 col-md-auto d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 145px;">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" id="table_start_date" class="form-control form-control-sm border-start-0" value="{{ $table_start_date }}" placeholder="วันที่เริ่มต้น">
                        </div>
                        <span class="text-muted small">ถึง</span>
                        <div class="input-group input-group-sm" style="width: 145px;">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" id="table_end_date" class="form-control form-control-sm border-start-0" value="{{ $table_end_date }}" placeholder="วันที่สิ้นสุด">
                        </div>
                    </div>

                    <!-- Quick Date Buttons -->
                    <div class="col-12 col-md-auto d-flex gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnToday" style="font-size: 0.75rem;">วันนี้</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnYesterday" style="font-size: 0.75rem;">เมื่อวาน</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCurrentMonth" style="font-size: 0.75rem;">เดือนนี้</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnPrevMonth" style="font-size: 0.75rem;">เดือนก่อน</button>
                    </div>

                    <!-- Staff Position Filter -->
                    <div class="col-12 col-md-auto">
                        <select class="form-select form-select-sm" id="position_filter" style="width: 170px; font-size: 0.8rem;">
                            <option value="all">-- ทุกตำแหน่งผู้ตรวจ --</option>
                            @foreach($position_types as $ptype)
                                <option value="{{ $ptype->position_id }}">
                                    {{ $ptype->position_name }} ({{ number_format($ptype->total_count) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="col-12 col-md-auto ms-auto">
                        <button type="button" id="btnFilterTable" class="btn btn-primary btn-sm px-3 shadow-sm" style="font-weight: 600;">
                            <i class="fas fa-search me-1"></i> ค้นหา
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div class="card-body p-3" id="tableContainer">
                @include('hosxp.er.partials._table_non_doctor', ['patient_list' => $patient_list])
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.print.min.js') }}"></script>

        <script>
            $(document).ready(function() {
                let currentPeriod = 'all';
                let currentPosition = 'all';

                function initPatientTable() {
                    if ($.fn.DataTable.isDataTable('#table-non-doctor-list')) {
                        $('#table-non-doctor-list').DataTable().destroy();
                    }

                    $('#table-non-doctor-list').DataTable({
                        dom: '<"d-flex flex-wrap justify-content-between align-items-center mb-3"<"d-flex align-items-center"lB><"d-flex align-items-center"f>>rt<"d-flex flex-wrap justify-content-between align-items-center mt-3"ip>',
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fas fa-file-excel me-1"></i> Excel',
                                className: 'btn btn-success btn-sm me-1',
                                title: 'รายงานคนไข้รับบริการ 19.00-08.00 น. ที่ไม่ใช่แพทย์ตรวจ ({{ $table_start_date }} ถึง {{ $table_end_date }})'
                            },
                            {
                                extend: 'print',
                                text: '<i class="fas fa-print me-1"></i> พิมพ์',
                                className: 'btn btn-secondary btn-sm me-1'
                            },
                            {
                                extend: 'copyHtml5',
                                text: '<i class="fas fa-copy me-1"></i> Copy',
                                className: 'btn btn-info btn-sm text-white'
                            }
                        ],
                        language: {
                            search: "ค้นหา:",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            infoEmpty: "แสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
                            infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
                            zeroRecords: "ไม่พบข้อมูลที่ตรงกับการค้นหา",
                            paginate: {
                                first: "แรก",
                                last: "ท้าย",
                                previous: "ก่อนหน้า",
                                next: "ถัดไป"
                            }
                        },
                        pageLength: 10,
                        responsive: true,
                        order: [[1, 'desc'], [2, 'desc']]
                    });
                }

                // Initialize Flatpickr
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

                function loadTableData(startDate, endDate) {
                    const $btn = $('#btnFilterTable');
                    const originalText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> กำลังโหลด...');
                    $('#tableContainer').css('opacity', '0.5');

                    $.ajax({
                        url: "{{ route('hosxp.er.non_doctor') }}",
                        method: 'GET',
                        data: {
                            budget_year: "{{ $budget_year }}",
                            table_start_date: startDate,
                            table_end_date: endDate,
                            period_filter: currentPeriod,
                            position_filter: $('#position_filter').val()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success && res.html) {
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

                // Filter Pill click
                $('.filter-pill-btn').on('click', function() {
                    $('.filter-pill-btn').removeClass('active');
                    $(this).addClass('active');
                    currentPeriod = $(this).data('period');
                    const s = $('#table_start_date').val();
                    const e = $('#table_end_date').val();
                    loadTableData(s, e);
                });

                // Position dropdown change
                $('#position_filter').on('change', function() {
                    const s = $('#table_start_date').val();
                    const e = $('#table_end_date').val();
                    loadTableData(s, e);
                });

                // Search button click
                $('#btnFilterTable').on('click', function() {
                    const s = $('#table_start_date').val();
                    const e = $('#table_end_date').val();
                    loadTableData(s, e);
                });

                // Quick Date Buttons
                $('#btnToday').on('click', function() {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const d = String(now.getDate()).padStart(2, '0');
                    const s = `${y}-${m}-${d}`;

                    if (tableStartPicker) tableStartPicker.setDate(s, true);
                    if (tableEndPicker) tableEndPicker.setDate(s, true);
                    loadTableData(s, s);
                });

                $('#btnYesterday').on('click', function() {
                    const yesterday = new Date();
                    yesterday.setDate(yesterday.getDate() - 1);
                    const y = yesterday.getFullYear();
                    const m = String(yesterday.getMonth() + 1).padStart(2, '0');
                    const d = String(yesterday.getDate()).padStart(2, '0');
                    const s = `${y}-${m}-${d}`;

                    if (tableStartPicker) tableStartPicker.setDate(s, true);
                    if (tableEndPicker) tableEndPicker.setDate(s, true);
                    loadTableData(s, s);
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
                    loadTableData(s, e);
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
                    loadTableData(s, e);
                });

                initPatientTable();

                // Monthly Trend Chart (Stacked Column Chart)
                const monthlyCategories = {!! json_encode(array_column($monthly_stats, 'month_year')) !!};
                const eveningData = {!! json_encode(array_map('intval', array_column($monthly_stats, 'evening_cases'))) !!};
                const nightData = {!! json_encode(array_map('intval', array_column($monthly_stats, 'night_cases'))) !!};

                const chartOptions = {
                    series: [
                        { name: 'เวร 19:00 - 24:00 น.', data: eveningData },
                        { name: 'เวร 00:00 - 08:00 น.', data: nightData }
                    ],
                    chart: {
                        type: 'bar',
                        height: 330,
                        stacked: true,
                        toolbar: { show: true }
                    },
                    colors: ['#f59e0b', '#4f46e5'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            borderRadius: 4,
                            columnWidth: '45%',
                            dataLabels: {
                                total: {
                                    enabled: true,
                                    style: { fontSize: '11px', fontWeight: 700 }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 1,
                        colors: ['#fff']
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
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString() + " ราย";
                            }
                        }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#chart-monthly-trend"), chartOptions);
                chart.render();
            });
        </script>
    @endpush
@endsection
