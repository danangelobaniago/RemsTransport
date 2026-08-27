<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TourPackage;

class HomeController extends Controller
{
    // ================= PUBLIC HOMEPAGE =================
public function index()
{
    // 1. Fetch Vans available for general rental
    $vans = DB::table('vans')->where('status', 'available')->get();

    // 2. Fetch Joiner Trips with Dynamic Availability Calculation
    $joinerTrips = DB::table('joiner_trips')
    ->where('status', 'active')
    ->get()
    ->map(function ($trip) {
        // Count occupied seats by passenger rows (accurate for multi-seat bookings)
        $bookingIds = DB::table('joiner_bookings')
            ->where('joiner_trip_id', $trip->id)
            ->whereIn('status', ['paid', 'pending', 'downpayment_paid', 'fully_paid'])
            ->pluck('id');

        $occupied = DB::table('joiner_passengers')
            ->whereIn('joiner_booking_id', $bookingIds)
            ->count();

        // Fallback: count bookings if no passenger rows exist yet
        if ($occupied === 0) {
            $occupied = $bookingIds->count();
        }

        $trip->available_seats = $trip->total_seats - $occupied;

        // Check if CURRENT user has a reservation
        $trip->alreadyReserved = false;
        if (auth()->check()) {
            $trip->alreadyReserved = DB::table('joiner_bookings')
                ->where('user_id', auth()->id())
                ->where('joiner_trip_id', $trip->id)
                ->whereIn('status', ['paid', 'pending', 'downpayment_paid', 'fully_paid'])
                ->exists();
        }
        return $trip;
    });

    // 3. Fetch Feedbacks
    $feedbacks = DB::table('feedbacks')
        ->join('users', 'feedbacks.user_id', '=', 'users.id')
        ->select('feedbacks.*', 'users.first_name', 'users.last_name')
        ->whereNotNull('comment')
        ->where('comment', '!=', '')
        ->orderBy('feedbacks.created_at', 'desc')
        ->get();

    // 4. Fetch Tour Packages and compute per-tour availability
    $tours = \App\Models\TourPackage::all()->map(function ($tour) {
        preg_match('/(\d+)/', $tour->duration ?? '1', $dm);
        $tripDays = max(1, (int)($dm[1] ?? 1));

        $tourStart = $tour->tour_date instanceof \Carbon\Carbon
            ? $tour->tour_date->format('Y-m-d')
            : (string) $tour->tour_date;
        $tourEnd   = $tour->end_date
            ? ($tour->end_date instanceof \Carbon\Carbon ? $tour->end_date->format('Y-m-d') : (string) $tour->end_date)
            : $tourStart;

        // Latest valid start: full trip duration must fit inside the available window
        $maxStart = date('Y-m-d', strtotime($tourEnd . ' -' . ($tripDays - 1) . ' days'));

        if ($maxStart < $tourStart) {
            // Trip length exceeds the whole available window — impossible to book
            $tour->is_fully_booked = true;
            return $tour;
        }

        // Active bookings for this tour (ordered so we can jump efficiently)
        $bookedRanges = DB::table('bookings')
            ->where('tour_id', $tour->id)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->select('start_date', 'end_date')
            ->orderBy('start_date')
            ->get();

        if ($bookedRanges->isEmpty()) {
            $tour->is_fully_booked = false;
            return $tour;
        }

        // Walk through possible start dates; jump past each blocking range
        $current = $tourStart;
        $fullyBooked = true;

        while ($current <= $maxStart) {
            $tripEnd = date('Y-m-d', strtotime($current . ' +' . ($tripDays - 1) . ' days'));

            $blocker = $bookedRanges->first(
                fn($r) => $current <= $r->end_date && $tripEnd >= $r->start_date
            );

            if (!$blocker) {
                $fullyBooked = false; // Found a free slot
                break;
            }

            // Jump past this blocker
            $current = date('Y-m-d', strtotime($blocker->end_date . ' +1 day'));
        }

        $tour->is_fully_booked = $fullyBooked;
        return $tour;
    });

    $bookedTourIds = []; // kept for backwards-compat with any other view references

    // 5. Return everything to the 'home' view
    return view('home', compact('vans', 'joinerTrips', 'feedbacks', 'tours', 'bookedTourIds'));
}

    // ================= ADMIN TOUR MANAGEMENT =================

    public function manageTours()
    {
        // Join with BOTH vans and drivers so we can see their names in the admin table
        $tours = DB::table('tour_packages')
            ->leftJoin('vans', 'tour_packages.van_id', '=', 'vans.id')
            ->leftJoin('drivers', 'tour_packages.driver_id', '=', 'drivers.id')
            ->select('tour_packages.*', 'vans.name as assigned_van', 'drivers.name as assigned_driver')
            ->latest('tour_packages.created_at')
            ->get();

        // Get available vans and drivers for the dropdowns
        $vans = DB::table('vans')->where('status', 'available')->get();
        $drivers = DB::table('drivers')->where('status', 'available')->get();

        return view('admin.tours', compact('tours', 'vans', 'drivers'));
    }

    public function addTour(Request $request)
{
    // 1. Validate - These keys MUST match the 'name' attribute in your HTML
    $request->validate([
        'name'           => 'required|string|max:255',
        'destination'    => 'required|string|max:255',
        'pickup_point'   => 'required|string',
        'van_id'         => 'required|integer',
        'driver_id'      => 'required|integer',
        'tour_date'      => 'required|date',
        'end_date'       => 'required|date',
        'price'          => 'required|numeric',
        'max_passengers' => 'required|integer',
        'duration'       => 'required|string',
        'image'          => 'required|image|mimes:jpg,jpeg,png|max:2048', // name is "image" in your Blade
    ]);

    // 2. Handle Image Upload
    $path = null;
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('tours', 'public');
    }

