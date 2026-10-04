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
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            font-family: 'Plus Jakarta Sans', 'Prompt', sans-serif;
            margin: 0;
            padding-top: 50px; /* ระยะห่างจากด้านบน 50px เท่ากับหน้า register */
            padding-bottom: 30px;
        }

        .login-card-rims {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(2, 132, 199, 0.08), 0 5px 15px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(2, 132, 199, 0.15);
            border-top: 4px solid #0284c7;
            overflow: hidden;
            width: 100%;
            max-width: 820px;
            transition: all 0.3s ease;
        }

        .login-card-rims:hover {
            box-shadow: 0 20px 45px rgba(2, 132, 199, 0.12), 0 8px 20px rgba(0, 0, 0, 0.06);
        }

        .logo-col-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
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

        .login-heading {
            font-size: 1.65rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }

        .login-subheading {
            font-size: 0.88rem;
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        .form-label-custom {
            font-weight: 600;
            font-size: 0.85rem;
            color: #475569;
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
            font-size: 0.95rem;
            transition: color 0.3s ease;
            z-index: 10;
        }

        .form-input-custom {
            padding-left: 40px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            border-radius: 10px !important;
            font-size: 0.92rem !important;
            color: #1e293b !important;
            transition: all 0.3s ease !important;
            height: 44px !important;
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
            font-size: 0.88rem !important;
        }

        /* Action Buttons */
        .btn-login-rims {
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
            flex: 1;
            white-space: nowrap;
        }

        .btn-login-rims:hover {
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35) !important;
            color: #ffffff !important;
        }

        .btn-provider-rims {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;
            border: none !important;
            color: #ffffff !important;
            padding: 10px 18px !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 0.92rem !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25) !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            white-space: nowrap;
        }

        .btn-provider-rims:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35) !important;
            color: #ffffff !important;
        }

        .register-link-rims {
            color: #059669;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .register-link-rims:hover {
            color: #047857;
            text-decoration: underline;
        }

        .footer-copyright {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container d-flex flex-column align-items-center justify-content-center">
    <div class="card login-card-rims shadow-lg">
        <div class="row g-0 align-items-center">
            <!-- Left Column: Big Centered Logo -->
            <div class="col-md-5 logo-col-wrapper text-center">
                <img src="{{ asset('images/logo.png') }}" 
                     style="max-width: 210px; width: 100%; height: auto; display: block; margin: 0 auto; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.08));" 
                     alt="SmartData Logo">
            </div>

            <!-- Right Column: Form Header, Inputs, and Action Buttons -->
            <div class="col-md-7 form-col-wrapper">
                <h3 class="login-heading">เข้าสู่ระบบ</h3>
                <p class="login-subheading">ระบุบัญชีผู้ใช้งานของท่านเพื่อเข้าสู่ระบบ SmartData</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    @if($errors->has('provider_id'))
                        <div class="alert alert-danger shadow-sm small py-2 px-3 mb-3 border-0 rounded-lg" style="background-color: rgba(220, 53, 69, 0.9); color: #fff;">
                            <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first('provider_id') }}
                        </div>
                    @endif

                    <!-- Username Field -->
                    <div class="mb-3">
                        <label for="username" class="form-label-custom">ชื่อผู้ใช้งาน (Username)</label>
                        <div class="input-wrapper">
                            <i class="fa-regular fa-user input-icon"></i>
                            <input id="username" type="text" 
                                   class="form-control form-input-custom @error('username') is-invalid @enderror" 
                                   name="username" 
                                   value="{{ old('username') }}" 
                                   required 
                                   autocomplete="username" 
                                   autofocus 
                                   placeholder="กรอก Username เข้าใช้งาน">
                        </div>
                        @error('username')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label-custom">รหัสผ่าน</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input id="password" type="password" 
                                   class="form-control form-input-custom @error('password') is-invalid @enderror" 
                                   name="password" 
                                   required
                                   autocomplete="current-password" 
                                   placeholder="กรอกรหัสผ่านเข้าใช้งาน">
                        </div>
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Action Buttons: Login & ProviderID Login -->
                    <div class="d-flex align-items-center gap-2 mb-3 mt-4">
                        <button type="submit" class="btn btn-login-rims">
                            <i class="fas fa-sign-in-alt me-1.5"></i> เข้าสู่ระบบ
                        </button>

                        @php $providerConfig = \App\Models\ProviderId::where('active', 'Y')->first(); @endphp
                        @if($providerConfig && $providerConfig->active === 'Y')
                            <a href="{{ route('login.provider_id') }}" class="btn btn-provider-rims">
                                <i class="fas fa-shield-alt me-1.5"></i> เข้าด้วย Provider ID
                            </a>
                        @endif
                    </div>

                    <!-- Register Link -->
                    <div class="text-center mt-2.5 mb-0">
                        <p class="mb-0 small text-muted">
                            ยังไม่มีบัญชีผู้ใช้งานระบบ? <a href="{{ route('register') }}" class="register-link-rims fw-bold">สมัครสมาชิกใหม่ที่นี่</a>
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
