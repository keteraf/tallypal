<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\DTOs\TransactionDTO;
use App\Models\Tally;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class StoreTransactionAction
{
    public function handle(TransactionDTO $transactionDTO, Tally $tally): Transaction
    {
        try {
            return DB::transaction(function () use ($transactionDTO, $tally): Transaction {
                /** @var Transaction $transaction */
                $transaction = $tally->transactions()->create($transactionDTO->toArray());

                return $transaction;
            });
        } catch (Throwable $e) {
            report($e);
            abort(500);
        }
    }
}
