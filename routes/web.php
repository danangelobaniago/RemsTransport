<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\JoinerTripController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DriverController;




Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/van/details/{id}', function ($id) {
    $van = DB::table('vans')->where('id', $id)->first();
    $pricing = DB::table('pricing')->where('id', 1)->first();
    return view('van-details', compact('van', 'pricing'));
});

Route::get('/tour/details/{id}', [TourController::class, 'show'])->name('tour.details');



// 1. Standard Login/Register
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Staff Login (Admin & Driver)
Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'loginStaff'])->name('admin.login.post');

// 2. Login 2FA Flow (This is what handles your login verification)
Route::get('/verify-login-otp', [AuthController::class, 'showOtpForm'])->name('otp.verify');
Route::post('/verify-login-otp', [AuthController::class, 'verifyLoginOtp'])->name('verify.otp.post');

// 3. Forgot Password Flow
Route::get('/forgot-password', function () { return view('login.forgot'); })->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendOtp']);

// Reset Code Verification for Forgot Password
Route::get('/verify-reset-otp', [AuthController::class, 'showResetOtpVerifyForm'])->name('otp.reset.verify');
Route::post('/verify-reset-otp', [AuthController::class, 'verifyResetOtp']);

// Final Password Reset Form
Route::get('/reset-otp', [AuthController::class, 'showResetOtpForm'])->name('password.reset');
Route::post('/reset-password-otp', [AuthController::class, 'updatePassword'])->name('password.update');

// Resend OTP Utility
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('resend.otp');

