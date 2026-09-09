@extends('layouts.admin')

@section('title', 'ตั้งค่า SmartData Copilot (AI Engine) - SmartData')

@push('styles')
<style>
.provider-radio-card {
    transition: all 0.2s ease-in-out;
}
.provider-radio-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.gemini-pill {
    font-size: 0.82rem;
    padding: 0.25rem 0.75rem;
    border-radius: 50rem;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    background-color: #ffffff;
    border: 1px solid #dee2e6;
    color: #495057;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    user-select: none;
    line-height: 1.4;
}
.gemini-pill:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.06);
}
.gemini-pill.pill-rec {
    background-color: #f0fdf4;
    border-color: #86efac;
    color: #166534;
}
.gemini-pill.pill-rec:hover {
    background-color: #dcfce7;
    border-color: #22c55e;
}
.gemini-pill.pill-fast {
    background-color: #fffbeb;
    border-color: #fde68a;
    color: #92400e;
}
.gemini-pill.pill-fast:hover {
    background-color: #fef3c7;
    border-color: #f59e0b;
}
.gemini-pill.active {
    background-color: #198754 !important;
    border-color: #198754 !important;
    color: #ffffff !important;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(25, 135, 84, 0.35);
}
</style>
@endpush

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

        @php
            $activeProvider = $settings['active_provider'] ?? 'gemini';
        @endphp

        <!-- Unified AI & LLM Connection Card -->
        <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center">
                                <i class="fas fa-cog text-warning me-2"></i>ตั้งค่า AI & LLM Connection
                                <span id="header_provider_badge" class="badge {{ $activeProvider === 'gemini' ? 'bg-primary-subtle text-primary' : ($activeProvider === 'openai' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-dark') }} rounded-pill ms-2 fs-6 fw-normal px-3 py-1">
                                    {{ $activeProvider === 'gemini' ? 'Google Gemini' : ($activeProvider === 'openai' ? 'OpenAI (ChatGPT)' : 'Local LLM (Ollama)') }}
                                </span>
                            </h5>
                            <p class="text-muted small mb-0">ระบบเชื่อมต่อ AI สำหรับตอบคำถามค้นหาเวชระเบียน, Text-to-SQL และ RAG คลังความรู้</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" id="header_gemini_table_btn" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="openGeminiListModal()" style="{{ $activeProvider === 'gemini' ? '' : 'display: none;' }}">
                                <i class="fas fa-list-ul me-1"></i> ดูตารางทุกโมเดล
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="testActiveConnection()">
                                <i class="fas fa-satellite-dish me-1"></i> ((•)) ทดสอบเชื่อมต่อทันที
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Info Alert Banner matching Image 2 -->
                        <div class="alert alert-info border-0 rounded-3 mb-4 py-2 px-3 small d-flex align-items-center" style="background-color: #e0f2fe; color: #0369a1;">
                            <i class="fas fa-info-circle fs-5 me-2 text-primary"></i>
                            <span>ปรับเปลี่ยนผู้ให้บริการ AI, Key หรือระบุโมเดล ค่าจะบันทึกลง <code>main_setting / ai_settings</code> (เฉพาะ Admin)</span>
                        </div>

                        <div class="row g-3">
                            <!-- Row 1: Provider & Dynamic Base URL -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">ผู้ให้บริการ AI (AI Provider)</label>
                                <select id="active_provider_select" name="active_provider" class="form-select bg-light border-0 shadow-sm fw-semibold text-primary py-2 fs-6" onchange="switchProvider(this.value)">
                                    <option value="gemini" {{ $activeProvider === 'gemini' ? 'selected' : '' }}>Google Gemini (แนะนำ Cloud เร็ว/ฟรี)</option>
                                    <option value="openai" {{ $activeProvider === 'openai' ? 'selected' : '' }}>OpenAI (ChatGPT - GPT-4o / GPT-4o-mini)</option>
                                    <option value="ollama" {{ $activeProvider === 'ollama' ? 'selected' : '' }}>Local LLM (Ollama - Offline 100% / PDPA)</option>
                                </select>
                                <div class="small text-muted mt-1" id="provider_description">
                                    @if($activeProvider === 'gemini')
                                        <span class="text-primary"><i class="fab fa-google me-1"></i> แนะนำ Cloud เร็ว & ฟรี/คุ้มค่า (Gemini 2.0 Flash / 1.5 Pro)</span>
                                    @elseif($activeProvider === 'openai')
                                        <span class="text-success"><i class="fas fa-brain me-1"></i> OpenAI Official Cloud (GPT-4o / GPT-4o-mini)</span>
                                    @else
                                        <span class="text-warning"><i class="fas fa-server me-1"></i> 100% Offline / PDPA ปลอดภัยบนเซิร์ฟเวอร์โรงพยาบาล</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Gemini Base URL -->
                                <div id="base-url-gemini" class="provider-base-url-section" style="{{ $activeProvider === 'gemini' ? '' : 'display: none;' }}">
                                    <label class="form-label fw-bold small text-muted">AI API Base URL (Google Cloud Official API)</label>
                                    <input type="text" class="form-control bg-light border-0 shadow-sm font-monospace" value="https://generativelanguage.googleapis.com" readonly>
                                    <div class="small text-success mt-1 fw-medium">
                                        <i class="fas fa-check-circle me-1"></i> เชื่อมต่อ Google Official Cloud API โดยตรง (ไม่ต้องแก้ไข)
                                    </div>
                                </div>

                                <!-- OpenAI Base URL -->
                                <div id="base-url-openai" class="provider-base-url-section" style="{{ $activeProvider === 'openai' ? '' : 'display: none;' }}">
                                    <label class="form-label fw-bold small text-muted">OpenAI API Base URL</label>
                                    <input type="text" class="form-control bg-light border-0 shadow-sm font-monospace" value="https://api.openai.com/v1" readonly>
                                    <div class="small text-success mt-1 fw-medium">
                                        <i class="fas fa-check-circle me-1"></i> เชื่อมต่อ OpenAI Official Endpoint (ไม่ต้องแก้ไข)
                                    </div>
                                </div>

                                <!-- Ollama Base URL -->
                                <div id="base-url-ollama" class="provider-base-url-section" style="{{ $activeProvider === 'ollama' ? '' : 'display: none;' }}">
                                    <label class="form-label fw-bold small text-muted">Ollama Base URL (IP / Server URL)</label>
                                    <input type="text" id="ollama_base_url" name="ollama_base_url" value="{{ $settings['ollama_base_url'] ?? 'http://localhost:11434' }}" class="form-control bg-light border-0 shadow-sm font-monospace" placeholder="http://localhost:11434">
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-network-wired me-1"></i> เช่น <code>http://localhost:11434</code> หรือ <code>http://192.168.1.xxx:11434</code>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <!-- 1. Google Gemini Config Section -->
                        <div id="section-gemini" class="provider-config-section" style="{{ $activeProvider === 'gemini' ? '' : 'display: none;' }}">
                            <div class="row g-3">

                            <!-- Row 2: API Key with Search Button matching Image 2 -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold small text-muted mb-0">
                                        AI API Key <span class="text-danger small">(จำเป็นสำหรับ Gemini)</span>
                                    </label>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm" onclick="fetchGeminiModels()">
                                        <i class="fas fa-search me-1"></i> ค้นหาโมเดลที่ใช้งานได้
                                    </button>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-key text-muted"></i></span>
                                    <input type="password" id="gemini_api_key" name="gemini_api_key" value="{{ $settings['gemini_api_key'] ?? '' }}" class="form-control bg-light border-0 shadow-sm" placeholder="AIzaSy...">
                                    <button class="btn btn-light border-0" type="button" onclick="togglePassword('gemini_api_key', this)"><i class="fas fa-eye"></i></button>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <small class="text-muted">สำหรับ Gemini ขอรับ API Key ฟรีได้ที่ <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-primary text-decoration-none">Google AI Studio</a></small>
                                    <span id="gemini-status-badge" class="text-success small fw-semibold" style="display: none;">
                                        <i class="fas fa-check-circle me-1"></i> <span id="gemini-status-count">พบ 27 โมเดลพร้อมใช้</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Row 3: Chat Model & Embedding Model with Quick Pills matching Image 2 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">
                                    <i class="fas fa-comment-dots text-info me-1"></i> ชื่อโมเดลตอบคำถาม (Chat Model)
                                </label>
                                <div class="input-group">
                                    <input type="text" id="gemini_model" name="gemini_model" value="{{ $settings['gemini_model'] ?? 'gemini-2.0-flash' }}" class="form-control bg-light border-0 shadow-sm font-monospace" placeholder="gemini-2.0-flash" list="gemini_chat_models_datalist">
                                    <button class="btn btn-light border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        เลือกรุ่น
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" id="gemini_chat_dropdown" style="max-height: 280px; overflow-y: auto;">
                                        <li><h6 class="dropdown-header">โมเดลยอดนิยม</h6></li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiChatModel('gemini-3.7-flash')"><strong>gemini-3.7-flash</strong> <span class="badge bg-success-subtle text-success ms-1">แนะนำ/ฉลาดล่าสุด</span></a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiChatModel('gemini-2.0-flash')"><strong>gemini-2.0-flash</strong> <span class="badge bg-primary-subtle text-primary ms-1">แนะนำ/เร็วสุด</span></a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiChatModel('gemini-2.0-flash-lite')">gemini-2.0-flash-lite</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiChatModel('gemini-1.5-flash')">gemini-1.5-flash</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiChatModel('gemini-1.5-pro')">gemini-1.5-pro</a></li>
                                    </ul>
                                </div>
                                <datalist id="gemini_chat_models_datalist"></datalist>

                                <!-- Clickable Pill Badges matching Image 2 -->
                                <div class="d-flex flex-wrap gap-2 mt-2" id="gemini-chat-pills">
                                    <button type="button" class="gemini-pill pill-rec {{ ($settings['gemini_model'] ?? '') === 'gemini-3.7-flash' ? 'active' : '' }}" data-model="gemini-3.7-flash" onclick="selectGeminiChatModel('gemini-3.7-flash', this)">
                                        ⭐ gemini-3.7-flash (แนะนำ)
                                    </button>
                                    <button type="button" class="gemini-pill pill-rec {{ ($settings['gemini_model'] ?? 'gemini-2.0-flash') === 'gemini-2.0-flash' ? 'active' : '' }}" data-model="gemini-2.0-flash" onclick="selectGeminiChatModel('gemini-2.0-flash', this)">
                                        ⭐ gemini-2.0-flash (แนะนำ)
                                    </button>
                                    <button type="button" class="gemini-pill pill-fast {{ ($settings['gemini_model'] ?? '') === 'gemini-3.5-flash-lite' ? 'active' : '' }}" data-model="gemini-3.5-flash-lite" onclick="selectGeminiChatModel('gemini-3.5-flash-lite', this)">
                                        ⚡ gemini-3.5-flash-lite (ตอบไว)
                                    </button>
                                    <button type="button" class="gemini-pill pill-fast {{ ($settings['gemini_model'] ?? '') === 'gemini-2.0-flash-lite' ? 'active' : '' }}" data-model="gemini-2.0-flash-lite" onclick="selectGeminiChatModel('gemini-2.0-flash-lite', this)">
                                        ⚡ gemini-2.0-flash-lite (ตอบไว)
                                    </button>
                                    <button type="button" class="gemini-pill {{ ($settings['gemini_model'] ?? '') === 'gemini-flash-latest' ? 'active' : '' }}" data-model="gemini-flash-latest" onclick="selectGeminiChatModel('gemini-flash-latest', this)">
                                        gemini-flash-latest
                                    </button>
                                    <button type="button" class="gemini-pill {{ ($settings['gemini_model'] ?? '') === 'gemini-1.5-pro' ? 'active' : '' }}" data-model="gemini-1.5-pro" onclick="selectGeminiChatModel('gemini-1.5-pro', this)">
                                        gemini-1.5-pro
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">สำหรับค้นหาคู่มือ/ระเบียบในหน้า RAG Knowledge และถามทั่วไป</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark mb-1">
                                    <i class="fas fa-magic text-warning me-1"></i> ชื่อโมเดลทำ Vector (Embedding Model)
                                </label>
                                <div class="input-group">
                                    <input type="text" id="gemini_embed_model" name="gemini_embed_model" value="{{ $settings['gemini_embed_model'] ?? 'text-embedding-004' }}" class="form-control bg-light border-0 shadow-sm font-monospace" placeholder="text-embedding-004" list="gemini_embed_models_datalist">
                                    <button class="btn btn-light border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        เลือกรุ่น
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" id="gemini_embed_dropdown" style="max-height: 250px; overflow-y: auto;">
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiEmbedModel('gemini-embedding-001')"><strong>gemini-embedding-001</strong></a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiEmbedModel('text-embedding-004')"><strong>text-embedding-004</strong> (768 มิติ)</a></li>
                                        <li><a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiEmbedModel('embedding-001')">embedding-001</a></li>
                                    </ul>
                                </div>
                                <datalist id="gemini_embed_models_datalist"></datalist>

                                <!-- Clickable Pill Badges matching Image 2 -->
                                <div class="d-flex flex-wrap gap-2 mt-2" id="gemini-embed-pills">
                                    <button type="button" class="gemini-pill pill-rec {{ ($settings['gemini_embed_model'] ?? '') === 'gemini-embedding-001' ? 'active' : '' }}" data-model="gemini-embedding-001" onclick="selectGeminiEmbedModel('gemini-embedding-001', this)">
                                        ⭐ gemini-embedding-001
                                    </button>
                                    <button type="button" class="gemini-pill pill-rec {{ ($settings['gemini_embed_model'] ?? 'text-embedding-004') === 'text-embedding-004' ? 'active' : '' }}" data-model="text-embedding-004" onclick="selectGeminiEmbedModel('text-embedding-004', this)">
                                        ⭐ text-embedding-004 (แนะนำ)
                                    </button>
                                    <button type="button" class="gemini-pill {{ ($settings['gemini_embed_model'] ?? '') === 'embedding-001' ? 'active' : '' }}" data-model="embedding-001" onclick="selectGeminiEmbedModel('embedding-001', this)">
                                        embedding-001
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">แปลงเอกสารเป็น Vector เพื่อการค้นหาความหมาย (Semantic Search)</small>
                            </div>
                        </div>

                        <!-- Presets Box (Token Quota Comparison) -->
                        <div class="mt-4 p-3 rounded-3 bg-light-subtle border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-dark"><i class="fas fa-bolt text-warning me-1"></i> แนะนำชุดโมเดลตามโควต้า Token (คลิกเพื่อเลือกทันที):</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="p-2 rounded-3 border bg-white cursor-pointer gemini-preset-item hover-shadow h-100" onclick="applyGeminiPreset('gemini-2.0-flash', 'text-embedding-004', 'ชุดมาตรฐาน Flash 2.0', 'เร็วสุด ตอบไวใน 1 วิ • ฟรี 1,500 RPD / 1M TPM • เหมาะกับดึงข้อมูล SQL คนไข้ และ CPG')">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-success-subtle text-success">แนะนำอันดับ 1</span>
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;">ใช้ชุดนี้</button>
                                        </div>
                                        <strong class="text-dark small d-block">Flash 2.0 (เร็ว + โควต้าฟรีเยอะ)</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">Chat: <code>gemini-2.0-flash</code> | Embed: <code>text-embedding-004</code></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 rounded-3 border bg-white cursor-pointer gemini-preset-item hover-shadow h-100" onclick="applyGeminiPreset('gemini-2.0-flash-lite', 'text-embedding-004', 'ชุดประหยัด Token (Lite)', 'กิน Token น้อยสุด ตอบไว • เหมาะสำหรับบุคลากรใช้งานพร้อมกันจำนวนมาก')">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-info-subtle text-info">ประหยัด Token</span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;">ใช้ชุดนี้</button>
                                        </div>
                                        <strong class="text-dark small d-block">Flash 2.0 Lite (สเปกเบา)</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">Chat: <code>gemini-2.0-flash-lite</code> | Embed: <code>text-embedding-004</code></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 rounded-3 border bg-white cursor-pointer gemini-preset-item hover-shadow h-100" onclick="applyGeminiPreset('gemini-1.5-pro', 'text-embedding-004', 'ชุดวิเคราะห์เชิงลึก (Pro 1.5)', 'ความจุสูงถึง 2,000,000 Tokens • อ่านเอกสาร CPG หรือระเบียบเล่มหนา')">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary-subtle text-primary">วิเคราะห์ลึก</span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-0 px-2" style="font-size: 0.72rem;">ใช้ชุดนี้</button>
                                        </div>
                                        <strong class="text-dark small d-block">Pro 1.5 (ความจุ 2 ล้าน Tokens)</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">Chat: <code>gemini-1.5-pro</code> | Embed: <code>text-embedding-004</code></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. OpenAI Config Section -->
                <div id="section-openai" class="provider-config-section" style="{{ $activeProvider === 'openai' ? '' : 'display: none;' }}">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">OpenAI API Key <span class="text-danger small">(จำเป็นสำหรับ OpenAI)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="fas fa-key text-muted"></i></span>
                                <input type="password" id="openai_api_key" name="openai_api_key" value="{{ $settings['openai_api_key'] ?? '' }}" class="form-control bg-light border-0 shadow-sm" placeholder="sk-proj-...">
                                <button class="btn btn-light border-0" type="button" onclick="togglePassword('openai_api_key', this)"><i class="fas fa-eye"></i></button>
                            </div>
                            <small class="text-muted">รับ API Key ได้ที่ <a href="https://platform.openai.com/api-keys" target="_blank" class="text-decoration-none">OpenAI Platform</a></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-comment-dots text-success me-1"></i> ชื่อโมเดลตอบคำถาม (Chat Model)</label>
                            <input type="text" id="openai_model" name="openai_model" value="{{ $settings['openai_model'] ?? 'gpt-4o-mini' }}" class="form-control bg-light border-0 shadow-sm font-monospace" placeholder="gpt-4o-mini">
                            <div class="d-flex flex-wrap gap-1 mt-2" id="openai-chat-pills">
                                <button type="button" class="gemini-pill pill-rec {{ ($settings['openai_model'] ?? 'gpt-4o-mini') === 'gpt-4o-mini' ? 'active' : '' }}" onclick="selectOpenAiChat('gpt-4o-mini', this)">
                                    ⭐ gpt-4o-mini (แนะนำ/คุ้มค่า)
                                </button>
                                <button type="button" class="gemini-pill pill-rec {{ ($settings['openai_model'] ?? '') === 'gpt-4o' ? 'active' : '' }}" onclick="selectOpenAiChat('gpt-4o', this)">
                                    ⭐ gpt-4o (เรือธง)
                                </button>
                                <button type="button" class="gemini-pill {{ ($settings['openai_model'] ?? '') === 'gpt-3.5-turbo' ? 'active' : '' }}" onclick="selectOpenAiChat('gpt-3.5-turbo', this)">
                                    gpt-3.5-turbo
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">แนะนำ: <code>gpt-4o-mini</code> หรือ <code>gpt-4o</code></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-project-diagram text-primary me-1"></i> Embedding Model (Vector)</label>
                            <input type="text" id="openai_embed_model" name="openai_embed_model" value="{{ $settings['openai_embed_model'] ?? 'text-embedding-3-small' }}" class="form-control bg-light border-0 shadow-sm font-monospace" placeholder="text-embedding-3-small">
                            <div class="d-flex flex-wrap gap-1 mt-2" id="openai-embed-pills">
                                <button type="button" class="gemini-pill pill-rec {{ ($settings['openai_embed_model'] ?? 'text-embedding-3-small') === 'text-embedding-3-small' ? 'active' : '' }}" onclick="selectOpenAiEmbed('text-embedding-3-small', this)">
                                    ⭐ text-embedding-3-small (แนะนำ)
                                </button>
                                <button type="button" class="gemini-pill {{ ($settings['openai_embed_model'] ?? '') === 'text-embedding-3-large' ? 'active' : '' }}" onclick="selectOpenAiEmbed('text-embedding-3-large', this)">
                                    text-embedding-3-large
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">ขนาด 1,536 มิติ (Default: <code>text-embedding-3-small</code>)</small>
                        </div>

                        <!-- OpenAI Presets -->
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold small text-muted mb-2">
                                <i class="fas fa-magic text-warning me-1"></i> ⚡ แนะนำโมเดลตามการใช้งาน (คลิกเพื่อเลือกทันที):
                            </label>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="card border rounded-3 p-2 h-100 cursor-pointer hover-shadow transition-all bg-light" style="cursor: pointer;" onclick="applyOpenAiPreset('gpt-4o-mini', 'text-embedding-3-small', 'GPT-4o-mini (คุ้มค่า)', 'โมเดลราคาประหยัด ประมวลผลเร็ว')">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-success-subtle text-success rounded-pill fw-bold" style="font-size: 0.72rem;">⭐ แนะนำ / ประหยัด</span>
                                            <span class="badge bg-white text-muted border" style="font-size: 0.68rem;">128k Tokens</span>
                                        </div>
                                        <strong class="text-dark small d-block">GPT-4o-mini (เร็ว & ประหยัด)</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">Chat: <code>gpt-4o-mini</code> | Embed: <code>text-embedding-3-small</code></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border rounded-3 p-2 h-100 cursor-pointer hover-shadow transition-all bg-light" style="cursor: pointer;" onclick="applyOpenAiPreset('gpt-4o', 'text-embedding-3-small', 'GPT-4o (เรือธง)', 'โมเดลเรือธง ความแม่นยำสูงสุด')">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-primary-subtle text-primary rounded-pill fw-bold" style="font-size: 0.72rem;">👑 โมเดลเรือธง</span>
                                            <span class="badge bg-white text-muted border" style="font-size: 0.68rem;">128k Tokens</span>
                                        </div>
                                        <strong class="text-dark small d-block">GPT-4o (แม่นยำสูงสุด)</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">Chat: <code>gpt-4o</code> | Embed: <code>text-embedding-3-small</code></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Local Ollama Config Section -->
                <div id="section-ollama" class="provider-config-section" style="{{ $activeProvider === 'ollama' ? '' : 'display: none;' }}">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-success border-0 rounded-3 py-2 px-3 small d-flex align-items-center mb-0" style="background-color: #f0fdf4; color: #166534;">
                                <i class="fas fa-shield-alt fs-4 me-2 text-success"></i>
                                <div>
                                    <strong>100% Offline & PDPA Compliant:</strong> Local LLM (Ollama) ทำงานบน Server ภายในโรงพยาบาล ไม่ต้องใช้ API Key ข้อมูลเวชระเบียนของผู้ป่วยจะไม่ถูกส่งออกสู่อินเทอร์เน็ต
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-comment-dots text-warning me-1"></i> Chat Model (โมเดลตอบคำถาม)</label>
                            <input type="text" id="ollama_model" name="ollama_model" value="{{ $settings['ollama_model'] ?? 'deepseek-r1:latest' }}" class="form-control bg-light border-0 shadow-sm font-monospace" placeholder="deepseek-r1:latest">
                            <div class="d-flex flex-wrap gap-1 mt-2" id="ollama-chat-pills">
                                <button type="button" class="gemini-pill pill-rec {{ ($settings['ollama_model'] ?? 'deepseek-r1:latest') === 'deepseek-r1:latest' ? 'active' : '' }}" onclick="selectOllamaChat('deepseek-r1:latest', this)">
                                    ⭐ deepseek-r1:latest (เหตุผล)
                                </button>
                                <button type="button" class="gemini-pill pill-rec {{ ($settings['ollama_model'] ?? '') === 'qwen2.5:latest' ? 'active' : '' }}" onclick="selectOllamaChat('qwen2.5:latest', this)">
                                    ⭐ qwen2.5:latest (ภาษาไทยดี)
                                </button>
                                <button type="button" class="gemini-pill {{ ($settings['ollama_model'] ?? '') === 'typhoon2:latest' ? 'active' : '' }}" onclick="selectOllamaChat('typhoon2:latest', this)">
                                    typhoon2:latest
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">เช่น: <code>deepseek-r1:latest</code>, <code>qwen2.5:latest</code>, <code>typhoon2:latest</code></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark mb-1"><i class="fas fa-project-diagram text-primary me-1"></i> Embedding Model (Vector)</label>
                            <input type="text" id="ollama_embed_model" name="ollama_embed_model" value="{{ $settings['ollama_embed_model'] ?? 'nomic-embed-text' }}" class="form-control bg-light border-0 shadow-sm font-monospace" placeholder="nomic-embed-text">
                            <div class="d-flex flex-wrap gap-1 mt-2" id="ollama-embed-pills">
                                <button type="button" class="gemini-pill pill-rec {{ ($settings['ollama_embed_model'] ?? 'nomic-embed-text') === 'nomic-embed-text' ? 'active' : '' }}" onclick="selectOllamaEmbed('nomic-embed-text', this)">
                                    ⭐ nomic-embed-text (แนะนำ)
                                </button>
                                <button type="button" class="gemini-pill {{ ($settings['ollama_embed_model'] ?? '') === 'bge-m3' ? 'active' : '' }}" onclick="selectOllamaEmbed('bge-m3', this)">
                                    bge-m3
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">แนะนำ: <code>nomic-embed-text</code> หรือ <code>bge-m3</code></small>
                        </div>

                        <!-- Ollama Presets -->
                        <div class="col-12 mt-3">
                            <label class="form-label fw-bold small text-muted mb-2">
                                <i class="fas fa-magic text-warning me-1"></i> ⚡ แนะนำโมเดลตามการใช้งาน (คลิกเพื่อเลือกทันที):
                            </label>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="card border rounded-3 p-2 h-100 cursor-pointer hover-shadow transition-all bg-light" style="cursor: pointer;" onclick="applyOllamaPreset('deepseek-r1:latest', 'nomic-embed-text', 'DeepSeek-R1', 'โมเดลเน้นการคิดวิเคราะห์ขั้นสูง')">
                                        <span class="badge bg-warning-subtle text-dark rounded-pill fw-bold mb-1" style="font-size: 0.72rem;">🧠 วิเคราะห์เหตุผล</span>
                                        <strong class="text-dark small d-block">DeepSeek-R1</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">Chat: <code>deepseek-r1:latest</code> | Embed: <code>nomic-embed-text</code></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border rounded-3 p-2 h-100 cursor-pointer hover-shadow transition-all bg-light" style="cursor: pointer;" onclick="applyOllamaPreset('qwen2.5:latest', 'nomic-embed-text', 'Qwen 2.5', 'โมเดลภาษาไทยยอดนิยม ตอบได้เป็นธรรมชาติ')">
                                        <span class="badge bg-success-subtle text-success rounded-pill fw-bold mb-1" style="font-size: 0.72rem;">🇹🇭 ภาษาไทยดีเยี่ยม</span>
                                        <strong class="text-dark small d-block">Qwen 2.5</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">Chat: <code>qwen2.5:latest</code> | Embed: <code>nomic-embed-text</code></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border rounded-3 p-2 h-100 cursor-pointer hover-shadow transition-all bg-light" style="cursor: pointer;" onclick="applyOllamaPreset('typhoon2:latest', 'nomic-embed-text', 'Typhoon 2', 'โมเดลภาษาไทยเฉพาะทางโดย SCB 10X')">
                                        <span class="badge bg-primary-subtle text-primary rounded-pill fw-bold mb-1" style="font-size: 0.72rem;">🇹🇭 ภาษาไทย SCB 10X</span>
                                        <strong class="text-dark small d-block">Typhoon 2</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">Chat: <code>typhoon2:latest</code> | Embed: <code>nomic-embed-text</code></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Result Inline Alert Box -->
                <div id="active-test-result" class="mt-3" style="display: none;"></div>

                <!-- Card Action Footer -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary rounded-pill px-4 shadow-sm" onclick="testActiveConnection()">
                        <i class="fas fa-satellite-dish me-2"></i> ((•)) ทดสอบเชื่อมต่อทันที
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.ai.settings') }}" class="btn btn-secondary rounded-pill px-4">ยกเลิก</a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                            <i class="fas fa-save me-2"></i> บันทึกการตั้งค่า
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- พารามิเตอร์ RAG & SQL Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-cogs text-secondary me-2"></i>พารามิเตอร์ RAG & SQL
                    </h5>
                    <p class="text-muted small mb-0">กำหนดฐานข้อมูลเป้าหมายสำหรับการค้นหาเวชระเบียน และความละเอียดในการดึงเอกสาร RAG</p>
                </div>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">ฐานข้อมูลเริ่มต้นสำหรับ Text-to-SQL</label>
                        <select name="sql_default_db" class="form-select bg-light border-0 shadow-sm py-2">
                            <option value="hosxp" {{ ($settings['sql_default_db'] ?? 'hosxp') === 'hosxp' ? 'selected' : '' }}>🏥 HOSxP (Slave 1) - เวชระเบียน / ผู้ป่วย / คลินิก</option>
                            <option value="backoffice" {{ ($settings['sql_default_db'] ?? '') === 'backoffice' ? 'selected' : '' }}>🏢 Backoffice - งานบริหาร / พัสดุ / บุคคล</option>
                            <option value="mysql" {{ ($settings['sql_default_db'] ?? '') === 'mysql' ? 'selected' : '' }}>⚙️ SmartData - ฐานข้อมูลระบบภายใน</option>
                        </select>
                        <small class="text-muted">สามารถสลับฐานข้อมูลเป้าหมายได้อิสระในห้องแชท</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Top-K Chunks (RAG)</label>
                        <input type="number" name="rag_top_k" value="{{ $settings['rag_top_k'] ?? '4' }}" min="1" max="10" class="form-control bg-light border-0 shadow-sm py-2">
                        <small class="text-muted">จำนวนท่อนเอกสารที่ดึงมาตอบ</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Min Cosine Score (0 - 1)</label>
                        <input type="number" step="0.05" name="rag_min_score" value="{{ $settings['rag_min_score'] ?? '0.60' }}" min="0.1" max="1.0" class="form-control bg-light border-0 shadow-sm py-2">
                        <small class="text-muted">คะแนนความสอดคล้องขั้นต่ำ</small>
                    </div>
                </div>
                <div class="alert alert-info border-0 rounded-3 mb-0 small">
                    <i class="fas fa-info-circle me-1"></i> <strong>ระบบ Vector MySQL:</strong> เมื่อมีการสลับโมเดล Provider (เช่น จาก Gemini เป็น OpenAI) อย่าลืมกดปุ่ม <code>Re-Embed ทั้งหมด</code> ในหน้าจัดการคลังความรู้ เพื่อแปลงมิติ Vector ให้ตรงกับโมเดลใหม่
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center mt-4 mb-5">
            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow">
                <i class="fas fa-save me-2"></i> บันทึกการตั้งค่าทั้งหมด
            </button>
        </div>
    </form>
