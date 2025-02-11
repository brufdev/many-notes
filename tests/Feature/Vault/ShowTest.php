<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromUser;
use App\Actions\GetPathFromVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Actions\ProcessVaultNodeLinks;
use App\Actions\ProcessVaultNodeTags;
use App\Livewire\Vault\Show;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('opens a file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('nodeForm.node.name', $node->name);
});

it('does not open a non-existing file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => 500])
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('selectedFile', null);
});

it('does not open a folder', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->call('openFile', $node)
        ->assertSet('selectedFile', null);
});

it('resets edit mode when opening a that is not a note', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->call('openFile', $node)
        ->assertSet('isEditMode', true);
});

it('opens a file from the path', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->call('openFilePath', $node->name)
        ->assertSet('selectedFile', $node->id);
});

it('opens a file from the path with an open file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $firstNode = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folderNode->id,
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $secondNode = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folderNode->id,
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $firstNode->id])
        ->test(Show::class, ['vault' => $vault])
        ->call('openFilePath', $secondNode->name)
        ->assertSet('selectedFile', $secondNode->id);
});

it('does not open a file from a non-existent path', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->call('openFilePath', fake()->words(4, true))
        ->assertSet('selectedFile', null);
});

it('refreshes an open file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $relativeUrl = new GetUrlFromVaultNode()->handle($node);
    $name = $node->name;
    $newName = fake()->words(4, true);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('selectedFileUrl', $relativeUrl)
        ->set('nodeForm.name', $newName)
        ->call('refreshFile', $node->refresh())
        ->assertSet('selectedFileUrl', str_replace($name, $newName, $relativeUrl));
});

it('does not refresh a file that is not open', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $firstNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $secondNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $firstNode->id])
        ->test(Show::class, ['vault' => $vault])
        ->call('refreshFile', $secondNode)
        ->assertSet('selectedFile', $firstNode->id);
});

it('closes an open file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('selectedFile', $node->id)
        ->call('closeFile')
        ->assertSet('selectedFile', null);
});

it('sets the template folder', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('vault.templates_node_id', null)
        ->call('setTemplateFolder', $node)
        ->assertSet('vault.templates_node_id', $node->id);
});

it('does not set the template folder if it is a file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('vault.templates_node_id', null)
        ->call('setTemplateFolder', $node)
        ->assertSet('vault.templates_node_id', null);
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
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('nodeForm.content', $node->content)
        ->call('setTemplateFolder', $templateFolderNode)
        ->call('insertTemplate', $templateNode)
        ->assertSet('nodeForm.content', 'content: ' . $node->content);
});

it('does not insert a template from a non-template node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $firstNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'parent_id' => $folderNode->id,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => 'content: {{content}}',
    ]);
    $secondNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $secondNode->id])
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('nodeForm.content', $secondNode->content)
        ->call('insertTemplate', $firstNode)
        ->assertSet('nodeForm.content', $secondNode->content);
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
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('nodeForm.content', $node->content)
        ->call('setTemplateFolder', $templateFolderNode)
        ->call('insertTemplate', $templateNode)
        ->assertSet('nodeForm.content', 'Daily note' . PHP_EOL . $node->content);
});

it('updates the node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    $newContent = fake()->paragraph();

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vault' => $vault])
        ->set('nodeForm.content', $newContent);
    expect($vault->nodes()->first()->content)->toBe($newContent);
});

it('process the links when updating a node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $firstNodeName = fake()->words(3, true);
    $firstNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => $firstNodeName,
        'extension' => 'md',
    ]);
    $secondNodeName = fake()->words(3, true);
    $secondNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => $secondNodeName,
        'extension' => 'md',
    ]);
    $content = '[link](/' . $secondNodeName . '.md)';
    expect($firstNode->links()->count())->toBe(0);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $firstNode->id])
        ->test(Show::class, ['vault' => $vault])
        ->set('nodeForm.content', $content);

    expect($firstNode->links()->count())->toBe(1);
    expect($firstNode->links()->first()->is($secondNode))->toBeTrue();
});

it('process the tags when updating a node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);
    $content = '#tag1 ' . fake()->paragraph() . ' #tag2';
    expect($node->tags()->count())->toBe(0);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vault' => $vault])
        ->set('nodeForm.content', $content);

    expect($node->tags->count())->toBe(2);
    expect($node->tags->get(0)->name)->toBe('#tag1');
    expect($node->tags->get(1)->name)->toBe('#tag2');
});

it('updates the vault', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(3, true);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->set('vaultForm.name', $newName)
        ->call('editVault');
    expect($user->vaults()->first()->name)->toBe($newName);

    $relativePath = new GetPathFromUser()->handle($user);
    $absolutePath = Storage::disk('local')->path($relativePath . $newName);
    expect($absolutePath)->toBeDirectory();
});

it('deletes a node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folderNode = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'parent_id' => $folderNode->id,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);
    expect($vault->nodes()->count())->toBe(2);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->call('deleteNode', $folderNode)
        ->assertDispatched('toast');
    expect($vault->nodes()->count())->toBe(0);

    $relativePath = new GetPathFromVaultNode()->handle($folderNode);
    $absolutePath = Storage::disk('local')->path($relativePath);
    expect($absolutePath)->not->toBeDirectory();
});

it('closes an open file when it is deleted', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => fake()->paragraph(),
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vault' => $vault])
        ->assertSet('selectedFile', $node->id)
        ->call('deleteNode', $node)
        ->assertSet('selectedFile', null);
});

it('deletes the links and backlinks when deleting a node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $firstNodeName = fake()->words(3, true);
    $secondNodeName = fake()->words(3, true);
    $firstNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => $firstNodeName,
        'extension' => 'md',
        'content' => '[link](/' . $secondNodeName . '.md)',
    ]);
    $secondNode = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => $secondNodeName,
        'extension' => 'md',
        'content' => '[link](/' . $firstNodeName . '.md)',
    ]);
    new ProcessVaultNodeLinks()->handle($firstNode);
    new ProcessVaultNodeLinks()->handle($secondNode);
    expect($firstNode->links()->count())->toBe(1);
    expect($secondNode->links()->count())->toBe(1);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->call('deleteNode', $firstNode)
        ->assertDispatched('toast');
    expect($firstNode->links()->count())->toBe(0);
    expect($secondNode->links()->count())->toBe(0);
});

it('deletes the tags when deleting a node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
        'content' => '#tag1 ' . fake()->paragraph() . ' #tag2',
    ]);
    new ProcessVaultNodeTags()->handle($node);
    expect($node->tags->count())->toBe(2);

    Livewire::actingAs($user)
        ->test(Show::class, ['vault' => $vault])
        ->call('deleteNode', $node)
        ->assertDispatched('toast');

    expect($node->refresh()->tags()->count())->toBe(0);
});
