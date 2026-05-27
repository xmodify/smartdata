<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์ข้อมูลงานบริการ CT Scan</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 13px;
            color: #333;
            background-color: #fff;
            line-height: 1.4;
        }
        .print-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px double #dee2e6;
            padding-bottom: 15px;
        }
        .print-header h4 {
            font-weight: 700;
            margin-bottom: 5px;
            color: #111;
        }
        .print-header .subtitle {
            font-size: 14px;
            color: #555;
        }
        .metadata-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .metadata-table td {
            padding: 3px 5px;
            border: none !important;
        }
        .table-print {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table-print th {
            background-color: #f8f9fa !important;
            color: #000 !important;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #000 !important;
            padding: 6px 4px;
            font-size: 11px;
        }
        .table-print td {
            border: 1px solid #000 !important;
            padding: 6px 4px;
            font-size: 11px;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .fw-bold {
            font-weight: bold;
        }
        .no-print-zone {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        @media print {
            .no-print-zone {
                display: none !important;
            }
            body {
                padding: 0;
                margin: 0;
            }
            @page {
                size: A4 landscape;
                margin: 1.5cm 1cm 1.5cm 1cm;
            }
            .table-print th {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-zone d-flex justify-content-between align-items-center">
        <div>
            <span class="fw-bold"><i class="fas fa-print text-primary me-2"></i> โหมดการพิมพ์รายงาน CT Scan</span>
        </div>
        <div>
            <button onclick="window.print();" class="btn btn-primary btn-sm me-2">
                <i class="fas fa-print me-1"></i> พิมพ์รายงาน
            </button>
            <button onclick="window.close();" class="btn btn-secondary btn-sm">
                <i class="fas fa-times me-1"></i> ปิดหน้าต่าง
            </button>
        </div>
    </div>

    <div class="container-fluid px-4">
        <!-- Report Header -->
        <div class="print-header">
            <h4>{{ $title }}</h4>
            <div class="subtitle">
                ปีงบประมาณ {{ $budget_year }} | ระหว่างวันที่ {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
            </div>
            <div class="text-muted small mt-1" style="font-size: 11px;">
                ข้อมูล ณ วันที่ {{ DateThai(date('Y-m-d')) }} เวลา {{ date('H:i') }} น.
            </div>
        </div>

        <!-- Table -->
        <table class="table-print">
            <thead>
                <tr>
                    <th style="width: 40px;">ลำดับ</th>
                    <th style="width: 50px;">ประเภท</th>
                    <th style="width: 100px;">วันที่ / เวลา</th>
                    <th style="min-width: 140px; text-align: left;">ชื่อ-สกุล</th>
                    <th style="width: 75px;">HN</th>
                    <th style="width: 75px;">AN</th>
                    <th style="width: 110px; text-align: left;">สิทธิการรักษา</th>
                    <th style="text-align: left;">รายการ</th>
                    <th style="width: 45px;">จำนวน</th>
                    <th style="width: 80px; text-align: right;">เรียกเก็บ</th>
                    <th style="width: 80px; text-align: right;">วางบิล</th>
                    <th style="width: 80px; text-align: right;">บริษัท CT</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total_qty = 0;
                    $total_bill = 0;
                    $total_claim = 0;
                    $total_ct = 0;
                @endphp
                @forelse($patients as $row)
                    @php
                        $total_qty += $row->qty;
                        $total_bill += $row->price_bill;
                        $total_claim += $row->price_claim;
                        $total_ct += $row->price_ct;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $row->depart }}</td>
                        <td class="text-center">
                            {{ $row->rxdate ? DateThai($row->rxdate) : '-' }}
                            @if($row->rxtime)
                                <br><span class="text-muted" style="font-size: 10px;">{{ substr($row->rxtime, 0, 5) }} น.</span>
                            @endif
                        </td>
                        <td>{{ $row->ptname }}</td>
                        <td class="text-center">{{ $row->hn }}</td>
                        <td class="text-center">{{ $row->an ?? '-' }}</td>
                        <td>{{ $row->pttype }}</td>
                        <td>{{ $row->item_name }}</td>
                        <td class="text-center">{{ number_format($row->qty) }}</td>
                        <td class="text-end">{{ number_format($row->price_claim, 2) }}</td>
                        <td class="text-end">{{ number_format($row->price_bill, 2) }}</td>
                        <td class="text-end">{{ number_format($row->price_ct, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4 text-muted">ไม่พบข้อมูลบริการตรวจ CT Scan ในช่วงเวลาที่เลือก</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($patients) > 0)
                <tfoot>
                    <tr class="fw-bold" style="background-color: #f8f9fa;">
                        <td colspan="8" class="text-end">รวมทั้งสิ้น</td>
                        <td class="text-center">{{ number_format($total_qty) }}</td>
                        <td class="text-end">{{ number_format($total_claim, 2) }}</td>
                        <td class="text-end">{{ number_format($total_bill, 2) }}</td>
                        <td class="text-end">{{ number_format($total_ct, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <!-- Signature Area -->
        @if(count($patients) > 0)
            <div class="row mt-5 pt-3">
                <div class="col-6 text-center">
                    <br>
                    <p>ลงชื่อ...........................................................ผู้รายงาน</p>
                    <p class="small text-muted">(...........................................................)</p>
                    <p class="small text-muted">ตำแหน่ง...........................................................</p>
                </div>
                <div class="col-6 text-center">
                    <br>
                    <p>ลงชื่อ...........................................................ผู้ตรวจสอบ</p>
                    <p class="small text-muted">(...........................................................)</p>
                    <p class="small text-muted">ตำแหน่ง...........................................................</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Auto print triggered on load -->
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(() => {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
