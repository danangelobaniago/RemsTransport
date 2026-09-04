<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReceiptMail;
use App\Http\Traits\BookingValidator; // 1. Import the Trait

class JoinerTripController extends Controller
{
    use BookingValidator; // 2. Use the Trait inside the class

    /**
     * Store a new Joiner Trip (Admin Side)
     */
    // app/Http/Controllers/JoinerTripController.php

public function store(Request $request)
{
    $request->validate([
        'name'           => 'required|string|max:255',
        'destination'    => 'required|string',
        'description'    => 'nullable|string',
        'image'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'meetup_point'   => 'required|string',
        'meetup_time'    => 'required|string',
        'trip_date'      => 'required|date|after_or_equal:today',
        'end_date'       => 'required|date|after_or_equal:trip_date',
        'price_per_seat' => 'required|numeric',
        'total_seats'    => 'required|integer',
        'van_id'         => 'required|exists:vans,id',
        'driver_id'      => 'required|exists:drivers,id',
    ]);

    $van    = DB::table('vans')->where('id', $request->van_id)->first();
    $driver = DB::table('drivers')->where('id', $request->driver_id)->first();

    $imagePath = $request->hasFile('image') ? $request->file('image')->store('joiner-trips', 'public') : null;

    $conflict = DB::table('joiner_trips')
        ->where('trip_date', $request->trip_date)
        ->where(function($q) use ($van, $driver) {
            $q->where('plate_number', $van->plate_number)
              ->orWhere('driver_name', $driver->name);
        })->first();

    if ($conflict) {
        $msg = ($conflict->plate_number === $van->plate_number)
            ? "Van ({$van->plate_number}) is already scheduled on this date."
            : "Driver ({$driver->name}) is already assigned on this date.";
        return back()->withInput()->with('error', '❌ ' . $msg);
    }

    DB::table('joiner_trips')->insert([
        'name'                  => $request->name,
        'destination'           => $request->destination,
        'description'           => $request->description,
        'image'                 => $imagePath,
        'meetup_point'          => $request->meetup_point,
        'meetup_time'           => $request->meetup_time,
        'trip_date'             => $request->trip_date,
        'end_date'              => $request->end_date,
        'price_per_seat'        => $request->price_per_seat,
        'total_seats'           => $request->total_seats,
        'available_seats'       => $request->total_seats,
        'van'                   => $van->name,
        'plate_number'          => $van->plate_number,
        'driver_name'           => $driver->name,
        'driver_license_number' => $driver->license_number,
        'driver_license_image'  => $driver->license_image,
        'status'                => 'active',
        'created_at'            => now(),
        'updated_at'            => now(),
    ]);

    return back()->with('success', 'Joiner trip scheduled successfully!');
}


    public function show($id)
    {
        $trip = DB::table('joiner_trips')->where('id', $id)->first();
        if (!$trip) { abort(404); }

        // Dynamic seat count — same logic as home page
        $bookingIds = DB::table('joiner_bookings')
            ->where('joiner_trip_id', $id)
            ->whereIn('status', ['paid', 'pending', 'downpayment_paid', 'fully_paid'])
            ->pluck('id');

        $occupied = DB::table('joiner_passengers')
            ->whereIn('joiner_booking_id', $bookingIds)
            ->count();

        if ($occupied === 0) {
            $occupied = $bookingIds->count();
        }

        $trip->available_seats = max(0, $trip->total_seats - $occupied);

        return view('joiner_details', compact('trip'));
    }

