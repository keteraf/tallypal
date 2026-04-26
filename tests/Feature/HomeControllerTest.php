<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

covers(HomeController::class);

test('guests are redirected to login', function (): void {
    $this->get('/home')->assertRedirect('/login');
});

test('unverified users are redirected to email verification', function (): void {
    $user = User::factory()->unverified()->create();
    actingAs($user)
        ->get('/home')
        ->assertRedirect(route('verification.notice'));
});

test('verified users without tallies see an empty payload', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/home')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->where('user_name', $user->name)
            ->where('primary_tally', null)
            ->where('tallies', [])
            ->where('recent_transactions', [])
            ->where('weekly_history', [])
            ->where('categories_breakdown', [])
            ->where('shared_tally', null)
            ->where('balance.net', 0)
            ->where('balance.income', 0)
            ->where('balance.expense', 0)
            ->where('balance.currency_symbol', '€')
            ->where('balance.currency_code', 'EUR')
            ->where('balance.decimals', 2)
        );
});

test('home page returns the Home Inertia component with required props for users with tallies', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Personal']);

    Transaction::factory()
        ->forTally($tally)
        ->forParty($user)
        ->withCurrency($currency)
        ->create([
            'type' => 'expense',
            'amount' => 10.0,
            'transaction_date' => now()->toDateString(),
            'title' => 'Coffee',
        ]);

    actingAs($user)
        ->get('/home')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->where('user_name', $user->name)
            ->where('current_month_label', now()->format('F Y'))
            ->has('primary_tally')
            ->where('primary_tally.uuid', $tally->uuid)
            ->where('primary_tally.name', 'Personal')
            ->has('tallies', 1)
            ->where('tallies.0.name', 'Personal')
            ->where('tallies.0.uuid', $tally->uuid)
            ->has('balance.net')
            ->has('balance.income')
            ->has('balance.expense')
            ->has('balance.currency_symbol')
            ->has('recent_transactions', 1)
            ->where('recent_transactions.0.title', 'Coffee')
            ->where('recent_transactions.0.type', 'expense')
            ->where('recent_transactions.0.amount', 10)
            ->has('weekly_history', 12)
            ->has('categories_breakdown')
        );
});

test('home page scopes data to the authenticated user', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);

    $myTally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Mine']);
    $theirTally = Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Theirs']);

    Transaction::factory()->forTally($myTally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 5.0, 'title' => 'Mine tx', 'transaction_date' => now()->toDateString()]);
    Transaction::factory()->forTally($theirTally)->forParty($other)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 50.0, 'title' => 'Theirs tx', 'transaction_date' => now()->toDateString()]);

    actingAs($user)
        ->get('/home')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->has('tallies', 1)
            ->where('tallies.0.name', 'Mine')
            ->has('recent_transactions', 1)
            ->where('recent_transactions.0.title', 'Mine tx')
        );
});

test('home page exposes the most-active shared tally with members', function (): void {
    $owner = User::factory()->create();
    $collab = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Lisbon trip']);
    $tally->users()->attach($collab->id, ['default_share_percentage' => 50]);

    Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 100.0, 'title' => 'Hotel', 'transaction_date' => now()->toDateString()]);

    actingAs($owner)
        ->get('/home')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('Home')
            ->has('shared_tally')
            ->where('shared_tally.name', 'Lisbon trip')
            ->where('shared_tally.uuid', $tally->uuid)
            ->has('shared_tally.members')
            ->where('shared_tally.total_spent', 100)
        );
});
