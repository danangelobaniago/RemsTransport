<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a customer that the admin changed the van, driver and/or route of
 * their trip (Terms & Conditions §6).
 */
class TripDetailsChanged extends Notification
{
    use Queueable;

    /**
     * @param string $tripLabel e.g. "Van Rental #12" or "Tour Package \"Baguio Tour\""
     * @param array  $changes   lines like "Van: Toyota Hiace (ABC 123) → Nissan Urvan (XYZ 789)"
     */
    public function __construct(
        protected string $tripLabel,
        protected array $changes,
        protected string $reason,
        protected string $details,
        protected ?int $bookingId = null
    ) {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message'    => "Your {$this->tripLabel} has been updated — " . implode('; ', $this->changes) . ". Reason: {$this->reason}.",
            'booking_id' => $this->bookingId,
            'type'       => 'trip_changed',
        ];
    }

    public function toMail($notifiable)
    {
        $fullName = trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? ''));

        $mail = (new MailMessage)
            ->subject("Trip Update: Change in Vehicle, Driver, or Route — Rem's Transport")
            ->greeting('Hello ' . ($fullName ?: 'Customer') . ',')
            ->line("We'd like to inform you that there has been a change to your **{$this->tripLabel}**:");

        foreach ($this->changes as $change) {
            $mail->line('• ' . $change);
        }

        return $mail
            ->line("**Reason:** {$this->reason} — {$this->details}")
            ->line('As stated in our Terms and Conditions (Section 6), the replacement is comparable and all of your agreed service inclusions remain the same. Your booking dates and payment are not affected.')
            ->action('View My Bookings', url('/my-bookings'))
            ->line("If you have questions, just message us through the chat button on our website. Thank you for your understanding and for choosing Rem's Transport!");
    }
}
