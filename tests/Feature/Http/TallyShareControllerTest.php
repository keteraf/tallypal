<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TallyShareController;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

covers(TallyShareController::class);

it('lists all users sharing a tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $tally->users()->attach([
        $user1->id => ['default_share_percentage' => 50],
        $user2->id => ['default_share_percentage' => 50],
    ]);

    Sanctum::actingAs($owner);

    $response = $this->getJson(route('api.tallies.shares.index', $tally));

    $response->assertSuccessful();
    $response->assertJsonCount(2, 'data');
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'name', 'email'],
        ],
    ]);
});

it('denies stranger from listing shares', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $stranger = User::factory()->create();

    Sanctum::actingAs($stranger);

    $response = $this->getJson(route('api.tallies.shares.index', $tally));

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

it('allows owner to share a tally with a user', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $user = User::factory()->create();

    Sanctum::actingAs($owner);

    $response = $this->postJson(route('api.tallies.shares.store', $tally), [
        'email' => $user->email,
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $response->assertJson(['message' => 'Tally shared successfully']);

    expect($tally->users()->where('user_id', $user->id)->exists())->toBeTrue();
    expect($tally->users()->find($user->id)->pivot->default_share_percentage)->toBe(0.0);
});

it('denies non-owner from sharing a tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $stranger = User::factory()->create();
    $user = User::factory()->create();

    Sanctum::actingAs($stranger);

    $response = $this->postJson(route('api.tallies.shares.store', $tally), [
        'email' => $user->email,
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

it('allows owner to remove a user from a tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $user = User::factory()->create();

    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    Sanctum::actingAs($owner);

    $response = $this->deleteJson(route('api.tallies.shares.destroy', [$tally, $user]));

    $response->assertStatus(Response::HTTP_NO_CONTENT);

    expect($tally->users()->where('user_id', $user->id)->exists())->toBeFalse();
});

it('denies non-owner from removing a user from a tally', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $user = User::factory()->create();
    $stranger = User::factory()->create();

    $tally->users()->attach($user->id, ['default_share_percentage' => 100]);

    Sanctum::actingAs($stranger);

    $response = $this->deleteJson(route('api.tallies.shares.destroy', [$tally, $user]));

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

it('allows owner to update share percentages', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $tally->users()->attach([
        $user1->id => ['default_share_percentage' => 50],
        $user2->id => ['default_share_percentage' => 50],
    ]);

    Sanctum::actingAs($owner);

    $response = $this->putJson(route('api.tallies.shares.update', $tally), [
        'shares' => [
            ['user_id' => $user1->id, 'percentage' => 30],
            ['user_id' => $user2->id, 'percentage' => 70],
        ],
    ]);

    $response->assertSuccessful();
    $response->assertJson(['message' => 'Shares updated successfully']);

    expect($tally->users()->find($user1->id)->pivot->default_share_percentage)->toEqual(30)
        ->and($tally->users()->find($user2->id)->pivot->default_share_percentage)->toEqual(70);
});

it('allows owner to update share percentages with floats totaling 100', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $tally->users()->attach([
        $user1->id => ['default_share_percentage' => 50],
        $user2->id => ['default_share_percentage' => 50],
    ]);

    Sanctum::actingAs($owner);

    $response = $this->putJson(route('api.tallies.shares.update', $tally), [
        'shares' => [
            ['user_id' => $user1->id, 'percentage' => 30.5],
            ['user_id' => $user2->id, 'percentage' => 69.5],
        ],
    ]);

    $response->assertSuccessful();
    $response->assertJson(['message' => 'Shares updated successfully']);

    expect((float) $tally->users()->find($user1->id)->pivot->default_share_percentage)->toEqual(30.5)
        ->and((float) $tally->users()->find($user2->id)->pivot->default_share_percentage)->toEqual(69.5);
});

it('denies updating shares if total percentage is not 100', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $user1 = User::factory()->create();

    $tally->users()->attach($user1->id, ['default_share_percentage' => 100]);

    Sanctum::actingAs($owner);

    $response = $this->putJson(route('api.tallies.shares.update', $tally), [
        'shares' => [
            ['user_id' => $user1->id, 'percentage' => 50],
        ],
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonValidationErrors(['shares']);
});

it('denies non-owner from updating share percentages', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $user1 = User::factory()->create();
    $stranger = User::factory()->create();

    $tally->users()->attach($user1->id, ['default_share_percentage' => 100]);

    Sanctum::actingAs($stranger);

    $response = $this->putJson(route('api.tallies.shares.update', $tally), [
        'shares' => [
            ['user_id' => $user1->id, 'percentage' => 100],
        ],
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});
