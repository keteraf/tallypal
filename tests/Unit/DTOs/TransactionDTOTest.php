<?php

declare(strict_types=1);

use App\DTOs\TransactionDTO;

covers(TransactionDTO::class);

it('can be instantiated via constructor', function () {
    $dto = new TransactionDTO(
        party_id: 1,
        currency_id: 2,
        category_id: 3,
        type: 'income',
        title: 'Test',
        description: 'Desc',
        amount: 100.50,
        transaction_date: '2024-01-01'
    );

    expect($dto->getPartyId())->toBe(1)
        ->and($dto->getCurrencyId())->toBe(2)
        ->and($dto->getCategoryId())->toBe(3)
        ->and($dto->getType())->toBe('income')
        ->and($dto->getTitle())->toBe('Test')
        ->and($dto->getDescription())->toBe('Desc')
        ->and($dto->getAmount())->toBe(100.50)
        ->and($dto->getTransactionDate())->toBe('2024-01-01');
});

it('creates from array with all fields', function () {
    $data = [
        'party_id' => 1,
        'currency_id' => 2,
        'category_id' => 3,
        'type' => 'income',
        'title' => 'Test',
        'description' => 'Desc',
        'amount' => 100.50,
        'transaction_date' => '2024-01-01',
    ];

    $dto = TransactionDTO::fromArray($data);

    expect($dto->getPartyId())->toBe(1)
        ->and($dto->getCurrencyId())->toBe(2)
        ->and($dto->getCategoryId())->toBe(3)
        ->and($dto->getType())->toBe('income')
        ->and($dto->getTitle())->toBe('Test')
        ->and($dto->getDescription())->toBe('Desc')
        ->and($dto->getAmount())->toBe(100.50)
        ->and($dto->getTransactionDate())->toBe('2024-01-01');
});

it('creates from array with numeric strings', function () {
    $data = [
        'party_id' => '1',
        'currency_id' => '2',
        'category_id' => '3',
        'type' => 'income',
        'title' => 'Test',
        'description' => 'Desc',
        'amount' => '100.50',
        'transaction_date' => '2024-01-01',
    ];

    $dto = TransactionDTO::fromArray($data);

    expect($dto->getPartyId())->toBe(1)
        ->and($dto->getCurrencyId())->toBe(2)
        ->and($dto->getCategoryId())->toBe(3)
        ->and($dto->getAmount())->toBe(100.50);
});

it('creates from array without description', function () {
    $data = [
        'party_id' => 1,
        'currency_id' => 2,
        'category_id' => 3,
        'type' => 'income',
        'title' => 'Test',
        'amount' => 100.50,
        'transaction_date' => '2024-01-01',
    ];

    $dto = TransactionDTO::fromArray($data);

    expect($dto->getDescription())->toBeNull();
});

it('converts to array', function () {
    $data = [
        'party_id' => 1,
        'currency_id' => 2,
        'category_id' => 3,
        'type' => 'income',
        'title' => 'Test',
        'description' => 'Desc',
        'amount' => 100.50,
        'transaction_date' => '2024-01-01',
    ];

    $dto = TransactionDTO::fromArray($data);
    $array = $dto->toArray();

    expect($array)->toBe($data);
});

it('converts to array with null description', function () {
    $data = [
        'party_id' => 1,
        'currency_id' => 2,
        'category_id' => 3,
        'type' => 'income',
        'title' => 'Test',
        'description' => null,
        'amount' => 100.50,
        'transaction_date' => '2024-01-01',
    ];

    $dto = TransactionDTO::fromArray($data);
    $array = $dto->toArray();

    expect($array)->toBe($data);
});

it('uses setters', function () {
    $dto = new TransactionDTO(1, 2, 3, 'income', 'Title', 'Desc', 100.0, '2024-01-01');

    $dto->setPartyId(10)
        ->setCurrencyId(20)
        ->setCategoryId(30)
        ->setType('expense')
        ->setTitle('New Title')
        ->setDescription('New Desc')
        ->setAmount(200.0)
        ->setTransactionDate('2024-02-01');

    expect($dto->getPartyId())->toBe(10)
        ->and($dto->getCurrencyId())->toBe(20)
        ->and($dto->getCategoryId())->toBe(30)
        ->and($dto->getType())->toBe('expense')
        ->and($dto->getTitle())->toBe('New Title')
        ->and($dto->getDescription())->toBe('New Desc')
        ->and($dto->getAmount())->toBe(200.0)
        ->and($dto->getTransactionDate())->toBe('2024-02-01');
});

it('can set null description', function () {
    $dto = new TransactionDTO(1, 2, 3, 'income', 'Title', 'Desc', 100.0, '2024-01-01');

    $dto->setDescription(null);

    expect($dto->getDescription())->toBeNull();
});

it('has no category when the data has none', function (array $data) {
    $dto = TransactionDTO::fromArray([
        'party_id' => 1,
        'currency_id' => 2,
        'type' => 'expense',
        'title' => 'Test',
        'amount' => 5,
        'transaction_date' => '2024-01-01',
        ...$data,
    ]);

    expect($dto->getCategoryId())->toBeNull()
        ->and($dto->toArray()['category_id'])->toBeNull()
        ->and($dto->setCategoryId(null)->getCategoryId())->toBeNull();
})->with([
    'key missing' => [[]],
    'null' => [['category_id' => null]],
]);
