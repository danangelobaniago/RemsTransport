<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourPackage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentReceiptMail;
use App\Notifications\BookingRejected;
use App\Http\Traits\BookingValidator;
use GuzzleHttp\Client;

class TourController extends Controller
{
    use BookingValidator;

    public function index()
    {
        $tours = DB::table('tour_packages')->orderBy('tour_date', 'asc')->get()->map(function ($tour) {
            $tour->pending_count  = DB::table('bookings')
                ->where('tour_id', $tour->id)
                ->whereIn('status', ['downpayment_paid', 'fully_paid'])
                ->count();
            $tour->approved_count = DB::table('bookings')
                ->where('tour_id', $tour->id)
                ->where('status', 'approved')
                ->count();
            return $tour;
        });

        $vans    = DB::table('vans')->where('status', 'available')->get();
        $drivers = DB::table('drivers')->where('status', 'available')->get();

        return view('admin.tours', compact('tours', 'vans', 'drivers'));
    }

    public function getBookingsJson($id)
    {
        $bookings = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->where('bookings.tour_id', $id)
            ->whereNotIn('bookings.status', ['cancelled'])
            ->select(
                'bookings.id', 'bookings.status', 'bookings.total',
                'bookings.downpayment', 'bookings.amount_paid',
                'bookings.passengers as seat_count',
                'users.first_name', 'users.last_name', 'users.email'
            )
            ->get()
            ->map(function ($b) {
                $b->passenger_list = DB::table('passengers')->where('booking_id', $b->id)->get();
                return $b;
            });
        return response()->json($bookings);
    }

    public function approveTourBooking($id)
    {
        DB::table('bookings')->where('id', $id)
            ->whereIn('status', ['pending', 'downpayment_paid', 'fully_paid'])
            ->update(['status' => 'approved', 'updated_at' => now()]);
        return back()->with('success', 'Booking #' . $id . ' approved.');
    }

