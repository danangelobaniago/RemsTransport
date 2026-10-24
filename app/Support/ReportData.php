<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Everything the admin Reports page shows for one date range.
 *
 * Bookings belong to the period of their TRIP date (start date / joiner trip
 * date) in Philippine time — not when the row was last edited — so an old
 * booking being touched never moves its revenue to another month. Payments
 * and refunds belong to the day they were made.
 */
class ReportData
{
    /** Booking status → outcome group shared by van rentals, tours and joiners. */
    public const OUTCOMES = [
        'completed' => 'Completed',
        'active'    => 'Confirmed / Ongoing',
        'awaiting'  => 'Awaiting Approval',
        'cancelled' => 'Cancelled',
        'rejected'  => 'Rejected',
    ];

    public function __construct(public string $from, public string $to)
    {
    }

    /** Range for a preset name (week, month, year, all) in PH dates, or null for custom. */
    public static function preset(string $name): ?array
    {
        $today = Carbon::parse(LocalDate::today());

        return match ($name) {
            'week'  => [$today->copy()->startOfWeek()->toDateString(), $today->copy()->endOfWeek()->toDateString()],
            'month' => [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()],
            'year'  => [$today->copy()->startOfYear()->toDateString(), $today->copy()->endOfYear()->toDateString()],
            'all'   => [self::earliestTripDate(), $today->copy()->addYear()->toDateString()],
            default => null,
        };
    }

    private static function earliestTripDate(): string
    {
        $dates = array_filter([
            DB::table('bookings')->min('start_date'),
            DB::table('joiner_trips')->min('trip_date'),
        ]);

        return $dates ? min($dates) : LocalDate::today();
    }

    /** Number of days in the range, inclusive. */
    public function days(): int
    {
        return Carbon::parse($this->from)->diffInDays(Carbon::parse($this->to)) + 1;
    }

    /** UTC bounds of the PH range, for timestamp columns (paid_at, refunded_at, created_at). */
    private function utcBounds(): array
    {
        return [
            Carbon::parse($this->from, LocalDate::TIMEZONE)->startOfDay()->setTimezone('UTC'),
            Carbon::parse($this->to, LocalDate::TIMEZONE)->endOfDay()->setTimezone('UTC'),
        ];
    }

