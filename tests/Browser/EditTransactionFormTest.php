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

require_once __DIR__ . '/Support.php';

/**
 * The category picker (E7) on the Edit transaction page (C-D8): prefill, type switch, server 422.
 */
beforeEach(function () {
    // Always the built assets, never a dev server that may be running beside the suite.
    Vite::useHotFile(storage_path('framework/vite.hot.disabled'));

    $this->seed(ReferenceDataSeeder::class);
    $this->user = User::factory()->create(['default_currency_id' => Currency::query()->where('code', 'EUR')->value('currency_id')]);
    $this->tally = Tally::factory()->create(['owner_id' => $this->user->id, 'name' => 'Household']);
    $this->tally->users()->syncWithoutDetaching([$this->user->id]);
    $this->actingAs($this->user);
});

dataset('viewports', [
    'desktop 1440x900' => [1440, 900],
    'phone 375x812' => [375, 812],
]);

dataset('themes', ['dark' => ['dark'], 'light' => ['light']]);

/** A saved transaction of the household tally, in its own category. */
function savedTransactionIn(Tally $tally, User $user, string $slug, TransactionType $type): Transaction
{
    return Transaction::factory()->forTally($tally)->forParty($user)
        ->withCurrency(Currency::query()->where('code', 'EUR')->firstOrFail())
        ->withCategory(Category::query()->where('slug', $slug)->firstOrFail())
        ->withType($type)
        ->create(['title' => 'Saved one', 'amount' => 80, 'transaction_date' => now()->toDateString()]);
}

/**
 * The transaction once the form's update has been handled. The app is served from this very process, so the
 * wait goes through the browser (which lets it answer) and never through a blocking sleep.
 *
 * @param  Closure(Transaction): bool  $isSaved
 */
function updatedTransaction(mixed $page, Transaction $transaction, Closure $isSaved): Transaction
{
    $deadline = microtime(true) + 5;

    while (microtime(true) < $deadline) {
        $saved = Transaction::query()->findOrFail($transaction->getKey());

        if ($isSaved($saved)) {
            return $saved;
        }

        $page->wait(0.1);
    }

    throw new RuntimeException("Transaction [{$transaction->uuid}] was not updated.");
}

function openEditTransaction(Transaction $transaction, string $theme, int $width, int $height, string $locale = 'en'): mixed
{
    $url = route('tallies.transactions.edit', [$transaction->tally, $transaction], false);
    $page = $theme === 'dark' ? visit($url)->inDarkMode() : visit($url)->inLightMode();
    $page->resize($width, $height);

    if ($locale !== 'en') {
        $page->script("localStorage.setItem('locale', '{$locale}')");
        $page->script('location.reload()');
    }

    // Labels are measured against the web font, not the fallback it replaces a moment later.
    waitUntil($page, "document.querySelector('" . PICKER_ROW . "') !== null && document.fonts.status === 'loaded'");

    return $page;
}

it('prefills the saved category, pinned first when it is not a frequent one', function (int $width, int $height, string $theme) {
    $transaction = savedTransactionIn($this->tally, $this->user, 'sold_items', TransactionType::INCOME);

    $page = openEditTransaction($transaction, $theme, $width, $height);

    $page->assertAttribute(PICKER_ROW . '[data-category-id="' . categoryId('sold_items') . '"]', 'aria-checked', 'true')
        ->assertAttributeContains('#amount', 'class', 'text-pos')
        ->assertNoJavaScriptErrors();

    // Pinned to the first slot of the row (E7), not left in the grid behind "All".
    expect($page->script("document.querySelector('" . PICKER_ROW . "')?.getAttribute('data-category-id')"))
        ->toBe((string) categoryId('sold_items'));
})->with('viewports')->with('themes');

