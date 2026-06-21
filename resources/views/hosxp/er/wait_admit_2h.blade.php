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

        .card-wait {
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card-wait:hover {
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
            align-items: center !}

        .table-wait tbody td {
            font-size: 0.82rem;
            vertical-align: middle;
        }

        .text-wait-red { color: #e11d48; }
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
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Box -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-clock text-wait-red me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-wait-red small fw-bold mt-1">
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
                        <span class="input-group-text bg-white border-end-0 text-wait-red"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-wait-red"><i
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
            $total_count = count($waitingtime_admit);
            $max_wait = $total_count > 0 ? $waitingtime_admit[0]->time_wait_admit : '00:00:00';
        @endphp

        <!-- Summary Cards -->
        <div class="row mb-4 g-3 justify-content-center">
            <div class="col-md-4">
                <div class="card card-wait shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #dc3545 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-exclamation-circle fa-2x text-danger opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($total_count) }}</h3>
                        <div class="small fw-bold text-danger mt-1">จำนวนผู้ป่วยรอ Admit เกิน 2 ชั่วโมง</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-wait shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #fd7e14 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-stopwatch fa-2x text-warning opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-warning">{{ $max_wait }}</h3>
                        <div class="small fw-bold text-warning mt-1">ระยะเวลารอคอยสูงสุด (ชั่วโมง)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Patient List Table -->
        <div class="row pb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-wait" style="border-radius: 15px;">
                    <div class="card-header bg-light py-3 border-0" style="border-radius: 16px 16px 0 0;">
                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-table me-2"></i>รายชื่อผู้ป่วยที่รอ Admit เกิน 2 ชั่วโมง</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="table-wait-list" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">ลำดับ</th>
                                        <th>วันที่รับบริการ</th>
                                        <th style="width: 5%">คิว</th>
                                        <th>HN</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>AN</th>
                                        <th>เวลาเข้า ER</th>
                                        <th>เวลา Admit</th>
                                        <th>ระยะเวลารอคอย</th>
                                        <th>ประเภทความรุนแรง</th>
                                        <th>แพทย์ ER</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($waitingtime_admit as $index => $row)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="text-center">{{ DateThai($row->vstdate) }}</td>
                                            <td class="text-center fw-bold text-primary">{{ $row->oqueue }}</td>
                                            <td class="text-center">{{ $row->hn }}</td>
                                            <td>{{ $row->ptname }}</td>
                                            <td class="text-center fw-bold">{{ $row->an }}</td>
                                            <td class="text-center">{{ $row->er_time }} น.</td>
                                            <td class="text-center">{{ $row->admit_time }} น.</td>
                                            <td class="text-center fw-bold text-danger">{{ $row->time_wait_admit }} ชม.</td>
                                            <td class="text-center">
                                                @if($row->emergency_type === 'Resuscitate')
                                                    <span class="badge bg-danger">Resuscitate</span>
                                                @elseif($row->emergency_type === 'Emergency')
                                                    <span class="badge bg-orange text-white">Emergency</span>
                                                @elseif($row->emergency_type === 'Urgency')
                                                    <span class="badge bg-warning text-dark">Urgency</span>
                                                @elseif($row->emergency_type === 'Semi_Urgency')
                                                    <span class="badge bg-primary">Semi_Urgency</span>
                                                @elseif($row->emergency_type === 'Non_Urgency')
                                                    <span class="badge bg-success">Non_Urgency</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $row->emergency_type ?: '-' }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $row->er_doctor ?: '-' }}</td>
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

                // Initialize DataTable
                $('#table-wait-list').DataTable({
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
            });
        </script>
    @endpush
@endsection