</div>

<!-- Gemini Models List Modal -->
<div class="modal fade" id="geminiModelsModal" tabindex="-1" aria-labelledby="geminiModelsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-gradient-primary-custom text-white border-0 py-3 px-4 rounded-top-4">
                <h5 class="modal-title fw-bold" id="geminiModelsModalLabel">
                    <i class="fab fa-google me-2"></i>รายชื่อโมเดล Google Gemini ที่สามารถใช้งานได้
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="input-group" style="max-width: 320px;">
                        <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="modelFilterInput" class="form-control bg-light border-0" placeholder="พิมพ์กรองชื่อ Model..." onkeyup="filterGeminiModelsTable()">
                    </div>
                    <span id="modalTotalModelsCount" class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">0 รุ่น</span>
                </div>

                <div class="table-responsive rounded-3 border">
                    <table class="table table-hover align-middle mb-0 small" id="geminiModelsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Model ID</th>
                                <th>ชื่อทางการ (Display Name)</th>
                                <th>ประเภท</th>
                                <th class="text-end">เลือกใช้งาน</th>
                            </tr>
                        </thead>
                        <tbody id="geminiModelsTableBody">
                            <!-- Dynamically populated -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cachedGeminiModels = [];

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

function openGeminiListModal() {
    const modal = new bootstrap.Modal(document.getElementById('geminiModelsModal'));
    modal.show();
}

