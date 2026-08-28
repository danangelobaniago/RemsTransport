<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Van;
use App\Models\TourPackage;
use App\Mail\PaymentReceiptMail;
use App\Http\Traits\BookingValidator;

class BookingController extends Controller
{
    use BookingValidator;

    public function getAvailableDrivers(Request $request)
    {
        $start = $request->start_date;
        $end   = $request->end_date ?? $start;

        // 1. Driver IDs busy in regular van bookings
        $busyIds = DB::table('bookings')
            ->where('start_date', '<=', $end)
            ->where('end_date',   '>=', $start)
            ->whereNotIn('status', ['rejected', 'cancelled', 'completed'])
            ->whereNotNull('driver')
            ->pluck('driver')
            ->map(fn($id) => (int) $id)
            ->unique();

        // 2. Driver names busy in joiner trips on overlapping dates
        $busyJoinerNames = DB::table('joiner_trips')
            ->where('trip_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $start);
            })
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->pluck('driver_name');

        // 3. Driver names busy in tour packages on overlapping dates
        $busyTourNames = DB::table('tour_packages')
            ->where('tour_date', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $start);
            })
            ->pluck('driver_name');

        // Map names → IDs and merge all busy drivers
        $allBusyNames  = $busyJoinerNames->merge($busyTourNames)->filter()->unique();
        $busyNameIds   = DB::table('drivers')
            ->whereIn('name', $allBusyNames)
            ->pluck('id')
            ->map(fn($id) => (int) $id);

        $allBusy = $busyIds->merge($busyNameIds)->unique()->values();

        return response()->json(['busy' => $allBusy]);
    }

    public function getBookedDates($van_id)
    {
        $van   = DB::table('vans')->where('id', $van_id)->first();
        $dates = [];

        // Helper to expand a date range into individual dates
        $expand = function ($startStr, $endStr) use (&$dates) {
            $s = strtotime($startStr);
            $e = strtotime($endStr ?? $startStr);
            for ($d = $s; $d <= $e; $d = strtotime('+1 day', $d)) {
                $dates[] = date('Y-m-d', $d);
            }
        };

        // 1. Regular van bookings
        DB::table('bookings')
            ->where('van_id', $van_id)
            ->whereIn('status', ['pending', 'approved'])
            ->get(['start_date', 'end_date'])
            ->each(fn($b) => $expand($b->start_date, $b->end_date));

        if ($van) {
            // 2. Joiner trips using the same van plate
            DB::table('joiner_trips')
                ->where('plate_number', $van->plate_number)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->get(['trip_date', 'end_date'])
                ->each(fn($t) => $expand($t->trip_date, $t->end_date ?? $t->trip_date));

            // 3. Tour packages using the same van plate
            DB::table('tour_packages')
                ->where('plate_number', $van->plate_number)
                ->get(['tour_date', 'end_date'])
                ->each(fn($t) => $expand($t->tour_date, $t->end_date ?? $t->tour_date));
        }

        return response()->json(array_values(array_unique($dates)));
    }

    public function showPassengerForm(Request $request)
    {
        $request->validate([
            'van_id' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'pickup' => 'required',
            'destination' => 'required',
        ]);

        $formData = $request->all();
        $passengers = $request->input('passengers', 1);
        return view('passengers', compact('formData', 'passengers'));
    }

    public function show($id)
{
    $booking = DB::table('bookings')
        ->join('users', 'bookings.user_id', '=', 'users.id')
        // NEW: Join with tour_packages to get vehicle info
        ->join('tour_packages', 'bookings.tour_id', '=', 'tour_packages.id')
        ->select(
            'bookings.*',
            'users.first_name',
            'users.last_name',
            'users.email',
            'tour_packages.van as vehicle_type' // This pulls the van name
        )
        ->where('bookings.id', $id)
        ->first();

    $passengers = DB::table('passengers')->where('booking_id', $id)->get();

    return view('receipt', compact('booking', 'passengers'));
}

    public function showBooking($id)
    {
        $van = Van::findOrFail($id);
        $drivers = DB::table('drivers')->get();
        $pricing = DB::table('pricing')->first();
        return view('booking', compact('van', 'drivers', 'pricing'));
    }

    public function showMultiBooking($id)
    {
        $van = Van::findOrFail($id);
        $drivers = DB::table('drivers')->get();
        $pricing = DB::table('pricing')->first();
        return view('booking-multi', compact('van', 'drivers', 'pricing'));
    }

    public function storeBooking(Request $request)
    {
        $van = Van::findOrFail($request->van_id);
        $request->validate([
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_time' => 'required',
            'driver' => 'required', // Ensure driver is selected
            'passengers' => 'required|integer|min:1|max:' . $van->seats,
        ]);

        // Server-side NCR pickup validation
        $pickupLat = (float) $request->pickup_lat;
        $pickupLng = (float) $request->pickup_lng;
        if (!$pickupLat || !$pickupLng
            || $pickupLat < 14.38 || $pickupLat > 14.78
            || $pickupLng < 120.92 || $pickupLng > 121.15) {
            return back()->withInput()->with('error', '⚠️ Pickup location must be within NCR (Metro Manila) only. Please select a valid Metro Manila address from the dropdown suggestions.');
        }

        // 3. CROSS-TABLE MULTI-DAY VALIDATION
        // We check every day between start and end date
        $start = strtotime($request->start_date);
        $end = strtotime($request->end_date);

        for ($date = $start; $date <= $end; $date = strtotime("+1 day", $date)) {
            $currentDate = date('Y-m-d', $date);

            if (!$this->checkAvailability($van->name, $request->driver, $currentDate)) {
                return back()->with('error', "❌ Conflict: The van or driver is busy on $currentDate.");
            }
        }

        session(['formData' => $request->all()]);
        return redirect('/checkout');
    }

    public function paymongoCheckout(Request $request)
{
    $formData = json_decode($request->formData, true);

    // 1. Capture the data from the Passenger Form
    $formData['total'] = $request->input('total');
    $formData['days'] = $request->input('days');
    $formData['baseFare'] = $request->input('baseFare');
    $formData['driverFee'] = $request->input('driverFee');
    $formData['passengers_data'] = $request->passengers_data;

    // 2. NEW: Capture the specific amount and payment type
    $amountToPay = (float) $request->input('amount_to_pay');
    $paymentType = $request->input('payment_type'); // 'downpayment' or 'full'

    // Add payment choice to formData so it's available in the success method later
    $formData['payment_type'] = $paymentType;
    $formData['amount_to_pay'] = $amountToPay;

    session(['formData' => $formData]);

    // 3. Validation
    if ($amountToPay <= 0) {
        return redirect()->back()->with('error', 'Invalid payment amount.');
    }

    // 4. Convert to centavos for PayMongo (Amount * 100)
    $amountInCents = (int) round($amountToPay * 100);

    // PayMongo minimum requirement (100.00 PHP = 10000 cents)
    if ($amountInCents < 10000) {
        $amountInCents = 10000;
    }

    // 5. Create the Checkout Session
    $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
        ->post('https://api.paymongo.com/v1/checkout_sessions', [
            'data' => [
                'attributes' => [
                    'line_items' => [[
                        'currency' => 'PHP',
                        'amount'   => $amountInCents,
                        // Dynamic name based on selection
                        'name'     => ($paymentType === 'full') ? 'Van Booking: Full Payment' : 'Van Booking: Downpayment (20%)',
                        'quantity' => 1,
                    ]],
                    'payment_method_types' => ['gcash', 'card'],
                    'success_url' => url('/payment-success'),
                    'cancel_url'  => url('/book'),
                ]
            ]
        ]);

    if (!$response->successful()) {
        return back()->with('error', 'Payment gateway error.');
    }

    session(['pending_checkout_session_id' => $response['data']['id']]);

    return redirect($response['data']['attributes']['checkout_url']);
}

    public function paymentSuccess(Request $request)
    {
        $formData = session('formData');
        if (!$formData) { return redirect('/book')->with('error', 'Session expired'); }

        $checkoutSessionId = session('pending_checkout_session_id');
        $paymentId = $checkoutSessionId;
        $paymentMethodLabel = 'PayMongo';

        if ($checkoutSessionId) {
            $csResponse = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
                ->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutSessionId}");

            if ($csResponse->successful()) {
                $payments = $csResponse['data']['attributes']['payments'] ?? [];
                if (!empty($payments)) {
                    $paymentId = $payments[0]['id'];
                    $methodType = $payments[0]['data']['attributes']['source']['type'] ?? null;
                    $paymentMethodLabel = match($methodType) {
                        'gcash' => 'GCash',
                        'card'  => 'Credit/Debit Card',
                        default => 'PayMongo',
                    };
                }
            }
        }

        $paymentId = $paymentId ?? $request->get('payment_intent_id') ?? 'N/A';

        $start = date('Y-m-d', strtotime($formData['start_date']));
        $end   = date('Y-m-d', strtotime($formData['end_date']));

       $total = (float) $formData['total'];
