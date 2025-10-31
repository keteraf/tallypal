<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;

uses(RefreshDatabase::class);

covers(AuthController::class);

function authHeader(string $token): array
{
    return ['Authorization' => 'Bearer ' . $token];
}

it('registers a new user and returns a token', function (): void {
    $payload = [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response->assertCreated();

    $response->assertJsonStructure([
        'message',
        'user' => ['id', 'name', 'email'],
        'token',
        'token_type',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
        'name' => 'Jane Doe',
    ]);

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();
    expect(Hash::check('secret123', $user->password))->toBeTrue();

    expect($user->tokens()->count())->toBe(1);
});

it('fails registration with validation errors (duplicate email)', function (): void {
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/register', [
        'name' => 'John',
        'email' => 'john@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('fails registration when password confirmation mismatches', function (): void {
    $response = $this->postJson('/api/register', [
        'name' => 'Jake',
        'email' => 'jake@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret124',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['password']);
});

it('logs in and replaces previous tokens', function (): void {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'alice@example.com',
        'password' => Hash::make('correct-horse'),
    ]);

    $user->createToken('old-1');
    $user->createToken('old-2');
    expect($user->tokens()->count())->toBe(2);

    $response = $this->postJson('/api/login', [
        'email' => 'alice@example.com',
        'password' => 'correct-horse',
    ]);

    $response->assertSuccessful();

    $response->assertJsonStructure([
        'message',
        'user' => ['id', 'name', 'email'],
        'token',
        'token_type',
    ]);

    $user->refresh();
    expect($user->tokens()->count())->toBe(1);

    expect($response->json('token'))->toBeString()->not->toBe('');
    expect($response->json('token_type'))->toBe('Bearer');
});

it('rejects invalid login credentials', function (): void {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'bob@example.com',
        'password' => Hash::make('right-pass'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'bob@example.com',
        'password' => 'wrong-pass',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['email']);
});

it('returns the authenticated user', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var NewAccessToken $token */
    $token = $user->createToken('api');

    $response = $this->getJson('/api/user', authHeader($token->plainTextToken));

    $response->assertSuccessful();
    expect($response->json('user.id'))->toBe($user->id);
});

it('logs out by deleting only the current token', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var NewAccessToken $tokenA */
    $tokenA = $user->createToken('A');
    /** @var NewAccessToken $tokenB */
    $tokenB = $user->createToken('B');

    expect($user->tokens()->count())->toBe(2);

    $response = $this->postJson('/api/logout', [], authHeader($tokenA->plainTextToken));

    $response->assertSuccessful();

    $user->refresh();
    expect($user->tokens()->count())->toBe(1);
    expect($user->tokens()->firstWhere('id', $tokenB->accessToken->id))->not->toBeNull();
});

it('logs out from all devices by deleting all tokens', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    /** @var NewAccessToken $tokenA */
    $tokenA = $user->createToken('A');
    $user->createToken('B');

    expect($user->tokens()->count())->toBe(2);

    $response = $this->postJson('/api/logout-all', [], authHeader($tokenA->plainTextToken));

    $response->assertSuccessful();

    $user->refresh();
    expect($user->tokens()->count())->toBe(0);
});
