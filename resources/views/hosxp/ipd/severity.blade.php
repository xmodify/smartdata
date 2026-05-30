@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

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
                    <input type="hidden" name="tab" value="{{ $tab }}">
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

        <!-- Sub-tabs for Severity Report (Total / General / VIP) -->
        <div class="d-flex mb-3 align-items-center" style="gap: 8px;">
            <span class="small fw-bold text-muted me-2"><i class="fas fa-filter"></i> ตัวกรองระดับ:</span>
            <a href="?tab=total&start_date={{ $start_date }}&end_date={{ $end_date }}&budget_year={{ $budget_year }}" 
               class="btn btn-sm btn-outline-secondary px-3 py-1 fw-bold rounded-pill {{ $tab === 'total' ? 'active btn-secondary text-white' : '' }}" style="font-size: 0.78rem;">
                ทั้งหมด (รวม)
            </a>
            <a href="?tab=general&start_date={{ $start_date }}&end_date={{ $end_date }}&budget_year={{ $budget_year }}" 
               class="btn btn-sm btn-outline-primary px-3 py-1 fw-bold rounded-pill {{ $tab === 'general' ? 'active btn-primary text-white' : '' }}" style="font-size: 0.78rem;">
                สามัญ
            </a>
            <a href="?tab=vip&start_date={{ $start_date }}&end_date={{ $end_date }}&budget_year={{ $budget_year }}" 
               class="btn btn-sm btn-outline-success px-3 py-1 fw-bold rounded-pill {{ $tab === 'vip' ? 'active btn-success text-white' : '' }}" style="font-size: 0.78rem;">
                VIP
            </a>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #4e73df !important; border-radius: 12px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-sign-in-alt me-2 text-primary"></i> กราฟสัดส่วนระดับความรุนแรงตอน "แรกรับ" (Admit Severity - {{ $tab_title }})
                        </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="admitSevereChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #1cc88a !important; border-radius: 12px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-sign-out-alt me-2 text-success"></i> กราฟสัดส่วนระดับความรุนแรงตอน "จำหน่าย" (Discharge Severity - {{ $tab_title }})
                        </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="dchSevereChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="card card-ipd shadow-sm mb-5" style="border-top: 4px solid #36b9cc !important; border-radius: 12px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-table me-2 text-info"></i> ตารางวิเคราะห์ระดับความรุนแรงรายเดือน ({{ $tab_title }})
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="severityTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead>
                            <tr class="bg-light">
                                <th rowspan="2" class="align-middle text-center ps-4" style="border-bottom: 2px solid #dee2e6;">เดือน</th>
                                <th colspan="5" class="text-center text-primary" style="border-bottom: 2px solid #dee2e6; background-color: #f0f4ff !important;">ระดับความรุนแรงแรกรับ (Admit)</th>
                                <th colspan="5" class="text-center text-success" style="border-bottom: 2px solid #dee2e6; background-color: #eafaf1 !important;">ระดับความรุนแรงจำหน่าย (Discharge)</th>
                                <th rowspan="2" class="align-middle text-center fw-bold text-dark" style="border-bottom: 2px solid #dee2e6;">ผู้ป่วยทั้งหมด</th>
                            </tr>
                            <tr class="bg-light">
                                <th class="text-center text-primary" style="font-size: 0.8rem; background-color: #f0f4ff !important;">Convalescent</th>
                                <th class="text-center text-primary" style="font-size: 0.8rem; background-color: #f0f4ff !important;">Moderate</th>
                                <th class="text-center text-primary" style="font-size: 0.8rem; background-color: #f0f4ff !important;">Semi-critical</th>
                                <th class="text-center text-primary" style="font-size: 0.8rem; background-color: #f0f4ff !important;">Critical</th>
                                <th class="text-center text-muted" style="font-size: 0.8rem; background-color: #f0f4ff !important;">ไม่ระบุ</th>
                                
                                <th class="text-center text-success" style="font-size: 0.8rem; background-color: #eafaf1 !important;">Convalescent</th>
                                <th class="text-center text-success" style="font-size: 0.8rem; background-color: #eafaf1 !important;">Moderate</th>
                                <th class="text-center text-success" style="font-size: 0.8rem; background-color: #eafaf1 !important;">Semi-critical</th>
                                <th class="text-center text-success" style="font-size: 0.8rem; background-color: #eafaf1 !important;">Critical</th>
                                <th class="text-center text-muted" style="font-size: 0.8rem; background-color: #eafaf1 !important;">ไม่ระบุ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sum_admit_1 = 0; $sum_admit_2 = 0; $sum_admit_3 = 0; $sum_admit_4 = 0; $sum_admit_null = 0;
                                $sum_dch_1 = 0; $sum_dch_2 = 0; $sum_dch_3 = 0; $sum_dch_4 = 0; $sum_dch_null = 0;
                                $sum_total = 0;
                            @endphp
                            @foreach ($results as $row)
                                @php
                                    $sum_admit_1 += $row->admit_1;
                                    $sum_admit_2 += $row->admit_2;
                                    $sum_admit_3 += $row->admit_3;
                                    $sum_admit_4 += $row->admit_4;
                                    $sum_admit_null += $row->admit_null;
                                    $sum_dch_1 += $row->dch_1;
                                    $sum_dch_2 += $row->dch_2;
                                    $sum_dch_3 += $row->dch_3;
                                    $sum_dch_4 += $row->dch_4;
                                    $sum_dch_null += $row->dch_null;
                                    $sum_total += $row->total_patients;
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $row->month_year }}</td>
                                    <td class="text-center" style="background-color: #fcfdfe !important;">{{ number_format($row->admit_1) }}</td>
                                    <td class="text-center" style="background-color: #fcfdfe !important;">{{ number_format($row->admit_2) }}</td>
                                    <td class="text-center" style="background-color: #fcfdfe !important;">{{ number_format($row->admit_3) }}</td>
                                    <td class="text-center" style="background-color: #fcfdfe !important;">{{ number_format($row->admit_4) }}</td>
                                    <td class="text-center text-muted" style="background-color: #fcfdfe !important;">{{ number_format($row->admit_null) }}</td>
                                    
                                    <td class="text-center" style="background-color: #fdfefd !important;">{{ number_format($row->dch_1) }}</td>
                                    <td class="text-center" style="background-color: #fdfefd !important;">{{ number_format($row->dch_2) }}</td>
                                    <td class="text-center" style="background-color: #fdfefd !important;">{{ number_format($row->dch_3) }}</td>
                                    <td class="text-center" style="background-color: #fdfefd !important;">{{ number_format($row->dch_4) }}</td>
                                    <td class="text-center text-muted" style="background-color: #fdfefd !important;">{{ number_format($row->dch_null) }}</td>
                                    
                                    <td class="text-center fw-bold text-info">{{ number_format($row->total_patients) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light fw-bold" style="border-top: 2px solid #dee2e6;">
                                <td class="ps-4 text-dark">รวมทั้งหมด</td>
                                <td class="text-center text-primary">{{ number_format($sum_admit_1) }}</td>
                                <td class="text-center text-primary">{{ number_format($sum_admit_2) }}</td>
                                <td class="text-center text-primary">{{ number_format($sum_admit_3) }}</td>
                                <td class="text-center text-primary">{{ number_format($sum_admit_4) }}</td>
                                <td class="text-center text-muted">{{ number_format($sum_admit_null) }}</td>
                                
                                <td class="text-center text-success">{{ number_format($sum_dch_1) }}</td>
                                <td class="text-center text-success">{{ number_format($sum_dch_2) }}</td>
                                <td class="text-center text-success">{{ number_format($sum_dch_3) }}</td>
                                <td class="text-center text-success">{{ number_format($sum_dch_4) }}</td>
                                <td class="text-center text-muted">{{ number_format($sum_dch_null) }}</td>
                                
                                <td class="text-center text-info">{{ number_format($sum_total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
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
            border-radius: 8px !important;
            font-size: 0.8rem !important;
            padding: 0.4rem 1rem !important;
            margin-right: 5px;
            font-weight: 600 !important;
            box-shadow: 0 4px 6px rgba(29, 111, 66, 0.1) !important;
        }

        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            border-bottom: 2px solid #e3e6f0 !important;
            font-size: 0.85rem !important;
        }
    </style>
@endpush

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
            $('#severityTable').DataTable({
                dom: '<"d-flex justify-content-end mb-3"B>rt',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success btn-sm m-0',
                    title: '{{ $title }} ({{ $tab_title }})',
                    messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
                }],
                paging: false,
                info: false,
                searching: false,
                ordering: true,
                order: [],
                responsive: true
            });

            // ApexCharts Setup
            const months = @json(array_column($results, 'month_year'));
            
            // 1. Admit Chart Options
            var admitChartOptions = {
                series: [
                    { name: 'Convalescent', data: @json(array_column($results, 'admit_1')) },
                    { name: 'Moderate', data: @json(array_column($results, 'admit_2')) },
                    { name: 'Semi-critical', data: @json(array_column($results, 'admit_3')) },
                    { name: 'Critical', data: @json(array_column($results, 'admit_4')) },
                    { name: 'ไม่ระบุ', data: @json(array_column($results, 'admit_null')) }
                ],
                chart: {
                    type: 'bar',
                    height: 320,
                    stacked: true,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 4
                    },
                },
                colors: ['#4e73df', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                xaxis: {
                    categories: months,
                },
                yaxis: {
                    title: { text: 'จำนวนผู้ป่วย (ราย)' }
                },
                legend: { position: 'bottom' },
                fill: { opacity: 1 },
                tooltip: {
                    y: {
                        formatter: function (val) { return val + " ราย"; }
                    }
                }
            };
            var admitChart = new ApexCharts(document.querySelector("#admitSevereChart"), admitChartOptions);
            admitChart.render();

            // 2. Discharge Chart Options
            var dchChartOptions = {
                series: [
                    { name: 'Convalescent', data: @json(array_column($results, 'dch_1')) },
                    { name: 'Moderate', data: @json(array_column($results, 'dch_2')) },
                    { name: 'Semi-critical', data: @json(array_column($results, 'dch_3')) },
                    { name: 'Critical', data: @json(array_column($results, 'dch_4')) },
                    { name: 'ไม่ระบุ', data: @json(array_column($results, 'dch_null')) }
                ],
                chart: {
                    type: 'bar',
                    height: 320,
                    stacked: true,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '50%',
                        borderRadius: 4
                    },
                },
                colors: ['#1cc88a', '#20c9a6', '#f6c23e', '#e74a3b', '#858796'],
                xaxis: {
                    categories: months,
                },
                yaxis: {
                    title: { text: 'จำนวนผู้ป่วย (ราย)' }
                },
                legend: { position: 'bottom' },
                fill: { opacity: 1 },
                tooltip: {
                    y: {
                        formatter: function (val) { return val + " ราย"; }
                    }
                }
            };
            var dchChart = new ApexCharts(document.querySelector("#dchSevereChart"), dchChartOptions);
            dchChart.render();
        });
    </script>
@endpush
