@php
    $show_back = (isset($sub_id) && $sub_id !== 'all' && $sub_id !== '0' && $sub_id !== '');
    $back_onclick = $show_back ? "openTableDetailModal('all', '$month', '$year', '$program_id', 'all', 'all', 1)" : "";
@endphp
<div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        @if ($show_back)
            <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 py-1 px-2" onclick="{{ $back_onclick }}">
                <i class="fas fa-arrow-left"></i> ย้อนกลับ
            </button>
        @endif
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
    </div>
    <div class="text-muted small">
        ช่วงเวลา: <strong>{{ DateThai($start_date) }}</strong> ถึง <strong>{{ DateThai($end_date) }}</strong>
    </div>
</div>

<div class="table-responsive">
    <table id="modalTableDrilldown" class="table table-bordered table-hover align-middle w-100" style="font-size: 0.85rem;">
        <thead class="table-light">
            <tr>
                <th class="text-start align-middle" style="min-width: 250px; background-color: #f8fafc;">
                    {{ isset($subs) ? 'โปรแกรมย่อย 1' : 'โปรแกรมย่อย 2' }}
                </th>
                <th class="text-center align-middle fw-bold text-primary bg-light" style="width: 70px;">รวม</th>
                @foreach(['A','B','C','D','E','F','G','H','I','1','2','3','4','5','Null'] as $lvl)
                    <th class="text-center align-middle" style="width: 45px;">{{ $lvl }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $rows = isset($subs) ? $subs : $subsubs;
                $no_row = isset($subs) ? $no_sub : $no_subsub;
                $has_records = false;
                
                // Initialize totals
                $total_sum = 0;
                $col_sums = [
                    'a' => 0, 'b' => 0, 'c' => 0, 'd' => 0, 'e' => 0, 'f' => 0, 'g' => 0, 'h' => 0, 'i' => 0,
                    'g1' => 0, 'g2' => 0, 'g3' => 0, 'g4' => 0, 'g5' => 0, 'null' => 0
                ];
            @endphp

            @if (!empty($rows))
                @foreach ($rows as $row)
                    @if ($row->total > 0)
                        @php 
                            $has_records = true; 
                            $total_sum += $row->total;
                            $col_sums['a'] += $row->a;
                            $col_sums['b'] += $row->b;
                            $col_sums['c'] += $row->c;
                            $col_sums['d'] += $row->d;
                            $col_sums['e'] += $row->e;
                            $col_sums['f'] += $row->f;
                            $col_sums['g'] += $row->g;
                            $col_sums['h'] += $row->h;
                            $col_sums['i'] += $row->i;
                            $col_sums['g1'] += $row->g1;
                            $col_sums['g2'] += $row->g2;
                            $col_sums['g3'] += $row->g3;
                            $col_sums['g4'] += $row->g4;
                            $col_sums['g5'] += $row->g5;
                            $col_sums['null'] += $row->null;
                        @endphp
                        <tr>
                            <td class="fw-bold">
                                @if (isset($subs))
                                    <!-- Drill down from Sub 1 to Sub 2 -->
                                    <a href="javascript:void(0)" class="text-decoration-underline text-dark" onclick="openTableDetailModal('{{ $level }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $row->id }}', 'all', 1)">
                                        {{ $row->name ?: 'ไม่ระบุ' }}
                                    </a>
                                @else
                                    <!-- Show flat incidents list for Sub 2 -->
                                    <a href="javascript:void(0)" class="text-decoration-underline text-dark" onclick="openTableDetailModal('{{ $level }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $sub_id }}', '{{ $row->id }}', 1)">
                                        {{ $row->name ?: 'ไม่ระบุ' }}
                                    </a>
                                @endif
                            </td>
                            <td class="text-center fw-bold bg-light">
                                @if (isset($subs))
                                    <a href="javascript:void(0)" class="text-decoration-underline text-primary" onclick="openTableDetailModal('{{ $level }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $row->id }}', 'all', 1)">
                                        {{ number_format($row->total) }}
                                    </a>
                                @else
                                    <a href="javascript:void(0)" class="text-decoration-underline text-primary" onclick="openTableDetailModal('{{ $level }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $sub_id }}', '{{ $row->id }}', 1)">
                                        {{ number_format($row->total) }}
                                    </a>
                                @endif
                            </td>
                            
                            <!-- Severity Levels matrix -->
                            @php
                                $levels_map = [
                                    'A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e', 'F' => 'f', 'G' => 'g', 'H' => 'h', 'I' => 'i',
                                    '1' => 'g1', '2' => 'g2', '3' => 'g3', '4' => 'g4', '5' => 'g5', 'Null' => 'null'
                                ];
                            @endphp
                            @foreach ($levels_map as $lvl_param => $prop)
                                <td class="text-center">
                                    @if ($row->$prop > 0)
                                        @if (isset($subs))
                                            <a href="javascript:void(0)" class="text-decoration-underline text-dark" onclick="openTableDetailModal('{{ $lvl_param }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $row->id }}', 'all', 0)">
                                                {{ $row->$prop }}
                                            </a>
                                        @else
                                            <a href="javascript:void(0)" class="text-decoration-underline text-dark" onclick="openTableDetailModal('{{ $lvl_param }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $sub_id }}', '{{ $row->id }}', 0)">
                                                {{ $row->$prop }}
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            @endif

            <!-- Direct incidents with no sub-program (อื่นๆ) -->
            @if ($no_row && $no_row->total > 0)
                @php
                    $has_records = true;
                    $total_sum += $no_row->total;
                    $col_sums['a'] += $no_row->a;
                    $col_sums['b'] += $no_row->b;
                    $col_sums['c'] += $no_row->c;
                    $col_sums['d'] += $no_row->d;
                    $col_sums['e'] += $no_row->e;
                    $col_sums['f'] += $no_row->f;
                    $col_sums['g'] += $no_row->g;
                    $col_sums['h'] += $no_row->h;
                    $col_sums['i'] += $no_row->i;
                    $col_sums['g1'] += $no_row->g1;
                    $col_sums['g2'] += $no_row->g2;
                    $col_sums['g3'] += $no_row->g3;
                    $col_sums['g4'] += $no_row->g4;
                    $col_sums['g5'] += $no_row->g5;
                    $col_sums['null'] += $no_row->null;
                @endphp
                <tr class="table-light">
                    <td class="fw-bold text-muted text-start">
                        {{ $no_row->name }}
                    </td>
                    <td class="text-center fw-bold bg-light">
                        @if (isset($subs))
                            <a href="javascript:void(0)" class="text-decoration-underline text-primary" onclick="openTableDetailModal('{{ $level }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '0', 'all', 1)">
                                {{ number_format($no_row->total) }}
                            </a>
                        @else
                            <a href="javascript:void(0)" class="text-decoration-underline text-primary" onclick="openTableDetailModal('{{ $level }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $sub_id }}', '0', 1)">
                                {{ number_format($no_row->total) }}
                            </a>
                        @endif
                    </td>
                    @php
                        $levels_map = [
                            'A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e', 'F' => 'f', 'G' => 'g', 'H' => 'h', 'I' => 'i',
                            '1' => 'g1', '2' => 'g2', '3' => 'g3', '4' => 'g4', '5' => 'g5', 'Null' => 'null'
                        ];
                    @endphp
                    @foreach ($levels_map as $lvl_param => $prop)
                        <td class="text-center">
                            @if ($no_row->$prop > 0)
                                @if (isset($subs))
                                    <a href="javascript:void(0)" class="text-decoration-underline text-dark" onclick="openTableDetailModal('{{ $lvl_param }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '0', 'all', 0)">
                                        {{ $no_row->$prop }}
                                    </a>
                                @else
                                    <a href="javascript:void(0)" class="text-decoration-underline text-dark" onclick="openTableDetailModal('{{ $lvl_param }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $sub_id }}', '0', 0)">
                                        {{ $no_row->$prop }}
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endif

            @if (!$has_records)
                <tr>
                    <td colspan="17" class="text-center py-4 text-muted">ไม่พบข้อมูลอุบัติการณ์ความเสี่ยง</td>
                </tr>
            @endif
        </tbody>
        
        @if ($has_records)
            <tfoot class="table-light fw-bold text-dark">
                <tr>
                    <td class="text-center">รวม</td>
                    <td class="text-center text-primary bg-light">
                        {{ number_format($total_sum) }}
                    </td>
                    @php
                        $levels_map = [
                            'A' => 'a', 'B' => 'b', 'C' => 'c', 'D' => 'd', 'E' => 'e', 'F' => 'f', 'G' => 'g', 'H' => 'h', 'I' => 'i',
                            '1' => 'g1', '2' => 'g2', '3' => 'g3', '4' => 'g4', '5' => 'g5', 'Null' => 'null'
                        ];
                    @endphp
                    @foreach ($levels_map as $lvl_param => $prop)
                        <td class="text-center">
                            @if ($col_sums[$prop] > 0)
                                @if (isset($subs))
                                    <a href="javascript:void(0)" class="text-decoration-underline text-dark" onclick="openTableDetailModal('{{ $lvl_param }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', 'all', 'all', 0)">
                                        {{ $col_sums[$prop] }}
                                    </a>
                                @else
                                    <a href="javascript:void(0)" class="text-decoration-underline text-dark" onclick="openTableDetailModal('{{ $lvl_param }}', '{{ $month }}', '{{ $year }}', '{{ $program_id }}', '{{ $sub_id }}', 'all', 0)">
                                        {{ $col_sums[$prop] }}
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</div>
