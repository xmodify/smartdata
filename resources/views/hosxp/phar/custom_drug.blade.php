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

        @media (max-width: 992px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
        }

        .dataTables_length select {
            border-radius: 8px !important;
            padding: 0.25rem 1.8rem 0.25rem 0.6rem !important;
            border: 1px solid #d1d3e2 !important;
            font-size: 0.85rem !important;
            min-width: 65px;
            display: inline-block;
            margin: 0 4px;
        }

        /* Custom Multi-select for Drugs (รูปที่ 2) */
        .dropdown-menu-multiselect {
            min-width: 360px;
            max-width: 460px;
            max-height: 460px;
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid #e3e6f0;
            z-index: 1050;
        }

        .multiselect-header {
            position: sticky;
            top: 0;
            background: white;
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            z-index: 10;
        }

        .multiselect-search {
            border-radius: 8px;
            font-size: 0.85rem;
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
        }

        .multiselect-item-list {
            max-height: 280px;
            overflow-y: auto;
        }

        .multiselect-item {
            padding: 8px 15px;
            transition: background 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #f8f9fc;
        }

        .multiselect-item:hover {
            background-color: #f8f9fc;
        }

        .multiselect-item input[type="checkbox"] {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #4e73df;
        }

        .multiselect-item label {
            flex: 1;
            cursor: pointer;
            margin-bottom: 0;
            font-size: 0.85rem;
            color: #5a5c69;
            user-select: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .multiselect-item.selected {
            background-color: #eaecf4;
        }

        .dropdown-toggle-drug {
            background-color: white !important;
            border-radius: 8px !important;
            font-size: 0.8rem !important;
            min-width: 220px;
            border: 1px solid #dee2e6 !important;
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

        .nav-tabs-months {
            background: #f8fafc;
            border-radius: 12px;
            padding: 5px;
            gap: 5px;
        }
        .nav-tabs-months .nav-link {
            border-radius: 8px !important;
            font-size: 0.82rem !important;
            padding: 6px 14px !important;
            color: #64748b;
            font-weight: 600;
            border: none;
            transition: all 0.2s;
        }
        .nav-tabs-months .nav-link.active {
            background: #ffffff !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        .tab-pane-months-opd .nav-link.active {
            color: #10b981 !important;
        }
        .tab-pane-months-ipd .nav-link.active {
            color: #ef4444 !important;
        }

        .card-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        /* Pivot Table Styling */
        .table-pivot { font-size: 0.8rem; }
        .table-pivot th {
            text-align: center !important;
            vertical-align: middle !important;
            border: 1px solid #dee2e6 !important;
            font-weight: bold;
            padding: 6px 4px;
        }
        .table-pivot td {
            border: 1px solid #dee2e6 !important;
            padding: 6px 4px;
        }
        
        .th-total { background-color: #e0f2fe !important; color: #0369a1 !important; }
        .th-ucs { background-color: #ecfdf5 !important; color: #047857 !important; }
        .th-ofc { background-color: #fef2f2 !important; color: #b91c1c !important; }
        .th-lgo { background-color: #fef3c7 !important; color: #b45309 !important; }
        .th-sss { background-color: #faf5ff !important; color: #6b21a8 !important; }
        .th-other { background-color: #f3f4f6 !important; color: #374151 !important; }

        .text-green { color: #10b981 !important; }
        .text-red { color: #ef4444 !important; }
        .bg-pastel-green { background-color: #ecfdf5 !important; }
        .bg-pastel-red { background-color: #fef2f2 !important; }

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

        /* Service point pills */
        .btn-sp-filter {
            border-radius: 20px;
            font-size: 0.8rem;
            padding: 4px 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-sp-filter.active {
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        }

        /* DataTables Custom Styling */
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
        }

        /* Selected Drug Badges Box */
        .selected-drugs-box {
            background: #fff;
            border-radius: 12px;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.25rem;
            border: 1px solid #e0e7ff;
            background-color: #fcfdff;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.04);
        }
        .selected-drug-badge {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            padding: 4px 10px 4px 12px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            transition: all 0.2s;
        }
        .selected-drug-badge:hover {
            background: #dbeafe;
            border-color: #93c5fd;
        }
        .selected-drug-badge .btn-remove-tag {
            cursor: pointer;
            color: #60a5fa;
            font-size: 1.1rem;
            line-height: 1;
            padding: 0 4px;
            border-radius: 50%;
            transition: all 0.15s;
        }
        .selected-drug-badge .btn-remove-tag:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.15);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Page Header & Filter Form -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-capsules text-primary me-2"></i> {{ $title }}</h5>
                    <div class="text-muted small mt-1">
                        ข้อมูลปีงบประมาณ {{ $budget_year }} | สถิติภาพรวมประจำปี ({{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }})
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <form action="{{ route('hosxp.phar.custom_drug') }}" method="GET" class="m-0 header-form-controls">
                    <!-- Dropdown ค้นหาและเลือกตัวยา -->
                    <div class="d-flex align-items-center gap-1">
                        <span class="fw-bold text-muted small text-nowrap">ตัวยา:</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-white dropdown-toggle dropdown-toggle-drug shadow-sm d-flex justify-content-between align-items-center" 
                                    type="button" id="drugDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="dropdown-label text-truncate" style="max-width: 180px;">
                                    @if(empty($selected_icodes))
                                        -- เลือกตัวยา --
                                    @else
                                        เลือก ({{ count($selected_icodes) }}) รายการ
                                    @endif
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-multiselect" aria-labelledby="drugDropdown">
                                <div class="multiselect-header">
                                    <input type="text" class="form-control form-control-sm multiselect-search mb-2" 
                                        placeholder="ค้นหายา..." id="drugSearch">
                                    <div class="form-check ms-1 mt-1">
                                        <input class="form-check-input" type="checkbox" id="selectAllDrug" 
                                            {{ !empty($selected_icodes) && count($selected_icodes) == count($drug_list) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-primary small" for="selectAllDrug" style="cursor: pointer;">
                                            เลือกทั้งหมด
                                        </label>
                                    </div>
                                </div>
                                <div class="multiselect-item-list" id="drugList">
                                    @foreach($drug_list as $drug)
                                        <div class="multiselect-item {{ in_array($drug->icode, $selected_icodes) ? 'selected' : '' }}">
                                            <input type="checkbox" name="drug_icodes[]" value="{{ $drug->icode }}" 
                                                id="drug_{{ $drug->icode }}" 
                                                class="drug-checkbox"
                                                {{ in_array($drug->icode, $selected_icodes) ? 'checked' : '' }}>
                                            <label for="drug_{{ $drug->icode }}">{{ $drug->drug_name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="p-2 border-top bg-light text-center">
                                    <small class="text-muted">เลือกได้หลายรายการ</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Budget year select (ตรงตามรูปที่ 2) -->
                    <div class="input-group input-group-sm shadow-sm" style="width: 230px; border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary fw-bold" style="font-size: 0.85rem;">
                            <i class="fas fa-calendar-alt me-1"></i> เลือก
                        </span>
                        <select class="form-select border-start-0 ps-2" name="budget_year" id="budget_year" style="font-size: 0.85rem; cursor: pointer;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="budget_year_changed" id="budget_year_changed" value="0">
                    </div>

                    <!-- ปุ่มค้นหาแยก -->
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm d-flex align-items-center gap-1" style="border-radius: 8px; font-size: 0.85rem; height: 31px;">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </form>
            </div>
        </div>

        <!-- Selected Drugs Box (กล่องแสดงรายการยาที่เลือก) -->
        <div id="selectedDrugsBox" class="selected-drugs-box" style="display: none;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted small fw-bold text-nowrap">
                        <i class="fas fa-check-circle text-primary me-1"></i>ตัวยาที่เลือก (<span id="selectedCount" class="text-primary fw-bold">0</span> รายการ):
                    </span>
                    <div id="selectedBadgesContainer" class="d-flex flex-wrap gap-2 align-items-center">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
                <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none p-0 text-nowrap" id="clearAllSelectedDrugs">
                    <i class="fas fa-trash-alt me-1"></i>ล้างที่เลือกทั้งหมด
                </button>
            </div>
        </div>

        @if(!$has_searched)
            <!-- Initial Empty State Prompt -->
            <div class="card card-custom p-5 bg-white text-center shadow-sm my-4" style="border-radius: 20px;">
                <div class="py-4">
                    <div class="mb-3">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary" style="width: 80px; height: 80px;">
                            <i class="fas fa-search-plus fa-2x"></i>
                        </span>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">กรุณาเลือกตัวยาและกดปุ่ม "ค้นหา"</h5>
                    <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">
                        คลิกที่กล่อง <strong>"ตัวยา"</strong> ด้านบนเพื่อพิมพ์ค้นหาและเลือกชื่อยาที่ต้องการดูข้อมูล จากนั้นกดปุ่ม <strong>"ค้นหา"</strong> เพื่อประมวลผลข้อมูลสถิติ กราฟ และรายชื่อผู้ป่วย
                    </p>
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4" onclick="$('#drugDropdown').dropdown('show')">
                        <i class="fas fa-hand-pointer me-1"></i> คลิกเลือกตัวยาที่นี่
                    </button>
                </div>
            </div>
        @else
            <!-- Main Content with Tabs (OPD & IPD) -->
            <ul class="nav nav-tabs nav-tabs-custom" id="customDrugReportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd-content" type="button" role="tab"><i class="fas fa-user-nurse me-2"></i>ผู้ป่วยนอก (OPD)</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd-content" type="button" role="tab"><i class="fas fa-bed-pulse me-2"></i>ผู้ป่วยใน (IPD)</button>
                </li>
            </ul>

            <div class="tab-content" id="customDrugReportTabsContent">
                <!-- ===================== OPD TAB ===================== -->
                <div class="tab-pane fade show active" id="opd-content" role="tabpanel">
                    <!-- OPD Chart -->
                    <div class="card card-custom p-4 mb-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-green mb-0"><i class="fas fa-chart-line me-2"></i>ปริมาณการใช้ยา (Qty) รายเดือน แยกตามตัวยา (OPD)</h6>
                        </div>
                        <div id="chart-opd" style="min-height: 300px;"></div>
                    </div>

                    <!-- Monthly Sub-tabs OPD -->
                    <div class="nav nav-tabs nav-tabs-months tab-pane-months-opd mb-3" role="tablist">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#opd-month-all" type="button">รวมทั้งปี</button>
                        @foreach ($months_list as $m)
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#opd-month-{{ $m['key'] }}" type="button">{{ $m['label'] }}</button>
                        @endforeach
                    </div>

                    <div class="tab-content mb-4">
                        <!-- รวมทั้งปี OPD -->
                        <div class="tab-pane fade show active" id="opd-month-all">
                            <div class="card card-custom p-4 bg-white">
                                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-table text-success me-2"></i>ตารางสถิติข้อมูลการใช้ยา (OPD) - ยอดรวมทั้งปี</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-pivot align-middle w-100 dataTable-export">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="width: 70px;">รหัสยา</th>
                                                <th rowspan="2">ชื่อยา</th>
                                                <th rowspan="2">ชื่อสามัญ</th>
                                                <th colspan="4" class="th-total">รวมทั้งหมด</th>
                                                <th colspan="4" class="th-ucs">สิทธิ บัตรทอง (UCS)</th>
                                                <th colspan="4" class="th-ofc">สิทธิ ข้าราชการ (OFC)</th>
                                                <th colspan="4" class="th-lgo">สิทธิ อปท. (LGO)</th>
                                                <th colspan="4" class="th-sss">สิทธิ ประกันสังคม</th>
                                                <th colspan="4" class="th-other">สิทธิ อื่นๆ</th>
                                            </tr>
                                            <tr>
                                                <th class="th-total">Visit</th><th class="th-total">Qty</th><th class="th-total">ทุน (บาท)</th><th class="th-total">มูลค่า (บาท)</th>
                                                <th class="th-ucs">Visit</th><th class="th-ucs">Qty</th><th class="th-ucs">ทุน (บาท)</th><th class="th-ucs">มูลค่า (บาท)</th>
                                                <th class="th-ofc">Visit</th><th class="th-ofc">Qty</th><th class="th-ofc">ทุน (บาท)</th><th class="th-ofc">มูลค่า (บาท)</th>
                                                <th class="th-lgo">Visit</th><th class="th-lgo">Qty</th><th class="th-lgo">ทุน (บาท)</th><th class="th-lgo">มูลค่า (บาท)</th>
                                                <th class="th-sss">Visit</th><th class="th-sss">Qty</th><th class="th-sss">ทุน (บาท)</th><th class="th-sss">มูลค่า (บาท)</th>
                                                <th class="th-other">Visit</th><th class="th-other">Qty</th><th class="th-other">ทุน (บาท)</th><th class="th-other">มูลค่า (บาท)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($custom_opd as $row)
                                                <tr>
                                                    <td class="text-center font-monospace">{{ $row->icode }}</td>
                                                    <td class="fw-bold">{{ $row->drug_name }}</td>
                                                    <td class="text-muted">{{ $row->generic_name }}</td>
                                                    <td class="text-end fw-bold">{{ number_format($row->total_visit) }}</td>
                                                    <td class="text-end fw-bold text-primary">{{ number_format($row->total_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->total_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-success">{{ number_format($row->total_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->ucs_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->ucs_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->ucs_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->ofc_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->ofc_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->ofc_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->lgo_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->lgo_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->lgo_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->sss_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->sss_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->sss_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->other_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->other_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->other_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- แต่ละเดือน OPD -->
                        @foreach ($months_list as $m)
                            <div class="tab-pane fade" id="opd-month-{{ $m['key'] }}">
                                <div class="card card-custom p-4 bg-white">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-table text-success me-2"></i>ตารางสถิติข้อมูลการใช้ยา (OPD) - ประจำเดือน {{ $m['label'] }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-pivot align-middle w-100 dataTable-export">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="width: 70px;">รหัสยา</th>
                                                    <th rowspan="2">ชื่อยา</th>
                                                    <th rowspan="2">ชื่อสามัญ</th>
                                                    <th colspan="4" class="th-total">รวมทั้งหมด</th>
                                                    <th colspan="4" class="th-ucs">สิทธิ บัตรทอง (UCS)</th>
                                                    <th colspan="4" class="th-ofc">สิทธิ ข้าราชการ (OFC)</th>
                                                    <th colspan="4" class="th-lgo">สิทธิ อปท. (LGO)</th>
                                                    <th colspan="4" class="th-sss">สิทธิ ประกันสังคม</th>
                                                    <th colspan="4" class="th-other">สิทธิ อื่นๆ</th>
                                                </tr>
                                                <tr>
                                                    <th class="th-total">Visit</th><th class="th-total">Qty</th><th class="th-total">ทุน (บาท)</th><th class="th-total">มูลค่า (บาท)</th>
                                                    <th class="th-ucs">Visit</th><th class="th-ucs">Qty</th><th class="th-ucs">ทุน (บาท)</th><th class="th-ucs">มูลค่า (บาท)</th>
                                                    <th class="th-ofc">Visit</th><th class="th-ofc">Qty</th><th class="th-ofc">ทุน (บาท)</th><th class="th-ofc">มูลค่า (บาท)</th>
                                                    <th class="th-lgo">Visit</th><th class="th-lgo">Qty</th><th class="th-lgo">ทุน (บาท)</th><th class="th-lgo">มูลค่า (บาท)</th>
                                                    <th class="th-sss">Visit</th><th class="th-sss">Qty</th><th class="th-sss">ทุน (บาท)</th><th class="th-sss">มูลค่า (บาท)</th>
                                                    <th class="th-other">Visit</th><th class="th-other">Qty</th><th class="th-other">ทุน (บาท)</th><th class="th-other">มูลค่า (บาท)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($custom_opd_monthly[$m['key']] ?? [] as $row)
                                                    <tr>
                                                        <td class="text-center font-monospace">{{ $row->icode }}</td>
                                                        <td class="fw-bold">{{ $row->drug_name }}</td>
                                                        <td class="text-muted">{{ $row->generic_name }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($row->total_visit) }}</td>
                                                        <td class="text-end fw-bold text-primary">{{ number_format($row->total_qty) }}</td>
                                                        <td class="text-end text-muted">{{ number_format($row->total_cost, 2) }}</td>
                                                        <td class="text-end fw-bold text-success">{{ number_format($row->total_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->ucs_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->ucs_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->ucs_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->ofc_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->ofc_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->ofc_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->lgo_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->lgo_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->lgo_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->sss_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->sss_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->sss_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->other_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->other_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->other_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- ===================== IPD TAB ===================== -->
                <div class="tab-pane fade" id="ipd-content" role="tabpanel">
                    <!-- IPD Chart -->
                    <div class="card card-custom p-4 mb-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-red mb-0"><i class="fas fa-chart-line me-2"></i>ปริมาณการใช้ยา (Qty) รายเดือน แยกตามตัวยา (IPD)</h6>
                        </div>
                        <div id="chart-ipd" style="min-height: 300px;"></div>
                    </div>

                    <!-- Monthly Sub-tabs IPD -->
                    <div class="nav nav-tabs nav-tabs-months tab-pane-months-ipd mb-3" role="tablist">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#ipd-month-all" type="button">รวมทั้งปี</button>
                        @foreach ($months_list as $m)
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ipd-month-{{ $m['key'] }}" type="button">{{ $m['label'] }}</button>
                        @endforeach
                    </div>

                    <div class="tab-content mb-4">
                        <!-- รวมทั้งปี IPD -->
                        <div class="tab-pane fade show active" id="ipd-month-all">
                            <div class="card card-custom p-4 bg-white">
                                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-table text-danger me-2"></i>ตารางสถิติข้อมูลการใช้ยา (IPD) - ยอดรวมทั้งปี</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-pivot align-middle w-100 dataTable-export">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="width: 70px;">รหัสยา</th>
                                                <th rowspan="2">ชื่อยา</th>
                                                <th rowspan="2">ชื่อสามัญ</th>
                                                <th colspan="4" class="th-total">รวมทั้งหมด</th>
                                                <th colspan="4" class="th-ucs">สิทธิ บัตรทอง (UCS)</th>
                                                <th colspan="4" class="th-ofc">สิทธิ ข้าราชการ (OFC)</th>
                                                <th colspan="4" class="th-lgo">สิทธิ อปท. (LGO)</th>
                                                <th colspan="4" class="th-sss">สิทธิ ประกันสังคม</th>
                                                <th colspan="4" class="th-other">สิทธิ อื่นๆ</th>
                                            </tr>
                                            <tr>
                                                <th class="th-total">Visit</th><th class="th-total">Qty</th><th class="th-total">ทุน (บาท)</th><th class="th-total">มูลค่า (บาท)</th>
                                                <th class="th-ucs">Visit</th><th class="th-ucs">Qty</th><th class="th-ucs">ทุน (บาท)</th><th class="th-ucs">มูลค่า (บาท)</th>
                                                <th class="th-ofc">Visit</th><th class="th-ofc">Qty</th><th class="th-ofc">ทุน (บาท)</th><th class="th-ofc">มูลค่า (บาท)</th>
                                                <th class="th-lgo">Visit</th><th class="th-lgo">Qty</th><th class="th-lgo">ทุน (บาท)</th><th class="th-lgo">มูลค่า (บาท)</th>
                                                <th class="th-sss">Visit</th><th class="th-sss">Qty</th><th class="th-sss">ทุน (บาท)</th><th class="th-sss">มูลค่า (บาท)</th>
                                                <th class="th-other">Visit</th><th class="th-other">Qty</th><th class="th-other">ทุน (บาท)</th><th class="th-other">มูลค่า (บาท)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($custom_ipd as $row)
                                                <tr>
                                                    <td class="text-center font-monospace">{{ $row->icode }}</td>
                                                    <td class="fw-bold">{{ $row->drug_name }}</td>
                                                    <td class="text-muted">{{ $row->generic_name }}</td>
                                                    <td class="text-end fw-bold">{{ number_format($row->total_visit) }}</td>
                                                    <td class="text-end fw-bold text-primary">{{ number_format($row->total_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->total_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-danger">{{ number_format($row->total_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->ucs_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->ucs_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->ucs_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->ofc_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->ofc_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->ofc_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->lgo_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->lgo_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->lgo_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->sss_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->sss_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->sss_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>

                                                    <td class="text-end">{{ number_format($row->other_visit) }}</td>
                                                    <td class="text-end">{{ number_format($row->other_qty) }}</td>
                                                    <td class="text-end">{{ number_format($row->other_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- แต่ละเดือน IPD -->
                        @foreach ($months_list as $m)
                            <div class="tab-pane fade" id="ipd-month-{{ $m['key'] }}">
                                <div class="card card-custom p-4 bg-white">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-table text-danger me-2"></i>ตารางสถิติข้อมูลการใช้ยา (IPD) - ประจำเดือน {{ $m['label'] }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-pivot align-middle w-100 dataTable-export">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="width: 70px;">รหัสยา</th>
                                                    <th rowspan="2">ชื่อยา</th>
                                                    <th rowspan="2">ชื่อสามัญ</th>
                                                    <th colspan="4" class="th-total">รวมทั้งหมด</th>
                                                    <th colspan="4" class="th-ucs">สิทธิ บัตรทอง (UCS)</th>
                                                    <th colspan="4" class="th-ofc">สิทธิ ข้าราชการ (OFC)</th>
                                                    <th colspan="4" class="th-lgo">สิทธิ อปท. (LGO)</th>
                                                    <th colspan="4" class="th-sss">สิทธิ ประกันสังคม</th>
                                                    <th colspan="4" class="th-other">สิทธิ อื่นๆ</th>
                                                </tr>
                                                <tr>
                                                    <th class="th-total">Visit</th><th class="th-total">Qty</th><th class="th-total">ทุน (บาท)</th><th class="th-total">มูลค่า (บาท)</th>
                                                    <th class="th-ucs">Visit</th><th class="th-ucs">Qty</th><th class="th-ucs">ทุน (บาท)</th><th class="th-ucs">มูลค่า (บาท)</th>
                                                    <th class="th-ofc">Visit</th><th class="th-ofc">Qty</th><th class="th-ofc">ทุน (บาท)</th><th class="th-ofc">มูลค่า (บาท)</th>
                                                    <th class="th-lgo">Visit</th><th class="th-lgo">Qty</th><th class="th-lgo">ทุน (บาท)</th><th class="th-lgo">มูลค่า (บาท)</th>
                                                    <th class="th-sss">Visit</th><th class="th-sss">Qty</th><th class="th-sss">ทุน (บาท)</th><th class="th-sss">มูลค่า (บาท)</th>
                                                    <th class="th-other">Visit</th><th class="th-other">Qty</th><th class="th-other">ทุน (บาท)</th><th class="th-other">มูลค่า (บาท)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($custom_ipd_monthly[$m['key']] ?? [] as $row)
                                                    <tr>
                                                        <td class="text-center font-monospace">{{ $row->icode }}</td>
                                                        <td class="fw-bold">{{ $row->drug_name }}</td>
                                                        <td class="text-muted">{{ $row->generic_name }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($row->total_visit) }}</td>
                                                        <td class="text-end fw-bold text-primary">{{ number_format($row->total_qty) }}</td>
                                                        <td class="text-end text-muted">{{ number_format($row->total_cost, 2) }}</td>
                                                        <td class="text-end fw-bold text-danger">{{ number_format($row->total_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->ucs_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->ucs_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->ucs_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->ofc_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->ofc_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->ofc_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->lgo_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->lgo_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->lgo_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->sss_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->sss_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->sss_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>

                                                        <td class="text-end">{{ number_format($row->other_visit) }}</td>
                                                        <td class="text-end">{{ number_format($row->other_qty) }}</td>
                                                        <td class="text-end">{{ number_format($row->other_cost, 2) }}</td>
                                                        <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- BOTTOM SECTION: ตารางรายชื่อผู้ป่วยที่ได้รับยา (รายละเอียด + วิธีใช้ยา + แยกจุดบริการ) -->
            <!-- ========================================================================= -->
            <div class="card card-custom p-4 bg-white mt-4 shadow-sm" style="border-radius: 20px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="fas fa-users text-primary me-2"></i>รายชื่อผู้ป่วยที่ได้รับยา (รายละเอียดรายบุคคล)
                        </h5>
                        <div class="text-muted small">
                            แสดงข้อมูลตามช่วงวันที่ <span id="displayDateRange" class="fw-bold text-dark">{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}</span>
                        </div>
                    </div>

                    <!-- Table Independent Date Filter Controls (เหมือนหน้ารายงาน Stroke) -->
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group input-group-sm shadow-sm" style="width: 145px;">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-day"></i></span>
                            <input type="text" id="table_start_date" class="form-control border-start-0 ps-0" value="{{ $table_start_date }}" placeholder="วันที่เริ่มต้น">
                        </div>
                        <div class="input-group input-group-sm shadow-sm" style="width: 145px;">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-day"></i></span>
                            <input type="text" id="table_end_date" class="form-control border-start-0 ps-0" value="{{ $table_end_date }}" placeholder="วันที่สิ้นสุด">
                        </div>
                        <button type="button" id="btnFilterTable" class="btn btn-primary btn-sm shadow-sm px-3 fw-bold">
                            <i class="fas fa-search me-1"></i> ค้นหา
                        </button>
                        <div class="btn-group btn-group-sm shadow-sm">
                            <button type="button" id="btnCurrentMonth" class="btn btn-outline-secondary" title="เลือกเดือนปัจจุบัน">
                                เดือนนี้
                            </button>
                            <button type="button" id="btnPrevMonth" class="btn btn-outline-secondary" title="เลือกเดือนก่อนหน้า">
                                เดือนก่อน
                            </button>
                            <button type="button" id="btnFullYear" class="btn btn-outline-secondary" title="เลือกทั้งปีงบประมาณ">
                                ทั้งปี
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Service Point Quick Filter Buttons -->
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                    <div class="d-flex flex-wrap align-items-center gap-1">
                        <span class="text-muted small fw-bold me-1">จุดบริการ:</span>
                        <button type="button" class="btn btn-outline-primary btn-sp-filter active" data-sp="ALL">
                            ทั้งหมด <span class="badge bg-primary rounded-pill ms-1" id="sp-count-ALL">{{ $sp_counts['ALL'] }}</span>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sp-filter" data-sp="ER">
                            🚨 ER <span class="badge bg-danger rounded-pill ms-1" id="sp-count-ER">{{ $sp_counts['ER'] }}</span>
                        </button>
                        <button type="button" class="btn btn-outline-purple btn-sp-filter" style="border-color: #7c3aed; color: #7c3aed;" data-sp="ICU">
                            🩺 ICU <span class="badge rounded-pill ms-1" style="background-color: #7c3aed;" id="sp-count-ICU">{{ $sp_counts['ICU'] }}</span>
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sp-filter" style="border-color: #d97706; color: #d97706;" data-sp="VIP">
                            🏨 VIP <span class="badge rounded-pill ms-1" style="background-color: #d97706;" id="sp-count-VIP">{{ $sp_counts['VIP'] }}</span>
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sp-filter" data-sp="IPD">
                            🛏️ IPD <span class="badge bg-info rounded-pill ms-1" id="sp-count-IPD">{{ $sp_counts['IPD'] }}</span>
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sp-filter" data-sp="OPD">
                            🏥 OPD <span class="badge bg-success rounded-pill ms-1" id="sp-count-OPD">{{ $sp_counts['OPD'] }}</span>
                        </button>
                    </div>
                </div>

                <div id="tableContainer">
                    @include('hosxp.phar.partials._table_patient_prescriptions')
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>

    <script>
        $(document).ready(function () {
            // Disable DataTables alerts (suppress tn warnings gracefully)
            if ($.fn.dataTable) {
                $.fn.dataTable.ext.errMode = 'none';
            }

            let tableStartPicker, tableEndPicker;

            // 1. Setup Flatpickr
            if (typeof flatpickr !== 'undefined') {
                const yearOffset = 543;
                const commonConfig = {
                    locale: "th",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "j M Y",
                    allowInput: false,
                    onReady: function (selectedDates, dateStr, instance) {
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
                    onChange: function (selectedDates, dateStr, instance) {
                        if (instance.altInput && selectedDates.length > 0) {
                            const date = selectedDates[0];
                            const day = date.getDate();
                            const month = instance.l10n.months.shorthand[date.getMonth()];
                            const year = date.getFullYear() + yearOffset;
                            instance.altInput.value = `${day} ${month} ${year}`;
                        }
                    }
                };
                if (document.getElementById("start_date")) flatpickr("#start_date", commonConfig);
                if (document.getElementById("end_date")) flatpickr("#end_date", commonConfig);
                tableStartPicker = flatpickr("#table_start_date", commonConfig);
                tableEndPicker = flatpickr("#table_end_date", commonConfig);
            }

            // Sync Budget Year
            $('select[name="budget_year"]').on('change', function () {
                $('#budget_year_changed').val('1');
                $(this).closest('form').submit();
            });

            // 2. Setup Multi-Select Dropdown for Drugs (รูปที่ 2)
            const drugSearch = document.getElementById('drugSearch');
            const drugList = document.getElementById('drugList');
            const selectAllDrug = document.getElementById('selectAllDrug');
            const drugCheckboxes = document.querySelectorAll('.drug-checkbox');
            const drugDropdownBtn = document.getElementById('drugDropdown');
            const drugLabel = drugDropdownBtn ? drugDropdownBtn.querySelector('.dropdown-label') : null;

            $('.dropdown-menu-multiselect').on('click', function (e) {
                e.stopPropagation();
            });

            if (drugSearch) {
                drugSearch.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase();
                    const items = drugList.querySelectorAll('.multiselect-item');
                    items.forEach(item => {
                        const text = item.querySelector('label').textContent.toLowerCase();
                        item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
                    });
                });
            }

            if (selectAllDrug) {
                selectAllDrug.addEventListener('change', function () {
                    const isChecked = this.checked;
                    const items = drugList.querySelectorAll('.multiselect-item');
                    items.forEach(item => {
                        if (item.style.display !== 'none') {
                            const cb = item.querySelector('.drug-checkbox');
                            cb.checked = isChecked;
                            item.classList.toggle('selected', isChecked);
                        }
                    });
                    updateDrugDropdownLabel();
                });
            }

            drugCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    this.closest('.multiselect-item').classList.toggle('selected', this.checked);
                    updateDrugDropdownLabel();
                });
            });

            function updateDrugDropdownLabel() {
                if (!drugLabel) return;
                const total = document.querySelectorAll('.drug-checkbox').length;
                const checkedCount = document.querySelectorAll('.drug-checkbox:checked').length;
                
                if (checkedCount === 0) {
                    drugLabel.textContent = '-- เลือกตัวยา --';
                } else if (checkedCount === total) {
                    drugLabel.textContent = 'ทุกตัวยา (' + checkedCount + ')';
                } else {
                    drugLabel.textContent = 'เลือก (' + checkedCount + ') รายการ';
                }

                renderSelectedBadges();
            }

            function renderSelectedBadges() {
                const checkedCbs = document.querySelectorAll('.drug-checkbox:checked');
                const box = document.getElementById('selectedDrugsBox');
                const container = document.getElementById('selectedBadgesContainer');
                const countSpan = document.getElementById('selectedCount');
                
                if (!box || !container) return;

                if (checkedCbs.length === 0) {
                    box.style.display = 'none';
                    container.innerHTML = '';
                    if (countSpan) countSpan.textContent = '0';
                    return;
                }

                box.style.display = 'block';
                if (countSpan) countSpan.textContent = checkedCbs.length;

                let html = '';
                checkedCbs.forEach(cb => {
                    const icode = cb.value;
                    const item = cb.closest('.multiselect-item');
                    const label = item ? item.querySelector('label').textContent.trim() : icode;
                    html += `
                        <span class="selected-drug-badge" title="${label}">
                            <i class="fas fa-pills text-primary"></i>
                            <span class="text-truncate" style="max-width: 250px;">${label}</span>
                            <span class="btn-remove-tag" data-icode="${icode}" title="นำออก">&times;</span>
                        </span>
                    `;
                });
                container.innerHTML = html;

                // Bind remove tag event
                container.querySelectorAll('.btn-remove-tag').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const icode = this.getAttribute('data-icode');
                        const targetCb = document.getElementById('drug_' + icode);
                        if (targetCb) {
                            targetCb.checked = false;
                            const item = targetCb.closest('.multiselect-item');
                            if (item) item.classList.remove('selected');
                            if (selectAllDrug) selectAllDrug.checked = false;
                            updateDrugDropdownLabel();
                        }
                    });
                });
            }

            // Clear all selected drugs button
            $('#clearAllSelectedDrugs').on('click', function() {
                drugCheckboxes.forEach(cb => {
                    cb.checked = false;
                    const item = cb.closest('.multiselect-item');
                    if (item) item.classList.remove('selected');
                });
                if (selectAllDrug) selectAllDrug.checked = false;
                updateDrugDropdownLabel();
            });

            // Initial render on load
            updateDrugDropdownLabel();

            @if($has_searched)
                // Helper for Pivot Excel Export Headers
                const getPivotExportHeaderFormatter = function() {
                    return function(data, columnIdx) {
                        if (columnIdx === 0) return 'รหัสยา';
                        if (columnIdx === 1) return 'ชื่อยา';
                        if (columnIdx === 2) return 'ชื่อสามัญ';
                        const groupIndex = Math.floor((columnIdx - 3) / 4);
                        const colType = (columnIdx - 3) % 4;
                        const groups = ['รวมทั้งหมด', 'สิทธิ บัตรทอง (UCS)', 'สิทธิ ข้าราชการ (OFC)', 'สิทธิ อปท. (LGO)', 'สิทธิ ประกันสังคม', 'สิทธิ อื่นๆ'];
                        const types = ['Visit', 'Qty', 'ทุน (บาท)', 'มูลค่า (บาท)'];
                        return (groups[groupIndex] || '') + ' - ' + (types[colType] || '');
                    };
                };

                // 3. Initialize Pivot DataTables with Excel
                $('.dataTable-export').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    deferRender: true,
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success',
                            title: 'Drug_Usage_Summary_{{ date('Y-m-d') }}',
                            exportOptions: {
                                columns: ':visible',
                                format: { header: getPivotExportHeaderFormatter() }
                            }
                        }
                    ],
                    language: {
                        search: "ค้นหา:",
                        lengthMenu: "แสดง _MENU_ รายการ",
                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                        paginate: { previous: "ก่อนหน้า", next: "ถัดไป" },
                        emptyTable: "ไม่พบข้อมูลการใช้ยาในช่วงเวลานี้"
                    },
                    order: [],
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]]
                });

                // 4. Initialize Patient Prescription DataTable
                let ptTable = null;

                function initPatientTable() {
                    if ($.fn.DataTable.isDataTable('#patientPrescriptionTable')) {
                        $('#patientPrescriptionTable').DataTable().destroy();
                    }

                    ptTable = $('#patientPrescriptionTable').DataTable({
                        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                        deferRender: true,
                        processing: true,
                        buttons: [
                            {
                                text: '<i class="fa-solid fa-file-excel me-1"></i> ส่งออก Excel',
                                className: 'btn btn-success btn-sm shadow-sm fw-bold',
                                action: function (e, dt, node, config) {
                                    triggerCustomDrugExport();
                                }
                            }
                        ],
                        language: {
                            search: "ค้นหาผู้ป่วย/HN/CID/แพทย์:",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            infoEmpty: "แสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
                            infoFiltered: "(กรองจากทั้งหมด _MAX_ รายการ)",
                            zeroRecords: "ไม่พบข้อมูลรายการสั่งยาที่ตรงกับเงื่อนไข",
                            paginate: { previous: "ก่อนหน้า", next: "ถัดไป" },
                            emptyTable: "ไม่พบข้อมูลรายการสั่งยาในช่วงเวลานี้"
                        },
                        order: [],
                        pageLength: 10,
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
                        footerCallback: function (row, data, start, end, display) {
                            const api = this.api();

                            const parseNumber = function (val) {
                                if (typeof val === 'number') return val;
                                if (typeof val === 'string') {
                                    const clean = val.replace(/<[^>]*>/g, '').replace(/,/g, '').trim();
                                    const num = parseFloat(clean);
                                    return isNaN(num) ? 0 : num;
                                }
                                return 0;
                            };

                            // Column 9: จำนวน (Qty)
                            const totalQty = api
                                .column(9, { search: 'applied' })
                                .data()
                                .reduce((a, b) => a + parseNumber(b), 0);

                            // Column 11: มูลค่า (บาท) (Price)
                            const totalPrice = api
                                .column(11, { search: 'applied' })
                                .data()
                                .reduce((a, b) => a + parseNumber(b), 0);

                            $('#footer-total-qty').text(totalQty.toLocaleString());
                            $('#footer-total-price').text(totalPrice.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        }
                    });
                }

                // Direct Server-Side Streaming CSV/Excel Export Handler
                function triggerCustomDrugExport() {
                    const selectedIcodes = [];
                    document.querySelectorAll('.drug-checkbox:checked').forEach(cb => {
                        selectedIcodes.push(cb.value);
                    });

                    if (selectedIcodes.length === 0) {
                        alert('กรุณาเลือกตัวยาอย่างน้อย 1 รายการก่อนส่งออกข้อมูล');
                        return;
                    }

                    const s = $('#table_start_date').val() || '{{ $table_start_date }}';
                    const e_date = $('#table_end_date').val() || '{{ $table_end_date }}';
                    const activeSp = $('.btn-sp-filter.active').data('sp') || 'ALL';

                    const params = new URLSearchParams();
                    selectedIcodes.forEach(code => params.append('drug_icodes[]', code));
                    params.append('table_start_date', s);
                    params.append('table_end_date', e_date);
                    params.append('service_point', activeSp);
                    params.append('budget_year', '{{ $budget_year }}');

                    window.location.href = "{{ route('hosxp.phar.custom_drug_export') }}?" + params.toString();
                }

                // AJAX function to load patient table data
                function loadPatientTableData(startDate, endDate) {
                    const $btn = $('#btnFilterTable');
                    const originalText = $btn.html();
                    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> ค้นหา...');
                    $('#tableContainer').css('opacity', '0.5');

                    const selectedIcodes = [];
                    document.querySelectorAll('.drug-checkbox:checked').forEach(cb => {
                        selectedIcodes.push(cb.value);
                    });

                    $.ajax({
                        url: "{{ route('hosxp.phar.custom_drug') }}",
                        method: 'GET',
                        data: {
                            drug_icodes: selectedIcodes,
                            table_start_date: startDate,
                            table_end_date: endDate,
                            budget_year: "{{ $budget_year }}"
                        },
                        dataType: 'json',
                        success: function (res) {
                            if (res.success && res.html) {
                                $('#tableContainer').html(res.html);
                                initPatientTable();

                                // Update Header Subtitle
                                $('#displayDateRange').text(`${res.start_date_thai} ถึง ${res.end_date_thai}`);

                                // Update Badge Counts
                                if (res.sp_counts) {
                                    $('#sp-count-ALL').text(res.sp_counts.ALL || 0);
                                    $('#sp-count-ER').text(res.sp_counts.ER || 0);
                                    $('#sp-count-ICU').text(res.sp_counts.ICU || 0);
                                    $('#sp-count-VIP').text(res.sp_counts.VIP || 0);
                                    $('#sp-count-IPD').text(res.sp_counts.IPD || 0);
                                    $('#sp-count-OPD').text(res.sp_counts.OPD || 0);
                                }

                                // Reset active SP filter button to ALL
                                $('.btn-sp-filter').removeClass('active');
                                $('.btn-sp-filter[data-sp="ALL"]').addClass('active');
                            }
                        },
                        error: function (err) {
                            console.error("Failed to load patient table data", err);
                            alert("เกิดข้อผิดพลาดในการโหลดข้อมูลตารางรายชื่อ กรุณาลองใหม่อีกครั้ง");
                        },
                        complete: function () {
                            $('#tableContainer').css('opacity', '1');
                            $btn.prop('disabled', false).html(originalText);
                        }
                    });
                }

                // Table Filter Button click
                $('#btnFilterTable').on('click', function() {
                    const s = $('#table_start_date').val();
                    const e = $('#table_end_date').val();
                    loadPatientTableData(s, e);
                });

                // Quick Filter: Current Month
                $('#btnCurrentMonth').on('click', function() {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
                    const s = `${y}-${m}-01`;
                    const e = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

                    if (tableStartPicker) tableStartPicker.setDate(s, true);
                    if (tableEndPicker) tableEndPicker.setDate(e, true);
                    loadPatientTableData(s, e);
                });

                // Quick Filter: Previous Month
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
                    loadPatientTableData(s, e);
                });

                // Quick Filter: Full Year
                $('#btnFullYear').on('click', function() {
                    const s = "{{ $start_date }}";
                    const e = "{{ $end_date }}";

                    if (tableStartPicker) tableStartPicker.setDate(s, true);
                    if (tableEndPicker) tableEndPicker.setDate(e, true);
                    loadPatientTableData(s, e);
                });

                // Initialize Patient Table on page load
                initPatientTable();

                // Service Point Filter Buttons Logic (Delegated)
                $(document).on('click', '.btn-sp-filter', function() {
                    $('.btn-sp-filter').removeClass('active');
                    $(this).addClass('active');

                    const sp = $(this).data('sp');
                    if (ptTable) {
                        if (sp === 'ALL') {
                            ptTable.column(6).search('').draw();
                        } else {
                            ptTable.column(6).search('^' + sp + '$', true, false).draw();
                        }
                    }
                });

                // 5. ApexCharts Setup
                const categories = @json($month_categories);
                const opdSeries = @json($chart_series_opd);
                const ipdSeries = @json($chart_series_ipd);

                const chartOptions = {
                    chart: {
                        type: 'line',
                        height: 320,
                        toolbar: { show: true },
                        zoom: { enabled: true },
                        fontFamily: 'inherit'
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    xaxis: {
                        categories: categories
                    },
                    yaxis: {
                        title: { text: 'ปริมาณการใช้ยา (Qty)' },
                        labels: {
                            formatter: function (val) {
                                return parseInt(val).toLocaleString();
                            }
                        }
                    },
                    markers: {
                        size: 4
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString() + ' หน่วย';
                            }
                        }
                    }
                };

                if (document.querySelector("#chart-opd")) {
                    const opdOptions = Object.assign({}, chartOptions, {
                        series: opdSeries.length > 0 ? opdSeries : [{ name: 'ไม่มีข้อมูล', data: new Array(categories.length).fill(0) }]
                    });
                    new ApexCharts(document.querySelector("#chart-opd"), opdOptions).render();
                }

                let ipdChartRendered = false;
                $('#ipd-tab').on('shown.bs.tab', function () {
                    if (!ipdChartRendered && document.querySelector("#chart-ipd")) {
                        const ipdOptions = Object.assign({}, chartOptions, {
                            series: ipdSeries.length > 0 ? ipdSeries : [{ name: 'ไม่มีข้อมูล', data: new Array(categories.length).fill(0) }]
                        });
                        new ApexCharts(document.querySelector("#chart-ipd"), ipdOptions).render();
                        ipdChartRendered = true;
                    }
                });
            @endif
        });
    </script>
@endpush
