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
 * The category picker (E7) inside Add transaction: the desktop dialog and the phone sheet.
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
    'phone 360x800' => [360, 800],
]);

dataset('layouts', [
    'desktop dialog' => [1440, 900],
    'phone sheet' => [375, 812],
]);

dataset('themes', ['dark' => ['dark'], 'light' => ['light']]);

/**
 * Opens Home and the Add transaction overlay: a dialog ≥ md, a sheet from the tab bar below.
 */
function openAddTransaction(string $theme, int $width, int $height, string $locale = 'en'): mixed
{
    $page = $theme === 'dark' ? visit('/home')->inDarkMode() : visit('/home')->inLightMode();
    $page->resize($width, $height);

    if ($locale !== 'en') {
        $page->script("localStorage.setItem('locale', '{$locale}')");
        $page->script('location.reload()');
        $page->waitForEvent('networkidle');
    }

    $label = $locale === 'it' ? 'Aggiungi transazione' : 'Add transaction';

    // The tab bar button below md; the `n` shortcut above it (Home's header button links to the full-page form instead).
    if ($width >= 768) {
        $page->keys('[data-sidebar="menu-button"][href="/home"]', 'n');
    } else {
        $page->click('button[aria-label="' . $label . '"]');
    }

    return $page->waitForText($locale === 'it' ? 'Nessuna categoria' : 'No category');
}

/** What holds the keyboard focus: the tile's category id, "all", or the element's id / aria-label. */
function focusedElement(mixed $page): string
{
    return (string) $page->script(<<<'JS'
        (() => {
            const el = document.activeElement;
            if (el?.hasAttribute('data-picker-all')) return 'all';
            if (el?.hasAttribute('data-category-id')) return 'tile:' + el.getAttribute('data-category-id');
            return el?.id || el?.getAttribute('aria-label') || el?.tagName.toLowerCase() || '';
        })()
    JS);
}

/** The form lives in the overlay (dialog or sheet), so it is on the page exactly while the overlay is open. */
function dialogIsOpen(mixed $page): bool
{
    return (bool) $page->script("document.querySelector('#title') !== null");
}

/**
 * The transaction the form saved, once the request has been handled. The app is served from this very process,
 * so the wait goes through the browser (which lets it answer) and never through a blocking sleep.
 */
function savedTransaction(mixed $page, string $title): Transaction
{
    $deadline = microtime(true) + 5;

    while (microtime(true) < $deadline) {
        $transaction = Transaction::query()->where('title', $title)->first();

        if ($transaction !== null) {
            return $transaction;
        }

        $page->wait(0.1);
    }

    throw new RuntimeException("No transaction titled [{$title}] was saved.");
}

function titleValue(mixed $page): string
{
    return (string) $page->script("document.querySelector('#title')?.value ?? ''");
}

/** True when a label wraps past its two lines or is cut off by the tile. */
function truncatedLabels(mixed $page): array
{
    return (array) $page->script(<<<'JS'
        [...document.querySelectorAll('[data-picker-tile] span')]
            .filter((label) => label.scrollHeight > label.clientHeight + 1 || label.scrollWidth > label.clientWidth + 1)
            .map((label) => label.textContent)
    JS);
}

it('adds an expense with a frequent tile', function (int $width, int $height, string $theme) {
    $page = openAddTransaction($theme, $width, $height);

    $page->type('#amount', '12.50')
        ->type('#title', 'Weekly shop')
        ->click(PICKER_ROW . ':has-text("Groceries")')
        ->assertAttribute(PICKER_ROW . ':has-text("Groceries")', 'aria-checked', 'true');

    expect(truncatedLabels($page))->toBe([]);

    $page->click('form:has(#title) button[type="submit"]');

    $transaction = savedTransaction($page, 'Weekly shop');

    $page->assertNoJavaScriptErrors();

    expect($transaction->category_id)->toBe(categoryId('groceries'))
        ->and($transaction->type)->toBe(TransactionType::EXPENSE);
})->with('viewports')->with('themes');

it('adds an income found by searching "stipendio" in Italian', function (int $width, int $height, string $theme) {
    $page = openAddTransaction($theme, $width, $height, 'it');

    $page->type('#amount', '1800')
        ->type('#title', 'Busta paga')
        ->click('[role="radiogroup"] button:has-text("Entrata")')
        ->assertAttributeContains('#amount', 'class', 'text-pos')
        ->click('[data-picker-all]')
        ->type(PICKER_FILTER, 'stipendio');

    expect(truncatedLabels($page))->toBe([]);

    $page->keys(PICKER_FILTER, 'Enter')
        ->assertAttribute(PICKER_ROW . '[data-category-id="' . categoryId('salary') . '"]', 'aria-checked', 'true');

    $page->click('form:has(#title) button[type="submit"]');

    $transaction = savedTransaction($page, 'Busta paga');

    $page->assertNoJavaScriptErrors();

    expect($transaction->category_id)->toBe(categoryId('salary'))
        ->and($transaction->type)->toBe(TransactionType::INCOME);
})->with('viewports')->with('themes');

