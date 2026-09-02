<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Manage Joiner Trips | Admin</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .alignment-fix {
            align-items: flex-end;
        }
        .form-section {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 20px;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 11px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .section-title {
            color: #2563eb;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .status-badge {
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
        }
        .btn-complete {
            background: none;
            border: none;
            color: #10b981;
            cursor: pointer;
            padding: 0;
            font-size: 1.1rem;
            transition: transform 0.2s;
        }
        .btn-complete:hover {
            transform: scale(1.2);
        }
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
            <a href="/admin/vans">Vans</a>
            <a href="/admin/tours">Tours</a>
            <a href="/admin/customers">Customers</a>
            <a href="/admin/book-for-customer">New Booking</a>
            <a href="/admin/joiner-trips" class="active">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports">Reports</a>
            <a href="/admin/live-map">Live Map</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Logout
            </a>
            <form id="logout-form" action="/logout" method="POST" style="display: none;">
                @csrf
            </form>
        </nav>
    </div>

    <div class="main">
        <div class="header-flex" style="display:flex;align-items:center;gap:10px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
            <h1>Manage Joiner Trips</h1>
        </div>

        {{-- ALERTS --}}
        @if(session('success'))
            <div class="alert success" style="background: #dcfce7; color: #16a34a; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert error" style="background: #fee2e2; color: #ef4444; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- ADD NEW TRIP CARD --}}
        <div class="card">
            <h3><i class="fas fa-plus-circle"></i> Add New Joiner Trip</h3>
            <form action="{{ route('admin.joiner-trips.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>Destination</label>
                        <input type="text" name="destination" placeholder="e.g. Mt. Pulag" required>
                    </div>
                    <div class="form-group">
                        <label>Meetup Point</label>
                        <input type="text" name="meetup_point" placeholder="e.g. SM North" required>
                    </div>
                    <div class="form-group">
                        <label>Meetup Time</label>
                        <input type="text" name="meetup_time" id="meetup_time" placeholder="Select time" autocomplete="off" required>
                    </div>
                    <div class="form-group">
                         <label>Start Date</label>
                         <input type="date" name="trip_date" id="trip_date" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                         <label>End Date</label>
                         <input type="date" name="end_date" id="end_date" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Total Seats</label>
                        <input type="number" name="total_seats" placeholder="15" required>
                    </div>
                    <div class="form-group">
                        <label>Price Per Seat (PHP)</label>
                        <input type="number" name="price_per_seat" placeholder="2500" required>
                    </div>
                </div>

                <div class="form-section">
                    <h4 class="section-title"><i class="fas fa-bus"></i> Van & Driver Assignment</h4>
                    <div class="form-grid alignment-fix">
                        <div class="form-group">
                            <label>Select Van</label>
                            <select name="van_id" id="joinerVanSelect" required onchange="fillJoinerVanInfo(this)">
                                <option value="">-- Choose Van --</option>
                                @foreach($vans as $van)
                                    <option value="{{ $van->id }}"
                                            data-name="{{ $van->name }}"
                                            data-plate="{{ $van->plate_number }}"
                                            data-seats="{{ $van->seats }}">
                                        {{ $van->name }} — {{ $van->plate_number }} ({{ $van->seats }} pax)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Driver</label>
                            <select name="driver_id" id="joinerDriverSelect" required>
                                <option value="">-- Choose Driver --</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}"
                                            data-name="{{ $driver->name }}"
                                            data-license="{{ $driver->license_number }}">
                                        {{ $driver->name }} — {{ $driver->license_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-add" style="margin-top: 25px; background: #2563eb; color: white; padding: 13px 40px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-save"></i> Create Joiner Trip
                </button>
            </form>
        </div>

        {{-- LIST SECTION --}}
        <div class="card" style="margin-top: 30px;">
            <h3><i class="fas fa-list"></i> Active Trip List</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="text-align: left; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px;">Destination</th>
                        <th style="padding: 12px;">Dates (Start-End)</th>
                        <th style="padding: 12px;">Van & Plate</th>
                        <th style="padding: 12px;">Driver Details</th>
                        <th style="padding: 12px;">Available</th>
                        <th style="padding: 12px;">Price</th>
                        <th style="padding: 12px;">Status</th>
                        <th style="padding: 12px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trips as $trip)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px; font-weight: 600;">{{ $trip->destination }}</td>
                        <td style="padding: 12px;">
                            {{ date('M d, Y', strtotime($trip->trip_date)) }} -<br>
                            <small>{{ $trip->end_date ? date('M d, Y', strtotime($trip->end_date)) : 'N/A' }}</small>
                            @if($trip->meetup_time)
                                <br><small style="color:#2563eb;font-weight:600;"><i class="fas fa-clock"></i> {{ date('g:i A', strtotime($trip->meetup_time)) }}</small>
                            @endif
                        </td>
                        <td style="padding: 12px;">
                            <strong>{{ $trip->van }}</strong><br>
                            <small style="color: #64748b;">{{ $trip->plate_number }}</small>
                        </td>
                        <td style="padding: 12px;">
                            <strong>{{ $trip->driver_name }}</strong><br>
                            @if(!empty($trip->driver_license_image))
                                <a href="{{ \Storage::disk('public')->url($trip->driver_license_image) }}" target="_blank" style="text-decoration: none; color: #64748b; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fas fa-id-card" style="color: #2563eb;"></i> {{ $trip->driver_license_number }}
                                </a>
                            @else
                                <small style="color: #94a3b8;">{{ $trip->driver_license_number }}</small>
                            @endif
                        </td>
                        <td style="padding: 12px;">
                            <span style="font-weight: 700; color: #1e293b;">{{ $trip->available_seats }}</span> / {{ $trip->total_seats }}
                        </td>
                        <td style="padding: 12px; color: #2563eb; font-weight: 700;">
                            ₱{{ number_format($trip->price_per_seat) }}
                        </td>
                        <td style="padding: 12px;">
                            @php
                                $sc = $trip->status;
                                $ts = $trip->trip_status ?? 'not_started';

                                if ($sc === 'pending') {
                                    $label = 'PENDING APPROVAL'; $bg = '#fef3c7'; $col = '#92400e';
                                } elseif ($sc === 'rejected') {
                                    $label = 'REJECTED'; $bg = '#fee2e2'; $col = '#991b1b';
                                } elseif ($sc === 'cancelled') {
                                    $label = 'CANCELLED'; $bg = '#f1f5f9'; $col = '#475569';
                                } elseif ($sc === 'completed' || $ts === 'completed') {
                                    $label = 'COMPLETED'; $bg = '#e0e7ff'; $col = '#3730a3';
                                } elseif ($ts === 'arrived') {
                                    $label = 'ARRIVED AT PICKUP'; $bg = '#dbeafe'; $col = '#1d4ed8';
                                } elseif ($ts === 'in_progress' && ($trip->all_paid ?? false)) {
                                    $label = 'BALANCE COLLECTED'; $bg = '#dcfce7'; $col = '#166534';
                                } elseif ($ts === 'in_progress') {
                                    $label = 'ONGOING TRIP'; $bg = '#ede9fe'; $col = '#7c3aed';
                                } else {
                                    $label = 'ACTIVE'; $bg = '#dcfce7'; $col = '#166534';
                                }
                            @endphp
                            <span style="padding:4px 10px;border-radius:50px;font-size:11px;font-weight:700;white-space:nowrap;background:{{ $bg }};color:{{ $col }};">
                                {{ $label }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <div style="display: flex; gap: 8px; align-items: center; justify-content: center; flex-wrap: wrap;">

                                {{-- Manifest --}}
                                <a href="{{ route('admin.joiner-trips.passengers', $trip->id) }}" style="color: #16a34a;" title="View Manifest">
                                    <i class="fas fa-users"></i>
                                </a>

                                {{-- ACTIVATE (if rejected/pending) --}}
                                @if(in_array($trip->status, ['pending', 'rejected']))
                                    <form action="{{ route('admin.joiner.approve', $trip->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="submit" style="background:#16a34a;color:white;border:none;padding:5px 10px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;" title="Activate Trip">
                                            <i class="fas fa-check"></i> Activate
                                        </button>
                                    </form>
                                @endif


                                {{-- COMPLETE TRIP ACTION --}}
                                @if(!in_array($trip->status, ['completed', 'pending', 'rejected']))
                                    <form id="complete-form-{{ $trip->id }}" action="{{ route('admin.joiner.complete', $trip->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        <button type="button" class="btn-complete" title="Complete Trip"
                                                onclick="if(confirm('Mark this trip as COMPLETED?')) { document.getElementById('complete-form-{{ $trip->id }}').submit(); }">
                                            <i class="fas fa-check-double"></i>
                                        </button>
                                    </form>

                                    <a href="/admin/joiner-trips/edit/{{ $trip->id }}" style="color: #2563eb;" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @else
                                    <span class="status-badge" style="background: #dcfce7; color: #166534;">DONE</span>
                                @endif

                                {{-- Delete --}}
                                <form action="/admin/joiner-trips/{{ $trip->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this trip?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align: center; padding: 50px; color: #94a3b8;">No joiner trips found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const bookedSchedule = @json($bookedSchedule);
    const tripDateInput = document.getElementById('trip_date');
    const plateInput = document.querySelector('input[name="plate_number"]');
    const driverInput = document.querySelector('input[name="driver_name"]');
    const endDateInput = document.getElementById('end_date');

    // When start date changes, end date min must be >= start date (and never in the past)
    tripDateInput.addEventListener('change', function () {
        const today = '{{ date('Y-m-d') }}';
        const minEnd = this.value > today ? this.value : today;
        endDateInput.min = minEnd;
        if (endDateInput.value && endDateInput.value < minEnd) {
            endDateInput.value = minEnd;
        }
    });

    function checkConflicts() {
        const selectedDate = tripDateInput.value;
        const selectedPlate = plateInput.value.trim().toLowerCase();
        const selectedDriver = driverInput.value.trim().toLowerCase();
        if (!selectedDate) return;

        bookedSchedule.forEach(trip => {
            if (trip.trip_date === selectedDate) {
                if (selectedPlate && trip.plate_number.toLowerCase() === selectedPlate) {
                    alert(`❌ Conflict: Van with plate "${plateInput.value}" is already booked for this date.`);
                    plateInput.value = '';
                }
                if (selectedDriver && trip.driver_name.toLowerCase() === selectedDriver) {
                    alert(`❌ Conflict: Driver "${driverInput.value}" is already assigned to a trip on this date.`);
                    driverInput.value = '';
                }
            }
        });
    }

    tripDateInput.addEventListener('input', () => {
        checkConflicts();
        if(tripDateInput.value) endDateInput.min = tripDateInput.value;
    });
    plateInput.addEventListener('change', checkConflicts);
    driverInput.addEventListener('change', checkConflicts);
</script>
<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

function fillJoinerVanInfo(select) {
    const opt = select.options[select.selectedIndex];
    const info = document.getElementById('joinerVanInfo');
    if (opt.value) {
        info.value = opt.getAttribute('data-name') + ' | ' + opt.getAttribute('data-plate') + ' | ' + opt.getAttribute('data-seats') + ' pax';
    } else {
        info.value = '';
    }
}

function fillJoinerDriverInfo(select) {
    const opt = select.options[select.selectedIndex];
    const info = document.getElementById('joinerDriverInfo');
    if (opt.value) {
        info.value = opt.getAttribute('data-name') + ' | License: ' + opt.getAttribute('data-license');
    } else {
        info.value = '';
    }
}

flatpickr('#meetup_time', {
    enableTime: true,
    noCalendar: true,
    dateFormat: 'H:i',
    time_24hr: false,
    minuteIncrement: 30,
});
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
