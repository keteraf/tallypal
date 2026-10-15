<?php

declare(strict_types=1);

use App\Models\Category;

/**
 * Helpers shared by the Pest browser tests (the files in this folder that end in `Test.php` are the tests).
 */
const PICKER_FILTER = 'input[aria-controls$="-grid"]';
const PICKER_ROW = '[data-slot="category-picker"] [data-picker-tile]';

function categoryId(string $slug): int
{
    return (int) Category::query()->where('slug', $slug)->value('category_id');
}

/** Waits in the browser for a JavaScript condition, so a test waits for the effect and never for a fixed time. */
function waitUntil(mixed $page, string $condition): void
{
    $page->page()->waitForFunction($condition);
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
