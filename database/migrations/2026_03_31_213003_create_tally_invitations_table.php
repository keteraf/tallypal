<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\TallyInvitation;
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
        Schema::create(TallyInvitation::TABLE_NAME, function (Blueprint $table): void {
            $table->id(TallyInvitation::PRIMARY_KEY);
            $table->uuid('uuid')->unique();
            $table->foreignId(Tally::PRIMARY_KEY)->constrained()->references(Tally::PRIMARY_KEY)->on(Tally::TABLE_NAME)->cascadeOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('email');
            $table->string('token')->unique();
            $table->enum('status', TallyInvitation::STATUSES)->default(TallyInvitation::STATUS_PENDING);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index([Tally::PRIMARY_KEY, 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(TallyInvitation::TABLE_NAME);
    }
};
