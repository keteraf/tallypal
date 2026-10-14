<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Tally;
use App\Models\TallyUser;
use App\Services\HomeService;

final readonly class TallyUserObserver
{
    public function __construct(private HomeService $homeService) {}

    public function created(TallyUser $tallyUser): void
    {
        $this->invalidate($tallyUser);
    }

    public function updated(TallyUser $tallyUser): void
    {
        $this->invalidate($tallyUser);
    }

    public function deleted(TallyUser $tallyUser): void
    {
        $this->invalidate($tallyUser);
    }

    /**
     * Forget the Home cache of the user joining or leaving, and of every member of the tally: a
     * member or a share change moves everyone's balances (B-04).
     */
    private function invalidate(TallyUser $tallyUser): void
    {
        /** @var int|null $userId */
        $userId = $tallyUser->getAttribute('user_id');

        if ($userId !== null) {
            $this->homeService->forgetCacheForUser((int) $userId);
        }

        $tally = Tally::query()->whereKey($tallyUser->getAttribute(Tally::PRIMARY_KEY))->first();

        if ($tally !== null) {
            $this->homeService->forgetCacheForTallyMembers($tally);
        }
    }
}
