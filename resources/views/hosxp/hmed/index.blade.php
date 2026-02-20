@extends('layouts.app')

@section('title', 'SmartData | ' . $title)

@section('topbar_actions')
<a href="{{ route('hosxp.stats.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm" style="border-radius: 10px; padding: 5px 15px; color: #4e73df; transition: all 0.3s;">
    <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
</a>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .page-header-container { background: #fff; border-radius: 12px; padding: 1rem 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 1.5rem; border: 1px solid #f0f0f0; }
    .header-form-controls { display: flex; align-items: center; gap: 0.5rem; }
    .input-group-date { width: 160px !important; }
    .input-group-budget { width: 200px !important; }
</style>
@endpush

@section('content')
<div class="container-fluid px-2 px-md-3">
    <div class="page-header-container d-flex justify-content-between align-items-center mt-3">
        <div class="d-flex align-items-center report-title-box">
            <div class="ps-3 py-1">
                <h5 class="text-dark mb-0 fw-bold"><i class="fas fa-leaf text-success me-2"></i> {{ $title }}</h5>
                <div class="text-muted small mt-1">ข้อมูลปีงบประมาณ {{ $budget_year }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <form action="" method="GET" class="m-0 header-form-controls">
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="start_date" id="start_date" class="form-control border-start-0 ps-0" value="{{ $start_date }}" placeholder="วันที่เริ่ม" style="font-size: 0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-date" style="border-radius: 8px; overflow: hidden;">
                    <span class="input-group-text bg-white border-end-0 text-success"><i class="fas fa-calendar-alt"></i></span>
                    <input type="text" name="end_date" id="end_date" class="form-control border-start-0 ps-0" value="{{ $end_date }}" placeholder="วันที่สิ้นสุด" style="font-size: 0.8rem;">
                </div>
                <div class="input-group input-group-sm shadow-sm input-group-budget" style="border-radius: 8px; overflow: hidden;">
                    <select class="form-select border-end-0" name="budget_year" style="font-size: 0.8rem;">
                        @foreach ($budget_year_select as $row)
                            <option value="{{ $row->LEAVE_YEAR_ID }}" {{ (int)$budget_year === (int)$row->LEAVE_YEAR_ID ? 'selected' : '' }}>{{ $row->LEAVE_YEAR_NAME }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-success px-3" style="font-size: 0.8rem;"><i class="fas fa-search"></i> ค้นหา</button>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-5 text-center">
                    <div class="mb-4"><i class="fas fa-leaf fa-4x text-muted opacity-25"></i></div>
                    <h4 class="fw-bold">อยู่ระหว่างการพัฒนา</h4>
                    <p class="text-muted">ระบบรายงานงานแพทย์แผนไทย กำลังอยู่ในขั้นตอนการจัดสรรข้อมูลและออกแบบรายงานเพื่อเพิ่มประสิทธิภาพในการวิเคราะห์</p>
                    <div class="mt-4"><a href="{{ route('hosxp.stats.index') }}" class="btn btn-outline-secondary">กลับหน้าหลัก</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
<script>
    $(document).ready(function() {
        if (typeof flatpickr !== 'undefined') {
            const yearOffset = 543;
            const commonConfig = { locale: "th", dateFormat: "Y-m-d", altInput: true, altFormat: "j M Y", allowInput: false, 
                onReady: function(selectedDates, dateStr, instance) {
                    if (instance.altInput) {
                        const date = instance.selectedDates[0] || new Date(instance.input.value);
                        instance.altInput.value = `${date.getDate()} ${instance.l10n.months.shorthand[date.getMonth()]} ${date.getFullYear() + yearOffset}`;
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (instance.altInput && selectedDates.length > 0) {
                        const date = selectedDates[0];
                        setTimeout(() => { instance.altInput.value = `${date.getDate()} ${instance.l10n.months.shorthand[date.getMonth()]} ${date.getFullYear() + yearOffset}`; }, 10);
                    }
                }
            };
            flatpickr("#start_date", commonConfig);
            flatpickr("#end_date", commonConfig);
        }
    });
</script>
@endpush
@endsection
