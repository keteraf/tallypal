<?php

declare(strict_types=1);

use App\Http\Requests\Transactions\StoreTransactionRequest;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

covers(StoreTransactionRequest::class);

it('authorizes the request', function () {
    $request = new StoreTransactionRequest();
    expect($request->authorize())->toBeTrue();
});

it('has validation rules', function () {
    $request = new StoreTransactionRequest();
    $rules = $request->rules();

    expect($rules)->toHaveKey('party_id')
        ->and($rules)->toHaveKey('category_id')
        ->and($rules)->toHaveKey('currency_id')
        ->and($rules)->toHaveKey('type')
        ->and($rules)->toHaveKey('title')
        ->and($rules)->toHaveKey('description')
        ->and($rules)->toHaveKey('amount')
        ->and($rules)->toHaveKey('transaction_date');
});
