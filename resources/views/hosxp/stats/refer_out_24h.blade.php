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
    :root {
        --primary-gradient: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        --secondary-gradient: linear-gradient(135deg, #858796 0%, #60616f 100%);
        --success-gradient: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        --info-gradient: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        --warning-gradient: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        --danger-gradient: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
    }

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

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    .btn-excel {
        background: #198754 !important;
        color: white !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 0.4rem 1rem !important;
        font-weight: 600 !important;
        transition: all 0.3s !important;
    }

    .btn-excel:hover {
        background: #17a673 !important;
        transform: scale(1.05);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-4">
    <!-- Header -->
    <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center report-title-box">
            <div class="ps-3 py-1">
                <h5 class="text-dark mb-0 fw-bold">
                    <i class="fas fa-clock text-primary me-2"></i>
                    {{ $title }}
                </h5>
                <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
            </div>
        </div>
        
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="m-0 header-form-controls">
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
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
                    <button type="submit" class="btn btn-primary px-3" style="font-size: 0.8rem;">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card text-white h-100 shadow-sm" style="background: var(--info-gradient);">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small opacity-75">จำนวนการส่งต่อทั้งหมด</div>
                            <div class="h2 mb-0 fw-bold">{{ number_format(count($report_data)) }}</div>
                            <div class="small mt-2"><i class="fas fa-clock"></i> ภายใน {{ $hours }} ชม.</div>
                        </div>
                        <div class="bg-white bg-opacity-25 p-3 rounded-circle align-self-center">
                            <i class="fas fa-exchange-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="m-0 fw-bold text-primary">แนวโน้มรายเดือน (ตามช่วงเวลาที่เลือก)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-3 pb-0">
                    <h6 class="m-0 fw-bold text-info">แนวโน้ม 5 ปี ย้อนหลัง (ปีงบประมาณ)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="yearlyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Data Table -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="reportTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="bg-light text-primary">
                        <tr>
                            <th>AN/HN</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>Admit PDX</th>
                            <th>Refer PDX</th>
                            <th>รพ.ที่ส่งต่อ</th>
                            <th>เวลา Admit</th>
                            <th>เวลา Refer</th>
                            <th class="text-center">ชม.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report_data as $row)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row->an }}</div>
                                    <div class="small text-muted">HN: {{ $row->hn }}</div>
                                </td>
                                <td><div class="fw-medium">{{ $row->ptname }}</div></td>
                                <td><span class="badge bg-info bg-opacity-10 text-info">{{ $row->admit_pdx }}</span></td>
                                <td><span class="badge bg-warning bg-opacity-10 text-warning text-dark">{{ $row->refer_pdx }}</span></td>
                                <td>{{ $row->refer_hos }}</td>
                                <td>
                                    <div class="small">{{ DateThai($row->regdate) }}</div>
                                    <div class="fw-bold">{{ $row->regtime }}</div>
                                </td>
                                <td>
                                    <div class="small">{{ DateThai($row->refer_date) }}</div>
                                    <div class="fw-bold">{{ $row->refer_time }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3">{{ $row->admit_hour }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

        const monthlyData = @json($monthly_trend);
        const yearlyData = @json($yearly_trend);

        // Monthly Trend
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyData.map(d => d.label),
                datasets: [{
                    label: 'จำนวนส่งต่อ (ราย)',
                    data: monthlyData.map(d => d.total_count),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#4e73df',
                        font: { weight: 'bold' }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } }
                }
            }
        });

        // Yearly Trend
        new Chart(document.getElementById('yearlyChart'), {
            type: 'bar',
            data: {
                labels: yearlyData.map(d => 'ปีงบ ' + d.year_be),
                datasets: [{
                    label: 'จำนวนส่งต่อ (ราย)',
                    data: yearlyData.map(d => d.total_count),
                    backgroundColor: '#36ccba',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#36ccba',
                        font: { weight: 'bold' }
                    }
                },
                scales: {
                    y: { beginAtZero: true, grid: { display: false } }
                }
            }
        });

        // DataTable
        $('#reportTable').DataTable({
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
            initComplete: function() {
                $("div.dt-left-info").html('<div class="text-primary fw-bold"><i class="fas fa-calendar-alt me-1"></i> ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>');
            }
        });

        // Flatpickr
        if (typeof flatpickr !== 'undefined') {
            const yearOffset = 543;
            const commonConfig = {
                locale: "th", dateFormat: "Y-m-d", altInput: true, altFormat: "j M Y", allowInput: false,
                onReady: function(s, d, i) {
                    if (i.altInput) {
                        const date = i.selectedDates[0] || new Date(i.input.value);
                        i.altInput.value = `${date.getDate()} ${i.l10n.months.shorthand[date.getMonth()]} ${date.getFullYear() + yearOffset}`;
                    }
                    const todayBtn = document.createElement("div");
                    todayBtn.innerHTML = "วันนี้";
                    todayBtn.className = "text-primary fw-bold text-center py-2 border-top";
                    todayBtn.style.cursor = "pointer";
                    todayBtn.addEventListener("click", () => {
                        i.setDate(new Date(), true);
                        i.close();
                    });
                    i.calendarContainer.appendChild(todayBtn);
                },
                onChange: function(s, d, i) {
                    if (i.altInput && s.length > 0) {
                        const date = s[0];
                        setTimeout(() => {
                            i.altInput.value = `${date.getDate()} ${i.l10n.months.shorthand[date.getMonth()]} ${date.getFullYear() + yearOffset}`;
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
