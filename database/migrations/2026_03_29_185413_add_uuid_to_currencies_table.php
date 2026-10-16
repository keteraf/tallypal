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
        Schema::table('currencies', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('currency_id');
        });

        DB::table('currencies')->orderBy('currency_id')->each(function (object $row): void {
            DB::table('currencies')->where('currency_id', $row->currency_id)->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('currencies', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
