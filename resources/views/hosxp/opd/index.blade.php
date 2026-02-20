@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
<a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
    <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
</a>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .bg-pastel-blue { background-color: #f0f9ff; }
    .bg-pastel-green { background-color: #f0fdf4; }
    .bg-pastel-amber { background-color: #fffbeb; }
    .bg-pastel-purple { background-color: #faf5ff; }
    .bg-pastel-rose { background-color: #fff1f2; }
    
    .card-stats {
        border-radius: 15px;
        transition: transform 0.2s ease, shadow 0.2s ease;
    }
    .card-stats:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }
    
    .report-title-box {
        border-left: 5px solid #0d6efd;
        background: rgba(13, 110, 253, 0.05);
        border-radius: 0 10px 10px 0;
    }
    
    .table-fixed-header thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }
    
    .trend-badge-up {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.75rem;
    }
    
    .chart-container {
        min-height: 350px;
    }
    
    .stat-val {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.2;
    }
    
    .label-small {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        font-weight: 600;
    }

    /* Table custom styling to match screenshot */
    .table-opd-stats {
        font-size: 0.85rem;
    }
    .table-opd-stats th {
        vertical-align: middle;
        text-align: center;
        background-color: #e9ecef;
        border-bottom: 2px solid #dee2e6;
    }
    .table-opd-stats td {
        vertical-align: middle;
    }
    .col-visit { background-color: rgba(13, 110, 253, 0.02); }
    .col-income { background-color: rgba(25, 135, 84, 0.02); }
    .text-num { font-family: 'Inter', sans-serif; text-align: right; }
    
    .header-group {
        border-bottom: 2px solid #cbd5e1 !important;
    }
    
    .input-group-date {
        width: 160px !important;
    }
    .input-group-budget {
        width: 200px !important;
    }
    .header-form-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3 px-3">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="ps-3 py-1">
            <h4 class="fw-bold mb-0 text-dark">
                <i class="fas fa-stethoscope text-primary me-2"></i> {{ $title }}
            </h4>
            <span class="text-muted small">สรุปผลการดำเนินงานและสถิติบริการ ปีงบประมาณ <strong>{{ $budget_year }}</strong></span>
        </div>
        
        <div class="d-flex align-items-center gap-2">
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
                    <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;" onchange="document.getElementById('start_date').value=''; document.getElementById('end_date').value='';">
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

    <!-- Quick Stats Summary Cards -->
    <div class="row g-3 mb-4">
        @php
            $total_visit = array_sum($visits);
            $total_income = array_sum($incomes);
            $avg_visit = count($visits) > 0 ? $total_visit / count($visits) : 0;
        @endphp
        <div class="col-md-4">
            <div class="card card-stats border-0 shadow-sm bg-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="bg-pastel-blue p-2 rounded-3">
                            <i class="fas fa-users text-primary fa-lg"></i>
                        </div>
                        <span class="trend-badge-up"><i class="fas fa-chart-line me-1"></i> +5.2%</span>
                    </div>
                    <div class="label-small mb-1">จำนวนผู้รับบริการทั้งหมด</div>
                    <div class="stat-val text-dark">{{ number_format($total_visit) }} <span class="fs-6 fw-normal text-muted">ครั้ง</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats border-0 shadow-sm bg-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="bg-pastel-green p-2 rounded-3">
                            <i class="fas fa-hand-holding-usd text-success fa-lg"></i>
                        </div>
                        <span class="trend-badge-up"><i class="fas fa-chart-line me-1"></i> +8.1%</span>
                    </div>
                    <div class="label-small mb-1">รายได้จากการบริการรวม</div>
                    <div class="stat-val text-dark">{{ number_format($total_income, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats border-0 shadow-sm bg-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="bg-pastel-amber p-2 rounded-3">
                            <i class="fas fa-calendar-check text-warning fa-lg"></i>
                        </div>
                    </div>
                    <div class="label-small mb-1">เฉลี่ยต่อเดือน</div>
                    <div class="stat-val text-dark">{{ number_format($avg_visit) }} <span class="fs-6 fw-normal text-muted">ครั้ง</span></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Monthly Visit & Income Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">กราฟจำนวนผู้รับบริการและรายได้รายเดือน</h5>
                    <p class="text-muted small">เปรียบเทียบปริมาณงานและรายได้ในปีงบประมาณ {{ $budget_year }}</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="monthlyChart" class="chart-container"></div>
                </div>
            </div>
        </div>
        
        <!-- Monthly OP-PP Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">แยกประเภท OP-PP รายเดือน</h5>
                    <p class="text-muted small">สัดส่วนผู้รับบริการทั่วไป (OP) และส่งเสริม (PP)</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="opPpChart" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h5 class="fw-bold mb-0">ตารางสถิติผู้รับบริการแยกตามกลุ่มสิทธิการรักษาหลัก</h5>
                        <p class="text-muted small mb-0">แสดงจำนวนการรับบริการ (Visit) และรายได้ (Income) แบ่งตามกองทุนหลัก</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="badge bg-info-subtle text-info p-2 rounded-3 border">
                            <i class="fas fa-info-circle me-1"></i> ข้อมูลกรองตามปีงบประมาณ {{ $budget_year }}
                        </div>
                        <button class="btn btn-success btn-sm shadow-sm px-3" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-opd-stats mb-0" id="opdStatsTable">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle border-end bg-light">เดือน</th>
                                    <th colspan="2" class="header-group bg-pastel-blue text-primary">ทั้งหมด</th>
                                    <th colspan="2" class="header-group">ประกันสุขภาพ ใน CUP</th>
                                    <th colspan="2" class="header-group">ประกันสุขภาพ นอก CUP</th>
                                    <th colspan="2" class="header-group">ข้าราชการ</th>
                                    <th colspan="2" class="header-group">ประกันสังคม</th>
                                    <th colspan="2" class="header-group">อปท.</th>
                                    <th colspan="2" class="header-group">ต่างด้าว</th>
                                    <th colspan="2" class="header-group">Stateless (STP)</th>
                                    <th colspan="2" class="header-group">ชำระเงิน/อื่นๆ</th>
                                </tr>
                                <tr>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                    <th class="col-visit">Visit</th>
                                    <th class="col-income">Income</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($visit_month as $row)
                                <tr>
                                    <td class="fw-bold text-center border-end bg-light">{{ $row->month }}</td>
                                    <td class="text-num col-visit fw-bold">{{ number_format($row->visit) }}</td>
                                    <td class="text-num col-income fw-bold text-success">{{ number_format($row->income, 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format($row->ucs_incup) }}</td>
                                    <td class="text-num text-success">{{ number_format($row->ucs_incup_income, 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format($row->ucs_outcup) }}</td>
                                    <td class="text-num text-success">{{ number_format($row->ucs_outcup_income, 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format($row->ofc) }}</td>
                                    <td class="text-num text-success">{{ number_format($row->ofc_income, 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format($row->sss) }}</td>
                                    <td class="text-num text-success">{{ number_format($row->sss_income, 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format($row->lgo) }}</td>
                                    <td class="text-num text-success">{{ number_format($row->lgo_income, 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format($row->fss) }}</td>
                                    <td class="text-num text-success">{{ number_format($row->fss_income, 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format($row->stp) }}</td>
                                    <td class="text-num text-success">{{ number_format($row->stp_income, 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format($row->pay) }}</td>
                                    <td class="text-num text-success">{{ number_format($row->pay_income, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold border-top-2">
                                <tr>
                                    <td class="text-center">รวม</td>
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'visit'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'income')), 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'ucs_incup'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'ucs_incup_income')), 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'ucs_outcup'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'ucs_outcup_income')), 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'ofc'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'ofc_income')), 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'sss'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'sss_income')), 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'lgo'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'lgo_income')), 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'fss'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'fss_income')), 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'stp'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'stp_income')), 2) }}</td>
                                    
                                    <td class="text-num">{{ number_format(array_sum(array_column($visit_month, 'pay'))) }}</td>
                                    <td class="text-num text-success">{{ number_format(array_sum(array_column($visit_month, 'pay_income')), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        // Monthly Chart Data
        var monthlyOptions = {
            series: [{
                name: 'จำนวนผู้ป่วย (ครั้ง)',
                type: 'column',
                data: @json($visits)
            }, {
                name: 'จำนวนคน (HN)',
                type: 'column',
                data: @json($hns)
            }, {
                name: 'รายได้ (บาท)',
                type: 'line',
                data: @json($incomes)
            }],
            chart: {
                height: 350,
                type: 'line',
                stacked: false,
                toolbar: { show: false }
            },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [0, 1],
                offsetY: -20,
                style: { 
                    fontSize: '10px', 
                    fontWeight: 'bold',
                    colors: ["#304758"] 
                },
                formatter: function (val) { return val.toLocaleString(); }
            },
            stroke: { 
                show: true,
                width: [0, 0, 4], 
                curve: 'smooth'
            },
            plotOptions: { 
                bar: { 
                    columnWidth: '60%', 
                    borderRadius: 4
                } 
            },
            colors: ['#007bff', '#6f42c1', '#28a745'], // Vivid Blue, Vivid Purple, Vivid Green
            fill: { opacity: 1 },
            labels: @json($months),
            xaxis: { type: 'category' },
            yaxis: [{
                title: { text: 'จำนวนผู้ป่วย (ครั้ง/คน)', style: { color: '#007bff', fontWeight: 'bold' } },
            }, {
                show: false
            }, {
                opposite: true,
                title: { text: 'รายได้ (บาท)', style: { color: '#28a745', fontWeight: 'bold' } }
            }],
            tooltip: {
                shared: true,
                intersect: false,
                y: { formatter: function (y) { return y !== undefined ? y.toLocaleString() : y; } }
            },
            legend: { position: 'top', horizontalAlign: 'right' }
        };

        var monthlyChart = new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions);
        monthlyChart.render();

        // OP vs PP Chart Data
        var opPpOptions = {
            series: [{
                name: 'ทั่วไป (OP)',
                data: @json($visit_ops)
            }, {
                name: 'ส่งเสริม (PP)',
                data: @json($visit_pps)
            }],
            chart: {
                type: 'bar',
                height: 350,
                stacked: true,
                toolbar: { show: false }
            },
            plotOptions: { 
                bar: { 
                    columnWidth: '70%', 
                    borderRadius: 4,
                    dataLabels: { position: 'center' }
                } 
            },
            colors: ['#f97316', '#06b6d4'],
            dataLabels: {
                enabled: true,
                style: { 
                    fontSize: '11px', 
                    fontWeight: 'bold',
                    colors: ['#fff'] 
                },
                formatter: function (val) { return val > 0 ? val.toLocaleString() : ''; }
            },
            xaxis: {
                categories: @json($months),
                title: { text: 'เดือน' }
            },
            yaxis: { title: { text: 'จำนวนครั้ง' } },
            tooltip: {
                y: { formatter: function (val) { return val.toLocaleString() + " ครั้ง"; } }
            },
            legend: { position: 'top' }
        };

        var opPpChart = new ApexCharts(document.querySelector("#opPpChart"), opPpOptions);
        opPpChart.render();
    });

    function exportToExcel() {
        const table = document.getElementById("opdStatsTable");
        let csv = [];
        for (let i = 0; i < table.rows.length; i++) {
            let row = [], cols = table.rows[i].querySelectorAll("td, th");
            for (let j = 0; j < cols.length; j++) {
                let text = cols[j].innerText.replace(/,/g, ""); // Remove commas from numbers
                row.push('"' + text + '"');
            }
            csv.push(row.join(","));
        }
        const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "opd_stats_{{ $budget_year }}.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
@endpush
@endsection
