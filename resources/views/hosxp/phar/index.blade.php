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

        .bg-pastel-purple { background-color: #f3e8ff; }
        .bg-pastel-blue { background-color: #e0f2fe; }
        .bg-pastel-green { background-color: #dcfce7; }
        
        .text-purple { color: #a855f7; }
        
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
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-capsules text-purple me-2"></i> {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">เลือกรายงานที่ต้องการดูข้อมูล</div>
                </div>
            </div>
        </div>

        <!-- Report Menu Grid -->
        <div class="row g-4 mb-4">
            <!-- รายงานทั่วไป -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-purple py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-purple"><i class="fas fa-folder-open me-2"></i>รายงานสถิติทั่วไป</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('hosxp.phar.prescription_count') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-bold">จำนวนใบสั่งยา</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>                            
                            <a href="{{ route('hosxp.phar.top20_value') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-bold">มูลค่าการใช้ยา 20 อันดับ</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="{{ route('hosxp.phar.top20_diag') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-bold">มูลค่าการใช้ยา 20 อันดับโรค (Primary Diagnosis)</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">ข้อมูลการสั่งยาช่วงเวลา 00.00-08.00 น.</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">ข้อมูลการแพ้ยาแยก รพ.สต.</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">Medication Error Report</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- รายงานเฉพาะทาง/เฉพาะโรค & คุณภาพ -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-blue py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-file-medical me-2"></i>รายงานเฉพาะกลุ่ม</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">มูลค่าการใช้ยาสมุนไพร</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">ข้อมูลการใช้ยา ESRD</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>     
                            <a href="#" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">ข้อมูลการใช้ยา HD</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a> 
                            <a href="#" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">ข้อมูลการใช้ยา DM-HT</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a> 
                            <a href="{{ route('hosxp.phar.due') }}" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-bold">ข้อมูลการใช้ยา DUE</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a> 
                            <a href="{{ route('hosxp.phar.metformin') }}" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-bold">ข้อมูลการใช้ยา Metformin</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a> 
                            <a href="{{ route('hosxp.phar.warfarin') }}" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-bold">ข้อมูลการใช้ยา Warfarin</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a> 
                            <a href="{{ route('hosxp.phar.antiviral') }}" class="list-group-item list-group-item-action py-2 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-bold">ข้อมูลการใช้ยาต้านไวรัส</span>
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
