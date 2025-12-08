<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\DTOs\TransactionDTO;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateTransactionAction
{
    /**
     * @throws Throwable
     */
    public function handle(Transaction $transaction, TransactionDTO $transactionDTO): Transaction
    {
        DB::transaction(function () use ($transaction, $transactionDTO): void {
            $transaction->update($transactionDTO->toArray());
        });

        return $transaction->refresh();
    }
}
