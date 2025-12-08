<?php

declare(strict_types=1);

use App\Http\Requests\Transactions\UpdateTransactionRequest;

covers(UpdateTransactionRequest::class);

it('authorizes the request', function () {
    $request = new UpdateTransactionRequest();
    expect($request->authorize())->toBeTrue();
});

it('has validation rules', function () {
    $request = new UpdateTransactionRequest();
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
