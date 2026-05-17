<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Tally;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

interface TallyRepositoryInterface
{
    /**
     * Return all tallies accessible by the given user with income/expense totals.
     *
     * @return Collection<int, Tally>
     */
    public function accessibleTalliesWithTotals(int $userId): Collection;

    /**
     * Return all tally IDs accessible by the given user (owned + shared).
     *
     * @return Collection<int, int>
     */
    public function accessibleTallyIds(int $userId): Collection;

    /**
     * Return tally summaries ordered by most recent activity.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{uuid: string, name: string, member_count: int, monthly_total: float}>
     */
    public function tallySummaries(int $userId, Collection $tallyIds, CarbonImmutable $monthStart): Collection;

    /**
     * Return the shared tally with the most members plus per-user spending.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array{uuid: string, name: string, total_spent: float, members: array<int, array{name: string, initials: string, amount: float, percentage: float}>}|null
     */
    public function sharedTallySummary(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): ?array;
}
