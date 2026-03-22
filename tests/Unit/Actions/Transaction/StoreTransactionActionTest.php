<?php

declare(strict_types=1);

use App\Actions\Transaction\StoreTransactionAction;
use App\DTOs\TransactionDTO;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;

covers(StoreTransactionAction::class);

it('creates a transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $currency = Currency::where('code', 'USD')->first() ?? Currency::factory()->create(['decimals' => 2]);
    $party = User::factory()->create();
    $category = Category::query()->inRandomOrder()->first();

    $dto = TransactionDTO::fromArray([
        'party_id' => $party->id,
        'currency_id' => $currency->currency_id,
        'category_id' => $category->category_id,
        'type' => 'income',
        'title' => 'Test Transaction',
        'description' => 'Test Description',
        'amount' => 100.50,
        'transaction_date' => '2024-01-01',
    ]);

    $action = new StoreTransactionAction();
    $transaction = $action->handle($dto, $tally);

    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->tally_id)->toBe($tally->tally_id)
        ->and($transaction->party_id)->toBe($party->id)
        ->and($transaction->title)->toBe('Test Transaction')
        ->and($transaction->amount)->toBe(100.50);
});
