@extends('layouts.app')

@section('title', 'SmartData | ข้อมูลการแก้ไขอุบัติการณ์ส่ง NRLS')

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
        #tableNrlsEdit {
            border: 1px solid #e3e6f0 !important;
            border-collapse: collapse;
        }
        #tableNrlsEdit thead th {
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
        #tableNrlsEdit tbody td {
            padding: 8px 8px !important;
            border-bottom: 1px solid #e3e6f0 !important;
            font-size: 0.8rem;
            color: #4f5d73;
            vertical-align: middle;
        }
        #tableNrlsEdit tbody tr:hover {
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
                        <i class="fas fa-edit text-primary me-2"></i>
                        ข้อมูลการแก้ไขอุบัติการณ์ความเสี่ยงส่ง NRLS
                    </h5>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-end align-items-center gap-2">
                    <form action="" method="GET" class="m-0 d-flex gap-2 align-items-center">
                        <input type="text" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $start_date }}" style="width: 140px;">
                        <input type="text" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $end_date }}" style="width: 140px;">
                        <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-search me-1"></i>ค้นหา</button>
                    </form>
                    <a href="{{ route('backoffice.incident.nrls_editexport') }}" class="btn btn-success btn-sm px-3 shadow-sm"><i class="fas fa-file-export me-1"></i>ส่งออกข้อมูล (.txt)</a>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="tableNrlsEdit" class="table table-hover align-middle w-100" style="font-size: 0.85rem;">
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
                            @foreach ($nrls as $row)
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
            $('#tableNrlsEdit').DataTable({
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                },
                pageLength: 10
            });

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
