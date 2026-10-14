<?php

declare(strict_types=1);

use App\Actions\Transaction\UpdateTransactionAction;
use App\DTOs\TransactionDTO;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

covers(UpdateTransactionAction::class);

it('updates a transaction', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    $currency = Currency::where('code', 'USD')->first() ?? Currency::factory()->create(['decimals' => 2]);
    $transaction = Transaction::factory()->forTally($tally)->withCurrency($currency)->create(['title' => 'Old Title', 'amount' => 50.00]);
    $party = User::factory()->create();
    $category = Category::query()->inRandomOrder()->first();

    $dto = TransactionDTO::fromArray([
        'party_id' => $party->id,
        'currency_id' => $currency->currency_id,
        'category_id' => $category->category_id,
        'type' => 'expense',
        'title' => 'Updated Title',
        'description' => 'Updated Description',
        'amount' => 200.75,
        'transaction_date' => '2024-02-01',
    ]);

    $action = new UpdateTransactionAction();
    $updated = $action->handle($transaction, $dto);

    expect($updated->title)->toBe('Updated Title')
        ->and($updated->amount)->toBe(200.75)
        ->and($updated->type->value)->toBe('expense');
});

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $currency = Currency::factory()->create(['decimals' => 2]);
    $transaction = Transaction::factory()->forTally($tally)->withCurrency($currency)->create();
    $dto = TransactionDTO::fromArray([
        'party_id' => $owner->id,
        'currency_id' => $currency->currency_id,
        'category_id' => Category::query()->inRandomOrder()->first()->category_id,
        'type' => 'expense',
        'title' => 'Coffee',
        'description' => null,
        'amount' => 5.0,
        'transaction_date' => '2024-01-01',
    ]);
    $failure = databaseFailure();
    DB::partialMock()->shouldReceive('transaction')->once()->andThrow($failure);

    expect(fn () => (new UpdateTransactionAction())->handle($transaction, $dto))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});
