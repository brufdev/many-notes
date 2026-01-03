<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('sets a template node', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    expect($vault->templates_node_id)->toBe(null);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.templates-node', [
            'vault' => $vault->id,
        ]),
        [
            'templates_node_id' => $node->id,
        ],
    );

    $response->assertStatus(200);
    expect($vault->refresh()->templates_node_id)->toBe($node->id);
});

it('unsets a template node', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $vault->update(['templates_node_id' => $node->id]);
    expect($vault->templates_node_id)->toBe($node->id);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.templates-node', [
            'vault' => $vault->id,
        ]),
        [
            'templates_node_id' => $node->id,
        ],
    );

    $response->assertStatus(200);
    expect($vault->refresh()->templates_node_id)->toBe(null);
});

it('does not set a file as template node', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
    ]);
    expect($vault->templates_node_id)->toBe(null);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.templates-node', [
            'vault' => $vault->id,
        ]),
        [
            'templates_node_id' => $node->id,
        ],
    );

    $response->assertStatus(404);
    expect($vault->refresh()->templates_node_id)->toBe(null);
});

it('does not set a folder from a different vault as template node', function (): void {
    $user = User::factory()->create();
    $vault1 = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $vault2 = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault2, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    expect($vault1->templates_node_id)->toBe(null);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.templates-node', [
            'vault' => $vault1->id,
        ]),
        [
            'templates_node_id' => $node->id,
        ],
    );

    $response->assertStatus(404);
    expect($vault1->refresh()->templates_node_id)->toBe(null);
});

it('does not set a template node if the user lacks permissions', function (): void {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $vault = new CreateVault()->handle($user1, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    expect($vault->templates_node_id)->toBe(null);

    $this->actingAs($user2);

    $response = $this->patch(
        route('vaults.templates-node', [
            'vault' => $vault->id,
        ]),
        [
            'templates_node_id' => $node->id,
        ],
    );

    $response->assertStatus(403);
    expect($vault->refresh()->templates_node_id)->toBe(null);
});
