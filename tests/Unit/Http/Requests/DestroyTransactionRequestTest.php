<?php

declare(strict_types=1);

use App\Http\Requests\Transactions\DestroyTransactionRequest;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(DestroyTransactionRequest::class);

it('authorizes the request', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);

    $request = new DestroyTransactionRequest();
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(fn () => new class($transaction)
    {
        public function __construct(private Transaction $transaction) {}

        public function parameter(string $key): Transaction
        {
            return $this->transaction;
        }
    });

    expect($request->authorize())->toBeTrue();
});

it('has empty validation rules', function () {
    $request = new DestroyTransactionRequest();
    expect($request->rules())->toBe([]);
});
