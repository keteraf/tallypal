<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionStatsRepository;
use Carbon\CarbonImmutable;
use Database\Seeders\ReferenceDataSeeder;

covers(TransactionStatsRepository::class);

test('primaryCurrency returns fallback EUR when no transactions exist', function (): void {
    $repo = new TransactionStatsRepository;
    $result = $repo->primaryCurrency(collect([99999]));

    expect($result)->toBe([
        'symbol' => '€',
        'code' => 'EUR',
        'decimals' => 2,
        'currency_id' => 0,
    ]);
});

test('primaryCurrency returns all fields correctly including symbol and decimals', function (): void {
    $user = User::factory()->create();
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $usd = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($eur)
        ->count(3)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => now()->toDateString()]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($usd)
        ->count(1)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => now()->toDateString()]);

    $repo = new TransactionStatsRepository;
    $result = $repo->primaryCurrency(collect([$tally->tally_id]));

    expect($result['code'])->toBe('EUR')
        ->and($result['symbol'])->toBe('€')
        ->and($result['decimals'])->toBe(2)
        ->and($result['currency_id'])->toBe((int) $eur->currency_id);
});

test('monthlyBalance returns all fields with correct values', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::now();

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'income', 'amount' => 200.0, 'transaction_date' => $now->toDateString()]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 80.0, 'transaction_date' => $now->toDateString()]);

    $primaryCurrency = ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => (int) $currency->currency_id];

    $repo = new TransactionStatsRepository;
    $result = $repo->monthlyBalance(
        collect([$tally->tally_id]),
        $now->startOfMonth(),
        $now,
        $primaryCurrency,
    );

    expect($result)->toBe([
        'net' => 120.0,
        'income' => 200.0,
        'expense' => 80.0,
        'currency_symbol' => '€',
        'currency_code' => 'EUR',
        'decimals' => 2,
    ]);
});

test('monthlyBalance respects decimal scaling', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 3, 'symbol' => 'د.ك', 'code' => 'KWD']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::now();

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => $now->toDateString()]);

    $primaryCurrency = ['symbol' => 'د.ك', 'code' => 'KWD', 'decimals' => 3, 'currency_id' => (int) $currency->currency_id];

    $repo = new TransactionStatsRepository;
    $result = $repo->monthlyBalance(
        collect([$tally->tally_id]),
        $now->startOfMonth(),
        $now,
        $primaryCurrency,
    );

    expect($result['expense'])->toBe(10.0)
        ->and($result['decimals'])->toBe(3);
});

test('recentTransactions returns at most 7 transactions', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->count(10)
        ->create(['type' => 'expense', 'amount' => 5.0, 'transaction_date' => now()->toDateString()]);

    $repo = new TransactionStatsRepository;
    $result = $repo->recentTransactions(collect([$tally->tally_id]));

    expect($result)->toHaveCount(7);
});

test('recentTransactions returns all required fields with correct values', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€']);
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Main']);
    $today = now()->toDateString();

    $tx = Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 15.0, 'title' => 'Groceries', 'transaction_date' => $today]);

    $repo = new TransactionStatsRepository;
    $result = $repo->recentTransactions(collect([$tally->tally_id]));

    expect($result)->toHaveCount(1);
    $row = $result[0];
    expect($row['uuid'])->toBe((string) $tx->uuid)
        ->and($row['title'])->toBe('Groceries')
        ->and($row['type'])->toBe('expense')
        ->and($row['amount'])->toBe(15.0)
        ->and($row['transaction_date'])->toBe($today)
        ->and($row['tally_name'])->toBe('Main')
        ->and($row['currency_symbol'])->toBe('€');
});

test('recentTransactions flags rows of multi-member tallies as shared', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $solo = Tally::factory()->create(['owner_id' => $user->id]);
    $solo->users()->attach($user->id, ['default_share_percentage' => 100]);
    $shared = Tally::factory()->create(['owner_id' => $user->id]);
    $shared->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 50]);
    Transaction::factory()->forTally($solo)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 5.0, 'transaction_date' => '2026-10-02', 'title' => 'Solo tx']);
    Transaction::factory()->forTally($shared)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 5.0, 'transaction_date' => '2026-10-01', 'title' => 'Shared tx']);

    $result = (new TransactionStatsRepository)->recentTransactions(collect([$solo->tally_id, $shared->tally_id]));

    expect($result[0]['is_shared'])->toBeFalse()
        ->and($result[1]['is_shared'])->toBeTrue();
});

