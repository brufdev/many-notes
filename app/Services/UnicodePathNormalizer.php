<?php

declare(strict_types=1);

namespace App\Services;

use League\Flysystem\CorruptedPathDetected;
use League\Flysystem\PathNormalizer;
use League\Flysystem\PathTraversalDetected;

final readonly class UnicodePathNormalizer implements PathNormalizer
{
    public function normalizePath(string $path): string
    {
        $unixPath = str_replace('\\', '/', $path);

        // Reject control characters and invalid UTF-8
        if (preg_match('/\p{Cc}/u', $unixPath) !== 0) {
            throw CorruptedPathDetected::forPath($path);
        }

        $parts = [];

        foreach (explode('/', $unixPath) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part !== '..') {
                $parts[] = $part;

                continue;
            }

            if ($parts === []) {
                throw PathTraversalDetected::forPath($path);
            }

            array_pop($parts);
        }

        return implode('/', $parts);
    }
}
