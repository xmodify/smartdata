@extends('layouts.app')

@section('title', 'SmartData | รายงานความคลาดเคลื่อนทางยา')

@section('topbar_actions')
    <a href="{{ route('backoffice.incident.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
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
            color: #4e73df;
            background: #f8f9fc;
        }
        .nav-tabs-custom .nav-link.active {
            color: #4e73df;
            background: #f8f9fc;
            border-bottom: 3px solid #4e73df;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-4">
        <!-- Header Box -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-pills text-primary me-2"></i>
                        รายงานความคลาดเคลื่อนทางยา (Medication Error)
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>

            <div>
                <form action="" method="GET" class="m-0 header-form-controls d-flex gap-2">
                    <span class="align-self-center fw-bold text-muted small">ช่วงวันที่:</span>
                    <div class="input-group input-group-sm shadow-sm" style="width: 150px; border-radius: 8px; overflow: hidden;">
                        <input type="text" name="start_date" id="start_date" class="form-control" value="{{ $start_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm" style="width: 150px; border-radius: 8px; overflow: hidden;">
                        <input type="text" name="end_date" id="end_date" class="form-control" value="{{ $end_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm" style="width: 220px; border-radius: 8px; overflow: hidden;">
                        <select class="form-select" name="budget_year">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs -->
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs border-0" id="medErrorTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-pane" type="button" role="tab"><i class="fas fa-chart-line me-1"></i> ภาพรวม Dashboard</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd-pane" type="button" role="tab"><i class="fas fa-user-injured me-1"></i> ผู้ป่วยนอก OPD</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd-pane" type="button" role="tab"><i class="fas fa-bed me-1"></i> ผู้ป่วยใน IPD</button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="medErrorTabsContent">
            <!-- Dashboard Tab -->
            <div class="tab-pane fade show active" id="dashboard-pane" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold text-dark mb-0">รายงานจำนวนความคลาดเคลื่อนทางยา แยกตามประเภทผู้ป่วยรายเดือน</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div style="position: relative; height: 300px;">
                                    <canvas id="chartMedMonthly"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold text-dark mb-0">Top 20 ยาที่เกิดความคลาดเคลื่อนสูงสุด (OPD)</h6>
                            </div>
                            <div class="card-body">
                                <div style="position: relative; height: 400px;">
                                    <canvas id="chartTopDrugOpd"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-white border-0 pt-4 px-4">
                                <h6 class="fw-bold text-dark mb-0">Top 20 ยาที่เกิดความคลาดเคลื่อนสูงสุด (IPD)</h6>
                            </div>
                            <div class="card-body">
                                <div style="position: relative; height: 400px;">
                                    <canvas id="chartTopDrugIpd"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OPD Tab -->
            <div class="tab-pane fade" id="opd-pane" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="fw-bold mb-0">ตารางวิเคราะห์ความคลาดเคลื่อนทางยา (OPD) แยกตามขั้นตอนและระดับความรุนแรง</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 text-center align-middle" style="font-size: 0.85rem;">
                                <thead class="table-light text-primary fw-bold">
                                    <tr>
                                        <th rowspan="2" class="text-start ps-4">เดือน</th>
                                        <th rowspan="2">รวม</th>
                                        <th colspan="5">แยกตามขั้นตอน (Process)</th>
                                        <th colspan="9">แยกตามระดับความรุนแรง (Level)</th>
                                    </tr>
                                    <tr>
                                        <th>Prescribing (1)</th>
                                        <th>Transcribing (2)</th>
                                        <th>Dispensing (3)</th>
                                        <th>Administering (4)</th>
                                        <th>Monitoring (5)</th>
                                        <th>A</th>
                                        <th>B</th>
                                        <th>C</th>
                                        <th>D</th>
                                        <th>E</th>
                                        <th>F</th>
                                        <th>G</th>
                                        <th>H</th>
                                        <th>I</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($med_error as $row)
                                        <tr>
                                            <td class="text-start fw-bold ps-4">{{ $row->month }}</td>
                                            <td class="fw-bold text-primary">{{ number_format($row->opd) }}</td>
                                            <td>{{ $row->po_1 }}</td>
                                            <td>{{ $row->po_2 }}</td>
                                            <td>{{ $row->po_3 }}</td>
                                            <td>{{ $row->po_4 }}</td>
                                            <td>{{ $row->po_5 }}</td>
                                            <td>{{ $row->o_a }}</td>
                                            <td>{{ $row->o_b }}</td>
                                            <td>{{ $row->o_c }}</td>
                                            <td>{{ $row->o_d }}</td>
                                            <td>{{ $row->o_e }}</td>
                                            <td>{{ $row->o_f }}</td>
                                            <td>{{ $row->o_g }}</td>
                                            <td>{{ $row->o_h }}</td>
                                            <td>{{ $row->o_i }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IPD Tab -->
            <div class="tab-pane fade" id="ipd-pane" role="tabpanel">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-success text-white py-3">
                        <h6 class="fw-bold mb-0">ตารางวิเคราะห์ความคลาดเคลื่อนทางยา (IPD) แยกตามขั้นตอนและระดับความรุนแรง</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 text-center align-middle" style="font-size: 0.85rem;">
                                <thead class="table-light text-success fw-bold">
                                    <tr>
                                        <th rowspan="2" class="text-start ps-4">เดือน</th>
                                        <th rowspan="2">รวม</th>
                                        <th colspan="5">แยกตามขั้นตอน (Process)</th>
                                        <th colspan="9">แยกตามระดับความรุนแรง (Level)</th>
                                    </tr>
                                    <tr>
                                        <th>Prescribing (1)</th>
                                        <th>Transcribing (2)</th>
                                        <th>Dispensing (3)</th>
                                        <th>Administering (4)</th>
                                        <th>Monitoring (5)</th>
                                        <th>A</th>
                                        <th>B</th>
                                        <th>C</th>
                                        <th>D</th>
                                        <th>E</th>
                                        <th>F</th>
                                        <th>G</th>
                                        <th>H</th>
                                        <th>I</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($med_error as $row)
                                        <tr>
                                            <td class="text-start fw-bold ps-4">{{ $row->month }}</td>
                                            <td class="fw-bold text-success">{{ number_format($row->ipd) }}</td>
                                            <td>{{ $row->pi_1 }}</td>
                                            <td>{{ $row->pi_2 }}</td>
                                            <td>{{ $row->pi_3 }}</td>
                                            <td>{{ $row->pi_4 }}</td>
                                            <td>{{ $row->pi_5 }}</td>
                                            <td>{{ $row->i_a }}</td>
                                            <td>{{ $row->i_b }}</td>
                                            <td>{{ $row->i_c }}</td>
                                            <td>{{ $row->i_d }}</td>
                                            <td>{{ $row->i_e }}</td>
                                            <td>{{ $row->i_f }}</td>
                                            <td>{{ $row->i_g }}</td>
                                            <td>{{ $row->i_h }}</td>
                                            <td>{{ $row->i_i }}</td>
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
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

    <script>
        $(document).ready(function() {
            Chart.register(ChartDataLabels);

            // Chart Med monthly
            const months = @json($med_error_m);
            const opdValues = @json($med_error_opd);
            const ipdValues = @json($med_error_ipd);

            new Chart(document.getElementById('chartMedMonthly'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'OPD Error',
                            data: opdValues,
                            borderColor: '#0268c7',
                            backgroundColor: 'rgba(2, 104, 199, 0.05)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'IPD Error',
                            data: ipdValues,
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.05)',
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#5a5c69',
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            formatter: Math.round
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: Math.max(...opdValues, ...ipdValues) * 1.2
                        }
                    }
                }
            });

            // OPD Top Drugs
            const opdDrugs = @json($med_error_drug);
            const opdTotals = @json($med_error_total);

            new Chart(document.getElementById('chartTopDrugOpd'), {
                type: 'bar',
                data: {
                    labels: opdDrugs.map(d => d.substring(0, 20)),
                    datasets: [{
                        label: 'จำนวนครั้ง',
                        data: opdTotals,
                        backgroundColor: '#0268c7'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            color: '#5a5c69',
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            formatter: Math.round
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            beginAtZero: true,
                            suggestedMax: Math.max(...opdTotals) * 1.2
                        }
                    }
                }
            });

            // IPD Top Drugs
            const ipdDrugs = @json($med_error_drug_ipd);
            const ipdTotals = @json($med_error_total_ipd);

            new Chart(document.getElementById('chartTopDrugIpd'), {
                type: 'bar',
                data: {
                    labels: ipdDrugs.map(d => d.substring(0, 20)),
                    datasets: [{
                        label: 'จำนวนครั้ง',
                        data: ipdTotals,
                        backgroundColor: '#198754'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            color: '#5a5c69',
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            formatter: Math.round
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            beginAtZero: true,
                            suggestedMax: Math.max(...ipdTotals) * 1.2
                        }
                    }
                }
            });

            // Calendar
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

                $('select[name="budget_year"]').on('change', function() {
                    const selectedYear = parseInt($(this).val());
                    if (!isNaN(selectedYear)) {
                        const startYear = selectedYear - 544;
                        const endYear = selectedYear - 543;
                        const startDateStr = startYear + "-10-01";
                        const endDateStr = endYear + "-09-30";
                        
                        setTimeout(() => {
                            if (startPicker) startPicker.setDate(startDateStr, true);
                            if (endPicker) endPicker.setDate(endDateStr, true);
                        }, 50);
                    }
                });
            }
        });
    </script>
@endpush
