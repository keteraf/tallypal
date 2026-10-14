<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Laravel\Sanctum\Sanctum;

covers(StoreTransactionRequest::class, UpdateTransactionRequest::class);

const ACCEPTED = [200, 201, 302];

beforeEach(function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $this->user = User::factory()->create();
    $this->tally = Tally::factory()->create(['owner_id' => $this->user->id]);
    $this->tally->users()->attach($this->user->id, ['default_share_percentage' => 100]);
    $this->currency = Currency::query()->firstOrFail();
    $this->expenseCategory = Category::query()->where('slug', 'groceries')->firstOrFail();
    $this->incomeCategory = Category::query()->where('slug', 'salary')->firstOrFail();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function categoryPayload(array $overrides = []): array
{
    return [
        'party_id' => test()->user->id,
        'currency_id' => test()->currency->getKey(),
        'category_id' => test()->expenseCategory->getKey(),
        'type' => TransactionType::EXPENSE->value,
        'title' => 'Payload',
        'amount' => 10,
        'transaction_date' => now()->toDateString(),
        ...$overrides,
    ];
}

/**
 * Each endpoint as [label, send(payload) => TestResponse]; update targets an expense with an expense category.
 */
dataset('endpoints', [
    'web store' => [fn (array $payload) => test()->actingAs(test()->user)->postJson(route('tallies.transactions.store', test()->tally), $payload)],
    'web update' => [fn (array $payload) => test()->actingAs(test()->user)->patchJson(route('tallies.transactions.update', [test()->tally, categoryTransaction()]), $payload)],
    'api store' => [function (array $payload) {
        Sanctum::actingAs(test()->user);

        return test()->postJson(route('api.tallies.transactions.store', test()->tally), $payload);
    }],
    'api update' => [function (array $payload) {
        Sanctum::actingAs(test()->user);

        return test()->patchJson(route('api.tallies.transactions.update', [test()->tally, categoryTransaction()]), $payload);
    }],
]);

function categoryTransaction(): Transaction
{
    return Transaction::factory()->forTally(test()->tally)->withCurrency(test()->currency)
        ->withType(TransactionType::EXPENSE)->withCategory(test()->expenseCategory)->create();
}

it('accepts an expense with an expense category', function (Closure $send): void {
    expect($send(categoryPayload())->status())->toBeIn(ACCEPTED);
})->with('endpoints');

it('accepts an income with an income category', function (Closure $send): void {
    expect($send(categoryPayload(['type' => 'income', 'category_id' => $this->incomeCategory->getKey()]))->status())->toBeIn(ACCEPTED);
})->with('endpoints');

it('accepts a transaction without a category for either type', function (Closure $send, string $type): void {
    expect($send(categoryPayload(['type' => $type, 'category_id' => null]))->status())->toBeIn(ACCEPTED);
})->with('endpoints')->with(['expense', 'income']);

it('rejects an income with an expense category', function (Closure $send): void {
    $send(categoryPayload(['type' => 'income']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id' => __('validation.category_type_mismatch')]);
})->with('endpoints');

it('rejects an expense with an income category', function (Closure $send): void {
    $send(categoryPayload(['category_id' => $this->incomeCategory->getKey()]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['category_id' => __('validation.category_type_mismatch')]);
})->with('endpoints');

it('rejects an unknown category id', function (Closure $send): void {
    $send(categoryPayload(['category_id' => 999999]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');
})->with('endpoints');

it('rejects an update that flips the type but keeps the old category', function (): void {
    $transaction = categoryTransaction();

    $this->actingAs($this->user)
        ->patchJson(route('tallies.transactions.update', [$this->tally, $transaction]), categoryPayload(['type' => 'income']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');

    expect($transaction->refresh()->type->value)->toBe('expense');
});

it('accepts an update that flips the type and changes or clears the category', function (?string $slug): void {
    $transaction = categoryTransaction();
    $categoryId = $slug === null ? null : Category::query()->where('slug', $slug)->value('category_id');

    $this->actingAs($this->user)
        ->patchJson(route('tallies.transactions.update', [$this->tally, $transaction]), categoryPayload(['type' => 'income', 'category_id' => $categoryId]))
        ->assertRedirect();

    expect($transaction->refresh()->type->value)->toBe('income')
        ->and($transaction->category_id)->toBe($categoryId);
})->with(['changed' => 'salary', 'cleared' => null]);
