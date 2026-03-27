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

it('denies unauthorized request', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

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

    expect($request->authorize())->toBeFalse();
});

it('denies guest request', function () {
    $tally = Tally::factory()->create();

    $request = new StoreTransactionRequest();
    $request->setUserResolver(fn () => null);
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->authorize())->toBeFalse();
});

it('has validation rules', function () {
    $request = new StoreTransactionRequest();
    expect($request->rules())->toBe([
        'party_id' => 'required|exists:users,id',
        'category_id' => 'required|exists:categories,category_id',
        'currency_id' => 'required|exists:currencies,currency_id',
        'type' => 'required|in:income,expense',
        'title' => 'required|string',
        'description' => 'nullable|string',
        'amount' => 'required|numeric',
        'transaction_date' => 'required|date',
    ]);
});

it('normalizes numeric amount before validation', function () {
    $request = new StoreTransactionRequest();
    $request->merge(['amount' => '12,34']);

    $method = new ReflectionMethod(StoreTransactionRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('amount'))->toBe(12.34);
});

it('does not normalize amount when value is non-numeric', function () {
    $request = new StoreTransactionRequest();
    $request->merge(['amount' => 'not-a-number']);

    $method = new ReflectionMethod(StoreTransactionRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('amount'))->toBe('not-a-number');
});

it('keeps payload unchanged when amount is missing', function () {
    $request = new StoreTransactionRequest();
    $request->merge(['title' => 'No amount']);

    $method = new ReflectionMethod(StoreTransactionRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->has('amount'))->toBeFalse();
    expect($request->input('title'))->toBe('No amount');
});
