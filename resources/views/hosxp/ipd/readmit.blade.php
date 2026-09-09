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
                    <div class="text-muted small mt-1">
                        ปีงบประมาณ {{ $budget_year }} | สถิติภาพรวมประจำปี ({{ DateThai($year_start) }} ถึง {{ DateThai($year_end) }})
                    </div>
                </div>
            </div>

            <!-- Top Controls: Only Budget Year (Controls Charts) -->
            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <div class="input-group input-group-sm shadow-sm" style="width: 230px;">
                        <span class="input-group-text bg-white border-end-0 text-success fw-bold">
                            <i class="fas fa-calendar-alt me-1"></i> เลือก
                        </span>
                        <select class="form-select border-start-0 ps-2" name="budget_year" id="budget_year" onchange="this.form.submit()" style="cursor: pointer;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
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
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-table me-2 text-info"></i> รายชื่อผู้ป่วย Re-Admit ภายใน 28 วันด้วยโรคเดิม
                        </h6>
                        <p class="text-muted small mb-0 mt-1">
                            แสดงข้อมูลตามช่วงวันที่ <span id="displayDateRange" class="fw-bold text-dark">{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}</span>
                        </p>
                    </div>

                    <!-- Table Independent Date Filter Controls -->
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group input-group-sm shadow-sm" style="width: 155px;">
                            <span class="input-group-text bg-white border-end-0 text-info"><i class="fas fa-calendar-day"></i></span>
                            <input type="text" id="table_start_date" class="form-control border-start-0 ps-0" value="{{ $table_start_date }}" placeholder="วันที่เริ่มต้น">
                        </div>
                        <div class="input-group input-group-sm shadow-sm" style="width: 155px;">
                            <span class="input-group-text bg-white border-end-0 text-info"><i class="fas fa-calendar-day"></i></span>
                            <input type="text" id="table_end_date" class="form-control border-start-0 ps-0" value="{{ $table_end_date }}" placeholder="วันที่สิ้นสุด">
                        </div>
                        <button type="button" id="btnFilterTable" class="btn btn-info text-white btn-sm shadow-sm px-3 fw-bold">
                            <i class="fas fa-search me-1"></i> ค้นหา
                        </button>
                        <div class="btn-group btn-group-sm shadow-sm">
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
                @include('hosxp.ipd.partials._table_readmit')
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
            function initPatientTable() {
                if ($.fn.DataTable.isDataTable('#readmitTable')) {
                    $('#readmitTable').DataTable().destroy();
                }
                $('#readmitTable').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"l<"d-flex align-items-center gap-2"fB>>rtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm m-0',
                        title: '{{ $title }}',
                        messageTop: function() {
                            return 'ช่วงวันที่: ' + ($('#displayDateRange').text() || '{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}');
                        }
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

            function loadTableData(startDate, endDate) {
                const $btn = $('#btnFilterTable');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> ค้นหา...');
                $('#tableContainer').css('opacity', '0.5');

                $.ajax({
                    url: "{{ route('hosxp.ipd.readmit') }}",
                    method: 'GET',
                    data: {
                        budget_year: "{{ $budget_year }}",
                        table_start_date: startDate,
                        table_end_date: endDate
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success && res.html) {
                            if ($.fn.DataTable.isDataTable('#readmitTable')) {
                                $('#readmitTable').DataTable().destroy();
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

            $('#btnFilterTable').on('click', function() {
                const s = $('#table_start_date').val();
                const e = $('#table_end_date').val();
                loadTableData(s, e);
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
