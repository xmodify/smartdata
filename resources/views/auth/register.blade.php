<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ลงทะเบียน - SmartData</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Bootstrap CSS & FontAwesome -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: radial-gradient(circle at 50% 50%, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', 'Prompt', sans-serif;
            margin: 0;
            padding-top: 50px; /* ระยะห่างจากด้านบน 50px เสมอกันทุกหน้า */
        }
        .card-register-custom {
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(2, 132, 199, 0.06), 0 5px 15px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(2, 132, 199, 0.18) !important;
            overflow: hidden;
            transition: all 0.3s ease;
            background-color: #ffffff;
        }
        .card-register-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(2, 132, 199, 0.1), 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .card-register-header {
            background: linear-gradient(135deg, #0284c7 0%, #16a34a 100%);
            color: white;
            padding: 1.8rem;
            text-align: center;
        }

        /* Form styling */
        .form-label-custom {
            font-weight: 700;
            font-size: 0.85rem;
            color: #0284c7;
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
            height: 42px !important;
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

        /* Submit Button & Links */
        .btn-register-submit {
            background: linear-gradient(135deg, #0284c7 0%, #16a34a 100%) !important;
            border: none !important;
            color: #ffffff !important;
            padding: 12px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.18) !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            width: 100%;
        }

        .btn-register-submit:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(2, 132, 199, 0.28) !important;
        }

        .divider-register {
            border: 0;
            height: 1px;
            background: #e2e8f0;
            margin: 15px 0;
            opacity: 0.5;
        }

        .login-link-custom {
            color: #0284c7;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .login-link-custom:hover {
            color: #015f8e;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-register-custom shadow-lg">
                <div class="card-register-header">
                    <div class="d-inline-flex align-items-center justify-content-center mb-2" style="width: 55px; height: 55px; background-color: rgba(255, 255, 255, 0.2); border-radius: 50%;">
                        <i class="fa-solid fa-user-plus fa-lg text-white"></i>
                    </div>
                    <h4 class="mb-1 fw-bold" style="font-size: 1.35rem; letter-spacing: -0.5px;">ลงทะเบียนเข้าใช้งาน</h4>
                    <p class="mb-0 small opacity-75">สมัครสมาชิกเพื่อเข้าถึงระบบ SmartData</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Name Input -->
                        <div class="mb-3">
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
                        <div class="mb-3">
                            <label for="email" class="form-label-custom">อีเมล</label>
                            <div class="input-wrapper">
                                <i class="fa-regular fa-envelope input-icon"></i>
                                <input id="email" type="email" class="form-control form-input-custom @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="ตัวอย่าง: name@email.com">
                            </div>
                            @error('email')
                                <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <!-- Username Input -->
                        <div class="mb-3">
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

                        <!-- Submit & Redirect Links -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn-register-submit fw-bold">
                                <i class="fa-solid fa-user-check me-2"></i>ลงทะเบียน
                            </button>
                            <hr class="divider-register">
                            <div class="text-center">
                                <a href="{{ route('login') }}" class="login-link-custom small fw-bold">
                                    มีบัญชีอยู่แล้ว? เข้าสู่ระบบ
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
