@extends('layouts.admin')

@section('title', 'Admin Hub - SmartData')

@section('content')
<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="fw-bold text-success mb-2">Admin Control Center</h1>
            <p class="text-muted">เลือกโมดูลที่ต้องการจัดการ</p>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- User Management Card -->
        <div class="col-md-5 col-lg-4">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-success transition-all">
                    <div class="card-body p-5 text-center">
                        <div class="icon-shape bg-success-subtle text-success rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-users-cog fa-2x"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-3">จัดการผู้ใช้งาน</h3>
                        <p class="text-muted mb-0">เพิ่ม ลบ แก้ไข และกำหนดสิทธิ์การเข้าใช้งานของผู้ใช้ในระบบ</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- System Settings Card -->
        <div class="col-md-5 col-lg-4">
            <a href="{{ route('admin.system.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-primary transition-all">
                    <div class="card-body p-5 text-center">
                        <div class="icon-shape bg-primary-subtle text-primary rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="fas fa-server fa-2x"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-3">ตั้งค่าระบบ</h3>
                        <p class="text-muted mb-0">อัปเดตโค้ด อัปเกรดฐานข้อมูล และจัดการตัวแปรระบบ (System Variables)</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
    .border-hover-success:hover {
        border: 1px solid #198754 !important;
        transform: translateY(-10px);
    }
    .border-hover-primary:hover {
        border: 1px solid #0d6efd !important;
        transform: translateY(-10px);
    }
    .icon-shape {
        transition: transform 0.3s ease;
    }
    .card:hover .icon-shape {
        transform: scale(1.1);
    }
</style>
@endsection
