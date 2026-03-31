<?php

declare(strict_types=1);

namespace App\Actions\TallyShare;

use App\Mail\TallyInvitationExistingUser;
use App\Mail\TallyInvitationNewUser;
use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateTallyShareAction
{
    public function handle(Tally $tally, string $email): TallyInvitation
    {
        /** @var User $owner */
        $owner = $tally->owner;

        $invitation = $tally->invitations()->create([
            'invited_by_user_id' => $owner->getKey(),
            'email' => mb_strtolower($email),
            'token' => Str::random(64),
            'status' => TallyInvitation::STATUS_PENDING,
            'expires_at' => now()->addDays(7),
        ]);

        $invitation->load(['tally', 'invitedBy']);

        $existingUser = User::query()->where('email', mb_strtolower($email))->exists();

        if ($existingUser) {
            Mail::to($email)->send(new TallyInvitationExistingUser($invitation));
        } else {
            Mail::to($email)->send(new TallyInvitationNewUser($invitation));
        }

        return $invitation;
    }
}
