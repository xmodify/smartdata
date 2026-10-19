@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #dc3545; transition: all 0.3s;">
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

        .header-form-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
                flex-wrap: wrap;
            }

            .input-group-date,
            .input-group-budget {
                width: 100% !important;
            }
        }

        .card-ems {
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .card-ems:hover {
            transform: translateY(-4px);
        }

        /* Override DataTables UI to match the premium look */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            outline: none !important;
            font-size: 0.8rem !important;
        }

        .dt-buttons .btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
            border-radius: 0.4rem !important;
            font-weight: 500 !important;
            padding: 0.25rem 0.6rem !important;
            font-size: 0.75rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            box-shadow: 0 2px 4px rgba(25, 135, 84, 0.2) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4e73df !important;
            color: white !important;
            border: 1px solid #4e73df !important;
            border-radius: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f8f9fc !important;
            color: #4e73df !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
        }

        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #e3e6f0 !important;
            font-size: 0.85rem !important;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0rem;
        }

        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
            font-size: 0.85rem;
        }

        .dt-buttons {
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        .table-ems tbody td {
            font-size: 0.82rem;
            vertical-align: middle;
        }

        .text-ems-red { color: #e11d48; }
        .bg-pastel-red { background-color: #fff1f2; }
        
        .flatpickr-today-button {
            padding: 10px;
            text-align: center;
            border-top: 1px solid #e6e6e6;
            cursor: pointer;
            font-weight: bold;
            color: #e11d48;
            background: #f8f9fa;
        }
        .flatpickr-today-button:hover {
            background: #fff1f2;
        }

        /* Diagnostic List Style */
        .diag-item {
            padding: 9px 12px;
            border-bottom: 1px solid #f1f5f9;
            position: relative;
        }
        .diag-item:last-child {
            border-bottom: none;
        }
        .diag-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(59, 130, 246, 0.15);
            transition: width 0.6s ease;
        }
        .diag-progress.bg-als { background: rgba(239, 68, 68, 0.2); }
        .diag-progress.bg-bls { background: rgba(2, 132, 199, 0.2); }
        .diag-progress.bg-ils { background: rgba(59, 130, 246, 0.2); }
        .diag-progress.bg-fr { background: rgba(16, 185, 129, 0.2); }

        .diag-progress.bg-sev1 { background: rgba(190, 18, 60, 0.25); }
        .diag-progress.bg-sev2 { background: rgba(234, 88, 12, 0.25); }
        .diag-progress.bg-sev3 { background: rgba(202, 138, 4, 0.25); }
        .diag-progress.bg-sev4 { background: rgba(37, 99, 235, 0.25); }
        .diag-progress.bg-sev5 { background: rgba(22, 163, 74, 0.25); }

        .nav-pills-custom .nav-link {
            color: #64748b;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-pills-custom .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
            box-shadow: 0 2px 6px rgba(13, 110, 253, 0.25);
        }
        .nav-pills-custom .nav-link:hover:not(.active) {
            background-color: #e2e8f0;
            color: #1e293b;
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
                        <i class="fas fa-ambulance text-ems-red me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0">
                    <div class="input-group input-group-sm shadow-sm" style="width: 230px; border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-light text-danger border-end-0 fw-bold" style="font-size: 0.8rem;">เลือก</span>
                        <select class="form-select border-start-0" name="budget_year" onchange="this.form.submit()" style="font-size: 0.85rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @php
            $total_count = count($ems_list);
            $als_count = count(array_filter($ems_list, function($item) { return $item->ems === 'ALS'; }));
            $bls_count = count(array_filter($ems_list, function($item) { return $item->ems === 'BLS'; }));
            $ils_count = count(array_filter($ems_list, function($item) { return $item->ems === 'ILS'; }));
            $fr_count = count(array_filter($ems_list, function($item) { return $item->ems === 'FR'; }));

            $level_1_count = count(array_filter($ems_list, function($item) { return $item->emergency_type_id == 1 || $item->er_emergency_type === 'Resuscitate'; }));
            $level_2_count = count(array_filter($ems_list, function($item) { return $item->emergency_type_id == 2 || $item->er_emergency_type === 'Emergency'; }));
            $level_3_count = count(array_filter($ems_list, function($item) { return $item->emergency_type_id == 3 || $item->er_emergency_type === 'Urgency'; }));
            $level_4_count = count(array_filter($ems_list, function($item) { return $item->emergency_type_id == 4 || $item->er_emergency_type === 'Semi_Urgency'; }));
            $level_5_count = count(array_filter($ems_list, function($item) { return $item->emergency_type_id == 5 || $item->er_emergency_type === 'Non_Urgency'; }));
            $level_null_count = count(array_filter($ems_list, function($item) {
                return (empty($item->emergency_type_id) && empty($item->er_emergency_type)) || $item->er_emergency_type === 'Unspecified';
            }));
        @endphp

        <!-- Section 1: Summary Cards by EMS Unit -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold text-muted text-uppercase">
                <i class="fas fa-ambulance text-primary me-1"></i> สรุปจำแนกตามระดับบริการ EMS (ทีมปฏิบัติการ)
            </span>
            <span class="badge bg-light text-dark border">
                ข้อมูลช่วง: <span class="badge-range-text fw-bold text-primary">{{ DateThai($table_start_date) }} - {{ DateThai($table_end_date) }}</span>
            </span>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 mb-4 g-3">
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #475569 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-users fa-2x text-secondary opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($total_count) }}</h3>
                        <div class="small fw-bold text-muted mt-1">ผู้ป่วยให้บริการ EMS ทั้งหมด</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #dc3545 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-ambulance fa-2x text-danger opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($als_count) }}</h3>
                        <div class="small fw-bold text-danger mt-1">ALS (Advanced Life Support)</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #0284c7 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-ambulance fa-2x opacity-75" style="color: #0284c7;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #0284c7;">{{ number_format($bls_count) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #0284c7;">BLS (Basic Life Support)</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #0d6efd !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-ambulance fa-2x text-primary opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-primary">{{ number_format($ils_count) }}</h3>
                        <div class="small fw-bold text-primary mt-1">ILS (Intermediate Life Support)</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #198754 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-ambulance fa-2x text-success opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($fr_count) }}</h3>
                        <div class="small fw-bold text-success mt-1">FR (First Responder)</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Summary Cards by ER Severity (Triage 5 Levels) -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-bold text-muted text-uppercase">
                <i class="fas fa-heartbeat text-danger me-1"></i> สรุปจำแนกตามระดับความรุนแรง ER (Triage 5 ระดับ)
            </span>
            @if($level_null_count > 0)
                <span class="badge bg-light text-secondary border">
                    <i class="fas fa-info-circle me-1"></i> ไม่ระบุระดับ: <span class="fw-bold text-dark">{{ number_format($level_null_count) }}</span> ราย
                </span>
            @endif
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 mb-4 g-3">
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #be123c !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-heartbeat fa-2x text-danger opacity-75"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #be123c;">{{ number_format($level_1_count) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #be123c;">Resuscitate (กู้ชีพทันที)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Level 1 • แดง</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #ea580c !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-exclamation-triangle fa-2x opacity-75" style="color: #ea580c;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #ea580c;">{{ number_format($level_2_count) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #ea580c;">Emergency (ฉุกเฉินเร่งด่วน)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Level 2 • ส้ม</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #ca8a04 !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-bell fa-2x opacity-75" style="color: #ca8a04;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #ca8a04;">{{ number_format($level_3_count) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #ca8a04;">Urgency (ด่วนมาก)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Level 3 • เหลือง</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #2563eb !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-check-circle fa-2x opacity-75" style="color: #2563eb;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #2563eb;">{{ number_format($level_4_count) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #2563eb;">Semi Urgency (ด่วน)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Level 4 • น้ำเงิน</div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-ems shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #16a34a !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-2"><i class="fas fa-smile fa-2x opacity-75" style="color: #16a34a;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #16a34a;">{{ number_format($level_5_count) }}</h3>
                        <div class="small fw-bold mt-1" style="color: #16a34a;">Non Urgency (รอได้)</div>
                        <div class="text-muted" style="font-size: 0.72rem;">Level 5 • เขียว</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trends Chart Row (Switcher for EMS Team vs 5 Severity Levels) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-ems" style="border-radius: 15px;">
                    <div class="card-header bg-light py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-radius: 16px 16px 0 0;">
                        <h6 class="fw-bold mb-0 text-dark" id="chart-title">
                            <i class="fas fa-chart-line text-primary me-2"></i>จำนวนผู้รับบริการ EMS แยกรายเดือน (ALS, BLS, ILS, FR)
                        </h6>
                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                            <button type="button" class="btn btn-primary active fw-bold" id="btnChartEms" style="font-size: 0.8rem;">
                                <i class="fas fa-ambulance me-1"></i> ระดับบริการ EMS
                            </button>
                            <button type="button" class="btn btn-outline-danger fw-bold" id="btnChartSeverity" style="font-size: 0.8rem;">
                                <i class="fas fa-heartbeat me-1"></i> ระดับความรุนแรง 5 ระดับ
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- ApexChart Container (Single instance updated via JS) -->
                        <div id="chart-ems-monthly"></div>

                        <!-- Divider -->
                        <hr class="my-4 text-muted opacity-25">

                        <!-- Monthly Breakdown Table: Tab 1 (EMS Services) -->
                        <div id="table-monthly-ems-wrapper">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-dark">
                                    <i class="fas fa-table text-primary me-1"></i> ตารางสถิติผู้ป่วย EMS รายเดือน จำแนกตามระดับบริการ (ALS, BLS, ILS, FR)
                                </span>
                                <span class="badge bg-light text-muted border" style="font-size: 0.75rem;">
                                    หน่วย: ราย
                                </span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap table-sm" style="font-size: 0.82rem;">
                                    <thead class="table-light">
                                        <tr class="text-center align-middle" style="background-color: #f8fafc;">
                                            <th class="text-start ps-3" style="min-width: 190px;">ประเภท / ระดับบริการ EMS</th>
                                            @foreach($ems_monthly as $m)
                                                <th style="min-width: 65px;">{{ $m->month_year }}</th>
                                            @endforeach
                                            <th class="bg-primary text-white" style="min-width: 85px;">รวมทั้งปี</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge bg-danger me-1">ALS</span> ALS (Advanced Life Support)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->als) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold text-danger bg-light">{{ number_format($year_summary['als']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge text-white me-1" style="background-color: #0284c7;">BLS</span> BLS (Basic Life Support)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->bls) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold bg-light" style="color: #0284c7;">{{ number_format($year_summary['bls']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge bg-primary me-1">ILS</span> ILS (Intermediate Life Support)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->ils) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold text-primary bg-light">{{ number_format($year_summary['ils']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge bg-success me-1">FR</span> FR (First Responder)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->fr) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold text-success bg-light">{{ number_format($year_summary['fr']) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr style="border-top: 2px solid #cbd5e1;">
                                            <td class="ps-3 text-dark fw-bold">
                                                <i class="fas fa-users me-1 text-secondary"></i> รวมผู้ป่วย EMS ทั้งหมด
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center text-dark fw-bold">{{ number_format($m->total_visit) }}</td>
                                            @endforeach
                                            <td class="text-center text-dark fw-bold bg-secondary bg-opacity-25">{{ number_format($year_summary['total']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Monthly Breakdown Table: Tab 2 (Severity 5 Levels) -->
                        <div id="table-monthly-severity-wrapper" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-dark">
                                    <i class="fas fa-table text-danger me-1"></i> ตารางสถิติผู้ป่วย EMS รายเดือน จำแนกตามระดับความรุนแรง ER (Triage 5 ระดับ)
                                </span>
                                <span class="badge bg-light text-muted border" style="font-size: 0.75rem;">
                                    หน่วย: ราย
                                </span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle mb-0 text-nowrap table-sm" style="font-size: 0.82rem;">
                                    <thead class="table-light">
                                        <tr class="text-center align-middle" style="background-color: #f8fafc;">
                                            <th class="text-start ps-3" style="min-width: 190px;">ประเภท / ระดับความรุนแรง ER</th>
                                            @foreach($ems_monthly as $m)
                                                <th style="min-width: 65px;">{{ $m->month_year }}</th>
                                            @endforeach
                                            <th class="bg-danger text-white" style="min-width: 85px;">รวมทั้งปี</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge text-white me-1" style="background-color: #be123c;">Level 1</span> Resuscitate (กู้ชีพทันที)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->level_1) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold bg-light" style="color: #be123c;">{{ number_format($year_summary['level_1']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge text-white me-1" style="background-color: #ea580c;">Level 2</span> Emergency (ฉุกเฉินเร่งด่วน)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->level_2) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold bg-light" style="color: #ea580c;">{{ number_format($year_summary['level_2']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge text-white me-1" style="background-color: #ca8a04;">Level 3</span> Urgency (ด่วนมาก)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->level_3) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold bg-light" style="color: #ca8a04;">{{ number_format($year_summary['level_3']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge text-white me-1" style="background-color: #2563eb;">Level 4</span> Semi Urgency (ด่วน)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->level_4) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold bg-light" style="color: #2563eb;">{{ number_format($year_summary['level_4']) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <span class="badge text-white me-1" style="background-color: #16a34a;">Level 5</span> Non Urgency (รอได้)
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center">{{ number_format($m->level_5) }}</td>
                                            @endforeach
                                            <td class="text-center fw-bold bg-light" style="color: #16a34a;">{{ number_format($year_summary['level_5']) }}</td>
                                        </tr>
                                        @if($year_summary['level_null'] > 0)
                                            <tr>
                                                <td class="ps-3 fw-bold text-muted">
                                                    <span class="badge bg-secondary me-1">ไม่ระบุ</span> ไม่ระบุระดับความรุนแรง
                                                </td>
                                                @foreach($ems_monthly as $m)
                                                    <td class="text-center text-muted">{{ number_format($m->level_null) }}</td>
                                                @endforeach
                                                <td class="text-center fw-bold text-muted bg-light">{{ number_format($year_summary['level_null']) }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr style="border-top: 2px solid #cbd5e1;">
                                            <td class="ps-3 text-dark fw-bold">
                                                <i class="fas fa-users me-1 text-secondary"></i> รวมผู้ป่วย EMS ทั้งหมด
                                            </td>
                                            @foreach($ems_monthly as $m)
                                                <td class="text-center text-dark fw-bold">{{ number_format($m->total_visit) }}</td>
                                            @endforeach
                                            <td class="text-center text-dark fw-bold bg-secondary bg-opacity-25">{{ number_format($year_summary['total']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top 20 Diagnoses Section (Tabs for EMS Team vs 5 Severity Levels) -->
        <div class="card border-0 shadow-sm card-ems mb-4" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 border-0 d-flex flex-wrap justify-content-between align-items-center gap-2" style="border-radius: 16px 16px 0 0;">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-trophy text-warning me-2"></i>20 อันดับโรคสูงสุดของผู้ป่วย EMS (ข้อมูลปีงบประมาณ {{ $budget_year }})
                </h6>
                <ul class="nav nav-pills nav-pills-custom shadow-sm p-1 bg-light" style="border-radius: 10px;" id="diagTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1 px-3 fw-bold small" id="tab-ems-diag" data-bs-toggle="pill" data-bs-target="#pane-ems-diag" type="button" role="tab">
                            <i class="fas fa-ambulance me-1"></i> แยกตามระดับบริการ EMS (4 ทีม)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 fw-bold small" id="tab-severity-diag" data-bs-toggle="pill" data-bs-target="#pane-severity-diag" type="button" role="tab">
                            <i class="fas fa-heartbeat me-1"></i> แยกตามระดับความรุนแรง 5 ระดับ
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-3">
                <div class="tab-content" id="diagTabContent">
                    <!-- Tab 1: EMS Units (ALS, BLS, ILS, FR) -->
                    <div class="tab-pane fade show active" id="pane-ems-diag" role="tabpanel">
                        <div class="row g-3">
                            <!-- ALS Top Diags -->
                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header bg-danger text-white py-2 border-0" style="border-radius: 12px 12px 0 0;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-trophy me-1"></i>20 อันดับโรคสูงสุด (ALS)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_als))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_als = reset($ems_diag_als)->sum ?? 1; @endphp
                                            @foreach($ems_diag_als as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge bg-danger rounded-pill">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-als" style="width: {{ ($row->sum / $max_als) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- BLS Top Diags -->
                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header text-white py-2 border-0" style="border-radius: 12px 12px 0 0; background-color: #0284c7;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-trophy me-1"></i>20 อันดับโรคสูงสุด (BLS)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_bls))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_bls = reset($ems_diag_bls)->sum ?? 1; @endphp
                                            @foreach($ems_diag_bls as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge rounded-pill text-white" style="background-color: #0284c7;">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-bls" style="width: {{ ($row->sum / $max_bls) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- ILS Top Diags -->
                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header bg-primary text-white py-2 border-0" style="border-radius: 12px 12px 0 0;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-trophy me-1"></i>20 อันดับโรคสูงสุด (ILS)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_ils))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_ils = reset($ems_diag_ils)->sum ?? 1; @endphp
                                            @foreach($ems_diag_ils as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge bg-primary rounded-pill">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-ils" style="width: {{ ($row->sum / $max_ils) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- FR Top Diags -->
                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header bg-success text-white py-2 border-0" style="border-radius: 12px 12px 0 0;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-trophy me-1"></i>20 อันดับโรคสูงสุด (FR)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_fr))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_fr = reset($ems_diag_fr)->sum ?? 1; @endphp
                                            @foreach($ems_diag_fr as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge bg-success rounded-pill">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-fr" style="width: {{ ($row->sum / $max_fr) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: 5 Severity Levels -->
                    <div class="tab-pane fade" id="pane-severity-diag" role="tabpanel">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3">
                            <!-- Level 1: Resuscitate -->
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header text-white py-2 border-0" style="border-radius: 12px 12px 0 0; background-color: #be123c;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-heartbeat me-1"></i>1: Resuscitate (กู้ชีพ)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_sev1))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_sev1 = reset($ems_diag_sev1)->sum ?? 1; @endphp
                                            @foreach($ems_diag_sev1 as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge rounded-pill text-white" style="background-color: #be123c;">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-sev1" style="width: {{ ($row->sum / $max_sev1) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Level 2: Emergency -->
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header text-white py-2 border-0" style="border-radius: 12px 12px 0 0; background-color: #ea580c;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-exclamation-triangle me-1"></i>2: Emergency (ฉุกเฉิน)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_sev2))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_sev2 = reset($ems_diag_sev2)->sum ?? 1; @endphp
                                            @foreach($ems_diag_sev2 as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge rounded-pill text-white" style="background-color: #ea580c;">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-sev2" style="width: {{ ($row->sum / $max_sev2) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Level 3: Urgency -->
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header text-white py-2 border-0" style="border-radius: 12px 12px 0 0; background-color: #ca8a04;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-bell me-1"></i>3: Urgency (ด่วนมาก)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_sev3))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_sev3 = reset($ems_diag_sev3)->sum ?? 1; @endphp
                                            @foreach($ems_diag_sev3 as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge rounded-pill text-white" style="background-color: #ca8a04;">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-sev3" style="width: {{ ($row->sum / $max_sev3) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Level 4: Semi Urgency -->
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header text-white py-2 border-0" style="border-radius: 12px 12px 0 0; background-color: #2563eb;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-check-circle me-1"></i>4: Semi Urgency (ด่วน)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_sev4))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_sev4 = reset($ems_diag_sev4)->sum ?? 1; @endphp
                                            @foreach($ems_diag_sev4 as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge rounded-pill text-white" style="background-color: #2563eb;">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-sev4" style="width: {{ ($row->sum / $max_sev4) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Level 5: Non Urgency -->
                            <div class="col">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border: 1px solid #f1f5f9 !important;">
                                    <div class="card-header text-white py-2 border-0" style="border-radius: 12px 12px 0 0; background-color: #16a34a;">
                                        <h6 class="fw-bold mb-0 small"><i class="fas fa-smile me-1"></i>5: Non Urgency (รอได้)</h6>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        @if(empty($ems_diag_sev5))
                                            <div class="p-4 text-center text-muted small">ไม่มีข้อมูล</div>
                                        @else
                                            @php $max_sev5 = reset($ems_diag_sev5)->sum ?? 1; @endphp
                                            @foreach($ems_diag_sev5 as $index => $row)
                                                <div class="diag-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="small text-dark fw-bold" style="max-width: 80%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $row->name }}">
                                                            @if($index < 3 && $row->sum > 0)
                                                                <span class="badge rounded-pill bg-warning text-dark px-2 me-1">
                                                                    <i class="fas fa-crown"></i> {{ $index + 1 }}
                                                                </span>
                                                            @else
                                                                <span class="me-1 fw-bold text-muted">{{ $index + 1 }}.</span>
                                                            @endif
                                                            {{ $row->name }}
                                                        </span>
                                                        <span class="badge rounded-pill text-white" style="background-color: #16a34a;">{{ number_format($row->sum) }}</span>
                                                    </div>
                                                    <div class="diag-progress bg-sev5" style="width: {{ ($row->sum / $max_sev5) * 100 }}%"></div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Patient List Table with Dynamic Filters -->
        <div class="row pb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm card-ems" style="border-radius: 15px;">
                    <div class="card-header bg-white py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h6 class="m-0 fw-bold text-primary">
                                    <i class="fas fa-table me-2"></i> รายชื่อผู้ป่วยให้บริการ EMS
                                </h6>
                                <small class="text-muted">
                                    ช่วงวันที่: <span id="displayDateRange" class="fw-bold text-dark">{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}</span>
                                </small>
                            </div>

                            <!-- Independent Table Filters: Severity + EMS Type + Date Range -->
                            <div class="d-flex align-items-center flex-wrap gap-2">
                                <!-- Severity Filter -->
                                <div class="input-group input-group-sm" style="width: 175px;">
                                    <span class="input-group-text bg-white border-end-0 text-danger"><i class="fas fa-heartbeat"></i></span>
                                    <select class="form-select border-start-0" id="filter_severity">
                                        <option value="all">ความรุนแรง: ทั้งหมด</option>
                                        <option value="1">1: Resuscitate (กู้ชีพ)</option>
                                        <option value="2">2: Emergency (ฉุกเฉิน)</option>
                                        <option value="3">3: Urgency (ด่วนมาก)</option>
                                        <option value="4">4: Semi Urgency (ด่วน)</option>
                                        <option value="5">5: Non Urgency (รอได้)</option>
                                        <option value="null">ไม่ระบุระดับ</option>
                                    </select>
                                </div>

                                <!-- EMS Type Filter -->
                                <div class="input-group input-group-sm" style="width: 155px;">
                                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-ambulance"></i></span>
                                    <select class="form-select border-start-0" id="filter_ems_type">
                                        <option value="all">ระดับ EMS: ทั้งหมด</option>
                                        <option value="ALS">ALS</option>
                                        <option value="BLS">BLS</option>
                                        <option value="ILS">ILS</option>
                                        <option value="FR">FR</option>
                                    </select>
                                </div>

                                <!-- Date Pickers -->
                                <div class="input-group input-group-sm" style="width: 135px;">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" id="table_start_date" class="form-control border-start-0 ps-0 text-center"
                                        value="{{ $table_start_date }}" placeholder="เริ่ม">
                                </div>
                                <span class="text-muted small">ถึง</span>
                                <div class="input-group input-group-sm" style="width: 135px;">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" id="table_end_date" class="form-control border-start-0 ps-0 text-center"
                                        value="{{ $table_end_date }}" placeholder="สิ้นสุด">
                                </div>
                                <button type="button" id="btnFilterTable" class="btn btn-sm btn-danger px-3 shadow-sm" style="border-radius: 6px; background-color: #e11d48; border-color: #e11d48;">
                                    <i class="fas fa-search me-1"></i> ค้นหา
                                </button>
                                <div class="btn-group btn-group-sm ms-1 shadow-sm">
                                    <button type="button" id="btnCurrentMonth" class="btn btn-outline-secondary" title="เลือกเดือนปัจจุบัน">
                                        เดือนนี้
                                    </button>
                                    <button type="button" id="btnPrevMonth" class="btn btn-outline-secondary" title="เลือกเดือนก่อนหน้า">
                                        เดือนก่อน
                                    </button>
                                </div>
                                <button type="button" id="btnResetTableFilter" class="btn btn-sm btn-outline-secondary shadow-sm" title="ล้างตัวกรอง">
                                    <i class="fas fa-redo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4" id="tableContainer">
                        @include('hosxp.er.partials._table_ems')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
        <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
        
        <script>
            $(document).ready(function() {
                // Initialize DataTable
                function initPatientTable() {
                    if ($.fn.DataTable.isDataTable('#table-ems-list')) {
                        $('#table-ems-list').DataTable().destroy();
                    }
                    $('#table-ems-list').DataTable({
                        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                        buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success',
                            title: '{{ $title }}',
                            messageTop: function() {
                                return 'ช่วงวันที่: ' + ($('#displayDateRange').text() || '{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}');
                            }
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

                let tableStartPicker, tableEndPicker;
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
                        allowInput: false,
                        onReady: function(selectedDates, dateStr, instance) {
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
                    tableStartPicker = flatpickr("#table_start_date", commonConfig);
                    tableEndPicker = flatpickr("#table_end_date", commonConfig);
                }

                function loadTableData(startDate, endDate, emsType, severityType) {
                    const $btn = $('#btnFilterTable');
                    const originalText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> ค้นหา...');
                    $('#tableContainer').css('opacity', '0.5');

                    $.ajax({
                        url: "{{ route('hosxp.er.ems') }}",
                        method: 'GET',
                        data: {
                            budget_year: "{{ $budget_year }}",
                            table_start_date: startDate || $('#table_start_date').val(),
                            table_end_date: endDate || $('#table_end_date').val(),
                            ems_type: emsType !== undefined ? emsType : $('#filter_ems_type').val(),
                            severity_type: severityType !== undefined ? severityType : $('#filter_severity').val()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success && res.html) {
                                if ($.fn.DataTable.isDataTable('#table-ems-list')) {
                                    $('#table-ems-list').DataTable().destroy();
                                }
                                $('#tableContainer').html(res.html);
                                initPatientTable();
                                $('#displayDateRange').text(`${res.start_date_thai} ถึง ${res.end_date_thai}`);
                            }
                        },
                        error: function(err) {
                            console.error("Failed to load table data", err);
                            alert("เกิดข้อผิดพลาดในการโหลดข้อมูลตาราง กรุณาลองใหม่อีกครั้ง");
                        },
                        complete: function() {
                            $('#tableContainer').css('opacity', '1');
                            $btn.prop('disabled', false).html(originalText);
                        }
                    });
                }

                $('#btnFilterTable').on('click', function() {
                    const s = $('#table_start_date').val();
                    const e = $('#table_end_date').val();
                    loadTableData(s, e);
                });

                $('#filter_severity, #filter_ems_type').on('change', function() {
                    const s = $('#table_start_date').val();
                    const e = $('#table_end_date').val();
                    loadTableData(s, e);
                });

                $('#btnCurrentMonth').on('click', function() {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
                    const s = `${y}-${m}-01`;
                    const e = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

                    if (tableStartPicker) tableStartPicker.setDate(s, true);
                    if (tableEndPicker) tableEndPicker.setDate(e, true);
                    loadTableData(s, e);
                });

                $('#btnPrevMonth').on('click', function() {
                    const now = new Date();
                    const prev = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    const y = prev.getFullYear();
                    const m = String(prev.getMonth() + 1).padStart(2, '0');
                    const lastDay = new Date(y, prev.getMonth() + 1, 0).getDate();
                    const s = `${y}-${m}-01`;
                    const e = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

                    if (tableStartPicker) tableStartPicker.setDate(s, true);
                    if (tableEndPicker) tableEndPicker.setDate(e, true);
                    loadTableData(s, e);
                });

                $('#btnResetTableFilter').on('click', function() {
                    $('#filter_severity').val('all');
                    $('#filter_ems_type').val('all');
                    if (tableStartPicker) tableStartPicker.setDate("{{ $table_start_date }}", true);
                    if (tableEndPicker) tableEndPicker.setDate("{{ $table_end_date }}", true);
                    loadTableData("{{ $table_start_date }}", "{{ $table_end_date }}", 'all', 'all');
                });

                initPatientTable();

                // 1. Chart Data & Render for EMS Teams (ALS, BLS, ILS, FR)
                const monthlyCategories = {!! json_encode(array_column($ems_monthly, 'month_year')) !!};
                const alsData = {!! json_encode(array_map('intval', array_column($ems_monthly, 'als'))) !!};
                const blsData = {!! json_encode(array_map('intval', array_column($ems_monthly, 'bls'))) !!};
                const ilsData = {!! json_encode(array_map('intval', array_column($ems_monthly, 'ils'))) !!};
                const frData = {!! json_encode(array_map('intval', array_column($ems_monthly, 'fr'))) !!};

                const chartEmsOptions = {
                    series: [
                        { name: 'ALS (Advanced Life Support)', data: alsData },
                        { name: 'BLS (Basic Life Support)', data: blsData },
                        { name: 'ILS (Intermediate Life Support)', data: ilsData },
                        { name: 'FR (First Responder)', data: frData }
                    ],
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: { show: true },
                        fontFamily: 'inherit'
                    },
                    dataLabels: {
                        enabled: true
                    },
                    colors: ['#dc3545', '#0284c7', '#0d6efd', '#198754'],
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 5
                    },
                    xaxis: {
                        categories: monthlyCategories,
                        labels: { style: { fontSize: '11px', fontWeight: 600 } }
                    },
                    yaxis: {
                        title: { text: 'จำนวนผู้ป่วย (ราย)', style: { fontWeight: 600 } },
                        labels: { formatter: (val) => val.toLocaleString() }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'center'
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                };

                const chartEms = new ApexCharts(document.querySelector("#chart-ems-monthly"), chartEmsOptions);
                chartEms.render();

                // 2. Chart Data & Render for 5 Severity Levels (Resuscitate, Emergency, Urgency, Semi Urgency, Non Urgency)
                const sev1Data = {!! json_encode(array_map('intval', array_column($ems_monthly, 'level_1'))) !!};
                const sev2Data = {!! json_encode(array_map('intval', array_column($ems_monthly, 'level_2'))) !!};
                const sev3Data = {!! json_encode(array_map('intval', array_column($ems_monthly, 'level_3'))) !!};
                const sev4Data = {!! json_encode(array_map('intval', array_column($ems_monthly, 'level_4'))) !!};
                const sev5Data = {!! json_encode(array_map('intval', array_column($ems_monthly, 'level_5'))) !!};

                const chartSeverityOptions = {
                    series: [
                        { name: '1: Resuscitate (กู้ชีพทันที)', data: sev1Data },
                        { name: '2: Emergency (ฉุกเฉินเร่งด่วน)', data: sev2Data },
                        { name: '3: Urgency (ด่วนมาก)', data: sev3Data },
                        { name: '4: Semi Urgency (ด่วน)', data: sev4Data },
                        { name: '5: Non Urgency (รอได้)', data: sev5Data }
                    ],
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: { show: true },
                        fontFamily: 'inherit'
                    },
                    dataLabels: {
                        enabled: true
                    },
                    colors: ['#be123c', '#ea580c', '#ca8a04', '#2563eb', '#16a34a'],
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 5
                    },
                    xaxis: {
                        categories: monthlyCategories,
                        labels: { style: { fontSize: '11px', fontWeight: 600 } }
                    },
                    yaxis: {
                        title: { text: 'จำนวนผู้ป่วย (ราย)', style: { fontWeight: 600 } },
                        labels: { formatter: (val) => val.toLocaleString() }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'center'
                    },
                    grid: {
                        borderColor: '#f1f1f1'
                    }
                };

                // Toggle Chart & Table Views (Switch options on single chart instance for 100% reliability)
                $('#btnChartEms').on('click', function() {
                    $(this).removeClass('btn-outline-primary').addClass('btn-primary active');
                    $('#btnChartSeverity').removeClass('btn-danger active text-white').addClass('btn-outline-danger');
                    $('#chart-title').html('<i class="fas fa-chart-line text-primary me-2"></i>จำนวนผู้รับบริการ EMS แยกรายเดือน (ALS, BLS, ILS, FR)');

                    // Update chart to EMS datasets
                    chartEms.updateOptions({
                        series: [
                            { name: 'ALS (Advanced Life Support)', data: alsData },
                            { name: 'BLS (Basic Life Support)', data: blsData },
                            { name: 'ILS (Intermediate Life Support)', data: ilsData },
                            { name: 'FR (First Responder)', data: frData }
                        ],
                        colors: ['#dc3545', '#0284c7', '#0d6efd', '#198754']
                    });

                    // Toggle Tables
                    $('#table-monthly-severity-wrapper').hide();
                    $('#table-monthly-ems-wrapper').fadeIn(150);
                });

                $('#btnChartSeverity').on('click', function() {
                    $(this).removeClass('btn-outline-danger').addClass('btn-danger active text-white');
                    $('#btnChartEms').removeClass('btn-primary active').addClass('btn-outline-primary');
                    $('#chart-title').html('<i class="fas fa-heartbeat text-danger me-2"></i>จำนวนผู้รับบริการ EMS แยกตามระดับความรุนแรง 5 ระดับ (Triage 1-5)');

                    // Update chart to Severity datasets
                    chartEms.updateOptions({
                        series: [
                            { name: '1: Resuscitate (กู้ชีพทันที)', data: sev1Data },
                            { name: '2: Emergency (ฉุกเฉินเร่งด่วน)', data: sev2Data },
                            { name: '3: Urgency (ด่วนมาก)', data: sev3Data },
                            { name: '4: Semi Urgency (ด่วน)', data: sev4Data },
                            { name: '5: Non Urgency (รอได้)', data: sev5Data }
                        ],
                        colors: ['#be123c', '#ea580c', '#ca8a04', '#2563eb', '#16a34a']
                    });

                    // Toggle Tables
                    $('#table-monthly-ems-wrapper').hide();
                    $('#table-monthly-severity-wrapper').fadeIn(150);
                });
            });
        </script>
    @endpush
@endsection
