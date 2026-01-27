<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;

it('displays the transactions for a tally', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->count(15)->create(['tally_id' => $tally->tally_id]);

    $response = $this->actingAs($user)->get(route('tallies.transactions.index', $tally));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('tallies/transactions/Index')
        ->has('transactions.data', 15)
        ->has('tally')
    );
});

it('cannot view transactions of a tally owned by another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $otherUser->id]);

    $response = $this->actingAs($user)->get(route('tallies.transactions.index', $tally));

    $response->assertStatus(403);
});

it('displays the create transaction form', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('tallies.transactions.create', $tally));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('tallies/transactions/Create')
        ->has('tally')
        ->has('categories')
        ->has('currencies')
        ->has('parties')
        ->where('auth.user.id', $user->id)
    );
});

it('can store a new transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user, ['default_share_percentage' => 100]);
    $category = Category::query()->first() ?? Category::factory()->create();
    $currency = Currency::query()->first() ?? Currency::factory()->create();

    $transactionData = [
        'title' => 'Test Transaction',
        'amount' => 100.50,
        'type' => 'expense',
        'party_id' => $user->id,
        'category_id' => $category->category_id,
        'currency_id' => $currency->currency_id,
        'transaction_date' => now()->format('Y-m-d'),
        'description' => 'Test description',
    ];

    $response = $this->actingAs($user)->post(route('tallies.transactions.store', $tally), $transactionData);

    $response->assertRedirect(route('tallies.transactions.index', $tally));
    $this->assertDatabaseHas('transactions', [
        'tally_id' => $tally->tally_id,
        'title' => 'Test Transaction',
        'amount' => 10050,
    ]);
});
