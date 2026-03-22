<?php

declare(strict_types=1);

use App\Models\TallyUser;
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
        Schema::table(TallyUser::TABLE_NAME, function (Blueprint $table): void {
            $table->decimal('default_share_percentage', 5)->default(100.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(TallyUser::TABLE_NAME, function (Blueprint $table): void {
            $table->decimal('default_share_percentage', 3)->default(0)->change();
        });
    }
};
