<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final readonly class AppMetadata
{
    public function appVersion(): string
    {
        /** @var string */
        return config('app.version');
    }

    public function latestVersion(): string
    {
        /** @var string */
        return Cache::get('app:latest_version', '0.0.0');
    }

    public function githubUrl(): string
    {
        return 'https://github.com/brufdev/many-notes';
    }

    public function updateAvailable(): bool
    {
        return version_compare($this->latestVersion(), $this->appVersion(), '>');
    }
}
