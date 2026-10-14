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

    expect($array['owner_id'])->toBe(1)
        ->and($array['name'])->toBe('Test Tally')
        ->and($array['description'])->toBe('Test Description')
        ->and($array['color'])->toBeNull()
        ->and($array['glyph'])->toBeNull();
});

it('converts to array with null description', function () {
    $data = [
        'owner_id' => 1,
        'name' => 'Test Tally',
        'description' => null,
    ];

    $dto = TallyDTO::fromArray($data);
    $array = $dto->toArray();

    expect($array['owner_id'])->toBe(1)
        ->and($array['name'])->toBe('Test Tally')
        ->and($array['description'])->toBeNull()
        ->and($array['color'])->toBeNull()
        ->and($array['glyph'])->toBeNull();
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

it('includes color and glyph from array', function () {
    $data = [
        'owner_id' => 1,
        'name' => 'Test Tally',
        'description' => null,
        'color' => 'c3',
        'glyph' => '✈',
    ];

    $dto = TallyDTO::fromArray($data);

    expect($dto->getColor())->toBe('c3')
        ->and($dto->getGlyph())->toBe('✈');
});

it('converts to array including color and glyph', function () {
    $dto = new TallyDTO(1, 'Test', null, 'c2', '⌂');

    $array = $dto->toArray();

    expect($array['color'])->toBe('c2')
        ->and($array['glyph'])->toBe('⌂');
});

it('defaults color and glyph to null when omitted from array', function () {
    $data = [
        'owner_id' => 1,
        'name' => 'Test Tally',
        'description' => null,
    ];

    $dto = TallyDTO::fromArray($data);

    expect($dto->getColor())->toBeNull()
        ->and($dto->getGlyph())->toBeNull();
});

it('uses color and glyph setters', function () {
    $dto = new TallyDTO(1, 'Test', null);

    $dto->setColor('c4')->setGlyph('♪');

    expect($dto->getColor())->toBe('c4')
        ->and($dto->getGlyph())->toBe('♪');
});

it('carries the default currency id through array, getter and setter', function () {
    $dto = TallyDTO::fromArray(['owner_id' => 1, 'name' => 'T', 'description' => null, 'default_currency_id' => 2]);

    expect($dto->getDefaultCurrencyId())->toBe(2)
        ->and($dto->toArray()['default_currency_id'])->toBe(2)
        ->and($dto->setDefaultCurrencyId(null)->getDefaultCurrencyId())->toBeNull()
        ->and(TallyDTO::fromArray(['owner_id' => 1, 'name' => 'T', 'description' => null])->getDefaultCurrencyId())->toBeNull();
});
