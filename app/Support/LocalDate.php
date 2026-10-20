<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * "Today" as the business sees it. The app clock runs in UTC (stored
 * timestamps depend on that), which is 8 hours behind the Philippines —
 * so between 12:00 AM and 7:59 AM PH time a plain date('Y-m-d') still
 * returns yesterday. Use this for any "is the trip today?" check.
 */
class LocalDate
{
    const TIMEZONE = 'Asia/Manila';

    public static function today(): string
    {
        return Carbon::now(self::TIMEZONE)->toDateString();
    }
}
