@extends('layouts.app')

@section('title', 'คลังความรู้โรงพยาบาล (Hospital Knowledge Library) - SmartData')

@section('content')
<div class="container py-4">
    <!-- Header Hero Banner -->
    <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);">
        <div class="card-body p-4 p-md-5 text-white">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="badge bg-white text-primary rounded-pill px-3 py-2 mb-3 fw-bold">
                        <i class="fas fa-book-reader me-1"></i> Hospital Digital Library & CPG
                    </span>
                    <h1 class="fw-bold mb-2 display-6">คลังความรู้โรงพยาบาล</h1>
                    <p class="lead mb-0 text-white-50 fs-6">
                        ศูนย์รวมแนวทางการรักษา (CPG), นโยบายคุณภาพ, ระเบียบปฏิบัติ และคู่มือการทำงาน สามารถเปิดอ่าน พิมพ์ หรือดาวน์โหลดได้โดยตรง พร้อมมีระบบ AI ช่วยสรุปเนื้อหา
                    </p>
                </div>
                <div class="col-lg-4 text-center text-lg-end mt-3 mt-lg-0">
                    <a href="{{ route('ai.chat') }}" class="btn btn-light btn-lg rounded-pill px-4 shadow-sm text-primary fw-bold">
                        <i class="fas fa-robot me-2"></i> เปิด SmartData Copilot
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('ai.knowledge.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted ps-3">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control bg-light border-0 py-2" placeholder="ค้นหาชื่อเอกสาร, แนวทาง CPG, ระเบียบ...">
                        @if($search || $category)
                            <a href="{{ route('ai.knowledge.index') }}" class="btn btn-light border-0 text-muted" title="ล้างการค้นหา">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select bg-light border-0 py-2" onchange="this.form.submit()">
                        <option value="">-- ทุกหมวดหมู่ --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 py-2">
                        <i class="fas fa-search me-1"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Grid -->
    <div class="row g-4">
        @forelse($docs as $doc)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 transition-all hover-translate-y">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="p-3 rounded-4 
                            @if($doc->file_type === 'pdf') bg-danger-subtle text-danger 
                            @elseif(in_array($doc->file_type, ['doc', 'docx'])) bg-primary-subtle text-primary 
                            @else bg-secondary-subtle text-secondary @endif">
                            @if($doc->file_type === 'pdf')
                                <i class="fas fa-file-pdf fa-2x"></i>
                            @elseif(in_array($doc->file_type, ['doc', 'docx']))
                                <i class="fas fa-file-word fa-2x"></i>
                            @else
                                <i class="fas fa-file-alt fa-2x"></i>
                            @endif
                        </div>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1 small">
                            {{ $doc->category }}
                        </span>
                    </div>

                    <h5 class="fw-bold text-dark mb-2 line-clamp-2" title="{{ $doc->title }}">
                        {{ $doc->title }}
                    </h5>
                    <div class="text-muted small mb-3 text-truncate">
                        <i class="fas fa-paperclip me-1"></i> {{ $doc->filename }}
                    </div>

                    <div class="mt-auto">
                        <div class="d-flex justify-content-between align-items-center text-muted small border-top pt-3 mb-3">
                            <span><i class="fas fa-weight-hanging me-1"></i> {{ $doc->formatted_file_size }}</span>
                            <span><i class="far fa-calendar-alt me-1"></i> {{ $doc->created_at ? $doc->created_at->format('d/m/Y') : '-' }}</span>
                        </div>

                        <div class="d-flex gap-2">
                            <!-- View / Preview -->
                            <button type="button" class="btn btn-outline-primary rounded-pill btn-sm flex-fill" onclick="previewDoc('{{ route('ai.knowledge.view', $doc->id) }}', '{{ addslashes($doc->title) }}', '{{ $doc->file_type }}')">
                                <i class="fas fa-eye me-1"></i> เปิดอ่าน
                            </button>

                            <!-- Direct Download -->
                            <a href="{{ route('ai.knowledge.download', $doc->id) }}" class="btn btn-light rounded-pill btn-sm px-3" title="ดาวน์โหลดไฟล์ต้นฉบับ">
                                <i class="fas fa-download text-secondary"></i>
                            </a>

                            <!-- Ask AI About This Doc -->
                            <a href="{{ route('ai.chat') }}?doc_id={{ $doc->id }}&message={{ urlencode('ช่วยสรุปสาระสำคัญของเอกสาร "' . $doc->title . '" ให้หน่อย') }}" class="btn btn-light rounded-pill btn-sm px-3 text-info" title="ถาม AI ผู้ช่วยเกี่ยวกับเล่มนี้">
                                <i class="fas fa-robot"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="text-muted">
                <i class="fas fa-book-open fa-3x mb-3 text-light-emphasis d-block"></i>
                <h5>ไม่พบเอกสารตามเงื่อนไขที่ค้นหา</h5>
                <p class="small">ลองค้นหาด้วยคำอื่น หรือเลือกดูทุกหมวดหมู่</p>
                <a href="{{ route('ai.knowledge.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                    ดูเอกสารทั้งหมด
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($docs->hasPages())
    <div class="d-flex justify-content-center mt-5">
        {{ $docs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- PDF Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="height: 90vh;">
        <div class="modal-content border-0 shadow-lg rounded-4 h-100">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4 rounded-top-4">
                <h5 class="modal-title fw-bold text-truncate" id="previewModalLabel">
                    <i class="fas fa-file-pdf text-danger me-2"></i><span id="previewTitle">เอกสาร</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a id="previewDownloadBtn" href="#" class="btn btn-sm btn-outline-light rounded-pill px-3">
                        <i class="fas fa-download me-1"></i> ดาวน์โหลด
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 bg-secondary-subtle d-flex flex-column h-100">
                <iframe id="previewIframe" src="" class="w-100 h-100 border-0 flex-grow-1" style="min-height: 500px;"></iframe>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.hover-translate-y:hover {
    transform: translateY(-5px);
}
.transition-all {
    transition: all 0.25s ease-in-out;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush

@push('scripts')
<script>
function previewDoc(url, title, type) {
    if (type === 'pdf' || type === 'txt' || type === 'md') {
        document.getElementById('previewTitle').innerText = title;
        document.getElementById('previewIframe').src = url;
        document.getElementById('previewDownloadBtn').href = url.replace('/view', '/download');
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    } else {
        // For Word/Docx, download directly
        window.location.href = url.replace('/view', '/download');
    }
}
</script>
@endpush
@endsection
