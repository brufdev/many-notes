<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('shows the file to a user', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $imageNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);
    $imagePath = new GetPathFromVaultNode()->handle($imageNode);
    Storage::disk('local')->put($imagePath, '');
    $imageUrl = new GetUrlFromVaultNode()->handle($imageNode);

    $this->actingAs($user)
        ->get($imageUrl)
        ->assertStatus(200);
});

it('returns a 404 error if the path is not provided', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user)
        ->get('/files/' . $vault->id)
        ->assertStatus(404);
});

it('shows the file, resolving the path from another file', function (): void {
    $user = User::factory()->create();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $imageNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);
    $textNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $imagePath = new GetPathFromVaultNode()->handle($imageNode);
    Storage::disk('local')->put($imagePath, '');
    $imageUrl = new GetUrlFromVaultNode()->handle($imageNode);

    $this->actingAs($user)
        ->get($imageUrl . '&node=' . $textNode->id)
        ->assertStatus(200);
});

it('return a 404 error if the giving node is from another vault', function (): void {
    $user = User::factory()->create();
    $firstVault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $secondVault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $imageNode = new CreateVaultNode()->handle($firstVault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);
    $textNode = new CreateVaultNode()->handle($secondVault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $imagePath = new GetPathFromVaultNode()->handle($imageNode);
    Storage::disk('local')->put($imagePath, '');
    $imageUrl = new GetUrlFromVaultNode()->handle($imageNode);

    $this->actingAs($user)
        ->get($imageUrl . '&node=' . $textNode->id)
        ->assertStatus(404);
});
