<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Every aggregate here is computed in one currency: amounts in different currencies are never
 * added together and never converted (O-04).
 *
 * @phpstan-import-type CurrencyShape from TransactionStatsRepositoryInterface
 * @phpstan-import-type CurrencyTotalShape from TransactionStatsRepositoryInterface
 * @phpstan-import-type ActivityShape from TransactionStatsRepositoryInterface
 */
final class TransactionStatsRepository implements TransactionStatsRepositoryInterface
{
    /**
     * @param  Collection<int, int>  $tallyIds
     * @return CurrencyShape
     */
    public function primaryCurrency(Collection $tallyIds, ?int $preferredCurrencyId = null): array
    {
        $preferred = $preferredCurrencyId === null
            ? null
            : DB::table(Currency::TABLE_NAME)
                ->select('currency_id', 'symbol', 'code', 'decimals')
                ->where('currency_id', $preferredCurrencyId)
                ->first();

        /** @var object{currency_id: int|string, symbol: string, code: string, decimals: int|string}|null $row */
        $row = $preferred ?? DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->select('currencies.currency_id', 'currencies.symbol', 'currencies.code', 'currencies.decimals', DB::raw('COUNT(*) as cnt'))
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->groupBy('currencies.currency_id', 'currencies.symbol', 'currencies.code', 'currencies.decimals')
            ->orderByDesc('cnt')
            ->first();

        if ($row === null) {
            return ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 0];
        }

