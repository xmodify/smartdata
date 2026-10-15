<div class="modal-header bg-gradient-primary text-white py-3 px-4" style="background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);">
    <div class="d-flex align-items-center">
        <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; font-size: 1.4rem;">
            <i class="fas fa-user-tie"></i>
        </div>
        <div>
            <h5 class="modal-title fw-bold text-white mb-0">
                {{ $person->HR_PREFIX_NAME }}{{ $person->HR_FNAME }} {{ $person->HR_LNAME }}
            </h5>
            <div class="text-white text-opacity-85 small mt-1">
                <span><i class="fas fa-briefcase me-1"></i> {{ $person->POSITION_IN_WORK ?: 'ไม่ระบุตำแหน่ง' }}</span>
                <span class="mx-2">•</span>
                <span><i class="fas fa-building me-1"></i> {{ $person->HR_DEPARTMENT_SUB_SUB_NAME ?: $person->HR_DEPARTMENT_NAME }}</span>
                <span class="mx-2">•</span>
                <span><i class="fas fa-id-card me-1"></i> {{ $person->HR_CID }}</span>
                @if($book_num)
                    <span class="mx-2">•</span>
                    <span><i class="fas fa-credit-card me-1"></i> บัญชี: {{ $book_num }}</span>
                @endif
            </div>
        </div>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4 bg-light">
    <!-- Year Selector & Year Summary -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold mb-1">
                        <i class="fas fa-calendar-alt text-primary me-1"></i> เลือกปีงบประมาณ:
                    </label>
                    <select class="form-select form-select-sm fw-bold border-primary shadow-sm" id="modalYearSelect" onchange="loadPersonDetail({{ $person->ID }}, this.value)">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $y == $year_id ? 'selected' : '' }}>ปีงบประมาณ {{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                                <div class="text-muted small" style="font-size: 0.72rem;">เงินเดือนสุทธิทั้งปี</div>
                                <div class="fw-bold text-primary fs-6">{{ number_format($yearlySummary['salary_net'], 2) }} ฿</div>
                                <div class="text-muted" style="font-size: 0.68rem;">รับ {{ number_format($yearlySummary['salary_rec'], 2) }} | หัก {{ number_format($yearlySummary['salary_pay'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-info bg-opacity-10 border border-info border-opacity-25">
                                <div class="text-muted small" style="font-size: 0.72rem;">ค่าตอบแทนสุทธิทั้งปี</div>
                                <div class="fw-bold text-info fs-6">{{ number_format($yearlySummary['compen_net'], 2) }} ฿</div>
                                <div class="text-muted" style="font-size: 0.68rem;">รับ {{ number_format($yearlySummary['compen_rec'], 2) }} | หัก {{ number_format($yearlySummary['compen_pay'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-success bg-opacity-10 border border-success border-opacity-25">
                                <div class="text-muted small" style="font-size: 0.72rem;">รวมรับสุทธิทั้งปี</div>
                                <div class="fw-bold text-success fs-6">{{ number_format($yearlySummary['grand_net'], 2) }} ฿</div>
                                <div class="text-muted" style="font-size: 0.68rem;">เงินเดือน + ค่าตอบแทน</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">
                <i class="fas fa-list-ol text-success me-2"></i>
                รายละเอียดการจ่ายเงินรายเดือน ประจำปีงบประมาณ {{ $year_id }}
            </h6>
            <span class="badge bg-light text-muted border px-2 py-1 small">
                ตุลาคม {{ (int)$year_id - 1 }} - กันยายน {{ $year_id }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead class="table-light">
                    <tr class="text-muted small">
                        <th class="ps-4" style="width: 140px;">งวดเดือน</th>
                        <th class="text-end text-success">เงินเดือน (รับ)</th>
                        <th class="text-end text-danger">เงินเดือน (หัก)</th>
                        <th class="text-end text-dark">เงินเดือน (สุทธิ)</th>
                        <th class="text-end text-info border-start">ค่าตอบแทน (รับ)</th>
                        <th class="text-end text-danger">ค่าตอบแทน (หัก)</th>
                        <th class="text-end text-info">ค่าตอบแทน (สุทธิ)</th>
                        <th class="text-end bg-success bg-opacity-10 text-success fw-bold">รวมรับสุทธิเดือนนี้</th>
                        <th class="text-center pe-4" style="width: 110px;">พิมพ์สลิป</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fiscalMonths as $fm)
                        <tr class="{{ $fm['has_data'] ? '' : 'text-muted bg-light bg-opacity-50' }}">
                            <td class="ps-4 fw-bold">
                                @if($fm['has_data'])
                                    <span class="badge bg-success bg-opacity-10 text-success me-1"><i class="fas fa-check-circle"></i></span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-muted me-1"><i class="fas fa-minus-circle"></i></span>
                                @endif
                                {{ $fm['month_name'] }} {{ $fm['cal_year'] }}
                            </td>

                            {{-- Salary --}}
                            @if($fm['salary'])
                                <td class="text-end text-muted">{{ number_format($fm['salary']['receive'], 2) }}</td>
                                <td class="text-end text-danger">
                                    @if($fm['salary']['pay'] > 0)
                                        -{{ number_format($fm['salary']['pay'], 2) }}
                                    @else
                                        0.00
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-dark">{{ number_format($fm['salary']['net'], 2) }}</td>
                            @else
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                            @endif

                            {{-- Compensation --}}
                            @if($fm['compen'])
                                <td class="text-end text-muted border-start">{{ number_format($fm['compen']['receive'], 2) }}</td>
                                <td class="text-end text-danger">
                                    @if($fm['compen']['pay'] > 0)
                                        -{{ number_format($fm['compen']['pay'], 2) }}
                                    @else
                                        0.00
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-info">{{ number_format($fm['compen']['net'], 2) }}</td>
                            @else
                                <td class="text-end text-muted border-start">-</td>
                                <td class="text-end text-muted">-</td>
                                <td class="text-end text-muted">-</td>
                            @endif

                            {{-- Grand Net --}}
                            <td class="text-end fw-bold fs-6 {{ $fm['has_data'] ? 'text-success bg-success bg-opacity-10' : 'text-muted' }}">
                                @if($fm['has_data'])
                                    {{ number_format($fm['combined_net'], 2) }} ฿
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Single Print Slip Button for Month --}}
                            <td class="text-center pe-4">
                                @if($fm['has_data'])
                                    <button type="button" class="btn btn-sm btn-outline-success py-1 px-3 rounded-pill shadow-xs d-inline-flex align-items-center gap-1"
                                        style="font-size: 0.78rem;" onclick="loadMonthlySlip({{ $person->ID }}, '{{ $year_id }}', '{{ $fm['month_id'] }}')">
                                        <i class="fas fa-print"></i> พิมพ์สลิป
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light border-top-2">
                    <tr class="fw-bold">
                        <td class="ps-4">รวมทั้งปีงบประมาณ {{ $year_id }}</td>
                        <td class="text-end text-muted">{{ number_format($yearlySummary['salary_rec'], 2) }}</td>
                        <td class="text-end text-danger">-{{ number_format($yearlySummary['salary_pay'], 2) }}</td>
                        <td class="text-end text-dark">{{ number_format($yearlySummary['salary_net'], 2) }}</td>
                        <td class="text-end text-muted border-start">{{ number_format($yearlySummary['compen_rec'], 2) }}</td>
                        <td class="text-end text-danger">
                            @if($yearlySummary['compen_pay'] > 0)
                                -{{ number_format($yearlySummary['compen_pay'], 2) }}
                            @else
                                0.00
                            @endif
                        </td>
                        <td class="text-end text-info">{{ number_format($yearlySummary['compen_net'], 2) }}</td>
                        <td class="text-end text-success bg-success bg-opacity-10 fs-6">
                            {{ number_format($yearlySummary['grand_net'], 2) }} ฿
                        </td>
                        <td class="text-center pe-4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="modal-footer bg-white border-0 py-3 px-4 d-flex justify-content-between">
    <div class="text-muted small">
        <i class="fas fa-info-circle text-primary me-1"></i> ข้อมูลนำเข้าจากระบบบัญชีเงินเดือนและค่าตอบแทน BackOffice
    </div>
    <button type="button" class="btn btn-secondary px-4 rounded-pill shadow-sm" data-bs-dismiss="modal">
        <i class="fas fa-times me-1"></i> ปิด
    </button>
</div>
