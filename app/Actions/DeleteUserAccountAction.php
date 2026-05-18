<?php

declare(strict_types=1);

namespace App\Actions;

use App\Actions\TallyShare\DeleteTallyShareAction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DeleteUserAccountAction
{
    public function __construct(private DeleteTallyShareAction $deleteTallyShareAction) {}

    /**
     * Delete a user account and all associated data.
     *
     * Cascade order:
     *  1. Delete all tallies owned by the user (transactions and invitations
     *     cascade via DB FK on the tally rows themselves).
     *  2. Delete any remaining transactions where the user is party_id
     *     (e.g. transactions on tallies they joined but did not own).
     *  3. For each tally where the user is a member but not the owner, call
     *     DeleteTallyShareAction to detach and redistribute percentages.
     *  4. Delete the user record.
     */
    /**
     * @throws Throwable
     */
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            // Step 1: delete owned tallies (their transactions cascade via DestroyTallyAction pattern).
            foreach ($user->ownedTallies as $tally) {
                $tally->transactions()->delete();
                $tally->users()->detach();
                // Invitations cascade automatically via FK cascadeOnDelete.
                $tally->delete();
            }

            // Step 2: delete transactions where this user is party_id on tallies they don't own.
            Transaction::where('party_id', $user->id)->delete();

            // Step 3: detach from non-owned tallies and redistribute share percentages to remaining members.
            $nonOwnedTallies = $user->tallies()->where('owner_id', '!=', $user->id)->get();

            foreach ($nonOwnedTallies as $tally) {
                $this->deleteTallyShareAction->handle($tally, $user);
            }

            // Step 4: delete the user record.
            $user->delete();
        });
    }
}
