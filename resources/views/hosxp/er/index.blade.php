@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #dc3545; transition: all 0.3s;">
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

        .card-er {
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card-er:hover {
            transform: translateY(-5px);
        }

        .table-er thead th {
            vertical-align: middle;
            text-align: center;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.85rem;
            color: #475569;
            font-weight: 600;
        }

        .table-er tbody td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .text-er-red { color: #e11d48; }
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
                        <i class="fas fa-ambulance text-er-red me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-er-red small fw-bold mt-1">
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
                        <span class="input-group-text bg-white border-end-0 text-er-red"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-er-red"><i
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

        <!-- Summary Cards (6 Cards for ER Severity) -->
        <div class="row mb-4 g-3">
            <div class="col-md">
                <div class="card card-er shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #475569 !important;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-users fa-2x text-secondary opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($summary_stats->total_visit ?? 0) }}</h3>
                        <div class="small fw-bold text-muted mt-1">ผู้รับบริการ ER ทั้งหมด</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-er shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #be123c !important;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-heartbeat fa-2x text-danger opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($summary_stats->level_1 ?? 0) }}</h3>
                        <div class="small fw-bold text-danger mt-1">{{ $severity_types[1]->name ?? 'Resuscitate (กู้ชีพ)' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-er shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #f97316 !important;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-exclamation-triangle fa-2x text-warning opacity-50"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #f97316;">{{ number_format($summary_stats->level_2 ?? 0) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #f97316;">{{ $severity_types[2]->name ?? 'Emergency (ฉุกเฉิน)' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-er shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #eab308 !important;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-bell fa-2x text-info opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-warning">{{ number_format($summary_stats->level_3 ?? 0) }}</h3>
                        <div class="small fw-bold text-warning mt-1">{{ $severity_types[3]->name ?? 'Urgency (ด่วนมาก)' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-er shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #3b82f6 !important;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-check-circle fa-2x text-primary opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-primary">{{ number_format($summary_stats->level_4 ?? 0) }}</h3>
                        <div class="small fw-bold text-primary mt-1">{{ $severity_types[4]->name ?? 'Semi Urgency (ด่วน)' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-er shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #10b981 !important;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-smile fa-2x text-success opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($summary_stats->level_5 ?? 0) }}</h3>
                        <div class="small fw-bold text-success mt-1">{{ $severity_types[5]->name ?? 'Non Urgency (รอได้)' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-er">
                    <div class="card-header bg-pastel-red py-3 border-0" style="border-radius: 16px 16px 0 0;">
                        <h6 class="fw-bold mb-0 text-er-red"><i class="fas fa-chart-bar me-2"></i>กราฟสรุปจำนวนผู้รับบริการจำแนกตามระดับความรุนแรง (ER Severity Trends)</h6>
                    </div>
                    <div class="card-body">
                        <div id="chart-er-severity"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="row pb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-er">
                    <div class="card-header bg-pastel-red py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 16px 16px 0 0;">
                        <h6 class="fw-bold mb-0 text-er-red"><i class="fas fa-table me-2"></i>ตารางข้อมูลรายเดือนจำแนกตามระดับความรุนแรง (ER)</h6>
                        <button type="button" class="btn btn-sm btn-success px-2 shadow-sm btn-export-excel" data-target="#table-er" style="font-size: 0.75rem; padding: 2px 8px;">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-er mb-0" id="table-er">
                                <thead>
                                    <tr>
                                        <th>เดือน</th>
                                        <th class="bg-light">รวมทั้งหมด (VN)</th>
                                        <th style="background-color: #ffe4e6; color: #be123c;">{{ $severity_types[1]->name ?? 'Level 1: กู้ชีพ' }}</th>
                                        <th style="background-color: #ffedd5; color: #ea580c;">{{ $severity_types[2]->name ?? 'Level 2: ฉุกเฉิน' }}</th>
                                        <th style="background-color: #fef9c3; color: #ca8a04;">{{ $severity_types[3]->name ?? 'Level 3: ด่วนมาก' }}</th>
                                        <th style="background-color: #dbeafe; color: #2563eb;">{{ $severity_types[4]->name ?? 'Level 4: ด่วน' }}</th>
                                        <th style="background-color: #dcfce7; color: #16a34a;">{{ $severity_types[5]->name ?? 'Level 5: รอได้' }}</th>
                                        <th class="bg-light text-muted">ไม่ระบุระดับ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totals = [
                                            'total_visit' => 0, 'level_1' => 0, 'level_2' => 0,
                                            'level_3' => 0, 'level_4' => 0, 'level_5' => 0, 'level_null' => 0
                                        ];
                                    @endphp
                                    @foreach($monthly_stats as $row)
                                    <tr>
                                        <td class="text-center fw-bold text-dark">{{ $row->month_year }}</td>
                                        <td class="text-center fw-bold text-primary">{{ number_format($row->total_visit) }}</td>
                                        <td class="text-center fw-bold text-danger">{{ number_format($row->level_1) }}</td>
                                        <td class="text-center fw-bold" style="color: #ea580c;">{{ number_format($row->level_2) }}</td>
                                        <td class="text-center fw-bold text-warning">{{ number_format($row->level_3) }}</td>
                                        <td class="text-center fw-bold text-blue">{{ number_format($row->level_4) }}</td>
                                        <td class="text-center fw-bold text-success">{{ number_format($row->level_5) }}</td>
                                        <td class="text-center text-muted">{{ number_format($row->level_null) }}</td>
                                    </tr>
                                    @php
                                        $totals['total_visit'] += $row->total_visit;
                                        $totals['level_1'] += $row->level_1;
                                        $totals['level_2'] += $row->level_2;
                                        $totals['level_3'] += $row->level_3;
                                        $totals['level_4'] += $row->level_4;
                                        $totals['level_5'] += $row->level_5;
                                        $totals['level_null'] += $row->level_null;
                                    @endphp
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td class="text-center">รวมทั้งปี</td>
                                        <td class="text-center text-primary">{{ number_format($totals['total_visit']) }}</td>
                                        <td class="text-center text-danger">{{ number_format($totals['level_1']) }}</td>
                                        <td class="text-center" style="color: #ea580c;">{{ number_format($totals['level_2']) }}</td>
                                        <td class="text-center text-warning">{{ number_format($totals['level_3']) }}</td>
                                        <td class="text-center text-blue">{{ number_format($totals['level_4']) }}</td>
                                        <td class="text-center text-success">{{ number_format($totals['level_5']) }}</td>
                                        <td class="text-center text-muted">{{ number_format($totals['level_null']) }}</td>
                                    </tr>
                                </tfoot>
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
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        
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

                // ApexCharts setup
                const months = {!! json_encode(array_column($monthly_stats, 'month_year')) !!};
                const series_data = [
                    { name: '{{ $severity_types[1]->name ?? "Level 1: กู้ชีพ" }}', data: {!! json_encode(array_map('intval', array_column($monthly_stats, 'level_1'))) !!} },
                    { name: '{{ $severity_types[2]->name ?? "Level 2: ฉุกเฉิน" }}', data: {!! json_encode(array_map('intval', array_column($monthly_stats, 'level_2'))) !!} },
                    { name: '{{ $severity_types[3]->name ?? "Level 3: ด่วนมาก" }}', data: {!! json_encode(array_map('intval', array_column($monthly_stats, 'level_3'))) !!} },
                    { name: '{{ $severity_types[4]->name ?? "Level 4: ด่วน" }}', data: {!! json_encode(array_map('intval', array_column($monthly_stats, 'level_4'))) !!} },
                    { name: '{{ $severity_types[5]->name ?? "Level 5: รอได้" }}', data: {!! json_encode(array_map('intval', array_column($monthly_stats, 'level_5'))) !!} }
                ];

                const chartOptions = {
                    series: series_data,
                    chart: {
                        type: 'bar',
                        height: 380,
                        stacked: true,
                        toolbar: { show: true }
                    },
                    colors: ['#e11d48', '#f97316', '#eab308', '#3b82f6', '#10b981'],
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            borderRadius: 4
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
                        categories: months,
                        labels: { style: { fontSize: '12px', fontWeight: 600 } }
                    },
                    yaxis: {
                        title: { text: 'จำนวนผู้รับบริการ (ครั้ง/VN)', style: { fontWeight: 600 } },
                        labels: { formatter: (val) => val.toLocaleString() }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'center'
                    },
                    fill: {
                        opacity: 1
                    },
                    tooltip: {
                        y: { formatter: (val) => val + " ครั้ง" }
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                };

                const chart = new ApexCharts(document.querySelector("#chart-er-severity"), chartOptions);
                chart.render();

                // Excel Export handler
                $('.btn-export-excel').on('click', function() {
                    const target = $(this).data('target');
                    const title = $(this).prev('h6').text().trim();
                    
                    const dt = $(target).DataTable({
                        destroy: true,
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: false,
                        autoWidth: false,
                        dom: 'tB',
                        buttons: [{
                            extend: 'excelHtml5',
                            title: title,
                            messageTop: 'ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}',
                            filename: title + '_{{ date("Ymd") }}'
                        }]
                    });
                    dt.button(0).trigger();
                    dt.destroy();
                });
            });
        </script>
    @endpush
@endsection
