<?php

declare(strict_types=1);

use App\Actions\TallyShare\CreateTallyShareAction;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(CreateTallyShareAction::class);

it('adds a user to a tally by email', function () {
    $tally = Tally::factory()->create();
    $user = User::factory()->create(['email' => 'test@example.com']);

    $action = new CreateTallyShareAction();
    $action->handle($tally, 'test@example.com');

    expect($tally->users()->where('user_id', $user->id)->exists())->toBeTrue();
    expect($tally->users()->find($user->id)->pivot->default_share_percentage)->toBe(0.0);
});

it('throws exception when user is not found by email', function () {
    $tally = Tally::factory()->create();

    $action = new CreateTallyShareAction();

    expect(fn () => $action->handle($tally, 'nonexistent@example.com'))
        ->toThrow(ModelNotFoundException::class);
});

it('does not detach existing users', function () {
    $tally = Tally::factory()->create();
    $existingUser = User::factory()->create();
    $tally->users()->attach($existingUser->id, ['default_share_percentage' => 100]);

    $newUser = User::factory()->create(['email' => 'new@example.com']);

    $action = new CreateTallyShareAction();
    $action->handle($tally, 'new@example.com');

    expect($tally->users()->count())->toBe(2);
    expect($tally->users()->where('user_id', $existingUser->id)->exists())->toBeTrue();
    expect($tally->users()->where('user_id', $newUser->id)->exists())->toBeTrue();
});
