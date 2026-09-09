<div class="table-responsive">
    <table id="reportTable" class="table table-hover align-middle mb-0" style="width:100%">
        <thead class="bg-light text-primary">
            <tr>
                <th class="text-center" style="width: 50px;">ลำดับ</th>
                <th>AN/HN</th>
                <th>ชื่อ-นามสกุล</th>
                <th>Admit PDX</th>
                <th>Refer PDX</th>
                <th>รพ.ที่ส่งต่อ</th>
                <th>เวลา Admit</th>
                <th>เวลา Refer</th>
                <th class="text-center">ชม.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report_data as $row)
                <tr>
                    <td class="text-center text-muted" style="font-size: 0.85rem;">{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $row->an }}</div>
                        <div class="small text-muted">HN: {{ $row->hn }}</div>
                    </td>
                    <td>
                        <div class="fw-medium">{{ $row->ptname }}</div>
                    </td>
                    <td><span class="badge bg-info bg-opacity-10 text-info">{{ $row->admit_pdx }}</span></td>
                    <td><span class="badge bg-warning bg-opacity-10 text-warning text-dark">{{ $row->refer_pdx }}</span></td>
                    <td>{{ $row->refer_hos }}</td>
                    <td>
                        <div class="small">{{ DateThai($row->regdate) }}</div>
                        <div class="fw-bold">{{ $row->regtime }}</div>
                    </td>
                    <td>
                        <div class="small">{{ DateThai($row->refer_date) }}</div>
                        <div class="fw-bold">{{ $row->refer_time }}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-danger">{{ $row->admit_hour }} ชม.</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
