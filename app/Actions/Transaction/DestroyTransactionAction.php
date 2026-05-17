<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DestroyTransactionAction
{
    public function handle(Transaction $transaction): void
    {
        try {
            DB::transaction(function () use ($transaction): void {
                $transaction->delete();
            });
        } catch (Throwable $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }
}
