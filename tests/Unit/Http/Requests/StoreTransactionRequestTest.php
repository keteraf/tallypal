<?php

declare(strict_types=1);

use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\User;
use App\Rules\CategoryMatchesType;
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
    $tally = Tally::factory()->create();

    $request = new StoreTransactionRequest();
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private readonly Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
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
    $outsider = User::factory()->create();
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $this->actingAs($owner)
        ->post(route('tallies.transactions.store', $tally), [
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

it('accepts party_id belonging to the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $party = User::factory()->create();
    $tally->users()->attach($party->id, ['default_share_percentage' => 50]);
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $this->actingAs($owner)
        ->post(route('tallies.transactions.store', $tally), [
            'party_id' => $party->id,
            'category_id' => $category->category_id,
            'currency_id' => $currency->currency_id,
            'type' => 'expense',
            'title' => 'Test',
            'amount' => 10,
            'transaction_date' => now()->toDateString(),
        ])
        ->assertSessionDoesntHaveErrors(['party_id']);
});

it('rejects title longer than 25 characters', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $party = User::factory()->create();
    $tally->users()->attach($party->id, ['default_share_percentage' => 50]);
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $this->actingAs($owner)
        ->post(route('tallies.transactions.store', $tally), [
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

it('requires a party and still builds its rules when the route has no tally', function () {
    $request = new StoreTransactionRequest();
    $request->setRouteResolver(fn () => new class
    {
        public function parameter(string $key): null
        {
            return null;
        }
    });

    $rules = $request->rules();

    expect($rules['party_id'][0])->toBe('required')
        ->and($rules['party_id'])->toHaveCount(2);
});
