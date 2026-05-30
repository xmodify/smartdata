<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <span class="badge bg-primary px-3 py-2" style="font-size: 0.9rem;">
            ประเภท: {{ ucfirst($type) }}
        </span>
        <span class="badge bg-secondary px-3 py-2 ms-1" style="font-size: 0.9rem;">
            Consequence Score: {{ $consequence }}
        </span>
        <span class="badge bg-info text-white px-3 py-2 ms-1" style="font-size: 0.9rem;">
            Likelihood Score: {{ $likelihood }}
        </span>
    </div>
    <div class="text-muted small">
        ช่วงเวลา: <strong>{{ DateThai($start_date) }}</strong> ถึง <strong>{{ DateThai($end_date) }}</strong>
    </div>
</div>

<div class="table-responsive">
    <table id="modalTableIncidents" class="table table-hover align-middle w-100" style="font-size: 0.85rem;">
        <thead>
            <tr>
                <th style="width: 10%;">รหัส</th>
                <th style="width: 12%;">วันที่เกิดความเสี่ยง</th>
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
                    <td class="text-center"><span class="badge bg-secondary">{{ $row->RISK_REP_LEVEL_NAME }}</span></td>
                    <td class="text-center fw-bold">{{ $row->consequence }}</td>
                    <td class="text-center fw-bold">{{ $row->likelihood }}</td>
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
