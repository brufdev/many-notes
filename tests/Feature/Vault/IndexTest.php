<?php

declare(strict_types=1);

use App\Actions\AcceptCollaborationInvite;
use App\Actions\CreateCollaborationInvite;
use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\DeclineCollaborationInvite;
use App\Actions\GetPathFromUser;
use App\Actions\GetPathFromVault;
use App\Livewire\Vault\Index;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('only lists the user\'s vaults', function (): void {
    $user = User::factory(2)->hasVaults(2)->create()->first();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertCount('vaults', 2);
});

it('creates a vault', function (): void {
    $user = User::factory()->create();
    $vaultName = fake()->words(3, true);
    expect($user->vaults()->count())->toBe(0);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('form.name', $vaultName)
        ->call('create');
    expect($user->vaults()->count())->toBe(1);

    $path = new GetPathFromUser()->handle($user) . $vaultName;
    expect(Storage::disk('local')->path($path))->toBeDirectory();
});

it('exports a vault', function (): void {
    $user = User::factory()->create();
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
    new CreateVaultNode()->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'jpg',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('export', $vault)
        ->assertFileDownloaded($vault->name . '.zip');
});

it('fails exporting an empty vault', function (): void {
    $user = User::factory()->hasVaults(1)->create();
    $vault = $user->vaults()->first();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('export', $vault)
        ->assertReturned(null);
});

it('fails exporting a vault with files missing on disk', function (): void {
    $user = User::factory()->hasVaults(1)->create();
    $vault = $user->vaults()->first();
    $vault->nodes()->create([
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('export', $vault)
        ->assertReturned(null);
});

it('deletes a vault with invites, collaborators and related notifications', function (): void {
    $createCollaborationInvite = new CreateCollaborationInvite();
    $acceptCollaborationInvite = new AcceptCollaborationInvite();
    $declineCollaborationInvite = new DeclineCollaborationInvite();

    $user = User::factory()->create()->first();
    $firstCollaborator = User::factory()->create()->first();
    $secondCollaborator = User::factory()->create()->first();
    $thirdCollaborator = User::factory()->create()->first();

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

    $createCollaborationInvite->handle($vault, $firstCollaborator);
    $acceptCollaborationInvite->handle($vault, $firstCollaborator);

    $createCollaborationInvite->handle($vault, $secondCollaborator);
    $declineCollaborationInvite->handle($vault, $secondCollaborator);

    $createCollaborationInvite->handle($vault, $thirdCollaborator);

    expect($user->vaults()->count())->toBe(1);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('delete', $vault)
        ->assertDispatched('toast');
    expect($user->vaults()->count())->toBe(0);

    $path = new GetPathFromVault()->handle($vault);
    expect(Storage::disk('local')->path($path))->not->toBeDirectory();
});
