<?php

declare(strict_types=1);

use App\Actions\CreateVault;
use App\Actions\CreateVaultNode;
use App\Actions\GetPathFromVault;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

/** @return array<string, string|null> */
function exportedZipEntries(TestResponse $response): array
{
    Storage::disk('local')->put('public/export.zip', (string) $response->streamedContent());
    $zip = new ZipArchive();
    $zip->open(Storage::disk('local')->path('public/export.zip'));
    $entries = [];

    for ($i = 0; $i < $zip->count(); $i++) {
        $name = (string) $zip->getNameIndex($i);
        $entries[$name] = str_ends_with($name, '/') ? null : (string) $zip->getFromIndex($i);
    }

    $zip->close();
    ksort($entries);

    return $entries;
}

it('exports a vault', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $folderNode = app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => 'Notes',
    ]);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'parent_id' => $folderNode->id,
        'name' => 'Plan',
        'extension' => 'md',
        'content' => 'plan',
    ]);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => true,
        'name' => 'Photo',
        'extension' => 'jpg',
        'content' => 'photo',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertOk();
    expect($response->headers->get('Content-Disposition'))->toBe('attachment; filename="' . $vault->name . '.zip"');
    expect(exportedZipEntries($response))->toBe([
        'Notes/' => null,
        'Notes/Plan.md' => 'plan',
        'Photo.jpg' => 'photo',
    ]);
});

it('exports an imported vault unchanged', function (): void {
    $user = User::factory()->create();
    $entries = [
        'Daily/' => null,
        'Daily/Today.md' => '#daily [[Ideas]]',
        'Empty/' => null,
        'Ideas.md' => 'ideas',
        'Projects/' => null,
        'Projects/Archive/' => null,
        'Projects/Archive/Old.md' => 'old',
    ];

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => createZipUpload($entries)]);
    $vault = $user->vaults()->firstOrFail();

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertOk();
    expect(exportedZipEntries($response))->toBe($entries);
});

it('exports an imported Obsidian vault unchanged', function (): void {
    $user = User::factory()->create();
    $entries = [
        '.obsidian/' => null,
        '.obsidian/app.json' => '{"promptDelete":false}',
        '.obsidian/plugins/' => null,
        '.obsidian/plugins/x/' => null,
        '.obsidian/plugins/x/main.js' => 'module.exports = {};',
        'Attachments/' => null,
        'Attachments/data.json' => '[]',
        'Board.canvas' => '{"nodes":[]}',
        'Notes/' => null,
        'Notes/.trash/' => null,
        'Notes/.trash/Old.md' => 'old',
        'Notes/Plan.md' => '#plan [[Board.canvas]]',
        'photo.png' => '<html></html>',
    ];

    $this->actingAs($user)
        ->post(route('vaults.import'), ['file' => createZipUpload($entries)]);
    $vault = $user->vaults()->firstOrFail();

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertOk();
    expect(exportedZipEntries($response))->toBe($entries);
});

it('exports files on disk that have no node', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    $vaultPath = app(GetPathFromVault::class)->handle($vault);
    Storage::disk('local')->put($vaultPath . '.obsidian/app.json', '{}');

    $this->actingAs($user);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertOk();
    expect(exportedZipEntries($response))->toBe([
        '.obsidian/' => null,
        '.obsidian/app.json' => '{}',
    ]);
});

it('exports a vault with only an empty folder', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, [
        'name' => fake()->words(3, true),
    ]);
    app(CreateVaultNode::class)->handle($vault, [
        'is_file' => false,
        'name' => 'Empty',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertOk();
    expect(exportedZipEntries($response))->toBe(['Empty/' => null]);
});

it('fails exporting a vault with no permissions', function (): void {
    $firstUser = User::factory()->hasVaults(1)->create();
    $secondUser = User::factory()->create();
    $vault = $firstUser->vaults()->first();
    $vault->nodes()->create([
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($secondUser);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertForbidden();
});

it('fails exporting an empty vault', function (): void {
    $user = User::factory()->create();
    $vault = app(CreateVault::class)->handle($user, [
        'name' => fake()->words(3, true),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertUnprocessable();
});

it('fails exporting a vault whose nodes are missing on disk', function (): void {
    $user = User::factory()->hasVaults(1)->create();
    $vault = $user->vaults()->first();
    $vault->nodes()->create([
        'is_file' => true,
        'name' => fake()->words(3, true),
        'extension' => 'md',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('vaults.export', ['vault' => $vault->id]));

    $response->assertUnprocessable();
});
