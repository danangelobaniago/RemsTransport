<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TripCompletedFeedbackRequest extends Notification
{
    use Queueable;

    protected $bookingId;
    protected $destination;

    public function __construct($bookingId, $destination)
    {
        $this->bookingId = $bookingId;
        $this->destination = $destination;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "Your trip to {$this->destination} is complete! Please leave feedback for your driver.",
            'booking_id' => $this->bookingId,
            'type' => 'feedback_request',
        ];
    }
}
