<?php

declare(strict_types=1);

use App\Actions\TallyShare\AcceptTallyInvitationAction;
use App\Http\Controllers\Api\TallyInvitationController as ApiTallyInvitationController;
use App\Http\Controllers\TallyInvitationController;
use App\Http\Resources\TallyInvitationResource;
use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

covers(TallyInvitationController::class, ApiTallyInvitationController::class, AcceptTallyInvitationAction::class, TallyInvitationResource::class);

// ─── Web: Accept ─────────────────────────────────────────────────────────────

it('allows an authenticated invited user to accept an invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'test-token-123',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->actingAs($invitee)
        ->get(route('invitations.accept', $invitation->uuid))
        ->assertRedirectToRoute('tallies.index');

    $invitation->refresh();
    expect($invitation->status)->toBe(TallyInvitation::STATUS_ACCEPTED);
    expect($tally->users()->where('user_id', $invitee->id)->exists())->toBeTrue();

    $pivot = $tally->users()->where('user_id', $invitee->id)->first()->pivot;
    expect($pivot->default_share_percentage)->toEqual(0);
});

it('stores intended url in session when unauthenticated user visits invitation link', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'session-store-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $acceptUrl = route('invitations.accept', $invitation->uuid);

    $this->get($acceptUrl)
        ->assertRedirectToRoute('login');

    expect(session()->get('url.intended'))->toBe($acceptUrl);
});

it('includes the tally name in the success message after accepting', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create(['name' => 'My Tally']);
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'success-msg-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->actingAs($invitee)
        ->get(route('invitations.accept', $invitation->uuid))
        ->assertRedirectToRoute('tallies.index')
        ->assertSessionHas('success', fn ($value) => str_contains($value, 'My Tally'));
});

it('redirects unauthenticated user to login when accepting', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'test-token-abc',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->get(route('invitations.accept', $invitation->uuid))
        ->assertRedirectToRoute('login');
});

it('rejects accept when authenticated user email does not match invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $otherUser = User::factory()->create(['email' => 'other@example.com']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'test-token-mismatch',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->actingAs($otherUser)
        ->get(route('invitations.accept', $invitation->uuid))
        ->assertRedirectToRoute('tallies.index');

    expect($tally->users()->where('user_id', $otherUser->id)->exists())->toBeFalse();
    $invitation->refresh();
    expect($invitation->status)->toBe(TallyInvitation::STATUS_PENDING);
});

it('rejects accepting an already accepted invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'test-token-done',
        'status' => TallyInvitation::STATUS_ACCEPTED,
        'expires_at' => now()->addDays(7),
    ]);

    $this->actingAs($invitee)
        ->get(route('invitations.accept', $invitation->uuid))
        ->assertRedirectToRoute('tallies.index');

    expect($tally->users()->where('user_id', $invitee->id)->exists())->toBeFalse();
});

it('rejects accepting an expired invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'test-token-expired',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->subDay(),
    ]);

    $this->actingAs($invitee)
        ->get(route('invitations.accept', $invitation->uuid))
        ->assertRedirectToRoute('tallies.index');

    expect($tally->users()->where('user_id', $invitee->id)->exists())->toBeFalse();
});

// ─── Web: Decline ────────────────────────────────────────────────────────────

it('allows anyone to decline an invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'test-token-decline',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->get(route('invitations.decline', $invitation->uuid))
        ->assertRedirectToRoute('home');

    $invitation->refresh();
    expect($invitation->status)->toBe(TallyInvitation::STATUS_DECLINED);
});

it('rejects declining an already-responded invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'test-token-already-declined',
        'status' => TallyInvitation::STATUS_DECLINED,
        'expires_at' => now()->addDays(7),
    ]);

    $this->get(route('invitations.decline', $invitation->uuid))
        ->assertRedirectToRoute('home')
        ->assertSessionHas('error')
        ->assertSessionMissing('success');

    $invitation->refresh();
    expect($invitation->status)->toBe(TallyInvitation::STATUS_DECLINED);
});

// ─── Registration: auto-accept ────────────────────────────────────────────────

it('auto-accepts pending invitations when a new user registers', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'newuser@example.com',
        'token' => 'registration-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirectToRoute('dashboard');

    $newUser = User::where('email', 'newuser@example.com')->first();
    expect($newUser)->not->toBeNull();

    $invitation->refresh();
    expect($invitation->status)->toBe(TallyInvitation::STATUS_ACCEPTED);
    expect($tally->users()->where('user_id', $newUser->id)->exists())->toBeTrue();
});

