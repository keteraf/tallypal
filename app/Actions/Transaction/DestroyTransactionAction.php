<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DestroyTransactionAction
{
    /**
     * @throws Throwable
     */
    public function handle(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $transaction->delete();
        });
    }
}
