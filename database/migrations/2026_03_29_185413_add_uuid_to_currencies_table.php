<?php

declare(strict_types=1);

use App\Models\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Currency::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after(Currency::PRIMARY_KEY);
        });

        DB::table(Currency::TABLE_NAME)->orderBy(Currency::PRIMARY_KEY)->each(function (object $row): void {
            DB::table(Currency::TABLE_NAME)->where(Currency::PRIMARY_KEY, $row->{Currency::PRIMARY_KEY})->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table(Currency::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table(Currency::TABLE_NAME, function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
