<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\User;
use App\Policies\TallyPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(TallyPolicy::class);

beforeEach(function () {
    $this->policy = new TallyPolicy();
});

it('allows any user to view any tally', function () {
    $user = User::factory()->create();
    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows any user to create a tally', function () {
    $user = User::factory()->create();
    expect($this->policy->create($user))->toBeTrue();
});

it('allows owner to view their tally', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    expect($this->policy->view($user, $tally))->toBeTrue();
});

it('allows party member to view the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $member = User::factory()->create();
    $tally->users()->attach($member->id);

    expect($this->policy->view($member, $tally))->toBeTrue();
});

it('denies stranger from viewing the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $stranger = User::factory()->create();

    expect($this->policy->view($stranger, $tally))->toBeFalse();
});

it('allows owner to update their tally', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    expect($this->policy->update($user, $tally))->toBeTrue();
});

it('allows party member to update the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $member = User::factory()->create();
    $tally->users()->attach($member->id);

    expect($this->policy->update($member, $tally))->toBeTrue();
});

it('allows owner to delete their tally', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    expect($this->policy->delete($user, $tally))->toBeTrue();
});

it('denies non-owner from deleting the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $member = User::factory()->create();
    $tally->users()->attach($member->id);

    expect($this->policy->delete($member, $tally))->toBeFalse();
});

it('allows owner to restore their tally', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    expect($this->policy->restore($user, $tally))->toBeTrue();
});

it('denies non-owner from restoring the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $stranger = User::factory()->create();

    expect($this->policy->restore($stranger, $tally))->toBeFalse();
});

it('allows owner to force delete their tally', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    expect($this->policy->forceDelete($user, $tally))->toBeTrue();
});

it('denies non-owner from force deleting the tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $stranger = User::factory()->create();

    expect($this->policy->forceDelete($stranger, $tally))->toBeFalse();
});
