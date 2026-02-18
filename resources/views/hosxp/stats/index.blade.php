@extends('layouts.app')

@section('title', 'SmartData | ข้อมูลและสถิติ')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <div class="bg-gradient-primary-custom text-white p-3 rounded-3 shadow-sm me-3">
            <i class="fas fa-chart-bar fa-2x"></i>
        </div>
        <div>
            <h3 class="fw-bold mb-0">ข้อมูลและสถิติ</h3>
            <p class="text-muted mb-0">ศูนย์รวมรายงานและสถิติต่างๆ จากระบบ HOSxP</p>
        </div>
    </div>

    <div class="row">
        <!-- General Reports -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-pastel-blue p-2 rounded-3 me-3">
                            <i class="fas fa-file-invoice text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-0">รายงานทั่วไป</h5>
                    </div>
                    <div class="list-group list-group-flush mt-3">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>สถิติผู้มาใช้บริการรายวัน</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>สถิติผู้มาใช้บริการรายเดือน</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>สถิติผู้มาใช้บริการรายปี</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Reports -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-pastel-teal p-2 rounded-3 me-3">
                            <i class="fas fa-hospital-user text-success"></i>
                        </div>
                        <h5 class="fw-bold mb-0">แยกตามหน่วยงาน</h5>
                    </div>
                    <div class="list-group list-group-flush mt-3">
                        <a href="{{ url('/service_opd') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>บริการผู้ป่วยนอก (OPD)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ url('/service_ipd') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>บริการผู้ป่วยใน (IPD)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ url('/service_er') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>งานอุบัติเหตุ-ฉุกเฉิน (ER)</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disease Reports -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-pastel-pink p-2 rounded-3 me-3" style="background-color: #fee2e2;">
                            <i class="fas fa-virus text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-0">รายงานรายโรค</h5>
                    </div>
                    <div class="list-group list-group-flush mt-3">
                        <a href="{{ url('medicalrecord_diag/asthma') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Asthma / COPD</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ url('medicalrecord_diag/stroke') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Stroke</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                        <a href="{{ url('medicalrecord_diag/sepsis') }}" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                            <span>Sepsis</span>
                            <i class="fas fa-chevron-right small text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-pastel-blue { background-color: #e0f2fe; }
    .bg-pastel-teal { background-color: #f0fdfa; }
    .list-group-item:hover {
        background-color: #f8f9fc !important;
        transform: translateX(5px);
        transition: all 0.2s ease;
    }
</style>
@endsection
