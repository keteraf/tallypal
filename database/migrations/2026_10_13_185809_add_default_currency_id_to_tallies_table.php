<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tallies', function (Blueprint $table): void {
            $table->foreignId('default_currency_id')
                ->nullable()
                ->after('glyph')
                ->constrained('currencies', 'currency_id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tallies', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('default_currency_id');
        });
    }
};
