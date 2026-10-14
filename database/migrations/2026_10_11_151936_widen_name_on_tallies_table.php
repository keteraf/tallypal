<?php

declare(strict_types=1);

use App\Models\Tally;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Tally::TABLE_NAME, function (Blueprint $table): void {
            $table->string('name', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table(Tally::TABLE_NAME, function (Blueprint $table): void {
            $table->string('name', 25)->change();
        });
    }
};
