<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TransactionController;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;

covers(TransactionController::class);

it('lists transactions', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->count(5)->forTally($tally)->create();

    $response = $this->actingAs($user)->getJson("/api/tallies/{$tally->tally_id}/transactions");

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

it('stores a transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $party = User::factory()->create();
    $currency = Currency::query()->inRandomOrder()->first();
    $category = Category::query()->inRandomOrder()->first();

    $data = [
        'party_id' => $party->id,
        'currency_id' => $currency->currency_id,
        'category_id' => $category->category_id,
        'type' => 'income',
        'title' => 'Test Transaction',
        'description' => 'Test Description',
        'amount' => 100.50,
        'transaction_date' => '2024-01-01',
    ];

    $response = $this->actingAs($user)->postJson("/api/tallies/{$tally->tally_id}/transactions", $data);

    $response->assertSuccessful()
        ->assertJsonPath('data.title', 'Test Transaction');
});

it('shows a transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $response = $this->actingAs($user)->getJson("/api/tallies/{$tally->tally_id}/transactions/{$transaction->transaction_id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $transaction->transaction_id);
});

it('updates a transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();
    $party = User::factory()->create();
    $currency = Currency::query()->inRandomOrder()->first();
    $category = Category::query()->inRandomOrder()->first();

    $data = [
        'party_id' => $party->id,
        'currency_id' => $currency->currency_id,
        'category_id' => $category->category_id,
        'type' => 'expense',
        'title' => 'Updated Transaction',
        'description' => 'Updated Description',
        'amount' => 200.75,
        'transaction_date' => '2024-02-01',
    ];

    $response = $this->actingAs($user)->putJson("/api/tallies/{$tally->tally_id}/transactions/{$transaction->transaction_id}", $data);

    $response->assertSuccessful()
        ->assertJsonPath('data.title', 'Updated Transaction');
});

it('destroys a transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $response = $this->actingAs($user)->deleteJson("/api/tallies/{$tally->tally_id}/transactions/{$transaction->transaction_id}");

    $response->assertNoContent();
    expect(Transaction::query()->find($transaction->transaction_id))->toBeNull();
});