    public function passengerForm(Request $request, $id)
    {
        // Fetch the trip details
        $trip = DB::table('joiner_trips')->where('id', $id)->first();

        // Safety check: if trip doesn't exist
        if (!$trip) {
            return redirect('/')->with('error', 'Trip not found.');
        }
        if ($trip->trip_date < date('Y-m-d')) {
            return redirect('/')->with('error', 'This trip has already passed and can no longer be booked.');
        }

        // Get seats from URL (?seats=5), default to 1 if not found
        $seats = $request->query('seats', 1);

        // Optional: Backend validation to ensure seats don't exceed 5 or van capacity
        $seats = min(max($seats, 1), 5, $trip->available_seats);

        return view('joiner_passenger_form', compact('trip', 'seats'));
    }

public function index()
{
    $trips = DB::table('joiner_trips')->get()->map(function($trip) {
        $bookingIds = DB::table('joiner_bookings')
            ->where('joiner_trip_id', $trip->id)
            ->whereIn('status', ['paid', 'pending', 'downpayment_paid', 'fully_paid'])
            ->pluck('id');

        // Count actual seats: one row per person in joiner_passengers
        $occupied = DB::table('joiner_passengers')
            ->whereIn('joiner_booking_id', $bookingIds)
            ->count();

        // Fall back to booking count if joiner_passengers has no records yet
        if ($occupied === 0) {
            $occupied = $bookingIds->count();
        }

        $trip->available_seats = $trip->total_seats - $occupied;

        // All balances collected = no active booking is still unpaid
        $trip->all_paid = $bookingIds->isNotEmpty() && !DB::table('joiner_bookings')
            ->whereIn('id', $bookingIds)
            ->whereNotIn('status', ['fully_paid', 'completed', 'cancelled'])
            ->exists();

        return $trip;
    });

    $vans    = DB::table('vans')->where('status', 'available')->get();
    $drivers = DB::table('drivers')->where('status', 'available')->get();

    $bookedSchedule = DB::table('joiner_trips')
        ->select('trip_date', 'plate_number', 'driver_name')
        ->get();

    return view('admin.joiner_trips', compact('trips', 'vans', 'drivers', 'bookedSchedule'));
}

public function processBooking(Request $request, $id)
    {
        // 1. IMPROVED VALIDATION
        // We use '.*' to validate every item inside the passenger arrays
        $request->validate([
            'passenger_name'     => 'required|array|min:1',
            'passenger_name.*'   => 'required|string|max:255', // Removed strict regex to allow dots/dashes
            'passenger_contact'  => 'required|array',
            'passenger_contact.*'=> ['required', 'regex:/^09\d{9}$/'],
            'passenger_birthday' => 'required|array',
            'passenger_birthday.*' => 'required|date',
            'passenger_gender'   => 'required|array',
            'payment_option'     => 'required|in:downpayment,full',
        ]);

        $trip = DB::table('joiner_trips')->where('id', $id)->first();
        if (!$trip) {
            return redirect()->back()->with('error', 'Trip not found.');
        }
        if ($trip->trip_date < date('Y-m-d')) {
            return redirect('/')->with('error', 'This trip has already passed and can no longer be booked.');
        }

        // 2. CALCULATE TOTALS BASED ON SEATS
        $seatsBooked = count($request->passenger_name);
        $totalPackagePrice = $trip->price_per_seat * $seatsBooked;

        // 3. DETERMINE CHARGE AMOUNT (Full vs 20%)
        if ($request->payment_option === 'full') {
            $amountToCharge = $totalPackagePrice;
            $description = "Full Payment for " . $trip->destination . " ($seatsBooked seats)";
        } else {
            $amountToCharge = $totalPackagePrice * 0.20;
            $description = "20% Reservation Fee for " . $trip->destination . " ($seatsBooked seats)";
        }

        $amountInCents = (int)($amountToCharge * 100);
        $reference = 'JOIN-' . time() . '-' . Auth::id();

        // If an admin is booking this on behalf of a walk-in customer, attribute
        // the booking to that customer instead of the logged-in admin.
        $bookingUserId = session('admin_acting_customer_id') ?? Auth::id();

        // 4. CALL PAYMONGO
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
        ])->post('https://api.paymongo.com/v1/checkout_sessions', [
            'data' => [
                'attributes' => [
                    'send_email_receipt' => true,
                    'show_description' => true,
                    'description' => $description,
                    'line_items' => [
                        [
                            'currency' => 'PHP',
                            'amount' => $amountInCents,
                            'description' => "Booking for $seatsBooked seat(s)",
                            'name' => ($request->payment_option === 'full') ? "Full Payment" : "Reservation Fee (20%)",
                            'quantity' => 1,
                        ]
                    ],
                    'payment_method_types' => ['gcash', 'card'],
                    'success_url' => url('/joiner/payment-success/' . $reference),
                    'cancel_url' => url('/joiner/passenger-details/' . $id . '?seats=' . $seatsBooked),
                ]
            ]
        ]);

        if ($response->failed()) {
            return redirect()->back()->with('error', 'Payment gateway error. Please try again.');
        }

        $checkoutUrl = $response->json()['data']['attributes']['checkout_url'];
        $checkoutSessionId = $response->json()['data']['id'];

        // 5. DATABASE OPERATIONS
        DB::beginTransaction();
        try {
            // Insert Main Booking
            $bookingId = DB::table('joiner_bookings')->insertGetId([
                'user_id'              => $bookingUserId,
                'joiner_trip_id'       => $id,
                'passenger_name'       => $request->passenger_name[0],
                'passenger_contact'    => $request->passenger_contact[0],
                'payment_id'           => $reference,
                'checkout_session_id'  => $checkoutSessionId,
                'total_price'          => $totalPackagePrice,
                'downpayment'          => $amountToCharge,
                'status'               => 'pending',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            // Insert All Passengers into Manifesto/Passengers table
            foreach($request->passenger_name as $key => $name) {
                DB::table('joiner_passengers')->insert([
                    'joiner_booking_id' => $bookingId,
                    'name'              => $name,
                    'contact'           => $request->passenger_contact[$key],
                    'birthday'          => $request->passenger_birthday[$key],
                    'gender'            => $request->passenger_gender[$key],
                    'created_at'        => now(),
                ]);
            }

            DB::commit();
            return redirect()->away($checkoutUrl);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Database error: ' . $e->getMessage());
        }
    }

    public function paymentSuccess($reference)
    {
        $booking = DB::table('joiner_bookings')->where('payment_id', $reference)->first();

        if ($booking) {
            $newStatus = ($booking->downpayment >= $booking->total_price) ? 'fully_paid' : 'downpayment_paid';

            $paymentMethod = $this->getPaymongoPaymentMethod($booking->checkout_session_id);

            DB::table('joiner_bookings')
                ->where('payment_id', $reference)
                ->update([
                    'status'         => $newStatus,
                    'payment_method' => $paymentMethod,
                    'updated_at'     => now(),
                ]);

            try {
                $user = DB::table('users')->find($booking->user_id);
                $trip = DB::table('joiner_trips')->find($booking->joiner_trip_id);
                if ($user && $user->email) {
                    Mail::to($user->email)->send(new PaymentReceiptMail(
                        trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                        'Joiner Trip',
                        $trip ? $trip->destination : 'Joiner Trip',
                        $booking->payment_id ?? $reference,
                        (float) $booking->downpayment,
                        $paymentMethod,
                        now()->format('F d, Y h:i A'),
                        $newStatus
                    ));
                }
            } catch (\Exception $e) {
                \Log::error('Payment receipt email failed (joiner): ' . $e->getMessage());
            }

            // If this booking belongs to someone other than the person completing
            // checkout, an admin made it on behalf of a walk-in customer — send them
            // back to the admin panel instead of the customer-facing my-bookings page.
            if (session('admin_acting_customer_id') || (int) $booking->user_id !== (int) Auth::id()) {
                session()->forget('admin_acting_customer_id');
                $ownerName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'the customer';
                return redirect('/admin/joiner-trips')->with('success', "Booking created for {$ownerName}.");
            }

            return redirect('/my-bookings')->with('success', 'Payment successful! Your seats are reserved.');
        }

        return redirect('/my-bookings')->with('error', 'Booking record not found.');
    }

    private function getPaymongoPaymentMethod(?string $sessionId): string
    {
        if (!$sessionId) return 'Paymongo';

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
        ])->get("https://api.paymongo.com/v1/checkout_sessions/{$sessionId}");

        if ($response->failed()) return 'Paymongo';

        $payments = $response->json()['data']['attributes']['payments'] ?? [];

        if (empty($payments)) return 'Paymongo';

        $type = $payments[0]['data']['attributes']['source']['type'] ?? null;

        return match($type) {
            'gcash'     => 'GCash',
            'card'      => 'Credit/Debit Card',
            'paymaya'   => 'Maya',
            'grab_pay'  => 'GrabPay',
            'billease'  => 'BillEase',
            default     => 'Paymongo',
        };
    }

    public function viewPassengers($id)
{
    $trip = DB::table('joiner_trips')->where('id', $id)->first();
    if (!$trip) { abort(404); }

    $bookings = DB::table('joiner_bookings')
        ->join('users', 'joiner_bookings.user_id', '=', 'users.id')
        ->where('joiner_trip_id', $id)
        ->select('joiner_bookings.*', 'users.first_name', 'users.last_name', 'users.email')
        ->orderBy('joiner_bookings.created_at')
        ->get()
        ->map(function ($b) {
            $b->passengers  = DB::table('joiner_passengers')->where('joiner_booking_id', $b->id)->get();
            $b->actual_paid = ($b->amount_paid > 0) ? $b->amount_paid : ($b->downpayment ?? 0);
            $b->balance     = $b->total_price - $b->actual_paid;
            return $b;
        });

    return view('admin.joiner_passengers', compact('trip', 'bookings'));
}

