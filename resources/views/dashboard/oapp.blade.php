<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ตารางนัดหมายผู้ป่วย</title>
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    <!-- CSS Stylesheets -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&family=Nunito:wght@400;600;700;800&display=swap');

        body {
            font-family: 'Sarabun', 'Nunito', sans-serif;
            background-color: #f3f4f6;
            color: #374151;
            padding: 2rem 1rem;
        }

        .main-card {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            margin-bottom: 2rem;
        }

        .header-section {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            padding: 1.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.2rem;
            border-bottom: none;
        }

        .header-title h4 {
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }

        .header-subtitle {
            color: #bfdbfe;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .date-label {
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .clinic-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        .clinic-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            border-left: 6px solid var(--clinic-color, #2563eb);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .clinic-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px var(--clinic-shadow-color, rgba(37, 99, 235, 0.08));
            border-color: var(--clinic-border-hover, #cbd5e1);
        }

        .clinic-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .clinic-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: #1e293b;
            margin: 0;
            line-height: 1.4;
            max-width: 80%;
        }

        .clinic-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: var(--clinic-color-light, #eff6ff);
            color: var(--clinic-color, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .count-display {
            margin: 1.2rem 0;
            text-align: center;
        }

        .count-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--clinic-color, #2563eb);
            line-height: 1;
            transition: all 0.2s ease-in-out;
        }

        .count-unit {
            font-size: 0.95rem;
            color: #64748b;
            font-weight: 600;
            margin-left: 0.25rem;
        }

        .doctor-selector-wrapper {
            margin-top: 1rem;
            border-top: 1px dashed #e2e8f0;
            padding-top: 1rem;
        }

        .doctor-select {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
            width: 100%;
            background-color: #f8fafc;
            color: #334155;
            font-weight: 600;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s;
        }

        .doctor-select:focus {
            border-color: var(--clinic-color, #2563eb);
            box-shadow: 0 0 0 2px var(--clinic-shadow-color, rgba(37, 99, 235, 0.1));
        }

        .doctor-list-preview {
            margin-top: 0.8rem;
            font-size: 0.8rem;
            color: #64748b;
            max-height: 80px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .doctor-item-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .doctor-item-row:last-child {
            border-bottom: none;
        }

        .footer {
            text-align: center;
            margin-top: 3rem;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        /* Flatpickr Custom Styling for B.E. Offset */
        .flatpickr-today-button {
            text-align: center;
            padding: 8px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            font-weight: bold;
            font-size: 0.85rem;
            border-bottom-left-radius: 5px;
            border-bottom-right-radius: 5px;
        }
        .flatpickr-today-button:hover {
            background: #1d4ed8;
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 768px) {
            body {
                padding: 1rem 0.5rem;
            }
            .header-section {
                padding: 1.2rem 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 0.8rem;
            }
            .clinic-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 1rem;
            }
            .clinic-card {
                padding: 1.2rem 1rem;
            }
        }
    </style>
</head>
<body>

<div class="container-lg">
    <!-- Main Card -->
    <div class="main-card">
        <!-- Header -->
        <div class="header-section">
            <div class="header-title">
                <h4><i class="fa-solid fa-calendar-days me-2" style="color: #60a5fa;"></i>Dashboard - ตารางนัดหมายผู้ป่วย</h4>
                <div class="header-subtitle">
                    แสดงยอดผู้ป่วยนัดหมายตามคลินิกและรายแพทย์ ประจำวันที่ {{ DateThai($date) }}
                </div>
            </div>

            <!-- Date Selector Form -->
            <div>
                <form action="" method="GET" class="d-flex align-items-center gap-2 m-0">
                    <label class="date-label mb-0 text-nowrap">เลือกวันที่นัด:</label>
                    <div class="input-group input-group-sm shadow-sm" style="border-radius: 8px; overflow: hidden; width: 170px;">
                        <span class="input-group-text bg-white border-end-0 text-primary border-0"><i class="fas fa-calendar-alt"></i></span>
                        <input type="text" id="oapp_date" class="form-control border-0 ps-0" name="date" value="{{ $date }}" placeholder="เลือกวันที่นัด" style="font-size: 0.85rem; font-weight: 600; cursor: pointer; background-color: #fff; border-radius: 0 8px 8px 0;">
                    </div>
                </form>
            </div>
        </div>

        <!-- Clinics Grid -->
        @if (empty($clinics))
            <div class="empty-state">
                <i class="fa-regular fa-calendar-times"></i>
                <h5>ไม่มีรายการนัดหมายผู้ป่วย</h5>
                <p class="text-muted">ไม่พบข้อมูลการนัดหมายในระบบสำหรับวันที่เลือก</p>
            </div>
        @else
            @php
                $colors = [
                    ['color' => '#2563eb', 'light' => '#eff6ff', 'shadow' => 'rgba(37, 99, 235, 0.08)', 'border' => '#bfdbfe'], // Blue
                    ['color' => '#10b981', 'light' => '#ecfdf5', 'shadow' => 'rgba(16, 185, 129, 0.08)', 'border' => '#a7f3d0'], // Green
                    ['color' => '#8b5cf6', 'light' => '#f5f3ff', 'shadow' => 'rgba(139, 92, 246, 0.08)', 'border' => '#ddd6fe'], // Purple
                    ['color' => '#f59e0b', 'light' => '#fffbeb', 'shadow' => 'rgba(245, 158, 11, 0.08)', 'border' => '#fde68a'], // Amber
                    ['color' => '#f43f5e', 'light' => '#fff1f2', 'shadow' => 'rgba(244, 63, 94, 0.08)', 'border' => '#fecdd3'], // Rose
                    ['color' => '#06b6d4', 'light' => '#ecfeff', 'shadow' => 'rgba(6, 182, 212, 0.08)', 'border' => '#a5f3fc'], // Cyan
                    ['color' => '#6366f1', 'light' => '#f5f7ff', 'shadow' => 'rgba(99, 102, 241, 0.08)', 'border' => '#c7d2fe'], // Indigo
                    ['color' => '#14b8a6', 'light' => '#f0fdfa', 'shadow' => 'rgba(20, 184, 166, 0.08)', 'border' => '#99f6e4'], // Teal
                    ['color' => '#ec4899', 'light' => '#fdf2f8', 'shadow' => 'rgba(236, 72, 153, 0.08)', 'border' => '#fbcfe8'], // Pink
                ];
                $cardIndex = 0;
            @endphp
            <div class="clinic-grid">
                @foreach ($clinics as $clinicName => $data)
                    @php 
                        $cardId = 'clinic-card-' . $cardIndex;
                        $colorInfo = $colors[$cardIndex % count($colors)];
                        $cardIndex++;
                    @endphp
                    <div class="clinic-card" id="{{ $cardId }}" 
                         style="--clinic-color: {{ $colorInfo['color'] }}; 
                                --clinic-color-light: {{ $colorInfo['light'] }}; 
                                --clinic-shadow-color: {{ $colorInfo['shadow'] }};
                                --clinic-border-hover: {{ $colorInfo['border'] }};">
                        <div class="clinic-header">
                            <h5 class="clinic-title" title="{{ $clinicName }}">{{ $clinicName }}</h5>
                            <div class="clinic-icon">
                                <i class="fa-solid fa-user-doctor"></i>
                            </div>
                        </div>

                        <div class="count-display">
                            <span class="count-number" id="{{ $cardId }}-count">{{ $data['total'] }}</span>
                            <span class="count-unit">ราย</span>
                        </div>

                        <div class="doctor-selector-wrapper">
                            <label class="form-label small text-muted fw-bold mb-1"><i class="fa-solid fa-filter me-1"></i>เลือกแพทย์ผู้ตรวจ:</label>
                            <select class="doctor-select" onchange="updateDoctorCount('{{ $cardId }}', this)">
                                <option value="all" data-count="{{ $data['total'] }}">แพทย์ทุกคน (ทั้งหมด)</option>
                                @foreach ($data['doctors'] as $doc)
                                    <option value="{{ $doc['name'] }}" data-count="{{ $doc['count'] }}">
                                        {{ $doc['name'] }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Doctor distribution list -->
                            <div class="doctor-list-preview mt-2">
                                @foreach ($data['doctors'] as $doc)
                                    <div class="doctor-item-row">
                                        <span>{{ $doc['name'] }}</span>
                                        <span class="fw-bold" style="color: var(--clinic-color);">{{ $doc['count'] }} ราย</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Page Footer -->
    <div class="footer">
        พิมพ์โดยระบบ SmartData | โรงพยาบาลหัวตะพาน
    </div>
</div>

<script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('vendor/flatpickr/th.js') }}"></script>

<script>
    $(document).ready(function() {
        // Initialize Flatpickr with B.E. (Buddhist Era) offset format
        if (typeof flatpickr !== 'undefined') {
            const yearOffset = 543;
            flatpickr("#oapp_date", {
                locale: "th",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j M Y",
                allowInput: false,
                onReady: function(selectedDates, dateStr, instance) {
                    // Add Today Button
                    const container = instance.calendarContainer;
                    if (container && !container.querySelector('.flatpickr-today-button')) {
                        const btn = document.createElement("div");
                        btn.className = "flatpickr-today-button";
                        btn.innerHTML = '<i class="fas fa-calendar-day me-1"></i> วันนี้';
                        btn.addEventListener("mousedown", function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            instance.setDate(new Date(), true);
                            instance.close();
                        });
                        container.appendChild(btn);
                    }

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
                            
                            // Submit form on date change
                            instance.input.form.submit();
                        }, 10);
                    }
                }
            });
        }
    });

    function updateDoctorCount(cardId, selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const count = selectedOption.getAttribute('data-count');
        const countElement = document.getElementById(cardId + '-count');
        
        // Simple animation effect
        countElement.style.opacity = 0;
        setTimeout(() => {
            countElement.textContent = count;
            countElement.style.opacity = 1;
        }, 150);
    }
</script>

</body>
</html>
