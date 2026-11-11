<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Models\Scopes\SupportedVaultNodeScope;
use App\Models\User;
use App\Models\VaultNode;
use Illuminate\Support\Facades\Storage;

it('deletes a file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $file = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
    ]);

    expect($vault->nodes()->count())->toBe(1);

    $this->actingAs($user);

    $this->delete(
        route('vaults.nodes.destroy', [
            'vault' => $vault->id,
            'node' => $file->id,
        ]),
    );

    expect($vault->nodes()->count())->toBe(0);
});

it('deletes a folder and its children', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => fake()->words(3, true),
    ]);
    app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    expect($vault->nodes()->count())->toBe(2);

    $this->actingAs($user);

    $this->delete(
        route('vaults.nodes.destroy', [
            'vault' => $vault->id,
            'node' => $folder->id,
        ]),
    );

    expect($vault->nodes()->count())->toBe(0);
});

it('deletes files without a node with their folder', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => 'Notes',
    ]);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);
    Storage::disk('local')->put($vaultPath . 'Notes/.trash/Old.md', 'old');

    $this->actingAs($user)
        ->delete(route('vaults.nodes.destroy', ['vault' => $vault->id, 'node' => $folder->id]))
        ->assertOk();

    expect(Storage::disk('local')->exists($vaultPath . 'Notes'))->toBeFalse();
});

it('deletes an unsupported file', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $node = createUnsupportedNode($vault, 'Board.canvas');

    $this->actingAs($user)
        ->delete(route('vaults.nodes.destroy', ['vault' => $vault->id, 'node' => $node->id]))
        ->assertOk();

    expect($vault->nodes()->withoutGlobalScope(SupportedVaultNodeScope::class)->count())->toBe(0);
    expect(Storage::disk('local')->exists(app(GetPathFromVault::class)->handle($vault) . 'Board.canvas'))
        ->toBeFalse();
});

it('deletes a folder with unsupported files', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => fake()->words(3, true)]);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Attachments']);
    createUnsupportedNode($vault, 'data.json', $folder->id);

    $this->actingAs($user)
        ->delete(route('vaults.nodes.destroy', ['vault' => $vault->id, 'node' => $folder->id]))
        ->assertOk();

    expect($vault->nodes()->withoutGlobalScope(SupportedVaultNodeScope::class)->count())->toBe(0);
});

it('does not delete a file without permissions', function (): void {
    [$user1, $user2] = User::factory(2)->create();
    $vault = app(CreateVault::class)->handle($user1, ['name' => fake()->words(3, true)]);
    $file = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user2);

    $response = $this->delete(
        route('vaults.nodes.destroy', [
            'vault' => $vault->id,
            'node' => $file->id,
        ]),
    );

    $response->assertForbidden();
    expect($vault->nodes()->count())->toBe(1);
});

it('deletes a folder whose name is unsafe without touching the disk', function (string $name): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $folder = VaultNode::factory()->for($vault)->create(['name' => $name]);
    VaultNode::factory()->for($vault)->file()->create(['parent_id' => $folder->id]);
    $paths = [...storeFilesOutsideVault($user), "vaults/{$user->id}/Notes/keep.md"];
    Storage::disk('local')->put("vaults/{$user->id}/Notes/keep.md", 'outside');

    $this->actingAs($user)
        ->deleteJson(route('vaults.nodes.destroy', ['vault' => $vault->id, 'node' => $folder->id]))
        ->assertOk();

    expect($vault->nodes()->count())->toBe(0);
    expectFilesKept($paths);
})->with('unsafe path segments');
