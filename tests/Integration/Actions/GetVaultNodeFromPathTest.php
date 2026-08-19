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
