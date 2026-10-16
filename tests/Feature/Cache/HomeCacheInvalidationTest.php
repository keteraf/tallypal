<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\TallyUser;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\TallyObserver;
use App\Observers\TallyUserObserver;
use App\Observers\TransactionObserver;
use App\Services\HomeCacheService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

covers(TallyObserver::class, TallyUserObserver::class, TransactionObserver::class);

beforeEach(function (): void {
    Cache::flush();
});

test('creating a transaction invalidates the cache for the party user', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $userId = (int) $user->id;
    $now = CarbonImmutable::now();
    $dayKey = $now->format('Y-m-d');

    // Pre-seed a cache key
    Cache::put(HomeCacheService::key($userId, 'recent_tx'), ['some' => 'cached'], 60);
    Cache::put(HomeCacheService::key($userId, "balance:{$dayKey}"), ['net' => 100.0], 60);

    Transaction::factory()
        ->forTally($tally)
        ->forParty($user)
        ->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => now()->toDateString()]);

    expect(Cache::has(HomeCacheService::key($userId, 'recent_tx')))->toBeFalse()
        ->and(Cache::has(HomeCacheService::key($userId, "balance:{$dayKey}")))->toBeFalse();
});

test('updating a transaction invalidates the cache', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $userId = (int) $user->id;

    $tx = Transaction::factory()
        ->forTally($tally)
        ->forParty($user)
        ->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => now()->toDateString()]);

    Cache::put(HomeCacheService::key($userId, 'recent_tx'), ['cached'], 60);

    $tx->update(['title' => 'Updated']);

    expect(Cache::has(HomeCacheService::key($userId, 'recent_tx')))->toBeFalse();
});

test('deleting a transaction invalidates the cache', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $userId = (int) $user->id;

    $tx = Transaction::factory()
        ->forTally($tally)
        ->forParty($user)
        ->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => now()->toDateString()]);

    Cache::put(HomeCacheService::key($userId, 'recent_tx'), ['cached'], 60);

    $tx->delete();

    expect(Cache::has(HomeCacheService::key($userId, 'recent_tx')))->toBeFalse();
});

test('creating a tally invalidates the owner cache', function (): void {
    $user = User::factory()->create();
    $userId = (int) $user->id;

    Cache::put(HomeCacheService::key($userId, 'tally_ids'), collect([1, 2]), 60);

    Tally::factory()->create(['owner_id' => $user->id]);

    expect(Cache::has(HomeCacheService::key($userId, 'tally_ids')))->toBeFalse();
});

test('updating a tally invalidates the owner cache', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $userId = (int) $user->id;
    $now = CarbonImmutable::now();
    $monthKey = $now->format('Y-m');

    Cache::put(HomeCacheService::key($userId, "tallies_summary:{$monthKey}"), ['data'], 60);

    $tally->update(['name' => 'Renamed']);

    expect(Cache::has(HomeCacheService::key($userId, "tallies_summary:{$monthKey}")))->toBeFalse();
});

test('deleting a tally invalidates the owner cache', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $userId = (int) $user->id;

    Cache::put(HomeCacheService::key($userId, 'tally_ids'), collect([1]), 60);

    $tally->delete();

    expect(Cache::has(HomeCacheService::key($userId, 'tally_ids')))->toBeFalse();
});

test('attaching a user to a tally invalidates the member cache', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $memberId = (int) $member->id;

    Cache::put(HomeCacheService::key($memberId, 'tally_ids'), collect([]), 60);

    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);

    expect(Cache::has(HomeCacheService::key($memberId, 'tally_ids')))->toBeFalse();
});

test('detaching a user from a tally invalidates the member cache', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);

    $memberId = (int) $member->id;
    Cache::put(HomeCacheService::key($memberId, 'tally_ids'), collect([99]), 60);

    $tally->users()->detach($member->id);

    expect(Cache::has(HomeCacheService::key($memberId, 'tally_ids')))->toBeFalse();
});