test('recentTransactions and activity send the category slug and colour, null without a category', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $today = CarbonImmutable::now();
    $category = Category::factory()->create(['slug' => 'test_market', 'color' => 'c4']);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'title' => 'Tagged', 'transaction_date' => $today->toDateString(), 'category_id' => $category->getKey()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'title' => 'Untagged', 'transaction_date' => $today->toDateString(), 'category_id' => null]);

    $repo = new TransactionStatsRepository;
    $recent = collect($repo->recentTransactions(collect([$tally->tally_id])));
    $activity = collect($repo->activity(collect([$tally->tally_id]), $today->startOfMonth(), $today, 10));

    foreach ([$recent, $activity] as $rows) {
        expect(collect($rows->firstWhere('title', 'Tagged'))->only(['category_slug', 'category_color'])->all())
            ->toBe(['category_slug' => 'test_market', 'category_color' => 'c4'])
            ->and(collect($rows->firstWhere('title', 'Untagged'))->only(['category_slug', 'category_color'])->all())
            ->toBe(['category_slug' => null, 'category_color' => null]);
    }
});

test('weeklyHistory returns exactly 12 weeks with correct label format', function (): void {
    $now = CarbonImmutable::now();
    $start = $now->startOfWeek()->subWeeks(11);

    $repo = new TransactionStatsRepository;
    $result = $repo->weeklyHistory(collect([]), $start, $now, 0);

    expect($result)->toHaveCount(12);
    expect($result[11]['is_current'])->toBeTrue();
    expect($result[0]['is_current'])->toBeFalse();

    // Verify label format "W XX"
    expect($result[0]['label'])->toStartWith('W ');
    expect($result[11]['label'])->toStartWith('W ');

    // Labels should differ between first and last week
    expect($result[0]['label'])->not->toBe($result[11]['label']);
});

test('weeklyHistory uses week number in label not just W prefix', function (): void {
    $now = CarbonImmutable::parse('2025-05-12'); // a Monday
    $start = $now->startOfWeek()->subWeeks(11);

    $repo = new TransactionStatsRepository;
    $result = $repo->weeklyHistory(collect([]), $start, $now, 0);

    // The last week should be W 20 (week 20 of 2025)
    expect($result[11]['label'])->toBe('W ' . $now->format('W'));
});

test('weeklyHistory aggregates expenses per week', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::now();

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 50.0, 'transaction_date' => $now->toDateString()]);

    $start = $now->startOfWeek()->subWeeks(11);

    $repo = new TransactionStatsRepository;
    $result = $repo->weeklyHistory(collect([$tally->tally_id]), $start, $now, (int) $currency->currency_id);

    $currentWeek = collect($result)->firstWhere('is_current', true);
    expect($currentWeek['amount'])->toBe(50.0);

    // Weeks without data should have amount 0.0
    $nonCurrentWeeks = collect($result)->where('is_current', false)->where('amount', '>', 0);
    expect($nonCurrentWeeks)->toBeEmpty();
});

test('categoriesBreakdown returns name, amount, and percentage for each category', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::now();

    $catA = Category::factory()->create(['name' => 'FoodX']);
    $catB = Category::factory()->create(['name' => 'TransportX']);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 60.0, 'transaction_date' => $now->toDateString(), 'category_id' => $catA->getKey()]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 40.0, 'transaction_date' => $now->toDateString(), 'category_id' => $catB->getKey()]);

    $repo = new TransactionStatsRepository;
    $result = $repo->categoriesBreakdown(
        collect([$tally->tally_id]),
        $now->startOfMonth(),
        $now,
        (int) $currency->currency_id,
    );

    $foodRow = collect($result)->firstWhere('name', 'FoodX');
    $transportRow = collect($result)->firstWhere('name', 'TransportX');

    expect($foodRow)->not->toBeNull()
        ->and((float) $foodRow['amount'])->toBe(60.0)
        ->and((float) $foodRow['percentage'])->toBe(60.0)
        ->and($transportRow)->not->toBeNull()
        ->and((float) $transportRow['amount'])->toBe(40.0)
        ->and((float) $transportRow['percentage'])->toBe(40.0);

    $total = collect($result)->sum('percentage');
    expect($total)->toBe(100.0);
});

