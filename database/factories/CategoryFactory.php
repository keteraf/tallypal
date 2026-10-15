<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => CategoryType::EXPENSE,
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'color' => fake()->randomElement(['c1', 'c2', 'c3', 'c4', 'c5', 'c6']),
        ];
    }

    public function income(): self
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CategoryType::INCOME,
        ]);
    }
}
