<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public function dashboard()
    {
        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();

        if (!$driver) {
            return redirect('/login')->with('error', 'No driver record linked to your account.');
        }

        $bookings = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('vans', 'bookings.van_id', '=', 'vans.id')
            ->select(
                'bookings.*',
                'users.first_name',
                'users.last_name',
                'users.email as user_email',
                'users.phone_number as user_phone',
                'vans.name as van_name',
                'vans.plate_number',
                DB::raw('COALESCE(NULLIF(bookings.phone, ""), users.phone_number) as contact_number')
            )
            ->where('bookings.driver', $driver->id)
            ->whereNotIn('bookings.status', ['rejected', 'cancelled'])
            ->orderBy('bookings.start_date', 'asc')
            ->get();

        // Fetch tour packages assigned to this driver by name
        $tourPackages = DB::table('tour_packages')
            ->where('driver_name', $driver->name)
            ->orderBy('tour_date', 'asc')
            ->get()
            ->map(function ($tour) {
                $tour->passenger_count = DB::table('bookings')
                    ->where('tour_id', $tour->id)
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->count();
                $tour->total_revenue = DB::table('bookings')
                    ->where('tour_id', $tour->id)
                    ->whereNotIn('status', ['cancelled', 'rejected'])
                    ->sum('total');
                return $tour;
            });

        // Fetch joiner trips assigned to this driver by name
        $joinerTrips = DB::table('joiner_trips')
            ->where('driver_name', $driver->name)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('trip_date', 'asc')
            ->get()
            ->map(function ($trip) {
                // Count actual passengers from joiner_passengers (one row per person)
                $bookingIds = DB::table('joiner_bookings')
                    ->where('joiner_trip_id', $trip->id)
                    ->whereIn('status', ['paid', 'pending', 'downpayment_paid', 'fully_paid'])
                    ->pluck('id');

                $passengerCount = DB::table('joiner_passengers')
                    ->whereIn('joiner_booking_id', $bookingIds)
                    ->count();

                // Fall back to booking count if joiner_passengers is empty
                $trip->passenger_count = $passengerCount ?: $bookingIds->count();
                $trip->total_revenue   = DB::table('joiner_bookings')
                    ->where('joiner_trip_id', $trip->id)
                    ->sum('total_price');
                return $trip;
            });

        // Pre-process for JS calendar (avoids complex closure inside @json in Blade)
        $bookingsForJs = $bookings->map(function ($b) {
            $bal = $b->remaining_balance ?? ($b->total - ($b->amount_paid ?? 0));
            return [
                'id'          => $b->id,
                'ref'         => '#REM-' . str_pad($b->id, 5, '0', STR_PAD_LEFT),
                'customer'    => trim(($b->first_name ?? 'N/A') . ' ' . ($b->last_name ?? '')),
                'contact'     => $b->contact_number ?? '',
                'pickup'      => $b->pickup,
                'destination' => $b->destination,
                'start_date'  => $b->start_date,
                'end_date'    => $b->end_date,
                'status'      => $b->status,
                'trip_status' => $b->trip_status ?? 'not_started',
                'total'       => (float) $b->total,
                'amount_paid' => (float) ($b->amount_paid ?? 0),
                'balance'     => (float) $bal,
                'van'         => $b->van_name ?? 'N/A',
                'plate'       => $b->plate_number ?? '',
                'type'        => 'rental',
            ];
        })->values();

        // Add joiner trips to calendar data
        $joinerForJs = $joinerTrips->map(function ($t) {
            return [
                'id'          => 'j' . $t->id,
                'ref'         => 'JOINER-' . str_pad($t->id, 4, '0', STR_PAD_LEFT),
                'customer'    => $t->passenger_count . ' passengers',
                'contact'     => '',
                'pickup'      => $t->meetup_point,
                'destination' => $t->destination,
                'start_date'  => $t->trip_date,
                'end_date'    => $t->end_date ?? $t->trip_date,
                'status'      => $t->status,
                'trip_status' => 'joiner',
                'total'       => 0,
                'amount_paid' => 0,
                'balance'     => 0,
                'van'         => $t->van,
                'plate'       => $t->plate_number,
                'type'        => 'joiner',
            ];
        })->values();

        // Tour packages have a bookable DATE RANGE (customers each pick their own date
        // within it), so the calendar must only mark the specific dates that customers
        // actually booked — not the whole range — otherwise the driver looks booked solid
        // even with zero customers. Pull the real per-customer bookings instead.
        $tourForJs = DB::table('bookings')
            ->join('tour_packages', 'bookings.tour_id', '=', 'tour_packages.id')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->where('tour_packages.driver_name', $driver->name)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
            ->select(
                'bookings.id',
                'bookings.start_date',
                'bookings.end_date',
                'bookings.status',
                'tour_packages.id as tour_package_id',
                'tour_packages.name as tour_name',
                'tour_packages.destination',
                'tour_packages.pickup_point',
                'tour_packages.van',
                'tour_packages.plate_number',
                'users.first_name',
                'users.last_name'
            )
            ->get()
            ->map(function ($b) {
                return [
                    'id'              => 't' . $b->id,
                    'ref'             => strtoupper($b->tour_name),
                    'customer'        => trim(($b->first_name ?? '') . ' ' . ($b->last_name ?? '')) ?: 'N/A',
                    'contact'         => '',
                    'pickup'          => $b->pickup_point,
                    'destination'     => $b->destination,
                    'start_date'      => $b->start_date,
                    'end_date'        => $b->end_date ?? $b->start_date,
                    'status'          => $b->status,
                    'trip_status'     => 'not_started',
                    'total'           => 0,
                    'amount_paid'     => 0,
                    'balance'         => 0,
                    'van'             => $b->van,
                    'plate'           => $b->plate_number,
                    'type'            => 'tour',
                    'tour_package_id' => $b->tour_package_id,
                ];
            })->values();

        $bookingsForJs = $bookingsForJs->concat($joinerForJs)->concat($tourForJs)->values();

        // Whichever trip needs the driver's attention right now: one already under way
        // (arrived/in progress) takes priority, otherwise the soonest upcoming one.
        $today = date('Y-m-d');
        $doneStatuses = ['cancelled', 'rejected', 'completed'];
        $nextTrip = $bookingsForJs
            ->filter(function ($b) use ($doneStatuses, $today) {
                if (in_array(strtolower($b['status']), $doneStatuses)) return false;
                if (($b['trip_status'] ?? null) === 'completed') return false;
                return $b['end_date'] >= $today;
            })
            ->sortBy(function ($b) {
                $inMotion = in_array($b['trip_status'] ?? '', ['arrived', 'in_progress']) ? '0' : '1';
                return $inMotion . '_' . $b['start_date'];
            })
            ->first();

        return view('driver.dashboard', compact('driver', 'bookings', 'bookingsForJs', 'joinerTrips', 'tourPackages', 'nextTrip'));
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();
        if (!$driver) {
            return response()->json(['error' => 'No driver record linked to your account.'], 404);
        }

        DB::table('drivers')->where('id', $driver->id)->update([
            'current_lat'         => $request->lat,
            'current_lng'         => $request->lng,
            'location_updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function joinerTripManifest($id)
    {
        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();
        if (!$driver) return redirect('/login');

        $trip = DB::table('joiner_trips')
            ->where('id', $id)
            ->where('driver_name', $driver->name)
            ->first();
        if (!$trip) abort(403);

        $bookings = DB::table('joiner_bookings')
            ->leftJoin('users', 'joiner_bookings.user_id', '=', 'users.id')
            ->where('joiner_bookings.joiner_trip_id', $id)
            ->whereNotIn('joiner_bookings.status', ['cancelled'])
            ->select('joiner_bookings.*', 'users.first_name', 'users.last_name', 'users.email')
            ->orderBy('joiner_bookings.created_at')
            ->get()
            ->map(function ($b) {
                $b->passengers  = DB::table('joiner_passengers')->where('joiner_booking_id', $b->id)->get();
                // amount_paid is only set when driver manually collects; downpayment holds what Paymongo charged
                $b->actual_paid = ($b->amount_paid > 0) ? $b->amount_paid : ($b->downpayment ?? 0);
                $b->balance     = $b->total_price - $b->actual_paid;
                return $b;
            });

        $totalPassengers     = $bookings->sum(fn ($b) => $b->passengers->count() ?: 1);
        $totalCashCollected  = $bookings->sum('amount_paid');                                   // driver physically collected
        $totalOnlinePaid     = $bookings->sum(fn ($b) => $b->amount_paid > 0 ? 0 : $b->actual_paid); // paid via Paymongo
        $totalOutstanding    = $bookings->sum(fn ($b) => max(0, $b->balance));                  // still needs to be collected
        $totalDue            = $bookings->sum('total_price');
        $allPaid             = $bookings->every(fn ($b) => $b->balance <= 0);

        return view('driver.joiner-manifest', compact(
            'trip', 'bookings', 'totalPassengers',
            'totalCashCollected', 'totalOnlinePaid', 'totalOutstanding', 'totalDue', 'allPaid'
        ));
    }

    public function updateJoinerTripStatus(Request $request, $id)
    {
        $request->validate(['trip_status' => 'required|in:arrived,in_progress,completed']);

        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();
        $trip   = DB::table('joiner_trips')->where('id', $id)->where('driver_name', $driver->name)->first();
        if (!$trip) abort(403);

        $order   = ['not_started' => 0, 'arrived' => 1, 'in_progress' => 2, 'completed' => 3];
        $current = $order[$trip->trip_status] ?? 0;
        $next    = $order[$request->trip_status] ?? 0;

        if ($next !== $current + 1) {
            return back()->with('error', 'Invalid status transition.');
        }

        $updateData = ['trip_status' => $request->trip_status, 'updated_at' => now()];
        if ($request->trip_status === 'completed') {
            $updateData['status'] = 'completed';
        }

        DB::table('joiner_trips')->where('id', $id)->update($updateData);

        // When trip completes, mark all individual bookings as completed too
        if ($request->trip_status === 'completed') {
            DB::table('joiner_bookings')
                ->where('joiner_trip_id', $id)
                ->whereNotIn('status', ['cancelled'])
                ->update(['status' => 'completed', 'updated_at' => now()]);
        }

        $messages = [
            'arrived'     => 'Marked as arrived at meetup point.',
            'in_progress' => 'Trip is now in progress!',
            'completed'   => 'Trip completed!',
        ];

        return back()->with('success', $messages[$request->trip_status]);
    }

    public function collectJoinerPassengerPayment($tripId, $bookingId)
    {
        $driver  = DB::table('drivers')->where('user_id', Auth::id())->first();
        $trip    = DB::table('joiner_trips')->where('id', $tripId)->where('driver_name', $driver->name)->first();
        if (!$trip) abort(403);

        $booking = DB::table('joiner_bookings')->where('id', $bookingId)->where('joiner_trip_id', $tripId)->first();
        if (!$booking) return back()->with('error', 'Booking not found.');

        $actualPaid = ($booking->amount_paid > 0) ? $booking->amount_paid : ($booking->downpayment ?? 0);
        $balance    = $booking->total_price - $actualPaid;
        if ($balance <= 0) return back()->with('error', 'Already fully paid.');

        DB::table('joiner_bookings')->where('id', $bookingId)->update([
            'amount_paid'    => $booking->total_price,
            'payment_status' => 'fully_paid',
            'status'         => 'fully_paid',
            'updated_at'     => now(),
        ]);

        return back()->with('success', $booking->passenger_name . ' — ₱' . number_format($balance, 2) . ' collected.');
    }

    public function tourPackageManifest($id)
    {
        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();
        if (!$driver) return redirect('/login');

        $tour = DB::table('tour_packages')
            ->where('id', $id)
            ->where('driver_name', $driver->name)
            ->first();
        if (!$tour) abort(403);

        $bookings = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->where('bookings.tour_id', $id)
            ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
            ->select(
                'bookings.*',
                'users.first_name', 'users.last_name', 'users.email',
                DB::raw('COALESCE(NULLIF(bookings.phone,""), users.phone_number) as contact_number')
            )
            ->orderBy('bookings.created_at')
            ->get()
            ->map(function ($b) {
                $b->passengers = DB::table('passengers')->where('booking_id', $b->id)->get();
                // online_paid = what Paymongo charged (downpayment column)
                // driver_collected = cash the driver physically got (amount_paid minus what was already online)
                $b->online_paid       = (float) ($b->downpayment ?? 0);
                $b->driver_collected  = max(0, (float) ($b->amount_paid ?? 0) - $b->online_paid);
                $b->actual_paid       = $b->online_paid + $b->driver_collected;
                $b->balance           = max(0, (float) $b->total - $b->actual_paid);
                return $b;
            });

        $totalPassengers       = $bookings->sum(fn($b) => $b->passengers->count() ?: 1);
        $totalCashCollected    = $bookings->sum('driver_collected');
        $totalOutstanding      = $bookings->sum('balance');
        $totalDue              = $bookings->sum('total');
        $allPaid               = $bookings->every(fn($b) => $b->balance <= 0);
        $hasApprovedBookings   = DB::table('bookings')
            ->where('tour_id', $id)
            ->where('status', 'approved')
            ->exists();

        return view('driver.tour-manifest', compact(
            'tour', 'bookings', 'totalPassengers',
            'totalCashCollected', 'totalOutstanding', 'totalDue', 'allPaid',
            'hasApprovedBookings'
        ));
    }

    public function updateTourPackageStatus(Request $request, $id)
    {
        $request->validate(['trip_status' => 'required|in:arrived,in_progress,completed']);

        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();
        $tour   = DB::table('tour_packages')->where('id', $id)->where('driver_name', $driver->name)->first();
        if (!$tour) abort(403);

        $order   = ['not_started' => 0, 'arrived' => 1, 'in_progress' => 2, 'completed' => 3];
        $current = $order[$tour->trip_status ?? 'not_started'] ?? 0;
        $next    = $order[$request->trip_status] ?? 0;

        if ($next !== $current + 1) {
            return back()->with('error', 'Invalid status transition.');
        }

        if ($request->trip_status === 'arrived') {
            $hasApproved = DB::table('bookings')
                ->where('tour_id', $id)
                ->where('status', 'approved')
                ->exists();
            if (!$hasApproved) {
                return back()->with('error', 'Cannot mark as Arrived — no bookings have been approved by admin yet.');
            }
        }

        DB::table('tour_packages')->where('id', $id)->update([
            'trip_status' => $request->trip_status,
            'updated_at'  => now(),
        ]);

        $messages = [
            'arrived'     => 'Marked as arrived at meetup point.',
            'in_progress' => 'Trip is now in progress!',
            'completed'   => 'Trip completed!',
        ];

        return back()->with('success', $messages[$request->trip_status]);
    }

    public function collectTourBookingPayment($tourId, $bookingId)
    {
        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();
        $tour   = DB::table('tour_packages')->where('id', $tourId)->where('driver_name', $driver->name)->first();
        if (!$tour) abort(403);

        $booking = DB::table('bookings')->where('id', $bookingId)->where('tour_id', $tourId)->first();
        if (!$booking) return back()->with('error', 'Booking not found.');

        $onlinePaid = (float) ($booking->downpayment ?? 0);
        $totalPaid  = (float) ($booking->amount_paid ?? 0);
        $actualPaid = $onlinePaid + max(0, $totalPaid - $onlinePaid);
        $balance    = max(0, (float) $booking->total - $actualPaid);
        if ($balance <= 0) return back()->with('error', 'Already fully paid.');

        DB::table('bookings')->where('id', $bookingId)->update([
            'amount_paid'       => $booking->total,
            'remaining_balance' => 0,
            'payment_status'    => 'fully_paid',
            'updated_at'        => now(),
        ]);

        return back()->with('success', 'Balance of ₱' . number_format($balance, 2) . ' collected.');
    }

    public function collectPayment(Request $request)
    {
        $request->validate(['booking_id' => 'required|integer']);

        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();
        if (!$driver) return back()->with('error', 'Unauthorized.');

        $booking = DB::table('bookings')
            ->where('id', $request->booking_id)
            ->where('driver', $driver->id)
            ->first();

        if (!$booking) return back()->with('error', 'Booking not found or not assigned to you.');

        $balance = $booking->remaining_balance ?? ($booking->total - ($booking->amount_paid ?? 0));

        if ($balance <= 0) return back()->with('error', 'No outstanding balance on this booking.');

        DB::table('bookings')->where('id', $request->booking_id)->update([
            'amount_paid'       => $booking->total,
            'remaining_balance' => 0,
            'payment_status'    => 'fully_paid',
            'updated_at'        => now(),
        ]);

        return back()->with('success', '₱' . number_format($balance, 2) . ' collected successfully. Trip is now fully paid.');
    }

    public function rentalManifest($id)
    {
        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();
        if (!$driver) return redirect('/login');

        $booking = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('vans', 'bookings.van_id', '=', 'vans.id')
            ->where('bookings.id', $id)
            ->where('bookings.driver', $driver->id)
            ->select(
                'bookings.*',
                'users.first_name', 'users.last_name', 'users.email',
                'vans.name as van_name', 'vans.plate_number',
                DB::raw('COALESCE(NULLIF(bookings.phone,""), users.phone_number) as contact_number')
            )
            ->first();

        if (!$booking) abort(403);

        $passengers = DB::table('passengers')->where('booking_id', $id)->get();

        return view('driver.rental-manifest', compact('booking', 'passengers', 'driver'));
    }

    public function updateTripStatus(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'trip_status' => 'required|in:arrived,in_progress,completed',
        ]);

        $driver = DB::table('drivers')->where('user_id', Auth::id())->first();

        if (!$driver) {
            return back()->with('error', 'Unauthorized.');
        }

        $booking = DB::table('bookings')
            ->where('id', $request->booking_id)
            ->where('driver', $driver->id)
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found or not assigned to you.');
        }

        // Enforce progression order
        $order = ['not_started' => 0, 'arrived' => 1, 'in_progress' => 2, 'completed' => 3];
        $current = $order[$booking->trip_status] ?? 0;
        $next    = $order[$request->trip_status] ?? 0;

        if ($next !== $current + 1) {
            return back()->with('error', 'Invalid trip status transition.');
        }

        // For completing: require full payment
        if ($request->trip_status === 'completed') {
            $balance = $booking->remaining_balance ?? ($booking->total - ($booking->amount_paid ?? 0));
            if ($balance > 0) {
                return back()->with('error', 'Trip cannot be completed — there is still a remaining balance of ₱' . number_format($balance, 2) . '.');
            }
        }

        $updateData = ['trip_status' => $request->trip_status, 'updated_at' => now()];

        DB::table('bookings')->where('id', $request->booking_id)->update($updateData);

        $messages = [
            'arrived'     => 'Marked as arrived at pickup point.',
            'in_progress' => 'Trip is now in progress!',
            'completed'   => 'Trip completed successfully!',
        ];

        return back()->with('success', $messages[$request->trip_status]);
    }
}
