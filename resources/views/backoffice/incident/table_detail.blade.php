@php
    $show_back = ($program_id !== 'all' && $program_id !== '');
    $back_onclick = $show_back ? "openTableDetailModal('all', '$month', '$year', '$program_id', 'all', 'all', 1)" : "";
@endphp
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        @if ($show_back)
            <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 py-1 px-2" onclick="{{ $back_onclick }}">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </button>
        @endif
        @if (!empty($breadcrumbs))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 0.9rem;">
                    <li class="breadcrumb-item">
                        <a href="javascript:void(0)" class="text-decoration-none fw-bold" onclick="openTableDetailModal('{{ $level }}', '{{ $month }}', '{{ $year }}', 'all')">โปรแกรมหลักทั้งหมด</a>
                    </li>
                    @foreach ($breadcrumbs as $index => $crumb)
                        @if ($index === count($breadcrumbs) - 1)
                            <li class="breadcrumb-item active fw-bold text-primary" aria-current="page">{{ $crumb['name'] }}</li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="javascript:void(0)" class="text-decoration-none fw-bold" onclick="openTableDetailModal('{{ $level }}', '{{ $month }}', '{{ $year }}', '{{ $crumb['program_id'] }}', '{{ $crumb['sub_id'] }}', '{{ $crumb['subsub_id'] }}', 1)">{{ $crumb['name'] }}</a>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
    <div class="text-muted small">
        ช่วงเวลา: <strong>{{ DateThai($start_date) }}</strong> ถึง <strong>{{ DateThai($end_date) }}</strong>
    </div>
</div>

<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    @if ($program_id !== 'all')
        <span class="badge bg-primary px-3 py-2" style="font-size: 0.85rem;">
            โปรแกรม: {{ $incidents[0]->RISK_REPPROGRAM_NAME ?? ($program_id == '0' ? 'Non-Program' : 'รหัสโปรแกรม ' . $program_id) }}
        </span>
    @endif
    @if ($level !== 'all')
        <span class="badge bg-secondary px-3 py-2" style="font-size: 0.85rem;">
            ระดับความรุนแรง: {{ $level }}
        </span>
    @endif
    @if ($month !== 'all')
        @php
            $months_th = [
                '1' => 'ม.ค.', '2' => 'ก.พ.', '3' => 'มี.ค.', '4' => 'เม.ย.', 
                '5' => 'พ.ค.', '6' => 'มิ.ย.', '7' => 'ก.ค.', '8' => 'ส.ค.', 
                '9' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
            ];
            $m_name = $months_th[$month] ?? '';
            $y_th = $year !== 'all' ? ($year + 543) : '';
        @endphp
        <span class="badge bg-info text-white px-3 py-2" style="font-size: 0.85rem;">
            เดือน: {{ $m_name }} {{ $y_th }}
        </span>
    @endif
    @if ($program_id === 'all' && $level === 'all' && $month === 'all')
        <span class="badge bg-success text-white px-3 py-2" style="font-size: 0.85rem;">
            ทั้งหมด
        </span>
    @endif
</div>

<div class="table-responsive">
    <table id="modalTableIncidents" class="table table-hover align-middle w-100" style="font-size: 0.85rem;">
        <thead>
            <tr>
                <th style="width: 10%;">รหัส</th>
                <th style="width: 12%;">วันที่เกิดอุบัติการณ์</th>
                <th style="width: 8%;">ความรุนแรง</th>
                <th style="width: 8%;">Consequence</th>
                <th style="width: 8%;">Likelihood</th>
                <th style="width: 15%;">โปรแกรมหลัก</th>
                <th style="width: 15%;">โปรแกรมย่อย</th>
                <th style="width: 30%;">รายละเอียด</th>
                <th style="width: 10%;">วันที่ทบทวน</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($incidents as $row)
                <tr>
                    <td class="fw-bold text-primary">{{ $row->id }}</td>
                    <td>{{ DateThai($row->RISKREP_STARTDATE) }}</td>
                    <td class="text-center"><span class="badge bg-secondary">{{ $row->RISK_REP_LEVEL_NAME ?: 'Null' }}</span></td>
                    <td class="text-center fw-bold">{{ $row->consequence ?: '-' }}</td>
                    <td class="text-center fw-bold">{{ $row->likelihood ?: '-' }}</td>
                    <td>{{ $row->RISK_REPPROGRAM_NAME }}</td>
                    <td>{{ $row->RISK_REPPROGRAMSUB_NAME }}</td>
                    <td>
                        <div class="detail-scroll" title="{{ $row->RISKREP_DETAILRISK }}">
                            {{ $row->RISKREP_DETAILRISK }}
                        </div>
                    </td>
                    <td>
                        @if ($row->recheck)
                            @foreach (explode(',', $row->recheck) as $date)
                                <span class="badge bg-success d-block mb-1">{{ DateThai(trim($date)) }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