    // 3. Insert into Database
    DB::table('tour_packages')->insert([
        'name'           => $request->name,
        'destination'    => $request->destination,
        'pickup_point'   => $request->pickup_point,
        'van_id'         => $request->van_id,
        'driver_id'      => $request->driver_id,
        'tour_date'      => $request->tour_date,
        'end_date'       => $request->end_date,
        'price'          => $request->price,
        'max_passengers' => $request->max_passengers,
        'duration'       => $request->duration,
        'description'    => $request->description,
        'is_best_seller' => $request->has('is_best_seller') ? 1 : 0,
        'image'          => $path,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    return back()->with('success', 'Tour package created successfully!');
}

    public function deleteBooking($id)
{
    // Delete passengers first (Foreign Key constraint)
    DB::table('passengers')->where('booking_id', $id)->delete();

    // Delete the booking
    DB::table('bookings')->where('id', $id)->delete();

    return back()->with('success', 'Booking deleted.');
}

    public function showMyBookings(Request $request)
{
    $userId = Auth::id();

    // 1. Fetch Tour & Private Bookings from the 'bookings' table
    $regularBookings = DB::table('bookings')
        ->select(
            'id',
            DB::raw('COALESCE(package_name, destination) as destination'),
            'status',
            'pickup',
            'start_date',
            'passengers',
            'booking_type', // <--- This is the key column
            'total',
            'downpayment',
            'created_at'
        )
        ->where('user_id', $userId);

    // 2. Fetch Joiner Bookings and UNION them
    $allBookings = DB::table('joiner_bookings')
        ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
        ->select(
            'joiner_bookings.id',
            'joiner_trips.destination',
            'joiner_bookings.status',
            'joiner_trips.meetup_point as pickup',
            'joiner_trips.trip_date as start_date',
            'joiner_bookings.seats_booked as passengers',
            DB::raw("'joiner' as booking_type"), // Force type to 'joiner'
            'joiner_bookings.total_price as total',
            'joiner_bookings.downpayment',
            'joiner_bookings.created_at'
        )
        ->where('joiner_bookings.user_id', $userId)
        ->union($regularBookings)
        ->orderBy('created_at', 'desc')
        ->get();

    return view('my-bookings', ['bookings' => $allBookings]);
}

// 1. View Manifest (List of passengers for a specific tour)
public function viewTourManifest($id)
{
    $tour = DB::table('tour_packages')->where('id', $id)->first();
    if (!$tour) { abort(404); }

    // Fetch the booking and the passengers linked to this tour_id
    $passengers = DB::table('passengers')
        ->join('bookings', 'passengers.booking_id', '=', 'bookings.id')
        ->where('bookings.tour_id', $id)
        ->select('passengers.*', 'bookings.status as payment_status')
        ->get();

    return view('admin.tour_manifest', compact('tour', 'passengers'));
}

// 2. Complete Tour (Marks booking as fully paid and trip as done)
public function completeTour($id)
{
    DB::beginTransaction();
    try {
        // 1. Update the Tour Package status (Optional: if you want to hide it from home)
        // DB::table('tour_packages')->where('id', $id)->update(['status' => 'completed']);

        // 2. Find all bookings linked to this tour and mark them as completed
        DB::table('bookings')
            ->where('tour_id', $id)
            ->whereIn('status', ['paid', 'downpayment_paid', 'fully_paid'])
            ->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);

        DB::commit();
        return back()->with('success', '✅ Tour marked as completed! Users can now leave feedback.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', '❌ Failed to complete tour: ' . $e->getMessage());
    }
}

// 3. Update Tour (For the Edit feature)
public function updateTour(Request $request, $id)
{
    // Block edits once a customer's booking for this package has been approved.
    $hasApprovedBooking = DB::table('bookings')
        ->where('tour_id', $id)
        ->where('status', 'approved')
        ->exists();

    if ($hasApprovedBooking) {
        return redirect('/admin/tours')->with('error', 'This package cannot be edited — it already has an approved booking.');
    }

    // 1. Validate the incoming data
    $request->validate([
        'name' => 'required|string',
        'destination' => 'required|string',
        'price' => 'required|numeric',
        // Add other validation rules as needed
    ]);

    // 2. Prepare the data for update
    $data = [
        'name'           => $request->name,
        'destination'    => $request->destination,
        'duration'       => $request->duration,
        'pickup_point'   => $request->pickup_point,
        'tour_date'      => $request->tour_date,
        'end_date'       => $request->end_date,
        'pickup_time'    => $request->pickup_time,
        'price'          => $request->price,
        'max_passengers' => $request->max_passengers,
        'description'    => $request->description,
        'van'            => $request->van,
        'plate_number'   => $request->plate_number,
        'driver_name'    => $request->driver_name,
        'driver_license_number' => $request->driver_license_number,
        'is_best_seller' => $request->has('is_best_seller') ? 1 : 0,
        'updated_at'     => now(),
    ];

    // 3. Handle Image Upload (Optional - only if a new file is chosen)
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('tours', 'public');
        $data['image'] = $path;
    }

    // 4. Perform the Update
    DB::table('tour_packages')->where('id', $id)->update($data);

    return redirect('/admin/tours')->with('success', '✅ Tour Package updated successfully!');
}



public function getManifest($id) {
    $passengers = DB::table('passengers')
        ->join('bookings', 'passengers.booking_id', '=', 'bookings.id')
        ->where('bookings.tour_id', $id)
        ->select('passengers.*', 'bookings.status')
        ->get();

    return response()->json($passengers);
}



}


