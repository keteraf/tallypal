<?php

declare(strict_types=1);

use App\Actions\Transaction\DestroyTransactionAction;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

covers(DestroyTransactionAction::class);

it('deletes a transaction', function () {
    $transaction = Transaction::factory()->create();
    $transactionId = $transaction->transaction_id;

    $action = new DestroyTransactionAction();
    $action->handle($transaction);

    expect(Transaction::query()->find($transactionId))->toBeNull();
});

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $transaction = Transaction::factory()->create();
    $failure = databaseFailure();
    DB::partialMock()->shouldReceive('transaction')->once()->andThrow($failure);
    expect(fn () => (new DestroyTransactionAction())->handle($transaction))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});
