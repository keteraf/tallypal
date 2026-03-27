<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tally;
use App\Models\User;

class TallyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Tally $tally): bool
    {
        return $this->isOwnerOrParty($user, $tally);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Tally $tally): bool
    {
        return $this->isOwnerOrParty($user, $tally);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Tally $tally): bool
    {
        return (int) $tally->owner_id === (int) $user->id; // @pest-mutate-ignore
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tally $tally): bool
    {
        return $tally->owner_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tally $tally): bool
    {
        return $tally->owner_id === $user->id;
    }

    private function isOwnerOrParty(User $user, Tally $tally): bool
    {
        return $tally->owner_id === $user->id ||
            $tally->users()->where('user_id', $user->id)->exists();
    }
}
