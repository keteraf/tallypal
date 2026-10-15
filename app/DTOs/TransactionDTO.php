<?php

declare(strict_types=1);

namespace App\DTOs;

final class TransactionDTO
{
    public function __construct(
        private int $party_id,
        private int $currency_id,
        private ?int $category_id,
        private string $type,
        private string $title,
        private ?string $description,
        private float $amount,
        private string $transaction_date,
    ) {}

    /**
     * @param  array{party_id: int|numeric-string, currency_id: int|numeric-string, category_id?: int|numeric-string|null, type: string, title: string, description?: string|null, amount: float|numeric-string, transaction_date: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            party_id: (int) $data['party_id'],
            currency_id: (int) $data['currency_id'],
            category_id: isset($data['category_id']) ? (int) $data['category_id'] : null,
            type: $data['type'],
            title: $data['title'],
            description: $data['description'] ?? null,
            amount: (float) $data['amount'],
            transaction_date: $data['transaction_date'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'party_id' => $this->getPartyId(),
            'currency_id' => $this->getCurrencyId(),
            'category_id' => $this->getCategoryId(),
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'amount' => $this->getAmount(),
            'transaction_date' => $this->getTransactionDate(),
        ];
    }

    public function getPartyId(): int
    {
        return $this->party_id;
    }

    public function setPartyId(int $party_id): self
    {
        $this->party_id = $party_id;

        return $this;
    }

    public function getCurrencyId(): int
    {
        return $this->currency_id;
    }

    public function setCurrencyId(int $currency_id): self
    {
        $this->currency_id = $currency_id;

        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->category_id;
    }

    public function setCategoryId(?int $category_id): self
    {
        $this->category_id = $category_id;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

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

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTransactionDate(): string
    {
        return $this->transaction_date;
    }

    public function setTransactionDate(string $transaction_date): self
    {
        $this->transaction_date = $transaction_date;

        return $this;
    }
}
