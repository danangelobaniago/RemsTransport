<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#2563eb">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Rem's Transport">

<title>Rem's Transport | Van Rental Services</title>

<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/icon-192.svg">

<link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ filemtime(public_path('css/responsive.css')) }}">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<!-- NAVBAR -->
<header class="navbar">

<div class="topbar">
    <a href="/" class="logo">Rem's Transport</a>
</div>

<nav>
<a href="#home">Home</a>
<a href="#vans">Vans</a>
<a href="#tours">Tour Package</a>
<a href="#joiners">Joiners</a>
<a href="#features">Features</a>
<a href="#services">Services</a>
<a href="#faq-section">FAQ</a>

<div class="nav-right">

    @auth

    <!-- NOTIFICATION -->
    <div class="notification-wrapper">
        <button class="notif-btn" onclick="toggleNotif(event)">
            <i class="fa fa-bell"></i>
            @if(auth()->user()->unreadNotifications->count())
                <span class="notif-count">{{ auth()->user()->unreadNotifications->count() }}</span>
            @endif
        </button>
        <div class="notif-dropdown" id="notifDropdown">
            @forelse(auth()->user()->notifications as $notif)
                <a href="/notifications/read" class="notif-item {{ $notif->read_at ? '' : 'unread' }}">
                    {{ $notif->data['message'] ?? 'Notification' }}
                </a>
            @empty
                <p class="no-notif">No notifications</p>
            @endforelse
        </div>
    </div>

    <!-- USER MENU -->
    <div class="user-menu">
        <button class="user-btn" onclick="toggleMenu(event)">
            <i class="fa fa-user-circle"></i>
            <span class="user-name-text">{{ auth()->user()->first_name }}</span>
            <i class="fa fa-caret-down"></i>
        </button>
        <div class="dropdown" id="dropdownMenu">
            <a href="/profile" class="dropdown-item"><i class="fa fa-user"></i><span>Profile</span></a>
            <a href="/my-bookings" class="dropdown-item"><i class="fa fa-book"></i><span>My Bookings</span></a>
            <div class="dropdown-divider"></div>
            <form method="POST" action="/logout" style="margin:0;">
                @csrf
                <button type="submit" class="dropdown-item logout">
                    <i class="fa fa-sign-out-alt"></i><span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    @else
        <a href="/login" style="color:white;font-size:14px;">Login</a>
    @endauth

    <!-- Hamburger — mobile only -->
    <button class="nav-hamburger" onclick="toggleMobileNav(event)" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>

</div>
</nav>

</header>

<!-- Mobile nav dropdown (hidden by default) -->
<div class="mobile-nav-menu" id="mobileNavMenu" style="display:none;">
    <a href="#home" onclick="closeMobileNav()">Home</a>
    <a href="#vans" onclick="closeMobileNav()">Vans</a>
    <a href="#tours" onclick="closeMobileNav()">Tour Package</a>
    <a href="#joiners" onclick="closeMobileNav()">Joiners</a>
    <a href="#features" onclick="closeMobileNav()">Features</a>
    <a href="#services" onclick="closeMobileNav()">Services</a>
    <a href="#faq-section" onclick="closeMobileNav()">FAQ</a>
</div>



<!-- HERO CAROUSEL -->
<section id="home" class="hero">

    <div class="hero-carousel">
        <div class="carousel-slide active" style="background-image: url('{{ asset('image/hero-1.jpg') }}')"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('image/hero-2.jpg') }}')"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('image/hero-3.jpg') }}')"></div>
        <div class="carousel-slide" style="background-image: url('{{ asset('image/hero-4.jpg') }}')"></div>
    </div>

    <div class="hero-overlay"></div>

    <div class="hero-content">
        <span class="hero-badge">Philippines' Trusted Van Rental</span>
        <h1>Rem's Transport</h1>
        <div class="hero-divider"></div>
        <p>Travel Easy, Travel Smart</p>

        @auth
            <a href="#vans" class="hero-btn">Book Now <i class="fas fa-arrow-right"></i></a>
        @else
            <a href="/login" class="hero-btn">Book Now <i class="fas fa-arrow-right"></i></a>
        @endauth
    </div>

    <button class="carousel-arrow carousel-prev" onclick="heroCarousel(-1)">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="carousel-arrow carousel-next" onclick="heroCarousel(1)">
        <i class="fas fa-chevron-right"></i>
    </button>

    <div class="carousel-dots">
        <span class="carousel-dot active" onclick="heroGoTo(0)"></span>
        <span class="carousel-dot" onclick="heroGoTo(1)"></span>
        <span class="carousel-dot" onclick="heroGoTo(2)"></span>
        <span class="carousel-dot" onclick="heroGoTo(3)"></span>
    </div>

