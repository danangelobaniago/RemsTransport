<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingReminder extends Notification
{
    use Queueable;

    protected $booking;
    protected int $daysAway;

    public function __construct($booking, int $daysAway = 1)
    {
        $this->booking   = $booking;
        $this->daysAway  = $daysAway;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        $when = $this->daysAway === 1 ? 'tomorrow' : "in {$this->daysAway} days";

        return [
            'message'    => "Reminder: Your trip to {$this->booking->destination} is {$when}! (Booking #{$this->booking->id})",
            'booking_id' => $this->booking->id,
            'type'       => 'booking_reminder',
        ];
    }

    public function toMail($notifiable)
    {
        $fullName = trim(
            ($notifiable->first_name ?? '') . ' ' .
            ($notifiable->middle_name ?? '') . ' ' .
            ($notifiable->last_name  ?? '')
        );

        $when   = $this->daysAway === 1 ? 'tomorrow' : "in {$this->daysAway} days";
        $date   = date('F d, Y', strtotime($this->booking->start_date));

        return (new MailMessage)
            ->subject("Trip Reminder 🚐 — Your booking is {$when}!")
            ->greeting('Hello ' . ($fullName ?: 'Customer') . '!')
            ->line("This is a friendly reminder that your upcoming trip is scheduled for **{$date}**.")
            ->line("**Booking #:** {$this->booking->id}")
            ->line("**Pickup:** {$this->booking->pickup}")
            ->line("**Destination:** {$this->booking->destination}")
            ->action('View My Bookings', url('/my-bookings'))
            ->line('Please be at your pickup location on time. Thank you for choosing Rem\'s Transport!');
    }
}
