<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SmartData - เข้าสู่ระบบ</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap CSS & FontAwesome -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: radial-gradient(circle at 50% 50%, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start; /* แหนวตั้งเกือบติดบน */
            justify-content: center;
            font-family: 'Plus Jakarta Sans', 'Prompt', sans-serif;
            margin: 0;
            padding-top: 50px; /* ระยะห่างจากด้านบน 50px เสมอกันทุกหน้า */
        }

        .login-card-original {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(2, 132, 199, 0.06), 0 5px 15px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(2, 132, 199, 0.18) !important;
            overflow: hidden;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 680px; /* ขยายความกว้างเพื่อรองรับ 2 คอลัมน์ด้านใน */
        }

        .login-card-original:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(2, 132, 199, 0.1), 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .card-login-header {
            background: linear-gradient(135deg, #0284c7 0%, #16a34a 100%);
            color: white;
            padding: 1.8rem;
            text-align: center;
        }

        .border-start-custom {
            border-left: none;
            padding-left: 12px;
        }

        @media (min-width: 768px) {
            .border-start-custom {
                border-left: 1px solid #e2e8f0;
                padding-left: 30px;
            }
        }

        /* Form Controls with Dimensions */
        .form-label-custom {
            font-weight: 700;
            font-size: 0.85rem;
            color: #0284c7;
            margin-bottom: 6px;
            display: block;
        }

        .form-label-custom-otp {
            font-weight: 700;
            font-size: 0.85rem;
            color: #16a34a;
            margin-bottom: 6px;
            display: block;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 0.9rem;
            transition: color 0.3s ease;
            z-index: 10;
        }

        .form-input-custom {
            padding-left: 38px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #f8fafc !important;
            border-radius: 8px !important;
            font-size: 0.95rem !important;
            color: #1e293b !important;
            transition: all 0.3s ease !important;
            height: 40px !important;
        }

        .form-input-custom:focus {
            outline: none !important;
            border-color: #0284c7 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12) !important;
        }

        .form-input-custom:focus + .input-icon {
            color: #0284c7;
        }

        /* Buttons with Custom Premium Shape & Icons */
        .btn-login-original {
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
            border: none !important;
            color: #ffffff !important;
            padding: 10px 22px !important;
            border-radius: 8px !important; /* รูปทรงปุ่มใหม่โค้งมนสวยงาม */
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.18) !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-login-original:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.28) !important;
        }

        .btn-provider-original {
            background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
            border: none !important;
            color: #ffffff !important;
            padding: 10px 22px !important;
            border-radius: 8px !important; /* รูปทรงปุ่มใหม่โค้งมนสวยงาม */
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.18) !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-provider-original:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.28) !important;
            color: #ffffff !important;
        }

        .btn-otp-original {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
            border: none !important;
            color: #1e293b !important;
            padding: 10px 22px !important;
            border-radius: 8px !important; /* รูปทรงปุ่มใหม่โค้งมนสวยงาม */
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.15) !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-otp-original:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(255, 193, 7, 0.25) !important;
        }

        .divider-original {
            border: 0;
            height: 1px;
            background: #e2e8f0;
            margin: 15px 0;
            opacity: 0.5;
        }

        .register-link-original {
            color: #0d6efd;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .register-link-original:hover {
            color: #0a58ca;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 d-flex justify-content-center">
            <div class="card login-card-original shadow-lg"> 
                <div class="card-login-header">
                    <h4 class="mb-1 fw-bold text-white" style="font-size: 1.35rem; letter-spacing: -0.5px;">เข้าสู่ระบบ SmartData</h4>
                    <p class="mb-0 small text-white opacity-75">โรงพยาบาลหัวตะพาน</p>
                </div>
                <div class="card-body px-4 pt-3 pb-3" style="padding-bottom: 15px !important; padding-top: 15px !important;">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        @if($errors->has('provider_id'))
                            <div class="alert alert-danger shadow-sm small py-2 px-3 mb-3 border-0 rounded-lg" style="background-color: rgba(220, 53, 69, 0.9); color: #fff;">
                                <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first('provider_id') }}
                            </div>
                        @endif

                        <!-- Top Split Row: Logo on Left, Username/Password on Right -->
                        <div class="row align-items-center mb-1">
                            <!-- Left Column: Hospital Logo -->
                            <div class="col-md-5 text-center mb-4 mb-md-0">
                                <img src="{{ asset('images/logo_smartdata.png') }}" style="max-width: 100%; height: auto; display: block; margin: 0 auto; padding: 10px;" alt="SmartData Logo">
                            </div>
                            
                            <!-- Right Column: Login Inputs -->
                            <div class="col-md-7 border-start-custom">
                                <!-- Username Input -->
                                <div class="mb-3">
                                    <label for="username" class="form-label-custom">Username</label>
                                    <div class="input-wrapper">
                                        <i class="fa-regular fa-user input-icon"></i>
                                        <input id="username" type="text" class="form-control form-input-custom @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus placeholder="กรอก Username เข้าใช้งาน">
                                    </div>
                                    @error('username')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <!-- Password Input Wrapper Row -->
                                <div class="row mb-0">
                                    <div class="col-md-12">
                                        <label for="password" class="form-label-custom">Password</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-lock input-icon"></i>
                                            <input id="password" type="password" class="form-control form-input-custom @error('password') is-invalid @enderror" name="password" autocomplete="current-password" placeholder="รหัสผ่านเข้าใช้งาน">
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- OTP Verification Block (Initially Hidden) -->
                        <div id="otp_verify_block" style="display: none;" class="mb-3">
                            <div class="row justify-content-center">
                                <div class="col-md-10">
                                    <label for="otp" class="form-label-custom-otp">รหัส OTP</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-key input-icon"></i>
                                        <input id="otp" type="text" class="form-control form-input-custom text-center fw-bold" style="font-size: 1.25rem; letter-spacing: 4px;" maxlength="6" placeholder="รหัส OTP 6 หลัก">
                                    </div>
                                    <div class="form-text text-muted text-center mt-2" style="font-size: 0.85rem;"><i class="fas fa-info-circle me-1"></i> กรอกรหัสที่ท่านได้รับผ่าน หมอพร้อม LineOA</div>
                                    <div class="d-flex justify-content-center gap-2 mt-3">
                                        <button type="button" id="btn_verify_otp" class="btn btn-success px-4 fw-bold shadow-sm" style="background: linear-gradient(135deg, #198754 0%, #157347 100%) !important; border: none !important;">
                                            <i class="fas fa-check-circle me-1"></i> ยืนยันรหัส OTP
                                        </button>
                                        <button type="button" id="btn_cancel_otp" class="btn btn-outline-secondary px-3 fw-bold">
                                            ยกเลิก
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Full-Width Action Block (Centered below split columns) -->
                        <div class="row mb-0">
                            <div class="col-md-12 text-center mt-0">
                                <div id="login_buttons_block" class="d-flex justify-content-center flex-wrap align-items-center gap-2 mb-3">
                                    <button type="submit" class="btn btn-login-original py-2">
                                        <i class="fas fa-sign-in-alt me-1"></i> {{ __('Login') }}
                                    </button>
                                    
                                    @php $mophAlertConfig = \App\Models\MophAlert::find(1); @endphp
                                    @if($mophAlertConfig && $mophAlertConfig->active === 'Y' && $mophAlertConfig->enable_2fa !== 'Y')
                                        <button type="button" id="btn_request_otp" class="btn btn-otp-original py-2">
                                            <i class="fas fa-sms me-1"></i> OTP Login
                                        </button>
                                    @endif
                                    
                                    @php $providerConfig = \App\Models\ProviderId::find(1); @endphp
                                    @if($providerConfig && $providerConfig->active === 'Y' && !empty($providerConfig->health_id_client_id) && !empty($providerConfig->provider_id_client_id))
                                        <a href="{{ route('login.provider_id') }}" class="btn btn-provider-original py-2">
                                            <i class="fas fa-id-card me-1"></i> ProviderID Login
                                        </a>
                                    @endif
                                </div>
                                <hr class="divider-original">
                                <div class="text-center">
                                    <p class="mb-0 small text-muted">ยังไม่มีบัญชี? <a href="{{ route('register') }}" class="register-link-original fw-bold">ลงทะเบียนที่นี่</a></p>
                                </div>
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
                document.getElementById('login_buttons_block').style.display = 'flex';
                usernameInput.readOnly = false;
                document.getElementById('otp_verify_block').style.display = 'none';
            });
        }

        // Auto-submit passwordless OTP on 6 digits
        const otpInputPasswordless = document.getElementById('otp');
        if (otpInputPasswordless) {
            otpInputPasswordless.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 6 && btnVerifyOtp) {
                    btnVerifyOtp.click();
                }
            });
        }

        // Prevent double click on form submission
        const loginForm = document.querySelector('form');
        if (loginForm) {
            loginForm.addEventListener('submit', function() {
                const submitBtn = loginForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังเข้าระบบ...';
                }
            });
        }
    });
</script>
</body>
</html>
