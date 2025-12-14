<?php

declare(strict_types=1);

namespace App\Actions\TallyShare;

use App\Models\Tally;
use App\Models\User;

class CreateTallyShareAction
{
    public function handle(Tally $tally, string $email): void
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $tally->users()->syncWithoutDetaching([$user->getKey()]);
    }
}
