<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="table-non-doctor-list" style="width: 100%">
        <thead>
            <tr>
                <th style="width: 4%" class="text-center">ลำดับ</th>
                <th style="width: 8%" class="text-center">วันที่</th>
                <th style="width: 7%" class="text-center">เวลา</th>
                <th style="width: 8%" class="text-center">ช่วงเวลา</th>
                <th style="width: 4%" class="text-center">คิว</th>
                <th style="width: 7%" class="text-center">HN</th>
                <th style="width: 13%">ชื่อ-นามสกุล</th>
                <th style="width: 4%" class="text-center">อายุ</th>
                <th style="width: 9%">สิทธิการรักษา</th>
                <th style="width: 14%">อาการสำคัญ (CC)</th>
                <th style="width: 10%">วินิจฉัย (PDX)</th>
                <th style="width: 12%">ผู้ตรวจรักษา</th>
                <th style="width: 10%">แพทย์เวร ER</th>
                <th style="width: 8%" class="text-center">ระดับ ER</th>
                <th style="width: 7%" class="text-center">ผลการรักษา</th>
            </tr>
        </thead>
        <tbody>
            @forelse($patient_list as $index => $row)
                <tr>
                    <td class="text-center text-muted small">{{ $index + 1 }}</td>
                    <td class="text-center small">{{ DateThai($row->vstdate) }}</td>
                    <td class="text-center fw-semibold text-dark small">{{ substr($row->vsttime, 0, 5) }} น.</td>
                    <td class="text-center">
                        @if($row->shift_period === '19:00-24:00')
                            <span class="badge" style="background-color: #f8fafc; color: #475569; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.72rem;">
                                <i class="fas fa-moon text-warning me-1"></i> 19:00 - 24:00
                            </span>
                        @else
                            <span class="badge" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-weight: 600; font-size: 0.72rem;">
                                <i class="fas fa-cloud-moon text-primary me-1"></i> 00:00 - 08:00
                            </span>
                        @endif
                    </td>
                    <td class="text-center fw-bold text-primary">{{ $row->oqueue }}</td>
                    <td class="text-center font-monospace small fw-bold">{{ $row->hn }}</td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $row->ptname }}</div>
                    </td>
                    <td class="text-center small">{{ $row->age_y }} ปี</td>
                    <td class="small text-muted">{{ $row->pttype }}</td>
                    <td class="small" title="{{ $row->cc }}">
                        <span class="d-inline-block text-truncate" style="max-width: 180px;">
                            {{ $row->cc ?: '-' }}
                        </span>
                    </td>
                    <td class="small">
                        @if($row->pdx)
                            <span class="badge bg-secondary font-monospace">{{ $row->pdx }}</span>
                            @if($row->pdx_name)
                                <div class="text-muted smaller mt-1 text-truncate" style="max-width: 150px;" title="{{ $row->pdx_name }}">
                                    {{ $row->pdx_name }}
                                </div>
                            @endif
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="small">
                        <div class="fw-bold text-dark">{{ $row->dx_doctor }}</div>
                        <span class="badge bg-light text-primary border border-primary-subtle smaller mt-1">
                            <i class="fas fa-user-nurse me-1"></i> {{ $row->dx_position }}
                        </span>
                    </td>
                    <td class="small text-muted">
                        {{ $row->er_doctor }}
                    </td>
                    <td class="text-center">
                        @if($row->er_emergency_type === 'Resuscitate')
                            <span class="badge bg-danger shadow-sm">Resuscitate (1)</span>
                        @elseif($row->er_emergency_type === 'Emergency')
                            <span class="badge text-white shadow-sm" style="background-color: #f97316;">Emergency (2)</span>
                        @elseif($row->er_emergency_type === 'Urgency')
                            <span class="badge bg-warning text-dark shadow-sm">Urgency (3)</span>
                        @elseif($row->er_emergency_type === 'Semi_Urgency')
                            <span class="badge bg-info text-dark shadow-sm">Semi Urgency (4)</span>
                        @elseif($row->er_emergency_type === 'Non_Urgency')
                            <span class="badge bg-success shadow-sm">Non Urgency (5)</span>
                        @else
                            <span class="badge bg-secondary">{{ $row->er_emergency_type }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($row->admit)
                            <span class="badge" style="background-color: #7c3aed; color: white;">
                                <i class="fas fa-bed me-1"></i> Admit
                            </span>
                        @elseif($row->refer)
                            <span class="badge" style="background-color: #ea580c; color: white;" title="{{ $row->refer }}">
                                <i class="fas fa-share me-1"></i> Refer
                            </span>
                        @else
                            <span class="badge bg-light text-muted border">
                                <i class="fas fa-home me-1"></i> กลับบ้าน
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-2 text-secondary opacity-50 d-block"></i>
                        ไม่พบข้อมูลคนไข้ที่มารับบริการในช่วงเวลาและเงื่อนไขที่เลือก
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
