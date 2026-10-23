<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\BookingApproved;
use App\Notifications\BookingRejected;
use App\Notifications\NewTripAssigned;
use App\Notifications\TripCompletedFeedbackRequest;
use App\Http\Traits\BookingValidator;

class AdminController extends Controller
{
    use BookingValidator;

    public function updateStatus(Request $request)
    {
        $booking = DB::table('bookings')
            ->where('id', $request->booking_id)
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found');
        }

        // ✅ APPROVE
        if ($request->status == 'approved') {
            // Approving is the moment the van/driver are actually committed —
            // make sure nothing else already claims them on these dates.
            if ($booking->tour_id) {
                $tour = DB::table('tour_packages')->where('id', $booking->tour_id)->first();
                $vanPlate  = $tour->plate_number ?? null;
                $driverRef = $tour->driver_name ?? null;
            } else {
                $vanPlate  = $booking->plate_number
                    ?: DB::table('vans')->where('id', $booking->van_id)->value('plate_number');
                $driverRef = $booking->driver;
            }

            $conflictDate = null;
            for ($d = strtotime($booking->start_date); $d <= strtotime($booking->end_date); $d = strtotime('+1 day', $d)) {
                $checkDate = date('Y-m-d', $d);
                if (!$this->checkAvailability($vanPlate, $driverRef, $checkDate, $booking->id)) {
                    $conflictDate = $checkDate;
                    break;
                }
            }

            if ($conflictDate) {
                return back()->with('error', "❌ Cannot approve booking #{$booking->id}: the van or driver already has another active booking on {$conflictDate}. Reassign the van/driver, or resolve the conflicting booking first.");
            }

            DB::table('bookings')
                ->where('id', $request->booking_id)
                ->update(['status' => 'approved']);

            $user = User::find($booking->user_id);
            if ($user) {
                $user->notify(new BookingApproved($booking));
            }

            if ($driverRef) {
                [$driverId, ] = $this->resolveDriverIdAndName($driverRef);
                if ($driverId) {
                    $driverUserId = DB::table('drivers')->where('id', $driverId)->value('user_id');
                    if ($driverUserId) {
                        $driverUser = User::find($driverUserId);
                        if ($driverUser) {
                            $tripType = $booking->tour_id ? 'tour booking' : 'van rental';
                            $destination = $booking->destination ?? $booking->package_name ?? 'a trip';
                            $driverUser->notify(new NewTripAssigned($tripType, $destination, $booking->start_date));
                        }
                    }
                }
            }
        }

