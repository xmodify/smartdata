@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }

        .report-title-box h4 {
            font-size: 1.15rem;
            letter-spacing: -0.01em;
        }

        .header-form-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .input-group-date {
            width: 160px !important;
        }

        .input-group-budget {
            width: 250px !important;
        }

        @media (max-width: 768px) {
            .page-header-container {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .header-form-controls {
                width: 100%;
            }

            .input-group-date,
            .input-group-budget {
                width: 100% !important;
            }
        }

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            color: #4e73df;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }

        .flatpickr-today-button:hover {
            background: #f8f9fc;
            color: #2e59d9;
        }

        .bg-pastel-blue { background-color: #f0f9ff; }
        .bg-pastel-green { background-color: #f0fdf4; }
        .bg-pastel-purple { background-color: #faf5ff; }
        .bg-pastel-amber { background-color: #fffbeb; }

        .text-purple { color: #8b5cf6 !important; }

        .card-stats {
            border-radius: 15px;
            transition: transform 0.2s ease, shadow 0.2s ease;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }

        .text-num {
            font-family: 'Inter', sans-serif;
            text-align: right;
        }

        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
        }

        .table-stats {
            white-space: nowrap;
            font-size: 0.85rem;
        }

        .table-stats th {
            text-align: center;
            vertical-align: middle;
            padding: 10px 14px;
            font-weight: 600;
            background-color: #f8fafc;
        }

        .stat-val {
            font-size: 1.35rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .label-small {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
        }

        .chart-container {
            min-height: 350px;
        }

        .btn-view-patients-detail {
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-view-patients-detail:hover {
            text-decoration: underline !important;
            filter: brightness(0.85);
        }

        /* Modal Customizations */
        #patientDetailsModal .modal-header {
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border-bottom: none;
            border-radius: 16px 16px 0 0;
            padding: 1.25rem 1.5rem;
        }

        #patientDetailsModal .modal-title {
            font-size: 1.15rem;
            letter-spacing: -0.02em;
            font-weight: 700;
        }

        #patientDetailsModal .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        #patientDetailsModal .btn-close:hover {
            opacity: 1;
        }

        .modal-table-container {
            border-radius: 12px;
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        #table-patient-details {
            border-collapse: separate;
            border-spacing: 0;
        }

        #table-patient-details th {
            background-color: #f8fafc !important;
            color: #475569 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-bottom: 2px solid #e2e8f0 !important;
            border-top: none !important;
        }

        #table-patient-details td {
            padding: 8px 10px;
            color: #334155;
            font-size: 0.82rem;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        #table-patient-details tbody tr:hover {
            background-color: #f8fafc !important;
        }

        .badge-pdx {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
            font-family: 'Inter', 'SFMono-Regular', Consolas, monospace;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }

        .badge-results {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.75rem;
            line-height: 1.4;
            display: inline-block;
            max-width: 320px;
            white-space: normal;
            word-break: break-word;
        }

        .patient-hn {
            font-family: 'Inter', monospace;
            font-weight: 700;
            color: #0f172a;
        }

        .patient-name {
            font-weight: 600;
            color: #1e293b;
        }

        #patientDetailsContent .dt-buttons .btn {
            background-color: #198754 !important;
            border: 1px solid #198754 !important;
            color: white !important;
            border-radius: 6px !important;
            font-size: 0.75rem !important;
            padding: 5px 12px !important;
            box-shadow: none !important;
        }

        #table-patient-details_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 5px 12px;
            font-size: 0.8rem;
            outline: none;
        }

        #table-patient-details_filter input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Page -->
        <div class="page-header-container d-flex flex-wrap justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-2 py-1">
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-vial text-primary me-2"></i> {{ $title }}
                    </h4>
                    <div class="text-muted small mt-1">
                        สถิติการตรวจทางห้องปฏิบัติการนอกหน่วยบริการ / แล็บใกล้บ้าน (Lab Anywhere) ปีงบประมาณ <strong>{{ $budget_year }}</strong>
                    </div>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
                    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary">
                            <i class="fas fa-calendar-alt"></i>
                        </span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0"
                            value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
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
            <div class="col-md-3">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-blue p-2 rounded-3">
                                <i class="fas fa-clipboard-list text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">จำนวนสั่งตรวจทั้งหมด</div>
                        <div class="stat-val text-dark">
                            {{ number_format($total_orders) }} <span class="fs-6 fw-normal text-muted">ครั้ง</span>
                            <span class="text-muted mx-1">|</span>
                            {{ number_format($total_hn) }} <span class="fs-6 fw-normal text-muted">คน</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-purple p-2 rounded-3">
                                <i class="fas fa-vial text-purple fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">จำนวนรายการตรวจสะสม</div>
                        <div class="stat-val text-dark">
                            {{ number_format($total_tests) }} <span class="fs-6 fw-normal text-muted">รายการ</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-green p-2 rounded-3">
                                <i class="fas fa-check-circle text-success fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">รายงานผลสมบูรณ์แล้ว</div>
                        <div class="stat-val text-dark">
                            {{ number_format($confirmed_orders) }} <span class="fs-6 fw-normal text-muted">ครั้ง</span>
                            <span class="fs-6 fw-bold text-success ms-1">({{ $confirm_pct }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-amber p-2 rounded-3">
                                <i class="fas fa-calculator text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">เฉลี่ยรายการต่อครั้ง</div>
                        <div class="stat-val text-dark">
                            {{ number_format($total_orders > 0 ? $total_tests / $total_orders : 0, 1) }} <span class="fs-6 fw-normal text-muted">รายการ/ใบ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-4">
            <!-- Monthly Trend -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">แนวโน้มการให้บริการ Lab Anywhere รายเดือน</h5>
                        <p class="text-muted small">เปรียบเทียบจำนวนครั้งการสั่งตรวจและจำนวนผู้รับบริการ (HN) ในแต่ละเดือน</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="monthlyTrendChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <!-- Top Lab Tests -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">10 อันดับรายการตรวจยอดนิยม</h5>
                        <p class="text-muted small">จำนวนรายการตรวจทางห้องปฏิบัติการที่มีการส่งตรวจสูงสุด</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="topItemsChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Top Diagnoses -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">10 อันดับโรคหลักของผู้ป่วย (ICD-10)</h5>
                        <p class="text-muted small">การวินิจฉัยโรคหลัก (Primary Diagnosis) ของผู้รับบริการ Lab Anywhere</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="topDiagsChart" class="chart-container" style="min-height: 380px;"></div>
                    </div>
                </div>
            </div>

            <!-- Top Ordering Doctors -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">10 อันดับแพทย์/เจ้าหน้าที่ผู้สั่งตรวจ</h5>
                        <p class="text-muted small">จำนวนใบสั่งตรวจ Lab Anywhere สะสมจำแนกตามผู้สั่งตรวจ</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="topDoctorsChart" class="chart-container" style="min-height: 380px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-pastel-blue py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 20px 20px 0 0;">
                        <h6 class="fw-bold mb-0 text-primary">
                            <i class="fas fa-table me-2"></i>ตารางสรุปข้อมูลการให้บริการ Lab Anywhere รายเดือน
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-success px-2 shadow-sm btn-export-excel" data-target="#anywhereStatsTable" style="font-size: 0.75rem; padding: 2px 8px;">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-stats mb-0" id="anywhereStatsTable">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle">เดือน</th>
                                        <th colspan="2" class="bg-pastel-blue border-end">ผู้รับบริการ</th>
                                        <th colspan="10" class="bg-pastel-green border-end">จำนวนรายการตรวจสำคัญ</th>
                                        <th rowspan="2" class="bg-pastel-purple align-middle">รวมทุกรายการ</th>
                                    </tr>
                                    <tr>
                                        <th class="bg-pastel-blue">ส่งตรวจ (ครั้ง)</th>
                                        <th class="bg-pastel-blue border-end">ผู้ป่วย (คน)</th>
                                        
                                        <th class="bg-pastel-green">FBS</th>
                                        <th class="bg-pastel-green">Creatinine</th>
                                        <th class="bg-pastel-green">BUN</th>
                                        <th class="bg-pastel-green">Lipid</th>
                                        <th class="bg-pastel-green">HbA1C</th>
                                        <th class="bg-pastel-green">HCT</th>
                                        <th class="bg-pastel-green">Albumin</th>
                                        <th class="bg-pastel-green">Electrolyte</th>
                                        <th class="bg-pastel-green">LFT</th>
                                        <th class="bg-pastel-green border-end">อื่นๆ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthly_stats as $row)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $row->month_name }}</td>
                                            
                                            <td class="text-num fw-bold text-primary">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-primary text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="all" data-label="ผู้รับบริการทั้งหมด">
                                                    {{ number_format($row->total_orders) }}
                                                </a>
                                            </td>
                                            <td class="text-num text-dark border-end">{{ number_format($row->total_hn) }}</td>

                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="fbs" data-label="ตรวจ FBS">
                                                    {{ number_format($row->fbs_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="cr" data-label="ตรวจ Creatinine">
                                                    {{ number_format($row->cr_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="bun" data-label="ตรวจ BUN">
                                                    {{ number_format($row->bun_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="lipid" data-label="ตรวจ Lipid Profile">
                                                    {{ number_format($row->lipid_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="hba1c" data-label="ตรวจ HbA1C">
                                                    {{ number_format($row->hba1c_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="hct" data-label="ตรวจ HCT">
                                                    {{ number_format($row->hct_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="alb" data-label="ตรวจ Albumin">
                                                    {{ number_format($row->alb_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="electrolyte" data-label="ตรวจ Electrolyte">
                                                    {{ number_format($row->electrolyte_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="lft" data-label="ตรวจ LFT">
                                                    {{ number_format($row->lft_count) }}
                                                </a>
                                            </td>
                                            <td class="text-num border-end">
                                                <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                    data-month-key="{{ $row->month_key }}" data-month-label="{{ $row->month_name }}" 
                                                    data-lab-item="other" data-label="ตรวจอื่นๆ (CBC, UA, HBsAg)">
                                                    {{ number_format($row->other_count) }}
                                                </a>
                                            </td>

                                            <td class="text-num fw-bold text-purple">{{ number_format($row->total_tests) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold border-top-2">
                                    <tr>
                                        <td class="text-center">รวม</td>
                                        <td class="text-num text-primary">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-primary text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="all" data-label="ผู้รับบริการทั้งหมด">
                                                {{ number_format($total_orders) }}
                                            </a>
                                        </td>
                                        <td class="text-num text-dark border-end">{{ number_format($total_hn) }}</td>
                                        
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="fbs" data-label="ตรวจ FBS ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'fbs_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="cr" data-label="ตรวจ Creatinine ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'cr_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="bun" data-label="ตรวจ BUN ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'bun_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="lipid" data-label="ตรวจ Lipid Profile ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'lipid_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="hba1c" data-label="ตรวจ HbA1C ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'hba1c_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="hct" data-label="ตรวจ HCT ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'hct_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="alb" data-label="ตรวจ Albumin ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'alb_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="electrolyte" data-label="ตรวจ Electrolyte ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'electrolyte_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="lft" data-label="ตรวจ LFT ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'lft_count'))) }}
                                            </a>
                                        </td>
                                        <td class="text-num border-end">
                                            <a href="javascript:void(0)" class="btn-view-patients-detail text-dark text-decoration-none" 
                                                data-month-key="all" data-month-label="ทั้งหมด" 
                                                data-lab-item="other" data-label="ตรวจอื่นๆ ทั้งหมด">
                                                {{ number_format(array_sum(array_column($monthly_stats, 'other_count'))) }}
                                            </a>
                                        </td>

                                        <td class="text-num text-purple">{{ number_format($total_tests) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Details Modal -->
    <div class="modal fade" id="patientDetailsModal" tabindex="-1" aria-labelledby="patientDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header text-white py-3">
                    <h5 class="modal-title fw-bold text-white mb-0" id="patientDetailsModalLabel">
                        <i class="fas fa-vial text-white me-2"></i>รายชื่อผู้รับบริการ Lab Anywhere
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="patientDetailsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">กำลังดึงข้อมูลรายชื่อผู้รับบริการ...</p>
                    </div>

                    <div id="patientDetailsContent" class="d-none">
                        <div class="modal-table-container">
                            <table class="table table-hover align-middle w-100 mb-0" id="table-patient-details" style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width: 45px;">#</th>
                                        <th class="text-center" style="width: 95px;">วันที่/เวลา</th>
                                        <th class="text-center" style="width: 90px;">HN</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th class="text-center" style="width: 70px;">อายุ/เพศ</th>
                                        <th style="width: 140px;">สิทธิการรักษา</th>
                                        <th class="text-center" style="width: 75px;">PDX</th>
                                        <th>รายการแล็บและผลตรวจ</th>
                                        <th style="width: 140px;">แพทย์/ผู้สั่ง</th>
                                    </tr>
                                </thead>
                                <tbody id="patientDetailsBody">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" style="border-radius: 8px;" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                // Flatpickr Init
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
                            const container = instance.calendarContainer;
                            if (container && !container.querySelector('.flatpickr-today-button')) {
                                const btn = document.createElement("div");
                                btn.className = "flatpickr-today-button";
                                btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                                btn.addEventListener("mousedown", function(e) {
                                    e.preventDefault();
                                    instance.setDate(new Date());
                                    instance.close();
                                });
                                container.appendChild(btn);
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
                        var selectedYear = parseInt($(this).val());
                        if(!isNaN(selectedYear)) {
                            var startYear = selectedYear - 544;
                            var endYear = selectedYear - 543;
                            var startDateStr = startYear + "-10-01";
                            var endDateStr = endYear + "-09-30";
                            setTimeout(() => {
                                if (startPicker) startPicker.setDate(startDateStr, true);
                                if (endPicker) endPicker.setDate(endDateStr, true);
                            }, 50);
                        }
                    });
                }

                // Excel Export
                $('.btn-export-excel').on('click', function() {
                    const target = $(this).data('target');
                    const title = $(this).closest('.card-header').find('h6').text().trim();
                    const dt = $(target).DataTable({
                        destroy: true,
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: false,
                        autoWidth: false,
                        dom: 'tB',
                        buttons: [{
                            extend: 'excelHtml5',
                            title: title,
                            messageTop: 'ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}',
                            filename: title + '_{{ date("Ymd") }}'
                        }]
                    });
                    dt.button(0).trigger();
                    dt.destroy();
                });

                // 1. Monthly Trend Chart (Area / Line / Bar)
                const monthlyTrendOptions = {
                    series: [{
                        name: 'จำนวนสั่งตรวจ (ครั้ง)',
                        type: 'column',
                        data: @json($orders_series)
                    }, {
                        name: 'จำนวนผู้ป่วย (คน)',
                        type: 'line',
                        data: @json($hns_series)
                    }],
                    chart: {
                        height: 350,
                        type: 'line',
                        toolbar: { show: false }
                    },
                    stroke: {
                        width: [0, 3],
                        curve: 'smooth'
                    },
                    colors: ['#3b82f6', '#10b981'],
                    plotOptions: {
                        bar: {
                            columnWidth: '50%',
                            borderRadius: 4
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        enabledOnSeries: [0, 1],
                        style: { fontSize: '10px' },
                        formatter: val => val > 0 ? val.toLocaleString() : ''
                    },
                    xaxis: {
                        categories: @json($months)
                    },
                    yaxis: [{
                        title: { text: 'จำนวนสั่งตรวจ (ครั้ง)' },
                        labels: { formatter: val => Math.round(val).toLocaleString() }
                    }, {
                        opposite: true,
                        title: { text: 'จำนวนผู้ป่วย (คน)' },
                        labels: { formatter: val => Math.round(val).toLocaleString() }
                    }],
                    legend: { position: 'top' }
                };
                const monthlyTrendChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyTrendOptions);
                monthlyTrendChart.render();

                // 2. Top Lab Tests Chart
                const topItemsOptions = {
                    series: [{
                        name: 'จำนวนรายการตรวจ',
                        data: @json($top_items_counts)
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                            barHeight: '65%'
                        }
                    },
                    colors: ['#8b5cf6'],
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', colors: ['#fff'] },
                        formatter: val => val.toLocaleString()
                    },
                    xaxis: {
                        categories: @json($top_items_names),
                        labels: { formatter: val => val.toLocaleString() }
                    }
                };
                const topItemsChart = new ApexCharts(document.querySelector("#topItemsChart"), topItemsOptions);
                topItemsChart.render();

                // 3. Top Diagnoses Chart
                const topDiagsOptions = {
                    series: [{
                        name: 'จำนวนครั้งที่สั่งตรวจ',
                        data: @json($top_diags_counts)
                    }],
                    chart: {
                        type: 'bar',
                        height: 380,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                            barHeight: '65%'
                        }
                    },
                    colors: ['#0284c7'],
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', colors: ['#fff'] },
                        formatter: val => val.toLocaleString()
                    },
                    xaxis: {
                        categories: @json($top_diags_names),
                        labels: { formatter: val => val.toLocaleString() }
                    }
                };
                const topDiagsChart = new ApexCharts(document.querySelector("#topDiagsChart"), topDiagsOptions);
                topDiagsChart.render();

                // 4. Top Ordering Doctors Chart
                const topDoctorsOptions = {
                    series: [{
                        name: 'จำนวนสั่งตรวจ',
                        data: @json($top_doctors_counts)
                    }],
                    chart: {
                        type: 'bar',
                        height: 380,
                        toolbar: { show: false }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                            barHeight: '65%'
                        }
                    },
                    colors: ['#f59e0b'],
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', colors: ['#fff'] },
                        formatter: val => val.toLocaleString()
                    },
                    xaxis: {
                        categories: @json($top_doctors_names),
                        labels: { formatter: val => val.toLocaleString() }
                    }
                };
                const topDoctorsChart = new ApexCharts(document.querySelector("#topDoctorsChart"), topDoctorsOptions);
                topDoctorsChart.render();

                // Patient Details Modal Handler
                $('.btn-view-patients-detail').on('click', function() {
                    const monthKey = $(this).data('month-key');
                    const monthLabel = $(this).data('month-label');
                    const labItem = $(this).data('lab-item');
                    const label = $(this).data('label');

                    $('#patientDetailsModalLabel').html(`<i class="fas fa-vial text-white me-2"></i>รายชื่อผู้รับบริการ Lab Anywhere: <span class="text-warning">${label}</span> (${monthLabel})`);

                    const modal = new bootstrap.Modal(document.getElementById('patientDetailsModal'));
                    modal.show();

                    $('#patientDetailsLoading').removeClass('d-none');
                    $('#patientDetailsContent').addClass('d-none');
                    $('#patientDetailsBody').html('');

                    if ($.fn.DataTable.isDataTable('#table-patient-details')) {
                        $('#table-patient-details').DataTable().destroy();
                    }

                    $.ajax({
                        url: '{{ route("hosxp.lab.anywhere.patients") }}',
                        method: 'GET',
                        data: {
                            start_date: '{{ $start_date }}',
                            end_date: '{{ $end_date }}',
                            month_key: monthKey,
                            lab_item: labItem
                        },
                        success: function(response) {
                            let html = '';
                            if (response.patients && response.patients.length > 0) {
                                response.patients.forEach((patient, index) => {
                                    const pdxBadge = patient.pdx && patient.pdx !== '-' 
                                        ? `<span class="badge-pdx" title="${patient.pdx_name || ''}">${patient.pdx}</span>` 
                                        : '<span class="text-muted">-</span>';
                                    
                                    const resultsBadge = patient.lab_results_text && patient.lab_results_text !== '-'
                                        ? `<span class="badge-results">${patient.lab_results_text}</span>`
                                        : '<span class="text-muted">-</span>';

                                    html += `
                                        <tr>
                                            <td class="text-center text-muted font-monospace">${index + 1}</td>
                                            <td class="text-center text-secondary">
                                                <div>${patient.order_date_thai || ''}</div>
                                                <small class="text-muted">${patient.order_time || ''}</small>
                                            </td>
                                            <td class="text-center patient-hn">${patient.hn || ''}</td>
                                            <td class="patient-name">${patient.patient_name || ''}</td>
                                            <td class="text-center">${patient.age_y || '-'}/${patient.sex_name || '-'}</td>
                                            <td><small class="text-muted">${patient.pttype_name || '-'}</small></td>
                                            <td class="text-center">${pdxBadge}</td>
                                            <td>${resultsBadge}</td>
                                            <td class="text-secondary"><small>${patient.doctor_name || ''}</small></td>
                                        </tr>
                                    `;
                                });
                            } else {
                                html = `<tr><td colspan="9" class="text-center text-muted py-4">ไม่พบข้อมูลรายชื่อคนไข้ในช่วงเวลาดังกล่าว</td></tr>`;
                            }
                            
                            $('#patientDetailsBody').html(html);
                            $('#patientDetailsLoading').addClass('d-none');
                            $('#patientDetailsContent').removeClass('d-none');

                            if (response.patients && response.patients.length > 0) {
                                $('#table-patient-details').DataTable({
                                    pageLength: 10,
                                    lengthMenu: [10, 25, 50, 100, 500],
                                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                                    buttons: [{
                                        extend: 'excelHtml5',
                                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                                        className: 'btn btn-success btn-sm',
                                        title: `รายชื่อผู้รับบริการ Lab Anywhere - ${label} (${monthLabel})`,
                                        exportOptions: { columns: ':visible' }
                                    }],
                                    language: {
                                        search: "ค้นหา:",
                                        lengthMenu: "แสดง _MENU_ รายการ",
                                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                                        paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                                    }
                                });
                            }
                        },
                        error: function(xhr) {
                            $('#patientDetailsBody').html(`<tr><td colspan="9" class="text-center text-danger py-4">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>`);
                            $('#patientDetailsLoading').addClass('d-none');
                            $('#patientDetailsContent').removeClass('d-none');
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection

