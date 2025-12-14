<?php

declare(strict_types=1);

use App\DTOs\TransactionDTO;

covers(TransactionDTO::class);

it('creates from array', function () {
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

it('converts to array', function () {
    $dto = TransactionDTO::fromArray([
        'party_id' => 1,
        'currency_id' => 2,
        'category_id' => 3,
        'type' => 'expense',
        'title' => 'Test',
        'description' => null,
        'amount' => 50.00,
        'transaction_date' => '2024-01-01',
    ]);

    $array = $dto->toArray();

    expect($array)->toBe([
        'party_id' => 1,
        'currency_id' => 2,
        'category_id' => 3,
        'type' => 'expense',
        'title' => 'Test',
        'description' => null,
        'amount' => 50.00,
        'transaction_date' => '2024-01-01',
    ]);
});

it('uses setters', function () {
    $dto = TransactionDTO::fromArray([
        'party_id' => 1,
        'currency_id' => 2,
        'category_id' => 3,
        'type' => 'income',
        'title' => 'Test',
        'description' => 'Desc',
        'amount' => 100.00,
        'transaction_date' => '2024-01-01',
    ]);

    $dto->setPartyId(10)
        ->setCurrencyId(20)
        ->setCategoryId(30)
        ->setType('expense')
        ->setTitle('New Title')
        ->setDescription('New Desc')
        ->setAmount(200.00)
        ->setTransactionDate('2024-02-01');

    expect($dto->getPartyId())->toBe(10)
        ->and($dto->getCurrencyId())->toBe(20)
        ->and($dto->getCategoryId())->toBe(30)
        ->and($dto->getType())->toBe('expense')
        ->and($dto->getTitle())->toBe('New Title')
        ->and($dto->getDescription())->toBe('New Desc')
        ->and($dto->getAmount())->toBe(200.00)
        ->and($dto->getTransactionDate())->toBe('2024-02-01');
});
