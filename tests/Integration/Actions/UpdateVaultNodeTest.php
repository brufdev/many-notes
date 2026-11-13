<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Actions\UpdateVaultNode;
use App\Models\User;
use App\Models\VaultNode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

it('updates the search index after renaming a note', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'Plan', 'extension' => 'md']);
    $engine = spySearchEngine();

    app(UpdateVaultNode::class)->handle($note, ['name' => 'Renamed']);

    $engine->shouldHaveReceived('update')->once();
});

it('does not rename a node when renaming it on disk fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'foo']);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);
    Storage::disk('local')->put($vaultPath . 'bar/old.md', 'old');

    expect(fn() => app(UpdateVaultNode::class)->handle($folder, ['name' => 'bar']))
        ->toThrow(ValidationException::class, 'The name has already been taken.');

    expect($folder->refresh()->name)->toBe('foo');
    expect(Storage::disk('local')->exists($vaultPath . 'foo'))->toBeTrue();
});

it('does not rename the node on disk when saving the new name fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'Plan', 'extension' => 'md']);
    VaultNode::updating(fn(): never => throw new RuntimeException('Database error'));

    expect(fn() => app(UpdateVaultNode::class)->handle($note, ['name' => 'Renamed']))
        ->toThrow(RuntimeException::class, 'Database error');

    expect($note->refresh()->name)->toBe('Plan');
    expect(Storage::disk('local')->files(app(GetPathFromVault::class)->handle($vault)))
        ->toBe(['vaults/' . $user->id . '/Notes/Plan.md']);
});

it('does not update the search index when renaming a note on disk fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'Plan', 'extension' => 'md']);
    makeDirectoryReadOnly(app(GetPathFromVault::class)->handle($vault));
    $engine = spySearchEngine();

    expect(fn() => app(UpdateVaultNode::class)->handle($note, ['name' => 'Renamed']))
        ->toThrow(ValidationException::class, 'The name has already been taken.');

    $engine->shouldNotHaveReceived('update');
})->after(fn() => makeDirectoriesWritable());