        // ❌ REJECT
        if ($request->status == 'rejected') {
            $request->validate([
                'reason' => 'required|string|max:1000',
            ]);

            DB::table('bookings')
                ->where('id', $request->booking_id)
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->reason,
                ]);

            $user = User::find($booking->user_id);
            if ($user) {
                $destination = $booking->destination ?? $booking->package_name ?? 'N/A';
                $user->notify(new BookingRejected($booking->id, $destination, 'Van Rental', $request->reason, $booking->amount_paid));
            }
        }

        // ✔ COMPLETE
        if ($request->status === 'completed') {
            DB::table('bookings')
                ->where('id', $request->booking_id)
                ->update([
                    'status' => 'completed',
                    'payment_status' => 'fully_paid',
                    'amount_paid' => $booking->total,
                    'remaining_balance' => 0,
                ]);

            $user = User::find($booking->user_id);
            if ($user) {
                $destination = $booking->destination ?? $booking->package_name ?? 'your trip';
                $user->notify(new TripCompletedFeedbackRequest($booking->id, $destination));
            }
        }

        return back()->with('success', 'Status updated successfully');
    }

    public function dashboard(Request $request)
{
    $search = $request->input('search');
    $tripType = $request->input('trip_type');

    // --- NEW: Strip prefixes to allow searching "JT-20" or "PV-42" ---
    $cleanSearch = $search;
    if ($search) {
        $cleanSearch = preg_replace('/^(jt-|pv-)/i', '', $search);
    }

    // Stats Logic
    $totalBookings = DB::table('bookings')->count() + DB::table('joiner_bookings')->count();

    // --- FIX: Total Revenue now only sums 'completed' trips ---
    $privateRevenue = DB::table('bookings')
        ->where('status', 'completed')
        ->sum('total');

    $joinerRevenue = DB::table('joiner_bookings')
        ->where('status', 'completed')
        ->sum('total_price');

    $totalRevenue = $privateRevenue + $joinerRevenue;

    // Counts for Pending and Completed status
    $pending = DB::table('bookings')->where('status', 'pending')->count() +
               DB::table('joiner_bookings')->where('status', 'pending')->count();

    $completed = DB::table('bookings')->where('status', 'completed')->count() +
                 DB::table('joiner_bookings')->where('status', 'completed')->count();

    // 1. Private Query
    $privateQuery = DB::table('bookings')
        ->select(
            DB::raw("CONCAT('PV-', id) as display_id"),
            'pickup', 'destination', 'total', 'status', 'start_date as date', 'created_at'
        )
        ->when($cleanSearch, function ($query, $cleanSearch) {
            return $query->where(function($q) use ($cleanSearch) {
                $q->where('id', 'like', "%{$cleanSearch}%")
                  ->orWhere('destination', 'like', "%{$cleanSearch}%")
                  ->orWhere('pickup', 'like', "%{$cleanSearch}%");
            });
        });

    // 2. Joiner Query
    $joinerQuery = DB::table('joiner_bookings')
        ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
        ->select(
            DB::raw("CONCAT('JT-', joiner_bookings.id) as display_id"),
            'joiner_trips.meetup_point as pickup',
            'joiner_trips.destination',
            'joiner_bookings.total_price as total',
            'joiner_bookings.status',
            'joiner_trips.trip_date as date',
            'joiner_bookings.created_at'
        )
        ->when($cleanSearch, function ($query, $cleanSearch) {
            return $query->where(function($q) use ($cleanSearch) {
                $q->where('joiner_bookings.id', 'like', "%{$cleanSearch}%")
                  ->orWhere('joiner_trips.destination', 'like', "%{$cleanSearch}%")
                  ->orWhere('joiner_trips.meetup_point', 'like', "%{$cleanSearch}%");
            });
        });

    // 3. Apply Trip Type Filtering Logic
    if ($tripType === 'private') {
        $recent = $privateQuery->orderBy('created_at', 'desc')->limit(10)->get();
    } elseif ($tripType === 'joiner') {
        $recent = $joinerQuery->orderBy('created_at', 'desc')->limit(10)->get();
    } else {
        $recent = $joinerQuery->union($privateQuery)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    return view('admin.dashboard', compact(
        'totalBookings', 'totalRevenue', 'pending', 'completed', 'recent'
    ));
}

    public function bookings(Request $request)
{
    $search = $request->input('search');
    $filter = $request->input('filter', 'all');

    $cleanSearch = $search;
    if ($search) {
        $cleanSearch = preg_replace('/^#/', '', $search);
    }

    $bookings = DB::table('bookings')
        ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
        ->leftJoin('tour_packages', 'bookings.tour_id', '=', 'tour_packages.id')
        ->leftJoin('vans', 'bookings.van_id', '=', 'vans.id')
        ->leftJoin('drivers', 'bookings.driver', '=', 'drivers.id')
        ->leftJoin('booking_refunds', function ($join) {
            $join->on('booking_refunds.booking_id', '=', 'bookings.id')->where('booking_refunds.source', 'bookings');
        })
        ->select(
            'bookings.*',
            'booking_refunds.amount as refund_amount',
            'booking_refunds.proof_path as refund_proof_path',
            'booking_refunds.refunded_at',
            'users.first_name',
            'users.last_name',
            DB::raw('COALESCE(NULLIF(bookings.phone, ""), users.phone_number) as contact_number'),
            DB::raw('IFNULL(tour_packages.van, vans.name) as display_van'),
            DB::raw('IFNULL(tour_packages.driver_name, drivers.name) as display_driver'),
            DB::raw('CASE WHEN bookings.tour_id IS NOT NULL THEN COALESCE(tour_packages.trip_status, "not_started") ELSE COALESCE(NULLIF(bookings.trip_status, ""), "not_started") END as eff_trip_status'),
            DB::raw('GREATEST(0, bookings.total - COALESCE(bookings.amount_paid, 0)) as computed_balance')
        )
        ->when($cleanSearch, function ($query, $cleanSearch) {
            return $query->where(function($q) use ($cleanSearch) {
                $q->where('bookings.id', 'like', "%{$cleanSearch}%")
                  ->orWhere(DB::raw("CONCAT(users.first_name, ' ', users.last_name)"), 'like', "%{$cleanSearch}%")
                  ->orWhere('bookings.destination', 'like', "%{$cleanSearch}%")
                  ->orWhere('bookings.pickup', 'like', "%{$cleanSearch}%");
            });
        })
        ->when($filter === 'pending', fn($q) => $q->whereIn('bookings.status', ['pending', 'downpayment_paid', 'fully_paid']))
        ->when($filter === 'ongoing', fn($q) => $q->where('bookings.status', 'approved'))
        ->when($filter === 'completed', fn($q) => $q->where('bookings.status', 'completed'))
        ->when($filter === 'rejected', fn($q) => $q->where('bookings.status', 'rejected'))
        ->orderBy('bookings.id', 'desc')
        ->paginate(15)
        ->withQueryString(); // keep ?search= and ?filter= on the page links

    // A page past the end (e.g. after bookings were removed) → go to the last real page.
    if ($bookings->isEmpty() && $bookings->currentPage() > 1) {
        return redirect($bookings->url(max(1, $bookings->lastPage())));
    }

    // Payment history per booking, for the "Paid" popup — grouped in one
    // query instead of one per row, for this page's bookings only.
    $paymentHistories = DB::table('booking_payments')
        ->where('source', 'bookings')
        ->whereIn('booking_id', $bookings->pluck('id'))
        ->orderBy('paid_at')
        ->get()
        ->groupBy('booking_id');

    // Active bookings that share a van (plate) or driver on overlapping dates —
    // flagged in the table so the admin can reassign one of them. Bookings of
    // the same tour package legitimately share its van/driver, so skip those pairs.
    $active = DB::table('bookings')
        ->leftJoin('tour_packages', 'bookings.tour_id', '=', 'tour_packages.id')
        ->whereNotIn('bookings.status', ['rejected', 'cancelled', 'completed'])
        ->get([
            'bookings.id', 'bookings.start_date', 'bookings.end_date', 'bookings.tour_id', 'bookings.driver',
            DB::raw('COALESCE(tour_packages.plate_number, bookings.plate_number) as plate'),
        ]);

    $conflicts = [];
    foreach ($active as $i => $a) {
        foreach ($active->slice($i + 1) as $b) {
            if ($a->tour_id && $a->tour_id === $b->tour_id) continue;
            if ($a->start_date > $b->end_date || $b->start_date > $a->end_date) continue;
            $sameVan    = $a->plate && $a->plate === $b->plate;
            $sameDriver = $a->driver && (string) $a->driver === (string) $b->driver;
            if ($sameVan || $sameDriver) {
                $conflicts[$a->id][] = $b->id;
                $conflicts[$b->id][] = $a->id;
            }
        }
    }

    $bookings->through(function ($booking) use ($paymentHistories, $conflicts) {
        $booking->payments = $paymentHistories->get($booking->id, collect())->values();
        $booking->conflicts_with = $conflicts[$booking->id] ?? [];
        $booking->refundable_amount = \App\Support\BookingRefund::eligibleAmount($booking->status, $booking->payment_status ?? null, $booking->amount_paid, $booking->created_at, $booking->cancelled_at ?? null, $booking->start_date);
        return $booking;
    });

    // Counts for filter badges
    $counts = DB::table('bookings')
        ->select(
            DB::raw("SUM(status IN ('pending','downpayment_paid','fully_paid')) as pending_count"),
            DB::raw("SUM(status = 'approved') as ongoing_count"),
            DB::raw("SUM(status = 'completed') as completed_count"),
            DB::raw("SUM(status = 'rejected') as rejected_count")
        )->first();

    return view('admin.admin-booking', compact('bookings', 'filter', 'counts'));
}

    public function pricing()
    {
        $pricing = DB::table('pricing')->where('id', 1)->first();

        if (!$pricing) {
            DB::table('pricing')->insert([
                'id' => 1,
                'base_fare' => 2000,
                'price_per_km' => 10,
                'driver_fee' => 500,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $pricing = DB::table('pricing')->where('id', 1)->first();
        }

        // The base fare must sit inside every van's price range, so the only
        // allowed values are from the highest van minimum to the lowest van maximum.
        $allowedMin = DB::table('vans')->max('price_min');
        $allowedMax = DB::table('vans')->min('price_max');
        $outOfRangeVans = $this->vansOutsideBaseFare($pricing->base_fare);

        $history = DB::table('price_changes')
            ->leftJoin('users', 'price_changes.changed_by', '=', 'users.id')
            ->select('price_changes.*', 'users.first_name', 'users.last_name')
            ->orderByDesc('price_changes.id')
            ->paginate(15);

        return view('admin.pricing', compact('pricing', 'allowedMin', 'allowedMax', 'outOfRangeVans', 'history'));
    }

    // Vans whose price range doesn't include the given base fare.
    private function vansOutsideBaseFare($baseFare)
    {
        return DB::table('vans')
            ->where(function ($q) use ($baseFare) {
                $q->where('price_min', '>', $baseFare)->orWhere('price_max', '<', $baseFare);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'plate_number', 'price_min', 'price_max']);
    }

    public function updatePricing(Request $request)
    {
        $request->validate([
            'base_fare' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'driver_fee' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        // The base fare has to fall inside every van's price range. If it
        // wouldn't, the admin must adjust those vans' ranges first.
        $conflicts = $this->vansOutsideBaseFare($request->base_fare);
        if ($conflicts->isNotEmpty()) {
            $list = $conflicts->map(fn ($v) => "{$v->name} ({$v->plate_number}): ₱" . number_format($v->price_min) . ' – ₱' . number_format($v->price_max))->implode('; ');

            return back()->withInput()->with('error',
                'Base fare ₱' . number_format($request->base_fare) . " is outside the price range of: {$list}. "
                . 'Update these vans\' price ranges on the Vans page first, or choose a base fare within every van\'s range.');
        }

        $old = DB::table('pricing')->where('id', 1)->first();

        DB::transaction(function () use ($request, $old) {
            DB::table('pricing')->where('id', 1)->update([
                'base_fare' => $request->base_fare,
                'price_per_km' => $request->price_per_km,
                'driver_fee' => $request->driver_fee,
                'last_update_reason' => $request->reason,
                'updated_at' => now()
            ]);

            foreach (['base_fare', 'price_per_km', 'driver_fee'] as $field) {
                $this->logPriceChange($field, $old->$field ?? null, $request->$field, $request->reason);
            }
        });

        return back()->with('success', 'Pricing updated successfully!');
    }

    // One history row per value that actually changed.
    private function logPriceChange(string $field, $oldValue, $newValue, ?string $reason, $van = null): void
    {
        if ($oldValue !== null && round((float) $oldValue, 2) === round((float) $newValue, 2)) {
            return;
        }

        DB::table('price_changes')->insert([
            'field'      => $field,
            'van_id'     => $van->id ?? null,
            'van_label'  => $van ? "{$van->name} ({$van->plate_number})" : null,
            'old_value'  => $oldValue,
            'new_value'  => $newValue,
            'reason'     => $reason,
            'changed_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ✅ WALK-IN BOOKING (admin books on behalf of a customer)
    public function bookForCustomerPage(Request $request)
    {
        $search = $request->get('search');

        $customers = DB::table('users')
            ->where('role', 'customer')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->limit(50)
            ->get();

        $actingAs = null;
        if ($id = session('admin_acting_customer_id')) {
            $actingAs = DB::table('users')->find($id);
        }

        return view('admin.book-for-customer', compact('customers', 'search', 'actingAs'));
    }

    public function selectBookingCustomer(Request $request)
    {
        $request->validate(['customer_id' => 'required|exists:users,id']);

        session(['admin_acting_customer_id' => (int) $request->customer_id]);

        return redirect('/')->with('success', 'You are now booking on behalf of this customer. Go through checkout as normal, then return to Admin > New Booking when done.');
    }

    public function createBookingCustomer(Request $request)
    {
        $request->validate([
            'first_name'   => ['required', 'string', 'max:30', 'regex:/^(?!\s)[A-Za-zÀ-ÿ \'\-]+$/u', new \App\Rules\PersonName],
            'last_name'    => ['required', 'string', 'max:30', 'regex:/^(?!\s)[A-Za-zÀ-ÿ \'\-]+$/u', new \App\Rules\PersonName],
            'email'        => ['required', 'email', 'unique:users,email'],
            'phone_number' => ['required', 'regex:/^09\d{9}$/', 'unique:users,phone_number'],
        ]);

        $userId = DB::table('users')->insertGetId([
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
            'phone_number' => $request->phone_number,
            'password'     => Hash::make(Str::random(24)),
            'role'         => 'customer',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        session(['admin_acting_customer_id' => $userId]);

        return redirect('/')->with('success', 'New customer created. You are now booking on their behalf — go through checkout as normal, then return to Admin > New Booking when done.');
    }

    public function stopBookingForCustomer()
    {
        session()->forget('admin_acting_customer_id');
        return redirect()->route('admin.book_for_customer')->with('success', 'Exited walk-in booking mode.');
    }

    // ✅ LIVE DRIVER MAP
    public function liveMap()
    {
        return view('admin.live-map');
    }

    public function driverLocations()
    {
        // Same 'Y-m-d H:i:s' (UTC) format as location_updated_at, so plain string comparison works
        $liveSince = now()->subSeconds(\App\Support\DriverLocation::LIVE_SECONDS)->toDateTimeString();
        $idleSince = now()->subSeconds(\App\Support\DriverLocation::IDLE_SECONDS)->toDateTimeString();

        $drivers = DB::table('drivers')
            ->whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->select('id', 'name', 'phone', 'status', 'current_lat', 'current_lng', 'location_accuracy',
                     'location_speed', 'location_heading', 'location_source', 'location_updated_at')
            ->orderByDesc('location_updated_at')
            ->get();

        $drivers = $drivers->map(function ($driver) use ($liveSince, $idleSince) {
            // live: reporting now · idle: stopped / weak signal, last spot still shown · offline: gone quiet
            $driver->state = match (true) {
                $driver->location_updated_at >= $liveSince => 'live',
                $driver->location_updated_at >= $idleSince => 'idle',
                default                                    => 'offline',
            };
            $driver->is_live = $driver->state === 'live';
            $driver->current_trip = $this->currentTripForDriver($driver);
            return $driver;
        });

        return response()->json(['drivers' => $drivers]);
    }

    // Finds the trip a driver is actively out on right now (arrived/in transit),
    // across the three booking types, so the live map can show what they're doing.
    private function currentTripForDriver($driver)
    {
        $activeStatuses = ['arrived', 'in_progress'];

        $rental = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->where('bookings.driver', $driver->id)
            ->whereIn('bookings.trip_status', $activeStatuses)
            ->select('bookings.destination', 'bookings.trip_status', 'users.first_name', 'users.last_name')
            ->first();

        if ($rental) {
            return [
                'type'        => 'Van Booking',
                'destination' => $rental->destination,
                'customer'    => trim(($rental->first_name ?? '') . ' ' . ($rental->last_name ?? '')),
                'status'      => $rental->trip_status,
            ];
        }

        $joiner = DB::table('joiner_trips')
            ->where('driver_name', $driver->name)
            ->whereIn('trip_status', $activeStatuses)
            ->select('destination', 'trip_status')
            ->first();

        if ($joiner) {
            return [
                'type'        => 'Joiner Trip',
                'destination' => $joiner->destination,
                'customer'    => null,
                'status'      => $joiner->trip_status,
            ];
        }

        $tour = DB::table('tour_packages')
            ->where('driver_name', $driver->name)
            ->whereIn('trip_status', $activeStatuses)
            ->select('destination', 'trip_status')
            ->first();

        if ($tour) {
            return [
                'type'        => 'Tour Package',
                'destination' => $tour->destination,
                'customer'    => null,
                'status'      => $tour->trip_status,
            ];
        }

        return null;
    }

    // ✅ TOGGLE DRIVER STATUS
    public function toggleDriver($id)
    {
        $driver = DB::table('drivers')->where('id', $id)->first();
        if (!$driver) return back()->with('error', 'Driver not found.');

        $newStatus = ($driver->status === 'available') ? 'unavailable' : 'available';

        DB::table('drivers')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Driver status updated!');
    }

    // ✅ RECORD A REFUND for a rejected / cancelled booking and tell the customer
    public function refundBooking(Request $request, $id)
    {
        $booking = DB::table('bookings')->where('id', $id)->first();
        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        $eligible = \App\Support\BookingRefund::eligibleAmount($booking->status, $booking->payment_status ?? null, $booking->amount_paid, $booking->created_at, $booking->cancelled_at ?? null, $booking->start_date);
        if ($eligible <= 0) {
            return back()->with('error', "Booking #{$id} has nothing to refund.");
        }
        if (\App\Support\BookingRefund::find($id)) {
            return back()->with('error', "Booking #{$id} has already been refunded.");
        }

        $paid = round((float) $booking->amount_paid, 2);
        // Report as a page-level error; the bookings table has no per-field error slots.
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'amount' => "required|numeric|min:1|max:{$paid}",
            'proof'  => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes'  => 'required|string|max:500', // message shown to the customer
        ], [
            'notes.required' => 'Please write a message to the customer.',
            'amount.max'     => 'The refund can\'t be more than the ₱' . number_format($paid, 2) . ' the customer paid.',
            'proof.required' => 'Please attach a photo of the refund receipt.',
            'proof.image'    => 'The refund receipt must be a photo (JPG, PNG or WEBP).',
            'proof.max'      => 'The refund receipt photo must be 5 MB or smaller.',
        ]);
        if ($validator->fails()) {
            return back()->with('error', "Booking #{$id}: " . $validator->errors()->first());
        }
        $data = $validator->validated();

        // Random file name, so the photo's link can't be guessed from the booking id
        $proofPath = $request->file('proof')->store('refunds', 'public');

        DB::table('booking_refunds')->insert([
            'booking_id'  => $id,
            'source'      => 'bookings',
            'amount'      => $data['amount'],
            'proof_path'  => $proofPath,
            'notes'       => trim($data['notes']),
            'refunded_by' => auth()->id(),
            'refunded_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $user = User::find($booking->user_id);
        if ($user) {
            $user->notify(new \App\Notifications\BookingRefunded(
                $booking->id,
                $booking->package_name ?? $booking->destination ?? 'N/A',
                $booking->tour_id ? 'Tour Booking' : 'Van Rental',
                (float) $data['amount'],
                $proofPath,
                trim($data['notes'])
            ));
        }

        return back()->with('success', "Refund of ₱" . number_format($data['amount'], 2) . " recorded for booking #{$id}. The customer has been notified.");
    }

    // ✅ TOGGLE VAN STATUS
    public function toggleVan($id)
    {
        $van = DB::table('vans')->where('id', $id)->first();
        if (!$van) return back()->with('error', 'Van not found.');

        $newStatus = ($van->status === 'available') ? 'unavailable' : 'available';

        DB::table('vans')->where('id', $id)->update([
            'status' => $newStatus,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Van status updated!');
    }

    // ✅ SHOW EDIT DRIVER PAGE
public function editDriver($id)
{
    $driver = DB::table('drivers')->where('id', $id)->first();

    if (!$driver) {
        return redirect('/admin/drivers')->with('error', 'Driver not found.');
    }

    return view('admin.edit-driver', compact('driver'));
}

// ✅ UPDATE DRIVER DATA
public function updateDriver(Request $request, $id)
{
    // 1. Strict Validation
    $request->validate([
        'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-]+$/'],
        'phone' => ['required', 'regex:/^09\d{9}$/'],
        'license' => 'required|string|max:255',
        'license_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Optional on update
        'day_off' => ['nullable', 'integer', 'between:0,6'],
    ], [
        'name.regex' => 'The driver name cannot contain numbers.',
        'phone.regex' => 'The phone number must start with 09 and be exactly 11 digits.',
    ]);

    // 2. Prepare the basic data
    $updateData = [
        'name' => $request->name,
        'phone' => $request->phone,
        'license_number' => $request->license,
        'day_off' => $request->filled('day_off') ? (int) $request->day_off : null,
        'updated_at' => now()
    ];

    // 3. Check if a new license photo was uploaded
    if ($request->hasFile('license_image')) {
        // Store the new image
        $imagePath = $request->file('license_image')->store('licenses', 'public');

        // Add the new path to the update array
        $updateData['license_image'] = $imagePath;
    }

    // 4. Execute the update
    DB::table('drivers')
        ->where('id', $id)
        ->update($updateData);

    return redirect('/admin/drivers')->with('success', 'Driver information and license updated!');
}

// ✅ DELETE DRIVER
public function deleteDriver($id)
{
    // Find the driver first to make sure they exist
    $driver = DB::table('drivers')->where('id', $id)->first();

    if (!$driver) {
        return back()->with('error', 'Driver not found.');
    }

    // Delete the record
    DB::table('drivers')->where('id', $id)->delete();

    return back()->with('success', 'Driver removed from the system.');
}

// ✅ ADD NEW DRIVER
public function addDriver(Request $request)
{
    // 1. VALIDATION WITH UNIQUE CHECKS
    $request->validate([
        // unique:table,column_name
        'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s\-]+$/', 'unique:drivers,name'],
        'phone' => ['required', 'regex:/^09\d{9}$/', 'unique:drivers,phone'],

        // MATCHED TO YOUR DB: license_number
        'license' => ['required', 'string', 'max:255', 'unique:drivers,license_number'],

        'license_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        'day_off' => ['nullable', 'integer', 'between:0,6'],
    ], [
        // Custom Error Messages for the Admin Alert
        'name.unique' => 'Duplicate Entry: This driver name is already in the list.',
        'phone.unique' => 'Duplicate Entry: This phone number is already registered.',
        'license.unique' => 'Duplicate Entry: This license number already exists.',
        'name.regex' => 'The driver name cannot contain numbers or special characters.',
        'phone.regex' => 'The phone number must start with 09 and be 11 digits.',
    ]);

    // 2. FILE UPLOAD
    $imagePath = $request->file('license_image')->store('licenses', 'public');

    // 3. DATABASE INSERT
    DB::table('drivers')->insert([
        'name' => $request->name,
        'phone' => $request->phone,
        'license_number' => $request->license, // Matched to your DB screenshot
        'license_image' => $imagePath,
        'status' => 'available', // lowercase — matches toggleDriverStatus() and every "status = 'available'" filter elsewhere
        'day_off' => $request->filled('day_off') ? (int) $request->day_off : null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Driver verified and added successfully!');
}

// ✅ CREATE DRIVER LOGIN ACCOUNT
public function createDriverAccount(Request $request, $id)
{
    $request->validate([
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
    ]);

    $driver = DB::table('drivers')->where('id', $id)->first();
    if (!$driver) return back()->with('error', 'Driver not found.');
    if ($driver->user_id) return back()->with('error', 'This driver already has a login account.');

    $nameParts = explode(' ', trim($driver->name), 2);
    $firstName = $nameParts[0];
    $lastName  = $nameParts[1] ?? '';

    $userId = DB::table('users')->insertGetId([
        'first_name'   => $firstName,
        'last_name'    => $lastName,
        'email'        => $request->email,
        'phone_number' => $driver->phone,
        'password'     => \Illuminate\Support\Facades\Hash::make($request->password),
        'role'         => 'driver',
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    DB::table('drivers')->where('id', $id)->update(['user_id' => $userId, 'updated_at' => now()]);

    return back()->with('success', 'Driver login account created successfully! Email: ' . $request->email);
}

// ✅ REMOVE DRIVER LOGIN ACCOUNT
public function removeDriverAccount($id)
{
    $driver = DB::table('drivers')->where('id', $id)->first();
    if (!$driver || !$driver->user_id) return back()->with('error', 'No account linked to this driver.');

    DB::table('users')->where('id', $driver->user_id)->delete();
    DB::table('drivers')->where('id', $id)->update(['user_id' => null, 'updated_at' => now()]);

    return back()->with('success', 'Driver account removed.');
}

// ✅ CHANGE DRIVER PASSWORD
public function changeDriverPassword(Request $request, $id)
{
    $request->validate([
        'password' => 'required|min:8|confirmed',
    ]);

    $driver = DB::table('drivers')->where('id', $id)->first();
    if (!$driver || !$driver->user_id) return back()->with('error', 'No account linked to this driver.');

    DB::table('users')->where('id', $driver->user_id)->update([
        'password'   => \Illuminate\Support\Facades\Hash::make($request->password),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Password for ' . $driver->name . ' updated successfully.');
}

// ✅ ADD NEW VAN WITH IMAGE UPLOAD
public function addVan(Request $request)
{
    $pricing = DB::table('pricing')->where('id', 1)->first();
    $baseFare = $pricing ? $pricing->base_fare : 0;

    $request->validate([
        'name' => 'required|string|max:255',
        'color' => 'required|string|max:50',
        'plate_number' => 'required|string|max:100|unique:vans,plate_number',
        'seats' => 'required|integer|min:1',
        'transmission' => 'required|string',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ] + $this->vanPriceRules($baseFare), [
        'plate_number.unique' => 'That plate number is already registered. Please use a different plate.',
    ] + $this->vanPriceMessages($baseFare));

    $imagePath = null;
    if ($request->hasFile('image')) {
        // This stores the image in storage/app/public/vans
        $imagePath = $request->file('image')->store('vans', 'public');
    }

    DB::table('vans')->insert([
        'name' => $request->name,
        'color' => ucwords(trim($request->color)),
        'plate_number' => $request->plate_number,
        'seats' => $request->seats,
        'price_min' => $request->price_min,
        'price_max' => $request->price_max,
        'transmission' => $request->transmission,
        'image' => $imagePath,
        'status' => 'available',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Van added successfully!');
}

// ✅ DELETE VAN
// A van's price range must include the current base fare:
// price_min <= base fare <= price_max.
private function vanPriceRules($baseFare): array
{
    return [
        'price_min' => "required|numeric|min:1|max:{$baseFare}",
        'price_max' => "required|numeric|min:{$baseFare}|gte:price_min",
    ];
}

private function vanPriceMessages($baseFare): array
{
    $fare = '₱' . number_format($baseFare);
    return [
        'price_min.min' => 'Min price must be at least ₱1.',
        'price_min.max' => "Min price must not be higher than the base fare ({$fare}).",
        'price_max.min' => "Max price must not be lower than the base fare ({$fare}).",
        'price_max.gte' => 'Max price must be greater than or equal to min price.',
    ];
}

public function updateVanPrice(Request $request, $id)
{
    $van = DB::table('vans')->where('id', $id)->first();
    if (!$van) {
        return back()->with('error', 'Van not found.');
    }

    $baseFare = DB::table('pricing')->where('id', 1)->value('base_fare') ?? 0;
    // Report as a page-level error rather than under the Add Van form's fields.
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $this->vanPriceRules($baseFare), $this->vanPriceMessages($baseFare));
    if ($validator->fails()) {
        return back()->with('error', "{$van->name} ({$van->plate_number}): " . $validator->errors()->first());
    }

    DB::transaction(function () use ($request, $van, $id) {
        DB::table('vans')->where('id', $id)->update([
            'price_min'  => $request->price_min,
            'price_max'  => $request->price_max,
            'updated_at' => now(),
        ]);

        $reason = trim((string) $request->reason) ?: null;
        $this->logPriceChange('price_min', $van->price_min, $request->price_min, $reason, $van);
        $this->logPriceChange('price_max', $van->price_max, $request->price_max, $reason, $van);
    });

    return back()->with('success', "Price range of {$van->name} ({$van->plate_number}) updated to ₱"
        . number_format($request->price_min) . ' – ₱' . number_format($request->price_max) . '.');
}

// ✅ EDIT VAN DETAILS (price has its own modal so changes get logged)
public function updateVan(Request $request, $id)
{
    $van = DB::table('vans')->where('id', $id)->first();
    if (!$van) {
        return back()->with('error', 'Van not found.');
    }

    // Report as a page-level error rather than under the Add Van form's fields.
    $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
        'name'         => 'required|string|max:255',
        'color'        => 'required|string|max:50',
        'plate_number' => 'required|string|max:100|unique:vans,plate_number,' . $van->id,
        'seats'        => 'required|integer|min:1',
        'transmission' => 'required|string',
        'image'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ], [
        'plate_number.unique' => 'That plate number is already registered. Please use a different plate.',
    ]);
    if ($validator->fails()) {
        return back()->with('error', "{$van->name} ({$van->plate_number}): " . $validator->errors()->first());
    }

    $name  = trim($request->name);
    $plate = trim($request->plate_number);

    $data = [
        'name'         => $name,
        'color'        => ucwords(trim($request->color)),
        'plate_number' => $plate,
        'seats'        => $request->seats,
        'transmission' => $request->transmission,
        'updated_at'   => now(),
    ];
    if ($request->hasFile('image')) {
        $data['image'] = $request->file('image')->store('vans', 'public');
    }

    DB::transaction(function () use ($van, $data, $name, $plate) {
        DB::table('vans')->where('id', $van->id)->update($data);

        if ($name === $van->name && $plate === $van->plate_number) {
            return;
        }

        // Trips, tours and holds point at a van by its name/plate (availability
        // and the van calendar match on them), so carry the new values over.
        DB::table('joiner_trips')
            ->where(fn ($q) => $q->where('plate_number', $van->plate_number)
                ->orWhere(fn ($q2) => $q2->whereNull('plate_number')->where('van', $van->name)))
            ->update(['van' => $name, 'plate_number' => $plate]);

        DB::table('tour_packages')
            ->where(fn ($q) => $q->where('van_id', $van->id)->orWhere('plate_number', $van->plate_number))
            ->update(['van' => $name, 'plate_number' => $plate]);

        DB::table('bookings')->where('van_id', $van->id)
            ->update(['van' => $name, 'plate_number' => $plate]);

        DB::table('booking_holds')->where('plate_number', $van->plate_number)
            ->update(['plate_number' => $plate]);
    });

    return back()->with('success', "{$name} ({$plate}) updated successfully.");
}

public function deleteVan($id)
{
    $van = DB::table('vans')->where('id', $id)->first();

    if (!$van) {
        return back()->with('error', 'Van not found.');
    }

    // Delete the record from the database
    DB::table('vans')->where('id', $id)->delete();

    return back()->with('success', 'Van deleted successfully!');
}

// ✅ ADD PHOTOS TO A VAN'S GALLERY (shown as a carousel on the public van page)
public function addVanImages(Request $request, $id)
{
    $van = DB::table('vans')->where('id', $id)->first();
    if (!$van) {
        return back()->with('error', 'Van not found.');
    }

    $request->validate([
        'images'   => 'required|array|max:10',
        'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $nextOrder = (int) (DB::table('van_images')->where('van_id', $id)->max('sort_order')) + 1;

    foreach ($request->file('images') as $file) {
        $path = $file->store('vans', 'public');

        DB::table('van_images')->insert([
            'van_id'     => $id,
            'image'      => $path,
            'sort_order' => $nextOrder++,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return back()->with('success', 'Photo(s) added to the gallery!');
}

// ✅ REMOVE ONE PHOTO FROM A VAN'S GALLERY
public function deleteVanImage($imageId)
{
    $image = DB::table('van_images')->where('id', $imageId)->first();

    if (!$image) {
        return back()->with('error', 'Photo not found.');
    }

    \Storage::disk('public')->delete($image->image);
    DB::table('van_images')->where('id', $imageId)->delete();

    return back()->with('success', 'Photo removed.');
}

public function customers()
{
    $customers = DB::table('users')
        ->leftJoin('bookings', 'users.id', '=', 'bookings.user_id')
        ->select(
            'users.id', 'users.first_name', 'users.last_name',
            'users.email', 'users.phone_number', 'users.birthday',
            DB::raw('COUNT(DISTINCT bookings.id) as total_bookings')
        )
        ->where('users.role', 'customer')
        ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.phone_number', 'users.birthday')
        ->orderBy('users.first_name')
        ->get();

    $allBookings = DB::table('bookings')
        ->select('id', 'user_id', 'destination', 'start_date', 'end_date', 'status', 'total', 'van', 'driver')
        ->orderBy('start_date', 'desc')
        ->get()
        ->groupBy('user_id');

    $passengers = DB::table('passengers')
        ->select('booking_id', 'first_name', 'middle_name', 'last_name', 'birthday', 'gender')
        ->get()
        ->groupBy('booking_id');

    return view('admin.customers', compact('customers', 'allBookings', 'passengers'));
}

public function joinerTrips()
{
    // Rename $trips to $joinerTrips so the Blade file recognizes it
    $joinerTrips = DB::table('joiner_trips')->get();

    $vans    = DB::table('vans')->where('status', 'available')->get();
    $drivers = DB::table('drivers')->where('status', 'available')->get();

    return view('admin.joiner_trips', compact('joinerTrips', 'vans', 'drivers'));
}
public function editJoinerTrip($id)
{
    // 1. Find the trip or fail
    $trip = DB::table('joiner_trips')->where('id', $id)->first();

    if (!$trip) {
        return redirect('/admin/joiner-trips')->with('error', 'Trip not found.');
    }

    // 2. Fetch vans in case they want to change the assigned van
    $vans = DB::table('vans')->get();

    return view('admin.edit_joiner_trip', compact('trip', 'vans'));
}

public function updateJoinerTrip(Request $request, $id)
{
    $request->validate([
        'destination'   => 'required',
        'meetup_point'  => 'required',
        'trip_date'     => 'required|date',
        'total_seats'   => 'required|integer',
        'price_per_seat'=> 'required|numeric',
        'status'        => 'required|in:active,inactive,completed',
    ]);

    $trip = DB::table('joiner_trips')->where('id', $id)->first();
    if ($trip && $this->driverRestsOn($trip->driver_name, $request->trip_date)) {
        return back()->withInput()->with('error',
            "❌ {$trip->driver_name} has a weekly day off on " . \Illuminate\Support\Carbon::parse($request->trip_date)->format('l') . ". Pick another date or reassign the driver.");
    }

    DB::table('joiner_trips')->where('id', $id)->update([
        'destination' => $request->destination,
        'meetup_point' => $request->meetup_point,
        'trip_date' => $request->trip_date,
        'van' => $request->van,
        'total_seats' => $request->total_seats,
        'price_per_seat' => $request->price_per_seat,
        'status' => $request->status,
        'updated_at' => now(),
    ]);

    return redirect('/admin/joiner-trips')->with('success', 'Trip updated successfully!');
}
public function deleteJoinerTrip($id)
{
    // 1. Find the trip
    $trip = DB::table('joiner_trips')->where('id', $id)->first();

    if (!$trip) {
        return back()->with('error', 'Trip not found.');
    }

    // 2. Delete the record
    DB::table('joiner_trips')->where('id', $id)->delete();

    // 3. Redirect back with a success message
    return back()->with('success', 'Joiner trip deleted successfully!');
}

public function driverSchedule($id)
{
    $driver = DB::table('drivers')->where('id', $id)->first();
    if (!$driver) {
        return response()->json(['bookings' => [], 'driver' => null]);
    }

    // Van rental & tour bookings assigned to this driver
    $vanBookings = DB::table('bookings')
        ->whereRaw('CAST(driver AS CHAR) = ?', [(string) $id])
        ->whereNotIn('status', ['rejected', 'cancelled'])
        ->select('id', 'destination', 'start_date', 'end_date',
                 DB::raw("'Van Rental' as type"), 'status')
        ->get();

    // Joiner trips assigned by driver name
    $joinerTrips = DB::table('joiner_trips')
        ->where('driver_name', $driver->name)
        ->whereNotIn('status', ['cancelled', 'rejected'])
        ->select('id', 'destination',
                 'trip_date as start_date', 'trip_date as end_date',
                 DB::raw("'Joiner Trip' as type"), 'status')
        ->get();

    $all = $vanBookings->concat($joinerTrips)->map(fn($b) => [
        'id'          => $b->id,
        'destination' => $b->destination,
        'start'       => $b->start_date,
        'end'         => $b->end_date,
        'type'        => $b->type,
        'status'      => $b->status,
    ])->values();

    return response()->json([
        'driver'   => $driver->name,
        'bookings' => $all,
    ]);
}

public function vanSchedule($id)
{
    $van = DB::table('vans')->where('id', $id)->first();
    if (!$van) {
        return response()->json(['bookings' => [], 'van' => null]);
    }

    // Van rental & tour bookings assigned to this van
    $vanBookings = DB::table('bookings')
        ->where('van_id', $id)
        ->whereNotIn('status', ['rejected', 'cancelled'])
        ->select('id', 'destination', 'start_date', 'end_date',
                 DB::raw("'Van Rental' as type"), 'status')
        ->get();

    // Joiner trips using this van by name
    $joinerTrips = DB::table('joiner_trips')
        ->where('van', $van->name)
        ->whereNotIn('status', ['cancelled', 'rejected'])
        ->select('id', 'destination',
                 'trip_date as start_date', 'trip_date as end_date',
                 DB::raw("'Joiner Trip' as type"), 'status')
        ->get();

    $all = $vanBookings->concat($joinerTrips)->map(fn($b) => [
        'id'          => $b->id,
        'destination' => $b->destination,
        'start'       => $b->start_date,
        'end'         => $b->end_date,
        'type'        => $b->type,
        'status'      => $b->status,
    ])->values();

    return response()->json([
        'van'      => $van->name . ' (' . $van->plate_number . ')',
        'bookings' => $all,
    ]);
}

public function toggleDriverStatus($id)
{
    // 1. Find the driver in the database
    $driver = DB::table('drivers')->where('id', $id)->first();

    if (!$driver) {
        return back()->with('error', 'Driver not found.');
    }

    // 2. Determine the new status
    $newStatus = ($driver->status == 'available') ? 'not available' : 'available';

    // 3. Update the database
    DB::table('drivers')->where('id', $id)->update([
        'status' => $newStatus,
        'updated_at' => now()
    ]);

    return back()->with('success', 'Driver status updated to ' . ucfirst($newStatus));
}

public function manageBookings() {
    $bookings = DB::table('bookings')
        ->join('tour_packages', 'bookings.tour_id', '=', 'tour_packages.id')
        ->join('users', 'bookings.user_id', '=', 'users.id')
        ->select('bookings.*', 'tour_packages.van', 'tour_packages.driver_name', 'users.first_name', 'users.last_name')
        ->get();

    return view('admin.bookings', compact('bookings'));
}

public function approveJoinerTrip($id)
{
    DB::table('joiner_trips')->where('id', $id)->update(['status' => 'active', 'updated_at' => now()]);
    return back()->with('success', 'Joiner trip approved. Driver can now begin the trip.');
}

public function rejectJoinerTrip(Request $request, $id)
{
    $trip = DB::table('joiner_trips')->where('id', $id)->first();
    DB::table('joiner_trips')->where('id', $id)->update(['status' => 'rejected', 'updated_at' => now()]);

    if ($trip) {
        $bookings = DB::table('joiner_bookings')
            ->where('joiner_trip_id', $id)
            ->whereIn('status', ['pending', 'downpayment_paid', 'fully_paid'])
            ->get();

        foreach ($bookings as $jBooking) {
            $user = User::find($jBooking->user_id);
            if ($user) {
                $user->notify(new BookingRejected($jBooking->id, $trip->destination, 'Joiner Trip', null, $jBooking->amount_paid));
            }
        }
    }

    return back()->with('success', 'Joiner trip rejected.');
}

public function collectJoinerPassengerPayment($tripId, $bookingId)
{
    $booking = DB::table('joiner_bookings')->where('id', $bookingId)->where('joiner_trip_id', $tripId)->first();
    if (!$booking) return back()->with('error', 'Booking not found.');

    $balance = $booking->total_price - ($booking->amount_paid ?? 0);
    if ($balance <= 0) return back()->with('error', 'Already fully paid.');

    DB::table('joiner_bookings')->where('id', $bookingId)->update([
        'amount_paid'    => $booking->total_price,
        'payment_status' => 'fully_paid',
        'status'         => 'fully_paid',
        'updated_at'     => now(),
    ]);

    return back()->with('success', 'Payment of ₱' . number_format($balance, 2) . ' collected.');
}

public function updateVanMaintenance(Request $request, $id)
{
    DB::table('vans')->where('id', $id)->update([
        'last_oil_change'    => $request->last_oil_change ?: null,
        'last_maintenance'   => $request->last_maintenance ?: null,
        'last_problem_date'  => $request->last_problem_date ?: null,
        'last_problem_notes' => $request->last_problem_notes ?: null,
        'updated_at'         => now(),
    ]);
    return back()->with('success', 'Van maintenance updated.');
}

public function reports()
{
    // All-time revenue
    $privateRevenue = DB::table('bookings')->where('status', 'completed')->sum('total');
    $joinerRevenue  = DB::table('joiner_bookings')->where('status', 'completed')->sum('total_price');
    $totalRevenue   = $privateRevenue + $joinerRevenue;

    // Revenue by period
    $weekRevenue = DB::table('bookings')->where('status', 'completed')
        ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total')
        + DB::table('joiner_bookings')->where('status', 'completed')
        ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_price');

    $monthRevenue = DB::table('bookings')->where('status', 'completed')
        ->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->sum('total')
        + DB::table('joiner_bookings')->where('status', 'completed')
        ->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->sum('total_price');

    $yearRevenue = DB::table('bookings')->where('status', 'completed')
        ->whereYear('updated_at', now()->year)->sum('total')
        + DB::table('joiner_bookings')->where('status', 'completed')
        ->whereYear('updated_at', now()->year)->sum('total_price');

    // Booking counts
    $privateCount = DB::table('bookings')->count();
    $joinerCount  = DB::table('joiner_bookings')->count();

    // Get recent completed bookings for a small table
    $recentEarnings = DB::table('bookings')
        ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
        ->select(
            'bookings.id', 'bookings.destination', 'bookings.total as amount', 'bookings.updated_at as date',
            DB::raw("'Private' as type"),
            DB::raw("COALESCE(NULLIF(bookings.name, ''), CONCAT(users.first_name, ' ', users.last_name)) as customer_name"),
            'bookings.payment_method',
            'bookings.payment_id',
            'bookings.status'
        )
        ->where('bookings.status', 'completed')
        ->union(
            DB::table('joiner_bookings')
                ->leftJoin('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
                ->leftJoin('users', 'joiner_bookings.user_id', '=', 'users.id')
                ->select(
                    'joiner_bookings.id', 'joiner_trips.destination', 'joiner_bookings.total_price as amount',
                    'joiner_bookings.updated_at as date',
                    DB::raw("'Joiner' as type"),
                    DB::raw("COALESCE(NULLIF(joiner_bookings.passenger_name, ''), CONCAT(users.first_name, ' ', users.last_name)) as customer_name"),
                    'joiner_bookings.payment_method',
                    'joiner_bookings.payment_id',
                    'joiner_bookings.status'
                )
                ->where('joiner_bookings.status', 'completed')
        )
        ->orderBy('date', 'desc')
        ->get();

    // Vans with last trip date from bookings
    $vans = DB::table('vans')
        ->leftJoin(DB::raw('(SELECT van_id, MAX(end_date) as last_trip FROM bookings GROUP BY van_id) as bt'), 'vans.id', '=', 'bt.van_id')
        ->select('vans.*', 'bt.last_trip as last_trip_date')
        ->orderBy('vans.name')
        ->get();

    // ---- Analytics dashboard data ----

    // Revenue trend, last 6 months (completed private + joiner bookings)
    $monthlyRevenue = collect(range(5, 0))->map(function ($monthsAgo) {
        $start = now()->subMonths($monthsAgo)->startOfMonth();
        $end   = now()->subMonths($monthsAgo)->endOfMonth();

        $private = DB::table('bookings')->where('status', 'completed')
            ->whereBetween('updated_at', [$start, $end])->sum('total');
        $joiner  = DB::table('joiner_bookings')->where('status', 'completed')
            ->whereBetween('updated_at', [$start, $end])->sum('total_price');

        return [
            'label' => $start->format('M Y'),
            'total' => round((float) $private + (float) $joiner, 2),
        ];
    })->values();

    // Bookings per van (private rentals) — vans are a small, clean, bounded
    // category set, unlike free-text destinations, so this is the reliable
    // "what's in demand" chart.
    $bookingsPerVan = DB::table('bookings')
        ->join('vans', 'bookings.van_id', '=', 'vans.id')
        ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
        ->select('vans.name', DB::raw('COUNT(*) as bookings'))
        ->groupBy('vans.id', 'vans.name')
        ->orderByDesc('bookings')
        ->limit(6)
        ->get();

    $completedTripsCount = DB::table('bookings')->where('status', 'completed')->count()
        + DB::table('joiner_bookings')->where('status', 'completed')->count();

    $customerCount = DB::table('users')->where('role', 'customer')->count();

    $avgBookingValue = $completedTripsCount > 0 ? $totalRevenue / $completedTripsCount : 0;

    return view('admin.reports', compact(
        'totalRevenue', 'weekRevenue', 'monthRevenue', 'yearRevenue',
        'privateCount', 'joinerCount', 'recentEarnings', 'vans',
        'monthlyRevenue', 'bookingsPerVan', 'completedTripsCount', 'customerCount', 'avgBookingValue'
    ));
}

}