$paid = (float) $formData['amount_to_pay']; // Use the actual amount paid
$remaining = $total - $paid;
$status = ($formData['payment_type'] === 'full') ? 'fully_paid' : 'downpayment_paid';

        $bookingId = DB::table('bookings')->insertGetId([
            'user_id' => auth()->id(),
            'van_id' => $formData['van_id'],
            'pickup' => $formData['pickup'],
            'destination' => $formData['destination'],
            'start_date' => $start,
            'end_date' => $end,
            'days' => $formData['days'],
            'driver' => $formData['driver'] ?? null,
            'passengers' => $formData['passengers'],
            'base_fare' => $formData['baseFare'],
            'driver_fee' => $formData['driver_fee'] ?? 0,
            'total' => $total,
            'status' => 'pending',
            'payment_method' => 'paymongo',
            'payment_status' => $status,
            'amount_paid' => $paid,
            'remaining_balance' => $remaining,
            'payment_id' => $paymentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!empty($formData['passengers_data']) && is_array($formData['passengers_data'])) {
            foreach ($formData['passengers_data'] as $p) {
                if (!is_array($p)) continue;
                DB::table('passengers')->insert([
                    'booking_id' => $bookingId,
                    'first_name' => $p['first_name'] ?? '',
                    'middle_name' => $p['middle_name'] ?? '',
                    'last_name' => $p['last_name'] ?? '',
                    'birthday' => $p['birthday'] ?? null,
                    'gender' => $p['gender'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        session()->forget('formData');
        $passengers = DB::table('passengers')->where('booking_id', $bookingId)->get();
        $booking = DB::table('bookings')->find($bookingId);

        try {
            $user = DB::table('users')->find(auth()->id());
            if ($user && $user->email) {
                Mail::to($user->email)->send(new PaymentReceiptMail(
                    trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'Van Rental',
                    $formData['destination'] ?? 'N/A',
                    $paymentId,
                    $paid,
                    $paymentMethodLabel,
                    now()->format('F d, Y h:i A'),
                    $status
                ));
            }
        } catch (\Exception $e) {
            \Log::error('Payment receipt email failed (van): ' . $e->getMessage());
        }

        return view('payment-success', compact('booking', 'passengers'));
    }

    public function createTourBooking($tour_id)
    {
        $tour = TourPackage::findOrFail($tour_id);
        $preferred_date = request('preferred_date', $tour->tour_date->format('Y-m-d'));

        preg_match('/(\d+)/', $tour->duration ?? '1', $dm);
        $tripDays    = max(1, (int)($dm[1] ?? 1));
        $preferred_end = date('Y-m-d', strtotime($preferred_date . ' +' . ($tripDays - 1) . ' days'));

        return view('bookings.tour_manifesto', compact('tour', 'preferred_date', 'preferred_end', 'tripDays'));
    }

    public function processTourPayment(Request $request)
    {
        $tour = TourPackage::findOrFail($request->tour_id);

        // 4. CROSS-TABLE CHECK FOR TOURS
        $tourDate = $tour->tour_date->format('Y-m-d');
        if (!$this->checkAvailability($tour->van_name, $tour->driver_name, $tourDate)) {
            return back()->with('error', '❌ This tour package van or driver is currently booked for another trip.');
        }

        $passengers_data = [];
        if ($request->has('first_name')) {
            foreach ($request->first_name as $key => $val) {
                $passengers_data[] = [
                    'first_name' => $request->first_name[$key],
                    'last_name'  => $request->last_name[$key],
                    'age'        => $request->age[$key] ?? null,
                    'gender'     => $request->gender[$key] ?? null,
                ];
            }
        }

        $total = (float) $tour->price;
        $minDownpayment = $total * 0.20;
        $paymentType = $request->input('payment_type', 'downpayment'); // 'downpayment' or 'full'

        $amountToPay = $paymentType === 'full'
            ? $total
            : (float) $request->input('amount_to_pay', $minDownpayment);

        // Never allow less than the 20% minimum or more than the total.
        $amountToPay = max($minDownpayment, min($amountToPay, $total));

        $formData = [
            'tour_id'         => $tour->id,
            'van_id'          => $tour->van_id,
            'driver'          => $tour->driver_id,
            'pickup'          => $tour->pickup_point,
            'destination'     => $tour->destination,
            'start_date'      => $tourDate,
            'end_date'        => $tour->end_date ? $tour->end_date->format('Y-m-d') : $tourDate,
            'total'           => $total,
            'baseFare'        => $total,
            'driverFee'       => 0,
            'days'            => 1,
            'passengers'      => count($passengers_data),
            'passengers_data' => $passengers_data,
            'type'            => 'tour_package',
            'payment_type'    => $paymentType,
            'amount_to_pay'   => $amountToPay,
        ];

        session(['formData' => $formData]);

        $amountInCents = (int) round($amountToPay * 100);
        if ($amountInCents < 10000) { $amountInCents = 10000; }

        $lineItemName = $paymentType === 'full'
            ? 'Tour Full Payment: ' . $tour->name
            : 'Tour Downpayment: ' . $tour->name;

        $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
            ->post('https://api.paymongo.com/v1/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'line_items' => [[
                            'currency' => 'PHP',
                            'amount'   => $amountInCents,
                            'name'     => $lineItemName,
                            'quantity' => 1,
                        ]],
                        'payment_method_types' => ['gcash', 'card'],
                        'success_url'          => url('/payment-success'),
                        'cancel_url'           => url('/tour/details/' . $tour->id),
                        'description'          => ($paymentType === 'full' ? 'Full Payment' : 'Downpayment') . ' for ' . $tour->name . ' Tour Package',
                    ]
                ]
            ]);

        if (!$response->successful()) {
            return back()->with('error', 'Payment Gateway Error.');
        }

        return redirect($response['data']['attributes']['checkout_url']);
    }

