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

        .nav-tabs-custom {
            background: #fff;
            border-radius: 12px;
            padding: 0.5rem 0.5rem 0 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
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
        .nav-tabs-custom .nav-link:hover { color: var(--primary-green); background: #f0fdf4; }
        .nav-tabs-custom .nav-link.active {
            color: var(--primary-green);
            background: #f0fdf4;
            border-bottom: 3px solid var(--primary-green);
            font-weight: bold;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        .summary-card {
            border-radius: 15px;
            border: none;
            transition: transform 0.3s;
        }
        .summary-card:hover { transform: translateY(-5px); }

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
            <div class="d-flex align-items-center">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-hand-holding-usd text-green me-2"></i> {{ $title }}</h5>
                    <div class="text-muted small mt-1">ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls d-flex align-items-center gap-2">
                    <input type="hidden" name="budget_year_changed" id="budget_year_changed" value="0">
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-green"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-green"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget">
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

        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs border-0" id="valueTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="hmed-tab" data-bs-toggle="tab" data-bs-target="#hmed" type="button" role="tab">
                        <i class="fas fa-leaf me-1"></i> รายการแพทย์แผนไทย
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other" type="button" role="tab">
                        <i class="fas fa-box-open me-1"></i> รายการอื่น ๆ/ยา
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="valueTabsContent">
            @php
                $tabs = [
                    ['id' => 'hmed', 'data' => $data_hmed, 'color' => '#10b981', 'icon' => 'fa-leaf', 'title' => 'แพทย์แผนไทย'],
                    ['id' => 'other', 'data' => $data_other, 'color' => '#3b82f6', 'icon' => 'fa-box-open', 'title' => 'อื่น ๆ/ยา']
                ];
            @endphp

            @foreach ($tabs as $index => $tab)
                <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="{{ $tab['id'] }}" role="tabpanel">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card summary-card shadow-sm text-white" style="background: linear-gradient(135deg, {{ $tab['color'] }} 0%, #000 150%);">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="small opacity-75">มูลค่ารวม (บาท)</div>
                                            <div class="h3 mb-0 fw-bold">฿{{ number_format(collect($tab['data'])->sum('sum_price'), 2) }}</div>
                                        </div>
                                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                            <i class="fas fa-coins fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card summary-card shadow-sm text-white" style="background: linear-gradient(135deg, #64748b 0%, #334155 100%);">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="small opacity-75">จำนวนหน่วยบริการทั้งหมด</div>
                                            <div class="h3 mb-0 fw-bold">{{ number_format(collect($tab['data'])->sum('qty')) }}</div>
                                        </div>
                                        <div class="bg-white bg-opacity-25 p-3 rounded-circle">
                                            <i class="fas fa-layer-group fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-6">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                                <div class="card-header bg-white py-3 border-0">
                                    <h6 class="fw-bold mb-0" style="color: {{ $tab['color'] }}"><i class="fas fa-chart-pie me-2"></i>Top 10 มูลค่าสูงสุด</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container" style="height: 500px;">
                                        <canvas id="chart-{{ $tab['id'] }}"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                                <div class="card-header bg-white py-3 border-0">
                                    <h6 class="fw-bold mb-0" style="color: {{ $tab['color'] }}"><i class="fas fa-list me-2"></i>รายการบริการทั้งหมด</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive p-3">
                                        <table class="table table-hover align-middle datatable-value" id="table-{{ $tab['id'] }}" style="width:100%">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">อันดับ</th>
                                                    <th class="text-center">รหัส</th>
                                                    <th>ชื่อรายการ</th>
                                                    <th class="text-center">จำนวน</th>
                                                    <th class="text-center">ชาย</th>
                                                    <th class="text-center">หญิง</th>
                                                    <th class="text-end">ราคา/หน่วย</th>
                                                    <th class="text-end">รวมมูลค่า (บาท)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($tab['data'] as $index => $row)
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
                                                                <span class="badge rounded-pill text-dark py-1 px-2" style="background: linear-gradient(135deg, #f0fdf4 0%, {{ $tab['color'] }} 100%) !important;">
                                                                    <i class="fas fa-crown"></i> 3
                                                                </span>
                                                            @else
                                                                <span class="text-muted fw-bold small">{{ $index + 1 }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                                                                {{ $row->icode }}
                                                            </span>
                                                        </td>
                                                        <td class="fw-bold small text-dark">{{ $row->name }}</td>
                                                        <td class="text-center fw-bold text-dark">{{ number_format($row->qty) }}</td>
                                                        <td class="text-center text-primary small" style="opacity: 0.8;">{{ number_format($row->male) }}</td>
                                                        <td class="text-center text-danger small" style="opacity: 0.8;">{{ number_format($row->female) }}</td>
                                                        <td class="text-end small">฿{{ number_format($row->unitprice, 2) }}</td>
                                                        <td class="text-end fw-bold" data-order="{{ $row->sum_price }}" style="color: {{ $tab['color'] }}">฿{{ number_format($row->sum_price, 2) }}</td>
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

                const tabsData = {
                    hmed: @json($data_hmed),
                    other: @json($data_other)
                };

                const colors = { hmed: '#10b981', other: '#3b82f6' };

                Object.keys(tabsData).forEach(key => {
                    const data = tabsData[key].slice(0, 10);
                    if (data.length > 0) {
                        new Chart(document.getElementById('chart-' + key), {
                            type: 'bar',
                            data: {
                                labels: data.map(d => d.name.length > 20 ? d.name.substring(0, 20) + '...' : d.name),
                                datasets: [{
                                    data: data.map(d => d.sum_price),
                                    backgroundColor: colors[key],
                                    borderRadius: 5
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
                                        formatter: (val) => '฿' + val.toLocaleString(),
                                        font: { size: 10, weight: 'bold' }
                                    }
                                },
                                scales: {
                                    x: { display: false },
                                    y: { ticks: { font: { size: 11 } } }
                                },
                                layout: { padding: { right: 70 } }
                            }
                        });
                    }
                });

                $('.datatable-value').each(function() {
                    $(this).DataTable({
                        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-2"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                        buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success',
                            title: 'มูลค่าการให้บริการแพทย์แผนไทย'
                        }],
                        pageLength: 10,
                        order: [[7, 'desc']],
                        language: {
                            search: "ค้นหา:", lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                        }
                    });
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
