<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Driver positions for the admin live map. They arrive two ways:
 * - 'portal': the Driver Portal page (only while it's open on screen), and
 * - 'app':    the Traccar Client app, which keeps sending in the background.
 */
class DriverLocation
{
    /** A driver counts as live if they reported within this window. */
    public const LIVE_SECONDS = 120;

    /**
     * After that, "idle" (stopped, in a tunnel, weak signal — the app also slows
     * down while the phone isn't moving) until this long without any report,
     * and only then offline.
     */
    public const IDLE_SECONDS = 900;

    /** Trail points older than this are deleted. */
    private const KEEP_HOURS = 48;

    /**
     * Save one position fix. The driver's "current" position only moves forward
     * in time — the app may upload fixes it buffered while offline, and those
     * older fixes still go into the trail without overwriting a newer position.
     */
    public static function record(int $driverId, float $lat, float $lng, ?float $accuracy, ?float $speed,
                                  ?float $heading, string $source, ?Carbon $recordedAt = null): void
    {
        $recordedAt = ($recordedAt ?? now())->copy()->setTimezone('UTC');
        if ($recordedAt->isFuture()) {
            $recordedAt = now(); // phone clock ahead of ours
        }

        DB::table('driver_location_points')->insert([
            'driver_id'   => $driverId,
            'lat'         => $lat,
            'lng'         => $lng,
            'accuracy'    => $accuracy !== null ? (int) round($accuracy) : null,
            'recorded_at' => $recordedAt,
        ]);

        DB::table('drivers')
            ->where('id', $driverId)
            ->where(fn ($q) => $q->whereNull('location_updated_at')->orWhere('location_updated_at', '<=', $recordedAt))
            ->update([
                'current_lat'         => $lat,
                'current_lng'         => $lng,
                'location_accuracy'   => $accuracy !== null ? round($accuracy) : null,
                'location_speed'      => $speed !== null && $speed >= 0 ? round($speed, 2) : null,
                'location_heading'    => $heading !== null && $heading >= 0 ? round($heading, 1) : null,
                'location_source'     => $source,
                'location_updated_at' => $recordedAt,
            ]);

        DB::table('driver_location_points')
            ->where('driver_id', $driverId)
            ->where('recorded_at', '<', now()->subHours(self::KEEP_HOURS))
            ->delete();
    }

    /** The driver's Traccar device id, created the first time it's needed. */
    public static function trackingToken(object $driver): string
    {
        if ($driver->tracking_token) {
            return $driver->tracking_token;
        }

        do {
            $token = 'rems-' . Str::lower(Str::random(12));
        } while (DB::table('drivers')->where('tracking_token', $token)->exists());

        DB::table('drivers')->where('id', $driver->id)->update(['tracking_token' => $token]);

        return $token;
    }
}
