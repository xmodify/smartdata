@extends('layouts.admin')

@section('title', 'Admin Dashboard - SmartData')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold text-success mb-0">
                        <i class="fas fa-shield-alt me-2"></i> Admin Control Panel
                    </h4>
                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                        <i class="fas fa-user-check me-1"></i> Administrator: {{ Auth::user()->name }}
                    </span>
                </div>
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs border-0" id="adminTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') == 'system' ? '' : 'active' }} fw-bold border-0 px-4" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="{{ session('active_tab') == 'system' ? 'false' : 'true' }}">
                            <i class="fas fa-users-cog me-2"></i>จัดการผู้ใช้งาน
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') == 'system' ? 'active' : '' }} fw-bold border-0 px-4" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab" aria-controls="system" aria-selected="{{ session('active_tab') == 'system' ? 'true' : 'false' }}">
                            <i class="fas fa-server me-2"></i>ตั้งค่าระบบ
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="adminTabsContent">
                    <!-- User Management Tab -->
                    <div class="tab-pane fade {{ session('active_tab') == 'system' ? '' : 'show active' }}" id="users" role="tabpanel" aria-labelledby="users-tab">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0">รายชื่อผู้ใช้งานระบบ</h5>
                            <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-user-plus me-2"></i>เพิ่มผู้ใช้งาน
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3">#</th>
                                        <th class="border-0 py-3">ชื่อ-นามสกุล</th>
                                        <th class="border-0 py-3">Username</th>
                                        <th class="border-0 py-3">สิทธิ์การใช้งาน</th>
                                        <th class="border-0 py-3">สถานะ</th>
                                        <th class="border-0 px-4 py-3 text-center">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $index => $user)
                                    <tr>
                                        <td class="px-4">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $user->name }}</div>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </td>
                                        <td><code>{{ $user->username }}</code></td>
                                        <td>
                                            @if($user->role === 'admin')
                                                <span class="badge bg-danger rounded-pill px-3">Administrator</span>
                                            @else
                                                <span class="badge bg-primary rounded-pill px-3">Regular User</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->active)
                                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>ใช้งานอยู่</span>
                                            @else
                                                <span class="text-danger"><i class="fas fa-times-circle me-1"></i>ปิดใช้งาน</span>
                                            @endif
                                        </td>
                                        <td class="px-4 text-center">
                                            <div class="btn-group shadow-sm rounded">
                                                <button class="btn btn-white btn-sm border-end edit-user" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-user="{{ json_encode($user) }}">
                                                    <i class="fas fa-edit text-primary"></i>
                                                </button>
                                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline delete-user-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-white btn-sm btn-delete-user">
                                                        <i class="fas fa-trash-alt text-danger"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- System Setting Tab -->
                    <div class="tab-pane fade {{ session('active_tab') == 'system' ? 'show active' : '' }}" id="system" role="tabpanel" aria-labelledby="system-tab">
                        <div class="row g-4">
                            <!-- Maintenance Card -->
                            <div class="col-md-12">
                                <div class="card border shadow-none rounded-lg p-4">
                                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-tools me-2"></i> System Maintenance</h5>
                                    
                                    <div class="row">
                                        <!-- Git Update -->
                                        <div class="col-md-6 mb-4 mb-md-0 border-end">
                                            <h6 class="fw-bold mb-3"><i class="fab fa-git-alt me-2 text-danger"></i> Source Code Update</h6>
                                            <p class="small text-muted mb-3">ดึงรหัสต้นฉบับล่าสุดจาก Repository (Git Pull)</p>
                                            <form id="git-pull-form" action="{{ route('admin.git_pull') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm shadow-sm px-4" id="gitPullBtn">
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
                                                <button type="submit" class="btn btn-primary btn-sm shadow-sm px-4" id="upgradeBtn">
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

                            <!-- System Variables Card -->
                            <div class="col-md-8">
                                <div class="card border shadow-none rounded-lg p-4">
                                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-cog me-2"></i> ตัวแปรระบบ (System Variables)</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover align-middle border-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="border-0 small">ชื่อตัวแปร</th>
                                                    <th class="border-0 small">ค่าที่กำหนด</th>
                                                    <th class="border-0 small text-center">จัดการ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sysVars as $var)
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
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- System Info Card -->
                            <div class="col-md-4">
                                <div class="card border shadow-none rounded-lg p-4 bg-light h-100">
                                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i> System Info</h5>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><small class="text-muted">Laravel Version:</small> <br><strong>{{ app()->version() }}</strong></li>
                                        <li class="mb-2"><small class="text-muted">PHP Version:</small> <br><strong>{{ PHP_VERSION }}</strong></li>
                                        <li class="mb-2"><small class="text-muted">Environment:</small> <br><span class="badge bg-dark rounded-pill">{{ config('app.env') }}</span></li>
                                        <li><small class="text-muted">Server Time:</small> <br><strong>{{ now()->format('Y-m-d H:i:s') }}</strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>เพิ่มผู้ใช้งานใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">ชื่อ-นามสกุล</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">อีเมล (Email)</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">สิทธิ์ (Role)</label>
                            <select name="role" class="form-select">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">รหัสผ่าน</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="active" value="0">
                                <input name="active" value="1" class="form-check-input" type="checkbox" role="switch" id="activeAdd" checked>
                                <label class="form-check-label fw-bold small" for="activeAdd">เปิดใช้งานบัญชี</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>แก้ไขข้อมูลผู้ใช้งาน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">ชื่อ-นามสกุล</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">อีเมล (Email)</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Username</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">สิทธิ์ (Role)</label>
                            <select name="role" id="edit_role" class="form-select">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">รหัสผ่าน (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="active" value="0">
                                <input name="active" value="1" class="form-check-input" type="checkbox" role="switch" id="edit_active">
                                <label class="form-check-label fw-bold small" for="edit_active">เปิดใช้งานบัญชี</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">อัปเดตข้อมูล</button>
                </div>
            </form>
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
@endsection