public function payBalanceCheckout(Request $request, $id)
{
    $request->validate([
        'payment_method' => 'required|in:gcash,card',
    ]);

    $booking = DB::table('joiner_bookings')->where('id', $id)->first();
    if (!$booking) {
        abort(404);
    }

    $totalPaid = (float) ($booking->downpayment ?? 0);
    $balance   = max(0, (float) $booking->total_price - $totalPaid);

    if ($balance <= 0) {
        return redirect("/joiner-receipt/{$id}")->with('error', 'This booking has no remaining balance.');
    }

    $amountInCents = (int) round($balance * 100);
    if ($amountInCents < 10000) {
        $amountInCents = 10000;
    }

    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
    ])->post('https://api.paymongo.com/v1/checkout_sessions', [
        'data' => [
            'attributes' => [
                'line_items' => [[
                    'currency' => 'PHP',
                    'amount'   => $amountInCents,
                    'name'     => 'Joiner Trip Balance Payment (Booking #' . $id . ')',
                    'quantity' => 1,
                ]],
                'payment_method_types' => [$request->payment_method],
                'success_url' => url("/joiner-booking/{$id}/pay-balance/success"),
                'cancel_url'  => url("/joiner-receipt/{$id}"),
            ]
        ]
    ]);

    if ($response->failed()) {
        return redirect("/joiner-receipt/{$id}")->with('error', 'Payment gateway error. Please try again.');
    }

    session([
        'pending_joiner_balance_checkout' => [
            'booking_id' => $id,
            'amount'     => $balance,
            'session_id' => $response['data']['id'],
        ],
    ]);

    return redirect($response['data']['attributes']['checkout_url']);
}

