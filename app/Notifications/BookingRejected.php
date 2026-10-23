<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingRejected extends Notification
{
    use Queueable;

    protected int $bookingId;
    protected string $destination;
    protected string $bookingType;
    protected ?string $reason;
    protected ?float $amountPaid;

    public function __construct(int $bookingId, string $destination, string $bookingType = 'Booking', ?string $reason = null, $amountPaid = null)
    {
        $this->bookingId   = $bookingId;
        $this->destination = $destination;
        $this->bookingType = $bookingType;
        $this->reason      = $reason;
        $this->amountPaid  = $amountPaid !== null ? (float) $amountPaid : null;
    }

    /**
     * "You are eligible for a refund of the ₱1,277.30 you paid." — the customer
     * may have paid only a downpayment, and nothing is sent back until the
     * admin has their payment details.
     */
    protected function refundLine(): string
    {
        return $this->amountPaid > 0
            ? 'You are eligible for a refund of the ₱' . number_format($this->amountPaid, 2) . ' you paid.'
            : 'You are eligible for a refund of any payment you made.';
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        $reasonLine = $this->reason ? " Reason: {$this->reason}." : '';

        return [
            'message'      => "Your {$this->bookingType} #{$this->bookingId} to {$this->destination} has been rejected.{$reasonLine} " . $this->refundLine() . " Our admin will contact you to get your payment details for the refund.",
            'booking_id'   => $this->bookingId,
            'reason'       => $this->reason,
            'type'         => 'booking_rejected',
        ];
    }

    public function toMail($notifiable)
    {
        $fullName = trim(
            ($notifiable->first_name ?? '') . ' ' .
            ($notifiable->middle_name ?? '') . ' ' .
            ($notifiable->last_name  ?? '')
        );

        $mail = (new MailMessage)
            ->subject("Booking Rejected ❌ — Rem's Transport")
            ->greeting('Hello ' . ($fullName ?: 'Customer') . ',')
            ->line("We regret to inform you that your **{$this->bookingType} #{$this->bookingId}** to **{$this->destination}** has been **rejected** by the admin.");

        if ($this->reason) {
            $mail->line("**Reason:** {$this->reason}");
        } else {
            $mail->line('This may be due to van or driver unavailability, scheduling conflicts, or other reasons.');
        }

        return $mail
            ->line('✅ **' . $this->refundLine() . '** Our admin will contact you to get your payment details for the refund (e.g. your GCash number or bank account).')
            ->line('Please contact us directly so we can assist you further or help reschedule your trip:')
            ->line('📧 **Email:** remstransport1@gmail.com')
            ->line('📞 **Phone/SMS:** Available during business hours')
            ->action('View My Bookings', url('/my-bookings'))
            ->line("We apologize for the inconvenience and hope to serve you again. Thank you for choosing Rem's Transport!");
    }
}