@push('scripts')
<script>
    // Edit User Modal Population
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const user = JSON.parse(this.dataset.user);
            const form = document.getElementById('editUserForm');
            form.action = `/admin/users/${user.id}`;
            
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_active').checked = !!user.active;
        });
    });

    // Delete User Confirmation
    document.querySelectorAll('.btn-delete-user').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            Swal.fire({
                title: 'ยืนยันการลบผู้ใช้งาน?',
                text: "ข้อมูลผู้ใช้งานจะถูกลบออกจากระบบอย่างถาวร",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

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
                cancelButtonColor: '#d33',
                confirmButtonText: 'ตกลง, อัปเดตเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังอัปเดตโค้ด...',
                        text: 'กรุณารอสักครู่ ระบบกำลังดำเนินการ Git Pull',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    this.submit();
                }
            });
        });
    }

    // Upgrade Structure logic
    const upgradeForm = document.getElementById('upgrade-structure-form');
    if (upgradeForm) {
        upgradeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'ยืนยันการอัปเกรดฐานข้อมูล?',
                text: "ระบบจะดำเนินการรัน Migration เพื่ออัปเดตโครงสร้างตารางและคอลัมน์",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ตกลง, อัปเกรดเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังอัปเกรดฐานข้อมูล...',
                        text: 'กรุณารอสักครู่ ระบบกำลังรันคำสั่ง Migration',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    this.submit();
                }
            });
        });
    }

    // Edit SysVar Modal Population
    document.querySelectorAll('.edit-sysvar').forEach(button => {
        button.addEventListener('click', function() {
            const sysVar = JSON.parse(this.dataset.var);
            const form = document.getElementById('editSysVarForm');
            form.action = `/admin/sys-var/${sysVar.sys_name}`;
            
            document.getElementById('sys_name_th_label').innerText = sysVar.sys_name_th;
            document.getElementById('sys_name_label').innerText = sysVar.sys_name;
            document.getElementById('edit_sys_value').value = sysVar.sys_value;
        });
    });

    // Success/Error Alerts
    @if(session('success'))
        Swal.fire({
            title: 'สำเร็จ!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'ตกลง'
        });
    @endif

    @if(session('error'))
        Swal.fire({
            title: 'เกิดข้อผิดพลาด!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'ตกลง'
        });
    @endif
</script>
@endpush