it('does not auto-accept expired invitations on registration', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'newuser@example.com',
        'token' => 'expired-registration-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->subDay(),
    ]);

    $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $invitation->refresh();
    expect($invitation->status)->toBe(TallyInvitation::STATUS_PENDING);
});

// ─── API: Invitations index ───────────────────────────────────────────────────

it('lists pending non-expired invitations for a tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'pending@example.com',
        'token' => 'api-list-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'accepted@example.com',
        'token' => 'api-list-token-2',
        'status' => TallyInvitation::STATUS_ACCEPTED,
        'expires_at' => now()->addDays(7),
    ]);

    Sanctum::actingAs($owner);

    $this->getJson(route('api.tallies.invitations.index', $tally))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.email', 'pending@example.com')
        ->assertJsonStructure(['data' => [['uuid', 'email', 'status', 'expires_at', 'invited_by']]]);
});

// ─── API: Cancel invitation ───────────────────────────────────────────────────

it('allows owner to cancel a pending invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'cancel@example.com',
        'token' => 'cancel-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson(route('api.tallies.invitations.destroy', [$tally, $invitation]))
        ->assertStatus(Response::HTTP_NO_CONTENT);

    $invitation->refresh();
    expect($invitation->status)->toBe(TallyInvitation::STATUS_DECLINED);
});

it('returns all invitation resource fields in index response', function () {
    $owner = User::factory()->create(['name' => 'Owner']);
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'fields@example.com',
        'token' => 'fields-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    Sanctum::actingAs($owner);

    $this->getJson(route('api.tallies.invitations.index', $tally))
        ->assertSuccessful()
        ->assertJsonPath('data.0.email', 'fields@example.com')
        ->assertJsonPath('data.0.status', TallyInvitation::STATUS_PENDING)
        ->assertJsonPath('data.0.invited_by.name', 'Owner')
        ->assertJsonPath('data.0.uuid', fn ($v) => is_string($v))
        ->assertJsonPath('data.0.expires_at', fn ($v) => $v !== null);
});

it('returns null for expires_at when invitation has no expiry', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'no-expiry@example.com',
        'token' => 'no-expiry-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => null,
    ]);

    Sanctum::actingAs($owner);

    $this->getJson(route('api.tallies.invitations.index', $tally))
        ->assertSuccessful()
        ->assertJsonPath('data.0.expires_at', null);
});

it('denies unauthenticated user from listing invitations', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $this->getJson(route('api.tallies.invitations.index', $tally))
        ->assertUnauthorized();
});

it('denies authenticated stranger from listing tally invitations', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $stranger = User::factory()->create();

    Sanctum::actingAs($stranger);

    $this->getJson(route('api.tallies.invitations.index', $tally))
        ->assertForbidden();
});

it('returns 404 when cancelling an invitation that belongs to a different tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $otherTally = Tally::factory()->withOwner($owner)->create();

    $invitation = $otherTally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'other@example.com',
        'token' => 'wrong-tally-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson(route('api.tallies.invitations.destroy', [$tally, $invitation]))
        ->assertNotFound();
});

it('returns 422 when cancelling a non-pending invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'accepted@example.com',
        'token' => 'accepted-token',
        'status' => TallyInvitation::STATUS_ACCEPTED,
        'expires_at' => now()->addDays(7),
    ]);

    Sanctum::actingAs($owner);

    $this->deleteJson(route('api.tallies.invitations.destroy', [$tally, $invitation]))
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

it('resets percentage to zero when invitee is already attached to the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    // Simulate invitee already attached with non-zero percentage (e.g. from a DB default or prior state)
    $tally->users()->attach($invitee->id, ['default_share_percentage' => 100]);

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invitee@example.com',
        'token' => 'already-attached-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->actingAs($invitee)
        ->get(route('invitations.accept', $invitation->uuid))
        ->assertRedirectToRoute('tallies.index');

    $invitation->refresh();
    expect($invitation->status)->toBe(TallyInvitation::STATUS_ACCEPTED);

    $pivot = $tally->users()->where('user_id', $invitee->id)->first()->pivot;
    expect($pivot->default_share_percentage)->toEqual(0);
});

it('denies non-owner from cancelling an invitation', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $stranger = User::factory()->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'cancel@example.com',
        'token' => 'cancel-token-stranger',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    Sanctum::actingAs($stranger);

    $this->deleteJson(route('api.tallies.invitations.destroy', [$tally, $invitation]))
        ->assertForbidden();
});
