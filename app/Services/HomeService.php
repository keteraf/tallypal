<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Transaction\GetBalanceSummaryAction;
use App\Enums\StatsRange;
use App\Models\Tally;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * @phpstan-import-type MyBalance from GetBalanceSummaryAction
 */
final readonly class HomeService
{
    public function __construct(
        private HomeCacheService $cache,
        private GetBalanceSummaryAction $balanceSummaryAction,
    ) {}

    /**
     * Build the full Inertia payload for the Home page. Cross-tally aggregates (balance, In/Out,
     * sparkline, By category) are in the primary currency only; other currencies are listed
     * apart, never added in or converted (O-04). Each tally carries the user's balance per currency
     * (`my_balance`) and `settle_suggestion` the debt to settle first (B-04).
     *
     * @return array<string, mixed>
     */
    public function buildHomePayload(User $user, CarbonImmutable $now): array
    {
        $userId = (int) $user->id; // @pest-mutate-ignore
        $monthKey = $now->format('Y-m');
        $dayKey = $now->format('Y-m-d');
        $monthStart = $now->startOfMonth();

        ['start' => $previousPeriodStart, 'end' => $previousPeriodEnd] = PeriodComparison::previousPeriod(StatsRange::MONTH, $now);

        $tallyIds = $this->cache->tallyIds($userId);

        if ($tallyIds->isEmpty()) {
            return $this->emptyPayload($user, $now);
        }

        $primaryCurrency = $this->cache->primaryCurrency($userId, $tallyIds, $user->default_currency_id);
        $primaryCurrencyId = $primaryCurrency['currency_id'];
        $tallySummaries = $this->cache->tallySummaries($userId, $tallyIds, $monthStart, $now, $monthKey);
        $currentBalance = $this->cache->monthlyBalance($userId, $tallyIds, $monthStart, $now, $primaryCurrency, $dayKey);
        $balance = $this->withMonthOverMonthDelta(
            $currentBalance,
            $this->cache->previousMonthNet($userId, $tallyIds, $previousPeriodStart, $previousPeriodEnd, $primaryCurrency, $dayKey),
            $previousPeriodStart,
            $previousPeriodEnd,
        );
        $recentTransactions = $this->cache->recentTransactions($userId, $tallyIds);
        $weeklyHistory = $this->cache->weeklyHistory($userId, $tallyIds, $now->startOfWeek()->subWeeks(11), $now, $primaryCurrencyId, $dayKey); // @pest-mutate-ignore
        $categoriesBreakdown = $this->cache->categoriesBreakdown($userId, $tallyIds, $monthStart, $now, $primaryCurrencyId, $monthKey);
        $categoriesOtherCurrencies = $this->cache->otherCurrencyExpenses($userId, $tallyIds, $monthStart, $now, $primaryCurrencyId, $monthKey);
        $balanceSummary = $this->cache->balanceSummary($userId);

        /** @var array{uuid: string, name: string, color: string|null, glyph: string|null, member_count: int, members_preview: array<int, array{initials: string, color: string}>, split_label: string|null, monthly_totals: array<int, array{amount: float, currency_symbol: string, currency_code: string, decimals: int}>}|null $primaryTally */
        $primaryTally = $tallySummaries->first();

        return [
            'user_name' => $user->name,
            'current_month_label' => $now->format('F Y'),
            'current_month_start' => $now->startOfMonth()->toDateString(),
            'primary_tally' => $primaryTally === null ? null : [
                'uuid' => $primaryTally['uuid'],
                'name' => $primaryTally['name'],
            ],
            'tallies' => $tallySummaries
                ->map(fn (array $summary): array => [...$summary, 'my_balance' => $balanceSummary['my_balances'][$summary['uuid']] ?? []])
                ->all(),
            'settle_suggestion' => $this->balanceSummaryAction->settleSuggestion($balanceSummary['owed_to_me'], $primaryCurrencyId),
            'balance' => $balance,
            'recent_transactions' => $recentTransactions,
            'weekly_history' => $weeklyHistory,
            'categories_breakdown' => $categoriesBreakdown,
            'categories_other_currencies' => $categoriesOtherCurrencies,
        ];
    }

    /**
     * The sidebar tally list: each tally's identity and its full per-currency `monthly_totals`
     * (most-used first), reusing the cached Home summaries. The sidebar picks what to show.
     *
     * @return array<int, array{uuid: string, name: string, color: string|null, glyph: string|null, monthly_totals: array<int, array{amount: float, currency_symbol: string, currency_code: string, decimals: int}>}>
     */
    public function sidebarTallies(User $user, CarbonImmutable $now): array
    {
        $userId = (int) $user->id; // @pest-mutate-ignore
        $tallyIds = $this->cache->tallyIds($userId);

        return $this->cache->tallySummaries($userId, $tallyIds, $now->startOfMonth(), $now, $now->format('Y-m'))
            ->map(fn (array $summary): array => [
                'uuid' => $summary['uuid'],
                'name' => $summary['name'],
                'color' => $summary['color'],
                'glyph' => $summary['glyph'],
                'monthly_totals' => $summary['monthly_totals'],
            ])
            ->all();
    }

    /**
     * The user's primary currency (their default currency, else the most used one): what a tally
     * with no spending takes its "0.00" from, since there is no transaction to take a currency from (FP-14).
     *
     * @return array{symbol: string, code: string, decimals: int}
     */
    public function primaryCurrency(User $user): array
    {
        $userId = (int) $user->id; // @pest-mutate-ignore
        $currency = $this->cache->primaryCurrency($userId, $this->cache->tallyIds($userId), $user->default_currency_id);

        return ['symbol' => $currency['symbol'], 'code' => $currency['code'], 'decimals' => $currency['decimals']];
    }

    /**
     * The user's balance per currency in each accessible tally, keyed by tally uuid (Tallies index).
     *
     * @return array<string, list<MyBalance>>
     */
    public function myBalances(User $user): array
    {
        $userId = (int) $user->id; // @pest-mutate-ignore

        return $this->cache->balanceSummary($userId)['my_balances'];
    }

    /**
     * Token that changes whenever the user's tallies or transactions do.
     */
    public function sidebarVersion(User $user): string
    {
        return $this->cache->sidebarVersion((int) $user->id);
    }

    /**
     * Forget all home cache keys for the given user.
     */
    public function forgetCacheForUser(int $userId): void
    {
        $this->cache->forgetForUser($userId);
    }

    /**
     * Forget the home cache of every member of the tally: the owner and all joined users.
     */
    public function forgetCacheForTallyMembers(Tally $tally): void
    {
        /** @var list<int> $memberIds */
        $memberIds = $tally->users()->pluck('user_id')->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0)->all();
        $memberIds[] = (int) $tally->owner_id;

        foreach (array_unique($memberIds) as $memberId) {
            $this->forgetCacheForUser($memberId);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(User $user, CarbonImmutable $now): array
    {
        return [
            'user_name' => $user->name,
            'current_month_label' => $now->format('F Y'),
            'current_month_start' => $now->startOfMonth()->toDateString(),
            'primary_tally' => null,
            'tallies' => [],
            'settle_suggestion' => null,
            'balance' => [
                'net' => 0.0,
                'income' => 0.0,
                'expense' => 0.0,
                'currency_symbol' => '€',
                'currency_code' => 'EUR',
                'decimals' => 2,
                'other_currencies' => [],
                'previous_net' => 0.0,
                'previous_period' => null,
                'delta_percentage' => null,
            ],
            'recent_transactions' => [],
            'weekly_history' => [],
            'categories_breakdown' => [],
            'categories_other_currencies' => [],
        ];
    }

    /**
     * Add the previous period's net and the month-over-month delta to the balance. The current
     * period is the month to date; the previous one is the same span of days of the previous month
     * (clamped when that month is shorter). Both are in the primary currency (O-04). The delta is
     * `PeriodComparison::deltaPercentage()`: its sign is the arrow direction, null when the previous
     * net is 0. `previous_period` names the compared span (`Y-m-d` start and end) for the pill caption.
     *
     * @param  array{net: float, income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int, other_currencies: array<int, array{amount: float, currency_symbol: string, currency_code: string, decimals: int}>}  $balance
     * @return array<string, mixed>
     */
    private function withMonthOverMonthDelta(array $balance, float $previousNet, CarbonImmutable $previousPeriodStart, CarbonImmutable $previousPeriodEnd): array
    {
        $currentNet = $balance['net'];

        return [
            ...$balance,
            'previous_net' => $previousNet,
            'previous_period' => [
                'start' => $previousPeriodStart->toDateString(),
                'end' => $previousPeriodEnd->toDateString(),
            ],
            'delta_percentage' => PeriodComparison::deltaPercentage($currentNet, $previousNet),
        ];
    }
}
