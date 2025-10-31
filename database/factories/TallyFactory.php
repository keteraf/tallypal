<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tally;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tally>
 */
class TallyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->create()->id,
            'name' => fake()->sentence(2),
            'description' => fake()->paragraph(1),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function withOwner(User $user): self
    {
        return $this->state(fn (array $attributes): array => [
            'owner_id' => $user->getKey(),
        ]);
    }
}
