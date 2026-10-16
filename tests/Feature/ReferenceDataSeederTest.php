<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Currency;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

function categoriesMigration(): Migration
{
    /** @var Migration */
    return require database_path('migrations/2026_10_14_211827_add_type_slug_color_to_categories_table.php');
}

it('matches the 16 pre-existing categories and the 10 currencies instead of duplicating them', function (): void {
    $migration = categoriesMigration();
    $migration->down();

    expect(Schema::hasColumn(Category::TABLE_NAME, 'slug'))->toBeFalse();

    /** @var array<string, string> $uuidsBefore name => uuid */
    $uuidsBefore = DB::table(Category::TABLE_NAME)->pluck('uuid', 'name')->all();
    /** @var array<string, int> $idsBefore name => id */
    $idsBefore = DB::table(Category::TABLE_NAME)->pluck(Category::PRIMARY_KEY, 'name')->all();
    /** @var array<string, int> $currencyIdsBefore code => id */
    $currencyIdsBefore = DB::table(Currency::TABLE_NAME)->pluck(Currency::PRIMARY_KEY, 'code')->all();

    $migration->up();
    $this->seed(ReferenceDataSeeder::class);
    $this->seed(ReferenceDataSeeder::class);

    expect(Category::query()->count())->toBe(27)
        ->and(Currency::query()->count())->toBe(10)
        ->and(Category::query()->pluck(Category::PRIMARY_KEY, 'name')->only(array_keys($idsBefore))->all())->toBe($idsBefore)
        ->and(Category::query()->pluck('uuid', 'name')->only(array_keys($uuidsBefore))->all())->toBe($uuidsBefore)
        ->and(Currency::query()->pluck(Currency::PRIMARY_KEY, 'code')->all())->toBe($currencyIdsBefore);
});

it('backfills slug, type and colour onto the pre-existing categories', function (): void {
    $migration = categoriesMigration();
    $migration->down();
    $migration->up();

    expect(Category::query()->count())->toBe(16)
        ->and(Category::query()->where('type', '!=', CategoryType::EXPENSE->value)->count())->toBe(0)
        ->and(Category::query()->where('name', 'Dining Out')->first()?->only(['slug', 'color']))->toBe(['slug' => 'dining_out', 'color' => 'c6'])
        ->and(Category::query()->where('name', 'Gifts & Donations')->first()?->only(['slug', 'color']))->toBe(['slug' => 'gifts_donations', 'color' => 'c5']);
});

it('gives a category the backfill does not know a unique slug derived from its name and id', function (): void {
    $migration = categoriesMigration();
    $migration->down();
    $housingId = DB::table(Category::TABLE_NAME)->insertGetId(['uuid' => (string) Str::uuid(), 'name' => 'Housing', 'created_at' => now(), 'updated_at' => now()], Category::PRIMARY_KEY);

    $migration->up();

    expect(Category::query()->find($housingId)?->only(['slug', 'color']))->toBe(['slug' => 'housing_' . $housingId, 'color' => null]);
});

it('seeds the 16 expense and 11 income categories with their type and colour', function (): void {
    $this->seed(ReferenceDataSeeder::class);

    expect(Category::query()->ofType(CategoryType::EXPENSE)->count())->toBe(16)
        ->and(Category::query()->ofType(CategoryType::INCOME)->pluck('slug')->sort()->values()->all())->toBe([
            'bonus', 'gifts_received', 'investments', 'other_income', 'pension_benefits', 'refunds_returns',
            'rental_income', 'salary', 'side_work', 'sold_items', 'winnings',
        ])
        ->and(Category::query()->where('slug', 'refunds_returns')->first()?->only(['name', 'type', 'color']))
        ->toBe(['name' => 'Refunds & returns', 'type' => CategoryType::INCOME, 'color' => 'c2']);
});

it('restores a reference value that was changed by hand', function (): void {
    Currency::query()->where('code', 'JPY')->update(['decimals' => 2]);
    Category::query()->where('slug', 'travel')->update(['color' => 'c5']);

    $this->seed(ReferenceDataSeeder::class);

    expect(Currency::query()->where('code', 'JPY')->value('decimals'))->toBe(0)
        ->and(Category::query()->where('slug', 'travel')->value('color'))->toBe('c2');
});
