<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('updates a node', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(4, true);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.update', [
            'vault' => $vault->id,
            'node' => $node->id,
        ]),
        [
            'name' => $newName,
        ],
    );

    $response->assertStatus(200);
    expect($vault->nodes()->first()->name)->toBe($newName);
    $path = new GetPathFromVault()->handle($vault) . $newName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('does not update a node from a different vault', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(4, true);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.update', [
            'vault' => 2,
            'node' => $node->id,
        ]),
        [
            'name' => $newName,
        ],
    );

    $response->assertStatus(404);
    expect($vault->nodes()->first()->name)->toBe($node->name);
});

it('does not update a node without permissions', function (): void {
    $user = User::factory()->create();
    $secondUser = User::factory()->create();
    $vault = new CreateVault()->handle($secondUser, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user);

    $response = $this->patch(
        route('vaults.nodes.update', [
            'vault' => $vault->id,
            'node' => $node->id,
        ]),
        [
            'name' => fake()->words(3, true),
        ],
    );

    $response->assertStatus(403);
    expect($vault->nodes()->first()->name)->toBe($node->name);
});
