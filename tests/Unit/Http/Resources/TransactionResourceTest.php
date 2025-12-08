<?php

declare(strict_types=1);

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

covers(TransactionResource::class);

it('transforms transaction to array', function () {
    $transaction = Transaction::factory()->create();
    $resource = new TransactionResource($transaction);
    $request = Request::create('/');

    $array = $resource->toArray($request);

    expect($array)->toHaveKey('id')
        ->and($array)->toHaveKey('tally_id')
        ->and($array)->toHaveKey('party_id')
        ->and($array)->toHaveKey('currency_id')
        ->and($array)->toHaveKey('category_id')
        ->and($array)->toHaveKey('type')
        ->and($array)->toHaveKey('title')
        ->and($array)->toHaveKey('description')
        ->and($array)->toHaveKey('amount')
        ->and($array)->toHaveKey('transaction_date')
        ->and($array)->toHaveKey('created_at')
        ->and($array)->toHaveKey('updated_at')
        ->and($array['id'])->toBe($transaction->transaction_id);
});
