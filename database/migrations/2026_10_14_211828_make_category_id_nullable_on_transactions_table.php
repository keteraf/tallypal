<?php

declare(strict_types=1);

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
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable()->change();
            $table->foreign('category_id')->references('category_id')->on('categories')->nullOnDelete();
        });
    }

    /**
     * Not reversible once a transaction has no category: restoring NOT NULL would fail on those rows.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropForeign(['category_id']);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->foreignId('category_id')->nullable(false)->change();
            $table->foreign('category_id')->references('category_id')->on('categories');
        });
    }
};
