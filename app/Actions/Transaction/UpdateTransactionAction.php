<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\DTOs\TransactionDTO;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateTransactionAction
{
    public function handle(Transaction $transaction, TransactionDTO $transactionDTO): Transaction
    {
        try {
            DB::transaction(function () use ($transaction, $transactionDTO): void {
                $transaction->update($transactionDTO->toArray());
            });

            return $transaction->refresh();
        } catch (Throwable $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }
}
