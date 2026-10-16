<?php

declare(strict_types=1);

use App\Actions\TallyShare\DeleteTallyShareAction;
use App\Console\Commands\CompareBalancesCommand;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

covers(CompareBalancesCommand::class);

/**
 * @param  array<int, float>  $shares  user id => percentage
 */
function compareTally(User $owner, array $shares, User $payer, Currency $currency, float $amount): Tally
{
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    foreach ($shares as $userId => $percentage) {
        $tally->users()->attach($userId, ['default_share_percentage' => $percentage]);
    }

    Transaction::factory()->forTally($tally)->forParty($payer)->withCurrency($currency)->create(['type' => 'expense', 'amount' => $amount]);

    return $tally;
}

it('reports no change when the old and the new netting agree', function (): void {
    [$owner, $member] = User::factory()->count(2)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = compareTally($owner, [$owner->id => 50.0, $member->id => 50.0], $owner, $euro, 30.00);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies scanned: 1')
        ->expectsOutput('Tallies whose balances change: 0')
        ->expectsOutput('Currency blocks whose settlements change: 0')
        ->expectsTable(['Currency', 'Largest difference', 'Tally'], [['EUR', '€ 0.00', '—']])
        ->assertExitCode(0);
});

it('reports each tally whose balances change with its own per-currency difference', function (): void {
    [$owner, $leaver, $member] = User::factory()->count(3)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $dollar = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);

    $departed = compareTally($owner, [$owner->id => 50.0, $leaver->id => 50.0], $leaver, $euro, 100.00);
    (new DeleteTallyShareAction())->handle($departed, $leaver);

    $ownerWithoutRow = compareTally($owner, [$member->id => 40.0], $owner, $dollar, 100.00);

    compareTally($owner, [$owner->id => 50.0, $member->id => 50.0], $owner, $euro, 30.00);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies scanned: 3')
        ->expectsOutput('Tallies whose balances change: 2')
        ->expectsOutput('Currency blocks whose settlements change: 2')
        ->expectsTable(['Currency', 'Largest difference', 'Tally'], [
            ['EUR', '€ 100.00', $departed->uuid],
            ['USD', '$ 40.00', $ownerWithoutRow->uuid],
        ])
        ->assertExitCode(0);
});

it('reports the open balance a member who left now keeps, without writing anything', function (): void {
    [$owner, $leaver] = User::factory()->count(2)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = compareTally($owner, [$owner->id => 50.0, $leaver->id => 50.0], $leaver, $euro, 100.00);
    (new DeleteTallyShareAction())->handle($tally, $leaver);

    $writes = [];
    DB::listen(function (QueryExecuted $query) use (&$writes): void {
        if (preg_match('/^\s*(insert|update|delete|replace|alter|create|drop)/i', $query->sql) === 1) {
            $writes[] = $query->sql;
        }
    });

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies scanned: 1')
        ->expectsOutput('Tallies whose balances change: 1')
        ->expectsOutput('Currency blocks whose settlements change: 1')
        ->expectsTable(['Currency', 'Largest difference', 'Tally'], [['EUR', '€ 100.00', $tally->uuid]])
        ->assertExitCode(0);

    expect($writes)->toBe([]);
});

/**
 * @param  array<int, array{0: User, 1: string, 2: float}>  $rows  payer, type, amount
 */
function compareAddTransactions(Tally $tally, Currency $currency, array $rows): void
{
    foreach ($rows as [$payer, $type, $amount]) {
        Transaction::factory()->forTally($tally)->forParty($payer)->withCurrency($currency)->create(['type' => $type, 'amount' => $amount]);
    }
}

it('agrees with the legacy netting on a four-member tally with incomes in two currencies', function (): void {
    [$anna, $bruno, $carla, $dino] = User::factory()->count(4)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $yen = Currency::factory()->create(['code' => 'JPY', 'symbol' => '¥', 'decimals' => 0]);
    $tally = Tally::factory()->create(['owner_id' => $anna->id]);

    foreach ([[$anna, 40.0], [$bruno, 30.0], [$carla, 20.0], [$dino, 10.0]] as [$user, $share]) {
        $tally->users()->attach($user->id, ['default_share_percentage' => $share]);
    }

    compareAddTransactions($tally, $euro, [
        [$anna, 'expense', 100.00],
        [$bruno, 'expense', 57.50],
        [$carla, 'income', 20.00],
        [$dino, 'expense', 3.00],
        [$carla, 'expense', 12.50],
    ]);
    compareAddTransactions($tally, $yen, [
        [$anna, 'expense', 1000.0],
        [$bruno, 'expense', 2000.0],
        [$dino, 'income', 500.0],
    ]);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies scanned: 1')
        ->expectsOutput('Tallies whose balances change: 0')
        ->expectsOutput('Currency blocks whose settlements change: 0')
        ->expectsTable(['Currency', 'Largest difference', 'Tally'], [['EUR', '€ 0.00', '—'], ['JPY', '¥ 0', '—']])
        ->assertExitCode(0);
});

