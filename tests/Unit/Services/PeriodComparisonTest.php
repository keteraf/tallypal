<?php

declare(strict_types=1);

use App\Enums\StatsRange;
use App\Services\PeriodComparison;
use Carbon\CarbonImmutable;

covers(PeriodComparison::class);

test('the period starts on Monday, the 1st of the month or January 1st', function (StatsRange $range, string $expected): void {
    $start = PeriodComparison::periodStart($range, CarbonImmutable::parse('2026-10-14 12:30:00'));

    expect($start->toDateTimeString())->toBe($expected);
})->with([
    'week' => [StatsRange::WEEK, '2026-10-12 00:00:00'],
    'month' => [StatsRange::MONTH, '2026-10-01 00:00:00'],
    'year' => [StatsRange::YEAR, '2026-01-01 00:00:00'],
]);

test('the previous period is the same span of the previous week, month or year', function (StatsRange $range, string $now, string $start, string $end): void {
    $previous = PeriodComparison::previousPeriod($range, CarbonImmutable::parse($now));

    expect($previous['start']->toDateString())->toBe($start)
        ->and($previous['end']->toDateString())->toBe($end);
})->with([
    'week, Wednesday' => [StatsRange::WEEK, '2026-10-14 12:00:00', '2026-10-05', '2026-10-07'],
    'week, Monday' => [StatsRange::WEEK, '2026-10-12 08:00:00', '2026-10-05', '2026-10-05'],
    'week across a year' => [StatsRange::WEEK, '2026-01-01 12:00:00', '2025-12-22', '2025-12-25'],
    'month, mid-month' => [StatsRange::MONTH, '2026-10-14 12:00:00', '2026-09-01', '2026-09-14'],
    'month, the 1st' => [StatsRange::MONTH, '2026-10-01 09:00:00', '2026-09-01', '2026-09-01'],
    'month, clamped to February' => [StatsRange::MONTH, '2026-03-31 12:00:00', '2026-02-01', '2026-02-28'],
    'month, clamped to a leap February' => [StatsRange::MONTH, '2028-03-31 12:00:00', '2028-02-01', '2028-02-29'],
    'month, unclamped March 28' => [StatsRange::MONTH, '2026-03-28 12:00:00', '2026-02-01', '2026-02-28'],
    'month, January to December' => [StatsRange::MONTH, '2026-01-01 12:00:00', '2025-12-01', '2025-12-01'],
    'year, mid-year' => [StatsRange::YEAR, '2026-10-14 12:00:00', '2025-01-01', '2025-10-14'],
    'year, January 1st' => [StatsRange::YEAR, '2026-01-01 12:00:00', '2025-01-01', '2025-01-01'],
    'year, clamped from February 29' => [StatsRange::YEAR, '2028-02-29 12:00:00', '2027-01-01', '2027-02-28'],
]);

test('the day count includes both ends of the span', function (string $start, string $end, int $expected): void {
    expect(PeriodComparison::dayCount(CarbonImmutable::parse($start), CarbonImmutable::parse($end)))->toBe($expected);
})->with([
    'one day' => ['2026-10-01 00:00:00', '2026-10-01 18:00:00', 1],
    'week to Wednesday' => ['2026-10-12 00:00:00', '2026-10-14 12:00:00', 3],
    'month to the 14th' => ['2026-10-01 00:00:00', '2026-10-14 23:59:59', 14],
    'February' => ['2026-02-01 00:00:00', '2026-02-28 00:00:00', 28],
    'year to October 14' => ['2026-01-01 00:00:00', '2026-10-14 12:00:00', 287],
]);

test('the delta is the change over the previous value, rounded to one decimal', function (float $current, float $previous, float $expected): void {
    expect(PeriodComparison::deltaPercentage($current, $previous))->toBe($expected);
})->with([
    'better' => [112.34, 100.0, 12.3],
    'worse' => [100.0, 200.0, -50.0],
    'from a negative base' => [100.0, -200.0, 150.0],
    'unchanged' => [10.0, 10.0, 0.0],
    'rounds up' => [101.26, 100.0, 1.3],
]);

test('the delta is null when the previous value is zero', function (): void {
    expect(PeriodComparison::deltaPercentage(50.0, 0.0))->toBeNull();
});
