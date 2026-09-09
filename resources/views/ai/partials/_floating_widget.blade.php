<!-- SmartData Copilot Floating Widget -->
<div id="smartdata-copilot-widget" style="position: fixed; bottom: 25px; right: 25px; z-index: 1060; font-family: inherit;">
    <!-- Floating Trigger Button -->
    <button id="copilot-trigger-btn" type="button" class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0" style="width: 60px; height: 60px; background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%); border: 3px solid #ffffff; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);" onclick="toggleCopilotWidget()" title="ถาม SmartData Copilot">
        <i class="fas fa-robot fa-2x text-white" id="copilot-btn-icon"></i>
    </button>

    <!-- Floating Chat Window (Drawer) -->
    <div id="copilot-chat-window" class="card border-0 shadow-2xl rounded-4 overflow-hidden" style="display: none; position: absolute; bottom: 75px; right: 0; width: 380px; max-width: calc(100vw - 35px); height: 530px; max-height: calc(100vh - 120px); z-index: 1061; flex-direction: column;">
        <!-- Header -->
        <div class="card-header text-white border-0 py-3 px-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0d6efd 0%, #0250c5 100%);">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-white text-primary p-2 me-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-white leading-tight">SmartData Copilot</h6>
                    <small class="text-white-50" style="font-size: 0.7rem;">ผู้ช่วยอัจฉริยะ (SQL & CPG)</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('ai.knowledge.index') }}" class="text-white text-opacity-75 hover-opacity-100 text-decoration-none" title="เปิดคลังความรู้ CPG & ระเบียบ">
                    <i class="fas fa-book-medical small"></i>
                </a>
                <a href="{{ route('ai.chat') }}" class="text-white text-opacity-75 hover-opacity-100 text-decoration-none" title="เปิดหน้าจอเต็ม">
                    <i class="fas fa-external-link-alt small"></i>
                </a>
                <button type="button" class="btn-close btn-close-white p-1" style="font-size: 0.75rem;" onclick="toggleCopilotWidget()" aria-label="Close"></button>
            </div>
        </div>

        <!-- Hidden Auto Target DB -->
        <input type="hidden" id="widget_target_db" value="auto">

        <!-- Message Body -->
        <div class="card-body p-3 overflow-auto flex-grow-1" id="widgetChatContainer" style="background: #f8fafc; font-size: 0.85rem;">
            <div class="d-flex justify-content-start mb-3">
                <div class="rounded-circle bg-light p-1 shadow-sm d-flex align-items-center justify-content-center text-primary me-2" style="width: 28px; height: 28px;">
                    <i class="fas fa-robot small"></i>
                </div>
                <div class="p-2 rounded-3 shadow-sm bg-white border" style="max-width: 85%;">
                    สวัสดีครับ! ผมคือ <strong>SmartData Copilot</strong> ถามสถิติคนไข้, แปลง SQL, หรือค้นหาแนวทาง CPG โรงพยาบาลได้เลยครับ
                </div>
            </div>

            <!-- Quick Chips -->
            <div id="widgetQuickChips" class="mb-3">
                <div class="text-muted small mb-1" style="font-size: 0.7rem;">คำถามแนะนำ:</div>
                <div class="d-flex flex-wrap gap-1">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill py-0 px-2 text-start" style="font-size: 0.75rem;" onclick="sendWidgetQuickPrompt('ขอยอดผู้ป่วยนอกวันนี้แยกตามสิทธิ')">
                        ยอด OPD วันนี้
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm rounded-pill py-0 px-2 text-start" style="font-size: 0.75rem;" onclick="sendWidgetQuickPrompt('แนวทางรักษาผู้ป่วย Stroke')">
                        CPG Stroke
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Input -->
        <div class="card-footer p-2 bg-white border-top">
            <form id="widgetChatForm" onsubmit="handleWidgetSubmit(event)">
                <div class="input-group">
                    <input type="text" id="widgetMessageInput" class="form-control form-control-sm border-0 bg-light px-3" placeholder="พิมพ์คำถามที่นี่..." autocomplete="off">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
