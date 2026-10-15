<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Enums\TransactionType;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

covers(TransactionController::class);

it('displays the transaction edit page', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $currency = Currency::where('code', 'USD')->first() ?? Currency::factory()->create(['decimals' => 2, 'symbol' => '$']);
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
                ->where('amount', fn (int|float $amount): bool => (float) $amount === (float) $transaction->amount)
                ->etc()
            )
            ->has('categories')
            ->has('currencies')
            ->has('currencies.0.symbol')
            ->has('parties')
        );
});

it('currencies prop includes symbol for create', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);

    $this->actingAs($user)
        ->get(route('tallies.transactions.create', $tally))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tallies/transactions/Create')
            ->has('currencies.0', fn (Assert $currency) => $currency
                ->has('symbol')
                ->has('code')
                ->has('name')
                ->has('currency_id')
                ->has('uuid')
            )
        );
});

it('transactions index includes splits prop', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    $this->actingAs($user)
        ->get(route('tallies.transactions.index', $tally))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tallies/transactions/Index')
            ->has('splits')
        );
});

it('updates a transaction via web route', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);
    $currency = Currency::where('code', 'USD')->first() ?? Currency::factory()->create(['decimals' => 2]);
    $this->seed(ReferenceDataSeeder::class);
    $salary = Category::query()->where('slug', 'salary')->firstOrFail();
    $transaction = Transaction::factory()->forTally($tally)->withCurrency($currency)->withType(TransactionType::INCOME)->withCategory($salary)->create();

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

it('sends the edit form exactly the tally, transaction, options, default currency and parties it needs', function () {
    $owner = User::factory()->create(['name' => 'Olive Owner']);
    $member = User::factory()->create(['name' => 'Max Member']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 60]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 40]);
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $category = Category::factory()->create(['name' => 'Groceries']);
    $transaction = Transaction::factory()->forTally($tally)->forParty($member)->withCurrency($currency)->create([
        'title' => 'Weekly shop',
        'description' => 'Fruit and veg',
        'type' => 'expense',
        'amount' => 12.5,
        'transaction_date' => '2026-10-13',
        'category_id' => $category->getKey(),
    ]);

    $this->actingAs($owner)
        ->get(route('tallies.transactions.edit', [$tally, $transaction]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('tally', ['tally_id' => $tally->tally_id, 'uuid' => $tally->uuid, 'name' => $tally->name])
            ->where('transaction', fn ($sent): bool => collect($sent)->toArray() === [
                'transaction_id' => $transaction->transaction_id,
                'uuid' => $transaction->uuid,
                'title' => 'Weekly shop',
                'amount' => $transaction->amount,
                'party_id' => $member->id,
                'category_id' => $category->getKey(),
                'currency_id' => $currency->currency_id,
                'type' => 'expense',
                'transaction_date' => $transaction->transaction_date,
                'description' => 'Fruit and veg',
            ])
            ->where('default_currency_uuid', $currency->uuid)
            ->where('parties', [
                ['id' => $owner->id, 'name' => 'Olive Owner'],
                ['id' => $member->id, 'name' => 'Max Member'],
            ])
            ->where('categories', fn ($categories): bool => collect($categories)->pluck('name')->contains('Groceries'))
            ->where('currencies', fn ($currencies): bool => collect($currencies)->pluck('uuid')->contains($currency->uuid))
        );
});

it('sends every category with its slug, type and colour to the transaction form', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 100]);
    $this->seed(ReferenceDataSeeder::class);

    $response = $this->actingAs($owner)->getJson(route('tallies.transactions.form-data', $tally))->assertOk();

    expect($response->json('categories'))->toHaveCount(27)
        ->and(collect($response->json('categories'))->firstWhere('slug', 'salary'))->toBe([
            'category_id' => Category::query()->where('slug', 'salary')->value('category_id'),
            'slug' => 'salary',
            'type' => 'income',
            'name' => 'Salary',
            'color' => 'c3',
        ]);
});

it('does not show the edit form of a transaction to someone outside the tally', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 100]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $this->actingAs($stranger)
        ->get(route('tallies.transactions.edit', [$tally, $transaction]))
        ->assertForbidden();
});

it('does not update a transaction of someone outside the tally', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 100]);
    $transaction = Transaction::factory()->forTally($tally)->create(['title' => 'Original']);

    $this->actingAs($stranger)
        ->patch(route('tallies.transactions.update', [$tally, $transaction]), [
            'title' => 'Hijacked',
            'amount' => 1,
            'type' => 'expense',
            'party_id' => $owner->id,
            'category_id' => $transaction->category_id,
            'currency_id' => $transaction->currency_id,
            'transaction_date' => '2026-10-13',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas(Transaction::TABLE_NAME, ['transaction_id' => $transaction->transaction_id, 'title' => 'Original']);
});

it('sends the frequent categories of each type with the transaction form data', function () {
    $this->travelTo('2026-10-15 12:00:00');
    $this->seed(ReferenceDataSeeder::class);
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 100]);

    foreach (['healthcare' => TransactionType::EXPENSE, 'bonus' => TransactionType::INCOME] as $slug => $type) {
        Transaction::factory()->forTally($tally)->forParty($owner)->withType($type)
            ->withCategory(Category::query()->where('slug', $slug)->firstOrFail())
            ->count(2)->create(['transaction_date' => '2026-10-10']);
    }

    $expected = [
        'expense' => ['healthcare', 'groceries', 'dining_out', 'transportation', 'bills'],
        'income' => ['bonus', 'salary', 'side_work', 'refunds_returns', 'gifts_received'],
    ];

    $this->actingAs($owner)->getJson(route('tallies.transactions.form-data', $tally))
        ->assertOk()
        ->assertJsonPath('frequent_categories', $expected);

    $transaction = Transaction::factory()->forTally($tally)->forParty($owner)->create();
    $this->actingAs($owner)
        ->get(route('tallies.transactions.edit', [$tally, $transaction]))
        ->assertInertia(fn (Assert $page) => $page->where('frequent_categories.expense.0', fn ($slug): bool => is_string($slug)));
    $this->actingAs($owner)
        ->get(route('tallies.transactions.create', $tally))
        ->assertInertia(fn (Assert $page) => $page->where('frequent_categories', $expected));
});

it('computes the frequent categories with two queries however many transactions there are', function () {
    $this->travelTo('2026-10-15 12:00:00');
    $this->seed(ReferenceDataSeeder::class);
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 100]);
    $countQueries = function () use ($owner, $tally): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($owner)->getJson(route('tallies.transactions.form-data', $tally))->assertOk();
        $queries = array_column(DB::getQueryLog(), 'query');
        DB::disableQueryLog();

        return count(array_filter($queries, fn (string $sql): bool => mb_stripos($sql, 'having') !== false));
    };

    $frequencyBefore = $countQueries();
    Transaction::factory()->forTally($tally)->forParty($owner)->count(40)->create(['transaction_date' => '2026-10-10']);
    $frequencyAfter = $countQueries();

    expect($frequencyBefore)->toBe(2)
        ->and($frequencyAfter)->toBe(2);
});
