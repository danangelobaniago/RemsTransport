<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tour->name }} | Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Flatpickr customisation to match site style */
        .flatpickr-calendar { border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: none; font-family: 'Inter', sans-serif; }
        .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #2563eb; border-color: #2563eb; }
        .flatpickr-day:hover { background: #eff6ff; }
        .flatpickr-day.disabled, .flatpickr-day.disabled:hover { color: #cbd5e1 !important; background: #f8fafc !important; cursor: not-allowed !important; text-decoration: line-through; }
        .flatpickr-months .flatpickr-month { background: #2563eb; color: white; border-radius: 14px 14px 0 0; }
        .flatpickr-current-month .flatpickr-monthDropdown-months, .flatpickr-current-month input.cur-year { color: white; }
        .flatpickr-weekdays { background: #eff6ff; }
        span.flatpickr-weekday { color: #2563eb; font-weight: 700; }
        .flatpickr-day.today { border-color: #2563eb; }
        .numInputWrapper:hover { background: transparent; }
        .flatpickr-prev-month svg, .flatpickr-next-month svg { fill: white; }
    </style>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --text-dark: #0f172a;
            --text-light: #64748b;
            --bg-gray: #f8fafc;
        }

        /* Keep your existing CSS styles exactly as they were */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-gray); color: var(--text-dark); }

        .top-nav { background: white; padding: 15px 5%; display: flex; align-items: center; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 1000; }
        .back-btn { text-decoration: none; color: var(--text-light); font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: color 0.2s; }
        .back-btn:hover { color: var(--primary); }

        .main-wrapper { max-width: 1200px; margin: 40px auto; display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 30px; padding: 0 20px; }

        .content-section { background: white; padding: 40px; border-radius: 20px; border: 1px solid #e2e8f0; }
        .tour-header h1 { font-size: 42px; font-weight: 800; letter-spacing: -1px; line-height: 1.1; }
        .location-tag { color: var(--primary); font-weight: 700; margin: 10px 0; display: block; font-size: 16px; }

        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 30px 0; padding: 20px 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        .feature-item { text-align: center; }
        .feature-item i { color: var(--primary); font-size: 20px; display: block; margin-bottom: 8px; }
        .feature-item span { font-size: 13px; color: var(--text-light); font-weight: 600; text-transform: uppercase; }
        .feature-item p { font-size: 15px; font-weight: 700; }

        .section-heading { font-size: 20px; font-weight: 700; margin: 40px 0 15px; display: flex; align-items: center; gap: 10px; }
        .description-box { background: #f8fafc; padding: 25px; border-radius: 15px; color: #334155; line-height: 1.8; white-space: pre-line; }

        .sidebar-section { display: flex; flex-direction: column; gap: 30px; }
        .image-gallery { width: 100%; height: 300px; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .image-gallery img { width: 100%; height: 100%; object-fit: cover; }

        .booking-sticky-card { background: white; padding: 30px; border-radius: 20px; border: 1px solid #e2e8f0; position: sticky; top: 100px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
        .price-label { font-size: 14px; color: var(--text-light); font-weight: 600; }
        .price-amount { font-size: 36px; font-weight: 800; color: var(--primary); margin-bottom: 20px; }

        .logistics-card { background: #f1f5f9; padding: 15px; border-radius: 12px; margin-bottom: 20px; }
        .log-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .log-row:last-child { margin-bottom: 0; }
        .log-label { color: var(--text-light); font-weight: 500; }
        .log-val { font-weight: 700; text-align: right; }

        .btn-book-now { display: block; background: var(--primary); color: white; text-align: center; padding: 18px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 16px; transition: all 0.3s; }
        .btn-book-now:hover { background: var(--primary-dark); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2); }

        @media (max-width: 900px) {
            .main-wrapper { grid-template-columns: 1fr; }
            .sidebar-section { order: -1; }
        }
    </style>
</head>
<body>

<div class="top-nav">
    <a href="/" class="back-btn"><i class="fas fa-chevron-left"></i> Back</a>
</div>

<div class="main-wrapper">
    <div class="content-section">
        <div class="tour-header">
            <span class="location-tag"><i class="fas fa-map-marker-alt"></i> {{ $tour->destination }}</span>
            <h1>{{ $tour->name }}</h1>
        </div>

        <div class="feature-grid">
            <div class="feature-item">
                <i class="fas fa-clock"></i>
                <span>Duration</span>
                <p>{{ $tour->duration }}</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-user-friends"></i>
                <span>Capacity</span>
                <p>{{ $tour->max_passengers }} Pax</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-calendar-check"></i>
                <span>Available Dates</span>
                <p style="font-size:13px;">{{ date('M d', strtotime($tour->tour_date)) }} – {{ date('M d, Y', strtotime($tour->end_date ?? $tour->tour_date)) }}</p>
            </div>
        </div>

        <h2 class="section-heading"><i class="fas fa-file-alt text-primary"></i> Description & Inclusions</h2>
        <div class="description-box">
            {{ $tour->description }}
        </div>
    </div>

    <div class="sidebar-section">
        <div class="image-gallery">
            <img src="{{ \Storage::disk('public')->url($tour->image) }}" alt="Tour Image">
        </div>

        @php
            preg_match('/(\d+)/', $tour->duration ?? '1', $dm);
            $tripDays = max(1, (int)($dm[1] ?? 1));
            $availEnd  = $tour->end_date ?? $tour->tour_date;
            // Latest valid start = availEnd minus (tripDays-1) so the full trip fits inside the range
            $maxStart  = date('Y-m-d', strtotime($availEnd . ' -' . ($tripDays - 1) . ' days'));
            if ($maxStart < $tour->tour_date) $maxStart = $tour->tour_date;
        @endphp

        <div class="booking-sticky-card">
            <p class="price-label">Price per Package</p>
            <div class="price-amount">₱{{ number_format($tour->price) }}</div>

            <div class="logistics-card">
                <div class="log-row">
                    <span class="log-label">Pickup Point:</span>
                    <span class="log-val">{{ $tour->pickup_point }}</span>
                </div>
                <div class="log-row">
                    <span class="log-label">Pickup Time:</span>
                    <span class="log-val">{{ isset($tour->pickup_time) ? date('g:i A', strtotime($tour->pickup_time)) : 'TBA' }}</span>
                </div>
                <div class="log-row">
                    <span class="log-label">Vehicle:</span>
                    <span class="log-val">{{ $tour->van ?? 'TBA' }} ({{ $tour->plate_number ?? '' }})</span>
                </div>
                <div class="log-row">
                    <span class="log-label">Driver:</span>
                    <span class="log-val">{{ $tour->driver_name ?? 'TBA' }}</span>
                </div>
            </div>

            <form action="{{ route('bookings.tour_create', $tour->id) }}" method="GET">
                <div style="margin-bottom:16px;">
                    <label style="display:block;font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                        <i class="fas fa-calendar-alt" style="color:var(--primary);margin-right:4px;"></i> Choose Your Start Date
                    </label>
                    <input type="text" name="preferred_date" id="preferred_date"
                           placeholder="Select your travel start date"
                           required readonly
                           style="width:100%;padding:12px 14px;border:2px solid #e2e8f0;border-radius:10px;font-size:15px;font-weight:600;color:#1e293b;cursor:pointer;background:white;">

                    {{-- Trip range preview shown after user picks a date --}}
                    <div id="trip-range-preview" style="display:none;margin-top:10px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 12px;">
                        <div style="font-size:11px;font-weight:700;color:#1d4ed8;text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">
                            <i class="fas fa-route"></i> Your Trip Schedule
                        </div>
                        <div id="trip-range-text" style="font-size:14px;font-weight:700;color:#1e293b;"></div>
                        <div style="font-size:11px;color:#3b82f6;margin-top:2px;">{{ $tour->duration }} · {{ $tripDays }} day{{ $tripDays > 1 ? 's' : '' }}</div>
                    </div>

                    <p style="font-size:11px;color:#94a3b8;margin-top:6px;">
                        <i class="fas fa-info-circle"></i>
                        Available: {{ date('M d', strtotime($tour->tour_date)) }} – {{ date('M d, Y', strtotime($availEnd)) }}
                        &nbsp;·&nbsp; Trip is {{ $tripDays }} day{{ $tripDays > 1 ? 's' : '' }}
                        &nbsp;·&nbsp; <span style="color:#ef4444;">&#9632;</span> Greyed = booked
                    </p>
                </div>
                <button type="submit" id="reserve-btn" class="btn-book-now" style="border:none;cursor:pointer;width:100%;">
                    Reserve This Trip <i class="fas fa-arrow-right" style="margin-left:8px;"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const TRIP_DAYS     = {{ $tripDays }};
const BOOKED_RANGES = @json($bookedRanges);
const TOUR_START    = '{{ $tour->tour_date }}';
const MAX_START     = '{{ $maxStart }}';

function toYMD(d) {
    return d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');
}

// Each booked range [B_start, B_end] blocks all start dates in:
//   [B_start − (TRIP_DAYS−1),  B_end]
// because any trip starting in that window would overlap the booking.
const disabledRanges = BOOKED_RANGES.map(r => {
    const from = new Date(r.start + 'T00:00:00');
    from.setDate(from.getDate() - (TRIP_DAYS - 1));
    const to = new Date(r.end + 'T00:00:00');
    return { from: toYMD(from), to: toYMD(to) };
});

flatpickr('#preferred_date', {
    minDate:       TOUR_START,
    maxDate:       MAX_START,
    disable:       disabledRanges,
    dateFormat:    'Y-m-d',
    disableMobile: true,
    onChange: function(selectedDates, dateStr) {
        if (!selectedDates.length) return;

        const start = selectedDates[0];
        const end   = new Date(start);
        end.setDate(end.getDate() + TRIP_DAYS - 1);

        const opts      = { month: 'short', day: 'numeric', year: 'numeric' };
        const optsShort = { month: 'short', day: 'numeric' };

        document.getElementById('trip-range-text').textContent = TRIP_DAYS === 1
            ? start.toLocaleDateString('en-PH', opts)
            : start.toLocaleDateString('en-PH', optsShort) + ' – ' + end.toLocaleDateString('en-PH', opts);

        document.getElementById('trip-range-preview').style.display = 'block';

        const btn = document.getElementById('reserve-btn');
        btn.disabled      = false;
        btn.style.opacity = '1';
        btn.style.cursor  = 'pointer';
    }
});
</script>
</body>
</html>
