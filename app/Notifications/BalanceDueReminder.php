<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

class BalanceDueReminder extends Notification
{
    use Queueable;

    protected $bookingId;
    protected $bookingType;
    protected $source;
    protected $destination;
    protected $balance;
    protected $tripDate;

    public function __construct($bookingId, $bookingType, $source, $destination, $balance, $tripDate)
    {
        $this->bookingId = $bookingId;
        $this->bookingType = $bookingType;
        $this->source = $source;
        $this->destination = $destination;
        $this->balance = $balance;
        $this->tripDate = $tripDate;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Reminder: ₱' . number_format($this->balance, 2) . " balance due for your {$this->destination} trip on " . Carbon::parse($this->tripDate)->format('M d, Y') . '.',
            'booking_id' => $this->bookingId,
            'source' => $this->source,
            'type' => 'balance_due_reminder',
        ];
    }

    public function toMail($notifiable)
    {
        $fullName = trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? ''));

        return (new MailMessage)
            ->subject('Balance Due Reminder 💰')
            ->greeting('Hello ' . ($fullName ?: 'Customer') . '!')
            ->line("Your trip to {$this->destination} is coming up on " . Carbon::parse($this->tripDate)->format('F d, Y') . '.')
            ->line('You still have an outstanding balance of ₱' . number_format($this->balance, 2) . '.')
            ->action('View Booking', url('/my-bookings'))
            ->line('You can pay online (if more than 7 days before the trip) or settle it in cash with your driver.');
    }
}
