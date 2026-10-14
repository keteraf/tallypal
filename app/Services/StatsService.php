<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\Enums\StatsRange;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * @phpstan-import-type CurrencyShape from TransactionStatsRepositoryInterface
 */
final readonly class StatsService
{
    public function __construct(
        private HomeCacheService $homeCache,
        private TransactionStatsRepositoryInterface $statsRepository,
        private TallyRepositoryInterface $tallyRepository,
    ) {}

    /**
     * Build the full Inertia payload for the Stats page, for all the user's tallies or for one
     * (B-09). Every figure is in one currency: the user's primary currency, or the filtered tally's
     * own (its default, else its most-used); other currencies' expenses are listed apart, never added
     * in or converted (O-04). Amounts are floats in major units, except `owed_to_me.amount_minor`
     * and `owed_to_me.other_currencies[].amount_minor` (integer minor units, B-04). Activity rows each keep their own currency.
     *
     * @return array<string, mixed>
     */
    public function buildStatsPayload(User $user, StatsRange $range, CarbonImmutable $now, ?Tally $tally = null): array
    {
        $userId = (int) $user->id; // @pest-mutate-ignore
        $accessibleTallyIds = $this->homeCache->tallyIds($userId);
        $tallyIds = $tally instanceof Tally ? collect([(int) $tally->tally_id]) : $accessibleTallyIds;

        if ($tallyIds->isEmpty()) {
            return $this->emptyPayload($range, $now);
        }

        $currency = $tally instanceof Tally
            ? $this->tallyCurrency($tally, $user, $accessibleTallyIds)
            : $this->homeCache->primaryCurrency($userId, $tallyIds, $user->default_currency_id);
        $currencyId = $currency['currency_id'];
        $periodStart = PeriodComparison::periodStart($range, $now);
        ['start' => $previousStart, 'end' => $previousEnd] = PeriodComparison::previousPeriod($range, $now);
        $tallyOptions = $this->tallyRepository->filterOptions($accessibleTallyIds);
        $sharedTallyIds = $tallyOptions
            ->filter(fn (array $option): bool => $option['is_shared'])
            ->map(fn (array $option): int => $option['tally_id'])
            ->intersect($tallyIds)
            ->values();

        $series = match ($range) {
            StatsRange::WEEK => $this->statsRepository->dailyHistory($tallyIds, $periodStart, $now, $currencyId),
            StatsRange::YEAR => $this->statsRepository->monthlyHistory($tallyIds, $periodStart, $now, $currencyId),
            StatsRange::MONTH => $this->statsRepository->weeklyHistory($tallyIds, $now->startOfWeek()->subWeeks(11), $now, $currencyId), // @pest-mutate-ignore
        };

        $totalOut = $this->statsRepository->expenseTotal($tallyIds, $periodStart, $now, $currencyId);
        $previousTotalOut = $this->statsRepository->expenseTotal($tallyIds, $previousStart, $previousEnd, $currencyId);
        $sharedTotalOut = $this->statsRepository->expenseTotal($sharedTallyIds, $periodStart, $now, $currencyId);
        $dailyAverage = $totalOut / PeriodComparison::dayCount($periodStart, $now);
        $previousDailyAverage = $previousTotalOut / PeriodComparison::dayCount($previousStart, $previousEnd);

        return [
            'range' => $range->value,
            'range_label' => $this->rangeLabel($range, $now),
            'total_out' => $totalOut,
            'currency_symbol' => $currency['symbol'],
            'currency_code' => $currency['code'],
            'decimals' => $currency['decimals'],
            'series' => $series,
            'categories' => CategoryBars::build($this->statsRepository->categoriesBreakdown($tallyIds, $periodStart, $now, $currencyId)),
            'other_currencies' => $this->statsRepository->otherCurrencyExpenses($tallyIds, $periodStart, $now, $currencyId),
            'daily_average' => round($dailyAverage, $currency['decimals']),
            'previous_total_out' => $previousTotalOut,
            'previous_daily_average' => round($previousDailyAverage, $currency['decimals']),
            'previous_period' => ['start' => $previousStart->toDateString(), 'end' => $previousEnd->toDateString()],
            'delta_percentage' => PeriodComparison::deltaPercentage($dailyAverage, $previousDailyAverage),
            'shared_total_out' => $sharedTotalOut,
            'shared_percentage' => $totalOut === 0.0 ? null : round($sharedTotalOut / $totalOut * 100, 1),
            'owed_to_me' => $this->owedToMe($userId, $currencyId, $tally),
            'activity' => $this->statsRepository->activity($tallyIds, $periodStart, $now, limit: 20),
            'filter' => ['tally_uuid' => $tally?->uuid],
            'tallies' => $tallyOptions->map(fn (array $option): array => ['uuid' => $option['uuid'], 'name' => $option['name'], 'is_shared' => $option['is_shared']])->all(),
        ];
    }

    /**
     * The filtered tally's currency in the B-11 prefill order (its default, then its most-used);
     * the user's primary currency when there is nothing to prefill.
     *
     * @param  Collection<int, int>  $accessibleTallyIds
     * @return CurrencyShape
     */
    private function tallyCurrency(Tally $tally, User $user, Collection $accessibleTallyIds): array
    {
        $currency = $tally->prefillCurrency($user);

        if (! $currency instanceof Currency) {
            return $this->homeCache->primaryCurrency((int) $user->id, $accessibleTallyIds, $user->default_currency_id);
        }

        return [
            'symbol' => (string) $currency->symbol,
            'code' => (string) $currency->code,
            'decimals' => (int) $currency->decimals,
            'currency_id' => (int) $currency->currency_id,
        ];
    }

    /**
     * The open debts owed to the user in one currency (B-04), in the filtered tally only when there
     * is one: their total in integer minor units and how many there are. The debts in every other
     * currency are listed apart, one total each in minor units in the order they appear in the
     * ledger, never added to the figure or converted (O-04).
     *
     * @return array{amount_minor: int, open_balances: int, other_currencies: list<array{amount_minor: int, currency_symbol: string, currency_code: string, decimals: int}>}
     */
    private function owedToMe(int $userId, int $currencyId, ?Tally $tally): array
    {
        $debts = collect($this->homeCache->balanceSummary($userId)['owed_to_me']);

        if ($tally instanceof Tally) {
            $debts = $debts->where('tally_uuid', $tally->uuid);
        }

        $inKpiCurrency = $debts->where('currency_id', $currencyId);
        $otherCurrencies = [];

        foreach ($debts->where('currency_id', '!=', $currencyId) as $debt) {
            $otherCurrencies[$debt['currency_id']] ??= [
                'amount_minor' => 0,
                'currency_symbol' => $debt['currency']['symbol'],
                'currency_code' => $debt['currency']['code'],
                'decimals' => $debt['currency']['decimals'],
            ];
            $otherCurrencies[$debt['currency_id']]['amount_minor'] += $debt['amount'];
        }

        return [
            'amount_minor' => $inKpiCurrency->reduce(fn (int $total, array $debt): int => $total + $debt['amount'], 0),
            'open_balances' => $inKpiCurrency->count(),
            'other_currencies' => array_values($otherCurrencies),
        ];
    }

    private function rangeLabel(StatsRange $range, CarbonImmutable $now): string
    {
        return match ($range) {
            StatsRange::WEEK => $now->startOfWeek()->format('M j') . ' – ' . $now->startOfWeek()->addDays(6)->format('M j'),
            StatsRange::YEAR => $now->format('Y'),
            StatsRange::MONTH => $now->format('F Y'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(StatsRange $range, CarbonImmutable $now): array
    {
        return [
            'range' => $range->value,
            'range_label' => $this->rangeLabel($range, $now),
            'total_out' => 0.0,
            'currency_symbol' => '€',
            'currency_code' => 'EUR',
            'decimals' => 2,
            'series' => [],
            'categories' => [],
            'other_currencies' => [],
            'daily_average' => 0.0,
            'previous_total_out' => 0.0,
            'previous_daily_average' => 0.0,
            'previous_period' => null,
            'delta_percentage' => null,
            'shared_total_out' => 0.0,
            'shared_percentage' => null,
            'owed_to_me' => ['amount_minor' => 0, 'open_balances' => 0, 'other_currencies' => []],
            'activity' => [],
            'filter' => ['tally_uuid' => null],
            'tallies' => [],
        ];
    }
}