</section>

<!-- VANS -->
<section id="vans" class="vans-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Vans</h2>
            <p class="section-subtitle">Choose the perfect vehicle for your group's comfort and style.</p>
        </div>

        <div class="van-grid">
            @foreach($vans as $van)
            <div class="van-card">
                <div class="van-image-box">
                    <img src="{{ \Storage::disk('public')->url($van->image) }}" alt="{{ $van->name }}">
                    <div class="van-badge">Available</div>
                </div>

                <div class="van-info">
                    <h3>{{ $van->name }}</h3>

                    <div class="van-meta">
                        <span><i class="fas fa-cog"></i> {{ $van->transmission }}</span>
                        <span><i class="fas fa-snowflake"></i> Aircon</span>
                    </div>

                    <div class="van-price">
                        <small>Daily Rate</small>
                        <p>₱{{ number_format($van->price_min) }} - ₱{{ number_format($van->price_max) }}</p>
                    </div>

                    <a href="/van/details/{{ $van->id }}" class="btn-industry">
                        View Details <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- TOUR PACKAGE -->
<section id="tours" class="tours-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Tour Packages</h2>
            <p class="section-subtitle">Fixed destinations with assigned vans for a hassle-free trip.</p>
        </div>

        <div class="tours-grid">
            @forelse($tours as $tour)
                @php
                    $isBooked = $tour->is_fully_booked ?? false;
                @endphp

                <div class="tour-card {{ $isBooked ? 'tour-locked' : '' }}">
                    <div class="tour-image">
                        <img src="{{ \Storage::disk('public')->url($tour->image) }}" alt="{{ $tour->name }}" style="{{ $isBooked ? 'filter: grayscale(1); opacity: 0.7;' : '' }}">

                        @if($isBooked)
                            <div class="booked-overlay" style="position: absolute; top: 10px; right: 10px; background: #ef4444; color: white; padding: 5px 12px; border-radius: 5px; font-weight: 800; font-size: 12px; z-index: 10;">
                                <i class="fas fa-lock"></i> FULLY BOOKED
                            </div>
                        @elseif($tour->is_best_seller)
                            <div class="best-seller-badge">
                                <i class="fas fa-star"></i> Best Seller
                            </div>
                        @endif

                        <div class="tour-price-tag">₱{{ number_format($tour->price) }}</div>
                    </div>

                    <div class="tour-details">
                        <div class="tour-meta">
                            <span class="meta-item"><i class="fas fa-clock"></i> {{ $tour->duration }}</span>
                            <span class="meta-item"><i class="fas fa-users"></i> Max {{ $tour->max_passengers }}</span>
                        </div>

                        <h3 class="tour-name">{{ $tour->name }}</h3>
                        <p class="tour-destination"><i class="fas fa-map-marker-alt"></i> {{ $tour->destination }}</p>

                        <div class="tour-footer">
                            <div class="tour-date">
                                <small>Available Dates:</small>
                                <span>{{ date('M d', strtotime($tour->tour_date)) }} – {{ date('M d, Y', strtotime($tour->end_date ?? $tour->tour_date)) }}</span>
                            </div>

                            @if($isBooked)
                                <button class="btn-book" disabled style="background: #94a3b8; cursor: not-allowed; border: none;">Already Booked</button>
                            @else
                                <a href="/tour/details/{{ $tour->id }}" class="btn-book">Book Now</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="no-tours">
                    <p>No tour packages available at the moment. Check back soon!</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- messages -->
