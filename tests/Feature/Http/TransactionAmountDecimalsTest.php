<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Http\Requests\Transactions\UpdateTransactionRequest;
use App\Http\Requests\Transactions\ValidatesAmountDecimals;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;

covers(StoreTransactionRequest::class, UpdateTransactionRequest::class, ValidatesAmountDecimals::class);

beforeEach(function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $this->user = User::factory()->create();
    $this->tally = Tally::factory()->create(['owner_id' => $this->user->id, 'default_currency_id' => null]);
    $this->tally->users()->attach($this->user->id, ['default_share_percentage' => 100]);
    $this->eur = Currency::query()->where('code', 'EUR')->firstOrFail();
    $this->jpy = Currency::query()->where('code', 'JPY')->firstOrFail();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function decimalsPayload(array $overrides = []): array
{
    return [
        'party_id' => test()->user->id,
        'currency_id' => test()->eur->getKey(),
        'category_id' => null,
        'type' => TransactionType::EXPENSE->value,
        'title' => 'Payload',
        'amount' => 10,
        'transaction_date' => now()->toDateString(),
        ...$overrides,
    ];
}

dataset('endpoints', [
    'store' => [fn (array $payload) => test()->actingAs(test()->user)->postJson(route('tallies.transactions.store', test()->tally), $payload)],
    'update' => [fn (array $payload) => test()->actingAs(test()->user)->patchJson(
        route('tallies.transactions.update', [test()->tally, Transaction::factory()->forTally(test()->tally)->withCurrency(test()->eur)->create()]),
        $payload,
    )],
]);

it('rejects more decimals than the currency allows', function (Closure $send): void {
    $send(decimalsPayload(['amount' => 12.345]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('amount');
})->with('endpoints');

it('accepts amounts within the currency decimals', function (Closure $send, float|int $amount): void {
    $send(decimalsPayload(['amount' => $amount]))->assertValid()->assertRedirect();
})->with('endpoints')->with([12.34, 12, 12.3]);

it('allows whole numbers only for a zero-decimal currency', function (Closure $send): void {
    $send(decimalsPayload(['currency_id' => $this->jpy->getKey(), 'amount' => 12]))->assertValid();
    $send(decimalsPayload(['currency_id' => $this->jpy->getKey(), 'amount' => 12.5]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('amount');
})->with('endpoints');

it('rejects a comma decimal over the limit', function (Closure $send): void {
    $send(decimalsPayload(['amount' => '12,345']))->assertJsonValidationErrors('amount');
})->with('endpoints');

it('falls back to the tally default currency when the submitted one is unknown', function (Closure $send): void {
    $this->tally->update(['default_currency_id' => $this->jpy->getKey()]);

    $send(decimalsPayload(['currency_id' => 999999, 'amount' => 12.5]))
        ->assertJsonValidationErrors(['amount', 'currency_id']);
})->with('endpoints');

it('only applies the base rules when no currency can be resolved', function (Closure $send): void {
    $send(decimalsPayload(['currency_id' => null, 'amount' => 12.345]))
        ->assertJsonValidationErrors('currency_id')
        ->assertJsonMissingValidationErrors('amount');
})->with('endpoints');
