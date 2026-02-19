@extends('layouts.app')

@section('title', 'SmartData')

@section('content')
<div class="container-fluid px-2 px-md-3">
    <!-- Service Icon Links -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card dashboard-card border-0 shadow-sm">
                <div class="card-header bg-pastel-blue py-3 border-bottom-0">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-th-large me-2"></i>ข้อมูลงานบริการ HOSxP</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $services = [
                                ['url' => '/service_opd', 'label' => 'ผู้ป่วยนอก', 'icon' => 'fa-user-nurse', 'color' => '#4e73df'],
                                ['url' => '/service_ipd', 'label' => 'ผู้ป่วยใน', 'icon' => 'fa-bed-pulse', 'color' => '#1cc88a'],
                                ['url' => '/service_er', 'label' => 'อุบัติเหตุ-ฉุกเฉิน', 'icon' => 'fa-truck-medical', 'color' => '#e74a3b'],
                                ['url' => '/service_drug', 'label' => 'เภสัชกรรม', 'icon' => 'fa-pills', 'color' => '#f6c23e'],
                                ['url' => '/service_mental', 'label' => 'สุขภาพจิต/ยาเสพติด', 'icon' => 'fa-brain', 'color' => '#36b9cc'],
                                ['url' => '/service_physic', 'label' => 'กายภาพบำบัด', 'icon' => 'fa-walking', 'color' => '#6610f2'],
                                ['url' => '/service_healthmed', 'label' => 'แพทย์แผนไทย', 'icon' => 'fa-leaf', 'color' => '#20c997'],
                                ['url' => '/service_dent', 'label' => 'ทันตกรรม', 'icon' => 'fa-tooth', 'color' => '#fd7e14'],
                                ['url' => '/service_ncd', 'label' => 'คลินิกโรคเรื้อรัง', 'icon' => 'fa-clipboard-list', 'color' => '#858796'],
                                ['url' => '/service_pcu', 'label' => 'งานเชิงรุก', 'icon' => 'fa-hand-holding-heart', 'color' => '#5a5c69'],
                                ['url' => '/service_xray', 'label' => 'รังสีวิทยา', 'icon' => 'fa-x-ray', 'color' => '#4e73df'],
                                ['url' => '/service_lab', 'label' => 'เทคนิคการแพทย์', 'icon' => 'fa-flask', 'color' => '#36b9cc'],
                                ['url' => '/service_operation', 'label' => 'ห้องผ่าตัด', 'icon' => 'fa-scissors', 'color' => '#e74a3b'],
                                ['url' => '/service_refer', 'label' => 'ข้อมูลการส่งต่อ', 'icon' => 'fa-share-nodes', 'color' => '#4e73df'],
                            ];
                        @endphp

                        @foreach($services as $service)
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <a href="{{ url($service['url']) }}" class="text-decoration-none transition-hover h-100 d-block">
                                <div class="card h-100 border-0 shadow text-center p-2" style="border-radius: 12px; background: #fff;">
                                    <div class="mx-auto mb-2 icon-box-grid" style="background-color: {{ $service['color'] }}15; color: {{ $service['color'] }};">
                                        <i class="fas {{ $service['icon'] }}"></i>
                                    </div>
                                    <span class="text-dark small fw-bold">{{ $service['label'] }}</span>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Icon Links -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card dashboard-card border-0 shadow-sm">
                <div class="card-header bg-pastel-teal py-2 border-bottom-0"> <!-- Reduced py-3 to py-2 -->
                    <h6 class="m-0 font-weight-bold text-primary small"><i class="fas fa-tools me-2"></i>ข้อมูลงานสนับสนุน BackOffice</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $supportItems = [
                                ['url' => '/backoffice_asset', 'label' => 'งานทรัพย์สิน', 'icon' => 'fa-boxes-stacked', 'color' => '#4e73df'],
                                ['url' => '/backoffice_hrd', 'label' => 'บุคลากร', 'icon' => 'fa-id-card', 'color' => '#1cc88a'],
                                ['url' => '/backoffice_risk', 'label' => 'ความเสี่ยง', 'icon' => 'fa-triangle-exclamation', 'color' => '#e74a3b'],
                                ['url' => '/skpcard', 'label' => 'บัตรสังฆประชาร่วมใจ', 'icon' => 'fa-address-card', 'color' => '#f6c23e'],
                                ['url' => '/form', 'label' => 'ระบบตรวจสอบ|ประเมิน', 'icon' => 'fa-check-to-slot', 'color' => '#6610f2'],
                            ];
                        @endphp

                        @foreach($supportItems as $item)
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <a href="{{ url($item['url']) }}" class="text-decoration-none transition-hover h-100 d-block">
                                <div class="card h-100 border-0 shadow text-center p-2" style="border-radius: 12px; background: #fff;">
                                    <div class="mx-auto mb-2 icon-box-grid" style="background-color: {{ $item['color'] }}15; color: {{ $item['color'] }};">
                                        <i class="fas {{ $item['icon'] }}"></i>
                                    </div>
                                    <span class="text-dark small fw-bold">{{ $item['label'] }}</span>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

