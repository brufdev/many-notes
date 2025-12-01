<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('exports a vault', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'parent_id' => $folderNode->id,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertStatus(200);
    expect($response->headers->get('Content-Disposition'))->toBe('attachment; filename="' . $vault->name . '.zip"');
    expect($response->streamedContent())->toBeString();
});

it('fails exporting a vault with no permissions', function (): void {
    $firstUser = User::factory()->hasVaults(1)->create();
    $secondUser = User::factory()->create();
    $vault = $firstUser->vaults()->first();
    $vault->nodes()->create([
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($secondUser);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertStatus(403);
});

it('fails exporting an empty vault', function (): void {
    $user = User::factory()->hasVaults(1)->create();
    $vault = $user->vaults()->first();

    $this->actingAs($user);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertStatus(422);
});

it('fails exporting a vault with files missing on disk', function (): void {
    $user = User::factory()->hasVaults(1)->create();
    $vault = $user->vaults()->first();
    $vault->nodes()->create([
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertStatus(500);
});
