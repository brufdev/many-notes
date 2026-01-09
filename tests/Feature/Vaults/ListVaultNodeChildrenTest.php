<?php

declare(strict_types=1);

use App\Models\User;
use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;

it('lists the children of a folder', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($user);

    $response = $this->get(
        route('vaults.nodes.children', [
            'vault' => $vault->id,
            'node' => $folder->id,
        ]),
    );

    $response->assertStatus(200);
    expect($response->content())
        ->json()
        ->children
        ->toHaveCount(1);
});

it('does not list the children of a folder without permissions', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = new CreateVault()->handle($user1, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($user2);

    $response = $this->get(
        route('vaults.nodes.children', [
            'vault' => $vault->id,
            'node' => $folder->id,
        ]),
    );

    $response->assertStatus(403);
});
