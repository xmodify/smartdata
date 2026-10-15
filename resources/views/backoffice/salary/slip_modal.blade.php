@php
    $salReceivesList = is_array($salReceives) ? $salReceives : (is_object($salReceives) && method_exists($salReceives, 'toArray') ? $salReceives->toArray() : (array)$salReceives);
    $salPaysList = is_array($salPays) ? $salPays : (is_object($salPays) && method_exists($salPays, 'toArray') ? $salPays->toArray() : (array)$salPays);
    $salRowCount = max(count($salReceivesList), count($salPaysList), 1);

    $compReceivesList = is_array($compReceives) ? $compReceives : (is_object($compReceives) && method_exists($compReceives, 'toArray') ? $compReceives->toArray() : (array)$compReceives);
    $compPaysList = is_array($compPays) ? $compPays : (is_object($compPays) && method_exists($compPays, 'toArray') ? $compPays->toArray() : (array)$compPays);
    $compRowCount = max(count($compReceivesList), count($compPaysList), 1);
@endphp

<div class="modal-header bg-dark text-white py-3 px-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
    <div class="d-flex align-items-center">
        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 44px; height: 44px;">
            <i class="fas fa-file-invoice-dollar fs-5"></i>
        </div>
        <div>
            <h5 class="modal-title fw-bold text-white mb-0">
                ใบแจ้งการจ่ายเงินเดือนและเงินค่าตอบแทนรายบุคคล
            </h5>
            <div class="text-white text-opacity-80 small mt-1">
                ประจำเดือน{{ $monthName }} พ.ศ. {{ $calYear }} (ปีงบประมาณ {{ $yearId }})
            </div>
        </div>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4 bg-light">
    <!-- Printable Slip Document (Designed to fit 1 Single A4 Sheet Perfectly) -->
    <div id="printableSlipArea" class="bg-white p-4 rounded-3 shadow-sm mx-auto" style="max-width: 800px; color: #0f172a; font-family: 'Sarabun', 'TH Sarabun New', sans-serif;">
        
        <!-- Official Government Header -->
        <div class="text-center mb-3 pb-2 border-bottom">
            <h5 class="fw-bold mb-1 text-dark" style="font-size: 1.15rem; letter-spacing: 0.5px;">
                โรงพยาบาลหัวตะพาน อำเภอหัวตะพาน จังหวัดอำนาจเจริญ
            </h5>
            <div class="fw-bold text-dark" style="font-size: 1.05rem;">
                ใบแจ้งการจ่ายเงินเดือน ค่าจ้าง และเงินค่าตอบแทนรายบุคคล
            </div>
            <div class="text-muted small mt-1" style="font-size: 0.88rem;">
                ประจำเดือน <strong>{{ $monthName }}</strong> พ.ศ. <strong>{{ $calYear }}</strong> (งวดปีงบประมาณ {{ $yearId }})
            </div>
        </div>

        <!-- Personnel Information Grid -->
        <table class="table table-sm table-bordered mb-3" style="font-size: 0.82rem; border-color: #cbd5e1;">
            <tbody>
                <tr>
                    <td class="bg-light fw-bold text-nowrap" style="width: 17%;">ชื่อ - สกุล ผู้รับเงิน</td>
                    <td class="fw-bold text-dark" style="width: 33%;">
                        {{ $person->HR_PREFIX_NAME }}{{ $person->HR_FNAME }} {{ $person->HR_LNAME }}
                    </td>
                    <td class="bg-light fw-bold text-nowrap" style="width: 17%;">ตำแหน่งสายงาน</td>
                    <td class="text-dark" style="width: 33%;">
                        {{ $person->POSITION_IN_WORK ?: '-' }}
                    </td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold text-nowrap">เลขประจำตัวประชาชน</td>
                    <td class="font-monospace text-dark">{{ $person->HR_CID ?: '-' }}</td>
                    <td class="bg-light fw-bold text-nowrap">กลุ่มงาน / หน่วยงาน</td>
                    <td class="text-dark">{{ $person->HR_DEPARTMENT_SUB_SUB_NAME ?: $person->HR_DEPARTMENT_NAME }}</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold text-nowrap">ประเภทบุคลากร</td>
                    <td class="text-dark">{{ $person->HR_PERSON_TYPE_NAME ?: 'พนักงาน' }}</td>
                    <td class="bg-light fw-bold text-nowrap">เลขที่บัญชีรับโอน</td>
                    <td class="font-monospace text-primary fw-bold">{{ $bookNum ?: 'ไม่ระบุ' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Section 1: หมวดเงินเดือนและค่าจ้างประจำ -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center px-2 py-1 bg-light border border-bottom-0 fw-bold" style="font-size: 0.85rem; border-color: #cbd5e1 !important;">
                <span class="text-dark"><i class="fas fa-money-check-dollar text-success me-1"></i> 1. หมวดเงินเดือนและค่าจ้างประจำ</span>
                <span class="text-success font-monospace">เงินเดือนสุทธิ: {{ number_format($salNet, 2) }} บาท</span>
            </div>
            <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem; border-color: #cbd5e1;">
                <thead>
                    <tr class="bg-light text-muted" style="border-color: #cbd5e1;">
                        <th style="width: 32%;">รายการเงินได้ (รับ)</th>
                        <th class="text-end" style="width: 18%;">จำนวนเงิน (บาท)</th>
                        <th style="width: 32%;">รายการเงินหัก (จ่าย)</th>
                        <th class="text-end" style="width: 18%;">จำนวนเงิน (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!$salary && count($salReceivesList) === 0 && count($salPaysList) === 0)
                        <tr>
                            <td class="text-muted text-center py-1" colspan="2">ไม่มีรายการเงินเดือนในงวดนี้</td>
                            <td class="text-muted text-center py-1" colspan="2">ไม่มีรายการหัก</td>
                        </tr>
                    @else
                        @for($i = 0; $i < $salRowCount; $i++)
                            @php
                                $rec = $salReceivesList[$i] ?? null;
                                $pay = $salPaysList[$i] ?? null;
                                $recObj = is_object($rec) ? $rec : (object)$rec;
                                $payObj = is_object($pay) ? $pay : (object)$pay;
                            @endphp
                            <tr>
                                <td class="py-1">{{ $rec ? ($recObj->item_name ?? '-') : '' }}</td>
                                <td class="text-end font-monospace text-success py-1">
                                    {{ $rec ? number_format($recObj->amount, 2) : '' }}
                                </td>
                                <td class="py-1">{{ $pay ? ($payObj->item_name ?? '-') : '' }}</td>
                                <td class="text-end font-monospace text-danger py-1">
                                    {{ $pay ? ('-' . number_format($payObj->amount, 2)) : '' }}
                                </td>
                            </tr>
                        @endfor
                    @endif
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold" style="font-size: 0.8rem; border-color: #cbd5e1;">
                        <td>รวมเงินได้ (เงินเดือน)</td>
                        <td class="text-end text-success font-monospace">{{ number_format($salTotalRec, 2) }}</td>
                        <td>รวมเงินหัก (เงินเดือน)</td>
                        <td class="text-end text-danger font-monospace">
                            @if($salTotalPay > 0)
                                -{{ number_format($salTotalPay, 2) }}
                            @else
                                0.00
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Section 2: หมวดค่าตอบแทนและเงินเพิ่มพิเศษ -->
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center px-2 py-1 bg-light border border-bottom-0 fw-bold" style="font-size: 0.85rem; border-color: #cbd5e1 !important;">
                <span class="text-dark"><i class="fas fa-hand-holding-dollar text-info me-1"></i> 2. หมวดค่าตอบแทนและเงินเพิ่มพิเศษ</span>
                <span class="text-info font-monospace">ค่าตอบแทนสุทธิ: {{ number_format($compNet, 2) }} บาท</span>
            </div>
            <table class="table table-sm table-bordered mb-0" style="font-size: 0.8rem; border-color: #cbd5e1;">
                <thead>
                    <tr class="bg-light text-muted" style="border-color: #cbd5e1;">
                        <th style="width: 32%;">รายการเงินได้ (รับ)</th>
                        <th class="text-end" style="width: 18%;">จำนวนเงิน (บาท)</th>
                        <th style="width: 32%;">รายการเงินหัก (จ่าย)</th>
                        <th class="text-end" style="width: 18%;">จำนวนเงิน (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!$compen && count($compReceivesList) === 0 && count($compPaysList) === 0)
                        <tr>
                            <td class="text-muted text-center py-1" colspan="2">ไม่มีรายการค่าตอบแทนในงวดนี้</td>
                            <td class="text-muted text-center py-1" colspan="2">ไม่มีรายการหัก</td>
                        </tr>
                    @else
                        @for($i = 0; $i < $compRowCount; $i++)
                            @php
                                $rec = $compReceivesList[$i] ?? null;
                                $pay = $compPaysList[$i] ?? null;
                                $recObj = is_object($rec) ? $rec : (object)$rec;
                                $payObj = is_object($pay) ? $pay : (object)$pay;
                            @endphp
                            <tr>
                                <td class="py-1">{{ $rec ? ($recObj->item_name ?? '-') : '' }}</td>
                                <td class="text-end font-monospace text-info py-1">
                                    {{ $rec ? number_format($recObj->amount, 2) : '' }}
                                </td>
                                <td class="py-1">{{ $pay ? ($payObj->item_name ?? '-') : '' }}</td>
                                <td class="text-end font-monospace text-danger py-1">
                                    {{ $pay ? ('-' . number_format($payObj->amount, 2)) : '' }}
                                </td>
                            </tr>
                        @endfor
                    @endif
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold" style="font-size: 0.8rem; border-color: #cbd5e1;">
                        <td>รวมเงินได้ (ค่าตอบแทน)</td>
                        <td class="text-end text-info font-monospace">{{ number_format($compTotalRec, 2) }}</td>
                        <td>รวมเงินหัก (ค่าตอบแทน)</td>
                        <td class="text-end text-danger font-monospace">
                            @if($compTotalPay > 0)
                                -{{ number_format($compTotalPay, 2) }}
                            @else
                                0.00
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Section 3: สรุปยอดรับสุทธิรวมทั้งสิ้น -->
        <table class="table table-sm table-bordered mb-3" style="font-size: 0.82rem; border-color: #cbd5e1;">
            <tbody>
                <tr class="table-light">
                    <td class="fw-bold" style="width: 32%;">รวมเงินได้ทั้งสิ้น (เงินเดือน + ค่าตอบแทน)</td>
                    <td class="text-end fw-bold font-monospace text-dark" style="width: 18%;">{{ number_format($grandRec, 2) }} บาท</td>
                    <td class="fw-bold" style="width: 32%;">รวมรายการหักทั้งสิ้น (เงินเดือน + ค่าตอบแทน)</td>
                    <td class="text-end fw-bold font-monospace text-danger" style="width: 18%;">
                        @if($grandPay > 0)
                            -{{ number_format($grandPay, 2) }}
                        @else
                            0.00
                        @endif
                        บาท
                    </td>
                </tr>
                <tr style="background-color: #f0fdf4;">
                    <td class="fw-bold text-dark fs-6" colspan="2">
                        ยอดเงินได้สุทธิที่ได้รับโอนเข้าบัญชี
                        <div class="small text-muted fw-normal" style="font-size: 0.76rem;">
                            (เงินเดือนสุทธิ {{ number_format($salNet, 2) }} + ค่าตอบแทนสุทธิ {{ number_format($compNet, 2) }})
                        </div>
                    </td>
                    <td class="text-end fw-bold fs-5 font-monospace text-success" colspan="2">
                        {{ number_format($grandNet, 2) }} บาท
                    </td>
                </tr>
                <tr>
                    <td class="fw-bold bg-light">จำนวนเงินตัวอักษร</td>
                    <td class="fw-bold text-dark" colspan="3" style="font-size: 0.85rem;">{{ $grandNetThai }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Section 4: การลงนามรับรอง -->
        <div class="signature-section pt-2 mb-2" style="font-size: 0.82rem;">
            <div class="row text-center g-2">
                <div class="col-6">
                    <div>ลงชื่อ......................................................................ผู้รับเงิน</div>
                    <div class="mt-1 fw-bold">({{ $person->HR_PREFIX_NAME }}{{ $person->HR_FNAME }} {{ $person->HR_LNAME }})</div>
                    <div class="text-muted small mt-1">วันที่ ......... เดือน ......................... พ.ศ. ...............</div>
                </div>
                <div class="col-6">
                    <div>ลงชื่อ......................................................................ผู้จ่ายเงิน</div>
                    <div class="mt-1 fw-bold">(......................................................................)</div>
                    <div class="text-muted small">เจ้าหน้าที่การเงินและบัญชี</div>
                    <div class="text-muted small mt-1">วันที่ ......... เดือน ......................... พ.ศ. ...............</div>
                </div>
            </div>
        </div>

        <!-- Official Disclaimer Footer -->
        <div class="text-muted text-center pt-2 border-top mt-3" style="font-size: 0.72rem;">
            เอกสารนี้จัดทำโดยระบบสารสนเทศทางการเงินและบุคลากร BackOffice โรงพยาบาลหัวตะพาน 
            • ออกเมื่อวันที่ {{ date('d/m/Y H:i') }} น.
        </div>
    </div>
</div>

<div class="modal-footer bg-white border-0 py-3 px-4 d-flex justify-content-between">
    <button type="button" class="btn btn-outline-dark rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" onclick="printSlip()">
        <i class="fas fa-print"></i> พิมพ์ใบแจ้งยอด (ขนาด A4)
    </button>
    <button type="button" class="btn btn-secondary px-4 rounded-pill shadow-sm" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i> ปิด
    </button>
</div>