        return [
            'symbol' => $row->symbol,
            'code' => $row->code,
            'decimals' => (int) $row->decimals,
            'currency_id' => (int) $row->currency_id,
        ];
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @param  CurrencyShape  $primaryCurrency
     * @return array{net: float, income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int}
     */
    public function monthlyBalance(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, array $primaryCurrency): array
    {
        /** @var object{income: float|int|string, expense: float|int|string}|null $row */
        $row = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN transactions.type = ? THEN ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' ELSE 0 END), 0) as income', [TransactionType::INCOME->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN transactions.type = ? THEN ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' ELSE 0 END), 0) as expense', [TransactionType::EXPENSE->value])
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.currency_id', $primaryCurrency['currency_id'])
            ->where('transactions.transaction_date', '>=', $monthStart->toDateString())
            ->where('transactions.transaction_date', '<=', $now->toDateString())
            ->first();

        $income = (float) ($row->income ?? 0);
        $expense = (float) ($row->expense ?? 0);

        return [
            'net' => $income - $expense,
            'income' => $income,
            'expense' => $expense,
            'currency_symbol' => $primaryCurrency['symbol'],
            'currency_code' => $primaryCurrency['code'],
            'decimals' => $primaryCurrency['decimals'],
        ];
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_id: int|null, category_name: string|null, category_code: string, category_slug: string|null, category_color: string|null, tally_name: string, is_shared: bool, currency_symbol: string}>
     */
    public function recentTransactions(Collection $tallyIds): array
    {
        return Transaction::query()
            ->whereIn('tally_id', $tallyIds->all())
            ->with(['currency', 'tally' => function (Relation $tally): void {
                /** @var BelongsTo<Tally, Transaction> $tally */
                $tally->withMembersCount();
            }])
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->select('transactions.*', 'categories.name as category_name', 'categories.slug as category_slug', 'categories.color as category_color')
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(7)
            ->get()
            ->map(function (Transaction $tx): array {
                /** @var string|null $rawCategoryName */
                $rawCategoryName = $tx->getAttribute('category_name');
                $currency = $tx->currency;
                /** @var int|string $usersCount */
                $usersCount = $tx->tally?->getAttribute('users_count') ?? 1;

                return [
                    'uuid' => (string) $tx->uuid,
                    'title' => (string) $tx->title,
                    'type' => $tx->type->value,
                    'amount' => $tx->amount,
                    'transaction_date' => (string) $tx->transaction_date,
                    'category_id' => $tx->category_id,
                    'category_name' => $rawCategoryName,
                    'category_code' => $this->categoryCode($rawCategoryName),
                    'category_slug' => $this->nullableString($tx->getAttribute('category_slug')),
                    'category_color' => $this->nullableString($tx->getAttribute('category_color')),
                    'tally_name' => $tx->tally === null ? '' : (string) $tx->tally->name,
                    'is_shared' => (int) $usersCount > 1,
                    'currency_symbol' => $currency === null ? '' : (string) $currency->symbol,
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, ActivityShape>
     */
    public function activity(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $end, int $limit): array
    {
        return Transaction::query()
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.transaction_date', '>=', $start->toDateString())
            ->where('transactions.transaction_date', '<=', $end->toDateString())
            ->with(['currency', 'tally' => function (Relation $tally): void {
                /** @var BelongsTo<Tally, Transaction> $tally */
                $tally->withMembersCount();
            }])
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->select('transactions.*', 'categories.name as category_name', 'categories.slug as category_slug', 'categories.color as category_color')
            ->orderByDesc('transactions.transaction_date')
            ->orderByDesc('transactions.created_at')
            ->limit($limit)
            ->get()
            ->map(function (Transaction $tx): array {
                /** @var string|null $rawCategoryName */
                $rawCategoryName = $tx->getAttribute('category_name');
                /** @var Tally $tally */
                $tally = $tx->tally;
                /** @var Currency $currency */
                $currency = $tx->currency;
                /** @var int|string $usersCount */
                $usersCount = $tally->getAttribute('users_count');

                return [
                    'uuid' => (string) $tx->uuid,
                    'title' => (string) $tx->title,
                    'type' => $tx->type->value,
                    'amount' => $tx->amount,
                    'transaction_date' => (string) $tx->transaction_date,
                    'category_id' => $tx->category_id,
                    'category_name' => $rawCategoryName,
                    'category_code' => $this->categoryCode($rawCategoryName),
                    'category_slug' => $this->nullableString($tx->getAttribute('category_slug')),
                    'category_color' => $this->nullableString($tx->getAttribute('category_color')),
                    'tally_uuid' => (string) $tally->uuid,
                    'tally_name' => (string) $tally->name,
                    'is_shared' => (int) $usersCount > 1,
                    'currency_symbol' => (string) $currency->symbol,
                    'currency_code' => (string) $currency->code,
                    'decimals' => (int) $currency->decimals,
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function weeklyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now, int $currencyId): array
    {
        /** @var Collection<int, object{transaction_date: string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('transactions.transaction_date, ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' as total')
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.currency_id', $currencyId)
            ->where('transactions.transaction_date', '>=', $start->toDateString())
            ->get();

        $weekTotals = [];

        foreach ($rows as $row) {
            $key = CarbonImmutable::parse((string) $row->transaction_date)->format('oW');
            $rawTotal = $row->total;
            $value = is_numeric($rawTotal) ? (float) $rawTotal : 0.0;
            $weekTotals[$key] = ($weekTotals[$key] ?? 0.0) + $value;
        }

        $weeks = [];
        $cursor = $start;

        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('oW');
            $weeks[] = [
                'label' => 'W ' . $cursor->format('W'),
                'amount' => $weekTotals[$key] ?? 0.0,
                'is_current' => $cursor->isSameWeek($now),
            ];
            $cursor = $cursor->addWeek();
        }

        return $weeks;
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{name: string, amount: float, percentage: float}>
     */
    public function categoriesBreakdown(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, int $currencyId): array
    {
        /** @var Collection<int, object{category_name: string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->selectRaw('COALESCE(categories.name, ?) as category_name, COALESCE(SUM(' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . '), 0) as total', ['Uncategorised'])
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.currency_id', $currencyId)
            ->where('transactions.transaction_date', '>=', $monthStart->toDateString())
            ->where('transactions.transaction_date', '<=', $now->toDateString())
            ->groupBy('categories.category_id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        $rawTotal = $rows->sum('total');
        $totalSum = is_numeric($rawTotal) ? (float) $rawTotal : 0.0;

        return $rows->map(function (object $row) use ($totalSum): array {
            $amount = (float) $row->total;

            return [
                'name' => $row->category_name,
                'amount' => $amount,
                'percentage' => $totalSum > 0 ? round(($amount / $totalSum) * 100, 1) : 0.0,
            ];
        })->all();
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     */
    public function expenseTotal(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now, int $currencyId): float
    {
        /** @var object{total: float|int|string|null} $row */
        $row = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('COALESCE(SUM(' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . '), 0) as total')
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.currency_id', $currencyId)
            ->where('transactions.transaction_date', '>=', $start->toDateString())
            ->where('transactions.transaction_date', '<=', $now->toDateString())
            ->first();

        $rawTotal = $row->total;

        return is_numeric($rawTotal) ? (float) $rawTotal : 0.0;
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function dailyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now, int $currencyId): array
    {
        $end = $start->addDays(7);

        /** @var Collection<int, object{transaction_date: string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('transactions.transaction_date, ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' as total')
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.currency_id', $currencyId)
            ->where('transactions.transaction_date', '>=', $start->toDateString())
            ->where('transactions.transaction_date', '<', $end->toDateString())
            ->get();

        $dayTotals = [];

        foreach ($rows as $row) {
            $key = CarbonImmutable::parse((string) $row->transaction_date)->toDateString();
            $rawTotal = $row->total;
            $value = is_numeric($rawTotal) ? (float) $rawTotal : 0.0;
            $dayTotals[$key] = ($dayTotals[$key] ?? 0.0) + $value;
        }

        $days = [];
        $cursor = $start;

        for ($i = 0; $i < 7; $i++) {
            $days[] = [
                'label' => $cursor->format('D'),
                'amount' => $dayTotals[$cursor->toDateString()] ?? 0.0,
                'is_current' => $cursor->isSameDay($now),
            ];
            $cursor = $cursor->addDay();
        }

        return $days;
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function monthlyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now, int $currencyId): array
    {
        $end = $start->addMonths(12);

        /** @var Collection<int, object{transaction_date: string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('transactions.transaction_date, ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' as total')
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.currency_id', $currencyId)
            ->where('transactions.transaction_date', '>=', $start->toDateString())
            ->where('transactions.transaction_date', '<', $end->toDateString())
            ->get();

        $monthTotals = [];

        foreach ($rows as $row) {
            $key = CarbonImmutable::parse((string) $row->transaction_date)->format('Y-m');
            $rawTotal = $row->total;
            $value = is_numeric($rawTotal) ? (float) $rawTotal : 0.0;
            $monthTotals[$key] = ($monthTotals[$key] ?? 0.0) + $value;
        }

        $months = [];
        $cursor = $start;

        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('Y-m');
            $months[] = [
                'label' => $cursor->format('M'),
                'amount' => $monthTotals[$key] ?? 0.0,
                'is_current' => $cursor->isSameMonth($now),
            ];
            $cursor = $cursor->addMonth();
        }

        return $months;
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, CurrencyTotalShape>
     */
    public function otherCurrencyNets(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $end, int $excludedCurrencyId): array
    {
        $net = 'COALESCE(SUM(CASE WHEN transactions.type = ? THEN ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' ELSE -' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' END), 0)';

        return $this->otherCurrencyTotals(
            DB::table(Transaction::TABLE_NAME)->selectRaw($net . ' as total', [TransactionType::INCOME->value]),
            $tallyIds,
            $start,
            $end,
            $excludedCurrencyId,
        );
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, CurrencyTotalShape>
     */
    public function otherCurrencyExpenses(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $end, int $excludedCurrencyId): array
    {
        return $this->otherCurrencyTotals(
            DB::table(Transaction::TABLE_NAME)
                ->selectRaw('COALESCE(SUM(' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . '), 0) as total')
                ->where('transactions.type', TransactionType::EXPENSE->value),
            $tallyIds,
            $start,
            $end,
            $excludedCurrencyId,
        );
    }

    /**
     * One total per currency other than the excluded one with transactions in the range (inclusive),
     * most-used first.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, CurrencyTotalShape>
     */
    private function otherCurrencyTotals(Builder $query, Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $end, int $excludedCurrencyId): array
    {
        /** @var Collection<int, object{code: string, symbol: string, decimals: int|string, total: float|int|string}> $rows */
        $rows = $query
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->addSelect('currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.currency_id', '!=', $excludedCurrencyId)
            ->where('transactions.transaction_date', '>=', $start->toDateString())
            ->where('transactions.transaction_date', '<=', $end->toDateString())
            ->groupBy('currencies.currency_id', 'currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->orderByRaw('COUNT(*) DESC')
            ->orderBy('currencies.code')
            ->get();

        return $rows
            ->map(fn (object $row): array => [
                'amount' => (float) $row->total,
                'currency_symbol' => $row->symbol,
                'currency_code' => $row->code,
                'decimals' => (int) $row->decimals,
            ])
            ->values()
            ->all();
    }

    /**
     * A joined column that is null for a transaction with no category.
     */
    private function nullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private function categoryCode(?string $name): string
    {
        if ($name === null) { // @pest-mutate-ignore
            return '··'; // @pest-mutate-ignore
        }

        $cleaned = preg_replace('/[^A-Za-z]/', '', $name) ?? '';

        return $cleaned === ''
            ? '··'
            : mb_strtoupper(mb_substr($cleaned, 0, 2));
    }
}
