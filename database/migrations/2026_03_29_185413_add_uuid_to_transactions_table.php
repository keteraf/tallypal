<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('transaction_id');
        });

        DB::table('transactions')->orderBy('transaction_id')->each(function (object $row): void {
            DB::table('transactions')->where('transaction_id', $row->transaction_id)->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
