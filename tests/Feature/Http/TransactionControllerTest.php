<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Http\Controllers\Api\TransactionController;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

covers(TransactionController::class);

it('requires authentication for all transaction endpoints', function (): void {
    $tally = Tally::factory()->create();
    $transaction = Transaction::factory()->forTally($tally)->create();

    $this->getJson(route('api.tallies.transactions.index', $tally))->assertUnauthorized();
    $this->postJson(route('api.tallies.transactions.store', $tally), [])->assertUnauthorized();
    $this->getJson(route('api.tallies.transactions.show', [$tally, $transaction]))->assertUnauthorized();
    $this->patchJson(route('api.tallies.transactions.update', [$tally, $transaction]), [])->assertUnauthorized();
    $this->deleteJson(route('api.tallies.transactions.destroy', [$tally, $transaction]))->assertUnauthorized();
});

it('lists transactions for a tally, newest first, paginated', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    // Create 12 so pagination (per 10) applies
    Transaction::factory()->count(12)->forTally($tally)->create();
    // Transactions belonging to a different tally should not appear
    Transaction::factory()->count(3)->create();

    $response = $this->getJson(route('api.tallies.transactions.index', $tally));

    $response->assertSuccessful();

    // Ensure pagination (10 per page) and structure of resource
    $response->assertJson(fn ($json) => $json
        ->has('data', 10)
        ->has('links')
        ->has('meta')
        ->where('meta.per_page', 10)
        ->has('data.0', fn ($item) => $item
            ->hasAll([
                'id', 'tally_id', 'party_id', 'currency_id', 'category_id', 'type', 'title', 'description',
                'amount', 'transaction_date', 'created_at', 'updated_at',
            ])
        )
    );
});

it('forbids listing transactions for a tally the user does not own or participate in', function (): void {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $other->id]);

    $response = $this->getJson(route('api.tallies.transactions.index', $tally));

    $response->assertForbidden();
});

it('stores a transaction with valid payload', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $party = User::factory()->create();
    $tally->users()->attach($party->id, ['default_share_percentage' => 50]);
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

    $response = $this->postJson(route('api.tallies.transactions.store', $tally), $payload);

    $response->assertSuccessful();

    $response->assertJson([
        'data' => [
            'party_id' => $party->id,
            'currency_id' => $currency->getKey(),
            'category_id' => $category->getKey(),
            'type' => TransactionType::EXPENSE->value,
            'title' => 'Grocery Shopping',
            'amount' => 123.45,
        ],
    ]);

    $this->assertDatabaseHas(Transaction::TABLE_NAME, [
        'tally_id' => $tally->getKey(),
        'party_id' => $party->getKey(),
        'currency_id' => $currency->getKey(),
        'category_id' => $category->getKey(),
        'type' => TransactionType::EXPENSE->value,
        'title' => 'Grocery Shopping',
    ]);
});

it('forbids storing a transaction in a tally the user does not own or participate in', function (): void {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $other->id]);

    $payload = [
        'party_id' => $me->id,
        'currency_id' => 1,
        'category_id' => 1,
        'type' => TransactionType::EXPENSE->value,
        'title' => 'Stealing Tally',
        'amount' => 100,
        'transaction_date' => now()->toDateString(),
    ];

    $response = $this->postJson(route('api.tallies.transactions.store', $tally), $payload);

    $response->assertForbidden();
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

    $response = $this->postJson(route('api.tallies.transactions.store', $tally), $payload);

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

    $response = $this->postJson(route('api.tallies.transactions.store', $tally), $payload);

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

    $response = $this->postJson(route('api.tallies.transactions.store', $tally), $payload);

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

    $response = $this->postJson(route('api.tallies.transactions.store', $tally), $payload);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['transaction_date']);
});

it('shows a single transaction', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $response = $this->getJson(route('api.tallies.transactions.show', [$tally, $transaction]));

    $response->assertSuccessful();
    $response->assertJson([
        'data' => [
            'id' => $transaction->getKey(),
            'tally_id' => $tally->getKey(),
            'party_id' => $transaction->party_id,
            'amount' => $transaction->amount,
        ],
    ]);
});

it('forbids showing a transaction the user does not own or participate in', function (): void {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $other->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $response = $this->getJson(route('api.tallies.transactions.show', [$tally, $transaction]));

    $response->assertForbidden();
});

it('updates a transaction', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();
    $party = User::factory()->create();
    $tally->users()->attach($party->id, ['default_share_percentage' => 50]);
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

    $response = $this->patchJson(route('api.tallies.transactions.update', [$tally, $transaction]), $payload);

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

it('forbids updating a transaction the user does not own or participate in', function (): void {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $other->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $payload = [
        'party_id' => $me->id,
        'currency_id' => 1,
        'category_id' => 1,
        'type' => TransactionType::INCOME->value,
        'title' => 'Stealing Tally',
        'amount' => 1000000,
        'transaction_date' => now()->toDateString(),
    ];

    $response = $this->patchJson(route('api.tallies.transactions.update', [$tally, $transaction]), $payload);

    $response->assertForbidden();
});

it('deletes a transaction', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $response = $this->deleteJson(route('api.tallies.transactions.destroy', [$tally, $transaction]));

    $response->assertNoContent();
    $this->assertModelMissing($transaction);
});

it('forbids deleting a transaction the user does not own or participate in', function (): void {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Sanctum::actingAs($me);

    $tally = Tally::factory()->create(['owner_id' => $other->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $response = $this->deleteJson(route('api.tallies.transactions.destroy', [$tally, $transaction]));

    $response->assertForbidden();
});
