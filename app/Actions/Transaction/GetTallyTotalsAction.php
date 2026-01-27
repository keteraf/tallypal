<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Tally;
use Illuminate\Support\Collection;

final readonly class GetTallyTotalsAction
{
    /**
     * Get totals for a tally grouped by type and currency.
     *
     * @return Collection<int, array{type: string, currency_id: int, total: float}>
     */
    public function handle(Tally $tally): Collection
    {
        return $tally->transactions()
            ->with('currency')
            ->selectRaw('type, currency_id, SUM(amount) as raw_total')
            ->groupBy('type', 'currency_id')
            ->get()
            ->map(function ($item): array {
                $decimals = $item->currency?->decimals ?? 2;
                $factor = 10 ** $decimals;

                return [
                    'type' => $item->type->value,
                    'currency_id' => $item->currency_id,
                    'total' => $item->raw_total / $factor,
                    'currency' => $item->currency,
                ];
            });
    }
}
