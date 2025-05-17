<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\ProcessVaultNodeTags;
use App\Livewire\Modals\SearchNode;
use App\Models\User;
use Livewire\Livewire;

it('opens the modal', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(SearchNode::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open')
        ->assertSet('show', true);
});

it('searches for a node by tag', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'First note',
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $secondNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'Second note',
        'extension' => 'md',
        'content' => fake()->paragraph() . ' #test',
    ]);
    new ProcessVaultNodeTags()->handle($secondNode);

    Livewire::actingAs($user)
        ->test(SearchNode::class, ['vault' => $vault])
        ->call('open')
        ->set('search', 'tag:test')
        ->assertCount('nodes', 1);
});
