<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Livewire\Modals\MarkdownEditorSearch;
use App\Models\User;

it('opens the modal', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(MarkdownEditorSearch::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open')
        ->assertSet('show', true);
});

it('searches for an image file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $firstImageNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'First image',
        'extension' => 'jpg',
    ]);
    $secondImageNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'Second image',
        'extension' => 'jpg',
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'First note',
        'extension' => 'md',
    ]);

    Livewire::actingAs($user)
        ->test(MarkdownEditorSearch::class, ['vault' => $vault])
        ->call('open', 'image')
        ->assertCount('nodes', 2)
        ->set('search', 'first')
        ->assertCount('nodes', 1);
});
