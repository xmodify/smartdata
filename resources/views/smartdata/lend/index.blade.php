@extends('layouts.app')

@section('title', 'ศูนย์ยืม-คืน')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
<style>
    .stat-card {
        border-radius: 16px;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important; }
    .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    .lend-tab .nav-link { border-radius: 10px 10px 0 0; font-weight: 600; color: #6c757d; padding: 0.6rem 1.2rem; }
    .lend-tab .nav-link.active { color: #0268c7; background: #fff; border-color: #dee2e6 #dee2e6 #fff; }
    .badge-status { font-size: 0.78rem; padding: 0.35em 0.75em; border-radius: 8px; font-weight: 600; }
    .table-lend th { font-size: 0.8rem; font-weight: 700; color: #6c757d; text-transform: uppercase; letter-spacing: 0.05em; border-top: none; }
    .table-lend td { vertical-align: middle; font-size: 0.88rem; }
    .action-btn { font-size: 0.75rem; padding: 0.25rem 0.6rem; border-radius: 6px; }
    .filter-chip { display: inline-flex; align-items: center; padding: 0.3rem 0.85rem; border-radius: 50px; font-size: 0.8rem; font-weight: 600; text-decoration: none; border: 1.5px solid transparent; transition: all 0.15s; cursor: pointer; }
    .filter-chip:hover { transform: translateY(-1px); }
    .filter-chip.active { border-color: currentColor; }
    .overdue-row { background-color: #fff5f5 !important; }

    /* Override DataTables UI to match premium design */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.5rem !important;
        padding: 0.2rem 0.6rem !important;
        outline: none !important;
        font-size: 0.8rem !important;
    }
    .dt-buttons .btn-success {
        background-color: #198754 !important;
        border-color: #198754 !important;
        color: #ffffff !important;
        border-radius: 0.4rem !important;
        font-weight: 500 !important;
        padding: 0.25rem 0.6rem !important;
        font-size: 0.75rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.4rem !important;
        box-shadow: 0 2px 4px rgba(25, 135, 84, 0.2) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0268c7 !important;
        color: white !important;
        border: 1px solid #0268c7 !important;
        border-radius: 0.5rem !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f8f9fc !important;
        color: #0268c7 !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.5rem !important;
    }
    table.dataTable thead th {
        background-color: #f8fafc !important;
        color: #0268c7 !important;
        font-weight: 700 !important;
        border-bottom: 2px solid #dee2e6 !important;
        font-size: 0.85rem !important;
    }
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 0rem;
    }
    .dataTables_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 0;
        font-size: 0.85rem;
    }
    .dt-buttons {
        margin-bottom: 0 !important;
        display: flex !important;
        align-items: center !important;
    }
</style>
@endpush

@section('topbar_actions')
    <span class="fw-bold text-white"><i class="fas fa-hand-holding-medical me-2"></i>ศูนย์ยืม-คืน</span>
@endsection

@section('content')
<div class="container-fluid px-3">

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#e0f2fe,#bae6fd);">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon" style="background:#0ea5e9;color:#fff;"><i class="fas fa-box-open"></i></div>
                    <div>
                        <div class="fs-2 fw-bold text-primary">{{ $stats['borrowed'] }}</div>
                        <div class="small text-muted fw-semibold">กำลังยืม</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#fef3c7,#fde68a);">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon" style="background:#f59e0b;color:#fff;"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="fs-2 fw-bold text-warning">{{ $stats['overdue'] }}</div>
                        <div class="small text-muted fw-semibold">เกินกำหนด</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#d1fae5,#a7f3d0);">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon" style="background:#10b981;color:#fff;"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="fs-2 fw-bold text-success">{{ $stats['returned'] }}</div>
                        <div class="small text-muted fw-semibold">คืนแล้ว</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg,#f1f5f9,#e2e8f0);">
                <div class="card-body d-flex align-items-center gap-3 p-3">
                    <div class="stat-icon" style="background:#94a3b8;color:#fff;"><i class="fas fa-ban"></i></div>
                    <div>
                        <div class="fs-2 fw-bold text-secondary">{{ $stats['cancelled'] }}</div>
                        <div class="small text-muted fw-semibold">ยกเลิก</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius:14px;">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- Tab --}}
                <ul class="nav lend-tab border-bottom-0 me-2">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-boxes-stacked me-1"></i> วัสดุ/ครุภัณฑ์
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled text-muted" href="#" tabindex="-1">
                            <i class="fas fa-pills me-1"></i> ยา <small class="text-muted">(เร็วๆนี้)</small>
                        </a>
                    </li>
                </ul>

                <div class="ms-auto d-flex gap-2 flex-wrap align-items-center">
                    @if(auth()->user()->hasAccessRole('admin'))
                    <a href="{{ route('lend.settings') }}" class="btn btn-outline-secondary btn-sm px-3" style="border-radius:8px;">
                        <i class="fas fa-cog me-1"></i> ตั้งค่า
                    </a>
                    @endif

                    <button type="button" class="btn btn-sm text-white px-3 shadow-sm" onclick="openCreateModal()"
                       style="background:linear-gradient(135deg,#0268c7,#17a6a7);border-radius:8px;border:none;">
                        <i class="fas fa-plus me-1"></i> ยืมรายการ
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Chips --}}
    <div class="mb-3 d-flex flex-wrap gap-2">
        @php
            $chips = [
                'all'       => ['label'=>'ทั้งหมด',    'color'=>'#64748b', 'bg'=>'#f1f5f9'],
                'borrowed'  => ['label'=>'กำลังยืม',   'color'=>'#0ea5e9', 'bg'=>'#e0f2fe'],
                'overdue'   => ['label'=>'เกินกำหนด',  'color'=>'#ef4444', 'bg'=>'#fee2e2'],
                'returned'  => ['label'=>'คืนแล้ว',    'color'=>'#10b981', 'bg'=>'#d1fae5'],
                'cancelled' => ['label'=>'ยกเลิก',     'color'=>'#94a3b8', 'bg'=>'#f1f5f9'],
            ];
        @endphp
        @foreach($chips as $val => $chip)
            <a href="{{ route('lend.index', ['status'=>$val]) }}"
               class="filter-chip {{ $status_filter === $val ? 'active' : '' }}"
               style="color:{{ $chip['color'] }};background:{{ $chip['bg'] }};{{ $status_filter===$val ? 'border-color:'.$chip['color'].';' : '' }}">
                {{ $chip['label'] }}
            </a>
        @endforeach
    </div>

    {{-- ตาราง --}}
    <div class="card border-0 shadow-sm p-4" style="border-radius:14px;overflow:hidden;">
        <div class="table-responsive">
            <table id="tableLend" class="table table-lend table-hover mb-0" style="width:100%;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="px-3 py-3">#</th>
                        <th>รายการยืม</th>
                        <th>ผู้ยืม</th>
                        <th>เบอร์โทร</th>
                        <th>วันยืม</th>
                        <th>กำหนดคืน</th>
                        <th>มัดจำ</th>
                        <th>สถานะ</th>
                        <th>ผู้จ่าย</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $t)
                        @php
                            $badge = $t->getStatusBadge();
                            $isOverdue = $t->isOverdue();
                        @endphp
                        <tr class="{{ $isOverdue ? 'overdue-row' : '' }}">
                            <td class="px-3 text-muted small">{{ $t->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $t->lendItem->name ?? '-' }}</div>
                                @if($t->qty > 1)
                                    <small class="text-muted">จำนวน {{ $t->qty }} ชิ้น</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $t->borrower_name }}</div>
                            </td>
                            <td class="text-muted small">{{ $t->borrower_phone ?: '-' }}</td>
                            <td class="small">{{ DateThai($t->borrow_date) }}</td>
                            <td class="small">
                                @if($t->due_date)
                                    <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                        {{ DateThai($t->due_date) }}
                                    </span>
                                    @if($isOverdue)
                                        <br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> เกิน {{ $t->due_date->diffInDays(now()) }} วัน</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($t->deposit_amount)
                                    {{ number_format($t->deposit_amount, 0) }} บ.
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-status {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </td>
                            <td class="small text-muted">{{ $t->creator->name ?? '-' }}</td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                    @if($t->status === 'borrowed')
                                        <button type="button" class="btn btn-outline-primary action-btn" title="แก้ไข"
                                                onclick="openEditModal({{ json_encode($t) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success action-btn"
                                                title="บันทึกคืน" onclick="openReturnModal({{ $t->id }}, '{{ addslashes($t->borrower_name) }}')">
                                            <i class="fas fa-undo-alt"></i>
                                        </button>
                                        <a href="{{ route('lend.print', $t->id) }}" target="_blank"
                                           class="btn btn-outline-secondary action-btn" title="พิมพ์ใบยืม">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger action-btn"
                                                title="ยกเลิก" onclick="openCancelModal({{ $t->id }}, '{{ addslashes($t->borrower_name) }}')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @elseif($t->status === 'returned')
                                        <a href="{{ route('lend.print', $t->id) }}" target="_blank"
                                           class="btn btn-outline-secondary action-btn" title="พิมพ์ใบยืม">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: ยืมอุปกรณ์ใหม่ --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#0268c7,#17a6a7);">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-plus-circle me-2"></i>ยืมรายการใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('lend.store') }}" method="POST">
                @csrf
                <input type="hidden" name="borrower_type" value="other">
                <input type="hidden" name="hn" value="">
                
                <div class="modal-body p-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-user me-2"></i>ข้อมูลผู้ยืม</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label">ชื่อผู้ยืม <span class="text-danger">*</span></label>
                            <input type="text" name="borrower_name" class="form-control" placeholder="ชื่อ-นามสกุลผู้ยืม" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">ที่อยู่</label>
                            <textarea name="borrower_address" class="form-control" rows="2" placeholder="ที่อยู่ผู้ยืม"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">เบอร์โทร</label>
                            <input type="text" name="borrower_phone" class="form-control" placeholder="0xx-xxx-xxxx">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-boxes-stacked me-2"></i>รายการยืม</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label">เลือกรายการ <span class="text-danger">*</span></label>
                            <select name="lend_item_id" class="form-select" required>
                                <option value="">-- เลือกรายการ --</option>
                                @foreach($lendItems as $item)
                                    <option value="{{ $item->id }}">
                                        {{ $item->name }}
                                        @if($item->total_qty > 1) ({{ $item->availableQty() }}/{{ $item->total_qty }} ชิ้น) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">จำนวน <span class="text-danger">*</span></label>
                            <input type="number" name="qty" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">วันที่ยืม <span class="text-danger">*</span></label>
                            <input type="text" name="borrow_date" id="create_borrow_date" class="form-control bg-white" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">กำหนดคืน</label>
                            <input type="text" name="due_date" id="create_due_date" class="form-control bg-white">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-receipt me-2"></i>ค่ามัดจำ</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">จำนวนเงินมัดจำ (บาท)</label>
                            <input type="number" name="deposit_amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เลขใบเสร็จมัดจำ</label>
                            <input type="text" name="deposit_receipt_no" class="form-control" placeholder="เลขที่ใบเสร็จ">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn text-white px-4" style="background:linear-gradient(135deg,#0268c7,#17a6a7);border-radius:8px;border:none;">
                        <i class="fas fa-save me-1"></i> บันทึกการยืม
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: แก้ไขรายการยืม --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#4e73df,#224abe);">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-edit me-2"></i>แก้ไขรายการยืม</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="borrower_type" value="other">
                <input type="hidden" name="hn" value="">
                
                <div class="modal-body p-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-user me-2"></i>ข้อมูลผู้ยืม</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label">ชื่อผู้ยืม <span class="text-danger">*</span></label>
                            <input type="text" name="borrower_name" id="edit_borrower_name" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">ที่อยู่</label>
                            <textarea name="borrower_address" id="edit_borrower_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">เบอร์โทร</label>
                            <input type="text" name="borrower_phone" id="edit_borrower_phone" class="form-control">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-boxes-stacked me-2"></i>รายการยืม</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label">เลือกรายการ <span class="text-danger">*</span></label>
                            <select name="lend_item_id" id="edit_lend_item_id" class="form-select" required>
                                @foreach($lendItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">จำนวน <span class="text-danger">*</span></label>
                            <input type="number" name="qty" id="edit_qty" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">วันที่ยืม <span class="text-danger">*</span></label>
                            <input type="text" name="borrow_date" id="edit_borrow_date" class="form-control bg-white" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">กำหนดคืน</label>
                            <input type="text" name="due_date" id="edit_due_date" class="form-control bg-white">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-receipt me-2"></i>ค่ามัดจำ</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">จำนวนเงินมัดจำ (บาท)</label>
                            <input type="number" name="deposit_amount" id="edit_deposit_amount" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">เลขใบเสร็จมัดจำ</label>
                            <input type="text" name="deposit_receipt_no" id="edit_deposit_receipt_no" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">หมายเหตุ</label>
                            <textarea name="note" id="edit_note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius:8px;">
                        <i class="fas fa-save me-1"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: บันทึกคืน --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);">
                <h5 class="modal-title fw-bold text-success"><i class="fas fa-undo-alt me-2"></i>บันทึกการคืนอุปกรณ์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="returnForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">ผู้ยืม: <strong id="returnBorrowerName"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">วันที่คืน <span class="text-danger">*</span></label>
                        <input type="text" name="return_date" id="returnDate" class="form-control bg-white"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">เวลาที่คืน</label>
                        <input type="time" name="return_time" class="form-control" value="{{ now()->format('H:i') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">หมายเหตุ</label>
                        <textarea name="returned_note" class="form-control" rows="2" placeholder="หมายเหตุการคืน..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success px-4" style="border-radius:8px;">
                        <i class="fas fa-check me-1"></i> บันทึกคืน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: ยกเลิกรายการ --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="background:linear-gradient(135deg,#fee2e2,#fecaca);">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-ban me-2"></i>ยกเลิกรายการยืม</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body p-4">
                    <p class="text-muted mb-3">ผู้ยืม: <strong id="cancelBorrowerName"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">เหตุผลที่ยกเลิก <span class="text-danger">*</span></label>
                        <textarea name="cancelled_reason" class="form-control" rows="3"
                                  placeholder="ระบุเหตุผล..." required></textarea>
                    </div>
                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        การยกเลิกไม่สามารถย้อนกลับได้ และไม่สามารถลบรายการออกจากระบบ
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-danger px-4" style="border-radius:8px;">
                        <i class="fas fa-ban me-1"></i> ยืนยันยกเลิก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('vendor/flatpickr/th.js') }}"></script>
