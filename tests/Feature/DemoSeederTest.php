<?php

declare(strict_types=1);

use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\DemoSeeder;

it('seeds a tally with no transactions alongside the populated ones', function (): void {
    $this->seed(DemoSeeder::class);

    $demo = User::query()->where('email', DemoSeeder::DEMO_EMAIL)->firstOrFail();
    $empty = Tally::query()->where('owner_id', $demo->id)->where('name', DemoSeeder::EMPTY_TALLY_NAME)->firstOrFail();

    expect($empty->transactions()->count())->toBe(0)
        ->and(Tally::query()->where('owner_id', $demo->id)->where('name', '!=', DemoSeeder::EMPTY_TALLY_NAME)->count())->toBe(5);
});

it('can be re-run without duplicating the empty tally', function (): void {
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);

    expect(Tally::query()->where('name', DemoSeeder::EMPTY_TALLY_NAME)->count())->toBe(1);
});

it('seeds a tally that spends two currencies in the current month', function (): void {
    $this->seed(DemoSeeder::class);

    $cityBreak = Tally::query()->where('name', DemoSeeder::MIXED_CURRENCY_TALLY_NAME)->firstOrFail();

    $codes = Transaction::query()
        ->where('tally_id', $cityBreak->tally_id)
        ->where('transaction_date', '>=', now()->startOfMonth()->toDateString())
        ->where('transaction_date', '<=', now()->toDateString())
        ->with('currency')
        ->get()
        ->map(fn (Transaction $transaction): string => (string) $transaction->currency?->code)
        ->unique()
        ->sort()
        ->values()
        ->all();

    expect($codes)->toBe(['EUR', 'USD']);
});

it('seeds a three-member EUR tally under a fixed uuid', function (): void {
    $this->seed(DemoSeeder::class);

    $skiCabin = Tally::query()->where('uuid', DemoSeeder::THREE_MEMBER_UUID)->firstOrFail();

    $codes = Transaction::query()
        ->where('tally_id', $skiCabin->tally_id)
        ->with('currency')
        ->get()
        ->map(fn (Transaction $transaction): string => (string) $transaction->currency?->code)
        ->unique()
        ->values()
        ->all();

    expect($skiCabin->users()->count())->toBe(3)
        ->and($codes)->toBe(['EUR']);
});