// 4. Registration Email Verification Flow
Route::get('/verify-register-otp', [AuthController::class, 'showRegisterOtpForm'])->name('register.otp.verify');
Route::post('/verify-register-otp', [AuthController::class, 'verifyRegisterOtp'])->name('register.otp.post');
Route::post('/resend-register-otp', [AuthController::class, 'resendRegisterOtp'])->name('register.otp.resend');


Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/driver/dashboard', [DriverController::class, 'dashboard'])->name('driver.dashboard');
    Route::post('/driver/trip-status', [DriverController::class, 'updateTripStatus'])->name('driver.trip_status');
    Route::post('/driver/collect-payment', [DriverController::class, 'collectPayment'])->name('driver.collect_payment');
    Route::get('/driver/bookings/{id}/manifest', [DriverController::class, 'rentalManifest'])->name('driver.rental.manifest');
    Route::get('/driver/joiner-trips/{id}', [DriverController::class, 'joinerTripManifest'])->name('driver.joiner.manifest');
    Route::post('/driver/joiner-trips/{id}/status', [DriverController::class, 'updateJoinerTripStatus'])->name('driver.joiner.status');
    Route::post('/driver/joiner-trips/{id}/collect/{bookingId}', [DriverController::class, 'collectJoinerPassengerPayment'])->name('driver.joiner.collect');
    Route::get('/driver/tour-packages/{id}', [DriverController::class, 'tourPackageManifest'])->name('driver.tour.manifest');
    Route::post('/driver/tour-packages/{id}/status', [DriverController::class, 'updateTourPackageStatus'])->name('driver.tour.status');
    Route::post('/driver/tour-packages/{id}/collect/{bookingId}', [DriverController::class, 'collectTourBookingPayment'])->name('driver.tour.collect');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/admin/drivers/create-account/{id}', [AdminController::class, 'createDriverAccount'])->name('admin.drivers.createAccount');
    Route::post('/admin/drivers/remove-account/{id}', [AdminController::class, 'removeDriverAccount'])->name('admin.drivers.removeAccount');
    Route::post('/admin/drivers/change-password/{id}', [AdminController::class, 'changeDriverPassword'])->name('admin.drivers.changePassword');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/booking/{id}', [BookingController::class, 'showBooking'])->name('booking.page');
    Route::get('/booking-multi/{id}', [BookingController::class, 'showMultiBooking'])->name('booking.multi');
    Route::post('/save-booking', [BookingController::class, 'storeBooking']);

    Route::get('/bookings/tour/{tour_id}', [BookingController::class, 'createTourBooking'])->name('bookings.tour_create');
    Route::post('/bookings/tour/pay', [BookingController::class, 'processTourPayment'])->name('bookings.tour_pay');

    Route::post('/paymongo/checkout', [BookingController::class, 'paymongoCheckout']);
    Route::get('/payment-success', [BookingController::class, 'paymentSuccess']);
    Route::get('/receipt/{id}', [BookingController::class, 'show']);

    Route::post('/join-trip/{id}', [BookingController::class, 'joinTrip'])->name('join.trip');

    Route::get('/profile', function () { return view('profile', ['user' => Auth::user()]); });
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/profile/password', [AuthController::class, 'changePassword']);

    Route::get('/my-bookings', function () {
        $bookings = DB::table('bookings')->where('user_id', Auth::id())->latest()->get();
        return view('my-bookings', compact('bookings'));
    });

    Route::post('/booking/cancel/{id}', [BookingController::class, 'cancelBooking']);
    Route::post('/submit-feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});



Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/bookings', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::post('/admin/update-status', [AdminController::class, 'updateStatus'])->name('admin.updateStatus');


    Route::get('/admin/vans', function () {
        $pricing = DB::table('pricing')->where('id', 1)->first();
        return view('admin.vans', [
            'vans' => DB::table('vans')->get(),
            'baseFare' => $pricing ? $pricing->base_fare : 0,
        ]);
    })->name('admin.vans');
    Route::get('/admin/vans/{id}/schedule', [AdminController::class, 'vanSchedule'])->name('admin.vans.schedule');
    Route::post('/admin/vans/add', [AdminController::class, 'addVan'])->name('admin.vans.add');
    Route::get('/admin/vans/delete/{id}', [AdminController::class, 'deleteVan'])->name('admin.vans.delete');
    Route::get('/admin/vans/toggle/{id}', [AdminController::class, 'toggleVan'])->name('admin.vans.toggle');
    Route::post('/admin/vans/maintenance/{id}', [AdminController::class, 'updateVanMaintenance'])->name('admin.vans.maintenance');


    Route::get('/admin/drivers', function () {
        $drivers = DB::table('drivers')
            ->leftJoin('users', 'drivers.user_id', '=', 'users.id')
            ->select('drivers.*', 'users.email as account_email')
            ->get();
        return view('admin.drivers', compact('drivers'));
    })->name('admin.drivers');
    Route::get('/admin/drivers/{id}/schedule', [AdminController::class, 'driverSchedule'])->name('admin.drivers.schedule');
    Route::get('/admin/drivers/edit/{id}', [AdminController::class, 'editDriver'])->name('admin.drivers.edit');
    Route::post('/admin/drivers/update/{id}', [AdminController::class, 'updateDriver'])->name('admin.drivers.update');
    Route::get('/admin/drivers/delete/{id}', [AdminController::class, 'deleteDriver'])->name('admin.drivers.delete');
    Route::post('/admin/drivers/add', [AdminController::class, 'addDriver'])->name('admin.drivers.add');

    Route::get('/admin/tours', [HomeController::class, 'manageTours'])->name('admin.tours');
    Route::post('/admin/tours/add', [TourController::class, 'store']);
    Route::get('/admin/tours/delete/{id}', [HomeController::class, 'deleteTour'])->name('admin.tours.delete');
    Route::get('/admin/tours', [TourController::class, 'index']);
    Route::get('/admin/customers', [AdminController::class, 'customers'])->name('admin.customers');
    Route::get('/admin/joiner-trips', [AdminController::class, 'joinerTrips'])->name('admin.joiner');
    Route::get('/admin/pricing', [AdminController::class, 'pricing']);
    Route::post('/admin/pricing/update', [AdminController::class, 'updatePricing']);

    //joiners
    Route::post('/admin/drivers/add', [AdminController::class, 'addDriver'])->name('admin.drivers.add');
    Route::get('/admin/joiner-trips/edit/{id}', [AdminController::class, 'editJoinerTrip'])->name('admin.joiner-trips.edit');
    Route::put('/admin/joiner-trips/update/{id}', [AdminController::class, 'updateJoinerTrip'])->name('admin.joiner-trips.update');
    Route::delete('/admin/joiner-trips/{id}', [AdminController::class, 'deleteJoinerTrip'])->name('admin.joiner-trips.delete');
    Route::get('/admin/joiner-trips/{id}/passengers', [JoinerTripController::class, 'viewPassengers'])->name('admin.joiner-trips.passengers');
    });

/* ================= UTILITY ================= */
Route::get('/booked-dates/{vanId}', [BookingController::class, 'getBookedDates']);
Route::get('/available-drivers', [BookingController::class, 'getAvailableDrivers']);
Route::post('/paymongo/webhook', [BookingController::class, 'paymongoWebhook']);

// Step 2: Show this page (GET)
Route::get('/verify-login-otp', [AuthController::class, 'showOtpForm'])->name('otp.verify');

// Step 2: Handle the button click (POST)
// Note: name('verify.otp.post') matches the form above
Route::post('/verify-login-otp', [AuthController::class, 'verifyLoginOtp'])->name('verify.otp.post');

// Step 1: Forgot Password Page (Current)
Route::get('/forgot-password', function () { return view('login.forgot'); })->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendOtp']);

// Step 2: Input OTP Page (The one you want next)
Route::get('/verify-reset-otp', [AuthController::class, 'showResetOtpVerifyForm'])->name('otp.reset.verify');
Route::post('/verify-reset-otp', [AuthController::class, 'verifyResetOtp']);

