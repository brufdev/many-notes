<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('creates a share link for a note', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'Hello world',
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('vaults.nodes.share.store', ['vault' => $vault->id, 'node' => $note->id]),
    );

    $response->assertStatus(200);
    $response->assertJsonStructure(['data' => ['token', 'url']]);
    expect($note->fresh()->share)->not->toBeNull();
});

it('returns the same token when a note is already shared', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'Hello world',
    ]);

    $this->actingAs($user);

    $firstResponse = $this->post(
        route('vaults.nodes.share.store', ['vault' => $vault->id, 'node' => $note->id]),
    );
    $secondResponse = $this->post(
        route('vaults.nodes.share.store', ['vault' => $vault->id, 'node' => $note->id]),
    );

    expect($firstResponse->json('data.token'))->toBe($secondResponse->json('data.token'));
});

it('does not allow a collaborator without access to share a note', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = new CreateVault()->handle($user1, [
        'name' => fake()->words(3, true),
    ]);
    $note = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'Hello world',
    ]);

    $this->actingAs($user2);

    $response = $this->post(
        route('vaults.nodes.share.store', ['vault' => $vault->id, 'node' => $note->id]),
    );

    $response->assertStatus(403);
    expect($note->fresh()->share)->toBeNull();
});

it('does not allow sharing a folder', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user);

    $response = $this->post(
        route('vaults.nodes.share.store', ['vault' => $vault->id, 'node' => $folder->id]),
    );

    $response->assertStatus(422);
});
