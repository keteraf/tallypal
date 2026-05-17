<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\TallyUser;
use App\Services\HomeService;

final readonly class TallyUserObserver
{
    public function __construct(private HomeService $homeService) {}

    public function created(TallyUser $tallyUser): void
    {
        $this->invalidate($tallyUser);
    }

    public function deleted(TallyUser $tallyUser): void
    {
        $this->invalidate($tallyUser);
    }

    private function invalidate(TallyUser $tallyUser): void
    {
        /** @var int|null $userId */
        $userId = $tallyUser->getAttribute('user_id');

        if ($userId !== null) {
            $this->homeService->forgetCacheForUser((int) $userId);
        }
    }
}
