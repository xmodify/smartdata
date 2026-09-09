@extends('layouts.admin')

@section('title', 'จัดการคลังความรู้ AI (RAG & Vector) - SmartData')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จัดการคลังความรู้ AI</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">
                <i class="fas fa-book-medical text-success me-2"></i>จัดการคลังความรู้ AI (Hospital Knowledge RAG)
            </h2>
            <p class="text-muted small mb-0">อัปโหลดเอกสาร CPG, ระเบียบ, คู่มือ ตัด Chunk และจัดเก็บ Vector ใน MySQL สำหรับ SmartData Copilot</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('ai.knowledge.index') }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3">
                <i class="fas fa-external-link-alt me-1"></i> มุมมองผู้ใช้ (User Library)
            </a>
            <button type="button" class="btn btn-outline-info rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#testSearchModal">
                <i class="fas fa-search me-1"></i> ทดสอบสืบค้น Vector
            </button>
            <button type="button" class="btn btn-outline-warning rounded-pill px-3 text-dark" onclick="confirmReEmbedAll()">
                <i class="fas fa-sync-alt me-1"></i> Re-Embed ทั้งหมด
            </button>
            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-plus-circle me-1"></i> อัปโหลดเอกสารใหม่
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 me-3">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">เอกสารทั้งหมดในคลัง</div>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['total_docs']) }} <small class="text-muted fs-6">เล่ม</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success-subtle text-success p-3 me-3">
                        <i class="fas fa-check-double fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">แปลง Vector แล้ว (Indexed)</div>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['indexed_docs']) }} <small class="text-muted fs-6">เล่ม</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info-subtle text-info p-3 me-3">
                        <i class="fas fa-layer-group fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">จำนวน Chunks ใน MySQL</div>
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['total_chunks']) }} <small class="text-muted fs-6">ท่อน</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 me-3">
                        <i class="fas fa-robot fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">โมเดล Active ขณะนี้</div>
                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 170px;" title="{{ $stats['active_provider'] }}">{{ $stats['active_provider'] }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-folder-open text-primary me-2"></i>รายการเอกสารความรู้ทั้งหมด
            </h5>
            <span class="text-muted small">จัดเก็บที่ <code>storage/app/public/knowledge/</code></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ชื่อเอกสาร / ไฟล์</th>
                            <th>หมวดหมู่</th>
                            <th>ขนาด</th>
                            <th>สถานะ Vector</th>
                            <th>Chunks</th>
                            <th>โมเดล Embedding</th>
                            <th>วันที่นำเข้า</th>
                            <th class="text-end pe-4">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($docs as $doc)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3 fs-3">
                                        @if($doc->file_type === 'pdf')
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        @elseif(in_array($doc->file_type, ['doc', 'docx']))
                                            <i class="fas fa-file-word text-primary"></i>
                                        @else
                                            <i class="fas fa-file-alt text-secondary"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $doc->title }}</div>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $doc->filename }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $doc->category }}</span>
                            </td>
                            <td class="small text-muted">{{ $doc->formatted_file_size }}</td>
                            <td>
                                @if($doc->status === 'indexed')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                        <i class="fas fa-check-circle me-1"></i> Indexed
                                    </span>
                                @elseif($doc->status === 'indexing')
                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 py-1">
                                        <i class="fas fa-spinner fa-spin me-1"></i> กำลังแปลง...
                                    </span>
                                @elseif($doc->status === 'failed')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1" title="{{ $doc->error_message }}">
                                        <i class="fas fa-exclamation-circle me-1"></i> ผิดพลาด
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">
                                        <i class="fas fa-clock me-1"></i> ยังไม่แปลง
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1">
                                    {{ $doc->chunks_count }}
                                </span>
                            </td>
                            <td class="small text-muted text-truncate" style="max-width: 140px;" title="{{ $doc->embedded_model }}">
                                {{ $doc->embedded_model ?: '-' }}
                            </td>
                            <td class="small text-muted">{{ $doc->created_at ? $doc->created_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <!-- View/Preview -->
                                    <a href="{{ route('ai.knowledge.view', $doc->id) }}" target="_blank" class="btn btn-light" title="เปิดอ่านพรีวิว">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                    <!-- Download -->
                                    <a href="{{ route('ai.knowledge.download', $doc->id) }}" class="btn btn-light" title="ดาวน์โหลดไฟล์ต้นฉบับ">
                                        <i class="fas fa-download text-muted"></i>
                                    </a>
                                    <!-- Re-Embed Single -->
                                    <form action="{{ route('admin.ai.knowledge.embed', $doc->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-light" title="Re-Embed เอกสารนี้ใหม่">
                                            <i class="fas fa-sync-alt text-warning"></i>
                                        </button>
                                    </form>
                                    <!-- Delete Cascade -->
                                    <form action="{{ route('admin.ai.knowledge.destroy', $doc->id) }}" method="POST" class="d-inline delete-doc-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-light" title="ลบเอกสารและ Vector ทั้งหมด" onclick="confirmDelete(this, '{{ addslashes($doc->title) }}')">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-light-emphasis d-block"></i>
                                ยังไม่มีเอกสารในคลังความรู้ AI คลิกปุ่ม <strong>"อัปโหลดเอกสารใหม่"</strong> เพื่อเริ่มต้น
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold" id="uploadModalLabel">
                    <i class="fas fa-cloud-upload-alt me-2"></i>อัปโหลดเอกสารเข้าคลังความรู้ AI
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.ai.knowledge.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ชื่อเอกสาร / แนวทางปฏิบัติ</label>
                        <input type="text" name="title" class="form-control bg-light border-0 shadow-sm" placeholder="เช่น CPG แนวทางการรักษาโรคหลอดเลือดสมอง (Stroke) 2568" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">หมวดหมู่เอกสาร</label>
                        <input type="text" name="category" class="form-control bg-light border-0 shadow-sm" placeholder="เช่น แนวทางการรักษา CPG, นโยบายโรงพยาบาล, คู่มือระบบ" list="categoryList" required>
                        <datalist id="categoryList">
                            <option value="แนวทางการรักษา CPG">
                            <option value="นโยบายและความปลอดภัย">
                            <option value="คู่มือระบบสารสนเทศ">
                            <option value="ระเบียบและข้อบังคับ">
                            <option value="มาตรฐานงานบริการ">
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">เลือกไฟล์เอกสาร (PDF, DOCX, TXT, MD)</label>
                        <input type="file" name="document" class="form-control bg-light border-0 shadow-sm" accept=".pdf,.docx,.txt,.md" required>
                        <small class="text-muted">ขนาดไม่เกิน 50 MB ระบบจะจัดเก็บไฟล์ต้นฉบับไว้ให้ผู้ใช้เปิดอ่านและดาวน์โหลดได้</small>
                    </div>
                    <div class="form-check form-switch p-3 bg-light rounded-3 mt-4">
                        <input class="form-check-input ms-0 me-3" type="checkbox" name="embed_immediately" id="embed_immediately" value="1" checked>
                        <label class="form-check-label fw-bold text-dark" for="embed_immediately">
                            <i class="fas fa-magic text-warning me-1"></i> แปลงเป็น Vector Embedding ทันที
                        </label>
                        <div class="small text-muted mt-1">หากเลือก ระบบจะตัดข้อความเป็น Chunks และเรียกโมเดลสร้าง Vector บันทึกลง MySQL ทันที</div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow">
                        <i class="fas fa-upload me-1"></i> เริ่มอัปโหลด
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Search Modal -->
<div class="modal fade" id="testSearchModal" tabindex="-1" aria-labelledby="testSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-info text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold" id="testSearchModalLabel">
                    <i class="fas fa-search me-2"></i>ทดสอบสืบค้นคลังความรู้ (Semantic Vector Search)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">ทดสอบจำลองว่าเมื่อผู้ใช้ถามคำถามนี้ ระบบ Cosine Similarity จะดึงเนื้อหาท่อนใดจาก MySQL และ AI จะตอบอย่างไร</p>
                <div class="input-group mb-3">
                    <input type="text" id="test-search-query" class="form-control bg-light border-0 shadow-sm" placeholder="พิมพ์คำถามทดสอบ เช่น ขั้นตอนการดูแลผู้ป่วย STEMI...">
                    <button class="btn btn-info text-white px-4" type="button" onclick="runSemanticSearch()">
                        <i class="fas fa-search me-1"></i> ค้นหา
                    </button>
                </div>
                <div id="test-search-loading" style="display: none;" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-info mb-2"></i>
                    <p class="text-muted small">กำลังแปลงคำถามเป็น Vector และคำนวณ Cosine Similarity...</p>
                </div>
                <div id="test-search-result" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(btn, title) {
    Swal.fire({
        title: 'ยืนยันการลบเอกสาร?',
        html: `ต้องการลบ <strong>"${title}"</strong> ใช่หรือไม่?<br><small class="text-danger">ระบบจะทำการลบไฟล์ต้นฉบับ, Chunks และ Vector ทั้งหมดออกจากฐานข้อมูล MySQL อย่างสะอาด (Cascade Delete)</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-trash me-1"></i> ลบเอกสาร',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            btn.closest('form').submit();
        }
    });
}

function confirmReEmbedAll() {
    Swal.fire({
        title: 'Re-Embed เอกสารทั้งหมด?',
        html: 'ระบบจะทำการสร้าง Vector ใหม่ให้กับทุกเอกสารในคลังโดยใช้ <strong>{{ $stats['active_provider'] }}</strong><br><small class="text-muted">แนะนำให้ทำเมื่อมีการสลับ AI Provider</small>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        confirmButtonText: '<span class="text-dark"><i class="fas fa-sync-alt me-1"></i> เริ่มต้นแปลงใหม่</span>',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'กำลัง Re-Embed เอกสาร...',
                text: 'กรุณารอสักครู่ ระบบกำลังประมวลผล Chunks และเรียก Embedding API',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('{{ route('admin.ai.knowledge.reembed_all') }}', {
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
                    Swal.fire('สำเร็จ!', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถ Re-Embed ได้', 'error');
                }
            })
            .catch(err => {
                Swal.fire('ผิดพลาด', err.message, 'error');
            });
        }
    });
}

function runSemanticSearch() {
    const query = document.getElementById('test-search-query').value.trim();
    if (!query) return;

    const loading = document.getElementById('test-search-loading');
    const resultBox = document.getElementById('test-search-result');
    loading.style.display = 'block';
    resultBox.style.display = 'none';

    fetch('{{ route('admin.ai.knowledge.test_search') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ query: query })
    })
    .then(res => res.json())
    .then(data => {
        loading.style.display = 'none';
        resultBox.style.display = 'block';

        if (data.success) {
            let html = `<div class="alert alert-success border-0 rounded-3 mb-3">
                <h6 class="fw-bold mb-1"><i class="fas fa-robot me-1"></i> คำตอบที่ AI สรุปได้:</h6>
                <p class="mb-0" style="white-space: pre-wrap;">${data.answer}</p>
            </div>`;

            if (data.sources && data.sources.length > 0) {
                html += `<h6 class="fw-bold text-dark mb-2"><i class="fas fa-book-open me-1"></i> Chunks ที่คะแนนความคล้ายคลึงสูงสุด (Top Matches):</h6>`;
                data.sources.forEach((src, idx) => {
                    html += `<div class="card border-0 bg-light rounded-3 mb-2 p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold text-primary small">#${idx+1} [${src.title}] (Chunk #${src.chunk_index})</span>
                            <span class="badge bg-success-subtle text-success">Cosine Score: ${src.score}</span>
                        </div>
                        <p class="small text-muted mb-0">${src.snippet}</p>
                    </div>`;
                });
            }

            resultBox.innerHTML = html;
        } else {
            resultBox.innerHTML = `<div class="alert alert-warning border-0 rounded-3">${data.answer || data.message}</div>`;
        }
    })
    .catch(err => {
        loading.style.display = 'none';
        resultBox.style.display = 'block';
        resultBox.innerHTML = `<div class="alert alert-danger border-0 rounded-3">เกิดข้อผิดพลาด: ${err.message}</div>`;
    });
}
</script>
@endpush
@endsection
