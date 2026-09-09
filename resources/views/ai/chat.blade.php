@extends('layouts.app')

@section('title', 'SmartData Copilot - AI Hospital Assistant')

@section('content')
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
                    <div class="session-item p-2 rounded-3 mb-1 d-flex align-items-center justify-content-between cursor-pointer {{ ($currentSession && $currentSession->session_uuid === $s->session_uuid) ? 'bg-primary-subtle text-primary fw-bold' : 'text-dark hover-bg-light' }}" onclick="switchSession('{{ $s->session_uuid }}')">
                        <div class="text-truncate flex-grow-1 me-2 small" title="{{ $s->title }}">
                            <i class="far fa-comment-dots me-1 text-secondary"></i> {{ $s->title }}
                        </div>
                        <button type="button" class="btn btn-link btn-sm text-muted p-0 delete-session-btn" onclick="event.stopPropagation(); deleteSession('{{ $s->session_uuid }}')" title="ลบ">
                            <i class="fas fa-times small"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-4 text-muted small">
                        ยังไม่มีประวัติการสนทนา
                    </div>
                    @endforelse
                </div>

                <!-- Bottom Links -->
                <div class="p-3 border-top bg-light-subtle">
                    <a href="{{ route('ai.knowledge.index') }}" class="d-flex align-items-center text-decoration-none text-muted small mb-2">
                        <i class="fas fa-book-medical me-2 text-info"></i> คลังความรู้โรงพยาบาล
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.ai.settings') }}" class="d-flex align-items-center text-decoration-none text-muted small">
                        <i class="fas fa-sliders-h me-2 text-primary"></i> ตั้งค่า AI Engine
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-9 col-lg-10 h-100 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column overflow-hidden bg-white">
                <!-- Chat Header Toolbar -->
                <div class="px-4 py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center bg-white gap-2">
                    <div class="d-flex align-items-center">
                        <div class="bg-gradient-primary rounded-circle p-2 text-white me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
                            <i class="fas fa-robot fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">SmartData Copilot</h5>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0 small">
                                <i class="fas fa-circle fa-2xs me-1"></i> Online พร้อมใช้งาน
                            </span>
                        </div>
                    </div>

                    <!-- Mode Selector Pills -->
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="btn-group btn-group-sm rounded-pill p-1 bg-light shadow-sm" role="group">
                            <input type="radio" class="btn-check" name="chat_mode" id="mode_smart" value="smart" checked>
                            <label class="btn btn-sm rounded-pill px-3" for="mode_smart">
                                <i class="fas fa-magic me-1"></i> อัจฉริยะ (Auto)
                            </label>

                            <input type="radio" class="btn-check" name="chat_mode" id="mode_sql" value="sql">
                            <label class="btn btn-sm rounded-pill px-3" for="mode_sql">
                                <i class="fas fa-database me-1"></i> Text-to-SQL
                            </label>

                            <input type="radio" class="btn-check" name="chat_mode" id="mode_rag" value="rag">
                            <label class="btn btn-sm rounded-pill px-3" for="mode_rag">
                                <i class="fas fa-book-reader me-1"></i> คลังความรู้ (RAG)
                            </label>

                            <input type="radio" class="btn-check" name="chat_mode" id="mode_general" value="general">
                            <label class="btn btn-sm rounded-pill px-3" for="mode_general">
                                <i class="fas fa-comments me-1"></i> คุยทั่วไป
                            </label>
                        </div>

                        <!-- Target Database Selector (for SQL/Smart mode) -->
                        <div class="d-flex align-items-center" id="targetDbWrapper">
                            <select id="target_db" class="form-select form-select-sm rounded-pill bg-light border-0 shadow-sm" style="width: auto;">
                                <option value="auto">🤖 ตรวจหา DB อัตโนมัติ</option>
                                <option value="hosxp" selected>🏥 HOSxP (เวชระเบียน/คนไข้)</option>
                                <option value="backoffice">🏢 Backoffice (บริหาร/พัสดุ/บุคคล)</option>
                                <option value="mysql">⚙️ SmartData (ระบบภายใน)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="flex-grow-1 overflow-auto p-4" id="chatContainer" style="background: #f8fafc;">
                    @if(!$currentSession || $currentSession->messages->isEmpty())
                    <!-- Welcome Hero -->
                    <div id="welcomeHero" class="text-center py-5 my-auto">
                        <div class="mb-4">
                            <div class="d-inline-flex p-4 rounded-circle shadow-sm" style="background: linear-gradient(135deg, rgba(13,110,253,0.1), rgba(13,202,240,0.15));">
                                <i class="fas fa-robot fa-4x text-primary"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold text-dark mb-2">ยินดีต้อนรับสู่ SmartData Copilot</h3>
                        <p class="text-muted mb-4 mx-auto" style="max-width: 550px;">
                            ผู้ช่วย AI วิเคราะห์ข้อมูลสุขภาพ แปลงภาษาไทยเป็น SQL ค้นหาสถิติคนไข้ HOSxP, ข้อมูล Backoffice และตอบคำถามจากคลังความรู้ CPG โรงพยาบาล
                        </p>

                        <div class="text-muted small fw-bold mb-3 text-uppercase">ตัวอย่างคำถามที่สามารถคลิกถามได้ทันที:</div>
                        <div class="d-flex flex-wrap justify-content-center gap-2 mx-auto" style="max-width: 750px;">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 bg-white shadow-sm" onclick="sendQuickPrompt('ขอยอดผู้ป่วยนอก (OPD) วันนี้แยกตามสิทธิการรักษา')">
                                <i class="fas fa-chart-pie me-1"></i> ยอดผู้ป่วยนอกวันนี้แยกตามสิทธิ
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 bg-white shadow-sm" onclick="sendQuickPrompt('5 อันดับโรคผู้ป่วยนอก (Top 5 OPD Dx) ที่มารับบริการมากที่สุดเดือนนี้')">
                                <i class="fas fa-stethoscope me-1"></i> 5 อันดับโรคผู้ป่วยนอกเดือนนี้
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 bg-white shadow-sm" onclick="sendQuickPrompt('ขอรายชื่อเจ้าหน้าที่กลุ่มงานสารสนเทศทางการแพทย์ จาก Backoffice', 'backoffice')">
                                <i class="fas fa-users me-1"></i> รายชื่อเจ้าหน้าที่กลุ่มงานไอที (Backoffice)
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
                                <div class="rounded-circle bg-light p-2 shadow-sm d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px;">
                                    <i class="fas fa-robot"></i>
                                </div>
                            </div>
                            <div class="assistant-bubble p-3 rounded-4 shadow-sm bg-white border" style="max-width: 85%;">
                                <div class="message-text mb-2" style="white-space: pre-wrap;">{!! nl2br(e($msg->content)) !!}</div>

                                @if($msg->message_type === 'sql_query' && $msg->generated_sql)
                                <div class="sql-box rounded-3 p-3 bg-dark text-light mb-3 mt-2">
                                    <div class="d-flex justify-content-between align-items-center mb-2 border-bottom border-secondary pb-1">
                                        <span class="badge bg-primary text-white"><i class="fas fa-database me-1"></i> {{ strtoupper($msg->target_db ?: 'HOSxP') }}</span>
                                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" onclick="copySql(this)">
                                            <i class="fas fa-copy me-1"></i> Copy SQL
                                        </button>
                                    </div>
                                    <pre class="mb-0 font-monospace small text-info" style="white-space: pre-wrap;"><code>{{ $msg->generated_sql }}</code></pre>
                                </div>

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
                                            @foreach($msg->query_result as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                <td>{{ is_numeric($cell) ? number_format($cell) : $cell }}</td>
                                                @endforeach
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="exportTableToCsv(this)">
                                        <i class="fas fa-file-excel me-1"></i> ส่งออก CSV
                                    </button>
                                </div>
                                @endif
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
                    <form id="chatForm" onsubmit="handleChatSubmit(event)">
                        <div class="input-group shadow-sm rounded-4 overflow-hidden border">
                            <textarea id="messageInput" class="form-control border-0 py-3 px-4 bg-light" rows="1" placeholder="พิมพ์คำถามภาษาไทย เช่น ขอยอดผู้ป่วยนอกวันนี้, รายชื่อเจ้าหน้าที่, หรือแนวทาง CPG... (กด Enter เพื่อส่ง, Shift+Enter ขึ้นบรรทัดใหม่)" style="resize: none;"></textarea>
                            <button type="submit" id="sendBtn" class="btn btn-primary px-4 border-0 d-flex align-items-center justify-content-center">
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
</style>
@endpush

@push('scripts')
<script>
let currentSessionUuid = '{{ $currentSession->session_uuid ?? "" }}';

document.getElementById('messageInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
});

