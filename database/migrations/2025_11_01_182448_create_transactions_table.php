<?php

declare(strict_types=1);

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
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
        Schema::create(Transaction::TABLE_NAME, function (Blueprint $table): void {
            $table->id(Transaction::PRIMARY_KEY);
            $table->foreignId('party_id')->constrained()->references('id')->on(User::TABLE_NAME);
            $table->foreignId(Tally::PRIMARY_KEY)->constrained()->references(Tally::PRIMARY_KEY)->on(Tally::TABLE_NAME);
            $table->foreignId(Category::PRIMARY_KEY)->constrained()->references(Category::PRIMARY_KEY)->on(Category::TABLE_NAME);
            $table->foreignId(Currency::PRIMARY_KEY)->constrained()->references(Currency::PRIMARY_KEY)->on(Currency::TABLE_NAME);
            $table->enum('type', [...TransactionType::cases()])->default(TransactionType::EXPENSE);
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
        Schema::dropIfExists(Transaction::TABLE_NAME);
    }
};
