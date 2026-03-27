<?php

declare(strict_types=1);

use App\Actions\TallyShare\DeleteTallyShareAction;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(DeleteTallyShareAction::class);

it('removes a user and redistributes their share percentage', function () {
    $tally = Tally::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $tally->users()->attach([
        $user1->id => ['default_share_percentage' => 40],
        $user2->id => ['default_share_percentage' => 30],
        $user3->id => ['default_share_percentage' => 30],
    ]);

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user1);

    expect($tally->users()->where('user_id', $user1->id)->exists())->toBeFalse();

    // Total remaining was 30 + 30 = 60.
    // User 1 had 40.
    // User 2: 30 + (30 / 60 * 40) = 30 + 20 = 50.
    // User 3: 30 + (30 / 60 * 40) = 30 + 20 = 50.

    $user2Pivot = $tally->users()->where('user_id', $user2->id)->first()->pivot;
    $user3Pivot = $tally->users()->where('user_id', $user3->id)->first()->pivot;

    expect($user2Pivot->default_share_percentage)->toBe(50.0);
    expect($user3Pivot->default_share_percentage)->toBe(50.0);
});

it('does nothing if user is not part of the tally', function () {
    $tally = Tally::factory()->create();
    $user = User::factory()->create();

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user);

    expect($tally->users()->count())->toBe(0);
});

it('does nothing if no users remain after deletion', function () {
    $tally = Tally::factory()->create();
    $user = User::factory()->create();
    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user);

    expect($tally->users()->count())->toBe(0);
});

it('does not redistribute if revoked percentage is zero', function () {
    $tally = Tally::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $tally->users()->attach([
        $user1->id => ['default_share_percentage' => 0],
        $user2->id => ['default_share_percentage' => 100],
    ]);

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user1);

    expect($tally->users()->where('user_id', $user1->id)->exists())->toBeFalse();
    expect($tally->users()->where('user_id', $user2->id)->first()->pivot->default_share_percentage)->toBe(100.0);
});

it('redistributes equally if total current percentage of remaining users is zero', function () {
    $tally = Tally::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $tally->users()->attach([
        $user1->id => ['default_share_percentage' => 100],
        $user2->id => ['default_share_percentage' => 0],
        $user3->id => ['default_share_percentage' => 0],
    ]);

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user1);

    expect($tally->users()->where('user_id', $user1->id)->exists())->toBeFalse();

    $user2Pivot = $tally->users()->where('user_id', $user2->id)->first()->pivot;
    $user3Pivot = $tally->users()->where('user_id', $user3->id)->first()->pivot;

    expect($user2Pivot->default_share_percentage)->toBe(50.0)
        ->and($user3Pivot->default_share_percentage)->toBe(50.0);
});

it('returns early if user has no pivot data', function () {
    $tally = mock(Tally::class);
    $user = User::factory()->create();
    $userToRevoke = User::factory()->make();
    $userToRevoke->pivot = null;

    $relation = mock(BelongsToMany::class);
    $relation->shouldReceive('where')->andReturnSelf();
    $relation->shouldReceive('first')->andReturn($userToRevoke);

    $tally->shouldReceive('users')->andReturn($relation);

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user);

    expect(true)->toBeTrue();
});

it('skips remaining users with no pivot data', function () {
    $tally = mock(Tally::class);
    $user = User::factory()->create();

    $userToRevoke = User::factory()->make();
    $userToRevoke->pivot = (object) ['default_share_percentage' => 50];

    $remainingUser = User::factory()->make();
    $remainingUser->pivot = null;

    $relation = mock(BelongsToMany::class);
    $relation->shouldReceive('where')->andReturnSelf();
    $relation->shouldReceive('first')->andReturn($userToRevoke);
    $relation->shouldReceive('detach')->andReturn(1);
    $relation->shouldReceive('get')->andReturn(collect([$remainingUser]));
    $relation->shouldReceive('sync')->with([])->andReturn([]);

    $tally->shouldReceive('users')->andReturn($relation);

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user);

    expect(true)->toBeTrue();
});

it('returns early and does not sync if revoked percentage is zero', function () {
    $tally = mock(Tally::class);
    $user = User::factory()->create();

    $userToRevoke = User::factory()->make();
    $userToRevoke->pivot = (object) ['default_share_percentage' => '0.0'];

    $relation = mock(BelongsToMany::class);
    $relation->shouldReceive('where')->andReturnSelf();
    $relation->shouldReceive('first')->andReturn($userToRevoke);

    $relation->shouldReceive('detach')->once();
    $relation->shouldReceive('get')->once()->andReturn(collect([User::factory()->make(['pivot' => (object) ['default_share_percentage' => '100.0']])]));
    $relation->shouldNotReceive('sync');

    $tally->shouldReceive('users')->andReturn($relation);

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user);
});

it('handles small total current percentage correctly', function () {
    $tally = Tally::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $tally->users()->attach([
        $user1->id => ['default_share_percentage' => 99.5],
        $user2->id => ['default_share_percentage' => 0.2],
        $user3->id => ['default_share_percentage' => 0.3],
    ]);

    $action = new DeleteTallyShareAction();
    $action->handle($tally, $user1);

    $user2Pivot = $tally->users()->where('user_id', $user2->id)->first()->pivot;
    $user3Pivot = $tally->users()->where('user_id', $user3->id)->first()->pivot;

    expect($user2Pivot->default_share_percentage)->toBe(40.0);
    expect($user3Pivot->default_share_percentage)->toBe(60.0);
});
