<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\BookingReminder;
use Carbon\Carbon;

class SendBookingReminders extends Command
{
    protected $signature   = 'booking:send-reminders';
    protected $description = 'Send reminder notifications for bookings starting tomorrow or in 3 days';

    public function handle(): void
    {
        $this->sendReminders(1);
        $this->sendReminders(3);
        $this->info('Booking reminders sent.');
    }

    private function sendReminders(int $daysAway): void
    {
        $targetDate = Carbon::today()->addDays($daysAway)->toDateString();

        $bookings = DB::table('bookings')
            ->whereDate('start_date', $targetDate)
            ->whereIn('status', ['approved', 'downpayment_paid', 'fully_paid'])
            ->whereNotNull('user_id')
            ->get();

        foreach ($bookings as $booking) {
            // Skip if a reminder for this booking was already sent today
            $alreadySent = DB::table('notifications')
                ->where('notifiable_id', $booking->user_id)
                ->where('type', BookingReminder::class)
                ->whereDate('created_at', Carbon::today())
                ->whereRaw("JSON_EXTRACT(data, '$.booking_id') = ?", [$booking->id])
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $user = User::find($booking->user_id);
            if ($user) {
                $user->notify(new BookingReminder($booking, $daysAway));
                $this->line("Reminder ({$daysAway}d) sent to user #{$user->id} for booking #{$booking->id}");
            }
        }
    }
}
