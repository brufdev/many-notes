<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\GetPathFromVault;
use App\Actions\GetPathFromVaultNode;
use App\Models\User;
use App\Models\VaultNode;
use App\Services\UploadLimit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** @return list<string> */
function importedVaultPaths(User $user): array
{
    return $user->vaults()->firstOrFail()->nodes()->get()
        ->map(fn(VaultNode $node): string => $node->fullPath() . ($node->is_file ? ".{$node->extension}" : '/'))
        ->sort()
        ->values()
        ->all();
}

it('imports a zip file', function (): void {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test.zip');

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    expect($vaults->first()->name)->toBe('test');
    $path = app(GetPathFromVault::class)->handle($vaults->first());
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('sanitizes the vault name taken from the zip file name', function (string $fileName, string $vaultName): void {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create($fileName);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    expect($vaults->first()->name)->toBe($vaultName);
    $path = app(GetPathFromVault::class)->handle($vaults->first());
    expect($path)->toBe('vaults/' . $user->id . '/' . $vaultName . '/');
    expect(Storage::disk('local')->exists($path))->toBeTrue();
})->with([
    'parent directory' => ['...zip', 'Imported vault'],
    'leading dot' => ['.backup.zip', 'backup'],
    'link characters' => ['Ideas #1.zip', 'Ideas -1'],
    'reserved name' => ['CON.zip', 'CON_'],
]);

it('handles name collisions when importing a vault with an existing name', function (): void {
    $getPathFromVault = app(GetPathFromVault::class);

    $user = User::factory()->create();
    $vaultName = fake()->words(3, true);
    app(CreateVault::class)->handle($user, ['name' => $vaultName]);
    $file = UploadedFile::fake()->create($vaultName . '.zip');

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file]);

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
    $createVault = app(CreateVault::class);

    $user = User::factory()->create();
    $vaultName = fake()->words(3, true);
    $createVault->handle($user, ['name' => $vaultName]);
    $createVault->handle($user, ['name' => $vaultName]);
    $file = UploadedFile::fake()->create($vaultName . '.zip');

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(3);
    expect($vaults->get(0)->name)->toBe($vaultName);
    expect($vaults->get(1)->name)->toBe($vaultName . '-1');
    expect($vaults->get(2)->name)->toBe($vaultName . '-2');
});

it('imports a zip file with files and folders', function (): void {
    $getPathFromVaultNode = app(GetPathFromVaultNode::class);

    $user = User::factory()->create();
    $file = createZipUpload([
        fake()->words(3, true) . '.sh' => fake()->paragraph(),
        'Notes' => null,
        'Notes/' . fake()->words(3, true) . '.md' => fake()->paragraph(),
    ]);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    $nodes = $vaults->first()->nodes()->get();
    expect($nodes->count())->toBe(2);
    $path = $getPathFromVaultNode->handle($nodes->get(0));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
    $path = $getPathFromVaultNode->handle($nodes->get(1));
    expect(Storage::disk('local')->exists($path))->toBeTrue();
});

it('imports files whose folders have no entry in the zip', function (): void {
    $user = User::factory()->create();
    $file = createZipUpload(['Notes/Daily/Plan.md' => 'plan']);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe(['Notes/', 'Notes/Daily/', 'Notes/Daily/Plan.md']);
    $path = app(GetPathFromVault::class)->handle($user->vaults()->first()) . 'Notes/Daily/Plan.md';
    expect(Storage::disk('local')->get($path))->toBe('plan');
});

it('imports files listed before their folder', function (): void {
    $user = User::factory()->create();
    $file = createZipUpload(['Notes/Plan.md' => 'plan', 'Notes' => null]);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe(['Notes/', 'Notes/Plan.md']);
});

it('normalizes the paths of zip entries', function (): void {
    $user = User::factory()->create();
    $file = createZipUpload([
        'Notes\\Plan.md' => 'plan',
        './Ideas.md' => 'ideas',
        'Daily//Today.md' => 'today',
        '/Archive/Old.md' => 'old',
    ]);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe([
        'Archive/',
        'Archive/Old.md',
        'Daily/',
        'Daily/Today.md',
        'Ideas.md',
        'Notes/',
        'Notes/Plan.md',
    ]);
});

it('skips zip entries that leave the vault', function (): void {
    $user = User::factory()->create();
    $file = createZipUpload([
        '../Escape.md' => 'escape',
        'Notes/../../Escape.md' => 'escape',
        'Notes\\..\\..\\Escape.md' => 'escape',
        'Plan.md' => 'plan',
    ]);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe(['Plan.md']);
});

it('skips junk entries added by operating systems', function (): void {
    $user = User::factory()->create();
    $file = createZipUpload([
        '__MACOSX' => null,
        '__MACOSX/Notes' => null,
        '__MACOSX/Notes/._Plan.md' => 'junk',
        'Notes/._Plan.md' => 'junk',
        'Notes/.DS_Store' => 'junk',
        'Notes/Thumbs.db' => 'junk',
        'Notes/desktop.ini' => 'junk',
        'Notes/Plan.md' => 'plan',
    ]);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe(['Notes/', 'Notes/Plan.md']);
});

