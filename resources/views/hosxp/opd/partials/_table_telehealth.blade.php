<div class="table-responsive">
    <table id="telehealthTable" class="table table-hover align-middle mb-0" style="width:100%">
        <thead class="bg-light">
            <tr>
                <th class="text-center" style="width: 50px;">ลำดับ</th>
                <th class="text-center">วันที่บริการ</th>
                <th class="text-center">VN</th>
                <th class="text-center">คิว</th>
                <th class="text-center">HN</th>
                <th>ชื่อ-สกุล</th>
                <th class="text-center">อายุ (ปี)</th>
                <th>สิทธิการรักษา</th>
                <th class="text-center">โรคหลัก</th>
                <th>ห้องตรวจที่รักษา</th>
                <th class="text-center">แพทย์ผู้ตรวจ</th>
                <th class="text-center">สถานะ</th>
                <th>เลขที่สิทธิ / เลขอนุมัติ</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($patients as $row)
                <tr>
                    <td class="text-center text-muted" style="font-size: 0.85rem;">{{ $loop->iteration }}</td>
                    <td class="text-center" style="font-size: 0.85rem;">{{ DateThai($row->vstdate) }}</td>
                    <td class="text-center text-primary fw-bold" style="font-size: 0.85rem;">{{ $row->vn }}</td>
                    <td class="text-center" style="font-size: 0.85rem;">
                        <span class="badge bg-secondary rounded-pill px-2 py-1">{{ $row->oqueue }}</span>
                    </td>
                    <td class="text-center" style="font-size: 0.85rem;">{{ $row->hn }}</td>
                    <td class="fw-bold text-dark" style="font-size: 0.85rem;">{{ $row->ptname }}</td>
                    <td class="text-center" style="font-size: 0.85rem;">{{ $row->age_y }}</td>
                    <td style="font-size: 0.85rem;">{{ $row->pttype }}</td>
                    <td class="text-center fw-bold text-danger" style="font-size: 0.85rem;">{{ $row->pdx }}</td>
                    <td style="font-size: 0.85rem;">{{ $row->department }}</td>
                    <td class="text-center" style="font-size: 0.85rem;">{{ $row->dx_doctor }}</td>
                    <td class="text-center" style="font-size: 0.82rem;" data-status="{{ ($row->ovstist == '12' && $row->has_telmed_charge) ? 'complete' : (($row->ovstist == '12') ? 'type_only' : 'charge_only') }}">
                        @if ($row->ovstist == '12' && $row->has_telmed_charge)
                            <span class="badge bg-success">ครบถ้วน</span>
                        @elseif ($row->ovstist == '12')
                            <span class="badge bg-primary">ประเภท: Tele</span>
                        @else
                            <span class="badge bg-warning text-dark">เฉพาะรหัสเบิก</span>
                        @endif
                    </td>
                    <td style="font-family: monospace; font-size: 0.82rem;">{{ $row->auth_code ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center text-muted py-4">ไม่พบข้อมูลตามช่วงเวลาที่ระบุ</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
