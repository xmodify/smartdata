@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
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

        /* Override DataTables UI */
        button.dt-button.btn-excel {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #fff !important;
            border-radius: 8px !important;
            font-size: 0.8rem !important;
            padding: 6px 15px !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            box-shadow: 0 2px 4px rgba(25, 135, 84, 0.2) !important;
            transition: all 0.2s !important;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .nav-tabs-custom {
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 1.5rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6e707e;
            font-weight: 600;
            padding: 10px 20px;
            position: relative;
            transition: all 0.3s;
        }

        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            background: transparent;
        }

        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #4e73df;
        }

        .chart-container {
            position: relative;
            height: 250px;
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
                        <i class="fas fa-ambulance text-primary me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">สรุปผลการดำเนินงานและสถิติการส่งต่อ ปีงบประมาณ
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

        <!-- Summary & Trends -->
        <div class="row g-3 mb-4">
            <!-- Summary Cards -->
            <div class="col-xl-3">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm text-white"
                            style="border-radius: 12px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                            <div class="card-body p-3">
                                <div class="small opacity-75">รวมส่งต่อทั้งหมด</div>
                                <div class="h4 mb-0 fw-bold">
                                    {{ number_format(count($refer_list_opd) + count($refer_list_ipd)) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm text-white"
                            style="border-radius: 12px; background: linear-gradient(135deg, #36b9cc 0%, #1a8a97 100%);">
                            <div class="card-body p-3">
                                <div class="small opacity-75">OPD Refer Out</div>
                                <div class="h4 mb-0 fw-bold">{{ number_format(count($refer_list_opd)) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm text-white"
                            style="border-radius: 12px; background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                            <div class="card-body p-3">
                                <div class="small opacity-75">IPD Refer Out</div>
                                <div class="h4 mb-0 fw-bold">{{ number_format(count($refer_list_ipd)) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend Chart -->
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="m-0 fw-bold text-primary">แนวโน้มรายเดือน (ตามตัวเลือก)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yearly Trend Chart -->
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="m-0 fw-bold text-primary">แนวโน้ม 5 ปี ย้อนหลัง</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="yearlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Lists -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body p-4">
                <ul class="nav nav-tabs nav-tabs-custom" id="referTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd-panel"
                            type="button" role="tab">
                            <i class="fas fa-user-md me-2"></i>ผู้ป่วยนอก (OPD)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd-panel"
                            type="button" role="tab">
                            <i class="fas fa-bed me-2"></i>ผู้ป่วยใน (IPD)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="referTabsContent">
                    <!-- OPD Panel -->
                    <div class="tab-pane fade show active" id="opd-panel" role="tabpanel">
                        <div class="table-responsive">
                            <table id="opdTable" class="table table-hover align-middle" style="width:100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th>HN</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>โรคหลัก</th>
                                        <th>Refer Hos</th>
                                        <th>วันที่ส่งต่อ</th>
                                        <th>จุดส่งต่อ</th>
                                        <th class="text-center">Ambulance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($refer_list_opd as $row)
                                        <tr>
                                            <td><span
                                                    class="badge bg-light text-primary border px-2">{{ $row->hn }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $row->ptname }}</div>
                                            </td>
                                            <td><span
                                                    class="badge bg-primary bg-opacity-10 text-primary">{{ $row->pdx }}</span>
                                            </td>
                                            <td>{{ $row->refer_hos }}</td>
                                            <td>{{ DateThai($row->refer_date) }} {{ $row->refer_time }}</td>
                                            <td>{{ $row->refer_point }}</td>
                                            <td class="text-center">
                                                @if ($row->with_ambulance == 'Y')
                                                    <i class="fas fa-truck-medical text-danger"></i>
                                                @else
                                                    <i class="fas fa-times text-muted opacity-50"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- IPD Panel -->
                    <div class="tab-pane fade" id="ipd-panel" role="tabpanel">
                        <div class="table-responsive">
                            <table id="ipdTable" class="table table-hover align-middle" style="width:100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th>HN</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th>โรคหลัก</th>
                                        <th>Refer Hos</th>
                                        <th>วันที่ส่งต่อ</th>
                                        <th>จุดส่งต่อ</th>
                                        <th class="text-center">Ambulance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($refer_list_ipd as $row)
                                        <tr>
                                            <td><span
                                                    class="badge bg-light text-primary border px-2">{{ $row->hn }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $row->ptname }}</div>
                                            </td>
                                            <td><span
                                                    class="badge bg-info bg-opacity-10 text-info">{{ $row->pdx }}</span>
                                            </td>
                                            <td>{{ $row->refer_hos }}</td>
                                            <td>{{ DateThai($row->refer_date) }} {{ $row->refer_time }}</td>
                                            <td>{{ $row->refer_point }}</td>
                                            <td class="text-center">
                                                @if ($row->with_ambulance == 'Y')
                                                    <i class="fas fa-truck-medical text-danger"></i>
                                                @else
                                                    <i class="fas fa-times text-muted opacity-50"></i>
                                                @endif
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
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

        <script>
            $(document).ready(function() {
                Chart.register(ChartDataLabels);
                // Flatpickr setup
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
                        },
                        onChange: function(selectedDates, dateStr, instance) {
                            if (instance.altInput && selectedDates.length > 0) {
                                const date = selectedDates[0];
                                const day = date.getDate();
                                const month = instance.l10n.months.shorthand[date.getMonth()];
                                const year = date.getFullYear() + yearOffset;
                                instance.altInput.value = `${day} ${month} ${year}`;
                            }
                        }
                    };
                    flatpickr("#start_date", commonConfig);
                    flatpickr("#end_date", commonConfig);
                }

                // DataTables setup
                const dtConfig = {
                    language: {
                        url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json"
                    },
                    pageLength: 10,
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"dt-left-info"> <"d-flex gap-2"fB>>rtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn-excel',
                        title: '{{ $title }}',
                        messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
                    }],
                    initComplete: function() {
                        $("div.dt-left-info").html(
                            '<div class="text-primary fw-bold"><i class="fas fa-calendar-alt me-1"></i> ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>'
                        );
                    }
                };
                $('#opdTable').DataTable(dtConfig);
                $('#ipdTable').DataTable(dtConfig);

                // Chart Data from PHP
                const monthlyData = @json($monthly_trend);
                const yearlyData = @json($yearly_trend);

                // Monthly Trend Chart
                new Chart(document.getElementById('monthlyChart'), {
                    type: 'line',
                    data: {
                        labels: monthlyData.map(d => d.label),
                        datasets: [{
                                label: 'OPD',
                                data: monthlyData.map(d => d.opd_count),
                                borderColor: '#4e73df',
                                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'IPD',
                                data: monthlyData.map(d => d.ipd_count),
                                borderColor: '#1cc88a',
                                backgroundColor: 'rgba(28, 200, 138, 0.05)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: (ctx) => ctx.dataset.borderColor,
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // Yearly Trend Chart
                new Chart(document.getElementById('yearlyChart'), {
                    type: 'bar',
                    data: {
                        labels: yearlyData.map(d => 'ปีงบ ' + d.year_be),
                        datasets: [{
                                label: 'OPD',
                                data: yearlyData.map(d => d.opd_count),
                                backgroundColor: '#4e73df'
                            },
                            {
                                label: 'IPD',
                                data: yearlyData.map(d => d.ipd_count),
                                backgroundColor: '#1cc88a'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#444',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
@endsection
