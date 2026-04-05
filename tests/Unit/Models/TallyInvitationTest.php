<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(TallyInvitation::class);

it('has the correct STATUSES constant with all three values', function () {
    expect(TallyInvitation::STATUSES)->toHaveCount(3)
        ->toContain(TallyInvitation::STATUS_PENDING)
        ->toContain(TallyInvitation::STATUS_ACCEPTED)
        ->toContain(TallyInvitation::STATUS_DECLINED);
});

it('returns uuid as the unique id column', function () {
    $invitation = new TallyInvitation();
    expect($invitation->uniqueIds())->toBe(['uuid']);
});

it('casts expires_at to datetime', function () {
    $invitation = new TallyInvitation();
    expect($invitation->getCasts())->toHaveKey('expires_at');
    expect($invitation->getCasts()['expires_at'])->toBe('datetime');
});

it('is expired when expires_at is in the past', function () {
    $invitation = new TallyInvitation();
    $invitation->expires_at = now()->subDay();

    expect($invitation->isExpired())->toBeTrue();
});

it('is not expired when expires_at is null', function () {
    $invitation = new TallyInvitation();
    $invitation->expires_at = null;

    expect($invitation->isExpired())->toBeFalse();
});

it('is not expired when expires_at is in the future', function () {
    $invitation = new TallyInvitation();
    $invitation->expires_at = now()->addDay();

    expect($invitation->isExpired())->toBeFalse();
});

it('is pending when status is pending', function () {
    $invitation = new TallyInvitation();
    $invitation->status = TallyInvitation::STATUS_PENDING;

    expect($invitation->isPending())->toBeTrue();
});

it('is not pending when status is accepted', function () {
    $invitation = new TallyInvitation();
    $invitation->status = TallyInvitation::STATUS_ACCEPTED;

    expect($invitation->isPending())->toBeFalse();
});

it('is not pending when status is declined', function () {
    $invitation = new TallyInvitation();
    $invitation->status = TallyInvitation::STATUS_DECLINED;

    expect($invitation->isPending())->toBeFalse();
});

it('scopePending returns only pending invitations', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'pending@example.com',
        'token' => 'token-scope-pending',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => null,
    ]);

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'accepted@example.com',
        'token' => 'token-scope-accepted',
        'status' => TallyInvitation::STATUS_ACCEPTED,
        'expires_at' => null,
    ]);

    $results = TallyInvitation::query()->pending()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->email)->toBe('pending@example.com');
});

it('scopeNotExpired includes records with null expires_at', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'no-expiry@example.com',
        'token' => 'token-no-expiry',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => null,
    ]);

    $results = TallyInvitation::query()->notExpired()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->email)->toBe('no-expiry@example.com');
});

it('scopeNotExpired includes records with future expires_at', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'future@example.com',
        'token' => 'token-future',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $results = TallyInvitation::query()->notExpired()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->email)->toBe('future@example.com');
});

it('scopeNotExpired excludes records with past expires_at', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'expired@example.com',
        'token' => 'token-expired',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->subDay(),
    ]);

    $results = TallyInvitation::query()->notExpired()->get();

    expect($results)->toHaveCount(0);
});

it('belongs to a tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'test@example.com',
        'token' => 'token-tally',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    expect($invitation->tally())->toBeInstanceOf(BelongsTo::class)
        ->and($invitation->tally)->toBeInstanceOf(Tally::class);
});

it('belongs to an invited user by email', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => $owner->email,
        'token' => 'token-inviteduser',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    expect($invitation->invitedUser())->toBeInstanceOf(BelongsTo::class)
        ->and($invitation->invitedUser)->toBeInstanceOf(User::class);
});

it('belongs to the inviting user', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'test@example.com',
        'token' => 'token-invitedby',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    expect($invitation->invitedBy())->toBeInstanceOf(BelongsTo::class)
        ->and($invitation->invitedBy)->toBeInstanceOf(User::class);
});
