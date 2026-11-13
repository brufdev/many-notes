<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('links to nodes through dot segments, stopping at the vault root', function (string $link): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Folder']);
    $image = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'img',
        'extension' => 'png',
    ]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'parent_id' => $folder->id,
        'name' => 'note',
        'extension' => 'md',
        'content' => "![image]({$link})",
    ]);

    expect($note->links()->pluck('vault_nodes.id')->all())->toBe([$image->id]);
})->with([
    'parent folder' => '../img.png',
    'encoded dots' => '%2e%2e/img.png',
    'encoded slash' => '..%2fimg.png',
    'above the root' => '../../../../img.png',
    'encoded above the root' => '%2e%2e/%2e%2e/%2e%2e/img.png',
    'absolute' => '/Folder/../img.png',
]);

it('links to a note with plus signs in its name', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $target = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'C++',
        'extension' => 'md',
        'content' => 'Hello',
    ]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => '[C++](C++.md)',
    ]);

    expect($note->links()->pluck('vault_nodes.id')->all())->toBe([$target->id]);
});

it('links to a note with parentheses in its name', function (string $link): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $target = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'Meeting (2024)',
        'extension' => 'md',
    ]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => "[Meeting]({$link}) (see above)",
    ]);

    expect($note->links()->pluck('vault_nodes.id')->all())->toBe([$target->id]);
})->with([
    'encoded spaces' => 'Meeting%20(2024).md',
    'spaces' => 'Meeting (2024).md',
    'title' => 'Meeting%20(2024).md "Minutes"',
]);

it('does not link to a node of another vault', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $otherVault = app(CreateVault::class)->handle($user, ['name' => 'Other']);
    app(CreateVaultNode::class)->handle($otherVault, [
        'is_file' => true,
        'name' => 'img',
        'extension' => 'png',
    ]);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => '![a](../img.png) ![b](../Other/img.png) ![c](%2e%2e/Other/img.png)',
    ]);

    expect($note->links()->count())->toBe(0);
});
