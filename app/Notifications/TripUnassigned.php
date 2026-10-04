<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * Tells a driver they've been taken off a trip because the admin assigned
 * another driver.
 */
class TripUnassigned extends Notification
{
    use Queueable;

    public function __construct(
        protected string $tripLabel,
        protected ?string $date,
        protected string $reason
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $on = $this->date ? ' on ' . Carbon::parse($this->date)->format('M d, Y') : '';

        return [
            'message' => "You have been reassigned from {$this->tripLabel}{$on}. Reason: {$this->reason}.",
            'type'    => 'trip_unassigned',
        ];
    }
}
