<?php

declare(strict_types=1);

use App\Models\Tally;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Tally::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after(Tally::PRIMARY_KEY);
        });

        DB::table(Tally::TABLE_NAME)->orderBy(Tally::PRIMARY_KEY)->each(function (object $row): void {
            DB::table(Tally::TABLE_NAME)->where(Tally::PRIMARY_KEY, $row->{Tally::PRIMARY_KEY})->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table(Tally::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table(Tally::TABLE_NAME, function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
