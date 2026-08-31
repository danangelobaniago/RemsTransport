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

        // Check tour packages — but only against dates customers have actually booked,
        // not the package's whole bookable range (that range just bounds what a customer
        // is ALLOWED to pick; it isn't a real reservation until someone picks a date).
        $conflictTours = DB::table('bookings')
            ->join('tour_packages', 'bookings.tour_id', '=', 'tour_packages.id')
            ->where('bookings.start_date', '<=', $date)
            ->where('bookings.end_date', '>=', $date)
            ->whereNotIn('bookings.status', ['rejected', 'cancelled', 'completed'])
            ->where(function ($q) use ($vanName, $driverName) {
                $q->where('tour_packages.van', $vanName)
                  ->orWhere('tour_packages.driver_name', $driverName);
            })
            ->exists();

        if ($conflictTours) return false;

        return true;
    }
}