function switchProvider(provider) {
    // 1. Update dropdown
    const select = document.getElementById('active_provider_select');
    if (select && select.value !== provider) select.value = provider;

    // 2. Switch provider sections
    document.querySelectorAll('.provider-config-section').forEach(sec => sec.style.display = 'none');
    const activeSec = document.getElementById('section-' + provider);
    if (activeSec) activeSec.style.display = 'block';

    // 3. Switch Base URL displays
    document.querySelectorAll('.provider-base-url-section').forEach(u => u.style.display = 'none');
    const activeUrl = document.getElementById('base-url-' + provider);
    if (activeUrl) activeUrl.style.display = 'block';

    // 4. Update Header Badge and buttons
    const badge = document.getElementById('header_provider_badge');
    const tableBtn = document.getElementById('header_gemini_table_btn');
    if (provider === 'gemini') {
        if (badge) {
            badge.className = 'badge bg-primary-subtle text-primary rounded-pill ms-2 fs-6 fw-normal px-3 py-1';
            badge.textContent = 'Google Gemini';
        }
        if (tableBtn) tableBtn.style.display = 'inline-block';
    } else if (provider === 'openai') {
        if (badge) {
            badge.className = 'badge bg-success-subtle text-success rounded-pill ms-2 fs-6 fw-normal px-3 py-1';
            badge.textContent = 'OpenAI (ChatGPT)';
        }
        if (tableBtn) tableBtn.style.display = 'none';
    } else if (provider === 'ollama') {
        if (badge) {
            badge.className = 'badge bg-warning-subtle text-dark rounded-pill ms-2 fs-6 fw-normal px-3 py-1';
            badge.textContent = 'Local LLM (Ollama)';
        }
        if (tableBtn) tableBtn.style.display = 'none';
    }

    // 5. Update helper description
    const desc = document.getElementById('provider_description');
    if (desc) {
        if (provider === 'gemini') {
            desc.innerHTML = '<span class="text-primary"><i class="fab fa-google me-1"></i> แนะนำ Cloud เร็ว & ฟรี/คุ้มค่า (Gemini 2.0 Flash / 1.5 Pro)</span>';
        } else if (provider === 'openai') {
            desc.innerHTML = '<span class="text-success"><i class="fas fa-brain me-1"></i> OpenAI Official Cloud (GPT-4o / GPT-4o-mini)</span>';
        } else if (provider === 'ollama') {
            desc.innerHTML = '<span class="text-warning"><i class="fas fa-server me-1"></i> 100% Offline / PDPA ปลอดภัยบนเซิร์ฟเวอร์โรงพยาบาล</span>';
        }
    }
}

