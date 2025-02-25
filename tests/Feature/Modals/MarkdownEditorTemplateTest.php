<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Livewire\Modals\MarkdownEditorTemplate;
use App\Models\User;

it('opens the modal', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(MarkdownEditorTemplate::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open')
        ->assertSet('show', true);
});

it('inserts a template', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $templateFolderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $templateNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'parent_id' => $templateFolderNode->id,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'content: {{content}}',
    ]);
    $content = fake()->paragraph();
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => $content,
    ]);
    $vault->update(['templates_node_id' => $templateFolderNode->id]);

    Livewire::actingAs($user)
        ->test(MarkdownEditorTemplate::class, ['vault' => $vault])
        ->call('open', $node)
        ->call('insertTemplate', $templateNode);

    expect($node->refresh()->content)->toBe('content: ' . $content);
});

it('does not insert a template from a non-template node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $firstNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'content: {{content}}',
    ]);
    $secondNodeContent = fake()->paragraph();
    $secondNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => $secondNodeContent,
    ]);

    Livewire::actingAs($user)
        ->test(MarkdownEditorTemplate::class, ['vault' => $vault])
        ->call('open', $secondNode)
        ->call('insertTemplate', $firstNode);

    expect($secondNode->refresh()->content)->toBe($secondNodeContent);
});

it('inserts a template without {{content}} variable', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $templateFolderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $templateNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'parent_id' => $templateFolderNode->id,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'Daily note',
    ]);
    $nodeContent = fake()->paragraph();
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => $nodeContent,
    ]);
    $vault->update(['templates_node_id' => $templateFolderNode->id]);

    Livewire::actingAs($user)
        ->test(MarkdownEditorTemplate::class, ['vault' => $vault])
        ->call('open', $node)
        ->call('insertTemplate', $templateNode);

    expect($node->refresh()->content)->toBe('Daily note' . PHP_EOL . $nodeContent);
});
