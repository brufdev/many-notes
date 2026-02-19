<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<VaultNode> */
final class VaultNodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vault_id' => Vault::factory(),
            'parent_id' => null,
            'is_file' => false,
            'name' => fake()->words(3, true),
            'extension' => null,
            'content' => null,
        ];
    }

    public function file(): Factory
    {
        return $this->state(fn(array $attributes): array => [
            'is_file' => true,
            'extension' => 'md',
            'content' => fake()->paragraph(),
        ]);
    }
}
