@extends('layouts.app')

@section('title', 'ดองกี้ - ผู้ช่วย AI โรงพยาบาล')

@section('content')
@php
    $donkeyImgUrl = file_exists(public_path('images/donkey.jpg')) ? asset('images/donkey.jpg') . '?v=' . filemtime(public_path('images/donkey.jpg')) : asset('images/logo.png');
@endphp
<div class="container-fluid px-3 px-md-4 py-3" style="height: calc(100vh - 85px);">
    <div class="row h-100 g-3">
        <!-- Sidebar: Chat History -->
        <div class="col-md-3 col-lg-2 d-none d-md-flex flex-column h-100">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column overflow-hidden bg-white">
                <!-- New Chat Button -->
                <div class="p-3 border-bottom">
                    <button type="button" class="btn btn-primary rounded-pill w-100 py-2 shadow-sm fw-bold" onclick="createNewSession()">
                        <i class="fas fa-plus me-1"></i> การสนทนาใหม่
                    </button>
                </div>

                <!-- Sessions List -->
                <div class="flex-grow-1 overflow-auto p-2" id="sessionsList">
                    <div class="text-muted small px-2 py-1 fw-bold text-uppercase">ประวัติการสนทนา</div>
                    @forelse($sessions as $s)
                    <div class="session-item p-2 rounded-3 mb-1 d-flex align-items-center justify-content-between cursor-pointer {{ ($currentSession && $currentSession->session_uuid === $s->session_uuid) ? 'bg-primary-subtle text-primary fw-bold' : 'text-dark hover-bg-light' }}" data-uuid="{{ $s->session_uuid }}" onclick="switchSession('{{ $s->session_uuid }}')">
                        <div class="text-truncate flex-grow-1 me-2 small" title="{{ $s->title }}">
                            <i class="far fa-comment-dots me-1 text-secondary"></i> {{ $s->title }}
                        </div>
                        <button type="button" class="btn btn-link btn-sm text-muted p-1 delete-session-btn hover-danger" onclick="event.stopPropagation(); deleteSession('{{ $s->session_uuid }}')" title="ลบการสนทนานี้">
                            <i class="fas fa-trash-alt fa-xs"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted small">
                        ยังไม่มีประวัติการสนทนา
                    </div>
                    @endforelse
                </div>

                <!-- Bottom: Single Clear History Button -->
                <div class="p-3 border-top bg-light-subtle">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill w-100 py-2 shadow-xs d-flex align-items-center justify-content-center" onclick="clearAllSessions()" title="ล้างประวัติการสนทนาทั้งหมดของคุณ">
                        <i class="fas fa-trash-alt me-2"></i> ล้างประวัติสนทนาทั้งหมด
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-9 col-lg-10 h-100 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column overflow-hidden bg-white">
                <!-- Chat Header Toolbar -->
                <div class="px-4 py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center bg-white gap-2">
                    <div class="d-flex align-items-center">
                        <img src="{{ $donkeyImgUrl }}" class="rounded-circle me-3 shadow-sm bg-white p-1 border" style="width: 42px; height: 42px; object-fit: cover;" alt="ดองกี้">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">ดองกี้</h5>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0 small">
                                <i class="fas fa-circle fa-2xs me-1"></i> Online พร้อมใช้งาน
                            </span>
                        </div>
                    </div>

                    <!-- Header Actions -->
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <!-- Hospital Knowledge Library Direct Link -->
                        <a href="{{ route('ai.knowledge.index') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs text-dark" title="เปิดห้องสมุดคลังความรู้ CPG & ระเบียบปฏิบัติ">
                            <i class="fas fa-book-medical me-1 text-info"></i> คลังความรู้
                        </a>

                        <!-- Target Database Selector Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-pill px-3 text-secondary border shadow-xs dropdown-toggle" type="button" id="targetDbDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-database me-1 text-primary"></i> <span id="currentDbLabel">ฐานข้อมูล: ตรวจหาอัตโนมัติ</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end p-2 shadow-lg border-0 rounded-4" id="targetDbDropdownMenu" style="min-width: 250px; font-size: 0.85rem;" aria-labelledby="targetDbDropdown">
                                <li><h6 class="dropdown-header text-muted small fw-bold">เลือกฐานข้อมูลเป้าหมาย</h6></li>
                                <li><a class="dropdown-item rounded-3 py-2 cursor-pointer active" href="javascript:void(0)" onclick="selectTargetDb('auto', 'ตรวจหาอัตโนมัติ', this)"><i class="fas fa-magic text-primary me-2"></i> <strong>ตรวจหาอัตโนมัติ (Auto)</strong> <span class="badge bg-primary-subtle text-primary ms-1" style="font-size: 0.68rem;">แนะนำ</span></a></li>
                                <li><a class="dropdown-item rounded-3 py-2 cursor-pointer" href="javascript:void(0)" onclick="selectTargetDb('hosxp', 'HOSxP (คนไข้)', this)"><i class="fas fa-hospital text-danger me-2"></i> <strong>HOSxP</strong> (เวชระเบียน/คนไข้)</a></li>
                                <li><a class="dropdown-item rounded-3 py-2 cursor-pointer" href="javascript:void(0)" onclick="selectTargetDb('backoffice', 'Backoffice', this)"><i class="fas fa-building text-warning me-2"></i> <strong>Backoffice</strong> (พัสดุ/บุคคล)</a></li>
                                <li><a class="dropdown-item rounded-3 py-2 cursor-pointer" href="javascript:void(0)" onclick="selectTargetDb('mysql', 'SmartData', this)"><i class="fas fa-cog text-secondary me-2"></i> <strong>SmartData</strong> (ระบบภายใน)</a></li>
                            </ul>
                        </div>
                        <input type="hidden" id="target_db" value="auto">
                        <input type="hidden" name="chat_mode" id="mode_smart" value="smart">
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="flex-grow-1 overflow-auto p-4" id="chatContainer" style="background: #f8fafc;">
                    @if(!$currentSession || $currentSession->messages->isEmpty())
                    <!-- Welcome Hero -->
                    <div id="welcomeHero" class="text-center py-5 my-auto">
                        <div class="mb-4">
                            <div class="d-inline-flex p-3 rounded-circle shadow-sm bg-white border" style="width: 90px; height: 90px;">
                                <img src="{{ $donkeyImgUrl }}" class="w-100 h-100 rounded-circle" style="object-fit: cover;" alt="ดองกี้">
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">ยินดีต้อนรับสู่ ดองกี้</h3>
                        <p class="text-muted mb-4 mx-auto" style="max-width: 550px;">
                            ผู้ช่วย AI วิเคราะห์ข้อมูลสุขภาพ แปลงภาษาไทยเป็น SQL ค้นหาสถิติคนไข้ HOSxP, ข้อมูล Backoffice และตอบคำถามจากคลังความรู้ CPG โรงพยาบาล
                        </p>

                        <div class="text-muted small fw-bold mb-3 text-uppercase">ตัวอย่างคำถามที่สามารถคลิกถามได้ทันที:</div>
                        <div class="d-flex flex-wrap justify-content-center gap-2 mx-auto" style="max-width: 860px;">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 bg-white shadow-sm fw-medium" onclick="sendQuickPrompt('ขอกราฟสรุป 10 อันดับโรคผู้ป่วยนอก (OPD) ที่มารับบริการมากที่สุดเดือนนี้')">
                                <i class="bi bi-bar-chart-fill text-success me-1"></i> กราฟ 10 อันดับโรค OPD เดือนนี้
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 bg-white shadow-sm fw-medium" onclick="sendQuickPrompt('ขอกราฟสรุปยอดผู้ป่วยนอก (OPD) ย้อนหลัง 30 วัน แยกตามสิทธิการรักษา')">
                                <i class="bi bi-pie-chart-fill text-primary me-1"></i> กราฟยอดผู้ป่วยนอกแยกตามสิทธิ (30 วัน)
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 bg-white shadow-sm" onclick="sendQuickPrompt('5 อันดับโรคผู้ป่วยนอก (Top 5 OPD Dx) ที่มารับบริการมากที่สุดเดือนนี้')">
                                <i class="fas fa-stethoscope me-1"></i> 5 อันดับโรคผู้ป่วยนอกเดือนนี้
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 bg-white shadow-sm" onclick="sendQuickPrompt('จำนวนผู้ป่วยใน (IPD) กำลัง Admit แยกตามหอผู้ป่วยและอัตราครองเตียง')">
                                <i class="fas fa-bed me-1"></i> ยอดผู้ป่วยใน IPD กำลัง Admit รายวอร์ด
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 bg-white shadow-sm" onclick="sendQuickPrompt('ขอสรุปจำนวนบุคลากรในโรงพยาบาลแยกตามกลุ่มงาน จาก Backoffice', 'backoffice')">
                                <i class="fas fa-users me-1"></i> สรุปจำนวนบุคลากรแยกกลุ่มงาน (Backoffice)
                            </button>
                            <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3 py-2 bg-white shadow-sm" onclick="sendQuickPrompt('แนวทางการดูแลรักษาผู้ป่วย STEMI หรือ Stroke มีขั้นตอนอย่างไร', 'rag')">
                                <i class="fas fa-book-medical me-1"></i> แนวทาง CPG การรักษา Stroke / STEMI
                            </button>
                        </div>
                    </div>
                    @else
                    <!-- Render Existing Messages -->
                    @foreach($currentSession->messages as $msg)
                        @if($msg->role === 'user')
                        <div class="d-flex justify-content-end mb-4">
                            <div class="user-bubble p-3 rounded-4 shadow-sm text-white" style="max-width: 75%; background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
                                <div class="message-text" style="white-space: pre-wrap;">{{ $msg->content }}</div>
                            </div>
                        </div>
                        @else
                        <div class="d-flex justify-content-start mb-4">
                            <div class="me-3">
                                <img src="{{ $donkeyImgUrl }}" class="rounded-circle bg-white p-1 shadow-sm border" style="width: 36px; height: 36px; object-fit: cover;" alt="ดองกี้">
                            </div>
                            <div class="assistant-bubble p-3 rounded-4 shadow-sm bg-white border" style="max-width: 85%;">
                                <div class="message-text mb-2" style="white-space: pre-wrap;">{!! nl2br(preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', e($msg->content))) !!}</div>

                                @if(!empty($msg->query_result) && is_array($msg->query_result))
                                @php
                                    $histCols = array_keys($msg->query_result[0] ?? []);
                                    $prevPrompt = $currentSession->messages->where('id', '<', $msg->id)->where('role', 'user')->last()?->content ?? '';
                                @endphp
                                <div class="copilot-vis-container" id="container-vis-hist-{{ $msg->id }}"></div>
                                <script>
                                    (function() {
                                        const initFn = function() {
                                            if (typeof renderVisContainer === 'function') {
                                                renderVisContainer('container-vis-hist-{{ $msg->id }}', {
                                                    columns: @json($histCols),
                                                    rows: @json($msg->query_result),
                                                    total_rows: {{ count($msg->query_result) }},
                                                    target_db: '{{ $msg->target_db ?: "hosxp" }}',
                                                    content: @json($msg->content)
                                                }, @json($prevPrompt));
                                            } else {
                                                setTimeout(initFn, 80);
                                            }
                                        };
                                        if (document.readyState === 'loading') {
                                            document.addEventListener('DOMContentLoaded', initFn);
                                        } else {
                                            initFn();
                                        }
                                    })();
                                </script>
                                @endif

                                @if(auth()->user()->role === 'admin' && $msg->message_type === 'sql_query' && $msg->generated_sql)
                                <details class="mt-2 text-muted">
                                    <summary class="small cursor-pointer user-select-none text-muted" style="font-size: 0.75rem;">
                                        <i class="fas fa-terminal me-1"></i> คำสั่ง SQL ที่ใช้สืบค้น (สำหรับ Admin)
                                    </summary>
                                    <div class="sql-box rounded-3 p-2 bg-dark text-light mt-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-secondary" style="font-size: 0.68rem;">{{ strtoupper($msg->target_db ?: 'HOSxP') }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" style="font-size: 0.68rem;" onclick="copySql(this)">
                                                <i class="fas fa-copy me-1"></i> Copy SQL
                                            </button>
                                        </div>
                                        <pre class="mb-0 font-monospace small text-info" style="white-space: pre-wrap; font-size: 0.75rem;"><code>{{ $msg->generated_sql }}</code></pre>
                                    </div>
                                </details>
                                @endif

                                @if($msg->message_type === 'rag_result' && !empty($msg->sources))
                                <div class="mt-3 pt-2 border-top">
                                    <div class="small fw-bold text-muted mb-2"><i class="fas fa-book-open me-1"></i> แหล่งอ้างอิงจากคลังความรู้:</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($msg->sources as $src)
                                        <span class="badge bg-info-subtle text-info border px-2 py-1 small" title="{{ $src['snippet'] ?? '' }}">
                                            <i class="fas fa-file-alt me-1"></i> {{ $src['title'] ?? 'เอกสาร' }} (Chunk #{{ $src['chunk_index'] ?? 1 }})
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                    @endif
                </div>

                <!-- Input Footer -->
                <div class="p-3 border-top bg-white">
                    <form id="chatForm" onsubmit="handleChatSubmit(event); return false;" action="javascript:void(0);">
                        <div class="input-group shadow-sm rounded-4 overflow-hidden border">
                            <textarea id="messageInput" class="form-control border-0 py-3 px-4 bg-light" rows="1" placeholder="พิมพ์คำถามภาษาไทย เช่น ขอยอดผู้ป่วยนอกวันนี้, รายชื่อเจ้าหน้าที่, หรือแนวทาง CPG... (กด Enter เพื่อส่ง, Shift+Enter ขึ้นบรรทัดใหม่)" style="resize: none;"></textarea>
                            <button type="button" id="sendBtn" class="btn btn-primary px-4 border-0 d-flex align-items-center justify-content-center" onclick="handleChatSubmit(event)">
                                <i class="fas fa-paper-plane fa-lg"></i>
                            </button>
                        </div>
                    </form>
                    <div class="d-flex justify-content-between align-items-center mt-2 px-2 text-muted" style="font-size: 0.75rem;">
                        <span><i class="fas fa-shield-alt text-success me-1"></i> ปลอดภัย 100%: รันเฉพาะ SELECT และจำกัด 100 รายการเสมอ</span>
                        <span>SmartData Copilot AI Hospital Assistant</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
<style>
.hover-bg-light:hover {
    background-color: #f1f5f9;
}
.cursor-pointer {
    cursor: pointer;
}
.btn-check:checked + .btn {
    background-color: #0d6efd !important;
    color: #ffffff !important;
    font-weight: 600;
}
.hover-danger:hover {
    color: #dc3545 !important;
}

/* SmartData Interactive Visualization Styles (RiMS Chart.js Style) */
.table-card-container {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #ffffff;
    overflow: hidden;
    margin-top: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}
.table-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
    gap: 8px;
}
.table-responsive-custom {
    max-height: 380px;
    overflow: auto;
}
.chart-view-panel {
    padding: 14px 16px;
    background: #ffffff;
}
.chart-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px dashed #e2e8f0;
}
.chart-canvas-wrapper {
    position: relative;
    width: 100%;
    height: 360px;
    max-height: 420px;
}
.chart-stats-card {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}
.chart-stat-item {
    font-size: 0.76rem;
    padding: 5px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #475569;
}
.chart-stat-item strong {
    color: #0f172a;
}
.data-table-copilot {
    width: 100%;
    margin-bottom: 0;
    font-size: 0.82rem;
    border-collapse: separate;
    border-spacing: 0;
}
.data-table-copilot thead th {
    position: sticky;
    top: 0;
    background: #e2e8f0;
    color: #1e293b;
    font-weight: 700;
    padding: 8px 12px;
    border-bottom: 2px solid #cbd5e1;
    white-space: nowrap;
    z-index: 2;
}
.data-table-copilot tbody td {
    padding: 7px 12px;
    border-bottom: 1px solid #f1f5f9;
    white-space: nowrap;
}
.data-table-copilot tbody tr:hover {
    background-color: #f8fafc;
}
</style>
@endpush

@push('scripts')
<!-- Offline Local Vendors: SheetJS for Excel and Chart.js -->
<script src="{{ asset('vendor/xlsx.full.min.js') }}"></script>
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script>
var isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
var currentSessionUuid = '{{ $currentSession->session_uuid ?? "" }}';
window.smartdataLogoUrl = "{{ $donkeyImgUrl }}";
var smartdataLogoUrl = window.smartdataLogoUrl;

document.getElementById('messageInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        handleChatSubmit(e);
    }
});

