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
