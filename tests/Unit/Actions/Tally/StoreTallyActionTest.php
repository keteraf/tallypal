<?php

declare(strict_types=1);

use App\Actions\Tally\StoreTallyAction;
use App\DTOs\TallyDTO;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

covers(StoreTallyAction::class);

test('a database error is reported and becomes a plain 500', function (): void {
    Exceptions::fake();
    $owner = User::factory()->create();
    $dto = TallyDTO::fromArray(['owner_id' => $owner->id, 'name' => 'Household', 'description' => null]);
    $failure = databaseFailure();
    DB::partialMock()->shouldReceive('transaction')->once()->andThrow($failure);

    expect(fn () => (new StoreTallyAction())->handle($dto))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});

test('it creates the tally and attaches the owner at a 100% share', function (): void {
    $owner = User::factory()->create();
    $dto = TallyDTO::fromArray(['owner_id' => $owner->id, 'name' => 'Household', 'description' => null]);

    $tally = (new StoreTallyAction())->handle($dto);

    expect($tally)->toBeInstanceOf(Tally::class)
        ->and($tally->name)->toBe('Household')
        ->and($tally->users()->count())->toBe(1)
        ->and((float) $tally->users()->first()->pivot->default_share_percentage)->toBe(100.0);
});
