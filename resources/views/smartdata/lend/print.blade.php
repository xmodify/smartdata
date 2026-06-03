<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบยืมอุปกรณ์ #{{ $transaction->id }}</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap');

        * { font-family: 'Sarabun', sans-serif; }
        body { background: #f5f5f5; margin: 0; padding: 20px; }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            padding: 20mm 18mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
        }

        .hospital-header { text-align: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #0268c7; }
        .hospital-name { font-size: 1.3rem; font-weight: 700; color: #0268c7; }
        .doc-title { font-size: 1.1rem; font-weight: 700; margin-top: 0.5rem; }
        .doc-no { font-size: 0.85rem; color: #6c757d; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .info-table td { padding: 6px 8px; font-size: 0.9rem; vertical-align: top; }
        .info-table .label { font-weight: 700; white-space: nowrap; width: 130px; color: #374151; }
        .info-table .value { border-bottom: 1px dotted #aaa; }

        .item-table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
        .item-table th { background: #0268c7; color: #fff; padding: 8px 10px; font-size: 0.85rem; text-align: left; }
        .item-table td { padding: 8px 10px; font-size: 0.9rem; border-bottom: 1px solid #e5e7eb; }
        .item-table tr:nth-child(even) td { background: #f8fafc; }

        .conditions { background: #f8fafc; border-radius: 8px; padding: 12px 16px; margin: 1.5rem 0; font-size: 0.82rem; color: #374151; }
        .conditions h6 { font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; }
        .conditions ol { margin: 0; padding-left: 1.2rem; }
        .conditions li { margin-bottom: 4px; }

        .sign-section { display: flex; justify-content: space-around; margin-top: 3rem; }
        .sign-box { text-align: center; width: 42%; }
        .sign-line { border-top: 1px solid #333; margin: 2.5rem 1rem 0.3rem; }
        .sign-label { font-size: 0.82rem; color: #6c757d; }

        .deposit-box { background: linear-gradient(135deg, #fffbeb, #fef3c7); border-radius: 10px; padding: 12px 16px; border: 1.5px solid #f59e0b; margin: 1rem 0; }
        .status-returned { background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; display: inline-block; }

        .btn-print { position: fixed; bottom: 30px; right: 30px; z-index: 999; border-radius: 50%; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border: none; background: #0268c7; color: #fff; cursor: pointer; }

        @media print {
            body { background: #fff; padding: 0; }
            .print-page { box-shadow: none; margin: 0; padding: 15mm 15mm; width: 100%; }
            .btn-print { display: none !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<button class="btn-print btn-print" onclick="window.print()" title="พิมพ์">
    <i class="fas fa-print"></i>
</button>

<div class="no-print text-center mb-3">
    <a href="{{ route('lend.index') }}" class="btn btn-outline-secondary btn-sm me-2">
        ← กลับ
    </a>
    <button class="btn btn-primary btn-sm" onclick="window.print()">
        🖨️ พิมพ์ใบยืม
    </button>
</div>

<div class="print-page">

    {{-- หัวกระดาษ --}}
    <div class="hospital-header">
        <div class="hospital-name">
            โรงพยาบาลหัวตะพาน
        </div>
        <div class="doc-title">ใบยืมอุปกรณ์/ครุภัณฑ์ทางการแพทย์</div>
        <div class="doc-no">เลขที่: LND-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    {{-- สถานะ --}}
    @if($transaction->status === 'returned')
    <div class="text-end mb-2">
        <span class="status-returned">✓ คืนแล้ว {{ $transaction->return_date?->format('d/m/Y') }}</span>
    </div>
    @endif

    {{-- ข้อมูลผู้ยืม --}}
    <h6 class="fw-bold mb-2" style="color:#0268c7;font-size:0.9rem;">
        <span style="border-bottom:2px solid #0268c7;padding-bottom:2px;">ข้อมูลผู้ยืม</span>
    </h6>
    <table class="info-table mb-3">
        <tr>
            <td class="label">ชื่อ-สกุล</td>
            <td class="value">{{ $transaction->borrower_name }}</td>
            @if($transaction->hn)
            <td class="label" style="padding-left:20px;">HN</td>
            <td class="value">{{ $transaction->hn }}</td>
            @endif
        </tr>
        <tr>
            <td class="label">ที่อยู่</td>
            <td class="value" colspan="{{ $transaction->hn ? 3 : 1 }}">{{ $transaction->borrower_address ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">เบอร์โทรศัพท์</td>
            <td class="value">{{ $transaction->borrower_phone ?: '-' }}</td>
            <td class="label" style="padding-left:20px;">วันที่ยืม</td>
            <td class="value">{{ $transaction->borrow_date?->locale('th')->translatedFormat('j F Y') }}</td>
        </tr>
        <tr>
            <td class="label">กำหนดคืน</td>
            <td class="value">{{ $transaction->due_date ? $transaction->due_date->locale('th')->translatedFormat('j F Y') : 'ไม่กำหนด' }}</td>
            <td class="label" style="padding-left:20px;">ผู้จ่าย</td>
            <td class="value">{{ $transaction->creator->name ?? '-' }}</td>
        </tr>
    </table>

    {{-- รายการอุปกรณ์ --}}
    <h6 class="fw-bold mb-2" style="color:#0268c7;font-size:0.9rem;">
        <span style="border-bottom:2px solid #0268c7;padding-bottom:2px;">รายการอุปกรณ์</span>
    </h6>
    <table class="item-table">
        <thead>
            <tr>
                <th style="width:40px;">ที่</th>
                <th>รายการ</th>
                <th style="width:70px;text-align:center;">จำนวน</th>
                <th>หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><strong>{{ $transaction->lendItem->name ?? '-' }}</strong></td>
                <td style="text-align:center;">{{ $transaction->qty }}</td>
                <td>{{ $transaction->note ?: '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ค่ามัดจำ --}}
    @if($transaction->deposit_amount)
    <div class="deposit-box">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>ค่ามัดจำ:</strong>
                <span style="font-size:1.1rem;font-weight:700;color:#d97706;margin-left:8px;">
                    {{ number_format($transaction->deposit_amount, 2) }} บาท
                </span>
            </div>
            @if($transaction->deposit_receipt_no)
            <div style="font-size:0.85rem;color:#6c757d;">
                ใบเสร็จ: <strong>{{ $transaction->deposit_receipt_no }}</strong>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- เงื่อนไข --}}
    <div class="conditions">
        <h6>เงื่อนไขการยืมอุปกรณ์</h6>
        <ol>
            <li>ผู้ยืมต้องรักษาอุปกรณ์ให้อยู่ในสภาพดี และคืนภายในกำหนด</li>
            <li>หากอุปกรณ์ชำรุดหรือสูญหาย ผู้ยืมต้องรับผิดชอบค่าเสียหาย</li>
            <li>กรณีไม่คืนตามกำหนด โรงพยาบาลขอสงวนสิทธิ์ดำเนินการตามที่เห็นสมควร</li>
            <li>ค่ามัดจำจะได้รับคืนเมื่อส่งคืนอุปกรณ์ครบถ้วนสมบูรณ์</li>
        </ol>
    </div>

    {{-- ลายเซ็น --}}
    <div class="sign-section">
        <div class="sign-box">
            <div class="sign-line"></div>
            <div class="fw-bold" style="font-size:0.9rem;">ลายเซ็นผู้ยืม</div>
            <div class="sign-label">({{ $transaction->borrower_name }})</div>
            <div class="sign-label mt-1">วันที่ ........../........../..........&nbsp;&nbsp;</div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div class="fw-bold" style="font-size:0.9rem;">ลายเซ็นผู้จ่าย</div>
            <div class="sign-label">({{ $transaction->creator->name ?? '................................' }})</div>
            <div class="sign-label mt-1">วันที่ ........../........../..........&nbsp;&nbsp;</div>
        </div>
    </div>

    {{-- ข้อมูลการคืน (กรณีคืนแล้ว) --}}
    @if($transaction->status === 'returned')
    <div style="margin-top:2rem;padding-top:1rem;border-top:1px dashed #ccc;">
        <h6 class="fw-bold mb-2" style="color:#10b981;font-size:0.9rem;">ข้อมูลการคืน</h6>
        <table class="info-table">
            <tr>
                <td class="label">วันที่คืน</td>
                <td class="value">{{ $transaction->return_date?->locale('th')->translatedFormat('j F Y') }} เวลา {{ $transaction->return_time ?? '-' }}</td>
                <td class="label" style="padding-left:20px;">ผู้รับคืน</td>
                <td class="value">{{ $transaction->returner->name ?? '-' }}</td>
            </tr>
            @if($transaction->returned_note)
            <tr>
                <td class="label">หมายเหตุ</td>
                <td class="value" colspan="3">{{ $transaction->returned_note }}</td>
            </tr>
            @endif
        </table>
        <div class="sign-section mt-3">
            <div class="sign-box">
                <div class="sign-line"></div>
                <div class="fw-bold" style="font-size:0.9rem;">ลายเซ็นผู้คืน</div>
                <div class="sign-label">({{ $transaction->borrower_name }})</div>
            </div>
            <div class="sign-box">
                <div class="sign-line"></div>
                <div class="fw-bold" style="font-size:0.9rem;">ลายเซ็นผู้รับคืน</div>
                <div class="sign-label">({{ $transaction->returner->name ?? '................................' }})</div>
            </div>
        </div>
    </div>
    @endif

    <div style="position:absolute;bottom:12mm;left:18mm;right:18mm;text-align:center;font-size:0.72rem;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:6px;">
        พิมพ์โดยระบบ SmartData | {{ now()->locale('th')->translatedFormat('j F Y H:i') }}
    </div>
</div>

<link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
<script>
    // Auto print on load (optional - comment out if not wanted)
    // window.onload = () => window.print();
</script>
</body>
</html>
