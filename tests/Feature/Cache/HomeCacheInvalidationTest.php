<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

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
    Cache::put("home:user:{$userId}:recent_tx", ['some' => 'cached'], 60);
    Cache::put("home:user:{$userId}:balance:{$dayKey}", ['net' => 100.0], 60);

    Transaction::factory()
        ->forTally($tally)
        ->forParty($user)
        ->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => now()->toDateString()]);

    expect(Cache::has("home:user:{$userId}:recent_tx"))->toBeFalse()
        ->and(Cache::has("home:user:{$userId}:balance:{$dayKey}"))->toBeFalse();
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

    Cache::put("home:user:{$userId}:recent_tx", ['cached'], 60);

    $tx->update(['title' => 'Updated']);

    expect(Cache::has("home:user:{$userId}:recent_tx"))->toBeFalse();
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

    Cache::put("home:user:{$userId}:recent_tx", ['cached'], 60);

    $tx->delete();

    expect(Cache::has("home:user:{$userId}:recent_tx"))->toBeFalse();
});

test('creating a tally invalidates the owner cache', function (): void {
    $user = User::factory()->create();
    $userId = (int) $user->id;

    Cache::put("home:user:{$userId}:tally_ids", collect([1, 2]), 60);

    Tally::factory()->create(['owner_id' => $user->id]);

    expect(Cache::has("home:user:{$userId}:tally_ids"))->toBeFalse();
});

test('updating a tally invalidates the owner cache', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $userId = (int) $user->id;
    $now = CarbonImmutable::now();
    $monthKey = $now->format('Y-m');

    Cache::put("home:user:{$userId}:tallies_summary:{$monthKey}", ['data'], 60);

    $tally->update(['name' => 'Renamed']);

    expect(Cache::has("home:user:{$userId}:tallies_summary:{$monthKey}"))->toBeFalse();
});

test('deleting a tally invalidates the owner cache', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $userId = (int) $user->id;

    Cache::put("home:user:{$userId}:tally_ids", collect([1]), 60);

    $tally->delete();

    expect(Cache::has("home:user:{$userId}:tally_ids"))->toBeFalse();
});

test('attaching a user to a tally invalidates the member cache', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $memberId = (int) $member->id;

    Cache::put("home:user:{$memberId}:tally_ids", collect([]), 60);

    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);

    expect(Cache::has("home:user:{$memberId}:tally_ids"))->toBeFalse();
});

test('detaching a user from a tally invalidates the member cache', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);

    $memberId = (int) $member->id;
    Cache::put("home:user:{$memberId}:tally_ids", collect([99]), 60);

    $tally->users()->detach($member->id);

    expect(Cache::has("home:user:{$memberId}:tally_ids"))->toBeFalse();
});
