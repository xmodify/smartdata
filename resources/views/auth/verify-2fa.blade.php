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
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

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
        .verify-card {
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(25, 135, 84, 0.08), 0 5px 15px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(25, 135, 84, 0.16) !important;
            overflow: hidden;
            width: 100%;
            max-width: 460px; /* ขยายขนาดเพื่อให้ข้อความยาวไม่ตกบรรทัด */
            background: #ffffff;
        }
        .otp-input-field {
            font-size: 1.6rem !important;
            letter-spacing: 8px !important;
            text-align: center !important;
            font-weight: 700 !important;
            border-radius: 12px !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #f8fafc !important;
            height: 52px !important;
            color: #1e293b !important;
            transition: all 0.3s ease !important;
        }
        .otp-input-field::placeholder {
            color: #cbd5e1 !important;
            letter-spacing: 8px !important;
        }
        .otp-input-field:focus {
            outline: none !important;
            border-color: #198754 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.12) !important;
        }
        .btn-success-otp {
            background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
            border: none !important;
            color: #ffffff !important;
            padding: 12px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            width: 100% !important;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.18) !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-success-otp:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.28) !important;
        }
        .btn-light-otp {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            color: #475569 !important;
            padding: 12px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            width: 100% !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02) !important;
            transition: all 0.3s ease !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none !important;
        }
        .btn-light-otp:hover {
            background-color: #f8fafc !important;
            color: #1e293b !important;
        }
        .resend-link {
            color: #198754 !important;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .resend-link:hover {
            color: #157347 !important;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="verify-card shadow-lg">
    <div class="card-body p-4 text-center">
        <!-- Shield Badge Check -->
        <div class="d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background-color: #e8f5e9; border-radius: 50%;">
            <div class="position-relative d-inline-flex align-items-center justify-content-center">
                <i class="fas fa-shield fa-2x" style="color: #198754;"></i>
                <i class="fas fa-check position-absolute text-white" style="font-size: 0.95rem; top: 9px; left: 50%; transform: translateX(-50%);"></i>
            </div>
        </div>
        
        <h4 class="fw-bold text-dark mb-3" style="font-size: 1.5rem; letter-spacing: -0.5px;">ยืนยันตัวตนเข้าระบบ (2FA)</h4>
        <p class="text-muted small mb-4" style="font-size: 0.85rem; line-height: 1.6;">ระบบความปลอดภัยต้องการรหัสผ่านขั้นตอนที่สองเพื่อเข้าใช้งาน<br>กรุณากรอกรหัส OTP ที่ได้รับทาง หมอพร้อม LineOA</p>
        
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
                       placeholder="X X X X X X">

                @error('otp')
                    <span class="invalid-feedback text-start mt-2" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="d-grid gap-2 mb-3">
                <button type="submit" class="btn btn-success-otp fw-bold">
                    <i class="fas fa-lock me-2"></i>ยืนยันรหัส OTP
                </button>
                <a href="{{ route('login') }}" class="btn btn-light-otp fw-bold">
                    <i class="fas fa-arrow-left me-2"></i>กลับไปหน้า Login
                </a>
            </div>
        </form>
        
        <div class="text-muted small mt-4 pt-3 border-top" id="timer_container" style="font-size: 0.9rem;">
            <span id="cooldown_label"><i class="far fa-clock me-1"></i>หมดเวลาใน <span id="timer_seconds">{{ $remainingSeconds }}</span> วินาที</span>
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
 
        // Auto numeric input only & auto-submit on 6 digits
        const otpInput = document.getElementById('otp');
        const verifyFormEl = document.querySelector('form');
        if (otpInput) {
            otpInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length === 6 && verifyFormEl) {
                    verifyFormEl.requestSubmit();
                }
            });
        }

        // Countdown Timer Logic
        let remainingSeconds = parseInt('{{ $remainingSeconds }}');
        const timerContainer = document.getElementById('timer_container');
        let countdownInterval;

        function startCountdown() {
            if (remainingSeconds <= 0) {
                showResendLink();
                return;
            }

            timerContainer.innerHTML = `<span id="cooldown_label"><i class="far fa-clock me-1"></i>หมดเวลาใน <span id="timer_seconds">${remainingSeconds}</span> วินาที</span>`;
            
            clearInterval(countdownInterval);
            countdownInterval = setInterval(function() {
                remainingSeconds--;
                if (remainingSeconds <= 0) {
                    clearInterval(countdownInterval);
                    showResendLink();
                } else {
                    const secondsSpan = document.getElementById('timer_seconds');
                    if (secondsSpan) {
                        secondsSpan.textContent = remainingSeconds;
                    }
                }
            }, 1000);
        }

        function showResendLink() {
            timerContainer.innerHTML = `ไม่ได้รับรหัสใช่ไหม? <a id="resend_otp_link" class="resend-link">ส่งรหัสใหม่อีกครั้ง</a>`;
            
            const resendLink = document.getElementById('resend_otp_link');
            if (resendLink) {
                resendLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    resendLink.style.pointerEvents = 'none';
                    resendLink.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังส่ง...';
                    
                    fetch('{{ route("login.send_otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ username: '{{ $username }}' })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ!',
                                text: 'ส่งรหัส OTP ใหม่เรียบร้อยแล้ว',
                                confirmButtonText: 'ตกลง',
                                confirmButtonColor: '#198754'
                            }).then(() => {
                                // Reset timer back to 120 seconds
                                remainingSeconds = 120;
                                startCountdown();
                            });
                        } else {
                            SwendError(data.message);
                        }
                    })
                    .catch(err => {
                        SwendError('ไม่สามารถส่งรหัส OTP ได้ในขณะนี้: ' + err.message);
                    });
                });
            }
        }

        function SwendError(msg) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: msg,
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#dc3545'
            }).then(() => {
                showResendLink();
            });
        }

        startCountdown();

        // Prevent double click on form submission
        const verifyForm = document.querySelector('form');
        if (verifyForm) {
            verifyForm.addEventListener('submit', function() {
                const submitBtn = verifyForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังยืนยัน...';
                }
            });
        }
    });
</script>
</body>
</html>
