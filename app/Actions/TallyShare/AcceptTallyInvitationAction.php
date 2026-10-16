<?php

declare(strict_types=1);

namespace App\Actions\TallyShare;

use App\Models\TallyInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class AcceptTallyInvitationAction
{
    public function handle(TallyInvitation $invitation, User $user): void
    {
        try {
            $tally = $invitation->tally()->firstOrFail();

            /** @var int $userId */
            $userId = $user->getKey();

            $alreadyAttached = $tally->users()->where('user_id', $userId)->exists();

            if ($alreadyAttached) {
                $tally->users()->updateExistingPivot($userId, ['default_share_percentage' => 0]);
            } else {
                $tally->users()->syncWithoutDetaching([$userId => ['default_share_percentage' => 0]]); // @pest-mutate-ignore
            }

            $invitation->update(['status' => TallyInvitation::STATUS_ACCEPTED]);
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (Throwable $e) {
            report($e);
            abort(500);
        }
    }
}
