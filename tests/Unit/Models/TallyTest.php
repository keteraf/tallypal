<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

covers(Tally::class);

it('has correct table name', function () {
    $tally = new Tally();
    expect($tally->getTable())->toBe('tallies');
    expect(Tally::TABLE_NAME)->toBe('tallies');
    expect(Tally::KEYS_PREFIX)->toBe('tally');
});

it('has correct primary key', function () {
    $tally = new Tally();
    expect($tally->getKeyName())->toBe('tally_id');
    expect(Tally::PRIMARY_KEY)->toBe('tally_id');
});

it('has fillable attributes', function () {
    $tally = new Tally();
    expect($tally->getFillable())->toBe([
        'owner_id',
        'name',
        'description',
    ]);
    expect($tally->getGuarded())->toContain('income_total', 'expense_total');
});

it('belongs to an owner', function () {
    $tally = Tally::factory()->create();
    expect($tally->owner())->toBeInstanceOf(BelongsTo::class)
        ->and($tally->owner)->toBeInstanceOf(User::class);
});

it('belongs to many users', function () {
    $tally = Tally::factory()->create();
    $relation = $tally->users();
    expect($relation)->toBeInstanceOf(BelongsToMany::class);
    expect($relation->getTable())->toBe('tally_user');
});

it('can attach users', function () {
    $tally = Tally::factory()->create();
    $user = User::factory()->create();

    $tally->users()->attach([$user->id]);

    expect($tally->fresh()->users)->toHaveCount(1)
        ->and($tally->users->first()->id)->toBe($user->id);
});

it('has many transactions', function () {
    $tally = Tally::factory()->create();
    expect($tally->transactions())->toBeInstanceOf(HasMany::class);
});

it('has many invitations', function () {
    $tally = Tally::factory()->create();
    expect($tally->invitations())->toBeInstanceOf(HasMany::class);
});

it('returns uuid as the unique id column', function () {
    $tally = new Tally();
    expect($tally->uniqueIds())->toBe(['uuid']);
});

it('returns uuid as the route key name', function () {
    $tally = new Tally();
    expect($tally->getRouteKeyName())->toBe('uuid');
});

it('scopeForUser returns owned tallies', function (): void {
    $user = User::factory()->create();
    $owned = Tally::factory()->create(['owner_id' => $user->id]);
    $other = Tally::factory()->create();

    $result = Tally::query()->forUser((int) $user->id)->pluck('tally_id');

    expect($result->toArray())->toContain($owned->tally_id)
        ->and($result->toArray())->not->toContain($other->tally_id);
});

it('scopeForUser returns tallies the user is a member of', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $shared = Tally::factory()->create(['owner_id' => $owner->id]);
    $shared->users()->attach($member->id, ['default_share_percentage' => 50]);

    $result = Tally::query()->forUser((int) $member->id)->pluck('tally_id');

    expect($result->toArray())->toContain($shared->tally_id);
});

it('scopeWithIncomeAndExpenseTotals computes totals in decimals', function (): void {
    $user = User::factory()->create();
    $currency = Currency::factory()->create(['decimals' => 2]);
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    Transaction::factory()->forTally($tally)->withCurrency($currency)
        ->create(['type' => 'income', 'amount' => 10.0]);
    Transaction::factory()->forTally($tally)->withCurrency($currency)
        ->create(['type' => 'expense', 'amount' => 4.0]);

    /** @var Tally $result */
    $result = Tally::query()->withIncomeAndExpenseTotals()->find($tally->tally_id);

    expect($result->income_total)->toBe(10.0)
        ->and($result->expense_total)->toBe(4.0);
});

it('scopeWithIncomeAndExpenseTotals returns zero when no transactions exist', function (): void {
    $tally = Tally::factory()->create();

    /** @var Tally $result */
    $result = Tally::query()->withIncomeAndExpenseTotals()->find($tally->tally_id);

    expect($result->income_total)->toBe(0.0)
        ->and($result->expense_total)->toBe(0.0);
});

it('creates with factory', function () {
    $tally = Tally::factory()->create([
        'name' => 'Test Tally',
        'description' => 'Test Description',
    ]);

    expect($tally->name)->toBe('Test Tally')
        ->and($tally->description)->toBe('Test Description')
        ->and($tally->owner)->toBeInstanceOf(User::class);
});
