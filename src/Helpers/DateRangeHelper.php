<?php

declare(strict_types=1);

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

        $format = config('app.date_format') ?? 'd/m/Y';

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

        $baseFormat = config('app.date_format') ?? 'd/m/Y';

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
     * Parse a "date - date" string into start_date and end_date, both rendered in
     * config('app.date_format').
     *
     * Throws when the string matches no supported format, when either side is an
     * impossible calendar date (31/02/2026 is rejected, never rolled into March),
     * or when the end date falls before the start date.
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

        $displayFormat = config('app.date_format') ?? 'd/m/Y';

        $formats = [
            $displayFormat, // preferred
            'd/m/Y',
            'm/d/Y',
            'Y-m-d',
            'd-m-Y',
            'm-d-Y',
        ];

        foreach ($formats as $format) {
            $start = self::parseExact($format, $start_date);

            if (! $start) {
                continue;
            }

            $end = null;

            if ($end_date !== null) {
                $end = self::parseExact($format, $end_date);

                if (! $end) {
                    continue;
                }
            }

            if ($end && $start->greaterThan($end)) {
                throw new InvalidArgumentException(
                    "Date range [$dateRange] ends before it starts"
                );
            }

            return [
                'start_date' => $start->format($displayFormat),
                'end_date' => $end?->format($displayFormat),
            ];
        }

        throw new InvalidArgumentException(
            "Date range [$dateRange] does not match any supported formats"
        );
    }

    /**
     * Parse one date strictly. Carbon rolls impossible dates over (31/02/2026 becomes
     * 03/03/2026), so a value only counts as parsed when it round-trips to the input.
     */
    private static function parseExact(string $format, ?string $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat($format, $value);
        } catch (Exception) {
            return null;
        }

        if (! $date instanceof Carbon) {
            return null;
        }

        $errors = Carbon::getLastErrors();

        if (is_array($errors) && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0)) {
            return null;
        }

        return $date->format($format) === $value ? $date : null;
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
