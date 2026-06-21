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

        /* Override DataTables UI to match the premium look (skpcard style) */
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
            background: #dc3545 !important;
            color: white !important;
            border: 1px solid #dc3545 !important;
            border-radius: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f8f9fc !important;
            color: #dc3545 !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
        }

        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #dc3545 !important;
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

        .nav-tabs-custom {
            background: #fff;
            border-radius: 12px;
            padding: 0.5rem 0.5rem 0 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            border: 1px solid #f0f0f0;
            margin-bottom: 1.5rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6e707e;
            padding: 0.75rem 1.5rem;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #dc3545;
            background: #f8f9fc;
        }

        .nav-tabs-custom .nav-link.active {
            color: #dc3545;
            background: #f8f9fc;
            border-bottom: 3px solid #dc3545;
        }

        .chart-container {
            position: relative;
            height: 600px;
            width: 100%;
        }

        .flatpickr-today-button {
            padding: 10px;
            text-align: center;
            border-top: 1px solid #e6e6e6;
            cursor: pointer;
            font-weight: bold;
            color: #dc3545;
            background: #f8f9fa;
        }
        .flatpickr-today-button:hover {
            background: #fff5f5;
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
                        <i class="fas fa-ambulance text-danger me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-danger small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง
                        {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-danger"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-danger"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0"
                            value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget"
                        style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-danger px-3" style="font-size: 0.8rem;">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs border-0" id="erTop20Tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="icd10-tab" data-bs-toggle="tab" data-bs-target="#icd10"
                        type="button" role="tab" aria-controls="icd10" aria-selected="true">
                        <i class="fas fa-clipboard-list me-1 text-danger"></i> 20 อันดับโรค (Primary Diagnosis)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="grp504-tab" data-bs-toggle="tab" data-bs-target="#grp504"
                        type="button" role="tab" aria-controls="grp504" aria-selected="false">
                        <i class="fas fa-layer-group me-1 text-warning"></i> 21 กลุ่มสาเหตุ (รง.504)
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="erTop20TabsContent">
            <!-- ICD10 Tab -->
            <div class="tab-pane fade show active" id="icd10" role="tabpanel" aria-labelledby="icd10-tab">
                <div class="row g-4 mb-4">
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartIcd10"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table id="tableIcd10" class="table table-hover align-middle" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>อันดับ</th>
                                                <th>ICD10</th>
                                                <th>ชื่อโรค (อังกฤษ)</th>
                                                <th>ชื่อโรค (ไทย)</th>
                                                <th class="text-center">จำนวน</th>
                                                <th class="text-center">ชาย</th>
                                                <th class="text-center">หญิง</th>
                                                <th class="text-end">ค่า Lab</th>
                                                <th class="text-end">ค่ายา</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($diag_icd10 as $index => $row)
                                                <tr>
                                                    <td class="text-center fw-bold text-muted">
                                                        @if ($index < 3 && $row->sum > 0)
                                                            <span class="badge rounded-pill bg-warning text-dark px-2">
                                                                <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                            </span>
                                                        @else
                                                            {{ $index + 1 }}
                                                        @endif
                                                    </td>
                                                    <td><span
                                                            class="badge bg-danger bg-opacity-10 text-danger px-2">{{ $row->code }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;"
                                                            title="{{ $row->name }}">{{ $row->name }}</div>
                                                    </td>
                                                    <td>
                                                        <div class="text-truncate text-muted small"
                                                            style="max-width: 150px;" title="{{ $row->tname }}">
                                                            {{ $row->tname }}</div>
                                                    </td>
                                                    <td class="text-center fw-bold text-danger">
                                                        {{ number_format($row->sum) }}</td>
                                                    <td class="text-center text-primary">{{ number_format($row->male) }}
                                                    </td>
                                                    <td class="text-center text-danger">{{ number_format($row->female) }}
                                                    </td>
                                                    <td class="text-end">฿{{ number_format($row->inc_lab, 2) }}</td>
                                                    <td class="text-end">฿{{ number_format($row->inc_drug, 2) }}</td>
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

            <!-- 504 Tab -->
            <div class="tab-pane fade" id="grp504" role="tabpanel" aria-labelledby="grp504-tab">
                <div class="row g-4 mb-4">
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chart504"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table id="table504" class="table table-hover align-middle" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>อันดับ</th>
                                                <th>ชื่อตาราง 504</th>
                                                <th class="text-center">จำนวน</th>
                                                <th class="text-center">ชาย</th>
                                                <th class="text-center">หญิง</th>
                                                <th class="text-end">ค่า Lab</th>
                                                <th class="text-end">ค่ายา</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($diag_504 as $index => $row)
                                                <tr>
                                                    <td class="text-center fw-bold text-muted">
                                                        @if ($index < 3 && $row->sum > 0)
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
                                                    <td class="text-center fw-bold text-warning">
                                                        {{ number_format($row->sum) }}</td>
                                                    <td class="text-center text-primary">{{ number_format($row->male) }}
                                                    </td>
                                                    <td class="text-center text-danger">{{ number_format($row->female) }}
                                                    </td>
                                                    <td class="text-end">฿{{ number_format($row->inc_lab, 2) }}</td>
                                                    <td class="text-end">฿{{ number_format($row->inc_drug, 2) }}</td>
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
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
        <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
        <script src="{{ asset('vendor/chartjs/chartjs-plugin-datalabels.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>

        <script>
            $(document).ready(function() {
                Chart.register(ChartDataLabels);

                // --- Data from PHP ---
                const icd10Data = @json($diag_icd10);
                const grp504Data = @json($diag_504);

                // Chart Config Helper
                function createBarChart(ctxId, dataLabel, dataArray, color) {
                    return new Chart(document.getElementById(ctxId), {
                        type: 'bar',
                        data: {
                            labels: dataArray.map(d => {
                                let text = d.tname || d.name;
                                return text.length > 30 ? text.substring(0, 30) + '...' : text;
                            }),
                            datasets: [{
                                label: dataLabel,
                                data: dataArray.map(d => d.sum),
                                backgroundColor: color,
                                borderRadius: 4,
                                maxBarThickness: 15
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'right',
                                    offset: 4,
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    formatter: (val) => val > 0 ? val.toLocaleString() : ''
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10
                                        }
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 10,
                                            weight: 'bold'
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Render Charts
                let chart1 = null;
                let chart2 = null;

                if (icd10Data.length > 0) {
                    chart1 = createBarChart('chartIcd10', 'ผู้ป่วย (คน)', icd10Data, '#dc3545');
                }
                if (grp504Data.length > 0) {
                    chart2 = createBarChart('chart504', 'ผู้ป่วย (คน)', grp504Data, '#ffc107');
                }

                // Fix chart display issue inside hidden tabs on switch
                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    if (chart1) chart1.resize();
                    if (chart2) chart2.resize();
                });

                // Initialize DataTables
                function initTable(tableId) {
                    return $(tableId).DataTable({
                        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                        buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success',
                            title: '{{ $title }}',
                            messageTop: 'ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
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

                const dt1 = initTable('#tableIcd10');
                const dt2 = initTable('#table504');

                // Flatpickr Setup
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

                $('select[name="budget_year"]').on('change', function() {
                    var selectedYear = parseInt($(this).val());
                    if (!isNaN(selectedYear)) {
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
            });
        </script>
    @endpush
@endsection
