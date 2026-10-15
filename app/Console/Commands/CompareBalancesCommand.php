<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Transaction\GetTallyDebtsAction;
use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\TallyUser;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * One-off, read-only check before deploying B-04: for every tally, compares the debts the pre-B-04
 * `GetTallyDebtsAction::handle()` produced (floats, members' pivot rows only) with the new integer
 * `ledger()`. A user's net is what the settlements say they get (+) or pay (−), in minor units.
 *
 * @phpstan-import-type LedgerEntry from GetTallyDebtsAction
 */
class CompareBalancesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'tallypal:compare-balances';

    /**
     * @var string
     */
    protected $description = 'Read-only: compare the pre-B-04 debts netting with the new ledger for every tally';

    public function handle(GetTallyDebtsAction $debtsAction): int
    {
        $scanned = 0;
        $balancesChanged = 0;
        $settlementsChanged = 0;
        /** @var array<string, array{symbol: string, decimals: int, difference: int, tally: string}> $largest */
        $largest = [];

        Tally::query()->with(['owner', 'users'])->chunkById(100, function (Collection $tallies) use ($debtsAction, &$scanned, &$balancesChanged, &$settlementsChanged, &$largest): void {
            /** @var Collection<int, Tally> $tallies */
            $ledgers = $debtsAction->ledgers($tallies);

            foreach ($tallies as $tally) {
                $scanned++;
                $old = $this->legacyDebts($tally);
                /** @var Collection<int, LedgerEntry> $new */
                $new = $ledgers->get($tally->tally_id) ?? collect();
                $tallyChanged = false;

                foreach ($new as $entry) {
                    $currency = $entry['currency'];
                    $oldEntry = $old->firstWhere('currency.code', $currency['code']);
                    $oldSettlements = collect($oldEntry['settlements'] ?? [])->map(fn (array $settlement): array => [
                        'debtor' => $settlement['debtor']['id'],
                        'creditor' => $settlement['creditor']['id'],
                        'amount' => (int) round($settlement['amount'] * 10 ** $currency['decimals']),
                    ]);
                    $newSettlements = collect($entry['settlements'])->map(fn (array $settlement): array => [
                        'debtor' => $settlement['debtor']['id'],
                        'creditor' => $settlement['creditor']['id'],
                        'amount' => $settlement['amount'],
                    ]);

                    $oldNet = $this->netPerUser($oldSettlements);
                    $newNet = $this->netPerUser($newSettlements);
                    $difference = 0;

                    foreach (array_keys($oldNet + $newNet) as $userId) {
                        $difference = max($difference, abs(($newNet[$userId] ?? 0) - ($oldNet[$userId] ?? 0)));
                    }

                    $tallyChanged = $tallyChanged || $difference > 0;
                    $settlementsChanged += (int) ($oldSettlements->sortBy(['debtor', 'creditor'])->values()->all() !== $newSettlements->sortBy(['debtor', 'creditor'])->values()->all());

                    $current = $largest[$currency['code']] ?? ['symbol' => $currency['symbol'], 'decimals' => $currency['decimals'], 'difference' => 0, 'tally' => '—'];

                    if ($difference > $current['difference']) {
                        $current = [...$current, 'difference' => $difference, 'tally' => $tally->uuid];
                    }

                    $largest[$currency['code']] = $current;
                }

                $balancesChanged += (int) $tallyChanged;
            }
        });

        $this->line("Tallies scanned: {$scanned}");
        $this->line("Tallies whose balances change: {$balancesChanged}");
        $this->line("Currency blocks whose settlements change: {$settlementsChanged}");
        $this->table(
            ['Currency', 'Largest difference', 'Tally'],
            collect($largest)->map(fn (array $row, string $code): array => [
                $code,
                $row['symbol'] . ' ' . number_format($row['difference'] / 10 ** $row['decimals'], $row['decimals'], '.', ''),
                $row['tally'],
            ])->values()->all(),
        );

        return self::SUCCESS;
    }

    /**
     * What each user gets (+) or pays (−) according to a list of settlements, in minor units.
     *
     * @param  Collection<int, array{debtor: int, creditor: int, amount: int}>  $settlements
     * @return array<int, int>
     */
    private function netPerUser(Collection $settlements): array
    {
        $net = [];

        foreach ($settlements as $settlement) {
            $net[$settlement['creditor']] = ($net[$settlement['creditor']] ?? 0) + $settlement['amount'];
            $net[$settlement['debtor']] = ($net[$settlement['debtor']] ?? 0) - $settlement['amount'];
        }

        return $net;
    }

    /**
     * Verbatim copy of `GetTallyDebtsAction::handle()` before B-04 (commit 3968aac), minus its
     * try/catch: float amounts, a 0.001 tolerance, only users with a `tally_user` row.
     *
     * @return Collection<int, array{currency: array{code: string, symbol: string, decimals: int}, settlements: array<int, array{debtor: array{id: int, name: string}, creditor: array{id: int, name: string}, amount: float}>}>
     */
    private function legacyDebts(Tally $tally): Collection
    {
        /** @var Collection<int, array{currency: array{code: string, symbol: string, decimals: int}, settlements: array<int, array{debtor: array{id: int, name: string}, creditor: array{id: int, name: string}, amount: float}>}> $debtsPerCurrency */
        $debtsPerCurrency = collect();

        /** @var Collection<int, User> $users */
        $users = $tally->users()->get();
        if ($users->isEmpty()) {
            return $debtsPerCurrency;
        }

        /** @var Collection<int, Transaction> $transactions */
        $transactions = $tally->transactions()
            ->with(['currency', 'party'])
            ->get();

        /** @var Collection<int, Currency> $currencies */
        $currencies = $transactions->pluck('currency')->unique('currency_id')->filter();

        foreach ($currencies as $currency) {
            $currencyTransactions = $transactions->where('currency_id', $currency->getKey());

            /** @var float $totalExpenses */
            $totalExpenses = $currencyTransactions->where('type', TransactionType::EXPENSE)->sum('amount');
            /** @var float $totalIncomes */
            $totalIncomes = $currencyTransactions->where('type', TransactionType::INCOME)->sum('amount');
            $netTotalSpent = $totalExpenses - $totalIncomes;

            $userBalances = [];
            foreach ($users as $user) {
                /** @var float $userExpenses */
                $userExpenses = $currencyTransactions->where('party_id', $user->id)->where('type', TransactionType::EXPENSE)->sum('amount');
                /** @var float $userIncomes */
                $userIncomes = $currencyTransactions->where('party_id', $user->id)->where('type', TransactionType::INCOME)->sum('amount');
                $userNetPaid = $userExpenses - $userIncomes;

                /** @var TallyUser|null $pivot */
                $pivot = $user->pivot;
                $userPercentage = $pivot->default_share_percentage ?? 0;
                $userFairShare = $netTotalSpent * ($userPercentage / 100);

                $balance = $userNetPaid - $userFairShare;

                if (abs($balance) > 0.001) {
                    $userBalances[] = [
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                        ],
                        'balance' => $balance,
                    ];
                }
            }

            $creditors = array_filter($userBalances, fn (array $b): bool => $b['balance'] > 0);
            $debtors = array_filter($userBalances, fn (array $b): bool => $b['balance'] < 0);

            usort($creditors, fn (array $a, array $b): int => $b['balance'] <=> $a['balance']);
            usort($debtors, fn (array $a, array $b): int => $a['balance'] <=> $b['balance']);

            $settlements = [];
            $cIdx = 0;
            $dIdx = 0;

            while ($cIdx < count($creditors) && $dIdx < count($debtors)) {
                $creditor = &$creditors[$cIdx];
                $debtor = &$debtors[$dIdx];

                $amountToSettle = min($creditor['balance'], abs($debtor['balance']));

                if ($amountToSettle > 0.001) {
                    $settlements[] = [
                        'debtor' => $debtor['user'],
                        'creditor' => $creditor['user'],
                        'amount' => round($amountToSettle, $currency->decimals),
                    ];
                }

                $creditor['balance'] -= $amountToSettle;
                $debtor['balance'] += $amountToSettle;

                if ($creditor['balance'] < 0.001) {
                    $cIdx++;
                }
                if (abs($debtor['balance']) < 0.001) {
                    $dIdx++;
                }
            }

            if ($settlements !== []) {
                $debtsPerCurrency->push([
                    'currency' => [
                        'code' => $currency->code,
                        'symbol' => $currency->symbol,
                        'decimals' => $currency->decimals,
                    ],
                    'settlements' => $settlements,
                ]);
            }
        }

        return $debtsPerCurrency;
    }
}
