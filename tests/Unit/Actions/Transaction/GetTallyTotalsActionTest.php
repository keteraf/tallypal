<?php

declare(strict_types=1);

use App\Actions\Transaction\GetTallyTotalsAction;
use App\Enums\TransactionType;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

covers(GetTallyTotalsAction::class);

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $failure = databaseFailure();
    $tally = Mockery::mock(Tally::class)->makePartial();
    $tally->shouldReceive('transactions')->andThrow($failure);
    expect(fn () => (new GetTallyTotalsAction())->handle($tally))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});

test('it totals per type and currency in major units', function (): void {
    $tally = Tally::factory()->create();
    $eur = Currency::factory()->create(['decimals' => 2]);
    $jpy = Currency::factory()->create(['decimals' => 0]);
    Transaction::factory()->forTally($tally)->withCurrency($eur)->withType(TransactionType::EXPENSE)->withAmount(10.25)->create();
    Transaction::factory()->forTally($tally)->withCurrency($eur)->withType(TransactionType::EXPENSE)->withAmount(5.50)->create();
    Transaction::factory()->forTally($tally)->withCurrency($eur)->withType(TransactionType::INCOME)->withAmount(40.00)->create();
    Transaction::factory()->forTally($tally)->withCurrency($jpy)->withType(TransactionType::EXPENSE)->withAmount(300)->create();

    $totals = (new GetTallyTotalsAction())->handle($tally)
        ->keyBy(fn (array $row): string => $row['type'] . '-' . $row['currency_id']);

    expect($totals)->toHaveCount(3)
        ->and($totals[TransactionType::EXPENSE->value . '-' . $eur->getKey()]['total'])->toBe(15.75)
        ->and($totals[TransactionType::INCOME->value . '-' . $eur->getKey()]['total'])->toBe(40.0)
        ->and($totals[TransactionType::EXPENSE->value . '-' . $jpy->getKey()]['total'])->toBe(300.0)
        ->and($totals[TransactionType::EXPENSE->value . '-' . $jpy->getKey()]['currency'])->toBe([
            'code' => $jpy->code,
            'symbol' => $jpy->symbol,
            'decimals' => 0,
        ]);
});
