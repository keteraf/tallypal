<?php

declare(strict_types=1);

use App\Actions\Transaction\GetTallySplitAction;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

uses(RefreshDatabase::class);

covers(GetTallySplitAction::class);

beforeEach(function () {
    $this->currency = Currency::query()->create([
        'name' => 'Euro',
        'code' => 'EUR',
        'symbol' => '€',
        'decimals' => 2,
        'description' => 'European Euro',
    ]);
    $this->category = Category::factory()->create(['name' => 'Food']);
});

test('it splits expenses between two members with correct percentages', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 60.00]);
    $tally->users()->attach($user2, ['default_share_percentage' => 40.00]);

    // Alice paid 60
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(60.00)
        ->create();

    // Bob paid 40
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user2)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(40.00)
        ->create();

    $action = new GetTallySplitAction();
    $splits = $action->handle($tally);

    expect($splits)->toHaveCount(1);

    $split = $splits[0];
    expect($split['currency']['code'])->toBe('EUR');
    expect($split['members'])->toHaveCount(2);

    $aliceMember = collect($split['members'])->firstWhere('name', 'Alice');
    $bobMember = collect($split['members'])->firstWhere('name', 'Bob');

    expect($aliceMember['amount'])->toBe(60.0);
    expect($aliceMember['percentage'])->toBe(60.0);
    expect($bobMember['amount'])->toBe(40.0);
    expect($bobMember['percentage'])->toBe(40.0);
});

test('it returns zero amount and percentage for a member with no transactions', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 100.00]);
    $tally->users()->attach($user2, ['default_share_percentage' => 0.00]);

    // Only Alice has transactions
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(100.00)
        ->create();

    $action = new GetTallySplitAction();
    $splits = $action->handle($tally);

    expect($splits)->toHaveCount(1);

    $bobMember = collect($splits[0]['members'])->firstWhere('name', 'Bob');
    expect((float) $bobMember['amount'])->toBe(0.0);
    expect((float) $bobMember['percentage'])->toBe(0.0);
});

test('it does not count income transactions toward paid amount', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 50.00]);
    $tally->users()->attach($user2, ['default_share_percentage' => 50.00]);

    // Alice paid 80 expense
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(80.00)
        ->create();

    // Alice also has income of 20 — must not count toward "paid"
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::INCOME)
        ->withAmount(20.00)
        ->create();

    $action = new GetTallySplitAction();
    $splits = $action->handle($tally);

    expect($splits)->toHaveCount(1);

    // Total expenses = 80 (income excluded)
    $aliceMember = collect($splits[0]['members'])->firstWhere('name', 'Alice');
    expect($aliceMember['amount'])->toBe(80.0);
    expect($aliceMember['percentage'])->toBe(100.0);
});

test('it returns empty collection for a tally with no expenses', function () {
    $user1 = User::factory()->create(['name' => 'Alice']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 100.00]);

    // Only income — no expenses
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::INCOME)
        ->withAmount(50.00)
        ->create();

    $action = new GetTallySplitAction();
    $splits = $action->handle($tally);

    expect($splits)->toBeEmpty();
});

test('it returns empty collection when tally has no transactions', function () {
    $tally = Tally::factory()->create();

    $action = new GetTallySplitAction();
    $splits = $action->handle($tally);

    expect($splits)->toBeEmpty();
});

test('it includes initials for members', function () {
    $user1 = User::factory()->create(['name' => 'Alice Smith']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 100.00]);

    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(50.00)
        ->create();

    $action = new GetTallySplitAction();
    $splits = $action->handle($tally);

    $member = $splits[0]['members'][0];
    expect($member['initials'])->toBe('AS');
});

test('it gives a member with a blank name the double-dot initials', function () {
    $user = User::factory()->create(['name' => '   ']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user, ['default_share_percentage' => 100.00]);

    Transaction::factory()
        ->forTally($tally)
        ->forParty($user)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(5.00)
        ->create();

    $splits = (new GetTallySplitAction())->handle($tally);

    expect($splits[0]['members'][0]['initials'])->toBe('··');
});

test('a database error is reported and becomes a plain 500', function () {
    Exceptions::fake();
    $failure = databaseFailure();
    $tally = Mockery::mock(Tally::class)->makePartial();
    $tally->shouldReceive('users')->once()->andThrow($failure);

    expect(fn () => (new GetTallySplitAction())->handle($tally))
        ->toThrow(function (HttpException $exception): void {
            expect($exception->getStatusCode())->toBe(500)
                ->and($exception->getMessage())->not->toContain('secret_table');
        });

    Exceptions::assertReported(fn (QueryException $exception): bool => $exception === $failure);
});
