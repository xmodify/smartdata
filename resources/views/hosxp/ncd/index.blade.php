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
        .bg-pastel-purple  { background-color: #f3e5f5; }
        .bg-pastel-blue    { background-color: #e3f2fd; }
        .bg-pastel-yellow  { background-color: #fffde7; }
        .bg-pastel-brown   { background-color: #efebe9; }

        .text-orange  { color: #f57c00; }
        .text-red     { color: #e53935; }
        .text-rose    { color: #db2777; }
        .text-teal    { color: #00796b; }
        .text-indigo  { color: #3949ab; }
        .text-cyan    { color: #0097a7; }
        .text-green2  { color: #388e3c; }
        .text-purple  { color: #8e24aa; }
        .text-blue    { color: #1e88e5; }
        .text-yellow  { color: #fbc02d; }
        .text-brown   { color: #6d4c41; }

        .list-group-item-action:hover {
            background-color: #f8fafc;
            transform: translateX(5px);
            transition: all 0.2s ease;
        }
    </style>
@endpush

@section('content')
    @php
        $clinics = [
            [
                'code' => '001',
                'name' => 'คลินิกเบาหวาน',
                'icon' => 'fas fa-syringe',
                'bg_class' => 'bg-pastel-red',
                'text_class' => 'text-red',
            ],
            [
                'code' => '002',
                'name' => 'คลินิกความดัน',
                'icon' => 'fas fa-heartbeat',
                'bg_class' => 'bg-pastel-orange',
                'text_class' => 'text-orange',
            ],
            [
                'code' => '029',
                'name' => 'คลินิกโรคหัวใจล้มเหลว',
                'icon' => 'fas fa-heart',
                'bg_class' => 'bg-pastel-rose',
                'text_class' => 'text-rose',
            ],
            [
                'code' => '028',
                'name' => 'คลินิกโรคหลอดเลือดสมอง',
                'icon' => 'fas fa-head-side-virus',
                'bg_class' => 'bg-pastel-brown',
                'text_class' => 'text-brown',
            ],
            [
                'code' => '020',
                'name' => 'คลินิกบำบัดยาเสพติด',
                'icon' => 'fas fa-capsules',
                'bg_class' => 'bg-pastel-purple',
                'text_class' => 'text-purple',
            ],
            [
                'code' => '012',
                'name' => 'คลินิกสุขภาพจิต',
                'icon' => 'fas fa-brain',
                'bg_class' => 'bg-pastel-cyan',
                'text_class' => 'text-cyan',
            ],
            [
                'code' => '007',
                'name' => 'คลินิก CKD',
                'icon' => 'fas fa-circle-nodes',
                'bg_class' => 'bg-pastel-teal',
                'text_class' => 'text-teal',
            ],
            [
                'code' => '032',
                'name' => 'คลินิกโรคไตเรื้อรังระยะ 4-5',
                'icon' => 'fas fa-network-wired',
                'bg_class' => 'bg-pastel-teal',
                'text_class' => 'text-teal',
            ],
            [
                'code' => '013',
                'name' => 'คลินิกฟอกไต HD',
                'icon' => 'fas fa-procedures',
                'bg_class' => 'bg-pastel-blue',
                'text_class' => 'text-blue',
            ],
            [
                'code' => '014',
                'name' => 'คลินิกฟอกไต CAPD',
                'icon' => 'fas fa-water',
                'bg_class' => 'bg-pastel-indigo',
                'text_class' => 'text-indigo',
            ],
            [
                'code' => '009',
                'name' => 'คลินิกวัณโรค / Asthma',
                'icon' => 'fas fa-lungs',
                'bg_class' => 'bg-pastel-green',
                'text_class' => 'text-green2',
            ],
            [
                'code' => '021',
                'name' => 'คลินิก COPD',
                'icon' => 'fas fa-lungs',
                'bg_class' => 'bg-pastel-yellow',
                'text_class' => 'text-yellow',
            ],
        ];
    @endphp

    <div class="container-fluid px-2 px-md-3">
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-heartbeat text-warning me-2"></i> {{ $title }}</h5>
                    <div class="text-muted small mt-1">เลือกรายงานที่ต้องการดูข้อมูล</div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            @foreach ($clinics as $c)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-header {{ $c['bg_class'] }} py-3 border-0" style="border-radius: 15px 15px 0 0;">
                            <h6 class="fw-bold mb-0 {{ $c['text_class'] }}"><i class="{{ $c['icon'] }} me-2"></i>{{ $c['name'] }}</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="{{ route('hosxp.ncd.clinic_register', ['clinic_code' => $c['code']]) }}" 
                                   class="list-group-item list-group-item-action py-3 px-4 border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold {{ $c['text_class'] }}">ทะเบียนผู้ป่วย{{ $c['name'] }}</span>
                                        <i class="fas fa-chevron-right {{ $c['text_class'] }}" style="font-size: 0.7rem;"></i>
                                    </div>
                                </a>
                                @if ($c['code'] === '013')
                                    <a href="{{ route('hosxp.ncd.hd_report') }}" 
                                       class="list-group-item list-group-item-action py-3 px-4 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold {{ $c['text_class'] }}">รายงานรับบริการผู้ป่วยคลินิกฟอกไต HD</span>
                                            <i class="fas fa-chevron-right {{ $c['text_class'] }}" style="font-size: 0.7rem;"></i>
                                        </div>
                                    </a>
                                    <a href="{{ route('hosxp.ncd.hd_private_report') }}" 
                                       class="list-group-item list-group-item-action py-3 px-4 border-top"
                                       style="border-radius: 0 0 15px 15px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold {{ $c['text_class'] }}">รายงานรับบริการผู้ป่วยคลินิกฟอกไต HD เอกชน</span>
                                            <i class="fas fa-chevron-right {{ $c['text_class'] }}" style="font-size: 0.7rem;"></i>
                                        </div>
                                    </a>
                                @else
                                    <a href="{{ route('hosxp.ncd.clinic_report', ['clinic_code' => $c['code']]) }}" 
                                       class="list-group-item list-group-item-action py-3 px-4 border-top"
                                       style="border-radius: 0 0 15px 15px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold {{ $c['text_class'] }}">รายงานรับบริการผู้ป่วย{{ $c['name'] }}</span>
                                            <i class="fas fa-chevron-right {{ $c['text_class'] }}" style="font-size: 0.7rem;"></i>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
