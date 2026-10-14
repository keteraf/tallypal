<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StatsRange;
use Carbon\CarbonImmutable;

/**
 * B-03: a period to date compared with the same span of the previous period, on
 * `transaction_date`. Shared by the Home delta (month) and the Stats KPIs (week, month, year).
 */
final class PeriodComparison
{
    /**
     * The first day of the week (Monday), month or year containing `$now`.
     */
    public static function periodStart(StatsRange $range, CarbonImmutable $now): CarbonImmutable
    {
        return match ($range) {
            StatsRange::WEEK => $now->startOfWeek(),
            StatsRange::YEAR => $now->startOfYear(),
            StatsRange::MONTH => $now->startOfMonth(),
        };
    }

    /**
     * The same span of the previous period: last week's Monday to the same weekday, the previous
     * month's 1st to the same day (clamped when that month is shorter), or last year's January 1st to
     * the same date (February 29 → February 28).
     *
     * @return array{start: CarbonImmutable, end: CarbonImmutable}
     */
    public static function previousPeriod(StatsRange $range, CarbonImmutable $now): array
    {
        if ($range === StatsRange::MONTH) {
            $previousPeriodStart = $now->startOfMonth()->subMonthNoOverflow();
            $previousPeriodEnd = $previousPeriodStart->setDate($previousPeriodStart->year, $previousPeriodStart->month, min($now->day, $previousPeriodStart->daysInMonth));

            return ['start' => $previousPeriodStart, 'end' => $previousPeriodEnd];
        }

        $previousEnd = $range === StatsRange::WEEK ? $now->subWeek() : $now->subYearNoOverflow();

        return ['start' => self::periodStart($range, $previousEnd), 'end' => $previousEnd->startOfDay()];
    }

    /**
     * The number of calendar days from `$start` to `$end`, both included.
     */
    public static function dayCount(CarbonImmutable $start, CarbonImmutable $end): int
    {
        return (int) $start->startOfDay()->diffInDays($end->startOfDay()) + 1;
    }

    /**
     * `(current - previous) / |previous| × 100`, one decimal, so its sign is the sign of
     * `current - previous` (an improvement from a negative base is positive); null when the
     * previous value is 0.
     */
    public static function deltaPercentage(float $current, float $previous): ?float
    {
        return $previous === 0.0
            ? null
            : round(($current - $previous) / abs($previous) * 100, 1);
    }
}
