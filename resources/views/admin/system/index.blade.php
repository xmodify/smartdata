@extends('layouts.admin')

@section('title', 'System Settings - SmartData')

@push('styles')
<style>
    /* Custom Styling for Modern Settings UI */
    .page-header-box {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }
    .nav-pills-custom .nav-link {
        color: #64748b;
        font-weight: 600;
        font-size: 0.9rem;
        padding: 0.65rem 1.25rem;
        border-radius: 12px;
        transition: all 0.2s ease-in-out;
        border: 1px solid transparent;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .nav-pills-custom .nav-link:hover {
        background-color: #f8fafc;
        color: #334155;
    }
    .nav-pills-custom .nav-link.active {
        background: #4e73df;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.25);
    }
    .setting-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .setting-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }
    .icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .table-custom thead th {
        background-color: #f8fafc;
        color: #475569;
        font-weight: 700;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 0.85rem 1rem;
    }
    .table-custom tbody td {
        padding: 1rem;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .hover-scale {
        transition: transform 0.15s ease-in-out;
    }
    .hover-scale:hover {
        transform: scale(1.03);
    }
    .code-box {
        background: #1e293b;
        color: #38bdf8;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        font-size: 0.8rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Top Banner & Actions -->
    <div class="page-header-box p-3 p-md-4 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs hover-scale" style="width: 38px; height: 38px;" title="ย้อนกลับไปแดชบอร์ด">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2 flex-wrap">
                        <span class="icon-box bg-primary-subtle text-primary" style="width: 36px; height: 36px; font-size: 1.1rem;">
                            <i class="fas fa-sliders-h"></i>
                        </span>
                        <span>ตั้งค่าระบบ (System Settings)</span>
                        @if(app()->isLocal() || config('app.env') === 'local')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1.5 px-2.5 rounded-pill small fw-bold" style="font-size: 0.78rem;" title="ระบบกำลังทำงานในสภาพแวดล้อม Local (โหมดพัฒนา)">
                                <i class="fas fa-laptop-code text-warning me-1"></i> Local (Dev)
                            </span>
                        @elseif(app()->environment('production'))
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1.5 px-2.5 rounded-pill small fw-bold" style="font-size: 0.78rem;" title="ระบบกำลังทำงานบน Production Server">
                                <i class="fas fa-shield-alt text-success me-1"></i> Production
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle py-1.5 px-2.5 rounded-pill small fw-bold" style="font-size: 0.78rem;">
                                <i class="fas fa-server text-info me-1"></i> {{ strtoupper(config('app.env')) }}
                            </span>
                        @endif
                    </h4>
                    <div class="text-muted small">จัดการการตั้งค่าพื้นฐาน การแจ้งเตือน การเชื่อมต่อภายนอก และการอัปเกรดระบบ</div>
                </div>
            </div>

            <!-- Version Status & Action Buttons -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-lg-auto">
                <span id="versionStatusBadge" class="badge bg-light text-muted border py-2 px-3 font-monospace cursor-pointer shadow-xs" onclick="loadVersionStatus(true)" title="คลิกเพื่อตรวจสอบเวอร์ชันใหม่ล่าสุด">
                    <span class="spinner-border spinner-border-sm me-1" style="width: 0.75rem; height: 0.75rem;"></span> กำลังตรวจเวอร์ชัน...
                </span>

                <button type="button" class="btn btn-primary btn-sm px-3.5 py-2 rounded-pill shadow-sm hover-scale fw-bold d-inline-flex align-items-center gap-1.5" onclick="startAutoUpdate()" title="อัปเดตระบบและปรับปรุงโครงสร้างฐานข้อมูล">
                    <i class="fas fa-sync-alt"></i>
                    <span>Update Version</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills nav-pills-custom mb-4 gap-2 flex-wrap" id="settingTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-moph-btn" data-bs-toggle="pill" data-bs-target="#tab-moph" type="button" role="tab">
                <i class="fas fa-bell text-warning"></i> MOPH Notify
                <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill ms-1">{{ count($mophNotifies) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-alert-btn" data-bs-toggle="pill" data-bs-target="#tab-alert" type="button" role="tab">
                <i class="fas fa-bullhorn text-info"></i> MOPH Alert & 2FA
                <span class="badge bg-info-subtle text-info-emphasis rounded-pill ms-1">{{ count($mophAlerts) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tele-btn" data-bs-toggle="pill" data-bs-target="#tab-tele" type="button" role="tab">
                <i class="fab fa-telegram text-primary"></i> Telegram Notify
                <span class="badge bg-primary-subtle text-primary rounded-pill ms-1">{{ count($telegramNotifies) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-provider-btn" data-bs-toggle="pill" data-bs-target="#tab-provider" type="button" role="tab">
                <i class="fas fa-id-card text-success"></i> Provider ID & Health ID
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tasks-btn" data-bs-toggle="pill" data-bs-target="#tab-tasks" type="button" role="tab">
                <i class="fas fa-clock text-secondary"></i> Task Scheduler
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="settingTabsContent">
        
        <!-- ========================================== -->
        <!-- TAB 1: MOPH NOTIFY -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="tab-moph" role="tabpanel">
            <div class="setting-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-bell text-warning me-2"></i> รายการเชื่อมต่อ MOPH Notify</h5>
                        <div class="text-muted small">บริการส่งการแจ้งเตือนรอบการทำงานผ่านระบบ MOPH Notify API</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="text-center">ID</th>
                                <th>ชื่อบริการ</th>
                                <th>Client ID</th>
                                <th>Secret Key</th>
                                <th style="width: 120px;" class="text-center">สถานะ</th>
                                <th style="width: 100px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mophNotifies as $moph)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">#{{ $moph->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $moph->name }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="moph_client_id_{{ $moph->id }}" class="font-monospace small text-muted">********</span>
                                        <button class="btn btn-link btn-xs p-0 ms-2 text-muted toggle-moph-value" 
                                            data-id="{{ $moph->id }}" 
                                            data-type="client_id"
                                            data-value="{{ $moph->client_id }}"
                                            title="ดู/ซ่อน Client ID">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="moph_secret_{{ $moph->id }}" class="font-monospace small text-muted">********</span>
                                        <button class="btn btn-link btn-xs p-0 ms-2 text-muted toggle-moph-value" 
                                            data-id="{{ $moph->id }}" 
                                            data-type="secret"
                                            data-value="{{ $moph->secret }}"
                                            title="ดู/ซ่อน Secret Key">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($moph->active === 'Y')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm rounded-circle p-2 edit-moph hover-scale" 
                                        style="width: 34px; height: 34px;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editMophModal"
                                        data-moph="{{ json_encode($moph) }}"
                                        title="แก้ไขการตั้งค่า">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">ไม่พบรายการตั้งค่า MOPH Notify</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: MOPH ALERT & 2FA -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-alert" role="tabpanel">
            <div class="setting-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-bullhorn text-info me-2"></i> บริการ MOPH Alert & ยืนยันตัวตน 2FA</h5>
                        <div class="text-muted small">ระบบส่งข้อความประชาสัมพันธ์และระบบยืนยันตัวตน 2FA เข้าแอปพลิเคชันหมอพร้อม</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;" class="text-center">ID</th>
                                <th>ชื่อบริการ</th>
                                <th>Client Key</th>
                                <th>Secret Key</th>
                                <th style="width: 120px;" class="text-center">สถานะ</th>
                                <th style="width: 120px;" class="text-center">2FA Login</th>
                                <th style="width: 130px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mophAlerts as $alert)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">#{{ $alert->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $alert->name }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="moph_alert_client_id_{{ $alert->id }}" class="font-monospace small text-muted">********</span>
                                        <button class="btn btn-link btn-xs p-0 ms-2 text-muted toggle-moph-alert-value" 
                                            data-id="{{ $alert->id }}" 
                                            data-type="client_id"
                                            data-value="{{ $alert->client_id }}"
                                            title="ดู/ซ่อน Client Key">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="moph_alert_secret_{{ $alert->id }}" class="font-monospace small text-muted">********</span>
                                        <button class="btn btn-link btn-xs p-0 ms-2 text-muted toggle-moph-alert-value" 
                                            data-id="{{ $alert->id }}" 
                                            data-type="secret"
                                            data-value="{{ $alert->secret }}"
                                            title="ดู/ซ่อน Secret Key">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($alert->active === 'Y')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($alert->enable_2fa === 'Y')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">
                                            <i class="fas fa-shield-alt me-1"></i>Enabled
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill small">Off</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                                        <button class="btn btn-outline-info btn-sm rounded-circle p-2 test-moph-alert hover-scale"
                                            style="width: 34px; height: 34px;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#testMophAlertModal"
                                            data-alert-id="{{ $alert->id }}"
                                            data-alert-name="{{ $alert->name }}"
                                            title="ทดสอบส่งแจ้งเตือน">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm rounded-circle p-2 edit-moph-alert hover-scale" 
                                            style="width: 34px; height: 34px;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editMophAlertModal"
                                            data-alert="{{ json_encode($alert) }}"
                                            title="แก้ไขการตั้งค่า">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">ไม่พบรายการตั้งค่า MOPH Alert</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: TELEGRAM NOTIFY -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-tele" role="tabpanel">
            <div class="setting-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fab fa-telegram text-primary me-2"></i> การตั้งค่า Telegram Bot Notify</h5>
                        <div class="text-muted small">ตั้งค่า Bot Token และ Chat ID สำหรับการส่งข้อความแจ้งเตือนผ่าน Telegram</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ชื่อรายการ</th>
                                <th>Key อ้างอิง</th>
                                <th>ค่าที่ตั้งไว้ (Value)</th>
                                <th style="width: 100px;" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($telegramNotifies as $tele)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $tele->name_th }}</div>
                                </td>
                                <td>
                                    <code class="text-primary font-monospace bg-primary-subtle px-2 py-0.5 rounded">{{ $tele->name }}</code>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="tele_val_display_{{ $loop->index }}" class="font-monospace small text-muted text-truncate" style="max-width: 320px;">
                                            {{ $tele->name == 'telegram_bot_token' ? '********' : ($tele->value ?: '(ว่าง)') }}
                                        </span>
                                        <button class="btn btn-link btn-xs p-0 ms-2 text-muted toggle-tele-value" 
                                            data-index="{{ $loop->index }}" 
                                            data-value="{{ $tele->value }}"
                                            data-masked="{{ $tele->name == 'telegram_bot_token' ? 'true' : 'false' }}"
                                            title="ดู/ซ่อนค่า">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm rounded-circle p-2 edit-tele hover-scale" 
                                        style="width: 34px; height: 34px;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editTelegramModal"
                                        data-tele="{{ json_encode($tele) }}"
                                        title="แก้ไขการตั้งค่า">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">ไม่พบรายการตั้งค่า Telegram</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 5: PROVIDER ID & HEALTH ID -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-provider" role="tabpanel">
            <div class="setting-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-id-card text-success me-2"></i> ระบบยืนยันตัวตน Provider ID & Health ID</h5>
                        <div class="text-muted small">ตั้งค่า API Keys สำหรับระบบ Single Sign-On ด้วย Provider ID ของกระทรวงสาธารณสุข</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ระบบ</th>
                                <th class="text-center">สถานะ</th>
                                <th>Health ID Client</th>
                                <th>Health ID Secret</th>
                                <th>Provider ID Client</th>
                                <th>Provider ID Secret</th>
                                <th class="text-center" style="width: 100px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($providerId)
                            <tr>
                                <td class="fw-bold text-dark">{{ $providerId->name ?: 'Provider ID / Health ID' }}</td>
                                <td class="text-center">
                                    @if($providerId->active === 'Y')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">
                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="provider_hid_client_{{ $providerId->id }}" class="font-monospace small text-muted">********</span>
                                        <button class="btn btn-link btn-xs p-0 ms-1 text-muted toggle-provider-value" data-id="{{ $providerId->id }}" data-type="hid_client" data-value="{{ $providerId->health_id_client_id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="provider_hid_secret_{{ $providerId->id }}" class="font-monospace small text-muted">********</span>
                                        <button class="btn btn-link btn-xs p-0 ms-1 text-muted toggle-provider-value" data-id="{{ $providerId->id }}" data-type="hid_secret" data-value="{{ $providerId->health_id_secret }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="provider_pid_client_{{ $providerId->id }}" class="font-monospace small text-muted">********</span>
                                        <button class="btn btn-link btn-xs p-0 ms-1 text-muted toggle-provider-value" data-id="{{ $providerId->id }}" data-type="pid_client" data-value="{{ $providerId->provider_id_client_id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span id="provider_pid_secret_{{ $providerId->id }}" class="font-monospace small text-muted">********</span>
                                        <button class="btn btn-link btn-xs p-0 ms-1 text-muted toggle-provider-value" data-id="{{ $providerId->id }}" data-type="pid_secret" data-value="{{ $providerId->provider_id_secret }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-success btn-sm rounded-circle p-2 edit-provider-id hover-scale" 
                                        style="width: 34px; height: 34px;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editProviderIdModal"
                                        data-provider="{{ json_encode($providerId) }}"
                                        title="แก้ไขการตั้งค่า">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @else
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">ยังไม่มีข้อมูลการตั้งค่า Provider ID</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 6: SCHEDULED TASKS -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-tasks" role="tabpanel">
            <div class="setting-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-clock text-primary me-2"></i> การตั้งค่าระบบตั้งเวลาอัตโนมัติ (Task Scheduler)</h5>
                        <div class="text-muted small">คำสั่งสำหรับนำไปใส่ใน Windows Task Scheduler หรือ Linux Crontab (รันทุก ๆ 1 นาที) เพื่อให้คิวการทำงานอัตโนมัติทั้งหมดทำงาน</div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">
                            <i class="fab fa-windows me-1"></i> คำสั่งหลัก Windows PowerShell
                        </span>
                        <button class="btn btn-link btn-xs text-primary p-0 text-decoration-none fw-bold" type="button" onclick="copyText('cmd_scheduler')">
                            <i class="fas fa-copy me-1"></i> คัดลอกคำสั่ง (Copy)
                        </button>
                    </div>
                    <div class="code-box shadow-xs" id="cmd_scheduler">-ExecutionPolicy Bypass -WindowStyle Hidden -Command "php {{ str_replace('/', '\\', base_path('artisan')) }} schedule:run"</div>
                </div>

                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-dark-subtle text-dark border px-2.5 py-1 rounded-pill">
                            <i class="fab fa-linux me-1"></i> คำสั่ง Linux Crontab (crontab -e)
                        </span>
                        <button class="btn btn-link btn-xs text-primary p-0 text-decoration-none fw-bold" type="button" onclick="copyText('cmd_crontab')">
                            <i class="fas fa-copy me-1"></i> คัดลอกคำสั่ง (Copy)
                        </button>
                    </div>
                    <div class="code-box shadow-xs" id="cmd_crontab">* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</div>
                </div>
            </div>
        </div>

    </div>

    <!-- System Environment Footer -->
    <div class="d-flex flex-wrap justify-content-between align-items-center text-muted small mt-4 pt-3 border-top px-1 gap-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span><i class="fab fa-laravel text-danger me-1"></i> Laravel <strong class="text-dark">{{ app()->version() }}</strong></span>
            <span><i class="fab fa-php text-primary me-1"></i> PHP <strong class="text-dark">{{ phpversion() }}</strong></span>
            <span><i class="fas fa-database text-success me-1"></i> DB Connection: <strong class="text-dark font-monospace">{{ config('database.default') }}</strong></span>
            <span>
                <i class="fas fa-server text-secondary me-1"></i> Environment: 
                @if(app()->isLocal() || config('app.env') === 'local')
                    <span class="badge bg-warning-subtle text-warning-emphasis font-monospace border border-warning-subtle px-2 py-1">local</span>
                @elseif(app()->environment('production'))
                    <span class="badge bg-success-subtle text-success font-monospace border border-success-subtle px-2 py-1">production</span>
                @else
                    <span class="badge bg-info-subtle text-info font-monospace border border-info-subtle px-2 py-1">{{ config('app.env') }}</span>
                @endif
            </span>
        </div>
        <div>
            <span class="badge bg-light text-muted border font-monospace px-2.5 py-1.5">System Version: {{ \App\Services\VersionService::getCurrentVersion() }}</span>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODALS -->
<!-- ========================================== -->

<!-- Edit Moph Notify Modal -->
<div class="modal fade" id="editMophModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold fs-6"><i class="fas fa-bell me-2"></i>แก้ไข MOPH Notify</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMophForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ID / รายการ</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0 xsmall">#<span id="moph_id_label"></span></span>
                            <input type="text" id="moph_name" class="form-control shadow-none bg-light" readonly disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Client ID</label>
                        <div class="input-group">
                            <input type="password" name="client_id" id="moph_client_id_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="moph_client_id_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Secret Key</label>
                        <div class="input-group">
                            <input type="password" name="secret" id="moph_secret_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="moph_secret_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-0 pt-2 border-top">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="active" id="moph_active_edit" value="1">
                            <label class="form-check-label fw-bold small ms-1" for="moph_active_edit">เปิดใช้งาน (Enable Notification)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Moph Alert Modal -->
<div class="modal fade" id="editMophAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold fs-6"><i class="fas fa-bullhorn me-2"></i>แก้ไข MOPH Alert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMophAlertForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ID / รายการ</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0 xsmall">#<span id="moph_alert_id_label"></span></span>
                            <input type="text" id="moph_alert_name" class="form-control shadow-none bg-light" readonly disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Client Key</label>
                        <div class="input-group">
                            <input type="password" name="client_id" id="moph_alert_client_id_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="moph_alert_client_id_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Secret Key</label>
                        <div class="input-group">
                            <input type="password" name="secret" id="moph_alert_secret_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="moph_alert_secret_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-2 pt-2 border-top">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="active" id="moph_alert_active_edit" value="1">
                            <label class="form-check-label fw-bold small ms-1" for="moph_alert_active_edit">เปิดใช้งาน (Enable Notification)</label>
                        </div>
                    </div>
                    <div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="enable_2fa" id="moph_alert_enable_2fa_edit" value="1">
                            <label class="form-check-label fw-bold small ms-1 text-danger" for="moph_alert_enable_2fa_edit">เปิดการใช้งาน 2FA ในการล็อคอินเข้าระบบ</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Moph Alert Modal -->
<div class="modal fade" id="testMophAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold fs-6"><i class="fas fa-paper-plane me-2"></i>ทดสอบส่ง MOPH Alert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="testMophAlertForm" method="POST" onsubmit="runMophAlertTest(event)">
                @csrf
                <input type="hidden" name="alert_id" id="test_alert_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">กำลังทดสอบผ่านบริการ:</label>
                        <div class="fw-bold fs-6 text-primary" id="test_alert_name_label"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">เลือกเจ้าหน้าที่โรงพยาบาล (ดึงจาก Backoffice)</label>
                        <input type="text" name="cid" id="test_alert_cid" class="form-control shadow-none" placeholder="พิมพ์ชื่อเพื่อค้นหา หรือระบุเลข CID 13 หลัก" list="staffDatalist" required>
                        <datalist id="staffDatalist">
                            @foreach($staffList as $staff)
                                <option value="{{ $staff->cid }}">{{ $staff->prefix }}{{ $staff->fname }} {{ $staff->lname }}</option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">หัวข้อแจ้งเตือน (Title)</label>
                        <input type="text" name="title" id="test_alert_title" class="form-control shadow-none" value="ทดสอบระบบประชาสัมพันธ์ รพ.หัวตะพาน" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ข้อความสั้น (Message Text)</label>
                        <input type="text" name="text" id="test_alert_text" class="form-control shadow-none" value="ทดสอบระบบส่งการแจ้งเตือนประชาสัมพันธ์ข่าวสาร" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ข้อความ HTML (Message HTML)</label>
                        <textarea name="html" id="test_alert_html" class="form-control shadow-none" rows="3" required><div><strong>สวัสดีครับ โรงพยาบาลหัวตะพานขอแจ้งทดสอบระบบประชาสัมพันธ์ผ่านหมอพร้อม</strong></div></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" id="btn_submit_test" class="btn btn-success rounded-pill px-4 shadow-sm">
                        <i class="fas fa-paper-plane me-1"></i> ส่งทดสอบ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Telegram Notify Modal -->
<div class="modal fade" id="editTelegramModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold fs-6"><i class="fab fa-telegram me-2"></i>แก้ไข Telegram Notify</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTelegramForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ชื่อรายการ (Thai)</label>
                        <input type="text" name="name_th" id="tele_name_th" class="form-control shadow-none">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Key (Reference Name)</label>
                        <input type="text" id="tele_name" class="form-control shadow-none bg-light" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small" id="tele_value_label">ค่าที่ระบุ</label>
                        <div class="input-group">
                            <input type="password" name="value" id="tele_value_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="tele_value_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Provider ID Modal -->
<div class="modal fade" id="editProviderIdModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold fs-6"><i class="fas fa-id-card me-2"></i>แก้ไข Provider ID / Health ID</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProviderIdForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ID / รายการ</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light text-muted border-end-0 xsmall">#<span id="provider_id_label"></span></span>
                            <input type="text" id="provider_name_display" class="form-control shadow-none bg-light" value="ระบบ Provider ID / Health ID" readonly disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">ชื่อระบบ / รายการ</label>
                        <input type="text" name="name" id="provider_name_edit" class="form-control shadow-none" placeholder="เช่น RiMS หรือ SmartData">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Health ID Client ID</label>
                        <div class="input-group">
                            <input type="password" name="health_id_client_id" id="provider_hid_client_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="provider_hid_client_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Health ID Client Secret</label>
                        <div class="input-group">
                            <input type="password" name="health_id_secret" id="provider_hid_secret_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="provider_hid_secret_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Provider ID Client ID</label>
                        <div class="input-group">
                            <input type="password" name="provider_id_client_id" id="provider_pid_client_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="provider_pid_client_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Provider ID Client Secret</label>
                        <div class="input-group">
                            <input type="password" name="provider_id_secret" id="provider_pid_secret_edit" class="form-control shadow-none">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="provider_pid_secret_edit">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-0 pt-2 border-top">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="active" id="provider_active_edit" value="1">
                            <label class="form-check-label fw-bold small ms-1" for="provider_active_edit">เปิดใช้งานปุ่ม Login ด้วย Provider ID</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm text-white">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tab Memory Handling (Preserve active tab across reload)
    document.addEventListener('DOMContentLoaded', function() {
        // Load active tab from hash or localStorage
        const activeTabId = localStorage.getItem('smartdata_active_setting_tab') || window.location.hash;
        if (activeTabId) {
            const triggerEl = document.querySelector(`button[data-bs-target="${activeTabId}"]`);
            if (triggerEl) {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        // Store active tab on change
        document.querySelectorAll('#settingTabs button[data-bs-toggle="pill"]').forEach(tabBtn => {
            tabBtn.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                localStorage.setItem('smartdata_active_setting_tab', target);
                window.location.hash = target;
            });
        });

        // Load version status
        loadVersionStatus(false);
    });

    // Version update check via VersionService
    window.loadVersionStatus = function(force = false) {
        const badge = document.getElementById('versionStatusBadge');
        if (!badge) return;
        badge.className = 'badge bg-light text-muted border py-2 px-3 font-monospace cursor-pointer shadow-xs';
        badge.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width: 0.75rem; height: 0.75rem;"></span> กำลังตรวจเวอร์ชัน...';

        fetch("{{ route('admin.version.check') }}" + (force ? '?force=1' : ''))
            .then(res => {
                if (!res.ok) throw new Error('Network response not ok');
                return res.json();
            })
            .then(data => {
                if (data && data.has_update) {
                    const verLabel = data.remote_version || data.remote_commit || 'มีอัปเดต';
                    badge.className = 'badge bg-warning text-dark border border-warning py-2 px-3 font-monospace shadow-sm cursor-pointer';
                    badge.innerHTML = `<i class="fas fa-exclamation-circle text-danger me-1"></i> มีอัปเดตใหม่ (${verLabel})`;
                    badge.title = 'พบคอมมิตใหม่บน GitHub คลิกปุ่ม "Update Version" เพื่ออัปเดต';
                } else if (data && data.status === 'dev_mode') {
                    badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle py-2 px-3 font-monospace cursor-pointer';
                    badge.innerHTML = `<i class="fas fa-code text-primary me-1"></i> โหมดพัฒนา (${data.current_version})`;
                    badge.title = 'เครื่องพัฒนา (Local Repo) คลิกเพื่อตรวจอีกครั้ง';
                } else if (data && data.status === 'offline') {
                    badge.className = 'badge bg-light text-secondary border py-2 px-3 font-monospace cursor-pointer';
                    badge.innerHTML = `<i class="fas fa-wifi text-muted me-1"></i> ${data.current_version}`;
                    badge.title = 'ไม่สามารถติดต่อ GitHub ได้ (คลิกเพื่อลองใหม่)';
                } else {
                    badge.className = 'badge bg-success-subtle text-success border border-success-subtle py-2 px-3 font-monospace cursor-pointer';
                    badge.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> เวอร์ชันล่าสุด (${data.current_version})`;
                    badge.title = 'ระบบเป็นเวอร์ชันล่าสุดแล้ว (คลิกเพื่อตรวจอีกครั้ง)';
                }
            })
            .catch(err => {
                badge.className = 'badge bg-light text-secondary border py-2 px-3 font-monospace cursor-pointer';
                badge.innerHTML = `<i class="fas fa-info-circle me-1"></i> {{ config('app.version', 'V.26-10-04') }}`;
                badge.title = 'คลิกเพื่อตรวจสอบเวอร์ชัน';
            });
    };

    // Automated One-Click Update Version Chain
    window.startAutoUpdate = function() {
        const isLocalEnv = {{ (app()->isLocal() || config('app.env') === 'local') ? 'true' : 'false' }};
        
        if (isLocalEnv) {
            Swal.fire({
                title: 'โหมดพัฒนา (Local Environment)',
                text: 'ระบบตรวจพบโหมดพัฒนา (APP_ENV=local) จึงจะข้ามขั้นตอน Git Pull เพื่อป้องกันโค้ดที่คุณกำลังพัฒนาสูญหาย และเริ่มขั้นตอน Upgrade Structure & ซิงค์ข้อมูลเริ่มต้นให้ทันที',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#4e73df',
                confirmButtonText: '<i class="fas fa-layer-group me-1"></i> ตกลง, ดำเนินการ!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    runUpgradeSteps(true, 'โหมดพัฒนา (Local Mode): ข้ามขั้นตอน Git Pull เพื่อความปลอดภัยของโค้ด');
                }
            });
            return;
        }

        Swal.fire({
            title: 'ยืนยันการอัปเดตเวอร์ชัน?',
            text: 'ระบบจะดำเนินการดึงโค้ดล่าสุดจาก Repository (Git Pull) และดำเนินการ Upgrade Structure พร้อมซิงค์ข้อมูลพื้นฐานอัตโนมัติ',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-cloud-download-alt me-1"></i> ตกลง, อัปเดตทันที!',
            cancelButtonText: 'ยกเลิก'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังดึงรหัสต้นฉบับล่าสุด (Git Pull)...',
                    text: 'กรุณารอสักครู่ ห้ามปิดหน้าต่างนี้',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const pullRes = await fetch("{{ route('admin.git_pull') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ details: 'Automated Update Version triggered' })
                    });

                    const pullData = await pullRes.json();
                    if (!pullRes.ok) {
                        throw new Error(pullData.error || 'เกิดข้อผิดพลาดในการดึงโค้ด');
                    }

                    // Proceed to upgrade steps
                    runUpgradeSteps(true, pullData.output || 'Git Pull สำเร็จ');
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'การดึงโค้ดล้มเหลว!',
                        text: err.message,
                        confirmButtonText: 'ตกลง'
                    });
                }
            }
        });
    };

    // Git Pull standalone form submit
    const gitForm = document.getElementById('git-pull-form');
    if (gitForm) {
        gitForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'ยืนยันการเคลียร์โค้ดและดึงล่าสุด?',
                text: "ระบบจะดำเนินการ git reset --hard และ git pull origin main เพื่อล้างการแก้ไขที่เครื่องและดึงโค้ดล่าสุดจาก Repository",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'ตกลง, อัปเดตเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => { if (result.isConfirmed) { Swal.showLoading(); this.submit(); } });
        });
    }

    // Upgrade Structure standalone form submit
    const upgradeForm = document.getElementById('upgrade-structure-form');
    if (upgradeForm) {
        upgradeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'ยืนยันการอัปเกรดโครงสร้างฐานข้อมูล?',
                text: "ระบบจะดำเนินการตรวจสอบโครงสร้างตาราง อัปเดตคอลัมน์จากไฟล์ schema และซิงค์ข้อมูลเริ่มต้นระบบ (*_seeds.json)",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4e73df',
                confirmButtonText: 'ตกลง, เริ่มอัปเกรด!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    runUpgradeSteps();
                }
            });
        });
    }

    // Upgrade Steps Runner
    async function runUpgradeSteps(isAuto = false, gitOutput = '') {
        Swal.fire({
            title: 'กำลังเตรียมการปรับปรุงระบบ...',
            text: 'กรุณารอสักครู่ กำลังเตรียมขั้นตอนการทำงาน...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const stepsResponse = await fetch(`{{ route('admin.upgrade_structure') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ action: 'get_steps' })
            });

            const stepsData = await stepsResponse.json();
            if (!stepsResponse.ok || !stepsData.success) {
                throw new Error(stepsData.message || 'ไม่สามารถดึงขั้นตอนการทำงานได้');
            }

            const steps = stepsData.steps;

            Swal.fire({
                title: 'กำลังดำเนินการปรับปรุงระบบ...',
                html: `
                    <div class="text-start mb-2 small text-muted" id="upgrade-step-name">เริ่มต้นการทำงาน...</div>
                    <div class="progress mb-3" style="height: 22px; border-radius: 10px;">
                        <div id="upgrade-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; font-size: 0.75rem; font-weight: bold;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <pre id="upgrade-log-output" class="text-start bg-dark text-light p-2.5 rounded-3 xsmall" style="max-height: 160px; overflow-y: auto; font-family: monospace; font-size: 0.72rem; white-space: pre-wrap; border: 1px solid #334155;"></pre>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                width: '620px',
                didOpen: async () => {
                    Swal.showLoading();
                    const stepNameEl = document.getElementById('upgrade-step-name');
                    const progressBarEl = document.getElementById('upgrade-progress-bar');
                    const logEl = document.getElementById('upgrade-log-output');
                    let changes = [];

                    if (gitOutput) {
                        logEl.innerText += `> Git Pull: ${gitOutput}\n\n`;
                    }

                    for (let i = 0; i < steps.length; i++) {
                        const step = steps[i];
                        const percent = Math.round((i / steps.length) * 100);

                        stepNameEl.innerText = `ขั้นตอนที่ ${i + 1}/${steps.length}: ${step.name}`;
                        progressBarEl.style.width = `${percent}%`;
                        progressBarEl.innerText = `${percent}%`;
                        progressBarEl.setAttribute('aria-valuenow', percent);

                        logEl.innerText += `> กำลังทำ: ${step.name}\n`;
                        logEl.scrollTop = logEl.scrollHeight;

                        try {
                            const response = await fetch(`{{ route('admin.upgrade_structure') }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ step: step.id })
                            });

                            const data = await response.json();

                            if (response.ok && data.success) {
                                logEl.innerText += `> สำเร็จ: ${data.message}\n\n`;
                                logEl.scrollTop = logEl.scrollHeight;

                                if (step.id !== 'finalize') {
                                    changes.push({
                                        stepId: step.id,
                                        stepName: step.name,
                                        detail: data.message
                                    });
                                }
                            } else {
                                throw new Error(data.message || 'เกิดข้อผิดพลาดในการตอบสนอง');
                            }
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'การอัปเกรดล้มเหลว!',
                                text: `เกิดข้อผิดพลาดในขั้นตอน: ${step.name} (${err.message})`,
                                confirmButtonText: 'ตกลง'
                            });
                            return;
                        }
                    }

                    progressBarEl.style.width = '100%';
                    progressBarEl.innerText = '100%';
                    progressBarEl.className = 'progress-bar bg-success';
                    progressBarEl.setAttribute('aria-valuenow', 100);
                    stepNameEl.innerText = 'ปรับปรุงโครงสร้างเสร็จสมบูรณ์!';
                    logEl.innerText += `> ปรับปรุงฐานข้อมูลและซิงค์ข้อมูลพื้นฐานเสร็จสิ้นเรียบร้อย!\n`;
                    logEl.scrollTop = logEl.scrollHeight;

                    let step1Changes = [];
                    let step2Changes = [];
                    changes.forEach(c => {
                        if (c.stepId === 'run_migrations' || c.stepId.startsWith('sync_table_')) {
                            if (c.detail && c.detail !== 'Schema is up-to-date.' && !c.detail.includes('Nothing to migrate')) {
                                step1Changes.push(c);
                            }
                        } else if (c.stepId === 'sync_seed_data') {
                            if (c.detail && c.detail !== 'ข้อมูลตั้งต้นระบบเป็นปัจจุบันแล้ว') {
                                step2Changes.push(c);
                            }
                        }
                    });

                    let changesHtml = '<div class="text-start mt-2">';
                    changesHtml += '<div class="mb-3"><strong><i class="fas fa-database text-primary me-1"></i> โครงสร้างฐานข้อมูล (Structure)</strong>';
                    if (step1Changes.length > 0) {
                        changesHtml += '<ul class="small mt-1 text-primary mb-0" style="list-style-type: square; padding-left: 20px; font-family: monospace;">';
                        step1Changes.forEach(c => {
                            const cleanedName = c.stepName.replace('ตรวจสอบและอัปเดตตาราง ', '').replace('...', '');
                            changesHtml += `<li class="mb-1"><span class="badge bg-secondary me-1">${cleanedName}</span> ${c.detail}</li>`;
                        });
                        changesHtml += '</ul>';
                    } else {
                        changesHtml += '<div class="text-muted small ms-3 mt-1"><i class="fas fa-check-circle text-success me-1"></i>โครงสร้างฐานข้อมูลเป็นปัจจุบันอยู่แล้ว</div>';
                    }
                    changesHtml += '</div>';

                    changesHtml += '<div><strong><i class="fas fa-seedling text-success me-1"></i> ข้อมูลตั้งต้นระบบ (Master Seeds)</strong>';
                    if (step2Changes.length > 0) {
                        changesHtml += '<ul class="small mt-1 text-success mb-0" style="list-style-type: square; padding-left: 20px;">';
                        step2Changes.forEach(c => {
                            changesHtml += `<li class="mb-1">${c.detail}</li>`;
                        });
                        changesHtml += '</ul>';
                    } else {
                        changesHtml += '<div class="text-muted small ms-3 mt-1"><i class="fas fa-check-circle text-success me-1"></i>ข้อมูลตั้งต้นระบบเป็นปัจจุบันแล้ว</div>';
                    }
                    changesHtml += '</div>';
                    changesHtml += '</div>';

                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'อัปเกรดระบบเสร็จเรียบร้อย!',
                            html: changesHtml,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#198754',
                            width: '560px'
                        }).then(() => {
                            window.location.reload();
                        });
                    }, 800);
                }
            });
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'ไม่สามารถเริ่มต้นการอัปเกรดได้!',
                text: err.message,
                confirmButtonText: 'ตกลง'
            });
        }
    }

    // Edit Moph Modal Population
    document.querySelectorAll('.edit-moph').forEach(button => {
        button.addEventListener('click', function() {
            const moph = JSON.parse(this.dataset.moph);
            const form = document.getElementById('editMophForm');
            form.action = `{{ url('/') }}/admin/moph-notify/${moph.id}`;
            document.getElementById('moph_id_label').innerText = moph.id;
            document.getElementById('moph_name').value = moph.name;
            document.getElementById('moph_client_id_edit').value = moph.client_id;
            document.getElementById('moph_secret_edit').value = moph.secret;
            document.getElementById('moph_active_edit').checked = moph.active === 'Y';
        });
    });

    // Edit Moph Alert Modal Population
    document.querySelectorAll('.edit-moph-alert').forEach(button => {
        button.addEventListener('click', function() {
            const alert = JSON.parse(this.dataset.alert);
            const form = document.getElementById('editMophAlertForm');
            form.action = `{{ url('/') }}/admin/moph-alert/${alert.id}`;
            document.getElementById('moph_alert_id_label').innerText = alert.id;
            document.getElementById('moph_alert_name').value = alert.name;
            document.getElementById('moph_alert_client_id_edit').value = alert.client_id;
            document.getElementById('moph_alert_secret_edit').value = alert.secret;
            document.getElementById('moph_alert_active_edit').checked = alert.active === 'Y';
            document.getElementById('moph_alert_enable_2fa_edit').checked = alert.enable_2fa === 'Y';
        });
    });

    // Edit Telegram Modal Population
    document.querySelectorAll('.edit-tele').forEach(button => {
        button.addEventListener('click', function() {
            const tele = JSON.parse(this.dataset.tele);
            const form = document.getElementById('editTelegramForm');
            form.action = `{{ url('/') }}/admin/telegram-notify/${tele.name}`;
            document.getElementById('tele_name_th').value = tele.name_th;
            document.getElementById('tele_name').value = tele.name;
            document.getElementById('tele_value_edit').value = tele.value || '';
        });
    });

    // Edit Provider ID Modal Population
    document.querySelectorAll('.edit-provider-id').forEach(button => {
        button.addEventListener('click', function() {
            const provider = JSON.parse(this.dataset.provider);
            const form = document.getElementById('editProviderIdForm');
            form.action = `{{ url('/') }}/admin/provider-id/${provider.id}`;
            document.getElementById('provider_id_label').innerText = provider.id;
            document.getElementById('provider_name_edit').value = provider.name || '';
            document.getElementById('provider_hid_client_edit').value = provider.health_id_client_id || '';
            document.getElementById('provider_hid_secret_edit').value = provider.health_id_secret || '';
            document.getElementById('provider_pid_client_edit').value = provider.provider_id_client_id || '';
            document.getElementById('provider_pid_secret_edit').value = provider.provider_id_secret || '';
            document.getElementById('provider_active_edit').checked = provider.active === 'Y';
        });
    });

    // Toggle Visibility Handlers
    document.querySelectorAll('.toggle-moph-value').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            const realValue = this.dataset.value || '(ว่าง)';
            const displayEl = document.getElementById(`moph_${type}_${id}`);
            const icon = this.querySelector('i');

            if (icon.classList.contains('fa-eye')) {
                displayEl.innerText = realValue;
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                displayEl.innerText = '********';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    document.querySelectorAll('.toggle-moph-alert-value').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            const realValue = this.dataset.value || '(ว่าง)';
            const displayEl = document.getElementById(`moph_alert_${type}_${id}`);
            const icon = this.querySelector('i');

            if (icon.classList.contains('fa-eye')) {
                displayEl.innerText = realValue;
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                displayEl.innerText = '********';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    document.querySelectorAll('.toggle-tele-value').forEach(button => {
        button.addEventListener('click', function() {
            const index = this.dataset.index;
            const realValue = this.dataset.value || '(ว่าง)';
            const isMasked = this.dataset.masked === 'true';
            const displayEl = document.getElementById(`tele_val_display_${index}`);
            const icon = this.querySelector('i');

            if (icon.classList.contains('fa-eye')) {
                displayEl.innerText = realValue;
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                displayEl.innerText = isMasked ? '********' : realValue;
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    document.querySelectorAll('.toggle-provider-value').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            const value = this.dataset.value || '(ว่าง)';
            const span = document.getElementById(`provider_${type}_${id}`);
            const icon = this.querySelector('i');
            
            if (span.innerText === '********') {
                span.innerText = value;
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                span.innerText = '********';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password Visibility in Modals
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Test MOPH Alert
    document.querySelectorAll('.test-moph-alert').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.alertId;
            const name = this.dataset.alertName;
            document.getElementById('test_alert_id').value = id;
            document.getElementById('test_alert_name_label').innerText = name;
            document.getElementById('test_alert_cid').value = '';
        });
    });

    function runMophAlertTest(e) {
        e.preventDefault();
        const btn = document.getElementById('btn_submit_test');
        const origContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังส่งข้อความ...';
        
        const data = {
            _token: document.querySelector('input[name="_token"]').value,
            alert_id: document.getElementById('test_alert_id').value,
            cid: document.getElementById('test_alert_cid').value,
            title: document.getElementById('test_alert_title').value,
            text: document.getElementById('test_alert_text').value,
            html: document.getElementById('test_alert_html').value
        };

        fetch('{{ route("admin.moph_alert.test") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = origContent;
            
            if (res.success) {
                const modalEl = document.getElementById('testMophAlertModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                
                Swal.fire({
                    icon: 'success',
                    title: 'ส่งทดสอบสำเร็จ!',
                    text: res.message,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#198754'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'ส่งทดสอบล้มเหลว!',
                    text: res.message,
                    confirmButtonText: 'ตกลง'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origContent;
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้: ' + err.message,
                confirmButtonText: 'ตกลง'
            });
        });
    }

    // Copy to Clipboard Helper
    function copyText(id) {
        const element = document.getElementById(id);
        const text = element.innerText;
        
        const tempInput = document.createElement("textarea");
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        
        Swal.fire({
            icon: 'success',
            title: 'คัดลอกคำสั่งสำเร็จ!',
            text: 'คุณสามารถนำไปวางใน Task Scheduler หรือ Crontab ได้ทันที',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
</script>
@endpush
@endsection
