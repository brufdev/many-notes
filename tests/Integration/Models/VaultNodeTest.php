<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\ProcessVaultNodeTags;
use App\Models\Tag;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;

it('has valid schema', function (): void {
    $node = VaultNode::factory()->create()->refresh();

    expect(array_keys($node->toArray()))
        ->toBe([
            'id',
            'vault_id',
            'parent_id',
            'is_file',
            'name',
            'extension',
            'content',
            'created_at',
            'updated_at',
        ]);
});

it('belongs to a vault', function (): void {
    $node = VaultNode::factory()->create();

    expect($node->vault)->toBeInstanceOf(Vault::class);
});

it('may have childs', function (): void {
    $node = VaultNode::factory()->hasChilds(3)->create();

    expect($node->childs)->toHaveCount(3)
        ->each->toBeInstanceOf(VaultNode::class);
});

it('may have links', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'name' => 'folder name',
        'is_file' => false,
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => 'file',
        'extension' => 'md',
    ]);
    $rootFile = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file',
        'extension' => 'md',
        'content' => "Link: [file](/$folder->name/$file->name.md).",
    ]);

    expect($rootFile->links()->get())->toHaveCount(1)
        ->each->toBeInstanceOf(VaultNode::class);
    expect($rootFile->backlinks()->get())->toHaveCount(0)
        ->each->toBeInstanceOf(VaultNode::class);
    expect($file->backlinks()->get())->toHaveCount(1)
        ->each->toBeInstanceOf(VaultNode::class);
    expect($file->links()->get())->toHaveCount(0)
        ->each->toBeInstanceOf(VaultNode::class);
});

it('may have tags', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $firstNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $secondNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph() . ' #test',
    ]);
    new ProcessVaultNodeTags()->handle($secondNode);

    expect($firstNode->tags()->count())->toBe(0);
    expect($secondNode->tags()->get())->toHaveCount(1)
        ->each->toBeInstanceOf(Tag::class);
});
