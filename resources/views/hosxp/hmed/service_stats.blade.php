@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.hmed.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #10b981; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
        .nav-tabs-custom .nav-link:hover { color: var(--primary-green); background: #fff; }
        .nav-tabs-custom .nav-link.active {
            color: var(--primary-green);
            background: #fff;
            border-bottom: 3px solid var(--primary-green);
            font-weight: bold;
        }

        .table-stats thead th {
            vertical-align: middle;
            text-align: center;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 10px 8px !important;
            font-size: 0.8rem !important;
            color: #333 !important;
        }

        .table-stats tbody td {
            padding: 10px 8px !important;
            font-size: 0.8rem !important;
            vertical-align: middle;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            background-color: #f8fafc !important;
            z-index: 1;
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
                    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
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
                        <button type="submit" class="btn btn-green text-white px-3"><i class="fas fa-search"></i> ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>


        <div class="tab-content" id="reportTabsContent">
            <!-- OPD Tab -->
            <div class="tab-pane fade show active" id="opd" role="tabpanel">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-pastel-green py-3 border-0" style="border-radius: 15px 15px 0 0;">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-chart-bar me-2"></i>กราฟสรุปจำนวนผู้รับบริการแยกตามสิทธิ (OPD)</h6>
                            </div>
                            <div class="card-body">
                                <div id="chart-opd"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-pastel-green py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>ตารางข้อมูลรายเดือนแยกตามสิทธิ (OPD)</h6>
                                <button type="button" class="btn btn-sm btn-success px-2 shadow-sm btn-export-excel" data-target="#table-opd" style="font-size: 0.75rem; padding: 2px 8px;">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-stats mb-0" id="table-opd">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="sticky-col">เดือน</th>
                                                <th colspan="4" class="bg-light">รวมทั้งหมด</th>
                                                <th colspan="4" style="background-color: #e0f2fe;">ประกันสุขภาพ (UCS)</th>
                                                <th colspan="4" style="background-color: #fef9c3;">ข้าราชการ (OFC)</th>
                                                <th colspan="4" style="background-color: #dcfce7;">ประกันสังคม (SSS)</th>
                                                <th colspan="4" style="background-color: #f3e8ff;">อปท. (LGO)</th>
                                                <th colspan="4" style="background-color: #fee2e2;">ชำระเงิน/พรบ.</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">HN</th><th class="bg-light">Visit</th><th class="bg-light">แพทย์แผนไทย</th><th class="bg-light">ยา/อื่น ๆ</th>
                                                <th>HN</th><th>Visit</th><th>แพทย์แผนไทย</th><th>ยา/อื่น ๆ</th>
                                                <th>HN</th><th>Visit</th><th>แพทย์แผนไทย</th><th>ยา/อื่น ๆ</th>
                                                <th>HN</th><th>Visit</th><th>แพทย์แผนไทย</th><th>ยา/อื่น ๆ</th>
                                                <th>HN</th><th>Visit</th><th>แพทย์แผนไทย</th><th>ยา/อื่น ๆ</th>
                                                <th>HN</th><th>Visit</th><th>แพทย์แผนไทย</th><th>ยา/อื่น ๆ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totals = [
                                                    'hn' => 0, 'visit' => 0, 'service' => 0, 'other' => 0,
                                                    'hn_ucs' => 0, 'visit_ucs' => 0, 'service_ucs' => 0, 'other_ucs' => 0,
                                                    'hn_ofc' => 0, 'visit_ofc' => 0, 'service_ofc' => 0, 'other_ofc' => 0,
                                                    'hn_sss' => 0, 'visit_sss' => 0, 'service_sss' => 0, 'other_sss' => 0,
                                                    'hn_lgo' => 0, 'visit_lgo' => 0, 'service_lgo' => 0, 'other_lgo' => 0,
                                                    'hn_pay' => 0, 'visit_pay' => 0, 'service_pay' => 0, 'other_pay' => 0,
                                                ];
                                            @endphp
                                            @foreach($stats_opd as $row)
                                            <tr>
                                                <td class="sticky-col text-center fw-bold">{{ $row->month_name }}</td>
                                                <td class="text-center">{{ number_format($row->total_hn) }}</td>
                                                <td class="text-center text-primary fw-bold">{{ number_format($row->total_visit) }}</td>
                                                <td class="text-end">{{ number_format($row->total_sum_service, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->total_sum_other, 2) }}</td>
                                                
                                                <td class="text-center">{{ number_format($row->hn_ucs) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_ucs) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_ucs, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_other_ucs, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_ofc) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_ofc) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_ofc, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_other_ofc, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_sss) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_sss) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_sss, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_other_sss, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_lgo) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_lgo) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_lgo, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_other_lgo, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_pay) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_pay) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_pay, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_other_pay, 2) }}</td>
                                            </tr>
                                            @php
                                                $totals['hn'] += $row->total_hn; $totals['visit'] += $row->total_visit; $totals['service'] += $row->total_sum_service; $totals['other'] += $row->total_sum_other;
                                                $totals['hn_ucs'] += $row->hn_ucs; $totals['visit_ucs'] += $row->visit_ucs; $totals['service_ucs'] += $row->sum_price_service_ucs; $totals['other_ucs'] += $row->sum_price_other_ucs;
                                                $totals['hn_ofc'] += $row->hn_ofc; $totals['visit_ofc'] += $row->visit_ofc; $totals['service_ofc'] += $row->sum_price_service_ofc; $totals['other_ofc'] += $row->sum_price_other_ofc;
                                                $totals['hn_sss'] += $row->hn_sss; $totals['visit_sss'] += $row->visit_sss; $totals['service_sss'] += $row->sum_price_service_sss; $totals['other_sss'] += $row->sum_price_other_sss;
                                                $totals['hn_lgo'] += $row->hn_lgo; $totals['visit_lgo'] += $row->visit_lgo; $totals['service_lgo'] += $row->sum_price_service_lgo; $totals['other_lgo'] += $row->sum_price_other_lgo;
                                                $totals['hn_pay'] += $row->hn_pay; $totals['visit_pay'] += $row->visit_pay; $totals['service_pay'] += $row->sum_price_service_pay; $totals['other_pay'] += $row->sum_price_other_pay;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light fw-bold">
                                            <tr>
                                                <td class="sticky-col text-center">รวม</td>
                                                <td class="text-center">{{ number_format($totals['hn']) }}</td>
                                                <td class="text-center text-primary">{{ number_format($totals['visit']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['other'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_ucs']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_ucs']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_ucs'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['other_ucs'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_ofc']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_ofc']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_ofc'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['other_ofc'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_sss']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_sss']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_sss'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['other_sss'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_lgo']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_lgo']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_lgo'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['other_lgo'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_pay']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_pay']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_pay'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['other_pay'], 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
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
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            $(document).ready(function() {
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th", dateFormat: "Y-m-d", altInput: true, altFormat: "j M Y",
                        onReady: function(selectedDates, dateStr, instance) {
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

                // Shared chart options
                const chartOptions = {
                    chart: { 
                        type: 'bar', 
                        height: 450, 
                        toolbar: { show: true },
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '85%',
                            borderRadius: 4,
                            dataLabels: { position: 'top' }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        offsetY: -22,
                        style: { 
                            fontSize: '11px', 
                            fontWeight: 'bold',
                            colors: ["#444"] 
                        },
                        formatter: function(val) {
                            return val > 0 ? val : '';
                        }
                    },
                    stroke: { show: true, width: 1, colors: ['transparent'] },
                    fill: { opacity: 1 },
                    legend: {
                        show: true,
                        position: 'top',
                        horizontalAlign: 'center',
                    },
                    colors: ['#0ea5e9', '#eab308', '#22c55e', '#a855f7', '#ef4444'],
                    grid: { borderColor: '#f1f1f1', padding: { top: 10 } }
                };

                // OPD Chart
                const statsOpd = @json($stats_opd);
                const opdOptions = {
                    ...chartOptions,
                    series: [
                        { name: 'UCS', data: statsOpd.map(d => d.visit_ucs) },
                        { name: 'OFC', data: statsOpd.map(d => d.visit_ofc) },
                        { name: 'SSS', data: statsOpd.map(d => d.visit_sss) },
                        { name: 'LGO', data: statsOpd.map(d => d.visit_lgo) },
                        { name: 'Pay', data: statsOpd.map(d => d.visit_pay) }
                    ],
                    xaxis: { 
                        categories: statsOpd.map(d => d.month_name),
                        labels: { style: { fontSize: '11px', fontWeight: 600 } }
                    },
                    yaxis: { 
                        title: { text: 'จำนวนครั้ง (Visit)', style: { fontWeight: 600 } },
                        labels: { formatter: (val) => val.toLocaleString() }
                    },
                };
                new ApexCharts(document.querySelector("#chart-opd"), opdOptions).render();

                // Handle tab switch chart resize
                window.dispatchEvent(new Event('resize'));

                // Excel Export
                $('.btn-export-excel').on('click', function() {
                    const target = $(this).data('target');
                    const title = $(this).prev('h6').text().trim();
                    
                    // Create a temporary DataTable to handle export
                    const dt = $(target).DataTable({
                        retrieve: true,
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: false,
                        autoWidth: false,
                        dom: 'tB', // Only table and buttons
                        buttons: [{
                            extend: 'excelHtml5',
                            title: title,
                            messageTop: 'ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}',
                            filename: title + '_{{ date("Ymd") }}'
                        }]
                    });
                    dt.button(0).trigger();
                });
            });
        </script>
    @endpush
@endsection
