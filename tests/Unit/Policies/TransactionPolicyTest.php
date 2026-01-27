<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\TransactionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(TransactionPolicy::class);

beforeEach(function () {
    $this->policy = new TransactionPolicy();
});

it('allows owner or party member to view any transaction in a tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $member = User::factory()->create();
    $tally->users()->attach($member->id);
    $stranger = User::factory()->create();

    expect($this->policy->viewAny($owner, $tally))->toBeTrue()
        ->and($this->policy->viewAny($member, $tally))->toBeTrue()
        ->and($this->policy->viewAny($stranger, $tally))->toBeFalse();
});

it('allows owner or party member to view a transaction', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);

    $member = User::factory()->create();
    $tally->users()->attach($member->id);
    $stranger = User::factory()->create();

    expect($this->policy->view($owner, $transaction))->toBeTrue()
        ->and($this->policy->view($member, $transaction))->toBeTrue()
        ->and($this->policy->view($stranger, $transaction))->toBeFalse();
});

it('allows owner or party member to create a transaction in a tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $member = User::factory()->create();
    $tally->users()->attach($member->id);
    $stranger = User::factory()->create();

    expect($this->policy->create($owner, $tally))->toBeTrue()
        ->and($this->policy->create($member, $tally))->toBeTrue()
        ->and($this->policy->create($stranger, $tally))->toBeFalse();
});

it('allows owner or party member to update a transaction', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);

    $member = User::factory()->create();
    $tally->users()->attach($member->id);
    $stranger = User::factory()->create();

    expect($this->policy->update($owner, $transaction))->toBeTrue()
        ->and($this->policy->update($member, $transaction))->toBeTrue()
        ->and($this->policy->update($stranger, $transaction))->toBeFalse();
});

it('allows owner or party member to delete a transaction', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);

    $member = User::factory()->create();
    $tally->users()->attach($member->id);
    $stranger = User::factory()->create();

    expect($this->policy->delete($owner, $transaction))->toBeTrue()
        ->and($this->policy->delete($member, $transaction))->toBeTrue()
        ->and($this->policy->delete($stranger, $transaction))->toBeFalse();
});

it('allows owner or party member to restore a transaction', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);

    $member = User::factory()->create();
    $tally->users()->attach($member->id);
    $stranger = User::factory()->create();

    expect($this->policy->restore($owner, $transaction))->toBeTrue()
        ->and($this->policy->restore($member, $transaction))->toBeTrue()
        ->and($this->policy->restore($stranger, $transaction))->toBeFalse();
});

it('allows owner or party member to force delete a transaction', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->create(['tally_id' => $tally->tally_id]);

    $member = User::factory()->create();
    $tally->users()->attach($member->id);
    $stranger = User::factory()->create();

    expect($this->policy->forceDelete($owner, $transaction))->toBeTrue()
        ->and($this->policy->forceDelete($member, $transaction))->toBeTrue()
        ->and($this->policy->forceDelete($stranger, $transaction))->toBeFalse();
});
