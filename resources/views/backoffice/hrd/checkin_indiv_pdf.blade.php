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
            src: url('fonts/thsarabunnew_bolditalic-webfont.eot');
            src: url('fonts/thsarabunnew_bolditalic-webfont.eot?#iefix') format('embedded-opentype'),
                url('fonts/thsarabunnew_bolditalic-webfont.woff') format('woff'),
                url('fonts/thsarabunnew_bolditalic-webfont.ttf') format('truetype');
            font-weight: bold;
            font-style: italic;
        }

        @font-face {
            font-family: 'THSarabunNew';
            src: url('fonts/thsarabunnew_italic-webfont.eot');
            src: url('fonts/thsarabunnew_italic-webfont.eot?#iefix') format('embedded-opentype'),
                url('fonts/thsarabunnew_italic-webfont.woff') format('woff'),
                url('fonts/thsarabunnew_italic-webfont.ttf') format('truetype');
            font-weight: normal;
            font-style: italic;
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
            margin: 0cm 0cm;
        }

        header {
            position: fixed;
            font-family: "THSarabunNew";
            top: 1cm;
            left: 2cm;
            right: 1cm;
            font-size: 13px;
            line-height: 0.75;
            text-align: center;
        }

        footer {
            position: fixed;
            font-family: "THSarabunNew";
            bottom: 1.0cm;
            left: 2cm;
            right: 1cm;
            font-size: 12px;
            line-height: 0.75;
        }

        body {
            /* font-family: 'THSarabunNew', sans-serif;
                    font-size: 13px;
                line-height: 0.9;  
                margin-top:    0.2cm;
                margin-bottom: 0.2cm;
                margin-left:   1cm;
                margin-right:  1cm;  */
            font-family: "THSarabunNew";
            font-size: 12px;
            line-height: 0.75;
            margin-top: 4cm;
            margin-bottom: 4cm;
            margin-left: 2cm;
            margin-right: 1cm;
        }

        #watermark {
            position: fixed;
            bottom: 0px;
            left: 0px;
            width: 29.5cm;
            height: 21cm;
            z-index: -1000;
        }

        table,
        td {
            border: 1px solid rgb(5, 5, 5);
        }

        .text-pedding {
            /* padding-left:10px;
                padding-right:10px; */
        }

        table {
            border-collapse: collapse; //กรอบด้านในหายไป
        }

        table.one {
            border: 1px solid rgb(5, 5, 5);
            /* height: 800px; */
            /* padding: 15px; */
        }

        td {
            margin: .2rem;
            /* height: 3px; */
            /* padding: 5px; */
            /* text-align: left; */
        }

        td.o {
            border: 1px solid rgb(5, 5, 5);
            font-family: "THSarabunNew";
            font-size: 12px;
        }

        td.b {
            border: 1px solid rgb(5, 5, 5);
        }

        td.d {
            border: 1px solid rgb(5, 5, 5);
            height: 170px;
        }

        td.e {
            border: 1px solid rgb(5, 5, 5);

        }

        td.h {
            border: 1px solid rgb(5, 5, 5);
            height: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        input {
            margin: .3rem;
        }

        .tsm {
            font-family: "THSarabunNew";
            font-size: 11px;
        }

        .tss {
            font-family: "THSarabunNew";
            font-size: 10px;
        }
    </style>
</head>

<body>
    <header>
        <div>
            <strong>
                <p align=center>
                    ข้อมูลสรุปการลงเวลาปฏิบัติงาน {{$type_name}}<br>
                    {{$ptname}} เบอร์โทรศัพท์ {{$phone}}<br>
                    ตำแหน่ง {{$position_name}} หน่วยงาน {{$depart}}<br>
                    วันที่ {{dateThaifromFull($start_date)}} ถึง {{dateThaifromFull($end_date)}} <br>
                </p>
            </strong>
        </div>
    </header>

    <footer>
        ขอรับรองว่าข้อมูลผู้มีรายชื่อข้างต้นปฏิบัติงานตามเวลาจริง&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        ลงชื่อ...................................................หัวหน้าหน่วยงาน<br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        (.....................................................................................)<br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        ตำแหน่ง..........................................................................
    </footer>

    <main>
        <div class="container">
            <div class="row justify-content-center">
                <table width="100%">
                    <thead>
                        <tr>
                            <td align="center" width="12%"><strong>วันที่ลงเวลา</strong></td>
                            <td align="center" width="6%"><strong>ชื่อเวร</strong></td>
                            <td align="center" width="30%"><strong>ชื่อ-สกุล</strong></td>
                            <td align="center" width="9%"><strong>บันทึกเข้า</strong></td>
                            <td align="center" width="9%"><strong>ลายมือชื่อ</strong></td>
                            <td align="center" width="9%"><strong>บันทึกออก</strong></td>
                            <td align="center" width="9%"><strong>ลายมือชื่อ</strong></td>
                            <td align="center" width="16%"><strong>หมายเหตุ</strong></td>
                        </tr>
                    </thead>
                    <?php $count = 1; ?>
                    @foreach($checkin_indiv as $row)
                        <tr>
                            <td align="center">{{DateThai($row->SHIFT_DATE)}}</td>
                            <td align="center">{{$row->shift}}</td>
                            <td align="left">{{$row->ptname}}</td>
                            <td align="center">{{$row->scan_start}}</td>
                            <td align="left"></td>
                            <td align="center">{{$row->scan_end}}</td>
                            <td align="left"></td>
                            <td align="left"></td>
                        </tr>
                        <?php    $count++; ?>
                    @endforeach
                </table>
            </div>
        </div>
    </main>

</body>

</html>