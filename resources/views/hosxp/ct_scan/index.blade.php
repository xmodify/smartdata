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
            background: #f8fbfd;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            border: 1px solid #e3eef5;
        }

        body { background-color: #f4f7fa !important; }

        .header-form-controls {
            display: flex; align-items: center; gap: 0.5rem;
        }

        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        .card-ct { 
            border-radius: 16px; 
            border: 1px solid #e3eef5 !important; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            background: #fff;
            overflow: hidden;
        }
        .chart-container { min-height: 350px; }
        
        .table-ct { font-size: 0.85rem; }
        .table-ct thead th { background-color: #f8f9fa; color: #334155; font-weight: 700; border-bottom: 2px solid #e2e8f0; }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-budget { width: 100% !important; }
        }

        /* Custom DataTables Styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            font-size: 0.8rem !important;
        }
        .dt-buttons .btn-success {
            background-color: #1d6f42 !important;
            border-color: #1d6f42 !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            font-size: 0.75rem !important;
            padding: 0.25rem 0.75rem !important;
            margin-right: 5px;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(29, 111, 66, 0.1) !important;
            transition: all 0.2s ease !important;
        }
        .dt-buttons .btn-success:hover {
            background-color: #155130 !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(29, 111, 66, 0.2) !important;
        }
        .dt-buttons .btn-success i {
            color: #ffffff !important;
        }
        .dt-buttons .btn-info {
            background-color: #17a2b8 !important;
            border-color: #17a2b8 !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            font-size: 0.75rem !important;
            padding: 0.25rem 0.75rem !important;
            margin-right: 5px;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(23, 162, 184, 0.1) !important;
            transition: all 0.2s ease !important;
        }
        .dt-buttons .btn-info:hover {
            background-color: #138496 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(23, 162, 184, 0.2) !important;
        }
        .dt-buttons .btn-info i {
            color: #ffffff !important;
        }
        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #17a2b8 !important;
            border-bottom: 2px solid #e3e6f0 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #17a2b8 !important;
            color: white !important;
            border: 1px solid #17a2b8 !important;
            border-radius: 0.5rem !important;
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
                        <i class="fas fa-x-ray text-info me-2"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ปีงบประมาณ {{ $budget_year }} | ระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-info"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-info"><i class="fas fa-calendar-alt"></i></span>
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
                        <button type="submit" class="btn btn-info text-white px-3">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md">
                <div class="card card-ct shadow-sm border-0 h-100 bg-white" style="transition: transform 0.3s ease; border-top: 4px solid #17a2b8 !important; background: #eef9fa !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-x-ray fa-2x text-info opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-info">{{ number_format($summary['Total']) }}</h3>
                        <div class="small fw-bold text-info mb-1">รวมทั้งหมด (ครั้ง)</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-ct shadow-sm border-0 h-100 bg-white" style="transition: transform 0.3s ease; border-top: 4px solid #4e73df !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-heartbeat fa-2x text-primary opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-primary">{{ number_format($summary['UCS']) }}</h3>
                        <div class="small fw-bold text-primary mb-1">ประกันสุขภาพ</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-ct shadow-sm border-0 h-100 bg-white" style="transition: transform 0.3s ease; border-top: 4px solid #1cc88a !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-user-tie fa-2x text-success opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($summary['OFC']) }}</h3>
                        <div class="small fw-bold text-success mb-1">ข้าราชการ</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-ct shadow-sm border-0 h-100 bg-white" style="transition: transform 0.3s ease; border-top: 4px solid #36b9cc !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-landmark fa-2x text-info opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-info">{{ number_format($summary['LGO']) }}</h3>
                        <div class="small fw-bold text-info mb-1">อปท.</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-ct shadow-sm border-0 h-100 bg-white" style="transition: transform 0.3s ease; border-top: 4px solid #f6c23e !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-shield-halved fa-2x text-warning opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-warning">{{ number_format($summary['SSS']) }}</h3>
                        <div class="small fw-bold text-warning mb-1">ประกันสังคม</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-ct shadow-sm border-0 h-100 bg-white" style="transition: transform 0.3s ease; border-top: 4px solid #fd7e14 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-car-burst fa-2x text-orange opacity-50" style="color: #fd7e14;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #fd7e14;">{{ number_format($summary['A9']) }}</h3>
                        <div class="small fw-bold mb-1" style="color: #fd7e14;">พรบ.</div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card card-ct shadow-sm border-0 h-100 bg-white" style="transition: transform 0.3s ease; border-top: 4px solid #858796 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-folder-plus fa-2x text-secondary opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-secondary">{{ number_format($summary['Others']) }}</h3>
                        <div class="small fw-bold text-secondary mb-1">อื่น ๆ</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Charts -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="card card-ct shadow-sm h-100" style="border-top: 4px solid #17a2b8 !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-bar me-2 text-info"></i> จำนวนผู้รับบริการ CT Scan </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="ctMonthlyChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-ct shadow-sm h-100" style="border-top: 4px solid #fd7e14 !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-coins me-2" style="color: #fd7e14;"></i> ยอดรวมแต่ละเดือน</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="ctPriceMonthlyChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Rights Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-ct shadow-sm" style="border-top: 4px solid #1cc88a !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-bar me-2 text-success"></i> ยอดเรียกเก็บ แยกตามกลุ่มสิทธิ</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="ctClaimRightsChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="row pb-5">
            <div class="col-12">
                <div class="card card-ct shadow-sm" style="border-top: 4px solid #17a2b8 !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-info"></i> รายชื่อผู้รับบริการตรวจ CT Scan</h6>
                        <p class="text-muted small mb-0 mt-1">แสดงข้อมูลบริการตรวจ CT Scan ตามช่วงวันที่เลือก</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-ct mb-0" id="ctTable">
                                <thead>
                                    <tr>
                                        <th style="width: 50px; text-align: center;">ลำดับ</th>
                                        <th style="width: 70px; text-align: center;">ประเภท</th>
                                        <th style="min-width: 90px;">วันที่<br>เวลา</th>
                                        <th style="min-width: 150px;">ชื่อ-สกุล</th>
                                        <th>HN</th>
                                        <th>AN</th>
                                        <th>สิทธิการรักษา</th>
                                        <th style="min-width: 200px;">รายการ</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-end">เรียกเก็บ</th>
                                        <th class="text-end">วางบิล</th>
                                        <th class="text-end">บริษัท CT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $total_qty = 0;
                                        $total_bill = 0;
                                        $total_claim = 0;
                                        $total_ct = 0;
                                    @endphp
                                    @foreach($patients as $row)
                                    @php
                                        $total_qty += $row->qty;
                                        $total_bill += $row->price_bill;
                                        $total_claim += $row->price_claim;
                                        $total_ct += $row->price_ct;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ $row->depart }}</td>
                                        <td>{{ $row->rxdate ? DateThai($row->rxdate) : '-' }}@if($row->rxtime)<br><small class="text-muted"> {{ substr($row->rxtime, 0, 5) }} น.</small>@endif</td>
                                        <td>{{ $row->ptname }}</td>
                                        <td>{{ $row->hn }}</td>
                                        <td>{{ $row->an ?? '-' }}</td>
                                        <td>{{ $row->pttype }}</td>
                                        <td>{{ $row->item_name }}</td>
                                        <td class="text-center">{{ number_format($row->qty) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($row->price_claim, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($row->price_bill, 2) }}</td>
                                        <td class="text-end fw-bold">{{ number_format($row->price_ct, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold bg-light" style="border-top: 2px solid #dee2e6;">
                                        <td colspan="8" class="text-end">รวม</td>
                                        <td class="text-center">{{ number_format($total_qty) }}</td>
                                        <td class="text-end text-success">{{ number_format($total_claim, 2) }}</td>
                                        <td class="text-end text-primary">{{ number_format($total_bill, 2) }}</td>
                                        <td class="text-end text-danger">{{ number_format($total_ct, 2) }}</td>
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
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
        
        <script>
            $(document).ready(function() {
                // Initialize DataTable
                $('#ctTable').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success btn-sm',
                            title: 'รายงานผู้รับบริการ CT Scan ({{ DateThai($start_date) }} - {{ DateThai($end_date) }})'
                        },
                        {
                            text: '<i class="fa-solid fa-print me-1"></i> พิมพ์',
                            className: 'btn btn-info btn-sm',
                            action: function (e, dt, node, config) {
                                window.open("{{ route('hosxp.ct_scan.print', ['start_date' => $start_date, 'end_date' => $end_date, 'budget_year' => $budget_year]) }}", "_blank");
                            }
                        }
                    ],
                    language: {
                        search: "ค้นหา:",
                        lengthMenu: "แสดง _MENU_ รายการ",
                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                        paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                    },
                    pageLength: 10,
                    ordering: true,
                    responsive: true
                });

                const yearOffset = 543;
                const commonConfig = {
                    locale: "th", dateFormat: "Y-m-d", altInput: true, altFormat: "j M Y", allowInput: false,
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
                                e.stopPropagation();
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

                // Update start_date and end_date based on budget_year change
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

                // Chart configuration
                const labels = @json(array_column($monthly_stats, 'month'));
                const counts = @json(array_column($monthly_stats, 'count'));
                const prices = @json(array_column($monthly_stats, 'price_ct'));
                const priceClaims = @json(array_column($monthly_stats, 'price_claim'));

                var chartOptions = {
                    series: [{ name: 'จำนวนคนรับบริการ', data: counts }],
                    chart: { height: 320, type: 'bar', toolbar: { show: false } },
                    colors: ['#17a2b8'],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '50%', dataLabels: { position: 'top' } } },
                    dataLabels: { 
                        enabled: true, 
                        offsetY: -20, 
                        style: { fontSize: '12px', colors: ['#17a2b8'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false }
                    },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: 'จำนวนครั้ง' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#ctMonthlyChart"), chartOptions).render();

                var priceChartOptions = {
                    series: [
                        { name: 'ยอดเรียกเก็บ (บาท)', data: priceClaims },
                        { name: 'ยอด บริษัท CT (บาท)', data: prices }
                    ],
                    chart: { height: 320, type: 'bar', toolbar: { show: false } },
                    colors: ['#1cc88a', '#fd7e14'],
                    plotOptions: { bar: { borderRadius: 6, columnWidth: '70%', dataLabels: { position: 'top' } } },
                    dataLabels: { 
                        enabled: true, 
                        offsetY: -20, 
                        style: { fontSize: '10px', colors: ['#1cc88a', '#fd7e14'] },
                        formatter: function (val) {
                            return val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                        },
                        background: { enabled: false },
                        dropShadow: { enabled: false }
                    },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: 'จำนวนเงิน (บาท)' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#ctPriceMonthlyChart"), priceChartOptions).render();

                const ucsPrices = @json(array_column($monthly_stats, 'UCS'));
                const ofcPrices = @json(array_column($monthly_stats, 'OFC'));
                const lgoPrices = @json(array_column($monthly_stats, 'LGO'));
                const sssPrices = @json(array_column($monthly_stats, 'SSS'));
                const a9Prices = @json(array_column($monthly_stats, 'A9'));
                const otherPrices = @json(array_column($monthly_stats, 'Others'));

                var claimRightsChartOptions = {
                    series: [
                        { name: 'ประกันสุขภาพ', data: ucsPrices },
                        { name: 'ข้าราชการ', data: ofcPrices },
                        { name: 'อปท.', data: lgoPrices },
                        { name: 'ประกันสังคม', data: sssPrices },
                        { name: 'พรบ.', data: a9Prices },
                        { name: 'อื่น ๆ', data: otherPrices }
                    ],
                    chart: { height: 380, type: 'area', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    colors: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#fd7e14', '#858796'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.3,
                            opacityTo: 0.05
                        }
                    },
                    dataLabels: { 
                        enabled: false 
                    },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: 'จำนวนเงิน (บาท)' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    legend: { position: 'bottom' }
                };
                new ApexCharts(document.querySelector("#ctClaimRightsChart"), claimRightsChartOptions).render();
            });
        </script>
    @endpush
@endsection
