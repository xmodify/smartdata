@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
<a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm" style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
    <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
</a>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<style>
    .page-header-container {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
        border: 1px solid #f0f0f0;
    }
    .report-title-box h5 {
        font-size: 1.1rem;
        letter-spacing: -0.01em;
    }
    .header-form-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .input-group-date {
        width: 160px !important;
    }
    .input-group-budget {
        width: 200px !important;
    }
    
    /* Override DataTables UI */
    button.dt-button.btn-excel {
        background-color: #198754 !important;
        border-color: #198754 !important;
        color: #fff !important;
        border-radius: 8px !important;
        font-size: 0.8rem !important;
        padding: 6px 15px !important;
        display: flex !important;
        align-items: center !important;
        gap: 8px !important;
        box-shadow: 0 2px 4px rgba(25, 135, 84, 0.2) !important;
        transition: all 0.2s !important;
    }
    button.dt-button.btn-excel:hover {
        background-color: #157347 !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 8px rgba(25, 135, 84, 0.3) !important;
    }
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }
    .dataTables_filter input {
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        padding: 5px 12px !important;
        font-size: 0.85rem !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-3">
    <!-- Header Box -->
    <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center report-title-box">
            <div class="ps-3 py-1">
                <h5 class="text-dark mb-0 fw-bold">
                    <i class="fas fa-virus text-primary me-2"></i>
                    {{ $title }}
                </h5>
                <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
            </div>
        </div>
        
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="m-0 header-form-controls">
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 text-primary"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius: 8px; overflow: hidden;">
                    <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
                        @foreach ($budget_year_select as $row)
                            <option value="{{ $row->LEAVE_YEAR_ID }}"
                                {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>
                                {{ $row->LEAVE_YEAR_NAME }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary px-3" style="font-size: 0.8rem;">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="border-radius: 15px; background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">จำนวนผู้ป่วยเฝ้าระวังทั้งหมด</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format(collect($report_data)->sum('sum')) }}</div>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle">
                            <i class="fas fa-head-side-mask fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="border-radius: 15px; background: linear-gradient(135deg, #36b9cc 0%, #1a8a97 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">ชาย</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format(collect($report_data)->sum('male')) }}</div>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle">
                            <i class="fas fa-mars fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-white" style="border-radius: 15px; background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small opacity-75">หญิง</div>
                            <div class="h4 mb-0 fw-bold">{{ number_format(collect($report_data)->sum('female')) }}</div>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded-circle">
                            <i class="fas fa-venus fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table id="reportTable" class="table table-hover align-middle" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center" style="width: 80px;">อันดับ</th>
                            <th>กลุ่มโรค / รหัสเฝ้าระวัง</th>
                            <th class="text-center">ชาย (ราย)</th>
                            <th class="text-center">หญิง (ราย)</th>
                            <th class="text-center">รวม (ราย)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report_data as $index => $row)
                            <tr>
                                <td class="text-center">
                                    {{ $index + 1 }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row->name }}</div>
                                </td>
                                <td class="text-center text-primary">{{ number_format($row->male) }}</td>
                                <td class="text-center text-danger">{{ number_format($row->female) }}</td>
                                <td class="text-center fw-bold text-success">{{ number_format($row->sum) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<script>
    $(document).ready(function() {
        if (typeof flatpickr !== 'undefined') {
            const yearOffset = 543;
            const commonConfig = {
                locale: "th",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j M Y",
                allowInput: true,
                onReady: function(selectedDates, dateStr, instance) {
                    const todayBtn = document.createElement("div");
                    todayBtn.innerHTML = "วันนี้";
                    todayBtn.className = "text-primary fw-bold text-center py-2 border-top";
                    todayBtn.style.cursor = "pointer";
                    todayBtn.addEventListener("click", () => {
                        instance.setDate(new Date(), true);
                        instance.close();
                    });
                    instance.calendarContainer.appendChild(todayBtn);
                    
                    if (instance.altInput) {
                        const originalValue = instance.altInput.value;
                        if (originalValue) {
                            const date = instance.selectedDates[0] || new Date(instance.input.value);
                            const day = date.getDate();
                            const month = instance.l10n.months.shorthand[date.getMonth()];
                            const year = date.getFullYear() + yearOffset;
                            instance.altInput.value = `${day} ${month} ${year}`;
                        }
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (instance.altInput && selectedDates.length > 0) {
                        const date = selectedDates[0];
                        const day = date.getDate();
                        const month = instance.l10n.months.shorthand[date.getMonth()];
                        const year = date.getFullYear() + yearOffset;
                        setTimeout(() => {
                            instance.altInput.value = `${day} ${month} ${year}`;
                        }, 10);
                    }
                }
            };

            const startPicker = flatpickr("#start_date", commonConfig);
            const endPicker = flatpickr("#end_date", commonConfig);

            // Synchronize: Clear dates when budget year changes
            $('select[name="budget_year"]').on('change', function() {
                if (typeof startPicker !== 'undefined') startPicker.clear();
                if (typeof endPicker !== 'undefined') endPicker.clear();
                $('#start_date, #end_date').val('');
            });
        }

        $('#reportTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json",
            },
            pageLength: 10,
            dom: '<"d-flex justify-content-between align-items-center mb-3"<"dt-left-info"> <"d-flex gap-2"fB>>rtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn-excel',
                    title: '{{ $title }}',
                    messageTop: 'ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}'
                }
            ],
            responsive: true,
            order: [[4, 'desc']],
            initComplete: function() {
                $("div.dt-left-info").html('<div class="text-primary fw-bold"><i class="fas fa-calendar-alt me-1"></i> ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}</div>');
            }
        });
    });
</script>
@endpush
@endsection
