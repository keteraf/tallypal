<?php

declare(strict_types=1);

namespace App\Actions\Tally;

use App\Models\Tally;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DestroyTallyAction
{
    /**
     * @throws Throwable
     */
    public function handle(Tally $tally): void
    {
        DB::transaction(function () use ($tally): void {
            $tally->transactions()->delete();
            $tally->users()->detach();
            $tally->delete();
        });
    }
}
