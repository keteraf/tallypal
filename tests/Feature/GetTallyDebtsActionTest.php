<?php

declare(strict_types=1);

use App\Actions\TallyShare\DeleteTallyShareAction;
use App\Actions\Transaction\GetTallyDebtsAction;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

covers(GetTallyDebtsAction::class);

beforeEach(function () {
    $this->currency = Currency::query()->create([
        'name' => 'Euro',
        'code' => 'EUR',
        'symbol' => '€',
        'decimals' => 2,
        'description' => 'European Euro',
    ]);
    $this->usd = Currency::query()->create([
        'name' => 'US Dollar',
        'code' => 'USD',
        'symbol' => '$',
        'decimals' => 2,
        'description' => 'US Dollar',
    ]);
    $this->category = Category::query()->create(['name' => 'Food']);
});

/**
 * A tally owned by `$owner` with one `tally_user` row per entry of `$shares` (user id => percentage).
 *
 * @param  array<int, float>  $shares
 */
function ledgerTally(User $owner, array $shares): Tally
{
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    foreach ($shares as $userId => $percentage) {
        $tally->users()->attach($userId, ['default_share_percentage' => $percentage]);
    }

    return $tally;
}

function ledgerTx(Tally $tally, User $party, Currency $currency, float $amount, TransactionType $type = TransactionType::EXPENSE): void
{
    Transaction::factory()
        ->forTally($tally)
        ->forParty($party)
        ->withCurrency($currency)
        ->withCategory(Category::query()->firstOrFail())
        ->withType($type)
        ->withAmount($amount)
        ->create();
}

/**
 * Each participant's position (integer minor units) in one currency, keyed by user id.
 *
 * @return array<int, int>
 */
function ledgerPositions(Collection $ledger, Currency $currency): array
{
    $entry = $ledger->firstWhere('currency_id', $currency->currency_id);

    return collect($entry['positions'])->mapWithKeys(fn (array $position): array => [$position['user']['id'] => $position['amount']])->all();
}

function expectLedgerSumsToZero(Collection $ledger): void
{
    expect($ledger)->not->toBeEmpty();

    foreach ($ledger as $entry) {
        expect(array_sum(array_column($entry['positions'], 'amount')))->toBe(0);
    }
}

test('it calculates debts for 2 users with equal share', function () {
    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 50.00]);
    $tally->users()->attach($user2, ['default_share_percentage' => 50.00]);

    // User 1 paid 20
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(20.00)
        ->create();

    $action = new GetTallyDebtsAction();
    $debts = $action->handle($tally);

    expect($debts)->toHaveCount(1);
    $settlements = $debts[0]['settlements'];
    expect($settlements)->toHaveCount(1);
    expect($settlements[0]['debtor']['id'])->toBe($user2->id);
    expect($settlements[0]['creditor']['id'])->toBe($user1->id);
    expect($settlements[0]['amount'])->toBe(10.0);
});

test('it calculates debts for 3 users with equal share', function () {
    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);
    $user3 = User::factory()->create(['name' => 'User 3']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 33.33]);
    $tally->users()->attach($user2, ['default_share_percentage' => 33.33]);
    $tally->users()->attach($user3, ['default_share_percentage' => 33.34]);

    // User 1 paid 30
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(30.00)
        ->create();

    $action = new GetTallyDebtsAction();
    $debts = $action->handle($tally);

    expect($debts)->toHaveCount(1);
    $settlements = $debts[0]['settlements'];
    expect($settlements)->toHaveCount(2);

    $user2Debt = collect($settlements)->where('debtor.id', $user2->id)->first();
    $user3Debt = collect($settlements)->where('debtor.id', $user3->id)->first();

    expect($user2Debt['amount'])->toBe(10.0);
    expect($user3Debt['amount'])->toBe(10.0);
});

test('it considers incomes in debt calculation', function () {
    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 50.00]);
    $tally->users()->attach($user2, ['default_share_percentage' => 50.00]);

    // User 1 paid 20 (expense)
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(20.00)
        ->create();

    // User 1 received 4 (income)
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::INCOME)
        ->withAmount(4.00)
        ->create();

    // Net spent = 16. User 1 net paid = 16. Each share = 8.
    // User 2 owes User 1 8.

    $action = new GetTallyDebtsAction();
    $debts = $action->handle($tally);

    expect($debts)->toHaveCount(1);
    $settlements = $debts[0]['settlements'];
    expect($settlements)->toHaveCount(1);
    expect($settlements[0]['debtor']['id'])->toBe($user2->id);
    expect($settlements[0]['creditor']['id'])->toBe($user1->id);
    expect($settlements[0]['amount'])->toBe(8.0);
});

