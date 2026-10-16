<?php

declare(strict_types=1);

use App\Actions\Transaction\GetTallyTransactionsAction;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

covers(GetTallyTransactionsAction::class);

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $failure = databaseFailure();
    $tally = Mockery::mock(Tally::class)->makePartial();
    $tally->shouldReceive('transactions')->andThrow($failure);
    expect(fn () => (new GetTallyTransactionsAction())->handle($tally))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});

it('pages the transactions fifteen at a time', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);
    Transaction::factory()->forTally($tally)->count(16)->create();

    $page = (new GetTallyTransactionsAction())->handle($tally);

    expect($page->perPage())->toBe(15)
        ->and($page->items())->toHaveCount(15)
        ->and($page->total())->toBe(16);
});