it('keeps hidden and unsupported zip entries on disk without a node', function (): void {
    $user = User::factory()->create();
    $passthroughFiles = [
        '.obsidian/app.json' => '{}',
        'Attachments/data.json' => '[]',
        'Board.canvas' => '{"nodes":[]}',
        'Notes/.trash/Old.md' => 'old',
        'README' => 'readme',
        'photo.png' => '<html></html>',
    ];
    $file = createZipUpload(['.obsidian/plugins/' => null, ...$passthroughFiles, 'Notes/Plan.md' => 'plan']);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe(['Attachments/', 'Notes/', 'Notes/Plan.md']);
    $vaultPath = app(GetPathFromVault::class)->handle($user->vaults()->first());
    expect(Storage::disk('local')->path($vaultPath . '.obsidian/plugins'))->toBeDirectory();

    foreach ($passthroughFiles as $path => $content) {
        expect(Storage::disk('local')->get($vaultPath . $path))->toBe($content);
    }
});

it('skips zip entries whose names cannot be stored', function (): void {
    $user = User::factory()->create();
    $file = createZipUpload([
        "Plan\t.md" => 'tab',
        str_repeat('a', 253) . '.md' => 'too long',
        "Notes\n/Plan.md" => 'line feed',
        'Plan.md' => 'plan',
    ]);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe(['Plan.md']);
});

it('imports zip entries whose names have Unicode format characters', function (): void {
    $user = User::factory()->create();
    $file = createZipUpload(["\u{1F468}\u{200D}\u{1F4BB} Work/co\u{00AD}operate.md" => 'plan']);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe([
        "\u{1F468}\u{200D}\u{1F4BB} Work/",
        "\u{1F468}\u{200D}\u{1F4BB} Work/co\u{00AD}operate.md",
    ]);
    $path = app(GetPathFromVault::class)->handle($user->vaults()->first())
        . "\u{1F468}\u{200D}\u{1F4BB} Work/co\u{00AD}operate.md";
    expect(Storage::disk('local')->get($path))->toBe('plan');
});

it('creates links when importing a vault', function (): void {
    $user = User::factory()->create();
    $firstNodeName = fake()->words(3, true);
    $secondNodeName = fake()->words(3, true);
    $file = createZipUpload([
        $firstNodeName . '.md' => '[link](/' . $secondNodeName . '.md)',
        $secondNodeName . '.md' => '[link](/' . $firstNodeName . '.md)',
    ]);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    $nodes = $vaults->first()->nodes()->get();
    expect($nodes->count())->toBe(2);
    expect($nodes->get(0)->links()->count())->toBe(1);
    expect($nodes->get(1)->links()->count())->toBe(1);
});

it('creates tags when importing a vault', function (): void {
    $user = User::factory()->create();
    $file = createZipUpload(['note.md' => '#tag1 ' . fake()->paragraph() . ' #tag2']);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file]);

    $vaults = $user->vaults()->get();
    expect($vaults->count())->toBe(1);
    $nodes = $vaults->first()->nodes()->get();
    expect($nodes->count())->toBe(1);
    expect($nodes->first()->tags()->count())->toBe(2);
});

it('does not import a zip file larger than the upload limit', function (): void {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create(
        'test.zip',
        UploadLimit::kilobytes() + 1,
        'application/zip',
    );

    $response = $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file], ['Accept' => 'application/json']);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('file');
    expect($user->vaults()->count())->toBe(0);
});

it('does not import a zip file larger than the unzipped size limit', function (): void {
    config()->set('settings.import.max_unzipped_size', '1K');
    $user = User::factory()->create();
    $file = createZipUpload(['Plan.md' => str_repeat('a', 1025)]);

    $response = $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file], ['Accept' => 'application/json']);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['file' => 'The unzipped vault must be smaller than 1 KB.']);
    expect($user->vaults()->count())->toBe(0);
});

it('does not import a zip file whose declared sizes are wrong', function (int $declaredSize): void {
    $user = User::factory()->create();
    $file = createZipUpload(['data.json' => str_repeat('a', 100_000)]);
    $zip = (string) file_get_contents($file->getRealPath());

    // Overwrite the size in the local header and the central directory
    $localHeader = (int) mb_strpos($zip, "PK\x03\x04", 0, '8bit');
    $centralDirectory = (int) mb_strpos($zip, "PK\x01\x02", 0, '8bit');
    $zip = substr_replace($zip, pack('V', $declaredSize), $localHeader + 22, 4);
    $zip = substr_replace($zip, pack('V', $declaredSize), $centralDirectory + 24, 4);
    file_put_contents($file->getRealPath(), $zip);

    $response = $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file], ['Accept' => 'application/json']);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['file' => 'The zip file is damaged.']);
    expect($user->vaults()->count())->toBe(0);
})->with(['smaller' => 10, 'larger' => 200_000]);

it('imports a zip file that is exactly at the unzipped size limit', function (): void {
    config()->set('settings.import.max_unzipped_size', '1K');
    $user = User::factory()->create();
    $file = createZipUpload(['Plan.md' => str_repeat('a', 1024)]);

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => $file])
        ->assertOk();

    expect(importedVaultPaths($user))->toBe(['Plan.md']);
});
