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
