<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has correct table name', function () {
    $user = new User();
    expect($user->getTable())->toBe('users');
});

it('has correct primary key', function () {
    $user = new User();
    expect($user->getKeyName())->toBe('id');
});

it('has fillable attributes', function () {
    $user = new User();
    expect($user->getFillable())->toBe([
        'name',
        'email',
        'password',
    ]);
});

it('has hidden attributes', function () {
    $user = new User();
    expect($user->getHidden())->toBe([
        'password',
        'remember_token',
    ]);
});

it('has many owned tallies', function () {
    $user = User::factory()->create();
    expect($user->ownedTallies())->toBeInstanceOf(HasMany::class);
});

it('belongs to many tallies', function () {
    $user = User::factory()->create();
    expect($user->tallies())->toBeInstanceOf(BelongsToMany::class);
});

it('can own tallies', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    expect($user->ownedTallies)->toHaveCount(1)
        ->and($user->ownedTallies->first()->tally_id)->toBe($tally->tally_id);
});

it('can be attached to tallies', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create();

    $user->tallies()->attach($tally->tally_id);

    expect($user->fresh()->tallies)->toHaveCount(1)
        ->and($user->tallies->first()->tally_id)->toBe($tally->tally_id);
});

it('creates with factory', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->password)->not->toBeNull();
});

it('has correct casts', function () {
    $user = new User();
    expect($user->getCasts())->toMatchArray([
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ]);
});
