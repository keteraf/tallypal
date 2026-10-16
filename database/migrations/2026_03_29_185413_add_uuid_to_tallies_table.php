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
        Schema::table('tallies', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('tally_id');
        });

        DB::table('tallies')->orderBy('tally_id')->each(function (object $row): void {
            DB::table('tallies')->where('tally_id', $row->tally_id)->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('tallies', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tallies', function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
