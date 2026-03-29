@extends('layouts.admin')

@section('title', 'System Settings - SmartData')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex align-items-center">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill me-3 shadow-sm transition-all hover-translate-x">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </a>
        <div>
            <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-server me-2 text-primary"></i>ตั้งค่าระบบ (System Settings)</h2>
        </div>
    </div>

    <div class="row g-4">
        <!-- Maintenance Card -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-lg p-4">
                <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-tools me-2"></i> System Maintenance</h5>
                
                <div class="row">
                    <!-- Git Update -->
                    <div class="col-md-6 mb-4 mb-md-0 border-md-end">
                        <h6 class="fw-bold mb-3"><i class="fab fa-git-alt me-2 text-danger"></i> Source Code Update</h6>
                        <p class="small text-muted mb-3">ดึงรหัสต้นฉบับล่าสุดจาก Repository (Git Pull)</p>
                        <form id="git-pull-form" action="{{ route('admin.git_pull') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm shadow-sm px-4">
                                <i class="fas fa-code-branch me-2"></i> Git Pull (อัปเดตโค้ด)
                            </button>
                        </form>
                    </div>

                    <!-- Database Structure -->
                    <div class="col-md-6 ps-md-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-database me-2 text-primary"></i> Database Upgrade</h6>
                        <p class="small text-muted mb-3">อัปเดตตารางและคอลัมน์ในฐานข้อมูล (Artisan Migrate)</p>
                        <form id="upgrade-structure-form" action="{{ route('admin.upgrade_structure') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4">
                                <i class="fas fa-layer-group me-2"></i> Upgrade Structure
                            </button>
                        </form>
                    </div>
                </div>

                @if(session('git_output') || session('migrate_output'))
                    <div class="mt-4 pt-3 border-top">
                        <label class="form-label fw-bold text-success small">
                            <i class="fas fa-terminal me-2"></i> ผลการตรวจสอบล่าสุด:
                        </label>
                        <pre class="small bg-dark text-light p-3 rounded" style="max-height: 250px; overflow-y: auto;">{{ session('git_output') ?? session('migrate_output') }}</pre>
                    </div>
                @endif
            </div>
        </div>

        <!-- System Variables Card (Full Width) -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-lg p-4 mb-4">
                <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-cog me-2"></i> ตัวแปรระบบ (System Variables)</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle border-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 small">ชื่อตัวแปร</th>
                                <th class="border-0 small">ค่าที่กำหนด</th>
                                <th class="border-0 small text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $notifyKeys = ['telegram_token', 'telegram_chat_id_register', 'moph_notify_secret', 'moph_notify_client_id'];
                            @endphp
                            @foreach($sysVars as $var)
                            @if(!in_array($var->sys_name, $notifyKeys))
                            <tr>
                                <td>
                                    <div class="fw-bold small">{{ $var->sys_name_th }}</div>
                                    <code class="xsmall text-muted">{{ $var->sys_name }}</code>
                                </td>
                                <td class="small text-truncate" style="max-width: 200px;">
                                    {{ $var->sys_value ?: '(ว่าง)' }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-xs edit-sysvar" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editSysVarModal"
                                        data-var="{{ json_encode($var) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <!-- Moph Notify Settings (Left 50%) -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-lg p-4 mb-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-primary"><i class="fas fa-bell me-2"></i> Moph Notify Settings</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle border-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 small">ชื่อรายการ</th>
                                        <th class="border-0 small text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mophNotifies as $moph)
                                    <tr>
                                        <td>
                                            <div class="fw-bold small">{{ $moph->name }}</div>
                                            <div class="mt-1 d-flex flex-wrap align-items-center gap-2">
                                                <div class="d-flex align-items-center me-2">
                                                    <span class="xsmall text-muted me-1">ClientID:</span>
                                                    <span id="moph_client_id_{{ $moph->id }}" class="small text-truncate" style="max-width: 120px;">********</span>
                                                    <button class="btn btn-link btn-xs p-0 ms-1 toggle-moph-value" 
                                                        data-id="{{ $moph->id }}" 
                                                        data-type="client_id"
                                                        data-value="{{ $moph->client_id }}">
                                                        <i class="fas fa-eye small"></i>
                                                    </button>
                                                </div>
                                                <span class="text-muted small">|</span>
                                                <div class="d-flex align-items-center ms-2">
                                                    <span class="xsmall text-muted me-1">Secret:</span>
                                                    <span id="moph_secret_{{ $moph->id }}" class="small text-truncate" style="max-width: 120px;">********</span>
                                                    <button class="btn btn-link btn-xs p-0 ms-1 toggle-moph-value" 
                                                        data-id="{{ $moph->id }}" 
                                                        data-type="secret"
                                                        data-value="{{ $moph->secret }}">
                                                        <i class="fas fa-eye small"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-primary btn-xs edit-moph" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editMophModal"
                                                data-moph="{{ json_encode($moph) }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Telegram Notify Settings (Right 50%) -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-lg p-4 mb-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-primary"><i class="fab fa-telegram me-2"></i> Telegram Notify Settings</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle border-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 small">ชื่อรายการ</th>
                                        <th class="border-0 small text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($telegramNotifies as $tele)
                                    <tr>
                                        <td>
                                            <div class="fw-bold small">{{ $tele->name_th }}</div>
                                            <code class="xsmall text-muted">{{ $tele->name }}</code>
                                            <div class="mt-1 d-flex align-items-center">
                                                <span class="xsmall text-muted me-2">Value:</span>
                                                <span id="tele_val_display_{{ $loop->index }}" class="small text-truncate" style="max-width: 200px;">
                                                    {{ $tele->name == 'telegram_bot_token' ? '********' : ($tele->value ?: '(ว่าง)') }}
                                                </span>
                                                <button class="btn btn-link btn-xs p-0 ms-2 toggle-tele-value" 
                                                    data-index="{{ $loop->index }}" 
                                                    data-value="{{ $tele->value }}"
                                                    data-masked="{{ $tele->name == 'telegram_bot_token' ? 'true' : 'false' }}">
                                                    <i class="fas fa-eye small"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-outline-primary btn-xs edit-tele" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTelegramModal"
                                                data-tele="{{ json_encode($tele) }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit SysVar Modal -->
<div class="modal fade" id="editSysVarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-cog me-2"></i>แก้ไขค่าตัวแปรระบบ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSysVarForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small" id="sys_name_th_label"></label>
                        <p class="xsmall text-muted mb-2">Key: <code id="sys_name_label"></code></p>
                        <textarea name="sys_value" id="edit_sys_value" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Moph Notify Modal -->
<div class="modal fade" id="editMophModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-bell me-2"></i>แก้ไข Moph Notify</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMophForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ชื่อรายการ</label>
                        <input type="text" id="moph_name" class="form-control shadow-sm bg-light" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Client ID</label>
                        <div class="input-group">
                            <input type="password" name="client_id" id="moph_client_id_edit" class="form-control shadow-sm">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="moph_client_id_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Secret Key</label>
                        <div class="input-group">
                            <input type="password" name="secret" id="moph_secret_edit" class="form-control shadow-sm">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="moph_secret_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Telegram Notify Modal -->
<div class="modal fade" id="editTelegramModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fab fa-telegram me-2"></i>แก้ไข Telegram Notify</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTelegramForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ชื่อรายการ (Thai)</label>
                        <input type="text" name="name_th" id="tele_name_th" class="form-control shadow-sm">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Key (Reference Name)</label>
                        <input type="text" id="tele_name" class="form-control shadow-sm bg-light" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small" id="tele_value_label">ค่าที่ระบุ</label>
                        <div class="input-group">
                            <input type="password" name="value" id="tele_value_edit" class="form-control shadow-sm">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="tele_value_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Git Pull logic
    const gitForm = document.getElementById('git-pull-form');
    if (gitForm) {
        gitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'ยืนยันการอัปเดตโค้ด?',
                text: "ระบบจะดำเนินการ Git Pull เพื่ออัปเดตซอร์สโค้ดจาก Repository",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: 'ตกลง, อัปเดตเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => { if (result.isConfirmed) { Swal.showLoading(); this.submit(); } });
        });
    }

    // Upgrade Structure logic
    const upgradeForm = document.getElementById('upgrade-structure-form');
    if (upgradeForm) {
        upgradeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'ยืนยันการอัปเกรดฐานข้อมูล?',
                text: "ระบบจะดำเนินการรัน Migration และ Sync ข้อมูล Notify",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'ตกลง, อัปเกรดเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => { if (result.isConfirmed) { Swal.showLoading(); this.submit(); } });
        });
    }

    // Edit SysVar Modal Population
    document.querySelectorAll('.edit-sysvar').forEach(button => {
        button.addEventListener('click', function() {
            const sysVar = JSON.parse(this.dataset.var);
            const form = document.getElementById('editSysVarForm');
            form.action = `{{ url('/') }}/admin/sys-var/${sysVar.sys_name}`;
            
            document.getElementById('sys_name_th_label').innerText = sysVar.sys_name_th;
            document.getElementById('sys_name_label').innerText = sysVar.sys_name;
            document.getElementById('edit_sys_value').value = sysVar.sys_value;
        });
    });

    // Edit Moph Modal Population
    document.querySelectorAll('.edit-moph').forEach(button => {
        button.addEventListener('click', function() {
            const moph = JSON.parse(this.dataset.moph);
            const form = document.getElementById('editMophForm');
            form.action = `{{ url('/') }}/admin/moph-notify/${moph.id}`;
            document.getElementById('moph_name').value = moph.name;
            document.getElementById('moph_client_id_edit').value = moph.client_id;
            document.getElementById('moph_secret_edit').value = moph.secret;
        });
    });

    // Edit Telegram Modal Population
    document.querySelectorAll('.edit-tele').forEach(button => {
        button.addEventListener('click', function() {
            const tele = JSON.parse(this.dataset.tele);
            const form = document.getElementById('editTelegramForm');
            form.action = `{{ url('/') }}/admin/telegram-notify/${tele.name}`;
            document.getElementById('tele_name_th').value = tele.name_th;
            document.getElementById('tele_name').value = tele.name;
            document.getElementById('tele_value_edit').value = tele.value || '';
        });
    });

    // Modal Password Visibility Toggle
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Toggle Telegram Value Visibility
    document.querySelectorAll('.toggle-tele-value').forEach(button => {
        button.addEventListener('click', function() {
            const index = this.dataset.index;
            const realValue = this.dataset.value || '(ว่าง)';
            const isMasked = this.dataset.masked === 'true';
            const displayEl = document.getElementById(`tele_val_display_${index}`);
            const icon = this.querySelector('i');

            if (icon.classList.contains('fa-eye')) {
                displayEl.innerText = realValue;
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                displayEl.innerText = isMasked ? '********' : realValue;
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Toggle Moph Value Visibility
    document.querySelectorAll('.toggle-moph-value').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            const realValue = this.dataset.value || '(ว่าง)';
            const displayEl = document.getElementById(`moph_${type}_${id}`);
            const icon = this.querySelector('i');

            if (icon.classList.contains('fa-eye')) {
                displayEl.innerText = realValue;
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                displayEl.innerText = '********';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
</script>
@endpush
@endsection
