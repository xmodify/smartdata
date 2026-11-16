@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
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
            color: #e74a3b;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }

        .flatpickr-today-button:hover {
            background: #fff1f0;
            color: #be2617;
        }

        /* Override DataTables UI to match the premium look (skpcard style) */
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
            background: #e74a3b !important;
            color: white !important;
            border: 1px solid #e74a3b !important;
            border-radius: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #fff1f0 !important;
            color: #e74a3b !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
        }

        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #e74a3b !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #edeff4 !important;
            font-size: 0.85rem !important;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0rem;
        }

        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
            font-size: 0.85rem;
        }

        .dt-buttons {
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        .nav-tabs-custom {
            background: #fff;
            border-radius: 12px;
            padding: 0.5rem 0.5rem 0 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            border: 1px solid #f0f0f0;
            margin-bottom: 1.5rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #6e707e;
            padding: 0.75rem 1.5rem;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #e74a3b;
            background: #f8f9fc;
        }

        .nav-tabs-custom .nav-link.active {
            color: #e74a3b;
            background: #f8f9fc;
            border-bottom: 3px solid #e74a3b;
        }

        .chart-container {
            position: relative;
            height: 600px;
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
                        <i class="fas fa-book-dead text-danger me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปี พ.ศ. {{ $year_be }} (ในสถานพยาบาล = HOSxP, นอกสถานพยาบาล = HOSxP (สถานที่ตาย 2) + AOPOD ที่ไม่พบใน HOSxP)</div>
                    <div class="text-danger small fw-bold mt-1">
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
                        <select class="form-select border-end-0" name="year" style="font-size: 0.8rem;">
                            @foreach ($year_select as $y)
                                <option value="{{ $y }}" {{ (int) $year_be === (int) $y ? 'selected' : '' }}>
                                    ปี พ.ศ. {{ $y }}
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
        @php
            $panes = [
                ['id' => 'hospIcd', 'label' => 'ในสถานพยาบาล · 20 อันดับโรค', 'icon' => 'fa-clipboard-list text-danger', 'data' => $diag_icd10, 'col' => 'รหัส - ชื่อโรค (ICD10)', 'color' => '#e74a3b', 'cls' => 'text-danger', 'title' => 'อันดับโรคการเสียชีวิต (ในสถานพยาบาล - HOSxP)'],
                ['id' => 'hospGrp', 'label' => 'ในสถานพยาบาล · 21 กลุ่มสาเหตุ (รง.504)', 'icon' => 'fa-layer-group text-warning', 'data' => $diag_504, 'col' => 'กลุ่มสาเหตุ (504)', 'color' => '#f6c23e', 'cls' => 'text-warning', 'title' => 'กลุ่มสาเหตุการเสียชีวิต 504 (ในสถานพยาบาล - HOSxP)'],
                ['id' => 'outIcd', 'label' => 'นอกสถานพยาบาล · 20 อันดับโรค', 'icon' => 'fa-house-user text-secondary', 'data' => $out_icd10, 'col' => 'รหัส - ชื่อโรค (ICD10)', 'color' => '#6c757d', 'cls' => 'text-secondary', 'title' => 'อันดับโรคการเสียชีวิต (นอกสถานพยาบาล - HOSxP + AOPOD)'],
                ['id' => 'outGrp', 'label' => 'นอกสถานพยาบาล · 21 กลุ่มสาเหตุ (รง.504)', 'icon' => 'fa-layer-group text-secondary', 'data' => $out_504, 'col' => 'กลุ่มสาเหตุ (504)', 'color' => '#adb5bd', 'cls' => 'text-secondary', 'title' => 'กลุ่มสาเหตุการเสียชีวิต 504 (นอกสถานพยาบาล - HOSxP + AOPOD)'],
            ];
        @endphp

        <!-- Tabs -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs border-0 flex-wrap" id="deathTop20Tabs" role="tablist">
                @foreach ($panes as $p)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold {{ $loop->first ? 'active' : '' }}" id="{{ $p['id'] }}-tab"
                            data-bs-toggle="tab" data-bs-target="#{{ $p['id'] }}" type="button" role="tab"
                            aria-controls="{{ $p['id'] }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            <i class="fas {{ $p['icon'] }} me-1"></i> {{ $p['label'] }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="tab-content" id="deathTop20TabsContent">
            @foreach ($panes as $p)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $p['id'] }}" role="tabpanel"
                    aria-labelledby="{{ $p['id'] }}-tab">
                    <div class="row g-4 mb-4">
                        <div class="col-xl-6">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                                <div class="card-header bg-transparent border-0 pt-4 pb-0">
                                    <h6 class="m-0 fw-bold {{ $p['cls'] }}">{{ $p['title'] }}</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="chart_{{ $p['id'] }}"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                                <div class="card-body p-4">
                                    <div class="table-responsive">
                                        <table id="table_{{ $p['id'] }}" class="death-rank-table table table-hover align-middle" style="width:100%">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>อันดับ</th>
                                                    <th>{{ $p['col'] }}</th>
                                                    <th class="text-end">ชาย</th>
                                                    <th class="text-end">หญิง</th>
                                                    <th class="text-end">รวม</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($p['data'] as $index => $row)
                                                    <tr>
                                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                                        <td>
                                                            <div class="fw-medium text-dark">{{ $row->name }}</div>
                                                        </td>
                                                        <td class="text-end">{{ number_format($row->male) }}</td>
                                                        <td class="text-end">{{ number_format($row->female) }}</td>
                                                        <td class="text-end fw-bold {{ $p['cls'] }}">{{ number_format($row->sum) }}</td>
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
            @endforeach
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

                // --- Data from PHP ---
                const panes = {
                    hospIcd: { data: @json($diag_icd10), color: '#e74a3b' },
                    hospGrp: { data: @json($diag_504), color: '#f6c23e' },
                    outIcd: { data: @json($out_icd10), color: '#6c757d' },
                    outGrp: { data: @json($out_504), color: '#adb5bd' }
                };

                // Chart Config Helper
                function createBarChart(ctxId, dataLabel, dataArray, color) {
                    return new Chart(document.getElementById(ctxId), {
                        type: 'bar',
                        data: {
                            labels: dataArray.map(d => {
                                let text = d.name;
                                return text.length > 30 ? text.substring(0, 30) + '...' : text;
                            }),
                            datasets: [{
                                label: dataLabel,
                                data: dataArray.map(d => d.sum),
                                backgroundColor: color,
                                borderRadius: 4,
                                maxBarThickness: 15
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        title: (ctx) => dataArray[ctx[0].dataIndex].name
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'right',
                                    color: '#5a5c69',
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    },
                                    formatter: Math.round
                                }
                            },
                            scales: {
                                x: {
                                    display: false,
                                    beginAtZero: true,
                                    suggestedMax: Math.max(...dataArray.map(d => d.sum)) * 1.15
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            },
                            layout: {
                                padding: {
                                    right: 40
                                }
                            }
                        }
                    });
                }

                // Initialize Charts
                Object.entries(panes).forEach(([id, p]) => {
                    if (p.data.length > 0) createBarChart('chart_' + id, 'ผู้เสียชีวิต (ราย)', p.data, p.color);
                });

                // DataTables Init Function
                function initTable(id) {
                    return $(id).DataTable({
                        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                        buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success',
                            title: '{{ $title }}',
                            messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
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
                        responsive: true
                    });
                }

                $('.death-rank-table').each(function() {
                    initTable('#' + this.id);
                });

                // Fix DataTables width inside inactive tabs
                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    $.fn.dataTable.tables({
                        visible: true,
                        api: true
                    }).columns.adjust();
                });

                // Date Picker Init
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
                        allowInput: false,
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

                    // Update start_date and end_date based on year (พ.ศ.) change: 1 ม.ค. - 31 ธ.ค.
                    $('select[name="year"]').on('change', function() {
                        var selectedYear = parseInt($(this).val());
                        if(!isNaN(selectedYear)) {
                            var ceYear = selectedYear - 543; // Example: 2569 -> 2026
                            var startDateStr = ceYear + "-01-01";
                            var endDateStr = ceYear + "-12-31";
                            
                            setTimeout(() => {
                                if (typeof startPicker !== 'undefined' && startPicker) startPicker.setDate(startDateStr, true);
                                if (typeof endPicker !== 'undefined' && endPicker) endPicker.setDate(endDateStr, true);
                            }, 50);
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
