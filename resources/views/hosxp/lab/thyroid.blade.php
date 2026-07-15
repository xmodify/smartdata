@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
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
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }

        .report-title-box h4 {
            font-size: 1.1rem;
            letter-spacing: -0.01em;
        }

        .header-form-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
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

        .bg-pastel-blue { background-color: #f0f9ff; }
        .bg-pastel-green { background-color: #f0fdf4; }
        .bg-pastel-purple { background-color: #faf5ff; }
        .bg-pastel-amber { background-color: #fffbeb; }

        .card-stats {
            border-radius: 15px;
            transition: transform 0.2s ease, shadow 0.2s ease;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }

        .text-num {
            font-family: 'Inter', sans-serif;
            text-align: right;
        }

        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
        }

        .table-stats {
            white-space: nowrap;
            font-size: 0.85rem;
        }

        .table-stats th {
            text-align: center;
            vertical-align: middle;
            padding: 10px 15px;
            font-weight: 600;
            background-color: #f8fafc;
        }

        .stat-val {
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .label-small {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
        }

        .chart-container {
            min-height: 350px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Page -->
        <div class="page-header-container d-flex flex-wrap justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-flask text-primary me-2"></i> {{ $title }}
                    </h4>
                    <div class="text-muted small mt-1">สรุปข้อมูลส่งตรวจและค่าบริการทางห้องปฏิบัติการไทรอยด์ ปีงบประมาณ
                        <strong>{{ $budget_year }}</strong>
                    </div>
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

        <!-- Quick Stats Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-blue p-2 rounded-3">
                                <i class="fas fa-clipboard-list text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">จำนวนส่งตรวจทั้งหมด</div>
                        <div class="stat-val text-dark" style="font-size: 1.3rem;">
                            {{ number_format($total_visit) }} <span class="fs-6 fw-normal text-muted">ครั้ง</span>
                            <span class="text-muted mx-1">|</span>
                            {{ number_format($total_hn) }} <span class="fs-6 fw-normal text-muted">คน</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-purple p-2 rounded-3">
                                <i class="fas fa-user-plus text-purple fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">ผู้ป่วยรายใหม่ (New Cases)</div>
                        <div class="stat-val text-dark">{{ number_format($total_new_cases) }} <span
                                class="fs-6 fw-normal text-muted">คน</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-green p-2 rounded-3">
                                <i class="fas fa-hand-holding-usd text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">รายได้ค่าบริการสะสม</div>
                        <div class="stat-val text-dark">{{ number_format($total_income, 2) }} <span
                                class="fs-6 fw-normal text-muted">บาท</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-amber p-2 rounded-3">
                                <i class="fas fa-calculator text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">เฉลี่ยต่อการตรวจ (Visit Cost)</div>
                        <div class="stat-val text-dark">
                            {{ number_format($total_visit > 0 ? $total_income / $total_visit : 0, 2) }} <span
                                class="fs-6 fw-normal text-muted">บาท</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-4">
            <!-- Thyroid Tests Distribution -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">แนวโน้มการสั่งตรวจแล็บไทรอยด์รายเดือน</h5>
                        <p class="text-muted small">จำนวนรายการส่งตรวจ (จำนวนชิ้น) แยกตามประเภทแล็บ 3 รายการหลัก</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="thyroidMonthlyChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <!-- New Cases Trend -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">แนวโน้มผู้ป่วยรายใหม่ (New Cases)</h5>
                        <p class="text-muted small">จำนวนผู้รับบริการรายใหม่ตรวจครั้งแรกในแต่ละเดือน</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="newCasesChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-pastel-blue py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 20px 20px 0 0;">
                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-table me-2"></i>ตารางแสดงรายการสถิติแล็บไทรอยด์แบบละเอียด</h6>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-success px-2 shadow-sm btn-export-excel" data-target="#thyroidStatsTable" style="font-size: 0.75rem; padding: 2px 8px;">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-stats mb-0" id="thyroidStatsTable">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle">เดือน</th>
                                        <th colspan="3" class="bg-pastel-blue border-end">Free T3</th>
                                        <th colspan="3" class="bg-pastel-green border-end">Free T4</th>
                                        <th colspan="3" class="bg-pastel-amber border-end">TSH</th>
                                        <th colspan="4" class="bg-pastel-purple">รวมทั้งหมดในกลุ่ม</th>
                                    </tr>
                                    <tr>
                                        <th class="bg-pastel-blue">จำนวนตรวจ</th>
                                        <th class="bg-pastel-blue text-purple">รายใหม่ (คน)</th>
                                        <th class="bg-pastel-blue border-end">รายได้ (บาท)</th>
                                        
                                        <th class="bg-pastel-green">จำนวนตรวจ</th>
                                        <th class="bg-pastel-green text-purple">รายใหม่ (คน)</th>
                                        <th class="bg-pastel-green border-end">รายได้ (บาท)</th>

                                        <th class="bg-pastel-amber">จำนวนตรวจ</th>
                                        <th class="bg-pastel-amber text-purple">รายใหม่ (คน)</th>
                                        <th class="bg-pastel-amber border-end">รายได้ (บาท)</th>

                                        <th class="bg-pastel-purple">ผู้ป่วย (คน)</th>
                                        <th class="bg-pastel-purple">ส่งตรวจ (ครั้ง)</th>
                                        <th class="bg-pastel-purple">รายใหม่รวม (คน)</th>
                                        <th class="bg-pastel-purple">รายได้รวม (บาท)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthly_stats as $row)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $row->month }}</td>
                                            
                                            <td class="text-num">{{ number_format($row->ft3_qty) }}</td>
                                            <td class="text-num text-purple">{{ number_format($row->ft3_new) }}</td>
                                            <td class="text-num border-end">{{ number_format($row->ft3_income, 2) }}</td>

                                            <td class="text-num">{{ number_format($row->ft4_qty) }}</td>
                                            <td class="text-num text-purple">{{ number_format($row->ft4_new) }}</td>
                                            <td class="text-num border-end">{{ number_format($row->ft4_income, 2) }}</td>

                                            <td class="text-num">{{ number_format($row->tsh_qty) }}</td>
                                            <td class="text-num text-purple">{{ number_format($row->tsh_new) }}</td>
                                            <td class="text-num border-end">{{ number_format($row->tsh_income, 2) }}</td>

                                            <td class="text-num fw-bold text-dark">{{ number_format($row->total_hn) }}</td>
                                            <td class="text-num fw-bold text-primary">{{ number_format($row->total_visit) }}</td>
                                            <td class="text-num fw-bold text-purple">{{ number_format($row->new_cases) }}</td>
                                            <td class="text-num fw-bold text-success">{{ number_format($row->total_income, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold border-top-2">
                                    <tr>
                                        <td class="text-center">รวม</td>
                                        <td class="text-num">{{ number_format(array_sum(array_column($monthly_stats, 'ft3_qty'))) }}</td>
                                        <td class="text-num text-purple">{{ number_format(array_sum(array_column($monthly_stats, 'ft3_new'))) }}</td>
                                        <td class="text-num border-end">{{ number_format(array_sum(array_column($monthly_stats, 'ft3_income')), 2) }}</td>
                                        
                                        <td class="text-num">{{ number_format(array_sum(array_column($monthly_stats, 'ft4_qty'))) }}</td>
                                        <td class="text-num text-purple">{{ number_format(array_sum(array_column($monthly_stats, 'ft4_new'))) }}</td>
                                        <td class="text-num border-end">{{ number_format(array_sum(array_column($monthly_stats, 'ft4_income')), 2) }}</td>
                                        
                                        <td class="text-num">{{ number_format(array_sum(array_column($monthly_stats, 'tsh_qty'))) }}</td>
                                        <td class="text-num text-purple">{{ number_format(array_sum(array_column($monthly_stats, 'tsh_new'))) }}</td>
                                        <td class="text-num border-end">{{ number_format(array_sum(array_column($monthly_stats, 'tsh_income')), 2) }}</td>
                                        
                                        <td class="text-num text-dark">{{ number_format($total_hn) }}</td>
                                        <td class="text-num text-primary">{{ number_format($total_visit) }}</td>
                                        <td class="text-num text-purple">{{ number_format($total_new_cases) }}</td>
                                        <td class="text-num text-success">{{ number_format($total_income, 2) }}</td>
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
        <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
                        allowInput: false,
                        onReady: function(selectedDates, dateStr, instance) {
                            if (instance.altInput) {
                                const date = instance.selectedDates[0] || new Date(instance.input.value);
                                if (date && !isNaN(date.getTime())) {
                                    const day = date.getDate();
                                    const month = instance.l10n.months.shorthand[date.getMonth()];
                                    const year = date.getFullYear() + yearOffset;
                                    instance.altInput.value = `${day} ${month} ${year}`;
                                }
                            }
                            const container = instance.calendarContainer;
                            if (container && !container.querySelector('.flatpickr-today-button')) {
                                const btn = document.createElement("div");
                                btn.className = "flatpickr-today-button";
                                btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                                btn.addEventListener("mousedown", function(e) {
                                    e.preventDefault();
                                    instance.setDate(new Date());
                                    instance.close();
                                });
                                container.appendChild(btn);
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
                            var startDateStr = startYear + "-10-01";
                            var endDateStr = endYear + "-09-30";
                            setTimeout(() => {
                                if (startPicker) startPicker.setDate(startDateStr, true);
                                if (endPicker) endPicker.setDate(endDateStr, true);
                            }, 50);
                        }
                    });
                }

                // Excel Export
                $('.btn-export-excel').on('click', function() {
                    const target = $(this).data('target');
                    const title = $(this).closest('.card-header').find('h6').text().trim();
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

                // Thyroid Monthly Test Count Chart
                const monthlyOptions = {
                    series: [{
                        name: 'Free T3',
                        data: @json($ft3_qtys)
                    }, {
                        name: 'Free T4',
                        data: @json($ft4_qtys)
                    }, {
                        name: 'TSH',
                        data: @json($tsh_qtys)
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        toolbar: { show: false }
                    },
                    colors: ['#3b82f6', '#10b981', '#f59e0b'],
                    plotOptions: {
                        bar: {
                            columnWidth: '60%',
                            borderRadius: 4
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', colors: ['#fff'] },
                        formatter: val => val > 0 ? val.toLocaleString() : ''
                    },
                    xaxis: {
                        categories: @json($months),
                    },
                    yaxis: {
                        labels: { formatter: val => val.toLocaleString() }
                    },
                    legend: { position: 'top' }
                };

                const monthlyChart = new ApexCharts(document.querySelector("#thyroidMonthlyChart"), monthlyOptions);
                monthlyChart.render();

                // New Cases Trend Line Chart
                const newCasesOptions = {
                    series: [{
                        name: 'Free T3 รายใหม่',
                        data: @json($ft3_new_series)
                    }, {
                        name: 'Free T4 รายใหม่',
                        data: @json($ft4_new_series)
                    }, {
                        name: 'TSH รายใหม่',
                        data: @json($tsh_new_series)
                    }],
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: { show: false }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#3b82f6', '#10b981', '#f59e0b'],
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px' }
                    },
                    xaxis: {
                        categories: @json($months),
                    },
                    yaxis: {
                        labels: { formatter: val => Math.round(val).toLocaleString() }
                    },
                    legend: { position: 'top' },
                    markers: { size: 5 }
                };

                const newCasesChart = new ApexCharts(document.querySelector("#newCasesChart"), newCasesOptions);
                newCasesChart.render();
            });
        </script>
    @endpush
@endsection
