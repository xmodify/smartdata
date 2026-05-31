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
        $count_type = count(array_filter($patients, function($p) { return $p->ovstist == '12'; }));
        $count_charge = count(array_filter($patients, function($p) { return $p->has_telmed_charge == 1; }));
        $unique_hns = count(array_unique(array_column($patients, 'hn')));
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


        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow-sm h-100 py-2 card-hover-effect">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    จำนวนการให้บริการรวม
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($total_visits) }} ครั้ง</div>
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
                                    จำนวนผู้ป่วย (ไม่ซ้ำคน)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($unique_hns) }} คน</div>
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
                                    ผ่านประเภทผู้ป่วย (ovstist=12)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($count_type) }} ครั้ง</div>
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
                                    ผ่านการบันทึกรหัส (TELMED)
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($count_charge) }} ครั้ง</div>
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
                            <i class="fas fa-chart-bar me-2 text-primary"></i> กราฟแสดงจำนวนผู้รับบริการ Telehealth รายเดือน (หน่วย: ครั้ง)
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
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold mb-3 text-dark">
                    <i class="fas fa-list me-2 text-success"></i> รายชื่อผู้รับบริการ Telehealth ทั้งหมด
                </h6>
                
                <!-- Status Filter Tabs -->
                <ul class="nav nav-pills custom-pills mb-2" id="statusFilterTabs" role="tablist" style="gap: 5px;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active status-tab py-1 px-3" data-status-filter="all" type="button">
                            ทั้งหมด ({{ number_format($total_visits) }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link status-tab py-1 px-3" data-status-filter="complete" type="button">
                            ครบถ้วน ({{ number_format(count(array_filter($patients, function($p) { return $p->ovstist == '12' && $p->has_telmed_charge; }))) }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link status-tab py-1 px-3" data-status-filter="type_only" type="button">
                            เฉพาะประเภทผู้ป่วย ({{ number_format(count(array_filter($patients, function($p) { return $p->ovstist == '12' && !$p->has_telmed_charge; }))) }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link status-tab py-1 px-3" data-status-filter="charge_only" type="button">
                            เฉพาะรหัสเบิก ({{ number_format(count(array_filter($patients, function($p) { return $p->ovstist != '12' && $p->has_telmed_charge; }))) }})
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="table-responsive">
                    <table id="telehealthTable" class="table table-hover align-middle mb-0" style="width:100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">ลำดับ</th>
                                <th class="text-center">วันที่บริการ</th>
                                <th class="text-center">VN</th>
                                <th class="text-center">คิว</th>
                                <th class="text-center">HN</th>
                                <th>ชื่อ-สกุล</th>
                                <th class="text-center">อายุ (ปี)</th>
                                <th>สิทธิการรักษา</th>
                                <th class="text-center">โรคหลัก</th>
                                <th>ห้องตรวจที่รักษา</th>
                                <th class="text-center">แพทย์ผู้ตรวจ</th>
                                <th class="text-center">สถานะ</th>
                                <th>เลขที่สิทธิ / เลขอนุมัติ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($patients as $row)
                                <tr>
                                    <td class="text-center text-muted" style="font-size: 0.85rem;">{{ $loop->iteration }}</td>
                                    <td class="text-center" style="font-size: 0.85rem;">{{ DateThai($row->vstdate) }}</td>
                                    <td class="text-center text-primary fw-bold" style="font-size: 0.85rem;">{{ $row->vn }}</td>
                                    <td class="text-center" style="font-size: 0.85rem;">
                                        <span class="badge bg-secondary rounded-pill px-2 py-1">{{ $row->oqueue }}</span>
                                    </td>
                                    <td class="text-center" style="font-size: 0.85rem;">{{ $row->hn }}</td>
                                    <td class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $row->ptname }}</td>
                                    <td class="text-center" style="font-size: 0.85rem;">{{ $row->age_y }}</td>
                                    <td style="font-size: 0.85rem;">{{ $row->pttype }}</td>
                                    <td class="text-center fw-bold text-danger" style="font-size: 0.85rem;">{{ $row->pdx }}</td>
                                    <td style="font-size: 0.85rem;">{{ $row->department }}</td>
                                    <td class="text-center" style="font-size: 0.85rem;">{{ $row->dx_doctor }}</td>
                                    <td class="text-center" style="font-size: 0.82rem;" data-status="{{ ($row->ovstist == '12' && $row->has_telmed_charge) ? 'complete' : (($row->ovstist == '12') ? 'type_only' : 'charge_only') }}">
                                        @if ($row->ovstist == '12' && $row->has_telmed_charge)
                                            <span class="badge bg-success">ครบถ้วน</span>
                                        @elseif ($row->ovstist == '12')
                                            <span class="badge bg-primary">ประเภท: Tele</span>
                                        @else
                                            <span class="badge bg-warning text-dark">เฉพาะรหัสเบิก</span>
                                        @endif
                                    </td>
                                    <td style="font-family: monospace; font-size: 0.82rem;">{{ $row->auth_code ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

            // Setup Custom DataTable Filter for Status Tabs
            let currentFilterStatus = 'all';
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    if (currentFilterStatus === 'all') {
                        return true;
                    }
                    let cell = table.cell(dataIndex, 11).node();
                    let status = $(cell).attr('data-status');
                    return status === currentFilterStatus;
                }
            );

            // Initialize DataTable
            const table = $('#telehealthTable').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-3"l<"d-flex align-items-center gap-2"fB>>rtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success btn-sm m-0',
                    title: '{{ $title }}',
                    messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
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

            // Handle tab filtering click
            $('.status-tab').on('click', function(e) {
                e.preventDefault();
                $('.status-tab').removeClass('active');
                $(this).addClass('active');
                currentFilterStatus = $(this).data('status-filter');
                table.draw();
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
