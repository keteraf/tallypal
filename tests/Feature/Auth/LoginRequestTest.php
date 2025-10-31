<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

test('login request throttles after too many attempts', function () {
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
    RateLimiter::spy();

    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    RateLimiter::shouldHaveReceived('clear');
});
