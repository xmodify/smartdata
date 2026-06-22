@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.ncd.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #00796b; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }

        .report-title-box h4 {
            font-size: 1.1rem;
            letter-spacing: -0.01em;
        }

        .header-form-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .input-group-date {
            width: 160px !important;
        }

        .input-group-budget {
            width: 250px !important;
        }

        @media (max-width: 768px) {
            .page-header-container {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .header-form-controls {
                width: 100%;
            }

            .input-group-date,
            .input-group-budget {
                width: 100% !important;
            }
        }

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            color: #00796b;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }

        .flatpickr-today-button:hover {
            background: #f0fdfa;
            color: #004d40;
        }

        .bg-pastel-blue   { background-color: #f0f9ff; }
        .bg-pastel-green  { background-color: #f0fdf4; }
        .bg-pastel-amber  { background-color: #fffbeb; }
        .bg-pastel-purple { background-color: #faf5ff; }
        .bg-pastel-rose   { background-color: #fff1f2; }
        .bg-pastel-cyan   { background-color: #ecfeff; }
        .bg-pastel-indigo { background-color: #eef2ff; }
        .bg-pastel-teal   { background-color: #f0fdfa; }
        .bg-pastel-orange { background-color: #fff7ed; }
        .bg-pastel-gray   { background-color: #f8f9fa; }

        /* Soft Header Colors */
        .header-all { background-color: #f8fafc !important; color: #334155 !important; }
        .header-ucs-in { background-color: #e0f2fe !important; color: #0369a1 !important; }
        .header-ucs-inprov { background-color: #e0e7ff !important; color: #4338ca !important; }
        .header-ucs-out { background-color: #fff7ed !important; color: #9a3412 !important; }
        .header-ofc { background-color: #f1f5f9 !important; color: #475569 !important; }
        .header-sss { background-color: #fee2e2 !important; color: #b91c1c !important; }
        .header-lgo { background-color: #f3e8ff !important; color: #7e22ce !important; }
        .header-fss { background-color: #dcfce7 !important; color: #15803d !important; }
        .header-stp { background-color: #fef9c3 !important; color: #a16207 !important; }
        .header-pay { background-color: #f8fafc !important; color: #334155 !important; }

        .card-stats {
            border-radius: 15px;
            transition: transform 0.2s ease, shadow 0.2s ease;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }

        .table-hd-stats {
            font-size: 0.85rem;
        }

        .table-hd-stats th {
            vertical-align: middle;
            text-align: center;
            background-color: #e9ecef;
            border-bottom: 2px solid #dee2e6;
        }

        .table-hd-stats td {
            vertical-align: middle;
        }

        .text-num {
            font-family: 'Inter', sans-serif;
            text-align: right;
        }

        .header-group {
            border-bottom: 2px solid rgba(0,0,0,0.05) !important;
            white-space: nowrap;
            padding: 10px 15px !important;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .sticky-col-header {
            position: sticky;
            left: 0;
            background-color: #f8fafc !important;
            color: #334155 !important;
            z-index: 11;
            border-right: 2px solid #e2e8f0 !important;
            font-weight: 700;
        }

        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e2e8f0;
        }

        .table-stats {
            white-space: nowrap;
            font-size: 0.85rem;
        }

        .table-stats th {
            text-align: center;
            vertical-align: middle;
            padding: 10px 15px;
            font-weight: 600;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            background-color: #f8f9fa !important;
            z-index: 10;
            border-right: 2px solid #dee2e6 !important;
        }

        .stat-val {
            font-size: 1.5rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .label-small {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
        }

        .chart-container {
            min-height: 350px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header Page -->
        <div class="page-header-container d-flex flex-wrap justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h4 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-procedures text-teal me-2"></i> {{ $title }}
                    </h4>
                    <div class="text-muted small mt-1">สรุปผลการดำเนินงานการให้บริการผู้ป่วยฟอกไต HD เอกชน ปีงบประมาณ
                        <strong>{{ $budget_year }}</strong>
                    </div>
                    <div class="text-teal small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ข้อมูลระหว่างวันที่ {{ DateThai($start_date) }} ถึง
                        {{ DateThai($end_date) }}
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <span class="me-1 fw-bold text-muted small">ช่วงวันที่:</span>
                    <input type="hidden" name="budget_year_changed" id="budget_year_changed" value="0">
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-teal"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-teal"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0"
                            value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget"
                        style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-teal text-white px-3" style="font-size: 0.8rem; background-color: #00796b;">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="row g-3 mb-4">
            @php
                $total_visit = array_sum($visits);
                $total_hd_fee = array_sum($inc_hds);
                $total_drug_fee = array_sum($inc_drugs);
            @endphp
            <div class="col-md-3 col-sm-6">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-amber p-2 rounded-3">
                                <i class="fas fa-clipboard-list text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">จำนวนคนไข้นอกทะเบียน</div>
                        <div class="stat-val text-warning">{{ number_format($total_register) }} <span
                                class="fs-6 fw-normal text-muted">คน</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-blue p-2 rounded-3">
                                <i class="fas fa-users text-primary fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">จำนวนการรับบริการทั้งหมด</div>
                        <div class="stat-val text-dark">{{ number_format($total_visit) }} <span
                                class="fs-6 fw-normal text-muted">ครั้ง</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-teal p-2 rounded-3">
                                <i class="fas fa-procedures text-teal fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">ค่าฟอกไต (Hemodialysis)</div>
                        <div class="stat-val text-teal">{{ number_format($total_hd_fee, 2) }} <span
                                class="fs-6 fw-normal text-muted">บาท</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-purple p-2 rounded-3">
                                <i class="fas fa-pills text-purple fa-lg" style="color: #6f42c1;"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">ค่ายา (EPO)</div>
                        <div class="stat-val text-dark" style="color: #6f42c1 !important;">{{ number_format($total_drug_fee, 2) }} <span
                                class="fs-6 fw-normal text-muted">บาท</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-4">
            <!-- Monthly Visit & HN Chart -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">กราฟจำนวนผู้รับบริการฟอกไตเอกชนรายเดือน</h5>
                        <p class="text-muted small">เปรียบเทียบจำนวนผู้รับบริการ (HN) และการมารับบริการรวม (Visit)</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="monthlyChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <!-- Monthly Expenses Chart (HD vs Drug) -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">เปรียบเทียบค่าฟอกไตและค่ายารายเดือน</h5>
                        <p class="text-muted small">แสดงสัดส่วนค่าฟอกไต และค่ายา (EPO) แยกตามรายเดือน</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="expensesChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-pastel-teal py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 20px 20px 0 0;">
                        <h6 class="fw-bold mb-0 text-teal"><i class="fas fa-table me-2"></i>ตารางสถิติแยกตามกลุ่มสิทธิการรักษาและค่าใช้จ่าย (HD เอกชน)</h6>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-success px-2 shadow-sm btn-export-excel" data-target="#hdStatsTable" style="font-size: 0.75rem; padding: 2px 8px;">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-stats mb-0" id="hdStatsTable">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="sticky-col-header">เดือน</th>
                                        <th colspan="3" class="header-group header-all border-end">ทั้งหมด</th>
                                        <th colspan="3" class="header-group header-ucs-in border-end">ประกันสุขภาพ ใน CUP</th>
                                        <th colspan="3" class="header-group header-ucs-inprov border-end">ประกันสุขภาพ ในจังหวัด</th>
                                        <th colspan="3" class="header-group header-ucs-out border-end">ประกันสุขภาพ ต่างจังหวัด</th>
                                        <th colspan="3" class="header-group header-ofc border-end">ข้าราชการ</th>
                                        <th colspan="3" class="header-group header-sss border-end">ประกันสังคม</th>
                                        <th colspan="3" class="header-group header-lgo border-end">อปท.</th>
                                        <th colspan="3" class="header-group header-fss border-end">ต่างด้าว</th>
                                        <th colspan="3" class="header-group header-stp border-end">Stateless (STP)</th>
                                        <th colspan="3" class="header-group header-pay">ชำระเงิน/อื่นๆ</th>
                                    </tr>
                                    <tr>
                                        <!-- All -->
                                        <th class="col-visit bg-pastel-blue">Visit</th>
                                        <th class="bg-pastel-blue">ฟอกไต</th>
                                        <th class="bg-pastel-blue border-end">ค่ายา</th>
                                        <!-- UCS IN -->
                                        <th class="col-visit bg-pastel-green">Visit</th>
                                        <th class="bg-pastel-green">ฟอกไต</th>
                                        <th class="bg-pastel-green border-end">ค่ายา</th>
                                        <!-- UCS INPROV -->
                                        <th class="col-visit bg-pastel-indigo">Visit</th>
                                        <th class="bg-pastel-indigo">ฟอกไต</th>
                                        <th class="bg-pastel-indigo border-end">ค่ายา</th>
                                        <!-- UCS OUT -->
                                        <th class="col-visit bg-pastel-amber">Visit</th>
                                        <th class="bg-pastel-amber">ฟอกไต</th>
                                        <th class="bg-pastel-amber border-end">ค่ายา</th>
                                        <!-- OFC -->
                                        <th class="col-visit bg-pastel-purple">Visit</th>
                                        <th class="bg-pastel-purple">ฟอกไต</th>
                                        <th class="bg-pastel-purple border-end">ค่ายา</th>
                                        <!-- SSS -->
                                        <th class="col-visit bg-pastel-rose">Visit</th>
                                        <th class="bg-pastel-rose">ฟอกไต</th>
                                        <th class="bg-pastel-rose border-end">ค่ายา</th>
                                        <!-- LGO -->
                                        <th class="col-visit bg-pastel-cyan">Visit</th>
                                        <th class="bg-pastel-cyan">ฟอกไต</th>
                                        <th class="bg-pastel-cyan border-end">ค่ายา</th>
                                        <!-- FSS -->
                                        <th class="col-visit bg-pastel-teal">Visit</th>
                                        <th class="bg-pastel-teal">ฟอกไต</th>
                                        <th class="bg-pastel-teal border-end">ค่ายา</th>
                                        <!-- STP -->
                                        <th class="col-visit bg-pastel-orange">Visit</th>
                                        <th class="bg-pastel-orange">ฟอกไต</th>
                                        <th class="bg-pastel-orange border-end">ค่ายา</th>
                                        <!-- PAY -->
                                        <th class="col-visit bg-pastel-gray">Visit</th>
                                        <th class="bg-pastel-gray">ฟอกไต</th>
                                        <th class="bg-pastel-gray border-end">ค่ายา</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($visit_month as $row)
                                        <tr>
                                            <td class="sticky-col text-center fw-bold">{{ $row->month }}</td>
                                            
                                            <!-- All -->
                                            <td class="text-num col-visit bg-pastel-blue fw-bold">{{ number_format($row->visit) }}</td>
                                            <td class="text-num bg-pastel-blue text-teal">{{ number_format($row->inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-blue border-end" style="color: #6f42c1;">{{ number_format($row->inc_drug, 2) }}</td>

                                            <!-- UCS IN -->
                                            <td class="text-num bg-pastel-green">{{ number_format($row->ucs_incup) }}</td>
                                            <td class="text-num bg-pastel-green text-teal">{{ number_format($row->ucs_incup_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-green border-end" style="color: #6f42c1;">{{ number_format($row->ucs_incup_inc_drug, 2) }}</td>

                                            <!-- UCS INPROV -->
                                            <td class="text-num bg-pastel-indigo">{{ number_format($row->ucs_inprov) }}</td>
                                            <td class="text-num bg-pastel-indigo text-teal">{{ number_format($row->ucs_inprov_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-indigo border-end" style="color: #6f42c1;">{{ number_format($row->ucs_inprov_inc_drug, 2) }}</td>

                                            <!-- UCS OUT -->
                                            <td class="text-num bg-pastel-amber">{{ number_format($row->ucs_outprov) }}</td>
                                            <td class="text-num bg-pastel-amber text-teal">{{ number_format($row->ucs_outprov_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-amber border-end" style="color: #6f42c1;">{{ number_format($row->ucs_outprov_inc_drug, 2) }}</td>

                                            <!-- OFC -->
                                            <td class="text-num bg-pastel-purple">{{ number_format($row->ofc) }}</td>
                                            <td class="text-num bg-pastel-purple text-teal">{{ number_format($row->ofc_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-purple border-end" style="color: #6f42c1;">{{ number_format($row->ofc_inc_drug, 2) }}</td>

                                            <!-- SSS -->
                                            <td class="text-num bg-pastel-rose">{{ number_format($row->sss) }}</td>
                                            <td class="text-num bg-pastel-rose text-teal">{{ number_format($row->sss_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-rose border-end" style="color: #6f42c1;">{{ number_format($row->sss_inc_drug, 2) }}</td>

                                            <!-- LGO -->
                                            <td class="text-num bg-pastel-cyan">{{ number_format($row->lgo) }}</td>
                                            <td class="text-num bg-pastel-cyan text-teal">{{ number_format($row->lgo_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-cyan border-end" style="color: #6f42c1;">{{ number_format($row->lgo_inc_drug, 2) }}</td>

                                            <!-- FSS -->
                                            <td class="text-num bg-pastel-teal">{{ number_format($row->fss) }}</td>
                                            <td class="text-num bg-pastel-teal text-teal">{{ number_format($row->fss_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-teal border-end" style="color: #6f42c1;">{{ number_format($row->fss_inc_drug, 2) }}</td>

                                            <!-- STP -->
                                            <td class="text-num bg-pastel-orange">{{ number_format($row->stp) }}</td>
                                            <td class="text-num bg-pastel-orange text-teal">{{ number_format($row->stp_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-orange border-end" style="color: #6f42c1;">{{ number_format($row->stp_inc_drug, 2) }}</td>

                                            <!-- PAY -->
                                            <td class="text-num bg-pastel-gray">{{ number_format($row->pay) }}</td>
                                            <td class="text-num bg-pastel-gray text-teal">{{ number_format($row->pay_inc_hd, 2) }}</td>
                                            <td class="text-num bg-pastel-gray border-end" style="color: #6f42c1;">{{ number_format($row->pay_inc_drug, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold border-top-2">
                                    <tr>
                                        <td class="sticky-col text-center">รวม</td>
                                        
                                        <!-- All -->
                                        <td class="text-num bg-pastel-blue">{{ number_format(array_sum(array_column($visit_month, 'visit'))) }}</td>
                                        <td class="text-num bg-pastel-blue text-teal">{{ number_format(array_sum(array_column($visit_month, 'inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-blue border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'inc_drug')), 2) }}</td>

                                        <!-- UCS IN -->
                                        <td class="text-num bg-pastel-green">{{ number_format(array_sum(array_column($visit_month, 'ucs_incup'))) }}</td>
                                        <td class="text-num bg-pastel-green text-teal">{{ number_format(array_sum(array_column($visit_month, 'ucs_incup_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-green border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'ucs_incup_inc_drug')), 2) }}</td>

                                        <!-- UCS INPROV -->
                                        <td class="text-num bg-pastel-indigo">{{ number_format(array_sum(array_column($visit_month, 'ucs_inprov'))) }}</td>
                                        <td class="text-num bg-pastel-indigo text-teal">{{ number_format(array_sum(array_column($visit_month, 'ucs_inprov_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-indigo border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'ucs_inprov_inc_drug')), 2) }}</td>

                                        <!-- UCS OUT -->
                                        <td class="text-num bg-pastel-amber">{{ number_format(array_sum(array_column($visit_month, 'ucs_outprov'))) }}</td>
                                        <td class="text-num bg-pastel-amber text-teal">{{ number_format(array_sum(array_column($visit_month, 'ucs_outprov_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-amber border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'ucs_outprov_inc_drug')), 2) }}</td>

                                        <!-- OFC -->
                                        <td class="text-num bg-pastel-purple">{{ number_format(array_sum(array_column($visit_month, 'ofc'))) }}</td>
                                        <td class="text-num bg-pastel-purple text-teal">{{ number_format(array_sum(array_column($visit_month, 'ofc_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-purple border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'ofc_inc_drug')), 2) }}</td>

                                        <!-- SSS -->
                                        <td class="text-num bg-pastel-rose">{{ number_format(array_sum(array_column($visit_month, 'sss'))) }}</td>
                                        <td class="text-num bg-pastel-rose text-teal">{{ number_format(array_sum(array_column($visit_month, 'sss_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-rose border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'sss_inc_drug')), 2) }}</td>

                                        <!-- LGO -->
                                        <td class="text-num bg-pastel-cyan">{{ number_format(array_sum(array_column($visit_month, 'lgo'))) }}</td>
                                        <td class="text-num bg-pastel-cyan text-teal">{{ number_format(array_sum(array_column($visit_month, 'lgo_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-cyan border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'lgo_inc_drug')), 2) }}</td>

                                        <!-- FSS -->
                                        <td class="text-num bg-pastel-teal">{{ number_format(array_sum(array_column($visit_month, 'fss'))) }}</td>
                                        <td class="text-num bg-pastel-teal text-teal">{{ number_format(array_sum(array_column($visit_month, 'fss_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-teal border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'fss_inc_drug')), 2) }}</td>

                                        <!-- STP -->
                                        <td class="text-num bg-pastel-orange">{{ number_format(array_sum(array_column($visit_month, 'stp'))) }}</td>
                                        <td class="text-num bg-pastel-orange text-teal">{{ number_format(array_sum(array_column($visit_month, 'stp_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-orange border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'stp_inc_drug')), 2) }}</td>

                                        <!-- PAY -->
                                        <td class="text-num bg-pastel-gray">{{ number_format(array_sum(array_column($visit_month, 'pay'))) }}</td>
                                        <td class="text-num bg-pastel-gray text-teal">{{ number_format(array_sum(array_column($visit_month, 'pay_inc_hd')), 2) }}</td>
                                        <td class="text-num bg-pastel-gray border-end" style="color: #6f42c1;">{{ number_format(array_sum(array_column($visit_month, 'pay_inc_drug')), 2) }}</td>
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
        <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script>
            $(document).ready(function() {
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
                                const date = instance.selectedDates[0] || new Date(instance.input.value);
                                if (date && !isNaN(date.getTime())) {
                                    const day = date.getDate();
                                    const month = instance.l10n.months.shorthand[date.getMonth()];
                                    const year = date.getFullYear() + yearOffset;
                                    instance.altInput.value = `${day} ${month} ${year}`;
                                }
                            }
                            const container = instance.calendarContainer;
                            if (container && !container.querySelector('.flatpickr-today-button')) {
                                const btn = document.createElement("div");
                                btn.className = "flatpickr-today-button";
                                btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                                btn.addEventListener("mousedown", function(e) {
                                    e.preventDefault();
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
                            $('#budget_year_changed').val('1');
                            var startYear = selectedYear - 544;
                            var endYear = selectedYear - 543;
                            var startDateStr = startYear + "-10-01";
                            var endDateStr = endYear + "-09-30";
                            setTimeout(() => {
                                if (startPicker) startPicker.setDate(startDateStr, true);
                                if (endPicker) endPicker.setDate(endDateStr, true);
                            }, 50);
                        }
                    });
                }

                // Monthly Visit & HN Chart
                const monthlyOptions = {
                    series: [{
                        name: 'จำนวนคน (HN)',
                        data: @json($hns)
                    }, {
                        name: 'จำนวนครั้ง (Visit)',
                        data: @json($visits)
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        toolbar: { show: false }
                    },
                    colors: ['#008080', '#0284c7'],
                    plotOptions: {
                        bar: {
                            columnWidth: '60%',
                            borderRadius: 4
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: { fontSize: '11px', fontWeight: 'bold', colors: ['#fff'] },
                        formatter: val => val > 0 ? val.toLocaleString() : ''
                    },
                    xaxis: {
                        categories: @json($months),
                    },
                    yaxis: {
                        labels: { formatter: val => val.toLocaleString() }
                    },
                    legend: { position: 'top' }
                };

                const monthlyChart = new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions);
                monthlyChart.render();

                // Expenses Chart (HD vs Drug)
                const expensesOptions = {
                    series: [{
                        name: 'ค่าฟอกไต',
                        data: @json($inc_hds)
                    }, {
                        name: 'ค่ายา (EPO)',
                        data: @json($inc_drugs)
                    }],
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: { show: false }
                    },
                    colors: ['#0d9488', '#8b5cf6'],
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    xaxis: {
                        categories: @json($months),
                    },
                    yaxis: {
                        labels: { 
                            formatter: val => '฿' + val.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0}) 
                        }
                    },
                    legend: { position: 'top' },
                    tooltip: {
                        y: {
                            formatter: val => '฿' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})
                        }
                    }
                };

                const expensesChart = new ApexCharts(document.querySelector("#expensesChart"), expensesOptions);
                expensesChart.render();

                // Excel Export
                $('.btn-export-excel').on('click', function() {
                    const target = $(this).data('target');
                    const title = $(this).closest('.card-header').find('h6').text().trim();
                    const dt = $(target).DataTable({
                        destroy: true,
                        paging: false,
                        searching: false,
                        info: false,
                        ordering: false,
                        autoWidth: false,
                        dom: 'tB',
                        buttons: [{
                            extend: 'excelHtml5',
                            title: title,
                            messageTop: 'ข้อมูลการให้บริการฟอกไต HD เอกชน ระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}',
                            filename: title + '_{{ date("Ymd") }}'
                        }]
                    });
                    dt.button(0).trigger();
                    dt.destroy();
                });
            });
        </script>
    @endpush
@endsection
