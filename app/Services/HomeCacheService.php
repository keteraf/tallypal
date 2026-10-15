<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Transaction\GetBalanceSummaryAction;
use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @phpstan-import-type CurrencyShape from TransactionStatsRepositoryInterface
 * @phpstan-import-type CurrencyTotalShape from TransactionStatsRepositoryInterface
 * @phpstan-import-type BalanceSummary from GetBalanceSummaryAction
 *
 * @phpstan-type BalanceShape array{net: float, income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int, other_currencies: array<int, CurrencyTotalShape>}
 */
final readonly class HomeCacheService
{
    /**
     * Version segment of every Home cache key. Bump it whenever the shape of a cached value
     * changes: entries written by the previous release are then never read again (they expire on
     * their own TTL), so a deploy never needs a `cache:clear`.
     */
    public const string KEY_VERSION = 'v13';

    public function __construct(
        private TallyRepositoryInterface $tallyRepository,
        private TransactionStatsRepositoryInterface $statsRepository,
        private GetBalanceSummaryAction $balanceSummaryAction,
    ) {}

    /**
     * The versioned cache key of one Home value of the given user.
     */
    public static function key(int $userId, string $name): string
    {
        return 'home:' . self::KEY_VERSION . ":user:{$userId}:{$name}";
    }

    /**
     * @return Collection<int, int>
     */
    public function tallyIds(int $userId): Collection
    {
        /** @var array<int, int> $ids */
        $ids = Cache::remember(
            self::key($userId, 'tally_ids'),
            now()->addMinutes(10), // @pest-mutate-ignore
            fn (): array => $this->tallyRepository->accessibleTallyIds($userId)->all(),
        );

        return collect($ids);
    }

    /**
     * The user's default currency (B-10) when set, otherwise their most-used currency.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return CurrencyShape
     */
    public function primaryCurrency(int $userId, Collection $tallyIds, ?int $defaultCurrencyId): array
    {
        /** @var CurrencyShape */
        return Cache::remember(
            self::key($userId, 'primary_currency'),
            now()->addMinutes(30), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->primaryCurrency($tallyIds, $defaultCurrencyId),
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{uuid: string, name: string, color: string|null, glyph: string|null, member_count: int, members_preview: array<int, array{initials: string, color: string}>, split_label: string|null, monthly_totals: array<int, array{amount: float, currency_symbol: string, currency_code: string, decimals: int}>}>
     */
    public function tallySummaries(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, string $monthKey): Collection
    {
        /** @var array<int, array{uuid: string, name: string, color: string|null, glyph: string|null, member_count: int, members_preview: array<int, array{initials: string, color: string}>, split_label: string|null, monthly_totals: array<int, array{amount: float, currency_symbol: string, currency_code: string, decimals: int}>}> $summaries */
        $summaries = Cache::remember(
            self::key($userId, "tallies_summary:{$monthKey}"),
            now()->addMinutes(5), // @pest-mutate-ignore
            fn (): array => $this->tallyRepository->tallySummaries($userId, $tallyIds, $monthStart, $now)->all(),
        );

        return collect($summaries);
    }

    /**
     * The month's balance in the primary currency, plus the net of every other currency with
     * activity in the month (never added to it).
     *
     * @param  Collection<int, int>  $tallyIds
     * @param  CurrencyShape  $primaryCurrency
     * @return BalanceShape
     */
    public function monthlyBalance(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, array $primaryCurrency, string $dayKey): array
    {
        /** @var BalanceShape */
        return Cache::remember(
            self::key($userId, "balance:{$dayKey}"),
            now()->addMinutes(5), // @pest-mutate-ignore
            fn (): array => [
                ...$this->statsRepository->monthlyBalance($tallyIds, $monthStart, $now, $primaryCurrency),
                'other_currencies' => $this->statsRepository->otherCurrencyNets($tallyIds, $monthStart, $now, $primaryCurrency['currency_id']),
            ],
        );
    }

    /**
     * The net of the previous period (the same span of days of the previous month) in the primary
     * currency (B-03), the base of the month-over-month delta. Cached per day: the span grows daily.
     *
     * @param  Collection<int, int>  $tallyIds
     * @param  CurrencyShape  $primaryCurrency
     */
    public function previousMonthNet(int $userId, Collection $tallyIds, CarbonImmutable $previousMonthStart, CarbonImmutable $previousMonthEnd, array $primaryCurrency, string $dayKey): float
    {
        /** @var float */
        return Cache::remember(
            self::key($userId, "previous_net:{$dayKey}"),
            now()->addMinutes(30), // @pest-mutate-ignore
            fn (): float => $this->statsRepository->monthlyBalance($tallyIds, $previousMonthStart, $previousMonthEnd, $primaryCurrency)['net'],
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_id: int|null, category_name: string|null, category_code: string, category_slug: string|null, category_color: string|null, tally_name: string, currency_symbol: string}>
     */
    public function recentTransactions(int $userId, Collection $tallyIds): array
    {
        /** @var array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_id: int|null, category_name: string|null, category_code: string, category_slug: string|null, category_color: string|null, tally_name: string, currency_symbol: string}> */
        return Cache::remember(
            self::key($userId, 'recent_tx'),
            now()->addMinutes(2), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->recentTransactions($tallyIds),
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function weeklyHistory(int $userId, Collection $tallyIds, CarbonImmutable $weekHistoryStart, CarbonImmutable $now, int $currencyId, string $dayKey): array
    {
        /** @var array<int, array{label: string, amount: float, is_current: bool}> */
        return Cache::remember(
            self::key($userId, "weekly_history:{$dayKey}"),
            now()->addMinutes(10), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->weeklyHistory($tallyIds, $weekHistoryStart, $now, $currencyId),
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{name: string, amount: float, percentage: float, bar_fill: float, is_other: bool}>
     */
    public function categoriesBreakdown(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, int $currencyId, string $monthKey): array
    {
        /** @var array<int, array{name: string, amount: float, percentage: float, bar_fill: float, is_other: bool}> */
        return Cache::remember(
            self::key($userId, "categories:{$monthKey}"),
            now()->addMinutes(5), // @pest-mutate-ignore
            fn (): array => CategoryBars::build($this->statsRepository->categoriesBreakdown($tallyIds, $monthStart, $now, $currencyId)),
        );
    }

    /**
     * The month's expenses of every currency other than the primary one, shown apart from the
     * By category card.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, CurrencyTotalShape>
     */
    public function otherCurrencyExpenses(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, int $primaryCurrencyId, string $monthKey): array
    {
        /** @var array<int, CurrencyTotalShape> */
        return Cache::remember(
            self::key($userId, "other_currency_expenses:{$monthKey}"),
            now()->addMinutes(5), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->otherCurrencyExpenses($tallyIds, $monthStart, $now, $primaryCurrencyId),
        );
    }

    /**
     * The user's open balances in every tally they can view and the debts owed to them (B-04). It
     * always covers all of the user's tallies (the `balances` key is not scoped), so a caller that
     * needs fewer filters the result. A transaction or a share change in one of their tallies forgets
     * it for every member.
     *
     * @return BalanceSummary
     */
    public function balanceSummary(int $userId): array
    {
        /** @var BalanceSummary */
        return Cache::remember(
            self::key($userId, 'balances'),
            now()->addMinutes(10), // @pest-mutate-ignore
            fn (): array => $this->balanceSummaryAction->handle($userId, $this->tallyIds($userId)),
        );
    }

    /**
     * Token naming the current state of the user's sidebar tallies. `forgetForUser()` drops it, so
     * the next read issues a new one: the Inertia once prop is keyed by it and the client refetches.
     */
    public function sidebarVersion(int $userId): string
    {
        /** @var string */
        return Cache::rememberForever(
            self::key($userId, 'sidebar_version'),
            fn (): string => Str::random(12),
        );
    }

    public function forgetForUser(int $userId): void
    {
        $now = CarbonImmutable::now();
        $monthKey = $now->format('Y-m');
        $dayKey = $now->format('Y-m-d');

        Cache::forget(self::key($userId, 'sidebar_version'));
        Cache::forget(self::key($userId, 'tally_ids'));
        Cache::forget(self::key($userId, 'primary_currency'));
        Cache::forget(self::key($userId, "tallies_summary:{$monthKey}"));
        Cache::forget(self::key($userId, "balance:{$dayKey}"));
        Cache::forget(self::key($userId, "previous_net:{$dayKey}"));
        Cache::forget(self::key($userId, 'recent_tx'));
        Cache::forget(self::key($userId, "weekly_history:{$dayKey}"));
        Cache::forget(self::key($userId, "categories:{$monthKey}"));
        Cache::forget(self::key($userId, "other_currency_expenses:{$monthKey}"));
        Cache::forget(self::key($userId, 'balances'));
    }
}