    /**
     * Every booking whose trip falls in the range, as one list:
     * {type, id, user_id, customer, date, end_date, created_at, outcome, total, paid,
     *  destination, van_id, plate, van_name, driver_id, driver_name, trip_key}
     */
    public function trips(): Collection
    {
        $rentalsAndTours = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('tour_packages', 'bookings.tour_id', '=', 'tour_packages.id')
            ->leftJoin('vans', 'bookings.van_id', '=', 'vans.id')
            ->leftJoin('drivers', 'bookings.driver', '=', 'drivers.id')
            ->whereBetween('bookings.start_date', [$this->from, $this->to])
            ->select(
                'bookings.id', 'bookings.user_id', 'bookings.tour_id', 'bookings.status', 'bookings.start_date',
                'bookings.end_date', 'bookings.created_at', 'bookings.total', 'bookings.amount_paid',
                'bookings.destination', 'bookings.package_name', 'bookings.van_id',
                'users.first_name', 'users.last_name',
                'tour_packages.name as tour_name', 'tour_packages.van_id as tour_van_id',
                'tour_packages.plate_number as tour_plate', 'tour_packages.van as tour_van',
                'tour_packages.driver_name as tour_driver', 'tour_packages.driver_id as tour_driver_id',
                'vans.name as van_name', 'vans.plate_number as van_plate',
                'drivers.id as driver_id', 'drivers.name as driver_name'
            )
            ->get()
            ->map(fn ($b) => (object) [
                'type'        => $b->tour_id ? 'Tour Package' : 'Van Rental',
                'id'          => $b->id,
                'ref'         => 'PV-' . $b->id,
                'user_id'     => $b->user_id,
                'customer'    => trim(($b->first_name ?? '') . ' ' . ($b->last_name ?? '')) ?: 'Customer',
                'date'        => $b->start_date,
                'end_date'    => $b->end_date ?: $b->start_date,
                'created_at'  => $b->created_at,
                'outcome'     => match ($b->status) {
                    'completed' => 'completed',
                    'cancelled' => 'cancelled',
                    'rejected'  => 'rejected',
                    'approved'  => 'active',
                    default     => 'awaiting', // pending / downpayment_paid / fully_paid, waiting for the admin
                },
                'total'       => (float) $b->total,
                'paid'        => (float) $b->amount_paid,
                'destination' => $b->tour_id ? ($b->tour_name ?? $b->package_name ?? $b->destination) : $b->destination,
                'van_id'      => $b->tour_id ? $b->tour_van_id : $b->van_id,
                'plate'       => $b->tour_id ? $b->tour_plate : $b->van_plate,
                'van_name'    => $b->tour_id ? $b->tour_van : $b->van_name,
                'driver_id'   => $b->tour_id ? $b->tour_driver_id : $b->driver_id,
                'driver_name' => $b->tour_id ? $b->tour_driver : $b->driver_name,
                'trip_key'    => 'b' . $b->id, // one booking = one trip for the van/driver
            ]);

        $joiners = DB::table('joiner_bookings')
            ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
            ->leftJoin('users', 'joiner_bookings.user_id', '=', 'users.id')
            ->whereBetween('joiner_trips.trip_date', [$this->from, $this->to])
            ->select(
                'joiner_bookings.id', 'joiner_bookings.user_id', 'joiner_bookings.status', 'joiner_bookings.created_at',
                'joiner_bookings.total_price', 'joiner_bookings.downpayment', 'joiner_bookings.amount_paid',
                'joiner_trips.id as trip_id', 'joiner_trips.status as trip_status_flag', 'joiner_trips.trip_date',
                'joiner_trips.end_date', 'joiner_trips.destination', 'joiner_trips.plate_number', 'joiner_trips.van',
                'joiner_trips.driver_name', 'users.first_name', 'users.last_name'
            )
            ->get()
            ->map(fn ($j) => (object) [
                'type'        => 'Joiner Trip',
                'id'          => $j->id,
                'ref'         => 'JT-' . $j->id,
                'user_id'     => $j->user_id,
                'customer'    => trim(($j->first_name ?? '') . ' ' . ($j->last_name ?? '')) ?: 'Customer',
                'date'        => $j->trip_date,
                'end_date'    => $j->end_date ?: $j->trip_date,
                'created_at'  => $j->created_at,
                'outcome'     => match (true) {
                    $j->status === 'completed'                                   => 'completed',
                    $j->status === 'cancelled'                                   => 'cancelled',
                    in_array($j->trip_status_flag, ['rejected', 'cancelled'], true) => 'rejected',
                    default                                                      => 'active', // joiner seats don't need approval
                },
                'total'       => (float) $j->total_price,
                'paid'        => max((float) $j->amount_paid, (float) $j->downpayment),
                'destination' => $j->destination,
                'van_id'      => null,
                'plate'       => $j->plate_number,
                'van_name'    => $j->van,
                'driver_id'   => null,
                'driver_name' => $j->driver_name,
                'trip_key'    => 'j' . $j->trip_id, // many joiner bookings share one trip
            ]);

        return $rentalsAndTours->concat($joiners)->sortBy('date')->values();
    }

    /** Payment rows made in the range (PH days), with refunds made in the range. */
    public function payments(): array
    {
        [$from, $to] = $this->utcBounds();

        $rows = DB::table('booking_payments')->whereBetween('paid_at', [$from, $to])->get(['amount', 'method', 'collected_by']);
        $byMethod = $rows->groupBy(function ($p) {
            if ($p->collected_by === 'driver' || strtolower((string) $p->method) === 'cash') {
                return 'Cash';
            }
            return match (strtolower((string) $p->method)) {
                'gcash'                    => 'GCash',
                'card', 'credit/debit card' => 'Card',
                default                    => 'Online (other)',
            };
        })->map->sum('amount')->sortDesc();

        $refunds = (float) DB::table('booking_refunds')->whereBetween('refunded_at', [$from, $to])->sum('amount');

        return [
            'by_method' => $byMethod,
            'received'  => (float) $rows->sum('amount'),
            'refunds'   => $refunds,
            'net'       => (float) $rows->sum('amount') - $refunds,
        ];
    }

