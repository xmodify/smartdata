<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสำรองข้อมูล (Backup HOSxP)</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- CSS Stylesheets -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Sarabun', 'Nunito', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            padding: 1.5rem 0.5rem;
        }

        .main-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .header-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
            color: #ffffff;
            padding: 1.5rem 2rem;
        }

        .header-title h4 {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .table-responsive {
            margin: 0;
        }

        .table thead th {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            border-bottom: 2px solid #cbd5e1 !important;
            padding: 1rem 0.75rem;
        }

        .table tbody td {
            padding: 1rem 0.75rem;
            font-size: 0.9rem;
        }

        .badge-success-custom {
            background-color: #dcfce7;
            color: #166534;
            font-weight: 600;
            border: 1px solid #bbf7d0;
        }

        .text-filename {
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .text-filename {
                max-width: 180px;
            }
            .header-section {
                padding: 1rem 1.25rem;
            }
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8rem;
            }
            .table thead th {
                padding: 0.75rem 0.5rem;
                font-size: 0.75rem;
            }
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #64748b;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="container-xl">
    <div class="main-card">
        <!-- Header -->
        <div class="header-section d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="header-title">
                <h4><i class="fas fa-database me-2 text-warning"></i>ประวัติการสำรองข้อมูล HOSxP</h4>
                <div class="text-white-50 small mt-1">แสดงรายการสำรองข้อมูลล่าสุด 10 รายการย้อนหลัง</div>
            </div>
            <div>
                <span class="badge badge-success-custom px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-check-circle me-1"></i> เชื่อมต่อปกติ
                </span>
            </div>
        </div>

        <!-- Latest Backup Highlight (Alert style card) -->
        @if(count($logs) > 0)
            @php $latest = $logs[0]; @endphp
            <div class="p-4 border-bottom bg-light bg-opacity-50">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <span class="text-muted small uppercase fw-bold d-block mb-1">ไฟล์สำรองข้อมูลล่าสุด (Latest Backup File)</span>
                        <h5 class="fw-bold text-primary mb-1 text-filename" title="{{ $latest->backup_filename }}">
                            {{ basename($latest->backup_filename) }}
                        </h5>
                        <div class="text-muted small">
                            <i class="fas fa-server me-1"></i> เครื่องคอมพิวเตอร์: <strong>{{ $latest->backup_computer }}</strong>
                            <span class="mx-2">|</span>
                            <i class="fas fa-hdd me-1"></i> ขนาดไฟล์: <strong>{{ $latest->backup_size ? number_format($latest->backup_size / 1024, 2) . ' MB' : '-' }}</strong>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="text-muted small d-block">เสร็จสิ้นเมื่อ</span>
                        <strong class="text-success fs-5">{{ DateThai($latest->backup_finish_datetime) }}</strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- Table Grid -->
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">ลำดับ</th>
                        <th>วันที่เริ่มสำรองข้อมูล</th>
                        <th>วันที่เสร็จสิ้น</th>
                        <th>ชื่อเครื่องคอมพิวเตอร์</th>
                        <th>ขนาดไฟล์ (MB)</th>
                        <th>ชื่อไฟล์และที่เก็บข้อมูล</th>
                        <th>ประเภท</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $index => $row)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>{{ $row->backup_datetime ? DateThai($row->backup_datetime) : '-' }}</td>
                            <td class="fw-bold text-success">{{ $row->backup_finish_datetime ? DateThai($row->backup_finish_datetime) : '-' }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-20 px-2 py-1">
                                    {{ $row->backup_computer }}
                                </span>
                            </td>
                            <td>{{ $row->backup_size ? number_format($row->backup_size / 1024, 2) : '-' }} MB</td>
                            <td>
                                <span class="text-filename fw-semibold text-dark" title="{{ $row->backup_filename }}">
                                    {{ basename($row->backup_filename) }}
                                </span>
                                <div class="text-muted small text-truncate" style="max-width: 320px;" title="{{ $row->backup_filename }}">
                                    {{ $row->backup_filename }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 px-2 py-1 small">
                                    {{ $row->backup_type ?: 'MySQL' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-white-50"></i>
                                <p class="mb-0">ไม่พบข้อมูลประวัติการสำรองข้อมูล</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        ระบบ SmartData | โรงพยาบาลหัวตะพาน
    </div>
</div>

<!-- JS Libraries -->
<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>
