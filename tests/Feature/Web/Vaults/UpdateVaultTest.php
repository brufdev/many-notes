<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;

it('allows a user to update his vault', function (): void {
    $user = User::factory()->create();

    $vault = Vault::factory()->for($user)->create();

    $folder = VaultNode::factory()->for($vault)->create();

    $payload = [
        'name' => fake()->words(3, true),
        'templates_node_id' => $folder->id,
    ];

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault->id]),
            $payload,
        );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'name',
                'templates_node_id',
            ],
        ])
        ->assertJsonPath('data.name', $payload['name'])
        ->assertJsonPath('data.templates_node_id', $payload['templates_node_id']);
});

it('forbids users without permission from updating a vault', function (): void {
    [$user1, $user2] = User::factory(2)->create();

    $vault = Vault::factory()->for($user1)->create();

    $payload = [
        'name' => fake()->words(3, true),
    ];

    $response = $this
        ->actingAs($user2)
        ->patch(
            route('vaults.update', ['vault' => $vault->id]),
            $payload,
        );

    $response
        ->assertForbidden();
});

it('does not allow to set a folder from a different vault as template node', function (): void {
    $user = User::factory()->create();

    [$vault1, $vault2] = Vault::factory(2)->for($user)->create();

    $folder = VaultNode::factory()->for($vault2)->create();

    $payload = [
        'templates_node_id' => $folder->id,
    ];

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault1->id]),
            $payload,
        );

    $response
        ->assertRedirect()
        ->assertSessionHasErrors([
            'templates_node_id' => 'The selected templates node id is invalid.',
        ]);
});

it('does not allow renaming a vault to an existing name', function (): void {
    $user = User::factory()->create();

    [$vault1, $vault2] = Vault::factory(2)->for($user)->create();

    $payload = [
        'name' => $vault2->name,
    ];

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault1->id]),
            $payload,
        );

    $response
        ->assertRedirect()
        ->assertSessionHasErrors([
            'name' => 'The name has already been taken.',
        ]);
});

it('allows renaming a vault to the name it already has', function (): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create();

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault->id]),
            ['name' => $vault->name],
        );

    $response->assertOk();
    expect($vault->refresh()->name)->toBe($vault->name);
});

it('does not allow setting a file as template node', function (): void {
    $user = User::factory()->hasVaults(1)->create();

    $vault = Vault::factory()->for($user)->create();

    $file = VaultNode::factory()->for($vault)->file()->create();

    $payload = [
        'templates_node_id' => $file->id,
    ];

    $response = $this
        ->actingAs($user)
        ->patch(
            route('vaults.update', ['vault' => $vault->id]),
            $payload,
        );

    $response
        ->assertRedirect()
        ->assertSessionHasErrors([
            'templates_node_id' => 'The selected templates node id is invalid.',
        ]);
});
