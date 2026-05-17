<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * @phpstan-type CurrencyShape array{symbol: string, code: string, decimals: int, currency_id: int}
 */
interface TransactionStatsRepositoryInterface
{
    /**
     * Return the most-used currency across the given tallies.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return CurrencyShape
     */
    public function primaryCurrency(Collection $tallyIds): array;

    /**
     * Return monthly income/expense/net totals in the primary currency.
     *
     * @param  Collection<int, int>  $tallyIds
     * @param  CurrencyShape  $primaryCurrency
     * @return array{net: float, income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int}
     */
    public function monthlyBalance(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, array $primaryCurrency): array;

    /**
     * Return the 7 most recent transactions across the given tallies.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_name: string|null, category_code: string, tally_name: string, currency_symbol: string}>
     */
    public function recentTransactions(Collection $tallyIds): array;

    /**
     * Return weekly expense totals for the last 12 weeks.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function weeklyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now): array;

    /**
     * Return top-6 expense categories with percentages for the current month.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{name: string, amount: float, percentage: float}>
     */
    public function categoriesBreakdown(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): array;
}
