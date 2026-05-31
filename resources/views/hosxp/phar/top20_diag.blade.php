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

        .badge-rank {
            width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;
            border-radius: 50%; font-size: 0.75rem; font-weight: 700;
        }
        .rank-1 { background-color: #fef3c7; color: #d97706; }
        .rank-2 { background-color: #f1f5f9; color: #475569; }
        .rank-3 { background-color: #ffedd5; color: #c2410c; }

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
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-microscope text-primary me-2"></i> {{ $title }}</h5>
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

        <ul class="nav nav-tabs nav-tabs-custom" id="diagTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd-content" type="button" role="tab"><i class="fas fa-user-nurse me-2"></i>ผู้ป่วยนอก (OPD)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd-content" type="button" role="tab"><i class="fas fa-bed-pulse me-2"></i>ผู้ป่วยใน (IPD)</button>
            </li>
        </ul>

        <div class="tab-content" id="diagTabsContent">
            <!-- OPD Tab -->
            <div class="tab-pane fade show active" id="opd-content" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-chart-bar me-2"></i>กราฟแสดง 20 อันดับโรค (OPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div id="opdDiagChart" style="min-height: 450px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>ตารางข้อมูล (OPD)</h6>
                                <div id="opdExportBtn"></div>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="opdTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center">อันดับ</th>
                                                <th>ชื่อโรค</th>
                                                <th class="text-end">คน</th>
                                                <th class="text-end">ครั้ง</th>
                                                <th class="text-end">ต้นทุน</th>
                                                <th class="text-end">มูลค่า</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($top20_diag_opd as $index => $row)
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="badge-rank mx-auto {{ $index < 3 ? 'rank-'.($index+1) : '' }}">{{ $index + 1 }}</div>
                                                    </td>
                                                    <td><span class="fw-bold text-dark">{{ $row->name }}</span></td>
                                                    <td class="text-end">{{ number_format($row->hn_count) }}</td>
                                                    <td class="text-end">{{ number_format($row->visit_count) }}</td>
                                                    <td class="text-end">{{ number_format($row->sum_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-green">{{ number_format($row->sum_price, 2) }}</td>
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

            <!-- IPD Tab -->
            <div class="tab-pane fade" id="ipd-content" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-chart-bar me-2"></i>กราฟแสดง 20 อันดับโรค (IPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div id="ipdDiagChart" style="min-height: 450px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card card-custom h-100">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-table me-2"></i>ตารางข้อมูล (IPD)</h6>
                                <div id="ipdExportBtn"></div>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="ipdTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center">อันดับ</th>
                                                <th>ชื่อโรค</th>
                                                <th class="text-end">คน</th>
                                                <th class="text-end">ครั้ง</th>
                                                <th class="text-end">ต้นทุน</th>
                                                <th class="text-end">มูลค่า</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($top20_diag_ipd as $index => $row)
                                                <tr>
                                                    <td class="text-center">
                                                        <div class="badge-rank mx-auto {{ $index < 3 ? 'rank-'.($index+1) : '' }}">{{ $index + 1 }}</div>
                                                    </td>
                                                    <td><span class="fw-bold text-dark">{{ $row->name }}</span></td>
                                                    <td class="text-end">{{ number_format($row->hn_count) }}</td>
                                                    <td class="text-end">{{ number_format($row->visit_count) }}</td>
                                                    <td class="text-end">{{ number_format($row->sum_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-red">{{ number_format($row->sum_price, 2) }}</td>
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
                const chartBase = {
                    chart: { type: 'bar', height: 450, toolbar: { show: false } },
                    plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                    xaxis: { labels: { formatter: function(val) { return val.toLocaleString(); } } },
                    tooltip: { y: { formatter: function(val) { return val.toLocaleString() + " Visit"; } } }
                };

                // OPD Chart
                new ApexCharts(document.querySelector("#opdDiagChart"), {
                    ...chartBase,
                    series: [{ name: 'Visit', data: @json(array_column($top20_diag_opd, 'visit_count')) }],
                    xaxis: { ...chartBase.xaxis, categories: @json(array_column($top20_diag_opd, 'name')) },
                    colors: ['#10b981']
                }).render();

                // IPD Chart
                new ApexCharts(document.querySelector("#ipdDiagChart"), {
                    ...chartBase,
                    series: [{ name: 'Visit', data: @json(array_column($top20_diag_ipd, 'visit_count')) }],
                    xaxis: { ...chartBase.xaxis, categories: @json(array_column($top20_diag_ipd, 'name')) },
                    colors: ['#ef4444']
                }).render();

                const dtConfig = { pageLength: 10, language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json' }, dom: 'lrtip' };
                const opdTable = $('#opdTable').DataTable(dtConfig);
                const ipdTable = $('#ipdTable').DataTable(dtConfig);

                new $.fn.dataTable.Buttons(opdTable, {
                    buttons: [{ extend: 'excelHtml5', text: '<i class="fas fa-file-excel me-1"></i> Excel', className: 'btn btn-success btn-sm', title: '20 อันดับโรค (OPD)' }]
                }).container().appendTo($('#opdExportBtn'));

                new $.fn.dataTable.Buttons(ipdTable, {
                    buttons: [{ extend: 'excelHtml5', text: '<i class="fas fa-file-excel me-1"></i> Excel', className: 'btn btn-success btn-sm', title: '20 อันดับโรค (IPD)' }]
                }).container().appendTo($('#ipdExportBtn'));

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
