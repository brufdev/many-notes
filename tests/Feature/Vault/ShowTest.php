<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVaultNode;
use App\Actions\GetUrlFromVaultNode;
use App\Actions\ProcessVaultNodeTags;
use App\Actions\UpdateVaultNode;
use App\Events\VaultFileSystemUpdatedEvent;
use App\Events\VaultNodeUpdatedEvent;
use App\Livewire\Vault\Show;
use App\Models\User;
use Illuminate\Support\Facades\Event;
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->assertSet('nodeForm.name', $node->name);
});

it('does not open a non-existing file', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => 500])
        ->test(Show::class, ['vaultId' => $vault->id])
        ->assertDispatched('toast', type: 'error');
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->call('openFileId', $node->id)
        ->assertDispatched('toast', type: 'error');
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->call('openFilePath', $node->name)
        ->assertSet('selectedFileId', $node->id);
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->call('openFilePath', $secondNode->name)
        ->assertSet('selectedFileId', $secondNode->id);
});

it('does not open a file from a non-existent path', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(Show::class, ['vaultId' => $vault->id])
        ->call('openFilePath', fake()->words(4, true))
        ->assertDispatched('toast', type: 'error');
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
    $url = new GetUrlFromVaultNode()->handle($node);
    $name = $node->name;
    $newName = fake()->words(4, true);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $node->id])
        ->test(Show::class, ['vaultId' => $vault->id])
        ->assertSet('selectedFileUrl', $url)
        ->set('nodeForm.name', $newName)
        ->call('refreshFile', $node->refresh()->id)
        ->assertSet('selectedFileUrl', str_replace($name, $newName, $url));
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->call('refreshFile', $secondNode->id)
        ->assertSet('selectedFileId', $firstNode->id);
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->assertSet('selectedFileId', $node->id)
        ->call('closeFile')
        ->assertSet('selectedFileId', null);
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
        ->test(Show::class, ['vaultId' => $vault->id])
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->assertSet('vault.templates_node_id', null)
        ->call('setTemplateFolder', $node)
        ->assertSet('vault.templates_node_id', null);
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->set('nodeForm.content', $newContent);
    expect($vault->nodes()->first()->content)->toBe($newContent);

    $path = new GetPathFromVaultNode()->handle($node);
    expect(Storage::disk('local')->get($path))->toBe($newContent);
});

it('process the links when updating the title of a node', function (): void {
    Event::fake();

    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => 'folder 1',
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => 'file',
        'extension' => 'md',
    ]);
    $rootFile1 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 1',
        'extension' => 'md',
        'content' => "Link: [file](/$folder->name/$file->name.md).",
    ]);
    $rootFile2 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 2',
        'extension' => 'md',
        'content' => "Link: [file](/$folder->name/$file->name.md).",
    ]);

    $newFileName = 'new file name';
    Livewire::actingAs($user)
        ->withQueryParams(['file' => $file->id])
        ->test(Show::class, ['vaultId' => $vault->id])
        ->set('nodeForm.name', $newFileName);

    expect($file->refresh()->name)->toBe($newFileName);
    $expectedContent = "Link: [file](/$folder->name/$newFileName.md).";
    expect($rootFile1->refresh()->content)->toBe($expectedContent);
    expect($rootFile2->refresh()->content)->toBe($expectedContent);

    Event::assertDispatched(VaultFileSystemUpdatedEvent::class, 1);
    Event::assertDispatched(VaultNodeUpdatedEvent::class, 3);
});

it('process the links when updating the content of a node', function (): void {
    Event::fake();

    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $file1 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'file 1',
        'extension' => 'md',
    ]);
    $file2 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'file 2',
        'extension' => 'md',
    ]);
    expect($file1->links()->count())->toBe(0);

    Livewire::actingAs($user)
        ->withQueryParams(['file' => $file1->id])
        ->test(Show::class, ['vaultId' => $vault->id])
        ->set('nodeForm.content', "[link](/$file2->name.md)");

    expect($file1->links()->count())->toBe(1);
    expect($file1->links()->first()->is($file2))->toBeTrue();

    Event::assertDispatched(VaultFileSystemUpdatedEvent::class, 0);
    Event::assertDispatched(VaultNodeUpdatedEvent::class, 1);
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->set('nodeForm.content', $content);

    expect($node->tags->count())->toBe(2);
    expect($node->tags->get(0)->name)->toBe('tag1');
    expect($node->tags->get(1)->name)->toBe('tag2');
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->call('deleteNode', $folderNode)
        ->assertDispatched('toast');
    expect($vault->nodes()->count())->toBe(0);

    $path = new GetPathFromVaultNode()->handle($folderNode);
    expect(Storage::disk('local')->path($path))->not->toBeDirectory();
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->assertSet('selectedFileId', $node->id)
        ->call('deleteNode', $node)
        ->assertSet('selectedFileId', null);
});

it('deletes the links and backlinks when deleting a node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $file1 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'file 1',
        'extension' => 'md',
    ]);
    $file2 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'file 2',
        'extension' => 'md',
        'content' => "[link](/$file1->name.md)",
    ]);
    new UpdateVaultNode()->handle($file1, [
        'content' => "[link](/$file2->name.md)",
    ]);
    expect($file1->links()->count())->toBe(1);
    expect($file2->links()->count())->toBe(1);

    Livewire::actingAs($user)
        ->test(Show::class, ['vaultId' => $vault->id])
        ->call('deleteNode', $file1)
        ->assertDispatched('toast');

    expect($file1->links()->count())->toBe(0);
    expect($file2->links()->count())->toBe(0);
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
        ->test(Show::class, ['vaultId' => $vault->id])
        ->call('deleteNode', $node)
        ->assertDispatched('toast');

    expect($node->refresh()->tags()->count())->toBe(0);
});
