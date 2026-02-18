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
            padding: 0.8rem 1.25rem;
            margin: 0.2rem 0.8rem;
            background-color: transparent;
            border: none;
            color: #5a5c69;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.25s ease;
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
            background-color: #f8f9fc;
            display: none;
        }
        
        .sidebar-submenu.show {
            display: block;
        }
        
        .sidebar-submenu .list-group-item {
            padding-left: 3.2rem;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            font-size: 0.9rem;
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

                <a href="{{ url('/medicalrecord') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                    <i class="fas fa-file-waveform me-2" style="color: #4e73df;"></i> ข้อมูลและสถิติ
                </a>

                <!-- Service Menu (Renamed and Merged) -->
                <div class="sidebar-dropdown">
                    <a href="#serviceSubmenu" class="list-group-item list-group-item-action bg-transparent text-dark dropdown-toggle" data-bs-toggle="collapse" aria-expanded="false">
                        <i class="fas fa-hospital" style="color: #20c997;"></i> หน่วยบริการ
                    </a>
                    <div class="collapse sidebar-submenu shadow-sm" id="serviceSubmenu">
                        <a href="{{ url('/service_opd') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-user-nurse me-2" style="color: #4e73df;"></i> ผู้ป่วยนอก
                        </a>
                        <a href="{{ url('/service_ipd') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-bed-pulse me-2" style="color: #1cc88a;"></i> ผู้ป่วยใน
                        </a>
                        <a href="{{ url('/service_er') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-truck-medical me-2" style="color: #e74a3b;"></i> อุบัติเหตุ-ฉุกเฉิน
                        </a>
                        <a href="{{ url('/service_drug') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-pills me-2" style="color: #f6c23e;"></i> เภสัชกรรม
                        </a>
                        <a href="{{ url('/service_mental') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-brain me-2" style="color: #36b9cc;"></i> สุขภาพจิต|ยาเสพติด
                        </a>
                        <a href="{{ url('/service_physic') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-walking me-2" style="color: #6610f2;"></i> กายภาพบำบัด
                        </a>
                        <a href="{{ url('/service_healthmed') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-leaf me-2" style="color: #20c997;"></i> แพทย์แผนไทย
                        </a>
                        <a href="{{ url('/service_dent') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-tooth me-2" style="color: #fd7e14;"></i> ทันตกรรม
                        </a> 
                        <a href="{{ url('/service_ncd') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-clipboard-list me-2" style="color: #858796;"></i> คลินิกโรคเรื้อรัง
                        </a>
                        <a href="{{ url('/service_pcu') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-hand-holding-heart me-2" style="color: #5a5c69;"></i> งานเชิงรุก
                        </a>
                        <a href="{{ url('/service_xray') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-x-ray me-2" style="color: #4e73df;"></i> รังสีวิทยา
                        </a> 
                        <a href="{{ url('/service_lab') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-flask me-2" style="color: #36b9cc;"></i> เทคนิคการแพทย์
                        </a>
                        <a href="{{ url('/service_operation') }}" class="list-group-item list-group-item-action bg-transparent text-dark">
                            <i class="fas fa-scissors me-2" style="color: #e74a3b;"></i> ห้องผ่าตัด
                        </a>
                    </div>
                </div>

                <!-- Major Disease Menu -->
                <div class="sidebar-dropdown">
                    <a href="#diseaseSubmenu" class="list-group-item list-group-item-action bg-transparent text-dark dropdown-toggle" data-bs-toggle="collapse" aria-expanded="false">
                        <i class="fas fa-virus" style="color: #e74a3b;"></i> รายโรคสำคัญ
                    </a>
                    <div class="collapse sidebar-submenu shadow-sm" id="diseaseSubmenu">
                        <a href="{{ url('medicalrecord_diag/alcohol_withdrawal') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-glass-whiskey me-2" style="color: #fd7e14;"></i> Alcohol Withdrawal
                        </a> 
                        <a href="{{ url('medicalrecord_diag/asthma') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-lungs me-2" style="color: #36b9cc;"></i> Asthma
                        </a> 
                        <a href="{{ url('medicalrecord_diag/copd') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-lungs-virus me-2" style="color: #1cc88a;"></i> COPD
                        </a> 
                        <a href="{{ url('medicalrecord_diag/fracture') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-bone me-2" style="color: #858796;"></i> กระดูกสะโพกหัก
                        </a> 
                        <a href="{{ url('medicalrecord_diag/head_injury') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-user-injured me-2" style="color: #e74a3b;"></i> Head Injury
                        </a> 
                        <a href="{{ url('medicalrecord_diag/ihd') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-heart-pulse me-2" style="color: #e74a3b;"></i> IHD
                        </a> 
                        <a href="{{ url('medicalrecord_diag/mi') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-heart-circle-exclamation me-2" style="color: #e74a3b;"></i> MI
                        </a>                                         
                        <a href="{{ url('medicalrecord_diag/palliative_care') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-hands-holding-child me-2" style="color: #20c997;"></i> Palliative Care
                        </a> 
                        <a href="{{ url('medicalrecord_diag/pneumonia') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-biohazard me-2" style="color: #6610f2;"></i> Pneumonia
                        </a> 
                        <a href="{{ url('medicalrecord_diag/sepsis') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-bacteria me-2" style="color: #f6c23e;"></i> Sepsis
                        </a> 
                        <a href="{{ url('medicalrecord_diag/septic_shock') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-burst me-2" style="color: #e74a3b;"></i> Septic Shock
                        </a> 
                        <a href="{{ url('medicalrecord_diag/stroke') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-brain me-2" style="color: #6610f2;"></i> Stroke
                        </a>   
                        <a href="{{ url('medicalrecord_diag/trauma') }}" class="list-group-item list-group-item-action bg-transparent text-dark py-2">
                            <i class="fas fa-person-falling me-2" style="color: #fd7e14;"></i> Trauma
                        </a> 
                    </div>
                </div>

                <!-- Dashboard Menu -->
                <div class="sidebar-dropdown">
                    <a href="#dashboardSubmenu" class="list-group-item list-group-item-action bg-transparent text-dark dropdown-toggle" data-bs-toggle="collapse" aria-expanded="false">
                        <i class="fas fa-chart-line" style="color: #6610f2;"></i> Dashboard
                    </a>
                    <div class="collapse sidebar-submenu shadow-sm" id="dashboardSubmenu">  
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
                V. 69-02-08
            </div>
        </div>
        <!-- /#sidebar-wrapper -->

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary-custom navbar-custom">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link text-white" id="menu-toggle">
                        <i class="fas fa-bars fa-lg"></i>
                    </button>
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
                                    <li><hr class="dropdown-divider"></li>
                                @endif
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

            <main class="container-fluid py-4 px-4 content-area">
                @yield('content')
            </main>
        </div>
        <!-- /#page-content-wrapper -->
    </div>
    <!-- /#wrapper -->

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
        });
    </script>
    @stack('scripts')
</body>
</html>
