@extends('layouts.app')

@section('title', 'SmartData | รายงานอุบัติการณ์')

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df;">
        <i class="fas fa-home me-1"></i> หน้าแรก
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
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

        .input-group-date {
            width: 160px !important;
        }

        .input-group-budget {
            width: 250px !important;
        }

        /* Matrix design */
        .matrix-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3px;
        }
        .matrix-table th, .matrix-table td {
            text-align: center;
            vertical-align: middle;
            padding: 10px;
            font-weight: bold;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .matrix-table th {
            background-color: #f8f9fc;
            color: #4e73df;
            font-weight: 700;
        }
        .matrix-label-col {
            text-align: left !important;
            background-color: #f8f9fc !important;
            color: #4e73df !important;
            width: 30%;
        }
        
        .risk-low {
            background-color: #28a745 !important;
            color: white !important;
        }
        .risk-medium {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }
        .risk-high {
            background-color: #fd7e14 !important;
            color: white !important;
        }
        .risk-unacceptable {
            background-color: #dc3545 !important;
            color: white !important;
        }
        
        .matrix-cell-link {
            color: inherit !important;
            text-decoration: underline;
            display: block;
            width: 100%;
            height: 100%;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .matrix-cell-link:hover {
            transform: scale(1.15);
        }

        .table-striped tbody a {
            text-decoration: underline !important;
        }

        /* Modal Table Premium Styles */
        #modalTableIncidents {
            border: 1px solid #e3e6f0 !important;
            border-collapse: collapse;
        }
        #modalTableIncidents thead th {
            background: #f8f9fc !important;
            color: #4e73df !important;
            border-bottom: 2px solid #e3e6f0 !important;
            padding: 10px 8px !important;
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            vertical-align: middle;
            border-top: none !important;
        }
        #modalTableIncidents tbody td {
            padding: 8px 8px !important;
            border-bottom: 1px solid #e3e6f0 !important;
            font-size: 0.8rem;
            color: #4f5d73;
            vertical-align: middle;
        }
        #modalTableIncidents tbody tr:hover {
            background-color: #f8fafd !important;
        }
        /* Custom scrollbar for detail cells */
        .detail-scroll {
            max-height: 70px;
            overflow-y: auto;
            white-space: pre-line;
            font-size: 0.78rem;
            color: #555;
            padding-right: 5px;
        }
        .detail-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .detail-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .detail-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 4px;
        }
        .detail-scroll::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        /* Modernizing DataTable elements inside modal */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d3e2 !important;
            border-radius: 8px !important;
            padding: 4px 10px !important;
            color: #6e707e !important;
            font-size: 0.85rem !important;
        }
        .dataTables_wrapper .dataTables_length select:focus,
        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none !important;
            border-color: #bac8f3 !important;
            box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.2) !important;
        }

        .quick-link-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
            transition: all 0.25s ease;
        }
        .quick-link-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.08);
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
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-4">
        <!-- Quick Links -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <a href="{{ route('backoffice.incident.nrls') }}" class="text-decoration-none">
                    <div class="card quick-link-card bg-primary text-white p-3 d-flex flex-row align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 text-white-50">NRLS</h6>
                            <h6 class="mb-0 fw-bold text-white">ข้อมูลรายงานส่ง NRLS (อุบัติการณ์ / การแก้ไข / Dataset)</h6>
                        </div>
                        <i class="fas fa-paper-plane fa-2x opacity-50"></i>
                    </div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="{{ route('backoffice.incident.med_error') }}" class="text-decoration-none">
                    <div class="card quick-link-card bg-warning text-white p-3 d-flex flex-row align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1 text-white-50">Medication Error</h6>
                            <h6 class="mb-0 fw-bold text-white text-truncate">Medication Error Report HOSxP</h6>
                        </div>
                        <i class="fas fa-pills fa-2x opacity-50"></i>
                    </div>
                </a>
            </div>
        </div>

        <!-- Header Box -->
        <div class="page-header-container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-triangle-exclamation text-primary me-2"></i>
                        รายงานอุบัติการณ์
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>

            <div>
                <form action="" method="GET" class="m-0 header-form-controls">
                    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
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
                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary px-3" style="font-size: 0.8rem;">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between">
                        <h6 class="fw-bold text-dark mb-0">รายงานอุบัติการณ์แยกรายเดือน ปีงบประมาณ {{ $budget_year }}</h6>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div style="position: relative; height: 350px;">
                            <canvas id="chartClinicalMonthly"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h6 class="fw-bold text-dark mb-0">สัดส่วนอุบัติการณ์ทั้งหมด</h6>
                    </div>
                    <div class="card-body px-4 pb-4 d-flex align-items-center justify-content-center">
                        <div style="position: relative; height: 300px; width: 100%;">
                            <canvas id="chartClinicalPie"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h6 class="fw-bold text-dark mb-0">รายงานอุบัติการณ์ตามระดับความรุนแรง ปีงบประมาณ {{ $budget_year }}</h6>
            </div>
            <div class="card-body px-4 pb-4">
                <div style="position: relative; height: 320px;">
                    <canvas id="chartLevelMonthly"></canvas>
                </div>
            </div>
        </div>

        <!-- Table: Level Summary -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-gradient-primary-custom text-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-list-ol me-2"></i>ตารางรายงานอุบัติการณ์ตามระดับความรุนแรง ปีงบประมาณ {{ $budget_year }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0 text-center" style="font-size: 0.85rem;">
                        <thead class="table-light text-primary fw-bold">
                            <tr>
                                <th class="text-start ps-4">เดือน</th>
                                <th>รวม</th>
                                <th>A</th>
                                <th>B</th>
                                <th>C</th>
                                <th>D</th>
                                <th>E</th>
                                <th>F</th>
                                <th>G</th>
                                <th>H</th>
                                <th>I</th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>Null</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $total_sum = 0;
                                $a_sum = 0; $b_sum = 0; $c_sum = 0; $d_sum = 0; $e_sum = 0; $f_sum = 0; $g_sum = 0; $h_sum = 0; $i_sum = 0;
                                $g1_sum = 0; $g2_sum = 0; $g3_sum = 0; $g4_sum = 0; $g5_sum = 0; $null_sum = 0;
                            @endphp
                            @foreach ($risk_clinic as $row)
                                <tr>
                                    <td class="text-start fw-bold ps-4">
                                        @if($row->total > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('all', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->month }}</a>
                                        @else
                                            {{ $row->month }}
                                        @endif
                                    </td>
                                    <td class="fw-bold text-primary">
                                        @if($row->total > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold" onclick="openTableDetailModal('all', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ number_format($row->total) }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->a > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('A', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->a }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->b > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('B', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->b }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->c > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('C', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->c }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->d > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('D', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->d }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->e > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('E', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->e }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->f > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('F', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->f }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('G', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->g }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->h > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('H', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->h }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->i > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('I', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->i }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g1 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('1', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->g1 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g2 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('2', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->g2 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g3 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('3', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->g3 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g4 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('4', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->g4 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g5 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('5', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->g5 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->null > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('Null', '{{ $row->month_num }}', '{{ $row->year_num }}', 'all')">{{ $row->null }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    $total_sum += $row->total;
                                    $a_sum += $row->a; $b_sum += $row->b; $c_sum += $row->c; $d_sum += $row->d; $e_sum += $row->e; $f_sum += $row->f; $g_sum += $row->g; $h_sum += $row->h; $i_sum += $row->i;
                                    $g1_sum += $row->g1; $g2_sum += $row->g2; $g3_sum += $row->g3; $g4_sum += $row->g4; $g5_sum += $row->g5; $null_sum += $row->null;
                                @endphp
                            @endforeach
                            <tr class="table-primary fw-bold text-dark">
                                <td class="text-start ps-4">รวม</td>
                                <td class="text-primary">
                                    @if($total_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold" onclick="openTableDetailModal('all', 'all', 'all', 'all')">{{ number_format($total_sum) }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($a_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('A', 'all', 'all', 'all')">{{ $a_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($b_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('B', 'all', 'all', 'all')">{{ $b_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($c_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('C', 'all', 'all', 'all')">{{ $c_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($d_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('D', 'all', 'all', 'all')">{{ $d_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($e_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('E', 'all', 'all', 'all')">{{ $e_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($f_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('F', 'all', 'all', 'all')">{{ $f_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($g_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('G', 'all', 'all', 'all')">{{ $g_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($h_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('H', 'all', 'all', 'all')">{{ $h_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($i_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('I', 'all', 'all', 'all')">{{ $i_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($g1_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('1', 'all', 'all', 'all')">{{ $g1_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($g2_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('2', 'all', 'all', 'all')">{{ $g2_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($g3_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('3', 'all', 'all', 'all')">{{ $g3_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($g4_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('4', 'all', 'all', 'all')">{{ $g4_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($g5_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('5', 'all', 'all', 'all')">{{ $g5_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($null_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('Null', 'all', 'all', 'all')">{{ $null_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Table: Program Summary -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-gradient-primary-custom text-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-shield-virus me-2"></i>รายงานอุบัติการณ์ตามโปรแกรมหลัก ปีงบประมาณ {{ $budget_year }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0 text-center" style="font-size: 0.85rem;">
                        <thead class="table-light text-primary fw-bold">
                            <tr>
                                <th class="text-start ps-4" style="width: 30%;">โปรแกรมหลัก</th>
                                <th>รวม</th>
                                <th>A</th>
                                <th>B</th>
                                <th>C</th>
                                <th>D</th>
                                <th>E</th>
                                <th>F</th>
                                <th>G</th>
                                <th>H</th>
                                <th>I</th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>Null</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $p_total_sum = 0;
                                $p_a_sum = 0; $p_b_sum = 0; $p_c_sum = 0; $p_d_sum = 0; $p_e_sum = 0; $p_f_sum = 0; $p_g_sum = 0; $p_h_sum = 0; $p_i_sum = 0;
                                $p_g1_sum = 0; $p_g2_sum = 0; $p_g3_sum = 0; $p_g4_sum = 0; $p_g5_sum = 0; $p_null_sum = 0;
                            @endphp
                            @foreach ($risk_program as $row)
                                <tr>
                                    <td class="text-start fw-bold ps-4">
                                        <a href="javascript:void(0)" class="text-decoration-none" onclick="openTableDetailModal('all', 'all', 'all', '{{ $row->id }}', 'all', 'all', 1)">
                                            {{ $row->RISK_REPPROGRAM_NAME }}
                                        </a>
                                    </td>
                                    <td class="fw-bold text-primary">
                                        @if($row->total > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold" onclick="openTableDetailModal('all', 'all', 'all', '{{ $row->id }}')">{{ number_format($row->total) }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->a > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('A', 'all', 'all', '{{ $row->id }}')">{{ $row->a }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->b > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('B', 'all', 'all', '{{ $row->id }}')">{{ $row->b }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->c > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('C', 'all', 'all', '{{ $row->id }}')">{{ $row->c }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->d > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('D', 'all', 'all', '{{ $row->id }}')">{{ $row->d }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->e > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('E', 'all', 'all', '{{ $row->id }}')">{{ $row->e }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->f > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('F', 'all', 'all', '{{ $row->id }}')">{{ $row->f }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('G', 'all', 'all', '{{ $row->id }}')">{{ $row->g }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->h > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('H', 'all', 'all', '{{ $row->id }}')">{{ $row->h }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->i > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('I', 'all', 'all', '{{ $row->id }}')">{{ $row->i }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g1 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('1', 'all', 'all', '{{ $row->id }}')">{{ $row->g1 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g2 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('2', 'all', 'all', '{{ $row->id }}')">{{ $row->g2 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g3 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('3', 'all', 'all', '{{ $row->id }}')">{{ $row->g3 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g4 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('4', 'all', 'all', '{{ $row->id }}')">{{ $row->g4 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->g5 > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('5', 'all', 'all', '{{ $row->id }}')">{{ $row->g5 }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->null > 0)
                                            <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('Null', 'all', 'all', '{{ $row->id }}')">{{ $row->null }}</a>
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    $p_total_sum += $row->total;
                                    $p_a_sum += $row->a; $p_b_sum += $row->b; $p_c_sum += $row->c; $p_d_sum += $row->d; $p_e_sum += $row->e; $p_f_sum += $row->f; $p_g_sum += $row->g; $p_h_sum += $row->h; $p_i_sum += $row->i;
                                    $p_g1_sum += $row->g1; $p_g2_sum += $row->g2; $p_g3_sum += $row->g3; $p_g4_sum += $row->g4; $p_g5_sum += $row->g5; $p_null_sum += $row->null;
                                @endphp
                            @endforeach
                            <tr class="table-primary fw-bold text-dark">
                                <td class="text-start ps-4">รวม</td>
                                <td class="text-primary">
                                    @if($p_total_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold" onclick="openTableDetailModal('all', 'all', 'all', 'all')">{{ number_format($p_total_sum) }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_a_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('A', 'all', 'all', 'all')">{{ $p_a_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_b_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('B', 'all', 'all', 'all')">{{ $p_b_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_c_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('C', 'all', 'all', 'all')">{{ $p_c_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_d_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('D', 'all', 'all', 'all')">{{ $p_d_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_e_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('E', 'all', 'all', 'all')">{{ $p_e_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_f_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('F', 'all', 'all', 'all')">{{ $p_f_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_g_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('G', 'all', 'all', 'all')">{{ $p_g_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_h_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('H', 'all', 'all', 'all')">{{ $p_h_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_i_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('I', 'all', 'all', 'all')">{{ $p_i_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_g1_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('1', 'all', 'all', 'all')">{{ $p_g1_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_g2_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('2', 'all', 'all', 'all')">{{ $p_g2_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_g3_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('3', 'all', 'all', 'all')">{{ $p_g3_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_g4_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('4', 'all', 'all', 'all')">{{ $p_g4_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_g5_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('5', 'all', 'all', 'all')">{{ $p_g5_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                                <td>
                                    @if($p_null_sum > 0)
                                        <a href="javascript:void(0)" class="text-decoration-none fw-bold text-dark" onclick="openTableDetailModal('Null', 'all', 'all', 'all')">{{ $p_null_sum }}</a>
                                    @else
                                        0
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Risk Assessment Matrix: Clinical -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-table me-2"></i>Incident Matrix แผนผังประเมินอุบัติการณ์ทางคลินิก</h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th style="width: 30%; text-align: left;">ระดับความรุนแรงอุบัติการณ์ทางคลินิก : ความถี่</th>
                                <th>มากกว่า 5 ปี ครั้ง (1)</th>
                                <th>2-5 ปี ครั้ง (2)</th>
                                <th>1 ปี ครั้ง (3)</th>
                                <th>2-5 เดือน ครั้ง (4)</th>
                                <th>ทุกสัปดาห์/เดือน (5)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 5 (I) -->
                            <tr>
                                <td class="matrix-label-col">สูงมาก/หายนะ ระดับความรุนแรง I</td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 5, 1)">{{ $matrix['c5_1'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 5, 2)">{{ $matrix['c5_2'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 5, 3)">{{ $matrix['c5_3'] ?? 0 }}</span></td>
                                <td class="risk-unacceptable"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 5, 4)">{{ $matrix['c5_4'] ?? 0 }}</span></td>
                                <td class="risk-unacceptable"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 5, 5)">{{ $matrix['c5_5'] ?? 0 }}</span></td>
                            </tr>
                            <!-- Row 4 (G,H) -->
                            <tr>
                                <td class="matrix-label-col">สูง/วิกฤต ระดับความรุนแรง G,H</td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 4, 1)">{{ $matrix['c4_1'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 4, 2)">{{ $matrix['c4_2'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 4, 3)">{{ $matrix['c4_3'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 4, 4)">{{ $matrix['c4_4'] ?? 0 }}</span></td>
                                <td class="risk-unacceptable"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 4, 5)">{{ $matrix['c4_5'] ?? 0 }}</span></td>
                            </tr>
                            <!-- Row 3 (E,F) -->
                            <tr>
                                <td class="matrix-label-col">ปานกลาง ระดับความรุนแรง E,F</td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 3, 1)">{{ $matrix['c3_1'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 3, 2)">{{ $matrix['c3_2'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 3, 3)">{{ $matrix['c3_3'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 3, 4)">{{ $matrix['c3_4'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 3, 5)">{{ $matrix['c3_5'] ?? 0 }}</span></td>
                            </tr>
                            <!-- Row 2 (B,C,D) -->
                            <tr>
                                <td class="matrix-label-col">ต่ำ/น้อย ระดับความรุนแรง B,C,D</td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 2, 1)">{{ $matrix['c2_1'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 2, 2)">{{ $matrix['c2_2'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 2, 3)">{{ $matrix['c2_3'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 2, 4)">{{ $matrix['c2_4'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 2, 5)">{{ $matrix['c2_5'] ?? 0 }}</span></td>
                            </tr>
                            <!-- Row 1 (A) -->
                            <tr>
                                <td class="matrix-label-col">ไม่เป็นสาระสำคัญ ระดับความรุนแรง A</td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 1, 1)">{{ $matrix['c1_1'] ?? 0 }}</span></td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 1, 2)">{{ $matrix['c1_2'] ?? 0 }}</span></td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 1, 3)">{{ $matrix['c1_3'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 1, 4)">{{ $matrix['c1_4'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('clinical', 1, 5)">{{ $matrix['c1_5'] ?? 0 }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Risk Assessment Matrix: General -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-info text-white py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-table me-2"></i>Incident Matrix แผนผังประเมินอุบัติการณ์ทั่วไป</h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th style="width: 30%; text-align: left;">ระดับความรุนแรงอุบัติการณ์ทั่วไป : ความถี่</th>
                                <th>มากกว่า 5 ปี ครั้ง (1)</th>
                                <th>2-5 ปี ครั้ง (2)</th>
                                <th>1 ปี ครั้ง (3)</th>
                                <th>2-5 เดือน ครั้ง (4)</th>
                                <th>ทุกสัปดาห์/เดือน (5)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 5 -->
                            <tr>
                                <td class="matrix-label-col">สูงมาก/หายนะ ระดับความรุนแรง 5</td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 5, 1)">{{ $matrix['g5_1'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('general', 5, 2)">{{ $matrix['g5_2'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('general', 5, 3)">{{ $matrix['g5_3'] ?? 0 }}</span></td>
                                <td class="risk-unacceptable"><span class="matrix-cell-link" onclick="openMatrixModal('general', 5, 4)">{{ $matrix['g5_4'] ?? 0 }}</span></td>
                                <td class="risk-unacceptable"><span class="matrix-cell-link" onclick="openMatrixModal('general', 5, 5)">{{ $matrix['g5_5'] ?? 0 }}</span></td>
                            </tr>
                            <!-- Row 4 -->
                            <tr>
                                <td class="matrix-label-col">สูง/วิกฤต ระดับความรุนแรง 4</td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 4, 1)">{{ $matrix['g4_1'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 4, 2)">{{ $matrix['g4_2'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('general', 4, 3)">{{ $matrix['g4_3'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('general', 4, 4)">{{ $matrix['g4_4'] ?? 0 }}</span></td>
                                <td class="risk-unacceptable"><span class="matrix-cell-link" onclick="openMatrixModal('general', 4, 5)">{{ $matrix['g4_5'] ?? 0 }}</span></td>
                            </tr>
                            <!-- Row 3 -->
                            <tr>
                                <td class="matrix-label-col">ปานกลาง ระดับความรุนแรง 3</td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('general', 3, 1)">{{ $matrix['g3_1'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 3, 2)">{{ $matrix['g3_2'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 3, 3)">{{ $matrix['g3_3'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('general', 3, 4)">{{ $matrix['g3_4'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('general', 3, 5)">{{ $matrix['g3_5'] ?? 0 }}</span></td>
                            </tr>
                            <!-- Row 2 -->
                            <tr>
                                <td class="matrix-label-col">ต่ำ/น้อย ระดับความรุนแรง 2</td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('general', 2, 1)">{{ $matrix['g2_1'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 2, 2)">{{ $matrix['g2_2'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 2, 3)">{{ $matrix['g2_3'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 2, 4)">{{ $matrix['g2_4'] ?? 0 }}</span></td>
                                <td class="risk-high"><span class="matrix-cell-link" onclick="openMatrixModal('general', 2, 5)">{{ $matrix['g2_5'] ?? 0 }}</span></td>
                            </tr>
                            <!-- Row 1 -->
                            <tr>
                                <td class="matrix-label-col">ไม่เป็นสาระสำคัญ ระดับความรุนแรง 1</td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('general', 1, 1)">{{ $matrix['g1_1'] ?? 0 }}</span></td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('general', 1, 2)">{{ $matrix['g1_2'] ?? 0 }}</span></td>
                                <td class="risk-low"><span class="matrix-cell-link" onclick="openMatrixModal('general', 1, 3)">{{ $matrix['g1_3'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 1, 4)">{{ $matrix['g1_4'] ?? 0 }}</span></td>
                                <td class="risk-medium"><span class="matrix-cell-link" onclick="openMatrixModal('general', 1, 5)">{{ $matrix['g1_5'] ?? 0 }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Matrix Details -->
    <div class="modal fade" id="incidentMatrixDetailModal" tabindex="-1" aria-labelledby="incidentMatrixDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header bg-gradient-primary-custom text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="incidentMatrixDetailModalLabel">
                        <i class="fas fa-list-alt me-2"></i>รายละเอียดอุบัติการณ์ตาม Incident Matrix
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" id="matrixModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลดข้อมูล...</span>
                        </div>
                        <div class="mt-3 text-muted">กำลังดึงข้อมูลอุบัติการณ์...</div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">ปิดหน้าต่าง</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chartjs-plugin-datalabels.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>

    <script>
        // Modal function
        function openMatrixModal(type, consequence, likelihood) {
            const modalEl = document.getElementById('incidentMatrixDetailModal');
            const modalBody = document.getElementById('matrixModalBody');
            const myModal = new bootstrap.Modal(modalEl);
            
            // Show loading spinner first
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลดข้อมูล...</span>
                    </div>
                    <div class="mt-3 text-muted">กำลังดึงข้อมูลความเสี่ยง...</div>
                </div>
            `;
            
            myModal.show();
            
            // Fetch contents
            const url = `{{ url('/backoffice/incident/matrix_detail') }}/${type}_${consequence}_${likelihood}`;
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    modalBody.innerHTML = html;
                    // Initialize DataTable if present in modal
                    $('#modalTableIncidents').DataTable({
                        language: {
                            search: "ค้นหา:",
                            lengthMenu: "แสดง _MENU_ รายการ",
                            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                            zeroRecords: "ไม่พบข้อมูลอุบัติการณ์ความเสี่ยงในช่วงเวลาและระดับที่เลือก",
                            paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                        },
                        pageLength: 10,
                        order: [[1, 'desc']]
                    });
                })
                .catch(err => {
                    modalBody.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>ไม่สามารถดึงข้อมูลได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง
                        </div>
                    `;
                });
        }

        // Table Cell Modal function
        function openTableDetailModal(level, month, year, program_id, sub_id = 'all', subsub_id = 'all', drilldown = 0) {
            const modalEl = document.getElementById('incidentMatrixDetailModal');
            const modalBody = document.getElementById('matrixModalBody');
            
            // Check if modal instance already exists, if not, create it
            let myModal = bootstrap.Modal.getInstance(modalEl);
            if (!myModal) {
                myModal = new bootstrap.Modal(modalEl);
            }
            
            // Show loading spinner first
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">กำลังโหลดข้อมูล...</span>
                    </div>
                    <div class="mt-3 text-muted">กำลังดึงข้อมูลอุบัติการณ์...</div>
                </div>
            `;
            
            myModal.show();
            
            // Fetch contents
            const url = `{{ route('backoffice.incident.table_detail') }}?level=${level}&month=${month}&year=${year}&program_id=${program_id}&sub_id=${sub_id}&subsub_id=${subsub_id}&drilldown=${drilldown}`;
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    modalBody.innerHTML = html;
                    
                    // Initialize DataTable if present in modal (flat incidents list)
                    if ($('#modalTableIncidents').length > 0) {
                        $('#modalTableIncidents').DataTable({
                            language: {
                                search: "ค้นหา:",
                                lengthMenu: "แสดง _MENU_ รายการ",
                                info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                                zeroRecords: "ไม่พบข้อมูลอุบัติการณ์ความเสี่ยงในช่วงเวลาและระดับที่เลือก",
                                paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                            },
                            pageLength: 10,
                            order: [[1, 'desc']]
                        });
                    }
                    
                    // Initialize DataTable if present in modal (drilldown list)
                    if ($('#modalTableDrilldown').length > 0) {
                        $('#modalTableDrilldown').DataTable({
                            language: {
                                search: "ค้นหา:",
                                lengthMenu: "แสดง _MENU_ รายการ",
                                info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                                zeroRecords: "ไม่พบข้อมูลอุบัติการณ์ความเสี่ยง",
                                paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                            },
                            pageLength: 10,
                            order: [[1, 'desc']]
                        });
                    }
                })
                .catch(err => {
                    modalBody.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>ไม่สามารถดึงข้อมูลได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง
                        </div>
                    `;
                });
        }

        $(document).ready(function() {
            // Register the datalabels plugin globally for all charts
            Chart.register(ChartDataLabels);

            // Chart 1: Clinical vs General monthly
            const clinicMonths = @json($risk_clinic_m);
            const clinicalValues = @json($risk_clinical);
            const generalValues = @json($risk_general);

            new Chart(document.getElementById('chartClinicalMonthly'), {
                type: 'line',
                data: {
                    labels: clinicMonths,
                    datasets: [
                        {
                            label: 'Clinical',
                            data: clinicalValues,
                            borderColor: '#0268c7',
                            backgroundColor: 'rgba(2, 104, 199, 0.05)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3
                        },
                        {
                            label: 'General',
                            data: generalValues,
                            borderColor: '#17a6a7',
                            backgroundColor: 'rgba(23, 166, 167, 0.05)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#5a5c69',
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            formatter: Math.round
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            suggestedMax: Math.max(...clinicalValues, ...generalValues) * 1.2
                        }
                    }
                }
            });

            // Chart 2: Proportion
            const clinicalTotal = {{ $risk_clinical_y }};
            const generalTotal = {{ $risk_general_y }};
            const nullTotal = {{ $risk_null_y }};

            new Chart(document.getElementById('chartClinicalPie'), {
                type: 'doughnut',
                data: {
                    labels: ['Clinical', 'General', 'Non-Program'],
                    datasets: [{
                        data: [clinicalTotal, generalTotal, nullTotal],
                        backgroundColor: ['#0268c7', '#17a6a7', '#fd7e14']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 11
                            },
                            formatter: (value, ctx) => {
                                let sum = 0;
                                let dataArr = ctx.chart.data.datasets[0].data;
                                dataArr.map(data => {
                                    sum += data;
                                });
                                if (sum === 0) return '';
                                let percentage = (value*100 / sum).toFixed(1)+"%";
                                return percentage;
                            }
                        }
                    }
                }
            });

            // Chart 3: Level distribution monthly
            const nearMissValues = @json($risk_lavel_near_miss);
            const lowRiskValues = @json($risk_lavel_low_risk);
            const moderateRiskValues = @json($risk_lavel_moderate_risk);
            const highRiskValues = @json($risk_lavel_high_risk);

            new Chart(document.getElementById('chartLevelMonthly'), {
                type: 'bar',
                data: {
                    labels: clinicMonths,
                    datasets: [
                        { label: 'Near Miss', data: nearMissValues, backgroundColor: '#28a745' },
                        { label: 'Low Risk', data: lowRiskValues, backgroundColor: '#17a6a7' },
                        { label: 'Moderate Risk', data: moderateRiskValues, backgroundColor: '#ffc107' },
                        { label: 'High Risk', data: highRiskValues, backgroundColor: '#dc3545' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            color: '#5a5c69',
                            font: {
                                weight: 'bold',
                                size: 10
                            },
                            formatter: function(value) {
                                return value > 0 ? value : '';
                            }
                        }
                    },
                    scales: {
                        x: { stacked: false },
                        y: { stacked: false, beginAtZero: true }
                    }
                }
            });

            // Date Picker Init
            if (typeof flatpickr !== 'undefined') {
                const yearOffset = 543;
                const commonConfig = {
                    locale: "th",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "j M Y",
                    allowInput: false,
                    onReady: function(selectedDates, dateStr, instance) {
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

                // Update dates when budget year shifts
                $('select[name="budget_year"]').on('change', function() {
                    const selectedYear = parseInt($(this).val());
                    if (!isNaN(selectedYear)) {
                        const startYear = selectedYear - 544; // e.g. 2569 -> 2025
                        const endYear = selectedYear - 543;   // e.g. 2569 -> 2026
                        const startDateStr = startYear + "-10-01";
                        const endDateStr = endYear + "-09-30";
                        
                        setTimeout(() => {
                            if (typeof startPicker !== 'undefined' && startPicker) startPicker.setDate(startDateStr, true);
                            if (typeof endPicker !== 'undefined' && endPicker) endPicker.setDate(endDateStr, true);
                        }, 50);
                    }
                });
            }
        });
    </script>
@endpush
