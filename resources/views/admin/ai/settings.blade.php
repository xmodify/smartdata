@extends('layouts.admin')

@section('title', 'ตั้งค่า SmartData Copilot (AI Engine) - SmartData')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ตั้งค่า SmartData Copilot</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">
                <i class="fas fa-robot text-primary me-2"></i>ตั้งค่า SmartData Copilot (AI Providers)
            </h2>
            <p class="text-muted small mb-0">กำหนดค่า AI Engine รองรับ 3 ค่าย (Gemini, OpenAI, Ollama) บันทึกและทดสอบเชื่อมต่อได้ทันที</p>
        </div>
        <div>
            <a href="{{ route('admin.ai.knowledge') }}" class="btn btn-outline-info rounded-pill px-3 me-2">
                <i class="fas fa-book-medical me-1"></i> จัดการคลังความรู้ AI
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> กลับหน้า Admin
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('admin.ai.settings.update') }}" method="POST">
        @csrf

        <!-- Master Enable/Disable Switch -->
        @php
            $isCopilotEnabled = ($settings['copilot_enabled'] ?? 'Y') === 'Y';
        @endphp
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: {{ $isCopilotEnabled ? '#f0fdf4' : '#fef2f2' }}; border-left: 5px solid {{ $isCopilotEnabled ? '#22c55e' : '#ef4444' }} !important;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 me-3 {{ $isCopilotEnabled ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                        <i class="fas {{ $isCopilotEnabled ? 'fa-power-off' : 'fa-ban' }} fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 {{ $isCopilotEnabled ? 'text-success' : 'text-danger' }}">
                            สถานะระบบ SmartData Copilot: {{ $isCopilotEnabled ? 'เปิดใช้งานอยู่ (Active)' : 'ปิดการใช้งานชั่วคราว (Disabled)' }}
                        </h5>
                        <p class="text-muted small mb-0">
                            {{ $isCopilotEnabled ? 'ผู้ใช้งานทั่วไปสามารถเข้าถึงหน้าจอแชทและปุ่มลอย (Floating Widget) ได้ตามปกติ' : 'ระบบถูกซ่อนจากผู้ใช้งานทั่วไป และปิดการประมวลผลคำขอใหม่ชั่วคราว' }}
                        </p>
                    </div>
                </div>
                <div class="form-check form-switch ps-0">
                    <input class="form-check-input ms-0" type="checkbox" name="copilot_enabled" id="copilot_enabled" value="Y" {{ $isCopilotEnabled ? 'checked' : '' }} style="width: 3.5rem; height: 1.8rem; cursor: pointer;">
                </div>
            </div>
        </div>

        <!-- Provider Selection Section -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-sliders-h text-primary me-2"></i>1. เลือก AI Provider หลักที่ใช้งาน (Active Provider)
                </h5>
                <p class="text-muted small mb-0">เลือกว่าจะใช้สมองกลค่ายใดเป็นหลักในการตอบแชท, แปลง Text-to-SQL และสร้าง Vector RAG</p>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3">
                    @php
                        $activeProvider = $settings['active_provider'] ?? 'gemini';
                    @endphp

                    <!-- Gemini Option -->
                    <div class="col-md-4">
                        <label class="provider-radio-card d-block p-3 rounded-4 border {{ $activeProvider === 'gemini' ? 'border-primary bg-primary-subtle active' : 'border-light-subtle bg-light' }} text-center h-100 position-relative cursor-pointer shadow-sm">
                            <input type="radio" name="active_provider" value="gemini" class="form-check-input position-absolute top-0 end-0 m-3" {{ $activeProvider === 'gemini' ? 'checked' : '' }}>
                            <div class="mb-2">
                                <i class="fab fa-google fa-2x text-primary"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Google Gemini</h5>
                            <span class="badge bg-primary-subtle text-primary rounded-pill mb-2">แนะนำ Cloud เร็ว & ฟรี/คุ้มค่า</span>
                            <p class="small text-muted mb-0">โมเดล Gemini 2.0 Flash / 1.5 Pro ประมวลผลภาษาไทยได้ดีมาก เหมาะสำหรับ Text-to-SQL และ RAG</p>
                        </label>
                    </div>

                    <!-- OpenAI Option -->
                    <div class="col-md-4">
                        <label class="provider-radio-card d-block p-3 rounded-4 border {{ $activeProvider === 'openai' ? 'border-success bg-success-subtle active' : 'border-light-subtle bg-light' }} text-center h-100 position-relative cursor-pointer shadow-sm">
                            <input type="radio" name="active_provider" value="openai" class="form-check-input position-absolute top-0 end-0 m-3" {{ $activeProvider === 'openai' ? 'checked' : '' }}>
                            <div class="mb-2">
                                <i class="fas fa-brain fa-2x text-success"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">OpenAI (ChatGPT)</h5>
                            <span class="badge bg-success-subtle text-success rounded-pill mb-2">GPT-4o / GPT-4o-mini</span>
                            <p class="small text-muted mb-0">โมเดลยอดนิยม แม่นยำสูง รองรับ Embedding-3 Small</p>
                        </label>
                    </div>

                    <!-- Ollama Option -->
                    <div class="col-md-4">
                        <label class="provider-radio-card d-block p-3 rounded-4 border {{ $activeProvider === 'ollama' ? 'border-warning bg-warning-subtle active' : 'border-light-subtle bg-light' }} text-center h-100 position-relative cursor-pointer shadow-sm">
                            <input type="radio" name="active_provider" value="ollama" class="form-check-input position-absolute top-0 end-0 m-3" {{ $activeProvider === 'ollama' ? 'checked' : '' }}>
                            <div class="mb-2">
                                <i class="fas fa-server fa-2x text-warning"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Local LLM (Ollama)</h5>
                            <span class="badge bg-warning-subtle text-dark rounded-pill mb-2">100% Offline / PDPA ปลอดภัย</span>
                            <p class="small text-muted mb-0">รันบน Server โรงพยาบาล ข้อมูลไม่ออกนอกเครื่อง (DeepSeek-R1, Qwen2.5, Typhoon)</p>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Provider Configurations Accordion/Cards -->
        <div class="row g-4">
            <!-- 1. Google Gemini Config -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fab fa-google text-primary me-2"></i>Google Gemini
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="testConnection('gemini')">
                            <i class="fas fa-plug me-1"></i> ทดสอบเชื่อมต่อ
                        </button>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Gemini API Key</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" id="gemini_api_key" name="gemini_api_key" value="{{ $settings['gemini_api_key'] ?? '' }}" class="form-control bg-light border-0 shadow-sm" placeholder="AIzaSy...">
                                <button class="btn btn-light border-0" type="button" onclick="togglePassword('gemini_api_key', this)"><i class="fas fa-eye"></i></button>
                            </div>
                            <small class="text-muted">รับ API Key ได้ฟรีที่ <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-decoration-none">Google AI Studio</a></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Chat Model</label>
                            <input type="text" id="gemini_model" name="gemini_model" value="{{ $settings['gemini_model'] ?? 'gemini-2.0-flash' }}" class="form-control bg-light border-0 shadow-sm" placeholder="gemini-2.0-flash">
                            <small class="text-muted">แนะนำ: <code>gemini-2.0-flash</code> หรือ <code>gemini-1.5-pro</code></small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted">Embedding Model (Vector)</label>
                            <input type="text" id="gemini_embed_model" name="gemini_embed_model" value="{{ $settings['gemini_embed_model'] ?? 'text-embedding-004' }}" class="form-control bg-light border-0 shadow-sm" placeholder="text-embedding-004">
                            <small class="text-muted">ขนาด 768 มิติ (Default: <code>text-embedding-004</code>)</small>
                        </div>
                        <div id="gemini-test-result" class="mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- 2. OpenAI Config -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-brain text-success me-2"></i>OpenAI (ChatGPT)
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="testConnection('openai')">
                            <i class="fas fa-plug me-1"></i> ทดสอบเชื่อมต่อ
                        </button>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">OpenAI API Key</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" id="openai_api_key" name="openai_api_key" value="{{ $settings['openai_api_key'] ?? '' }}" class="form-control bg-light border-0 shadow-sm" placeholder="sk-proj-...">
                                <button class="btn btn-light border-0" type="button" onclick="togglePassword('openai_api_key', this)"><i class="fas fa-eye"></i></button>
                            </div>
                            <small class="text-muted">รับ API Key ได้ที่ <a href="https://platform.openai.com/api-keys" target="_blank" class="text-decoration-none">OpenAI Platform</a></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Chat Model</label>
                            <input type="text" id="openai_model" name="openai_model" value="{{ $settings['openai_model'] ?? 'gpt-4o-mini' }}" class="form-control bg-light border-0 shadow-sm" placeholder="gpt-4o-mini">
                            <small class="text-muted">แนะนำ: <code>gpt-4o-mini</code> หรือ <code>gpt-4o</code></small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted">Embedding Model (Vector)</label>
                            <input type="text" id="openai_embed_model" name="openai_embed_model" value="{{ $settings['openai_embed_model'] ?? 'text-embedding-3-small' }}" class="form-control bg-light border-0 shadow-sm" placeholder="text-embedding-3-small">
                            <small class="text-muted">ขนาด 1,536 มิติ (Default: <code>text-embedding-3-small</code>)</small>
                        </div>
                        <div id="openai-test-result" class="mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- 3. Local Ollama Config -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-server text-warning me-2"></i>Local LLM (Ollama)
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 text-dark" onclick="testConnection('ollama')">
                            <i class="fas fa-plug me-1"></i> ทดสอบเชื่อมต่อ
                        </button>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Ollama Base URL</label>
                            <input type="text" id="ollama_base_url" name="ollama_base_url" value="{{ $settings['ollama_base_url'] ?? 'http://localhost:11434' }}" class="form-control bg-light border-0 shadow-sm" placeholder="http://localhost:11434">
                            <small class="text-muted">IP หรือ Domain ของเครื่องเซิร์ฟเวอร์ที่รัน Ollama</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Chat Model</label>
                            <input type="text" id="ollama_model" name="ollama_model" value="{{ $settings['ollama_model'] ?? 'deepseek-r1:latest' }}" class="form-control bg-light border-0 shadow-sm" placeholder="deepseek-r1:latest">
                            <small class="text-muted">เช่น: <code>deepseek-r1:latest</code>, <code>qwen2.5:latest</code>, <code>typhoon2:latest</code></small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-muted">Embedding Model</label>
                            <input type="text" id="ollama_embed_model" name="ollama_embed_model" value="{{ $settings['ollama_embed_model'] ?? 'nomic-embed-text' }}" class="form-control bg-light border-0 shadow-sm" placeholder="nomic-embed-text">
                            <small class="text-muted">แนะนำ: <code>nomic-embed-text</code> หรือ <code>bge-m3</code></small>
                        </div>
                        <div id="ollama-test-result" class="mt-3" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- 4. RAG & SQL Parameters -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-cogs text-secondary me-2"></i>พารามิเตอร์ RAG & SQL
                        </h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">ฐานข้อมูลเริ่มต้นสำหรับ Text-to-SQL</label>
                            <select name="sql_default_db" class="form-select bg-light border-0 shadow-sm">
                                <option value="hosxp" {{ ($settings['sql_default_db'] ?? 'hosxp') === 'hosxp' ? 'selected' : '' }}>🏥 HOSxP (Slave 1) - เวชระเบียน / ผู้ป่วย / คลินิก</option>
                                <option value="backoffice" {{ ($settings['sql_default_db'] ?? '') === 'backoffice' ? 'selected' : '' }}>🏢 Backoffice - งานบริหาร / พัสดุ / บุคคล</option>
                                <option value="mysql" {{ ($settings['sql_default_db'] ?? '') === 'mysql' ? 'selected' : '' }}>⚙️ SmartData - ฐานข้อมูลระบบภายใน</option>
                            </select>
                            <small class="text-muted">สามารถสลับฐานข้อมูลเป้าหมายได้อิสระในห้องแชท</small>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Top-K Chunks (RAG)</label>
                                <input type="number" name="rag_top_k" value="{{ $settings['rag_top_k'] ?? '4' }}" min="1" max="10" class="form-control bg-light border-0 shadow-sm">
                                <small class="text-muted">จำนวนท่อนเอกสารที่ดึงมาตอบ</small>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Min Cosine Score (0 - 1)</label>
                                <input type="number" step="0.05" name="rag_min_score" value="{{ $settings['rag_min_score'] ?? '0.60' }}" min="0.1" max="1.0" class="form-control bg-light border-0 shadow-sm">
                                <small class="text-muted">คะแนนความสอดคล้องขั้นต่ำ</small>
                            </div>
                        </div>
                        <div class="alert alert-info border-0 rounded-3 mb-0 small">
                            <i class="fas fa-info-circle me-1"></i> <strong>ระบบ Vector MySQL:</strong> เมื่อมีการสลับโมเดล Provider (เช่น จาก Gemini เป็น OpenAI) อย่าลืมกดปุ่ม <code>Re-Embed ทั้งหมด</code> ในหน้าจัดการคลังความรู้ เพื่อแปลงมิติ Vector ให้ตรงกับโมเดลใหม่
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mt-5 mb-5">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                <i class="fas fa-save me-2"></i> บันทึกการตั้งค่าทั้งหมด
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function testConnection(provider) {
    const resultBox = document.getElementById(provider + '-test-result');
    resultBox.style.display = 'block';
    resultBox.innerHTML = '<div class="alert alert-secondary py-2 px-3 small mb-0"><i class="fas fa-spinner fa-spin me-2"></i> กำลังทดสอบเชื่อมต่อกับ ' + provider + '...</div>';

    const payload = {
        _token: '{{ csrf_token() }}',
        provider: provider,
        gemini_api_key: document.getElementById('gemini_api_key') ? document.getElementById('gemini_api_key').value : '',
        gemini_model: document.getElementById('gemini_model') ? document.getElementById('gemini_model').value : '',
        openai_api_key: document.getElementById('openai_api_key') ? document.getElementById('openai_api_key').value : '',
        openai_model: document.getElementById('openai_model') ? document.getElementById('openai_model').value : '',
        ollama_base_url: document.getElementById('ollama_base_url') ? document.getElementById('ollama_base_url').value : '',
        ollama_model: document.getElementById('ollama_model') ? document.getElementById('ollama_model').value : '',
    };

    fetch('{{ route('admin.ai.settings.test') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            resultBox.innerHTML = '<div class="alert alert-success py-2 px-3 small mb-0"><i class="fas fa-check-circle me-1"></i> ' + data.message + ' <span class="badge bg-success ms-1">' + (data.latency_ms || 0) + ' ms</span></div>';
        } else {
            resultBox.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0"><i class="fas fa-times-circle me-1"></i> ' + data.message + '</div>';
        }
    })
    .catch(err => {
        resultBox.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0"><i class="fas fa-times-circle me-1"></i> ไม่สามารถส่งคำขอได้: ' + err.message + '</div>';
    });
}
</script>
@endpush
@endsection