// Auto-expand textarea
document.getElementById('messageInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

function handleChatSubmit(e) {
    if (e) {
        e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
    }

    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (!message) return false;

    window.lastUserQuestion = message;

    input.value = '';
    input.style.height = 'auto';

    // Remove welcome hero if present
    const hero = document.getElementById('welcomeHero');
    if (hero) hero.remove();

    appendUserMessage(message);

    const mode = (document.querySelector('input[name="chat_mode"]:checked') || document.querySelector('input[name="chat_mode"]'))?.value || 'smart';
    const targetDb = document.getElementById('target_db')?.value || 'auto';

    const loadingBubble = appendLoadingBubble();
    scrollChatToBottom();

    if (!currentSessionUuid) {
        currentSessionUuid = 'sess-' + Math.random().toString(36).substr(2, 9);
    }

    // Send AJAX request
    fetch('{{ route('ai.chat.message') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            message: message,
            session_uuid: currentSessionUuid,
            mode: mode,
            target_db: targetDb
        })
    })
    .then(async res => {
        let data = {};
        try {
            data = await res.json();
        } catch (e) {
            data = {
                success: false,
                content: 'เกิดข้อผิดพลาดจากเซิร์ฟเวอร์ (HTTP ' + res.status + ')'
            };
        }
        if (!res.ok && !data.content) {
            data.content = data.message || ('เกิดข้อผิดพลาดจากเซิร์ฟเวอร์ (HTTP ' + res.status + ')');
        }
        return data;
    })
    .then(data => {
        loadingBubble.remove();
        appendAssistantMessage(data);
        scrollChatToBottom();
        if (data.session_uuid) {
            currentSessionUuid = data.session_uuid;
        }
    })
    .catch(err => {
        loadingBubble.remove();
        appendAssistantMessage({
            success: false,
            content: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + (err.message || 'กรุณาลองใหม่อีกครั้ง')
        });
        scrollChatToBottom();
    });

    return false;
}

