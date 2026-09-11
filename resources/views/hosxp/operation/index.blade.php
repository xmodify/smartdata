@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
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
            background: #f8fbfd;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            border: 1px solid #e3eef5;
        }

        body { background-color: #f4f7fa !important; }

        .header-form-controls {
            display: flex; align-items: center; gap: 0.5rem;
        }

        .input-group-date { width: 170px !important; }
        .input-group-date input.form-control { background-color: #fff !important; cursor: pointer; font-size: 0.85rem; }
        .input-group-status { width: 195px !important; }
        .input-group-status select.form-select { font-size: 0.85rem; }
        .input-group-budget { width: 285px !important; }
        .input-group-budget select.form-select { font-size: 0.85rem; }
        
        .flatpickr-today-button {
            padding: 8px 10px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            cursor: pointer;
            font-weight: 600;
            color: #e83e8c;
            background: #f8fafc;
            font-size: 0.85rem;
            transition: all 0.2s;
        }
        .flatpickr-today-button:hover {
            background: #fdf2f8;
            color: #d63384;
        }

        .card-or { 
            border-radius: 16px; 
            border: 1px solid #e3eef5 !important; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            background: #fff;
            overflow: hidden;
        }
        .chart-container { min-height: 330px; }
        
        .table-or { font-size: 0.85rem; }
        .table-or thead th { 
            background-color: #f8f9fa; 
            color: #334155; 
            font-weight: 700; 
            border-bottom: 2px solid #e2e8f0; 
        }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-status, .input-group-budget { width: 100% !important; }
        }

        /* Custom DataTables Styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            font-size: 0.8rem !important;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            float: none !important;
            margin-bottom: 0 !important;
        }
        .dataTables_wrapper .dataTables_filter label {
            margin-bottom: 0 !important;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.85rem;
            color: #475569;
        }
        .dataTables_wrapper .dt-buttons {
            float: none !important;
            margin-bottom: 0 !important;
        }
        .dt-buttons .btn-success {
            background-color: #1d6f42 !important;
            border-color: #1d6f42 !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            font-size: 0.8rem !important;
            padding: 0.4rem 1rem !important;
            margin-right: 0 !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 6px rgba(29, 111, 66, 0.1) !important;
            transition: all 0.2s ease !important;
        }
        .dt-buttons .btn-success:hover {
            background-color: #155130 !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(29, 111, 66, 0.2) !important;
        }
        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            border-bottom: 2px solid #e3e6f0 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4e73df !important;
            color: white !important;
            border: 1px solid #4e73df !important;
            border-radius: 0.5rem !important;
        }

        /* Custom Tabs Layout */
        .nav-tabs-custom {
            background: #fff;
            border-radius: 12px;
            padding: 0.5rem 0.5rem 0 0.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            border: 1px solid #f0f0f0;
            margin-bottom: 1.5rem;
        }

        #orTabs .nav-link {
            border: none;
            color: #6e707e;
            padding: 0.75rem 1.25rem;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s;
            background: transparent;
            font-size: 0.9rem;
        }
        
        #orTabs .nav-link:hover {
            color: #e83e8c !important;
            background-color: #fdf2f8;
        }
        #orTabs .nav-link.active {
            color: #e83e8c !important;
            background-color: #fff !important;
            border-bottom: 3px solid #e83e8c !important;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.02);
        }

        .badge-opd { background-color: #e0f2fe; color: #0284c7; }
        .badge-ipd { background-color: #dcfce7; color: #16a34a; }
        .badge-elective { background-color: #ede9fe; color: #7c3aed; }
        .badge-emergency { background-color: #fee2e2; color: #dc2626; }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-procedures text-danger me-2"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">
                        ปีงบประมาณ {{ $budget_year }} ({{ DateThai($by_start_date) }} - {{ DateThai($by_end_date) }}) | 
                        ช่วงวันที่สืบค้น: <span class="text-dark fw-bold">{{ DateThai($start_date) }}</span> ถึง <span class="text-dark fw-bold">{{ DateThai($end_date) }}</span> |
                        สถานะ: <span class="badge {{ $status == '3' ? 'bg-success' : ($status == 'all' ? 'bg-secondary' : 'bg-warning text-dark') }} fw-semibold">
                            @if ($status == '3')
                                ผ่าตัดจริง (เสร็จสิ้น)
                            @elseif ($status == 'not_done')
                                สั่งแต่ไม่ได้ผ่า / รอผ่าตัด
                            @elseif ($status == '1')
                                รอผ่าตัด (สั่ง set ไว้)
                            @elseif ($status == '9')
                                ยกเลิกการผ่าตัด
                            @else
                                ทั้งหมดทุกสถานะ
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-danger"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}" placeholder="วันที่เริ่ม">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-danger"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}" placeholder="ถึงวันที่">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-status">
                        <span class="input-group-text bg-white border-end-0 text-danger"><i class="fas fa-filter"></i></span>
                        <select class="form-select border-start-0 ps-0 fw-semibold" name="status" id="status_select">
                            <option value="3" {{ $status == '3' ? 'selected' : '' }}>ผ่าตัดจริง (เสร็จแล้ว)</option>
                            <option value="not_done" {{ $status == 'not_done' ? 'selected' : '' }}>สั่งแต่ไม่ได้ผ่า / รอผ่าตัด</option>
                            <option value="1" {{ $status == '1' ? 'selected' : '' }}>รอผ่าตัด (สั่ง set)</option>
                            <option value="9" {{ $status == '9' ? 'selected' : '' }}>ยกเลิกการผ่าตัด</option>
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>ทั้งหมดทุกสถานะ</option>
                        </select>
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget">
                        <select class="form-select border-end-0 fw-semibold" name="budget_year" id="budget_year_select">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-danger px-3">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary KPI Cards -->
        <div class="row mb-4 g-3">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card card-or shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #4e73df !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-procedures fa-2x text-primary opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-primary">{{ number_format($summary->total_cases ?? 0) }}</h3>
                        <div class="small fw-bold text-muted">ผ่าตัดทั้งหมด</div>
                        <span class="badge badge-opd mt-1">OPD: {{ number_format($summary->opd_cases ?? 0) }}</span>
                        <span class="badge badge-ipd mt-1">IPD: {{ number_format($summary->ipd_cases ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card card-or shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #1cc88a !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-check-circle fa-2x text-success opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($summary->completed_cases ?? 0) }}</h3>
                        <div class="small fw-bold text-muted">ผ่าตัดเสร็จแล้ว</div>
                        <div class="small text-success mt-1 fw-bold">
                            {{ ($summary->total_cases ?? 0) > 0 ? round((($summary->completed_cases ?? 0) / $summary->total_cases) * 100, 1) : 0 }}% ของทั้งหมด
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card card-or shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #7c3aed !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-calendar-check fa-2x text-purple opacity-50" style="color: #7c3aed;"></i></div>
                        <h3 class="fw-bold mb-0" style="color: #7c3aed;">{{ number_format($summary->elective_cases ?? 0) }}</h3>
                        <div class="small fw-bold text-muted">เคสนัดล่วงหน้า (Elective)</div>
                        <span class="badge badge-elective mt-1">ตามแผนการรักษา</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card card-or shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #e74a3b !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-truck-medical fa-2x text-danger opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($summary->emergency_cases ?? 0) }}</h3>
                        <div class="small fw-bold text-muted">ผ่าตัดฉุกเฉิน (Emergency)</div>
                        <span class="badge badge-emergency mt-1">เคสด่วน/ฉุกเฉิน</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card card-or shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #f6c23e !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-clock fa-2x text-warning opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-warning">{{ number_format(($summary->pending_cases ?? 0) + ($summary->cancelled_cases ?? 0)) }}</h3>
                        <div class="small fw-bold text-muted">สั่งแต่ไม่ได้ผ่า / รอผ่าตัด</div>
                        <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                            รอผ่าตัด {{ number_format($summary->pending_cases ?? 0) }} | ยกเลิก {{ number_format($summary->cancelled_cases ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card card-or shadow-sm border-0 h-100 bg-white" style="border-top: 4px solid #36b9cc !important;">
                    <div class="card-body text-center p-3">
                        <div class="mb-1"><i class="fas fa-droplet fa-2x text-info opacity-50"></i></div>
                        <h3 class="fw-bold mb-0 text-info">{{ number_format($summary->total_blood_loss ?? 0) }}</h3>
                        <div class="small fw-bold text-muted">เลือดที่สูญเสียรวม (cc)</div>
                        <div class="small text-muted mt-1">Total Blood Loss</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Section: Monthly Chart (ด้านบนกราฟแยกเดือนเป็นปี) -->
        <div class="card card-or shadow-sm mb-4" style="border-top: 4px solid #e83e8c !important;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-chart-column me-2 text-danger"></i> สถิติการผ่าตัดรายเดือน ประจำปีงบประมาณ {{ $budget_year }}
                    </h6>
                    <small class="text-muted">จำนวนเคสผ่าตัดรวม 12 เดือน (ต.ค. {{ substr($budget_year - 1, -2) }} - ก.ย. {{ substr($budget_year, -2) }}) แยกตามความเร่งด่วนและประเภทผู้ป่วย</small>
                </div>
                <div class="mt-2 mt-md-0">
                    <span class="badge bg-light text-dark border p-2">
                        <i class="fas fa-info-circle text-primary me-1"></i> รวมปีงบ {{ $budget_year }}: <strong>{{ number_format(array_sum(array_column($monthly_stats, 'total_cases'))) }}</strong> เคส
                    </span>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <div id="monthlyChart" class="chart-container"></div>
            </div>
        </div>

        <!-- Tab Navigation (5 Tabs based on hand-written note) -->
        <div class="nav-tabs-custom mt-3">
            <ul class="nav nav-tabs border-0" id="orTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="patients-tab" data-bs-toggle="tab" data-bs-target="#patients-pane" type="button" role="tab" aria-controls="patients-pane" aria-selected="true">
                        <i class="fas fa-user-injured me-1"></i> 1. รายชื่อผู้ป่วยผ่าตัด ({{ count($patients) }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="procedures-tab" data-bs-toggle="tab" data-bs-target="#procedures-pane" type="button" role="tab" aria-controls="procedures-pane" aria-selected="false">
                        <i class="fas fa-scalpel me-1"></i> 2. แยกตามหัตถการ Operation
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="surgeons-tab" data-bs-toggle="tab" data-bs-target="#surgeons-pane" type="button" role="tab" aria-controls="surgeons-pane" aria-selected="false">
                        <i class="fas fa-user-doctor me-1"></i> 3. แยกตามแพทย์ผู้ทำ (Surgeon)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="departments-tab" data-bs-toggle="tab" data-bs-target="#departments-pane" type="button" role="tab" aria-controls="departments-pane" aria-selected="false">
                        <i class="fas fa-hospital me-1"></i> 4. แยกตามแผนก (ศัลย์, Ortho, สูติ, ตา)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="anesthesia-tab" data-bs-toggle="tab" data-bs-target="#anesthesia-pane" type="button" role="tab" aria-controls="anesthesia-pane" aria-selected="false">
                        <i class="fas fa-syringe me-1"></i> 5. หัตถการวิสัญญี (GA, RA, TIVA, MAC) & ASA Class
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tabs Content -->
        <div class="tab-content mt-3" id="orTabsContent">
            <!-- ============================================== -->
            <!-- TAB 1: Detailed Patient List (ด้านล่างรายชื่อคนไข้) -->
            <!-- ============================================== -->
            <div class="tab-pane fade show active" id="patients-pane" role="tabpanel" aria-labelledby="patients-tab">
                <div class="card card-or shadow-sm border-0 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-list-check me-2 text-primary"></i> บันทึกข้อมูลการผ่าตัดรายบุคคล
                            </h6>
                            <small class="text-muted">ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }} ทั้งหมด {{ count($patients) }} รายการ</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-or w-100" id="patientTable">
                            <thead>
                                <tr class="text-center">
                                    <th style="width: 50px;">ลำดับ</th>
                                    <th style="width: 90px;">วันที่ผ่าตัด</th>
                                    <th style="width: 70px;">เวลา</th>
                                    <th style="width: 80px;">HN</th>
                                    <th>ชื่อ - นามสกุล ผู้ป่วย</th>
                                    <th style="width: 60px;">แผนก</th>
                                    <th style="width: 80px;">AN</th>
                                    <th>ห้องผ่าตัด</th>
                                    <th>หัตถการผ่าตัด (ICD-9)</th>
                                    <th>ศัลยแพทย์ผู้ผ่าตัด</th>
                                    <th style="width: 90px;">ความเร่งด่วน</th>
                                    <th>วิธีระงับความรู้สึก</th>
                                    <th style="width: 80px;">ASA Class</th>
                                    <th style="width: 90px;">สถานะ</th>
                                    <th style="width: 80px;">เสียเลือด(cc)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patients as $index => $row)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="text-center">{{ $row->operation_date }}</td>
                                        <td class="text-center">{{ substr($row->operation_time, 0, 5) }}</td>
                                        <td class="text-center fw-bold">{{ $row->hn }}</td>
                                        <td>{{ $row->patient_name }}</td>
                                        <td class="text-center">
                                            @if ($row->patient_department === 'IPD')
                                                <span class="badge badge-ipd px-2 py-1">IPD</span>
                                            @else
                                                <span class="badge badge-opd px-2 py-1">OPD</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $row->an ?: '-' }}</td>
                                        <td>{{ $row->room_name ?: 'ห้องผ่าตัด' }}</td>
                                        <td>
                                            @php
                                                $proc_name = $row->procedures;
                                                if (!$proc_name) {
                                                    $proc_name = ($row->operation_name && $row->operation_name !== '-1')
                                                        ? $row->operation_name
                                                        : (($row->operation_detail_name && $row->operation_detail_name !== '-1') ? $row->operation_detail_name : '-');
                                                }
                                            @endphp
                                            <span class="fw-bold text-dark">{{ $proc_name }}</span>
                                        </td>
                                        <td class="fw-bold text-primary">{{ $row->surgeon_name }}</td>
                                        <td class="text-center">
                                            @if ($row->emergency_name === 'Emergency')
                                                <span class="badge badge-emergency px-2 py-1">Emergency</span>
                                            @elseif ($row->emergency_name === 'Elective')
                                                <span class="badge badge-elective px-2 py-1">Elective</span>
                                            @else
                                                <span class="badge bg-light text-dark px-2 py-1">{{ $row->emergency_name ?: '-' }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->anes_type ?: '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border">{{ $row->asa_class }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($row->status_name === 'ผ่าตัดเสร็จแล้ว')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">เสร็จสิ้น</span>
                                            @elseif ($row->status_name === 'กำลังผ่าตัด')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">กำลังผ่า</span>
                                            @elseif ($row->status_name === 'ยกเลิกการผ่าตัด')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">ยกเลิก</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ $row->status_name ?: 'รอผ่าตัด' }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold {{ $row->blood_loss > 500 ? 'text-danger' : '' }}">
                                            {{ $row->blood_loss ? number_format($row->blood_loss) : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 2: แยกตามหัตถการ Operation (MRM, Hernioplasty, Excision, ฯลฯ) -->
            <!-- ============================================== -->
            <div class="tab-pane fade" id="procedures-pane" role="tabpanel" aria-labelledby="procedures-tab">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card card-or shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-chart-bar me-2 text-danger"></i> 10 อันดับหัตถการผ่าตัดสูงสุด
                            </h6>
                            <div id="procedureChart" class="chart-container"></div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card card-or shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-table-list me-2 text-primary"></i> รายละเอียดหัตถการและรหัส ICD-9-CM
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-or w-100" id="procedureTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width: 80px;">ICD-9</th>
                                            <th>ชื่อหัตถการ / การผ่าตัด</th>
                                            <th style="width: 80px;">รวม (เคส)</th>
                                            <th style="width: 70px;">IPD</th>
                                            <th style="width: 70px;">OPD</th>
                                            <th style="width: 80px;">Elective</th>
                                            <th style="width: 90px;">Emergency</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($procedures as $p)
                                            <tr>
                                                <td class="text-center fw-bold text-primary">{{ $p->icd9 }}</td>
                                                <td class="fw-bold">{{ $p->proc_name }}</td>
                                                <td class="text-center fw-bold text-dark fs-6">{{ number_format($p->total_count) }}</td>
                                                <td class="text-center"><span class="badge badge-ipd">{{ number_format($p->ipd_count) }}</span></td>
                                                <td class="text-center"><span class="badge badge-opd">{{ number_format($p->opd_count) }}</span></td>
                                                <td class="text-center"><span class="badge badge-elective">{{ number_format($p->elective_count) }}</span></td>
                                                <td class="text-center"><span class="badge badge-emergency">{{ number_format($p->emergency_count) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 3: แยกตามแพทย์ผู้ทำ (Surgeon) -->
            <!-- ============================================== -->
            <div class="tab-pane fade" id="surgeons-pane" role="tabpanel" aria-labelledby="surgeons-tab">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card card-or shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-user-doctor me-2 text-primary"></i> ปริมาณเคสผ่าตัดแยกรายแพทย์
                            </h6>
                            <div id="surgeonChart" class="chart-container"></div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card card-or shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-table me-2 text-success"></i> สถิติการผ่าตัดของแพทย์ผู้ทำ
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-or w-100" id="surgeonTable">
                                    <thead>
                                        <tr class="text-center">
                                            <th style="width: 50px;">ลำดับ</th>
                                            <th>แพทย์ผู้ทำการผ่าตัด</th>
                                            <th>สาขาความเชี่ยวชาญ</th>
                                            <th style="width: 80px;">รวม (เคส)</th>
                                            <th style="width: 80px;">Elective</th>
                                            <th style="width: 90px;">Emergency</th>
                                            <th style="width: 70px;">IPD</th>
                                            <th style="width: 70px;">OPD</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($surgeons as $idx => $s)
                                            <tr>
                                                <td class="text-center">{{ $idx + 1 }}</td>
                                                <td class="fw-bold text-dark">{{ $s->doctor_name }}</td>
                                                <td><span class="badge bg-light text-dark border">{{ $s->spclty_name }}</span></td>
                                                <td class="text-center fw-bold text-primary fs-6">{{ number_format($s->total_cases) }}</td>
                                                <td class="text-center"><span class="badge badge-elective">{{ number_format($s->elective_cases) }}</span></td>
                                                <td class="text-center"><span class="badge badge-emergency">{{ number_format($s->emergency_cases) }}</span></td>
                                                <td class="text-center"><span class="badge badge-ipd">{{ number_format($s->ipd_cases) }}</span></td>
                                                <td class="text-center"><span class="badge badge-opd">{{ number_format($s->opd_cases) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 4: แยกตามแผนก (ศัลย์, Ortho, สูติ, ตา) -->
            <!-- ============================================== -->
            <div class="tab-pane fade" id="departments-pane" role="tabpanel" aria-labelledby="departments-tab">
                <div class="row g-4">
                    <div class="col-lg-5">
                        <div class="card card-or shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-chart-pie me-2 text-warning"></i> สัดส่วนการผ่าตัดตามแผนก
                            </h6>
                            <div id="deptDonutChart" class="chart-container"></div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card card-or shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-sitemap me-2 text-primary"></i> จำนวนเคสผ่าตัดจำแนกตามแผนก
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-or w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th>แผนกการรักษา</th>
                                            <th style="width: 100px;">จำนวนรวม (เคส)</th>
                                            <th style="width: 80px;">IPD</th>
                                            <th style="width: 80px;">OPD</th>
                                            <th style="width: 90px;">Elective</th>
                                            <th style="width: 100px;">Emergency</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($departments as $d)
                                            <tr>
                                                <td class="fw-bold text-dark fs-6">
                                                    @if (str_contains($d->dept_name, 'ศัลย์'))
                                                        <i class="fas fa-scalpel me-2 text-danger"></i>
                                                    @elseif (str_contains($d->dept_name, 'Ortho'))
                                                        <i class="fas fa-bone me-2 text-warning"></i>
                                                    @elseif (str_contains($d->dept_name, 'สูติ'))
                                                        <i class="fas fa-baby me-2 text-pink" style="color: #ec4899;"></i>
                                                    @elseif (str_contains($d->dept_name, 'ตา'))
                                                        <i class="fas fa-eye me-2 text-info"></i>
                                                    @else
                                                        <i class="fas fa-hospital me-2 text-secondary"></i>
                                                    @endif
                                                    {{ $d->dept_name }}
                                                </td>
                                                <td class="text-center fw-bold text-primary fs-6">{{ number_format($d->total_cases) }}</td>
                                                <td class="text-center"><span class="badge badge-ipd">{{ number_format($d->ipd_cases) }}</span></td>
                                                <td class="text-center"><span class="badge badge-opd">{{ number_format($d->opd_cases) }}</span></td>
                                                <td class="text-center"><span class="badge badge-elective">{{ number_format($d->elective_cases) }}</span></td>
                                                <td class="text-center"><span class="badge badge-emergency">{{ number_format($d->emergency_cases) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- TAB 5: หัตถการวิสัญญี (GA, RA, TIVA, MAC) & ASA Class -->
            <!-- ============================================== -->
            <div class="tab-pane fade" id="anesthesia-pane" role="tabpanel" aria-labelledby="anesthesia-tab">
                <div class="row g-4 mb-4">
                    <!-- Anesthesia Techniques -->
                    <div class="col-lg-6">
                        <div class="card card-or shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-syringe me-2 text-danger"></i> หัตถการและเทคนิควิสัญญี (GA, RA, TIVA, MAC)
                            </h6>
                            <div id="anesTechChart" class="chart-container"></div>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered table-hover table-or">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>เทคนิคการระงับความรู้สึก</th>
                                            <th style="width: 100px;">จำนวนเคส</th>
                                            <th style="width: 80px;">สัดส่วน %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalAnes = array_sum(array_column($anes_techniques, 'total_cases')); @endphp
                                        @foreach ($anes_techniques as $at)
                                            <tr>
                                                <td class="fw-bold">{{ $at->anes_category }}</td>
                                                <td class="text-center fw-bold text-primary">{{ number_format($at->total_cases) }}</td>
                                                <td class="text-center text-muted">
                                                    {{ $totalAnes > 0 ? round(($at->total_cases / $totalAnes) * 100, 1) : 0 }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ASA Physical Status -->
                    <div class="col-lg-6">
                        <div class="card card-or shadow-sm p-4 h-100">
                            <h6 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-heart-pulse me-2 text-warning"></i> ระดับการประเมินความเสี่ยง ASA Physical Status (Class I - V)
                            </h6>
                            <div id="asaClassChart" class="chart-container"></div>
                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered table-hover table-or">
                                    <thead class="table-light">
                                        <tr class="text-center">
                                            <th>ระดับ ASA Class</th>
                                            <th style="width: 100px;">จำนวนเคส</th>
                                            <th style="width: 80px;">สัดส่วน %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalAsa = array_sum(array_column($asa_classes, 'total_cases')); @endphp
                                        @foreach ($asa_classes as $asa)
                                            <tr>
                                                <td class="fw-bold">{{ $asa->asa_class_name }}</td>
                                                <td class="text-center fw-bold text-success">{{ number_format($asa->total_cases) }}</td>
                                                <td class="text-center text-muted">
                                                    {{ $totalAsa > 0 ? round(($asa->total_cases / $totalAsa) * 100, 1) : 0 }}%
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Anesthesia Billed Items Table -->
                @if (count($anes_procedures) > 0)
                <div class="card card-or shadow-sm p-4">
                    <h6 class="fw-bold mb-3 text-dark">
                        <i class="fas fa-file-invoice-dollar me-2 text-info"></i> รายการบริการวิสัญญีที่มีการบันทึกเรียกเก็บ
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-or w-100">
                            <thead>
                                <tr class="text-center">
                                    <th style="width: 50px;">ลำดับ</th>
                                    <th>รายการบริการวิสัญญี</th>
                                    <th style="width: 100px;">จำนวนเคส</th>
                                    <th style="width: 100px;">จำนวนครั้ง</th>
                                    <th style="width: 120px;">มูลค่ารวม (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($anes_procedures as $i => $ap)
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td class="fw-bold text-dark">{{ $ap->proc_name }}</td>
                                        <td class="text-center fw-bold text-primary">{{ number_format($ap->total_cases) }}</td>
                                        <td class="text-center">{{ number_format($ap->total_qty) }}</td>
                                        <td class="text-end fw-bold text-success">{{ number_format($ap->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
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
            $(document).ready(function () {
                const yearOffset = 543;

                function formatThaiDate(date, instance) {
                    if (!date || isNaN(date.getTime())) return "";
                    const day = date.getDate();
                    const month = (instance && instance.l10n && instance.l10n.months) 
                        ? instance.l10n.months.shorthand[date.getMonth()] 
                        : ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."][date.getMonth()];
                    const year = date.getFullYear() + yearOffset;
                    return `${day} ${month} ${year}`;
                }

                const commonConfig = {
                    locale: "th",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "j M Y",
                    allowInput: false,
                    onReady: function(selectedDates, dateStr, instance) {
                        if (instance.altInput) {
                            const date = instance.selectedDates[0] || (instance.input.value ? new Date(instance.input.value) : null);
                            if (date && !isNaN(date.getTime())) {
                                instance.altInput.value = formatThaiDate(date, instance);
                            }
                        }

                        // Add "วันนี้" (Today) button
                        const container = instance.calendarContainer;
                        if (container && !container.querySelector('.flatpickr-today-button')) {
                            const btn = document.createElement("div");
                            btn.className = "flatpickr-today-button";
                            btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                            btn.addEventListener("mousedown", function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                instance.setDate(new Date(), true);
                                instance.close();
                            });
                            container.appendChild(btn);
                        }
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        if (instance.altInput && selectedDates.length > 0) {
                            setTimeout(() => {
                                instance.altInput.value = formatThaiDate(selectedDates[0], instance);
                            }, 10);
                        }
                    }
                };

                const startPicker = flatpickr("#start_date", commonConfig);
                const endPicker = flatpickr("#end_date", commonConfig);

                // Auto update start and end dates when changing budget year select
                $('#budget_year_select').on('change', function() {
                    const selectedYear = parseInt($(this).val());
                    if (!isNaN(selectedYear)) {
                        const startYear = selectedYear - 544; 
                        const endYear = selectedYear - 543;   
                        const startDateStr = `${startYear}-10-01`;
                        const endDateStr = `${endYear}-09-30`;

                        if (startPicker) {
                            startPicker.setDate(startDateStr, true);
                            setTimeout(() => {
                                if (startPicker.altInput) {
                                    startPicker.altInput.value = `1 ต.ค. ${selectedYear - 1}`;
                                }
                            }, 15);
                        }
                        if (endPicker) {
                            endPicker.setDate(endDateStr, true);
                            setTimeout(() => {
                                if (endPicker.altInput) {
                                    endPicker.altInput.value = `30 ก.ย. ${selectedYear}`;
                                }
                            }, 15);
                        }
                    }
                });

                // Initialize DataTables for Patient List
                $('#patientTable').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2"l<"d-flex align-items-center gap-2"fB>>rtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> ส่งออก Excel',
                        className: 'btn btn-success',
                        title: 'รายงานรายชื่อผู้ป่วยผ่าตัด ({{ DateThai($start_date) }} - {{ DateThai($end_date) }})'
                    }],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    language: {
                        search: "ค้นหา:",
                        lengthMenu: "แสดง _MENU_ แถวต่อหน้า",
                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                        paginate: {
                            first: "หน้าแรก",
                            last: "หน้าสุดท้าย",
                            next: "ถัดไป",
                            previous: "ก่อนหน้า"
                        },
                        emptyTable: "ไม่พบข้อมูลการผ่าตัดในช่วงวันที่ระบุ"
                    },
                    order: [[1, 'desc'], [2, 'desc']]
                });

                $('#procedureTable').DataTable({
                    dom: '<"d-flex justify-content-end mb-2"f>rtp',
                    pageLength: 10,
                    language: { search: "ค้นหาหัตถการ:" },
                    order: [[2, 'desc']]
                });

                $('#surgeonTable').DataTable({
                    dom: '<"d-flex justify-content-end mb-2"f>rtp',
                    pageLength: 10,
                    language: { search: "ค้นหาแพทย์:" },
                    order: [[3, 'desc']]
                });

                // ==========================================
                // ApexCharts Initialization
                // ==========================================
                const monthLabels = @json(array_column($monthly_stats, 'month_label'));
                const monthTotal = @json(array_map('intval', array_column($monthly_stats, 'total_cases')));
                const monthElective = @json(array_map('intval', array_column($monthly_stats, 'elective_cases')));
                const monthEmergency = @json(array_map('intval', array_column($monthly_stats, 'emergency_cases')));
                const monthIPD = @json(array_map('intval', array_column($monthly_stats, 'ipd_cases')));
                const monthOPD = @json(array_map('intval', array_column($monthly_stats, 'opd_cases')));

                // 1. Monthly Chart (Top Section)
                var monthlyOptions = {
                    series: [
                        { name: 'เคสทั้งหมด', type: 'column', data: monthTotal },
                        { name: 'Elective (นัดหมาย)', type: 'column', data: monthElective },
                        { name: 'Emergency (ฉุกเฉิน)', type: 'column', data: monthEmergency },
                        { name: 'ผู้ป่วยใน IPD', type: 'line', data: monthIPD },
                        { name: 'ผู้ป่วยนอก OPD', type: 'line', data: monthOPD }
                    ],
                    chart: {
                        height: 350,
                        type: 'line',
                        stacked: false,
                        toolbar: { show: true, tools: { download: true } },
                        fontFamily: 'inherit'
                    },
                    stroke: { width: [0, 0, 0, 3, 3], curve: 'smooth' },
                    plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                    colors: ['#4e73df', '#7c3aed', '#e74a3b', '#1cc88a', '#36b9cc'],
                    xaxis: { categories: monthLabels },
                    yaxis: {
                        title: { text: 'จำนวนเคส (ราย)' },
                        min: 0,
                        labels: { formatter: function(val) { return Math.round(val); } }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: { formatter: function(val) { return val + " เคส"; } }
                    },
                    legend: { position: 'top', horizontalAlign: 'center' },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions).render();

                // 2. Top Procedures Chart
                const procData = @json(array_slice($procedures, 0, 10));
                var procOptions = {
                    series: [{ name: 'จำนวนเคส', data: procData.map(p => parseInt(p.total_count)) }],
                    chart: { type: 'bar', height: 350, toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: true, borderRadius: 5, barHeight: '65%' } },
                    colors: ['#e83e8c'],
                    xaxis: { categories: procData.map(p => p.proc_name.substring(0, 35)) },
                    dataLabels: { enabled: true, style: { fontSize: '11px' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#procedureChart"), procOptions).render();

                // 3. Top Surgeons Chart
                const surgeonData = @json(array_slice($surgeons, 0, 8));
                var surgeonOptions = {
                    series: [
                        { name: 'Elective', data: surgeonData.map(s => parseInt(s.elective_cases)) },
                        { name: 'Emergency', data: surgeonData.map(s => parseInt(s.emergency_cases)) }
                    ],
                    chart: { type: 'bar', height: 350, stacked: true, toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
                    colors: ['#7c3aed', '#e74a3b'],
                    xaxis: { categories: surgeonData.map(s => s.doctor_name) },
                    legend: { position: 'top' },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#surgeonChart"), surgeonOptions).render();

                // 4. Department Donut Chart
                const deptData = @json($departments);
                var deptOptions = {
                    series: deptData.map(d => parseInt(d.total_cases)),
                    labels: deptData.map(d => d.dept_name),
                    chart: { type: 'donut', height: 350 },
                    colors: ['#e83e8c', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#64748b'],
                    legend: { position: 'bottom' },
                    responsive: [{ breakpoint: 480, options: { chart: { width: 300 } } }]
                };
                new ApexCharts(document.querySelector("#deptDonutChart"), deptOptions).render();

                // 5. Anesthesia Technique Chart
                const anesData = @json($anes_techniques);
                var anesOptions = {
                    series: anesData.map(a => parseInt(a.total_cases)),
                    labels: anesData.map(a => a.anes_category),
                    chart: { type: 'donut', height: 320 },
                    colors: ['#ef4444', '#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#94a3b8'],
                    legend: { position: 'bottom' }
                };
                new ApexCharts(document.querySelector("#anesTechChart"), anesOptions).render();

                // 6. ASA Class Chart
                const asaData = @json($asa_classes);
                var asaOptions = {
                    series: [{ name: 'จำนวนเคส', data: asaData.map(a => parseInt(a.total_cases)) }],
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    plotOptions: { bar: { columnWidth: '50%', borderRadius: 4, dataLabels: { position: 'top' } } },
                    colors: ['#f59e0b'],
                    xaxis: { categories: asaData.map(a => a.asa_class_name.substring(0, 15)) },
                    dataLabels: { enabled: true, offsetY: -20, style: { fontSize: '11px', colors: ['#333'] } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#asaClassChart"), asaOptions).render();

                // Fix chart resizing when tabs are switched
                document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tabEl) {
                    tabEl.addEventListener('shown.bs.tab', function () {
                        window.dispatchEvent(new Event('resize'));
                    });
                });
            });
        </script>
    @endpush
@endsection
