@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.physic.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
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

        .table-stats thead th {
            vertical-align: middle;
            text-align: center;
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            font-size: 0.85rem;
        }

        .table-stats tbody td {
            font-size: 0.85rem;
            vertical-align: middle;
        }

        .text-orange { color: #f97316; }
        .bg-pastel-orange { background-color: #fff7ed; }

        .nav-pills .nav-link {
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.2s;
            color: #64748b;
        }

        .nav-pills .nav-link.active {
            background-color: #f97316;
            color: white;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.2);
        }

        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            background-color: #f8fafc !important;
            z-index: 1;
        }

        /* Flatpickr Today Button Style */
        .flatpickr-today-button {
            padding: 10px;
            text-align: center;
            border-top: 1px solid #e6e6e6;
            cursor: pointer;
            font-weight: bold;
            color: #f97316;
            background: #f8f9fa;
        }
        .flatpickr-today-button:hover {
            background: #fff7ed;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-chart-line text-orange me-2"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง
                        {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <form action="" method="GET" id="filter-form" class="m-0 header-form-controls">
                    <input type="hidden" name="budget_year_changed" id="budget_year_changed" value="0">
                    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-orange"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-orange"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0"
                            value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget"
                        style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" id="budget_year" style="font-size: 0.8rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-orange text-white px-3" style="font-size: 0.8rem; background-color: #f97316; border-color: #f97316;"><i
                                class="fas fa-search"></i> ค้นหา</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-pills mb-4 bg-white p-1 shadow-sm d-inline-flex" style="border-radius: 15px; border: 1px solid #f1f5f9;">
            <li class="nav-item">
                <a class="nav-link active" id="opd-tab" data-bs-toggle="pill" href="#opd-content"><i class="fas fa-user-nurse me-2"></i>ผู้ป่วยนอก (OPD)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="ipd-tab" data-bs-toggle="pill" href="#ipd-content"><i class="fas fa-bed-pulse me-2"></i>ผู้ป่วยใน (IPD)</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- OPD Tab Content -->
            <div class="tab-pane fade show active" id="opd-content">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-pastel-orange py-3 border-0" style="border-radius: 15px 15px 0 0;">
                                <h6 class="fw-bold mb-0 text-orange"><i class="fas fa-chart-bar me-2"></i>กราฟสรุปจำนวนผู้รับบริการแยกตามสิทธิ (OPD)</h6>
                            </div>
                            <div class="card-body">
                                <div id="chart-opd"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-pastel-orange py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                                <h6 class="fw-bold mb-0 text-orange"><i class="fas fa-table me-2"></i>ตารางข้อมูลรายเดือนแยกตามสิทธิ (OPD)</h6>
                                <button type="button" class="btn btn-sm btn-success px-2 shadow-sm btn-export-excel" data-target="#table-opd" style="font-size: 0.75rem; padding: 2px 8px;">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-stats mb-0" id="table-opd">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="sticky-col">เดือน</th>
                                                <th colspan="4" class="bg-light">รวมทั้งหมด</th>
                                                <th colspan="4" style="background-color: #e0f2fe;">ประกันสุขภาพ (UCS)</th>
                                                <th colspan="4" style="background-color: #fef9c3;">ข้าราชการ (OFC)</th>
                                                <th colspan="4" style="background-color: #dcfce7;">ประกันสังคม (SSS)</th>
                                                <th colspan="4" style="background-color: #f3e8ff;">อปท. (LGO)</th>
                                                <th colspan="4" style="background-color: #fee2e2;">ชำระเงิน/พรบ.</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">HN</th><th class="bg-light">Visit</th><th class="bg-light">กายภาพบำบัด</th><th class="bg-light">ค่า Instrument</th>
                                                <th>HN</th><th>Visit</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                                <th>HN</th><th>Visit</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                                <th>HN</th><th>Visit</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                                <th>HN</th><th>Visit</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                                <th>HN</th><th>Visit</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totals = [
                                                    'hn' => 0, 'visit' => 0, 'service' => 0, 'inst' => 0,
                                                    'hn_ucs' => 0, 'visit_ucs' => 0, 'service_ucs' => 0, 'inst_ucs' => 0,
                                                    'hn_ofc' => 0, 'visit_ofc' => 0, 'service_ofc' => 0, 'inst_ofc' => 0,
                                                    'hn_sss' => 0, 'visit_sss' => 0, 'service_sss' => 0, 'inst_sss' => 0,
                                                    'hn_lgo' => 0, 'visit_lgo' => 0, 'service_lgo' => 0, 'inst_lgo' => 0,
                                                    'hn_pay' => 0, 'visit_pay' => 0, 'service_pay' => 0, 'inst_pay' => 0,
                                                ];
                                            @endphp
                                            @foreach($stats_opd as $row)
                                            <tr>
                                                <td class="sticky-col text-center fw-bold">{{ $row->month_name }}</td>
                                                <td class="text-center">{{ number_format($row->total_hn) }}</td>
                                                <td class="text-center text-primary fw-bold">{{ number_format($row->total_visit) }}</td>
                                                <td class="text-end">{{ number_format($row->total_sum_service, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->total_sum_inst, 2) }}</td>
                                                
                                                <td class="text-center">{{ number_format($row->hn_ucs) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_ucs) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_ucs, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_ucs, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_ofc) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_ofc) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_ofc, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_ofc, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_sss) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_sss) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_sss, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_sss, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_lgo) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_lgo) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_lgo, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_lgo, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_pay) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_pay) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_pay, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_pay, 2) }}</td>
                                            </tr>
                                            @php
                                                $totals['hn'] += $row->total_hn; $totals['visit'] += $row->total_visit; $totals['service'] += $row->total_sum_service; $totals['inst'] += $row->total_sum_inst;
                                                $totals['hn_ucs'] += $row->hn_ucs; $totals['visit_ucs'] += $row->visit_ucs; $totals['service_ucs'] += $row->sum_price_service_ucs; $totals['inst_ucs'] += $row->sum_price_inst_ucs;
                                                $totals['hn_ofc'] += $row->hn_ofc; $totals['visit_ofc'] += $row->visit_ofc; $totals['service_ofc'] += $row->sum_price_service_ofc; $totals['inst_ofc'] += $row->sum_price_inst_ofc;
                                                $totals['hn_sss'] += $row->hn_sss; $totals['visit_sss'] += $row->visit_sss; $totals['service_sss'] += $row->sum_price_service_sss; $totals['inst_sss'] += $row->sum_price_inst_sss;
                                                $totals['hn_lgo'] += $row->hn_lgo; $totals['visit_lgo'] += $row->visit_lgo; $totals['service_lgo'] += $row->sum_price_service_lgo; $totals['inst_lgo'] += $row->sum_price_inst_lgo;
                                                $totals['hn_pay'] += $row->hn_pay; $totals['visit_pay'] += $row->visit_pay; $totals['service_pay'] += $row->sum_price_service_pay; $totals['inst_pay'] += $row->sum_price_inst_pay;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light fw-bold">
                                            <tr>
                                                <td class="sticky-col text-center">รวม</td>
                                                <td class="text-center">{{ number_format($totals['hn']) }}</td>
                                                <td class="text-center text-primary">{{ number_format($totals['visit']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['inst'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_ucs']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_ucs']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_ucs'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['inst_ucs'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_ofc']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_ofc']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_ofc'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['inst_ofc'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_sss']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_sss']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_sss'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['inst_sss'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_lgo']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_lgo']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_lgo'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['inst_lgo'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals['hn_pay']) }}</td>
                                                <td class="text-center">{{ number_format($totals['visit_pay']) }}</td>
                                                <td class="text-end">{{ number_format($totals['service_pay'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals['inst_pay'], 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IPD Tab Content -->
            <div class="tab-pane fade" id="ipd-content">
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-pastel-orange py-3 border-0" style="border-radius: 15px 15px 0 0;">
                                <h6 class="fw-bold mb-0 text-orange"><i class="fas fa-chart-bar me-2"></i>กราฟสรุปจำนวนผู้รับบริการแยกตามสิทธิ (IPD)</h6>
                            </div>
                            <div class="card-body">
                                <div id="chart-ipd"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-header bg-pastel-orange py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                                <h6 class="fw-bold mb-0 text-orange"><i class="fas fa-table me-2"></i>ตารางข้อมูลรายเดือนแยกตามสิทธิ (IPD)</h6>
                                <button type="button" class="btn btn-sm btn-success px-2 shadow-sm btn-export-excel" data-target="#table-ipd" style="font-size: 0.75rem; padding: 2px 8px;">
                                    <i class="fas fa-file-excel me-1"></i> Excel
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-stats mb-0" id="table-ipd">
                                        <thead>
                                            <tr>
                                                <th rowspan="2" class="sticky-col">เดือน</th>
                                                <th colspan="4" class="bg-light">รวมทั้งหมด</th>
                                                <th colspan="4" style="background-color: #e0f2fe;">ประกันสุขภาพ (UCS)</th>
                                                <th colspan="4" style="background-color: #fef9c3;">ข้าราชการ (OFC)</th>
                                                <th colspan="4" style="background-color: #dcfce7;">ประกันสังคม (SSS)</th>
                                                <th colspan="4" style="background-color: #f3e8ff;">อปท. (LGO)</th>
                                                <th colspan="4" style="background-color: #fee2e2;">ชำระเงิน/พรบ.</th>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">HN</th><th class="bg-light">Admission</th><th class="bg-light">กายภาพบำบัด</th><th class="bg-light">Inst</th>
                                                <th>HN</th><th>Admission</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                                <th>HN</th><th>Admission</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                                <th>HN</th><th>Admission</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                                <th>HN</th><th>Admission</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                                <th>HN</th><th>Admission</th><th>กายภาพบำบัด</th><th>Instrument</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $totals_ipd = [
                                                    'hn' => 0, 'visit' => 0, 'service' => 0, 'inst' => 0,
                                                    'hn_ucs' => 0, 'visit_ucs' => 0, 'service_ucs' => 0, 'inst_ucs' => 0,
                                                    'hn_ofc' => 0, 'visit_ofc' => 0, 'service_ofc' => 0, 'inst_ofc' => 0,
                                                    'hn_sss' => 0, 'visit_sss' => 0, 'service_sss' => 0, 'inst_sss' => 0,
                                                    'hn_lgo' => 0, 'visit_lgo' => 0, 'service_lgo' => 0, 'inst_lgo' => 0,
                                                    'hn_pay' => 0, 'visit_pay' => 0, 'service_pay' => 0, 'inst_pay' => 0,
                                                ];
                                            @endphp
                                            @foreach($stats_ipd as $row)
                                            <tr>
                                                <td class="sticky-col text-center fw-bold">{{ $row->month_name }}</td>
                                                <td class="text-center">{{ number_format($row->total_hn) }}</td>
                                                <td class="text-center text-primary fw-bold">{{ number_format($row->total_visit) }}</td>
                                                <td class="text-end">{{ number_format($row->total_sum_service, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->total_sum_inst, 2) }}</td>
                                                
                                                <td class="text-center">{{ number_format($row->hn_ucs) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_ucs) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_ucs, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_ucs, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_ofc) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_ofc) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_ofc, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_ofc, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_sss) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_sss) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_sss, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_sss, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_lgo) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_lgo) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_lgo, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_lgo, 2) }}</td>

                                                <td class="text-center">{{ number_format($row->hn_pay) }}</td>
                                                <td class="text-center fw-bold">{{ number_format($row->visit_pay) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_service_pay, 2) }}</td>
                                                <td class="text-end">{{ number_format($row->sum_price_inst_pay, 2) }}</td>
                                            </tr>
                                            @php
                                                $totals_ipd['hn'] += $row->total_hn; $totals_ipd['visit'] += $row->total_visit; $totals_ipd['service'] += $row->total_sum_service; $totals_ipd['inst'] += $row->total_sum_inst;
                                                $totals_ipd['hn_ucs'] += $row->hn_ucs; $totals_ipd['visit_ucs'] += $row->visit_ucs; $totals_ipd['service_ucs'] += $row->sum_price_service_ucs; $totals_ipd['inst_ucs'] += $row->sum_price_inst_ucs;
                                                $totals_ipd['hn_ofc'] += $row->hn_ofc; $totals_ipd['visit_ofc'] += $row->visit_ofc; $totals_ipd['service_ofc'] += $row->sum_price_service_ofc; $totals_ipd['inst_ofc'] += $row->sum_price_inst_ofc;
                                                $totals_ipd['hn_sss'] += $row->hn_sss; $totals_ipd['visit_sss'] += $row->visit_sss; $totals_ipd['service_sss'] += $row->sum_price_service_sss; $totals_ipd['inst_sss'] += $row->sum_price_inst_sss;
                                                $totals_ipd['hn_lgo'] += $row->hn_lgo; $totals_ipd['visit_lgo'] += $row->visit_lgo; $totals_ipd['service_lgo'] += $row->sum_price_service_lgo; $totals_ipd['inst_lgo'] += $row->sum_price_inst_lgo;
                                                $totals_ipd['hn_pay'] += $row->hn_pay; $totals_ipd['visit_pay'] += $row->visit_pay; $totals_ipd['service_pay'] += $row->sum_price_service_pay; $totals_ipd['inst_pay'] += $row->sum_price_inst_pay;
                                            @endphp
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light fw-bold">
                                            <tr>
                                                <td class="sticky-col text-center">รวม</td>
                                                <td class="text-center">{{ number_format($totals_ipd['hn']) }}</td>
                                                <td class="text-center text-primary">{{ number_format($totals_ipd['visit']) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['service'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['inst'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals_ipd['hn_ucs']) }}</td>
                                                <td class="text-center">{{ number_format($totals_ipd['visit_ucs']) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['service_ucs'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['inst_ucs'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals_ipd['hn_ofc']) }}</td>
                                                <td class="text-center">{{ number_format($totals_ipd['visit_ofc']) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['service_ofc'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['inst_ofc'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals_ipd['hn_sss']) }}</td>
                                                <td class="text-center">{{ number_format($totals_ipd['visit_sss']) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['service_sss'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['inst_sss'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals_ipd['hn_lgo']) }}</td>
                                                <td class="text-center">{{ number_format($totals_ipd['visit_lgo']) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['service_lgo'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['inst_lgo'], 2) }}</td>

                                                <td class="text-center">{{ number_format($totals_ipd['hn_pay']) }}</td>
                                                <td class="text-center">{{ number_format($totals_ipd['visit_pay']) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['service_pay'], 2) }}</td>
                                                <td class="text-end">{{ number_format($totals_ipd['inst_pay'], 2) }}</td>
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
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            $(document).ready(function() {
                // Flatpickr Setup
                let startPicker, endPicker;
                if (typeof flatpickr !== 'undefined') {
                    const yearOffset = 543;
                    const commonConfig = {
                        locale: "th",
                        dateFormat: "Y-m-d",
                        altInput: true,
                        altFormat: "j M Y",
                        allowInput: false,
                        onReady: function(selectedDates, dateStr, instance) {
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
                    startPicker = flatpickr("#start_date", commonConfig);
                    endPicker = flatpickr("#end_date", commonConfig);
                }

                // Update start_date and end_date based on budget_year change (without immediate submit)
                $('#budget_year').on('change', function() {
                    var selectedYear = parseInt($(this).val());
                    if(!isNaN(selectedYear)) {
                        // Calculate budget year ranges
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

                // Charts
                const categories_opd = {!! json_encode(array_column($stats_opd, 'month_name')) !!};
                const series_opd = [
                    { name: 'UCS', data: {!! json_encode(array_column($stats_opd, 'visit_ucs')) !!} },
                    { name: 'OFC', data: {!! json_encode(array_column($stats_opd, 'visit_ofc')) !!} },
                    { name: 'SSS', data: {!! json_encode(array_column($stats_opd, 'visit_sss')) !!} },
                    { name: 'LGO', data: {!! json_encode(array_column($stats_opd, 'visit_lgo')) !!} },
                    { name: 'Pay', data: {!! json_encode(array_column($stats_opd, 'visit_pay')) !!} }
                ];

                const chartOptions = {
                    series: series_opd,
                    chart: { 
                        type: 'bar', 
                        height: 450, 
                        toolbar: { show: true }, 
                        zoom: { enabled: false },
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '80%',
                            borderRadius: 4,
                            dataLabels: { position: 'top' }
                        }
                    },
                    dataLabels: { 
                        enabled: true,
                        offsetY: -20,
                        style: { 
                            fontSize: '11px', 
                            fontWeight: 'bold',
                            colors: ["#444"] 
                        },
                        formatter: function(val) {
                            return val > 0 ? val : '';
                        }
                    },
                    stroke: { show: true, width: 1, colors: ['transparent'] },
                    xaxis: { 
                        categories: categories_opd,
                        labels: { style: { fontSize: '12px', fontWeight: 600 } }
                    },
                    yaxis: { 
                        title: { text: 'จำนวนครั้ง (Visit)', style: { fontWeight: 600 } },
                        labels: { formatter: (val) => val.toLocaleString() }
                    },
                    fill: { opacity: 1 },
                    legend: {
                        show: true,
                        position: 'top',
                        horizontalAlign: 'center',
                    },
                    tooltip: { y: { formatter: function (val) { return val + " ครั้ง" } } },
                    colors: ['#0ea5e9', '#eab308', '#22c55e', '#a855f7', '#ef4444'],
                    grid: { borderColor: '#f1f1f1', padding: { top: 10 } }
                };

                const chartOpd = new ApexCharts(document.querySelector("#chart-opd"), chartOptions);
                chartOpd.render();
                
                // IPD Chart
                const categories_ipd = {!! json_encode(array_column($stats_ipd, 'month_name')) !!};
                const series_ipd = [
                    { name: 'UCS', data: {!! json_encode(array_column($stats_ipd, 'visit_ucs')) !!} },
                    { name: 'OFC', data: {!! json_encode(array_column($stats_ipd, 'visit_ofc')) !!} },
                    { name: 'SSS', data: {!! json_encode(array_column($stats_ipd, 'visit_sss')) !!} },
                    { name: 'LGO', data: {!! json_encode(array_column($stats_ipd, 'visit_lgo')) !!} },
                    { name: 'Pay', data: {!! json_encode(array_column($stats_ipd, 'visit_pay')) !!} }
                ];
                
                const ipdOptions = { 
                    ...chartOptions, 
                    series: series_ipd,
                    xaxis: { 
                        ...chartOptions.xaxis,
                        categories: categories_ipd 
                    },
                    yaxis: { 
                        ...chartOptions.yaxis,
                        title: { ...chartOptions.yaxis.title, text: 'จำนวนครั้ง (Admission)' } 
                    }
                };
                const chartIpd = new ApexCharts(document.querySelector("#chart-ipd"), ipdOptions);
                chartIpd.render();

                // Fix chart rendering in hidden tabs
                $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
                    window.dispatchEvent(new Event('resize'));
                });

                // Excel Export
                $('.btn-export-excel').on('click', function() {
                    const target = $(this).data('target');
                    const title = $(this).prev('h6').text().trim();
                    
                    // Create a temporary DataTable to handle export
                    const dt = $(target).DataTable({
                        retrieve: true,
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: false,
                        autoWidth: false,
                        dom: 'tB',
                        buttons: [{
                            extend: 'excelHtml5',
                            title: title,
                            messageTop: 'ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}',
                            filename: title + '_{{ date("Ymd") }}'
                        }]
                    });
                    dt.button(0).trigger();
                });
            });
        </script>
    @endpush
@endsection
