@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
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

        .bg-pastel-teal    { background-color: #e0f2f1; }
        .bg-pastel-green   { background-color: #e8f5e9; }
        .bg-pastel-blue    { background-color: #e3f2fd; }
        .bg-pastel-indigo  { background-color: #e8eaf6; }

        .text-teal2   { color: #00897b; }
        .text-green3  { color: #43a047; }
        .text-blue2   { color: #1e88e5; }
        .text-indigo2 { color: #3949ab; }

        .list-group-item-action:hover {
            background-color: #f8fafc;
            transform: translateX(5px);
            transition: all 0.2s ease;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-hand-holding-heart me-2" style="color: #20c997;"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">เลือกรายงานที่ต้องการดูข้อมูล</div>
                </div>
            </div>
        </div>

        {{-- Row 1 --}}
        <div class="row g-4 mb-4">
            {{-- งางานบัญชี 1 --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-teal py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-teal2">
                            <i class="fas fa-house-chimney-medical me-2"></i>งานบัญชี 1
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ทะเบียนผู้ป่วยคลินิกโรคเรื้อรัง</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">จำนวนผู้ป่วยที่รับบริการ (D-H-P)</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">การเยี่ยมบ้าน-ประเมินในชุมชนคลินิกโรคเรื้อรัง</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ข้อมูลบริการ --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-blue py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-blue2">
                            <i class="fas fa-calendar-check me-2"></i>ข้อมูลบริการ
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">รุ่น ดัชนีโรค (Primary Diagnosis) ต่อ วัยงาน</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">สถิติผลการดำเนินงาน ต่อ วัยงาน</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2 --}}
        <div class="row g-4 mb-4">
            {{-- งานบัญชี 2 --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-green py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-green3">
                            <i class="fas fa-notes-medical me-2"></i>งานบัญชี 2
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ทะเบียนผู้รับ ANC</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
