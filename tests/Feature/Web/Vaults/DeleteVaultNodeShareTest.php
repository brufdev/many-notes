<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\CreateVaultNodeShare;
use App\Models\User;

it('revokes a share link', function (): void {
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
    $share = new CreateVaultNodeShare()->handle($note);

    $this->actingAs($user);

    $response = $this->delete(
        route('vaults.nodes.share.destroy', ['vault' => $vault->id, 'node' => $note->id]),
    );

    $response->assertStatus(200);
    expect($note->fresh()->share)->toBeNull();

    $this->get(route('share.show', ['share' => $share->token]))->assertStatus(404);
});

it('does not allow a user without access to revoke a share link', function (): void {
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
    new CreateVaultNodeShare()->handle($note);

    $this->actingAs($user2);

    $response = $this->delete(
        route('vaults.nodes.share.destroy', ['vault' => $vault->id, 'node' => $note->id]),
    );

    $response->assertStatus(403);
    expect($note->fresh()->share)->not->toBeNull();
});
