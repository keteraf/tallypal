<?php

declare(strict_types=1);

use App\Models\User;
use Inertia\Testing\AssertableInertia;

test('ziggy prop is not shared on public routes', function () {
    $this->get('/')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('ziggy')
        );
});

test('ziggy prop is not shared on authenticated routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('ziggy')
        );
});
