@extends('layouts.app')

@section('title', 'SmartData | ข้อมูลและสถิติ')

@section('content')
<div class="container-fluid px-2 px-md-3">
    <!-- Header -->
    <div class="d-flex align-items-center mb-3"> <!-- Reduced mb-4 to mb-3 -->
        <div class="bg-gradient-primary-custom text-white p-2 rounded-3 shadow-sm me-3"> <!-- Reduced p-3 to p-2 -->
            <i class="fas fa-chart-bar fa-lg"></i> <!-- Reduced fa-2x to fa-lg -->
        </div>
        <div>
            <h4 class="fw-bold mb-0">ข้อมูลและสถิติ</h4>
            <p class="text-muted mb-0 small">ศูนย์รวมรายงานและสถิติต่างๆ จากระบบ HOSxP</p>
        </div>
    </div>

    <div class="row">
        <!-- รายงานทั่วไป -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;"> <!-- Reduced radius 15 to 12 -->
                <div class="card-body p-3"> <!-- Reduced from p-4 -->
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-blue p-2 rounded-3 me-2">
                            <i class="fas fa-file-medical text-primary"></i>
                        </div>
                        <h6 class="fw-bold mb-0">รายงานทั่วไป</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="{{ route('hosxp.stats.top20_opd') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงาน 20 อันดับโรค ผู้ป่วยนอก</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="{{ route('hosxp.stats.top20_ipd') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงาน 20 อันดับโรค ผู้ป่วยใน</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="{{ route('hosxp.stats.group_506') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงานกลุ่มโรคที่ต้องเฝ้าระวัง (รง.506)</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายงานรับ-ส่ง Refer -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-teal p-2 rounded-3 me-2">
                            <i class="fas fa-ambulance text-success"></i>
                        </div>
                        <h6 class="fw-bold mb-0">รายงานรับ-ส่ง Refer</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="{{ route('hosxp.stats.refer_out') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงานผู้ป่วยส่งต่อ Refer Out</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="{{ route('hosxp.stats.refer_out_4h') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงานผู้ป่วยส่งต่อ Refer Out ภายใน 4 ชม.หลัง Admit</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="{{ route('hosxp.stats.refer_out_24h') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงานผู้ป่วยส่งต่อ Refer Out ภายใน 24 ชม.หลัง Admit</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="{{ route('hosxp.stats.refer_out_top20') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงานผู้ป่วยส่งต่อ Refer Out 20 อันดับโรค (Primary Diagnosis)</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- รายงานการเสียชีวิต -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-red p-2 rounded-3 me-2">
                            <i class="fas fa-cross text-danger"></i>
                        </div>
                        <h6 class="fw-bold mb-0">รายงานการเสียชีวิต</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="{{ route('hosxp.stats.death') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงานการเสียชีวิต</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="{{ route('hosxp.stats.death_top20') }}" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center">
                            <span class="small">รายงาน 20 อันดับโรค การเสียชีวิต</span>
                            <i class="fas fa-chevron-right smaller text-muted" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <!-- ผู้ป่วยนอก (OPD) -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-blue p-2 rounded-3 me-2">
                            <i class="fas fa-stethoscope text-primary"></i>
                        </div>
                        <h6 class="fw-bold mb-0">ผู้ป่วยนอก (OPD)</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงานข้อมูลบริการผู้ป่วยนอก</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงานการให้บริการแพทย์ทางไกล Telehealth</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงานระยะเวลารอคอยผู้ป่วยนอก</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ผู้ป่วยใน (IPD) -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-teal p-2 rounded-3 me-2">
                            <i class="fas fa-bed text-success"></i>
                        </div>
                        <h6 class="fw-bold mb-0">ผู้ป่วยใน (IPD)</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงานข้อมูลบริการผู้ป่วยใน</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงานจำนวนผู้ป่วยในแยกระดับความรุนแรง</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงาน Re-Admit ภายใน 28 วันด้วยโรคเดิม</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- อุบัติเหตุ-ฉุกเฉิน (ER) -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-red p-2 rounded-3 me-2">
                            <i class="fas fa-ambulance text-danger"></i>
                        </div>
                        <h6 class="fw-bold mb-0">อุบัติเหตุ-ฉุกเฉิน (ER)</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงานข้อมูลบริการผู้ป่วยอุบัติเหตุ-ฉุกเฉิน</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงานจำนวนผู้ป่วยอุบัติเหตุ-ฉุกเฉิน แยกระดับความรุนแรง</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงานผู้ป่วยรอ Admit ที่ ER เกิน 2 ชั่วโมง</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงาน Re-Visit ใน 48 ชม. ด้วยโรคเดิม</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">รายงาน 20 อันดับโรค งานอุบัติเหตุ-ฉุกเฉิน</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Additional Services Row -->
    <div class="row mt-3">
        <!-- กายภาพบำบัด -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-orange p-2 rounded-3 me-2">
                            <i class="fas fa-running text-warning"></i>
                        </div>
                        <h6 class="fw-bold mb-0">กายภาพบำบัด</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">--- รายงานรอดำเนินการ ---</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- แพทย์แผนไทย -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-green p-2 rounded-3 me-2">
                            <i class="fas fa-leaf text-success"></i>
                        </div>
                        <h6 class="fw-bold mb-0">แพทย์แผนไทย</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">--- รายงานรอดำเนินการ ---</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ทันตกรรม -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-cyan p-2 rounded-3 me-2">
                            <i class="fas fa-tooth text-info"></i>
                        </div>
                        <h6 class="fw-bold mb-0">ทันตกรรม</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">--- รายงานรอดำเนินการ ---</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- เภสัชกรรม -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-purple p-2 rounded-3 me-2">
                            <i class="fas fa-capsules text-purple"></i>
                        </div>
                        <h6 class="fw-bold mb-0">เภสัชกรรม</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">--- รายงานรอดำเนินการ ---</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- คลินิกโรคเรื้อรัง -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-orange p-2 rounded-3 me-2">
                            <i class="fas fa-heartbeat text-warning"></i>
                        </div>
                        <h6 class="fw-bold mb-0">คลินิกโรคเรื้อรัง</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">--- รายงานรอดำเนินการ ---</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- งานเชิงรุก PCU -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-pastel-yellow p-2 rounded-3 me-2">
                            <i class="fas fa-home text-yellow"></i>
                        </div>
                        <h6 class="fw-bold mb-0">งานเชิงรุก PCU</h6>
                    </div>
                    <div class="list-group list-group-flush mt-2">
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 d-flex justify-content-between align-items-center text-muted">
                            <span class="small">--- รายงานรอดำเนินการ ---</span>
                            <i class="fas fa-chevron-right smaller" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-pastel-blue { background-color: #e0f2fe; }
    .bg-pastel-teal { background-color: #f0fdfa; }
    .bg-pastel-red { background-color: #fef2f2; }
    .bg-pastel-orange { background-color: #fff7ed; }
    .bg-pastel-green { background-color: #f0fdf4; }
    .bg-pastel-cyan { background-color: #ecfeff; }
    .bg-pastel-purple { background-color: #faf5ff; }
    .bg-pastel-yellow { background-color: #fefce8; }
    .text-purple { color: #a855f7 !important; }
    .text-yellow { color: #eab308 !important; }
    .list-group-item {
        font-size: 0.95rem;
    }
    .list-group-item:hover {
        background-color: #f8f9fc !important;
        transform: translateX(5px);
        transition: all 0.2s ease;
    }
</style>
@endsection