it('agrees on amounts whose float form is just below the minor unit', function (): void {
    [$anna, $bruno] = User::factory()->count(2)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $anna->id]);
    $tally->users()->attach($anna->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($bruno->id, ['default_share_percentage' => 50]);
    compareAddTransactions($tally, $euro, [[$anna, 'expense', 0.58]]);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies whose balances change: 0')
        ->expectsOutput('Currency blocks whose settlements change: 0')
        ->assertExitCode(0);
});

it('formats a large difference without a thousands separator', function (): void {
    [$owner, $leaver] = User::factory()->count(2)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = compareTally($owner, [$owner->id => 50.0, $leaver->id => 50.0], $leaver, $euro, 2500.00);
    (new DeleteTallyShareAction())->handle($tally, $leaver);

    $this->artisan('tallypal:compare-balances')
        ->expectsTable(['Currency', 'Largest difference', 'Tally'], [['EUR', '€ 2500.00', $tally->uuid]])
        ->assertExitCode(0);
});

it('scans a tally without members and one without transactions', function (): void {
    $owner = User::factory()->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $memberless = Tally::factory()->create(['owner_id' => $owner->id]);
    compareAddTransactions($memberless, $euro, [[$owner, 'expense', 10.00]]);
    Tally::factory()->create(['owner_id' => $owner->id]);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies scanned: 2')
        ->assertExitCode(0);
});

it('agrees when several creditors and debtors must be paired largest first', function (): void {
    [$anna, $bruno, $carla, $dino, $elena] = User::factory()->count(5)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $anna->id]);

    foreach ([$anna, $bruno, $carla, $dino, $elena] as $user) {
        $tally->users()->attach($user->id, ['default_share_percentage' => 20]);
    }

    compareAddTransactions($tally, $euro, [
        [$anna, 'expense', 130.00],
        [$bruno, 'expense', 200.00],
        [$carla, 'expense', 10.00],
        [$dino, 'expense', 40.00],
    ]);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies whose balances change: 0')
        ->expectsOutput('Currency blocks whose settlements change: 0')
        ->assertExitCode(0);
});

it('agrees on amounts whose float form is just above the minor unit', function (): void {
    [$anna, $bruno] = User::factory()->count(2)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $anna->id]);
    $tally->users()->attach($anna->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($bruno->id, ['default_share_percentage' => 50]);
    compareAddTransactions($tally, $euro, [[$anna, 'expense', 0.14]]);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies whose balances change: 0')
        ->expectsOutput('Currency blocks whose settlements change: 0')
        ->assertExitCode(0);
});

it('sums the settlements a creditor receives from several debtors when a member left', function (): void {
    [$owner, $leaver, $member] = User::factory()->count(3)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = compareTally($owner, [$owner->id => 50.0, $leaver->id => 25.0, $member->id => 25.0], $leaver, $euro, 100.00);
    (new DeleteTallyShareAction())->handle($tally, $leaver);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies whose balances change: 1')
        ->expectsOutput('Currency blocks whose settlements change: 1')
        ->expectsTable(['Currency', 'Largest difference', 'Tally'], [['EUR', '€ 100.00', $tally->uuid]])
        ->assertExitCode(0);
});

it('sums what a debtor owes to several creditors when two members left', function (): void {
    [$owner, $first, $second] = User::factory()->count(3)->create();
    $euro = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $tally = compareTally($owner, [$owner->id => 50.0, $first->id => 25.0, $second->id => 25.0], $first, $euro, 60.00);
    compareAddTransactions($tally, $euro, [[$second, 'expense', 60.00]]);
    (new DeleteTallyShareAction())->handle($tally, $first);
    (new DeleteTallyShareAction())->handle($tally, $second);

    $this->artisan('tallypal:compare-balances')
        ->expectsOutput('Tallies whose balances change: 1')
        ->expectsTable(['Currency', 'Largest difference', 'Tally'], [['EUR', '€ 120.00', $tally->uuid]])
        ->assertExitCode(0);
});