public function showMyBookings()
{
    $userId = auth()->id();

    $privateBookings = DB::table('bookings')
        ->where('user_id', $userId)
        ->select(
            'id', 'van', 'pickup', 'destination', 'start_date',
            'passengers', 'status', 'driver',
            'payment_status', 'amount_paid', 'total',
            DB::raw("'private' as booking_type")
        );

    $bookings = DB::table('joiner_bookings')
        ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
        ->where('joiner_bookings.user_id', $userId)
        ->select(
            'joiner_bookings.id',
            DB::raw("'Joiner Trip' as van"),
            'joiner_trips.meetup_point as pickup',
            'joiner_trips.destination',
            'joiner_trips.trip_date as start_date',
            DB::raw("1 as passengers"),
            'joiner_bookings.status',
            'joiner_trips.driver_name as driver',
            'joiner_bookings.payment_status',
            'joiner_bookings.amount_paid',
            'joiner_bookings.total_price as total',
            DB::raw("'joiner' as booking_type")
        )
        ->union($privateBookings)
        ->orderBy('start_date', 'desc')
        ->get();

    return view('my-bookings', compact('bookings'));
}

public function submitFeedback(Request $request) {
    DB::table('feedbacks')->insert([
        'booking_id' => $request->booking_id,
        'trip_type'  => $request->trip_type, // Add this line
        'user_id'    => auth()->id(),
        'service_rating' => $request->service_rating,
        'driver_rating'  => $request->driver_rating,
        'comment'    => $request->comment,
        'created_at' => now(),
    ]);
    return back()->with('success', 'Thank you for your feedback!');
}

