<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\User;
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
        Schema::create(Tally::TABLE_NAME, function (Blueprint $table): void {
            $table->id(Tally::PRIMARY_KEY);
            $table->foreignIdFor(User::class, 'owner_id')->constrained();
            $table->string('name', 25);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Tally::TABLE_NAME);
    }
};
