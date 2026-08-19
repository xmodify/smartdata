<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SmartData</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            padding-top: 50px;
        }
    </style>
</head>
<body>
<div class="container ">
    <div class="row justify-content-center" >
        <div class="col-md-6">
            <div class="card border border-success shadow-lg"> 
                <div class="card-body text-white" style="
                    background-image: url('{{ asset('images/logo_smartdata.png') }}');
                    background-repeat: no-repeat;
                    background-position: left 1px;
                    background-size: 300px;
                    background-blend-mode: lighten;
                    padding-top: 200px;
                ">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        @if($errors->has('provider_id'))
                            <div class="alert alert-danger shadow-sm small py-2 px-3 mb-3 border-0 rounded-lg" style="background-color: rgba(220, 53, 69, 0.9); color: #fff;">
                                <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first('provider_id') }}
                            </div>
                        @endif

                        <div class="row mb-3">
                            <label for="username" class="col-md-4 col-form-label text-md-end text-primary fw-bold">Username</label>

                            <div class="col-md-7">
                                <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus placeholder="กรอก Username เข้าใช้งาน">

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end text-primary fw-bold">{{ __('Password') }}</label>

                            <div class="col-md-7">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" autocomplete="current-password" placeholder="รหัสผ่านเข้าใช้งาน">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- OTP Verification Block (Initially Hidden) -->
                        <div id="otp_verify_block" style="display: none;">
                            <div class="row mb-3">
                                <label for="otp" class="col-md-4 col-form-label text-md-end text-success fw-bold">รหัส OTP</label>
                                <div class="col-md-7">
                                    <input id="otp" type="text" class="form-control text-center fw-bold" style="font-size: 1.25rem; letter-spacing: 4px;" maxlength="6" placeholder="รหัส OTP 6 หลัก">
                                    <div class="form-text text-white-50 text-center mt-1"><i class="fas fa-info-circle me-1"></i> กรอกรหัสที่ท่านได้รับผ่าน หมอพร้อม LineOA</div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button type="button" id="btn_verify_otp" class="btn btn-success px-4 fw-bold shadow-sm">
                                        <i class="fas fa-check-circle me-1"></i> ยืนยันรหัส OTP
                                    </button>
                                    <button type="button" id="btn_cancel_otp" class="btn btn-outline-secondary px-3 ms-2 fw-bold">
                                        ยกเลิก
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-12 text-center">
                                <div id="login_buttons_block" class="d-flex justify-content-center flex-wrap align-items-center gap-2 mb-3">
                                    <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i> {{ __('Login') }}
                                    </button>
                                    @php $mophAlertConfig = \App\Models\MophAlert::find(1); @endphp
                                    @if($mophAlertConfig && $mophAlertConfig->active === 'Y' && $mophAlertConfig->enable_2fa !== 'Y')
                                        <button type="button" id="btn_request_otp" class="btn btn-warning text-dark px-4 fw-bold shadow-sm">
                                            <i class="fas fa-sms me-1"></i> OTP Login
                                        </button>
                                    @endif
                                    @php $providerConfig = \App\Models\ProviderId::find(1); @endphp
                                    @if($providerConfig && $providerConfig->active === 'Y' && !empty($providerConfig->health_id_client_id) && !empty($providerConfig->provider_id_client_id))
                                        <a href="{{ route('login.provider_id') }}" class="btn btn-success px-4 fw-bold shadow-sm text-white" style="background-color: #198754; border-color: #198754;">
                                            <i class="fas fa-id-card me-1"></i> ProviderID Login
                                        </a>
                                    @endif
                                </div>
                                <hr>
                                <p class="mb-0 small text-white text-muted">ยังไม่มีบัญชี? <a href="{{ route('register') }}" class="text-primary fw-bold">ลงทะเบียนที่นี่</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: "{{ session('success') }}",
                confirmButtonText: 'ตกลง',
                timer: 4000
            });
        @endif

        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'เข้าสู่ระบบไม่สำเร็จ',
                text: "{{ $errors->first() }}",
                confirmButtonText: 'ลองอีกครั้ง'
            });
        @endif

        const btnRequestOtp = document.getElementById('btn_request_otp');
        const btnVerifyOtp = document.getElementById('btn_verify_otp');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        if (btnRequestOtp) {
            btnRequestOtp.addEventListener('click', function() {
                const username = usernameInput.value.trim();
                if (!username) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณากรอกข้อมูล',
                        text: 'กรุณากรอก Username ก่อนขอรหัส OTP',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }

                btnRequestOtp.disabled = true;
                btnRequestOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังส่ง OTP...';

                fetch('{{ route("login.send_otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ username: username })
                })
                .then(res => res.json())
                .then(data => {
                    btnRequestOtp.disabled = false;
                    btnRequestOtp.innerHTML = '<i class="fas fa-sms me-1"></i> OTP Login';

                    if (data.success) {
                        if (data.redirect) {
                            // Global 2FA is active, redirect to 2FA page
                            Swal.fire({
                                icon: 'info',
                                title: 'ระบบเปิดใช้งาน 2FA',
                                text: 'ส่งรหัสเรียบร้อยแล้ว กำลังนำทางไปหน้ากรอก OTP...',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = data.redirect;
                            });
                        } else {
                            // Passwordless OTP (2FA inactive), show OTP field here
                            Swal.fire({
                                icon: 'success',
                                title: 'ส่งรหัส OTP สำเร็จ!',
                                text: data.message,
                                confirmButtonText: 'ตกลง',
                                confirmButtonColor: '#198754'
                            });
                            
                            // Hide password block and login buttons
                            passwordInput.closest('.row').style.display = 'none';
                            document.getElementById('login_buttons_block').style.display = 'none';
                            
                            // Disable username edit to prevent mismatch
                            usernameInput.readOnly = true;

                            // Show OTP input block
                            document.getElementById('otp_verify_block').style.display = 'block';
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: data.message,
                            confirmButtonText: 'ลองอีกครั้ง'
                        });
                    }
                })
                .catch(err => {
                    btnRequestOtp.disabled = false;
                    btnRequestOtp.innerHTML = '<i class="fas fa-sms me-1"></i> OTP Login';
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถส่งรหัส OTP ได้ในขณะนี้ ' + err.message,
                        confirmButtonText: 'ตกลง'
                    });
                });
            });
        }

        if (btnVerifyOtp) {
            btnVerifyOtp.addEventListener('click', function() {
                const otp = document.getElementById('otp').value.trim();
                const username = usernameInput.value.trim();
                if (!otp) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณากรอกรหัส',
                        text: 'กรุณากรอกรหัส OTP 6 หลักก่อนยืนยันการเข้าสู่ระบบ',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#198754'
                    });
                    return;
                }

                btnVerifyOtp.disabled = true;
                btnVerifyOtp.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังตรวจสอบ...';

                fetch('{{ route("login.verify_otp_passwordless") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ username: username, otp: otp })
                })
                .then(res => res.json())
                .then(data => {
                    btnVerifyOtp.disabled = false;
                    btnVerifyOtp.innerHTML = '<i class="fas fa-check-circle me-1"></i> ยืนยันรหัส OTP';

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'เข้าสู่ระบบสำเร็จ',
                            text: 'ระบบความปลอดภัยยืนยันถูกต้อง กำลังพาท่านเข้าสู่ระบบ...',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = data.redirect;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'รหัสไม่ถูกต้อง',
                            text: data.message,
                            confirmButtonText: 'ลองอีกครั้ง'
                        });
                    }
                })
                .catch(err => {
                    btnVerifyOtp.disabled = false;
                    btnVerifyOtp.innerHTML = '<i class="fas fa-check-circle me-1"></i> ยืนยันรหัส OTP';
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด',
                        text: 'ไม่สามารถตรวจสอบรหัสได้ในขณะนี้: ' + err.message,
                        confirmButtonText: 'ตกลง'
                    });
                });
            });
        }

        const btnCancelOtp = document.getElementById('btn_cancel_otp');
        if (btnCancelOtp) {
            btnCancelOtp.addEventListener('click', function() {
                document.getElementById('otp').value = '';
                passwordInput.closest('.row').style.display = 'flex';
                document.getElementById('login_buttons_block').style.display = 'block';
                usernameInput.readOnly = false;
                document.getElementById('otp_verify_block').style.display = 'none';
            });
        }
    });
</script>
</body>
</html>
