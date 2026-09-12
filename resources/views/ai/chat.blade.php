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
                        <div class="d-flex flex-wrap justify-content-center gap-2 mx-auto" style="max-width: 800px;">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 bg-white shadow-sm" onclick="sendQuickPrompt('ขอยอดผู้ป่วยนอก (OPD) ย้อนหลัง 30 วัน แยกตามสิทธิการรักษา')">
                                <i class="fas fa-chart-pie me-1"></i> ยอดผู้ป่วยนอกแยกตามสิทธิ (30 วัน)
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
                                <div class="table-responsive rounded-3 border bg-light mt-2" style="max-height: 350px;">
                                    <table class="table table-sm table-striped table-hover mb-0 small">
                                        <thead class="table-primary sticky-top">
                                            <tr>
                                                @foreach(array_keys($msg->query_result[0] ?? []) as $col)
                                                <th>{{ $col }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $isCodeOrId = function($name) {
                                                    return (bool) preg_match('/(hn|an|vn|cid|pid|code|icode|tmt|billcode|adp|idcard|phone|tel|year|bed|ward|dept|clinic|เลข|รหัส|ปี|เบอร์|โทร|เตียง|ลำดับ)/i', $name);
                                                };
                                                $isMoneyOrQty = function($name) {
                                                    return (bool) preg_match('/(จำนวน|ราคา|ยอด|บาท|มูลค่า|ผลรวม|จ่าย|ค้าง|ต้นทุน|count|qty|amount|price|cost|total|sum|adjrw|cmi)/i', $name);
                                                };
                                            @endphp
                                            @foreach($msg->query_result as $row)
                                            <tr>
                                                @foreach($row as $colName => $cell)
                                                @php
                                                    $displayCell = $cell;
                                                    if (is_numeric($cell) && !$isCodeOrId($colName) && $isMoneyOrQty($colName)) {
                                                        $displayCell = is_float($cell + 0) ? number_format($cell, 2) : number_format($cell);
                                                    }
                                                @endphp
                                                <td>{{ $displayCell }}</td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                                    <span class="badge bg-light text-muted border">
                                        <i class="fas fa-database text-primary me-1"></i> {{ strtoupper($msg->target_db ?: 'HOSxP') }} • {{ count($msg->query_result) }} รายการ
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="exportTableToCsv(this)">
                                        <i class="fas fa-file-excel me-1"></i> ส่งออก CSV
                                    </button>
                                </div>
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
</style>
@endpush

@push('scripts')
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

    // If SQL query result
    if (data.mode === 'sql') {
        if (data.rows && data.rows.length > 0) {
            let tableHeaders = '';
            data.columns.forEach(col => {
                tableHeaders += `<th>${escapeHtml(col)}</th>`;
            });

            let tableRows = '';
            data.rows.forEach(row => {
                tableRows += '<tr>';
                data.columns.forEach(col => {
                    const val = row[col];
                    tableRows += `<td>${escapeHtml(String(val !== null ? val : ''))}</td>`;
                });
                tableRows += '</tr>';
            });

            extraHtml += `
                <div class="table-responsive rounded-3 border bg-light mt-2" style="max-height: 350px;">
                    <table class="table table-sm table-striped table-hover mb-0 small">
                        <thead class="table-primary sticky-top">
                            <tr>${tableHeaders}</tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                    <span class="badge bg-light text-muted border">
                        <i class="fas fa-database text-primary me-1"></i> ${(data.target_db || 'HOSxP').toUpperCase()} • ${data.count || data.rows.length} รายการ
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="exportTableToCsv(this)">
                        <i class="fas fa-file-excel me-1"></i> ส่งออก CSV
                    </button>
                </div>
            `;
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
        <div class="assistant-bubble p-3 rounded-4 shadow-sm bg-white border" style="max-width: 85%;">
            ${bodyHtml}
            ${extraHtml}
        </div>
    `;

    container.appendChild(div);
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
