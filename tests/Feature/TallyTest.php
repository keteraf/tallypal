<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\User;

test('normal users are redirected to home dashboard after login', function () {
    $user = User::factory()->create();
    // Assuming no role means "normal user"

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('home.dashboard', absolute: false));
});

test('users can access the tallies page', function () {
    $user = User::factory()->create();

    Tally::factory()->count(3)->create([
        'owner_id' => $user->id,
    ]);

    $response = $this->actingAs($user)->get('/tallies');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('tallies/Index')
        ->has('tallies', 3)
    );
});

test('unauthenticated users cannot access the tallies page', function () {
    $response = $this->get('/tallies');

    $response->assertRedirect('/login');
});

test('normal users accessing dashboard are redirected to home dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('home.dashboard', absolute: false));
});
