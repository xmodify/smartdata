<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ไม่พบข้อมูลคิว - โรงพยาบาลหัวตะพาน</title>
    
    <!-- Local Assets จากในโปรเจกต์ (ไม่ใช้ CDN) -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">

    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background-color: #f4f7f6; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100 p-3">
    <div class="card border-0 shadow-sm p-4 text-center rounded-4" style="max-width: 400px;">
        <div class="text-warning mb-3">
            <i class="fa-solid fa-triangle-exclamation fa-4x"></i>
        </div>
        <h5 class="fw-bold mb-2">ไม่พบข้อมูลคิวรับบริการ</h5>
        <p class="text-muted small mb-3">ไม่พบหมายเลข VN: <strong>{{ $vn }}</strong> ในระบบของวันนี้ หรือคิวได้รับการรับบริการเสร็จสิ้นแล้ว</p>
        <div class="alert alert-light border small text-secondary">
            หากมีข้อสงสัย กรุณาติดต่อช่องบริการคัดกรอง หรือประชาสัมพันธ์โรงพยาบาล
        </div>
    </div>
</body>
</html>
