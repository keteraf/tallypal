<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;

final readonly class HomeService
{
    public function __construct(
        private HomeCacheService $cache,
    ) {}

    /**
     * Build the full Inertia payload for the Home page.
     *
     * @return array<string, mixed>
     */
    public function buildHomePayload(User $user, CarbonImmutable $now): array
    {
        $userId = (int) $user->id; // @pest-mutate-ignore
        $monthKey = $now->format('Y-m');
        $dayKey = $now->format('Y-m-d');
        $monthStart = $now->startOfMonth();

        $tallyIds = $this->cache->tallyIds($userId);

        if ($tallyIds->isEmpty()) {
            return $this->emptyPayload($user, $now);
        }

        $primaryCurrency = $this->cache->primaryCurrency($userId, $tallyIds);
        $tallySummaries = $this->cache->tallySummaries($userId, $tallyIds, $monthStart, $monthKey);
        $balance = $this->cache->monthlyBalance($userId, $tallyIds, $monthStart, $now, $primaryCurrency, $dayKey);
        $recentTransactions = $this->cache->recentTransactions($userId, $tallyIds);
        $weeklyHistory = $this->cache->weeklyHistory($userId, $tallyIds, $now->startOfWeek()->subWeeks(11), $now, $dayKey); // @pest-mutate-ignore
        $categoriesBreakdown = $this->cache->categoriesBreakdown($userId, $tallyIds, $monthStart, $now, $monthKey);
        $sharedTally = $this->cache->sharedTally($userId, $tallyIds, $monthStart, $now, $monthKey);

        /** @var array{uuid: string, name: string, member_count: int, monthly_total: float}|null $primaryTally */
        $primaryTally = $tallySummaries->first();

        return [
            'user_name' => $user->name,
            'current_month_label' => $now->format('F Y'),
            'primary_tally' => $primaryTally === null ? null : [
                'uuid' => $primaryTally['uuid'],
                'name' => $primaryTally['name'],
            ],
            'tallies' => $tallySummaries->all(),
            'balance' => $balance,
            'recent_transactions' => $recentTransactions,
            'weekly_history' => $weeklyHistory,
            'categories_breakdown' => $categoriesBreakdown,
            'shared_tally' => $sharedTally,
        ];
    }

    /**
     * Forget all home cache keys for the given user.
     */
    public function forgetCacheForUser(int $userId): void
    {
        $this->cache->forgetForUser($userId);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(User $user, CarbonImmutable $now): array
    {
        return [
            'user_name' => $user->name,
            'current_month_label' => $now->format('F Y'),
            'primary_tally' => null,
            'tallies' => [],
            'balance' => [
                'net' => 0.0,
                'income' => 0.0,
                'expense' => 0.0,
                'currency_symbol' => '€',
                'currency_code' => 'EUR',
                'decimals' => 2,
            ],
            'recent_transactions' => [],
            'weekly_history' => [],
            'categories_breakdown' => [],
            'shared_tally' => null,
        ];
    }
}
