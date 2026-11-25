@extends('layouts.app')

@section('title', 'ข้อมูลพื้นฐาน HOSxP - SmartData')

@push('styles')
<style>
    .hs-cat-card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #ffffff;
    }
    .hs-cat-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        text-align: left;
        padding: 11px 14px;
        border-radius: 11px;
        border: 1px solid transparent;
        background: transparent;
        transition: all 0.16s ease;
        margin-bottom: 6px;
        cursor: pointer;
    }
    .hs-cat-item:hover {
        background: #f8fafc;
        border-color: #e2e8f0;
    }
    .hs-cat-item.active {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff !important;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.22);
    }
    .hs-cat-item.active .hs-cat-sub {
        color: rgba(255, 255, 255, 0.85) !important;
    }
    .hs-cat-item.active .hs-cat-badge {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
    }
    .hs-cat-item.active .hs-cat-icon {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
    }
    .hs-cat-icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.95rem;
    }
    .hs-kpi-card {
        cursor: pointer;
        border-radius: 13px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        transition: all 0.16s ease;
    }
    .hs-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }
    .hs-kpi-card.active {
        border-width: 2px;
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.12);
    }
    .hs-kpi-card.active[data-tone="success"] { border-color: #10b981; background: #ecfdf5; }
    .hs-kpi-card.active[data-tone="primary"] { border-color: #2563eb; background: #eff6ff; }
    .hs-kpi-card.active[data-tone="secondary"] { border-color: #64748b; background: #f8fafc; }
    .hs-kpi-card.active[data-tone="danger"] { border-color: #ef4444; background: #fef2f2; }
    .hs-kpi-card.active[data-tone="warning"] { border-color: #f59e0b; background: #fffbeb; }
    .hs-kpi-card.active[data-tone="info"] { border-color: #06b6d4; background: #ecfeff; }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">
    <!-- Top Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #eff6ff; color: #2563eb;">
                    <i class="fas fa-database fs-5"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">ข้อมูลพื้นฐาน HOSxP</h5>
                    <div class="text-muted small">ตรวจสอบความถูกต้องและครบถ้วนของข้อมูลแพทย์, ค่ารักษาพยาบาล, ข้อมูลยา, Lab, สิทธิการรักษา และรหัสมาตรฐาน</div>
                </div>
            </div>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2 align-self-start align-self-md-center">
                <i class="fas fa-circle me-1" style="font-size: 0.55rem;"></i> เชื่อมต่อ HOSxP MySQL Live
            </span>
        </div>
    </div>

    <div class="row g-3">
        <!-- Left Sidebar Categories -->
        <div class="col-lg-3">
            <div class="hs-cat-card shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                    <span class="fw-bold text-dark small"><i class="fas fa-table-cells-large me-1.5 text-primary"></i> หมวดหมู่ข้อมูลพื้นฐาน</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-1" style="font-size: 0.72rem;">{{ count($categories) }} หมวด</span>
                </div>

                @foreach($categories as $key => $cat)
                    <button type="button"
                            class="hs-cat-item {{ $activeTab === $key ? 'active' : '' }}"
                            data-tab="{{ $key }}"
                            onclick="selectHosxpTab('{{ $key }}', this)">
                        <div class="d-flex align-items-center gap-2.5 min-w-0">
                            <div class="hs-cat-icon" style="background: {{ $cat['color'] }}15; color: {{ $cat['color'] }};">
                                <i class="fas {{ $cat['icon'] }}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-truncate" style="font-size: 0.86rem;">{{ $cat['title'] }}</div>
                                <div class="hs-cat-sub text-muted text-truncate" style="font-size: 0.72rem;">{{ $cat['subtitle'] }}</div>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border rounded-pill hs-cat-badge font-monospace" style="font-size: 0.72rem;">
                            {{ number_format($counts[$key] ?? 0) }}
                        </span>
                    </button>
                @endforeach

                <div class="mt-3 p-3 rounded-3 border" style="background: #f8fafc;">
                    <div class="fw-bold text-primary small mb-1">
                        <i class="fas fa-shield-halved me-1"></i> ความพร้อมก่อนส่งออก
                    </div>
                    <div class="text-muted" style="font-size: 0.75rem; line-height: 1.45;">
                        ตรวจสอบข้อมูลให้ถูกต้องครบถ้วนเพื่อความพร้อมในการส่งออกข้อมูล 43 แฟ้ม และการส่งเคลม FDH MOPH / e-Claim
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Main Content -->
        <div class="col-lg-9">
            <!-- 4 KPI Cards -->
            <div class="row g-2 mb-3" id="hsKpiCards"></div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div class="fw-bold text-primary d-flex align-items-center gap-2" id="hsTableTitle">
                        <i class="fas fa-table"></i>
                        <span>กำลังโหลดข้อมูล...</span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex align-items-center gap-1 small text-muted">
                            <span>แสดง</span>
                            <select id="hsPerPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="15" selected>15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>รายการ</span>
                        </div>
                        <div class="input-group input-group-sm" style="width: 230px;">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="hsSearchInput" class="form-control border-start-0" placeholder="ค้นหา...">
                        </div>
                        <a href="#" id="hsExportBtn" class="btn btn-sm btn-outline-success rounded-pill px-3">
                            <i class="fas fa-file-csv me-1"></i> Export CSV
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                            <thead class="bg-light">
                                <tr id="hsTableHead" class="text-muted" style="font-size: 0.77rem;"></tr>
                            </thead>
                            <tbody id="hsTableBody">
                                <tr><td class="text-center py-5 text-muted">กำลังโหลดข้อมูล...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white py-2.5 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <div class="text-muted small" id="hsPaginationInfo">-</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="hsPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let hsTab = @json($activeTab);
    let hsFilter = 'card1';
    let hsPage = 1;
    let hsSearchTimer = null;

    function hsEsc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function selectHosxpTab(tab, btnEl) {
        hsTab = tab;
        hsFilter = 'card1';
        hsPage = 1;
        document.getElementById('hsSearchInput').value = '';
        document.querySelectorAll('.hs-cat-item').forEach(el => el.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');
        loadHosxpData();
    }

    function selectHosxpFilter(filterKey) {
        hsFilter = filterKey;
        hsPage = 1;
        loadHosxpData();
    }

    function goToHosxpPage(p) {
        hsPage = p;
        loadHosxpData();
    }

    function loadHosxpData() {
        const search = document.getElementById('hsSearchInput').value.trim();
        const perPage = document.getElementById('hsPerPage').value;
        const url = `{{ url('/hosxp_setting/data') }}/${encodeURIComponent(hsTab)}?filter=${encodeURIComponent(hsFilter)}&search=${encodeURIComponent(search)}&page=${hsPage}&per_page=${perPage}`;
        const exportUrl = `{{ url('/hosxp_setting/export') }}/${encodeURIComponent(hsTab)}?filter=${encodeURIComponent(hsFilter)}&search=${encodeURIComponent(search)}`;
        document.getElementById('hsExportBtn').href = exportUrl;

        document.getElementById('hsTableBody').innerHTML = '<tr><td colspan="12" class="text-center py-5 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>กำลังโหลดข้อมูล...</td></tr>';

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                renderCards(res.cards || []);
                renderTable(res);
                renderPagination(res);
            })
            .catch(() => {
                document.getElementById('hsTableBody').innerHTML = '<tr><td colspan="12" class="text-center py-4 text-danger">ไม่สามารถโหลดข้อมูลได้</td></tr>';
            });
    }

    function renderCards(cards) {
        const toneColors = {
            success: '#10b981',
            primary: '#2563eb',
            secondary: '#64748b',
            danger: '#ef4444',
            warning: '#d97706',
            info: '#0891b2'
        };
        let html = '';
        cards.forEach(c => {
            const isActive = c.key === hsFilter;
            const color = toneColors[c.tone] || '#2563eb';
            html += `
                <div class="col-6 col-xl-3">
                    <div class="hs-kpi-card p-3 h-100 ${isActive ? 'active' : ''}" data-tone="${hsEsc(c.tone)}" onclick="selectHosxpFilter('${hsEsc(c.key)}')">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; background: ${color}18; color: ${color};">
                                <i class="fas ${hsEsc(c.icon)}"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-muted text-truncate" style="font-size: 0.77rem;">${hsEsc(c.label)}</div>
                                <div class="fw-bold fs-5 lh-sm" style="color: ${color};">
                                    ${Number(c.count || 0).toLocaleString()} <small class="text-muted fw-normal" style="font-size: 0.75rem;">${hsEsc(c.unit)}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        document.getElementById('hsKpiCards').innerHTML = html;
    }

    function renderTable(res) {
        document.getElementById('hsTableTitle').innerHTML = `<i class="fas fa-table"></i> <span>${hsEsc(res.table_title)} (จำนวน ${Number(res.total || 0).toLocaleString()} รายการ)</span>`;

        const cols = res.columns || [];
        document.getElementById('hsTableHead').innerHTML = cols.map(c => `<th class="text-nowrap">${hsEsc(c)}</th>`).join('');

        const rows = res.rows || [];
        if (rows.length === 0) {
            document.getElementById('hsTableBody').innerHTML = `<tr><td colspan="${cols.length}" class="text-center py-5 text-muted">ไม่พบข้อมูลที่ตรงกับเงื่อนไข</td></tr>`;
            return;
        }

        const keys = res.export_keys || [];
        let bodyHtml = '';
        rows.forEach(row => {
            bodyHtml += '<tr>';
            keys.forEach((k, idx) => {
                const val = row[k] ?? '-';
                if (k === 'cid') {
                    const cls = row.cid_valid ? 'text-dark fw-semibold' : 'text-danger fw-bold';
                    bodyHtml += `<td class="font-monospace text-nowrap ${cls}">${hsEsc(val)}</td>`;
                } else if (k === 'licenseno') {
                    const cls = row.lic_valid ? 'text-success fw-semibold' : 'text-danger fw-bold';
                    bodyHtml += `<td class="font-monospace text-nowrap ${cls}">${hsEsc(val)}</td>`;
                } else if (k === 'active') {
                    const ok = (val === 'Active' || val === 'จับคู่แล้ว');
                    const badge = ok ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary';
                    bodyHtml += `<td><span class="badge rounded-pill ${badge} px-2.5 py-1">${hsEsc(val)}</span></td>`;
                } else if (k === 'audit_note') {
                    const icon = row.audit_ok
                        ? '<span class="badge rounded-pill bg-success bg-opacity-10 text-success px-2 py-1" title="' + hsEsc(val) + '"><i class="fas fa-check-circle me-1"></i>' + hsEsc(val) + '</span>'
                        : '<span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-2 py-1" title="' + hsEsc(val) + '"><i class="fas fa-exclamation-circle me-1"></i>' + hsEsc(val) + '</span>';
                    bodyHtml += `<td class="text-nowrap">${icon}</td>`;
                } else if (idx === 0) {
                    bodyHtml += `<td class="font-monospace text-muted text-nowrap">${hsEsc(val)}</td>`;
                } else {
                    bodyHtml += `<td>${hsEsc(val)}</td>`;
                }
            });
            bodyHtml += '</tr>';
        });
        document.getElementById('hsTableBody').innerHTML = bodyHtml;
    }

    function renderPagination(res) {
        const total = Number(res.total || 0);
        const page = Number(res.page || 1);
        const lastPage = Number(res.last_page || 1);
        const perPage = Number(res.per_page || 15);
        const start = total === 0 ? 0 : ((page - 1) * perPage + 1);
        const end = Math.min(total, page * perPage);

        document.getElementById('hsPaginationInfo').textContent = `แสดง ${start.toLocaleString()} - ${end.toLocaleString()} จากทั้งหมด ${total.toLocaleString()} รายการ`;

        let html = '';
        html += `<li class="page-item ${page <= 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="goToHosxpPage(${page - 1})">ก่อนหน้า</a></li>`;

        const minP = Math.max(1, page - 2);
        const maxP = Math.min(lastPage, page + 2);
        for (let p = minP; p <= maxP; p++) {
            html += `<li class="page-item ${p === page ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="goToHosxpPage(${p})">${p}</a></li>`;
        }

        html += `<li class="page-item ${page >= lastPage ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="goToHosxpPage(${page + 1})">ถัดไป</a></li>`;
        document.getElementById('hsPagination').innerHTML = html;
    }

    document.getElementById('hsSearchInput').addEventListener('input', function() {
        clearTimeout(hsSearchTimer);
        hsSearchTimer = setTimeout(() => {
            hsPage = 1;
            loadHosxpData();
        }, 300);
    });

    document.getElementById('hsPerPage').addEventListener('change', function() {
        hsPage = 1;
        loadHosxpData();
    });

    document.addEventListener('DOMContentLoaded', loadHosxpData);
</script>
@endpush