function selectTargetDb(dbValue, label, el) {
    const dbInput = document.getElementById('target_db');
    if (dbInput) dbInput.value = dbValue;
    const lbl = document.getElementById('currentDbLabel');
    if (lbl) lbl.innerText = 'ฐานข้อมูล: ' + label;
    if (el) {
        document.querySelectorAll('#targetDbDropdownMenu .dropdown-item').forEach(item => item.classList.remove('active'));
        el.classList.add('active');
    }
}

function sendQuickPrompt(promptText, targetDbChoice = null) {
    if (targetDbChoice && targetDbChoice !== 'rag' && targetDbChoice !== 'sql' && targetDbChoice !== 'smart') {
        const dbLabels = {
            'auto': 'ตรวจหาอัตโนมัติ',
            'hosxp': 'HOSxP (คนไข้)',
            'backoffice': 'Backoffice',
            'mysql': 'SmartData'
        };
        const dbInput = document.getElementById('target_db');
        if (dbInput) dbInput.value = targetDbChoice;
        const lbl = document.getElementById('currentDbLabel');
        if (lbl && dbLabels[targetDbChoice]) {
            lbl.innerText = 'ฐานข้อมูล: ' + dbLabels[targetDbChoice];
        }
    }
    document.getElementById('messageInput').value = promptText;
    document.getElementById('chatForm').dispatchEvent(new Event('submit'));
}

function appendUserMessage(text) {
    const container = document.getElementById('chatContainer');
    const div = document.createElement('div');
    div.className = 'd-flex justify-content-end mb-4';
    div.innerHTML = `
        <div class="user-bubble p-3 rounded-4 shadow-sm text-white" style="max-width: 75%; background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);">
            <div class="message-text" style="white-space: pre-wrap;">${escapeHtml(text)}</div>
        </div>
    `;
    container.appendChild(div);
}

