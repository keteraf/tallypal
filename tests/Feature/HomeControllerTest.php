<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Models\Category;
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
            ->where('balance.net', 0)
            ->where('balance.income', 0)
            ->where('balance.expense', 0)
            ->where('balance.currency_symbol', '€')
            ->where('balance.currency_code', 'EUR')
            ->where('balance.decimals', 2)
            ->where('settle_suggestion', null)
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
            ->where('current_month_start', now()->startOfMonth()->toDateString())
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

/**
 * @param  array<int, array{0: User, 1: float|int}>  $members  member and default share
 */
function homeTallyWithMembers(User $owner, array $members, float|int|null $ownerShare = 100): Tally
{
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    if ($ownerShare !== null) {
        $tally->users()->attach($owner->id, ['default_share_percentage' => $ownerShare]);
    }

    foreach ($members as [$member, $share]) {
        $tally->users()->attach($member->id, ['default_share_percentage' => $share]);
    }

    return $tally;
}

test('home tally cards carry the member preview with initials and a deterministic colour', function (): void {
    $owner = User::factory()->create(['name' => 'Sam Rivera']);
    $partner = User::factory()->create(['name' => 'luca bianchi']);
    homeTallyWithMembers($owner, [[$partner, 40]], 60);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.member_count', 2)
            ->where('tallies.0.members_preview', [
                ['initials' => 'SR', 'color' => $owner->avatarColor()],
                ['initials' => 'LB', 'color' => $partner->avatarColor()],
            ])
        );
});

test('home member preview lists the owner once and never exposes a co-member email', function (): void {
    $owner = User::factory()->create();
    $partner = User::factory()->create(['email' => 'partner-secret@example.test']);
    homeTallyWithMembers($owner, [[$partner, 50]], 50);

    $response = actingAs($owner)->get('/home')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
        ->has('tallies.0.members_preview', 2));

    expect($response->getContent())->not->toContain('partner-secret@example.test');
});

test('home member preview works when the owner has no pivot row', function (): void {
    $owner = User::factory()->create(['name' => 'Ada Lovelace']);
    $partner = User::factory()->create(['name' => 'Bob Stone']);
    homeTallyWithMembers($owner, [[$partner, 100]], null);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.member_count', 2)
            ->where('tallies.0.members_preview.0.initials', 'AL')
            ->where('tallies.0.members_preview.1.initials', 'BS')
            ->where('tallies.0.split_label', '0 / 100')
        );
});

test('home member preview is capped at three while the count and split keep every member', function (): void {
    $owner = User::factory()->create();
    $members = User::factory()->count(3)->create();
    homeTallyWithMembers($owner, [[$members[0], 30], [$members[1], 20], [$members[2], 10]], 40);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.member_count', 4)
            ->has('tallies.0.members_preview', 3)
            ->where('tallies.0.split_label', '40 / 30 / 20 / 10')
        );
});

test('home split label lists the owner share first for an unequal split', function (): void {
    $owner = User::factory()->create();
    homeTallyWithMembers($owner, [[User::factory()->create(), 40]], 60);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.split_label', '60 / 40')
        );
});

test('home split label keeps meaningful decimals', function (): void {
    $owner = User::factory()->create();
    homeTallyWithMembers($owner, [[User::factory()->create(), 37.5]], 62.5);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.split_label', '62.5 / 37.5')
        );
});

test('home split label is null for an equal split', function (): void {
    $owner = User::factory()->create();
    homeTallyWithMembers($owner, [[User::factory()->create(), 50]], 50);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.split_label', null)
        );
});

test('home split label treats rounded thirds as equal', function (): void {
    $owner = User::factory()->create();
    homeTallyWithMembers($owner, [[User::factory()->create(), 33.33], [User::factory()->create(), 33.34]], 33.33);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.split_label', null)
        );
});

test('home solo tally previews only its owner and has no split label', function (): void {
    $owner = User::factory()->create(['name' => 'Solo Sam']);
    Tally::factory()->create(['owner_id' => $owner->id]);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.members_preview', [['initials' => 'SS', 'color' => $owner->avatarColor()]])
            ->where('tallies.0.split_label', null)
        );
});

test('home no longer sends the cross-currency shared tally', function (): void {
    $owner = User::factory()->create();
    homeTallyWithMembers($owner, [[User::factory()->create(), 50]], 50);

    actingAs($owner)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->missing('shared_tally')
        );
});

