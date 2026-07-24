<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>แสดงความคิดเห็น / เสนอแนะ / ร้องเรียน</title>
     <!-- Local Assets (Favicon Icons) --> 
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- SweetAlert2 -->
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:     #1565c0;
            --primary-lt:  #1976d2;
            --teal:        #00897b;
            --teal-lt:     #26a69a;
            --bg:          #eef2f7;
            --surface:     #ffffff;
            --border:      #dde3ec;
            --text:        #1e293b;
            --text-muted:  #64748b;
            --radius:      14px;

            --praise:  #388e3c;
            --suggest: #f57c00;
            --complain:#c62828;
            --other:   #6a1b9a;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 36px 16px 48px;
        }

        /* ─── Wrapper ─── */
        .page-wrap {
            width: 100%;
            max-width: 680px;
        }

        /* ─── Top bar (mimics SmartData header) ─── */
        .top-bar {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
        }

        .top-bar-logo {
            background: linear-gradient(135deg, var(--primary-lt), var(--teal));
            border-radius: 10px;
            width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(21,101,192,0.3);
        }

        .top-bar-title h1 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
        }

        .top-bar-title p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 1px;
        }

        /* ─── Main card ─── */
        .form-card {
            background: var(--surface);
            border-radius: 18px;
            border: 1px solid var(--border);
            box-shadow:
                0 1px 3px rgba(0,0,0,0.05),
                0 8px 32px rgba(21,101,192,0.07);
            overflow: hidden;
            animation: cardIn .45s cubic-bezier(.22,1,.36,1) both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Card Header ─── */
        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, #0d47a1 40%, var(--teal) 100%);
            padding: 28px 32px 24px;
            position: relative;
            overflow: hidden;
        }

        .card-header::after {
            content: '';
            position: absolute;
            right: -40px; top: -40px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .card-header::before {
            content: '';
            position: absolute;
            right: 30px; bottom: -60px;
            width: 140px; height: 140px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
        }

        .header-inner {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
        }

        .header-icon-wrap {
            width: 54px; height: 54px;
            background: rgba(255,255,255,0.15);
            border: 1.5px solid rgba(255,255,255,0.25);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            color: #fff;
            flex-shrink: 0;
            backdrop-filter: blur(6px);
        }

        .header-text h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
        }

        .header-text p {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.75);
            margin-top: 3px;
        }

        /* ─── Card Body ─── */
        .card-body {
            padding: 28px 32px 32px;
        }

        /* ─── Alert Success ─── */
        .alert-success {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-left: 4px solid var(--praise);
            border-radius: 10px;
            padding: 13px 16px;
            margin-bottom: 24px;
            animation: slideDown .35s ease both;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .alert-success .alert-icon { color: var(--praise); font-size: 16px; margin-top: 1px; flex-shrink: 0; }
        .alert-success .alert-text { color: #1b5e20; font-size: 0.9rem; font-weight: 500; }

        /* ─── Section heading ─── */
        .section-heading {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            margin-top: 24px;
        }

        .section-heading:first-of-type { margin-top: 0; }

        .section-heading::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .section-heading i {
            color: var(--primary-lt);
            font-size: 13px;
        }

        /* ─── Type Grid ─── */
        .type-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 4px;
        }

        .type-option { position: relative; }

        .type-option input[type="radio"] {
            position: absolute; opacity: 0; width: 0; height: 0;
        }

        .type-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 7px;
            padding: 14px 8px 12px;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            background: #f8fafd;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
            text-align: center;
            user-select: none;
        }

        .type-option label .type-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            transition: transform .2s ease;
            flex-shrink: 0;
        }

        /* Icon bg colors */
        .type-option:nth-child(1) label .type-icon { background: #e8f5e9; color: var(--praise); }
        .type-option:nth-child(2) label .type-icon { background: #fff3e0; color: var(--suggest); }
        .type-option:nth-child(3) label .type-icon { background: #ffebee; color: var(--complain); }
        .type-option:nth-child(4) label .type-icon { background: #f3e5f5; color: var(--other); }

        .type-option label:hover {
            border-color: #90caf9;
            background: #f0f7ff;
            color: var(--text);
        }

        .type-option label:hover .type-icon { transform: scale(1.12); }

        /* Checked states */
        .type-option input[value="คำชมเชย"]:checked + label {
            border-color: var(--praise);
            background: #e8f5e9;
            color: var(--praise);
            box-shadow: 0 0 0 3px rgba(56,142,60,0.12);
        }

        .type-option input[value="ข้อเสนอแนะ"]:checked + label {
            border-color: var(--suggest);
            background: #fff8f0;
            color: var(--suggest);
            box-shadow: 0 0 0 3px rgba(245,124,0,0.12);
        }

        .type-option input[value="ข้อร้องเรียน"]:checked + label {
            border-color: var(--complain);
            background: #fff5f5;
            color: var(--complain);
            box-shadow: 0 0 0 3px rgba(198,40,40,0.1);
        }

        .type-option input[value="อื่น ๆ"]:checked + label {
            border-color: var(--other);
            background: #fdf4ff;
            color: var(--other);
            box-shadow: 0 0 0 3px rgba(106,27,154,0.1);
        }

        /* ─── Form Fields ─── */
        .form-group { margin-bottom: 16px; }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .form-label .badge-opt {
            font-size: 0.68rem;
            font-weight: 400;
            color: var(--text-muted);
            background: #f1f5f9;
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 1px 6px;
            margin-left: 6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .field-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
            width: 16px;
            text-align: center;
        }

        .input-wrap.textarea-mode .field-icon {
            top: 13px;
            transform: none;
        }

        .form-control {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            font-family: inherit;
            font-size: 0.88rem;
            padding: 10px 13px 10px 36px;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .form-control::placeholder { color: #b0bec5; }

        .form-control:focus {
            border-color: var(--primary-lt);
            box-shadow: 0 0 0 3px rgba(25,118,210,0.12);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .field-error {
            margin-top: 5px;
            font-size: 0.78rem;
            color: var(--complain);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ─── Callback Toggle ─── */
        .cb-row {
            display: flex;
            gap: 10px;
        }

        .cb-option { position: relative; flex: 1; }

        .cb-option input[type="radio"] {
            position: absolute; opacity: 0; width: 0; height: 0;
        }

        .cb-option label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: #f8fafd;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .18s ease;
        }

        .cb-option label:hover { border-color: #90caf9; color: var(--text); background: #f0f7ff; }

        .cb-option input:checked + label {
            border-color: var(--primary-lt);
            background: #e3f0fd;
            color: var(--primary);
            box-shadow: 0 0 0 3px rgba(25,118,210,0.1);
        }

        /* ─── Contact fields accordion ─── */
        #contact-fields {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height .4s ease, opacity .3s ease;
        }

        #contact-fields.open {
            max-height: 260px;
            opacity: 1;
        }

        .contact-inner {
            padding-top: 4px;
        }

        /* ─── Disclaimer ─── */
        .disclaimer {
            display: flex;
            gap: 10px;
            background: #fff8e1;
            border: 1px solid #ffe082;
            border-left: 4px solid #f9a825;
            border-radius: 10px;
            padding: 12px 16px;
            margin: 20px 0 24px;
        }

        .disclaimer i { color: #f9a825; font-size: 14px; margin-top: 2px; flex-shrink: 0; }
        .disclaimer p { font-size: 0.82rem; color: #5d4037; line-height: 1.65; }

        /* ─── Buttons ─── */
        .action-row {
            display: flex;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 26px;
            border-radius: 9px;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all .2s ease;
        }

        .btn-primary {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-lt), var(--primary));
            color: #fff;
            box-shadow: 0 3px 12px rgba(21,101,192,0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1565c0, #0d47a1);
            box-shadow: 0 5px 16px rgba(21,101,192,0.4);
            transform: translateY(-1px);
        }

        .btn-primary:active { transform: translateY(0); }

        .btn-secondary {
            background: #f1f5f9;
            border: 1.5px solid var(--border);
            color: var(--text-muted);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: var(--text);
        }

        /* ─── Responsive ─── */
        @media (max-width: 560px) {
            body { padding: 20px 12px 36px; }
            .card-header { padding: 22px 20px 18px; }
            .card-body   { padding: 20px 20px 24px; }
            .type-grid   { grid-template-columns: repeat(2, 1fr); }
            .action-row  { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="page-wrap">

    {{-- Top bar --}}
    <div class="top-bar">
        <div class="top-bar-logo">
            <i class="fas fa-hospital-user"></i>
        </div>
        <div class="top-bar-title">
            <h1>โรงพยาบาลหัวตะพาน</h1>
            <p>ระบบรับเรื่องร้องเรียน / เสนอแนะ / ชมเชย</p>
        </div>
    </div>

    {{-- Main card --}}
    <div class="form-card">

        {{-- Header --}}
        <div class="card-header">
            <div class="header-inner">
                <div class="header-icon-wrap">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <div class="header-text">
                    <h2>แบบฟอร์มแสดงความคิดเห็น</h2>
                    <p>ความคิดเห็นของท่านมีคุณค่าต่อการพัฒนาบริการของเรา</p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="card-body">

            @if ($message = Session::get('success'))
            <div id="successMsg" data-message="{{ $message }}"></div>
            @endif

            <form action="{{ route('customer_complain.store') }}" method="POST" id="complainForm">
                @csrf

                {{-- Type --}}
                <div class="section-heading">
                    <i class="fas fa-tag"></i> ประเภทความคิดเห็น
                </div>
                <div class="type-grid">
                    <div class="type-option">
                        <input type="radio" name="type" id="type1" value="คำชมเชย"
                               {{ old('type','คำชมเชย') === 'คำชมเชย' ? 'checked' : '' }}>
                        <label for="type1">
                            <span class="type-icon"><i class="fas fa-thumbs-up"></i></span>
                            คำชมเชย
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" id="type2" value="ข้อเสนอแนะ"
                               {{ old('type') === 'ข้อเสนอแนะ' ? 'checked' : '' }}>
                        <label for="type2">
                            <span class="type-icon"><i class="fas fa-lightbulb"></i></span>
                            ข้อเสนอแนะ
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" id="type3" value="ข้อร้องเรียน"
                               {{ old('type') === 'ข้อร้องเรียน' ? 'checked' : '' }}>
                        <label for="type3">
                            <span class="type-icon"><i class="fas fa-bullhorn"></i></span>
                            ข้อร้องเรียน
                        </label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="type" id="type4" value="อื่น ๆ"
                               {{ old('type') === 'อื่น ๆ' ? 'checked' : '' }}>
                        <label for="type4">
                            <span class="type-icon"><i class="fas fa-ellipsis"></i></span>
                            อื่น ๆ
                        </label>
                    </div>
                </div>

                {{-- Sender info --}}
                <div class="section-heading" style="margin-top:24px;">
                    <i class="fas fa-user"></i> ข้อมูลผู้ส่ง
                </div>

                <div class="form-group">
                    <label class="form-label" for="name">
                        ชื่อ-สกุล
                        <span class="badge-opt">ไม่บังคับ</span>
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-user field-icon"></i>
                        <input type="text" name="name" id="name" class="form-control"
                               placeholder="ระบุชื่อ-สกุล หรือพิมพ์ ไม่ระบุ"
                               value="{{ old('name') }}">
                    </div>
                    @error('name')
                        <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="detail">
                        รายละเอียด
                        <span class="badge-opt">ไม่บังคับ</span>
                    </label>
                    <div class="input-wrap textarea-mode">
                        <i class="fas fa-align-left field-icon"></i>
                        <textarea name="detail" id="detail" class="form-control" rows="4"
                                  placeholder="กรุณาระบุรายละเอียดที่ท่านต้องการแสดงความคิดเห็น / เสนอแนะ">{{ old('detail') }}</textarea>
                    </div>
                    @error('detail')
                        <div class="field-error"><i class="fas fa-triangle-exclamation"></i> {{ $message }}</div>
                    @enderror
                </div>

                {{-- Callback --}}
                <div class="section-heading">
                    <i class="fas fa-phone"></i> การติดต่อกลับ
                </div>

                <div class="form-group">
                    <label class="form-label">ต้องการให้ติดต่อกลับหรือไม่</label>
                    <div class="cb-row">
                        <div class="cb-option">
                            <input type="radio" name="call_back" id="cb_no" value="ไม่ต้องการ"
                                   {{ old('call_back','ไม่ต้องการ') === 'ไม่ต้องการ' ? 'checked' : '' }}>
                            <label for="cb_no">
                                <i class="fas fa-xmark"></i> ไม่ต้องการ
                            </label>
                        </div>
                        <div class="cb-option">
                            <input type="radio" name="call_back" id="cb_yes" value="ต้องการ"
                                   {{ old('call_back') === 'ต้องการ' ? 'checked' : '' }}>
                            <label for="cb_yes">
                                <i class="fas fa-phone"></i> ต้องการ
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Contact fields (slide in when ต้องการ) --}}
                <div id="contact-fields">
                    <div class="contact-inner">
                        <div class="form-group">
                            <label class="form-label" for="phone">
                                หมายเลขโทรศัพท์
                                <span class="badge-opt">ไม่บังคับ</span>
                            </label>
                            <div class="input-wrap">
                                <i class="fas fa-mobile-screen field-icon"></i>
                                <input type="tel" name="phone" id="phone" class="form-control"
                                       maxlength="10" placeholder="ระบุเบอร์โทรศัพท์ 10 หลัก"
                                       value="{{ old('phone') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">
                                อีเมล์
                                <span class="badge-opt">ไม่บังคับ</span>
                            </label>
                            <div class="input-wrap">
                                <i class="fas fa-envelope field-icon"></i>
                                <input type="email" name="email" id="email" class="form-control"
                                       placeholder="ตัวอย่าง name@example.com"
                                       value="{{ old('email') }}">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Disclaimer --}}
                <div class="disclaimer">
                    <i class="fas fa-lock"></i>
                    <p>ข้อมูลชื่อ-สกุล และหมายเลขโทรศัพท์ของท่านจะถูกเก็บเป็น<strong>ความลับ</strong><br>
                       ไม่มีผลใด ๆ ต่อท่านที่แสดงความคิดเห็น / เสนอแนะ</p>
                </div>

                {{-- Actions --}}
                <div class="action-row">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> ส่งข้อมูล
                    </button>
                    <button type="reset" class="btn btn-secondary" id="resetBtn">
                        <i class="fas fa-rotate-left"></i> ล้างข้อมูล
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<script>
    const cbYes = document.getElementById('cb_yes');
    const cbNo  = document.getElementById('cb_no');
    const contactFields = document.getElementById('contact-fields');

    function toggleContact() {
        if (cbYes.checked) {
            contactFields.classList.add('open');
        } else {
            contactFields.classList.remove('open');
        }
    }

    cbYes.addEventListener('change', toggleContact);
    cbNo.addEventListener('change',  toggleContact);
    toggleContact(); // init

    document.getElementById('resetBtn').addEventListener('click', function () {
        setTimeout(() => {
            document.getElementById('type1').checked = true;
            document.getElementById('cb_no').checked = true;
            toggleContact();
        }, 10);
    });

    // ─── SweetAlert confirm before submit ───
    document.getElementById('complainForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const form = this;

        // Get selected type label
        const typeEl = document.querySelector('input[name="type"]:checked');
        const typeLabel = typeEl ? typeEl.value : 'ความคิดเห็น';

        Swal.fire({
            title: 'ยืนยันการส่งข้อมูล',
            html: `คุณต้องการส่ง <strong>${typeLabel}</strong><br>ใช่หรือไม่?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane"></i> ยืนยัน ส่งข้อมูล',
            cancelButtonText: '<i class="fas fa-xmark"></i> ยกเลิก',
            confirmButtonColor: '#1976d2',
            cancelButtonColor: '#90a4ae',
            reverseButtons: false,
            focusConfirm: false,
            customClass: {
                popup: 'swal-th-font',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังส่งข้อมูล...';
                form.submit();
            }
        });
    });

    // ─── SweetAlert success toast after redirect ───
    const successEl = document.getElementById('successMsg');
    if (successEl) {
        const msg = successEl.dataset.message;
        Swal.fire({
            icon: 'success',
            title: 'ส่งข้อมูลสำเร็จ!',
            text: msg,
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#1976d2',
            timer: 5000,
            timerProgressBar: true,
            customClass: {
                popup: 'swal-th-font',
            }
        });
    }
</script>
</body>
</html>