test('positions in each currency sum to exactly zero', function (array $shares, array $payments) {
    $members = collect($shares)->map(fn (): User => User::factory()->create());
    $tally = ledgerTally($members[0], $members->mapWithKeys(fn (User $member, int $index): array => [$member->id => $shares[$index]])->all());

    foreach ($payments as [$memberIndex, $amount, $type]) {
        ledgerTx($tally, $members[$memberIndex], $this->currency, $amount, $type);
    }

    expectLedgerSumsToZero((new GetTallyDebtsAction())->ledger($tally));
})->with([
    'uneven three-way split' => [[33.33, 33.33, 33.34], [[0, 100.00, TransactionType::EXPENSE], [2, 0.07, TransactionType::EXPENSE]]],
    'three equal 33.33 shares' => [[33.33, 33.33, 33.33], [[1, 100.00, TransactionType::EXPENSE], [2, 12.34, TransactionType::EXPENSE]]],
    'incomes exceeding expenses' => [[60.0, 40.0], [[0, 10.00, TransactionType::INCOME], [1, 3.33, TransactionType::EXPENSE]]],
    'a single cent' => [[50.0, 50.0], [[1, 0.01, TransactionType::EXPENSE]]],
    'a single cent of income' => [[50.0, 50.0], [[0, 0.01, TransactionType::INCOME]]],
    'expenses and incomes cancelling out' => [[50.0, 50.0], [[0, 10.00, TransactionType::EXPENSE], [1, 10.00, TransactionType::INCOME]]],
    'odd shares' => [[12.5, 45.25, 42.25], [[0, 99.99, TransactionType::EXPENSE], [1, 0.05, TransactionType::INCOME], [2, 1234.57, TransactionType::EXPENSE]]],
]);

test('100.00 split three ways at 33.33 / 33.33 / 33.34', function () {
    [$owner, $second, $third] = User::factory()->count(3)->create();
    $tally = ledgerTally($owner, [$owner->id => 33.33, $second->id => 33.33, $third->id => 33.34]);
    ledgerTx($tally, $owner, $this->currency, 100.00);

    $action = new GetTallyDebtsAction();
    $ledger = $action->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => 6667, $second->id => -3333, $third->id => -3334])
        ->and($ledger[0]['settlements'])->toBe([
            ['debtor' => ['id' => $third->id, 'name' => $third->name], 'creditor' => ['id' => $owner->id, 'name' => $owner->name], 'amount' => 3334],
            ['debtor' => ['id' => $second->id, 'name' => $second->name], 'creditor' => ['id' => $owner->id, 'name' => $owner->name], 'amount' => 3333],
        ])
        ->and($action->handle($tally)[0]['settlements'][0]['amount'])->toBe(33.34)
        ->and($action->handle($tally)[0]['settlements'][1]['amount'])->toBe(33.33);
});

test('three equal 33.33 shares give the leftover cent to the owner, first in member order', function () {
    $second = User::factory()->create();
    $third = User::factory()->create();
    $owner = User::factory()->create();
    $tally = ledgerTally($owner, [$second->id => 33.33, $third->id => 33.33, $owner->id => 33.33]);
    ledgerTx($tally, $second, $this->currency, 100.00);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => -3334, $second->id => 6667, $third->id => -3333]);
});

test('the leftover cent goes to the largest fractional remainder', function () {
    [$owner, $second, $third] = User::factory()->count(3)->create();
    $tally = ledgerTally($owner, [$owner->id => 33.33, $second->id => 33.33, $third->id => 33.34]);
    ledgerTx($tally, $owner, $this->currency, 100.01);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => 6668, $second->id => -3333, $third->id => -3335]);
});

test('0.07 split 60 / 40 gives the leftover cent to the 40% member, whose remainder is larger', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 60.0, $member->id => 40.0]);
    ledgerTx($tally, $member, $this->currency, 0.07);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => -4, $member->id => 4]);
});

