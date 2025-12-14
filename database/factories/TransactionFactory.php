<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'tally_id' => Tally::factory(),
            'party_id' => User::factory(),
            'currency_id' => Currency::inRandomOrder()->first()->currency_id,
            'category_id' => Category::inRandomOrder()->first()->category_id,
            'type' => Arr::random(TransactionType::cases()),
            'title' => $this->faker->sentence(2),
            'description' => $this->faker->paragraph(1),
            'amount' => $this->faker->randomFloat(2),
            'transaction_date' => $this->faker->date(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function forTally(Tally $tally): self
    {
        return $this->state(fn (array $attributes): array => [
            'tally_id' => $tally->getKey(),
        ]);
    }

    public function forParty(User $party): self
    {
        return $this->state(fn (array $attributes): array => [
            'party_id' => $party->getKey(),
        ]);
    }

    public function withCurrency(Currency $currency): self
    {
        return $this->state(fn (array $attributes): array => [
            'currency_id' => $currency->getKey(),
        ]);
    }

    public function withCategory(Category $category): self
    {
        return $this->state(fn (array $attributes): array => [
            'category_id' => $category->getKey(),
        ]);
    }

    public function withType(TransactionType $type): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $type->value,
        ]);
    }

    public function withAmount(float $amount): self
    {
        return $this->state(fn (array $attributes): array => [
            'amount' => $amount,
        ]);
    }

    public function withTransactionDate(Carbon $transactionDate): self
    {
        return $this->state(fn (array $attributes): array => [
            'transaction_date' => $transactionDate,
        ]);
    }
}
