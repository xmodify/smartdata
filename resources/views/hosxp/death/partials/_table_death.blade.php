<div class="table-responsive">
    <table id="reportTable_{{ $tab }}" class="death-table table table-hover align-middle mb-0" style="width:100%">
        <thead class="bg-light text-danger">
            <tr>
                <th class="text-center" style="width: 50px;">ลำดับ</th>
                <th>AN/HN</th>
                <th>ชื่อ-นามสกุล</th>
                <th class="text-center">อายุ (ปี)</th>
                <th>วันที่เสียชีวิต</th>
                <th>เวลา</th>
                <th class="text-center">ประเภท</th>
                <th>สาเหตุการเสียชีวิต (504)</th>
                <th>รหัสโรค (ICD10)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($death_list as $row)
                @php
                    $isOut = ($row->dept ?? '') === 'OUT'; // เฉพาะ AOPOD (ไม่พบใน HOSxP)
                    $age = '-';
                    if (isset($row->age) && $row->age !== null && $row->age !== '') {
                        $age = $row->age;
                    } else {
                        try {
                            $age = \Carbon\Carbon::parse($row->birthday)->age;
                        } catch (\Exception $e) {
                            $age = '-';
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center text-muted" style="font-size: 0.85rem;">{{ $loop->iteration }}</td>
                    <td>
                        @if ($isOut)
                            <div class="fw-bold text-dark font-monospace" style="font-size: 0.85rem;">{{ $row->cid ?: '-' }}</div>
                            <div class="small text-muted">{{ $row->hn ? 'HN: ' . $row->hn : 'ไม่พบใน HOSxP' }}</div>
                        @else
                            <div class="fw-bold text-dark">{{ $row->an ?: '-' }}</div>
                            <div class="small text-muted">HN: {{ $row->hn }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-medium">{{ $row->ptname ?: '-' }}</div>
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3">{{ $age }}</span>
                    </td>
                    <td data-order="{{ $row->death_date }}">
                        <div class="small text-dark">{{ DateThai($row->death_date) }}</div>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $row->death_time ?: '-' }}</div>
                    </td>
                    <td class="text-center">
                        @if ($isOut)
                            <span class="badge bg-secondary">นอกสถานพยาบาล (เฉพาะ AOPOD)</span>
                        @elseif ($row->dept === 'OUTH')
                            <span class="badge bg-secondary bg-opacity-75">นอกสถานพยาบาล (HOSxP)</span>
                        @elseif ($row->dept === 'IPD')
                            <span class="badge bg-danger">ผู้ป่วยใน</span>
                        @else
                            <span class="badge bg-info">ในสถานพยาบาล (ไม่มี AN)</span>
                        @endif
                    </td>
                    <td><span class="badge bg-warning bg-opacity-10 text-warning text-dark"
                            style="white-space: normal; text-align: left;">{{ $row->name504 }}</span></td>
                    <td><span class="badge bg-danger bg-opacity-10 text-danger text-dark"
                            style="white-space: normal; text-align: left;">{{ $row->icdname }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
