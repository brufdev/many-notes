<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Events\VaultFileSystemUpdatedEvent;
use App\Events\VaultNodeUpdatedEvent;
use App\Livewire\Modals\EditNode;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

it('opens the modal', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);

    Livewire::actingAs($user)
        ->test(EditNode::class, ['vault' => $vault])
        ->assertSet('show', false)
        ->call('open', $node)
        ->assertSet('show', true);
});

it('updates a node', function (): void {
    $user = User::factory()->create()->first();
    $vault = new CreateVault()->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $node = new CreateVaultNode()->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    $newName = fake()->words(4, true);

    Livewire::actingAs($user)
        ->test(EditNode::class, ['vault' => $vault])
        ->call('open', $node)
        ->set('form.name', $newName)
        ->call('edit')
        ->assertSet('show', false);

    expect($vault->nodes()->first()->name)->toBe($newName);
});

it('update note links when renaming a node', function (): void {
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
    $folder3 = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder2->id,
        'is_file' => false,
        'name' => 'folder 3',
    ]);
    $file1 = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder3->id,
        'is_file' => true,
        'name' => 'file 1',
        'extension' => 'md',
    ]);
    $file2 = new CreateVaultNode()->handle($vault, [
        'parent_id' => $folder3->id,
        'is_file' => true,
        'name' => 'file 2',
        'extension' => 'md',
    ]);
    $rootFile1 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 1',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$folder2->name/$folder3->name/$file1->name.md).",
    ]);
    $rootFile2 = new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => 'root file 2',
        'extension' => 'md',
        'content' => "Link: [file](/$folder1->name/$folder2->name/$folder3->name/$file2->name.md).",
    ]);

    $newFolderName = 'new folder name';
    Livewire::actingAs($user)
        ->test(EditNode::class, ['vault' => $vault])
        ->call('open', $folder2)
        ->set('form.name', $newFolderName)
        ->call('edit')
        ->assertSet('show', false);

    expect($folder2->refresh()->name)->toBe($newFolderName);
    $expectedContent = "Link: [file](/$folder1->name/$newFolderName/$folder3->name/$file1->name.md).";
    expect($rootFile1->refresh()->content)->toBe($expectedContent);
    $expectedContent = "Link: [file](/$folder1->name/$newFolderName/$folder3->name/$file2->name.md).";
    expect($rootFile2->refresh()->content)->toBe($expectedContent);

    Event::assertDispatched(VaultFileSystemUpdatedEvent::class, 1);
    Event::assertDispatched(VaultNodeUpdatedEvent::class, 3);
});
