@extends('layouts.app')

@section('title', $category->DECLINE_NAME . ' - SmartData')

@section('topbar_actions')
    <a href="{{ route('backoffice.asset.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
    <style>
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            color: #6e707e;
            background-color: #f8f9fc;
            margin-right: 2px;
            transition: all 0.2s ease-in-out;
        }
        .nav-tabs .nav-link:hover {
            background-color: #eaecf4;
            color: #4e73df;
            border-color: transparent;
        }
        .nav-tabs .nav-link.active {
            color: #4e73df !important;
            background-color: #fff !important;
            border-color: #dddfeb #dddfeb #fff !important;
            border-bottom: 3px solid #4e73df !important;
        }
        .table-custom {
            border: 1px solid #e3e6f0 !important;
            border-collapse: collapse;
        }
        .table-custom thead th {
            background: #f8f9fc !important;
            color: #4e73df !important;
            border-bottom: 2px solid #e3e6f0 !important;
            padding: 12px 10px !important;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            vertical-align: middle;
            border-top: none !important;
        }
        .table-custom tbody td {
            padding: 10px 10px !important;
            border-bottom: 1px solid #e3e6f0 !important;
            font-size: 0.85rem;
            color: #4f5d73;
            vertical-align: middle;
        }
        .table-custom tbody tr:hover {
            background-color: #f8fafd !important;
        }
        
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            outline: none !important;
            font-size: 0.85rem !important;
        }

        .dt-buttons .btn-success {
            background-color: #198754 !important;
            border-color: #198754 !important;
            color: #ffffff !important;
            border-radius: 0.4rem !important;
            font-weight: 500 !important;
            padding: 0.25rem 0.6rem !important;
            font-size: 0.85rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            box-shadow: 0 2px 4px rgba(25, 135, 84, 0.2) !important;
        }

        .dt-buttons .btn-danger {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
            border-radius: 0.4rem !important;
            font-weight: 500 !important;
            padding: 0.25rem 0.6rem !important;
            font-size: 0.85rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2) !important;
            margin-right: 5px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #4e73df !important;
            color: white !important;
            border: 1px solid #4e73df !important;
            border-radius: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f8f9fc !important;
            color: #4e73df !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
        }

        /* Custom Multiselect styles matching HRD */
        .dropdown-menu-multiselect {
            min-width: 350px;
            max-height: 450px;
            overflow-y: auto;
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid #e3e6f0;
        }
        .multiselect-header {
            position: sticky;
            top: 0;
            background: white;
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            z-index: 10;
        }
        .multiselect-search {
            border-radius: 8px;
            font-size: 0.85rem;
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
        }
        .multiselect-item-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .multiselect-item {
            padding: 8px 15px;
            transition: background 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #f8f9fc;
        }
        .multiselect-item:hover {
            background-color: #f8f9fc;
        }
        .multiselect-item input[type="checkbox"] {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #4e73df;
        }
        .multiselect-item label {
            flex: 1;
            cursor: pointer;
            margin-bottom: 0;
            font-size: 0.85rem;
            color: #5a5c69;
            user-select: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .multiselect-item.selected {
            background-color: #eaecf4;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Header Box -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px; overflow: hidden;">
        <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(180deg, #ffffff 0%, #f8f9fc 100%); border-bottom: 1px solid #eaecf4;">
            <h5 class="mb-0 fw-bold text-dark">
                {{ $category->DECLINE_NAME }} ปีงบประมาณ {{ $fiscalYearThai }}
            </h5>
        </div>
        
        <div class="card-body p-4">
            <!-- Navigation Tabs by Status -->
            <ul class="nav nav-tabs border-bottom gap-1 mb-4" id="assetStatusTabs" role="tablist">
                @foreach ($groupedAssets as $statusId => $group)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($loop->first) active @endif fw-bold px-3 py-2" 
                                id="status-{{ $statusId }}-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#status-{{ $statusId }}-pane" 
                                data-status-id="{{ $statusId }}"
                                type="button" role="tab" 
                                style="border-radius: 8px 8px 0 0;">
                            {{ $group['name'] }} ({{ count($group['items']) }})
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" id="assetStatusTabsContent">
                @foreach ($groupedAssets as $statusId => $group)
                    <div class="tab-pane fade @if($loop->first) show active @endif" 
                         id="status-{{ $statusId }}-pane" 
                         role="tabpanel" 
                         aria-labelledby="status-{{ $statusId }}-tab">
                        
                        <!-- Group Summary Calculator -->
                        @php
                            $summaryCounts = [];
                            $sumTotal = 0;
                            $sumWin = 0;
                            $sumAnti = 0;
                            foreach ($group['items'] as $item) {
                                $key = $item->SUP_NAME ?: ($item->ARTICLE_NAME ?: 'ไม่ระบุกลุ่ม');
                                if (!isset($summaryCounts[$key])) {
                                    $summaryCounts[$key] = [
                                        'count' => 0,
                                        'windows' => 0,
                                        'antivirus' => 0
                                    ];
                                }
                                $summaryCounts[$key]['count']++;
                                $sumTotal++;
                                if ($item->software_list && (stripos($item->software_list, 'Window') !== false || stripos($item->software_list, 'License') !== false)) {
                                    $summaryCounts[$key]['windows']++;
                                    $sumWin++;
                                }
                                if ($item->software_list && stripos($item->software_list, 'Antivirus') !== false) {
                                    $summaryCounts[$key]['antivirus']++;
                                    $sumAnti++;
                                }
                            }
                        @endphp

                        <!-- Asset Code Multi-select Dropdown Filter & Summary Button -->
                        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                            <span class="fw-bold text-muted small text-nowrap">รหัสทรัพย์สิน:</span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-white dropdown-toggle shadow-sm" type="button" 
                                    id="assetFilterDropdown-{{ $statusId }}" data-bs-toggle="dropdown" aria-expanded="false"
                                    style="background-color: white !important; border-radius: 8px !important; font-size: 0.8rem !important; min-width: 250px; text-align: left; display: flex; justify-content: space-between; align-items: center; padding: 0.4rem 0.75rem !important; border: 1px solid #d1d3e2;">
                                    <span class="dropdown-label text-truncate" style="max-width: 200px;">-- ทั้งหมด --</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-multiselect p-0" aria-labelledby="assetFilterDropdown-{{ $statusId }}">
                                    <div class="multiselect-header">
                                        <input type="text" class="form-control form-control-sm multiselect-search mb-2" 
                                            placeholder="ค้นหารหัสทรัพย์สิน..." id="searchAsset-{{ $statusId }}">
                                        <div class="form-check ms-1 mt-1">
                                            <input class="form-check-input select-all-assets" type="checkbox" id="selectAllAsset-{{ $statusId }}" checked data-status-id="{{ $statusId }}">
                                            <label class="form-check-label fw-bold text-primary small" for="selectAllAsset-{{ $statusId }}" style="cursor: pointer;">
                                                เลือกทั้งหมด
                                            </label>
                                        </div>
                                    </div>
                                    <div class="multiselect-item-list" id="assetList-{{ $statusId }}">
                                        @php
                                            $uniqueFsn = [];
                                            $fsnCounts = [];
                                            foreach ($group['items'] as $item) {
                                                if ($item->SUP_FSN) {
                                                    $uniqueFsn[$item->SUP_FSN] = $item->SUP_NAME ?: $item->ARTICLE_NAME;
                                                    $fsnCounts[$item->SUP_FSN] = ($fsnCounts[$item->SUP_FSN] ?? 0) + 1;
                                                }
                                            }
                                            asort($uniqueFsn);
                                        @endphp
                                        @foreach ($uniqueFsn as $fsn => $name)
                                            <div class="multiselect-item selected">
                                                <input type="checkbox" value="{{ $fsn }}" class="asset-checkbox" checked data-status-id="{{ $statusId }}" id="opt-{{ $statusId }}-{{ md5($fsn) }}">
                                                <label for="opt-{{ $statusId }}-{{ md5($fsn) }}"><strong class="text-primary">{{ $fsn }}</strong> : {{ $name }} <span class="badge bg-secondary ms-1">{{ $fsnCounts[$fsn] }} {{ $category->DECLINE_ID == 18 ? 'เครื่อง' : 'รายการ' }}</span></label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="p-2 border-top bg-light text-center">
                                        <small class="text-muted">เลือกได้หลายรายการ</small>
                                    </div>
                                </div>
                            </div>

                            <button class="btn btn-sm btn-outline-primary shadow-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#summaryModal-{{ $statusId }}"
                                style="border-radius: 8px; padding: 0.4rem 0.75rem; font-size: 0.8rem;">
                                <i class="fas fa-chart-pie me-1"></i>ดูสรุปจำนวน
                            </button>

                            @if($category->DECLINE_ID == 18)
                                <button class="btn btn-sm btn-outline-success shadow-sm btn-show-license" 
                                    data-status-id="{{ $statusId }}" 
                                    data-type="windows"
                                    style="border-radius: 8px; padding: 0.4rem 0.75rem; font-size: 0.8rem; margin-left: 5px;">
                                    <i class="fab fa-windows me-1"></i>สรุป Windows ({{ $sumWin }})
                                </button>

                                <button class="btn btn-sm btn-outline-info shadow-sm btn-show-license text-info" 
                                    data-status-id="{{ $statusId }}" 
                                    data-type="antivirus"
                                    style="border-radius: 8px; padding: 0.4rem 0.75rem; font-size: 0.8rem; margin-left: 5px; background-color: transparent; border-color: #0dcaf0;">
                                    <i class="fas fa-shield-virus me-1"></i>สรุป AntiVirus ({{ $sumAnti }})
                                </button>
                            @endif
                        </div>

                        <!-- Summary Modal for this Status -->
                        <div class="modal fade" id="summaryModal-{{ $statusId }}" tabindex="-1" aria-labelledby="summaryModalLabel-{{ $statusId }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                                    <div class="modal-header text-white" style="background: linear-gradient(135deg, #1f3b68 0%, #4e73df 100%); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                        <h5 class="modal-title fw-bold" id="summaryModalLabel-{{ $statusId }}">
                                            <i class="fas fa-list-ol me-2"></i>สรุปจำนวนครุภัณฑ์ ({{ $group['name'] }})
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <!-- Search input for summary table -->
                                        <div class="input-group mb-3">
                                            <span class="input-group-text bg-white border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; border: 1px solid #ced4da;">
                                                <i class="fas fa-search text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control form-control-sm border-start-0 search-summary-table" 
                                                placeholder="ค้นหาชื่อกลุ่มครุภัณฑ์..." 
                                                data-target="summaryTable-{{ $statusId }}"
                                                style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border: 1px solid #ced4da; font-size: 0.85rem; padding: 8px 12px; height: auto;">
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped align-middle border" id="summaryTable-{{ $statusId }}">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 70%;">กลุ่มครุภัณฑ์</th>
                                                        <th class="text-center" style="width: 30%;">จำนวน</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $sumTotal = 0; @endphp
                                                    @foreach ($summaryCounts as $groupName => $counts)
                                                        <tr>
                                                            <td class="fw-semibold text-dark">{{ $groupName }}</td>
                                                            <td class="text-center fw-bold text-primary">{{ $counts['count'] }}</td>
                                                        </tr>
                                                        @php 
                                                            $sumTotal += $counts['count']; 
                                                        @endphp
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light fw-bold" style="border-top: 2px solid #dee2e6;">
                                                    <tr>
                                                        <td>รวมทั้งหมด</td>
                                                        <td class="text-center text-primary fs-6">{{ $sumTotal }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0 p-3">
                                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">ปิด</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="assetTable-{{ $statusId }}" class="table table-hover table-custom align-middle w-100 asset-table-instance">
                                @if($category->DECLINE_ID == 18)
                                    <thead>
                                        <tr>
                                            <th style="width: 8%;">รหัสกลุ่ม</th>
                                            <th style="width: 10%;">กลุ่ม</th>
                                            <th style="width: 8%;">รหัสครุภัณฑ์</th>
                                            <th style="width: 15%;">ชื่อครุภัณฑ์</th>
                                            <th style="width: 7%;">ยี่ห้อ</th>
                                            <th style="width: 7%;">รุ่น</th>
                                            <th style="width: 8%;">วันที่รับเข้า</th>
                                            <th class="text-end" style="width: 8%;">ราคา</th>
                                            <th style="width: 7%;">วิธีได้มา</th>
                                            <th style="width: 7%;">งบที่ใช้</th>
                                            <th style="width: 10%;">ประจำหน่วยงาน</th>
                                            <th class="d-none">ทะเบียนซอฟต์แวร์</th>
                                            <th class="text-center" style="width: 7%;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($group['items'] as $index => $asset)
                                            <tr>
                                                <td class="cell-fsn"><code>{{ $asset->SUP_FSN }}</code></td>
                                                <td>{{ $asset->SUP_NAME ?: '-' }}</td>
                                                <td><code>{{ $asset->ARTICLE_NUM }}</code></td>
                                                <td class="fw-semibold text-dark">{{ $asset->ARTICLE_NAME }}</td>
                                                <td>{{ $asset->BRAND_NAME ?: '-' }}</td>
                                                <td>{{ $asset->MODEL_NAME ?: '-' }}</td>
                                                <td>{{ $asset->thai_receive_date }}</td>
                                                <td class="text-end fw-bold text-dark">
                                                    {{ number_format($asset->PRICE_PER_UNIT, 2) }}
                                                </td>
                                                <td>{{ $asset->BUY_NAME ?: '-' }}</td>
                                                <td>{{ $asset->BUDGET_NAME ?: '-' }}</td>
                                                <td>{{ $asset->HR_DEPARTMENT_SUB_SUB_NAME }}</td>
                                                <td class="d-none">{{ $asset->software_list }}</td>
                                                <td class="text-center">
                                                    @if(in_array($asset->SUP_FSN, ['7440-001-0001', '7440-001-0002', '7440-001-0003', '7440-001-0005', '7440-001-0006', '7440-001-0007', '7440-001-0009', '7440-001-0011']))
                                                        <button class="btn btn-info btn-sm btn-software text-white fw-bold" 
                                                            data-id="{{ $asset->ARTICLE_ID }}" 
                                                            data-num="{{ $asset->ARTICLE_NUM }}"
                                                            data-name="{{ $asset->ARTICLE_NAME }}"
                                                            style="font-size: 11px; padding: 4px 8px; line-height: 1.2; background-color: #00bcd4; border-color: #00bcd4; border-radius: 5px;">
                                                            ทะเบียน<br>Software
                                                        </button>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @else
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 5%;">ลำดับ</th>
                                            <th style="width: 25%;">ชื่อครุภัณฑ์</th>
                                            <th style="width: 12%;">รหัสครุภัณฑ์</th>
                                            <th style="width: 12%;">รหัสทรัพย์สิน</th>
                                            <th style="width: 10%;">วันที่ได้มา</th>
                                            <th style="width: 8%;">แหล่งเงิน</th>
                                            <th style="width: 8%;">วิธีได้มา</th>
                                            <th class="text-end" style="width: 10%;">ราคาทรัพย์สิน</th>
                                            <th style="width: 12%;">ประจำหน่วยงาน</th>
                                            <th style="width: 10%;">อายุการใช้งาน</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $count = 1; @endphp
                                        @foreach ($group['items'] as $index => $asset)
                                            <tr>
                                                <td class="text-center">{{ $count++ }}</td>
                                                <td>
                                                    <span class="fw-semibold text-dark">{{ $asset->ARTICLE_NAME }}</span>
                                                    @if ($asset->BRAND_NAME)
                                                        <div class="small text-muted">ยี่ห้อ: {{ $asset->BRAND_NAME }}</div>
                                                    @endif
                                                </td>
                                                <td><code>{{ $asset->ARTICLE_NUM }}</code></td>
                                                <td class="cell-fsn"><code>{{ $asset->SUP_FSN }}</code></td>
                                                <td>{{ $asset->thai_receive_date }}</td>
                                                <td>{{ $asset->BUDGET_NAME ?: '-' }}</td>
                                                <td>{{ $asset->BUY_NAME ?: '-' }}</td>
                                                <td class="text-end fw-bold text-dark">
                                                    {{ number_format($asset->PRICE_PER_UNIT, 2) }}
                                                </td>
                                                <td>{{ $asset->HR_DEPARTMENT_SUB_SUB_NAME }}</td>
                                                <td class="small">{{ $asset->age_string }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @endif
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Software Modal -->
<div class="modal fade" id="softwareModal" tabindex="-1" aria-labelledby="softwareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1f3b68 0%, #4e73df 100%); border-top-left-radius: 15px; border-top-right-radius: 15px;">
                <h5 class="modal-title fw-bold" id="softwareModalLabel">
                    <i class="fas fa-laptop-code me-2"></i>รายละเอียดทะเบียน Software
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 p-3 bg-light rounded-3" style="border-left: 5px solid #4e73df;">
                    <div class="row">
                        <div class="col-md-6 mb-2 mb-md-0">
                            <span class="text-muted small d-block">รหัสครุภัณฑ์</span>
                            <strong id="modal-article-num" class="text-dark">-</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">ชื่อครุภัณฑ์</span>
                            <strong id="modal-article-name" class="text-dark">-</strong>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle" id="softwareTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 10%;">ลำดับ</th>
                                <th>รายการซ่อมบำรุง/ติดตั้ง Software</th>
                            </tr>
                        </thead>
                        <tbody id="softwareListBody">
                            <!-- Items will be appended here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- License List Modal -->
<div class="modal fade" id="licenseModal" tabindex="-1" aria-labelledby="licenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header text-white" id="licenseModalHeader" style="border-top-left-radius: 15px; border-top-right-radius: 15px; transition: background 0.3s;">
                <h5 class="modal-title fw-bold" id="licenseModalLabel">
                    <i class="fas fa-list me-2"></i>รายชื่อเครื่องคอมพิวเตอร์
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Search input for license table -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white border-end-0" style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; border: 1px solid #ced4da;">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm border-start-0 search-summary-table" 
                        placeholder="ค้นหาข้อมูลเครื่อง..." 
                        data-target="licenseTable"
                        style="border-top-right-radius: 8px; border-bottom-right-radius: 8px; border: 1px solid #ced4da; font-size: 0.85rem; padding: 8px 12px; height: auto;">
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle" id="licenseTable">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 8%;">ลำดับ</th>
                                <th style="width: 22%;">รหัสครุภัณฑ์</th>
                                <th style="width: 35%;">ชื่อครุภัณฑ์</th>
                                <th style="width: 25%;">รายการ Software</th>
                                <th style="width: 15%;">ประจำหน่วยงาน</th>
                            </tr>
                        </thead>
                        <tbody id="licenseListBody">
                            <!-- Items will be appended here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-3">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">ปิด</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Register DataTables custom search filter for asset FSN
            $.fn.dataTable.ext.search.push(
                function( settings, data, dataIndex ) {
                    let tableId = settings.nTable.id;
                    if (!tableId.startsWith('assetTable-')) return true;
                    
                    let statusId = tableId.split('-')[1];
                    let checkedFsn = [];
                    
                    $(`#assetList-${statusId} .asset-checkbox:checked`).each(function() {
                        checkedFsn.push($(this).val());
                    });
                    
                    // For decline_id == 18, FSN is in column 0. For others, it's column 3.
                    let isComputer = "{{ $category->DECLINE_ID }}" == "18";
                    let fsnColumnIndex = isComputer ? 0 : 3;
                    let rowFsn = data[fsnColumnIndex].trim(); 
                    
                    return checkedFsn.includes(rowFsn);
                }
            );

            // Initialize DataTable for each status tab with the standard language translation and excel/print export button placement
            $('.asset-table-instance').each(function() {
                let statusId = $(this).attr('id').split('-')[1];
                let tabButton = document.querySelector(`#status-${statusId}-tab`);
                let tabName = tabButton ? tabButton.innerText.trim().replace(/\s*\(\d+\)/g, '') : '';
                let pdfUrl = "{{ route('backoffice.asset.pdf', $category->DECLINE_ID) }}?status_id=" + statusId;
                let isComputer = "{{ $category->DECLINE_ID }}" == "18";

                $(this).DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [
                        {
                            text: '<i class="fas fa-print me-1"></i> พิมพ์',
                            className: 'btn btn-danger',
                            action: function ( e, dt, node, config ) {
                                window.open(pdfUrl, '_blank');
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                            className: 'btn btn-success',
                            title: 'รายงาน_' + '{{ str_replace(' ', '_', $category->DECLINE_NAME) }}' + '_' + tabName + '_{{ date('Y-m-d') }}',
                            exportOptions: {
                                columns: isComputer ? ':not(:last-child)' : ':visible'
                            }
                        }
                    ],
                    language: {
                        search: "ค้นหา:",
                        lengthMenu: "แสดง _MENU_ รายการ",
                        info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                        paginate: {
                            previous: "ก่อนหน้า",
                            next: "ถัดไป"
                        }
                    },
                    pageLength: 10
                });
            });

            // Prevent dropdown from closing when clicking inside
            $('.dropdown-menu-multiselect').on('click', function (e) {
                e.stopPropagation();
            });

            // Filter search input within dropdowns
            $(`.multiselect-search`).on('input', function() {
                let searchTerm = $(this).val().toLowerCase();
                let statusId = $(this).attr('id').split('-')[1];
                $(`#assetList-${statusId} .multiselect-item`).each(function() {
                    let text = $(this).text().toLowerCase();
                    if (text.includes(searchTerm)) {
                        $(this).css('display', 'flex');
                    } else {
                        $(this).css('display', 'none');
                    }
                });
            });

            // Select All Checkbox Handler
            $('.select-all-assets').on('change', function() {
                let isChecked = this.checked;
                let statusId = $(this).data('status-id');
                let list = $(`#assetList-${statusId}`);
                
                list.find('.multiselect-item').each(function() {
                    if ($(this).css('display') !== 'none') {
                        let cb = $(this).find('.asset-checkbox');
                        cb.prop('checked', isChecked);
                        $(this).toggleClass('selected', isChecked);
                    }
                });
                
                updateAssetDropdownLabel(statusId);
                $('#assetTable-' + statusId).DataTable().draw();
            });

            // Individual Checkbox Handler
            $('.asset-checkbox').on('change', function() {
                let statusId = $(this).data('status-id');
                $(this).closest('.multiselect-item').toggleClass('selected', this.checked);
                
                // Update Select All state
                let list = $(`#assetList-${statusId}`);
                let allChecked = list.find('.asset-checkbox:checked').length === list.find('.asset-checkbox').length;
                $(`#selectAllAsset-${statusId}`).prop('checked', allChecked);

                updateAssetDropdownLabel(statusId);
                $('#assetTable-' + statusId).DataTable().draw();
            });

            function updateAssetDropdownLabel(statusId) {
                let list = $(`#assetList-${statusId}`);
                let checkedCount = list.find('.asset-checkbox:checked').length;
                let totalCount = list.find('.asset-checkbox').length;
                let dropdownBtn = $(`#assetFilterDropdown-${statusId}`);
                let label = dropdownBtn.find('.dropdown-label');
                
                if (checkedCount === 0) {
                    label.text('-- ไม่มีตัวเลือก --');
                } else if (checkedCount === totalCount) {
                    label.text('-- ทั้งหมด --');
                } else {
                    label.text('เลือก (' + checkedCount + ') รหัสทรัพย์สิน');
                }
            }

            // Software Modal handler
            $(document).on('click', '.btn-software', function() {
                let articleId = $(this).data('id');
                let articleNum = $(this).data('num');
                let articleName = $(this).data('name');
                let modal = new bootstrap.Modal(document.getElementById('softwareModal'));
                
                $('#modal-article-num').text(articleNum || '-');
                $('#modal-article-name').text(articleName || '-');
                $('#softwareListBody').html('<tr><td colspan="2" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');
                
                modal.show();

                $.ajax({
                    url: '/backoffice/asset/software/' + articleId,
                    type: 'GET',
                    success: function(response) {
                        if (response && response.length > 0) {
                            let html = '';
                            response.forEach(function(item, index) {
                                html += `<tr>
                                    <td class="text-center fw-bold text-muted">${index + 1}</td>
                                    <td class="fw-semibold text-dark">${item.CARE_LIST_NAME || '-'}</td>
                                </tr>`;
                            });
                            $('#softwareListBody').html(html);
                        } else {
                            $('#softwareListBody').html('<tr><td colspan="2" class="text-center text-muted py-4"><i class="fas fa-info-circle me-1"></i>ไม่พบข้อมูลทะเบียน Software สำหรับครุภัณฑ์นี้</td></tr>');
                        }
                    },
                    error: function() {
                        $('#softwareListBody').html('<tr><td colspan="2" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle me-1"></i>เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
                    }
                });
            });

            // Initialize DataTable for static summary tables in modals
            $('.modal table[id^="summaryTable-"]').each(function() {
                $(this).DataTable({
                    dom: 'rtp', // Only show processing, table, and pagination (no default search input)
                    pageLength: 10,
                    language: {
                        paginate: {
                            previous: "ก่อนหน้า",
                            next: "ถัดไป"
                        }
                    }
                });
            });

            // Live filter search for summary and license tables inside modals via DataTable search API
            $(document).on('input', '.search-summary-table', function() {
                let val = $(this).val();
                let targetTableId = $(this).data('target');
                if ($.fn.DataTable.isDataTable('#' + targetTableId)) {
                    $('#' + targetTableId).DataTable().search(val).draw();
                }
            });

            // Recalculate DataTable column widths when modals are fully opened to prevent headers squishing
            $('.modal').on('shown.bs.modal', function () {
                $(this).find('table').each(function() {
                    if ($.fn.DataTable.isDataTable(this)) {
                        $(this).DataTable().columns.adjust();
                    }
                });
            });

            // License detail modal handler
            $(document).on('click', '.btn-show-license', function() {
                let statusId = $(this).data('status-id');
                let type = $(this).data('type');
                let isWindows = type === 'windows';
                let title = isWindows ? 'ข้อมูลเครื่องคอมพิวเตอร์ที่ติดตั้ง WindowsLicense' : 'ข้อมูลเครื่องคอมพิวเตอร์ที่ติดตั้ง AntiVirus';
                
                // Destroy existing DataTable instances if already initialized
                if ($.fn.DataTable.isDataTable('#licSummaryTable')) {
                    $('#licSummaryTable').DataTable().destroy();
                }
                if ($.fn.DataTable.isDataTable('#licenseTable')) {
                    $('#licenseTable').DataTable().destroy();
                }

                // Clear search input inside license modal and show the first tab by default
                $('.search-summary-table[data-target="licenseTable"]').val('');
                
                // Trigger Bootstrap pill tab reset using class selectors
                let firstTabEl = document.querySelector('#lic-summary-tab');
                if (firstTabEl) {
                    let firstTab = bootstrap.Tab.getOrCreateInstance(firstTabEl);
                    firstTab.show();
                }
                
                // Set style dynamically
                if (isWindows) {
                    $('#licenseModalHeader').removeClass('bg-info').css('background', 'linear-gradient(135deg, #198754 0%, #2e7d32 100%)');
                    $('#licenseModalLabel').html('<i class="fab fa-windows me-2"></i>' + title);
                } else {
                    $('#licenseModalHeader').removeClass('bg-success').css('background', 'linear-gradient(135deg, #0dcaf0 0%, #0288d1 100%)');
                    $('#licenseModalLabel').html('<i class="fas fa-shield-virus me-2"></i>' + title);
                }

                let html = '';
                let count = 1;
                let groupCounts = {};
                let totalLicCount = 0;
                
                // Read rows from the DataTable of this status tab
                let table = $('#assetTable-' + statusId).DataTable();
                table.rows().every(function(rowIdx, tableLoop, rowLoop) {
                    let data = this.data();
                    let groupName = data[1] || 'ไม่ระบุกลุ่ม';
                    let software = data[11] || '';
                    let match = false;
                    
                    if (isWindows && (software.toLowerCase().includes('window') || software.toLowerCase().includes('license'))) {
                        match = true;
                    } else if (!isWindows && software.toLowerCase().includes('antivirus')) {
                        match = true;
                    }
                    
                    if (match) {
                        html += `<tr>
                            <td class="text-center fw-bold text-muted">${count++}</td>
                            <td><code>${data[2]}</code></td>
                            <td class="fw-semibold text-dark">${data[3]}</td>
                            <td><span class="badge ${isWindows ? 'bg-success' : 'bg-info'} text-white" style="white-space: normal; text-align: left;">${data[11]}</span></td>
                            <td>${data[10]}</td>
                        </tr>`;
                        
                        groupCounts[groupName] = (groupCounts[groupName] || 0) + 1;
                        totalLicCount++;
                    }
                });
                
                // Generate summary rows
                let summaryHtml = '';
                for (let groupName in groupCounts) {
                    summaryHtml += `<tr>
                        <td class="fw-semibold text-dark">${groupName}</td>
                        <td class="text-center fw-bold text-primary">${groupCounts[groupName]}</td>
                    </tr>`;
                }
                
                $('#licSummaryListBody').html(summaryHtml);
                $('#licSummaryTotal').text(totalLicCount);
                $('#licenseListBody').html(html);
                
                // Initialize DataTable for dynamic summary table in modal
                $('#licSummaryTable').DataTable({
                    dom: 'rtp',
                    pageLength: 10,
                    language: {
                        zeroRecords: "ไม่พบข้อมูลสรุปสำหรับเงื่อนไขนี้",
                        paginate: {
                            previous: "ก่อนหน้า",
                            next: "ถัดไป"
                        }
                    }
                });

                // Initialize DataTable for dynamic license list table in modal
                $('#licenseTable').DataTable({
                    dom: 'rtp',
                    pageLength: 10,
                    language: {
                        zeroRecords: "ไม่พบข้อมูลเครื่องคอมพิวเตอร์สำหรับเงื่อนไขนี้",
                        paginate: {
                            previous: "ก่อนหน้า",
                            next: "ถัดไป"
                        }
                    }
                });

                let modal = new bootstrap.Modal(document.getElementById('licenseModal'));
                modal.show();
            });
        });
    </script>
@endpush
