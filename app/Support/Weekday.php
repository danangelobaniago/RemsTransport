<?php

namespace App\Support;

class Weekday
{
    /** 0 = Sunday ... 6 = Saturday (matches PHP date('w') / Carbon dayOfWeek). */
    public const NAMES = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /** Human label for a weekday number, or null for null/blank/out-of-range. */
    public static function label($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::NAMES[(int) $value] ?? null;
    }
}
