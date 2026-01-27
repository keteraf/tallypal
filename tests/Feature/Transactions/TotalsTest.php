<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('calculates totals correctly with different currency decimals', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    // EUR has 2 decimals
    $eur = Currency::query()->where('code', 'EUR')->first();
    // JPY has 0 decimals
    $jpy = Currency::query()->where('code', 'JPY')->first();

    // Create a 10.50 EUR expense (stored as 1050)
    Transaction::factory()->create([
        'tally_id' => $tally->tally_id,
        'currency_id' => $eur->currency_id,
        'amount' => 10.50,
        'type' => TransactionType::EXPENSE,
    ]);

    // Create a 500 JPY expense (stored as 500)
    Transaction::factory()->create([
        'tally_id' => $tally->tally_id,
        'currency_id' => $jpy->currency_id,
        'amount' => 500,
        'type' => TransactionType::EXPENSE,
    ]);

    $response = $this->actingAs($user)->get(route('tallies.transactions.index', $tally));

    $response->assertStatus(200);

    // Current logic does (1050 + 500) / 100 = 15.50
    // Correct logic should handle them separately or at least correctly per currency.
    // If they are returned as an array of totals by currency/type, we should check that.

    $response->assertInertia(fn (Assert $page) => $page
        ->component('tallies/transactions/Index')
        ->has('totals', 2)
        ->where('totals.0.type', 'expense')
        ->where('totals.0.total', 10.5)
        ->where('totals.1.type', 'expense')
        ->where('totals.1.total', 500)
    );
});
