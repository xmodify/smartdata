<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard-ผู้ป่วยในรอสรุป Chart</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- CSS Stylesheets -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/buttons.dataTables.min.css') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Sarabun', 'Nunito', sans-serif;
            background-color: #f3f4f6;
            color: #374151;
            padding: 2rem 1rem;
        }

        .main-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            margin-bottom: 2rem;
        }

        .header-section {
            border-bottom: 1px solid #f3f4f6;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-title h4 {
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 0.25rem;
        }

        .chart-container {
            position: relative;
            min-height: 380px;
            width: 100%;
        }

        /* Nav Pills Styling */
        .nav-pills-custom {
            display: flex;
            gap: 12px;
            margin-bottom: 1.5rem;
        }

        .nav-pills-custom .nav-link {
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            padding: 10px 20px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            display: inline-flex;
            align-items: center;
        }

        .nav-pills-custom .nav-link:hover {
            transform: translateY(-2px);
        }

        .nav-pills-custom .nav-link.btn-diag {
            background-color: #ffffff;
            color: #c53030;
            border-color: #fee2e2;
        }

        .nav-pills-custom .nav-link.btn-diag.active {
            background-color: #fde8e8;
            color: #9b1c1c;
            border-color: #fbd5d5;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.08);
        }

        .nav-pills-custom .nav-link.btn-icd10 {
            background-color: #ffffff;
            color: #d97706;
            border-color: #fef3c7;
        }

        .nav-pills-custom .nav-link.btn-icd10.active {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.08);
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            padding: 0.3rem 0.75rem !important;
            font-size: 0.85rem !important;
            outline: none !important;
        }

        .dt-buttons .btn-success {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            border-radius: 0.5rem !important;
            font-weight: 600 !important;
            padding: 0.35rem 0.8rem !important;
            font-size: 0.85rem !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.15) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            color: white !important;
            border: 1px solid #2563eb !important;
            border-radius: 0.5rem !important;
        }

        /* Header Colors Matching Respective Tab Colors (Soft Pastel) */
        #tableDiagText thead th {
            background-color: #fde8e8 !important;
            color: #9b1c1c !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            border-bottom: 2px solid #fbd5d5 !important;
        }

        #tableIcd10 thead th {
            background-color: #fef3c7 !important;
            color: #92400e !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            border-bottom: 2px solid #fde68a !important;
        }

        /* Ensure sorting icons stand out on colored header backgrounds */
        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc {
            background-color: inherit !important;
        }

        .footer {
            text-align: center;
            margin-top: 3rem;
            color: #6b7280;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="container-lg">
    <!-- Main Dashboard Card -->
    <div class="main-card">
        <!-- Header -->
        <div class="header-section">
            <div class="header-title">
                <h4><i class="fas fa-file-invoice text-primary me-2"></i>Dashboard-ผู้ป่วยในรอสรุป Chart</h4>
                <div class="text-muted small mt-1">
                    ข้อมูลประจำปีงบประมาณ {{ $budget_year }}
                    @if($start_date && $end_date)
                        (ระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }})
                    @endif
                </div>
            </div>

            <!-- Budget Year Selector Form -->
            <div>
                <form action="" method="GET" class="d-flex align-items-center gap-2 m-0">
                    <label class="fw-bold text-muted small mb-0 text-nowrap">ปีงบประมาณ:</label>
                    <select class="form-select form-select-sm" name="budget_year" onchange="this.form.submit()" style="width: 180px; border-radius: 8px;">
                        @foreach ($budget_year_select as $row)
                            <option value="{{ $row->LEAVE_YEAR_ID }}"
                                {{ (int) $budget_year === (int) $row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                {{ $row->LEAVE_YEAR_NAME }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <!-- Chart -->
        <div class="p-4 border-bottom" style="background-color: #fafafa;">
            <div class="chart-container">
                <div id="stackedBarChart"></div>
            </div>
        </div>

        <!-- Tabs & Lists -->
        <div class="p-4">
            <!-- Navigation Pills -->
            <div class="nav nav-pills nav-pills-custom" id="pills-tab" role="tablist">
                <button class="nav-link btn-diag active" id="pills-diag-tab" data-bs-toggle="pill" data-bs-target="#pills-diag" type="button" role="tab" aria-controls="pills-diag" aria-selected="true">
                    <i class="fas fa-user-md me-1"></i> รอแพทย์สรุป Chart 
                    <span class="badge bg-danger ms-1">{{ count($non_diagtext_list) }}</span>
                </button>
                <button class="nav-link btn-icd10" id="pills-icd10-tab" data-bs-toggle="pill" data-bs-target="#pills-icd10" type="button" role="tab" aria-controls="pills-icd10" aria-selected="false">
                    <i class="fas fa-code me-1"></i> รอลงบันทึก ICD10 
                    <span class="badge bg-warning text-dark ms-1">{{ count($non_icd10_list) }}</span>
                </button>
            </div>

            <!-- Tab Content Tables -->
            <div class="tab-content" id="pills-tabContent">
                <!-- Waiting for Doctor Summary -->
                <div class="tab-pane fade show active" id="pills-diag" role="tabpanel" aria-labelledby="pills-diag-tab">
                    <div class="table-responsive">
                        <table id="tableDiagText" class="table table-hover align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">ลำดับ</th>
                                    <th>Ward</th>
                                    <th class="text-center">AN</th>
                                    <th>แพทย์เจ้าของคนไข้</th>
                                    <th class="text-center">วันที่จำหน่าย</th>
                                    <th class="text-center">จำนวนวันคงค้าง</th>
                                    <th class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($non_diagtext_list as $index => $row)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>{{ $row->ward }}</td>
                                        <td class="text-center fw-bold text-primary">{{ $row->an }}</td>
                                        <td>{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}</td>
                                        <td class="text-center">{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 fw-bold">
                                                {{ $row->dch_day }} วัน
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger px-2 py-1 small">รอแพทย์สรุป Chart</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Waiting for ICD10 Coding -->
                <div class="tab-pane fade" id="pills-icd10" role="tabpanel" aria-labelledby="pills-icd10-tab">
                    <div class="table-responsive">
                        <table id="tableIcd10" class="table table-hover align-middle" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">ลำดับ</th>
                                    <th>Ward</th>
                                    <th class="text-center">AN</th>
                                    <th>แพทย์เจ้าของคนไข้</th>
                                    <th class="text-center">วันที่จำหน่าย</th>
                                    <th class="text-center">จำนวนวันคงค้าง</th>
                                    <th class="text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($non_icd10_list as $index => $row)
                                    <tr>
                                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                        <td>{{ $row->ward }}</td>
                                        <td class="text-center fw-bold text-primary">{{ $row->an }}</td>
                                        <td>{{ $row->owner_doctor_name ?: 'ไม่ระบุแพทย์' }}</td>
                                        <td class="text-center">{{ $row->dchdate ? DateThai($row->dchdate) : '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 fw-bold" style="color: #b07d0d !important;">
                                                {{ $row->dch_day }} วัน
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark px-2 py-1 small">รอลงบันทึก ICD10</span>
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

    <!-- Page Footer -->
    <div class="footer">
        พิมพ์โดยระบบ SmartData | โรงพยาบาลหัวตะพาน
    </div>
</div>

<!-- JS Libraries -->
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('vendor/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/buttons.html5.min.js') }}"></script>
<script src="{{ asset('vendor/apexcharts/apexcharts.min.js') }}"></script>

<script>
    $(document).ready(function() {
        // --- Stacked Bar Chart (ApexCharts) ---
        const chartData = @json($chart_data);
        
        // Calculate dynamic height based on number of doctors
        const numDoctors = chartData.doctors.length;
        const minHeight = Math.max(380, numDoctors * 42);
        document.getElementById('stackedBarChart').style.height = minHeight + 'px';

        const options = {
            series: [{
                name: 'รอสรุป Chart',
                data: chartData.non_diagtext.map(Number)
            }, {
                name: 'รอบันทึก ICD10',
                data: chartData.non_icd10.map(Number)
            }],
            chart: {
                type: 'bar',
                height: minHeight,
                stacked: true,
                toolbar: {
                    show: true
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '75%',
                }
            },
            colors: ['#dc3545', '#f6c23e'], // Red and Yellow/Orange
            xaxis: {
                categories: chartData.doctors.map(d => d || 'ไม่ระบุแพทย์'),
                labels: {
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Sarabun, sans-serif'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '12px',
                        fontWeight: 'bold',
                        fontFamily: 'Sarabun, sans-serif'
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                offsetX: 10,
                fontSize: '13px',
                fontFamily: 'Sarabun, sans-serif',
                markers: {
                    radius: 4
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '11px',
                    fontWeight: 'bold',
                    colors: ['#fff']
                },
                formatter: function(val) {
                    return val > 0 ? val : '';
                }
            },
            grid: {
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };

        const chart = new ApexCharts(document.querySelector("#stackedBarChart"), options);
        chart.render();

        // --- DataTables ---
        function initTable(id, excelTitle) {
            return $(id).DataTable({
                dom: '<"d-flex justify-content-between align-items-center mb-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                buttons: [{
                    extend: 'excelHtml5',
                    text: '<i class="fa-solid fa-file-excel me-1"></i> Excel',
                    className: 'btn btn-success',
                    title: excelTitle,
                    messageTop: 'Dashboard-ผู้ป่วยในรอสรุป Chart - ปีงบประมาณ {{ $budget_year }}'
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

        const tableDiag = initTable('#tableDiagText', 'รายชื่อผู้ป่วยในรอแพทย์สรุป Chart');
        const tableIcd10 = initTable('#tableIcd10', 'รายชื่อผู้ป่วยในรอลงบันทึก ICD10');

        // Adjust table headers when switching tabs
        $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });
    });
</script>

</body>
</html>
