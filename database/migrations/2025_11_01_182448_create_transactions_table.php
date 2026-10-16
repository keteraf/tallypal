<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id('transaction_id');
            $table->foreignId('party_id')->constrained()->references('id')->on('users');
            $table->foreignId('tally_id')->constrained()->references('tally_id')->on('tallies');
            $table->foreignId('category_id')->constrained()->references('category_id')->on('categories');
            $table->foreignId('currency_id')->constrained()->references('currency_id')->on('currencies');
            $table->enum('type', ['income', 'expense'])->default('expense');
            $table->string('title', 25);
            $table->text('description')->nullable();
            $table->bigInteger('amount')->default(0);
            $table->date('transaction_date')->default(now());
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
