<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    protected $bookingId;
    protected $bookingType;
    protected $amount;
    protected $remainingBalance;

    public function __construct($bookingId, $bookingType, $amount, $remainingBalance)
    {
        $this->bookingId = $bookingId;
        $this->bookingType = $bookingType;
        $this->amount = $amount;
        $this->remainingBalance = $remainingBalance;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $message = '₱' . number_format($this->amount, 2) . " payment received for your {$this->bookingType} booking #{$this->bookingId}.";
        $message .= $this->remainingBalance > 0
            ? ' Remaining balance: ₱' . number_format($this->remainingBalance, 2) . '.'
            : ' Your booking is now fully paid!';

        return [
            'message' => $message,
            'booking_id' => $this->bookingId,
            'type' => 'payment_received',
        ];
    }
}