function appendLoadingBubble() {
    const container = document.getElementById('chatContainer');
    const div = document.createElement('div');
    div.className = 'd-flex justify-content-start mb-4 loading-bubble-wrapper';
    div.innerHTML = `
        <div class="me-3">
            <img src="${smartdataLogoUrl}" class="rounded-circle bg-white p-1 shadow-sm border fa-spin" style="width: 36px; height: 36px; object-fit: contain;" alt="SmartData">
        </div>
        <div class="p-3 rounded-4 shadow-sm bg-white border text-muted small d-flex align-items-center">
            <span class="spinner-grow spinner-grow-sm me-2 text-primary"></span>
            SmartData Copilot กำลังประมวลผล...
        </div>
    `;
    container.appendChild(div);
    return div;
}

function appendAssistantMessage(data) {
    const container = document.getElementById('chatContainer');
    const div = document.createElement('div');
    div.className = 'd-flex justify-content-start mb-4';

    let extraHtml = '';
    let visContainerId = null;

    // If SQL query result with rows
    if (data.mode === 'sql' && data.rows && data.rows.length > 0) {
        visContainerId = 'container-vis-' + Math.random().toString(36).substring(2, 9);
        extraHtml += `<div class="copilot-vis-container w-100" id="${visContainerId}"></div>`;
    }

    // Show SQL only for Admin in a collapsed details toggle
    if (isAdmin && data.sql) {
        let errorAlert = '';
        if (data.raw_error) {
            errorAlert = `
                <div class="p-2 mb-2 rounded bg-danger bg-opacity-25 border border-danger border-opacity-50 text-white small" style="font-size: 0.72rem; white-space: pre-wrap;">
                    <div class="fw-bold text-warning mb-1"><i class="fas fa-exclamation-triangle me-1"></i> ข้อมูลทางเทคนิคสำหรับ Admin (Technical Error):</div>
                    <div class="font-monospace text-light opacity-75">${escapeHtml(data.raw_error)}</div>
                </div>
            `;
        }

        extraHtml += `
            <details class="mt-2 text-muted">
                <summary class="small cursor-pointer user-select-none text-muted" style="font-size: 0.75rem;">
                    <i class="fas fa-terminal me-1"></i> คำสั่ง SQL ที่ใช้สืบค้น (สำหรับ Admin)
                </summary>
                <div class="sql-box rounded-3 p-2 bg-dark text-light mt-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-secondary" style="font-size: 0.68rem;">${(data.target_db || 'HOSxP').toUpperCase()}</span>
                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" style="font-size: 0.68rem;" onclick="copySql(this)">
                            <i class="fas fa-copy me-1"></i> Copy SQL
                        </button>
                    </div>
                    ${errorAlert}
                    <pre class="mb-0 font-monospace small text-info" style="white-space: pre-wrap; font-size: 0.75rem;"><code>${escapeHtml(data.sql)}</code></pre>
                </div>
            </details>
        `;
    }

    // If RAG source citations
    if (data.mode === 'rag' && data.sources && data.sources.length > 0) {
        extraHtml += `<div class="mt-3 pt-2 border-top">
            <div class="small fw-bold text-muted mb-2"><i class="fas fa-book-open me-1"></i> แหล่งอ้างอิงจากคลังความรู้:</div>
            <div class="d-flex flex-wrap gap-2">`;
        data.sources.forEach(src => {
            extraHtml += `<span class="badge bg-info-subtle text-info border px-2 py-1 small" title="${escapeHtml(src.snippet || '')}">
                <i class="fas fa-file-alt me-1"></i> ${escapeHtml(src.title || 'เอกสาร')} (Chunk #${src.chunk_index || 1})
            </span>`;
        });
        extraHtml += `</div></div>`;
    }

    let formattedContent = escapeHtml(data.content || '')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>');

    let bodyHtml = '';
    if (data.success === false) {
        bodyHtml = `
            <div class="alert alert-warning py-2 px-3 mb-2 rounded-3 border-warning border-opacity-50 small d-flex align-items-center">
                <i class="fas fa-exclamation-triangle text-warning me-2 fs-6"></i>
                <div class="text-dark">${formattedContent || 'ไม่สามารถประมวลผลคำตอบได้ในขณะนี้ กรุณาลองใหม่อีกครั้งครับ'}</div>
            </div>
        `;
    } else if (!formattedContent && !extraHtml) {
        bodyHtml = `
            <div class="text-muted small py-1">
                <i class="fas fa-info-circle me-1"></i> ไม่พบข้อมูลตอบกลับ
            </div>
        `;
    } else {
        bodyHtml = `<div class="message-text mb-2 text-dark" style="white-space: pre-wrap;">${formattedContent}</div>`;
    }

    div.innerHTML = `
        <div class="me-3 flex-shrink-0">
            <img src="${smartdataLogoUrl}" class="rounded-circle bg-white p-1 shadow-sm border" style="width: 36px; height: 36px; object-fit: contain;" alt="SmartData">
        </div>
        <div class="assistant-bubble p-3 rounded-4 shadow-sm bg-white border" style="max-width: 88%; width: 100%;">
            ${bodyHtml}
            ${extraHtml}
        </div>
    `;

    container.appendChild(div);

    if (visContainerId) {
        renderVisContainer(visContainerId, data, window.lastUserQuestion || data.user_prompt || '');
    }
}

function scrollChatToBottom() {
    const container = document.getElementById('chatContainer');
    container.scrollTop = container.scrollHeight;
}

function createNewSession() {
    fetch('{{ route('ai.chat.session.new') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route('ai.chat') }}?session=' + data.session_uuid;
        }
    });
}

function switchSession(uuid) {
    window.location.href = '{{ route('ai.chat') }}?session=' + uuid;
}

function deleteSession(uuid) {
    const doDelete = function() {
        const deleteUrl = "{{ route('ai.chat.session.delete', ['uuid' => '___UUID___']) }}".replace('___UUID___', encodeURIComponent(uuid));

        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                _method: 'DELETE',
                _token: '{{ csrf_token() }}'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                if (currentSessionUuid === uuid) {
                    window.location.href = '{{ route('ai.chat') }}';
                } else {
                    const item = document.querySelector(`.session-item[data-uuid="${uuid}"]`);
                    if (item) {
                        item.remove();
                    } else {
                        window.location.href = '{{ route('ai.chat') }}';
                    }
                }
            } else {
                alert('ไม่สามารถลบการสนทนาได้: ' + (data.content || 'เกิดข้อผิดพลาด'));
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            window.location.href = '{{ route('ai.chat') }}';
        });
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'ต้องการลบประวัติการสนทนานี้?',
            text: 'ข้อมูลคำถามและผลลัพธ์ในเซสชันนี้จะถูกลบถาวร',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ลบเลย',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                doDelete();
            }
        });
    } else {
        if (confirm('ต้องการลบประวัติการสนทนานี้ใช่หรือไม่?')) {
            doDelete();
        }
    }
}

