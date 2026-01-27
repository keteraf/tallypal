<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

it('requires authentication for all transaction endpoints', function (): void {
    $tally = Tally::factory()->create();
    $transaction = Transaction::factory()->forTally($tally)->create();

    $this->getJson(route('tallies.transactions.index', $tally))->assertUnauthorized();
    $this->postJson(route('tallies.transactions.store', $tally), [])->assertUnauthorized();
    $this->getJson(route('tallies.transactions.show', [$tally, $transaction]))->assertUnauthorized();
    $this->patchJson(route('tallies.transactions.update', [$tally, $transaction]), [])->assertUnauthorized();
    $this->deleteJson(route('tallies.transactions.destroy', [$tally, $transaction]))->assertUnauthorized();
});

it('lists transactions for a tally, newest first, paginated', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    // Create 12 so pagination (per 10) applies
    Transaction::factory()->count(12)->forTally($tally)->create();
    // Transactions belonging to a different tally should not appear
    Transaction::factory()->count(3)->create();

    $response = $this->getJson(route('tallies.transactions.index', $tally));

    $response->assertSuccessful();

    // Ensure pagination (10 per page) and structure of resource
    $response->assertJson(fn ($json) => $json
        ->has('data', 10)
        ->has('links')
        ->has('meta')
        ->has('data.0', fn ($item) => $item
            ->hasAll([
                'id', 'tally_id', 'party_id', 'currency_id', 'category_id', 'type', 'title', 'description',
                'amount', 'transaction_date', 'created_at', 'updated_at',
            ])
        )
    );
});

it('stores a transaction with valid payload', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $party = User::factory()->create();
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $payload = [
        'party_id' => $party->id,
        'currency_id' => $currency->getKey(),
        'category_id' => $category->getKey(),
        'type' => TransactionType::EXPENSE->value,
        'title' => 'Grocery Shopping',
        'description' => 'Weekly groceries',
        'amount' => 123.45,
        'transaction_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postJson(route('tallies.transactions.store', $tally), $payload);

    $response->assertSuccessful();

    // Assert response structure (resources are wrapped in "data")
    $response->assertJson(fn ($json) => $json
        ->has('data', fn ($item) => $item->hasAll([
            'id', 'tally_id', 'party_id', 'currency_id', 'category_id', 'type', 'title', 'description',
            'amount', 'transaction_date', 'created_at', 'updated_at',
        ]))
    );

    $this->assertDatabaseHas(Transaction::TABLE_NAME, [
        'tally_id' => $tally->getKey(),
        'party_id' => $party->getKey(),
        'currency_id' => $currency->getKey(),
        'category_id' => $category->getKey(),
        'type' => TransactionType::EXPENSE->value,
        'title' => 'Grocery Shopping',
    ]);
});

it('validates store payload - missing party_id', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $payload = [
        'currency_id' => $currency->getKey(),
        'category_id' => $category->getKey(),
        'type' => TransactionType::INCOME->value,
        'title' => 'x',
        'amount' => 10,
        'transaction_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postJson(route('tallies.transactions.store', $tally), $payload);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['party_id']);
});

it('validates store payload - invalid type', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $party = User::factory()->create();
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $payload = [
        'party_id' => $party->id,
        'currency_id' => $currency->getKey(),
        'category_id' => $category->getKey(),
        'type' => 'other',
        'title' => 'x',
        'amount' => 10,
        'transaction_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postJson(route('tallies.transactions.store', $tally), $payload);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['type']);
});

it('validates store payload - amount not numeric', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $party = User::factory()->create();
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $payload = [
        'party_id' => $party->id,
        'currency_id' => $currency->getKey(),
        'category_id' => $category->getKey(),
        'type' => TransactionType::INCOME->value,
        'title' => 'x',
        'amount' => 'abc',
        'transaction_date' => Carbon::now()->toDateString(),
    ];

    $response = $this->postJson(route('tallies.transactions.store', $tally), $payload);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['amount']);
});

it('validates store payload - missing transaction_date', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $party = User::factory()->create();
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $payload = [
        'party_id' => $party->id,
        'currency_id' => $currency->getKey(),
        'category_id' => $category->getKey(),
        'type' => TransactionType::INCOME->value,
        'title' => 'x',
        'amount' => 10,
    ];

    $response = $this->postJson(route('tallies.transactions.store', $tally), $payload);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['transaction_date']);
});

it('shows a single transaction', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $response = $this->getJson(route('tallies.transactions.show', [$tally, $transaction]));

    $response->assertSuccessful();
    $response->assertJson(fn ($json) => $json
        ->has('data', fn ($item) => $item
            ->where('id', $transaction->getKey())
            ->where('tally_id', $tally->getKey())
            ->hasAll([
                'party_id', 'currency_id', 'category_id', 'type', 'title', 'description', 'amount', 'transaction_date', 'created_at', 'updated_at',
            ])
            ->etc()
        )
    );
});

it('updates a transaction', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();
    $party = User::factory()->create();
    $currency = Currency::query()->firstOrFail();
    $category = Category::query()->firstOrFail();

    $payload = [
        'party_id' => $party->id,
        'currency_id' => $currency->getKey(),
        'category_id' => $category->getKey(),
        'type' => TransactionType::INCOME->value,
        'title' => 'Updated Title',
        'description' => 'Updated Desc',
        'amount' => 987.65,
        'transaction_date' => Carbon::now()->subDay()->toDateString(),
    ];

    $response = $this->patchJson(route('tallies.transactions.update', [$tally, $transaction]), $payload);

    $response->assertSuccessful();

    $transaction->refresh();
    expect($transaction->party_id)->toBe($party->id)
        ->and($transaction->currency_id)->toBe($currency->getKey())
        ->and($transaction->category_id)->toBe($category->getKey())
        ->and($transaction->type->value)->toBe(TransactionType::INCOME->value)
        ->and($transaction->title)->toBe('Updated Title')
        ->and($transaction->description)->toBe('Updated Desc')
        ->and($transaction->amount)->toBe(987.65);
});

it('deletes a transaction', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $response = $this->deleteJson(route('tallies.transactions.destroy', [$tally, $transaction]));

    $response->assertNoContent();
    $this->assertModelMissing($transaction);
});
