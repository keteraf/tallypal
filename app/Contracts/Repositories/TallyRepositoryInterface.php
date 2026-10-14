<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\Tally;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * @phpstan-type MonthlyTotalShape array{amount: float, currency_symbol: string, currency_code: string, decimals: int}
 * @phpstan-type TotalShape array{income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int}
 */
interface TallyRepositoryInterface
{
    /**
     * Return all tallies accessible by the given user as Tallies-index rows: member data, activity
     * and income/expense totals kept per currency, most recently active first.
     *
     * @return Collection<int, array{tally_id: int, uuid: string, name: string, description: string, owner_id: int, color: string|null, glyph: string|null, users_count: int, is_shared: bool, transactions_count: int, last_activity_at: string, members_preview: array<int, array{initials: string, color: string}>, split_label: string|null, totals: array<int, TotalShape>}>
     */
    public function accessibleTalliesWithTotals(int $userId): Collection;

    /**
     * Return all tally IDs accessible by the given user (owned + shared).
     *
     * @return Collection<int, int>
     */
    public function accessibleTallyIds(int $userId): Collection;

    /**
     * Return the given tallies ordered by name, each flagged `is_shared` when it has more than one
     * member (`Tally::withMembersCount()`): the Stats tally filter options.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{tally_id: int, uuid: string, name: string, is_shared: bool}>
     */
    public function filterOptions(Collection $tallyIds): Collection;

    /**
     * Return tally summaries ordered by most recent activity, with the current-month expense
     * total kept per currency.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{uuid: string, name: string, color: string|null, glyph: string|null, member_count: int, members_preview: array<int, array{initials: string, color: string}>, split_label: string|null, monthly_totals: array<int, MonthlyTotalShape>}>
     */
    public function tallySummaries(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): Collection;
}