// Auto-expand textarea
document.getElementById('messageInput').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

function handleChatSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (!message) return;

    input.value = '';
    input.style.height = 'auto';

    // Remove welcome hero if present
    const hero = document.getElementById('welcomeHero');
    if (hero) hero.remove();

    appendUserMessage(message);

    const mode = document.querySelector('input[name="chat_mode"]:checked').value;
    const targetDb = document.getElementById('target_db').value;

    const loadingBubble = appendLoadingBubble();
    scrollChatToBottom();

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
    .then(res => res.json())
    .then(data => {
        loadingBubble.remove();
        appendAssistantMessage(data);
        scrollChatToBottom();
    })
    .catch(err => {
        loadingBubble.remove();
        appendAssistantMessage({
            success: false,
            content: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + err.message
        });
        scrollChatToBottom();
    });
}

function sendQuickPrompt(promptText, targetDbChoice = null) {
    if (targetDbChoice) {
        document.getElementById('target_db').value = targetDbChoice;
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
            <div class="rounded-circle bg-light p-2 shadow-sm d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px;">
                <i class="fas fa-robot fa-spin"></i>
            </div>
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
    if (data.mode === 'sql' && data.sql) {
        extraHtml += `
            <div class="sql-box rounded-3 p-3 bg-dark text-light mb-3 mt-2">
                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom border-secondary pb-1">
                    <span class="badge bg-primary text-white"><i class="fas fa-database me-1"></i> ${(data.target_db || 'HOSxP').toUpperCase()}</span>
                    <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" onclick="copySql(this)">
                        <i class="fas fa-copy me-1"></i> Copy SQL
                    </button>
                </div>
                <pre class="mb-0 font-monospace small text-info" style="white-space: pre-wrap;"><code>${escapeHtml(data.sql)}</code></pre>
            </div>
        `;

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
                <div class="text-end mt-2">
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="exportTableToCsv(this)">
                        <i class="fas fa-file-excel me-1"></i> ส่งออก CSV
                    </button>
                </div>
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

    div.innerHTML = `
        <div class="me-3">
            <div class="rounded-circle bg-light p-2 shadow-sm d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px;">
                <i class="fas fa-robot"></i>
            </div>
        </div>
        <div class="assistant-bubble p-3 rounded-4 shadow-sm bg-white border" style="max-width: 85%;">
            <div class="message-text mb-2" style="white-space: pre-wrap;">${escapeHtml(data.content || '')}</div>
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
    if (!confirm('ต้องการลบประวัติการสนทนานี้ใช่หรือไม่?')) return;

    fetch('{{ url('/ai/chat/session') }}/' + uuid, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '{{ route('ai.chat') }}';
        }
    });
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
