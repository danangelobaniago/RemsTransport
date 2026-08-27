<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Edit Trip | Admin Management</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--bg);
            background-image: radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.05) 0, transparent 50%),
                              radial-gradient(at 50% 0%, rgba(37, 99, 235, 0.03) 0, transparent 50%);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-main);
        }

        .wrapper {
            width: 100%;
            max-width: 700px;
            padding: 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 20px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .edit-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .card-header {
            padding: 32px 40px 0 40px;
        }

        .card-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.025em;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 8px;
        }

        .card-body {
            padding: 40px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .full-width {
            grid-column: span 2;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        input, select {
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
            background-color: #fcfcfc;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background-color: var(--white);
        }

        .btn-save {
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 32px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-save:hover {
            background: var(--primary-hover);
        }

        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <a href="/admin/joiner-trips" class="back-link">
        <i class="fas fa-arrow-left"></i> &nbsp; Back to Joiner Trips
    </a>

    <div class="edit-card">
        <div class="card-header">
            <h1>Edit Trip Details</h1>
            <p>Update information for the trip to <strong>{{ $trip->destination }}</strong></p>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.joiner-trips.update', $trip->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Destination</label>
                        <input type="text" name="destination" value="{{ $trip->destination }}" required>
                    </div>

                    <div class="form-group">
                        <label>Meetup Point</label>
                        <input type="text" name="meetup_point" value="{{ $trip->meetup_point }}" required>
                    </div>

                    <div class="form-group">
                        <label>Trip Date</label>
                        <input type="date" name="trip_date" value="{{ $trip->trip_date }}" required>
                    </div>

                    <div class="form-group">
                        <label>Assigned Van</label>
                        <select name="van" required>
                            @foreach($vans as $van)
                                <option value="{{ $van->name }}" {{ $trip->van == $van->name ? 'selected' : '' }}>
                                    {{ $van->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active"    {{ ($trip->status ?? 'active') == 'active'    ? 'selected' : '' }}>OPEN — Accepting Joiners</option>
                            <option value="inactive"  {{ ($trip->status ?? '')       == 'inactive'  ? 'selected' : '' }}>CLOSED — No More Slots</option>
                            <option value="completed" {{ ($trip->status ?? '')       == 'completed' ? 'selected' : '' }}>COMPLETED</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Total Seats</label>
                        <input type="number" name="total_seats" value="{{ $trip->total_seats }}" required>
                    </div>

                    <div class="form-group">
                        <label>Price Per Seat (₱)</label>
                        <input type="number" name="price_per_seat" value="{{ $trip->price_per_seat }}" required>
                    </div>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-check-circle"></i> Update Trip Information
                </button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
