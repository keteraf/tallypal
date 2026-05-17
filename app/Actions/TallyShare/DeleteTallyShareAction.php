<?php

declare(strict_types=1);

namespace App\Actions\TallyShare;

use App\Models\Tally;
use App\Models\User;
use Throwable;

class DeleteTallyShareAction
{
    public function handle(Tally $tally, User $user): void
    {
        try {
            /** @var User|null $userToRevoke */
            $userToRevoke = $tally->users()->where('user_id', $user->getKey())->first();

            if ($userToRevoke === null || $userToRevoke->pivot === null) {
                return;
            }

            // @pest-mutate-ignore
            $revokedPercentage = (float) ($userToRevoke->pivot->default_share_percentage); // @pest-mutate-ignore

            $tally->users()->detach($user->getKey());

            $remainingUsers = $tally->users()->get();

            if ($remainingUsers->isEmpty() || $revokedPercentage === 0.0) {
                return;
            }

            /** @var float|int $sumResult */
            $sumResult = $remainingUsers->sum('pivot.default_share_percentage');
            $totalCurrentPercentage = (float) $sumResult; // @pest-mutate-ignore

            $syncData = $remainingUsers->mapWithKeys(function (User $remainingUser) use ($revokedPercentage, $totalCurrentPercentage, $remainingUsers): array {
                if ($remainingUser->pivot === null) {
                    return [];
                }

                // @pest-mutate-ignore
                $currentPercentage = (float) ($remainingUser->pivot->default_share_percentage);

                $newPercentage = $totalCurrentPercentage > 0
                    ? $currentPercentage + ($currentPercentage / $totalCurrentPercentage * $revokedPercentage)
                    : 100 / $remainingUsers->count();

                return [$remainingUser->id => ['default_share_percentage' => $newPercentage]];
            })->toArray();

            $tally->users()->sync($syncData);
        } catch (Throwable $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }
}
