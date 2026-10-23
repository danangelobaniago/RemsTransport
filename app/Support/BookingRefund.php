<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Refunds the admin sends back (manually) for rejected or cancelled bookings,
 * each with a photo of the transfer receipt as proof.
 * Policy: a rejected booking gets back everything paid; a fully-paid
 * cancellation gets 80% (20% cancellation fee); a cancelled downpayment
 * is non-refundable.
 */
class BookingRefund
{
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
    public static function eligibleAmount(?string $status, ?string $paymentStatus, $amountPaid): float
    {
        $paid = round((float) $amountPaid, 2);

        return match (strtolower((string) $status)) {
            'rejected'  => $paid,
            'cancelled' => $paymentStatus === 'fully_paid' ? round($paid * 0.8, 2) : 0.0,
            default     => 0.0,
        };
    }

    /** The refund already recorded for a booking, if any. */
    public static function find($bookingId, string $source = 'bookings'): ?object
    {
        return DB::table('booking_refunds')->where('booking_id', $bookingId)->where('source', $source)->first();
    }
}
