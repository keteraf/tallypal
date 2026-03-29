<?php

declare(strict_types=1);

use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Transaction::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after(Transaction::PRIMARY_KEY);
        });

        DB::table(Transaction::TABLE_NAME)->orderBy(Transaction::PRIMARY_KEY)->each(function (object $row): void {
            DB::table(Transaction::TABLE_NAME)->where(Transaction::PRIMARY_KEY, $row->{Transaction::PRIMARY_KEY})->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table(Transaction::TABLE_NAME, function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table(Transaction::TABLE_NAME, function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