<script>
let returnPicker;
let createBorrowPicker, createDuePicker;
let editBorrowPicker, editDuePicker;

const yearOffset = 543;
const commonConfig = {
    locale: "th",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "j M Y",
    allowInput: false,
    onReady: function(selectedDates, dateStr, instance) {
        const container = instance.calendarContainer;
        if (container && !container.querySelector('.flatpickr-today-button')) {
            const btn = document.createElement("div");
            btn.className = "flatpickr-today-button";
            btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
            btn.style.cssText = "text-align: center; padding: 8px; cursor: pointer; border-top: 1px solid #e6e6e6; font-weight: bold; color: #0268c7;";
            btn.addEventListener("mousedown", function(e) {
                e.preventDefault();
                e.stopPropagation();
                instance.setDate(new Date());
                instance.close();
            });
            container.appendChild(btn);
        }

        if (instance.altInput) {
            const originalValue = instance.altInput.value;
            if (originalValue) {
                const date = instance.selectedDates[0] || new Date(instance.input.value);
                if (date && !isNaN(date.getTime())) {
                    const day = date.getDate();
                    const month = instance.l10n.months.shorthand[date.getMonth()];
                    const year = date.getFullYear() + yearOffset;
                    instance.altInput.value = `${day} ${month} ${year}`;
                }
            }
        }
    },
    onChange: function(selectedDates, dateStr, instance) {
        if (instance.altInput && selectedDates.length > 0) {
            const date = selectedDates[0];
            setTimeout(() => {
                const day = date.getDate();
                const month = instance.l10n.months.shorthand[date.getMonth()];
                const year = date.getFullYear() + yearOffset;
                instance.altInput.value = `${day} ${month} ${year}`;
            }, 10);
        }
    }
};

