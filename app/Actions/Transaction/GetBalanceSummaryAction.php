<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Models\Tally;
use Illuminate\Support\Collection;

/**
 * B-04: a user's open balances, built on the `GetTallyDebtsAction` ledger. Amounts are integer
 * minor units and never cross currencies.
 *
 * @phpstan-import-type LedgerCurrency from GetTallyDebtsAction
 * @phpstan-import-type LedgerParty from GetTallyDebtsAction
 * @phpstan-import-type LedgerEntry from GetTallyDebtsAction
 *
 * @phpstan-type MyBalance array{currency: LedgerCurrency, amount: int, direction: 'owed_to_me'|'i_owe'|'settled', counterparty_name: string|null, counterparties_count: int}
 * @phpstan-type OwedToMe array{tally_uuid: string, currency_id: int, currency: LedgerCurrency, counterparty_name: string, amount: int}
 * @phpstan-type SettleSuggestion array{tally_uuid: string, counterparty_name: string, amount: int, currency: LedgerCurrency}
 * @phpstan-type BalanceSummary array{my_balances: array<string, list<MyBalance>>, owed_to_me: list<OwedToMe>}
 */
final readonly class GetBalanceSummaryAction
{
    public function __construct(private GetTallyDebtsAction $debtsAction) {}

    /**
     * The user's balance in each currency of every given tally (`my_balance`, keyed by tally uuid,
     * most-used currency first), and every single debt owed to them, the most recently active tally
     * (latest `transaction_date`, ties to the newer tally) first, then in ledger order: the
     * candidates of `settleSuggestion()`.
     *
     * @param  Collection<int, int>  $tallyIds  tallies the user is a member of
     * @return BalanceSummary
     */
    public function handle(int $userId, Collection $tallyIds): array
    {
        $tallies = Tally::query()
            ->whereKey($tallyIds->all())
            ->with(['owner', 'users']) // @pest-mutate-ignore
            ->withMax('transactions', 'transaction_date')
            ->get()
            ->sortByDesc(fn (Tally $tally): array => [$tally->getAttribute('transactions_max_transaction_date'), $tally->tally_id]);

        $ledgers = $this->debtsAction->ledgers($tallies);
        $myBalances = [];
        $owedToMe = [];

        foreach ($tallies as $tally) {
            $myBalances[$tally->uuid] = [];

            foreach ($ledgers->get($tally->tally_id) ?? [] as $entry) {
                $myBalances[$tally->uuid][] = $this->myBalance($entry, $userId);

                foreach ($entry['settlements'] as $settlement) {
                    if ($settlement['creditor']['id'] === $userId) {
                        $owedToMe[] = [
                            'tally_uuid' => $tally->uuid,
                            'currency_id' => $entry['currency_id'],
                            'currency' => $entry['currency'],
                            'counterparty_name' => $settlement['debtor']['name'],
                            'amount' => $settlement['amount'],
                        ];
                    }
                }
            }
        }

        return ['my_balances' => $myBalances, 'owed_to_me' => $owedToMe];
    }

    /**
     * The debt to suggest settling (Home "Settle"): the largest single debt owed to the user in their
     * primary currency; if none, the largest debt in the first currency of the most recently active
     * tally owed anything. Ties keep the `handle()` order. Amounts are only compared within one
     * currency. Null when nothing is owed to the user.
     *
     * @param  list<OwedToMe>  $owedToMe  as returned by `handle()`
     * @return SettleSuggestion|null
     */
    public function settleSuggestion(array $owedToMe, int $primaryCurrencyId): ?array
    {
        if ($owedToMe === []) {
            return null;
        }

        $candidates = collect($owedToMe);
        $inPrimaryCurrency = $candidates->where('currency_id', $primaryCurrencyId);
        $pool = $inPrimaryCurrency->isNotEmpty()
            ? $inPrimaryCurrency
            : $candidates->where('tally_uuid', $owedToMe[0]['tally_uuid'])->where('currency_id', $owedToMe[0]['currency_id']);

        $pick = $pool->sortByDesc('amount')->firstOrFail();

        return [
            'tally_uuid' => $pick['tally_uuid'],
            'counterparty_name' => $pick['counterparty_name'],
            'amount' => $pick['amount'],
            'currency' => $pick['currency'],
        ];
    }

    /**
     * Per currency of the tally (most-used first), the current members whose position is exactly
     * zero, in member order (the tally detail banner's "X is settled"). A former member is never
     * listed.
     *
     * @return Collection<int, array{currency: LedgerCurrency, members: list<LedgerParty>}>
     */
    public function settledMembers(Tally $tally): Collection
    {
        return $this->debtsAction->ledger($tally)->map(fn (array $entry): array => [
            'currency' => $entry['currency'],
            'members' => array_values(array_map(
                fn (array $position): array => $position['user'],
                array_filter($entry['positions'], fn (array $position): bool => $position['is_member'] && $position['amount'] === 0),
            )),
        ]);
    }

    /**
     * The user's net position in one currency of a tally. The counterparties are the other side of
     * the user's settlements in that currency (a pair is settled at most once).
     *
     * @param  LedgerEntry  $entry
     * @return MyBalance
     */
    private function myBalance(array $entry, int $userId): array
    {
        $amount = collect($entry['positions'])->firstWhere('user.id', $userId)['amount'] ?? 0; // @pest-mutate-ignore

        $counterparties = [];

        foreach ($entry['settlements'] as $settlement) {
            if ($settlement['debtor']['id'] === $userId) {
                $counterparties[] = $settlement['creditor'];
            }

            if ($settlement['creditor']['id'] === $userId) {
                $counterparties[] = $settlement['debtor'];
            }
        }

        $direction = match ($amount <=> 0) {
            1 => 'owed_to_me',
            -1 => 'i_owe',
            0 => 'settled',
        };

        return [
            'currency' => $entry['currency'],
            'amount' => $amount,
            'direction' => $direction,
            'counterparty_name' => count($counterparties) === 1 ? $counterparties[0]['name'] : null,
            'counterparties_count' => count($counterparties),
        ];
    }
}
