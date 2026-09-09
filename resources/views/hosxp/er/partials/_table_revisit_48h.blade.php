<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="table-revisit-list" style="width: 100%">
        <thead>
            <tr>
                <th style="width: 5%">ลำดับ</th>
                <th>วันที่รับบริการ</th>
                <th>เว้นระยะจากครั้งก่อน</th>
                <th style="width: 5%">คิว</th>
                <th>HN</th>
                <th>ชื่อ-นามสกุล</th>
                <th style="width: 5%">อายุ</th>
                <th>สิทธิการรักษา</th>
                <th>อาการสำคัญ (CC)</th>
                <th>วินิจฉัยหลัก (PDX)</th>
                <th>แผนกที่เข้ารับบริการ</th>
                <th>สถานะหลังตรวจ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($revisit_list as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ DateThai($row->vstdate) }}</td>
                    <td class="text-center fw-bold text-danger">{{ $row->p_vstdate }}</td>
                    <td class="text-center fw-bold text-primary">{{ $row->q }}</td>
                    <td class="text-center">{{ $row->hn }}</td>
                    <td>{{ $row->ptname }}</td>
                    <td class="text-center">{{ $row->age_y }} ปี</td>
                    <td class="small">{{ $row->pttype }}</td>
                    <td class="small" title="{{ $row->cc }}">{{ Str::limit($row->cc, 40) }}</td>
                    <td class="text-center fw-bold">{{ $row->pdx }}</td>
                    <td class="text-center">
                        @if($row->depart === 'ER')
                            <span class="badge bg-danger">ER</span>
                        @else
                            <span class="badge bg-primary">OPD</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row->admit)
                            <span class="badge bg-purple"><i class="fas fa-bed me-1"></i> {{ $row->admit }}</span>
                        @endif
                        @if($row->refer)
                            <span class="badge bg-orange"><i class="fas fa-share me-1"></i> {{ $row->refer }}</span>
                        @endif
                        @if(!$row->admit && !$row->refer)
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
