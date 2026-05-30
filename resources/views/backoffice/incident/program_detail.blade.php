@extends('layouts.app')

@section('title', 'SmartData | รายละเอียดอุบัติการณ์แยกตามโปรแกรมหลัก')

@section('topbar_actions')
    <a href="{{ route('backoffice.incident.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        #tableProgramDetail {
            border: 1px solid #e3e6f0 !important;
            border-collapse: collapse;
        }
        #tableProgramDetail thead th {
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
        #tableProgramDetail tbody td {
            padding: 8px 8px !important;
            border-bottom: 1px solid #e3e6f0 !important;
            font-size: 0.8rem;
            color: #4f5d73;
            vertical-align: middle;
        }
        #tableProgramDetail tbody tr:hover {
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
            <h5 class="text-dark mb-0 fw-bold">
                <i class="fas fa-shield-virus text-primary me-2"></i>
                รายละเอียดอุบัติการณ์ความเสี่ยงตามโปรแกรมหลัก: {{ $RISK_REPPROGRAM_NAME }}
            </h5>
            <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
        </div>

        <!-- Table Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table id="tableProgramDetail" class="table table-hover align-middle w-100" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th style="width: 10%;">รหัส</th>
                                <th style="width: 12%;">วันที่บันทึก</th>
                                <th style="width: 10%;">ระดับความรุนแรง</th>
                                <th style="width: 15%;">โปรแกรมย่อย</th>
                                <th style="width: 15%;">โปรแกรมย่อยระดับ 2</th>
                                <th style="width: 30%;">รายละเอียด</th>
                                <th style="width: 8%;">วันที่ทบทวน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($program_detail as $row)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $row->id }}</td>
                                    <td>{{ DateThai($row->RISKREP_DATESAVE) }}</td>
                                    <td class="text-center"><span class="badge bg-secondary">{{ $row->RISK_REP_LEVEL_NAME }}</span></td>
                                    <td>{{ $row->RISK_REPPROGRAMSUB_NAME }}</td>
                                    <td>{{ $row->RISK_REPPROGRAMSUBSUB_NAME }}</td>
                                    <td>
                                        <div class="detail-scroll">
                                            {{ $row->RISKREP_DETAILRISK }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($row->recheck)
                                            @foreach (explode(',', $row->recheck) as $date)
                                                <span class="badge bg-success d-block mb-1">{{ DateThai(trim($date)) }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
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
    <script>
        $(document).ready(function() {
            $('#tableProgramDetail').DataTable({
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    zeroRecords: "ไม่พบข้อมูลอุบัติการณ์ความเสี่ยงตามโปรแกรมหลักนี้ในช่วงเวลาที่กำหนด",
                    paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
                },
                pageLength: 10,
                order: [[1, 'desc']]
            });
        });
    </script>
@endpush
