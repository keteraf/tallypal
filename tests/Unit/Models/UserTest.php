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
        'email_verified_at',
        'default_currency_id',
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

it('derives up to two uppercase initials from the name', function (string $name, string $initials) {
    expect(User::factory()->make(['name' => $name])->initials())->toBe($initials);
})->with([
    'two words' => ['marco rossi', 'MR'],
    'three words' => ['Anna Maria Bianchi', 'AM'],
    'single word' => ['Luca', 'L'],
    'extra spaces' => ['  sara   neri ', 'SN'],
    'blank' => ['   ', '··'],
]);

it('maps a user to a stable avatar colour token', function () {
    $user = User::factory()->create();

    expect($user->avatarColor())->toBeIn(['c1', 'c2', 'c3', 'c4', 'c5', 'c6'])
        ->and($user->avatarColor())->toBe($user->avatarColor())
        ->and(User::factory()->make(['id' => 1])->avatarColor())->toBe('c2')
        ->and(User::factory()->make(['id' => 6])->avatarColor())->toBe('c1');
});
