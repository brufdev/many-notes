<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromUser;
use App\Livewire\Vault\TreeView;
use App\Models\User;
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
