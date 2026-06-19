<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard-ผู้ป่วยในรอสรุป Chart</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- CSS Stylesheets -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Sarabun', 'Nunito', sans-serif;
            background-color: #f3f4f6;
            color: #374151;
            padding: 2rem 1rem;
        }

        .main-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            margin-bottom: 2rem;
        }

        .header-section {
            border-bottom: 1px solid #f3f4f6;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-title h4 {
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 0.25rem;
        }

        .chart-container {
            position: relative;
            min-height: 380px;
            width: 100%;
        }

        /* Nav Pills Styling */
        .nav-pills-custom {
            display: flex;
            gap: 12px;
            margin-bottom: 1.5rem;
        }

        .nav-pills-custom .nav-link {
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 10px 20px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
        }

        .nav-pills-custom .nav-link:hover {
            transform: translateY(-2px);
        }

        .nav-pills-custom .nav-link.btn-diag {
            background-color: #ffffff;
            color: #c53030;
            border-color: #fee2e2;
        }

        .nav-pills-custom .nav-link.btn-diag.active {
            background-color: #fde8e8;
            color: #9b1c1c;
            border-color: #fbd5d5;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.08);
        }

        .nav-pills-custom .nav-link.btn-audit {
            background-color: #ffffff;
            color: #2563eb;
            border-color: #dbeafe;
        }

        .nav-pills-custom .nav-link.btn-audit.active {
            background-color: #dbeafe;
            color: #1e40af;
            border-color: #bfdbfe;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
        }

        .nav-pills-custom .nav-link.btn-icd10 {
            background-color: #ffffff;
            color: #d97706;
            border-color: #fef3c7;
        }

        .nav-pills-custom .nav-link.btn-icd10.active {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.08);
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.3rem 0.75rem !important;
            font-size: 0.85rem !important;
            outline: none !important;
        }

        .dt-buttons .btn-success {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            border-radius: 0.5rem !important;
            font-weight: 600 !important;
            padding: 0.35rem 0.8rem !important;
            font-size: 0.85rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.15) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            color: white !important;
            border: 1px solid #2563eb !important;
            border-radius: 0.5rem !important;
        }

        /* Header Colors Matching Respective Tab Colors (Soft Pastel) */
        #tableDiagText thead th {
            background-color: #fde8e8 !important;
            color: #9b1c1c !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            border-bottom: 2px solid #fbd5d5 !important;
        }

        #tableAudit thead th {
            background-color: #dbeafe !important;
            color: #1e40af !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            border-bottom: 2px solid #bfdbfe !important;
        }

        #tableIcd10 thead th {
            background-color: #fef3c7 !important;
            color: #92400e !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            border-bottom: 2px solid #fde68a !important;
        }

        /* Ensure sorting icons stand out on colored header backgrounds and are clickable */
        table.dataTable thead th {
            cursor: pointer !important;
            user-select: none;
            position: relative;
        }

        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc {
            background-repeat: no-repeat !important;
            background-position: center right 8px !important;
        }

        .footer {
            text-align: center;
            margin-top: 3rem;
            color: #6b7280;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="container-lg">
    <!-- Main Dashboard Card -->
    <div class="main-card">
        <!-- Header -->
        <div class="header-section">
            <div class="header-title">
                <h4><i class="fas fa-file-invoice text-primary me-2"></i>Dashboard-ผู้ป่วยในรอสรุป Chart</h4>
                <div class="text-muted small mt-1">
                    ข้อมูลประจำปีงบประมาณ {{ $budget_year }}
                    @if($start_date && $end_date)
                        (ระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }})
                    @endif
                </div>
            </div>

            <!-- Budget Year Selector Form -->
            <div>
                <form action="" method="GET" class="d-flex align-items-center gap-2 m-0">
                    <label class="fw-bold text-muted small mb-0 text-nowrap">ปีงบประมาณ:</label>
                    <select class="form-select form-select-sm" name="budget_year" onchange="this.form.submit()" style="width: 180px; border-radius: 8px;">
                        @foreach ($budget_year_select as $row)
                            <option value="{{ $row->LEAVE_YEAR_ID }}"
                                {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                {{ $row->LEAVE_YEAR_NAME }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <!-- Navigation Pills (Moved above the Chart) -->
        <div class="px-4 pt-4 pb-2 border-bottom" style="background-color: #ffffff;">
            <div class="nav nav-pills nav-pills-custom" id="pills-tab" role="tablist">
                <button class="nav-link btn-diag active" id="pills-diag-tab" data-bs-toggle="pill" data-bs-target="#pills-diag" type="button" role="tab" aria-controls="pills-diag" aria-selected="true">
                    <i class="fas fa-user-md me-1"></i> รอแพทย์สรุป Chart 
                    <span class="badge bg-danger ms-1">{{ count($non_diagtext_list) }}</span>
                </button>
                <button class="nav-link btn-audit" id="pills-audit-tab" data-bs-toggle="pill" data-bs-target="#pills-audit" type="button" role="tab" aria-controls="pills-audit" aria-selected="false">
                    <i class="fas fa-search me-1"></i> รอ Audit 
                    <span class="badge bg-primary ms-1">{{ count($wait_audit_list) }}</span>
                </button>
                <button class="nav-link btn-icd10" id="pills-icd10-tab" data-bs-toggle="pill" data-bs-target="#pills-icd10" type="button" role="tab" aria-controls="pills-icd10" aria-selected="false">
                    <i class="fas fa-code me-1"></i> รอบันทึกรหัสโรค 
                    <span class="badge bg-success ms-1">Audit แล้ว {{ count($non_icd10_audited) }}</span>
                    <span class="badge bg-danger ms-1">รอ Audit {{ count($non_icd10_wait_audit) }}</span>
                </button>
            </div>
        </div>

        <!-- Chart -->
        <div class="p-4 border-bottom" style="background-color: #fafafa;">
            <div class="chart-container">
                <div id="stackedBarChart"></div>
            </div>
        </div>

        <!-- Tab Content Tables (Below the Chart) -->
        <div class="p-4">
            <div class="tab-content" id="pills-tabContent">
                <!-- Waiting for Doctor Summary -->
                <div class="tab-pane fade show active" id="pills-diag" role="tabpanel" aria-labelledby="pills-diag-tab">
                    <div class="table-responsive">
                        <table id="tableDiagText" class="table table-hover align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">ลำดับ</th>
                                    <th>Ward</th>
                                    <th class="text-center">AN</th>
                                    <th>แพทย์เจ้าของคนไข้</th>
                                    <th class="text-center">วันที่จำหน่าย</th>
                                    <th class="text-center">จำนวนวันคงค้าง</th>
                                    <th class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($non_diagtext_list as $index => $row)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>{{ $row->ward }}</td>
                                        <td class="text-center fw-bold text-primary">{{ $row->an }}</td>
                                        <td>{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}</td>
                                        <td class="text-center">{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 fw-bold">
                                                {{ $row->dch_day }} วัน
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger px-2 py-1 small">รอแพทย์สรุป Chart</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Waiting for Audit -->
                <div class="tab-pane fade" id="pills-audit" role="tabpanel" aria-labelledby="pills-audit-tab">
                    <div class="table-responsive">
                        <table id="tableAudit" class="table table-hover align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">ลำดับ</th>
                                    <th>Ward</th>
                                    <th class="text-center">AN</th>
                                    <th>แพทย์เจ้าของคนไข้</th>
                                    <th class="text-center">วันที่จำหน่าย</th>
                                    <th class="text-center">จำนวนวันคงค้าง</th>
                                    <th class="text-center">การวินิจฉัยโรค</th>
                                    <th class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($wait_audit_list as $index => $row)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>{{ $row->ward }}</td>
                                        <td class="text-center fw-bold text-primary">{{ $row->an }}</td>
                                        <td>{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}</td>
                                        <td class="text-center">{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 fw-bold">
                                                {{ $row->dch_day }} วัน
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info btn-view-diag text-white" 
                                                data-an="{{ $row->an }}"
                                                data-doctor="{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}"
                                                data-dch-doctor="{{ $row->discharge_doctor_name ?: 'ไม่ระบุแพทย์' }}"
                                                data-dchdate="{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}"
                                                style="border-radius: 8px;">
                                                <i class="fas fa-search-plus me-1"></i> ดูวินิจฉัย
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary px-2 py-1 small">รอ Audit</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Waiting for ICD10 Coding -->
                <div class="tab-pane fade" id="pills-icd10" role="tabpanel" aria-labelledby="pills-icd10-tab">
                    <!-- Sub-tabs nav-tabs -->
                    <ul class="nav nav-tabs mb-3" id="icd10SubTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold text-success" id="icd10-audited-tab" data-bs-toggle="tab" data-bs-target="#icd10-audited" type="button" role="tab" aria-controls="icd10-audited" aria-selected="true">
                                <i class="fas fa-check-circle me-1 text-success"></i>Audit แล้ว
                                <span class="badge bg-success ms-1">{{ count($non_icd10_audited) }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold text-danger" id="icd10-wait-audit-tab" data-bs-toggle="tab" data-bs-target="#icd10-wait-audit" type="button" role="tab" aria-controls="icd10-wait-audit" aria-selected="false">
                                <i class="fas fa-minus-circle me-1 text-danger"></i>รอ Audit
                                <span class="badge bg-danger ms-1">{{ count($non_icd10_wait_audit) }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="icd10SubTabsContent">
                        <!-- Audit แล้ว Sub-tab -->
                        <div class="tab-pane fade show active" id="icd10-audited" role="tabpanel" aria-labelledby="icd10-audited-tab">
                            <div class="table-responsive">
                                <table id="tableIcd10Audited" class="table table-hover align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">ลำดับ</th>
                                            <th>Ward</th>
                                            <th class="text-center">AN</th>
                                            <th>แพทย์เจ้าของคนไข้</th>
                                            <th class="text-center">วันที่จำหน่าย</th>
                                            <th class="text-center">จำนวนวันคงค้าง</th>
                                            <th class="text-center">การวินิจฉัยโรค</th>
                                            <th class="text-center">สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($non_icd10_audited as $index => $row)
                                            <tr>
                                                <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                                <td>{{ $row->ward }}</td>
                                                <td class="text-center fw-bold text-primary">{{ $row->an }}</td>
                                                <td>{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}</td>
                                                <td class="text-center">{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning bg-opacity-10 text-warning px-2 fw-bold" style="color: #b07d0d !important;">
                                                        {{ $row->dch_day }} วัน
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-info btn-view-diag text-white" 
                                                        data-an="{{ $row->an }}"
                                                        data-doctor="{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}"
                                                        data-dch-doctor="{{ $row->discharge_doctor_name ?: 'ไม่ระบุแพทย์' }}"
                                                        data-dchdate="{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}"
                                                        style="border-radius: 8px;">
                                                        <i class="fas fa-search-plus me-1"></i> ดูวินิจฉัย
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark px-2 py-1 small">รอบันทึกรหัสโรค</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- รอ Audit Sub-tab -->
                        <div class="tab-pane fade" id="icd10-wait-audit" role="tabpanel" aria-labelledby="icd10-wait-audit-tab">
                            <div class="table-responsive">
                                <table id="tableIcd10WaitAudit" class="table table-hover align-middle" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">ลำดับ</th>
                                            <th>Ward</th>
                                            <th class="text-center">AN</th>
                                            <th>แพทย์เจ้าของคนไข้</th>
                                            <th class="text-center">วันที่จำหน่าย</th>
                                            <th class="text-center">จำนวนวันคงค้าง</th>
                                            <th class="text-center">การวินิจฉัยโรค</th>
                                            <th class="text-center">สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($non_icd10_wait_audit as $index => $row)
                                            <tr>
                                                <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                                <td>{{ $row->ward }}</td>
                                                <td class="text-center fw-bold text-primary">{{ $row->an }}</td>
                                                <td>{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}</td>
                                                <td class="text-center">{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning bg-opacity-10 text-warning px-2 fw-bold" style="color: #b07d0d !important;">
                                                        {{ $row->dch_day }} วัน
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-info btn-view-diag text-white" 
                                                        data-an="{{ $row->an }}"
                                                        data-doctor="{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}"
                                                        data-dch-doctor="{{ $row->discharge_doctor_name ?: 'ไม่ระบุแพทย์' }}"
                                                        data-dchdate="{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}"
                                                        style="border-radius: 8px;">
                                                        <i class="fas fa-search-plus me-1"></i> ดูวินิจฉัย
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark px-2 py-1 small">รอบันทึกรหัสโรค</span>
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
    <!-- Modal for viewing Diagnosis -->
    <div class="modal fade" id="diagModal" tabindex="-1" aria-labelledby="diagModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-primary" id="diagModalLabel">
                        <i class="fas fa-user-md me-2"></i>รายละเอียดการวินิจฉัยโรค
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">AN</span>
                            <strong id="modal-an" class="fs-5 text-dark">-</strong>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block">วันที่จำหน่าย</span>
                                <strong id="modal-dchdate" class="fs-5 text-dark">-</strong>
                            </div>
                            <div id="modal-status-container">
                                <!-- Status badge will be generated here -->
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">แพทย์ผู้รักษา</span>
                            <span id="modal-doctor" class="fw-bold text-dark">-</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">แพทย์ผู้จำหน่าย</span>
                            <span id="modal-dch-doctor" class="fw-bold text-dark">-</span>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded-3 border">
                        <span class="text-muted small d-block mb-2 fw-semibold" id="modal-diag-title">การวินิจฉัยโรค</span>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle bg-white m-0" style="font-size: 0.9rem;">
                                <thead class="table-secondary text-dark" id="modal-diag-thead">
                                    <!-- Dynamic headers will be generated here -->
                                </thead>
                                <tbody id="modal-diag-tbody">
                                    <!-- Rows will be generated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="footer">
        พิมพ์โดยระบบ SmartData | โรงพยาบาลหัวตะพาน
    </div>
</div>

<!-- JS Libraries -->
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
<script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // --- Stacked Bar Chart (ApexCharts) ---
        const chartData = @json($chart_data);
        
        // Calculate dynamic height based on number of doctors
        const numDoctors = chartData.doctors.length;
        const minHeight = Math.max(380, numDoctors * 42);
        document.getElementById('stackedBarChart').style.height = minHeight + 'px';

        const options = {
            series: [{
                name: 'รอสรุป Chart',
                data: chartData.non_diagtext.map(Number)
            }, {
                name: 'รอ Audit',
                data: chartData.wait_audit.map(Number)
            }, {
                name: 'รอบันทึกรหัสโรค',
                data: chartData.non_icd10.map(Number)
            }],
            chart: {
                type: 'bar',
                height: minHeight,
                stacked: true,
                toolbar: {
                    show: true
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '75%',
                }
            },
            colors: ['#dc3545', '#2563eb', '#f6c23e'], // Red, Blue, and Yellow/Orange
            xaxis: {
                categories: chartData.doctors.map(d => d || 'ไม่ระบุแพทย์'),
                labels: {
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Sarabun, sans-serif'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        fontFamily: 'Sarabun, sans-serif'
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                offsetX: 10,
                fontSize: '13px',
                fontFamily: 'Sarabun, sans-serif',
                markers: {
                    radius: 4
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '11px',
                    fontWeight: 'bold',
                    colors: ['#fff']
                },
                formatter: function(val) {
                    return val > 0 ? val : '';
                }
            },
            grid: {
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        const chart = new ApexCharts(document.querySelector("#stackedBarChart"), options);
        chart.render();

        // --- DataTables ---
        function initTable(id, excelTitle) {
            return $(id).DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success',
                    title: excelTitle,
                    messageTop: 'Dashboard-ผู้ป่วยในรอสรุป Chart - ปีงบประมาณ {{ $budget_year }}'
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

        const tableDiag = initTable('#tableDiagText', 'รายชื่อผู้ป่วยในรอแพทย์สรุป Chart');
        const tableAudit = initTable('#tableAudit', 'รายชื่อผู้ป่วยในรอ Audit');
        const tableIcd10WaitAudit = initTable('#tableIcd10WaitAudit', 'รายชื่อผู้ป่วยในรอบันทึกรหัสโรค (รอ Audit)');
        const tableIcd10Audited = initTable('#tableIcd10Audited', 'รายชื่อผู้ป่วยในรอบันทึกรหัสโรค (Audit แล้ว)');

        // Adjust table headers when switching tabs
        $('button[data-bs-toggle="pill"], button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });

        // Show diagnosis modal
        $(document).on('click', '.btn-view-diag', function() {
            const an = $(this).data('an');
            const doctor = $(this).data('doctor');
            const dchDoctor = $(this).data('dch-doctor');
            const dchdate = $(this).data('dchdate');
            
            $('#modal-an').text(an);
            $('#modal-doctor').text(doctor);
            $('#modal-dch-doctor').text(dchDoctor);
            $('#modal-dchdate').text(dchdate);
            
            const listContainer = $('#modal-diag-tbody');
            const theadContainer = $('#modal-diag-thead');
            const titleContainer = $('#modal-diag-title');
            
            listContainer.empty();
            theadContainer.empty();
            listContainer.append('<tr><td colspan="3" class="text-center text-muted"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> กำลังโหลดข้อมูล...</td></tr>');
            
            $('#modal-status-container').html('<div class="spinner-border spinner-border-sm text-primary" role="status"></div>');
            
            const myModal = new bootstrap.Modal(document.getElementById('diagModal'));
            myModal.show();
            
            $.ajax({
                url: `{{ url('/dashboard/ipd_wait_dchsummary/diags') }}/${an}`,
                method: 'GET',
                success: function(diags) {
                    // Check if any diagnosis has been audited
                    const isAudited = diags.some(item => item.audit_ok === 'Y' || (item.audit_diag_text && item.audit_diag_text.trim() !== ''));
                    
                    const statusContainer = $('#modal-status-container');
                    statusContainer.empty();
                    if (isAudited) {
                        statusContainer.append('<span class="badge bg-success fs-6 px-3 py-2"><i class="fas fa-check-circle me-1"></i> Audit แล้ว</span>');
                    } else {
                        statusContainer.append('<span class="badge bg-danger fs-6 px-3 py-2"><i class="fas fa-history me-1"></i> รอ Audit</span>');
                    }
                    
                    // Set title and headers dynamically
                    theadContainer.empty();
                    if (isAudited) {
                        titleContainer.text("การวินิจฉัยโรค (เปรียบเทียบการ Audit)");
                        theadContainer.append(`
                            <tr>
                                <th style="width: 25%;">ประเภท</th>
                                <th style="width: 37.5%;">การวินิจฉัยเดิม (แพทย์)</th>
                                <th style="width: 37.5%;">การวินิจฉัยหลัง Audit (ผู้ตรวจสอบ)</th>
                            </tr>
                        `);
                    } else {
                        titleContainer.text("การวินิจฉัยโรค");
                        theadContainer.append(`
                            <tr>
                                <th style="width: 25%;">ประเภท</th>
                                <th style="width: 55%;">การวินิจฉัยของแพทย์</th>
                                <th style="width: 20%;">Audit</th>
                            </tr>
                        `);
                    }
                    
                    listContainer.empty();
                    if (!diags || diags.length === 0) {
                        listContainer.append(`<tr><td colspan="3" class="text-center text-muted">ไม่มีข้อมูลการวินิจฉัย</td></tr>`);
                    } else {
                        const typeNames = {
                            '1': { name: 'วินิจฉัยหลัก (Principal Diag)', badgeClass: 'bg-danger' },
                            '2': { name: 'วินิจฉัยร่วม (Comorbidity)', badgeClass: 'bg-primary' },
                            '3': { name: 'โรคแทรก (Complication)', badgeClass: 'bg-warning text-dark' },
                            '4': { name: 'อื่น ๆ (Other)', badgeClass: 'bg-secondary' },
                            '5': { name: 'สาเหตุภายนอก (External Cause)', badgeClass: 'bg-info text-white' }
                        };
                        
                        diags.forEach((item) => {
                            const type = item.diagtype || '';
                            const text = item.diag_text || 'ไม่มีระบุข้อความ';
                            const auditOk = item.audit_ok || '';
                            const auditText = item.audit_diag_text || '';
                            const auditDoc = item.audit_doctor_code || '';
                            const auditDiagtype = item.audit_diagtype || '';
                            
                            const typeInfo = typeNames[type] || { name: `ประเภท ${type}`, badgeClass: 'bg-dark' };
                            
                            if (isAudited) {
                                const isRowAudited = auditOk === 'Y' || (auditText && auditText.trim() !== '');
                                let auditDisplayVal = '';
                                if (isRowAudited) {
                                    if (auditDiagtype === type) {
                                        auditDisplayVal = `
                                            <div class="fw-bold text-success">${auditText || 'ถูกต้อง (ไม่มีแก้ไข)'}</div>
                                            <div class="text-muted small mt-1">ผู้ตรวจสอบ: ${auditDoc}</div>
                                        `;
                                    } else {
                                        auditDisplayVal = `<span class="text-muted small">-</span>`;
                                    }
                                } else {
                                    auditDisplayVal = `
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2 py-0.5" style="font-size: 0.75rem;">
                                            รอ Audit
                                        </span>
                                    `;
                                }
                                
                                const rowHtml = `
                                    <tr>
                                        <td><span class="badge ${typeInfo.badgeClass} d-block text-wrap text-center" style="font-size: 0.75rem;">${typeInfo.name}</span></td>
                                        <td class="fw-bold text-dark" style="white-space: pre-wrap;">${text}</td>
                                        <td style="white-space: pre-wrap;">${auditDisplayVal}</td>
                                    </tr>
                                `;
                                listContainer.append(rowHtml);
                            } else {
                                const rowHtml = `
                                    <tr>
                                        <td><span class="badge ${typeInfo.badgeClass} d-block text-wrap text-center" style="font-size: 0.75rem;">${typeInfo.name}</span></td>
                                        <td class="fw-bold text-dark" style="white-space: pre-wrap;">${text}</td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2 py-0.5" style="font-size: 0.75rem;">
                                                รอ Audit
                                            </span>
                                        </td>
                                    </tr>
                                `;
                                listContainer.append(rowHtml);
                            }
                        });
                    }
                },
                error: function() {
                    $('#modal-status-container').empty();
                    const colspan = theadContainer.find('th').length || 3;
                    listContainer.empty();
                    listContainer.append(`<tr><td colspan="${colspan}" class="text-center text-danger">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>`);
                }
            });
        });
    });
</script>

</body>
</html>
