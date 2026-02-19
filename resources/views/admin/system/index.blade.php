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

        <!-- System Variables Card -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-lg p-4 h-100">
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
            <div class="card border-0 shadow-sm rounded-lg p-4 bg-light h-100">
                <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i> System Info</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 text-sm"><span class="text-muted d-block small">Laravel Version:</span> <strong>{{ app()->version() }}</strong></li>
                    <li class="mb-3 text-sm"><span class="text-muted d-block small">PHP Version:</span> <strong>{{ PHP_VERSION }}</strong></li>
                    <li class="mb-3 text-sm"><span class="text-muted d-block small">Environment:</span> <span class="badge bg-dark rounded-pill">{{ config('app.env') }}</span></li>
                    <li class="text-sm"><span class="text-muted d-block small">Server Time:</span> <strong>{{ now()->format('Y-m-d H:i:s') }}</strong></li>
                </ul>
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
@endsection

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
                text: "ระบบจะดำเนินการรัน Migration เพื่ออัปเดตโครงสร้างฐานข้อมูล",
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
</script>
@endpush
