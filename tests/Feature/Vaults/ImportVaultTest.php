<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromVault;
use App\Actions\GetPathFromVaultNode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

it('imports a zip file', function (): void {
    $user = User::factory()->create()->first();
    $file = UploadedFile::fake()->create('test.zip');

    $this->actingAs($user);

    $this->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    expect($vaults->first()->name)->toBe('test');
    $path = new GetPathFromVault()->handle($vaults->first());
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('handles name collisions when importing a vault with an existing name', function (): void {
    $getPathFromVault = new GetPathFromVault();

    $user = User::factory()->create()->first();
    $vaultName = fake()->words(3, true);
    new CreateVault()->handle($user, ['name' => $vaultName]);
    $file = UploadedFile::fake()->create($vaultName . '.zip');

    $this->actingAs($user);

    $this->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(2);
    expect($vaults->get(0)->name)->toBe($vaultName);
    expect($vaults->get(1)->name)->toBe($vaultName . '-1');
    $path = $getPathFromVault->handle($vaults->get(0));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = $getPathFromVault->handle($vaults->get(1));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('handles name collisions when importing a vault with a name existing in multiple vaults', function (): void {
    $createVault = new CreateVault();

    $user = User::factory()->create()->first();
    $vaultName = fake()->words(3, true);
    $createVault->handle($user, ['name' => $vaultName]);
    $createVault->handle($user, ['name' => $vaultName]);
    $file = UploadedFile::fake()->create($vaultName . '.zip');

    $this->actingAs($user);

    $this->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(3);
    expect($vaults->get(0)->name)->toBe($vaultName);
    expect($vaults->get(1)->name)->toBe($vaultName . '-1');
    expect($vaults->get(2)->name)->toBe($vaultName . '-2');
});

it('imports a zip file with files and folders', function (): void {
    $zip = new ZipArchive();
    $getPathFromVaultNode = new GetPathFromVaultNode();

    $user = User::factory()->create()->first();
    $relativePath = 'public/' . Str::random(16) . '.zip';
    Storage::disk('local')->put($relativePath, '');
    $path = Storage::disk('local')->path($relativePath);
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString(fake()->words(3, true) . '.sh', fake()->paragraph());
    $zip->addEmptyDir('Notes');
    $zip->addFromString('Notes/' . fake()->words(3, true) . '.md', fake()->paragraph());
    $zip->close();
    $file = UploadedFile::fake()->createWithContent('vault.zip', file_get_contents($path));

    $this->actingAs($user);

    $this->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    $nodes = $vaults->first()->nodes()->get();
    expect($nodes->count())->toBe(2);
    $path = $getPathFromVaultNode->handle($nodes->get(0));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = $getPathFromVaultNode->handle($nodes->get(1));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('creates links when importing a vault', function (): void {
    $zip = new ZipArchive();

    $user = User::factory()->create()->first();
    $relativePath = 'public/' . Str::random(16) . '.zip';
    Storage::disk('local')->put($relativePath, '');
    $path = Storage::disk('local')->path($relativePath);
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $firstNodeName = fake()->words(3, true);
    $secondNodeName = fake()->words(3, true);
    $zip->addFromString($firstNodeName . '.md', '[link](/' . $secondNodeName . '.md)');
    $zip->addFromString($secondNodeName . '.md', '[link](/' . $firstNodeName . '.md)');
    $zip->close();
    $file = UploadedFile::fake()->createWithContent('vault.zip', file_get_contents($path));

    $this->actingAs($user);

    $this->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    $nodes = $vaults->first()->nodes()->get();
    expect($nodes->count())->toBe(2);
    expect($nodes->get(0)->links()->count())->toBe(1);
    expect($nodes->get(1)->links()->count())->toBe(1);
});

it('creates tags when importing a vault', function (): void {
    $user = User::factory()->create()->first();
    $zip = new ZipArchive();
    $relativePath = 'public/' . Str::random(16) . '.zip';
    Storage::disk('local')->put($relativePath, '');
    $path = Storage::disk('local')->path($relativePath);
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('note.md', '#tag1 ' . fake()->paragraph() . ' #tag2');
    $zip->close();
    $file = UploadedFile::fake()->createWithContent('vault.zip', file_get_contents($path));

    $this->actingAs($user);

    $this->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    $nodes = $vaults->first()->nodes()->get();
    expect($nodes->count())->toBe(1);
    expect($nodes->first()->tags()->count())->toBe(2);
});