function clearAllSessions() {
    const doClearAll = function() {
        fetch('{{ route('ai.chat.session.clear_all') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                _method: 'DELETE',
                _token: '{{ csrf_token() }}'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.success) {
                window.location.href = '{{ route('ai.chat') }}';
            } else {
                alert('ไม่สามารถล้างประวัติได้: ' + (data.content || 'เกิดข้อผิดพลาด'));
            }
        })
        .catch(err => {
            console.error('Clear all error:', err);
            window.location.href = '{{ route('ai.chat') }}';
        });
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'ล้างประวัติสนทนาทั้งหมด?',
            text: 'ประวัติการสนทนาทั้งหมดของคุณจะถูกลบถาวร (ไม่มีผลกระทบต่อผู้ใช้งานท่านอื่น)',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ล้างประวัติทั้งหมด',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                doClearAll();
            }
        });
    } else {
        if (confirm('ต้องการล้างประวัติการสนทนาทั้งหมดของคุณใช่หรือไม่? (ประวัติจะถูกลบถาวร)')) {
            doClearAll();
        }
    }
}

function copySql(btn) {
    const pre = btn.closest('.sql-box').querySelector('code');
    navigator.clipboard.writeText(pre.innerText).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i> คัดลอกแล้ว';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 2000);
    });
}

// ==============================================================
// SmartData Interactive Visualization System (RiMS Copilot Style)
// ==============================================================

const COLUMN_TITLE_MAP = {
    // General / Common Aliases
    'id': 'ลำดับ',
    'count': 'จำนวน',
    'total': 'ยอดรวม',
    'total_cases': 'จำนวนคนไข้ (ราย)',
    'case_count': 'จำนวนคนไข้ (ราย)',
    'visit_count': 'จำนวนครั้งบริการ (ครั้ง)',
    'patient_count': 'จำนวนผู้ป่วย (คน)',
    'admit_count': 'จำนวน Admit (ราย)',
    'death_count': 'จำนวนผู้เสียชีวิต (ราย)',
    'refer_count': 'จำนวนส่งต่อ (ครั้ง)',
    'total_amount': 'ยอดเงินรวม (บาท)',
    'total_income': 'รายได้รวม (บาท)',
    'total_price': 'ราคารวม (บาท)',
    'sum_price': 'ยอดรวม (บาท)',
    'unitcost': 'ราคาทุน (บาท)',
    'unitprice': 'ราคาขาย (บาท)',
    'paid': 'ชำระแล้ว (บาท)',
    'remain': 'คงค้าง (บาท)',
    'balance': 'คงเหลือ (บาท)',
    'los': 'วันนอนเฉลี่ย (วัน)',
    'avg_los': 'วันนอนเฉลี่ย (วัน)',
    'cmi': 'ค่า CMI',
    'adjrw': 'น้ำหนักสัมพัทธ์ (AdjRW)',

    // HOSxP Clinical & Master Data
    'hn': 'เลข HN',
    'an': 'เลข AN',
    'vn': 'เลข VN',
    'cid': 'เลขบัตรประชาชน',
    'patient_name': 'ชื่อ-นามสกุล',
    'pttype': 'รหัสสิทธิ',
    'pttype_name': 'สิทธิการรักษา',
    'pdx': 'รหัสโรคหลัก (ICD-10)',
    'diag_name': 'ชื่อการวินิจฉัยโรค',
    'disease_name': 'ชื่อโรค',
    'clinic': 'รหัสคลินิก',
    'clinic_name': 'ชื่อคลินิก',
    'ward': 'รหัสหอผู้ป่วย',
    'ward_name': 'หอผู้ป่วย',
    'doctor': 'รหัสแพทย์',
    'doctor_name': 'ชื่อแพทย์',
    'dept_name': 'แผนก/จุดบริการ',
    'department': 'แผนก/จุดบริการ',
    'drug_name': 'ชื่อยา',
    'icode': 'รหัสรายการ (icode)',
    'item_name': 'ชื่อรายการ',
    'income': 'หมวดรายได้',
    'income_name': 'ชื่อหมวดรายได้',
    'vstdate': 'วันที่รับบริการ',
    'regdate': 'วันที่รับไว้รักษา (Admit)',
    'dchdate': 'วันที่จำหน่าย',
    'rxdate': 'วันที่สั่งยา',
    'qty': 'จำนวน',
    'units': 'หน่วยนับ',
    'bedno': 'เลขเตียง',

    // Backoffice
    'person_id': 'รหัสบุคลากร',
    'staff_name': 'ชื่อบุคลากร',
    'department_name': 'กลุ่มงาน/ฝ่าย',
    'position_name': 'ตำแหน่ง',
    'article_name': 'ชื่อครุภัณฑ์',
    'article_num': 'เลขครุภัณฑ์',
    'supplies_name': 'ชื่อพัสดุ',
    'total_qty': 'จำนวนรวม',
    'total_cost': 'มูลค่ารวม (บาท)',
    'risk_count': 'จำนวนอุบัติการณ์',
    'risk_level': 'ระดับความรุนแรง',
    'risk_program': 'โปรแกรมความเสี่ยง',
    'incident_date': 'วันที่เกิดเหตุ',

    // Period
    'vst_month': 'เดือนที่มารับบริการ',
    'month_name': 'เดือน',
    'year_name': 'ปีงบประมาณ',
    'fiscal_year': 'ปีงบประมาณ'
};

function formatColumnHeader(col) {
    if (!col) return '';
    const clean = String(col).trim();
    if (COLUMN_TITLE_MAP[clean]) return COLUMN_TITLE_MAP[clean];
    const lower = clean.toLowerCase();
    if (COLUMN_TITLE_MAP[lower]) return COLUMN_TITLE_MAP[lower];

    let label = clean
        .replace(/^total_/i, 'ยอดรวม ')
        .replace(/^sum_/i, 'ยอดรวม ')
        .replace(/^count_/i, 'จำนวน ')
        .replace(/^avg_/i, 'เฉลี่ย ')
        .replace(/_/g, ' ');
    return label;
}

function formatCellValue(val, col) {
    if (val === null || val === undefined || val === '') return '-';
    const colLower = String(col || '').toLowerCase();
    if (colLower.includes('year') || colLower.includes('code') || colLower.includes('cid') || colLower === 'id' || colLower.includes('no') || colLower.includes('phone') || colLower.includes('hn') || colLower.includes('an') || colLower.includes('vn')) {
        return escapeHtml(val);
    }
    if (typeof val === 'number' || (!isNaN(val) && !isNaN(parseFloat(val)) && isFinite(val) && String(val).trim() !== '')) {
        const num = parseFloat(val);
        if (String(val).includes('.') || colLower.includes('amount') || colLower.includes('cost') || colLower.includes('price') || colLower.includes('income') || colLower.includes('debt') || colLower.includes('balance') || colLower.includes('cmi') || colLower.includes('adjrw') || colLower.includes('rate') || colLower.includes('percent')) {
            return num.toLocaleString('th-TH', { minimumFractionDigits: (num % 1 !== 0) ? 2 : 0, maximumFractionDigits: 2 });
        }
        return num.toLocaleString('th-TH');
    }
    return escapeHtml(val);
}

