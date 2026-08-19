<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SmartData | ยืนยันรหัสความปลอดภัย 2FA</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            padding-top: 80px;
            font-family: 'Source Sans Pro', 'Sarabun', sans-serif;
        }
        .verify-card {
            border-radius: 16px;
            overflow: hidden;
        }
        .otp-input-field {
            font-size: 1.75rem;
            letter-spacing: 6px;
            text-align: center;
            font-weight: 700;
            border-radius: 8px;
            border: 2px solid #ced4da;
            transition: border-color 0.25s;
        }
        .otp-input-field:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card verify-card border-0 shadow-lg">
                <div class="card-body p-4 text-center">
                    <div class="mb-3 text-success">
                        <i class="fas fa-shield-halved fa-3x"></i>
                    </div>
                    
                    <h5 class="fw-bold text-dark mb-2">ยืนยันตัวตนเข้าระบบ (2FA)</h5>
                    <p class="text-muted small mb-4">ระบบความปลอดภัยต้องการรหัสผ่านขั้นตอนที่สองเพื่อเข้าใช้งาน กรุณากรอกรหัส OTP ที่ได้รับทาง หมอพร้อม LineOA</p>
                    
                    <form method="POST" action="{{ url('/login/verify-2fa') }}">
                        @csrf

                        <div class="mb-4">
                            <input id="otp" type="text" 
                                   class="form-control otp-input-field @error('otp') is-invalid @enderror" 
                                   name="otp" 
                                   maxlength="6" 
                                   required 
                                   autocomplete="off" 
                                   autofocus 
                                   placeholder="xxxxxx">

                            @error('otp')
                                <span class="invalid-feedback text-start mt-2" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-success fw-bold py-2 shadow-sm">
                                <i class="fas fa-unlock me-2"></i>ยืนยันรหัส OTP
                            </button>
                            <a href="{{ route('login') }}" class="btn btn-light border py-2 text-muted">
                                <i class="fas fa-arrow-left me-1"></i>กลับไปหน้า Login
                            </a>
                        </div>
                    </form>
                    
                    <div class="text-muted small mt-4 pt-3 border-top">
                        <span id="cooldown_label"><i class="far fa-clock me-1"></i>รหัส OTP จะมีอายุใช้งานใน 5 นาที</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'ยืนยันรหัสไม่สำเร็จ',
                text: "{{ $errors->first() }}",
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#dc3545'
            });
        @endif

        // Auto numeric input only
        const otpInput = document.getElementById('otp');
        if (otpInput) {
            otpInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });
</script>
</body>
</html>
