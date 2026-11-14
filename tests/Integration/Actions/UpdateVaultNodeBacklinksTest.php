<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\UpdateVaultNode;
use App\Models\User;

it('updates every backlink on a line when the target note is renamed', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $target = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'target',
        'extension' => 'md',
        'content' => 'Target note',
    ]);
    $source = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => '[one](/target.md "first") and [two](/target.md "second")',
    ]);

    app(UpdateVaultNode::class)->handle($target, ['name' => 'renamed']);

    expect($source->fresh()->content)->toBe('[one](/renamed.md "first") and [two](/renamed.md "second")');
});

it('updates a backlink written with different casing when the target is renamed', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $target = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'target',
        'extension' => 'md',
        'content' => 'Target note',
    ]);
    $source = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => 'See [x](/Target.MD) here.',
    ]);

    app(UpdateVaultNode::class)->handle($target, ['name' => 'renamed']);

    expect($source->fresh()->content)->toBe('See [x](/renamed.md) here.');
});

it('updates backlinks when a folder is moved', function (?string $destination, string $expected): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $oldParent = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Old']);
    $folder = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Folder', 'parent_id' => $oldParent->id]);
    $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'parent_id' => $folder->id,
    ]);
    $newParent = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'New']);
    $nestedParent = $createVaultNode->handle($vault, [
        'is_file' => false,
        'name' => 'Nested',
        'parent_id' => $newParent->id,
    ]);
    $destinations = ['New' => $newParent, 'Nested' => $nestedParent];
    $source = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => '[n](/Old/Folder/note.md)',
    ]);

    app(UpdateVaultNode::class)->handle($folder, [
        'parent_id' => $destination === null ? null : $destinations[$destination]->id,
    ]);

    expect($source->fresh()->content)->toBe($expected);
})->with([
    'same depth' => ['New', '[n](/New/Folder/note.md)'],
    'deeper' => ['Nested', '[n](/New/Nested/Folder/note.md)'],
    'to the root' => [null, '[n](/Folder/note.md)'],
]);

it('keeps a dollar sign of the new name in backlinks', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $target = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'target', 'extension' => 'md']);
    $source = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => '[x](/target.md "Title")',
    ]);

    app(UpdateVaultNode::class)->handle($target, ['name' => 'Budget $2026']);

    expect($source->fresh()->content)->toBe('[x](/Budget $2026.md "Title")');
});
