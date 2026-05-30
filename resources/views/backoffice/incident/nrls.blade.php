@extends('layouts.app')

@section('title', 'SmartData | ข้อมูลรายงาน NRLS')

@section('topbar_actions')
    <a href="{{ route('backoffice.incident.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
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
        /* Style Search & Length */
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
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-4">
        <!-- Header Box -->
        <div class="page-header-container bg-white rounded-3 shadow-sm border p-4 mb-4 mt-3">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-paper-plane text-primary me-2"></i>
                        ข้อมูลรายงานส่ง NRLS
                    </h5>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-end align-items-center">
                    <form action="" method="GET" class="m-0 d-flex gap-2 align-items-center">
                        <input type="hidden" name="tab" id="active_tab_input" value="{{ $active_tab }}">
                        <input type="text" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $start_date }}" style="width: 140px;">
                        <input type="text" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $end_date }}" style="width: 140px;">
                        <button type="submit" class="btn btn-primary btn-sm px-3" style="height: 31px; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;"><i class="fas fa-search"></i> ค้นหา</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs border-bottom gap-1 mb-3" id="nrlsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link @if($active_tab === 'occurrence') active @endif fw-bold px-3 py-2" id="occurrence-tab" data-bs-toggle="tab" data-bs-target="#occurrence-tab-pane" type="button" role="tab" aria-controls="occurrence-tab-pane" aria-selected="true" style="border-radius: 8px 8px 0 0;">
                    <i class="fas fa-paper-plane me-1"></i>ข้อมูลการเกิดอุบัติการณ์
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link @if($active_tab === 'correction') active @endif fw-bold px-3 py-2" id="correction-tab" data-bs-toggle="tab" data-bs-target="#correction-tab-pane" type="button" role="tab" aria-controls="correction-tab-pane" aria-selected="false" style="border-radius: 8px 8px 0 0;">
                    <i class="fas fa-edit me-1"></i>ข้อมูลการแก้ไขอุบัติการณ์
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link @if($active_tab === 'dataset') active @endif fw-bold px-3 py-2" id="dataset-tab" data-bs-toggle="tab" data-bs-target="#dataset-tab-pane" type="button" role="tab" aria-controls="dataset-tab-pane" aria-selected="false" style="border-radius: 8px 8px 0 0;">
                    <i class="fas fa-file-invoice me-1"></i>ข้อมูล Dataset รายเดือน
                </button>
            </li>
        </ul>

        <!-- Tab Panes -->
        <div class="tab-content bg-white p-4 rounded-3 shadow-sm border mb-4" id="nrlsTabsContent">
            
            <!-- Tab 1: occurrence -->
            <div class="tab-pane fade @if($active_tab === 'occurrence') show active @endif" id="occurrence-tab-pane" role="tabpanel" aria-labelledby="occurrence-tab" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-dark fw-bold mb-0">ข้อมูลการเกิดอุบัติการณ์ความเสี่ยงส่ง NRLS</h6>
                    <a href="{{ route('backoffice.incident.nrls_export') }}" class="btn btn-success btn-sm px-3 shadow-sm">
                        <i class="fas fa-file-export me-1"></i>ส่งออกข้อมูล (.csv)
                    </a>
                </div>
                <div class="table-responsive">
                    <table id="tableNrls" class="table table-hover table-custom align-middle w-100" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th>รหัสโรงพยาบาล</th>
                                <th>รหัสความเสี่ยง</th>
                                <th>รหัสอุบัติการณ์</th>
                                <th>ผลกระทบ</th>
                                <th>เพศ</th>
                                <th>อายุ (ปี)</th>
                                <th>สถานที่เกิดเหตุ</th>
                                <th>วันที่เกิด</th>
                                <th>ระดับความรุนแรง</th>
                                <th>สถานะตรวจสอบระดับ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nrls as $row)
                                <tr>
                                    <td>{{ $row->hospital }}</td>
                                    <td class="fw-bold text-primary">{{ $row->risk_id }}</td>
                                    <td>
                                        <span class="badge bg-secondary" title="{{ $row->datadic1_name }}">
                                            {{ $row->datadic1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark" title="{{ $row->effect_name }}">
                                            {{ $row->effect_code }}
                                        </span>
                                    </td>
                                    <td>{{ $row->pt_sex }}</td>
                                    <td>{{ intval($row->person_age) }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark" title="{{ $row->datadic4_name }}">
                                            {{ $row->datadic4 }}
                                        </span>
                                    </td>
                                    <td>{{ DateThai($row->risk_date) }}</td>
                                    <td>
                                        <span class="badge bg-info text-white" title="{{ $row->datadic6_name }}">
                                            {{ $row->datadic6 }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($row->status_lavel == 'OK')
                                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>{{ $row->status_lavel }}</span>
                                        @else
                                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>{{ $row->status_lavel }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: correction -->
            <div class="tab-pane fade @if($active_tab === 'correction') show active @endif" id="correction-tab-pane" role="tabpanel" aria-labelledby="correction-tab" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-dark fw-bold mb-0">ข้อมูลการแก้ไขอุบัติการณ์ความเสี่ยงส่ง NRLS</h6>
                    <a href="{{ route('backoffice.incident.nrls_editexport') }}" class="btn btn-success btn-sm px-3 shadow-sm">
                        <i class="fas fa-file-export me-1"></i>ส่งออกข้อมูล (.csv)
                    </a>
                </div>
                <div class="table-responsive">
                    <table id="tableNrlsEdit" class="table table-hover table-custom align-middle w-100" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th>รหัสความเสี่ยง</th>
                                <th>วันที่เกิด</th>
                                <th>วันที่แก้ไขเสร็จ</th>
                                <th>ระดับความรุนแรง</th>
                                <th>รายละเอียดอุบัติการณ์</th>
                                <th>รายละเอียดการแก้ไข</th>
                                <th>แนวทางป้องกัน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($nrls_edit as $row)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $row->risk_id }}</td>
                                    <td>{{ DateThai($row->risk_date) }}</td>
                                    <td>{{ DateThai($row->risk_date_edit) }}</td>
                                    <td>
                                        <span class="badge bg-info text-white" title="{{ $row->datadic6_name }}">
                                            {{ $row->datadic6 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="detail-scroll">
                                            {{ $row->risk_detail }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="detail-scroll">
                                            {{ $row->risk_detail_edit }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="detail-scroll">
                                            {{ $row->RISKREP_INFER_IMPROVE }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 3: dataset -->
            <div class="tab-pane fade @if($active_tab === 'dataset') show active @endif" id="dataset-tab-pane" role="tabpanel" aria-labelledby="dataset-tab" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-dark fw-bold mb-0">รายการรหัสโครงสร้างตัวชี้วัด (Dataset)</h6>
                    <a href="{{ route('backoffice.incident.nrls_dataset_export') }}" class="btn btn-success btn-sm px-3 shadow-sm">
                        <i class="fas fa-file-export me-1"></i>ส่งออกข้อมูล (.csv)
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 15%;">รหัสตัวชี้วัด</th>
                                <th style="width: 65%;">รายละเอียดตัวชี้วัด</th>
                                <th class="text-center" style="width: 20%;">ค่าที่ได้</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-4 fw-bold">rr001</td>
                                <td>จำนวนวันนอนรวมของผู้ป่วยในทั้งหมด</td>
                                <td class="text-center fw-bold text-primary">{{ $rr001[0]->rr001 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr003</td>
                                <td>จำนวนครั้งการรับบริการผู้ป่วยนอกนอกเวลา</td>
                                <td class="text-center fw-bold text-primary">{{ $rr003[0]->rr003 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr004</td>
                                <td>จำนวนครั้งการรับบริการผู้ป่วยนอกในเวลา</td>
                                <td class="text-center fw-bold text-primary">{{ $rr004[0]->rr004 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr005</td>
                                <td>จำนวนครั้งการรับบริการผู้ป่วยในนอกเวลา</td>
                                <td class="text-center fw-bold text-primary">{{ $rr005[0]->rr005 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr006</td>
                                <td>จำนวนครั้งการรับบริการผู้ป่วยในในเวลา</td>
                                <td class="text-center fw-bold text-primary">{{ $rr006[0]->rr006 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr007</td>
                                <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 1)</td>
                                <td class="text-center fw-bold text-primary">{{ $rr007[0]->rr007 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr008</td>
                                <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 3)</td>
                                <td class="text-center fw-bold text-primary">{{ $rr008[0]->rr008 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr009</td>
                                <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 4)</td>
                                <td class="text-center fw-bold text-primary">{{ $rr009[0]->rr009 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr010</td>
                                <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 5)</td>
                                <td class="text-center fw-bold text-primary">{{ $rr010[0]->rr010 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr011</td>
                                <td>จำนวนครั้งการส่งต่อผู้ป่วยนอกทั้งหมด</td>
                                <td class="text-center fw-bold text-primary">{{ $rr011[0]->rr011 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr015</td>
                                <td>จำนวนครั้งการส่งต่อผู้ป่วยในระดับวิกฤต (Triage level 3)</td>
                                <td class="text-center fw-bold text-primary">{{ $rr015[0]->rr015 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr016</td>
                                <td>จำนวนครั้งการส่งต่อผู้ป่วยในระดับวิกฤต (Triage level 4)</td>
                                <td class="text-center fw-bold text-primary">{{ $rr016[0]->rr016 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr022</td>
                                <td>จำนวนรายการใบสั่งยากลุ่มเสี่ยงสูญเสียชีวิต/ทุพพลภาพ</td>
                                <td class="text-center fw-bold text-primary">{{ $rr022[0]->rr022 ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="ps-4 fw-bold">rr024</td>
                                <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 2)</td>
                                <td class="text-center fw-bold text-primary">{{ $rr024[0]->rr024 ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#tableNrls').DataTable({
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                },
                pageLength: 10
            });

            $('#tableNrlsEdit').DataTable({
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                },
                pageLength: 10
            });

            // Tab change event to adjust url hidden tab parameter
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
                btn.addEventListener('shown.bs.tab', function (event) {
                    const targetId = event.target.id;
                    const tabInput = document.getElementById('active_tab_input');
                    let tabName = 'occurrence';

                    if (targetId === 'occurrence-tab') {
                        tabName = 'occurrence';
                    } else if (targetId === 'correction-tab') {
                        tabName = 'correction';
                    } else if (targetId === 'dataset-tab') {
                        tabName = 'dataset';
                    }

                    if (tabInput) {
                        tabInput.value = tabName;
                    }

                    // Update url state without reloading page
                    const url = new URL(window.location);
                    url.searchParams.set('tab', tabName);
                    window.history.pushState({}, '', url);
                });
            });

            // Date Pickers setup
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

                flatpickr("#start_date", commonConfig);
                flatpickr("#end_date", commonConfig);
            }
        });
    </script>
@endpush