function testActiveConnection() {
    const provider = document.getElementById('active_provider_select')?.value || 'gemini';
    testConnection(provider);
}

function selectGeminiChatModel(modelName, btn) {
    const input = document.getElementById('gemini_model');
    if (input) input.value = modelName;

    document.querySelectorAll('#gemini-chat-pills .gemini-pill').forEach(el => el.classList.remove('active'));
    if (btn) {
        btn.classList.add('active');
    } else {
        const found = document.querySelector(`#gemini-chat-pills .gemini-pill[data-model="${modelName}"]`);
        if (found) found.classList.add('active');
    }

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'เลือก Chat Model: ' + modelName,
        showConfirmButton: false,
        timer: 1800
    });
}

function selectGeminiEmbedModel(modelName, btn) {
    const input = document.getElementById('gemini_embed_model');
    if (input) input.value = modelName;

    document.querySelectorAll('#gemini-embed-pills .gemini-pill').forEach(el => el.classList.remove('active'));
    if (btn) {
        btn.classList.add('active');
    } else {
        const found = document.querySelector(`#gemini-embed-pills .gemini-pill[data-model="${modelName}"]`);
        if (found) found.classList.add('active');
    }

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'เลือก Embedding Model: ' + modelName,
        showConfirmButton: false,
        timer: 1800
    });
}

