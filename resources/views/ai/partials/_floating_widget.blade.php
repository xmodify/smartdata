<!-- SmartData Copilot Floating Widget (RiMS Style) -->
<div id="smartdata-copilot-widget" style="position: fixed; bottom: 25px; right: 25px; z-index: 9999; font-family: inherit;">
    <!-- Floating Trigger Button with AI Badge -->
    <div class="position-relative d-inline-block">
        <button id="copilot-trigger-btn" type="button" class="btn rounded-circle shadow-lg d-flex align-items-center justify-content-center p-0" style="width: 60px; height: 60px; background: #ffffff; border: 3px solid #ffffff; box-shadow: 0 6px 24px rgba(9, 74, 136, 0.4); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); overflow: hidden; cursor: pointer;" onclick="toggleCopilotWidget()" title="SmartData Copilot">
            <img src="{{ asset('images/logo.png') }}" id="copilot-btn-img" alt="SmartData Copilot" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; pointer-events: none;">
            <i class="fas fa-times fa-2x text-white d-none" id="copilot-btn-close" style="pointer-events: none;"></i>
        </button>
        <!-- Red AI Pill Badge (RiMS Style) -->
        <span class="badge rounded-pill bg-danger position-absolute" style="top: -3px; right: -3px; font-size: 0.65rem; font-weight: 800; padding: 3px 6px; box-shadow: 0 2px 6px rgba(220, 53, 69, 0.5); border: 2px solid #ffffff; pointer-events: none; letter-spacing: 0.5px;">AI</span>
    </div>

    <!-- Floating Chat Window (Drawer - RiMS Style) -->
    <div id="copilot-chat-window" class="card border-0 rounded-4 overflow-hidden" style="display: none; position: absolute; bottom: 75px; right: 0; width: 400px; max-width: calc(100vw - 30px); height: 590px; max-height: calc(100vh - 105px); z-index: 10000; flex-direction: column; box-shadow: 0 16px 48px rgba(0, 0, 0, 0.22), 0 0 0 1px rgba(0,0,0,0.05); border-radius: 20px !important;">
        <!-- Header: Deep Hospital Navy Blue -->
        <div class="card-header text-white border-0 py-3 px-3 d-flex justify-content-between align-items-center" style="background: #094a88;">
            <div class="d-flex align-items-center">
                <!-- Avatar with Online Glowing Dot -->
                <div class="position-relative me-2 flex-shrink-0" style="width: 38px; height: 38px;">
                    <img src="{{ asset('images/logo.png') }}" class="rounded-circle bg-white p-1 shadow-sm w-100 h-100" style="object-fit: contain;" alt="SmartData">
                    <span style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; background-color: #22c55e; border: 2px solid #094a88; border-radius: 50%; box-shadow: 0 0 6px #22c55e;"></span>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-white" style="font-size: 0.98rem; letter-spacing: 0.3px;">SmartData Copilot</h6>
                    <div class="text-white-50" style="font-size: 0.72rem; line-height: 1.25;">ผู้ช่วย AI: วิเคราะห์เวชระเบียน HOSxP • SQL • CPG คู่มือ สธ.</div>
                </div>
            </div>
            <!-- Header Actions: Trash, Settings, Expand, Close -->
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-link text-white-50 p-1 text-decoration-none hover-white" onclick="clearWidgetChat()" title="ล้างการสนทนา">
                    <i class="far fa-trash-alt small"></i>
                </button>
                @if(auth()->check() && auth()->user()->role === 'admin')
                <a href="{{ route('admin.ai.settings') }}" class="text-white-50 text-decoration-none p-1 hover-white" title="ตั้งค่า AI Engine">
                    <i class="fas fa-cog small"></i>
                </a>
                @endif
                <a href="{{ route('ai.chat') }}" class="text-white-50 text-decoration-none p-1 hover-white" title="เปิดหน้าจอเต็ม">
                    <i class="fas fa-external-link-alt small"></i>
                </a>
                <button type="button" class="btn btn-link text-white-50 p-1 text-decoration-none hover-white ms-1" onclick="toggleCopilotWidget()" title="ปิดหน้าต่าง">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Hidden Target DB -->
        <input type="hidden" id="widget_target_db" value="auto">

        <!-- Message Body -->
        <div class="card-body p-3 overflow-auto flex-grow-1" id="widgetChatContainer" style="background: #f8fafc; font-size: 0.85rem;">
            <!-- Welcome Card (RiMS Style) -->
            <div class="card border border-light-subtle rounded-4 shadow-sm bg-white p-3 mb-3" id="widgetWelcomeCard" style="border-radius: 16px !important;">
                <div class="fw-bold text-dark mb-2 d-flex align-items-center" style="font-size: 0.95rem;">
                    สวัสดีครับ! ผมคือ SmartData Copilot 🩺 ✨
                </div>
                <p class="text-secondary small mb-3" style="font-size: 0.8rem; line-height: 1.55;">
                    ผู้ช่วย AI อัจฉริยะประจำโรงพยาบาล พร้อมวิเคราะห์ข้อมูลเวชระเบียน HOSxP, เขียนคำสั่ง SQL ค้นหาสถิติผู้ป่วย OPD/IPD, งาน Backoffice และสรุปแนวทาง CPG คู่มือระเบียบ สธ. สามารถพิมพ์สอบถามได้เลยครับ
                </p>
                <hr class="my-2 border-secondary-subtle opacity-25">
                <div class="small fw-bold text-muted mb-2 d-flex align-items-center" style="font-size: 0.75rem;">
                    <span class="me-1">💡</span> คำถามแนะนำด่วน:
                </div>
                <div class="d-flex flex-column gap-2" id="widgetQuickChips">
                    <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('ขอยอดผู้ป่วยนอก (OPD) ย้อนหลัง 30 วัน แยกตามสิทธิการรักษา')">
                        <span class="me-2">📌</span> ยอดผู้ป่วยนอก (OPD) แยกตามสิทธิ
                    </button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('5 อันดับโรคผู้ป่วยนอกที่มารับบริการมากที่สุดเดือนนี้')">
                        <span class="me-2">💊</span> 5 อันดับโรคผู้ป่วยนอกสูงสุดเดือนนี้
                    </button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('จำนวนผู้ป่วยใน (IPD) กำลัง Admit แยกตามหอผู้ป่วยและอัตราครองเตียง')">
                        <span class="me-2">👛</span> ยอดผู้ป่วยใน IPD กำลัง Admit รายวอร์ด
                    </button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('ขอสรุปจำนวนบุคลากรในโรงพยาบาลแยกตามกลุ่มงาน จาก Backoffice')">
                        <span class="me-2">👥</span> สรุปจำนวนบุคลากรแยกกลุ่มงาน (Backoffice)
                    </button>
                    <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('แนวทางการดูแลรักษาผู้ป่วย Stroke หรือ STEMI มีขั้นตอนอย่างไร')">
                        <span class="me-2">📊</span> แนวทางเวชปฏิบัติ CPG Stroke / STEMI
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Input (RiMS Style) -->
        <div class="card-footer p-3 bg-white border-top border-light-subtle">
            <form id="widgetChatForm" onsubmit="handleWidgetSubmit(event)">
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="widgetMessageInput" class="form-control rounded-pill border py-2 px-3 shadow-none flex-grow-1" placeholder="พิมพ์คำถามที่นี่... (กด Enter เพื่อส่ง)" style="font-size: 0.85rem; border-color: #d1d5db; background: #ffffff;" autocomplete="off">
                    <button type="submit" class="btn rounded-circle d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 42px; height: 42px; background: #094a88; color: #ffffff; border: none; transition: transform 0.2s ease;">
                        <i class="fas fa-paper-plane" style="font-size: 0.9rem;"></i>
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
    0% { box-shadow: 0 0 0 0 rgba(9, 74, 136, 0.55); }
    70% { box-shadow: 0 0 0 15px rgba(9, 74, 136, 0); }
    100% { box-shadow: 0 0 0 0 rgba(9, 74, 136, 0); }
}
#copilot-trigger-btn {
    animation: copilotPulse 3s infinite;
}
.widget-chip-btn {
    background: #ffffff;
    border-color: #e2e8f0 !important;
    color: #334155;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}
.widget-chip-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1 !important;
    color: #094a88;
    transform: translateX(4px);
}
.hover-white:hover {
    color: #ffffff !important;
}
@media (max-width: 576px) {
    #smartdata-copilot-widget {
        bottom: 15px !important;
        right: 15px !important;
    }
    #copilot-chat-window {
        position: fixed !important;
        bottom: 85px !important;
        right: 15px !important;
        left: 15px !important;
        width: auto !important;
        max-width: none !important;
        height: calc(100vh - 110px) !important;
    }
}
</style>

