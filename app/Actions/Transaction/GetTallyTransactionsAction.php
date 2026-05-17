<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Tally;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Throwable;

final readonly class GetTallyTransactionsAction
{
    /**
     * Get paginated transactions for a tally.
     *
     * @return LengthAwarePaginator<int, \App\Models\Transaction>
     */
    public function handle(Tally $tally, int $perPage = 15): LengthAwarePaginator
    {
        try {
            return $tally->transactions()
                ->with(['party', 'currency', 'category'])
                ->latest()
                ->paginate($perPage)
                ->withQueryString();
        } catch (Throwable $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }
}
