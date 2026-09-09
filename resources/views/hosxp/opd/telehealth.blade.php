@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@section('content')
    @php
        $total_visits = count($patients);
        $count_complete = count(array_filter($patients, function($p) { return $p->ovstist == '12' && $p->has_telmed_charge; }));
        $count_type = count(array_filter($patients, function($p) { return $p->ovstist == '12' && !$p->has_telmed_charge; }));
        $count_charge = count(array_filter($patients, function($p) { return $p->ovstist != '12' && $p->has_telmed_charge; }));
        
        $ann_total = $summary->total_visits ?? 0;
        $ann_hns = $summary->unique_hns ?? 0;
        $ann_type = $summary->count_type ?? 0;
        $ann_charge = $summary->count_charge ?? 0;
    @endphp

    <div class="container-fluid px-2 px-md-3">
        <!-- Header Box -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-tv text-primary me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลภาพรวมปีงบประมาณ {{ $budget_year }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0">
                    <div class="input-group input-group-sm shadow-sm" style="width: 230px; border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-light text-primary border-end-0 fw-bold" style="font-size: 0.8rem;">
                            <i class="fas fa-calendar-alt me-1"></i> เลือก
                        </span>
                        <select class="form-select border-start-0" name="budget_year" onchange="this.form.submit()" style="font-size: 0.85rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    ปีงบ {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards (Annual Budget Year) -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow-sm h-100 py-2 card-hover-effect">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    บริการรวมทั้งปีงบ (ครั้ง)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($ann_total) }} ครั้ง</div>
                                <div class="text-muted" style="font-size: 0.75rem;">ปีงบประมาณ {{ $budget_year }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-laptop-medical fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow-sm h-100 py-2 card-hover-effect">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    ผู้ป่วยทั้งปีงบ (ไม่ซ้ำคน)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($ann_hns) }} คน</div>
                                <div class="text-muted" style="font-size: 0.75rem;">ปีงบประมาณ {{ $budget_year }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-info shadow-sm h-100 py-2 card-hover-effect">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    ประเภทผู้ป่วย (ovstist=12)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($ann_type) }} ครั้ง</div>
                                <div class="text-muted" style="font-size: 0.75rem;">ปีงบประมาณ {{ $budget_year }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-md fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow-sm h-100 py-2 card-hover-effect">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    บันทึกรหัสเบิก (TELMED)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($ann_charge) }} ครั้ง</div>
                                <div class="text-muted" style="font-size: 0.75rem;">ปีงบประมาณ {{ $budget_year }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Container -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; border-top: 4px solid #4e73df !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-chart-bar me-2 text-primary"></i> กราฟแสดงจำนวนผู้รับบริการ Telehealth รายเดือน ปีงบประมาณ {{ $budget_year }} (หน่วย: ครั้ง)
                        </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="telehealthMonthlyChart" style="min-height: 320px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div class="card border-0 shadow-sm mb-5 shadow-hover" style="border-radius: 15px; border-top: 4px solid #1cc88a !important;">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-list me-2 text-success"></i> รายชื่อผู้รับบริการ Telehealth
                        </h6>
                        <small class="text-muted">
                            ช่วงวันที่: <span id="displayDateRange" class="fw-bold text-dark">{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}</span>
                        </small>
                    </div>

                    <!-- Independent Table Date Filter -->
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" id="table_start_date" class="form-control border-start-0 ps-0 text-center"
                                value="{{ $table_start_date }}" placeholder="เริ่ม">
                        </div>
                        <span class="text-muted small">ถึง</span>
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" id="table_end_date" class="form-control border-start-0 ps-0 text-center"
                                value="{{ $table_end_date }}" placeholder="สิ้นสุด">
                        </div>
                        <button type="button" id="btnFilterTable" class="btn btn-sm btn-primary px-3 shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-search me-1"></i> ค้นหา
                        </button>
                        <div class="btn-group btn-group-sm ms-1 shadow-sm">
                            <button type="button" id="btnCurrentMonth" class="btn btn-outline-secondary" title="เลือกเดือนปัจจุบัน">
                                เดือนนี้
                            </button>
                            <button type="button" id="btnPrevMonth" class="btn btn-outline-secondary" title="เลือกเดือนก่อนหน้า">
                                เดือนก่อน
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Status Filter Tabs -->
                <ul class="nav nav-pills custom-pills mb-1" id="statusFilterTabs" role="tablist" style="gap: 5px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active status-tab py-1 px-3" data-status-filter="all" type="button">
                            ทั้งหมด (<span id="tab_count_all">{{ number_format($total_visits) }}</span>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link status-tab py-1 px-3" data-status-filter="complete" type="button">
                            ครบถ้วน (<span id="tab_count_complete">{{ number_format($count_complete) }}</span>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link status-tab py-1 px-3" data-status-filter="type_only" type="button">
                            เฉพาะประเภทผู้ป่วย (<span id="tab_count_type">{{ number_format($count_type) }}</span>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link status-tab py-1 px-3" data-status-filter="charge_only" type="button">
                            เฉพาะรหัสเบิก (<span id="tab_count_charge">{{ number_format($count_charge) }}</span>)
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 pt-2" id="tableContainer">
                @include('hosxp.opd.partials._table_telehealth')
            </div>
        </div>
    </div>
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

        .report-title-box h5 {
            font-size: 1.1rem;
            letter-spacing: -0.01em;
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

        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }
        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }
        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }
        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .card-hover-effect {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover-effect:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08) !important;
        }

        .custom-pills .nav-link {
            background-color: #f8f9fc;
            color: #4e73df;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .custom-pills .nav-link:hover {
            background-color: #eaecf4;
        }
        .custom-pills .nav-link.active {
            background-color: #4e73df !important;
            color: white !important;
            border-color: #4e73df !important;
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
            let currentFilterStatus = 'all';
            let table = null;

            // Setup Custom DataTable Filter for Status Tabs
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    if (currentFilterStatus === 'all') {
                        return true;
                    }
                    if (!table) return true;
                    let cell = table.cell(dataIndex, 11).node();
                    let status = $(cell).attr('data-status');
                    return status === currentFilterStatus;
                }
            );

            function initPatientTable() {
                if ($.fn.DataTable.isDataTable('#telehealthTable')) {
                    $('#telehealthTable').DataTable().destroy();
                }

                table = $('#telehealthTable').DataTable({
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
                    responsive: true
                });
            }

            initPatientTable();

            // Handle tab filtering click
            $('.status-tab').on('click', function(e) {
                e.preventDefault();
                $('.status-tab').removeClass('active');
                $(this).addClass('active');
                currentFilterStatus = $(this).data('status-filter');
                if (table) table.draw();
            });

            // Flatpickr setup
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

                    if (instance.altInput && instance.input.value) {
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
                        const day = date.getDate();
                        const month = instance.l10n.months.shorthand[date.getMonth()];
                        const year = date.getFullYear() + yearOffset;
                        setTimeout(() => {
                            instance.altInput.value = `${day} ${month} ${year}`;
                        }, 10);
                    }
                }
            };

            const startPicker = flatpickr("#table_start_date", commonConfig);
            const endPicker = flatpickr("#table_end_date", commonConfig);

            // Ajax table loader
            function loadTableData(startDate, endDate) {
                $('#tableContainer').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <div class="mt-2 text-muted small">กำลังโหลดข้อมูลรายชื่อ...</div>
                    </div>
                `);

                $.ajax({
                    url: "{{ route('hosxp.opd.telehealth') }}",
                    type: "GET",
                    data: {
                        budget_year: "{{ $budget_year }}",
                        table_start_date: startDate,
                        table_end_date: endDate
                    },
                    dataType: "json",
                    success: function(res) {
                        if (res.success) {
                            $('#tableContainer').html(res.html);
                            $('#displayDateRange').text(`${res.start_date_thai} ถึง ${res.end_date_thai}`);
                            $('#tab_count_all').text(Number(res.total).toLocaleString());
                            $('#tab_count_complete').text(Number(res.count_complete).toLocaleString());
                            $('#tab_count_type').text(Number(res.count_type).toLocaleString());
                            $('#tab_count_charge').text(Number(res.count_charge).toLocaleString());
                            initPatientTable();
                        } else {
                            $('#tableContainer').html('<div class="alert alert-danger text-center m-4">ไม่สามารถโหลดข้อมูลได้</div>');
                        }
                    },
                    error: function() {
                        $('#tableContainer').html('<div class="alert alert-danger text-center m-4">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</div>');
                    }
                });
            }

            $('#btnFilterTable').on('click', function() {
                const sDate = $('#table_start_date').val();
                const eDate = $('#table_end_date').val();
                if (sDate && eDate) {
                    loadTableData(sDate, eDate);
                }
            });

            $('#btnCurrentMonth').on('click', function() {
                const now = new Date();
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

                const sStr = firstDay.toISOString().split('T')[0];
                const eStr = lastDay.toISOString().split('T')[0];

                startPicker.setDate(sStr, true);
                endPicker.setDate(eStr, true);
                loadTableData(sStr, eStr);
            });

            $('#btnPrevMonth').on('click', function() {
                const now = new Date();
                const firstDay = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth(), 0);

                const sStr = firstDay.toISOString().split('T')[0];
                const eStr = lastDay.toISOString().split('T')[0];

                startPicker.setDate(sStr, true);
                endPicker.setDate(eStr, true);
                loadTableData(sStr, eStr);
            });

            // ApexCharts Setup
            const labels = @json(array_column($monthly_stats, 'month'));
            const completeData = @json(array_column($monthly_stats, 'complete_count'));
            const typeOnlyData = @json(array_column($monthly_stats, 'type_only_count'));
            const chargeOnlyData = @json(array_column($monthly_stats, 'charge_only_count'));

            var chartOptions = {
                series: [
                    { name: 'ครบถ้วน', data: completeData },
                    { name: 'เฉพาะประเภทผู้ป่วย', data: typeOnlyData },
                    { name: 'เฉพาะรหัสเบิก', data: chargeOnlyData }
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
                        columnWidth: '45%',
                        borderRadius: 4
                    },
                },
                colors: ['#1cc88a', '#4e73df', '#f6c23e'],
                xaxis: {
                    categories: labels,
                },
                yaxis: {
                    title: { text: 'จำนวนผู้รับบริการ (ครั้ง)' }
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
                            return val + " ครั้ง";
                        }
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#telehealthMonthlyChart"), chartOptions);
            chart.render();
        });
    </script>
@endpush
