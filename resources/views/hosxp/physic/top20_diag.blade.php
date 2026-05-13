@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.physic.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #f97316; transition: all 0.3s;">
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

        .text-orange { color: #f97316; }
        .bg-orange { background-color: #f97316 !important; }

        .btn-orange {
            background-color: #f97316 !important;
            border-color: #f97316 !important;
            color: #fff !important;
            transition: all 0.3s;
        }

        .btn-orange:hover {
            background-color: #ea580c !important;
            border-color: #ea580c !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(249, 115, 22, 0.2);
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
            background: #f97316 !important;
            color: white !important;
            border: 1px solid #f97316 !important;
            border-radius: 0.5rem !important;
        }

        table.dataTable thead th {
            background-color: #fff7ed !important;
            color: #f97316 !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #fed7aa !important;
            font-size: 0.85rem !important;
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
                        <i class="fas fa-walking text-orange me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-orange small fw-bold mt-1">
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
                        <span class="input-group-text bg-white border-end-0 text-orange"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-orange"><i
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
                        <button type="submit" class="btn btn-orange text-white px-3" style="font-size: 0.8rem;">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-orange"><i class="fas fa-chart-bar me-2"></i>กราฟแสดง 20 อันดับโรค</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartDiag"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-orange"><i class="fas fa-table me-2"></i>ตารางข้อมูล 20 อันดับโรค</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="tableDiag" class="table table-hover align-middle" style="width:100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-center">อันดับ</th>
                                        <th>ICD10</th>
                                        <th>ชื่อโรค</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-center">ชาย</th>
                                        <th class="text-center">หญิง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($diag_top20 as $index => $row)
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
                                                    class="badge bg-orange bg-opacity-10 text-orange px-2">{{ $row->code }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 300px;" title="{{ $row->name }}">
                                                    {{ $row->name }}
                                                </div>
                                            </td>
                                            <td class="text-center fw-bold text-orange">
                                                {{ number_format($row->sum) }}</td>
                                            <td class="text-center text-primary">{{ number_format($row->male) }}
                                            </td>
                                            <td class="text-center text-danger">{{ number_format($row->female) }}
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

                const diagData = @json($diag_top20);

                // Chart Initialization
                if (diagData.length > 0) {
                    new Chart(document.getElementById('chartDiag'), {
                        type: 'bar',
                        data: {
                            labels: diagData.map(d => {
                                let text = d.name; // Use i.name for chart
                                return text.length > 35 ? text.substring(0, 35) + '...' : text;
                            }),
                            datasets: [{
                                label: 'จำนวน (ครั้ง)',
                                data: diagData.map(d => d.sum),
                                backgroundColor: '#f97316',
                                borderRadius: 4,
                                maxBarThickness: 15
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        title: (ctx) => diagData[ctx[0].dataIndex].name
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'right',
                                    color: '#ea580c',
                                    font: { weight: 'bold', size: 11 },
                                    formatter: Math.round
                                }
                            },
                            scales: {
                                x: {
                                    display: false,
                                    beginAtZero: true,
                                    suggestedMax: Math.max(...diagData.map(d => parseInt(d.sum))) * 1.15
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } }
                                }
                            },
                            layout: { padding: { right: 40 } }
                        }
                    });
                }

                // DataTable Initialization
                $('#tableDiag').DataTable({
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
                        paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                    },
                    pageLength: 10,
                    responsive: true
                });

                // Date Picker and Budget Year Sync
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
                        onReady: function(selectedDates, dateStr, instance) {
                            if (instance.altInput && instance.input.value) {
                                const date = new Date(instance.input.value);
                                const day = date.getDate();
                                const month = instance.l10n.months.shorthand[date.getMonth()];
                                const year = date.getFullYear() + yearOffset;
                                instance.altInput.value = `${day} ${month} ${year}`;
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

                    $('select[name="budget_year"]').on('change', function() {
                        var selectedYear = parseInt($(this).val());
                        if(!isNaN(selectedYear)) {
                            var startYear = selectedYear - 544;
                            var endYear = selectedYear - 543;
                            startPicker.setDate(startYear + "-10-01", true);
                            endPicker.setDate(endYear + "-09-30", true);
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
