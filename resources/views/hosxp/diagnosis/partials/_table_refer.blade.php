<div class="table-responsive">            
    <table id="diag_list" class="table table-modern w-100 mb-0">
        <thead>
            <tr>
                <th class="text-center col-order">ลำดับ</th>
                <th class="text-center">HN</th>
                <th class="text-center">ชื่อ-สกุล | ประวัติการเจ็บป่วย</th>
                <th class="text-center">จุดที่ส่งต่อ | แผนก</th>
                <th class="text-center">PDX | PDX Refer</th> 
                <th class="text-center">วันที่ออก Refer</th>
                <th class="text-center">วินิจฉัยก่อนส่งต่อ</th>
                <th class="text-center">สถานพยาบาลปลายทาง</th>          
            </tr>     
        </thead> 
        <tbody> 
            @php $count = 1 ; @endphp
            @foreach($diag_list as $row)          
            <tr>
                <td class="text-center col-order text-muted small">{{ $count }}</td> 
                <td class="text-center">
                    <span class="fw-bold text-primary">{{ $row->hn }}</span>
                </td> 
                <td class="text-start">
                    <div class="text-dark fw-bold small">{{ $row->ptname }}</div>
                    <div class="text-muted xsmall" style="max-width: 200px">{{ $row->pmh ?: '-' }}</div>
                    <div class="text-info xsmall">คลินิก: {{ $row->clinic ?: '-' }}</div>
                </td>
                <td class="text-start">
                    <div class="small fw-bold">{{ $row->refer_point ?: '-' }}</div>
                    <div class="text-muted small">{{ $row->department ?: '-' }}</div>
                </td>
                <td class="text-center">
                    <div class="badge badge-pdx mb-1">{{ $row->pdx ?: '-' }}</div>
                    <div class="badge bg-secondary text-white xsmall">{{ $row->pdx_refer ?: '-' }}</div>
                </td>
                <td class="text-center">
                    <div class="small fw-bold">{{ DateThai($row->refer_date) }}</div>
                    <div class="text-muted xsmall">เวลา {{ $row->refer_time }}</div>
                    <div class="xsmall text-info mt-1">Visit: {{ DateThai($row->vstdate) }}</div>
                </td>
                <td class="text-start">
                    <div class="small text-muted" style="font-size: 0.75rem; line-height: 1.2;">{{ Str::limit($row->pre_diagnosis, 100) }}</div>
                </td>
                <td class="text-start">
                    <div class="small fw-bold text-danger">{{ $row->refer_hos }}</div>
                </td>                 
            </tr>                
            @php $count++; @endphp
            @endforeach                
        </tbody>
    </table>  
</div>
