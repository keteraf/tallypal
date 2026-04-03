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

it('denies unauthorized request', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
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

    expect($request->authorize())->toBeFalse();
});

it('denies guest request', function () {
    $transaction = Transaction::factory()->create();

    $request = new UpdateTransactionRequest();
    $request->setUserResolver(fn () => null);
    $request->setRouteResolver(fn () => new class($transaction)
    {
        public function __construct(private Transaction $transaction) {}

        public function parameter(string $key): Transaction
        {
            return $this->transaction;
        }
    });

    expect($request->authorize())->toBeFalse();
});

it('has validation rules', function () {
    $request = new UpdateTransactionRequest();
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
    $request = new UpdateTransactionRequest();
    $request->merge(['amount' => '56,78']);

    $method = new ReflectionMethod(UpdateTransactionRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('amount'))->toBe(56.78);
});

it('does not normalize amount when value is non-numeric', function () {
    $request = new UpdateTransactionRequest();
    $request->merge(['amount' => 'NaN-text']);

    $method = new ReflectionMethod(UpdateTransactionRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('amount'))->toBe('NaN-text');
});

it('normalizes amount when it is a Stringable object', function () {
    $stringable = new class('12,34') implements Stringable
    {
        public function __construct(private string $val) {}

        public function __toString(): string
        {
            return $this->val;
        }
    };

    $request = new UpdateTransactionRequest();
    $request->merge(['amount' => $stringable]);

    $method = new ReflectionMethod(UpdateTransactionRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('amount'))->toBe(12.34);
});

it('does not normalize when amount is non-scalar and non-Stringable', function () {
    $request = new UpdateTransactionRequest();
    $request->merge(['amount' => ['not', 'scalar']]);

    $method = new ReflectionMethod(UpdateTransactionRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->input('amount'))->toBe(['not', 'scalar']);
});

it('keeps payload unchanged when amount is missing', function () {
    $request = new UpdateTransactionRequest();
    $request->merge(['title' => 'No amount']);

    $method = new ReflectionMethod(UpdateTransactionRequest::class, 'prepareForValidation');
    $method->setAccessible(true);
    $method->invoke($request);

    expect($request->has('amount'))->toBeFalse();
    expect($request->input('title'))->toBe('No amount');
});
