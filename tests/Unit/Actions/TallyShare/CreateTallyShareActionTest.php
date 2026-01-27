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
});

it('throws exception when user is not found by email', function () {
    $tally = Tally::factory()->create();

    $action = new CreateTallyShareAction();

    expect(fn () => $action->handle($tally, 'nonexistent@example.com'))
        ->toThrow(ModelNotFoundException::class);
});
