<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

// These tests exercise LoginRequest in isolation from route-level throttle middleware.
test('login request throttles after too many attempts', function () {
    $this->withoutMiddleware(ThrottleRequests::class);

    Event::fake();

    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        try {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        } catch (ValidationException $e) {

        }
    }

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('email');
    Event::assertDispatched(Lockout::class);
});

test('login request clears rate limiter on successful authentication', function () {
    $this->withoutMiddleware(ThrottleRequests::class);

    RateLimiter::spy();

    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    RateLimiter::shouldHaveReceived('clear');
});
