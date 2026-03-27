<?php

declare(strict_types=1);

use App\DTOs\TallyDTO;

covers(TallyDTO::class);

it('can be instantiated with full data', function () {
    $dto = new TallyDTO(1, 'Test Tally', 'Test Description');

    expect($dto->getOwnerId())->toBe(1)
        ->and($dto->getName())->toBe('Test Tally')
        ->and($dto->getDescription())->toBe('Test Description');
});

it('can be instantiated with nullable description', function () {
    $dto = new TallyDTO(1, 'Test Tally', null);

    expect($dto->getOwnerId())->toBe(1)
        ->and($dto->getName())->toBe('Test Tally')
        ->and($dto->getDescription())->toBeNull();
});

it('creates from array with all fields', function () {
    $data = [
        'owner_id' => 1,
        'name' => 'Test Tally',
        'description' => 'Test Description',
    ];

    $dto = TallyDTO::fromArray($data);

    expect($dto->getOwnerId())->toBe(1)
        ->and($dto->getName())->toBe('Test Tally')
        ->and($dto->getDescription())->toBe('Test Description');
});

it('creates from array with numeric string owner_id', function () {
    $data = [
        'owner_id' => '123',
        'name' => 'Test Tally',
        'description' => 'Test Description',
    ];

    $dto = TallyDTO::fromArray($data);

    expect($dto->getOwnerId())->toBe(123);
});

it('creates from array without description', function () {
    $data = [
        'owner_id' => 1,
        'name' => 'Test Tally',
    ];

    $dto = TallyDTO::fromArray($data);

    expect($dto->getOwnerId())->toBe(1)
        ->and($dto->getName())->toBe('Test Tally')
        ->and($dto->getDescription())->toBeNull();
});

it('converts to array', function () {
    $data = [
        'owner_id' => 1,
        'name' => 'Test Tally',
        'description' => 'Test Description',
    ];

    $dto = TallyDTO::fromArray($data);
    $array = $dto->toArray();

    expect($array)->toBe($data);
});

it('converts to array with null description', function () {
    $data = [
        'owner_id' => 1,
        'name' => 'Test Tally',
        'description' => null,
    ];

    $dto = TallyDTO::fromArray($data);
    $array = $dto->toArray();

    expect($array)->toBe($data);
});

it('uses setters', function () {
    $dto = new TallyDTO(1, 'Original', 'Original Description');

    $dto->setOwnerId(2)
        ->setName('Updated Name')
        ->setDescription('Updated Description');

    expect($dto->getOwnerId())->toBe(2)
        ->and($dto->getName())->toBe('Updated Name')
        ->and($dto->getDescription())->toBe('Updated Description');
});

it('can set null description', function () {
    $dto = new TallyDTO(1, 'Original', 'Original Description');

    $dto->setDescription(null);

    expect($dto->getDescription())->toBeNull();
});
