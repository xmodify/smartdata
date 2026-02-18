@extends('layouts.admin')

@section('title', 'Admin Dashboard - SmartData')

@section('content')
<div class="mt-2">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-body p-5">
                    <h2 class="text-success fw-bold">Welcome to Admin Dashboard</h2>
                    <p class="text-muted">You are logged in as an <strong>Administrator</strong>.</p>
                    <hr>
                    <div class="alert alert-info">
                        This is the template for the <strong>Admin</strong> role.
                    </div>

                    <div class="mt-4">
                        <h4 class="fw-bold mb-3"><i class="fab fa-git-alt me-2 text-danger"></i> Git Management</h4>
                        <form id="git-pull-form" action="{{ route('admin.git_pull') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="update_details" class="form-label fw-bold">รายละเอียดการอัปเดต (ถ้ามี):</label>
                                <textarea name="details" id="update_details" class="form-control" rows="3" placeholder="ระบุรายละเอียดการเปลี่ยนแปลง..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger shadow-sm" id="gitPullBtn">
                                <i class="fas fa-code-branch me-2"></i> Git Pull
                            </button>
                        </form>

                        @if(session('git_output'))
                            <div class="mt-4">
                                <label class="form-label fw-bold text-success"><i class="fas fa-terminal me-2"></i> ผลการทำงานล่าสุด:</label>
                                <pre id="gitOutput" style="background:#eeee; padding:1rem; border-radius:6px; margin-bottom: 20px;">{{ session('git_output') }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('git-pull-form').addEventListener('submit', function(e) {
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
                // Show loading
                Swal.fire({
                    title: 'กำลังอัปเดตโค้ด...',
                    text: 'กรุณารอสักครู่ ระบบกำลังดำเนินการ Git Pull',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Submit form
                this.submit();
            }
        });
    });

    @if(session('git_output'))
        Swal.fire({
            title: 'ดำเนินการเสร็จสิ้น',
            text: 'ระบบได้รันคำสั่ง Git Pull เรียบร้อยแล้ว',
            icon: 'success',
            confirmButtonText: 'ตกลง'
        });
    @endif
</script>
@endpush