Route::middleware(['auth'])->group(function () {
    // ... other routes ...

    // Change the URL to /book to match your blade file
    Route::post('/book', [BookingController::class, 'storeBooking'])->name('booking.save');
});

Route::middleware(['auth'])->group(function () {
    // This receives the data from booking.blade.php and shows passenger.blade.php
    Route::post('/book', [BookingController::class, 'showPassengerForm'])->name('booking.passengers');

    // This will handle the final checkout
    Route::post('/paymongo/checkout', [BookingController::class, 'paymongoCheckout']);
});

Route::middleware(['auth'])->group(function () {
    // ... other profile routes
    Route::post('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');
});

Route::prefix('admin')->group(function () {
    // This loads the page
    Route::get('/joiner-trips', [JoinerTripController::class, 'index'])->name('admin.joiner-trips');

    // This FIXES the 404 by handling the form submission
    Route::post('/joiner-trips', [JoinerTripController::class, 'store'])->name('admin.joiner-trips.store');
});

// Add this route
Route::post('/join-trip/{id}', [App\Http\Controllers\JoinerTripController::class, 'join'])->name('join-trip');
Route::get('/join-trip/{id}', [JoinerTripController::class, 'show'])->name('joiner.show');
Route::middleware(['auth'])->group(function () {
    // This matches the link in your navbar
    Route::get('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Optional: for a "See All" page
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});


// View the details
Route::get('/join-trip/{id}', [JoinerTripController::class, 'show'])->name('joiner.show');

// Process the join request (POST)
Route::post('/join-trip/{id}', [JoinerTripController::class, 'join'])->name('join-trip');



// Finalize the booking (Move your previous 'join' logic here)
Route::post('/joiner/book/{id}', [JoinerTripController::class, 'processBooking'])->name('joiner.book');
Route::get('/joiner/passenger-details/{id}', [JoinerTripController::class, 'passengerForm'])->name('joiner.passengerForm');


Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'index'])->name('dashboard');



});

// Route for toggling driver status
Route::get('/admin/drivers/toggle/{id}', [AdminController::class, 'toggleDriverStatus'])->name('admin.drivers.toggle');

Route::get('/my-bookings', [BookingController::class, 'showMyBookings'])->middleware('auth');
Route::get('/joiner/payment-success/{payment_id}', [JoinerTripController::class, 'paymentSuccess']);
Route::get('/joiner-receipt/{id}', [JoinerTripController::class, 'showReceipt'])->name('joiner.receipt');
Route::post('/admin/joiner-trips/{id}/complete', [JoinerTripController::class, 'completeTrip'])->name('admin.joiner.complete');
Route::post('/admin/joiner-trips/{id}/approve', [AdminController::class, 'approveJoinerTrip'])->name('admin.joiner.approve');
Route::post('/admin/joiner-trips/{id}/reject', [AdminController::class, 'rejectJoinerTrip'])->name('admin.joiner.reject');
Route::get('/my-bookings', [JoinerTripController::class, 'myBookings']);
Route::post('/submit-feedback', [App\Http\Controllers\JoinerTripController::class, 'submitFeedback']);

Route::get('/tour/details/{id}', [TourController::class, 'show']);

Route::post('/bookings/tour/pay', [TourController::class, 'tour_pay'])->name('bookings.tour_pay');

// Find this line and change it:
Route::get('/my-bookings', [TourController::class, 'showMyBookings'])->name('my.bookings');
Route::delete('/admin/tours/delete/{id}', [TourController::class, 'delete'])->name('admin.tours.delete');
// In routes/web.php
Route::get('/admin/tours/manifest/{id}', [HomeController::class, 'getManifest']);
Route::post('/admin/tours/update/{id}', [HomeController::class, 'updateTour']);
// Add this inside your admin middleware group
Route::post('/admin/tours/complete/{id}', [HomeController::class, 'completeTour']);

// Ensure this is Route::post
Route::post('/bookings/cancel/{id}/{type}', [BookingController::class, 'cancelBooking'])->name('booking.cancel');
Route::get('/admin/reports', [AdminController::class, 'reports'])->name('admin.reports');
Route::get('/admin/tours/manifest/{id}', [TourController::class, 'getManifest']);
Route::get('/admin/tours/{id}/bookings', [TourController::class, 'getBookingsJson'])->name('admin.tour.bookings.json');
Route::post('/admin/tour-bookings/{id}/approve', [TourController::class, 'approveTourBooking'])->name('admin.tour.booking.approve');
Route::post('/admin/tour-bookings/{id}/reject', [TourController::class, 'rejectTourBooking'])->name('admin.tour.booking.reject');
Route::get('/receipt/{id}', [BookingController::class, 'showReceipt']);
