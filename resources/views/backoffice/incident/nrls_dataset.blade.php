@extends('layouts.app')

@section('title', 'SmartData | NRLS Dataset')

@section('topbar_actions')
    <a href="{{ route('backoffice.incident.index') }}" class="btn btn-light btn-sm fw-bold shadow-sm"
        style="border-radius: 10px; padding: 5px 15px; color: #4e73df;">
        <i class="fas fa-chevron-left me-1"></i> ย้อนกลับ
    </a>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
    <div class="container-fluid px-2 px-md-4">
        <!-- Header Box -->
        <div class="page-header-container bg-white rounded-3 shadow-sm border p-4 mb-4 mt-3">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h5 class="text-dark mb-0 fw-bold">
                        <i class="fas fa-file-invoice text-primary me-2"></i>
                        ข้อมูล Dataset รายเดือน ส่ง NRLS
                    </h5>
                    <div class="text-primary small fw-bold mt-1">
                        <i class="fas fa-calendar-alt me-1"></i> ช่วงวันที่: {{ DateThai($start_date) }} ถึง {{ DateThai($end_date) }}
                    </div>
                </div>
                <div class="col-lg-6 d-flex justify-content-end align-items-center gap-2">
                    <form action="" method="GET" class="m-0 d-flex gap-2 align-items-center">
                        <input type="text" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $start_date }}" style="width: 140px;">
                        <input type="text" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $end_date }}" style="width: 140px;">
                        <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-search me-1"></i>ค้นหา</button>
                    </form>
                    <a href="{{ route('backoffice.incident.nrls_dataset_export') }}" class="btn btn-success btn-sm px-3 shadow-sm"><i class="fas fa-file-export me-1"></i>ส่งออกข้อมูล (.txt)</a>
                </div>
            </div>
        </div>

        <!-- Data Cards -->
        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-primary text-white py-3">
                <h6 class="fw-bold mb-0">รายการรหัสโครงสร้างตัวชี้วัด (Dataset)</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover align-middle mb-0" style="font-size: 0.9rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 15%;">รหัสตัวชี้วัด</th>
                            <th style="width: 65%;">รายละเอียดตัวชี้วัด</th>
                            <th class="text-center" style="width: 20%;">ค่าที่ได้</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4 fw-bold">rr001</td>
                            <td>จำนวนวันนอนรวมของผู้ป่วยในทั้งหมด</td>
                            <td class="text-center fw-bold text-primary">{{ $rr001[0]->rr001 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr003</td>
                            <td>จำนวนครั้งการรับบริการผู้ป่วยนอกนอกเวลา</td>
                            <td class="text-center fw-bold text-primary">{{ $rr003[0]->rr003 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr004</td>
                            <td>จำนวนครั้งการรับบริการผู้ป่วยนอกในเวลา</td>
                            <td class="text-center fw-bold text-primary">{{ $rr004[0]->rr004 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr005</td>
                            <td>จำนวนครั้งการรับบริการผู้ป่วยในนอกเวลา</td>
                            <td class="text-center fw-bold text-primary">{{ $rr005[0]->rr005 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr006</td>
                            <td>จำนวนครั้งการรับบริการผู้ป่วยในในเวลา</td>
                            <td class="text-center fw-bold text-primary">{{ $rr006[0]->rr006 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr007</td>
                            <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 1)</td>
                            <td class="text-center fw-bold text-primary">{{ $rr007[0]->rr007 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr008</td>
                            <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 3)</td>
                            <td class="text-center fw-bold text-primary">{{ $rr008[0]->rr008 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr009</td>
                            <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 4)</td>
                            <td class="text-center fw-bold text-primary">{{ $rr009[0]->rr009 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr010</td>
                            <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 5)</td>
                            <td class="text-center fw-bold text-primary">{{ $rr010[0]->rr010 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr011</td>
                            <td>จำนวนครั้งการส่งต่อผู้ป่วยนอกทั้งหมด</td>
                            <td class="text-center fw-bold text-primary">{{ $rr011[0]->rr011 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr015</td>
                            <td>จำนวนครั้งการส่งต่อผู้ป่วยในระดับวิกฤต (Triage level 3)</td>
                            <td class="text-center fw-bold text-primary">{{ $rr015[0]->rr015 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr016</td>
                            <td>จำนวนครั้งการส่งต่อผู้ป่วยในระดับวิกฤต (Triage level 4)</td>
                            <td class="text-center fw-bold text-primary">{{ $rr016[0]->rr016 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr022</td>
                            <td>จำนวนรายการใบสั่งยากลุ่มเสี่ยงสูญเสียชีวิต/ทุพพลภาพ</td>
                            <td class="text-center fw-bold text-primary">{{ $rr022[0]->rr022 ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="ps-4 fw-bold">rr024</td>
                            <td>จำนวนผู้ป่วยนอกฉุกเฉินระดับวิกฤต (Triage level 2)</td>
                            <td class="text-center fw-bold text-primary">{{ $rr024[0]->rr024 ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                    allowInput: false,
                    onReady: function(selectedDates, dateStr, instance) {
                        if (instance.altInput) {
                            const originalValue = instance.altInput.value;
                            if (originalValue) {
                                const date = instance.selectedDates[0] || new Date(instance.input.value);
                                if (date && !isNaN(date.getTime())) {
                                    const day = date.getDate();
                                    const month = instance.l10n.months.shorthand[date.getMonth()];
                                    const year = date.getFullYear() + yearOffset;
                                    instance.altInput.value = `${day} ${month} ${year}`;
                                }
                            }
                        }
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        if (instance.altInput && selectedDates.length > 0) {
                            const date = selectedDates[0];
                            setTimeout(() => {
                                const day = date.getDate();
                                const month = instance.l10n.months.shorthand[date.getMonth()];
                                const year = date.getFullYear() + yearOffset;
                                instance.altInput.value = `${day} ${month} ${year}`;
                            }, 10);
                        }
                    }
                };

                flatpickr("#start_date", commonConfig);
                flatpickr("#end_date", commonConfig);
            }
        });
    </script>
@endpush
