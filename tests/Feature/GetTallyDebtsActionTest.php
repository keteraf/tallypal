<?php

declare(strict_types=1);

use App\Actions\Transaction\GetTallyDebtsAction;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->currency = Currency::query()->create([
        'name' => 'Euro',
        'code' => 'EUR',
        'symbol' => '€',
        'decimals' => 2,
        'description' => 'European Euro',
    ]);
    $this->category = Category::query()->create(['name' => 'Food']);
});

test('it calculates debts for 2 users with equal share', function () {
    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 50.00]);
    $tally->users()->attach($user2, ['default_share_percentage' => 50.00]);

    // User 1 paid 20
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(20.00)
        ->create();

    $action = new GetTallyDebtsAction();
    $debts = $action->handle($tally);

    expect($debts)->toHaveCount(1);
    $settlements = $debts[0]['settlements'];
    expect($settlements)->toHaveCount(1);
    expect($settlements[0]['debtor']['id'])->toBe($user2->id);
    expect($settlements[0]['creditor']['id'])->toBe($user1->id);
    expect($settlements[0]['amount'])->toBe(10.0);
});

test('it calculates debts for 3 users with equal share', function () {
    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);
    $user3 = User::factory()->create(['name' => 'User 3']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 33.33]);
    $tally->users()->attach($user2, ['default_share_percentage' => 33.33]);
    $tally->users()->attach($user3, ['default_share_percentage' => 33.34]);

    // User 1 paid 30
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(30.00)
        ->create();

    $action = new GetTallyDebtsAction();
    $debts = $action->handle($tally);

    expect($debts)->toHaveCount(1);
    $settlements = $debts[0]['settlements'];
    expect($settlements)->toHaveCount(2);

    $user2Debt = collect($settlements)->where('debtor.id', $user2->id)->first();
    $user3Debt = collect($settlements)->where('debtor.id', $user3->id)->first();

    expect($user2Debt['amount'])->toBe(10.0);
    expect($user3Debt['amount'])->toBe(10.0);
});

test('it considers incomes in debt calculation', function () {
    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);

    $tally = Tally::factory()->create();
    $tally->users()->attach($user1, ['default_share_percentage' => 50.00]);
    $tally->users()->attach($user2, ['default_share_percentage' => 50.00]);

    // User 1 paid 20 (expense)
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::EXPENSE)
        ->withAmount(20.00)
        ->create();

    // User 1 received 4 (income)
    Transaction::factory()
        ->forTally($tally)
        ->forParty($user1)
        ->withCurrency($this->currency)
        ->withCategory($this->category)
        ->withType(TransactionType::INCOME)
        ->withAmount(4.00)
        ->create();

    // Net spent = 16. User 1 net paid = 16. Each share = 8.
    // User 2 owes User 1 8.

    $action = new GetTallyDebtsAction();
    $debts = $action->handle($tally);

    expect($debts)->toHaveCount(1);
    $settlements = $debts[0]['settlements'];
    expect($settlements)->toHaveCount(1);
    expect($settlements[0]['debtor']['id'])->toBe($user2->id);
    expect($settlements[0]['creditor']['id'])->toBe($user1->id);
    expect($settlements[0]['amount'])->toBe(8.0);
});
