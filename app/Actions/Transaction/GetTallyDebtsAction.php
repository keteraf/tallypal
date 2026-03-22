<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Enums\TransactionType;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetTallyDebtsAction
{
    /**
     * Calculate who owes what based on expenses and incomes in a tally.
     *
     * @return Collection<int, array{
     *     currency: mixed,
     *     settlements: array<int, array{
     *         debtor: array{id: int, name: string},
     *         creditor: array{id: int, name: string},
     *         amount: float
     *     }>
     * }>
     */
    public function handle(Tally $tally): Collection
    {
        // 1. Get all users and their share percentages
        $users = $tally->users()->get();
        if ($users->isEmpty()) {
            return collect();
        }

        // 2. Get total expenses and incomes per currency and per user
        $transactions = $tally->transactions()
            ->with(['currency', 'party'])
            ->get();

        $currencies = $transactions->pluck('currency')->unique('currency_id')->filter();

        $debtsPerCurrency = collect();

        foreach ($currencies as $currency) {
            $currencyTransactions = $transactions->where('currency_id', $currency->getKey());

            // Total spent in this currency (Expenses - Incomes)
            $totalExpenses = $currencyTransactions->where('type', TransactionType::EXPENSE)->sum('amount');
            $totalIncomes = $currencyTransactions->where('type', TransactionType::INCOME)->sum('amount');
            $netTotalSpent = $totalExpenses - $totalIncomes;

            // Balance for each user
            $userBalances = [];
            foreach ($users as $user) {
                $userExpenses = $currencyTransactions->where('party_id', $user->id)->where('type', TransactionType::EXPENSE)->sum('amount');
                $userIncomes = $currencyTransactions->where('party_id', $user->id)->where('type', TransactionType::INCOME)->sum('amount');
                $userNetPaid = $userExpenses - $userIncomes;

                $userPercentage = $user->pivot->default_share_percentage ?? 0;
                $userFairShare = $netTotalSpent * ($userPercentage / 100);

                $balance = $userNetPaid - $userFairShare;

                // We use a small epsilon for floating point comparison
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

            // Settle debts
            $creditors = array_filter($userBalances, fn ($b) => $b['balance'] > 0);
            $debtors = array_filter($userBalances, fn ($b) => $b['balance'] < 0);

            // Sort them to settle largest debts first (heuristic)
            usort($creditors, fn ($a, $b) => $b['balance'] <=> $a['balance']);
            usort($debtors, fn ($a, $b) => $a['balance'] <=> $b['balance']);

            $settlements = [];
            $cIdx = 0;
            $dIdx = 0;

            $creditors = array_values($creditors);
            $debtors = array_values($debtors);

            while ($cIdx < count($creditors) && $dIdx < count($debtors)) {
                $creditor = &$creditors[$cIdx];
                $debtor = &$debtors[$dIdx];

                $amountToSettle = min($creditor['balance'], abs($debtor['balance']));

                if ($amountToSettle > 0.001) {
                    $settlements[] = [
                        'debtor' => $debtor['user'],
                        'creditor' => $creditor['user'],
                        'amount' => round($amountToSettle, $currency->decimals ?? 2),
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

            if (! empty($settlements)) {
                $debtsPerCurrency->push([
                    'currency' => $currency,
                    'settlements' => $settlements,
                ]);
            }
        }

        return $debtsPerCurrency;
    }
}
