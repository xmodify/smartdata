@extends('layouts.app')

@section('title', 'SmartData | รายโรคสำคัญ')

@section('content')
<div class="container-fluid px-2 px-md-3">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <div class="bg-gradient-danger text-white p-3 rounded-3 shadow-sm me-3" style="background: linear-gradient(135deg, #e74a3b 0%, #be185d 100%);">
            <i class="fas fa-virus fa-2x"></i>
        </div>
        <div>
            @php
                $category_names = [
                    'opd' => 'ผู้ป่วยนอก OPD',
                    'ipd' => 'ผู้ป่วยใน IPD',
                    'refer' => 'ผู้ป่วยส่งต่อ Refer',
                    'all' => ''
                ];
                $category_label = $category_names[(string)$category] ?? '';
            @endphp
            <h4 class="fw-bold mb-0">รายโรคสำคัญ {{ $category_label ? '(' . $category_label . ')' : '' }}</h4>
            <p class="text-muted mb-0 small">ระบบติดตามและสถิติข้อมูลแยกตามกลุ่มโรคสำคัญ{{ $category_label ? ' : ' . $category_label : '' }}</p>
        </div>
    </div>

    <div class="row">
        @php
            $groups = [];
            foreach ($configs as $type => $config) {
                $groupName = $config['group'] ?? 'Others';
                if (!isset($groups[$groupName])) {
                    $groups[$groupName] = [
                        'icon' => $config['group_icon'] ?? 'fas fa-clipboard-check',
                        'color' => $config['group_color'] ?? '#fef3c7',
                        'diseases' => []
                    ];
                }
                $groups[$groupName]['diseases'][$type] = $config;
            }
            
            // Reorder groups to match original design if possible
            $orderedGroupNames = ['Cardiovascular & Neurology', 'Respiratory & Sepsis', 'Trauma & Injury', 'Others'];
            $orderedGroups = [];
            foreach ($orderedGroupNames as $name) {
                if (isset($groups[$name])) {
                    $orderedGroups[$name] = $groups[$name];
                    unset($groups[$name]);
                }
            }
            $orderedGroups = array_merge($orderedGroups, $groups);
        @endphp

        @foreach ($orderedGroups as $groupName => $group)
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                <div class="card-body p-3"> <!-- Reduced from p-4 -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="p-2 rounded-3 me-3" style="background-color: {{ $group['color'] }};">
                            @php
                                // Map technical group names to Thai display names
                                $displayNames = [
                                    'Cardiovascular & Neurology' => 'หัวใจและหลอดเลือด/สมอง',
                                    'Respiratory & Sepsis' => 'ทางเดินหายใจและติดเชื้อ',
                                    'Trauma & Injury' => 'อุบัติเหตุและพร่องประสาท',
                                    'Others' => 'กลุ่มโรคอื่นๆ'
                                ];
                            @endphp
                            <i class="{{ $group['icon'] }} text-dark" style="opacity: 0.7;"></i>
                        </div>
                        <h6 class="fw-bold mb-0">{{ $displayNames[$groupName] ?? $groupName }}</h6>
                    </div>
                    <div class="list-group list-group-flush mt-3">
                        @foreach ($group['diseases'] as $type => $config)
                        <a href="{{ route('hosxp.diagnosis.report', ['type' => $type, 'category' => $category]) }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">{{ $config['name'] }}</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach
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
