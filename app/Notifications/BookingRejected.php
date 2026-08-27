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

    public function __construct(int $bookingId, string $destination, string $bookingType = 'Booking', ?string $reason = null)
    {
        $this->bookingId   = $bookingId;
        $this->destination = $destination;
        $this->bookingType = $bookingType;
        $this->reason      = $reason;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        $reasonLine = $this->reason ? " Reason: {$this->reason}." : '';

        return [
            'message'      => "Your {$this->bookingType} #{$this->bookingId} to {$this->destination} has been rejected.{$reasonLine} Your full payment will be refunded. Please contact the admin for more information.",
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
            ->line('✅ **Your full payment will be refunded.** Please allow a few business days for the refund to reflect on your account.')
            ->line('Please contact us directly so we can assist you further or help reschedule your trip:')
            ->line('📧 **Email:** remstransport1@gmail.com')
            ->line('📞 **Phone/SMS:** Available during business hours')
            ->action('View My Bookings', url('/my-bookings'))
            ->line("We apologize for the inconvenience and hope to serve you again. Thank you for choosing Rem's Transport!");
    }
}
