<?php

declare(strict_types=1);

namespace App\Actions\Tally;

use App\Models\Tally;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class DestroyTallyAction
{
    public function handle(Tally $tally): void
    {
        try {
            DB::transaction(function () use ($tally): void {
                $tally->transactions()->delete();
                $tally->users()->detach();
                $tally->delete();
            });
        } catch (Throwable $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }
}
