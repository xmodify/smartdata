@extends('layouts.admin')

@section('title', 'Manage Users - SmartData')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
<style>
    /* Override DataTables UI to match premium design */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.5rem !important;
        padding: 0.2rem 0.6rem !important;
        outline: none !important;
        font-size: 0.8rem !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #13855c 0%, #17a6a7 100%) !important;
        color: white !important;
        border: none !important;
        border-radius: 0.5rem !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f8f9fc !important;
        color: #13855c !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.5rem !important;
    }
    table.dataTable thead th {
        background-color: #f8fafc !important;
        color: #13855c !important;
        font-weight: 700 !important;
        border-bottom: 2px solid #dee2e6 !important;
        font-size: 0.85rem !important;
    }
    .nav-pills .nav-link {
        color: #6c757d;
        font-weight: 600;
        border-radius: 50px;
        padding: 0.5rem 1.25rem;
        transition: all 0.2s ease-in-out;
    }
    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #13855c 0%, #17a6a7 100%) !important;
        color: #fff;
        box-shadow: 0 4px 10px rgba(19, 133, 92, 0.2);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill me-3 shadow-sm transition-all hover-translate-x">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
            <div>
                <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-users-cog me-2 text-success"></i>จัดการผู้ใช้งาน</h2>
            </div>
        </div>
        <button class="btn btn-success shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-user-plus me-2"></i>เพิ่มผู้ใช้งาน
        </button>
    </div>

    {{-- Tabs --}}
    <div class="mb-3">
        <ul class="nav nav-pills gap-2" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-users" type="button" role="tab" aria-controls="active-users" aria-selected="true">
                    <i class="fas fa-check-circle me-1"></i>เปิดใช้งาน ({{ $users->where('active', 'Y')->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-users" type="button" role="tab" aria-controls="pending-users" aria-selected="false">
                    <i class="fas fa-clock me-1"></i>รอเปิดใช้งาน ({{ $users->where('active', 'N')->count() }})
                </button>
            </li>
        </ul>
    </div>

    <div class="card border-0 shadow-sm rounded-lg overflow-hidden p-4">
        <div class="tab-content" id="userTabsContent">
            {{-- Tab: Active --}}
            <div class="tab-pane fade show active" id="active-users" role="tabpanel" aria-labelledby="active-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="tableActiveUsers">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>Username</th>
                                <th>การเข้าถึงเมนู</th>
                                <th>สิทธิ์การใช้งาน</th>
                                <th>สถานะ</th>
                                <th class="text-center px-4">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1; @endphp
                            @foreach($users->where('active', 'Y') as $user)
                            <tr>
                                <td class="px-4">{{ $i++ }}</td>
                                <td>
                                    <div class="fw-bold">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td><code>{{ $user->username }}</code></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($user->hasAccessHosxpReport())
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">รายงาน HOSxP</span>
                                        @endif
                                        @if($user->hasAccessAsset())
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">งานทรัพย์สิน</span>
                                        @endif
                                        @if($user->hasAccessPersonnel())
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">บุคลากร</span>
                                        @endif
                                        @if($user->hasAccessIncident())
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">อุบัติการณ์</span>
                                        @endif
                                        @if($user->hasAccessSkpcard())
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill">บัตรสังฆะประชาร่วมใจ</span>
                                        @endif
                                        @if($user->hasAccessLend())
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">ศูนย์ยืม-คืน</span>
                                        @endif
                                        @if($user->hasAccessMra())
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Audit เวชระเบียน</span>
                                        @endif
                                        
                                        @if(!$user->hasAccessHosxpReport() && !$user->hasAccessAsset() && !$user->hasAccessPersonnel() && !$user->hasAccessIncident() && !$user->hasAccessSkpcard() && !$user->hasAccessLend() && !$user->hasAccessMra())
                                            <span class="text-muted small">ไม่มีสิทธิ์เข้าถึง</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Administrator</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Regular User</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3"><i class="fas fa-check-circle me-1"></i>ใช้งานอยู่</span>
                                </td>
                                <td class="px-4 text-center">
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-white btn-sm edit-user" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal"
                                            data-user="{{ json_encode($user) }}"
                                            title="แก้ไขข้อมูล">
                                            <i class="fas fa-edit text-primary"></i>
                                        </button>
                                        <form action="{{ route('admin.users.reset_password', $user) }}" method="POST" class="d-inline reset-password-form">
                                            @csrf
                                            @method('PUT')
                                            <button type="button" class="btn btn-white btn-sm btn-reset-password" title="รีเซ็ตรหัสผ่านเป็น 12345678">
                                                <i class="fas fa-key text-warning"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline delete-user-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-white btn-sm btn-delete-user" title="ลบผู้ใช้งาน">
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

            {{-- Tab: Pending --}}
            <div class="tab-pane fade" id="pending-users" role="tabpanel" aria-labelledby="pending-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 w-100" id="tablePendingUsers">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>Username</th>
                                <th>การเข้าถึงเมนู</th>
                                <th>สิทธิ์การใช้งาน</th>
                                <th>สถานะ</th>
                                <th class="text-center px-4">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $j = 1; @endphp
                            @foreach($users->where('active', 'N') as $user)
                            <tr>
                                <td class="px-4">{{ $j++ }}</td>
                                <td>
                                    <div class="fw-bold">{{ $user->name }}</div>
                                    <small class="text-muted">{{ $user->email }}</small>
                                </td>
                                <td><code>{{ $user->username }}</code></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($user->hasAccessHosxpReport())
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">รายงาน HOSxP</span>
                                        @endif
                                        @if($user->hasAccessAsset())
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">งานทรัพย์สิน</span>
                                        @endif
                                        @if($user->hasAccessPersonnel())
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">บุคลากร</span>
                                        @endif
                                        @if($user->hasAccessIncident())
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">อุบัติการณ์</span>
                                        @endif
                                        @if($user->hasAccessSkpcard())
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill">บัตรสังฆะประชาร่วมใจ</span>
                                        @endif
                                        @if($user->hasAccessLend())
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">ศูนย์ยืม-คืน</span>
                                        @endif
                                        @if($user->hasAccessMra())
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">Audit เวชระเบียน</span>
                                        @endif
                                        
                                        @if(!$user->hasAccessHosxpReport() && !$user->hasAccessAsset() && !$user->hasAccessPersonnel() && !$user->hasAccessIncident() && !$user->hasAccessSkpcard() && !$user->hasAccessLend() && !$user->hasAccessMra())
                                            <span class="text-muted small">ไม่มีสิทธิ์เข้าถึง</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Administrator</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Regular User</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3"><i class="fas fa-times-circle me-1"></i>ปิดใช้งาน</span>
                                </td>
                                <td class="px-4 text-center">
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-white btn-sm edit-user" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal"
                                            data-user="{{ json_encode($user) }}"
                                            title="แก้ไขข้อมูล">
                                            <i class="fas fa-edit text-primary"></i>
                                        </button>
                                        <form action="{{ route('admin.users.reset_password', $user) }}" method="POST" class="d-inline reset-password-form">
                                            @csrf
                                            @method('PUT')
                                            <button type="button" class="btn btn-white btn-sm btn-reset-password" title="รีเซ็ตรหัสผ่านเป็น 12345678">
                                                <i class="fas fa-key text-warning"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline delete-user-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-white btn-sm btn-delete-user" title="ลบผู้ใช้งาน">
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
        </div>
    </div>
</div>

<!-- Modals (Add/Edit) -->
@include('admin.users.modals')

@endsection

@push('scripts')
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        const tableConfig = {
            language: {
                search: "ค้นหา:",
                lengthMenu: "แสดง _MENU_ รายการ",
                info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                infoEmpty: "แสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
                zeroRecords: "ไม่พบข้อมูลที่ต้องการ",
                paginate: {
                    previous: "ก่อนหน้า",
                    next: "ถัดไป"
                }
            },
            pageLength: 10,
            responsive: true
        };

        const activeTable = $('#tableActiveUsers').DataTable(tableConfig);
        const pendingTable = $('#tablePendingUsers').DataTable(tableConfig);

        // Adjust columns on tab change to prevent header layout issues
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        });

        // Edit User Modal Population using delegated events
        $(document).on('click', '.edit-user', function() {
            const user = JSON.parse(this.dataset.user);
            const form = document.getElementById('editUserForm');
            form.action = `{{ url('/') }}/admin/users/${user.id}`;
            
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_active').checked = user.active === 'Y';

            document.getElementById('edit_allow_hosxp_report').checked = user.allow_hosxp_report === 'Y';
            document.getElementById('edit_allow_asset').checked = user.allow_asset === 'Y';
            document.getElementById('edit_allow_personnel').checked = user.allow_personnel === 'Y';
            document.getElementById('edit_allow_incident').checked = user.allow_incident === 'Y';
            document.getElementById('edit_allow_skpcard').checked = user.allow_skpcard === 'Y';
            document.getElementById('edit_allow_lend').checked = user.allow_lend === 'Y';
            document.getElementById('edit_allow_mra').checked = user.allow_mra === 'Y';
        });

        // Reset Password Confirmation using delegated events
        $(document).on('click', '.btn-reset-password', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'ยืนยันการรีเซ็ตรหัสผ่าน?',
                text: "รหัสผ่านของผู้ใช้งานรายนี้จะถูกรีเซ็ตเป็น 12345678 ทันที",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ใช่, รีเซ็ตเลย!',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#e0a800'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });

        // Delete User Confirmation using delegated events
        $(document).on('click', '.btn-delete-user', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'ยืนยันการลบผู้ใช้งาน?',
                text: "ข้อมูลผู้ใช้งานจะถูกลบออกจากระบบอย่างถาวร",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });
</script>
@endpush
