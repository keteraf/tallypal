<?php

declare(strict_types=1);

namespace App\Actions\Tally;

use App\DTOs\TallyDTO;
use App\Models\Tally;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class UpdateTallyAction
{
    public function handle(Tally $tally, TallyDTO $tallyDTO): Tally
    {
        try {
            DB::transaction(function () use ($tally, $tallyDTO): void {
                $tally->update($tallyDTO->toArray());
            });

            return $tally->refresh();
        } catch (Throwable $e) {
            report($e);
            abort(500);
        }
    }
}
