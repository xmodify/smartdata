@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
    <style>
        .page-header-container {
            background: #f8fbfd;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            border: 1px solid #e3eef5;
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

        .card-ipd {
            border-radius: 16px;
            border: 1px solid #e3eef5 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            background: #fff;
            overflow: hidden;
        }

        /* Override DataTables UI to match premium look */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            outline: none !important;
            font-size: 0.8rem !important;
        }

        .dt-buttons .btn-success {
            background-color: #1d6f42 !important;
            border-color: #1d6f42 !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            font-size: 0.75rem !important;
            padding: 0.25rem 0.6rem !important;
            margin-right: 5px;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(29, 111, 66, 0.1) !important;
        }

        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            border-bottom: 2px solid #e3e6f0 !important;
            font-size: 0.85rem !important;
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
                        <i class="fas fa-bed text-success me-2"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ปีงบประมาณ {{ $budget_year }} | ระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls" id="filterForm">
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-calendar-alt"></i></span>
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
                        <button type="submit" class="btn btn-success px-3">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #4e73df !important; border-radius: 12px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-chart-line me-2 text-primary"></i> จำนวนเคส Re-Admit 28 วัน แยกตามรายเดือน
                        </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="readmitMonthlyChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #1cc88a !important; border-radius: 12px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-list-ol me-2 text-success"></i> 10 อันดับโรค Re-Admit สูงสุด
                        </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="topDiagChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="card card-ipd shadow-sm mb-5" style="border-top: 4px solid #36b9cc !important; border-radius: 12px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-table me-2 text-info"></i> รายชื่อผู้ป่วย Re-Admit ภายใน 28 วันด้วยโรคเดิม
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="readmitTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead>
                            <tr class="bg-light">
                                <th class="text-center" style="width: 50px;">ลำดับ</th>
                                <th class="text-center">HN</th>
                                <th>ชื่อ-สกุล</th>
                                <th class="text-center">AN ใหม่</th>
                                <th class="text-center">วันที่ Admit ใหม่</th>
                                <th class="text-center">AN เก่า</th>
                                <th class="text-center">วันที่ Admit เก่า</th>
                                <th class="text-center">วันที่ จำหน่ายเก่า</th>
                                <th class="text-center">โรคหลัก (ICD-10)</th>
                                <th>ชื่อโรค</th>
                                <th class="text-center text-danger fw-bold">ระยะห่าง (วัน)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($patients as $row)
                                <tr>
                                    <td class="text-center text-muted" style="font-size: 0.85rem;">{{ $loop->iteration }}</td>
                                    <td class="text-center" style="font-size: 0.85rem;">{{ $row->hn }}</td>
                                    <td class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $row->ptname }}</td>
                                    <td class="text-center text-primary fw-bold" style="font-size: 0.85rem;">{{ $row->AN_new }}</td>
                                    <td class="text-center" style="font-size: 0.85rem;">{{ DateThai($row->regdate_AN_New) }}</td>
                                    <td class="text-center text-secondary" style="font-size: 0.85rem;">{{ $row->AN_old }}</td>
                                    <td class="text-center" style="font-size: 0.85rem;">{{ DateThai($row->regdate_AN_Old) }}</td>
                                    <td class="text-center" style="font-size: 0.85rem;">{{ DateThai($row->dcdate_AN_Old) }}</td>
                                    <td class="text-center fw-bold text-danger" style="font-size: 0.85rem;">{{ $row->icd10_1 }}</td>
                                    <td style="font-size: 0.85rem;">{{ $row->icd_name }}</td>
                                    <td class="text-center text-danger fw-bold" style="font-size: 0.88rem;">{{ $row->ReAdmitDate }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

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
            if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
                        allowInput: true,
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
                                const originalValue = instance.altInput.value;
                                if (originalValue) {
                                    const date = instance.selectedDates[0] || new Date(instance.input.value);
                                    if (date && !isNaN(date.getTime())) {
                                        const day = date.getDate();
                                        const month = instance.l10n.months.shorthand[date.getMonth()];
                                        const year = date.getFullYear() + yearOffset;
                                        instance.altInput.value = `${day} ${month} ${year}`;
                                    }
                                }
                            }
                        },
                        onChange: function(selectedDates, dateStr, instance) {
                            if (instance.altInput && selectedDates.length > 0) {
                                const date = selectedDates[0];
                                const day = date.getDate();
                                const month = instance.l10n.months.shorthand[date.getMonth()];
                                const year = date.getFullYear() + yearOffset;
                                setTimeout(() => {
                                    instance.altInput.value = `${day} ${month} ${year}`;
                                }, 10);
                            }
                        }
                    };

                    const startPicker = flatpickr("#start_date", commonConfig);
                    const endPicker = flatpickr("#end_date", commonConfig);

                $('select[name="budget_year"]').on('change', function() {
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
            }

            // Initialize DataTable
            $('#readmitTable').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-3"l<"d-flex align-items-center gap-2"fB>>rtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success btn-sm m-0',
                    title: '{{ $title }}',
                    messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
                }],
                pageLength: 10,
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    paginate: {
                        first: "หน้าแรก",
                        last: "หน้าสุดท้าย",
                        next: "ถัดไป",
                        previous: "ก่อนหน้า"
                    }
                },
                ordering: true,
                order: [],
                responsive: true
            });

            // 1. Monthly Chart Setup
            const months = @json(array_column($monthly_stats, 'month_year'));
            const readmitCounts = @json(array_map('intval', array_column($monthly_stats, 'total_readmit')));

            var monthlyChartOptions = {
                series: [{
                    name: 'จำนวนเคส Re-Admit',
                    data: readmitCounts
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '40%',
                        borderRadius: 4,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                colors: ['#4e73df'],
                dataLabels: {
                    enabled: true,
                    formatter: function (val) { return val; },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                xaxis: {
                    categories: months,
                    position: 'bottom',
                },
                yaxis: {
                    title: { text: 'จำนวนผู้ป่วย (ราย)' }
                },
                fill: { opacity: 1 },
                tooltip: {
                    y: {
                        formatter: function (val) { return val + " ราย"; }
                    }
                }
            };
            var monthlyChart = new ApexCharts(document.querySelector("#readmitMonthlyChart"), monthlyChartOptions);
            monthlyChart.render();

            // 2. Top 10 Diagnoses Chart Setup
            const diags = @json(array_column($top_diagnoses, 'icd10'));
            const diagCounts = @json(array_map('intval', array_column($top_diagnoses, 'total_readmit')));

            var diagChartOptions = {
                series: [{
                    name: 'จำนวนเคส',
                    data: diagCounts
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '60%',
                        borderRadius: 4,
                    }
                },
                colors: ['#1cc88a'],
                xaxis: {
                    categories: diags,
                },
                yaxis: {
                    title: { text: 'รหัสโรค (ICD-10)' }
                },
                tooltip: {
                    y: {
                        formatter: function (val) { return val + " ราย"; }
                    }
                }
            };
            var diagChart = new ApexCharts(document.querySelector("#topDiagChart"), diagChartOptions);
            diagChart.render();
        });
    </script>
@endpush