    /** Money still to come in from bookings that haven't happened or finished yet (not range-bound). */
    public static function outstandingBalances(): array
    {
        $today = LocalDate::today();

        $bookings = DB::table('bookings')
            ->whereIn('status', ['pending', 'downpayment_paid', 'fully_paid', 'approved'])
            ->where('start_date', '>=', $today)
            ->get(['total', 'amount_paid'])
            ->sum(fn ($b) => max(0, (float) $b->total - (float) $b->amount_paid));

        $joiners = DB::table('joiner_bookings')
            ->join('joiner_trips', 'joiner_bookings.joiner_trip_id', '=', 'joiner_trips.id')
            ->whereNotIn('joiner_bookings.status', ['cancelled', 'completed'])
            ->where('joiner_trips.status', 'active')
            ->where('joiner_trips.trip_date', '>=', $today)
            ->get(['joiner_bookings.total_price', 'joiner_bookings.downpayment', 'joiner_bookings.amount_paid'])
            ->sum(fn ($j) => max(0, (float) $j->total_price - max((float) $j->amount_paid, (float) $j->downpayment)));

        return ['amount' => $bookings + $joiners];
    }

    /** Refunds owed but not yet sent (not range-bound). */
    public static function refundsOwed(): float
    {
        return (float) array_sum(BookingRefund::owed());
    }

    /** Days of the range each trip occupies (multi-day trips clipped to the range). */
    private function daysInRange(object $t): array
    {
        $start = max($t->date, $this->from);
        $end   = min($t->end_date ?: $t->date, $this->to);
        $days  = [];
        for ($d = Carbon::parse($start); $d->toDateString() <= $end; $d->addDay()) {
            $days[] = $d->toDateString();
        }
        return $days;
    }

    /** Per van: trips, days used, utilization % of the range, and completed revenue. */
    public function vanUtilization(Collection $trips): Collection
    {
        $live = $trips->whereNotIn('outcome', ['cancelled', 'rejected']);
        $rangeDays = $this->days();

        return DB::table('vans')->orderBy('name')->get(['id', 'name', 'plate_number'])->map(function ($van) use ($live, $rangeDays) {
            $mine = $live->filter(fn ($t) => ($t->van_id && (int) $t->van_id === (int) $van->id)
                || (!$t->van_id && $t->plate && $t->plate === $van->plate_number));

            $days = $mine->groupBy('trip_key')->flatMap(fn ($group) => $this->daysInRange($group->first()))->unique();

            return (object) [
                'name'        => $van->name,
                'plate'       => $van->plate_number,
                'trips'       => $mine->pluck('trip_key')->unique()->count(),
                'days_used'   => $days->count(),
                'utilization' => $rangeDays > 0 ? round($days->count() / $rangeDays * 100) : 0,
                'revenue'     => $mine->where('outcome', 'completed')->sum('total'),
            ];
        })->sortByDesc('utilization')->values();
    }

    /** Per driver: completed trips, revenue from them, and average customer rating (all time). */
    public function driverPerformance(Collection $trips): Collection
    {
        $ratings = DriverRating::averages();
        $completed = $trips->where('outcome', 'completed');
        $active = $trips->whereNotIn('outcome', ['cancelled', 'rejected']);

        return DB::table('drivers')->orderBy('name')->get(['id', 'name'])->map(function ($d) use ($completed, $active, $ratings) {
            $isMine = fn ($t) => ($t->driver_id && (int) $t->driver_id === (int) $d->id)
                || (!$t->driver_id && $t->driver_name && strcasecmp($t->driver_name, $d->name) === 0);

            return (object) [
                'name'         => $d->name,
                'trips'        => $completed->filter($isMine)->pluck('trip_key')->unique()->count(),
                'assigned'     => $active->filter($isMine)->pluck('trip_key')->unique()->count(),
                'revenue'      => $completed->filter($isMine)->sum('total'),
                'rating'       => $ratings[$d->id]->avg_rating ?? null,
                'rating_count' => $ratings[$d->id]->rating_count ?? 0,
            ];
        })->sortByDesc('trips')->values();
    }

    /** Most booked destinations (city part, before the first comma) and tour packages. */
    public function topDestinations(Collection $trips, int $limit = 5): array
    {
        $live = $trips->whereNotIn('outcome', ['cancelled', 'rejected']);
        $city = fn ($dest) => trim(explode(',', (string) $dest)[0]) ?: '—';

        return [
            'places' => $live->where('type', '!=', 'Tour Package')
                ->groupBy(fn ($t) => mb_strtolower($city($t->destination)))
                ->map(fn ($g) => (object) ['name' => $city($g->first()->destination), 'count' => $g->count(), 'revenue' => $g->where('outcome', 'completed')->sum('total')])
                ->sortByDesc('count')->take($limit)->values(),
            'tours'  => $live->where('type', 'Tour Package')
                ->groupBy('destination')
                ->map(fn ($g, $name) => (object) ['name' => $name, 'count' => $g->count(), 'revenue' => $g->where('outcome', 'completed')->sum('total')])
                ->sortByDesc('count')->take($limit)->values(),
        ];
    }

