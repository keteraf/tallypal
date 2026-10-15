<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A transaction's category is optional (owner decision C-D1): "◌ No category" is a valid state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Transaction::TABLE_NAME, function (Blueprint $table): void {
            $table->dropForeign([Category::PRIMARY_KEY]);
        });

        Schema::table(Transaction::TABLE_NAME, function (Blueprint $table): void {
            $table->foreignId(Category::PRIMARY_KEY)->nullable()->change();
            $table->foreign(Category::PRIMARY_KEY)->references(Category::PRIMARY_KEY)->on(Category::TABLE_NAME)->nullOnDelete();
        });
    }

    /**
     * Not reversible once a transaction has no category: restoring NOT NULL would fail on those rows.
     */
    public function down(): void
    {
        Schema::table(Transaction::TABLE_NAME, function (Blueprint $table): void {
            $table->dropForeign([Category::PRIMARY_KEY]);
        });

        Schema::table(Transaction::TABLE_NAME, function (Blueprint $table): void {
            $table->foreignId(Category::PRIMARY_KEY)->nullable(false)->change();
            $table->foreign(Category::PRIMARY_KEY)->references(Category::PRIMARY_KEY)->on(Category::TABLE_NAME);
        });
    }
};
