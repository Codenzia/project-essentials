<?php

namespace Codenzia\ProjectEssentials\Helpers;

use Carbon\Carbon;
use Exception;
use InvalidArgumentException;

class DateRangeHelper
{
    /**
     * Convert start + end dates into a "date - date" string for the form.
     */
    public static function make(?string $start, ?string $end, $separator = '-'): ?string
    {
        if (! $start || ! $end) {
            return null;
        }

        $format = config('app.date_format', 'd/m/Y');

        return Carbon::parse($start)->format($format)
            . " $separator " .
            Carbon::parse($end)->format($format);
    }

    /**
     * Create a shorter, human-friendly date range,
     * but still respect app date_format ordering (d/m vs m/d).
     */
    public static function makeShort(?string $start, ?string $end): ?string
    {
        if (! $start || ! $end) {
            return '';
        }

        $startDate = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        $baseFormat = config('app.date_format', 'd/m/Y');

        // detect order: does config put day before month?
        $dayFirst = strpos($baseFormat, 'd') < strpos($baseFormat, 'm');

        // define short pieces based on config ordering
        $shortDay = $dayFirst ? 'j M' : 'M j';
        $shortDayYr = $dayFirst ? "j M 'y" : "M j 'y";

        // Same year
        if ($startDate->year === $endDate->year) {
            // Same month
            if ($startDate->month === $endDate->month) {
                return $startDate->format($shortDay) . ' - ' . $endDate->format("j 'y");
            }

            return $startDate->format($shortDay) . ' - ' . $endDate->format($shortDayYr);
        }

        // Different years
        return $startDate->format($shortDayYr) . ' - ' . $endDate->format($shortDayYr);
    }

    /**
     * Parse a "date - date" string into Y-m-d start_date and end_date.
     * Will throw an exception if the date does not match config format.
     */
    public static function parse(?string $dateRange, $separator = '-', $endDateOptional = false): array
    {
        if (! $dateRange) {
            return ['start_date' => null, 'end_date' => null];
        }

        $dates = preg_split('/\s+' . preg_quote($separator, '/') . '\s+/', trim($dateRange));
        if (count($dates) === 1 && substr_count($dateRange, $separator) === 1) {
            // unpadded separator and unambiguous — fall back to plain explode
            $dates = explode($separator, $dateRange);
        }

        $start_date = $dates[0];
        $end_date = null;
        if (count($dates) !== 2 && ! $endDateOptional) {
            throw new InvalidArgumentException("Invalid date range format: [$dateRange]");
        }

        if (count($dates) === 2) {
            $end_date = $dates[1];
        }

        $formats = [
            config('app.date_format', 'd/m/Y'), // preferred
            'd/m/Y',
            'm/d/Y',
            'Y-m-d',
            'd-m-Y',
            'm-d-Y',
        ];

        $start = null;
        $end = null;

        foreach ($formats as $format) {
            try {
                $start = Carbon::createFromFormat($format, $start_date);
                if ($end_date) {
                    $end = Carbon::createFromFormat($format, $end_date);
                }
                if ($start && ($end || $endDateOptional)) {
                    return [
                        'start_date' => $start->format(config('app.date_format', 'd/m/Y')),
                        'end_date' => $end?->format(config('app.date_format', 'd/m/Y')),
                    ];
                }
            } catch (Exception $e) {
                // just try the next format
            }
        }

        throw new InvalidArgumentException(
            "Date range [$dateRange] does not match any supported formats"
        );
    }

    public static function tryExplode(?string $dateRange, $separator = '-'): array
    {
        if (! $dateRange) {
            return ['start_date' => null, 'end_date' => null];
        }

        $dates = preg_split('/\s+' . preg_quote($separator, '/') . '\s+/', trim($dateRange));
        if (count($dates) === 1 && substr_count($dateRange, $separator) === 1) {
            // unpadded separator and unambiguous — fall back to plain explode
            $dates = explode($separator, $dateRange);
        }

        $start_date = $dates[0];
        if (count($dates) === 2) {
            $end_date = $dates[1];
        } else {
            $end_date = null;
        }

        return ['start_date' => $start_date, 'end_date' => $end_date];
    }
}
