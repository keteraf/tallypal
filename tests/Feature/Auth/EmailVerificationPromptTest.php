<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('email verification prompt is displayed for unverified user', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertInertia(fn (AssertableInertia $page) => $page->component('auth/VerifyEmail')
    );
});

test('verified user is redirected from email verification prompt', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertRedirect(route('dashboard'));
});
