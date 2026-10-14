<?php

declare(strict_types=1);

use App\Http\Controllers\TallyController;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

covers(TallyController::class);

it('lists income and expense per currency, most used first, never added together', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $usd = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);

    Transaction::factory()->forTally($tally)->withCurrency($usd)->create(['type' => 'expense', 'amount' => 7.00]);
    Transaction::factory()->forTally($tally)->withCurrency($eur)->create(['type' => 'income', 'amount' => 12.34]);
    Transaction::factory()->forTally($tally)->withCurrency($eur)->create(['type' => 'expense', 'amount' => 5.67]);
    Transaction::factory()->forTally($tally)->withCurrency($eur)->create(['type' => 'expense', 'amount' => 1.00]);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->component('tallies/Index')
            ->missing('tallies.0.income_total')
            ->missing('tallies.0.expense_total')
            ->has('tallies.0.totals', 2)
            ->where('tallies.0.totals.0', ['income' => 12.34, 'expense' => 6.67, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2])
            ->where('tallies.0.totals.1', ['income' => 0, 'expense' => 7, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2])
            ->where('tallies.0.transactions_count', 4)
        );
});

it('gives a tally with no transactions empty totals and the updated_at activity fallback', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.totals', [])
            ->where('tallies.0.transactions_count', 0)
            ->where('tallies.0.last_activity_at', $tally->updated_at->toIso8601String())
        );
});

it('reports the latest transaction_date, not created_at, as last activity', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);

    Transaction::factory()->forTally($tally)->create(['transaction_date' => '2026-03-10', 'created_at' => '2026-09-01 10:00:00']);
    Transaction::factory()->forTally($tally)->create(['transaction_date' => '2026-05-20', 'created_at' => '2026-01-01 10:00:00']);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.last_activity_at', fn (string $at): bool => str_starts_with($at, '2026-05-20') || str_starts_with($at, '2026-05-19'))
        );
});

it('counts members once, flags shared tallies and previews at most three with the split label', function (): void {
    $me = User::factory()->create();
    $solo = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Solo']);
    $shared = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Shared']);
    $shared->users()->attach($me->id, ['default_share_percentage' => 60]);
    $shared->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 40]);
    $crowd = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Crowd']);
    foreach (User::factory()->count(4)->create() as $member) {
        $crowd->users()->attach($member->id, ['default_share_percentage' => 20]);
    }
    $equal = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Equal']);
    $equal->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 50]);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertInertia(function (AssertableInertia $page): void {
            $rows = collect($page->toArray()['props']['tallies'])->keyBy('name');

            expect($rows['Solo'])->toMatchArray(['users_count' => 1, 'is_shared' => false, 'split_label' => null])
                ->and($rows['Solo']['members_preview'])->toHaveCount(1)
                ->and($rows['Shared'])->toMatchArray(['users_count' => 2, 'is_shared' => true, 'split_label' => '60 / 40'])
                ->and($rows['Shared']['members_preview'])->toHaveCount(2)
                ->and($rows['Shared']['members_preview'][0])->toHaveKeys(['initials', 'color'])
                ->and($rows['Crowd'])->toMatchArray(['users_count' => 5, 'is_shared' => true])
                ->and($rows['Crowd']['members_preview'])->toHaveCount(3)
                ->and($rows['Equal'])->toMatchArray(['users_count' => 2, 'split_label' => null]);
        });
});

it('does not expose member emails', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $other = User::factory()->create();
    $tally->users()->attach($other->id, ['default_share_percentage' => 50]);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertDontSee($other->email);
});

it('only lists tallies the user can access', function (): void {
    $me = User::factory()->create();
    Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Mine']);
    Tally::factory()->create(['name' => 'Foreign']);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->has('tallies', 1)->where('tallies.0.name', 'Mine'));
});

it('runs a constant number of queries however many tallies there are', function (): void {
    $me = User::factory()->create();
    $currency = Currency::factory()->create();
    $build = function (int $count) use ($me, $currency): void {
        foreach (range(1, $count) as $ignored) {
            $tally = Tally::factory()->create(['owner_id' => $me->id]);
            $tally->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 40]);
            Transaction::factory()->forTally($tally)->withCurrency($currency)->create();
        }
    };
    $countQueries = function () use ($me): int {
        Cache::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();
        actingAs($me)->get(route('tallies.index'))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $count;
    };

    $build(2);
    actingAs($me)->get(route('tallies.index'));
    $few = $countQueries();
    $build(6);
    $many = $countQueries();

    expect($many)->toBe($few);
});

it('gives each tally the user balance per currency, empty for a tally without transactions', function (): void {
    $me = User::factory()->create();
    $partner = User::factory()->create(['name' => 'Luca Bianchi']);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $shared = Tally::factory()->create(['owner_id' => $me->id]);
    $shared->users()->attach($me->id, ['default_share_percentage' => 50]);
    $shared->users()->attach($partner->id, ['default_share_percentage' => 50]);
    Transaction::factory()->forTally($shared)->forParty($me)->withCurrency($eur)->create(['type' => 'expense', 'amount' => 152.60, 'transaction_date' => now()->toDateString()]);
    $quiet = Tally::factory()->create(['owner_id' => $me->id, 'updated_at' => now()->subYear()]);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.uuid', $shared->uuid)
            ->where('tallies.0.my_balance', [[
                'currency' => ['code' => 'EUR', 'symbol' => '€', 'decimals' => 2],
                'amount' => 7630,
                'direction' => 'owed_to_me',
                'counterparty_name' => 'Luca Bianchi',
                'counterparties_count' => 1,
            ]])
            ->where('tallies.1.uuid', $quiet->uuid)
            ->where('tallies.1.my_balance', [])
        );
});

it('gives a tally the cached balances do not know yet an empty balance', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    Cache::put(App\Services\HomeCacheService::key((int) $me->id, 'tally_ids'), [], 60);
    Cache::put(App\Services\HomeCacheService::key((int) $me->id, 'balances'), ['my_balances' => [], 'owed_to_me' => []], 60);

    actingAs($me)
        ->get(route('tallies.index'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('tallies.0.uuid', $tally->uuid)
            ->where('tallies.0.my_balance', [])
        );
});