public function cancelBooking($id, $type)
{
    $table = (str_contains(strtolower($type), 'joiner')) ? 'joiner_bookings' : 'bookings';

    $booking = DB::table($table)->where('id', $id)->first();

    if (!$booking) {
        return back()->with('error', 'Booking not found.');
    }

    $updated = DB::table($table)
        ->where('id', $id)
        ->where('user_id', auth()->id())
        ->update([
            'status' => 'cancelled',
            'updated_at' => now()
        ]);

    if ($updated) {
        // Use isset() to safely check the property
        if (isset($booking->payment_status) && $booking->payment_status === 'fully_paid') {
            return redirect('/my-bookings')->with('success', 'Booking cancelled. Note: 20% is retained as a fee. Admin will contact you regarding the 80% refund.');
        }
        return redirect('/my-bookings')->with('success', 'Booking cancelled. Downpayment is non-refundable.');
    }

    return redirect('/my-bookings')->with('error', 'Could not process cancellation.');
}

public function store(Request $request) {
    $selectedDateTime = Carbon::parse($request->start_date . ' ' . $request->pickup_time);

    if ($selectedDateTime->isPast()) {
        return back()->with('error', 'The selected pickup time has already passed. Please choose a future time.');
    }
}

public function payBalanceCheckout(Request $request, $id)
{
    $request->validate([
        'payment_method' => 'required|in:gcash,card',
    ]);

    $booking = DB::table('bookings')->where('id', $id)->first();
    if (!$booking) {
        abort(404);
    }

    $totalPaid = (float) ($booking->amount_paid ?? 0);
    $balance   = max(0, (float) $booking->total - $totalPaid);

    if ($balance <= 0) {
        return redirect("/receipt/{$id}")->with('error', 'This booking has no remaining balance.');
    }

    $amountInCents = (int) round($balance * 100);
    if ($amountInCents < 10000) {
        $amountInCents = 10000;
    }

    $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
        ->post('https://api.paymongo.com/v1/checkout_sessions', [
            'data' => [
                'attributes' => [
                    'line_items' => [[
                        'currency' => 'PHP',
                        'amount'   => $amountInCents,
                        'name'     => 'Van Booking Balance Payment (#REM-' . str_pad($id, 5, '0', STR_PAD_LEFT) . ')',
                        'quantity' => 1,
                    ]],
                    'payment_method_types' => [$request->payment_method],
                    'success_url' => url("/booking/{$id}/pay-balance/success"),
                    'cancel_url'  => url("/receipt/{$id}"),
                ]
            ]
        ]);

    if (!$response->successful()) {
        return redirect("/receipt/{$id}")->with('error', 'Payment gateway error. Please try again.');
    }

    session([
        'pending_balance_checkout' => [
            'booking_id' => $id,
            'amount'     => $balance,
            'session_id' => $response['data']['id'],
        ],
    ]);

    return redirect($response['data']['attributes']['checkout_url']);
}

