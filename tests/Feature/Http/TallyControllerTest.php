<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TallyController;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

covers(TallyController::class);

function getTallyIds(array $data): array
{
    return array_map(static fn (array $row): int => (int) $row['id'], $data);
}

it('lists only tallies owned by the authenticated user', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    // Owned by me
    $ownedA = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Mine A']);
    $ownedB = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Mine B']);

    // Owned by others and not shared with me
    $other = User::factory()->create();
    $otherA = Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Other A']);
    $otherB = Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Other B']);

    $response = $this->getJson(route('tallies.index'));

    $response->assertSuccessful();

    $data = $response->json('data');
    expect($data)->toBeArray();

    $ids = getTallyIds($data);
    expect($ids)->toContain($ownedA->getKey(), $ownedB->getKey())
        ->and($ids)->not->toContain($otherA->getKey(), $otherB->getKey())
        ->and(count($ids))->toBe(2);
});

it('lists tallies shared with the authenticated user via pivot', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $owner = User::factory()->create();

    // Shared with me via pivot
    $sharedA = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Shared A']);
    $sharedB = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Shared B']);
    $sharedA->users()->attach([$me->id]);
    $sharedB->users()->attach([$me->id]);

    // Not related
    $unrelated = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Not Mine']);

    $response = $this->getJson(route('tallies.index'));

    $response->assertSuccessful();

    $data = $response->json('data');
    $ids = getTallyIds($data);

    expect($ids)->toContain($sharedA->getKey(), $sharedB->getKey())
        ->and($ids)->not->toContain($unrelated->getKey());
});

it('lists both owned and shared tallies without including unrelated', function (): void {
    $me = User::factory()->create();
    Sanctum::actingAs($me);

    $other = User::factory()->create();

    $owned = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Mine']);

    $shared = Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Shared']);
    $shared->users()->attach([$me->id]);

    $unrelated = Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Nope']);

    $response = $this->getJson(route('tallies.index'));

    $response->assertSuccessful();

    $data = $response->json('data');
    $ids = getTallyIds($data);

    expect($ids)->toContain($owned->getKey(), $shared->getKey())
        ->and($ids)->not->toContain($unrelated->getKey());

    // Validate JSON structure includes owner and users from the resource
    $row = collect($data)->firstWhere('id', $owned->getKey());
    expect($row)->toHaveKeys(['id', 'name', 'description', 'owner', 'users', 'created_at', 'updated_at']);
});

it('shows a tally with owner and users relations loaded', function (): void {
    $viewer = User::factory()->create();
    Sanctum::actingAs($viewer);

    $tally = Tally::factory()->create([
        'owner_id' => $viewer->id,
        'name' => 'View Me',
        'description' => 'Details',
    ]);

    // Attach some collaborators
    $collabA = User::factory()->create();
    $collabB = User::factory()->create();
    $tally->users()->attach([$collabA->id, $collabB->id]);

    $response = $this->getJson(route('tallies.show', $tally));

    $response->assertSuccessful();

    $data = $response->json('data');
    expect($data['id'])->toBe($tally->getKey())
        ->and($data['name'])->toBe('View Me')
        ->and($data['owner'])->toBeArray()
        ->and($data['users'])->toBeArray()
        ->and(count($data['users']))->toBe(2);

    // Ensure users array includes two collaborators
});
