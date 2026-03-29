<?php

declare(strict_types=1);

use App\Http\Controllers\TallyController;
use App\Models\Currency;
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

    $tally = App\Models\Tally::factory()->create([
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

    $owned = App\Models\Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Owned']);
    $shared = App\Models\Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Shared']);
    $shared->users()->attach($me->id, ['default_share_percentage' => 50]);
    App\Models\Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Unrelated']);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('tallies/Index')
            ->has('tallies', 2)
            ->has('tallies.0.owner')
            ->has('tallies.1.owner')
            ->where('tallies.0.name', fn (string $name): bool => in_array($name, ['Owned', 'Shared'], true))
            ->where('tallies.1.name', fn (string $name): bool => in_array($name, ['Owned', 'Shared'], true))
        );
});

it('converts income and expense totals from cents to decimals on index', function (): void {
    $me = User::factory()->create();
    $tally = App\Models\Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Totals']);
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
            ->where('tallies.0.income_total', 12.34)
            ->where('tallies.0.expense_total', 5.67)
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
    $tally = App\Models\Tally::factory()->create(['owner_id' => $user->id]);

    actingAs($user)
        ->get(route('tallies.edit', $tally))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('tallies/Edit')
            ->where('tally.tally_id', $tally->tally_id)
        );
});

it('forbids editing tally for non-owner', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $tally = App\Models\Tally::factory()->create(['owner_id' => $owner->id]);

    actingAs($stranger)
        ->get(route('tallies.edit', $tally))
        ->assertForbidden();
});

it('updates a tally and redirects to transactions index', function (): void {
    $user = User::factory()->create();
    $tally = App\Models\Tally::factory()->create(['owner_id' => $user->id]);

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

it('deletes a tally and redirects to index', function (): void {
    $user = User::factory()->create();
    $tally = App\Models\Tally::factory()->create(['owner_id' => $user->id]);

    actingAs($user)
        ->delete(route('tallies.destroy', $tally))
        ->assertRedirect(route('tallies.index'));

    $this->assertDatabaseMissing('tallies', [
        'tally_id' => $tally->tally_id,
    ]);
});
