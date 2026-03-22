<?php

declare(strict_types=1);

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated Super Admin users can visit the dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
