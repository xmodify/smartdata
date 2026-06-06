@extends('layouts.app')

@section('title', 'SmartData | งานทรัพย์สิน')

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

        .bg-pastel-blue { background-color: #e0f2fe; }
        .text-blue { color: #0268c7; }
        
        .hrd-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        /* Card interactive styles matching HRD dashboard */
        .stat-card {
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .stat-card:active {
            transform: scale(0.98);
        }
        .stat-card .card-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.25;
            font-size: 3.5rem;
            transition: all 0.3s ease;
        }
        .stat-card:hover .card-icon {
            opacity: 0.45;
            right: 10px;
            transform: translateY(-50%) scale(1.1);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-3">
        <!-- Header -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-boxes-stacked text-blue me-2"></i> งานทรัพย์สิน
                    </h5>
                    <div class="text-muted small mt-1">เลือกหมวดหมู่ครุภัณฑ์ที่ต้องการดูข้อมูล</div>
                </div>
            </div>
        </div>

        @php
            $bgGradients = [
                'linear-gradient(135deg, #4e73df 0%, #224abe 100%)', // Blue
                'linear-gradient(135deg, #1cc88a 0%, #13855c 100%)', // Green
                'linear-gradient(135deg, #36b9cc 0%, #258391 100%)', // Cyan
                'linear-gradient(135deg, #f6c23e 0%, #dda20a 100%)', // Yellow
                'linear-gradient(135deg, #e74a3b 0%, #be2617 100%)', // Red
                'linear-gradient(135deg, #6610f2 0%, #520dc2 100%)', // Indigo
                'linear-gradient(135deg, #fd7e14 0%, #ca6510 100%)', // Orange
                'linear-gradient(135deg, #20c997 0%, #17a67d 100%)', // Teal
                'linear-gradient(135deg, #6f42c1 0%, #59359a 100%)', // Purple
                'linear-gradient(135deg, #e83e8c 0%, #b83270 100%)', // Pink
                'linear-gradient(135deg, #5a5c69 0%, #3a3b45 100%)', // Grey
                'linear-gradient(135deg, #3f51b5 0%, #283593 100%)'  // Dark Blue
            ];

            $iconMap = [
                5  => 'fa-briefcase',          // ครุภัณฑ์สำนักงาน
                6  => 'fa-car',                // ครุภัณฑ์ยานพาหนะ
                7  => 'fa-broadcast-tower',    // ครุภัณฑ์ไฟฟ้าและวิทยุ
                8  => 'fa-charging-station',   // เครื่องกำเนิดไฟฟ้า
                9  => 'fa-bullhorn',           // ครุภัณฑ์โฆษณา
                10 => 'fa-seedling',           // ครุภัณฑ์การเกษตร เครื่องมือ
                11 => 'fa-tractor',            // ครุภัณฑ์การเกษตร เครื่องจักร
                12 => 'fa-industry',           // ครุภัณฑ์โรงงาน
                17 => 'fa-stethoscope',        // ครุภัณฑ์วิทยาศาสตร์การแพทย์
                18 => 'fa-laptop',             // ครุภัณฑ์คอมพิวเตอร์
                20 => 'fa-utensils',           // ครุภัณฑ์งานบ้านงานครัว
                21 => 'fa-running',            // ครุภัณฑ์กีฬา
                'Default' => 'fa-boxes-stacked'
            ];
        @endphp

        <!-- Category Grid Menu (Premium Stat-Card style) -->
        <div class="row g-3 mb-4 mt-2">
            @foreach ($categories as $index => $cat)
                <div class="col-xl-3 col-md-6">
                    <div class="card hrd-card stat-card text-white overflow-hidden h-100"
                        onclick="window.location.href='{{ route('backoffice.asset.show', $cat->DECLINE_ID) }}'"
                        style="background: {{ $bgGradients[$index % count($bgGradients)] }}; min-height: 110px;">
                        <div class="card-body p-3 d-flex flex-column justify-content-center">
                            <div class="small opacity-75 fw-bold mb-1 text-truncate" style="max-width: 90%;">
                                {{ $cat->DECLINE_NAME }}
                            </div>
                            <div class="h3 mb-0 fw-bold">
                                {{ number_format($cat->asset_count) }} <span class="h6" style="font-size: 0.75rem;">รายการ (สถานะปกติ)</span>
                            </div>
                            <div class="card-icon">
                                <i class="fas {{ $iconMap[$cat->DECLINE_ID] ?? $iconMap['Default'] }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
