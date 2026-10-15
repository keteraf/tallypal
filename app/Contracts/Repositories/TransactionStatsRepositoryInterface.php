<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Enums\CategoryType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Aggregates are computed in a single currency; amounts in different currencies are never added
 * together and never converted (O-04).
 *
 * @phpstan-type CurrencyShape array{symbol: string, code: string, decimals: int, currency_id: int}
 * @phpstan-type CurrencyTotalShape array{amount: float, currency_symbol: string, currency_code: string, decimals: int}
 * @phpstan-type ActivityShape array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_id: int|null, category_name: string|null, category_code: string, category_slug: string|null, category_color: string|null, tally_uuid: string, tally_name: string, is_shared: bool, currency_symbol: string, currency_code: string, decimals: int}
 */
interface TransactionStatsRepositoryInterface
{
    /**
     * Return the user's primary currency: the preferred (default) currency when it exists,
     * otherwise the most-used currency across the given tallies.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return CurrencyShape
     */
    public function primaryCurrency(Collection $tallyIds, ?int $preferredCurrencyId = null): array;

    /**
     * Return monthly income/expense/net totals in the primary currency.
     *
     * @param  Collection<int, int>  $tallyIds
     * @param  CurrencyShape  $primaryCurrency
     * @return array{net: float, income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int}
     */
    public function monthlyBalance(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, array $primaryCurrency): array;

    /**
     * Return the latest `$limit` transactions (income and expenses) dated between the given dates
     * (inclusive), newest first, each in its own currency.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, ActivityShape>
     */
    public function activity(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $end, int $limit): array;

    /**
     * Return the 7 most recent transactions across the given tallies.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_id: int|null, category_name: string|null, category_code: string, category_slug: string|null, category_color: string|null, tally_name: string, currency_symbol: string}>
     */
    public function recentTransactions(Collection $tallyIds): array;

    /**
     * Return weekly expense totals in the given currency for the last 12 weeks.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function weeklyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now, int $currencyId): array;

    /**
     * Return every expense category in the given currency for the range, largest first, with its
     * percentage of the range's total. Callers fold the tail into "Other" with `CategoryBars`.
     *
     * Uncategorised spending is one row with a null slug and `is_uncategorised` set; its label is the caller's to translate.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{name: string, slug: string|null, is_uncategorised: bool, amount: float, percentage: float}>
     */
    public function categoriesBreakdown(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, int $currencyId): array;

    /**
     * Return total expenses in the given currency between the given dates (inclusive).
     *
     * @param  Collection<int, int>  $tallyIds
     */
    public function expenseTotal(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now, int $currencyId): float;

    /**
     * Return daily expense totals in the given currency for the 7 days starting at `$start`.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function dailyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now, int $currencyId): array;

    /**
     * Return monthly expense totals in the given currency for the 12 months starting at `$start`.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function monthlyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now, int $currencyId): array;

    /**
     * Return the net (income − expense) of every currency other than the excluded one that has
     * transactions between the given dates (inclusive), most-used first.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, CurrencyTotalShape>
     */
    public function otherCurrencyNets(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $end, int $excludedCurrencyId): array;

    /**
     * Return the expense total of every currency other than the excluded one that has expenses
     * between the given dates (inclusive), most-used first.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, CurrencyTotalShape>
     */
    public function otherCurrencyExpenses(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $end, int $excludedCurrencyId): array;

    /**
     * Return the slugs of the categories the user entered at least twice in the last 90 days for the type,
     * most used first then most recent, up to five, topped up with the type's default slugs. A transaction
     * counts for the user in `party_id` (the payer, or the receiver of an income), the only user column it has.
     *
     * @return list<string>
     */
    public function frequentCategories(int $userId, CategoryType $type, CarbonImmutable $now): array;
}
