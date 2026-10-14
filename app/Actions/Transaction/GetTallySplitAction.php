<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Throwable;

final readonly class GetTallySplitAction
{
    /**
     * Calculate per-member paid amount and percentage per currency for a tally.
     *
     * @return Collection<int, array{
     *     currency: array{code: string, symbol: string, decimals: int},
     *     members: array<int, array{name: string, initials: string, amount: float, percentage: float}>
     * }>
     */
    public function handle(Tally $tally): Collection
    {
        try {
            /** @var Collection<int, array{currency: array{code: string, symbol: string, decimals: int}, members: array<int, array{name: string, initials: string, amount: float, percentage: float}>}> $splitsPerCurrency */
            $splitsPerCurrency = collect();

            /** @var Collection<int, User> $users */
            $users = $tally->users()->get();
            if ($users->isEmpty()) {
                return $splitsPerCurrency;
            }

            /** @var Collection<int, Transaction> $transactions */
            $transactions = $tally->transactions()
                ->with(['currency', 'party'])
                ->get();

            /** @var Collection<int, Currency> $currencies */
            $currencies = $transactions->pluck('currency')->unique('currency_id')->filter();

            foreach ($currencies as $currency) {
                $currencyTransactions = $transactions->where('currency_id', $currency->getKey());

                /** @var float $totalSpent */
                $totalSpent = $currencyTransactions->where('type', TransactionType::EXPENSE)->sum('amount');

                if ($totalSpent <= 0) {
                    continue;
                }

                $members = [];
                foreach ($users as $user) {
                    /** @var float $amount */
                    $amount = $currencyTransactions
                        ->where('party_id', $user->id)
                        ->where('type', TransactionType::EXPENSE)
                        ->sum('amount');

                    $members[] = [
                        'name' => $user->name,
                        'initials' => $this->initials($user->name),
                        'amount' => $amount,
                        'percentage' => round($amount / $totalSpent * 100, 1),
                    ];
                }

                $splitsPerCurrency->push([
                    'currency' => [
                        'code' => $currency->code,
                        'symbol' => $currency->symbol,
                        'decimals' => $currency->decimals,
                    ],
                    'members' => $members,
                ]);
            }

            return $splitsPerCurrency;
        } catch (Throwable $e) {
            report($e);
            abort(500);
        }
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
