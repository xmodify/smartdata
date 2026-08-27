@extends('layouts.app')

@section('title', 'SmartData | RCA ความเสี่ยงทางคลินิก')

@section('topbar_actions')
    <a href="{{ route('backoffice.incident.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <style>
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            color: #6e707e;
            background-color: #f8f9fc;
            margin-right: 2px;
            transition: all 0.2s ease-in-out;
        }
        .nav-tabs .nav-link:hover {
            background-color: #eaecf4;
            color: #4e73df;
            border-color: transparent;
        }
        .nav-tabs .nav-link.active {
            color: #4e73df !important;
            background-color: #fff !important;
            border-color: #dddfeb #dddfeb #fff !important;
            border-bottom: 3px solid #4e73df !important;
        }
        .table-custom {
            border: 1px solid #e3e6f0 !important;
            border-collapse: collapse;
        }
        .table-custom thead th {
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
        .table-custom tbody td {
            padding: 8px 8px !important;
            border-bottom: 1px solid #e3e6f0 !important;
            font-size: 0.8rem;
            color: #4f5d73;
            vertical-align: middle;
        }
        .table-custom tbody tr:hover {
            background-color: #f8fafd !important;
        }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d3e2 !important;
            border-radius: 8px !important;
            padding: 4px 10px !important;
            color: #6e707e !important;
            font-size: 0.85rem !important;
        }
        .section-header-custom {
            background: #f8f9fc;
            border-left: 4px solid #4e73df;
            padding: 8px 12px;
            font-weight: 700;
            color: #4e73df;
            margin-bottom: 15px;
        }
        .form-label-bold {
            font-weight: 700;
            color: #4f5d73;
            font-size: 0.85rem;
        }
        .card-custom-form {
            border: 1px solid #e3e6f0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            margin-bottom: 20px;
        }
        .badge-status {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .person-search-results, #recorder_person_results {
            border: 1px solid #d1d3e2;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.22);
            background: #fff;
            margin-top: 4px;
            z-index: 1080 !important;
            min-width: 320px;
        }
        .person-search-results .list-group-item, #recorder_person_results .list-group-item {
            cursor: pointer;
            border: none;
            border-bottom: 1px solid #f1f3f9;
            padding: 9px 12px;
            transition: all 0.15s ease-in-out;
        }
        .person-search-results .list-group-item:hover, #recorder_person_results .list-group-item:hover {
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .person-search-results .list-group-item:last-child, #recorder_person_results .list-group-item:last-child {
            border-bottom: none;
        }
        .dept-dropdown-menu {
            border: 1px solid #d1d3e2;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.22);
            background: #fff;
        }
        .dept-item {
            font-size: 0.84rem;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dept-item:hover:not(.active-dept) {
            background-color: #f0f4ff;
            color: #2e59d9 !important;
        }
        /* Styles for Print Mode */
        @media print {
            body * {
                visibility: hidden;
            }
            #modalRcaForm, #modalRcaForm * {
                visibility: visible;
            }
            #modalRcaForm {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .modal-header {
                border-bottom: none !important;
            }
            .modal-footer {
                display: none !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-4">
        <!-- Header Box -->
        <div class="page-header-container bg-white rounded-3 shadow-sm border p-4 mb-4 mt-3">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-search-plus text-primary me-2"></i>
                        RCA อุบัติการณ์ทางคลินิก
                    </h5>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-info-circle me-1"></i> เฉพาะอุบัติการณ์รุนแรงระดับ E, F, G, H, I เท่านั้น
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs border-bottom gap-1 mb-3" id="rcaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold px-3 py-2" id="incidents-tab" data-bs-toggle="tab" data-bs-target="#incidents-tab-pane" type="button" role="tab" style="border-radius: 8px 8px 0 0;">
                    <i class="fas fa-file-alt me-1"></i> แบบฟอร์ม RCA
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-3 py-2" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-tab-pane" type="button" role="tab" style="border-radius: 8px 8px 0 0;">
                    <i class="fas fa-chart-bar me-1"></i> Dashboard สรุปผล
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="rcaTabsContent">
            
            <!-- Tab 1: Incidents List to record RCA -->
            <div class="tab-pane fade show active" id="incidents-tab-pane" role="tabpanel" aria-labelledby="incidents-tab" tabindex="0">
                
                <!-- Tab 1: Date & Budget Year Filters Box -->
                <div class="bg-white p-3 rounded-3 shadow-sm border mb-3">
                    <form action="" method="GET" class="m-0">
                        <input type="hidden" name="tab" value="incidents">
                        <div class="row align-items-center g-3">
                            <div class="col-xl-5 col-lg-4 col-md-12">
                                <h6 class="text-dark fw-bold mb-0"><i class="fas fa-filter me-1"></i> ค้นหาอุบัติการณ์เกิดเหตุ</h6>
                                <div class="text-muted small fw-bold mt-1">
                                    ปีงบประมาณ {{ $budget_year }}
                                </div>
                                <div class="text-primary small fw-bold mt-1">
                                    <i class="far fa-calendar-alt me-1"></i> วันที่เกิดอุบัติการณ์: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                                </div>
                            </div>
                            <div class="col-xl-7 col-lg-8 col-md-12">
                                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                                    <span class="fw-bold text-muted small me-1">ช่วงวันที่เกิดอุบัติการณ์:</span>
                                    <div style="width: 140px;">
                                        <input type="text" name="start_date" id="start_date" class="form-control form-control-sm flatpickr-input bg-white" value="{{ $start_date }}" style="border-radius: 6px;">
                                    </div>
                                    <span class="text-muted small">ถึง</span>
                                    <div style="width: 140px;">
                                        <input type="text" name="end_date" id="end_date" class="form-control form-control-sm flatpickr-input bg-white" value="{{ $end_date }}" style="border-radius: 6px;">
                                    </div>
                                    <span class="fw-bold text-muted small ms-2 me-1">ปีงบประมาณ:</span>
                                    <div style="width: 130px;">
                                        <select class="form-select form-select-sm" name="budget_year" style="border-radius: 6px;">
                                            @foreach ($budget_year_select as $row)
                                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                                    {{ $row->LEAVE_YEAR_NAME }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" style="border-radius: 6px; height: 31px;">
                                        <i class="fas fa-search"></i> ค้นหา
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-4 rounded-3 shadow-sm border mb-4">
                    <h6 class="text-dark fw-bold mb-3"><i class="fas fa-list me-1"></i> รายการอุบัติการณ์ (ระดับ E-I)</h6>
                    <div class="table-responsive">
                        <table id="tableRcaIncidents" class="table table-hover table-custom align-middle w-100">
                            <thead>
                                <tr>
                                    <th>รหัสอุบัติการณ์</th>
                                    <th>วันที่เกิดเหตุ</th>
                                    <th>เรื่อง/รายละเอียดอุบัติการณ์</th>
                                    <th class="text-center">ระดับความรุนแรง</th>
                                    <th>หน่วยงานที่เกิดเหตุ</th>
                                    <th>สถานะ RCA</th>
                                    <th class="text-center" style="width: 120px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($incidents as $row)
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $row->RISKREP_ID }}</td>
                                        <td>{{ DateThai($row->RISKREP_STARTDATE) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="text-truncate" style="max-width: 320px;" title="{{ $row->RISKREP_DETAILRISK }}">
                                                    {{ $row->RISKREP_DETAILRISK }}
                                                </div>
                                                <button type="button" class="btn btn-xs btn-outline-primary ms-1 py-0 px-1 border-0" onclick="showIncidentDetail('{{ addslashes(str_replace(["\r", "\n"], ' ', $row->RISKREP_DETAILRISK)) }}')" title="ดูรายละเอียดอุบัติการณ์">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger text-white">{{ $row->severity }}</span>
                                        </td>
                                        <td>{{ $row->department ?? 'ไม่ระบุ' }}</td>
                                        <td>
                                            @if ($row->rca)
                                                @if (($row->rca->rca_status ?? 'pending') === 'completed')
                                                    <span class="badge bg-success text-white badge-status"><i class="fas fa-check-circle me-1"></i>วิเคราะห์เสร็จสิ้น</span>
                                                @else
                                                    <span class="badge bg-warning text-dark badge-status"><i class="fas fa-spinner fa-spin me-1"></i>กำลังดำเนินการ</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary text-white badge-status"><i class="fas fa-clock me-1"></i>ยังไม่ได้ทำ RCA</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row->rca)
                                                <button class="btn btn-sm btn-success fw-bold px-2 py-1" onclick="openRcaModal('{{ $row->RISKREP_ID }}')">
                                                    <i class="fas fa-edit me-1"></i>แก้ไข RCA
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-primary fw-bold px-2 py-1" onclick="openRcaModal('{{ $row->RISKREP_ID }}')">
                                                    <i class="fas fa-plus-circle me-1"></i>ทำ RCA
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Dashboard Statistics -->
            <div class="tab-pane fade" id="dashboard-tab-pane" role="tabpanel" aria-labelledby="dashboard-tab" tabindex="0">
                <!-- Tab 2: Date & Budget Year Filters Box -->
                <div class="bg-white p-3 rounded-3 shadow-sm border mb-3">
                    <form action="" method="GET" class="m-0">
                        <input type="hidden" name="tab" value="dashboard">
                        <div class="row align-items-center g-3">
                            <div class="col-xl-5 col-lg-4 col-md-12">
                                <h5 class="text-dark fw-bold mb-0">
                                    <i class="fas fa-chart-line text-primary me-2"></i> Dashboard สรุปผล RCA อุบัติการณ์ทางคลินิก
                                </h5>
                                <div class="text-muted small fw-bold mt-1">
                                    ปีงบประมาณ {{ $dash_budget_year }}
                                </div>
                                <div class="text-primary small fw-bold mt-1">
                                    <i class="far fa-calendar-alt me-1"></i> วันที่บันทึก RCA: {{ DateThai($dash_start_date) }} ถึง {{ DateThai($dash_end_date) }}
                                </div>
                            </div>
                            <div class="col-xl-7 col-lg-8 col-md-12">
                                <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                                    <span class="fw-bold text-muted small me-1">ช่วงวันที่ RCA:</span>
                                    <div style="width: 140px;">
                                        <input type="text" name="dash_start_date" id="dash_start_date" class="form-control form-control-sm flatpickr-input bg-white" value="{{ $dash_start_date }}" style="border-radius: 6px;">
                                    </div>
                                    <span class="text-muted small">ถึง</span>
                                    <div style="width: 140px;">
                                        <input type="text" name="dash_end_date" id="dash_end_date" class="form-control form-control-sm flatpickr-input bg-white" value="{{ $dash_end_date }}" style="border-radius: 6px;">
                                    </div>
                                    <span class="fw-bold text-muted small ms-2 me-1">ปีงบประมาณ:</span>
                                    <div style="width: 130px;">
                                        <select class="form-select form-select-sm" name="dash_budget_year" style="border-radius: 6px;">
                                            @foreach ($budget_year_select as $row)
                                                <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int) $dash_budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                                    {{ $row->LEAVE_YEAR_NAME }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm" style="border-radius: 6px; height: 31px;">
                                        <i class="fas fa-search"></i> ค้นหา
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Charts Row -->
                <div class="row g-3 mb-4">
                    <!-- Chart 1: Risk Levels -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-exclamation-triangle me-1"></i> จำนวนตามระดับความรุนแรง (Risk Level)</h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 280px; position: relative;">
                                    <canvas id="chartRiskLevel"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Swiss Cheese System Vulnerabilities -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-circle-notch me-1"></i> รอยโหว่ของระบบ (Swiss Cheese Model)</h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 280px; position: relative;">
                                    <canvas id="chartSwissCheese"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 3: Potential Change points -->
                    <div class="col-lg-12">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-line me-1"></i> จุดที่มีโอกาสเปลี่ยนแปลงการตัดสินใจ (Potential Change)</h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 380px; position: relative;">
                                    <canvas id="chartPotentialChange"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Individual Reports Table -->
                <div class="bg-white p-4 rounded-3 shadow-sm border mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-dark fw-bold mb-0"><i class="fas fa-file-invoice me-1"></i> รายงานรายตัว (Individual Reports)</h6>
                        <span class="badge bg-primary px-3 py-2 fs-6">ทั้งหมด {{ count($saved_rcas_query) }} รายการ</span>
                    </div>
                    <div class="table-responsive">
                        <table id="tableSavedRcas" class="table table-hover table-custom align-middle w-100">
                            <thead>
                                <tr>
                                    <th>วันที่บันทึก</th>
                                    <th>AN ผู้ป่วย</th>
                                    <th>เรื่องที่ทำ RCA</th>
                                    <th class="text-center">ระดับความรุนแรง</th>
                                    <th>หน่วยงาน</th>
                                    <th>ผู้บันทึก</th>
                                    <th class="text-center" style="width: 150px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($saved_rcas_query as $rca)
                                    <tr>
                                        <td>{{ DateThai($rca->record_date ?? $rca->created_at) }}</td>
                                        <td>{{ $rca->an ?? 'ไม่ระบุ' }}</td>
                                        <td class="fw-bold">{{ $rca->rca_subject }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">{{ $rca->severity }}</span>
                                        </td>
                                        <td>{{ $rca->department ?? 'ไม่ระบุ' }}</td>
                                        <td>{{ $rca->recorder_name }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary px-2 me-1" onclick="openRcaModal('{{ $rca->incident_id }}')">
                                                <i class="fas fa-edit me-1"></i>แก้ไขข้อมูล
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary px-2" onclick="printRcaForm('{{ $rca->incident_id }}')">
                                                <i class="fas fa-print me-1"></i>พิมพ์
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

    <!-- RCA Wizard Form Modal -->
    <div class="modal fade" id="modalRcaForm" tabindex="-1" aria-labelledby="modalRcaFormLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalRcaFormLabel">
                        <i class="fas fa-search-plus me-1"></i> แบบฟอร์มบันทึก RCA ความเสี่ยงทางคลินิก
                    </h5>
                    <button type="button" class="btn-close btn-close-white no-print" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formRca" enctype="multipart/form-data" method="POST">
                        @csrf
                        <input type="hidden" name="incident_id" id="form_incident_id">
                        
                        <!-- Page Title Header (Print Mode Only) -->
                        <div class="d-none d-print-block text-center mb-4">
                            <h3 class="fw-bold text-dark">RCA ความเสี่ยงทางคลินิกโรงพยาบาลหัวตะพาน</h3>
                            <h5 class="text-muted">งาน PCT โรงพยาบาลหัวตะพาน</h5>
                        </div>

                        <!-- SECTION 1: ข้อมูลทั่วไป (General Information) -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom"><i class="fas fa-info-circle me-1"></i> 1. ข้อมูลทั่วไป (General Information)</div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label-bold">ประเภท RCA</label>
                                        <div class="d-flex gap-4 mt-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="rca_type" id="rca_type_internal" value="RCA ในหน่วยงาน">
                                                <label class="form-check-label text-dark" for="rca_type_internal">RCA ในหน่วยงาน</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="rca_type" id="rca_type_cross" value="RCA ระหว่างหน่วยงาน">
                                                <label class="form-check-label text-dark" for="rca_type_cross">RCA ระหว่างหน่วยงาน</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="rca_type" id="rca_type_committee" value="RCA โดยคณะกรรมการ">
                                                <label class="form-check-label text-dark" for="rca_type_committee">RCA โดยคณะกรรมการ</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label-bold" for="rca_subject">เรื่องที่ทำ RCA</label>
                                        <input type="text" name="rca_subject" id="rca_subject" class="form-control" placeholder="ระบุเรื่อง...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label-bold">ระดับความเสี่ยง (Risk Level)</label>
                                        <div class="d-flex gap-3 mt-2">
                                            @foreach (['E', 'F', 'G', 'H', 'I'] as $l)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="severity" id="severity_{{ $l }}" value="{{ $l }}">
                                                    <label class="form-check-label text-dark fw-bold" for="severity_{{ $l }}">{{ $l }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label-bold">สถานะ Error</label>
                                        <div class="d-flex gap-4 mt-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="error_status" id="error_status_with" value="With error">
                                                <label class="form-check-label text-dark" for="error_status_with">With error</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="error_status" id="error_status_without" value="Without error">
                                                <label class="form-check-label text-dark" for="error_status_without">Without error</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label-bold" for="department">หน่วยงานที่เกิดเหตุ</label>
                                        <input type="text" name="department" id="department" list="departmentList" class="form-control" placeholder="เลือกหรือพิมพ์หน่วยงาน...">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label-bold" for="incident_date">วันที่เกิดเหตุ</label>
                                        <input type="date" name="incident_date" id="incident_date" class="form-control">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label-bold" for="shift">ช่วงเวลาเวร</label>
                                        <select name="shift" id="shift" class="form-select">
                                            <option value="">เลือกเวร...</option>
                                            <option value="เวรเช้า">เวรเช้า (08:00 - 16:00 น.)</option>
                                            <option value="เวรบ่าย">เวรบ่าย (16:00 - 24:00 น.)</option>
                                            <option value="เวรดึก">เวรดึก (00:00 - 08:00 น.)</option>
                                            <option value="นอกเวลา">นอกเวลาราชการ</option>
                                            <option value="ในเวลา">ในเวลาราชการ</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label-bold" for="an">เกิดขึ้นกับผู้ป่วย (AN)</label>
                                        <input type="text" name="an" id="an" class="form-control" placeholder="ระบุ AN ผู้ป่วย...">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label-bold" for="main_risk_topic">ประเด็นความเสี่ยงหลัก</label>
                                        <select name="main_risk_topic" id="main_risk_topic" class="form-select">
                                            <option value="">เลือกประเด็น...</option>
                                            <option value="ความเสี่ยงด้านคลินิกทั่วไป">ความเสี่ยงด้านคลินิกทั่วไป</option>
                                            <option value="ความเสี่ยงด้านคลินิกเฉพาะโรค">ความเสี่ยงด้านคลินิกเฉพาะโรค</option>
                                            <option value="ความผิดพลาดด้านยา">ความผิดพลาดด้านยา</option>
                                            <option value="การระบุตัวผู้ป่วยผิดพลาด">การระบุตัวผู้ป่วยผิดพลาด</option>
                                            <option value="การติดเชื้อในโรงพยาบาล">การติดเชื้อในโรงพยาบาล</option>
                                            <option value="อุบัติเหตุ/การตกเตียง">อุบัติเหตุ/การตกเตียง</option>
                                            <option value="อื่นๆ">อื่นๆ</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: ลำดับเหตุการณ์ (Story & Timeline) -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-history me-1"></i> 2. ลำดับเหตุการณ์ (Story & Timeline)</span>
                                    <button type="button" class="btn btn-sm btn-success no-print" onclick="addTimelineRow()"><i class="fas fa-plus me-1"></i> เพิ่มแถว</button>
                                </div>
                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered table-striped align-middle" id="tableTimelineForm" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 15%;">วันที่</th>
                                                <th style="width: 15%;">เวร/เวลา</th>
                                                <th>Story Timeline</th>
                                                <th style="width: 25%;">แนบไฟล์ (PDF)</th>
                                                <th class="text-center no-print" style="width: 5%;">ลบ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows go here -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label-bold text-danger" for="critical_point"><i class="fas fa-star me-1"></i> จุดเปลี่ยน (Critical Point)</label>
                                    <textarea name="critical_point" id="critical_point" class="form-control" rows="3" placeholder="ระบุจุดเปลี่ยนสำคัญของเหตุการณ์..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: สมาชิกผู้ร่วมวิเคราะห์ (Team Members) -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-users me-1"></i> 1.1 สมาชิกผู้ร่วมวิเคราะห์</span>
                                    <button type="button" class="btn btn-sm btn-success no-print" onclick="addTeamRow()"><i class="fas fa-plus me-1"></i> เพิ่มแถว</button>
                                </div>
                                <div style="overflow: visible;">
                                    <table class="table table-bordered table-striped align-middle" id="tableTeamForm" style="font-size: 0.85rem; width: 100%;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 6%;" class="text-center">ลำดับ</th>
                                                <th style="width: 32%; min-width: 220px;">ชื่อ-สกุล</th>
                                                <th style="width: 22%;">ตำแหน่ง</th>
                                                <th style="width: 22%;">หน่วยงาน</th>
                                                <th style="width: 12%;">ระบบงาน</th>
                                                <th class="text-center no-print" style="width: 6%;">ลบ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows go here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: ขั้นตอนการวิเคราะห์ (Analysis Steps) -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom"><i class="fas fa-search me-1"></i> 1.2 ขั้นตอนการวิเคราะห์</div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label-bold" for="impact_details">ผลกระทบต่อการบริการหรือหน่วยงานใดบ้าง</label>
                                        <textarea name="impact_details" id="impact_details" class="form-control" rows="3" placeholder="ระบุรายละเอียดผลกระทบ..."></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label-bold" for="flowchart_details">ขั้นตอนการปฏิบัติงานที่ออกแบบไว้เป็นอย่างไร (Flow chart)</label>
                                        <textarea name="flowchart_details" id="flowchart_details" class="form-control" rows="3" placeholder="อธิบายขั้นตอนการปฏิบัติงานปกติ หรือวาด Flow chart ย่อ..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 5: จุดที่มีโอกาสเปลี่ยนแปลงการตัดสินใจ (Potential Change) -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom"><i class="fas fa-random me-1"></i> 2. จุดที่มีโอกาสเปลี่ยนแปลงการตัดสินใจ (Potential Change)</div>
                                <label class="form-label-bold mb-2">มีขั้นตอนใดที่เกี่ยวข้องกับเหตุการณ์บ้าง เกิดในขั้นตอนของกระบวนการดูแลผู้ป่วยใด?</label>
                                <div class="row g-3">
                                    @php
                                        $potential_opts = [
                                            'Access', 'Entry', 'Assessment', 'Investigate', 'Diagnosis',
                                            'Plan of care', 'Discharge Plan', 'Reassess', 'Care of patient',
                                            'Communication', 'Info & Empowerment', 'Discharge'
                                        ];
                                    @endphp
                                    @foreach ($potential_opts as $opt)
                                        <div class="col-md-3 col-sm-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="potential_changes[]" value="{{ $opt }}" id="pc_{{ Str::slug($opt) }}">
                                                <label class="form-check-label text-dark text-truncate" for="pc_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 6: เสียงจากผู้ปฏิบัติงาน (Listen to Voice of staff) -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom"><i class="fas fa-comment-dots me-1"></i> 3. เสียงจากผู้ปฏิบัติงาน (Listen to Voice of staff)</div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label-bold" for="staff_voice_1">ข้อมูลจากผู้ที่เกี่ยวข้องด้วยบรรยากาศที่ผู้บอกเล่า</label>
                                        <textarea name="staff_voice_1" id="staff_voice_1" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label-bold" for="staff_voice_2">จุดที่มีโอกาสตัดสินใจหรือกระทำนั้นผู้เกี่ยวข้องเห็นสถานการณ์อย่างไร เห็นอะไร ได้รับข้อมูลอะไร ประเมินสถานการณ์ว่าอย่างไร</label>
                                        <textarea name="staff_voice_2" id="staff_voice_2" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label-bold" for="staff_voice_3">ต้องการความช่วยเหลือและสิ่งอำนวยความสะดวกอะไรบ้าง</label>
                                        <textarea name="staff_voice_3" id="staff_voice_3" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label-bold" for="staff_voice_4">มีความไม่แน่ใจหรือมีสมมติฐานอะไรบ้าง</label>
                                        <textarea name="staff_voice_4" id="staff_voice_4" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 7: Swiss Cheese Model -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom"><i class="fas fa-layer-group me-1"></i> 4. การเปลี่ยนแปลงเชื่อมโยงกับระบบงานสำคัญอะไร? (Swiss Cheese)</div>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-striped align-middle" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 50%;">คำถามกระตุ้นทีมวิเคราะห์</th>
                                                <th class="text-center" style="width: 10%;">ใช่</th>
                                                <th class="text-center" style="width: 10%;">ไม่ใช่</th>
                                                <th>ถ้าใช่ ประเด็น...</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $cheese_qs = [
                                                    1 => 'ผู้ป่วย เกี่ยวข้องหรือไม่ (อาการ, ความรุนแรง, แนวโน้ม, case ซ้ำซ้อน, ขาดความรู้, ญาติ)',
                                                    2 => 'บุคลากร เกี่ยวข้องหรือไม่ (ความรู้, ความสามารถ, ทักษะ, อ่อนล้า, แรงจูงใจ, ทัศนคติ, สุขภาพ, ไม่ปฏิบัติตามแนวทาง)',
                                                    3 => 'งานที่มอบหมาย เกี่ยวข้องหรือไม่ (ฝึกอบรมเพิ่มเติม, อยากเปลี่ยนงาน, มีข้อจำกัด, แนวทางที่รัดกุม, อัตราส่วน...)',
                                                    4 => 'ลักษณะผู้นำ, การสนับสนุน (การดูแลควบคุมงาน, โครงสร้างองค์กร, นโยบายระดับสูง)',
                                                    5 => 'เครื่องมือ เกี่ยวข้องหรือไม่ (ชำรุด, ใช้ไม่เป็น, บำรุงรักษา, ไม่ได้รับการตรวจสอบ, Error บ่อย)',
                                                    6 => 'วัฒนธรรมองค์กร เกี่ยวข้องหรือไม่ (องค์กรเอื้อต่อการแก้ปัญหา, แรงกดดัน, การเงิน, ทิศทาง-นโยบาย)',
                                                    7 => 'สิ่งแวดล้อม เกี่ยวข้องหรือไม่ (แสง, เสียง, โต๊ะ-เก้าอี้ไม่เหมาะสม, ความปลอดภัย)',
                                                    8 => 'การสื่อสาร เกี่ยวข้องหรือไม่ (คู่มือ, การสื่อสารไม่ทั่วถึง, แนวทางไม่ชัดเจน, ไม่สื่อสาร, การสื่อสารระหว่างหน่วยงาน)',
                                                    9 => 'ปัจจัยที่ควบคุมไม่ได้ (เช่น พายุ, ภัยธรรมชาติ)'
                                                ];
                                            @endphp
                                            @foreach ($cheese_qs as $qi => $qtxt)
                                                <tr>
                                                    <td><strong>{{ $qi }}.</strong> {{ $qtxt }}</td>
                                                    <td class="text-center">
                                                        <input class="form-check-input" type="radio" name="swiss_cheese[q{{ $qi }}][yes_no]" value="yes" id="sc_yes_{{ $qi }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <input class="form-check-input" type="radio" name="swiss_cheese[q{{ $qi }}][yes_no]" value="no" id="sc_no_{{ $qi }}" checked>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="swiss_cheese[q{{ $qi }}][detail]" class="form-control form-control-sm" placeholder="ระบุประเด็น...">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label class="form-label-bold mb-2">ระบบงานสำคัญที่เชื่อมโยงกับเหตุการณ์</label>
                                    <div class="row g-2">
                                        @foreach (['IC', 'ENV', 'HRD', 'RM', 'IM', 'NSO', 'PTC', 'PCT', 'MSO', 'อื่นๆ'] as $sys)
                                            <div class="col-md-2 col-sm-4 col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="related_systems[]" value="{{ $sys }}" id="sys_{{ $sys }}">
                                                    <label class="form-check-label text-dark" for="sys_{{ $sys }}">{{ $sys }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 8: ออกแบบระบบงานใหม่ (Creative Solution) -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-lightbulb me-1"></i> ออกแบบระบบงานใหม่ (Creative Solution) - ผลที่ได้จากการทำ RCA</span>
                                    <button type="button" class="btn btn-sm btn-success no-print" onclick="addSolutionRow()"><i class="fas fa-plus me-1"></i> เพิ่มแถว</button>
                                </div>
                                <div style="overflow: visible;">
                                    <table class="table table-bordered table-striped align-middle" id="tableSolutionForm" style="font-size: 0.85rem; width: 100%;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 18%;">รากของปัญหา (Root Cause)</th>
                                                <th style="width: 16%;">กระบวนการ</th>
                                                <th style="width: 22%;">หน่วยงานรับผิดชอบ</th>
                                                <th style="width: 10%;">ระบบ</th>
                                                <th style="width: 20%;">การปรับปรุง/ออกแบบงานใหม่</th>
                                                <th style="width: 10%;">ตัวชี้วัด/ความถี่</th>
                                                <th class="text-center no-print" style="width: 4%;">ลบ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Dynamic Rows go here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 9: ข้อมูลผู้บันทึกแบบฟอร์ม (Form Recorder Info) -->
                        <div class="card card-custom-form">
                            <div class="card-body">
                                <div class="section-header-custom"><i class="fas fa-user-edit me-1"></i> ข้อมูลผู้บันทึกแบบฟอร์ม</div>
                                <div class="row g-3">
                                    <div class="col-md-4 position-relative">
                                        <label class="form-label-bold" for="recorder_name">ชื่อ-สกุล ผู้บันทึก</label>
                                        <input type="text" name="recorder_name" id="recorder_name" class="form-control" placeholder="พิมพ์ค้นหาชื่อ หรือกรอกเอง..." autocomplete="off">
                                        <div id="recorder_person_results" class="list-group position-absolute w-100 shadow-lg" style="display: none; z-index: 1060; max-height: 200px; overflow-y: auto;"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-bold" for="recorder_position">ตำแหน่ง</label>
                                        <input type="text" name="recorder_position" id="recorder_position" class="form-control" placeholder="ตำแหน่ง...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label-bold" for="record_date">วันที่บันทึก</label>
                                        <input type="date" name="record_date" id="record_date" class="form-control">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label-bold" for="rca_status">สถานะแบบฟอร์ม RCA</label>
                                        <select name="rca_status" id="rca_status" class="form-select">
                                            <option value="pending">กำลังดำเนินการ (Pending)</option>
                                            <option value="completed">วิเคราะห์เสร็จสิ้น (Completed)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light no-print">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> ปิด</button>
                    <button type="button" class="btn btn-warning px-3" onclick="resetRcaForm()"><i class="fas fa-undo me-1"></i> ล้างข้อมูล</button>
                    <button type="button" class="btn btn-success px-4" onclick="submitRcaForm()"><i class="fas fa-save me-1"></i> บันทึกข้อมูล RCA</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Datalist for Backoffice Departments -->
    <datalist id="departmentList">
        @if(isset($departments) && count($departments) > 0)
            @foreach($departments as $dept)
                <option value="{{ $dept }}">
            @endforeach
        @endif
    </datalist>

    <!-- Incident Detail View Modal -->
    <div class="modal fade" id="modalIncidentDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-file-alt text-primary me-2"></i>รายละเอียดอุบัติการณ์</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="incident_full_text" class="text-dark" style="white-space: pre-line; line-height: 1.6; font-size: 0.9rem;"></p>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
    
    <script>
        const backofficeDepartments = @json($departments ?? []);
        const yearOffset = 543;
        const thaiMonths = ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
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
                        const date = instance.selectedDates[0] || (instance.input.value ? new Date(instance.input.value) : null);
                        if (date && !isNaN(date.getTime())) {
                            const day = date.getDate();
                            const month = (instance.l10n && instance.l10n.months && instance.l10n.months.shorthand) 
                                          ? instance.l10n.months.shorthand[date.getMonth()] 
                                          : thaiMonths[date.getMonth()];
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
                        const month = (instance.l10n && instance.l10n.months && instance.l10n.months.shorthand) 
                                      ? instance.l10n.months.shorthand[date.getMonth()] 
                                      : thaiMonths[date.getMonth()];
                        const year = date.getFullYear() + yearOffset;
                        instance.altInput.value = `${day} ${month} ${year}`;
                    }, 10);
                }
            }
        };

        $(document).ready(function() {
            // Date Pickers setup matching nrls date picker format
            if (typeof flatpickr !== 'undefined') {
                flatpickr("#start_date", commonConfig);
                flatpickr("#end_date", commonConfig);
                flatpickr("#dash_start_date", commonConfig);
                flatpickr("#dash_end_date", commonConfig);
                flatpickr("#incident_date", commonConfig);
                flatpickr("#record_date", commonConfig);
            }

            // Initialize Datatables
            $('#tableRcaIncidents').DataTable({
                "language": {
                    "search": "ค้นหาอุบัติการณ์:",
                    "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                    "info": "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    "paginate": {
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                },
                "order": [[1, "desc"]]
            });

            $('#tableSavedRcas').DataTable({
                "language": {
                    "search": "ค้นหารายงาน RCA:",
                    "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                    "info": "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    "paginate": {
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                },
                "order": [[0, "desc"]]
            });

            // Initialize Charts if Tab 2 is active or loaded
            initDashboardCharts();
            
            // Initialize Recorder Person Search
            initRecorderPersonSearch();
            
            // Switch tabs helper to check URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam === 'dashboard') {
                $('#dashboard-tab').tab('show');
                $('#active_tab_input').val('dashboard');
            } else {
                $('#active_tab_input').val('incidents');
            }

            // Sync active tab state to hidden input
            $('#rcaTabs button').on('shown.bs.tab', function (e) {
                const targetId = $(e.target).attr('id');
                if (targetId === 'dashboard-tab') {
                    $('#active_tab_input').val('dashboard');
                } else {
                    $('#active_tab_input').val('incidents');
                }
            });

            // Update Tab 1 start_date and end_date on budget_year change
            $('select[name="budget_year"]').on('change', function() {
                var selectedYear = parseInt($(this).val());
                if(!isNaN(selectedYear)) {
                    var startYear = selectedYear - 544;
                    var endYear = selectedYear - 543;
                    var startDateStr = startYear + "-10-01";
                    var endDateStr = endYear + "-09-30";
                    setTimeout(() => {
                        const startEl = document.querySelector("#start_date");
                        const endEl = document.querySelector("#end_date");
                        if (startEl && startEl._flatpickr) startEl._flatpickr.setDate(startDateStr, true);
                        if (endEl && endEl._flatpickr) endEl._flatpickr.setDate(endDateStr, true);
                    }, 50);
                }
            });

            // Update Tab 2 dash_start_date and dash_end_date on dash_budget_year change
            $('select[name="dash_budget_year"]').on('change', function() {
                var selectedYear = parseInt($(this).val());
                if(!isNaN(selectedYear)) {
                    var startYear = selectedYear - 544;
                    var endYear = selectedYear - 543;
                    var startDateStr = startYear + "-10-01";
                    var endDateStr = endYear + "-09-30";
                    setTimeout(() => {
                        const startEl = document.querySelector("#dash_start_date");
                        const endEl = document.querySelector("#dash_end_date");
                        if (startEl && startEl._flatpickr) startEl._flatpickr.setDate(startDateStr, true);
                        if (endEl && endEl._flatpickr) endEl._flatpickr.setDate(endDateStr, true);
                    }, 50);
                }
            });
        });

        // Dashboard Charts Initialization
        let riskChart, swissChart, potentialChart;

        function initDashboardCharts() {
            const levelCounts = @json($level_counts);
            const swissLabels = @json($swiss_labels);
            const swissCounts = @json($swiss_counts);
            const potentialLabels = @json($potential_labels);
            const potentialCounts = @json($potential_counts);

            // Chart 1: Risk Levels (Bar Chart)
            const ctxRisk = document.getElementById('chartRiskLevel').getContext('2d');
            if (riskChart) riskChart.destroy();
            riskChart = new Chart(ctxRisk, {
                type: 'bar',
                data: {
                    labels: Object.keys(levelCounts),
                    datasets: [{
                        label: 'จำนวนอุบัติการณ์',
                        data: Object.values(levelCounts),
                        backgroundColor: [
                            '#ffe082', // E
                            '#ffb74d', // F
                            '#ff8a65', // G
                            '#b39ddb', // H
                            '#80deea'  // I
                        ],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });

            // Chart 2: Swiss Cheese Vulnerabilities (Donut Chart)
            const ctxSwiss = document.getElementById('chartSwissCheese').getContext('2d');
            if (swissChart) swissChart.destroy();
            swissChart = new Chart(ctxSwiss, {
                type: 'doughnut',
                data: {
                    labels: swissLabels,
                    datasets: [{
                        data: swissCounts,
                        backgroundColor: [
                            '#ff6384', '#36a2eb', '#cc65fe', '#ffce56',
                            '#2ecc71', '#e67e22', '#95a5a6', '#9b59b6', '#34495e'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 10 } } }
                    }
                }
            });

            // Chart 3: Potential Change Points (Radar Chart)
            const ctxPotential = document.getElementById('chartPotentialChange').getContext('2d');
            if (potentialChart) potentialChart.destroy();
            potentialChart = new Chart(ctxPotential, {
                type: 'radar',
                data: {
                    labels: potentialLabels,
                    datasets: [{
                        label: 'ความถี่จุดวิเคราะห์ความเสี่ยงที่เกิดเหตุ',
                        data: potentialCounts,
                        backgroundColor: 'rgba(78, 115, 223, 0.2)',
                        borderColor: '#4e73df',
                        pointBackgroundColor: '#4e73df',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4e73df'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: { display: true },
                            suggestedMin: 0,
                            suggestedMax: 5
                        }
                    }
                }
            });
        }

        // Dynamic Table Row Handlers
        let timelineRowIndex = 0;
        function addTimelineRow(data = null) {
            const tableBody = document.querySelector('#tableTimelineForm tbody');
            const rowId = timelineRowIndex++;
            const dateVal = data && data.date && data.date !== 'null' ? data.date : '';
            const timeVal = data && data.time && data.time !== 'null' ? data.time : '';
            const storyVal = data && data.story && data.story !== 'null' ? data.story : '';
            let fileLink = '';
            if (data && data.file_path) {
                const fileNameOnly = data.file_path.split('/').pop();
                const fileUrl = "{{ route('backoffice.incident.rca.file', ':filename') }}".replace(':filename', encodeURIComponent(fileNameOnly));
                fileLink = `<a href="${fileUrl}" target="_blank" class="btn btn-xs btn-outline-info p-1"><i class="fas fa-file-pdf"></i> ไฟล์แนบเดิม</a><input type="hidden" name="timeline[${rowId}][existing_file]" value="${data.file_path}">`;
            }

            const rowHtml = `
                <tr id="timeline-row-${rowId}">
                    <td><input type="text" name="timeline[${rowId}][date]" class="form-control form-control-sm timeline-date-picker" value="${dateVal}"></td>
                    <td><input type="text" name="timeline[${rowId}][time]" class="form-control form-control-sm" placeholder="เวร/เวลา..." value="${timeVal}"></td>
                    <td><textarea name="timeline[${rowId}][story]" class="form-control form-control-sm" rows="1">${storyVal}</textarea></td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <input type="file" name="timeline[${rowId}][file]" class="form-control form-control-sm" accept="application/pdf">
                            ${fileLink}
                        </div>
                    </td>
                    <td class="text-center no-print">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow('timeline-row-${rowId}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);

            // Initialize flatpickr on the newly added row's date field
            if (typeof flatpickr !== 'undefined' && typeof commonConfig !== 'undefined') {
                flatpickr(`#timeline-row-${rowId} .timeline-date-picker`, commonConfig);
            }
        }

        let teamRowIndex = 0;
        function addTeamRow(data = null) {
            const tableBody = document.querySelector('#tableTeamForm tbody');
            const rowId = teamRowIndex++;
            const nameVal = data && data.name && data.name !== 'null' ? data.name : '';
            const posVal = data && data.position && data.position !== 'null' ? data.position : '';
            const deptVal = data && data.department && data.department !== 'null' ? data.department : '';
            const sysVal = data && data.work_system && data.work_system !== 'null' ? data.work_system : '';
            const count = tableBody.rows.length + 1;

            const rowHtml = `
                <tr id="team-row-${rowId}">
                    <td class="text-center fw-bold team-no">${count}</td>
                    <td>
                        <div class="position-relative">
                            <input type="text" name="team_members[${rowId}][name]" class="form-control form-control-sm team-person-search" value="${nameVal}" placeholder="พิมพ์ค้นหาชื่อ..." autocomplete="off">
                            <div class="list-group position-absolute w-100 shadow-lg person-search-results" style="display: none; z-index: 1060; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </td>
                    <td><input type="text" name="team_members[${rowId}][position]" class="form-control form-control-sm team-person-position" value="${posVal}" placeholder="ตำแหน่ง..."></td>
                    <td><input type="text" name="team_members[${rowId}][department]" list="departmentList" class="form-control form-control-sm team-person-department" value="${deptVal}" placeholder="หน่วยงาน..."></td>
                    <td><input type="text" name="team_members[${rowId}][work_system]" class="form-control form-control-sm" value="${sysVal}" placeholder="ระบบงาน (เช่น IC, RM...)"></td>
                    <td class="text-center no-print">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTeamRow('team-row-${rowId}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
            initTeamPersonSearch(rowId);
        }

        function removeTeamRow(rowId) {
            document.getElementById(rowId).remove();
            // Re-index row numbers
            document.querySelectorAll('#tableTeamForm tbody tr').forEach((tr, index) => {
                tr.querySelector('.team-no').textContent = index + 1;
            });
        }

        let solutionRowIndex = 0;
        function addSolutionRow(data = null) {
            const tableBody = document.querySelector('#tableSolutionForm tbody');
            const rowId = solutionRowIndex++;
            const rcVal = data && data.root_cause && data.root_cause !== 'null' ? data.root_cause : '';
            const procVal = data && data.process && data.process !== 'null' ? data.process : '';
            const deptVal = data && data.department && data.department !== 'null' ? data.department : '';
            const sysVal = data && data.system && data.system !== 'null' ? data.system : '';
            const impVal = data && data.improvement && data.improvement !== 'null' ? data.improvement : '';
            const freqVal = data && data.frequency && data.frequency !== 'null' ? data.frequency : '';

            // Generate department items list from Backoffice
            let departmentItemsHtml = `
                <div class="dept-item py-1 px-2 rounded cursor-pointer ${!deptVal ? 'active-dept bg-primary text-white' : 'text-dark'}" data-value="">
                    <i class="fas fa-undo me-1 opacity-75"></i> -- ไม่ระบุหน่วยงาน --
                </div>
            `;
            let foundMatch = false;
            if (Array.isArray(backofficeDepartments)) {
                backofficeDepartments.forEach(d => {
                    const isSelected = (deptVal === d);
                    if (isSelected) foundMatch = true;
                    departmentItemsHtml += `
                        <div class="dept-item py-1 px-2 rounded cursor-pointer ${isSelected ? 'active-dept bg-primary text-white' : 'text-dark'}" data-value="${d}">
                            ${d}
                        </div>
                    `;
                });
            }
            if (deptVal && !foundMatch) {
                departmentItemsHtml += `
                    <div class="dept-item py-1 px-2 rounded cursor-pointer active-dept bg-primary text-white" data-value="${deptVal}">
                        ${deptVal}
                    </div>
                `;
            }

            const rowHtml = `
                <tr id="solution-row-${rowId}">
                    <td><textarea name="creative_solutions[${rowId}][root_cause]" class="form-control form-control-sm" rows="2" placeholder="ระบุรากของปัญหา...">${rcVal}</textarea></td>
                    <td><textarea name="creative_solutions[${rowId}][process]" class="form-control form-control-sm" rows="2" placeholder="กระบวนการ...">${procVal}</textarea></td>
                    <td>
                        <div class="dropdown custom-dept-select position-relative" id="dept-select-wrap-${rowId}">
                            <input type="hidden" name="creative_solutions[${rowId}][department]" class="dept-hidden-val" value="${deptVal}">
                            <button type="button" class="form-select form-select-sm text-start dept-toggle-btn d-flex justify-content-between align-items-center" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <span class="dept-label-text text-truncate">${deptVal ? deptVal : '-- เลือกหน่วยงาน --'}</span>
                            </button>
                            <div class="dropdown-menu p-2 shadow-lg dept-dropdown-menu" style="min-width: 280px; max-width: 320px; z-index: 1080;">
                                <div class="mb-2">
                                    <input type="text" class="form-control form-control-sm dept-search-input" placeholder="ค้นหาแผนก/หน่วยงาน..." autocomplete="off">
                                </div>
                                <div class="dept-items-list" style="max-height: 220px; overflow-y: auto;">
                                    ${departmentItemsHtml}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><input type="text" name="creative_solutions[${rowId}][system]" class="form-control form-control-sm" value="${sysVal}" placeholder="เช่น IC, PTC..."></td>
                    <td><textarea name="creative_solutions[${rowId}][improvement]" class="form-control form-control-sm" rows="2" placeholder="แนวทางปรับปรุง...">${impVal}</textarea></td>
                    <td><input type="text" name="creative_solutions[${rowId}][frequency]" class="form-control form-control-sm" value="${freqVal}" placeholder="เช่น ทุก 1 เดือน..."></td>
                    <td class="text-center no-print">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow('solution-row-${rowId}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
            initDeptSelect(rowId);
        }

        // Initialize searchable single-select department dropdown for a row
        function initDeptSelect(rowId) {
            const wrap = document.getElementById(`dept-select-wrap-${rowId}`);
            if (!wrap) return;

            const searchInput = wrap.querySelector('.dept-search-input');
            const toggleBtn = wrap.querySelector('.dept-toggle-btn');
            const hiddenVal = wrap.querySelector('.dept-hidden-val');
            const labelText = wrap.querySelector('.dept-label-text');
            const items = wrap.querySelectorAll('.dept-item');

            // Search filter in real time
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const q = this.value.toLowerCase().trim();
                    items.forEach(item => {
                        const val = (item.getAttribute('data-value') || '').toLowerCase();
                        const txt = item.textContent.toLowerCase();
                        if (!q || val.includes(q) || txt.includes(q)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            // Single item click selection
            items.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const val = this.getAttribute('data-value') || '';
                    hiddenVal.value = val;
                    labelText.textContent = val ? val : '-- เลือกหน่วยงาน --';

                    items.forEach(el => {
                        el.classList.remove('active-dept', 'bg-primary', 'text-white');
                        el.classList.add('text-dark');
                    });
                    this.classList.add('active-dept', 'bg-primary', 'text-white');
                    this.classList.remove('text-dark');

                    // Close dropdown
                    if (window.bootstrap && bootstrap.Dropdown) {
                        const dd = bootstrap.Dropdown.getInstance(toggleBtn) || new bootstrap.Dropdown(toggleBtn);
                        if (dd) dd.hide();
                    } else if (window.$) {
                        $(toggleBtn).dropdown('hide');
                    }
                });
            });

            // Focus search input when dropdown opens
            if (toggleBtn) {
                toggleBtn.addEventListener('shown.bs.dropdown', function() {
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                        items.forEach(el => el.style.display = 'block');
                    }
                });
            }
        }

        function removeRow(rowId) {
            document.getElementById(rowId).remove();
        }

        // Safe Bootstrap Modal display helper
        function showBsModal(modalId) {
            const modalEl = document.getElementById(modalId);
            if (!modalEl) return;
            try {
                if (window.bootstrap && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getOrCreateInstance ? 
                                  bootstrap.Modal.getOrCreateInstance(modalEl) : 
                                  (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl));
                    modal.show();
                } else if (window.$ && $.fn.modal) {
                    $(modalEl).modal('show');
                }
            } catch (err) {
                console.error('Error showing modal:', err);
                if (window.$ && $.fn.modal) {
                    $(modalEl).modal('show');
                }
            }
        }

        // Open RCA Modal Form & Populate existing values
        function openRcaModal(incidentId) {
            // Reset form first
            resetRcaForm();
            
            // Set incident ID
            const incidentInput = document.getElementById('form_incident_id');
            if (incidentInput) incidentInput.value = incidentId;

            // Open the modal immediately
            showBsModal('modalRcaForm');

            // Load details using AJAX
            const detailUrl = "{{ route('backoffice.incident.rca.detail', ':id') }}".replace(':id', incidentId);
            $.ajax({
                url: detailUrl,
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        try {
                            const inc = res.incident || {};
                            const rca = res.rca || null;

                            // Pre-populate fields from backoffice incident
                            const deptEl = document.getElementById('department');
                            if (deptEl) deptEl.value = (rca && rca.department) ? rca.department : (inc.department || '');

                            const incDateEl = document.getElementById('incident_date');
                            if (incDateEl) {
                                const iDate = (rca && rca.incident_date) ? rca.incident_date : (inc.RISKREP_STARTDATE || '');
                                incDateEl.value = iDate;
                                if (incDateEl._flatpickr) incDateEl._flatpickr.setDate(iDate, true);
                            }

                            const anEl = document.getElementById('an');
                            if (anEl) anEl.value = (rca && rca.an) ? rca.an : (inc.an || '');
                            
                            // Set severity level
                            const sev = (rca && rca.severity) ? rca.severity : inc.severity;
                            if (sev) {
                                const sevRadio = document.getElementById(`severity_${sev}`);
                                if (sevRadio) sevRadio.checked = true;
                            }

                            // Pre-populate fields from saved RCA record
                            if (rca) {
                                // RCA Type radio
                                if (rca.rca_type) {
                                    const typeRadio = document.querySelector(`input[name="rca_type"][value="${rca.rca_type}"]`);
                                    if (typeRadio) typeRadio.checked = true;
                                }
                                
                                const subjEl = document.getElementById('rca_subject');
                                if (subjEl) subjEl.value = rca.rca_subject || '';
                                
                                // Error Status radio
                                if (rca.error_status) {
                                    const errRadio = document.querySelector(`input[name="error_status"][value="${rca.error_status}"]`);
                                    if (errRadio) errRadio.checked = true;
                                }
                                
                                const shiftEl = document.getElementById('shift');
                                if (shiftEl && rca.shift) shiftEl.value = rca.shift;

                                const mainTopicEl = document.getElementById('main_risk_topic');
                                if (mainTopicEl && rca.main_risk_topic) mainTopicEl.value = rca.main_risk_topic;

                                const cpEl = document.getElementById('critical_point');
                                if (cpEl) cpEl.value = rca.critical_point || '';

                                const impEl = document.getElementById('impact_details');
                                if (impEl) impEl.value = rca.impact_details || '';

                                const fcEl = document.getElementById('flowchart_details');
                                if (fcEl) fcEl.value = rca.flowchart_details || '';

                                const sv1 = document.getElementById('staff_voice_1');
                                if (sv1) sv1.value = rca.staff_voice_1 || '';

                                const sv2 = document.getElementById('staff_voice_2');
                                if (sv2) sv2.value = rca.staff_voice_2 || '';

                                const sv3 = document.getElementById('staff_voice_3');
                                if (sv3) sv3.value = rca.staff_voice_3 || '';

                                const sv4 = document.getElementById('staff_voice_4');
                                if (sv4) sv4.value = rca.staff_voice_4 || '';
                                
                                const recName = document.getElementById('recorder_name');
                                if (recName) recName.value = rca.recorder_name || '';

                                const recPos = document.getElementById('recorder_position');
                                if (recPos) recPos.value = rca.recorder_position || '';

                                const recDate = document.getElementById('record_date');
                                if (recDate) {
                                    recDate.value = rca.record_date || '';
                                    if (recDate._flatpickr) recDate._flatpickr.setDate(rca.record_date || '', true);
                                }

                                const rcaStat = document.getElementById('rca_status');
                                if (rcaStat) rcaStat.value = rca.rca_status || 'pending';

                                // Populate dynamic Timeline rows
                                if (Array.isArray(rca.timeline) && rca.timeline.length > 0) {
                                    rca.timeline.forEach(row => addTimelineRow(row));
                                } else {
                                    addTimelineRow();
                                }
                                
                                // Populate dynamic Team Member rows
                                if (Array.isArray(rca.team_members) && rca.team_members.length > 0) {
                                    rca.team_members.forEach(row => addTeamRow(row));
                                } else {
                                    addTeamRow();
                                }
                                
                                // Populate dynamic Solution rows
                                if (Array.isArray(rca.creative_solutions) && rca.creative_solutions.length > 0) {
                                    rca.creative_solutions.forEach(row => addSolutionRow(row));
                                } else {
                                    addSolutionRow();
                                }

                                // Check Potential Changes checkboxes
                                if (Array.isArray(rca.potential_changes)) {
                                    rca.potential_changes.forEach(opt => {
                                        const cb = document.querySelector(`input[name="potential_changes[]"][value="${opt}"]`);
                                        if (cb) cb.checked = true;
                                    });
                                }

                                // Populate Swiss Cheese table
                                if (rca.swiss_cheese && typeof rca.swiss_cheese === 'object') {
                                    Object.keys(rca.swiss_cheese).forEach(qk => {
                                        const idx = qk.replace('q', '');
                                        const item = rca.swiss_cheese[qk];
                                        if (item && item.yes_no) {
                                            const radio = document.querySelector(`input[name="swiss_cheese[q${idx}][yes_no]"][value="${item.yes_no}"]`);
                                            if (radio) radio.checked = true;
                                        }
                                        if (item && item.detail) {
                                            const txt = document.querySelector(`input[name="swiss_cheese[q${idx}][detail]"]`);
                                            if (txt) txt.value = item.detail || '';
                                        }
                                    });
                                }

                                // Check Related Systems checkboxes
                                if (Array.isArray(rca.related_systems)) {
                                    rca.related_systems.forEach(sys => {
                                        const cb = document.getElementById(`sys_${sys}`);
                                        if (cb) cb.checked = true;
                                    });
                                }
                            } else {
                                // If no RCA saved, add default blank rows
                                addTimelineRow();
                                addTeamRow();
                                addSolutionRow();
                                
                                const recDate = document.getElementById('record_date');
                                const todayStr = new Date().toISOString().substring(0, 10);
                                if (recDate) {
                                    recDate.value = todayStr;
                                    if (recDate._flatpickr) recDate._flatpickr.setDate(todayStr, true);
                                }
                            }
                        } catch (err) {
                            console.error('Error populating RCA form:', err);
                        }
                    } else {
                        Swal.fire('ข้อผิดพลาด', res.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        }

        // Reset RCA Form values
        function resetRcaForm() {
            document.getElementById('formRca').reset();
            document.querySelector('#tableTimelineForm tbody').innerHTML = '';
            document.querySelector('#tableTeamForm tbody').innerHTML = '';
            document.querySelector('#tableSolutionForm tbody').innerHTML = '';
            
            // Clear checked severity & error
            document.querySelectorAll('input[name="severity"]').forEach(r => r.checked = false);
            document.querySelectorAll('input[name="error_status"]').forEach(r => r.checked = false);
            document.querySelectorAll('input[name="rca_type"]').forEach(r => r.checked = false);
            document.querySelectorAll('input[name="potential_changes[]"]').forEach(cb => cb.checked = false);
            document.querySelectorAll('input[name="related_systems[]"]').forEach(cb => cb.checked = false);
            
            // Reset Swiss cheese Qs to "no"
            for (let i = 1; i <= 9; i++) {
                const noRadio = document.getElementById(`sc_no_${i}`);
                if (noRadio) noRadio.checked = true;
            }
        }

        // AJAX Form Submission
        function submitRcaForm() {
            // Form validation
            const subject = document.getElementById('rca_subject').value.trim();
            const recorder = document.getElementById('recorder_name').value.trim();
            if (!subject) {
                Swal.fire('แจ้งเตือน', 'กรุณาระบุเรื่องที่ทำ RCA', 'warning');
                return;
            }
            if (!recorder) {
                Swal.fire('แจ้งเตือน', 'กรุณาระบุชื่อผู้บันทึกแบบฟอร์ม', 'warning');
                return;
            }

            const formElement = document.getElementById('formRca');
            const formData = new FormData(formElement);

            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('backoffice.incident.rca.save') }}",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    Swal.close();
                    if (res.success) {
                        Swal.fire('สำเร็จ', res.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('ข้อผิดพลาด', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire('ข้อผิดพลาด', 'เกิดปัญหาขณะบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง', 'error');
                }
            });
        }

        // Local Print Trigger
        function printRcaForm(incidentId) {
            // Load detail first
            openRcaModal(incidentId);
            
            // Wait for modal to show and populate before printing
            setTimeout(() => {
                window.print();
            }, 1000);
        }

        // Show Full Incident Detail Modal
        function showIncidentDetail(text) {
            const el = document.getElementById('incident_full_text');
            if (el) el.textContent = text;
            showBsModal('modalIncidentDetail');
        }

        // AJAX search handler for Team Members row
        function initTeamPersonSearch(rowId) {
            const row = document.getElementById(`team-row-${rowId}`);
            if (!row) return;
            const searchInput = row.querySelector('.team-person-search');
            const resultsDiv = row.querySelector('.person-search-results');
            const posInput = row.querySelector('.team-person-position');
            const deptInput = row.querySelector('.team-person-department');
            let timeout = null;

            if (!searchInput || !resultsDiv) return;

            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const q = this.value.trim();
                if (q.length < 1) {
                    resultsDiv.style.display = 'none';
                    resultsDiv.innerHTML = '';
                    return;
                }

                timeout = setTimeout(() => {
                    fetch(`{{ route('backoffice.incident.rca.search_person') }}?q=${encodeURIComponent(q)}`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.success && res.data && res.data.length > 0) {
                                resultsDiv.innerHTML = '';
                                res.data.forEach(p => {
                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className = 'list-group-item list-group-item-action text-start py-2';
                                    btn.style.fontSize = '0.85rem';
                                    btn.innerHTML = `
                                        <div class="fw-bold text-dark d-flex align-items-center justify-content-between">
                                            <span><i class="fas fa-user text-primary me-2"></i>${p.fullname}</span>
                                            <span class="badge bg-light text-secondary border">${p.department ? p.department : 'บุคลากร'}</span>
                                        </div>
                                        <div class="text-muted small mt-1" style="font-size: 0.78rem;">
                                            <i class="fas fa-briefcase me-1 text-muted"></i>${p.position ? p.position : 'ไม่ระบุตำแหน่ง'}
                                        </div>
                                    `;
                                    btn.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        searchInput.value = p.fullname;
                                        if (posInput) posInput.value = p.position || '';
                                        if (deptInput) deptInput.value = p.department || '';

                                        resultsDiv.style.display = 'none';
                                        resultsDiv.innerHTML = '';
                                    });
                                    resultsDiv.appendChild(btn);
                                });
                                resultsDiv.style.display = 'block';
                            } else {
                                resultsDiv.style.display = 'none';
                                resultsDiv.innerHTML = '';
                            }
                        })
                        .catch(err => console.error(err));
                }, 250);
            });
        }

        // AJAX search handler for Recorder Name (Section 9)
        function initRecorderPersonSearch() {
            const searchInput = document.getElementById('recorder_name');
            const resultsDiv = document.getElementById('recorder_person_results');
            const posInput = document.getElementById('recorder_position');
            let timeout = null;

            if (!searchInput || !resultsDiv) return;

            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const q = this.value.trim();
                if (q.length < 1) {
                    resultsDiv.style.display = 'none';
                    resultsDiv.innerHTML = '';
                    return;
                }

                timeout = setTimeout(() => {
                    fetch(`{{ route('backoffice.incident.rca.search_person') }}?q=${encodeURIComponent(q)}`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.success && res.data && res.data.length > 0) {
                                resultsDiv.innerHTML = '';
                                res.data.forEach(p => {
                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className = 'list-group-item list-group-item-action text-start py-2';
                                    btn.style.fontSize = '0.85rem';
                                    btn.innerHTML = `
                                        <div class="fw-bold text-dark"><i class="fas fa-user-edit text-primary me-1"></i>${p.fullname}</div>
                                        <small class="text-muted">${p.position ? p.position : 'ไม่ระบุตำแหน่ง'}${p.department ? ' | ' + p.department : ''}</small>
                                    `;
                                    btn.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        searchInput.value = p.fullname;
                                        if (posInput) posInput.value = p.position || '';

                                        resultsDiv.style.display = 'none';
                                        resultsDiv.innerHTML = '';
                                    });
                                    resultsDiv.appendChild(btn);
                                });
                                resultsDiv.style.display = 'block';
                            } else {
                                resultsDiv.style.display = 'none';
                                resultsDiv.innerHTML = '';
                            }
                        })
                        .catch(err => console.error(err));
                }, 250);
            });
        }

        // Close search results on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.position-relative')) {
                document.querySelectorAll('.person-search-results, #recorder_person_results').forEach(el => {
                    el.style.display = 'none';
                });
            }
        });
    </script>
@endpush
