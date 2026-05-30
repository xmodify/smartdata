@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
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
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }

        .report-title-box h5 {
            font-size: 1.1rem;
            letter-spacing: -0.01em;
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

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            color: #4e73df;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }

        .flatpickr-today-button:hover {
            background: #f8f9fc;
            color: #2e59d9;
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

        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #e3e6f0 !important;
            font-size: 0.85rem !important;
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
                        <i class="fas fa-clock text-primary me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-primary small fw-bold mt-1">
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
                        <span class="input-group-text bg-white border-end-0 text-primary"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i
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
                        <button type="submit" class="btn btn-primary px-3" style="font-size: 0.8rem;">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Chart Container -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; border-top: 4px solid #4e73df !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-bar me-2 text-primary"></i> กราฟแสดงสัดส่วนระยะเวลารอคอยเฉลี่ยรายเดือน (หน่วย: นาที)</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="waitChart" style="min-height: 380px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="card border-0 shadow-sm mb-5" style="border-radius: 15px; border-top: 4px solid #1cc88a !important;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-success"></i> ตารางวิเคราะห์ระยะเวลารอคอยเฉลี่ย (หน่วย: hh:mm:ss)</h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="reportTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">เดือน</th>
                                <th class="text-center text-primary">รอซักประวัติ</th>
                                <th class="text-center text-info">ซักประวัติ</th>
                                <th class="text-center text-warning">รอตรวจ</th>
                                <th class="text-center text-success">แพทย์ตรวจ</th>
                                <th class="text-center text-purple">รอรับยา</th>
                                <th class="text-center fw-bold text-danger">รวมทั้งหมด</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthly_stats as $row)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $row->month }}</td>
                                    <td class="text-center">{{ $row->screen_wait }}</td>
                                    <td class="text-center">{{ $row->screen_success }}</td>
                                    <td class="text-center">{{ $row->doctor_wait }}</td>
                                    <td class="text-center">{{ $row->doctor_success }}</td>
                                    <td class="text-center">{{ $row->rx_success }}</td>
                                    <td class="text-center fw-bold text-danger">{{ $row->success_all }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light fw-bold" style="border-top: 2px solid #dee2e6;">
                                <td class="ps-4">{{ $summary_stats->month }}</td>
                                <td class="text-center text-primary">{{ $summary_stats->screen_wait }}</td>
                                <td class="text-center text-info">{{ $summary_stats->screen_success }}</td>
                                <td class="text-center text-warning">{{ $summary_stats->doctor_wait }}</td>
                                <td class="text-center text-success">{{ $summary_stats->doctor_success }}</td>
                                <td class="text-center text-purple">{{ $summary_stats->rx_success }}</td>
                                <td class="text-center text-danger">{{ $summary_stats->success_all }}</td>
                            </tr>
                        </tfoot>
                    </table>
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
                $('#reportTable').DataTable({
                    dom: '<"d-flex justify-content-end mb-3"B>rt',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: '{{ $title }}',
                        messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
                    }],
                    paging: false,
                    info: false,
                    searching: false,
                    ordering: true,
                    order: [],
                    responsive: true
                });

                // ApexCharts stacked column chart
                const labels = @json(array_column($monthly_stats, 'month'));
                
                var chartOptions = {
                    series: [
                        { name: 'รอซักประวัติ', data: @json(array_column($monthly_stats, 'screen_wait_min')) },
                        { name: 'ซักประวัติ', data: @json(array_column($monthly_stats, 'screen_success_min')) },
                        { name: 'รอตรวจ', data: @json(array_column($monthly_stats, 'doctor_wait_min')) },
                        { name: 'แพทย์ตรวจ', data: @json(array_column($monthly_stats, 'doctor_success_min')) },
                        { name: 'รอรับยา', data: @json(array_column($monthly_stats, 'rx_success_min')) }
                    ],
                    chart: {
                        type: 'bar',
                        height: 380,
                        stacked: true,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 4
                        },
                    },
                    colors: ['#4e73df', '#36b9cc', '#f6c23e', '#1cc88a', '#8f5fe8'],
                    xaxis: {
                        categories: labels,
                    },
                    yaxis: {
                        title: { text: 'นาที' }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val + " นาที";
                            }
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#waitChart"), chartOptions);
                chart.render();
            });
        </script>
    @endpush
@endsection
