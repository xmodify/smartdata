@extends('layouts.app')

@section('title', 'SmartData | ระบบแจ้งเตือน Moph Alert')

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
            padding: 0.2rem 0.6rem !important;
            outline: none !important;
            font-size: 0.8rem !important;
        }
        .dropdown-menu-multiselect {
            min-width: 350px;
            max-height: 450px;
            overflow-y: auto;
            padding: 0;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid #e3e6f0;
        }
        .multiselect-header {
            position: sticky;
            top: 0;
            background: white;
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            z-index: 10;
        }
        .multiselect-search {
            border-radius: 8px;
            font-size: 0.85rem;
            padding: 8px 12px;
            border: 1px solid #d1d3e2;
        }
        .multiselect-item-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .multiselect-item {
            padding: 8px 15px;
            transition: background 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid #f8f9fc;
        }
        .multiselect-item:hover {
            background-color: #f8f9fc;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header-container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="report-title-box">
            <h5 class="fw-bold mb-1 text-dark">
                <i class="fas fa-bell text-success me-2"></i>ระบบส่งการแจ้งเตือน Moph Alert (งานบุคลากร)
            </h5>
            <p class="text-muted small mb-0">ค้นหารายชื่อ และเลือกส่งข้อความแจ้งเตือนตรงไปยังแอปพลิเคชันหมอพร้อมของเจ้าหน้าที่ รพ.หัวตะพาน</p>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            @if($mophAlert && $mophAlert->active === 'Y')
                <span class="badge bg-success shadow-sm px-3 py-2"><i class="fas fa-check-circle me-1"></i> MOPH Alert พร้อมใช้งาน</span>
            @else
                <span class="badge bg-danger shadow-sm px-3 py-2"><i class="fas fa-exclamation-triangle me-1"></i> ปิดใช้งาน MOPH Alert (ติดต่อผู้ดูแลระบบ)</span>
            @endif
        </div>
    </div>



    <!-- Tab Navigation -->
    <ul class="nav nav-pills mb-3" id="alertTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 shadow-sm" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff-content" type="button" role="tab" aria-controls="staff-content" aria-selected="true">
                <i class="fas fa-users me-1"></i> รายชื่อเจ้าหน้าที่
            </button>
        </li>
        <li class="nav-item ms-2" role="presentation">
            <button class="nav-link rounded-pill px-4 shadow-sm" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-content" type="button" role="tab" aria-controls="history-content" aria-selected="false">
                <i class="fas fa-history me-1"></i> ประวัติการส่งแจ้งเตือน
            </button>
        </li>
    </ul>

    <div class="tab-content" id="alertTabContent">
        <!-- Tab 1: Staff list -->
        <div class="tab-pane fade show active" id="staff-content" role="tabpanel" aria-labelledby="staff-tab">
            <!-- Filter Card -->
            <div class="card border-0 shadow-sm rounded-lg p-3 mb-3 bg-white">
                <form method="GET" action="{{ route('mophalert.hrd.index') }}" id="filterForm">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label class="fw-bold small text-muted mb-0">หน่วยงาน/ฝ่าย:</label>
                            
                            <!-- Department Multiselect Dropdown -->
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle shadow-sm px-3 rounded-pill" 
                                        type="button" id="deptDropdown" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                                    <i class="fas fa-building me-1 text-primary"></i>
                                    <span id="dept_btn_label">เลือกหน่วยงาน (ทั้งหมด)</span>
                                </button>
                                
                                <div class="dropdown-menu dropdown-menu-multiselect p-0" aria-labelledby="deptDropdown">
                                    <div class="multiselect-header">
                                        <input type="text" class="form-control multiselect-search mb-2" id="deptSearchInput" placeholder="ค้นหาหน่วยงาน...">
                                        <div class="d-flex justify-content-between xsmall mt-2 px-1">
                                            <a href="javascript:void(0)" class="text-primary text-decoration-none fw-bold" onclick="checkAllDepts(true)">เลือกทั้งหมด</a>
                                            <a href="javascript:void(0)" class="text-danger text-decoration-none fw-bold" onclick="checkAllDepts(false)">ล้างตัวเลือก</a>
                                        </div>
                                    </div>
                                    
                                    <div class="multiselect-item-list" id="deptListContainer">
                                        @foreach($depts as $dept)
                                        <div class="multiselect-item" onclick="toggleDeptCheckbox('dept_{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}')">
                                            <input class="form-check-input dept-checkbox" type="checkbox" name="dept_ids[]" 
                                                   value="{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}" 
                                                   id="dept_{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}"
                                                   {{ in_array($dept->HR_DEPARTMENT_SUB_SUB_ID, $dept_ids) ? 'checked' : '' }}
                                                   onclick="event.stopPropagation(); updateDeptLabel();">
                                            <label class="form-check-label small mb-0 cursor-pointer w-100" style="font-size:0.8rem;">
                                                {{ $dept->HR_DEPARTMENT_SUB_SUB_NAME }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        </div>
                    </div>
                </form>
            </div>
            <!-- Main List Card -->
            <div class="card border-0 shadow-sm rounded-lg p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="fas fa-users text-success me-2"></i>รายชื่อเจ้าหน้าที่
                        <span class="text-muted fs-6 fw-normal">
                            @if(empty($selected_dept_names))
                                (ทั้งหมด)
                            @else
                                (หน่วยงาน: {{ implode(', ', $selected_dept_names) }})
                            @endif
                        </span>
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill shadow-sm" id="btn_clear_selections" onclick="clearAllSelectedStaff()">
                            <i class="fas fa-trash-alt me-1"></i> ล้างการเลือก
                        </button>
                        <button type="button" class="btn btn-success btn-sm rounded-pill shadow-sm" id="btn_send_batch" onclick="openBatchModal()">
                            <i class="fas fa-paper-plane me-1"></i> ส่งแบบกลุ่ม (<span id="batch_count">0</span> คน)
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100 mb-0" id="tableStaffAlert">
                        <thead class="bg-light">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="check_all_staff" onclick="toggleSelectAllStaff(this.checked)">
                                </th>
                                <th>ชื่อ - นามสกุล</th>
                                <th>หน่วยงาน/กลุ่มงาน</th>
                                <th>ตำแหน่ง</th>
                                <th class="text-center">ส่งแจ้งเตือน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staffList as $staff)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input staff-checkbox" name="selected_cids[]" value="{{ $staff->cid }}" data-name="{{ $staff->prefix }}{{ $staff->fname }} {{ $staff->lname }}" onchange="updateBatchCount()">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $staff->prefix }}{{ $staff->fname }} {{ $staff->lname }}</div>
                                    <code class="xsmall text-muted">{{ $staff->cid }}</code>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $staff->department }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border xsmall">{{ $staff->position ?: 'ไม่ระบุตำแหน่ง' }}</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-primary btn-sm rounded-circle px-2 py-1" 
                                        onclick="openSingleSendModal('{{ $staff->cid }}', '{{ $staff->prefix }}{{ $staff->fname }} {{ $staff->lname }}')"
                                        title="ส่ง MOPH Alert รายบุคคล">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab 2: History log -->
        <div class="tab-pane fade" id="history-content" role="tabpanel" aria-labelledby="history-tab">
            <div class="card border-0 shadow-sm rounded-lg p-4 bg-white">
                <h5 class="fw-bold text-dark mb-3"><i class="fas fa-history text-primary me-2"></i>ประวัติการส่งแจ้งเตือน Moph Alert</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100 mb-0" id="tableHistoryLog">
                        <thead class="bg-light">
                            <tr>
                                <th width="60" class="text-center">#</th>
                                <th width="150">วันที่ทำรายการ</th>
                                <th width="150">ผู้ทำรายการ</th>
                                <th>หัวข้อ (Title)</th>
                                <th>รายละเอียดข้อความ</th>
                                <th width="100" class="text-center">ผู้รับ (คน)</th>
                                <th width="100" class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $hIdx = 1; @endphp
                            @foreach($history as $item)
                            <tr>
                                <td class="text-center">{{ $hIdx++ }}</td>
                                <td>
                                    <span class="small text-muted">{{ $item->created_at->format('d/m/Y H:i') }} น.</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">{{ $item->user ? $item->user->name : 'ไม่ระบุผู้ส่ง' }}</span>
                                </td>
                                <td>
                                    <span class="small text-dark">{{ $item->title }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="xsmall text-muted text-truncate" style="max-width: 250px;">
                                            {{ strip_tags($item->message_text) }}
                                        </div>
                                        <button class="btn btn-outline-secondary btn-xs px-1 py-0 btn-view-message" 
                                            data-title="{{ $item->title }}"
                                            data-message="{{ $item->message_text }}"
                                            title="ดูข้อความเต็ม">
                                            <i class="fas fa-eye small"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $item->recipient_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item->status === 'success')
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2">สำเร็จ</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2" title="{{ $item->response_message }}">ล้มเหลว</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Alert Modal -->
<div class="modal fade" id="sendAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary-custom text-white border-0">
                <h5 class="modal-title fw-bold" id="modal_title_label"><i class="fas fa-paper-plane me-2"></i>ส่งข้อความแจ้งเตือน Moph Alert</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sendAlertForm" method="POST" onsubmit="dispatchAlert(event)">
                @csrf
                <!-- Hidden CIDs array -->
                <div id="modal_cids_container"></div>
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ส่งไปหา:</label>
                        <div class="fw-bold text-primary fs-6" id="recipient_label"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">หัวข้อแจ้งเตือน (Title)</label>
                        <input type="text" name="title" id="alert_title" class="form-control shadow-sm" value="แจ้งประชาสัมพันธ์ รพ.หัวตะพาน" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">รายละเอียดตัวเต็ม (พิมพ์ข้อความธรรมดาได้ปกติ)</label>
                        <textarea name="html" id="alert_html" class="form-control shadow-sm" rows="4" required>โรงพยาบาลหัวตะพานขอเรียนเชิญเจ้าหน้าที่เข้าร่วมประชุมสัมมนา...</textarea>
                        <div class="form-text xsmall text-muted mt-1">
                            <i class="fas fa-info-circle me-1"></i> กด Enter ขึ้นบรรทัดใหม่ได้ตามปกติ ระบบจะจัดรูปแบบส่งเข้าแอปหมอพร้อมอัตโนมัติ
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" id="btn_submit_alert" class="btn btn-primary px-4 shadow-sm" {{ (!$mophAlert || $mophAlert->active !== 'Y') ? 'disabled' : '' }}>
                        <i class="fas fa-paper-plane me-1"></i> ส่งข้อความแจ้งเตือน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary-custom text-white border-0">
                <h5 class="modal-title fw-bold" id="viewMessageModalLabel"><i class="fas fa-eye me-2"></i>รายละเอียดข้อความทั้งหมด</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">หัวข้อ (Title):</label>
                    <div class="fw-bold text-dark fs-6" id="viewMessageTitle"></div>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold small text-muted">ข้อความแจ้งเตือนเต็ม:</label>
                    <div class="p-3 bg-light rounded border text-dark fs-6" id="viewMessageContent" style="white-space: pre-wrap; font-family: 'Sarabun', sans-serif; line-height: 1.6;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-secondary px-4 shadow-sm rounded-pill" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script>
        // Global Map to store selected staff (CID -> Name) across pagination and searches
        const selectedStaff = new Map();

        $(document).ready(function() {
            // DataTables
            const staffTable = $('#tableStaffAlert').DataTable({
                language: {
                    search: "ค้นหาเจ้าหน้าที่:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    infoEmpty: "แสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
                    zeroRecords: "ไม่พบรายชื่อเจ้าหน้าที่ที่ค้นหา",
                    paginate: {
                        previous: "ก่อนหน้า",
                        next: "ถัดไป"
                    }
                },
                pageLength: 10,
                responsive: true
            });

            // Restore checked state when Datatable is redrawn (paging, sorting, searching)
            staffTable.on('draw.dt', function() {
                $('input.staff-checkbox').each(function() {
                    const cid = $(this).val();
                    if (selectedStaff.has(cid)) {
                        $(this).prop('checked', true);
                    } else {
                        $(this).prop('checked', false);
                    }
                });
                
                // Update select all header checkbox status
                const visible = $('input.staff-checkbox');
                const checkedVisible = $('input.staff-checkbox:checked');
                const checkAll = $('#check_all_staff');
                if (visible.length > 0 && visible.length === checkedVisible.length) {
                    checkAll.prop('checked', true);
                } else {
                    checkAll.prop('checked', false);
                }
            });

            // Delegate checkbox change events
            $('#tableStaffAlert tbody').on('change', '.staff-checkbox', function() {
                const cid = $(this).val();
                const name = $(this).data('name');
                if (this.checked) {
                    selectedStaff.set(cid, name);
                } else {
                    selectedStaff.delete(cid);
                }
                saveToLocalStorage();
                updateBatchCount();
            });

            $('#tableHistoryLog').DataTable({
                language: {
                    search: "ค้นหาประวัติ:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    infoEmpty: "แสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
                    zeroRecords: "ไม่พบข้อมูลประวัติการส่ง",
                    paginate: {
                        previous: "ก่อนหน้า",
                        next: "ถัดไป"
                    }
                },
                pageLength: 10,
                order: [[1, 'desc']], // Order by date descending
                responsive: true
            });

            // Adjust columns on tab change to prevent header layout issues
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
            });

            // Department Search filter inside multiselect
            $('#deptSearchInput').on('input', function() {
                const query = this.value.toLowerCase();
                $('.multiselect-item').each(function() {
                    const text = $(this).text().toLowerCase();
                    if (text.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // View full message modal handler
            $(document).on('click', '.btn-view-message', function() {
                const title = $(this).data('title');
                const message = $(this).data('message');
                $('#viewMessageTitle').text(title);
                $('#viewMessageContent').text(message);
                $('#viewMessageModal').modal('show');
            });

            // Auto submit filter form when department dropdown is closed
            $('#deptDropdown').parent().on('hidden.bs.dropdown', function () {
                $('#filterForm').submit();
            });

            // Load saved staff from localStorage
            const savedStaff = localStorage.getItem('moph_selected_staff');
            if (savedStaff) {
                try {
                    const arr = JSON.parse(savedStaff);
                    arr.forEach(([cid, name]) => {
                        selectedStaff.set(cid, name);
                    });
                } catch(e) {
                    console.error("Error parsing saved staff", e);
                }
            }
            updateBatchCount();

            updateDeptLabel();
        });

        // Toggle checkbox helper inside multiselect list item click
        function toggleDeptCheckbox(id) {
            const cb = document.getElementById(id);
            cb.checked = !cb.checked;
            updateDeptLabel();
        }

        // Check/Uncheck all depts
        function checkAllDepts(status) {
            $('.dept-checkbox').prop('checked', status);
            updateDeptLabel();
        }

        // Update dropdown label text
        function updateDeptLabel() {
            const checkedCount = $('.dept-checkbox:checked').length;
            const totalCount = $('.dept-checkbox').length;
            
            if (checkedCount === 0) {
                $('#dept_btn_label').text('เลือกหน่วยงาน (ทั้งหมด)');
            } else if (checkedCount === totalCount) {
                $('#dept_btn_label').text('เลือกหน่วยงาน (ทั้งหมด)');
            } else {
                $('#dept_btn_label').text(`เลือกหน่วยงาน (${checkedCount} หน่วยงาน)`);
            }
        }

        // Save Map to LocalStorage
        function saveToLocalStorage() {
            localStorage.setItem('moph_selected_staff', JSON.stringify(Array.from(selectedStaff.entries())));
        }

        // Toggle checkboxes
        function toggleSelectAllStaff(status) {
            const table = $('#tableStaffAlert').DataTable();
            // Get all rows currently matching the search filter across all pages in Datatable
            const rows = table.rows({ search: 'applied' }).nodes();
            
            // Find checkboxes inside these rows and update their prop
            $('input.staff-checkbox', rows).prop('checked', status).each(function() {
                const cid = $(this).val();
                const name = $(this).data('name');
                if (status) {
                    selectedStaff.set(cid, name);
                } else {
                    selectedStaff.delete(cid);
                }
            });
            
            // Sync visible rows immediately
            $('input.staff-checkbox').prop('checked', status);
            saveToLocalStorage();
            updateBatchCount();
        }

        // Clear all selected staff
        function clearAllSelectedStaff() {
            selectedStaff.clear();
            localStorage.removeItem('moph_selected_staff');
            $('.staff-checkbox').prop('checked', false);
            $('#check_all_staff').prop('checked', false);
            updateBatchCount();
        }

        // Update count of checked users
        function updateBatchCount() {
            const checkedCount = selectedStaff.size;
            $('#batch_count').text(checkedCount);
        }

        // Open single recipient send modal
        function openSingleSendModal(cid, name) {
            $('#modal_title_label').html('<i class="fas fa-paper-plane me-2 text-primary"></i>ส่งแจ้งเตือน Moph Alert รายบุคคล');
            $('#recipient_label').text(name + ' (' + cid + ')');
            
            // Populating hidden cids container
            $('#modal_cids_container').html(`<input type="hidden" name="cids[]" value="${cid}">`);
            
            // Open modal
            const modal = new bootstrap.Modal(document.getElementById('sendAlertModal'));
            modal.show();
        }

        // Open batch recipient send modal
        function openBatchModal() {
            if (selectedStaff.size === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกผู้รับ!',
                    text: 'คุณต้องเลือกเจ้าหน้าที่อย่างน้อย 1 คนเพื่อส่งข้อมูล',
                    confirmButtonText: 'ตกลง'
                });
                return;
            }

            $('#modal_title_label').html('<i class="fas fa-users me-2 text-success"></i>ส่งแจ้งเตือน Moph Alert แบบกลุ่ม');
            
            let names = [];
            let inputsHtml = '';
            selectedStaff.forEach((name, cid) => {
                names.push(name);
                inputsHtml += `<input type="hidden" name="cids[]" value="${cid}">`;
            });

            $('#recipient_label').text(`ส่งหาเจ้าหน้าที่กลุ่มจำนวน ${selectedStaff.size} คน (${names.slice(0, 3).join(', ')}${names.length > 3 ? '...' : ''})`);
            $('#modal_cids_container').html(inputsHtml);

            // Open modal
            const modal = new bootstrap.Modal(document.getElementById('sendAlertModal'));
            modal.show();
        }

        // Dispatch alert API via AJAX
        function dispatchAlert(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btn_submit_alert');
            const origContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> กำลังส่งข้อมูล...';

            const cids = Array.from(document.querySelectorAll('input[name="cids[]"]')).map(input => input.value);
            const title = document.getElementById('alert_title').value;
            let htmlVal = document.getElementById('alert_html').value;

            // Auto format to HTML card structure if typed as plain text
            if (!htmlVal.trim().startsWith('<') && !htmlVal.includes('<div') && !htmlVal.includes('<p')) {
                htmlVal = `
                <div style="font-family: 'Sarabun', sans-serif; padding: 16px; background-color: #ffffff; border-left: 5px solid #198754; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin: 8px 0; border-top: 1px solid #f0f0f0; border-right: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0;">
                    <div style="font-size: 16px; font-weight: bold; color: #198754; margin-bottom: 8px; border-bottom: 1px solid #f0f0f0; padding-bottom: 6px;">📢 ${title}</div>
                    <div style="font-size: 14px; color: #333333; line-height: 1.6; padding-top: 4px;">${htmlVal.replace(/\n/g, '<br>')}</div>
                    <div style="margin-top: 16px; font-size: 11px; color: #888888; text-align: right; border-top: 1px dashed #eeeeee; padding-top: 8px;">โรงพยาบาลหัวตะพาน</div>
                </div>`;
            }

            fetch('{{ route("mophalert.hrd.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({ cids, title, html: htmlVal })
            })
            .then(res => res.json())
            .then(res => {
                btn.disabled = false;
                btn.innerHTML = origContent;

                if (res.success) {
                    // Hide modal
                    const modalEl = document.getElementById('sendAlertModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ!',
                        text: res.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        clearAllSelectedStaff();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: res.message,
                        confirmButtonText: 'ตกลง'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = origContent;

                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาดระบบ!',
                    text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ ' + err.message,
                    confirmButtonText: 'ตกลง'
                });
            });
        }
    </script>
@endpush
