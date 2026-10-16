<?php

declare(strict_types=1);

use App\Http\Controllers\TallyController;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

covers(TallyController::class);

it('renders the create page', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('tallies.create'))
        ->assertOk();
});

it('shows tallies where the user is a collaborator on the index page', function (): void {
    $owner = User::factory()->create();
    $collaborator = User::factory()->create();

    $tally = Tally::factory()->create([
        'owner_id' => $owner->id,
        'name' => 'Shared Tally',
    ]);

    // Attach collaborator
    $tally->users()->attach($collaborator->id, ['default_share_percentage' => 50]);

    actingAs($collaborator)
        ->get(route('tallies.index'))
        ->assertOk()
        ->assertSee('Shared Tally');
});

it('shows only owned and shared tallies on index', function (): void {
    $me = User::factory()->create();
    $owner = User::factory()->create();

    $owned = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Owned']);
    $shared = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Shared']);
    $shared->users()->attach($me->id, ['default_share_percentage' => 50]);
    Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Unrelated']);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('tallies/Index')
            ->has('tallies', 2)
            ->has('tallies.0.tally_id')
            ->has('tallies.0.uuid')
            ->has('tallies.0.owner_id')
            ->missing('tallies.0.owner')
            ->missing('tallies.0.created_at')
            ->where('tallies.0.name', fn (string $name): bool => in_array($name, ['Owned', 'Shared'], true))
            ->where('tallies.1.name', fn (string $name): bool => in_array($name, ['Owned', 'Shared'], true))
        );
});

it('converts income and expense totals from cents to decimals on index', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Totals']);
    $currency = Currency::factory()->create(['decimals' => 2]);

    Transaction::factory()->forTally($tally)->create(['currency_id' => $currency->currency_id, 'type' => 'income', 'amount' => 12.34]);
    Transaction::factory()->forTally($tally)->create(['currency_id' => $currency->currency_id, 'type' => 'expense', 'amount' => 5.67]);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('tallies/Index')
            ->has('tallies', 1)
            ->where('tallies.0.name', 'Totals')
            ->where('tallies.0.totals.0.income', 12.34)
            ->where('tallies.0.totals.0.expense', 5.67)
        );
});

it('stores a new tally and redirects', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('tallies.store'), [
            'name' => 'My New Tally',
            'description' => 'A description of my new tally',
        ])
        ->assertRedirect(route('tallies.index'));

    $this->assertDatabaseHas('tallies', [
        'name' => 'My New Tally',
        'description' => 'A description of my new tally',
        'owner_id' => $user->id,
    ]);
});

it('validates the tally name is required', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('tallies.store'), [
            'name' => '',
        ])
        ->assertSessionHasErrors('name');
});

it('renders the edit page', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    actingAs($user)
        ->get(route('tallies.edit', $tally))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('tallies/Edit')
            ->where('tally.tally_id', $tally->tally_id)
            ->where('other_members_count', 0)
        );
});

it('counts the other members of a shared tally on the edit page', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach(User::factory()->count(2)->create()->pluck('id')->all(), ['default_share_percentage' => 50]);

    actingAs($owner)
        ->get(route('tallies.edit', $tally))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('other_members_count', 2));
});

it('forbids editing tally for non-owner', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    actingAs($stranger)
        ->get(route('tallies.edit', $tally))
        ->assertForbidden();
});

it('updates a tally and redirects to transactions index', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    actingAs($user)
        ->put(route('tallies.update', $tally), [
            'name' => 'Updated Tally Name',
            'description' => 'Updated Tally Description',
        ])
        ->assertRedirect(route('tallies.transactions.index', $tally));

    $this->assertDatabaseHas('tallies', [
        'tally_id' => $tally->tally_id,
        'name' => 'Updated Tally Name',
        'description' => 'Updated Tally Description',
    ]);
});

it('accepts the unchanged legacy glyph when updating a tally', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'glyph' => '🏠']);

    actingAs($user)
        ->put(route('tallies.update', $tally), ['name' => 'Renamed', 'glyph' => '🏠'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('tallies.transactions.index', $tally));

    expect($tally->fresh()->glyph)->toBe('🏠');
});

it('rejects changing the glyph to one outside the picker set', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'glyph' => '🏠']);

    actingAs($user)
        ->put(route('tallies.update', $tally), ['name' => 'Renamed', 'glyph' => '🚀'])
        ->assertSessionHasErrors('glyph');

    expect($tally->fresh()->glyph)->toBe('🏠');
});

it('accepts changing the glyph to one in the picker set', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id, 'glyph' => '🏠']);

    actingAs($user)
        ->put(route('tallies.update', $tally), ['name' => 'Renamed', 'glyph' => '✈'])
        ->assertSessionHasNoErrors();

    expect($tally->fresh()->glyph)->toBe('✈');
});

it('deletes a tally and redirects to index', function (): void {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    actingAs($user)
        ->delete(route('tallies.destroy', $tally))
        ->assertRedirect(route('tallies.index'))
        ->assertSessionHas('status', 'tally-deleted');

    $this->assertDatabaseMissing('tallies', [
        'tally_id' => $tally->tally_id,
    ]);
});

it('forbids a member who is not the owner from deleting the tally', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->syncWithoutDetaching([$owner->id, $member->id]);

    actingAs($member)
        ->delete(route('tallies.destroy', $tally))
        ->assertForbidden();

    $this->assertModelExists($tally);
});

it('forbids a stranger from deleting the tally', function (): void {
    $tally = Tally::factory()->create();

    actingAs(User::factory()->create())
        ->delete(route('tallies.destroy', $tally))
        ->assertForbidden();

    $this->assertModelExists($tally);
});

it('passes the delete flash to the index page once', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->withSession(['status' => 'tally-deleted'])
        ->get(route('tallies.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('status', 'tally-deleted'));
});

it('hands the create form every currency with its identity columns and the user default', function () {
    $currency = Currency::factory()->create();
    $user = User::factory()->create(['default_currency_id' => $currency->getKey()]);

    $this->actingAs($user)->get(route('tallies.create'))
        ->assertInertia(fn ($page) => $page
            ->where('default_currency_uuid', $currency->uuid)
            ->where('currencies', fn ($rows): bool => collect($rows)->firstWhere('uuid', $currency->uuid) === [
                'currency_id' => $currency->getKey(),
                'uuid' => $currency->uuid,
                'name' => $currency->name,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
            ])
        );
});

it('hands the edit form the tally columns, its default currency, every currency and the other member count', function () {
    $currency = Currency::factory()->create();
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create([
        'owner_id' => $owner->id,
        'default_currency_id' => $currency->getKey(),
        'description' => 'About',
        'color' => 'c3',
        'glyph' => 'house',
    ]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 50]);

    $this->actingAs($owner)->get(route('tallies.edit', $tally))
        ->assertInertia(fn ($page) => $page
            ->where('tally', [
                'tally_id' => $tally->tally_id,
                'uuid' => $tally->uuid,
                'name' => $tally->name,
                'description' => 'About',
                'owner_id' => $owner->id,
                'color' => 'c3',
                'glyph' => 'house',
                'default_currency_uuid' => $currency->uuid,
            ])
            ->where('other_members_count', 1)
            ->where('currencies', fn ($rows): bool => collect($rows)->firstWhere('uuid', $currency->uuid) === [
                'currency_id' => $currency->getKey(),
                'uuid' => $currency->uuid,
                'name' => $currency->name,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
            ])
        );
});
