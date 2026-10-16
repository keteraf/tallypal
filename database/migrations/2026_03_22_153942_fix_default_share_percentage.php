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
        Schema::table('tally_user', function (Blueprint $table): void {
            $table->decimal('default_share_percentage', 5)->default(100.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tally_user', function (Blueprint $table): void {
            $table->decimal('default_share_percentage', 3)->default(0)->change();
        });
    }
};
