<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('prevents unauthorized users from viewing a tally', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $stranger = User::factory()->create();
    Sanctum::actingAs($stranger);

    $this->getJson(route('api.tallies.show', $tally))->assertForbidden();
});

it('prevents unauthorized users from updating a tally', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $stranger = User::factory()->create();
    Sanctum::actingAs($stranger);

    $this->patchJson(route('api.tallies.update', $tally), ['name' => 'New Name'])->assertForbidden();
});

it('prevents unauthorized users from deleting a tally', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $stranger = User::factory()->create();
    Sanctum::actingAs($stranger);

    $this->deleteJson(route('api.tallies.destroy', $tally))->assertForbidden();
});

it('allows parties to view a tally but not update or delete it', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $party = User::factory()->create();
    $tally->users()->attach($party->id);

    Sanctum::actingAs($party);

    $this->getJson(route('api.tallies.show', $tally))->assertSuccessful();
    $this->patchJson(route('api.tallies.update', $tally), ['name' => 'New Name'])->assertForbidden();
    $this->deleteJson(route('api.tallies.destroy', $tally))->assertForbidden();
});

it('prevents unauthorized users from accessing transactions', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $stranger = User::factory()->create();
    Sanctum::actingAs($stranger);

    $this->getJson(route('api.tallies.transactions.index', $tally))->assertForbidden();
    $this->postJson(route('api.tallies.transactions.store', $tally), [])->assertForbidden();
    $this->getJson(route('api.tallies.transactions.show', [$tally, $transaction]))->assertForbidden();
    $this->patchJson(route('api.tallies.transactions.update', [$tally, $transaction]), [])->assertForbidden();
    $this->deleteJson(route('api.tallies.transactions.destroy', [$tally, $transaction]))->assertForbidden();
});

it('allows parties to access transactions', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $transaction = Transaction::factory()->forTally($tally)->create();

    $party = User::factory()->create();
    $tally->users()->attach($party->id);

    Sanctum::actingAs($party);

    $this->getJson(route('api.tallies.transactions.index', $tally))->assertSuccessful();
    // store/update/delete would need valid payload to pass validation, but we can at least check if it's NOT 403
    // but better to check if it reaches validation
});