test('shares whose hundredths are not exact in floating point are weighted exactly', function () {
    [$owner, $second, $third] = User::factory()->count(3)->create();
    $tally = ledgerTally($owner, [$owner->id => 18.4, $second->id => 16.67, $third->id => 64.93]);
    ledgerTx($tally, $third, $this->currency, 1000.00);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => -18400, $second->id => -16670, $third->id => 35070]);
});

test('a member at zero takes no part in the settlements', function () {
    [$owner, $partner, $guest] = User::factory()->count(3)->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $partner->id => 50.0, $guest->id => 0.0]);
    ledgerTx($tally, $owner, $this->currency, 152.60);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => 7630, $partner->id => -7630, $guest->id => 0])
        ->and($ledger[0]['settlements'])->toBe([
            ['debtor' => ['id' => $partner->id, 'name' => $partner->name], 'creditor' => ['id' => $owner->id, 'name' => $owner->name], 'amount' => 7630],
        ]);
});

test('expenses and incomes cancelling out leave each payer square with the others', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    ledgerTx($tally, $owner, $this->currency, 10.00);
    ledgerTx($tally, $member, $this->currency, 10.00, TransactionType::INCOME);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => 1000, $member->id => -1000]);
});

test('a single cent split 50 / 50 goes to the owner, whatever the user ids', function () {
    $member = User::factory()->create();
    $owner = User::factory()->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    ledgerTx($tally, $member, $this->currency, 0.01);

    $action = new GetTallyDebtsAction();

    expect(ledgerPositions($action->ledger($tally), $this->currency))->toBe([$owner->id => -1, $member->id => 1])
        ->and($action->handle($tally)[0]['settlements'])->toBe([
            ['debtor' => ['id' => $owner->id, 'name' => $owner->name], 'creditor' => ['id' => $member->id, 'name' => $member->name], 'amount' => 0.01],
        ]);
});

test('incomes exceeding expenses split the net income the same way', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    ledgerTx($tally, $owner, $this->currency, 0.03, TransactionType::INCOME);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => -1, $member->id => 1]);
});

test('EUR and USD keep opposite directions per currency and are never netted', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    ledgerTx($tally, $owner, $this->usd, 60.00);
    ledgerTx($tally, $member, $this->currency, 100.00);

    $action = new GetTallyDebtsAction();
    $ledger = $action->ledger($tally);
    $debts = $action->handle($tally);

    expectLedgerSumsToZero($ledger);
    expect($ledger)->toHaveCount(2)
        ->and($ledger[0]['currency'])->toBe(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2])
        ->and($ledger[1]['currency'])->toBe(['code' => 'USD', 'symbol' => '$', 'decimals' => 2])
        ->and($ledger[0]['positions'])->toHaveCount(2)
        ->and(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => -5000, $member->id => 5000])
        ->and(ledgerPositions($ledger, $this->usd))->toBe([$owner->id => 3000, $member->id => -3000])
        ->and($debts)->toHaveCount(2)
        ->and($debts[0]['currency']['code'])->toBe('EUR')
        ->and($debts[0]['settlements'][0]['creditor']['id'])->toBe($member->id)
        ->and($debts[0]['settlements'][0]['amount'])->toBe(50.0)
        ->and($debts[1]['currency']['code'])->toBe('USD')
        ->and($debts[1]['settlements'][0]['creditor']['id'])->toBe($owner->id)
        ->and($debts[1]['settlements'][0]['amount'])->toBe(30.0);
});

test('currencies are listed most-used first', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    ledgerTx($tally, $owner, $this->currency, 10.00);
    ledgerTx($tally, $owner, $this->usd, 10.00);
    ledgerTx($tally, $member, $this->usd, 4.00);

    $action = new GetTallyDebtsAction();

    expect($action->ledger($tally)->pluck('currency.code')->all())->toBe(['USD', 'EUR'])
        ->and($action->ledger($tally)->pluck('currency_id')->all())->toBe([$this->usd->currency_id, $this->currency->currency_id])
        ->and($action->handle($tally)->pluck('currency.code')->all())->toBe(['USD', 'EUR']);
});

