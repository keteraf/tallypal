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
        'color',
        'glyph',
        'default_currency_id',
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

it('counts the owner once when the tally has no pivot rows', function () {
    $tally = Tally::factory()->create();

    expect($tally->membersCount())->toBe(1);
});

it('counts the owner once when the owner is also in the pivot', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 60]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 40]);

    expect($tally->membersCount())->toBe(2);
});

it('counts the owner when the owner is missing from the pivot', function () {
    $tally = Tally::factory()->create();
    $tally->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 50]);
    $tally->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 50]);

    expect($tally->membersCount())->toBe(3);
});

it('exposes the members count as users_count for many tallies at once', function () {
    $owner = User::factory()->create();
    $solo = Tally::factory()->create(['owner_id' => $owner->id]);
    $solo->users()->attach($owner->id, ['default_share_percentage' => 100]);
    $shared = Tally::factory()->create(['owner_id' => $owner->id]);
    $shared->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $shared->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 50]);

    $counts = Tally::query()->withMembersCount()->get()->pluck('users_count', 'tally_id');

    expect((int) $counts[$solo->tally_id])->toBe(1)
        ->and((int) $counts[$shared->tally_id])->toBe(2);
});

it('lists its members owner first, then the others by id, the owner once', function () {
    $later = User::factory()->create();
    $earlier = User::factory()->create();
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($earlier->id, ['default_share_percentage' => 30]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 50]);
    $tally->users()->attach($later->id, ['default_share_percentage' => 20]);

    expect($tally->membersInOrder()->pluck('id')->all())->toBe([$owner->id, $later->id, $earlier->id])
        ->and($tally->memberShares())->toBe([$owner->id => 50.0, $later->id => 20.0, $earlier->id => 30.0]);
});

it('gives an owner without a pivot row the remainder of the other shares', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 35]);

    expect($tally->membersInOrder()->pluck('id')->all())->toBe([$owner->id, $member->id])
        ->and($tally->memberShares())->toBe([$owner->id => 65.0, $member->id => 35.0]);
});

it('never gives an owner without a pivot row a negative share', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 70]);
    $tally->users()->attach(User::factory()->create()->id, ['default_share_percentage' => 40]);

    expect($tally->memberShares()[$owner->id])->toBe(0.0);
});

it('counts one member for a tally that is not stored', function (): void {
    $tally = new Tally;
    $tally->setAttribute(Tally::PRIMARY_KEY, 999_999);

    expect($tally->membersCount())->toBe(1);
});

it('lets an owner with a share row keep it whatever the other members hold', function (): void {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);
    $tally->users()->attach($owner->id, ['default_share_percentage' => 10]);
    $tally->users()->attach($member->id, ['default_share_percentage' => 90]);

    expect($tally->fresh()->memberShares())->toBe([$owner->id => 10.0, $member->id => 90.0]);
});