<section id="joiners" class="joiners-section">
    <div class="container">
        <div class="section-header">
            <span class="sub-title">Share the Ride</span>
            <h2>Joiner Trips</h2>
            <p>Perfect for solo travelers. Book a seat and meet new friends on the road.</p>
        </div>

        <div class="joiner-grid">
            @forelse($joinerTrips as $trip)
                @php
                    $isFull = $trip->available_seats <= 0;
                @endphp
                <div class="joiner-card">
                    <div class="trip-badge {{ $isFull ? 'status-full' : 'status-open' }}">
                        {{ $isFull ? 'Full' : 'Open' }}
                    </div>

                    <div class="joiner-content">
                        <h3 class="destination">📍 {{ $trip->destination }}</h3>

                        <div class="trip-info">
                            <div class="info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>{{ \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-chair"></i>
                                <span>{{ $trip->available_seats }} / {{ $trip->total_seats }} Seats Left</span>
                            </div>
                        </div>

                        <div class="joiner-footer">
                            <div class="price-tag">
                                <small>Price per seat</small>
                                <p>₱{{ number_format($trip->price_per_seat) }}</p>
                            </div>

                            {{-- CHANGED: Button now links to the details page --}}
                            @if($isFull)
                                <button class="btn-action full" disabled>Fully Booked</button>
                            @else
                                <a href="{{ route('joiner.show', $trip->id) }}" class="btn-action join">Join Now</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <p>No joiner trips scheduled at the moment. Check back soon!</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
<!-- FEATURES -->
</section>

<section id="features" class="features">

<div class="container">

<div class="section-header">
            <span class="sub-title">Why Choose Us</span>
            <h2>Our Features</h2>
            <p>We provide high-quality transport solutions tailored to your needs.</p>
        </div>

<div class="features-grid">

<div class="feature-box">
<i class="fas fa-user-tie icon"></i>
<h3>Professional Drivers</h3>
<p>Our trained drivers ensure safe and comfortable travel.</p>
</div>

<div class="feature-box">
<i class="fas fa-shield-halved icon"></i>
<h3>Safety Secure and Comfort</h3>
<p>Your safety is our top priority while providing comfort.</p>
</div>

<div class="feature-box">
<i class="fas fa-screwdriver-wrench icon"></i>
<h3>Maintained Vehicles</h3>
<p>All vehicles are regularly inspected and maintained.</p>
</div>

<div class="feature-box">
<i class="fas fa-file-shield icon"></i>
<h3>Comprehensive Insurance</h3>
<p>Every trip includes insurance coverage for protection.</p>
</div>

<div class="feature-box">
<i class="fas fa-door-open icon"></i>
<h3>Door to Door Pick-up</h3>
<p>Convenient pickup and drop-off at your location.</p>
</div>

<div class="feature-box">
<i class="fas fa-coins icon"></i>
<h3>Affordable Rental Prices</h3>
<p>Reliable service with competitive rental rates.</p>
</div>

</div>

</div>

</section>


<!-- SERVICES -->
<section id="services" class="services">

<h2>Our Services</h2>

<div class="services-grid">

<div class="service-card">
<i class="fas fa-shuttle-van service-icon"></i>
<h3>Van Rental</h3>
<p>Reliable and spacious vans ideal for family trips, group travel, event transport and long-distance journeys.</p>
</div>

<div class="service-card">
<i class="fas fa-plane-arrival service-icon"></i>
<h3>Airport Transfer</h3>
<p>Fast and convenient airport pickup and drop-off with professional drivers.</p>
</div>

<div class="service-card">
<i class="fas fa-map-marked-alt service-icon"></i>
<h3>Tour Packages</h3>
<p>Enjoy comfortable and organized tours to popular destinations.</p>
</div>

<div class="service-card">
<i class="fas fa-calendar-check service-icon"></i>
<h3>Joiners Travel</h3>
<p>Affordable shared van trips for solo travelers and small groups heading to the same destination.</p>
</div>

</div>

</section>

<!-FEEDBACK-!>
<section class="feedback-industry">
    <div class="container">
        <div class="section-header">
            <span class="sub-title">Customer Reviews</span>
            <h2>Latest Feedback</h2>
        </div>

        <div class="slider-relative-wrapper">

            <button class="side-control prev" onclick="manualScroll(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="feedback-slider-container" id="feedbackSlider">
                @forelse($feedbacks as $f)
                    <div class="feedback-item">
                        <div class="feedback-top">
                            <div class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-star {{ $i <= $f->service_rating ? 'fas' : 'far' }}"></i>
                                @endfor
                            </div>
                            <span class="date">{{ \Carbon\Carbon::parse($f->created_at)->diffForHumans() }}</span>
                        </div>

                        <p class="feedback-text">"{{ $f->comment }}"</p>

                        <div class="feedback-user">
                            <div class="avatar-circle">
                                {{ strtoupper(substr($f->first_name, 0, 1)) }}
                            </div>
                            <div class="user-info">
                                <h4>{{ $f->first_name }} {{ $f->last_name }}</h4>
                                <div class="verified">
                                    <i class="fas fa-check-circle"></i> Verified Trip
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>No feedback submitted yet.</p>
                    </div>
                @endforelse
            </div>

            <button class="side-control next" onclick="manualScroll(1)">
                <i class="fas fa-chevron-right"></i>
            </button>

        </div>
    </div>
