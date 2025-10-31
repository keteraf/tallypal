<?php

declare(strict_types=1);

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Validation\ValidationException;

function makeLoginRequest(array $data = [], string $ip = '127.0.0.1'): LoginRequest
{
    $request = LoginRequest::create('/', 'POST', $data, [], [], ['REMOTE_ADDR' => $ip]);
    $request->setLaravelSession(app('session')->driver());

    return $request;
}

beforeEach(function () {
    RateLimiter::clear('test@example.com|127.0.0.1');
});

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

it('THROWS ValidationException and fires Lockout event if rate-limited', function () {
    RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
    RateLimiter::shouldReceive('availableIn')->once()->andReturn(99);

    Event::fake();

    $request = makeLoginRequest(['email' => 'test@example.com']);

    try {
        $request->ensureIsNotRateLimited();
        // If we get here, the exception weren't thrown. That's bad.
        expect()->fail('ValidationException was not thrown, you muppet');
    } catch (ValidationException $e) {
        Event::assertDispatched(Lockout::class);
        $msg = $e->errors()['email'][0] ?? '';
        expect($msg)->toContain('seconds');
    }
});
