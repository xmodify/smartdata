<div class="table-responsive">            
    <table id="diag_list" class="table table-modern w-100 mb-0">
        <thead>
            <tr>
                <th class="text-center col-order">ลำดับ</th>
                <th class="text-center">วัน-เวลาที่รับบริการ</th>     
                <th class="text-center">HN</th>
                <th class="text-center">ชื่อ-สกุล | อายุ</th>
                <th class="text-center">สิทธิ์การรักษา</th>
                <th class="text-center">อาการสำคัญ</th>
                <th class="text-center">แพทย์ผู้ตรวจ</th>
                <th class="text-center">PDX | DX</th> 
                <th class="text-center">ADMIT/REFER</th>          
            </tr>     
        </thead> 
        <tbody> 
            @php $count = 1 ; @endphp
            @foreach($diag_list as $row)          
            <tr>
                <td class="text-center col-order text-muted small">{{ $count }}</td> 
                <td class="text-start">
                    <div class="small fw-bold">{{ DateThai($row->vstdate) }}</div>
                    <div class="text-muted xsmall">เวลา {{ $row->vsttime }} | Q: {{ $row->oqueue }}</div>
                </td> 
                <td class="text-center">
                    <span class="fw-bold text-primary">{{ $row->hn }}</span>
                </td> 
                <td class="text-start">
                    <div class="text-dark fw-bold small">{{ $row->ptname }}</div>
                    <div class="text-info xsmall">อายุ {{ $row->age_y }} ปี</div>
                </td>
                <td class="text-start">
                    <div class="small text-truncate" style="max-width: 140px;" title="{{ $row->pttype }}">{{ $row->pttype }}</div>
                </td>
                <td class="text-start">
                    <div class="small text-muted" style="font-size: 0.75rem; line-height: 1.2;">{{ Str::limit($row->cc, 80) }}</div>
                </td>
                <td class="text-center">
                    <div class="small fw-bold text-dark">{{ $row->dx_doctor }}</div>
                    <div class="xsmall text-muted">{{ $row->ovstist }}</div>
                </td>
                <td class="text-center">
                    <div class="badge badge-pdx mb-1">{{ $row->pdx }}</div>
                    <div class="xsmall text-muted text-truncate" style="max-width: 120px;" title="{{ $row->dx }}">{{ $row->dx }}</div>
                </td>
                <td class="text-center">
                    @if(isset($row->admit) && $row->admit) <span class="badge bg-warning text-dark xsmall">Admit</span> @endif
                    @if(isset($row->refer) && $row->refer) 
                        <div class="badge bg-danger text-white xsmall mb-1">Refer</div>
                        <div class="xsmall text-muted text-truncate" style="max-width:100px" title="{{ $row->refer }}">{{ $row->refer }}</div>
                    @endif
                    @if(!(isset($row->admit) && $row->admit) && !(isset($row->refer) && $row->refer)) <span class="text-muted">-</span> @endif
                </td>                 
            </tr>                
            @php $count++; @endphp
            @endforeach                
        </tbody>
    </table>  
</div>
