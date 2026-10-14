<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

covers(HandleInertiaRequests::class);

beforeEach(function (): void {
    Cache::flush();
    $this->travelTo('2026-10-13 12:00:00');
});

it('does not share the sidebar tallies with a guest', function (): void {
    get('/')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->missing('sidebar_tallies'));
});

it('shares only the owned and member tallies of the user', function (): void {
    $me = User::factory()->create();
    $other = User::factory()->create();
    $owned = Tally::factory()->create(['owner_id' => $me->id, 'name' => 'Owned']);
    $joined = Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Joined']);
    $joined->users()->attach($me->id, ['default_share_percentage' => 50]);
    Tally::factory()->create(['owner_id' => $other->id, 'name' => 'Foreign']);

    actingAs($me)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->has('sidebar_tallies', 2)
            ->where('sidebar_tallies', fn ($rows): bool => collect($rows)->pluck('uuid')->sort()->values()->all() === collect([$owned->uuid, $joined->uuid])->sort()->values()->all())
            ->has('sidebar_tallies.0', fn (AssertableInertia $row): AssertableInertia => $row
                ->hasAll(['uuid', 'name', 'color', 'glyph', 'monthly_totals'])
                ->etc())
        );
});

it('exposes every currency of a mixed tally, most used first, never summed', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $usd = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);

    foreach ([10.0, 20.0] as $amount) {
        Transaction::factory()->forTally($tally)->forParty($me)->withCurrency($eur)
            ->create(['type' => 'expense', 'amount' => $amount, 'transaction_date' => '2026-10-05']);
    }
    Transaction::factory()->forTally($tally)->forParty($me)->withCurrency($usd)
        ->create(['type' => 'expense', 'amount' => 7.5, 'transaction_date' => '2026-10-06']);

    actingAs($me)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('sidebar_tallies.0.monthly_totals', [
                ['amount' => 30, 'currency_symbol' => '€', 'currency_code' => 'EUR', 'decimals' => 2],
                ['amount' => 7.5, 'currency_symbol' => '$', 'currency_code' => 'USD', 'decimals' => 2],
            ])
        );
});

it('filters the monthly total on transaction_date, not created_at', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);

    Transaction::factory()->forTally($tally)->forParty($me)->withCurrency($eur)
        ->create(['type' => 'expense', 'amount' => 11.0, 'transaction_date' => '2026-09-20', 'created_at' => '2026-10-10 09:00:00']);
    Transaction::factory()->forTally($tally)->forParty($me)->withCurrency($eur)
        ->create(['type' => 'expense', 'amount' => 4.0, 'transaction_date' => '2026-10-02', 'created_at' => '2026-08-01 09:00:00']);

    actingAs($me)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
            ->where('sidebar_tallies.0.monthly_totals.0.amount', 4)
        );
});

it('gives a tally without transactions an empty monthly_totals', function (): void {
    $me = User::factory()->create();
    Tally::factory()->create(['owner_id' => $me->id]);

    actingAs($me)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('sidebar_tallies.0.monthly_totals', []));
});

it('shares the prop once and issues a new once key when a tally or transaction changes', function (): void {
    $me = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);

    $onceKey = function () use ($me): string {
        $page = actingAs($me)->get('/home')->viewData('page');

        return array_key_first(array_filter($page['onceProps'], fn (array $meta): bool => $meta['prop'] === 'sidebar_tallies'));
    };

    $initialKey = $onceKey();
    $version = (string) actingAs($me)->get('/home')->viewData('page')['version'];
    expect($onceKey())->toBe($initialKey);

    Transaction::factory()->forTally($tally)->forParty($me)->withCurrency($eur)
        ->create(['type' => 'expense', 'amount' => 3.0, 'transaction_date' => '2026-10-05']);
    $afterTransactionKey = $onceKey();
    expect($afterTransactionKey)->not->toBe($initialKey);

    $tally->update(['name' => 'Renamed']);
    expect($onceKey())->not->toBe($afterTransactionKey);

    $currentKey = $onceKey();
    $inertiaGet = fn (string $exceptKey) => actingAs($me)
        ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $version, 'X-Inertia-Except-Once-Props' => $exceptKey])
        ->get('/home');

    $inertiaGet($currentKey)->assertJsonMissingPath('props.sidebar_tallies');
    $inertiaGet($initialKey)->assertJsonPath('props.sidebar_tallies.0.name', 'Renamed');
});

it('does not share the primary currency with a guest', function (): void {
    get('/')->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->missing('primary_currency'));
});

it('shares the default currency as the primary currency, without its id', function (): void {
    $currency = Currency::factory()->create(['code' => 'JPY', 'symbol' => '¥', 'decimals' => 0]);
    $me = User::factory()->create(['default_currency_id' => $currency->currency_id]);
    Tally::factory()->create(['owner_id' => $me->id]);

    actingAs($me)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('primary_currency', ['symbol' => '¥', 'code' => 'JPY', 'decimals' => 0]));
});

it('shares the most used currency as the primary currency when there is no default', function (): void {
    $me = User::factory()->create(['default_currency_id' => null]);
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $usd = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);

    foreach ([$usd, $usd, $eur] as $currency) {
        Transaction::factory()->forTally($tally)->forParty($me)->withCurrency($currency)
            ->create(['type' => 'expense', 'amount' => 5.0, 'transaction_date' => '2026-10-05']);
    }

    actingAs($me)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('primary_currency', ['symbol' => '$', 'code' => 'USD', 'decimals' => 2]));
});

it('keys the primary currency once prop to the sidebar version', function (): void {
    $me = User::factory()->create(['default_currency_id' => null]);
    $tally = Tally::factory()->create(['owner_id' => $me->id]);
    $eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $usd = Currency::factory()->create(['code' => 'USD', 'symbol' => '$', 'decimals' => 2]);

    $onceKey = function () use ($me): string {
        $page = actingAs($me)->get('/home')->viewData('page');

        return array_key_first(array_filter($page['onceProps'], fn (array $meta): bool => $meta['prop'] === 'primary_currency'));
    };

    Transaction::factory()->forTally($tally)->forParty($me)->withCurrency($eur)
        ->create(['type' => 'expense', 'amount' => 3.0, 'transaction_date' => '2026-10-05']);
    $version = (string) actingAs($me)->get('/home')->viewData('page')['version'];
    $initialKey = $onceKey();
    expect($initialKey)->toStartWith('primary_currency:')
        ->and($onceKey())->toBe($initialKey);

    foreach (range(1, 2) as $ignored) {
        Transaction::factory()->forTally($tally)->forParty($me)->withCurrency($usd)
            ->create(['type' => 'expense', 'amount' => 3.0, 'transaction_date' => '2026-10-06']);
    }
    $currentKey = $onceKey();
    expect($currentKey)->not->toBe($initialKey);

    $inertiaGet = fn (string $exceptKey) => actingAs($me)
        ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $version, 'X-Inertia-Except-Once-Props' => $exceptKey])
        ->get('/home');

    $inertiaGet($currentKey)->assertJsonMissingPath('props.primary_currency');
    $inertiaGet($initialKey)->assertJsonPath('props.primary_currency.code', 'USD');
});
