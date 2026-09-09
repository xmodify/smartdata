@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
    <a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            --secondary-gradient: linear-gradient(135deg, #858796 0%, #60616f 100%);
            --success-gradient: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            --info-gradient: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
            --warning-gradient: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
            --danger-gradient: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);
        }

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
            width: 160px !important;
        }

        .input-group-budget {
            width: 250px !important;
        }

        @media (max-width: 768px) {
            .page-header-container {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 1rem;
            }

            .header-form-controls {
                width: 100%;
                flex-wrap: wrap;
            }

            .input-group-date,
            .input-group-budget {
                width: 100% !important;
            }
        }

        .flatpickr-today-button {
            border-top: 1px solid #e6e6e6;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            color: #e74a3b;
            font-weight: bold;
            font-size: 0.9rem;
            transition: background 0.2s;
            border-radius: 0 0 12px 12px;
        }

        .flatpickr-today-button:hover {
            background: #fff1f0;
            color: #be2617;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
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

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #e74a3b !important;
            color: white !important;
            border: 1px solid #e74a3b !important;
            border-radius: 0.5rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #fff1f0 !important;
            color: #e74a3b !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.5rem !important;
        }

        table.dataTable thead th {
            background-color: #f8f9fc !important;
            color: #e74a3b !important;
            font-weight: 700 !important;
            border-bottom: 2px solid #edeff4 !important;
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

@section('content')
    <div class="container-fluid px-2 px-md-4">
        <!-- Header -->
        <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex align-items-center report-title-box">
                <div class="ps-3 py-1">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-bed text-danger me-2"></i>
                        {{ $title }}
                    </h5>
                    <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <form action="" method="GET" class="m-0">
                    <div class="input-group input-group-sm shadow-sm" style="width: 230px; border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-light text-danger border-end-0 fw-bold" style="font-size: 0.8rem;">เลือก</span>
                        <select class="form-select border-start-0" name="budget_year" onchange="this.form.submit()" style="font-size: 0.85rem;">
                            @foreach ($budget_year_select as $row)
                                <option value="{{ $row->LEAVE_YEAR_ID }}"
                                    {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                    ปีงบ {{ $row->LEAVE_YEAR_NAME }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card text-white h-100 shadow-sm" style="background: var(--danger-gradient);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="small opacity-75">จำนวนผู้เสียชีวิตทั้งหมด</div>
                                <div class="h2 mb-0 fw-bold">{{ number_format(count($death_list)) }}</div>
                                <div class="small mt-2"><i class="fas fa-calendar-day"></i> เฉพาะผู้ป่วยใน (1)</div>
                            </div>
                            <div class="bg-white bg-opacity-25 p-3 rounded-circle align-self-center">
                                <i class="fas fa-bed fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="m-0 fw-bold text-danger">แนวโน้มรายเดือน (ตามช่วงเวลาที่เลือก)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-header bg-transparent border-0 pt-3 pb-0">
                        <h6 class="m-0 fw-bold text-warning">แนวโน้ม 5 ปี ย้อนหลัง (ปีงบประมาณ)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="yearlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Patient List Table Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 border-0" style="border-radius: 15px 15px 0 0;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h6 class="m-0 fw-bold text-danger">
                            <i class="fas fa-user-injured me-1"></i> รายชื่อผู้เสียชีวิต (เฉพาะผู้ป่วยใน)
                        </h6>
                        <small class="text-muted">
                            ช่วงวันที่: <span id="displayDateRange" class="fw-bold text-dark">{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}</span>
                        </small>
                    </div>

                    <!-- Independent Table Filter -->
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" id="table_start_date" class="form-control border-start-0 ps-0 text-center"
                                value="{{ $table_start_date }}" placeholder="เริ่ม">
                        </div>
                        <span class="text-muted small">ถึง</span>
                        <div class="input-group input-group-sm" style="width: 140px;">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" id="table_end_date" class="form-control border-start-0 ps-0 text-center"
                                value="{{ $table_end_date }}" placeholder="สิ้นสุด">
                        </div>
                        <button type="button" id="btnFilterTable" class="btn btn-sm btn-danger px-3 shadow-sm" style="border-radius: 6px;">
                            <i class="fas fa-search me-1"></i> ค้นหา
                        </button>
                        <div class="btn-group btn-group-sm ms-1 shadow-sm">
                            <button type="button" id="btnCurrentMonth" class="btn btn-outline-secondary" title="เลือกเดือนปัจจุบัน">
                                เดือนนี้
                            </button>
                            <button type="button" id="btnPrevMonth" class="btn btn-outline-secondary" title="เลือกเดือนก่อนหน้า">
                                เดือนก่อน
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-4" id="tableContainer">
                @include('hosxp.death.partials._table_death')
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
        <script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
        <script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
        <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
        <script src="{{ asset('vendor/chartjs/chartjs-plugin-datalabels.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
        <script src="{{ asset('vendor/flatpickr/th.js') }}"></script>

        <script>
            $(document).ready(function() {
                Chart.register(ChartDataLabels);

                const monthlyData = @json($monthly_trend);
                const yearlyData = @json($yearly_trend);

                // Monthly Trend
                new Chart(document.getElementById('monthlyChart'), {
                    type: 'line',
                    data: {
                        labels: monthlyData.map(d => d.label),
                        datasets: [{
                            label: 'จำนวนผู้เสียชีวิต (ราย)',
                            data: monthlyData.map(d => d.total_count),
                            borderColor: '#e74a3b',
                            backgroundColor: 'rgba(231, 74, 59, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#e74a3b',
                            pointBorderColor: '#fff',
                            pointRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#e74a3b',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                // Yearly Trend
                new Chart(document.getElementById('yearlyChart'), {
                    type: 'bar',
                    data: {
                        labels: yearlyData.map(d => d.year_be),
                        datasets: [{
                            label: 'จำนวนผู้เสียชีวิต (ราย)',
                            data: yearlyData.map(d => d.total_count),
                            backgroundColor: '#f6c23e',
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#f6c23e',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

            function initPatientTable() {
                if ($.fn.DataTable.isDataTable('#reportTable')) {
                    $('#reportTable').DataTable().destroy();
                }
                $('#reportTable').DataTable({
                    dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-success',
                        title: '{{ $title }}',
                        messageTop: function() {
                            return 'ช่วงวันที่: ' + ($('#displayDateRange').text() || '{{ DateThai($table_start_date) }} ถึง {{ DateThai($table_end_date) }}');
                        }
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
            }

            let tableStartPicker, tableEndPicker;
            if (typeof flatpickr !== 'undefined') {
                const yearOffset = 543;
                const commonConfig = {
                    locale: "th",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "j M Y",
                    allowInput: false,
                    onReady: function(s, d, i) {
                        const container = i.calendarContainer;
                        if (container && !container.querySelector('.flatpickr-today-button')) {
                            const btn = document.createElement("div");
                            btn.className = "flatpickr-today-button";
                            btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                            btn.addEventListener("click", function() {
                                i.setDate(new Date());
                                i.close();
                            });
                            container.appendChild(btn);
                        }

                        if (i.altInput) {
                            const date = i.selectedDates[0] || new Date(i.input.value);
                            if (date && !isNaN(date.getTime())) {
                                i.altInput.value =
                                    `${date.getDate()} ${i.l10n.months.shorthand[date.getMonth()]} ${date.getFullYear() + yearOffset}`;
                            }
                        }
                    },
                    onChange: function(s, d, i) {
                        if (i.altInput && s.length > 0) {
                            const date = s[0];
                            setTimeout(() => {
                                i.altInput.value =
                                    `${date.getDate()} ${i.l10n.months.shorthand[date.getMonth()]} ${date.getFullYear() + yearOffset}`;
                            }, 10);
                        }
                    }
                };
                tableStartPicker = flatpickr("#table_start_date", commonConfig);
                tableEndPicker = flatpickr("#table_end_date", commonConfig);
            }

            function loadTableData(startDate, endDate) {
                const $btn = $('#btnFilterTable');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> ค้นหา...');
                $('#tableContainer').css('opacity', '0.5');

                $.ajax({
                    url: "{{ route('hosxp.death.index') }}",
                    method: 'GET',
                    data: {
                        budget_year: "{{ $budget_year }}",
                        table_start_date: startDate,
                        table_end_date: endDate
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success && res.html) {
                            if ($.fn.DataTable.isDataTable('#reportTable')) {
                                $('#reportTable').DataTable().destroy();
                            }
                            $('#tableContainer').html(res.html);
                            initPatientTable();
                            $('#displayDateRange').text(`${res.start_date_thai} ถึง ${res.end_date_thai}`);
                        }
                    },
                    error: function(err) {
                        console.error("Failed to load table data", err);
                        alert("เกิดข้อผิดพลาดในการโหลดข้อมูลตาราง กรุณาลองใหม่อีกครั้ง");
                    },
                    complete: function() {
                        $('#tableContainer').css('opacity', '1');
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            }

            $('#btnFilterTable').on('click', function() {
                const s = $('#table_start_date').val();
                const e = $('#table_end_date').val();
                loadTableData(s, e);
            });

            $('#btnCurrentMonth').on('click', function() {
                const now = new Date();
                const y = now.getFullYear();
                const m = String(now.getMonth() + 1).padStart(2, '0');
                const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
                const s = `${y}-${m}-01`;
                const e = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

                if (tableStartPicker) tableStartPicker.setDate(s, true);
                if (tableEndPicker) tableEndPicker.setDate(e, true);
                loadTableData(s, e);
            });

            $('#btnPrevMonth').on('click', function() {
                const now = new Date();
                const prev = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                const y = prev.getFullYear();
                const m = String(prev.getMonth() + 1).padStart(2, '0');
                const lastDay = new Date(y, prev.getMonth() + 1, 0).getDate();
                const s = `${y}-${m}-01`;
                const e = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;

                if (tableStartPicker) tableStartPicker.setDate(s, true);
                if (tableEndPicker) tableEndPicker.setDate(e, true);
                loadTableData(s, e);
            });

            initPatientTable();
            });
        </script>
    @endpush
@endsection
