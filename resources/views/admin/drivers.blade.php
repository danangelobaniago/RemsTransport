<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Manage Drivers | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .driver-status { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-available { background: #dcfce7; color: #166534; }
        .status-unavailable, .status-not-available { background: #fee2e2; color: #991b1b; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: white; border-radius: 12px; padding: 28px; width: 420px; max-width: 95vw; }
        .modal-box h3 { margin-bottom: 18px; font-size: 18px; }
        .modal-box input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; margin-bottom: 12px; outline: none; }
        .modal-box .btn-row { display: flex; gap: 10px; margin-top: 6px; }
        .modal-close { float: right; background: none; border: none; font-size: 20px; cursor: pointer; color: #6b7280; }
        .license-thumb { width: 50px; height: 36px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #e2e8f0; }
        .img-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center; cursor: pointer; }
        .img-modal-overlay.open { display: flex; }
        .img-modal-overlay img { max-width: 90vw; max-height: 90vh; border-radius: 8px; }
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
            <a href="/admin/drivers" class="active">Drivers</a>
            <a href="/admin/vans">Vans</a>
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
            <h1 style="margin:0;">Manage Drivers</h1>
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

        {{-- ADD DRIVER FORM --}}
        <div class="card" style="margin-bottom:24px;">
            <h3 style="margin-bottom:16px;font-size:16px;">Add New Driver</h3>
            <form method="POST" action="{{ route('admin.drivers.add') }}" enctype="multipart/form-data" onsubmit="return validateDriverForm(this)">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" id="driverName" placeholder="e.g. Juan Dela Cruz" required value="{{ old('name') }}"
                               oninput="this.value=this.value.replace(/[0-9]/g,'')">
                        <small id="nameError" style="color:#ef4444;display:none;">Name must not contain numbers.</small>
                        @error('name')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" id="driverPhone" placeholder="09XXXXXXXXX" required value="{{ old('phone') }}"
                               maxlength="11" oninput="enforcePhone(this)" onfocus="enforcePhone(this)">
                        <small id="phoneError" style="color:#ef4444;display:none;">Phone must start with 09 and be exactly 11 digits.</small>
                        @error('phone')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>License Number</label>
                        <input type="text" name="license" placeholder="Professional Driver's License" required value="{{ old('license') }}">
                        @error('license')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label>License Image</label>
                        <input type="file" name="license_image" accept="image/*" required>
                        @error('license_image')<small style="color:#ef4444;">{{ $message }}</small>@enderror
                    </div>
                    <div class="full-width">
                        <button type="submit" class="btn btn-approve" style="padding:10px 24px;">
                            <i class="fas fa-plus"></i> Add Driver
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- DRIVERS TABLE --}}
        <div class="table-section table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>License No.</th>
                        <th>License</th>
                        <th>Status</th>
                        <th>Login Account</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                    <tr>
                        <td>{{ $driver->id }}</td>
                        <td style="font-weight:600;">{{ $driver->name }}</td>
                        <td>{{ $driver->phone }}</td>
                        <td>{{ $driver->license_number }}</td>
                        <td>
                            @if($driver->license_image)
                                <img src="{{ \Storage::disk('public')->url($driver->license_image) }}"
                                     class="license-thumb"
                                     onclick="viewImage('{{ \Storage::disk('public')->url($driver->license_image) }}')"
                                     title="View License">
                            @else
                                <span style="color:#cbd5e1;font-size:12px;">No image</span>
                            @endif
                        </td>
                        <td>
                            <span class="driver-status status-{{ strtolower(str_replace(' ', '-', $driver->status)) }}">
                                {{ $driver->status }}
                            </span>
                        </td>
                        <td>
                            @if($driver->user_id)
                                <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;">
                                    <span style="font-size:12px;color:#374151;">
                                        <i class="fas fa-check-circle" style="color:#22c55e;"></i>
                                        {{ $driver->account_email }}
                                    </span>
                                    <div style="display:flex;gap:6px;">
                                        <button class="btn small" style="background:#f59e0b;color:white;"
                                                onclick="openChangePassword({{ $driver->id }}, '{{ $driver->name }}')"
                                                title="Change Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.drivers.removeAccount', $driver->id) }}" style="display:inline;"
                                              onsubmit="return confirm('Remove login account for {{ $driver->name }}?')">
                                            @csrf
                                            <button type="submit" class="btn small red" title="Remove Account">
                                                <i class="fas fa-user-minus"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start;">
                                    <span style="font-size:12px;color:#9ca3af;">No account</span>
                                    <button class="btn small" style="background:#6366f1;color:white;"
                                            onclick="openCreateAccount({{ $driver->id }}, '{{ $driver->name }}')"
                                            title="Create Login">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;justify-content:center;">
                                {{-- Calendar --}}
                                <button class="btn small" style="background:#7c3aed;color:white;"
                                        onclick="openCalendar({{ $driver->id }}, '{{ addslashes($driver->name) }}')"
                                        title="View Schedule">
                                    <i class="fas fa-calendar-alt"></i>
                                </button>

                                {{-- Edit --}}
                                <a href="{{ route('admin.drivers.edit', $driver->id) }}"
                                   class="btn small blue" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>

                                {{-- Toggle Status --}}
                                <a href="{{ route('admin.drivers.toggle', $driver->id) }}"
                                   class="btn small {{ $driver->status === 'available' ? 'gray' : 'btn-approve' }}"
                                   title="Toggle Status">
                                    <i class="fas fa-toggle-{{ $driver->status === 'available' ? 'on' : 'off' }}"></i>
                                </a>

                                {{-- Delete --}}
                                <a href="{{ route('admin.drivers.delete', $driver->id) }}"
                                   class="btn small red"
                                   onclick="return confirm('Delete {{ $driver->name }}?')"
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">No drivers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- CREATE ACCOUNT MODAL --}}
<div class="modal-overlay" id="createAccountModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('createAccountModal')">&times;</button>
        <h3><i class="fas fa-user-plus" style="color:#6366f1;"></i> Create Login Account</h3>
        <p id="createAccountDriver" style="color:#6b7280;font-size:14px;margin-bottom:16px;"></p>
        <form method="POST" id="createAccountForm">
            @csrf
            <input type="email" name="email" placeholder="Email address" required>
            <input type="password" name="password" placeholder="Password (min 8 chars)" required>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
            <div class="btn-row">
                <button type="submit" class="btn" style="background:#6366f1;color:white;padding:10px 20px;">Create Account</button>
                <button type="button" class="btn gray" onclick="closeModal('createAccountModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- CHANGE PASSWORD MODAL --}}
<div class="modal-overlay" id="changePasswordModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('changePasswordModal')">&times;</button>
        <h3><i class="fas fa-key" style="color:#f59e0b;"></i> Change Password</h3>
        <p id="changePasswordDriver" style="color:#6b7280;font-size:14px;margin-bottom:16px;"></p>
        <form method="POST" id="changePasswordForm">
            @csrf
            <input type="password" name="password" placeholder="New Password (min 8 chars)" required>
            <input type="password" name="password_confirmation" placeholder="Confirm New Password" required>
            <div class="btn-row">
                <button type="submit" class="btn" style="background:#f59e0b;color:white;padding:10px 20px;">Update Password</button>
                <button type="button" class="btn gray" onclick="closeModal('changePasswordModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- IMAGE VIEWER MODAL --}}
<div class="img-modal-overlay" id="imgModal" onclick="this.classList.remove('open')">
    <img id="imgModalSrc" src="" alt="License">
</div>

{{-- DRIVER CALENDAR MODAL --}}
<div class="modal-overlay" id="calendarModal" style="align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:28px;width:520px;max-width:96vw;max-height:90vh;overflow-y:auto;position:relative;">
        <button onclick="closeModal('calendarModal')" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280;">&times;</button>
        <h3 id="calTitle" style="margin:0 0 6px;font-size:17px;color:#1e293b;"></h3>
        <p style="font-size:12px;color:#64748b;margin:0 0 18px;">Highlighted dates indicate booked/assigned trips.</p>

        {{-- Legend --}}
        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:16px;font-size:12px;">
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#fbbf24;margin-right:4px;vertical-align:middle;"></span>Pending</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#3b82f6;margin-right:4px;vertical-align:middle;"></span>Approved</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#10b981;margin-right:4px;vertical-align:middle;"></span>Completed</span>
            <span><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#8b5cf6;margin-right:4px;vertical-align:middle;"></span>Joiner Trip</span>
        </div>

        {{-- Month nav --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <button onclick="changeMonth(-1)" style="border:none;background:#f1f5f9;border-radius:8px;padding:6px 14px;cursor:pointer;font-size:16px;">&#8249;</button>
            <span id="calMonthLabel" style="font-weight:700;font-size:15px;color:#1e293b;"></span>
            <button onclick="changeMonth(1)" style="border:none;background:#f1f5f9;border-radius:8px;padding:6px 14px;cursor:pointer;font-size:16px;">&#8250;</button>
        </div>

        {{-- Calendar grid --}}
        <div id="calGrid" style="display:grid;grid-template-columns:repeat(7,1fr);gap:4px;"></div>

        {{-- Booking list for selected date --}}
        <div id="calDetail" style="margin-top:18px;display:none;">
            <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:8px;" id="calDetailDate"></div>
            <div id="calDetailList"></div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function openCreateAccount(id, name) {
    document.getElementById('createAccountDriver').textContent = 'Driver: ' + name;
    document.getElementById('createAccountForm').action = '/admin/drivers/create-account/' + id;
    document.getElementById('createAccountModal').classList.add('open');
}

function openChangePassword(id, name) {
    document.getElementById('changePasswordDriver').textContent = 'Driver: ' + name;
    document.getElementById('changePasswordForm').action = '/admin/drivers/change-password/' + id;
    document.getElementById('changePasswordModal').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

function viewImage(src) {
    document.getElementById('imgModalSrc').src = src;
    document.getElementById('imgModal').classList.add('open');
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
});

function enforcePhone(input) {
    // Strip non-digits
    let val = input.value.replace(/[^0-9]/g, '');
    // Always prepend "09" if not already starting with it
    if (!val.startsWith('09')) {
        val = '09' + val.replace(/^0*9*/, '');
    }
    // Cap at 11 digits
    input.value = val.slice(0, 11);
}

function validateDriverForm(form) {
    let valid = true;

    const nameInput = form.querySelector('#driverName');
    const nameError = document.getElementById('nameError');
    if (/[0-9]/.test(nameInput.value)) {
        nameError.style.display = 'block';
        nameInput.style.borderColor = '#ef4444';
        valid = false;
    } else {
        nameError.style.display = 'none';
        nameInput.style.borderColor = '';
    }

    const phoneInput = form.querySelector('#driverPhone');
    const phoneError = document.getElementById('phoneError');
    if (!/^09\d{9}$/.test(phoneInput.value)) {
        phoneError.style.display = 'block';
        phoneInput.style.borderColor = '#ef4444';
        valid = false;
    } else {
        phoneError.style.display = 'none';
        phoneInput.style.borderColor = '';
    }

    return valid;
}

// Auto-hide alerts
setTimeout(() => {
    document.querySelectorAll('.alert').forEach(el => el.style.opacity = '0');
}, 4000);

// ======= DRIVER CALENDAR =======
let calBookings = [], calYear, calMonth;

function openCalendar(driverId, driverName) {
    document.getElementById('calTitle').textContent = driverName + ' — Booking Schedule';
    document.getElementById('calDetail').style.display = 'none';
    const now = new Date();
    calYear = now.getFullYear();
    calMonth = now.getMonth();
    document.getElementById('calendarModal').classList.add('open');

    fetch(`/admin/drivers/${driverId}/schedule`)
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

    // Day headers
    dayNames.forEach(d => {
        const h = document.createElement('div');
        h.textContent = d;
        h.style.cssText = 'text-align:center;font-size:11px;font-weight:700;color:#94a3b8;padding:4px 0;';
        grid.appendChild(h);
    });

    const firstDay = new Date(calYear, calMonth, 1).getDay();
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    const today = new Date();

    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
        grid.appendChild(document.createElement('div'));
    }

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
