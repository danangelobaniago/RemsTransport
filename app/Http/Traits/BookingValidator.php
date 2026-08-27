<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;

trait BookingValidator
{
    private function checkAvailability($vanName, $driverName, $date)
    {
        // Check bookings — date range (start_date to end_date), skip rejected/cancelled/completed
        $conflictBookings = DB::table('bookings')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->whereNotIn('status', ['rejected', 'cancelled', 'completed'])
            ->where(function ($q) use ($vanName, $driverName) {
                $q->where('van', $vanName)
                  ->orWhere('driver', $driverName);
            })
            ->exists();

        if ($conflictBookings) return false;

        // Check joiner_trips — single date
        $conflictJoiner = DB::table('joiner_trips')
            ->where('trip_date', $date)
            ->where(function ($q) use ($vanName, $driverName) {
                $q->where('van', $vanName)
                  ->orWhere('driver_name', $driverName);
            })
            ->exists();

        if ($conflictJoiner) return false;

        // Check tour_packages — date range (tour_date to end_date)
        $conflictTours = DB::table('tour_packages')
            ->where('tour_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where(function ($q) use ($vanName, $driverName) {
                $q->where('van', $vanName)
                  ->orWhere('driver_name', $driverName);
            })
            ->exists();

        if ($conflictTours) return false;

        return true;
    }
}
