<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/** Tells a customer the admin changed a passenger on their booking's manifest. */
class ManifestUpdated extends Notification
{
    use Queueable;

    public function __construct(
        protected int $bookingId,
        protected string $destination,
        protected string $change, // e.g. "Juan Dela Cruz was replaced by Maria Santos."
    ) {
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message'    => "The passenger list for booking #{$this->bookingId} to {$this->destination} was updated: {$this->change}",
            'booking_id' => $this->bookingId,
            'type'       => 'manifest_updated',
        ];
    }

    public function toMail($notifiable)
    {
        $fullName = trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? ''));

        return (new MailMessage)
            ->subject("Passenger List Updated — Booking #{$this->bookingId}")
            ->greeting('Hello ' . ($fullName ?: 'Customer') . ',')
            ->line("As requested, we updated the passenger list for **booking #{$this->bookingId}** to **{$this->destination}**.")
            ->line('**Change:** ' . e($this->change))
            ->line('If this change was not requested by you, please contact us right away at remstransport1@gmail.com.')
            ->action('View My Bookings', url('/my-bookings'));
    }
}
