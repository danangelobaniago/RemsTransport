<?php

namespace App\Http\Controllers;

use App\Support\DriverLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Receives positions from the Traccar Client app on a driver's phone, which
 * keeps reporting with the screen locked or another app open (the Driver
 * Portal page can't). The app identifies itself with the driver's secret
 * tracking token as its "device identifier".
 */
class DriverTrackingController extends Controller
{
    private const KNOTS_TO_MPS = 0.514444;

    public function store(Request $request)
    {
        $json = $request->isJson() ? $request->json()->all() : [];

        // New Traccar Client: JSON {device_id, location: {...} or [{...}, ...]}
        // Classic Traccar Client / OsmAnd protocol: ?id=&lat=&lon=&timestamp=&speed=(knots)&bearing=&accuracy=
        $token = $json['device_id'] ?? $request->input('id') ?? $request->input('deviceid');
        $driver = $token ? DB::table('drivers')->where('tracking_token', (string) $token)->first() : null;
        if (!$driver) {
            return response('Unknown device', 404);
        }

        $fixes = isset($json['location'])
            ? (array_is_list($json['location']) ? $json['location'] : [$json['location']])
            : [null];

        foreach ($fixes as $location) {
            $fix = $location !== null ? $this->fromJson($location) : $this->fromOsmAnd($request);
            if ($fix === null) {
                continue;
            }

            DriverLocation::record($driver->id, $fix['lat'], $fix['lng'], $fix['accuracy'], $fix['speed'],
                $fix['heading'], 'app', $fix['time']);
        }

        return response('OK');
    }

    private function fromJson(array $location): ?array
    {
        $coords = $location['coords'] ?? [];

        return $this->fix(
            $coords['latitude'] ?? null,
            $coords['longitude'] ?? null,
            $coords['accuracy'] ?? null,
            $coords['speed'] ?? null,   // already m/s
            $coords['heading'] ?? null,
            $location['timestamp'] ?? null
        );
    }

    private function fromOsmAnd(Request $request): ?array
    {
        $speed = $request->input('speed');

        return $this->fix(
            $request->input('lat'),
            $request->input('lon'),
            $request->input('accuracy'),
            is_numeric($speed) ? $speed * self::KNOTS_TO_MPS : null,
            $request->input('bearing') ?? $request->input('heading'),
            $request->input('timestamp')
        );
    }

    private function fix($lat, $lng, $accuracy, $speed, $heading, $timestamp): ?array
    {
        if (!is_numeric($lat) || !is_numeric($lng) || abs($lat) > 90 || abs($lng) > 180 || ($lat == 0 && $lng == 0)) {
            return null;
        }

        return [
            'lat'      => (float) $lat,
            'lng'      => (float) $lng,
            'accuracy' => is_numeric($accuracy) ? (float) $accuracy : null,
            'speed'    => is_numeric($speed) ? (float) $speed : null,
            'heading'  => is_numeric($heading) ? (float) $heading : null,
            'time'     => $this->parseTime($timestamp),
        ];
    }

    /** Unix seconds, unix milliseconds or an ISO date — whatever the app version sends. */
    private function parseTime($timestamp): ?Carbon
    {
        try {
            if (is_numeric($timestamp)) {
                $seconds = $timestamp > 100000000000 ? $timestamp / 1000 : $timestamp;
                return Carbon::createFromTimestamp((int) $seconds, 'UTC');
            }
            return $timestamp ? Carbon::parse($timestamp)->setTimezone('UTC') : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
