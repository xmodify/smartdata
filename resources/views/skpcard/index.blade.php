@extends('layouts.app')

@section('title', 'จัดการบัตรสังฆะประชาร่วมใจ - SmartData')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill me-3 shadow-sm transition-all hover-translate-x">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
            <div>
                <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-address-card me-2 text-warning"></i>บัตรสังฆะประชาร่วมใจ</h2>
                <p class="text-muted small mb-0">ระบบจัดการและเพิ่มข้อมูลการซื้อบัตร</p>
            </div>
        </div>
        <button class="btn btn-warning shadow-sm rounded-pill px-4 text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#addCardModal">
            <i class="fas fa-plus-circle me-2"></i>เพิ่มข้อมูลการซื้อบัตร
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th>ข้อมูลผู้ถือบัตร</th>
                        <th>วันที่ซื้อ</th>
                        <th>วันหมดอายุ</th>
                        <th>ราคา</th>
                        <th>เลขที่ใบเสร็จ</th>
                        <th class="text-center px-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cards as $index => $card)
                    <tr>
                        <td class="px-4 text-muted small">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $card->name }}</div>
                            <div class="small text-muted"><i class="fas fa-id-card me-1 small"></i>{{ $card->cid }}</div>
                            <div class="small text-muted"><i class="fas fa-phone me-1 small"></i>{{ $card->phone ?: '-' }}</div>
                        </td>
                        <td>{{ $card->buy_date ? $card->buy_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            @php
                                $isExpired = $card->ex_date && $card->ex_date->isPast();
                            @endphp
                            <span class="badge {{ $isExpired ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} rounded-pill px-3">
                                {{ $card->ex_date ? $card->ex_date->format('d/m/Y') : '-' }}
                                @if($isExpired) (หมดอายุ) @endif
                            </span>
                        </td>
                        <td><span class="fw-bold text-primary">{{ number_format($card->price, 2) }}</span> ฿</td>
                        <td><code>{{ $card->rcpt ?: '-' }}</code></td>
                        <td class="px-4 text-center">
                            <div class="btn-group shadow-sm">
                                <button class="btn btn-white btn-sm edit-card" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editCardModal"
                                    data-card="{{ json_encode($card) }}">
                                    <i class="fas fa-edit text-primary"></i>
                                </button>
                                <form action="{{ route('skpcard.destroy', $card) }}" method="POST" class="d-inline delete-card-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-white btn-sm btn-delete-card">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                            <p>ยังไม่มีข้อมูลการซื้อบัตรในระบบ</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Card Modal -->
<div class="modal fade" id="addCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>เพิ่มข้อมูลการซื้อบัตรใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('skpcard.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">เลขบัตรประชาชน (CID)</label>
                            <input type="text" name="cid" class="form-control" maxlength="13" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">ชื่อ-นามสกุล</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">วันเกิด</label>
                            <input type="date" name="birthday" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ราคาบัตร</label>
                            <div class="input-group">
                                <input type="number" name="price" class="form-control" value="100" required>
                                <span class="input-group-text">฿</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">ที่อยู่</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">วันที่ซื้อบัตร</label>
                            <input type="date" name="buy_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">เลขที่ใบเสร็จ</label>
                            <input type="text" name="rcpt" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning px-4 shadow-sm fw-bold">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Card Modal -->
<div class="modal fade" id="editCardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>แก้ไขข้อมูลการซื้อบัตร</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCardForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">เลขบัตรประชาชน (CID)</label>
                            <input type="text" name="cid" id="edit_cid" class="form-control" maxlength="13" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">ชื่อ-นามสกุล</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">วันเกิด</label>
                            <input type="date" name="birthday" id="edit_birthday" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ราคาบัตร</label>
                            <div class="input-group">
                                <input type="number" name="price" id="edit_price" class="form-control" required>
                                <span class="input-group-text">฿</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">ที่อยู่</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">วันที่ซื้อบัตร</label>
                            <input type="date" name="buy_date" id="edit_buy_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">เลขที่ใบเสร็จ</label>
                            <input type="text" name="rcpt" id="edit_rcpt" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold text-white">อัปเดตข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Edit Card Modal Population
    document.querySelectorAll('.edit-card').forEach(button => {
        button.addEventListener('click', function() {
            const card = JSON.parse(this.dataset.card);
            const form = document.getElementById('editCardForm');
            form.action = `{{ url('/') }}/skpcard/${card.id}`;
            
            document.getElementById('edit_cid').value = card.cid;
            document.getElementById('edit_name').value = card.name;
            document.getElementById('edit_birthday').value = card.birthday ? card.birthday.substring(0, 10) : '';
            document.getElementById('edit_phone').value = card.phone;
            document.getElementById('edit_price').value = card.price;
            document.getElementById('edit_address').value = card.address;
            document.getElementById('edit_buy_date').value = card.buy_date ? card.buy_date.substring(0, 10) : '';
            document.getElementById('edit_rcpt').value = card.rcpt;
        });
    });

    // Delete Card Confirmation
    document.querySelectorAll('.btn-delete-card').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'ยืนยันการลบข้อมูล?',
                text: "ข้อมูลการซื้อบัตรนี้จะถูกลบออกจากระบบอย่างถาวร",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลบเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
