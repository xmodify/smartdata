@if(!empty($is_limited) && $is_limited)
    <div class="alert alert-primary bg-primary bg-opacity-10 border-primary border-opacity-25 py-2 px-3 mb-3 small d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-radius: 10px;">
        <div class="d-flex align-items-center gap-2 text-primary fw-medium">
            <i class="fas fa-bolt"></i>
            <span>แสดงผล <strong>{{ number_format(count($patient_prescriptions)) }}</strong> รายการล่าสุด จากทั้งหมด <strong>{{ number_format($total_count) }}</strong> รายการ เพื่อความรวดเร็วและประสิทธิภาพของระบบ</span>
        </div>
        <span class="badge bg-primary text-white px-2 py-1 shadow-sm">
            <i class="fas fa-file-excel me-1"></i> ดาวน์โหลดข้อมูลทั้งหมดครบถ้วนได้ที่ปุ่ม "ส่งออก Excel (ทั้งหมด)"
        </span>
    </div>
@endif

<div class="table-responsive">
    <table class="table table-hover align-middle w-100" id="patientPrescriptionTable" style="font-size: 0.82rem;">
        <thead class="table-light">
            <tr>
                <th class="text-center" style="width: 45px;">ลำดับ</th>
                <th class="text-center">วันที่</th>
                <th class="text-center">เวลา</th>
                <th class="text-center">HN</th>
                <th class="text-center">CID</th>
                <th>ชื่อ-นามสกุล</th>
                <th class="text-center">จุดบริการ</th>
                <th>ตัวยา</th>
                <th>วิธีใช้ยา</th>
                <th class="text-end">จำนวน</th>
                <th class="text-center">หน่วย</th>
                <th class="text-end">มูลค่า (บาท)</th>
                <th>แพทย์ผู้สั่ง</th>
                <th>สิทธิการรักษา</th>
                <th>รพ.สต. / พื้นที่</th>
            </tr>
        </thead>
        <tbody>
            @foreach($patient_prescriptions as $index => $pt)
                <tr data-service-point="{{ $pt->service_point_code }}">
                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                    <td class="text-center text-nowrap" data-order="{{ $pt->rxdate }}">
                        {{ DateThai($pt->rxdate) }}
                    </td>
                    <td class="text-center font-monospace text-nowrap">
                        {{ $pt->rxtime ?: '-' }}
                    </td>
                    <td class="text-center font-monospace fw-bold text-primary text-nowrap">
                        {{ $pt->hn }}
                    </td>
                    <td class="text-center font-monospace text-nowrap">
                        {{ $pt->cid ?: '-' }}
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $pt->ptname }}</div>
                        @if($pt->age_y)
                            <small class="text-muted">อายุ {{ $pt->age_y }} ปี</small>
                        @endif
                    </td>
                    <td class="text-center" data-filter="{{ $pt->service_point_code }}">
                        @if($pt->service_point_code == 'ER')
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1 rounded-pill">
                                ER (ฉุกเฉิน)
                            </span>
                        @elseif($pt->service_point_code == 'ICU')
                            <span class="badge bg-opacity-10 border px-2 py-1 rounded-pill" style="background-color: #f3e8ff; color: #7c3aed; border-color: #7c3aed !important;">
                                ICU ({{ $pt->service_point_name }})
                            </span>
                        @elseif($pt->service_point_code == 'VIP')
                            <span class="badge bg-opacity-10 border px-2 py-1 rounded-pill" style="background-color: #fef3c7; color: #b45309; border-color: #b45309 !important;">
                                VIP ({{ $pt->service_point_name }})
                            </span>
                        @elseif($pt->service_point_code == 'IPD')
                            <span class="badge bg-info bg-opacity-10 text-info border border-info px-2 py-1 rounded-pill">
                                IPD ({{ $pt->service_point_name }})
                            </span>
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill">
                                OPD ({{ $pt->service_point_name }})
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $pt->drug_name }}</div>
                        <small class="text-muted">รหัส: <code>{{ $pt->icode }}</code></small>
                    </td>
                    <td>
                        <div class="p-1 px-2 rounded bg-light border text-secondary small" style="max-width: 250px; line-height: 1.3;">
                            {{ $pt->drugusage_text }}
                        </div>
                    </td>
                    <td class="text-end fw-bold text-primary text-nowrap">
                        {{ number_format($pt->qty) }}
                    </td>
                    <td class="text-center text-nowrap">
                        <span class="badge bg-light text-secondary border px-2 py-1">{{ $pt->units ?: '-' }}</span>
                    </td>
                    <td class="text-end fw-bold text-success text-nowrap">
                        {{ number_format($pt->sum_price, 2) }}
                    </td>
                    <td class="text-nowrap">
                        <div class="small text-dark"><i class="fas fa-user-md text-muted me-1"></i>{{ $pt->doctor_name }}</div>
                    </td>
                    <td class="text-nowrap">
                        <span class="badge bg-light text-dark border">{{ $pt->pttype_name }}</span>
                    </td>
                    <td class="text-nowrap">
                        <span class="small text-muted"><i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $pt->pcu }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light fw-bold border-top border-2">
            <tr class="align-middle">
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="text-end text-dark">รวมทั้งหมด:</th>
                <th class="text-end text-primary fw-bold" id="footer-total-qty">0</th>
                <th class="text-center text-muted">-</th>
                <th class="text-end text-success fw-bold" id="footer-total-price">0.00</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