function detectChartableColumns(columns, rows) {
    if (!rows || rows.length === 0 || !columns || columns.length === 0) return null;

    const numericCols = [];
    const labelCols = [];

    columns.forEach(col => {
        let numCount = 0;
        let strCount = 0;
        const sampleSize = Math.min(rows.length, 20);
        for (let i = 0; i < sampleSize; i++) {
            const val = rows[i][col];
            if (val !== null && val !== undefined && val !== '') {
                const cleaned = String(val).replace(/,/g, '').trim();
                if (!isNaN(Number(cleaned)) && isFinite(Number(cleaned))) {
                    numCount++;
                } else {
                    strCount++;
                }
            }
        }
        const isLikelyId = /(_id|^id$|code$|icode$|cid$|vn$|an$|hn$|no$|เลขที่|รหัส|เบอร์|โทร|phone|tel|year$|ปี)/i.test(col);
        if (numCount > sampleSize * 0.7 && !isLikelyId) {
            numericCols.push(col);
        } else {
            labelCols.push(col);
        }
    });

    if (numericCols.length === 0 || labelCols.length === 0) {
        return null;
    }

    return {
        labelCol: labelCols[0],
        numericCols: numericCols,
        defaultMetric: numericCols[numericCols.length - 1]
    };
}

function renderVisContainer(containerId, data, userPrompt = '') {
    const container = document.getElementById(containerId);
    if (!container) return;

    const rows = data.rows || [];
    const columns = data.columns || (rows.length > 0 ? Object.keys(rows[0]) : []);
    if (!rows || rows.length === 0 || !columns || columns.length === 0) return;

    const visId = 'vis-' + Math.random().toString(36).substring(2, 9);
    const tableId = 'table-' + visId;
    window[tableId + '_data'] = rows;

    const chartInfo = detectChartableColumns(columns, rows);
    let chartToolbarHtml = '';
    let chartPanelHtml = '';

    const promptText = (userPrompt || window.lastUserQuestion || '').toLowerCase();
    const wantsChart = Boolean(chartInfo && (/กราฟ|chart|แผนภูมิ|พล็อต|plot|สัดส่วน|เปรียบเทียบ|แนวโน้ม/i.test(promptText) || (data.content && /กราฟ|chart|แผนภูมิ/i.test(data.content))));

    if (chartInfo) {
        window['chartData_' + visId] = {
            rows: rows,
            columns: columns,
            columnLabels: data.column_labels || {},
            labelCol: chartInfo.labelCol,
            numericCols: chartInfo.numericCols,
            currentMetric: chartInfo.defaultMetric,
            currentType: 'bar_h',
            currentTop: 10,
            chartInstance: null
        };

        // Metric dropdown options
        let metricOptions = chartInfo.numericCols.map(c => {
            const lbl = (data.column_labels && data.column_labels[c]) ? data.column_labels[c] : formatColumnHeader(c);
            const isSelected = c === chartInfo.defaultMetric ? 'selected' : '';
            return `<option value="${escapeHtml(c)}" ${isSelected}>${escapeHtml(lbl)}</option>`;
        }).join('');

        const metricSelectorHtml = chartInfo.numericCols.length > 1 ? `
            <div class="d-flex align-items-center gap-1">
                <span class="text-muted small" style="font-size: 0.72rem;">ตัวชี้วัด:</span>
                <select class="form-select form-select-sm py-0 px-2 shadow-none" style="font-size: 0.75rem; width: auto; height: 26px; border-radius: 6px;" onchange="changeChartMetric('${visId}', this.value)">
                    ${metricOptions}
                </select>
            </div>
        ` : '';

        chartToolbarHtml = `
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fw-medium ${wantsChart ? '' : 'active bg-white text-primary shadow-sm'}" id="btn-tab-table-${visId}" onclick="switchVisualizationView('${visId}', 'table')">
                    <i class="bi bi-table text-primary me-1"></i> ตาราง (${(data.total_rows || rows.length).toLocaleString('th-TH')})
                </button>
                <button type="button" class="btn btn-sm btn-light border py-1 px-2.5 fw-medium ${wantsChart ? 'active bg-white text-success shadow-sm' : ''}" id="btn-tab-chart-${visId}" onclick="switchVisualizationView('${visId}', 'chart')">
                    <i class="bi bi-bar-chart-fill text-success me-1"></i> กราฟสรุป <span class="badge bg-success text-white ms-1" style="font-size: 0.6rem; padding: 2px 5px;">AI</span>
                </button>
            </div>
        `;

        chartPanelHtml = `
            <div id="panel-chart-${visId}" class="chart-view-panel ${wantsChart ? '' : 'd-none'}">
                <div class="chart-toolbar">
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <!-- Chart Type Buttons -->
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-sm btn-primary py-0 px-2 small shadow-none active" title="กราฟแท่งแนวนอน (อ่านชื่อยาวสะดวก)" onclick="changeChartType('${visId}', 'bar_h', this)">
                                <i class="bi bi-bar-chart-steps me-1"></i>แท่งแนวนอน
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 small shadow-none" title="กราฟแท่งแนวตั้ง" onclick="changeChartType('${visId}', 'bar_v', this)">
                                <i class="bi bi-bar-chart me-1"></i>แท่งแนวตั้ง
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 small shadow-none" title="กราฟวงกลม/โดนัท" onclick="changeChartType('${visId}', 'doughnut', this)">
                                <i class="bi bi-pie-chart me-1"></i>โดนัท
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 small shadow-none" title="กราฟเส้นแนวโน้ม" onclick="changeChartType('${visId}', 'line', this)">
                                <i class="bi bi-graph-up me-1"></i>เส้น
                            </button>
                        </div>
                        ${metricSelectorHtml}
                    </div>

                    <!-- Top N Selector -->
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-muted small" style="font-size: 0.72rem;">แสดง:</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 small shadow-none" onclick="changeChartTopN('${visId}', 5, this)">Top 5</button>
                            <button type="button" class="btn btn-sm btn-secondary active py-0 px-2 small shadow-none" onclick="changeChartTopN('${visId}', 10, this)">Top 10</button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 small shadow-none" onclick="changeChartTopN('${visId}', 20, this)">Top 20</button>
                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 small shadow-none" onclick="changeChartTopN('${visId}', 'all', this)">ทั้งหมด</button>
                        </div>
                    </div>
                </div>

                <!-- Canvas -->
                <div class="chart-canvas-wrapper">
                    <canvas id="canvas-${visId}"></canvas>
                </div>

                <!-- Stats summary card -->
                <div class="chart-stats-card" id="stats-${visId}"></div>
            </div>
        `;
    }

    let headers = columns.map(col => {
        const label = (data.column_labels && data.column_labels[col]) ? data.column_labels[col] : formatColumnHeader(col);
        return `<th class="text-nowrap">${escapeHtml(label)}</th>`;
    }).join('');

    let rowsHtml = rows.slice(0, 100).map(r => {
        let cells = columns.map(col => `<td>${formatCellValue(r[col], col)}</td>`).join('');
        return `<tr>${cells}</tr>`;
    }).join('');

    const targetDbName = String(data.target_db || data.db_target || 'HOSxP').toUpperCase();

    const fullHtml = `
        <div class="table-card-container">
            <div class="table-toolbar">
                <div class="d-flex align-items-center gap-2">
                    ${chartToolbarHtml || `<span class="small fw-bold text-dark"><i class="bi bi-table text-primary me-1"></i> ตาราง (${(data.total_rows || rows.length).toLocaleString('th-TH')} รายการ)</span>`}
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 small ${wantsChart ? 'd-none' : ''}" id="btn-export-excel-${visId}" onclick="exportTableToExcel('${tableId}', '${escapeHtml(targetDbName)}')">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </button>
                    ${chartInfo ? `
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 small ${wantsChart ? '' : 'd-none'}" id="btn-download-png-${visId}" onclick="downloadChartImage('${visId}')">
                        <i class="bi bi-camera me-1"></i> เซฟรูปกราฟ (PNG)
                    </button>
                    ` : ''}
                </div>
            </div>
            <div id="panel-table-${visId}" class="table-responsive-custom ${wantsChart ? 'd-none' : ''}">
                <table class="data-table-copilot" id="${tableId}">
                    <thead><tr>${headers}</tr></thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            </div>
            ${chartPanelHtml}
            <div class="d-flex justify-content-between align-items-center p-2 bg-light border-top text-muted" style="font-size: 0.72rem;">
                <span><i class="bi bi-database text-primary me-1"></i> ฐานข้อมูล: ${escapeHtml(targetDbName)} • ${rows.length.toLocaleString('th-TH')} รายการ</span>
                <span>SmartData AI Visualizer</span>
            </div>
        </div>
    `;

    container.innerHTML = fullHtml;

    if (chartInfo && wantsChart) {
        setTimeout(() => {
            renderVisualizationChart(visId);
        }, 80);
    }
}

