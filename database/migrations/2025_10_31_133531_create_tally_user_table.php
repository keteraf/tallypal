<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\TallyUser;
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
        Schema::create(TallyUser::TABLE_NAME, function (Blueprint $table): void {
            $table->id(TallyUser::PRIMARY_KEY);
            $table->foreignId(Tally::PRIMARY_KEY)->constrained()->references(Tally::PRIMARY_KEY)->on(Tally::TABLE_NAME);
            $table->foreignId('user_id')->constrained()->references('id')->on(User::TABLE_NAME);
            $table->decimal('default_share_percentage', 3)->default(0);
            $table->timestamps();

            $table->unique([Tally::PRIMARY_KEY, 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Tally::KEYS_PREFIX . '_' . User::KEYS_PREFIX);
    }
};
