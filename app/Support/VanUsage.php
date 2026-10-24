<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * What a van is still committed to — upcoming or ongoing van rentals, tour
 * packages and joiner trips, and customers paying for it right now. A van
 * with anything here can't be deleted.
 */
class VanUsage
{
    /** @return string[] human-readable list, e.g. "Van Rental #53 (Oct 24)" */
    public static function activeTrips(object $van): array
    {
        $today = LocalDate::today();
        $fmt   = fn ($date) => $date ? Carbon::parse($date)->format('M d') : '';
        $uses  = [];

        // Van rentals still open (tours are covered by their package below)
        DB::table('bookings')
            ->where('van_id', $van->id)
            ->whereNull('tour_id')
            ->whereNotIn('status', ['cancelled', 'rejected', 'completed'])
            ->where(fn ($q) => $q->whereNull('trip_status')->orWhere('trip_status', '!=', 'completed'))
            ->orderBy('start_date')
            ->get(['id', 'start_date'])
            ->each(function ($b) use (&$uses, $fmt) {
                $uses[] = "Van Rental #{$b->id} ({$fmt($b->start_date)})";
            });

        // Tour packages assigned this van that haven't finished
        DB::table('tour_packages')
            ->where(fn ($q) => $q->where('van_id', $van->id)->orWhere('plate_number', $van->plate_number))
            ->where(fn ($q) => $q->whereNull('trip_status')->orWhere('trip_status', '!=', 'completed'))
            ->whereRaw('COALESCE(end_date, tour_date) >= ?', [$today])
            ->get(['name', 'tour_date'])
            ->each(function ($t) use (&$uses, $fmt) {
                $uses[] = "Tour: {$t->name} ({$fmt($t->tour_date)})";
            });

        // Joiner trips on this van that are still running
        DB::table('joiner_trips')
            ->where(fn ($q) => $q->where('plate_number', $van->plate_number)
                ->orWhere(fn ($q2) => $q2->whereNull('plate_number')->where('van', $van->name)))
            ->whereNotIn('status', ['rejected', 'cancelled', 'completed'])
            ->where(fn ($q) => $q->whereNull('trip_status')->orWhere('trip_status', '!=', 'completed'))
            ->whereRaw('COALESCE(end_date, trip_date) >= ?', [$today])
            ->get(['destination', 'trip_date'])
            ->each(function ($j) use (&$uses, $fmt) {
                $uses[] = "Joiner trip to {$j->destination} ({$fmt($j->trip_date)})";
            });

        // A customer is checking out with this van right now
        if (DB::table('booking_holds')->where('plate_number', $van->plate_number)->where('expires_at', '>', now())->exists()) {
            $uses[] = 'A customer is paying for a booking with this van right now';
        }

        return $uses;
    }
}
