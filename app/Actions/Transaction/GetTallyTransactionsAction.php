<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Tally;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class GetTallyTransactionsAction
{
    /**
     * Get paginated transactions for a tally.
     *
     * @return LengthAwarePaginator<int, \App\Models\Transaction>
     */
    public function handle(Tally $tally, int $perPage = 15): LengthAwarePaginator
    {
        return $tally->transactions()
            ->with(['party', 'currency', 'category'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
