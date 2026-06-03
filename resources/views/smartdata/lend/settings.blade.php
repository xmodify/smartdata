@extends('layouts.app')

@section('title', 'ตั้งค่ารายการ - ศูนย์ยืม-คืน')

@push('styles')
<style>
    .settings-card { border-radius: 16px; border: none; }
    .item-row { transition: background 0.15s; }
    .item-row:hover { background: #f8fafc; }
    .badge-active { background:#d1fae5;color:#065f46;font-size:0.75rem;padding:0.3em 0.7em;border-radius:50px;font-weight:700; }
    .badge-inactive { background:#f1f5f9;color:#64748b;font-size:0.75rem;padding:0.3em 0.7em;border-radius:50px;font-weight:700; }
    .form-control, .form-select { border-radius: 10px; border: 1.5px solid #e2e8f0; }
    .form-control:focus, .form-select:focus { border-color: #0268c7; box-shadow: 0 0 0 0.2rem rgba(2,104,199,0.12); }
    .form-label { font-weight: 600; font-size: 0.85rem; color: #374151; }
</style>
@endpush

@section('topbar_actions')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('lend.index') }}" class="text-white-50">ศูนย์ยืม-คืน</a></li>
            <li class="breadcrumb-item active text-white fw-bold">ตั้งค่ารายการ</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid px-3" style="max-width:900px;">
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="{{ route('lend.index') }}" class="btn btn-light btn-sm" style="border-radius:8px;">
            <i class="fas fa-arrow-left me-1"></i> กลับ
        </a>
        <h5 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>ตั้งค่ารายการ</h5>

        @if(auth()->user()->hasAccessRole('admin'))
        <button class="btn btn-sm text-white ms-auto px-3 shadow-sm"
                style="background:linear-gradient(135deg,#0268c7,#17a6a7);border-radius:8px;border:none;"
                data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="fas fa-plus me-1"></i> เพิ่มรายการ
        </button>
        @endif
    </div>

    <div class="card settings-card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="px-4 py-3 small fw-bold text-muted" style="width:50px;">#</th>
                            <th class="py-3 small fw-bold text-muted">ชื่อรายการ</th>
                            <th class="py-3 small fw-bold text-muted">ประเภท</th>
                            <th class="py-3 small fw-bold text-muted text-center">จำนวน (ทั้งหมด)</th>
                            <th class="py-3 small fw-bold text-muted text-center">ว่าง</th>
                            <th class="py-3 small fw-bold text-muted text-center">สถานะ</th>
                            @if(auth()->user()->hasAccessRole('admin'))
                            <th class="py-3 small fw-bold text-muted text-center">จัดการ</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="item-row">
                            <td class="px-4 text-muted small">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->name }}</div>
                                @if($item->description)
                                <small class="text-muted">{{ $item->description }}</small>
                                @endif
                            </td>
                            <td>
                                @if($item->category === 'equipment')
                                    <span class="badge" style="background:#dbeafe;color:#1d4ed8;border-radius:6px;font-size:0.75rem;">ครุภัณฑ์</span>
                                @else
                                    <span class="badge" style="background:#fef3c7;color:#92400e;border-radius:6px;font-size:0.75rem;">ยา</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold">{{ $item->total_qty }}</td>
                            <td class="text-center">
                                @php $avail = $item->availableQty(); @endphp
                                <span class="{{ $avail > 0 ? 'text-success fw-bold' : 'text-danger fw-bold' }}">
                                    {{ $avail }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->active === 'Y')
                                    <span class="badge-active">เปิดใช้งาน</span>
                                @else
                                    <span class="badge-inactive">ปิด</span>
                                @endif
                            </td>
                            @if(auth()->user()->hasAccessRole('admin'))
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-outline-primary btn-sm" style="border-radius:6px;font-size:0.75rem;"
                                            onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->category }}', {{ $item->total_qty }}, '{{ $item->description }}', {{ $item->sort_order }}, '{{ $item->active }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('lend.settings.toggle', $item->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-sm {{ $item->active === 'Y' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                style="border-radius:6px;font-size:0.75rem;"
                                                title="{{ $item->active === 'Y' ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}">
                                            <i class="fas {{ $item->active === 'Y' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-2x mb-2 d-block opacity-50"></i>
                                ยังไม่มีรายการ<br>
                                <small>กด "+ เพิ่มรายการ" เพื่อเริ่มต้น</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal: เพิ่มรายการ --}}
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#e0f2fe,#bae6fd);">
                <h5 class="modal-title fw-bold text-primary"><i class="fas fa-plus-circle me-2"></i>เพิ่มรายการใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('lend.settings.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">ชื่อรายการ <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="เช่น ถังออกซิเจน, ยาพาราเซตามอล" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ประเภท</label>
                            <select name="category" class="form-select">
                                <option value="equipment">ครุภัณฑ์/วัสดุ</option>
                                <option value="medicine">ยา</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">จำนวนทั้งหมด</label>
                            <input type="number" name="total_qty" class="form-control" value="1" min="1">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="รายละเอียดเพิ่มเติม..."></textarea>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">ลำดับแสดง</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn text-white px-4" style="background:linear-gradient(135deg,#0268c7,#17a6a7);border:none;border-radius:8px;">
                        <i class="fas fa-plus me-1"></i> เพิ่มรายการ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: แก้ไขรายการ --}}
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#fef3c7,#fde68a);">
                <h5 class="modal-title fw-bold" style="color:#92400e;"><i class="fas fa-edit me-2"></i>แก้ไขรายการ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editItemForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">ชื่อรายการ <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ประเภท</label>
                            <select name="category" id="editCategory" class="form-select">
                                <option value="equipment">ครุภัณฑ์/วัสดุ</option>
                                <option value="medicine">ยา</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">จำนวนทั้งหมด</label>
                            <input type="number" name="total_qty" id="editQty" class="form-control" min="1">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea name="description" id="editDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">ลำดับแสดง</label>
                            <input type="number" name="sort_order" id="editSort" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">สถานะ</label>
                            <select name="active" id="editActive" class="form-select">
                                <option value="Y">เปิดใช้งาน</option>
                                <option value="N">ปิดใช้งาน</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning text-dark px-4" style="border-radius:8px;">
                        <i class="fas fa-save me-1"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(id, name, category, qty, desc, sort, active) {
    document.getElementById('editItemForm').action = '/lend/settings/' + id;
    document.getElementById('editName').value     = name;
    document.getElementById('editCategory').value = category;
    document.getElementById('editQty').value      = qty;
    document.getElementById('editDesc').value     = desc || '';
    document.getElementById('editSort').value     = sort;
    document.getElementById('editActive').value   = active;
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}
</script>
@endpush
