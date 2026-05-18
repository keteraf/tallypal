<?php

declare(strict_types=1);

use App\Mail\TallyInvitationExistingUser;
use App\Mail\TallyInvitationNewUser;
use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

covers(TallyInvitationExistingUser::class, TallyInvitationNewUser::class);

it('existing user mailable queues after commit', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'My Budget']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'token-existing-commit',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $mailable = new TallyInvitationExistingUser($invitation);

    expect($mailable->afterCommit)->toBeTrue();
});

it('existing user mailable has correct subject', function () {
    $owner = User::factory()->create(['name' => 'Inviter']);
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'My Budget']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'token-existing',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $mailable = new TallyInvitationExistingUser($invitation);

    expect($mailable->envelope()->subject)->toContain('My Budget');
});

it('existing user mailable has correct content', function () {
    $owner = User::factory()->create(['name' => 'Inviter']);
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'My Budget']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'token-existing-content',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $mailable = new TallyInvitationExistingUser($invitation);
    $content = $mailable->content();

    expect($content->markdown)->toBe('mail.tally.invitation-existing-user');
    expect($content->with)->toHaveKeys(['inviterName', 'tallyName', 'acceptUrl', 'declineUrl']);
    expect($content->with['inviterName'])->toBe('Inviter');
    expect($content->with['tallyName'])->toBe('My Budget');
});

it('new user mailable queues after commit', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'Family Budget']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'newuser@example.com',
        'token' => 'token-new-commit',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $mailable = new TallyInvitationNewUser($invitation);

    expect($mailable->afterCommit)->toBeTrue();
});

it('new user mailable has correct subject', function () {
    $owner = User::factory()->create(['name' => 'Inviter']);
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'Family Budget']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'newuser@example.com',
        'token' => 'token-new',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $mailable = new TallyInvitationNewUser($invitation);
    $subject = $mailable->envelope()->subject;

    expect($subject)
        ->toContain('Family Budget')
        ->toContain(Config::string('app.name'));
});

it('new user mailable has correct content', function () {
    $owner = User::factory()->create(['name' => 'Inviter']);
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'Family Budget']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'newuser@example.com',
        'token' => 'token-new-content',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $mailable = new TallyInvitationNewUser($invitation);
    $content = $mailable->content();

    expect($content->markdown)->toBe('mail.tally.invitation-new-user');
    expect($content->with)->toHaveKeys(['inviterName', 'tallyName', 'registerUrl']);
    expect($content->with['inviterName'])->toBe('Inviter');
    expect($content->with['tallyName'])->toBe('Family Budget');
    expect($content->with['registerUrl'])->toContain('token-new-content');
});

it('existing user rendered html contains tallypal brand color', function () {
    $owner = User::factory()->create(['name' => 'Inviter']);
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'Brand Test']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'brand@example.com',
        'token' => 'token-brand-existing',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $mailable = new TallyInvitationExistingUser($invitation);
    $html = $mailable->render();

    expect($html)
        ->toContain('#C2613B')
        ->toMatch('/viewbox="0 0 64 64"/i');
});

it('new user rendered html contains tallypal brand color', function () {
    $owner = User::factory()->create(['name' => 'Inviter']);
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'Brand Test']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'brand@example.com',
        'token' => 'token-brand-new',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $mailable = new TallyInvitationNewUser($invitation);
    $html = $mailable->render();

    expect($html)
        ->toContain('#C2613B')
        ->toMatch('/viewbox="0 0 64 64"/i');
});
