<?php

declare(strict_types=1);

use App\Actions\TallyShare\DeleteTallyShareAction;
use App\Actions\Transaction\GetBalanceSummaryAction;
use App\Actions\Transaction\GetTallyDebtsAction;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(GetBalanceSummaryAction::class);

beforeEach(function (): void {
    $this->eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $this->usd = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);
    $this->gbp = Currency::factory()->create(['code' => 'GBP', 'symbol' => '£', 'decimals' => 2]);
    $this->action = new GetBalanceSummaryAction(new GetTallyDebtsAction());
});

/**
 * @param  array<int, float>  $shares  user id => percentage
 */
function summaryTally(User $owner, array $shares): Tally
{
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    foreach ($shares as $userId => $percentage) {
        $tally->users()->attach($userId, ['default_share_percentage' => $percentage]);
    }

    return $tally;
}

function summaryTx(Tally $tally, User $party, Currency $currency, float $amount, string $date = '2026-10-01', TransactionType $type = TransactionType::EXPENSE): void
{
    Transaction::factory()
        ->forTally($tally)
        ->forParty($party)
        ->withCurrency($currency)
        ->withCategory(Category::query()->first() ?? Category::factory()->create())
        ->withType($type)
        ->create(['amount' => $amount, 'transaction_date' => $date]);
}

/**
 * @return array{code: string, symbol: string, decimals: int}
 */
function summaryCurrency(Currency $currency): array
{
    return ['code' => $currency->code, 'symbol' => $currency->symbol, 'decimals' => $currency->decimals];
}

// ─── my_balance ───────────────────────────────────────────────────────────────

test('a balance owed to the user names the one counterparty', function (): void {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    summaryTx($tally, $owner, $this->eur, 100.00);

    $summary = $this->action->handle($owner->id, collect([$tally->tally_id]));

    expect($summary['my_balances'])->toBe([
        $tally->uuid => [[
            'currency' => summaryCurrency($this->eur),
            'amount' => 5000,
            'direction' => 'owed_to_me',
            'counterparty_name' => $member->name,
            'counterparties_count' => 1,
        ]],
    ]);
});

test('a balance the user owes is negative and names the creditor', function (): void {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    summaryTx($tally, $owner, $this->eur, 100.00);

    $summary = $this->action->handle($member->id, collect([$tally->tally_id]));

    expect($summary['my_balances'][$tally->uuid])->toBe([[
        'currency' => summaryCurrency($this->eur),
        'amount' => -5000,
        'direction' => 'i_owe',
        'counterparty_name' => $owner->name,
        'counterparties_count' => 1,
    ]])
        ->and($summary['owed_to_me'])->toBe([]);
});

test('a settled balance has no amount and no counterparty', function (): void {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    summaryTx($tally, $owner, $this->eur, 50.00);
    summaryTx($tally, $member, $this->eur, 50.00);

    $summary = $this->action->handle($owner->id, collect([$tally->tally_id]));

    expect($summary['my_balances'][$tally->uuid])->toBe([[
        'currency' => summaryCurrency($this->eur),
        'amount' => 0,
        'direction' => 'settled',
        'counterparty_name' => null,
        'counterparties_count' => 0,
    ]]);
});

test('two people owing the user leave the name out and count them', function (): void {
    [$owner, $second, $third] = User::factory()->count(3)->create();
    $tally = summaryTally($owner, [$owner->id => 33.33, $second->id => 33.33, $third->id => 33.34]);
    summaryTx($tally, $owner, $this->eur, 100.00);

    $summary = $this->action->handle($owner->id, collect([$tally->tally_id]));

    expect($summary['my_balances'][$tally->uuid][0])->toMatchArray([
        'amount' => 6667,
        'direction' => 'owed_to_me',
        'counterparty_name' => null,
        'counterparties_count' => 2,
    ]);
});

test('a EUR + USD household keeps one balance per currency with opposite directions', function (): void {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = summaryTally($owner, [$owner->id => 60.0, $member->id => 40.0]);
    summaryTx($tally, $owner, $this->eur, 70.00);
    summaryTx($tally, $owner, $this->eur, 30.00);
    summaryTx($tally, $member, $this->usd, 50.00);

    $summary = $this->action->handle($owner->id, collect([$tally->tally_id]));

    expect($summary['my_balances'][$tally->uuid])->toBe([
        ['currency' => summaryCurrency($this->eur), 'amount' => 4000, 'direction' => 'owed_to_me', 'counterparty_name' => $member->name, 'counterparties_count' => 1],
        ['currency' => summaryCurrency($this->usd), 'amount' => -3000, 'direction' => 'i_owe', 'counterparty_name' => $member->name, 'counterparties_count' => 1],
    ]);
});

