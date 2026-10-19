<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="table-ems-list" style="width: 100%">
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
                <th>ระดับ EMS</th>
                <th>ผลการรักษา / การส่งต่อ</th>
                <th>ระดับความรุนแรง ER</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ems_list as $index => $row)
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
                        @if($row->ems === 'ALS')
                            <span class="badge bg-danger">ALS</span>
                        @elseif($row->ems === 'ILS')
                            <span class="badge bg-primary">ILS</span>
                        @elseif($row->ems === 'FR')
                            <span class="badge bg-success">FR</span>
                        @elseif($row->ems === 'BLS')
                            <span class="badge bg-info text-dark">BLS</span>
                        @else
                            <span class="badge bg-secondary">{{ $row->ems }}</span>
                        @endif
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
                        @if($row->emergency_type_id == 1 || $row->er_emergency_type === 'Resuscitate')
                            <span class="badge" style="background-color: #be123c; color: white;">
                                <i class="fas fa-heartbeat me-1"></i> Resuscitate (กู้ชีพทันที)
                            </span>
                        @elseif($row->emergency_type_id == 2 || $row->er_emergency_type === 'Emergency')
                            <span class="badge text-white" style="background-color: #ea580c;">
                                <i class="fas fa-exclamation-triangle me-1"></i> Emergency (ฉุกเฉินเร่งด่วน)
                            </span>
                        @elseif($row->emergency_type_id == 3 || $row->er_emergency_type === 'Urgency')
                            <span class="badge text-dark" style="background-color: #facc15;">
                                <i class="fas fa-bell me-1"></i> Urgency (ด่วนมาก)
                            </span>
                        @elseif($row->emergency_type_id == 4 || $row->er_emergency_type === 'Semi_Urgency')
                            <span class="badge text-white" style="background-color: #2563eb;">
                                <i class="fas fa-check-circle me-1"></i> Semi Urgency (ด่วน)
                            </span>
                        @elseif($row->emergency_type_id == 5 || $row->er_emergency_type === 'Non_Urgency')
                            <span class="badge text-white" style="background-color: #16a34a;">
                                <i class="fas fa-smile me-1"></i> Non Urgency (รอได้)
                            </span>
                        @else
                            <span class="badge bg-secondary text-white">ไม่ระบุระดับ</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