function switchVisualizationView(visId, mode) {
    const config = window['chartData_' + visId];
    if (!config) return;

    const tableBtn = document.getElementById(`btn-tab-table-${visId}`);
    const chartBtn = document.getElementById(`btn-tab-chart-${visId}`);
    const tablePanel = document.getElementById(`panel-table-${visId}`);
    const chartPanel = document.getElementById(`panel-chart-${visId}`);
    const exportBtn = document.getElementById(`btn-export-excel-${visId}`);
    const pngBtn = document.getElementById(`btn-download-png-${visId}`);

    if (mode === 'chart') {
        if (tableBtn) tableBtn.classList.remove('active', 'bg-white', 'text-primary', 'shadow-sm');
        if (chartBtn) chartBtn.classList.add('active', 'bg-white', 'text-success', 'shadow-sm');
        if (tablePanel) tablePanel.classList.add('d-none');
        if (chartPanel) chartPanel.classList.remove('d-none');
        if (exportBtn) exportBtn.classList.add('d-none');
        if (pngBtn) pngBtn.classList.remove('d-none');

        setTimeout(() => { renderVisualizationChart(visId); }, 50);
    } else {
        if (tableBtn) tableBtn.classList.add('active', 'bg-white', 'text-primary', 'shadow-sm');
        if (chartBtn) chartBtn.classList.remove('active', 'bg-white', 'text-success', 'shadow-sm');
        if (tablePanel) tablePanel.classList.remove('d-none');
        if (chartPanel) chartPanel.classList.add('d-none');
        if (exportBtn) exportBtn.classList.remove('d-none');
        if (pngBtn) pngBtn.classList.add('d-none');
    }
}

function changeChartType(visId, type, btn) {
    const config = window['chartData_' + visId];
    if (!config) return;
    config.currentType = type;

    const parent = btn.closest('.btn-group');
    if (parent) {
        parent.querySelectorAll('button').forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-light');
        });
        btn.classList.add('active', 'btn-primary');
        btn.classList.remove('btn-light');
    }

    renderVisualizationChart(visId);
}

function changeChartMetric(visId, metric) {
    const config = window['chartData_' + visId];
    if (!config) return;
    config.currentMetric = metric;
    renderVisualizationChart(visId);
}

function changeChartTopN(visId, topN, btn) {
    const config = window['chartData_' + visId];
    if (!config) return;
    config.currentTop = topN;

    const parent = btn.closest('.btn-group');
    if (parent) {
        parent.querySelectorAll('button').forEach(b => {
            b.classList.remove('active', 'btn-secondary');
            b.classList.add('btn-light');
        });
        btn.classList.add('active', 'btn-secondary');
        btn.classList.remove('btn-light');
    }

    renderVisualizationChart(visId);
}

function downloadChartImage(visId) {
    const canvas = document.getElementById(`canvas-${visId}`);
    if (!canvas) {
        if (typeof Swal !== 'undefined') Swal.fire('แจ้งเตือน', 'ไม่พบกราฟสำหรับบันทึกรูปภาพ', 'info');
        else alert('ไม่พบกราฟสำหรับบันทึกรูปภาพ');
        return;
    }
    try {
        const a = document.createElement('a');
        a.href = canvas.toDataURL('image/png');
        a.download = `ดองกี้_AI_Chart_${new Date().toISOString().slice(0,10)}.png`;
        a.click();
    } catch (e) {
        console.error(e);
        if (typeof Swal !== 'undefined') Swal.fire('ข้อผิดพลาด', 'ไม่สามารถบันทึกรูปกราฟได้: ' + e, 'error');
        else alert('ไม่สามารถบันทึกรูปกราฟได้: ' + e);
    }
}

