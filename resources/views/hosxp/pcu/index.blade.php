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

        .list-group-item-action {
            transition: all 0.2s ease;
        }

        .list-group-item-action:hover {
            background-color: #f0fdfa;
            transform: translateX(6px);
        }

        .report-card {
            border-radius: 15px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .report-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
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
                    <div class="text-muted small mt-1">เลือกรายงานที่มีการประมวลผลข้อมูล</div>
                </div>
            </div>
        </div>

        {{-- Row 1: งานบัญชี 1 --}}
        <div class="row g-4 mb-4">
            {{-- งานบัญชี 1 --}}
            <div class="col-md-7 col-lg-6">
                <div class="card border-0 shadow-sm h-100 report-card">
                    <div class="card-header bg-pastel-teal py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-teal2">
                            <i class="fas fa-house-chimney-medical me-2"></i>งานบัญชี 1
                        </h6>
                        <span class="badge bg-white text-teal2 shadow-xs px-2 py-1 small">1 รายงานพร้อมใช้งาน</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            {{-- รายงานอันดับสาเหตุการเจ็บป่วย/การมารับบริการสูงสุด ตามรูปที่ 2 (ยึดคนไข้ person ในเขต ที่ไม่ใช่หมู่ 0) --}}
                            <a href="{{ route('hosxp.pcu.top10_illness') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                                            <i class="fas fa-file-medical text-teal2 me-2"></i>อันดับสาเหตุการเจ็บป่วย/การมารับบริการสูงสุด คนไข้ในเขตรับผิดชอบ
                                        </div>
                                        <div class="text-muted small">
                                            <span class="badge bg-success bg-opacity-10 text-success me-1">
                                                <i class="fas fa-check-circle me-1"></i> มีข้อมูล
                                            </span>
                                            ยึดคนไข้ person ในเขต (ไม่ใช่หมู่ 0)
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-teal2" style="font-size: 0.85rem;"></i>
                                </div>
                            </a>

                            {{-- link อื่นที่ไม่มีข้อมูลเอาออกก่อน ตามคำสั่ง --}}
                            {{--
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0 text-muted">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ทะเบียนผู้ป่วยคลินิกโรคเรื้อรัง</span>
                                    <span class="badge bg-light text-muted">กำลังพัฒนา</span>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0 text-muted">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">จำนวนผู้ป่วยที่รับบริการ (D-H-P)</span>
                                    <span class="badge bg-light text-muted">กำลังพัฒนา</span>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0 text-muted">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">การเยี่ยมบ้าน-ประเมินในชุมชนคลินิกโรคเรื้อรัง</span>
                                    <span class="badge bg-light text-muted">กำลังพัฒนา</span>
                                </div>
                            </a>
                            --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- หมายเหตุรายงาน / สถานะข้อมูล --}}
            <div class="col-md-5 col-lg-6">
                <div class="card border-0 shadow-sm h-100 report-card" style="background: linear-gradient(135deg, #f0fdfa 0%, #ffffff 100%);">
                    <div class="card-body p-4 d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-3 rounded-circle me-3" style="background-color: #e0f2f1; color: #00897b;">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-teal2">ข้อมูลงานเชิงรุก PCU</h6>
                                <p class="text-muted small mb-0">ระบบรายงานข้อมูลสุขภาพปฐมภูมิระดับตำบล</p>
                            </div>
                        </div>
                        <div class="text-muted small lh-lg">
                            <i class="fas fa-check text-teal2 me-2"></i>รายงานอันดับสาเหตุการเจ็บป่วย/การมารับบริการสูงสุด ประมวลผลจากข้อมูลการรับบริการจริง (vn_stat)<br>
                            <i class="fas fa-check text-teal2 me-2"></i>เชื่อมโยงแฟ้มประชากร (person) และหมู่บ้าน (village)<br>
                            <i class="fas fa-check text-teal2 me-2"></i>คัดกรองเฉพาะประชากรในเขตในเขตรับผิดชอบ (ไม่นับหมู่ 0 นอกเขต)<br>
                            <i class="fas fa-check text-teal2 me-2"></i>พร้อมระบบส่งออก Excel และพิมพ์แบบฟอร์มรายงาน
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- หมวดหมู่อื่นที่ยังไม่มีข้อมูลถูกซ่อนไว้ก่อนตามคำสั่ง --}}
        {{--
        <div class="row g-4 mb-4">
            <div class="col-md-6">... ข้อมูลบริการ ...</div>
            <div class="col-md-6">... งานบัญชี 2 ...</div>
        </div>
        --}}

    </div>
@endsection