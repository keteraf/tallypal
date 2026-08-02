<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Transaction;
use App\Services\HomeService;

final readonly class TransactionObserver
{
    public function __construct(private HomeService $homeService) {}

    public function created(Transaction $transaction): void
    {
        $this->invalidate($transaction);
    }

    public function updated(Transaction $transaction): void
    {
        $this->invalidate($transaction);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->invalidate($transaction);
    }

    private function invalidate(Transaction $transaction): void
    {
        $this->homeService->forgetCacheForUser((int) $transaction->party_id);

        $tally = $transaction->tally;

        if ($tally !== null) {
            $this->homeService->forgetCacheForTallyMembers($tally);
        }
    }
}
