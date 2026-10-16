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

    /**
     * The owner is a member for cache purposes: forgetCacheForTallyMembers() covers them too.
     */
    private function invalidate(Tally $tally): void
    {
        $this->homeService->forgetCacheForTallyMembers($tally);
    }
}
