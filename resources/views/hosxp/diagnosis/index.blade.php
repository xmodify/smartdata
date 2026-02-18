@extends('layouts.app')

@section('title', 'SmartData | รายโรคสำคัญ')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <div class="bg-gradient-danger text-white p-3 rounded-3 shadow-sm me-3" style="background: linear-gradient(135deg, #e74a3b 0%, #be185d 100%);">
            <i class="fas fa-virus fa-2x"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-0">รายโรคสำคัญ</h3>
            <p class="text-muted mb-0">ระบบติดตามและสถิติข้อมูลแยกตามกลุ่มโรคสำคัญ</p>
        </div>
    </div>

    <div class="row">
        <!-- Cardiovascular & Stroke -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 rounded-3 me-3" style="background-color: #fee2e2;">
                            <i class="fas fa-heart-pulse text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-0">หัวใจและหลอดเลือด</h5>
                    </div>
                    <div class="list-group list-group-flush mt-3">
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'stroke']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Stroke (หลอดเลือดสมอง)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'ihd']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>IHD (หัวใจขาดเลือด)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'mi']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>MI (กล้ามเนื้อหัวใจตาย)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Respiratory & Sepsis -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 rounded-3 me-3" style="background-color: #e0f2fe;">
                            <i class="fas fa-lungs text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-0">ทางเดินหายใจและติดเชื้อ</h5>
                    </div>
                    <div class="list-group list-group-flush mt-3">
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'asthma']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Asthma (หอบหืด)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'copd']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>COPD (ปอดอุดกั้นเรื้อรัง)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'pneumonia']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Pneumonia (ปอดบวม)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'sepsis']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Sepsis (ติดเชื้อในกระแสเลือด)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Others -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 rounded-3 me-3" style="background-color: #fef3c7;">
                            <i class="fas fa-clipboard-check text-warning"></i>
                        </div>
                        <h5 class="fw-bold mb-0">กลุ่มโรคอื่นๆ</h5>
                    </div>
                    <div class="list-group list-group-flush mt-3">
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'alcohol_withdrawal']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Alcohol Withdrawal</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'fracture']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>กระดูกสะโพกหัก</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'head_injury']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Head Injury</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'trauma']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Trauma</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => 'palliative_care']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Palliative Care</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .list-group-item:hover {
        background-color: #f8f9fc !important;
        transform: translateX(5px);
        transition: all 0.2s ease;
    }
</style>
@endsection
