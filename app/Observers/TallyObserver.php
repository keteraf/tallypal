<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Tally;
use App\Services\HomeService;

final readonly class TallyObserver
{
    public function __construct(private HomeService $homeService) {}

    public function created(Tally $tally): void
    {
        $this->invalidate($tally);
    }

    public function updated(Tally $tally): void
    {
        $this->invalidate($tally);
    }

    public function deleted(Tally $tally): void
    {
        $this->invalidate($tally);
    }

    private function invalidate(Tally $tally): void
    {
        $this->homeService->forgetCacheForUser((int) $tally->owner_id);
        $this->homeService->forgetCacheForTallyMembers($tally);
    }
}
