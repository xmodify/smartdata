@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
<a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm" style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
    <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
</a>
@endsection

@push('styles')
<style>
    .page-header-container {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.25rem; /* Reduced padding */
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem; /* Reduced margin */
        border: 1px solid #f0f0f0;
    }
    .report-title-box h5 {
        font-size: 1.1rem; /* Smaller title */
        letter-spacing: -0.01em;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-3">
    <!-- Header Box -->
    <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center report-title-box">
            <div class="ps-3 py-1">
                <h5 class="text-dark mb-0 fw-bold">
                    <i class="fas fa-virus text-primary me-2"></i>
                    {{ $title }}
                </h5>
                <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
            </div>
        </div>
        
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="m-0">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;"><i class="fas fa-calendar-alt text-primary"></i></span>
                    <select class="form-select form-select-sm border-start-0 border-end-0" name="budget_year" style="min-width: 140px;">
                        @foreach ($budget_year_select as $row)
                            <option value="{{ $row->LEAVE_YEAR_ID }}"
                                {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                {{ $row->LEAVE_YEAR_NAME }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius: 0 8px 8px 0;">
                        <i class="fas fa-search me-2"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-5 text-center">
            <div class="mb-4">
                <i class="fas fa-tools fa-4x text-muted opacity-25"></i>
            </div>
            <h4 class="fw-bold text-muted">กำลังพัฒนา</h4>
            <p class="text-muted">หน้านี้กำลังอยู่ระหว่างการเชื่อมโยงข้อมูลและออกแบบการแสดงผล กรุณากลับมาตรวจสอบอีกครั้งในภายหลัง</p>
            <a href="{{ route('hosxp.stats.index') }}" class="btn btn-primary px-4 mt-3" style="border-radius: 10px;">
                <i class="fas fa-chevron-left me-2"></i> กลับไปหน้าสถิติ
            </a>
        </div>
    </div>
</div>
@endsection
