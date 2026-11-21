<?php

declare(strict_types=1);

use App\Support\AppMetadata;
use Illuminate\Support\Facades\Cache;

it('reports an update only for older releases', function (string $version, string $latest, bool $expected): void {
    config()->set('app.version', $version);
    Cache::put('app:latest_version', $latest);

    expect(app(AppMetadata::class)->updateAvailable())->toBe($expected);
})->with([
    'older release' => ['0.18.1', '0.19.0', true],
    'same release' => ['0.19.0', '0.19.0', false],
    'newer release' => ['0.19.0', '0.18.1', false],
    'pre-release of the latest' => ['0.19.0-rc.1', '0.19.0', true],
    'pre-release of the next' => ['0.20.0-rc.1', '0.19.0', false],
    'failed check' => ['0.19.0', '0.0.0', false],
    'development build' => ['dev', '0.19.0', false],
    'empty version' => ['', '0.19.0', false],
]);
