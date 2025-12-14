<?php

declare(strict_types=1);

use App\Http\Requests\Transactions\DestroyTransactionRequest;

covers(DestroyTransactionRequest::class);

it('authorizes the request', function () {
    $request = new DestroyTransactionRequest();
    expect($request->authorize())->toBeTrue();
});

it('has empty validation rules', function () {
    $request = new DestroyTransactionRequest();
    expect($request->rules())->toBe([]);
});
