<?php

declare(strict_types=1);

use App\Actions\Transaction\GetTallyPerPersonAction;
use App\Enums\TransactionType;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;

covers(GetTallyPerPersonAction::class);

covers(TransactionController::class);

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

it('pins the tally payload exposed on the transactions index', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->where('tally.tally_id', $tally->tally_id)
            ->where('tally.uuid', $tally->uuid)
            ->where('tally.name', $tally->name)
            ->where('tally.description', $tally->description)
            ->where('tally.owner_id', $user->id)
            ->has('tally', 8)
        );
});

it('pins the transaction item fields and pagination metadata', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $category = Category::query()->first() ?? Category::factory()->create();
    $currency = Currency::query()->first() ?? Currency::factory()->create();
    $transaction = Transaction::factory()
        ->forTally($tally)
        ->withCategory($category)
        ->withCurrency($currency)
        ->withType(TransactionType::EXPENSE)
        ->create();
    Transaction::factory()->count(19)->forTally($tally)->withType(TransactionType::EXPENSE)->create();

    $this->actingAs($user)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->has('transactions.data', 15)
            ->where('transactions.current_page', 1)
            ->where('transactions.per_page', 15)
            ->where('transactions.total', 20)
            ->where('transactions.last_page', 2)
            ->whereNot('transactions.next_page_url', null)
            ->where('transactions.prev_page_url', null)
            ->has('transactions.data.0', fn ($item) => $item
                ->has('transaction_id')
                ->has('uuid')
                ->has('title')
                ->has('amount')
                ->has('type')
                ->has('transaction_date')
                ->has('category', fn ($c) => $c->has('category_id')->has('slug')->has('name')->has('color'))
                ->has('currency', fn ($c) => $c->has('symbol')->has('decimals'))
                ->has('party', fn ($p) => $p->has('name')->has('initials')->has('color'))
            )
        );

    $this->actingAs($user)->get(route('tallies.transactions.index', ['tally' => $tally, 'page' => 2]))
        ->assertInertia(fn ($page) => $page
            ->has('transactions.data', 5)
            ->where('transactions.current_page', 2)
            ->where('transactions.next_page_url', null)
        );

    expect($transaction->type)->toBe(TransactionType::EXPENSE);
});

it('maps the exact values of a single transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $category = Category::query()->first() ?? Category::factory()->create();
    $currency = Currency::query()->first() ?? Currency::factory()->create();
    $transaction = Transaction::factory()
        ->forTally($tally)
        ->withCategory($category)
        ->withCurrency($currency)
        ->withType(TransactionType::INCOME)
        ->create();

    $this->actingAs($user)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->where('transactions.data.0.transaction_id', $transaction->transaction_id)
            ->where('transactions.data.0.uuid', $transaction->uuid)
            ->where('transactions.data.0.title', $transaction->title)
            ->where('transactions.data.0.amount', fn (int|float $amount): bool => (float) $amount === (float) $transaction->amount)
            ->where('transactions.data.0.type', 'income')
            ->where('transactions.data.0.transaction_date', $transaction->transaction_date)
            ->where('transactions.data.0.category', ['category_id' => $category->category_id, 'slug' => $category->slug, 'name' => $category->name, 'color' => $category->color])
            ->where('transactions.data.0.currency', ['symbol' => $currency->symbol, 'decimals' => $currency->decimals])
        );
});

it('sends no category for an uncategorised transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->forTally($tally)->withType(TransactionType::INCOME)->create(['category_id' => null]);

    $this->actingAs($user)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page->where('transactions.data.0.category', null));
});

it('exposes the transactions as an appendable scroll prop on page 2', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->count(20)->forTally($tally)->create();

    $response = $this->actingAs($user)->get(
        route('tallies.transactions.index', ['tally' => $tally, 'page' => 2]),
        [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => app(Inertia\Middleware::class)->version(request()),
            'X-Inertia-Partial-Component' => 'tallies/transactions/Index',
            'X-Inertia-Partial-Data' => 'transactions',
            'X-Inertia-Infinite-Scroll-Merge-Intent' => 'append',
        ],
    );

    $response->assertOk();
    expect($response->json('mergeProps'))->toContain('transactions.data')
        ->and($response->json('scrollProps.transactions'))->toBe([
            'pageName' => 'page',
            'previousPage' => 1,
            'nextPage' => null,
            'currentPage' => 2,
            'reset' => false,
        ])
        ->and($response->json('props.transactions.data'))->toHaveCount(5);
});

