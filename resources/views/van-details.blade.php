<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>{{ $van->name }} | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #2563eb;
            --success: #16a34a;
            --dark: #0f172a;
            --light: #f8fafc;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background-color: #f1f5f9; color: var(--dark); line-height: 1.6; }

        .navbar {
            background: #ffffff;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo { font-size: 20px; font-weight: 800; color: var(--dark); text-decoration: none; }
        .back-link { text-decoration: none; color: var(--primary); font-weight: 600; font-size: 14px; }

        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1.8fr 1.2fr;
            gap: 30px;
        }

        /* LEFT SIDE: VEHICLE SHOWCASE */
        .vehicle-display {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .image-hero {
            width: 100%;
            height: 450px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .image-hero img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* Shows whole van without cropping */
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.1));
        }

        .vehicle-info { padding: 40px; }
        .badge-status {
            display: inline-block;
            background: #dcfce7;
            color: var(--success);
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 15px;
        }

        .vehicle-info h1 { font-size: 48px; font-weight: 800; letter-spacing: -1.5px; margin-bottom: 10px; }
        .sub-text { color: #64748b; font-size: 18px; margin-bottom: 30px; }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .spec-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid var(--border);
        }

        .spec-card i { color: var(--primary); font-size: 24px; margin-bottom: 10px; display: block; }
        .spec-card span { display: block; font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; }
        .spec-card p { font-weight: 700; font-size: 15px; margin-top: 4px; }

        /* RIGHT SIDE: PRICING & BOOKING */
        .booking-sidebar {
            background: white;
            padding: 35px;
            border-radius: 24px;
            border: 1px solid var(--border);
            height: fit-content;
            position: sticky;
            top: 100px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.02);
        }

        .price-label { font-size: 14px; color: #64748b; font-weight: 600; margin-bottom: 5px; }
        .price-range { font-size: 32px; font-weight: 800; color: var(--dark); margin-bottom: 25px; }

        .info-box {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
        }

        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { color: #64748b; }
        .info-value { font-weight: 700; }

        .btn-reserve {
            display: block;
            width: 100%;
            background: var(--primary);
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-reserve:hover {
            background: #1d4ed8;
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2);
        }

        .desc-section { margin-top: 40px; }
        .desc-section h2 { font-size: 20px; margin-bottom: 15px; }
        .desc-section p { color: #475569; line-height: 1.8; }

        @media (max-width: 960px) {
            .main-container { grid-template-columns: 1fr; }
            .image-hero { height: 300px; }
        }
    </style>
</head>

<body>

    <header class="navbar">
    <div class="nav-content">
        <a href="/" class="back-link">
            <i class="fa-solid fa-chevron-left"></i>
            <span>Back</span>
        </a>
    </div>
</header>

    <main class="main-container">

        <section>
            <div class="vehicle-display">
                <div class="image-hero">
                    <img src="{{ \Storage::disk('public')->url($van->image) }}" alt="{{ $van->name }}">
                </div>

                <div class="vehicle-info">
                    <span class="badge-status">Available</span>
                    <h1>{{ $van->name }}</h1>
                    <p class="sub-text">Premium Van Rental with Professional Driver</p>

                    <div class="specs-grid">
                        <div class="spec-card">
                            <i class="fa-solid fa-users"></i>
                            <span>Capacity</span>
                            <p>{{ $van->seats }} Pax</p>
                        </div>
                        <div class="spec-card">
                            <i class="fa-solid fa-gear"></i>
                            <span>Type</span>
                            <p>{{ $van->transmission }}</p>
                        </div>
                        <div class="spec-card">
                            <i class="fa-solid fa-gas-pump"></i>
                            <span>Fuel</span>
                            <p>Diesel</p>
                        </div>
                        <div class="spec-card">
                            <i class="fa-solid fa-snowflake"></i>
                            <span>Cooling</span>
                            <p>Dual AC</p>
                        </div>
                    </div>

                    <div class="desc-section">
                        <h2>Vehicle Overview</h2>
                        <p>
                            Experience unparalleled comfort with the {{ $van->name }}. This vehicle is meticulously
                            maintained to ensure a smooth, safe, and reliable journey for your group. Whether it's
                            for business trips, family vacations, or airport transfers, our professional drivers
                            are ready to provide a first-class transport experience.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <aside>
            <div class="booking-sidebar">
                <p class="price-label">Rental Base Fare Price Range</p>
                <div class="price-range">₱{{ number_format($van->price_min) }} - {{ number_format($van->price_max) }}</div>

                <div class="info-box">
                    <div class="info-row">
                        <span class="info-label">Base Fare:</span>
                        <span class="info-value">₱{{ number_format($pricing->base_fare ?? 0) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Driver Fee:</span>
                        <span class="info-value">Included</span>
                    </div>
                </div>

                <a href="{{ route('booking.page', $van->id) }}" class="btn-reserve">
                    <i class="fa-solid fa-map-marker-alt" style="margin-right:8px;"></i> Single Destination
                </a>
                <a href="{{ route('booking.multi', $van->id) }}" class="btn-reserve" style="margin-top:10px;background:#7c3aed;">
                    <i class="fa-solid fa-route" style="margin-right:8px;"></i> Multi-Destination Booking
                </a>
            </div>
        </aside>

    </main>
<script src="/js/pwa.js"></script>
</body>
</html>
