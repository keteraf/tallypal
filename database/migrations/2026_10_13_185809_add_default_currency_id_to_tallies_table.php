<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Tally::TABLE_NAME, function (Blueprint $table): void {
            $table->foreignId('default_currency_id')
                ->nullable()
                ->after('glyph')
                ->constrained(Currency::TABLE_NAME, Currency::PRIMARY_KEY)
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(Tally::TABLE_NAME, function (Blueprint $table): void {
            $table->dropConstrainedForeignId('default_currency_id');
        });
    }
};
