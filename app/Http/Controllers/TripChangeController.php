<?php

namespace App\Http\Controllers;

use App\Http\Traits\BookingValidator;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewTripAssigned;
use App\Notifications\TripDetailsChanged;
use App\Notifications\TripUnassigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Terms & Conditions §6 — "Vehicle, Route, and Driver Changes".
 *
 * Lets the admin swap the van, driver and/or route of a van rental, a tour
 * package or a joiner trip, with a recorded reason. The replacement van must
 * be comparable (enough seats) and the van/driver must be free on the trip's
 * dates. Affected customers are told by bell notification, chat and email;
 * the old and new drivers get a bell notification.
 */
class TripChangeController extends Controller
{
    use BookingValidator;

    const REASONS = [
        'mechanical'         => 'Mechanical issue',
        'road'               => 'Road conditions',
        'weather'            => 'Weather disturbance',
        'driver_unavailable' => 'Driver unavailable',
        'other'              => 'Circumstances beyond our control',
    ];

    const TYPES = ['rental', 'tour', 'joiner'];

    public function update(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, self::TYPES, true), 404);

        $data = $request->validate([
            'van_id'      => 'nullable|integer|exists:vans,id',
            'driver_id'   => 'nullable|integer|exists:drivers,id',
            'pickup'      => 'nullable|string|max:255',
            'destination' => 'nullable|string|max:255',
            'reason'      => 'required|in:' . implode(',', array_keys(self::REASONS)),
            'details'     => 'required|string|max:500',
        ]);

        $trip = $this->loadTrip($type, $id);
        if (is_string($trip)) {
            return back()->with('error', $trip);
        }

        // ---- Work out what actually changes ----
        $newVan = null;
        if (!empty($data['van_id']) && (int) $data['van_id'] !== (int) ($trip['van']->id ?? 0)) {
            $newVan = DB::table('vans')->where('id', $data['van_id'])->first();
        }

        $newDriver = null;
        if (!empty($data['driver_id']) && (int) $data['driver_id'] !== (int) ($trip['driver']->id ?? 0)) {
            $newDriver = DB::table('drivers')->where('id', $data['driver_id'])->first();
        }

        $newPickup = trim($data['pickup'] ?? '');
        $newPickup = ($newPickup !== '' && $newPickup !== (string) $trip['pickup']) ? $newPickup : null;

        $newDestination = trim($data['destination'] ?? '');
        $newDestination = ($newDestination !== '' && $newDestination !== (string) $trip['destination']) ? $newDestination : null;

        if (!$newVan && !$newDriver && !$newPickup && !$newDestination) {
            return back()->with('error', 'Nothing to change — pick a different van, driver, pickup point or destination.');
        }

        // ---- "Comparable" replacement + availability checks ----
        if ($newVan) {
            if ($newVan->status !== 'available') {
                return back()->with('error', "{$newVan->name} ({$newVan->plate_number}) is marked unavailable.");
            }
            if ((int) $newVan->seats < $trip['seats_needed']) {
                return back()->with('error', "{$newVan->name} only has {$newVan->seats} seats, but this trip needs {$trip['seats_needed']}. Choose a comparable van.");
            }
        }

        if ($newDriver && $newDriver->status !== 'available') {
            return back()->with('error', "{$newDriver->name} is marked unavailable.");
        }

        if ($newVan || $newDriver) {
            foreach ($trip['dates'] as $date) {
                if ($newDriver && $this->driverRestsOn($newDriver->id, $date)) {
                    return back()->with('error', "{$newDriver->name} has a weekly day off on {$date}.");
                }
                if (!$this->checkAvailability($newVan->plate_number ?? null, $newDriver->id ?? null, $date, $trip['exclude_booking_id'])) {
                    return back()->with('error', "The new van or driver already has another trip on {$date}.");
                }
            }
        }

        // ---- Apply ----
        $oldVanLabel    = $this->vanLabel($trip['van'], $trip['van_fallback']);
        $oldDriverLabel = $trip['driver']->name ?? $trip['driver_fallback'];

        DB::transaction(function () use ($type, $id, $trip, $newVan, $newDriver, $newPickup, $newDestination, $data, $oldVanLabel, $oldDriverLabel) {
            $this->applyChanges($type, $id, $trip, $newVan, $newDriver, $newPickup, $newDestination);

            DB::table('trip_changes')->insert([
                'trip_type'          => $type,
                'trip_id'            => $id,
                'old_van'            => $newVan ? $oldVanLabel : null,
                'new_van'            => $newVan ? $this->vanLabel($newVan) : null,
                'old_driver'         => $newDriver ? $oldDriverLabel : null,
                'new_driver'         => $newDriver->name ?? null,
                'old_pickup'         => $newPickup ? $trip['pickup'] : null,
                'new_pickup'         => $newPickup,
                'old_destination'    => $newDestination ? $trip['destination'] : null,
                'new_destination'    => $newDestination,
                'reason'             => $data['reason'],
                'details'            => trim($data['details']),
                'changed_by'         => Auth::id(),
                'customers_notified' => count($trip['customer_ids']),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        });

        // ---- Notify (after commit, so a mail hiccup can't undo the change) ----
        $changes = [];
        if ($newVan)         $changes[] = "Van: {$oldVanLabel} → " . $this->vanLabel($newVan);
        if ($newDriver)      $changes[] = 'Driver: ' . ($oldDriverLabel ?: 'None') . " → {$newDriver->name}";
        if ($newPickup)      $changes[] = 'Pickup point: ' . ($trip['pickup'] ?: 'N/A') . " → {$newPickup}";
        if ($newDestination) $changes[] = 'Destination: ' . ($trip['destination'] ?: 'N/A') . " → {$newDestination}";

        $reasonLabel = self::REASONS[$data['reason']];
        $details     = trim($data['details']);

        foreach (User::whereIn('id', $trip['customer_ids'])->get() as $customer) {
            $this->safely(fn () => $customer->notify(
                new TripDetailsChanged($trip['label'], $changes, $reasonLabel, $details, $trip['booking_id'])
            ));

            Message::create([
                'user_id'    => $customer->id,
                'sender_id'  => Auth::id(),
                'from_admin' => true,
                'body'       => "📢 Trip update for your {$trip['label']}:\n\n• " . implode("\n• ", $changes)
                    . "\n\nReason: {$reasonLabel} — {$details}\n\n"
                    . "As stated in our Terms and Conditions (Section 6), your agreed service inclusions stay the same. "
                    . "Feel free to reply here if you have any questions.",
            ]);
        }

        if ($newDriver) {
            $oldDriverUser = $trip['driver'] && $trip['driver']->user_id ? User::find($trip['driver']->user_id) : null;
            if ($oldDriverUser) {
                $this->safely(fn () => $oldDriverUser->notify(new TripUnassigned($trip['label'], $trip['dates'][0] ?? null, $reasonLabel)));
            }

            $newDriverUser = $newDriver->user_id ? User::find($newDriver->user_id) : null;
            if ($newDriverUser) {
                $this->safely(fn () => $newDriverUser->notify(new NewTripAssigned($trip['kind'], $newDestination ?? $trip['destination'], $trip['dates'][0] ?? now())));
            }
        }

        $who = count($trip['customer_ids']) === 1 ? '1 customer was' : count($trip['customer_ids']) . ' customers were';

        return back()->with('success', "{$trip['label']} updated. {$who} notified by notification, chat and email.");
    }

    // Change history for the modal.
    public function history(string $type, int $id)
    {
        abort_unless(in_array($type, self::TYPES, true), 404);

        $rows = DB::table('trip_changes')
            ->leftJoin('users', 'trip_changes.changed_by', '=', 'users.id')
            ->where('trip_type', $type)
            ->where('trip_id', $id)
            ->orderByDesc('trip_changes.id')
            ->select('trip_changes.*', 'users.first_name', 'users.last_name')
            ->get()
            ->map(function ($c) {
                $lines = [];
                if ($c->new_van)         $lines[] = "Van: {$c->old_van} → {$c->new_van}";
                if ($c->new_driver)      $lines[] = 'Driver: ' . ($c->old_driver ?: 'None') . " → {$c->new_driver}";
                if ($c->new_pickup)      $lines[] = "Pickup: {$c->old_pickup} → {$c->new_pickup}";
                if ($c->new_destination) $lines[] = "Destination: {$c->old_destination} → {$c->new_destination}";

                return [
                    'when'    => \Carbon\Carbon::parse($c->created_at)->timezone('Asia/Manila')->format('M j, Y g:i A'),
                    'by'      => trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: 'Admin',
                    'changes' => $lines,
                    'reason'  => (self::REASONS[$c->reason] ?? $c->reason) . ' — ' . $c->details,
                ];
            });

        return response()->json(['history' => $rows]);
    }

    /**
     * Normalises the three trip kinds into one shape, or returns an error
     * string if the trip can't be changed.
     */
    private function loadTrip(string $type, int $id)
    {
        if ($type === 'rental') {
            $b = DB::table('bookings')->where('id', $id)->whereNull('tour_id')->first();
            if (!$b) return 'Booking not found.';
            if (in_array($b->status, ['rejected', 'cancelled', 'completed'], true) || $b->trip_status === 'completed') {
                return "Booking #{$id} is already {$b->status} and can no longer be changed.";
            }

            $van = $b->van_id ? DB::table('vans')->where('id', $b->van_id)->first() : null;

            return [
                'kind'               => 'van rental',
                'label'              => "Van Rental #{$b->id}",
                'van'                => $van,
                'van_fallback'       => $b->plate_number,
                'driver'             => $b->driver ? DB::table('drivers')->where('id', $b->driver)->first() : null,
                'driver_fallback'    => null,
                'pickup'             => $b->pickup,
                'destination'        => $b->destination,
                'dates'              => $this->dateRange($b->start_date, $b->end_date),
                'seats_needed'       => (int) $b->passengers,
                'customer_ids'       => array_filter([$b->user_id]),
                'booking_id'         => $b->id,
                'exclude_booking_id' => $b->id,
            ];
        }

        if ($type === 'tour') {
            $t = DB::table('tour_packages')->where('id', $id)->first();
            if (!$t) return 'Tour package not found.';
            if (($t->trip_status ?? null) === 'completed') {
                return 'This tour has already been completed.';
            }

            // Only dates customers actually booked are real reservations.
            $active = DB::table('bookings')
                ->where('tour_id', $id)
                ->whereNotIn('status', ['rejected', 'cancelled', 'completed'])
                ->get(['user_id', 'start_date', 'end_date', 'passengers']);

            $dates = [];
            $seatsPerDate = [];
            foreach ($active as $b) {
                foreach ($this->dateRange($b->start_date, $b->end_date) as $d) {
                    $dates[$d] = true;
                    $seatsPerDate[$d] = ($seatsPerDate[$d] ?? 0) + (int) $b->passengers;
                }
            }
            ksort($dates);

            return [
                'kind'               => 'tour booking',
                'label'              => "Tour Package \"{$t->name}\"",
                'van'                => $t->plate_number ? DB::table('vans')->where('plate_number', $t->plate_number)->first() : null,
                'van_fallback'       => trim(($t->van ?? '') . ($t->plate_number ? " ({$t->plate_number})" : '')),
                'driver'             => $t->driver_name ? DB::table('drivers')->where('name', $t->driver_name)->first() : null,
                'driver_fallback'    => $t->driver_name,
                'pickup'             => $t->pickup_point,
                'destination'        => $t->destination,
                'dates'              => array_keys($dates),
                'seats_needed'       => $seatsPerDate ? max($seatsPerDate) : 0,
                'customer_ids'       => $active->pluck('user_id')->filter()->unique()->values()->all(),
                'booking_id'         => null,
                'exclude_booking_id' => null,
            ];
        }

        $j = DB::table('joiner_trips')->where('id', $id)->first();
        if (!$j) return 'Joiner trip not found.';
        if (in_array($j->status, ['completed', 'rejected', 'cancelled'], true) || ($j->trip_status ?? null) === 'completed') {
            return 'This joiner trip is already ' . $j->status . ' and can no longer be changed.';
        }

        return [
            'kind'               => 'joiner trip',
            'label'              => 'Joiner Trip to ' . $j->destination,
            'van'                => $j->plate_number ? DB::table('vans')->where('plate_number', $j->plate_number)->first() : null,
            'van_fallback'       => trim(($j->van ?? '') . ($j->plate_number ? " ({$j->plate_number})" : '')),
            'driver'             => $j->driver_name ? DB::table('drivers')->where('name', $j->driver_name)->first() : null,
            'driver_fallback'    => $j->driver_name,
            'pickup'             => $j->meetup_point,
            'destination'        => $j->destination,
            'dates'              => $this->dateRange($j->trip_date, $j->end_date ?: $j->trip_date),
            'seats_needed'       => max(0, (int) $j->total_seats - (int) $j->available_seats),
            'customer_ids'       => DB::table('joiner_bookings')
                ->where('joiner_trip_id', $id)
                ->whereNotIn('status', ['cancelled', 'rejected'])
                ->pluck('user_id')->filter()->unique()->values()->all(),
            'booking_id'         => null,
            'exclude_booking_id' => null,
        ];
    }

    private function applyChanges(string $type, int $id, array $trip, $newVan, $newDriver, ?string $newPickup, ?string $newDestination): void
    {
        $now = now();

        if ($type === 'rental') {
            $update = ['updated_at' => $now];
            if ($newVan)         $update += ['van_id' => $newVan->id, 'plate_number' => $newVan->plate_number];
            if ($newDriver)      $update['driver'] = $newDriver->id;
            if ($newPickup)      $update['pickup'] = $newPickup;
            if ($newDestination) $update['destination'] = $newDestination;
            DB::table('bookings')->where('id', $id)->update($update);
            return;
        }

        if ($type === 'tour') {
            $update = ['updated_at' => $now];
            if ($newVan)         $update += ['van_id' => $newVan->id, 'van' => $newVan->name, 'plate_number' => $newVan->plate_number];
            if ($newDriver)      $update += ['driver_id' => $newDriver->id, 'driver_name' => $newDriver->name, 'driver_license_number' => $newDriver->license_number];
            if ($newPickup)      $update['pickup_point'] = $newPickup;
            if ($newDestination) $update['destination'] = $newDestination;
            DB::table('tour_packages')->where('id', $id)->update($update);

            // Keep the package's open bookings in step (availability checks
            // and the admin bookings list read these columns).
            $bookingUpdate = ['updated_at' => $now];
            if ($newVan)    $bookingUpdate += ['van_id' => $newVan->id, 'plate_number' => $newVan->plate_number];
            if ($newDriver) $bookingUpdate['driver'] = $newDriver->id;
            if ($newPickup) $bookingUpdate['pickup'] = $newPickup;
            DB::table('bookings')
                ->where('tour_id', $id)
                ->whereNotIn('status', ['rejected', 'cancelled', 'completed'])
                ->update($bookingUpdate);
            return;
        }

        $update = ['updated_at' => $now];
        if ($newVan) {
            // Seat count follows the real van, keeping already-booked seats.
            $update += [
                'van'             => $newVan->name,
                'plate_number'    => $newVan->plate_number,
                'total_seats'     => $newVan->seats,
                'available_seats' => max(0, (int) $newVan->seats - $trip['seats_needed']),
            ];
        }
        if ($newDriver) {
            $update += [
                'driver_name'           => $newDriver->name,
                'driver_license_number' => $newDriver->license_number,
                'driver_license_image'  => $newDriver->license_image,
            ];
        }
        if ($newPickup)      $update['meetup_point'] = $newPickup;
        if ($newDestination) $update['destination'] = $newDestination;
        DB::table('joiner_trips')->where('id', $id)->update($update);
    }

    private function dateRange($start, $end): array
    {
        $dates = [];
        for ($d = strtotime($start); $d <= strtotime($end ?: $start); $d = strtotime('+1 day', $d)) {
            $dates[] = date('Y-m-d', $d);
        }
        return $dates;
    }

    private function vanLabel($van, ?string $fallback = null): string
    {
        if ($van) {
            return "{$van->name} ({$van->plate_number})";
        }
        return $fallback ?: 'None';
    }

    private function safely(callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
