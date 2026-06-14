@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.phar.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }

        .report-title-box h5 { font-size: 1.1rem; letter-spacing: -0.01em; }
        .header-form-controls { display: flex; align-items: center; gap: 0.5rem; }
        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-budget { width: 100% !important; }
        }

        .nav-tabs-custom { border-bottom: 2px solid #f0f0f0; margin-bottom: 1.5rem; }
        .nav-tabs-custom .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            transition: all 0.3s;
            position: relative;
        }
        .nav-tabs-custom .nav-link#opd-tab.active { color: #10b981; background: transparent; }
        .nav-tabs-custom .nav-link#opd-tab.active::after {
            content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: #10b981;
        }
        .nav-tabs-custom .nav-link#ipd-tab.active { color: #ef4444; background: transparent; }
        .nav-tabs-custom .nav-link#ipd-tab.active::after {
            content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: #ef4444;
        }

        .card-custom { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); }
        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            border-bottom: 1px solid #e2e8f0;
        }

        .text-green { color: #10b981 !important; }
        .text-red { color: #ef4444 !important; }
        .bg-pastel-green { background-color: #ecfdf5 !important; }
        .bg-pastel-red { background-color: #fef2f2 !important; }

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6; padding: 8px; text-align: center; cursor: pointer;
            color: #10b981; font-weight: bold; font-size: 0.9rem; transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }
        .flatpickr-today-button:hover { background: #fdfaff; color: #059669; }
        
        .table-total { background-color: #f8fafc; font-weight: bold; }

        /* DataTables Custom Styling to match Image 2 */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.25rem 0.6rem !important;
            outline: none !important;
            font-size: 0.85rem !important;
            box-shadow: none !important;
        }

        .dataTables_wrapper .dataTables_length select {
            padding-right: 1.5rem !important;
            min-width: 60px !important;
        }
        
        /* Excel Button Styling */
        .dt-buttons .btn-success, .buttons-excel {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
            border-radius: 0.4rem !important;
            font-weight: 500 !important;
            padding: 0.3rem 0.75rem !important;
            font-size: 0.85rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            box-shadow: 0 2px 4px rgba(25, 135, 84, 0.15) !important;
            transition: all 0.2s ease-in-out !important;
        }
        
        .dt-buttons .btn-success:hover, .buttons-excel:hover {
            background-color: #157347 !important;
            border-color: #146c43 !important;
        }

        /* Pagination Styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .page-item.active .page-link {
            background: #4f46e5 !important; /* Royal blue / Indigo */
            color: white !important;
            border-color: #4f46e5 !important;
            border-radius: 0.4rem !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current),
        .page-item:not(.active) .page-link {
            color: #4f46e5 !important;
            background: transparent !important;
            border: 1px solid transparent !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
        .page-link:hover {
            background: #f3f4f6 !important;
            color: #4f46e5 !important;
            border-radius: 0.4rem !important;
            border-color: #dee2e6 !important;
        }
        
        .page-item:first-child .page-link,
        .page-item:last-child .page-link {
            border-radius: 0.4rem !important;
        }
        
        .page-link {
            margin: 0 2px !important;
            border-radius: 0.4rem !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.85rem !important;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0rem !important;
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin-bottom: 0 !important;
            font-size: 0.85rem !important;
        }

        .dt-buttons {
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-file-prescription text-primary me-2"></i> {{ $title }}</h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>{{ $row->LEAVE_YEAR_NAME }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="budget_year_changed" id="budget_year_changed" value="0">
                        <button type="submit" class="btn btn-primary text-white px-3" style="font-size: 0.8rem;"><i class="fas fa-search"></i> ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom" id="prescriptionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd-content" type="button" role="tab"><i class="fas fa-user-nurse me-2"></i>ผู้ป่วยนอก (OPD)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd-content" type="button" role="tab"><i class="fas fa-bed-pulse me-2"></i>ผู้ป่วยใน (IPD)</button>
            </li>
        </ul>

        <div class="tab-content" id="prescriptionTabsContent">
            <!-- OPD Tab -->
            <div class="tab-pane fade show active" id="opd-content" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-chart-line me-2"></i>แนวโน้มจำนวนใบสั่งยา (OPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div id="opdPrescriptionChart" style="min-height: 350px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>สรุปข้อมูลรายเดือน (OPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle text-center" id="opdTable">
                                        <thead>
                                            <tr>
                                                <th>เดือน</th>
                                                <th>จำนวนใบสั่งยา</th>
                                                <th>จำนวนรายการยา</th>
                                                <th>ต้นทุน</th>
                                                <th>มูลค่า</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php 
                                                $total_count = 0; $total_drug = 0; $total_cost = 0; $total_price = 0;
                                            @endphp
                                            @foreach ($prescription_opd as $row)
                                                <tr>
                                                    <td class="fw-bold">{{ $row->month_name }}</td>
                                                    <td>{{ number_format($row->count) }}</td>
                                                    <td>{{ number_format($row->drug_count) }}</td>
                                                    <td class="text-end">{{ number_format($row->sum_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-green">{{ number_format($row->sum_price, 2) }}</td>
                                                </tr>
                                                @php
                                                    $total_count += $row->count; $total_drug += $row->drug_count;
                                                    $total_cost += $row->sum_cost; $total_price += $row->sum_price;
                                                @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-total">
                                            <tr>
                                                <td>รวม</td>
                                                <td>{{ number_format($total_count) }}</td>
                                                <td>{{ number_format($total_drug) }}</td>
                                                <td class="text-end">{{ number_format($total_cost, 2) }}</td>
                                                <td class="text-end text-green">{{ number_format($total_price, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IPD Tab -->
            <div class="tab-pane fade" id="ipd-content" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-chart-line me-2"></i>แนวโน้มจำนวนใบสั่งยา (IPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div id="ipdPrescriptionChart" style="min-height: 350px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-table me-2"></i>สรุปข้อมูลรายเดือน (IPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle text-center" id="ipdTable">
                                        <thead>
                                            <tr>
                                                <th>เดือน</th>
                                                <th>จำนวนใบสั่งยา</th>
                                                <th>จำนวนรายการยา</th>
                                                <th>ต้นทุน</th>
                                                <th>มูลค่า</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php 
                                                $total_count = 0; $total_drug = 0; $total_cost = 0; $total_price = 0;
                                            @endphp
                                            @foreach ($prescription_ipd as $row)
                                                <tr>
                                                    <td class="fw-bold">{{ $row->month_name }}</td>
                                                    <td>{{ number_format($row->count) }}</td>
                                                    <td>{{ number_format($row->drug_count) }}</td>
                                                    <td class="text-end">{{ number_format($row->sum_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-red">{{ number_format($row->sum_price, 2) }}</td>
                                                </tr>
                                                @php
                                                    $total_count += $row->count; $total_drug += $row->drug_count;
                                                    $total_cost += $row->sum_cost; $total_price += $row->sum_price;
                                                @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-total">
                                            <tr>
                                                <td>รวม</td>
                                                <td>{{ number_format($total_count) }}</td>
                                                <td>{{ number_format($total_drug) }}</td>
                                                <td class="text-end">{{ number_format($total_cost, 2) }}</td>
                                                <td class="text-end text-red">{{ number_format($total_price, 2) }}</td>
                                            </tr>
                                        </tfoot>
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
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
        <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>

        <script>
            $(document).ready(function() {
                const chartOptions = {
                    chart: { height: 350, toolbar: { show: false }, zoom: { enabled: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    yaxis: { labels: { formatter: function(val) { return val.toLocaleString(); } } },
                    tooltip: { x: { show: true }, y: { formatter: function(val) { return val.toLocaleString(); } } }
                };

                // OPD Chart
                const opdChart = new ApexCharts(document.querySelector("#opdPrescriptionChart"), {
                    ...chartOptions,
                    series: [{ name: 'ใบสั่งยา', data: @json(array_column($prescription_opd, 'count')) }],
                    xaxis: { categories: @json(array_column($prescription_opd, 'month_name')) },
                    colors: ['#10b981'],
                });
                opdChart.render();

                // IPD Chart
                const ipdChart = new ApexCharts(document.querySelector("#ipdPrescriptionChart"), {
                    ...chartOptions,
                    series: [{ name: 'ใบสั่งยา', data: @json(array_column($prescription_ipd, 'count')) }],
                    xaxis: { categories: @json(array_column($prescription_ipd, 'month_name')) },
                    colors: ['#ef4444'],
                });
                ipdChart.render();

                // DataTables
                const opdTable = $('#opdTable').DataTable({
                    paging: false, info: false, searching: false,
                    dom: '<"d-flex justify-content-end align-items-center mb-3"B>rt',
                    buttons: [{
                        extend: 'excelHtml5', text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm', title: 'จำนวนใบสั่งยา (OPD)'
                    }]
                });
                const ipdTable = $('#ipdTable').DataTable({
                    paging: false, info: false, searching: false,
                    dom: '<"d-flex justify-content-end align-items-center mb-3"B>rt',
                    buttons: [{
                        extend: 'excelHtml5', text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm', title: 'จำนวนใบสั่งยา (IPD)'
                    }]
                }).container().appendTo($('#ipdExportBtn'));

                // Flatpickr & Budget Year sync (Reuse logic from top20)
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th", dateFormat: "Y-m-d", altInput: true, altFormat: "j M Y",
                        onReady: function(s, d, instance) {
                            const container = instance.calendarContainer;
                            if (container && !container.querySelector('.flatpickr-today-button')) {
                                const btn = document.createElement("div");
                                btn.className = "flatpickr-today-button"; btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                                btn.addEventListener("mousedown", function(e) { e.preventDefault(); instance.setDate(new Date()); instance.close(); });
                                container.appendChild(btn);
                            }
                            if (instance.altInput && instance.altInput.value) {
                                const date = instance.selectedDates[0] || new Date(instance.input.value);
                                const day = date.getDate(); const month = instance.l10n.months.shorthand[date.getMonth()];
                                const year = date.getFullYear() + yearOffset;
                                instance.altInput.value = `${day} ${month} ${year}`;
                            }
                        },
                        onChange: function(s, d, instance) {
                            if (instance.altInput && s.length > 0) {
                                const date = s[0];
                                setTimeout(() => {
                                    const day = date.getDate(); const month = instance.l10n.months.shorthand[date.getMonth()];
                                    const year = date.getFullYear() + yearOffset;
                                    instance.altInput.value = `${day} ${month} ${year}`;
                                }, 10);
                            }
                        }
                    };
                    const startPicker = flatpickr("#start_date", commonConfig);
                    const endPicker = flatpickr("#end_date", commonConfig);
                    $('select[name="budget_year"]').on('change', function() {
                        const v = parseInt($(this).val());
                        if (!isNaN(v)) {
                            startPicker.setDate((v-544)+"-10-01", true);
                            endPicker.setDate((v-543)+"-09-30", true);
                            $('#budget_year_changed').val('1');
                        }
                    });
                }

                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function() { window.dispatchEvent(new Event('resize')); });
            });
        </script>
    @endpush
@endsection
