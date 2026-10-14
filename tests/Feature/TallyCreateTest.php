<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\User;

test('users can access the create tally page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('tallies.create'));

    $response->assertStatus(200);
});

test('tally index page shows empty state with create button when no tallies exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('tallies.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('tallies/Index')
        ->has('tallies', 0)
    );
});

test('a tally name of 50 characters is stored on create', function () {
    $user = User::factory()->create();
    $name = str_repeat('a', 50);

    $this->actingAs($user)
        ->post(route('tallies.store'), ['name' => $name])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tallies', ['name' => $name, 'owner_id' => $user->id]);
});

test('a tally name of 51 characters is rejected on create', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tallies.store'), ['name' => str_repeat('a', 51)])
        ->assertSessionHasErrors('name');

    $this->assertDatabaseCount('tallies', 0);
});

test('a tally name of 50 characters is stored on update', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $name = str_repeat('b', 50);

    $this->actingAs($user)
        ->patch(route('tallies.update', $tally), ['name' => $name])
        ->assertSessionHasNoErrors();

    expect($tally->refresh()->name)->toBe($name);
});

test('a tally name of 51 characters is rejected on update', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'name' => 'Original']);

    $this->actingAs($user)
        ->patch(route('tallies.update', $tally), ['name' => str_repeat('b', 51)])
        ->assertSessionHasErrors('name');

    expect($tally->refresh()->name)->toBe('Original');
});
