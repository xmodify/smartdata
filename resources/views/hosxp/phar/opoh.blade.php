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

        .input-group-date { width: 160px !important; }
        .input-group-budget { width: 250px !important; }

        @media (max-width: 768px) {
            .page-header-container { flex-direction: column; align-items: flex-start !important; gap: 1rem; }
            .header-form-controls { width: 100%; flex-wrap: wrap; }
            .input-group-date, .input-group-budget { width: 100% !important; }
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
        .nav-tabs-custom .nav-link.active {
            color: #4f46e5;
            background: transparent;
        }
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #4f46e5;
        }

        /* Nav Pills for Sub-tabs */
        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 600;
            border-radius: 8px;
            padding: 0.4rem 1rem;
            transition: all 0.2s;
            border: 1px solid #e2e8f0;
            margin-right: 0.5rem;
            background-color: #f8fafc;
        }
        .nav-pills .nav-link.active {
            background-color: #4f46e5 !important;
            color: white !important;
            border-color: #4f46e5 !important;
        }

        .card-custom {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        /* Pivot Table Styling */
        .table-pivot {
            font-size: 0.8rem;
        }
        .table-pivot th {
            text-align: center;
            vertical-align: middle;
            border: 1px solid #dee2e6 !important;
            font-weight: bold;
            padding: 6px 4px;
        }
        .table-pivot td {
            border: 1px solid #dee2e6 !important;
            padding: 6px 4px;
        }
        
        /* Pastel Colors for Right Groups */
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

        .dataTables_wrapper .dataTables_length select {
            padding-right: 1.5rem !important;
            min-width: 60px !important;
        }
        
        /* Excel Button Styling */
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
            transition: all 0.2s ease-in-out !important;
        }
        
        .dt-buttons .btn-success:hover, .buttons-excel:hover {
            background-color: #157347 !important;
            border-color: #146c43 !important;
        }

        /* Pagination Styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .page-item.active .page-link {
            background: #4f46e5 !important;
            color: white !important;
            border-color: #4f46e5 !important;
            border-radius: 0.4rem !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current),
        .page-item:not(.active) .page-link {
            color: #4f46e5 !important;
            background: transparent !important;
            border: 1px solid transparent !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
        .page-link:hover {
            background: #f3f4f6 !important;
            color: #4f46e5 !important;
            border-radius: 0.4rem !important;
            border-color: #dee2e6 !important;
        }
        
        .page-item:first-child .page-link,
        .page-item:last-child .page-link {
            border-radius: 0.4rem !important;
        }
        
        .page-link {
            margin: 0 2px !important;
            border-radius: 0.4rem !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.85rem !important;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0rem !important;
        }

        .dataTables_wrapper .dataTables_filter label,
        .dataTables_filter label {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            margin-bottom: 0 !important;
            font-size: 0.85rem !important;
        }

        .dt-buttons {
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Month Tabs Styling */
        .nav-tabs-months {
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1.25rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .nav-tabs-months::-webkit-scrollbar {
            display: none;
        }
        .nav-tabs-months .nav-item {
            margin-bottom: -2px;
        }
        .nav-tabs-months .nav-link {
            border: none;
            color: #64748b;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            transition: all 0.3s;
            position: relative;
            background: transparent;
            font-size: 0.90rem;
        }
        .nav-tabs-months .nav-link.active {
            color: #10b981;
            font-weight: 600;
        }
        .nav-tabs-months .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: #10b981;
            border-radius: 3px 3px 0 0;
        }
        .nav-tabs-months-ipd .nav-link.active {
            color: #ef4444;
        }
        .nav-tabs-months-ipd .nav-link.active::after {
            background: #ef4444;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-filter text-primary me-2"></i> {{ $title }}</h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>{{ $row->LEAVE_YEAR_NAME }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="budget_year_changed" id="budget_year_changed" value="0">
                        <button type="submit" class="btn btn-primary text-white px-3" style="font-size: 0.8rem;"><i class="fas fa-search"></i> ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Content with Property Tabs -->
        <ul class="nav nav-tabs nav-tabs-custom" id="opohPropertyTabs" role="tablist">
            @foreach($opoh_data as $prop_id => $data)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="prop-tab-{{ $prop_id }}" data-bs-toggle="tab" data-bs-target="#prop-content-{{ $prop_id }}" type="button" role="tab">
                        <i class="fas fa-prescription-bottle-medical me-2"></i>{{ $data['name'] }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="tab-content" id="opohPropertyTabsContent">
            @foreach($opoh_data as $prop_id => $data)
                <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}" id="prop-content-{{ $prop_id }}" role="tabpanel">
                    
                    <!-- Sub-tabs for OPD and IPD -->
                    <ul class="nav nav-pills mb-3" id="sub-tabs-{{ $prop_id }}" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active btn-sm" id="opd-tab-{{ $prop_id }}" data-bs-toggle="pill" data-bs-target="#opd-content-{{ $prop_id }}" type="button" role="tab">
                                <i class="fas fa-user-nurse me-2"></i>ผู้ป่วยนอก (OPD)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn-sm" id="ipd-tab-{{ $prop_id }}" data-bs-toggle="pill" data-bs-target="#ipd-content-{{ $prop_id }}" type="button" role="tab">
                                <i class="fas fa-bed-pulse me-2"></i>ผู้ป่วยใน (IPD)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="sub-tabs-content-{{ $prop_id }}">
                        <!-- OPD Sub-tab -->
                        <div class="tab-pane fade show active" id="opd-content-{{ $prop_id }}" role="tabpanel">
                            <div class="row g-4 mb-4">
                                <!-- Line Chart -->
                                <div class="col-12">
                                    <div class="card card-custom">
                                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                                            <h6 class="fw-bold mb-0 text-green"><i class="fas fa-chart-line me-2"></i>ปริมาณการใช้{{ $data['name'] }} (QTY) รายเดือน (OPD)</h6>
                                        </div>
                                        <div class="card-body px-4 pb-4">
                                            @if(count($data['chart_series_opd']) > 0)
                                                <div id="chart-opd-{{ $prop_id }}" style="min-height: 300px;"></div>
                                            @else
                                                <div class="text-center text-muted py-5">ไม่มีข้อมูลการสั่งใช้ยาในช่วงเวลาดังกล่าว</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- Month Tabs for OPD -->
                                <div class="col-12 mb-2">
                                    <ul class="nav nav-tabs nav-tabs-months" id="opd-month-tabs-{{ $prop_id }}" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="opd-month-total-tab-{{ $prop_id }}" data-bs-toggle="tab" data-bs-target="#opd-month-total-content-{{ $prop_id }}" type="button" role="tab">
                                                <i class="fas fa-calendar-alt me-1"></i> รวมทั้งปี
                                            </button>
                                        </li>
                                        @foreach($months_list as $month)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="opd-month-{{ str_replace('-', '_', $month['key']) }}-tab-{{ $prop_id }}" data-bs-toggle="tab" data-bs-target="#opd-month-{{ str_replace('-', '_', $month['key']) }}-content-{{ $prop_id }}" type="button" role="tab">
                                                    {{ $month['label'] }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- Table Content -->
                                <div class="col-12">
                                    <div class="tab-content" id="opd-month-tabs-content-{{ $prop_id }}">
                                        <!-- Total Tab Pane -->
                                        <div class="tab-pane fade show active" id="opd-month-total-content-{{ $prop_id }}" role="tabpanel">
                                            <div class="card card-custom">
                                                <div class="card-header bg-transparent border-0 pt-4 px-4">
                                                    <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>ตารางสถิติการใช้{{ $data['name'] }} (OPD) - ยอดรวมทั้งปี</h6>
                                                </div>
                                                <div class="card-body p-4">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover table-bordered table-pivot w-100" id="table-opd-total-{{ $prop_id }}">
                                                            <thead>
                                                                <tr>
                                                                    <th rowspan="2" style="background-color: #f8fafc;">icode</th>
                                                                    <th rowspan="2" style="background-color: #f8fafc;">ชื่อยา</th>
                                                                    <th rowspan="2" style="background-color: #f8fafc;">ชื่อสามัญ</th>
                                                                    <th colspan="3" class="th-total">รวมทั้งหมด</th>
                                                                    <th colspan="3" class="th-ucs">สิทธิ บัตรทอง (UCS)</th>
                                                                    <th colspan="3" class="th-ofc">สิทธิ ข้าราชการ (OFC)</th>
                                                                    <th colspan="3" class="th-lgo">สิทธิ อปท. (LGO)</th>
                                                                    <th colspan="3" class="th-sss">สิทธิ ประกันสังคม (SSS)</th>
                                                                    <th colspan="3" class="th-other">สิทธิ อื่นๆ</th>
                                                                </tr>
                                                                <tr>
                                                                    <th class="th-total">Visit</th>
                                                                    <th class="th-total">Qty</th>
                                                                    <th class="th-total">มูลค่า (บาท)</th>
                                                                    <th class="th-ucs">Visit</th>
                                                                    <th class="th-ucs">Qty</th>
                                                                    <th class="th-ucs">มูลค่า (บาท)</th>
                                                                    <th class="th-ofc">Visit</th>
                                                                    <th class="th-ofc">Qty</th>
                                                                    <th class="th-ofc">มูลค่า (บาท)</th>
                                                                    <th class="th-lgo">Visit</th>
                                                                    <th class="th-lgo">Qty</th>
                                                                    <th class="th-lgo">มูลค่า (บาท)</th>
                                                                    <th class="th-sss">Visit</th>
                                                                    <th class="th-sss">Qty</th>
                                                                    <th class="th-sss">มูลค่า (บาท)</th>
                                                                    <th class="th-other">Visit</th>
                                                                    <th class="th-other">Qty</th>
                                                                    <th class="th-other">มูลค่า (บาท)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($data['opd'] as $row)
                                                                    <tr>
                                                                        <td class="text-center">{{ $row->icode }}</td>
                                                                        <td class="fw-bold d-flex align-items-center justify-content-between">
                                                                            <span>{{ $row->drug_name }}</span>
                                                                            <button class="btn btn-sm btn-outline-primary btn-view-patients p-1 py-0 ms-2" 
                                                                                data-icode="{{ $row->icode }}" 
                                                                                data-drugname="{{ $row->drug_name }}" 
                                                                                data-type="OPD" 
                                                                                data-monthkey="" 
                                                                                title="ดูรายชื่อผู้ใช้ยานี้">
                                                                                <i class="fas fa-eye"></i>
                                                                            </button>
                                                                        </td>
                                                                        <td>{{ $row->generic_name }}</td>
                                                                        <td class="text-center bg-pastel-green fw-bold">{{ number_format($row->total_visit) }}</td>
                                                                        <td class="text-center bg-pastel-green fw-bold">{{ number_format($row->total_qty) }}</td>
                                                                        <td class="text-end bg-pastel-green fw-bold">{{ number_format($row->total_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->ucs_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->ucs_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->ofc_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->ofc_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->lgo_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->lgo_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->sss_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->sss_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->other_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->other_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Monthly Tab Panes -->
                                        @foreach($months_list as $month)
                                            @php
                                                $month_key = $month['key'];
                                                $monthly_data = isset($data['opd_monthly'][$month_key]) ? $data['opd_monthly'][$month_key] : [];
                                                $month_id = str_replace('-', '_', $month_key);
                                            @endphp
                                            <div class="tab-pane fade" id="opd-month-{{ $month_id }}-content-{{ $prop_id }}" role="tabpanel">
                                                <div class="card card-custom">
                                                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                                                        <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>ตารางสถิติการใช้{{ $data['name'] }} (OPD) - {{ $month['label'] }}</h6>
                                                    </div>
                                                    <div class="card-body p-4">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-bordered table-pivot w-100" id="table-opd-{{ $month_id }}-{{ $prop_id }}">
                                                                <thead>
                                                                    <tr>
                                                                        <th rowspan="2" style="background-color: #f8fafc;">icode</th>
                                                                        <th rowspan="2" style="background-color: #f8fafc;">ชื่อยา</th>
                                                                        <th rowspan="2" style="background-color: #f8fafc;">ชื่อสามัญ</th>
                                                                        <th colspan="3" class="th-total">รวมทั้งหมด</th>
                                                                        <th colspan="3" class="th-ucs">สิทธิ บัตรทอง (UCS)</th>
                                                                        <th colspan="3" class="th-ofc">สิทธิ ข้าราชการ (OFC)</th>
                                                                        <th colspan="3" class="th-lgo">สิทธิ อปท. (LGO)</th>
                                                                        <th colspan="3" class="th-sss">สิทธิ ประกันสังคม (SSS)</th>
                                                                        <th colspan="3" class="th-other">สิทธิ อื่นๆ</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="th-total">Visit</th>
                                                                        <th class="th-total">Qty</th>
                                                                        <th class="th-total">มูลค่า (บาท)</th>
                                                                        <th class="th-ucs">Visit</th>
                                                                        <th class="th-ucs">Qty</th>
                                                                        <th class="th-ucs">มูลค่า (บาท)</th>
                                                                        <th class="th-ofc">Visit</th>
                                                                        <th class="th-ofc">Qty</th>
                                                                        <th class="th-ofc">มูลค่า (บาท)</th>
                                                                        <th class="th-lgo">Visit</th>
                                                                        <th class="th-lgo">Qty</th>
                                                                        <th class="th-lgo">มูลค่า (บาท)</th>
                                                                        <th class="th-sss">Visit</th>
                                                                        <th class="th-sss">Qty</th>
                                                                        <th class="th-sss">มูลค่า (บาท)</th>
                                                                        <th class="th-other">Visit</th>
                                                                        <th class="th-other">Qty</th>
                                                                        <th class="th-other">มูลค่า (บาท)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($monthly_data as $row)
                                                                        <tr>
                                                                            <td class="text-center">{{ $row->icode }}</td>
                                                                            <td class="fw-bold d-flex align-items-center justify-content-between">
                                                                                <span>{{ $row->drug_name }}</span>
                                                                                <button class="btn btn-sm btn-outline-primary btn-view-patients p-1 py-0 ms-2" 
                                                                                    data-icode="{{ $row->icode }}" 
                                                                                    data-drugname="{{ $row->drug_name }}" 
                                                                                    data-type="OPD" 
                                                                                    data-monthkey="{{ $month_key }}" 
                                                                                    title="ดูรายชื่อผู้ใช้ยานี้">
                                                                                    <i class="fas fa-eye"></i>
                                                                                </button>
                                                                            </td>
                                                                            <td>{{ $row->generic_name }}</td>
                                                                            <td class="text-center bg-pastel-green fw-bold">{{ number_format($row->total_visit) }}</td>
                                                                            <td class="text-center bg-pastel-green fw-bold">{{ number_format($row->total_qty) }}</td>
                                                                            <td class="text-end bg-pastel-green fw-bold">{{ number_format($row->total_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->ucs_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->ucs_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->ofc_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->ofc_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->lgo_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->lgo_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->sss_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->sss_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->other_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->other_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- IPD Sub-tab -->
                        <div class="tab-pane fade" id="ipd-content-{{ $prop_id }}" role="tabpanel">
                            <div class="row g-4 mb-4">
                                <!-- Line Chart -->
                                <div class="col-12">
                                    <div class="card card-custom">
                                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                                            <h6 class="fw-bold mb-0 text-red"><i class="fas fa-chart-line me-2"></i>ปริมาณการใช้{{ $data['name'] }} (QTY) รายเดือน (IPD)</h6>
                                        </div>
                                        <div class="card-body px-4 pb-4">
                                            @if(count($data['chart_series_ipd']) > 0)
                                                <div id="chart-ipd-{{ $prop_id }}" style="min-height: 300px;"></div>
                                            @else
                                                <div class="text-center text-muted py-5">ไม่มีข้อมูลการสั่งใช้ยาในช่วงเวลาดังกล่าว</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!-- Month Tabs for IPD -->
                                <div class="col-12 mb-2">
                                    <ul class="nav nav-tabs nav-tabs-months nav-tabs-months-ipd" id="ipd-month-tabs-{{ $prop_id }}" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="ipd-month-total-tab-{{ $prop_id }}" data-bs-toggle="tab" data-bs-target="#ipd-month-total-content-{{ $prop_id }}" type="button" role="tab">
                                                <i class="fas fa-calendar-alt me-1"></i> รวมทั้งปี
                                            </button>
                                        </li>
                                        @foreach($months_list as $month)
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="ipd-month-{{ str_replace('-', '_', $month['key']) }}-tab-{{ $prop_id }}" data-bs-toggle="tab" data-bs-target="#ipd-month-{{ str_replace('-', '_', $month['key']) }}-content-{{ $prop_id }}" type="button" role="tab">
                                                    {{ $month['label'] }}
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <!-- Table Content -->
                                <div class="col-12">
                                    <div class="tab-content" id="ipd-month-tabs-content-{{ $prop_id }}">
                                        <!-- Total Tab Pane -->
                                        <div class="tab-pane fade show active" id="ipd-month-total-content-{{ $prop_id }}" role="tabpanel">
                                            <div class="card card-custom">
                                                <div class="card-header bg-transparent border-0 pt-4 px-4">
                                                    <h6 class="fw-bold mb-0 text-red"><i class="fas fa-table me-2"></i>ตารางสถิติการใช้{{ $data['name'] }} (IPD) - ยอดรวมทั้งปี</h6>
                                                </div>
                                                <div class="card-body p-4">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover table-bordered table-pivot w-100" id="table-ipd-total-{{ $prop_id }}">
                                                            <thead>
                                                                <tr>
                                                                    <th rowspan="2" style="background-color: #f8fafc;">icode</th>
                                                                    <th rowspan="2" style="background-color: #f8fafc;">ชื่อยา</th>
                                                                    <th rowspan="2" style="background-color: #f8fafc;">ชื่อสามัญ</th>
                                                                    <th colspan="3" class="th-total">รวมทั้งหมด</th>
                                                                    <th colspan="3" class="th-ucs">สิทธิ บัตรทอง (UCS)</th>
                                                                    <th colspan="3" class="th-ofc">สิทธิ ข้าราชการ (OFC)</th>
                                                                    <th colspan="3" class="th-lgo">สิทธิ อปท. (LGO)</th>
                                                                    <th colspan="3" class="th-sss">สิทธิ ประกันสังคม (SSS)</th>
                                                                    <th colspan="3" class="th-other">สิทธิ อื่นๆ</th>
                                                                </tr>
                                                                <tr>
                                                                    <th class="th-total">AN</th>
                                                                    <th class="th-total">Qty</th>
                                                                    <th class="th-total">มูลค่า (บาท)</th>
                                                                    <th class="th-ucs">AN</th>
                                                                    <th class="th-ucs">Qty</th>
                                                                    <th class="th-ucs">มูลค่า (บาท)</th>
                                                                    <th class="th-ofc">AN</th>
                                                                    <th class="th-ofc">Qty</th>
                                                                    <th class="th-ofc">มูลค่า (บาท)</th>
                                                                    <th class="th-lgo">AN</th>
                                                                    <th class="th-lgo">Qty</th>
                                                                    <th class="th-lgo">มูลค่า (บาท)</th>
                                                                    <th class="th-sss">AN</th>
                                                                    <th class="th-sss">Qty</th>
                                                                    <th class="th-sss">มูลค่า (บาท)</th>
                                                                    <th class="th-other">AN</th>
                                                                    <th class="th-other">Qty</th>
                                                                    <th class="th-other">มูลค่า (บาท)</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($data['ipd'] as $row)
                                                                    <tr>
                                                                        <td class="text-center">{{ $row->icode }}</td>
                                                                        <td class="fw-bold d-flex align-items-center justify-content-between">
                                                                            <span>{{ $row->drug_name }}</span>
                                                                            <button class="btn btn-sm btn-outline-danger btn-view-patients p-1 py-0 ms-2" 
                                                                                data-icode="{{ $row->icode }}" 
                                                                                data-drugname="{{ $row->drug_name }}" 
                                                                                data-type="IPD" 
                                                                                data-monthkey="" 
                                                                                title="ดูรายชื่อผู้ใช้ยานี้">
                                                                                <i class="fas fa-eye"></i>
                                                                            </button>
                                                                        </td>
                                                                        <td>{{ $row->generic_name }}</td>
                                                                        <td class="text-center bg-pastel-red fw-bold">{{ number_format($row->total_visit) }}</td>
                                                                        <td class="text-center bg-pastel-red fw-bold">{{ number_format($row->total_qty) }}</td>
                                                                        <td class="text-end bg-pastel-red fw-bold">{{ number_format($row->total_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->ucs_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->ucs_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->ofc_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->ofc_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->lgo_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->lgo_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->sss_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->sss_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>
                                                                        
                                                                        <td class="text-center">{{ number_format($row->other_visit) }}</td>
                                                                        <td class="text-center">{{ number_format($row->other_qty) }}</td>
                                                                        <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Monthly Tab Panes -->
                                        @foreach($months_list as $month)
                                            @php
                                                $month_key = $month['key'];
                                                $monthly_data = isset($data['ipd_monthly'][$month_key]) ? $data['ipd_monthly'][$month_key] : [];
                                                $month_id = str_replace('-', '_', $month_key);
                                            @endphp
                                            <div class="tab-pane fade" id="ipd-month-{{ $month_id }}-content-{{ $prop_id }}" role="tabpanel">
                                                <div class="card card-custom">
                                                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                                                        <h6 class="fw-bold mb-0 text-red"><i class="fas fa-table me-2"></i>ตารางสถิติการใช้{{ $data['name'] }} (IPD) - {{ $month['label'] }}</h6>
                                                    </div>
                                                    <div class="card-body p-4">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover table-bordered table-pivot w-100" id="table-ipd-{{ $month_id }}-{{ $prop_id }}">
                                                                <thead>
                                                                    <tr>
                                                                        <th rowspan="2" style="background-color: #f8fafc;">icode</th>
                                                                        <th rowspan="2" style="background-color: #f8fafc;">ชื่อยา</th>
                                                                        <th rowspan="2" style="background-color: #f8fafc;">ชื่อสามัญ</th>
                                                                        <th colspan="3" class="th-total">รวมทั้งหมด</th>
                                                                        <th colspan="3" class="th-ucs">สิทธิ บัตรทอง (UCS)</th>
                                                                        <th colspan="3" class="th-ofc">สิทธิ ข้าราชการ (OFC)</th>
                                                                        <th colspan="3" class="th-lgo">สิทธิ อปท. (LGO)</th>
                                                                        <th colspan="3" class="th-sss">สิทธิ ประกันสังคม (SSS)</th>
                                                                        <th colspan="3" class="th-other">สิทธิ อื่นๆ</th>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="th-total">AN</th>
                                                                        <th class="th-total">Qty</th>
                                                                        <th class="th-total">มูลค่า (บาท)</th>
                                                                        <th class="th-ucs">AN</th>
                                                                        <th class="th-ucs">Qty</th>
                                                                        <th class="th-ucs">มูลค่า (บาท)</th>
                                                                        <th class="th-ofc">AN</th>
                                                                        <th class="th-ofc">Qty</th>
                                                                        <th class="th-ofc">มูลค่า (บาท)</th>
                                                                        <th class="th-lgo">AN</th>
                                                                        <th class="th-lgo">Qty</th>
                                                                        <th class="th-lgo">มูลค่า (บาท)</th>
                                                                        <th class="th-sss">AN</th>
                                                                        <th class="th-sss">Qty</th>
                                                                        <th class="th-sss">มูลค่า (บาท)</th>
                                                                        <th class="th-other">AN</th>
                                                                        <th class="th-other">Qty</th>
                                                                        <th class="th-other">มูลค่า (บาท)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($monthly_data as $row)
                                                                        <tr>
                                                                            <td class="text-center">{{ $row->icode }}</td>
                                                                            <td class="fw-bold d-flex align-items-center justify-content-between">
                                                                                <span>{{ $row->drug_name }}</span>
                                                                                <button class="btn btn-sm btn-outline-danger btn-view-patients p-1 py-0 ms-2" 
                                                                                    data-icode="{{ $row->icode }}" 
                                                                                    data-drugname="{{ $row->drug_name }}" 
                                                                                    data-type="IPD" 
                                                                                    data-monthkey="{{ $month_key }}" 
                                                                                    title="ดูรายชื่อผู้ใช้ยานี้">
                                                                                    <i class="fas fa-eye"></i>
                                                                                </button>
                                                                            </td>
                                                                            <td>{{ $row->generic_name }}</td>
                                                                            <td class="text-center bg-pastel-red fw-bold">{{ number_format($row->total_visit) }}</td>
                                                                            <td class="text-center bg-pastel-red fw-bold">{{ number_format($row->total_qty) }}</td>
                                                                            <td class="text-end bg-pastel-red fw-bold">{{ number_format($row->total_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->ucs_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->ucs_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->ofc_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->ofc_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->lgo_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->lgo_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->sss_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->sss_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>
                                                                            
                                                                            <td class="text-center">{{ number_format($row->other_visit) }}</td>
                                                                            <td class="text-center">{{ number_format($row->other_qty) }}</td>
                                                                            <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    <!-- Patient Details Modal -->
    <div class="modal fade" id="patientDetailsModal" tabindex="-1" aria-labelledby="patientDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="patientDetailsModalLabel">
                        <i class="fas fa-prescription-bottle-medical text-primary me-2"></i>รายชื่อผู้ได้รับยา
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="patientDetailsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">กำลังดึงข้อมูลรายชื่อผู้ใช้ยา...</p>
                    </div>

                    <div id="patientDetailsContent" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-bordered align-middle w-100" id="table-patient-details" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">ลำดับที่</th>
                                        <th class="text-center" style="width: 100px;">HN</th>
                                        <th class="text-center" style="width: 140px;">CID</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th class="text-center" style="width: 110px;">วันที่ได้รับ</th>
                                        <th class="text-center" style="width: 90px;">เวลา</th>
                                        <th class="text-center" style="width: 80px;">จำนวน</th>
                                        <th>รพ.สต. (หน่วยบริการปฐมภูมิ)</th>
                                    </tr>
                                </thead>
                                <tbody id="patientDetailsBody">
                                    <!-- Rendered dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-secondary btn-sm" style="border-radius: 8px;" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
    <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // DataTables Common Configuration
            const dataTableConfig = {
                pageLength: 10,
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    paginate: {
                        previous: "ก่อนหน้า",
                        next: "ถัดไป"
                    }
                }
            };

            // Header prefix mapping helper for Excel Export
            const getExportHeaderFormatter = function() {
                return function(data, columnIdx) {
                    const prefixes = [
                        "", "", "", // icode, ชื่อยา, ชื่อสามัญ
                        "รวมทั้งหมด - ", "รวมทั้งหมด - ", "รวมทั้งหมด - ", // Visit, Qty, มูลค่า
                        "สิทธิ บัตรทอง (UCS) - ", "สิทธิ บัตรทอง (UCS) - ", "สิทธิ บัตรทอง (UCS) - ",
                        "สิทธิ ข้าราชการ (OFC) - ", "สิทธิ ข้าราชการ (OFC) - ", "สิทธิ ข้าราชการ (OFC) - ",
                        "สิทธิ อปท. (LGO) - ", "สิทธิ อปท. (LGO) - ", "สิทธิ อปท. (LGO) - ",
                        "สิทธิ ประกันสังคม (SSS) - ", "สิทธิ ประกันสังคม (SSS) - ", "สิทธิ ประกันสังคม (SSS) - ",
                        "สิทธิ อื่นๆ - ", "สิทธิ อื่นๆ - ", "สิทธิ อื่นๆ - "
                    ];
                    return (prefixes[columnIdx] || "") + data;
                };
            };

            // Setup DataTables and Charts for each property dynamically
            @foreach($opoh_data as $prop_id => $data)
                // OPD Total Table
                $('#table-opd-total-{{ $prop_id }}').DataTable({
                    ...dataTableConfig,
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานสรุปการใช้{{ $data['name'] }} (OPD) - ยอดรวมทั้งปี',
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                header: getExportHeaderFormatter()
                            }
                        }
                    }]
                });

                // OPD Monthly Tables
                @foreach($months_list as $month)
                    @php
                        $month_id = str_replace('-', '_', $month['key']);
                    @endphp
                    $('#table-opd-{{ $month_id }}-{{ $prop_id }}').DataTable({
                        ...dataTableConfig,
                        buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success btn-sm',
                            title: 'รายงานสรุปการใช้{{ $data['name'] }} (OPD) - {{ $month['label'] }}',
                            exportOptions: {
                                columns: ':visible',
                                format: {
                                    header: getExportHeaderFormatter()
                                }
                            }
                        }]
                    });
                @endforeach

                // IPD Total Table
                $('#table-ipd-total-{{ $prop_id }}').DataTable({
                    ...dataTableConfig,
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานสรุปการใช้{{ $data['name'] }} (IPD) - ยอดรวมทั้งปี',
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                header: getExportHeaderFormatter()
                            }
                        }
                    }]
                });

                // IPD Monthly Tables
                @foreach($months_list as $month)
                    @php
                        $month_id = str_replace('-', '_', $month['key']);
                    @endphp
                    $('#table-ipd-{{ $month_id }}-{{ $prop_id }}').DataTable({
                        ...dataTableConfig,
                        buttons: [{
                            extend: 'excelHtml5',
                            text: '<i class="fas fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success btn-sm',
                            title: 'รายงานสรุปการใช้{{ $data['name'] }} (IPD) - {{ $month['label'] }}',
                            exportOptions: {
                                columns: ':visible',
                                format: {
                                    header: getExportHeaderFormatter()
                                }
                            }
                        }]
                    });
                @endforeach

                // OPD Chart
                @if(count($data['chart_series_opd']) > 0)
                var opdChart_{{ $prop_id }} = new ApexCharts(document.querySelector("#chart-opd-{{ $prop_id }}"), {
                    series: @json($data['chart_series_opd']),
                    chart: { type: 'line', height: 300, toolbar: { show: true } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 5, strokeWidth: 2, hover: { size: 7 } },
                    xaxis: { categories: @json($month_categories) },
                    tooltip: { y: { formatter: function(val) { return val.toLocaleString(); } } }
                });
                opdChart_{{ $prop_id }}.render();
                @endif

                // IPD Chart
                @if(count($data['chart_series_ipd']) > 0)
                var ipdChart_{{ $prop_id }} = new ApexCharts(document.querySelector("#chart-ipd-{{ $prop_id }}"), {
                    series: @json($data['chart_series_ipd']),
                    chart: { type: 'line', height: 300, toolbar: { show: true } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 5, strokeWidth: 2, hover: { size: 7 } },
                    xaxis: { categories: @json($month_categories) },
                    tooltip: { y: { formatter: function(val) { return val.toLocaleString(); } } }
                });
                ipdChart_{{ $prop_id }}.render();
                @endif
            @endforeach

            // Flatpickr setup
            if (typeof flatpickr !== 'undefined') {
                const yearOffset = 543;
                const commonConfig = {
                    locale: "th",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "j M Y",
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
                            const originalValue = instance.altInput.value;
                            if (originalValue) {
                                const date = instance.selectedDates[0] || new Date(instance.input.value);
                                if (date && !isNaN(date.getTime())) {
                                    const day = date.getDate();
                                    const month = instance.l10n.months.shorthand[date.getMonth()];
                                    const year = date.getFullYear() + yearOffset;
                                    instance.altInput.value = `${day} ${month} ${year}`;
                                }
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
                const startPicker = flatpickr("#start_date", commonConfig);
                const endPicker = flatpickr("#end_date", commonConfig);

                $('select[name="budget_year"]').on('change', function() {
                    const selectedYear = parseInt($(this).val());
                    if (!isNaN(selectedYear)) {
                        const startYear = selectedYear - 544;
                        const endYear = selectedYear - 543;
                        const startDateStr = startYear + "-10-01";
                        const endDateStr = endYear + "-09-30";

                        startPicker.setDate(startDateStr, true);
                        endPicker.setDate(endDateStr, true);
                        $('#budget_year_changed').val('1');
                    }
                });
            }

            // View patients modal handler
            $(document).on('click', '.btn-view-patients', function() {
                const icode = $(this).data('icode');
                const drugName = $(this).data('drugname');
                const type = $(this).data('type');
                const monthKey = $(this).data('monthkey');
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                // Set modal title
                $('#patientDetailsModalLabel').html(`<i class="fas fa-prescription-bottle-medical text-primary me-2"></i>รายชื่อผู้รับยา: <span class="text-primary">${drugName}</span> (${type})`);

                // Reset/Rewrite table headers dynamically
                let headerHtml = '';
                if (type === 'IPD') {
                    headerHtml = `
                        <tr>
                            <th class="text-center" style="width: 60px;">ลำดับที่</th>
                            <th class="text-center" style="width: 100px;">HN</th>
                            <th class="text-center" style="width: 100px;">AN</th>
                            <th class="text-center" style="width: 140px;">CID</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th class="text-center" style="width: 110px;">วันที่ Admit</th>
                            <th class="text-center" style="width: 110px;">วันที่ DC</th>
                            <th class="text-center" style="width: 110px;">วันที่ได้รับยา</th>
                            <th class="text-center" style="width: 90px;">เวลา</th>
                            <th class="text-center" style="width: 80px;">จำนวน</th>
                            <th>รพ.สต. (หน่วยบริการปฐมภูมิ)</th>
                        </tr>
                    `;
                } else {
                    headerHtml = `
                        <tr>
                            <th class="text-center" style="width: 60px;">ลำดับที่</th>
                            <th class="text-center" style="width: 100px;">HN</th>
                            <th class="text-center" style="width: 100px;">VN</th>
                            <th class="text-center" style="width: 140px;">CID</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th class="text-center" style="width: 110px;">วันที่ได้รับยา</th>
                            <th class="text-center" style="width: 90px;">เวลา</th>
                            <th class="text-center" style="width: 80px;">จำนวน</th>
                            <th>รพ.สต. (หน่วยบริการปฐมภูมิ)</th>
                        </tr>
                    `;
                }
                $('#table-patient-details thead').html(headerHtml);

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('patientDetailsModal'));
                modal.show();

                // Toggle loading state
                $('#patientDetailsLoading').removeClass('d-none');
                $('#patientDetailsContent').addClass('d-none');
                $('#patientDetailsBody').html('');

                // Destroy DataTable if it already exists
                if ($.fn.DataTable.isDataTable('#table-patient-details')) {
                    $('#table-patient-details').DataTable().destroy();
                }

                // AJAX fetch
                $.ajax({
                    url: '{{ route("hosxp.phar.opoh.patients") }}',
                    method: 'GET',
                    data: {
                        icode: icode,
                        type: type,
                        start_date: startDate,
                        end_date: endDate,
                        month_key: monthKey
                    },
                    success: function(response) {
                        let html = '';
                        if (response.patients && response.patients.length > 0) {
                            response.patients.forEach((patient, index) => {
                                const formatThaiDate = function(dateStr) {
                                    if (!dateStr) return '';
                                    const d = new Date(dateStr);
                                    if (isNaN(d.getTime())) return dateStr;
                                    const monthsTh = ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
                                    return `${d.getDate()} ${monthsTh[d.getMonth()]} ${d.getFullYear() + 543}`;
                                };

                                const displayDate = formatThaiDate(patient.rx_date);
                                const admitDate = formatThaiDate(patient.admit_date);
                                const dcDate = formatThaiDate(patient.dc_date);

                                if (type === 'IPD') {
                                    html += `
                                        <tr>
                                            <td class="text-center font-monospace">${index + 1}</td>
                                            <td class="text-center font-monospace fw-bold">${patient.hn || ''}</td>
                                            <td class="text-center font-monospace text-primary fw-bold">${patient.an || ''}</td>
                                            <td class="text-center font-monospace">${patient.cid || ''}</td>
                                            <td>${patient.patient_name || ''}</td>
                                            <td class="text-center">${admitDate || '<span class="text-muted small">-</span>'}</td>
                                            <td class="text-center">${dcDate || '<span class="text-muted small">ยังไม่จำหน่าย</span>'}</td>
                                            <td class="text-center">${displayDate || ''}</td>
                                            <td class="text-center font-monospace">${patient.rx_time || ''}</td>
                                            <td class="text-center font-monospace fw-bold text-success">${Number(patient.qty).toLocaleString()}</td>
                                            <td>${patient.hosp_name || '<span class="text-muted small">นอกเขตอำเภอหัวตะพาน</span>'}</td>
                                        </tr>
                                    `;
                                } else {
                                    html += `
                                        <tr>
                                            <td class="text-center font-monospace">${index + 1}</td>
                                            <td class="text-center font-monospace fw-bold">${patient.hn || ''}</td>
                                            <td class="text-center font-monospace text-secondary fw-bold">${patient.vn || ''}</td>
                                            <td class="text-center font-monospace">${patient.cid || ''}</td>
                                            <td>${patient.patient_name || ''}</td>
                                            <td class="text-center">${displayDate || ''}</td>
                                            <td class="text-center font-monospace">${patient.rx_time || ''}</td>
                                            <td class="text-center font-monospace fw-bold text-success">${Number(patient.qty).toLocaleString()}</td>
                                            <td>${patient.hosp_name || '<span class="text-muted small">นอกเขตอำเภอหัวตะพาน</span>'}</td>
                                        </tr>
                                    `;
                                }
                            });
                        } else {
                            const colCount = (type === 'IPD') ? 11 : 9;
                            html = `<tr><td colspan="${colCount}" class="text-center text-muted py-4">ไม่พบรายชื่อคนไข้ได้รับยาตัวนี้ในช่วงเวลาดังกล่าว</td></tr>`;
                        }
                        
                        $('#patientDetailsBody').html(html);
                        $('#patientDetailsLoading').addClass('d-none');
                        $('#patientDetailsContent').removeClass('d-none');

                        // Initialize DataTable for details
                        if (response.patients && response.patients.length > 0) {
                            $('#table-patient-details').DataTable({
                                pageLength: 10,
                                lengthMenu: [10, 25, 50, 100, 500],
                                dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                                buttons: [{
                                    extend: 'excelHtml5',
                                    text: '<i class="fas fa-file-excel me-1"></i> Excel',
                                    className: 'btn btn-success btn-sm',
                                    title: 'รายชื่อผู้ใช้ยา - ' + drugName + ' (' + type + ')',
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
                        const colCount = (type === 'IPD') ? 11 : 9;
                        $('#patientDetailsBody').html(`<tr><td colspan="${colCount}" class="text-center text-danger py-4">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>`);
                        $('#patientDetailsLoading').addClass('d-none');
                        $('#patientDetailsContent').removeClass('d-none');
                    }
                });
            });

            // Fix chart and table render sizes on tab switch
            $('button[data-bs-toggle="tab"], button[data-bs-toggle="pill"]').on('shown.bs.tab shown.bs.pill', function (e) {
                window.dispatchEvent(new Event('resize'));
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });
        });
    </script>
@endpush
