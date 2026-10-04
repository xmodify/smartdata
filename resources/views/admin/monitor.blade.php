@extends('layouts.admin')

@section('title', 'System Monitor & Operations - SmartData')

@push('styles')
<style>
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
        background: #198754;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.25);
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
    .code-box {
        background: #1e293b;
        color: #38bdf8;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        font-size: 0.8rem;
    }
    .log-terminal {
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        font-family: 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        font-size: 0.78rem;
        line-height: 1.6;
        max-height: 380px;
        overflow-y: auto;
        white-space: pre-wrap;
        border: 1px solid #334155;
    }
    .hover-scale {
        transition: transform 0.15s ease-in-out;
    }
    .hover-scale:hover {
        transform: scale(1.02);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Top Header Banner -->
    <div class="page-header-box p-3 p-md-4 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs hover-scale" style="width: 38px; height: 38px;" title="ย้อนกลับไปแดชบอร์ด">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2 flex-wrap">
                        <span class="icon-box bg-success-subtle text-success" style="width: 36px; height: 36px; font-size: 1.1rem;">
                            <i class="fas fa-desktop-alt"></i>
                        </span>
                        <span>System Monitor & Operations</span>
                        @if(app()->isLocal() || config('app.env') === 'local')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle py-1.5 px-2.5 rounded-pill small fw-bold" style="font-size: 0.78rem;">
                                <i class="fas fa-laptop-code text-warning me-1"></i> Local (Dev)
                            </span>
                        @elseif(app()->environment('production'))
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-1.5 px-2.5 rounded-pill small fw-bold" style="font-size: 0.78rem;">
                                <i class="fas fa-shield-alt text-success me-1"></i> Production
                            </span>
                        @else
                            <span class="badge bg-info-subtle text-info font-monospace border border-info-subtle py-1.5 px-2.5 rounded-pill small fw-bold" style="font-size: 0.78rem;">
                                {{ config('app.env') }}
                            </span>
                        @endif
                    </h4>
                    <div class="text-muted small">ตรวจสอบสุขภาพระบบ งานตั้งเวลาอัตโนมัติ (Scheduler) และการสำรองกู้คืนฐานข้อมูล</div>
                </div>
            </div>

            <!-- Server Time & Refresh Button -->
            <div class="d-flex align-items-center gap-2 flex-wrap ms-lg-auto">
                <span class="badge bg-light text-muted border py-2 px-3 font-monospace shadow-xs" title="เวลาเซิร์ฟเวอร์ (Asia/Bangkok)">
                    <i class="fas fa-clock text-primary me-1.5"></i> {{ $serverTime }}
                </span>

                <a href="{{ route('admin.monitor.index') }}" class="btn btn-outline-success btn-sm px-3 py-2 rounded-pill shadow-xs hover-scale fw-bold d-inline-flex align-items-center gap-1.5">
                    <i class="fas fa-sync-alt"></i>
                    <span>รีเฟรชข้อมูล</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Health Status Cards (3 Cards) -->
    <div class="row g-3 mb-4">
        <!-- Laravel Scheduler Status -->
        <div class="col-md-4">
            <div class="setting-card p-3 p-md-4 h-100 position-relative">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="icon-box bg-primary-subtle text-primary">
                        <i class="fas fa-clock"></i>
                    </div>
                    @if($schedulerStatus === 'online')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-check-circle me-1"></i> ปกติ (Online)
                        </span>
                    @elseif($schedulerStatus === 'delayed')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-exclamation-triangle me-1"></i> ดีเลย์ (Delayed)
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-times-circle me-1"></i> ไม่ทำงาน (Offline)
                        </span>
                    @endif
                </div>
                <h6 class="fw-bold text-dark mb-1">Laravel Scheduler (Cron)</h6>
                <p class="text-muted small mb-3">รันทุก 1 นาทีเพื่อประมวลผลงานอัตโนมัติทั้งหมด</p>
                <div class="border-top pt-2.5">
                    <div class="d-flex justify-content-between text-muted small mb-1">
                        <span>รันล่าสุด:</span>
                        <strong class="text-dark font-monospace">{{ $schedulerLastRun ?? 'ยังไม่พบประวัติ' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>สถานะ:</span>
                        <span class="fw-bold {{ $schedulerStatus === 'online' ? 'text-success' : ($schedulerStatus === 'delayed' ? 'text-warning' : 'text-danger') }}">
                            {{ $schedulerStatus === 'online' ? 'ทำงานปกติ' : ($schedulerStatus === 'delayed' ? 'ทำงานล่าช้า (> 2 นาที)' : 'หยุดทำงาน') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Local DB Status -->
        <div class="col-md-4">
            <div class="setting-card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="icon-box bg-success-subtle text-success">
                        <i class="fas fa-database"></i>
                    </div>
                    @if($localDbStatus === 'online')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-check-circle me-1"></i> ปกติ (Online)
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-times-circle me-1"></i> ตัดการเชื่อมต่อ
                        </span>
                    @endif
                </div>
                <h6 class="fw-bold text-dark mb-1">Local Database (MySQL)</h6>
                <p class="text-muted small mb-3">ฐานข้อมูลระบบ SmartData (ตารางหลัก & ข้อมูลตั้งค่า)</p>
                <div class="border-top pt-2.5">
                    @if($localDbStatus === 'online')
                        <div class="text-success small d-flex align-items-center gap-1.5">
                            <i class="fas fa-check-circle"></i> เชื่อมต่อฐานข้อมูลหลักเรียบร้อย
                        </div>
                    @else
                        <div class="text-danger small text-truncate" title="{{ $localDbError }}">
                            <i class="fas fa-exclamation-triangle"></i> {{ $localDbError }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- HOSxP DB Status -->
        <div class="col-md-4">
            <div class="setting-card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="icon-box bg-info-subtle text-info">
                        <i class="fas fa-server"></i>
                    </div>
                    @if($hosxpDbStatus === 'online')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-check-circle me-1"></i> ปกติ (Online)
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-bold">
                            <i class="fas fa-times-circle me-1"></i> ตัดการเชื่อมต่อ
                        </span>
                    @endif
                </div>
                <h6 class="fw-bold text-dark mb-1">HOSxP Database</h6>
                <p class="text-muted small mb-3">ฐานข้อมูลโรงพยาบาลสำหรับดึงสถิติบริการต่างๆ</p>
                <div class="border-top pt-2.5">
                    @if($hosxpDbStatus === 'online')
                        <div class="text-success small d-flex align-items-center gap-1.5">
                            <i class="fas fa-check-circle"></i> เชื่อมต่อฐานข้อมูล HOSxP เรียบร้อย
                        </div>
                    @else
                        <div class="text-danger small text-truncate" title="{{ $hosxpDbError }}">
                            <i class="fas fa-exclamation-triangle"></i> {{ $hosxpDbError }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills nav-pills-custom mb-4 gap-2 flex-wrap" id="monitorTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-backup-btn" data-bs-toggle="pill" data-bs-target="#tab-backup" type="button" role="tab">
                <i class="fas fa-save text-success"></i> สำรอง & กู้คืนฐานข้อมูล
                <span class="badge bg-success-subtle text-success rounded-pill ms-1">{{ count($backupFiles) }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-tasks-btn" data-bs-toggle="pill" data-bs-target="#tab-tasks" type="button" role="tab">
                <i class="fas fa-bolt text-warning"></i> ทดสอบรันงานส่งข้อมูล (Manual Run)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-logs-btn" data-bs-toggle="pill" data-bs-target="#tab-logs" type="button" role="tab">
                <i class="fas fa-terminal text-info"></i> บันทึกการทำงานระบบ (Logs)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-scheduler-btn" data-bs-toggle="pill" data-bs-target="#tab-scheduler" type="button" role="tab">
                <i class="fas fa-cog text-secondary"></i> การตั้งค่า Scheduler
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="monitorTabsContent">

        <!-- ========================================== -->
        <!-- TAB 1: DATABASE BACKUP & RESTORE -->
        <!-- ========================================== -->
        <div class="tab-pane fade show active" id="tab-backup" role="tabpanel">
            <div class="setting-card p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="fas fa-database text-success"></i>
                            <span>รายการไฟล์สำรองฐานข้อมูล (Database Backups)</span>
                        </h5>
                        <div class="text-muted small">ระบบบีบอัดไฟล์ความเร็วสูง (<code class="text-success">.sql.gz</code>) พร้อมนโยบายเก็บไฟล์ย้อนหลัง 7 วันอัตโนมัติ</div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm px-3.5 py-2 rounded-pill shadow-sm hover-scale fw-bold d-inline-flex align-items-center gap-1.5" onclick="runManualBackup()">
                        <i class="fas fa-plus-circle"></i>
                        <span>สำรองข้อมูลทันที (Backup Now)</span>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">#</th>
                                <th>ชื่อไฟล์สำรอง (.sql.gz)</th>
                                <th>ขนาดไฟล์</th>
                                <th>วันที่สำรองข้อมูล</th>
                                <th>อายุไฟล์</th>
                                <th style="width: 220px;" class="text-center">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="backupListTbody">
                            @forelse($backupFiles as $index => $bf)
                            <tr>
                                <td class="text-center font-monospace fw-bold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="p-2 bg-success-subtle text-success rounded-3 small">
                                            <i class="fas fa-file-archive"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark font-monospace">{{ $bf['filename'] }}</div>
                                            <div class="text-muted xsmall">Path: storage/app/backups</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1.5">{{ $bf['size_formatted'] }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $bf['created_at'] }}</div>
                                    <div class="text-muted xsmall">{{ $bf['diff_human'] }}</div>
                                </td>
                                <td>
                                    @if($bf['age_days'] == 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">วันนี้</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill">{{ $bf['age_days'] }} วันที่แล้ว</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm shadow-xs" role="group">
                                        <a href="{{ route('admin.monitor.backup.download', ['filename' => $bf['filename']]) }}" class="btn btn-outline-success" title="ดาวน์โหลดไฟล์สำรอง">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-primary" onclick="openRestoreModal('{{ $bf['filename'] }}', '{{ $bf['created_at'] }}', '{{ $bf['size_formatted'] }}')" title="กู้คืนฐานข้อมูลจากไฟล์นี้">
                                            <i class="fas fa-undo-alt me-1"></i> กู้คืน
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteBackupFile('{{ $bf['filename'] }}')" title="ลบไฟล์สำรองนี้">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                    <div class="fw-bold">ยังไม่พบไฟล์สำรองข้อมูลในระบบ</div>
                                    <div class="small text-muted mt-1">คลิกปุ่ม "สำรองข้อมูลทันที" ด้านบนเพื่อสร้างไฟล์สำรองแรก</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: MANUAL RUN & TASKS -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-tasks" role="tabpanel">
            <div class="setting-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-bolt text-warning me-2"></i>ทดสอบรันสคริปต์ส่งงาน (Manual Run)</h5>
                        <div class="text-muted small">หากระบบอัตโนมัติไม่ทำงาน หรือต้องการส่งข้อมูลซ้ำ สามารถกดปุ่มด้านล่างเพื่อส่งข้อมูลได้ทันที</div>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Task 1: Night Shift -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">ส่งสถิติเวรดึก (00.00 - 08.00 น.)</div>
                                <div class="text-muted small"><i class="fas fa-clock text-secondary me-1"></i>รันอัตโนมัติ: 08:00 น.</div>
                            </div>
                            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-xs hover-scale fw-bold btn-run-task" data-task="service_night">
                                <i class="fas fa-play me-1"></i> รัน
                            </button>
                        </div>
                    </div>

                    <!-- Task 2: Morning Shift -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">ส่งสถิติเวรเช้า (08.00 - 16.00 น.)</div>
                                <div class="text-muted small"><i class="fas fa-clock text-secondary me-1"></i>รันอัตโนมัติ: 16:00 น.</div>
                            </div>
                            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-xs hover-scale fw-bold btn-run-task" data-task="service_morning">
                                <i class="fas fa-play me-1"></i> รัน
                            </button>
                        </div>
                    </div>

                    <!-- Task 3: Afternoon Shift -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">ส่งสถิติเวรบ่าย (16.00 - 24.00 น.)</div>
                                <div class="text-muted small"><i class="fas fa-clock text-secondary me-1"></i>รันอัตโนมัติ: 00:01 น.</div>
                            </div>
                            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-xs hover-scale fw-bold btn-run-task" data-task="service_afternoon">
                                <i class="fas fa-play me-1"></i> รัน
                            </button>
                        </div>
                    </div>

                    <!-- Task 4: Replication Check -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">ตรวจสอบ MySQL Replication</div>
                                <div class="text-muted small"><i class="fas fa-clock text-secondary me-1"></i>รันอัตโนมัติ: ทุกๆ 10 นาที</div>
                            </div>
                            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-xs hover-scale fw-bold btn-run-task" data-task="replication">
                                <i class="fas fa-play me-1"></i> รัน
                            </button>
                        </div>
                    </div>

                    <!-- Task 5: Backup HOSxP Check -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">ตรวจสอบ Backup HOSxP</div>
                                <div class="text-muted small"><i class="fas fa-clock text-secondary me-1"></i>รันอัตโนมัติ: ทุกๆ 10 นาที</div>
                            </div>
                            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-xs hover-scale fw-bold btn-run-task" data-task="backup_hosxp">
                                <i class="fas fa-play me-1"></i> รัน
                            </button>
                        </div>
                    </div>

                    <!-- Task 6: Audit EMR -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">ตรวจสอบสถิติ Audit EMR</div>
                                <div class="text-muted small"><i class="fas fa-clock text-secondary me-1"></i>รันอัตโนมัติ: ตามรอบที่ตั้งไว้</div>
                            </div>
                            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-xs hover-scale fw-bold btn-run-task" data-task="audit_emr">
                                <i class="fas fa-play me-1"></i> รัน
                            </button>
                        </div>
                    </div>

                    <!-- Task 7: Database Backup -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark">สำรองฐานข้อมูล SmartData (.sql.gz)</div>
                                <div class="text-muted small"><i class="fas fa-clock text-secondary me-1"></i>รันอัตโนมัติ: 02:00 น. ทุกวัน</div>
                            </div>
                            <button class="btn btn-success btn-sm rounded-pill px-3 shadow-xs hover-scale fw-bold btn-run-task" data-task="db_backup">
                                <i class="fas fa-play me-1"></i> รัน
                            </button>
                        </div>
                    </div>

                    <!-- Task 8: Heartbeat Trigger -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-dark"><i class="fas fa-heartbeat text-danger me-1"></i> ทดสอบบันทึกสถานะ Heartbeat</div>
                                <div class="text-muted small">อัปเดตเวลา Scheduler เพื่อทดสอบสถานะ Online</div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs hover-scale fw-bold btn-run-task" data-task="test_heartbeat">
                                <i class="fas fa-sync-alt me-1"></i> อัปเดต
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: SYSTEM LOGS -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-logs" role="tabpanel">
            <div class="row g-4">
                <!-- Laravel Application Log -->
                <div class="col-lg-6">
                    <div class="setting-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><i class="fab fa-laravel text-danger me-2"></i>Laravel Log (laravel.log)</h6>
                                <div class="text-muted xsmall">60 บรรทัดล่าสุด</div>
                            </div>
                            <div class="d-flex gap-1.5">
                                <button class="btn btn-outline-secondary btn-xs rounded-pill px-2.5" onclick="copyText('laravel_log_content')" title="คัดลอกทั้งหมด">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-xs rounded-pill px-2.5" onclick="clearLogFile('laravel')" title="ล้างประวัติ Log">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="log-terminal mt-auto" id="laravel_log_content">@forelse($logLines as $line){{ $line . "\n" }}@emptyยังไม่มีประวัติ Log ในขณะนี้@endforelse</div>
                    </div>
                </div>

                <!-- Backup & Scheduler Log -->
                <div class="col-lg-6">
                    <div class="setting-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-save text-success me-2"></i>Backup Log (backup_schedule.log)</h6>
                                <div class="text-muted xsmall">ประวัติการสำรองและกู้คืนฐานข้อมูล</div>
                            </div>
                            <div class="d-flex gap-1.5">
                                <button class="btn btn-outline-secondary btn-xs rounded-pill px-2.5" onclick="copyText('backup_log_content')" title="คัดลอกทั้งหมด">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-xs rounded-pill px-2.5" onclick="clearLogFile('backup')" title="ล้างประวัติ Log">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="log-terminal mt-auto" id="backup_log_content">@forelse($backupLogLines as $bLine){{ $bLine . "\n" }}@emptyยังไม่มีประวัติการสำรองข้อมูลในขณะนี้@endforelse</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: SCHEDULER & SERVER GUIDE -->
        <!-- ========================================== -->
        <div class="tab-pane fade" id="tab-scheduler" role="tabpanel">
            <div class="setting-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><i class="fas fa-clock text-primary me-2"></i> การตั้งค่าระบบตั้งเวลาอัตโนมัติ (Scheduler Setup)</h5>
                        <div class="text-muted small">คำสั่งสำหรับนำไปใส่ใน Windows Task Scheduler หรือ Linux Crontab (รันทุก ๆ 1 นาที)</div>
                    </div>
                </div>

                <!-- Windows Command -->
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

                <!-- Linux Command -->
                <div class="mb-4">
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

                <!-- Scheduled Jobs Table Summary -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="fas fa-list-ol text-success me-2"></i>ตารางเวลาทำงานของงานอัตโนมัติทั้งหมด (Registered Scheduled Jobs)</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle small mb-0 bg-white">
                            <thead class="table-light">
                                <tr>
                                    <th>ชื่องาน (Task Name)</th>
                                    <th>รอบเวลาทำงาน (Schedule Interval)</th>
                                    <th>คำสั่ง / ฟังก์ชัน</th>
                                    <th style="width: 100px;" class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-moon text-primary me-1.5"></i> ส่งสถิติเวรดึก (00.00-08.00 น.)</td>
                                    <td><span class="badge bg-light text-dark border font-monospace">ทุกวัน 08:00 น.</span></td>
                                    <td><code>ServiceController@service_night</code></td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success">เปิดใช้งาน</span></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-sun text-warning me-1.5"></i> ส่งสถิติเวรเช้า (08.00-16.00 น.)</td>
                                    <td><span class="badge bg-light text-dark border font-monospace">ทุกวัน 16:00 น.</span></td>
                                    <td><code>ServiceController@service_morning</code></td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success">เปิดใช้งาน</span></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-cloud-sun text-info me-1.5"></i> ส่งสถิติเวรบ่าย (16.00-24.00 น.)</td>
                                    <td><span class="badge bg-light text-dark border font-monospace">ทุกวัน 00:01 น.</span></td>
                                    <td><code>ServiceController@service_afternoon</code></td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success">เปิดใช้งาน</span></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-sync-alt text-secondary me-1.5"></i> ตรวจสอบ MySQL Replication</td>
                                    <td><span class="badge bg-light text-dark border font-monospace">ทุกๆ 10 นาที</span></td>
                                    <td><code>ReplicationController@check</code></td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success">เปิดใช้งาน</span></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-hdd text-secondary me-1.5"></i> ตรวจสอบ Backup HOSxP</td>
                                    <td><span class="badge bg-light text-dark border font-monospace">ทุกๆ 10 นาที</span></td>
                                    <td><code>BackupController@check</code></td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success">เปิดใช้งาน</span></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-file-medical text-primary me-1.5"></i> ตรวจสอบสถิติ Audit EMR</td>
                                    <td><span class="badge bg-light text-dark border font-monospace">ทุกวัน 07:00 & 19:00 น.</span></td>
                                    <td><code>AuditEmrController@check</code></td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success">เปิดใช้งาน</span></td>
                                </tr>
                                <tr class="table-success-subtle">
                                    <td class="fw-bold"><i class="fas fa-save text-success me-1.5"></i> สำรองฐานข้อมูล SmartData (.sql.gz)</td>
                                    <td><span class="badge bg-success text-white font-monospace">ทุกวัน 02:00 น.</span></td>
                                    <td><code>php artisan db:backup</code></td>
                                    <td class="text-center"><span class="badge bg-success">เปิดใช้งาน</span></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-heartbeat text-danger me-1.5"></i> Heartbeat ตรวจสอบความพร้อม Scheduler</td>
                                    <td><span class="badge bg-light text-dark border font-monospace">ทุกๆ 1 นาที</span></td>
                                    <td><code>Cache Heartbeat Record</code></td>
                                    <td class="text-center"><span class="badge bg-success-subtle text-success">เปิดใช้งาน</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Server Details Card -->
                <div class="p-3 bg-light rounded-3 border">
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <div class="text-muted xsmall mb-1">ระบบปฏิบัติการ (OS)</div>
                            <div class="fw-bold text-dark font-monospace">{{ $osName }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted xsmall mb-1">PHP Version</div>
                            <div class="fw-bold text-dark font-monospace">{{ $phpVersion }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted xsmall mb-1">Laravel Version</div>
                            <div class="fw-bold text-danger font-monospace">{{ app()->version() }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted xsmall mb-1">Timezone</div>
                            <div class="fw-bold text-success font-monospace">{{ config('app.timezone', 'Asia/Bangkok') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer Summary -->
    <div class="d-flex flex-wrap justify-content-between align-items-center text-muted small mt-4 pt-3 border-top px-1 gap-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <span><i class="fab fa-laravel text-danger me-1"></i> Laravel <strong class="text-dark">{{ app()->version() }}</strong></span>
            <span><i class="fab fa-php text-primary me-1"></i> PHP <strong class="text-dark">{{ phpversion() }}</strong></span>
            <span><i class="fas fa-database text-success me-1"></i> DB: <strong class="text-dark font-monospace">{{ config('database.default') }}</strong></span>
            <span><i class="fas fa-save text-success me-1"></i> Total Backups: <strong class="text-dark font-monospace">{{ count($backupFiles) }} files</strong></span>
        </div>
        <div>
            <span class="badge bg-light text-muted border font-monospace px-2.5 py-1.5">System Version: {{ \App\Services\VersionService::getCurrentVersion() }}</span>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODALS -->
<!-- ========================================== -->

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreBackupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold fs-6"><i class="fas fa-exclamation-triangle me-2"></i>ยืนยันการกู้คืนฐานข้อมูล (Database Restore)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="restoreBackupForm" onsubmit="submitRestoreBackup(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="restore_filename" value="">
                    
                    <div class="alert alert-danger border-0 d-flex gap-2 p-3 mb-3 rounded-3 small">
                        <i class="fas fa-radiation-alt fa-2x text-danger mt-1"></i>
                        <div>
                            <strong>คำเตือนสำคัญมาก!</strong>
                            <div class="mt-1">การกู้คืนข้อมูลจะทำการ **เขียนทับ (Overwrite)** ฐานข้อมูลปัจจุบันด้วยข้อมูลจากไฟล์สำรองนี้ ข้อมูลที่ถูกสร้างหลังจากวันเวลาที่สำรองข้อมูลจะสูญหาย</div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">ไฟล์สำรอง:</span>
                            <strong class="font-monospace text-dark" id="restore_display_filename">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="text-muted">วันที่สำรอง:</span>
                            <strong class="text-dark" id="restore_display_date">-</strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted">ขนาดไฟล์:</span>
                            <strong class="text-success font-monospace" id="restore_display_size">-</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-danger" for="restore_security_code">
                            กรุณากรอกรหัสความปลอดภัยเพื่อยืนยัน:
                        </label>
                        <input type="text" id="restore_security_code" class="form-control font-monospace fw-bold text-center border-danger shadow-none" placeholder="กรอกรหัสความปลอดภัย" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold" id="btn_submit_restore">
                        <i class="fas fa-undo-alt me-1"></i> ยืนยันการกู้คืนข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Task Output Modal -->
<div class="modal fade" id="taskOutputModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <h5 class="modal-title fw-bold fs-6"><i class="fas fa-terminal me-2"></i>ผลลัพธ์การรันงาน (Task Output)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-dark">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-success-subtle text-success border font-monospace px-2 py-1" id="task_output_status">SUCCESS</span>
                    <button class="btn btn-link btn-xs text-light text-decoration-none p-0" onclick="copyText('task_modal_output')">
                        <i class="fas fa-copy me-1"></i> คัดลอกผลลัพธ์
                    </button>
                </div>
                <pre class="bg-black text-success p-3 rounded-3 mb-0 font-monospace" style="font-size: 0.78rem; max-height: 350px; overflow-y: auto; white-space: pre-wrap;" id="task_modal_output"></pre>
            </div>
            <div class="modal-footer border-0 p-3 bg-dark">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Tab Memory Handling (Preserve active tab across reload)
    document.addEventListener('DOMContentLoaded', function() {
        const activeTabId = localStorage.getItem('smartdata_active_monitor_tab') || window.location.hash;
        if (activeTabId) {
            const triggerEl = document.querySelector(`button[data-bs-target="${activeTabId}"]`);
            if (triggerEl) {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        document.querySelectorAll('#monitorTabs button[data-bs-toggle="pill"]').forEach(tabBtn => {
            tabBtn.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                localStorage.setItem('smartdata_active_monitor_tab', target);
                window.location.hash = target;
            });
        });
    });

    // 1. Manual Backup Runner
    function runManualBackup() {
        Swal.fire({
            title: 'สร้างไฟล์สำรองฐานข้อมูล?',
            text: 'ระบบจะทำการดัมพ์และบีบอัดข้อมูลทั้งหมดของ SmartData เป็นไฟล์ .sql.gz',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: '<i class="fas fa-save me-1"></i> ตกลง, สำรองข้อมูลทันที!',
            cancelButtonText: 'ยกเลิก'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'กำลังสำรองฐานข้อมูล...',
                    text: 'กรุณารอสักครู่ ห้ามปิดหน้าต่างนี้',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                try {
                    const response = await fetch("{{ route('admin.monitor.backup.run') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'สำรองข้อมูลสำเร็จ!',
                            html: `
                                <div class="text-start mt-2 p-3 bg-light rounded-3 font-monospace small">
                                    <div><strong>ไฟล์:</strong> ${data.data.filename}</div>
                                    <div><strong>ขนาด:</strong> ${data.data.file_size_formatted}</div>
                                    <div><strong>วิธีสำรอง:</strong> ${data.data.method}</div>
                                    <div><strong>เวลาที่ใช้:</strong> ${data.data.duration_seconds} วินาที</div>
                                </div>
                            `,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#198754'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'เกิดข้อผิดพลาดในการสำรองข้อมูล');
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'สำรองข้อมูลล้มเหลว!',
                        text: err.message,
                        confirmButtonText: 'ตกลง'
                    });
                }
            }
        });
    }

    // 2. Open Restore Modal
    function openRestoreModal(filename, createdAt, size) {
        document.getElementById('restore_filename').value = filename;
        document.getElementById('restore_display_filename').innerText = filename;
        document.getElementById('restore_display_date').innerText = createdAt;
        document.getElementById('restore_display_size').innerText = size;
        document.getElementById('restore_security_code').value = '';

        const modal = new bootstrap.Modal(document.getElementById('restoreBackupModal'));
        modal.show();
    }

    // 3. Submit Restore Backup
    async function submitRestoreBackup(e) {
        e.preventDefault();
        const filename = document.getElementById('restore_filename').value;
        const securityCode = document.getElementById('restore_security_code').value.trim();
        const btn = document.getElementById('btn_submit_restore');
        const origBtnText = btn.innerHTML;

        const modalEl = document.getElementById('restoreBackupModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        Swal.fire({
            title: 'กำลังกู้คืนฐานข้อมูล...',
            text: 'กรุณารอสักครู่ กระบวนการนี้อาจใช้เวลา 10-60 วินาที ห้ามปิดเบราว์เซอร์',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const response = await fetch(`{{ url('/admin/monitor/backup/restore') }}/${filename}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ security_code: securityCode })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'กู้คืนฐานข้อมูลสำเร็จ!',
                    text: data.message,
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                throw new Error(data.message || 'การกู้คืนล้มเหลว');
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'กู้คืนฐานข้อมูลล้มเหลว!',
                text: err.message,
                confirmButtonText: 'ตกลง'
            });
        }
    }

    // 4. Delete Backup File
    function deleteBackupFile(filename) {
        Swal.fire({
            title: 'ยืนยันการลบไฟล์สำรอง?',
            text: `ต้องการลบไฟล์สำรอง ${filename} ออกจากเซิร์ฟเวอร์ใช่หรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> ลบทันที',
            cancelButtonText: 'ยกเลิก'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`{{ url('/admin/monitor/backup/delete') }}/${filename}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบสำเร็จ!',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'ไม่สามารถลบไฟล์ได้');
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: err.message,
                        confirmButtonText: 'ตกลง'
                    });
                }
            }
        });
    }

    // 5. Clear Log File
    function clearLogFile(type) {
        Swal.fire({
            title: 'ยืนยันการล้างประวัติ Log?',
            text: `ต้องการล้างข้อมูลในไฟล์ ${type}.log ใช่หรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'ตกลง, ล้างข้อมูล',
            cancelButtonText: 'ยกเลิก'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const response = await fetch("{{ route('admin.monitor.log.clear') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ type: type })
                    });

                    const data = await response.json();
                    if (data.success) {
                        const targetEl = document.getElementById(type === 'backup' ? 'backup_log_content' : 'laravel_log_content');
                        if (targetEl) targetEl.innerText = 'ยังไม่มีประวัติ Log ในขณะนี้';
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'ล้าง Log สำเร็จ',
                            timer: 1200,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                } catch (err) {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: err.message });
                }
            }
        });
    }

    // 6. Manual Task Runner
    document.querySelectorAll('.btn-run-task').forEach(button => {
        button.addEventListener('click', async function() {
            const task = this.dataset.task;
            const origContent = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            try {
                const response = await fetch(`{{ url('/admin/monitor/run-task') }}/${task}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                this.disabled = false;
                this.innerHTML = origContent;

                if (response.ok && data.success) {
                    document.getElementById('task_modal_output').innerText = data.output || '(ไม่มีข้อมูล Output)';
                    document.getElementById('task_output_status').innerText = 'SUCCESS';
                    document.getElementById('task_output_status').className = 'badge bg-success-subtle text-success border font-monospace px-2 py-1';
                    
                    const modal = new bootstrap.Modal(document.getElementById('taskOutputModal'));
                    modal.show();
                } else {
                    throw new Error(data.message || 'การรันงานล้มเหลว');
                }
            } catch (err) {
                this.disabled = false;
                this.innerHTML = origContent;
                Swal.fire({
                    icon: 'error',
                    title: 'การรันงานล้มเหลว!',
                    text: err.message,
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    });

    // Copy Helper
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
            title: 'คัดลอกสำเร็จ!',
            timer: 1200,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
</script>
@endpush
@endsection
