<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DriverRating
{
    /**
     * Average driver_rating (from feedbacks left on completed trips) for every
     * driver who has at least one, keyed by driver id. Each value has
     * ->avg_rating (rounded to 1 decimal) and ->rating_count.
     */
    public static function averages()
    {
        return DB::table('feedbacks')
            ->join('drivers', 'feedbacks.driver_name', '=', 'drivers.name')
            ->select('drivers.id', DB::raw('ROUND(AVG(feedbacks.driver_rating), 1) as avg_rating'), DB::raw('COUNT(*) as rating_count'))
            ->groupBy('drivers.id')
            ->get()
            ->keyBy('id');
    }
}
