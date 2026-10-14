<?php

declare(strict_types=1);

use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Rules\CategoryMatchesType;
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
    $tally = Tally::factory()->create();
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);

    $request = new UpdateTransactionRequest();
    $request->setRouteResolver(fn () => new class($tally, $transaction)
    {
        public function __construct(private readonly Tally $tally, private readonly Transaction $transaction) {}

        public function parameter(string $key): Tally|Transaction
        {
            return match ($key) {
                'transaction' => $this->transaction,
                default => $this->tally,
            };
        }
    });

    $rules = $request->rules();

    expect($rules['party_id'])->toBeArray()
        ->and($rules['category_id'])->toHaveCount(3)
        ->and($rules['category_id'][0])->toBe('nullable')
        ->and($rules['category_id'][1])->toBe('exists:categories,category_id')
        ->and($rules['category_id'][2])->toBeInstanceOf(CategoryMatchesType::class)
        ->and($rules['currency_id'])->toBe('required|exists:currencies,currency_id')
        ->and($rules['type'])->toBe('required|in:income,expense')
        ->and($rules['title'])->toBe('required|string|max:25')
        ->and($rules['description'])->toBe('nullable|string')
        ->and($rules['amount'])->toBe('required|numeric')
        ->and($rules['transaction_date'])->toBe('required|date');
});

it('rejects party_id not belonging to the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);
    $outsider = User::factory()->create();
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $this->actingAs($owner)
        ->patch(route('tallies.transactions.update', [$tally, $transaction]), [
            'party_id' => $outsider->id,
            'category_id' => $category->category_id,
            'currency_id' => $currency->currency_id,
            'type' => 'expense',
            'title' => 'Test',
            'amount' => 10,
            'transaction_date' => now()->toDateString(),
        ])
        ->assertSessionHasErrors(['party_id']);
});

it('rejects title longer than 25 characters', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);
    $party = User::factory()->create();
    $tally->users()->attach($party->id, ['default_share_percentage' => 50]);
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $this->actingAs($owner)
        ->patch(route('tallies.transactions.update', [$tally, $transaction]), [
            'party_id' => $party->id,
            'category_id' => $category->category_id,
            'currency_id' => $currency->currency_id,
            'type' => 'expense',
            'title' => str_repeat('a', 26),
            'amount' => 10,
            'transaction_date' => now()->toDateString(),
        ])
        ->assertSessionHasErrors(['title']);
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
