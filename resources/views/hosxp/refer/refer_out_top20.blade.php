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
            color: #4e73df;
            background: #f8f9fc;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            background: #f8f9fc;
            border-bottom: 3px solid #4e73df;
        }

        .chart-container {
            position: relative;
            height: 600px;
            width: 100%;
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
                        <i class="fas fa-ranking-star text-primary me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
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

        <!-- Tabs -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs border-0" id="referTop20Tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="er-tab" data-bs-toggle="tab" data-bs-target="#er"
                        type="button" role="tab" aria-controls="er" aria-selected="true">
                        <i class="fas fa-ambulance me-1 text-danger"></i> ER (อุบัติเหตุฉุกเฉิน)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd"
                        type="button" role="tab" aria-controls="opd" aria-selected="false">
                        <i class="fas fa-stethoscope me-1 text-primary"></i> OPD (ผู้ป่วยนอก)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd"
                        type="button" role="tab" aria-controls="ipd" aria-selected="false">
                        <i class="fas fa-bed me-1 text-success"></i> IPD (ผู้ป่วยใน)
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="referTop20TabsContent">
            <!-- ER Tab -->
            <div class="tab-pane fade show active" id="er" role="tabpanel" aria-labelledby="er-tab">
                <div class="row g-4 mb-4">
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h6 class="m-0 fw-bold text-danger"><i class="fas fa-chart-bar me-2"></i>
                                    กราฟอันดับโรคส่งต่อ - ER (Top 20) <span
                                        class="text-muted small fw-normal ms-2">ช่วงวันที่: {{ DateThai($start_date) }} ถึง
                                        {{ DateThai($end_date) }}</span></h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartER"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table id="tableER" class="table table-hover align-middle w-100">
                                        <thead class="bg-light text-danger">
                                            <tr>
                                                <th class="text-center" style="width: 10%">อันดับ</th>
                                                <th style="width: 70%">โรค (Primary Diagnosis)</th>
                                                <th class="text-center" style="width: 20%">จำนวน (ครั้ง)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($diag_top_er as $index => $row)
                                                <tr>
                                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 300px;"
                                                            title="{{ $row->name }}">{{ $row->name }}</div>
                                                    </td>
                                                    <td class="text-center fw-bold">{{ number_format($row->sum) }}</td>
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

            <!-- OPD Tab -->
            <div class="tab-pane fade" id="opd" role="tabpanel" aria-labelledby="opd-tab">
                <div class="row g-4 mb-4">
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i>
                                    กราฟอันดับโรคส่งต่อ - OPD (Top 20) <span
                                        class="text-muted small fw-normal ms-2">ช่วงวันที่: {{ DateThai($start_date) }}
                                        ถึง {{ DateThai($end_date) }}</span></h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartOPD"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table id="tableOPD" class="table table-hover align-middle w-100">
                                        <thead class="bg-light text-primary">
                                            <tr>
                                                <th class="text-center" style="width: 10%">อันดับ</th>
                                                <th style="width: 70%">โรค (Primary Diagnosis)</th>
                                                <th class="text-center" style="width: 20%">จำนวน (ครั้ง)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($diag_top_opd as $index => $row)
                                                <tr>
                                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 300px;"
                                                            title="{{ $row->name }}">{{ $row->name }}</div>
                                                    </td>
                                                    <td class="text-center fw-bold">{{ number_format($row->sum) }}</td>
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

            <!-- IPD Tab -->
            <div class="tab-pane fade" id="ipd" role="tabpanel" aria-labelledby="ipd-tab">
                <div class="row g-4 mb-4">
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                <h6 class="m-0 fw-bold text-success"><i class="fas fa-chart-bar me-2"></i>
                                    กราฟอันดับโรคส่งต่อ - IPD (Top 20) <span
                                        class="text-muted small fw-normal ms-2">ช่วงวันที่: {{ DateThai($start_date) }}
                                        ถึง {{ DateThai($end_date) }}</span></h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chartIPD"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <div class="table-responsive">
                                    <table id="tableIPD" class="table table-hover align-middle w-100">
                                        <thead class="bg-light text-success">
                                            <tr>
                                                <th class="text-center" style="width: 10%">อันดับ</th>
                                                <th style="width: 70%">โรค (Primary Diagnosis)</th>
                                                <th class="text-center" style="width: 20%">จำนวน (ครั้ง)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($diag_top_ipd as $index => $row)
                                                <tr>
                                                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 300px;"
                                                            title="{{ $row->name }}">{{ $row->name }}</div>
                                                    </td>
                                                    <td class="text-center fw-bold">{{ number_format($row->sum) }}</td>
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
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

        <script>
            $(document).ready(function() {
                Chart.register(ChartDataLabels);

                // Date Picker Init
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
                        if (typeof startPicker !== 'undefined') startPicker.clear();
                        if (typeof endPicker !== 'undefined') endPicker.clear();
                        $('#start_date, #end_date').val('');
                    });
                }

                // DataTables Init Function
                function initTable(id) {
                    return $(id).DataTable({
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
                }

                const tableER = initTable('#tableER');
                const tableOPD = initTable('#tableOPD');
                const tableIPD = initTable('#tableIPD');

                // Charts Init Function
                function initChart(id, label, data, color) {
                    const chartData = data.slice(0, 15); // Show top 15 in chart

                    const chartLabels = chartData.map(d => {
                        let name = d.name;
                        if (name.indexOf('] ') > -1) {
                            name = name.split('] ')[1];
                        }
                        return name.length > 20 ? name.substring(0, 20) + '...' : name;
                    });
                    const chartValues = chartData.map(d => d.sum);

                    return new Chart(document.getElementById(id), {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                label: label,
                                data: chartValues,
                                backgroundColor: color + 'A6',
                                borderColor: color,
                                borderWidth: 1,
                                borderRadius: 4
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
                                tooltip: {
                                    callbacks: {
                                        title: function(context) {
                                            return chartData[context[0].dataIndex].name;
                                        }
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'right',
                                    color: '#444',
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    },
                                    formatter: function(value) {
                                        return value.toLocaleString();
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grace: '10%',
                                    grid: {
                                        display: false
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            },
                            layout: {
                                padding: {
                                    right: 40
                                }
                            }
                        }
                    });
                }

                const dataER = @json($diag_top_er);
                const dataOPD = @json($diag_top_opd);
                const dataIPD = @json($diag_top_ipd);

                initChart('chartER', 'ผู้ป่วย ER', dataER, '#e74a3b');
                initChart('chartOPD', 'ผู้ป่วย OPD', dataOPD, '#4e73df');
                initChart('chartIPD', 'ผู้ป่วย IPD', dataIPD, '#1cc88a');
            });
        </script>
    @endpush
@endsection
