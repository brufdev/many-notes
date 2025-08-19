<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromUser;
use App\Events\VaultFileSystemUpdatedEvent;
use App\Events\VaultNodeUpdatedEvent;
use App\Livewire\Vault\TreeView;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('renders the tree view', function (): void {
    $user = User::factory()->hasVaults(1)->create()->first();
    $vault = $user->vaults()->first();

    Livewire::actingAs($user)
        ->test(TreeView::class, ['vault' => $vault])
        ->assertSee('Your vault is empty.');
});

it('updates the vault', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(3, true);

    Livewire::actingAs($user)
        ->test(TreeView::class, ['vault' => $vault])
        ->set('vaultForm.name', $newName)
        ->call('editVault');
    expect($user->vaults()->first()->name)->toBe($newName);

    $path = new GetPathFromUser()->handle($user) . $newName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('update note links when moving a node', function (): void {
    Event::fake();

    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folder1 = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => 'folder 1',
    ]);
    $folder2 = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder1->id,
        'is_file' => false,
        'name' => 'folder 2',
    ]);
    $file = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder1->id,
        'is_file' => true,
        'name' => 'file',
        'extension' => 'md',
    ]);
    $rootFile1 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 1',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$file->name.md \"label\").",
    ]);
    $rootFile2 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 2',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$file->name.md \"\").",
    ]);

    Livewire::actingAs($user)
        ->test(TreeView::class, ['vault' => $vault])
        ->call('moveNode', $file, $folder2);

    $expectedContent = "Link: [file](/$folder1->name/$folder2->name/$file->name.md \"label\").";
    expect($rootFile1->refresh()->content)->toBe($expectedContent);
    $expectedContent = "Link: [file](/$folder1->name/$folder2->name/$file->name.md \"\").";
    expect($rootFile2->refresh()->content)->toBe($expectedContent);

    Event::assertDispatched(VaultFileSystemUpdatedEvent::class, 1);
    Event::assertDispatched(VaultNodeUpdatedEvent::class, 3);
});
