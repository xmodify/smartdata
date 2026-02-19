<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartData | Admin')</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
    <style>
        .bg-gradient-primary-custom {
            background: linear-gradient(135deg, #0268c7 0%, #17a6a7 100%);
        }
        .bg-gradient-success-custom {
            background: linear-gradient(135deg, #13855c 0%, #17a6a7 100%);
        }
        .hover-translate-x:hover {
            transform: translateX(-5px);
        }
        .transition-all {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-success-custom shadow-sm">
        <div class="container-fluid px-md-5">
            <a class="navbar-brand fw-bold" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-home me-2"></i>SmartData | Admin Panel
            </a>
            @auth
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white fw-bold d-flex align-items-center" href="#" id="adminNavbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fa-lg me-2 text-white-50"></i>
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="adminNavbarDropdown">                        
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fas fa-key me-2 text-warning"></i> เปลี่ยนรหัสผ่าน
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                            </a>
                        </li>
                    </ul>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            @endauth
        </div>
    </nav>

    <main class="py-4">
        <div class="container-fluid px-md-5">
            @yield('content')
        </div>
    </main>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header bg-gradient-success-custom text-white border-0">
                    <h5 class="modal-title fw-bold" id="changePasswordModalLabel"><i class="fas fa-key me-2"></i>เปลี่ยนรหัสผ่าน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changePasswordForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">รหัสผ่านปัจจุบัน</label>
                            <input type="password" name="current_password" class="form-control shadow-sm border-0 bg-light" placeholder="ระบุรหัสผ่านเดิม" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">รหัสผ่านใหม่</label>
                            <input type="password" name="password" class="form-control shadow-sm border-0 bg-light" placeholder="อย่างน้อย 8 ตัวอักษร" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="password_confirmation" class="form-control shadow-sm border-0 bg-light" placeholder="ระบุรหัสผ่านใหม่ซ้ำอีกครั้ง" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn text-white px-4 shadow-sm bg-gradient-success-custom" style="border: none;">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>กำลังบันทึก...';

            fetch('{{ route("profile.password.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(new FormData(form)))
            })
            .then(response => response.json().then(data => ({ status: response.status, data })))
            .then(({ status, data }) => {
                if (status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('changePasswordModal'));
                        if (modal) modal.hide();
                        form.reset();
                    });
                } else {
                    let errorMsg = data.message;
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: errorMsg
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาดระบบ',
                    text: 'ไม่สามารถดำเนินการได้ในขณะนี้'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
