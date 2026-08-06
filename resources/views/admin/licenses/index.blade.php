@extends('layouts.admin')

@section('title', 'ระบบจัดการลิขสิทธิ์ (License Management) - SmartData')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
<style>
    /* Flatpickr Modal Z-Index Fix */
    .flatpickr-calendar {
        z-index: 9999999 !important;
    }
    .flatpickr-today-button {
        text-align: center;
        padding: 8px;
        border-top: 1px solid #e6e6e6;
        font-weight: bold;
        color: #198754;
        cursor: pointer;
        background: #f8f9fa;
        font-size: 0.9rem;
    }
    .flatpickr-today-button:hover {
        background: #e2e6ea;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 animate-fade-in">
    <!-- Header -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 fw-bold text-success mb-1">
                <i class="fas fa-key me-2 text-success"></i>ระบบจัดการลิขสิทธิ์ (License Management)
            </h1>
            <p class="text-muted mb-0">ออกใบอนุญาตลิขสิทธิ์สิทธิ์การใช้งานโปรแกรมและควบคุมตัวเครื่องที่เปิดใช้งาน</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-success shadow-sm px-4 fw-bold me-2" data-bs-toggle="modal" data-bs-target="#addLicenseModal">
                <i class="fas fa-plus me-2"></i>ออกลิขสิทธิ์ใหม่
            </button>
            <button class="btn btn-outline-success shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                <i class="fas fa-cubes me-2"></i>เพิ่มโปรแกรมใหม่
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm glass-card border-start border-4 border-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-bold text-uppercase small">โปรแกรมทั้งหมด</span>
                            <h2 class="fw-bold mb-0 mt-1">{{ $programs->count() }}</h2>
                        </div>
                        <div class="icon-shape bg-info-subtle text-info rounded-3 p-3">
                            <i class="fas fa-cubes fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm glass-card border-start border-4 border-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-bold text-uppercase small">Active Licenses</span>
                            <h2 class="fw-bold mb-0 mt-1">{{ $licenses->where('status', 'active')->count() }}</h2>
                        </div>
                        <div class="icon-shape bg-success-subtle text-success rounded-3 p-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm glass-card border-start border-4 border-warning h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-bold text-uppercase small">หมดอายุ (Expired)</span>
                            <h2 class="fw-bold mb-0 mt-1">{{ $licenses->where('status', 'expired')->count() }}</h2>
                        </div>
                        <div class="icon-shape bg-warning-subtle text-warning rounded-3 p-3">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm glass-card border-start border-4 border-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fw-bold text-uppercase small">ระงับใช้งาน (Suspended)</span>
                            <h2 class="fw-bold mb-0 mt-1">{{ $licenses->where('status', 'suspended')->count() }}</h2>
                        </div>
                        <div class="icon-shape bg-danger-subtle text-danger rounded-3 p-3">
                            <i class="fas fa-ban fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="card border-0 shadow-sm glass-card">
        <div class="card-header bg-white border-0 py-3">
            <ul class="nav nav-tabs card-header-tabs" id="licenseTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-success" id="licenses-tab" data-bs-toggle="tab" data-bs-target="#licenses-tab-pane" type="button" role="tab" aria-controls="licenses-tab-pane" aria-selected="true">
                        <i class="fas fa-key me-2"></i>รายการสิทธิ์ใช้งาน (Licenses)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-success" id="programs-tab" data-bs-toggle="tab" data-bs-target="#programs-tab-pane" type="button" role="tab" aria-controls="programs-tab-pane" aria-selected="false">
                        <i class="fas fa-cubes me-2"></i>ระบบ/โปรแกรมทั้งหมด (Programs)
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="licenseTabsContent">
                
                <!-- Licenses Tab Pane -->
                <div class="tab-pane fade show active" id="licenses-tab-pane" role="tabpanel" aria-labelledby="licenses-tab" tabindex="0">
                    <!-- Filters and Search -->
                    <form action="{{ route('license.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">ค้นหา</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-0" placeholder="รหัสคีย์, ชื่อลูกค้า, รหัส รพ., Hardware ID" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted small">คัดกรองตามโปรแกรม</label>
                            <select name="program_id" class="form-select border-0 shadow-sm">
                                <option value="">ทุกโปรแกรม...</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-success shadow-sm px-4 fw-bold w-100">
                                <i class="fas fa-filter me-2"></i>กรองข้อมูล
                            </button>
                            <a href="{{ route('license.index') }}" class="btn btn-light border shadow-sm px-3 w-100">ล้างตัวกรอง</a>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ระบบ / โปรแกรม</th>
                                    <th>รหัสคีย์ (License Key)</th>
                                    <th>ลูกค้า / หน่วยงาน</th>
                                    <th>Hardware ID / เครื่อง</th>
                                    <th>สถานะ</th>
                                    <th>วันเริ่ม / หมดอายุ</th>
                                    <th class="text-end">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($licenses as $license)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $license->program->name }}</div>
                                            <small class="badge bg-secondary-subtle text-secondary mb-1">{{ $license->program->code }}</small>
                                            
                                            <!-- License Type and Modules Badges -->
                                            <div class="mt-1 d-flex flex-wrap gap-1" style="max-width: 250px;">
                                                @if(($license->license_type ?? 'full') === 'full')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;"><i class="fas fa-gem me-1"></i>Full Access</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 0.7rem;"><i class="fas fa-cubes me-1"></i>Modules: {{ $license->activatedModules->count() }}</span>
                                                    @foreach($license->activatedModules as $mod)
                                                        <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;" title="{{ $mod->code }}">{{ $mod->name }}</span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <code class="text-success fw-bold me-2">{{ $license->license_key }}</code>
                                                <button class="btn btn-sm btn-link text-muted p-0" onclick="copyToClipboard('{{ $license->license_key }}')" title="คัดลอกรหัสคีย์">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $license->customer_name }}</div>
                                            @if($license->hcode)
                                                <small class="text-muted"><i class="fas fa-hospital me-1"></i>รหัส รพ.: <strong>{{ $license->hcode }}</strong></small>
                                            @else
                                                <small class="text-warning-emphasis">ไม่ล็อกหน่วยงาน</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($license->hardware_id)
                                                <span class="badge bg-dark-subtle text-dark-emphasis font-monospace" style="font-size: 0.8rem;">
                                                    <i class="fas fa-desktop me-1"></i>{{ Str::limit($license->hardware_id, 15) }}
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning-emphasis"><i class="fas fa-lock-open me-1"></i>รอ Activate เครื่องแรก</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($license->status === 'active' && !$license->isExpired())
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i>Active</span>
                                            @elseif($license->status === 'pending')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill"><i class="fas fa-clock me-1"></i>Pending</span>
                                            @elseif($license->status === 'expired' || $license->isExpired())
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill"><i class="fas fa-exclamation-triangle me-1"></i>Expired</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill"><i class="fas fa-ban me-1"></i>Suspended</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small">
                                                <span>เริ่ม: {{ $license->activated_at ? $license->activated_at->format('d/m/Y') : 'ยังไม่เปิดใช้งาน' }}</span>
                                                <br>
                                                <span class="fw-bold {{ $license->isExpired() ? 'text-danger' : 'text-muted' }}">
                                                    หมดอายุ: {{ $license->expired_at ? $license->expired_at->format('d/m/Y') : 'Lifetime (ไม่มีวันหมดอายุ)' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <button class="btn btn-sm btn-light border shadow-xs" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editLicenseModal{{ $license->id }}">
                                                    <i class="fas fa-edit text-primary"></i>
                                                </button>
                                                <form action="{{ route('license.destroy', $license->id) }}" method="POST" onsubmit="return confirm('ยืนยันลบสิทธิ์ใช้งานคีย์นี้หรือไม่?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border shadow-xs">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="fas fa-key fa-3x mb-3 text-muted-opacity"></i>
                                            <p class="mb-0">ยังไม่มีการออกคีย์ลิขสิทธิ์สิทธิ์ในระบบ</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $licenses->appends(request()->query())->links() }}
                    </div>
                </div>

                <!-- Programs Tab Pane -->
                <div class="tab-pane fade" id="programs-tab-pane" role="tabpanel" aria-labelledby="programs-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ชื่อโปรแกรม (Name)</th>
                                    <th>รหัสโปรแกรม (Program Code)</th>
                                    <th>คำอธิบาย (Description)</th>
                                    <th>ภาษาที่เขียน (Platform)</th>
                                    <th class="text-center">จำนวนคีย์ลิขสิทธิ์</th>
                                    <th class="text-end">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programs as $program)
                                    <tr>
                                        <td class="fw-bold text-dark">{{ $program->name }}</td>
                                        <td>
                                            <code class="bg-secondary-subtle text-secondary-emphasis px-2 py-1 rounded">{{ $program->code }}</code>
                                        </td>
                                        <td class="text-muted small">{{ $program->description ?: 'ไม่มีรายละเอียดโปรแกรม' }}</td>
                                        <td>
                                            @if(($program->language ?? 'go') === 'go')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="fab fa-golang me-1"></i>Go (Golang)</span>
                                            @elseif($program->language === 'laravel')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fab fa-laravel me-1"></i>Laravel (PHP)</span>
                                            @elseif($program->language === 'python')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fab fa-python me-1"></i>Python</span>
                                            @elseif($program->language === 'csharp')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 font-monospace">C# (.NET)</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">{{ strtoupper($program->language) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success rounded-pill px-3">{{ $program->licenses_count }} คีย์</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <button class="btn btn-sm btn-light border shadow-xs" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#guideModal{{ $program->id }}"
                                                    title="คู่มือเชื่อมต่อ AI (Antigravity)">
                                                    <i class="fas fa-robot text-info"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light border shadow-xs" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#programModulesModal{{ $program->id }}"
                                                    title="จัดการโมดูลย่อย">
                                                    <i class="fas fa-puzzle-piece text-warning"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light border shadow-xs" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editProgramModal{{ $program->id }}">
                                                    <i class="fas fa-edit text-primary"></i>
                                                </button>
                                                <form action="{{ route('license.programs.destroy', $program->id) }}" method="POST" onsubmit="return confirm('ยืนยันลบระบบ/โปรแกรมนี้? (ต้องไม่มีสิทธิ์ License ค้างอยู่)')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border shadow-xs">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-cubes fa-3x mb-3 text-muted-opacity"></i>
                                            <p class="mb-0">ยังไม่มีระบบโปรแกรมลงทะเบียนในระบบ</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal: Add License -->
<div class="modal fade" id="addLicenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-gradient-success-custom text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>ออกคีย์ลิขสิทธิ์สิทธิ์การใช้งานใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('license.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">เลือกระบบ / โปรแกรม <span class="text-danger">*</span></label>
                            <select name="program_id" class="form-select border shadow-xs" required>
                                <option value="">-- โปรดเลือกโปรแกรม --</option>
                                @foreach($programs as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">ประเภทลิขสิทธิ์</label>
                            <select name="license_type" id="add_license_type" class="form-select border shadow-xs" onchange="toggleAddModules()">
                                <option value="full">Full License (เข้าถึงทุกโมดูล)</option>
                                <option value="module">Module License (เลือกเฉพาะบางโมดูล)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">ชื่อลูกค้า / โรงพยาบาลปลายทาง <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control border shadow-xs" placeholder="เช่น รพ.ตัวอย่างการแพทย์" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">วันหมดอายุ (เว้นว่าง = ตลอดชีพ)</label>
                            <input type="text" name="expired_at" id="add_expired_at" class="form-control border shadow-xs bg-white" placeholder="เลือกวันหมดอายุ" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-muted">รหัสโรงพยาบาล (HCODE)</label>
                            <input type="text" name="hcode" class="form-control border shadow-xs" placeholder="เช่น 10702">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-muted">Hardware ID</label>
                            <input type="text" name="hardware_id" class="form-control border shadow-xs" placeholder="ยึดติดเครื่อง (เว้นว่าง = ล็อคเครื่องแรก)">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold small text-muted">สถานะเริ่มต้น</label>
                            <select name="status" class="form-select border shadow-xs">
                                <option value="active">Active</option>
                                <option value="pending">Pending (รอเปิดใช้งาน)</option>
                                <option value="suspended">Suspended</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic program modules list container -->
                    <div id="add_modules_section" class="mb-3 d-none border rounded p-3 bg-light" style="max-height: 280px; overflow-y: auto;">
                        <label class="form-label fw-bold small text-muted mb-2">เลือกโมดูลย่อยที่เปิดสิทธิ์การใช้งาน</label>
                        
                        @foreach($programs as $p)
                            <div class="program-modules-list d-none" id="add_program_modules_{{ $p->id }}">
                                @if($p->modules->count() > 0)
                                    @foreach($p->modules as $mod)
                                        <div class="row align-items-center mb-3 g-2 py-2 border-bottom">
                                            <div class="col-lg-5 col-md-12">
                                                <div class="form-check">
                                                    <input class="form-check-input module-checkbox" type="checkbox" name="modules[{{ $mod->id }}][active]" value="1" id="add_mod_{{ $mod->id }}" onchange="toggleModuleFields('add', 0, {{ $mod->id }})">
                                                    <label class="form-check-label text-dark small fw-bold" for="add_mod_{{ $mod->id }}">
                                                        {{ $mod->name }}
                                                    </label>
                                                </div>
                                                <div class="ps-4"><code class="small text-danger" style="font-size:0.75rem;">{{ $mod->code }}</code></div>
                                            </div>
                                            <div class="col-lg-7 col-md-12">
                                                <div class="d-flex align-items-center gap-2 ps-4 ps-lg-0 d-none" id="add_settings_0_{{ $mod->id }}">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="small text-muted" style="font-size:0.75rem;">สถานะ:</span>
                                                        <select name="modules[{{ $mod->id }}][status]" class="form-select form-select-sm border shadow-xs" style="font-size:0.8rem; width:110px;">
                                                            <option value="active" selected>Active</option>
                                                            <option value="suspended">Suspended</option>
                                                            <option value="expired">Expired</option>
                                                            <option value="pending">Pending</option>
                                                        </select>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1 flex-grow-1">
                                                        <span class="small text-muted" style="font-size:0.75rem;">หมดอายุ:</span>
                                                        <input type="text" name="modules[{{ $mod->id }}][expired_at]" class="form-control form-control-sm border shadow-xs bg-white license-module-expired-at" style="font-size:0.8rem;" placeholder="วันหมดอายุย่อย" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted small d-block"><i class="fas fa-info-circle me-1"></i>ไม่มีโมดูลย่อยสำหรับโปรแกรมนี้ (ใช้สิทธิ์ Full เสมอ)</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">หมายเหตุรายละเอียดเพิ่มเติม</label>
                        <textarea name="notes" class="form-control border shadow-xs" rows="2" placeholder="รายละเอียดดีล รายละเอียดการติดต่อ หรือข้อตกลง..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">สร้าง License Key</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Program -->
<div class="modal fade" id="addProgramModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-gradient-success-custom text-white border-0" style="border-radius: 15px 15px 0 0;">
                <h5 class="modal-title fw-bold"><i class="fas fa-cubes me-2"></i>ลงทะเบียนระบบ / โปรแกรมใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('license.programs.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ชื่อระบบ / โปรแกรม <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control border shadow-xs" placeholder="เช่น SmartReport Pro หรือบอทส่งคิวไลน์" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">รหัสอ้างอิงโปรแกรม (Program Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control border shadow-xs" placeholder="เช่น smartreport_pro" required>
                        <small class="text-muted small">ต้องใช้ภาษาอังกฤษตัวพิมพ์เล็ก ไม่มีเว้นวรรค (ตัวอย่าง: `my_tool_code` สำหรับส่งตรวจลิขสิทธิ์ผ่าน API)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ประเภท/แพลตฟอร์มโปรแกรม <span class="text-danger">*</span></label>
                        <select name="language" class="form-select border shadow-xs" required>
                            <option value="laravel">Laravel</option>
                            <option value="go">Go</option>
                            <option value="python">Python</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">รายละเอียด / คำอธิบายเพิ่มเติม</label>
                        <textarea name="description" class="form-control border shadow-xs" rows="3" placeholder="ระบุขอบเขตการใช้งานหรือรายละเอียดของระบบนี้..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit License Modals (Rendered outside to avoid table layout overflow issues) -->
@foreach($licenses as $license)
    <div class="modal fade" id="editLicenseModal{{ $license->id }}" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-success text-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>แก้ไข License</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('license.update', $license->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">ระบบ / โปรแกรม</label>
                                <input type="text" class="form-control bg-light border-0" value="{{ $license->program->name }} ({{ $license->program->code }})" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">ประเภทลิขสิทธิ์</label>
                                <select name="license_type" id="edit_license_type_{{ $license->id }}" class="form-select border shadow-xs" onchange="toggleEditModules({{ $license->id }})">
                                    <option value="full" {{ ($license->license_type ?? 'full') === 'full' ? 'selected' : '' }}>Full License (เข้าถึงทุกโมดูล)</option>
                                    <option value="module" {{ ($license->license_type ?? 'full') === 'module' ? 'selected' : '' }}>Module License (เลือกเฉพาะบางโมดูล)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">รหัสคีย์ (License Key)</label>
                                <code class="d-block bg-light p-2 rounded fw-bold text-center text-success border" style="line-height: 24px;">{{ $license->license_key }}</code>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">ชื่อลูกค้า / หน่วยงาน <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control border shadow-xs" value="{{ $license->customer_name }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold small text-muted">รหัสโรงพยาบาล (HCODE)</label>
                                <input type="text" name="hcode" class="form-control border shadow-xs" placeholder="เช่น 10702" value="{{ $license->hcode }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold small text-muted">Hardware ID</label>
                                <input type="text" name="hardware_id" class="form-control border shadow-xs" placeholder="ยึดกับตัวเครื่อง" value="{{ $license->hardware_id }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold small text-muted">สถานะใช้งาน</label>
                                <select name="status" class="form-select border shadow-xs">
                                    <option value="active" {{ $license->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ $license->status === 'pending' ? 'selected' : '' }}>Pending (รอเปิดใช้งาน)</option>
                                    <option value="suspended" {{ $license->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="expired" {{ $license->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold small text-muted">วันหมดอายุ (เว้นว่าง = ตลอดชีพ)</label>
                                <input type="text" name="expired_at" class="form-control border shadow-xs bg-white license-edit-expired-at" data-value="{{ $license->expired_at ? $license->expired_at->format('Y-m-d') : '' }}" value="{{ $license->expired_at ? $license->expired_at->format('Y-m-d') : '' }}" placeholder="เลือกวันหมดอายุ" readonly>
                            </div>
                        </div>

                        <div id="edit_modules_section_{{ $license->id }}" class="mb-3 {{ ($license->license_type ?? 'full') === 'module' ? '' : 'd-none' }} border rounded p-3 bg-light" style="max-height: 280px; overflow-y: auto;">
                            <label class="form-label fw-bold small text-muted mb-2">เลือกโมดูลย่อยที่เปิดสิทธิ์การใช้งาน</label>
                            
                            @if($license->program->modules->count() > 0)
                                @foreach($license->program->modules as $mod)
                                    <div class="row align-items-center mb-3 g-2 py-2 border-bottom">
                                        <div class="col-lg-5 col-md-12">
                                            <div class="form-check">
                                                <input class="form-check-input module-checkbox" type="checkbox" name="modules[{{ $mod->id }}][active]" value="1" id="edit_mod_{{ $license->id }}_{{ $mod->id }}" {{ $license->activatedModules->contains($mod->id) ? 'checked' : '' }} onchange="toggleModuleFields('edit', {{ $license->id }}, {{ $mod->id }})">
                                                <label class="form-check-label text-dark small fw-bold" for="edit_mod_{{ $license->id }}_{{ $mod->id }}">
                                                    {{ $mod->name }}
                                                </label>
                                            </div>
                                            <div class="ps-4"><code class="small text-danger" style="font-size:0.75rem;">{{ $mod->code }}</code></div>
                                        </div>
                                        <div class="col-lg-7 col-md-12">
                                            @php
                                                $pivot = $license->activatedModules->find($mod->id)?->pivot;
                                                $pivotStatus = $pivot ? $pivot->status : 'active';
                                                $pivotExpired = $pivot && $pivot->expired_at ? $pivot->expired_at->format('Y-m-d') : '';
                                            @endphp
                                            <div class="d-flex align-items-center gap-2 ps-4 ps-lg-0 {{ $license->activatedModules->contains($mod->id) ? '' : 'd-none' }}" id="edit_settings_{{ $license->id }}_{{ $mod->id }}">
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="small text-muted" style="font-size:0.75rem;">สถานะ:</span>
                                                    <select name="modules[{{ $mod->id }}][status]" class="form-select form-select-sm border shadow-xs" style="font-size:0.8rem; width:110px;">
                                                        <option value="active" {{ $pivotStatus === 'active' ? 'selected' : '' }}>Active</option>
                                                        <option value="suspended" {{ $pivotStatus === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                        <option value="expired" {{ $pivotStatus === 'expired' ? 'selected' : '' }}>Expired</option>
                                                        <option value="pending" {{ $pivotStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    </select>
                                                </div>
                                                <div class="d-flex align-items-center gap-1 flex-grow-1">
                                                    <span class="small text-muted" style="font-size:0.75rem;">หมดอายุ:</span>
                                                    <input type="text" name="modules[{{ $mod->id }}][expired_at]" class="form-control form-control-sm border shadow-xs bg-white license-module-expired-at" data-value="{{ $pivotExpired }}" value="{{ $pivotExpired }}" style="font-size:0.8rem;" placeholder="วันหมดอายุย่อย" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-muted small d-block"><i class="fas fa-info-circle me-1"></i>ไม่มีโมดูลย่อยสำหรับโปรแกรมนี้ (ใช้สิทธิ์ Full เสมอ)</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">หมายเหตุ / ข้อมูลเพิ่มเติม</label>
                            <textarea name="notes" class="form-control border shadow-xs" rows="2" placeholder="เช่น ช่องทางติดต่อลูกค้า รายละเอียดดีล">{{ $license->notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Edit Program Modals (Rendered outside to avoid table layout overflow issues) -->
@foreach($programs as $program)
    <!-- Modal: Manage Program Modules -->
    <div class="modal fade" id="programModulesModal{{ $program->id }}" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-gradient-success-custom text-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-puzzle-piece me-2"></i>จัดการโมดูลย่อยสำหรับโปรแกรม: {{ $program->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Current modules table -->
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-list me-2"></i>โมดูลย่อยทั้งหมดของโปรแกรมนี้ ({{ $program->modules->count() }})</h6>
                    <div class="table-responsive mb-4 border rounded bg-white" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>รหัสโมดูล (Module Code)</th>
                                    <th>ชื่อโมดูล (Module Name)</th>
                                    <th>คำอธิบาย</th>
                                    <th class="text-end" style="width: 80px;">ลบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($program->modules as $mod)
                                    <tr>
                                        <td><code class="bg-secondary-subtle text-secondary-emphasis px-2 py-1 rounded">{{ $mod->code }}</code></td>
                                        <td class="fw-bold text-dark">{{ $mod->name }}</td>
                                        <td class="text-muted small">{{ $mod->description ?: '-' }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('license.modules.destroy', $mod->id) }}" method="POST" onsubmit="return confirm('ยืนยันลบโมดูลนี้? (สิทธิ์ที่เชื่อมกับลูกค้าจะถูกลบออกไปด้วย)')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border">
                                                    <i class="fas fa-trash-alt text-danger"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            <i class="fas fa-info-circle me-1"></i>ยังไม่มีโมดูลย่อยถูกเพิ่มเข้ามา
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">

                    <!-- Form to add new module -->
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-plus-circle me-2"></i>ลงทะเบียนโมดูลย่อยใหม่</h6>
                    <form action="{{ route('license.programs.modules.store', $program->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">รหัสโมดูล (Module Code) <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control border shadow-xs" placeholder="เช่น export_ssop" required>
                                <small class="text-muted" style="font-size: 0.75rem;">ภาษาอังกฤษพิมพ์เล็ก ไม่มีเว้นวรรค (ใช้สำหรับ API ตรวจสอบ)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">ชื่อโมดูล (Module Name) <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control border shadow-xs" placeholder="เช่น ระบบส่งออกข้อมูลประกันสังคม (SSOP)" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">รายละเอียด / คำอธิบาย</label>
                            <textarea name="description" class="form-control border shadow-xs" rows="2" placeholder="ระบุรายละเอียดลักษณะงานหรือขอบเขตของโมดูลย่อยนี้..."></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success px-4 fw-bold"><i class="fas fa-save me-2"></i>บันทึกโมดูล</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProgramModal{{ $program->id }}" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-success text-white border-0" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>แก้ไขโปรแกรม</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('license.programs.update', $program->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">ชื่อระบบ / ชื่อโปรแกรม <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control border shadow-xs" value="{{ $program->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">รหัสระบบ (Program Code) <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control border shadow-xs" value="{{ $program->code }}" placeholder="เช่น ipd_check_tool" required>
                            <small class="text-muted-opacity">ห้ามเว้นวรรค ใช้ตัวพิมพ์เล็กและขีดล่าง (snake_case) ในการเรียกใช้ API เช็คคีย์</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">ประเภท/แพลตฟอร์มโปรแกรม <span class="text-danger">*</span></label>
                            <select name="language" class="form-select border shadow-xs" required>
                                <option value="laravel" {{ ($program->language ?? '') === 'laravel' ? 'selected' : '' }}>Laravel</option>
                                <option value="go" {{ ($program->language ?? 'go') === 'go' ? 'selected' : '' }}>Go</option>
                                <option value="python" {{ ($program->language ?? '') === 'python' ? 'selected' : '' }}>Python</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">คำอธิบาย</label>
                            <textarea name="description" class="form-control border shadow-xs" rows="3">{{ $program->description }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">อัปเดตข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Guide Modals (Rendered outside to avoid table layout overflow issues) -->
@foreach($programs as $program)
    <div class="modal fade" id="guideModal{{ $program->id }}" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-info text-white border-0" style="border-radius: 15px 15px 0 0; background: linear-gradient(135deg, #0268c7 0%, #17a6a7 100%) !important;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-robot me-2"></i>คู่มือเชื่อมต่อด้วย AI สำหรับโปรแกรม: {{ $program->name }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <h6 class="fw-bold text-success mb-3"><i class="fas fa-link me-2"></i>API Endpoint สำหรับระบบของคุณ</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-1">1. ตรวจสอบสิทธิ์ใช้งาน (Verify License)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted font-monospace small">POST</span>
                            <input type="text" class="form-control bg-light font-monospace small" value="{{ url('/license/verify') }}" readonly id="url_verify_{{ $program->id }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ url('/license/verify') }}')"><i class="far fa-copy"></i></button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted mb-1">2. ส่งคำขอลงทะเบียนเปิดสิทธิ์คีย์ (Request Activation)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted font-monospace small">POST</span>
                            <input type="text" class="form-control bg-light font-monospace small" value="{{ url('/license/request') }}" readonly id="url_request_{{ $program->id }}">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ url('/license/request') }}')"><i class="far fa-copy"></i></button>
                        </div>
                    </div>

                    <h6 class="fw-bold text-success mb-2"><i class="fas fa-comment-dots me-2"></i>AI Command Prompt สำหรับ Antigravity</h6>
                    
                    @if(($program->language ?? 'go') === 'laravel')
                        <p class="text-muted small mb-2">คัดลอกคำสั่งด้านล่างไปส่งให้ **Google Antigravity** ในโฟลเดอร์โปรเจกต์ Laravel ของคุณ เพื่อให้ AI เขียนระบบลิขสิทธิ์ฝั่ง Client ให้โดยอัตโนมัติ:</p>
                        <div class="position-relative bg-light rounded p-3 border">
                            <textarea class="form-control border-0 bg-transparent font-monospace small text-dark" id="prompt_text_{{ $program->id }}" rows="12" readonly style="resize: none; font-size:0.85rem;">คุณคือ Antigravity AI ช่วยเขียนระบบตรวจสอบคีย์ลิขสิทธิ์ (License Verification) ด้วยภาษา PHP (Laravel) สำหรับระบบ "{{ $program->name }}" (รหัสโปรแกรม: {{ $program->code }}) โดยมีแนวทางดังนี้:
@if($program->modules->count() > 0)
(ข้อมูลเพิ่มเติม - รหัสโมดูลย่อยที่ระบบนี้มีให้เลือกใช้งานในปัจจุบัน:
@foreach($program->modules as $mod)
- {{ $mod->code }} (ชื่อ: {{ $mod->name }})
@endforeach
)
@endif

1. ดึงรหัสโรงพยาบาล: เขียนโค้ดดึงค่ารหัสโรงพยาบาล 5 หลัก (HCODE) จากฐานข้อมูลหรือการตั้งค่าของระบบ (เช่น ดึงจากตาราง opdconfig หรือไฟล์ Config ของแอปพลิเคชัน)
2. การลงทะเบียนคีย์ลิขสิทธิ์ (Request Activation): เขียน Service class ส่ง HTTP POST (โดยใช้ Laravel Http Client) ไปที่ URL: {{ url('/license/request') }}
   แนบ JSON: program_code: "{{ $program->code }}", hcode: [ค่ารหัสโรงพยาบาล HCODE], customer_name: [ระบุชื่อโรงพยาบาล/หน่วยงาน], customer_email: [ระบุอีเมล]
   - หากส่งสำเร็จและได้สถานะ "pending" ให้แสดงข้อความแจ้งเตือนสถานะรออนุมัติและแสดงรหัสคีย์ที่ได้รับ
3. การตรวจสอบสิทธิ์ใช้งาน (Verify License): เขียนคำสั่งส่ง HTTP POST (Laravel Http Client) ไปที่ URL: {{ url('/license/verify') }}
   แนบ JSON: license_key: [รหัสคีย์], program_code: "{{ $program->code }}", hcode: [ค่ารหัสโรงพยาบาล HCODE]
   - หากสถานะเป็น "active" ให้เก็บผลการตรวจสอบลิขสิทธิ์ลงใน Laravel Cache เพื่อป้องกันการ bypass/ดักแก้ผลลัพธ์
4. การทำ Offline Cache: ตรวจสอบสถานะผ่าน Laravel Cache ก่อนเสมอกลางโปรแกรม เพื่อป้องกันความเร็วตก และตั้งเวลาดึงข้อมูลใหม่จาก API เซิร์ฟเวอร์ทุกๆ 7 วัน
5. สร้าง Laravel Middleware (เช่น CheckLicense) สำหรับคัดกรอง Routes ทั้งหมด หากลิขสิทธิ์ถูกยกเลิก หมดอายุ หรือถูกระงับ ให้แสดงหน้าจอแจ้งเตือนลิขสิทธิ์ผิดพลาดอย่างสวยงาม
6. การเช็กสิทธิ์ฟังก์ชันย่อย (Module Check): ตรวจเช็กตัวแปร license_type ใน JSON ผลลัพธ์:
   - หาก license_type เท่ากับ "full" ให้ผ่านเข้าใช้งานได้ทุกฟังก์ชัน
   - หาก license_type เท่ากับ "module" ให้เช็กว่ารหัสโมดูลที่ต้องการใช้งาน (เช่น export_ssop) มีระบุอยู่ในอาเรย์ modules ของ JSON หรือไม่ หากไม่มีให้ปิดการเข้าถึงฟังก์ชันนั้น</textarea>
                            <button class="btn btn-sm btn-success position-absolute end-0 bottom-0 m-3" onclick="copyPrompt('prompt_text_{{ $program->id }}')">
                                <i class="far fa-copy me-1"></i> คัดลอก Prompt สั่งงาน AI
                            </button>
                        </div>
                    @elseif(($program->language ?? 'go') === 'python')
                        <p class="text-muted small mb-2">คัดลอกคำสั่งด้านล่างไปส่งให้ **Google Antigravity** ในโฟลเดอร์โปรเจกต์ Python ของคุณ เพื่อให้ AI เขียนระบบลิขสิทธิ์ฝั่ง Client ให้โดยอัตโนมัติ:</p>
                        <div class="position-relative bg-light rounded p-3 border">
                            <textarea class="form-control border-0 bg-transparent font-monospace small text-dark" id="prompt_text_{{ $program->id }}" rows="12" readonly style="resize: none; font-size:0.85rem;">คุณคือ Antigravity AI ช่วยเขียนระบบตรวจสอบคีย์ลิขสิทธิ์ (License Verification) ด้วยภาษา Python สำหรับโปรแกรม "{{ $program->name }}" (รหัสโปรแกรม: {{ $program->code }}) โดยมีแนวทางดังนี้:
@if($program->modules->count() > 0)
(ข้อมูลเพิ่มเติม - รหัสโมดูลย่อยที่ระบบนี้มีให้เลือกใช้งานในปัจจุบัน:
@foreach($program->modules as $mod)
- {{ $mod->code }} (ชื่อ: {{ $mod->name }})
@endforeach
)
@endif

1. ฟังก์ชันดึง Machine Signature: เขียนฟังก์ชันดึงค่า Hardware ID ของเครื่องผู้ใช้งาน (เช่น ดึง CPU ID หรือ Motherboard UUID) ด้วย Python ที่รันได้ทั้งบน Windows และ Linux
2. การลงทะเบียนคีย์ลิขสิทธิ์ (Request Activation): ส่ง HTTP POST (โดยใช้ไลบรารี requests) ไปที่ URL: {{ url('/license/request') }}
   แนบ JSON: program_code: "{{ $program->code }}", hardware_id: [ค่า Hardware ID], customer_name: [ระบุชื่อผู้ใช้], customer_email: [ระบุอีเมล]
3. การตรวจสอบสิทธิ์ใช้งาน (Verify License): ส่ง HTTP POST ไปที่ URL: {{ url('/license/verify') }}
   แนบ JSON: license_key: [รหัสคีย์], program_code: "{{ $program->code }}", hardware_id: [ค่า Hardware ID]
   - หากสถานะเป็น "active" ให้เก็บข้อมูลลิขสิทธิ์และ signature ลงในแคชท้องถิ่นในรูปแบบไฟล์ที่เข้ารหัส (เช่น เข้ารหัสด้วย cryptography หรือเซฟเป็น hashed json)
   - ตรวจสอบ signature เพื่อยืนยันว่าผลตอบกลับจริงมาจากเซิร์ฟเวอร์ของเรา
4. การทำงานออฟไลน์ (Offline Cache): ตรวจสอบความถูกต้องจากไฟล์แคชก่อน และจะทำการเชื่อมต่อเพื่อซิงก์ข้อมูลกับเซิร์ฟเวอร์ออนไลน์ทุกๆ 7 วัน
5. เขียนคลาสหรือโมดูลตรวจสิทธิ์ใช้งาน และเชื่อมต่อกับอินเทอร์เฟซโปรแกรมของคุณ หากพบว่าสิทธิ์ไม่ผ่าน ให้แสดงหน้าจอปิดกั้นการใช้งานระบบ
6. ตรวจสอบระดับโมดูลย่อย: ใน JSON ตรวจสอบตัวแปร license_type:
   - หากมีค่าเป็น "full" ให้สิทธิ์การใช้งานผ่านทุกโมดูล
   - หากมีค่าเป็น "module" ให้เช็กสิทธิ์เฉพาะเจาะจงว่าชื่อรหัสโมดูลที่ต้องการใช้งาน (เช่น export_ssop) มีอยู่ภายในลิสต์ modules หรือไม่ เพื่อใช้ล็อกสิทธิ์ฟีเจอร์ย่อยของโปรเจกต์</textarea>
                            <button class="btn btn-sm btn-success position-absolute end-0 bottom-0 m-3" onclick="copyPrompt('prompt_text_{{ $program->id }}')">
                                <i class="far fa-copy me-1"></i> คัดลอก Prompt สั่งงาน AI
                            </button>
                        </div>
                    @else
                        <p class="text-muted small mb-2">คัดลอกคำสั่งด้านล่างไปส่งให้ **Google Antigravity** ในโฟลเดอร์โปรเจกต์ Go ของคุณ เพื่อให้ AI เขียนโค้ดระบบลิขสิทธิ์ฝั่ง Client ให้โดยอัตโนมัติ:</p>
                        <div class="position-relative bg-light rounded p-3 border">
                            <textarea class="form-control border-0 bg-transparent font-monospace small text-dark" id="prompt_text_{{ $program->id }}" rows="12" readonly style="resize: none; font-size:0.85rem;">คุณคือ Antigravity AI ช่วยเขียนระบบตรวจสอบคีย์ลิขสิทธิ์ (License Verification) ด้วยภาษา Go (Golang) สำหรับโปรแกรม "{{ $program->name }}" (รหัส: {{ $program->code }}) โดยให้ปฏิบัติดังนี้:
@if($program->modules->count() > 0)
(ข้อมูลเพิ่มเติม - รหัสโมดูลย่อยที่ระบบนี้มีให้เลือกใช้งานในปัจจุบัน:
@foreach($program->modules as $mod)
- {{ $mod->code }} (ชื่อ: {{ $mod->name }})
@endforeach
)
@endif

1. เขียนฟังก์ชันดึง Hardware ID (เช่น disk serial number, cpu id หรือ mac address) ที่ทำงานได้เสถียรทั้งบน Windows และ Linux
2. เขียนฟังก์ชันลงทะเบียน (Request Activation): ส่ง HTTP POST ไปที่ URL: {{ url('/license/request') }} 
   แนบ JSON: program_code: "{{ $program->code }}", hardware_id: [ค่าที่ดึงได้], customer_name: [ระบุชื่อผู้ใช้], customer_email: [ระบุอีเมล]
   - หาก API ตอบกลับสถานะ "pending" ให้แสดงรหัสคีย์ที่ขอ และบอกผู้ใช้นำคีย์นี้ไปแจ้ง Admin
3. เขียนฟังก์ชันตรวจสอบสิทธิ์ (Verify License): ส่ง HTTP POST ไปที่ URL: {{ url('/license/verify') }}
   แนบ JSON: license_key: [รหัสคีย์ลิขสิทธิ์], program_code: "{{ $program->code }}", hardware_id: [ค่าที่ดึงได้]
   - หากสถานะเป็น "active" ให้บันทึกข้อมูลสิทธิ์ที่ตรวจสอบแล้วและลายเซ็น (signature) ลงใน local cache ที่เข้ารหัส (Encrypted File) ในเครื่อง
   - หากสถานะเป็น "pending", "suspended" หรือ "expired" ให้อธิบายสถานะและล็อกไม่ให้ใช้งานโปรแกรม
4. ทำการเช็คสิทธิ์แบบ Offline Cache: ทุกครั้งที่เปิดโปรแกรม ให้อ่านไฟล์แคชในเครื่องก่อนเพื่อให้ทำงานออฟไลน์ได้ และค่อยส่งคำขอเช็คออนไลน์ซ้ำเมื่อพบการเชื่อมต่ออินเทอร์เน็ตทุกๆ 7 วัน
5. สร้างหน้าเมนู Console หรือหน้าต่างให้ป้อนคีย์ลิขสิทธิ์และแสดงสถานะให้สวยงาม
6. ตรวจสอบรหัสโมดูลย่อย (Module Checking): นำข้อมูล license_type และอาเรย์ modules จาก JSON ผลลัพธ์:
   - หาก license_type เป็น "full" ถือว่าได้สิทธิ์ครบทุกฟังก์ชันของโปรแกรม
   - หาก license_type เป็น "module" ให้ทำการเช็คว่ารหัสโมดูลที่ใช้ (เช่น export_ssop) มีระบุอยู่ใน slice/array modules หรือไม่เพื่ออนุญาตการทำงาน</textarea>
                            <button class="btn btn-sm btn-success position-absolute end-0 bottom-0 m-3" onclick="copyPrompt('prompt_text_{{ $program->id }}')">
                                <i class="far fa-copy me-1"></i> คัดลอก Prompt สั่งงาน AI
                            </button>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิดคู่มือ</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<style>
    /* Custom Tab Styling */
    #licenseTabs {
        border-bottom: none;
        gap: 8px;
    }
    #licenseTabs .nav-link {
        border: 1px solid #dee2e6 !important;
        border-radius: 8px !important;
        padding: 10px 20px;
        background-color: #f8f9fa;
        color: #495057 !important;
        transition: all 0.25s ease;
    }
    #licenseTabs .nav-link:hover {
        background-color: #e9ecef;
    }
    #licenses-tab.active {
        background: linear-gradient(135deg, #13855c 0%, #178a5f 100%) !important;
        color: #ffffff !important;
        border-color: #13855c !important;
        box-shadow: 0 4px 12px rgba(19, 133, 92, 0.2);
    }
    #programs-tab.active {
        background: linear-gradient(135deg, #0268c7 0%, #17a6a7 100%) !important;
        color: #ffffff !important;
        border-color: #0268c7 !important;
        box-shadow: 0 4px 12px rgba(2, 104, 199, 0.2);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .text-muted-opacity {
        opacity: 0.4;
    }
    .shadow-xs {
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .animate-fade-in {
        animation: fadeIn 0.35s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .icon-shape {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .bg-gradient-success-custom {
        background: linear-gradient(135deg, #13855c 0%, #17a6a7 100%);
    }
</style>

@push('scripts')
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'คัดลอกสำเร็จ!',
                text: 'คัดลอกเรียบร้อยแล้ว',
                timer: 1500,
                showConfirmButton: false
            });
        }, function(err) {
            console.error('ไม่สามารถคัดลอกข้อความได้: ', err);
        });
    }

    function copyPrompt(textareaId) {
        const textarea = document.getElementById(textareaId);
        textarea.select();
        textarea.setSelectionRange(0, 99999); /* For mobile devices */
        navigator.clipboard.writeText(textarea.value).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'คัดลอก Prompt สำเร็จ!',
                text: 'คุณสามารถนำคำสั่งนี้ไปแชทสั่งงาน Google Antigravity ได้ทันที',
                timer: 2000,
                showConfirmButton: false
            });
        }, function(err) {
            console.error('ไม่สามารถคัดลอกข้อความได้: ', err);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const yearOffset = 543;
        const commonConfig = {
            locale: "th",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "j M Y",
            allowInput: false,
            static: false,
            onReady: function(selectedDates, dateStr, instance) {
                if (instance.altInput) {
                    const date = instance.selectedDates[0] || (instance.input.value ? new Date(instance.input.value) : null);
                    if (date && !isNaN(date.getTime())) {
                        const day = date.getDate();
                        const month = instance.l10n.months.shorthand[date.getMonth()];
                        const year = date.getFullYear() + yearOffset;
                        instance.altInput.value = `${day} ${month} ${year}`;
                    }
                }
                
                // Add Today Button
                const container = instance.calendarContainer;
                if (container && !container.querySelector('.flatpickr-today-button')) {
                    const btn = document.createElement("div");
                    btn.className = "flatpickr-today-button";
                    btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                    btn.addEventListener("mousedown", function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        instance.setDate(new Date());
                        instance.close();
                    });
                    container.appendChild(btn);
                }
            },
            onChange: function(selectedDates, dateStr, instance) {
                if (instance.altInput && selectedDates.length > 0) {
                    const date = selectedDates[0];
                    setTimeout(() => {
                        const day = date.getDate();
                        const month = instance.l10n.months.shorthand[date.getMonth()];
                        const year = date.getFullYear() + yearOffset;
                        instance.altInput.value = `${day} ${month} ${year}`;
                    }, 10);
                }
            }
        };

        // Toggle settings fields for a single module checkbox
        window.toggleModuleFields = function(mode, licenseId, moduleId) {
            const prefix = mode === 'add' ? 'add_mod_' : `edit_mod_${licenseId}_`;
            const checkbox = document.getElementById(prefix + moduleId);
            const settingsDiv = document.getElementById(`${mode}_settings_${licenseId}_${moduleId}`);
            if (checkbox && settingsDiv) {
                if (checkbox.checked) {
                    settingsDiv.classList.remove('d-none');
                } else {
                    settingsDiv.classList.add('d-none');
                }
            }
        };

        // Toggle modules section in Add License Modal
        window.toggleAddModules = function() {
            const type = document.getElementById('add_license_type').value;
            const programSelect = document.querySelector('select[name="program_id"]');
            const programId = programSelect ? programSelect.value : null;
            const section = document.getElementById('add_modules_section');
            
            // Hide all lists first
            document.querySelectorAll('.program-modules-list').forEach(div => {
                div.classList.add('d-none');
            });
            
            if (type === 'module' && programId) {
                if (section) section.classList.remove('d-none');
                const targetList = document.getElementById('add_program_modules_' + programId);
                if (targetList) {
                    targetList.classList.remove('d-none');
                }
            } else {
                if (section) section.classList.add('d-none');
            }
        };
        
        // Bind program select change
        const programSelect = document.querySelector('select[name="program_id"]');
        if (programSelect) {
            programSelect.addEventListener('change', window.toggleAddModules);
        }

        // Toggle modules section in Edit License Modal
        window.toggleEditModules = function(licenseId) {
            const type = document.getElementById('edit_license_type_' + licenseId).value;
            const section = document.getElementById('edit_modules_section_' + licenseId);
            if (type === 'module') {
                if (section) section.classList.remove('d-none');
            } else {
                if (section) section.classList.add('d-none');
            }
        };

        // Initialize flatpickr on Add Expired At
        flatpickr("#add_expired_at", commonConfig);

        // Initialize flatpickr on Edit Expired At inputs
        document.querySelectorAll('.license-edit-expired-at').forEach(function(input) {
            const defaultVal = input.getAttribute('data-value');
            const config = { ...commonConfig };
            if (defaultVal) {
                config.defaultDate = defaultVal;
            }
            flatpickr(input, config);
        });

        // Initialize flatpickr on Module Expired At inputs
        document.querySelectorAll('.license-module-expired-at').forEach(function(input) {
            const defaultVal = input.getAttribute('data-value');
            const config = { ...commonConfig };
            if (defaultVal) {
                config.defaultDate = defaultVal;
            }
            flatpickr(input, config);
        });
    });
</script>
@endpush
@endsection
