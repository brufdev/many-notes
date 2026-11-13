<?php

declare(strict_types=1);

use App\Actions\CreateVaultNode;
use App\Models\User;
use App\Models\Vault;
use App\Models\VaultNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\NullEngine;
use Mockery\MockInterface;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Storage::fake('local');
        $this->freezeTime();
        config()->set('settings.local_auth.enabled', true);
    })
    ->in('Browser', 'Feature', 'Integration');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn() => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/** @param array<string, string|null> $entries */
function createZipUpload(array $entries): UploadedFile
{
    $zip = new ZipArchive();
    $relativePath = 'public/' . Str::random(16) . '.zip';
    Storage::disk('local')->put($relativePath, '');
    $path = Storage::disk('local')->path($relativePath);
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($entries as $name => $content) {
        $content === null
            ? $zip->addEmptyDir($name)
            : $zip->addFromString($name, $content);
    }

    $zip->close();

    return UploadedFile::fake()->createWithContent('vault.zip', (string) file_get_contents($path));
}

function createUnsupportedNode(Vault $vault, string $fileName, ?int $parentId = null): VaultNode
{
    $pathInfo = pathinfo($fileName);

    return app(CreateVaultNode::class)->handle($vault, [
        'parent_id' => $parentId,
        'is_file' => true,
        'name' => $pathInfo['filename'],
        'extension' => $pathInfo['extension'] ?? null,
        'content' => 'unsupported',
        'is_supported' => false,
    ]);
}

/** @return array<string, string|null> */
function unzipResponse(TestResponse $response): array
{
    Storage::disk('local')->put('public/response.zip', (string) $response->streamedContent());
    $zip = new ZipArchive();
    $zip->open(Storage::disk('local')->path('public/response.zip'));
    $entries = [];

    for ($i = 0; $i < $zip->count(); $i++) {
        $name = (string) $zip->getNameIndex($i);
        $entries[$name] = str_ends_with($name, '/') ? null : (string) $zip->getFromIndex($i);
    }

    $zip->close();
    ksort($entries);

    return $entries;
}

/** @return list<string> */
function storeFilesOutsideVault(User $owner): array
{
    $other = User::factory()->create();
    $paths = [
        "vaults/{$other->id}/Private/secret.md",
        "vaults/{$owner->id}/Other/own.md",
        "vaults/{$owner->id}/photo.png",
    ];

    foreach ($paths as $path) {
        Storage::disk('local')->put($path, 'outside');
    }

    return $paths;
}

/** @param list<string> $paths */
function expectFilesKept(array $paths): void
{
    foreach ($paths as $path) {
        expect(Storage::disk('local')->get($path))->toBe('outside');
    }
}

function makeDirectoryReadOnly(string $path): void
{
    chmod(Storage::disk('local')->path($path), 0555);
}

function makeDirectoriesWritable(): void
{
    foreach (Storage::disk('local')->allDirectories() as $directory) {
        chmod(Storage::disk('local')->path($directory), 0755);
    }
}

function spySearchEngine(): MockInterface
{
    $engine = Mockery::spy(NullEngine::class);
    app(EngineManager::class)->extend('spy', fn(): MockInterface => $engine);
    config()->set('scout.driver', 'spy');

    return $engine;
}
