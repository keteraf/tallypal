<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\TallyRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

covers(TallyRepository::class);

test('accessibleTalliesWithTotals returns owned and shared tallies', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $owned = Tally::factory()->create(['owner_id' => $owner->id]);
    $shared = Tally::factory()->create(['owner_id' => $member->id]);
    $shared->users()->attach($owner->id, ['default_share_percentage' => 50]);
    Tally::factory()->create();

    $repo = new TallyRepository;
    $result = $repo->accessibleTalliesWithTotals((int) $owner->id);

    expect($result->pluck('tally_id')->toArray())
        ->toContain($owned->tally_id)
        ->toContain($shared->tally_id)
        ->toHaveCount(2);
});

test('accessibleTalliesWithTotals computes income and expense totals per currency', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'income', 'amount' => 10.0]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 3.5]);

    $repo = new TallyRepository;
    $result = $repo->accessibleTalliesWithTotals((int) $user->id);

    $found = $result->firstWhere('tally_id', $tally->tally_id);
    expect($found['totals'][0]['income'])->toBe(10.0)
        ->and($found['totals'][0]['expense'])->toBe(3.5);
});

test('accessibleTalliesWithTotals returns empty collection when user has no tallies', function (): void {
    $user = User::factory()->create();

    $repo = new TallyRepository;
    $result = $repo->accessibleTalliesWithTotals((int) $user->id);

    expect($result)->toBeEmpty();
});

test('accessibleTallyIds returns owned tallies', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $other = Tally::factory()->create();

    $repo = new TallyRepository;
    $ids = $repo->accessibleTallyIds((int) $user->id);

    expect($ids->toArray())->toContain($tally->tally_id)
        ->and($ids->toArray())->not->toContain($other->tally_id);
});

test('accessibleTallyIds returns shared tallies', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);

    $repo = new TallyRepository;
    $ids = $repo->accessibleTallyIds((int) $member->id);

    expect($ids->toArray())->toContain($tally->tally_id);
});

test('accessibleTallyIds returns empty collection when user has no tallies', function (): void {
    $user = User::factory()->create();

    $repo = new TallyRepository;
    $ids = $repo->accessibleTallyIds((int) $user->id);

    expect($ids)->toBeEmpty();
});

test('tallySummaries returns correct uuid and name', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'My Budget']);
    $monthStart = CarbonImmutable::now()->startOfMonth();

    Transaction::factory()
        ->forTally($tally)
        ->forParty($user)
        ->withCurrency($currency)
        ->create([
            'type' => 'expense',
            'amount' => 20.0,
            'transaction_date' => now()->toDateString(),
        ]);

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), $monthStart, $monthStart->endOfMonth());

    expect($summaries)->toHaveCount(1);
    $summary = $summaries->first();
    expect($summary['uuid'])->toBe((string) $tally->uuid)
        ->and($summary['name'])->toBe('My Budget')
        ->and($summary)->toHaveKeys(['uuid', 'name', 'member_count', 'monthly_totals']);
});

test('tallySummaries member_count equals 1 for owner-only tally', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $monthStart = CarbonImmutable::now()->startOfMonth();

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), $monthStart, $monthStart->endOfMonth());

    expect($summaries->first()['member_count'])->toBe(1);
});

test('tallySummaries adds 1 to member count when user is owner', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);
    $monthStart = CarbonImmutable::now()->startOfMonth();

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $owner->id, collect([$tally->tally_id]), $monthStart, $monthStart->endOfMonth());

    // 1 shared member (in users table) + 1 for owner = 2
    expect($summaries->first()['member_count'])->toBe(2);
});

test('tallySummaries counts the owner once when the owner is also in the pivot', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 60]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 40]);
    $monthStart = CarbonImmutable::now()->startOfMonth();

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $owner->id, collect([$tally->tally_id]), $monthStart, $monthStart->endOfMonth());

    expect($summaries->first()['member_count'])->toBe(2);
});

test('tallySummaries counts the owner for a member viewing a shared tally', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);
    $monthStart = CarbonImmutable::now()->startOfMonth();

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $member->id, collect([$tally->tally_id]), $monthStart, $monthStart->endOfMonth());

    expect($summaries->first()['member_count'])->toBe(2);
});

test('tallySummaries monthly_totals count only expenses dated within the month, in the tally currency', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $monthStart = CarbonImmutable::parse('2026-10-01 00:00:00');

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 75.0, 'transaction_date' => '2026-10-01', 'created_at' => '2026-09-30 23:00:00']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 40.0, 'transaction_date' => '2026-09-30', 'created_at' => '2026-10-02 10:00:00']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 25.0, 'transaction_date' => '2026-11-01', 'created_at' => '2026-10-02 10:00:00']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'income', 'amount' => 500.0, 'transaction_date' => '2026-10-05']);

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), $monthStart, $monthStart->endOfMonth());

    expect($summaries->first()['monthly_totals'])->toBe([
        ['amount' => 75.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
    ]);
});

test('tallySummaries monthly_totals is the month expense per currency, ignoring income, by transaction_date', function (): void {
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $dollar = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $monthStart = CarbonImmutable::parse('2026-10-01 00:00:00');

    Transaction::factory()->count(3)->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 40.0, 'transaction_date' => '2026-10-03']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'income', 'amount' => 100.0, 'transaction_date' => '2026-10-04']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'income', 'amount' => 900.0, 'transaction_date' => '2026-09-30', 'created_at' => '2026-10-02 10:00:00']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'income', 'amount' => 60.0, 'transaction_date' => '2026-10-06']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'expense', 'amount' => 60.0, 'transaction_date' => '2026-10-07']);

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), $monthStart, $monthStart->endOfMonth());

    expect($summaries->first()['monthly_totals'])->toBe([
        ['amount' => 120.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
        ['amount' => 60.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
    ]);
});

