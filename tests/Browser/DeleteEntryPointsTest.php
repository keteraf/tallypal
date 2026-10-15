<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Support\Facades\Vite;

/**
 * The delete entry points: "Delete transaction" on Edit transaction and "Delete tally" on Edit tally,
 * each through the confirmation dialog.
 */
beforeEach(function () {
    // Always the built assets, never a dev server that may be running beside the suite.
    Vite::useHotFile(storage_path('framework/vite.hot.disabled'));

    $this->seed(ReferenceDataSeeder::class);
    $eur = Currency::query()->where('code', 'EUR')->firstOrFail();

    $this->owner = User::factory()->create(['default_currency_id' => $eur->currency_id]);
    $this->member = User::factory()->create();
    $this->tally = Tally::factory()->create(['owner_id' => $this->owner->id, 'name' => 'Household']);
    $this->tally->users()->syncWithoutDetaching([$this->owner->id, $this->member->id]);

    $category = Category::query()->where('slug', 'groceries')->firstOrFail();
    $this->kept = Transaction::factory()->forTally($this->tally)->forParty($this->owner)->withCurrency($eur)
        ->withCategory($category)->withType(TransactionType::EXPENSE)
        ->create(['title' => 'Kept lunch', 'amount' => 70, 'transaction_date' => now()->toDateString()]);
    $this->doomed = Transaction::factory()->forTally($this->tally)->forParty($this->owner)->withCurrency($eur)
        ->withCategory($category)->withType(TransactionType::EXPENSE)
        ->create(['title' => 'Doomed dinner', 'amount' => 30, 'transaction_date' => now()->toDateString()]);
});

dataset('viewports', [
    'desktop 1440x900' => [1440, 900],
    'phone 375x812' => [375, 812],
]);

dataset('themes', ['dark' => ['dark'], 'light' => ['light']]);

/** Opens a page as `$user` in the given theme and viewport, with the locale set before the first render. */
function openAs(User $user, string $url, string $theme, int $width, int $height, string $locale = 'en'): mixed
{
    test()->actingAs($user);

    $page = $theme === 'dark' ? visit($url)->inDarkMode() : visit($url)->inLightMode();
    $page->resize($width, $height);

    if ($locale !== 'en') {
        $page->script("localStorage.setItem('locale', '{$locale}')");
        $page->script('location.reload()');
    }

    return $page;
}

/** The trigger is the first match in the document, the dialog's confirm button the one inside the dialog. */
const DELETE_TRIGGER = 'button.bg-destructive >> nth=0';
const DELETE_CONFIRM = '[role="dialog"] button.bg-destructive';

it('deletes a transaction after confirming, then shows the message, the new balance and an updated Home', function (int $width, int $height, string $theme) {
    $page = openAs($this->owner, route('tallies.transactions.edit', [$this->tally, $this->doomed], false), $theme, $width, $height);

    $page->waitForText('Delete transaction')->click(DELETE_TRIGGER);
    $page->waitForText('Delete this transaction?')->assertSee('Doomed dinner')->click(DELETE_CONFIRM);

    $page->waitForText('Transaction deleted')
        ->assertPathIs(route('tallies.transactions.index', $this->tally, false))
        ->assertSee('Kept lunch')
        ->assertDontSee('Doomed dinner')
        ->assertSee('70.00')
        ->assertDontSee('100.00')
        ->assertNoJavaScriptErrors();

    $this->assertModelMissing($this->doomed);

    $home = visit(route('home.dashboard', [], false))->resize($width, $height);
    $home->waitForText('Kept lunch')->assertDontSee('Doomed dinner');
})->with('viewports')->with('themes');

it('keeps the transaction when the dialog is cancelled', function (int $width, int $height) {
    $page = openAs($this->owner, route('tallies.transactions.edit', [$this->tally, $this->doomed], false), 'dark', $width, $height);

    $page->waitForText('Delete transaction')->click(DELETE_TRIGGER);
    $page->waitForText('Delete this transaction?')->click('[role="dialog"] button:has-text("Cancel")');

    $page->assertDontSee('Delete this transaction?')
        ->assertPathIs(route('tallies.transactions.edit', [$this->tally, $this->doomed], false))
        ->assertDontSee('Transaction deleted');

    $this->assertModelExists($this->doomed);
})->with('viewports');

it('lets a member who is not the owner delete a transaction', function () {
    $page = openAs($this->member, route('tallies.transactions.edit', [$this->tally, $this->doomed], false), 'light', 375, 812);

    $page->waitForText('Delete transaction')->click(DELETE_TRIGGER);
    $page->waitForText('Delete this transaction?')->click(DELETE_CONFIRM);

    $page->waitForText('Transaction deleted')->assertDontSee('Doomed dinner');

    $this->assertModelMissing($this->doomed);
});

it('shows the success message in Italian', function () {
    $page = openAs($this->owner, route('tallies.transactions.edit', [$this->tally, $this->doomed], false), 'dark', 1440, 900, 'it');

    $page->waitForText('Elimina transazione')->click(DELETE_TRIGGER);
    $page->waitForText('Eliminare questa transazione?')->click(DELETE_CONFIRM);

    $page->waitForText('Transazione eliminata')->assertNoJavaScriptErrors();
});

it('deletes a tally after confirming, then shows the message and drops it from Home', function (int $width, int $height, string $theme) {
    $page = openAs($this->owner, route('tallies.edit', $this->tally, false), $theme, $width, $height);

    $page->waitForText('Delete tally')->click(DELETE_TRIGGER);
    $page->waitForText('Are you sure you want to delete this tally?')
        ->assertSee('shared with 1 other member')
        ->click(DELETE_CONFIRM);

    $page->waitForText('Tally deleted.')
        ->assertPathIs(route('tallies.index', [], false))
        ->assertNoJavaScriptErrors();

    $this->assertModelMissing($this->tally);

    $home = visit(route('home.dashboard', [], false))->resize($width, $height);
    $home->waitForText('Home')->assertDontSee('Household')->assertDontSee('Kept lunch');
})->with('viewports')->with('themes');

it('keeps the tally when the dialog is cancelled', function () {
    $page = openAs($this->owner, route('tallies.edit', $this->tally, false), 'light', 1440, 900);

    $page->waitForText('Delete tally')->click(DELETE_TRIGGER);
    $page->waitForText('Are you sure you want to delete this tally?')->click('[role="dialog"] button:has-text("Cancel")');

    $page->assertDontSee('Are you sure you want to delete this tally?')
        ->assertPathIs(route('tallies.edit', $this->tally, false))
        ->assertDontSee('Tally deleted.');

    $this->assertModelExists($this->tally);
});

it('does not offer Delete tally to a member who is not the owner', function () {
    $detail = openAs($this->member, route('tallies.transactions.index', $this->tally, false), 'dark', 1440, 900);
    $detail->waitForText('Kept lunch')
        ->assertMissing('a[href="' . route('tallies.edit', $this->tally, false) . '"]');

    $edit = openAs($this->member, route('tallies.edit', $this->tally, false), 'dark', 1440, 900);
    $edit->assertDontSee('Delete tally');

    $this->assertModelExists($this->tally);
});