test('categoriesBreakdown zero percentage when total is zero', function (): void {
    $repo = new TransactionStatsRepository;
    $now = CarbonImmutable::now();

    $result = $repo->categoriesBreakdown(collect([99999]), $now->startOfMonth(), $now, 0);

    expect($result)->toBeEmpty();
});

test('expenseTotal sums expenses within the date window', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::now();

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 30.0, 'transaction_date' => $now->toDateString()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'income', 'amount' => 500.0, 'transaction_date' => $now->toDateString()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 15.0, 'transaction_date' => $now->subMonths(2)->toDateString()]);

    $repo = new TransactionStatsRepository;
    $total = $repo->expenseTotal(collect([$tally->tally_id]), $now->startOfMonth(), $now, (int) $currency->currency_id);

    expect($total)->toBe(30.0);
});

test('expenseTotal returns zero when there is no matching expense', function (): void {
    $repo = new TransactionStatsRepository;
    $now = CarbonImmutable::now();

    $total = $repo->expenseTotal(collect([99999]), $now->startOfMonth(), $now, 0);

    expect($total)->toBe(0.0);
});

test('dailyHistory returns exactly 7 days starting at the given date', function (): void {
    $now = CarbonImmutable::parse('2025-05-15'); // Thursday
    $start = $now->startOfWeek();

    $repo = new TransactionStatsRepository;
    $result = $repo->dailyHistory(collect([]), $start, $now, 0);

    expect($result)->toHaveCount(7)
        ->and($result[0]['label'])->toBe($start->format('D'))
        ->and($result[3]['is_current'])->toBeTrue();
});

test('dailyHistory aggregates expenses per day and excludes days outside the window', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::parse('2025-05-15'); // Thursday
    $start = $now->startOfWeek();

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 25.0, 'transaction_date' => $now->toDateString()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 99.0, 'transaction_date' => $start->subDay()->toDateString()]);

    $repo = new TransactionStatsRepository;
    $result = $repo->dailyHistory(collect([$tally->tally_id]), $start, $now, (int) $currency->currency_id);

    $today = collect($result)->firstWhere('is_current', true);
    expect($today['amount'])->toBe(25.0);

    $total = collect($result)->sum('amount');
    expect($total)->toBe(25.0);
});

test('monthlyHistory returns exactly 12 months starting at the given date', function (): void {
    $now = CarbonImmutable::parse('2025-05-15');
    $start = $now->startOfYear();

    $repo = new TransactionStatsRepository;
    $result = $repo->monthlyHistory(collect([]), $start, $now, 0);

    expect($result)->toHaveCount(12)
        ->and($result[0]['label'])->toBe($start->format('M'))
        ->and($result[4]['is_current'])->toBeTrue();
});

test('monthlyHistory aggregates expenses per month and excludes months outside the window', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::parse('2025-05-15');
    $start = $now->startOfYear();

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 60.0, 'transaction_date' => $now->toDateString()]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 40.0, 'transaction_date' => $start->subMonth()->toDateString()]);

    $repo = new TransactionStatsRepository;
    $result = $repo->monthlyHistory(collect([$tally->tally_id]), $start, $now, (int) $currency->currency_id);

    $currentMonth = collect($result)->firstWhere('is_current', true);
    expect($currentMonth['amount'])->toBe(60.0);

    $total = collect($result)->sum('amount');
    expect($total)->toBe(60.0);
});

