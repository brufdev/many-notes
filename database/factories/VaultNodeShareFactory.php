<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\VaultNode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VaultNodeShare>
 */
final class VaultNodeShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vault_node_id' => VaultNode::factory(),
            'token' => Str::random(48),
        ];
    }
}
