<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('displays the transaction edit page', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $currency = Currency::where('code', 'USD')->first() ?? Currency::factory()->create(['decimals' => 2]);
    $transaction = Transaction::factory()->forTally($tally)->withCurrency($currency)->create();

    $this->actingAs($user)
        ->get(route('tallies.transactions.edit', [$tally, $transaction]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tallies/transactions/Edit')
            ->has('tally', fn (Assert $tallyProp) => $tallyProp
                ->has('tally_id')
                ->has('name')
                ->etc()
            )
            ->has('transaction', fn (Assert $transactionProp) => $transactionProp
                ->has('transaction_id')
                ->where('transaction_id', $transaction->transaction_id)
                ->has('title')
                ->has('amount')
                ->where('amount', (float) $transaction->amount)
                ->etc()
            )
            ->has('categories')
            ->has('currencies')
            ->has('parties')
        );
});

it('updates a transaction via web route', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);
    $currency = Currency::where('code', 'USD')->first() ?? Currency::factory()->create(['decimals' => 2]);
    $transaction = Transaction::factory()->forTally($tally)->withCurrency($currency)->create();

    $payload = [
        'title' => 'Updated via Web',
        'amount' => 50.00,
        'type' => 'income',
        'party_id' => $user->id,
        'category_id' => $transaction->category_id,
        'currency_id' => $transaction->currency_id,
        'transaction_date' => now()->toDateString(),
    ];

    $this->actingAs($user)
        ->patch(route('tallies.transactions.update', [$tally, $transaction]), $payload)
        ->assertRedirect(route('tallies.transactions.index', $tally));

    $this->assertDatabaseHas(Transaction::TABLE_NAME, [
        'transaction_id' => $transaction->transaction_id,
        'title' => 'Updated via Web',
        'amount' => 5000,
    ]);
});