test('categoriesBreakdown returns every category, largest first, with percentages of the whole', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::now();

    // Create 8 distinct categories and transactions
    for ($i = 1; $i <= 8; $i++) {
        $cat = Category::factory()->create(['name' => "TestCat{$i}"]);
        Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
            ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => $now->toDateString(), 'category_id' => $cat->getKey()]);
    }

    $repo = new TransactionStatsRepository;
    $result = $repo->categoriesBreakdown(collect([$tally->tally_id]), $now->startOfMonth(), $now, (int) $currency->currency_id);

    expect($result)->toHaveCount(8)
        ->and(array_sum(array_column($result, 'amount')))->toBe(80.0)
        ->and(array_column($result, 'percentage'))->each->toBe(12.5);
});

/**
 * Wednesday 2025-05-14: EUR has 3 transactions (expenses 30 + 20, income 100), USD has 2
 * (expense 12.50, income 40). GBP is a third currency with no activity.
 *
 * @return array{tallyIds: Illuminate\Support\Collection<int, int>, euro: Currency, dollar: Currency, pound: Currency, now: CarbonImmutable}
 */
function seedMixedCurrencyTransactions(): array
{
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $dollar = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);
    $pound = Currency::factory()->create(['code' => 'GBP', 'symbol' => '£', 'decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::parse('2025-05-14 12:00:00');

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 30.0, 'transaction_date' => '2025-05-13']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 20.0, 'transaction_date' => '2025-05-02']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'income', 'amount' => 100.0, 'transaction_date' => '2025-05-05']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'expense', 'amount' => 12.5, 'transaction_date' => '2025-05-13']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'income', 'amount' => 40.0, 'transaction_date' => '2025-05-06']);

    return ['tallyIds' => collect([(int) $tally->tally_id]), 'euro' => $euro, 'dollar' => $dollar, 'pound' => $pound, 'now' => $now];
}

