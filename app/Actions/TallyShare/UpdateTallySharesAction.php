<?php

declare(strict_types=1);

namespace App\Actions\TallyShare;

use App\Models\Tally;
use Throwable;

class UpdateTallySharesAction
{
    /**
     * @param  array<int, array{user_id: int, percentage: float|int}>  $shares
     */
    public function handle(Tally $tally, array $shares): void
    {
        try {
            $syncData = collect($shares)->mapWithKeys(fn (array $share): array => [
                $share['user_id'] => ['default_share_percentage' => $share['percentage']],
            ])->toArray();

            $tally->users()->sync($syncData);
        } catch (Throwable $e) {
            report($e);
            abort(500);
        }
    }
}
