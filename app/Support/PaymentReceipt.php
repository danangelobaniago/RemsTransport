<?php

namespace App\Support;

use App\Mail\PaymentReceiptMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails the customer a receipt for one payment — the first payment at
 * checkout, an online balance/installment payment, or cash the driver
 * collected. A failed email is logged and never breaks the payment flow.
 */
class PaymentReceipt
{
    /** What the first payment at checkout was for, from the option the customer picked. */
    public static function initialLabel(?string $paymentType, float $remainingBalance): string
    {
        if ($paymentType === 'full' || $remainingBalance <= 0) {
            return 'Full Payment';
        }

        return $paymentType === 'installment' ? 'Initial Installment' : 'Downpayment';
    }

    /** Human label for a PayMongo method code ("gcash" → "GCash"); labels pass through. */
    public static function methodLabel(?string $method): string
    {
        return match (strtolower((string) $method)) {
            'gcash'    => 'GCash',
            'card'     => 'Credit/Debit Card',
            'paymaya'  => 'Maya',
            'grab_pay' => 'GrabPay',
            'cash'     => 'Cash (collected by driver)',
            ''         => 'PayMongo',
            default    => $method,
        };
    }

    public static function send(
        $userId,
        string $bookingType,
        $bookingId,
        string $description,
        ?string $referenceId,
        float $amountPaid,
        string $paymentLabel,
        ?string $paymentMethod,
        float $total,
        float $remainingBalance
    ): void {
        // Same "#51" the customer sees in My Bookings and in notifications.
        $bookingRef = '#' . $bookingId;

        try {
            $user = DB::table('users')->find($userId);
            if (!$user || !$user->email) {
                return;
            }

            Mail::to($user->email)->send(new PaymentReceiptMail(
                trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: 'Customer',
                $bookingType,
                $bookingRef,
                $description,
                $referenceId,
                round($amountPaid, 2),
                $paymentLabel,
                self::methodLabel($paymentMethod),
                // The app clock is UTC; customers read Philippine time.
                Carbon::now(LocalDate::TIMEZONE)->format('F d, Y h:i A'),
                round($total, 2),
                round(max(0, $remainingBalance), 2)
            ));
        } catch (\Throwable $e) {
            Log::error("Payment receipt email failed ({$bookingType} {$bookingRef}): " . $e->getMessage());
        }
    }
}
