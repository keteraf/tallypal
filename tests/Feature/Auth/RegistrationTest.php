<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

covers(RegisteredUserController::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    Event::fake();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    Event::assertDispatched(Registered::class);
});

test('registration fails when passwords do not match', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('registration fails when password is missing', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => '',
        'password_confirmation' => '',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('registration fails when password is too short', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'abc',
        'password_confirmation' => 'abc',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('pending invitations are auto-accepted on registration', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $tally = Tally::factory()->withOwner($owner)->create();

    $invitation = $tally->invitations()->create([
        'invited_by_user_id' => $owner->id,
        'email' => 'invited@example.com',
        'token' => 'reg-token',
        'status' => TallyInvitation::STATUS_PENDING,
        'expires_at' => now()->addDays(7),
    ]);

    $this->post('/register', [
        'name' => 'Invited User',
        'email' => 'invited@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $newUser = User::where('email', 'invited@example.com')->first();
    $invitation->refresh();

    expect($invitation->status)->toBe(TallyInvitation::STATUS_ACCEPTED);
    expect($tally->users()->where('user_id', $newUser->id)->exists())->toBeTrue();
});

test('registration stores the app default currency as the new user default', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'currency@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'currency@example.com')->firstOrFail();

    expect($user->defaultCurrency?->code)->toBe('EUR');
});

test('registration follows the configured app default currency', function () {
    config(['app.default_currency' => 'USD']);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'usd@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::query()->where('email', 'usd@example.com')->firstOrFail()->default_currency_id)
        ->toBe(Currency::query()->where('code', 'USD')->firstOrFail()->currency_id);
});

test('registration leaves the default currency empty when the configured code is unknown', function () {
    config(['app.default_currency' => 'ZZZ']);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'zzz@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::query()->where('email', 'zzz@example.com')->firstOrFail()->default_currency_id)->toBeNull();
});
