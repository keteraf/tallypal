<?php

declare(strict_types=1);

use App\Actions\TallyShare\CreateTallyShareAction;
use App\Mail\TallyInvitationExistingUser;
use App\Mail\TallyInvitationNewUser;
use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

covers(CreateTallyShareAction::class);

it('creates a pending invitation for an existing user', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $action = new CreateTallyShareAction();
    $invitation = $action->handle($tally, $invitee->email);

    expect($invitation)->toBeInstanceOf(TallyInvitation::class)
        ->and($invitation->email)->toBe('invitee@example.com')
        ->and($invitation->status)->toBe(TallyInvitation::STATUS_PENDING)
        ->and($invitation->token)->not->toBeEmpty()
        ->and($invitation->expires_at)->not->toBeNull();

    expect($tally->invitations()->where('email', $invitee->email)->exists())->toBeTrue();
    expect($tally->users()->where('user_id', $invitee->id)->exists())->toBeFalse();
});

it('sends the existing user mailable when invitee is registered', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $invitee = User::factory()->create(['email' => 'existing@example.com']);

    $action = new CreateTallyShareAction();
    $action->handle($tally, $invitee->email);

    Mail::assertQueued(TallyInvitationExistingUser::class, fn ($mail) => $mail->hasTo($invitee->email));
    Mail::assertNotQueued(TallyInvitationNewUser::class);
});

it('sends the new user mailable when invitee is not registered', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $action = new CreateTallyShareAction();
    $action->handle($tally, 'newuser@example.com');

    Mail::assertQueued(TallyInvitationNewUser::class, fn ($mail) => $mail->hasTo('newuser@example.com'));
    Mail::assertNotQueued(TallyInvitationExistingUser::class);
});

it('creates a pending invitation for a non-registered email', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $action = new CreateTallyShareAction();
    $action->handle($tally, 'newuser@example.com');

    expect($tally->invitations()->where('email', 'newuser@example.com')->exists())->toBeTrue();
    expect($tally->users()->count())->toBe(0);
});

it('creates the invitation with a 64-character token', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $action = new CreateTallyShareAction();
    $invitation = $action->handle($tally, 'someone@example.com');

    expect(mb_strlen($invitation->token))->toBe(64);
});

it('creates the invitation expiring in exactly 7 days', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $action = new CreateTallyShareAction();
    $invitation = $action->handle($tally, 'someone@example.com');

    expect($invitation->expires_at->toDateString())->toBe(now()->addDays(7)->toDateString());
});

it('returns the invitation with tally and invitedBy relations already loaded', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $action = new CreateTallyShareAction();
    $invitation = $action->handle($tally, 'someone@example.com');

    expect($invitation->relationLoaded('tally'))->toBeTrue()
        ->and($invitation->relationLoaded('invitedBy'))->toBeTrue();
});

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $failure = databaseFailure();
    $tally = Mockery::mock(Tally::class)->makePartial();
    $tally->setRelation('owner', User::factory()->make());
    $tally->shouldReceive('invitations')->andThrow($failure);
    expect(fn () => (new CreateTallyShareAction())->handle($tally, 'invitee@example.com'))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});
