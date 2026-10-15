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
