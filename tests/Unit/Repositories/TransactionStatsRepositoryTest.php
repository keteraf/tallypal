<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TransactionStatsRepository;
use Carbon\CarbonImmutable;

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

test('recentTransactions category_code uses first two letters of category name', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $today = now()->toDateString();

    $category = App\Models\Category::create(['name' => 'Groceries']);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 5.0, 'title' => 'Item', 'transaction_date' => $today, 'category_id' => $category->getKey()]);

    $repo = new TransactionStatsRepository;
    $result = $repo->recentTransactions(collect([$tally->tally_id]));

    $row = collect($result)->firstWhere('title', 'Item');
    expect($row['category_code'])->toBe('GR')
        ->and($row['category_name'])->toBe('Groceries');
});

test('recentTransactions category_code is double-dot for numeric-only category names', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $category = App\Models\Category::create(['name' => '123']);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 5.0, 'title' => 'Numeric Cat', 'transaction_date' => now()->toDateString(), 'category_id' => $category->getKey()]);

    $repo = new TransactionStatsRepository;
    $result = $repo->recentTransactions(collect([$tally->tally_id]));

    $tx = collect($result)->firstWhere('title', 'Numeric Cat');
    expect($tx['category_code'])->toBe('··');
});

test('weeklyHistory returns exactly 12 weeks with correct label format', function (): void {
    $now = CarbonImmutable::now();
    $start = $now->startOfWeek()->subWeeks(11);

    $repo = new TransactionStatsRepository;
    $result = $repo->weeklyHistory(collect([]), $start, $now);

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
    $result = $repo->weeklyHistory(collect([]), $start, $now);

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
    $result = $repo->weeklyHistory(collect([$tally->tally_id]), $start, $now);

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

    $catA = App\Models\Category::create(['name' => 'FoodX']);
    $catB = App\Models\Category::create(['name' => 'TransportX']);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 60.0, 'transaction_date' => $now->toDateString(), 'category_id' => $catA->getKey()]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 40.0, 'transaction_date' => $now->toDateString(), 'category_id' => $catB->getKey()]);

    $repo = new TransactionStatsRepository;
    $result = $repo->categoriesBreakdown(
        collect([$tally->tally_id]),
        $now->startOfMonth(),
        $now,
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

    $result = $repo->categoriesBreakdown(collect([99999]), $now->startOfMonth(), $now);

    expect($result)->toBeEmpty();
});

test('categoriesBreakdown returns at most 6 categories', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::now();

    // Create 8 distinct categories and transactions
    for ($i = 1; $i <= 8; $i++) {
        $cat = App\Models\Category::create(['name' => "TestCat{$i}"]);
        Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
            ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => $now->toDateString(), 'category_id' => $cat->getKey()]);
    }

    $repo = new TransactionStatsRepository;
    $result = $repo->categoriesBreakdown(collect([$tally->tally_id]), $now->startOfMonth(), $now);

    expect($result)->toHaveCount(6);
});
