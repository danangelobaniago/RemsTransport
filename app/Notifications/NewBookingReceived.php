<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBookingReceived extends Notification
{
    use Queueable;

    protected $bookingId;
    protected $bookingType;
    protected $customerName;
    protected $destination;
    protected $amount;

    public function __construct($bookingId, $bookingType, $customerName, $destination, $amount)
    {
        $this->bookingId = $bookingId;
        $this->bookingType = $bookingType;
        $this->customerName = $customerName;
        $this->destination = $destination;
        $this->amount = $amount;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "New {$this->bookingType} booking from {$this->customerName} — {$this->destination} (₱" . number_format($this->amount, 2) . ')',
            'booking_id' => $this->bookingId,
            'type' => 'new_booking',
        ];
    }
}