test('a transaction by one member flushes the balances of every member', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);
    Cache::put(HomeCacheService::key((int) $owner->id, 'balances'), ['cached'], 60);
    Cache::put(HomeCacheService::key((int) $member->id, 'balances'), ['cached'], 60);

    Transaction::factory()->forTally($tally)->forParty($member)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => now()->toDateString()]);

    expect(Cache::has(HomeCacheService::key((int) $owner->id, 'balances')))->toBeFalse()
        ->and(Cache::has(HomeCacheService::key((int) $member->id, 'balances')))->toBeFalse();
});

test('changing the shares flushes the balances of every member', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);
    Cache::put(HomeCacheService::key((int) $owner->id, 'balances'), ['cached'], 60);
    Cache::put(HomeCacheService::key((int) $member->id, 'balances'), ['cached'], 60);

    (new App\Actions\TallyShare\UpdateTallySharesAction())->handle($tally, [
        ['user_id' => $owner->id, 'percentage' => 70],
        ['user_id' => $member->id, 'percentage' => 30],
    ]);

    expect(Cache::has(HomeCacheService::key((int) $owner->id, 'balances')))->toBeFalse()
        ->and(Cache::has(HomeCacheService::key((int) $member->id, 'balances')))->toBeFalse();
});

test('removing a member flushes the balances of the members who stay', function (): void {
    $owner = User::factory()->create();
    $stayer = User::factory()->create();
    $leaver = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 40]);
    $tally->users()->attach($stayer->id, ['default_share_percentage' => 30]);
    $tally->users()->attach($leaver->id, ['default_share_percentage' => 30]);
    foreach ([$owner, $stayer, $leaver] as $user) {
        Cache::put(HomeCacheService::key((int) $user->id, 'balances'), ['cached'], 60);
    }

    (new App\Actions\TallyShare\DeleteTallyShareAction())->handle($tally, $leaver);

    foreach ([$owner, $stayer, $leaver] as $user) {
        expect(Cache::has(HomeCacheService::key((int) $user->id, 'balances')))->toBeFalse();
    }
});

test('a transaction forgets the cache of a party who is not a member of the tally', function (): void {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $currency = Currency::factory()->create(['decimals' => 2]);

    Cache::put(HomeCacheService::key((int) $outsider->id, 'recent_tx'), ['cached'], 60);

    Transaction::factory()->forTally($tally)->forParty($outsider)->withCurrency($currency)->create();

    expect(Cache::has(HomeCacheService::key((int) $outsider->id, 'recent_tx')))->toBeFalse();
});

test('joining a tally forgets the cache of the joining user and of every other member', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $joiner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);

    $keys = collect([$owner, $member, $joiner])
        ->map(fn (User $user): string => HomeCacheService::key((int) $user->id, 'recent_tx'));
    $keys->each(fn (string $key) => Cache::put($key, ['cached'], 60));

    $tally->users()->attach($joiner->id, ['default_share_percentage' => 0]);

    expect($keys->map(fn (string $key): bool => Cache::has($key))->all())->toBe([false, false, false]);
});

test('the tally user observer ignores a membership whose tally no longer exists', function (): void {
    $user = User::factory()->create();
    $tallyUser = new TallyUser;
    $tallyUser->setAttribute('user_id', $user->id);
    $tallyUser->setAttribute(Tally::PRIMARY_KEY, 999_999);
    Cache::put(HomeCacheService::key((int) $user->id, 'recent_tx'), ['cached'], 60);

    resolve(TallyUserObserver::class)->deleted($tallyUser);

    expect(Cache::has(HomeCacheService::key((int) $user->id, 'recent_tx')))->toBeFalse();
});

test('updating a tally forgets the cache of its owner and members', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);
    $ownerKey = HomeCacheService::key((int) $owner->id, 'recent_tx');
    $memberKey = HomeCacheService::key((int) $member->id, 'recent_tx');
    Cache::put($ownerKey, ['cached'], 60);
    Cache::put($memberKey, ['cached'], 60);

    $tally->update(['name' => 'Renamed']);

    expect(Cache::has($ownerKey))->toBeFalse()->and(Cache::has($memberKey))->toBeFalse();
});
