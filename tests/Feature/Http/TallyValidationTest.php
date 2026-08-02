<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('creates a tally with valid data and sets the owner', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = [
        'name' => 'My Tally',
        'description' => 'About this tally',
    ];

    $response = $this->postJson(route('api.tallies.store'), $payload);

    $response->assertSuccessful();

    $this->assertDatabaseHas('tallies', [
        'name' => 'My Tally',
        'description' => 'About this tally',
        'owner_id' => $user->id,
    ]);
});

it('allows null description on create', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = [
        'name' => 'Null Desc',
        'description' => null,
    ];

    $response = $this->postJson(route('api.tallies.store'), $payload);

    $response->assertSuccessful();

    $this->assertDatabaseHas('tallies', [
        'name' => 'Null Desc',
        'description' => null,
        'owner_id' => $user->id,
    ]);
});

it('validates create payload', function (array $payload, string $errorField): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson(route('api.tallies.store'), $payload);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors([$errorField]);
})->with([
    'missing name' => [
        ['description' => 'x'], 'name',
    ],
    'name not string' => [
        ['name' => 123, 'description' => 'x'], 'name',
    ],
    'name too long' => [
        ['name' => str_repeat('a', 51), 'description' => 'x'], 'name',
    ],
    'description not string' => [
        ['name' => 'Ok', 'description' => 123], 'description',
    ],
    'description too long' => [
        ['name' => 'Ok', 'description' => str_repeat('a', 1001)], 'description',
    ],
]);

it('accepts boundary lengths on create', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = [
        'name' => str_repeat('a', 50),
        'description' => str_repeat('b', 1000),
    ];

    $response = $this->postJson(route('api.tallies.store'), $payload);

    $response->assertSuccessful();
});

// Update
it('updates a tally when owner provides valid data', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Old Name',
        'description' => 'Old Desc',
    ]);

    Sanctum::actingAs($owner);

    $response = $this->patchJson(route('api.tallies.update', $tally), [
        'name' => 'New Name',
        'description' => 'New Desc',
    ]);

    $response->assertSuccessful();

    $tally->refresh();
    expect($tally->name)->toBe('New Name')
        ->and($tally->description)->toBe('New Desc');
});

it('forbids update when user is not the owner', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $tally = Tally::factory()->create([
        'owner_id' => $owner->id,
    ]);

    Sanctum::actingAs($other);

    $response = $this->patchJson(route('api.tallies.update', $tally), [
        'name' => 'Hack',
    ]);

    $response->assertForbidden();
});

// Delete
it('deletes a tally when requested by the owner', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create([
        'owner_id' => $owner->id,
    ]);

    Sanctum::actingAs($owner);

    $response = $this->deleteJson(route('api.tallies.destroy', $tally));

    $response->assertNoContent();
    $this->assertModelMissing($tally);
});

it('forbids delete when user is not the owner', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $tally = Tally::factory()->create([
        'owner_id' => $owner->id,
    ]);

    Sanctum::actingAs($other);

    $response = $this->deleteJson(route('api.tallies.destroy', $tally));

    $response->assertForbidden();
});

// ─── Color / glyph ────────────────────────────────────────────────────────────

it('creates a tally with color and glyph', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson(route('api.tallies.store'), [
        'name' => 'Coloured Tally',
        'color' => 'c3',
        'glyph' => '✈',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('tallies', [
        'name' => 'Coloured Tally',
        'color' => 'c3',
        'glyph' => '✈',
    ]);
});

it('updates a tally persisting color and glyph', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Plain']);

    $response = $this->patchJson(route('api.tallies.update', $tally), [
        'name' => 'Coloured',
        'color' => 'c5',
        'glyph' => '♪',
    ]);

    $response->assertSuccessful();

    $tally->refresh();
    expect($tally->color)->toBe('c5')
        ->and($tally->glyph)->toBe('♪');
});

it('rejects an out-of-range color value on create', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson(route('api.tallies.store'), [
        'name' => 'Bad Color',
        'color' => 'c9',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['color']);
});

it('rejects an out-of-range color value on update', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Test']);

    $response = $this->patchJson(route('api.tallies.update', $tally), [
        'name' => 'Test',
        'color' => 'c9',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['color']);
});

it('allows null color and glyph on create', function (): void {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson(route('api.tallies.store'), [
        'name' => 'Null Color',
        'color' => null,
        'glyph' => null,
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('tallies', [
        'name' => 'Null Color',
        'color' => null,
        'glyph' => null,
    ]);
});

it('accepts every glyph from the picker set on create', function (string $glyph): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson(route('api.tallies.store'), ['name' => 'Glyph', 'glyph' => $glyph])
        ->assertSuccessful();

    $this->assertDatabaseHas('tallies', ['name' => 'Glyph', 'glyph' => $glyph]);
})->with(Tally::GLYPHS);

it('rejects an emoji glyph on create', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson(route('api.tallies.store'), ['name' => 'Glyph', 'glyph' => '🏠'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('glyph');
});

it('rejects a glyph outside the set on update and keeps the stored one', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);
    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'glyph' => '⌂']);

    $this->patchJson(route('api.tallies.update', $tally), ['name' => 'Renamed', 'glyph' => '🚀'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('glyph');

    expect($tally->refresh()->glyph)->toBe('⌂');
});

it('accepts a picker glyph on update', function (): void {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $this->patchJson(route('api.tallies.update', $tally), ['name' => 'Renamed', 'glyph' => '☕'])
        ->assertSuccessful();

    expect($tally->refresh()->glyph)->toBe('☕');
});
