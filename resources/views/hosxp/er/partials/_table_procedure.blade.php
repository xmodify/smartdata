<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="table-procedure-list" style="width: 100%">
        <thead>
            <tr>
                <th style="width: 5%">ลำดับ</th>
                <th>วันที่รับบริการ</th>
                <th>เวลา</th>
                <th style="width: 5%">คิว</th>
                <th>HN</th>
                <th>ชื่อ-นามสกุล</th>
                <th style="width: 5%">อายุ</th>
                <th>สิทธิการรักษา</th>
                <th>อาการสำคัญ (CC)</th>
                <th>วินิจฉัยหลัก (PDX)</th>
                <th>แพทย์ผู้ตรวจ</th>
                <th>หัตถการสำคัญ</th>
                <th>ผลการรักษา / การส่งต่อ</th>
                <th>ระดับความรุนแรง ER</th>
            </tr>
        </thead>
        <tbody>
            @foreach($procedure_list as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ DateThai($row->vstdate) }}</td>
                    <td class="text-center">{{ substr($row->vsttime, 0, 5) }} น.</td>
                    <td class="text-center fw-bold text-primary">{{ $row->oqueue }}</td>
                    <td class="text-center">{{ $row->hn }}</td>
                    <td>{{ $row->ptname }}</td>
                    <td class="text-center">{{ $row->age_y }} ปี</td>
                    <td class="small">{{ $row->pttype }}</td>
                    <td class="small" title="{{ $row->cc }}">{{ Str::limit($row->cc, 40) }}</td>
                    <td class="text-center fw-bold">{{ $row->pdx }}</td>
                    <td class="small">{{ $row->dx_doctor }}</td>
                    <td class="text-center">
                        <div class="d-flex flex-column gap-1 align-items-center">
                            @if($row->has_intubation)
                                <span class="badge" style="background-color: #0284c7; font-size: 0.75rem;">
                                    <i class="fas fa-lungs me-1"></i> ใส่ท่อช่วยหายใจ (96.04)
                                </span>
                            @endif
                            @if($row->has_cpr)
                                <span class="badge bg-danger" style="font-size: 0.75rem;">
                                    <i class="fas fa-heartbeat me-1"></i> ฟื้นคืนชีพ CPR (99.60)
                                </span>
                            @endif
                            @if(!$row->has_intubation && !$row->has_cpr && $row->procedure_summary)
                                <span class="badge bg-secondary" style="font-size: 0.75rem;">{{ $row->procedure_summary }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">
                        @if($row->admit)
                            <span class="badge bg-purple"><i class="fas fa-bed me-1"></i> {{ $row->admit }}</span>
                        @endif
                        @if($row->refer)
                            <span class="badge bg-orange"><i class="fas fa-share me-1"></i> Refer: {{ $row->refer }}</span>
                        @endif
                        @if(!$row->admit && !$row->refer)
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row->er_emergency_type === 'Resuscitate')
                            <span class="badge bg-danger">Resuscitate</span>
                        @elseif($row->er_emergency_type === 'Emergency')
                            <span class="badge bg-orange text-white">Emergency</span>
                        @elseif($row->er_emergency_type === 'Urgency')
                            <span class="badge bg-warning text-dark">Urgency</span>
                        @elseif($row->er_emergency_type === 'Semi_Urgency')
                            <span class="badge bg-primary">Semi_Urgency</span>
                        @elseif($row->er_emergency_type === 'Non_Urgency')
                            <span class="badge bg-success">Non_Urgency</span>
                        @else
                            <span class="badge bg-secondary">{{ $row->er_emergency_type ?: '-' }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