test('primaryCurrency prefers the given currency over the most-used one', function (): void {
    ['tallyIds' => $tallyIds, 'dollar' => $dollar] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->primaryCurrency($tallyIds, (int) $dollar->currency_id);

    expect($result)->toBe(['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => (int) $dollar->currency_id]);
});

test('primaryCurrency honours a preferred currency the tallies have never used', function (): void {
    ['tallyIds' => $tallyIds, 'pound' => $pound] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->primaryCurrency($tallyIds, (int) $pound->currency_id);

    expect($result['code'])->toBe('GBP');
});

test('primaryCurrency falls back to the most-used currency when the preferred one is null or unknown', function (?int $preferred): void {
    ['tallyIds' => $tallyIds] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->primaryCurrency($tallyIds, $preferred);

    expect($result['code'])->toBe('EUR');
})->with([
    'null' => [null],
    'unknown id' => [999_999],
]);

test('monthlyBalance only adds up transactions in the primary currency', function (): void {
    ['tallyIds' => $tallyIds, 'dollar' => $dollar, 'now' => $now] = seedMixedCurrencyTransactions();
    $primary = ['symbol' => '$', 'code' => 'USD', 'decimals' => 2, 'currency_id' => (int) $dollar->currency_id];

    $result = (new TransactionStatsRepository)->monthlyBalance($tallyIds, $now->startOfMonth(), $now, $primary);

    expect($result['net'])->toBe(27.5)
        ->and($result['income'])->toBe(40.0)
        ->and($result['expense'])->toBe(12.5);
});

test('weeklyHistory only adds up expenses in the given currency', function (): void {
    ['tallyIds' => $tallyIds, 'euro' => $euro, 'now' => $now] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->weeklyHistory($tallyIds, $now->startOfWeek()->subWeeks(11), $now, (int) $euro->currency_id);

    expect($result[11]['amount'])->toBe(30.0)
        ->and(collect($result)->sum('amount'))->toBe(50.0);
});

test('categoriesBreakdown only adds up expenses in the given currency', function (): void {
    ['tallyIds' => $tallyIds, 'dollar' => $dollar, 'now' => $now] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->categoriesBreakdown($tallyIds, $now->startOfMonth(), $now, (int) $dollar->currency_id);

    expect($result)->toHaveCount(1)
        ->and($result[0]['amount'])->toBe(12.5)
        ->and($result[0]['percentage'])->toBe(100.0);
});

test('expenseTotal only adds up expenses in the given currency', function (): void {
    ['tallyIds' => $tallyIds, 'euro' => $euro, 'now' => $now] = seedMixedCurrencyTransactions();

    expect((new TransactionStatsRepository)->expenseTotal($tallyIds, $now->startOfMonth(), $now, (int) $euro->currency_id))->toBe(50.0);
});

test('dailyHistory only adds up expenses in the given currency', function (): void {
    ['tallyIds' => $tallyIds, 'dollar' => $dollar, 'now' => $now] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->dailyHistory($tallyIds, $now->startOfWeek(), $now, (int) $dollar->currency_id);

    expect(collect($result)->sum('amount'))->toBe(12.5);
});

test('monthlyHistory only adds up expenses in the given currency', function (): void {
    ['tallyIds' => $tallyIds, 'euro' => $euro, 'now' => $now] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->monthlyHistory($tallyIds, $now->startOfYear(), $now, (int) $euro->currency_id);

    expect($result[4]['amount'])->toBe(50.0)
        ->and(collect($result)->sum('amount'))->toBe(50.0);
});

test('otherCurrencyNets lists the net of every other currency with activity in the range', function (): void {
    ['tallyIds' => $tallyIds, 'euro' => $euro, 'dollar' => $dollar, 'now' => $now] = seedMixedCurrencyTransactions();
    $repo = new TransactionStatsRepository;

    expect($repo->otherCurrencyNets($tallyIds, $now->startOfMonth(), $now, (int) $euro->currency_id))->toBe([
        ['amount' => 27.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
    ])->and($repo->otherCurrencyNets($tallyIds, $now->startOfMonth(), $now, (int) $dollar->currency_id))->toBe([
        ['amount' => 50.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
    ])->and($repo->otherCurrencyNets($tallyIds, $now->startOfMonth()->subMonth(), $now->startOfMonth()->subDay(), (int) $euro->currency_id))->toBe([]);
});

test('otherCurrencyNets lists every currency when the primary one has no activity, most-used first', function (): void {
    ['tallyIds' => $tallyIds, 'pound' => $pound, 'now' => $now] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->otherCurrencyNets($tallyIds, $now->startOfMonth(), $now, (int) $pound->currency_id);

    expect(array_column($result, 'currency_code'))->toBe(['EUR', 'USD']);
});

test('otherCurrencyExpenses lists the expenses of every other currency within the range', function (): void {
    ['tallyIds' => $tallyIds, 'euro' => $euro, 'dollar' => $dollar, 'now' => $now] = seedMixedCurrencyTransactions();
    $repo = new TransactionStatsRepository;

    expect($repo->otherCurrencyExpenses($tallyIds, $now->startOfMonth(), $now, (int) $euro->currency_id))->toBe([
        ['amount' => 12.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
    ])->and($repo->otherCurrencyExpenses($tallyIds, $now->startOfMonth(), $now, (int) $dollar->currency_id))->toBe([
        ['amount' => 50.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
    ])->and($repo->otherCurrencyExpenses($tallyIds, $now->subDay(), $now, (int) $euro->currency_id))->toBe([
        ['amount' => 12.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
    ])->and($repo->otherCurrencyExpenses($tallyIds, $now->startOfMonth(), $now->startOfMonth()->addDays(3), (int) $euro->currency_id))->toBe([]);
});

test('otherCurrencyExpenses leaves out a currency that only has income in the range', function (): void {
    ['tallyIds' => $tallyIds, 'euro' => $euro, 'now' => $now] = seedMixedCurrencyTransactions();

    $result = (new TransactionStatsRepository)->otherCurrencyExpenses($tallyIds, $now->startOfMonth(), $now->setDate(2025, 5, 12), (int) $euro->currency_id);

    expect($result)->toBe([]);
});

test('activity returns the newest transactions of the range up to the limit, both ends included', function (): void {
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    foreach (['2026-09-30', '2026-10-01', '2026-10-05', '2026-10-09', '2026-10-14', '2026-10-15'] as $date) {
        Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
            ->create(['type' => 'income', 'amount' => 3.0, 'transaction_date' => $date, 'title' => $date]);
    }

    $result = (new TransactionStatsRepository)->activity(collect([$tally->tally_id]), CarbonImmutable::parse('2026-10-01'), CarbonImmutable::parse('2026-10-14 12:00:00'), 3);

    expect(array_column($result, 'title'))->toBe(['2026-10-14', '2026-10-09', '2026-10-05']);
});

test('activity keeps each row in its own currency and flags rows of multi-member tallies', function (): void {
    $user = User::factory()->create();
    $pound = Currency::factory()->create(['decimals' => 2, 'symbol' => '£', 'code' => 'GBP']);
    $solo = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Solo']);
    $solo->users()->attach($user->id, ['default_share_percentage' => 100]);
    $shared = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Shared']);
    $shared->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 50]);
    $food = Category::factory()->create(['name' => 'Food', 'slug' => 'food', 'color' => 'c4']);
    $soloTx = Transaction::factory()->forTally($solo)->forParty($user)->withCurrency($pound)->withCategory($food)
        ->create(['type' => 'expense', 'amount' => 12.40, 'transaction_date' => '2026-10-02', 'title' => 'Ramen']);
    Transaction::factory()->forTally($shared)->forParty($user)->withCurrency($pound)
        ->create(['type' => 'expense', 'amount' => 8.0, 'transaction_date' => '2026-10-01', 'title' => 'Tea']);

    $result = (new TransactionStatsRepository)->activity(collect([$solo->tally_id, $shared->tally_id]), CarbonImmutable::parse('2026-10-01'), CarbonImmutable::parse('2026-10-14'), 20);

    expect($result[0])->toBe([
        'uuid' => $soloTx->uuid,
        'title' => 'Ramen',
        'type' => 'expense',
        'amount' => 12.4,
        'transaction_date' => '2026-10-02',
        'category_id' => $food->getKey(),
        'category_name' => 'Food',
        'category_slug' => 'food',
        'category_color' => 'c4',
        'tally_uuid' => $solo->uuid,
        'tally_name' => 'Solo',
        'is_shared' => false,
        'currency_symbol' => '£',
        'currency_code' => 'GBP',
        'decimals' => 2,
    ])->and($result[1]['is_shared'])->toBeTrue();
});

// ─── frequentCategories ───────────────────────────────────────────────────────

/**
 * One transaction per date, all entered by $user in a tally of their own, in a seeded category.
 *
 * @param  array<int, string>  $dates
 */
function enterCategoryUses(User $user, string $slug, array $dates): void
{
    $category = Category::query()->where('slug', $slug)->firstOrFail();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    foreach ($dates as $date) {
        Transaction::factory()->forTally($tally)->forParty($user)->withCategory($category)
            ->withType(TransactionType::from($category->type->value))
            ->create(['transaction_date' => $date]);
    }
}

test('frequentCategories falls back to the design defaults of each type when nothing qualifies', function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $user = User::factory()->create();
    $repo = new TransactionStatsRepository;
    $now = CarbonImmutable::parse('2026-10-15');

    expect($repo->frequentCategories($user->id, CategoryType::EXPENSE, $now))
        ->toBe(['groceries', 'dining_out', 'transportation', 'bills', 'subscriptions'])
        ->and($repo->frequentCategories($user->id, CategoryType::INCOME, $now))
        ->toBe(['salary', 'side_work', 'refunds_returns', 'gifts_received', 'investments']);
});

test('frequentCategories needs a category used at least twice', function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $user = User::factory()->create();
    enterCategoryUses($user, 'healthcare', ['2026-10-01', '2026-10-02']);
    enterCategoryUses($user, 'travel', ['2026-10-03']);

    expect((new TransactionStatsRepository)->frequentCategories($user->id, CategoryType::EXPENSE, CarbonImmutable::parse('2026-10-15')))
        ->toBe(['healthcare', 'groceries', 'dining_out', 'transportation', 'bills']);
});

test('frequentCategories counts day 90 and ignores day 91 and the future', function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $user = User::factory()->create();
    enterCategoryUses($user, 'healthcare', ['2026-07-17', '2026-10-15']);
    enterCategoryUses($user, 'education', ['2026-07-17', '2026-07-16']);
    enterCategoryUses($user, 'travel', ['2026-10-16', '2026-10-17']);

    expect((new TransactionStatsRepository)->frequentCategories($user->id, CategoryType::EXPENSE, CarbonImmutable::parse('2026-10-15')))
        ->toBe(['healthcare', 'groceries', 'dining_out', 'transportation', 'bills']);
});

test('frequentCategories ranks by use count, then by the most recent use', function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $user = User::factory()->create();
    enterCategoryUses($user, 'education', ['2026-09-01', '2026-09-02']);
    enterCategoryUses($user, 'utilities', ['2026-10-01', '2026-10-02']);
    enterCategoryUses($user, 'healthcare', ['2026-08-01', '2026-08-02', '2026-08-03']);

    expect((new TransactionStatsRepository)->frequentCategories($user->id, CategoryType::EXPENSE, CarbonImmutable::parse('2026-10-15')))
        ->toBe(['healthcare', 'utilities', 'education', 'groceries', 'dining_out']);
});

test('frequentCategories returns at most five and the fill never repeats a qualifying default', function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $busy = User::factory()->create();

    foreach (['healthcare', 'education', 'utilities', 'travel', 'rent', 'insurance'] as $index => $slug) {
        enterCategoryUses($busy, $slug, ['2026-10-0' . ($index + 1), '2026-10-0' . ($index + 1)]);
    }

    $single = User::factory()->create();
    enterCategoryUses($single, 'groceries', ['2026-10-01', '2026-10-02']);
    $repo = new TransactionStatsRepository;
    $now = CarbonImmutable::parse('2026-10-15');

    expect($repo->frequentCategories($busy->id, CategoryType::EXPENSE, $now))
        ->toBe(['insurance', 'rent', 'travel', 'utilities', 'education'])
        ->and($repo->frequentCategories($single->id, CategoryType::EXPENSE, $now))
        ->toBe(['groceries', 'dining_out', 'transportation', 'bills', 'subscriptions']);
});