it('advertises the next page in the scroll metadata on page 1', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->count(20)->forTally($tally)->create();

    $response = $this->actingAs($user)->get(
        route('tallies.transactions.index', $tally),
        ['X-Inertia' => 'true', 'X-Inertia-Version' => app(Inertia\Middleware::class)->version(request())],
    );

    expect($response->json('scrollProps.transactions.nextPage'))->toBe(2)
        ->and($response->json('scrollProps.transactions.previousPage'))->toBeNull();
});

it('exposes the tally colour, glyph and members count for the header', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'color' => 'c3', 'glyph' => '✈']);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 60]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 40]);

    $this->actingAs($member)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->where('tally.color', 'c3')
            ->where('tally.glyph', '✈')
            ->where('tally.users_count', 2)
        );
});

it('counts a solo tally as one member and keeps a missing colour and glyph null', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    $this->actingAs($user)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->where('tally.color', null)
            ->where('tally.glyph', null)
            ->where('tally.users_count', 1)
        );
});

it('maps the party of each transaction to name, initials and colour for members', function () {
    $owner = User::factory()->create(['name' => 'Marco Rossi']);
    $member = User::factory()->create(['name' => 'Anna Bianchi']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);
    Transaction::factory()->forTally($tally)->forParty($member)->create();

    $this->actingAs($owner)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->where('transactions.data.0.party', [
                'name' => 'Anna Bianchi',
                'initials' => 'AB',
                'color' => $member->avatarColor(),
            ])
        );
});

it('never reveals party names to a user who is not a member of the tally', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    Transaction::factory()->forTally($tally)->forParty($owner)->create();

    $response = $this->actingAs($outsider)->get(route('tallies.transactions.index', $tally));

    $response->assertForbidden();
    expect($response->getContent())->not->toContain($owner->name);
});

it('does not expose party emails', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->forTally($tally)->forParty($user)->create();

    $this->actingAs($user)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->has('transactions.data.0.party', 3)
        );
});

it('computes per person as one value per currency, never summed across currencies', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 50]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $usd = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);
    Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($eur)->withType(TransactionType::EXPENSE)->withAmount(100.0)->create();
    Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($eur)->withType(TransactionType::INCOME)->withAmount(500.0)->create();
    Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($usd)->withType(TransactionType::EXPENSE)->withAmount(41.0)->create();

    $this->actingAs($owner)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->has('per_person', 2)
            ->where('per_person.0.currency_code', 'EUR')
            ->where('per_person.0.currency_symbol', '€')
            ->where('per_person.0.amount', 50)
            ->where('per_person.0.decimals', 2)
            ->where('per_person.1.currency_code', 'USD')
            ->where('per_person.1.currency_symbol', '$')
            ->where('per_person.1.amount', 20.5)
        );
});

it('reports a zero per person amount for a currency with income only', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($eur)->withType(TransactionType::INCOME)->withAmount(80.0)->create();

    $this->actingAs($owner)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->where('per_person.0.amount', 0)
            ->where('per_person.0.currency_code', 'EUR')
        );
});

it('has an empty per person list for a tally without transactions', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page->has('per_person', 0));
});

it('rounds per person to the currency decimals', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 34]);
    $tally->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 33]);
    $tally->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 33]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($eur)->withType(TransactionType::EXPENSE)->withAmount(100.0)->create();

    $this->actingAs($owner)->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page->where('per_person.0.amount', 33.33));
});

it('sends the members settled in each currency next to the unchanged debts', function () {
    $owner = User::factory()->create(['name' => 'Sam Rivera']);
    $partner = User::factory()->create(['name' => 'Priya Shah']);
    $guest = User::factory()->create(['name' => 'Sara Conti']);
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($partner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($guest->id, ['default_share_percentage' => 0]);
    Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($euro)
        ->withType(TransactionType::EXPENSE)->withAmount(152.60)->create();

    $this->actingAs($owner)
        ->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn ($page) => $page
            ->where('settled_members', [[
                'currency' => ['code' => 'EUR', 'symbol' => '€', 'decimals' => 2],
                'members' => [['id' => $guest->id, 'name' => 'Sara Conti']],
            ]])
            ->where('debts.0.settlements.0.debtor.id', $partner->id)
            ->where('debts.0.settlements.0.creditor.id', $owner->id)
            ->where('debts.0.settlements.0.amount', 76.3)
        );
});
