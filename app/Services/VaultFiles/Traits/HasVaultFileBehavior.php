<?php

declare(strict_types=1);

namespace App\Services\VaultFiles\Traits;

trait HasVaultFileBehavior
{
    /**
     * @return list<string>
     */
    abstract private static function extensionsList(): array;

    /**
     * @return list<string>
     */
    abstract private static function mimeTypesList(): array;

    /**
     * @return list<string>
     */
    public static function extensions(bool $withDots = false): array
    {
        return $withDots
            ? array_map(fn(string $value): string => '.' . $value, static::extensionsList())
            : static::extensionsList();
    }

    public static function validate(string $extension, string $mimeType): bool
    {
        return in_array($extension, static::extensionsList())
            && array_any(
                static::mimeTypesList(),
                fn(string $value): bool => str_starts_with($mimeType, $value),
            );
    }
}
