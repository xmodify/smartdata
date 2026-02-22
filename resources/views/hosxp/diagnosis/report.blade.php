@extends('layouts.app')
@php
    $category_names = [
        'opd' => 'ผู้ป่วยนอก OPD',
        'ipd' => 'ผู้ป่วยใน IPD',
        'refer' => 'ผู้ป่วยส่งต่อ Refer',
    ];
    $category_label = $category_names[(string) $category] ?? 'ผู้ป่วยนอก OPD';
@endphp

@section('title', 'รายชื่อ' . $category_label . 'โรค ' . $config['name'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            /* Reduced padding */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            /* Reduced margin */
        }

        .report-title-box h5 {
            font-size: 1.1rem;
            /* Smaller title */
            letter-spacing: -0.01em;
        }

        .budget-select-box {
            background: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .input-group-date {
            width: 160px !important;
        }

        .input-group-budget {
            width: 250px !important;
        }

        @media (max-width: 768px) {

            .input-group-date,
            .input-group-budget {
                width: 100% !important;
            }
        }

        .table-modern thead th {
            background-color: #e3f2fd !important;
            color: #0d47a1;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            /* Smaller header font */
            padding: 10px 8px;
            /* Reduced padding */
            border-bottom: 2px solid #bbdefb !important;
        }

        .table-modern tbody td {
            vertical-align: middle;
            padding: 8px 10px;
            /* Reduced vertical padding */
            font-size: 0.85rem;
            /* Smaller table body font */
        }

        .col-order {
            background-color: #f1f8fe;
            width: 50px;
        }

        .badge-pdx {
            background-color: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .dash-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .card-header-premium {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 0.75rem 1.25rem;
            /* Reduced padding */
        }

        /* Override DataTables UI */
        button.dt-button.btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #fff !important;
            border-radius: 0.25rem !important;
            font-size: 0.75rem !important;
            padding: 5px 12px !important;
            /* Adjusted for better alignment */
            line-height: 1 !important;
            margin: 0 !important;
            height: 31px !important;
            /* Fixed height for perfect alignment */
            display: flex !important;
            align-items: center !important;
        }

        .dataTables_filter input,
        .dataTables_length select {
            border-radius: 0.25rem !important;
            border: 1px solid #dee2e6 !important;
            font-size: 0.75rem !important;
            padding: 4px 10px !important;
            height: 31px !important;
            /* Same as button */
            outline: none !important;
            box-shadow: none !important;
        }

        .dataTables_filter label,
        .dataTables_length label {
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: #64748b !important;
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        button.dt-button.btn-success:hover {
            background-color: #157347 !important;
            border-color: #146c43 !important;
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
    </style>
@endpush

@section('topbar_actions')
    <a href="{{ route('hosxp.diagnosis.index', ['category' => $category]) }}"
        class="btn btn-light btn-sm shadow-sm text-primary fw-bold">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Box -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="{{ $config['icon'] }} {{ $config['color'] }} me-2"></i>
                        รายชื่อ{{ $category_label }}โรค {{ $config['name'] }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง
                        {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 d-md-flex align-items-center gap-2 header-form-controls">
                    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
                    <div class="input-group input-group-sm mb-2 mb-md-0 shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm mb-2 mb-md-0 shadow-sm input-group-date"
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


        <!-- Chart Row -->
        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card dash-card h-100">
                    <div class="card-header card-header-premium">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-bar-chart-fill text-info me-2"></i>
                            สถิติรายเดือน (ครั้ง/คน)
                        </h6>
                    </div>
                    <div class="card-body">
                        <canvas id="diag_month" style="width: 100%; height: 350px"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card dash-card h-100">
                    <div class="card-header card-header-premium">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-graph-up-arrow text-warning me-2"></i>
                            แนวโน้ม 5 ปีงบประมาณย้อนหลัง
                        </h6>
                    </div>
                    <div class="card-body">
                        <div id="diag_year" style="width: 100%; height: 350px"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient List Card -->
        <div class="card dash-card">
            <div class="card-header card-header-premium">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-people-fill text-primary me-2"></i>
                    รายชื่อ{{ $category_label }}โรค {{ $config['name'] }} ปีงบประมาณ {{ $budget_year }}
                </h6>
            </div>
            <div class="card-body p-0">
                @if ($category === 'ipd')
                    @include('hosxp.diagnosis.partials._table_ipd')
                @elseif($category === 'refer')
                    @include('hosxp.diagnosis.partials._table_refer')
                @else
                    @include('hosxp.diagnosis.partials._table_opd')
                @endif
            </div>
        </div>
    </div>
    <br>

@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

    <script>
        // Register the plugin to all charts if needed, or just specific ones
        Chart.register(ChartDataLabels);

        $(document).ready(function() {
            if (typeof flatpickr !== 'undefined') {
                const commonConfig = {
                    locale: "th",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "j M Y",
                    allowInput: true,
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
                    }
                };

                const startPicker = flatpickr("#start_date", commonConfig);
                const endPicker = flatpickr("#end_date", commonConfig);

                // Synchronize: Clear dates when budget year changes
                $('select[name="budget_year"]').on('change', function() {
                    if (typeof startPicker !== 'undefined') startPicker.clear();
                    if (typeof endPicker !== 'undefined') endPicker.clear();
                    $('#start_date, #end_date').val('');
                });
            }

            $('#diag_list').DataTable({
                dom: '<"d-flex justify-content-between align-items-center py-2 px-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-2"fB>>rt<"d-flex justify-content-between align-items-center p-3"ip>',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                    className: 'btn btn-success btn-sm px-3',
                    title: 'รายชื่อ{{ $category_label }}โรค {{ $config['name'] }} ปีงบประมาณ {{ $budget_year }}'
                }],
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    paginate: {
                        previous: "ก่อนหน้า",
                        next: "ถัดไป"
                    }
                },
                pageLength: 10,
                order: [
                    [0, 'asc']
                ]
            });
        });

        document.addEventListener("DOMContentLoaded", () => {
            // Monthly Bar Chart
            new Chart(document.querySelector('#diag_month'), {
                type: 'bar',
                data: {
                    labels: @json($diag_m),
                    datasets: [{
                            label: 'ครั้ง (Visits)',
                            data: @json($diag_visit_m),
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgb(54, 162, 235)',
                            borderWidth: 1,
                            borderRadius: 6,
                            datalabels: {
                                align: 'end',
                                anchor: 'end'
                            }
                        },
                        {
                            label: 'คน (Patients)',
                            data: @json($diag_hn_m),
                            backgroundColor: 'rgba(153, 102, 255, 0.7)',
                            borderColor: 'rgb(153, 102, 255)',
                            borderWidth: 1,
                            borderRadius: 6,
                            datalabels: {
                                align: 'end',
                                anchor: 'end'
                            }
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 25
                        } // Add padding for labels
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        datalabels: {
                            color: '#444',
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            formatter: function(value, context) {
                                return value > 0 ? value : '';
                            }
                        }
                    },
                    scales: {
                        y: {
                            grid: {
                                borderDash: [5, 5]
                            },
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => v.toLocaleString()
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Yearly Area Chart
            new ApexCharts(document.querySelector("#diag_year"), {
                series: [{
                        name: 'ครั้ง',
                        data: @json($diag_visit_y)
                    },
                    {
                        name: 'คน',
                        data: @json($diag_hn_y)
                    }
                ],
                chart: {
                    height: 350,
                    type: 'area',
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Nunito, sans-serif'
                },
                markers: {
                    size: 5,
                    strokeWidth: 3,
                    hover: {
                        size: 7
                    }
                },
                colors: ['#3b82f6', '#8b5cf6'],
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.5,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 4
                },
                xaxis: {
                    categories: @json($diag_y),
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '12px',
                            fontWeight: 600
                        }
                    },
                    axisBorder: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#64748b'
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    offsetY: 8
                }
            }).render();
        });
    </script>
@endpush
