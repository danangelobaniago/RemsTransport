<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Refunds the admin sends back (manually) for rejected or cancelled bookings,
 * each with a photo of the transfer receipt as proof.
 * Policy: a rejected booking gets back everything paid; so does a booking the
 * customer cancels within 3 days (72 hours) of making it, as long as the trip
 * is still at least 3 days away. Otherwise a fully-paid cancellation gets 80%
 * (20% cancellation fee) and a cancelled downpayment is non-refundable.
 */
class BookingRefund
{
    public const FULL_REFUND_HOURS = 72;     // after booking
    public const FULL_REFUND_DAYS_BEFORE_TRIP = 3;

    /**
     * Last moment (UTC) a cancellation still gets a full refund: 72 hours after
     * booking, or the start of the day 3 days before the trip (Philippine time),
     * whichever comes first. Null when either date is unknown.
     */
    public static function fullRefundDeadline($createdAt, $tripDate): ?Carbon
    {
        if (!$createdAt || !$tripDate) {
            return null;
        }

        $afterBooking = Carbon::parse($createdAt, 'UTC')->addHours(self::FULL_REFUND_HOURS);
        $beforeTrip   = Carbon::parse($tripDate, LocalDate::TIMEZONE)->startOfDay()
            ->subDays(self::FULL_REFUND_DAYS_BEFORE_TRIP)->setTimezone('UTC');

        return $afterBooking->min($beforeTrip);
    }

    /** Was this cancellation early enough for a full refund? Older cancellations have no cancelled_at and don't qualify. */
    public static function cancelledWithinGrace($createdAt, $cancelledAt, $tripDate): bool
    {
        $deadline = self::fullRefundDeadline($createdAt, $tripDate);

        return $deadline && $cancelledAt && Carbon::parse($cancelledAt, 'UTC')->lte($deadline);
    }

    /** Can the customer still cancel for a full refund right now? */
    public static function graceActive($createdAt, $tripDate): bool
    {
        $deadline = self::fullRefundDeadline($createdAt, $tripDate);

        return $deadline && Carbon::now('UTC')->lte($deadline);
    }

    /** Link to the photo of the refund receipt. */
    public static function proofUrl(object $refund): string
    {
        return Storage::disk('public')->url($refund->proof_path);
    }

    /** "Oct 23, 2026" in Philippine time. */
    public static function refundedOn(object $refund): string
    {
        return Carbon::parse($refund->refunded_at, 'UTC')->setTimezone(LocalDate::TIMEZONE)->format('M d, Y');
    }

    /** How much the customer is owed back, or 0 if the booking isn't refundable. */
    public static function eligibleAmount(?string $status, ?string $paymentStatus, $amountPaid, $createdAt = null, $cancelledAt = null, $tripDate = null): float
    {
        $paid = round((float) $amountPaid, 2);

        return match (strtolower((string) $status)) {
            'rejected'  => $paid,
            'cancelled' => match (true) {
                self::cancelledWithinGrace($createdAt, $cancelledAt, $tripDate) => $paid,
                $paymentStatus === 'fully_paid'                      => round($paid * 0.8, 2),
                default                                              => 0.0,
            },
            default     => 0.0,
        };
    }

    /** The refund already recorded for a booking, if any. */
    public static function find($bookingId, string $source = 'bookings'): ?object
    {
        return DB::table('booking_refunds')->where('booking_id', $bookingId)->where('source', $source)->first();
    }
}
