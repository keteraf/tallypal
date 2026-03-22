<?php

declare(strict_types=1);

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
