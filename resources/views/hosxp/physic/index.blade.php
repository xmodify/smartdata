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

        .bg-pastel-orange { background-color: #fff7ed; }
        .bg-pastel-blue { background-color: #e0f2fe; }
        .bg-pastel-green { background-color: #dcfce7; }
        
        .text-orange { color: #f97316; }
        
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
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-walking text-orange me-2"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">เลือกรายงานที่ต้องการดูข้อมูล</div>
                </div>
            </div>
        </div>

        <!-- Report Menu Grid -->
        <div class="row g-4 mb-4">
            <!-- รายงานสถิติทั่วไป -->
            <div class="col-12">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-orange py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-orange"><i class="fas fa-folder-open me-2"></i>รายงานสถิติทั่วไป</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('hosxp.physic.service_stats') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">สถิติผู้รับบริการกายภาพบำบัด</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>  
                            <a href="{{ route('hosxp.physic.top20_diag') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">20 อันดับโรคกายภาพบำบัด</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="{{ route('hosxp.physic.service_value') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">มูลค่าการให้บริการกายภาพบำบัด</span>
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
