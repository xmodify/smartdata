<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SmartData')</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --sidebar-width: 280px;
            --topbar-height: 70px;
        }
        
        body {
            overflow-x: hidden;
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
            font-size: 0.9rem; /* Slightly smaller base font */
        }

        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: 100vh;
        }

        #sidebar-wrapper {
            height: 100vh;
            width: var(--sidebar-width);
            margin-left: 0;
            transition: margin 0.25s ease-out;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            overflow-y: auto;
            overflow-x: hidden;
        }

        #wrapper.toggled #sidebar-wrapper {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        #page-content-wrapper {
            width: 100%;
            margin-left: var(--sidebar-width);
            transition: margin 0.25s ease-out;
            display: flex;
            flex-direction: column;
        }

        #wrapper.toggled #page-content-wrapper {
            margin-left: 0;
        }

        .sidebar-heading {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 1.5rem;
            color: #fff;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 800;
            color: #fff !important;
            margin-bottom: 0;
            letter-spacing: 0.5px;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .sidebar-version {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
            font-weight: normal;
            margin-top: 2px;
        }

        .list-group-item {
            position: relative;
            display: block;
            padding: 0.6rem 1rem; /* Reduced padding */
            margin: 0.1rem 0.6rem;
            background-color: transparent;
            border: none;
            color: #5a5c69;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.25s ease;
            font-size: 0.85rem; /* Smaller sidebar font */
        }

        .list-group-item:hover, .list-group-item:focus {
            color: #4e73df;
            background-color: #f8f9fc;
            transform: translateX(5px);
            text-decoration: none;
        }
        
        .list-group-item.active {
            color: #4e73df !important;
            background-color: #eaecf4;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .list-group-item i {
            width: 25px;
            text-align: center;
            margin-right: 12px;
            color: #4e73df;
            font-size: 1.1rem;
        }

        /* Sidebar Dropdown */
        .sidebar-dropdown .dropdown-toggle::after {
            display: inline-block;
            margin-left: auto;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.2s;
        }
        
        .sidebar-dropdown .dropdown-toggle[aria-expanded="true"]::after {
            transform: translateY(-50%) rotate(-180deg);
        }

        .sidebar-submenu {
            background-color: #f8f9fc !important;
        }
        
        /* Force visibility for collapse-show in sidebar */
        .sidebar-submenu.collapse.show {
            display: block !important;
            height: auto !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .sidebar-submenu .list-group-item {
            padding-left: 2.8rem;
            padding-top: 0.4rem;
            padding-bottom: 0.4rem;
            font-size: 0.85rem;
            color: #6e707e;
        }

        /* Topbar */
        .navbar-custom {
            height: var(--topbar-height);
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            background-color: #fff;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 768px) {
            #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #wrapper.toggled #sidebar-wrapper {
                margin-left: 0;
            }
            #page-content-wrapper {
                margin-left: 0;
            }
            #wrapper.toggled #page-content-wrapper {
                position: absolute;
                margin-right: calc(-1 * var(--sidebar-width));
            }
        }

        /* Custom Gradients from previous design */
        .bg-gradient-primary-custom {
            background: linear-gradient(135deg, #0268c7 0%, #17a6a7 100%);
        }
        .bg-pastel-blue { background-color: #e0f2fe; }
        .bg-pastel-teal { background-color: #f0fdfa; }

        .dashboard-card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .dashboard-card:hover { transform: translateY(-5px); }
        .icon-box-grid {
            width: 45px; height: 45px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }
        .transition-hover { transition: all 0.2s ease-in-out; }
        .transition-hover:hover { transform: translateY(-3px); z-index: 10; }

        /* Scrollbar styles for sidebar */
        #sidebar-wrapper::-webkit-scrollbar {
            width: 5px;
        }
        #sidebar-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(78, 115, 223, 0.4);
            border-radius: 10px;
        }
        #sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background-color: rgba(78, 115, 223, 0.7);
        }

        /* Sidebar Section Header */
        .sidebar-section-header {
            padding: 1.5rem 1.25rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            color: #b7b9cc;
        }

        /* Mobile Fix for Menu Toggle */
        @media (max-width: 768px) {
            #menu-toggle {
                position: fixed !important;
                top: 15px;
                left: 15px;
                z-index: 2001 !important;
                background-color: rgba(2, 104, 199, 0.8); /* Semi-transparent blue */
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        <div class="bg-white" id="sidebar-wrapper" style="box-shadow: 0.15rem 0 1.75rem 0 rgba(58, 59, 69, 0.15); border-right: none;">
            <div class="sidebar-heading bg-gradient-primary-custom">
                <a href="{{ route('dashboard') }}" class="sidebar-brand">
                    <i class="fas fa-hospital-user me-2"></i> SmartData
                </a>
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-home" style="color: #4e73df;"></i> หน้าแรก
                </a>

                @auth
                <div class="sidebar-section-header">รายงาน HOSxP</div>

                <a href="{{ route('hosxp.stats.index') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-file-waveform me-2" style="color: #4e73df;"></i> ข้อมูลและสถิติ
                </a>


                <!-- Major Disease Menu -->
                <div class="sidebar-dropdown">
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action bg-transparent text-dark dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#diagnosisSubmenu" aria-expanded="false">
                        <i class="fas fa-virus" style="color: #e74a3b;"></i> รายโรคสำคัญ
                    </a>
                    <div class="collapse sidebar-submenu" id="diagnosisSubmenu" style="background-color: #f8f9fc !important;">
                        <a href="{{ route('hosxp.diagnosis.index', ['category' => 'opd']) }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-user-nurse me-2" style="color: #4e73df;"></i> ผู้ป่วยนอก OPD
                        </a>
                        <a href="{{ route('hosxp.diagnosis.index', ['category' => 'ipd']) }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-bed-pulse me-2" style="color: #1cc88a;"></i> ผู้ป่วยใน IPD
                        </a>
                        <a href="{{ route('hosxp.diagnosis.index', ['category' => 'refer']) }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-ambulance me-2" style="color: #e74a3b;"></i> ผู้ป่วยส่งต่อ Refer
                        </a>
                    </div>
                </div>

                <!-- Dashboard Menu -->
                <div class="sidebar-dropdown">
                    <a href="javascript:void(0)" class="list-group-item list-group-item-action bg-transparent text-dark dropdown-toggle" data-bs-toggle="collapse" data-bs-target="#dashboardSubmenu" aria-expanded="false">
                        <i class="fas fa-chart-line" style="color: #6610f2;"></i> Dashboard
                    </a>
                    <div class="collapse sidebar-submenu" id="dashboardSubmenu">
                        <a href="{{ url('/dashboard/opd_mornitor') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2" target="_blank">
                            <i class="fas fa-desktop me-2" style="color: #4e73df;"></i> OPD Monitor
                        </a>   
                        <a href="{{ url('/dashboard/ipd_mornitor') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2" target="_blank">
                            <i class="fas fa-procedures me-2" style="color: #1cc88a;"></i> IPD Monitor
                        </a>   
                        <a href="{{ url('/dashboard/digitalhealth') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2" target="_blank">
                            <i class="fas fa-hand-holding-medical me-2" style="color: #36b9cc;"></i> นโยบาย 30 บาท
                        </a> 
                    </div>                 
                </div>

                <!-- HOSxP Setting Menu -->
                <a href="{{ url('/hosxp_setting') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-database me-2" style="color: #4e73df;"></i> ข้อมูลพื้นฐาน HOSxP
                </a>

                <div class="sidebar-section-header">รายงาน BackOffice</div>
                <a href="{{ url('/backoffice_hrd') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-id-card me-2" style="color: #1cc88a;"></i> งานบุคลากร
                </a>
                <a href="{{ url('/backoffice_asset') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-boxes-stacked me-2" style="color: #4e73df;"></i> งานทรัพย์สิน
                </a>
                <a href="{{ url('/backoffice_risk') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-triangle-exclamation me-2" style="color: #e74a3b;"></i> รายงานอุบัติการณ์
                </a>

                <div class="sidebar-section-header">ระบบ SmartData</div>
                <a href="{{ url('/skpcard') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-address-card me-2" style="color: #f6c23e;"></i> บัตรสังฆประชาร่วมใจ
                </a>
                <a href="{{ url('/form') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-check-to-slot me-2" style="color: #6610f2;"></i> ระบบตรวจสอบ
                </a>
                <a href="{{ url('/form') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-clipboard-check me-2" style="color: #20c997;"></i> แบบประเมิน
                </a>
                @endauth
            </div>
            
            <div class="text-center pb-4 text-muted small mt-auto" style="opacity: 0.6;">
                V. 69-02-20 13.30
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary-custom navbar-custom">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-white" id="menu-toggle" style="z-index: 1001; position: relative;">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
                    <div class="ms-3">
                        @yield('topbar_actions')
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    @auth
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle text-white fw-bold d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle fa-lg me-2 text-white-50"></i>
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="navbarDropdown">
                                @if (auth()->user()->hasAccessRole('admin'))                                    
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-external-link-alt me-2 text-success"></i> Admin Dashboard
                                        </a>
                                    </li>
                                @endif
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

            <main class="container-fluid py-4 px-0 content-area">
                @yield('content')
            </main>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header bg-gradient-primary-custom text-white border-0">
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
                        <button type="submit" class="btn text-white px-4 shadow-sm bg-gradient-primary-custom" style="border: none;">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var menuToggle = document.getElementById('menu-toggle');
            var wrapper = document.getElementById('wrapper');
            
            if(menuToggle) {
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    wrapper.classList.toggle('toggled');
                });
            }

            // Password Change Form Handling
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
        });
    </script>
    @stack('scripts')
</body>
</html>
