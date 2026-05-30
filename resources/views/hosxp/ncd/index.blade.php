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

        .bg-pastel-orange  { background-color: #fff3e0; }
        .bg-pastel-red     { background-color: #fce4ec; }
        .bg-pastel-teal    { background-color: #e0f2f1; }
        .bg-pastel-indigo  { background-color: #e8eaf6; }
        .bg-pastel-cyan    { background-color: #e0f7fa; }
        .bg-pastel-green   { background-color: #e8f5e9; }

        .text-orange  { color: #f57c00; }
        .text-red     { color: #e53935; }
        .text-teal    { color: #00796b; }
        .text-indigo  { color: #3949ab; }
        .text-cyan    { color: #0097a7; }
        .text-green2  { color: #388e3c; }

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
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-heartbeat text-warning me-2"></i> {{ $title }}</h5>
                    <div class="text-muted small mt-1">เลือกรายงานที่ต้องการดูข้อมูล</div>
                </div>
            </div>
        </div>

        {{-- Row 1 --}}
        <div class="row g-4 mb-4">
            {{-- คลินิกความดัน --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-orange py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-orange"><i class="fas fa-tachometer-alt me-2"></i>คลินิกความดัน</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ทะเบียนผู้ป่วยคลินิกโรคความดัน</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">จำนวนผู้ป่วยคลินิกโรคความดัน</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ประเมินผลภาวะความดัน ผู้ป่วย-ห้องตรวจ/ผลการรักษา</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">รายชื่อผู้ป่วยโรคความดัน ที่มีโรคอื่นร่วม Chronic</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">รายชื่อผู้ป่วยโรคความดันที่รักษาต่อเนื่องครั้งล่าสุด</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ตรวจสอบความถูกต้องสิทธิ์การรักษาโรคความดันในโรงพยาบาล</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ตรวจสอบอาการแทรกซ้อนสิทธิ์การรักษา ความดัน รายบุคคล</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- คลินิกเบาหวาน --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-red py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-red"><i class="fas fa-syringe me-2"></i>คลินิกเบาหวาน</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('hosxp.ncd.dm_register') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-danger fw-bold">ทะเบียนผู้ป่วยคลินิกเบาหวาน</span>
                                    <i class="fas fa-chevron-right text-danger" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">จำนวนผู้ป่วยคลินิกโรคเบาหวาน</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ประเมินผล ผู้ป่วย-ห้องตรวจ/ผลการรักษา</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">รายชื่อผู้ป่วยโรคเบาหวาน ที่มีโรคอื่นร่วม Chronic</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">รายชื่อผู้ป่วยโรคเบาหวานที่รักษาต่อเนื่องครั้งล่าสุดที่ Admit</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ตรวจสอบความถูกต้องสิทธิ์การรักษา เบาหวาน รายบุคคล</span>
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
            {{-- คลินิก ARV --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-indigo py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-indigo"><i class="fas fa-pills me-2"></i>คลินิก ARV</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ทะเบียนผู้ป่วยคลินิก ARV</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- คลินิกผู้ป่วย HD --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-teal py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-teal"><i class="fas fa-procedures me-2"></i>คลินิกผู้ป่วย HD</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ทะเบียนผู้ป่วยคลินิกผู้ป่วย Hd (รพ.หลัก)</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">จำนวนผู้ป่วยที่รับบริการ HD แยกเดือน (&lt;5 คน)</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">จำนวนครั้งผู้ป่วยที่รับบริการ HD แยกเดือน (&lt;5 ครั้ง)</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ดัชนี GFR น้อยกว่า 30</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3 --}}
        <div class="row g-4 mb-4">
            {{-- คลินิกผู้ป่วย CAPD --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-cyan py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-cyan"><i class="fas fa-water me-2"></i>คลินิกผู้ป่วย CAPD</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ทะเบียนผู้ป่วยคลินิกผู้ป่วย CAPD</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">จำนวนผู้ป่วยที่รับบริการ CAPD แยกเดือน</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ตรวจสอบความถูกต้องสิทธิ์การรักษาโรค CAPD</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ดัชนี Stem eGFR 4.5 ที่ยังไม่เริ่มเยียวยา</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- คลินิก Asthma --}}
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                    <div class="card-header bg-pastel-green py-3 border-0" style="border-radius: 15px 15px 0 0;">
                        <h6 class="fw-bold mb-0 text-green2"><i class="fas fa-lungs me-2"></i>คลินิก Asthma</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">ทะเบียนผู้ป่วยคลินิก Asthma</span>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action py-3 px-4 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small">จำนวนผู้ป่วยโรค Asthma</span>
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
