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
        Schema::table('categories', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->unique()->after('category_id');
        });

        DB::table('categories')->orderBy('category_id')->each(function (object $row): void {
            DB::table('categories')->where('category_id', $row->category_id)->update(['uuid' => (string) Str::uuid()]);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('uuid');
        });
    }
};
