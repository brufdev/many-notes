<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetVaultNodeFromPath;
use App\Models\User;

it('resolves a note regardless of the case used in the path', function (): void {
    $getVaultNodeFromPath = app(GetVaultNodeFromPath::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'Target',
        'extension' => 'md',
        'content' => 'Hello',
    ]);

    expect($getVaultNodeFromPath->handle($vault, 'target.md')?->id)->toBe($note->id);
    expect($getVaultNodeFromPath->handle($vault, 'TARGET.MD')?->id)->toBe($note->id);
    expect($getVaultNodeFromPath->handle($vault, 'Target.md')?->id)->toBe($note->id);
});

it('resolves a folder regardless of the case used in the path', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = $createVaultNode->handle($vault, [
        'is_file' => false,
        'name' => 'Sub',
    ]);
    $note = $createVaultNode->handle($vault, [
        'is_file' => true,
        'parent_id' => $folder->id,
        'name' => 'note',
        'extension' => 'md',
        'content' => 'Hello',
    ]);

    expect(app(GetVaultNodeFromPath::class)->handle($vault, 'SUB/NOTE.md')?->id)->toBe($note->id);
});

it('does not treat underscores in a name as a wildcard', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $wildcardVictim = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'axb',
        'extension' => 'md',
        'content' => 'Wrong note',
    ]);

    // Under LIKE, the underscore matched any character and returned "axb" instead
    expect(app(GetVaultNodeFromPath::class)->handle($vault, 'a_b.md'))->toBeNull();
    expect($wildcardVictim->fresh())->not->toBeNull();
});

it('resolves a name that contains an underscore', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $note = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'a_b',
        'extension' => 'md',
        'content' => 'Right note',
    ]);
    $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'axb',
        'extension' => 'md',
        'content' => 'Wrong note',
    ]);

    expect(app(GetVaultNodeFromPath::class)->handle($vault, 'a_b.md')?->id)->toBe($note->id);
});

it('resolves dot segments, stopping at the vault root', function (string $path): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Folder']);
    $image = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'img',
        'extension' => 'png',
    ]);

    expect(app(GetVaultNodeFromPath::class)->handle($vault, $path)?->id)->toBe($image->id);
})->with([
    'parent folder' => 'Folder/../img.png',
    'above the root' => '../../img.png',
    'above the root from a folder' => 'Folder/../../../img.png',
    'absolute' => '/Folder/../img.png',
    'current folder and empty segments' => './/Folder/./../img.png',
]);

it('does not treat a percent sign in a name as a wildcard', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'discount',
        'extension' => 'md',
        'content' => 'Wrong note',
    ]);

    expect(app(GetVaultNodeFromPath::class)->handle($vault, '%.md'))->toBeNull();
});

it('does not resolve an unsupported file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    createUnsupportedNode($vault, 'Board.canvas');

    expect(app(GetVaultNodeFromPath::class)->handle($vault, 'Board.canvas'))->toBeNull();
});

it('does not resolve a path that ends in a folder', function (string $path): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Folder']);
    $createVaultNode->handle($vault, [
        'is_file' => true,
        'parent_id' => $folder->id,
        'name' => 'Folder',
        'extension' => 'md',
        'content' => 'Hello',
    ]);

    expect(app(GetVaultNodeFromPath::class)->handle($vault, $path))->toBeNull();
})->with(['trailing slash' => 'Folder/', 'current folder' => 'Folder/.', 'parent folder' => 'Folder/Folder/..']);

it('does not resolve a node of another vault', function (): void {
    $createVault = app(CreateVault::class);

    $user = User::factory()->create();
    $vault = $createVault->handle($user, ['name' => 'Notes']);
    $otherVault = $createVault->handle($user, ['name' => 'Other']);
    app(CreateVaultNode::class)->handle($otherVault, [
        'is_file' => true,
        'name' => 'img',
        'extension' => 'png',
    ]);
    $action = app(GetVaultNodeFromPath::class);

    expect($action->handle($vault, '../img.png'))->toBeNull();
    expect($action->handle($vault, '../Other/img.png'))->toBeNull();
});