test('after a member leaves, the remaining member owes them by name', function (): void {
    [$owner, $leaver] = User::factory()->count(2)->create();
    $tally = summaryTally($owner, [$owner->id => 50.0, $leaver->id => 50.0]);
    summaryTx($tally, $leaver, $this->eur, 100.00);
    (new DeleteTallyShareAction())->handle($tally, $leaver);

    $summary = $this->action->handle($owner->id, collect([$tally->tally_id]));

    expect($summary['my_balances'][$tally->uuid])->toBe([[
        'currency' => summaryCurrency($this->eur),
        'amount' => -10000,
        'direction' => 'i_owe',
        'counterparty_name' => $leaver->name,
        'counterparties_count' => 1,
    ]]);
});

test('only the given tallies are summarised and a tally without transactions has no balances', function (): void {
    [$owner, $member] = User::factory()->count(2)->create();
    $empty = summaryTally($owner, [$owner->id => 100.0]);
    $other = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    summaryTx($other, $owner, $this->eur, 10.00);

    $summary = $this->action->handle($owner->id, collect([$empty->tally_id]));

    expect($summary)->toBe(['my_balances' => [$empty->uuid => []], 'owed_to_me' => []]);
});

// ─── settle_suggestion ────────────────────────────────────────────────────────

test('the suggestion is the largest single debt in the primary currency, a larger debt in another currency is ignored', function (): void {
    [$owner, $member, $friend] = User::factory()->count(3)->create();
    $eurTally = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    $usdTally = summaryTally($owner, [$owner->id => 50.0, $friend->id => 50.0]);
    summaryTx($eurTally, $owner, $this->eur, 60.00, '2026-09-01');
    summaryTx($usdTally, $owner, $this->usd, 1000.00, '2026-10-10');

    $summary = $this->action->handle($owner->id, collect([$eurTally->tally_id, $usdTally->tally_id]));

    expect($this->action->settleSuggestion($summary['owed_to_me'], $this->eur->currency_id))->toBe([
        'tally_uuid' => $eurTally->uuid,
        'counterparty_name' => $member->name,
        'amount' => 3000,
        'currency' => summaryCurrency($this->eur),
    ]);
});

test('the suggestion compares single debts, not a tally total', function (): void {
    [$owner, $second, $third, $friend] = User::factory()->count(4)->create();
    $group = summaryTally($owner, [$owner->id => 30.0, $second->id => 30.0, $third->id => 40.0]);
    $pair = summaryTally($owner, [$owner->id => 50.0, $friend->id => 50.0]);
    summaryTx($group, $owner, $this->eur, 100.00, '2026-10-10');
    summaryTx($pair, $owner, $this->eur, 100.00, '2026-09-01');

    $summary = $this->action->handle($owner->id, collect([$group->tally_id, $pair->tally_id]));

    expect($this->action->settleSuggestion($summary['owed_to_me'], $this->eur->currency_id))->toBe([
        'tally_uuid' => $pair->uuid,
        'counterparty_name' => $friend->name,
        'amount' => 5000,
        'currency' => summaryCurrency($this->eur),
    ]);
});

test('equal debts in the primary currency go to the tally with the most recent activity', function (): void {
    [$owner, $member, $friend] = User::factory()->count(3)->create();
    $recent = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    $older = summaryTally($owner, [$owner->id => 50.0, $friend->id => 50.0]);
    summaryTx($recent, $owner, $this->eur, 40.00, '2026-10-12');
    summaryTx($older, $owner, $this->eur, 40.00, '2026-10-02');

    $summary = $this->action->handle($owner->id, collect([$older->tally_id, $recent->tally_id]));

    expect($this->action->settleSuggestion($summary['owed_to_me'], $this->eur->currency_id)['tally_uuid'])->toBe($recent->uuid);
});