test('home tally cards keep monthly expenses per currency instead of netting them', function (): void {
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $dollar = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Mixed']);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => now()->toDateString()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'expense', 'amount' => 5.5, 'transaction_date' => now()->toDateString()]);

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->missing('tallies.0.monthly_total')
            ->where('tallies.0.monthly_totals', [
                ['amount' => 10, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
                ['amount' => 5.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
        );
});

test('home tally card labels a quiet month in the tally own currency, not the home primary currency', function (): void {
    $this->travelTo('2026-10-13 12:00:00');
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $dollar = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
    $household = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Household']);
    $trip = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Weekend Trip']);

    Transaction::factory()->count(2)->forTally($household)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 20.0, 'transaction_date' => '2026-10-02', 'created_at' => '2026-10-02 09:00:00']);
    Transaction::factory()->forTally($trip)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'expense', 'amount' => 420.0, 'transaction_date' => '2026-09-25', 'created_at' => '2026-09-25 09:00:00']);

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('balance.currency_code', 'EUR')
            ->where('tallies.0.name', 'Household')
            ->where('tallies.0.monthly_totals', [
                ['amount' => 40, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
            ])
            ->where('tallies.1.name', 'Weekend Trip')
            ->where('tallies.1.monthly_totals', [
                ['amount' => 0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
        );
});

/**
 * October 2026: EUR is the most-used currency (3 transactions, net +50), USD has 2 (net +27.50).
 *
 * @return array{user: User, euro: Currency, dollar: Currency, tally: Tally}
 */
function seedMixedCurrencyMonth(): array
{
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $dollar = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
    $food = Category::factory()->create(['name' => 'Food']);
    $travel = Category::factory()->create(['name' => 'Travel']);
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Mixed']);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 30.0, 'transaction_date' => '2026-10-13', 'category_id' => $food->getKey()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 20.0, 'transaction_date' => '2026-10-02', 'category_id' => $food->getKey()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'income', 'amount' => 100.0, 'transaction_date' => '2026-10-05']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'expense', 'amount' => 12.5, 'transaction_date' => '2026-10-13', 'category_id' => $travel->getKey()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'income', 'amount' => 40.0, 'transaction_date' => '2026-10-06']);

    return ['user' => $user, 'euro' => $euro, 'dollar' => $dollar, 'tally' => $tally];
}

test('home aggregates of a mixed EUR/USD month use only the most-used currency and list the others apart', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    ['user' => $user] = seedMixedCurrencyMonth();

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('balance.currency_code', 'EUR')
            ->where('balance.net', 50)
            ->where('balance.income', 100)
            ->where('balance.expense', 50)
            ->where('balance.other_currencies', [
                ['amount' => 27.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
            ->where('weekly_history.11.is_current', true)
            ->where('weekly_history.11.amount', 30)
            ->where('categories_breakdown', [
                ['name' => 'Food', 'amount' => 50, 'percentage' => 100, 'bar_fill' => 100, 'is_other' => false],
            ])
            ->where('categories_other_currencies', [
                ['amount' => 12.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
        );
});

test('home By category folds every category beyond the top five into a real Other row and the rows add up to the month', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $amounts = ['Rent' => 400.0, 'Food' => 200.0, 'Travel' => 100.0, 'Fun' => 50.0, 'Gym' => 25.0, 'Cafe' => 15.0, 'Bus' => 10.0, 'Tea' => 5.0, 'Pub' => 2.5];

    foreach ($amounts as $name => $amount) {
        Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)->create([
            'type' => 'expense',
            'amount' => $amount,
            'transaction_date' => '2026-10-13',
            'category_id' => Category::factory()->create(['name' => $name])->getKey(),
        ]);
    }

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('categories_breakdown', 6)
            ->where('categories_breakdown', [
                ['name' => 'Rent', 'amount' => 400, 'percentage' => 49.5, 'bar_fill' => 100, 'is_other' => false],
                ['name' => 'Food', 'amount' => 200, 'percentage' => 24.8, 'bar_fill' => 50, 'is_other' => false],
                ['name' => 'Travel', 'amount' => 100, 'percentage' => 12.4, 'bar_fill' => 25, 'is_other' => false],
                ['name' => 'Fun', 'amount' => 50, 'percentage' => 6.2, 'bar_fill' => 12.5, 'is_other' => false],
                ['name' => 'Gym', 'amount' => 25, 'percentage' => 3.1, 'bar_fill' => 6.25, 'is_other' => false],
                ['name' => 'Other', 'amount' => 32.5, 'percentage' => 4, 'bar_fill' => 8.125, 'is_other' => true],
            ])
            ->where('categories_breakdown', fn ($rows): bool => array_sum(array_column($rows->toArray(), 'amount')) === array_sum($amounts))
            ->where('balance.expense', array_sum($amounts))
            ->etc()
        );
});

test('home aggregates use the user default currency over the most-used one', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    ['user' => $user, 'dollar' => $dollar] = seedMixedCurrencyMonth();
    $user->update(['default_currency_id' => $dollar->currency_id]);

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('balance.currency_code', 'USD')
            ->where('balance.currency_symbol', '$')
            ->where('balance.net', 27.5)
            ->where('balance.other_currencies', [
                ['amount' => 50, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
            ])
            ->where('weekly_history.11.amount', 12.5)
            ->where('categories_breakdown', [
                ['name' => 'Travel', 'amount' => 12.5, 'percentage' => 100, 'bar_fill' => 100, 'is_other' => false],
            ])
            ->where('categories_other_currencies', [
                ['amount' => 50, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
            ])
        );
});

test('home sends no other-currency lines when every transaction is in the primary currency', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 9.0, 'transaction_date' => '2026-10-10']);

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('balance.other_currencies', [])
            ->where('categories_other_currencies', [])
        );
});

