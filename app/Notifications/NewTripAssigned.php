<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class NewTripAssigned extends Notification
{
    use Queueable;

    protected $tripType;
    protected $destination;
    protected $date;

    public function __construct($tripType, $destination, $date)
    {
        $this->tripType = $tripType;
        $this->destination = $destination;
        $this->date = $date;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "New {$this->tripType} assigned: {$this->destination} on " . Carbon::parse($this->date)->format('M d, Y') . '.',
            'type' => 'trip_assigned',
        ];
    }
}
