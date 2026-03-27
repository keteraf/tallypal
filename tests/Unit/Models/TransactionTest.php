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
    expect(Transaction::TABLE_NAME)->toBe('transactions');
    expect(Transaction::KEYS_PREFIX)->toBe('transaction');
});

it('has correct primary key', function () {
    $transaction = new Transaction();
    expect($transaction->getKeyName())->toBe('transaction_id');
    expect(Transaction::PRIMARY_KEY)->toBe('transaction_id');
});

it('has expected fillable and casts', function () {
    $transaction = new Transaction();

    expect($transaction->getFillable())->toBe([
        'tally_id',
        'party_id',
        'currency_id',
        'category_id',
        'type',
        'title',
        'description',
        'amount',
        'transaction_date',
    ]);

    expect($transaction->getCasts())->toMatchArray([
        'amount' => 'float',
    ]);
    expect($transaction->getCasts()['type'])->toContain('TransactionType');
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

it('gets and sets amount correctly', function () {
    $currency = Currency::factory()->create(['decimals' => 2]);
    $transaction = Transaction::factory()->withCurrency($currency)->create(['amount' => 12.34]);

    // Internal value in DB should be 1234
    expect($transaction->getRawOriginal('amount'))->toEqual(1234);

    // Accessed value should be 12.34
    expect($transaction->amount)->toBe(12.34);

    // Set a new value
    $transaction->amount = 56.78;
    expect($transaction->getAttributes()['amount'])->toEqual(5678);
    expect($transaction->amount)->toBe(56.78);
});

it('defaults non-numeric raw amount getter to zero', function () {
    $currency = Currency::factory()->create(['decimals' => 2]);
    $transaction = Transaction::factory()->withCurrency($currency)->make();
    $transaction->setRawAttributes(array_merge($transaction->getAttributes(), ['amount' => 'not-numeric']), true);

    expect($transaction->amount)->toBe(0.0);
});

it('uses default two decimals when currency relation is missing', function () {
    $transaction = Transaction::factory()->make();
    $transaction->unsetRelation('currency');
    $transaction->setRawAttributes(array_merge($transaction->getAttributes(), [
        'currency_id' => null,
        'amount' => 250,
    ]), true);

    expect($transaction->amount)->toBe(2.5);

    $transaction->amount = 3.75;
    expect($transaction->getAttributes()['amount'])->toBe(375.0);
});

it('uses currency decimals for amount scaling', function () {
    $currency = Currency::factory()->create(['decimals' => 3]);
    $transaction = Transaction::factory()->withCurrency($currency)->create(['amount' => 1.234]);

    expect($transaction->getRawOriginal('amount'))->toBe(1234.0);

    $transaction->amount = 9.876;
    expect($transaction->getAttributes()['amount'])->toBe(9876.0);
    expect($transaction->amount)->toBe(9.876);
});

it('stores non-numeric amount input as zero', function () {
    $currency = Currency::factory()->create(['decimals' => 2]);
    $transaction = Transaction::factory()->withCurrency($currency)->make();

    $transaction->amount = 'invalid';

    expect($transaction->getAttributes()['amount'])->toBe(0.0);
});
