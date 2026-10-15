<?php

declare(strict_types=1);

use App\Http\Controllers\TransactionController;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

covers(TransactionController::class);

/**
 * A tally owned by `$owner` with `$member` attached, and one transaction in it.
 *
 * @return array{0: User, 1: User, 2: Tally, 3: Transaction}
 */
function sharedTallyWithTransaction(): array
{
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->syncWithoutDetaching([$owner->id, $member->id]);
    $currency = Currency::factory()->create(['decimals' => 2, 'symbol' => '€']);
    $transaction = Transaction::factory()->forTally($tally)->forParty($owner)->withCurrency($currency)->create();

    return [$owner, $member, $tally, $transaction];
}

it('lets the owner delete a transaction and redirects to the tally with a success flash', function (): void {
    [$owner, , $tally, $transaction] = sharedTallyWithTransaction();

    $this->actingAs($owner)
        ->delete(route('tallies.transactions.destroy', [$tally, $transaction]))
        ->assertRedirect(route('tallies.transactions.index', $tally))
        ->assertSessionHas('status', 'transaction-deleted');

    $this->assertModelMissing($transaction);
});

it('lets any member delete a transaction, not only its payer', function (): void {
    [, $member, $tally, $transaction] = sharedTallyWithTransaction();

    $this->actingAs($member)
        ->delete(route('tallies.transactions.destroy', [$tally, $transaction]))
        ->assertRedirect(route('tallies.transactions.index', $tally));

    $this->assertModelMissing($transaction);
});

it('forbids deleting a transaction of a tally the user does not belong to', function (): void {
    [, , $tally, $transaction] = sharedTallyWithTransaction();
    $stranger = User::factory()->create();

    $this->actingAs($stranger)
        ->delete(route('tallies.transactions.destroy', [$tally, $transaction]))
        ->assertForbidden();

    $this->assertModelExists($transaction);
});

it('redirects a guest to the login page instead of deleting', function (): void {
    [, , $tally, $transaction] = sharedTallyWithTransaction();

    $this->delete(route('tallies.transactions.destroy', [$tally, $transaction]))
        ->assertRedirect(route('login'));

    $this->assertModelExists($transaction);
});

it('does not delete a transaction through a tally it does not belong to', function (): void {
    [, $member, , $transaction] = sharedTallyWithTransaction();
    $otherTally = Tally::factory()->create(['owner_id' => $member->id]);

    $this->actingAs($member)
        ->delete(route('tallies.transactions.destroy', [$otherTally, $transaction]))
        ->assertNotFound();

    $this->assertModelExists($transaction);
});

it('tells the edit page whether the user may delete the transaction', function (): void {
    [, $member, $tally, $transaction] = sharedTallyWithTransaction();

    $this->actingAs($member)
        ->get(route('tallies.transactions.edit', [$tally, $transaction]))
        ->assertInertia(fn (Assert $page): Assert => $page->where('can_delete', true));
});

it('passes the delete flash to the tally detail page', function (): void {
    [$owner, , $tally] = sharedTallyWithTransaction();

    $this->actingAs($owner)
        ->withSession(['status' => 'transaction-deleted'])
        ->get(route('tallies.transactions.index', $tally))
        ->assertInertia(fn (Assert $page): Assert => $page->where('status', 'transaction-deleted'));
});