test('with nothing owed in the primary currency the tally with the most recent activity wins over a larger debt', function (): void {
    [$owner, $member, $friend] = User::factory()->count(3)->create();
    $older = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    $recent = summaryTally($owner, [$owner->id => 50.0, $friend->id => 50.0]);
    summaryTx($older, $owner, $this->usd, 1000.00, '2026-08-01');
    summaryTx($recent, $owner, $this->gbp, 20.00, '2026-10-11');

    $summary = $this->action->handle($owner->id, collect([$recent->tally_id, $older->tally_id]));

    expect($this->action->settleSuggestion($summary['owed_to_me'], $this->eur->currency_id))->toBe([
        'tally_uuid' => $recent->uuid,
        'counterparty_name' => $friend->name,
        'amount' => 1000,
        'currency' => summaryCurrency($this->gbp),
    ]);
});

test('the fallback breaks an activity tie with the newer tally', function (): void {
    [$owner, $member, $friend] = User::factory()->count(3)->create();
    $first = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    $second = summaryTally($owner, [$owner->id => 50.0, $friend->id => 50.0]);
    summaryTx($first, $owner, $this->usd, 10.00, '2026-10-05');
    summaryTx($second, $owner, $this->usd, 8.00, '2026-10-05');

    $summary = $this->action->handle($owner->id, collect([$first->tally_id, $second->tally_id]));

    expect($this->action->settleSuggestion($summary['owed_to_me'], $this->eur->currency_id)['tally_uuid'])->toBe($second->uuid);
});

test('the fallback picks the most-used currency of that tally, then its largest debt', function (): void {
    [$owner, $second, $third] = User::factory()->count(3)->create();
    $tally = summaryTally($owner, [$owner->id => 0.0, $second->id => 30.0, $third->id => 70.0]);
    summaryTx($tally, $owner, $this->usd, 40.00, '2026-10-01');
    summaryTx($tally, $owner, $this->usd, 60.00, '2026-10-02');
    summaryTx($tally, $owner, $this->gbp, 500.00, '2026-10-03');

    $summary = $this->action->handle($owner->id, collect([$tally->tally_id]));

    expect($this->action->settleSuggestion($summary['owed_to_me'], $this->eur->currency_id))->toBe([
        'tally_uuid' => $tally->uuid,
        'counterparty_name' => $third->name,
        'amount' => 7000,
        'currency' => summaryCurrency($this->usd),
    ]);
});

test('no suggestion when everyone is settled', function (): void {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    summaryTx($tally, $owner, $this->eur, 25.00);
    summaryTx($tally, $member, $this->eur, 25.00);

    $summary = $this->action->handle($owner->id, collect([$tally->tally_id]));

    expect($summary['owed_to_me'])->toBe([])
        ->and($this->action->settleSuggestion($summary['owed_to_me'], $this->eur->currency_id))->toBeNull();
});

test('no suggestion when the user only owes', function (): void {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = summaryTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    summaryTx($tally, $owner, $this->eur, 80.00);

    $summary = $this->action->handle($member->id, collect([$tally->tally_id]));

    expect($this->action->settleSuggestion($summary['owed_to_me'], $this->eur->currency_id))->toBeNull();
});

// ─── settled members ──────────────────────────────────────────────────────────

test('settled members lists, per currency, the current members whose position is zero', function (): void {
    [$owner, $second, $third, $leaver] = User::factory()->count(4)->create();
    $tally = summaryTally($owner, [$owner->id => 50.0, $second->id => 50.0, $third->id => 0.0, $leaver->id => 0.0]);
    summaryTx($tally, $owner, $this->eur, 100.00);
    summaryTx($tally, $leaver, $this->eur, 10.00);
    summaryTx($tally, $leaver, $this->eur, 10.00, type: TransactionType::INCOME);
    summaryTx($tally, $second, $this->usd, 10.00);
    summaryTx($tally, $owner, $this->usd, 10.00);
    (new DeleteTallyShareAction())->handle($tally, $leaver);

    expect($this->action->settledMembers($tally)->all())->toBe([
        ['currency' => summaryCurrency($this->eur), 'members' => [['id' => $third->id, 'name' => $third->name]]],
        ['currency' => summaryCurrency($this->usd), 'members' => [
            ['id' => $owner->id, 'name' => $owner->name],
            ['id' => $second->id, 'name' => $second->name],
            ['id' => $third->id, 'name' => $third->name],
        ]],
    ]);
});
