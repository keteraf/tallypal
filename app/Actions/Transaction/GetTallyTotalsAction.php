<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Tally;
use Illuminate\Support\Collection;
use Throwable;

final readonly class GetTallyTotalsAction
{
    /**
     * Get totals for a tally grouped by type and currency.
     *
     * @return Collection<int, array{type: 'expense'|'income', currency_id: int, total: float, currency: array{code: string, symbol: string, decimals: int}|null}>
     */
    public function handle(Tally $tally): Collection
    {
        try {
            return $tally->transactions()
                ->with('currency')
                ->selectRaw('type, currency_id, SUM(amount) as raw_total')
                ->groupBy('type', 'currency_id')
                ->get()
                ->map(function (\App\Models\Transaction $item): array {
                    $decimals = $item->currency->decimals ?? 2;
                    $factor = 10 ** $decimals;

                    return [
                        'type' => $item->type->value,
                        'currency_id' => $item->currency_id,
                        'total' => (float) ($item->raw_total / $factor),
                        'currency' => $item->currency ? [
                            'code' => $item->currency->code,
                            'symbol' => $item->currency->symbol,
                            'decimals' => $item->currency->decimals,
                        ] : null,
                    ];
                });
        } catch (Throwable $e) {
            report($e);
            abort(500);
        }
    }
}
