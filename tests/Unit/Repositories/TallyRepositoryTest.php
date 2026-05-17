<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Eloquent\TallyRepository;
use Carbon\CarbonImmutable;

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

test('accessibleTalliesWithTotals computes income and expense totals', function (): void {
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
    expect($found->income_total)->toBe(10.0)
        ->and($found->expense_total)->toBe(3.5);
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
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), $monthStart);

    expect($summaries)->toHaveCount(1);
    $summary = $summaries->first();
    expect($summary['uuid'])->toBe((string) $tally->uuid)
        ->and($summary['name'])->toBe('My Budget')
        ->and($summary)->toHaveKeys(['uuid', 'name', 'member_count', 'monthly_total']);
});

test('tallySummaries member_count equals 1 for owner-only tally', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $monthStart = CarbonImmutable::now()->startOfMonth();

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), $monthStart);

    expect($summaries->first()['member_count'])->toBe(1);
});

test('tallySummaries adds 1 to member count when user is owner', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);
    $monthStart = CarbonImmutable::now()->startOfMonth();

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $owner->id, collect([$tally->tally_id]), $monthStart);

    // 1 shared member (in users table) + 1 for owner = 2
    expect($summaries->first()['member_count'])->toBe(2);
});

test('tallySummaries monthly_total reflects current month expenses', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $monthStart = CarbonImmutable::now()->startOfMonth();

    Transaction::factory()->forTally($tally)->forParty($user)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 75.0, 'transaction_date' => now()->toDateString()]);

    $repo = new TallyRepository;
    $summaries = $repo->tallySummaries((int) $user->id, collect([$tally->tally_id]), $monthStart);

    expect($summaries->first()['monthly_total'])->toBe(75.0);
});

test('sharedTallySummary returns null when no shared tallies exist', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $now = CarbonImmutable::now();

    $repo = new TallyRepository;
    $result = $repo->sharedTallySummary(collect([$tally->tally_id]), $now->startOfMonth(), $now);

    expect($result)->toBeNull();
});

test('sharedTallySummary returns uuid and name', function (): void {
    $owner = User::factory()->create();
    $collab = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Lisbon trip']);
    $tally->users()->attach($collab->id, ['default_share_percentage' => 50]);
    $now = CarbonImmutable::now();

    $repo = new TallyRepository;
    $result = $repo->sharedTallySummary(collect([$tally->tally_id]), $now->startOfMonth(), $now);

    expect($result)->not->toBeNull()
        ->and($result['uuid'])->toBe((string) $tally->uuid)
        ->and($result['name'])->toBe('Lisbon trip');
});

test('sharedTallySummary returns members and spending data', function (): void {
    $owner = User::factory()->create();
    $collab = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($collab->id, ['default_share_percentage' => 50]);

    $now = CarbonImmutable::now();

    Transaction::factory()
        ->forTally($tally)
        ->forParty($owner)
        ->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 100.0, 'transaction_date' => $now->toDateString()]);

    $repo = new TallyRepository;
    $result = $repo->sharedTallySummary(collect([$tally->tally_id]), $now->startOfMonth(), $now);

    expect($result)->not->toBeNull()
        ->and($result['total_spent'])->toBe(100.0)
        ->and($result['members'])->toHaveCount(2);

    $member = collect($result['members'])->firstWhere('name', $owner->name);
    expect($member['amount'])->toBe(100.0)
        ->and($member['percentage'])->toBe(100.0);

    // Members must have all required keys
    expect($result['members'][0])->toHaveKeys(['name', 'initials', 'amount', 'percentage']);
});

test('sharedTallySummary generates initials from member names', function (): void {
    $owner = User::factory()->create(['name' => 'John Doe']);
    $collab = User::factory()->create(['name' => 'Jane Smith']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($collab->id, ['default_share_percentage' => 50]);
    $now = CarbonImmutable::now();

    $repo = new TallyRepository;
    $result = $repo->sharedTallySummary(collect([$tally->tally_id]), $now->startOfMonth(), $now);

    $ownerMember = collect($result['members'])->firstWhere('name', 'John Doe');
    $collabMember = collect($result['members'])->firstWhere('name', 'Jane Smith');

    expect($ownerMember['initials'])->toBe('JD')
        ->and($collabMember['initials'])->toBe('JS');
});

test('sharedTallySummary percentage is zero when total_spent is zero', function (): void {
    $owner = User::factory()->create(['name' => '123']);
    $collab = User::factory()->create(['name' => '456']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($collab->id, ['default_share_percentage' => 50]);

    $now = CarbonImmutable::now();

    // No transactions — total_spent is zero
    $repo = new TallyRepository;
    $result = $repo->sharedTallySummary(collect([$tally->tally_id]), $now->startOfMonth(), $now);

    expect($result)->not->toBeNull()
        ->and($result['total_spent'])->toBe(0.0)
        ->and($result['members'][0]['percentage'])->toBe(0.0);
});

test('initials uses double-dot for name with only non-letter characters via shared summary', function (): void {
    $owner = User::factory()->create(['name' => '  ']);
    $collab = User::factory()->create(['name' => 'Regular Name']);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($collab->id, ['default_share_percentage' => 50]);

    $now = CarbonImmutable::now();

    $repo = new TallyRepository;
    $result = $repo->sharedTallySummary(collect([$tally->tally_id]), $now->startOfMonth(), $now);

    expect($result)->not->toBeNull();
    $ownerMember = collect($result['members'])->firstWhere('name', $owner->name);
    expect($ownerMember['initials'])->toBe('··');
});
