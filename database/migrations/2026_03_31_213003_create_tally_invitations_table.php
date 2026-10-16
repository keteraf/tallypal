<?php

declare(strict_types=1);

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
        Schema::create('tally_invitations', function (Blueprint $table): void {
            $table->id('tally_invitation_id');
            $table->uuid('uuid')->unique();
            $table->foreignId('tally_id')->constrained()->references('tally_id')->on('tallies')->cascadeOnDelete();
            $table->foreignId('invited_by_user_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('email');
            $table->string('token')->unique();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tally_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tally_invitations');
    }
};
