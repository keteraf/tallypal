<?php

declare(strict_types=1);

use App\Actions\DeleteUserAccountAction;
use App\Http\Controllers\Settings\ProfileController;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;

covers(DeleteUserAccountAction::class, ProfileController::class);

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/settings/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/settings/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/settings/profile');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/settings/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/settings/profile')
        ->delete('/settings/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect('/settings/profile');

    expect($user->fresh())->not->toBeNull();
});

test('deleting account also deletes owned tallies and their transactions', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->withOwner($user)->create();
    $transaction = Transaction::factory()->forTally($tally)->forParty($user)->create();

    $this->actingAs($user)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(Tally::find($tally->getKey()))->toBeNull();
    expect(Transaction::find($transaction->getKey()))->toBeNull();
});

test('deleting account removes membership from tallies the user does not own', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $tally->users()->attach($member->id, ['default_share_percentage' => 0]);

    $this->actingAs($member)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect($member->fresh())->toBeNull();
    expect(Tally::find($tally->getKey()))->not->toBeNull();
    expect($tally->users()->where('user_id', $member->id)->exists())->toBeFalse();
});

test('deleting account removes transactions where user is party on tallies they do not own', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();
    $tally->users()->attach($member->id, ['default_share_percentage' => 0]);
    $transaction = Transaction::factory()->forTally($tally)->forParty($member)->create();

    $this->actingAs($member)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect($member->fresh())->toBeNull();
    expect(Transaction::find($transaction->getKey()))->toBeNull();
    expect(Tally::find($tally->getKey()))->not->toBeNull();
});

test('deleting member account redistributes their share percentage proportionally to remaining members', function () {
    $owner = User::factory()->create();
    $memberA = User::factory()->create();
    $memberB = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($memberA->id, ['default_share_percentage' => 30]);
    $tally->users()->attach($memberB->id, ['default_share_percentage' => 20]);

    $this->actingAs($memberA)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect($memberA->fresh())->toBeNull();

    $remainingUsers = $tally->users()->get()->keyBy('id');

    expect($remainingUsers)->toHaveCount(2);

    $ownerPercentage = (float) $remainingUsers[$owner->id]->pivot->default_share_percentage;
    $memberBPercentage = (float) $remainingUsers[$memberB->id]->pivot->default_share_percentage;

    expect($ownerPercentage + $memberBPercentage)->toEqualWithDelta(100.0, 0.001);
    expect($ownerPercentage)->toBeGreaterThan($memberBPercentage);
});

test('deleting member account with zero share only detaches without changing remaining members percentages', function () {
    $owner = User::factory()->create();
    $memberA = User::factory()->create();
    $memberB = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->users()->attach($owner->id, ['default_share_percentage' => 60]);
    $tally->users()->attach($memberA->id, ['default_share_percentage' => 0]);
    $tally->users()->attach($memberB->id, ['default_share_percentage' => 40]);

    $this->actingAs($memberA)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect($memberA->fresh())->toBeNull();

    $remainingUsers = $tally->users()->get()->keyBy('id');

    expect($remainingUsers)->toHaveCount(2);

    $ownerPercentage = (float) $remainingUsers[$owner->id]->pivot->default_share_percentage;
    $memberBPercentage = (float) $remainingUsers[$memberB->id]->pivot->default_share_percentage;

    expect($ownerPercentage)->toEqualWithDelta(60.0, 0.001);
    expect($memberBPercentage)->toEqualWithDelta(40.0, 0.001);
    expect($ownerPercentage + $memberBPercentage)->toEqualWithDelta(100.0, 0.001);
});

test('deleting member account when user is the only non-owner member gives owner 100 percent', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->users()->attach($owner->id, ['default_share_percentage' => 70]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 30]);

    $this->actingAs($member)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect($member->fresh())->toBeNull();

    $remainingUsers = $tally->users()->get()->keyBy('id');

    expect($remainingUsers)->toHaveCount(1);

    $ownerPercentage = (float) $remainingUsers[$owner->id]->pivot->default_share_percentage;

    expect($ownerPercentage)->toEqualWithDelta(100.0, 0.001);
});

test('deleting owner account cascade-deletes the tally without redistributing percentages', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $tally->users()->attach($owner->id, ['default_share_percentage' => 60]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 40]);

    $this->actingAs($owner)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    expect($owner->fresh())->toBeNull();
    expect(Tally::find($tally->getKey()))->toBeNull();
});
