<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;

it('suffixes a new node whose name differs from an existing one only by case', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'aa',
        'extension' => 'md',
        'content' => 'First',
    ]);
    $second = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'Aa',
        'extension' => 'md',
        'content' => 'Second',
    ]);

    expect($second->name)->toBe('Aa-1');
});

it('keeps suffixing new nodes that collide with an existing one', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);

    $names = collect(range(1, 3))
        ->map(
            fn(): string => $createVaultNode->handle($vault, [
                'is_file' => true,
                'name' => 'note',
                'extension' => 'md',
            ])->name,
        )
        ->all();

    expect($names)->toBe(['note', 'note-1', 'note-2']);
});

it('does not suffix a new node when only its type, extension or folder matches', function (): void {
    $createVaultNode = app(CreateVaultNode::class);

    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);

    $folder = $createVaultNode->handle($vault, ['is_file' => false, 'name' => 'note']);
    $note = $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'note', 'extension' => 'md']);
    $image = $createVaultNode->handle($vault, ['is_file' => true, 'name' => 'note', 'extension' => 'png']);
    $nested = $createVaultNode->handle($vault, [
        'is_file' => true,
        'name' => 'note',
        'extension' => 'md',
        'parent_id' => $folder->id,
    ]);

    expect([$folder->name, $note->name, $image->name, $nested->name])
        ->toBe(['note', 'note', 'note', 'note']);
});
