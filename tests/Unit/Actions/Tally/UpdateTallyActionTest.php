<?php

declare(strict_types=1);

use App\Actions\Tally\UpdateTallyAction;
use App\DTOs\TallyDTO;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

covers(UpdateTallyAction::class);

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $dto = TallyDTO::fromArray(['owner_id' => $owner->id, 'name' => 'Household', 'description' => null]);
    $failure = databaseFailure();
    DB::partialMock()->shouldReceive('transaction')->once()->andThrow($failure);

    expect(fn () => (new UpdateTallyAction())->handle($tally, $dto))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});

test('it updates the tally and returns the refreshed model', function (): void {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id, 'name' => 'Old']);
    $dto = TallyDTO::fromArray(['owner_id' => $owner->id, 'name' => 'New', 'description' => 'About']);

    $result = (new UpdateTallyAction())->handle($tally, $dto);

    expect($result->name)->toBe('New')
        ->and($result->description)->toBe('About')
        ->and(Tally::query()->find($tally->getKey())->name)->toBe('New');
});
