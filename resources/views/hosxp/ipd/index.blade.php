@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
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

        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        .card-ipd { 
            border-radius: 16px; 
            border: 1px solid #e3eef5 !important; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            background: #fff;
            overflow: hidden;
        }
        .chart-container { min-height: 350px; }
        
        .table-ipd { font-size: 0.85rem; }
        .table-ipd thead th { background-color: #f8f9fa; color: #334155; font-weight: 700; border-bottom: 2px solid #e2e8f0; }

        .stat-card {
            transition: transform 0.3s ease;
            border-top: 4px solid #4e73df;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-budget { width: 100% !important; }
        }

        /* Custom DataTables Styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            font-size: 0.8rem !important;
        }
        .dt-buttons .btn-success {
            background-color: #1d6f42 !important;
            border-color: #1d6f42 !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            font-size: 0.8rem !important;
            padding: 0.4rem 1rem !important;
            margin-right: 5px;
            font-weight: 600 !important;
            box-shadow: 0 4px 6px rgba(29, 111, 66, 0.1) !important;
        }
        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            border-bottom: 2px solid #e3e6f0 !important;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-bed text-success me-2"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ปีงบประมาณ {{ $budget_year }} | ระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date">
                        <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget">
                        <select class="form-select border-end-0" name="budget_year">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-success px-3">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Currently Admitted Cards -->
        <div class="row mb-4 g-3">
            <div class="col-md-3">
                <div class="card card-ipd stat-card shadow-sm h-100" style="border-top-color: #4e73df;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-procedures fa-2x text-primary opacity-50"></i></div>
                        <h2 class="fw-bold mb-0 text-primary">{{ number_format($current_admit->ipd) }}</h2>
                        <div class="small fw-bold text-primary mb-1">กำลัง Admit</div>
                        <div class="text-muted small fw-bold text-uppercase">หอผู้ป่วยสามัญ (IPD)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-ipd stat-card shadow-sm h-100" style="border-top-color: #f6c23e;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-star fa-2x text-warning opacity-50"></i></div>
                        <h2 class="fw-bold mb-0 text-warning">{{ number_format($current_admit->vip) }}</h2>
                        <div class="small fw-bold text-warning mb-1">กำลัง Admit</div>
                        <div class="text-muted small fw-bold text-uppercase">หอผู้ป่วยพิเศษ (VIP)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-ipd stat-card shadow-sm h-100" style="border-top-color: #e74a3b;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-baby fa-2x text-danger opacity-50"></i></div>
                        <h2 class="fw-bold mb-0 text-danger">{{ number_format($current_admit->lr) }}</h2>
                        <div class="small fw-bold text-danger mb-1">กำลัง Admit</div>
                        <div class="text-muted small fw-bold text-uppercase">ห้องคลอด (LR)</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-ipd stat-card shadow-sm h-100" style="border-top-color: #1cc88a;">
                    <div class="card-body text-center p-4">
                        <div class="mb-2"><i class="fas fa-home fa-2x text-success opacity-50"></i></div>
                        <h2 class="fw-bold mb-0 text-success">{{ number_format($current_admit->homeward) }}</h2>
                        <div class="small fw-bold text-success mb-1">กำลัง Admit</div>
                        <div class="text-muted small fw-bold text-uppercase">Home Ward</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ward Tabs -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-ipd shadow-sm overflow-hidden" style="border-top: 4px solid #4e73df !important; border-radius: 12px;">
                    <div class="card-body p-0">
                        <div class="nav nav-tabs nav-fill border-0" id="wardTabs" role="tablist" style="background-color: #f8f9fc;">
                            <a href="{{ url()->current() }}?tab=total&start_date={{ $start_date }}&end_date={{ $end_date }}&budget_year={{ $budget_year }}" 
                               class="nav-link py-3 border-0 rounded-0 {{ $tab == 'total' ? 'active fw-bold text-danger' : 'text-muted' }}"
                               style="{{ $tab == 'total' ? 'background-color: #fff5f5 !important; border-bottom: 4px solid #e74a3b !important;' : 'background-color: #fffcfc;' }}">
                                <i class="fas fa-hospital me-2"></i> ผู้ป่วยในรวม
                            </a>
                            <a href="{{ url()->current() }}?tab=general&start_date={{ $start_date }}&end_date={{ $end_date }}&budget_year={{ $budget_year }}" 
                               class="nav-link py-3 border-0 rounded-0 {{ $tab == 'general' ? 'active fw-bold text-primary' : 'text-muted' }}"
                               style="{{ $tab == 'general' ? 'background-color: #f0f7ff !important; border-bottom: 4px solid #4e73df !important;' : 'background-color: #fafdff;' }}">
                                <i class="fas fa-procedures me-2"></i> ผู้ป่วยในสามัญ
                            </a>
                            <a href="{{ url()->current() }}?tab=vip&start_date={{ $start_date }}&end_date={{ $end_date }}&budget_year={{ $budget_year }}" 
                               class="nav-link py-3 border-0 rounded-0 {{ $tab == 'vip' ? 'active fw-bold text-success' : 'text-muted' }}"
                               style="{{ $tab == 'vip' ? 'background-color: #f0fff4 !important; border-bottom: 4px solid #1cc88a !important;' : 'background-color: #fafffb;' }}">
                                <i class="fas fa-star me-2 text-warning"></i> ผู้ป่วยใน VIP
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1: Admissions & Occupancy -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #4e73df !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-injured me-2 text-primary"></i> จำนวน ({{ $tab_name }}) </h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="admissionChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #1cc88a !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-bed me-2 text-success"></i> อัตราครองเตียง % ({{ $tab_name }})</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="occupancyChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2: AdjRW, CMI & Shifts -->
        <div class="row mb-4 g-4">
            <div class="col-md-4">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #f6c23e !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-invoice-dollar me-2 text-warning"></i> Sum AdjRW ({{ $tab_name }})</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="adjrwChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #36b9cc !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-stethoscope me-2 text-info"></i> CMI ({{ $tab_name }})</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="cmiChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #e74a3b !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-clock me-2 text-danger"></i> การรับใหม่ตามเวร ({{ $tab_name }})</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="shiftChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row 3: Discharge Type & PDX -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #1cc88a !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie me-2 text-success"></i> แยกตามประเภทการจำหน่าย (Discharge Type)</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="dchTypeChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #f6c23e !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ol me-2 text-warning"></i> 10 อันดับรายโรค (PDX)</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="pdxChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 4: Refer Trends -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #0dcaf0 !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sign-in-alt me-2 text-info"></i> แนวโน้มการรับเข้า Refer In ({{ $tab_name }})</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="referInChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #e74a3b !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sign-out-alt me-2 text-danger"></i> แนวโน้มการส่งต่อ Refer Out ({{ $tab_name }})</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="referOutChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Charts Row 5: Drug & Lab Cost Trends -->
        <div class="row mb-4 g-4">
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #198754 !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-pills me-2 text-success"></i> แนวโน้มค่ายา ({{ $tab_name }})</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="drugCostChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-ipd shadow-sm h-100" style="border-top: 4px solid #ffc107 !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-flask me-2 text-warning"></i> แนวโน้มค่า LAB ({{ $tab_name }})</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="labCostChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1.5: Average Length of Stay (ALOS) -->
        <div class="row mb-4 g-4">
            <div class="col-12">
                <div class="card card-ipd shadow-sm" style="border-top: 4px solid #e74a3b !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-history me-2 text-danger"></i> วันนอนเฉลี่ยรายเดือน (Average Length of Stay - ALOS)</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="alosChart" class="chart-container" style="min-height: 350px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Table -->
        <div class="row pb-5">
            <div class="col-12">
                <div class="card card-ipd shadow-sm" style="border-top: 4px solid #1cc88a !important;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-table me-2 text-success"></i> สรุปสถิติ{{ $tab_name }} รายเดือน</h6>
                            <p class="text-muted small mb-0 mt-1">ข้อมูลสรุปตามเดือนที่จำหน่าย (Discharge Date)</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover table-ipd mb-0" id="ipdTable">
                                <thead>
                                    <tr>
                                        <th class="ps-4">เดือน/ปี</th>
                                        <th class="text-center">Admit (AN)</th>
                                        <th class="text-center">
                                            @if($tab == 'vip')
                                                วันนอน VIP
                                            @elseif($tab == 'general')
                                                วันนอนสามัญ
                                            @else
                                                วันนอนรวม
                                            @endif
                                        </th>
                                        <th class="text-center text-danger">
                                            @if($tab == 'vip')
                                                วันนอน VIP เฉลี่ย (วัน)
                                            @elseif($tab == 'general')
                                                วันนอนสามัญเฉลี่ย (วัน)
                                            @else
                                                วันนอนเฉลี่ย (วัน)
                                            @endif
                                        </th>
                                        @if($tab != 'total')
                                            <th class="text-center text-warning">วันนอน รพ. เฉลี่ย (วัน)</th>
                                        @endif
                                        <th class="text-center">อัตราครองเตียง (%)</th>
                                        <th class="text-center">Active Bed</th>
                                        <th class="text-center">Sum AdjRW</th>
                                        <th class="text-center">CMI</th>
                                        <th class="text-center">รายได้/RW</th>
                                        <th class="text-center text-success">ค่ายา</th>
                                        <th class="text-center text-warning">ค่า LAB</th>
                                        <th class="text-center">รับใหม่เวรเช้า</th>
                                        <th class="text-center">รับใหม่เวรบ่าย</th>
                                        <th class="text-center">รับใหม่เวรดึก</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthly_stats as $row)
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">{{ $row->month_year }}</td>
                                        <td class="text-center fw-bold text-primary">{{ number_format($row->total_admission) }}</td>
                                        <td class="text-center">{{ number_format($row->total_bed_days) }}</td>
                                        <td class="text-center fw-bold text-danger">{{ number_format($row->avg_los_days, 2) }}</td>
                                        @if($tab != 'total')
                                            <td class="text-center fw-bold text-warning">{{ number_format($row->avg_hospital_los_days, 2) }}</td>
                                        @endif
                                        <td class="text-center">
                                            <div class="progress" style="height: 10px; border-radius: 5px;">
                                                <div class="progress-bar {{ $row->bed_occupancy_rate > 80 ? 'bg-danger' : ($row->bed_occupancy_rate > 60 ? 'bg-warning' : 'bg-success') }}" 
                                                    role="progressbar" style="width: {{ $row->bed_occupancy_rate }}%"></div>
                                            </div>
                                            <small class="fw-bold">{{ $row->bed_occupancy_rate }}%</small>
                                        </td>
                                        <td class="text-center">{{ $row->active_bed }}</td>
                                        <td class="text-center">{{ number_format($row->total_adjrw, 2) }}</td>
                                        <td class="text-center fw-bold text-info">{{ number_format($row->cmi, 2) }}</td>
                                        <td class="text-center">{{ number_format($row->net_income_per_rw, 2) }}</td>
                                        <td class="text-center text-success fw-bold">{{ number_format($row->total_inc12, 2) }}</td>
                                        <td class="text-center text-warning fw-bold">{{ number_format($row->total_inc03, 2) }}</td>
                                        <td class="text-center">{{ number_format($row->admit_morning_shift) }}</td>
                                        <td class="text-center">{{ number_format($row->admit_evening_shift) }}</td>
                                        <td class="text-center">{{ number_format($row->admit_night_shift) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light fw-bold" style="border-top: 2px solid #dee2e6;">
                                        <td class="ps-4">{{ $summary_stats->month_year }}</td>
                                        <td class="text-center text-primary">{{ number_format($summary_stats->total_admission) }}</td>
                                        <td class="text-center">{{ number_format($summary_stats->total_bed_days) }}</td>
                                        <td class="text-center text-danger">{{ number_format($summary_stats->avg_los_days, 2) }}</td>
                                        @if($tab != 'total')
                                            <td class="text-center text-warning">{{ number_format($summary_stats->avg_hospital_los_days, 2) }}</td>
                                        @endif
                                        <td class="text-center">
                                            <div class="progress" style="height: 10px; border-radius: 5px; background-color: #e9ecef;">
                                                <div class="progress-bar {{ $summary_stats->bed_occupancy_rate > 80 ? 'bg-danger' : ($summary_stats->bed_occupancy_rate > 60 ? 'bg-warning' : 'bg-success') }}" 
                                                     role="progressbar" style="width: {{ $summary_stats->bed_occupancy_rate }}%"></div>
                                            </div>
                                            <small>{{ $summary_stats->bed_occupancy_rate }}%</small>
                                        </td>
                                        <td class="text-center">{{ $summary_stats->active_bed }}</td>
                                        <td class="text-center">{{ number_format($summary_stats->total_adjrw, 2) }}</td>
                                        <td class="text-center text-info">{{ number_format($summary_stats->cmi, 2) }}</td>
                                        <td class="text-center">{{ number_format($summary_stats->net_income_per_rw, 2) }}</td>
                                        <td class="text-center text-success fw-bold">{{ number_format($summary_stats->total_inc12, 2) }}</td>
                                        <td class="text-center text-warning fw-bold">{{ number_format($summary_stats->total_inc03, 2) }}</td>
                                        <td class="text-center">{{ number_format($summary_stats->admit_morning_shift) }}</td>
                                        <td class="text-center">{{ number_format($summary_stats->admit_evening_shift) }}</td>
                                        <td class="text-center">{{ number_format($summary_stats->admit_night_shift) }}</td>
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
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
        
        <script>
            $(document).ready(function() {
                // Initialize DataTable
                $('#ipdTable').DataTable({
                    dom: '<"d-flex justify-content-end mb-3"B>rt',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success',
                        title: 'รายงานสถิติ IPD ({{ DateThai($start_date) }} - {{ DateThai($end_date) }})'
                    }],
                    paging: false,
                    info: false,
                    searching: false,
                    ordering: true,
                    order: [],
                    responsive: true
                });

                const yearOffset = 543;
                const commonConfig = {
                    locale: "th", dateFormat: "Y-m-d", altInput: true, altFormat: "j M Y", allowInput: false,
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
                            if (typeof startPicker !== 'undefined' && startPicker) startPicker.setDate(startDateStr, true);
                            if (typeof endPicker !== 'undefined' && endPicker) endPicker.setDate(endDateStr, true);
                        }, 50);
                    }
                });

                // Chart Configurations
                const labels = @json(array_column($monthly_stats, 'month_year'));

                // 1.5 ALOS Chart (Average Length of Stay)
                var alosOptions = {
                    series: [
                        { 
                            name: '@if($tab == "vip")วันนอน VIP เฉลี่ย (วัน)@elseif($tab == "general")วันนอนสามัญเฉลี่ย (วัน)@elseวันนอนเฉลี่ย (วัน)@endif', 
                            data: @json(array_column($monthly_stats, 'avg_los_days')) 
                        }
                        @if($tab != 'total')
                        ,
                        { 
                            name: 'วันนอน รพ. เฉลี่ย (วัน)', 
                            data: @json(array_column($monthly_stats, 'avg_hospital_los_days')) 
                        }
                        @endif
                    ],
                    chart: { height: 350, type: 'line', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: @if($tab != 'total')[3, 3]@else 3 @endif },
                    markers: { size: 4 },
                    colors: @if($tab != 'total')['#e74a3b', '#f6c23e']@else['#e74a3b']@endif,
                    dataLabels: { 
                        enabled: true, 
                        offsetY: -10,
                        style: { fontSize: '11px', colors: @if($tab != 'total')['#e74a3b', '#f6c23e']@else['#e74a3b']@endif },
                        background: { enabled: true, padding: 3, borderRadius: 2, borderWidth: 0 }
                    },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: 'วันนอนเฉลี่ย (วัน)' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val + " วัน";
                            }
                        }
                    }
                };
                new ApexCharts(document.querySelector("#alosChart"), alosOptions).render();

                // 1. Admission Chart
                var admissionOptions = {
                    series: [{ name: 'Admit (AN)', data: @json(array_column($monthly_stats, 'total_admission')) }],
                    chart: { height: 300, type: 'bar', toolbar: { show: false } },
                    colors: ['#4e73df'],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%', dataLabels: { position: 'top' } } },
                    dataLabels: { 
                        enabled: true, 
                        offsetY: -20, 
                        style: { fontSize: '12px', colors: ['#4e73df'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false }
                    },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: '' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#admissionChart"), admissionOptions).render();

                // 2. Occupancy Chart
                var occupancyOptions = {
                    series: [{ name: 'อัตราครองเตียง (%)', data: @json(array_column($monthly_stats, 'bed_occupancy_rate')) }],
                    chart: { height: 300, type: 'area', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    colors: ['#1cc88a'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 } },
                    dataLabels: { 
                        enabled: true, 
                        offsetY: -10,
                        style: { fontSize: '12px', colors: ['#1cc88a'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false }
                    },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: '' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#occupancyChart"), occupancyOptions).render();

                // 3. AdjRW Chart
                var adjrwOptions = {
                    series: [{ name: 'Sum AdjRW', data: @json(array_column($monthly_stats, 'total_adjrw')) }],
                    chart: { height: 300, type: 'bar', toolbar: { show: false } },
                    colors: ['#f6c23e'],
                    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%', dataLabels: { position: 'top' } } },
                    dataLabels: { 
                        enabled: true, 
                        offsetY: -20, 
                        style: { fontSize: '12px', colors: ['#f6c23e'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false }
                    },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: '' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#adjrwChart"), adjrwOptions).render();

                // 4. CMI Chart
                var cmiOptions = {
                    series: [{ name: 'CMI', data: @json(array_column($monthly_stats, 'cmi')) }],
                    chart: { height: 300, type: 'line', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 4 },
                    markers: { size: 4 },
                    colors: ['#36b9cc'],
                    dataLabels: { 
                        enabled: true, 
                        offsetY: -15,
                        style: { fontSize: '12px', colors: ['#36b9cc'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false }
                    },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: '' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 }
                };
                new ApexCharts(document.querySelector("#cmiChart"), cmiOptions).render();

                var shiftOptions = {
                    series: [
                        @json(array_sum(array_column($monthly_stats, 'admit_morning_shift'))),
                        @json(array_sum(array_column($monthly_stats, 'admit_evening_shift'))),
                        @json(array_sum(array_column($monthly_stats, 'admit_night_shift')))
                    ],
                    chart: { type: 'donut', height: 350 },
                    labels: ['เวรเช้า (08:00-16:00)', 'เวรบ่าย (16:00-24:00)', 'เวรดึก (00:00-08:00)'],
                    colors: ['#4e73df', '#f6c23e', '#e74a3b'],
                    legend: { position: 'bottom', fontSize: '12px' },
                    stroke: { width: 0 },
                    dataLabels: { enabled: true, formatter: function (val) { return val.toFixed(1) + "%" } }
                };
                new ApexCharts(document.querySelector("#shiftChart"), shiftOptions).render();

                // 6. Discharge Type Chart
                var dchTypeOptions = {
                    series: @json(array_column($dch_types, 'count')),
                    chart: { type: 'donut', height: 350 },
                    labels: @json(array_column($dch_types, 'dch_type_name')),
                    colors: ['#1cc88a', '#4e73df', '#f6c23e', '#e74a3b', '#858796', '#5a5c69', '#36b9cc'],
                    legend: { position: 'bottom', fontSize: '11px' },
                    stroke: { width: 0 },
                    dataLabels: { enabled: true, dropShadow: { enabled: false } },
                    responsive: [{ breakpoint: 480, options: { chart: { width: 200 } } }]
                };
                new ApexCharts(document.querySelector("#dchTypeChart"), dchTypeOptions).render();

                // 7. Top 10 PDX Chart
                var pdxOptions = {
                    series: [{ name: 'จำนวนคน', data: @json(array_column($top_pdx, 'count')) }],
                    chart: { type: 'bar', height: 350, toolbar: { show: false } },
                    plotOptions: { bar: { horizontal: true, borderRadius: 6, barHeight: '60%' } },
                    colors: ['#f6c23e'],
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    xaxis: { categories: @json(array_column($top_pdx, 'diag_name')), axisBorder: { show: false } },
                    dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#444'] }, offsetX: 5, dropShadow: { enabled: false } }
                };
                new ApexCharts(document.querySelector("#pdxChart"), pdxOptions).render();

                // 8. Refer In Monthly Trend Chart
                var referInOptions = {
                    series: [{ name: 'Refer In (รับเข้า)', data: @json(array_column($monthly_stats, 'total_refer_in')) }],
                    chart: { height: 300, type: 'area', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    colors: ['#0dcaf0'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 } },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: '' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    dataLabels: {
                        enabled: true,
                        offsetY: -10,
                        style: { fontSize: '11px', colors: ['#0dcaf0'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false }
                    }
                };
                new ApexCharts(document.querySelector("#referInChart"), referInOptions).render();

                // 9. Refer Out Monthly Trend Chart
                var referOutOptions = {
                    series: [{ name: 'Refer Out (ส่งต่อ)', data: @json(array_column($monthly_stats, 'total_refer_out')) }],
                    chart: { height: 300, type: 'area', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    colors: ['#e74a3b'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 } },
                    xaxis: { categories: labels },
                    yaxis: { min: 0, title: { text: '' } },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    dataLabels: {
                        enabled: true,
                        offsetY: -10,
                        style: { fontSize: '11px', colors: ['#e74a3b'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false }
                    }
                };
                new ApexCharts(document.querySelector("#referOutChart"), referOutOptions).render();

                // 10. Drug Cost Chart
                var drugCostOptions = {
                    series: [{ name: 'ค่ายา (inc12)', data: @json(array_column($monthly_stats, 'total_inc12')) }],
                    chart: { height: 300, type: 'area', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    colors: ['#198754'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 } },
                    xaxis: { categories: labels },
                    yaxis: { 
                        min: 0, 
                        labels: {
                            formatter: function (value) {
                                return new Intl.NumberFormat('th-TH').format(value);
                            }
                        }
                    },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    dataLabels: {
                        enabled: true,
                        offsetY: -10,
                        style: { fontSize: '11px', colors: ['#198754'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false },
                        formatter: function (val) {
                            return new Intl.NumberFormat('th-TH').format(val);
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return new Intl.NumberFormat('th-TH').format(val) + " บาท";
                            }
                        }
                    }
                };
                new ApexCharts(document.querySelector("#drugCostChart"), drugCostOptions).render();

                // 11. Lab Cost Chart
                var labCostOptions = {
                    series: [{ name: 'ค่า LAB (inc03)', data: @json(array_column($monthly_stats, 'total_inc03')) }],
                    chart: { height: 300, type: 'area', toolbar: { show: false } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4 },
                    colors: ['#ffc107'],
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 } },
                    xaxis: { categories: labels },
                    yaxis: { 
                        min: 0, 
                        labels: {
                            formatter: function (value) {
                                return new Intl.NumberFormat('th-TH').format(value);
                            }
                        }
                    },
                    grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
                    dataLabels: {
                        enabled: true,
                        offsetY: -10,
                        style: { fontSize: '11px', colors: ['#ffc107'] },
                        background: { enabled: false },
                        dropShadow: { enabled: false },
                        formatter: function (val) {
                            return new Intl.NumberFormat('th-TH').format(val);
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return new Intl.NumberFormat('th-TH').format(val) + " บาท";
                            }
                        }
                    }
                };
                new ApexCharts(document.querySelector("#labCostChart"), labCostOptions).render();
            });
        </script>
    @endpush
@endsection
