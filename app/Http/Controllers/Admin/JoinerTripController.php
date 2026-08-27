<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JoinerTrip;
use App\Models\Van;

class JoinerTripController extends Controller
{

    public function show($id)
    {
        // Fetch the trip from the database
        $trip = DB::table('joiner_trips')->where('id', $id)->first();

        // If not found, show 404
        if (!$trip) {
            abort(404);
        }

        // Return the industry-style view we created
        return view('joiner_details', compact('trip'));
    }

    public function index()
    {
        $vans = Van::all();
        $joinerTrips = JoinerTrip::latest()->get();
        return view('admin.joiner_trips', compact('vans', 'joinerTrips'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'destination'    => 'required|string',
        'meetup_point'   => 'required|string',
        'trip_date'      => 'required|date',
        'total_seats'    => 'required|integer',
        'price_per_seat' => 'required|numeric',
        'van'            => 'required|string', // Changed from van_id to van
    ]);

    // Set available_seats equal to total_seats initially
    $validated['available_seats'] = $request->total_seats;
    $validated['status'] = 'open';

    JoinerTrip::create($validated);

    return redirect()->back()->with('success', 'Trip created successfully!');
}

public function join($id)
{
    $trip = DB::table('joiner_trips')->where('id', $id)->first();

    // 1. Check if seats are available
    if ($trip->available_seats <= 0) {
        return redirect()->back()->with('error', 'This trip is already full!');
    }

    // 2. Create the booking
    DB::table('joiner_bookings')->insert([
        'user_id' => auth()->id(),
        'joiner_trip_id' => $id,
        'seats_booked' => 1,
        'status' => 'confirmed',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 3. Deduct 1 seat from the trip
    DB::table('joiner_trips')->where('id', $id)->decrement('available_seats', 1);

    return redirect()->back()->with('success', 'You have successfully joined the trip!');
}

// Add this at the bottom of your JoinerTripController class
public function passengerForm($id)
{
    // Fetch the trip details so the form knows which trip we are booking
    $trip = \DB::table('joiner_trips')->where('id', $id)->first();

    // If the trip doesn't exist, show 404
    if (!$trip) {
        abort(404);
    }

    // Return the standalone form view we created earlier
    return view('joiner_passenger_form', compact('trip'));
}
}
