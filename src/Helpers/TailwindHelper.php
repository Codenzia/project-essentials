<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Helpers;

/**
 * Tailwind v4 safe color class resolver.
 *
 * Dynamic class interpolation like `text-{{ $color }}-500` doesn't work in Tailwind v4
 * because the JIT compiler can't detect classes at build time. This helper maps Filament
 * color names to full, statically-analyzable class strings.
 */
class TailwindHelper
{
    /**
     * Filament color name → text color classes (shade variants).
     */
    private static array $textColors = [
        'primary' => ['400' => 'text-primary-400', '500' => 'text-primary-500', '600' => 'text-primary-600', '700' => 'text-primary-700'],
        'success' => ['400' => 'text-success-400', '500' => 'text-success-500', '600' => 'text-success-600', '700' => 'text-success-700'],
        'warning' => ['400' => 'text-warning-400', '500' => 'text-warning-500', '600' => 'text-warning-600', '700' => 'text-warning-700'],
        'danger' => ['400' => 'text-danger-400',  '500' => 'text-danger-500',  '600' => 'text-danger-600',  '700' => 'text-danger-700'],
        'info' => ['400' => 'text-info-400',    '500' => 'text-info-500',    '600' => 'text-info-600',    '700' => 'text-info-700'],
        'gray' => ['400' => 'text-gray-400',    '500' => 'text-gray-500',    '600' => 'text-gray-600',    '700' => 'text-gray-700'],
    ];

    /**
     * Filament color name → bg color classes (shade variants).
     */
    private static array $bgColors = [
        'primary' => ['100' => 'bg-primary-100', '500' => 'bg-primary-500', '600' => 'bg-primary-600'],
        'success' => ['100' => 'bg-success-100', '500' => 'bg-success-500', '600' => 'bg-success-600'],
        'warning' => ['100' => 'bg-warning-100', '500' => 'bg-warning-500', '600' => 'bg-warning-600'],
        'danger' => ['100' => 'bg-danger-100',  '500' => 'bg-danger-500',  '600' => 'bg-danger-600'],
        'info' => ['100' => 'bg-info-100',    '500' => 'bg-info-500',    '600' => 'bg-info-600'],
        'gray' => ['100' => 'bg-gray-100',    '500' => 'bg-gray-500',    '600' => 'bg-gray-600'],
    ];

    /**
     * Filament color name → border color classes (shade variants).
     */
    private static array $borderColors = [
        'primary' => ['200' => 'border-primary-200', '500' => 'border-primary-500'],
        'success' => ['200' => 'border-success-200', '500' => 'border-success-500'],
        'warning' => ['200' => 'border-warning-200', '500' => 'border-warning-500'],
        'danger' => ['200' => 'border-danger-200',  '500' => 'border-danger-500'],
        'info' => ['200' => 'border-info-200',    '500' => 'border-info-500'],
        'gray' => ['200' => 'border-gray-200',    '500' => 'border-gray-500'],
    ];

    /**
     * Get a text color class for a Filament color name.
     *
     * @param  string  $color  Filament color name (primary, success, warning, danger, info, gray)
     * @param  string  $shade  Tailwind shade (400, 500, 600, 700)
     */
    public static function text(string $color, string $shade = '500'): string
    {
        return self::$textColors[$color][$shade] ?? "text-gray-{$shade}";
    }

    /**
     * Get a bg color class for a Filament color name.
     *
     * @param  string  $color  Filament color name
     * @param  string  $shade  Tailwind shade (100, 500, 600)
     */
    public static function bg(string $color, string $shade = '500'): string
    {
        return self::$bgColors[$color][$shade] ?? "bg-gray-{$shade}";
    }

    /**
     * Get a border color class for a Filament color name.
     *
     * @param  string  $color  Filament color name
     * @param  string  $shade  Tailwind shade (200, 500)
     */
    public static function border(string $color, string $shade = '500'): string
    {
        return self::$borderColors[$color][$shade] ?? "border-gray-{$shade}";
    }
}