public function payBalanceSuccess(Request $request, $id)
{
    $pending = session('pending_joiner_balance_checkout');

    if (!$pending || (string) $pending['booking_id'] !== (string) $id) {
        return redirect("/joiner-receipt/{$id}")->with('error', 'Payment session expired.');
    }

    $paymentMethod = $this->getPaymongoPaymentMethod($pending['session_id']);

    $booking = DB::table('joiner_bookings')->where('id', $id)->first();
    if (!$booking) {
        abort(404);
    }

    $newDownpayment = (float) ($booking->downpayment ?? 0) + (float) $pending['amount'];
    $newBalance     = max(0, (float) $booking->total_price - $newDownpayment);
    $newStatus      = $newBalance <= 0 ? 'fully_paid' : 'downpayment_paid';

    DB::table('joiner_bookings')->where('id', $id)->update([
        'downpayment'    => $newDownpayment,
        'status'         => $newStatus,
        'payment_method' => $paymentMethod,
        'updated_at'     => now(),
    ]);

    session()->forget('pending_joiner_balance_checkout');

    return redirect("/joiner-receipt/{$id}")->with('success', 'Payment received! Thank you.');
}

public function showReceipt($id)
{
    $booking = DB::table('joiner_bookings')
        ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
        ->leftJoin('drivers', 'joiner_trips.driver_name', '=', 'drivers.name')
        ->where('joiner_bookings.id', $id)
        ->select(
            'joiner_bookings.*',
            'joiner_trips.destination',
            'joiner_trips.trip_date',
            'joiner_trips.meetup_point',
            'joiner_trips.meetup_time',
            'joiner_trips.van',
            'joiner_trips.plate_number',
            'joiner_trips.driver_name',
            'drivers.phone as driver_phone'
        )
        ->first();

    if (!$booking) {
        abort(404);
    }

    // 1. Define $totalPaid using the 'downpayment' column (actual amount paid)
    $totalPaid = $booking->downpayment;

    // 2. Define $balance as total price minus what was paid
    $balance = $booking->total_price - $totalPaid;

    // 3. Pass ALL three variables to the view
    return view('joiner_receipt', compact('booking', 'totalPaid', 'balance'));
}