test('frequentCategories keeps income and expense apart', function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $user = User::factory()->create();
    enterCategoryUses($user, 'healthcare', ['2026-10-01', '2026-10-02']);
    enterCategoryUses($user, 'bonus', ['2026-10-03', '2026-10-04']);
    $repo = new TransactionStatsRepository;
    $now = CarbonImmutable::parse('2026-10-15');

    expect($repo->frequentCategories($user->id, CategoryType::EXPENSE, $now))
        ->toBe(['healthcare', 'groceries', 'dining_out', 'transportation', 'bills'])
        ->and($repo->frequentCategories($user->id, CategoryType::INCOME, $now))
        ->toBe(['bonus', 'salary', 'side_work', 'refunds_returns', 'gifts_received']);
});

test('frequentCategories ignores uncategorised transactions', function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->forTally($tally)->forParty($user)->withType(TransactionType::EXPENSE)->count(3)
        ->create(['category_id' => null, 'transaction_date' => '2026-10-01']);

    expect((new TransactionStatsRepository)->frequentCategories($user->id, CategoryType::EXPENSE, CarbonImmutable::parse('2026-10-15')))
        ->toBe(['groceries', 'dining_out', 'transportation', 'bills', 'subscriptions']);
});

test('frequentCategories never counts what another user entered, even in a shared tally', function (): void {
    $this->seed(ReferenceDataSeeder::class);
    $me = User::factory()->create();
    $other = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $tally->users()->attach([$me->id => ['default_share_percentage' => 50], $other->id => ['default_share_percentage' => 50]]);
    $category = Category::query()->where('slug', 'healthcare')->firstOrFail();
    Transaction::factory()->forTally($tally)->forParty($other)->withCategory($category)->withType(TransactionType::EXPENSE)->count(5)
        ->create(['transaction_date' => '2026-10-01']);
    $repo = new TransactionStatsRepository;
    $now = CarbonImmutable::parse('2026-10-15');

    expect($repo->frequentCategories($me->id, CategoryType::EXPENSE, $now))
        ->toBe(['groceries', 'dining_out', 'transportation', 'bills', 'subscriptions'])
        ->and($repo->frequentCategories($other->id, CategoryType::EXPENSE, $now)[0])->toBe('healthcare');
});
