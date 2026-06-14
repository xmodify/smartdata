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

        .report-title-box h5 {
            font-size: 1.1rem;
            letter-spacing: -0.01em;
        }

        .header-form-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-budget { width: 100% !important; }
        }

        .card-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .table-custom {
            font-size: 0.85rem;
        }
        .table-custom th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 0.025em;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
        }
        .table-custom td {
            vertical-align: middle;
        }

        .text-green { color: #10b981 !important; }
        .text-red { color: #ef4444 !important; }
        .bg-pastel-green { background-color: #ecfdf5 !important; }
        .bg-pastel-red { background-color: #fef2f2 !important; }

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            color: #10b981;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }

        .flatpickr-today-button:hover {
            background: #fdfaff;
            color: #059669;
        }

        /* DataTables Custom Styling */
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
            background: #4f46e5 !important;
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
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-moon text-primary me-2"></i> {{ $title }}
                    </h5>
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

        <!-- Monthly Chart Section -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-chart-line me-2"></i>แนวโน้มการสั่งยาช่วงเวลาเวรดึกรายเดือน (จำนวนรายการสั่งยา)</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="monthlyNightShiftChart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table Section -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card card-custom">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-primary"></i>ตารางข้อมูลการสั่งยาช่วงเวลาเวรดึก (00:00 - 08:00 น.)</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-custom" id="nightShiftTable">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">ลำดับ</th>
                                        <th>วันที่สั่งยา</th>
                                        <th>เวลาสั่ง</th>
                                        <th>แผนก</th>
                                        <th>HN</th>
                                        <th>VN / AN</th>
                                        <th>ชื่อผู้ป่วย</th>
                                        <th>ชื่อยา</th>
                                        <th class="text-center">บัญชียา</th>
                                        <th class="text-end">จำนวน</th>
                                        <th class="text-end">ราคาทุนรวม</th>
                                        <th class="text-end">ราคาขายรวม</th>
                                        <th>แพทย์ผู้สั่ง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($night_shift_data as $index => $row)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td class="text-center">{{ $row->rxdate ? DateThai($row->rxdate) : '-' }}</td>
                                            <td class="text-center">{{ $row->rxtime ?? '-' }}</td>
                                            <td class="text-center">
                                                @if($row->department === 'IPD')
                                                    <span class="badge bg-pastel-red text-red border px-2">IPD</span>
                                                @else
                                                    <span class="badge bg-pastel-green text-green border px-2">ER</span>
                                                @endif
                                            </td>
                                            <td class="text-center font-monospace">{{ $row->hn }}</td>
                                            <td class="text-center font-monospace">{{ $row->an ?? $row->vn ?? '-' }}</td>
                                            <td>{{ $row->ptname }}</td>
                                            <td class="fw-bold text-dark">{{ $row->drug_name }}</td>
                                            <td class="text-center">
                                                @if($row->acc === 'ED')
                                                    <span class="badge bg-success">ED</span>
                                                @else
                                                    <span class="badge bg-secondary">NED</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($row->qty) }}</td>
                                            <td class="text-end text-muted">{{ number_format($row->sum_cost, 2) }}</td>
                                            <td class="text-end text-primary fw-bold">{{ number_format($row->sum_price, 2) }}</td>
                                            <td>{{ $row->doctor ?? '-' }}</td>
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
                // Monthly Chart setup
                var monthlyChart = new ApexCharts(document.querySelector("#monthlyNightShiftChart"), {
                    series: [{
                        name: 'จำนวนรายการสั่งยา',
                        data: @json(array_column($monthly_data, 'order_count'))
                    }],
                    chart: {
                        type: 'line',
                        height: 300,
                        toolbar: { show: false }
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '11px',
                            colors: ['#4f46e5']
                        },
                        background: {
                            enabled: true,
                            foreColor: '#ffffff',
                            padding: 4,
                            borderRadius: 4,
                            borderWidth: 1,
                            borderColor: '#4f46e5',
                            opacity: 0.9,
                        },
                        offsetY: -10
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 4
                    },
                    xaxis: {
                        categories: @json(array_column($monthly_data, 'month_name'))
                    },
                    colors: ['#4f46e5'],
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toLocaleString() + " รายการ";
                            }
                        }
                    }
                });
                monthlyChart.render();

                var table = $('#nightShiftTable').DataTable({
                    pageLength: 10,
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานข้อมูลการสั่งยาช่วงเวลาเวรดึก 00.00-08.00 น.',
                        exportOptions: { columns: ':visible' }
                    }],
                    language: {
                        search: "ค้นหา:",
                        lengthMenu: "แสดง _MENU_ รายการ",
                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                        paginate: {
                            previous: "ก่อนหน้า",
                            next: "ถัดไป"
                        }
                    }
                });

                // Flatpickr setup
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
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
                        const selectedYear = parseInt($(this).val());
                        if (!isNaN(selectedYear)) {
                            const startYear = selectedYear - 544;
                            const endYear = selectedYear - 543;
                            const startDateStr = startYear + "-10-01";
                            const endDateStr = endYear + "-09-30";

                            startPicker.setDate(startDateStr, true);
                            endPicker.setDate(endDateStr, true);
                            $('#budget_year_changed').val('1');
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
