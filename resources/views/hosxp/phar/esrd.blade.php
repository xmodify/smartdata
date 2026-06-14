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
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-filter text-primary me-2"></i> {{ $title }}
                    </h5>
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

        <!-- Main Content with Tabs -->
        <ul class="nav nav-tabs nav-tabs-custom" id="esrdReportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="opd-tab" data-bs-toggle="tab" data-bs-target="#opd-content" type="button" role="tab"><i class="fas fa-user-nurse me-2"></i>ผู้ป่วยนอก (OPD)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ipd-tab" data-bs-toggle="tab" data-bs-target="#ipd-content" type="button" role="tab"><i class="fas fa-bed-pulse me-2"></i>ผู้ป่วยใน (IPD)</button>
            </li>
        </ul>

        <div class="tab-content" id="esrdReportTabsContent">
            <!-- OPD Tab -->
            <div class="tab-pane fade show active" id="opd-content" role="tabpanel">
                <div class="row g-4 mb-4">
                    <!-- Line Chart -->
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-chart-line me-2"></i>ปริมาณการใช้ยา ESRD (QTY) รายเดือน แยกตามตัวยา (OPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                @if(count($chart_series_opd) > 0)
                                    <div id="esrdOpdChart" style="min-height: 300px;"></div>
                                @else
                                    <div class="text-center text-muted py-5">ไม่มีข้อมูลการสั่งใช้ยาในช่วงเวลาดังกล่าว</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-green"><i class="fas fa-table me-2"></i>ตารางสถิติข้อมูลการใช้ยา ESRD (OPD)</h6>
                                <div id="opdExportBtn"></div>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-pivot" id="opdTable">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="width: 80px;">รหัสยา</th>
                                                <th rowspan="2" style="min-width: 150px;">ชื่อยา</th>
                                                <th rowspan="2" style="min-width: 120px;">ชื่อสามัญ</th>
                                                <th colspan="4" class="th-total">TOTAL</th>
                                                <th colspan="4" class="th-ucs">UCS</th>
                                                <th colspan="4" class="th-ofc">OFC</th>
                                                <th colspan="4" class="th-lgo">LGO</th>
                                                <th colspan="4" class="th-sss">SSS</th>
                                                <th colspan="4" class="th-other">Other</th>
                                            </tr>
                                            <tr>
                                                <!-- TOTAL -->
                                                <th class="th-total">VISIT</th><th class="th-total">QTY</th><th class="th-total">COST</th><th class="th-total">PRICE</th>
                                                <!-- UCS -->
                                                <th class="th-ucs">VISIT</th><th class="th-ucs">QTY</th><th class="th-ucs">COST</th><th class="th-ucs">PRICE</th>
                                                <!-- OFC -->
                                                <th class="th-ofc">VISIT</th><th class="th-ofc">QTY</th><th class="th-ofc">COST</th><th class="th-ofc">PRICE</th>
                                                <!-- LGO -->
                                                <th class="th-lgo">VISIT</th><th class="th-lgo">QTY</th><th class="th-lgo">COST</th><th class="th-lgo">PRICE</th>
                                                <!-- SSS -->
                                                <th class="th-sss">VISIT</th><th class="th-sss">QTY</th><th class="th-sss">COST</th><th class="th-sss">PRICE</th>
                                                <!-- Other -->
                                                <th class="th-other">VISIT</th><th class="th-other">QTY</th><th class="th-other">COST</th><th class="th-other">PRICE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $t_v = 0; $t_q = 0; $t_c = 0; $t_p = 0;
                                                $u_v = 0; $u_q = 0; $u_c = 0; $u_p = 0;
                                                $o_v = 0; $o_q = 0; $o_c = 0; $o_p = 0;
                                                $l_v = 0; $l_q = 0; $l_c = 0; $l_p = 0;
                                                $s_v = 0; $s_q = 0; $s_c = 0; $s_p = 0;
                                                $ot_v = 0; $ot_q = 0; $ot_c = 0; $ot_p = 0;
                                            @endphp
                                            @foreach ($esrd_opd as $row)
                                                <tr>
                                                    <td class="text-center font-monospace">{{ $row->icode }}</td>
                                                    <td class="fw-bold text-dark">{{ $row->drug_name }}</td>
                                                    <td>{{ $row->generic_name ?? '-' }}</td>
                                                    
                                                    <!-- TOTAL -->
                                                    <td class="text-center">{{ number_format($row->total_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->total_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->total_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark">{{ number_format($row->total_price, 2) }}</td>
                                                    
                                                    <!-- UCS -->
                                                    <td class="text-center">{{ number_format($row->ucs_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->ucs_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->ucs_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>

                                                    <!-- OFC -->
                                                    <td class="text-center">{{ number_format($row->ofc_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->ofc_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->ofc_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>

                                                    <!-- LGO -->
                                                    <td class="text-center">{{ number_format($row->lgo_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->lgo_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->lgo_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>

                                                    <!-- SSS -->
                                                    <td class="text-center">{{ number_format($row->sss_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->sss_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->sss_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>

                                                    <!-- Other -->
                                                    <td class="text-center">{{ number_format($row->other_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->other_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->other_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                </tr>
                                                @php
                                                    $t_v += $row->total_visit; $t_q += $row->total_qty; $t_c += $row->total_cost; $t_p += $row->total_price;
                                                    $u_v += $row->ucs_visit; $u_q += $row->ucs_qty; $u_c += $row->ucs_cost; $u_p += $row->ucs_price;
                                                    $o_v += $row->ofc_visit; $o_q += $row->ofc_qty; $o_c += $row->ofc_cost; $o_p += $row->ofc_price;
                                                    $l_v += $row->lgo_visit; $l_q += $row->lgo_qty; $l_c += $row->lgo_cost; $l_p += $row->lgo_price;
                                                    $s_v += $row->sss_visit; $s_q += $row->sss_qty; $s_c += $row->sss_cost; $s_p += $row->sss_price;
                                                    $ot_v += $row->other_visit; $ot_q += $row->other_qty; $ot_c += $row->other_cost; $ot_p += $row->other_price;
                                                @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold bg-light">
                                                <td colspan="3" class="text-center">รวมทั้งหมด</td>
                                                <!-- TOTAL -->
                                                <td class="text-center">{{ number_format($t_v) }}</td>
                                                <td class="text-center">{{ number_format($t_q) }}</td>
                                                <td class="text-end">{{ number_format($t_c, 2) }}</td>
                                                <td class="text-end">{{ number_format($t_p, 2) }}</td>
                                                <!-- UCS -->
                                                <td class="text-center">{{ number_format($u_v) }}</td>
                                                <td class="text-center">{{ number_format($u_q) }}</td>
                                                <td class="text-end">{{ number_format($u_c, 2) }}</td>
                                                <td class="text-end">{{ number_format($u_p, 2) }}</td>
                                                <!-- OFC -->
                                                <td class="text-center">{{ number_format($o_v) }}</td>
                                                <td class="text-center">{{ number_format($o_q) }}</td>
                                                <td class="text-end">{{ number_format($o_c, 2) }}</td>
                                                <td class="text-end">{{ number_format($o_p, 2) }}</td>
                                                <!-- LGO -->
                                                <td class="text-center">{{ number_format($l_v) }}</td>
                                                <td class="text-center">{{ number_format($l_q) }}</td>
                                                <td class="text-end">{{ number_format($l_c, 2) }}</td>
                                                <td class="text-end">{{ number_format($l_p, 2) }}</td>
                                                <!-- SSS -->
                                                <td class="text-center">{{ number_format($s_v) }}</td>
                                                <td class="text-center">{{ number_format($s_q) }}</td>
                                                <td class="text-end">{{ number_format($s_c, 2) }}</td>
                                                <td class="text-end">{{ number_format($s_p, 2) }}</td>
                                                <!-- Other -->
                                                <td class="text-center">{{ number_format($ot_v) }}</td>
                                                <td class="text-center">{{ number_format($ot_q) }}</td>
                                                <td class="text-end">{{ number_format($ot_c, 2) }}</td>
                                                <td class="text-end">{{ number_format($ot_p, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IPD Tab -->
            <div class="tab-pane fade" id="ipd-content" role="tabpanel">
                <div class="row g-4 mb-4">
                    <!-- Line Chart -->
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-chart-line me-2"></i>ปริมาณการใช้ยา ESRD (QTY) รายเดือน แยกตามตัวยา (IPD)</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                @if(count($chart_series_ipd) > 0)
                                    <div id="esrdIpdChart" style="min-height: 300px;"></div>
                                @else
                                    <div class="text-center text-muted py-5">ไม่มีข้อมูลการสั่งใช้ยาในช่วงเวลาดังกล่าว</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-red"><i class="fas fa-table me-2"></i>ตารางสถิติข้อมูลการใช้ยา ESRD (IPD)</h6>
                                <div id="ipdExportBtn"></div>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle table-pivot" id="ipdTable">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" style="width: 80px;">รหัสยา</th>
                                                <th rowspan="2" style="min-width: 150px;">ชื่อยา</th>
                                                <th rowspan="2" style="min-width: 120px;">ชื่อสามัญ</th>
                                                <th colspan="4" class="th-total">TOTAL</th>
                                                <th colspan="4" class="th-ucs">UCS</th>
                                                <th colspan="4" class="th-ofc">OFC</th>
                                                <th colspan="4" class="th-lgo">LGO</th>
                                                <th colspan="4" class="th-sss">SSS</th>
                                                <th colspan="4" class="th-other">Other</th>
                                            </tr>
                                            <tr>
                                                <!-- TOTAL -->
                                                <th class="th-total">VISIT</th><th class="th-total">QTY</th><th class="th-total">COST</th><th class="th-total">PRICE</th>
                                                <!-- UCS -->
                                                <th class="th-ucs">VISIT</th><th class="th-ucs">QTY</th><th class="th-ucs">COST</th><th class="th-ucs">PRICE</th>
                                                <!-- OFC -->
                                                <th class="th-ofc">VISIT</th><th class="th-ofc">QTY</th><th class="th-ofc">COST</th><th class="th-ofc">PRICE</th>
                                                <!-- LGO -->
                                                <th class="th-lgo">VISIT</th><th class="th-lgo">QTY</th><th class="th-lgo">COST</th><th class="th-lgo">PRICE</th>
                                                <!-- SSS -->
                                                <th class="th-sss">VISIT</th><th class="th-sss">QTY</th><th class="th-sss">COST</th><th class="th-sss">PRICE</th>
                                                <!-- Other -->
                                                <th class="th-other">VISIT</th><th class="th-other">QTY</th><th class="th-other">COST</th><th class="th-other">PRICE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $t_v_ipd = 0; $t_q_ipd = 0; $t_c_ipd = 0; $t_p_ipd = 0;
                                                $u_v_ipd = 0; $u_q_ipd = 0; $u_c_ipd = 0; $u_p_ipd = 0;
                                                $o_v_ipd = 0; $o_q_ipd = 0; $o_c_ipd = 0; $o_p_ipd = 0;
                                                $l_v_ipd = 0; $l_q_ipd = 0; $l_c_ipd = 0; $l_p_ipd = 0;
                                                $s_v_ipd = 0; $s_q_ipd = 0; $s_c_ipd = 0; $s_p_ipd = 0;
                                                $ot_v_ipd = 0; $ot_q_ipd = 0; $ot_c_ipd = 0; $ot_p_ipd = 0;
                                            @endphp
                                            @foreach ($esrd_ipd as $row)
                                                <tr>
                                                    <td class="text-center font-monospace">{{ $row->icode }}</td>
                                                    <td class="fw-bold text-dark">{{ $row->drug_name }}</td>
                                                    <td>{{ $row->generic_name ?? '-' }}</td>
                                                    
                                                    <!-- TOTAL -->
                                                    <td class="text-center">{{ number_format($row->total_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->total_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->total_cost, 2) }}</td>
                                                    <td class="text-end fw-bold text-dark">{{ number_format($row->total_price, 2) }}</td>
                                                    
                                                    <!-- UCS -->
                                                    <td class="text-center">{{ number_format($row->ucs_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->ucs_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->ucs_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->ucs_price, 2) }}</td>

                                                    <!-- OFC -->
                                                    <td class="text-center">{{ number_format($row->ofc_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->ofc_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->ofc_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->ofc_price, 2) }}</td>

                                                    <!-- LGO -->
                                                    <td class="text-center">{{ number_format($row->lgo_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->lgo_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->lgo_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->lgo_price, 2) }}</td>

                                                    <!-- SSS -->
                                                    <td class="text-center">{{ number_format($row->sss_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->sss_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->sss_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->sss_price, 2) }}</td>

                                                    <!-- Other -->
                                                    <td class="text-center">{{ number_format($row->other_visit) }}</td>
                                                    <td class="text-center">{{ number_format($row->other_qty) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($row->other_cost, 2) }}</td>
                                                    <td class="text-end">{{ number_format($row->other_price, 2) }}</td>
                                                </tr>
                                                @php
                                                    $t_v_ipd += $row->total_visit; $t_q_ipd += $row->total_qty; $t_c_ipd += $row->total_cost; $t_p_ipd += $row->total_price;
                                                    $u_v_ipd += $row->ucs_visit; $u_q_ipd += $row->ucs_qty; $u_c_ipd += $row->ucs_cost; $u_p_ipd += $row->ucs_price;
                                                    $o_v_ipd += $row->ofc_visit; $o_q_ipd += $row->ofc_qty; $o_c_ipd += $row->ofc_cost; $o_p_ipd += $row->ofc_price;
                                                    $l_v_ipd += $row->lgo_visit; $l_q_ipd += $row->lgo_qty; $l_c_ipd += $row->lgo_cost; $l_p_ipd += $row->lgo_price;
                                                    $s_v_ipd += $row->sss_visit; $s_q_ipd += $row->sss_qty; $s_c_ipd += $row->sss_cost; $s_p_ipd += $row->sss_price;
                                                    $ot_v_ipd += $row->other_visit; $ot_q_ipd += $row->other_qty; $ot_c_ipd += $row->other_cost; $ot_p_ipd += $row->other_price;
                                                @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold bg-light">
                                                <td colspan="3" class="text-center">รวมทั้งหมด</td>
                                                <!-- TOTAL -->
                                                <td class="text-center">{{ number_format($t_v_ipd) }}</td>
                                                <td class="text-center">{{ number_format($t_q_ipd) }}</td>
                                                <td class="text-end">{{ number_format($t_c_ipd, 2) }}</td>
                                                <td class="text-end">{{ number_format($t_p_ipd, 2) }}</td>
                                                <!-- UCS -->
                                                <td class="text-center">{{ number_format($u_v_ipd) }}</td>
                                                <td class="text-center">{{ number_format($u_q_ipd) }}</td>
                                                <td class="text-end">{{ number_format($u_c_ipd, 2) }}</td>
                                                <td class="text-end">{{ number_format($u_p_ipd, 2) }}</td>
                                                <!-- OFC -->
                                                <td class="text-center">{{ number_format($o_v_ipd) }}</td>
                                                <td class="text-center">{{ number_format($o_q_ipd) }}</td>
                                                <td class="text-end">{{ number_format($o_c_ipd, 2) }}</td>
                                                <td class="text-end">{{ number_format($o_p_ipd, 2) }}</td>
                                                <!-- LGO -->
                                                <td class="text-center">{{ number_format($l_v_ipd) }}</td>
                                                <td class="text-center">{{ number_format($l_q_ipd) }}</td>
                                                <td class="text-end">{{ number_format($l_c_ipd, 2) }}</td>
                                                <td class="text-end">{{ number_format($l_p_ipd, 2) }}</td>
                                                <!-- SSS -->
                                                <td class="text-center">{{ number_format($s_v_ipd) }}</td>
                                                <td class="text-center">{{ number_format($s_q_ipd) }}</td>
                                                <td class="text-end">{{ number_format($s_c_ipd, 2) }}</td>
                                                <td class="text-end">{{ number_format($s_p_ipd, 2) }}</td>
                                                <!-- Other -->
                                                <td class="text-center">{{ number_format($ot_v_ipd) }}</td>
                                                <td class="text-center">{{ number_format($ot_q_ipd) }}</td>
                                                <td class="text-end">{{ number_format($ot_c_ipd, 2) }}</td>
                                                <td class="text-end">{{ number_format($ot_p_ipd, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                // Charts
                @if(count($chart_series_opd) > 0)
                var opdChart = new ApexCharts(document.querySelector("#esrdOpdChart"), {
                    series: @json($chart_series_opd),
                    chart: { type: 'line', height: 300, toolbar: { show: true } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 5, strokeWidth: 2, hover: { size: 7 } },
                    xaxis: { categories: @json($month_categories) },
                    tooltip: { y: { formatter: function(val) { return val.toLocaleString(); } } }
                });
                opdChart.render();
                @endif

                @if(count($chart_series_ipd) > 0)
                var ipdChart = new ApexCharts(document.querySelector("#esrdIpdChart"), {
                    series: @json($chart_series_ipd),
                    chart: { type: 'line', height: 300, toolbar: { show: true } },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 5, strokeWidth: 2, hover: { size: 7 } },
                    xaxis: { categories: @json($month_categories) },
                    tooltip: { y: { formatter: function(val) { return val.toLocaleString(); } } }
                });
                ipdChart.render();
                @endif

                // DataTables Configuration
                const dataTableConfig = {
                    pageLength: 10,
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json' },
                    dom: 'lrtip'
                };

                var opdTable = $('#opdTable').DataTable(dataTableConfig);
                var ipdTable = $('#ipdTable').DataTable(dataTableConfig);

                // Excel Export Buttons
                new $.fn.dataTable.Buttons(opdTable, {
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานสรุปการใช้ยา ESRD (OPD) แยกตามสิทธิ',
                        exportOptions: { columns: ':visible' }
                    }]
                }).container().appendTo($('#opdExportBtn'));

                new $.fn.dataTable.Buttons(ipdTable, {
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'รายงานสรุปการใช้ยา ESRD (IPD) แยกตามสิทธิ',
                        exportOptions: { columns: ':visible' }
                    }]
                }).container().appendTo($('#ipdExportBtn'));

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

                // Fix chart and table render sizes on tab switch
                $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                    window.dispatchEvent(new Event('resize'));
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                });
            });
        </script>
    @endpush
@endsection
