<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the opened file when a file is given', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $node = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('vaults.show', ['vault' => $vault->id, 'file' => $node->id]));

    $response->assertInertia(
        fn(Assert $page): Assert => $page->where('openedFile.file.id', $node->id),
    );
});

it('sends a null opened file when no file is given', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);

    $response = $this
        ->actingAs($user)
        ->get(route('vaults.show', ['vault' => $vault->id]));

    $response->assertInertia(
        fn(Assert $page): Assert => $page->where('openedFile', null),
    );
});
