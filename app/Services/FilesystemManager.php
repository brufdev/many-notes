<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Filesystem\FilesystemManager as BaseFilesystemManager;
use Illuminate\Support\Arr;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemOperator;
use Override;

final class FilesystemManager extends BaseFilesystemManager
{
    /** @param array<string, mixed> $config */
    #[Override]
    protected function createFlysystem(FilesystemAdapter $adapter, array $config): FilesystemOperator
    {
        return new Filesystem($adapter, Arr::only($config, [
            'directory_visibility',
            'disable_asserts',
            'retain_visibility',
            'temporary_url',
            'url',
            'visibility',
        ]), new UnicodePathNormalizer());
    }
}
