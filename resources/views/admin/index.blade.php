@extends('layouts.admin')

@section('title', 'Admin Hub - SmartData')

@section('content')
<div class="container-fluid px-md-5 py-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-success mb-1">Admin Control Center</h2>
            <p class="text-muted small">เลือกโมดูลที่ต้องการจัดการระบบ</p>
        </div>
    </div>

    <div class="row g-3 justify-content-center">
        <!-- 1. User Dashboard Card -->
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-info transition-all rounded-4">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <div class="icon-shape bg-info-subtle text-info rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px;">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">User Dashboard</h5>
                        <p class="text-muted small mb-0">เข้าชมหน้าแสดงผลข้อมูลสำหรับผู้ใช้งานทั่วไป (Data Visualization)</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- 2. User Management Card -->
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('admin.users.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-success transition-all rounded-4">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <div class="icon-shape bg-success-subtle text-success rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px;">
                            <i class="fas fa-users-cog fa-lg"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">จัดการผู้ใช้งาน</h5>
                        <p class="text-muted small mb-0">เพิ่ม ลบ แก้ไข และกำหนดสิทธิ์การเข้าใช้งานของผู้ใช้ในระบบ</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- 3. System Settings Card -->
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('admin.system.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-primary transition-all rounded-4">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <div class="icon-shape bg-primary-subtle text-primary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px;">
                            <i class="fas fa-server fa-lg"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">ตั้งค่าระบบ</h5>
                        <p class="text-muted small mb-0">อัปเดตโค้ด อัปเกรดฐานข้อมูล และจัดการตัวแปรระบบ (System Variables)</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- 4. System Monitor Card -->
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('admin.monitor.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-danger transition-all rounded-4">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <div class="icon-shape bg-danger-subtle text-danger rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px;">
                            <i class="fas fa-desktop fa-lg"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">System Monitor</h5>
                        <p class="text-muted small mb-0">ตรวจสอบสถานะการทำงานของบอทส่งรายงานอัตโนมัติ ฐานข้อมูล และล็อกของระบบ</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- 5. AI Copilot Settings Card -->
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.settings') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-primary transition-all rounded-4">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <div class="icon-shape bg-primary-subtle text-primary rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px;">
                            <i class="fas fa-robot fa-lg"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">ตั้งค่า AI Copilot</h5>
                        <p class="text-muted small mb-0">กำหนดค่า AI Engine (Gemini, OpenAI, Ollama) บันทึก API Key และสวิตช์เปิด/ปิด</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- 6. AI Knowledge Base Card -->
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.knowledge') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-info transition-all rounded-4">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <div class="icon-shape bg-info-subtle text-info rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px;">
                            <i class="fas fa-book-medical fa-lg"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">คลังความรู้ AI (RAG)</h5>
                        <p class="text-muted small mb-0">อัปโหลดเอกสาร CPG, แปลงเป็น Vector ใน MySQL, Re-Embed และจัดการเอกสาร</p>
                    </div>
                </div>
            </a>
        </div>

        @if (auth()->user()->username === '1341800003078')
        <!-- 7. License System Card -->
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="{{ route('license.index') }}" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm glass-card border-hover-success transition-all rounded-4" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                        <div class="icon-shape bg-success-subtle text-success rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center shadow-xs" style="width: 60px; height: 60px;">
                            <i class="fas fa-key fa-lg"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">License System</h5>
                        <p class="text-muted small mb-0">ระบบออกสิทธิ์ใช้งานคีย์ลิขสิทธิ์โปรแกรมและล็อก Hardware/รหัสโรงพยาบาล</p>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>
</div>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .transition-all {
        transition: all 0.25s ease-in-out;
    }
    .border-hover-success:hover {
        border-color: #198754 !important;
        transform: translateY(-6px);
        box-shadow: 0 10px 20px rgba(25, 135, 84, 0.15) !important;
    }
    .border-hover-primary:hover {
        border-color: #0d6efd !important;
        transform: translateY(-6px);
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.15) !important;
    }
    .border-hover-info:hover {
        border-color: #0dcaf0 !important;
        transform: translateY(-6px);
        box-shadow: 0 10px 20px rgba(13, 202, 240, 0.15) !important;
    }
    .border-hover-danger:hover {
        border-color: #dc3545 !important;
        transform: translateY(-6px);
        box-shadow: 0 10px 20px rgba(220, 53, 69, 0.15) !important;
    }
</style>
@endsection
