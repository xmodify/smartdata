@extends('layouts.admin')

@section('title', 'Manage Users - SmartData')

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

    <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>Username</th>
                        <th>สิทธิ์การใช้งาน</th>
                        <th>สถานะ</th>
                        <th class="text-center px-4">จัดการ</th>
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
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Administrator</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Regular User</span>
                            @endif
                        </td>
                        <td>
                            @if($user->active)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3"><i class="fas fa-check-circle me-1"></i>ใช้งานอยู่</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3"><i class="fas fa-times-circle me-1"></i>ปิดใช้งาน</span>
                            @endif
                        </td>
                        <td class="px-4 text-center">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-white btn-sm edit-user" 
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
</div>

<!-- Modals (Add/Edit) -->
@include('admin.users.modals')

@endsection

@push('scripts')
<script>
    // Edit User Modal Population
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const user = JSON.parse(this.dataset.user);
            const form = document.getElementById('editUserForm');
            form.action = `{{ url('/') }}/admin/users/${user.id}`;
            
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
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => { if (result.isConfirmed) form.submit(); });
        });
    });
</script>
@endpush
