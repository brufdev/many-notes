<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\DeleteVault;
use App\Actions\GetPathFromVault;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('does not delete a vault when deleting it on disk fails', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, ['name' => 'Notes']);
    $note = app(CreateVaultNode::class)->handle($vault, ['is_file' => true, 'name' => 'Plan', 'extension' => 'md']);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);
    makeDirectoryReadOnly($vaultPath);

    expect(fn() => app(DeleteVault::class)->handle($vault))
        ->toThrow(Exception::class, 'Something went wrong');

    expect($user->vaults()->find($vault->id))->not->toBeNull();
    expect($vault->nodes()->find($note->id))->not->toBeNull();
    expect(Storage::disk('local')->exists($vaultPath . 'Plan.md'))->toBeTrue();
})->after(fn() => makeDirectoriesWritable());
