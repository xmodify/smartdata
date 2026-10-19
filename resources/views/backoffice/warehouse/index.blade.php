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
        .wh-nav-tabs .nav-link.active-drug     { background: linear-gradient(135deg, #059669, #047857) !important; }
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
        /* MOPH F5 Modal & Tabs Custom Styling */
        #uploadMophF5Modal .nav-pills .nav-link {
            color: #4b5563;
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
            font-size: 0.82rem;
            padding: 0.4rem 0.85rem;
        }
        #uploadMophF5Modal .nav-pills .nav-link:hover {
            background-color: #e5e7eb;
            color: #111827;
        }
        #uploadMophF5Modal .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #059669, #047857) !important;
            color: #ffffff !important;
            border-color: #059669 !important;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.25);
        }
        #uploadMophF5Modal .nav-pills .nav-link.active .badge {
            background-color: rgba(255, 255, 255, 0.28) !important;
            color: #ffffff !important;
        }
        #uploadMophF5Modal table.dataTable {
            width: 100% !important;
            margin-bottom: 0 !important;
        }
        #uploadMophF5Modal table.dataTable thead th {
            font-size: 0.76rem !important;
            white-space: nowrap !important;
            background-color: #f8fafc !important;
            color: #334155 !important;
            padding: 0.38rem 0.5rem !important;
        }
        #uploadMophF5Modal table.dataTable tbody td {
            font-size: 0.76rem !important;
            padding: 0.28rem 0.5rem !important;
            line-height: 1.3 !important;
        }
        #uploadMophF5Modal table.dataTable tfoot th {
            font-size: 0.78rem !important;
            background-color: #f1f5f9 !important;
            padding: 0.35rem 0.5rem !important;
        }
        #uploadMophF5Modal .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px !important;
            padding: 0.22rem 0.75rem !important;
            font-size: 0.78rem !important;
        }
        #uploadMophF5Modal .dataTables_wrapper .dataTables_filter label {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.35rem !important;
            margin-bottom: 0 !important;
            font-size: 0.78rem !important;
            font-weight: 500 !important;
            color: #4b5563 !important;
        }
        #uploadMophF5Modal .dataTables_wrapper .dt-buttons {
            display: inline-flex !important;
            align-items: center !important;
            margin-bottom: 0 !important;
        }
        #uploadMophF5Modal .dataTables_wrapper .dataTables_length select {
            border-radius: 12px !important;
            padding: 0.22rem 1.6rem 0.22rem 0.6rem !important;
            font-size: 0.78rem !important;
        }
        /* Official Vibrant Microsoft Excel Button Styling */
        #uploadMophF5Modal .dt-buttons .btn-f5-excel,
        #uploadMophF5Modal .btn-f5-excel {
            background: linear-gradient(135deg, #107c41 0%, #15803d 100%) !important;
            color: #ffffff !important;
            border: 1px solid #0f5132 !important;
            font-weight: 600 !important;
            font-size: 0.78rem !important;
            padding: 0.28rem 0.9rem !important;
            border-radius: 50rem !important;
            box-shadow: 0 2px 4px rgba(16, 124, 65, 0.25) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.35rem !important;
            transition: all 0.2s ease !important;
            text-decoration: none !important;
        }
        #uploadMophF5Modal .dt-buttons .btn-f5-excel:hover,
        #uploadMophF5Modal .btn-f5-excel:hover {
            background: linear-gradient(135deg, #15803d 0%, #166534 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(16, 124, 65, 0.35) !important;
            transform: translateY(-1px);
        }
        /* F5 Modal Dialog & Content Layout */
        #uploadMophF5Modal .modal-dialog {
            max-width: 92vw !important;
            width: 92vw !important;
            height: 88vh !important;
            margin: 2vh auto !important;
        }
        #uploadMophF5Modal .modal-content {
            height: 100% !important;
            max-height: 88vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        #uploadMophF5Modal .modal-body {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
            padding: 0.65rem 0.85rem !important;
        }
        #uploadMophF5Modal .tab-content {
            flex: 1 1 auto !important;
            height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }
        #uploadMophF5Modal .tab-pane {
            height: 100% !important;
            min-height: 0 !important;
        }
        #uploadMophF5Modal .tab-pane.active:not(#tab-f5-upload) {
            display: flex !important;
            flex-direction: column !important;
        }
        #tab-f5-upload {
            overflow-y: auto !important;
        }

        /* F5 Data Cards (Tabs 1-5) */
        .f5-data-card {
            height: 100% !important;
            min-height: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            padding: 0.65rem 0.85rem !important;
            background-color: #ffffff;
        }
        .f5-data-card .dataTables_wrapper {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 auto !important;
            height: 100% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        /* F5 Scrollable Table Container (with smooth custom scrollbars) */
        .f5-table-scroll-container {
            flex: 1 1 auto !important;
            overflow-y: auto !important;
            overflow-x: auto !important;
            min-height: 0 !important;
            position: relative !important;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #ffffff;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }
        .f5-table-scroll-container table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
            margin-bottom: 0 !important;
        }
        .f5-table-scroll-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .f5-table-scroll-container::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 4px;
        }
        .f5-table-scroll-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .f5-table-scroll-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sticky Header for all F5 Tables */
        .f5-table-scroll-container table thead th {
            position: sticky !important;
            top: 0 !important;
            z-index: 10 !important;
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
            border-bottom: 2px solid #cbd5e1 !important;
        }

        /* Sticky Footer for Total Row in F5 Tables */
        .f5-table-scroll-container table tfoot th {
            position: sticky !important;
            bottom: 0 !important;
            z-index: 9 !important;
            background-color: #f8fafc !important;
            box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.08) !important;
            border-top: 2px solid #cbd5e1 !important;
        }

        /* F5 Pagination & Info Bottom Row - Always pinned at bottom */
        .f5-dt-bottom-row {
            flex-shrink: 0 !important;
            padding-top: 0.35rem !important;
            padding-bottom: 0.15rem !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0 !important;
            background: #ffffff !important;
            border-top: 1px solid #f1f5f9 !important;
        }
        #uploadMophF5Modal .dataTables_info {
            font-size: 0.78rem !important;
            padding-top: 0.2rem !important;
            padding-bottom: 0.2rem !important;
            color: #64748b !important;
            margin: 0 !important;
        }
        #uploadMophF5Modal .dataTables_paginate {
            padding-top: 0.2rem !important;
            padding-bottom: 0.2rem !important;
            margin: 0 !important;
        }
        #uploadMophF5Modal .dataTables_paginate .pagination {
            margin-bottom: 0 !important;
            gap: 2px !important;
        }
        #uploadMophF5Modal .dataTables_paginate .page-link {
            padding: 0.22rem 0.55rem !important;
            font-size: 0.76rem !important;
            border-radius: 6px !important;
            color: #107c41 !important;
            border-color: #e2e8f0 !important;
        }
        #uploadMophF5Modal .dataTables_paginate .page-item.active .page-link {
            background-color: #107c41 !important;
            border-color: #107c41 !important;
            color: #ffffff !important;
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
        <!-- Tab 0: ภาพรวม 6 คลัง -->
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'overview' ? 'active active-overview' : '' }}" 
               href="{{ route('backoffice.warehouse.index', array_merge(request()->except('tab'), ['tab' => 'overview'])) }}">
                <i class="fas fa-layer-group"></i>
                <span>ภาพรวม 6 คลัง</span>
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
                    <span>สรุปเปรียบเทียบ 6 คลังหลัก (รับเข้า • เบิกจ่าย • คงคลัง)</span>
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
                    <span class="text-success fw-bold" title="คงคลัง"><i class="fas fa-boxes-stacked me-1"></i>คงคลัง{{ ($isMonthlyView ?? false) ? ' (ณ สิ้นเดือน' . $selectedMonthName . ')' : ' (ณ วันที่ล่าสุด)' }} {{ number_format($totalHospitalStock, 2) }} ฿</span>
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
                                <span class="text-muted small fw-medium text-truncate me-1" style="font-size: 0.78rem;">
                                    <i class="fas fa-file-arrow-down text-primary me-1"></i> รับเข้า{{ ($isMonthlyView ?? false) ? ' (' . ($thaiMonthsShort[(int)$month_id] ?? $selectedMonthName) . ')' : ' (ทั้งปี)' }}
                                </span>
                                <span class="fw-bold text-dark text-nowrap" style="font-size: 0.86rem;">
                                    {{ number_format($ws['receive_val'], 2) }} <span class="fw-normal text-muted" style="font-size: 0.7rem;">฿</span>
                                </span>
                            </div>

                            <!-- เบิกจ่าย -->
                            <div class="p-2 rounded-3 bg-light d-flex justify-content-between align-items-center border border-light">
                                <span class="text-muted small fw-medium text-truncate me-1" style="font-size: 0.78rem;">
                                    <i class="fas fa-file-arrow-up text-warning me-1"></i> เบิกจ่าย{{ ($isMonthlyView ?? false) ? ' (' . ($thaiMonthsShort[(int)$month_id] ?? $selectedMonthName) . ')' : ' (ทั้งปี)' }}
                                </span>
                                <span class="fw-bold text-dark text-nowrap" style="font-size: 0.86rem;">
                                    {{ number_format($ws['disbursement_val'], 2) }} <span class="fw-normal text-muted" style="font-size: 0.7rem;">฿</span>
                                </span>
                            </div>

                            <!-- คงคลัง -->
                            <div class="p-2 rounded-3 d-flex justify-content-between align-items-center" style="background-color: #ecfdf5; border: 1px solid #a7f3d0;">
                                <span class="small fw-bold text-truncate me-1" style="color: #065f46; font-size: 0.78rem;">
                                    <i class="fas fa-boxes-stacked me-1 text-success"></i> คงคลัง ({{ $ws['stock_date_label'] ?? 'ณ วันที่ล่าสุด' }})
                                </span>
                                <span class="fw-bold text-nowrap" style="color: #047857; font-size: 0.88rem;">
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
                                    เปรียบเทียบการเบิกจ่ายรายวันของ 6 คลังหลัก (ว.1 – ว.{{ count($dailyTrend ?? []) }})
                                @else
                                    เปรียบเทียบการเบิกจ่ายรายเดือนตลอดปีงบประมาณของ 6 คลังหลัก (ต.ค. - ก.ย.)
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-light text-muted border px-2.5 py-1 font-monospace" style="font-size: 0.75rem;">
                            @if($isMonthlyView ?? false)
                                <i class="fas fa-calendar-day me-1 text-primary"></i> รายวัน (Daily) 6 คลัง
                            @else
                                <i class="fas fa-calendar me-1 text-warning"></i> เปรียบเทียบ 6 คลัง (12 เดือน)
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
        {{-- SPECIFIC WAREHOUSE TAB: DRUG / MATERIAL / DENTAL / MEDTECH / PHYSICAL / MEDICAL --}}
        {{-- ========================================================================= --}}
        
        <!-- Warehouse Header & Action Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <span class="badge rounded-pill px-3 py-1.5" style="background-color: {{ $config['color'] }}18; color: {{ $config['color'] }}; font-size: 0.95rem; border: 1px solid {{ $config['color'] }}30;">
                        <i class="fas {{ $config['icon'] }} me-1.5"></i> {{ $config['full_name'] }}
                    </span>
                </h5>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if($tab === 'drug')
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 py-1.5 shadow-sm fw-bold d-inline-flex align-items-center gap-2" 
                            data-bs-toggle="modal" data-bs-target="#uploadMophF5Modal">
                        <i class="fas fa-cloud-arrow-up"></i>
                        <span>นำเข้า 5 แฟ้มยา</span>
                    </button>
                @endif
                <a href="{{ route('backoffice.warehouse.export_reorder', ['tab' => $tab, 'year_id' => $year_id]) }}" 
                   class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5">
                    <i class="fas fa-file-excel text-success"></i>
                    <span>ส่งออกแผนจัดซื้อ (CSV)</span>
                </a>
            </div>
        </div>

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
                                    <span>มูลค่าคงคลัง ({{ $stockDateLabel ?? (($isMonthlyView ?? false) ? 'ณ สิ้นเดือน' . $selectedMonthName : 'ณ วันที่ล่าสุด') }})</span>
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
                                                       onclick="viewItemDepartments('{{ $config['type'] }}', '{{ $tItem->item_id ?? 0 }}', '{{ addslashes($tItem->name) }}')"
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
                                                            onclick="viewItemDepartments('{{ $config['type'] }}', '{{ $tItem->item_id ?? 0 }}', '{{ addslashes($tItem->name) }}')"
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
                        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2 flex-wrap">
                            <i class="fas fa-cubes" style="color: {{ $config['color'] }};"></i>
                            <span>ระบบตรวจสอบตัวเลขสต็อกจริง 2 ระดับ (คลังหลัก vs คลังย่อยประจำแผนก)</span>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-0.5 rounded-pill small">Real-time Multi-tier</span>
                            @if(!empty($stockDateLabel))
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-0.5 rounded-pill small">
                                    <i class="fas fa-calendar-check me-1"></i>{{ $stockDateLabel }}
                                </span>
                            @endif
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
                        <h6 class="m-0 fw-bold text-dark d-flex align-items-center gap-2 flex-wrap">
                            <i class="fas fa-cart-shopping text-danger"></i>
                            <span>ข้อเสนอแนะแผนจัดซื้อพัสดุ/เวชภัณฑ์ ประจำรอบเดือนถัดไป (Smart Reorder Matrix)</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-0.5 rounded-pill small">
                                อิงเกณฑ์มาตรฐาน สธ.
                            </span>
                            @if(!empty($stockDateLabel))
                                <span class="badge bg-light text-secondary border px-2.5 py-0.5 rounded-pill small">
                                    <i class="fas fa-calendar-check me-1 text-success"></i>สต็อกฐาน: {{ $stockDateLabel }}
                                </span>
                            @endif
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

