<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BookingApproved extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    // ✅ SEND BOTH DATABASE + EMAIL
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    // 🔔 DATABASE NOTIFICATION
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your booking has been approved!',
            'booking_id' => $this->booking->id,
            'type' => 'booking_approved'
        ];
    }

    // 📧 EMAIL NOTIFICATION
    public function toMail($notifiable)
    {
        // ✅ FIX FULL NAME (since no "name" column)
        $fullName = trim(
            ($notifiable->first_name ?? '') . ' ' .
            ($notifiable->middle_name ?? '') . ' ' .
            ($notifiable->last_name ?? '')
        );

        return (new MailMessage)
            ->subject('Booking Approved ✅')
            ->greeting('Hello ' . ($fullName ?: 'Customer') . '!')
            ->line('Good news! Your booking has been approved by the admin.')
            ->line('Booking ID: #' . $this->booking->id)
            ->action('View Booking', url('/my-bookings'))
            ->line('Please prepare for your trip. Thank you for choosing Rem\'s Transport!');
    }
}
