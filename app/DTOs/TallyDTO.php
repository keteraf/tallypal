<?php

declare(strict_types=1);

namespace App\DTOs;

final class TallyDTO
{
    public function __construct(
        private int $owner_id,
        private string $name,
        private ?string $description,
        private ?string $color = null,
        private ?string $glyph = null,
        private ?int $default_currency_id = null,
    ) {}

    /**
     * @param  array{owner_id: int|numeric-string, name: string, description?: string|null, color?: string|null, glyph?: string|null, default_currency_id?: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            owner_id: (int) $data['owner_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            color: $data['color'] ?? null,
            glyph: $data['glyph'] ?? null,
            default_currency_id: $data['default_currency_id'] ?? null,
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
            'color' => $this->color,
            'glyph' => $this->glyph,
            'default_currency_id' => $this->default_currency_id,
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getGlyph(): ?string
    {
        return $this->glyph;
    }

    public function setGlyph(?string $glyph): self
    {
        $this->glyph = $glyph;

        return $this;
    }

    public function getDefaultCurrencyId(): ?int
    {
        return $this->default_currency_id;
    }

    public function setDefaultCurrencyId(?int $default_currency_id): self
    {
        $this->default_currency_id = $default_currency_id;

        return $this;
    }
}
