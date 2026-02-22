@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
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
            width: 200px !important;
        }

        .bg-pastel-blue {
            background-color: #f0f9ff;
        }

        .bg-pastel-green {
            background-color: #f0fdf4;
        }

        .bg-pastel-amber {
            background-color: #fffbeb;
        }

        .bg-pastel-purple {
            background-color: #faf5ff;
        }

        .bg-pastel-rose {
            background-color: #fff1f2;
        }

        .bg-pastel-cyan {
            background-color: #ecfeff;
        }

        .bg-pastel-teal {
            background-color: #f0fdfa;
        }

        .bg-pastel-orange {
            background-color: #fff7ed;
        }

        .bg-pastel-gray {
            background-color: #f8f9fa;
        }

        .card-stats {
            border-radius: 15px;
            transition: transform 0.2s ease, shadow 0.2s ease;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
        }

        .table-opd-stats {
            font-size: 0.85rem;
        }

        .table-opd-stats th {
            vertical-align: middle;
            text-align: center;
            background-color: #e9ecef;
            border-bottom: 2px solid #dee2e6;
        }

        .table-opd-stats td {
            vertical-align: middle;
        }

        .text-num {
            font-family: 'Inter', sans-serif;
            text-align: right;
        }

        .header-group {
            border-bottom: 2px solid #cbd5e1 !important;
            white-space: nowrap;
            padding: 10px 15px !important;
        }

        .table-responsive {
            border-radius: 15px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
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

        .trend-badge-up {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.75rem;
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
                        <i class="fas fa-stethoscope text-primary me-2"></i> {{ $title }}
                    </h4>
                    <div class="text-muted small mt-1">สรุปผลการดำเนินงานและสถิติบริการ ปีงบประมาณ
                        <strong>{{ $budget_year }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0 header-form-controls">
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0"
                            value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-date"
                        style="border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i
                                class="fas fa-calendar-alt"></i></span>
                        <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0"
                            value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm shadow-sm input-group-budget"
                        style="border-radius: 8px; overflow: hidden;">
                        <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;"
                            onchange="document.getElementById('start_date').value=''; document.getElementById('end_date').value='';">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
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

        <!-- Quick Stats Summary Cards -->
        <div class="row g-3 mb-4">
            @php
                $total_visit = array_sum($visits);
                $total_income = array_sum($incomes);
                $avg_visit = count($visits) > 0 ? $total_visit / count($visits) : 0;
            @endphp
            <div class="col-md-4">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-blue p-2 rounded-3">
                                <i class="fas fa-users text-primary fa-lg"></i>
                            </div>
                            <span class="trend-badge-up"><i class="fas fa-chart-line me-1"></i> +5.2%</span>
                        </div>
                        <div class="label-small mb-1">จำนวนผู้รับบริการทั้งหมด</div>
                        <div class="stat-val text-dark">{{ number_format($total_visit) }} <span
                                class="fs-6 fw-normal text-muted">ครั้ง</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-green p-2 rounded-3">
                                <i class="fas fa-hand-holding-usd text-success fa-lg"></i>
                            </div>
                            <span class="trend-badge-up"><i class="fas fa-chart-line me-1"></i> +8.1%</span>
                        </div>
                        <div class="label-small mb-1">รายได้จากการบริการรวม</div>
                        <div class="stat-val text-dark">{{ number_format($total_income, 2) }} <span
                                class="fs-6 fw-normal text-muted">บาท</span></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats border-0 shadow-sm bg-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="bg-pastel-amber p-2 rounded-3">
                                <i class="fas fa-calendar-check text-warning fa-lg"></i>
                            </div>
                        </div>
                        <div class="label-small mb-1">เฉลี่ยต่อเดือน</div>
                        <div class="stat-val text-dark">{{ number_format($avg_visit) }} <span
                                class="fs-6 fw-normal text-muted">ครั้ง</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-4">
            <!-- Monthly Visit & Income Chart -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">กราฟจำนวนผู้รับบริการรายเดือน (แยกข้อมูลรายคน/รายครั้ง)</h5>
                        <p class="text-muted small">เปรียบเทียบจำนวนผู้รับบริการ (HN) และการมารับบริการซ้ำในปีงบประมาณ
                            {{ $budget_year }}</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="monthlyChart" class="chart-container"></div>
                    </div>
                </div>
            </div>

            <!-- Monthly OP-PP Chart -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0">แยกประเภท OP-PP รายเดือน</h5>
                        <p class="text-muted small">สัดส่วนผู้รับบริการทั่วไป (OP) และส่งเสริม (PP)</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div id="opPpChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statistics Table -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center p-4">
                        <div>
                            <h5 class="fw-bold mb-0">ตารางสถิติผู้รับบริการแยกตามกลุ่มสิทธิการรักษาหลัก</h5>
                            <p class="text-muted small mb-0">แสดงจำนวนการรับบริการ (Visit) และรายได้ (Income)
                                แบ่งตามกองทุนหลัก</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="badge bg-info-subtle text-info p-2 rounded-3 border">
                                <i class="fas fa-info-circle me-1"></i> ข้อมูลกรองตามปีงบประมาณ {{ $budget_year }}
                            </div>
                            <button class="btn btn-success btn-sm shadow-sm px-3" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-opd-stats mb-0" id="opdStatsTable">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="align-middle border-end bg-light">เดือน</th>
                                        <th colspan="4" class="header-group bg-pastel-blue text-dark border-end">
                                            ทั้งหมด</th>
                                        <th colspan="4" class="header-group bg-pastel-green text-dark border-end">
                                            ประกันสุขภาพ ใน CUP</th>
                                        <th colspan="4" class="header-group bg-pastel-amber text-dark border-end">
                                            ประกันสุขภาพ นอก CUP</th>
                                        <th colspan="4" class="header-group bg-pastel-purple text-dark border-end">
                                            ข้าราชการ</th>
                                        <th colspan="4" class="header-group bg-pastel-rose text-dark border-end">
                                            ประกันสังคม</th>
                                        <th colspan="4" class="header-group bg-pastel-cyan text-dark border-end">อปท.
                                        </th>
                                        <th colspan="4" class="header-group bg-pastel-teal text-dark border-end">
                                            ต่างด้าว</th>
                                        <th colspan="4" class="header-group bg-pastel-orange text-dark border-end">
                                            Stateless (STP)</th>
                                        <th colspan="4" class="header-group bg-pastel-gray text-dark">ชำระเงิน/อื่นๆ
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="col-visit bg-pastel-blue">Visit</th>
                                        <th class="col-income bg-pastel-blue">Income</th>
                                        <th class="bg-pastel-blue">Drug</th>
                                        <th class="bg-pastel-blue border-end">Lab</th>

                                        <th class="col-visit bg-pastel-green">Visit</th>
                                        <th class="col-income bg-pastel-green">Income</th>
                                        <th class="bg-pastel-green">Drug</th>
                                        <th class="bg-pastel-green border-end">Lab</th>

                                        <th class="col-visit bg-pastel-amber">Visit</th>
                                        <th class="col-income bg-pastel-amber">Income</th>
                                        <th class="bg-pastel-amber">Drug</th>
                                        <th class="bg-pastel-amber border-end">Lab</th>

                                        <th class="col-visit bg-pastel-purple">Visit</th>
                                        <th class="col-income bg-pastel-purple">Income</th>
                                        <th class="bg-pastel-purple">Drug</th>
                                        <th class="bg-pastel-purple border-end">Lab</th>

                                        <th class="col-visit bg-pastel-rose">Visit</th>
                                        <th class="col-income bg-pastel-rose">Income</th>
                                        <th class="bg-pastel-rose">Drug</th>
                                        <th class="bg-pastel-rose border-end">Lab</th>

                                        <th class="col-visit bg-pastel-cyan">Visit</th>
                                        <th class="col-income bg-pastel-cyan">Income</th>
                                        <th class="bg-pastel-cyan">Drug</th>
                                        <th class="bg-pastel-cyan border-end">Lab</th>

                                        <th class="col-visit bg-pastel-teal">Visit</th>
                                        <th class="col-income bg-pastel-teal">Income</th>
                                        <th class="bg-pastel-teal">Drug</th>
                                        <th class="bg-pastel-teal border-end">Lab</th>

                                        <th class="col-visit bg-pastel-orange">Visit</th>
                                        <th class="col-income bg-pastel-orange">Income</th>
                                        <th class="bg-pastel-orange">Drug</th>
                                        <th class="bg-pastel-orange border-end">Lab</th>

                                        <th class="col-visit bg-pastel-gray">Visit</th>
                                        <th class="col-income bg-pastel-gray">Income</th>
                                        <th class="bg-pastel-gray">Drug</th>
                                        <th class="bg-pastel-gray border-end">Lab</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($visit_month as $row)
                                        <tr>
                                            <td class="fw-bold text-center border-end bg-light">{{ $row->month }}</td>
                                            <td class="text-num col-visit bg-pastel-blue fw-bold">
                                                {{ number_format($row->visit) }}</td>
                                            <td class="text-num col-income bg-pastel-blue fw-bold text-success">
                                                {{ number_format($row->income, 2) }}</td>
                                            <td class="text-num bg-pastel-blue">{{ number_format($row->inc_drug, 2) }}
                                            </td>
                                            <td class="text-num bg-pastel-blue border-end">
                                                {{ number_format($row->inc_lab, 2) }}</td>

                                            <td class="text-num bg-pastel-green">{{ number_format($row->ucs_incup) }}</td>
                                            <td class="text-num bg-pastel-green text-success">
                                                {{ number_format($row->ucs_incup_income, 2) }}</td>
                                            <td class="text-num bg-pastel-green">
                                                {{ number_format($row->ucs_incup_inc_drug, 2) }}</td>
                                            <td class="text-num bg-pastel-green border-end">
                                                {{ number_format($row->ucs_incup_inc_lab, 2) }}</td>

                                            <td class="text-num bg-pastel-amber">{{ number_format($row->ucs_outcup) }}
                                            </td>
                                            <td class="text-num bg-pastel-amber text-success">
                                                {{ number_format($row->ucs_outcup_income, 2) }}</td>
                                            <td class="text-num bg-pastel-amber">
                                                {{ number_format($row->ucs_outcup_inc_drug, 2) }}</td>
                                            <td class="text-num bg-pastel-amber border-end">
                                                {{ number_format($row->ucs_outcup_inc_lab, 2) }}</td>

                                            <td class="text-num bg-pastel-purple">{{ number_format($row->ofc) }}</td>
                                            <td class="text-num bg-pastel-purple text-success">
                                                {{ number_format($row->ofc_income, 2) }}</td>
                                            <td class="text-num bg-pastel-purple">
                                                {{ number_format($row->ofc_inc_drug, 2) }}</td>
                                            <td class="text-num bg-pastel-purple border-end">
                                                {{ number_format($row->ofc_inc_lab, 2) }}</td>

                                            <td class="text-num bg-pastel-rose">{{ number_format($row->sss) }}</td>
                                            <td class="text-num bg-pastel-rose text-success">
                                                {{ number_format($row->sss_income, 2) }}</td>
                                            <td class="text-num bg-pastel-rose">{{ number_format($row->sss_inc_drug, 2) }}
                                            </td>
                                            <td class="text-num bg-pastel-rose border-end">
                                                {{ number_format($row->sss_inc_lab, 2) }}</td>

                                            <td class="text-num bg-pastel-cyan">{{ number_format($row->lgo) }}</td>
                                            <td class="text-num bg-pastel-cyan text-success">
                                                {{ number_format($row->lgo_income, 2) }}</td>
                                            <td class="text-num bg-pastel-cyan">{{ number_format($row->lgo_inc_drug, 2) }}
                                            </td>
                                            <td class="text-num bg-pastel-cyan border-end">
                                                {{ number_format($row->lgo_inc_lab, 2) }}</td>

                                            <td class="text-num bg-pastel-teal">{{ number_format($row->fss) }}</td>
                                            <td class="text-num bg-pastel-teal text-success">
                                                {{ number_format($row->fss_income, 2) }}</td>
                                            <td class="text-num bg-pastel-teal">{{ number_format($row->fss_inc_drug, 2) }}
                                            </td>
                                            <td class="text-num bg-pastel-teal border-end">
                                                {{ number_format($row->fss_inc_lab, 2) }}</td>

                                            <td class="text-num bg-pastel-orange">{{ number_format($row->stp) }}</td>
                                            <td class="text-num bg-pastel-orange text-success">
                                                {{ number_format($row->stp_income, 2) }}</td>
                                            <td class="text-num bg-pastel-orange">
                                                {{ number_format($row->stp_inc_drug, 2) }}</td>
                                            <td class="text-num bg-pastel-orange border-end">
                                                {{ number_format($row->stp_inc_lab, 2) }}</td>

                                            <td class="text-num bg-pastel-gray">{{ number_format($row->pay) }}</td>
                                            <td class="text-num bg-pastel-gray text-success">
                                                {{ number_format($row->pay_income, 2) }}</td>
                                            <td class="text-num bg-pastel-gray">{{ number_format($row->pay_inc_drug, 2) }}
                                            </td>
                                            <td class="text-num bg-pastel-gray border-end">
                                                {{ number_format($row->pay_inc_lab, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light fw-bold border-top-2">
                                    <tr>
                                        <td class="text-center">รวม</td>
                                        <td class="text-num bg-pastel-blue">
                                            {{ number_format(array_sum(array_column($visit_month, 'visit'))) }}</td>
                                        <td class="text-num bg-pastel-blue text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'income')), 2) }}</td>
                                        <td class="text-num bg-pastel-blue">
                                            {{ number_format(array_sum(array_column($visit_month, 'inc_drug')), 2) }}</td>
                                        <td class="text-num bg-pastel-blue border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'inc_lab')), 2) }}</td>

                                        <td class="text-num bg-pastel-green">
                                            {{ number_format(array_sum(array_column($visit_month, 'ucs_incup'))) }}</td>
                                        <td class="text-num bg-pastel-green text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'ucs_incup_income')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-green">
                                            {{ number_format(array_sum(array_column($visit_month, 'ucs_incup_inc_drug')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-green border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'ucs_incup_inc_lab')), 2) }}
                                        </td>

                                        <td class="text-num bg-pastel-amber">
                                            {{ number_format(array_sum(array_column($visit_month, 'ucs_outcup'))) }}</td>
                                        <td class="text-num bg-pastel-amber text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'ucs_outcup_income')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-amber">
                                            {{ number_format(array_sum(array_column($visit_month, 'ucs_outcup_inc_drug')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-amber border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'ucs_outcup_inc_lab')), 2) }}
                                        </td>

                                        <td class="text-num bg-pastel-purple">
                                            {{ number_format(array_sum(array_column($visit_month, 'ofc'))) }}</td>
                                        <td class="text-num bg-pastel-purple text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'ofc_income')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-purple">
                                            {{ number_format(array_sum(array_column($visit_month, 'ofc_inc_drug')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-purple border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'ofc_inc_lab')), 2) }}
                                        </td>

                                        <td class="text-num bg-pastel-rose">
                                            {{ number_format(array_sum(array_column($visit_month, 'sss'))) }}</td>
                                        <td class="text-num bg-pastel-rose text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'sss_income')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-rose">
                                            {{ number_format(array_sum(array_column($visit_month, 'sss_inc_drug')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-rose border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'sss_inc_lab')), 2) }}
                                        </td>

                                        <td class="text-num bg-pastel-cyan">
                                            {{ number_format(array_sum(array_column($visit_month, 'lgo'))) }}</td>
                                        <td class="text-num bg-pastel-cyan text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'lgo_income')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-cyan">
                                            {{ number_format(array_sum(array_column($visit_month, 'lgo_inc_drug')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-cyan border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'lgo_inc_lab')), 2) }}
                                        </td>

                                        <td class="text-num bg-pastel-teal">
                                            {{ number_format(array_sum(array_column($visit_month, 'fss'))) }}</td>
                                        <td class="text-num bg-pastel-teal text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'fss_income')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-teal">
                                            {{ number_format(array_sum(array_column($visit_month, 'fss_inc_drug')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-teal border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'fss_inc_lab')), 2) }}
                                        </td>

                                        <td class="text-num bg-pastel-orange">
                                            {{ number_format(array_sum(array_column($visit_month, 'stp'))) }}</td>
                                        <td class="text-num bg-pastel-orange text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'stp_income')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-orange">
                                            {{ number_format(array_sum(array_column($visit_month, 'stp_inc_drug')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-orange border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'stp_inc_lab')), 2) }}
                                        </td>

                                        <td class="text-num bg-pastel-gray">
                                            {{ number_format(array_sum(array_column($visit_month, 'pay'))) }}</td>
                                        <td class="text-num bg-pastel-gray text-success">
                                            {{ number_format(array_sum(array_column($visit_month, 'pay_income')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-gray">
                                            {{ number_format(array_sum(array_column($visit_month, 'pay_inc_drug')), 2) }}
                                        </td>
                                        <td class="text-num bg-pastel-gray border-end">
                                            {{ number_format(array_sum(array_column($visit_month, 'pay_inc_lab')), 2) }}
                                        </td>
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
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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

                    flatpickr("#start_date", commonConfig);
                    flatpickr("#end_date", commonConfig);
                }

                // Monthly Visit & HN Chart Data
                var monthlyOptions = {
                    series: [{
                        name: 'จำนวนคน (HN)',
                        data: @json($hns)
                    }, {
                        name: 'จำนวนครั้ง (Visit)',
                        data: @json($repeat_visits)
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: true,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true
                        }
                    },
                    width: '100%',
                    plotOptions: {
                        bar: {
                            columnWidth: '70%',
                            borderRadius: 4,
                            dataLabels: {
                                position: 'center'
                            }
                        }
                    },
                    colors: ['#6366f1', '#a855f7'], // Indigo and Purple
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '11px',
                            fontWeight: 'bold',
                            colors: ['#fff']
                        },
                        formatter: function(val) {
                            return val > 0 ? val.toLocaleString() : '';
                        }
                    },
                    xaxis: {
                        categories: @json($months),
                        title: {
                            text: 'เดือน'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'จำนวนครั้ง/คน'
                        },
                        labels: {
                            formatter: function(val) {
                                return val.toLocaleString();
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toLocaleString() + " ราย";
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                };

                var monthlyChart = new ApexCharts(document.querySelector("#monthlyChart"), monthlyOptions);
                monthlyChart.render();

                // OP vs PP Chart Data
                var opPpOptions = {
                    series: [{
                        name: 'ทั่วไป (OP)',
                        data: @json($visit_ops)
                    }, {
                        name: 'ส่งเสริม (PP)',
                        data: @json($visit_pps)
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        stacked: true,
                        toolbar: {
                            show: false
                        }
                    },
                    width: '100%',
                    plotOptions: {
                        bar: {
                            columnWidth: '70%',
                            borderRadius: 4,
                            dataLabels: {
                                position: 'center'
                            }
                        }
                    },
                    colors: ['#f97316', '#06b6d4'],
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '11px',
                            fontWeight: 'bold',
                            colors: ['#fff']
                        },
                        formatter: function(val) {
                            return val > 0 ? val.toLocaleString() : '';
                        }
                    },
                    xaxis: {
                        categories: @json($months),
                        title: {
                            text: 'เดือน'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'จำนวนครั้ง'
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toLocaleString() + " ครั้ง";
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    }
                };

                var opPpChart = new ApexCharts(document.querySelector("#opPpChart"), opPpOptions);
                opPpChart.render();
            });

            function exportToExcel() {
                const table = document.getElementById("opdStatsTable");
                let csv = [];
                for (let i = 0; i < table.rows.length; i++) {
                    let row = [],
                        cols = table.rows[i].querySelectorAll("td, th");
                    for (let j = 0; j < cols.length; j++) {
                        let text = cols[j].innerText.replace(/,/g, ""); // Remove commas from numbers
                        row.push('"' + text + '"');
                    }
                    csv.push(row.join(","));
                }
                const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "opd_stats_{{ $budget_year }}.csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    @endpush
@endsection
