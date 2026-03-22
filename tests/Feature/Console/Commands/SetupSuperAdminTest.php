<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

it('creates a super admin user and assigns the role', function () {
    // Role is already created by migration, but we verify it here
    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();

    $email = 'admin@example.com';
    $password = 'password123';

    $this->artisan('app:setup-super-admin', [
        'email' => $email,
        'password' => $password,
    ])
        ->expectsOutput("Super Admin created successfully for {$email}")
        ->assertExitCode(0);

    $user = User::where('email', $email)->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Super Admin')
        ->and(Hash::check($password, $user->password))->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull();

    expect($user->hasRole('Super Admin'))->toBeTrue();
});

it('creates the role if it does not exist', function () {
    // Delete the role created by the migration
    Role::where('name', 'Super Admin')->delete();
    expect(Role::where('name', 'Super Admin')->exists())->toBeFalse();

    $email = 'admin2@example.com';
    $password = 'password123';

    $this->artisan('app:setup-super-admin', [
        'email' => $email,
        'password' => $password,
    ])
        ->expectsOutput("Super Admin created successfully for {$email}")
        ->assertExitCode(0);

    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();
    $user = User::where('email', $email)->first();
    expect($user->hasRole('Super Admin'))->toBeTrue();
});