</section>

<!-FAQ-!>
<section id="faq-section" class="faq-pretty">
    <div class="container">
        <div class="section-header">
            <span class="sub-title">Got Questions?</span>
            <h2>Frequently Asked Questions</h2>
            <div class="header-line"></div>
        </div>

        <div class="faq-container">
            <div class="faq-item">
                <button class="faq-btn" onclick="toggleFaq(this)">
                    <span class="faq-question">What is included in the van rental?</span>
                    <div class="faq-icon">
                        <span class="line"></span>
                        <span class="line"></span>
                    </div>
                </button>
                <div class="faq-body">
                    <div class="faq-text">
                        Our rental includes a professional company driver, air-conditioned vehicle, and safe transportation for tours, airport transfers, and group travel.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" onclick="toggleFaq(this)">
                    <span class="faq-question">How many passengers can the van accommodate?</span>
                    <div class="faq-icon">
                        <span class="line"></span>
                        <span class="line"></span>
                    </div>
                </button>
                <div class="faq-body">
                    <div class="faq-text">
                        Most of our vans can accommodate between 12 to 15 passengers, depending on the van model selected and luggage space required.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" onclick="toggleFaq(this)">
                    <span class="faq-question">Are fuel and toll fees included in the price?</span>
                    <div class="faq-icon">
                        <span class="line"></span>
                        <span class="line"></span>
                    </div>
                </button>
                <div class="faq-body">
                    <div class="faq-text">
                        For standard rentals, fuel, toll fees, and parking fees are usually shouldered by the client unless specifically included in a pre-arranged tour package.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" onclick="toggleFaq(this)">
                    <span class="faq-question">How do joiner trips work?</span>
                    <div class="faq-icon">
                        <span class="line"></span>
                        <span class="line"></span>
                    </div>
                </button>
                <div class="faq-body">
                    <div class="faq-text">
                        Joiner trips allow solo travelers or small groups to share a van to a set destination. You only pay for your seat, making it a very affordable way to travel.
                    </div>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-btn" onclick="toggleFaq(this)">
                    <span class="faq-question">Do I need to book in advance?</span>
                    <div class="faq-icon">
                        <span class="line"></span>
                        <span class="line"></span>
                    </div>
                </button>
                <div class="faq-body">
                    <div class="faq-text">
                        Yes, we recommend booking at least 2-3 days in advance for city transfers and 1 week in advance for out-of-town trips to ensure vehicle availability.
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<footer class="site-footer">
    <div class="footer-top">
        <div class="container">
            <div class="footer-grid">

                <div class="footer-column brand-info">
                    <h3 class="footer-logo">Rem's Transport</h3>
                    <p>Providing safe, reliable, and comfortable van rentals across the Philippines since 2026. Travel easy, travel smart.</p>
                </div>

                <div class="footer-column contact-info">
                    <h4>Contact Us</h4>
                    <ul>
                        <li><i class="fas fa-phone-alt"></i> +63 999 883 4375</li>
                        <li><i class="fas fa-envelope"></i> remstransport1@gmail.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> San Bartolome, Novaliches, QC</li>
                    </ul>
                </div>

                <div class="footer-column quick-links">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#vans">Our Vans</a></li>
                         <li><a href="#tours">Tour Package</a></li>
                        <li><a href="#joiners">Joiner Trips</a></li>
                        <li><a href="#faq-section">FAQ</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2026 Rem's Transport | Van Rental System</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/script.js') }}?v={{ filemtime(public_path('js/script.js')) }}"></script>
<script src="{{ asset('js/pwa.js') }}?v={{ filemtime(public_path('js/pwa.js')) }}"></script>
</body>
</html>