public function completeTrip($id)
{
    DB::beginTransaction();
    try {
        // 1. Update the Trip Status to completed
        DB::table('joiner_trips')->where('id', $id)->update([
            'status' => 'completed',
            'updated_at' => now()
        ]);

        // 2. Update bookings to 'completed' so the Leave Feedback button appears
        // 🔹 FIX: Changed 'paid' to 'downpayment_paid' to match your DB
        // 🔹 FIX: Changed update status to 'completed' for the feedback logic
        DB::table('joiner_bookings')
            ->where('joiner_trip_id', $id)
            ->whereNotIn('status', ['cancelled'])
            ->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);

        DB::commit();
        return back()->with('success', '✅ Trip completed! Feedback is now enabled for all passengers.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', '❌ Failed to complete trip: ' . $e->getMessage());
    }
}

public function myBookings()
{
    $bookings = DB::table('joiner_bookings')
        ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
        ->where('joiner_bookings.user_id', Auth::id())
        ->select(
            'joiner_bookings.*',
            'joiner_trips.destination',
            'joiner_trips.trip_date as start_date',
            'joiner_trips.meetup_point as pickup',
            'joiner_trips.price_per_seat',
            'joiner_trips.driver_name as driver',
            DB::raw("'joiner' as booking_type"),
            // ADD THIS LINE TO FIX THE 'passengers' ERROR
            DB::raw("1 as passengers")
        )
        ->get();

    return view('my-bookings', compact('bookings'));
}

public function submitFeedback(Request $request)
{
    // 1. Validate the input
    $request->validate([
        'booking_id'     => 'required',
        'service_rating' => 'required|integer|min:1|max:5',
        'driver_rating'  => 'required|integer|min:1|max:5',
        'comment'        => 'nullable|string|max:500',
        'trip_type'      => 'required|string'
    ]);

    // 2. Insert into the feedbacks table
    DB::table('feedbacks')->insert([
        'user_id'        => Auth::id(),
        'booking_id'     => $request->booking_id,
        'driver_name'    => $request->driver_name,
        'trip_type'      => $request->trip_type,
        'service_rating' => $request->service_rating,
        'driver_rating'  => $request->driver_rating,
        'comment'        => $request->comment,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    return back()->with('success', 'Thank you! Your feedback has been submitted.');
}

public function cancelBooking($id, $type)
{
    DB::beginTransaction();
    try {
        if ($type === 'joiner') {
            // Delete joiner booking
            DB::table('joiner_bookings')->where('id', $id)->where('user_id', auth()->id())->delete();
        } else {
            // Delete regular/tour booking
            DB::table('passengers')->where('booking_id', $id)->delete();
            DB::table('bookings')->where('id', $id)->where('user_id', auth()->id())->delete();
        }

        DB::commit();
        return back()->with('success', 'Booking cancelled successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Failed to cancel booking.');
    }
}


}
