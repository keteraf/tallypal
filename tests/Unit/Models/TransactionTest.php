<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

covers(Transaction::class);

it('has correct table name', function () {
    $transaction = new Transaction();
    expect($transaction->getTable())->toBe('transactions');
});

it('has correct primary key', function () {
    $transaction = new Transaction();
    expect($transaction->getKeyName())->toBe('transaction_id');
});

it('belongs to a tally', function () {
    $transaction = Transaction::factory()->create();
    expect($transaction->tally())->toBeInstanceOf(BelongsTo::class)
        ->and($transaction->tally)->toBeInstanceOf(Tally::class);
});

it('belongs to a party', function () {
    $transaction = Transaction::factory()->create();
    expect($transaction->party())->toBeInstanceOf(BelongsTo::class)
        ->and($transaction->party)->toBeInstanceOf(User::class);
});

it('belongs to a currency', function () {
    $currency = Currency::query()->inRandomOrder()->first();
    $transaction = Transaction::factory()->withCurrency($currency)->create();
    expect($transaction->currency())->toBeInstanceOf(BelongsTo::class)
        ->and($transaction->currency)->toBeInstanceOf(Currency::class);
});

it('belongs to a category', function () {
    $category = Category::query()->inRandomOrder()->first();
    $transaction = Transaction::factory()->withCategory($category)->create();
    expect($transaction->category())->toBeInstanceOf(BelongsTo::class)
        ->and($transaction->category)->toBeInstanceOf(Category::class);
});