    public function rejectTourBooking($id)
    {
        $booking = DB::table('bookings')->where('id', $id)->first();

        DB::table('bookings')->where('id', $id)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        if ($booking) {
            $user = User::find($booking->user_id);
            if ($user) {
                $destination = $booking->package_name ?? $booking->destination ?? 'Tour Package';
                $user->notify(new BookingRejected($booking->id, $destination, 'Tour Booking'));
            }
        }

        return back()->with('success', 'Booking #' . $id . ' rejected.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string',
            'destination'    => 'required|string',
            'pickup_point'   => 'required|string',
            'tour_date'      => 'required|date|after_or_equal:today',
            'end_date'       => 'required|date|after_or_equal:tour_date',
            'pickup_time'    => 'required',
            'price'          => 'required|numeric',
            'max_passengers' => 'required|integer',
            'duration'       => 'required|string',
            'van_id'         => 'required|exists:vans,id',
            'driver_id'      => 'required|exists:drivers,id',
            'image'          => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $van    = DB::table('vans')->where('id', $request->van_id)->first();
        $driver = DB::table('drivers')->where('id', $request->driver_id)->first();

        $isAvailable = $this->checkAvailability(
            $van->plate_number,
            $driver->name,
            $request->tour_date
        );

        if (!$isAvailable) {
            return back()->withInput()->with('error', '❌ Conflict: Van or Driver is already scheduled on that date.');
        }

        $path = $request->hasFile('image') ? $request->file('image')->store('tours', 'public') : null;

        DB::table('tour_packages')->insert([
            'name'                  => $request->name,
            'destination'           => $request->destination,
            'pickup_point'          => $request->pickup_point,
            'tour_date'             => $request->tour_date,
            'end_date'              => $request->end_date,
            'pickup_time'           => $request->pickup_time,
            'price'                 => $request->price,
            'max_passengers'        => $request->max_passengers,
            'duration'              => $request->duration,
            'is_best_seller'        => $request->has('is_best_seller') ? 1 : 0,
            'description'           => $request->description,
            'van'                   => $van->name,
            'plate_number'          => $van->plate_number,
            'driver_name'           => $driver->name,
            'driver_license_number' => $driver->license_number,
            'image'                 => $path,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        return redirect()->back()->with('success', 'Tour Package created successfully!');
    }

    public function show($id)
{
    $tour = DB::table('tour_packages')->where('id', $id)->first();
    if (!$tour) { abort(404); }

    $isBooked = DB::table('bookings')
        ->where('tour_id', $id)
        ->whereIn('status', ['paid', 'confirmed', 'pending'])
        ->exists();

    // Collect all booked date ranges so the date picker can block them
    $bookedRanges = DB::table('bookings')
        ->where('tour_id', $id)
        ->whereNotIn('status', ['cancelled', 'rejected'])
        ->select('start_date', 'end_date')
        ->get()
        ->map(fn($b) => ['start' => $b->start_date, 'end' => $b->end_date])
        ->values();

    return view('show', compact('tour', 'isBooked', 'bookedRanges'));
}

    public function showManifesto($id)
    {
        $tour = DB::table('tour_packages')->where('id', $id)->first();
        if (!$tour) { abort(404); }
        return view('passenger_manifesto', compact('tour'));
    }

    public function tour_pay(Request $request)
{
    // Fetch tour first so we can use its dates in validation
    $tour = DB::table('tour_packages')->where('id', $request->tour_id)->first();

    // Parse trip duration in days from e.g. "3D2N", "Day Tour", "2"
    preg_match('/(\d+)/', $tour->duration ?? '1', $dm);
    $tripDays = max(1, (int)($dm[1] ?? 1));
    $availEnd = $tour->end_date ?? $tour->tour_date;
    // Latest valid start: the trip must end on or before availEnd
    $maxStart = date('Y-m-d', strtotime($availEnd . ' -' . ($tripDays - 1) . ' days'));

    $request->validate([
        'tour_id'        => 'required|exists:tour_packages,id',
        'preferred_date' => [
            'required', 'date',
            'after_or_equal:' . $tour->tour_date,
            'before_or_equal:' . $maxStart,
        ],
        'preferred_end'  => 'required|date',
        'payment_type'   => 'required|in:downpayment,full',
        'amount_to_pay'  => 'nullable|numeric|min:0',
        'first_name'     => 'required|array',
        'first_name.*'   => 'required|string|max:255',
        'last_name.*'    => 'required|string|max:255',
        'birthday.*'     => 'required|date|before_or_equal:today',
        'gender.*'       => 'required|in:Male,Female',
    ]);

    if (count($request->first_name) > $tour->max_passengers) {
        return back()->withErrors(['pax' => "This tour only allows a maximum of {$tour->max_passengers} passengers."])->withInput();
    }

    $preferredStart = \Carbon\Carbon::parse($request->preferred_date);
    $preferredEnd   = $preferredStart->copy()->addDays($tripDays - 1);

    // Check for date conflicts with existing bookings
    $startYMD = $preferredStart->format('Y-m-d');
    $endYMD   = $preferredEnd->format('Y-m-d');
    $conflict = DB::table('bookings')
        ->where('tour_id', $tour->id)
        ->whereNotIn('status', ['cancelled', 'rejected'])
        ->where(function ($q) use ($startYMD, $endYMD) {
            $q->whereBetween('start_date', [$startYMD, $endYMD])
              ->orWhereBetween('end_date', [$startYMD, $endYMD])
              ->orWhere(function ($q2) use ($startYMD, $endYMD) {
                  $q2->where('start_date', '<=', $startYMD)
                     ->where('end_date', '>=', $endYMD);
              });
        })
        ->exists();

    if ($conflict) {
        return back()->withErrors(['preferred_date' => 'The selected dates are already booked. Please choose different dates.'])->withInput();
    }

    // 1. DYNAMIC CALCULATION BASED ON SELECTION
    $isFullPayment = $request->payment_type === 'full';
    $minDownpayment = $tour->price * 0.20;

    if ($isFullPayment) {
        $amountToPay = $tour->price;
    } else {
        // Respect the customer's chosen downpayment amount, but never let it fall
        // below the required 20% minimum or exceed the total price.
        $amountToPay = max($minDownpayment, min((float) $request->input('amount_to_pay', $minDownpayment), $tour->price));
    }

    $amountInCents = (int)($amountToPay * 100);

    // Start Transaction
    DB::beginTransaction();

    try {
        // 2. Create the Main Booking
        $bookingId = DB::table('bookings')->insertGetId([
            'user_id'           => Auth::id(),
            'tour_id'           => $tour->id,
            'package_name'      => $tour->name,
            'destination'       => $tour->name,
            'pickup'            => $tour->pickup_point,
            'start_date'        => $preferredStart->format('Y-m-d'),
            'end_date'          => $preferredEnd->format('Y-m-d'),
            'passengers'        => count($request->first_name),
            'total'             => $tour->price,
            'downpayment'       => $amountToPay,
            'status'            => 'pending',
            'payment_status'    => 'pending',
            'booking_type'      => 'tour',
            // Save the payment type so you know if it's 'full' or 'downpayment' later
            'notes'             => $isFullPayment ? 'Full Payment Selection' : 'Downpayment Selection',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // 3. Save each passenger
        foreach ($request->first_name as $key => $fname) {
            DB::table('passengers')->insert([
                'booking_id' => $bookingId,
                'first_name' => $fname,
                'last_name'  => $request->last_name[$key],
                'birthday'   => $request->birthday[$key],
                'gender'     => $request->gender[$key],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::commit();

        // 4. Initiate PayMongo Checkout with the dynamic amount
        $client = new Client();
        $response = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
            'body' => json_encode([
                'data' => [
                    'attributes' => [
                        'send_email_receipt' => true,
                        'show_description' => true,
                        'line_items' => [
                            [
                                'currency' => 'PHP',
                                'amount' => $amountInCents,
                                'description' => $isFullPayment ? 'Full Payment for ' . $tour->name : 'Downpayment for ' . $tour->name,
                                'name' => $isFullPayment ? 'Tour Full Payment' : 'Tour Downpayment',
                                'quantity' => 1,
                            ]
                        ],
                        'payment_method_types' => ['gcash', 'card'],
                        'success_url' => url('/my-bookings?payment=success&booking_id=' . $bookingId),
                        'cancel_url'  => url('/bookings/tour/' . $tour->id),
                        'description' => 'Booking ID: #' . $bookingId . ' - ' . $tour->name,
                    ]
                ]
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'accept' => 'application/json',
                'authorization' => 'Basic ' . base64_encode(config('services.paymongo.secret_key') . ':'),
            ],
        ]);

        $session = json_decode($response->getBody(), true);
        $checkoutUrl = $session['data']['attributes']['checkout_url'];

        // Save the checkout session ID as payment reference immediately
        $checkoutSessionId = $session['data']['id'] ?? null;
        if ($checkoutSessionId) {
            DB::table('bookings')
                ->where('id', $bookingId)
                ->update(['payment_id' => $checkoutSessionId]);
        }

        return redirect()->away($checkoutUrl);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Processing Error: ' . $e->getMessage());
    }
}

public function showMyBookings(Request $request)
{
    $userId = Auth::id();

    // 1. PAYMENT SUCCESS LOGIC
    if ($request->query('payment') === 'success') {
        // Use the booking_id we embedded in the success URL for precision
        $bookingId = $request->query('booking_id');

        $targetBooking = $bookingId
            ? DB::table('bookings')->where('id', $bookingId)->where('user_id', $userId)->first()
            : DB::table('bookings')->where('user_id', $userId)->where('status', 'pending')->latest()->first();

        if ($targetBooking) {
            $finalStatus = ($targetBooking->total == $targetBooking->downpayment) ? 'fully_paid' : 'downpayment_paid';

            // Fetch real pay_... ID and payment method from PayMongo checkout session
            $sessionId          = $targetBooking->payment_id; // cs_...
            $actualPaymentId    = $sessionId; // fallback
            $paymentMethodLabel = 'PayMongo';

            if ($sessionId && str_starts_with($sessionId, 'cs_')) {
                try {
                    $csResponse = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
                        ->get("https://api.paymongo.com/v1/checkout_sessions/{$sessionId}");
                    if ($csResponse->successful()) {
                        $pmts = $csResponse['data']['attributes']['payments'] ?? [];
                        if (!empty($pmts)) {
                            $actualPaymentId = $pmts[0]['id']; // pay_...
                            $methodType      = $pmts[0]['data']['attributes']['source']['type'] ?? null;
                            $paymentMethodLabel = match($methodType) {
                                'gcash' => 'GCash',
                                'card'  => 'Credit/Debit Card',
                                default => 'PayMongo',
                            };
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('PayMongo session fetch failed (tour): ' . $e->getMessage());
                }
            }

            DB::table('bookings')
                ->where('id', $targetBooking->id)
                ->update([
                    'status'         => $finalStatus,
                    'payment_status' => 'paid',
                    'amount_paid'    => $targetBooking->downpayment,
                    'payment_id'     => $actualPaymentId,
                    'updated_at'     => now(),
                ]);

            try {
                $user = DB::table('users')->find($userId);
                if ($user && $user->email) {
                    $tourName = $targetBooking->package_name ?? $targetBooking->destination ?? 'Tour Package';
                    Mail::to($user->email)->send(new PaymentReceiptMail(
                        trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                        'Tour Package',
                        $tourName,
                        $actualPaymentId,
                        (float) $targetBooking->downpayment,
                        $paymentMethodLabel,
                        now()->format('F d, Y h:i A'),
                        $finalStatus
                    ));
                }
            } catch (\Exception $e) {
                \Log::error('Payment receipt email failed (tour): ' . $e->getMessage());
            }
        }
    }

    // 2. FETCH TOUR & PRIVATE BOOKINGS
    // We select specific columns so they align with the Joiner table
    $tourBookings = DB::table('bookings')
        ->select(
            'id',
            DB::raw('COALESCE(package_name, destination) as destination'), // Handles both naming conventions
            'status',
            'pickup',
            'start_date',
            'passengers',
            'booking_type',
            'total',
            'downpayment',
            'created_at'
        )
        ->where('user_id', $userId);

    // 3. FETCH JOINER TRIP BOOKINGS
    // Join with joiner_trips to get the destination and dates
    // Inside TourController.php -> showMyBookings()

// Inside TourController.php -> showMyBookings()

$allBookings = DB::table('joiner_bookings')
    ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
    ->select(
        'joiner_bookings.id',
        'joiner_trips.destination',
        'joiner_bookings.status',
        'joiner_trips.meetup_point as pickup',
        'joiner_trips.trip_date as start_date',
        'joiner_bookings.seats_booked as passengers',
        DB::raw("'joiner' as booking_type"),

        // Use the columns I see in your joiner_bookings screenshot:
        'joiner_bookings.total_price as total',
        'joiner_bookings.downpayment',

        'joiner_bookings.created_at'
    )
    ->where('joiner_bookings.user_id', $userId)
    ->union($tourBookings)
    ->orderBy('created_at', 'desc')
    ->get();

    return view('my-bookings', ['bookings' => $allBookings])
        ->with('success', $request->query('payment') === 'success' ? 'Payment Successful! Your tour booking is now Downpayment Paid.' : null);
}
    public function delete($id)
{
    // Block deletion once a customer's booking for this package has been approved.
    $hasApprovedBooking = DB::table('bookings')
        ->where('tour_id', $id)
        ->where('status', 'approved')
        ->exists();

    if ($hasApprovedBooking) {
        return back()->with('error', 'This package cannot be deleted — it already has an approved booking.');
    }

    // Start a transaction to ensure all-or-nothing deletion
    DB::beginTransaction();

    try {
        // 1. Find all bookings associated with this tour ID
        $bookingIds = DB::table('bookings')->where('tour_id', $id)->pluck('id');

        // 2. Delete all passengers linked to those bookings
        DB::table('passengers')->whereIn('booking_id', $bookingIds)->delete();

        // 3. Delete the bookings themselves
        DB::table('bookings')->where('tour_id', $id)->delete();

        // 4. Finally, delete the tour package
        DB::table('tour_packages')->where('id', $id)->delete();

        DB::commit();
        return back()->with('success', 'Package and all associated bookings deleted successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error deleting package: ' . $e->getMessage());
    }
}

public function getManifest($id)
{
    // Fetch passengers only from bookings that are NOT cancelled
    $passengers = DB::table('passengers')
        ->join('bookings', 'passengers.booking_id', '=', 'bookings.id')
        ->where('bookings.tour_id', $id)
        ->where('bookings.status', '!=', 'cancelled') // This removes cancelled users
        ->select(
            'passengers.first_name',
            'passengers.last_name',
            'passengers.birthday',
            'passengers.gender'
        )
        ->get();

    return response()->json($passengers);
}


}
