<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Enums\TransactionType;
use App\Models\Tally;
use App\Models\Transaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class GetTallyPerPersonAction
{
    /**
     * Expense total divided by the tally's members, one entry per currency the tally has used
     * (most-used first). Currencies are never summed or converted.
     *
     * @return Collection<int, array{amount: float, currency_symbol: string, currency_code: string, decimals: int}>
     */
    public function handle(Tally $tally): Collection
    {
        $members = $tally->membersCount();

        /** @var Collection<int, object{code: string, symbol: string, decimals: int|string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->select('currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN transactions.type = ? THEN ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' ELSE 0 END), 0) as total',
                [TransactionType::EXPENSE->value],
            )
            ->where('transactions.tally_id', $tally->getKey())
            ->groupBy('currencies.currency_id', 'currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->orderByRaw('COUNT(*) DESC')
            ->orderBy('currencies.code')
            ->get();

        return $rows->map(function (object $row) use ($members): array {
            $decimals = (int) $row->decimals; // @pest-mutate-ignore
            $total = (float) $row->total; // @pest-mutate-ignore

            return [
                'amount' => round($total / $members, $decimals),
                'currency_symbol' => $row->symbol,
                'currency_code' => $row->code,
                'decimals' => $decimals,
            ];
        })->values();
    }
}