function renderVisualizationChart(visId) {
    const config = window['chartData_' + visId];
    if (!config || typeof Chart === 'undefined') return;

    const canvas = document.getElementById(`canvas-${visId}`);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (config.chartInstance) {
        config.chartInstance.destroy();
        config.chartInstance = null;
    }

    const { rows, labelCol, currentMetric, currentType, currentTop, columnLabels } = config;
    const metricLabel = (columnLabels && columnLabels[currentMetric]) ? columnLabels[currentMetric] : formatColumnHeader(currentMetric);

    // Sort rows descending by currentMetric
    const sorted = rows.slice().sort((a, b) => {
        const valA = parseFloat(String(a[currentMetric]).replace(/,/g, '')) || 0;
        const valB = parseFloat(String(b[currentMetric]).replace(/,/g, '')) || 0;
        return valB - valA;
    });

    const limit = currentTop === 'all' ? sorted.length : parseInt(currentTop, 10);
    const sliced = sorted.slice(0, limit);

    const labels = sliced.map(r => {
        const raw = String(r[labelCol] || '-');
        return raw.length > 30 ? raw.substring(0, 28) + '...' : raw;
    });
    const values = sliced.map(r => parseFloat(String(r[currentMetric]).replace(/,/g, '')) || 0);

    // Summary statistics
    const allValues = sorted.map(r => parseFloat(String(r[currentMetric]).replace(/,/g, '')) || 0);
    const totalSum = allValues.reduce((acc, v) => acc + v, 0);
    const topItem = sorted.length > 0 ? sorted[0] : null;
    const topItemLabel = topItem ? (topItem[labelCol] || '-') : '-';
    const topItemVal = topItem ? (parseFloat(String(topItem[currentMetric]).replace(/,/g, '')) || 0) : 0;
    const avgVal = allValues.length > 0 ? (totalSum / allValues.length) : 0;

    const statsContainer = document.getElementById(`stats-${visId}`);
    if (statsContainer) {
        const isBaht = /amount|ยอด|บาท|เงิน|หนี้|จ่าย|price|cost|income/i.test(currentMetric) || /amount|ยอด|บาท|เงิน|หนี้|จ่าย|ราคา|รายได้/i.test(metricLabel);
        const isPerson = /คน|ผู้ป่วย|staff|person|patient/i.test(currentMetric) || /คน|ผู้ป่วย|บุคลากร/i.test(metricLabel);
        const unit = isBaht ? ' บาท' : (isPerson ? ' คน' : ' รายการ');

        statsContainer.innerHTML = `
            <div class="chart-stat-item">
                <i class="bi bi-calculator text-primary"></i>
                <span>ยอดรวม (${allValues.length.toLocaleString('th-TH')} รายการ): <strong>${totalSum.toLocaleString('th-TH', { maximumFractionDigits: 2 })}${unit}</strong></span>
            </div>
            <div class="chart-stat-item">
                <i class="bi bi-trophy text-warning"></i>
                <span>อันดับ 1: <strong>${escapeHtml(topItemLabel)}</strong> (${topItemVal.toLocaleString('th-TH', { maximumFractionDigits: 2 })}${unit})</span>
            </div>
            <div class="chart-stat-item">
                <i class="bi bi-graph-up-arrow text-success"></i>
                <span>ค่าเฉลี่ย: <strong>${avgVal.toLocaleString('th-TH', { maximumFractionDigits: 2 })}${unit}</strong></span>
            </div>
        `;
    }

    // Modern Vibrant Color Palette
    const colors = [
        '#0d6efd', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#6366f1',
        '#84cc16', '#a855f7', '#0ea5e9', '#e11d48', '#d97706'
    ];

    let chartJsType = 'bar';
    let indexAxis = 'y'; // Default horizontal bar

    if (currentType === 'bar_v') {
        chartJsType = 'bar';
        indexAxis = 'x';
    } else if (currentType === 'bar_h' || currentType === 'bar') {
        chartJsType = 'bar';
        indexAxis = 'y';
    } else if (currentType === 'doughnut') {
        chartJsType = 'doughnut';
    } else if (currentType === 'line') {
        chartJsType = 'line';
    }

    const isBar = chartJsType === 'bar';
    const isDoughnut = chartJsType === 'doughnut';
    const isLine = chartJsType === 'line';

    const datasetBg = isDoughnut
        ? colors.slice(0, sliced.length)
        : (isLine ? 'rgba(13, 110, 253, 0.12)' : (isBar && indexAxis === 'y' ? colors.slice(0, sliced.length) : '#0d6efd'));

    const datasetBorder = isDoughnut
        ? '#ffffff'
        : (isLine ? '#0d6efd' : (isBar && indexAxis === 'y' ? colors.slice(0, sliced.length) : '#0b5ed7'));

    config.chartInstance = new Chart(ctx, {
        type: chartJsType,
        data: {
            labels: labels,
            datasets: [{
                label: metricLabel,
                data: values,
                backgroundColor: datasetBg,
                borderColor: datasetBorder,
                borderWidth: isDoughnut ? 2 : (isLine ? 3 : 1),
                borderRadius: isBar ? 6 : 0,
                fill: isLine,
                tension: isLine ? 0.35 : 0,
                pointBackgroundColor: isLine ? '#0d6efd' : undefined,
                pointRadius: isLine ? 5 : undefined
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: isBar ? indexAxis : undefined,
            plugins: {
                legend: {
                    display: isDoughnut,
                    position: 'right',
                    labels: {
                        boxWidth: 14,
                        font: { family: "'Nunito', 'Prompt', sans-serif", size: 11 }
                    }
                },
                tooltip: {
                    titleFont: { family: "'Nunito', 'Prompt', sans-serif", size: 12 },
                    bodyFont: { family: "'Nunito', 'Prompt', sans-serif", size: 12 },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            const val = context.parsed ? (isBar && indexAxis === 'y' ? context.parsed.x : (isDoughnut ? context.raw : context.parsed.y)) : context.raw;
                            return label + Number(val).toLocaleString('th-TH', { maximumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: isDoughnut ? {} : {
                x: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { family: "'Nunito', 'Prompt', sans-serif", size: 10 },
                        callback: function(val) {
                            if (isBar && indexAxis === 'y') {
                                if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
                            }
                            return this.getLabelForValue ? this.getLabelForValue(val) : val;
                        }
                    }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { family: "'Nunito', 'Prompt', sans-serif", size: 10 },
                        callback: function(val) {
                            if ((isBar && indexAxis === 'x') || isLine) {
                                if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                                if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
                            }
                            return this.getLabelForValue ? this.getLabelForValue(val) : val;
                        }
                    }
                }
            }
        }
    });
}

function exportTableToExcel(tableId, targetName) {
    const table = document.getElementById(tableId);
    if (!table) {
        if (typeof Swal !== 'undefined') Swal.fire('แจ้งเตือน', 'ไม่พบข้อมูลสำหรับส่งออก', 'info');
        else alert('ไม่พบข้อมูลสำหรับส่งออก');
        return;
    }
    try {
        if (typeof XLSX !== 'undefined') {
            const ws = XLSX.utils.table_to_sheet(table);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Result");
            const filename = `SmartData_AI_${targetName}_${new Date().toISOString().slice(0,10)}.xlsx`;
            XLSX.writeFile(wb, filename);
        } else {
            exportTableFallbackCsv(table, targetName);
        }
    } catch (err) {
        console.error(err);
        if (typeof Swal !== 'undefined') Swal.fire('ข้อผิดพลาด', 'ไม่สามารถส่งออกไฟล์ได้: ' + err, 'error');
        else alert('ไม่สามารถส่งออกไฟล์ได้: ' + err);
    }
}

function exportTableFallbackCsv(table, targetName) {
    let csv = [];
    const rows = table.querySelectorAll('tr');
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        csv.push(row.join(','));
    }

    const csvFile = new Blob(["\uFEFF" + csv.join('\n')], {type: "text/csv;charset=utf-8;"});
    const downloadLink = document.createElement("a");
    downloadLink.download = `SmartData_AI_${targetName}_${new Date().toISOString().slice(0,10)}.csv`;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function exportTableToCsv(btn) {
    const table = btn.closest('.assistant-bubble').querySelector('table');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        csv.push(row.join(','));
    }

    const csvFile = new Blob(["\uFEFF" + csv.join('\n')], {type: "text/csv;charset=utf-8;"});
    const downloadLink = document.createElement("a");
    downloadLink.download = "smartdata_query_result.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
@endpush
@endsection
