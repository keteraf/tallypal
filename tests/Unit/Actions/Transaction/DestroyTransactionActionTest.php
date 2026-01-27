<?php

declare(strict_types=1);

use App\Actions\Transaction\DestroyTransactionAction;
use App\Models\Transaction;

covers(DestroyTransactionAction::class);

it('deletes a transaction', function () {
    $transaction = Transaction::factory()->create();
    $transactionId = $transaction->transaction_id;

    $action = new DestroyTransactionAction();
    $action->handle($transaction);

    expect(Transaction::query()->find($transactionId))->toBeNull();
});