test('home tally card month filter uses the transaction date, not the entry date', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 80.0, 'transaction_date' => '2026-09-28', 'created_at' => '2026-10-03 09:00:00']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 15.0, 'transaction_date' => '2026-10-01', 'created_at' => '2026-09-30 22:00:00']);

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.monthly_totals', [
                ['amount' => 15, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
            ])
            ->where('balance.expense', 15)
        );
});

/**
 * Adds an expense of `$amount` (major units) paid by `$party`, dated `$date`.
 */
function homeDebtExpense(Tally $tally, User $party, Currency $currency, float $amount, string $date): void
{
    Transaction::factory()->forTally($tally)->forParty($party)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => $amount, 'transaction_date' => $date]);
}

test('home tallies carry the user balance per currency and the settle suggestion in the user default currency', function (): void {
    $this->travelTo('2026-10-14 12:00:00');
    $user = User::factory()->create();
    $partner = User::factory()->create(['name' => 'Priya Shah']);
    $friend = User::factory()->create(['name' => 'Noah Whitfield']);
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $dollar = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);
    $user->update(['default_currency_id' => $dollar->currency_id]);

    $household = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Household']);
    $household->users()->attach($user->id, ['default_share_percentage' => 60]);
    $household->users()->attach($partner->id, ['default_share_percentage' => 40]);
    homeDebtExpense($household, $user, $euro, 500.00, '2026-10-10');
    homeDebtExpense($household, $user, $euro, 500.00, '2026-10-11');
    homeDebtExpense($household, $partner, $dollar, 100.00, '2026-10-12');

    $trip = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Trip']);
    $trip->users()->attach($user->id, ['default_share_percentage' => 50]);
    $trip->users()->attach($friend->id, ['default_share_percentage' => 50]);
    homeDebtExpense($trip, $user, $dollar, 20.00, '2026-10-01');

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies', fn ($tallies): bool => collect($tallies)->firstWhere('uuid', $household->uuid)['my_balance'] === [
                ['currency' => ['code' => 'EUR', 'symbol' => '€', 'decimals' => 2], 'amount' => 40000, 'direction' => 'owed_to_me', 'counterparty_name' => 'Priya Shah', 'counterparties_count' => 1],
                ['currency' => ['code' => 'USD', 'symbol' => '$', 'decimals' => 2], 'amount' => -6000, 'direction' => 'i_owe', 'counterparty_name' => 'Priya Shah', 'counterparties_count' => 1],
            ])
            ->where('settle_suggestion', [
                'tally_uuid' => $trip->uuid,
                'counterparty_name' => 'Noah Whitfield',
                'amount' => 1000,
                'currency' => ['code' => 'USD', 'symbol' => '$', 'decimals' => 2],
            ])
        );
});

test('home never suggests a debt from a tally the user has left', function (): void {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $own = Tally::factory()->create(['owner_id' => $user->id]);
    $own->users()->attach($user->id, ['default_share_percentage' => 100]);
    $left = Tally::factory()->create(['owner_id' => $owner->id]);
    $left->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $left->users()->attach($user->id, ['default_share_percentage' => 50]);
    homeDebtExpense($left, $user, $euro, 300.00, '2026-10-01');
    $left->users()->detach($user->id);

    actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('tallies', 1)
            ->where('tallies.0.uuid', $own->uuid)
            ->where('settle_suggestion', null)
        );
});
