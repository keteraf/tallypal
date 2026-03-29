<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Category::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after(Category::PRIMARY_KEY);
        });

        DB::table(Category::TABLE_NAME)->orderBy(Category::PRIMARY_KEY)->each(function (object $row): void {
            DB::table(Category::TABLE_NAME)->where(Category::PRIMARY_KEY, $row->{Category::PRIMARY_KEY})->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table(Category::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table(Category::TABLE_NAME, function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
