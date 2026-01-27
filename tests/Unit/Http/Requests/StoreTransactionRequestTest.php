<?php

declare(strict_types=1);

use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(StoreTransactionRequest::class);

it('authorizes the request', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $request = new StoreTransactionRequest();
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->authorize())->toBeTrue();
});

it('has validation rules', function () {
    $request = new StoreTransactionRequest();
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
