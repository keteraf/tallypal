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
            $table->string('color')->nullable()->after('description');
            $table->string('glyph', 8)->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('tallies', function (Blueprint $table): void {
            $table->dropColumn(['color', 'glyph']);
        });
    }
};
