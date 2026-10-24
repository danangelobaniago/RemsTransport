<?php

namespace App\Support;

/**
 * Passenger manifests can be edited by the admin (to replace someone who can
 * no longer join) until the trip starts, and only while the booking is active.
 */
class Manifest
{
    public static function editable(?string $bookingStatus, ?string $tripStatus): bool
    {
        return !in_array(strtolower((string) $bookingStatus), ['cancelled', 'rejected', 'completed'], true)
            && !in_array($tripStatus, ['arrived', 'in_progress', 'completed'], true);
    }
}
