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
        .bg-pastel-blue { background-color: #e0f2fe !important; }

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

        /* DataTables Custom Styling to match Image 2 */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.25rem 0.6rem !important;
            outline: none !important;
            font-size: 0.85rem !important;
            box-shadow: none !important;
        }

        .dataTables_wrapper .dataTables_length select {
            padding-right: 1.5rem !important;
            min-width: 60px !important;
        }
        
        /* Excel Button Styling */
        .dt-buttons .btn-success, .buttons-excel {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
            border-radius: 0.4rem !important;
            font-weight: 500 !important;
            padding: 0.3rem 0.75rem !important;
            font-size: 0.85rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            box-shadow: 0 2px 4px rgba(25, 135, 84, 0.15) !important;
            transition: all 0.2s ease-in-out !important;
        }
        
        .dt-buttons .btn-success:hover, .buttons-excel:hover {
            background-color: #157347 !important;
            border-color: #146c43 !important;
        }

        /* Pagination Styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .page-item.active .page-link {
            background: #4f46e5 !important; /* Royal blue / Indigo */
            color: white !important;
            border-color: #4f46e5 !important;
            border-radius: 0.4rem !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current),
        .page-item:not(.active) .page-link {
            color: #4f46e5 !important;
            background: transparent !important;
            border: 1px solid transparent !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
        .page-link:hover {
            background: #f3f4f6 !important;
            color: #4f46e5 !important;
            border-radius: 0.4rem !important;
            border-color: #dee2e6 !important;
        }
        
        .page-item:first-child .page-link,
        .page-item:last-child .page-link {
            border-radius: 0.4rem !important;
        }
        
        .page-link {
            margin: 0 2px !important;
            border-radius: 0.4rem !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.85rem !important;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0rem !important;
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin-bottom: 0 !important;
            font-size: 0.85rem !important;
        }

        .dt-buttons {
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-prescription text-primary me-2"></i> {{ $title }}
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
                            <div class="bg-light p-2 rounded-3"><i class="fas fa-pills text-primary fa-lg"></i></div>
                        </div>
                        <div class="label-small mb-1 text-muted small fw-bold">จำนวนการใช้ยารวม (ไม่ซ้ำ Visit/AN)</div>
                        <div class="stat-val text-dark fw-bold h4">{{ number_format(count($grouped_opd) + count($grouped_ipd)) }} <span class="fs-6 fw-normal text-muted">ราย</span></div>
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
                        <div class="stat-val text-dark fw-bold h4">{{ number_format(count($grouped_opd)) }} <span class="fs-6 fw-normal text-muted">ราย</span></div>
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
                        <div class="stat-val text-dark fw-bold h4">{{ number_format(count($grouped_ipd)) }} <span class="fs-6 fw-normal text-muted">ราย</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content with Tabs -->
        <ul class="nav nav-tabs nav-tabs-custom" id="warfarinReportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd-content" type="button" role="tab"><i class="fas fa-user-nurse me-2"></i>ผู้ป่วยนอก (OPD)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd-content" type="button" role="tab"><i class="fas fa-bed-pulse me-2"></i>ผู้ป่วยใน (IPD)</button>
            </li>
        </ul>

        <div class="tab-content" id="warfarinReportTabsContent">
            <!-- OPD Tab -->
            <div class="tab-pane fade show active" id="opd-content" role="tabpanel">
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-chart-line me-2"></i>จำนวนการใช้ยา Warfarin รายเดือน (OPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div id="warfarinOpdChart" style="min-height: 250px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>ตารางข้อมูลการใช้ยา (OPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="opdTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>HN</th>
                                                <th>วันที่/เวลารับบริการ</th>
                                                <th>ชื่อผู้ป่วย</th>
                                                <th class="text-center" style="width: 80px;">อายุ</th>
                                                <th>PCU / เขตรับผิดชอบ</th>
                                                <th class="text-center" style="width: 150px;">ข้อมูลการสั่งยา</th>
                                                <th class="text-center" style="width: 150px;">รายงานผล LAB</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $idx_opd = 1; @endphp
                                            @foreach ($grouped_opd as $row)
                                                <tr>
                                                    <td class="text-center">{{ $idx_opd++ }}</td>
                                                    <td>{{ $row['hn'] }}</td>
                                                    <td>
                                                        <div class="text-nowrap small fw-bold text-dark">
                                                            {{ DateThai($row['vstdate']) }} {{ $row['vsttime'] }}
                                                        </div>
                                                    </td>
                                                    <td><span class="fw-bold text-dark">{{ $row['ptname'] }}</span></td>
                                                    <td class="text-center">{{ $row['age_y'] }}</td>
                                                    <td>{{ $row['pcu'] }}</td>
                                                    
                                                    <!-- Clickable Prescription Icon -->
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-success open-drug-modal" 
                                                            data-ptname="{{ $row['ptname'] }}" 
                                                            data-hn="{{ $row['hn'] }}" 
                                                            data-drugs="{{ json_encode($row['drugs']) }}"
                                                            title="คลิกเพื่อดูวิธีใช้ยา">
                                                            <i class="fas fa-pills me-1"></i> ดูวิธีใช้ยา 
                                                            @if(count($row['drugs']) > 1)
                                                                <span class="badge bg-success text-white ms-1">{{ count($row['drugs']) }}</span>
                                                            @endif
                                                        </button>
                                                    </td>
                                                    
                                                    <!-- Clickable Lab Icon -->
                                                    <td class="text-center">
                                                        @if(count($row['labs']) > 0)
                                                            <button class="btn btn-sm btn-outline-primary open-lab-modal"
                                                                data-ptname="{{ $row['ptname'] }}"
                                                                data-hn="{{ $row['hn'] }}"
                                                                data-labs="{{ json_encode(array_values($row['labs'])) }}"
                                                                title="คลิกเพื่อดูผล LAB">
                                                                <i class="fas fa-file-medical-alt me-1"></i> ดูผล LAB
                                                                @if(count($row['labs']) > 1)
                                                                    <span class="badge bg-primary text-white ms-1">{{ count($row['labs']) }}</span>
                                                                @endif
                                                            </button>
                                                        @else
                                                            <span class="text-muted small">- ไม่มีผลตรวจ -</span>
                                                        @endif
                                                    </td>
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
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-chart-line me-2"></i>จำนวนการใช้ยา Warfarin รายเดือน (IPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div id="warfarinIpdChart" style="min-height: 250px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-table me-2"></i>ตารางข้อมูลการใช้ยา (IPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="ipdTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">#</th>
                                                <th>HN</th>
                                                <th>AN</th>
                                                <th>วันที่ Admit / DC</th>
                                                <th>ชื่อผู้ป่วย</th>
                                                <th class="text-center" style="width: 80px;">อายุ</th>
                                                <th>PCU / เขตรับผิดชอบ</th>
                                                <th class="text-center" style="width: 150px;">ข้อมูลการสั่งยา</th>
                                                <th class="text-center" style="width: 150px;">รายงานผล LAB</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $idx_ipd = 1; @endphp
                                            @foreach ($grouped_ipd as $row)
                                                <tr>
                                                    <td class="text-center">{{ $idx_ipd++ }}</td>
                                                    <td>{{ $row['hn'] }}</td>
                                                    <td>{{ $row['an'] }}</td>
                                                    <td>
                                                        <div class="text-nowrap small text-success">
                                                            <strong>Adm:</strong> {{ DateThai($row['regdate']) }} {{ $row['regtime'] }}
                                                        </div>
                                                        <div class="text-nowrap small text-danger mt-1">
                                                            <strong>DC:</strong> {{ $row['dchdate'] ? DateThai($row['dchdate']) . ' ' . $row['dchtime'] : '-' }}
                                                        </div>
                                                    </td>
                                                    <td><span class="fw-bold text-dark">{{ $row['ptname'] }}</span></td>
                                                    <td class="text-center">{{ $row['age_y'] }}</td>
                                                    <td>{{ $row['pcu'] }}</td>
                                                    
                                                    <!-- Clickable Prescription Icon -->
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-success open-drug-modal" 
                                                            data-ptname="{{ $row['ptname'] }}" 
                                                            data-hn="{{ $row['hn'] }}" 
                                                            data-drugs="{{ json_encode($row['drugs']) }}"
                                                            title="คลิกเพื่อดูวิธีใช้ยา">
                                                            <i class="fas fa-pills me-1"></i> ดูวิธีใช้ยา
                                                            @if(count($row['drugs']) > 1)
                                                                <span class="badge bg-success text-white ms-1">{{ count($row['drugs']) }}</span>
                                                            @endif
                                                        </button>
                                                    </td>
                                                    
                                                    <!-- Clickable Lab Icon -->
                                                    <td class="text-center">
                                                        @if(count($row['labs']) > 0)
                                                            <button class="btn btn-sm btn-outline-primary open-lab-modal"
                                                                data-ptname="{{ $row['ptname'] }}"
                                                                data-hn="{{ $row['hn'] }}"
                                                                data-labs="{{ json_encode(array_values($row['labs'])) }}"
                                                                title="คลิกเพื่อดูผล LAB">
                                                                <i class="fas fa-file-medical-alt me-1"></i> ดูผล LAB
                                                                @if(count($row['labs']) > 1)
                                                                    <span class="badge bg-primary text-white ms-1">{{ count($row['labs']) }}</span>
                                                                @endif
                                                            </button>
                                                        @else
                                                            <span class="text-muted small">- ไม่มีผลตรวจ -</span>
                                                        @endif
                                                    </td>
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

    <!-- Modal for Drug Usage -->
    <div class="modal fade" id="drugUsageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-pastel-green border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="modal-title fw-bold text-green"><i class="fas fa-pills me-2"></i> ข้อมูลการสั่งยา</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 p-2 bg-light rounded-3 text-muted small">
                        <strong>ผู้ป่วย:</strong> <span id="modalPtName" class="text-dark fw-bold"></span><br>
                        <strong>HN:</strong> <span id="modalHn" class="text-dark fw-bold"></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>วันที่สั่งยา</th>
                                    <th>ชื่อยา / ความแรง</th>
                                    <th>วิธีใช้ยา</th>
                                </tr>
                            </thead>
                            <tbody id="modalDrugList">
                                <!-- Dynamic drug list -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Lab Result -->
    <div class="modal fade" id="labResultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-pastel-blue border-0" style="border-radius: 15px 15px 0 0;">
                    <h6 class="modal-title fw-bold text-primary"><i class="fas fa-vial me-2"></i> ข้อมูลผลการตรวจทางห้องปฏิบัติการ (LAB)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 p-2 bg-light rounded-3 text-muted small">
                        <strong>ผู้ป่วย:</strong> <span id="modalLabPtName" class="text-dark fw-bold"></span><br>
                        <strong>HN:</strong> <span id="modalLabHn" class="text-dark fw-bold"></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle small">
                            <thead class="table-light">
                                <tr>
                                    <th>วันที่รายงานผล</th>
                                    <th>การตรวจ PT</th>
                                    <th>ผล PT</th>
                                    <th>การตรวจ INR</th>
                                    <th>ผล INR</th>
                                </tr>
                            </thead>
                            <tbody id="modalLabList">
                                <!-- Dynamic lab list -->
                            </tbody>
                        </table>
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
            // Custom Date Formatter for JS (Thai format helper)
            function formatThaiDate(dateStr) {
                if (!dateStr) return '-';
                const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                const parts = dateStr.split('-');
                if (parts.length !== 3) return dateStr;
                const year = parseInt(parts[0]) + 543;
                const month = months[parseInt(parts[1]) - 1];
                const day = parseInt(parts[2]);
                return `${day} ${month} ${year}`;
            }

            $(document).ready(function() {
                // Event Handlers for Modals
                $(document).on('click', '.open-drug-modal', function() {
                    const ptname = $(this).data('ptname');
                    const hn = $(this).data('hn');
                    const drugs = $(this).data('drugs');
                    
                    $('#modalPtName').text(ptname);
                    $('#modalHn').text(hn);
                    
                    let html = '';
                    drugs.forEach(function(item) {
                        const dateFormatted = formatThaiDate(item.rxdate) + ' ' + (item.rxtime ? item.rxtime : '');
                        html += `<tr>
                            <td>${dateFormatted}</td>
                            <td><span class="fw-bold text-dark">${item.name}</span></td>
                            <td>${item.usage ? item.usage : '-'}</td>
                        </tr>`;
                    });
                    
                    $('#modalDrugList').html(html);
                    $('#drugUsageModal').modal('show');
                });

                $(document).on('click', '.open-lab-modal', function() {
                    const ptname = $(this).data('ptname');
                    const hn = $(this).data('hn');
                    const labs = $(this).data('labs');
                    
                    $('#modalLabPtName').text(ptname);
                    $('#modalLabHn').text(hn);
                    
                    let html = '';
                    labs.forEach(function(item) {
                        const dateFormatted = formatThaiDate(item.report_date) + ' ' + (item.report_time ? item.report_time : '');
                        html += `<tr>
                            <td>${dateFormatted}</td>
                            <td>${item.pt ? item.pt : '-'}</td>
                            <td class="fw-bold text-primary">${item.pt_result ? item.pt_result : '-'}</td>
                            <td>${item.inr ? item.inr : '-'}</td>
                            <td class="fw-bold text-danger">${item.inr_result ? item.inr_result : '-'}</td>
                        </tr>`;
                    });
                    
                    $('#modalLabList').html(html);
                    $('#labResultModal').modal('show');
                });

                // OPD Chart
                var opdChart = new ApexCharts(document.querySelector("#warfarinOpdChart"), {
                    series: [{ name: 'ผู้ป่วย (ราย)', data: @json($monthly_opd['values']) }],
                    chart: { type: 'area', height: 250, toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 2 },
                    xaxis: { categories: @json($monthly_opd['categories']) },
                    colors: ['#10b981'],
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] }
                    },
                    tooltip: { y: { formatter: function(val) { return val.toLocaleString() + " ราย"; } } }
                });
                opdChart.render();

                // IPD Chart
                var ipdChart = new ApexCharts(document.querySelector("#warfarinIpdChart"), {
                    series: [{ name: 'ผู้ป่วย (ราย)', data: @json($monthly_ipd['values']) }],
                    chart: { type: 'area', height: 250, toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 2 },
                    xaxis: { categories: @json($monthly_ipd['categories']) },
                    colors: ['#ef4444'],
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] }
                    },
                    tooltip: { y: { formatter: function(val) { return val.toLocaleString() + " ราย"; } } }
                });
                ipdChart.render();

                // DataTables
                const dataTableConfig = {
                    pageLength: 10,
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    language: {
                        search: "ค้นหา:",
                        lengthMenu: "แสดง _MENU_ รายการ",
                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                        paginate: {
                            previous: "ก่อนหน้า",
                            next: "ถัดไป"
                        }
                    }
                };

                var opdTable = $('#opdTable').DataTable({
                    ...dataTableConfig,
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานข้อมูลการใช้ยา Warfarin (OPD)',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5] }
                    }]
                });

                var ipdTable = $('#ipdTable').DataTable({
                    ...dataTableConfig,
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานข้อมูลการใช้ยา Warfarin (IPD)',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
                    }]
                });

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

                // Handle tab switch for chart rendering issues
                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                    window.dispatchEvent(new Event('resize'));
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                });
            });
        </script>
    @endpush
@endsection
