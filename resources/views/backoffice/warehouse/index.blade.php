@extends('layouts.app')

@section('title', 'ระบบบริหารคลังพัสดุและเวชภัณฑ์ - SmartData')

@push('styles')
    <!-- DataTables & ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css">

    <style>
        /* Base Styling */
        .rpst-modal-card {
            cursor: pointer;
            transition: all 0.18s ease;
            user-select: none;
        }
        .rpst-modal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }
        .rpst-modal-card.active-wh-card {
            box-shadow: 0 0 0 2px #0d6efd !important;
            background-color: #eff6ff !important;
            border-radius: 8px;
        }
        .dt-buttons .btn {
            font-size: 0.75rem;
        }
        /* Custom Excel Export Button & DataTables Layout */
        .btn-excel-custom,
        .dt-buttons .btn.btn-excel-custom,
        button.dt-button.btn-excel-custom {
            background-color: #ffffff !important;
            background-image: none !important;
            border: 1.5px solid #16a34a !important;
            color: #15803d !important;
            border-radius: 50rem !important;
            padding: 0.32rem 0.9rem !important;
            font-size: 0.78rem !important;
            font-weight: 600 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            display: inline-flex !important;
            align-items: center !important;
            cursor: pointer !important;
            transition: all 0.18s ease-in-out !important;
        }
        .btn-excel-custom:hover,
        .dt-buttons .btn.btn-excel-custom:hover,
        button.dt-button.btn-excel-custom:hover {
            background-color: #16a34a !important;
            color: #ffffff !important;
            border-color: #16a34a !important;
            box-shadow: 0 4px 10px rgba(22, 163, 74, 0.25) !important;
        }
        .btn-excel-custom:hover span,
        .btn-excel-custom:hover i {
            color: #ffffff !important;
        }
        div.dataTables_filter {
            margin-bottom: 0 !important;
        }
        div.dataTables_filter label {
            margin-bottom: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            font-size: 0.82rem !important;
        }
        div.dataTables_filter input {
            margin-left: 0.35rem !important;
            border-radius: 20px !important;
            padding: 0.25rem 0.75rem !important;
            font-size: 0.82rem !important;
        }
        div.dataTables_length {
            margin-bottom: 0 !important;
        }
        div.dataTables_length label {
            margin-bottom: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            font-size: 0.82rem !important;
        }
        div.dataTables_length select {
            margin: 0 0.25rem !important;
            border-radius: 8px !important;
            font-size: 0.82rem !important;
        }
        .dt-buttons {
            margin-bottom: 0 !important;
        }
        .page-header-box {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.15rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
        }

        /* 6 Main Warehouse Tabs */
        .wh-nav-tabs {
            border-bottom: 2px solid #e2e8f0;
            gap: 0.5rem;
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            scrollbar-width: thin;
            padding-bottom: 2px;
        }

        .wh-nav-tabs .nav-link {
            border: none;
            border-radius: 12px 12px 0 0;
            color: #64748b;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 0.75rem 1.2rem;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            background: transparent;
            position: relative;
        }

        .wh-nav-tabs .nav-link:hover {
            color: #0f172a;
            background-color: #f1f5f9;
        }

        .wh-nav-tabs .nav-link.active {
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .wh-nav-tabs .nav-link.active-overview { background: linear-gradient(135deg, #4f46e5, #4338ca) !important; }
        .wh-nav-tabs .nav-link.active-material { background: linear-gradient(135deg, #f59e0b, #d97706) !important; }
        .wh-nav-tabs .nav-link.active-dental   { background: linear-gradient(135deg, #06b6d4, #0891b2) !important; }
        .wh-nav-tabs .nav-link.active-medtech  { background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important; }
        .wh-nav-tabs .nav-link.active-physical { background: linear-gradient(135deg, #10b981, #059669) !important; }
        .wh-nav-tabs .nav-link.active-medical  { background: linear-gradient(135deg, #ec4899, #db2777) !important; }
        .wh-nav-tabs .nav-link.active-rpst     { background: linear-gradient(135deg, #0284c7, #0369a1) !important; }

        /* Month Sub-tabs Navigation */
        .month-subtabs-box {
            background: #ffffff;
            border-radius: 14px;
            padding: 0.65rem 0.9rem;
            border: 1px solid #edf2f7;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            margin-bottom: 1.25rem;
        }

        .month-chip {
            border-radius: 50rem;
            padding: 0.38rem 0.95rem;
            font-size: 0.83rem;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            white-space: nowrap;
            border: 1px solid transparent;
            color: #475569;
            background-color: #f8fafc;
        }

        .month-chip:hover {
            background-color: #e2e8f0;
            color: #0f172a;
        }

        .month-chip.active {
            background-color: #0f172a !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.3);
        }


        /* KPI Cards */
        .wh-kpi-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
            transition: all 0.25s ease;
            overflow: hidden;
            position: relative;
            background: #ffffff;
        }

        .wh-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .wh-icon-box {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
        }

        /* Risk Badges */
        .badge-risk-critical {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .badge-risk-warning {
            background-color: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .badge-risk-normal {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .badge-risk-overstock {
            background-color: #f0f9ff;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }

        /* Status Pills */
        .status-badge-pending   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .status-badge-approve   { background: #fefce8; color: #ca8a04; border: 1px solid #fef08a; }
        .status-badge-verify    { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .status-badge-allow     { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-weight: bold; }
        .status-badge-disallow  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        /* Benchmark Metric Box */
        .benchmark-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 0.65rem 0.95rem;
        }

        /* Interactive Warehouse KPI Cards */
        .wh-kpi-interactive-card {
            cursor: pointer;
            transition: all 0.22s ease-in-out;
            border-radius: 16px;
            position: relative;
            background: #ffffff;
            user-select: none;
        }
        .wh-kpi-interactive-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
        }
        .wh-kpi-interactive-card.active-kpi-card {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12) !important;
            transform: translateY(-2px);
        }
        .wh-kpi-interactive-card.active-card-primary {
            border-color: #2563eb !important;
            background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%) !important;
            outline: 2.5px solid #2563eb;
        }
        .wh-kpi-interactive-card.active-card-warning {
            border-color: #f59e0b !important;
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%) !important;
            outline: 2.5px solid #f59e0b;
        }
        .wh-kpi-interactive-card.active-card-success {
            border-color: #10b981 !important;
            background: linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%) !important;
            outline: 2.5px solid #10b981;
        }
        .wh-kpi-interactive-card.active-card-danger {
            border-color: #ef4444 !important;
            background: linear-gradient(180deg, #fef2f2 0%, #ffffff 100%) !important;
            outline: 2.5px solid #ef4444;
        }
        .wh-view-section {
            animation: fadeIn 0.22s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Header & Filter Bar -->
    <div class="page-header-box">
        <div class="d-flex flex-wrap flex-xl-nowrap justify-content-between align-items-center gap-3">
            <!-- Left: Title & Fiscal Year Badge in One Line -->
            <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center rounded-3 shadow-xs" 
                     style="width: 38px; height: 38px; font-size: 1.15rem; background-color: #fef3c7; color: #b45309; flex-shrink: 0;">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <h4 class="fw-bold text-dark m-0" style="font-size: 1.2rem; white-space: nowrap;">
                    ระบบบริหารและติดตามคลังพัสดุ - เวชภัณฑ์
                </h4>
                <span class="badge rounded-pill px-2.5 py-1 ms-1 d-none d-sm-inline-block" 
                      style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 0.78rem; font-weight: 600;">
                    <i class="fas fa-calendar-check me-1"></i>ปีงบ {{ $year_id }}
                </span>
            </div>

            <!-- Right: Filter Form in One Line -->
            <form action="{{ route('backoffice.warehouse.index') }}" method="GET" id="warehouseFilterForm" class="d-flex align-items-center gap-2 flex-nowrap mb-0 ms-auto">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="hidden" name="month_id" value="{{ $month_id }}">
                <input type="hidden" name="view_mode" value="{{ $view_mode }}">

                <!-- Fiscal Year Selector -->
                <div class="d-flex align-items-center gap-1">
                    <label class="text-muted small fw-bold text-nowrap mb-0">ปีงบประมาณ:</label>
                    <select name="year_id" id="year_id" class="form-select form-select-sm fw-bold shadow-sm" style="width: 95px; border-radius: 8px;" onchange="this.form.submit()">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == $year_id ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                @if($tab === 'rpst')
                    <!-- รพ.สต. Network Node Selector Filter -->
                    <div class="d-flex align-items-center gap-1">
                        <label class="text-muted small fw-bold text-nowrap mb-0"><i class="fas fa-hospital-user text-info me-1"></i>รพ.สต.:</label>
                        <select name="rpst_id" id="rpst_id" class="form-select form-select-sm shadow-sm" style="width: 220px; border-radius: 8px;" onchange="this.form.submit()">
                            <option value="all">-- ทุก รพ.สต. (เครือข่าย 13 แห่ง) --</option>
                            @foreach($smallhosList as $sh)
                                <option value="{{ $sh->id }}" {{ (string)$sh->id === (string)$rpst_id ? 'selected' : '' }}>
                                    {{ $sh->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <!-- Sub-Warehouse Filter -->
                    <div class="d-flex align-items-center gap-1">
                        <label class="text-muted small fw-bold text-nowrap mb-0">คลังย่อย:</label>
                        <select name="sub_warehouse_id" id="sub_warehouse_id" class="form-select form-select-sm shadow-sm" style="width: 200px; border-radius: 8px;" onchange="this.form.submit()">
                            <option value="all">-- ทุกคลังย่อย (ทุกหน่วยงาน) --</option>
                            @foreach($subWarehouses as $sw)
                                <option value="{{ $sw->id }}" {{ (string)$sw->id === (string)$sub_warehouse_id ? 'selected' : '' }}>
                                    {{ $sw->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Search Input -->
                <div class="input-group input-group-sm" style="width: 170px;">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาใบเบิก/พัสดุ..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>

                @if($month_id !== 'all' || ($tab !== 'rpst' && $sub_warehouse_id !== 'all') || ($tab === 'rpst' && $rpst_id !== 'all') || !empty($search))
                    <a href="{{ route('backoffice.warehouse.index', ['tab' => $tab, 'year_id' => $year_id]) }}" class="btn btn-light btn-sm text-muted border shadow-xs" title="รีเซ็ตตัวกรอง">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- 7 Main Warehouse & Network Tabs Navigation -->
    <ul class="nav wh-nav-tabs mb-3">
        <!-- Tab 0: ภาพรวม 5 คลัง -->
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'overview' ? 'active active-overview' : '' }}" 
               href="{{ route('backoffice.warehouse.index', array_merge(request()->except('tab'), ['tab' => 'overview'])) }}">
                <i class="fas fa-layer-group"></i>
                <span>ภาพรวม 5 คลัง</span>
            </a>
        </li>

        <!-- 5 Specific Warehouses -->
        @foreach($warehouseConfigs as $k => $wc)
            <li class="nav-item">
                <a class="nav-link {{ $tab === $k ? 'active active-' . $k : '' }}" 
                   href="{{ route('backoffice.warehouse.index', array_merge(request()->except('tab'), ['tab' => $k])) }}">
                    <i class="fas {{ $wc['icon'] }}"></i>
                    <span>{{ $wc['name'] }}</span>
                </a>
            </li>
        @endforeach

        <!-- Tab 6: รพ.สต. (เครือข่าย CUP) -->
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'rpst' ? 'active active-rpst' : '' }}" 
               href="{{ route('backoffice.warehouse.index', array_merge(request()->except('tab'), ['tab' => 'rpst'])) }}">
                <i class="fas fa-hospital-user"></i>
                <span>รพ.สต. (เครือข่าย)</span>
            </a>
        </li>
    </ul>

    <!-- Month Sub-tabs Navigation Bar -->
    <div class="month-subtabs-box d-flex align-items-center gap-2 flex-wrap">
        <span class="small fw-bold text-muted d-flex align-items-center gap-1">
            <i class="fas fa-calendar-alt text-warning"></i> งวดเบิกจ่าย:
        </span>
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <!-- รวมทั้งปี -->
            <a href="{{ route('backoffice.warehouse.index', array_merge(request()->except('month_id'), ['month_id' => 'all'])) }}"
               class="month-chip {{ $month_id === 'all' ? 'active' : '' }}">
                <i class="fas fa-calendar-check me-1"></i> รวมทั้งปี
            </a>
            <!-- 12 Months -->
            @foreach($fiscalMonthsList as $fm)
                @php $isActive = (string)$month_id === (string)$fm['month_id']; @endphp
                <a href="{{ route('backoffice.warehouse.index', array_merge(request()->except('month_id'), ['month_id' => $fm['month_id']])) }}"
                   class="month-chip {{ $isActive ? 'active' : '' }}"
                   title="{{ $fm['name_full'] }} {{ $fm['cal_year'] }}">
                    {{ $fm['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- MAIN BODY CONTENT -->
    @if($tab === 'overview')
        {{-- ========================================================================= --}}
        {{-- OVERVIEW TAB: CONSOLIDATED 5 WAREHOUSES EXECUTIVE DASHBOARD --}}
        {{-- ========================================================================= --}}
        
        <!-- Consolidated 5 Main Warehouses Status (รับเข้า • เบิกจ่าย • คงคลัง) -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fas fa-table-columns text-primary"></i>
                    <span>สรุปเปรียบเทียบ 5 คลังหลัก (รับเข้า • เบิกจ่าย • คงคลัง)</span>
                </h6>
                <div class="text-muted small mt-1 d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.77rem;">
                    <span>
                        <i class="fas fa-calendar-alt text-warning me-1"></i>
                        @if($isMonthlyView ?? false)
                            <span>ประจำเดือน: <strong class="text-dark">{{ $selectedMonthName }} {{ $selectedCalYear }}</strong></span>
                        @else
                            <span>ทั้งปีงบประมาณ: <strong class="text-dark">{{ $year_id }}</strong></span>
                        @endif
                    </span>
                    @if($sub_warehouse_id !== 'all')
                        <span class="mx-1 text-muted">|</span>
                        <span class="badge bg-light text-primary border rounded-pill px-2.5 py-0.5">
                            <i class="fas fa-hospital me-1"></i>คลังย่อย: {{ $selectedSubWhName }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-white text-secondary border shadow-xs px-3 py-2 rounded-pill fw-normal" style="font-size: 0.8rem;">
                    รวมทั้ง รพ. ({{ ($isMonthlyView ?? false) ? $selectedMonthName : 'ทั้งปี ' . $year_id }}): 
                    <span class="ms-1 text-primary fw-bold"><i class="fas fa-file-arrow-down me-1"></i>รับเข้า {{ number_format($totalHospitalReceive, 2) }} ฿</span>
                    <span class="mx-2 text-muted">|</span>
                    <span class="text-warning fw-bold"><i class="fas fa-file-arrow-up me-1"></i>เบิกจ่าย {{ number_format($totalHospitalDisbursement, 2) }} ฿</span>
                    <span class="mx-2 text-muted">|</span>
                    <span class="text-success fw-bold"><i class="fas fa-boxes-stacked me-1"></i>คงคลัง {{ number_format($totalHospitalStock, 2) }} ฿</span>
                </span>
            </div>
        </div>

        <div class="row g-3 mb-4">
            @foreach($warehouseSummaries as $k => $ws)
                <div class="col-xl col-md-6">
                    <div class="card wh-kpi-card p-3 h-100 border-top border-4 shadow-sm rounded-4 d-flex flex-column" style="border-top-color: {{ $ws['config']['color'] }} !important; background: #ffffff;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill fw-bold" style="background-color: {{ $ws['config']['color'] }}15; color: {{ $ws['config']['color'] }}; font-size: 0.8rem; padding: 6px 10px;">
                                <i class="fas {{ $ws['config']['icon'] }} me-1"></i> {{ $ws['config']['name'] }}
                            </span>
                            <span class="text-muted small" style="font-size: 0.72rem;">{{ number_format($ws['items_count']) }} รายการ</span>
                        </div>

                        <!-- Card Subtitle: Month / All Year & Sub-Warehouse -->
                        <div class="d-flex align-items-center justify-content-between text-muted mb-2.5 px-0.5" style="font-size: 0.73rem;">
                            <span>
                                <i class="fas fa-calendar-alt me-1 text-secondary"></i>
                                @if($isMonthlyView ?? false)
                                    <strong class="text-dark">{{ $selectedMonthName }} {{ $selectedCalYear }}</strong>
                                @else
                                    <strong class="text-dark">ทั้งปี {{ $year_id }}</strong>
                                @endif
                            </span>
                            @if($sub_warehouse_id !== 'all')
                                <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5 text-truncate" style="max-width: 140px;" title="{{ $selectedSubWhName }}">
                                    <i class="fas fa-hospital me-0.5"></i>{{ Str::limit($selectedSubWhName, 15) }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex flex-column gap-2 mb-3">
                            <!-- รับเข้า -->
                            <div class="p-2 rounded-3 bg-light d-flex justify-content-between align-items-center border border-light">
                                <span class="text-muted small fw-medium" style="font-size: 0.78rem;">
                                    <i class="fas fa-file-arrow-down text-primary me-1"></i> รับเข้า{{ ($isMonthlyView ?? false) ? ' (' . ($thaiMonthsShort[(int)$month_id] ?? $selectedMonthName) . ')' : ' (ทั้งปี)' }}
                                </span>
                                <span class="fw-bold text-dark" style="font-size: 0.86rem;">
                                    {{ number_format($ws['receive_val'], 2) }} <span class="fw-normal text-muted" style="font-size: 0.7rem;">฿</span>
                                </span>
                            </div>

                            <!-- เบิกจ่าย -->
                            <div class="p-2 rounded-3 bg-light d-flex justify-content-between align-items-center border border-light">
                                <span class="text-muted small fw-medium" style="font-size: 0.78rem;">
                                    <i class="fas fa-file-arrow-up text-warning me-1"></i> เบิกจ่าย{{ ($isMonthlyView ?? false) ? ' (' . ($thaiMonthsShort[(int)$month_id] ?? $selectedMonthName) . ')' : ' (ทั้งปี)' }}
                                </span>
                                <span class="fw-bold text-dark" style="font-size: 0.86rem;">
                                    {{ number_format($ws['disbursement_val'], 2) }} <span class="fw-normal text-muted" style="font-size: 0.7rem;">฿</span>
                                </span>
                            </div>

                            <!-- คงคลัง -->
                            <div class="p-2 rounded-3 d-flex justify-content-between align-items-center" style="background-color: #ecfdf5; border: 1px solid #a7f3d0;">
                                <span class="small fw-bold" style="color: #065f46; font-size: 0.78rem;">
                                    <i class="fas fa-boxes-stacked me-1 text-success"></i> คงคลัง{{ ($isMonthlyView ?? false) ? ' (สิ้นเดือน ' . ($thaiMonthsShort[(int)$month_id] ?? $selectedMonthName) . ')' : ' (รวม)' }}
                                </span>
                                <span class="fw-bold" style="color: #047857; font-size: 0.88rem;">
                                    {{ number_format($ws['stock_val'], 2) }} <span class="fw-normal" style="font-size: 0.7rem;">฿</span>
                                </span>
                            </div>
                        </div>

                        <div class="mt-auto pt-1">
                            <a href="{{ route('backoffice.warehouse.index', array_merge(request()->except('tab'), ['tab' => $k])) }}" 
                               class="btn btn-sm btn-light w-100 rounded-pill py-1 fw-bold text-secondary border hover-shadow" style="font-size: 0.75rem;">
                                ดูรายละเอียดคลัง <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- MOPH Standards & Benchmarks Indicator Ribbon -->
        <div class="card border-0 shadow-xs rounded-4 bg-white p-3 mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="wh-icon-box bg-success bg-opacity-15 text-success rounded-circle" style="width: 42px; height: 42px; font-size: 1.2rem;">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div>
                        <h6 class="m-0 fw-bold text-dark">เกณฑ์มาตรฐานบริหารคลัง สธ. (MOPH & Planfin Indicators)</h6>
                        <span class="text-muted small">ประเมินประสิทธิภาพสต็อกสินค้าคงคลังและอัตราการหมุนเวียน</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="benchmark-box">
                        <span class="text-muted small d-block">อัตราหมุนเวียนคลัง (ITR):</span>
                        <strong class="text-success fs-6">{{ $hospitalItr }} รอบ/ปี</strong> 
                        <span class="badge bg-success rounded-pill ms-1" style="font-size: 0.68rem;">เกณฑ์ ≥ 6.0 ✅</span>
                    </div>
                    <div class="benchmark-box">
                        <span class="text-muted small d-block">ระยะเวลาสำรองคงคลัง (MOS):</span>
                        <strong class="text-primary fs-6">{{ $hospitalMos }} เดือน</strong> 
                        <span class="badge bg-primary rounded-pill ms-1" style="font-size: 0.68rem;">เกณฑ์ 1.5 - 2.0 ด. ✅</span>
                    </div>
                    <div class="benchmark-box">
                        <span class="text-muted small d-block">รายการในบัญชีสิ่งของ (Catalog):</span>
                        <strong class="text-dark fs-6">{{ number_format($totalHospitalCatalogItems) }} ชนิด</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Charts (Daily Trend in Month View, 12-Month Trend in Year View & Top Sub-warehouses) -->
        <div class="row g-4 mb-4">
            <!-- Trend Chart -->
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                @if($isMonthlyView ?? false)
                                    <i class="fas fa-chart-line text-warning"></i>
                                    <span>แนวโน้มการเบิกจ่ายรายวัน (Daily Trend) ประจำเดือน {{ $selectedMonthName }} {{ $selectedCalYear }}</span>
                                @else
                                    <i class="fas fa-chart-line text-warning"></i>
                                    <span>แนวโน้มมูลค่าการเบิกจ่าย 12 เดือน ปีงบประมาณ {{ $year_id }}</span>
                                @endif
                            </h6>
                            <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                @if($isMonthlyView ?? false)
                                    เปรียบเทียบการเบิกจ่ายรายวันของ 5 คลังหลัก (ว.1 – ว.{{ count($dailyTrend ?? []) }})
                                @else
                                    เปรียบเทียบการเบิกจ่ายรายเดือนตลอดปีงบประมาณของ 5 คลังหลัก (ต.ค. - ก.ย.)
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-light text-muted border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                            @if($isMonthlyView ?? false)
                                <i class="fas fa-calendar-day me-1 text-primary"></i> รายวัน (Daily) 5 คลัง
                            @else
                                <i class="fas fa-calendar me-1 text-warning"></i> เปรียบเทียบ 5 คลัง (12 เดือน)
                            @endif
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div id="overview_trend_chart" style="min-height: 330px;"></div>
                    </div>
                </div>
            </div>

            <!-- Top Sub-warehouses (Wards/Departments) -->
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-ranking-star text-warning"></i>
                            <span>คลังย่อยที่เบิกจ่ายสูงสุด {{ ($isMonthlyView ?? false) ? 'ประจำเดือน' . $selectedMonthName : 'ทั้งปีงบประมาณ ' . $year_id }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                                <thead>
                                    <tr class="text-muted text-uppercase" style="font-size: 0.74rem;">
                                        <th>คลังย่อย (แผนก)</th>
                                        <th class="text-center">ใบเบิก</th>
                                        <th class="text-end">มูลค่ารวม (บาท)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topSubWhStats as $idx => $ts)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 240px;" title="{{ $ts['name'] }}">
                                                    <span class="badge bg-light text-dark border me-1">{{ $idx + 1 }}</span>
                                                    {{ $ts['name'] }}
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-muted border px-2">{{ $ts['reqs'] }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-primary">
                                                {{ number_format($ts['val'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">ไม่พบข้อมูลคลังย่อย</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($tab === 'rpst')
        {{-- ========================================================================= --}}
        {{-- RPST TAB: SUB-DISTRICT HEALTH CENTER NETWORK (CUP HUB) --}}
        {{-- ========================================================================= --}}
        
        <!-- Multi-Warehouse Monthly Trend Chart for RPST (กราฟรายเดือน แยกเส้นแต่ละคลัง พร้อมแถบยอดรวมในตัว) -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-chart-line text-info"></i>
                        @if($isMonthlyView ?? false)
                            <span>แนวโน้มการเบิกจ่าย รพ.สต. รายวัน แยกตาม 5 คลังหลัก - เดือน{{ $selectedMonthName }} {{ $selectedCalYear }}</span>
                        @else
                            <span>แนวโน้มมูลค่าการเบิกจ่าย รพ.สต. รายเดือน แยกตาม 5 คลังหลัก (12 เดือน ปีงบประมาณ {{ $year_id }})</span>
                        @endif
                    </h6>
                    <div class="text-muted small mt-1 d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.76rem;">
                        <span>
                            <i class="fas fa-hospital text-info me-1"></i>เครือข่าย: <strong class="text-dark">{{ $selectedRpstName }}</strong>
                        </span>
                        <span class="text-muted">|</span>
                        <span>คลิกที่ชื่อคลังใน Legend เพื่อเปิด/ปิดเส้นได้</span>
                    </div>
                </div>
                
                <!-- ยอดรวมเบิกจ่าย และจำนวนใบเบิก ย้ายมาไว้แทนที่ badge เดิมตามคำสั่ง (รูป 2 แทนรูป 3) -->
                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                    <div class="d-flex align-items-center gap-3 px-3.5 py-2 rounded-3 bg-light border shadow-2xs">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="wh-icon-box bg-info bg-opacity-10 text-info rounded-3" style="width: 36px; height: 36px; font-size: 1.1rem; flex-shrink: 0;">
                                <i class="fas fa-coins text-warning"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium" style="font-size: 0.72rem; line-height: 1.2;">
                                    มูลค่าเบิกจ่ายรวม ({{ ($isMonthlyView ?? false) ? $selectedMonthName : 'ทั้งปี ' . $year_id }})
                                </div>
                                <div class="fw-bold text-primary font-monospace" style="font-size: 1.12rem; line-height: 1.2;">
                                    {{ number_format($totalDisbursement, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                                </div>
                            </div>
                        </div>
                        <div class="vr my-1 text-muted" style="height: 28px; opacity: 0.25;"></div>
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="wh-icon-box bg-primary bg-opacity-10 text-primary rounded-3" style="width: 36px; height: 36px; font-size: 1.1rem; flex-shrink: 0;">
                                <i class="fas fa-file-invoice text-info"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-medium" style="font-size: 0.72rem; line-height: 1.2;">จำนวนใบขอเบิก</div>
                                <div class="fw-bold text-dark font-monospace" style="font-size: 1.12rem; line-height: 1.2;">
                                    {{ number_format($totalReqCount) }} <span class="fs-6 fw-normal text-muted">ใบ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- 5 Warehouses Contribution Summary Pills -->
                <div class="row g-2 mb-3">
                    @foreach($warehouseContributions as $wk => $wc)
                        @php
                            $pct = $totalDisbursement > 0 ? round(($wc['val'] / $totalDisbursement) * 100, 1) : 0;
                        @endphp
                        <div class="col-xl col-md-4 col-sm-6">
                            <div class="p-2.5 rounded-3 border d-flex flex-column gap-1" style="background-color: {{ $wc['color'] }}08; border-color: {{ $wc['color'] }}25 !important;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="small fw-bold d-flex align-items-center gap-1.5" style="color: {{ $wc['color'] }}; font-size: 0.76rem;">
                                        <i class="fas {{ $wc['icon'] }}"></i>
                                        <span class="text-truncate" style="max-width: 140px;">{{ $wc['name'] }}</span>
                                    </span>
                                    <span class="badge rounded-pill px-1.5 py-0.5 font-monospace" style="font-size: 0.68rem; background: {{ $wc['color'] }}20; color: {{ $wc['color'] }};">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <div class="fw-bold font-monospace text-dark" style="font-size: 0.92rem;">
                                    {{ number_format($wc['val'], 2) }} <span class="fw-normal text-muted" style="font-size: 0.7rem;">฿</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- ApexChart Element for 5 Separate Lines -->
                <div id="rpst_warehouse_trend_chart" style="min-height: 330px;"></div>
            </div>
        </div>

        <!-- Section 1: Matrix Table (13 รพ.สต. x 5 Main Warehouses) -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-table-cells text-primary"></i>
                        <span>ตารางเปรียบเทียบการเบิกจ่าย 13 รพ.สต. x 5 คลังหลัก</span>
                    </h6>
                    <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                        แสดงยอดรวมที่แต่ละ รพ.สต. เบิกจากคลังพัสดุและคลังเวชภัณฑ์ทั้ง 5 คลัง ในช่วงเวลาที่เลือก
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                        <i class="fas fa-hospital me-1 text-info"></i> {{ count($matrix) }} หน่วยบริการ
                    </span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="rpst_matrix_table" style="font-size: 0.83rem;">
                        <thead class="bg-light">
                            <tr class="text-muted" style="font-size: 0.75rem;">
                                <th class="text-center" style="width: 45px;">#</th>
                                <th style="width: 85px;">รหัส</th>
                                <th>ชื่อ รพ.สต. / หน่วยบริการ</th>
                                <th class="text-end" style="color: #d97706; width: 110px;"><i class="fas fa-boxes-stacked me-1"></i>คลังวัสดุ</th>
                                <th class="text-end" style="color: #0891b2; width: 110px;"><i class="fas fa-tooth me-1"></i>ทันตกรรม</th>
                                <th class="text-end" style="color: #7c3aed; width: 110px;"><i class="fas fa-microscope me-1"></i>คลัง LAB</th>
                                <th class="text-end" style="color: #059669; width: 110px;"><i class="fas fa-person-walking me-1"></i>กายภาพ</th>
                                <th class="text-end" style="color: #db2777; width: 120px;"><i class="fas fa-stethoscope me-1"></i>วัสดุการแพทย์</th>
                                <th class="text-end fw-bold text-dark" style="width: 135px;"><i class="fas fa-coins me-1 text-warning"></i>รวม 5 คลัง (บาท)</th>
                                <th class="text-center" style="width: 95px;">สัดส่วน</th>
                                <th class="text-center" style="width: 75px;">ใบเบิก</th>
                                <th class="text-center" style="width: 90px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matrix as $idx => $r)
                                @php
                                    $rankBadge = match($idx) {
                                        0 => 'bg-warning text-dark border border-warning',
                                        1 => 'bg-secondary bg-opacity-25 text-dark border',
                                        2 => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                        default => 'bg-light text-muted border',
                                    };
                                    $isFiltered = ($rpst_id !== 'all' && (string)$rpst_id === (string)$r['id']);
                                @endphp
                                <tr class="{{ $isFiltered ? 'table-primary bg-opacity-10' : '' }}">
                                    <td class="text-center">
                                        <span class="badge rounded-circle p-1 {{ $rankBadge }}" style="width: 22px; height: 22px; line-height: 14px; font-size: 0.7rem;">
                                            {{ $idx + 1 }}
                                        </span>
                                    </td>
                                    <td class="font-monospace text-muted small">{{ $r['code'] ?: '-' }}</td>
                                    <td>
                                        <div class="fw-bold text-dark d-flex align-items-center gap-1.5">
                                            <i class="fas fa-hospital me-1 text-muted" style="font-size: 0.8rem;"></i>
                                            <span>{{ $r['name'] }}</span>
                                            @if($idx === 0 && $r['total'] > 0)
                                                <span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50 rounded-pill px-1.5 py-0.5" style="font-size: 0.65rem;">
                                                    <i class="fas fa-crown text-warning me-0.5"></i>สูงสุด
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end font-monospace text-muted">{{ number_format($r['material'], 2) }}</td>
                                    <td class="text-end font-monospace text-muted">{{ number_format($r['dental'], 2) }}</td>
                                    <td class="text-end font-monospace text-muted">{{ number_format($r['medtech'], 2) }}</td>
                                    <td class="text-end font-monospace text-muted">{{ number_format($r['physical'], 2) }}</td>
                                    <td class="text-end font-monospace text-muted">{{ number_format($r['medical'], 2) }}</td>
                                    <td class="text-end font-monospace fw-bold text-primary fs-6">
                                        {{ number_format($r['total'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <span class="font-monospace small fw-bold text-dark" style="font-size: 0.72rem;">{{ $r['share_pct'] }}%</span>
                                            <div class="progress w-100 mt-1" style="height: 4px;">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, $r['share_pct'] * 2) }}%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace">{{ $r['req_count'] }} ใบ</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 shadow-xs" 
                                                style="font-size: 0.74rem;" 
                                                onclick="viewRpstDetail({{ $r['id'] }}, '{{ addslashes($r['name']) }}')"
                                                title="คลิกเพื่อดูรายละเอียดรายการสิ่งของและใบเบิกของ {{ $r['name'] }}">
                                            <i class="fas fa-file-invoice me-1"></i>ดูรายละเอียด
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end text-dark">รวมทั้งเครือข่าย รพ.สต.:</td>
                                <td class="text-end font-monospace text-dark">{{ number_format(array_sum(array_column($matrix, 'material')), 2) }}</td>
                                <td class="text-end font-monospace text-dark">{{ number_format(array_sum(array_column($matrix, 'dental')), 2) }}</td>
                                <td class="text-end font-monospace text-dark">{{ number_format(array_sum(array_column($matrix, 'medtech')), 2) }}</td>
                                <td class="text-end font-monospace text-dark">{{ number_format(array_sum(array_column($matrix, 'physical')), 2) }}</td>
                                <td class="text-end font-monospace text-dark">{{ number_format(array_sum(array_column($matrix, 'medical')), 2) }}</td>
                                <td class="text-end font-monospace text-primary fs-6">{{ number_format($totalDisbursement, 2) }} ฿</td>
                                <td class="text-center font-monospace">100%</td>
                                <td class="text-center font-monospace text-dark">{{ $totalReqCount }} ใบ</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section 2: Side-by-Side (Top Items vs Requisitions History) -->
        <div class="row g-4 mb-5">
            <!-- Left Column: 15 อันดับรายการที่ รพ.สต. เบิกสูงสุด (แบ่งครึ่ง col-xl-6) -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden d-flex flex-column">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-ranking-star text-warning"></i>
                                <span>15 อันดับรายการที่ รพ.สต. เบิกสูงสุด</span>
                            </h6>
                            <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                สิ่งของ/เวชภัณฑ์ที่ส่งมอบให้ รพ.สต. มากที่สุด
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border px-2.5 py-1" style="font-size: 0.75rem;">
                            <i class="fas fa-boxes-stacked me-1 text-primary"></i> 15 อันดับสูงสุด
                        </span>
                    </div>
                    <div class="card-body p-0 flex-grow-1">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.81rem;">
                                <thead class="bg-light">
                                    <tr class="text-muted" style="font-size: 0.74rem;">
                                        <th class="text-center" style="width: 32px;">#</th>
                                        <th>รายการพัสดุ / เวชภัณฑ์</th>
                                        <th style="width: 105px;">คลังต้นทาง</th>
                                        <th class="text-center" style="width: 75px;">จำนวนเบิก</th>
                                        <th class="text-end" style="width: 95px;">มูลค่า (บาท)</th>
                                        <th class="text-center" style="width: 75px;">ดูรายการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topDisbursedItems as $tIdx => $tItem)
                                        @php
                                            $rankBadge = match($tIdx) {
                                                0 => 'bg-warning text-dark border border-warning',
                                                1 => 'bg-secondary bg-opacity-25 text-dark border',
                                                2 => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                                default => 'bg-light text-muted border',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge rounded-circle p-1 {{ $rankBadge }}" style="width: 20px; height: 20px; line-height: 12px; font-size: 0.68rem;">
                                                    {{ $tIdx + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 180px;" title="{{ $tItem->name }}">
                                                    {{ $tItem->name }}
                                                </div>
                                                <div class="text-muted small font-monospace" style="font-size: 0.7rem;">
                                                    {{ $tItem->code ?: '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill px-2 py-0.5" style="background: {{ $tItem->warehouse_color }}15; color: {{ $tItem->warehouse_color }}; font-size: 0.68rem;">
                                                    {{ $tItem->warehouse_name }}
                                                </span>
                                            </td>
                                            <td class="text-center font-monospace">
                                                <span class="fw-bold text-dark">{{ number_format($tItem->total_qty) }}</span>
                                                <span class="text-muted small ms-0.5">{{ $tItem->unit_name ?: '' }}</span>
                                            </td>
                                            <td class="text-end fw-bold font-monospace text-primary">
                                                {{ number_format($tItem->total_val, 2) }}
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 shadow-xs" 
                                                        style="font-size: 0.72rem;" 
                                                        onclick="viewRpstItemBreakdown('{{ $tItem->warehouse_key ?? 'material' }}', '{{ $tItem->item_id ?? '' }}', '{{ addslashes($tItem->code ?? '') }}', '{{ addslashes($tItem->name ?? '') }}')"
                                                        title="ดูรายชื่อ รพ.สต. ที่เบิก {{ $tItem->name }}">
                                                    <i class="fas fa-hospital-user me-1"></i>ดูรายการ
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">ไม่พบรายการเบิกจ่ายในงวดเวลานี้</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: ประวัติใบขอเบิกของ รพ.สต. (แบ่งครึ่ง col-xl-6) -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden d-flex flex-column">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-file-invoice text-info"></i>
                                <span>ประวัติใบขอเบิกของ รพ.สต.</span>
                            </h6>
                            <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                รายการใบเบิกส่งมอบสิ่งของ/เวชภัณฑ์ให้ รพ.สต. ในเครือข่าย
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                            {{ count($combinedRequisitions) }} ใบเบิก
                        </span>
                    </div>
                    <div class="card-body p-0 flex-grow-1">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="rpst_requisitions_table" style="font-size: 0.81rem;">
                                <thead class="bg-light">
                                    <tr class="text-muted" style="font-size: 0.74rem;">
                                        <th class="text-center" style="width: 32px;">#</th>
                                        <th style="width: 85px;">วันที่เบิก</th>
                                        <th>รพ.สต. ปลายทาง</th>
                                        <th style="width: 110px;">คลังที่เบิก</th>
                                        <th>ผู้ขอเบิก</th>
                                        <th class="text-end" style="width: 90px;">มูลค่า (บาท)</th>
                                        <th class="text-center" style="width: 70px;">สถานะ</th>
                                        <th class="text-center" style="width: 75px;">ดูรายการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($combinedRequisitions as $cIdx => $creq)
                                        @php
                                             $stClass = match($creq->status) {
                                                'Allow', 'Verify', 'Approve' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                                'Disallow', 'Disapprove' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                                default => 'bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50',
                                            };
                                            $stLabel = match($creq->status) {
                                                'Allow' => 'จ่ายแล้ว',
                                                'Verify' => 'ตรวจสอบแล้ว',
                                                'Approve' => 'อนุมัติแล้ว',
                                                'Disallow', 'Disapprove' => 'ไม่อนุมัติ',
                                                default => 'รอดำเนินการ',
                                            };
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted">{{ $cIdx + 1 }}</td>
                                            <td class="text-muted small">
                                                {{ $creq->request_date ? substr($creq->request_date, 0, 10) : '-' }}
                                                <span class="d-none">{{ $creq->code }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 140px;" title="{{ $creq->smallhos_name }}">
                                                    {{ $creq->smallhos_name }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill px-2 py-0.5" style="background: {{ $creq->warehouse_color ?? '#64748b' }}15; color: {{ $creq->warehouse_color ?? '#64748b' }}; font-size: 0.68rem;">
                                                    {{ $creq->warehouse_name }}
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                <div class="text-truncate" style="max-width: 120px;" title="{{ $creq->requester_name }}">
                                                    {{ $creq->requester_name }}
                                                </div>
                                            </td>
                                            <td class="text-end font-monospace fw-bold text-primary">
                                                {{ number_format($creq->total_val, 2) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill px-2 py-0.5 {{ $stClass }}" style="font-size: 0.68rem;">
                                                    {{ $stLabel }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 shadow-xs" 
                                                        style="font-size: 0.72rem;" 
                                                        onclick="viewRequestDetail('{{ $creq->warehouse_key }}', {{ $creq->id }})"
                                                        title="ดูรายการสิ่งของในใบขอเบิก {{ $creq->code }}">
                                                    <i class="fas fa-file-invoice me-1"></i>ดูรายการ
                                                </button>
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

    @else
        {{-- ========================================================================= --}}
        {{-- SPECIFIC WAREHOUSE TAB: MATERIAL / DENTAL / MEDTECH / PHYSICAL / MEDICAL --}}
        {{-- ========================================================================= --}}
        
        <!-- KPI Row for Specific Warehouse: รับเข้า • เบิกจ่าย • คงคลัง • รายการต้องซื้อด่วน (Interactive Mode Switcher) -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Receive Value (รับเข้า) -->
            <div class="col-xl-3 col-md-6">
                <div class="card wh-kpi-card wh-kpi-interactive-card p-3 h-100 border-start border-4 border-primary {{ $view_mode === 'receive' ? 'active-kpi-card active-card-primary' : '' }}" 
                     id="wh_card_receive" 
                     onclick="switchWarehouseView('receive')"
                     title="คลิกเพื่อสลับแสดงข้อมูลการตรวจรับเข้าคลัง">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="w-100 me-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                <div class="text-muted small fw-bold text-uppercase d-flex align-items-center gap-1">
                                    <i class="fas fa-file-arrow-down text-primary"></i>
                                    <span>มูลค่ารับเข้า ({{ ($isMonthlyView ?? false) ? $selectedMonthName : 'ทั้งปี ' . $year_id }})</span>
                                </div>
                                @if($sub_warehouse_id !== 'all')
                                    <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5" style="font-size: 0.68rem;" title="{{ $selectedSubWhName }}">
                                        <i class="fas fa-hospital me-1"></i>{{ Str::limit($selectedSubWhName, 16) }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="fw-bold text-primary mb-0 mt-1">
                                {{ number_format($totalReceive, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1 d-flex align-items-center justify-content-between flex-wrap gap-1" style="font-size: 0.75rem;">
                                <span>รวม {{ number_format($totalReceiveCount) }} ใบตรวจรับ</span>
                                <span class="badge wh-kpi-status-badge {{ $view_mode === 'receive' ? 'bg-primary text-white shadow-xs' : 'bg-light text-muted border' }} px-2 py-0.5 rounded-pill" data-mode="receive" style="font-size: 0.68rem;">
                                    <i class="fas {{ $view_mode === 'receive' ? 'fa-eye' : 'fa-hand-pointer' }} me-1"></i>{{ $view_mode === 'receive' ? 'กำลังแสดง' : 'คลิกดูข้อมูล' }}
                                </span>
                            </div>
                        </div>
                        <div class="wh-icon-box bg-primary bg-opacity-10 text-primary flex-shrink-0">
                            <i class="fas fa-box-archive"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Total Disbursement Value (เบิกจ่าย) -->
            <div class="col-xl-3 col-md-6">
                <div class="card wh-kpi-card wh-kpi-interactive-card p-3 h-100 border-start border-4 border-warning {{ $view_mode === 'overview' ? 'active-kpi-card active-card-warning' : '' }}" 
                     id="wh_card_overview" 
                     onclick="switchWarehouseView('overview')"
                     title="คลิกเพื่อสลับแสดงข้อมูลการเบิกจ่ายและใบเบิก">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="w-100 me-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                <div class="text-muted small fw-bold text-uppercase d-flex align-items-center gap-1">
                                    <i class="fas fa-dolly text-warning"></i>
                                    <span>มูลค่าเบิกจ่าย ({{ ($isMonthlyView ?? false) ? $selectedMonthName : 'ทั้งปี ' . $year_id }})</span>
                                </div>
                                @if($sub_warehouse_id !== 'all')
                                    <span class="badge bg-light text-warning border rounded-pill px-2 py-0.5" style="font-size: 0.68rem;" title="{{ $selectedSubWhName }}">
                                        <i class="fas fa-hospital me-1"></i>{{ Str::limit($selectedSubWhName, 16) }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="fw-bold text-warning mb-0 mt-1">
                                {{ number_format($totalDisbursement, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1 d-flex align-items-center justify-content-between flex-wrap gap-1" style="font-size: 0.75rem;">
                                <span>รวม {{ number_format($totalReqCount) }} ใบเบิก</span>
                                <span class="badge wh-kpi-status-badge {{ $view_mode === 'overview' ? 'bg-warning text-dark shadow-xs' : 'bg-light text-muted border' }} px-2 py-0.5 rounded-pill" data-mode="overview" style="font-size: 0.68rem;">
                                    <i class="fas {{ $view_mode === 'overview' ? 'fa-eye' : 'fa-hand-pointer' }} me-1"></i>{{ $view_mode === 'overview' ? 'กำลังแสดง' : 'คลิกดูข้อมูล' }}
                                </span>
                            </div>
                        </div>
                        <div class="wh-icon-box bg-warning bg-opacity-10 text-warning flex-shrink-0">
                            <i class="fas fa-boxes-packing"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Current Stock Valuation (คงคลัง) -->
            <div class="col-xl-3 col-md-6">
                <div class="card wh-kpi-card wh-kpi-interactive-card p-3 h-100 border-start border-4 border-success {{ $view_mode === 'stock' ? 'active-kpi-card active-card-success' : '' }}" 
                     id="wh_card_stock" 
                     onclick="switchWarehouseView('stock')"
                     title="คลิกเพื่อสลับแสดงสต็อกจริง 2 ระดับ">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="w-100 me-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                <div class="text-muted small fw-bold text-uppercase d-flex align-items-center gap-1">
                                    <i class="fas fa-warehouse text-success"></i>
                                    <span>มูลค่าคงคลัง ({{ ($isMonthlyView ?? false) ? 'สิ้นเดือน ' . $selectedMonthName : 'ทั้งปี ' . $year_id }})</span>
                                </div>
                                @if($sub_warehouse_id !== 'all')
                                    <span class="badge bg-light text-success border rounded-pill px-2 py-0.5" style="font-size: 0.68rem;" title="{{ $selectedSubWhName }}">
                                        <i class="fas fa-hospital me-1"></i>{{ Str::limit($selectedSubWhName, 16) }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="fw-bold text-success mb-0 mt-1">
                                {{ number_format($totalStockVal, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1 d-flex align-items-center justify-content-between flex-wrap gap-1" style="font-size: 0.75rem;">
                                <span>{{ number_format($catalogItemsCount) }} SKU ({{ $avgMos }} ด.)</span>
                                <span class="badge wh-kpi-status-badge {{ $view_mode === 'stock' ? 'bg-success text-white shadow-xs' : 'bg-light text-muted border' }} px-2 py-0.5 rounded-pill" data-mode="stock" style="font-size: 0.68rem;">
                                    <i class="fas {{ $view_mode === 'stock' ? 'fa-eye' : 'fa-hand-pointer' }} me-1"></i>{{ $view_mode === 'stock' ? 'กำลังแสดง' : 'คลิกดูข้อมูล' }}
                                </span>
                            </div>
                        </div>
                        <div class="wh-icon-box bg-success bg-opacity-10 text-success flex-shrink-0">
                            <i class="fas fa-cubes"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Critical Reorder Needed (รายการต้องซื้อด่วน) -->
            <div class="col-xl-3 col-md-6">
                <div class="card wh-kpi-card wh-kpi-interactive-card p-3 h-100 border-start border-4 border-danger {{ $view_mode === 'planner' ? 'active-kpi-card active-card-danger' : '' }}" 
                     id="wh_card_planner" 
                     onclick="switchWarehouseView('planner')"
                     title="คลิกเพื่อสลับแสดงข้อเสนอแนะแผนจัดซื้อ">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="w-100 me-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                                <div class="text-muted small fw-bold text-uppercase d-flex align-items-center gap-1">
                                    <i class="fas fa-triangle-exclamation text-danger"></i>
                                    <span class="text-danger">รายการต้องซื้อด่วน ({{ ($isMonthlyView ?? false) ? $selectedMonthName : 'ทั้งปี ' . $year_id }})</span>
                                </div>
                                @if($sub_warehouse_id !== 'all')
                                    <span class="badge bg-light text-danger border rounded-pill px-2 py-0.5" style="font-size: 0.68rem;" title="{{ $selectedSubWhName }}">
                                        <i class="fas fa-hospital me-1"></i>{{ Str::limit($selectedSubWhName, 16) }}
                                    </span>
                                @endif
                            </div>
                            <h3 class="fw-bold text-danger mb-0 mt-1">
                                {{ number_format(count($reorderItems)) }} <span class="fs-6 fw-normal text-muted">รายการ</span>
                            </h3>
                            <div class="text-muted small mt-1 d-flex align-items-center justify-content-between flex-wrap gap-1" style="font-size: 0.75rem;">
                                <span>งบ: {{ number_format($totalEstimatedBudget, 2) }} ฿</span>
                                <span class="badge wh-kpi-status-badge {{ $view_mode === 'planner' ? 'bg-danger text-white shadow-xs' : 'bg-light text-muted border' }} px-2 py-0.5 rounded-pill" data-mode="planner" style="font-size: 0.68rem;">
                                    <i class="fas {{ $view_mode === 'planner' ? 'fa-eye' : 'fa-hand-pointer' }} me-1"></i>{{ $view_mode === 'planner' ? 'กำลังแสดง' : 'คลิกดูข้อมูล' }}
                                </span>
                            </div>
                        </div>
                        <div class="wh-icon-box bg-danger bg-opacity-10 text-danger flex-shrink-0">
                            <i class="fas fa-cart-shopping"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- VIEW 1: GOODS RECEIPT TRACKING (ตรวจรับเข้าคลัง) -->
        <!-- ========================================================================= -->
        <div class="wh-view-section" id="view_section_receive" style="{{ $view_mode === 'receive' ? '' : 'display: none;' }}">
            <div class="row g-4 mb-4">
                <!-- Trend Chart: Receive vs Disburse -->
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-file-arrow-down text-primary"></i>
                                    @if($isMonthlyView ?? false)
                                        <span>แนวโน้มการตรวจรับเข้าคลัง รายวัน - เดือน{{ $selectedMonthName }} {{ $selectedCalYear }}</span>
                                    @else
                                        <span>แนวโน้มมูลค่าตรวจรับเข้าคลัง 12 เดือน - {{ $config['full_name'] }}</span>
                                    @endif
                                </h6>
                                <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                    @if($isMonthlyView ?? false)
                                        มูลค่าตรวจรับพัสดุ/เวชภัณฑ์เข้าคลังในแต่ละวันของเดือน{{ $selectedMonthName }} เปรียบเทียบกับยอดเบิกจ่าย
                                    @else
                                        เปรียบเทียบมูลค่าตรวจรับเข้าคลังกับมูลค่าการเบิกจ่ายรายเดือนตลอดปีงบประมาณ {{ $year_id }}
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 small">
                                    <i class="fas fa-circle me-1" style="font-size: 7px;"></i>รับเข้าคลัง
                                </span>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 small">
                                    <i class="fas fa-circle me-1" style="font-size: 7px;"></i>เบิกจ่าย
                                </span>
                                <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                                    @if($isMonthlyView ?? false)
                                        <i class="fas fa-calendar-day me-1 text-primary"></i> รายวัน (Daily)
                                    @else
                                        <i class="fas fa-calendar me-1 text-primary"></i> 12 เดือน (ต.ค. - ก.ย.)
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div id="warehouse_receive_chart" style="min-height: 290px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Receive Summary Highlights -->
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-clipboard-check text-primary"></i>
                                <span>สรุปภาพรวมการตรวจรับ</span>
                            </h6>
                            <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                ข้อมูลสะสมงวด{{ ($isMonthlyView ?? false) ? 'เดือน' . $selectedMonthName : 'ปี ' . $year_id }}
                            </div>
                        </div>
                        <div class="card-body p-4 d-flex flex-column justify-content-between gap-3">
                            <div class="p-3 rounded-3 border" style="background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase">มูลค่าตรวจรับสะสมทั้งหมด</div>
                                        <h3 class="fw-bold text-primary mb-0 mt-1">
                                            {{ number_format($totalReceive, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                                        </h3>
                                    </div>
                                    <div class="wh-icon-box bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded-3 border" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase">จำนวนใบตรวจรับ</div>
                                        <h4 class="fw-bold text-dark mb-0 mt-1">
                                            {{ number_format($totalReceiveCount) }} <span class="fs-6 fw-normal text-muted">ฉบับ</span>
                                        </h4>
                                    </div>
                                    <div class="wh-icon-box bg-light text-secondary border">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded-3 border bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase">มูลค่าเฉลี่ยต่อใบตรวจรับ</div>
                                        <h5 class="fw-bold text-dark mb-0 mt-1">
                                            {{ $totalReceiveCount > 0 ? number_format($totalReceive / $totalReceiveCount, 2) : '0.00' }} <span class="fs-6 fw-normal text-muted">บาท/ฉบับ</span>
                                        </h5>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="fas fa-circle-check me-1"></i>เรียบร้อย
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Goods Receipts Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-5 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-file-arrow-down text-primary"></i>
                            <span>ประวัติและรายการใบตรวจรับเข้าคลัง (Goods Receipts)</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-0.5 rounded-pill small">
                                {{ number_format(count($receives)) }} ฉบับ
                            </span>
                        </h6>
                        <div class="text-muted small mt-1">
                            คลิกปุ่ม <strong>"ดูรายการในใบรับ"</strong> เพื่อตรวจสอบ Lot No., วันหมดอายุ (EXP) และราคาทุกรายการ
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">มูลค่าตรวจรับรวม:</span>
                        <span class="fw-bold text-primary font-monospace fs-6">{{ number_format($totalReceive, 2) }} บาท</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="warehouse_receives_table" style="font-size: 0.83rem;">
                            <thead class="bg-light">
                                <tr class="text-muted" style="font-size: 0.75rem;">
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th style="width: 130px;">เลขที่ตรวจรับ</th>
                                    <th style="width: 110px;">วันที่รับ</th>
                                    <th style="width: 140px;">เลขที่ PO / บิล</th>
                                    <th>บริษัท / ผู้จัดจำหน่าย</th>
                                    <th>ผู้ตรวจรับ</th>
                                    <th class="text-center" style="width: 90px;">จำนวนชนิด</th>
                                    <th class="text-end" style="width: 130px;">มูลค่า (บาท)</th>
                                    <th class="text-center" style="width: 120px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($receives as $idx => $rec)
                                    <tr>
                                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                        <td class="font-monospace fw-bold text-primary">{{ $rec->code ?: '-' }}</td>
                                        <td class="text-muted small">{{ $rec->receive_date ? substr($rec->receive_date, 0, 10) : '-' }}</td>
                                        <td class="font-monospace small text-muted">{{ $rec->po_code ?? ($rec->invoice_code ?? '-') }}</td>
                                        <td>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 250px;" title="{{ $rec->vendor_name }}">
                                                {{ $rec->vendor_name ?: '-' }}
                                            </div>
                                        </td>
                                        <td class="text-muted small">{{ $rec->receiver_name ?: '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2">{{ number_format($rec->item_count) }}</span>
                                        </td>
                                        <td class="text-end fw-bold font-monospace text-primary">
                                            {{ number_format($rec->total_value, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 shadow-xs" 
                                                    style="font-size: 0.74rem;" 
                                                    onclick="viewReceiveItems('{{ $config['type'] }}', {{ $rec->id }})">
                                                <i class="fas fa-boxes-stacked me-1"></i>ดูรายการ
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- VIEW 2: REQUISITION TRACKING & CHARTS (เบิกจ่าย) -->
        <!-- ========================================================================= -->
        <div class="wh-view-section" id="view_section_overview" style="{{ $view_mode === 'overview' ? '' : 'display: none;' }}">
            <div class="row g-4 mb-4">
                <!-- Trend Chart: 12-Month if all year, Daily Trend if specific month -->
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-chart-line" style="color: {{ $config['color'] }};"></i>
                                    @if($isMonthlyView ?? false)
                                        <span>แนวโน้มรับเข้า vs เบิกจ่าย รายวัน - เดือน{{ $selectedMonthName }} {{ $selectedCalYear }}</span>
                                    @else
                                        <span>แนวโน้มมูลค่ารับเข้า vs เบิกจ่าย 12 เดือน - {{ $config['full_name'] }}</span>
                                    @endif
                                </h6>
                                <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                    @if($isMonthlyView ?? false)
                                        เปรียบเทียบมูลค่ารับเข้าคลังกับมูลค่าการเบิกจ่ายในแต่ละวันของเดือน{{ $selectedMonthName }}
                                    @else
                                        เปรียบเทียบมูลค่ารับเข้าคลังกับมูลค่าการเบิกจ่ายรายเดือนตลอดปีงบประมาณ {{ $year_id }}
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 small">
                                    <i class="fas fa-circle me-1" style="font-size: 7px;"></i>รับเข้า
                                </span>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 small">
                                    <i class="fas fa-circle me-1" style="font-size: 7px;"></i>เบิกจ่าย
                                </span>
                                <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                                    @if($isMonthlyView ?? false)
                                        <i class="fas fa-calendar-day me-1 text-primary"></i> รายวัน (Daily)
                                    @else
                                        <i class="fas fa-calendar me-1 text-warning"></i> 12 เดือน (ต.ค. - ก.ย.)
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div id="warehouse_single_trend_chart" style="min-height: 280px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Key Insights / Highlights of the period -->
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden d-flex flex-column">
                        <div class="card-header bg-white py-3 px-4 border-bottom">
                            <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="fas fa-chart-pie" style="color: {{ $config['color'] }};"></i>
                                <span>ไฮไลต์{{ ($isMonthlyView ?? false) ? 'ประจำเดือน' . $selectedMonthName : 'ภาพรวมทั้งปี ' . $year_id }}</span>
                            </h6>
                            <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">สรุปพฤติกรรมและการกระจายตัวของการเบิก</div>
                        </div>
                        <div class="card-body p-3 d-flex flex-column justify-content-around gap-2">
                            <div class="p-3 rounded-3 bg-light border d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small d-block" style="font-size: 0.75rem;">ยอดเบิกจ่ายในงวดนี้</span>
                                    <h5 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($totalDisbursement, 2) }} <span class="fs-6 text-muted fw-normal">บาท</span></h5>
                                </div>
                                <div class="badge bg-white text-dark border shadow-xs px-2.5 py-1.5 font-monospace">
                                    {{ number_format($totalReqCount) }} ใบเบิก
                                </div>
                            </div>

                            @php
                                $topWard1 = $topSubWhStats[0] ?? null;
                                $peakDay = null;
                                if (!empty($dailyTrend)) {
                                    $peakItem = collect($dailyTrend)->sortByDesc('val')->first();
                                    if ($peakItem && $peakItem['val'] > 0) {
                                        $peakDay = $peakItem;
                                    }
                                }
                            @endphp

                            @if($isMonthlyView ?? false)
                                <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-primary small fw-bold d-block" style="font-size: 0.75rem;"><i class="fas fa-fire me-1 text-danger"></i>วันที่มียอดเบิกสูงสุด (Peak Day)</span>
                                        <h6 class="fw-bold mb-0 text-dark mt-0.5">
                                            @if($peakDay)
                                                วันที่ {{ $peakDay['day'] }} {{ $selectedMonthName }} ({{ number_format($peakDay['val'], 2) }} ฿)
                                            @else
                                                -
                                            @endif
                                        </h6>
                                    </div>
                                    <i class="fas fa-calendar-check text-primary fs-3 opacity-50"></i>
                                </div>
                            @else
                                <div class="p-3 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25 d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-success small fw-bold d-block" style="font-size: 0.75rem;"><i class="fas fa-calculator me-1"></i>เฉลี่ยเบิกจ่ายต่อเดือน</span>
                                        <h6 class="fw-bold mb-0 text-dark mt-0.5 font-monospace">
                                            {{ number_format($totalDisbursement / 12, 2) }} บาท/เดือน
                                        </h6>
                                    </div>
                                    <i class="fas fa-chart-simple text-success fs-3 opacity-50"></i>
                                </div>
                            @endif

                            <div class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-warning-emphasis small fw-bold d-block" style="font-size: 0.75rem;"><i class="fas fa-crown me-1 text-warning"></i>คลังย่อยที่เบิกมากสุดอันดับ 1</span>
                                    <h6 class="fw-bold mb-0 text-dark mt-0.5 text-truncate" style="max-width: 200px;" title="{{ $topWard1->name ?? '-' }}">
                                        {{ $topWard1->name ?? '-' }}
                                    </h6>
                                    <span class="small text-muted font-monospace" style="font-size: 0.72rem;">
                                        {{ $topWard1 ? number_format($topWard1->val, 2) . ' ฿ (' . $topWard1->share_percent . '%)' : '-' }}
                                    </span>
                                </div>
                                @if($topWard1)
                                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill py-1 px-2.5 shadow-xs" style="font-size: 0.72rem;"
                                            onclick="viewDepartmentItems('{{ $config['type'] }}', '{{ $topWard1->dept_id ?? '' }}', '{{ addslashes($topWard1->name) }}')">
                                        <i class="fas fa-boxes-stacked me-1"></i>ดูรายการ
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side-by-Side: 20 อันดับรายการเบิกสูงสุด (Top 20) VS รายการใบขอเบิกพัสดุ/เวชภัณฑ์ (Requisitions) -->
            <div class="row g-4 mb-5">
                <!-- Left Column: 20 อันดับรายการเบิกจ่ายสูงสุด (Top 20 Disbursed Items) -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-ranking-star" style="color: {{ $config['color'] }};"></i>
                                    <span>20 อันดับรายการเบิกสูงสุด (Top 20 Items)</span>
                                </h6>
                                <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">พัสดุ/เวชภัณฑ์ที่มียอดเบิกจ่ายสะสมสูงสุด</div>
                            </div>
                            <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                                <i class="fas fa-fire me-1 text-danger"></i> Top 20 รายการ
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 560px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.81rem;">
                                    <thead class="bg-light sticky-top" style="z-index: 1;">
                                        <tr class="text-muted" style="font-size: 0.75rem;">
                                            <th class="text-center" style="width: 32px;">#</th>
                                            <th>รายการพัสดุ / เวชภัณฑ์</th>
                                            <th class="text-center" style="width: 90px;">จำนวนเบิก</th>
                                            <th class="text-end" style="width: 105px;">มูลค่ารวม (บาท)</th>
                                            <th class="text-center" style="width: 55px;">สัดส่วน</th>
                                            <th class="text-center" style="width: 85px;">หน่วยงาน</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($top20DisbursedItems ?? [] as $idx => $tItem)
                                            @php
                                                $rankBadge = match($idx) {
                                                    0 => 'bg-warning text-dark border border-warning',
                                                    1 => 'bg-secondary bg-opacity-25 text-dark border',
                                                    2 => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                                    default => 'bg-light text-muted border',
                                                };
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge rounded-circle p-1.5 {{ $rankBadge }}" style="width: 22px; height: 22px; line-height: 12px; font-size: 0.7rem;">
                                                        {{ $idx + 1 }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" 
                                                       onclick="viewItemDepartments('{{ $config['type'] }}', {{ $tItem->item_id ?? 0 }}, '{{ addslashes($tItem->name) }}')"
                                                       class="fw-bold text-dark text-decoration-none hover-primary text-truncate d-block" 
                                                       style="max-width: 180px;" 
                                                       title="{{ $tItem->name }} (คลิกเพื่อดูหน่วยงานที่เบิก)">
                                                        {{ $tItem->name }}
                                                    </a>
                                                    <div class="text-muted small font-monospace" style="font-size: 0.7rem;">
                                                        {{ $tItem->code ?: '-' }}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="fw-bold font-monospace text-dark">{{ number_format($tItem->total_qty) }}</span>
                                                    <span class="text-muted small ms-1">{{ $tItem->unit_name ?: '' }}</span>
                                                </td>
                                                <td class="text-end fw-bold font-monospace" style="color: {{ $config['color'] }};">
                                                    {{ number_format($tItem->total_val, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <span class="font-monospace small text-muted" style="font-size: 0.72rem;">{{ $tItem->share_percent }}%</span>
                                                        <div class="progress w-100 mt-1" style="height: 3px;">
                                                            <div class="progress-bar" role="progressbar" style="width: {{ min(100, $tItem->share_percent * 2) }}%; background-color: {{ $config['color'] }};"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-info py-0.5 px-2 rounded-pill shadow-xs" 
                                                            style="font-size: 0.72rem; white-space: nowrap;"
                                                            onclick="viewItemDepartments('{{ $config['type'] }}', {{ $tItem->item_id ?? 0 }}, '{{ addslashes($tItem->name) }}')"
                                                            title="ดูหน่วยงานที่เบิกรายการนี้ (เรียงจากมากไปน้อย)">
                                                        <i class="fas fa-sitemap me-1"></i>ดูหน่วยงาน
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูลรายการเบิกจ่าย</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: อันดับคลังย่อยที่เบิกมากที่สุด (Top Requisition Wards / Departments) -->
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden d-flex flex-column">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-hospital-user" style="color: {{ $config['color'] }};"></i>
                                    <span>อันดับคลังย่อยที่เบิกมากที่สุด (Top Wards)</span>
                                </h6>
                                <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                    จัดอันดับหน่วยงาน/หอผู้ป่วยตามมูลค่าการเบิกสะสม
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                                <i class="fas fa-building me-1 text-primary"></i> {{ count($topSubWhStats) }} คลังย่อย
                            </span>
                        </div>
                        <div class="card-body p-0 flex-grow-1">
                            <div class="table-responsive" style="max-height: 560px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.81rem;">
                                    <thead class="bg-light sticky-top" style="z-index: 1;">
                                        <tr class="text-muted" style="font-size: 0.75rem;">
                                            <th class="text-center" style="width: 32px;">#</th>
                                            <th>คลังย่อย (แผนก / หอผู้ป่วย)</th>
                                            <th class="text-center" style="width: 75px;">ใบเบิก</th>
                                            <th class="text-end" style="width: 110px;">มูลค่ารวม (บาท)</th>
                                            <th class="text-center" style="width: 55px;">สัดส่วน</th>
                                            <th class="text-center" style="width: 105px;">รายการที่เบิก</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topSubWhStats as $idx => $ts)
                                            @php
                                                $rankBadge = match($idx) {
                                                    0 => 'bg-warning text-dark border border-warning',
                                                    1 => 'bg-secondary bg-opacity-25 text-dark border',
                                                    2 => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                                    default => 'bg-light text-muted border',
                                                };
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge rounded-circle p-1.5 {{ $rankBadge }}" style="width: 22px; height: 22px; line-height: 12px; font-size: 0.7rem;">
                                                        {{ $idx + 1 }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0)" 
                                                       onclick="viewDepartmentItems('{{ $config['type'] }}', '{{ $ts->dept_id ?? '' }}', '{{ addslashes($ts->name) }}')"
                                                       class="fw-bold text-dark text-decoration-none hover-primary text-truncate d-block" 
                                                       style="max-width: 190px;" 
                                                       title="{{ $ts->name }} (คลิกเพื่อดูรายการที่เบิก)">
                                                        {{ $ts->name ?: 'ไม่ระบุ' }}
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-dark border px-2 py-0.5 font-monospace">
                                                        {{ number_format($ts->reqs) }} ใบ
                                                    </span>
                                                </td>
                                                <td class="text-end fw-bold font-monospace" style="color: {{ $config['color'] }};">
                                                    {{ number_format($ts->val, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <span class="font-monospace small text-muted" style="font-size: 0.72rem;">{{ $ts->share_percent }}%</span>
                                                        <div class="progress w-100 mt-1" style="height: 3px;">
                                                            <div class="progress-bar" role="progressbar" style="width: {{ min(100, $ts->share_percent * 2) }}%; background-color: {{ $config['color'] }};"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-primary py-0.5 px-2 rounded-pill shadow-xs" 
                                                            style="font-size: 0.72rem; white-space: nowrap;"
                                                            onclick="viewDepartmentItems('{{ $config['type'] }}', '{{ $ts->dept_id ?? '' }}', '{{ addslashes($ts->name) }}')"
                                                            title="ดูรายการพัสดุ/เวชภัณฑ์ที่คลังย่อยนี้เบิก">
                                                        <i class="fas fa-boxes-stacked me-1"></i>ดูรายการที่เบิก
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูลการเบิกจ่ายของคลังย่อย</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END VIEW 2: REQUISITION TRACKING & CHARTS (เบิกจ่าย) -->

        <!-- ========================================================================= -->
        <!-- VIEW 3: TWO-TIER STOCK (สต็อกจริง 2 ระดับ) -->
        <!-- ========================================================================= -->
        <div class="wh-view-section" id="view_section_stock" style="{{ $view_mode === 'stock' ? '' : 'display: none;' }}">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-5 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-cubes" style="color: {{ $config['color'] }};"></i>
                            <span>ระบบตรวจสอบตัวเลขสต็อกจริง 2 ระดับ (คลังหลัก vs คลังย่อยประจำแผนก)</span>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-0.5 rounded-pill small">Real-time Multi-tier</span>
                        </h6>
                        <div class="text-muted small mt-1">
                            ป้องกันสต็อกหลอกตา (Phantom Stockout) และตรวจนับพัสดุที่เบิกไปเก็บในหอผู้ป่วย
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="warehouse_stock_table" style="font-size: 0.85rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 45px;">ลำดับ</th>
                                    <th>รหัสพัสดุ/ยา</th>
                                    <th>ชื่อรายการสิ่งของ</th>
                                    <th class="text-end text-primary">สต็อกคลังหลัก</th>
                                    <th class="text-end text-info">สต็อกคลังย่อย (แผนก)</th>
                                    <th class="text-end fw-bold text-success">สต็อกรวมทั้ง รพ.</th>
                                    <th class="text-center">ใช้เฉลี่ย/ด. (AMC)</th>
                                    <th class="text-center">ใช้ได้อีก (MOS)</th>
                                    <th class="text-center">สถานะสต็อก</th>
                                    <th class="text-center" style="width: 90px;">คลังย่อย</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockItems as $idx => $item)
                                    @php
                                        $badgeClass = match($item->status) {
                                            'critical'  => 'badge-risk-critical',
                                            'warning'   => 'badge-risk-warning',
                                            'overstock' => 'badge-risk-overstock',
                                            default     => 'badge-risk-normal',
                                        };
                                        $label = match($item->status) {
                                            'critical'  => '🔴 ต่ำกว่า Min',
                                            'warning'   => '🟡 ใกล้จุดสั่งซื้อ',
                                            'overstock' => '🔵 สต็อกล้นคลัง',
                                            default     => '🟢 ปกติ',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                        <td class="font-monospace text-muted small">{{ $item->code }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->name }}</div>
                                            <div class="text-muted small" style="font-size: 0.73rem;">ราคาล่าสุด: {{ number_format($item->price, 2) }} ฿</div>
                                        </td>
                                        <td class="text-end font-monospace text-primary fw-bold">
                                            {{ number_format($item->main_stock) }}
                                        </td>
                                        <td class="text-end font-monospace text-info">
                                            {{ number_format($item->sub_stock) }}
                                        </td>
                                        <td class="text-end font-monospace text-success fw-bold fs-6">
                                            {{ number_format($item->total_stock) }}
                                        </td>
                                        <td class="text-center font-monospace">{{ number_format($item->amc, 1) }}</td>
                                        <td class="text-center font-monospace fw-bold {{ $item->mos < 0.5 ? 'text-danger' : 'text-dark' }}">
                                            {{ $item->mos }} เดือน
                                        </td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill px-2.5 py-1 {{ $badgeClass }}" style="font-size: 0.74rem;">
                                                {{ $label }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-info py-0.5 px-2 rounded-pill shadow-xs" 
                                                    style="font-size: 0.74rem;" 
                                                    onclick="viewItemTwoTierStock('{{ $config['type'] }}', {{ $item->id }})">
                                                <i class="fas fa-sitemap me-1"></i> ดูตึก
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- END VIEW 3: TWO-TIER STOCK -->

        <!-- ========================================================================= -->
        <!-- VIEW 4: SMART PROCUREMENT PLANNER & REORDER ALERT (แผนจัดซื้อรอบถัดไป) -->
        <!-- ========================================================================= -->
        <div class="wh-view-section" id="view_section_planner" style="{{ $view_mode === 'planner' ? '' : 'display: none;' }}">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-5 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-cart-shopping text-danger"></i>
                            <span>ข้อเสนอแนะแผนจัดซื้อพัสดุ/เวชภัณฑ์ ประจำรอบเดือนถัดไป (Smart Reorder Matrix)</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-0.5 rounded-pill small">
                                อิงเกณฑ์มาตรฐาน สธ.
                            </span>
                        </h6>
                        <div class="text-muted small mt-1">
                            คำนวณจากอัตราการใช้เฉลี่ยจริง (AMC) และเกณฑ์ Min/Max ป้องกันเงินจมและของขาดสต็อก
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning bg-opacity-15 text-dark border px-3 py-2 small fw-bold">
                            <i class="fas fa-coins me-1 text-warning"></i> ประมาณการงบประมาณรวม: {{ number_format($totalEstimatedBudget, 2) }} บาท
                        </span>
                        <a href="{{ route('backoffice.warehouse.export_reorder', ['tab' => $tab, 'year_id' => $year_id]) }}" 
                           class="btn btn-sm btn-success rounded-pill px-3 py-1.5 shadow-sm fw-bold">
                            <i class="fas fa-file-excel me-1"></i> ส่งออก Excel เสนอซื้อ
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="warehouse_planner_table" style="font-size: 0.85rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 45px;">ลำดับ</th>
                                    <th>สถานะความเสี่ยง</th>
                                    <th>รหัสพัสดุ/ยา</th>
                                    <th>ชื่อรายการสิ่งของ</th>
                                    <th class="text-end">คงเหลือรวม</th>
                                    <th class="text-center">ใช้/ด. (AMC)</th>
                                    <th class="text-center">พอใช้อีก</th>
                                    <th class="text-center">เกณฑ์ Min - Max</th>
                                    <th class="text-end text-primary fw-bold">แนะนำสั่งซื้อ</th>
                                    <th class="text-end">ราคา/หน่วย</th>
                                    <th class="text-end fw-bold text-danger">งบประมาณรวม (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reorderItems as $idx => $item)
                                    @php
                                        $badgeClass = $item->status === 'critical' ? 'badge-risk-critical' : 'badge-risk-warning';
                                        $statusText = $item->status === 'critical' ? '🔴 วิกฤต/ต้องซื้อด่วน' : '🟡 สั่งซื้อรอบปกติ';
                                    @endphp
                                    <tr>
                                        <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill px-2.5 py-1 {{ $badgeClass }}" style="font-size: 0.74rem;">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td class="font-monospace text-muted small">{{ $item->code }}</td>
                                        <td class="fw-bold text-dark">{{ $item->name }}</td>
                                        <td class="text-end font-monospace">{{ number_format($item->total_stock) }}</td>
                                        <td class="text-center font-monospace">{{ number_format($item->amc, 1) }}</td>
                                        <td class="text-center font-monospace fw-bold {{ $item->mos < 0.5 ? 'text-danger' : 'text-warning' }}">
                                            {{ $item->mos }} ด.
                                        </td>
                                        <td class="text-center text-muted small">
                                            {{ number_format($item->min_stock) }} - {{ number_format($item->max_stock) }}
                                        </td>
                                        <td class="text-end font-monospace text-primary fw-bold fs-6">
                                            {{ number_format($item->suggested_qty) }}
                                        </td>
                                        <td class="text-end text-muted small">{{ number_format($item->price, 2) }}</td>
                                        <td class="text-end font-monospace text-danger fw-bold">
                                            {{ number_format($item->estimated_budget, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- END VIEW 4: SMART PROCUREMENT PLANNER -->

    @endif

</div>

<!-- Modal 1: Requisition Detail Line Items -->
<div class="modal fade" id="requisitionDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-file-lines text-warning"></i>
                        <span>รายละเอียดใบขอเบิก: <span id="modalReqCode" class="text-primary font-monospace"></span></span>
                    </h5>
                    <div class="text-muted small mt-1">
                        ผู้ขอเบิก: <strong id="modalReqUser"></strong> | ปลายทาง: <strong id="modalReqSubWh"></strong>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 45px;">#</th>
                                <th>รหัส</th>
                                <th>ชื่อรายการสิ่งของ/พัสดุ</th>
                                <th class="text-center">จำนวนขอเบิก</th>
                                <th class="text-end">ราคา/หน่วย</th>
                                <th class="text-end">ราคารวม (บาท)</th>
                            </tr>
                        </thead>
                        <tbody id="modalReqItemsBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Item Two-Tier Stock Breakdown Across Wards -->
<div class="modal fade" id="twoTierStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <div>
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="fas fa-sitemap text-info"></i>
                        <span id="modalStockItemName">กระจายสต็อกตามคลังย่อย</span>
                    </h6>
                    <span class="text-muted small" id="modalStockItemCode"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="p-3 bg-primary bg-opacity-10 rounded-3 mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-bold">สต็อกในคลังกลาง (คลังหลัก):</span>
                        <h4 class="text-primary fw-bold mb-0" id="modalStockMainVal">0</h4>
                    </div>
                    <i class="fas fa-warehouse text-primary fs-2 opacity-50"></i>
                </div>

                <h6 class="fw-bold text-dark mb-2 small text-uppercase">สต็อกในคลังย่อยประจำแผนก/หอผู้ป่วย:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.82rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>คลังย่อย (แผนก)</th>
                                <th class="text-end" style="width: 110px;">จำนวนคงเหลือ</th>
                            </tr>
                        </thead>
                        <tbody id="modalStockSubBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Item Department Breakdown Modal (Top 20 Item by Departments, Sorted Descending) -->
<div class="modal fade" id="itemDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <div class="w-100 me-2">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-sitemap text-info"></i>
                        <span>สถิติการเบิกตามหน่วยงาน: <span id="modalItemDeptName" class="text-primary"></span></span>
                    </h5>
                    <div class="text-muted small font-monospace" id="modalItemDeptCode"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Summary bar -->
                <div class="p-3 bg-light border-bottom d-flex flex-wrap justify-content-around align-items-center text-center gap-2">
                    <div class="px-3">
                        <div class="text-muted small" style="font-size: 0.75rem;">จำนวนหน่วยงานที่เบิก</div>
                        <div class="fw-bold text-dark fs-5"><span id="modalItemDeptCount">0</span> <small class="text-muted fs-6">แห่ง</small></div>
                    </div>
                    <div class="border-start ps-3 pe-3">
                        <div class="text-muted small" style="font-size: 0.75rem;">ยอดจำนวนเบิกรวม</div>
                        <div class="fw-bold text-info fs-5" id="modalItemDeptTotalQty">-</div>
                    </div>
                    <div class="border-start ps-3 pe-3">
                        <div class="text-muted small" style="font-size: 0.75rem;">มูลค่าเบิกรวมทั้งหมด</div>
                        <div class="fw-bold text-primary fs-5"><span id="modalItemDeptTotalVal">-</span> <small class="text-muted fs-6">บาท</small></div>
                    </div>
                    <div class="border-start ps-3 pe-3">
                        <div class="text-muted small" style="font-size: 0.75rem;">การเรียงลำดับ</div>
                        <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mt-1">
                            <i class="fas fa-arrow-down-wide-short me-1"></i>เรียงจากมากไปน้อย
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 460px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                        <thead class="bg-light sticky-top" style="z-index: 1;">
                            <tr class="text-muted" style="font-size: 0.75rem;">
                                <th class="text-center" style="width: 45px;">#</th>
                                <th>หน่วยงาน / หอผู้ป่วย (คลังย่อย)</th>
                                <th class="text-center" style="width: 85px;">ใบเบิก</th>
                                <th class="text-end" style="width: 120px;">จำนวนที่เบิก</th>
                                <th class="text-end" style="width: 130px;">มูลค่ารวม (บาท)</th>
                                <th class="text-center" style="width: 100px;">สัดส่วน (%)</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemDeptTableBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small" style="font-size: 0.75rem;">
                    <i class="fas fa-info-circle me-1 text-info"></i>สถิตินี้คำนวณจากยอดเบิกจ่ายสะสมของรายการนี้ เรียงจากหน่วยงานที่เบิกมากที่สุด
                </span>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Department Items Modal (Items Disbursed by Sub-Warehouse, Sorted Descending) -->
<div class="modal fade" id="departmentItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <div class="w-100 me-2">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-boxes-stacked text-primary"></i>
                        <span>รายการที่เบิก: <span id="modalDeptName" class="text-primary"></span></span>
                    </h5>
                    <div class="text-muted small">
                        <i class="fas fa-hospital me-1"></i>คลังย่อย (แผนก/หอผู้ป่วย) | <span id="modalDeptPeriod"></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Summary bar -->
                <div class="p-3 bg-light border-bottom d-flex flex-wrap justify-content-around align-items-center text-center gap-2">
                    <div class="px-3">
                        <div class="text-muted small" style="font-size: 0.75rem;">จำนวนรายการสิ่งของ</div>
                        <div class="fw-bold text-dark fs-5"><span id="modalDeptItemsCount">0</span> <small class="text-muted fs-6">รายการ</small></div>
                    </div>
                    <div class="border-start ps-3 pe-3">
                        <div class="text-muted small" style="font-size: 0.75rem;">จำนวนชิ้นรวมที่เบิก</div>
                        <div class="fw-bold text-info fs-5" id="modalDeptTotalQty">-</div>
                    </div>
                    <div class="border-start ps-3 pe-3">
                        <div class="text-muted small" style="font-size: 0.75rem;">มูลค่าเบิกรวมทั้งหมด</div>
                        <div class="fw-bold text-primary fs-5"><span id="modalDeptTotalVal">-</span> <small class="text-muted fs-6">บาท</small></div>
                    </div>
                    <div class="border-start ps-3 pe-3">
                        <div class="text-muted small" style="font-size: 0.75rem;">การเรียงลำดับ</div>
                        <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mt-1">
                            <i class="fas fa-arrow-down-wide-short me-1"></i>เรียงจากมากไปน้อย
                        </div>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                        <thead class="bg-light sticky-top" style="z-index: 1;">
                            <tr class="text-muted" style="font-size: 0.75rem;">
                                <th class="text-center" style="width: 45px;">#</th>
                                <th>รหัส</th>
                                <th>ชื่อรายการพัสดุ / เวชภัณฑ์</th>
                                <th class="text-center" style="width: 100px;">จำนวนเบิก</th>
                                <th class="text-end" style="width: 110px;">ราคา/หน่วย</th>
                                <th class="text-end" style="width: 130px;">มูลค่ารวม (บาท)</th>
                                <th class="text-center" style="width: 90px;">สัดส่วน (%)</th>
                            </tr>
                        </thead>
                        <tbody id="modalDeptItemsBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small" style="font-size: 0.75rem;">
                    <i class="fas fa-info-circle me-1 text-info"></i>แสดงรายการสิ่งของทั้งหมดที่คลังย่อยนี้เบิกไป เรียงลำดับตามมูลค่ารวมจากมากไปน้อย
                </span>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal 6: Goods Receive Item Breakdown Modal (รายละเอียดสิ่งของในใบตรวจรับ) -->
<div class="modal fade" id="receiveDetailModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <div class="w-100 me-2">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-box-open"></i>
                        <span>รายละเอียดใบตรวจรับ: <span id="modalRecDetCode">-</span></span>
                    </h5>
                    <div class="text-white-50 small">
                        <span>ผู้จำหน่าย: <strong id="modalRecDetVendor" class="text-white">-</strong></span>
                        <span class="mx-2">|</span>
                        <span>วันที่รับ: <span id="modalRecDetDate" class="text-white">-</span></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 440px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                        <thead class="bg-light sticky-top" style="z-index: 1;">
                            <tr class="text-muted" style="font-size: 0.75rem;">
                                <th class="text-center" style="width: 45px;">#</th>
                                <th>รหัส</th>
                                <th>ชื่อรายการพัสดุ / เวชภัณฑ์</th>
                                <th class="text-center" style="width: 90px;">จำนวน</th>
                                <th class="text-end" style="width: 100px;">ราคา/หน่วย</th>
                                <th class="text-end" style="width: 120px;">มูลค่ารวม (บาท)</th>
                                <th class="text-center" style="width: 110px;">Lot / EXP</th>
                            </tr>
                        </thead>
                        <tbody id="modalRecDetItemsBody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small">ผู้ตรวจรับ: </span>
                    <strong id="modalRecDetReceiver" class="small text-dark">-</strong>
                </div>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">ย้อนกลับ</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 7: RPST Requisition & Disbursed Items Detail Modal (รายละเอียดการเบิกจ่าย รพ.สต.) -->
<div class="modal fade" id="rpstDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <div class="w-100 me-2">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0">
                            <i class="fas fa-hospital-user text-info"></i>
                            <span>รายละเอียดการเบิกจ่าย: <span id="modalRpstName" class="text-primary">-</span></span>
                            <span class="badge bg-light text-muted border font-monospace ms-1" style="font-size: 0.72rem;" id="modalRpstCode"></span>
                        </h5>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1" id="modalRpstPeriod">
                            -
                        </span>
                    </div>
                    <div class="text-muted small mt-1">
                        สรุปรายการพัสดุและเวชภัณฑ์ทั้งหมดที่ รพ.สต. ขอเบิกจาก 5 คลังหลักของโรงพยาบาล
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Summary 5 Warehouses Pills inside Modal (Clickable to Filter) -->
                <div class="p-3 bg-light border-bottom">
                    <div class="row g-2 text-center" id="rpstModalCardsRow">
                        <div class="col">
                            <div class="p-2 rounded-3 bg-white border rpst-modal-card active-wh-card" data-wh-key="all" onclick="filterRpstModal('all')" title="คลิกเพื่อดูทุกคลัง">
                                <div class="text-muted small" style="font-size: 0.72rem;">รวม 5 คลัง (บาท)</div>
                                <div class="fw-bold text-primary font-monospace fs-6" id="modalRpstTotalVal">0.00</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-2 rounded-3 bg-white border rpst-modal-card" data-wh-key="material" onclick="filterRpstModal('material')" style="border-left: 3px solid #f59e0b !important;" title="คลิกเพื่อกรองเฉพาะคลังวัสดุ">
                                <div class="text-muted small" style="font-size: 0.72rem;">คลังวัสดุ</div>
                                <div class="fw-bold font-monospace" style="color: #d97706; font-size: 0.9rem;" id="modalRpstMatVal">0.00</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-2 rounded-3 bg-white border rpst-modal-card" data-wh-key="dental" onclick="filterRpstModal('dental')" style="border-left: 3px solid #06b6d4 !important;" title="คลิกเพื่อกรองเฉพาะคลังทันตกรรม">
                                <div class="text-muted small" style="font-size: 0.72rem;">คลังทันตกรรม</div>
                                <div class="fw-bold font-monospace" style="color: #0891b2; font-size: 0.9rem;" id="modalRpstDenVal">0.00</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-2 rounded-3 bg-white border rpst-modal-card" data-wh-key="medtech" onclick="filterRpstModal('medtech')" style="border-left: 3px solid #8b5cf6 !important;" title="คลิกเพื่อกรองเฉพาะคลัง LAB">
                                <div class="text-muted small" style="font-size: 0.72rem;">คลัง LAB</div>
                                <div class="fw-bold font-monospace" style="color: #7c3aed; font-size: 0.9rem;" id="modalRpstMedtechVal">0.00</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-2 rounded-3 bg-white border rpst-modal-card" data-wh-key="physical" onclick="filterRpstModal('physical')" style="border-left: 3px solid #10b981 !important;" title="คลิกเพื่อกรองเฉพาะคลังกายภาพ">
                                <div class="text-muted small" style="font-size: 0.72rem;">คลังกายภาพ</div>
                                <div class="fw-bold font-monospace" style="color: #059669; font-size: 0.9rem;" id="modalRpstPhyVal">0.00</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-2 rounded-3 bg-white border rpst-modal-card" data-wh-key="medical" onclick="filterRpstModal('medical')" style="border-left: 3px solid #ec4899 !important;" title="คลิกเพื่อกรองเฉพาะคลังวัสดุการแพทย์">
                                <div class="text-muted small" style="font-size: 0.72rem;">คลังวัสดุการแพทย์</div>
                                <div class="fw-bold font-monospace" style="color: #db2777; font-size: 0.9rem;" id="modalRpstMedVal">0.00</div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="p-2 rounded-3 bg-white border rpst-modal-card" data-wh-key="all" onclick="filterRpstModal('all')" title="คลิกเพื่อดูทุกใบเบิก">
                                <div class="text-muted small" style="font-size: 0.72rem;">จำนวนใบเบิก</div>
                                <div class="fw-bold text-dark font-monospace fs-6" id="modalRpstReqCount">0 ใบ</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filter Banner -->
                <div id="rpstModalFilterBanner" class="px-3 pt-2 pb-0 d-none">
                    <div class="alert alert-primary bg-primary bg-opacity-10 border-primary border-opacity-25 py-1.5 px-3 mb-0 d-flex justify-content-between align-items-center rounded-3" style="font-size: 0.8rem;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary px-2 py-1"><i class="fas fa-filter me-1"></i>กำลังกรองเฉพาะ:</span>
                            <span id="rpstModalFilterText" class="fw-bold text-primary">-</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 shadow-xs" style="font-size: 0.72rem;" onclick="filterRpstModal('all')">
                            <i class="fas fa-rotate-left me-1"></i>ล้างตัวกรอง (ดูทุกคลัง)
                        </button>
                    </div>
                </div>

                <!-- Nav Tabs for Modal: Swap Requisitions to be the 1st Tab -->
                <ul class="nav nav-tabs px-3 pt-2 bg-light border-bottom" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-bold text-dark" id="modal-rpst-reqs-tab" data-bs-toggle="tab" data-bs-target="#modal-rpst-reqs" type="button" role="tab">
                            <i class="fas fa-file-invoice me-1.5 text-info"></i>ประวัติใบขอเบิก (<span id="modalRpstReqsTabCount">0</span>)
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-bold text-dark" id="modal-rpst-items-tab" data-bs-toggle="tab" data-bs-target="#modal-rpst-items" type="button" role="tab">
                            <i class="fas fa-boxes-stacked me-1.5 text-primary"></i>รายการพัสดุและเวชภัณฑ์ที่เบิก (<span id="modalRpstItemsCount">0</span>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-3">
                    <!-- Tab 1: Requisitions List (First Tab) -->
                    <div class="tab-pane fade show active" id="modal-rpst-reqs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0 w-100" id="modal_rpst_reqs_table" style="font-size: 0.82rem;">
                                <thead class="bg-light">
                                    <tr class="text-muted" style="font-size: 0.75rem;">
                                        <th class="text-center" style="width: 40px;">#</th>
                                        <th style="width: 120px;">เลขที่ใบเบิก</th>
                                        <th style="width: 95px;">วันที่เบิก</th>
                                        <th style="width: 130px;">คลังที่เบิก</th>
                                        <th>ผู้ขอเบิก</th>
                                        <th class="text-end" style="width: 110px;">มูลค่า (บาท)</th>
                                        <th class="text-center" style="width: 85px;">สถานะ</th>
                                        <th class="text-center" style="width: 90px;">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="modalRpstReqsBody">
                                    <!-- Populated via AJAX / DataTables -->
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="5" class="text-end text-dark">รวมมูลค่าตามที่แสดง:</td>
                                        <td class="text-end font-monospace text-primary fs-6" id="modalRpstReqsFooterVal">0.00 บาท</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Items List (Second Tab) -->
                    <div class="tab-pane fade" id="modal-rpst-items" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0 w-100" id="modal_rpst_items_table" style="font-size: 0.82rem;">
                                <thead class="bg-light">
                                    <tr class="text-muted" style="font-size: 0.75rem;">
                                        <th class="text-center" style="width: 40px;">#</th>
                                        <th style="width: 100px;">รหัส</th>
                                        <th>ชื่อรายการพัสดุ / เวชภัณฑ์</th>
                                        <th style="width: 140px;">คลังต้นทาง</th>
                                        <th class="text-center" style="width: 110px;">จำนวนเบิก</th>
                                        <th class="text-end" style="width: 120px;">มูลค่ารวม (บาท)</th>
                                    </tr>
                                </thead>
                                <tbody id="modalRpstItemsBody">
                                    <!-- Populated via AJAX / DataTables -->
                                </tbody>
                                <tfoot class="bg-light fw-bold">
                                    <tr>
                                        <td colspan="4" class="text-end text-dark">รวมทั้งหมดตามที่แสดง:</td>
                                        <td class="text-center font-monospace text-dark" id="modalRpstItemsFooterQty">0</td>
                                        <td class="text-end font-monospace text-primary fs-6" id="modalRpstItemsFooterVal">0.00 บาท</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small" style="font-size: 0.75rem;">
                    <i class="fas fa-circle-check me-1 text-success"></i>ข้อมูลบูรณาการ 5 คลังหลักสำหรับ รพ.สต. เครือข่าย
                </span>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 8: RPST Item Breakdown Modal (รายชื่อ รพ.สต. และจำนวนที่เบิก) -->
<div class="modal fade" id="rpstItemBreakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light py-3 px-4 border-bottom">
                <div class="w-100 me-2">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0">
                            <i class="fas fa-boxes-stacked text-primary"></i>
                            <span>สถิติการเบิก: <span id="modalItemBreakdownName" class="text-primary">-</span></span>
                            <span class="badge bg-light text-muted border font-monospace ms-1" style="font-size: 0.72rem;" id="modalItemBreakdownCode">-</span>
                            <span class="badge rounded-pill px-2.5 py-1" style="font-size: 0.72rem;" id="modalItemBreakdownWarehouse">-</span>
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1" id="modalItemBreakdownPeriod">
                            -
                        </span>
                    </div>
                    <div class="text-muted small mt-1">
                        แสดงรายชื่อ รพ.สต. เครือข่ายทั้งหมดที่ขอเบิกรายการนี้ พร้อมจำนวนและมูลค่ารวม
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Summary KPI Cards inside Modal -->
                <div class="p-3 bg-light border-bottom">
                    <div class="row g-2 text-center">
                        <div class="col-sm-3 col-6">
                            <div class="p-2 rounded-3 bg-white border">
                                <div class="text-muted small" style="font-size: 0.72rem;">รพ.สต. ที่เบิก</div>
                                <div class="fw-bold text-dark font-monospace fs-6" id="modalItemBreakdownRpstCount">0 แห่ง</div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="p-2 rounded-3 bg-white border">
                                <div class="text-muted small" style="font-size: 0.72rem;">จำนวนเบิกรวมทั้งหมด</div>
                                <div class="fw-bold text-success font-monospace fs-6">
                                    <span id="modalItemBreakdownTotalQty">0</span> <span id="modalItemBreakdownUnit" class="small fw-normal text-muted" style="font-size: 0.75rem;">หน่วย</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="p-2 rounded-3 bg-white border">
                                <div class="text-muted small" style="font-size: 0.72rem;">มูลค่ารวมทั้งหมด (บาท)</div>
                                <div class="fw-bold text-primary font-monospace fs-6" id="modalItemBreakdownTotalVal">0.00 บาท</div>
                            </div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="p-2 rounded-3 bg-white border">
                                <div class="text-muted small" style="font-size: 0.72rem;">คลังต้นทาง</div>
                                <div class="fw-bold text-dark font-monospace fs-6" id="modalItemBreakdownWhLabel">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0 w-100" id="modal_rpst_item_breakdown_table" style="font-size: 0.82rem;">
                            <thead class="bg-light">
                                <tr class="text-muted" style="font-size: 0.75rem;">
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th style="width: 95px;">รหัส รพ.สต.</th>
                                    <th>ชื่อ รพ.สต. / หน่วยบริการ</th>
                                    <th class="text-center" style="width: 120px;">จำนวนที่เบิก</th>
                                    <th style="width: 150px;">สัดส่วน (%)</th>
                                    <th class="text-end" style="width: 130px;">มูลค่ารวม (บาท)</th>
                                    <th class="text-center" style="width: 90px;">จำนวนใบเบิก</th>
                                    <th class="text-center" style="width: 100px;">เบิกล่าสุด</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemBreakdownBody">
                                <!-- Populated via AJAX / DataTables -->
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end text-dark">รวมทั้งหมดตามที่แสดง:</td>
                                    <td class="text-center font-monospace text-success fs-6" id="modalItemBreakdownFooterQty">0</td>
                                    <td class="text-center font-monospace text-muted" id="modalItemBreakdownFooterPct">100%</td>
                                    <td class="text-end font-monospace text-primary fs-6" id="modalItemBreakdownFooterVal">0.00 บาท</td>
                                    <td class="text-center font-monospace text-dark" id="modalItemBreakdownFooterReqs">0 ใบ</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small" style="font-size: 0.75rem;">
                    <i class="fas fa-circle-check me-1 text-success"></i>สถิติแจกแจงการเบิกจ่ายพัสดุและเวชภัณฑ์รายหน่วยบริการ (รพ.สต.)
                </span>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <!-- jQuery, DataTables & ApexCharts JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>

    <script>
        $(document).ready(function() {
            // =========================================================================
            // 1. Overview Tab Chart (5 Warehouses Comparison: Daily or 12-Month)
            // =========================================================================
            @if($tab === 'overview')
                @if($isMonthlyView ?? false)
                    // Monthly View: Daily Area Chart with Gradient across 5 Warehouses (ว.1 - ว.31)
                    var dailyTrendData = @json($dailyTrend ?? []);
                    var chartLabels = dailyTrendData.map(function(d) { return 'ว.' + d.day; });
                    var matSeries = dailyTrendData.map(function(d) { return d.material; });
                    var denSeries = dailyTrendData.map(function(d) { return d.dental; });
                    var medtechSeries = dailyTrendData.map(function(d) { return d.medtech; });
                    var phySeries = dailyTrendData.map(function(d) { return d.physical; });
                    var medSeries = dailyTrendData.map(function(d) { return d.medical; });

                    var overviewOptions = {
                        series: [
                            { name: 'คลังวัสดุการแพทย์', data: medSeries },
                            { name: 'คลัง LAB', data: medtechSeries },
                            { name: 'คลังวัสดุ (พัสดุ)', data: matSeries },
                            { name: 'คลังทันตกรรม', data: denSeries },
                            { name: 'คลังวัสดุกายภาพบำบัด', data: phySeries }
                        ],
                        chart: {
                            type: 'area',
                            height: 330,
                            stacked: false,
                            toolbar: { 
                                show: true,
                                tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false }
                            },
                            fontFamily: 'inherit'
                        },
                        stroke: { 
                            curve: 'smooth', 
                            width: 2.5 
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.28,
                                opacityTo: 0.02,
                                stops: [0, 90, 100]
                            }
                        },
                        markers: {
                            size: 3.5,
                            strokeColors: '#ffffff',
                            strokeWidth: 1.5,
                            hover: { size: 6 }
                        },
                        grid: {
                            borderColor: '#f1f1f1',
                            strokeDashArray: 3
                        },
                        colors: ['#e83e8c', '#8b5cf6', '#f59e0b', '#06b6d4', '#10b981'],
                        dataLabels: { enabled: false },
                        xaxis: { 
                            categories: chartLabels,
                            labels: { rotate: 0, style: { fontSize: '11px' } }
                        },
                        yaxis: {
                            labels: {
                                formatter: function (value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M B';
                                    if (value >= 1000) return (value / 1000).toFixed(0) + 'K B';
                                    return Number(value).toLocaleString() + ' B';
                                }
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: { formatter: function (val) { return Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' บาท'; } }
                        },
                        legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' }
                    };
                @else
                    // Full Year View: 12-Month Area Chart with Gradient across 5 Warehouses (ต.ค. - ก.ย.)
                    var monthlyTrendData = @json($monthlyTrend ?? []);
                    var chartLabels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
                    var matSeries = monthlyTrendData.map(function(d) { return d.material; });
                    var denSeries = monthlyTrendData.map(function(d) { return d.dental; });
                    var medtechSeries = monthlyTrendData.map(function(d) { return d.medtech; });
                    var phySeries = monthlyTrendData.map(function(d) { return d.physical; });
                    var medSeries = monthlyTrendData.map(function(d) { return d.medical; });

                    var overviewOptions = {
                        series: [
                            { name: 'คลังวัสดุการแพทย์', data: medSeries },
                            { name: 'คลัง LAB', data: medtechSeries },
                            { name: 'คลังวัสดุ (พัสดุ)', data: matSeries },
                            { name: 'คลังทันตกรรม', data: denSeries },
                            { name: 'คลังวัสดุกายภาพบำบัด', data: phySeries }
                        ],
                        chart: {
                            type: 'area',
                            height: 330,
                            stacked: false,
                            toolbar: { 
                                show: true,
                                tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false }
                            },
                            fontFamily: 'inherit'
                        },
                        stroke: { 
                            curve: 'smooth', 
                            width: 2.5 
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.28,
                                opacityTo: 0.02,
                                stops: [0, 90, 100]
                            }
                        },
                        markers: {
                            size: 4.5,
                            strokeColors: '#ffffff',
                            strokeWidth: 2,
                            hover: { size: 7 }
                        },
                        grid: {
                            borderColor: '#f1f1f1',
                            strokeDashArray: 3
                        },
                        colors: ['#e83e8c', '#8b5cf6', '#f59e0b', '#06b6d4', '#10b981'],
                        dataLabels: { enabled: false },
                        xaxis: { categories: chartLabels },
                        yaxis: {
                            labels: {
                                formatter: function (value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M B';
                                    if (value >= 1000) return (value / 1000).toFixed(0) + 'K B';
                                    return Number(value).toLocaleString() + ' B';
                                }
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: { formatter: function (val) { return Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' บาท'; } }
                        },
                        legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' }
                    };
                @endif

                var overviewChartEl = document.querySelector("#overview_trend_chart");
                if (overviewChartEl) {
                    var overviewChart = new ApexCharts(overviewChartEl, overviewOptions);
                    overviewChart.render();
                }
            @endif

            // =========================================================================
            // 2. Single Warehouse Tab: Charts & Tables Initialization
            // =========================================================================
            @if($tab !== 'overview' && $tab !== 'rpst')
                @if($isMonthlyView ?? false)
                    var dailyTrendData = @json($dailyTrend ?? []);
                    var chartLabels = dailyTrendData.map(function(d) { return 'ว.' + d.day; });
                    var recSeries = dailyTrendData.map(function(d) { return d.receive_val || 0; });
                    var disbSeries = dailyTrendData.map(function(d) { return d.val || 0; });
                    var chartType = 'area';
                @else
                    var singleTrendData = @json($monthlyTrend ?? []);
                    var chartLabels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
                    var recSeries = singleTrendData.map(function(d) { return d.receive_val || 0; });
                    var disbSeries = singleTrendData.map(function(d) { return d.val || 0; });
                    var chartType = 'bar';
                @endif

                var commonTrendOptions = {
                    series: [
                        { name: 'มูลค่ารับเข้าคลัง (บาท)', data: recSeries },
                        { name: 'มูลค่าเบิกจ่าย (บาท)', data: disbSeries }
                    ],
                    chart: {
                        type: chartType,
                        height: 290,
                        toolbar: { 
                            show: true,
                            tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false }
                        },
                        zoom: { enabled: false },
                        fontFamily: 'inherit'
                    },
                    colors: ['#0284c7', '{{ $config["color"] ?? "#f59e0b" }}'],
                    @if($isMonthlyView ?? false)
                        stroke: { curve: 'smooth', width: [2.5, 2.5] },
                        fill: {
                            type: 'gradient',
                            gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] }
                        },
                        markers: { size: 3.5, strokeColors: '#ffffff', strokeWidth: 1.5, hover: { size: 6 } },
                    @else
                        plotOptions: {
                            bar: { horizontal: false, columnWidth: '55%', borderRadius: 4, distributed: false }
                        },
                    @endif
                    dataLabels: { enabled: false },
                    xaxis: { categories: chartLabels },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M ฿';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K ฿';
                                return Number(value).toLocaleString() + ' ฿';
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: { formatter: function (val) { return Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' บาท'; } }
                    },
                    legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' }
                };

                // Chart 1: In Overview Section (Requisitions)
                var singleChartEl = document.querySelector("#warehouse_single_trend_chart");
                if (singleChartEl) {
                    var singleChart = new ApexCharts(singleChartEl, commonTrendOptions);
                    singleChart.render();
                }

                // Chart 2: In Receive Section (Goods Receipts)
                var receiveChartEl = document.querySelector("#warehouse_receive_chart");
                if (receiveChartEl) {
                    var receiveChart = new ApexCharts(receiveChartEl, commonTrendOptions);
                    receiveChart.render();
                }

                // Initialize DataTables for Clean Pagination & Search
                if (typeof $.fn.DataTable !== 'undefined') {
                    $.fn.dataTable.ext.errMode = 'none'; // Suppress alert dialogs for any minor DataTables warnings
                }

                if ($('#warehouse_receives_table').length && !$.fn.DataTable.isDataTable('#warehouse_receives_table')) {
                    $('#warehouse_receives_table').DataTable({
                        pageLength: 15,
                        language: {
                            search: "ค้นหาใบตรวจรับ:",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            infoEmpty: "ไม่พบข้อมูล",
                            emptyTable: "ไม่พบประวัติการตรวจรับเข้าคลังในงวดเวลาที่เลือก",
                            zeroRecords: "ไม่พบข้อมูลที่ตรงกับการค้นหา",
                            paginate: { first: "แรก", previous: "ก่อนหน้า", next: "ถัดไป", last: "ท้ายสุด" }
                        },
                        order: [[0, 'asc']]
                    });
                }
                if ($('#warehouse_stock_table').length && !$.fn.DataTable.isDataTable('#warehouse_stock_table')) {
                    $('#warehouse_stock_table').DataTable({
                        pageLength: 20,
                        language: {
                            search: "ค้นหาสต็อกพัสดุ/ยา:",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            infoEmpty: "ไม่พบข้อมูล",
                            emptyTable: "ไม่พบข้อมูลสต็อกพัสดุ/ยาในคลังนี้",
                            zeroRecords: "ไม่พบข้อมูลที่ตรงกับการค้นหา",
                            paginate: { first: "แรก", previous: "ก่อนหน้า", next: "ถัดไป", last: "ท้ายสุด" }
                        },
                        order: [[0, 'asc']]
                    });
                }
                if ($('#warehouse_planner_table').length && !$.fn.DataTable.isDataTable('#warehouse_planner_table')) {
                    $('#warehouse_planner_table').DataTable({
                        pageLength: 20,
                        language: {
                            search: "ค้นหารายการสั่งซื้อ:",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            infoEmpty: "ไม่พบข้อมูล",
                            emptyTable: "สต็อกสินค้าอยู่ในเกณฑ์ปลอดภัยครบทุกรายการ ไม่พบรายการที่ต้องสั่งซื้อด่วนในรอบนี้",
                            zeroRecords: "ไม่พบข้อมูลที่ตรงกับการค้นหา",
                            paginate: { first: "แรก", previous: "ก่อนหน้า", next: "ถัดไป", last: "ท้ายสุด" }
                        },
                        order: [[0, 'asc']]
                    });
                }
            @endif

            // =========================================================================
            // 3. RPST Tab: Tables Initialization
            // =========================================================================
            @if($tab === 'rpst')
                if (typeof $.fn.DataTable !== 'undefined') {
                    $.fn.dataTable.ext.errMode = 'none';
                }

                if ($('#rpst_requisitions_table').length && !$.fn.DataTable.isDataTable('#rpst_requisitions_table')) {
                    $('#rpst_requisitions_table').DataTable({
                        pageLength: 10,
                        language: {
                            search: "ค้นหาใบเบิก รพ.สต.:",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            infoEmpty: "ไม่พบข้อมูล",
                            emptyTable: "ไม่พบประวัติใบขอเบิกของ รพ.สต. ในงวดเวลาที่เลือก",
                            zeroRecords: "ไม่พบข้อมูลที่ตรงกับการค้นหา",
                            paginate: { first: "แรก", previous: "ก่อนหน้า", next: "ถัดไป", last: "ท้ายสุด" }
                        },
                        order: [[0, 'asc']],
                        columnDefs: [
                            { orderable: false, targets: -1 }
                        ]
                    });
                }

                // Render Multi-Warehouse Monthly Trend Chart (5 Separate Lines)
                @if($isMonthlyView ?? false)
                    var rpstDailyTrend = @json($dailyTrend ?? []);
                    var rpstLabels = rpstDailyTrend.map(function(d) { return 'ว.' + d.day; });
                    var rpstMat = rpstDailyTrend.map(function(d) { return d.material || 0; });
                    var rpstDen = rpstDailyTrend.map(function(d) { return d.dental || 0; });
                    var rpstMedtech = rpstDailyTrend.map(function(d) { return d.medtech || 0; });
                    var rpstPhy = rpstDailyTrend.map(function(d) { return d.physical || 0; });
                    var rpstMed = rpstDailyTrend.map(function(d) { return d.medical || 0; });
                @else
                    var rpstMonthlyTrend = @json($monthlyTrend ?? []);
                    var rpstLabels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
                    var rpstMat = rpstMonthlyTrend.map(function(d) { return d.material || 0; });
                    var rpstDen = rpstMonthlyTrend.map(function(d) { return d.dental || 0; });
                    var rpstMedtech = rpstMonthlyTrend.map(function(d) { return d.medtech || 0; });
                    var rpstPhy = rpstMonthlyTrend.map(function(d) { return d.physical || 0; });
                    var rpstMed = rpstMonthlyTrend.map(function(d) { return d.medical || 0; });
                @endif

                var rpstChartOptions = {
                    series: [
                        { name: 'คลังวัสดุ (พัสดุ)', data: rpstMat },
                        { name: 'คลังทันตกรรม', data: rpstDen },
                        { name: 'คลัง LAB', data: rpstMedtech },
                        { name: 'คลังวัสดุกายภาพบำบัด', data: rpstPhy },
                        { name: 'คลังวัสดุการแพทย์ (ยา/เวชภัณฑ์)', data: rpstMed }
                    ],
                    chart: {
                        type: 'area',
                        height: 330,
                        stacked: false,
                        toolbar: { 
                            show: true,
                            tools: { download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false }
                        },
                        fontFamily: 'inherit'
                    },
                    stroke: { 
                        curve: 'smooth', 
                        width: 2.5 
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.28,
                            opacityTo: 0.02,
                            stops: [0, 90, 100]
                        }
                    },
                    markers: {
                        size: 4.5,
                        strokeColors: '#ffffff',
                        strokeWidth: 2,
                        hover: { size: 7 }
                    },
                    grid: {
                        borderColor: '#f1f1f1',
                        strokeDashArray: 3
                    },
                    colors: ['#f59e0b', '#06b6d4', '#8b5cf6', '#10b981', '#ec4899'],
                    dataLabels: { enabled: false },
                    xaxis: { categories: rpstLabels },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M ฿';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K ฿';
                                return Number(value).toLocaleString() + ' ฿';
                            }
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: { formatter: function (val) { return Number(val || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' บาท'; } }
                    },
                    legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' }
                };

                var rpstChartEl = document.querySelector("#rpst_warehouse_trend_chart");
                if (rpstChartEl) {
                    var rpstChart = new ApexCharts(rpstChartEl, rpstChartOptions);
                    rpstChart.render();
                }
            @endif
            // Auto adjust DataTables columns when modal tabs switch
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($.fn.DataTable) {
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                }
            });
        });

        // =========================================================================
        // Interactive Tab Switching: 4 KPI Cards & Viewmode Buttons
        // Switches section content instantly on the same page without reload
        // =========================================================================
        function switchWarehouseView(mode) {
            if (!mode) return;

            // 1. Hide all view sections and show the target section
            $('.wh-view-section').hide();
            $('#view_section_' + mode).fadeIn(150);

            // 2. Update active style on the 4 top KPI cards
            $('#wh_card_receive, #wh_card_overview, #wh_card_stock, #wh_card_planner').removeClass(
                'active-kpi-card active-card-primary active-card-warning active-card-success active-card-danger'
            );

            if (mode === 'receive') {
                $('#wh_card_receive').addClass('active-kpi-card active-card-primary');
            } else if (mode === 'overview') {
                $('#wh_card_overview').addClass('active-kpi-card active-card-warning');
            } else if (mode === 'stock') {
                $('#wh_card_stock').addClass('active-kpi-card active-card-success');
            } else if (mode === 'planner') {
                $('#wh_card_planner').addClass('active-kpi-card active-card-danger');
            }

            // 3. Update badges inside cards
            $('.wh-kpi-status-badge').each(function() {
                var bMode = $(this).data('mode');
                $(this).removeClass('bg-light text-muted border bg-primary bg-warning bg-success bg-danger text-white text-dark shadow-xs');
                if (bMode === mode) {
                    var colorClass = (bMode === 'receive') ? 'bg-primary text-white' :
                                     ((bMode === 'overview') ? 'bg-warning text-dark' :
                                     ((bMode === 'stock') ? 'bg-success text-white' : 'bg-danger text-white'));
                    $(this).addClass(colorClass + ' shadow-xs').html('<i class="fas fa-eye me-1"></i>กำลังแสดง');
                } else {
                    $(this).addClass('bg-light text-muted border').html('<i class="fas fa-hand-pointer me-1"></i>คลิกดูข้อมูล');
                }
            });


            // 4. Update browser URL without reloading page
            if (window.history && window.history.replaceState) {
                var url = new URL(window.location.href);
                url.searchParams.set('view_mode', mode);
                window.history.replaceState({}, '', url.toString());
            }

            // 5. Trigger resize event for ApexCharts & DataTables adjustments
            setTimeout(function() {
                window.dispatchEvent(new Event('resize'));
                if ($.fn.dataTable) {
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                }
            }, 60);
        }

        // Keep viewReceivesList as a safe alias to switchWarehouseView('receive')
        function viewReceivesList(type) {
            switchWarehouseView('receive');
        }

        // AJAX: View Goods Receive Items (รายละเอียดสิ่งของในใบตรวจรับ)
        function viewReceiveItems(type, id) {
            $('#modalRecDetCode').text('กำลังโหลด...');
            $('#modalRecDetVendor').text('-');
            $('#modalRecDetDate').text('-');
            $('#modalRecDetReceiver').text('-');
            $('#modalRecDetItemsBody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> กำลังโหลดรายการสิ่งของ...</td></tr>');

            const modalEl = document.getElementById('receiveDetailModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            const url = '{{ url("backoffice/warehouse/receive") }}/' + type + '/' + id;
            $.get(url, function(res) {
                $('#modalRecDetCode').text(res.code + (res.po_code ? ' (PO: ' + res.po_code + ')' : ''));
                $('#modalRecDetVendor').text(res.vendor || '-');
                $('#modalRecDetDate').text(res.receive_date ? res.receive_date.substring(0, 16) : '-');
                $('#modalRecDetReceiver').text(res.receiver || '-');

                var html = '';
                if (res.items && res.items.length > 0) {
                    var sumVal = 0;
                    $.each(res.items, function(idx, it) {
                        var total = parseFloat(it.total || 0);
                        sumVal += total;
                        var lotExp = (it.lot_no ? 'Lot: ' + it.lot_no : '') + (it.exp_date ? '<br><span class="text-muted small">EXP: ' + it.exp_date + '</span>' : '');
                        html += '<tr>' +
                            '<td class="text-center text-muted">' + (idx + 1) + '</td>' +
                            '<td class="font-monospace text-muted small">' + (it.code || '-') + '</td>' +
                            '<td class="fw-bold text-dark">' + (it.name || '-') + '</td>' +
                            '<td class="text-center font-monospace">' + Number(it.amount || 0).toLocaleString() + (it.unit_name ? ' ' + it.unit_name : '') + '</td>' +
                            '<td class="text-end text-muted font-monospace">' + Number(it.price || 0).toLocaleString(undefined, {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end fw-bold text-primary font-monospace">' + total.toLocaleString(undefined, {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-center small font-monospace">' + (lotExp || '-') + '</td>' +
                        '</tr>';
                    });
                    html += '<tr class="bg-light fw-bold">' +
                        '<td colspan="5" class="text-end">ยอดรวมทั้งสิ้น:</td>' +
                        '<td class="text-end text-primary font-monospace fs-6">' + sumVal.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' บาท</td>' +
                        '<td></td>' +
                    '</tr>';
                } else {
                    html = '<tr><td colspan="7" class="text-center text-muted py-3">ไม่พบรายการสิ่งของในใบรับนี้</td></tr>';
                }
                $('#modalRecDetItemsBody').html(html);
            }).fail(function() {
                $('#modalRecDetItemsBody').html('<tr><td colspan="7" class="text-center text-danger py-3">เกิดข้อผิดพลาดในการโหลดรายการสิ่งของ</td></tr>');
            });
        }

        // AJAX: View Requisition Detail
        function viewRequestDetail(type, id) {
            $('#modalReqCode').text('กำลังโหลด...');
            $('#modalReqUser').text('-');
            $('#modalReqSubWh').text('-');
            $('#modalReqItemsBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> กำลังโหลดข้อมูล...</td></tr>');
            
            const modalEl = document.getElementById('requisitionDetailModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            const url = '{{ url("backoffice/warehouse/request") }}/' + type + '/' + id;
            $.get(url, function(res) {
                $('#modalReqCode').text(res.code);
                $('#modalReqUser').text(res.requester || '-');
                $('#modalReqSubWh').text(res.sub_warehouse || '-');

                var html = '';
                if (res.items && res.items.length > 0) {
                    var sumTotal = 0;
                    $.each(res.items, function(idx, item) {
                        var totalVal = parseFloat(item.total || 0);
                        sumTotal += totalVal;
                        html += '<tr>' +
                            '<td class="text-center text-muted">' + (idx + 1) + '</td>' +
                            '<td class="font-monospace text-muted small">' + (item.code || '-') + '</td>' +
                            '<td class="fw-bold text-dark">' + (item.name || '-') + '</td>' +
                            '<td class="text-center font-monospace">' + Number(item.amount).toLocaleString() + '</td>' +
                            '<td class="text-end text-muted">' + Number(item.price).toLocaleString(undefined, {minimumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end fw-bold text-primary font-monospace">' + totalVal.toLocaleString(undefined, {minimumFractionDigits: 2}) + '</td>' +
                        '</tr>';
                    });
                    html += '<tr class="bg-light fw-bold">' +
                        '<td colspan="5" class="text-end">ยอดรวมทั้งสิ้น:</td>' +
                        '<td class="text-end text-primary font-monospace fs-6">' + sumTotal.toLocaleString(undefined, {minimumFractionDigits: 2}) + ' บาท</td>' +
                    '</tr>';
                } else {
                    html = '<tr><td colspan="6" class="text-center text-muted py-3">ไม่พบรายการสิ่งของในใบเบิกนี้</td></tr>';
                }
                $('#modalReqItemsBody').html(html);
            }).fail(function() {
                $('#modalReqItemsBody').html('<tr><td colspan="6" class="text-center text-danger py-3">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
            });
        }

        // AJAX: View Two-Tier Stock Breakdown
        function viewItemTwoTierStock(type, id) {
            $('#modalStockItemName').text('กำลังโหลด...');
            $('#modalStockItemCode').text('');
            $('#modalStockMainVal').text('0');
            $('#modalStockSubBody').html('<tr><td colspan="2" class="text-center py-3 text-muted">กำลังค้นหาข้อมูลคลังย่อย...</td></tr>');
            
            const modalEl = document.getElementById('twoTierStockModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            const url = '{{ url("backoffice/warehouse/item") }}/' + type + '/' + id;
            $.get(url, function(res) {
                $('#modalStockItemName').text(res.name);
                $('#modalStockItemCode').text('รหัส: ' + res.code);
                $('#modalStockMainVal').text(Number(res.main_stock).toLocaleString() + ' ชิ้น');

                var html = '';
                if (res.sub_stocks && res.sub_stocks.length > 0) {
                    var totalSub = 0;
                    $.each(res.sub_stocks, function(idx, s) {
                        var qty = parseFloat(s.qty || 0);
                        totalSub += qty;
                        html += '<tr>' +
                            '<td class="fw-bold text-dark"><i class="fas fa-hospital me-1.5 text-muted"></i>' + s.dept_name + '</td>' +
                            '<td class="text-end font-monospace fw-bold text-info">' + qty.toLocaleString() + '</td>' +
                        '</tr>';
                    });
                    html += '<tr class="bg-light fw-bold">' +
                        '<td>รวมในคลังย่อยทุกแผนก:</td>' +
                        '<td class="text-end font-monospace text-info">' + totalSub.toLocaleString() + ' ชิ้น</td>' +
                    '</tr>';
                } else {
                    html = '<tr><td colspan="2" class="text-center text-muted py-2">ไม่มีสต็อกคั่งค้างในคลังย่อย (อยู่ในเกณฑ์ปลอดภัย)</td></tr>';
                }
                $('#modalStockSubBody').html(html);
            }).fail(function() {
                $('#modalStockSubBody').html('<tr><td colspan="2" class="text-center text-danger py-2">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
            });
        }

        // AJAX: View Item Department Disbursement Breakdown (Sorted Descending)
        function viewItemDepartments(type, id, name) {
            $('#modalItemDeptName').text(name || 'กำลังโหลด...');
            $('#modalItemDeptCode').text('');
            $('#modalItemDeptTotalQty').text('-');
            $('#modalItemDeptTotalVal').text('-');
            $('#modalItemDeptCount').text('-');
            $('#modalItemDeptTableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-info me-2"></div> กำลังโหลดข้อมูลสถิติการเบิกตามหน่วยงาน...</td></tr>');

            const modalEl = document.getElementById('itemDepartmentModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            const url = '{{ url("backoffice/warehouse/item-departments") }}/' + type + '/' + id + '?year_id={{ $year_id }}&month_id={{ $month_id }}';

            $.get(url, function(res) {
                $('#modalItemDeptName').text(res.name);
                $('#modalItemDeptCode').text('รหัส: ' + (res.code || '-'));
                $('#modalItemDeptTotalQty').text(Number(res.sum_qty).toLocaleString() + ' ' + (res.unit || ''));
                $('#modalItemDeptTotalVal').text(Number(res.sum_val).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#modalItemDeptCount').text(res.departments ? res.departments.length : 0);

                var html = '';
                if (res.departments && res.departments.length > 0) {
                    $.each(res.departments, function(idx, d) {
                        var rankBadge = (idx === 0) ? 'bg-warning text-dark border border-warning' :
                                        (idx === 1) ? 'bg-secondary bg-opacity-25 text-dark border' :
                                        (idx === 2) ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' :
                                        'bg-light text-muted border';

                        var totalVal = parseFloat(d.total_val || 0);
                        var totalQty = parseFloat(d.total_qty || 0);
                        var pct = parseFloat(d.percentage || 0);

                        html += '<tr>' +
                            '<td class="text-center">' +
                                '<span class="badge rounded-circle p-1 ' + rankBadge + '" style="width: 22px; height: 22px; line-height: 14px; font-size: 0.7rem;">' + (idx + 1) + '</span>' +
                            '</td>' +
                            '<td>' +
                                '<div class="fw-bold text-dark"><i class="fas fa-hospital me-1.5 text-muted"></i>' + (d.dept_name || '-') + '</div>' +
                            '</td>' +
                            '<td class="text-center font-monospace">' +
                                '<span class="badge bg-light text-dark border px-2 py-0.5">' + Number(d.req_count || 1).toLocaleString() + ' ใบ</span>' +
                            '</td>' +
                            '<td class="text-end font-monospace fw-semibold text-dark">' +
                                Number(totalQty).toLocaleString() + ' <span class="text-muted small">' + (res.unit || '') + '</span>' +
                            '</td>' +
                            '<td class="text-end font-monospace fw-bold text-primary">' +
                                totalVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) +
                            '</td>' +
                            '<td class="text-center" style="min-width: 90px;">' +
                                '<div class="d-flex flex-column align-items-center">' +
                                    '<span class="font-monospace small fw-bold text-dark" style="font-size: 0.72rem;">' + pct + '%</span>' +
                                    '<div class="progress w-100 mt-1" style="height: 4px;">' +
                                        '<div class="progress-bar bg-info" role="progressbar" style="width: ' + Math.min(100, pct) + '%;"></div>' +
                                    '</div>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="6" class="text-center text-muted py-4">ไม่พบประวัติการขอเบิกสำหรับรายการนี้ในงวดเวลาที่เลือก</td></tr>';
                }
                $('#modalItemDeptTableBody').html(html);
            }).fail(function() {
                $('#modalItemDeptTableBody').html('<tr><td colspan="6" class="text-center text-danger py-4">เกิดข้อผิดพลาดในการโหลดข้อมูลหน่วยงาน</td></tr>');
            });
        }

        // AJAX: View Sub-Warehouse (Department) Disbursed Items
        function viewDepartmentItems(type, deptId, deptName) {
            $('#modalDeptName').text(deptName || 'กำลังโหลด...');
            $('#modalDeptPeriod').text('ปีงบ {{ $year_id }}{{ ($isMonthlyView ?? false) ? " (เดือน" . ($selectedMonthName ?? "") . ")" : " (ทั้งปี)" }}');
            $('#modalDeptItemsCount').text('-');
            $('#modalDeptTotalQty').text('-');
            $('#modalDeptTotalVal').text('-');
            $('#modalDeptItemsBody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> กำลังโหลดรายการสิ่งของที่เบิก...</td></tr>');

            const modalEl = document.getElementById('departmentItemsModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            const url = '{{ url("backoffice/warehouse/department-items") }}/' + type + '?dept_id=' + encodeURIComponent(deptId || '') + '&dept_name=' + encodeURIComponent(deptName || '') + '&year_id={{ $year_id }}&month_id={{ $month_id }}';

            $.get(url, function(res) {
                $('#modalDeptName').text(res.dept_name);
                $('#modalDeptItemsCount').text(res.items_count || 0);
                $('#modalDeptTotalQty').text(Number(res.sum_qty || 0).toLocaleString());
                $('#modalDeptTotalVal').text(Number(res.sum_val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                var html = '';
                if (res.items && res.items.length > 0) {
                    $.each(res.items, function(idx, item) {
                        var rankBadge = (idx === 0) ? 'bg-warning text-dark border border-warning' :
                                        (idx === 1) ? 'bg-secondary bg-opacity-25 text-dark border' :
                                        (idx === 2) ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' :
                                        'bg-light text-muted border';

                        var totalVal = parseFloat(item.total_val || 0);
                        var avgPrice = parseFloat(item.avg_price || 0);
                        var totalQty = parseFloat(item.total_qty || 0);
                        var pct = parseFloat(item.percentage || 0);

                        html += '<tr>' +
                            '<td class="text-center">' +
                                '<span class="badge rounded-circle p-1 ' + rankBadge + '" style="width: 22px; height: 22px; line-height: 14px; font-size: 0.7rem;">' + (idx + 1) + '</span>' +
                            '</td>' +
                            '<td class="font-monospace text-muted small">' + (item.code || '-') + '</td>' +
                            '<td>' +
                                '<div class="fw-bold text-dark">' + (item.name || '-') + '</div>' +
                            '</td>' +
                            '<td class="text-center font-monospace">' +
                                '<span class="fw-bold text-dark">' + Number(totalQty).toLocaleString() + '</span> ' +
                                '<span class="text-muted small">' + (item.unit_name || '') + '</span>' +
                            '</td>' +
                            '<td class="text-end text-muted font-monospace">' + avgPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                            '<td class="text-end fw-bold font-monospace text-primary">' + totalVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                            '<td class="text-center" style="min-width: 85px;">' +
                                '<div class="d-flex flex-column align-items-center">' +
                                    '<span class="font-monospace small fw-bold text-dark" style="font-size: 0.72rem;">' + pct + '%</span>' +
                                    '<div class="progress w-100 mt-1" style="height: 4px;">' +
                                        '<div class="progress-bar bg-primary" role="progressbar" style="width: ' + Math.min(100, pct) + '%;"></div>' +
                                    '</div>' +
                                '</div>' +
                            '</td>' +
                        '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center text-muted py-4">ไม่พบรายการสิ่งของที่เบิกสำหรับหน่วยงานนี้ในงวดเวลาที่เลือก</td></tr>';
                }
                $('#modalDeptItemsBody').html(html);
            }).fail(function() {
                $('#modalDeptItemsBody').html('<tr><td colspan="7" class="text-center text-danger py-4">เกิดข้อผิดพลาดในการโหลดรายการสิ่งของ</td></tr>');
            });
        }

        // =========================================================================
        // AJAX: View RPST Requisitions & Disbursed Items Detail Modal
        // =========================================================================
        window.currentRpstModalData = null;
        window.currentRpstModalFilter = 'all';

        function renderRpstModalTables(filterWhKey) {
            filterWhKey = filterWhKey || 'all';
            const res = window.currentRpstModalData;
            if (!res) return;

            // Destroy previous DataTables instances
            if ($.fn.DataTable.isDataTable('#modal_rpst_reqs_table')) {
                $('#modal_rpst_reqs_table').DataTable().destroy();
            }
            if ($.fn.DataTable.isDataTable('#modal_rpst_items_table')) {
                $('#modal_rpst_items_table').DataTable().destroy();
            }

            // Filter Requisitions
            var filteredReqs = res.requisitions || [];
            if (filterWhKey !== 'all') {
                filteredReqs = filteredReqs.filter(function(rq) {
                    return rq.warehouse_key === filterWhKey;
                });
            }

            // Filter Items
            var filteredItems = res.items || [];
            if (filterWhKey !== 'all') {
                filteredItems = filteredItems.filter(function(it) {
                    return it.warehouse_key === filterWhKey;
                });
            }

            $('#modalRpstReqsTabCount').text(filteredReqs.length);
            $('#modalRpstItemsCount').text(filteredItems.length);

            // Populate Requisitions Table Body
            var reqsHtml = '';
            var reqsSumVal = 0;
            if (filteredReqs.length > 0) {
                $.each(filteredReqs, function(idx, rq) {
                    var stClass = (rq.status === 'Allow' || rq.status === 'Verify' || rq.status === 'Approve') 
                        ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25'
                        : (rq.status === 'Disallow' || rq.status === 'Disapprove' ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' : 'bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50');
                    var stLabel = (rq.status === 'Allow') ? 'จ่ายแล้ว' :
                                  (rq.status === 'Verify') ? 'ตรวจสอบแล้ว' :
                                  (rq.status === 'Approve') ? 'อนุมัติแล้ว' :
                                  (rq.status === 'Disallow' || rq.status === 'Disapprove') ? 'ไม่อนุมัติ' : 'รอดำเนินการ';

                    var actionBtn = '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 shadow-xs" style="font-size: 0.72rem;" onclick="viewRequestDetail(\'' + rq.warehouse_key + '\', ' + rq.id + ')"><i class="fas fa-file-invoice me-1"></i>ดูรายการ</button>';
                    var valNum = parseFloat(rq.total_val || 0);
                    reqsSumVal += valNum;

                    reqsHtml += '<tr>' +
                        '<td class="text-center text-muted font-monospace">' + (idx + 1) + '</td>' +
                        '<td class="font-monospace fw-bold text-primary">' + (rq.code || '-') + '</td>' +
                        '<td class="text-muted small">' + (rq.request_date || '-') + '</td>' +
                        '<td><span class="badge rounded-pill px-2 py-0.5" style="background: ' + rq.warehouse_color + '15; color: ' + rq.warehouse_color + '; font-size: 0.7rem;">' + rq.warehouse_name + '</span></td>' +
                        '<td class="text-muted small text-truncate" style="max-width: 150px;" title="' + rq.requester + '">' + rq.requester + '</td>' +
                        '<td class="text-end font-monospace fw-bold text-primary" data-order="' + valNum + '">' + valNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                        '<td class="text-center"><span class="badge rounded-pill px-2 py-0.5 ' + stClass + '" style="font-size: 0.68rem;">' + stLabel + '</span></td>' +
                        '<td class="text-center">' + actionBtn + '</td>' +
                    '</tr>';
                });
            } else {
                reqsHtml = '<tr><td colspan="8" class="text-center text-muted py-4">ไม่พบประวัติใบขอเบิก' + (filterWhKey !== 'all' ? ' สำหรับคลังนี้' : '') + ' ในงวดเวลานี้</td></tr>';
            }
            $('#modalRpstReqsBody').html(reqsHtml);
            $('#modalRpstReqsFooterVal').text(reqsSumVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');

            // Populate Items Table Body
            var itemsHtml = '';
            var itemsSumQty = 0;
            var itemsSumVal = 0;
            if (filteredItems.length > 0) {
                $.each(filteredItems, function(idx, it) {
                    var qtyNum = parseFloat(it.qty || 0);
                    var valNum = parseFloat(it.total_val || 0);
                    itemsSumQty += qtyNum;
                    itemsSumVal += valNum;

                    itemsHtml += '<tr>' +
                        '<td class="text-center text-muted font-monospace">' + (idx + 1) + '</td>' +
                        '<td class="font-monospace text-muted small">' + (it.code || '-') + '</td>' +
                        '<td><div class="fw-bold text-dark text-truncate" style="max-width: 320px;" title="' + it.name + '">' + it.name + '</div></td>' +
                        '<td><span class="badge rounded-pill px-2 py-0.5" style="background: ' + it.warehouse_color + '15; color: ' + it.warehouse_color + '; font-size: 0.7rem;">' + it.warehouse_name + '</span></td>' +
                        '<td class="text-center font-monospace fw-bold text-dark" data-order="' + qtyNum + '">' + Number(qtyNum).toLocaleString() + ' <span class="text-muted fw-normal small">' + (it.unit || '') + '</span></td>' +
                        '<td class="text-end font-monospace fw-bold text-primary" data-order="' + valNum + '">' + valNum.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                    '</tr>';
                });
            } else {
                itemsHtml = '<tr><td colspan="6" class="text-center text-muted py-4">ไม่พบรายการพัสดุและเวชภัณฑ์ที่ขอเบิก' + (filterWhKey !== 'all' ? ' สำหรับคลังนี้' : '') + ' ในงวดเวลานี้</td></tr>';
            }
            $('#modalRpstItemsBody').html(itemsHtml);
            $('#modalRpstItemsFooterQty').text(itemsSumQty.toLocaleString());
            $('#modalRpstItemsFooterVal').text(itemsSumVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');

            // Initialize DataTables
            if (typeof $.fn.DataTable !== 'undefined') {
                if (filteredReqs.length > 0) {
                    $('#modal_rpst_reqs_table').DataTable({
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]],
                        pagingType: 'full_numbers',
                        searching: true,
                        order: [[2, 'desc']],
                        dom: "<'row g-2 align-items-center mb-3'<'col-12 col-md-5 d-flex align-items-center'l><'col-12 col-md-7 d-flex align-items-center justify-content-md-end gap-2 flex-wrap'f B>>" +
                             "<'row'<'col-12'tr>>" +
                             "<'row g-2 align-items-center mt-3'<'col-12 col-md-5'i><'col-12 col-md-7 d-flex justify-content-md-end'p>>",
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fas fa-file-excel me-1.5" style="color: #107c41; font-size: 0.95rem;"></i><span style="color: #15803d; font-weight: 600;">ส่งออก Excel</span>',
                                className: 'btn btn-sm btn-outline-success rounded-pill shadow-xs btn-excel-custom',
                                title: function() {
                                    return 'ประวัติใบขอเบิก_' + ($('#modalRpstName').text() || 'รพ.สต.') + '_' + ($('#modalRpstPeriod').text() || '');
                                },
                                footer: true
                            }
                        ],
                        language: {
                            search: "ค้นหา:",
                            searchPlaceholder: "เลขที่ใบเบิก, ผู้ขอเบิก...",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            infoEmpty: "ไม่มีข้อมูล",
                            infoFiltered: "(กรองจาก _MAX_ รายการ)",
                            emptyTable: "ไม่พบข้อมูล",
                            zeroRecords: "ไม่พบข้อมูลที่ตรงกับการค้นหา",
                            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
                        },
                        footerCallback: function (row, data, start, end, display) {
                            var api = this.api();
                            var intVal = function (i) {
                                if (typeof i === 'number') return i;
                                if (typeof i === 'string') {
                                    var clean = i.replace(/<[^>]*>?/gm, '').replace(/[\$,บาท]/g, '').trim().replace(/,/g, '');
                                    var m = clean.match(/^([\d\.]+)/);
                                    return m ? parseFloat(m[1]) : 0;
                                }
                                return 0;
                            };
                            var total = api.column(5, { search: 'applied' }).data().reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                            $('#modalRpstReqsFooterVal').text(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                        }
                    });
                }

                if (filteredItems.length > 0) {
                    $('#modal_rpst_items_table').DataTable({
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]],
                        pagingType: 'full_numbers',
                        searching: true,
                        order: [[5, 'desc']],
                        dom: "<'row g-2 align-items-center mb-3'<'col-12 col-md-5 d-flex align-items-center'l><'col-12 col-md-7 d-flex align-items-center justify-content-md-end gap-2 flex-wrap'f B>>" +
                             "<'row'<'col-12'tr>>" +
                             "<'row g-2 align-items-center mt-3'<'col-12 col-md-5'i><'col-12 col-md-7 d-flex justify-content-md-end'p>>",
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fas fa-file-excel me-1.5" style="color: #107c41; font-size: 0.95rem;"></i><span style="color: #15803d; font-weight: 600;">ส่งออก Excel</span>',
                                className: 'btn btn-sm btn-outline-success rounded-pill shadow-xs btn-excel-custom',
                                title: function() {
                                    return 'รายการพัสดุและเวชภัณฑ์ที่เบิก_' + ($('#modalRpstName').text() || 'รพ.สต.') + '_' + ($('#modalRpstPeriod').text() || '');
                                },
                                footer: true
                            }
                        ],
                        language: {
                            search: "ค้นหา:",
                            searchPlaceholder: "รหัส, ชื่อรายการ...",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            infoEmpty: "ไม่มีข้อมูล",
                            infoFiltered: "(กรองจาก _MAX_ รายการ)",
                            emptyTable: "ไม่พบข้อมูล",
                            zeroRecords: "ไม่พบข้อมูลที่ตรงกับการค้นหา",
                            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
                        },
                        footerCallback: function (row, data, start, end, display) {
                            var api = this.api();
                            var intVal = function (i) {
                                if (typeof i === 'number') return i;
                                if (typeof i === 'string') {
                                    var clean = i.replace(/<[^>]*>?/gm, '').replace(/[\$,บาท]/g, '').trim().replace(/,/g, '');
                                    var m = clean.match(/^([\d\.]+)/);
                                    return m ? parseFloat(m[1]) : 0;
                                }
                                return 0;
                            };
                            var totalQty = api.column(4, { search: 'applied' }).data().reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                            var totalVal = api.column(5, { search: 'applied' }).data().reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                            $('#modalRpstItemsFooterQty').text(totalQty.toLocaleString());
                            $('#modalRpstItemsFooterVal').text(totalVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                        }
                    });
                }
            }
        }

        function filterRpstModal(whKey) {
            window.currentRpstModalFilter = whKey || 'all';

            $('.rpst-modal-card').removeClass('active-wh-card');
            $('.rpst-modal-card[data-wh-key="' + window.currentRpstModalFilter + '"]').addClass('active-wh-card');

            var whLabels = {
                'all': 'ทุกคลัง (5 คลังหลัก)',
                'material': 'คลังวัสดุ',
                'dental': 'คลังทันตกรรม',
                'medtech': 'คลัง LAB',
                'physical': 'คลังกายภาพ',
                'medical': 'คลังวัสดุการแพทย์'
            };

            if (window.currentRpstModalFilter !== 'all') {
                $('#rpstModalFilterBanner').removeClass('d-none');
                $('#rpstModalFilterText').text(whLabels[window.currentRpstModalFilter] || window.currentRpstModalFilter);
            } else {
                $('#rpstModalFilterBanner').addClass('d-none');
            }

            if (window.currentRpstModalData) {
                renderRpstModalTables(window.currentRpstModalFilter);
            }
        }

        function viewRpstDetail(id, name) {
            window.currentRpstModalFilter = 'all';
            $('.rpst-modal-card').removeClass('active-wh-card');
            $('.rpst-modal-card[data-wh-key="all"]').addClass('active-wh-card');
            $('#rpstModalFilterBanner').addClass('d-none');

            $('#modalRpstName').text(name || 'กำลังโหลด...');
            $('#modalRpstCode').text('');
            $('#modalRpstPeriod').text('กำลังโหลด...');
            $('#modalRpstTotalVal').text('0.00');
            $('#modalRpstMatVal').text('0.00');
            $('#modalRpstDenVal').text('0.00');
            $('#modalRpstMedtechVal').text('0.00');
            $('#modalRpstPhyVal').text('0.00');
            $('#modalRpstMedVal').text('0.00');
            $('#modalRpstReqCount').text('0 ใบ');
            $('#modalRpstItemsCount').text('0');
            $('#modalRpstReqsTabCount').text('0');
            $('#modalRpstReqsBody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-info me-2"></div> กำลังโหลดประวัติใบขอเบิก...</td></tr>');
            $('#modalRpstItemsBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-info me-2"></div> กำลังโหลดรายการสิ่งของที่เบิก...</td></tr>');

            // Switch to First Tab (ประวัติใบขอเบิก)
            var reqTab = document.querySelector('#modal-rpst-reqs-tab');
            if (reqTab && window.bootstrap) {
                var tabTrigger = new bootstrap.Tab(reqTab);
                tabTrigger.show();
            }

            const modalEl = document.getElementById('rpstDetailModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            const url = '{{ url("backoffice/warehouse/rpst-detail") }}/' + id + '?year_id={{ $year_id }}&month_id={{ $month_id }}';
            $.get(url, function(res) {
                window.currentRpstModalData = res;
                $('#modalRpstName').text(res.rpst_name);
                $('#modalRpstCode').text('รหัส: ' + (res.rpst_code || '-'));
                $('#modalRpstPeriod').text(res.period_label || '');

                if (res.summary) {
                    $('#modalRpstTotalVal').text(Number(res.summary.total_val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#modalRpstMatVal').text(Number(res.summary.material || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#modalRpstDenVal').text(Number(res.summary.dental || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#modalRpstMedtechVal').text(Number(res.summary.medtech || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#modalRpstPhyVal').text(Number(res.summary.physical || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#modalRpstMedVal').text(Number(res.summary.medical || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    $('#modalRpstReqCount').text(Number(res.summary.req_count || 0).toLocaleString() + ' ใบ');
                }

                renderRpstModalTables('all');

            }).fail(function() {
                $('#modalRpstReqsBody').html('<tr><td colspan="8" class="text-center text-danger py-4">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
                $('#modalRpstItemsBody').html('<tr><td colspan="6" class="text-center text-danger py-4">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
            });
        }

        function viewRpstItemBreakdown(whKey, itemId, code, name) {
            $('#modalItemBreakdownName').text(name || 'กำลังโหลด...');
            $('#modalItemBreakdownCode').text(code ? 'รหัส: ' + code : '-');
            $('#modalItemBreakdownWarehouse').text('-').attr('style', '');
            $('#modalItemBreakdownPeriod').text('กำลังโหลด...');
            $('#modalItemBreakdownRpstCount').text('0 แห่ง');
            $('#modalItemBreakdownTotalQty').text('0');
            $('#modalItemBreakdownUnit').text('หน่วย');
            $('#modalItemBreakdownTotalVal').text('0.00 บาท');
            $('#modalItemBreakdownWhLabel').text('-');
            $('#modalItemBreakdownFooterQty').text('0');
            $('#modalItemBreakdownFooterVal').text('0.00 บาท');
            $('#modalItemBreakdownFooterReqs').text('0 ใบ');

            if ($.fn.DataTable.isDataTable('#modal_rpst_item_breakdown_table')) {
                $('#modal_rpst_item_breakdown_table').DataTable().destroy();
            }

            $('#modalItemBreakdownBody').html('<tr><td colspan="8" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div> กำลังโหลดรายชื่อ รพ.สต. และจำนวนที่เบิก...</td></tr>');

            const modalEl = document.getElementById('rpstItemBreakdownModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }

            const url = '{{ route("backoffice.warehouse.rpst_item_breakdown") }}?warehouse_key=' + encodeURIComponent(whKey || 'material') +
                '&item_id=' + encodeURIComponent(itemId || '') +
                '&code=' + encodeURIComponent(code || '') +
                '&year_id={{ $year_id }}&month_id={{ $month_id }}';

            $.get(url, function(res) {
                if (!res || !res.item) {
                    $('#modalItemBreakdownBody').html('<tr><td colspan="8" class="text-center text-muted py-4">ไม่พบข้อมูล</td></tr>');
                    return;
                }

                $('#modalItemBreakdownName').text(res.item.name || name || '-');
                $('#modalItemBreakdownCode').text('รหัส: ' + (res.item.code || '-'));
                $('#modalItemBreakdownWarehouse').text(res.item.warehouse_name || '-')
                    .attr('style', 'background: ' + (res.item.warehouse_color || '#3b82f6') + '18; color: ' + (res.item.warehouse_color || '#3b82f6') + ';');
                $('#modalItemBreakdownPeriod').text(res.period_label || '');
                $('#modalItemBreakdownRpstCount').text((res.rpst_count || 0) + ' แห่ง');
                $('#modalItemBreakdownTotalQty').text(Number(res.item.total_qty || 0).toLocaleString());
                $('#modalItemBreakdownUnit').text(res.item.unit_name || 'หน่วย');
                $('#modalItemBreakdownTotalVal').text(Number(res.item.total_val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                $('#modalItemBreakdownWhLabel').text(res.item.warehouse_name || '-');

                var tbodyHtml = '';
                var list = res.rpst_list || [];

                if (list.length === 0) {
                    tbodyHtml = '<tr><td colspan="8" class="text-center text-muted py-4">ไม่พบประวัติการเบิกของ รพ.สต. ในช่วงเวลานี้</td></tr>';
                    $('#modalItemBreakdownBody').html(tbodyHtml);
                } else {
                    list.forEach(function(rp, idx) {
                        var rankBadge = (idx === 0) ? 'bg-warning text-dark border border-warning' :
                                        (idx === 1) ? 'bg-secondary bg-opacity-25 text-dark border' :
                                        (idx === 2) ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' :
                                        'bg-light text-muted border';

                        tbodyHtml += '<tr>' +
                            '<td class="text-center"><span class="badge rounded-circle p-1 ' + rankBadge + '" style="width: 20px; height: 20px; line-height: 12px; font-size: 0.68rem;">' + (idx + 1) + '</span></td>' +
                            '<td class="font-monospace text-muted">' + (rp.rpst_code || '-') + '</td>' +
                            '<td><div class="fw-bold text-dark">' + (rp.rpst_name || 'รพ.สต.') + '</div></td>' +
                            '<td class="text-center font-monospace"><span class="fw-bold text-dark fs-6">' + Number(rp.qty || 0).toLocaleString() + '</span> <span class="text-muted small ms-0.5">' + (res.item.unit_name || '') + '</span></td>' +
                            '<td>' +
                                '<div class="d-flex align-items-center gap-2">' +
                                    '<div class="progress flex-grow-1" style="height: 6px; background-color: #e2e8f0; border-radius: 3px;">' +
                                        '<div class="progress-bar bg-primary rounded" role="progressbar" style="width: ' + (rp.share_pct || 0) + '%;"></div>' +
                                    '</div>' +
                                    '<span class="small font-monospace text-muted" style="width: 44px; text-align: right; font-size: 0.74rem;">' + (rp.share_pct || 0) + '%</span>' +
                                '</div>' +
                            '</td>' +
                            '<td class="text-end fw-bold font-monospace text-primary">' + Number(rp.total_val || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                            '<td class="text-center"><span class="badge bg-light text-dark border px-2 py-0.5">' + Number(rp.req_count || 0) + ' ใบ</span></td>' +
                            '<td class="text-center text-muted small font-monospace">' + (rp.last_date || '-') + '</td>' +
                        '</tr>';
                    });

                    $('#modalItemBreakdownBody').html(tbodyHtml);

                    $('#modal_rpst_item_breakdown_table').DataTable({
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]],
                        order: [[3, 'desc']], // sort by quantity descending
                        dom: "<'row mb-2'<'col-sm-6'l><'col-sm-6 d-flex justify-content-end align-items-center gap-2'fB>>rt<'row mt-2'<'col-sm-6'i><'col-sm-6'p>>",
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                text: '<i class="fas fa-file-excel text-success me-1"></i>ส่งออก Excel',
                                className: 'btn btn-sm btn-outline-success rounded-pill px-3 shadow-xs',
                                title: function() {
                                    return 'สถิติการเบิก_' + ($('#modalItemBreakdownName').text() || 'รายการ') + '_' + ($('#modalItemBreakdownPeriod').text() || '');
                                },
                                footer: true
                            }
                        ],
                        language: {
                            search: "ค้นหา รพ.สต.:",
                            searchPlaceholder: "รหัส, ชื่อ รพ.สต....",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รพ.สต.",
                            infoEmpty: "ไม่มีข้อมูล",
                            infoFiltered: "(กรองจาก _MAX_ รพ.สต.)",
                            emptyTable: "ไม่พบข้อมูล",
                            zeroRecords: "ไม่พบ รพ.สต. ที่ตรงกับการค้นหา",
                            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
                        },
                        footerCallback: function (row, data, start, end, display) {
                            var api = this.api();
                            var intVal = function (i) {
                                if (typeof i === 'number') return i;
                                if (typeof i === 'string') {
                                    var clean = i.replace(/<[^>]*>?/gm, '').replace(/[\$,บาท]/g, '').trim().replace(/,/g, '');
                                    var m = clean.match(/^([\d\.]+)/);
                                    return m ? parseFloat(m[1]) : 0;
                                }
                                return 0;
                            };
                            var totalQty = api.column(3, { search: 'applied' }).data().reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                            var totalVal = api.column(5, { search: 'applied' }).data().reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                            var totalReqs = api.column(6, { search: 'applied' }).data().reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                            $('#modalItemBreakdownFooterQty').text(totalQty.toLocaleString());
                            $('#modalItemBreakdownFooterVal').text(totalVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                            $('#modalItemBreakdownFooterReqs').text(totalReqs.toLocaleString() + ' ใบ');
                        }
                    });
                }
            }).fail(function() {
                $('#modalItemBreakdownBody').html('<tr><td colspan="8" class="text-center text-danger py-4">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
            });
        }
    </script>
@endpush