test('a member who left keeps their open balance at a 0% share', function () {
    [$owner, $leaver] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $leaver->id => 50.0]);
    ledgerTx($tally, $leaver, $this->currency, 100.00);

    (new DeleteTallyShareAction())->handle($tally, $leaver);

    $action = new GetTallyDebtsAction();
    $ledger = $action->ledger($tally);

    expectLedgerSumsToZero($ledger);
    expect($ledger[0]['positions'])->toBe([
        ['user' => ['id' => $owner->id, 'name' => $owner->name], 'is_member' => true, 'amount' => -10000],
        ['user' => ['id' => $leaver->id, 'name' => $leaver->name], 'is_member' => false, 'amount' => 10000],
    ])
        ->and($action->handle($tally)[0]['settlements'])->toBe([
            ['debtor' => ['id' => $owner->id, 'name' => $owner->name], 'creditor' => ['id' => $leaver->id, 'name' => $leaver->name], 'amount' => 100.0],
        ]);
});

test('an owner without a tally_user row is counted once with the remainder share', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$member->id => 40.0]);
    ledgerTx($tally, $owner, $this->currency, 100.00);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect($ledger[0]['positions'])->toHaveCount(2)
        ->and(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => 4000, $member->id => -4000]);
});

test('everyone settled gives zero positions and no debts', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    ledgerTx($tally, $owner, $this->currency, 50.00);
    ledgerTx($tally, $member, $this->currency, 50.00);

    $action = new GetTallyDebtsAction();
    $ledger = $action->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => 0, $member->id => 0])
        ->and($ledger[0]['settlements'])->toBe([])
        ->and($action->handle($tally))->toBeEmpty();
});

test('shares that are all zero fall back to an equal split', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 0.0, $member->id => 0.0]);
    ledgerTx($tally, $owner, $this->currency, 10.00);

    $ledger = (new GetTallyDebtsAction())->ledger($tally);

    expect(ledgerPositions($ledger, $this->currency))->toBe([$owner->id => 500, $member->id => -500]);
});

test('a tally without transactions has an empty ledger and no debts', function () {
    $owner = User::factory()->create();
    $tally = ledgerTally($owner, [$owner->id => 100.0]);

    $action = new GetTallyDebtsAction();

    expect($action->ledger($tally))->toBeEmpty()
        ->and($action->handle($tally))->toBeEmpty();
});

test('settlement amounts are converted with each currency own decimals', function () {
    $yen = Currency::query()->create(['name' => 'Yen', 'code' => 'JPY', 'symbol' => '¥', 'decimals' => 0, 'description' => 'Japanese Yen']);
    [$owner, $member] = User::factory()->count(2)->create();
    $tally = ledgerTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    ledgerTx($tally, $owner, $yen, 1001);

    $action = new GetTallyDebtsAction();

    expect(ledgerPositions($action->ledger($tally), $yen))->toBe([$owner->id => 500, $member->id => -500])
        ->and($action->handle($tally)[0]['currency'])->toBe(['code' => 'JPY', 'symbol' => '¥', 'decimals' => 0])
        ->and($action->handle($tally)[0]['settlements'][0]['amount'])->toBe(500.0);
});

test('ledgers nets several tallies at once, keyed by tally id', function () {
    [$owner, $member] = User::factory()->count(2)->create();
    $first = ledgerTally($owner, [$owner->id => 50.0, $member->id => 50.0]);
    $second = ledgerTally($owner, [$owner->id => 100.0]);
    $empty = ledgerTally($owner, [$owner->id => 100.0]);
    ledgerTx($first, $owner, $this->currency, 10.00);
    ledgerTx($second, $owner, $this->usd, 7.00);

    $ledgers = (new GetTallyDebtsAction())->ledgers(collect([$first, $second, $empty]));

    expect($ledgers->keys()->all())->toBe([$first->tally_id, $second->tally_id, $empty->tally_id])
        ->and(ledgerPositions($ledgers[$first->tally_id], $this->currency))->toBe([$owner->id => 500, $member->id => -500])
        ->and(ledgerPositions($ledgers[$second->tally_id], $this->usd))->toBe([$owner->id => 0])
        ->and($ledgers[$empty->tally_id])->toBeEmpty();
});

test('a database error is reported and becomes a plain 500', function () {
    Exceptions::fake();
    $owner = User::factory()->create();
    $tally = ledgerTally($owner, [$owner->id => 100.0]);
    $failure = databaseFailure();
    DB::partialMock()->shouldReceive('table')->with(Transaction::TABLE_NAME)->once()->andThrow($failure);

    expect(fn () => (new GetTallyDebtsAction())->handle($tally))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});
