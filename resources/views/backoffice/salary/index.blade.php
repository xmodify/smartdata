@extends('layouts.app')

@section('title', 'SmartData | รายงานเงินเดือนและค่าตอบแทน')

@section('topbar_actions')
    <a href="{{ route('backoffice.hrd.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm me-2"
        style="border-radius: 10px; padding: 5px 15px; color: #1cc88a; transition: all 0.3s;">
        <i class="fas fa-id-card me-1"></i> งานบุคลากร
    </a>
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> หน้าหลัก
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.25rem;
            border: 1px solid #edf2f7;
        }

        /* Modern Main Tabs */
        .nav-tabs-custom {
            border-bottom: 2px solid #edf2f7;
            gap: 0.5rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #64748b;
            font-weight: 700;
            padding: 0.85rem 1.6rem;
            border-radius: 12px 12px 0 0;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.25s ease;
            position: relative;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #1e293b;
            background-color: #f8fafc;
        }

        .nav-tabs-custom .nav-link.active-overview {
            color: #4f46e5;
            background: #ffffff;
            border-bottom: 3px solid #6366f1;
        }

        .nav-tabs-custom .nav-link.active-salary {
            color: #059669;
            background: #ffffff;
            border-bottom: 3px solid #10b981;
        }

        .nav-tabs-custom .nav-link.active-compen {
            color: #0284c7;
            background: #ffffff;
            border-bottom: 3px solid #0ea5e9;
        }

        /* Sub-Tabs: Month Navigation */
        .month-subtabs-container {
            background: #ffffff;
            border-radius: 14px;
            padding: 0.65rem 0.85rem;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            margin-bottom: 1.5rem;
        }

        .month-subtab-btn {
            border-radius: 50rem;
            padding: 0.4rem 1rem;
            font-size: 0.83rem;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .month-subtab-btn:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        .month-subtab-btn.active-tab-overview {
            background-color: #6366f1 !important;
            color: #ffffff !important;
            border-color: #6366f1 !important;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.35);
        }

        .month-subtab-btn.active-tab-salary {
            background-color: #10b981 !important;
            color: #ffffff !important;
            border-color: #10b981 !important;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
        }

        .month-subtab-btn.active-tab-compen {
            background-color: #0ea5e9 !important;
            color: #ffffff !important;
            border-color: #0ea5e9 !important;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.35);
        }

        .month-nav-scroll {
            overflow-x: auto;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }

        /* KPI Cards */
        .kpi-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .kpi-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Custom Multi-select for Departments */
        .dropdown-menu-multiselect {
            min-width: 340px;
            max-height: 440px;
            overflow-y: auto;
            padding: 0;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            border: 1px solid #e2e8f0;
        }

        .multiselect-header {
            position: sticky;
            top: 0;
            background: white;
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            z-index: 10;
        }

        .multiselect-search {
            border-radius: 8px;
            font-size: 0.85rem;
            padding: 7px 12px;
            border: 1px solid #cbd5e1;
        }

        .multiselect-item-list {
            max-height: 280px;
            overflow-y: auto;
        }

        .multiselect-item {
            padding: 8px 14px;
            transition: background 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f8fafc;
        }

        .multiselect-item:hover {
            background-color: #f1f5f9;
        }

        .multiselect-item input[type="checkbox"] {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #10b981;
        }

        .multiselect-item label {
            flex: 1;
            cursor: pointer;
            margin-bottom: 0;
            font-size: 0.85rem;
            color: #334155;
            user-select: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .multiselect-item.selected {
            background-color: #ecfdf5;
        }

        .dropdown-toggle-dept {
            background-color: white !important;
            border-radius: 10px !important;
            font-size: 0.85rem !important;
            min-width: 220px;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.45rem 0.85rem !important;
            border: 1px solid #cbd5e1;
        }

        /* Table Styling */
        .table-salary thead th {
            background-color: #f8fafc !important;
            color: #334155 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            font-size: 0.78rem !important;
            letter-spacing: 0.04em;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 12px 14px !important;
        }

        .table-salary tbody td {
            font-size: 0.85rem;
            padding: 12px 14px;
            vertical-align: middle;
        }

        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 0.35rem 0.75rem !important;
            outline: none !important;
            font-size: 0.85rem !important;
        }

        .dt-buttons .btn-success {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            border-radius: 0.5rem !important;
            font-weight: 600 !important;
            padding: 0.35rem 0.85rem !important;
            font-size: 0.8rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            box-shadow: 0 2px 5px rgba(16, 185, 129, 0.25) !important;
        }

        .dept-progress-bar {
            height: 6px;
            border-radius: 3px;
            background-color: #e2e8f0;
            overflow: hidden;
        }

        .dept-progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.6s ease;
        }

        .badge-dept-rank {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.72rem;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Top Header & Filter -->
    <div class="page-header-container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                        <i class="fas fa-file-invoice-dollar me-1"></i> ระบบงานบุคลากร
                    </span>
                    <span class="text-muted small">•</span>
                    <span class="text-muted small">ปีงบประมาณ {{ $year_id }}</span>
                </div>
                <h4 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-wallet text-success"></i>
                    รายงานข้อมูลการจ่ายเงินเดือนและค่าตอบแทน
                </h4>
            </div>

            <!-- Main Filter Form -->
            <form action="{{ route('backoffice.salary.index') }}" method="GET" id="filterForm" class="d-flex flex-wrap align-items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="hidden" name="compen_type" value="{{ $compen_type }}">
                <input type="hidden" name="month_id" value="{{ $month_id }}">

                <!-- Fiscal Year Selector -->
                <div class="d-flex align-items-center gap-1">
                    <label class="text-muted small fw-bold text-nowrap mb-0">ปีงบประมาณ:</label>
                    <select name="year_id" id="year_id" class="form-select form-select-sm fw-bold shadow-sm" style="width: 110px; border-radius: 8px;" onchange="document.getElementById('filterForm').submit()">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == $year_id ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Multi-select -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-white dropdown-toggle dropdown-toggle-dept shadow-sm" type="button" 
                        id="deptDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="dropdown-label text-truncate" style="max-width: 180px;">
                            @if(empty($dept_ids) || count($dept_ids) == count($depts))
                                -- ทุกหน่วยงาน --
                            @else
                                {{ $selectedDeptText }}
                            @endif
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-multiselect p-0 shadow" aria-labelledby="deptDropdown">
                        <div class="multiselect-header">
                            <input type="text" class="form-control form-control-sm multiselect-search mb-2" 
                                placeholder="ค้นหาหน่วยงาน..." id="deptSearch">
                            <div class="form-check ms-1 mt-1">
                                <input class="form-check-input" type="checkbox" id="selectAllDept" {{ !empty($dept_ids) && count($dept_ids) == count($depts) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-success small" for="selectAllDept" style="cursor: pointer;">
                                    เลือกทั้งหมด
                                </label>
                            </div>
                        </div>
                        <div class="multiselect-item-list" id="deptList">
                            @foreach($depts as $dept)
                                <div class="multiselect-item {{ in_array($dept->HR_DEPARTMENT_SUB_SUB_ID, $dept_ids) ? 'selected' : '' }}">
                                    <input type="checkbox" name="dept_ids[]" value="{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}" 
                                        id="dept_{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}" 
                                        class="dept-checkbox"
                                        {{ in_array($dept->HR_DEPARTMENT_SUB_SUB_ID, $dept_ids) ? 'checked' : '' }}>
                                    <label for="dept_{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}">{{ $dept->HR_DEPARTMENT_SUB_SUB_NAME }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div class="p-2 border-top bg-light text-center">
                            <button type="submit" class="btn btn-sm btn-success w-100 rounded-pill py-1">
                                <i class="fas fa-check me-1"></i> นำไปใช้ (กรอง)
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-sm px-3 shadow-sm" style="border-radius: 8px;">
                    <i class="fas fa-search me-1"></i> ค้นหา
                </button>

                @if(!empty($dept_ids) || $month_id !== 'all' || $compen_type !== 'all')
                    <a href="{{ route('backoffice.salary.index', ['tab' => $tab, 'year_id' => $year_id, 'month_id' => 'all', 'compen_type' => 'all']) }}" class="btn btn-light btn-sm text-muted shadow-sm" style="border-radius: 8px;" title="รีเซ็ตตัวกรอง">
                        <i class="fas fa-rotate-left"></i> รีเซ็ต
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Main Tabs Navigation (3 Tabs: Overview, Salary, Compensation) -->
    <ul class="nav nav-tabs nav-tabs-custom mb-3">
        <!-- Tab 1: ภาพรวมทั้งหมด -->
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'overview' ? 'active active-overview' : '' }}" 
               href="{{ route('backoffice.salary.index', array_merge(request()->except(['tab', 'compen_type']), ['tab' => 'overview'])) }}">
                <i class="fas fa-layer-group fs-5"></i>
                <span>ภาพรวมทั้งหมด</span>
                @if($tab === 'overview')
                    <span class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-2 ms-1">
                        {{ number_format($total_persons) }} คน
                    </span>
                @endif
            </a>
        </li>

        <!-- Tab 2: รายงานเงินเดือน -->
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'salary' ? 'active active-salary' : '' }}" 
               href="{{ route('backoffice.salary.index', array_merge(request()->except(['tab', 'compen_type']), ['tab' => 'salary'])) }}">
                <i class="fas fa-money-check-dollar fs-5"></i>
                <span>รายงานเงินเดือน</span>
                @if($tab === 'salary')
                    <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2 ms-1">
                        {{ number_format($total_persons) }} คน
                    </span>
                @endif
            </a>
        </li>

        <!-- Tab 3: รายงานค่าตอบแทน -->
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'compen' ? 'active active-compen' : '' }}" 
               href="{{ route('backoffice.salary.index', array_merge(request()->except('tab'), ['tab' => 'compen'])) }}">
                <i class="fas fa-hand-holding-dollar fs-5"></i>
                <span>รายงานค่าตอบแทน</span>
                @if($tab === 'compen')
                    <span class="badge bg-info bg-opacity-15 text-info rounded-pill px-2 ms-1">
                        {{ number_format($total_persons) }} คน
                    </span>
                @endif
            </a>
        </li>
    </ul>

    <!-- Sub-Filter: Compensation Sub-Type Filter Bar (Only when Tab == 'compen') -->
    @if($tab === 'compen')
        <div class="card border-0 shadow-xs rounded-4 bg-white p-2 mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-2">
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <span class="small fw-bold text-muted me-1 d-flex align-items-center gap-1">
                        <i class="fas fa-filter text-info"></i>
                        <span>ประเภทค่าตอบแทน:</span>
                    </span>
                    @foreach($availableCompenTypes as $cKey => $cLabel)
                        <a href="{{ route('backoffice.salary.index', array_merge(request()->except('compen_type'), ['compen_type' => $cKey])) }}"
                           class="btn btn-sm rounded-pill px-3 py-1 fw-bold transition-all {{ $compen_type === $cKey ? 'btn-info text-white shadow-sm' : 'btn-light text-dark hover-bg-gray' }}"
                           style="font-size: 0.8rem;">
                            {{ $cLabel }}
                        </a>
                    @endforeach
                </div>
                @if($compen_type !== 'all')
                    <div>
                        <a href="{{ route('backoffice.salary.index', array_merge(request()->except('compen_type'), ['compen_type' => 'all'])) }}" 
                           class="btn btn-link btn-sm text-muted text-decoration-none p-0 small">
                            <i class="fas fa-times me-1"></i> ล้างตัวเลือก
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Sub-Tabs: Month Navigation Bar (First Tab is "รวมทั้งปี") -->
    <div class="month-subtabs-container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="small fw-bold text-muted d-flex align-items-center gap-1">
                <i class="fas fa-calendar-days text-success"></i>
                <span>งวดจ่าย:</span>
            </div>
            <div class="d-flex align-items-center gap-1 flex-wrap month-nav-scroll">
                @php
                    $activeTabClass = $tab === 'overview' ? 'active-tab-overview' : ($tab === 'salary' ? 'active-tab-salary' : 'active-tab-compen');
                @endphp

                <!-- Tab แรกรวมทั้งปี -->
                <a href="{{ route('backoffice.salary.index', array_merge(request()->except('month_id'), ['month_id' => 'all'])) }}"
                   class="month-subtab-btn {{ $month_id === 'all' ? $activeTabClass : 'bg-light text-dark' }}">
                    <i class="fas fa-calendar-check me-1"></i> รวมทั้งปี
                </a>

                <!-- 12 Fiscal Months Tabs (ต.ค. - ก.ย.) -->
                @foreach($fiscalMonthsList as $fm)
                    @php
                        $isActiveMonth = (string)$month_id === (string)$fm['month_id'];
                    @endphp
                    <a href="{{ route('backoffice.salary.index', array_merge(request()->except('month_id'), ['month_id' => $fm['month_id']])) }}"
                       class="month-subtab-btn position-relative {{ $isActiveMonth ? $activeTabClass : ($fm['has_data'] ? 'bg-light text-dark' : 'bg-light text-muted opacity-60') }}"
                       title="{{ $fm['name_full'] }} {{ $fm['cal_year'] }}">
                        <span>{{ $fm['label'] }}</span>
                        @if($fm['has_data'] && !$isActiveMonth)
                            <span class="badge bg-success p-1 rounded-circle" style="width: 6px; height: 6px; display: inline-block;"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        @if($tab === 'overview')
            <!-- Overview Card 1: งบจ่ายรวมของ รพ. ทั้งสิ้น (Total Hospital Payout) -->
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card bg-white p-3 h-100 border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">งบจ่ายจริงของ รพ. ทั้งสิ้น</div>
                            <h3 class="fw-bold text-success mb-0 mt-1">
                                {{ number_format($total_receive, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                โอนสุทธิ {{ number_format($total_net, 2) }} ฿ | หักนำส่ง {{ number_format($total_pay, 2) }} ฿
                            </div>
                        </div>
                        <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-building-columns"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Card 2: งบเงินเดือน (Salary) -->
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card bg-white p-3 h-100 border-start border-4 border-primary">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">งบเงินเดือน (รพ. จ่าย)</div>
                            <h3 class="fw-bold text-primary mb-0 mt-1">
                                {{ number_format($total_salary_rec_sum, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                โอนสุทธิ {{ number_format($total_salary_net_sum, 2) }} ฿ | หักนำส่ง {{ number_format($total_salary_pay_sum, 2) }} ฿
                            </div>
                        </div>
                        <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-money-check-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Card 3: งบค่าตอบแทน (Compensation) -->
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card bg-white p-3 h-100 border-start border-4 border-info">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">งบค่าตอบแทน (รพ. จ่าย)</div>
                            <h3 class="fw-bold text-info mb-0 mt-1">
                                {{ number_format($total_compen_rec_sum, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                โอนสุทธิ {{ number_format($total_compen_net_sum, 2) }} ฿ | หักนำส่ง {{ number_format($total_compen_pay_sum, 2) }} ฿
                            </div>
                        </div>
                        <div class="kpi-icon-box bg-info bg-opacity-10 text-info">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Card 4: ยอดหักนำส่งแทน จนท. (Deductions Remitted) -->
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card bg-white p-3 h-100 border-start border-4 border-danger">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">ยอดหักนำส่งแทน จนท.</div>
                            <h3 class="fw-bold text-danger mb-0 mt-1">
                                {{ number_format($total_pay, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                หักเงินเดือน {{ number_format($total_salary_pay_sum, 2) }} ฿ + ค่าตอบแทน {{ number_format($total_compen_pay_sum, 2) }} ฿
                            </div>
                        </div>
                        <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Salary / Compen Specific Cards -->
            <!-- 1. Total Receive (เงินได้ตั้งเบิก) -->
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">
                                {{ $tab === 'salary' ? 'ยอดจ่ายเงินเดือนรวม (ก่อนหัก)' : 'ยอดจ่ายค่าตอบแทนรวม (ก่อนหัก)' }}
                            </div>
                            <h3 class="fw-bold text-primary mb-0 mt-1">
                                {{ number_format($total_receive, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                {{ $tab === 'salary' ? 'เงินเดือนและค่าจ้างประจำ' : ($compen_type === 'all' ? 'ค่าตอบแทนทุกประเภท' : $compen_type) }}
                            </div>
                        </div>
                        <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-circle-plus"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Total Deductions (ยอดหักนำส่ง) -->
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">
                                {{ $tab === 'salary' ? 'ยอดหักนำส่ง (เงินเดือน)' : 'ยอดหักนำส่ง (ค่าตอบแทน)' }}
                            </div>
                            <h3 class="fw-bold text-danger mb-0 mt-1">
                                {{ number_format($total_pay, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                {{ $tab === 'salary' ? 'กบข. / สปส. / ภาษี / หนี้สิน' : 'สวัสดิการ / กยศ. / ค่าไฟ / อื่นๆ' }}
                            </div>
                        </div>
                        <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-circle-minus"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Net Payout (ยอดโอนสุทธิ) -->
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">
                                {{ $tab === 'salary' ? 'เงินเดือนสุทธิ (โอนเข้าบัญชี)' : 'ค่าตอบแทนสุทธิ (โอนเข้าบัญชี)' }}
                            </div>
                            <h3 class="fw-bold text-success mb-0 mt-1">
                                {{ number_format($total_net, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                เฉลี่ย {{ number_format($avg_per_person, 2) }} บาท / คน
                            </div>
                        </div>
                        <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-sack-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Staff Count -->
            <div class="col-xl-3 col-md-6">
                <div class="card kpi-card bg-white p-3 h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">จำนวนบุคลากรที่รับเงิน</div>
                            <h3 class="fw-bold mb-0 mt-1" style="color: #6366f1;">
                                {{ number_format($total_persons) }} <span class="fs-6 fw-normal text-muted">คน</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                กระจายตาม {{ number_format($total_depts_count) }} หน่วยงาน
                            </div>
                        </div>
                        <div class="kpi-icon-box" style="background-color: #e0e7ff; color: #4f46e5;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Section: กราฟเส้นแนวโน้มการจ่ายตลอดปีงบประมาณ 12 เดือน (Trend Line/Area Chart) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2 flex-wrap">
                    <i class="fas fa-chart-line text-success"></i>
                    <span>
                        กราฟแนวโน้มการจ่าย
                        @if($tab === 'overview')
                            เงินเดือนและค่าตอบแทน
                        @elseif($tab === 'salary')
                            เงินเดือน
                        @else
                            ค่าตอบแทน {{ $compen_type !== 'all' ? "($compen_type)" : '' }}
                        @endif
                        ตลอดปีงบประมาณ {{ $year_id }}
                    </span>
                    @if($selectedDeptText !== 'ทั้งหมด')
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 small rounded-pill">
                            <i class="fas fa-building me-1"></i> {{ $selectedDeptText }}
                        </span>
                    @endif
                </h6>
                <div class="text-muted small mt-1">
                    เปรียบเทียบยอดจ่ายรายเดือน 12 เดือน (ตุลาคม {{ (int)$year_id - 1 }} - กันยายน {{ $year_id }})
                    • หน่วยงาน: <strong class="{{ $selectedDeptText !== 'ทั้งหมด' ? 'text-primary' : 'text-dark' }}">{{ $selectedDeptText }}</strong>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($tab === 'overview')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill small">
                        <i class="fas fa-money-bill-wave me-1"></i> เงินเดือนรวมทั้งปี: {{ number_format($fiscalTrendData['total_salary_year'], 2) }} บาท
                    </span>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill small">
                        <i class="fas fa-hand-holding-dollar me-1"></i> ค่าตอบแทนรวมทั้งปี: {{ number_format($fiscalTrendData['total_compen_year'], 2) }} บาท
                    </span>
                    <span class="badge bg-dark bg-opacity-10 text-dark border px-3 py-2 rounded-pill small fw-bold">
                        <i class="fas fa-calculator me-1"></i> รวมสุทธิทั้งสิ้น: {{ number_format($fiscalTrendData['total_grand_year'], 2) }} บาท
                    </span>
                @elseif($tab === 'salary')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill small">
                        <i class="fas fa-money-bill-wave me-1"></i> เงินเดือนรวมทั้งปี: {{ number_format($fiscalTrendData['total_salary_year'], 2) }} บาท
                    </span>
                @else
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill small">
                        <i class="fas fa-hand-holding-dollar me-1"></i> ค่าตอบแทนรวมทั้งปี: {{ number_format($fiscalTrendData['total_compen_year'], 2) }} บาท
                    </span>
                    @if($compen_type !== 'all')
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill small">
                            {{ $availableCompenTypes[$compen_type] ?? $compen_type }}
                        </span>
                    @endif
                @endif
            </div>
        </div>
        <div class="card-body p-3">
            <div id="fiscal_trend_chart" style="min-height: 330px;"></div>
        </div>
    </div>

    <!-- Section: สรุปแยกตามหน่วยงาน (Department Breakdown & Bar Chart) -->
    <div class="row g-4 mb-4">
        <!-- Chart: Top Departments -->
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="fas fa-chart-column text-success me-2"></i>
                        ยอดจ่ายสูงสุดตามหน่วยงาน (8 อันดับแรก)
                    </h6>
                    <span class="badge bg-light text-muted border small">บาท</span>
                </div>
                <div class="card-body p-3">
                    <div id="dept_payout_chart" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>

        <!-- Table: Department Breakdown Summary -->
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-sitemap text-primary me-1"></i>
                        <span>สรุปการจ่ายแยกตามหน่วยงาน</span>
                        @if($selectedDeptText !== 'ทั้งหมด')
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 small rounded-pill">
                                กรอง: {{ $selectedDeptText }}
                            </span>
                        @endif
                    </h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 rounded-pill">
                        {{ $selectedDeptText !== 'ทั้งหมด' ? count($deptStats) . ' หน่วยงานที่เลือก' : 'ทั้งหมด ' . count($deptStats) . ' หน่วยงาน' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 text-nowrap">
                            <thead class="table-light sticky-top">
                                <tr class="text-muted small">
                                    <th class="ps-4" style="width: 40px;">#</th>
                                    <th>หน่วยงาน</th>
                                    <th class="text-center">จำนวนคน</th>
                                    <th class="text-end">ยอดรับรวม</th>
                                    <th class="text-end">ยอดหักรวม</th>
                                    <th class="text-end">ยอดรับสุทธิ (บาท)</th>
                                    <th class="pe-4" style="width: 110px;">สัดส่วน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deptStats as $idx => $ds)
                                    @php
                                        $percent = $total_net > 0 ? round(($ds->total_net / $total_net) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge-dept-rank {{ $idx < 3 ? 'bg-success text-white' : 'bg-light text-muted' }}">
                                                {{ $idx + 1 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $ds->dept_name }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                {{ $ds->person_count }} คน
                                            </span>
                                        </td>
                                        <td class="text-end text-muted small">{{ number_format($ds->total_receive, 2) }}</td>
                                        <td class="text-end text-danger small">-{{ number_format($ds->total_pay, 2) }}</td>
                                        <td class="text-end fw-bold text-success">{{ number_format($ds->total_net, 2) }}</td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="dept-progress-bar flex-grow-1">
                                                    <div class="dept-progress-fill bg-success" style="width: {{ min(100, $percent) }}%;"></div>
                                                </div>
                                                <small class="text-muted fw-bold" style="font-size: 0.72rem; min-width: 32px;">{{ $percent }}%</small>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: ตารางรายชื่อบุคลากร (Personnel Payment Table) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-5 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h5 class="m-0 fw-bold text-dark d-flex align-items-center gap-2 flex-wrap">
                    <i class="fas fa-users-viewfinder text-success"></i>
                    <span>
                        ตารางรายชื่อบุคลากร - 
                        @if($tab === 'overview')
                            ภาพรวม (เงินเดือนและค่าตอบแทน)
                        @elseif($tab === 'salary')
                            เงินเดือน
                        @else
                            ค่าตอบแทน {{ $compen_type !== 'all' ? "($compen_type)" : '' }}
                        @endif
                    </span>
                    @if($selectedDeptText !== 'ทั้งหมด')
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 small rounded-pill">
                            <i class="fas fa-building me-1"></i> {{ $selectedDeptText }}
                        </span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-muted border px-2.5 py-1 small rounded-pill">
                            <i class="fas fa-building me-1"></i> ทุกหน่วยงาน
                        </span>
                    @endif
                </h5>
                <div class="text-muted small mt-1 d-flex flex-wrap align-items-center gap-2">
                    <span>
                        @if($month_id !== 'all')
                            <i class="far fa-calendar-alt me-1"></i> งวดประจำเดือน {{ $thaiMonths[(int)$month_id] ?? $month_id }} ปีงบประมาณ {{ $year_id }}
                        @else
                            <i class="far fa-calendar-alt me-1"></i> ยอดรวมสะสมทั้งปีงบประมาณ {{ $year_id }}
                        @endif
                    </span>
                    <span class="text-muted">•</span>
                    <span>
                        <i class="fas fa-building me-1"></i> หน่วยงาน: 
                        <strong class="{{ $selectedDeptText !== 'ทั้งหมด' ? 'text-primary' : 'text-dark' }}">
                            {{ $selectedDeptText }}
                        </strong>
                    </span>
                    <span class="text-muted">•</span>
                    <span><i class="fas fa-user-check me-1"></i> แสดง {{ count($persons) }} รายชื่อ</span>
                </div>
            </div>
            <div>
                <span class="badge bg-light text-muted border px-3 py-2 small">
                    <i class="fas fa-filter me-1"></i> สามารถค้นหาชื่อ, สกุล, เลขบัตร, ตำแหน่ง หรือหน่วยงานได้
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-salary" id="salary_person_table">
                    <thead>
                        <tr class="text-nowrap">
                            <th class="text-center" style="width: 50px;">ลำดับ</th>
                            <th>เลขบัตรประชาชน</th>
                            <th>ชื่อ - นามสกุล</th>
                            <th>ตำแหน่งสายงาน</th>
                            <th>ประเภทบุคลากร</th>
                            <th>หน่วยงาน</th>
                            <th class="text-center">งวดจ่าย</th>
                            @if($tab === 'overview')
                                <th class="text-end text-success">เงินเดือน (สุทธิ)</th>
                                <th class="text-end text-info">ค่าตอบแทน (สุทธิ)</th>
                                <th class="text-end text-primary">รวมสุทธิทั้งสิ้น (บาท)</th>
                                <th class="text-center" style="width: 120px;">จัดการ</th>
                            @else
                                <th class="text-end">ยอดรับรวม</th>
                                <th class="text-end">ยอดหักรวม</th>
                                <th class="text-end">ยอดรับสุทธิ (บาท)</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($persons as $index => $p)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <span class="font-monospace text-muted small">{{ $p->HR_CID ?: '-' }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        {{ $p->HR_PREFIX_NAME }}{{ $p->HR_FNAME }} {{ $p->HR_LNAME }}
                                    </div>
                                    @if($p->SALARYALL_BOOK_NUM)
                                        <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                                            <i class="fas fa-credit-card me-1"></i>{{ $p->SALARYALL_BOOK_NUM }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $p->POSITION_IN_WORK ?: '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-light text-primary border px-2 py-1 small">
                                        {{ $p->HR_PERSON_TYPE_NAME ?: 'พนักงาน' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-dark">{{ $p->department_name ?: '-' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($month_id !== 'all')
                                        <span class="badge bg-light text-success border">
                                            {{ $thaiMonthsShort[(int)$p->SALARYALL_MONTH_ID] ?? $p->SALARYALL_MONTH_ID }}
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2">
                                            {{ $p->month_count }} งวด
                                        </span>
                                    @endif
                                </td>
                                @if($tab === 'overview')
                                    <td class="text-end text-success font-monospace">{{ number_format($p->salary_net, 2) }}</td>
                                    <td class="text-end text-info font-monospace">{{ number_format($p->compen_net, 2) }}</td>
                                    <td class="text-end fw-bold text-primary font-monospace fs-6">
                                        {{ number_format($p->net_total, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-success py-1 px-2 rounded-pill shadow-xs d-inline-flex align-items-center gap-1"
                                            style="font-size: 0.78rem;" onclick="loadPersonDetail({{ $p->person_id ?: $p->SALARYALL_PERSON_ID }}, '{{ $year_id }}')">
                                            <i class="fas fa-eye"></i>
                                            <span>ดูรายละเอียด</span>
                                        </button>
                                    </td>
                                @else
                                    <td class="text-end text-muted font-monospace">{{ number_format($p->total_receive, 2) }}</td>
                                    <td class="text-end text-danger font-monospace">
                                        @if($p->total_pay > 0)
                                            -{{ number_format($p->total_pay, 2) }}
                                        @else
                                            0.00
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-success font-monospace fs-6">
                                        {{ number_format($p->net_total, 2) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Person Monthly History Across Fiscal Year -->
<div class="modal fade" id="personDetailModal" tabindex="-1" aria-labelledby="personDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" id="personDetailModalContent">
            <div class="p-5 text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
                <div class="mt-2 text-muted">กำลังโหลดข้อมูลประวัติเงินเดือน...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Itemized Salary / Compensation Slip -->
<div class="modal fade" id="slipDetailModal" tabindex="-1" aria-labelledby="slipDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" id="slipDetailModalContent">
            <div class="p-5 text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">กำลังโหลด...</span>
                </div>
                <div class="mt-2 text-muted">กำลังโหลดสลิปรายการ...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>

    <script>
        $(document).ready(function () {
            // DataTables Setup with Style Override & Excel Export
            $('#salary_person_table').DataTable({
                dom: '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center px-4 py-3 border-bottom gap-2"<"d-flex align-items-center"l><"d-flex align-items-center gap-2"fB>>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center px-4 py-3 border-top gap-2"ip>',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> ส่งออก Excel',
                        className: 'btn btn-success',
                        title: 'รายงาน{{ $tab == "overview" ? "ภาพรวมเงินเดือนและค่าตอบแทน" : ($tab == "salary" ? "เงินเดือน" : "ค่าตอบแทน") }}_{{ $year_id }}_{{ date("Ymd_His") }}',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                        }
                    }
                ],
                language: {
                    search: "ค้นหาชื่อ / เลขบัตร / ตำแหน่ง:",
                    searchPlaceholder: "พิมพ์ชื่อบุคลากร...",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    paginate: {
                        previous: "ก่อนหน้า",
                        next: "ถัดไป"
                    },
                    emptyTable: "ไม่พบข้อมูลการจ่ายเงินเดือน/ค่าตอบแทน"
                },
                @if($tab === 'overview')
                columnDefs: [
                    { orderable: false, targets: 10 }
                ],
                @endif
                order: [[9, 'desc']], // Order by net total descending
                pageLength: 25
            });

            // Department Search Filter inside Multiselect Dropdown
            $('#deptSearch').on('keyup', function () {
                const term = $(this).val().toLowerCase();
                $('#deptList .multiselect-item').each(function () {
                    const text = $(this).find('label').text().toLowerCase();
                    $(this).toggle(text.indexOf(term) > -1);
                });
            });

            // Select All Departments Checkbox
            $('#selectAllDept').on('change', function () {
                const isChecked = $(this).is(':checked');
                $('.dept-checkbox').prop('checked', isChecked);
                if (isChecked) {
                    $('.multiselect-item').addClass('selected');
                } else {
                    $('.multiselect-item').removeClass('selected');
                }
            });

            // Toggle selected class on dept checkbox change
            $('.dept-checkbox').on('change', function () {
                $(this).closest('.multiselect-item').toggleClass('selected', $(this).is(':checked'));
                const total = $('.dept-checkbox').length;
                const checked = $('.dept-checkbox:checked').length;
                $('#selectAllDept').prop('checked', total === checked);
            });

            // Prevent dropdown from closing when clicking inside multiselect menu
            $('.dropdown-menu-multiselect').on('click', function (e) {
                e.stopPropagation();
            });

            // 1. Render Fiscal Year Trend Chart (Area / Spline)
            const trendLabels = {!! json_encode($fiscalTrendData['labels']) !!};
            const trendSalary = {!! json_encode($fiscalTrendData['salary']) !!};
            const trendCompen = {!! json_encode($fiscalTrendData['compen']) !!};
            const trendGrand = {!! json_encode($fiscalTrendData['grand']) !!};
            const currentTab = '{{ $tab }}';

            let chartSeries = [];
            let chartColors = [];

            if (currentTab === 'overview') {
                chartSeries = [
                    { name: 'เงินเดือน (สุทธิ)', data: trendSalary },
                    { name: 'ค่าตอบแทน (สุทธิ)', data: trendCompen },
                    { name: 'รวมจ่ายสุทธิทั้งสิ้น', data: trendGrand }
                ];
                chartColors = ['#10b981', '#0ea5e9', '#6366f1'];
            } else if (currentTab === 'salary') {
                chartSeries = [
                    { name: 'เงินเดือน (สุทธิ)', data: trendSalary }
                ];
                chartColors = ['#10b981'];
            } else if (currentTab === 'compen') {
                const compenSeriesData = {!! json_encode($fiscalTrendData['compen_series'] ?? []) !!};
                if (compenSeriesData && compenSeriesData.length > 0) {
                    chartSeries = compenSeriesData.map(s => ({ name: s.name, data: s.data }));
                    chartColors = compenSeriesData.map(s => s.color);
                } else {
                    chartSeries = [
                        { name: 'ค่าตอบแทน (สุทธิ)', data: trendCompen }
                    ];
                    chartColors = ['#0ea5e9'];
                }
            }

            if (trendLabels.length > 0 && typeof ApexCharts !== 'undefined') {
                const trendOptions = {
                    series: chartSeries,
                    chart: {
                        type: 'area',
                        height: 330,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: false,
                                zoomin: false,
                                zoomout: false,
                                pan: false,
                                reset: false
                            }
                        },
                        fontFamily: 'Nunito, sans-serif'
                    },
                    colors: chartColors,
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [0, 95, 100]
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: chartSeries.map((s, idx) => (currentTab === 'overview' && idx === 2) ? 3.5 : 3),
                        dashArray: chartSeries.map((s, idx) => (currentTab === 'overview' && idx === 2) ? 4 : 0)
                    },
                    markers: {
                        size: 4,
                        hover: { size: 6 }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: trendLabels,
                        labels: {
                            style: { fontSize: '11px', fontWeight: 600 }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                return (val >= 1000000 ? (val / 1000000).toFixed(2) + 'M' : (val / 1000).toFixed(0) + 'k');
                            },
                            style: { fontSize: '11px', fontWeight: 600 }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return Number(val).toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' บาท';
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '12px',
                        fontWeight: 600,
                        markers: { radius: 12 }
                    },
                    grid: {
                        borderColor: '#f1f5f9'
                    }
                };

                const trendChart = new ApexCharts(document.querySelector("#fiscal_trend_chart"), trendOptions);
                trendChart.render();
            }

            // 2. Render ApexChart for Top Departments (Bar)
            const chartLabels = {!! json_encode($chartData['labels']) !!};
            const chartNets = {!! json_encode($chartData['net_totals']) !!};

            if (chartLabels.length > 0 && typeof ApexCharts !== 'undefined') {
                const chartOptions = {
                    series: [{
                        name: 'ยอดรับสุทธิ (บาท)',
                        data: chartNets
                    }],
                    chart: {
                        type: 'bar',
                        height: 320,
                        toolbar: { show: false },
                        fontFamily: 'Nunito, sans-serif'
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            horizontal: true,
                            barHeight: '65%',
                            distributed: false
                        }
                    },
                    colors: ['#10b981'],
                    dataLabels: {
                        enabled: true,
                        formatter: function (val) {
                            return (val / 1000).toFixed(1) + 'k ฿';
                        },
                        style: {
                            fontSize: '11px',
                            fontWeight: 600,
                            colors: ['#334155']
                        },
                        offsetX: 30
                    },
                    xaxis: {
                        categories: chartLabels,
                        labels: {
                            formatter: function (val) {
                                return (val >= 1000000 ? (val / 1000000).toFixed(1) + 'M' : (val / 1000).toFixed(0) + 'k');
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: { fontSize: '11px', fontWeight: 600 }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return Number(val).toLocaleString('th-TH', { minimumFractionDigits: 2 }) + ' บาท';
                            }
                        }
                    },
                    grid: {
                        borderColor: '#f1f5f9'
                    }
                };

                const deptChart = new ApexCharts(document.querySelector("#dept_payout_chart"), chartOptions);
                deptChart.render();
            }
        });

        // Load Person Detail Modal via AJAX
        function loadPersonDetail(personId, yearId) {
            const modalEl = document.getElementById('personDetailModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            $('#personDetailModalContent').html(`
                <div class="p-5 text-center">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <div class="mt-2 text-muted fw-bold">กำลังโหลดประวัติการจ่ายเงินเดือนและค่าตอบแทน...</div>
                </div>
            `);

            $.ajax({
                url: "{{ url('/backoffice/salary/person') }}/" + personId,
                method: "GET",
                data: { year_id: yearId },
                success: function (html) {
                    $('#personDetailModalContent').html(html);
                },
                error: function (xhr) {
                    $('#personDetailModalContent').html(`
                        <div class="p-5 text-center text-danger">
                            <i class="fas fa-triangle-exclamation fs-1 mb-2"></i>
                            <div class="fw-bold">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>
                            <div class="text-muted small">${xhr.responseJSON?.error || 'กรุณาลองใหม่อีกครั้ง'}</div>
                        </div>
                    `);
                }
            });
        }

        // Load Monthly Unified Slip (both Salary & Compensation) via AJAX
        function loadMonthlySlip(personId, yearId, monthId) {
            const slipModalEl = document.getElementById('slipDetailModal');
            const slipModal = bootstrap.Modal.getOrCreateInstance(slipModalEl);
            slipModal.show();

            $('#slipDetailModalContent').html(`
                <div class="p-5 text-center">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <div class="mt-2 text-muted fw-bold">กำลังโหลดใบแจ้งยอดเงินเดือนและค่าตอบแทน...</div>
                </div>
            `);

            $.ajax({
                url: "{{ url('/backoffice/salary/monthly-slip') }}/" + personId + "/" + yearId + "/" + monthId,
                method: "GET",
                success: function (html) {
                    $('#slipDetailModalContent').html(html);
                },
                error: function (xhr) {
                    $('#slipDetailModalContent').html(`
                        <div class="p-5 text-center text-danger">
                            <i class="fas fa-triangle-exclamation fs-1 mb-2"></i>
                            <div class="fw-bold">เกิดข้อผิดพลาดในการโหลดใบแจ้งยอด</div>
                            <div class="text-muted small">${xhr.responseJSON?.error || 'กรุณาลองใหม่อีกครั้ง'}</div>
                        </div>
                    `);
                }
            });
        }

        // Load Slip Detail Modal via AJAX (Single item fallback)
        function loadSlipDetail(salaryAllId) {
            const slipModalEl = document.getElementById('slipDetailModal');
            const slipModal = bootstrap.Modal.getOrCreateInstance(slipModalEl);
            slipModal.show();

            $('#slipDetailModalContent').html(`
                <div class="p-5 text-center">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">กำลังโหลด...</span>
                    </div>
                    <div class="mt-2 text-muted fw-bold">กำลังโหลดใบแจ้งยอดรายการรับ-หัก...</div>
                </div>
            `);

            $.ajax({
                url: "{{ url('/backoffice/salary/slip') }}/" + salaryAllId,
                method: "GET",
                success: function (html) {
                    $('#slipDetailModalContent').html(html);
                },
                error: function (xhr) {
                    $('#slipDetailModalContent').html(`
                        <div class="p-5 text-center text-danger">
                            <i class="fas fa-triangle-exclamation fs-1 mb-2"></i>
                            <div class="fw-bold">เกิดข้อผิดพลาดในการโหลดใบแจ้งยอด</div>
                            <div class="text-muted small">${xhr.responseJSON?.error || 'กรุณาลองใหม่อีกครั้ง'}</div>
                        </div>
                    `);
                }
            });
        }

        // Print Slip helper
        function printSlip() {
            const printContent = document.getElementById('printableSlipArea').innerHTML;
            const printWindow = window.open('', '', 'height=850,width=820');
            printWindow.document.write('<!DOCTYPE html><html><head><meta charset="utf-8">');
            printWindow.document.write('<title>ใบแจ้งการจ่ายเงินเดือนและเงินค่าตอบแทนรายบุคคล - โรงพยาบาลหัวตะพาน</title>');
            printWindow.document.write('<link href="{{ asset("vendor/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet">');
            printWindow.document.write('<link href="{{ asset("vendor/fontawesome/css/all.min.css") }}" rel="stylesheet">');
            printWindow.document.write(`
                <style>
                    @page {
                        size: A4 portrait;
                        margin: 8mm 10mm 8mm 10mm;
                    }
                    * {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        box-sizing: border-box;
                    }
                    html, body {
                        margin: 0;
                        padding: 0;
                        background: #ffffff;
                        font-family: "Sarabun", "TH Sarabun New", "TH Sarabun PSK", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                        font-size: 9.5pt;
                        line-height: 1.25;
                        color: #0f172a;
                    }
                    .slip-container {
                        width: 100%;
                        max-width: 190mm;
                        margin: 0 auto;
                        padding: 0;
                        page-break-inside: avoid;
                    }
                    table {
                        border-collapse: collapse !important;
                        width: 100% !important;
                        margin-bottom: 6px !important;
                    }
                    table th, table td {
                        border: 1px solid #475569 !important;
                        padding: 3px 6px !important;
                        font-size: 9pt !important;
                        line-height: 1.2 !important;
                    }
                    .table-light, thead.bg-light, .bg-light {
                        background-color: #f1f5f9 !important;
                    }
                    .signature-section {
                        page-break-inside: avoid !important;
                        margin-top: 18px !important;
                    }
                    .no-print {
                        display: none !important;
                    }
                </style>
            `);
            printWindow.document.write('</head><body>');
            printWindow.document.write('<div class="slip-container">' + printContent + '</div>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function () {
                printWindow.print();
                printWindow.close();
            }, 400);
        }
    </script>
@endpush
