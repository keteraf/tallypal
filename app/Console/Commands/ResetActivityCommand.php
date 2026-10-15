<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tally;
use App\Models\TallyInvitation;
use App\Models\TallyUser;
use App\Models\Transaction;
use App\Models\User;
use App\Services\HomeCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetActivityCommand extends Command
{
    protected $signature = 'tallypal:reset-activity {--force : Delete the rows instead of only counting them}';

    protected $description = 'Delete every transaction, invitation, membership and tally (users and reference data are kept)';

    public function handle(HomeCacheService $homeCache): int
    {
        if (! config('app.allow_activity_reset')) {
            $this->error('Refusing to run: set ALLOW_ACTIVITY_RESET=true in the environment for this wipe, then remove it.');

            return self::FAILURE;
        }

        $counts = [
            'Transactions' => Transaction::TABLE_NAME,
            'Tally invitations' => TallyInvitation::TABLE_NAME,
            'Memberships' => TallyUser::TABLE_NAME,
            'Tallies' => Tally::TABLE_NAME,
        ];

        $deleted = [];

        if (! $this->option('force')) {
            foreach ($counts as $label => $table) {
                $this->line("{$label}: " . DB::table($table)->count());
            }

            $this->warn('Nothing deleted. Run again with --force to delete these rows.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($counts, &$deleted): void {
            foreach ($counts as $label => $table) {
                $deleted[$label] = DB::table($table)->delete();
            }
        });

        foreach (User::query()->get(['id']) as $user) {
            $homeCache->forgetForUser($user->id);
        }

        foreach ($deleted as $label => $count) {
            $this->line("{$label}: {$count}");
        }

        return self::SUCCESS;
    }
}