<script>
window.isWidgetOpen = false;
let widgetSessionUuid = 'widget-' + Math.random().toString(36).substr(2, 9);
window.smartdataLogoUrl = window.smartdataLogoUrl || "{{ asset('images/logo.png') }}";
var smartdataLogoUrl = window.smartdataLogoUrl;

function toggleCopilotWidget() {
    const chatWindow = document.getElementById('copilot-chat-window');
    const btnImg = document.getElementById('copilot-btn-img');
    const btnClose = document.getElementById('copilot-btn-close');
    const triggerBtn = document.getElementById('copilot-trigger-btn');
    if (!chatWindow) return;

    window.isWidgetOpen = !window.isWidgetOpen;

    if (window.isWidgetOpen) {
        chatWindow.style.display = 'flex';
        if (btnImg) btnImg.classList.add('d-none');
        if (btnClose) btnClose.classList.remove('d-none');
        if (triggerBtn) {
            triggerBtn.style.background = '#094a88';
            triggerBtn.style.border = '3px solid #ffffff';
        }
        setTimeout(() => {
            const input = document.getElementById('widgetMessageInput');
            if (input) input.focus();
        }, 150);
    } else {
        chatWindow.style.display = 'none';
        if (btnImg) btnImg.classList.remove('d-none');
        if (btnClose) btnClose.classList.add('d-none');
        if (triggerBtn) {
            triggerBtn.style.background = '#ffffff';
            triggerBtn.style.border = '3px solid #ffffff';
        }
    }
}

