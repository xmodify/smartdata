<div class="table-responsive">
    <table id="readmitTable" class="table table-hover align-middle mb-0" style="width:100%">
        <thead>
            <tr class="bg-light">
                <th class="text-center" style="width: 50px;">ลำดับ</th>
                <th class="text-center">HN</th>
                <th>ชื่อ-สกุล</th>
                <th class="text-center">AN ใหม่</th>
                <th class="text-center">วันที่ Admit ใหม่</th>
                <th class="text-center">AN เก่า</th>
                <th class="text-center">วันที่ Admit เก่า</th>
                <th class="text-center">วันที่ จำหน่ายเก่า</th>
                <th class="text-center">โรคหลัก (ICD-10)</th>
                <th>ชื่อโรค</th>
                <th class="text-center text-danger fw-bold">ระยะห่าง (วัน)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($patients as $row)
                <tr>
                    <td class="text-center text-muted" style="font-size: 0.85rem;">{{ $loop->iteration }}</td>
                    <td class="text-center" style="font-size: 0.85rem;">{{ $row->hn }}</td>
                    <td class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $row->ptname }}</td>
                    <td class="text-center text-primary fw-bold" style="font-size: 0.85rem;">{{ $row->AN_new }}</td>
                    <td class="text-center" style="font-size: 0.85rem;">{{ DateThai($row->regdate_AN_New) }}</td>
                    <td class="text-center text-secondary" style="font-size: 0.85rem;">{{ $row->AN_old }}</td>
                    <td class="text-center" style="font-size: 0.85rem;">{{ DateThai($row->regdate_AN_Old) }}</td>
                    <td class="text-center" style="font-size: 0.85rem;">{{ DateThai($row->dcdate_AN_Old) }}</td>
                    <td class="text-center fw-bold text-danger" style="font-size: 0.85rem;">{{ $row->icd10_1 }}</td>
                    <td style="font-size: 0.85rem;">{{ $row->icd_name }}</td>
                    <td class="text-center text-danger fw-bold" style="font-size: 0.88rem;">{{ $row->ReAdmitDate }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
