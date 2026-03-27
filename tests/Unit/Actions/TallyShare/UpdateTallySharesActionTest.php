<?php

declare(strict_types=1);

use App\Actions\TallyShare\UpdateTallySharesAction;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(UpdateTallySharesAction::class);

it('updates share percentages for multiple users', function () {
    $tally = Tally::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $shares = [
        ['user_id' => $user1->id, 'percentage' => 60],
        ['user_id' => $user2->id, 'percentage' => 40],
    ];

    $action = new UpdateTallySharesAction();
    $action->handle($tally, $shares);

    $user1Pivot = $tally->users()->where('user_id', $user1->id)->first()->pivot;
    $user2Pivot = $tally->users()->where('user_id', $user2->id)->first()->pivot;

    expect((float) $user1Pivot->default_share_percentage)->toBe(60.0);
    expect((float) $user2Pivot->default_share_percentage)->toBe(40.0);
});

it('detaches users not included in the shares array', function () {
    $tally = Tally::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $tally->users()->attach([
        $user1->id => ['default_share_percentage' => 33],
        $user2->id => ['default_share_percentage' => 33],
        $user3->id => ['default_share_percentage' => 34],
    ]);

    $shares = [
        ['user_id' => $user1->id, 'percentage' => 60],
        ['user_id' => $user2->id, 'percentage' => 40],
    ];

    $action = new UpdateTallySharesAction();
    $action->handle($tally, $shares);

    expect($tally->users()->count())->toBe(2);
    expect($tally->users()->where('user_id', $user3->id)->exists())->toBeFalse();
});
