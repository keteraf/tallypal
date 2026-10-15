<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Transaction;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

function deleteUnusedCategoriesMigration(): Migration
{
    /** @var Migration */
    return require database_path('migrations/2026_10_15_090000_delete_unused_non_reference_categories.php');
}

it('deletes an unused non-reference category and keeps an in-use one and every reference one', function (): void {
    test()->seed(ReferenceDataSeeder::class);
    $unused = Category::factory()->create(['slug' => 'housing_17', 'name' => 'Housing', 'type' => CategoryType::EXPENSE]);
    $inUse = Category::factory()->create(['slug' => 'custom_in_use', 'name' => 'Custom', 'type' => CategoryType::EXPENSE]);
    Transaction::factory()->create(['category_id' => $inUse->category_id]);

    deleteUnusedCategoriesMigration()->up();

    expect(Category::query()->whereKey($unused->category_id)->exists())->toBeFalse()
        ->and(Category::query()->whereKey($inUse->category_id)->exists())->toBeTrue()
        ->and(Category::query()->count())->toBe(28)
        ->and(Category::query()->ofType(CategoryType::EXPENSE)->whereIn('slug', array_column(ReferenceDataSeeder::CATEGORIES, 'slug'))->count())->toBe(16);
});

it('never deletes an unused reference category', function (): void {
    test()->seed(ReferenceDataSeeder::class);
    expect(DB::table(Category::TABLE_NAME)->count())->toBe(27);

    deleteUnusedCategoriesMigration()->up();

    expect(Category::query()->count())->toBe(27);
});

it('has nothing to undo', function (): void {
    expect(fn () => deleteUnusedCategoriesMigration()->down())->not->toThrow(Throwable::class);
});
