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
