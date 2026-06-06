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
            margin: 3.0cm 0.7cm 2.5cm 0.7cm; /* top, right, bottom, left */
        }

        body {
            font-family: "thsarabunnew", sans-serif;
            font-size: 12px;
            line-height: 1.0;
            color: #000;
        }

        header {
            position: fixed;
            top: -2.6cm;
            left: 0cm;
            right: 0cm;
            height: 2.0cm;
            text-align: center;
        }

        .title {
            font-size: 14.5px;
            font-weight: bold;
            line-height: 1.0;
        }

        table.content-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.content-table, th, td {
            border: 1px solid #000;
        }

        th {
            font-weight: bold;
            text-align: center;
            background-color: #f2f2f2;
            padding: 2px 3px;
            font-size: 11.5px;
        }

        td {
            padding: 1.5px 3px;
            vertical-align: middle;
            font-size: 10px;
            word-wrap: break-word;
            overflow: hidden;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        footer {
            position: fixed;
            bottom: -2.1cm;
            left: 0cm;
            right: 0cm;
            height: 1.8cm;
            width: 100%;
        }

        .signature-table {
            border: none;
            width: 100%;
        }

        .signature-table td {
            border: none;
            text-align: center;
            width: 33.33%;
            padding: 1px;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <!-- Fixed header repeated on all pages -->
    <header>
        <div class="title">
            รายงานผลการตรวจสอบ{{ $category->DECLINE_NAME }}<br>
            ประจำปีงบประมาณ {{ $fiscalYearThai }}<br>
            โรงพยาบาลหัวตะพาน อำเภอหัวตะพาน จังหวัดอำนาจเจริญ
        </div>
    </header>

    <!-- Fixed footer repeated on all pages -->
    <footer>
        <table class="signature-table">
            <tr>
                <td>
                    ลงชื่อ......................................................ประธานกรรมการ<br>
                    (......................................................)
                </td>
                <td>
                    ลงชื่อ......................................................กรรมการ<br>
                    (......................................................)
                </td>
                <td>
                    ลงชื่อ......................................................กรรมการ<br>
                    (......................................................)
                </td>
            </tr>
        </table>
    </footer>

    <!-- Main Table -->
    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 4%;">ลำดับ</th>
                <th style="width: 26%;">ชื่อครุภัณฑ์</th>
                <th style="width: 13%;">รหัสครุภัณฑ์</th>
                <th style="width: 11%;">รหัสทรัพย์สิน</th>
                <th style="width: 8%;">วันที่ได้มา</th>
                <th style="width: 8%;">แหล่งเงิน</th>
                <th style="width: 9%;">วิธีได้มา</th>
                <th style="width: 8%;">ราคาทรัพย์สิน</th>
                <th style="width: 13%;">ประจำหน่วยงาน</th>
                <th style="width: 10%;">อายุการใช้งาน</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($assets as $index => $asset)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $asset->ARTICLE_NAME }}</td>
                    <td class="text-center">{{ $asset->ARTICLE_NUM }}</td>
                    <td class="text-center">{{ $asset->SUP_FSN }}</td>
                    <td class="text-center">{{ $asset->thai_receive_date }}</td>
                    <td class="text-center">{{ $asset->BUDGET_NAME ?: '-' }}</td>
                    <td class="text-center">{{ $asset->BUY_NAME ?: '-' }}</td>
                    <td class="text-right">{{ number_format($asset->PRICE_PER_UNIT, 2) }}</td>
                    <td>{{ $asset->HR_DEPARTMENT_SUB_SUB_NAME }}</td>
                    <td class="text-center">{{ $asset->age_string }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
