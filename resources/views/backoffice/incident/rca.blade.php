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
                                                @if ($row->rca->rca_status === 'completed')
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
                                        <input type="text" name="department" id="department" class="form-control">
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
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle" id="tableTeamForm" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 8%;" class="text-center">ลำดับ</th>
                                                <th>ชื่อ-สกุล</th>
                                                <th>ตำแหน่ง</th>
                                                <th>หน่วยงาน</th>
                                                <th>ระบบงาน</th>
                                                <th class="text-center no-print" style="width: 8%;">ลบ</th>
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
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle" id="tableSolutionForm" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>รากของปัญหา (Root Cause)</th>
                                                <th>กระบวนการ</th>
                                                <th>หน่วยงานรับผิดชอบ</th>
                                                <th>ระบบ</th>
                                                <th>การปรับปรุง/ออกแบบงานใหม่</th>
                                                <th>ตัวชี้วัด/ความถี่</th>
                                                <th class="text-center no-print" style="width: 8%;">ลบ</th>
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
                                    <div class="col-md-4">
                                        <label class="form-label-bold" for="recorder_name">ชื่อ-สกุล ผู้บันทึก</label>
                                        <input type="text" name="recorder_name" id="recorder_name" class="form-control" placeholder="นาย/นาง/นางสาว...">
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
            const dateVal = data ? data.date : '';
            const timeVal = data ? data.time : '';
            const storyVal = data ? data.story : '';
            const fileLink = data && data.file_path ? `<a href="{{ asset('') }}${data.file_path}" target="_blank" class="btn btn-xs btn-outline-info p-1"><i class="fas fa-file-pdf"></i> ไฟล์แนบเดิม</a><input type="hidden" name="timeline[${rowId}][existing_file]" value="${data.file_path}">` : '';

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
            const nameVal = data ? data.name : '';
            const posVal = data ? data.position : '';
            const deptVal = data ? data.department : '';
            const sysVal = data ? data.work_system : '';
            const count = tableBody.rows.length + 1;

            const rowHtml = `
                <tr id="team-row-${rowId}">
                    <td class="text-center fw-bold team-no">${count}</td>
                    <td><input type="text" name="team_members[${rowId}][name]" class="form-control form-control-sm" value="${nameVal}"></td>
                    <td><input type="text" name="team_members[${rowId}][position]" class="form-control form-control-sm" value="${posVal}"></td>
                    <td><input type="text" name="team_members[${rowId}][department]" class="form-control form-control-sm" value="${deptVal}"></td>
                    <td><input type="text" name="team_members[${rowId}][work_system]" class="form-control form-control-sm" value="${sysVal}"></td>
                    <td class="text-center no-print">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTeamRow('team-row-${rowId}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
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
            const rcVal = data ? data.root_cause : '';
            const procVal = data ? data.process : '';
            const deptVal = data ? data.department : '';
            const sysVal = data ? data.system : '';
            const impVal = data ? data.improvement : '';
            const freqVal = data ? data.frequency : '';

            const rowHtml = `
                <tr id="solution-row-${rowId}">
                    <td><textarea name="creative_solutions[${rowId}][root_cause]" class="form-control form-control-sm" rows="1">${rcVal}</textarea></td>
                    <td><textarea name="creative_solutions[${rowId}][process]" class="form-control form-control-sm" rows="1">${procVal}</textarea></td>
                    <td><input type="text" name="creative_solutions[${rowId}][department]" class="form-control form-control-sm" value="${deptVal}"></td>
                    <td><input type="text" name="creative_solutions[${rowId}][system]" class="form-control form-control-sm" value="${sysVal}"></td>
                    <td><textarea name="creative_solutions[${rowId}][improvement]" class="form-control form-control-sm" rows="1">${impVal}</textarea></td>
                    <td><input type="text" name="creative_solutions[${rowId}][frequency]" class="form-control form-control-sm" value="${freqVal}"></td>
                    <td class="text-center no-print">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow('solution-row-${rowId}')"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML('beforeend', rowHtml);
        }

        function removeRow(rowId) {
            document.getElementById(rowId).remove();
        }

        // Open RCA Modal Form & Populate existing values
        function openRcaModal(incidentId) {
            // Reset form first
            resetRcaForm();
            
            // Set incident ID
            document.getElementById('form_incident_id').value = incidentId;

            // Load details using AJAX
            const detailUrl = "{{ route('backoffice.incident.rca.detail', ':id') }}".replace(':id', incidentId);
            $.ajax({
                url: detailUrl,
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        const inc = res.incident;
                        const rca = res.rca;

                        // Pre-populate fields from backoffice incident
                        document.getElementById('department').value = inc.department || '';
                        document.getElementById('incident_date').value = inc.RISKREP_STARTDATE || '';
                        document.getElementById('an').value = inc.an || '';
                        
                        // Set severity level
                        if (inc.severity) {
                            const sevRadio = document.getElementById(`severity_${inc.severity}`);
                            if (sevRadio) sevRadio.checked = true;
                        }

                        // Pre-populate fields from saved RCA record
                        if (rca) {
                            // RCA Type radio
                            if (rca.rca_type) {
                                const typeRadio = document.querySelector(`input[name="rca_type"][value="${rca.rca_type}"]`);
                                if (typeRadio) typeRadio.checked = true;
                            }
                            
                            document.getElementById('rca_subject').value = rca.rca_subject || '';
                            
                            // Error Status radio
                            if (rca.error_status) {
                                const errRadio = document.querySelector(`input[name="error_status"][value="${rca.error_status}"]`);
                                if (errRadio) errRadio.checked = true;
                            }
                            
                            if (rca.shift) document.getElementById('shift').value = rca.shift;
                            if (rca.main_risk_topic) document.getElementById('main_risk_topic').value = rca.main_risk_topic;
                            document.getElementById('critical_point').value = rca.critical_point || '';
                            document.getElementById('impact_details').value = rca.impact_details || '';
                            document.getElementById('flowchart_details').value = rca.flowchart_details || '';
                            document.getElementById('staff_voice_1').value = rca.staff_voice_1 || '';
                            document.getElementById('staff_voice_2').value = rca.staff_voice_2 || '';
                            document.getElementById('staff_voice_3').value = rca.staff_voice_3 || '';
                            document.getElementById('staff_voice_4').value = rca.staff_voice_4 || '';
                            
                            document.getElementById('recorder_name').value = rca.recorder_name || '';
                            document.getElementById('recorder_position').value = rca.recorder_position || '';
                            document.getElementById('record_date').value = rca.record_date || '';
                            document.getElementById('rca_status').value = rca.rca_status || 'pending';

                            // Populate dynamic Timeline rows
                            if (Array.isArray(rca.timeline)) {
                                rca.timeline.forEach(row => addTimelineRow(row));
                            }
                            
                            // Populate dynamic Team Member rows
                            if (Array.isArray(rca.team_members)) {
                                rca.team_members.forEach(row => addTeamRow(row));
                            }
                            
                            // Populate dynamic Solution rows
                            if (Array.isArray(rca.creative_solutions)) {
                                rca.creative_solutions.forEach(row => addSolutionRow(row));
                            }

                            // Check Potential Changes checkboxes
                            if (Array.isArray(rca.potential_changes)) {
                                rca.potential_changes.forEach(opt => {
                                    const cb = document.querySelector(`input[name="potential_changes[]"][value="${opt}"]`);
                                    if (cb) cb.checked = true;
                                });
                            }

                            // Populate Swiss Cheese table
                            if (rca.swiss_cheese) {
                                Object.keys(rca.swiss_cheese).forEach(qk => {
                                    const idx = qk.replace('q', '');
                                    const item = rca.swiss_cheese[qk];
                                    const radio = document.querySelector(`input[name="swiss_cheese[q${idx}][yes_no]"][value="${item.yes_no}"]`);
                                    if (radio) radio.checked = true;
                                    
                                    const txt = document.querySelector(`input[name="swiss_cheese[q${idx}][detail]"]`);
                                    if (txt) txt.value = item.detail || '';
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
                            // If no RCA saved, add at least one blank row for dynamic lists
                            addTimelineRow();
                            addTeamRow();
                            addSolutionRow();
                            
                            // Pre-fill record date with today
                            document.getElementById('record_date').value = new Date().toISOString().substring(0, 10);
                        }

                        // Open the modal
                        const rcaModal = new bootstrap.Modal(document.getElementById('modalRcaForm'));
                        rcaModal.show();
                    } else {
                        Swal.fire('ข้อผิดพลาด', res.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
                    }
                },
                error: function() {
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
            document.getElementById('incident_full_text').textContent = text;
            const detailModal = new bootstrap.Modal(document.getElementById('modalIncidentDetail'));
            detailModal.show();
        }
    </script>
@endpush
