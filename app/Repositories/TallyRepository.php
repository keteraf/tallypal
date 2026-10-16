<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\TallyRepositoryInterface;
use App\Enums\TransactionType;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * @phpstan-import-type MonthlyTotalShape from TallyRepositoryInterface
 * @phpstan-import-type TotalShape from TallyRepositoryInterface
 */
final class TallyRepository implements TallyRepositoryInterface
{
    private const int MEMBERS_PREVIEW_LIMIT = 3; // @pest-mutate-ignore

    /**
     * @return Collection<int, array{tally_id: int, uuid: string, name: string, description: string, owner_id: int, color: string|null, glyph: string|null, users_count: int, is_shared: bool, transactions_count: int, last_activity_at: string, members_preview: array<int, array{initials: string, color: string}>, split_label: string|null, totals: array<int, TotalShape>}>
     */
    public function accessibleTalliesWithTotals(int $userId): Collection
    {
        $tallies = Tally::query()
            ->forUser($userId)
            ->withMembersCount()
            ->withCount('transactions')
            ->withMax('transactions', 'transaction_date')
            ->with(['owner', 'users'])
            ->get();

        $totals = $this->totalsPerCurrency($tallies->map(fn (Tally $tally): int => (int) $tally->tally_id)->values());

        return $tallies
            ->map(function (Tally $tally) use ($totals): array {
                /** @var int|string $usersCount */
                $usersCount = $tally->getAttribute('users_count');
                /** @var int|string $transactionsCount */
                $transactionsCount = $tally->getAttribute('transactions_count');
                /** @var string|null $lastTransactionDate */
                $lastTransactionDate = $tally->getAttribute('transactions_max_transaction_date');
                $lastActivityAt = $lastTransactionDate !== null ? CarbonImmutable::parse($lastTransactionDate) : $tally->updated_at;

                return [
                    'tally_id' => (int) $tally->tally_id,
                    'uuid' => (string) $tally->uuid,
                    'name' => (string) $tally->name,
                    'description' => $tally->description,
                    'owner_id' => (int) $tally->owner_id,
                    'color' => $tally->color,
                    'glyph' => $tally->glyph,
                    'users_count' => (int) $usersCount,
                    'is_shared' => (int) $usersCount > 1,
                    'transactions_count' => (int) $transactionsCount,
                    'last_activity_at' => (string) $lastActivityAt?->toIso8601String(),
                    ...$this->memberPreview($tally),
                    'totals' => $totals->get($tally->tally_id, []),
                ];
            })
            ->sortByDesc('last_activity_at')
            ->values();
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
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0); // @pest-mutate-ignore
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{tally_id: int, uuid: string, name: string, is_shared: bool}>
     */
    public function filterOptions(Collection $tallyIds): Collection
    {
        return Tally::query()
            ->whereKey($tallyIds->all())
            ->withMembersCount()
            ->orderBy('name')
            ->orderBy(Tally::PRIMARY_KEY)
            ->get()
            ->map(function (Tally $tally): array {
                /** @var int|string $usersCount */
                $usersCount = $tally->getAttribute('users_count');
                $memberCount = (int) $usersCount; // @pest-mutate-ignore

                return [
                    'tally_id' => (int) $tally->tally_id, // @pest-mutate-ignore
                    'uuid' => (string) $tally->uuid, // @pest-mutate-ignore
                    'name' => (string) $tally->name, // @pest-mutate-ignore
                    'is_shared' => $memberCount > 1,
                ];
            })
            ->values();
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{uuid: string, name: string, color: string|null, glyph: string|null, member_count: int, members_preview: array<int, array{initials: string, color: string}>, split_label: string|null, monthly_totals: array<int, MonthlyTotalShape>}>
     */
    public function tallySummaries(int $userId, Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): Collection
    {
        $tallies = Tally::query()
            ->whereIn(Tally::PRIMARY_KEY, $tallyIds->all())
            ->withMembersCount()
            ->with(['owner', 'users']) // @pest-mutate-ignore
            ->orderByRaw('COALESCE((SELECT MAX(created_at) FROM transactions WHERE transactions.tally_id = tallies.tally_id), tallies.created_at) DESC')
            ->get();

        $monthlyTotals = $this->monthlyTotalsPerCurrency($tallyIds, $monthStart, $now);

        return $tallies->map(function (Tally $tally) use ($monthlyTotals): array {
            /** @var int|string $usersCount */
            $usersCount = $tally->getAttribute('users_count');

            return [
                'uuid' => (string) $tally->uuid, // @pest-mutate-ignore
                'name' => (string) $tally->name, // @pest-mutate-ignore
                'color' => $tally->color,
                'glyph' => $tally->glyph,
                'member_count' => (int) $usersCount, // @pest-mutate-ignore
                ...$this->memberPreview($tally),
                'monthly_totals' => $monthlyTotals->get($tally->tally_id, []),
            ];
        })->values();
    }

    /**
     * Current-month expense total (`amount`, money spent) of each tally (by `transaction_date`, like
     * every other Home total), one entry per currency the tally has ever used (most-used first), so a
     * quiet month still carries the tally's own currency and currencies are never added together.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array<int, MonthlyTotalShape>>
     */
    private function monthlyTotalsPerCurrency(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): Collection
    {
        /** @var Collection<int, object{tally_id: int|string, code: string, symbol: string, decimals: int|string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->select('transactions.tally_id', 'currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN transactions.type = ? AND transactions.transaction_date >= ? AND transactions.transaction_date <= ? THEN ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' ELSE 0 END), 0) as total',
                [TransactionType::EXPENSE->value, $monthStart->toDateString(), $now->toDateString()],
            )
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->groupBy('transactions.tally_id', 'currencies.currency_id', 'currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->orderByRaw('COUNT(*) DESC')
            ->orderBy('currencies.code')
            ->get();

        return $rows
            ->groupBy(fn (object $row): int => (int) $row->tally_id) // @pest-mutate-ignore
            ->map(fn (Collection $currencyRows): array => $currencyRows
                ->map(function (object $row): array {
                    $total = (float) $row->total; // @pest-mutate-ignore
                    $decimals = (int) $row->decimals; // @pest-mutate-ignore

                    return [
                        'amount' => $total,
                        'currency_symbol' => $row->symbol,
                        'currency_code' => $row->code,
                        'decimals' => $decimals,
                    ];
                })
                ->values()
                ->all());
    }

    /**
     * All-time income and expense of each tally, one entry per currency the tally has used (most
     * used first), never summed or converted across currencies.
     *
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array<int, TotalShape>>
     */
    private function totalsPerCurrency(Collection $tallyIds): Collection
    {
        /** @var Collection<int, object{tally_id: int|string, code: string, symbol: string, decimals: int|string, income: float|int|string, expense: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->select('transactions.tally_id', 'currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN transactions.type = ? THEN ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' ELSE 0 END), 0) as income',
                [TransactionType::INCOME->value],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN transactions.type = ? THEN ' . Transaction::NORMALIZED_AMOUNT_EXPRESSION . ' ELSE 0 END), 0) as expense',
                [TransactionType::EXPENSE->value],
            )
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->groupBy('transactions.tally_id', 'currencies.currency_id', 'currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->orderByRaw('COUNT(*) DESC')
            ->orderBy('currencies.code')
            ->get();

        return $rows
            ->groupBy(fn (object $row): int => (int) $row->tally_id) // @pest-mutate-ignore
            ->map(fn (Collection $currencyRows): array => $currencyRows
                ->map(function (object $row): array {
                    $income = (float) $row->income; // @pest-mutate-ignore
                    $expense = (float) $row->expense; // @pest-mutate-ignore
                    $decimals = (int) $row->decimals; // @pest-mutate-ignore

                    return [
                        'income' => $income,
                        'expense' => $expense,
                        'currency_symbol' => $row->symbol,
                        'currency_code' => $row->code,
                        'decimals' => $decimals,
                    ];
                })
                ->values()
                ->all());
    }

    /**
     * The avatar stack and split label shown on a tally card (Home rail and Tallies index).
     * Needs `owner` and `users` loaded.
     *
     * @return array{members_preview: array<int, array{initials: string, color: string}>, split_label: string|null}
     */
    private function memberPreview(Tally $tally): array
    {
        $members = $tally->membersInOrder();

        return [
            'members_preview' => $members->take(self::MEMBERS_PREVIEW_LIMIT)
                ->map(fn (User $member): array => ['initials' => $member->initials(), 'color' => $member->avatarColor()])
                ->all(),
            'split_label' => $this->splitLabel($tally->memberShares()),
        ];
    }

    /**
     * `"60 / 40"`, or null for a solo tally or an equal split (shares within 0.01 of each other).
     *
     * @param  array<int, float>  $shares
     */
    private function splitLabel(array $shares): ?string
    {
        if ($shares === [] || max($shares) - min($shares) < 0.015) { // @pest-mutate-ignore
            return null;
        }

        return implode(' / ', array_map(fn (float $share): string => mb_rtrim(mb_rtrim(sprintf('%.2f', $share), '0'), '.'), $shares)); // @pest-mutate-ignore
    }
}
