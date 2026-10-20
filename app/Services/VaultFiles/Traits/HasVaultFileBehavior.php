<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Traits;

trait HasVaultFileBehavior
{
    /** @return array<string, string> */
    abstract private static function responseMimeTypes(): array;

    /** @return list<string> */
    abstract private static function uploadMimeTypes(): array;

    /** @return list<string> */
    public static function extensions(bool $withDots = false): array
    {
        $extensions = array_keys(static::responseMimeTypes());

        return $withDots
            ? array_map(fn(string $value): string => '.' . $value, $extensions)
            : $extensions;
    }

    public static function contentType(string $extension): ?string
    {
        return static::responseMimeTypes()[$extension] ?? null;
    }

    public static function validate(string $extension, string $mimeType): bool
    {
        return array_key_exists($extension, static::responseMimeTypes())
            && in_array($mimeType, static::uploadMimeTypes());
    }
}
