<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate the input
        $request->validate([
            'booking_id'     => 'required|exists:bookings,id',
            'service_rating' => 'required|integer|min:1|max:5',
            'driver_rating'  => 'required|integer|min:1|max:5',
            'comment'        => 'nullable|string|max:500',
        ]);

        // 2. Prevent duplicate feedback
        $exists = DB::table('feedbacks')->where('booking_id', $request->booking_id)->exists();

        if ($exists) {
            return back()->with('error', 'Feedback has already been submitted for this booking.');
        }

        // 3. Save to database
        DB::table('feedbacks')->insert([
            'booking_id'     => $request->booking_id,
            'user_id'        => auth()->id(),
            'driver_name'    => $request->driver_name,
            'service_rating' => $request->service_rating,
            'driver_rating'  => $request->driver_rating,
            'comment'        => $request->comment,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('success', 'Thank you! Your feedback has been recorded.');
    }
}