test('tallySummaries monthly_totals lists the most-used currency first', function (): void {
    $user = User::factory()->create();
    $euro = Currency::factory()->create(['decimals' => 2, 'symbol' => '€', 'code' => 'EUR']);
    $dollar = Currency::factory()->create(['decimals' => 2, 'symbol' => '$', 'code' => 'USD']);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $monthStart = CarbonImmutable::parse('2026-10-01 00:00:00');

    Transaction::factory()->count(2)->forTally($tally)->forParty($user)->withCurrency($dollar)
        ->create(['type' => 'expense', 'amount' => 1.0, 'transaction_date' => '2026-09-10']);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($euro)
        ->create(['type' => 'expense', 'amount' => 3.0, 'transaction_date' => '2026-10-02']);

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), $monthStart, $monthStart->endOfMonth());

    expect($summaries->first()['monthly_totals'])->toBe([
        ['amount' => 0.0, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
        ['amount' => 3.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
    ]);
});

test('tallySummaries monthly_totals is empty for a tally without transactions', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now());

    expect($summaries->first()['monthly_totals'])->toBe([]);
});

test('tallySummaries members_preview falls back to double-dot initials for a blank name', function (): void {
    $owner = User::factory()->create(['name' => '  ']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $summaries = (new TallyRepository)->tallySummaries((int) $owner->id, collect([$tally->tally_id]), CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now());

    expect($summaries->first()['members_preview'][0]['initials'])->toBe('··');
});

test('tallySummaries loads members with a constant number of queries', function (): void {
    $owner = User::factory()->create();
    $tallies = Tally::factory()->count(4)->create(['owner_id' => $owner->id]);
    $tallies->each(fn (Tally $tally) => $tally->users()->attach(User::factory()->count(2)->create()->pluck('id')->all(), ['default_share_percentage' => 50]));

    DB::enableQueryLog();
    (new TallyRepository)->tallySummaries((int) $owner->id, $tallies->pluck('tally_id'), CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now());

    expect(DB::getQueryLog())->toHaveCount(4);
});

test('filterOptions lists the given tallies by name and flags those with more than one member', function (): void {
    $owner = User::factory()->create();
    $zoo = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Zoo']);
    $zoo->users()->attach($owner->id, ['default_share_percentage' => 100]);
    $home = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Home']);
    $home->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 50]);
    Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Attic']);

    $options = (new TallyRepository)->filterOptions(collect([$zoo->tally_id, $home->tally_id]));

    expect($options->all())->toBe([
        ['tally_id' => $home->tally_id, 'uuid' => $home->uuid, 'name' => 'Home', 'is_shared' => true],
        ['tally_id' => $zoo->tally_id, 'uuid' => $zoo->uuid, 'name' => 'Zoo', 'is_shared' => false],
    ]);
});

test('accessibleTalliesWithTotals exposes each total with its currency symbol, code and decimals', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)->create(['type' => 'income', 'amount' => 10.0]);

    $found = (new TallyRepository)->accessibleTalliesWithTotals((int) $user->id)->firstWhere('tally_id', $tally->tally_id);

    expect($found['totals'])->toBe([
        ['income' => 10.0, 'expense' => 0.0, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
    ]);
});

test('tallySummaries hands out the member preview and the split label of each tally', function (): void {
    $owner = User::factory()->create(['name' => 'Anna Bianchi']);
    $member = User::factory()->create(['name' => 'Bruno Neri']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'color' => 'c2', 'glyph' => 'house']);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 60]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 40]);

    $summary = (new TallyRepository)->tallySummaries((int) $owner->id, collect([$tally->tally_id]), CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now())->first();

    expect($summary['members_preview'])->toBe([
        ['initials' => 'AB', 'color' => $owner->avatarColor()],
        ['initials' => 'BN', 'color' => $member->avatarColor()],
    ])
        ->and($summary['split_label'])->toBe('60 / 40')
        ->and($summary['color'])->toBe('c2')
        ->and($summary['glyph'])->toBe('house');
});

test('the split label', function (array $shares, ?string $label): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    foreach ($shares as $position => $share) {
        $user = $position === 0 ? $owner : User::factory()->create();
        $tally->users()->attach($user->id, ['default_share_percentage' => $share]);
    }

    $summary = (new TallyRepository)->tallySummaries((int) $owner->id, collect([$tally->tally_id]), CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now())->first();

    expect($summary['split_label'])->toBe($label);
})->with([
    'solo' => [[100], null],
    'equal two' => [[50, 50], null],
    'within a hundredth' => [[33.33, 33.33, 33.34], null],
    'just over a hundredth' => [[33.33, 33.30, 33.37], '33.33 / 33.3 / 33.37'],
    'one decimal' => [[62.5, 37.5], '62.5 / 37.5'],
    'with a zero share' => [[100, 0], '100 / 0'],
    'unequal' => [[70, 30], '70 / 30'],
    'a few hundredths apart' => [[50.02, 49.98], '50.02 / 49.98'],
    'a point apart' => [[50.5, 49.5], '50.5 / 49.5'],
]);