#copilot-trigger-btn:hover {
    transform: scale(1.08);
}
@keyframes copilotPulse {
    0% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.5); }
    70% { box-shadow: 0 0 0 15px rgba(13, 110, 253, 0); }
    100% { box-shadow: 0 0 0 0 rgba(13, 110, 253, 0); }
}
#copilot-trigger-btn {
    animation: copilotPulse 3s infinite;
}
</style>

<script>
let widgetSessionUuid = 'widget-' + Math.random().toString(36).substr(2, 9);
let isWidgetOpen = false;

function toggleCopilotWidget() {
    const chatWindow = document.getElementById('copilot-chat-window');
    const icon = document.getElementById('copilot-btn-icon');
    isWidgetOpen = !isWidgetOpen;

    if (isWidgetOpen) {
        chatWindow.style.display = 'flex';
        icon.className = 'fas fa-times fa-2x text-white';
        document.getElementById('widgetMessageInput').focus();
    } else {
        chatWindow.style.display = 'none';
        icon.className = 'fas fa-robot fa-2x text-white';
    }
}

function handleWidgetSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('widgetMessageInput');
    const text = input.value.trim();
    if (!text) return;

    input.value = '';

    // Hide quick chips
    const chips = document.getElementById('widgetQuickChips');
    if (chips) chips.style.display = 'none';

    // Append user bubble
    appendWidgetMessage('user', text);

    // Append loading
    const loading = appendWidgetLoading();

    const targetDb = document.getElementById('widget_target_db').value;

    fetch('{{ route('ai.chat.message') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            message: text,
            session_uuid: widgetSessionUuid,
            mode: 'smart',
            target_db: targetDb
        })
    })
    .then(res => res.json())
    .then(data => {
        loading.remove();
        let reply = data.content || '';
        if (data.mode === 'sql' && data.count !== undefined) {
            reply += `\n(ดึงข้อมูลสำเร็จ ${data.count} รายการ จากฐานข้อมูล ${data.target_db || 'HOSxP'})`;
        }
        appendWidgetMessage('assistant', reply);
    })
    .catch(err => {
        loading.remove();
        appendWidgetMessage('assistant', 'ขออภัย เกิดข้อผิดพลาด: ' + err.message);
    });
}

function sendWidgetQuickPrompt(text) {
    document.getElementById('widgetMessageInput').value = text;
    document.getElementById('widgetChatForm').dispatchEvent(new Event('submit'));
}

function appendWidgetMessage(role, text) {
    const container = document.getElementById('widgetChatContainer');
    const div = document.createElement('div');

    if (role === 'user') {
        div.className = 'd-flex justify-content-end mb-2';
        div.innerHTML = `
            <div class="p-2 rounded-3 text-white shadow-sm" style="max-width: 85%; background: #0d6efd; white-space: pre-wrap;">
                ${escapeHtmlWidget(text)}
            </div>
        `;
    } else {
        div.className = 'd-flex justify-content-start mb-2';
        div.innerHTML = `
            <div class="rounded-circle bg-light p-1 shadow-sm d-flex align-items-center justify-content-center text-primary me-2 flex-shrink-0" style="width: 28px; height: 28px;">
                <i class="fas fa-robot small"></i>
            </div>
            <div class="p-2 rounded-3 shadow-sm bg-white border text-dark" style="max-width: 85%; white-space: pre-wrap;">
                ${escapeHtmlWidget(text)}
            </div>
        `;
    }

    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function appendWidgetLoading() {
    const container = document.getElementById('widgetChatContainer');
    const div = document.createElement('div');
    div.className = 'd-flex justify-content-start mb-2';
    div.innerHTML = `
        <div class="rounded-circle bg-light p-1 shadow-sm d-flex align-items-center justify-content-center text-primary me-2" style="width: 28px; height: 28px;">
            <i class="fas fa-robot fa-spin small"></i>
        </div>
        <div class="p-2 rounded-3 shadow-sm bg-white border text-muted small">
            กำลังประมวลผล...
        </div>
    `;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    return div;
}

function escapeHtmlWidget(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
