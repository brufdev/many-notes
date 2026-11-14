<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\UpdateVaultNode;
use App\Models\User;

it('updates every backlink in a note', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $target = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'target', 'extension' => 'md']);
    $source = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => 'Café 👋 [one](target.md "first") ñ [two](target.md "second") [other](other.md)',
    ]);

    app(UpdateVaultNode::class)->handle($target, ['name' => 'renamed']);

    expect($source->fresh()->content)
        ->toBe('Café 👋 [one](/renamed.md "first") ñ [two](/renamed.md "second") [other](other.md)');
});

it('updates a backlink however its path is written', function (string $link): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Folder']);
    $target = $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'my target', 'extension' => 'md']);
    $source = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'parent_id' => $folder->id,
        'content' => "See [target]({$link}) here.",
    ]);

    app(UpdateVaultNode::class)->handle($target, ['name' => 'renamed']);

    expect($source->fresh()->content)->toBe('See [target](/renamed.md) here.');
})->with([
    'relative' => '../my target.md',
    'encoded' => '../my%20target.md',
    'encoded absolute' => '/my%20target.md',
    'without extension' => '/my target',
    'different casing' => '/My Target.MD',
]);

it('updates an image backlink when the image is renamed', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $image = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'img', 'extension' => 'png']);
    $source = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => '![alt](img.png)',
    ]);

    app(UpdateVaultNode::class)->handle($image, ['name' => 'photo']);

    expect($source->fresh()->content)->toBe('![alt](/photo.png)');
});

it('updates backlinks to every note of a moved folder', function (?string $destination, string $path): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $oldParent = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Old']);
    $folder = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Folder', 'parent_id' => $oldParent->id]);
    foreach (['one', 'two'] as $name) {
        $createVaultNode->handle($vault, [
            'is_file' => true,
            'name' => $name,
            'extension' => 'md',
            'parent_id' => $folder->id,
        ]);
    }
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
        'content' => '[one](/Old/Folder/one.md) [two](/Old/Folder/two.md)',
    ]);

    app(UpdateVaultNode::class)->handle($folder, [
        'parent_id' => $destination === null ? null : $destinations[$destination]->id,
    ]);

    expect($source->fresh()->content)->toBe("[one]({$path}/one.md) [two]({$path}/two.md)");
})->with([
    'same depth' => ['New', '/New/Folder'],
    'deeper' => ['Nested', '/New/Nested/Folder'],
    'to the root' => [null, '/Folder'],
]);

it('updates the links of a moved note that no longer resolve', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $newParent = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'New']);
    $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'other', 'extension' => 'md']);
    $note = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => '[relative](other.md) [absolute](/other.md)',
    ]);

    $note = app(UpdateVaultNode::class)->handle($note, ['parent_id' => $newParent->id]);

    expect($note->content)->toBe('[relative](/other.md) [absolute](/other.md)');
});

it('keeps the links between notes of a moved folder', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Folder']);
    $newParent = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'New']);
    $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'other', 'extension' => 'md']);
    $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'two',
        'extension' => 'md',
        'parent_id' => $folder->id,
    ]);
    $one = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'one',
        'extension' => 'md',
        'parent_id' => $folder->id,
        'content' => '[two](two.md) [other](../other.md)',
    ]);

    app(UpdateVaultNode::class)->handle($folder, ['parent_id' => $newParent->id]);

    expect($one->fresh()->content)->toBe('[two](two.md) [other](/other.md)');
    expect($one->links()->pluck('name')->sort()->values()->all())->toBe(['other', 'two']);
});

it('keeps a dollar sign of the new name in backlinks', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $target = $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'target', 'extension' => 'md']);
    $source = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'source',
        'extension' => 'md',
        'content' => '[target](/target.md "Title")',
    ]);

    app(UpdateVaultNode::class)->handle($target, ['name' => 'Budget $2026']);

    expect($source->fresh()->content)->toBe('[target](/Budget $2026.md "Title")');
});
