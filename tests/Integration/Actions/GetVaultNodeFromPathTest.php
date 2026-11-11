<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetVaultNodeFromPath;
use App\Models\User;

it('resolves a note regardless of the case used in the path', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'Target',
        'extension' => 'md',
        'content' => 'Hello',
    ]);
    $action = app(GetVaultNodeFromPath::class);

    expect($action->handle($vault->id, 'target.md')?->id)->toBe($note->id)
        ->and($action->handle($vault->id, 'TARGET.MD')?->id)->toBe($note->id)
        ->and($action->handle($vault->id, 'Target.md')?->id)->toBe($note->id);
});

it('resolves a folder regardless of the case used in the path', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => 'Sub',
    ]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'parent_id' => $folder->id,
        'name' => 'note',
        'extension' => 'md',
        'content' => 'Hello',
    ]);
    $action = app(GetVaultNodeFromPath::class);

    expect($action->handle($vault->id, 'SUB/NOTE.md')?->id)->toBe($note->id);
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
    $action = app(GetVaultNodeFromPath::class);

    // Under LIKE, the underscore matched any character and returned "axb" instead
    expect($action->handle($vault->id, 'a_b.md'))->toBeNull();
    expect($wildcardVictim->fresh())->not->toBeNull();
});

it('resolves a name that contains an underscore', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'a_b',
        'extension' => 'md',
        'content' => 'Right note',
    ]);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'axb',
        'extension' => 'md',
        'content' => 'Wrong note',
    ]);
    $action = app(GetVaultNodeFromPath::class);

    expect($action->handle($vault->id, 'a_b.md')?->id)->toBe($note->id);
});

it('resolves dot segments, stopping at the vault root', function (string $path): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Folder']);
    $image = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'img',
        'extension' => 'png',
    ]);

    expect(app(GetVaultNodeFromPath::class)->handle($vault->id, $path)?->id)->toBe($image->id);
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
    $action = app(GetVaultNodeFromPath::class);

    expect($action->handle($vault->id, '%.md'))->toBeNull();
});

it('does not resolve an unsupported file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    createUnsupportedNode($vault, 'Board.canvas');

    expect(app(GetVaultNodeFromPath::class)->handle($vault->id, 'Board.canvas'))->toBeNull();
});

it('does not resolve a path that ends in a folder', function (string $path): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Folder']);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'parent_id' => $folder->id,
        'name' => 'Folder',
        'extension' => 'md',
        'content' => 'Hello',
    ]);

    expect(app(GetVaultNodeFromPath::class)->handle($vault->id, $path))->toBeNull();
})->with(['trailing slash' => 'Folder/', 'current folder' => 'Folder/.', 'parent folder' => 'Folder/Folder/..']);

it('does not resolve a node of another vault', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $otherVault = app(CreateVault::class)->handle($user, ['name' => 'Other']);
    app(CreateVaultNode::class)->handle($otherVault, [
        'is_file' => true,
        'name' => 'img',
        'extension' => 'png',
    ]);
    $action = app(GetVaultNodeFromPath::class);

    expect($action->handle($vault->id, '../img.png'))->toBeNull()
        ->and($action->handle($vault->id, '../Other/img.png'))->toBeNull();
});
