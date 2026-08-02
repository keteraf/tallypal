<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @phpstan-type LedgerCurrency array{code: string, symbol: string, decimals: int}
 * @phpstan-type LedgerParty array{id: int, name: string}
 * @phpstan-type LedgerPosition array{user: LedgerParty, is_member: bool, amount: int}
 * @phpstan-type LedgerSettlement array{debtor: LedgerParty, creditor: LedgerParty, amount: int}
 * @phpstan-type LedgerEntry array{currency_id: int, currency: LedgerCurrency, positions: list<LedgerPosition>, settlements: list<LedgerSettlement>}
 * @phpstan-type PaidRow object{tally_id: int, party_id: int, party_name: string, currency_id: int, code: string, symbol: string, decimals: int, paid: float|int|string, uses: int}
 */
final readonly class GetTallyDebtsAction
{
    /**
     * Who owes whom in each currency of a tally, for the settle banner: the ledger's settlements in
     * major units, only for the currencies with something to settle.
     *
     * @return Collection<int, array{
     *     currency: LedgerCurrency,
     *     settlements: non-empty-list<array{
     *         debtor: LedgerParty,
     *         creditor: LedgerParty,
     *         amount: float
     *     }>
     * }>
     */
    public function handle(Tally $tally): Collection
    {
        return $this->ledger($tally)
            ->filter(fn (array $entry): bool => $entry['settlements'] !== [])
            ->map(fn (array $entry): array => [
                'currency' => $entry['currency'],
                'settlements' => array_map(fn (array $settlement): array => [
                    'debtor' => $settlement['debtor'],
                    'creditor' => $settlement['creditor'],
                    'amount' => $settlement['amount'] / 10.0 ** $entry['currency']['decimals'],
                ], $entry['settlements']),
            ])
            ->values();
    }

    /**
     * The tally's ledger: per currency (most-used first), every participant's net position and the
     * settlements that square them, in integer minor units. See `ledgers()`.
     *
     * @return Collection<int, LedgerEntry>
     */
    public function ledger(Tally $tally): Collection
    {
        return $this->ledgers(collect([$tally]))->sole();
    }

    /**
     * The ledger of each tally, keyed by tally id. Per currency the tally has transactions in (most
     * used first, ties to the lowest currency id), never netted across currencies:
     *
     * - participants are the members (`Tally::membersInOrder()`) plus every former member with
     *   transactions in the tally, who has a 0% share;
     * - each participant paid expenses − incomes; the tally's net spent is split by the members'
     *   shares (`Tally::memberShares()`), see `allocate()`;
     * - a position is paid − fair share, so the positions of a currency sum to exactly zero;
     * - the settlements pair the largest creditor with the largest debtor until everyone is square.
     *
     * @param  Collection<int, Tally>  $tallies
     * @return Collection<int, Collection<int, LedgerEntry>>
     */
    public function ledgers(Collection $tallies): Collection
    {
        try {
            $paidRows = $this->paidRows($tallies->map(fn (Tally $tally): int => $tally->tally_id)->all());

            return $tallies->mapWithKeys(fn (Tally $tally): array => [
                $tally->tally_id => $paidRows->get($tally->tally_id, collect())
                    ->groupBy('currency_id')
                    ->sortBy(fn (Collection $currencyRows, int $currencyId): array => [-$currencyRows->sum(fn ($row): int => $row->uses), $currencyId])
                    ->map(fn (Collection $currencyRows): array => $this->currencyEntry($tally, $currencyRows))
                    ->values(),
            ]);
        } catch (Throwable $e) {
            report($e);
            abort(500);
        }
    }

    /**
     * What each party paid net (expenses − incomes) in each currency, in integer minor units, grouped
     * by tally id.
     *
     * @param  array<int, int>  $tallyIds
     * @return Collection<int, Collection<int, PaidRow>>
     */
    private function paidRows(array $tallyIds): Collection
    {
        /** @var Collection<int, Collection<int, PaidRow>> */
        return DB::table(Transaction::TABLE_NAME)
            ->join(User::TABLE_NAME, 'users.id', '=', 'transactions.party_id')
            ->join(Currency::TABLE_NAME, 'currencies.currency_id', '=', 'transactions.currency_id')
            ->select('transactions.tally_id', 'transactions.party_id', 'users.name as party_name', 'transactions.currency_id', 'currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->selectRaw(
                'SUM(CASE WHEN transactions.type = ? THEN ROUND(transactions.amount) ELSE -ROUND(transactions.amount) END) as paid',
                [TransactionType::EXPENSE->value],
            )
            ->selectRaw('COUNT(*) as uses')
            ->whereIn('transactions.tally_id', $tallyIds)
            ->groupBy('transactions.tally_id', 'transactions.party_id', 'users.name', 'transactions.currency_id', 'currencies.code', 'currencies.symbol', 'currencies.decimals')
            ->get()
            ->groupBy('tally_id');
    }

    /**
     * @param  Collection<int, PaidRow>  $currencyRows  one row per party with transactions in this currency (never empty)
     * @return LedgerEntry
     */
    private function currencyEntry(Tally $tally, Collection $currencyRows): array
    {
        $currency = $currencyRows->firstOrFail();
        $paid = $currencyRows->keyBy('party_id');
        $members = $tally->membersInOrder();
        $shares = $tally->memberShares();

        $participants = $members
            ->map(fn (User $member): array => ['user' => ['id' => $member->id, 'name' => $member->name], 'is_member' => true, 'weight' => (int) round($shares[$member->id] * 100)])
            ->concat($paid->except($members->map(fn (User $member): int => $member->id)->all())->sortKeys()->map(
                fn (object $row, int $partyId): array => ['user' => ['id' => $partyId, 'name' => $row->party_name], 'is_member' => false, 'weight' => 0],
            ))
            ->values()
            ->all();

        $weights = array_column($participants, 'weight');

        if (array_sum($weights) === 0) {
            $weights = array_map(fn (array $participant): int => (int) $participant['is_member'], $participants);
        }

        $fairShares = $this->allocate($paid->sum(fn ($row): int => (int) $row->paid), $weights);

        $positions = array_map(fn (array $participant, int $fairShare): array => [
            'user' => $participant['user'],
            'is_member' => $participant['is_member'],
            'amount' => (int) ($paid->get($participant['user']['id'])->paid ?? 0) - $fairShare,
        ], $participants, $fairShares);

        return [
            'currency_id' => $currency->currency_id,
            'currency' => ['code' => $currency->code, 'symbol' => $currency->symbol, 'decimals' => $currency->decimals],
            'positions' => $positions,
            'settlements' => $this->settle($positions),
        ];
    }

    /**
     * Split `$total` minor units by integer weights, largest remainder first: every share is floored,
     * then the units left over go one each to the largest fractional remainders, ties to the earlier
     * participant (members in `Tally::membersInOrder()` order). A negative total (more income than
     * spending) is split by its magnitude and given back its sign. The shares sum to `$total`.
     *
     * @param  array<int, int>  $weights
     * @return array<int, int>
     */
    private function allocate(int $total, array $weights): array
    {
        $sign = $total <=> 0;
        $magnitude = abs($total);
        $weightSum = array_sum($weights);
        $shares = [];
        $remainders = [];

        foreach ($weights as $index => $weight) {
            $shares[$index] = intdiv($magnitude * $weight, $weightSum);
            $remainders[$index] = $magnitude * $weight % $weightSum;
        }

        arsort($remainders);

        foreach (array_slice(array_keys($remainders), 0, $magnitude - array_sum($shares)) as $index) {
            $shares[$index]++;
        }

        return array_map(fn (int $share): int => $share * $sign, $shares);
    }

    /**
     * Pair the largest creditor with the largest debtor until one of them is square, repeatedly. The
     * positions sum to zero, so the creditors run out exactly when the debtors do and everyone ends
     * square; a participant at zero is never paired, and a pair is never settled twice. Equal
     * positions keep participant order.
     *
     * @param  list<LedgerPosition>  $positions
     * @return list<LedgerSettlement>
     */
    private function settle(array $positions): array
    {
        usort($positions, fn (array $a, array $b): int => $b['amount'] <=> $a['amount']);

        $parties = array_column($positions, 'user');
        $amounts = array_column($positions, 'amount');
        $settlements = [];
        $creditor = 0;
        $debtor = count($positions) - 1;

        while ($amounts[$creditor] > 0) {
            $amount = min($amounts[$creditor], -$amounts[$debtor]);
            $settlements[] = ['debtor' => $parties[$debtor], 'creditor' => $parties[$creditor], 'amount' => $amount];
            $amounts[$creditor] -= $amount;
            $amounts[$debtor] += $amount;

            if ($amounts[$creditor] === 0) {
                $creditor++;
            }

            if ($amounts[$debtor] === 0) {
                $debtor--;
            }
        }

        return $settlements;
    }
}