function selectOpenAiChat(modelName, btn) {
    const input = document.getElementById('openai_model');
    if (input) input.value = modelName;
    document.querySelectorAll('#openai-chat-pills .gemini-pill').forEach(el => el.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

function selectOpenAiEmbed(modelName, btn) {
    const input = document.getElementById('openai_embed_model');
    if (input) input.value = modelName;
    document.querySelectorAll('#openai-embed-pills .gemini-pill').forEach(el => el.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

function selectOllamaChat(modelName, btn) {
    const input = document.getElementById('ollama_model');
    if (input) input.value = modelName;
    document.querySelectorAll('#ollama-chat-pills .gemini-pill').forEach(el => el.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

function selectOllamaEmbed(modelName, btn) {
    const input = document.getElementById('ollama_embed_model');
    if (input) input.value = modelName;
    document.querySelectorAll('#ollama-embed-pills .gemini-pill').forEach(el => el.classList.remove('active'));
    if (btn) btn.classList.add('active');
}

function fetchGeminiModels() {
    const key = document.getElementById('gemini_api_key').value.trim();
    if (!key) {
        Swal.fire({
            icon: 'warning',
            title: 'ยังไม่ได้ระบุ API Key',
            text: 'กรุณากรอก Gemini API Key ก่อนดึงรายชื่อโมเดล'
        });
        return;
    }

    Swal.fire({
        title: 'กำลังเชื่อมต่อ Google Gemini...',
        text: 'กำลังค้นหาและตรวจสอบ Model จาก Google Cloud API',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('{{ route('admin.ai.settings.gemini_models') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ gemini_api_key: key })
    })
    .then(res => res.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            cachedGeminiModels = data;
            const chatCount = data.chat_models ? data.chat_models.length : 0;
            const embedCount = data.embed_models ? data.embed_models.length : 0;

            // 1. Auto-detect recommended models
            let recChat = '';
            const chatIds = (data.chat_models || []).map(m => m.id);
            const preferredChat = [
                'gemini-3.7-flash',
                'gemini-2.5-flash',
                'gemini-2.0-flash',
                'gemini-1.5-flash',
                'gemini-1.5-pro'
            ];
            for (const pref of preferredChat) {
                if (chatIds.includes(pref)) {
                    recChat = pref;
                    break;
                }
            }
            if (!recChat && chatIds.length > 0) {
                recChat = chatIds[0];
            }

            let recEmbed = '';
            const embedIds = (data.embed_models || []).map(m => m.id);
            const preferredEmbed = [
                'gemini-embedding-001',
                'text-embedding-004',
                'embedding-001'
            ];
            for (const pref of preferredEmbed) {
                if (embedIds.includes(pref)) {
                    recEmbed = pref;
                    break;
                }
            }
            if (!recEmbed && embedIds.length > 0) {
                recEmbed = embedIds[0];
            }

            // 2. Auto-populate input fields
            if (recChat) {
                document.getElementById('gemini_model').value = recChat;
            }
            if (recEmbed) {
                document.getElementById('gemini_embed_model').value = recEmbed;
            }

            // 3. Update status badge under Gemini API Key (Image 2)
            const statusBadge = document.getElementById('gemini-status-badge');
            const statusCount = document.getElementById('gemini-status-count');
            if (statusBadge && statusCount) {
                statusBadge.style.display = 'inline-flex';
                statusCount.innerText = `พบ ${chatCount} โมเดลพร้อมใช้`;
            }

            // 4. Render pills and populate modal/datalists
            renderGeminiPills(data, recChat, recEmbed);
            populateGeminiModelsUI(data);

            // 5. SweetAlert Popup matching reference screenshot (Image 1)
            Swal.fire({
                icon: 'success',
                title: '<h3 class="fw-bold text-dark mt-2 mb-2">ค้นหาโมเดลสำเร็จ!</h3>',
                html: `
                    <div class="text-center px-1">
                        <p class="text-success fw-bold fs-6 mb-2">✨ ค้นพบ ${chatCount} โมเดลตอบคำถาม และ ${embedCount} โมเดลเวกเตอร์ จาก Gemini API</p>
                        <p class="text-muted small mb-3">ระบบได้เพิ่มปุ่มลัดรายชื่อโมเดลทั้งหมดที่ Key นี้เข้าถึงได้ลงในแบบฟอร์มแล้ว</p>
                        <div class="p-3 bg-light rounded-3 text-start mb-3 border small">
                            <div class="mb-2">
                                <strong>โมเดลตอบคำถามแนะนำ:</strong> <code class="text-danger fw-bold fs-6">${recChat}</code>
                            </div>
                            <div>
                                <strong>โมเดลเวกเตอร์แนะนำ:</strong> <code class="text-danger fw-bold fs-6">${recEmbed}</code>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">*สามารถคลิกเลือกชื่อโมเดลที่ต้องการได้จากปุ่มด้านล่างช่องกรอก</p>
                    </div>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#6366f1',
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2 rounded-3 fw-bold'
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'ไม่สามารถดึง Model ได้',
                text: data.message || 'โปรดตรวจสอบ API Key ของคุณ'
            });
        }
    })
    .catch(err => {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: err.message
        });
    });
}

function renderGeminiPills(data, activeChat, activeEmbed) {
    const chatContainer = document.getElementById('gemini-chat-pills');
    const embedContainer = document.getElementById('gemini-embed-pills');

    if (chatContainer && data.chat_models) {
        chatContainer.innerHTML = '';
        const chatIds = data.chat_models.map(m => m.id);

        const priorityChat = [
            { id: 'gemini-3.7-flash', label: '⭐ gemini-3.7-flash (แนะนำ)', type: 'rec' },
            { id: 'gemini-2.0-flash', label: '⭐ gemini-2.0-flash (แนะนำ)', type: 'rec' },
            { id: 'gemini-3.5-flash-lite', label: '⚡ gemini-3.5-flash-lite (ตอบไว)', type: 'fast' },
            { id: 'gemini-2.0-flash-lite', label: '⚡ gemini-2.0-flash-lite (ตอบไว)', type: 'fast' },
            { id: 'gemini-flash-latest', label: 'gemini-flash-latest', type: 'normal' },
            { id: 'gemini-1.5-pro', label: 'gemini-1.5-pro', type: 'normal' },
            { id: 'gemini-1.5-flash', label: 'gemini-1.5-flash', type: 'normal' }
        ];

        let rendered = 0;
        priorityChat.forEach(p => {
            if (chatIds.includes(p.id) || p.id === activeChat) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('data-model', p.id);
                btn.className = `gemini-pill pill-${p.type} ${p.id === activeChat ? 'active' : ''}`;
                btn.innerHTML = p.label;
                btn.onclick = function() { selectGeminiChatModel(p.id, this); };
                chatContainer.appendChild(btn);
                rendered++;
            }
        });

        if (rendered === 0) {
            data.chat_models.slice(0, 6).forEach((m, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('data-model', m.id);
                btn.className = `gemini-pill pill-rec ${m.id === activeChat ? 'active' : ''}`;
                btn.innerHTML = (idx === 0 ? '⭐ ' : '') + m.id + (idx === 0 ? ' (แนะนำ)' : '');
                btn.onclick = function() { selectGeminiChatModel(m.id, this); };
                chatContainer.appendChild(btn);
            });
        }
    }

    if (embedContainer && data.embed_models) {
        embedContainer.innerHTML = '';
        const embedIds = data.embed_models.map(m => m.id);

        const priorityEmbed = [
            { id: 'gemini-embedding-001', label: '⭐ gemini-embedding-001', type: 'rec' },
            { id: 'text-embedding-004', label: '⭐ text-embedding-004 (แนะนำ)', type: 'rec' },
            { id: 'embedding-001', label: 'embedding-001', type: 'normal' }
        ];

        let renderedEmbed = 0;
        priorityEmbed.forEach(p => {
            if (embedIds.includes(p.id) || p.id === activeEmbed) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('data-model', p.id);
                btn.className = `gemini-pill pill-${p.type} ${p.id === activeEmbed ? 'active' : ''}`;
                btn.innerHTML = p.label;
                btn.onclick = function() { selectGeminiEmbedModel(p.id, this); };
                embedContainer.appendChild(btn);
                renderedEmbed++;
            }
        });

        if (renderedEmbed === 0) {
            data.embed_models.slice(0, 3).forEach((m, idx) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('data-model', m.id);
                btn.className = `gemini-pill pill-rec ${m.id === activeEmbed ? 'active' : ''}`;
                btn.innerHTML = '⭐ ' + m.id;
                btn.onclick = function() { selectGeminiEmbedModel(m.id, this); };
                embedContainer.appendChild(btn);
            });
        }
    }
}

function populateGeminiModelsUI(data) {
    const tbody = document.getElementById('geminiModelsTableBody');
    if (tbody) tbody.innerHTML = '';

    const chatDatalist = document.getElementById('gemini_chat_models_datalist');
    const embedDatalist = document.getElementById('gemini_embed_models_datalist');
    const chatDropdown = document.getElementById('gemini_chat_dropdown');
    const embedDropdown = document.getElementById('gemini_embed_dropdown');

    if (chatDatalist) chatDatalist.innerHTML = '';
    if (embedDatalist) embedDatalist.innerHTML = '';
    if (chatDropdown) chatDropdown.innerHTML = '<li><h6 class="dropdown-header">โมเดลที่พบบน API ของคุณ</h6></li>';
    if (embedDropdown) embedDropdown.innerHTML = '<li><h6 class="dropdown-header">โมเดล Embedding ที่พบ</h6></li>';

    let total = 0;

    // 1. Chat Models
    if (data.chat_models && data.chat_models.length > 0) {
        data.chat_models.forEach(m => {
            total++;
            if (tbody) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><code class="fw-bold text-primary">${m.id}</code></td>
                    <td>
                        <div class="fw-bold text-dark">${m.name || m.id}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">${m.description || ''}</div>
                    </td>
                    <td><span class="badge bg-primary-subtle text-primary rounded-pill">Chat / Generation</span></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1" onclick="selectGeminiChatModel('${m.id}')" data-bs-dismiss="modal">
                            เลือกใช้
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            if (chatDatalist) {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.innerText = m.name || m.id;
                chatDatalist.appendChild(opt);
            }

            if (chatDropdown) {
                const li = document.createElement('li');
                li.innerHTML = `<a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiChatModel('${m.id}')"><strong>${m.id}</strong> <small class="text-muted">(${m.name || ''})</small></a>`;
                chatDropdown.appendChild(li);
            }
        });
    }

    // 2. Embed Models
    if (data.embed_models && data.embed_models.length > 0) {
        data.embed_models.forEach(m => {
            total++;
            if (tbody) {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><code class="fw-bold text-info">${m.id}</code></td>
                    <td>
                        <div class="fw-bold text-dark">${m.name || m.id}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">${m.description || ''}</div>
                    </td>
                    <td><span class="badge bg-info-subtle text-info rounded-pill">Vector Embedding</span></td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 py-1" onclick="selectGeminiEmbedModel('${m.id}')" data-bs-dismiss="modal">
                            เลือกใช้
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            if (embedDatalist) {
                const opt = document.createElement('option');
                opt.value = m.id;
                embedDatalist.appendChild(opt);
            }

            if (embedDropdown) {
                const li = document.createElement('li');
                li.innerHTML = `<a class="dropdown-item" href="javascript:void(0)" onclick="selectGeminiEmbedModel('${m.id}')"><strong>${m.id}</strong></a>`;
                embedDropdown.appendChild(li);
            }
        });
    }

    const countLabel = document.getElementById('modalTotalModelsCount');
    if (countLabel) countLabel.innerText = `${total} รุ่นที่รองรับ`;
}

function filterGeminiModelsTable() {
    const filter = document.getElementById('modelFilterInput').value.toLowerCase();
    const rows = document.querySelectorAll('#geminiModelsTableBody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(filter) ? '' : 'none';
    });
}

function applyGeminiPreset(chatModel, embedModel, name, desc) {
    selectGeminiChatModel(chatModel);
    selectGeminiEmbedModel(embedModel);

    document.querySelectorAll('.gemini-preset-item').forEach(el => el.classList.remove('border-primary', 'bg-primary-subtle'));
    if (window.event && window.event.currentTarget) {
        window.event.currentTarget.classList.add('border-primary', 'bg-primary-subtle');
    }

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'เลือก ' + name + ' แล้ว!',
        html: '<small class="text-muted">' + desc + '<br><b class="text-primary">อย่าลืมกดปุ่ม "บันทึกการตั้งค่า"</b></small>',
        showConfirmButton: false,
        timer: 3500
    });
}

function applyOpenAiPreset(chatModel, embedModel, name, desc) {
    selectOpenAiChat(chatModel);
    selectOpenAiEmbed(embedModel);

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'เลือก ' + name + ' แล้ว!',
        html: '<small class="text-muted">' + desc + '<br><b class="text-success">อย่าลืมกดปุ่ม "บันทึกการตั้งค่า"</b></small>',
        showConfirmButton: false,
        timer: 3500
    });
}

function applyOllamaPreset(chatModel, embedModel, name, desc) {
    selectOllamaChat(chatModel);
    selectOllamaEmbed(embedModel);

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'เลือก ' + name + ' แล้ว!',
        html: '<small class="text-muted">' + desc + '<br><b class="text-warning">อย่าลืมกดปุ่ม "บันทึกการตั้งค่า"</b></small>',
        showConfirmButton: false,
        timer: 3500
    });
}