public function payBalanceSuccess(Request $request, $id)
{
    $pending = session('pending_balance_checkout');

    if (!$pending || (string) $pending['booking_id'] !== (string) $id) {
        return redirect("/receipt/{$id}")->with('error', 'Payment session expired.');
    }

    $csResponse = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
        ->get("https://api.paymongo.com/v1/checkout_sessions/{$pending['session_id']}");

    $paymentId = $pending['session_id'];
    if ($csResponse->successful()) {
        $payments = $csResponse['data']['attributes']['payments'] ?? [];
        if (!empty($payments)) {
            $paymentId = $payments[0]['id'];
        }
    }

    $booking = DB::table('bookings')->where('id', $id)->first();
    if (!$booking) {
        abort(404);
    }

    $newAmountPaid = (float) ($booking->amount_paid ?? 0) + (float) $pending['amount'];
    $newBalance    = max(0, (float) $booking->total - $newAmountPaid);
    $newStatus     = $newBalance <= 0 ? 'fully_paid' : 'downpayment_paid';

    DB::table('bookings')->where('id', $id)->update([
        'amount_paid'        => $newAmountPaid,
        'remaining_balance'  => $newBalance,
        'status'             => $newStatus,
        'payment_status'     => $newStatus,
        'payment_id'         => $paymentId,
        'updated_at'         => now(),
    ]);

    session()->forget('pending_balance_checkout');

    return redirect("/receipt/{$id}")->with('success', 'Payment received! Thank you.');
}

public function showReceipt($id)
{
    $booking = DB::table('bookings')
        ->join('users', 'bookings.user_id', '=', 'users.id')
        ->leftJoin('vans', 'bookings.van_id', '=', 'vans.id')
        ->leftJoin('tour_packages', 'bookings.tour_id', '=', 'tour_packages.id')
        ->leftJoin('drivers', 'bookings.driver', '=', DB::raw('CAST(drivers.id AS CHAR)'))
        ->where('bookings.id', $id)
        ->select(
            'bookings.*',
            'users.first_name',
            'users.last_name',
            'users.email',
            DB::raw("COALESCE(vans.name, tour_packages.van, bookings.package_name, 'N/A') as vehicle_type"),
            DB::raw("COALESCE(vans.plate_number, tour_packages.plate_number) as plate_number"),
            'drivers.name as driver_name',
            'drivers.phone as driver_phone'
        )
        ->first();

    if (!$booking) {
        abort(404);
    }

    $passengers = DB::table('passengers')->where('booking_id', $id)->get();

    // For tour bookings: amount_paid is set when Paymongo succeeds; downpayment is the initial charge
    // remaining_balance defaults to 0 in DB so cannot be trusted — compute directly
    $totalPaid = (float) ($booking->amount_paid ?? $booking->downpayment ?? 0);
    $balance   = max(0, (float) $booking->total - $totalPaid);

    return view('receipt', compact('booking', 'passengers', 'totalPaid', 'balance'));
}

}
