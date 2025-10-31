<?php

declare(strict_types=1);

namespace App\Actions\Tally;

use App\DTOs\TallyDTO;
use App\Models\Tally;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class StoreTallyAction
{
    /**
     * @throws Throwable
     */
    public function handle(TallyDTO $tallyDTO): Tally
    {
        return DB::transaction(function () use ($tallyDTO): Tally {
            $tally = Tally::query()->create([
                'owner_id' => $tallyDTO->getOwnerId(),
                'name' => $tallyDTO->getName(),
                'description' => $tallyDTO->getDescription(),
            ]);

            $tally->users()->attach($tallyDTO->getOwnerId(), [
                'default_share_percentage' => 100.00,
            ]);

            return $tally;
        });
    }
}
