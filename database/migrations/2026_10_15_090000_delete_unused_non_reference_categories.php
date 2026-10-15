<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Transaction;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Removes the hand-added categories (e.g. "Housing") that are not part of the reference set and that
 * no transaction uses. Reference categories and any category in use are never touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table(Category::TABLE_NAME)
            ->whereNotIn('slug', array_column(ReferenceDataSeeder::CATEGORIES, 'slug'))
            ->whereNotIn(Category::PRIMARY_KEY, DB::table(Transaction::TABLE_NAME)->whereNotNull(Category::PRIMARY_KEY)->select(Category::PRIMARY_KEY))
            ->delete();
    }

    /**
     * The deleted rows were unused and are not restorable.
     */
    public function down(): void {}
};
