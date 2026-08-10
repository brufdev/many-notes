<?php

declare(strict_types=1);

use App\Actions\GetPathFromUser;
use App\Actions\GetPathFromVault;
use App\Actions\GetPathFromVaultNode;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;

function localDiskPath(string $relativePath): string
{
    /** @var string $root */
    $root = config('filesystems.disks.local.root');

    return $root . '/' . $relativePath;
}

it('resolves the user path to the vaults folder on disk', function (): void {
    $user = User::factory()->create();

    $path = app(GetPathFromUser::class)->handle($user);

    expect(localDiskPath($path))->toBe(storage_path("app/private/vaults/{$user->id}/"));
});

it('resolves the vault path to the vault folder on disk', function (): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create(['name' => 'Personal']);

    $path = app(GetPathFromVault::class)->handle($vault);

    expect(localDiskPath($path))->toBe(storage_path("app/private/vaults/{$user->id}/Personal/"));
});

it('resolves a root vault node path to the note on disk', function (): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create(['name' => 'Personal']);
    $note = VaultNode::factory()->for($vault)->file()->create([
        'parent_id' => null,
        'name' => 'Note',
    ]);

    $path = app(GetPathFromVaultNode::class)->handle($note);

    expect(localDiskPath($path))->toBe(storage_path("app/private/vaults/{$user->id}/Personal/Note.md"));
});

it('resolves a nested vault node path to the note on disk', function (): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create(['name' => 'Personal']);
    $folder = VaultNode::factory()->for($vault)->create([
        'parent_id' => null,
        'name' => 'Inbox',
    ]);
    $note = VaultNode::factory()->for($vault)->file()->create([
        'parent_id' => $folder->id,
        'name' => 'Note',
    ]);

    $path = app(GetPathFromVaultNode::class)->handle($note);

    expect(localDiskPath($path))->toBe(storage_path("app/private/vaults/{$user->id}/Personal/Inbox/Note.md"));
});

it('resolves the folder of a vault node without including itself', function (): void {
    $user = User::factory()->create();
    $vault = Vault::factory()->for($user)->create(['name' => 'Personal']);
    $folder = VaultNode::factory()->for($vault)->create([
        'parent_id' => null,
        'name' => 'Inbox',
    ]);
    $note = VaultNode::factory()->for($vault)->file()->create([
        'parent_id' => $folder->id,
        'name' => 'Note',
    ]);

    $path = app(GetPathFromVaultNode::class)->handle($note, false);

    expect(localDiskPath($path))->toBe(storage_path("app/private/vaults/{$user->id}/Personal/Inbox/"));
});
