<?php

declare(strict_types=1);

use App\Console\Commands\ResetActivityCommand;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\Transaction;
use App\Models\User;
use App\Services\HomeCacheService;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

covers(ResetActivityCommand::class);

function seedActivity(): User
{
    $user = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $tally->users()->attach($user->id, ['default_share_percentage' => 50.0]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50.0]);
    Transaction::factory()->forTally($tally)->forParty($user)->count(3)->create();
    TallyInvitation::query()->create([
        'tally_id' => $tally->tally_id,
        'invited_by_user_id' => $user->id,
        'email' => 'guest@example.com',
        'token' => str_repeat('a', 64),
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDay(),
    ]);

    return $user;
}

it('refuses to run unless ALLOW_ACTIVITY_RESET is set', function (): void {
    config(['app.allow_activity_reset' => false]);
    seedActivity();

    $this->artisan('tallypal:reset-activity', ['--force' => true])
        ->expectsOutputToContain('ALLOW_ACTIVITY_RESET=true')
        ->assertExitCode(1);

    expect(Tally::query()->count())->toBe(1)
        ->and(Transaction::query()->count())->toBe(3);
});

it('only prints the counts and exits 1 without --force', function (): void {
    config(['app.allow_activity_reset' => true]);
    seedActivity();

    $this->artisan('tallypal:reset-activity')
        ->expectsOutputToContain('Transactions: 3')
        ->expectsOutputToContain('Tally invitations: 1')
        ->expectsOutputToContain('Memberships: 2')
        ->expectsOutputToContain('Tallies: 1')
        ->assertExitCode(1);

    expect(Tally::query()->count())->toBe(1)
        ->and(Transaction::query()->count())->toBe(3)
        ->and(TallyInvitation::query()->count())->toBe(1)
        ->and(DB::table('tally_user')->count())->toBe(2);
});

it('wipes the activity and keeps users and reference data with --force', function (): void {
    config(['app.allow_activity_reset' => true]);
    $this->seed(ReferenceDataSeeder::class);
    seedActivity();
    $users = User::query()->count();

    $this->artisan('tallypal:reset-activity', ['--force' => true])
        ->expectsOutputToContain('Transactions: 3')
        ->assertExitCode(0);

    expect(Transaction::query()->count())->toBe(0)
        ->and(TallyInvitation::query()->count())->toBe(0)
        ->and(DB::table('tally_user')->count())->toBe(0)
        ->and(Tally::query()->count())->toBe(0)
        ->and(User::query()->count())->toBe($users)
        ->and(Category::query()->count())->toBe(27)
        ->and(Currency::query()->count())->toBe(10);
});

it('clears every user Home cache and rotates their sidebar token', function (): void {
    config(['app.allow_activity_reset' => true]);
    $user = seedActivity();
    $other = User::factory()->create();
    $service = resolve(HomeCacheService::class);
    $tokenBefore = $service->sidebarVersion($user->id);
    $otherTokenBefore = $service->sidebarVersion($other->id);
    Cache::put(HomeCacheService::key($other->id, 'recent_tx'), ['stale'], 600);

    $this->artisan('tallypal:reset-activity', ['--force' => true])->assertExitCode(0);

    expect($service->sidebarVersion($user->id))->not->toBe($tokenBefore)
        ->and($service->sidebarVersion($other->id))->not->toBe($otherTokenBefore)
        ->and(Cache::has(HomeCacheService::key($other->id, 'recent_tx')))->toBeFalse();
});
