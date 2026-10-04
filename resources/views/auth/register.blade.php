<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SmartData - ลงทะเบียนเข้าใช้งาน</title>

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
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            font-family: 'Plus Jakarta Sans', 'Prompt', sans-serif;
            margin: 0;
            padding-top: 50px; /* ระยะห่างจากด้านบน 50px เสมอกันทุกหน้า */
            padding-bottom: 30px;
        }

        .register-card-rims {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(2, 132, 199, 0.08), 0 5px 15px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(2, 132, 199, 0.15);
            border-top: 4px solid #0284c7;
            overflow: hidden;
            width: 100%;
            max-width: 860px;
            transition: all 0.3s ease;
        }

        .register-card-rims:hover {
            box-shadow: 0 20px 45px rgba(2, 132, 199, 0.12), 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .logo-col-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
            background: #ffffff;
        }

        .form-col-wrapper {
            padding: 2.2rem 2.8rem 1.2rem 2.8rem;
        }

        @media (max-width: 767.98px) {
            .logo-col-wrapper {
                padding: 1.5rem 1.5rem 0.5rem 1.5rem;
            }
            .form-col-wrapper {
                padding: 1.2rem 1.5rem 1rem 1.5rem;
            }
        }

        .register-heading {
            font-size: 1.65rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }

        .register-subheading {
            font-size: 0.88rem;
            color: #64748b;
            margin-bottom: 1.2rem;
        }

        .form-label-custom {
            font-weight: 600;
            font-size: 0.82rem;
            color: #475569;
            margin-bottom: 5px;
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
            background-color: #ffffff !important;
            border-radius: 9px !important;
            font-size: 0.9rem !important;
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

        .form-input-custom::placeholder {
            color: #94a3b8 !important;
            font-size: 0.85rem !important;
        }

        /* Submit Button */
        .btn-register-rims {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%) !important;
            border: none !important;
            color: #ffffff !important;
            padding: 10px 18px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 0.92rem !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25) !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .btn-register-rims:hover {
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35) !important;
            color: #ffffff !important;
        }

        .login-link-rims {
            color: #059669;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .login-link-rims:hover {
            color: #047857;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container d-flex flex-column align-items-center justify-content-center">
    <div class="card register-card-rims shadow-lg">
        <div class="row g-0 align-items-center">
            <!-- Left Column: Big Centered Logo -->
            <div class="col-md-5 logo-col-wrapper text-center">
                <img src="{{ asset('images/logo.png') }}" 
                     style="max-width: 250px; width: 100%; height: auto; display: block; margin: 0 auto; filter: drop-shadow(0 6px 16px rgba(0,0,0,0.06));" 
                     alt="SmartData Logo">
            </div>

            <!-- Right Column: Form Header, Inputs, and Register Button -->
            <div class="col-md-7 form-col-wrapper">
                <h3 class="register-heading">ลงทะเบียนเข้าใช้งาน</h3>
                <p class="register-subheading">สมัครสมาชิกเพื่อเข้าถึงระบบ SmartData</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Name Input -->
                    <div class="mb-2.5">
                        <label for="name" class="form-label-custom">ชื่อ-นามสกุล</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-id-badge input-icon"></i>
                            <input id="name" type="text" class="form-control form-input-custom @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="กรอกชื่อและนามสกุลจริง">
                        </div>
                        @error('name')
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <!-- Email Input -->
                    <div class="mb-2.5">
                        <label for="email" class="form-label-custom">อีเมล</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-envelope input-icon"></i>
                            <input id="email" type="email" class="form-control form-input-custom @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="ตัวอย่าง: name@email.com">
                        </div>
                        @error('email')
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <!-- Username Input (CID) -->
                    <div class="mb-2.5">
                        <label for="username" class="form-label-custom">Username (เลขบัตรประชาชน / CID)</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user input-icon"></i>
                            <input id="username" type="text" class="form-control form-input-custom @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" placeholder="เลขบัตรประชาชน 13 หลัก" maxlength="13" pattern="[0-9]{13}">
                        </div>
                        @error('username')
                            <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <!-- Password Inputs Row -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label-custom">รหัสผ่าน</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input id="password" type="password" class="form-control form-input-custom @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="ตั้งรหัสผ่าน">
                            </div>
                            @error('password')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password-confirm" class="form-label-custom">ยืนยันรหัสผ่าน</label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-shield-halved input-icon"></i>
                                <input id="password-confirm" type="password" class="form-control form-input-custom" name="password_confirmation" required autocomplete="new-password" placeholder="พิมพ์อีกครั้ง">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-3">
                        <button type="submit" class="btn btn-register-rims">
                            <i class="fa-solid fa-user-plus me-1.5"></i> ลงทะเบียนเข้าใช้งาน
                        </button>
                    </div>

                    <!-- Login Redirect Link -->
                    <div class="text-center mt-2.5 mb-0">
                        <p class="mb-0 small text-muted">
                            มีบัญชีอยู่แล้ว? <a href="{{ route('login') }}" class="login-link-rims fw-bold">เข้าสู่ระบบที่นี่</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bottom Card Footer (White Background, Left-aligned, Bold SmartData) -->
        <div class="card-footer bg-white border-0 py-2.5 px-4 text-start text-muted" style="border-top: 1px solid #f1f5f9 !important; font-size: 0.8rem; padding-left: 2.5rem !important;">
            <strong class="text-dark">SmartData</strong> : Huataphanhospital Data System &copy; 2026. All Rights Reserved.
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Double-click protection on registration form
        const regForm = document.querySelector('form');
        if (regForm) {
            regForm.addEventListener('submit', function() {
                const submitBtn = regForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังลงทะเบียน...';
                }
            });
        }
    });
</script>
</body>
</html>
