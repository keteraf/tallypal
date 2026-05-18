<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use Carbon\CarbonImmutable;
use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @phpstan-type CurrencyShape array{symbol: string, code: string, decimals: int, currency_id: int}
 */
final readonly class HomeCacheService
{
    public function __construct(
        private TallyRepositoryInterface $tallyRepository,
        private TransactionStatsRepositoryInterface $statsRepository,
    ) {}

    /**
     * @return Collection<int, int>
     */
    public function tallyIds(int $userId): Collection
    {
        /** @var array<int, int> $ids */
        $ids = Cache::remember(
            "home:user:{$userId}:tally_ids",
            now()->addMinutes(10), // @pest-mutate-ignore
            fn (): array => $this->tallyRepository->accessibleTallyIds($userId)->all(),
        );

        return collect($ids);
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return CurrencyShape
     */
    public function primaryCurrency(int $userId, Collection $tallyIds): array
    {
        /** @var CurrencyShape */
        return Cache::remember(
            "home:user:{$userId}:primary_currency",
            now()->addMinutes(30), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->primaryCurrency($tallyIds),
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{uuid: string, name: string, member_count: int, monthly_total: float}>
     */
    public function tallySummaries(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, string $monthKey): Collection
    {
        /** @var array<int, array{uuid: string, name: string, member_count: int, monthly_total: float}> $summaries */
        $summaries = Cache::remember(
            "home:user:{$userId}:tallies_summary:{$monthKey}",
            now()->addMinutes(5), // @pest-mutate-ignore
            fn (): array => $this->tallyRepository->tallySummaries($userId, $tallyIds, $monthStart)->all(),
        );

        return collect($summaries);
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @param  CurrencyShape  $primaryCurrency
     * @return array{net: float, income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int}
     */
    public function monthlyBalance(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, array $primaryCurrency, string $dayKey): array
    {
        /** @var array{net: float, income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int} */
        return Cache::remember(
            "home:user:{$userId}:balance:{$dayKey}",
            now()->addMinutes(5), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->monthlyBalance($tallyIds, $monthStart, $now, $primaryCurrency),
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_name: string|null, category_code: string, tally_name: string, currency_symbol: string}>
     */
    public function recentTransactions(int $userId, Collection $tallyIds): array
    {
        /** @var array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_name: string|null, category_code: string, tally_name: string, currency_symbol: string}> */
        return Cache::remember(
            "home:user:{$userId}:recent_tx",
            now()->addMinutes(2), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->recentTransactions($tallyIds),
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function weeklyHistory(int $userId, Collection $tallyIds, CarbonImmutable $weekHistoryStart, CarbonImmutable $now, string $dayKey): array
    {
        /** @var array<int, array{label: string, amount: float, is_current: bool}> */
        return Cache::remember(
            "home:user:{$userId}:weekly_history:{$dayKey}",
            now()->addMinutes(10), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->weeklyHistory($tallyIds, $weekHistoryStart, $now),
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{name: string, amount: float, percentage: float}>
     */
    public function categoriesBreakdown(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, string $monthKey): array
    {
        /** @var array<int, array{name: string, amount: float, percentage: float}> */
        return Cache::remember(
            "home:user:{$userId}:categories:{$monthKey}",
            now()->addMinutes(5), // @pest-mutate-ignore
            fn (): array => $this->statsRepository->categoriesBreakdown($tallyIds, $monthStart, $now),
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array{uuid: string, name: string, total_spent: float, members: array<int, array{name: string, initials: string, amount: float, percentage: float}>}|null
     */
    public function sharedTally(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, string $monthKey): ?array
    {
        /** @var array{uuid: string, name: string, total_spent: float, members: array<int, array{name: string, initials: string, amount: float, percentage: float}>}|null */
        return $this->rememberNullable(
            "home:user:{$userId}:shared_tally:{$monthKey}",
            now()->addMinutes(5), // @pest-mutate-ignore
            fn (): ?array => $this->tallyRepository->sharedTallySummary($tallyIds, $monthStart, $now),
        );
    }

    public function forgetForUser(int $userId): void
    {
        $now = CarbonImmutable::now();
        $monthKey = $now->format('Y-m');
        $dayKey = $now->format('Y-m-d');

        Cache::forget("home:user:{$userId}:tally_ids");
        Cache::forget("home:user:{$userId}:primary_currency");
        Cache::forget("home:user:{$userId}:tallies_summary:{$monthKey}");
        Cache::forget("home:user:{$userId}:balance:{$dayKey}");
        Cache::forget("home:user:{$userId}:recent_tx");
        Cache::forget("home:user:{$userId}:weekly_history:{$dayKey}");
        Cache::forget("home:user:{$userId}:categories:{$monthKey}");
        Cache::forget("home:user:{$userId}:shared_tally:{$monthKey}");
    }

    /**
     * Cache::remember does not cache null values, so we use a sentinel string.
     *
     * @param  Closure(): mixed  $callback
     */
    private function rememberNullable(string $key, DateTimeInterface|DateInterval|int $ttl, Closure $callback): mixed
    {
        $sentinel = '__NULL__';

        $cached = Cache::remember($key, $ttl, function () use ($callback, $sentinel): mixed {
            $result = $callback();

            return $result ?? $sentinel;
        });

        return $cached === $sentinel ? null : $cached;
    }
}