it('gives the picker the full content width on a phone, so no label is clipped (CA-13 / CA-14)', function (int $width, string $theme, string $locale) {
    $transaction = savedTransactionIn($this->tally, $this->user, 'travel', TransactionType::EXPENSE);

    $page = openEditTransaction($transaction, $theme, $width, 800, $locale);
    $pickerIsAsWideAsForm = "(() => { const picker = document.querySelector('[data-slot=\"category-picker\"]'); return picker.getBoundingClientRect().width === picker.closest('form').getBoundingClientRect().width; })()";

    // No card around the form below sm: the picker gets the 20px gutters' worth of width the Add sheet has.
    expect($page->script($pickerIsAsWideAsForm))->toBeTrue()
        ->and($page->script("document.querySelector('[data-slot=\"category-picker\"]').getBoundingClientRect().width"))->toEqual($width - 40)
        ->and(truncatedLabels($page))->toBe([]);

    $page->click('[role="radiogroup"] button:has-text("' . ($locale === 'it' ? 'Entrata' : 'Income') . '")');
    waitUntil($page, "document.querySelector('[data-picker-all]').textContent.includes('" . ($locale === 'it' ? 'Tutte 11' : 'All 11') . "')");

    expect(truncatedLabels($page))->toBe([])
        ->and($page->script('document.documentElement.scrollWidth <= document.documentElement.clientWidth'))->toBeTrue();

    $page->assertNoJavaScriptErrors();
})->with([375, 360])->with('themes')->with(['en', 'it']);

it('saves a different category for the transaction', function (int $width, int $height) {
    $transaction = savedTransactionIn($this->tally, $this->user, 'groceries', TransactionType::EXPENSE);

    $page = openEditTransaction($transaction, 'dark', $width, $height);

    $page->click(PICKER_ROW . ':has-text("Bills")')
        ->click('form:has(#title) button[type="submit"]');

    expect(updatedTransaction($page, $transaction, fn (Transaction $saved): bool => $saved->category_id === categoryId('bills')))
        ->category_id->toBe(categoryId('bills'));
})->with('viewports');

it('clears the saved category on flipping the type, says so and still saves', function (int $width, int $height, string $theme) {
    $transaction = savedTransactionIn($this->tally, $this->user, 'groceries', TransactionType::EXPENSE);

    $page = openEditTransaction($transaction, $theme, $width, $height);

    $page->click('[role="radiogroup"] button:has-text("Income")')
        ->assertSee('is an expense category — cleared.')
        ->assertAttribute(PICKER_ROW . '[data-category-id="' . categoryId('salary') . '"]', 'aria-checked', 'false')
        ->assertNoJavaScriptErrors();

    $page->click('form:has(#title) button[type="submit"]');

    expect(updatedTransaction($page, $transaction, fn (Transaction $saved): bool => $saved->type === TransactionType::INCOME))
        ->category_id->toBeNull()
        ->type->toBe(TransactionType::INCOME);
})->with('viewports')->with('themes');

it('shows a server-side category mismatch under the picker in the field error state', function () {
    $transaction = savedTransactionIn($this->tally, $this->user, 'groceries', TransactionType::EXPENSE);

    $page = openEditTransaction($transaction, 'dark', 1440, 900);

    // The UI never lets a mismatch through, so swap the type under the form after the page has loaded.
    $page->script(<<<'JS'
        (() => {
            const send = XMLHttpRequest.prototype.send;
            XMLHttpRequest.prototype.send = function (body) {
                if (typeof body === 'string' && body.startsWith('{')) {
                    body = JSON.stringify({ ...JSON.parse(body), type: 'income' });
                }
                return send.call(this, body);
            };
        })()
    JS);

    $page->click('form:has(#title) button[type="submit"]')
        ->waitForText('The category must match the transaction type.')
        ->assertAttribute('[data-slot="category-picker"] [role="radiogroup"]', 'aria-invalid', 'true')
        ->assertAttributeContains('[data-slot="category-picker"] [role="radiogroup"]', 'aria-describedby', 'category_id-error');

    expect($transaction->refresh()->type)->toBe(TransactionType::EXPENSE);
});
