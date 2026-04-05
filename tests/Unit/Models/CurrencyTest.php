<?php

declare(strict_types=1);

use App\Models\Currency;

covers(Currency::class);

it('returns uuid as the unique id column', function () {
    $currency = new Currency();
    expect($currency->uniqueIds())->toBe(['uuid']);
});

it('returns uuid as the route key name', function () {
    $currency = new Currency();
    expect($currency->getRouteKeyName())->toBe('uuid');
});

it('has correct table name', function () {
    $currency = new Currency();
    expect($currency->getTable())->toBe('currencies');
    expect(Currency::TABLE_NAME)->toBe('currencies');
});

it('has correct primary key', function () {
    $currency = new Currency();
    expect($currency->getKeyName())->toBe('currency_id');
    expect(Currency::PRIMARY_KEY)->toBe('currency_id');
});

it('has fillable attributes', function () {
    $currency = new Currency();
    expect($currency->getFillable())->toBe(['name', 'code', 'symbol', 'decimals', 'description']);
});
