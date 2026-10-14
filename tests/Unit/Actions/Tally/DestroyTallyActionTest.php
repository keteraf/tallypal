<?php

declare(strict_types=1);

use App\Actions\Tally\DestroyTallyAction;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

covers(DestroyTallyAction::class);

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $failure = databaseFailure();
    DB::partialMock()->shouldReceive('transaction')->once()->andThrow($failure);

    expect(fn () => (new DestroyTallyAction())->handle($tally))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});

test('it deletes the tally, its transactions and its member links', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 100.00]);
    $kept = Transaction::factory()->forTally(Tally::factory()->create(['owner_id' => $owner->id]))->create();
    Transaction::factory()->forTally($tally)->count(2)->create();

    (new DestroyTallyAction())->handle($tally);

    expect(Tally::query()->find($tally->getKey()))->toBeNull()
        ->and(Transaction::query()->where('tally_id', $tally->getKey())->count())->toBe(0)
        ->and(DB::table('tally_user')->where('tally_id', $tally->getKey())->count())->toBe(0)
        ->and(Transaction::query()->find($kept->getKey()))->not->toBeNull();
});
