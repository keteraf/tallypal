<?php

declare(strict_types=1);

use App\DTOs\TallyDTO;

covers(TallyDTO::class);

it('uses setters', function () {
    $dto = TallyDTO::fromArray([
        'owner_id' => 1,
        'name' => 'Test',
        'description' => 'Desc',
    ]);

    $dto->setOwnerId(10)
        ->setName('New Name')
        ->setDescription('New Desc');

    expect($dto->getOwnerId())->toBe(10)
        ->and($dto->getName())->toBe('New Name')
        ->and($dto->getDescription())->toBe('New Desc');
});
