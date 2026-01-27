<?php

declare(strict_types=1);

use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(UpdateTransactionRequest::class);

it('authorizes the request', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);

    $request = new UpdateTransactionRequest();
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

it('has validation rules', function () {
    $request = new UpdateTransactionRequest();
    $rules = $request->rules();

    expect($rules)->toHaveKey('party_id')
        ->and($rules)->toHaveKey('category_id')
        ->and($rules)->toHaveKey('currency_id')
        ->and($rules)->toHaveKey('type')
        ->and($rules)->toHaveKey('title')
        ->and($rules)->toHaveKey('description')
        ->and($rules)->toHaveKey('amount')
        ->and($rules)->toHaveKey('transaction_date');
});
