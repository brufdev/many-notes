<?php

declare(strict_types=1);

use App\Actions\GetAvailableFileName;
use Illuminate\Support\Facades\Storage;

it('keeps a free name', function (): void {
    expect(app(GetAvailableFileName::class)->handle('vaults/1/Vault/', 'data.json'))->toBe('data.json');
});

it('suffixes a taken name', function (array $takenNames, string $availableName): void {
    foreach ($takenNames as $takenName) {
        Storage::disk('local')->put('vaults/1/Vault/' . $takenName, '');
    }

    expect(app(GetAvailableFileName::class)->handle('vaults/1/Vault/', $takenNames[0]))->toBe($availableName);
})->with([
    'extension' => [['data.json'], 'data-1.json'],
    'suffix also taken' => [['data.json', 'data-1.json'], 'data-2.json'],
    'no extension' => [['README'], 'README-1'],
    'dotfile' => [['.env'], '.env-1'],
    'multiple dots' => [['archive.tar.gz'], 'archive.tar-1.gz'],
]);

it('cuts a long taken name so the suffix fits', function (): void {
    $fileName = str_repeat('a', 250) . '.json';
    Storage::disk('local')->put('vaults/1/Vault/' . $fileName, '');

    expect(app(GetAvailableFileName::class)->handle('vaults/1/Vault/', $fileName))
        ->toBe(str_repeat('a', 248) . '-1.json');
});
