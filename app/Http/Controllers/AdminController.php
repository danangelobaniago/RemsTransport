<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\BookingApproved;
use App\Notifications\BookingRejected;

class AdminController extends Controller
{
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
            DB::table('bookings')
                ->where('id', $request->booking_id)
                ->update(['status' => 'approved']);

            $user = User::find($booking->user_id);
            if ($user) {
                $user->notify(new BookingApproved($booking));
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
                $user->notify(new BookingRejected($booking->id, $destination, 'Van Rental', $request->reason));
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
        ->select(
            'bookings.*',
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
        ->get();

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

        return view('admin.pricing', compact('pricing'));
    }

    public function updatePricing(Request $request)
    {
        $request->validate([
            'base_fare' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'driver_fee' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        DB::table('pricing')->where('id', 1)->update([
            'base_fare' => $request->base_fare,
            'price_per_km' => $request->price_per_km,
            'driver_fee' => $request->driver_fee,
            'last_update_reason' => $request->reason,
            'updated_at' => now()
        ]);

        return back()->with('success', 'Pricing updated successfully!');
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
        'license_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048' // Optional on update
    ], [
        'name.regex' => 'The driver name cannot contain numbers.',
        'phone.regex' => 'The phone number must start with 09 and be exactly 11 digits.',
    ]);

    // 2. Prepare the basic data
    $updateData = [
        'name' => $request->name,
        'phone' => $request->phone,
        'license_number' => $request->license,
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

        'license_image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
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
        'status' => 'AVAILABLE',
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
        'plate_number' => 'required|string|max:100|unique:vans,plate_number',
        'seats' => 'required|integer|min:1',
        'price_min' => "required|numeric|min:1|max:{$baseFare}",
        'price_max' => "required|numeric|min:1|max:{$baseFare}|gte:price_min",
        'transmission' => 'required|string',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ], [
        'plate_number.unique' => 'That plate number is already registered. Please use a different plate.',
        'price_min.min' => 'Min price must be at least ₱1.',
        'price_min.max' => "Min price must not exceed the base fare (₱" . number_format($baseFare) . ").",
        'price_max.min' => 'Max price must be at least ₱1.',
        'price_max.max' => "Max price must not exceed the base fare (₱" . number_format($baseFare) . ").",
        'price_max.gte' => 'Max price must be greater than or equal to min price.',
    ]);

    $imagePath = null;
    if ($request->hasFile('image')) {
        // This stores the image in storage/app/public/vans
        $imagePath = $request->file('image')->store('vans', 'public');
    }

    DB::table('vans')->insert([
        'name' => $request->name,
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
                $user->notify(new BookingRejected($jBooking->id, $trip->destination, 'Joiner Trip'));
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

    return view('admin.reports', compact(
        'totalRevenue', 'weekRevenue', 'monthRevenue', 'yearRevenue',
        'privateCount', 'joinerCount', 'recentEarnings', 'vans'
    ));
}

}

