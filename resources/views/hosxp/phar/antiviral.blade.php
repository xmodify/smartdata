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

        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-budget { width: 100% !important; }
        }

        /* Custom Tabs Styling */
        .nav-tabs-custom { border-bottom: 2px solid #f0f0f0; margin-bottom: 1.5rem; }
        .nav-tabs-custom .nav-link {
            border: none;
            color: #64748b;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            transition: all 0.3s;
            position: relative;
        }
        .nav-tabs-custom .nav-link#opd-tab.active {
            color: #10b981;
            background: transparent;
        }
        .nav-tabs-custom .nav-link#opd-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #10b981;
        }
        .nav-tabs-custom .nav-link#ipd-tab.active {
            color: #ef4444;
            background: transparent;
        }
        .nav-tabs-custom .nav-link#ipd-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #ef4444;
        }

        .card-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            border-bottom: 1px solid #e2e8f0;
        }

        .text-green { color: #10b981 !important; }
        .text-red { color: #ef4444 !important; }
        .bg-pastel-green { background-color: #ecfdf5 !important; }
        .bg-pastel-red { background-color: #fef2f2 !important; }

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            color: #10b981;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }

        .flatpickr-today-button:hover {
            background: #fdfaff;
            color: #059669;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-virus-slash text-primary me-2"></i> {{ $title }}
                    </h5>
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

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white h-100" style="border-radius: 15px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-light p-2 rounded-3"><i class="fas fa-prescription-bottle-alt text-primary fa-lg"></i></div>
                        </div>
                        <div class="label-small mb-1 text-muted small fw-bold">จำนวนเคสรวม (สิทธิประกันสังคม)</div>
                        <div class="stat-val text-dark fw-bold h4">{{ number_format(count($antiviral_opd) + count($antiviral_ipd)) }} <span class="fs-6 fw-normal text-muted">ราย</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white h-100" style="border-radius: 15px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-green p-2 rounded-3"><i class="fas fa-user-nurse text-green fa-lg"></i></div>
                        </div>
                        <div class="label-small mb-1 text-muted small fw-bold">ผู้ป่วยนอก (OPD)</div>
                        <div class="stat-val text-dark fw-bold h4">{{ number_format(count($antiviral_opd)) }} <span class="fs-6 fw-normal text-muted">ราย</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm bg-white h-100" style="border-radius: 15px;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-red p-2 rounded-3"><i class="fas fa-bed-pulse text-red fa-lg"></i></div>
                        </div>
                        <div class="label-small mb-1 text-muted small fw-bold">ผู้ป่วยใน (IPD)</div>
                        <div class="stat-val text-dark fw-bold h4">{{ number_format(count($antiviral_ipd)) }} <span class="fs-6 fw-normal text-muted">ราย</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content with Tabs -->
        <ul class="nav nav-tabs nav-tabs-custom" id="antiviralTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd-content" type="button" role="tab"><i class="fas fa-user-nurse me-2"></i>ผู้ป่วยนอก (OPD)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd-content" type="button" role="tab"><i class="fas fa-bed-pulse me-2"></i>ผู้ป่วยใน (IPD)</button>
            </li>
        </ul>

        <div class="tab-content" id="antiviralTabsContent">
            <!-- OPD Tab -->
            <div class="tab-pane fade show active" id="opd-content" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>ผู้ป่วยนอก</h6>
                                <a href="{{ route('hosxp.phar.antiviral.pdf', ['type' => 'opd', 'start_date' => $start_date, 'end_date' => $end_date, 'budget_year' => $budget_year]) }}" 
                                   target="_blank" class="btn btn-danger btn-sm fw-bold shadow-sm" style="border-radius: 8px;">
                                   <i class="fas fa-print me-1"></i> พิมพ์
                                </a>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="opdTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>วันที่ได้รับ</th>
                                                <th>HN</th>
                                                <th>CID</th>
                                                <th>ชื่อ-สกุล</th>
                                                <th class="text-center" style="width: 80px;">อายุ</th>
                                                <th>สิทธิการรักษา</th>
                                                <th>รายการยา</th>
                                                <th class="text-center" style="width: 100px;">จำนวน</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $idx_opd = 1; @endphp
                                            @foreach ($antiviral_opd as $row)
                                                <tr>
                                                    <td class="text-center">{{ $idx_opd++ }}</td>
                                                    <td>{{ DateThai($row->vstdate) }}</td>
                                                    <td>{{ $row->hn }}</td>
                                                    <td>{{ $row->cid }}</td>
                                                    <td><span class="fw-bold text-dark">{{ $row->ptname }}</span></td>
                                                    <td class="text-center">{{ $row->age_y }}</td>
                                                    <td>{{ $row->pttype_name }}</td>
                                                    <td><span class="text-dark">{{ $row->drug }}</span></td>
                                                    <td class="text-center fw-bold text-primary">{{ number_format($row->qty) }}</td>
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
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-table me-2"></i>ผู้ป่วยใน</h6>
                                <a href="{{ route('hosxp.phar.antiviral.pdf', ['type' => 'ipd', 'start_date' => $start_date, 'end_date' => $end_date, 'budget_year' => $budget_year]) }}" 
                                   target="_blank" class="btn btn-danger btn-sm fw-bold shadow-sm" style="border-radius: 8px;">
                                   <i class="fas fa-print me-1"></i> พิมพ์
                                </a>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="ipdTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>วันที่ได้รับ</th>
                                                <th>HN</th>
                                                <th>AN</th>
                                                <th>CID</th>
                                                <th>ชื่อ-สกุล</th>
                                                <th class="text-center" style="width: 80px;">อายุ</th>
                                                <th>สิทธิการรักษา</th>
                                                <th>รายการยา</th>
                                                <th class="text-center" style="width: 100px;">จำนวน</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $idx_ipd = 1; @endphp
                                            @foreach ($antiviral_ipd as $row)
                                                <tr>
                                                    <td class="text-center">{{ $idx_ipd++ }}</td>
                                                    <td>{{ DateThai($row->regdate) }}</td>
                                                    <td>{{ $row->hn }}</td>
                                                    <td>{{ $row->an }}</td>
                                                    <td>{{ $row->cid }}</td>
                                                    <td><span class="fw-bold text-dark">{{ $row->ptname }}</span></td>
                                                    <td class="text-center">{{ $row->age_y }}</td>
                                                    <td>{{ $row->pttype_name }}</td>
                                                    <td><span class="text-dark">{{ $row->drug }}</span></td>
                                                    <td class="text-center fw-bold text-primary">{{ number_format($row->qty) }}</td>
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
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $(document).ready(function() {
                // DataTables Config
                const dataTableConfig = {
                    pageLength: 10,
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json' }
                };

                $('#opdTable').DataTable(dataTableConfig);
                $('#ipdTable').DataTable(dataTableConfig);

                // Flatpickr
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
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

                    // Update dates when budget year changes
                    $('select[name="budget_year"]').on('change', function() {
                        const selectedYear = parseInt($(this).val());
                        if (!isNaN(selectedYear)) {
                            const startYear = selectedYear - 544;
                            const endYear = selectedYear - 543;
                            const startDateStr = startYear + "-10-01";
                            const endDateStr = endYear + "-09-30";

                            startPicker.setDate(startDateStr, true);
                            endPicker.setDate(endDateStr, true);
                            $('#budget_year_changed').val('1');
                        }
                    });
                }

                // Handle tab switch
                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                });
            });
        </script>
    @endpush
@endsection
