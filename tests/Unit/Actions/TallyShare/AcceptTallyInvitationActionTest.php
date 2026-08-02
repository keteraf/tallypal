<?php

declare(strict_types=1);

use App\Actions\TallyShare\AcceptTallyInvitationAction;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

covers(AcceptTallyInvitationAction::class);

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $failure = databaseFailure();
    $invitation = Mockery::mock(TallyInvitation::class)->makePartial();
    $invitation->shouldReceive('tally')->andThrow($failure);
    $user = User::factory()->create();
    expect(fn () => (new AcceptTallyInvitationAction())->handle($invitation, $user))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});

test('an invitation whose tally no longer exists stays a 404', function (): void {
    Exceptions::fake();
    $invitation = Mockery::mock(TallyInvitation::class)->makePartial();
    $invitation->shouldReceive('tally->firstOrFail')->andThrow(new ModelNotFoundException());
    $user = User::factory()->create();

    expect(fn () => (new AcceptTallyInvitationAction())->handle($invitation, $user))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(404);
        });

    Exceptions::assertNothingReported();
});