    /** Bookings by weekday of the trip (Mon–Sun) in the range. */
    public function byWeekday(Collection $trips): array
    {
        $counts = array_fill_keys(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 0);
        foreach ($trips->whereNotIn('outcome', ['cancelled', 'rejected']) as $t) {
            $counts[Carbon::parse($t->date)->format('D')]++;
        }
        return $counts;
    }

    /** Completed revenue and booking count per month for the 12 months ending with the range's last month. */
    public static function monthlyTrend(string $endDate): Collection
    {
        $end = Carbon::parse($endDate)->startOfMonth();
        $first = $end->copy()->subMonths(11);

        $all = (new self($first->toDateString(), $end->copy()->endOfMonth()->toDateString()))->trips()
            ->whereNotIn('outcome', ['cancelled', 'rejected']);

        return collect(range(0, 11))->map(function ($i) use ($first, $all) {
            $month = $first->copy()->addMonths($i)->format('Y-m');
            $inMonth = $all->filter(fn ($t) => substr($t->date, 0, 7) === $month);
            return [
                'label'    => Carbon::parse($month . '-01')->format('M Y'),
                'revenue'  => round($inMonth->where('outcome', 'completed')->sum('total'), 2),
                'bookings' => $inMonth->count(),
            ];
        });
    }

    /** New vs repeat customers, and the top spenders in the range. */
    public function customers(Collection $trips): array
    {
        [$from, $to] = $this->utcBounds();

        // A repeat customer has 2+ non-cancelled, non-rejected bookings overall
        $perCustomer = DB::table('bookings')->whereNotIn('status', ['cancelled', 'rejected'])
            ->select('user_id', DB::raw('COUNT(*) c'))->groupBy('user_id')->pluck('c', 'user_id');
        foreach (DB::table('joiner_bookings')->where('status', '!=', 'cancelled')
                     ->select('user_id', DB::raw('COUNT(*) c'))->groupBy('user_id')->pluck('c', 'user_id') as $uid => $c) {
            $perCustomer[$uid] = ($perCustomer[$uid] ?? 0) + $c;
        }
        $customersWithBookings = $perCustomer->filter(fn ($c) => $c > 0);
        $repeat = $customersWithBookings->filter(fn ($c) => $c >= 2)->count();

        return [
            'total'       => DB::table('users')->where('role', 'customer')->count(),
            'new'         => DB::table('users')->where('role', 'customer')->whereBetween('created_at', [$from, $to])->count(),
            'booked'      => $customersWithBookings->count(),
            'repeat'      => $repeat,
            'repeat_rate' => $customersWithBookings->count() ? round($repeat / $customersWithBookings->count() * 100) : 0,
            'top'         => $trips->where('outcome', 'completed')->groupBy('user_id')
                ->map(fn ($g) => (object) ['name' => $g->first()->customer, 'trips' => $g->count(), 'spent' => $g->sum('total')])
                ->sortByDesc('spent')->take(5)->values(),
        ];
    }

    /** How far ahead customers book: average days and a bucketed spread. */
    public function leadTime(Collection $trips): array
    {
        $buckets = ['Same / next day' => 0, '2–7 days' => 0, '8–30 days' => 0, 'Over 30 days' => 0];
        $days = [];

        foreach ($trips->whereNotIn('outcome', ['cancelled', 'rejected']) as $t) {
            if (!$t->created_at) {
                continue;
            }
            $booked = Carbon::parse($t->created_at, 'UTC')->setTimezone(LocalDate::TIMEZONE)->startOfDay();
            $lead = max(0, (int) $booked->diffInDays(Carbon::parse($t->date), false));
            $days[] = $lead;
            $bucket = match (true) {
                $lead <= 1  => 'Same / next day',
                $lead <= 7  => '2–7 days',
                $lead <= 30 => '8–30 days',
                default     => 'Over 30 days',
            };
            $buckets[$bucket]++;
        }

        return [
            'average' => $days ? round(array_sum($days) / count($days), 1) : null,
            'buckets' => $buckets,
        ];
    }
}
