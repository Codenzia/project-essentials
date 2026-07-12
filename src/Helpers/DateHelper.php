<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Helpers;

use Carbon\Carbon;

/**
 * Date and time formatting helpers.
 */
class DateHelper
{
    public static function getDelayPeriods(): array
    {
        $range = range(1, 12);

        return array_combine($range, array_map(
            fn ($value) => $value . ' ' . self::getPeriodSuffix($value),
            $range
        ));
    }

    public static function getPeriodSuffix(int $state): string
    {
        return match ($state) {
            1 => 'month',
            default => 'months',
        };
    }

    public static function lastModified(string $path): ?int
    {
        return file_exists($path) ? filemtime($path) : null;
    }

    public static function getHumanReadableDuration($start_date, $end_date): string
    {
        if (! $start_date || ! $end_date) {
            return '';
        }

        $startDate = Carbon::parse($start_date);
        $endDate = Carbon::parse($end_date);
        $now = now();

        $totalDays = number_format($startDate->diffInDays($endDate, true), 0);

        if ($now->greaterThan($startDate) && $now->lessThan($endDate)) {
            $daysLeft = number_format($now->diffInDays($endDate), 0);

            return __(':total_days days (:days_left left)', [
                'total_days' => $totalDays,
                'days_left' => $daysLeft,
            ]);
        }

        if ($now->greaterThan($endDate)) {
            return __(':total_days days', [
                'total_days' => $totalDays,
            ]);
        }

        if ($now->lessThan($startDate)) {
            return __(':total_days days (Starts soon)', [
                'total_days' => $totalDays,
            ]);
        }

        return '';
    }

    /**
     * Convert PHP date format tokens to human-readable placeholders.
     * e.g. "Y-m-d" => "yyyy-mm-dd", "d/m/Y" => "dd/mm/yyyy"
     */
    public static function readableDateFormat(?string $format = null): string
    {
        $format ??= config('app.date_format');

        return strtr($format, [
            'Y' => 'yyyy', 'y' => 'yy',
            'F' => 'mmmm', 'M' => 'mmm', 'm' => 'mm', 'n' => 'm',
            'l' => 'dddd', 'D' => 'ddd', 'd' => 'dd', 'j' => 'd',
        ]);
    }

    /**
     * Convert PHP datetime format tokens to human-readable placeholders.
     * e.g. "Y-m-d H:i" => "yyyy-mm-dd hh:mm"
     */
    public static function readableDateTimeFormat(?string $format = null): string
    {
        $format ??= config('app.datetime_format');

        return strtr($format, [
            'Y' => 'yyyy', 'y' => 'yy',
            'F' => 'mmmm', 'M' => 'mmm', 'm' => 'mm', 'n' => 'm',
            'l' => 'dddd', 'D' => 'ddd', 'd' => 'dd', 'j' => 'd',
            'H' => 'hh', 'h' => 'hh', 'G' => 'hh', 'g' => 'h',
            'i' => 'mm', 's' => 'ss',
            'A' => 'AM/PM', 'a' => 'am/pm',
        ]);
    }
}
