<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Gives categories a type (expense | income), a stable `slug` and a colour token, and backfills the 16
 * expense rows the create migration inserted, so `ReferenceDataSeeder` (upsert by slug) matches them
 * instead of inserting duplicates. The map is a snapshot: later catalogue changes go in the seeder.
 */
return new class extends Migration
{
    /**
     * Slug and colour token of each row the create migration inserted, keyed by its name.
     *
     * @var array<string, array{slug: string, color: string}>
     */
    private const array EXISTING_ROWS = [
        'Bills' => ['slug' => 'bills', 'color' => 'c4'],
        'Clothing' => ['slug' => 'clothing', 'color' => 'c6'],
        'Dining Out' => ['slug' => 'dining_out', 'color' => 'c6'],
        'Education' => ['slug' => 'education', 'color' => 'c1'],
        'Entertainment' => ['slug' => 'entertainment', 'color' => 'c6'],
        'Gifts & Donations' => ['slug' => 'gifts_donations', 'color' => 'c5'],
        'Groceries' => ['slug' => 'groceries', 'color' => 'c4'],
        'Healthcare' => ['slug' => 'healthcare', 'color' => 'c3'],
        'Household Supplies' => ['slug' => 'household_supplies', 'color' => 'c4'],
        'Insurance' => ['slug' => 'insurance', 'color' => 'c1'],
        'Personal Care' => ['slug' => 'personal_care', 'color' => 'c6'],
        'Rent' => ['slug' => 'rent', 'color' => 'c1'],
        'Subscriptions' => ['slug' => 'subscriptions', 'color' => 'c2'],
        'Transportation' => ['slug' => 'transportation', 'color' => 'c2'],
        'Travel' => ['slug' => 'travel', 'color' => 'c2'],
        'Utilities' => ['slug' => 'utilities', 'color' => 'c5'],
    ];

    public function up(): void
    {
        Schema::table(Category::TABLE_NAME, function (Blueprint $table): void {
            $table->string('type', 16)->default('expense')->after('uuid');
            $table->string('slug')->nullable()->after('type');
            $table->string('color')->nullable()->after('icon');
        });

        DB::table(Category::TABLE_NAME)->orderBy(Category::PRIMARY_KEY)->each(function (object $row): void {
            /** @var object{category_id: int, name: string} $row */
            $known = self::EXISTING_ROWS[$row->name] ?? null;

            DB::table(Category::TABLE_NAME)->where(Category::PRIMARY_KEY, $row->category_id)->update([
                'type' => 'expense',
                'slug' => $known['slug'] ?? Str::slug($row->name . ' ' . $row->category_id, '_'),
                'color' => $known['color'] ?? null,
            ]);
        });

        Schema::table(Category::TABLE_NAME, function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table(Category::TABLE_NAME, function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropColumn(['type', 'slug', 'color']);
        });
    }
};
