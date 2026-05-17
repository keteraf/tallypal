<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\TransactionType;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Contracts\TallyRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TallyRepository implements TallyRepositoryInterface
{
    /**
     * @return Collection<int, Tally>
     */
    public function accessibleTalliesWithTotals(int $userId): Collection
    {
        return Tally::query()
            ->with('owner')
            ->forUser($userId)
            ->withIncomeAndExpenseTotals()
            ->get();
    }

    /**
     * @return Collection<int, int>
     */
    public function accessibleTallyIds(int $userId): Collection
    {
        /** @var Collection<int, int> */
        return Tally::query()
            ->forUser($userId)
            ->pluck(Tally::PRIMARY_KEY)
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0);
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{uuid: string, name: string, member_count: int, monthly_total: float}>
     */
    public function tallySummaries(int $userId, Collection $tallyIds, CarbonImmutable $monthStart): Collection
    {
        $tallies = Tally::query()
            ->whereIn(Tally::PRIMARY_KEY, $tallyIds->all())
            ->withCount('users')
            ->addSelect([
                'last_tx_at' => DB::table(Transaction::TABLE_NAME)
                    ->selectRaw('MAX(created_at)')
                    ->whereColumn('transactions.tally_id', 'tallies.tally_id'),
                'monthly_total' => DB::table(Transaction::TABLE_NAME)
                    ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
                    ->selectRaw('COALESCE(SUM(' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . '), 0)')
                    ->whereColumn('transactions.tally_id', 'tallies.tally_id')
                    ->where('transactions.type', TransactionType::EXPENSE->value)
                    ->where('transactions.created_at', '>=', $monthStart),
            ])
            ->orderByRaw('COALESCE((SELECT MAX(created_at) FROM transactions WHERE transactions.tally_id = tallies.tally_id), tallies.created_at) DESC')
            ->get();

        return $tallies->map(function (Tally $tally) use ($userId): array {
            /** @var int|string|null $usersCount */
            $usersCount = $tally->getAttribute('users_count');
            /** @var float|int|string|null $monthlyTotal */
            $monthlyTotal = $tally->getAttribute('monthly_total');

            return [
                'uuid' => (string) $tally->uuid,
                'name' => (string) $tally->name,
                'member_count' => (int) ($usersCount ?? 0) + ($tally->owner_id === $userId ? 1 : 0),
                'monthly_total' => (float) ($monthlyTotal ?? 0),
            ];
        })->values();
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array{uuid: string, name: string, total_spent: float, members: array<int, array{name: string, initials: string, amount: float, percentage: float}>}|null
     */
    public function sharedTallySummary(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): ?array
    {
        /** @var Tally|null $sharedTally */
        $sharedTally = Tally::query()
            ->whereIn(Tally::PRIMARY_KEY, $tallyIds->all())
            ->whereHas('users')
            ->withCount('users')
            ->orderByDesc('users_count')
            ->orderByDesc('updated_at')
            ->with(['users', 'owner'])
            ->first();

        if ($sharedTally === null) {
            return null;
        }

        /** @var Collection<int, float|int|string> $perUser */
        $perUser = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('transactions.party_id, COALESCE(SUM(' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . '), 0) as total')
            ->where('transactions.tally_id', $sharedTally->getKey())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.transaction_date', '>=', $monthStart->toDateString())
            ->where('transactions.transaction_date', '<=', $now->toDateString())
            ->groupBy('transactions.party_id')
            ->pluck('total', 'transactions.party_id');

        $rawSpent = $perUser->sum();
        $totalSpent = is_numeric($rawSpent) ? (float) $rawSpent : 0.0;

        $owner = $sharedTally->owner;
        /** @var Collection<int, User> $members */
        $members = collect([$owner])
            ->merge($sharedTally->users)
            ->filter()
            ->unique('id')
            ->values();

        return [
            'uuid' => $sharedTally->uuid,
            'name' => $sharedTally->name,
            'total_spent' => $totalSpent,
            'members' => $members->map(function (User $member) use ($perUser, $totalSpent): array {
                $amount = (float) ($perUser[$member->id] ?? 0);

                return [
                    'name' => $member->name,
                    'initials' => $this->initials($member->name),
                    'amount' => $amount,
                    'percentage' => $totalSpent > 0 ? round(($amount / $totalSpent) * 100, 1) : 0.0,
                ];
            })->all(),
        ];
    }

    private function initials(string $name): string
    {
        $trimmed = mb_trim($name);
        $parts = preg_split('/\s+/', mb_strtoupper($trimmed)) ?: [];
        $initials = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $initials .= mb_substr($part, 0, 1);

            if (mb_strlen($initials) === 2) {
                break;
            }
        }

        return $initials === '' ? '··' : $initials;
    }
}
