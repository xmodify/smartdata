@extends('layouts.app')

@section('title', 'รายการร้องเรียน / เสนอแนะ / ชมเชย')

@section('content')
<div class="container-fluid px-4">

    {{-- Page header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-0" style="color:#1565c0;">
                <i class="fas fa-comment-dots me-2" style="color:#00897b;"></i>
                รายการแสดงความคิดเห็น / เสนอแนะ / ร้องเรียน
            </h4>
            <p class="text-muted small mb-0 mt-1">ข้อมูลที่ประชาชนส่งเข้ามาผ่านแบบฟอร์มออนไลน์</p>
        </div>
        <a href="{{ route('customer_complain.create') }}" target="_blank"
           class="btn btn-sm btn-outline-primary">
            <i class="fas fa-external-link-alt me-1"></i> เปิดหน้าฟอร์ม
        </a>
    </div>

    {{-- URL Share Card --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:10px; overflow:hidden;">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center gap-2">
                <span class="small fw-semibold text-muted text-nowrap">
                    <i class="fas fa-link me-1 text-primary"></i> ลิงก์ฟอร์ม
                </span>
                <input type="text" id="shareUrl"
                       class="form-control form-control-sm bg-light border-0 flex-grow-1"
                       style="font-size:.82rem; border-radius:6px;"
                       value="{{ route('customer_complain.create') }}"
                       readonly>
                <button class="btn btn-primary btn-sm px-3 text-nowrap" id="copyBtn"
                        onclick="copyUrl()" style="border-radius:6px;">
                    <i class="fas fa-copy me-1"></i> Copy
                </button>
                <button class="btn btn-outline-secondary btn-sm text-nowrap" onclick="showQR()" style="border-radius:6px;">
                    <i class="fas fa-qrcode me-1"></i> QR Code
                </button>
            </div>
        </div>
    </div>

    {{-- Stats row --}}
    @php
        $total    = $complains->total();
        $praise   = \App\Models\CustomerComplain::where('type','คำชมเชย')->count();
        $suggest  = \App\Models\CustomerComplain::where('type','ข้อเสนอแนะ')->count();
        $complain = \App\Models\CustomerComplain::where('type','ข้อร้องเรียน')->count();
    @endphp
    <div class="row g-3 mb-4">
        @foreach([
            ['label'=>'ทั้งหมด',      'val'=>$total,    'icon'=>'fa-comments',      'color'=>'#1976d2', 'bg'=>'#e3f0fd'],
            ['label'=>'คำชมเชย',      'val'=>$praise,   'icon'=>'fa-thumbs-up',     'color'=>'#388e3c', 'bg'=>'#e8f5e9'],
            ['label'=>'ข้อเสนอแนะ',  'val'=>$suggest,  'icon'=>'fa-lightbulb',     'color'=>'#f57c00', 'bg'=>'#fff3e0'],
            ['label'=>'ข้อร้องเรียน','val'=>$complain, 'icon'=>'fa-bullhorn',      'color'=>'#c62828', 'bg'=>'#ffebee'],
        ] as $s)
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="width:44px;height:44px;border-radius:10px;background:{{ $s['bg'] }};
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas {{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:18px;"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:1.35rem;color:{{ $s['color'] }};line-height:1.1;">
                            {{ $s['val'] }}
                        </div>
                        <div class="small text-muted">{{ $s['label'] }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size:.875rem;">
                    <thead style="background:#f1f5fb;">
                        <tr>
                            <th class="ps-4 py-3 text-muted fw-semibold" style="width:50px;">#</th>
                            <th class="py-3 text-muted fw-semibold">ประเภท</th>
                            <th class="py-3 text-muted fw-semibold">ชื่อ-สกุล</th>
                            <th class="py-3 text-muted fw-semibold">รายละเอียด</th>
                            <th class="py-3 text-muted fw-semibold">ติดต่อกลับ</th>
                            <th class="py-3 text-muted fw-semibold">เบอร์ / Email</th>
                            <th class="py-3 text-muted fw-semibold">สถานะ</th>
                            <th class="pe-4 py-3 text-muted fw-semibold">วันที่รับ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($complains as $item)
                        <tr>
                            <td class="ps-4 text-muted">{{ $loop->iteration + ($complains->currentPage()-1) * $complains->perPage() }}</td>

                            {{-- Type badge --}}
                            <td>
                                @php
                                    $typeMap = [
                                        'คำชมเชย'     => ['bg'=>'#e8f5e9','color'=>'#2e7d32','icon'=>'fa-thumbs-up'],
                                        'ข้อเสนอแนะ' => ['bg'=>'#fff3e0','color'=>'#e65100','icon'=>'fa-lightbulb'],
                                        'ข้อร้องเรียน'=> ['bg'=>'#ffebee','color'=>'#b71c1c','icon'=>'fa-bullhorn'],
                                        'อื่น ๆ'     => ['bg'=>'#f3e5f5','color'=>'#6a1b9a','icon'=>'fa-ellipsis'],
                                    ];
                                    $t = $typeMap[$item->type] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','icon'=>'fa-circle'];
                                @endphp
                                <span class="badge d-inline-flex align-items-center gap-1 px-2 py-1"
                                      style="background:{{ $t['bg'] }};color:{{ $t['color'] }};border-radius:7px;font-weight:600;font-size:.78rem;">
                                    <i class="fas {{ $t['icon'] }}" style="font-size:11px;"></i>
                                    {{ $item->type }}
                                </span>
                            </td>

                            <td>{{ $item->name ?? '<span class="text-muted fst-italic small">ไม่ระบุ</span>' }}</td>

                            <td style="max-width:260px;">
                                @if($item->detail)
                                    <span class="d-inline-block text-truncate" style="max-width:240px;"
                                          data-bs-toggle="tooltip" title="{{ $item->detail }}">
                                        {{ $item->detail }}
                                    </span>
                                @else
                                    <span class="text-muted fst-italic small">-</span>
                                @endif
                            </td>

                            {{-- Callback --}}
                            <td>
                                @if($item->call_back === 'ต้องการ')
                                    <span class="badge" style="background:#e3f0fd;color:#1565c0;border-radius:6px;">
                                        <i class="fas fa-phone me-1" style="font-size:10px;"></i> ต้องการ
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            <td class="small">
                                @if($item->phone)
                                    <div><i class="fas fa-mobile-screen me-1 text-muted" style="font-size:11px;"></i>{{ $item->phone }}</div>
                                @endif
                                @if($item->email)
                                    <div><i class="fas fa-envelope me-1 text-muted" style="font-size:11px;"></i>{{ $item->email }}</div>
                                @endif
                                @if(!$item->phone && !$item->email)
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td>
                                @php
                                    $stMap = [
                                        'รอดำเนินการ'      => ['bg'=>'#fff8e1','color'=>'#f57f17'],
                                        'กำลังดำเนินการ'   => ['bg'=>'#e3f0fd','color'=>'#1565c0'],
                                        'เสร็จสิ้น'        => ['bg'=>'#e8f5e9','color'=>'#2e7d32'],
                                    ];
                                    $st = $stMap[$item->status] ?? ['bg'=>'#f1f5f9','color'=>'#64748b'];
                                @endphp
                                <span class="badge px-2 py-1"
                                      style="background:{{ $st['bg'] }};color:{{ $st['color'] }};border-radius:6px;font-size:.78rem;font-weight:600;">
                                    {{ $item->status }}
                                </span>
                            </td>

                            <td class="pe-4 small text-muted">
                                {{ $item->created_at->format('d/m/Y') }}<br>
                                <span style="font-size:.75rem;">{{ $item->created_at->format('H:i') }} น.</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity:.3;"></i>
                                ยังไม่มีข้อมูล
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($complains->hasPages())
            <div class="d-flex justify-content-end px-4 py-3" style="border-top:1px solid #f1f5f9;">
                {{ $complains->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

{{-- QR Code Modal --}}
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h6 class="modal-title fw-bold" id="qrModalLabel">
                    <i class="fas fa-qrcode me-2 text-primary"></i> QR Code ฟอร์มรับความคิดเห็น
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-4 py-3">
                <p class="small text-muted mb-3">สแกน QR Code เพื่อเข้าสู่ฟอร์มแสดงความคิดเห็น</p>
                <div style="display:inline-block; padding:16px; background:#fff;
                            border-radius:14px; border:1px solid #e2e8f0;
                            box-shadow:0 2px 12px rgba(0,0,0,.07);">
                    <img id="qrImage"
                         src="{{ route('customer_complain.qrcode') }}"
                         alt="QR Code"
                         width="240" height="240"
                         style="display:block; border-radius:6px;">
                </div>
                <p class="small text-muted mt-3 mb-0" style="font-size:.75rem; word-break:break-all;">
                    {{ route('customer_complain.create') }}
                </p>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                <button class="btn btn-outline-secondary btn-sm flex-fill" data-bs-dismiss="modal">
                    <i class="fas fa-xmark me-1"></i> ปิด
                </button>
                <button class="btn btn-success btn-sm flex-fill" onclick="downloadQR()">
                    <i class="fas fa-download me-1"></i> โหลด QR Code
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // ─── Tooltip init ───
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el, { placement: 'top' });
    });

    const formUrl     = @json(route('customer_complain.create'));
    const qrcodeRoute = @json(route('customer_complain.qrcode'));

    // ─── Show QR Modal ───
    function showQR() {
        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
        modal.show();
    }

    // ─── Download QR ─── 
    function downloadQR() {
        const link = document.createElement('a');
        link.href     = qrcodeRoute + '?download=1';
        link.download = 'qrcode-customer-complain.png';
        link.click();
    }

    // ─── Copy URL ───
    function copyUrl() {
        navigator.clipboard.writeText(formUrl).then(() => {
            const btn  = document.getElementById('copyBtn');
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
            btn.classList.replace('btn-primary', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.replace('btn-success', 'btn-primary');
            }, 2000);
        });
    }
</script>
@endpush
