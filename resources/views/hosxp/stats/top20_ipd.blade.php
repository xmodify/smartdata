@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
<a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm" style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
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
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
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
        width: 200px !important;
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
    button.dt-button.btn-excel:hover {
        background-color: #157347 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(25, 135, 84, 0.3) !important;
    }
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    .dataTables_filter input {
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        padding: 5px 12px !important;
        font-size: 0.85rem !important;
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
    .nav-tabs-custom .nav-link:hover {
        color: #1cc88a;
        background: #f8f9fc;
    }
    .nav-tabs-custom .nav-link.active {
        color: #1cc88a;
        background: #f8f9fc;
        border-bottom: 3px solid #1cc88a;
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
                    <i class="fas fa-bed text-success me-2"></i>
                    {{ $title }}
                </h5>
                <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
            </div>
        </div>
        
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="m-0 header-form-controls">
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius: 8px; overflow: hidden;">
                    <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
                        @foreach ($budget_year_select as $row)
                            <option value="{{ $row->LEAVE_YEAR_ID }}"
                                {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                {{ $row->LEAVE_YEAR_NAME }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-success px-3" style="font-size: 0.8rem;">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white" style="border-radius: 15px; background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">จำนวนการรับบริการทั้งหมด</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format(collect($diag_icd10)->sum('sum')) }}</div>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white" style="border-radius: 15px; background: linear-gradient(135deg, #36b9cc 0%, #1a8a97 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">ชาย / หญิง</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format(collect($diag_icd10)->sum('male')) }} / {{ number_format(collect($diag_icd10)->sum('female')) }}</div>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle">
                            <i class="fas fa-venus-mars fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white" style="border-radius: 15px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">ค่า Lab รวม</div>
                            <div class="h4 mb-0 fw-bold">฿{{ number_format(collect($diag_icd10)->sum('inc_lab'), 2) }}</div>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle">
                            <i class="fas fa-flask fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-white" style="border-radius: 15px; background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">ค่ายา รวม</div>
                            <div class="h4 mb-0 fw-bold">฿{{ number_format(collect($diag_icd10)->sum('inc_drug'), 2) }}</div>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle">
                            <i class="fas fa-pills fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs border-0" id="ipdTop20Tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="icd10-tab" data-bs-toggle="tab" data-bs-target="#icd10" type="button" role="tab" aria-controls="icd10" aria-selected="true">
                    <i class="fas fa-clipboard-list me-1 text-success"></i> 20 อันดับโรค (Primary Diagnosis)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="grp505-tab" data-bs-toggle="tab" data-bs-target="#grp505" type="button" role="tab" aria-controls="grp505" aria-selected="false">
                    <i class="fas fa-layer-group me-1 text-info"></i> กลุ่มโรค (75 กลุ่มโรค) (รง.505)
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="ipdTop20TabsContent">
        <!-- ICD10 Tab -->
        <div class="tab-pane fade show active" id="icd10" role="tabpanel" aria-labelledby="icd10-tab">
            <div class="row g-4 mb-4">
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-header bg-transparent border-0 pt-4 pb-0">
                            <h6 class="m-0 fw-bold text-success"><i class="fas fa-chart-bar me-2"></i> 20 อันดับโรคผู้ป่วยใน <span class="text-muted small fw-normal ms-2">ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</span></h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="chartIcd10"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="tableIcd10" class="table table-hover align-middle" style="width:100%">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>อันดับ</th>
                                            <th>ICD10 / ชื่อโรค</th>
                                            <th class="text-center">จำนวน</th>
                                            <th class="text-center">ชาย</th>
                                            <th class="text-center">หญิง</th>
                                            <th class="text-end">ค่า Lab</th>
                                            <th class="text-end">ค่ายา</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($diag_icd10 as $index => $row)
                                            <tr>
                                                <td class="text-center fw-bold text-muted">
                                                    @if($index < 3 && $row->sum > 0)
                                                        <span class="badge rounded-pill bg-warning text-dark px-2">
                                                            <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                        </span>
                                                    @else
                                                        {{ $index + 1 }}
                                                    @endif
                                                </td>
                                                <td><div class="fw-medium text-dark text-truncate" style="max-width: 250px;" title="{{ $row->name }}">{{ $row->name }}</div></td>
                                                <td class="text-center fw-bold text-success">{{ number_format($row->sum) }}</td>
                                                <td class="text-center text-primary">{{ number_format($row->male) }}</td>
                                                <td class="text-center text-danger">{{ number_format($row->female) }}</td>
                                                <td class="text-end">฿{{ number_format($row->inc_lab, 2) }}</td>
                                                <td class="text-end">฿{{ number_format($row->inc_drug, 2) }}</td>
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

        <!-- 505 Tab -->
        <div class="tab-pane fade" id="grp505" role="tabpanel" aria-labelledby="grp505-tab">
            <div class="row g-4 mb-4">
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-header bg-transparent border-0 pt-4 pb-0">
                            <h6 class="m-0 fw-bold text-info"><i class="fas fa-chart-bar me-2"></i> อันดับสาเหตุ รง.505 <span class="text-muted small fw-normal ms-2">ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</span></h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="chart505"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table id="table505" class="table table-hover align-middle" style="width:100%">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>อันดับ</th>
                                            <th>ชื่อตาราง 505</th>
                                            <th class="text-center">จำนวน</th>
                                            <th class="text-center">ชาย</th>
                                            <th class="text-center">หญิง</th>
                                            <th class="text-end">ค่า Lab</th>
                                            <th class="text-end">ค่ายา</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($diag_505 as $index => $row)
                                            <tr>
                                                <td class="text-center fw-bold text-muted">
                                                    @if($index < 3 && $row->sum > 0)
                                                        <span class="badge rounded-pill bg-warning text-dark px-2">
                                                            <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                        </span>
                                                    @else
                                                        {{ $index + 1 }}
                                                    @endif
                                                </td>
                                                <td><div class="fw-medium text-dark text-truncate" style="max-width: 250px;" title="{{ $row->name }}">{{ $row->name }}</div></td>
                                                <td class="text-center fw-bold text-info">{{ number_format($row->sum) }}</td>
                                                <td class="text-center text-primary">{{ number_format($row->male) }}</td>
                                                <td class="text-center text-danger">{{ number_format($row->female) }}</td>
                                                <td class="text-end">฿{{ number_format($row->inc_lab, 2) }}</td>
                                                <td class="text-end">฿{{ number_format($row->inc_drug, 2) }}</td>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<script>
    $(document).ready(function() {
        Chart.register(ChartDataLabels);

        // --- Data from PHP ---
        const icd10Data = @json($diag_icd10);
        const grp505Data = @json($diag_505);

        // Chart Config Helper
        function createBarChart(ctxId, dataLabel, dataArray, color) {
            return new Chart(document.getElementById(ctxId), {
                type: 'bar',
                data: {
                    labels: dataArray.map(d => {
                        let text = d.tname || d.name; // Use tname if available, else name
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
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                title: (ctx) => {
                                    const d = dataArray[ctx[0].dataIndex];
                                    return d.tname || d.name;
                                }
                            }
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            color: '#5a5c69',
                            font: { weight: 'bold', size: 11 },
                            formatter: Math.round
                        }
                    },
                    scales: {
                        x: { display: false, beginAtZero: true, suggestedMax: Math.max(...dataArray.map(d => parseInt(d.sum))) * 1.15 },
                        y: { 
                            grid: { display: false },
                            ticks: { font: { size: 11 } }
                        }
                    },
                    layout: { padding: { right: 40 } }
                }
            });
        }

        // Initialize Charts (Top 20 limiting for the view)
        if(icd10Data.length > 0) createBarChart('chartIcd10', 'ผู้ป่วย (คน)', icd10Data.slice(0, 20), '#1cc88a');
        if(grp505Data.length > 0) createBarChart('chart505', 'ผู้ป่วย (คน)', grp505Data.slice(0, 20), '#36b9cc');

        // DataTables Init Function
        function initTable(id) {
            return $(id).DataTable({
                language: { url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
                pageLength: 10,
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"dt-left-info"> <"d-flex gap-2"fB>>rtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn-excel',
                    title: '{{ $title }}',
                    messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
                }],
                responsive: true,
                initComplete: function() {
                    $("div.dt-left-info").html('<div class="text-primary fw-bold"><i class="fas fa-calendar-alt me-1"></i> ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>');
                }
            });
        }

        const dtIcd10 = initTable('#tableIcd10');
        const dt505 = initTable('#table505');

        // Fix DataTables width inside inactive tabs
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
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
                    const todayBtn = document.createElement("div");
                    todayBtn.innerHTML = "วันนี้";
                    todayBtn.className = "text-primary fw-bold text-center py-2 border-top";
                    todayBtn.style.cursor = "pointer";
                    todayBtn.addEventListener("click", () => {
                        instance.setDate(new Date(), true);
                        instance.close();
                    });
                    instance.calendarContainer.appendChild(todayBtn);
                    
                    if (instance.altInput) {
                        const originalValue = instance.altInput.value;
                        if (originalValue) {
                            const date = instance.selectedDates[0] || new Date(instance.input.value);
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
                        setTimeout(() => {
                            const day = date.getDate();
                            const month = instance.l10n.months.shorthand[date.getMonth()];
                            const year = date.getFullYear() + yearOffset;
                            instance.altInput.value = `${day} ${month} ${year}`;
                        }, 10);
                    }
                }
            };

            flatpickr("#start_date", commonConfig);
            flatpickr("#end_date", commonConfig);
        }
    });
</script>
@endpush
@endsection