function openCreateModal() {
    // Reset form values
    document.querySelector('#createModal form').reset();
    if (createBorrowPicker) createBorrowPicker.setDate(new Date(), true);
    if (createDuePicker) createDuePicker.clear();
    new bootstrap.Modal(document.getElementById('createModal')).show();
}

function openEditModal(t) {
    document.getElementById('editForm').action = '/lend/' + t.id;
    document.getElementById('edit_borrower_name').value = t.borrower_name;
    document.getElementById('edit_borrower_address').value = t.borrower_address || '';
    document.getElementById('edit_borrower_phone').value = t.borrower_phone || '';
    document.getElementById('edit_lend_item_id').value = t.lend_item_id;
    document.getElementById('edit_qty').value = t.qty;
    
    // Pickers set date
    if (editBorrowPicker) editBorrowPicker.setDate(t.borrow_date, true);
    if (editDuePicker) {
        if (t.due_date) editDuePicker.setDate(t.due_date, true);
        else editDuePicker.clear();
    }
    
    document.getElementById('edit_deposit_amount').value = t.deposit_amount || '';
    document.getElementById('edit_deposit_receipt_no').value = t.deposit_receipt_no || '';
    document.getElementById('edit_note').value = t.note || '';
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function openReturnModal(id, name) {
    document.getElementById('returnBorrowerName').textContent = name;
    document.getElementById('returnForm').action = '/lend/' + id + '/return';
    
    const returnModalEl = document.getElementById('returnModal');
    const modal = new bootstrap.Modal(returnModalEl);
    
    modal.show();
}

function openCancelModal(id, name) {
    document.getElementById('cancelBorrowerName').textContent = name;
    document.getElementById('cancelForm').action = '/lend/' + id + '/cancel';
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

$(document).ready(function() {
    // Initialize DataTables with premium style
    $('#tableLend').DataTable({
        dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
        buttons: [{
            extend: 'excelHtml5',
            text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
            className: 'btn btn-success',
            title: 'รายงานการยืม-คืนอุปกรณ์',
        }],
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
            paginate: {
                previous: "ก่อนหน้า",
                next: "ถัดไป"
            }
        },
        pageLength: 10,
        responsive: true
    });

    if (typeof flatpickr !== 'undefined') {
        createBorrowPicker = flatpickr("#create_borrow_date", commonConfig);
        createDuePicker = flatpickr("#create_due_date", commonConfig);
        
        editBorrowPicker = flatpickr("#edit_borrow_date", commonConfig);
        editDuePicker = flatpickr("#edit_due_date", commonConfig);
        
        returnPicker = flatpickr("#returnDate", commonConfig);
    }
});
</script>
@endpush