function testConnection(provider) {
    const resultBox = document.getElementById('active-test-result') || document.getElementById(provider + '-test-result');
    if (resultBox) {
        resultBox.style.display = 'block';
        resultBox.innerHTML = '<div class="alert alert-secondary py-2 px-3 small mb-0"><i class="fas fa-spinner fa-spin me-2"></i> กำลังทดสอบเชื่อมต่อกับ ' + provider + '...</div>';
    }

    Swal.fire({
        title: 'กำลังทดสอบเชื่อมต่อ...',
        html: '<div class="py-2"><i class="fas fa-satellite-dish fa-spin fs-2 text-primary mb-3"></i><p class="text-muted small mb-0">กำลังส่งคำขอทดสอบการเชื่อมต่อและตรวจวัด Latency กับ ' + provider + '...</p></div>',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const payload = {
        _token: '{{ csrf_token() }}',
        provider: provider,
        gemini_api_key: document.getElementById('gemini_api_key') ? document.getElementById('gemini_api_key').value : '',
        gemini_model: document.getElementById('gemini_model') ? document.getElementById('gemini_model').value : '',
        gemini_embed_model: document.getElementById('gemini_embed_model') ? document.getElementById('gemini_embed_model').value : '',
        openai_api_key: document.getElementById('openai_api_key') ? document.getElementById('openai_api_key').value : '',
        openai_model: document.getElementById('openai_model') ? document.getElementById('openai_model').value : '',
        openai_embed_model: document.getElementById('openai_embed_model') ? document.getElementById('openai_embed_model').value : '',
        ollama_base_url: document.getElementById('ollama_base_url') ? document.getElementById('ollama_base_url').value : '',
        ollama_model: document.getElementById('ollama_model') ? document.getElementById('ollama_model').value : '',
        ollama_embed_model: document.getElementById('ollama_embed_model') ? document.getElementById('ollama_embed_model').value : '',
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
            if (resultBox) {
                resultBox.innerHTML = '<div class="alert alert-success py-2 px-3 small mb-0"><i class="fas fa-check-circle me-1"></i> ' + data.message + ' <span class="badge bg-success ms-1">' + (data.latency_ms || 0) + ' ms</span></div>';
            }

            let modelName = data.model;
            if (!modelName) {
                if (provider === 'gemini') modelName = document.getElementById('gemini_model')?.value || 'gemini-2.0-flash';
                else if (provider === 'openai') modelName = document.getElementById('openai_model')?.value || 'gpt-4o-mini';
                else modelName = document.getElementById('ollama_model')?.value || 'deepseek-r1:latest';
            }

            let replyText = (data.response || 'การเชื่อมต่อระบบ AI สำเร็จ').toString().trim();
            replyText = replyText.replace(/^["'\s]+|["'\s]+$/g, '');

            Swal.fire({
                icon: 'success',
                title: '<h3 class="fw-bold text-dark mt-2 mb-2">เชื่อมต่อ AI สำเร็จ!</h3>',
                html: `
                    <div class="text-center px-1">
                        <div class="mb-2">
                            <span class="text-success fw-bold fs-5">
                                <i class="fas fa-check-circle me-1"></i> ผู้ให้บริการ: ${provider}
                            </span>
                        </div>
                        <div class="text-muted small mb-3">
                            โมเดลที่ตอบ: <code class="text-danger fw-bold" style="font-size: 0.95rem;">${modelName}</code>
                        </div>
                        <div class="p-3 bg-light rounded-3 text-start mb-3 border small" style="background-color: #f8fafc;">
                            <div class="fw-bold text-dark mb-1">การตอบกลับ:</div>
                            <div class="text-dark">"${replyText}"</div>
                        </div>
                    </div>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#6366f1',
                customClass: {
                    confirmButton: 'btn btn-primary px-4 py-2 rounded-3 fw-bold'
                }
            });
        } else {
            if (resultBox) {
                resultBox.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0"><i class="fas fa-times-circle me-1"></i> ' + data.message + '</div>';
            }
            Swal.fire({
                icon: 'error',
                title: '<h4 class="fw-bold text-dark mt-2">เชื่อมต่อ AI ไม่สำเร็จ</h4>',
                html: `
                    <div class="text-start p-3 bg-light rounded-3 border small text-danger">
                        ${data.message || 'เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาตรวจสอบการตั้งค่า'}
                    </div>
                `,
                confirmButtonText: 'ปิด',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(err => {
        if (resultBox) {
            resultBox.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0"><i class="fas fa-times-circle me-1"></i> ไม่สามารถส่งคำขอได้: ' + err.message + '</div>';
        }
        Swal.fire({
            icon: 'error',
            title: '<h4 class="fw-bold text-dark mt-2">เกิดข้อผิดพลาด</h4>',
            text: 'ไม่สามารถส่งคำขอได้: ' + err.message,
            confirmButtonText: 'ปิด',
            confirmButtonColor: '#dc3545'
        });
    });
}

// Initial synchronization on page load
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initial provider display switch
    const curProvider = document.getElementById('active_provider_select')?.value || 'gemini';
    switchProvider(curProvider);

    // 2. Sync Gemini Chat Pill
    const curChat = document.getElementById('gemini_model')?.value.trim();
    if (curChat) {
        const p = document.querySelector(`#gemini-chat-pills .gemini-pill[data-model="${curChat}"]`);
        if (p) p.classList.add('active');
    }

    // 3. Sync Gemini Embed Pill
    const curEmbed = document.getElementById('gemini_embed_model')?.value.trim();
    if (curEmbed) {
        const p = document.querySelector(`#gemini-embed-pills .gemini-pill[data-model="${curEmbed}"]`);
        if (p) p.classList.add('active');
    }

    // 4. Input live listeners for pill highlights
    document.getElementById('gemini_model')?.addEventListener('input', function() {
        const val = this.value.trim();
        document.querySelectorAll('#gemini-chat-pills .gemini-pill').forEach(el => {
            if (el.getAttribute('data-model') === val) el.classList.add('active');
            else el.classList.remove('active');
        });
    });

    document.getElementById('gemini_embed_model')?.addEventListener('input', function() {
        const val = this.value.trim();
        document.querySelectorAll('#gemini-embed-pills .gemini-pill').forEach(el => {
            if (el.getAttribute('data-model') === val) el.classList.add('active');
            else el.classList.remove('active');
        });
    });
});
</script>
@endpush
@endsection