function clearWidgetChat() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'เริ่มการสนทนาใหม่?',
            text: 'ระบบจะล้างข้อความในหน้าต่างแชทนี้และเริ่มต้นใหม่',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ใช่, เริ่มใหม่',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#094a88'
        }).then((result) => {
            if (result.isConfirmed) {
                resetWidgetConversation();
            }
        });
    } else {
        if (confirm('ต้องการล้างการสนทนาและเริ่มต้นใหม่หรือไม่?')) {
            resetWidgetConversation();
        }
    }
}

function resetWidgetConversation() {
    widgetSessionUuid = 'widget-' + Math.random().toString(36).substr(2, 9);
    const container = document.getElementById('widgetChatContainer');
    if (!container) return;
    container.innerHTML = `
        <div class="card border border-light-subtle rounded-4 shadow-sm bg-white p-3 mb-3" id="widgetWelcomeCard" style="border-radius: 16px !important;">
            <div class="fw-bold text-dark mb-2 d-flex align-items-center" style="font-size: 0.95rem;">
                สวัสดีครับ! ผมคือ SmartData Copilot 🩺 ✨
            </div>
            <p class="text-secondary small mb-3" style="font-size: 0.8rem; line-height: 1.55;">
                ผู้ช่วย AI อัจฉริยะประจำโรงพยาบาล พร้อมวิเคราะห์ข้อมูลเวชระเบียน HOSxP, เขียนคำสั่ง SQL ค้นหาสถิติผู้ป่วย OPD/IPD, งาน Backoffice และสรุปแนวทาง CPG คู่มือระเบียบ สธ. สามารถพิมพ์สอบถามได้เลยครับ
            </p>
            <hr class="my-2 border-secondary-subtle opacity-25">
            <div class="small fw-bold text-muted mb-2 d-flex align-items-center" style="font-size: 0.75rem;">
                <span class="me-1">💡</span> คำถามแนะนำด่วน:
            </div>
            <div class="d-flex flex-column gap-2" id="widgetQuickChips">
                <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('ขอยอดผู้ป่วยนอก (OPD) ย้อนหลัง 30 วัน แยกตามสิทธิการรักษา')">
                    <span class="me-2">📌</span> ยอดผู้ป่วยนอก (OPD) แยกตามสิทธิ
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('5 อันดับโรคผู้ป่วยนอกที่มารับบริการมากที่สุดเดือนนี้')">
                    <span class="me-2">💊</span> 5 อันดับโรคผู้ป่วยนอกสูงสุดเดือนนี้
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('จำนวนผู้ป่วยใน (IPD) กำลัง Admit แยกตามหอผู้ป่วยและอัตราครองเตียง')">
                    <span class="me-2">👛</span> ยอดผู้ป่วยใน IPD กำลัง Admit รายวอร์ด
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('ขอสรุปจำนวนบุคลากรในโรงพยาบาลแยกตามกลุ่มงาน จาก Backoffice')">
                    <span class="me-2">👥</span> สรุปจำนวนบุคลากรแยกกลุ่มงาน (Backoffice)
                </button>
                <button type="button" class="btn btn-sm btn-white border rounded-pill text-start py-2 px-3 shadow-xs widget-chip-btn" onclick="sendWidgetQuickPrompt('แนวทางการดูแลรักษาผู้ป่วย Stroke หรือ STEMI มีขั้นตอนอย่างไร')">
                    <span class="me-2">📊</span> แนวทางเวชปฏิบัติ CPG Stroke / STEMI
                </button>
            </div>
        </div>
    `;
}

