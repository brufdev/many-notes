<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('links to nodes through dot segments, stopping at the vault root', function (string $link): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $folder = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'Folder']);
    $image = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'img',
        'extension' => 'png',
    ]);
    $note = $createVaultNode->handle($vault, [
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
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $target = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'C++',
        'extension' => 'md',
        'content' => 'Hello',
    ]);
    $note = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => '[C++](C++.md)',
    ]);

    expect($note->links()->pluck('vault_nodes.id')->all())->toBe([$target->id]);
});

it('links to a note with parentheses in its name', function (string $link): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $target = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'Meeting (2024)',
        'extension' => 'md',
    ]);
    $note = $createVaultNode->handle($vault, [
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

it('does not link from code', function (string $content): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'target', 'extension' => 'md']);
    $note = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => $content,
    ]);

    expect($note->links()->count())->toBe(0);
})->with([
    'inline code' => 'See `[target](target.md)` here.',
    'double backticks' => 'See ``a ` [target](target.md)`` here.',
    'backtick fence' => "```php\n[target](target.md)\n```",
    'tilde fence' => "~~~\n[target](target.md)\n~~~",
    'longer closing fence' => "```\n[target](target.md)\n`````",
    'a shorter fence inside a longer one' => "````\n```\n[target](target.md)\n````",
    'unclosed fence' => "Intro\n\n```\n[target](target.md)",
    'fence with parentheses' => "```\nf(x): [target](target.md)\n```",
]);

it('links to a note after code', function (string $content): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $target = $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'target', 'extension' => 'md']);
    $note = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => $content,
    ]);

    expect($note->links()->pluck('vault_nodes.id')->all())->toBe([$target->id]);
})->with([
    'inline code' => 'Café `code` [target](target.md)',
    'a fence' => "```\ncode\n```\n[target](target.md)",
    'a tilde line inside a backtick fence' => "```\n~~~\n```\n[target](target.md)",
    'a single backtick' => 'It`s [target](target.md)',
    'a backtick before a blank line' => "`open\n\n[target](target.md) `",
    'triple backticks on one line' => '```code``` [target](target.md)',
]);

it('does not link to a node of another vault', function (): void {
    $createVault = app(CreateVault::class);
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = $createVault->handle($user, ['name' => 'Notes']);
    $otherVault = $createVault->handle($user, ['name' => 'Other']);
    $createVaultNode->handle($otherVault, [
        'is_file' => true,
        'name' => 'img',
        'extension' => 'png',
    ]);
    $note = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'content' => '![a](../img.png) ![b](../Other/img.png) ![c](%2e%2e/Other/img.png)',
    ]);

    expect($note->links()->count())->toBe(0);
});
