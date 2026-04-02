@extends('layouts.app')

@section('title', 'SmartData | งานบุคลากร HRD')

@section('topbar_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .page-header-container {
            background: #fff;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
            border: 1px solid #f0f0f0;
        }

        .report-title-box h5 {
            font-size: 1.1rem;
            letter-spacing: -0.01em;
        }

        .header-form-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-group-date {
            width: 155px !important;
        }

        @media (max-width: 1200px) {
            .card-header-flex {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .header-form-controls {
                width: 100%;
                justify-content: flex-end;
            }
        }

        @media (max-width: 768px) {
            .header-form-controls {
                flex-wrap: wrap;
                justify-content: flex-start;
            }

            .input-group-date {
                width: 100% !important;
            }
        }

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            color: #4e73df;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }

        .flatpickr-today-button:hover {
            background: #f8f9fc;
            color: #2e59d9;
        }

        /* Override DataTables UI to match the premium look (skpcard style) */
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

        /* Custom Multi-select for Departments */
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

        .multiselect-item input[type="checkbox"] {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #4e73df;
        }

        .multiselect-item label {
            flex: 1;
            cursor: pointer;
            margin-bottom: 0;
            font-size: 0.85rem;
            color: #5a5c69;
            user-select: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .multiselect-item.selected {
            background-color: #eaecf4;
        }

        .dropdown-toggle-dept {
            background-color: white !important;
            border-radius: 8px !important;
            font-size: 0.8rem !important;
            min-width: 250px;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.4rem 0.75rem !important;
        }
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

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 0rem;
        }

        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
        }

        .dt-buttons {
            margin-bottom: 0 !important;
            display: flex !important;
            align-items: center !important;
        }

        .dataTables_wrapper .d-flex.justify-content-between.align-items-center {
            padding: 10px 0;
            min-height: 50px;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .hrd-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .table-premium thead th {
            background-color: #f8f9fc;
            color: #4e73df;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05rem;
            border-bottom: 2px solid #eaecf4;
            padding: 12px 10px;
        }

        .table-premium tbody td {
            font-size: 0.85rem;
            padding: 12px 10px;
        }

        .badge-count {
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-4">
        <!-- Summary Statistics Row -->
        <div class="row g-4 mb-4 mt-2">
            <div class="col-md-4">
                <div class="card hrd-card text-white overflow-hidden h-100"
                    style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small opacity-75 fw-bold mb-1">จำนวนบุคลากรทั้งหมด</div>
                                <div class="h2 mb-0 fw-bold">{{ number_format($total_all) }} <span class="h6">คน</span>
                                </div>
                            </div>
                            <div class="bg-white bg-opacity-20 p-3 rounded-circle">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card hrd-card text-white overflow-hidden h-100"
                    style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small opacity-75 fw-bold mb-1">ข้าราชการ</div>
                                <div class="h2 mb-0 fw-bold">{{ number_format($total_perm) }} <span class="h6">คน</span>
                                </div>
                            </div>
                            <div class="bg-white bg-opacity-20 p-3 rounded-circle">
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card hrd-card text-white overflow-hidden h-100"
                    style="background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small opacity-75 fw-bold mb-1">พนักงานจ้าง / อื่นๆ</div>
                                <div class="h2 mb-0 fw-bold">{{ number_format($total_other) }} <span class="h6">คน</span>
                                </div>
                            </div>
                            <div class="bg-white bg-opacity-20 p-3 rounded-circle">
                                <i class="fas fa-user-nurse fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-xl-5">
                <div class="card hrd-card h-100">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-pie me-2"></i>สัดส่วนประเภทบุคลากร</h6>
                    </div>
                    <div class="card-body px-4">
                        <div id="type_chart" class="chart-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-7">
                <div class="card hrd-card h-100">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex align-items-center">
                        <h6 class="m-0 fw-bold text-primary"><i class="fas fa-chart-bar me-2"></i>10 อันดับตำแหน่งงาน</h6>
                    </div>
                    <div class="card-body px-4">
                        <div id="position_chart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Personnel Table Card -->
        <div class="card hrd-card mb-5">
            <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom card-header-flex">
                <div class="d-flex align-items-center">
                    <div class="pe-3 py-1">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-stethoscope me-2"></i>
                            ข้อมูลการลงเวลาปฏิบัติงาน
                        </h6>
                        <div class="text-primary small fw-bold mt-1" style="font-size: 0.75rem;">
                            <i class="fas fa-calendar-alt me-1"></i> วันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                        </div>
                    </div>
                </div>

                <!-- Date & Dept Filter Form (Modern Style) -->
                <form action="" method="GET" class="m-0 d-md-flex align-items-center gap-2 header-form-controls">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-muted small text-nowrap">หน่วยงาน:</span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-white dropdown-toggle dropdown-toggle-dept shadow-sm" type="button" 
                                id="deptDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="dropdown-label text-truncate" style="max-width: 200px;">
                                    @if(empty($dept_ids))
                                        -- ทั้งหมด --
                                    @else
                                        เลือก ({{ count($dept_ids) }}) หน่วยงาน
                                    @endif
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-multiselect p-0" aria-labelledby="deptDropdown">
                                <div class="multiselect-header">
                                    <input type="text" class="form-control form-control-sm multiselect-search mb-2" 
                                        placeholder="ค้นหาแผนก..." id="deptSearch">
                                    <div class="form-check ms-1 mt-1">
                                        <input class="form-check-input" type="checkbox" id="selectAllDept" {{ !empty($dept_ids) && count($dept_ids) == count($depts) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-primary small" for="selectAllDept" style="cursor: pointer;">
                                            เลือกทั้งหมด
                                        </label>
                                    </div>
                                </div>
                                <div class="multiselect-item-list" id="deptList">
                                    @foreach($depts as $dept)
                                        <div class="multiselect-item {{ in_array($dept->HR_DEPARTMENT_SUB_SUB_ID, $dept_ids) ? 'selected' : '' }}">
                                            <input type="checkbox" name="dept_ids[]" value="{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}" 
                                                id="dept_{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}" 
                                                class="dept-checkbox"
                                                {{ in_array($dept->HR_DEPARTMENT_SUB_SUB_ID, $dept_ids) ? 'checked' : '' }}>
                                            <label for="dept_{{ $dept->HR_DEPARTMENT_SUB_SUB_ID }}">{{ $dept->HR_DEPARTMENT_SUB_SUB_NAME }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="p-2 border-top bg-light text-center">
                                    <small class="text-muted">เลือกได้หลายรายการ</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                        <span class="fw-bold text-muted small text-nowrap">ช่วงวันที่:</span>
                        <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                        </div>
                        <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary px-3 btn-sm shadow-sm mt-2 mt-md-0" style="border-radius: 8px; height: 31px; font-size: 0.75rem;">
                        <i class="fas fa-search me-1"></i> ค้นหา
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-premium" id="hrd_table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">ลำดับ</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>เบอร์โทร</th>
                                <th>ตำแหน่ง</th>
                                <th>หน่วยงาน</th>
                                <th>ประเภทการจ้าง</th>
                                <th class="text-center">จำนวนวัน</th>
                                <th class="text-center">จำนวนเวร</th>
                                <th class="text-center">ทำรายการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($persons as $index => $person)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $person->HR_PREFIX_NAME }}{{ $person->HR_FNAME }}
                                            {{ $person->HR_LNAME }}</div>
                                    </td>
                                    <td>{{ $person->HR_PHONE ?: '-' }}</td>
                                    <td>{{ $person->POSITION_IN_WORK }}</td>
                                    <td>{{ $person->HR_DEPARTMENT_SUB_SUB_NAME }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-light text-primary border px-2">
                                            {{ $person->HR_PERSON_TYPE_NAME }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-count bg-primary bg-opacity-10 text-primary">
                                            {{ $person->day_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-count bg-info bg-opacity-10 text-info">
                                            {{ $person->shift_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('backoffice.hrd.pdf.summary', $person->id) }}" target="_blank"
                                                class="btn btn-outline-danger btn-sm border-0 bg-danger bg-opacity-10 py-0 px-2"
                                                style="font-size: 0.7rem;">
                                                พิมพ์สรุป
                                            </a>
                                            <a href="{{ route('backoffice.hrd.pdf.detail', $person->id) }}" target="_blank"
                                                class="btn btn-outline-info btn-sm border-0 bg-info bg-opacity-10 py-0 px-2 ms-1"
                                                style="font-size: 0.7rem;">
                                                พิมพ์เวลาจากเครื่องสแกน
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

    <script>
        $(document).ready(function () {
            // Flatpickr Setup
            if (typeof flatpickr !== 'undefined') {
                const yearOffset = 543;
                const commonConfig = {
                    locale: "th",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "j M Y",
                    allowInput: false,
                    onReady: function (selectedDates, dateStr, instance) {
                        if (instance.altInput) {
                            const date = instance.selectedDates[0] || new Date(instance.input.value);
                            if (date && !isNaN(date.getTime())) {
                                const day = date.getDate();
                                const month = instance.l10n.months.shorthand[date.getMonth()];
                                const year = date.getFullYear() + yearOffset;
                                instance.altInput.value = `${day} ${month} ${year}`;
                            }
                        }
                        const container = instance.calendarContainer;
                        if (container && !container.querySelector('.flatpickr-today-button')) {
                            const btn = document.createElement("div");
                            btn.className = "flatpickr-today-button";
                            btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                            btn.addEventListener("mousedown", function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                instance.setDate(new Date());
                                instance.close();
                            });
                            container.appendChild(btn);
                        }
                    },
                    onChange: function (selectedDates, dateStr, instance) {
                        if (instance.altInput && selectedDates.length > 0) {
                            const date = selectedDates[0];
                            const day = date.getDate();
                            const month = instance.l10n.months.shorthand[date.getMonth()];
                            const year = date.getFullYear() + yearOffset;
                            instance.altInput.value = `${day} ${month} ${year}`;
                        }
                    }
                };
                flatpickr("#start_date", commonConfig);
                flatpickr("#end_date", commonConfig);
            }

            // DataTables with Style Override to match skpcard module
            $('#hrd_table').DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success',
                        title: 'HRD_Personnel_List_{{ date('Y-m-d') }}',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }
                ],
                language: {
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    paginate: {
                        previous: "ก่อนหน้า",
                        next: "ถัดไป"
                    }
                },
                order: [],
                pageLength: 10
            });

            // Charts Initialization
            setTimeout(() => {
                try {
                    const typeLabels = @json($chartData['type_labels'] ?? []);
                    const typeValues = (@json($chartData['type_values'] ?? [])).map(Number);
                    if (typeLabels.length > 0) {
                        new ApexCharts(document.querySelector("#type_chart"), {
                            series: typeValues,
                            labels: typeLabels,
                            chart: { type: 'pie', height: 300, fontFamily: 'Nunito, sans-serif' },
                            colors: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                            legend: { position: 'bottom' },
                            stroke: { width: 0 }
                        }).render();
                    }

                    const posLabels = @json($chartData['pos_labels'] ?? []);
                    const posValues = (@json($chartData['pos_values'] ?? [])).map(Number);
                    if (posLabels.length > 0) {
                        new ApexCharts(document.querySelector("#position_chart"), {
                            series: [{ name: 'Count', data: posValues }],
                            chart: { type: 'bar', height: 300, fontFamily: 'Nunito, sans-serif', toolbar: { show: false } },
                            colors: ['#4e73df'],
                            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
                            xaxis: { categories: posLabels }
                        }).render();
                    }
                } catch (e) { }
            }, 300);

            // Department Multi-select Logic
            const deptSearch = document.getElementById('deptSearch');
            const deptList = document.getElementById('deptList');
            const selectAllDept = document.getElementById('selectAllDept');
            const deptCheckboxes = document.querySelectorAll('.dept-checkbox');
            const deptDropdownBtn = document.getElementById('deptDropdown');
            const deptLabel = deptDropdownBtn ? deptDropdownBtn.querySelector('.dropdown-label') : null;

            // Prevent dropdown from closing when clicking inside
            $('.dropdown-menu-multiselect').on('click', function (e) {
                e.stopPropagation();
            });

            // Search Filter
            if (deptSearch) {
                deptSearch.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase();
                    const items = deptList.querySelectorAll('.multiselect-item');
                    items.forEach(item => {
                        const text = item.querySelector('label').textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }

            // Select All Toggle
            if (selectAllDept) {
                selectAllDept.addEventListener('change', function () {
                    const isChecked = this.checked;
                    const items = deptList.querySelectorAll('.multiselect-item');
                    items.forEach(item => {
                        if (item.style.display !== 'none') {
                            const cb = item.querySelector('.dept-checkbox');
                            cb.checked = isChecked;
                            item.classList.toggle('selected', isChecked);
                        }
                    });
                    updateDeptDropdownLabel();
                });
            }

            // Individual Checkbox Click
            deptCheckboxes.forEach(cb => {
                cb.addEventListener('change', function () {
                    this.closest('.multiselect-item').classList.toggle('selected', this.checked);
                    updateDeptDropdownLabel();
                });
            });

            function updateDeptDropdownLabel() {
                if (!deptLabel) return;
                const checkboxes = document.querySelectorAll('.dept-checkbox');
                const checkedCount = document.querySelectorAll('.dept-checkbox:checked').length;
                
                if (checkedCount === 0) {
                    deptLabel.textContent = '-- ทั้งหมด --';
                } else if (checkedCount === checkboxes.length) {
                    deptLabel.textContent = 'ทุกหน่วยงาน (' + checkedCount + ')';
                } else {
                    deptLabel.textContent = 'เลือก (' + checkedCount + ') หน่วยงาน';
                }
            }
        });
    </script>
@endpush