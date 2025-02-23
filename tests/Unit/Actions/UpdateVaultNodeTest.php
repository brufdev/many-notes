<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVaultNode;
use App\Actions\UpdateVaultNode;
use App\Models\User;

it('moves a file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $fileNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    expect($fileNode->parent_id)->toBeNull();

    new UpdateVaultNode()->handle($fileNode, ['parent_id' => $folderNode->id]);

    $path = new GetPathFromVaultNode()->handle($fileNode);
    expect($fileNode->parent_id)->toBe($folderNode->id);
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('moves a folder', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $firstFolderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $secondFolderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $fileNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'parent_id' => $secondFolderNode->id,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    expect($secondFolderNode->parent_id)->toBeNull();

    new UpdateVaultNode()->handle($secondFolderNode, ['parent_id' => $firstFolderNode->id]);

    $path = new GetPathFromVaultNode()->handle($secondFolderNode);
    expect($secondFolderNode->parent_id)->toBe($firstFolderNode->id);
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});
