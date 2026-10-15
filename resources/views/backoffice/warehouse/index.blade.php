@extends('layouts.app')

@section('title', 'ระบบบริหารคลังพัสดุและเวชภัณฑ์ - SmartData')

@push('styles')
    <!-- DataTables & ApexCharts CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css">

    <style>
        /* Base Styling */
        .page-header-box {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
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

        /* View Mode Pills */
        .viewmode-container {
            background: #f1f5f9;
            padding: 0.35rem;
            border-radius: 12px;
            display: inline-flex;
            gap: 0.35rem;
            border: 1px solid #e2e8f0;
        }

        .viewmode-btn {
            border-radius: 9px;
            padding: 0.45rem 1rem;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #64748b;
        }

        .viewmode-btn.active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
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
    </style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Header & Filter Bar -->
    <div class="page-header-box">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-warning bg-opacity-15 text-warning fw-bold px-2 py-1 rounded-pill" style="font-size: 0.78rem;">
                        <i class="fas fa-warehouse me-1"></i> ระบบบริหารงานคลังและเวชภัณฑ์
                    </span>
                    <span class="text-muted small">•</span>
                    <span class="text-muted small">ปีงบประมาณ {{ $year_id }}</span>
                    <span class="text-muted small">•</span>
                    <span class="badge bg-info bg-opacity-10 text-info px-2 py-0.5 rounded-pill small">
                        เกณฑ์มาตรฐาน สธ. / HA
                    </span>
                </div>
                <h4 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-boxes-stacked text-warning"></i>
                    <span>ระบบบริหารและติดตามคลังพัสดุ - เวชภัณฑ์ (5 คลังหลัก & คลังย่อย)</span>
                </h4>
            </div>

            <!-- Main Filter Form -->
            <form action="{{ route('backoffice.warehouse.index') }}" method="GET" id="warehouseFilterForm" class="d-flex flex-wrap align-items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="hidden" name="month_id" value="{{ $month_id }}">
                <input type="hidden" name="view_mode" value="{{ $view_mode }}">

                <!-- Fiscal Year Selector -->
                <div class="d-flex align-items-center gap-1">
                    <label class="text-muted small fw-bold text-nowrap mb-0">ปีงบประมาณ:</label>
                    <select name="year_id" id="year_id" class="form-select form-select-sm fw-bold shadow-sm" style="width: 105px; border-radius: 8px;" onchange="this.form.submit()">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == $year_id ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sub-Warehouse Filter -->
                <div class="d-flex align-items-center gap-1">
                    <label class="text-muted small fw-bold text-nowrap mb-0">คลังย่อย:</label>
                    <select name="sub_warehouse_id" id="sub_warehouse_id" class="form-select form-select-sm shadow-sm" style="max-width: 220px; border-radius: 8px;" onchange="this.form.submit()">
                        <option value="all">-- ทุกคลังย่อย (ทุกหน่วยงาน) --</option>
                        @foreach($subWarehouses as $sw)
                            <option value="{{ $sw->id }}" {{ (string)$sw->id === (string)$sub_warehouse_id ? 'selected' : '' }}>
                                {{ $sw->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Input -->
                <div class="input-group input-group-sm" style="width: 180px;">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาใบเบิก/พัสดุ..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>

                @if($month_id !== 'all' || $sub_warehouse_id !== 'all' || !empty($search))
                    <a href="{{ route('backoffice.warehouse.index', ['tab' => $tab, 'year_id' => $year_id]) }}" class="btn btn-light btn-sm text-muted shadow-sm" title="รีเซ็ตตัวกรอง">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- 6 Main Warehouse Tabs Navigation -->
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
    </ul>

    <!-- Month Sub-tabs Navigation Bar -->
    <div class="month-subtabs-box d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
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

        @if($tab !== 'overview')
            <!-- Sub-navigation: View Mode Switcher -->
            <div class="viewmode-container">
                <a href="{{ route('backoffice.warehouse.index', array_merge(request()->except('view_mode'), ['view_mode' => 'overview'])) }}"
                   class="viewmode-btn {{ $view_mode === 'overview' ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i> <span>ใบเบิกพัสดุ</span>
                </a>
                <a href="{{ route('backoffice.warehouse.index', array_merge(request()->except('view_mode'), ['view_mode' => 'stock'])) }}"
                   class="viewmode-btn {{ $view_mode === 'stock' ? 'active' : '' }}">
                    <i class="fas fa-cubes"></i> <span>สต็อกจริง 2 ระดับ</span>
                </a>
                <a href="{{ route('backoffice.warehouse.index', array_merge(request()->except('view_mode'), ['view_mode' => 'planner'])) }}"
                   class="viewmode-btn {{ $view_mode === 'planner' ? 'active' : '' }}">
                    <i class="fas fa-cart-shopping text-danger"></i> <span>แผนจัดซื้อรอบถัดไป</span>
                </a>
            </div>
        @endif
    </div>

    <!-- MAIN BODY CONTENT -->
    @if($tab === 'overview')
        {{-- ========================================================================= --}}
        {{-- OVERVIEW TAB: CONSOLIDATED 5 WAREHOUSES EXECUTIVE DASHBOARD --}}
        {{-- ========================================================================= --}}
        
        <!-- Consolidated 5 Main Warehouses Status (รับเข้า • เบิกจ่าย • คงคลัง) -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fas fa-table-columns text-primary"></i>
                <span>สรุปเปรียบเทียบ 5 คลังหลัก (รับเข้า • เบิกจ่าย • คงคลัง)</span>
            </h6>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-white text-secondary border shadow-xs px-3 py-2 rounded-pill fw-normal" style="font-size: 0.8rem;">
                    รวมทั้ง รพ.: 
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
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge rounded-pill fw-bold" style="background-color: {{ $ws['config']['color'] }}15; color: {{ $ws['config']['color'] }}; font-size: 0.8rem; padding: 6px 10px;">
                                <i class="fas {{ $ws['config']['icon'] }} me-1"></i> {{ $ws['config']['name'] }}
                            </span>
                            <span class="text-muted small" style="font-size: 0.72rem;">{{ number_format($ws['items_count']) }} รายการ</span>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-3">
                            <!-- รับเข้า -->
                            <div class="p-2 rounded-3 bg-light d-flex justify-content-between align-items-center border border-light">
                                <span class="text-muted small fw-medium" style="font-size: 0.78rem;">
                                    <i class="fas fa-file-arrow-down text-primary me-1"></i> รับเข้า
                                </span>
                                <span class="fw-bold text-dark" style="font-size: 0.86rem;">
                                    {{ number_format($ws['receive_val'], 2) }} <span class="fw-normal text-muted" style="font-size: 0.7rem;">฿</span>
                                </span>
                            </div>

                            <!-- เบิกจ่าย -->
                            <div class="p-2 rounded-3 bg-light d-flex justify-content-between align-items-center border border-light">
                                <span class="text-muted small fw-medium" style="font-size: 0.78rem;">
                                    <i class="fas fa-file-arrow-up text-warning me-1"></i> เบิกจ่าย
                                </span>
                                <span class="fw-bold text-dark" style="font-size: 0.86rem;">
                                    {{ number_format($ws['disbursement_val'], 2) }} <span class="fw-normal text-muted" style="font-size: 0.7rem;">฿</span>
                                </span>
                            </div>

                            <!-- คงคลัง -->
                            <div class="p-2 rounded-3 d-flex justify-content-between align-items-center" style="background-color: #ecfdf5; border: 1px solid #a7f3d0;">
                                <span class="small fw-bold" style="color: #065f46; font-size: 0.78rem;">
                                    <i class="fas fa-boxes-stacked me-1 text-success"></i> คงคลัง
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
                                    <span>แนวโน้มการเบิกจ่ายรายวัน (Daily Trend) ประจำเดือน{{ $selectedMonthName }} {{ $selectedCalYear }}</span>
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
                            <span>คลังย่อยที่เบิกจ่ายสูงสุด{{ ($isMonthlyView ?? false) ? ' ประจำเดือน' . $selectedMonthName : ' (Top Wards)' }}</span>
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

    @else
        {{-- ========================================================================= --}}
        {{-- SPECIFIC WAREHOUSE TAB: MATERIAL / DENTAL / MEDTECH / PHYSICAL / MEDICAL --}}
        {{-- ========================================================================= --}}
        
        <!-- KPI Row for Specific Warehouse -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Disbursement Value -->
            <div class="col-xl-3 col-md-6">
                <div class="card wh-kpi-card p-3 h-100 border-start border-4" style="border-start-color: {{ $config['color'] }} !important;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">มูลค่าเบิกจ่ายสะสม</div>
                            <h3 class="fw-bold mb-0 mt-1" style="color: {{ $config['color'] }};">
                                {{ number_format($totalDisbursement, 2) }} <span class="fs-6 fw-normal text-muted">บาท</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                รวม {{ number_format($totalReqCount) }} ใบเบิก | ปีงบ {{ $year_id }}
                            </div>
                        </div>
                        <div class="wh-icon-box" style="background-color: {{ $config['color'] }}15; color: {{ $config['color'] }};">
                            <i class="fas {{ $config['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Requisitions Pipeline -->
            <div class="col-xl-3 col-md-6">
                <div class="card wh-kpi-card p-3 h-100 border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">สถานะใบขอเบิก</div>
                            <h3 class="fw-bold text-warning mb-0 mt-1">
                                {{ number_format(($statusCounts['Pending'] ?? 0) + ($statusCounts['Approve'] ?? 0)) }} <span class="fs-6 fw-normal text-muted">เรื่องค้าง</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                รอหน.เห็นชอบ {{ $statusCounts['Pending'] ?? 0 }} | รอจ่าย {{ $statusCounts['Approve'] ?? 0 }}
                            </div>
                        </div>
                        <div class="wh-icon-box bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Critical Reorder Needed -->
            <div class="col-xl-3 col-md-6">
                <div class="card wh-kpi-card p-3 h-100 border-start border-4 border-danger">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">🔴 รายการต้องซื้อด่วน (รอบนี้)</div>
                            <h3 class="fw-bold text-danger mb-0 mt-1">
                                {{ number_format(count($reorderItems)) }} <span class="fs-6 fw-normal text-muted">รายการ</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                งบประมาณที่ต้องใช้: <strong>{{ number_format($totalEstimatedBudget, 2) }} ฿</strong>
                            </div>
                        </div>
                        <div class="wh-icon-box bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Inventory Turnover & MOS -->
            <div class="col-xl-3 col-md-6">
                <div class="card wh-kpi-card p-3 h-100 border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-bold text-uppercase">เกณฑ์มาตรฐาน สธ.</div>
                            <h3 class="fw-bold text-success mb-0 mt-1">
                                {{ $config['standard_itr'] }} <span class="fs-6 fw-normal text-muted">รอบ/ปี</span>
                            </h3>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                สำรองคลังเป้าหมาย: {{ $config['standard_mos_min'] }} - {{ $config['standard_mos_max'] }} เดือน
                            </div>
                        </div>
                        <div class="wh-icon-box bg-success bg-opacity-10 text-success">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($view_mode === 'overview')
            {{-- MODE 1: REQUISITION TRACKING & CHARTS --}}
            <div class="row g-4 mb-4">
                <!-- Trend Chart: 12-Month if all year, Daily Trend if specific month -->
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100 overflow-hidden">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2">
                                    <i class="fas fa-chart-line" style="color: {{ $config['color'] }};"></i>
                                    @if($isMonthlyView ?? false)
                                        <span>แนวโน้มการเบิกจ่ายรายวัน - เดือน{{ $selectedMonthName }} {{ $selectedCalYear }}</span>
                                    @else
                                        <span>แนวโน้มมูลค่าเบิกจ่ายรายเดือน 12 เดือน - {{ $config['full_name'] }}</span>
                                    @endif
                                </h6>
                                <div class="text-muted small mt-0.5" style="font-size: 0.74rem;">
                                    @if($isMonthlyView ?? false)
                                        วิเคราะห์การเบิกจ่ายในแต่ละวันของเดือน{{ $selectedMonthName }}
                                    @else
                                        วิเคราะห์การเบิกจ่ายรายเดือนตลอดปีงบประมาณ {{ $year_id }}
                                    @endif
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                                @if($isMonthlyView ?? false)
                                    <i class="fas fa-calendar-day me-1 text-primary"></i> รายวัน (Daily)
                                @else
                                    <i class="fas fa-calendar me-1 text-warning"></i> 12 เดือน (ต.ค. - ก.ย.)
                                @endif
                            </span>
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

        @elseif($view_mode === 'stock')
            {{-- MODE 2: TWO-TIER STOCK (MAIN WAREHOUSE VS SUB-WAREHOUSE) --}}
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

        @else
            {{-- MODE 3: SMART PROCUREMENT PLANNER & REORDER ALERT --}}
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
                                @forelse($reorderItems as $idx => $item)
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
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="fas fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                            สต็อกสินค้าอยู่ในเกณฑ์ปลอดภัยครบทุกรายการ ไม่พบรายการที่ต้องสั่งซื้อด่วนในรอบนี้
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    @endif

</div>

<!-- Modal 1: Requisition Detail Line Items -->
<div class="modal fade" id="requisitionDetailModal" tabindex="-1" aria-hidden="true">
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

@endsection

@push('scripts')
    <!-- jQuery, DataTables & ApexCharts JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
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
                            { name: 'คลังวัสดุเทคนิคการแพทย์', data: medtechSeries },
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
                            { name: 'คลังวัสดุเทคนิคการแพทย์', data: medtechSeries },
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
            // 2. Single Warehouse Tab Chart (Daily in Month View, 12-Month in Full Year)
            // =========================================================================
            @if($tab !== 'overview' && $view_mode === 'overview')
                @if($isMonthlyView ?? false)
                    var dailyTrendData = @json($dailyTrend ?? []);
                    var chartLabels = dailyTrendData.map(function(d) { return 'ว.' + d.day; });
                    var valSeries = dailyTrendData.map(function(d) { return d.val; });
                    var chartType = 'area';
                @else
                    var singleTrendData = @json($monthlyTrend ?? []);
                    var chartLabels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
                    var valSeries = singleTrendData.map(function(d) { return d.val; });
                    var chartType = 'bar';
                @endif

                var singleOptions = {
                    series: [{ name: 'มูลค่าเบิกจ่าย (บาท)', data: valSeries }],
                    chart: {
                        type: chartType,
                        height: 280,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        fontFamily: 'inherit'
                    },
                    colors: ['{{ $config["color"] ?? "#f59e0b" }}'],
                    @if($isMonthlyView ?? false)
                        stroke: { curve: 'smooth', width: 2.5 },
                        fill: {
                            type: 'gradient',
                            gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [0, 90, 100] }
                        },
                    @else
                        plotOptions: {
                            bar: { borderRadius: 6, columnWidth: '45%', distributed: false }
                        },
                    @endif
                    dataLabels: { enabled: false },
                    xaxis: { categories: chartLabels },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M ฿';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K ฿';
                                return value + ' ฿';
                            }
                        }
                    },
                    tooltip: {
                        y: { formatter: function (val) { return Number(val).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' บาท'; } }
                    }
                };

                var singleChartEl = document.querySelector("#warehouse_single_trend_chart");
                if (singleChartEl) {
                    var singleChart = new ApexCharts(singleChartEl, singleOptions);
                    singleChart.render();
                }
            @endif
        });

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
    </script>
@endpush
