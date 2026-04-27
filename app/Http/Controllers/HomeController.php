<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

use function abort_unless;

/**
 * @phpstan-type CurrencyShape array{symbol: string, code: string, decimals: int, currency_id: int}
 */
final class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless((bool) $user, 401);

        $userId = (int) $user->id;
        $now = CarbonImmutable::now();
        $monthStart = $now->startOfMonth();
        $weekHistoryStart = $now->startOfWeek()->subWeeks(11);

        $tallyIds = $this->getAccessibleTallyIds($userId);

        if ($tallyIds->isEmpty()) {
            return Inertia::render('Home', $this->emptyPayload($user, $now));
        }

        $primaryCurrency = $this->getPrimaryCurrency($tallyIds);
        $tallySummaries = $this->getTallySummaries($userId, $tallyIds);
        $primaryTally = $tallySummaries->first();

        return Inertia::render('Home', [
            'user_name' => $user->name,
            'current_month_label' => $now->format('F Y'),
            'primary_tally' => $primaryTally === null ? null : [
                'uuid' => $primaryTally['uuid'],
                'name' => $primaryTally['name'],
            ],
            'tallies' => $tallySummaries->all(),
            'balance' => $this->getMonthlyBalance($tallyIds, $monthStart, $now, $primaryCurrency),
            'recent_transactions' => $this->getRecentTransactions($tallyIds),
            'weekly_history' => $this->getWeeklyHistory($tallyIds, $weekHistoryStart, $now),
            'categories_breakdown' => $this->getCategoriesBreakdown($tallyIds, $monthStart, $now),
            'shared_tally' => $this->getSharedTallySummary($tallyIds, $monthStart, $now),
        ]);
    }

    /**
     * @return Collection<int, int>
     */
    private function getAccessibleTallyIds(int $userId): Collection
    {
        /** @var Collection<int, int> */
        return Tally::query()
            ->where('owner_id', $userId)
            ->orWhereHas('users', fn ($query) => $query->where('user_id', $userId))
            ->pluck(Tally::PRIMARY_KEY)
            ->map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0);
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return CurrencyShape
     */
    private function getPrimaryCurrency(Collection $tallyIds): array
    {
        /** @var object{currency_id: int|string, symbol: string, code: string, decimals: int|string}|null $row */
        $row = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->select('currencies.currency_id', 'currencies.symbol', 'currencies.code', 'currencies.decimals', DB::raw('COUNT(*) as cnt'))
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->groupBy('currencies.currency_id', 'currencies.symbol', 'currencies.code', 'currencies.decimals')
            ->orderByDesc('cnt')
            ->first();

        if ($row === null) {
            return ['symbol' => '€', 'code' => 'EUR', 'decimals' => 2, 'currency_id' => 0];
        }

        return [
            'symbol' => $row->symbol,
            'code' => $row->code,
            'decimals' => (int) $row->decimals,
            'currency_id' => (int) $row->currency_id,
        ];
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return Collection<int, array{uuid: string, name: string, member_count: int, monthly_total: float}>
     */
    private function getTallySummaries(int $userId, Collection $tallyIds): Collection
    {
        $monthStart = CarbonImmutable::now()->startOfMonth();

        $tallies = Tally::query()
            ->whereIn(Tally::PRIMARY_KEY, $tallyIds->all())
            ->withCount('users')
            ->addSelect([
                'last_tx_at' => DB::table(Transaction::TABLE_NAME)
                    ->selectRaw('MAX(created_at)')
                    ->whereColumn('transactions.tally_id', 'tallies.tally_id'),
                'monthly_total' => DB::table(Transaction::TABLE_NAME)
                    ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
                    ->selectRaw('COALESCE(SUM(transactions.amount / POWER(10, currencies.decimals)), 0)')
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
     * @param  CurrencyShape  $primaryCurrency
     * @return array{net: float, income: float, expense: float, currency_symbol: string, currency_code: string, decimals: int}
     */
    private function getMonthlyBalance(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now, array $primaryCurrency): array
    {
        /** @var object{income: float|int|string, expense: float|int|string}|null $row */
        $row = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN transactions.type = ? THEN transactions.amount / POWER(10, currencies.decimals) ELSE 0 END), 0) as income', [TransactionType::INCOME->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN transactions.type = ? THEN transactions.amount / POWER(10, currencies.decimals) ELSE 0 END), 0) as expense', [TransactionType::EXPENSE->value])
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.transaction_date', '>=', $monthStart->toDateString())
            ->where('transactions.transaction_date', '<=', $now->toDateString())
            ->first();

        $income = (float) ($row->income ?? 0);
        $expense = (float) ($row->expense ?? 0);

        return [
            'net' => $income - $expense,
            'income' => $income,
            'expense' => $expense,
            'currency_symbol' => $primaryCurrency['symbol'],
            'currency_code' => $primaryCurrency['code'],
            'decimals' => $primaryCurrency['decimals'],
        ];
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{uuid: string, title: string, type: string, amount: float, transaction_date: string, category_name: string|null, category_code: string, tally_name: string, currency_symbol: string}>
     */
    private function getRecentTransactions(Collection $tallyIds): array
    {
        return Transaction::query()
            ->whereIn('tally_id', $tallyIds->all())
            ->with(['currency', 'tally'])
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->select('transactions.*', 'categories.name as category_name')
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(7)
            ->get()
            ->map(function (Transaction $tx): array {
                /** @var string|null $rawCategoryName */
                $rawCategoryName = $tx->getAttribute('category_name');
                $currency = $tx->currency;

                return [
                    'uuid' => (string) $tx->uuid,
                    'title' => (string) $tx->title,
                    'type' => $tx->type->value,
                    'amount' => $tx->amount,
                    'transaction_date' => (string) $tx->transaction_date,
                    'category_name' => $rawCategoryName,
                    'category_code' => $this->categoryCode($rawCategoryName),
                    'tally_name' => $tx->tally === null ? '' : (string) $tx->tally->name,
                    'currency_symbol' => $currency === null ? '' : (string) $currency->symbol,
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{label: string, amount: float, is_current: bool}>
     */
    private function getWeeklyHistory(Collection $tallyIds, CarbonImmutable $start, CarbonImmutable $now): array
    {
        /** @var Collection<int, object{transaction_date: string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->selectRaw('transactions.transaction_date, transactions.amount / POWER(10, currencies.decimals) as total')
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.transaction_date', '>=', $start->toDateString())
            ->get();

        $weekTotals = [];

        foreach ($rows as $row) {
            $key = CarbonImmutable::parse((string) $row->transaction_date)->format('oW');
            $rawTotal = $row->total;
            $value = is_numeric($rawTotal) ? (float) $rawTotal : 0.0;
            $weekTotals[$key] = ($weekTotals[$key] ?? 0.0) + $value;
        }

        $weeks = [];
        $cursor = $start;

        for ($i = 0; $i < 12; $i++) {
            $key = $cursor->format('oW');
            $weeks[] = [
                'label' => 'W ' . $cursor->format('W'),
                'amount' => $weekTotals[$key] ?? 0.0,
                'is_current' => $cursor->isSameWeek($now),
            ];
            $cursor = $cursor->addWeek();
        }

        return $weeks;
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array<int, array{name: string, amount: float, percentage: float}>
     */
    private function getCategoriesBreakdown(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): array
    {
        /** @var Collection<int, object{category_name: string, total: float|int|string}> $rows */
        $rows = DB::table(Transaction::TABLE_NAME)
            ->join('currencies', 'transactions.currency_id', '=', 'currencies.currency_id')
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.category_id')
            ->selectRaw('COALESCE(categories.name, ?) as category_name, COALESCE(SUM(transactions.amount / POWER(10, currencies.decimals)), 0) as total', ['Uncategorised'])
            ->whereIn('transactions.tally_id', $tallyIds->all())
            ->where('transactions.type', TransactionType::EXPENSE->value)
            ->where('transactions.transaction_date', '>=', $monthStart->toDateString())
            ->where('transactions.transaction_date', '<=', $now->toDateString())
            ->groupByRaw('COALESCE(categories.name, ?)', ['Uncategorised'])
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $rawTotal = $rows->sum('total');
        $totalSum = is_numeric($rawTotal) ? (float) $rawTotal : 0.0;

        return $rows->map(function (object $row) use ($totalSum): array {
            $amount = (float) $row->total;

            return [
                'name' => $row->category_name,
                'amount' => $amount,
                'percentage' => $totalSum > 0 ? round(($amount / $totalSum) * 100, 1) : 0.0,
            ];
        })->all();
    }

    /**
     * @param  Collection<int, int>  $tallyIds
     * @return array{uuid: string, name: string, total_spent: float, members: array<int, array{name: string, initials: string, amount: float, percentage: float}>}|null
     */
    private function getSharedTallySummary(Collection $tallyIds, CarbonImmutable $monthStart, CarbonImmutable $now): ?array
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
            ->selectRaw('transactions.party_id, COALESCE(SUM(transactions.amount / POWER(10, currencies.decimals)), 0) as total')
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
            'uuid' => (string) $sharedTally->uuid,
            'name' => (string) $sharedTally->name,
            'total_spent' => $totalSpent,
            'members' => $members->map(function (User $member) use ($perUser, $totalSpent): array {
                $amount = (float) ($perUser[$member->id] ?? 0);

                return [
                    'name' => (string) $member->name,
                    'initials' => $this->initials((string) $member->name),
                    'amount' => $amount,
                    'percentage' => $totalSpent > 0 ? round(($amount / $totalSpent) * 100, 1) : 0.0,
                ];
            })->all(),
        ];
    }

    private function categoryCode(?string $name): string
    {
        if ($name === null) {
            return '··';
        }

        $cleaned = preg_replace('/[^A-Za-z]/', '', $name) ?? '';

        return $cleaned === ''
            ? '··'
            : mb_strtoupper(mb_substr($cleaned, 0, 2));
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

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(User $user, CarbonImmutable $now): array
    {
        return [
            'user_name' => $user->name,
            'current_month_label' => $now->format('F Y'),
            'primary_tally' => null,
            'tallies' => [],
            'balance' => [
                'net' => 0.0,
                'income' => 0.0,
                'expense' => 0.0,
                'currency_symbol' => '€',
                'currency_code' => 'EUR',
                'decimals' => 2,
            ],
            'recent_transactions' => [],
            'weekly_history' => [],
            'categories_breakdown' => [],
            'shared_tally' => null,
        ];
    }
}
