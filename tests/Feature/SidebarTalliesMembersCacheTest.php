<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Cache::flush();
    $this->travelTo('2026-10-13 12:00:00');
    $this->eur = Currency::factory()->create(['code' => 'EUR', 'symbol' => '€', 'decimals' => 2]);
    $this->a = User::factory()->create();
    $this->b = User::factory()->create();
    $this->household = Tally::factory()->create(['owner_id' => $this->a->id, 'name' => 'Household']);
    $this->household->users()->attach($this->b->id, ['default_share_percentage' => 40]);

    $this->sidebar = fn (User $user): array => actingAs($user)->get('/home')->viewData('page')['props']['sidebar_tallies'];
});

it('refreshes every member sidebar when a member adds, edits or deletes a transaction', function (): void {
    expect(($this->sidebar)($this->b)[0]['monthly_totals'])->toBe([]);

    $transaction = Transaction::factory()->forTally($this->household)->forParty($this->a)->withCurrency($this->eur)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => '2026-10-05']);
    expect(($this->sidebar)($this->b)[0]['monthly_totals'][0]['amount'])->toBe(10.0);

    $transaction->update(['amount' => 25.0]);
    expect(($this->sidebar)($this->b)[0]['monthly_totals'][0]['amount'])->toBe(25.0);

    $transaction->delete();
    expect(($this->sidebar)($this->b)[0]['monthly_totals'])->toBe([]);
});

it('refreshes every member sidebar when the tally is edited', function (): void {
    expect(($this->sidebar)($this->b)[0]['name'])->toBe('Household');

    $this->household->update(['name' => 'Flat']);

    expect(($this->sidebar)($this->b)[0]['name'])->toBe('Flat');
});

it('shows the tally in the sidebar of a user who accepts an invite', function (): void {
    $joiner = User::factory()->create();
    expect(($this->sidebar)($joiner))->toBe([]);

    $this->household->users()->attach($joiner->id, ['default_share_percentage' => 20]);

    expect(collect(($this->sidebar)($joiner))->pluck('name')->all())->toBe(['Household']);
});

it('drops the tally from a removed member and keeps the others correct', function (): void {
    Transaction::factory()->forTally($this->household)->forParty($this->a)->withCurrency($this->eur)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => '2026-10-05']);
    expect(($this->sidebar)($this->b))->toHaveCount(1);
    expect(($this->sidebar)($this->a))->toHaveCount(1);

    $this->household->users()->detach($this->b->id);

    expect(($this->sidebar)($this->b))->toBe([]);
    expect(($this->sidebar)($this->a)[0]['monthly_totals'][0]['amount'])->toBe(10.0);
});

it('refreshes remaining members when another member leaves', function (): void {
    $c = User::factory()->create();
    $this->household->users()->attach($c->id, ['default_share_percentage' => 10]);
    Transaction::factory()->forTally($this->household)->forParty($this->a)->withCurrency($this->eur)
        ->create(['type' => 'expense', 'amount' => 10.0, 'transaction_date' => '2026-10-05']);
    expect(($this->sidebar)($c))->toHaveCount(1);

    $this->household->users()->detach($c->id);

    expect(($this->sidebar)($c))->toBe([]);
    expect(($this->sidebar)($this->b)[0]['monthly_totals'][0]['amount'])->toBe(10.0);
});
