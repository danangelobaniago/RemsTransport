<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Vans of the same model are stored as separate rows, each with its own
 * `color`. Older vans also have the color in parentheses at the end of the
 * name, e.g. "Nissan Urvan NV350 (Gray)", so the model name is taken without
 * it. The public pages use these to show one card per model and let the
 * customer pick the color on the details page.
 */
class VanVariant
{
    /** Swatch colors for the color picker; unknown colors fall back to slate. */
    public const SWATCHES = [
        'white'  => '#ffffff',
        'pearl'  => '#f8f6f0',
        'black'  => '#111827',
        'gray'   => '#9ca3af',
        'grey'   => '#9ca3af',
        'silver' => '#cbd5e1',
        'red'    => '#dc2626',
        'maroon' => '#7f1d1d',
        'blue'   => '#2563eb',
        'navy'   => '#1e3a8a',
        'green'  => '#16a34a',
        'yellow' => '#facc15',
        'orange' => '#f97316',
        'brown'  => '#78350f',
        'beige'  => '#e7d8b8',
        'gold'   => '#d4a017',
    ];

    /** "Nissan Urvan NV350 (Gray)" → "Nissan Urvan NV350" */
    public static function baseName(?string $name): string
    {
        return trim(preg_replace('/\s*\([^()]*\)\s*$/', '', (string) $name));
    }

    /** "Nissan Urvan NV350 (Gray)" → "Gray"; null when the name has no color. */
    public static function colorFromName(?string $name): ?string
    {
        return preg_match('/\(([^()]+)\)\s*$/', (string) $name, $m) ? trim($m[1]) : null;
    }

    /** The van's color column, falling back to the color written in its name. */
    public static function color(object $van): ?string
    {
        $color = trim((string) ($van->color ?? ''));

        return $color !== '' ? $color : self::colorFromName($van->name);
    }

    /** Case/spacing-insensitive key so "Toyota HiAce" and "Toyota Hiace" group together. */
    public static function key(?string $name): string
    {
        return strtolower(preg_replace('/\s+/', ' ', self::baseName($name)));
    }

    /** "Toyota Hiace (White)" — model plus color, for showing which van was picked. */
    public static function displayName(object $van): string
    {
        $color = self::color($van);

        return self::baseName($van->name) . ($color !== null ? " ({$color})" : '');
    }

    /** Label shown on the picker — the color, or the plate number when no color was given. */
    public static function label(object $van): string
    {
        return self::color($van) ?? $van->plate_number;
    }

    public static function swatch(?string $color): string
    {
        $words = preg_split('/[\s\/-]+/', strtolower((string) $color));

        foreach ($words as $word) {
            if (isset(self::SWATCHES[$word])) {
                return self::SWATCHES[$word];
            }
        }

        return '#64748b';
    }

    /** Group van rows by model, keeping each group in the original order. */
    public static function group(Collection $vans): Collection
    {
        return $vans->groupBy(fn ($van) => self::key($van->name))->values();
    }
}
