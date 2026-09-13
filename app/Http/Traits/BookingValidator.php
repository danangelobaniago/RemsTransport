<?php

namespace App\Http\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

trait BookingValidator
{
    private function checkAvailability($vanName, $driverName, $date, $excludeBookingId = null)
    {
        // Driver's weekly rest day — a hard block, no assignment on that weekday.
        if ($this->driverRestsOn($driverName, $date)) {
            return false;
        }

        // Check bookings — date range (start_date to end_date), skip rejected/cancelled/completed
        $conflictBookings = DB::table('bookings')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->whereNotIn('status', ['rejected', 'cancelled', 'completed'])
            ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
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

    /**
     * True if the given date falls on the driver's weekly rest day.
     * Accepts a driver id or a driver name.
     */
    private function driverRestsOn($driver, $date): bool
    {
        if ($driver === null || $driver === '') {
            return false;
        }

        $dayOff = is_numeric($driver)
            ? DB::table('drivers')->where('id', $driver)->value('day_off')
            : DB::table('drivers')->where('name', $driver)->value('day_off');

        if ($dayOff === null) {
            return false;
        }

        return (int) Carbon::parse($date)->dayOfWeek === (int) $dayOff;
    }

    /**
     * True if any date in the inclusive range lands on the driver's rest day.
     * Pass a driver id (preferred) or name.
     */
    private function driverRestDayInRange($driver, $startDate, $endDate = null): bool
    {
        $dayOff = is_numeric($driver)
            ? DB::table('drivers')->where('id', $driver)->value('day_off')
            : DB::table('drivers')->where('name', $driver)->value('day_off');

        if ($dayOff === null) {
            return false;
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate ?: $startDate)->startOfDay();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ((int) $d->dayOfWeek === (int) $dayOff) {
                return true;
            }
        }

        return false;
    }

    /**
     * Server-side backstop for the passenger rules enforced client-side in
     * passengers.blade.php / tour_manifesto.blade.php / joiner_passenger_form.blade.php:
     * minors need an accompanying adult, minimum age is 1 month, and suffixes
     * must be real ones. Pass a flat list of passengers, each as an array
     * with 'suffix' and 'birthday' keys (other keys are ignored). Returns an
     * error message, or null if everything checks out.
     */
    private function validatePassengerAgesAndSuffixes($passengers): ?string
    {
        $suffixWhitelist = ['', 'Jr.', 'Sr.', 'II', 'III', 'IV', 'V'];
        $minBirthday     = now()->subMonth()->format('Y-m-d');

        $hasAdult = false;
        $hasMinor = false;

        foreach ((array) $passengers as $p) {
            if (!is_array($p)) {
                continue;
            }

            $suffix = trim($p['suffix'] ?? '');
            if ($suffix !== '' && !in_array($suffix, $suffixWhitelist, true)) {
                return 'Please choose a valid suffix (Jr., Sr., II, III, IV, or V) or leave it blank.';
            }

            if (!empty($p['birthday'])) {
                if ($p['birthday'] > $minBirthday) {
                    return 'Each passenger must be at least 1 month old.';
                }

                if (Carbon::parse($p['birthday'])->age >= 18) {
                    $hasAdult = true;
                } else {
                    $hasMinor = true;
                }
            }
        }

        if ($hasMinor && !$hasAdult) {
            return 'A passenger under 18 cannot travel alone — this booking needs at least one passenger who is 18 or older.';
        }

        return null;
    }
}
