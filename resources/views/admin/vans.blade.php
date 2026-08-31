<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Manage Vans | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .van-status { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-available { background: #dcfce7; color: #166534; }
        .status-unavailable, .status-not-available { background: #fee2e2; color: #991b1b; }
        .status-maintenance { background: #fef3c7; color: #92400e; }
        .van-thumb { width: 64px; height: 44px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 1px solid #e2e8f0; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: white; border-radius: 12px; padding: 28px; width: 460px; max-width: 95vw; max-height: 90vh; overflow-y: auto; }
        .modal-box h3 { margin-bottom: 18px; font-size: 18px; }
        .modal-box input, .modal-box textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; margin-bottom: 12px; outline: none; }
        .modal-box label { font-size: 13px; font-weight: 600; color: #374151; display: block; margin-bottom: 4px; }
        .modal-box .btn-row { display: flex; gap: 10px; margin-top: 6px; }
        .modal-close { float: right; background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; }
        .img-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center; cursor: pointer; }
        .img-modal-overlay.open { display: flex; }
        .img-modal-overlay img { max-width: 90vw; max-height: 90vh; border-radius: 8px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<div class="container">

    <div class="sidebar" id="adminSidebar">
        <h2 class="logo">Rem's Transport</h2>
        <nav>
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/bookings">Bookings</a>
            <a href="/admin/drivers">Drivers</a>
            <a href="/admin/vans" class="active">Vans</a>
            <a href="/admin/tours">Tours</a>
            <a href="/admin/customers">Customers</a>
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports">Reports</a>
            <a href="/admin/live-map">Live Map</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" style="display:none;">@csrf</form>
        </nav>
    </div>

    <div class="main">

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
            <h1 style="margin:0;">Manage Vans</h1>
        </div>

        @if(session('success'))
            <div class="alert" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:12px 18px;border-radius:8px;margin-bottom:16px;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;padding:12px 18px;border-radius:8px;margin-bottom:16px;">
                <i class="fas fa-times-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ADD VAN FORM --}}
        <div class="card" style="margin-bottom:24px;">
            <h3 style="margin-bottom:16px;font-size:16px;">Add New Van</h3>
            <form method="POST" action="{{ route('admin.vans.add') }}" enctype="multipart/form-data" onsubmit="return validateVanForm(this)" id="addVanForm">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Van Name / Model</label>
                        <input type="text" name="name" placeholder="e.g. Toyota Hi-Ace" required value="{{ old('name') }}">
                        @error('name')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Plate Number</label>
                        <input type="text" name="plate_number" id="plateInput" placeholder="e.g. ABC 1234" required value="{{ old('plate_number') }}"
                               oninput="checkDuplicatePlate(this)">
                        <small id="plateError" style="color:#ef4444;display:none;">This plate number is already registered.</small>
                        @error('plate_number')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Seats / Capacity</label>
                        <input type="number" name="seats" placeholder="e.g. 10" min="1" required value="{{ old('seats') }}">
                        @error('seats')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Transmission</label>
                        <input type="text" name="transmission" placeholder="e.g. Manual / Automatic" required value="{{ old('transmission') }}">
                        @error('transmission')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Min Price (₱) <span style="font-weight:400;color:#6b7280;font-size:12px;">— 1 to ₱{{ number_format($baseFare) }}</span></label>
                        <input type="number" name="price_min" id="priceMin" placeholder="e.g. 1000" min="1" max="{{ $baseFare }}" required value="{{ old('price_min') }}" oninput="validatePrices()">
                        <small id="priceMinError" style="color:#ef4444;display:none;"></small>
                        @error('price_min')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Max Price (₱) <span style="font-weight:400;color:#6b7280;font-size:12px;">— 1 to ₱{{ number_format($baseFare) }}</span></label>
                        <input type="number" name="price_max" id="priceMax" placeholder="e.g. 3000" min="1" max="{{ $baseFare }}" required value="{{ old('price_max') }}" oninput="validatePrices()">
                        <small id="priceMaxError" style="color:#ef4444;display:none;"></small>
                        @error('price_max')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Van Image</label>
                        <input type="file" name="image" accept="image/*" required>
                        @error('image')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="full-width">
                        <button type="submit" class="btn btn-approve" style="padding:10px 24px;">
                            <i class="fas fa-plus"></i> Add Van
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- VANS TABLE --}}
        <div class="table-section table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Van Name</th>
                        <th>Plate No.</th>
                        <th>Seats</th>
                        <th>Transmission</th>
                        <th>Price Range</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vans as $van)
                    <tr>
                        <td>{{ $van->id }}</td>
                        <td>
                            @if($van->image)
                                <img src="{{ \Storage::disk('public')->url($van->image) }}"
                                     class="van-thumb"
                                     onclick="viewImage('{{ \Storage::disk('public')->url($van->image) }}')"
                                     title="View Image">
                            @else
                                <span style="color:#cbd5e1;font-size:12px;">No image</span>
                            @endif
                        </td>
                        <td style="font-weight:600;">{{ $van->name }}</td>
                        <td>{{ $van->plate_number }}</td>
                        <td>{{ $van->seats }} pax</td>
                        <td>{{ $van->transmission }}</td>
                        <td>₱{{ number_format($van->price_min) }} – ₱{{ number_format($van->price_max) }}</td>
                        <td>
                            <span class="van-status status-{{ strtolower(str_replace(' ', '-', $van->status)) }}">
                                {{ $van->status }}
                            </span>
                        </td>
                        <td>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;width:fit-content;margin:0 auto;">
                                {{-- Calendar --}}
                                <button class="btn small" style="background:#7c3aed;color:white;"
                                        onclick="openCalendar({{ $van->id }}, '{{ addslashes($van->name) }} ({{ $van->plate_number }})')"
                                        title="View Schedule">
                                    <i class="fas fa-calendar-alt"></i>
                                </button>

                                {{-- Toggle Status --}}
                                <a href="{{ route('admin.vans.toggle', $van->id) }}"
                                   class="btn small {{ $van->status === 'available' ? 'gray' : 'btn-approve' }}"
                                   title="Toggle Status">
                                    <i class="fas fa-toggle-{{ $van->status === 'available' ? 'on' : 'off' }}"></i>
                                </a>

                                {{-- Maintenance --}}
                                <button class="btn small" style="background:#f59e0b;color:white;"
                                        onclick="openMaintenance({{ $van->id }}, '{{ addslashes($van->name) }}', '{{ $van->last_oil_change ?? '' }}', '{{ $van->last_maintenance ?? '' }}', '{{ $van->last_problem_date ?? '' }}', '{{ addslashes($van->last_problem_notes ?? '') }}')"
                                        title="Maintenance">
                                    <i class="fas fa-wrench"></i>
                                </button>

                                {{-- Delete --}}
                                <a href="{{ route('admin.vans.delete', $van->id) }}"
                                   class="btn small red"
                                   onclick="return confirm('Delete {{ addslashes($van->name) }}?')"
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;padding:20px;color:#9ca3af;">No vans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- MAINTENANCE MODAL --}}
<div class="modal-overlay" id="maintenanceModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('maintenanceModal')">&times;</button>
        <h3><i class="fas fa-wrench" style="color:#f59e0b;"></i> Maintenance Record</h3>
        <p id="maintenanceVanName" style="color:#6b7280;font-size:14px;margin-bottom:16px;"></p>
        <form method="POST" id="maintenanceForm">
            @csrf
            <div class="form-row">
                <div>
                    <label>Last Oil Change</label>
                    <input type="date" name="last_oil_change" id="fieldOilChange">
                </div>
                <div>
                    <label>Last Maintenance</label>
                    <input type="date" name="last_maintenance" id="fieldMaintenance">
                </div>
            </div>
            <label>Last Problem Date</label>
            <input type="date" name="last_problem_date" id="fieldProblemDate">
            <label>Last Problem Notes</label>
            <textarea name="last_problem_notes" id="fieldProblemNotes" rows="3" placeholder="Describe the issue..." style="resize:vertical;"></textarea>
            <div class="btn-row">
                <button type="submit" class="btn" style="background:#f59e0b;color:white;padding:10px 20px;">Save Record</button>
                <button type="button" class="btn gray" onclick="closeModal('maintenanceModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- IMAGE VIEWER MODAL --}}
<div class="img-modal-overlay" id="imgModal" onclick="this.classList.remove('open')">
    <img id="imgModalSrc" src="" alt="Van Image">
</div>

{{-- VAN CALENDAR MODAL --}}
<div class="modal-overlay" id="calendarModal" style="align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:28px;width:520px;max-width:96vw;max-height:90vh;overflow-y:auto;position:relative;">
        <button onclick="closeModal('calendarModal')" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280;">&times;</button>
        <h3 id="calTitle" style="margin:0 0 6px;font-size:17px;color:#1e293b;"></h3>
        <p style="font-size:12px;color:#64748b;margin:0 0 18px;">Highlighted dates indicate booked/assigned trips.</p>

        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:16px;font-size:12px;">
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#fbbf24;margin-right:4px;vertical-align:middle;"></span>Pending</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#3b82f6;margin-right:4px;vertical-align:middle;"></span>Approved</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#10b981;margin-right:4px;vertical-align:middle;"></span>Completed</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#8b5cf6;margin-right:4px;vertical-align:middle;"></span>Joiner Trip</span>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <button onclick="changeMonth(-1)" style="border:none;background:#f1f5f9;border-radius:8px;padding:6px 14px;cursor:pointer;font-size:16px;">&#8249;</button>
            <span id="calMonthLabel" style="font-weight:700;font-size:15px;color:#1e293b;"></span>
            <button onclick="changeMonth(1)" style="border:none;background:#f1f5f9;border-radius:8px;padding:6px 14px;cursor:pointer;font-size:16px;">&#8250;</button>
        </div>

        <div id="calGrid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;"></div>

        <div id="calDetail" style="margin-top:18px;display:none;">
            <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;" id="calDetailDate"></div>
            <div id="calDetailList"></div>
        </div>
    </div>
</div>

<script>
const existingPlates = @json($vans->pluck('plate_number')->map(fn($p) => strtolower(trim($p))));
const BASE_FARE = {{ $baseFare }};

function checkDuplicatePlate(input) {
    const val = input.value.trim().toLowerCase();
    const plateError = document.getElementById('plateError');
    if (val && existingPlates.includes(val)) {
        plateError.style.display = 'block';
        input.style.borderColor = '#ef4444';
    } else {
        plateError.style.display = 'none';
        input.style.borderColor = '';
    }
}

function validatePrices() {
    const minInput = document.getElementById('priceMin');
    const maxInput = document.getElementById('priceMax');
    const minErr = document.getElementById('priceMinError');
    const maxErr = document.getElementById('priceMaxError');
    const minVal = parseFloat(minInput.value);
    const maxVal = parseFloat(maxInput.value);
    let valid = true;

    // Validate min price
    if (!isNaN(minVal)) {
        if (minVal < 1) {
            minErr.textContent = 'Min price must be at least ₱1.';
            minErr.style.display = 'block';
            minInput.style.borderColor = '#ef4444';
            valid = false;
        } else if (minVal > BASE_FARE) {
            minErr.textContent = `Min price must not exceed the base fare (₱${BASE_FARE.toLocaleString()}).`;
            minErr.style.display = 'block';
            minInput.style.borderColor = '#ef4444';
            valid = false;
        } else {
            minErr.style.display = 'none';
            minInput.style.borderColor = '';
        }
    }

    // Validate max price
    if (!isNaN(maxVal)) {
        if (maxVal < 1) {
            maxErr.textContent = 'Max price must be at least ₱1.';
            maxErr.style.display = 'block';
            maxInput.style.borderColor = '#ef4444';
            valid = false;
        } else if (maxVal > BASE_FARE) {
            maxErr.textContent = `Max price must not exceed the base fare (₱${BASE_FARE.toLocaleString()}).`;
            maxErr.style.display = 'block';
            maxInput.style.borderColor = '#ef4444';
            valid = false;
        } else if (!isNaN(minVal) && maxVal < minVal) {
            maxErr.textContent = 'Max price must be greater than or equal to min price.';
            maxErr.style.display = 'block';
            maxInput.style.borderColor = '#ef4444';
            valid = false;
        } else {
            maxErr.style.display = 'none';
            maxInput.style.borderColor = '';
        }
    }

    return valid;
}

function validateVanForm(form) {
    const input = document.getElementById('plateInput');
    const val = input.value.trim().toLowerCase();
    if (existingPlates.includes(val)) {
        document.getElementById('plateError').style.display = 'block';
        input.style.borderColor = '#ef4444';
        input.focus();
        return false;
    }
    if (!validatePrices()) return false;
    return true;
}

function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function openMaintenance(id, name, oilChange, maintenance, problemDate, problemNotes) {
    document.getElementById('maintenanceVanName').textContent = 'Van: ' + name;
    document.getElementById('maintenanceForm').action = '/admin/vans/maintenance/' + id;
    document.getElementById('fieldOilChange').value = oilChange;
    document.getElementById('fieldMaintenance').value = maintenance;
    document.getElementById('fieldProblemDate').value = problemDate;
    document.getElementById('fieldProblemNotes').value = problemNotes;
    document.getElementById('maintenanceModal').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function viewImage(src) {
    document.getElementById('imgModalSrc').src = src;
    document.getElementById('imgModal').classList.add('open');
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => el.style.opacity = '0');
}, 4000);

// ======= VAN CALENDAR =======
let calBookings = [], calYear, calMonth;

function openCalendar(vanId, vanLabel) {
    document.getElementById('calTitle').textContent = vanLabel + ' — Booking Schedule';
    document.getElementById('calDetail').style.display = 'none';
    const now = new Date();
    calYear = now.getFullYear();
    calMonth = now.getMonth();
    document.getElementById('calendarModal').classList.add('open');

    fetch(`/admin/vans/${vanId}/schedule`)
        .then(r => r.json())
        .then(data => {
            calBookings = data.bookings || [];
            renderCalendar();
        });
}

function changeMonth(dir) {
    calMonth += dir;
    if (calMonth > 11) { calMonth = 0; calYear++; }
    if (calMonth < 0)  { calMonth = 11; calYear--; }
    document.getElementById('calDetail').style.display = 'none';
    renderCalendar();
}

function renderCalendar() {
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const dayNames   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    document.getElementById('calMonthLabel').textContent = monthNames[calMonth] + ' ' + calYear;

    const grid = document.getElementById('calGrid');
    grid.innerHTML = '';

    dayNames.forEach(d => {
        const h = document.createElement('div');
        h.textContent = d;
        h.style.cssText = 'text-align:center;font-size:11px;font-weight:700;color:#94a3b8;padding:4px 0;';
        grid.appendChild(h);
    });

    const firstDay = new Date(calYear, calMonth, 1).getDay();
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    const today = new Date();

    for (let i = 0; i < firstDay; i++) grid.appendChild(document.createElement('div'));

    for (let d = 1; d <= daysInMonth; d++) {
        const dateStr = calYear + '-' + String(calMonth + 1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        const hits = calBookings.filter(b => dateStr >= b.start && dateStr <= b.end);
        const isToday = today.getFullYear() === calYear && today.getMonth() === calMonth && today.getDate() === d;

        const cell = document.createElement('div');
        cell.textContent = d;
        cell.style.cssText = `text-align:center;padding:7px 2px;border-radius:8px;font-size:13px;cursor:${hits.length ? 'pointer' : 'default'};
            font-weight:${isToday ? '800' : '400'};
            border:${isToday ? '2px solid #2563eb' : '1px solid transparent'};`;

        if (hits.length) {
            const statusColors = { approved:'#3b82f6', completed:'#10b981', pending:'#fbbf24', downpayment_paid:'#f59e0b', fully_paid:'#10b981' };
            const isJoiner = hits.some(b => b.type === 'Joiner Trip');
            const bg = isJoiner ? '#8b5cf6' : (statusColors[hits[0].status] || '#64748b');
            cell.style.background = bg;
            cell.style.color = 'white';
            cell.title = hits.map(b => b.type + ': ' + b.destination).join('\n');
            cell.onclick = () => showDayDetail(dateStr, hits);
        }

        grid.appendChild(cell);
    }
}

function showDayDetail(dateStr, hits) {
    const detail = document.getElementById('calDetail');
    const dateLabel = new Date(dateStr + 'T00:00:00').toLocaleDateString('en-PH', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    document.getElementById('calDetailDate').textContent = dateLabel;

    const list = document.getElementById('calDetailList');
    list.innerHTML = hits.map(b => {
        const colorMap = { approved:'#3b82f6', completed:'#10b981', pending:'#fbbf24', downpayment_paid:'#f59e0b', fully_paid:'#10b981' };
        const bg = b.type === 'Joiner Trip' ? '#8b5cf6' : (colorMap[b.status] || '#64748b');
        return `<div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8fafc;border-radius:8px;margin-bottom:6px;border-left:4px solid ${bg};">
            <div>
                <div style="font-weight:700;font-size:13px;color:#1e293b;">${b.destination}</div>
                <div style="font-size:11px;color:#64748b;">${b.type} &bull; ${b.start}${b.start !== b.end ? ' → ' + b.end : ''}</div>
                <div style="font-size:11px;margin-top:2px;"><span style="background:${bg};color:white;padding:2px 8px;border-radius:50px;font-size:10px;font-weight:700;">${b.status.replace('_',' ').toUpperCase()}</span></div>
            </div>
        </div>`;
    }).join('');

    detail.style.display = 'block';
}
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
