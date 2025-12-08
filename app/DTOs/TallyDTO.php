<?php

declare(strict_types=1);

namespace App\DTOs;

final class TallyDTO
{
    public function __construct(
        private int $owner_id,
        private string $name,
        private ?string $description,
    ) {}

    /**
     * @param array{owner_id: int|numeric-string, name: string, description?: string|null} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            owner_id: (int) $data['owner_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public function getOwnerId(): int
    {
        return $this->owner_id;
    }

    public function setOwnerId(int $owner_id): self
    {
        $this->owner_id = $owner_id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
