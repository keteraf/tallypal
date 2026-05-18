<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\TransactionStatsRepositoryInterface;
use App\Enums\TransactionType;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-type CurrencyShape array{symbol: string, code: string, decimals: int, currency_id: int}
 */
final class TransactionStatsRepository implements TransactionStatsRepositoryInterface
{
    /**
     * @param  Collection<int, int>  $tallyIds
     * @return CurrencyShape
     */
    public function primaryCurrency(Collection $tallyIds): array
    {
        /** @var object{currency_id: int|string, symbol: string, code: string, decimals: int|string}|null $row */
        $row = DB::table(Transaction::TABLE_NAME)
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
     * @return array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_name: string|null, category_code: string, tally_name: string, currency_symbol: string}>
     */
    public function recentTransactions(Collection $tallyIds): array
    {
        return Transaction::query()
            ->whereIn('tally_id', $tallyIds->all())
            ->with(['currency', 'tally'])
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->select('transactions.*', 'categories.name as category_name')
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(7)
            ->get()
            ->map(function (Transaction $tx): array {
                /** @var string|null $rawCategoryName */
                $rawCategoryName = $tx->getAttribute('category_name');
                $currency = $tx->currency;

                return [
                    'uuid' => (string) $tx->uuid,
                    'title' => (string) $tx->title,
                    'type' => $tx->type->value,
                    'amount' => $tx->amount,
                    'transaction_date' => (string) $tx->transaction_date,
                    'category_name' => $rawCategoryName,
                    'category_code' => $this->categoryCode($rawCategoryName),
                    'tally_name' => $tx->tally === null ? '' : (string) $tx->tally->name,
                    'currency_symbol' => $currency === null ? '' : (string) $currency->symbol,
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    public function weeklyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now): array
    {
        /** @var Collection<int, object{transaction_date: string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('transactions.transaction_date, ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' as total')
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
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
    public function categoriesBreakdown(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): array
    {
        /** @var Collection<int, object{category_name: string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->selectRaw('COALESCE(categories.name, ?) as category_name, COALESCE(SUM(' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . '), 0) as total', ['Uncategorised'])
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.transaction_date', '>=', $monthStart->toDateString())
            ->where('transactions.transaction_date', '<=', $now->toDateString())
            ->groupBy('categories.category_id', 'categories.name')
            ->orderByDesc('total')
            ->limit(6)
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