function handleWidgetSubmit(e) {
    if (e && e.preventDefault) e.preventDefault();
    const input = document.getElementById('widgetMessageInput');
    if (!input) return;
    const text = input.value.trim();
    if (!text) return;

    input.value = '';

    // Append user bubble
    appendWidgetMessage('user', text);

    // Append loading
    const loading = appendWidgetLoading();

    const targetDbEl = document.getElementById('widget_target_db');
    const targetDb = targetDbEl ? targetDbEl.value : 'auto';

    fetch('{{ route('ai.chat.message') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            message: text,
            session_uuid: widgetSessionUuid,
            mode: 'smart',
            target_db: targetDb
        })
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.message || ('Server error ' + response.status));
        }
        return data;
    })
    .then(data => {
        if (loading && loading.remove) loading.remove();
        let reply = data.content || '';
        let extraHtml = '';

        if (data.mode === 'sql') {
            if (data.rows && data.rows.length > 0 && data.columns && data.columns.length > 1) {
                let ths = data.columns.map(c => `<th class="p-1 px-2 text-nowrap">${escapeHtmlWidget(c)}</th>`).join('');
                let trs = data.rows.slice(0, 12).map(r => {
                    let tds = data.columns.map(c => `<td class="p-1 px-2 text-nowrap">${escapeHtmlWidget(String(r[c] !== null ? r[c] : ''))}</td>`).join('');
                    return `<tr>${tds}</tr>`;
                }).join('');

                extraHtml = `
                    <div class="table-responsive rounded-3 border bg-white mt-2 shadow-sm" style="max-height: 220px; font-size: 0.76rem;">
                        <table class="table table-sm table-striped table-hover mb-0">
                            <thead class="sticky-top" style="background: #e8f0fe; color: #094a88;">
                                <tr>${ths}</tr>
                            </thead>
                            <tbody>${trs}</tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1 text-muted" style="font-size: 0.7rem;">
                        <span><i class="fas fa-database text-primary me-1"></i> ${(data.target_db || 'HOSxP').toUpperCase()} • ${data.count || data.rows.length} รายการ</span>
                    </div>
                `;
            } else if (data.count !== undefined) {
                reply += `\n(ดึงข้อมูลสำเร็จ ${data.count} รายการ จากฐานข้อมูล ${data.target_db || 'HOSxP'})`;
            }
        }

        appendWidgetMessage('assistant', reply, extraHtml);
    })
    .catch(err => {
        if (loading && loading.remove) loading.remove();
        appendWidgetMessage('assistant', 'ขออภัย เกิดข้อผิดพลาด: ' + (err.message || 'ไม่สามารถติดต่อ AI ได้'));
    });
}

function sendWidgetQuickPrompt(text) {
    const input = document.getElementById('widgetMessageInput');
    if (input) {
        input.value = text;
        handleWidgetSubmit(new Event('submit'));
    }
}

function appendWidgetMessage(role, text, extraHtml = '') {
    const container = document.getElementById('widgetChatContainer');
    if (!container) return;
    const div = document.createElement('div');

    if (role === 'user') {
        div.className = 'd-flex justify-content-end mb-3';
        div.innerHTML = `<div class="px-3 py-2 rounded-4 text-white shadow-sm" style="max-width: 82%; background: #094a88; line-height: 1.45; word-break: break-word; white-space: pre-wrap; border-bottom-right-radius: 4px !important;">${escapeHtmlWidget(text)}</div>`;
    } else {
        div.className = 'd-flex justify-content-start mb-3 align-items-start';
        div.innerHTML = `
            <img src="${smartdataLogoUrl}" class="rounded-circle shadow-sm me-2 border bg-white flex-shrink-0" style="width: 28px; height: 28px; object-fit: contain; padding: 1px;" alt="SmartData">
            <div class="px-3 py-2 rounded-4 shadow-sm bg-white border text-dark" style="max-width: 88%; line-height: 1.45; word-break: break-word; border-top-left-radius: 4px !important;">
                <div style="white-space: pre-wrap;">${escapeHtmlWidget(text)}</div>
                ${extraHtml}
            </div>
        `;
    }

    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function appendWidgetLoading() {
    const container = document.getElementById('widgetChatContainer');
    if (!container) return null;
    const div = document.createElement('div');
    div.className = 'd-flex justify-content-start mb-3 align-items-start';
    div.innerHTML = `
        <img src="${smartdataLogoUrl}" class="rounded-circle shadow-sm me-2 border bg-white flex-shrink-0 fa-spin" style="width: 28px; height: 28px; object-fit: contain; padding: 1px;" alt="SmartData">
        <div class="px-3 py-2 rounded-4 shadow-sm bg-white border text-muted small d-flex align-items-center">
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

// Expose functions to global window object
window.toggleCopilotWidget = toggleCopilotWidget;
window.clearWidgetChat = clearWidgetChat;
window.resetWidgetConversation = resetWidgetConversation;
window.handleWidgetSubmit = handleWidgetSubmit;
window.sendWidgetQuickPrompt = sendWidgetQuickPrompt;
</script>