it('clears an expense category on switching to Income and says so', function (int $width, int $height, string $theme) {
    $page = openAddTransaction($theme, $width, $height);

    $page->click(PICKER_ROW . ':has-text("Groceries")')
        ->click('[role="radiogroup"] button:has-text("Income")')
        ->assertSee('Groceries')
        ->assertSee('is an expense category — cleared.')
        ->assertAttribute('[data-slot="category-picker"] [role="radiogroup"] [data-category-id="' . categoryId('salary') . '"]', 'aria-checked', 'false')
        ->assertNoJavaScriptErrors();
})->with('viewports')->with('themes');

it('shows a server-side category mismatch under the picker in the field error state', function () {
    $page = openAddTransaction('dark', 1440, 900);

    $page->type('#amount', '9')
        ->type('#title', 'Mismatch')
        ->click(PICKER_ROW . ':has-text("Groceries")');

    // The UI never lets a mismatch through, so swap the type under the form after the pick.
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

    expect(Transaction::query()->count())->toBe(0);
});

describe('keyboard containment', function () {
    it('selects with Enter in the filter and does not submit the form', function (int $width, int $height) {
        $page = openAddTransaction('light', $width, $height);

        $page->type('#amount', '5')
            ->type('#title', 'Keyboard')
            ->click('[data-picker-all]')
            ->type(PICKER_FILTER, 'viaggi')
            ->keys(PICKER_FILTER, 'Enter')
            ->assertAttribute(PICKER_ROW . '[data-category-id="' . categoryId('travel') . '"]', 'aria-checked', 'true');

        $page->waitForEvent('networkidle');

        expect(Transaction::query()->count())->toBe(0)
            ->and(dialogIsOpen($page))->toBeTrue()
            ->and(titleValue($page))->toBe('Keyboard');
    })->with('layouts');

    it('selects a tile with Enter and does not submit the form', function (int $width, int $height) {
        $page = openAddTransaction('light', $width, $height);

        $page->type('#amount', '5')
            ->type('#title', 'Keyboard')
            ->keys(PICKER_ROW . ':has-text("Bills")', 'Enter')
            ->assertAttribute(PICKER_ROW . ':has-text("Bills")', 'aria-checked', 'true');

        $page->waitForEvent('networkidle');

        expect(Transaction::query()->count())->toBe(0)
            ->and(dialogIsOpen($page))->toBeTrue();
    })->with('layouts');

    it('closes only the picker panel on the first Esc and the dialog on the second', function (int $width, int $height) {
        $page = openAddTransaction('light', $width, $height);

        $page->type('#title', 'Keep me')
            ->click('[data-picker-all]')
            ->keys(PICKER_FILTER, 'Escape');

        waitUntil($page, "document.querySelector('" . PICKER_FILTER . "') === null");
        waitUntil($page, "document.activeElement?.hasAttribute('data-picker-all')");

        expect(dialogIsOpen($page))->toBeTrue()
            ->and(titleValue($page))->toBe('Keep me');

        $page->keys('[data-picker-all]', 'Escape');

        waitUntil($page, "document.querySelector('#title') === null");
    })->with('layouts');

    it('closes only the cleared notice on the first Esc and the dialog on the second', function (int $width, int $height) {
        $page = openAddTransaction('light', $width, $height);

        $page->type('#title', 'Keep me')
            ->click(PICKER_ROW . ':has-text("Groceries")')
            ->click('[role="radiogroup"] button:has-text("Income")')
            ->assertSee('is an expense category — cleared.')
            ->keys('#title', 'Escape');

        waitUntil($page, "!document.body.innerText.includes('is an expense category — cleared.')");

        expect(dialogIsOpen($page))->toBeTrue()
            ->and(titleValue($page))->toBe('Keep me');

        $page->keys('#title', 'Escape');

        waitUntil($page, "document.querySelector('#title') === null");
    })->with('layouts');

    it('keeps the picker in the natural tab order and returns focus to the row after a pick', function (int $width, int $height) {
        $page = openAddTransaction('light', $width, $height);

        $page->click('#title');

        expect(focusedElement($page))->toBe('title');

        // The row is one tab stop (the selected tile, else "All"), straight after the Title field.
        $page->keys('#title', 'Tab');

        expect(focusedElement($page))->toBe('all');

        $page->keys('[data-picker-all]', 'Tab');

        expect(focusedElement($page))->not->toStartWith('tile:')->not->toBe('all');

        $page->keys('[data-picker-all]', 'Enter')
            ->keys(PICKER_FILTER, 'Enter');

        waitUntil($page, "document.activeElement?.getAttribute('data-category-id') === '" . categoryId('bills') . "'");
    })->with('layouts');
});
