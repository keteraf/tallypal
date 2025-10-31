<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

covers(Tally::class);

it('has correct table name', function () {
    $tally = new Tally();
    expect($tally->getTable())->toBe('tallies');
});

it('has correct primary key', function () {
    $tally = new Tally();
    expect($tally->getKeyName())->toBe('tally_id');
});

it('has fillable attributes', function () {
    $tally = new Tally();
    expect($tally->getFillable())->toBe([
        'owner_id',
        'name',
        'description',
    ]);
});

it('belongs to an owner', function () {
    $tally = Tally::factory()->create();
    expect($tally->owner())->toBeInstanceOf(BelongsTo::class)
        ->and($tally->owner)->toBeInstanceOf(User::class);
});

it('belongs to many users', function () {
    $tally = Tally::factory()->create();
    expect($tally->users())->toBeInstanceOf(BelongsToMany::class);
});

it('can attach users', function () {
    $tally = Tally::factory()->create();
    $user = User::factory()->create();

    $tally->users()->attach([$user->id]);

    expect($tally->fresh()->users)->toHaveCount(1)
        ->and($tally->users->first()->id)->toBe($user->id);
});

it('creates with factory', function () {
    $tally = Tally::factory()->create([
        'name' => 'Test Tally',
        'description' => 'Test Description',
    ]);

    expect($tally->name)->toBe('Test Tally')
        ->and($tally->description)->toBe('Test Description')
        ->and($tally->owner)->toBeInstanceOf(User::class);
});
