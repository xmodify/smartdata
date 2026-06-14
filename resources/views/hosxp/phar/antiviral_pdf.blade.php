<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            src: url('fonts/thsarabunnew-webfont.eot');
            src: url('fonts/thsarabunnew-webfont.eot?#iefix') format('embedded-opentype'),
                url('fonts/thsarabunnew-webfont.woff') format('woff'),
                url('fonts/thsarabunnew-webfont.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @font-face {
            font-family: 'THSarabunNew';
            src: url('fonts/thsarabunnew_bold-webfont.eot');
            src: url('fonts/thsarabunnew_bold-webfont.eot?#iefix') format('embedded-opentype'),
                url('fonts/thsarabunnew_bold-webfont.woff') format('woff'),
                url('fonts/thsarabunnew_bold-webfont.ttf') format('truetype');
            font-weight: bold;
            font-style: normal;
        }
        @page {
            margin: 1.0cm 1.2cm;
        }
        body {
            font-family: "THSarabunNew";
            font-size: 14px;
            line-height: 0.85;
            color: #000;
        }
        .header-section {
            text-align: center;
            margin-bottom: 12px;
        }
        .header-section h3 {
            font-size: 17px;
            margin: 0 0 3px 0;
            font-weight: bold;
        }
        .header-section p {
            font-size: 14px;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            font-weight: bold;
            text-align: center;
            padding: 2px;
            background-color: #f2f2f2;
            font-size: 11px;
            line-height: 0.8;
        }
        td {
            padding: 1px 3px;
            font-size: 10px;
            vertical-align: middle;
            line-height: 0.8;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .summary-box {
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
            padding: 2px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <h3>{{ $title }}</h3>
        <p style="font-weight: bold;">{{ $subtitle }}</p>
        <p>วันที่ {{ dateThaifromFull($start_date) }} ถึง {{ dateThaifromFull($end_date) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ลำดับ</th>
                <th style="width: 15%;">วันที่ได้รับ</th>
                <th style="width: 10%;">HN</th>
                @if($type === 'ipd')
                    <th style="width: 10%;">AN</th>
                @endif
                <th style="width: 15%;">CID</th>
                <th style="width: 20%;">ชื่อ-สกุล</th>
                <th style="width: 8%;">อายุ</th>
                <th>รายการยา</th>
                <th style="width: 10%;">จำนวน</th>
            </tr>
        </thead>
        <tbody>
            @php $idx = 1; $total_qty = 0; @endphp
            @forelse($data as $row)
                <tr>
                    <td class="text-center">{{ $idx++ }}</td>
                    <td class="text-center">{{ DateThai($row->rxdate) }}</td>
                    <td class="text-center">{{ $row->hn }}</td>
                    @if($type === 'ipd')
                        <td class="text-center">{{ $row->an }}</td>
                    @endif
                    <td class="text-center">{{ $row->cid }}</td>
                    <td>{{ $row->ptname }}</td>
                    <td class="text-center">{{ $row->age_y }}</td>
                    <td>{{ $row->drug }}</td>
                    <td class="text-center">{{ number_format($row->qty) }}</td>
                </tr>
                @php $total_qty += $row->qty; @endphp
            @empty
                <tr>
                    <td colspan="{{ $type === 'ipd' ? 9 : 8 }}" class="text-center">ไม่พบข้อมูล</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($summary) > 0)
        <div class="summary-box">
            @php
                $summary_parts = [];
                foreach($summary as $drug_name => $qty) {
                    $summary_parts[] = "ยา " . $drug_name . " ทั้งหมดจำนวน " . number_format($qty) . " เม็ด";
                }
                $summary_str = implode(' และ ', $summary_parts);
            @endphp
            รวม{{ $summary_str }}
        </div>
    @endif
</body>
</html>
