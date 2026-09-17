<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\BalanceDueReminder;
use Carbon\Carbon;

class SendBalanceDueReminders extends Command
{
    protected $signature   = 'booking:send-balance-reminders';
    protected $description = 'Remind customers with an outstanding balance as their trip date approaches';

    public function handle(): void
    {
        $this->sendPrivateAndTourReminders(3);
        $this->sendJoinerReminders(3);
        $this->info('Balance due reminders sent.');
    }

    private function sendPrivateAndTourReminders(int $daysAway): void
    {
        $targetDate = Carbon::today()->addDays($daysAway)->toDateString();

        $bookings = DB::table('bookings')
            ->whereDate('start_date', $targetDate)
            ->whereIn('status', ['approved', 'downpayment_paid'])
            ->whereNotNull('user_id')
            ->get();

        foreach ($bookings as $booking) {
            $totalPaid = (float) ($booking->amount_paid ?? $booking->downpayment ?? 0);
            $balance   = round(max(0, (float) $booking->total - $totalPaid), 2);

            if ($balance <= 0) {
                continue;
            }

            $source = $booking->tour_id ? 'tour' : 'van';

            $alreadySent = DB::table('notifications')
                ->where('notifiable_id', $booking->user_id)
                ->where('type', BalanceDueReminder::class)
                ->whereDate('created_at', Carbon::today())
                ->whereRaw("JSON_EXTRACT(data, '$.booking_id') = ?", [$booking->id])
                ->whereRaw("JSON_EXTRACT(data, '$.source') = ?", [$source])
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $user = User::find($booking->user_id);
            if ($user) {
                $destination = $booking->package_name ?? $booking->destination ?? 'your trip';
                $bookingType = $booking->tour_id ? 'Tour Package' : 'Van Rental';
                $user->notify(new BalanceDueReminder($booking->id, $bookingType, $source, $destination, $balance, $booking->start_date));
                $this->line("Balance reminder sent to user #{$user->id} for booking #{$booking->id}");
            }
        }
    }

    private function sendJoinerReminders(int $daysAway): void
    {
        $targetDate = Carbon::today()->addDays($daysAway)->toDateString();

        $bookings = DB::table('joiner_bookings')
            ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
            ->whereDate('joiner_trips.trip_date', $targetDate)
            ->where('joiner_bookings.status', 'downpayment_paid')
            ->whereNotNull('joiner_bookings.user_id')
            ->select('joiner_bookings.*', 'joiner_trips.destination', 'joiner_trips.trip_date')
            ->get();

        foreach ($bookings as $booking) {
            $totalPaid = (float) ($booking->downpayment ?? 0);
            $balance   = round(max(0, (float) $booking->total_price - $totalPaid), 2);

            if ($balance <= 0) {
                continue;
            }

            $alreadySent = DB::table('notifications')
                ->where('notifiable_id', $booking->user_id)
                ->where('type', BalanceDueReminder::class)
                ->whereDate('created_at', Carbon::today())
                ->whereRaw("JSON_EXTRACT(data, '$.booking_id') = ?", [$booking->id])
                ->whereRaw("JSON_EXTRACT(data, '$.source') = ?", ['joiner'])
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $user = User::find($booking->user_id);
            if ($user) {
                $user->notify(new BalanceDueReminder($booking->id, 'Joiner Trip', 'joiner', $booking->destination, $balance, $booking->trip_date));
                $this->line("Balance reminder sent to user #{$user->id} for joiner booking #{$booking->id}");
            }
        }
    }
}
