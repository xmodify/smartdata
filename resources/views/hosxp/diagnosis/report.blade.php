@extends('layouts.app')

@section('title', 'รายชื่อผู้ป่วยนอกโรค ' . $config['name'])

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<style>
    .page-header-container {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        margin-bottom: 2rem;
    }
    .report-title-box h5 {
        font-size: 1.25rem;
        letter-spacing: -0.01em;
    }
    .budget-select-box {
        background: #f8f9fa;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    .table-modern thead th {
        background-color: #e3f2fd !important;
        color: #0d47a1;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 12px 10px;
        border-bottom: 2px solid #bbdefb !important;
    }
    .table-modern tbody td {
        vertical-align: middle;
        padding: 12px 10px;
    }
    .col-order { background-color: #f1f8fe; width: 50px; }
    .badge-pdx { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .dash-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    .card-header-premium {
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 1.25rem;
    }
    
    /* Override DataTables Buttons */
    button.dt-button.btn-success {
        background-color: #198754 !important;
        border-color: #198754 !important;
        color: #fff !important;
        border-radius: 0.2rem !important; /* sm radius */
        font-size: 0.875rem !important; /* sm font */
        padding: 0.25rem 0.5rem !important; /* sm padding */
    }
    button.dt-button.btn-success:hover {
        background-color: #157347 !important;
        border-color: #146c43 !important;
    }
</style>
@endpush

@section('topbar_actions')
<a href="{{ route('hosxp.diagnosis.index') }}" class="btn btn-light btn-sm shadow-sm text-primary fw-bold">
    <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
</a>
@endsection

@section('content')
<div class="container-fluid px-lg-4">
    <!-- Header Box -->
    <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center report-title-box">
            <div class="ps-3 py-1">
                <h5 class="text-dark mb-0 fw-bold">
                    <i class="{{ $config['icon'] }} {{ $config['color'] }} me-2"></i>
                    รายชื่อผู้ป่วยนอกโรค {{ $config['name'] }}
                </h5>
                <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
            </div>
        </div>
        
        <div class="d-flex align-items-center">
            <form method="POST" enctype="multipart/form-data" class="m-0">
                @csrf
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 border-radius-start-8"><i class="bi bi-calendar-event text-primary"></i></span>
                    <select class="form-select form-select-sm border-start-0 border-end-0" name="budget_year" style="min-width: 140px;">
                        @foreach ($budget_year_select as $row)
                            <option value="{{ $row->LEAVE_YEAR_ID }}"
                                {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                {{ $row->LEAVE_YEAR_NAME }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm px-4 border-radius-end-8">
                        <i class="bi bi-search me-2"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chart Row -->
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card dash-card h-100">
                <div class="card-header card-header-premium">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-bar-chart-fill text-info me-2"></i>
                        สถิติรายเดือน (ครั้ง/คน/Admit/Refer)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="diag_month" style="width: 100%; height: 350px"></canvas>             
                </div>      
            </div>      
        </div>   
        <div class="col-lg-5">
            <div class="card dash-card h-100">
                <div class="card-header card-header-premium">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-graph-up-arrow text-warning me-2"></i>
                        แนวโน้ม 5 ปีงบประมาณย้อนหลัง
                    </h6>
                </div>
                <div class="card-body">
                    <div id="diag_year" style="width: 100%; height: 350px"></div>             
                </div>      
            </div>      
        </div>      
    </div>

    <!-- Patient List Card -->
    <div class="card dash-card">
        <div class="card-header card-header-premium">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-people-fill text-primary me-2"></i>
                รายชื่อผู้ป่วยนอกโรค {{ $config['name'] }} ปีงบประมาณ {{ $budget_year }}
            </h6>
        </div>
        <div class="card-body p-0">
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
                            <th class="text-center">PDX | DX</th> 
                            <th class="text-center">ADMIT/REFER</th>  
                            <th class="text-center">ยา/LAB</th>          
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
                                <div class="badge badge-pdx mb-1">{{ $row->pdx }}</div>
                                <div class="xsmall text-muted text-truncate" style="max-width: 120px;">{{ $row->dx }}</div>
                            </td>
                            <td class="text-center">
                                @if($row->admit == 'Y') <span class="badge bg-warning text-dark xsmall">Admit</span> @endif
                                @if($row->refer == 'Y') <span class="badge bg-danger text-white xsmall">Refer</span> @endif
                                @if($row->admit != 'Y' && $row->refer != 'Y') <span class="text-muted">-</span> @endif
                            </td>      
                            <td class="text-end">
                                <div class="xsmall text-primary">ยา: {{ number_format($row->inc_drug,2) }}</div>
                                <div class="xsmall text-success">Lab: {{ number_format($row->inc_lab,2) }}</div>
                            </td>                 
                        </tr>                
                        @php $count++; @endphp
                        @endforeach                
                    </tbody>
                </table>  
            </div>         
        </div> 
    </div>  
</div>
<br>

@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
  
  <script>
    // Register the plugin to all charts if needed, or just specific ones
    Chart.register(ChartDataLabels);

    $(document).ready(function () {
      $('#diag_list').DataTable({
        dom: '<"d-flex justify-content-between align-items-center p-3"<"d-flex align-items-center"l><"d-flex align-items-center gap-3"fB>>rt<"d-flex justify-content-between align-items-center p-3"ip>',
        buttons: [
            {
              extend: 'excelHtml5',
              text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
              className: 'btn btn-success btn-sm px-3',
              title: 'รายชื่อผู้ป่วยนอกโรค {{ $config['name'] }} ปีงบประมาณ {{$budget_year}}'
            }
        ],
        language: {
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
            paginate: { previous: "ก่อนหน้า", next: "ถัดไป" }
        },
        pageLength: 10,
        order: [[0, 'asc']]
      });
    });

    document.addEventListener("DOMContentLoaded", () => {
      // Monthly Bar Chart
      new Chart(document.querySelector('#diag_month'), {
        type: 'bar',
        data: {
          labels: @json($diag_m),
          datasets: [
            {
              label: 'ครั้ง (Visits)',
              data: @json($diag_visit_m),
              backgroundColor: 'rgba(54, 162, 235, 0.7)',
              borderColor: 'rgb(54, 162, 235)',
              borderWidth: 1,
              borderRadius: 6,
              datalabels: { align: 'end', anchor: 'end' }
            },
            {
              label: 'คน (Patients)',
              data: @json($diag_hn_m),
              backgroundColor: 'rgba(153, 102, 255, 0.7)',
              borderColor: 'rgb(153, 102, 255)',
              borderWidth: 1,
              borderRadius: 6,
              datalabels: { align: 'end', anchor: 'end' }
            },
            {
              label: 'Admit',
              data: @json($diag_admit_m),
              backgroundColor: 'rgba(255, 205, 86, 0.7)',
              borderColor: 'rgb(255, 205, 86)',
              borderWidth: 1,
              borderRadius: 6,
              datalabels: { align: 'end', anchor: 'end' }
            },
            {
              label: 'Refer',
              data: @json($diag_refer_m),
              backgroundColor: 'rgba(255, 99, 132, 0.7)',
              borderColor: 'rgb(255, 99, 132)',
              borderWidth: 1,
              borderRadius: 6,
              datalabels: { align: 'end', anchor: 'end' }
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          layout: {
            padding: { top: 25 } // Add padding for labels
          },
          plugins: {
              legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8, font: { size: 12 } } },
              datalabels: {
                  color: '#444',
                  font: { weight: 'bold', size: 10 },
                  formatter: function(value, context) {
                      return value > 0 ? value : '';
                  }
              }
          },
          scales: {
            y: { grid: { borderDash: [5, 5] }, beginAtZero: true, ticks: { callback: (v) => v.toLocaleString() } },
            x: { grid: { display: false } }
          }
        }
      });

      // Yearly Area Chart
      new ApexCharts(document.querySelector("#diag_year"), {
          series: [
            { name: 'ครั้ง', data: @json($diag_visit_y) },
            { name: 'คน', data: @json($diag_hn_y) },
            { name: 'Admit', data: @json($diag_admit_y) },
            { name: 'Refer', data: @json($diag_refer_y) }
          ],
          chart: {
              height: 350,
              type: 'area',
              toolbar: { show: false },
              fontFamily: 'Nunito, sans-serif'
          },
          markers: { size: 5, strokeWidth: 3, hover: { size: 7 } },
          colors: [ '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444' ],
          fill: {
              type: "gradient",
              gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.5,
                opacityTo: 0.1,
                stops: [0, 90, 100]
              }
          },
          dataLabels: { enabled: false },
          stroke: { curve: 'smooth', width: 4 },
          xaxis: {
              categories: @json($diag_y),
              labels: { style: { colors: '#64748b', fontSize: '12px', fontWeight: 600 } },
              axisBorder: { show: false }
          },
          yaxis: {
              labels: { style: { colors: '#64748b' } }
          },
          grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
          legend: { position: 'bottom', horizontalAlign: 'center', offsetY: 8 }
      }).render();
    });
  </script>  
@endpush
