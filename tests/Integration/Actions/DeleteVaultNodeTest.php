<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\DeleteVaultNode;
use App\Actions\GetPathFromVault;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('does not delete a file when deleting it on disk fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'Plan', 'extension' => 'md']);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);
    makeDirectoryReadOnly($vaultPath);

    expect(fn() => app(DeleteVaultNode::class)->handle($note))
        ->toThrow(Exception::class, 'Something went wrong');

    expect($vault->nodes()->find($note->id))->not->toBeNull();
    expect(Storage::disk('local')->exists($vaultPath . 'Plan.md'))->toBeTrue();
})->after(fn() => makeDirectoriesWritable());

it('does not delete a folder when deleting it on disk fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $folder = app(CreateVaultNode::class)->handle($vault, ['is_file' => false, 'name' => 'Ideas']);
    $note = app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $folder->id,
        'is_file' => true,
        'name' => 'Plan',
        'extension' => 'md',
    ]);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);
    makeDirectoryReadOnly($vaultPath . 'Ideas');

    expect(fn() => app(DeleteVaultNode::class)->handle($folder))
        ->toThrow(Exception::class, 'Something went wrong');

    expect($vault->nodes()->find($folder->id))->not->toBeNull();
    expect($vault->nodes()->find($note->id))->not->toBeNull();
    expect(Storage::disk('local')->exists($vaultPath . 'Ideas/Plan.md'))->toBeTrue();
})->after(fn() => makeDirectoriesWritable());

it('does not remove the note from the search index when deleting it on disk fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'Plan', 'extension' => 'md']);
    makeDirectoryReadOnly(app(GetPathFromVault::class)->handle($vault));
    $engine = spySearchEngine();

    expect(fn() => app(DeleteVaultNode::class)->handle($note))
        ->toThrow(Exception::class, 'Something went wrong');

    $engine->shouldNotHaveReceived('delete');
})->after(fn() => makeDirectoriesWritable());