<!-- Modal 8: Upload & View MOPH 5 Files (Drug Warehouse INVS) -->
<div class="modal fade" id="uploadMophF5Modal" tabindex="-1" aria-labelledby="uploadMophF5ModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 92vw; width: 92vw; height: 88vh; margin: 2vh auto;">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden h-100 d-flex flex-column">
            
            <!-- Modal Header -->
            <div class="modal-header py-2.5 px-4 text-white flex-shrink-0" style="background: linear-gradient(135deg, #059669, #047857);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center bg-white bg-opacity-20 rounded-circle" style="width: 38px; height: 38px;">
                        <i class="fas fa-file-excel fs-5 text-white"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="modal-title fw-bold m-0" id="uploadMophF5ModalLabel">ศูนย์จัดการและตรวจสอบ 5 แฟ้มยา (INVS Drug Warehouse)</h6>
                            <span class="badge bg-white bg-opacity-25 text-white fw-normal" style="font-size: 0.72rem;">มาตรฐาน สป.สธ.</span>
                        </div>
                        <small class="text-white-50" style="font-size: 0.75rem;">ระบบนำเข้า ตรวจสอบ และดูข้อมูลรายแฟ้ม (druglist, purchaseplan, receipt, distribution, inventory)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="btnCloseMophF5Modal"></button>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="bg-white border-bottom px-4 pt-2 pb-2 flex-shrink-0 shadow-2xs">
                <ul class="nav nav-pills gap-1 flex-nowrap overflow-auto" id="mophF5ModalNavTabs" role="tablist" style="scrollbar-width: thin;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active px-3 py-1.5 rounded-pill fw-semibold small f5-tab-btn text-nowrap" 
                                id="tab-f5-upload-tab" data-bs-toggle="pill" data-bs-target="#tab-f5-upload" type="button" role="tab" data-f5-file="upload">
                            <i class="fas fa-cloud-arrow-up me-1"></i> นำเข้าไฟล์ (Upload)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-1.5 rounded-pill fw-semibold small f5-tab-btn text-nowrap" 
                                id="tab-f5-druglist-tab" data-bs-toggle="pill" data-bs-target="#tab-f5-druglist" type="button" role="tab" data-f5-file="druglist">
                            <i class="fas fa-pills me-1"></i> 1. ทะเบียนยา (druglist)
                            <span class="badge bg-secondary rounded-pill ms-1 font-monospace" id="f5TabBadge_druglist">{{ number_format($f5TableCounts['druglist'] ?? 0) }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-1.5 rounded-pill fw-semibold small f5-tab-btn text-nowrap" 
                                id="tab-f5-purchaseplan-tab" data-bs-toggle="pill" data-bs-target="#tab-f5-purchaseplan" type="button" role="tab" data-f5-file="purchaseplan">
                            <i class="fas fa-file-invoice-dollar me-1"></i> 2. แผนจัดซื้อ (purchaseplan)
                            <span class="badge bg-secondary rounded-pill ms-1 font-monospace" id="f5TabBadge_purchaseplan">{{ number_format($f5TableCounts['purchaseplan'] ?? 0) }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-1.5 rounded-pill fw-semibold small f5-tab-btn text-nowrap" 
                                id="tab-f5-receipt-tab" data-bs-toggle="pill" data-bs-target="#tab-f5-receipt" type="button" role="tab" data-f5-file="receipt">
                            <i class="fas fa-truck-ramp-box me-1"></i> 3. รับเข้าคลัง (receipt)
                            <span class="badge bg-secondary rounded-pill ms-1 font-monospace" id="f5TabBadge_receipt">{{ number_format($f5TableCounts['receipt'] ?? 0) }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-1.5 rounded-pill fw-semibold small f5-tab-btn text-nowrap" 
                                id="tab-f5-distribution-tab" data-bs-toggle="pill" data-bs-target="#tab-f5-distribution" type="button" role="tab" data-f5-file="distribution">
                            <i class="fas fa-boxes-packing me-1"></i> 4. เบิกจ่ายยา (distribution)
                            <span class="badge bg-secondary rounded-pill ms-1 font-monospace" id="f5TabBadge_distribution">{{ number_format($f5TableCounts['distribution'] ?? 0) }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-3 py-1.5 rounded-pill fw-semibold small f5-tab-btn text-nowrap" 
                                id="tab-f5-inventory-tab" data-bs-toggle="pill" data-bs-target="#tab-f5-inventory" type="button" role="tab" data-f5-file="inventory">
                            <i class="fas fa-warehouse me-1"></i> 5. ยาคงคลัง (inventory)
                            <span class="badge bg-secondary rounded-pill ms-1 font-monospace" id="f5TabBadge_inventory">{{ number_format($f5TableCounts['inventory'] ?? 0) }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Modal Body (Flex column container without outer scroll) -->
            <div class="modal-body p-2.5 p-md-3 flex-grow-1 overflow-hidden bg-light bg-opacity-50 d-flex flex-column min-h-0">
                <div class="tab-content flex-grow-1 h-100 overflow-hidden min-h-0" id="mophF5ModalTabContent">
                    
                    <!-- TAB 0: UPLOAD FILES -->
                    <div class="tab-pane fade show active h-100 overflow-auto" id="tab-f5-upload" role="tabpanel" aria-labelledby="tab-f5-upload-tab">
                        <form id="mophF5UploadForm" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- File drop / select container -->
                            <div class="p-4 rounded-4 border-2 border-dashed text-center mb-3 bg-white" id="mophF5Dropzone" 
                                 style="border-color: #cbd5e1; cursor: pointer; transition: all 0.2s ease;">
                                <div class="mb-2">
                                    <i class="fas fa-cloud-arrow-up text-success fa-3x mb-2"></i>
                                    <h6 class="fw-bold text-dark mb-1">คลิกเลือกไฟล์ หรือลากไฟล์ .xlsx ทั้ง 5 แฟ้มมาวางที่นี่</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.82rem;">
                                        สามารถเลือกไฟล์ทั้ง 5 แฟ้มยาพร้อมกันทีเดียว (กด Ctrl + คลิกเลือกไฟล์ หรือลากคลุมไฟล์)
                                    </p>
                                </div>
                                <input type="file" id="mophF5FileInput" name="files[]" multiple accept=".xlsx,.zip" class="d-none">
                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 mt-2 fw-semibold" onclick="$('#mophF5FileInput').click();">
                                    <i class="fas fa-folder-open me-1"></i> เลือกไฟล์ .xlsx จากคอมพิวเตอร์
                                </button>
                            </div>

                            <!-- 5 Files Checklist Status Cards -->
                            <div class="card border rounded-3 bg-white p-3 mb-3 shadow-2xs">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-dark">
                                        <i class="fas fa-list-check me-1 text-primary"></i> แฟ้มมาตรฐาน 5 แฟ้มที่ตรวจพบ:
                                    </span>
                                    <span class="badge bg-secondary rounded-pill" id="mophF5CountBadge">เลือก 0 / 5 แฟ้ม</span>
                                </div>
                                <div class="row g-2" id="mophF5FileListCards">
                                    <!-- Druglist -->
                                    <div class="col-sm-6 col-md-4">
                                        <div class="p-2 rounded-3 border bg-light d-flex align-items-center gap-2 f5-badge-card" id="badge_druglist">
                                            <i class="fas fa-circle-question text-muted fs-5 f5-icon"></i>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark small text-truncate">1. ทะเบียนยา (druglist)</div>
                                                <div class="text-muted f5-filename text-truncate" style="font-size: 0.72rem;">ยังไม่ได้เลือกไฟล์</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Purchaseplan -->
                                    <div class="col-sm-6 col-md-4">
                                        <div class="p-2 rounded-3 border bg-light d-flex align-items-center gap-2 f5-badge-card" id="badge_purchaseplan">
                                            <i class="fas fa-circle-question text-muted fs-5 f5-icon"></i>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark small text-truncate">2. แผนจัดซื้อ (purchaseplan)</div>
                                                <div class="text-muted f5-filename text-truncate" style="font-size: 0.72rem;">ยังไม่ได้เลือกไฟล์</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Receipt -->
                                    <div class="col-sm-6 col-md-4">
                                        <div class="p-2 rounded-3 border bg-light d-flex align-items-center gap-2 f5-badge-card" id="badge_receipt">
                                            <i class="fas fa-circle-question text-muted fs-5 f5-icon"></i>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark small text-truncate">3. รับเข้าคลัง (receipt)</div>
                                                <div class="text-muted f5-filename text-truncate" style="font-size: 0.72rem;">ยังไม่ได้เลือกไฟล์</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Distribution -->
                                    <div class="col-sm-6 col-md-6">
                                        <div class="p-2 rounded-3 border bg-light d-flex align-items-center gap-2 f5-badge-card" id="badge_distribution">
                                            <i class="fas fa-circle-question text-muted fs-5 f5-icon"></i>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark small text-truncate">4. เบิกจ่ายยา (distribution)</div>
                                                <div class="text-muted f5-filename text-truncate" style="font-size: 0.72rem;">ยังไม่ได้เลือกไฟล์</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Inventory -->
                                    <div class="col-sm-12 col-md-6">
                                        <div class="p-2 rounded-3 border bg-light d-flex align-items-center gap-2 f5-badge-card" id="badge_inventory">
                                            <i class="fas fa-circle-question text-muted fs-5 f5-icon"></i>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark small text-truncate">5. ยาคงคลัง (inventory)</div>
                                                <div class="text-muted f5-filename text-truncate" style="font-size: 0.72rem;">ยังไม่ได้เลือกไฟล์</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Progress Bar Container (Hidden by default) -->
                            <div id="mophF5ProgressContainer" style="display: none;" class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-bold text-dark" id="mophF5ProgressStage">
                                        <i class="fas fa-spinner fa-spin text-success me-1"></i> กำลังอัปโหลดไฟล์...
                                    </span>
                                    <span class="fw-bold text-success font-monospace" id="mophF5ProgressPct">0%</span>
                                </div>
                                <div class="progress shadow-xs" style="height: 18px; border-radius: 20px; background-color: #e2e8f0;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success fw-bold" 
                                         id="mophF5ProgressBar" role="progressbar" style="width: 0%; font-size: 0.75rem;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                </div>
                                <div class="text-muted small mt-1 text-center" id="mophF5ProgressHint" style="font-size: 0.75rem;">
                                    กำลังส่งข้อมูลไปยังเซิร์ฟเวอร์...
                                </div>
                            </div>

                            <!-- Result Alert Box (Shows complete breakdown on modal without full page reload) -->
                            <div id="mophF5ResultBox" style="display: none;" class="mb-3"></div>

                            <!-- Tab 0 Bottom Action Bar (Relocated from global modal footer to Tab 0 as requested) -->
                            <div class="p-3 bg-white border rounded-3 mt-3 d-flex justify-content-between align-items-center shadow-2xs flex-wrap gap-2" id="tab0UploadActionBar">
                                <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 0.78rem;">
                                    <i class="fas fa-circle-info text-primary fs-6"></i>
                                    <span>ระบบจะตรวจสอบโครงสร้างทั้ง 5 แฟ้มและทำการ Update/Upsert ข้อมูลลงฐานข้อมูลโดยอัตโนมัติ</span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal" id="btnCancelMophF5">ยกเลิก</button>
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs" id="btnStartUploadMophF5" disabled>
                                        <i class="fas fa-cloud-arrow-up me-1.5"></i> เริ่มนำเข้าข้อมูล
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- TAB 1: DRUGLIST (ทะเบียนยา) -->
                    <div class="tab-pane fade h-100 overflow-hidden" id="tab-f5-druglist" role="tabpanel" aria-labelledby="tab-f5-druglist-tab">
                        <div class="card border rounded-3 bg-white shadow-2xs f5-data-card">
                            <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2.5 border-bottom flex-wrap gap-2 flex-shrink-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-primary px-2.5 py-1.5"><i class="fas fa-pills me-1"></i> แฟ้มที่ 1: ทะเบียนยา (druglist)</span>
                                    <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">
                                        <i class="far fa-clock text-primary me-1"></i>อัปเดตล่าสุด: <strong class="f5-last-update text-dark" id="last_update_druglist">{{ $f5LastUpdated['druglist'] ?? '-' }}</strong>
                                    </span>
                                    <small class="text-muted d-none d-xl-inline">ข้อมูลรายการยา รหัส GPUID หมวดยา และสถานะ</small>
                                </div>
                                <!-- Filter controls for druglist -->
                                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-calendar-alt text-primary me-1"></i>งวดรายงาน:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_druglist_period" data-file="druglist" style="font-size: 0.78rem; width: auto; min-width: 150px;">
                                            <option value="all">ทั้งหมด (ทุกงวด)</option>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-toggle-on text-success me-1"></i>สถานะ:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_druglist_status" data-file="druglist" style="font-size: 0.78rem; width: auto;">
                                            <option value="all">ทั้งหมด</option>
                                            <option value="1">ใช้งาน (Active)</option>
                                            <option value="0">ยกเลิก (Inactive)</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 btn-f5-reload" data-file="druglist" title="รีเฟรชข้อมูล">
                                        <i class="fas fa-rotate me-1"></i> โหลดใหม่
                                    </button>
                                </div>
                            </div>
                            <table id="table_f5_druglist" class="table table-sm table-striped table-hover align-middle w-100 mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th class="text-center" style="width: 45px;">#</th>
                                        <th>รหัสยา (Working Code)</th>
                                        <th>ชื่อสามัญทางยา (Generic Name)</th>
                                        <th>GPU ID</th>
                                        <th class="text-center">NLEM</th>
                                        <th class="text-center">หมวดยา</th>
                                        <th class="text-center">หน่วยนับ</th>
                                        <th class="text-center">สถานะ</th>
                                        <th class="text-center">วันที่มีผล</th>
                                        <th class="text-center">งวดรายงาน</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <th colspan="10" class="text-muted py-2" id="tfoot_f5_druglist">
                                            กำลังโหลดข้อมูล...
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: PURCHASEPLAN (แผนจัดซื้อ) -->
                    <div class="tab-pane fade h-100 overflow-hidden" id="tab-f5-purchaseplan" role="tabpanel" aria-labelledby="tab-f5-purchaseplan-tab">
                        <div class="card border rounded-3 bg-white shadow-2xs f5-data-card">
                            <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2.5 border-bottom flex-wrap gap-2 flex-shrink-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-info text-dark px-2.5 py-1.5"><i class="fas fa-file-invoice-dollar me-1"></i> แฟ้มที่ 2: แผนจัดซื้อยา (purchaseplan)</span>
                                    <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">
                                        <i class="far fa-clock text-info me-1"></i>อัปเดตล่าสุด: <strong class="f5-last-update text-dark" id="last_update_purchaseplan">{{ $f5LastUpdated['purchaseplan'] ?? '-' }}</strong>
                                    </span>
                                    <small class="text-muted d-none d-xl-inline">แผนจัดซื้อยาประจำปีงบประมาณและรายไตรมาส (Q1 - Q4)</small>
                                </div>
                                <!-- Filter controls for purchaseplan -->
                                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-calendar-alt text-info me-1"></i>งวดรายงาน:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_purchaseplan_period" data-file="purchaseplan" style="font-size: 0.78rem; width: auto; min-width: 145px;">
                                            <option value="all">ทั้งหมด (ทุกงวด)</option>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-calendar-check text-warning me-1"></i>ปีงบประมาณ:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_purchaseplan_year" data-file="purchaseplan" style="font-size: 0.78rem; width: auto;">
                                            <option value="all">ทั้งหมด (ทุกปีงบ)</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 btn-f5-reload" data-file="purchaseplan" title="รีเฟรชข้อมูล">
                                        <i class="fas fa-rotate me-1"></i> โหลดใหม่
                                    </button>
                                </div>
                            </div>
                            <table id="table_f5_purchaseplan" class="table table-sm table-striped table-hover align-middle w-100 mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th class="text-center" style="width: 45px;">#</th>
                                        <th class="text-center">ปีงบ</th>
                                        <th>รหัสยา</th>
                                        <th>ชื่อสามัญทางยา (Generic Name)</th>
                                        <th class="text-center">GPU ID</th>
                                        <th class="text-center">NLEM</th>
                                        <th class="text-center">ขนาดบรรจุ</th>
                                        <th class="text-center">หน่วย</th>
                                        <th class="text-end">ราคา/แพ็ค (บาท)</th>
                                        <th class="text-end text-primary">แผนปีนี้ (จน.)</th>
                                        <th class="text-end text-success">มูลค่าแผนปีนี้ (บาท)</th>
                                        <th class="text-end">ใช้ปีก่อน (N-1)</th>
                                        <th class="text-end">Q1</th>
                                        <th class="text-end">Q2</th>
                                        <th class="text-end">Q3</th>
                                        <th class="text-end">Q4</th>
                                        <th class="text-center">งวด</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <th colspan="9" class="text-end py-2">รวมแผนจัดซื้อทั้งสิ้น:</th>
                                        <th class="text-end text-primary py-2" id="tfoot_plan_qty">0</th>
                                        <th class="text-end text-success py-2" id="tfoot_plan_val">0.00 บาท</th>
                                        <th colspan="6"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: RECEIPT (รับเข้าคลัง) -->
                    <div class="tab-pane fade h-100 overflow-hidden" id="tab-f5-receipt" role="tabpanel" aria-labelledby="tab-f5-receipt-tab">
                        <div class="card border rounded-3 bg-white shadow-2xs f5-data-card">
                            <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2.5 border-bottom flex-wrap gap-2 flex-shrink-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-success px-2.5 py-1.5"><i class="fas fa-truck-ramp-box me-1"></i> แฟ้มที่ 3: รับเข้าคลัง (receipt)</span>
                                    <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">
                                        <i class="far fa-clock text-success me-1"></i>อัปเดตล่าสุด: <strong class="f5-last-update text-dark" id="last_update_receipt">{{ $f5LastUpdated['receipt'] ?? '-' }}</strong>
                                    </span>
                                    <small class="text-muted d-none d-xl-inline">ประวัติการตรวจรับยาเข้าคลัง เลขที่รับ ใบสั่งซื้อ และ Lot/วันหมดอายุ</small>
                                </div>
                                <!-- Filter controls for receipt (Period only as requested) -->
                                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-calendar-alt text-success me-1"></i>งวดรายงาน:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_receipt_period" data-file="receipt" style="font-size: 0.78rem; width: auto; min-width: 150px;">
                                            <option value="all">ทั้งหมด (ทุกงวด)</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 btn-f5-reload" data-file="receipt" title="รีเฟรชข้อมูล">
                                        <i class="fas fa-rotate me-1"></i> โหลดใหม่
                                    </button>
                                </div>
                            </div>
                            <table id="table_f5_receipt" class="table table-sm table-striped table-hover align-middle w-100 mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th class="text-center" style="width: 45px;">#</th>
                                        <th class="text-center">วันที่รับ</th>
                                        <th>เลขที่รับ (RCV NO)</th>
                                        <th>เลขที่ PO</th>
                                        <th>รหัสยา</th>
                                        <th>ชื่อการค้า / รายการยา</th>
                                        <th>บริษัท / ผู้จัดจำหน่าย</th>
                                        <th class="text-end text-primary">จำนวนรับ</th>
                                        <th class="text-center">ขนาด</th>
                                        <th class="text-center">หน่วย</th>
                                        <th class="text-end">ราคา/หน่วย (บาท)</th>
                                        <th class="text-end text-success">มูลค่ารวม (บาท)</th>
                                        <th>Lot No.</th>
                                        <th class="text-center">วันหมดอายุ</th>
                                        <th class="text-center">งวด</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <th colspan="7" class="text-end py-2">รวมตรวจรับเข้าคลัง:</th>
                                        <th class="text-end text-primary py-2" id="tfoot_rcv_qty">0</th>
                                        <th colspan="3" class="text-end">รวมมูลค่าตรวจรับ:</th>
                                        <th class="text-end text-success py-2" id="tfoot_rcv_val">0.00 บาท</th>
                                        <th colspan="3"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 4: DISTRIBUTION (เบิกจ่ายยา) -->
                    <div class="tab-pane fade h-100 overflow-hidden" id="tab-f5-distribution" role="tabpanel" aria-labelledby="tab-f5-distribution-tab">
                        <div class="card border rounded-3 bg-white shadow-2xs f5-data-card">
                            <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2.5 border-bottom flex-wrap gap-2 flex-shrink-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-warning text-dark px-2.5 py-1.5"><i class="fas fa-boxes-packing me-1"></i> แฟ้มที่ 4: เบิกจ่ายยา (distribution)</span>
                                    <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">
                                        <i class="far fa-clock text-warning me-1"></i>อัปเดตล่าสุด: <strong class="f5-last-update text-dark" id="last_update_distribution">{{ $f5LastUpdated['distribution'] ?? '-' }}</strong>
                                    </span>
                                    <small class="text-muted d-none d-xl-inline">รายการเบิกจ่ายยาตามกลุ่มหน่วยงาน (OPD, IPD, คลังย่อย, รพ.สต.)</small>
                                </div>
                                <!-- Filter controls for distribution -->
                                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-calendar-alt text-warning me-1"></i>งวดรายงาน:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_distribution_period" data-file="distribution" style="font-size: 0.78rem; width: auto; min-width: 145px;">
                                            <option value="all">ทั้งหมด (ทุกงวด)</option>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-hospital-user text-primary me-1"></i>กลุ่มแผนก:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_distribution_dept" data-file="distribution" style="font-size: 0.78rem; width: auto; max-width: 220px;">
                                            <option value="all">ทั้งหมด (ทุกกลุ่ม)</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 btn-f5-reload" data-file="distribution" title="รีเฟรชข้อมูล">
                                        <i class="fas fa-rotate me-1"></i> โหลดใหม่
                                    </button>
                                </div>
                            </div>
                            <table id="table_f5_distribution" class="table table-sm table-striped table-hover align-middle w-100 mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th class="text-center" style="width: 45px;">#</th>
                                        <th class="text-center">งวดรายงาน</th>
                                        <th>กลุ่มแผนก/หน่วยงานที่เบิก</th>
                                        <th>รหัสยา</th>
                                        <th>ชื่อยา / เวชภัณฑ์</th>
                                        <th class="text-end text-primary">จำนวนที่จ่าย</th>
                                        <th class="text-center">ขนาด</th>
                                        <th class="text-center">หน่วย</th>
                                        <th class="text-end text-success">มูลค่าเบิกจ่าย (บาท)</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <th colspan="5" class="text-end py-2">รวมเบิกจ่ายทั้งสิ้น:</th>
                                        <th class="text-end text-primary py-2" id="tfoot_dis_qty">0</th>
                                        <th colspan="2" class="text-end">รวมมูลค่าเบิกจ่าย:</th>
                                        <th class="text-end text-success py-2" id="tfoot_dis_val">0.00 บาท</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 5: INVENTORY (ยาคงคลัง) -->
                    <div class="tab-pane fade h-100 overflow-hidden" id="tab-f5-inventory" role="tabpanel" aria-labelledby="tab-f5-inventory-tab">
                        <div class="card border rounded-3 bg-white shadow-2xs f5-data-card">
                            <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2.5 border-bottom flex-wrap gap-2 flex-shrink-0">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-danger px-2.5 py-1.5"><i class="fas fa-warehouse me-1"></i> แฟ้มที่ 5: ยาคงคลัง (inventory)</span>
                                    <span class="badge bg-light text-secondary border px-2.5 py-1 small fw-normal">
                                        <i class="far fa-clock text-danger me-1"></i>อัปเดตล่าสุด: <strong class="f5-last-update text-dark" id="last_update_inventory">{{ $f5LastUpdated['inventory'] ?? '-' }}</strong>
                                    </span>
                                    <small class="text-muted d-none d-xl-inline">ยอดคงเหลือในคลังยา Lot Number และวันหมดอายุ</small>
                                </div>
                                <!-- Filter controls for inventory -->
                                <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-calendar-day text-danger me-1"></i>ณ วันที่สำรวจ:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_inventory_date" data-file="inventory" style="font-size: 0.78rem; width: auto; min-width: 170px;">
                                            <option value="latest">ล่าสุด (ณ วันที่ตรวจนับล่าสุด)</option>
                                        </select>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <label class="small text-muted fw-semibold text-nowrap"><i class="fas fa-clock text-warning me-1"></i>วันหมดอายุ:</label>
                                        <select class="form-select form-select-sm rounded-pill f5-filter-select" id="filter_f5_inventory_expire" data-file="inventory" style="font-size: 0.78rem; width: auto;">
                                            <option value="all">ทั้งหมด</option>
                                            <option value="near_exp">ใกล้หมดอายุ (<= 6 เดือน)</option>
                                            <option value="expired">หมดอายุแล้ว</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 btn-f5-reload" data-file="inventory" title="รีเฟรชข้อมูล">
                                        <i class="fas fa-rotate me-1"></i> โหลดใหม่
                                    </button>
                                </div>
                            </div>
                            <table id="table_f5_inventory" class="table table-sm table-striped table-hover align-middle w-100 mb-0" style="font-size: 0.8rem;">
                                <thead class="table-light">
                                    <tr class="text-nowrap">
                                        <th class="text-center" style="width: 45px;">#</th>
                                        <th class="text-center">ณ วันที่</th>
                                        <th>รหัสยา</th>
                                        <th>ชื่อการค้า / รายการยา</th>
                                        <th>ผู้จัดจำหน่าย</th>
                                        <th class="text-end text-primary">ยอดคงคลัง</th>
                                        <th class="text-center">ขนาด</th>
                                        <th class="text-center">หน่วย</th>
                                        <th class="text-end">ราคา/หน่วย (บาท)</th>
                                        <th class="text-end text-success">มูลค่าคงคลัง (บาท)</th>
                                        <th>Lot No.</th>
                                        <th class="text-center">วันหมดอายุ</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <th colspan="5" class="text-end py-2">รวมคงคลังทั้งสิ้น:</th>
                                        <th class="text-end text-primary py-2" id="tfoot_inv_qty">0</th>
                                        <th colspan="3" class="text-end">รวมมูลค่าคงคลัง:</th>
                                        <th class="text-end text-success py-2" id="tfoot_inv_val">0.00 บาท</th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Footer (Only shown on the 5 data viewer tabs, hidden on Tab 0 Upload) -->
            <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center border-top flex-shrink-0" id="f5ModalMainFooter" style="display: none;">
                <div class="d-flex align-items-center gap-2 flex-wrap" id="f5FooterSummaryBadges">
                    <span class="badge bg-white text-dark border px-2.5 py-1.5 shadow-2xs fw-semibold" id="f5FootSummaryCount">
                        <i class="fas fa-database text-primary me-1"></i> 0 รายการ
                    </span>
                    <span class="badge bg-white text-primary border px-2.5 py-1.5 shadow-2xs fw-semibold" id="f5FootSummaryQty">
                        <i class="fas fa-boxes me-1"></i> จำนวน: 0
                    </span>
                    <span class="badge bg-white text-success border px-2.5 py-1.5 shadow-2xs fw-bold" id="f5FootSummaryVal">
                        <i class="fas fa-baht-sign me-1"></i> มูลค่ารวม: 0.00 บาท
                    </span>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" id="btnSwitchToUpload">
                        <i class="fas fa-cloud-arrow-up me-1"></i> นำเข้าไฟล์เพิ่ม
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">
                        <i class="fas fa-xmark me-1"></i> ปิดหน้าต่าง
                    </button>
                </div>
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
                    // Monthly View: Daily Area Chart with Gradient across 6 Warehouses (ว.1 - ว.31)
                    var dailyTrendData = @json($dailyTrend ?? []);
                    var chartLabels = dailyTrendData.map(function(d) { return 'ว.' + d.day; });
                    var drugSeries = dailyTrendData.map(function(d) { return d.drug || 0; });
                    var matSeries = dailyTrendData.map(function(d) { return d.material || 0; });
                    var denSeries = dailyTrendData.map(function(d) { return d.dental || 0; });
                    var medtechSeries = dailyTrendData.map(function(d) { return d.medtech || 0; });
                    var phySeries = dailyTrendData.map(function(d) { return d.physical || 0; });
                    var medSeries = dailyTrendData.map(function(d) { return d.medical || 0; });

                    var overviewOptions = {
                        series: [
                            { name: 'คลังยา (INVS)', data: drugSeries },
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
                        colors: ['#059669', '#ec4899', '#8b5cf6', '#f59e0b', '#06b6d4', '#10b981'],
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
                    // Full Year View: 12-Month Area Chart with Gradient across 6 Warehouses (ต.ค. - ก.ย.)
                    var monthlyTrendData = @json($monthlyTrend ?? []);
                    var chartLabels = ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'];
                    var drugSeries = monthlyTrendData.map(function(d) { return d.drug || 0; });
                    var matSeries = monthlyTrendData.map(function(d) { return d.material || 0; });
                    var denSeries = monthlyTrendData.map(function(d) { return d.dental || 0; });
                    var medtechSeries = monthlyTrendData.map(function(d) { return d.medtech || 0; });
                    var phySeries = monthlyTrendData.map(function(d) { return d.physical || 0; });
                    var medSeries = monthlyTrendData.map(function(d) { return d.medical || 0; });

                    var overviewOptions = {
                        series: [
                            { name: 'คลังยา (INVS)', data: drugSeries },
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
                        colors: ['#059669', '#ec4899', '#8b5cf6', '#f59e0b', '#06b6d4', '#10b981'],
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

        // =========================================================================
        // MOPH 5 Files (INVS Drug Warehouse) Multi-file Upload, Tabs & DataTables
        // =========================================================================
        var selectedF5Files = [];
        var f5DataTables = {};
        var f5Loaded = {};
        var f5Summaries = {};

        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024, dm = 1;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function updateF5Checklist() {
            var recognized = {
                druglist: null,
                purchaseplan: null,
                receipt: null,
                distribution: null,
                inventory: null
            };

            for (var i = 0; i < selectedF5Files.length; i++) {
                var f = selectedF5Files[i];
                var name = f.name.toLowerCase();
                if (name.indexOf('druglist') !== -1 || name.indexOf('1duglist') !== -1) {
                    recognized.druglist = f;
                } else if (name.indexOf('purchaseplan') !== -1 || name.indexOf('1purchaseplan') !== -1) {
                    recognized.purchaseplan = f;
                } else if (name.indexOf('receipt') !== -1 || name.indexOf('1receipt') !== -1) {
                    recognized.receipt = f;
                } else if (name.indexOf('distribution') !== -1 || name.indexOf('1distribution') !== -1) {
                    recognized.distribution = f;
                } else if (name.indexOf('inventory') !== -1 || name.indexOf('1inventory') !== -1) {
                    recognized.inventory = f;
                }
            }

            var recognizedCount = 0;
            var keys = ['druglist', 'purchaseplan', 'receipt', 'distribution', 'inventory'];
            for (var k = 0; k < keys.length; k++) {
                var key = keys[k];
                var badge = $('#badge_' + key);
                var f = recognized[key];
                if (f) {
                    recognizedCount++;
                    badge.removeClass('bg-light border-light').addClass('bg-success bg-opacity-10 border-success');
                    badge.find('.f5-icon').removeClass('fa-circle-question text-muted').addClass('fa-circle-check text-success');
                    badge.find('.f5-filename').text(f.name + ' (' + formatBytes(f.size) + ')').removeClass('text-muted').addClass('text-success fw-bold');
                } else {
                    badge.removeClass('bg-success bg-opacity-10 border-success').addClass('bg-light border-light');
                    badge.find('.f5-icon').removeClass('fa-circle-check text-success').addClass('fa-circle-question text-muted');
                    badge.find('.f5-filename').text('ยังไม่ได้เลือกไฟล์').removeClass('text-success fw-bold').addClass('text-muted');
                }
            }

            if (selectedF5Files.length > 0) {
                $('#mophF5CountBadge').text('เลือก ' + recognizedCount + ' / 5 แฟ้ม (' + selectedF5Files.length + ' ไฟล์)').removeClass('bg-secondary').addClass(recognizedCount === 5 ? 'bg-success' : 'bg-primary');
                $('#btnStartUploadMophF5').prop('disabled', false);
            } else {
                $('#mophF5CountBadge').text('เลือก 0 / 5 แฟ้ม').removeClass('bg-success bg-primary').addClass('bg-secondary');
                $('#btnStartUploadMophF5').prop('disabled', true);
            }
        }

        $('#mophF5FileInput').on('change', function(e) {
            if (this.files && this.files.length > 0) {
                selectedF5Files = Array.from(this.files);
                updateF5Checklist();
            }
        });

        // Drag and drop events
        var dropzone = document.getElementById('mophF5Dropzone');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(function(eventName) {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#059669';
                    dropzone.style.backgroundColor = '#ecfdf5';
                }, false);
            });

            ['dragleave', 'drop'].forEach(function(eventName) {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.backgroundColor = '#ffffff';
                }, false);
            });

            dropzone.addEventListener('drop', function(e) {
                var dt = e.dataTransfer;
                if (dt && dt.files && dt.files.length > 0) {
                    selectedF5Files = Array.from(dt.files);
                    updateF5Checklist();
                }
            }, false);
        }

        // Switch to upload tab from data viewer footer
        $('#btnSwitchToUpload').on('click', function() {
            var uploadTabTrigger = document.getElementById('tab-f5-upload-tab');
            if (uploadTabTrigger) {
                var tabInstance = bootstrap.Tab.getOrCreateInstance(uploadTabTrigger);
                tabInstance.show();
            }
        });

        // Manual reload button for each table
        $(document).on('click', '.btn-f5-reload', function() {
            var file = $(this).data('file');
            if (file) {
                loadF5TableData(file, true);
            }
        });

        // Handle Tab switching & Divided Footer
        $('#mophF5ModalNavTabs button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
            var targetFile = $(e.target).data('f5-file');
            if (targetFile === 'upload') {
                // Tab 0: Upload -> Hide modal footer (Tab 0 has its own action bar at bottom)
                $('#f5ModalMainFooter').hide();
            } else {
                // Tabs 1-5: Data tables -> Show modal footer with summaries and buttons
                $('#f5ModalMainFooter').show();

                // Load Data for this file if not already loaded
                loadF5TableData(targetFile, false);

                // Auto-adjust column widths for active DataTable
                setTimeout(function() {
                    if (f5DataTables[targetFile]) {
                        f5DataTables[targetFile].columns.adjust().draw(false);
                    }
                }, 120);
            }
        });

        // Ensure footer visibility when modal opens
        $('#uploadMophF5Modal').on('show.bs.modal', function() {
            var activeFile = $('#mophF5ModalNavTabs .nav-link.active').data('f5-file') || 'upload';
            if (activeFile === 'upload') {
                $('#f5ModalMainFooter').hide();
            } else {
                $('#f5ModalMainFooter').show();
            }
        });

        // Helper to update the divided modal footer summary badges
        function updateModalFooterSummary(file, summary) {
            if (!summary) return;
            if (file === 'druglist') {
                $('#f5FootSummaryCount').html('<i class="fas fa-pills text-primary me-1"></i> รวม ' + (summary.total_items || 0).toLocaleString() + ' รายการ');
                $('#f5FootSummaryQty').html('<i class="fas fa-circle-check text-success me-1"></i> ใช้งานได้ ' + (summary.total_active || 0).toLocaleString() + ' รายการ');
                $('#f5FootSummaryVal').html('<i class="fas fa-book-medical text-info me-1"></i> ในบัญชียาหลัก ' + (summary.total_nlem || 0).toLocaleString() + ' รายการ');
            } else if (file === 'purchaseplan') {
                $('#f5FootSummaryCount').html('<i class="fas fa-file-invoice-dollar text-info me-1"></i> แผน ' + (summary.total_items || 0).toLocaleString() + ' รายการ');
                $('#f5FootSummaryQty').html('<i class="fas fa-boxes text-primary me-1"></i> รวม ' + Number(summary.total_qty || 0).toLocaleString() + ' หน่วย');
                $('#f5FootSummaryVal').html('<i class="fas fa-baht-sign text-success me-1"></i> มูลค่าแผน: ' + Number(summary.total_value || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
            } else if (file === 'receipt') {
                $('#f5FootSummaryCount').html('<i class="fas fa-truck-ramp-box text-success me-1"></i> ตรวจรับ ' + (summary.total_items || 0).toLocaleString() + ' ใบ');
                $('#f5FootSummaryQty').html('<i class="fas fa-boxes text-primary me-1"></i> รวม ' + Number(summary.total_qty || 0).toLocaleString() + ' หน่วย');
                $('#f5FootSummaryVal').html('<i class="fas fa-baht-sign text-success me-1"></i> มูลค่ารับเข้า: ' + Number(summary.total_value || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
            } else if (file === 'distribution') {
                $('#f5FootSummaryCount').html('<i class="fas fa-boxes-packing text-warning me-1"></i> รายการเบิก ' + (summary.total_items || 0).toLocaleString() + ' รายการ');
                $('#f5FootSummaryQty').html('<i class="fas fa-boxes text-primary me-1"></i> รวมจ่าย ' + Number(summary.total_qty || 0).toLocaleString() + ' หน่วย');
                $('#f5FootSummaryVal').html('<i class="fas fa-baht-sign text-success me-1"></i> มูลค่าเบิกจ่าย: ' + Number(summary.total_value || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
            } else if (file === 'inventory') {
                $('#f5FootSummaryCount').html('<i class="fas fa-warehouse text-danger me-1"></i> รายการยา ' + (summary.total_items || 0).toLocaleString() + ' รายการ');
                $('#f5FootSummaryQty').html('<i class="fas fa-boxes text-primary me-1"></i> คงคลัง ' + Number(summary.total_qty || 0).toLocaleString() + ' หน่วย');
                $('#f5FootSummaryVal').html('<i class="fas fa-baht-sign text-success me-1"></i> มูลค่าคงคลัง: ' + Number(summary.total_value || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
            }
        }

        // Change events for filter selects
        $(document).on('change', '.f5-filter-select', function() {
            var file = $(this).data('file');
            if (file) {
                loadF5TableData(file, true);
            }
        });

        // Load & Initialize DataTables for MOPH 5 files with query filters
        function loadF5TableData(file, forceReload) {
            if (!forceReload && f5Loaded[file]) {
                updateModalFooterSummary(file, f5Summaries[file]);
                if (f5DataTables[file]) {
                    f5DataTables[file].columns.adjust().draw(false);
                }
                return;
            }

            var reloadBtn = $('.btn-f5-reload[data-file="' + file + '"]');
            reloadBtn.find('i').addClass('fa-spin');

            var url = '{{ route("backoffice.warehouse.moph_f5_table_data", ["file" => ":file"]) }}'.replace(':file', file);

            // Read filter parameters
            var queryParams = {};
            if (file === 'druglist') {
                queryParams.period_rpt = $('#filter_f5_druglist_period').val() || 'all';
                queryParams.status = $('#filter_f5_druglist_status').val() || 'all';
            } else if (file === 'purchaseplan') {
                queryParams.period_rpt = $('#filter_f5_purchaseplan_period').val() || 'all';
                queryParams.yearbudget = $('#filter_f5_purchaseplan_year').val() || 'all';
            } else if (file === 'receipt') {
                queryParams.period_rpt = $('#filter_f5_receipt_period').val() || 'all';
            } else if (file === 'distribution') {
                queryParams.period_rpt = $('#filter_f5_distribution_period').val() || 'all';
                queryParams.dis_dept_group = $('#filter_f5_distribution_dept').val() || 'all';
            } else if (file === 'inventory') {
                queryParams.date_onhand = $('#filter_f5_inventory_date').val() || 'latest';
                queryParams.expire_filter = $('#filter_f5_inventory_expire').val() || 'all';
            }

            $.ajax({
                url: url,
                type: 'GET',
                data: queryParams,
                dataType: 'json',
                success: function(resp) {
                    reloadBtn.find('i').removeClass('fa-spin');
                    if (!resp.success) return;

                    f5Loaded[file] = true;
                    f5Summaries[file] = resp.summary;

                    // Update latest update timestamp in card header
                    if (resp.last_updated) {
                        $('#last_update_' + file).text(resp.last_updated);
                    }

                    // Dynamically populate filter options if returned and select has only default option
                    if (resp.filters) {
                        // Periods dropdown
                        if (resp.filters.available_periods && resp.filters.available_periods.length > 0) {
                            var pSelect = $('#filter_f5_' + file + '_period');
                            if (pSelect.length && pSelect.children('option').length <= 1) {
                                var curPVal = pSelect.val();
                                resp.filters.available_periods.forEach(function(item) {
                                    pSelect.append($('<option>', { value: item.value, text: item.label }));
                                });
                                if (curPVal) pSelect.val(curPVal);
                            }
                        }
                        // Years dropdown (purchaseplan)
                        if (resp.filters.available_years && resp.filters.available_years.length > 0) {
                            var ySelect = $('#filter_f5_purchaseplan_year');
                            if (ySelect.length && ySelect.children('option').length <= 1) {
                                var curYVal = ySelect.val();
                                resp.filters.available_years.forEach(function(item) {
                                    ySelect.append($('<option>', { value: item.value, text: item.label }));
                                });
                                if (curYVal) ySelect.val(curYVal);
                            }
                        }
                        // Departments dropdown (distribution)
                        if (resp.filters.available_depts && resp.filters.available_depts.length > 0) {
                            var dSelect = $('#filter_f5_distribution_dept');
                            if (dSelect.length && dSelect.children('option').length <= 1) {
                                var curDVal = dSelect.val();
                                resp.filters.available_depts.forEach(function(item) {
                                    dSelect.append($('<option>', { value: item.value, text: item.label }));
                                });
                                if (curDVal) dSelect.val(curDVal);
                            }
                        }
                        // Survey Dates dropdown (inventory)
                        if (resp.filters.available_dates && resp.filters.available_dates.length > 0) {
                            var dateSelect = $('#filter_f5_inventory_date');
                            if (dateSelect.length && (dateSelect.children('option').length <= 1 || dateSelect.children('option').first().val() === 'latest')) {
                                dateSelect.empty();
                                resp.filters.available_dates.forEach(function(item) {
                                    dateSelect.append($('<option>', { value: item.value, text: item.label }));
                                });
                                if (resp.selected_date) {
                                    dateSelect.val(resp.selected_date);
                                }
                            }
                        }
                    }

                    // Update Tab badge
                    $('#f5TabBadge_' + file).text(Number(resp.count).toLocaleString());

                    // Update Divided Modal Footer
                    updateModalFooterSummary(file, resp.summary);

                    // Update Table Footer
                    if (file === 'druglist') {
                        $('#tfoot_f5_druglist').html('รวมทั้งหมด: ' + (resp.summary.total_items || 0).toLocaleString() + ' รายการ | ใช้งานได้: ' + (resp.summary.total_active || 0).toLocaleString() + ' รายการ | ในบัญชียาหลักแห่งชาติ: ' + (resp.summary.total_nlem || 0).toLocaleString() + ' รายการ');
                    } else if (file === 'purchaseplan') {
                        $('#tfoot_plan_qty').text(Number(resp.summary.total_qty || 0).toLocaleString());
                        $('#tfoot_plan_val').text(Number(resp.summary.total_value || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                    } else if (file === 'receipt') {
                        $('#tfoot_rcv_qty').text(Number(resp.summary.total_qty || 0).toLocaleString());
                        $('#tfoot_rcv_val').text(Number(resp.summary.total_value || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                    } else if (file === 'distribution') {
                        $('#tfoot_dis_qty').text(Number(resp.summary.total_qty || 0).toLocaleString());
                        $('#tfoot_dis_val').text(Number(resp.summary.total_value || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                    } else if (file === 'inventory') {
                        $('#tfoot_inv_qty').text(Number(resp.summary.total_qty || 0).toLocaleString());
                        $('#tfoot_inv_val').text(Number(resp.summary.total_value || 0).toLocaleString('th-TH', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' บาท');
                    }

                    // Populate or re-initialize DataTable
                    if (f5DataTables[file]) {
                        f5DataTables[file].clear().rows.add(resp.data).draw();
                        f5DataTables[file].columns.adjust().draw(false);
                    } else {
                        initF5DataTable(file, resp.data);
                    }
                },
                error: function(err) {
                    reloadBtn.find('i').removeClass('fa-spin');
                    console.error('Error loading MOPH F5 table data for ' + file, err);
                }
            });
        }

        // Initialize DataTable per file type (Excel button on the far right after search filter)
        function initF5DataTable(file, tableData) {
            var dtConfig = {
                data: tableData,
                dom: "<'row mb-2 align-items-center flex-shrink-0'<'col-sm-4 d-flex align-items-center'l><'col-sm-8 d-flex justify-content-end align-items-center gap-2'fB>>" +
                     "<'f5-table-scroll-container flex-grow-1 overflow-auto position-relative'tr>" +
                     "<'row f5-dt-bottom-row align-items-center flex-shrink-0'<'col-sm-5'i><'col-sm-7 d-flex justify-content-end'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel text-white me-1.5 fs-6"></i> Excel',
                        className: 'btn btn-sm btn-f5-excel shadow-sm',
                        title: 'MOPH_F5_' + file.toUpperCase() + '_' + new Date().toISOString().slice(0, 10)
                    }
                ],
                pageLength: 20,
                lengthMenu: [[20, 50, 100, 250, -1], [20, 50, 100, 250, "ทั้งหมด"]],
                order: [[0, 'asc']],
                autoWidth: false,
                scrollX: false,
                language: {
                    processing: "กำลังประมวลผล...",
                    search: "ค้นหาในแฟ้ม:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    infoEmpty: "แสดง 0 ถึง 0 จาก 0 รายการ",
                    infoFiltered: "(ค้นหาจากทั้งหมด _MAX_ รายการ)",
                    loadingRecords: "กำลังโหลดข้อมูล...",
                    zeroRecords: "ไม่พบข้อมูลที่ตรงกับคำค้น",
                    emptyTable: "ไม่มีข้อมูลในตาราง",
                    paginate: {
                        first: "แรก",
                        previous: "ก่อนหน้า",
                        next: "ถัดไป",
                        last: "ท้าย"
                    }
                }
            };

            if (file === 'druglist') {
                dtConfig.columns = [
                    { data: 'index', className: 'text-center' },
                    { data: 'working_code', className: 'font-monospace fw-bold' },
                    { data: 'generic_name' },
                    { data: 'gpuid', className: 'text-center font-monospace' },
                    { data: 'nlem', className: 'text-center' },
                    { data: 'product_cat', className: 'text-center' },
                    { data: 'base_unit', className: 'text-center' },
                    { data: 'status_badge', className: 'text-center' },
                    { data: 'date_status', className: 'text-center' },
                    { data: 'period_rpt', className: 'text-center' }
                ];
            } else if (file === 'purchaseplan') {
                dtConfig.columns = [
                    { data: 'index', className: 'text-center' },
                    { data: 'yearbudget', className: 'text-center' },
                    { data: 'working_code', className: 'font-monospace' },
                    { data: 'generic_name' },
                    { data: 'gpuid', className: 'text-center font-monospace' },
                    { data: 'nlem', className: 'text-center' },
                    { data: 'pack_size', className: 'text-center' },
                    { data: 'base_unit', className: 'text-center' },
                    { data: 'pack_cost', className: 'text-end', render: function(v) { return Number(v).toLocaleString('th-TH', {minimumFractionDigits: 2}); } },
                    { data: 'qty_this_year', className: 'text-end fw-semibold text-primary', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'value_this_year', className: 'text-end fw-bold text-success', render: function(v) { return Number(v).toLocaleString('th-TH', {minimumFractionDigits: 2}); } },
                    { data: 'qty_use_year1', className: 'text-end', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'qty_plan_trimes1', className: 'text-end', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'qty_plan_trimes2', className: 'text-end', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'qty_plan_trimes3', className: 'text-end', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'qty_plan_trimes4', className: 'text-end', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'period_rpt', className: 'text-center' }
                ];
            } else if (file === 'receipt') {
                dtConfig.columns = [
                    { data: 'index', className: 'text-center' },
                    { data: 'date_rcv', className: 'text-center text-nowrap' },
                    { data: 'rcv_no', className: 'font-monospace' },
                    { data: 'po_no', className: 'font-monospace' },
                    { data: 'working_code', className: 'font-monospace' },
                    { data: 'trade_name' },
                    { data: 'vendor_name' },
                    { data: 'qty_rcv', className: 'text-end fw-semibold text-primary', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'pack_size', className: 'text-center' },
                    { data: 'base_unit', className: 'text-center' },
                    { data: 'pack_cost', className: 'text-end', render: function(v) { return Number(v).toLocaleString('th-TH', {minimumFractionDigits: 2}); } },
                    { data: 'total_value', className: 'text-end fw-bold text-success', render: function(v) { return Number(v).toLocaleString('th-TH', {minimumFractionDigits: 2}); } },
                    { data: 'lot_no', className: 'font-monospace' },
                    { data: 'expire_date', className: 'text-center text-nowrap' },
                    { data: 'period_rpt', className: 'text-center' }
                ];
            } else if (file === 'distribution') {
                dtConfig.columns = [
                    { data: 'index', className: 'text-center' },
                    { data: 'period_rpt', className: 'text-center' },
                    { data: 'dept_name', className: 'fw-semibold text-dark' },
                    { data: 'working_code', className: 'font-monospace' },
                    { data: 'trade_name' },
                    { data: 'qty_dis', className: 'text-end fw-semibold text-primary', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'pack_size', className: 'text-center' },
                    { data: 'base_unit', className: 'text-center' },
                    { data: 'value', className: 'text-end fw-bold text-success', render: function(v) { return Number(v).toLocaleString('th-TH', {minimumFractionDigits: 2}); } }
                ];
            } else if (file === 'inventory') {
                dtConfig.columns = [
                    { data: 'index', className: 'text-center' },
                    { data: 'date_onhand', className: 'text-center text-nowrap' },
                    { data: 'working_code', className: 'font-monospace' },
                    { data: 'trade_name' },
                    { data: 'vendor_name' },
                    { data: 'qty_onhand', className: 'text-end fw-semibold text-primary', render: function(v) { return Number(v).toLocaleString(); } },
                    { data: 'pack_size', className: 'text-center' },
                    { data: 'base_unit', className: 'text-center' },
                    { data: 'pack_cost', className: 'text-end', render: function(v) { return Number(v).toLocaleString('th-TH', {minimumFractionDigits: 2}); } },
                    { data: 'value_onhand', className: 'text-end fw-bold text-success', render: function(v) { return Number(v).toLocaleString('th-TH', {minimumFractionDigits: 2}); } },
                    { data: 'lot_no', className: 'font-monospace' },
                    { data: 'expire_date', className: 'text-center text-nowrap' }
                ];
            }

            f5DataTables[file] = $('#table_f5_' + file).DataTable(dtConfig);
            setTimeout(function() {
                f5DataTables[file].columns.adjust().draw(false);
            }, 100);
        }

        // Quick switch tab button from Result Box
        $(document).on('click', '.btn-quick-tab', function() {
            var targetTab = $(this).data('tab-target');
            if (targetTab) {
                var tabTrigger = document.querySelector(targetTab);
                if (tabTrigger) {
                    var tabInstance = bootstrap.Tab.getOrCreateInstance(tabTrigger);
                    tabInstance.show();
                }
            }
        });

        // Reset upload form to select new files
        $(document).on('click', '#btnResetUploadForm', function() {
            selectedF5Files = [];
            $('#mophF5FileInput').val('');
            updateF5Checklist();
            $('#mophF5ProgressContainer').hide();
            $('#mophF5ResultBox').hide().empty();
            $('#btnStartUploadMophF5').prop('disabled', true).html('<i class="fas fa-cloud-arrow-up me-1.5"></i> เริ่มนำเข้าข้อมูล');
        });

        // Upload Button handler - Stays on modal on success and presents detailed result
        $('#btnStartUploadMophF5').on('click', function() {
            if (selectedF5Files.length === 0) return;

            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            for (var i = 0; i < selectedF5Files.length; i++) {
                formData.append('files[]', selectedF5Files[i]);
            }

            // Lock UI
            $('#btnStartUploadMophF5').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1.5"></i> กำลังนำเข้า...');
            $('#btnCancelMophF5, #btnCloseMophF5Modal').prop('disabled', true);
            $('#mophF5FileInput').prop('disabled', true);
            $('#mophF5ResultBox').hide().empty();
            $('#mophF5ProgressContainer').show();
            $('#mophF5ProgressBar').css('width', '0%').text('0%').attr('aria-valuenow', 0).removeClass('bg-danger').addClass('bg-success progress-bar-animated');
            $('#mophF5ProgressPct').text('0%');
            $('#mophF5ProgressStage').html('<i class="fas fa-spinner fa-spin text-success me-1"></i> กำลังอัปโหลดไฟล์...');
            $('#mophF5ProgressHint').text('กำลังส่งข้อมูลไปยังเซิร์ฟเวอร์...');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("backoffice.warehouse.upload_moph_f5") }}', true);

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    var percent = Math.min(92, Math.round((e.loaded / e.total) * 100));
                    $('#mophF5ProgressBar').css('width', percent + '%').text(percent + '%').attr('aria-valuenow', percent);
                    $('#mophF5ProgressPct').text(percent + '%');
                }
            });

            xhr.upload.addEventListener('load', function() {
                $('#mophF5ProgressBar').css('width', '95%').text('95%').attr('aria-valuenow', 95);
                $('#mophF5ProgressPct').text('95%');
                $('#mophF5ProgressStage').html('<i class="fas fa-database text-success me-1 fa-spin"></i> กำลังประมวลผลข้อมูลลงฐานข้อมูล (Upserting database rows...)');
                $('#mophF5ProgressHint').text('กรุณารอสักครู่ ระบบกำลังอ่านข้อมูล XML และบันทึกตาราง moph_f5_* ...');
            });

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    $('#btnCancelMophF5, #btnCloseMophF5Modal').prop('disabled', false);
                    $('#mophF5FileInput').prop('disabled', false);

                    if (xhr.status === 200) {
                        try {
                            var resp = JSON.parse(xhr.responseText);
                            if (resp.success) {
                                $('#mophF5ProgressBar').css('width', '100%').text('100%').attr('aria-valuenow', 100).removeClass('progress-bar-animated');
                                $('#mophF5ProgressPct').text('100%');
                                $('#mophF5ProgressStage').html('<i class="fas fa-circle-check text-success me-1"></i> นำเข้าข้อมูลสำเร็จเรียบร้อย!');
                                $('#mophF5ProgressHint').text('บันทึกและตรวจสอบข้อมูลทั้ง 5 แฟ้มลงฐานข้อมูลเรียบร้อยแล้ว');

                                // Invalidate loaded cache for DataTables so clicking any tab re-fetches
                                f5Loaded = {};

                                // Update Tab Badges immediately from server counts
                                if (resp.counts) {
                                    $('#f5TabBadge_druglist').text(Number(resp.counts.druglist || 0).toLocaleString());
                                    $('#f5TabBadge_purchaseplan').text(Number(resp.counts.purchaseplan || 0).toLocaleString());
                                    $('#f5TabBadge_receipt').text(Number(resp.counts.receipt || 0).toLocaleString());
                                    $('#f5TabBadge_distribution').text(Number(resp.counts.distribution || 0).toLocaleString());
                                    $('#f5TabBadge_inventory').text(Number(resp.counts.inventory || 0).toLocaleString());
                                }

                                var r = resp.results;
                                var fileTypeTitles = {
                                    'druglist': '1. ทะเบียนยา (druglist)',
                                    'purchaseplan': '2. แผนจัดซื้อ (purchaseplan)',
                                    'receipt': '3. รับเข้าคลัง (receipt)',
                                    'distribution': '4. เบิกจ่ายยา (distribution)',
                                    'inventory': '5. ยาคงคลัง (inventory)'
                                };
                                var fileTypeTabs = {
                                    'druglist': '#tab-f5-druglist-tab',
                                    'purchaseplan': '#tab-f5-purchaseplan-tab',
                                    'receipt': '#tab-f5-receipt-tab',
                                    'distribution': '#tab-f5-distribution-tab',
                                    'inventory': '#tab-f5-inventory-tab'
                                };

                                var html = '<div class="card border-success border-2 shadow-sm rounded-4 overflow-hidden mb-0">';
                                html += '  <div class="card-header bg-success bg-opacity-10 py-3 px-4 border-0">';
                                html += '    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">';
                                html += '      <div class="d-flex align-items-center gap-2">';
                                html += '        <div class="d-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow-xs" style="width: 36px; height: 36px;">';
                                html += '          <i class="fas fa-check fs-5"></i>';
                                html += '        </div>';
                                html += '        <div>';
                                html += '          <h6 class="fw-bold text-success mb-0">นำเข้าข้อมูล 5 แฟ้มยาสำเร็จเรียบร้อย!</h6>';
                                html += '          <small class="text-muted">ประมวลผลเสร็จสิ้น ' + (r.files_processed || 0) + ' ไฟล์ รวมทั้งสิ้น ' + Number(r.total_rows_imported || 0).toLocaleString() + ' รายการ</small>';
                                html += '        </div>';
                                html += '      </div>';
                                html += '      <span class="badge bg-success px-3 py-2 rounded-pill"><i class="fas fa-database me-1"></i> อัปเดตฐานข้อมูลแล้ว</span>';
                                html += '    </div>';
                                html += '  </div>';

                                html += '  <div class="card-body p-3 p-md-4 bg-white">';
                                html += '    <div class="row g-2 mb-3">';
                                if (r.details && r.details.length > 0) {
                                    for (var j = 0; j < r.details.length; j++) {
                                        var dt = r.details[j];
                                        var fTitle = fileTypeTitles[dt.file_type] || dt.table;
                                        var tabTarget = fileTypeTabs[dt.file_type] || '#tab-f5-druglist-tab';
                                        html += '      <div class="col-md-6 col-lg-4">';
                                        html += '        <div class="p-2.5 rounded-3 border bg-light d-flex align-items-center justify-content-between">';
                                        html += '          <div class="overflow-hidden me-2">';
                                        html += '            <div class="fw-bold text-dark small text-truncate">' + fTitle + '</div>';
                                        if (dt.success) {
                                            html += '            <div class="text-success small fw-semibold"><i class="fas fa-circle-check me-1"></i>+' + Number(dt.rows_imported || 0).toLocaleString() + ' แถว</div>';
                                        } else {
                                            html += '            <div class="text-danger small"><i class="fas fa-circle-exclamation me-1"></i>' + (dt.error || 'ผิดพลาด') + '</div>';
                                        }
                                        html += '          </div>';
                                        if (dt.success) {
                                            html += '          <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-nowrap btn-quick-tab" data-tab-target="' + tabTarget + '" title="คลิกดูตารางแฟ้มนี้">';
                                            html += '            <i class="fas fa-arrow-right"></i> ดูตาราง';
                                            html += '          </button>';
                                        }
                                        html += '        </div>';
                                        html += '      </div>';
                                    }
                                }
                                html += '    </div>';

                                html += '    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 border-top">';
                                html += '      <div class="d-flex align-items-center gap-2">';
                                html += '        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs btn-quick-tab" data-tab-target="#tab-f5-druglist-tab">';
                                html += '          <i class="fas fa-table-list me-1.5"></i> ไปดูตารางข้อมูลที่นำเข้า (แฟ้มที่ 1)';
                                html += '        </button>';
                                html += '        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold" id="btnResetUploadForm">';
                                html += '          <i class="fas fa-cloud-arrow-up me-1.5"></i> นำเข้าไฟล์เพิ่มอีกชุด';
                                html += '        </button>';
                                html += '      </div>';
                                html += '      <button type="button" class="btn btn-light border btn-sm rounded-pill px-3 text-muted" onclick="window.location.reload();" title="รีเฟรชหน้าภาพรวมหลัก">';
                                html += '        <i class="fas fa-rotate me-1"></i> รีเฟรชหน้าเว็บ';
                                html += '      </button>';
                                html += '    </div>';

                                html += '  </div>';
                                html += '</div>';

                                $('#mophF5ResultBox').html(html).show();
                                $('#btnStartUploadMophF5').prop('disabled', true).html('<i class="fas fa-check me-1.5"></i> นำเข้าสำเร็จแล้ว');
                            } else {
                                throw new Error(resp.message || 'การนำเข้าไม่สำเร็จ');
                            }
                        } catch (err) {
                            $('#mophF5ProgressBar').removeClass('bg-success').addClass('bg-danger');
                            $('#mophF5ResultBox').html('<div class="alert alert-danger border-0 rounded-3 p-3"><i class="fas fa-circle-exclamation me-1"></i> ' + (err.message || 'เกิดข้อผิดพลาดในการประมวลผล') + '</div>').show();
                            $('#btnStartUploadMophF5').prop('disabled', false).html('<i class="fas fa-cloud-arrow-up me-1.5"></i> เริ่มนำเข้าข้อมูล');
                        }
                    } else {
                        var errMsg = 'เกิดข้อผิดพลาดจากเซิร์ฟเวอร์ (HTTP ' + xhr.status + ')';
                        try {
                            var errResp = JSON.parse(xhr.responseText);
                            if (errResp.message) errMsg = errResp.message;
                        } catch (e) {}
                        $('#mophF5ProgressBar').removeClass('bg-success').addClass('bg-danger');
                        $('#mophF5ResultBox').html('<div class="alert alert-danger border-0 rounded-3 p-3"><i class="fas fa-triangle-exclamation me-1"></i> ' + errMsg + '</div>').show();
                        $('#btnStartUploadMophF5').prop('disabled', false).html('<i class="fas fa-cloud-arrow-up me-1.5"></i> เริ่มนำเข้าข้อมูล');
                    }
                }
            };

            xhr.send(formData);
        });
    </script>
@endpush
