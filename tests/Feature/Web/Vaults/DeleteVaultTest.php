<?php

declare(strict_types=1);

use App\Actions\AcceptVaultCollaboration;
use App\Actions\CreateVault;
use App\Actions\CreateVaultCollaboration;
use App\Actions\CreateVaultNode;
use App\Actions\DeclineVaultCollaboration;
use App\Actions\GetPathFromVault;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('deletes a vault with invites, collaborators and related notifications', function (): void {
    $createVaultCollaboration = new CreateVaultCollaboration();
    $acceptVaultCollaboration = new AcceptVaultCollaboration();
    $declineVaultCollaboration = new DeclineVaultCollaboration();

    $user = User::factory()->create()->first();
    $collaborators = User::factory(3)->create();

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

    $createVaultCollaboration->handle($vault, $collaborators->get(0));
    $acceptVaultCollaboration->handle($vault, $collaborators->get(0));
    $createVaultCollaboration->handle($vault, $collaborators->get(1));
    $declineVaultCollaboration->handle($vault, $collaborators->get(1));
    $createVaultCollaboration->handle($vault, $collaborators->get(2));

    expect($user->vaults()->count())->toBe(1);

    $this->actingAs($user);

    $this->delete(route('vaults.destroy', ['vault' => $vault->id]));

    expect($user->vaults()->count())->toBe(0);
    $path = new GetPathFromVault()->handle($vault);
    expect(Storage::disk('local')->path($path))->not->toBeDirectory();
});

it('deletes a vault without a folder in the disk', function (): void {
    $user = User::factory()->hasVaults(1)->create();
    $vault = $user->vaults()->first();

    expect($user->vaults()->count())->toBe(1);

    $this->actingAs($user);

    $this->delete(route('vaults.destroy', ['vault' => $vault->id]));

    expect($user->vaults()->count())->toBe(0);
});

it('does not delete a vault without permissions', function (): void {
    $user = User::factory()->create();
    $secondUser = User::factory()->hasVaults(1)->create();
    $vault = $secondUser->vaults()->first();

    $this->actingAs($user);

    $response = $this->delete(route('vaults.destroy', ['vault' => $vault->id]));

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['delete']);
    expect($secondUser->vaults()->count())->toBe(1);
});
