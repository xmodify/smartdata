@extends('layouts.app')

@section('title', 'จัดการบัตรสังฆะประชาร่วมใจ - SmartData')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    /* Override DataTables UI to match the premium look */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.5rem !important;
        padding: 0.3rem 0.75rem !important;
        outline: none !important;
        font-size: 0.85rem !important;
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
        background: #4e73df !important;
        color: white !important;
        border: 1px solid #4e73df !important;
        border-radius: 0.5rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f8f9fc !important;
        color: #4e73df !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.5rem !important;
    }
    
    table.dataTable thead th {
        background-color: #f8f9fc !important;
        color: #4e73df !important;
        font-weight: 700 !important;
        border-bottom: 2px solid #e3e6f0 !important;
        font-size: 0.85rem !important;
    }

    .nav-pills .nav-link {
        border-radius: 0.5rem;
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        font-size: 0.85rem;
        color: #6c757d;
        transition: all 0.2s;
    }

    .nav-pills .nav-link.active {
        background-color: #4e73df;
        box-shadow: 0 4px 6px rgba(78, 115, 223, 0.2);
    }
    
    /* Thai Datepicker fixes */
    .datepicker {
        z-index: 1600 !important; /* Ensure it appears above modals */
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
@endpush

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-md-center flex-column flex-md-row gap-3">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill me-3 shadow-sm transition-all hover-translate-x">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </a>
            <div>
                <h2 class="fw-bold mb-0 text-dark"><i class="fas fa-address-card me-2 text-warning"></i>บัตรสังฆะประชาร่วมใจ</h2>
                <div class="d-flex align-items-center gap-2">
                    <p class="text-muted small mb-0">ระบบจัดการและเพิ่มข้อมูลการซื้อบัตร</p>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2" style="font-size: 0.7rem;">ปีงบประมาณ {{ $budget_year }}</span>
                </div>
            </div>
        </div>
        
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Budget Year Selector -->
            <form action="{{ route('skpcard.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm shadow-sm" style="width: 200px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                    <select name="budget_year" class="form-select border-start-0 ps-0" onchange="this.form.submit()">
                        @foreach($budget_year_select as $year)
                            <option value="{{ $year->LEAVE_YEAR_ID }}" {{ $budget_year == $year->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                {{ $year->LEAVE_YEAR_NAME }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <button class="btn btn-info shadow-sm rounded-pill px-3 text-white fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#statsModal">
                <i class="fas fa-chart-line me-2"></i>ดูสถิติ
            </button>

            <button class="btn btn-warning shadow-sm rounded-pill px-4 text-dark fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#addCardModal">
                <i class="fas fa-plus-circle me-2"></i>เพิ่มข้อมูล
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
            <div class="d-flex justify-content-between align-items-center">
                <ul class="nav nav-pills" id="statusTabs" role="tablist">
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link active" data-status="all" type="button">
                            <i class="fas fa-list me-2"></i>ทั้งหมด
                        </button>
                    </li>
                    <li class="nav-item me-2" role="presentation">
                        <button class="nav-link" data-status="active" type="button">
                            <i class="fas fa-check-circle me-2 text-success"></i>ปกติ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-status="expired" type="button">
                            <i class="fas fa-exclamation-circle me-2 text-danger"></i>หมดอายุ
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="table-responsive p-3">
            <table id="skpcard_table" class="table table-hover align-middle mb-0 w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ข้อมูลผู้ถือบัตร</th>
                        <th>วันที่ซื้อ</th>
                        <th>วันหมดอายุ</th>
                        <th>ราคา</th>
                        <th>เลขที่ใบเสร็จ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cards as $index => $card)
                    @php
                        $isExpired = $card->ex_date && $card->ex_date->isPast();
                        $status = $isExpired ? 'expired' : 'active';
                        $th_months = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
                    @endphp
                    <tr data-status="{{ $status }}">
                        <td class="text-muted small">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $card->name }}</div>
                            <div class="small text-muted"><i class="fas fa-id-card me-1 small"></i>{{ $card->cid }}</div>
                            <div class="small text-muted"><i class="fas fa-phone me-1 small"></i>{{ $card->phone ?: '-' }}</div>
                        </td>
                        <td>{{ $card->buy_date ? $card->buy_date->format('j') . ' ' . $th_months[(int)$card->buy_date->format('m')] . ' ' . ($card->buy_date->format('Y') + 543) : '-' }}</td>
                        <td>
                            <span class="badge {{ $isExpired ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} rounded-pill px-3">
                                {{ $card->ex_date ? $card->ex_date->format('j') . ' ' . $th_months[(int)$card->ex_date->format('m')] . ' ' . ($card->ex_date->format('Y') + 543) : '-' }}
                                @if($isExpired) (หมดอายุ) @endif
                            </span>
                        </td>
                        <td><span class="fw-bold {{ (float)$card->price >= 1500 ? 'text-success' : 'text-info' }}">{{ number_format((float)$card->price, 2) }}</span> ฿</td>
                        <td><code>{{ $card->rcpt ?: '-' }}</code></td>
                        <td class="text-center">
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stats Modal -->
<div class="modal fade" id="statsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-info text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-chart-bar me-2"></i>สถิติการซื้อบัตร ปีงบประมาณ {{ $budget_year }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="skp_chart" style="min-height: 400px;"></div>
                <div class="row mt-4 g-3">
                    <div class="col-md-4">
                        <div class="card border-0 bg-primary-subtle rounded-lg p-3 text-center">
                            <div class="small text-primary fw-bold">ยอดจำหน่ายรวมทั้งสิ้น</div>
                            <div class="h3 fw-bold text-primary mb-0 mt-1">{{ number_format(array_sum($chartData['total_income'])) }} ฿</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-success-subtle rounded-lg p-3 text-center">
                            <div class="small text-success fw-bold">จำนวนบัตรราคา 1,500 ฿</div>
                            <div class="h3 fw-bold text-success mb-0 mt-1">{{ number_format(array_sum($chartData['count_1500'])) }} ใบ</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-info-subtle rounded-lg p-3 text-center">
                            <div class="small text-info fw-bold">จำนวนบัตรราคา 1,000 ฿</div>
                            <div class="h3 fw-bold text-info mb-0 mt-1">{{ number_format(array_sum($chartData['count_1000'])) }} ใบ</div>
                        </div>
                    </div>
                </div>
            </div>
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
                            <input type="text" name="birthday" class="form-control datepicker" data-provide="datepicker" data-date-language="th" data-date-format="yyyy-mm-dd" placeholder="วศ/ดด/ปปปป">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ราคาบัตร</label>
                            <select name="price" id="add_price" class="form-select" required>
                                <option value="0.00">0.00</option>
                                <option value="1000.00" selected>1000</option>
                                <option value="1500.00">1500</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">ที่อยู่</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">วันที่ซื้อบัตร</label>
                            <input type="text" name="buy_date" class="form-control datepicker" data-provide="datepicker" data-date-language="th" data-date-format="yyyy-mm-dd" value="{{ date('Y-m-d') }}" required>
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
                            <input type="text" name="birthday" id="edit_birthday" class="form-control datepicker" data-provide="datepicker" data-date-language="th" data-date-format="yyyy-mm-dd">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ราคาบัตร</label>
                            <select name="price" id="edit_price" class="form-select" required>
                                <option value="0.00">0.00</option>
                                <option value="1000.00">1000</option>
                                <option value="1500.00">1500</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">ที่อยู่</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">วันที่ซื้อบัตร</label>
                            <input type="text" name="buy_date" id="edit_buy_date" class="form-control datepicker" data-provide="datepicker" data-date-language="th" data-date-format="yyyy-mm-dd" required>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.th.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    $(document).ready(function() {
        // Initialize Thai Datepicker
        $('.datepicker').datepicker({
            language: 'th',
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            thaiyear: true // This is specific to some th-aware forks but we'll see
        });
        // Chart Initialization
        const chartOptions = {
            series: [
                {
                    name: 'บัตร 1,500 ฿ (ใบ)',
                    type: 'column',
                    data: @json($chartData['count_1500'])
                },
                {
                    name: 'บัตร 1,000 ฿ (ใบ)',
                    type: 'column',
                    data: @json($chartData['count_1000'])
                },
                {
                    name: 'รายได้รวม (บาท)',
                    type: 'line',
                    data: @json($chartData['total_income'])
                }
            ],
            chart: {
                height: 400,
                type: 'line',
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: false,
                        zoom: false,
                        zoomin: false,
                        zoomout: false,
                        pan: false,
                        reset: false
                    }
                }
            },
            stroke: {
                width: [0, 0, 4], 
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '60%', 
                    borderRadius: 2,
                    dataLabels: {
                        position: 'center', // Put labels inside bars
                    },
                }
            },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [0, 1, 2], // Show on all
                formatter: function (val) {
                    return val.toLocaleString();
                },
                background: {
                    enabled: false // Remove the background box
                },
                style: {
                    fontSize: '11px',
                    colors: ["#ffffff", "#ffffff", "#304758"] // White for bars, Dark for line
                }
            },
            colors: ['#198754', '#0dcaf0', '#f1c40f'], // Green, Cyan, Yellow
            labels: @json($chartData['labels']),
            xaxis: {
                type: 'category'
            },
            yaxis: [
                {
                    seriesName: 'บัตร 1,500 ฿ (ใบ)',
                    title: {
                        text: 'จำนวนบัตร (ใบ)',
                    },
                    labels: {
                        formatter: function(val) { return val.toFixed(0); }
                    }
                },
                {
                    seriesName: 'บัตร 1,500 ฿ (ใบ)',
                    show: false
                },
                {
                    seriesName: 'รายได้รวม (บาท)',
                    opposite: true,
                    title: {
                        text: 'รายได้รวม (บาท)'
                    },
                    labels: {
                        formatter: function (val) {
                            return val.toLocaleString();
                        }
                    }
                }
            ],
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (y) {
                        if (typeof y !== "undefined") {
                            return y.toLocaleString();
                        }
                        return y;
                    }
                }
            },
            legend: {
                position: 'top'
            }
        };

        let chart;
        $('#statsModal').on('shown.bs.modal', function () {
            if (!chart) {
                chart = new ApexCharts(document.querySelector("#skp_chart"), chartOptions);
                chart.render();
            }
        });
        // Custom DataTables filter for status
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const activeStatus = $('#statusTabs .nav-link.active').data('status');
                if (activeStatus === 'all') return true;
                
                const rowStatus = $(settings.aoData[dataIndex].nTr).data('status');
                return rowStatus === activeStatus;
            }
        );

        const table = $('#skpcard_table').DataTable({
            dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                    className: 'btn btn-success',
                    title: 'ข้อมูลการซื้อบัตรสังฆะประชาร่วมใจ',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                }
            ],
            language: {
                search: "ค้นหา:",
                lengthMenu: "แสดง _MENU_ รายการ",
                info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                infoEmpty: "ไม่มีข้อมูล",
                infoFiltered: "(ค้นหาจากทั้งหมด _MAX_ รายการ)",
                paginate: {
                    previous: "ก่อนหน้า",
                    next: "ถัดไป"
                }
            },
            pageLength: 10,
            order: [[0, 'desc']]
        });

        // Tab click handling
        $('#statusTabs .nav-link').on('click', function() {
            $('#statusTabs .nav-link').removeClass('active');
            $(this).addClass('active');
            table.draw();
        });
    });

    // Edit Card Modal Population
    document.querySelectorAll('.edit-card').forEach(button => {
        button.addEventListener('click', function() {
            const card = JSON.parse(this.dataset.card);
            const form = document.getElementById('editCardForm');
            form.action = `{{ url('/') }}/skpcard/${card.id}`;
            
            document.getElementById('edit_cid').value = card.cid;
            document.getElementById('edit_name').value = card.name;
            
            // Update datepicker values
            $('#edit_birthday').datepicker('update', card.birthday ? card.birthday.substring(0, 10) : '');
            
            document.getElementById('edit_phone').value = card.phone;
            
            // Set select value for price
            const priceVal = parseFloat(card.price).toFixed(2);
            $('#edit_price').val(priceVal);
            
            document.getElementById('edit_address').value = card.address;
            
            // Update datepicker values
            $('#edit_buy_date').datepicker('update', card.buy_date ? card.buy_date.substring(0, 10) : '');
            
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
