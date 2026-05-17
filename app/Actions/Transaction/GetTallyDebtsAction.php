<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\TallyUser;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Throwable;

final readonly class GetTallyDebtsAction
{
    /**
     * Calculate who owes what based on expenses and incomes in a tally.
     *
     * @return Collection<int, array{
     *     currency: Currency,
     *     settlements: array<int, array{
     *         debtor: array{id: int, name: string},
     *         creditor: array{id: int, name: string},
     *         amount: float
     *     }>
     * }>
     */
    public function handle(Tally $tally): Collection
    {
        try {
            /** @var Collection<int, array{currency: Currency, settlements: array<int, array{debtor: array{id: int, name: string}, creditor: array{id: int, name: string}, amount: float}>}> $debtsPerCurrency */
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
                        'currency' => $currency,
                        'settlements' => $settlements,
                    ]);
                }
            }

            return $debtsPerCurrency;
        } catch (Throwable $e) {
            abort($e->getCode(), $e->getMessage());
        }
    }
}
