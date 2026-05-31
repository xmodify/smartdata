@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.hmed.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #10b981; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
    <style>
        :root {
            --primary-green: #10b981;
            --secondary-green: #059669;
            --light-green: #f0fdf4;
        }

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

        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        .text-green { color: var(--primary-green); }
        .bg-green { background-color: var(--primary-green) !important; }
        .bg-pastel-green { background-color: var(--light-green); }

        .btn-green {
            background-color: var(--primary-green) !important;
            border-color: var(--primary-green) !important;
            color: #fff !important;
            transition: all 0.3s;
        }
        .btn-green:hover {
            background-color: var(--secondary-green) !important;
            border-color: var(--secondary-green) !important;
            transform: translateY(-1px);
        }

        .chart-container {
            position: relative;
            height: 500px;
            width: 100%;
        }

        /* Override DataTables UI */
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.4rem !important;
            padding: 0.2rem 1.2rem 0.2rem 0.5rem !important;
            margin: 0 0.3rem !important;
            outline: none !important;
            font-size: 0.75rem !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.4rem !important;
            padding: 0.2rem 0.6rem !important;
            outline: none !important;
            width: 180px !important;
            font-size: 0.75rem !important;
        }
        .dt-buttons .btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
            border-radius: 0.4rem !important;
            padding: 0.2rem 0.6rem !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
        }
        .table.dataTable thead th {
            background-color: #f8f9fa !important;
            color: #495057 !important;
            font-weight: 600 !important;
            border-bottom: 2px solid #dee2e6 !important;
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
        }
        .table.dataTable tbody td {
            padding: 8px 6px !important;
            font-size: 0.75rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-green) !important;
            color: white !important;
            border: 1px solid var(--primary-green) !important;
            border-radius: 0.5rem !important;
        }

        /* Flatpickr Today Button Style */
        .flatpickr-today-button {
            padding: 10px;
            text-align: center;
            border-top: 1px solid #e6e6e6;
            cursor: pointer;
            font-weight: bold;
            color: var(--primary-green);
            background: #f8f9fa;
        }
        .flatpickr-today-button:hover {
            background: var(--light-green);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-leaf text-green me-2"></i> {{ $title }}</h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-green small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <form action="" method="GET" id="filter-form" class="m-0 header-form-controls d-flex align-items-center gap-2">
                    <input type="hidden" name="budget_year_changed" id="budget_year_changed" value="0">
                    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-green"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-green"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" id="budget_year">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-green text-white px-3"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-green"><i class="fas fa-chart-bar me-2"></i>กราฟแสดง 20 อันดับโรค</h6>
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
                        <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>ตารางข้อมูล 20 อันดับโรค</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="tableDiag" class="table table-hover align-middle" style="width:100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">อันดับ</th>
                                        <th class="text-center">ICD10</th>
                                        <th>ชื่อโรค</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-center text-primary">ชาย</th>
                                        <th class="text-center text-danger">หญิง</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($diag_top20 as $index => $row)
                                        <tr>
                                            <td class="text-center">
                                                @if ($index === 0)
                                                    <span class="badge rounded-pill bg-warning text-dark py-1 px-2" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;">
                                                        <i class="fas fa-crown"></i> 1
                                                    </span>
                                                @elseif($index === 1)
                                                    <span class="badge rounded-pill bg-light text-dark py-1 px-2" style="background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%) !important;">
                                                        <i class="fas fa-crown"></i> 2
                                                    </span>
                                                @elseif($index === 2)
                                                    <span class="badge rounded-pill text-dark py-1 px-2" style="background: linear-gradient(135deg, #f0fdf4 0%, var(--primary-green) 100%) !important;">
                                                        <i class="fas fa-crown"></i> 3
                                                    </span>
                                                @else
                                                    <span class="text-muted fw-bold">{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                                                    {{ $row->code }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-dark">{{ $row->name }}</td>
                                            <td class="text-center fw-bold text-green">{{ number_format($row->sum) }}</td>
                                            <td class="text-center text-primary" style="opacity: 0.8;">{{ number_format($row->male) }}</td>
                                            <td class="text-center text-danger" style="opacity: 0.8;">{{ number_format($row->female) }}</td>
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
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
        <script src="{{ asset('vendor/chartjs/chartjs-plugin-datalabels.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>

        <script>
            $(document).ready(function() {
                Chart.register(ChartDataLabels);

                const diagData = @json($diag_top20);

                if (diagData.length > 0) {
                    new Chart(document.getElementById('chartDiag'), {
                        type: 'bar',
                        data: {
                            labels: diagData.map(d => d.name.length > 25 ? d.name.substring(0, 25) + '...' : d.name),
                            datasets: [{
                                label: 'จำนวน (ครั้ง)',
                                data: diagData.map(d => d.sum),
                                backgroundColor: '#10b981',
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
                                datalabels: {
                                    anchor: 'end', align: 'right',
                                    color: '#059669',
                                    font: { weight: 'bold', size: 10 },
                                    formatter: Math.round
                                }
                            },
                            scales: {
                                x: { display: false, beginAtZero: true },
                                y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                            },
                            layout: { padding: { right: 40 } }
                        }
                    });
                }

                $('#tableDiag').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-2"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success',
                        title: '{{ $title }}',
                    }],
                    language: {
                        search: "ค้นหา:", lengthMenu: "แสดง _MENU_ รายการ",
                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                        paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                    },
                    pageLength: 10
                });

                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th", dateFormat: "Y-m-d", altInput: true, altFormat: "j M Y",
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
                    $('#budget_year').on('change', function() {
                        $('#budget_year_changed').val('1');
                        var selectedYear = parseInt($(this).val());
                        if(!isNaN(selectedYear)) {
                            startPicker.setDate((selectedYear - 544) + "-10-01", true);
                            endPicker.setDate((selectedYear - 543) + "-09-30", true);
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
