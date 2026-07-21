@extends('layouts.admin')

@section('title', 'System Monitor - SmartData')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold text-success mb-1"><i class="fas fa-desktop-alt me-2"></i>System Monitor</h1>
            <p class="text-muted mb-0">ตรวจสอบสถานะการทำงานและคำสั่งอัตโนมัติ (Scheduler / Cron Job)</p>
        </div>
        <a href="{{ route('admin.monitor.index') }}" class="btn btn-outline-success shadow-sm rounded-3">
            <i class="fas fa-sync-alt me-2"></i>รีเฟรชข้อมูล
        </a>
    </div>

    <!-- Health Grid -->
    <div class="row g-4 mb-4">
        <!-- Scheduler Status Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-3 position-relative overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 text-secondary">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        @if($schedulerStatus === 'online')
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i>ปกติ (Online)</span>
                        @elseif($schedulerStatus === 'delayed')
                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill"><i class="fas fa-exclamation-triangle me-1"></i>ดีเลย์ (Delayed)</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i>ไม่ทำงาน (Offline)</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Laravel Scheduler (Cron)</h5>
                    <p class="text-muted small mb-2">ต้องรัน crontab `* * * * *` เพื่อกระตุ้นทุก 1 นาที</p>
                    <button class="btn btn-xs btn-outline-danger w-100 rounded-pill mb-3 small py-1" style="font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#cronFixModal">
                        <i class="fas fa-tools me-1"></i>วิธีแก้ไขเมื่อไม่ทำงาน
                    </button>
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>รันล่าสุด:</span>
                            <strong class="text-dark">{{ $schedulerLastRun ?? 'ไม่พบข้อมูล' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>สถานะ:</span>
                            <span class="fw-bold {{ $schedulerStatus === 'online' ? 'text-success' : 'text-danger' }}">
                                {{ $schedulerStatus === 'online' ? 'ทำงานปกติ' : ($schedulerStatus === 'delayed' ? 'ทำงานล่าช้า (> 2 นาที)' : 'หยุดทำงาน') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Local DB Status Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 text-secondary">
                            <i class="fas fa-database fa-2x"></i>
                        </div>
                        @if($localDbStatus === 'online')
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i>ปกติ (Online)</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i>ตัดการเชื่อมต่อ</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Local Database (MySQL)</h5>
                    <p class="text-muted small mb-3">ฐานข้อมูลระบบหลักสำหรับเก็บประวัติผู้ใช้และข้อมูลตั้งค่า</p>
                    <div class="border-top pt-3">
                        @if($localDbStatus === 'online')
                            <div class="text-success small"><i class="fas fa-info-circle me-1"></i>เชื่อมต่อฐานข้อมูลหลักเรียบร้อย</div>
                        @else
                            <div class="text-danger small text-break"><i class="fas fa-exclamation-triangle me-1"></i>{{ $localDbError }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- HOSxP DB Status Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-3">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 text-secondary">
                            <i class="fas fa-server fa-2x"></i>
                        </div>
                        @if($hosxpDbStatus === 'online')
                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fas fa-check-circle me-1"></i>ปกติ (Online)</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="fas fa-times-circle me-1"></i>ตัดการเชื่อมต่อ</span>
                        @endif
                    </div>
                    <h5 class="fw-bold text-dark mb-1">HOSxP Database</h5>
                    <p class="text-muted small mb-3">ฐานข้อมูลโรงพยาบาลสำหรับดึงสถิติบริการต่างๆ</p>
                    <div class="border-top pt-3">
                        @if($hosxpDbStatus === 'online')
                            <div class="text-success small"><i class="fas fa-info-circle me-1"></i>เชื่อมต่อฐานข้อมูล HOSxP เรียบร้อย</div>
                        @else
                            <div class="text-danger small text-break"><i class="fas fa-exclamation-triangle me-1"></i>{{ $hosxpDbError }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: System Details & Log Viewer -->
        <div class="col-lg-7">
            <!-- Server Info Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-info-circle text-success me-2"></i>ข้อมูลเซิร์ฟเวอร์</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row text-muted small">
                        <div class="col-6 mb-2">ระบบปฏิบัติการ: <strong class="text-dark">{{ $osName }}</strong></div>
                        <div class="col-6 mb-2">เวลาเซิร์ฟเวอร์ (PHP): <strong class="text-dark">{{ $serverTime }}</strong></div>
                        <div class="col-12 text-warning"><i class="fas fa-exclamation-circle me-1"></i>กรุณาตรวจสอบว่าเวลาของระบบตรงตามเขตเวลาประเทศไทย (ICT / Asia/Bangkok)</div>
                    </div>
                </div>
            </div>

            <!-- Log Viewer Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-file-alt text-success me-2"></i>Laravel Log (laravel.log)</h5>
                    <span class="badge bg-light text-dark">50 บรรทัดล่าสุด</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="bg-dark text-light p-3 rounded-3 font-monospace small overflow-auto" style="max-height: 400px; white-space: pre-wrap;">
                        @if(empty($logLines))
                            <span class="text-muted">ไม่พบข้อมูลล็อกล่าสุด หรือไฟล์ว่างเปล่า</span>
                        @else
                            @foreach($logLines as $line)
                                @if(Str::contains($line, 'error') || Str::contains($line, 'exception') || Str::contains($line, 'ERROR'))
                                    <div class="text-danger">{{ $line }}</div>
                                @elseif(Str::contains($line, 'info') || Str::contains($line, 'INFO'))
                                    <div class="text-info">{{ $line }}</div>
                                @else
                                    <div>{{ $line }}</div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Task Manual Trigger Panel -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-cogs text-success me-2"></i>ทดสอบรันสคริปต์ส่งงาน (Manual Run)</h5>
                    <p class="text-muted small mb-0">หากระบบอัตโนมัติไม่ทำงาน สามารถกดปุ่มด้านล่างเพื่อรันส่งข้อมูลได้ทันที</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="list-group list-group-flush">
                        <!-- Task: Night Service -->
                        <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">ส่งสถิติเวรดึก (00.00 - 08.00 น.)</h6>
                                <p class="text-muted small mb-0">ทำงานอัตโนมัติเวลา: 08:00 น.</p>
                            </div>
                            <button class="btn btn-sm btn-success px-3 rounded-pill btn-run-task" data-task="service_night">
                                <i class="fas fa-play me-1"></i>รัน
                            </button>
                        </div>

                        <!-- Task: Morning Service -->
                        <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">ส่งสถิติเวรเช้า (08.00 - 16.00 น.)</h6>
                                <p class="text-muted small mb-0">ทำงานอัตโนมัติเวลา: 16:00 น.</p>
                            </div>
                            <button class="btn btn-sm btn-success px-3 rounded-pill btn-run-task" data-task="service_morning">
                                <i class="fas fa-play me-1"></i>รัน
                            </button>
                        </div>

                        <!-- Task: Afternoon Service -->
                        <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">ส่งสถิติเวรบ่าย (16.00 - 24.00 น.)</h6>
                                <p class="text-muted small mb-0">ทำงานอัตโนมัติเวลา: 00:01 น.</p>
                            </div>
                            <button class="btn btn-sm btn-success px-3 rounded-pill btn-run-task" data-task="service_afternoon">
                                <i class="fas fa-play me-1"></i>รัน
                            </button>
                        </div>

                        <!-- Task: Replication -->
                        <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">ตรวจสอบ MySQL Replication</h6>
                                <p class="text-muted small mb-0">ทำงานอัตโนมัติ: ทุกๆ 10 นาที</p>
                            </div>
                            <button class="btn btn-sm btn-success px-3 rounded-pill btn-run-task" data-task="replication">
                                <i class="fas fa-play me-1"></i>รัน
                            </button>
                        </div>

                        <!-- Task: Backup HOSxP -->
                        <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">ตรวจสอบ Backup HOSxP</h6>
                                <p class="text-muted small mb-0">ทำงานอัตโนมัติ: ทุกๆ 10 นาที</p>
                            </div>
                            <button class="btn btn-sm btn-success px-3 rounded-pill btn-run-task" data-task="backup_hosxp">
                                <i class="fas fa-play me-1"></i>รัน
                            </button>
                        </div>

                        <!-- Task: Audit EMR -->
                        <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold mb-1">ส่งรายงาน EMR Audit</h6>
                                <p class="text-muted small mb-0">ทำงานอัตโนมัติ: 07:00 น. และ 19:00 น.</p>
                            </div>
                            <button class="btn btn-sm btn-success px-3 rounded-pill btn-run-task" data-task="audit_emr">
                                <i class="fas fa-play me-1"></i>รัน
                            </button>
                        </div>

                        <!-- Task: Test Heartbeat -->
                        <div class="list-group-item border-0 px-0 py-3 d-flex justify-content-between align-items-center bg-light-subtle rounded-3 mt-2">
                            <div>
                                <h6 class="fw-bold mb-1 text-primary">จำลองรัน Heartbeat</h6>
                                <p class="text-muted small mb-0">กดปุ่มนี้เพื่อทดสอบบันทึกเวลาเข้าระบบ Monitor</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary px-3 rounded-pill btn-run-task" data-task="test_heartbeat">
                                <i class="fas fa-heartbeat me-1"></i>กระตุ้น
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
    .btn-run-task { transition: all 0.2s ease; }
    .btn-run-task:hover { transform: scale(1.05); }
</style>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function() {
    const runButtons = document.querySelectorAll('.btn-run-task');

    runButtons.forEach(button => {
        button.addEventListener('click', function() {
            const task = this.getAttribute('data-task');
            const taskName = this.closest('.list-group-item').querySelector('h6').innerText;

            Swal.fire({
                title: 'ยืนยันรันงาน?',
                text: `คุณกำลังสั่งรันงาน "${taskName}" ด้วยตนเอง`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, สั่งรันงานเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show Loading
                    Swal.fire({
                        title: 'กำลังรันงาน...',
                        text: 'กรุณารอระบบประมวลผลสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Send AJAX
                    fetch(`{{ url('admin/monitor/run-task') }}/${task}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json().then(data => ({ status: response.status, body: data })))
                    .then(res => {
                        if (res.status === 200 && res.body.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'รันสำเร็จ',
                                html: `<div class="text-start small bg-light p-3 rounded font-monospace" style="max-height: 250px; overflow-y: auto;">
                                          <strong>Output:</strong><br>${res.body.output || 'รันสำเร็จ (ไม่มีข้อความตอบรับ)'}
                                       </div>`,
                                confirmButtonText: 'ตกลง'
                            }).then(() => {
                                // Auto refresh after run
                                location.reload();
                            });
                        } else {
                            throw new Error(res.body.message || 'เกิดข้อผิดพลาดในการรันงาน');
                        }
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: err.message,
                            confirmButtonText: 'ตกลง'
                        });
                    });
                }
            });
        });
    });
});
</script>

<!-- Cron Fix Instructions Modal -->
<div class="modal fade" id="cronFixModal" tabindex="-1" aria-labelledby="cronFixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-gradient-danger-custom text-white border-0" style="background: linear-gradient(135deg, #dc3545 0%, #f86b7a 100%);">
                <h5 class="modal-title fw-bold" id="cronFixModalLabel"><i class="fas fa-tools me-2"></i>วิธีแก้ไข Laravel Scheduler (Cron) ไม่ทำงาน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <h6 class="fw-bold text-dark"><i class="fas fa-terminal me-2 text-danger"></i>1. ตรวจสอบและตั้งค่า Crontab บน Server (AlmaLinux)</h6>
                    <p class="text-muted small mb-2">รันคำสั่งแก้ไข Crontab ของ User ที่ต้องการรันงาน (แนะนำให้ใช้สิทธิ์เดียวกับ Web Server เช่น `nginx` หรือ `apache`):</p>
                    <pre class="bg-dark text-light p-3 rounded-3 font-monospace small">sudo crontab -u nginx -e</pre>
                    <p class="text-muted small mb-2">จากนั้นเพิ่มบรรทัดคำสั่งตั้งค่าดักรันทุก 1 นาที (ปรับแต่งพาธ php และโฟลเดอร์โปรเจกต์ให้ถูกต้อง):</p>
                    <pre class="bg-dark text-light p-3 rounded-3 font-monospace small">* * * * * /usr/bin/php {{ base_path() }}/artisan schedule:run >> {{ storage_path('logs/cron.log') }} 2>&1</pre>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-dark"><i class="fas fa-server me-2 text-danger"></i>2. ตรวจสอบการทำงานของ Service Cron Daemon</h6>
                    <p class="text-muted small mb-2">ตรวจสอบสถานะของ `crond` บนเซิร์ฟเวอร์ AlmaLinux:</p>
                    <pre class="bg-dark text-light p-3 rounded-3 font-monospace small">sudo systemctl status crond</pre>
                    <p class="text-muted small mb-2">หากสถานะปิดอยู่ให้สั่งเริ่มทำงานใหม่:</p>
                    <pre class="bg-dark text-light p-3 rounded-3 font-monospace small">sudo systemctl enable crond && sudo systemctl restart crond</pre>
                </div>

                <div class="mb-0">
                    <h6 class="fw-bold text-dark"><i class="fas fa-folder-open me-2 text-danger"></i>3. ตรวจสอบ Permission ของโฟลเดอร์ในโปรเจกต์</h6>
                    <p class="text-muted small mb-2">หาก Cron ทำงานแต่ไม่สามารถบันทึก Log ลงในระบบได้ ให้เคลียร์สิทธิ์ของโฟลเดอร์ `storage`:</p>
                    <pre class="bg-dark text-light p-3 rounded-3 font-monospace small">sudo chmod -R 775 {{ storage_path() }}
sudo chown -R nginx:nginx {{ storage_path() }}</pre>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>
@endsection
